@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')

@section('content')

<div id="dashboardWrapper" class="flex flex-col gap-2 h-[calc(100vh-70px)] w-full overflow-hidden">

    <div class="flex-none grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-2">
        <div class="relative bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-blue-100 dark:bg-blue-900/50 text-blue-500 dark:text-blue-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                    <i class="fa-solid fa-file-lines fa-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Total Files</h3>
                    <p id="docCount" class="text-xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-green-100 dark:bg-green-900/50 text-green-500 dark:text-green-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                    <i class="fa-solid fa-cloud-arrow-up fa-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Upload</h3>
                    <p id="uploadCount" class="text-xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-yellow-100 dark:bg-yellow-900/50 text-yellow-500 dark:text-yellow-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                    <i class="fa-solid fa-cloud-arrow-down fa-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Download</h3>
                    <p id="downloadCount" class="text-xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-red-100 dark:bg-red-900/50 text-red-500 dark:text-red-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                    <i class="fa-solid fa-users fa-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">User Active</h3>
                    <p id="activeUserCount" class="text-xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-purple-100 dark:bg-purple-900/50 text-purple-500 dark:text-purple-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0">
                    <i class="fa-solid fa-hard-drive fa-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Server Storage</h3>
                    <p class="text-md font-bold text-gray-800 dark:text-gray-100">
                        <span id="usedSpace">...</span> / <span id="totalSpace">...</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="filterCard" style="display: none;" class="flex-none bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2 flex items-center">
            <i class="fa-solid fa-filter mr-2 text-gray-500"></i> Filter Data
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-start">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-0.5">Date Range</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-calendar-days text-gray-400"></i>
                    </div>
                    <input type="text" id="date_range_input" class="block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-0 focus:outline-none sm:text-sm py-2 pl-10 pr-3">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-0.5">Customer</label>
                <div class="relative">
                    <select id="customer_input" class="w-full"></select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-0.5">Model</label>
                <div class="relative">
                    <select id="model_input" class="w-full"></select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-0.5">Part Group</label>
                <div class="relative">
                    <select id="part_group_multi_input" class="w-full"></select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-0.5">Status</label>
                <div class="relative">
                    <select id="project_status" class="w-full"></select>
                </div>
            </div>
        </div>
        <div class="w-full flex justify-between items-center mt-3 pt-2 border-t border-gray-200 dark:border-gray-700">
            <div id="filterPillContainer" class="flex-grow pr-6"></div>
            <div class="flex space-x-3">
                <button type="button" id="btnReset" class="px-3 py-1.5 text-xs font-medium border rounded hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300">Reset</button>
                <button type="button" id="btnApply" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 min-w-[120px]">
                    <span id="btnApplyText"><i class="fa-solid fa-check mr-2"></i> Apply Filter</span>
                    <span id="btnApplyLoader" style="display:none;"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                </button>
            </div>
        </div>
    </div>

    {{-- BARIS 2: MONITORING & PHASE --}}
    <div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-2 items-stretch">
        <div class="w-full lg:w-[70%] bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="flex-none flex justify-between items-center mb-2">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                    <i class="fa-solid fa-chart-simple mr-2 text-blue-500"></i> Upload Monitoring
                </h3>
                <button type="button" id="toggleFilterBtn" class="text-gray-500 text-xs p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="Toggle Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
            <div class="relative w-full flex-1 min-h-0">
                <canvas id="monitoringChart"></canvas>
            </div>
        </div>

        <div class="w-full lg:w-[30%] bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="flex-none text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2 flex justify-between items-start">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-orange-500"></i>
                    <span>Phase Status</span>
                </div>
                <div x-data="{ tooltipVisible: false }" class="relative">
                    <i @mouseenter="tooltipVisible = true" @mouseleave="tooltipVisible = false" class="fa-solid fa-circle-info text-gray-400 dark:text-gray-500 text-md cursor-pointer mt-1"></i>
                    <div x-show="tooltipVisible"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        style="display: none;"
                        class="absolute z-30 w-64 p-3 text-xs font-normal text-white bg-gray-900 dark:bg-black rounded-lg shadow-lg top-full right-0 mt-2">
                        <div class="absolute w-3 h-3 bg-gray-900 dark:bg-black transform rotate-45 -top-1.5 right-2"></div>
                        <p class="text-gray-200 dark:text-gray-300">1. Feasibility status : Drawing for phase initial study, costing, and no LOI yet <br>
                            2. New Project : Drawing for phase Product & Tooling development <br>
                            3. Regular : Drawing for phase Mass production.</p>
                    </div>
                </div>
            </h3>
            <div class="relative w-full flex-1 min-h-0 flex justify-center items-center">
                <canvas id="phaseStatusChart"></canvas>
            </div>
        </div>
    </div>


    <div class="h-[35vh] flex-none flex flex-col lg:flex-row gap-2 items-stretch mb-2">
        <div class="w-full lg:w-[45%] bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col overflow-visible">
            <div class="flex-none flex justify-between items-center mb-2">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-trend-up text-purple-500"></i>
                    <span>Trend Upload & Download</span>
                    <div x-data="{ tooltipVisible: false }" class="relative">
                        <i @mouseenter="tooltipVisible = true" @mouseleave="tooltipVisible = false" class="fa-solid fa-circle-info text-gray-400 dark:text-gray-500 text-md cursor-pointer"></i>
                        <div x-show="tooltipVisible"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            style="display: none;"
                            class="absolute z-30 w-64 p-3 text-xs font-normal text-white bg-gray-900 dark:bg-black rounded-lg shadow-lg top-full left-0 mt-2">
                            <div class="absolute w-3 h-3 bg-gray-900 dark:bg-black transform rotate-45 -top-1.5 left-2"></div>
                            <p class="text-gray-200 dark:text-gray-300">Shows the monthly trend of document uploads and downloads for the selected year.</p>
                        </div>
                    </div>
                </h3>

                <div x-data="{
                            open: false,
                            selected: '{{ date('Y') }}',
                            select(val) {
                                this.selected = val;
                                this.open = false;
                                const input = document.getElementById('trendYearInput');
                                input.value = val;
                                input.dispatchEvent(new Event('change'));
                            }
                        }"
                    class="relative w-28 z-20">

                    <input type="hidden" id="trendYearInput" value="{{ date('Y') }}">

                    <button @click="open = !open" @click.outside="open = false" type="button"
                        class="flex items-center justify-between w-full px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none">
                        <span x-text="selected"></span>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                        style="display: none;"
                        class="absolute right-0 mt-2 w-full bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-600 py-1 z-50 max-h-48 overflow-y-auto custom-scrollbar">

                        @php
                        $currentYear = date('Y');
                        $startYear = $currentYear - 5;
                        @endphp

                        @for ($i = $currentYear; $i >= $startYear; $i--)
                        <button @click="select('{{ $i }}')" type="button"
                            class="group flex items-center justify-between w-full px-4 py-2 text-sm text-left hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-150">

                            <span class="text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 font-medium"
                                :class="selected == '{{ $i }}' ? 'text-blue-600 dark:text-blue-400 font-bold' : ''">
                                {{ $i }}
                            </span>

                            <i x-show="selected == '{{ $i }}'" class="fa-solid fa-check text-blue-600 dark:text-blue-400 text-xs"></i>
                        </button>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="relative w-full flex-1 min-h-0 z-0">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="w-full lg:w-[25%] bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col p-5 h-full font-sans">

            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2 flex justify-between items-start flex-shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-leaf text-emerald-500"></i>
                    <span>Eco Impact</span>
                </div>
                <div x-data="{ tooltipVisible: false }" class="relative">
                    <i @mouseenter="tooltipVisible = true" @mouseleave="tooltipVisible = false" class="fa-solid fa-circle-info text-gray-400 dark:text-gray-500 text-md cursor-pointer mt-1"></i>
                    <div x-show="tooltipVisible"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        style="display: none;"
                        class="absolute z-30 w-64 p-3 text-xs font-normal text-white bg-gray-900 dark:bg-black rounded-lg shadow-lg top-full right-0 mt-2">
                        <div class="absolute w-3 h-3 bg-gray-900 dark:bg-black transform rotate-45 -top-1.5 right-2"></div>
                        <p class="text-gray-200 dark:text-gray-300">This calculation shows that each digital document download saves the equivalent of 1/80,000 of a tree and reduces carbon emissions by approximately 0.000275 kg of CO₂, making a tangible contribution to environmental protection.</p>
                    </div>
                </div>
            </h3>

            <div class="flex-1 w-full flex flex-col lg:flex-row items-center justify-center gap-4">

                <div class="relative w-36 h-36 flex-shrink-0 flex items-center justify-center mx-auto lg:mx-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="10" fill="transparent"
                            class="text-emerald-50 dark:text-emerald-900/20" stroke-dasharray="264" stroke-dashoffset="0"
                            stroke-linecap="round" />
                        <circle id="ecoProgressCircle" cx="50" cy="50" r="42" stroke="currentColor" stroke-width="10" fill="transparent"
                            class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="264" stroke-dashoffset="264"
                            stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span id="ecoTreePercent" class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 leading-tight mb-1">0%<br>towards 1 Tree</span>
                        <i class="fa-solid fa-seedling text-3xl text-emerald-600 dark:text-emerald-400 my-1 filter drop-shadow-sm"></i>
                        <div class="flex flex-col leading-tight mt-1">
                            <span id="ecoTrees" class="text-sm font-bold text-gray-800 dark:text-gray-100">0</span>
                            <span class="text-[9px] text-gray-400 uppercase tracking-wide">Trees Saved</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 w-full lg:flex-1 justify-center">

                    <div class="flex items-center p-1.5 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800">
                        <div class="w-10 h-10 flex-shrink-0 rounded-lg flex items-center justify-center text-green-600 dark:text-green-300">
                            <i class="fa-solid fa-scroll text-lg"></i>
                        </div>
                        <div class="flex items-center gap-2 min-w-0 flex-1 ml-2">
                            <span id="ecoPaper" class="text-base font-bold text-gray-800 dark:text-gray-100">0</span>
                            <span class="text-xs text-green-700 dark:text-green-400 font-medium">Paper</span>
                        </div>
                    </div>

                    <div class="flex items-center p-1.5 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800">
                        <div class="w-10 h-10 flex-shrink-0 rounded-lg flex items-center justify-center text-yellow-500 dark:text-yellow-300">
                            <i class="fa-solid fa-coins text-lg"></i>
                        </div>
                        <div class="flex items-center gap-2 min-w-0 flex-1 ml-2">
                            <span class="text-base font-bold text-gray-800 dark:text-gray-100">Rp <span id="ecoCost">0</span></span>
                            <span class="text-xs text-yellow-700 dark:text-yellow-400 font-medium">Cost Save</span>
                        </div>
                    </div>

                    <div class="flex items-center p-1.5 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800">
                        <div class="w-10 h-10 flex-shrink-0 rounded-lg flex items-center justify-center text-cyan-600 dark:text-cyan-300">
                            <i class="fa-solid fa-wind text-lg"></i>
                        </div>
                        <div class="flex items-center gap-2 min-w-0 flex-1 ml-2">
                            <span id="ecoCO2" class="text-base font-bold text-gray-800 dark:text-gray-100">0 Kg</span>
                            <span class="text-xs text-cyan-700 dark:text-cyan-400 font-medium">CO2</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="w-full lg:w-[30%] h-full bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden">
            <h3 class="flex-none text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2 flex justify-between items-start">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-gray-500"></i>
                    <span>Activity Log</span>
                </div>
                <div x-data="{ tooltipVisible: false }" class="relative">
                    <i @mouseenter="tooltipVisible = true" @mouseleave="tooltipVisible = false" class="fa-solid fa-circle-info text-gray-400 dark:text-gray-500 text-md cursor-pointer mt-1"></i>
                    <div x-show="tooltipVisible"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        style="display: none;"
                        class="absolute z-30 w-64 p-3 text-xs font-normal text-white bg-gray-900 dark:bg-black rounded-lg shadow-lg top-full right-0 mt-2">
                        <div class="absolute w-3 h-3 bg-gray-900 dark:bg-black transform rotate-45 -top-1.5 right-2"></div>
                        <p class="text-gray-200 dark:text-gray-300">Displays recent user activities in the system, such as uploads, approvals, and shares, based on the current filters.</p>
                    </div>
                </div>
            </h3>
            <div id="activityLogContainer" class="flex-1 overflow-y-auto pr-2 custom-scrollbar min-h-0 divide-y divide-gray-200 dark:divide-gray-700"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.register(ChartDataLabels);
        const dashboard = new DashboardManager();
        Chart.defaults.font.family = "'Outfit', sans-serif";
        dashboard.init();
    });

    class DashboardManager {
        constructor() {
            this.isLoading = false;
            this.isDarkMode = document.documentElement.classList.contains('dark');
            this.dateRangeInstance = null;
            this.dateStart = '';
            this.dateEnd = '';

            this.trendYear = document.getElementById('trendYearInput') ? document.getElementById('trendYearInput').value : new Date().getFullYear();

            this.selectedCustomers = [];
            this.selectedModels = [];
            this.selectedPartGroup = [];
            this.monitoringChart = null;
            this.trendChart = null;
            this.phaseChart = null;
            this.currentChartData = [];
            this.currentTrendData = [];
        }

        init() {
            this.initThemeListener();
            this.initDateRange();
            this.initSelect2();
            this.loadSaveEnv();
            this.attachButtonEvents();
            this.loadMonitoringChart();
            this.loadTrendChart();
            this.loadActivityLog();
            this.loadPhaseStatusChart();
            this.loadCards();

            setInterval(() => this.loadCards(), 60000);
            setInterval(() => this.loadMonitoringChart(), 60000);
            setInterval(() => this.loadTrendChart(), 60000);
            setInterval(() => this.loadActivityLog(), 60000);
            setInterval(() => this.loadPhaseStatusChart(), 60000);
            setInterval(() => this.loadSaveEnv(), 60000);
        }

        getFilterParams() {
            const statusData = $('#project_status').select2('data');
            const statusVal = (statusData && statusData.length > 0) ? statusData[0].text.trim() : '';

            const params = new URLSearchParams();

            if (this.dateStart) params.append('date_start', this.dateStart);
            if (this.dateEnd) params.append('date_end', this.dateEnd);
            if (statusVal && statusVal !== 'ALL') params.append('project_status', statusVal);

            this.selectedCustomers.forEach(item => params.append('customer[]', item.text.trim()));
            this.selectedModels.forEach(item => params.append('model[]', item.text.trim()));
            this.selectedPartGroup.forEach(item => params.append('part_group[]', item.text.trim()));

            return params;
        }

        formatDateJS(date) {
            if (!date) return '';
            const d = new Date(date);
            if (isNaN(d.getTime())) return '';
            let month = '' + (d.getMonth() + 1);
            let day = '' + d.getDate();
            const year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        }

        parseStorageString(str) {
            if (!str || str === 'N/A') return {
                value: 0,
                unit: ''
            };
            const value = parseFloat(str);
            const unit = str.replace(/[0-9.]/g, '').trim();
            return {
                value: isNaN(value) ? 0 : value,
                unit: unit ? ' ' + unit : ''
            };
        }

        animateCount(el, to, suffix = '', decimals = 0) {
            if (to === null || to === undefined || isNaN(to)) {
                el.textContent = (0).toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }) + suffix;
                return;
            }

            const target = parseFloat(to);
            let from = 0;
            const duration = 1500;
            const startTime = performance.now();

            const update = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const ease = 1 - Math.pow(1 - progress, 4);

                let current = from + (target - from) * ease;

                el.textContent = current.toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }) + suffix;

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    el.textContent = target.toLocaleString('id-ID', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    }) + suffix;
                }
            };

            requestAnimationFrame(update);
        }

        async loadCards() {
            const fetchText = async (url, elemId) => {
                const el = document.getElementById(elemId);
                if (!el) return;

                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    let countVal = (data && data.status === 'success') ? parseFloat(data.count) : 0;
                    if (isNaN(countVal)) countVal = 0;

                    this.animateCount(el, countVal);
                } catch (e) {
                    el.textContent = '0';
                }
            };

            fetchText('/api/active-users-count', 'activeUserCount');
            fetchText('/api/upload-count', 'uploadCount');
            fetchText('/api/download-count', 'downloadCount');
            fetchText('/api/doc-count', 'docCount');

            const u = document.getElementById('usedSpace');
            const t = document.getElementById('totalSpace');

            if (u && t) {
                try {
                    const res = await fetch('/api/disk-space');
                    const data = await res.json();

                    if (data.status === 'success') {
                        const usedData = this.parseStorageString(data.used);
                        const totalData = this.parseStorageString(data.total);

                        this.animateCount(u, usedData.value, usedData.unit);
                        this.animateCount(t, totalData.value, totalData.unit);
                    } else {
                        u.textContent = '0 B';
                        t.textContent = '0 B';
                    }
                } catch (e) {
                    console.error("Error loading disk space", e);
                    u.textContent = '0 B';
                    t.textContent = '0 B';
                }
            }
        }

        async loadSaveEnv() {
            try {
                const params = this.getFilterParams();
                const response = await fetch(`{{ route('api.get-save-env') }}?${params.toString()}`);
                if (!response.ok) throw new Error(`Network response was not ok: ${response.statusText}`);
                
                const data = await response.json();

                const getVal = (val) => {
                    if (typeof val === 'number') return val;
                    if (typeof val === 'string' && val.trim() !== '') return parseFloat(val);
                    return 0;
                };

                // Get values from data
                const paperVal = getVal(data.paper);
                const costVal = getVal(data.harga);
                const treeVal = getVal(data.save_tree);
                const co2Val = getVal(data.co2_reduced);

                // 1. Update Paper
                const elPaper = document.getElementById('ecoPaper');
                if (elPaper) this.animateCount(elPaper, paperVal, '', 0);

                // 2. Update Cost
                const elCost = document.getElementById('ecoCost');
                if (elCost) {
                    let displayVal = costVal;
                    let suffix = '';
                    let dec = 0;

                    if (costVal >= 1000000000) {
                        displayVal = costVal / 1000000000;
                        suffix = ' M';
                        dec = 2;
                    } else if (costVal >= 1000000) {
                        displayVal = costVal / 1000000;
                        suffix = 'Jt';
                        dec = 2;
                    } else if (costVal >= 1000) {
                        displayVal = costVal / 1000;
                        suffix = 'K';
                        dec = 1;
                    }
                    this.animateCount(elCost, displayVal, suffix, dec);
                }

                // 3. Update Trees Saved
                const elTrees = document.getElementById('ecoTrees');
                if (elTrees) this.animateCount(elTrees, treeVal, '', 5);

                // 4. Update CO2 Reduced
                const elCO2 = document.getElementById('ecoCO2');
                if (elCO2) this.animateCount(elCO2, co2Val, ' Kg', 3);

                // 5. Update Progress Circle & Percentage
                const elTreePercent = document.getElementById('ecoTreePercent');
                const elProgressCircle = document.getElementById('ecoProgressCircle');
                if (elTreePercent && elProgressCircle) {
                    const treeProgress = Math.min(treeVal, 1); // Cap at 1 tree (100%)
                    const percentage = treeProgress * 100;
                    
                    elTreePercent.innerHTML = `${percentage.toFixed(1)}%<br>towards 1 Tree`;

                    const circumference = 264;
                    const offset = circumference * (1 - treeProgress);
                    
                    elProgressCircle.style.transition = 'stroke-dashoffset 1.5s ease-out';
                    requestAnimationFrame(() => {
                        elProgressCircle.setAttribute('stroke-dashoffset', offset);
                    });
                }

            } catch (error) {
                console.error("Failed to load environment stats", error);
                // Reset to 0 on error
                document.getElementById('ecoPaper').textContent = '0';
                document.getElementById('ecoCost').textContent = '0';
                document.getElementById('ecoTrees').textContent = '0';
                document.getElementById('ecoCO2').textContent = '0 Kg';
                document.getElementById('ecoTreePercent').innerHTML = '0%<br>towards 1 Tree';
                document.getElementById('ecoProgressCircle').setAttribute('stroke-dashoffset', 264);
            }
        }

        async loadMonitoringChart() {
            const ctx = document.getElementById('monitoringChart');
            if (!ctx) return;

            const params = this.getFilterParams();

            try {
                const url = `{{ route('api.upload-monitoring-data') }}?${params.toString()}`;
                const response = await fetch(url);
                const result = await response.json();
                if (result.status === 'success') {
                    this.currentChartData = result.data || [];
                    this.renderChart(this.currentChartData);
                } else {
                    this.currentChartData = [];
                    this.renderChart([]);
                }
            } catch (error) {
                this.currentChartData = [];
                this.renderChart([]);
            }
        }

        renderChart(data) {
            const ctx = document.getElementById('monitoringChart').getContext('2d');
            if (this.monitoringChart) this.monitoringChart.destroy();

            // Mapping Data
            const labels = data.map(item => `${item.customer_name}-${item.model}-${item.project_status}-${item.part_group}`);
            const planData = data.map(item => parseFloat(item.plan_count));
            const actualData = data.map(item => parseFloat(item.actual_count));
            const percentageData = data.map(item => parseFloat(item.percentage));

            // Perhitungan Max Value untuk Y-Axis
            const maxDataValue = Math.max(...planData, ...actualData, 0);
            const suggestedMaxCount = maxDataValue > 0 ? Math.ceil(maxDataValue * 1.3) : 10;

            // Style Variables
            const gridColor = this.isDarkMode ? '#374151' : '#E5E7EB';
            const textColor = this.isDarkMode ? '#D1D5DB' : '#4B5563';
            const bgColor = this.isDarkMode ? '#1F2937' : '#FFFFFF';
            const borderColor = this.isDarkMode ? '#4B5563' : '#E5E7EB';

            // Hitung total data untuk batas limit zoom
            const totalDataCount = labels.length;

            this.monitoringChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Plan Count',
                        data: planData,
                        backgroundColor: 'rgba(59, 130, 246, 1)',
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderRadius: 2,
                        borderSkipped: 'bottom',
                        categoryPercentage: 0.9,
                        barPercentage: 0.5,
                        order: 2,
                        yAxisID: 'y',
                        animation: {
                            duration: 500,
                            easing: 'easeOutQuad'
                        }
                    }, {
                        label: 'Actual Count',
                        data: actualData,
                        backgroundColor: 'rgba(34, 197, 94, 1)',
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderRadius: 2,
                        borderSkipped: 'bottom',
                        categoryPercentage: 0.9,
                        barPercentage: 0.5,
                        order: 3,
                        yAxisID: 'y',
                        animation: {
                            duration: 500,
                            easing: 'easeOutQuad',
                            delay: 200
                        }
                    }, {
                        label: 'Percentage',
                        data: percentageData,
                        type: 'line',
                        borderColor: '#F59E0B',
                        backgroundColor: '#F59E0B',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 4,
                        order: 1,
                        yAxisID: 'y1',
                        datalabels: {
                            align: 'top',
                            anchor: 'end',
                            formatter: (value) => value + '%',
                            color: textColor
                        },
                        animation: {
                            duration: 500,
                            easing: 'easeOutQuad',
                            delay: 200
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        zoom: {
                            pan: {
                                enabled: true,
                                mode: 'x',
                                threshold: 0, // Agar responsif saat disentuh/digeser
                            },
                            limits: {
                                x: {
                                    // PENTING: Batasi geseran dari index 0 sampai index terakhir data
                                    min: 0,
                                    max: totalDataCount - 1
                                },
                                y: {
                                    min: 'original',
                                    max: 'original'
                                }
                            },
                            zoom: {
                                wheel: {
                                    enabled: true
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'x',
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rect',
                                color: textColor,
                                padding: 15,
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: bgColor,
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: borderColor,
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 14
                            }
                        },
                        datalabels: {
                            color: textColor,
                            anchor: 'end',
                            align: 'top',
                            offset: -1,
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            formatter: Math.round
                        }
                    },
                    scales: {
                        x: {
                            min: 0,
                            max: 4,
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 14
                                },
                                maxRotation: 0,
                                minRotation: 0,
                                autoSkip: false,
                                callback: function(value) {
                                    const label = this.getLabelForValue(value);
                                    if (!label) return '';
                                    const parts = label.split('-');
                                    if (parts.length > 2) {
                                        const line1 = parts.slice(0, 2).join('-');
                                        const line2 = parts.slice(2).join('-');
                                        return [line1, line2];
                                    }
                                    return label;
                                }
                            },
                            grid: {
                                color: gridColor,
                                drawBorder: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Count',
                                color: textColor,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            suggestedMax: suggestedMaxCount,
                            ticks: {
                                color: textColor,
                                maxTicksLimit: 3,
                                font: {
                                    size: 12
                                },
                                precision: 0
                            },
                            grid: {
                                color: gridColor,
                                drawBorder: false
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            min: 0,
                            max: 120,
                            title: {
                                display: true,
                                text: 'Percentage',
                                color: textColor,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                drawOnChartArea: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 12
                                },
                                stepSize: 50,
                                callback: function(value) {
                                    if (value > 100) return null;
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        async loadTrendChart() {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;
            try {
                const response = await fetch(`{{ route('api.trend-upload-download') }}?year=${this.trendYear}`);
                const result = await response.json();
                if (result.status === 'success') {
                    this.currentTrendData = result.data;
                    this.renderTrendChart(this.currentTrendData);
                } else {
                    this.currentTrendData = [];
                    this.renderTrendChart([]);
                }
            } catch (error) {
                this.currentTrendData = [];
                this.renderTrendChart([]);
            }
        }

        renderTrendChart(data) {
            const ctx = document.getElementById('trendChart').getContext('2d');

            if (this.trendChart) this.trendChart.destroy();

            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const uploadData = new Array(12).fill(0);
            const downloadData = new Array(12).fill(0);

            if (data && Array.isArray(data)) {
                data.forEach(item => {
                    const monthIndex = item.month - 1;
                    if (monthIndex >= 0 && monthIndex < 12) {
                        uploadData[monthIndex] = parseFloat(item.upload_count) || 0;
                        downloadData[monthIndex] = parseFloat(item.download_count) || 0;
                    }
                });
            }

            const gridColor = this.isDarkMode ? '#374151' : '#E5E7EB';
            const textColor = this.isDarkMode ? '#D1D5DB' : '#4B5563';
            const bgColor = this.isDarkMode ? '#1F2937' : '#FFFFFF';
            const borderColor = this.isDarkMode ? '#4B5563' : '#E5E7EB';

            this.trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthNames,
                    datasets: [{
                        label: 'Uploads',
                        data: uploadData,
                        borderColor: 'rgba(34, 197, 94, 1)',
                        backgroundColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                        pointBorderWidth: 0,
                    }, {
                        label: 'Downloads',
                        data: downloadData,
                        borderColor: 'rgba(234, 179, 8, 1)',
                        backgroundColor: 'rgba(234, 179, 8, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgba(234, 179, 8, 1)',
                        pointBorderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },

                    scales: {
                        x: {
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 14
                                }
                            },
                            grid: {
                                color: gridColor,
                                drawBorder: false
                            }
                        },
                        y: {
                            ticks: {
                                color: textColor,
                                maxTicksLimit: 3,
                                precision: 0,
                                font: {
                                    size: 14
                                }
                            },
                            grid: {
                                color: gridColor,
                                borderDash: [5, 5],
                                drawBorder: false
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rect',
                                color: textColor,
                                padding: 15,
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: bgColor,
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: borderColor,
                            borderWidth: 1,
                            usePointStyle: true,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 14
                            }
                        },
                        datalabels: {
                            display: false
                        }
                    }
                }
            });
        }

        async loadPhaseStatusChart() {
            const ctx = document.getElementById('phaseStatusChart');
            if (!ctx) return;

            const params = this.getFilterParams();

            try {
                const url = `{{ route('api.upload-phase-status') }}?${params.toString()}`;
                const response = await fetch(url);
                const result = await response.json();
                if (result.status === 'success') {
                    this.renderPhaseChart(result.data);
                } else {
                    this.renderPhaseChart([]);
                }
            } catch (error) {
                this.renderPhaseChart([]);
            }
        }

        renderPhaseChart(data) {
            const ctx = document.getElementById('phaseStatusChart').getContext('2d');
            if (this.phaseChart) this.phaseChart.destroy();

            const aggregated = {};
            if (Array.isArray(data)) {
                data.forEach(item => {
                    const status = item.project_status || 'Unknown';
                    const count = parseInt(item.total) || 0;
                    aggregated[status] = (aggregated[status] || 0) + count;
                });
            }

            const labels = Object.keys(aggregated);
            const values = Object.values(aggregated);
            const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1'];
            const textColor = this.isDarkMode ? '#D1D5DB' : '#4B5563';
            const borderColor = this.isDarkMode ? '#1F2937' : '#FFFFFF';

            this.phaseChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [{
                        data: values.length ? values : [1],
                        backgroundColor: values.length ? colors.slice(0, values.length) : ['#9CA3AF'],
                        borderColor: borderColor,
                        borderWidth: 5,
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuad',
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rect',
                                color: textColor,
                                padding: 15,
                                font: {
                                    size: 14
                                },
                                boxWidth: 14,
                                boxHeight: 14,
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map((label, i) => {
                                            const dataset = data.datasets[0];
                                            const bgColor = dataset.backgroundColor[i];

                                            return {
                                                text: label,
                                                fillStyle: bgColor,
                                                strokeStyle: bgColor,
                                                lineWidth: 0,
                                                hidden: !chart.getDataVisibility(i),
                                                index: i,
                                                fontColor: textColor,
                                                fontSize: 14,
                                                pointStyle: 'rect'
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 14
                            },
                            boxWidth: 14,
                            boxHeight: 14,
                            callbacks: {
                                labelColor: function(context) {
                                    return {
                                        borderColor: 'transparent',
                                        backgroundColor: context.dataset.backgroundColor[context.dataIndex],
                                        borderWidth: 0,
                                        borderRadius: 0,
                                        pointStyle: 'rect'
                                    };
                                },
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) label += ': ';
                                    let value = context.raw;
                                    let total = context.chart._metasets[context.datasetIndex].total;
                                    let percentage = Math.round((value / total) * 100) + '%';
                                    return label + percentage + ' (' + value + ')';
                                }
                            }
                        },
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            formatter: (value, ctx) => {
                                let total = ctx.chart._metasets[ctx.datasetIndex].total;
                                let percentage = (value / total) * 100;
                                return percentage > 5 ? Math.round(percentage) + '%' : '';
                            }
                        }
                    }
                }
            });
        }

        async loadActivityLog() {
            const container = document.getElementById('activityLogContainer');
            if (!container) return;

            container.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full w-full min-h-[200px]">
                    <i class="fa-solid fa-circle-notch fa-spin text-blue-500 dark:text-blue-400 text-3xl mb-3"></i>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 animate-pulse">Loading activities...</span>
                </div>
            `;

            const params = this.getFilterParams();

            try {
                const response = await fetch(`{{ route('api.getDataActivityLog') }}?${params.toString()}`);
                const result = await response.json();

                if (result.status === 'success') {
                    container.innerHTML = '';

                    if (result.data && result.data.length > 0) {
                        result.data.forEach(log => {
                            container.insertAdjacentHTML('beforeend', this.formatLogEntry(log));
                        });

                        requestAnimationFrame(() => {
                            const items = container.querySelectorAll('.log-item');
                            items.forEach((item, index) => {
                                setTimeout(() => {
                                    item.classList.remove('opacity-0', 'translate-y-4');
                                }, index * 50);
                            });
                        });

                    } else {
                        container.innerHTML = `<div class="p-4 text-center text-gray-500 dark:text-gray-400 opacity-0 transition-opacity duration-500 ease-in" id="emptyMsg">No activity found for this filter.</div>`;
                        setTimeout(() => {
                            document.getElementById('emptyMsg').classList.remove('opacity-0');
                        }, 50);
                    }
                }
            } catch (error) {
                console.error(error);
                container.innerHTML = `<div class="p-4 text-center text-gray-500">Error loading activities.</div>`;
            }
        }

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

            let message = log.activity_code === 'UPLOAD' ?
                `<strong>${log.user_name || 'System'}</strong> upload new document.` :
                `<strong>${log.user_name || 'System'}</strong> performed action: <strong>${log.activity_code}</strong>.`;

            const date = log.created_at ? new Date(log.created_at.replace(' ', 'T')) : new Date();
            const dateStr = date.toLocaleString('id-ID', {
                day: 'numeric',
                month: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            let metaDetails = '';
            if (log.meta && log.activity_code === 'UPLOAD') {
                const details = [log.meta.customer_code, log.meta.model_name, log.meta.part_no, log.meta.doctype_group, log.meta.part_group_code].filter(Boolean);
                if (details.length) metaDetails = `<p class="mt-1 text-[12px] text-gray-600 dark:text-gray-400 font-mono">${details.join(' - ')}</p>`;
            }

            return `<div class="log-item py-2 px-1 flex space-x-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-all duration-500 ease-out opacity-0 translate-y-4" data-id="${log.id}">
                <div class="flex-shrink-0 pt-1"><i class="fa-solid ${logInfo.icon} ${logInfo.color} w-5 text-center"></i></div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <p class="text-sm text-gray-800 dark:text-gray-200">${message}</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${dateStr}</p>
                    ${metaDetails}
                </div>
            </div>`;
        }

        initSelect2() {
            const self = this;
            $('#customer_input').select2({
                dropdownParent: $('#customer_input').parent(),
                width: '100%',
                placeholder: 'Select Customer...',
                allowClear: true,
                ajax: {
                    url: "{{ route('dashboard.getCustomers') }}",
                    method: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({
                        _token: "{{ csrf_token() }}",
                        q: params.term,
                        page: params.page || 1
                    }),
                    processResults: (data, params) => ({
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    })
                }
            }).on('change', function() {
                const data = $(this).select2('data')[0];
                if (data && data.id) {
                    if (!self.selectedCustomers.find(x => x.id === data.id)) {
                        self.selectedCustomers.push({
                            id: data.id,
                            text: data.text
                        });
                        self.renderFilterPills();
                    }
                    $(this).val(null).trigger('change.select2');
                }
                $('#model_input').prop('disabled', self.selectedCustomers.length === 0);
                if (self.selectedCustomers.length > 1 && self.selectedPartGroup.length > 0) {
                    self.selectedPartGroup = [];
                    self.renderFilterPills();
                }
            });

            $('#model_input').prop('disabled', true);
            $('#model_input').select2({
                dropdownParent: $('#model_input').parent(),
                width: '100%',
                placeholder: 'Select Model...',
                allowClear: true,
                ajax: {
                    url: "{{ route('dashboard.getModels') }}",
                    method: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({
                        _token: "{{ csrf_token() }}",
                        q: params.term,
                        page: params.page || 1,
                        customer_ids: self.selectedCustomers.map(item => item.id)
                    }),
                    processResults: (data, params) => ({
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    })
                }
            }).on('change', function() {
                const d = $(this).select2('data')[0];
                if (d && d.id) {
                    if (!self.selectedModels.find(x => x.id === d.id)) {
                        self.selectedModels.push({
                            id: d.id,
                            text: d.text,
                            customer_id: d.customer_id
                        });
                        self.renderFilterPills();
                    }
                    $(this).val(null).trigger('change.select2');
                }
            });

            $('#part_group_multi_input').select2({
                dropdownParent: $('#part_group_multi_input').parent(),
                width: '100%',
                placeholder: 'Select Part Group...',
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
                            more: data.pagination ? data.pagination.more : (params.page * 10) < data.total_count
                        }
                    })
                }
            }).on('change', function() {
                const d = $(this).select2('data')[0];
                if (d && d.text) {
                    if (self.selectedCustomers.length > 1) {
                        self.selectedPartGroup = [{
                            text: d.text
                        }];
                    } else {
                        if (!self.selectedPartGroup.find(x => x.text === d.text)) self.selectedPartGroup.push({
                            text: d.text
                        });
                    }
                    self.renderFilterPills();
                    $(this).val(null).trigger('change.select2');
                }
            });

            $('#project_status').select2({
                dropdownParent: $('#project_status').parent(),
                width: '100%',
                placeholder: 'Select Status',
                ajax: {
                    url: "{{ route('dashboard.getStatus') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: (params) => ({
                        _token: "{{ csrf_token() }}",
                        q: params.term
                    }),
                    processResults: (data) => {
                        let res = data.results || [];
                        res.unshift({
                            id: 'ALL',
                            text: 'ALL'
                        });
                        return {
                            results: res
                        };
                    }
                }
            });
        }

        renderFilterPills() {
            const container = document.getElementById('filterPillContainer');
            container.innerHTML = '';

            const createPill = (type, item, stateKey) => {
                const span = document.createElement('span');

                span.className = 'filter-pill mr-2 inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-medium';

                span.innerHTML = `<span class="font-normal mr-1">${type}:</span><span>${item.text}</span><button type="button" class="filter-pill-remove ml-2 hover:text-blue-600 focus:outline-none" data-id="${item.id}"><i class="fa-solid fa-times fa-xs"></i></button>`;

                span.querySelector('button').addEventListener('click', () => {
                    const arr = this[stateKey];
                    const idx = arr.findIndex(x => x.id === item.id);
                    if (idx > -1) {
                        const removedItem = arr[idx];
                        arr.splice(idx, 1);
                        if (stateKey === 'selectedCustomers') {
                            if (this.selectedModels.length > 0) {
                                this.selectedModels = this.selectedModels.filter(model => String(model.customer_id) !== String(removedItem.id));
                            }
                            if (this.selectedCustomers.length === 0) {
                                $('#model_input').prop('disabled', true).val(null).trigger('change');
                                this.selectedModels = [];
                            } else {
                                $('#model_input').prop('disabled', false);
                            }
                        }
                        this.renderFilterPills();
                    }
                });
                return span;
            };

            this.selectedCustomers.forEach(i => container.appendChild(createPill('Cust', i, 'selectedCustomers')));
            this.selectedModels.forEach(i => container.appendChild(createPill('Model', i, 'selectedModels')));
            this.selectedPartGroup.forEach(i => container.appendChild(createPill('Group', i, 'selectedPartGroup')));
        }

        initDateRange() {
            const now = new Date();
            const year = now.getFullYear();
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
            this.dateStart = `${year}-${month}-01`;
            this.dateEnd = `${year}-${month}-${lastDay}`;
            this.dateRangeInstance = new Litepicker({
                element: document.getElementById('date_range_input'),
                singleMode: false,
                allowRepick: true,
                format: 'DD MMM YYYY',
                startDate: this.dateStart,
                endDate: this.dateEnd,
                setup: (picker) => {
                    picker.on('selected', (d1, d2) => {
                        this.dateStart = this.formatDateJS(d1.dateInstance);
                        this.dateEnd = this.formatDateJS(d2.dateInstance);
                    });
                    picker.on('show', () => {
                        this.isDarkMode ? picker.ui.classList.add('dark') : picker.ui.classList.remove('dark');
                    });
                }
            });
        }

        initThemeListener() {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((m) => {
                    if (m.attributeName === 'class') {
                        const newDarkMode = document.documentElement.classList.contains('dark');
                        if (newDarkMode !== this.isDarkMode) {
                            this.isDarkMode = newDarkMode;
                            if (this.dateRangeInstance && this.dateRangeInstance.ui) {
                                this.isDarkMode ? this.dateRangeInstance.ui.classList.add('dark') : this.dateRangeInstance.ui.classList.remove('dark');
                            }
                            if (this.currentChartData && this.currentChartData.length > 0) this.renderChart(this.currentChartData);
                            else if (this.monitoringChart) this.renderChart([]);

                            if (this.currentTrendData && this.currentTrendData.length > 0) this.renderTrendChart(this.currentTrendData);
                            else if (this.trendChart) this.renderTrendChart([]);

                            this.loadPhaseStatusChart();
                        }
                    }
                });
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        }

        attachButtonEvents() {
            const btnApply = document.getElementById('btnApply');
            const btnText = document.getElementById('btnApplyText');
            const btnLoader = document.getElementById('btnApplyLoader');
            const toggleBtn = document.getElementById('toggleFilterBtn');

            const yearInput = document.getElementById('trendYearInput');
            if (yearInput) {
                yearInput.addEventListener('change', (e) => {
                    if (e.target.value.length === 4) {
                        this.trendYear = e.target.value;
                        this.loadTrendChart();
                    }
                });
            }
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const $card = $('#filterCard');
                    const $icon = $(toggleBtn).find('i');
                    const wrapper = document.getElementById('dashboardWrapper');
                    const isOpening = $card.is(':hidden');

                    if (isOpening) {
                        wrapper.classList.remove('h-[calc(100vh-70px)]', 'overflow-hidden');
                        wrapper.classList.add('min-h-[calc(100vh-70px)]', 'h-auto', 'pb-4');
                    }

                    $card.stop(true, true).slideToggle(300, 'swing', function() {
                        $(this).css('overflow', 'visible');
                        if (!isOpening) {
                            wrapper.classList.remove('min-h-[calc(100vh-70px)]', 'h-auto', 'pb-4');
                            wrapper.classList.add('h-[calc(100vh-70px)]', 'overflow-hidden');
                        }
                    });

                    if (isOpening) {
                        $icon.css({
                            'transform': 'rotate(0deg)',
                            'transition': 'transform 0.3s ease'
                        });
                        toggleBtn.classList.remove('text-blue-600', 'bg-blue-50', 'dark:bg-gray-700');
                    } else {
                        $icon.css({
                            'transform': 'rotate(180deg)',
                            'transition': 'transform 0.3s ease'
                        });
                        toggleBtn.classList.add('text-blue-600', 'bg-blue-50', 'dark:bg-gray-700');
                    }
                });
            }

            btnApply.addEventListener('click', async () => {
                if (this.isLoading) return;
                this.isLoading = true;
                btnApply.disabled = true;
                btnText.style.display = 'none';
                btnLoader.style.display = 'inline-block';

                await Promise.all([
                    this.loadMonitoringChart(),
                    this.loadActivityLog(),
                    this.loadSaveEnv(),
                    this.loadPhaseStatusChart()
                ]);

                this.isLoading = false;
                btnApply.disabled = false;
                btnText.style.display = 'inline-block';
                btnLoader.style.display = 'none';
            });

            document.getElementById('btnReset').addEventListener('click', () => {
                this.selectedCustomers = [];
                this.selectedModels = [];
                this.selectedPartGroup = [];
                $('#customer_input').val(null).trigger('change');
                $('#model_input').prop('disabled', true).val(null).trigger('change');
                $('#part_group_multi_input').val(null).trigger('change');
                $('#project_status').val('ALL').trigger('change');
                const now = new Date();
                this.dateStart = this.formatDateJS(new Date(now.getFullYear(), now.getMonth(), 1));
                this.dateEnd = this.formatDateJS(new Date(now.getFullYear(), now.getMonth() + 1, 0));
                if (this.dateRangeInstance) this.dateRangeInstance.setDateRange(this.dateStart, this.dateEnd);
                this.renderFilterPills();
                this.loadMonitoringChart();
                this.loadPhaseStatusChart();
                this.loadSaveEnv();
                this.loadActivityLog();
            });
        }
    }
</script>
@endsection