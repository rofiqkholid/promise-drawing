@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')
@section('content')

<style>
    .select2-container--default .select2-selection--single .select2-selection__rendered:empty {
        display: none;
    }

    .filter-pill-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: #E0E7FF;
        border-radius: 9999px;
        padding: 4px 10px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #3730A3;
    }

    .filter-pill-remove {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        margin-left: 2px;
        color: #4338CA;
        line-height: 1;
    }

    .filter-pill-remove:hover {
        color: #C7D2FE;
    }

    .dark .filter-pill {
        background-color: #3730A3;
        color: #E0E7FF;
    }

    .dark .filter-pill-remove {
        color: #A5B4FC;
    }

    .dark .filter-pill-remove:hover {
        color: #E0E7FF;
    }

    .litepicker.dark {
        background-color: #1F2937 !important;
        border: 1px solid #374151 !important;
        color: #D1D5DB !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border-radius: 0.5rem;
    }

    .litepicker.dark .litepicker__header {
        background-color: #374151 !important;
        border-bottom: 1px solid #4B5563 !important;
        color: #F3F4F6 !important;
    }

    .litepicker.dark .litepicker__month-title,
    .litepicker.dark .litepicker__year-title {
        color: #F3F4F6 !important;
        font-weight: 600;
    }

    .litepicker.dark .litepicker__month-title:hover,
    .litepicker.dark .litepicker__year-title:hover {
        color: #60A5FA !important;
    }

    .litepicker.dark .litepicker__next-button,
    .litepicker.dark .litepicker__prev-button {
        background-color: transparent !important;
        border: none !important;
    }

    .litepicker.dark .litepicker__next-button:hover,
    .litepicker.dark .litepicker__prev-button:hover {
        background-color: #4B5563 !important;
        border-radius: 50%;
    }

    .litepicker.dark .litepicker__next-button svg,
    .litepicker.dark .litepicker__prev-button svg {
        stroke: #D1D5DB !important;
    }

    .litepicker.dark .litepicker__day-of-week {
        color: #9CA3AF !important;
        font-weight: 500;
    }

    .litepicker.dark .litepicker__day {
        color: #D1D5DB !important;
        background-color: transparent !important;
    }

    .litepicker.dark .litepicker__day:hover {
        background-color: #374151 !important;
        border-radius: 50%;
    }

    .litepicker.dark .litepicker__day.is-today {
        color: #3B82F6 !important;
        font-weight: 600 !important;
    }

    .litepicker.dark .litepicker__day.is-selected {
        background-color: #2563EB !important;
        color: #FFFFFF !important;
        border-radius: 50%;
    }

    .litepicker.dark .litepicker__day.is-in-range {
        background-color: #1E40AF !important;
        color: #FFFFFF !important;
    }

    .litepicker.dark .litepicker__day.is-start-date,
    .litepicker.dark .litepicker__day.is-end-date {
        background-color: #2563EB !important;
        color: #FFFFFF !important;
        border-radius: 50%;
    }

    .litepicker.dark .litepicker__footer {
        background-color: #374151 !important;
        border-top: 1px solid #4B5563 !important;
    }

    .litepicker.dark .litepicker__tooltip {
        background-color: #111827 !important;
        border: 1px solid #374151 !important;
        color: #D1D5DB !important;
    }

    .litepicker.dark .litepicker__button-cancel {
        background-color: #6B7280 !important;
        color: #FFFFFF !important;
    }

    .litepicker.dark .litepicker__button-apply {
        background-color: #2563EB !important;
        color: #FFFFFF !important;
    }

    .dark #date_range_input {
        background-color: #374151 !important;
        border-color: #4B5563 !important;
        color: #F9FAFB !important;
    }

    .dark #date_range_input::placeholder {
        color: #9CA3AF !important;
    }
</style>

