@props(['isFullscreen', 'imageZoom', 'selectedFile', 'pdfPageNum', 'pdfNumPages', 'tifPageNum', 'tifNumPages', 'enableFullscreen'])

<div x-show="isPreviewable2D(selectedFile?.name)" 
     class="absolute bottom-8 inset-x-0 z-10 flex justify-center pointer-events-none transition-all duration-300 translate-y-6 opacity-0 group-hover:translate-y-0 group-hover:opacity-100"
     :class="isFullscreen ? 'translate-y-0 opacity-100' : ''">
    
    {{-- Tool Hub --}}
    <div class="flex items-center bg-white/40 dark:bg-black/85 backdrop-blur-3xl shadow-2xl border border-white/40 dark:border-white/20 rounded-2xl ring-1 ring-black/5 dark:ring-white/10 p-1.5 gap-1.5 pointer-events-auto shadow-blue-500/5">
        
        {{-- Zoom Group --}}
        <div class="flex items-center gap-1">
            <button @click="zoomOut()"
                class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-white hover:bg-blue-600 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20" title="Zoom Out">
                <i class="fa-solid fa-minus text-xs"></i>
            </button>
            
            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400 px-3 min-w-[3.5rem] text-center"
                x-text="Math.round(imageZoom * 100) + '%'"></span>
            
            <button @click="zoomIn()"
                class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-white hover:bg-blue-600 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20" title="Zoom In">
                <i class="fa-solid fa-plus text-xs"></i>
            </button>
        </div>

        <div class="w-px h-6 bg-gray-400/30 dark:bg-white/10 mx-1"></div>

        {{-- Navigation Hub (PDF/TIFF) --}}
        <div x-show="isPdf(selectedFile?.name) || isTiff(selectedFile?.name)" class="flex items-center gap-1">
            
            {{-- PDF --}}
            <div x-show="isPdf(selectedFile?.name)" class="flex items-center gap-1">
                <button @click="prevPdfPage()" :disabled="pdfPageNum <= 1"
                    class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-white hover:bg-blue-600 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-500 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 px-2 min-w-[3.5rem] text-center">
                    <span x-text="pdfPageNum"></span> <span class="text-gray-400">/</span> <span x-text="pdfNumPages"></span>
                </span>
                <button @click="nextPdfPage()" :disabled="pdfPageNum >= pdfNumPages"
                    class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-white hover:bg-blue-600 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-500 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>

            {{-- TIFF --}}
            <div x-show="isTiff(selectedFile?.name)" class="flex items-center gap-1">
                <button @click="prevTifPage()" :disabled="tifPageNum <= 1"
                    class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-white hover:bg-blue-600 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-500 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 px-2 min-w-[3.5rem] text-center">
                    <span x-text="tifPageNum"></span> <span class="text-gray-400">/</span> <span x-text="tifNumPages"></span>
                </span>
                <button @click="nextTifPage()" :disabled="tifPageNum >= tifNumPages"
                    class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-white hover:bg-blue-600 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-500 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>

            <div class="w-px h-6 bg-gray-400/30 dark:bg-white/10 mx-1"></div>
        </div>

        <button @click="resetZoom()"
            class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-white hover:bg-blue-600 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20" title="Reset Fit">
            <i class="fa-solid fa-compress text-xs"></i>
        </button>

        <button x-show="@json($enableFullscreen)" @click="toggleFullscreen()" 
                class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-200 hover:text-white hover:bg-blue-600 rounded-xl transition-all active:scale-90 border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20"
                :title="isFullscreen ? 'Exit Fullscreen' : 'Enter Fullscreen'">
            <i class="fa-solid" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
        </button>
    </div>
</div>
