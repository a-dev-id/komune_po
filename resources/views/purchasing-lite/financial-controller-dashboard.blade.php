@extends('layouts.purchasing-lite')

@section('title', 'Financial Controller Dashboard - Purchasing Lite')

@section('content')
@php
$formatRupiah = function ($value) {
return 'Rp ' . number_format((float) $value, 0, ',', '.');
};

$formatQty = function ($value) {
return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
};

$formatPriority = function ($priority) {
$priority = strtolower((string) ($priority ?: 'regular'));

return match ($priority) {
'urgent' => 'Urgent',
'important' => 'Important',
default => 'Regular',
};
};

$priorityBadgeClass = function ($priority) {
$priority = strtolower((string) ($priority ?: 'regular'));

return match ($priority) {
'urgent' => 'border-red-600 bg-red-50 text-red-900',
'important' => 'border-yellow-500 bg-yellow-50 text-yellow-900',
default => 'border-slate-400 bg-slate-100 text-slate-800',
};
};

$getVendorNameFromVendorId = function ($vendorId) {
if (! $vendorId || ! \Illuminate\Support\Facades\Schema::hasTable('vendors')) {
return null;
}

$vendor = \Illuminate\Support\Facades\DB::table('vendors')
->where('id', $vendorId)
->first();

return $vendor->name ?? null;
};

$getVendorNameFromRow = function ($row) use ($getVendorNameFromVendorId) {
foreach (['vendor_name', 'selected_vendor_name', 'supplier_name', 'seller_name'] as $column) {
if (isset($row->{$column}) && filled($row->{$column})) {
return $row->{$column};
}
}

foreach (['vendor_id', 'selected_vendor_id', 'supplier_id'] as $column) {
if (isset($row->{$column}) && filled($row->{$column})) {
$vendorName = $getVendorNameFromVendorId($row->{$column});

if (filled($vendorName)) {
return $vendorName;
}
}
}

$parentLookups = [
'purchase_request_vendor_offer_id' => [
'purchase_request_vendor_offers',
'purchase_request_offers',
'vendor_offers',
],
'vendor_offer_id' => [
'purchase_request_vendor_offers',
'purchase_request_offers',
'vendor_offers',
],
'offer_id' => [
'purchase_request_vendor_offers',
'purchase_request_offers',
'vendor_offers',
],
'purchase_request_vendor_id' => [
'purchase_request_vendors',
'purchase_request_vendor_offers',
'purchase_request_offers',
],
'vendor_bid_id' => [
'purchase_request_vendor_bids',
'purchase_request_bids',
'vendor_bids',
],
'bid_id' => [
'purchase_request_vendor_bids',
'purchase_request_bids',
'vendor_bids',
],
];

foreach ($parentLookups as $rowColumn => $parentTables) {
if (! isset($row->{$rowColumn}) || ! filled($row->{$rowColumn})) {
continue;
}

foreach ($parentTables as $parentTable) {
if (! \Illuminate\Support\Facades\Schema::hasTable($parentTable)) {
continue;
}

$parentRow = \Illuminate\Support\Facades\DB::table($parentTable)
->where('id', $row->{$rowColumn})
->first();

if (! $parentRow) {
continue;
}

foreach (['vendor_name', 'selected_vendor_name', 'supplier_name', 'seller_name', 'name'] as $column) {
if (isset($parentRow->{$column}) && filled($parentRow->{$column})) {
return $parentRow->{$column};
}
}

foreach (['vendor_id', 'selected_vendor_id', 'supplier_id'] as $column) {
if (isset($parentRow->{$column}) && filled($parentRow->{$column})) {
$vendorName = $getVendorNameFromVendorId($parentRow->{$column});

if (filled($vendorName)) {
return $vendorName;
}
}
}
}
}

return null;
};

$getSelectedVendorItem = function ($purchaseRequest, $item) use ($getVendorNameFromRow) {
$candidateTables = [
'purchase_request_offer_items',
'purchase_request_vendor_offer_items',
'purchase_request_vendor_items',
'purchase_request_vendor_bids',
'purchase_request_bids',
'vendor_bids',
];

foreach ($candidateTables as $table) {
if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
continue;
}

$query = \Illuminate\Support\Facades\DB::table($table);

if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'purchase_request_id')) {
$query->where('purchase_request_id', $purchaseRequest->id);
}

if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'purchase_request_item_id')) {
$query->where('purchase_request_item_id', $item->id);
} elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, 'item_id')) {
$query->where('item_id', $item->id);
}

if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'is_selected')) {
$query->where('is_selected', 1);
} elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, 'is_selected_by_cost_control')) {
$query->where('is_selected_by_cost_control', 1);
} elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, 'selected_by_cost_control')) {
$query->where('selected_by_cost_control', 1);
} elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, 'selected_offer_item_id')) {
$query->whereNotNull('selected_offer_item_id');
}