<div x-data="dashboardController()" x-init="init()">

    <div classid="card-container" class="w-full grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">

        <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-blue-100 dark:bg-blue-900/50 text-blue-500 dark:text-blue-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                    <i class="fa-solid fa-file-lines fa-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Total Files</h3>
                    <p id="docCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
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
                    <p id="uploadCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
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
                    <p id="downloadCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
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

        <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-purple-100 dark:bg-purple-900/50 text-purple-500 dark:text-purple-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                    <i class="fa-solid fa-hard-drive fa-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Server Storage</h3>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        <span id="freeSpace">...</span> free
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span id="usedSpace">...</span> / <span id="totalSpace">...</span>
                    </p>
                </div>
            </div>
            <div class="absolute bottom-0 right-0">
                <svg class="w-28 text-purple-500/20 dark:text-purple-400/10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 100 30" stroke-width="2" stroke="currentColor">
                    <path d="M0 15 L20 10 L40 18 L60 15 L80 20 L100 10" />
                </svg>
            </div>
        </div>
    </div>


    <div class="mt-6 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
            <i class="fa-solid fa-filter mr-2 text-gray-500"></i> Filter Data
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-start">

            <div>
                <label for="date_range_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range (Upload)</label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-calendar-days text-gray-400"></i>
                    </div>
                    <input type="text" id="date_range_input" name="date_range_input" class="block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-500 focus:ring-0 focus:outline-none sm:text-sm py-2 pl-10 pr-3" placeholder="Select date range...">
                </div>
            </div>

            <div>
                <label for="customer_model_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer - Model</label>
                <div class="relative mt-1">
                    <select id="customer_model_input" name="customer_model_input" class="w-full"></select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>

                <div x-show="partGroupMode === 'multi'">
                    <div class="relative mt-1">
                        <select id="part_group_multi_input" name="part_group_multi_input" class="w-full"></select>
                    </div>
                </div>

                <div x-show="partGroupMode === 'single'" style="display: none;">
                    <div class="relative mt-1">
                        <select id="part_group_single_input" name="part_group_single_input" class="w-full"></select>
                    </div>
                </div>
            </div>

            <div>
                <label for="project_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <div class="relative mt-1">
                    <select id="project_status" name="project_status" class="w-full"></select>
                </div>
            </div>

        </div>
        <div class="w-full flex justify-between items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">


            <div class="filter-pill-container flex-grow pr-6">

                <template x-for="item in selectedProjects" :key="'proj-' + item.model_id">
                    <span class="filter-pill">
                        <span x-text="item.text"></span>
                        <button @click="removeProject(item.model_id)" type="button" class="filter-pill-remove">
                            <i class="fa-solid fa-times fa-xs"></i>
                        </button>
                    </span>
                </template>


                <template x-for="item in selectedPartGroup" :key="'pg-' + item.id">
                    <span class="filter-pill">
                        <span class="font-normal text-gray-500 dark:text-gray-400 mr-1">Part Group:</span>
                        <span x-text="item.text"></span>
                        <button @click="removePartGroup(item.id)" type="button" class="filter-pill-remove">
                            <i class="fa-solid fa-times fa-xs"></i>
                        </button>
                    </span>
                </template>


                <template x-if="partGroupMode === 'single' && partGroupSingleValue.id !== 'ALL'">
                    <span class="filter-pill">
                        <span class="font-normal text-gray-500 dark:text-gray-400 mr-1">Part Group:</span>

                        <span x-text="partGroupSingleValue.text"></span>
                        <button @click="resetPartGroupSingle" type="button" class="filter-pill-remove">
                            <i class="fa-solid fa-times fa-xs"></i>
                        </button>
                    </span>
                </template>
            </div>


            <div class="flex-shrink-0 flex space-x-3">
                <button type="button" @click="resetFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"> Reset </button>
                <button type="button" @click="applyFilters" :disabled="isLoading" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed min-w-[150px]">
                    <span x-show="!isLoading"> <i class="fa-solid fa-check mr-2"></i> Apply Filter </span>
                    <span x-show="isLoading" style="display: none;">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>


    <div class="mt-6 grid grid-cols-1 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-chart-column mr-2 text-blue-500"></i>Upload File Monitoring (ALL)</h3>
            <div class="h-96">
                <div id="planVsActualChart"></div>
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
        fetchUploadCount();
        fetchDocCount();
        fetchUDownloadCount();
        fetchDiskSpace();
    });

    function fetchActiveUsers() {
        const apiUrl = '/api/active-users-count';
        const userCountElement = document.getElementById('activeUserCount');
        if (!userCountElement) return;
        userCountElement.textContent = '...';
        fetch(apiUrl).then(response => response.json()).then(data => {
            if (data && data.status === 'success') userCountElement.textContent = data.count;
        }).catch(error => userCountElement.textContent = 'Error');
    }

    function fetchUploadCount() {
        const apiUrl = '/api/upload-count';
        const userCountElement = document.getElementById('uploadCount');
        if (!userCountElement) return;
        userCountElement.textContent = '...';
        fetch(apiUrl).then(response => response.json()).then(data => {
            if (data && data.status === 'success') userCountElement.textContent = data.count;
        }).catch(error => userCountElement.textContent = 'Error');
    }

    function fetchUDownloadCount() {
        const apiUrl = '/api/download-count';
        const userCountElement = document.getElementById('downloadCount');
        if (!userCountElement) return;
        userCountElement.textContent = '...';
        fetch(apiUrl).then(response => response.json()).then(data => {
            if (data && data.status === 'success') userCountElement.textContent = data.count;
        }).catch(error => userCountElement.textContent = 'Error');
    }

    function fetchDocCount() {
        const apiUrl = '/api/doc-count';
        const userCountElement = document.getElementById('docCount');
        if (!userCountElement) return;
        userCountElement.textContent = '...';
        fetch(apiUrl).then(response => response.json()).then(data => {
            if (data && data.status === 'success') userCountElement.textContent = data.count;
        }).catch(error => userCountElement.textContent = 'Error');
    }

    function fetchDiskSpace() {
        const apiUrl = '/api/disk-space';
        const freeEl = document.getElementById('freeSpace');
        const usedEl = document.getElementById('usedSpace');
        const totalEl = document.getElementById('totalSpace');
        if (!freeEl || !usedEl || !totalEl) return;
        freeEl.textContent = '...';
        usedEl.textContent = '...';
        totalEl.textContent = '...';
        fetch(apiUrl).then(response => response.json()).then(data => {
            if (data && data.status === 'success') {
                freeEl.textContent = data.free;
                usedEl.textContent = data.used;
                totalEl.textContent = data.total;
            } else {
                throw new Error('Invalid data');
            }
        }).catch(error => {
            freeEl.textContent = 'Error';
            usedEl.textContent = '-';
            totalEl.textContent = '-';
        });
    }


    function dashboardController() {
        return {
            apexPlanVsActualChart: null,
            apexUploadDownloadChart: null,
            isLoading: false,
            isDarkMode: document.documentElement.classList.contains('dark'),

            dateRangeInstance: null,
            dateStart: '',
            dateEnd: '',

            selectedProjects: [],
            selectedPartGroup: [],
            partGroupMode: 'multi',
            partGroupSingleValue: {
                id: 'ALL',
                text: 'ALL'
            },

            init() {
                if (typeof ApexCharts === 'undefined' || typeof $ === 'undefined' || !$.fn.select2 || typeof Litepicker === 'undefined') {
                    console.log('Waiting for libraries (ApexCharts, jQuery, Select2, Litepicker)...');
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
                        component.updateLitepickerTheme();
                    }
                };
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.attributeName === 'class') updateTheme();
                    });
                });
                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });
                window.addEventListener('theme-changed', updateTheme);
            },

            updateLitepickerTheme() {
                if (this.dateRangeInstance && this.dateRangeInstance.ui) {
                    const isDark = this.isDarkMode;
                    if (isDark) {
                        this.dateRangeInstance.ui.classList.add('dark');
                    } else {
                        this.dateRangeInstance.ui.classList.remove('dark');
                    }
                }
            },

            initializeDashboard() {
                const component = this;

                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                const lastDay = new Date(year, now.getMonth() + 1, 0).getDate().toString().padStart(2, '0');

                component.dateStart = `${year}-${month}-01`;
                component.dateEnd = `${year}-${month}-${lastDay}`;

                component.initCharts();
                component.initLitepicker();
                component.initCustomerModelSelect2();
                component.initStatusSelect2();
                component.initPartGroupSelect2_Multi();
                component.initPartGroupSelect2_Single();

                component.fetchActivityLog();

                component.applyFilters();
                component.updateUploadDownloadChart(year);
            },

            updateChartTheme() {
                const newMode = this.isDarkMode ? 'dark' : 'light';
                const gridColor = newMode === 'dark' ? '#4A5568' : '#E2E8F0';
                const labelColor = newMode === 'dark' ? '#E2E8F0' : '#4A5568';
                const background = newMode === 'dark' ? '#1F2937' : '#FFFFFF';
                const mainCharts = [this.apexPlanVsActualChart, this.apexUploadDownloadChart];
                mainCharts.forEach(chart => {
                    if (chart && chart.w && chart.w.config) {
                        const currentCategories = chart.w.globals.categoryLabels || [];
                        const currentSeries = JSON.parse(JSON.stringify(chart.w.config.series || []));
                        const updatedOptions = {
                            theme: {
                                mode: newMode
                            },
                            chart: {
                                foreColor: labelColor,
                                background: background,
                                animations: {
                                    enabled: true,
                                    easing: 'easeinout',
                                    speed: 800,
                                    animateGradually: {
                                        enabled: true,
                                        delay: 150
                                    },
                                    dynamicAnimation: {
                                        enabled: true,
                                        speed: 350
                                    }
                                }
                            },
                            xaxis: {
                                categories: currentCategories,
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
                            tooltip: {
                                theme: newMode
                            },
                            series: currentSeries
                        };
                        chart.updateOptions(updatedOptions, true, true);
                    }
                });
            },

            initCharts() {
                const charts = [this.apexPlanVsActualChart, this.apexUploadDownloadChart];
                charts.forEach(chart => {
                    if (chart) chart.destroy();
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
                    }, {
                        name: 'Actual',
                        type: 'bar',
                        data: []
                    }, {
                        name: 'Percentace',
                        type: 'line',
                        data: []
                    }],
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
                        formatter: (val) => val.toFixed(1) + '%',
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
                            formatter: (val) => val
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
                this.apexPlanVsActualChart = new ApexCharts(document.querySelector("#planVsActualChart"), {
                    ...mainChartOptions,
                    colors: ['#0063d5ff', '#3ea70dff', '#bd8e00ff']
                });
                this.apexPlanVsActualChart.render();
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
                    if (!response.ok) throw new Error('Network response was not ok');
                    const result = await response.json();
                    if (result.status === 'success') {
                        container.innerHTML = '';
                        if (result.data && result.data.length > 0) {
                            result.data.forEach(log => container.insertAdjacentHTML('beforeend', this.formatLogEntry(log)));
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
                    const detailsArray = [log.meta.customer_code, log.meta.model_name, log.meta.part_no, log.meta.doctype_group, log.meta.part_group_code, log.meta.doctype_subcategory, log.meta.ecn_no].filter(Boolean);
                    if (detailsArray.length > 0) metaDetails = `<p class="mt-2 text-sm text-gray-600 dark:text-gray-400 font-mono">${detailsArray.join(' - ')}</p>`;
                }
                return `<div class="py-3 px-2 flex space-x-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <div class="flex-shrink-0 pt-1"><i class="fa-solid ${logInfo.icon} fa-lg ${logInfo.color} w-5 text-center"></i></div>
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
                this.apexPlanVsActualChart?.updateOptions({
                    noData: {
                        text: 'Loading data...',
                        style: {
                            color: labelColor,
                            fontSize: '16px'
                        }
                    }
                });

                try {
                    // --- TAMBAHAN UNTUK MENGAMBIL TEKS STATUS ---
                    const statusData = $('#project_status').select2('data');
                    let projectStatusText = '';
                    if (statusData && statusData.length > 0) {
                        projectStatusText = statusData[0].text;
                    }
                    // --- END TAMBAHAN ---

                    const filters = {
                        date_start: this.dateStart,
                        date_end: this.dateEnd,

                        // --- PERUBAHAN DI SINI ---
                        project_status: projectStatusText,

                        model: this.selectedProjects.map(item => item.text),

                        part_group: this.partGroupMode === 'multi' ?
                            this.selectedPartGroup.map(item => item.text) : (this.partGroupSingleValue.id === 'ALL' ? [] : [this.partGroupSingleValue.text])
                    };

                    const params = new URLSearchParams();
                    for (const key in filters) {
                        const value = filters[key];
                        if (value) {
                            if (Array.isArray(value)) {
                                if (value.length > 0) {
                                    value.forEach(val => params.append(`${key}[]`, val));
                                }
                            } else if (value !== 'ALL') {
                                params.append(key, value);
                            }
                        }
                    }
                    const paramsString = params.toString();

                    const urlAll = `{{ route('api.upload-monitoring-data') }}?${paramsString}`;
                    const responseAll = await fetch(urlAll);

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
                            this.apexPlanVsActualChart?.updateOptions(errorOptionsAll);
                        }
                    } else {
                        this.apexPlanVsActualChart?.updateOptions({
                            ...errorOptionsAll,
                            noData: {
                                ...errorOptionsAll.noData,
                                text: 'Network error.'
                            }
                        });
                    }

                } catch (error) {
                    console.error('Error applying filters:', error);
                    this.apexPlanVsActualChart?.updateOptions({
                        noData: {
                            text: 'An error occurred.',
                            style: {
                                color: '#EF4444',
                                fontSize: '16px'
                            }
                        }
                    });
                } finally {
                    this.isLoading = false;
                }
            },

            resetFilters() {
                this.selectedProjects = [];
                this.selectedPartGroup = [];

                $('#project_status').val('ALL').trigger('change');

                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                const lastDay = new Date(year, now.getMonth() + 1, 0).getDate().toString().padStart(2, '0');
                const firstDayOfMonth = `${year}-${month}-01`;
                const lastDayOfMonth = `${year}-${month}-${lastDay}`;

                this.dateStart = firstDayOfMonth;
                this.dateEnd = lastDayOfMonth;

                if (this.dateRangeInstance) {
                    this.dateRangeInstance.destroy();
                }

                setTimeout(() => {
                    this.initLitepicker();
                }, 50);

                this.partGroupMode = 'multi';
                this.partGroupSingleValue = {
                    id: 'ALL',
                    text: 'ALL'
                };

                $('#part_group_single_input').select2('destroy');
                this.initPartGroupSelect2_Single();
                $('#part_group_single_input').val('ALL').trigger('change');

                this.applyFilters();
            },

            updateBarChart(chart, apiData, chartName = 'Chart') {
                if (!chart) return;
                const isDarkMode = this.isDarkMode;
                const labelColor = isDarkMode ? '#E2E8F0' : '#4A5568';
                if (!apiData || !Array.isArray(apiData) || apiData.length === 0) {
                    chart.updateOptions({
                        series: [{
                            name: 'Plan',
                            data: []
                        }, {
                            name: 'Actual',
                            data: []
                        }, {
                            name: 'Percentace',
                            data: []
                        }],
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
                    }, {
                        name: 'Actual',
                        data: actualSeriesData
                    }, {
                        name: 'Percentace',
                        data: percentaceData
                    }],
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

            removeProject(modelId) {
                this.selectedProjects = this.selectedProjects.filter(p => p.model_id !== modelId);
                this.updatePartGroupMode();
            },
            removePartGroup(id) {
                this.selectedPartGroup = this.selectedPartGroup.filter(p => p.id !== id);
            },

            resetPartGroupSingle() {
                this.partGroupSingleValue = {
                    id: 'ALL',
                    text: 'ALL'
                };
                $('#part_group_single_input').val('ALL').trigger('change');
            },

            updatePartGroupMode() {
                const component = this;
                let projectCount = component.selectedProjects.length;

                let newMode = (projectCount > 1) ? 'single' : 'multi';

                if (component.partGroupMode !== newMode) {
                    component.partGroupMode = newMode;
                    if (newMode === 'single') {
                        component.selectedPartGroup = [];
                        component.partGroupSingleValue = {
                            id: 'ALL',
                            text: 'ALL'
                        };
                        $('#part_group_single_input').val('ALL').trigger('change');
                    } else {
                        component.partGroupSingleValue = {
                            id: 'ALL',
                            text: 'ALL'
                        };
                        $('#part_group_single_input').val('ALL').trigger('change');
                    }
                }
            },

            initLitepicker() {
                const component = this;

                if (component.dateRangeInstance) {
                    component.dateRangeInstance.destroy();
                }

                component.dateRangeInstance = new Litepicker({
                    element: document.getElementById('date_range_input'),
                    singleMode: false,
                    allowRepick: true,
                    format: 'DD MMM YYYY',
                    startDate: new Date(component.dateStart),
                    endDate: new Date(component.dateEnd),
                    setup: (picker) => {
                        picker.on('selected', (date1, date2) => {
                            component.dateStart = date1 ? date1.format('YYYY-MM-DD') : '';
                            component.dateEnd = date2 ? date2.format('YYYY-MM-DD') : '';
                        });

                        picker.on('show', () => {
                            const isDark = document.documentElement.classList.contains('dark');
                            if (isDark) {
                                picker.ui.classList.add('dark');
                            } else {
                                picker.ui.classList.remove('dark');
                            }
                        });
                    }
                });

                setTimeout(() => {
                    const isDark = document.documentElement.classList.contains('dark');
                    if (component.dateRangeInstance && component.dateRangeInstance.ui) {
                        if (isDark) {
                            component.dateRangeInstance.ui.classList.add('dark');
                        } else {
                            component.dateRangeInstance.ui.classList.remove('dark');
                        }
                    }
                }, 100);
            },

            initCustomerModelSelect2() {
                const component = this;
                $('#customer_model_input').select2({
                    dropdownParent: $('#customer_model_input').parent(),
                    width: '100%',
                    placeholder: 'Tambah Customer-Model...',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('dashboard.getCustomerModel') }}",
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
                                results: data.results || [],
                                pagination: {
                                    more: data.pagination && data.pagination.more
                                }
                            };
                        },
                        cache: true
                    }
                }).on('change', function(e) {
                    let data = $(this).select2('data')[0];
                    if (data && data.model_id) {
                        if (!component.selectedProjects.find(p => p.model_id === data.model_id)) {
                            component.selectedProjects.push({
                                model_id: data.model_id,
                                text: data.text,
                                customer_id: data.customer_id
                            });
                            component.updatePartGroupMode();
                        }
                    }
                    $(this).val(null).trigger('change.select2');
                });
            },

            initPartGroupSelect2_Multi() {
                const component = this;
                $('#part_group_multi_input').select2({
                    dropdownParent: $('#part_group_multi_input').parent(),
                    width: '100%',
                    placeholder: 'Tambah Part Group...',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('dashboard.getPartGroup') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({
                            _token: "{{ csrf_token() }}",
                            q: params.term,
                            page: params.page || 1
                        }),
                        processResults: (data, params) => ({
                            results: data.results || [],
                            pagination: {
                                more: (params.page || 1) * 10 < (data.total_count || 0)
                            }
                        }),
                        cache: true
                    }
                }).on('change', function(e) {
                    let data = $(this).select2('data')[0];
                    if (data && data.id) {
                        if (!component.selectedPartGroup.find(p => p.id === data.id)) {
                            component.selectedPartGroup.push({
                                id: data.id,
                                text: data.text
                            });
                        }
                    }
                    $(this).val(null).trigger('change.select2');
                });
            },

            initPartGroupSelect2_Single() {
                const component = this;
                $('#part_group_single_input').select2({
                    dropdownParent: $('#part_group_single_input').parent(),
                    width: '100%',
                    placeholder: 'Pilih 1 Part Group',
                    ajax: {
                        url: "{{ route('dashboard.getPartGroup') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({
                            _token: "{{ csrf_token() }}",
                            q: params.term,
                            page: params.page || 1
                        }),
                        processResults: (data, params) => {
                            params.page = params.page || 1;
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) results.unshift({
                                id: 'ALL',
                                text: 'ALL'
                            });
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
                    let data = $(this).select2('data')[0];
                    if (data) {
                        component.partGroupSingleValue = {
                            id: data.id,
                            text: data.text
                        };
                    } else {
                        component.partGroupSingleValue = {
                            id: 'ALL',
                            text: 'ALL'
                        };
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
                        data: (params) => ({
                            _token: "{{ csrf_token() }}",
                            q: params.term,
                            page: params.page || 1
                        }),
                        processResults: (data, params) => {
                            params.page = params.page || 1;
                            let results = data.results || [];
                            if (params.page === 1 && !params.term) results.unshift({
                                id: 'ALL',
                                text: 'ALL'
                            });
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