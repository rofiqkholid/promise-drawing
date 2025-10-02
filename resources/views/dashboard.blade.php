@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')
@section('content')

<div x-data="dashboardController()" x-init="init()">

    <div class="sm:flex sm:items-center sm:gap-x-24">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analys File Management</p>
        </div>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:mt-0">

            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
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

            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
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

            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
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

            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                <div class="flex items-center">
                    <div class="bg-red-100 dark:bg-red-900/50 text-red-500 dark:text-red-400 rounded-lg p-2 mr-3 flex items-center justify-center h-9 w-9">
                        <i class="fa-solid fa-users fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">User Active</h3>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-100">15</p>
                    </div>
                </div>
                <div class="mt-2 h-8 w-full"><canvas id="activeUsersChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
            <i class="fa-solid fa-filter mr-2 text-gray-500"></i>
            Filter Data
        </h3>
        <div class="grid grid-cols-1 xl:grid-cols-6 gap-x-12 gap-y-6 items-end">
            <div class="xl:col-span-2">
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
                    <select id="doc_group" name="doc_group" class="w-full">
                        <option value="ALL">ALL</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="sub_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sub Type</label>
                <div class="relative mt-1">
                    <select id="sub_type" name="sub_type" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
                        <option>ALL</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                </div>
            </div>
            <div>
                <label for="from_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                <input type="date" name="from_date" id="from_date" value="{{ date('Y-m-01') }}" class="mt-1 block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-0 focus:outline-none sm:text-sm py-2 px-3">
            </div>
            <div>
                <label for="to_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                <input type="date" name="to_date" id="to_date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-0 focus:outline-none sm:text-sm py-2 px-3">
            </div>

            <div class="xl:col-span-6"
                x-show="showExtraFilters"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-4"
                style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-end border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div>
                        <label for="customer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                        <div class="relative mt-1">
                            <select id="customer" name="customer" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
                                <option>ALL</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                        <div class="relative mt-1">
                            <select id="model" name="model" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
                                <option>ALL</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <div class="relative mt-1">
                            <select id="status" name="status" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
                                <option>ALL</option>
                                <option>Active</option>
                                <option>Obsolete</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>
                    <div>
                        <label for="part_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>
                        <div class="relative mt-1">
                            <select id="part_group" name="part_group" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
                                <option>ALL</option>
                                <option>Crankshaft</option>
                                <option>Piston</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-6 gap-8">
        <div class="lg:col-span-4 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-column mr-2 text-blue-500"></i>Plan vs Actual (Quantity) & Progress %</h3>
            <div class="h-96"><canvas id="planVsActualChart"></canvas></div>
        </div>
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-line mr-2 text-green-500"></i>Upload vs Download Trend</h3>
            <div class="h-96"><canvas id="uploadDownloadChart"></canvas></div>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    function dashboardController() {
        return {
            showExtraFilters: false,

            planVsActualChart: null,
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
                        component.initPartGroupSelect2();
                        component.initModelSelect2();
                        component.chartsInitialized = true;
                    });
                }, 100);
            },

            initDocGroupSelect2() {
                let component = this;

                $('#doc_group').select2({
                    dropdownParent: $('#doc_group').parent(),
                    width: '100%',
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
                            return {
                                results: data.results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Cari atau pilih Document Group',
                    minimumInputLength: 0
                }).on('change', function(e) {
                    let selectedValue = $(this).val();
                    component.showExtraFilters = (selectedValue !== 'ALL' && selectedValue !== '' && selectedValue !== null);
                });
            },
            initCustomerSelect2() {
                let component = this;

                $('#customer').select2({
                    dropdownParent: $('#customer').parent(),
                    width: '100%',
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
                            return {
                                results: data.results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Cari atau pilih Document Group',
                    minimumInputLength: 0
                }).on('change', function(e) {
                    let selectedValue = $(this).val();
                    component.showExtraFilters = (selectedValue !== 'ALL' && selectedValue !== '' && selectedValue !== null);
                });
            },
            initModelSelect2() {
                let component = this;

                $('#model').select2({
                    dropdownParent: $('#model').parent(),
                    width: '100%',
                    ajax: {
                        url: "{{ route('dashboard.getModel') }}",
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
                            return {
                                results: data.results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Cari atau pilih Document Group',
                    minimumInputLength: 0
                }).on('change', function(e) {
                    let selectedValue = $(this).val();
                    component.showExtraFilters = (selectedValue !== 'ALL' && selectedValue !== '' && selectedValue !== null);
                });
            },
            initPartGroupSelect2() {
                let component = this;

                $('#part_group').select2({
                    dropdownParent: $('#part_group').parent(),
                    width: '100%',
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
                            return {
                                results: data.results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Cari atau pilih Document Group',
                    minimumInputLength: 0
                }).on('change', function(e) {
                    let selectedValue = $(this).val();
                    component.showExtraFilters = (selectedValue !== 'ALL' && selectedValue !== '' && selectedValue !== null);
                });
            },
            initSubTypeSelect2() {
                let component = this;

                $('#sub_type').select2({
                    dropdownParent: $('#sub_type').parent(),
                    width: '100%',
                    ajax: {
                        url: "{{ route('dashboard.getSubType') }}",
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
                            return {
                                results: data.results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Cari atau pilih Document Group',
                    minimumInputLength: 0
                }).on('change', function(e) {
                    let selectedValue = $(this).val();
                    component.showExtraFilters = (selectedValue !== 'ALL' && selectedValue !== '' && selectedValue !== null);
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
                
                // Inisialisasi Chart "Total Documents"
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
                
                // Inisialisasi Chart "Uploads"
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
                
                // Inisialisasi Chart "Downloads"
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

                // Inisialisasi Chart "Active Users"
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

                // Inisialisasi Chart "Plan vs Actual"
                if (this.canvasExists('planVsActualChart')) {
                    try {
                        this.planVsActualChart = new Chart('planVsActualChart', {
                            data: {
                                labels: ['MMKI - 5H45', 'MMKI - 5J45', 'MMKI - 4L45W', 'HPM - TG4R', 'HPM - 3K6A', 'SUZUKI - YHA', 'TOYOTA - D03B'],
                                datasets: [{
                                    type: 'bar',
                                    label: 'Actual (docs)',
                                    data: [60, 88, 90, 115, 120, 148, 150],
                                    backgroundColor: 'rgba(22, 163, 74, 0.8)',
                                    yAxisID: 'y',
                                    order: 2
                                }, {
                                    type: 'bar',
                                    label: 'Plan (docs)',
                                    data: [80, 110, 140, 135, 170, 205, 205],
                                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                                    yAxisID: 'y',
                                    order: 2
                                }, {
                                    type: 'line',
                                    label: 'Progress %',
                                    data: [75, 80, 64, 85, 71, 72, 73],
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
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    x: {
                                        ticks: { color: textColor },
                                        grid: { display: false }
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
                                        ticks: { color: textColor },
                                        grid: { color: gridColor }
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
                                        grid: { drawOnChartArea: false },
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
                                        font: { weight: 'normal', size: 12 }
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
                            }
                        });
                    } catch (error) {
                        console.error('Error creating planVsActualChart:', error);
                    }
                }
                
                // Inisialisasi Chart "Upload vs Download Trend"
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
                                        ticks: { color: textColor },
                                        grid: { color: gridColor }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        suggestedMax: 180,
                                        ticks: { color: textColor },
                                        grid: { color: gridColor },
                                        title: {
                                            display: true,
                                            text: 'Documents',
                                            color: textColor
                                        }
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
                            // Tunggu sebentar sebelum re-inisialisasi chart
                            setTimeout(() => {
                                component.initCharts();
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