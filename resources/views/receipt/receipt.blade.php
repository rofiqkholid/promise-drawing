@extends('layouts.app')
@section('title', 'Receipt - PROMISE')
@section('header-title', 'Receipt')

@section('content')

<div class="w-full p-3 sm:p-4 lg:p-6 bg-gray-50 dark:bg-gray-900 font-sans" x-data="{ modalOpen: false }">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Receipt Management</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and download your shared drawing packages.</p>
        </div>

        <div class="flex items-center gap-3">
            <button id="btnOpenHistory"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-md border border-red-200 dark:border-red-900/30
                        bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all shadow-sm">
                <i class="fa-solid fa-clock-rotate-left"></i>
                History Expired
            </button>
        </div>
    </div>

    {{-- KPI Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['id' => 'totalReceived',      'label' => 'Total Received',  'icon' => 'fa-box-archive',    'color' => 'indigo'],
            ['id' => 'totalActive',        'label' => 'Active Access',   'icon' => 'fa-check-circle',   'color' => 'green'],
            ['id' => 'totalExpired',       'label' => 'Expired Links',   'icon' => 'fa-calendar-xmark', 'color' => 'red'],
            ['id' => 'totalReceivedToday', 'label' => 'Received Today',  'icon' => 'fa-clock',          'color' => 'blue']
        ] as $card)
        <div class="bg-white dark:bg-gray-800 p-4 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-4 shadow-sm group hover:border-{{ $card['color'] }}-100 dark:hover:border-{{ $card['color'] }}-500/30 transition-all">
            <div class="w-10 h-10 rounded-md bg-{{ $card['color'] }}-100 dark:bg-{{ $card['color'] }}-900/30 flex items-center justify-center text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400 group-hover:scale-110 transition-transform">
                <i class="fa-solid {{ $card['icon'] }}"></i>
            </div>
            <div>
                <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ $card['label'] }}</p>
                <p id="{{ $card['id'] }}" class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none mt-1">-</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm mb-6">
        <div class="flex flex-wrap items-end gap-3 px-1">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 px-0.5">Search Package</label>
                <div class="relative group">
                    <input type="text" id="custom-receipt-search" 
                        class="block w-full pl-9 pr-4 py-2 bg-gray-50/50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-md text-xs font-semibold focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-gray-100 group-hover:border-indigo-300 dark:group-hover:border-indigo-500/50 placeholder:font-normal"
                        placeholder="Part No, Model, or ECN...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i id="receipt-search-icon" class="fa-solid fa-magnifying-glass text-gray-400 text-[10px]"></i>
                    </div>
                </div>
            </div>
            
            @foreach([
                ['id' => 'customer', 'label' => 'Customer', 'w' => 'w-40'],
                ['id' => 'model', 'label' => 'Model', 'w' => 'w-48'],
                ['id' => 'document-type', 'label' => 'Doc Type', 'w' => 'w-40'],
                ['id' => 'category', 'label' => 'Category', 'w' => 'w-48']
            ] as $f)
            <div class="{{ $f['w'] }}">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 px-0.5">{{ $f['label'] }}</label>
                <select id="{{ $f['id'] }}" 
                        class="js-filter appearance-none block w-full px-3 py-2 text-xs font-semibold border border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/30 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="All" selected>All</option>
                </select>
            </div>
            @endforeach

            <div class="flex-shrink-0 mb-0.5">
                <button id="btnResetFilters" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-md transition-all h-[34px] w-[34px] flex items-center justify-center border border-transparent hover:border-indigo-100 dark:hover:border-indigo-500/30" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table id="receiptTable" class="w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50/50 dark:bg-gray-700/50 text-[10px] uppercase text-gray-500 font-bold text-left tracking-widest leading-none">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">No</th>
                        <th class="px-5 py-4 min-w-[300px]">Package Documents</th>
                        <th class="px-5 py-4 w-32 border-l border-gray-100/50 dark:border-gray-700/50">ECN Number</th>
                        <th class="px-5 py-4 w-20 text-center">Rev</th>
                        <th class="px-5 py-4 w-40 border-l border-gray-100/50 dark:border-gray-700/50">Received At</th>
                        <th class="px-5 py-4 w-40">Expires At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                </tbody>
            </table>
        </div>
    </div>

