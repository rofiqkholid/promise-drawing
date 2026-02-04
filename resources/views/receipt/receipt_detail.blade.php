@extends('layouts.app')
@section('title', 'Receipt Detail - Download Receipt')
@section('header-title', 'Receipt - Download Detail')

@section('content')

<div class="min-h-screen bg-[#F8FAFC] dark:bg-[#0B0F1A] font-sans antialiased text-slate-900 dark:text-slate-100 p-4 md:p-8"
    x-data="receiptDetail({
        revisionId: @js($receiptId ?? null),
        userDeptCode: @js($userDeptCode ?? null)
    })" 
    x-init="init()">

    {{-- Glass Loading Overlay --}}
    <div x-show="isLoadingRevision" x-transition.opacity
        class="fixed inset-0 bg-white/40 dark:bg-black/60 backdrop-blur-[2px] z-[100] flex flex-col items-center justify-center"
        style="display: none;">
        <div class="relative flex items-center justify-center mb-6">
            <div class="absolute animate-ping inline-flex h-16 w-16 rounded-full bg-indigo-500 opacity-20"></div>
            <div class="w-12 h-12 border-[3px] border-indigo-600 border-t-transparent rounded-full animate-spin shadow-lg shadow-indigo-500/20"></div>
        </div>
        <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 tracking-[0.2em] uppercase animate-pulse">Synchronizing Data</p>
    </div>

    <div class="max-w-7xl mx-auto space-y-8">
        
        {{-- Navigation & Breadcrumbs --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <nav class="flex items-center gap-2 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">
                    <a href="{{ route('receipt') }}" class="hover:text-indigo-500 transition-colors">Receipt</a>
                    <i class="fa-solid fa-chevron-right text-[8px] opacity-30"></i>
                    <span class="text-indigo-600 dark:text-indigo-400">Package Details</span>
                </nav>
                <div class="flex items-center gap-4">
                    <div class="h-12 w-1.5 bg-indigo-600 rounded-full hidden md:block"></div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-none">
                            Drawing Package <span class="text-indigo-600" x-text="pkg.metadata?.ecn_no || '—'"></span>
                        </h2>
                        <p class="mt-2 text-sm font-medium text-slate-500 dark:text-slate-400 max-w-lg leading-relaxed">
                            Secured access to technical drawings and engineering change notifications.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex-shrink-0">
                <a href="{{ route('receipt') }}"
                    class="group inline-flex items-center gap-2.5 px-5 py-2.5 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 text-slate-600 dark:text-slate-300 text-xs font-bold uppercase tracking-wider rounded-xl hover:border-indigo-500/50 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                    <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Return to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
            
            {{-- Main Content Column --}}
            <div class="lg:col-span-3 space-y-8">
                
                {{-- Engineering Specifications Card --}}
                <div class="bg-white dark:bg-slate-900/40 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm backdrop-blur-sm">
                    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/30 dark:bg-slate-800/20">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-microchip text-indigo-500 text-sm"></i>
                            <h3 class="font-bold text-xs uppercase tracking-[0.15em] text-slate-400">Engineering Specifications</h3>
                        </div>
                        <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-widest rounded-md border border-indigo-100 dark:border-indigo-900/30"
                            x-text="revisionBadgeText()">
                        </span>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-y-8 gap-x-10">
                            @foreach([
                                ['label' => 'Customer',      'key' => 'customer', 'icon' => 'fa-building'],
                                ['label' => 'Project Model', 'key' => 'model',    'icon' => 'fa-car-side'],
                                ['label' => 'Part Number',   'key' => 'part_no',  'icon' => 'fa-hashtag'],
                                ['label' => 'Document Group','key' => 'doc_type', 'icon' => 'fa-layer-group'],
                                ['label' => 'Sub Category',  'key' => 'category', 'icon' => 'fa-tags'],
                                ['label' => 'ECN Control',   'key' => 'ecn_no',   'icon' => 'fa-barcode']
                            ] as $meta)
                            <div class="space-y-2">
                                <div class="flex items-center gap-1.5 opacity-60">
                                    <i class="fa-solid {{ $meta['icon'] }} text-[10px] text-slate-400"></i>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-slate-400">{{ $meta['label'] }}</label>
                                </div>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate" x-text="pkg.metadata?.{{ $meta['key'] }} || '—'"></p>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-10 pt-8 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex items-center gap-4 bg-slate-50/50 dark:bg-slate-800/30 p-4 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center text-slate-400 shadow-sm">
                                    <i class="fa-solid fa-files-medical"></i>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Payload Files</label>
                                    <p class="text-sm font-black text-slate-800 dark:text-slate-100"><span x-text="getTotalFiles()"></span> Items</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 bg-slate-50/50 dark:bg-slate-800/30 p-4 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center text-slate-400 shadow-sm">
                                    <i class="fa-solid fa-hard-drive"></i>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Package Weight</label>
                                    <p class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase" x-text="getTotalSize()"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 bg-red-50/30 dark:bg-red-900/10 p-4 rounded-xl border border-dashed border-red-200/50 dark:border-red-900/30">
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-red-600 flex items-center justify-center text-red-500 shadow-sm">
                                    <i class="fa-solid fa-hourglass-end text-white text-xs"></i>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-red-400 mb-0.5">Access Termination</label>
                                    <p class="text-sm font-black text-red-600 dark:text-red-400 uppercase" x-text="formatDate(pkg.metadata?.expired_at)"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Unified File Explorer --}}
                <div class="space-y-4">
                    @foreach([
                        ['title' => '2D Technical Drawings', 'key' => '2d',  'icon' => 'fa-compass-drafting', 'color' => 'indigo'],
                        ['title' => '3D Geometric Models',  'key' => '3d',  'icon' => 'fa-cube',             'color' => 'emerald'],
                        ['title' => 'Engineering Documents', 'key' => 'ecn', 'icon' => 'fa-file-signature',  'color' => 'amber']
                    ] as $section)
                    <div class="group/section bg-white dark:bg-slate-900/40 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transition-all duration-300 shadow-sm"
                         :class="{'ring-2 ring-{{ $section['color'] }}-500/20 border-{{ $section['color'] }}-500/20': openSections.includes('{{ $section['key'] }}')}">
                        
                        <button @click="toggleSection('{{ $section['key'] }}')"
                            class="w-full px-8 py-5 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all">
                            <div class="flex items-center gap-5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-{{ $section['color'] }}-50 dark:bg-{{ $section['color'] }}-900/20 text-{{ $section['color'] }}-600 dark:text-{{ $section['color'] }}-400 group-hover/section:scale-110 transition-transform shadow-sm">
                                    <i class="fa-solid {{ $section['icon'] }} text-sm"></i>
                                </div>
                                <div class="text-left space-y-0.5">
                                    <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">{{ $section['title'] }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]" x-text="`${pkg.files?.['{{ $section['key'] }}']?.length || 0} Registered Objects`"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 transition-transform duration-500 ease-out"
                                   :class="{'rotate-180 text-{{ $section['color'] }}-500': openSections.includes('{{ $section['key'] }}')}"></i>
                            </div>
                        </button>

                        <div x-show="openSections.includes('{{ $section['key'] }}')" x-collapse x-cloak>
                            <div class="px-6 pb-6 pt-2">
                                <div class="bg-slate-50/50 dark:bg-slate-800/20 rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800/50">
                                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/50">
                                        <template x-for="file in (pkg.files?.['{{ $section['key'] }}'] || [])" :key="file.id || file.name">
                                            <li class="px-6 py-4 flex items-center justify-between hover:bg-white dark:hover:bg-slate-800/50 transition-colors group/file">
                                                <div class="flex items-center gap-5 min-w-0 flex-1">
                                                    <div class="relative flex-shrink-0 group-hover/file:rotate-6 transition-transform">
                                                        <template x-if="file.icon_src">
                                                            <img :src="file.icon_src" class="w-9 h-9 object-contain drop-shadow-sm" />
                                                        </template>
                                                        <template x-if="!file.icon_src">
                                                            <div class="w-9 h-9 rounded-lg bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-400 shadow-sm">
                                                                <i class="fa-solid fa-file-code text-xs"></i>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    
                                                    <div class="min-w-0 pr-4">
                                                        <h5 class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate group-hover/file:text-indigo-600 transition-colors" x-text="file.name"></h5>
                                                        <div class="flex items-center gap-3 mt-1 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                                            <span class="flex items-center gap-1"><i class="fa-regular fa-hard-drive opacity-50"></i> <span x-text="file.size ? formatBytes(file.size) : '0 B'"></span></span>
                                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                            <span class="flex items-center gap-1"><i class="fa-regular fa-clock opacity-50"></i> READY</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <button @click="downloadSingleFile(file)" class="flex-shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-indigo-600 hover:border-indigo-500 transition-all flex items-center justify-center opacity-0 group-hover/file:opacity-100 shadow-sm translate-x-4 group-hover/file:translate-x-0">
                                                    <i class="fa-solid fa-download text-[10px]"></i>
                                                </button>
                                            </li>
                                        </template>
                                        <template x-if="!pkg.files?.['{{ $section['key'] }}']?.length">
                                            <div class="px-8 py-10 text-center space-y-3">
                                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-300">
                                                    <i class="fa-solid fa-folder-open text-xl"></i>
                                                </div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">System Data Node Empty<br><span class="opacity-50 font-medium">No objects found for this classification.</span></p>
                                            </div>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Controls & Actions Column --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Master Control Card --}}
                <div class="bg-indigo-600 dark:bg-indigo-700 rounded-2xl shadow-xl shadow-indigo-600/20 p-8 text-white relative overflow-hidden group">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-indigo-400/20 rounded-full blur-2xl"></div>
                    
                    <div class="relative z-10 space-y-6">
                        <div class="space-y-2">
                            <h3 class="text-xl font-black uppercase tracking-tight leading-none">Compile Package</h3>
                            <p class="text-indigo-100 text-[10px] font-bold uppercase tracking-[0.1em] opacity-80">ZIP Archive Generation</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 text-xs bg-black/10 p-3 rounded-xl border border-white/5">
                                <i class="fa-solid fa-stamp text-indigo-300"></i>
                                <span class="font-bold tracking-wide">Auto-Stamp Applied</span>
                            </div>
                            <div class="flex items-center gap-3 text-xs bg-black/10 p-3 rounded-xl border border-white/5">
                                <i class="fa-solid fa-shield-check text-indigo-300"></i>
                                <span class="font-bold tracking-wide">SECURE Channel SSL</span>
                            </div>
                        </div>

                        <button @click="downloadPackage()"
                            class="w-full flex items-center justify-center gap-3 py-4 bg-white text-indigo-600 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-slate-50 hover:shadow-2xl hover:shadow-indigo-900/40 transition-all active:scale-95 group/btn">
                            <i class="fa-solid fa-cloud-arrow-down group-hover/btn:translate-y-0.5 transition-transform"></i>
                            Execute Download
                        </button>
                    </div>
                </div>

                {{-- Version Controller --}}
                <div class="bg-white dark:bg-slate-900/40 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6" 
                     x-show="revisionList.length > 0">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-code-merge text-slate-400 text-xs"></i>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Object Version History</h4>
                    </div>
                    <div class="relative">
                        <select x-model="selectedRevisionId" @change="onRevisionChange()" :disabled="isLoadingRevision"
                            class="block w-full py-3.5 pl-4 pr-10 text-xs font-black uppercase tracking-widest border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white cursor-pointer hover:bg-white dark:hover:bg-slate-800 transition-all appearance-none">
                            <template x-for="rev in revisionList" :key="rev.id">
                                <option :value="rev.id" x-text="rev.text" :selected="rev.id == selectedRevisionId"></option>
                            </template>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none opacity-30">
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 mt-3 text-center uppercase tracking-widest">Select snapshot version to restore</p>
                </div>

                {{-- Technical Protocol --}}
                <div class="p-6 rounded-2xl border border-indigo-100 dark:border-slate-800 bg-indigo-50/20 dark:bg-indigo-900/5">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-500 shrink-0">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-black text-indigo-900 dark:text-indigo-400 uppercase tracking-widest">Quality Protocol</h4>
                            <p class="text-[10px] font-bold text-indigo-700/60 dark:text-slate-500 leading-relaxed uppercase tracking-wider">
                                All payloads are injected with <span class="text-indigo-600 dark:text-indigo-400">Digital Identity Stamps</span>. Unauthorized distribution is trace-mapped by system forensics.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@endsection

