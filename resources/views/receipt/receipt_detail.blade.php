@extends('layouts.app')
@section('title', 'Receipt Detail - PROMISE')
@section('header-title', 'Receipt - Detail')

@section('content')
{{-- Breadcrumb matching Standard Style --}}
<nav class="flex px-5 py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
        <li class="inline-flex items-center">
            <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600">
                Monitoring
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('receipt') }}" class="text-sm font-semibold text-gray-500 px-2.5 py-0.5 hover:text-blue-600 rounded">
                    Receipt Registry
                </a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded">
                    Package Manifest
                </span>
            </div>
        </li>
    </ol>
</nav>

<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen relative"
    x-data="receiptDetail({
        revisionId: @js($receiptId ?? null),
        userDeptCode: @js($userDeptCode ?? null)
    })"
    x-init="init()"
    x-cloak>

    {{-- Loading Overlay --}}
    <div x-show="isLoadingRevision" x-transition
        class="absolute inset-0 bg-gray-100/75 dark:bg-gray-900/75 z-50 flex items-center justify-center rounded-lg backdrop-blur-sm">
        <div class="flex items-center gap-3 px-6 py-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="w-6 h-6 border-4 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-sm font-bold text-gray-700 dark:text-gray-300 tracking-wide uppercase">Synchronizing Data...</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
        
        {{-- Left Column: Package Info (Standardized) --}}
        <div class="lg:col-span-4 space-y-6 sticky top-8">
            <div x-ref="metaCard" class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
                            <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
                            Package Metadata
                        </h2>
                        <a href="{{ route('receipt') }}"
                            class="inline-flex items-center gap-2 justify-center px-4 py-1.5 border border-gray-300 text-xs font-bold uppercase tracking-wider rounded-md text-gray-600 bg-white hover:bg-gray-50 transition-all dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                             <div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-sm">
                                <span x-text="pkg.metadata?.revision || 'Rev —'"></span>
                             </div>
                             <div x-show="pkg.metadata?.ecn_no" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-sm">
                                <span x-text="pkg.metadata?.ecn_no"></span>
                             </div>
                        </div>
                        
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-gray-900 dark:text-white" x-text="pkg.metadata?.part_no || 'PART_NUMBER'"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider" x-text="(pkg.metadata?.customer || '') + ' • ' + (pkg.metadata?.model || '')"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 border-t border-gray-100 dark:border-gray-700 pt-5">
                        <div class="flex justify-between items-center group">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-blue-500 transition-colors">Doc Type</label>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="pkg.metadata?.doc_type || '—'"></span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-blue-500 transition-colors">Category</label>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="pkg.metadata?.category || '—'"></span>
                        </div>
                         <div class="flex justify-between items-center group">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-blue-500 transition-colors">Part Group</label>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="pkg.metadata?.part_group || '—'"></span>
                        </div>
                    </div>

                    <div x-show="pkg.metadata?.expired_at" class="bg-amber-50 dark:bg-amber-900/10 p-3 rounded-lg border border-amber-100 dark:border-amber-900/20 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white dark:bg-amber-900 flex items-center justify-center text-amber-600 border border-amber-50">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-amber-500 uppercase tracking-widest">Access Expiry</span>
                            <span class="text-[11px] font-black text-amber-700 dark:text-amber-400" x-text="formatDate(pkg.metadata?.expired_at)"></span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700">
                    <button @click="downloadPackage()"
                        class="w-full group flex items-center justify-center gap-3 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-[0.15em] rounded-lg transition-all active:scale-95">
                        <i class="fa-solid fa-cloud-arrow-down text-sm transition-transform group-hover:-translate-y-0.5"></i>
                        Download All
                    </button>
                    <div class="flex justify-between items-center mt-3 px-1">
                         <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Original Size</span>
                            <div class="group relative inline-block">
                                <i class="fa-solid fa-circle-info text-[9px] text-gray-400 cursor-help hover:text-blue-500 transition-colors"></i>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-48 p-2 bg-gray-900 text-white text-[9px] rounded-lg shadow-xl z-50 leading-relaxed font-normal normal-case tracking-normal">
                                    Downloaded files may be larger due to stamp processing.
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                         </div>
                         <div class="text-xs font-bold text-gray-700 dark:text-gray-200">
                            <span x-text="getTotalFiles() + ' Items'"></span>
                            <span class="mx-1 opacity-20">/</span>
                            <span x-text="getTotalSize()"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: File Grid Manifest (Original Layout Restored) --}}
        <div class="lg:col-span-8 space-y-6">
            
            {{-- Manifest Header --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-400">
                        <i class="fa-solid fa-list-check text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Object Manifest Details</h2>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Package Contents Summary</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <template x-for="section in [
                    {key: '2d', title: '2D Drawings', icon: 'fa-drafting-compass', color: 'blue'},
                    {key: '3d', title: '3D Models', icon: 'fa-cubes', color: 'blue'},
                    {key: 'ecn', title: 'ECN / Documents', icon: 'fa-file-lines', color: 'amber'}
                ]" :key="section.key">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden group/sec">
                        <div @click="toggleSection(section.key)" 
                            class="px-6 py-5 flex items-center justify-between bg-white dark:bg-gray-800 group-hover/sec:bg-gray-50 dark:group-hover/sec:bg-gray-700/30 transition-colors cursor-pointer select-none">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white transition-transform"
                                    :class="`bg-${section.color === 'amber' ? 'yellow' : section.color}-500`"
                                >
                                    <i class="fa-solid text-lg" :class="section.icon"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight" x-text="section.title"></h4>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 font-bold">
                                <span class="text-xs text-gray-700 dark:text-gray-300" x-text="`${(pkg.files?.[section.key]?.length || 0)} Items`"></span>
                                <div class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center group-hover/sec:bg-gray-100 dark:group-hover/sec:bg-gray-600 transition-colors">
                                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300" :class="{'rotate-180': openSections.includes(section.key)}"></i>
                                </div>
                            </div>
                        </div>

                        <div x-show="openSections.includes(section.key)" x-collapse>
                            <div class="px-6 pb-6 pt-2">
                                <div class="bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-100 dark:border-gray-700/50 overflow-hidden min-h-[100px]">
                                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50 max-h-[400px] overflow-y-auto custom-scrollbar">
                                        <template x-for="(file, index) in (pkg.files?.[section.key] || [])" :key="section.key + '-' + index">
                                            <div class="flex items-center justify-between px-6 py-4 hover:bg-white dark:hover:bg-gray-800/50 transition-all group/item">
                                                <div class="flex items-center gap-5 flex-1 min-w-0">
                                                    <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center transition-colors">
                                                        <i class="fa-solid text-xs" :class="getFileIcon(file.name)"></i>
                                                    </div>
                                                     <div class="min-w-0 pr-4">
                                                         <div class="marquee-wrapper">
                                                            <p class="marquee-content text-xs font-black text-gray-800 dark:text-gray-100 group-hover/item:text-blue-600 transition-colors" x-text="file.name"></p>
                                                         </div>
                                                         <div class="flex items-center gap-2 mt-1">
                                                         <div class="flex items-center mt-0.5 text-[9px] text-gray-400 dark:text-gray-500 uppercase tracking-tight">
                                                             <span class="font-bold" x-text="formatBytes(file.size)"></span>
                                                             <span class="font-normal ml-1">(Original)</span>
                                                         </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- Preview Button (Optional, since we removed main viewer) --}}
                                                {{-- Preview/Download Button Removed as per request --}}
                                                {{-- <template x-if="file.url">
                                                    <a :href="file.url" target="_blank"
                                                        class="p-2 rounded-full border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                                        <i class="fa-solid fa-eye text-xs"></i>
                                                    </a>
                                                </template> --}}
                                            </div>
                                        </template>
                                        <template x-if="!(pkg.files?.[section.key] || []).length">
                                            <div class="p-10 flex flex-col items-center justify-center text-center opacity-40">
                                                <i class="fa-solid fa-folder-open text-3xl mb-3"></i>
                                                <p class="text-[10px] font-bold uppercase tracking-[0.2em]">No Synchronized Assets Found</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </template>
            </div>
        </div>
    </div>
    
    {{-- Shared Download Zip Modal --}}
    <x-files.download-zip-modal />