</div>


{{-- History Modal --}}
<div id="historyModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
     style="display: none;">

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-5xl mx-4 flex flex-col max-h-[90vh] border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Expired History</h3>
                <p class="text-[10px] text-gray-500 font-medium">List of packages that are no longer accessible</p>
            </div>
            <button type="button" class="btn-close-history text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 transition-colors">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>

        <div class="p-0 overflow-hidden flex-1 flex flex-col">
            <div class="overflow-y-auto flex-1">
                <table id="historyTable" class="w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 text-[9px] uppercase text-gray-500 font-bold text-left tracking-widest leading-none">
                        <tr>
                            <th class="px-5 py-3 w-12 text-center">No</th>
                            <th class="px-5 py-3 min-w-[200px]">Customer</th>
                            <th class="px-5 py-3">Model</th>
                            <th class="px-5 py-3">Part No</th>
                            <th class="px-5 py-3 w-20 text-center">Rev</th>
                            <th class="px-5 py-3">Received</th>
                            <th class="px-5 py-3 text-red-500">Expired At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700 text-[11px]">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
            <button type="button" class="btn-close-history px-6 py-1.5 text-[10px] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 uppercase tracking-widest transition-colors border border-gray-200 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
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

        function loadKpis() {
            const f = getCurrentFilters();
            const $cards = $('#totalReceived, #totalActive, #totalExpired, #totalReceivedToday');
            
            $cards.addClass('animate-pulse opacity-50');

            $.get('{{ route("receipts.kpi") }}', f, function(data) {
                const countUp = (id, val) => {
                    const $el = $('#' + id);
                    const current = parseInt($el.text()) || 0;
                    if (current === val) return;
                    
                    $({ val: current }).animate({ val: val }, {
                        duration: 600,
                        step: function() { $el.text(Math.ceil(this.val)); },
                        complete: function() { $el.text(val); }
                    });
                };

                countUp('totalReceived', data.totalReceived);
                countUp('totalActive', data.totalActive);
                countUp('totalExpired', data.totalExpired);
                countUp('totalReceivedToday', data.totalReceivedToday);
            }).always(() => {
                $cards.removeClass('animate-pulse opacity-50');
            });
        }

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
                        if (params.page === 1 && !results.some(r => r.id === 'All')) {
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
                    return $('<div class="text-[11px] font-semibold">' + (item.text || item.id) + '</div>');
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

        // Helper for highlighting search terms
        function highlightText(data, searchVal) {
            if (!searchVal || !data) return data;
            const safeSearch = searchVal.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${safeSearch})`, 'gi');
            return data.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 dark:text-gray-100 p-0">$1</mark>');
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

                // Default sort by Received Date (now index 4)
                order: [
                    [4, 'desc']
                ],

                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            const parts = [
                                row.customer,
                                row.model,
                                row.part_no,
                                row.doc_type,
                                row.category
                            ].filter(Boolean);

                            const combined = parts.join(' - ');
                            const searchVal = $('#receiptTable').DataTable().search(); // Use internal search if any, or custom search
                            const highlighted = highlightText(combined, $('#custom-receipt-search').val());

                            return `<div class="text-[11px] font-bold text-gray-900 dark:text-gray-100 tracking-tight leading-relaxed">${highlighted}</div>`;
                        }
                    },
                    {
                        data: 'ecn_no',
                        name: 'dpr.ecn_no',
                        defaultContent: '-',
                        render: function(data) {
                            const val = data ? data : '-';
                            return `<div class="text-[10px] font-mono text-gray-600 dark:text-gray-400">${val}</div>`;
                        }
                    },
                    {
                        data: null,
                        name: 'dpr.revision_no',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const revVal = row.revision ?? row.revision_no;
                            if (revVal === undefined || revVal === null || revVal === '') return '<span class="text-gray-400">—</span>';
                            return `<span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded text-[10px] font-bold">REV ${revVal}</span>`;
                        }
                    },
                    
                    {
                        data: 'received',
                        name: 'ps.shared_at',
                        render: function(v) {
                            const text = fmtDate(v);
                            return `<div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">${text || '—'}</div>`;
                        }
                    },
                    {
                        data: 'expired_at',
                        name: 'ps.expired_at',
                        render: function(v) {
                            if (!v) return '<span class="text-emerald-500 font-bold" title="Never Expire">∞ UNLIMITED</span>';
                            const text = fmtDate(v);
                            const isExpired = new Date(v) < new Date();
                            const colorClass = isExpired ? 'text-red-500 font-bold' : 'text-gray-500 dark:text-gray-400';
                            return `<div class="text-[10px] ${colorClass} font-mono">${text}</div>`;
                        }
                    }
                ],

                columnDefs: [{
                        targets: 0,
                        className: 'text-center text-[10px] font-bold text-gray-400',
                        width: '40px'
                    }
                ],

                responsive: true,
                dom: 't<"flex items-center justify-between mt-6 px-4"<"text-xs text-gray-500"i><"flex"p>>',
                createdRow: function(row) {
                    $(row).addClass('hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer');
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

            // Debounced Search Handler
            let searchTimer;
            $('#custom-receipt-search').on('keyup', function() {
                clearTimeout(searchTimer);
                const $icon = $('#receipt-search-icon');
                $icon.removeClass('fa-magnifying-glass').addClass('fa-spinner fa-spin text-indigo-500');
                
                searchTimer = setTimeout(() => {
                    table.search($(this).val()).draw();
                    loadKpis();
                    $icon.removeClass('fa-spinner fa-spin text-indigo-500').addClass('fa-magnifying-glass');
                }, 500);
            });
        }

        function bindHandlers() {
            $('#customer, #model, #document-type, #category').on('change', function() {
                if (table) table.ajax.reload(null, true);
                loadKpis();
            });

            $('#btnResetFilters').on('click', function() {
                $('#custom-receipt-search').val('');
                resetSelect2ToAll($('#customer'));
                resetSelect2ToAll($('#model'));
                resetSelect2ToAll($('#document-type'));
                resetSelect2ToAll($('#category'));

                if (table) {
                    table.search('').ajax.reload(null, true);
                }
                loadKpis();
            });

            $('#receiptTable tbody').on('click', 'tr', function() {
                const row = table.row(this).data();
                if (row && row.hash) {
                    window.location.href = `{{ url('/receipts') }}/${encodeURIComponent(row.hash)}`;
                }
            });
        }

        initTable();
        bindHandlers();
        loadKpis();

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
                        className: 'text-center text-[10px] font-bold text-gray-400'
                    },
                    {
                        data: 'customer',
                        name: 'c.code',
                        className: 'font-bold'
                    },
                    {
                        data: 'model',
                        name: 'm.name'
                    },
                    {
                        data: 'part_no',
                        name: 'p.part_no',
                        className: 'font-mono'
                    },
                    {
                        data: 'revision',
                        name: 'dpr.revision_no',
                        className: 'text-center',
                        render: function(v) {
                            return v ? `<span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded font-bold">REV ${v}</span>` : '-';
                        }
                    },
                    {
                        data: 'shared_at',
                        name: 'ps.shared_at',
                        render: function(v) {
                            return `<span class="font-mono text-gray-500">${fmtDate(v)}</span>`;
                        }
                    },
                    {
                        data: 'expired_at',
                        name: 'ps.expired_at',
                        className: 'text-red-500 font-bold font-mono',
                        render: function(v) {
                            return fmtDate(v);
                        }
                    }
                ],
                dom: 't<"flex items-center justify-between mt-4 px-4"<"text-[10px] text-gray-400"i><"flex"p>>',
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
            $historyModal.fadeIn(200);
            initHistoryTable();
        });

        $('body').on('click', '.btn-close-history', function() {
            $historyModal.fadeOut(200);
        });

        $historyModal.on('click', function(e) {
            if ($(e.target).is($historyModal)) {
                $(this).fadeOut(200);
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