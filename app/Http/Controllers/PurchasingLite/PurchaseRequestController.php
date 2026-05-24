<?php

namespace App\Http\Controllers\PurchasingLite;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function create()
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userCanCreatePr($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'You are not allowed to create a purchase request.');
        }

        return view('purchasing-lite.purchase-requests.create', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userCanCreatePr($user)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'You are not allowed to create a purchase request.');
        }

        $validated = $this->validatePrRequest($request);
        $items = $this->filledItems($validated);

        if ($items->isEmpty()) {
            return back()
                ->withErrors(['items' => 'Please add at least one item.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $purchaseRequest = PurchaseRequest::create([
                'pr_number' => $this->generatePrNumber(),
                'title' => $validated['title'],
                'requested_by' => $user->id,
                'requester_name' => $validated['requester_name'],
                'department_name' => $user->department_name ?? 'Unknown',
                'date_needed' => $validated['date_needed'] ?? null,
                'status' => 'draft',
                'current_step' => 'requester',
                'requester_remarks' => $validated['requester_remarks'] ?? null,
                'current_status_at' => now(),
            ]);

            $this->saveItems($purchaseRequest, $request, $items, $validated);

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? null,
                'action' => 'created_draft',
                'from_status' => null,
                'to_status' => 'draft',
                'from_step' => null,
                'to_step' => 'requester',
                'remarks' => 'Draft PR created.',
                'acted_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchasing-lite.purchase-requests.edit', $purchaseRequest)
                ->with('success', 'Draft PR has been saved. You can edit it before submitting.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to save draft. ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userCanEditDraft($user, $purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'You are not allowed to edit this purchase request.');
        }

        if ($purchaseRequest->status !== 'draft' || $purchaseRequest->current_step !== 'requester') {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only draft purchase requests can be edited.');
        }

        $purchaseRequest->load('items');

        return view('purchasing-lite.purchase-requests.edit', [
            'user' => $user,
            'purchaseRequest' => $purchaseRequest,
        ]);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userCanEditDraft($user, $purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'You are not allowed to update this purchase request.');
        }

        if ($purchaseRequest->status !== 'draft' || $purchaseRequest->current_step !== 'requester') {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'Only draft purchase requests can be updated.');
        }

        $validated = $this->validatePrRequest($request);
        $items = $this->filledItems($validated);

        if ($items->isEmpty()) {
            return back()
                ->withErrors(['items' => 'Please add at least one item.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $purchaseRequest->update([
                'title' => $validated['title'],
                'requester_name' => $validated['requester_name'],
                'date_needed' => $validated['date_needed'] ?? null,
                'requester_remarks' => $validated['requester_remarks'] ?? null,
                'current_status_at' => now(),
            ]);

            $existingItems = $purchaseRequest->items()
                ->get()
                ->keyBy('id');

            $purchaseRequest->items()->delete();

            $this->saveItems($purchaseRequest, $request, $items, $validated, $existingItems);

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? null,
                'action' => 'updated_draft',
                'from_status' => 'draft',
                'to_status' => 'draft',
                'from_step' => 'requester',
                'to_step' => 'requester',
                'remarks' => 'Draft PR updated.',
                'acted_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchasing-lite.purchase-requests.edit', $purchaseRequest)
                ->with('success', 'Draft PR has been updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to update draft. ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userCanViewPr($user, $purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'You are not allowed to view this purchase request.');
        }

        $purchaseRequest->load(['items', 'logs']);

        return view('purchasing-lite.purchase-requests.show', [
            'user' => $user,
            'purchaseRequest' => $purchaseRequest,
        ]);
    }

    public function submit(PurchaseRequest $purchaseRequest)
    {
        if (! Auth::check()) {
            return redirect('/purchasing-lite/login');
        }

        $user = Auth::user();

        if (! $this->userCanEditDraft($user, $purchaseRequest)) {
            return redirect('/purchasing-lite/dashboard')
                ->with('error', 'You are not allowed to submit this purchase request.');
        }

        if ($purchaseRequest->status !== 'draft' || $purchaseRequest->current_step !== 'requester') {
            return redirect()
                ->route('purchasing-lite.purchase-requests.edit', $purchaseRequest)
                ->with('error', 'Only draft purchase requests can be submitted.');
        }

        if ($purchaseRequest->items()->count() < 1) {
            return redirect()
                ->route('purchasing-lite.purchase-requests.edit', $purchaseRequest)
                ->with('error', 'Please add at least one item before submitting.');
        }

        DB::beginTransaction();

        try {
            $fromStatus = $purchaseRequest->status;
            $fromStep = $purchaseRequest->current_step;

            $purchaseRequest->update([
                'status' => 'submitted_to_purchasing',
                'current_step' => 'purchasing',
                'submitted_at' => now(),
                'current_status_at' => now(),
            ]);

            PurchaseRequestLog::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => $user->id,
                'role_name' => $user->role ?? null,
                'action' => 'submitted_to_purchasing',
                'from_status' => $fromStatus,
                'to_status' => 'submitted_to_purchasing',
                'from_step' => $fromStep,
                'to_step' => 'purchasing',
                'remarks' => 'PR submitted to Purchasing.',
                'acted_at' => now(),
            ]);

            DB::commit();

            return redirect('/purchasing-lite/dashboard')
                ->with('success', 'Purchase request has been submitted to Purchasing.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('purchasing-lite.purchase-requests.edit', $purchaseRequest)
                ->with('error', 'Failed to submit purchase request. ' . $e->getMessage());
        }
    }

    public function searchItems(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([]);
        }

        $query = trim((string) $request->get('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $items = Item::query()
            ->where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->limit(10)
            ->get([
                'id',
                'name',
                'default_specification',
                'default_unit',
                'image',
                'last_price',
                'currency',
            ]);

        return response()->json(
            $items->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'specification' => $item->default_specification,
                    'unit' => $item->default_unit,
                    'image' => $item->image,
                    'image_url' => $item->image ? asset('storage/' . ltrim($item->image, '/')) : null,
                    'last_price' => $item->last_price,
                    'currency' => $item->currency,
                ];
            })
        );
    }

    private function validatePrRequest(Request $request): array
    {
        return $request->validate([
            'requester_name' => ['required', 'string', 'max:191'],
            'date_needed' => ['nullable', 'date'],
            'title' => ['required', 'string', 'max:191'],
            'requester_remarks' => ['nullable', 'string'],

            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.item_name' => ['nullable', 'string', 'max:191'],
            'items.*.specification' => ['nullable', 'string'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],

            'items.*.item_photos' => ['nullable', 'array'],
            'items.*.item_photos.*' => ['nullable', 'image', 'max:2048'],

            'items.*.remove_photos' => ['nullable', 'array'],
            'items.*.remove_photos.*' => ['nullable', 'string'],
        ]);
    }

    private function filledItems(array $validated)
    {
        return collect($validated['items'] ?? [])
            ->filter(function ($item) {
                return trim((string) ($item['item_name'] ?? '')) !== '';
            });
    }

    private function saveItems(
        PurchaseRequest $purchaseRequest,
        Request $request,
        $items,
        array $validated,
        $existingItems = null
    ): void {
        $sortOrder = 1;

        foreach ($items as $itemIndex => $item) {
            $itemName = trim((string) ($item['item_name'] ?? ''));
            $specification = trim((string) ($item['specification'] ?? ''));
            $unit = trim((string) ($item['unit'] ?? ''));
            $quantity = (float) ($item['quantity'] ?? 0);

            $itemPhotoPaths = [];
            $existingItemId = (int) ($item['id'] ?? 0);

            if ($existingItems && $existingItemId > 0 && $existingItems->has($existingItemId)) {
                $existingItem = $existingItems->get($existingItemId);

                if (is_array($existingItem->item_photos) && count($existingItem->item_photos)) {
                    $itemPhotoPaths = $existingItem->item_photos;
                } elseif (! empty($existingItem->item_photo)) {
                    $itemPhotoPaths = [$existingItem->item_photo];
                }
            }

            $itemPhotoPaths = $this->normalizePhotoPaths($itemPhotoPaths);
            $removePhotoPaths = $this->normalizePhotoPaths($item['remove_photos'] ?? []);

            if (! empty($removePhotoPaths)) {
                $itemPhotoPaths = array_values(array_filter($itemPhotoPaths, function ($photoPath) use ($removePhotoPaths) {
                    return ! in_array($photoPath, $removePhotoPaths, true);
                }));
            }

            if ($request->hasFile("items.$itemIndex.item_photos")) {
                foreach ($request->file("items.$itemIndex.item_photos") as $photo) {
                    if ($photo) {
                        $itemPhotoPaths[] = $photo->store('purchase-request-items', 'public');
                    }
                }
            }

            $itemPhotoPaths = $this->normalizePhotoPaths($itemPhotoPaths);
            $itemPhotoPath = $itemPhotoPaths[0] ?? null;

            $masterItem = Item::whereRaw('LOWER(TRIM(name)) = ?', [
                strtolower($itemName),
            ])->first();

            if (! $masterItem) {
                Item::create([
                    'name' => $itemName,
                    'default_specification' => $specification ?: null,
                    'default_unit' => $unit ?: null,
                    'image' => $itemPhotoPath,
                    'currency' => 'IDR',
                    'is_active' => true,
                ]);
            } else {
                $updates = [];

                if (empty($masterItem->default_specification) && $specification !== '') {
                    $updates['default_specification'] = $specification;
                }

                if (empty($masterItem->default_unit) && $unit !== '') {
                    $updates['default_unit'] = $unit;
                }

                if (empty($masterItem->image) && $itemPhotoPath) {
                    $updates['image'] = $itemPhotoPath;
                }

                if (! empty($updates)) {
                    $masterItem->update($updates);
                }
            }

            PurchaseRequestItem::create([
                'purchase_request_id' => $purchaseRequest->id,
                'sort_order' => $sortOrder,
                'item_name' => $itemName,
                'specification' => $specification ?: null,
                'quantity' => $quantity > 0 ? $quantity : 1,
                'unit' => $unit ?: null,
                'item_photo' => $itemPhotoPath,
                'item_photos' => $itemPhotoPaths,
                'needed_date' => $validated['date_needed'] ?? null,
                'estimated_unit_price' => null,
                'estimated_total_price' => null,
                'requester_remarks' => null,
                'gm_status' => 'pending',
            ]);

            $sortOrder++;
        }
    }

    private function normalizePhotoPaths($photoPaths): array
    {
        if (! is_array($photoPaths)) {
            $photoPaths = [$photoPaths];
        }

        return array_values(array_filter(array_unique(array_map(function ($photoPath) {
            return ltrim(trim((string) $photoPath), '/');
        }, $photoPaths))));
    }

    private function userCanCreatePr($user): bool
    {
        $role = strtolower((string) ($user->role ?? ''));

        return in_array($role, ['requester', 'admin'], true);
    }

    private function userCanEditDraft($user, PurchaseRequest $purchaseRequest): bool
    {
        $role = strtolower((string) ($user->role ?? ''));

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'requester') {
            return (int) $purchaseRequest->requested_by === (int) $user->id;
        }

        return false;
    }

    private function userCanViewPr($user, PurchaseRequest $purchaseRequest): bool
    {
        $role = strtolower((string) ($user->role ?? ''));

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'requester') {
            return (int) $purchaseRequest->requested_by === (int) $user->id;
        }

        return $purchaseRequest->current_step === $role;
    }

    private function generatePrNumber(): string
    {
        $prefix = 'PR-' . now()->format('Ymd');

        $latest = PurchaseRequest::where('pr_number', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($latest) {
            $lastNumber = (int) substr($latest->pr_number, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
