@extends('layouts.app')
@section('title', 'Receipt - PROMISE')
@section('header-title', 'Receipt')

@section('content')

<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900" x-data="{ modalOpen: false }">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Receipt</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage Your File in Data Center</p>
        </div>

        <div class="mt-4 sm:mt-0">
            <button id="btnOpenHistory"
                    type="button"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border border-blue-300 dark:border-blue-700
                        bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                <i class="fa-solid fa-clock-rotate-left"></i>
                History Expired
            </button>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Filters</h3>
            <div class="flex items-center gap-2">
                <button id="btnResetFilters"
                        type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600
                            bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-rotate-left"></i>
                    Reset Filters
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-4">
            @foreach(['Customer', 'Model', 'Document Type', 'Category'] as $label)
            <div>
                <label for="{{ Str::slug($label) }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                <div class="relative mt-1">
                    <select id="{{ Str::slug($label) }}"
                            class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="All" selected>All</option>
                    </select>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table id="receiptTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Part No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ECN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rev</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doc Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Received</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expire Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
            </tbody>
        </table>
    </div>

</div>


<div id="historyModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75"
     style="display: none;">

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl mx-4 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Expired History</h3>
                <p class="text-xs text-gray-500">List of packages that are no longer accessible.</p>
            </div>
            <button type="button" class="btn-close-history text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fa-solid fa-times fa-lg"></i>
            </button>
        </div>

        <div class="p-4 overflow-y-auto flex-1">
            <table id="historyTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Part No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rev</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expired At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t dark:border-gray-700 flex justify-end">
            <button type="button" class="btn-close-history px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        let table;
        const ENDPOINT = '{{ route("receipts.filters") }}';

        function resetSelect2ToAll($el) {
            $el.empty();
            const opt = new Option('All', 'All', true, true);
            $el.append(opt);
            $el.trigger('change');
            $el.trigger('select2:select');
        }

        function makeSelect2($el, field, extraParamsFn) {
            $el.select2({
                width: '100%',
                placeholder: 'All',
                allowClear: false,
                minimumResultsForSearch: 0,
                ajax: {
                    url: ENDPOINT,
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function(params) {
                        const p = {
                            select2: field,
                            q: params.term || '',
                            page: params.page || 1
                        };
                        if (typeof extraParamsFn === 'function') {
                            Object.assign(p, extraParamsFn());
                        }
                        return p;
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        const results = Array.isArray(data.results) ? data.results.slice() : [];
                        if (!results.some(r => r.id === 'All')) {
                            results.unshift({
                                id: 'All',
                                text: 'All'
                            });
                        }
                        return {
                            results,
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    }
                },
                templateResult: function(item) {
                    if (item.loading) return item.text;
                    return $('<div class="text-sm">' + (item.text || item.id) + '</div>');
                },
                templateSelection: function(item) {
                    return item.text || item.id || 'All';
                }
            });
        }

        makeSelect2($('#customer'), 'customer');
        makeSelect2($('#model'), 'model', () => ({
            customer_code: $('#customer').val() || ''
        }));
        makeSelect2($('#document-type'), 'doc_type');
        makeSelect2($('#category'), 'category', () => ({
            doc_type: $('#document-type').val() || ''
        }));

        $('#customer').on('change', function() {
            resetSelect2ToAll($('#model'));
        });
        $('#document-type').on('change', function() {
            resetSelect2ToAll($('#category'));
        });

        function getCurrentFilters() {
            const valOrAll = v => (v && v.length ? v : 'All');
            return {
                customer: valOrAll($('#customer').val()),
                model: valOrAll($('#model').val()),
                doc_type: valOrAll($('#document-type').val()),
                category: valOrAll($('#category').val()),
            };
        }

        function fmtDate(v) {
            if (!v) return '';
            const d = new Date(v);
            if (isNaN(d)) return v;
            const pad = n => n.toString().padStart(2, '0');
            const dd = pad(d.getDate());
            const MM = pad(d.getMonth() + 1);
            const yyyy = d.getFullYear();
            const HH = pad(d.getHours());
            const mm = pad(d.getMinutes());
            return `${dd}-${MM}-${yyyy} ${HH}:${mm}`;
        }


        function initTable() {
            table = $('#receiptTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("receipts.list") }}',
                    type: 'GET',
                    data: function(d) {
                        const f = getCurrentFilters();
                        d.customer = f.customer;
                        d.model = f.model;
                        d.doc_type = f.doc_type;
                        d.category = f.category;
                    }
                },

                // Default sort by Received Date (now index 8)
                order: [
                    [8, 'desc']
                ],

                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'customer',
                        name: 'c.code'
                    },
                    {
                        data: 'model',
                        name: 'm.name'
                    },
                    {
                        data: 'part_no',
                        name: 'p.part_no'
                    },
                    {
                        data: 'ecn_no',
                        name: 'dpr.ecn_no',
                        defaultContent: '-',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: null,
                        name: 'dpr.revision_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            const revVal = row.revision ?? row.revision_no;
                            return (revVal !== undefined && revVal !== null && revVal !== '') ? revVal : '<span class="text-gray-400">—</span>';
                        }
                    },
                    {
                        data: 'doc_type',
                        name: 'dtg.name'
                    },
                    {
                        data: 'category',
                        name: 'dsc.name'
                    },
                    
                    {
                        data: 'received',
                        name: 'ps.shared_at',
                        render: function(v) {
                            const text = fmtDate(v);
                            return `<span title="${v || ''}">${text}</span>`;
                        }
                    },
                    {
                        data: 'expired_at',
                        name: 'ps.expired_at',
                        render: function(v) {
                            if (!v) return '<span class="text-green-600 font-bold" title="Never Expire">∞</span>';
                            const text = fmtDate(v);
                            // Cek jika expired
                            const isExpired = new Date(v) < new Date();
                            const colorClass = isExpired ? 'text-red-600 font-semibold' : 'text-gray-700 dark:text-gray-300';
                            return `<span class="${colorClass}">${text}</span>`;
                        }
                    }
                ],

                columnDefs: [{
                        targets: 0,
                        className: 'text-center w-12',
                        width: '48px'
                    },
                    {
                        targets: [1, 2, 5, 6], // Customer, Model, Part No, ECN
                        className: 'whitespace-nowrap text-sm'
                    },
                    {
                        targets: [3, 4], // Doc Type, Category
                        className: 'text-sm'
                    },
                    {
                        targets: 7, // Rev
                        className: 'text-center text-sm'
                    },
                    {
                        targets: [8, 9], // Received, Expire
                        className: 'whitespace-nowrap text-sm'
                    }
                ],

                responsive: true,
                dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
                createdRow: function(row) {
                    $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer');
                }
            });

            table.on('draw.dt', function() {
                const info = table.page.info();
                table.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + info.start;
                });
            });
        }

        function bindHandlers() {
            $('#customer, #model, #document-type, #category').on('change', function() {
                if (table) table.ajax.reload(null, true);
            });

            $('#btnResetFilters').on('click', function() {
                resetSelect2ToAll($('#customer'));
                resetSelect2ToAll($('#model'));
                resetSelect2ToAll($('#document-type'));
                resetSelect2ToAll($('#category'));

                if (table) table.ajax.reload(null, true);
            });

            $('#receiptTable tbody').on('click', 'tr', function() {
                const row = table.row(this).data();
                if (row && row.hash) {
                    window.location.href = `{{ url('/receipts') }}/${encodeURIComponent(row.hash)}`;
                }
            }).on('mouseenter', 'tr', function() {
                $(this).css('cursor', 'pointer');
            });
        }

        initTable();
        bindHandlers();

        // --- LOGIKA HISTORY TABLE ---
        let historyTable;

        function initHistoryTable() {
            if (historyTable) {
                historyTable.ajax.reload();
                return;
            }

            historyTable = $('#historyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("receipts.history_list") }}',
                    type: 'GET'
                },
                order: [
                    [6, 'desc']
                ], // Default sort by Expired At
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center w-10'
                    },
                    {
                        data: 'customer',
                        name: 'c.code'
                    },
                    {
                        data: 'model',
                        name: 'm.name'
                    },
                    {
                        data: 'part_no',
                        name: 'p.part_no'
                    },
                    {
                        data: 'revision',
                        name: 'dpr.revision_no',
                        render: function(v) {
                            return v ? 'Rev ' + v : '-';
                        }
                    },
                    {
                        data: 'shared_at',
                        name: 'ps.shared_at',
                        render: function(v) {
                            return fmtDate(v);
                        }
                    },
                    {
                        data: 'expired_at',
                        name: 'ps.expired_at',
                        className: 'text-red-500 font-medium',
                        render: function(v) {
                            return fmtDate(v);
                        }
                    }
                ],
                drawCallback: function(settings) {
                    const api = this.api();
                    api.column(0, {
                        page: 'current'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1 + api.page.info().start;
                    });
                }
            });
        }

        // Event Handlers untuk Modal History
        const $historyModal = $('#historyModal');

        $('#btnOpenHistory').on('click', function() {
            $historyModal.show();
            initHistoryTable();
        });

        $('body').on('click', '.btn-close-history', function() {
            $historyModal.hide();
        });

        $historyModal.on('click', function(e) {
            if ($(e.target).is($historyModal)) {
                $(this).hide();
            }
        });
    });
