@extends('layouts.purchasing-lite')

@section('title', 'GM Review - Purchasing Lite')

@section('content')
@php
$selectedVendorItems = $selectedVendorItems ?? [];
$selectedGrandTotal = $selectedGrandTotal ?? 0;

$formatRupiah = function ($value) {
return 'Rp ' . number_format((float) $value, 0, ',', '.');
};

$formatQty = function ($value) {
return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
};

$statusLabel = ucwords(str_replace('_', ' ', (string) $purchaseRequest->status));
$currentStepLabel = ucwords(str_replace('_', ' ', (string) $purchaseRequest->current_step));
$prNumber = $purchaseRequest->pr_number ?? $purchaseRequest->request_number ?? '-';

$itemsCount = $purchaseRequest->items->count();
$canSelectItems = $itemsCount > 1;

$allItemsHaveSelectedVendor = true;

if ($itemsCount < 1) { $allItemsHaveSelectedVendor=false; } else { foreach ($purchaseRequest->items as $checkItem) {
    if (empty($selectedVendorItems[$checkItem->id])) {
    $allItemsHaveSelectedVendor = false;
    break;
    }
    }
    }

    $approveRoute = $canSelectItems
    ? route('purchasing-lite.purchase-requests.gm.split-approve', $purchaseRequest)
    : route('purchasing-lite.purchase-requests.gm.approve', $purchaseRequest);

    $requesterRemarksText = (string) ($purchaseRequest->requester_remarks ?? '');

    $parentPrNumber = null;

    if (preg_match('/Split(?:ted|ed)? from\s+([A-Za-z0-9\-]+)/i', $requesterRemarksText, $matches)) {
    $parentPrNumber = $matches[1] ?? null;
    }

    $isSplitPr =
    str_contains((string) $prNumber, '-S')
    || filled($parentPrNumber)
    || str_contains(strtolower($requesterRemarksText), 'split');

    $cleanGmSplitRemark = function ($remark) {
    $remark = trim((string) ($remark ?? ''));

    if ($remark === '') {
    return null;
    }

    $lowerRemark = strtolower($remark);

    if (
    str_starts_with($lowerRemark, 'split from ')
    || str_starts_with($lowerRemark, 'splited from ')
    || str_starts_with($lowerRemark, 'splitted from ')
    ) {
    return null;
    }

    return $remark;
    };

    $getRemarkFromLog = function ($log) use ($cleanGmSplitRemark) {
    return $cleanGmSplitRemark(
    $log->remarks
    ?? $log->remark
    ?? $log->notes
    ?? null
    );
    };

    $findGmSplitLogFromCollection = function ($logs) use ($getRemarkFromLog) {
    return collect($logs)
    ->filter(function ($log) use ($getRemarkFromLog) {
    $action = strtolower((string) ($log->action ?? ''));
    $remark = $getRemarkFromLog($log);

    return filled($remark)
    && (
    str_contains($action, 'gm')
    || str_contains($action, 'split')
    || str_contains($action, 'approve')
    );
    })
    ->sortByDesc(function ($log) {
    return $log->acted_at ?? $log->created_at;
    })
    ->first();
    };

    $getGmSplitRemarkFromModelLogs = function ($model) use ($findGmSplitLogFromCollection, $getRemarkFromLog) {
    if (! $model || ! method_exists($model, 'logs')) {
    return null;
    }

    if ($model->relationLoaded('logs')) {
    $latestLog = $findGmSplitLogFromCollection($model->logs);
    } else {
    $logs = $model->logs()
    ->where(function ($query) {
    $query->where('action', 'like', '%gm%')
    ->orWhere('action', 'like', '%split%')
    ->orWhere('action', 'like', '%approve%');
    })
    ->latest('acted_at')
    ->latest('created_at')
    ->limit(30)
    ->get();

    $latestLog = $findGmSplitLogFromCollection($logs);
    }

    return $latestLog ? $getRemarkFromLog($latestLog) : null;
    };

    $gmSplitRemark = $cleanGmSplitRemark(
    $purchaseRequest->gm_split_remarks
    ?? $purchaseRequest->gm_split_remark
    ?? $purchaseRequest->split_remarks
    ?? $purchaseRequest->split_remark
    ?? null
    );

    if (! filled($gmSplitRemark)) {
    $gmSplitRemark = $getGmSplitRemarkFromModelLogs($purchaseRequest);
    }

    $parentPurchaseRequest = null;
    $purchaseRequestModelClass = get_class($purchaseRequest);

    $parentIdFields = [
    'parent_purchase_request_id',
    'split_from_id',
    'source_purchase_request_id',
    'original_purchase_request_id',
    ];

    foreach ($parentIdFields as $parentIdField) {
    $parentIdValue = $purchaseRequest->{$parentIdField} ?? null;

    if (filled($parentIdValue)) {
    $parentPurchaseRequest = $purchaseRequestModelClass::query()->find($parentIdValue);
    break;
    }
    }

    if (! $parentPurchaseRequest && filled($parentPrNumber)) {
    $purchaseRequestTable = method_exists($purchaseRequest, 'getTable') ? $purchaseRequest->getTable() : null;

    $parentQuery = $purchaseRequestModelClass::query();

    $parentQuery->where(function ($query) use ($purchaseRequestTable, $parentPrNumber) {
    $hasCondition = false;

    if (
    $purchaseRequestTable
    && \Illuminate\Support\Facades\Schema::hasColumn($purchaseRequestTable, 'pr_number')
    ) {
    $query->where('pr_number', $parentPrNumber);
    $hasCondition = true;
    }

    if (
    $purchaseRequestTable
    && \Illuminate\Support\Facades\Schema::hasColumn($purchaseRequestTable, 'request_number')
    ) {
    if ($hasCondition) {
    $query->orWhere('request_number', $parentPrNumber);
    } else {
    $query->where('request_number', $parentPrNumber);
    }
    }
    });

    $parentPurchaseRequest = $parentQuery->first();
    }

    if (! filled($gmSplitRemark) && $parentPurchaseRequest) {
    $gmSplitRemark = $getGmSplitRemarkFromModelLogs($parentPurchaseRequest);
    }

    $showGmSplitRemark = $isSplitPr && filled($gmSplitRemark);
    @endphp

    <section class="mb-6 border border-slate-300 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-950">
                    GM Review
                </h2>

                <p class="mt-1 text-base text-slate-600">
                    {{ $prNumber }} - {{ $purchaseRequest->title }}
                </p>
            </div>

            <a href="/purchasing-lite/dashboard" class="inline-flex h-10 items-center justify-center border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:bg-slate-50">
                Back
            </a>
        </div>
    </section>

    @if ($errors->any())
    <section class="mb-6 border border-red-300 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
        <p class="mb-2 font-bold">Please check the form:</p>

        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </section>
    @endif

    @if (session('success'))
    <section class="mb-6 border border-green-300 bg-green-50 px-5 py-4 text-sm font-bold text-green-800">
        {{ session('success') }}
    </section>
    @endif

    @if (session('error'))
    <section class="mb-6 border border-red-300 bg-red-50 px-5 py-4 text-sm font-bold text-red-800">
        {{ session('error') }}
    </section>
    @endif

    <section class="mb-6 border border-slate-300 bg-white shadow-sm">
        <div class="border-b border-slate-300 px-5 py-4">
            <h3 class="text-lg font-bold text-slate-950">
                PR Information
            </h3>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    Requester Name
                </p>

                <p class="mt-2 text-base font-bold text-slate-950">
                    {{ $purchaseRequest->requester_name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    Department
                </p>

                <p class="mt-2 text-base font-bold text-slate-950">
                    {{ $purchaseRequest->department_name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    Date Needed
                </p>

                <p class="mt-2 text-base font-bold text-slate-950">
                    {{ $purchaseRequest->date_needed ? \Carbon\Carbon::parse($purchaseRequest->date_needed)->format('d M Y') : '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    Status
                </p>

                <p class="mt-2 text-base font-bold text-slate-950">
                    {{ $statusLabel }}
                </p>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    Current Step
                </p>

                <p class="mt-2 text-base font-bold text-slate-950">
                    {{ $currentStepLabel }}
                </p>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    Created At
                </p>

                <p class="mt-2 text-base font-bold text-slate-950">
                    {{ $purchaseRequest->created_at ? $purchaseRequest->created_at->format('d M Y H:i') : '-' }}
                </p>
            </div>

            <div class="md:col-span-3">
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    Requester Remarks
                </p>

                <div class="mt-2 min-h-14 border border-slate-300 bg-slate-50 px-3 py-3 text-sm leading-6 text-slate-800">
                    {!! nl2br(e($purchaseRequest->requester_remarks ?: '-')) !!}
                </div>
            </div>

            @if ($showGmSplitRemark)
            <div class="md:col-span-3">
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                    GM Split Remarks
                </p>

                <div class="mt-2 min-h-14 border border-orange-300 bg-orange-50 px-3 py-3 text-sm font-bold leading-6 text-orange-950">
                    {!! nl2br(e($gmSplitRemark)) !!}
                </div>
            </div>
            @endif
        </div>
    </section>

    <section class="mb-6 border border-slate-300 bg-white shadow-sm">
        <div class="border-b border-slate-300 px-5 py-4">
            <h3 class="text-lg font-bold text-slate-950">
                Cost Control Selected Vendor
            </h3>

            <p class="mt-1 text-sm text-slate-600">
                Review the vendor selected by Cost Control before approving this PR.
            </p>

            @if ($canSelectItems)
            <p class="mt-2 text-sm font-bold text-blue-800">
                Untick the item(s) GM does not want to approve, then click Approve. Unticked items will be split into a new PR and stay in GM review.
            </p>
            @endif
        </div>

        @if (! $allItemsHaveSelectedVendor)
        <div class="border-b border-red-300 bg-red-50 px-5 py-4 text-sm font-bold text-red-800">
            Selected vendor data is incomplete. Please return this PR to Cost Control.
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        @if ($canSelectItems)
                        <th class="w-24 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Approve
                        </th>
                        @endif

                        <th class="w-16 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            No
                        </th>

                        <th class="w-40 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Photos
                        </th>

                        <th class="min-w-[260px] border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Item Name
                        </th>

                        <th class="min-w-[260px] border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Specification
                        </th>

                        <th class="w-24 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Qty
                        </th>

                        <th class="w-24 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Unit
                        </th>

                        <th class="min-w-[220px] border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Selected Vendor
                        </th>

                        <th class="w-40 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Unit Price
                        </th>

                        <th class="w-44 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Total
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($purchaseRequest->items as $item)
                    @php
                    $itemPhotos = $item->item_photos;

                    if (! is_array($itemPhotos) || count($itemPhotos) < 1) { $itemPhotos=$item->item_photo ? [$item->item_photo] : [];
                        }

                        $selectedVendorItem = $selectedVendorItems[$item->id] ?? null;
                        @endphp

                        <tr>
                            @if ($canSelectItems)
                            <td class="border border-slate-300 px-3 py-3 text-center">
                                @if ($selectedVendorItem)
                                <input type="checkbox" name="approved_item_ids[]" value="{{ $item->id }}" form="approve-form" data-approve-item-checkbox checked class="h-5 w-5 cursor-pointer">
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            @endif

                            <td class="border border-slate-300 px-3 py-3 text-center font-bold text-slate-700">
                                {{ $loop->iteration }}
                            </td>

                            <td class="border border-slate-300 px-3 py-2 align-top">
                                @if (! empty($itemPhotos))
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($itemPhotos as $photo)
                                    <a href="{{ asset('storage/' . ltrim($photo, '/')) }}" target="_blank" class="block">
                                        <img src="{{ asset('storage/' . ltrim($photo, '/')) }}" alt="" class="h-12 w-12 border border-slate-300 object-cover">
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <td class="border border-slate-300 px-3 py-3 font-bold text-slate-900">
                                {{ $item->item_name }}
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-slate-800">
                                {{ $item->specification ?: '-' }}
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-right text-slate-800">
                                {{ $formatQty($item->quantity) }}
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-slate-800">
                                {{ $item->unit ?: '-' }}
                            </td>

                            @if ($selectedVendorItem)
                            <td class="border border-slate-300 px-3 py-3 font-bold text-slate-950">
                                {{ $selectedVendorItem['vendor_name'] ?? '-' }}
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-right font-bold text-slate-950">
                                {{ $formatRupiah($selectedVendorItem['unit_price'] ?? 0) }}
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-right font-bold text-slate-950">
                                {{ $formatRupiah($selectedVendorItem['total_price'] ?? 0) }}
                            </td>
                            @else
                            <td colspan="3" class="border border-red-300 bg-red-50 px-3 py-3 text-center font-bold text-red-700">
                                No selected vendor
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $canSelectItems ? 10 : 9 }}" class="border border-slate-300 px-4 py-6 text-center text-base text-slate-500">
                                No item data.
                            </td>
                        </tr>
                        @endforelse
                </tbody>

                <tfoot>
                    <tr class="bg-slate-100">
                        <td colspan="{{ $canSelectItems ? 9 : 8 }}" class="border border-slate-300 px-3 py-4 text-right text-base font-bold text-slate-950">
                            Grand Total
                        </td>

                        <td class="border border-slate-300 px-3 py-4 text-right text-base font-bold text-slate-950">
                            {{ $formatRupiah($selectedGrandTotal) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex flex-col justify-end gap-3 border-t border-slate-300 p-5 md:flex-row">
            <button type="button" data-open-modal="return-cost-control-modal" class="inline-flex h-10 items-center justify-center border border-blue-700 bg-white px-6 text-sm font-bold text-blue-800 transition hover:bg-blue-50">
                Return to Cost Control
            </button>

            <button type="button" data-open-modal="reject-requester-modal" class="inline-flex h-10 items-center justify-center border border-red-700 bg-white px-6 text-sm font-bold text-red-800 transition hover:bg-red-50">
                Reject PR
            </button>

            @if ($allItemsHaveSelectedVendor)
            <button type="button" data-open-modal="approve-modal" class="inline-flex h-10 items-center justify-center bg-green-700 px-6 text-sm font-bold text-white transition hover:bg-green-800">
                Approve
            </button>
            @else
            <button type="button" onclick="alert('Selected vendor data is incomplete. Please return this PR to Cost Control.')" class="inline-flex h-10 items-center justify-center bg-slate-400 px-6 text-sm font-bold text-white">
                Approve
            </button>
            @endif
        </div>
    </section>

    <div id="approve-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg border border-slate-300 bg-white shadow-xl">
            <div class="border-b border-slate-300 px-5 py-4">
                <h3 class="text-lg font-bold text-slate-950">
                    Approve PR
                </h3>

                @if ($canSelectItems)
                <p class="mt-1 text-sm text-slate-600">
                    Selected items will be approved and sent to Owner. Unticked items will be split into a new PR and stay in GM review.
                </p>
                @else
                <p class="mt-1 text-sm text-slate-600">
                    This will approve this PR and send it to Owner.
                </p>
                @endif
            </div>

            <form id="approve-form" method="POST" action="{{ $approveRoute }}" onsubmit="return validateApprove();">
                @csrf

                <div class="p-5">
                    @if ($canSelectItems)
                    <div class="mb-4 border border-blue-300 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-900">
                        Untick the item row(s) you do not want to approve. If all items remain ticked, the full PR will be approved.
                    </div>
                    @endif

                    <label class="mb-2 block text-sm font-bold text-slate-800">
                        Remarks
                    </label>

                    <textarea name="remarks" rows="5" class="w-full border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100" placeholder="Optional remarks for Owner."></textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-300 p-5">
                    <button type="button" data-close-modal class="inline-flex h-10 items-center justify-center border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:bg-slate-50">
                        Cancel
                    </button>

                    <button type="submit" class="inline-flex h-10 items-center justify-center bg-green-700 px-6 text-sm font-bold text-white transition hover:bg-green-800">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="return-cost-control-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg border border-slate-300 bg-white shadow-xl">
            <div class="border-b border-slate-300 px-5 py-4">
                <h3 class="text-lg font-bold text-slate-950">
                    Return to Cost Control
                </h3>

                <p class="mt-1 text-sm text-slate-600">
                    Please write why this PR needs to be returned to Cost Control.
                </p>
            </div>

            <form method="POST" action="{{ route('purchasing-lite.purchase-requests.gm.return-to-cost-control', $purchaseRequest) }}">
                @csrf

                <div class="p-5">
                    <label class="mb-2 block text-sm font-bold text-slate-800">
                        Remarks
                    </label>

                    <textarea name="remarks" rows="5" required class="w-full border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="Example: Please review the selected vendor or price again."></textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-300 p-5">
                    <button type="button" data-close-modal class="inline-flex h-10 items-center justify-center border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:bg-slate-50">
                        Cancel
                    </button>

                    <button type="submit" class="inline-flex h-10 items-center justify-center bg-blue-700 px-6 text-sm font-bold text-white transition hover:bg-blue-800">
                        Return to Cost Control
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="reject-requester-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg border border-slate-300 bg-white shadow-xl">
            <div class="border-b border-slate-300 px-5 py-4">
                <h3 class="text-lg font-bold text-slate-950">
                    Reject PR
                </h3>

                <p class="mt-1 text-sm text-slate-600">
                    Please write the rejection reason for the requester.
                </p>
            </div>

            <form method="POST" action="{{ route('purchasing-lite.purchase-requests.gm.reject-to-requester', $purchaseRequest) }}">
                @csrf

                <div class="p-5">
                    <label class="mb-2 block text-sm font-bold text-slate-800">
                        Rejection Reason
                    </label>

                    <textarea name="remarks" rows="5" required class="w-full border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100" placeholder="Example: PR rejected because the purchase is not approved."></textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-300 p-5">
                    <button type="button" data-close-modal class="inline-flex h-10 items-center justify-center border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:bg-slate-50">
                        Cancel
                    </button>

                    <button type="submit" class="inline-flex h-10 items-center justify-center bg-red-700 px-6 text-sm font-bold text-white transition hover:bg-red-800">
                        Reject PR
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
        const gmCanSelectItems = @json($canSelectItems);

    function validateApprove() {
        if (!gmCanSelectItems) {
            return confirm('Approve this PR and send it to Owner?');
        }

        const checkboxes = Array.from(document.querySelectorAll('[data-approve-item-checkbox]'));
        const checked = checkboxes.filter(function (checkbox) {
            return checkbox.checked;
        });

        if (checked.length < 1) {
            alert('Please tick at least one item to approve.');
            return false;
        }

        if (checked.length === checkboxes.length) {
            return confirm('Approve all selected items and send this PR to Owner?');
        }

        return confirm('Approve selected items and split unticked items into a new PR?');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-open-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                const modalId = button.getAttribute('data-open-modal');
                const modal = document.getElementById(modalId);

                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                const modal = button.closest('.fixed');

                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });

        document.querySelectorAll('.fixed').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    });
    </script>
    @endpush