$row = $query->latest('id')->first();

if (! $row) {
continue;
}

$unitPrice =
$row->unit_price
?? $row->price
?? $row->offer_price
?? $row->vendor_price
?? 0;

$quantity =
$row->quantity
?? $row->qty
?? $item->quantity
?? 0;

$totalPrice =
$row->total_price
?? $row->total
?? ((float) $unitPrice * (float) $quantity);

return [
'vendor_name' => $getVendorNameFromRow($row) ?: '-',
'unit_price' => (float) $unitPrice,
'quantity' => (float) $quantity,
'total_price' => (float) $totalPrice,
];
}

return null;
};

$getFcStatusLabel = function ($status) {
$status = (string) $status;

if ($status === 'submitted_to_financial_controller') {
return 'New';
}

return ucwords(str_replace('_', ' ', $status));
};

$getNextFcAction = function ($status) {
$status = (string) $status;

if ($status === 'submitted_to_financial_controller') {
return [
'label' => 'On Progress',
'route' => 'purchasing-lite.purchase-requests.financial-controller.on-progress',
'class' => 'border border-blue-700 bg-white text-blue-800 hover:bg-blue-50',
'confirm' => 'Mark this PR as On Progress?',
];
}

if ($status === 'on_progress') {
return [
'label' => 'Waiting Payment',
'route' => 'purchasing-lite.purchase-requests.financial-controller.waiting-payment',
'class' => 'border border-yellow-700 bg-white text-yellow-800 hover:bg-yellow-50',
'confirm' => 'Mark this PR as Waiting Payment?',
];
}

if ($status === 'waiting_payment') {
return [
'label' => 'Paid to Vendor',
'route' => 'purchasing-lite.purchase-requests.financial-controller.paid-to-vendor',
'class' => 'bg-green-700 text-white hover:bg-green-800',
'confirm' => 'Mark this PR as Paid to Vendor and send it to Purchasing?',
];
}

return null;
};
@endphp

<section class="mb-6 border border-slate-300 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-950">
                Financial Controller Dashboard
            </h2>

            <p class="mt-1 text-base text-slate-600">
                Welcome, {{ $user->name }}.
            </p>
        </div>

        <a href="{{ route('purchasing-lite.purchase-requests.meeting-list') }}" class="inline-flex h-10 items-center justify-center border border-slate-950 bg-white px-6 text-sm font-bold text-slate-950 transition hover:bg-slate-100">
            All PR List
        </a>
    </div>
</section>

@if ($errors->any())
<section class="mb-6 border border-red-300 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
    <p class="mb-2 font-bold">Please check:</p>

    <ul class="list-inside list-disc space-y-1">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</section>
@endif

