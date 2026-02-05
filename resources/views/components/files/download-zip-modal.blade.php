<div x-data="downloadZipModal()" 
     @open-download-zip.window="open($event.detail)"
     x-show="show" 
     class="fixed inset-0 z-[9999] overflow-y-auto" 
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        {{-- Modal Content --}}
        <div class="inline-block w-full max-w-md p-8 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-2xl rounded-lg border border-slate-100 dark:border-slate-800"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.away="step === 'confirm' ? show = false : null">
            
            {{-- Close Button --}}
            <button x-show="step === 'confirm'" @click="show = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            {{-- STEP 1: CONFIRMATION --}}
            <div x-show="step === 'confirm'" class="text-center">
                <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-cloud-arrow-down text-2xl text-blue-600 dark:text-blue-400"></i>
                </div>
                
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Download All Files?</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-8 px-4 font-medium">
                    We will compress all drawings and documents into a single ZIP file for you.
                </p>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Files</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white" x-text="stats.count"></span>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Size</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white" x-text="stats.size"></span>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button @click="startDownload()" 
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm">
                        <i class="fa-solid fa-check mr-2"></i> Yes, Start Preparing
                    </button>
                    <button @click="show = false" class="text-sm font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 py-2">
                        Cancel
                    </button>
                </div>
            </div>

            {{-- STEP 2: PREPARING (PROGRESS) --}}
            <div x-show="step === 'preparing'" class="text-center py-4">
                <div class="relative w-20 h-20 mx-auto mb-8">
                    {{-- Spinner Animation --}}
                    <div class="absolute inset-0 border-4 border-blue-100 dark:border-blue-900/30 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <i class="fa-solid fa-file-zipper text-xl text-blue-600 dark:text-blue-400 animate-bounce"></i>
                    </div>
                </div>

                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Preparing ZIP</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
                    Gathering and compressing files...
                </p>

                {{-- Mock Progress Bar --}}
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 mb-2 overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full transition-all duration-500 ease-out" 
                         :style="`width: ${progress}%`"
                         x-init="$watch('step', value => value === 'preparing' ? startProgress() : stopProgress())"></div>
                </div>
                <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8">
                    <span x-text="statusText">Packing...</span>
                    <span x-text="`${progress}%`" class="text-blue-600">0%</span>
                </div>

                <button @click="cancelDownload()" class="text-sm font-bold text-red-500 hover:text-red-700 py-2">
                    <i class="fa-solid fa-ban mr-1"></i> Cancel Process
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function downloadZipModal() {
    return {
        show: false,
        step: 'confirm', // 'confirm', 'preparing'
        stats: { count: 0, size: 0 },
        progress: 0,
        statusText: 'Packing...',
        prepareUrl: '',
        abortController: null,
        progressInterval: null,
        
        open(detail) {
            this.stats = {
                count: detail.count || 0,
                size: detail.size || '0 Bytes'
            };
            this.prepareUrl = detail.url;
            this.step = 'confirm';
            this.progress = 0;
            this.show = true;
        },

        startProgress() {
            this.progress = 5;
            this.progressInterval = setInterval(() => {
                if (this.progress < 95) {
                    this.progress += Math.floor(Math.random() * 3) + 1;
                    if (this.progress > 40) this.statusText = 'Compressing...';
                    if (this.progress > 75) this.statusText = 'Finalizing...';
                }
            }, 600);
        },

        stopProgress() {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },

        async cancelDownload() {
            if (this.abortController) {
                this.abortController.abort();
            }
            this.stopProgress();
            this.show = false;
        },

        async startDownload() {
            this.step = 'preparing';
            this.abortController = new AbortController();
            
            try {
                // Using POST for prepare-zip
                const response = await fetch(this.prepareUrl, {
                    method: 'POST',
                    signal: this.abortController.signal,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();

                if (data.success && data.download_url) {
                    this.progress = 100;
                    this.statusText = 'Ready!';
                    this.stopProgress();
                    
                    setTimeout(() => {
                        window.location.href = data.download_url;
                        this.show = false;
                    }, 600);
                } else {
                    throw new Error(data.message || 'Failed to prepare ZIP package');
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    console.log('Download preparation cancelled by user');
                    return;
                }
                this.show = false;
                this.stopProgress();
                Swal.fire({
                    icon: 'error',
                    title: 'Bundle Failed',
                    text: err.message,
                    confirmButtonColor: '#ef4444'
                });
            }
        }
    }
}
</script>
