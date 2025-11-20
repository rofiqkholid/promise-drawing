@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')
@section('content')
<style>
    /* Custom Utility */
    .d-none-custom {
        display: none !important;
    }

    /* Fix Select2 & Header Layout */
    .select2-container--default .select2-selection--single .select2-selection__rendered:empty {
        display: none;
    }

    /* Filter Pill Styles */
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

    /* Dark Mode Styles */
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

    /* Litepicker Dark Mode */
   
</style>

<div>
    {{-- SECTION: CARDS --}}
    <div class="w-full grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
        <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-blue-100 dark:bg-blue-900/50 text-blue-500 dark:text-blue-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0"><i class="fa-solid fa-file-lines fa-xl"></i></div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Total Files</h3>
                    <p id="docCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-green-100 dark:bg-green-900/50 text-green-500 dark:text-green-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0"><i class="fa-solid fa-cloud-arrow-up fa-xl"></i></div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Upload</h3>
                    <p id="uploadCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-yellow-100 dark:bg-yellow-900/50 text-yellow-500 dark:text-yellow-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0"><i class="fa-solid fa-cloud-arrow-down fa-xl"></i></div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Download</h3>
                    <p id="downloadCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-red-100 dark:bg-red-900/50 text-red-500 dark:text-red-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0"><i class="fa-solid fa-users fa-xl"></i></div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">User Active</h3>
                    <p id="activeUserCount" class="text-2xl font-bold text-gray-800 dark:text-gray-100">0</p>
                </div>
            </div>
        </div>
        <div class="relative bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center">
                <div class="bg-purple-100 dark:bg-purple-900/50 text-purple-500 dark:text-purple-400 rounded-lg p-3 mr-4 flex items-center justify-center h-12 w-12 flex-shrink-0"><i class="fa-solid fa-hard-drive fa-xl"></i></div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-base font-medium">Server Storage</h3>
                    <p class="text-lg font-bold text-gray-800 dark:text-gray-100"><span id="freeSpace">...</span> free</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><span id="usedSpace">...</span> / <span id="totalSpace">...</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: FILTERS --}}
    <div class="mt-6 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
            <i class="fa-solid fa-filter mr-2 text-gray-500"></i> Filter Data
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-start">
            <!-- Date Range -->
            <div>
                <label for="date_range_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range (Upload)</label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><i class="fa-solid fa-calendar-days text-gray-400"></i></div>
                    <input type="text" id="date_range_input" name="date_range_input" class="block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-500 focus:ring-0 focus:outline-none sm:text-sm py-2 pl-10 pr-3" placeholder="Select date range...">
                </div>
            </div>
            <!-- Customer -->
            <div>
                <label for="customer_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                <div class="relative mt-1"><select id="customer_input" name="customer_input" class="w-full"></select></div>
            </div>
            <!-- Model -->
            <div>
                <label for="model_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                <div class="relative mt-1"><select id="model_input" name="model_input" class="w-full"></select></div>
            </div>
            <!-- Part Group -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>
                <div class="relative mt-1"><select id="part_group_multi_input" name="part_group_multi_input" class="w-full"></select></div>
            </div>
            <!-- Status -->
            <div>
                <label for="project_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <div class="relative mt-1"><select id="project_status" name="project_status" class="w-full"></select></div>
            </div>
        </div>

        <div class="w-full flex justify-between items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div id="filterPillContainer" class="filter-pill-container flex-grow pr-6"></div>
            <div class="flex-shrink-0 flex space-x-3">
                <button type="button" id="btnReset" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"> Reset </button>
                <button type="button" id="btnApply" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed min-w-[150px]">
                    <span id="btnApplyText"> <i class="fa-solid fa-check mr-2"></i> Apply Filter </span>
                    <span id="btnApplyLoader" style="display: none;">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- SECTION: CHART & NEWSFEED --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-1 gap-6">
        <!-- Chart Section -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
                <i class="fa-solid fa-chart-bar mr-2 text-blue-500"></i> Upload Monitoring (Plan vs Actual)
            </h3>
            <div class="relative h-96 w-full">
                <canvas id="monitoringChart"></canvas>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center"><i class="fa-solid fa-newspaper mr-2 text-gray-500"></i>Newsfeed / Activity Log</h3>
            <div id="activityLogContainer" class="divide-y divide-gray-200 dark:divide-gray-700 h-96 overflow-y-auto"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Register ChartDataLabels plugin globally
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

            // Filter State
            this.selectedCustomers = [];
            this.selectedModels = [];
            this.selectedPartGroup = [];

            // Chart Instance
            this.monitoringChart = null;
            this.currentChartData = []; // Menyimpan data chart terakhir
        }

        init() {
            this.initThemeListener();
            this.initDateRange();
            this.initSelect2();
            this.loadCards();
            this.loadActivityLog();
            this.loadMonitoringChart();
            this.attachButtonEvents();
        }

        // Helper: Format Date YYYY-MM-DD aman
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

        // --- API: CARDS ---
        async loadCards() {
            const fetchText = async (url, elemId) => {
                const el = document.getElementById(elemId);
                if (!el) return;
                el.textContent = '...';
                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    el.textContent = (data && data.status === 'success') ? data.count : 'Error';
                } catch (e) {
                    el.textContent = 'Error';
                }
            };

            fetchText('/api/active-users-count', 'activeUserCount');
            fetchText('/api/upload-count', 'uploadCount');
            fetchText('/api/download-count', 'downloadCount');
            fetchText('/api/doc-count', 'docCount');

            const f = document.getElementById('freeSpace'),
                u = document.getElementById('usedSpace'),
                t = document.getElementById('totalSpace');
            if (f && u && t) {
                try {
                    const res = await fetch('/api/disk-space');
                    const data = await res.json();
                    if (data.status === 'success') {
                        f.textContent = data.free;
                        u.textContent = data.used;
                        t.textContent = data.total;
                    }
                } catch (e) {
                    f.textContent = 'Err';
                }
            }
        }

        // --- API: CHART (Monitoring Data) ---
        async loadMonitoringChart() {
            const ctx = document.getElementById('monitoringChart');
            if (!ctx) return;

            // 1. Clear Old Chart first
            if (this.monitoringChart) {
                this.monitoringChart.destroy();
                this.monitoringChart = null;
            }

            // 2. Get Filter Values
            const statusData = $('#project_status').select2('data');

            // PERBAIKAN DISINI: 
            // Mengambil .text (label) alih-alih .id (value angka)
            // .trim() digunakan untuk menghapus spasi yang tidak perlu
            const statusVal = (statusData && statusData.length > 0) ? statusData[0].text.trim() : '';

            // 3. Construct Payload
            const params = new URLSearchParams();

            if (this.dateStart) params.append('date_start', this.dateStart);
            if (this.dateEnd) params.append('date_end', this.dateEnd);

            // Pastikan logic pengecekan 'ALL' tetap berjalan jika text-nya adalah "ALL"
            if (statusVal && statusVal !== 'ALL') params.append('project_status', statusVal);

            // Bagian ini sudah benar (mengirim text), tidak perlu diubah
            this.selectedCustomers.forEach(item => params.append('customer[]', item.text.trim()));
            this.selectedModels.forEach(item => params.append('model[]', item.text.trim()));
            this.selectedPartGroup.forEach(item => params.append('part_group[]', item.text.trim()));

            try {
                const url = `{{ route('api.upload-monitoring-data') }}?${params.toString()}`;
                console.log('Fetching Chart Data:', url);

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
                console.error("Error fetching chart data:", error);
                this.currentChartData = [];
                this.renderChart([]);
            }
        }

        renderChart(data) {
            const ctx = document.getElementById('monitoringChart').getContext('2d');

            if (this.monitoringChart) {
                this.monitoringChart.destroy();
            }

            const labels = data.map(item => `${item.customer_name} - ${item.model_name}`);
            const planData = data.map(item => parseFloat(item.plan_count));
            const actualData = data.map(item => parseFloat(item.actual_count));
            const percentageData = data.map(item => parseFloat(item.percentage));

            // Menghitung nilai tertinggi untuk memberi jarak (headroom)
            const maxDataValue = Math.max(...planData, ...actualData, 0);
            const suggestedMaxCount = maxDataValue > 0 ? Math.ceil(maxDataValue * 1.3) : 10;

            // Warna untuk mode terang dan gelap
            const gridColor = this.isDarkMode ? '#374151' : '#E5E7EB';
            const textColor = this.isDarkMode ? '#D1D5DB' : '#4B5563';
            const tooltipBgColor = this.isDarkMode ? '#1F2937' : '#FFFFFF';
            const tooltipTextColor = this.isDarkMode ? '#F9FAFB' : '#1F2937';
            const tooltipBorderColor = this.isDarkMode ? '#4B5563' : '#E5E7EB';

            this.monitoringChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Plan Count',
                            data: planData,
                            backgroundColor: 'rgba(59, 130, 246, 1)',
                            // UBAH DISINI: Hapus border dengan setting ke 'transparent'
                            borderColor: 'transparent',
                            borderWidth: 1, // Ini bisa tetap 1 atau 0, karena borderColor sudah transparent
                            borderRadius: 50,
                            borderSkipped: 'bottom',
                            categoryPercentage: 0.3,
                            barPercentage: 0.98,
                            order: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Actual Count',
                            data: actualData,
                            backgroundColor: 'rgba(34, 197, 94, 1)',
                            // UBAH DISINI: Hapus border dengan setting ke 'transparent'
                            borderColor: 'transparent',
                            borderWidth: 1, // Ini bisa tetap 1 atau 0, karena borderColor sudah transparent
                            borderRadius: 50,
                            borderSkipped: 'bottom',
                            categoryPercentage: 0.3,
                            barPercentage: 0.98,
                            order: 3,
                            yAxisID: 'y'
                        },
                        {
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
                            }
                        }
                    ]
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
                                color: textColor,
                                font: {
                                    size: 12
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: tooltipBgColor,
                            titleColor: tooltipTextColor,
                            bodyColor: tooltipTextColor,
                            borderColor: tooltipBorderColor,
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (context.dataset.yAxisID === 'y1') {
                                            label += context.parsed.y + '%';
                                        } else {
                                            label += context.parsed.y;
                                        }
                                    }
                                    return label;
                                }
                            }
                        },
                        datalabels: {
                            color: textColor,
                            anchor: 'end',
                            align: 'top',
                            offset: 4,
                            font: {
                                weight: 'bold',
                                size: 11
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
                                maxTicksLimit: 6,
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
                                maxTicksLimit: 6,
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return value + '%';
                                }
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
        // --- API: ACTIVITY LOG ---
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
            let message = log.activity_code === 'UPLOAD' ? `<strong>${log.user_name || 'System'}</strong> uploaded a new document.` : `<strong>${log.user_name || 'System'}</strong> performed action: <strong>${log.activity_code}</strong>.`;
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
            return `<div class="py-3 px-2 flex space-x-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"><div class="flex-shrink-0 pt-1"><i class="fa-solid ${logInfo.icon} fa-lg ${logInfo.color} w-5 text-center"></i></div><div class="flex-1 min-w-0"><div class="flex justify-between items-start"><p class="text-sm text-gray-800 dark:text-gray-200">${message}</p><p class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-3 whitespace-nowrap">${date.toLocaleString('id-ID')}</p></div><p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${timeAgo(date)}</p>${metaDetails}</div></div>`;
        }

        // --- SETUP COMPONENTS ---
        initSelect2() {
            const self = this;

            const setup = (id, url, stateKey) => {
                $(`#${id}`).select2({
                    dropdownParent: $(`#${id}`).parent(),
                    width: '100%',
                    placeholder: 'Select...',
                    allowClear: true,
                    ajax: {
                        url: url,
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
                            let more = data.pagination ? data.pagination.more : (data.total_count ? (params.page * 10) < data.total_count : false);
                            return {
                                results: data.results || [],
                                pagination: {
                                    more: more
                                }
                            };
                        }
                    }
                }).on('change', function() {
                    const d = $(this).select2('data')[0];
                    if (d && d.id) {
                        const arr = self[stateKey];
                        if (!arr.find(x => x.id === d.id)) {
                            arr.push({
                                id: d.id,
                                text: d.text
                            });
                            self.renderFilterPills();
                        }
                        $(this).val(null).trigger('change.select2');
                    }
                });
            };

            setup('customer_input', "{{ route('dashboard.getCustomers') }}", 'selectedCustomers');
            setup('model_input', "{{ route('dashboard.getModels') }}", 'selectedModels');
            setup('part_group_multi_input', "{{ route('dashboard.getPartGroup') }}", 'selectedPartGroup');

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
                        q: params.term,
                        page: params.page || 1
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
                span.innerHTML = `<span class="font-normal text-gray-500 dark:text-gray-400 mr-1">${type}:</span><span>${item.text}</span><button type="button" class="filter-pill-remove" data-id="${item.id}"><i class="fa-solid fa-times fa-xs"></i></button>`;

                span.querySelector('button').addEventListener('click', () => {
                    const arr = this[stateKey];
                    const idx = arr.findIndex(x => x.id === item.id);
                    if (idx > -1) {
                        arr.splice(idx, 1);
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

                        // Jika tema berubah, update chart
                        if (newDarkMode !== this.isDarkMode) {
                            this.isDarkMode = newDarkMode;

                            // Update date range picker
                            if (this.dateRangeInstance && this.dateRangeInstance.ui) {
                                this.isDarkMode ? this.dateRangeInstance.ui.classList.add('dark') : this.dateRangeInstance.ui.classList.remove('dark');
                            }

                            // Re-render chart dengan data terakhir jika ada
                            if (this.currentChartData && this.currentChartData.length > 0) {
                                this.renderChart(this.currentChartData);
                            } else if (this.monitoringChart) {
                                // Jika chart sudah ada tapi tidak ada data tersimpan, render ulang dengan data kosong
                                this.renderChart([]);
                            }
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
                $('#project_status').val('ALL').trigger('change');

                const now = new Date();
                this.dateStart = this.formatDateJS(new Date(now.getFullYear(), now.getMonth(), 1));
                this.dateEnd = this.formatDateJS(new Date(now.getFullYear(), now.getMonth() + 1, 0));

                if (this.dateRangeInstance) {
                    this.dateRangeInstance.setDateRange(this.dateStart, this.dateEnd);
                }
                this.renderFilterPills();
                this.loadMonitoringChart();
            });
        }
    }
</script>
@endsection