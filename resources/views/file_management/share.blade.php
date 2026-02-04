@extends('layouts.app')
@section('title', 'Share Packages - PROMISE')
@section('header-title', 'Share Packages')

@section('content')
<nav class="flex px-5 py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">

        <li class="inline-flex items-center">
            <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600">
                Monitoring
            </a>
        </li>

        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>

                <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded">
                    Share Packages
                </span>
            </div>
        </li>
    </ol>
</nav>
    <style>
        /* Clean Select2 Input */
        .ms-style-select2 .select2-container--default .select2-selection--multiple {
            border: 1px solid #e5e7eb;
            background-color: #f9fafb !important;
            border-radius: 0.375rem;
            min-height: 42px;
            padding: 4px 8px;
        }
        .dark .ms-style-select2 .select2-container--default .select2-selection--multiple {
            border-color: #374151;
            background-color: #1f2937 !important;
        }
        .ms-style-select2 .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #6366f1;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.1);
        }
        /* Hide default Select2 chips as we will render them externally */
        .ms-style-select2 .select2-selection__choice {
            display: none !important;
        }
        .ms-style-select2 .select2-search__field {
            font-size: 13px !important;
            margin-top: 0 !important;
            padding: 0 !important;
            height: 32px !important;
            color: #374151 !important;
        }
        .dark .ms-style-select2 .select2-search__field {
            color: #e5e7eb !important;
        }
    </style>
