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
        <div class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>

        {{-- Modal Content --}}
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-2xl border border-slate-100 dark:border-slate-800"
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

                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1.5">Download All Files?</h3>
                <p class="text-slate-500 dark:text-slate-400 text-[13px] mb-6 px-2 font-normal">
                    We will compress all drawings and documents into a single ZIP file for you.
                </p>

                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                        <span class="block text-[11px] font-medium text-slate-500 mb-0.5">Files</span>
                        <span class="text-base font-semibold text-slate-900 dark:text-white" x-text="stats.count"></span>
                    </div>
                    <div class="px-3 py-2.5 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                        <span class="block text-[11px] font-medium text-slate-500 mb-0.5">Original Size</span>
                        <span class="text-base font-semibold text-slate-900 dark:text-white" x-text="stats.size"></span>
                    </div>
                </div>

                <div class="mb-8 px-2">
                    <p class="text-[10px] text-slate-400 font-medium leading-relaxed italic">
                        * Note: Final ZIP size may be larger than individual file totals due to stamp processing.
                    </p>
                </div>

                <div class="flex flex-col gap-2.5">
                    <button @click="startDownload()"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm shadow-sm transition-all">
                        <i class="fa-solid fa-check mr-1.5"></i> Yes, Start Preparing
                    </button>
                    <button @click="show = false" class="text-[13px] font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 py-1.5">
                        Cancel
                    </button>
                </div>
            </div>

            {{-- STEP 2: PREPARING (PROGRESS) --}}
            <div x-show="step === 'preparing'" class="text-center py-4">
                <style>
                    @keyframes dash {
                        0% {
                            stroke-dashoffset: 264;
                            transform: rotate(0deg);
                        }

                        50% {
                            stroke-dashoffset: 66;
                            transform: rotate(135deg);
                        }

                        100% {
                            stroke-dashoffset: 264;
                            transform: rotate(450deg);
                        }
                    }

                    @keyframes dash-inner {
                        0% {
                            stroke-dashoffset: 201;
                            transform: rotate(0deg);
                        }

                        50% {
                            stroke-dashoffset: 50;
                            transform: rotate(-135deg);
                        }

                        100% {
                            stroke-dashoffset: 201;
                            transform: rotate(-450deg);
                        }
                    }
                </style>
                <div class="relative w-24 h-24 mx-auto mb-8">
                    {{-- Outer Glow Effect --}}
                    <div class="absolute inset-0 bg-blue-500/20 dark:bg-blue-400/10 blur-xl rounded-full animate-pulse"></div>

                    {{-- SVG Multi-Layered Spinner --}}
                    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 100">
                        {{-- Background Track --}}
                        <circle class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-width="6" fill="transparent" r="42" cx="50" cy="50"></circle>

                        {{-- Primary Outer Spinner --}}
                        <circle class="text-blue-600" style="animation: dash 1.8s ease-in-out infinite;" stroke="currentColor" stroke-width="6" stroke-linecap="round" fill="transparent" r="42" cx="50" cy="50" stroke-dasharray="264" stroke-dashoffset="264" transform-origin="center"></circle>

                        {{-- Secondary Inner Spinner --}}
                        <circle class="text-blue-400/40" style="animation: dash-inner 1.2s ease-in-out infinite;" stroke="currentColor" stroke-width="4" stroke-linecap="round" fill="transparent" r="32" cx="50" cy="50" stroke-dasharray="201" stroke-dashoffset="201" transform-origin="center"></circle>
                    </svg>

                    {{-- Central Pulsing Icon --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <i class="fa-solid fa-file-zipper text-2xl text-blue-600 dark:text-blue-400 animate-pulse"></i>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1.5">Preparing ZIP</h3>
                <p class="text-slate-500 dark:text-slate-400 text-[13px] font-normal mb-6">
                    Gathering and compressing files...
                </p>

                {{-- Mock Progress Bar --}}
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 mb-2 overflow-hidden">
                    <div class="bg-blue-600 h-full transition-all duration-500 ease-out"
                        :style="`width: ${progress}%`"
                        x-init="$watch('step', value => value === 'preparing' ? startProgress() : stopProgress())"></div>
                </div>
                <div class="flex justify-between text-[11px] font-medium text-slate-500 tracking-wide mb-8">
                    <span x-text="statusText">Packing...</span>
                    <span x-text="`${progress}%`" class="text-blue-600 font-semibold">0%</span>
                </div>

                <button @click="cancelDownload()" class="w-full py-2.5 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 font-medium text-[13px] transition-all duration-200 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancel Process
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
            stats: {
                count: 0,
                size: 0
            },
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