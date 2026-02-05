@extends('layouts.app')
@section('title', 'Receipt - PROMISE')
@section('header-title', 'Receipt')

@section('content')
<div class="w-full px-2 sm:px-4 lg:px-6 xl:px-4 2xl:px-6">
    <div class="w-full">
        <nav class="flex px-3 sm:px-5 py-2 sm:py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-md" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600 transition-colors">
                        <i class="fa-solid fa-chart-line mr-2"></i> Monitoring
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <span class="text-gray-400 mx-1">/</span>
                        <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20">
                            Receipt Library
                        </span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="w-full p-3 sm:p-4 lg:p-6 bg-gray-50 dark:bg-gray-900 font-sans">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Receipt Repository</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage drawing packages received from engineering.</p>
            </div>
            <button id="btnExportSummary"
                class="inline-flex items-center gap-2 justify-center px-6 py-3 border border-emerald-600 text-sm font-bold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200 dark:shadow-none">
                <i class="fa-solid fa-file-excel text-lg"></i>
                <span class="btn-label">Export Summary</span>
                <span class="btn-spinner hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
            </button>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar Filters --}}
            <aside class="w-full lg:w-72 flex-shrink-0 space-y-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-md border border-gray-200 dark:border-gray-700 sticky top-24 shadow-sm">
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                        <i class="fa-solid fa-filter text-blue-500"></i>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">Quick Filters</h3>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-1 gap-x-4 gap-y-5">
                        <div class="col-span-2 lg:col-span-1 border-b border-gray-50 dark:border-gray-700/50 pb-2">
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Search</label>
                            <div class="relative">
                                <input type="text" id="custom-receipt-search" 
                                    class="block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-md text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all dark:text-gray-100"
                                    placeholder="Part No, ECN...">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i id="search-icon-static" class="fa-solid fa-magnifying-glass text-gray-400 text-[10px]"></i>
                                    <i id="search-icon-loading" class="fa-solid fa-spinner fa-spin text-blue-500 text-[10px] opacity-0 absolute"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Customer</label>
                            <select id="customer" class="js-filter w-full"></select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Model</label>
                            <select id="model" class="js-filter w-full"></select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Doc Type</label>
                            <select id="document-type" class="js-filter w-full"></select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Category</label>
                            <select id="category" class="js-filter w-full"></select>
                        </div>

                        <div class="col-span-2 lg:col-span-1 pt-2 border-t border-gray-50 dark:border-gray-700/50 lg:border-none">
                            <button id="btnResetFilters" class="w-full py-2.5 text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-rotate-left"></i>
                                Reset All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 space-y-8">
                {{-- KPI Row --}}
                <div class="flex overflow-x-auto pb-4 lg:pb-0 gap-4 lg:grid lg:grid-cols-4 no-scrollbar">
                    @foreach([
                        ['id' => 'totalReceived',      'label' => 'Total',    'icon' => 'fa-inbox',             'color' => 'blue'],
                        ['id' => 'totalActive',        'label' => 'Active',   'icon' => 'fa-circle-check',      'color' => 'emerald'],
                        ['id' => 'totalExpired',       'label' => 'Expired',  'icon' => 'fa-clock-rotate-left', 'color' => 'rose'],
                        ['id' => 'totalReceivedToday', 'label' => 'Today',    'icon' => 'fa-calendar-day',      'color' => 'amber']
                    ] as $card)
                    <div class="flex-shrink-0 w-[240px] lg:w-auto bg-white dark:bg-gray-800 p-4 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-md bg-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-100 dark:bg-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-900/30 flex items-center justify-center text-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-600 dark:text-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-400">
                            <i class="fa-solid {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{{ $card['label'] }}</p>
                            <p id="{{ $card['id'] }}" class="text-xl font-bold text-gray-900 dark:text-gray-100">-</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                    {{-- Access Tabs --}}
                    <div class="px-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-6 overflow-x-auto no-scrollbar" id="access-tabs-container">
                        @foreach(['All' => 'All Packages', 'active' => 'Active Access', 'expired' => 'Expired Link'] as $val => $text)
                        <button type="button" 
                            class="access-tab relative py-4 text-sm font-semibold transition-all whitespace-nowrap {{ $val === 'All' ? 'text-blue-600 active-tab' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}"
                            data-access="{{ $val }}">
                            {{ $text }}
                            <span class="absolute bottom-0 left-0 w-full h-1 bg-blue-600 rounded-t-full tab-indicator {{ $val === 'All' ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-200"></span>
                        </button>
                        @endforeach
                    </div>

                    <div class="overflow-x-auto">
                        <table id="receiptTable" class="w-full divide-y divide-gray-100 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-[10px] uppercase text-gray-600 dark:text-gray-400 font-bold tracking-tight">
                                <tr>
                                    <th class="px-4 py-3.5 w-8 text-center bg-gray-50 dark:bg-gray-700/50">No</th>
                                    <th class="px-4 py-3.5 min-w-[200px] text-left">Package Information</th>
                                    <th class="px-4 py-3.5 w-24 text-center">Revision</th>
                                    <th class="px-4 py-3.5 text-left">ECN No</th>
                                    <th class="px-4 py-3.5 text-left">Category</th>
                                    <th class="px-4 py-3.5 w-32 text-left">Received At</th>
                                    <th class="px-4 py-3.5 w-32 text-left">Expires On</th>
                                    <th class="px-4 py-3.5 w-24 text-center bg-gray-50 dark:bg-gray-700/50">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 border-t border-gray-100 dark:border-gray-700 font-sans">
                                {{-- JS will inject initial skeletons --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

{{-- History Modal --}}
<div id="historyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
    <div class="bg-white dark:bg-gray-800 rounded-md shadow-2xl w-full max-w-5xl mx-4 flex flex-col max-h-[90vh] border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Expired Repository</h3>
                <p class="text-[10px] text-gray-500 font-medium tracking-tight">Access to these drawing packages has been deactivated.</p>
            </div>
            <button type="button" class="btn-close-history text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-hidden flex flex-col">
            <div class="overflow-y-auto flex-1">
                <table id="historyTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-[9px] uppercase text-gray-500 font-bold text-left tracking-widest leading-none">
                        <tr>
                            <th class="px-5 py-3 w-12 text-center">No</th>
                            <th class="px-5 py-3 min-w-[200px]">Package Info</th>
                            <th class="px-5 py-3">ECN No</th>
                            <th class="px-5 py-3 w-20 text-center">Rev</th>
                            <th class="px-5 py-3 w-32">Received</th>
                            <th class="px-5 py-3 w-32 text-red-500">Expired At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>

        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
            <button type="button" class="btn-close-history px-6 py-2 text-[10px] font-bold text-gray-500 hover:text-gray-900 uppercase tracking-widest transition-colors border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                Dismiss
            </button>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
    let currentAccess = 'All';

    // Helper for highlighting search terms (Stabilo effect)
    function highlightText(data, searchVal) {
        if (!searchVal || !data) return data || '';
        const safeSearch = searchVal.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${safeSearch})`, 'gi');
        return data.toString().replace(regex, '<span class="bg-yellow-100 text-gray-900 border-b border-yellow-400">$1</span>');
    }

    // --- Toast Helpers ---
    function detectTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        return isDark ? {
            bg: 'rgba(30,41,59,.95)', fg: '#E5E7EB', border: 'rgba(71,85,105,.5)', progress: 'rgba(255,255,255,.9)',
            icon: { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' }
        } : {
            bg: 'rgba(255,255,255,.98)', fg: '#0f172a', border: 'rgba(226,232,240,1)', progress: 'rgba(15,23,42,.8)',
            icon: { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' }
        };
    }

    const BaseToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2600,
        timerProgressBar: true,
        showClass: { popup: 'swal2-animate-toast-in' },
        hideClass: { popup: 'swal2-animate-toast-out' },
        didOpen: (el) => {
            el.addEventListener('mouseenter', Swal.stopTimer);
            el.addEventListener('mouseleave', Swal.resumeTimer);
            const t = detectTheme();
            const bar = el.querySelector('.swal2-timer-progress-bar'); if (bar) bar.style.background = t.progress;
            const popup = el.querySelector('.swal2-popup'); if (popup) popup.style.borderColor = t.border;
        }
    });

    function renderToast({ icon = 'success', title = 'Success', text = '' } = {}) {
        const t = detectTheme();
        BaseToast.fire({
            icon, title, text,
            iconColor: t.icon[icon] || t.icon.success,
            background: t.bg,
            color: t.fg,
            customClass: { popup: 'swal2-toast border' }
        });
    }

    // Skeleton Loader logic
    function getSkeleton() {
        return `
            <tr class="skeleton-row">
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-8 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="space-y-2"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-48 animate-pulse"></div><div class="h-3 bg-gray-100 dark:bg-gray-800 rounded w-32 animate-pulse"></div></div></td>
                <td class="px-4 py-4 text-center"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded-full w-16 mx-auto animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 animate-pulse"></div></td>
                <td class="px-4 py-4 text-center"><div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-md w-8 mx-auto animate-pulse"></div></td>
            </tr>
        `;
    }

    $(document).ready(function() {
        let table;
        const ENDPOINT = '{{ route("receipts.filters") }}';

        // 1. Keyboard shortcut: Press "/" to search
        $(document).on('keyup', function(e) {
            if (e.key === '/' && !$(e.target).is('input, textarea, select')) {
                $('#custom-receipt-search').focus();
            }
        });

        // --- Select2 Helpers ---
        function makeSelect2($el, field, extraParamsFn) {
            $el.select2({
                width: '100%',
                placeholder: 'All',
                allowClear: false,
                ajax: {
                    url: ENDPOINT,
                    dataType: 'json',
                    delay: 250,
                    data: params => {
                        let q = { select2: field, q: params.term || '', page: params.page || 1 };
                        if (typeof extraParamsFn === 'function') Object.assign(q, extraParamsFn());
                        return q;
                    },
                    processResults: (data, params) => {
                        params.page = params.page || 1;
                        let res = Array.isArray(data.results) ? data.results.slice() : [];
                        if (params.page === 1 && !res.some(r => r.id === 'All')) res.unshift({ id: 'All', text: 'All' });
                        return { results: res, pagination: { more: data.pagination?.more || false }};
                    }
                }
            });
        }

        makeSelect2($('#customer'), 'customer');
        makeSelect2($('#model'), 'model', () => ({ customer_code: $('#customer').val() || '' }));
        makeSelect2($('#document-type'), 'doc_type');
        makeSelect2($('#category'), 'category', () => ({ doc_type: $('#document-type').val() || '' }));

        $('#customer').on('change', () => { $('#model').val('All').trigger('change'); });
        $('#document-type').on('change', () => { $('#category').val('All').trigger('change'); });

        // --- DataTable Initialization ---
        let searchTimeout;
        const $staticIcon = $('#search-icon-static');
        const $loadingIcon = $('#search-icon-loading');

        // Inject skeletons
        let skeletons = '';
        for(let i=0; i<8; i++) skeletons += getSkeleton();
        $('#receiptTable tbody').html(skeletons);

        table = $('#receiptTable').DataTable({
            processing: false,
            serverSide: true,
            autoWidth: false,
            deferRender: true,
            ajax: {
                url: '{{ route("receipts.list") }}',
                data: function(d) {
                    d.customer = $('#customer').val();
                    d.model = $('#model').val();
                    d.doc_type = $('#document-type').val();
                    d.category = $('#category').val();
                    d.access = currentAccess; 
                    d.search = { value: $('#custom-receipt-search').val() };
                }
            },
            order: [[5, 'desc']],
            language: {
                info: `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50/50 dark:bg-blue-900/20 border border-blue-100/50 dark:border-blue-800/50 shadow-sm transition-all hover:bg-blue-50 dark:hover:bg-blue-900/40">
                          <i class="fa-solid fa-layer-group text-blue-500 text-[10px]"></i>
                          <span class="text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-tight">Records</span>
                          <span class="text-gray-900 dark:text-gray-100 text-[11px] font-black font-mono">_START_-_END_</span>
                          <span class="text-gray-300 dark:text-gray-600">/</span>
                          <span class="text-blue-600 dark:text-blue-400 text-[11px] font-black font-mono">_TOTAL_</span>
                       </div>`,
                infoEmpty: "No Records Found",
                zeroRecords: '<div class="flex flex-col items-center justify-center p-12 text-gray-400"><i class="fa-solid fa-folder-open text-4xl mb-3 opacity-20"></i><span class="text-xs italic">No matching receipt found</span></div>',
                paginate: {
                    previous: 'Previous',
                    next: 'Next'
                }
            },
            dom: 't<"flex flex-col sm:flex-row justify-between items-center p-6 border-t border-gray-50 dark:border-gray-800 gap-4" <"flex-1"i> <"flex justify-end"p>>',
            
            createdRow: function(row) {
                $(row).addClass('hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors border-b border-gray-50 dark:border-gray-800 last:border-0 text-gray-900 dark:text-gray-100 cursor-pointer');
                $('td', row).addClass('py-4 px-4 align-middle');
            },

            columns: [
                { 
                    data: null, orderable: false, searchable: false, className: 'text-center text-gray-400 font-mono text-[10px]',
                    render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                },
                {
                    data: null, name: 'package_info', searchable: true, orderable: false,
                    render: function(data, type, row) {
                        const s = $('#custom-receipt-search').val();
                        let p = highlightText(row.part_no || '-', s);
                        
                        // Append partners if available
                        if (row.partners) {
                            const pClean = row.partners.replace(/,/g, ' / ');
                            p += ` <span class="text-gray-400 font-normal">/ ${highlightText(pClean, s)}</span>`;
                        }

                        const sub = highlightText(`${row.customer || '-'} / ${row.model || '-'}`, s);
                        
                        return `
                            <div class="flex flex-col max-w-[450px]">
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">${p}</span>
                                <div class="text-[11px] text-gray-500 mt-0.5 truncate uppercase tracking-tight">
                                    ${sub}
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'revision_no', name: 'revision', className: 'text-center', searchable: true,
                    render: function(v, type, row) {
                        let label = '';
                        if (row.revision_label_name) {
                            label = `<span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[9px] font-black uppercase tracking-widest mr-1.5 shadow-sm">${row.revision_label_name}</span>`;
                        }
                        return `
                            <div class="flex items-center justify-center">
                                ${label}
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-100 dark:border-blue-800 shadow-sm whitespace-nowrap uppercase tracking-widest">REV ${v}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'ecn_no', name: 'ecn', searchable: true,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-gray-300">-</span>';
                        const s = $('#custom-receipt-search').val();
                        return `<span class="px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 text-[10px] font-mono text-blue-600 dark:text-blue-400 shadow-sm">${highlightText(data, s)}</span>`;
                    }
                },
                {
                    data: null, name: 'category', searchable: true, orderable: true,
                    render: function(data, type, row) {
                        return `
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 capitalize">${row.doctype_group || '-'}</span>
                                <span class="text-[10px] text-gray-500 uppercase tracking-tighter">${row.doctype_subcategory || ''}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'received', name: 'received', className: 'text-left',
                    render: function(v) {
                        if(!v) return '-';
                        const d = new Date(v);
                        return `
                            <div class="flex flex-col text-[11px] font-mono text-gray-600 dark:text-gray-400">
                                <span class="font-bold">${d.toLocaleDateString()}</span>
                                <span class="opacity-70 text-[10px]">${d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'expired_at', name: 'expired_at', className: 'text-left',
                    render: function(v) {
                        if(!v) return '<span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-black uppercase tracking-widest border border-emerald-100 dark:border-emerald-800 shadow-sm">Permanent</span>';
                        const exp = new Date(v);
                        const days = Math.floor((exp - new Date()) / 86400000);
                        let color = days < 3 ? 'text-rose-500 font-bold' : (days < 7 ? 'text-amber-500 font-bold' : 'text-gray-600 dark:text-gray-300');
                        return `
                            <div class="flex flex-col text-[11px] font-mono">
                                <span class="${color}">${exp.toLocaleDateString()}</span>
                                <span class="text-[10px] text-gray-400 uppercase tracking-tighter">${days > 0 ? days + ' d left' : (days === 0 ? 'Today' : 'Expired')}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: null, className: 'text-center', orderable: false, searchable: false,
                    render: function(data, type, row) {
                        return `
                            <button onclick="window.location.href='/receipts/${row.hash || row.id}'" 
                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-blue-50 text-blue-600 dark:text-blue-400 dark:hover:bg-gray-700 transition-all mx-auto border border-transparent hover:border-blue-100" 
                                title="Open Package">
                                <i class="fa-solid fa-up-right-from-square text-sm"></i>
                            </button>`;
                    }
                }
            ]
        });

        // Tab Switching
        $('.access-tab').on('click', function() {
            const $container = $('#access-tabs-container');
            $container.find('.access-tab').removeClass('text-blue-600 active-tab').addClass('text-gray-400');
            $container.find('.tab-indicator').removeClass('opacity-100').addClass('opacity-0');
            $(this).removeClass('text-gray-400').addClass('text-blue-600 active-tab');
            $(this).find('.tab-indicator').removeClass('opacity-0').addClass('opacity-100');
            currentAccess = $(this).data('access');
            table.draw();
        });

        // Skeleton Loader Trigger
        table.on('preXhr.dt', function() {
            let skeletons = '';
            for(let i=0; i<8; i++) skeletons += getSkeleton();
            $('#receiptTable tbody').html(skeletons);
        });

        // Search Highlighting & Throttling
        $('#custom-receipt-search').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const v = $(this).val();
                $staticIcon.addClass('opacity-0');
                $loadingIcon.removeClass('opacity-0').addClass('opacity-100');
                table.draw();
            }, 600);
        });

        // Reset Logic
        $('#btnResetFilters').on('click', function() {
            $('.js-filter').val('All').trigger('change.select2');
            $('#custom-receipt-search').val('');
            $('.access-tab[data-access="All"]').trigger('click');
            table.draw();
        });

        $('.js-filter').on('change', () => table.draw());

        table.on('draw.dt', function() {
            $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
            $staticIcon.removeClass('opacity-0');

            const json = table.ajax.json();
            if (json && json.kpis) {
                $('#totalReceived').text(json.kpis.total || 0);
                $('#totalActive').text(json.kpis.active || 0);
                $('#totalExpired').text(json.kpis.expired || 0);
                $('#totalReceivedToday').text(json.kpis.today || 0);
            }
        });

        // Row Click Navigation
        $('#receiptTable tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('button').length) return;
            const data = table.row(this).data();
            if (data) window.location.href = `/receipts/${data.hash || data.id}`;
        });

        // --- History Modal ---
        let historyTable;
        function initHistory() {
            if (historyTable) { historyTable.ajax.reload(); return; }
            historyTable = $('#historyTable').DataTable({
                processing: false, serverSide: true,
                ajax: '{{ route("receipts.history_list") }}',
                order: [[ 5, 'desc' ]],
                language: { info: '<span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">_TOTAL_ total expired records</span>' },
                dom: 't<"p-4 border-t border-gray-100 dark:border-gray-700"ip>',
                createdRow: row => $(row).addClass('hover:bg-rose-50/50 dark:hover:bg-rose-900/10 transition-colors cursor-pointer border-b border-gray-50 dark:border-gray-800 last:border-0 text-gray-900 dark:text-gray-100'),
                columns: [
                    { data: null, orderable: false, className: 'text-center text-gray-400 font-mono text-xs', render: (d, t, r, m) => m.row + 1 },
                    { 
                        data: null, name: 'package_info',
                        render: (d,t,r) => `
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-900 dark:text-gray-100">${r.part_no || '-'}</span>
                                <span class="text-[10px] text-gray-500 uppercase">${r.customer || '-'} / ${r.model || '-'}</span>
                            </div>`
                    },
                    { data: 'ecn_no', name: 'ecn', render: v => `<span class="text-xs font-mono text-gray-500">${v || '-'}</span>` },
                    { data: 'revision', className: 'text-center', render: v => `<span class="px-2 py-0.5 rounded-full bg-gray-100 text-[9px] font-black text-gray-500 uppercase border border-gray-200">REV ${v}</span>` },
                    { data: 'shared_at', render: v => `<span class="text-[10px] font-mono text-gray-500">${v ? new Date(v).toLocaleDateString() : '-'}</span>` },
                    { data: 'expired_at', className: 'text-rose-500 font-black', render: v => `<span class="text-[10px] font-mono uppercase">${v ? new Date(v).toLocaleDateString() : '-'}</span>` }
                ]
            });
        }

        // --- Export Summary ---
        $('#btnExportSummary').on('click', function() {
            const $btn = $(this);
            const $label = $btn.find('.btn-label');
            const $spinner = $btn.find('.btn-spinner');

            // Logic to get current filters
            const filters = {
                customer: $('#customer').val(),
                model: $('#model').val(),
                doc_type: $('#document-type').val(),
                category: $('#category').val(),
                search: $('#custom-receipt-search').val(),
                access: $('.access-tab.active-tab').data('access')
            };

            $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
            $label.addClass('hidden');
            $spinner.removeClass('hidden');

            renderToast({
                icon: 'info',
                title: 'Preparing Data',
                text: 'Please wait while we generate your report...'
            });

            // Simulate or call real export endpoint
            // For now, we point to a placeholder or hypothetical route
            const exportUrl = '{{ route("receipts.list") }}' + '?export=1&' + $.param(filters);
            
            // Redirect to export
            window.location.href = exportUrl;

            // Restore button after delay
            setTimeout(() => {
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                $label.removeClass('hidden');
                $spinner.addClass('hidden');
            }, 3000);
        });

        // Hide History Modal functions as they are no longer needed for the main header
        // window.openHistoryModal = () => { $('#historyModal').fadeIn(200); initHistory(); };
        $('.btn-close-history').on('click', () => $('#historyModal').fadeOut(200));
        $('#historyModal').on('click', e => { if(e.target === e.currentTarget) $(e.target).fadeOut(200); });
    });
</script>
@endpush

@push('style')
<style>
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
    /* Tampilan Pagination seperti Button Group (Halaman Lain) */
    .dataTables_wrapper .dataTables_paginate {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        display: flex;
        overflow: hidden;
        padding-top: 0 !important;
        margin-top: 1rem;
    }

    .dark .dataTables_wrapper .dataTables_paginate {
        border-color: #374151;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: none !important;
        border-right: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 0.5rem 1rem !important;
        background: white !important;
        color: #1e293b !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1f2937 !important;
        color: #d1d5db !important;
        border-right-color: #374151 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:last-child {
        border-right: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #f3f4f6 !important;
        color: #1e293b !important;
        box-shadow: none !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #374151 !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #f9fafb !important;
        color: #3b82f6 !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #2d3748 !important;
        color: #60a5fa !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #9ca3af !important;
        cursor: not-allowed !important;
        background: #fcfcfc !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        background: #111827 !important;
        color: #4b5563 !important;
    }

    /* Hilangkan padding span penampung angka */
    .dataTables_wrapper .dataTables_paginate span {
        display: flex;
    }
</style>
@endpush