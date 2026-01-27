@extends('layouts.app')
@section('title', 'File Manager - PROMISE')
@section('header-title', 'File Manager/Dashboard')

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
                    Upload Files
                </span>
            </div>
        </li>
    </ol>
</nav>

<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 font-sans">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Upload Management</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Control and monitor your drawing submissions.</p>
        </div>
        <a href="{{ route('drawing.upload') }}"
            class="inline-flex items-center gap-2 justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
            <i class="fa-solid fa-plus-circle text-lg"></i>
            <span>Upload New Drawing</span>
        </a>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Sidebar Filters --}}
        <aside class="w-full lg:w-72 flex-shrink-0 space-y-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-md border border-gray-200 dark:border-gray-700 sticky top-24">
                <div class="flex items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                    <i class="fa-solid fa-filter text-indigo-500"></i>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">Quick Filters</h3>
                </div>

                <div class="space-y-5">
                    <div class="relative">
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Search</label>
                        <div class="relative">
                            <input type="text" id="custom-upload-search" 
                                class="block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-md text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all dark:text-gray-100"
                                placeholder="Part No, ECN...">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i id="search-icon-static" class="fa-solid fa-magnifying-glass text-gray-400 text-[10px]"></i>
                                <i id="search-icon-loading" class="fa-solid fa-spinner fa-spin text-indigo-500 text-[10px] opacity-0 absolute"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Customer</label>
                        <select id="filter-customer" class="js-upload-filter w-full"></select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Model</label>
                        <select id="filter-model" class="js-upload-filter w-full"></select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Doc Type</label>
                        <select id="filter-doc-type" class="js-upload-filter w-full"></select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Category</label>
                        <select id="filter-category" class="js-upload-filter w-full"></select>
                    </div>

                    <button id="btnResetUploadFilters" class="w-full py-2.5 text-xs font-semibold text-gray-500 hover:text-indigo-600 transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-left"></i>
                        Reset All Filters
                    </button>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 min-w-0 space-y-8">
            {{-- KPI Row - Compact --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    ['id' => 'totalUpload',  'label' => 'Total',    'icon' => 'fa-cloud-arrow-up',  'color' => 'indigo'],
                    ['id' => 'totalDraft',   'label' => 'Draft',    'icon' => 'fa-file-pen',        'color' => 'blue'],
                    ['id' => 'totalPending', 'label' => 'Pending',  'icon' => 'fa-hourglass-start', 'color' => 'yellow'],
                    ['id' => 'totalRejected','label' => 'Rejected', 'icon' => 'fa-ban',             'color' => 'red']
                ] as $card)
                <div class="bg-white dark:bg-gray-800 p-4 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-md bg-{{ $card['color'] }}-100 dark:bg-{{ $card['color'] }}-900/30 flex items-center justify-center text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{{ $card['label'] }}</p>
                        <p id="{{ $card['id'] }}" class="text-xl font-bold text-gray-900 dark:text-gray-100">-</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Status Tabs --}}
                <div class="px-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-6 overflow-x-auto no-scrollbar" id="status-tabs-container">
                    @foreach(['All' => 'All Files', 'draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $text)
                    <button type="button" 
                        class="status-tab relative py-4 text-sm font-semibold transition-all whitespace-nowrap {{ $val === 'All' ? 'text-indigo-600 active-tab' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}"
                        data-status="{{ $val }}">
                        {{ $text }}
                        <span class="absolute bottom-0 left-0 w-full h-1 bg-indigo-600 rounded-t-full tab-indicator {{ $val === 'All' ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-200"></span>
                    </button>
                    @endforeach
                </div>

                <div class="p-0 overflow-x-auto w-full">
                    <table id="fileTable" class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 font-semibold">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3 min-w-[200px]">Package Info</th>
                        <th class="px-4 py-3 w-28">Revision</th>
                        <th class="px-4 py-3">ECN No</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Part Group</th>
                        <th class="px-4 py-3 w-32">Uploaded At</th>
                        <th class="px-4 py-3 w-28 text-center">Status</th>
                        <th class="px-4 py-3 w-24 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 border-t border-gray-100 dark:border-gray-700">
                    {{-- JS will inject initial skeletons here immediately on load --}}
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentStatus = 'All';
    
    // Helper for highlighting search terms (Stabilo effect)
    function highlightText(data, searchVal) {
        if (!searchVal || !data) return data;
        const safeSearch = searchVal.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${safeSearch})`, 'gi');
        return data.toString().replace(regex, '<span class="bg-yellow-200 text-gray-900">$1</span>');
    }

    // Skeleton Loader logic
    function getSkeleton() {
        return `
            <tr class="skeleton-row">
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-8 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="space-y-2"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-48 animate-pulse"></div><div class="h-3 bg-gray-100 dark:bg-gray-800 rounded w-32 animate-pulse"></div></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16 animate-pulse"></div></td>
                <td class="px-4 py-4"><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 animate-pulse"></div></td>
                <td class="px-4 py-4" align="center"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded-full w-16 animate-pulse"></div></td>
                <td class="px-4 py-4" align="center"><div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-md w-8 animate-pulse"></div></td>
            </tr>
        `;
    }

    $(document).ready(function() {
        // 1. URL Sync & Initial State (Power Feature)
        const urlParams = new URLSearchParams(window.location.search);
        
        // Restore filters from URL if present
        if(urlParams.get('q')) $('#custom-upload-search').val(urlParams.get('q'));
        if(urlParams.get('status')) {
            currentStatus = urlParams.get('status');
            // Update tab UI
            $('.status-tab').removeClass('text-indigo-600 active-tab').addClass('text-gray-400');
            $('.tab-indicator').removeClass('opacity-100').addClass('opacity-0');
            const $activeTab = $(`.status-tab[data-status="${currentStatus}"]`);
            $activeTab.removeClass('text-gray-400').addClass('text-indigo-600 active-tab');
            $activeTab.find('.tab-indicator').removeClass('opacity-0').addClass('opacity-100');
        }

        // 2. Keyboard shortcut: Press "/" to search
        $(document).on('keyup', function(e) {
            if (e.key === '/' && !$(e.target).is('input, textarea, select')) {
                $('#custom-upload-search').focus();
            }
        });

        // 3. Inject initial skeletons
        let initialSkeletons = '';
        for (let i = 0; i < 8; i++) initialSkeletons += getSkeleton();
        $('#fileTable tbody').html(initialSkeletons);

        initSelect2();

        let searchTimeout;
        const $staticIcon = $('#search-icon-static');
        const $loadingIcon = $('#search-icon-loading');

        let table = $('#fileTable').DataTable({
            processing: false,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            deferRender: true, // Optimization: lazy render rows
            stateSave: false,  // Set to true if you want to remember filters on refresh
            ajax: {
                url: '{{ route("api.files.list") }}',
                type: 'GET',
                data: function(d) {
                    d.customer = $('#filter-customer').val();
                    d.model = $('#filter-model').val();
                    d.doc_type = $('#filter-doc-type').val();
                    d.category = $('#filter-category').val();
                    d.status = currentStatus;
                    d.search = { value: $('#custom-upload-search').val() };
                }
            },
            order: [[6, "desc"]], 
            dom: 't<"flex flex-col sm:flex-row justify-between items-center p-6 border-t border-gray-100 dark:border-gray-700 gap-4" <"text-gray-500 dark:text-gray-400 text-xs font-mono"i> <"flex justify-end"p>>',
            
            createdRow: function(row, data, dataIndex) {
                 $(row).addClass('hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors cursor-pointer border-b border-gray-50 dark:border-gray-800 last:border-0 text-gray-900 dark:text-gray-100');
                 $('td', row).addClass('py-4 px-4 align-middle');
            },

            columns: [
                {
                    data: null,
                    name: 'No',
                    orderable: false,
                    searchable: false,
                    className: 'text-center text-gray-400 font-mono text-[10px]'
                },
                {
                    data: null,
                    name: 'Package Info',
                    searchable: true,
                    orderable: false,
                    render: function(data, type, row) {
                        const searchVal = $('#custom-upload-search').val();
                        
                        // Line 1: Part No + Partners
                        let mainText = row.part_no;
                        if (row.partners) {
                            let pClean = row.partners.replace(/,/g, ' / ');
                            mainText += ` / ${pClean}`;
                        }
                        mainText = highlightText(mainText, searchVal);

                        // Line 2: Customer - Model
                        let subText = highlightText(`${row.customer} - ${row.model}`, searchVal);

                        return `
                            <div class="flex flex-col min-w-[200px]">
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">${mainText}</span>
                                <div class="text-[11px] text-gray-600 dark:text-gray-400 mt-0.5 whitespace-nowrap">
                                    ${subText}
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: null,
                    name: 'Revision',
                    searchable: true,
                    orderable: false,
                    render: function(data, type, row) {
                        let labelBadges = '';
                        if(row.revision_label_name) {
                            labelBadges = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 mr-1 whitespace-nowrap">${row.revision_label_name}</span>`;
                        }
                        return `
                            <div class="flex items-center">
                                ${labelBadges}
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 whitespace-nowrap">
                                    REV ${row.revision_no}
                                </span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'ecn_no',
                    name: 'ecn_no',
                    searchable: true,
                    render: function(data) {
                        const searchVal = $('#custom-upload-search').val();
                        return data ? `<span class="font-mono text-[11px] text-gray-600 dark:text-gray-400">${highlightText(data, searchVal)}</span>` : '<span class="text-gray-200">-</span>';
                    }
                },
                {
                    data: null,
                    name: 'doctype_group',
                    searchable: true,
                    orderable: true,
                    render: function(data, type, row) {
                        return `
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 capitalize">${row.doctype_group}</span>
                                <span class="text-[10px] text-gray-600 dark:text-gray-400">${row.doctype_subcategory || ''}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'part_group',
                    name: 'part_group',
                    searchable: true,
                    orderable: true,
                    render: d => `<span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 whitespace-nowrap p-1 bg-gray-50 dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700">${d}</span>`
                },
                {
                    data: 'uploaded_at',
                    name: 'uploaded_at',
                    searchable: true,
                    render: function(data) {
                        if(!data) return '-';
                        const d = new Date(data);
                        return `
                            <div class="flex flex-col text-[11px] font-mono text-gray-600 dark:text-gray-400">
                                <span>${d.toLocaleDateString()}</span>
                                <span class="opacity-80 text-[10px]">${d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center',
                    render: function(data) {
                        let base = 'px-2 py-2 text-[9px] font-black uppercase rounded-full shadow-sm border ';
                        let colors = {
                            draft: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
                            pending: 'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800',
                            rejected: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
                            approved: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800'
                        };
                        return `<span class="${base} ${colors[data] || 'bg-gray-100 text-gray-600 border-gray-200'}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    name: 'Action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                        <button type="button" onclick="openPackageDetails('${row.id}')" 
                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-indigo-50 text-indigo-600 dark:text-indigo-400 dark:hover:bg-gray-700 transition-all mx-auto border border-transparent hover:border-indigo-100" 
                            title="Manage Files">
                            <i class="fa-solid fa-up-right-from-square text-sm"></i>
                        </button>`;
                    }
                }
            ]
        });

        // Tab Handler
        $('.status-tab').on('click', function() {
            const $container = $('#status-tabs-container');
            
            // 1. Reset all tabs
            $container.find('.status-tab').removeClass('text-indigo-600 active-tab').addClass('text-gray-400');
            $container.find('.tab-indicator').removeClass('opacity-100').addClass('opacity-0');
            
            // 2. Activate clicked tab
            $(this).removeClass('text-gray-400').addClass('text-indigo-600 active-tab');
            $(this).find('.tab-indicator').removeClass('opacity-0').addClass('opacity-100');

            currentStatus = $(this).data('status');
            syncUrlWithFilters();
            table.draw();
        });

        function syncUrlWithFilters() {
            const params = new URLSearchParams(window.location.search);
            const q = $('#custom-upload-search').val();
            const status = currentStatus;
            
            if (q) params.set('q', q); else params.delete('q');
            if (status && status !== 'All') params.set('status', status); else params.delete('status');
            
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.replaceState({path: newUrl}, '', newUrl);
        }

        // Skeleton Loader Event Handling - Triggered on every AJAX start
        table.on('preXhr.dt', function (e, settings, data) {
            let skeletonHtml = '';
            for (let i = 0; i < 8; i++) skeletonHtml += getSkeleton();
            $('#fileTable tbody').html(skeletonHtml);
        });

        // High-speed search handler
        $('#custom-upload-search').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                $staticIcon.addClass('opacity-0');
                $loadingIcon.removeClass('opacity-0').addClass('opacity-100');
                syncUrlWithFilters();
                table.draw();
            }, 500);
        });

        $('#btnResetUploadFilters').on('click', function() {
            $('.js-upload-filter').val('All').trigger('change');
            $('#custom-upload-search').val('').trigger('keyup');
            $('.status-tab[data-status="All"]').trigger('click');
        });

        $('.js-upload-filter').on('change', function() { table.draw(); });

        table.on('draw.dt', function() {
            $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
            $staticIcon.removeClass('opacity-0');
            
            const json = table.ajax.json();
            if (json && json.kpis) {
                $('#totalUpload').text(json.kpis.totalupload || 0);
                $('#totalDraft').text(json.kpis.totaldraft || 0);
                $('#totalPending').text(json.kpis.totalpending || 0);
                $('#totalRejected').text(json.kpis.totalrejected || 0);
            }

            var PageInfo = $('#fileTable').DataTable().page.info();
            table.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });


        $('#fileTable tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('button').length) return;

            const data = table.row(this).data();
            if (!data || !data.id) return;

            let targetUrl = `{{ route('drawing.upload') }}`;
            targetUrl += '?revision_id=' + data.id;

            if (data.status !== 'draft') {
                targetUrl += '&read_only=true';
            }

            const t = detectTheme();
            const titleText = data.status === 'draft' ? 'Loading Draft...' : 'Opening Details...';
            Swal.fire({
                title: titleText,
                text: 'Redirecting to details page, please wait.',
                // icon: 'info',
                timer: 1500,
                showConfirmButton: false,
                allowOutsideClick: false,
                iconColor: t.icon.info,
                background: t.bg,
                color: t.fg,
                customClass: {
                    popup: 'border',
                    loader: 'custom-loader-color'
                },
                didOpen: () => {
                    Swal.showLoading();
                    const popup = Swal.getPopup();
                    if (popup) {
                        popup.style.borderColor = t.border;
                    }
                    const loader = Swal.getLoader();
                    if (loader) {
                        loader.style.borderColor = `${t.icon.info} transparent transparent transparent`;
                    }
                    window.location.href = targetUrl;
                }
            });
        });
    });

    function initSelect2() {
        const selectConfigs = [
            { id: '#filter-customer', select2: 'customer' },
            { id: '#filter-model',    select2: 'model',    dependsOn: '#filter-customer' },
            { id: '#filter-doc-type', select2: 'doc_type' },
            { id: '#filter-category', select2: 'category', dependsOn: '#filter-doc-type' }
        ];

        selectConfigs.forEach(conf => {
            $(conf.id).select2({
                placeholder: 'All',
                allowClear: false,
                ajax: {
                    url: '{{ route("api.export.filters") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => {
                        let query = {
                            select2: conf.select2,
                            q: params.term,
                            page: params.page || 1
                        };
                        if (conf.dependsOn) {
                            const depVal = $(conf.dependsOn).val();
                            if (conf.select2 === 'model') query.customer_code = depVal;
                            if (conf.select2 === 'category') query.doc_type = depVal;
                        }
                        return query;
                    },
                    processResults: (data, params) => {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });
        });

        $('#filter-status').select2({ minimumResultsForSearch: -1 });
    }

    function deleteFile(id) {
        if (confirm('Are you sure you want to delete this file?')) {
            alert('Delete functionality to be implemented for ID: ' + id);
        }
    }


    // Modal utilities
    function bytesToSize(bytes) {
        if (!bytes && bytes !== 0) return '0 Bytes';
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes === 0) return '0 Bytes';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatDateTime(dt) {
        if (!dt) return '-';
        try {
            const d = new Date(dt);
            if (isNaN(d.getTime())) return dt;
            return d.toLocaleString();
        } catch (e) {
            return dt;
        }
    }

    function formatDateOnly(dt) {
        if (!dt) return '-';
        try {
            const datePart = dt.split('T')[0];
            const parts = datePart.split('-');

            if (parts.length === 3 && parts[0].length === 4) {
                const year = parts[0];
                const month = parseInt(parts[1], 10);
                const day = parseInt(parts[2], 10);

                return `${day}/${month}/${year}`;
            } else {
                const d = new Date(dt);
                if (isNaN(d.getTime())) return dt;
                return `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
            }
        } catch (e) {
            return dt;
        }
    }

    function closePackageDetails() {
        const modal = document.getElementById('package-details-modal');
        if (modal) {
            if (modal._cleanup) try {
                modal._cleanup();
            } catch (e) {}
            modal.remove();
        }
    }

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

    function formatTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + "y ago";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + "mo ago";
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + "d ago";
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + "h ago";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + "m ago";
        return Math.floor(seconds) + "s ago";
    }

    function renderActivityLogs(logs) {
        const container = $('#activity-log-content');
        container.empty();

        if (!logs || !logs.length) {
            container.html(
                '<div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500"><i class="fa-regular fa-calendar-xmark text-2xl mb-2"></i><p class="text-xs">No activity recorded yet.</p></div>'
            );
            return;
        }

        // Sort logs desc
        logs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        logs.forEach((l, index) => {
            const m = l.meta || {};
            const code = l.activity_code;
            const isLast = index === logs.length - 1;

            let icon = 'fa-circle-info';
            let colorClass = 'bg-gray-100 text-gray-600';
            let title = code;

            if (code === 'UPLOAD') {
                icon = 'fa-cloud-arrow-up';
                colorClass = 'bg-blue-100 text-blue-600';
                title = 'Uploaded';
            } else if (code === 'SUBMIT_APPROVAL') {
                icon = 'fa-paper-plane';
                colorClass = 'bg-yellow-100 text-yellow-600';
                title = 'Approval Requested';
            } else if (code === 'APPROVE') {
                icon = 'fa-check';
                colorClass = 'bg-green-100 text-green-600';
                title = 'Approved';
            } else if (code === 'REJECT') {
                icon = 'fa-xmark';
                colorClass = 'bg-red-100 text-red-600';
                title = 'Rejected';
            } else if (code === 'ROLLBACK') {
                icon = 'fa-rotate-left';
                colorClass = 'bg-amber-100 text-amber-600';
                title = 'Rollback';
            } else if (code === 'DOWNLOAD') {
                icon = 'fa-download';
                colorClass = 'bg-gray-100 text-gray-600';
                title = 'Downloaded';
            } else if (code.includes('SHARE')) {
                icon = 'fa-share-nodes';
                colorClass = 'bg-indigo-100 text-indigo-600';
                title = 'Shared';
            } else if (code === 'REVISE_CONFIRM') {
                icon = 'fa-pen-to-square';
                colorClass = 'bg-purple-100 text-purple-600';
                title = 'Revision Confirmed';
            }

            const timeStr = l.created_at ? formatDateTime(l.created_at) : '-';
            const userStr = l.user_name || 'System';

            // Content Logic
            let contentHtml = '';

            // Snapshot / Meta Details
            if (m.part_no || m.ecn_no) {
                contentHtml += `
                        <div class="mt-2 p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded text-xs shadow-sm">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-800 dark:text-gray-200">${m.part_no || '-'}</span>
                                <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded font-mono text-[10px] border border-gray-200 dark:border-gray-600">
                                    Rev ${m.revision_no ?? '-'}
                                </span>
                                ${m.ecn_no ? `<span class="text-blue-600 dark:text-blue-400 font-mono text-[10px] bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800">${m.ecn_no}</span>` : ''}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] flex items-center gap-1">
                                <i class="fa-solid fa-tag text-[9px]"></i>
                                <span>${m.customer_code || m.customer || '-'}</span>
                                <span class="mx-0.5">•</span>
                                <span>${m.model_name || m.model || '-'}</span>
                                ${m.doctype_group ? `<span><span class="mx-0.5">•</span>${m.doctype_group}</span>` : ''}
                            </div>
                            ${code === 'ROLLBACK' && m.previous_status ? `
                                <div class="mt-1.5 pt-1.5 border-t border-gray-100 dark:border-gray-700 flex items-center text-amber-600 dark:text-amber-500 font-medium">
                                    <i class="fa-solid fa-code-branch mr-1.5 text-[10px]"></i>
                                    <span class="capitalize">${m.previous_status}</span>
                                    <i class="fa-solid fa-arrow-right-long mx-1.5 text-[10px]"></i>
                                    <span>Waiting</span>
                                </div>
                            ` : ''}
                        </div>
                    `;
            }

            // Note
            if (m.note) {
                contentHtml += `
                        <div class="mt-1.5 flex items-start gap-1.5">
                            <i class="fa-solid fa-quote-left text-gray-300 dark:text-gray-600 text-[10px] mt-0.5"></i>
                            <p class="text-xs text-gray-600 dark:text-gray-300 italic">${m.note}</p>
                        </div>
                    `;
            }

            // Specific for Share
            if (code.includes('SHARE')) {
                let target = m.shared_to_dept || m.shared_with || m.shared_to || '-';
                target = target.replace('[EXP] ', '');

                contentHtml += `
                        <div class="mt-1.5 text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-1">
                                <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                                <span>To: <strong>${target}</strong></span>
                            </div>
                            ${m.recipients ? `<div class="mt-0.5 ml-3.5 text-[10px] text-gray-500">Recipients: ${m.recipients}</div>` : ''}
                            ${m.expired_at ? `<div class="mt-0.5 ml-3.5 text-[10px] text-red-500">Exp: ${m.expired_at}</div>` : ''}
                        </div>
                     `;
            }

            // Specific for Download
            if (code === 'DOWNLOAD' && m.downloaded_file) {
                contentHtml += `
                        <div class="mt-1.5 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            <i class="fa-solid fa-file text-[10px]"></i>
                            <span>${m.downloaded_file}</span>
                            ${m.file_size ? `<span class="text-gray-400">(${m.file_size})</span>` : ''}
                        </div>
                     `;
            }

            const el = $(`
                    <div class="relative flex gap-3">
                        ${!isLast ? '<div class="absolute top-4 left-3 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></div>' : ''}
                        
                        <div class="relative flex-shrink-0 mt-1">
                             <div class="w-6 h-6 rounded-full flex items-center justify-center ${colorClass} ring-4 ring-white dark:ring-gray-800 z-10 relative">
                                <i class="fa-solid ${icon} text-xs"></i>
                             </div>
                        </div>
                        
                        <div class="flex-1 min-w-0 mb-6 ${isLast ? 'mb-0' : ''}">
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-bold capitalize">${title}</span>
                                        <span class="text-xs text-gray-500 font-normal">by</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">${userStr}</span>
                                    </p>
                                    <span class="text-[10px] text-gray-400 whitespace-nowrap ml-2">${timeStr}</span>
                                </div>
                                ${contentHtml}
                            </div>
                        </div>
                    </div>
                `);

            container.append(el);
        });
    }

    window.openPackageDetails = function(id) {
        const existing = document.getElementById('package-details-modal');
        if (existing) existing.remove();

        const loaderOverlay = document.createElement('div');
        loaderOverlay.id = 'package-details-modal';
        loaderOverlay.className = 'fixed inset-0 bg-black bg-opacity-40 p-4 flex items-center justify-center z-50';
        loaderOverlay.innerHTML = `<div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center gap-3"><div class="loader-border w-8 h-8 border-4 border-blue-400 rounded-full animate-spin"></div><div class="text-sm text-gray-700 dark:text-gray-300">Loading package details...</div></div>`;
        document.body.appendChild(loaderOverlay);

        fetch(`{{ url('/files') }}` + '/' + encodeURIComponent(id))
            .then(res => {
                if (!res.ok) throw new Error('Failed to load details');
                return res.json();
            })
            .then(json => {
                const pkg = json.package || {};
                const files = json.files || {
                    count: 0,
                    size_bytes: 0
                };

                const loader = document.getElementById('package-details-modal');
                if (loader) loader.remove();

                const packageNo = pkg.package_no ?? pkg.packageNo ?? 'N/A';
                const revisionNo = pkg.revision_no ?? pkg.current_revision_no ?? pkg.revisionNo ?? 0;
                const customerCode = pkg.customer_code ?? pkg.customerCode ?? pkg.customer_code ?? '-';
                const modelName = pkg.model_name ?? pkg.modelName ?? '-';
                const partNo = pkg.part_no ?? pkg.partNo ?? '-';
                const docgroupName = pkg.docgroup_name ?? pkg.docgroupName ?? '-';
                const subcatName = pkg.subcategory_name ?? pkg.subcategoryName ?? '-';
                const partGroup = pkg.code_part_group ?? pkg.codePartGroup ?? '-';
                const createdAt = formatDateTime(pkg.created_at ?? pkg.revision_created_at ?? pkg.createdAt);
                const updatedAt = formatDateTime(pkg.updated_at ?? pkg.updatedAt);
                const receiptDate = formatDateOnly(pkg.receipt_date);
                const revisionStatus = pkg.revision_status ?? pkg.revisionStatus ?? pkg.status ?? '-';
                const revisionNote = pkg.revision_note ?? pkg.note ?? '';
                const isObsolete = (pkg.is_obsolete === 1 || pkg.is_obsolete === '1');
                const ecnNo = pkg.ecn_no ?? '-';
                const revisionLabel = pkg.revision_label_name ?? '-';

                const overlay = document.createElement('div');
                overlay.id = 'package-details-modal';
                overlay.className = 'fixed inset-0 bg-black bg-opacity-40 p-4 flex items-center justify-center z-50';
                overlay.addEventListener('click', function(ev) {
                    if (ev.target === overlay) closePackageDetails();
                });

                const dialog = document.createElement('div');
                dialog.className = 'bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-4xl w-full overflow-hidden';
                dialog.setAttribute('role', 'dialog');
                dialog.setAttribute('aria-modal', 'true');
                dialog.innerHTML = `
                        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 text-blue-500 bg-blue-100 dark:bg-blue-900/50 rounded-full">
                                    <i class="fa-solid fa-box-archive"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Package Details</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Package No: <span class="font-semibold text-gray-700 dark:text-gray-200">${packageNo}</span></p>
                                </div>
                            </div>
                            <button id="pkg-close-btn" class="text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                                <i class="fa-solid fa-xmark fa-xl"></i>
                            </button>
                        </div>

                        <div class="p-5 max-h-[70vh] overflow-y-auto space-y-6">
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3">Revision & Status</h4>
                            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                                <div class="sm:col-span-1">
                                    <dt class="text-gray-500">Revision No.</dt>
                                    <dd class="font-semibold text-gray-900 dark:text-gray-100">${revisionNo}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-gray-500">Revision Label</dt>
                                    <dd class="font-semibold text-gray-900 dark:text-gray-100">${revisionLabel}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-gray-500">ECN No.</dt>
                                    <dd class="font-semibold text-gray-900 dark:text-gray-100">${ecnNo}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-gray-500">Status</dt>
                                    <dd class="font-semibold">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            ${revisionStatus === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' :
                                            (revisionStatus === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' :
                                            (revisionStatus === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' :
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'))}
                                        ">${revisionStatus}</span>
                                    </dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-gray-500">Obsolete</dt>
                                    <dd class="font-semibold text-gray-900 dark:text-gray-100">${isObsolete ? '<span class="text-red-500 font-bold">Yes</span>' : 'No'}</dd>
                                </div>
                            </dl>
                        </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Product Information</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Customer</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${customerCode}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Model</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${modelName}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Part No.</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${partNo}</dd></div>
                                    </dl>
                                </div>
                                <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Document Classification</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Document Group</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${docgroupName}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Sub Category</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${subcatName}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Part Group</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${partGroup}</dd></div>
                                    </dl>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Revision Note</h4>
                                <div class="p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${revisionNote || '<span class="italic text-gray-400">No note provided.</span>'}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">File Summary</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Total Files</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${files.count}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Total Size</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${bytesToSize(files.size_bytes)}</dd></div>
                                    </dl>
                                </div>
                                <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Timestamps</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Receipt Date</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${receiptDate}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Created At</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${createdAt}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Last Updated</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${updatedAt}</dd></div>
                                    </dl>
                                </div>
                            </div>

                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center">
                                    <i class="fa-solid fa-clipboard-list mr-2 text-blue-500"></i>
                                    Activity Log
                                </h4>
                                <div id="activity-log-content" class="max-h-96 overflow-y-auto pr-2 pl-1 pt-1">
                                    <p class="italic text-center text-gray-500 dark:text-gray-400">Loading activity logs...</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                            <button id="pkg-close-btn-2" class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                <i class="fa-solid fa-xmark"></i>
                                Close
                            </button>
                        </div>
                    `;

                overlay.appendChild(dialog);
                document.body.appendChild(overlay);

                const closeBtn = document.getElementById('pkg-close-btn');
                const closeBtn2 = document.getElementById('pkg-close-btn-2');
                if (closeBtn) closeBtn.addEventListener('click', closePackageDetails);
                if (closeBtn2) closeBtn2.addEventListener('click', closePackageDetails);
                if (closeBtn) closeBtn.focus();

                function escHandler(e) {
                    if (e.key === 'Escape') closePackageDetails();
                }
                document.addEventListener('keydown', escHandler);

                overlay._cleanup = function() {
                    document.removeEventListener('keydown', escHandler);
                };

                fetch(`{{ route('upload.drawing.activity-logs') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            customer: pkg.customer_id,
                            model: pkg.model_id,
                            partNo: pkg.product_id,
                            docType: pkg.docgroup_id,
                            category: pkg.subcategory_id || null,
                            partGroup: pkg.part_group_id,
                            revision_no: pkg.revision_no
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Failed to load activity logs');
                        return res.json();
                    })
                    .then(data => {
                        renderActivityLogs(data.logs || []);
                    })
                    .catch(err => {
                        console.error('Error fetching activity logs:', err);
                        $('#activity-log-content').html('<p class="italic text-center text-gray-500 dark:text-gray-400">Failed to load activity logs.</p>');
                    });
            })
            .catch(err => {
                const loader = document.getElementById('package-details-modal');
                if (loader) loader.remove();
                alert('Unable to load package details: ' + err.message);
            });
    }
</script>
@endpush

@push('styles')
<style>
    #activity-log-content::-webkit-scrollbar {
        width: 6px;
    }

    #activity-log-content::-webkit-scrollbar-track {
        background: transparent;
    }

    #activity-log-content::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 20px;
    }

    .dark #activity-log-content::-webkit-scrollbar-thumb {
        background-color: #4b5563;
    }

    #activity-log-content:hover::-webkit-scrollbar-thumb {
        background-color: #93c5fd;
    }

    .dark #activity-log-content:hover::-webkit-scrollbar-thumb {
        background-color: #60a5fa;
    }
</style>
@endpush