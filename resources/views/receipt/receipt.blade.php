@extends('layouts.app')
@section('title', 'Receipt - PROMISE')
@section('header-title', 'Receipt')

@section('content')

<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900" x-data="{ modalOpen: false }">
    <div class="sm:flex sm:items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Receipt</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage Your File in Data Center</p>
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
        <div class="overflow-x-auto">
            <table id="receiptTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doc Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Part No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rev</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
                </tbody>
            </table>
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

                order: [
                    [7, 'desc']
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
                    data: 'doc_type',
                    name: 'dtg.name'
                },
                {
                    data: 'category',
                    name: 'dsc.name'
                },
                {
                    data: 'part_no',
                    name: 'p.part_no'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const revVal = row.revision ?? row.revision_no;
                        return (revVal !== undefined && revVal !== null && revVal !== '') ? revVal : '<span class="text-gray-400">—</span>';
                    }
                },
                {
                    data: 'request_date',
                    name: 'dpr.shared_at',
                    render: function(v) {
                        const text = fmtDate(v);
                        return `<span title="${v || ''}">${text}</span>`;
                    }
                }
                ],

                columnDefs: [{
                    targets: 0,
                    className: 'text-center w-12',
                    width: '48px'
                },
                {
                    targets: [1, 2, 5],
                    className: 'whitespace-nowrap text-sm'
                },
                {
                    targets: [3, 4],
                    className: 'text-sm'
                },
                {
                    targets: 6,
                    className: 'text-center text-sm'
                },
                {
                    targets: 7,
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
    });
</script>
@endpush