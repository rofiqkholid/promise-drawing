@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')
@section('content')

<div x-data="dashboardController()" x-init="init()">

    {{-- STAT CARDS --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-[15%]">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Monitoring</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analys File Management</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">

            <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center">
                    <div class="bg-blue-100 dark:bg-blue-900/50 text-blue-500 dark:text-blue-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                        <i class="fa-solid fa-file-lines fa-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Total Document</h3>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">1024</p>
                    </div>
                </div>
                <div class="absolute bottom-0 right-0">
                    <svg class="w-28 text-blue-500/20 dark:text-blue-400/10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 100 30" stroke-width="2" stroke="currentColor">
                        <path d="M0 25 L20 15 L40 20 L60 10 L80 15 L100 5" />
                    </svg>
                </div>
            </div>

            <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center">
                    <div class="bg-green-100 dark:bg-green-900/50 text-green-500 dark:text-green-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                        <i class="fa-solid fa-cloud-arrow-up fa-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Upload</h3>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">512</p>
                    </div>
                </div>
                <div class="absolute bottom-0 right-0">
                    <svg class="w-28 text-green-500/20 dark:text-green-400/10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 100 30" stroke-width="2" stroke="currentColor">
                        <path d="M0 10 L20 20 L40 15 L60 25 L80 10 L100 15" />
                    </svg>
                </div>
            </div>

            <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center">
                    <div class="bg-yellow-100 dark:bg-yellow-900/50 text-yellow-500 dark:text-yellow-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                        <i class="fa-solid fa-cloud-arrow-down fa-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Download</h3>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">403</p>
                    </div>
                </div>
                <div class="absolute bottom-0 right-0">
                    <svg class="w-28 text-yellow-500/20 dark:text-yellow-400/10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 100 30" stroke-width="2" stroke="currentColor">
                        <path d="M0 15 L20 25 L40 10 L60 20 L80 5 L100 20" />
                    </svg>
                </div>
            </div>

            <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center">
                    <div class="bg-red-100 dark:bg-red-900/50 text-red-500 dark:text-red-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                        <i class="fa-solid fa-users fa-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">User Active</h3>
                        <p id="activeUserCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
                    </div>
                </div>
                <div class="absolute bottom-0 right-0">
                    <svg class="w-28 text-red-500/20 dark:text-red-400/10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 100 30" stroke-width="2" stroke="currentColor">
                        <path d="M0 20 L20 18 L40 22 L60 20 L80 17 L100 15" />
                    </svg>
                </div>
            </div>

        </div>
    </div>

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
            <div class="lg:col-span-4 w-full flex justify-end items-center pt-4 border-t border-gray-200 dark:border-gray-700 mt-2">
                <div class="flex space-x-3">
                    <button type="button" @click="resetFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"> Reset </button>
                    <button type="button" @click="applyFilters" :disabled="isLoading" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed min-w-[150px]">
                        <span x-show="!isLoading"> <i class="fa-solid fa-check mr-2"></i> Apply Filter </span>
                        <span x-show="isLoading" style="display: none;"> <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
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
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-line mr-2 text-green-500"></i>Upload vs Download Trend</h3>
            <div class="flex-grow relative h-96">
                <div id="uploadDownloadChart"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-newspaper mr-2 text-gray-500"></i>Newsfeed / Activity Log</h3>
            <div id="activityLogContainer" class="divide-y divide-gray-200 dark:divide-gray-700 h-96 overflow-y-auto">
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchActiveUsers();
    });

    function fetchActiveUsers() {
        const apiUrl = '/api/active-users-count';
        const userCountElement = document.getElementById('activeUserCount');
        if (!userCountElement) return;
        userCountElement.textContent = '...';
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data && data.status === 'success') userCountElement.textContent = data.count;
            })
            .catch(error => {
                console.error('Error fetching active users:', error);
                userCountElement.textContent = 'Error';
            });
    }

    function dashboardController() {
        return {
            showExtraFilters: false,
            apexPlanVsActualChart: null,
            apexPlanVsActualProjectChart: null,
            apexUploadDownloadChart: null,
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
                component.fetchActivityLog();

                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                document.getElementById('month_input').value = `${year}-${month}`;

                component.applyFilters();
                component.updateUploadDownloadChart(year);
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
                    this.apexUploadDownloadChart
                ];

                mainCharts.forEach(chart => {
                    if (chart) {
                        const currentCategories = chart.w.globals.categoryLabels ||
                            chart.w.config.xaxis.categories || [];

                        chart.updateOptions(themeOptions);

                        if (currentCategories && currentCategories.length > 0) {
                            chart.updateOptions({
                                xaxis: {
                                    categories: currentCategories
                                }
                            });
                        }
                    }
                });
            },

            updateChartData() {
                fetchDataFromServer().then(data => {
                    this.apexPlanVsActualChart.updateOptions({
                        xaxis: {
                            categories: data.categories
                        }
                    });

                    this.apexPlanVsActualChart.updateSeries([{
                            name: 'Plan',
                            data: data.planData
                        },
                        {
                            name: 'Actual',
                            data: data.actualData
                        },
                        {
                            name: 'Percentage',
                            data: data.percentageData
                        }
                    ]);
                });
            },

            initCharts() {
                const charts = [
                    this.apexPlanVsActualChart,
                    this.apexPlanVsActualProjectChart,
                    this.apexUploadDownloadChart
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


                const allChartOptions = {
                    ...mainChartOptions,
                    colors: ['#0063d5ff', '#3ea70dff', '#bd8e00ff']
                };

                const projectChartOptions = {
                    ...mainChartOptions,
                    colors: ['#0063d5ff', '#3ea70dff', '#bd8e00ff']
                };

                this.apexPlanVsActualChart = new ApexCharts(document.querySelector("#planVsActualChart"), allChartOptions);
                this.apexPlanVsActualChart.render();

                this.apexPlanVsActualProjectChart = new ApexCharts(document.querySelector("#planVsActualProjectChart"), projectChartOptions);
                this.apexPlanVsActualProjectChart.render();

                const trendChartOptions = {
                    series: [],
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: false
                        },
                        foreColor: labelColor,
                        background: background
                    },

                    colors: ['#3ea70dff', '#0063d5ff'],
                    theme: {
                        mode: isDarkMode ? 'dark' : 'light'
                    },
                    dataLabels: {
                        enabled: true
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5
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
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                    tooltip: {
                        theme: isDarkMode ? 'dark' : 'light'
                    },
                    noData: {
                        text: 'Loading trend data...',
                        style: {
                            color: labelColor,
                            fontSize: '16px'
                        }
                    }
                };

                this.apexUploadDownloadChart = new ApexCharts(document.querySelector("#uploadDownloadChart"), trendChartOptions);
                this.apexUploadDownloadChart.render();
            },

            async fetchActivityLog() {
                const container = document.getElementById('activityLogContainer');
                if (!container) return;
                container.innerHTML = `<div class="p-4 text-center text-gray-500 dark:text-gray-400">Loading activities...</div>`;
                try {
                    const response = await fetch("{{ route('api.getDataActivityLog') }}");
                    if (!response.ok) throw new Error(`Network response was not ok`);
                    const result = await response.json();
                    if (result.status === 'success') {
                        container.innerHTML = '';
                        if (result.data && result.data.length > 0) {
                            result.data.forEach(log => {
                                const logHtml = this.formatLogEntry(log);
                                container.insertAdjacentHTML('beforeend', logHtml);
                            });
                        } else {
                            container.innerHTML = `<div class="p-4 text-center text-gray-500 dark:text-gray-400">No recent activity found.</div>`;
                        }
                    } else {
                        throw new Error(result.message || 'Failed to fetch data.');
                    }
                } catch (error) {
                    console.error('Error fetching activity log:', error);
                    container.innerHTML = `<div class="p-4 text-center text-red-500">Error loading activities.</div>`;
                }
            },

            formatLogEntry(log) {
                if (!log) return '';
                const iconMap = {
                    'UPLOAD': {
                        icon: 'fa-cloud-arrow-up',
                        color: 'text-green-500'
                    },
                    'DEFAULT': {
                        icon: 'fa-circle-info',
                        color: 'text-gray-500'
                    }
                };
                const logInfo = iconMap[log.activity_code] || iconMap['DEFAULT'];
                let message = '';
                const userName = `<strong>${log.user_name || 'System'}</strong>`;
                switch (log.activity_code) {
                    case 'UPLOAD':
                        message = `${userName} uploaded a new document.`;
                        break;
                    default:
                        message = `${userName} performed action: <strong>${log.activity_code}</strong>.`;
                }
                const date = log.created_at ? new Date(log.created_at.replace(' ', 'T')) : new Date();
                const fullTimestamp = date.toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }).replace(/\./g, ':');
                const relativeTime = this.formatTimeAgo(log.created_at);
                let metaDetails = '';
                if (log.meta && typeof log.meta === 'object' && log.activity_code === 'UPLOAD') {
                    const detailsArray = [log.meta.customer_code, log.meta.model_name, log.meta.part_no, log.meta.doctype_group, log.meta.doctype_subcategory].filter(Boolean);
                    if (detailsArray.length > 0) {
                        metaDetails = `<p class="mt-2 text-sm text-gray-600 dark:text-gray-400 font-mono">${detailsArray.join(' - ')}</p>`;
                    }
                }
                return `
                        <div class="py-3 px-2 flex space-x-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <div class="flex-shrink-0 pt-1"> <i class="fa-solid ${logInfo.icon} fa-lg ${logInfo.color} w-5 text-center"></i> </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm text-gray-800 dark:text-gray-200">${message}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-3 whitespace-nowrap">${fullTimestamp}</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${relativeTime}</p>
                                ${metaDetails}
                            </div>
                        </div>`;
            },

            formatTimeAgo(dateString) {
                if (!dateString) return "recently";
                const date = new Date(dateString.replace(' ', 'T'));
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                if (seconds < 5) return "just now";
                let interval = seconds / 31536000;
                if (interval > 1) return Math.floor(interval) + " years ago";
                interval = seconds / 2592000;
                if (interval > 1) return Math.floor(interval) + " months ago";
                interval = seconds / 86400;
                if (interval > 1) return Math.floor(interval) + " days ago";
                interval = seconds / 3600;
                if (interval > 1) return Math.floor(interval) + " hours ago";
                interval = seconds / 60;
                if (interval > 1) return Math.floor(interval) + " minutes ago";
                return Math.max(0, Math.floor(seconds)) + " seconds ago";
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

                    const urlAll = `{{ route('api.upload-monitoring-data') }}?${paramsString}`;
                    const urlProject = `{{ route('api.upload-monitoring-data-project') }}?${paramsString}`;

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
                            text: '',
                        }
                    });
                    return;
                }

                const categories = apiData.map(item => `${item.customer_name || '?'} - ${item.model_name || '?'} - ${item.part_group || '?'}`);
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

            async updateUploadDownloadChart(year) {
                if (!this.apexUploadDownloadChart || !year) return;
                const apiUrl = `{{ route('api.getDataLog') }}?year=${year}`;
                try {
                    const response = await fetch(apiUrl);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const result = await response.json();
                    let uploadData = Array(12).fill(0);
                    let downloadData = Array(12).fill(0);
                    const categories = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    if (result.status === 'success' && result.data) {
                        result.data.forEach(item => {
                            const index = parseInt(item.month) - 1;
                            if (index >= 0 && index < 12) {
                                uploadData[index] = parseInt(item.upload_count) || 0;
                                downloadData[index] = parseInt(item.download_count) || 0;
                            }
                        });
                    }
                    this.apexUploadDownloadChart.updateOptions({
                        series: [{
                            name: 'Download',
                            data: downloadData
                        }, {
                            name: 'Upload',
                            data: uploadData
                        }],
                        xaxis: {
                            categories: categories
                        }
                    });
                } catch (error) {
                    console.error('Error fetching/rendering upload/download chart:', error);
                    this.apexUploadDownloadChart.updateOptions({
                        series: [{
                            name: 'Download',
                            data: []
                        }, {
                            name: 'Upload',
                            data: []
                        }],
                        xaxis: {
                            categories: []
                        }
                    });
                }
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

    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardController', dashboardController);
    });
</script>
@endsection