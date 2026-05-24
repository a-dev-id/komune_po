<?php

namespace App\Http\Controllers\PurchasingLite;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLog;
use App\Services\PurchasingLite\PurchasingLiteEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseRequestCostControlController extends Controller
{
    public function show(PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userHasCostControlAccess($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only Cost Control can review this purchase request.');
        }

        if (! $this->purchaseRequestIsAtCostControlStep($purchaseRequest)) {
            if ($this->normalizeStep($purchaseRequest->current_step) === 'gm') {
                return redirect('/purchasing-lite/dashboard')
                    ->with('success', 'This PR has already been sent to GM.');
            }

            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'This PR is no longer waiting for Cost Control review.');
        }

        $purchaseRequest->load([
            'items',
            'vendorOffers.vendor',
            'vendorOffers.offerItems.purchaseRequestItem',
            'logs.user',
        ]);

        $vendorBids = [];
        $selectedOfferItemIds = [];

        foreach ($purchaseRequest->items as $item) {
            $vendorBids[$item->id] = [];
        }

        foreach ($purchaseRequest->vendorOffers as $vendorOffer) {
            foreach ($vendorOffer->offerItems as $offerItem) {
                $itemId = (int) $offerItem->purchase_request_item_id;

                if (! isset($vendorBids[$itemId])) {
                    $vendorBids[$itemId] = [];
                }

                $bidNumber = $this->extractBidNumber($offerItem->notes);
                $isSelected = str_contains((string) $offerItem->notes, 'SELECTED_BY_COST_CONTROL');

                if ($isSelected) {
                    $selectedOfferItemIds[$itemId] = (int) $offerItem->id;
                }

                $vendorBids[$itemId][] = [
                    'offer_item_id' => $offerItem->id,
                    'vendor_offer_id' => $vendorOffer->id,
                    'vendor_name' => $vendorOffer->vendor_name_snapshot ?: optional($vendorOffer->vendor)->name,
                    'bid_number' => $bidNumber,
                    'unit_price' => $offerItem->unit_price,
                    'quantity' => $offerItem->quantity,
                    'total_price' => (float) $offerItem->unit_price * (float) $offerItem->quantity,
                    'is_selected' => $isSelected,
                ];
            }
        }

        foreach ($vendorBids as $itemId => $bids) {
            usort($vendorBids[$itemId], function ($a, $b) {
                return ($a['bid_number'] <=> $b['bid_number'])
                    ?: ((float) $a['unit_price'] <=> (float) $b['unit_price']);
            });
        }

        return view('purchasing-lite.purchase-requests.cost-control', [
            'user' => $user,
            'purchaseRequest' => $purchaseRequest,
            'vendorBids' => $vendorBids,
            'selectedOfferItemIds' => $selectedOfferItemIds,
        ]);
    }

    public function selectVendor(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userHasCostControlAccess($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only Cost Control can choose vendor.');
        }

        if (! $this->purchaseRequestIsAtCostControlStep($purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'This PR is no longer waiting for Cost Control review.');
        }

        $validated = $request->validate([
            'selected_offer_item_ids' => ['required', 'array'],
            'selected_offer_item_ids.*' => ['required', 'integer'],
        ]);

        $purchaseRequest->load([
            'items',
            'vendorOffers.offerItems',
        ]);

        $requiredItemIds = $purchaseRequest->items
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $selectedOfferItemIds = collect($validated['selected_offer_item_ids'] ?? [])
            ->mapWithKeys(function ($offerItemId, $itemId) {
                return [(int) $itemId => (int) $offerItemId];
            })
            ->filter(function ($offerItemId) {
                return $offerItemId > 0;
            });

        foreach ($requiredItemIds as $itemId) {
            if (! $selectedOfferItemIds->has($itemId)) {
                return back()
                    ->withErrors([
                        'selected_offer_item_ids' => 'Please choose a vendor for every item before saving Cost Control selection.',
                    ])
                    ->withInput();
            }
        }

        DB::beginTransaction();

        try {
            $allOfferItems = $purchaseRequest->vendorOffers
                ->flatMap(function ($vendorOffer) {
                    return $vendorOffer->offerItems;
                });

            foreach ($allOfferItems as $offerItem) {
                $notes = $this->removeSelectedMarker($offerItem->notes);

                $isSelectedForItem =
                    $selectedOfferItemIds->has((int) $offerItem->purchase_request_item_id)
                    && (int) $selectedOfferItemIds->get((int) $offerItem->purchase_request_item_id) === (int) $offerItem->id;

                if ($isSelectedForItem) {
                    $notes = trim($notes);

                    if ($notes === '') {
                        $notes = 'SELECTED_BY_COST_CONTROL';
                    } else {
                        $notes .= ' | SELECTED_BY_COST_CONTROL';
                    }
                }

                $offerItem->update([
                    'notes' => $notes,
                ]);
            }

            foreach ($purchaseRequest->vendorOffers as $vendorOffer) {
                $hasSelectedItem = $vendorOffer->offerItems()
                    ->where('notes', 'like', '%SELECTED_BY_COST_CONTROL%')
                    ->exists();

                $vendorOffer->update([
                    'is_selected_by_cost_control' => $hasSelectedItem,
                ]);
            }

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? $user->role_name ?? null,
                'action' => 'cost_control_vendor_selected',
                'from_status' => $purchaseRequest->status,
                'to_status' => $purchaseRequest->status,
                'from_step' => $purchaseRequest->current_step,
                'to_step' => $purchaseRequest->current_step,
                'remarks' => 'Vendor selected by Cost Control.',
                'acted_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchasing-lite.purchase-requests.cost-control.show', $purchaseRequest)
                ->with('success', 'Vendor selection has been saved.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => 'Failed to save vendor selection. ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function sendToGm(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userHasCostControlAccess($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only Cost Control can send this PR to GM.');
        }

        if (! $this->purchaseRequestIsAtCostControlStep($purchaseRequest)) {
            if ($this->normalizeStep($purchaseRequest->current_step) === 'gm') {
                return redirect('/purchasing-lite/dashboard')
                    ->with('success', 'This PR has already been sent to GM.');
            }

            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'This PR is no longer waiting for Cost Control review.');
        }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $purchaseRequest->load([
            'items',
            'vendorOffers.offerItems',
        ]);

        if (! $this->allItemsHaveCostControlSelection($purchaseRequest)) {
            return back()
                ->withErrors([
                    'selected_offer_item_ids' => 'Please save selected vendor for every item before sending this PR to GM.',
                ])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $fromStatus = $purchaseRequest->status;
            $fromStep = $purchaseRequest->current_step;

            $remarks = trim((string) ($validated['remarks'] ?? ''));

            if ($remarks === '') {
                $remarks = 'Submitted to GM by Cost Control.';
            }

            $purchaseRequest->update([
                'status' => 'submitted_to_gm',
                'current_step' => 'gm',
                'current_status_at' => now(),
            ]);

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? $user->role_name ?? null,
                'action' => 'submitted_to_gm_from_cost_control',
                'from_status' => $fromStatus,
                'to_status' => 'submitted_to_gm',
                'from_step' => $fromStep,
                'to_step' => 'gm',
                'remarks' => $remarks,
                'acted_at' => now(),
            ]);

            DB::commit();

            app(PurchasingLiteEmailService::class)->sendToRoles(
                purchaseRequest: $purchaseRequest,
                roles: ['gm'],
                subject: 'PR Sent to GM Review - ' . $purchaseRequest->pr_number,
                title: 'PR Needs GM Review',
                messageText: 'Cost Control has selected the vendor and submitted this purchase request for GM review.',
                buttonLabel: 'Open PR',
                buttonUrl: route('purchasing-lite.purchase-requests.gm.show', $purchaseRequest),
                remarks: $remarks
            );

            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'PR has been sent to GM.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => 'Failed to send PR to GM. ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function returnToPurchasing(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userHasCostControlAccess($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only Cost Control can return this PR to Purchasing.');
        }

        if (! $this->purchaseRequestIsAtCostControlStep($purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'This PR is no longer waiting for Cost Control review.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $fromStatus = $purchaseRequest->status;
            $fromStep = $purchaseRequest->current_step;

            $purchaseRequest->update([
                'status' => 'revision_to_purchasing_from_cost_control',
                'current_step' => 'purchasing',
                'current_status_at' => now(),
            ]);

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? $user->role_name ?? null,
                'action' => 'returned_to_purchasing_from_cost_control',
                'from_status' => $fromStatus,
                'to_status' => 'revision_to_purchasing_from_cost_control',
                'from_step' => $fromStep,
                'to_step' => 'purchasing',
                'remarks' => $validated['remarks'],
                'acted_at' => now(),
            ]);

            DB::commit();

            app(PurchasingLiteEmailService::class)->sendToRoles(
                purchaseRequest: $purchaseRequest,
                roles: ['purchasing'],
                subject: 'PR Returned to Purchasing - ' . $purchaseRequest->pr_number,
                title: 'PR Returned by Cost Control',
                messageText: 'Cost Control has returned this purchase request to Purchasing for update.',
                buttonLabel: 'Open PR',
                buttonUrl: route('purchasing-lite.purchase-requests.show', $purchaseRequest),
                remarks: $validated['remarks']
            );

            return redirect()
                ->route('purchasing-lite.dashboard')
                ->with('success', 'PR has been returned to Purchasing.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => 'Failed to return PR to Purchasing. ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function sendBackToRequester(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userHasCostControlAccess($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only Cost Control can return this PR to Requester.');
        }

        if (! $this->purchaseRequestIsAtCostControlStep($purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'This PR is no longer waiting for Cost Control review.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $fromStatus = $purchaseRequest->status;
            $fromStep = $purchaseRequest->current_step;

            $purchaseRequest->update([
                'status' => 'revision_to_requester_from_cost_control',
                'current_step' => 'requester',
                'current_status_at' => now(),
            ]);

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? $user->role_name ?? null,
                'action' => 'returned_to_requester_from_cost_control',
                'from_status' => $fromStatus,
                'to_status' => 'revision_to_requester_from_cost_control',
                'from_step' => $fromStep,
                'to_step' => 'requester',
                'remarks' => $validated['remarks'],
                'acted_at' => now(),
            ]);

            DB::commit();

            app(PurchasingLiteEmailService::class)->sendToRequester(
                purchaseRequest: $purchaseRequest,
                subject: 'PR Sent Back for Revision - ' . $purchaseRequest->pr_number,
                title: 'PR Sent Back by Cost Control',
                messageText: 'Cost Control has sent your purchase request back for revision.',
                buttonLabel: 'Open PR',
                buttonUrl: route('purchasing-lite.purchase-requests.show', $purchaseRequest),
                remarks: $validated['remarks']
            );

            return redirect()
                ->route('purchasing-lite.dashboard')
                ->with('success', 'PR has been returned to Requester.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => 'Failed to return PR to Requester. ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function rejectToRequester(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userHasCostControlAccess($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only Cost Control can reject this PR.');
        }

        if (! $this->purchaseRequestIsAtCostControlStep($purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'This PR is no longer waiting for Cost Control review.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $fromStatus = $purchaseRequest->status;
            $fromStep = $purchaseRequest->current_step;

            $purchaseRequest->update([
                'status' => 'rejected',
                'current_step' => 'requester',
                'current_status_at' => now(),
            ]);

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? $user->role_name ?? null,
                'action' => 'rejected_by_cost_control',
                'from_status' => $fromStatus,
                'to_status' => 'rejected',
                'from_step' => $fromStep,
                'to_step' => 'requester',
                'remarks' => $validated['remarks'],
                'acted_at' => now(),
            ]);

            DB::commit();

            app(PurchasingLiteEmailService::class)->sendToRolesAndRequester(
                purchaseRequest: $purchaseRequest,
                roles: ['purchasing'],
                subject: 'PR Rejected by Cost Control - ' . $purchaseRequest->pr_number,
                title: 'PR Rejected by Cost Control',
                messageText: 'Cost Control has rejected this purchase request.',
                buttonLabel: 'Open PR',
                buttonUrl: route('purchasing-lite.purchase-requests.show', $purchaseRequest),
                remarks: $validated['remarks']
            );

            return redirect()
                ->route('purchasing-lite.dashboard')
                ->with('success', 'PR has been rejected.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => 'Failed to reject PR. ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    private function userHasCostControlAccess($user): bool
    {
        $role = $this->normalizeStep($user->role ?? $user->role_name ?? '');

        return in_array($role, [
            'admin',
            'cost_control',
        ], true);
    }

    private function purchaseRequestIsAtCostControlStep(PurchaseRequest $purchaseRequest): bool
    {
        return $this->normalizeStep($purchaseRequest->current_step) === 'cost_control';
    }

    private function normalizeStep(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return str_replace(['-', ' '], '_', $value);
    }

    private function allItemsHaveCostControlSelection(PurchaseRequest $purchaseRequest): bool
    {
        $requiredItemIds = $purchaseRequest->items
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values();

        if ($requiredItemIds->isEmpty()) {
            return false;
        }

        $selectedItemIds = $purchaseRequest->vendorOffers
            ->flatMap(function ($vendorOffer) {
                return $vendorOffer->offerItems;
            })
            ->filter(function ($offerItem) {
                return str_contains((string) $offerItem->notes, 'SELECTED_BY_COST_CONTROL');
            })
            ->pluck('purchase_request_item_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        foreach ($requiredItemIds as $itemId) {
            if (! $selectedItemIds->contains((int) $itemId)) {
                return false;
            }
        }

        return true;
    }

    private function extractBidNumber(?string $notes): int
    {
        if (preg_match('/Bid\s+([1-3])/i', (string) $notes, $matches)) {
            return (int) $matches[1];
        }

        return 99;
    }

    private function removeSelectedMarker(?string $notes): string
    {
        $notes = (string) $notes;

        $notes = str_replace('| SELECTED_BY_COST_CONTROL', '', $notes);
        $notes = str_replace('SELECTED_BY_COST_CONTROL |', '', $notes);
        $notes = str_replace('SELECTED_BY_COST_CONTROL', '', $notes);

        return trim($notes);
    }
}
