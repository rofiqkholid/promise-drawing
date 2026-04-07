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

        {{-- UNSUPPORTED FILE FALLBACK (REFINED) --}}
        <template x-if="selectedFile && !isPreviewable2D(selectedFile?.name) && !isCad(selectedFile?.name)">
            <div class="absolute inset-0 flex flex-col items-center justify-center p-12 text-center animate-fadeIn">
                <div class="relative mb-8">
                    <i class="fa-solid fa-file-circle-question text-5xl text-gray-300 dark:text-gray-600 group-hover/icon:scale-110 transition-transform duration-500"></i>
                </div>

                <h3 class="text-xl font-black text-gray-900 dark:text-gray-100 mb-3 tracking-tight">Preview Not Supported</h3>
                
                <div class="space-y-4 max-w-[320px]">
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                        The <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-mono text-blue-600 dark:text-blue-400 cursor-default" x-text="`.${extOf(selectedFile?.name)}`"></span> format cannot be displayed directly in the browser.
                    </p>
                    
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        To view this document, please download it to your computer or mobile device.
                    </p>
                </div>

                <div class="mt-8 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
                    <div class="w-12 h-px bg-gray-200 dark:bg-gray-800"></div>
                    <span>Reference Only</span>
                    <div class="w-12 h-px bg-gray-200 dark:bg-gray-800"></div>
                </div>
            </div>
        </template>
    </div>
</div>

@once
    @push('vite-scripts')
        @vite(['resources/js/file-viewer.js'])
    @endpush

    @push('scripts')

        {{-- Three.js + Dependencies (ES Modules via CDN) --}}
        <!-- ES Module Shims used for import maps polyfill -->
        <script async src="https://unpkg.com/es-module-shims@1.8.0/dist/es-module-shims.js"></script>
        
        <script type="importmap">
            {
                "imports": {
                    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
                    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/",
                    "three-mesh-bvh": "https://unpkg.com/three-mesh-bvh@0.9.8/build/index.module.js"
                }
            }
        </script>
        
        <script type="module">
            // Global Loader for Three.js dependencies using Dynamic Imports
            (async function() {
                if (!window.THREE) {
                    try {
                        // Import main library
                        const THREE_MODULE = await import('three');
                        
                        // Create a mutable copy of the module namespace
                        window.THREE = { ...THREE_MODULE };

                        // Import addons
                        const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');
                        const BufferGeometryUtils = await import('three/addons/utils/BufferGeometryUtils.js');
                        const { computeBoundsTree, disposeBoundsTree, acceleratedRaycast } = await import('three-mesh-bvh');

                        // Attach addons to the mutable window.THREE object
                        window.THREE.OrbitControls = OrbitControls;
                        window.THREE.BufferGeometryUtils = BufferGeometryUtils;
                        
                        // Expose BVH library separately
                        window.MeshBVHLib = { computeBoundsTree, disposeBoundsTree, acceleratedRaycast };

                        // console.log('[App] Three.js loaded globally via CDN');
                        window.dispatchEvent(new CustomEvent('three-ready'));
                    } catch (e) {
                        console.error('[App] Failed to load Three.js:', e);
                    }
                }
            })();
        </script>
        {{-- OCCT Loader --}}
        <script src="https://cdn.jsdelivr.net/npm/occt-import-js@0.0.22/dist/occt-import-js.js"></script>

        <!-- Interact.js for dragging/resizing masks -->
        <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>

        <!-- UTIF.js for TIFF rendering -->
        <script src="https://unpkg.com/utif@2.0.1/UTIF.js"></script>
        
        <!-- pdf.js 2.x -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
        <script>
            if (window['pdfjsLib']) {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
            }
        </script>
    @endpush
@endonce