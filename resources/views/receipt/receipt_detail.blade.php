@extends('layouts.app')
@section('title', 'Receipt Detail - Download Receipt')
@section('header-title', 'Receipt - Download Detail')

@section('content')

<div class="p-6 lg:p-8 bg-slate-50 dark:bg-gray-900 min-h-screen" 
    x-data="receiptDetail({
        revisionId: @js($receiptId ?? null),
        userDeptCode: @js($userDeptCode ?? null)
    })" 
    x-init="init()">

    {{-- Loading Overlay --}}
    <div x-show="isLoadingRevision" x-transition.opacity
        class="fixed inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-50 flex flex-col items-center justify-center"
        style="display: none;">
        <div class="relative flex items-center justify-center mb-4">
            <div class="absolute animate-ping inline-flex h-12 w-12 rounded-full bg-blue-400 opacity-25"></div>
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 tracking-wide animate-pulse">Loading Data...</span>
    </div>

    <div class="max-w-6xl mx-auto space-y-8">
        
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                    <span class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg text-blue-600 dark:text-blue-400">
                        <i class="fa-solid fa-file-invoice fa-lg"></i>
                    </span>
                    Package Details
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review specifications and download stamped files.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('receipt') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fa-solid fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            {{-- LEFT COLUMN: Metadata Information --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 flex justify-between items-center bg-slate-50/50 dark:bg-gray-800">
                        <h3 class="font-semibold text-slate-800 dark:text-white">Project Information</h3>
                        
                        {{-- Revision Badge --}}
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800"
                            x-text="revisionBadgeText()">
                        </span>
                    </div>

                    <div class="p-6">
                        {{-- Grid Layout for Metadata --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-6">
                            {{-- Baris 1 --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer</label>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate" x-text="pkg.metadata?.customer || '-'"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Model</label>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate" x-text="pkg.metadata?.model || '-'"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Part Number</label>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate" x-text="pkg.metadata?.part_no || '-'"></p>
                            </div>
                            
                            {{-- Baris 2 --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Doc Type</label>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate" x-text="pkg.metadata?.doc_type || '-'"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Category</label>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate" x-text="pkg.metadata?.category || '-'"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">ECN Number</label>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate" x-text="pkg.metadata?.ecn_no || '-'"></p>
                            </div>

                            {{-- Divider --}}
                            <div class="col-span-full border-t border-slate-100 dark:border-gray-700 my-1"></div>

                            {{-- Baris 3 (Total Stats & Expire) --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Files</label>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    <i class="fa-regular fa-copy mr-1 text-slate-400"></i>
                                    <span x-text="getTotalFiles()"></span> Files
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Size</label>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    <i class="fa-solid fa-weight-hanging mr-1 text-slate-400"></i>
                                    <span x-text="getTotalSize()"></span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Link Expires On</label>
                                <p class="text-sm font-bold text-red-600 dark:text-red-400">
                                    <i class="fa-regular fa-clock mr-1"></i>
                                    <span x-text="formatDate(pkg.metadata?.expired_at)"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Accordion File Lists --}}
                <div class="space-y-4">
                    @foreach([
                        ['title' => '2D Drawings', 'icon' => 'fa-drafting-compass', 'key' => '2d', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
                        ['title' => '3D Models', 'icon' => 'fa-cubes', 'key' => '3d', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-100'],
                        ['title' => 'ECN / Documents', 'icon' => 'fa-file-lines', 'key' => 'ecn', 'color' => 'text-amber-600', 'bg' => 'bg-amber-100']
                    ] as $section)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden transition-all duration-300"
                         :class="{'ring-2 ring-blue-500/20': openSections.includes('{{ $section['key'] }}')}">
                        
                        <button @click="toggleSection('{{ $section['key'] }}')"
                            class="w-full px-6 py-4 flex items-center justify-between bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $section['bg'] }} dark:bg-gray-700">
                                    <i class="fa-solid {{ $section['icon'] }} {{ $section['color'] }} dark:text-gray-300 text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block text-sm font-bold text-slate-800 dark:text-white group-hover:text-blue-600 transition-colors">{{ $section['title'] }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400" x-text="`${pkg.files?.['{{ $section['key'] }}']?.length || 0} Files Available`"></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 dark:bg-gray-700 text-slate-400 group-hover:text-blue-600 transition-all">
                                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"
                                   :class="{'rotate-180': openSections.includes('{{ $section['key'] }}')}"></i>
                            </div>
                        </button>

                        <div x-show="openSections.includes('{{ $section['key'] }}')" x-collapse>
                            <div class="border-t border-slate-100 dark:border-gray-700">
                                <ul class="divide-y divide-slate-100 dark:divide-gray-700">
                                    <template x-for="file in (pkg.files?.['{{ $section['key'] }}'] || [])" :key="file.id || file.name">
                                        <li class="px-6 py-3 flex items-center hover:bg-slate-50 dark:hover:bg-gray-700/30 transition-colors group/item">
                                            <div class="flex items-center min-w-0 gap-3 w-full">
                                                <div class="flex-shrink-0">
                                                    <template x-if="file.icon_src">
                                                        <img :src="file.icon_src" class="w-8 h-8 object-contain" />
                                                    </template>
                                                    <template x-if="!file.icon_src">
                                                        <div class="w-8 h-8 rounded bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-400">
                                                            <i class="fa-solid fa-file"></i>
                                                        </div>
                                                    </template>
                                                </div>
                                                
                                                <div class="flex flex-col min-w-0 flex-grow">
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200 truncate pr-4" x-text="file.name"></span>
                                                        <span class="text-[10px] text-slate-400 bg-slate-100 dark:bg-gray-700 px-2 py-0.5 rounded" x-text="file.size ? formatBytes(file.size) : ''"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </template>
                                    <template x-if="!pkg.files?.['{{ $section['key'] }}']?.length">
                                        <li class="p-8 text-center flex flex-col items-center justify-center text-slate-400">
                                            <i class="fa-regular fa-folder-open text-2xl mb-2 opacity-50"></i>
                                            <span class="text-sm italic">No files found in this category.</span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: Actions & History --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Download All Card --}}
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                    
                    <h3 class="text-lg font-bold mb-2">Download Package</h3>
                    <p class="text-blue-100 text-sm mb-6">Download all stamped drawings and documents in a single ZIP file.</p>
                    
                    <button @click="downloadPackage()"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-white text-blue-700 rounded-lg font-bold hover:bg-blue-50 hover:shadow-md transition-all active:scale-95">
                        <i class="fa-solid fa-file-zipper"></i>
                        Download ZIP Archive
                    </button>
                </div>

                {{-- Revision Selector --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 p-5" 
                     x-show="revisionList.length > 0">
                    <label class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-slate-400"></i>
                        Revision History
                    </label>
                    <div class="relative">
                        <select x-model="selectedRevisionId" @change="onRevisionChange()" :disabled="isLoadingRevision"
                            class="block w-full py-2.5 pl-3 pr-10 text-sm border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:text-white cursor-pointer hover:bg-white dark:hover:bg-gray-600 transition-colors">
                            <template x-for="rev in revisionList" :key="rev.id">
                                <option :value="rev.id" x-text="rev.text" :selected="rev.id == selectedRevisionId"></option>
                            </template>
                        </select>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Select a revision to view its details.</p>
                </div>

                {{-- Help / Info --}}
                <div class="bg-blue-50 dark:bg-gray-800/50 rounded-xl p-5 border border-blue-100 dark:border-gray-700">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300">Important Note</h4>
                            <p class="text-xs text-blue-700 dark:text-slate-400 mt-1 leading-relaxed">
                                All downloaded files are automatically stamped with your supplier information and the current timestamp for tracking purposes.
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

                // --- Download Package ---
                downloadPackage() {
                    if (!this.exportId) return notify('error', 'Package ID not found.');

                    Swal.fire({
                        title: 'Download All Files?',
                        text: "Files will be stamped and compressed into a ZIP archive.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Download',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#94a3b8'
                    }).then((result) => {
                        if (result.isConfirmed) this.executeZipDownload();
                    });
                },

                executeZipDownload() {
                    if (this._downloadAbortController) this._downloadAbortController.abort();
                    this._downloadAbortController = new AbortController();
                    const signal = this._downloadAbortController.signal;

                    Swal.fire({
                        title: 'Preparing ZIP...',
                        html: 'Applying stamps and compressing files.<br><span class="text-xs text-gray-400">Please do not close this page.</span>',
                        allowOutsideClick: false,
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
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
                            Swal.fire({
                                title: 'Ready!',
                                text: 'Your package is ready to download.',
                                icon: 'success',
                                confirmButtonText: 'Download Now',
                                confirmButtonColor: '#10b981'
                            }).then((r) => {
                                if(r.isConfirmed) window.location.href = data.download_url;
                            });
                        } else {
                            throw new Error('Invalid server response.');
                        }
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') {
                            notify('info', 'Download canceled.');
                        } else {
                            Swal.fire('Error', err.message, 'error');
                        }
                    });
                }
            };
        }
    </script>
@endpush