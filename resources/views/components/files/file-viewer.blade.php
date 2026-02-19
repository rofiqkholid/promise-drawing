{{--
/**
* File Viewer Component
*
* A reusable component for displaying various file types with optional stamp configuration.
* Refactored into smaller components for better maintainability.
*
* @props
* - showStampConfig (boolean, default: false) - Show stamp position configuration UI
* - showAdvanced3DControls (boolean, default: true) - Show advanced 3D CAD controls
* - enableFullscreen (boolean, default: true) - Enable fullscreen mode for 3D viewer
*/
--}}
@props([
    'showStampConfig' => false,
    'showAdvanced3DControls' => true,
    'enableFullscreen' => true,
    'enableMasking' => false,
])

<style>
    .mask-element { touch-action: none; user-select: none; }
    .mask-border { pointer-events: none; }
</style>

<div x-ref="refMainContainer" 
    @keydown.window.escape="isFullscreen ? toggleFullscreen() : null"
    @file-selected.window="loadFile($event.detail, false)"
    class="flex flex-col w-full select-none transition-all duration-300"
    :class="isFullscreen ? 'fixed inset-0 z-50 bg-white dark:bg-gray-900 p-4 md:p-8' : 'relative p-4 lg:p-6'">

    {{-- File Title Header --}}
    <template x-if="selectedFile">
        <div>
            <div class="mb-4 flex items-center justify-between pointer-events-auto">
                <div class="flex flex-col gap-0.5 max-w-[80%]">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 tracking-tight truncate" x-text="selectedFile?.name"></h3>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        <span>Size:</span>
                        <span x-text="formatBytes(selectedFile?.size || 0)"></span>
                    </div>
                </div>
                
                <button x-show="isFullscreen" @click="toggleFullscreen()" title="Exit Fullscreen"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-500 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20 transition-all active:scale-95">
                    <i class="fa-solid fa-compress"></i>
                </button>
            </div>
        </div>
    </template>

    {{-- STAMP CONFIGURATION PANEL --}}
    @include('components.files.partials.stamp-config', ['showStampConfig' => $showStampConfig])

    {{-- VIEWER CONTAINER (Area Abu-abu) --}}
    <div class="flex-1 relative group w-full h-full flex flex-col transition-all overflow-hidden" 
         :class="isFullscreen ? 'h-full' : 'bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-lg p-2 min-h-[500px] h-[75vh]'">
        
        {{-- MASKING TOOLBAR --}}
        @include('components.files.partials.masking-toolbar', ['enableMasking' => $enableMasking])

        {{-- 2D CONTROLS (Zoom/Pan/Pages) --}}
        @include('components.files.partials.controls-2d', ['enableFullscreen' => $enableFullscreen])

        {{-- 1. IMAGE VIEWER --}}
        @include('components.files.viewers.image')

        {{-- 2. PDF VIEWER --}}
        @include('components.files.viewers.pdf')

        {{-- 3. TIFF VIEWER --}}
        @include('components.files.viewers.tiff')

        {{-- 4. HPGL VIEWER --}}
        @include('components.files.viewers.hpgl')

        {{-- 5. 3D CAD VIEWER --}}
        @include('components.files.viewers.cad', [
            'showAdvanced3DControls' => $showAdvanced3DControls,
            'enableFullscreen' => $enableFullscreen
        ])

        {{-- UNSUPPORTED FILE FALLBACK --}}
        <template x-if="selectedFile && !isPreviewable2D(selectedFile?.name) && !isCad(selectedFile?.name)">
            <div class="flex flex-col items-center justify-center p-12 text-center h-full">
                <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <i class="fa-solid fa-file-circle-question text-4xl text-gray-300 dark:text-gray-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Preview Unavailable</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-[250px] leading-relaxed">
                    This file format (<span class="font-mono text-blue-600" x-text="extOf(selectedFile?.name)"></span>) cannot be previewed directly.
                </p>
            </div>
        </template>
    </div>
</div>