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


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
        
        {{-- Left Column: Package Info (Standardized) --}}
        <div class="lg:col-span-4 space-y-6 sticky top-8">
            <div x-ref="metaCard" class="bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
                            <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
                            Package Metadata
                        </h2>
                        <a href="{{ route('receipt') }}"
                            class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-xs font-bold uppercase tracking-wider rounded-xs text-gray-600 bg-white hover:bg-gray-50 transition-all dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                             <div class="inline-flex items-center px-2.5 py-0.5 rounded-xs text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <span x-text="pkg.metadata?.revision || 'Rev —'"></span>
                             </div>
                             <div x-show="pkg.metadata?.ecn_no" class="inline-flex items-center px-2.5 py-0.5 rounded-xs text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
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

                    <div x-show="pkg.metadata?.expired_at" class="bg-amber-50 dark:bg-amber-900/10 p-3 rounded-xs border border-amber-100 dark:border-amber-900/20 flex items-center gap-3">
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
                    <template x-if="!isExpired">
                        <button @click="downloadPackage()"
                            class="w-full group flex items-center justify-center gap-3 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-[0.15em] rounded-xs transition-all active:scale-95">
                            <i class="fa-solid fa-cloud-arrow-down text-sm transition-transform group-hover:-translate-y-0.5"></i>
                            Download All
                        </button>
                    </template>
                    
                    <template x-if="isExpired">
                        <div class="space-y-3">
                            <div class="p-3 bg-rose-50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-800 rounded-xs text-center">
                                <p class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-widest">Access Expired</p>
                                <p class="text-[9px] text-rose-500 mt-1">Please request a re-share to download these files.</p>
                            </div>
                            
                            <template x-if="reshareStatus === 'pending'">
                                <div class="w-full py-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-black uppercase tracking-widest rounded-xs border border-blue-100 dark:border-blue-800 text-center">
                                    Request Pending
                                </div>
                            </template>

                            <template x-if="reshareStatus !== 'pending'">
                                <button @click="openReshareModal()"
                                    class="w-full group flex items-center justify-center gap-3 py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-[0.15em] rounded-xs transition-all active:scale-95">
                                    <i class="fa-solid fa-rotate-right text-sm"></i>
                                    Request Re-share
                                </button>
                            </template>
                        </div>
                    </template>
                    <div class="flex justify-between items-center mt-3 px-1">
                         <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Original Size</span>
                            <div class="group relative inline-block">
                                <i class="fa-solid fa-circle-info text-[9px] text-gray-400 cursor-help hover:text-blue-500 transition-colors"></i>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-48 p-2 bg-gray-900 text-white text-[9px] rounded-xs z-50 leading-relaxed font-normal normal-case tracking-normal">
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
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xs border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xs bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-400">
                            <i x-show="!isLoadingRevision" class="fa-solid fa-list-check text-xl"></i>
                            <i x-show="isLoadingRevision" class="fa-solid fa-spinner fa-spin text-xl text-blue-500"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Object Manifest Details</h2>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1" x-text="isLoadingRevision ? 'Updating content...' : 'Package Contents Summary'"></p>
                        </div>
                    </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <template x-for="section in [
                    {key: '2d', title: '2D Drawings', icon: 'fa-drafting-compass', color: 'blue'},
                    {key: '3d', title: '3D Models', icon: 'fa-cubes', color: 'blue'},
                    {key: 'ecn', title: 'ECN / Documents', icon: 'fa-file-lines', color: 'amber'}
                ]" :key="section.key">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 overflow-hidden group/sec">
                        <div @click="toggleSection(section.key)" 
                            class="px-6 py-5 flex items-center justify-between bg-white dark:bg-gray-800 group-hover/sec:bg-gray-50 dark:group-hover/sec:bg-gray-700/30 transition-colors cursor-pointer select-none">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-xs flex items-center justify-center text-white transition-transform"
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
                                <div class="bg-gray-50/50 dark:bg-gray-900/30 rounded-xs border border-gray-100 dark:border-gray-700/50 overflow-hidden min-h-[100px]">
                                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50 max-h-[400px] overflow-y-auto custom-scrollbar">
                                        <template x-for="(file, index) in (pkg.files?.[section.key] || [])" :key="section.key + '-' + index">
                                            <div class="flex items-center justify-between px-6 py-4 hover:bg-white dark:hover:bg-gray-800/50 transition-all group/item">
                                                <div class="flex items-center gap-5 flex-1 min-w-0">
                                                    <div class="w-10 h-10 rounded-xs bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center transition-colors">
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

    {{-- Request Re-share Modal --}}
    <div id="requestReshareModal" 
        class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/70 p-4 backdrop-blur-sm"
        x-cloak
        x-show="showReshareModal"
        x-data="{ 
            reason: '', 
            submitting: false,
            async submit() {
                if (!this.reason.trim()) return notify('warning', 'Please provide a reason.');
                
                this.submitting = true;
                try {
                    const res = await fetch('{{ route('receipts.request-reshare') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        // We use $parent.pkg.metadata?.id or similar, but in detail view we have current revisionId decryptable
                        body: JSON.stringify({ 
                            revision_id: $data.actualRevisionId, 
                            reason: this.reason.trim() 
                        })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        notify('success', data.message);
                        this.reason = '';
                        $data.showReshareModal = false;
                        $data.reshareStatus = 'pending';
                    } else {
                        notify('error', data.message);
                    }
                } catch (e) {
                    notify('error', 'Failed to send request.');
                } finally {
                    this.submitting = false;
                }
            }
        }"
    >
        <div @click.away="!submitting && (showReshareModal = false)" class="bg-white dark:bg-gray-800 rounded-xs w-full max-w-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Request Re-share</h3>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Restore access to expired package</p>
                    </div>
                </div>
                <button type="button" @click="showReshareModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" :disabled="submitting">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="bg-blue-50/50 dark:bg-blue-900/10 p-4 rounded-xs border border-blue-100 dark:border-blue-800/50">
                    <p class="text-[9px] font-bold text-blue-400 uppercase tracking-widest mb-1">Package Details</p>
                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200" x-text="(pkg.metadata?.part_no || '') + ' (' + (pkg.metadata?.revision || '') + ')'"></p>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 px-1">
                        Reason for Re-request <span class="text-rose-500">*</span>
                    </label>
                    <textarea x-model="reason" 
                        placeholder="E.g., Need to re-check dimension, late arrival of parts..."
                        class="w-full h-32 px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xs text-sm outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-800 dark:text-gray-200"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" @click="showReshareModal = false" 
                    class="px-5 py-2 text-[10px] font-bold text-gray-500 hover:text-gray-700 uppercase tracking-widest" :disabled="submitting">
                    Cancel
                </button>
                <button type="button" @click="submit()" 
                    class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold uppercase tracking-widest rounded-xs transition-all disabled:opacity-50"
                    :disabled="submitting || !reason.trim()">
                    <span x-show="!submitting">Send Request</span>
                    <span x-show="submitting"><i class="fa-solid fa-spinner fa-spin"></i> Processing...</span>
                </button>
            </div>
        </div>
    </div>
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
                actualRevisionId: null, // To be loaded from JSON
                pkg: { files: {}, metadata: {} }, 
                isLoadingRevision: false,
                isExpired: false,
                reshareStatus: null,
                showReshareModal: false,
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
                        const res = await fetch(`{{ url('/api/receipts/revision-detail') }}/${id.toString().replace(/=/g, '-')}`); 
                        const data = await res.json();
                        if (data.success) {
                            this.pkg = data.detail || { files: {}, metadata: {} }; 
                            this.exportId = data.exportId;
                            this.isExpired = data.isExpired || false;
                            this.reshareStatus = data.reshareStatus || null;
                            // Extract actual revision ID from encrypted form for reshare request
                            // (Actually it's better to pass it back from API if needed, but we can reuse the current ID)
                            // The API expects 'revision_id' as integer in requestReshare.
                            // However, we need the decrypted ID or have the controller support encrypted ID.
                            // For now, let's assume we need to solve the reshare request later if it needs real ID.
                            // Actually, I should probably send back the raw ID in the JSON for internal use.
                             this.actualRevisionId = data.rawRevisionId; 
                        } else throw new Error(data.message);
                    } catch (e) {
                        notify('error', e.message || 'Error loading data');
                    } finally {
                        this.isLoadingRevision = false;
                    }
                },

                downloadPackage() {
                    if (!this.exportId || this.isExpired) return;
                    
                    // Dispatch event to open the shared modal
                    window.dispatchEvent(new CustomEvent('open-download-zip', {
                        detail: {
                            count: this.getTotalFiles(),
                            size: this.getTotalSize(),
                            url: `{{ url('/api/receipts/prepare-zip') }}/${this.exportId.toString().replace(/=/g, '-')}`
                        }
                    }));
                },

                openReshareModal() {
                    this.showReshareModal = true;
                }
            };
        }
    </script>
@endpush