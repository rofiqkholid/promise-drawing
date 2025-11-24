@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')

@section('content')
<div class="flex flex-col gap-2 h-[calc(100vh-110px)] w-full overflow-hidden">

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

    <div id="filterCard" style="display: none;" class="flex-none mt-2 bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
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

    <div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-2 items-stretch">
        <div class="w-full lg:w-[70%] bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="flex-none flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center">
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
            <h3 class="flex-none text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2 flex items-center">
                <i class="fa-solid fa-chart-pie mr-2 text-orange-500"></i> Phase Status
            </h3>
            <div class="relative w-full flex-1 min-h-0 flex justify-center items-center">
                <canvas id="phaseStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-2 items-stretch mb-2">
        <div class="w-full lg:w-[45%] bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="flex-none text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2 flex items-center">
                <i class="fa-solid fa-arrow-trend-up mr-2 text-purple-500"></i> Trend Upload & Download
            </h3>
            <div class="relative w-full flex-1 min-h-0">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="w-full lg:w-[25%] bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-gray-800 dark:to-gray-900 p-4 rounded-lg border border-emerald-200 dark:border-emerald-900/30 flex flex-col overflow-hidden">
            <h3 class="flex-none text-sm font-semibold text-emerald-800 dark:text-emerald-400 mb-2 flex items-center">
                <i class="fa-solid fa-seedling mr-2"></i> Environmental Impact
            </h3>
            <div class="flex-1 flex flex-col gap-2 min-h-0 justify-center">
                <div class="flex flex-row items-center bg-white dark:bg-gray-800/50 px-3 py-2 rounded-xl shadow-sm border border-emerald-100 dark:border-emerald-900/20 overflow-hidden">
                    <div class="bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-300 w-8 h-8 rounded-full flex items-center justify-center shrink-0 mr-3">
                        <i class="fa-solid fa-tree"></i>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Trees Saved</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-bold text-gray-800 dark:text-gray-100" id="ecoTrees">0.00140</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row items-center bg-white dark:bg-gray-800/50 px-3 py-2 rounded-xl shadow-sm border border-teal-100 dark:border-teal-900/20 overflow-hidden">
                    <div class="bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 w-8 h-8 rounded-full flex items-center justify-center shrink-0 mr-3">
                        <i class="fa-solid fa-wind"></i>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs text-gray-500 dark:text-gray-400">CO2 Reduced</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-bold text-gray-800 dark:text-gray-100" id="ecoCO2">0,031</span>
                            <span class="text-xs font-normal text-gray-500">kg</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-[30%] bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="flex-none text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2 flex items-center">
                <i class="fa-solid fa-newspaper mr-2 text-gray-500"></i> Activity Log
            </h3>
            <div id="activityLogContainer" class="flex-1 overflow-y-auto pr-2 custom-scrollbar min-h-0 divide-y divide-gray-200 dark:divide-gray-700"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.register(ChartDataLabels);
        const dashboard = new DashboardManager();
        dashboard.init();
    });

    class DashboardManager {
        constructor() {
            this.isLoading = false;
            this.isDarkMode = document.documentElement.classList.contains('dark');
            this.dateRangeInstance = null;
            this.dateStart = '';
            this.dateEnd = '';
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
            this.loadCards();
            this.loadActivityLog();
            this.loadMonitoringChart();
            this.loadTrendChart();
            this.loadPhaseStatusChart();
            this.attachButtonEvents();
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

        async loadCards() {
            const fetchText = async (url, elemId) => {
                const el = document.getElementById(elemId);
                if (!el) return;
                el.textContent = '...';
                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    let countVal = (data && data.status === 'success') ? data.count : 0;
                    el.textContent = countVal;
                    if (elemId === 'downloadCount') {
                        let numericCount = parseInt(String(countVal).replace(/,/g, '')) || 0;
                        if (numericCount < 100) numericCount = 250000;
                        this.updateEcoImpact(numericCount);
                    }
                } catch (e) {
                    el.textContent = 'Error';
                }
            };

            fetchText('/api/active-users-count', 'activeUserCount');
            fetchText('/api/upload-count', 'uploadCount');
            fetchText('/api/download-count', 'downloadCount');
            fetchText('/api/doc-count', 'docCount');

            const u = document.getElementById('usedSpace'),
                t = document.getElementById('totalSpace');
            if (u && t) {
                try {
                    const res = await fetch('/api/disk-space');
                    const data = await res.json();
                    if (data.status === 'success') {
                        u.textContent = data.used;
                        t.textContent = data.total;
                    }
                } catch (e) {
                    u.textContent = 'Err';
                }
            }
        }

        updateEcoImpact(downloadCount) {
            const treeFactor = 80000;
            const co2Factor = 0.000275;
            const treesSaved = downloadCount / treeFactor;
            const co2Reduced = downloadCount * co2Factor;
            const elTrees = document.getElementById('ecoTrees');
            const elCO2 = document.getElementById('ecoCO2');

            if (elTrees) {
                elTrees.textContent = treesSaved < 1 ?
                    treesSaved.toFixed(5) :
                    treesSaved.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
            }
            if (elCO2) {
                elCO2.textContent = co2Reduced.toLocaleString('id-ID', {
                    minimumFractionDigits: 3,
                    maximumFractionDigits: 3
                });
            }
        }

        async loadMonitoringChart() {
            const ctx = document.getElementById('monitoringChart');
            if (!ctx) return;
            if (this.monitoringChart) {
                this.monitoringChart.destroy();
                this.monitoringChart = null;
            }
            const statusData = $('#project_status').select2('data');
            const statusVal = (statusData && statusData.length > 0) ? statusData[0].text.trim() : '';
            const params = new URLSearchParams();
            if (this.dateStart) params.append('date_start', this.dateStart);
            if (this.dateEnd) params.append('date_end', this.dateEnd);
            if (statusVal && statusVal !== 'ALL') params.append('project_status', statusVal);
            this.selectedCustomers.forEach(item => params.append('customer[]', item.text.trim()));
            this.selectedModels.forEach(item => params.append('model[]', item.text.trim()));
            this.selectedPartGroup.forEach(item => params.append('part_group[]', item.text.trim()));

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

            const labels = data.map(item => `${item.customer_name} - ${item.model_name}`);
            const planData = data.map(item => parseFloat(item.plan_count));
            const actualData = data.map(item => parseFloat(item.actual_count));
            const percentageData = data.map(item => parseFloat(item.percentage));
            const maxDataValue = Math.max(...planData, ...actualData, 0);
            const suggestedMaxCount = maxDataValue > 0 ? Math.ceil(maxDataValue * 1.3) : 10;
            const gridColor = this.isDarkMode ? '#374151' : '#E5E7EB';
            const textColor = this.isDarkMode ? '#D1D5DB' : '#4B5563';
            const bgColor = this.isDarkMode ? '#1F2937' : '#FFFFFF';
            const borderColor = this.isDarkMode ? '#4B5563' : '#E5E7EB';

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
                        borderRadius: 20,
                        borderSkipped: 'bottom',
                        categoryPercentage: 0.9,
                        barPercentage: 0.73,
                        order: 2,
                        yAxisID: 'y',
                        animation: {
                            duration: 500,
                            easing: 'easeOutQuart'
                        }
                    }, {
                        label: 'Actual Count',
                        data: actualData,
                        backgroundColor: 'rgba(34, 197, 94, 1)',
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderRadius: 20,
                        borderSkipped: 'bottom',
                        categoryPercentage: 0.9,
                        barPercentage: 0.73,
                        order: 3,
                        yAxisID: 'y',
                        animation: {
                            duration: 500,
                            easing: 'easeOutQuart',
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
                            easing: 'easeOutQuart',
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
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rect',
                                color: textColor,
                                padding: 15,
                                font: {
                                    size: 11
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
                            usePointStyle: true
                        },
                        datalabels: {
                            color: textColor,
                            anchor: 'end',
                            align: 'top',
                            offset: 4,
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            formatter: Math.round
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 11
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
                                    size: 11
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
                            title: {
                                display: true,
                                color: textColor,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            ticks: {
                                color: textColor,
                                maxTicksLimit: 3,
                                font: {
                                    size: 11
                                },
                                callback: (v) => v + '%'
                            },
                            grid: {
                                drawOnChartArea: false,
                                drawBorder: false
                            },
                            min: 0,
                            max: 100
                        }
                    }
                }
            });
        }

        async loadTrendChart() {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;
            try {
                const response = await fetch("{{ route('api.trend-upload-download') }}");
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
                        animation: {
                            duration: 200,
                            easing: 'easeOutQuart'
                        }
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
                        animation: {
                            duration: 200,
                            easing: 'easeOutQuart',
                            delay: 100
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
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rect',
                                color: textColor,
                                padding: 15,
                                font: {
                                    size: 11
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
                            usePointStyle: true
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 11
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
                                precision: 0,
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: gridColor,
                                borderDash: [5, 5],
                                drawBorder: false
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        async loadPhaseStatusChart() {
            const ctx = document.getElementById('phaseStatusChart');
            if (!ctx) return;
            try {
                const response = await fetch("{{ route('api.upload-phase-status') }}");
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
                        borderWidth: 2
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
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) label += ': ';
                                    let value = context.raw;
                                    let total = context.chart._metasets[context.datasetIndex].total;
                                    let percentage = Math.round((value / total) * 100) + '%';

                                    // PERUBAHAN DI SINI: Format dibalik menjadi "Label: 25% (10)"
                                    return label + percentage + ' (' + value + ')';
                                }
                            }
                        },
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            formatter: (value, ctx) => {
                                let total = ctx.chart._metasets[ctx.datasetIndex].total;
                                let percentage = (value / total) * 100;

                                // PERUBAHAN DI SINI: Return persentase, bukan value
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
            container.innerHTML = `<div class="p-4 text-center text-gray-500 dark:text-gray-400">Loading activities...</div>`;
            try {
                const response = await fetch("{{ route('api.getDataActivityLog') }}");
                const result = await response.json();
                if (result.status === 'success') {
                    container.innerHTML = '';
                    if (result.data && result.data.length > 0) {
                        result.data.forEach(log => container.insertAdjacentHTML('beforeend', this.formatLogEntry(log)));
                    } else {
                        container.innerHTML = `<div class="p-4 text-center text-gray-500 dark:text-gray-400">No recent activity found.</div>`;
                    }
                }
            } catch (error) {
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
                `<strong>${log.user_name || 'System'}</strong> uploaded a new document.` :
                `<strong>${log.user_name || 'System'}</strong> performed action: <strong>${log.activity_code}</strong>.`;
            const date = log.created_at ? new Date(log.created_at.replace(' ', 'T')) : new Date();
            let metaDetails = '';
            if (log.meta && log.activity_code === 'UPLOAD') {
                const details = [log.meta.customer_code, log.meta.model_name, log.meta.part_no, log.meta.doctype_group, log.meta.part_group_code].filter(Boolean);
                if (details.length) metaDetails = `<p class="mt-2 text-sm text-gray-600 dark:text-gray-400 font-mono">${details.join(' - ')}</p>`;
            }
            const timeAgo = (d) => {
                const diff = Math.floor((new Date() - d) / 1000);
                if (diff < 5) return "just now";
                const units = {
                    31536000: 'years',
                    2592000: 'months',
                    86400: 'days',
                    3600: 'hours',
                    60: 'minutes'
                };
                for (const [sec, name] of Object.entries(units).reverse()) {
                    const val = Math.floor(diff / sec);
                    if (val >= 1) return `${val} ${name} ago`;
                }
                return `${diff} seconds ago`;
            };
            return `<div class="py-3 px-2 flex space-x-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                <div class="flex-shrink-0 pt-1"><i class="fa-solid ${logInfo.icon} fa-lg ${logInfo.color} w-5 text-center"></i></div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <p class="text-sm text-gray-800 dark:text-gray-200">${message}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-3 whitespace-nowrap">${date.toLocaleString('id-ID')}</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${timeAgo(date)}</p>
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
                span.className = 'filter-pill';
                span.innerHTML = `<span class="font-normal text-gray-500 mr-1">${type}:</span><span>${item.text}</span><button type="button" class="filter-pill-remove" data-id="${item.id}"><i class="fa-solid fa-times fa-xs"></i></button>`;
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

            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const $card = $('#filterCard');
                    const $icon = $(toggleBtn).find('i');
                    $card.stop(true, true).slideToggle(300, 'swing', function() {
                        $(this).css('overflow', 'visible');
                    });
                    if ($card.is(':visible')) {
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
                await this.loadMonitoringChart();
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
            });
        }
    }
</script>
@endsection