@push('style')
    <style>
        [x-collapse] { @apply overflow-hidden; }
        [x-cloak] { display: none !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /* ========== Minimal Toast Helper ========== */
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
            didOpen: (toast) => { toast.onmouseenter = Swal.stopTimer; toast.onmouseleave = Swal.resumeTimer; }
        });
        const notify = (type, msg) => Toast.fire({ icon: type, title: msg });

        /* ========== Alpine Core Logic ========== */
        function receiptDetail(config = {}) {
            return {
                revisionId: config.revisionId || null,
                exportId: config.revisionId || null, 
                selectedRevisionId: config.revisionId || null,
                pkg: { files: {} }, 
                revisionList: [],
                
                isLoadingRevision: false,
                openSections: ['2d', '3d', 'ecn'],
                _downloadAbortController: null, 

                // --- UI Helpers ---
                formatBytes(bytes, decimals = 2) {
                    if (!+bytes) return '0 B';
                    const k = 1024;
                    const dm = decimals < 0 ? 0 : decimals;
                    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
                },
                formatDate(dateString) {
                    if (!dateString) return '-';
                    // Format tanggal sederhana YYYY-MM-DD
                    return new Date(dateString).toLocaleDateString('en-GB', {
                        day: 'numeric', month: 'short', year: 'numeric'
                    });
                },
                revisionBadgeText() {
                    const m = this.pkg?.metadata || {};
                    return m.revision_label ? `${m.revision} | ${m.revision_label}` : (m.revision || 'REV-?');
                },
                
                // --- New Aggregation Helpers ---
                getTotalFiles() {
                    if (!this.pkg?.files) return 0;
                    let count = 0;
                    // Loop semua category object
                    Object.values(this.pkg.files).forEach(files => {
                        if (Array.isArray(files)) count += files.length;
                    });
                    return count;
                },
                getTotalSize() {
                    if (!this.pkg?.files) return '0 B';
                    let totalBytes = 0;
                    Object.values(this.pkg.files).forEach(files => {
                        if (Array.isArray(files)) {
                            files.forEach(f => {
                                // Pastikan size diperlakukan sebagai angka
                                totalBytes += (Number(f.size) || 0);
                            });
                        }
                    });
                    return this.formatBytes(totalBytes);
                },

                toggleSection(key) {
                    this.openSections.includes(key) 
                        ? this.openSections = this.openSections.filter(k => k !== key)
                        : this.openSections.push(key);
                },

                // --- Lifecycle ---
                async init() {
                    if (this.revisionId) await this.fetchData(this.revisionId);
                },

                // --- Data Fetching ---
                async fetchData(id) {
                    this.isLoadingRevision = true;
                    try {
                        const res = await fetch(`/api/receipts/revision-detail/${id}`); 
                        if (!res.ok) throw new Error('Failed to fetch data');
                        
                        const data = await res.json();
                        if (data.success) {
                            this.pkg = data.detail || { files: {} }; 
                            this.revisionList = data.revisionList || [];
                            this.exportId = data.exportId;
                            this.selectedRevisionId = data.exportId; 
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (e) {
                        console.error(e);
                        notify('error', 'Failed to load receipt detail.');
                    } finally {
                        this.isLoadingRevision = false;
                    }
                },

                onRevisionChange() {
                    if (this.selectedRevisionId) this.fetchData(this.selectedRevisionId);
                },

                // --- Single File Download ---
                downloadSingleFile(file) {
                    if (!file || !file.id) return;
                    
                    // Simple download - the backend handles the stamp
                    const downloadUrl = `/download/receipt/file/${file.id}`;
                    
                    notify('info', `Preparing ${file.name}...`);
                    
                    // Trigger download
                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.setAttribute('download', file.name);
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                },

                // --- Download Package ---
                downloadPackage() {
                    if (!this.exportId) return notify('error', 'Package ID not found.');

                    Swal.fire({
                        title: 'Confirm System Export',
                        text: "Target objects will be stamped and compiled into an encrypted ZIP container.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Execute Export',
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#94a3b8',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl font-bold uppercase tracking-widest text-[10px] px-6 py-3',
                            cancelButton: 'rounded-xl font-bold uppercase tracking-widest text-[10px] px-6 py-3'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) this.executeZipDownload();
                    });
                },

                executeZipDownload() {
                    if (this._downloadAbortController) this._downloadAbortController.abort();
                    this._downloadAbortController = new AbortController();
                    const signal = this._downloadAbortController.signal;

                    Swal.fire({
                        title: 'Compiling Data...',
                        html: '<div class="space-y-3 mt-4"><div class="text-[10px] font-black uppercase tracking-widest text-indigo-600 animate-pulse">Processing Payloads</div><p class="text-[11px] text-slate-400 font-medium">Please maintain connection. This process may take several moments.</p></div>',
                        allowOutsideClick: false,
                        showCancelButton: true,
                        cancelButtonText: 'Abort',
                        customClass: {
                            popup: 'rounded-2xl p-10',
                            cancelButton: 'rounded-xl font-bold uppercase tracking-widest text-[10px] px-6 py-2'
                        },
                        didOpen: () => Swal.showLoading()
                    }).then((res) => {
                        if (res.dismiss === Swal.DismissReason.cancel) {
                            this._downloadAbortController.abort();
                        }
                    });

                    fetch(`/api/receipts/prepare-zip/${this.exportId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        signal: signal
                    })
                    .then(async res => {
                        if (!res.ok) throw new Error((await res.json()).message || 'Server Error');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success && data.download_url) {
                            Swal.close();
                            notify('success', 'Package Exported Successfully');
                            window.location.href = data.download_url;
                        } else {
                            throw new Error('Invalid server response.');
                        }
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') {
                            notify('info', 'Process aborted by user.');
                        } else {
                            Swal.fire('System Error', err.message, 'error');
                        }
                    });
                }
            };
        }

    </script>
@endpush