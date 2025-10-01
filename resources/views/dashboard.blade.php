@extends('layouts.app')
@section('title', 'Dashboard - PROMISE')
@section('header-title', 'Dashboard')
@section('content')

{{-- Menambahkan x-data untuk mengelola state grafik --}}
<div x-data="dashboardCharts()" x-init="initCharts()">

    {{-- Kartu Statistik --}}
    <div class="sm:flex sm:items-center sm:gap-x-24">
        {{-- Bagian Judul --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analys File Management</p>
        </div>

        {{-- Kartu Statistik (dengan gap) --}}
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:mt-0">

            {{-- Total Document --}}
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

            {{-- Upload --}}
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

            {{-- Download --}}
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

            {{-- Document Types (Diperbarui dengan chart) --}}
            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                <div class="flex items-center">
                    <div class="bg-purple-100 dark:bg-purple-900/50 text-purple-500 dark:text-purple-400 rounded-lg p-2 mr-3 flex items-center justify-center h-9 w-9">
                        <i class="fa-solid fa-tags fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Document Types</h3>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-100">6</p>
                    </div>
                </div>
                <div class="mt-2 h-8 w-full"><canvas id="docTypesChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mt-8 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center">
            <i class="fa-solid fa-filter mr-2 text-gray-500"></i>
            Filter Data
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-6 items-end">
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
                    <select id="doc_group" name="doc_group" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
                        <option>ALL</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
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
                <label for="customer_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer - Model</label>
                <div class="relative mt-1">
                    <select id="customer_model" name="customer_model" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
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
            <div>
                <label for="revision_history" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision History</label>
                <div class="relative mt-1">
                    <select id="revision_history" name="revision_history" class="appearance-none block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 py-2 pl-3 pr-10 text-base focus:outline-none focus:ring-0 sm:text-sm">
                        <option>All</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik --}}
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

    {{-- Newsfeed --}}
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

{{-- Load Chart.js dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    function dashboardCharts() {
        return {
            planVsActualChart: null,
            uploadDownloadChart: null,
            totalDocsChart: null,
            uploadsChart: null,
            downloadsChart: null,
            docTypesChart: null, // State untuk chart baru
            activeUsersChart: null,

            initCharts() {
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js is not loaded!');
                    return;
                }
                setTimeout(() => {
                    this.drawCharts();
                }, 100);
            },

            drawCharts() {
                const textColor = document.documentElement.classList.contains('dark') ? '#d1d5db' : '#6b7280';
                const gridColor = document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

                // --- Sparkline Charts ---
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

                // Total Documents Chart
                const ctxDocs = document.getElementById('totalDocsChart');
                if (ctxDocs) {
                    this.totalDocsChart = new Chart(ctxDocs, {
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
                }

                // Uploads Chart
                const ctxUploads = document.getElementById('uploadsChart');
                if (ctxUploads) {
                    this.uploadsChart = new Chart(ctxUploads, {
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
                }

                // Downloads Chart
                const ctxDownloads = document.getElementById('downloadsChart');
                if (ctxDownloads) {
                    this.downloadsChart = new Chart(ctxDownloads, {
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
                }

                // Document Types Chart
                const ctxDocTypes = document.getElementById('docTypesChart');
                if (ctxDocTypes) {
                    this.docTypesChart = new Chart(ctxDocTypes, {
                        type: 'line',
                        data: {
                            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                            datasets: [{
                                label: 'Document Types',
                                data: [4, 4, 5, 5, 6, 6, 6],
                                borderColor: 'rgba(168, 85, 247, 1)',
                                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                                fill: true
                            }]
                        },
                        options: sparklineOptions
                    });
                }

                // --- Chart 1: Plan vs Actual ---
                const ctx1 = document.getElementById('planVsActualChart');
                if (ctx1) {
                    this.planVsActualChart = new Chart(ctx1, {
                        data: {
                            labels: ['MMKI - 5H45', 'MMKI - 5J45', 'MMKI - 4L45W', 'HPM - TG4R', 'HPM - 3K6A', 'SUZUKI - YHA', 'TOYOTA - D03B'],
                            datasets: [{
                                type: 'bar',
                                label: 'Actual (docs)',
                                data: [60, 88, 90, 115, 120, 148, 150],
                                backgroundColor: 'rgba(22, 163, 74, 0.8)',
                                borderColor: 'rgba(22, 163, 74, 1)',
                                yAxisID: 'y',
                                order: 2
                            }, {
                                type: 'bar',
                                label: 'Plan (docs)',
                                data: [80, 110, 140, 135, 170, 205, 205],
                                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                                borderColor: 'rgba(37, 99, 235, 1)',
                                yAxisID: 'y',
                                order: 2
                            }, {
                                type: 'line',
                                label: 'Progress %',
                                data: [75, 80, 64, 85, 71, 72, 73],
                                backgroundColor: 'rgba(249, 115, 22, 0.2)',
                                borderColor: 'rgba(249, 115, 22, 1)',
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
                        }
                    });
                }

                // --- Chart 2: Upload vs Download ---
                const ctx2 = document.getElementById('uploadDownloadChart');
                if (ctx2) {
                    this.uploadDownloadChart = new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: ['W1', 'W2', 'W3', 'W4'],
                            datasets: [{
                                label: 'Download',
                                data: [100, 130, 155, 145],
                                borderColor: 'rgba(22, 163, 74, 1)',
                                backgroundColor: 'rgba(22, 163, 74, 0.1)',
                                tension: 0.3,
                                borderWidth: 2,
                                fill: true,
                                pointBackgroundColor: 'rgba(22, 163, 74, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgba(22, 163, 74, 1)'
                            }, {
                                label: 'Upload',
                                data: [120, 145, 165, 158],
                                borderColor: 'rgba(37, 99, 235, 1)',
                                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                tension: 0.3,
                                borderWidth: 2,
                                fill: true,
                                pointBackgroundColor: 'rgba(37, 99, 235, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgba(37, 99, 235, 1)'
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
                                    suggestedMax: 180,
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
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: 'rgba(255, 255, 255, 0.1)',
                                    borderWidth: 1
                                }
                            }
                        }
                    });
                }
            }
        }
    }
</script>
@endsection