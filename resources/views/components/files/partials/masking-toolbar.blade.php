@props(['enableMasking', 'isFullscreen', 'isCreatingMask', 'masks'])

@if($enableMasking)
<div x-show="enableMasking && isPreviewable2D(selectedFile?.name) && !imgLoading && !pdfLoading && !tifLoading && !hpglLoading && !iges.loading && !imgError && !pdfError && !tifError && !hpglError && !iges.error" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute top-10 inset-x-0 z-30 flex justify-center pointer-events-none transition-all duration-300 group-hover:opacity-100"
     :class="isFullscreen ? 'translate-y-4 opacity-100' : 'translate-y-2 opacity-0'">
    
    {{-- Main Toolbar Container --}}
    <div class="flex items-center bg-white/40 dark:bg-black/85 backdrop-blur-3xl border border-white/40 dark:border-white/20 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 p-1.5 gap-1 pointer-events-auto shadow-blue-500/5">
    
    {{-- SEGMENT 1: STATUS & NAVIGATION --}}
    <div @click="selectAvailableMask()" 
         class="flex items-center gap-2.5 px-3 py-2 bg-gray-900/5 dark:bg-white/5 rounded-xl mr-1 border border-transparent hover:border-blue-500/30 hover:bg-blue-500/5 transition-all cursor-pointer group/status">
        <div class="relative">
            <i class="fa-solid fa-layer-group text-blue-600 dark:text-blue-400 text-xs"></i>
            <span x-show="masks.length > 0" 
                  class="absolute -top-1.5 -right-1.5 flex items-center justify-center w-3.5 h-3.5 bg-blue-600 text-white rounded-full text-[8px] font-bold ring-2 ring-white dark:ring-gray-900" 
                  x-text="masks.length"></span>
        </div>
        <div class="flex flex-col">
            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.1em] leading-none mb-0.5 whitespace-nowrap">Manager</span>
            <span class="text-[10px] font-bold text-gray-700 dark:text-gray-200 leading-none whitespace-nowrap">Block List</span>
        </div>
        <i class="fa-solid fa-chevron-right text-[8px] text-gray-400 group-hover/status:translate-x-0.5 transition-transform"></i>
    </div>

    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700/50 mx-1"></div>

    {{-- SEGMENT 2: GLOBAL ACTIONS --}}
    <div class="flex items-center gap-1">
        <button type="button" @click.stop="addMask()" title="Add New Block"
            class="flex items-center gap-2 px-3 py-2 rounded-xl transition-all duration-200 active:scale-95 font-bold group/btn"
            :class="isCreatingMask ? 'bg-emerald-600 text-white ring-2 ring-emerald-300 dark:ring-emerald-800' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white'">
            <i class="fa-solid fa-plus text-[10px] transition-transform" :class="isCreatingMask ? 'rotate-45' : 'group-hover/btn:rotate-90'"></i>
            <span class="text-[10px] whitespace-nowrap uppercase tracking-tighter" x-text="isCreatingMask ? 'Cancel' : 'New Block'"></span>
        </button>
        
        <button type="button" @click="saveCurrentMask()" title="Save Changes"
            class="flex items-center gap-2 px-3 py-2 rounded-xl text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white transition-all duration-200 active:scale-95 font-bold">
            <i class="fa-solid fa-check-double text-[10px]"></i>
            <span class="text-[10px] whitespace-nowrap uppercase tracking-tighter">Save All</span>
        </button>
    </div>

    {{-- SEGMENT 3: SELECTION CONTEXT (Hanya muncul jika ada blok aktif) --}}
    <template x-if="getActiveMask()">
        <div class="flex items-center gap-1 ml-1 pl-1 border-l border-gray-200 dark:border-gray-700/50"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <template x-if="(isPdf(selectedFile?.name) && pdfNumPages > 1) || (isTiff(selectedFile?.name) && tifNumPages > 1)">
                <button type="button" @click.stop="applyActiveMaskToAll()" 
                    class="flex items-center gap-2 px-3 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all duration-200 active:scale-95 font-bold border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                    <i class="fa-solid fa-clone text-[10px]"></i>
                    <span class="text-[10px] whitespace-nowrap uppercase tracking-tighter">Apply to All Pages</span>
                </button>
            </template>

            <button type="button" @click.stop="removeActiveMask()" 
                class="w-9 h-9 flex items-center justify-center rounded-xl text-red-500 hover:bg-red-600 hover:text-white transition-all duration-200 active:scale-95 border border-transparent hover:border-red-600 shadow-sm transition-colors"
                title="Delete Selected Block">
                <i class="fa-solid fa-trash-can text-xs"></i>
            </button>
        </div>
    </template>
    </div>
</div>
@endif