</head>
<div class="w-full p-3 sm:p-4 lg:p-6 bg-gray-50 dark:bg-gray-900 font-sans">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Share Packages</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Share and monitor package distribution.</p>
        </div>
    </div>

    {{-- KPI Row (Full Width Top) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['id' => 'totalShared',   'label' => 'Total Shared',  'icon' => 'fa-share-nodes',    'color' => 'indigo'],
            ['id' => 'totalActive',   'label' => 'Active',        'icon' => 'fa-check-circle',   'color' => 'green'],
            ['id' => 'totalExpired',  'label' => 'Expired',       'icon' => 'fa-clock-rotate-left', 'color' => 'red'],
            ['id' => 'totalRequest',  'label' => 'Pending Request','icon' => 'fa-envelope-open-text', 'color' => 'blue']
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

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Sidebar History (Left) --}}
        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm sticky top-24">
                <div class="flex items-center justify-between mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 bg-indigo-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-[11px] uppercase tracking-wider">Activity Log</h3>
                    </div>
                    <button onclick="loadHistory()" class="text-[10px] text-gray-400 hover:text-indigo-500 transition-colors">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>

                <div id="shareHistoryList" class="space-y-4 max-h-[calc(100vh-320px)] overflow-y-auto no-scrollbar pr-1">
                    <div class="flex items-center justify-center py-10 opacity-30">
                        <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Content Segment (Right) --}}
        <main class="flex-1 min-w-0 space-y-6">
            {{-- Filter Bar (Inside Right Segment) --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex flex-wrap items-end gap-3 px-1">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 px-0.5">Search Package</label>
                        <div class="relative group">
                            <input type="text" id="custom-share-search" 
                                class="block w-full pl-9 pr-4 py-2 bg-gray-50/50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-md text-xs font-semibold focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-gray-100 group-hover:border-indigo-300 dark:group-hover:border-indigo-500/50 placeholder:font-normal"
                                placeholder="ECN, Part No, or Model...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i id="share-search-icon" class="fa-solid fa-magnifying-glass text-gray-400 text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    
                    @foreach([
                        ['id' => 'customer', 'label' => 'Customer', 'w' => 'w-32'],
                        ['id' => 'model', 'label' => 'Model', 'w' => 'w-36'],
                        ['id' => 'document-type', 'label' => 'Doc Type', 'w' => 'w-32'],
                        ['id' => 'status', 'label' => 'Status', 'w' => 'w-28']
                    ] as $f)
                    <div class="{{ $f['w'] }}">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 px-0.5">{{ $f['label'] }}</label>
                        <select id="{{ $f['id'] }}" class="js-filter w-full"></select>
                    </div>
                    @endforeach

                    <div class="flex-shrink-0 mb-0.5">
                        <button id="btnResetFilters" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-md transition-all h-[34px] w-[34px] flex items-center justify-center border border-transparent hover:border-indigo-100 dark:hover:border-indigo-500/30" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="approvalTable" class="w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50/50 dark:bg-gray-700/50 text-[10px] uppercase text-gray-500 font-bold text-left tracking-widest leading-none">
                            <tr>
                                <th class="px-5 py-4 w-12 text-center">No</th>
                                <th class="px-5 py-4 min-w-[280px]">Package Documents</th>
                                <th class="px-5 py-4 w-28 text-center border-l border-gray-100/50 dark:border-gray-700/50">Status</th>
                                <th class="px-5 py-4 w-36">Request Date</th>
                                <th class="px-5 py-4 w-36">Decision Date</th>
                                <th class="px-5 py-4 w-32 border-l border-gray-100/50 dark:border-gray-700/50">Recipients</th>
                                <th class="px-5 py-4 w-20 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    {{-- Adjusted Share Modal --}}
    <div id="shareModal"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60"
        style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-2xl w-full max-w-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Share Package</h3>
                    <p class="text-[10px] text-gray-500 font-medium">Distribute drawings to suppliers</p>
                </div>
                <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 transition-colors p-2">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-5">
                {{-- Package Context --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 rounded-md">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <i class="fa-solid fa-file-shield text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p id="modal-package-name" class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate"></p>
                            <p id="modal-package-info" class="text-[10px] text-gray-500 uppercase tracking-widest leading-none mt-1"></p>
                        </div>
                    </div>
                </div>

                {{-- Recipient Selection --}}
                <div class="space-y-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Add Recipients</label>
                        <div class="ms-style-select2 relative group">
                            <select id="supplierListContainer" name="supplierListContainer" class="w-full" multiple="multiple"></select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Selected Recipients Container --}}
                    <div id="externalRecipientList" class="max-h-[150px] overflow-y-auto space-y-1 p-1 no-scrollbar">
                        {{-- Items injected via JS --}}
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                <button type="button" class="btn-close-modal text-[10px] font-bold text-gray-500 hover:text-gray-700 uppercase tracking-widest transition-colors">
                    Cancel
                </button>
                <button id="btnSaveShare" type="button" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-md shadow-sm transition-all uppercase tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                    <span>Notify & Share</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Adjusted Access Manager Modal --}}
    <div id="shareDetailsModal"
        class="fixed inset-0 z-[101] flex items-center justify-center bg-gray-900/60"
        style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-2xl w-full max-w-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Distribution List</h3>
            </div>
            
            <div class="p-4 overflow-y-auto max-h-[50vh] bg-gray-50 dark:bg-gray-900/50 space-y-2 no-scrollbar" id="shareDetailsBody">
                <!-- Data via JS -->
            </div>

            <div class="px-5 py-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button type="button" class="btn-close-share-details px-4 py-1.5 text-[10px] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 uppercase tracking-widest transition-colors border border-gray-200 dark:border-gray-600 rounded-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {

        function detectTheme() {
            const isDark = document.documentElement.classList.contains('dark');

            return isDark ? {
                mode: 'dark',
                bg: 'rgba(30, 41, 59, 0.95)',
                fg: '#E5E7EB',
                border: 'rgba(71, 85, 105, 0.5)',
                progress: 'rgba(255,255,255,.9)',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444',
                    warning: '#f59e0b',
                    info: '#3b82f6'
                }
            } : {
                mode: 'light',
                bg: 'rgba(255, 255, 255, 0.98)',
                fg: '#0f172a',
                border: 'rgba(226, 232, 240, 1)',
                progress: 'rgba(15,23,42,.8)',
                icon: {
                    success: '#16a34a',
                    error: '#dc2626',
                    warning: '#d97706',
                    info: '#2563eb'
                }
            };
        }

        const BaseToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2600,
            timerProgressBar: true,
            showClass: {
                popup: 'swal2-animate-toast-in'
            },
            hideClass: {
                popup: 'swal2-animate-toast-out'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        function renderToast({
            icon = 'success',
            title = 'Success',
            text = ''
        } = {}) {
            const t = detectTheme();

            BaseToast.fire({
                icon,
                title,
                text,
                iconColor: t.icon[icon] || t.icon.success,
                background: t.bg,
                color: t.fg,
                customClass: {
                    popup: 'swal2-toast border',
                    title: '',
                    timerProgressBar: ''
                },
                didOpen: (toast) => {
                    const bar = toast.querySelector('.swal2-timer-progress-bar');
                    if (bar) bar.style.background = t.progress;
                    const popup = toast.querySelector('.swal2-popup');
                    if (popup) popup.style.borderColor = t.border;
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        }

        function toastSuccess(title = 'Success', text = 'Operation completed successfully.') {
            renderToast({
                icon: 'success',
                title,
                text
            });
        }

        function toastError(title = 'Error', text = 'Something went wrong.') {
            renderToast({
                icon: 'error',
                title,
                text
            });
        }

        function toastWarning(title = 'Warning', text = 'Please check your input.') {
            renderToast({
                icon: 'warning',
                title,
                text
            });
        }


        let table;
        const ENDPOINT = '{{ route("share.filters") }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

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
        makeSelect2($('#status'), 'status');

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
                status: valOrAll($('#status').val()),
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
            table = $('#approvalTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("share.list") }}',
                    type: 'GET',
                    data: function(d) {
                        const f = getCurrentFilters();
                        d.customer = f.customer;
                        d.model = f.model;
                        d.doc_type = f.doc_type;
                        d.category = f.category;
                        d.status = f.status;
                        d.search_term = $('#custom-share-search').val() || '';
                    }
                },

                order: [
                    [3, 'desc']
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
                            const revVal = row.revision ?? row.revision_no;
                            const revTxt = (revVal !== undefined && revVal !== null && revVal !== '') ?
                                `rev ${revVal}` :
                                '';

                            const parts = [
                                row.customer,
                                row.model,
                                row.part_no,
                                row.doc_type,
                                row.category,
                                row.part_group,
                                row.ecn_no,
                                revTxt
                            ].filter(Boolean);

                            const combined = parts.join(' - ');
                            const searchVal = $('#custom-share-search').val();
                            const highlighted = highlightText(combined, searchVal);

                            return `<div class="text-xs font-medium text-gray-900 dark:text-gray-100 tracking-tight leading-relaxed">${highlighted}</div>`;
                        }
                    },
                    {
                        data: 'project_status',
                        name: 'project_status',
                        render: function(data, type, row) {
                            const value = row.project_status ?? row.project_status_name ?? data ?? '';
                            if (!value) return '<span class="text-xs text-gray-400 dark:text-gray-500">–</span>';

                            let colors = 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800';
                            if (value === 'Regular') colors = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800';
                            else if (value === 'Feasibility Study') colors = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800';

                            return `<span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-tighter border ${colors} rounded">${value}</span>`;
                        }
                    },
                    {
                        data: 'request_date',
                        name: 'dpr.requested_at',
                        render: function(v) {
                            const text = fmtDate(v);
                            return `<div class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">${text || '—'}</div>`;
                        }
                    },
                    {
                        data: 'decision_date',
                        name: 'dpr.decided_at',
                        render: function(v, t, row) {
                            const text = fmtDate(v);
                            return `<div class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">${text || '—'}</div>`;
                        }
                    },
                    {
                        data: 'share_to',
                        name: 'psr.share_to',
                        render: function(data, type, row) {
                            if (!data || data.length === 0) {
                                return '<span class="text-gray-400 text-xs italic">Not yet distributed</span>';
                            }
                            const json = JSON.stringify(data).replace(/"/g, '&quot;');
                            return `
                                <button type="button" 
                                    class="btn-view-shares inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-900/30 rounded-md hover:bg-indigo-600 hover:text-white transition-all border border-indigo-100 dark:border-indigo-800"
                                    data-shares="${json}">
                                    <i class="fa-solid fa-eye text-[8px]"></i> ${data.length} Recipients
                                </button>
                            `;
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center whitespace-nowrap',
                        render: function(packageId, type, row) {
                            return `
                                <button 
                                    type="button" 
                                    class="btn-share p-2 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 rounded-full transition-colors"
                                    data-id="${packageId}" 
                                    title="Share package">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </button>
                            `;
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
                    $(row).addClass('hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors');
                }
            });

            table.on('draw.dt', function() {
                const info = table.page.info();
                table.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + info.start;
                });
            });

            // Debounced Search Handler
            let searchTimer;
            $('#custom-share-search').on('keyup', function() {
                clearTimeout(searchTimer);
                const $icon = $('#share-search-icon');
                $icon.removeClass('fa-magnifying-glass').addClass('fa-spinner fa-spin text-indigo-500');
                
                searchTimer = setTimeout(() => {
                    table.ajax.reload();
                    loadKpis();
                    $icon.removeClass('fa-spinner fa-spin text-indigo-500').addClass('fa-magnifying-glass');
                }, 500);
            });
        }

        function loadKpis() {
            const f = getCurrentFilters();
            const $cards = $('#totalShared, #totalActive, #totalExpired, #totalRequest');
            
            // Subtle pulse while loading
            $cards.addClass('animate-pulse opacity-50');

            $.get('{{ route("share.kpi") }}', f, function(data) {
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

                countUp('totalShared', data.totalShared);
                countUp('totalActive', data.totalActive);
                countUp('totalExpired', data.totalExpired);
                countUp('totalRequest', data.totalRequest);
            }).always(() => {
                $cards.removeClass('animate-pulse opacity-50');
            });
        }

        function loadHistory() {
            const $container = $('#shareHistoryList');
            
            $.get('{{ route("share.history") }}', function(logs) {
                $container.empty();
                
                if (logs.length === 0) {
                    $container.html('<div class="text-xs text-gray-400 text-center py-6 italic">No recent activity</div>');
                    return;
                }

                logs.forEach(log => {
                    const expClass = log.is_expired ? 'text-red-500 font-bold' : 'text-gray-400';
                    const expIcon = log.is_expired ? 'fa-triangle-exclamation' : 'fa-clock';
                    
                    const node = `
                        <div class="relative pl-6 pb-6 border-l border-gray-100 dark:border-gray-700 last:pb-0">
                            <div class="absolute left-0 top-1 -translate-x-1/2 w-2 h-2 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-gray-800"></div>
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-tight">${log.user}</span>
                                    <span class="text-[9px] text-gray-400 font-medium" title="${log.full_time}">${log.time}</span>
                                </div>
                                
                                <div class="text-[11px] font-bold text-gray-800 dark:text-gray-200 leading-tight">
                                    ${log.part_no}
                                </div>

                                <div class="text-[10px] text-gray-500 dark:text-gray-400 leading-normal">
                                    <span class="opacity-60">To:</span> 
                                    <span class="font-medium text-gray-700 dark:text-gray-300">${log.shared_to}</span>
                                </div>

                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <i class="fa-solid ${expIcon} text-[9px] ${expClass}"></i>
                                    <span class="text-[9px] ${expClass} uppercase tracking-wider font-semibold">
                                        Exp: ${log.expired_at || '-'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;
                    $container.append(node);
                });
            });
        }

        function bindHandlers() {
            $('#customer, #model, #document-type, #category, #status').on('change', function() {
                if (table) table.ajax.reload(null, true);
                loadKpis();
            });

            $('#btnResetFilters').on('click', function() {
                $('#custom-share-search').val('');
                resetSelect2ToAll($('#customer'));
                resetSelect2ToAll($('#model'));
                resetSelect2ToAll($('#document-type'));
                resetSelect2ToAll($('#category'));
                resetSelect2ToAll($('#status'));

                if (table) table.ajax.reload(null, true);
                loadKpis();
            });

        }

        // --- Share Action Logic ---
        const $shareModal = $('#shareModal');
        const $supplierListContainer = $('#supplierListContainer');
        const $hiddenPackageId = $('#hiddenPackageId');
        const $btnSaveShare = $('#btnSaveShare');
        const $externalRecipientList = $('#externalRecipientList');

        // State for selected suppliers
        let selectedSuppliers = [];

        function renderSelectedSuppliers() {
            $externalRecipientList.empty();
            
            if (selectedSuppliers.length === 0) {
                $externalRecipientList.append(`
                    <div class="flex flex-col items-center justify-center py-6 border border-dashed border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-800/50">
                        <i class="fa-solid fa-user-group text-gray-300 mb-2"></i>
                        <p class="text-[11px] text-gray-400 font-medium">No recipients added</p>
                    </div>
                `);
                return;
            }

            selectedSuppliers.forEach(supplier => {
                const initials = supplier.code.substring(0, 2).toUpperCase();
                const item = `
                    <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm group hover:border-indigo-300 dark:hover:border-indigo-700 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 font-bold text-[10px] border border-gray-100 dark:border-gray-600">
                                ${initials}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-gray-800 dark:text-gray-200 leading-tight truncate max-w-[180px]">${supplier.text}</p>
                                <p class="text-[9px] text-gray-400 font-mono mt-0.5">${supplier.code}</p>
                            </div>
                        </div>
                        <button type="button" class="text-gray-300 hover:text-red-500 transition-colors p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded" onclick="removeSupplier('${supplier.id}')">
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                        </button>
                    </div>
                `;
                $externalRecipientList.append(item);
            });
        }

        // Global function for removal (needs to be global for onclick attribute)
        window.removeSupplier = function(id) {
            selectedSuppliers = selectedSuppliers.filter(s => s.id != id); // loose comparison for string/int safety
            renderSelectedSuppliers();
            // Also unselect in Select2 if needed, though we clear it anyway
        };

        function initModalSuppliers() {
            // Reset state
            selectedSuppliers = [];
            renderSelectedSuppliers();

            if ($supplierListContainer.data('select2')) {
                $supplierListContainer.select2('destroy');
            }
            $supplierListContainer.empty();
            
            $supplierListContainer.select2({
                dropdownParent: $('#shareModal'),
                width: '100%',
                placeholder: 'Type to search suppliers...',
                allowClear: true,
                multiple: true,
                closeOnSelect: true,
                ajax: {
                    url: "{{ route('share.getSuppliers') }}",
                    method: 'GET',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.id,
                                text: item.name || item.code, // Fallback to code if name missing
                                code: item.code
                            }))
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) return data.text;
                    return $(`
                        <div class="flex items-center justify-between px-1">
                            <span class="font-medium text-sm text-gray-700 dark:text-gray-200">${data.text}</span>
                            <span class="text-xs text-gray-400 font-mono bg-gray-100 dark:bg-gray-600 px-1.5 py-0.5 rounded">${data.code}</span>
                        </div>
                    `);
                },
                templateSelection: function(data) {
                    return data.text || data.id; // Fallback
                }
            });

            // Handle selection event
            $supplierListContainer.on('select2:select', function(e) {
                const data = e.params.data;
                // Add to our list if not exists
                if (!selectedSuppliers.find(s => s.id == data.id)) {
                    selectedSuppliers.push({
                        id: data.id,
                        text: data.text,
                        code: data.code
                    });
                    renderSelectedSuppliers();
                }
                // Clear the input so specific chips don't show up inside
                $supplierListContainer.val(null).trigger('change');
            });
        }


        // Global handlers for closing
        $('body').on('click', '.btn-close-modal', function() {
            $shareModal.hide();
        });

        $shareModal.on('click', function(e) {
            if ($(e.target).is($shareModal)) {
                $(this).hide();
            }
        });

        $btnSaveShare.on('click', function() {
            const $this = $(this);
            const packageId = $hiddenPackageId.val();
            // Use our tracked array instead of Select2 value
            const supplierIds = selectedSuppliers.map(s => s.id);

            if (!packageId) {
                toastError('Error', 'Package ID not found.');
                return;
            }

            if (supplierIds.length === 0) {
                toastWarning('Add recipients', 'Please select at least one supplier to share with.');
                return;
            }

            const originalContent = $this.html();
            $this.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Sending...</span>');

            $.ajax({
                url: '{{ route("share.save") }}',
                type: 'POST',
                data: {
                    package_id: packageId,
                    supplier_ids: supplierIds
                },
                dataType: 'json',
                success: function(response) {
                    $shareModal.hide();
                    toastSuccess('Sent', response.message || 'Shared successfully!');
                    if (table) table.ajax.reload(null, false);
                    loadKpis();
                    loadHistory();
                },
                error: function(xhr) {
                    console.error('Failed to share:', xhr.responseText);
                    const msg = xhr.responseJSON?.message || 'Failed to share package.';
                    if (xhr.status === 422) toastWarning('Check input', msg);
                    else toastError('Error', msg);
                },
                complete: function() {
                    $this.prop('disabled', false).html(originalContent);
                }
            });
        });

        initTable();
        bindHandlers();
        loadKpis();
        loadHistory();

        // --- Share Details Modal Logic ---
        const $shareDetailsModal = $('#shareDetailsModal');
        const $shareDetailsBody = $('#shareDetailsBody');

        $('body').on('click', '.btn-close-share-details', function() {
            $shareDetailsModal.hide();
        });

        $shareDetailsModal.on('click', function(e) {
            if ($(e.target).is($shareDetailsModal)) {
                $(this).hide();
            }
        });

        $('#approvalTable tbody').on('click', '.btn-view-shares', function(e) {
            e.stopPropagation();
            const json = $(this).attr('data-shares');
            if (!json) return;

            const shares = JSON.parse(json);
            $shareDetailsBody.empty();

            shares.forEach(s => {
                const sharedDate = fmtDate(s.shared_at);
                const expiredDate = s.expired_at ? fmtDate(s.expired_at).split(' ')[0] : '-';
                
                let isExpired = false;
                if (s.expired_at) {
                    isExpired = new Date(s.expired_at) < new Date();
                }

                const statusTxt = isExpired ? 'Expired' : 'Active';
                const statusBg = isExpired ? 'bg-red-50 text-red-600 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100';
                
                // Get initials (first 2 chars of code)
                const initials = (s.code || '??').substring(0, 2).toUpperCase();

                const item = `
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-[10px] border border-indigo-100 dark:border-indigo-800">
                                ${initials}
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-800 dark:text-gray-200 leading-tight">${s.name || s.code}</p>
                                <p class="text-[9px] text-gray-400 mt-0.5 font-mono">Shared: ${sharedDate.split(' ')[0]}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-1.5 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-wider border ${statusBg}">${statusTxt}</span>
                            <p class="text-[9px] text-gray-400 mt-1 font-mono">${expiredDate}</p>
                        </div>
                    </div>
                `;
                $shareDetailsBody.append(item);
            });

            $shareDetailsModal.show();
        });

        // Initialize Share Action
        $('#approvalTable tbody').on('click', '.btn-share', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const row = table.row($(this).parents('tr')).data();
            
            $('#modal-package-name').text(row.part_no || 'Document Package');
            $('#modal-package-info').text(`${row.customer} - ${row.model} | ${row.doc_type}`);
            $('#hiddenPackageId').val(id);
            
            initModalSuppliers();
            $shareModal.show();
        });

        $('#approvalTable tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('.btn-share, .btn-view-shares').length) return;

            const rowData = table.row(this).data();
            if (!rowData || !rowData.hash) return;

            const url = '{{ route("share.detail", ["id" => "__ID__"]) }}'.replace('__ID__', rowData.hash);
            window.location.href = url;
        });
    });
</script>
@endpush