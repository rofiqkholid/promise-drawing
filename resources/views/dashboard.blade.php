@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')
@section('content')

<div x-data="dashboardController()" x-init="init()">

    {{-- Bagian Statistik Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-[15%]">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analys File Management</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
            {{-- Total Document --}}
            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                <div class="flex items-center">
                    <div class="bg-blue-100 dark:bg-blue-900/50 text-blue-500 dark:text-blue-400 rounded-lg p-2 mr-3 flex items-center justify-center h-9 w-9">
                        <i class="fa-solid fa-file-lines fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Document</h3>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-100">1024</p>
                    </div>
                </div>
                <div class="mt-2 h-8 w-full"><canvas id="totalDocsChart"></canvas></div>
            </div>
            {{-- Upload --}}
            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                <div class="flex items-center">
                    <div class="bg-green-100 dark:bg-green-900/50 text-green-500 dark:text-green-400 rounded-lg p-2 mr-3 flex items-center justify-center h-9 w-9">
                        <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Upload</h3>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-100">512</p>
                    </div>
                </div>
                <div class="mt-2 h-8 w-full"><canvas id="uploadsChart"></canvas></div>
            </div>
            {{-- Download --}}
            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                <div class="flex items-center">
                    <div class="bg-yellow-100 dark:bg-yellow-900/50 text-yellow-500 dark:text-yellow-400 rounded-lg p-2 mr-3 flex items-center justify-center h-9 w-9">
                        <i class="fa-solid fa-cloud-arrow-down fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Download</h3>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-100">403</p>
                    </div>
                </div>
                <div class="mt-2 h-8 w-full"><canvas id="downloadsChart"></canvas></div>
            </div>
            {{-- User Active --}}
            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                <div class="flex items-center">
                    <div class="bg-red-100 dark:bg-red-900/50 text-red-500 dark:text-red-400 rounded-lg p-2 mr-3 flex items-center justify-center h-9 w-9">
                        <i class="fa-solid fa-users fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">User Active</h3>
                        <p id="activeUserCount" class="text-xl font-bold text-gray-800 dark:text-gray-100">0</p>
                    </div>
                </div>
                <div class="mt-2 h-8 w-full"><canvas id="activeUsersChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Bagian Filter --}}
    <div class="mt-6 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
            <i class="fa-solid fa-filter mr-2 text-gray-500"></i>
            Filter Data
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
            <div>
                <label for="key_word" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key Word</label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input type="text" name="key_word" id="key_word" class="block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-500 focus:ring-0 focus:outline-none sm:text-sm py-2 pl-10 pr-3" placeholder="e.g. 721005233 or MMKI - 5J45">
                </div>
            </div>
            <div>
                <label for="doc_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document Group</label>
                <div class="relative mt-1">
                    <select id="doc_group" name="doc_group" class="w-full"></select>
                </div>
            </div>
            <div>
                <label for="sub_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <div class="relative mt-1">
                    <select id="sub_type" name="sub_type" class="w-full"></select>
                </div>
            </div>
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                <input type="text" id="date_range" class="mt-1 block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-0 focus:outline-none sm:text-sm py-2 px-3">
                <input type="hidden" name="from_date" id="from_date">
                <input type="hidden" name="to_date" id="to_date">
            </div>
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="customer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                <div class="relative mt-1">
                    <select id="customer" name="customer" class="w-full"></select>
                </div>
            </div>
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                <div class="relative mt-1">
                    <select id="model" name="model" class="w-full"></select>
                </div>
            </div>
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="project_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <div class="relative mt-1">
                    <select id="project_status" name="project_status" class="w-full"></select>
                </div>
            </div>
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="part_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>
                <div class="relative mt-1">
                    <select id="part_group" name="part_group" class="w-full"></select>
                </div>
            </div>
            <div class="lg:col-span-4 w-full flex justify-end items-center pt-4 border-t border-gray-200 dark:border-gray-700 mt-2">
                <div class="flex space-x-3">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Reset
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-check mr-2"></i>
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris Chart Utama (2 Chart) --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-column mr-2 text-blue-500"></i>Upload File Monitoring (ALL)</h3>
            <div class="h-96"><canvas id="planVsActualChart"></canvas></div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-column mr-2 text-purple-500"></i>Upload File Monitoring (Project)</h3>
            <div class="h-96"><canvas id="planVsActualProjectChart"></canvas></div>
        </div>
    </div>

    {{-- [MODIFIED] Baris Chart Trend & Newsfeed, diubah menjadi 2 kolom --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- [MODIFIED] Menghapus lg:col-span-1 --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-line mr-2 text-green-500"></i>Upload vs Download Trend</h3>
            <div class="flex-grow relative">
                <canvas id="uploadDownloadChart"></canvas>
            </div>
        </div>
        {{-- [MODIFIED] Menghapus lg:col-span-2 --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-newspaper mr-2 text-gray-500"></i>Newsfeed / Activity Log</h3>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <div class="grid grid-cols-[auto,1fr,auto,auto] items-center gap-x-6 py-4">
                    <div class="w-8 text-center"><i class="fa-solid fa-upload text-gray-500 text-lg"></i></div>
                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">Upload Part Dwg Rev2 - MMKI - 5J45</p>
                    <div class="text-right whitespace-nowrap">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Andi Pratama</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">2025-09-25 14:10</p>
                    </div>
                    <div></div>
                </div>
                <div class="grid grid-cols-[auto,1fr,auto,auto] items-center gap-x-6 py-4">
                    <div class="w-8 text-center"><i class="fa-solid fa-download text-blue-500 text-lg"></i></div>
                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">Download Assy Dwg - SUZUKI - YHA</p>
                    <div class="text-right whitespace-nowrap">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Budi Santoso</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">2025-09-25 09:30</p>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap"><span class="font-semibold">Tujuan:</span> Dikirim ke Customer SUZUKI</div>
                </div>
                <div class="grid grid-cols-[auto,1fr,auto,auto] items-center gap-x-6 py-4">
                    <div class="w-8 text-center">
                        <div class="bg-blue-500 text-white rounded-full h-6 w-6 flex items-center justify-center mx-auto"><i class="fa-solid fa-arrows-rotate fa-xs"></i></div>
                    </div>
                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">Revision Jig Design Rev1 - HPM - TG4R</p>
                    <div class="text-right whitespace-nowrap">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Citra Lestari</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">2025-09-24 11:15</p>
                    </div>
                    <div></div>
                </div>
                <div class="grid grid-cols-[auto,1fr,auto,auto] items-center gap-x-6 py-4">
                    <div class="w-8 text-center">
                        <div class="bg-green-500 text-white rounded-md h-6 w-6 flex items-center justify-center mx-auto"><i class="fa-solid fa-check"></i></div>
                    </div>
                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">Approval Std Part Bolt - TOYOTA - D03B</p>
                    <div class="text-right whitespace-nowrap">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">David Firmansyah</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">2025-09-23 16:45</p>
                    </div>
                    <div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/70 dark:text-green-300">Approved</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchActiveUsers();
    });

    const startDate = "{{ date('Y-m-d', strtotime('first day of this month')) }}";
    const endDate = "{{ date('Y-m-d') }}";

    const picker = new Litepicker({
        element: document.getElementById('date_range'),
        singleMode: false,
        format: 'DD MMM YYYY',
        tooltipText: {
            one: 'day',
            other: 'days'
        },
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                const from = date1.format('YYYY-MM-DD');
                const to = date2.format('YYYY-MM-DD');
                document.getElementById('from_date').value = from;
                document.getElementById('to_date').value = to;
            });
        }
    });
    picker.setDateRange(new Date(startDate), new Date(endDate));
    document.getElementById('from_date').value = startDate;
    document.getElementById('to_date').value = endDate;

    function fetchActiveUsers() {
        const apiUrl = '/api/active-users-count';
        const userCountElement = document.getElementById('activeUserCount');
        userCountElement.textContent = '...';
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.status === 'success') {
                    userCountElement.textContent = data.count;
                }
            })
            .catch(error => {
                console.error('Error fetching active users:', error);
                userCountElement.textContent = 'Error';
            });
    }

    function dashboardController() {
        return {
            showExtraFilters: false,

            planVsActualChart: null,
            planVsActualProjectChart: null,
            uploadDownloadChart: null,
            totalDocsChart: null,
            uploadsChart: null,
            downloadsChart: null,
            activeUsersChart: null,

            chartsInitialized: false,

            init() {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        this.initializeDashboard();
                    });
                } else {
                    this.initializeDashboard();
                }
            },

            initializeDashboard() {
                const component = this;
                setTimeout(() => {
                    component.$nextTick(() => {
                        component.initCharts();
                        component.initDocGroupSelect2();
                        component.initSubTypeSelect2();
                        component.initCustomerSelect2();
                        component.initModelSelect2();
                        component.initPartGroupSelect2();
                        component.initStatusSelect2();
                        component.chartsInitialized = true;
                    });
                }, 100);
            },

            initDocGroupSelect2() {
                let component = this;
                $('#doc_group').select2({
                    dropdownParent: $('#doc_group').parent(),
                    width: '100%',
                    placeholder: 'Select Document Group',
                    ajax: {
                        url: "{{ route('dashboard.getDocumentGroups') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = data.results;
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    }
                }).on('change', function(e) {
                    let docGroupId = $(this).val();
                    component.showExtraFilters = (docGroupId !== 'ALL' && docGroupId !== '' && docGroupId !== null);
                    let subTypeSelect = $('#sub_type');
                    subTypeSelect.val('ALL').trigger('change.select2');
                    subTypeSelect.select2('destroy');
                    if (docGroupId && docGroupId !== 'ALL') {
                        subTypeSelect.prop('disabled', false);
                        component.initSubTypeSelect2(docGroupId);
                    } else {
                        subTypeSelect.prop('disabled', true);
                        component.initSubTypeSelect2();
                    }
                });
            },

            initSubTypeSelect2(docGroupId = null) {
                let subTypeSelect = $('#sub_type');
                let options = {
                    dropdownParent: subTypeSelect.parent(),
                    width: '100%',
                    placeholder: 'Doc Group First'
                };
                if (docGroupId) {
                    options.placeholder = 'Select Category';
                    options.ajax = {
                        url: "{{ route('dashboard.getSubType') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                document_group_id: docGroupId,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = data.results;
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    };
                }
                subTypeSelect.select2(options);
                if (!docGroupId) {
                    subTypeSelect.prop('disabled', true);
                }
            },

            initCustomerSelect2() {
                let component = this;
                $('#customer').select2({
                    dropdownParent: $('#customer').parent(),
                    width: '100%',
                    placeholder: 'Select Customer',
                    ajax: {
                        url: "{{ route('dashboard.getCustomer') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = data.results;
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    }
                }).on('change', function(e) {
                    let customerId = $(this).val();
                    let modelSelect = $('#model');
                    modelSelect.val('ALL').trigger('change.select2');
                    modelSelect.select2('destroy');
                    if (customerId && customerId !== 'ALL') {
                        modelSelect.prop('disabled', false);
                        component.initModelSelect2(customerId);
                    } else {
                        modelSelect.prop('disabled', true);
                        component.initModelSelect2();
                    }
                });
            },

            initModelSelect2(customerId = null) {
                let modelSelect = $('#model');
                let options = {
                    dropdownParent: modelSelect.parent(),
                    width: '100%',
                    placeholder: 'Select Customer First'
                };
                if (customerId) {
                    options.placeholder = 'Select Model';
                    options.ajax = {
                        url: "{{ route('dashboard.getModel') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                customer_id: customerId,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = data.results;
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    };
                }
                modelSelect.select2(options);
                if (!customerId) {
                    modelSelect.prop('disabled', true);
                }
            },

            initPartGroupSelect2() {
                $('#part_group').select2({
                    dropdownParent: $('#part_group').parent(),
                    width: '100%',
                    placeholder: 'Select Part Group',
                    ajax: {
                        url: "{{ route('dashboard.getPartGroup') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = data.results;
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    }
                });
            },

            initStatusSelect2() {
                $('#project_status').select2({
                    dropdownParent: $('#project_status').parent(),
                    width: '100%',
                    placeholder: 'Select Status',
                    ajax: {
                        url: "{{ route('dashboard.getStatus') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            let results = data.results;
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    }
                });
            },

            canvasExists(canvasId) {
                const canvas = document.getElementById(canvasId);
                return canvas !== null && canvas instanceof HTMLCanvasElement;
            },

            getCanvasContext(canvasId) {
                if (!this.canvasExists(canvasId)) {
                    console.warn(`Canvas element with id '${canvasId}' not found`);
                    return null;
                }
                const canvas = document.getElementById(canvasId);
                const context = canvas.getContext('2d');
                if (!context) {
                    console.error(`Unable to get 2D context for canvas '${canvasId}'`);
                    return null;
                }
                return context;
            },

            destroyChart(chartInstance) {
                if (chartInstance && typeof chartInstance.destroy === 'function') {
                    try {
                        chartInstance.destroy();
                    } catch (error) {
                        console.warn('Error destroying chart:', error);
                    }
                }
            },

            initCharts() {
                if (this.chartsInitialized) {
                    return;
                }
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js is not loaded!');
                    setTimeout(() => this.initCharts(), 100);
                    return;
                }

                this.destroyChart(this.totalDocsChart);
                this.destroyChart(this.uploadsChart);
                this.destroyChart(this.downloadsChart);
                this.destroyChart(this.activeUsersChart);
                this.destroyChart(this.planVsActualChart);
                this.destroyChart(this.planVsActualProjectChart);
                this.destroyChart(this.uploadDownloadChart);

                const textColor = document.documentElement.classList.contains('dark') ? '#d1d5db' : '#6b7280';
                const gridColor = document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

                const sparklineOptions = {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    elements: {
                        point: {
                            radius: 0
                        },
                        line: {
                            borderWidth: 2,
                            tension: 0.4
                        }
                    }
                };

                if (this.canvasExists('totalDocsChart')) {
                    try {
                        this.totalDocsChart = new Chart('totalDocsChart', {
                            type: 'line',
                            data: {
                                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                datasets: [{
                                    label: 'Total Documents',
                                    data: [980, 995, 1005, 1010, 1025, 1030, 1040],
                                    borderColor: 'rgba(59, 130, 246, 1)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    fill: true
                                }]
                            },
                            options: sparklineOptions
                        });
                    } catch (error) {
                        console.error('Error creating totalDocsChart:', error);
                    }
                }

                if (this.canvasExists('uploadsChart')) {
                    try {
                        this.uploadsChart = new Chart('uploadsChart', {
                            type: 'line',
                            data: {
                                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                datasets: [{
                                    label: 'Uploads',
                                    data: [60, 75, 70, 85, 95, 80, 105],
                                    borderColor: 'rgba(34, 197, 94, 1)',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    fill: true
                                }]
                            },
                            options: sparklineOptions
                        });
                    } catch (error) {
                        console.error('Error creating uploadsChart:', error);
                    }
                }

                if (this.canvasExists('downloadsChart')) {
                    try {
                        this.downloadsChart = new Chart('downloadsChart', {
                            type: 'line',
                            data: {
                                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                datasets: [{
                                    label: 'Downloads',
                                    data: [80, 70, 90, 85, 100, 110, 85],
                                    borderColor: 'rgba(245, 158, 11, 1)',
                                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                    fill: true
                                }]
                            },
                            options: sparklineOptions
                        });
                    } catch (error) {
                        console.error('Error creating downloadsChart:', error);
                    }
                }

                if (this.canvasExists('activeUsersChart')) {
                    try {
                        this.activeUsersChart = new Chart('activeUsersChart', {
                            type: 'line',
                            data: {
                                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                datasets: [{
                                    label: 'Active Users',
                                    data: [10, 12, 11, 14, 15, 13, 15],
                                    borderColor: 'rgba(239, 68, 68, 1)',
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    fill: true
                                }]
                            },
                            options: sparklineOptions
                        });
                    } catch (error) {
                        console.error('Error creating activeUsersChart:', error);
                    }
                }

                const mainChartOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Quantity (docs)',
                                color: textColor
                            },
                            suggestedMax: 220,
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Progress %',
                                color: textColor
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: (value) => value + '%',
                                color: textColor
                            },
                            suggestedMax: 100
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Bars = Quantity | Line = Progress %',
                            position: 'top',
                            align: 'end',
                            color: textColor,
                            font: {
                                weight: 'normal',
                                size: 12
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    }
                };

                if (this.canvasExists('planVsActualChart')) {
                    try {
                        this.planVsActualChart = new Chart('planVsActualChart', {
                            data: {
                                labels: ['TOYOTA - D03B', 'SUZUKI - YHA', 'HPM - 3K6A', 'MMKI - 4L45W', 'HPM - TG4R'],
                                datasets: [{
                                    type: 'bar',
                                    label: 'Actual (docs)',
                                    data: [150, 148, 120, 90, 115],
                                    backgroundColor: 'rgba(22, 163, 74, 0.8)',
                                    yAxisID: 'y',
                                    order: 2
                                }, {
                                    type: 'bar',
                                    label: 'Plan (docs)',
                                    data: [205, 205, 170, 140, 135],
                                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                                    yAxisID: 'y',
                                    order: 2
                                }, {
                                    type: 'line',
                                    label: 'Progress %',
                                    data: [73, 72, 71, 64, 85],
                                    borderColor: 'rgba(249, 115, 22, 1)',
                                    backgroundColor: 'rgba(249, 115, 22, 0.2)',
                                    yAxisID: 'y1',
                                    tension: 0.1,
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: 'rgba(249, 115, 22, 1)',
                                    order: 1
                                }]
                            },
                            options: mainChartOptions
                        });
                    } catch (error) {
                        console.error('Error creating planVsActualChart:', error);
                    }
                }

                if (this.canvasExists('planVsActualProjectChart')) {
                    try {
                        this.planVsActualProjectChart = new Chart('planVsActualProjectChart', {
                            data: {
                                labels: ['TOYOTA - D03B', 'SUZUKI - YHA', 'HPM - 3K6A', 'MMKI - 4L45W', 'HPM - TG4R'],
                                datasets: [{
                                    type: 'bar',
                                    label: 'Actual (docs)',
                                    data: [210, 180, 95, 140, 75],
                                    backgroundColor: 'rgba(107, 33, 168, 0.8)',
                                    yAxisID: 'y',
                                    order: 2
                                }, {
                                    type: 'bar',
                                    label: 'Plan (docs)',
                                    data: [250, 200, 150, 150, 100],
                                    backgroundColor: 'rgba(129, 140, 248, 0.8)',
                                    yAxisID: 'y',
                                    order: 2
                                }, {
                                    type: 'line',
                                    label: 'Progress %',
                                    data: [84, 90, 63, 93, 75],
                                    borderColor: 'rgba(217, 70, 239, 1)',
                                    backgroundColor: 'rgba(217, 70, 239, 0.2)',
                                    yAxisID: 'y1',
                                    tension: 0.1,
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: 'rgba(217, 70, 239, 1)',
                                    order: 1
                                }]
                            },
                            options: mainChartOptions
                        });
                    } catch (error) {
                        console.error('Error creating planVsActualProjectChart:', error);
                    }
                }

                if (this.canvasExists('uploadDownloadChart')) {
                    try {
                        this.uploadDownloadChart = new Chart('uploadDownloadChart', {
                            type: 'line',
                            data: {
                                labels: ['W1', 'W2', 'W3', 'W4'],
                                datasets: [{
                                    label: 'Download',
                                    data: [100, 130, 155, 145],
                                    borderColor: 'rgba(22, 163, 74, 1)',
                                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                                    tension: 0.3,
                                    fill: true,
                                    pointBackgroundColor: 'rgba(22, 163, 74, 1)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5
                                }, {
                                    label: 'Upload',
                                    data: [120, 145, 165, 158],
                                    borderColor: 'rgba(37, 99, 235, 1)',
                                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                    tension: 0.3,
                                    fill: true,
                                    pointBackgroundColor: 'rgba(37, 99, 235, 1)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    x: {
                                        ticks: {
                                            color: textColor
                                        },
                                        grid: {
                                            color: gridColor
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            color: textColor
                                        },
                                        grid: {
                                            color: gridColor
                                        },
                                        title: {
                                            display: true,
                                            text: 'Documents',
                                            color: textColor
                                        },
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: textColor,
                                            usePointStyle: true,
                                            padding: 15
                                        }
                                    }
                                }
                            }
                        });
                    } catch (error) {
                        console.error('Error creating uploadDownloadChart:', error);
                    }
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const dashboardElement = document.querySelector('[x-data="dashboardController()"]');
                    if (dashboardElement && dashboardElement.__x) {
                        const component = dashboardElement.__x.$data;
                        if (component && typeof component.initCharts === 'function') {
                            setTimeout(() => {
                                component.chartsInitialized = false;
                                component.initCharts();
                                component.chartsInitialized = true;
                            }, 300);
                        }
                    }
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    });
</script>
@endsection