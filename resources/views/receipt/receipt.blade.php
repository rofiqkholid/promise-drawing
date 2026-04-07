@extends('layouts.app')
@section('title', 'Receipt - PROMISE')
@section('header-title', 'Receipt')

@section('content')
<div class="w-full px-2 sm:px-4 lg:px-6 xl:px-4 2xl:px-6">
    <div class="w-full">
        <nav class="flex px-3 sm:px-5 py-2 sm:py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xs" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600 transition-colors">
                        <i class="fa-solid fa-chart-line mr-2"></i> Monitoring
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <span class="text-gray-400 mx-1">/</span>
                        <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded-xs bg-blue-50 dark:bg-blue-900/20">
                            Receipt Library
                        </span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="w-full p-3 sm:p-4 lg:p-6 bg-gray-50 dark:bg-gray-900 font-sans">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Receipt Repository</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage drawing packages received from engineering.</p>
            </div>
            <button id="btnExportSummary"
                class="inline-flex items-center gap-2 justify-center px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xs transition-all shadow-emerald-200 dark:shadow-none h-10 border border-transparent">
                <i class="fa-solid fa-file-excel"></i>
                <span class="text-[13px] font-semibold btn-label">Export Summary</span>
                <span class="btn-spinner hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
            </button>
        </div>

        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Sidebar Filters --}}
            <aside class="w-full lg:w-72 flex-shrink-0 space-y-3">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xs border border-gray-200 dark:border-gray-700 sticky top-24">
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                        <i class="fa-solid fa-filter text-blue-500"></i>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">Quick Filters</h3>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-1 gap-x-4 gap-y-5">
                        <div class="col-span-2 lg:col-span-1 border-b border-gray-50 dark:border-gray-700/50 pb-2">
                            <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 mb-2">Search</label>
                            <div class="relative group">
                                <input type="text" id="custom-receipt-search" 
                                    class="block w-full pl-4 pr-10 py-2.5 bg-gray-50/50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-xs text-xs font-semibold focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all dark:text-gray-100 group-hover:border-blue-300 dark:group-hover:border-blue-500/50"
                                    placeholder="Part No, ECN...">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i id="search-icon-static" class="fa-solid fa-magnifying-glass text-gray-400 text-[10px]"></i>
                                    <i id="search-icon-loading" class="fa-solid fa-spinner fa-spin text-blue-500 text-[10px] opacity-0 absolute"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 mb-2">Customer</label>
                            <select id="customer" class="js-filter w-full"></select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 mb-2">Model</label>
                            <select id="model" class="js-filter w-full"></select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 mb-2">Doc Type</label>
                            <select id="document-type" class="js-filter w-full"></select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 mb-2">Category</label>
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
            <main class="flex-1 min-w-0 space-y-6">
                {{-- KPI Row --}}
                <div class="flex overflow-x-auto pb-4 lg:pb-0 gap-3 lg:grid lg:grid-cols-4 no-scrollbar">
                    @foreach([
                        ['id' => 'totalReceived',      'label' => 'Total',    'icon' => 'fa-inbox',             'color' => 'blue'],
                        ['id' => 'totalActive',        'label' => 'Active',   'icon' => 'fa-circle-check',      'color' => 'emerald'],
                        ['id' => 'totalExpired',       'label' => 'Expired',  'icon' => 'fa-clock-rotate-left', 'color' => 'rose'],
                        ['id' => 'totalReceivedToday', 'label' => 'Today',    'icon' => 'fa-calendar-day',      'color' => 'amber']
                    ] as $card)
                    <div class="flex-shrink-0 w-[240px] lg:w-auto bg-white dark:bg-gray-800 p-4 rounded-xs border border-gray-200 dark:border-gray-700 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xs bg-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-100 dark:bg-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-900/30 flex items-center justify-center text-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-600 dark:text-{{ $card['color'] === 'emerald' ? 'green' : ($card['color'] === 'rose' ? 'red' : $card['color']) }}-400">
                            <i class="fa-solid {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-gray-400">{{ $card['label'] }}</p>
                            <p id="{{ $card['id'] }}" class="text-xl font-bold text-gray-900 dark:text-gray-100">-</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Access Tabs --}}
                    <div class="px-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-6 overflow-x-auto no-scrollbar" id="access-tabs-container">
                        @foreach(['All' => 'All Packages', 'active' => 'Active Access', 'expired' => 'Expired Link'] as $val => $text)
                        <button type="button" 
                            class="access-tab relative py-4 text-xs font-semibold transition-all whitespace-nowrap {{ $val === 'All' ? 'text-blue-600 active-tab' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}"
                            data-access="{{ $val }}">
                            {{ $text }}
                            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 tab-indicator {{ $val === 'All' ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-200"></span>
                        </button>
                        @endforeach
                    </div>

                    <div>
                        <table id="receiptTable" class="w-full divide-y divide-gray-100 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-[13px] text-gray-600 dark:text-gray-400 font-bold tracking-tight">
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
    {{-- ... (content remains the same) ... --}}
</div>

{{-- Request Re-share Modal --}}
<div id="requestReshareModal" 
    class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/70 p-4 backdrop-blur-sm"
    x-cloak
    x-show="open"
    x-data="{ 
        open: false,
        revisionId: null, 
        reason: '', 
        submitting: false,
        packageInfo: '',
        async submit() {
            if (!this.reason.trim()) return toastWarning('Reason Required', 'Please provide a reason for the re-share request.');
            
            this.submitting = true;
            try {
                const res = await fetch('{{ route('receipts.request-reshare') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ revision_id: this.revisionId, reason: this.reason.trim() })
                });
                const data = await res.json();
                if (res.ok) {
                    toastSuccess('Success', data.message);
                    this.reason = '';
                    this.open = false;
                    if (window.table) window.table.ajax.reload(null, false);
                } else {
                    toastError('Error', data.message);
                }
            } catch (e) {
                toastError('Error', 'Failed to send request.');
            } finally {
                this.submitting = false;
            }
        }
    }"
    @open-reshare-modal.window="revisionId = $event.detail.id; packageInfo = $event.detail.info; open = true;"