</script>
@endpush

@push('style')
<style>
    [x-collapse] { @apply overflow-hidden; }
    [x-cloak] { display: none !important; }

    /* =========================================
       DATATABLES DARK MODE FIX
       ========================================= */
    
    /* Mengubah warna teks label (Show, Search, Info, Pagination) */
    .dark .dataTables_wrapper .dataTables_length,
    .dark .dataTables_wrapper .dataTables_filter,
    .dark .dataTables_wrapper .dataTables_info,
    .dark .dataTables_wrapper .dataTables_processing,
    .dark .dataTables_wrapper .dataTables_paginate {
        color: #d1d5db !important; /* text-gray-300 */
    }

    /* Mengubah warna teks label di dalam Pagination button */
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #d1d5db !important;
    }
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #6b7280 !important; /* text-gray-500 */
    }

    /* Memperbaiki Input Search dan Select Length di Mode Gelap */
    .dark .dataTables_wrapper input[type="search"],
    .dark .dataTables_wrapper select {
        background-color: #374151; /* bg-gray-700 */
        color: #f3f4f6;            /* text-gray-100 */
        border: 1px solid #4b5563; /* border-gray-600 */
        border-radius: 0.375rem;   /* rounded-md */
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
    }

    /* Placeholder text di input search (opsional) */
    .dark .dataTables_wrapper input[type="search"]::placeholder {
        color: #9ca3af;
    }
    
    /* Hapus background putih default pada select option */
    .dark .dataTables_wrapper select option {
        background-color: #374151;
        color: #f3f4f6;
    }
</style>
@endpush