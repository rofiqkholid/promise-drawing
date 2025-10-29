@extends('layouts.app')
@section('title', 'Dashboard Charts - PROMISE')
@section('header-title', 'Dashboard Charts')
@section('content')

<div x-data="dashboardController()" x-init="init()">

    {{-- BAGIAN FILTER --}}
    <div class="mt-6 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
            <i class="fa-solid fa-filter mr-2 text-gray-500"></i> Filter Data
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
            <div>
                <label for="key_word" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key Word (Part No)</label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"> <i class="fa-solid fa-magnifying-glass text-gray-400"></i> </div>
                    <input type="text" name="key_word" id="key_word" class="block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-500 focus:ring-0 focus:outline-none sm:text-sm py-2 pl-10 pr-3" placeholder="e.g. 721005233 or MMKI - 5J45">
                </div>
            </div>
            <div>
                <label for="doc_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document Group</label>
                <div class="relative mt-1"> <select id="doc_group" name="doc_group" class="w-full"></select> </div>
            </div>
            <div>
                <label for="sub_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <div class="relative mt-1"> <select id="sub_type" name="sub_type" class="w-full"></select> </div>
            </div>
            <div>
                <label for="month_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Month (Upload)</label>
                <input type="month" id="month_input" name="month_input" class="mt-1 block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-0 focus:outline-none sm:text-sm py-[0.35rem] px-3">
            </div>

            {{-- FILTER TAMBAHAN (MUNCUL SETELAH DOC GROUP DIPILIH) --}}
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="customer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                <div class="relative mt-1"> <select id="customer" name="customer" class="w-full"></select> </div>
            </div>
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                <div class="relative mt-1"> <select id="model" name="model" class="w-full"></select> </div>
            </div>
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="project_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <div class="relative mt-1"> <select id="project_status" name="project_status" class="w-full"></select> </div>
            </div>
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label for="part_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>
                <div class="relative mt-1"> <select id="part_group" name="part_group" class="w-full"></select> </div>
            </div>

            {{-- RADIO BUTTON SORT BY (DIPINDAHKAN KE PALING AKHIR DARI FILTER TAMBAHAN) --}}
            <div x-show="showExtraFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort By</label>
                <div class="mt-2 flex space-x-4 pt-1">
                    <div class="flex items-center">
                        <input id="sort_actual" name="sort_by" type="radio" value="actual" class="h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 dark:focus:ring-blue-600" checked>
                        <label for="sort_actual" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Sort by Actual</label>
                    </div>
                    <div class="flex items-center">
                        <input id="sort_plan" name="sort_by" type="radio" value="plan" class="h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 dark:focus:ring-blue-600">
                        <label for="sort_plan" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Sort by Plan</label>
                    </div>
                </div>
            </div>

            {{-- TOMBOL FILTER --}}
            <div class="lg:col-span-4 w-full flex justify-end items-center pt-4 border-t border-gray-200 dark:border-gray-700 mt-2">
                <div class="flex space-x-3">
                    <button type="button" @click="resetFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"> Reset </button>
                    <button type="button" @click="applyFilters" :disabled="isLoading" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed min-w-[150px]">
                        <span x-show="!isLoading"> <i class="fa-solid fa-check mr-2"></i> Apply Filter </span>
                        <span x-show="isLoading" style="display: none;"> <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg> Memuat... </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid untuk Chart --}}
    <div class="mt-6 grid grid-cols-1 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-column mr-2 text-blue-500"></i>Upload File Monitoring (ALL)</h3>
            <div class="h-96">
                <div id="planVsActualChart"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-column mr-2 text-purple-500"></i>Upload File Monitoring (Project)</h3>
            <div class="h-96">
                <div id="planVsActualProjectChart"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    function dashboardController() {
        return {
            showExtraFilters: false,
            apexPlanVsActualChart: null,
            apexPlanVsActualProjectChart: null,
            isLoading: false,
            isDarkMode: document.documentElement.classList.contains('dark'),

            init() {
                if (typeof ApexCharts === 'undefined') {
                    console.log('Waiting for ApexCharts...');
                    setTimeout(() => this.init(), 100);
                    return;
                }

                this.detectThemeChanges();

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => this.initializeDashboard());
                } else {
                    this.initializeDashboard();
                }
            },

            detectThemeChanges() {
                const component = this;

                const updateTheme = () => {
                    const newDarkMode = document.documentElement.classList.contains('dark');
                    if (component.isDarkMode !== newDarkMode) {
                        component.isDarkMode = newDarkMode;
                        component.updateChartTheme();
                    }
                };

                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            updateTheme();
                        }
                    });
                });

                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });

                window.addEventListener('theme-changed', updateTheme);
            },

            initializeDashboard() {
                const component = this;
                component.initCharts();
                
                component.initDocGroupSelect2();
                component.initSubTypeSelect2();
                component.initCustomerSelect2();
                component.initModelSelect2();
                component.initPartGroupSelect2();
                component.initStatusSelect2();

                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                document.getElementById('month_input').value = `${year}-${month}`;

                component.applyFilters();
            },

            updateChartTheme() {
                const newMode = this.isDarkMode ? 'dark' : 'light';
                const gridColor = newMode === 'dark' ? '#4A5568' : '#E2E8F0';
                const labelColor = newMode === 'dark' ? '#E2E8F0' : '#4A5568';
                const background = newMode === 'dark' ? '#1F2937' : '#FFFFFF';

                const themeOptions = {
                    theme: {
                        mode: newMode
                    },
                    chart: {
                        foreColor: labelColor,
                        background: background
                    },
                    xaxis: {
                        labels: {
                            style: {
                                colors: labelColor
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            style: {
                                color: labelColor
                            }
                        },
                        labels: {
                            style: {
                                colors: labelColor
                            }
                        }
                    },
                    grid: {
                        borderColor: gridColor
                    },
                    tooltip: {
                        theme: newMode
                    }
                };

                const mainCharts = [
                    this.apexPlanVsActualChart,
                    this.apexPlanVsActualProjectChart,
                ];

                mainCharts.forEach(chart => {
                    if (chart) {
                        chart.updateOptions(themeOptions);
                    }
                });

            },

            initCharts() {
                const charts = [
                    this.apexPlanVsActualChart,
                    this.apexPlanVsActualProjectChart,
                ];

                charts.forEach(chart => {
                    if (chart) {
                        chart.destroy();
                    }
                });

                const isDarkMode = this.isDarkMode;
                const gridColor = isDarkMode ? '#4A5568' : '#E2E8F0';
                const labelColor = isDarkMode ? '#E2E8F0' : '#4A5568';
                const background = isDarkMode ? '#1F2937' : '#FFFFFF';

                const mainChartOptions = {
                    series: [{
                        name: 'Plan',
                        type: 'bar',
                        data: []
                    },
                    {
                        name: 'Actual',
                        type: 'bar',
                        data: []
                    },
                    {
                        name: 'Percentace',
                        type: 'line',
                        data: []
                    }
                    ],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        foreColor: labelColor,
                        background: background
                    },
                    theme: {
                        mode: isDarkMode ? 'dark' : 'light'
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        enabledOnSeries: [2],
                        formatter: function(val) {
                            return val.toFixed(1) + '%';
                        },
                        style: {
                            fontWeight: 'semibold',
                        },
                        background: {
                            enabled: true,
                            borderRadius: 4,
                            padding: 7,
                            opacity: 0.9
                        }
                    },
                    stroke: {
                        show: true,
                        width: [0, 0, 3],
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: [],
                        labels: {
                            style: {
                                colors: labelColor
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: labelColor
                            }
                        }
                    },
                    grid: {
                        borderColor: gridColor
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        theme: isDarkMode ? 'dark' : 'light',
                        y: {
                            formatter: function(val) {
                                return val
                            }
                        }
                    },
                    noData: {
                        text: 'Loading chart data...',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: {
                            color: labelColor,
                            fontSize: '16px'
                        }
                    }
                };

                this.apexPlanVsActualChart = new ApexCharts(document.querySelector("#planVsActualChart"), mainChartOptions);
                this.apexPlanVsActualChart.render();

                this.apexPlanVsActualProjectChart = new ApexCharts(document.querySelector("#planVsActualProjectChart"), mainChartOptions);
                this.apexPlanVsActualProjectChart.render();
            },

            async applyFilters() {
                if (this.isLoading) return;
                this.isLoading = true;

                const isDarkMode = this.isDarkMode;
                const labelColor = isDarkMode ? '#E2E8F0' : '#4A5568';
                const loadingOptions = {
                    noData: {
                        text: 'Loading data...',
                        style: {
                            color: labelColor,
                            fontSize: '16px'
                        }
                    }
                };
                this.apexPlanVsActualChart?.updateOptions(loadingOptions);
                this.apexPlanVsActualProjectChart?.updateOptions(loadingOptions);

                try {
                    const filters = {
                        key_word: document.getElementById('key_word')?.value || '',
                        doc_group: $('#doc_group').val() || '',
                        sub_type: $('#sub_type').val() || '',
                        month: document.getElementById('month_input')?.value || '',
                        customer: $('#customer').val() || '',
                        model: $('#model').val() || '',
                        project_status: $('#project_status').val() || '',
                        part_group: $('#part_group').val() || '',
                        sort_by: document.querySelector('input[name="sort_by"]:checked')?.value || 'actual'
                    };

                    const params = new URLSearchParams();
                    for (const key in filters) {
                        if (filters[key] && filters[key] !== 'ALL') params.append(key, filters[key]);
                    }
                    const paramsString = params.toString();

                    const urlAll = `{{ route('api.upload-dashboard-data') }}?${paramsString}`;
                    const urlProject = `{{ route('api.upload-dashboard-data-project') }}?${paramsString}`;

                    const [responseAll, responseProject] = await Promise.all([
                        fetch(urlAll),
                        fetch(urlProject)
                    ]);

                    const errorOptionsAll = {
                        noData: {
                            text: 'Error loading data.',
                            style: {
                                color: '#EF4444',
                                fontSize: '16px'
                            }
                        }
                    };
                    if (responseAll.ok) {
                        const resultAll = await responseAll.json();
                        if (resultAll.status === 'success') {
                            this.updateBarChart(this.apexPlanVsActualChart, resultAll.data, 'ALL Chart');
                        } else {
                            console.error('Failed to fetch data for (ALL) chart:', resultAll.message);
                            this.apexPlanVsActualChart?.updateOptions(errorOptionsAll);
                        }
                    } else {
                        console.error('Network error for (ALL) chart:', responseAll.statusText);
                        const networkErrorOptions = {
                            ...errorOptionsAll,
                            noData: {
                                ...errorOptionsAll.noData,
                                text: 'Network error.'
                            }
                        };
                        this.apexPlanVsActualChart?.updateOptions(networkErrorOptions);
                    }

                    const errorOptionsProject = {
                        noData: {
                            text: 'Error loading data.',
                            style: {
                                color: '#EF4444',
                                fontSize: '16px'
                            }
                        }
                    };
                    if (responseProject.ok) {
                        const resultProject = await responseProject.json();
                        if (resultProject.status === 'success') {
                            this.updateBarChart(this.apexPlanVsActualProjectChart, resultProject.data, 'Project Chart');
                        } else {
                            console.error('Failed to fetch data for (Project) chart:', resultProject.message);
                            this.apexPlanVsActualProjectChart?.updateOptions(errorOptionsProject);
                        }
                    } else {
                        console.error('Network error for (Project) chart:', responseProject.statusText);
                        const networkErrorOptions = {
                            ...errorOptionsProject,
                            noData: {
                                ...errorOptionsProject.noData,
                                text: 'Network error.'
                            }
                        };
                        this.apexPlanVsActualProjectChart?.updateOptions(networkErrorOptions);
                    }

                } catch (error) {
                    console.error('Error applying filters:', error);
                    const errorOptions = {
                        noData: {
                            text: 'An error occurred.',
                            style: {
                                color: '#EF4444',
                                fontSize: '16px'
                            }
                        }
                    };
                    this.apexPlanVsActualChart?.updateOptions(errorOptions);
                    this.apexPlanVsActualProjectChart?.updateOptions(errorOptions);
                } finally {
                    this.isLoading = false;
                }
            },

            resetFilters() {
                document.getElementById('key_word').value = '';
                $('#doc_group').val('ALL').trigger('change');
                $('#sub_type').val('ALL').trigger('change');
                $('#customer').val('ALL').trigger('change');
                $('#model').val('ALL').trigger('change');
                $('#project_status').val('ALL').trigger('change');
                $('#part_group').val('ALL').trigger('change');
                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                document.getElementById('month_input').value = `${year}-${month}`;
                document.getElementById('sort_actual').checked = true;
                
                this.applyFilters();
            },

            updateBarChart(chart, apiData, chartName = 'Chart') {
                if (!chart) return;

                const isDarkMode = this.isDarkMode;
                const labelColor = isDarkMode ? '#E2E8F0' : '#4A5568';

                if (!apiData || !Array.isArray(apiData) || apiData.length === 0) {
                    console.warn(`No valid data received for ${chartName}`);
                    chart.updateOptions({
                        series: [{
                            name: 'Plan',
                            data: []
                        },
                        {
                            name: 'Actual',
                            data: []
                        },
                        {
                            name: 'Percentace',
                            data: []
                        }
                        ],
                        xaxis: {
                            categories: []
                        },
                        noData: {
                            text: 'No data found for this view.',
                            style: {
                                color: labelColor,
                                fontSize: '16px'
                            }
                        }
                    });
                    return;
                }

                const categories = apiData.map(item => `${item.customer_name || '?'} - ${item.model_name || '?'}`);
                const actualSeriesData = apiData.map(item => parseInt(item.actual_count) || 0);
                const planSeriesData = apiData.map(item => parseInt(item.plan_count) || 0);
                const percentaceData = apiData.map(item => parseFloat(item.percentage).toFixed(2) || 0);

                chart.updateOptions({
                    series: [{
                        name: 'Plan',
                        data: planSeriesData
                    },
                    {
                        name: 'Actual',
                        data: actualSeriesData
                    },
                    {
                        name: 'Percentace',
                        data: percentaceData
                    }
                    ],
                    xaxis: {
                        categories: categories
                    },
                    noData: {
                        text: 'Loading chart data...',
                        style: {
                            color: labelColor,
                            fontSize: '16px'
                        }
                    }
                });
            },

            initDocGroupSelect2() {
                const component = this;
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
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < (data.total_count || 0)
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
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < (data.total_count || 0)
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
                const component = this;
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
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < (data.total_count || 0)
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
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < (data.total_count || 0)
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
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < (data.total_count || 0)
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
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) {
                                results.unshift({
                                    id: 'ALL',
                                    text: 'ALL'
                                });
                            }
                            return {
                                results: results,
                                pagination: {
                                    more: (params.page * 10) < (data.total_count || 0)
                                }
                            };
                        },
                        cache: true
                    }
                });
            },
        }
    }

    // Inisialisasi Alpine.js
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardController', dashboardController);
    });
</script>
@endsection