</div>
@endsection

@push('style')
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { @apply bg-gray-200 dark:bg-gray-700 rounded-full; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { @apply bg-gray-300 dark:bg-gray-600; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const notify = (icon, title) => Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
        }).fire({ icon, title });

        function receiptDetail(config = {}) {
            return {
                revisionId: config.revisionId || null,
                exportId: config.revisionId || null, 
                pkg: { files: {}, metadata: {} }, 
                isLoadingRevision: false,
                openSections: [], // Closed by default
                
                formatBytes(bytes) {
                    if (!+bytes) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
                },
                formatDate(dateString) {
                    if (!dateString) return null;
                    return new Date(dateString).toLocaleDateString('en-GB', {
                        day: 'numeric', month: 'short', year: 'numeric'
                    });
                },
                getFileIcon(filename) {
                    const ext = filename?.split('.').pop().toLowerCase();
                    const map = {
                        'pdf': 'fa-file-pdf text-red-500',
                        'xls': 'fa-file-excel text-green-600', 'xlsx': 'fa-file-excel text-green-600',
                        'doc': 'fa-file-word text-blue-500', 'docx': 'fa-file-word text-blue-500',
                        'zip': 'fa-file-zipper text-gray-500', 'rar': 'fa-file-zipper text-gray-500',
                        'dwg': 'fa-file-pen text-blue-500', 'step': 'fa-cube text-blue-500', 'stp': 'fa-cube text-blue-500'
                    };
                    return map[ext] || 'fa-file text-gray-400';
                },
                
                // Helper for total stats
                getTotalFiles() {
                    return Object.values(this.pkg.files).reduce((acc, files) => acc + (files?.length || 0), 0);
                },
                getTotalSize() {
                    const total = Object.values(this.pkg.files).reduce((acc, files) => {
                        return acc + (files?.reduce((fAcc, f) => fAcc + (Number(f.size) || 0), 0) || 0);
                    }, 0);
                    return this.formatBytes(total);
                },

                toggleSection(key) {
                    if (this.openSections.includes(key)) {
                        this.openSections = this.openSections.filter(k => k !== key);
                    } else {
                        this.openSections.push(key);
                    }
                },

                async init() {
                    if (this.revisionId) await this.fetchData(this.revisionId);
                },
                
                async fetchData(id) {
                    this.isLoadingRevision = true;
                    try {
                        const res = await fetch(`/api/receipts/revision-detail/${id.toString().replace(/=/g, '-')}`); 
                        const data = await res.json();
                        if (data.success) {
                            this.pkg = data.detail || { files: {}, metadata: {} }; 
                            this.exportId = data.exportId;
                        } else throw new Error(data.message);
                    } catch (e) {
                        notify('error', e.message || 'Error loading data');
                    } finally {
                        this.isLoadingRevision = false;
                    }
                },

                downloadPackage() {
                    if (!this.exportId) return;
                    
                    // Dispatch event to open the shared modal
                    window.dispatchEvent(new CustomEvent('open-download-zip', {
                        detail: {
                            count: this.getTotalFiles(),
                            size: this.getTotalSize(),
                            url: `/api/receipts/prepare-zip/${this.exportId.toString().replace(/=/g, '-')}`
                        }
                    }));
                }
            };
        }
    </script>
@endpush