@extends('layouts.purchasing-lite')

@section('title', 'Create PR - Purchasing Lite')

@section('content')
<form method="POST" action="{{ route('purchasing-lite.purchase-requests.store') }}" enctype="multipart/form-data" autocomplete="off">
    @csrf

    <section class="mb-6 border border-slate-300 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-950">
                    Create New Purchase Request
                </h2>

                <p class="mt-1 text-base text-slate-600">
                    Fill in the item list below. The form is designed like Excel for easier input.
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

    <section class="border border-slate-300 bg-white shadow-sm">
        <div class="border-b border-slate-300 px-5 py-4">
            <h3 class="text-lg font-bold text-slate-950">
                PR Information
            </h3>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-4">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    Requester Name <span class="text-red-600">*</span>
                </label>

                <input type="text" name="requester_name" value="{{ old('requester_name') }}" placeholder="Type requester name" autocomplete="off" spellcheck="false" required class="h-11 w-full border border-slate-400 bg-white px-3 text-sm text-slate-900 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    Department
                </label>

                <input type="text" value="{{ $user->department_name ?? '-' }}" readonly class="h-11 w-full border border-slate-400 bg-white px-3 text-sm text-slate-900 outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    Date Needed <span class="text-red-600">*</span>
                </label>

                <input type="date" name="date_needed" value="{{ old('date_needed') }}" autocomplete="off" required class="h-11 w-full border border-slate-400 bg-white px-3 text-sm text-slate-900 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    PR Priority <span class="text-red-600">*</span>
                </label>

                <select name="priority" required class="h-11 w-full border border-slate-400 bg-white px-3 text-sm text-slate-900 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                    <option value="" disabled {{ old('priority') ? '' : 'selected' }}>
                        Select PR priority
                    </option>
                    <option value="regular" {{ old('priority')==='regular' ? 'selected' : '' }}>
                        Regular
                    </option>
                    <option value="important" {{ old('priority')==='important' ? 'selected' : '' }}>
                        Important
                    </option>
                    <option value="urgent" {{ old('priority')==='urgent' ? 'selected' : '' }}>
                        Urgent
                    </option>
                </select>
            </div>

            <div class="md:col-span-4">
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    PR Title <span class="text-red-600">*</span>
                </label>

                <input type="text" name="title" value="{{ old('title') }}" placeholder="Example: Monthly IT Supplies" autocomplete="off" spellcheck="false" required class="h-11 w-full border border-slate-400 bg-white px-3 text-sm text-slate-900 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
            </div>

            <div class="md:col-span-4">
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    Remarks
                </label>

                <textarea name="requester_remarks" rows="3" placeholder="Optional remarks" autocomplete="off" spellcheck="false" class="w-full border border-slate-400 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">{{ old('requester_remarks') }}</textarea>
            </div>
        </div>
    </section>

    <section class="relative z-10 mt-6 border border-slate-300 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-300 px-5 py-4">
            <div>
                <h3 class="text-lg font-bold text-slate-950">
                    Item List
                </h3>

                <p class="mt-1 text-sm text-slate-600">
                    Search existing items by typing the item name. If the item does not exist, it will be created when saving draft.
                </p>
            </div>

            <button type="button" id="add-item-row" class="inline-flex h-10 items-center justify-center bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-slate-800">
                + Add Item
            </button>
        </div>

        <div class="overflow-visible">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="w-16 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            No
                        </th>

                        <th class="min-w-[340px] border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Item Name
                        </th>

                        <th class="w-64 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Photos
                        </th>

                        <th class="min-w-[380px] border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Specification
                        </th>

                        <th class="w-32 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Qty
                        </th>

                        <th class="w-32 border border-slate-300 px-3 py-3 text-center font-bold text-slate-800">
                            Unit
                        </th>
                    </tr>
                </thead>

                <tbody id="item-table-body">
                    @php
                    $oldItems = old('items');

                    if (! is_array($oldItems) || count($oldItems) < 1) { $oldItems=[ 1=> [],
                        2 => [],
                        3 => [],
                        4 => [],
                        5 => [],
                        ];
                        }
                        @endphp

                        @foreach ($oldItems as $index => $oldItem)
                        @php
                        $photoInputId = 'item_photos_' . $loop->iteration;
                        @endphp

                        <tr data-item-row>
                            <td class="row-number border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">
                                {{ $loop->iteration }}
                            </td>

                            <td class="relative border border-slate-300 p-0">
                                <input type="text" name="items[{{ $loop->iteration }}][item_name]" value="{{ $oldItem['item_name'] ?? '' }}" autocomplete="off" spellcheck="false" autocorrect="off" autocapitalize="off" class="js-item-search h-10 w-full border-0 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-100">

                                <div class="js-item-results absolute left-0 right-0 top-full z-[9999] hidden border border-slate-300 bg-white shadow-lg"></div>
                            </td>

                            <td class="border border-slate-300 p-2 align-top">
                                <div class="flex items-start gap-2">
                                    <div class="js-item-photo-preview flex min-h-12 flex-1 flex-wrap gap-1"></div>

                                    <label for="{{ $photoInputId }}" class="js-item-photo-label inline-flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center bg-slate-950 text-lg font-bold leading-none text-white transition hover:bg-slate-800">
                                        +
                                    </label>

                                    <input id="{{ $photoInputId }}" type="file" name="items[{{ $loop->iteration }}][item_photos][]" accept="image/*" multiple class="js-item-photo-input hidden">
                                </div>
                            </td>

                            <td class="border border-slate-300 p-0">
                                <input type="text" name="items[{{ $loop->iteration }}][specification]" value="{{ $oldItem['specification'] ?? '' }}" autocomplete="off" spellcheck="false" autocorrect="off" autocapitalize="off" class="js-item-specification h-10 w-full border-0 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-100">
                            </td>

                            <td class="border border-slate-300 p-0">
                                <input type="text" name="items[{{ $loop->iteration }}][quantity]" value="{{ $oldItem['quantity'] ?? '' }}" min="0" step="0.01" autocomplete="off" class="h-10 w-full border-0 px-3 text-right text-sm outline-none focus:ring-2 focus:ring-blue-100">
                            </td>

                            <td class="border border-slate-300 p-0">
                                <input type="text" name="items[{{ $loop->iteration }}][unit]" value="{{ $oldItem['unit'] ?? 'pcs' }}" autocomplete="off" spellcheck="false" autocorrect="off" autocapitalize="off" class="js-item-unit h-10 w-full border-0 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-100">
                            </td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-300 p-5">
            <button type="submit" class="inline-flex h-10 items-center justify-center border border-slate-950 bg-white px-6 text-sm font-bold text-slate-950 transition hover:bg-slate-100">
                Save Draft
            </button>

            <button type="button" disabled class="inline-flex h-10 items-center justify-center bg-slate-400 px-6 text-sm font-bold text-white">
                Submit to Purchasing
            </button>
        </div>
    </section>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.getElementById('item-table-body');
        const addButton = document.getElementById('add-item-row');
        const searchUrl = @json(route('purchasing-lite.items.search'));

        let searchTimers = {};
        let rowCount = tableBody.querySelectorAll('[data-item-row]').length;

        function renumberRows() {
            tableBody.querySelectorAll('[data-item-row]').forEach(function (row, index) {
                const number = index + 1;

                row.querySelector('.row-number').textContent = number;

                row.querySelectorAll('input').forEach(function (input) {
                    input.name = input.name.replace(/items\[\d+\]/, 'items[' + number + ']');
                });

                const photoInput = row.querySelector('.js-item-photo-input');
                const photoLabel = row.querySelector('.js-item-photo-label');

                if (photoInput) {
                    photoInput.id = 'item_photos_' + number;
                }

                if (photoLabel) {
                    photoLabel.setAttribute('for', 'item_photos_' + number);
                }
            });

            rowCount = tableBody.querySelectorAll('[data-item-row]').length;
        }

        function closeAllResults() {
            document.querySelectorAll('.js-item-results').forEach(function (box) {
                box.classList.add('hidden');
                box.innerHTML = '';
            });

            document.querySelectorAll('[data-item-row] td.z-\\[9999\\]').forEach(function (cell) {
                cell.classList.remove('z-[9999]');
            });
        }

        function renderPreviewImage(previewBox, imageUrl) {
            if (! previewBox || ! imageUrl) {
                return;
            }

            const img = document.createElement('img');

            img.src = imageUrl;
            img.alt = '';
            img.className = 'h-12 w-12 border border-slate-300 object-cover';

            previewBox.appendChild(img);
        }

        function setupPhotoPreview(row) {
            const fileInput = row.querySelector('.js-item-photo-input');
            const previewBox = row.querySelector('.js-item-photo-preview');

            if (! fileInput || ! previewBox) {
                return;
            }

            fileInput.addEventListener('change', function () {
                previewBox.innerHTML = '';

                const files = Array.from(fileInput.files || []);

                if (! files.length) {
                    return;
                }

                files.forEach(function (file) {
                    renderPreviewImage(previewBox, URL.createObjectURL(file));
                });
            });
        }

        function setupItemSearch(row) {
            const input = row.querySelector('.js-item-search');
            const resultBox = row.querySelector('.js-item-results');
            const specificationInput = row.querySelector('.js-item-specification');
            const unitInput = row.querySelector('.js-item-unit');
            const previewBox = row.querySelector('.js-item-photo-preview');
            const wrapper = input.closest('td');

            input.addEventListener('focus', function () {
                if (wrapper) {
                    wrapper.classList.add('z-[9999]');
                }
            });

            input.addEventListener('input', function () {
                const query = input.value.trim();

                clearTimeout(searchTimers[input.name]);

                if (query.length < 2) {
                    resultBox.classList.add('hidden');
                    resultBox.innerHTML = '';
                    return;
                }

                searchTimers[input.name] = setTimeout(function () {
                    fetch(searchUrl + '?q=' + encodeURIComponent(query), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (items) {
                            resultBox.innerHTML = '';

                            if (! items.length) {
                                resultBox.innerHTML = '<div class="px-3 py-2 text-sm text-slate-500">No item found. It will be created when saved.</div>';
                                resultBox.classList.remove('hidden');

                                if (wrapper) {
                                    wrapper.classList.add('z-[9999]');
                                }

                                return;
                            }

                            items.forEach(function (item) {
                                const button = document.createElement('button');

                                button.type = 'button';
                                button.className = 'block w-full border-b border-slate-200 px-3 py-2 text-left text-sm hover:bg-slate-100';

                                button.innerHTML =
                                    '<div class="flex items-center gap-3">' +
                                        (item.image_url
                                            ? '<img src="' + escapeHtml(item.image_url) + '" class="h-10 w-10 border border-slate-300 object-cover" alt="">'
                                            : '<div class="h-10 w-10 border border-slate-300 bg-slate-100"></div>'
                                        ) +
                                        '<div>' +
                                            '<div class="font-bold text-slate-900">' + escapeHtml(item.name) + '</div>' +
                                            (item.unit ? '<div class="text-xs text-slate-500">Unit: ' + escapeHtml(item.unit) + '</div>' : '') +
                                        '</div>' +
                                    '</div>';

                                button.addEventListener('click', function () {
                                    input.value = item.name;

                                    if (item.specification) {
                                        specificationInput.value = item.specification;
                                    }

                                    if (item.unit) {
                                        unitInput.value = item.unit;
                                    }

                                    if (previewBox && item.image_url) {
                                        previewBox.innerHTML = '';
                                        renderPreviewImage(previewBox, item.image_url);
                                    }

                                    resultBox.classList.add('hidden');
                                    resultBox.innerHTML = '';

                                    if (wrapper) {
                                        wrapper.classList.remove('z-[9999]');
                                    }
                                });

                                resultBox.appendChild(button);
                            });

                            resultBox.classList.remove('hidden');

                            if (wrapper) {
                                wrapper.classList.add('z-[9999]');
                            }
                        });
                }, 250);
            });
        }

        function createRow() {
            rowCount++;

            const row = document.createElement('tr');

            row.setAttribute('data-item-row', '');

            row.innerHTML = `
                <td class="row-number border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">
                    ${rowCount}
                </td>

                <td class="relative border border-slate-300 p-0">
                    <input
                        type="text"
                        name="items[${rowCount}][item_name]"
                        autocomplete="off"
                        spellcheck="false"
                        autocorrect="off"
                        autocapitalize="off"
                        class="js-item-search h-10 w-full border-0 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    >

                    <div class="js-item-results absolute left-0 right-0 top-full z-[9999] hidden border border-slate-300 bg-white shadow-lg"></div>
                </td>

                <td class="border border-slate-300 p-2 align-top">
                    <div class="flex items-start gap-2">
                        <div class="js-item-photo-preview flex min-h-12 flex-1 flex-wrap gap-1"></div>

                        <label for="item_photos_${rowCount}" class="js-item-photo-label inline-flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center bg-slate-950 text-lg font-bold leading-none text-white transition hover:bg-slate-800">
                            +
                        </label>

                        <input
                            id="item_photos_${rowCount}"
                            type="file"
                            name="items[${rowCount}][item_photos][]"
                            accept="image/*"
                            multiple
                            class="js-item-photo-input hidden"
                        >
                    </div>
                </td>

                <td class="border border-slate-300 p-0">
                    <input
                        type="text"
                        name="items[${rowCount}][specification]"
                        autocomplete="off"
                        spellcheck="false"
                        autocorrect="off"
                        autocapitalize="off"
                        class="js-item-specification h-10 w-full border-0 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    >
                </td>

                <td class="border border-slate-300 p-0">
                    <input
                        type="text"
                        name="items[${rowCount}][quantity]"
                        min="0"
                        step="0.01"
                        autocomplete="off"
                        class="h-10 w-full border-0 px-3 text-right text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    >
                </td>

                <td class="border border-slate-300 p-0">
                    <input
                        type="text"
                        name="items[${rowCount}][unit]"
                        value="pcs"
                        autocomplete="off"
                        spellcheck="false"
                        autocorrect="off"
                        autocapitalize="off"
                        class="js-item-unit h-10 w-full border-0 px-3 text-sm outline-none focus:ring-2 focus:ring-blue-100"
                    >
                </td>
            `;

            tableBody.appendChild(row);
            setupItemSearch(row);
            setupPhotoPreview(row);
            renumberRows();

            const firstInput = row.querySelector('.js-item-search');

            if (firstInput) {
                firstInput.focus();
            }
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        tableBody.querySelectorAll('[data-item-row]').forEach(function (row) {
            setupItemSearch(row);
            setupPhotoPreview(row);
        });

        addButton.addEventListener('click', function () {
            createRow();
        });

        document.addEventListener('click', function (event) {
            if (! event.target.closest('.js-item-search') && ! event.target.closest('.js-item-results')) {
                closeAllResults();
            }
        });
    });
</script>
@endpush