<section class="border border-slate-300 bg-white shadow-sm">
    <div class="border-b border-slate-300 px-5 py-4">
        <h3 class="text-lg font-bold text-slate-950">
            Purchase Request List
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-xs">
            <thead>
                <tr class="bg-slate-100">
                    <th class="w-12 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">No</th>
                    <th class="w-44 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">PR Number</th>
                    <th class="w-36 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Requester</th>
                    <th class="w-44 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Department</th>
                    <th class="w-32 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Date Needed</th>
                    <th class="w-32 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">PR Priority</th>
                    <th class="w-52 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">PR Title</th>
                    <th class="w-56 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Remarks</th>
                    <th class="w-20 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Photo</th>
                    <th class="min-w-[220px] align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Item</th>
                    <th class="w-20 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Qty</th>
                    <th class="w-20 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Unit</th>
                    <th class="min-w-[180px] align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Vendor</th>
                    <th class="w-32 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Price / Unit</th>
                    <th class="w-36 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Total</th>
                    <th class="w-40 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Grand Total</th>
                    <th class="w-40 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">FC Status</th>
                    <th class="w-44 align-middle border border-slate-300 px-2 py-2 text-center font-bold text-slate-800">Next Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($purchaseRequests ?? [] as $purchaseRequest)
                @php
                $prNumber = $purchaseRequest->pr_number ?? $purchaseRequest->request_number ?? '-';
                $items = $purchaseRequest->items ?? collect();
                $rowspan = max($items->count(), 1);
                $priority = strtolower((string) ($purchaseRequest->priority ?? 'regular'));

                $grandTotal = 0;

                foreach ($items as $checkItem) {
                $selectedVendorItem = $getSelectedVendorItem($purchaseRequest, $checkItem);

                if ($selectedVendorItem) {
                $grandTotal += (float) ($selectedVendorItem['total_price'] ?? 0);
                }
                }

                $fcStatus = (string) ($purchaseRequest->financial_controller_status ?? $purchaseRequest->status ?? 'submitted_to_financial_controller');
                $fcStatusLabel = $getFcStatusLabel($fcStatus);
                $nextAction = $getNextFcAction($fcStatus);
                @endphp

                @if ($items->count() > 0)
                @foreach ($items as $item)
                @php
                $itemPhotos = $item->item_photos;

                if (! is_array($itemPhotos) || count($itemPhotos) < 1) { $itemPhotos=$item->item_photo ? [$item->item_photo] : [];
                    }

                    $selectedVendorItem = $getSelectedVendorItem($purchaseRequest, $item);
                    @endphp

                    <tr>
                        @if ($loop->first)
                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 text-center font-bold text-slate-700">
                            {{ $loop->parent->iteration }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 font-bold text-slate-950">
                            {{ $prNumber }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 text-slate-800">
                            {{ $purchaseRequest->requester_name ?? '-' }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 text-slate-800">
                            {{ $purchaseRequest->department_name ?? '-' }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 text-center text-slate-800">
                            {{ $purchaseRequest->date_needed ? \Carbon\Carbon::parse($purchaseRequest->date_needed)->format('d M Y') : '-' }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 text-center">
                            <span class="inline-flex min-w-[90px] items-center justify-center border px-2 py-2 text-xs font-bold uppercase leading-tight {{ $priorityBadgeClass($priority) }}">
                                {{ $formatPriority($priority) }}
                            </span>
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 font-bold text-slate-950">
                            {{ $purchaseRequest->title }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle whitespace-pre-line border border-slate-300 px-2 py-3 text-slate-800">
                            {{ $purchaseRequest->requester_remarks ?: '-' }}
                        </td>
                        @endif

                        <td class="align-middle border border-slate-300 px-2 py-2">
                            @if (! empty($itemPhotos))
                            <div class="flex justify-center">
                                @foreach ($itemPhotos as $photo)
                                <a href="{{ asset('storage/' . ltrim($photo, '/')) }}" target="_blank" class="block">
                                    <img src="{{ asset('storage/' . ltrim($photo, '/')) }}" alt="" class="h-12 w-12 border border-slate-300 object-cover">
                                </a>
                                @break
                                @endforeach
                            </div>
                            @else
                            <span class="block text-center text-slate-400">-</span>
                            @endif
                        </td>

                        <td class="align-middle border border-slate-300 px-2 py-3 font-bold text-slate-950">
                            {{ $item->item_name }}
                        </td>

                        <td class="align-middle border border-slate-300 px-2 py-3 text-right text-slate-800">
                            {{ $formatQty($item->quantity) }}
                        </td>

                        <td class="align-middle border border-slate-300 px-2 py-3 text-slate-800">
                            {{ $item->unit ?: '-' }}
                        </td>

                        @if ($selectedVendorItem)
                        <td class="align-middle border border-slate-300 px-2 py-3 font-bold text-slate-950">
                            {{ $selectedVendorItem['vendor_name'] }}
                        </td>

                        <td class="align-middle border border-slate-300 px-2 py-3 text-right font-bold text-slate-950">
                            {{ $formatRupiah($selectedVendorItem['unit_price']) }}
                        </td>

                        <td class="align-middle border border-slate-300 px-2 py-3 text-right font-bold text-slate-950">
                            {{ $formatRupiah($selectedVendorItem['total_price']) }}
                        </td>
                        @else
                        <td colspan="3" class="align-middle border border-red-300 bg-red-50 px-2 py-3 text-center font-bold text-red-700">
                            No selected vendor
                        </td>
                        @endif

                        @if ($loop->first)
                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 bg-slate-50 px-2 py-3 text-right text-base font-bold text-slate-950">
                            {{ $formatRupiah($grandTotal) }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3 text-center font-bold text-slate-950">
                            {{ $fcStatusLabel }}
                        </td>

                        <td rowspan="{{ $rowspan }}" class="align-middle border border-slate-300 px-2 py-3">
                            @if ($nextAction)
                            <form method="POST" action="{{ route($nextAction['route'], $purchaseRequest) }}" onsubmit="return confirm('{{ $nextAction['confirm'] }}');">
                                @csrf

                                <button type="submit" class="inline-flex h-10 w-full items-center justify-center px-3 text-xs font-bold {{ $nextAction['class'] }}">
                                    {{ $nextAction['label'] }}
                                </button>
                            </form>
                            @else
                            <div class="border border-slate-300 bg-slate-50 px-3 py-3 text-center text-xs font-bold text-slate-500">
                                No Action
                            </div>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="18" class="align-middle border border-slate-300 px-4 py-8 text-center text-base text-slate-500">
                            No item data.
                        </td>
                    </tr>
                    @endif

                    @empty
                    <tr>
                        <td colspan="18" class="align-middle border border-slate-300 px-4 py-8 text-center text-base text-slate-500">
                            No PR waiting for Financial Controller.
                        </td>
                    </tr>
                    @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection