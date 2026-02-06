@extends('layouts.app')
@section('title', 'Download Detail - File Manager')
@section('header-title', 'File Manager - Download Detail')

@section('content')
<nav class="flex px-5 py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
        <li class="inline-flex items-center">
            <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600">
                Monitoring
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('file-manager.export') }}" class="text-sm font-semibold px-2.5 py-0.5 hover:text-blue-600 rounded">
                    Download Files
                </a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded">
                    Download Detail
                </span>
            </div>
        </li>
    </ol>
</nav>

<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen relative"
    x-data="exportDetail()"
    x-init="init()"
    @mousemove.window="onPan($event)"
    @mouseup.window="endPan()"
    @mouseleave.window="endPan()">

    <x-files.download-zip-modal />

    {{-- Loading Overlay for Revision Change --}}
    <div x-show="isLoadingRevision" x-transition x-cloak
        class="fixed inset-0 bg-gray-900/40 backdrop-blur-[2px] z-[2000] flex items-center justify-center">
        <div class="flex flex-col items-center gap-4 px-8 py-6 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="relative w-12 h-12">
                <div class="absolute inset-0 border-4 border-blue-100 dark:border-blue-900/30 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-base font-bold text-gray-900 dark:text-gray-100">Switching Revision</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Updating package data...</span>
            </div>
        </div>
    </div>

    <!-- ================= MAIN LAYOUT ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
        
        <!-- ================= LEFT COLUMN ================= -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- ===== Package Info Card ===== -->
            <div x-ref="metaCard"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 md:justify-between">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <i class="fa-solid fa-box-archive mr-2 text-blue-600"></i>
                            Package Info
                        </h2>
                        <a href="{{ route('file-manager.export') }}"
                            class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back
                        </a>
                    </div>
                </div>

                <div class="px-4 py-4 space-y-3">
                    <p class="text-sm text-gray-700 dark:text-gray-200" x-text="metaLine()"></p>

                        <template x-if="pkg.metadata?.linked_partners?.length > 0">
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Linked Partners</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="partner in pkg.metadata.linked_partners" :key="partner">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800">
                                            <i class="fa-solid fa-link mr-1.5 text-[9px] opacity-70"></i>
                                            <span x-text="partner"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Revision Selector --}}
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-code-branch text-[10px] text-gray-400"></i>
                            Revision History
                        </label>
                        <div class="relative">
                            <select id="revision-selector" x-ref="revisionSelector" :disabled="isLoadingRevision"
                                class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 transition-all shadow-sm">
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions / Stats footer --}}
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Content</span>
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-200 mt-0.5">
                            <span x-text="getTotalPackageStats().count + ' Files'"></span>
                            <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                            <span x-text="formatBytes(getTotalPackageStats().size)"></span>
                        </div>
                    </div>

                    <button @click="downloadPackage()"
                        :disabled="isLoadingPackage"
                        class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-blue-500 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-400 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid" :class="isLoadingPackage ? 'fa-spinner fa-spin' : 'fa-cloud-arrow-down'"></i>
                        <span x-text="isLoadingPackage ? 'Preparing...' : 'Download All'"></span>
                    </button>
                </div>
            </div>

            <!-- ===== File Group Lists ===== -->
            <div class="space-y-4">
                <x-files.file-group-list title="2D Drawings" icon="fa-drafting-compass" category="2d" allow-download="true" />
                <x-files.file-group-list title="3D Models" icon="fa-cubes" category="3d" allow-download="true" />
                <x-files.file-group-list title="ECN / Documents" icon="fa-file-lines" category="ecn" allow-download="true" />
            </div>

            <!-- ===== Activity Log (Optional, if needed) ===== -->
            <template x-if="pkg.activityLogs?.length > 0">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fa-solid fa-clock-rotate-left mr-2 text-gray-500 dark:text-gray-400"></i>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Activity Log</span>
                        </div>
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full"
                            x-text="`${pkg.activityLogs?.length || 0} events`"></span>
                    </div>
                    <div class="p-2 max-h-96 overflow-y-auto">
                        <template x-for="log in pkg.activityLogs.slice(0, 5)" :key="log.id">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 capitalize" x-text="log.action.replace('_', ' ')"></span>
                                    <span class="text-[9px] text-gray-400" x-text="log.time"></span>
                                </div>
                                <p class="text-[11px] text-gray-600 dark:text-gray-300">
                                    <span class="font-medium" x-text="log.user"></span> executed this action.
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

        </div>
        <!-- ================= /LEFT COLUMN ================= -->

        <!-- ================= RIGHT COLUMN (Preview) ================= -->
        <div class="lg:col-span-8">
            <div x-ref="refMainContainer" 
                class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col" style="min-height: 600px;">
                
                <template x-if="selectedFile">
                    <div class="flex-1 flex flex-col">
                        @include('components.files.file-viewer')
                    </div>
                </template>
                
                <template x-if="!selectedFile">
                    <div class="flex-1 flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
                        <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
                    </div>
                </template>
            </div>
        </div>
        <!-- ================= /RIGHT COLUMN ================= -->
        
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/file-viewer-alpine.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/utif@2.0.1/UTIF.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    if (window['pdfjsLib']) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    }
</script>
<script async src="https://unpkg.com/es-module-shims@1.10.0/dist/es-module-shims.js"></script>
<script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/",
            "three-mesh-bvh": "https://unpkg.com/three-mesh-bvh@0.7.6/build/index.module.js"
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/occt-import-js@0.0.23/dist/occt-import-js.js"></script>

<script>
    function detectTheme() {
        const isDark = document.documentElement.classList.contains('dark') || 
                      (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (isDark) {
            return {
                bg: '#1e293b', fg: '#f8fafc', border: 'rgba(51, 65, 85, 1)', progress: 'rgba(56, 189, 248, .8)',
                icon: { success: '#4ade80', error: '#f87171', warning: '#fbbf24', info: '#38bdf8' }
            };
        }
        return {
            bg: '#ffffff', fg: '#0f172a', border: 'rgba(226, 232, 240, 1)', progress: 'rgba(15,23,42,.8)',
            icon: { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' }
        };
    }

    const BaseToast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 2600, timerProgressBar: true,
        showClass: { popup: 'swal2-animate-toast-in' },
        hideClass: { popup: 'swal2-animate-toast-out' },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function renderToast({ icon = 'success', title = 'Success', text = '' } = {}) {
        const t = detectTheme();
        BaseToast.fire({
            icon, title, text,
            iconColor: t.icon[icon] || t.icon.success,
            background: t.bg, color: t.fg,
            customClass: { popup: 'swal2-toast border', title: '', timerProgressBar: '' },
            didOpen: (toast) => {
                const bar = toast.querySelector('.swal2-timer-progress-bar'); if (bar) bar.style.background = t.progress;
                const popup = toast.querySelector('.swal2-popup'); if (popup) popup.style.borderColor = t.border;
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    function toastSuccess(title = 'Success', text = '') { renderToast({ icon: 'success', title, text }); }
    function toastError(title = 'Error', text = '') { renderToast({ icon: 'error', title, text }); }
    function toastInfo(title = 'Information', text = '') { renderToast({ icon: 'info', title, text }); }

    function exportDetail() {
        // Mixin from file-viewer-alpine.js
        const viewer = fileViewerComponent({
            pkg: @js($detail),
            stampFormat: @js($detail['stamp_formats'] ?? []),
            showStampConfig: true,
            enableMasking: true,
            userDeptCode: @js($userDeptCode ?? null),
            userName: @js($userName ?? null),
            isEngineering: @js($isEngineering ?? false),
        });

        return {
            ...viewer,
            
            isLoadingRevision: false,
            isLoadingPackage: false, // Loading for Download All
            isDownloadingFile: null, // Track which file is being downloaded
            openSections: ['2d'],
            isEngineering: @js($isEngineering ?? false),

            init() {
                if (viewer.init) viewer.init.call(this);
                
                // Wait for DOM and libraries to be ready
                setTimeout(() => {
                    this.initRevisionSelector();
                }, 100);
            },

            initRevisionSelector() {
                const $sel = $(this.$refs.revisionSelector);
                // Use revision_no for stable matching, as IDs are encrypted and change per request
                const currentRevNo = this.pkg?.revision_no; 
                const history = this.pkg?.revisionHistory || [];

                // If no history data, don't initialize yet
                if (history.length === 0) {
                    console.warn('[ExportDetail] No revision history available');
                    return;
                }
                
                // Find the history item that matches currentRevNo
                const matchedItem = history.find(h => h.revision_no == currentRevNo);
                const currentId = matchedItem ? matchedItem.id : null;

                try {
                $sel.select2({
                    width: '100%',
                    data: history.map((h, idx) => ({
                        id: h.id,
                        text: h.revision,
                        revision: h.revision,
                        isLatest: idx === 0,
                        isObsolete: h.is_obsolete
                    })),
                    templateResult: function(data) {
                        if (!data.id) return data.text;
                        const isCur = data.id == currentId;
                        let badge = '';
                        if (data.isLatest) {
                            badge = '<span class="text-[10px] px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Latest</span>';
                        } else if (data.isObsolete) {
                            badge = '<span class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Obsolete</span>';
                        }
                        return $(`<div class="flex items-center justify-between py-1">
                            <span class="font-semibold text-sm ${isCur ? 'text-blue-600' : ''}">${data.text}</span>
                            ${badge}
                        </div>`);
                    },
                    templateSelection: data => data.text
                });

                    // Try to set the current ID
                    $sel.val(currentId).trigger('change');
                    
                    // If value is still null, select the first item (latest revision)
                    if (!$sel.val() && history.length > 0) {
                        $sel.val(history[0].id).trigger('change');
                    }
                    
                    $sel.off('select2:select').on('select2:select', (e) => this.switchRevision(e.params.data.id));
                } catch (error) {
                    console.error('[ExportDetail] Error initializing Select2:', error);
                }
            },

            async switchRevision(id) {
                const targetRev = this.pkg?.revisionHistory?.find(r => r.id === id);
                if ((targetRev && targetRev.revision_no == this.pkg?.revision_no) || this.isLoadingRevision) return;
                
                this.isLoadingRevision = true;
                const url = `{{ route('api.export.revision-detail', ['id' => ':id']) }}`.replace(':id', id);
                
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) {
                        throw new Error(`Failed to fetch revision data: ${res.status} ${res.statusText}`);
                    }
                    
                    const response = await res.json();
                    
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to load revision');
                    }
                    
                    // Reset viewer state
                    this.selectedFile = null;
                    this.pkg = response.pkg;
                    
                    // Reinitialize revision selector with new data
                    const $sel = $(this.$refs.revisionSelector);
                    if ($sel.data('select2')) {
                        $sel.select2('destroy');
                    }
                    $sel.empty();
                    
                    this.$nextTick(() => {
                        this.initRevisionSelector(); // Reinitialize with new data
                    });

                    this.isLoadingRevision = false;
                    
                } catch (e) {
                    console.error(e);
                    Swal.fire('Error', 'Failed to load revision data', 'error');
                    this.isLoadingRevision = false;
                }
            },

            toggleSection(id) {
                if (this.openSections.includes(id)) {
                    this.openSections = this.openSections.filter(s => s !== id);
                } else {
                    this.openSections.push(id);
                }
            },

            selectFile(file) {
                this.selectedFile = file;
            },

            downloadFile(file) {
                if (!file.url) return;
                
                // Set loading state
                this.isDownloadingFile = file.name;
                
                const link = document.createElement('a');
                link.href = file.url;
                link.download = file.name;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Remove loading state after a short delay
                setTimeout(() => {
                    this.isDownloadingFile = null;
                }, 1000);
            },

            metaLine() {
                const m = this.pkg?.metadata || {};
                return [
                    m.customer, m.model, m.part_no, m.part_group,
                    m.doc_type, m.category, m.ecn_no, m.revision
                ].filter(v => v && String(v).trim().length > 0).join(' - ');
            },

            revisionBadgeText() {
                return 'Rev ' + (this.pkg?.metadata?.revision || '0.0');
            },

            toggleSection(category) {
                const index = this.openSections.indexOf(category);
                if (index > -1) {
                    this.openSections.splice(index, 1);
                } else {
                    this.openSections.push(category);
                }
            },

            selectFile(file) {
                if (viewer.selectFile) {
                    viewer.selectFile.call(this, file);
                } else {
                    this.selectedFile = file;
                }
            },

            async downloadFile(file) {
                if (!file.file_id && !file.url) return;
                
                this.isDownloadingFile = file.name;
                
                const url = file.file_id 
                    ? `{{ route('file-manager.export.download-file', ['file_id' => ':id']) }}`.replace(':id', file.file_id)
                    : file.url;
                
                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Download failed');
                    
                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    
                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.setAttribute('download', file.name);
                    document.body.appendChild(link);
                    link.click();
                    
                    // Cleanup
                    document.body.removeChild(link);
                    setTimeout(() => window.URL.revokeObjectURL(downloadUrl), 100);
                } catch (err) {
                    console.error('Download error:', err);
                    toastError('Download Failed', 'Could not prepare the file for download.');
                } finally {
                    this.isDownloadingFile = null;
                }
            },

            formatBytes(bytes, decimals = 2) {
                if (!bytes) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            },

            getTotalPackageStats() {
                const files = this.pkg?.files || {};
                let count = 0;
                let size = 0;
                
                // Files are grouped by category (2d, 3d, ecn)
                Object.values(files).forEach(categoryFiles => {
                    if (Array.isArray(categoryFiles)) {
                        count += categoryFiles.length;
                        size += categoryFiles.reduce((acc, f) => acc + (parseInt(f.size) || 0), 0);
                    }
                });
                
                return { count, size };
            },

            async downloadPackage() {
                const id = '{{ $exportId }}';
                if (!id) return;
                
                const stats = this.getTotalPackageStats();
                const url = `{{ route('export.prepare-zip', ['revision_id' => ':id']) }}`.replace(':id', id);
                
                this.$dispatch('open-download-zip', {
                    count: stats.count,
                    size: this.formatBytes(stats.size),
                    url: url
                });
            },


        };
    }
</script>
@endpush
