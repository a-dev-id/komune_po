@extends('layouts.purchasing-lite')

@section('title', $purchaseRequest->pr_number . ' - Purchasing Lite')

@section('content')
<section class="mb-6 border border-slate-300 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-950">
                {{ $purchaseRequest->pr_number }}
            </h2>

            <p class="mt-1 text-base text-slate-600">
                {{ $purchaseRequest->title }}
            </p>
        </div>

        <a href="/purchasing-lite/dashboard" class="inline-flex h-10 items-center justify-center border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:bg-slate-50">
            Back
        </a>
    </div>
</section>

<section class="border border-slate-300 bg-white shadow-sm">
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
                {{ $purchaseRequest->date_needed ? $purchaseRequest->date_needed->format('d M Y') : '-' }}
            </p>
        </div>

        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                Status
            </p>

            <p class="mt-2 text-base font-bold capitalize text-slate-950">
                {{ str_replace('_', ' ', $purchaseRequest->status) }}
            </p>
        </div>

        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
                Current Step
            </p>

            <p class="mt-2 text-base font-bold capitalize text-slate-950">
                {{ str_replace('_', ' ', $purchaseRequest->current_step) }}
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
                Remarks
            </p>

            <p class="mt-2 whitespace-pre-line text-base text-slate-800">
                {{ $purchaseRequest->requester_remarks ?: '-' }}
            </p>
        </div>
    </div>
</section>

<section class="mt-6 border border-slate-300 bg-white shadow-sm">
    <div class="border-b border-slate-300 px-5 py-4">
        <h3 class="text-lg font-bold text-slate-950">
            Item List
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="bg-slate-100">
                    <th class="w-16 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                        No
                    </th>

                    <th class="w-72 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                        Photos
                    </th>

                    <th class="min-w-[300px] border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                        Item Name
                    </th>

                    <th class="min-w-[380px] border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                        Specification
                    </th>

                    <th class="w-28 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                        Qty
                    </th>

                    <th class="w-28 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                        Unit
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse ($purchaseRequest->items as $item)
                @php
                $photos = $item->item_photos;

                if (! is_array($photos) || count($photos) < 1) { $photos=$item->item_photo ? [$item->item_photo] : [];
                    }
                    @endphp

                    <tr>
                        <td class="border border-slate-300 px-3 py-3 text-center font-bold text-slate-700">
                            {{ $loop->iteration }}
                        </td>

                        <td class="border border-slate-300 px-3 py-3">
                            @if (count($photos))
                            <div class="flex flex-wrap gap-2">
                                @foreach ($photos as $photo)
                                <a href="{{ asset('storage/' . ltrim($photo, '/')) }}" target="_blank">
                                    <img src="{{ asset('storage/' . ltrim($photo, '/')) }}" alt="" class="h-16 w-16 border border-slate-300 object-cover">
                                </a>
                                @endforeach
                            </div>
                            @else
                            <span class="text-slate-400">No photo</span>
                            @endif
                        </td>

                        <td class="border border-slate-300 px-3 py-3 font-bold text-slate-900">
                            {{ $item->item_name }}
                        </td>

                        <td class="border border-slate-300 px-3 py-3 text-slate-800">
                            {{ $item->specification ?: '-' }}
                        </td>

                        <td class="border border-slate-300 px-3 py-3 text-right text-slate-800">
                            {{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}
                        </td>

                        <td class="border border-slate-300 px-3 py-3 text-slate-800">
                            {{ $item->unit ?: '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border border-slate-300 px-4 py-6 text-center text-base text-slate-500">
                            No item data.
                        </td>
                    </tr>
                    @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection