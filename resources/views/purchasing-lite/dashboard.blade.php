@extends('layouts.purchasing-lite')

@section('title', 'Dashboard - Purchasing Lite')

@section('content')
<section class="mb-6 border border-slate-300 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-950">
                Dashboard
            </h2>

            <p class="mt-1 text-base text-slate-600">
                Welcome, {{ $user->name }}.
            </p>
        </div>

        @if (($user->role ?? '') === 'requester' || ($user->role ?? '') === 'admin')
        <a href="{{ route('purchasing-lite.purchase-requests.create') }}" class="inline-flex h-10 items-center justify-center bg-slate-950 px-6 text-sm font-bold text-white transition hover:bg-slate-800">
            + Create New PR
        </a>
        @endif
    </div>
</section>

<section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="border border-slate-300 bg-white p-5 shadow-sm">
        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
            Department
        </p>

        <p class="mt-3 text-2xl font-bold text-slate-950">
            {{ $user->department_name ?? '-' }}
        </p>
    </div>

    <div class="border border-slate-300 bg-white p-5 shadow-sm">
        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
            Waiting Action
        </p>

        <p class="mt-3 text-2xl font-bold text-slate-950">
            {{ $waitingAction ?? 0 }}
        </p>
    </div>

    <div class="border border-slate-300 bg-white p-5 shadow-sm">
        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">
            Total PR
        </p>

        <p class="mt-3 text-2xl font-bold text-slate-950">
            {{ $totalPr ?? 0 }}
        </p>
    </div>
</section>

<section class="border border-slate-300 bg-white shadow-sm">
    <div class="border-b border-slate-300 px-5 py-4">
        <h3 class="text-lg font-bold text-slate-950">
            Purchase Request List
        </h3>

        <p class="mt-1 text-sm text-slate-600">
            This table shows PR data based on your account.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="bg-slate-100">
                    <th class="w-16 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        No
                    </th>

                    <th class="w-44 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        PR Number
                    </th>

                    <th class="w-44 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Requester
                    </th>

                    <th class="min-w-[260px] border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Title
                    </th>

                    <th class="w-48 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Department
                    </th>

                    <th class="w-36 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Date Needed
                    </th>

                    <th class="w-36 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Items
                    </th>

                    <th class="w-40 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Status
                    </th>

                    <th class="w-44 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Current Step
                    </th>

                    <th class="w-36 border border-slate-300 px-4 py-3 text-center font-bold text-slate-800">
                        Action
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse ($purchaseRequests ?? [] as $purchaseRequest)
                <tr class="hover:bg-slate-50">
                    <td class="border border-slate-300 px-4 py-3 text-center font-bold text-slate-700">
                        {{ $loop->iteration }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 font-bold text-slate-900">
                        {{ $purchaseRequest->pr_number }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-slate-800">
                        {{ $purchaseRequest->requester_name ?? '-' }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-slate-800">
                        {{ $purchaseRequest->title }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-slate-800">
                        {{ $purchaseRequest->department_name ?? '-' }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-center text-slate-800">
                        {{ $purchaseRequest->date_needed ? $purchaseRequest->date_needed->format('d M Y') : '-' }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-center text-slate-800">
                        {{ $purchaseRequest->items_count ?? 0 }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-center">
                        <span class="inline-flex border border-slate-300 bg-slate-100 px-3 py-1 text-xs font-bold uppercase text-slate-700">
                            {{ str_replace('_', ' ', $purchaseRequest->status) }}
                        </span>
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-center text-slate-800">
                        {{ str_replace('_', ' ', $purchaseRequest->current_step) }}
                    </td>

                    <td class="border border-slate-300 px-4 py-3 text-center">
                        @if (($user->role ?? '') === 'purchasing' || ($user->role ?? '') === 'admin')
                        <a href="{{ route('purchasing-lite.purchase-requests.vendors', $purchaseRequest) }}" class="inline-flex h-9 items-center justify-center border border-slate-950 bg-white px-4 text-xs font-bold text-slate-950 transition hover:bg-slate-100">
                            Vendors
                        </a>
                        @else
                        <a href="{{ route('purchasing-lite.purchase-requests.show', $purchaseRequest) }}" class="inline-flex h-9 items-center justify-center border border-slate-950 bg-white px-4 text-xs font-bold text-slate-950 transition hover:bg-slate-100">
                            View
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="border border-slate-300 px-4 py-6 text-center text-base text-slate-500">
                        No purchase request data yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection