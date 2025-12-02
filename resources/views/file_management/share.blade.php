@extends('layouts.app')
@section('title', 'Share Packages - PROMISE')
@section('header-title', 'Share Packages')

@section('content')

<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Share Packages</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Share packages with other supplier.</p>
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

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5">
            @foreach(['Customer', 'Model', 'Document Type', 'Category', 'Status'] as $label)
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
        <div class="overflow-x-hidden">
            <table id="approvalTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Package Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Share To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Decision Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
                </tbody>
            </table>
        </div>
    </div>

    <div id="shareModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75"
        style="display: none;">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Share Document Package</h3>
                <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fa-solid fa-times fa-lg"></i>
                </button>
            </div>

            <div class="p-6">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    Select one or more suppliers to share this package with.
                </p>

                <div>
                    <label for="supplierListContainer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select to Share</label>
                    <div class="relative mt-1"> <select id="supplierListContainer" name="supplierListContainer" class="w-full"></select> </div>
                    <div id="selectedSupplierContainer" class="mt-2"></div>
                </div>

                <input type="hidden" id="hiddenPackageId" value="">
            </div>

            <div class="flex justify-end p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 rounded-b-lg space-x-3">
                <button type="button"
                    class="btn-close-modal px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button id="btnSaveShare"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Share
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
                    }
                },

                order: [
                    [2, 'desc']
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
                        render: function(row) {
                            const revVal = row.revision ?? row.revision_no;
                            const revTxt = (revVal !== undefined && revVal !== null && revVal !== '') ?
                                `rev ${revVal}` :
                                '';

                            const parts = [
                                row.customer,
                                row.model,
                                row.doc_type,
                                row.category,
                                row.part_no,
                                revTxt
                            ].filter(Boolean);

                            return `<div class="text-sm">${parts.join(' - ')}</div>`;
                        }
                    },
                    {
                        data: 'share_to',
                        name: 'dp.share_to',
                    },
                    {
                        data: 'request_date',
                        name: 'dpr.requested_at',
                        render: function(v) {
                            const text = fmtDate(v);
                            return `<span title="${v || ''}">${text}</span>`;
                        }
                    },
                    {
                        data: 'decision_date',
                        name: 'dpr.decided_at',
                        render: function(v, t, row) {
                            if (!v) {
                                return '<span class="text-gray-400">—</span>';
                            }
                            const text = fmtDate(v);
                            return `<span title="${v}">${text}</span>`;
                        }
                    },
                    {
                        data: 'project_status',
                        name: 'project_status',
                        render: function(data, type, row) {

                            const value = row.project_status ?? row.project_status_name ?? data ?? '';

                            if (!value) {
                                return '<span class="text-xs text-gray-400 dark:text-gray-500">–</span>';
                            }


                            return `
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                   bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-300">
                ${value}
            </span>
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
                                    class="btn-share px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-300 
                                           bg-blue-100 dark:bg-blue-900/50 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/80"
                                    data-id="${packageId}" 
                                    title="Share package ${packageId}">
                                    <i class="fa-solid fa-share-nodes fa-fw"></i> Share
                                </button>
                            `;
                        }
                    }
                ],

                columnDefs: [{
                        targets: 0,
                        className: 'text-center w-12',
                        width: '48px'
                    },
                    {
                        targets: [2, 3],
                        className: 'whitespace-nowrap'
                    }
                ],

                responsive: true,
                dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
                createdRow: function(row) {
                    $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700/50');
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
            $('#customer, #model, #document-type, #category, #status').on('change', function() {
                if (table) table.ajax.reload(null, true);
            });

            $('#btnResetFilters').on('click', function() {
                resetSelect2ToAll($('#customer'));
                resetSelect2ToAll($('#model'));
                resetSelect2ToAll($('#document-type'));
                resetSelect2ToAll($('#category'));
                resetSelect2ToAll($('#status'));

                if (table) table.ajax.reload(null, true);
            });

            const $shareModal = $('#shareModal');
            const $supplierListContainer = $('#supplierListContainer');
            const $hiddenPackageId = $('#hiddenPackageId');
            const $shareError = $('#shareError');
            const $btnSaveShare = $('#btnSaveShare');


            $('body').on('click', '.btn-close-modal', function() {
                $shareModal.hide();
            });

            $shareModal.on('click', function(e) {
                if ($(e.target).is($shareModal)) {
                    $(this).hide();
                }
            });

            let selectedSupplier = [];

            function loadSuppliers() {
                $supplierListContainer.empty();
                $supplierListContainer.select2({
                    dropdownParent: $('#shareModal'),
                    width: '100%',
                    placeholder: 'Select suppliers...',
                    allowClear: true,
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
                            const formatted = data.map(item => ({
                                id: item.id,
                                text: item.code
                            }));
                            return {
                                results: formatted
                            };
                        },
                        cache: true
                    }
                });

                $supplierListContainer.on('select2:select', function(e) {
                    const data = e.params.data;
                    const exists = selectedSupplier.find(r => r.id === data.id);

                    if (!exists) {
                        selectedSupplier.push(data);
                        renderSelectSuppliers();
                    }

                    $supplierListContainer.val(null).trigger('change');
                });
            }

            function renderSelectSuppliers() {
                const $container = $('#selectedSupplierContainer');
                $container.empty();

                selectedSupplier.forEach(supplier => {
                    const item = $(`
                    <div class="flex items-center justify-between 
                                bg-gray-200 dark:bg-gray-700 
                                text-gray-700 dark:text-gray-200 
                                px-3 py-2 rounded-md mb-1 transition-colors">
                        <span class="text-sm">${supplier.text}</span>
                        <button 
                            type="button" 
                            class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-gray-300 
                                remove-select transition"
                            data-id="${supplier.id}">
                            &times;
                        </button>
                    </div>
                `);

                    $container.append(item);
                });
            }
            $(document).on('click', '.remove-select', function() {
                const id = $(this).data('id');
                $(this).parent().fadeOut(150, function() {
                    selectedSupplier = selectedSupplier.filter(r => r.id != id);
                    renderSelectSuppliers();
                });
            });




            $('#approvalTable tbody').on('click', '.btn-share', function(e) {
                e.stopPropagation();
                const packageId = $(this).data('id');
                if (!packageId) return;

                $hiddenPackageId.val(packageId);
                $btnSaveShare.prop('disabled', false).text('Share');

                loadSuppliers();

                $shareModal.show();
            });

            $('#approvalTable tbody').on('click', 'tr', function(e) {
                // kalau yang diklik adalah tombol Share, jangan redirect
                if ($(e.target).closest('.btn-share').length) return;

                const rowData = table.row(this).data();
                if (!rowData || !rowData.hash) return;

                // pakai placeholder __ID__ lalu di-replace di JS
                const url = '{{ route("share.detail", ["id" => "__ID__"]) }}'.replace('__ID__', rowData.hash);
                window.location.href = url;
            });


            $btnSaveShare.on('click', function() {
                const $this = $(this);
                const packageId = $hiddenPackageId.val();

                // Ambil ID dari supplier yang dipilih
                const selectSupplierIds = selectedSupplier.map(r => r.id);

                // Gunakan Toast untuk validasi UI
                if (!packageId) {
                    toastError('Error', 'Package ID not found. Please reload.');
                    return;
                }

                if (selectSupplierIds.length === 0) {
                    toastWarning('Warning', 'Please select at least one supplier.');
                    return;
                }

                $this.prop('disabled', true).text('Sharing...');

                $.ajax({
                    url: '{{ route("share.save") }}',
                    type: 'POST',
                    data: {
                        // Token CSRF sudah di-handle oleh $.ajaxSetup di atas
                        package_id: packageId,
                        supplier_ids: selectSupplierIds
                    },
                    dataType: 'json',
                    success: function(response) {
                        $shareModal.hide();

                        // Tampilkan pesan sukses dari server
                        toastSuccess('Shared', response.message || 'Package shared successfully!');

                        // Reload tabel tanpa reset paging
                        if (table) table.ajax.reload(null, false);

                        // Reset pilihan supplier agar modal bersih saat dibuka lagi nanti
                        selectedSupplier = [];
                        renderSelectSuppliers();
                    },
                    error: function(xhr) {
                        console.error('Failed to share:', xhr.responseText);
                        let msg = 'Failed to share package.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        if (xhr.status === 422) {
                            // 💡 untuk kasus Feasibility tanpa block → WARNING
                            toastWarning('Warning', msg);
                        } else {
                            // selain itu benar-benar error (500, 401, dll)
                            toastError('Error', msg);
                        }
                    },

                    complete: function() {
                        $this.prop('disabled', false).text('Share');
                    }
                });
            });

        }

        initTable();
        bindHandlers();
    });
</script>
@endpush