>
    <div @click.away="!submitting && (open = false)" class="bg-white dark:bg-gray-800 rounded-xs shadow-2xl w-full max-w-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xs bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Request Re-share</h3>
                    <p class="text-[10px] text-gray-500 font-bold">Restore access to expired package</p>
                </div>
            </div>
            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" :disabled="submitting">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="p-6 space-y-4">
            <div class="bg-blue-50/50 dark:bg-blue-900/10 p-4 rounded-xs border border-blue-100 dark:border-blue-800/50">
                <p class="text-[9px] font-bold text-blue-400 mb-1">Package Details</p>
                <p class="text-xs font-bold text-gray-800 dark:text-gray-200" x-text="packageInfo"></p>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-2 px-1">
                    Reason for Re-request <span class="text-rose-500">*</span>
                </label>
                <textarea x-model="reason" 
                    placeholder="E.g., Need to re-check dimension, late arrival of parts..."
                    class="w-full h-32 px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xs text-sm outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-800 dark:text-gray-200"></textarea>
                <p class="mt-2 text-[10px] text-gray-400 italic">This request will be sent to the Purchasing department for approval.</p>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
            <button type="button" @click="open = false" 
                class="px-5 py-2 text-[10px] font-bold text-gray-500 hover:text-gray-700" :disabled="submitting">
                Cancel
            </button>
            <button type="button" @click="submit()" 
                class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded-xs transition-all disabled:opacity-50"
                :disabled="submitting || !reason.trim()">
                <span x-show="!submitting">Send Request</span>
                <span x-show="submitting"><i class="fa-solid fa-spinner fa-spin"></i> Processing...</span>
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

    // Aliases
    function toastSuccess(title, text) { renderToast({ icon: 'success', title, text }); }
    function toastError(title, text) { renderToast({ icon: 'error', title, text }); }
    function toastWarning(title, text) { renderToast({ icon: 'warning', title, text }); }

    // Skeleton Loader logic
    function getSkeleton() {
        return `
            <tr class="skeleton-row">
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-xs w-8 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="space-y-2"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-xs w-48 animate-pulse"></div><div class="h-3 bg-gray-100 dark:bg-gray-800 rounded-xs w-32 animate-pulse"></div></div></td>
                <td class="px-4 py-4 text-center"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded-xs w-16 mx-auto animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-xs w-24 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-xs w-24 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-xs w-20 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-xs w-20 animate-pulse"></div></td>
                <td class="px-4 py-4 text-center"><div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-xs w-8 mx-auto animate-pulse"></div></td>
            </tr>
        `;
    }

    $(document).ready(function() {
        let table;
        const ENDPOINT = '{{ route("receipts.filters") }}';

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
            scrollX: true,
            scrollCollapse: true,
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
                infoEmpty: "",
                infoFiltered: "",
                zeroRecords: '<div class="flex flex-col items-center justify-center p-12 text-gray-400"><i class="fa-solid fa-folder-open text-4xl mb-3 opacity-20"></i><span class="text-xs italic">No matching files found</span></div>'
            },
            dom: 't<"flex flex-col sm:flex-row justify-between items-center p-6 border-t border-gray-50 dark:border-gray-800 gap-4" <"flex-1"i> <"flex justify-end"p>>',
            
            createdRow: function(row) {
                $(row).addClass('hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors border-b border-gray-50 dark:border-gray-800 last:border-0 text-gray-900 dark:text-gray-100 cursor-pointer');
                $('td', row).addClass('py-4 px-4 align-middle');
            },

            columns: [
                { 
                    data: null, orderable: false, searchable: false, className: 'text-center text-gray-400 text-[13px]',
                    render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                },
                {
                    data: null, name: 'package_info', searchable: true, orderable: false,
                    render: function(data, type, row) {
                        const s = $('#custom-receipt-search').val();
                        let mainText = row.part_no || '-';
                        if (row.partners) {
                            const pClean = row.partners.replace(/,/g, ' / ');
                            mainText += ` / ${pClean}`;
                        }
                        const p = highlightText(mainText, s);

                        const sub = highlightText(`${row.customer || '-'} / ${row.model || '-'}`, s);
                        
                        return `
                            <div class="flex flex-col max-w-[450px]">
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">${p}</span>
                                <div class="text-[11px] text-gray-500 mt-0.5 truncate">
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
                            label = `<span class="px-2 py-1 rounded-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[9px] font-black mr-1.5">${row.revision_label_name}</span>`;
                        }
                        return `
                            <div class="flex items-center justify-center">
                                ${label}
                                <span class="px-2.5 py-1 rounded-xs text-[10px] font-black bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-100 dark:border-blue-800 whitespace-nowrap">REV ${v}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'ecn_no', name: 'ecn', searchable: true,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-gray-300">-</span>';
                        const s = $('#custom-receipt-search').val();
                        return `<span class="px-2 py-0.5 rounded-xs bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 text-[10px] font-mono text-blue-600 dark:text-blue-400">${highlightText(data, s)}</span>`;
                    }
                },
                {
                    data: null, name: 'category', searchable: true, orderable: true,
                    render: function(data, type, row) {
                        return `
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 capitalize">${row.doctype_group || '-'}</span>
                                <span class="text-[10px] text-gray-500er">${row.doctype_subcategory || ''}</span>
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
                        if(!v) return '<span class="px-2 py-0.5 rounded-xs bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-black border border-emerald-100 dark:border-emerald-800">Permanent</span>';
                        const exp = new Date(v);
                        const days = Math.floor((exp - new Date()) / 86400000);
                        let color = days < 3 ? 'text-rose-500 font-bold' : (days < 7 ? 'text-amber-500 font-bold' : 'text-gray-600 dark:text-gray-300');
                        return `
                            <div class="flex flex-col text-[11px] font-mono">
                                <span class="${color}">${exp.toLocaleDateString()}</span>
                                <span class="text-[10px] text-gray-400er">${days > 0 ? days + ' d left' : (days === 0 ? 'Today' : 'Expired')}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: null, className: 'text-center', orderable: false, searchable: false,
                    render: function(data, type, row) {
                        const isExpired = row.expired_at && new Date(row.expired_at) < new Date();
                        
                        if (isExpired) {
                            if (row.reshare_status === 'pending') {
                                return `<span class="px-3 py-1.5 rounded-xs bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-bold border border-blue-100 dark:border-blue-800" title="Re-share requested">Request Pending</span>`;
                            }
                            
                            const pkgInfo = `${row.part_no} (Rev ${row.revision_no})`;
                            const infoJson = JSON.stringify(pkgInfo).replace(/"/g, '&quot;');

                            return `
                                <button onclick="window.dispatchEvent(new CustomEvent('open-reshare-modal', { detail: { id: ${row.id}, info: ${infoJson} } }))" 
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xs shadow-rose-200 dark:shadow-none bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black transition-all active:scale-95" 
                                    title="Request Re-share">
                                    <i class="fa-solid fa-rotate-right text-[10px]"></i> Re-request
                                </button>`;
                        }

                        return `
                            <button onclick="window.location.href='{{ url('/receipts') }}/${row.hash || row.id}'" 
                                class="w-10 h-10 flex items-center justify-center rounded-xs bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 mx-auto border border-transparent" 
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
            $('.dataTables_scrollBody').addClass('custom-scrollbar');
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
            if (data) window.location.href = `{{ url('/receipts') }}/${data.hash || data.id}`;
        });

        // --- History Modal ---
        let historyTable;
        function initHistory() {
            if (historyTable) { historyTable.ajax.reload(); return; }
            historyTable = $('#historyTable').DataTable({
                processing: false, serverSide: true,
                ajax: '{{ route("receipts.history_list") }}',
                order: [[ 5, 'desc' ]],
                language: { info: '<span class="text-[10px] font-bold text-gray-400">_TOTAL_ total expired records</span>' },
                dom: 't<"p-4 border-t border-gray-100 dark:border-gray-700"ip>',
                createdRow: row => $(row).addClass('hover:bg-rose-50/50 dark:hover:bg-rose-900/10 transition-colors cursor-pointer border-b border-gray-50 dark:border-gray-800 last:border-0 text-gray-900 dark:text-gray-100'),
                columns: [
                    { data: null, orderable: false, className: 'text-center text-gray-400 font-mono text-xs', render: (d, t, r, m) => m.row + 1 },
                    { 
                        data: null, name: 'package_info',
                        render: (d,t,r) => `
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-900 dark:text-gray-100">${r.part_no || '-'}</span>
                                <span class="text-[10px] text-gray-500">${r.customer || '-'} / ${r.model || '-'}</span>
                            </div>`
                    },
                    { data: 'ecn_no', name: 'ecn', render: v => `<span class="text-xs font-mono text-gray-500">${v || '-'}</span>` },
                    { data: 'revision', className: 'text-center', render: v => `<span class="px-2 py-0.5 rounded-full bg-gray-100 text-[9px] font-black text-gray-500 border border-gray-200">REV ${v}</span>` },
                    { data: 'shared_at', render: v => `<span class="text-[10px] font-mono text-gray-500">${v ? new Date(v).toLocaleDateString() : '-'}</span>` },
                    { data: 'expired_at', className: 'text-rose-500 font-black', render: v => `<span class="text-[10px] font-mono">${v ? new Date(v).toLocaleDateString() : '-'}</span>` }
                ]
            });
        }

        // --- Export Summary ---
        $('#btnExportSummary').on('click', function() {
            const $btn = $(this);
            const $label = $btn.find('.btn-label');
            const $spinner = $btn.find('.btn-spinner');

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

            const exportUrl = '{{ route("receipts.list") }}' + '?export=1&' + $.param(filters);
            
            window.location.href = exportUrl;
            setTimeout(() => {
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                $label.removeClass('hidden');
                $spinner.addClass('hidden');
            }, 3000);
        });

        $('.btn-close-history').on('click', () => $('#historyModal').fadeOut(200));
        $('#historyModal').on('click', e => { if(e.target === e.currentTarget) $(e.target).fadeOut(200); });
    });
</script>
@endpush

@push('style')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush