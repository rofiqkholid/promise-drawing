{{--
/**
* File Viewer Component
*
* A reusable component for displaying various file types with optional stamp configuration.
*
* @props
* - showStampConfig (boolean, default: false) - Show stamp position configuration UI
* - showAdvanced3DControls (boolean, default: true) - Show advanced 3D CAD controls
* - enableFullscreen (boolean, default: true) - Enable fullscreen mode for 3D viewer
*
* Supported File Types:
* - Images (JPG, PNG, GIF, etc.)
* - PDF documents
* - TIFF images (multi-page support)
* - HPGL plotter files
* - 3D CAD files (IGES, STEP, STL, OBJ, etc.)
*
* Features:
* - Zoom and pan controls for 2D files
* - Stamp overlay with configurable positions
* - Advanced 3D viewer with measurement tools, section cuts, exploded view
* - Loading states and error handling
*/
--}}

@php
    $showStampConfig = $showStampConfig ?? false;
    $showAdvanced3DControls = $showAdvanced3DControls ?? true;
    $showStampConfig = $showStampConfig ?? false;
    $showAdvanced3DControls = $showAdvanced3DControls ?? true;
    $enableFullscreen = $enableFullscreen ?? true;
    $enableMasking = $enableMasking ?? false; // Optional feature for masking blocks
@endphp

@once
<style>
    /* Masking cursor styles */
    .cursor-n-resize { cursor: n-resize; }
    .cursor-s-resize { cursor: s-resize; }
    .cursor-e-resize { cursor: e-resize; }
    .cursor-w-resize { cursor: w-resize; }
    .cursor-ne-resize { cursor: ne-resize; }
    .cursor-nw-resize { cursor: nw-resize; }
    .cursor-se-resize { cursor: se-resize; }
    .cursor-sw-resize { cursor: sw-resize; }
    .cursor-grab { cursor: grab; }
    .cursor-grabbing { cursor: grabbing; }
    .cursor-alias { cursor: alias; }
</style>
@endonce

<div x-ref="refMainContainer" 
    class="transition-all duration-300 bg-white dark:bg-gray-900"
    :class="isFullscreen ? 'fixed inset-0 z-[1000] flex flex-col p-4 md:p-8' : 'p-6'">
    {{-- File Header with Name and Size --}}
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedFile?.name"></h3>
            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="fileSizeInfo()"></p>
        </div>
    </div>

    {{-- Stamp Configuration Panel (Conditional) --}}
    @if($showStampConfig)
        <div x-show="isPreviewable2D(selectedFile?.name)" class="mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <span
                        class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-stamp"></i> Stamp Configuration
                    </span>

                    {{-- Apply to All Files Button --}}
                    <button type="button" @click="applyStampToAll()" :disabled="applyToAllProcessing"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-blue-500
                                                                                                                      text-[11px] font-medium text-blue-600 bg-blue-50
                                                                                                                      hover:bg-blue-100 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!applyToAllProcessing">
                            <i class="fa-solid fa-layer-group mr-1"></i>
                            Apply to All Files
                        </span>
                        <span x-show="applyToAllProcessing" class="inline-flex items-center gap-1">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            Applying...
                        </span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Original Stamp Position --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position:
                            Original</label>
                        <div class="relative">
                            <select x-model="stampConfig.original" @change="onStampChange()"
                                class="block w-full pl-3 pr-8 py-2 text-xs text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-blue-500">
                                <option value="top-left">Top Left</option>
                                <option value="top-center">Top Center</option>
                                <option value="top-right">Top Right</option>
                                <option value="bottom-left">Bottom Left</option>
                                <option value="bottom-center">Bottom Center</option>
                                <option value="bottom-right">Bottom Right</option>
                            </select>
                        </div>
                    </div>

                    {{-- Copy Stamp Position --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position: Copy</label>
                        <div class="relative">
                            <select x-model="stampConfig.copy" @change="onStampChange()"
                                class="block w-full pl-3 pr-8 py-2 text-xs text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-blue-500">
                                <option value="top-left">Top Left</option>
                                <option value="top-center">Top Center</option>
                                <option value="top-right">Top Right</option>
                                <option value="bottom-left">Bottom Left</option>
                                <option value="bottom-center">Bottom Center</option>
                                <option value="bottom-right">Bottom Right</option>
                            </select>
                        </div>
                    </div>

                    {{-- Obsolete Stamp Position --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position:
                            Obsolete</label>
                        <div class="relative">
                            <select x-model="stampConfig.obsolete" @change="onStampChange()"
                                class="block w-full pl-3 pr-8 py-2 text-xs text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-blue-500">
                                <option value="top-left">Top Left</option>
                                <option value="top-center">Top Center</option>
                                <option value="top-right">Top Right</option>
                                <option value="bottom-left">Bottom Left</option>
                                <option value="bottom-center">Bottom Center</option>
                                <option value="bottom-right">Bottom Right</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Old Masking Toolbar Removed (Moved to floating canvas tool) --}}

    <div x-show="isPreviewable2D(selectedFile?.name)"
        x-ref="ref2dContainer"
        class="flex flex-col transition-all duration-300 relative group"
        :class="isFullscreen ? 'flex-1 min-h-0 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden' : ''">

        {{-- NEW FLOATING BLOCKS TOOLBAR (Figma-style) --}}
        @if($enableMasking)
        <div x-show="enableMasking" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="absolute top-6 left-6 z-20 flex items-center bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-1.5 gap-1.5 transition-all duration-300 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100"
             :class="isFullscreen ? 'translate-y-0 opacity-100' : ''">
            
            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-800 rounded-xl mr-1 border border-gray-100 dark:border-gray-700">
                <i class="fa-solid fa-layer-group text-blue-600 text-xs"></i>
                <span class="text-[10px] font-extrabold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Block Manager</span>
                <span x-show="masks.length > 0" 
                      class="flex items-center justify-center px-1.5 h-4 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full text-[9px] font-black" 
                      x-text="masks.length"></span>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" @click.stop="addMask()" title="Add New Block"
                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-green-600 hover:text-white transition-all duration-200 active:scale-90 group">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span class="text-[10px] font-bold">New Block</span>
                </button>
                
                <button type="button" @click="saveCurrentMask()" title="Save Changes"
                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white transition-all duration-200 active:scale-90 group">
                    <i class="fa-solid fa-floppy-disk text-[10px]"></i>
                    <span class="text-[10px] font-bold">Save All</span>
                </button>

                <template x-if="getActiveMask()">
                    <div class="flex items-center gap-1">
                        <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                        <button type="button" @click.stop="removeActiveMask()" title="Delete Selected Block"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-red-500 hover:bg-red-600 hover:text-white transition-all duration-200 active:scale-90 group">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>
        @endif

        {{-- Centered Horizontal controls for 2D Files --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex items-center gap-3 transition-all duration-300 translate-y-6 opacity-0 group-hover:translate-y-0 group-hover:opacity-100"
             :class="isFullscreen ? 'translate-y-0 opacity-100' : ''">
            
            {{-- Tool Hub --}}
            <div class="flex items-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 rounded-2xl p-1.5 gap-1.5">
                
                {{-- Zoom Group --}}
                <div class="flex items-center gap-1">
                    <button @click="zoomOut()"
                        class="w-9 h-9 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all active:scale-75" title="Zoom Out">
                        <i class="fa-solid fa-minus text-xs"></i>
                    </button>
                    
                    <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400 px-3 min-w-[3.5rem] text-center"
                        x-text="Math.round(imageZoom * 100) + '%'"></span>
                    
                    <button @click="zoomIn()"
                        class="w-9 h-9 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all active:scale-75" title="Zoom In">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                </div>

                <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-1"></div>

                {{-- Navigation Hub (PDF/TIFF) --}}
                <div x-show="isPdf(selectedFile?.name) || isTiff(selectedFile?.name)" class="flex items-center gap-1">
                    
                    {{-- PDF --}}
                    <div x-show="isPdf(selectedFile?.name)" class="flex items-center gap-1">
                        <button @click="prevPdfPage()" :disabled="pdfPageNum <= 1"
                            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-blue-600 disabled:opacity-30 rounded-xl transition-all">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 px-2 min-w-[3.5rem] text-center">
                            <span x-text="pdfPageNum"></span> <span class="text-gray-400">/</span> <span x-text="pdfNumPages"></span>
                        </span>
                        <button @click="nextPdfPage()" :disabled="pdfPageNum >= pdfNumPages"
                            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-blue-600 disabled:opacity-30 rounded-xl transition-all">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>

                    {{-- TIFF --}}
                    <div x-show="isTiff(selectedFile?.name)" class="flex items-center gap-1">
                        <button @click="prevTifPage()" :disabled="tifPageNum <= 1"
                            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-blue-600 disabled:opacity-30 rounded-xl transition-all">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 px-2 min-w-[3.5rem] text-center">
                            <span x-text="tifPageNum"></span> <span class="text-gray-400">/</span> <span x-text="tifNumPages"></span>
                        </span>
                        <button @click="nextTifPage()" :disabled="tifPageNum >= tifNumPages"
                            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-blue-600 disabled:opacity-30 rounded-xl transition-all">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>

                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                </div>

                <button @click="resetZoom()"
                    class="w-9 h-9 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all active:scale-75" title="Reset Fit">
                    <i class="fa-solid fa-compress text-xs"></i>
                </button>

                <button x-show="@json($enableFullscreen)" @click="toggleFullscreen()" 
                        class="w-9 h-9 flex items-center justify-center text-gray-600 dark:text-gray-200 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all active:scale-75"
                        :title="isFullscreen ? 'Exit Fullscreen' : 'Enter Fullscreen'">
                    <i class="fa-solid" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                </button>
            </div>
        </div>
 
        {{-- Preview Area --}}
        <div class="preview-area flex-1 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 flex items-center justify-center w-full relative transition-all duration-300"
            :class="isFullscreen ? 'h-full' : 'min-h-[25rem] h-[82vh]'"
            @mousedown="enableMasking ? deactivateMask() : null">

        {{-- IMAGE VIEWER (JPG, PNG, GIF, etc.) --}}
        <template x-if="isImage(selectedFile?.name)">
            <div class="relative w-full overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                :class="isFullscreen ? 'h-full' : 'h-[70vh]'"
                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                <div class="w-full h-full flex items-center justify-center">
                    <div class="relative inline-block" :style="imageTransformStyle()">
                        <img x-ref="mainImage" :src="selectedFile?.url" @load="onImageLoad()"
                            @@error="imgLoading = false; imgError = 'The image could not be loaded. Please check the file source.'" alt="Preview"
                            class="block pointer-events-none select-none max-w-full" :class="isFullscreen ? 'max-h-full' : 'max-h-[70vh]'" loading="lazy">

                        {{-- WHITE BLOCKS (Masking) --}}
                        <template x-if="enableMasking">
                             <template x-for="mask in masks" :key="mask.id">
                                <template x-if="mask">
                                    <div x-show="mask.visible" x-cloak :style="maskStyle(mask)"
                                        class="absolute bg-white/100 shadow-sm cursor-move"
                                        :class="{ 'z-50': mask.active, 'z-10': !mask.active }"
                                        @mousedown.stop.prevent="onMaskMouseDown($event, mask)" @click.stop="activateMask(mask)">

                                        <!-- BORDER & HANDLES (Active Only) -->
                                        <div x-show="mask.active" x-cloak class="absolute inset-0 border border-blue-500 pointer-events-none"></div>

                                        <!-- ROTATE HANDLE -->
                                        <div x-show="mask.active && mask.editable" x-cloak>
                                            <div class="absolute left-1/2 -translate-x-1/2 -top-8 w-5 h-5 bg-white/80 rounded-full shadow-md border border-gray-200 flex items-center justify-center cursor-alias z-10"
                                                @mousedown.stop.prevent="startMaskRotate($event, mask)">
                                                <i class="fa-solid fa-rotate text-blue-600 text-[10px]"></i>
                                            </div>
                                        </div>

                                        <!-- RESIZE HANDLES -->
                                        <template x-if="mask.active && mask.editable">
                                            <div>
                                                <div class="absolute inset-x-3 top-0 h-2" :style="{ cursor: getCursorStyle('n', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'n', mask)"></div>
                                                <div class="absolute inset-x-3 bottom-0 h-2" :style="{ cursor: getCursorStyle('s', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 's', mask)"></div>
                                                <div class="absolute inset-y-3 left-0 w-2" :style="{ cursor: getCursorStyle('w', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'w', mask)"></div>
                                                <div class="absolute inset-y-3 right-0 w-2" :style="{ cursor: getCursorStyle('e', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'e', mask)"></div>
                                                
                                                <div class="absolute left-0 top-0 w-3 h-3" :style="{ cursor: getCursorStyle('nw', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'nw', mask)"></div>
                                                <div class="absolute right-0 top-0 w-3 h-3" :style="{ cursor: getCursorStyle('ne', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'ne', mask)"></div>
                                                <div class="absolute left-0 bottom-0 w-3 h-3" :style="{ cursor: getCursorStyle('sw', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'sw', mask)"></div>
                                                <div class="absolute right-0 bottom-0 w-3 h-3" :style="{ cursor: getCursorStyle('se', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'se', mask)"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </template>
                        </template>

                        {{-- STAMP ORIGINAL --}}
                        <div x-show="pkg.stamp && !isStampBurned" class="absolute" :class="stampPositionClass('original')" 
                            :key="`stamp-original-${stampConfig.original}`">
                            <div class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                :class="[
                                stampOriginClass('original'),
                                isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                            ]" style="transform: scale(0.45);">
                                <div class="w-full text-center border-b-2 py-0.5 px-4 font-semibold tracking-tight"
                                    :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                    <span x-text="stampTopLine('original')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold uppercase px-2"
                                        :class="isEngineering ? 'text-blue-700' : 'text-gray-600'"
                                        x-text="stampCenterOriginal()"></span>
                                </div>
                                <div class="w-full border-t-2 py-0.5 px-4 text-center font-semibold tracking-tight"
                                    :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                    <span x-text="stampBottomLine('original')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- STAMP COPY --}}
                        <div x-show="pkg.stamp && !isStampBurned" class="absolute" :class="stampPositionClass('copy')" 
                            :key="`stamp-copy-${stampConfig.copy}`">
                            <div :class="stampOriginClass('copy')"
                                class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                style="transform: scale(0.45);">
                                <div
                                    class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                    <span x-text="stampTopLine('copy')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                        x-text="stampCenterCopy()"></span>
                                </div>
                                <div
                                    class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                    <span x-text="stampBottomLine('copy')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- STAMP OBSOLETE --}}
                        <div x-show="pkg.stamp?.is_obsolete && !isStampBurned" class="absolute" :class="stampPositionClass('obsolete')" 
                            :key="`stamp-obsolete-${stampConfig.obsolete}`">
                            <div :class="stampOriginClass('obsolete')"
                                class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                style="transform: scale(0.45);">
                                <div
                                    class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                                    <span x-text="stampTopLine('obsolete')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                                        x-text="stampCenterObsolete()"></span>
                                </div>
                                <div class="w-full border-t-2 border-red-600 flex font-semibold tracking-tight">
                                    <div class="flex-1 border-r-2 border-red-600 text-center py-0.5 px-2">
                                        Name : <span x-text="obsoleteName()"></span>
                                    </div>
                                    <div class="flex-1 text-center py-0.5 px-2">
                                        Dept. : <span x-text="obsoleteDept()"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Solid Loading Overlay (Image) --}}
                <div x-show="imgLoading" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-20 rounded-lg">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-4"></i>
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Loading Preview</span>
                    </div>
                </div>

                {{-- Solid Error Overlay (Image) --}}
                <div x-show="imgError" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-30 rounded-lg p-6 text-center">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-circle-exclamation text-xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">Image Loading Failed</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 max-w-[240px] leading-relaxed line-clamp-2" x-text="imgError"></p>
                    <button @click="loadFile(selectedFile, true)" 
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-md text-[11px] font-bold hover:bg-gray-800 dark:hover:bg-gray-200 transition-all shadow-sm">
                        <i class="fa-solid fa-rotate-right"></i> Try Again
                    </button>
                </div>
            </div>
        </template>

        {{-- PDF VIEWER --}}
        <template x-if="isPdf(selectedFile?.name)">
            <div class="relative w-full overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                :class="isFullscreen ? 'h-full' : 'h-[70vh]'"
                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                <div class="w-full h-full flex items-center justify-center">
                    <div class="relative inline-block" :style="imageTransformStyle()">
                        <canvas x-ref="pdfCanvas" class="block pointer-events-none select-none max-w-full"
                            :class="isFullscreen ? 'max-h-full' : 'max-h-[70vh]'">
                        </canvas>

                        {{-- WHITE BLOCKS (Masking) --}}
                        <template x-if="enableMasking && !pdfError">
                             <template x-for="mask in masks" :key="mask.id">
                                <template x-if="mask">
                                    <div x-show="mask.visible" x-cloak :style="maskStyle(mask)"
                                        class="absolute bg-white/100 shadow-sm cursor-move"
                                        :class="{ 'z-50': mask.active, 'z-10': !mask.active }"
                                        @mousedown.stop.prevent="onMaskMouseDown($event, mask)" @click.stop="activateMask(mask)">

                                        <div x-show="mask.active" x-cloak class="absolute inset-0 border border-blue-500 pointer-events-none"></div>

                                        <div x-show="mask.active && mask.editable" x-cloak>
                                            <div class="absolute left-1/2 -translate-x-1/2 -top-8 w-5 h-5 bg-white/80 rounded-full shadow-md border border-gray-200 flex items-center justify-center cursor-alias z-10"
                                                @mousedown.stop.prevent="startMaskRotate($event, mask)">
                                                <i class="fa-solid fa-rotate text-blue-600 text-[10px]"></i>
                                            </div>
                                        </div>

                                        <template x-if="mask.active && mask.editable">
                                            <div>
                                                <div class="absolute inset-x-3 top-0 h-2" :style="{ cursor: getCursorStyle('n', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'n', mask)"></div>
                                                <div class="absolute inset-x-3 bottom-0 h-2" :style="{ cursor: getCursorStyle('s', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 's', mask)"></div>
                                                <div class="absolute inset-y-3 left-0 w-2" :style="{ cursor: getCursorStyle('w', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'w', mask)"></div>
                                                <div class="absolute inset-y-3 right-0 w-2" :style="{ cursor: getCursorStyle('e', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'e', mask)"></div>
                                                
                                                <div class="absolute left-0 top-0 w-3 h-3" :style="{ cursor: getCursorStyle('nw', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'nw', mask)"></div>
                                                <div class="absolute right-0 top-0 w-3 h-3" :style="{ cursor: getCursorStyle('ne', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'ne', mask)"></div>
                                                <div class="absolute left-0 bottom-0 w-3 h-3" :style="{ cursor: getCursorStyle('sw', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'sw', mask)"></div>
                                                <div class="absolute right-0 bottom-0 w-3 h-3" :style="{ cursor: getCursorStyle('se', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'se', mask)"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </template>
                        </template>

                        {{-- STAMP ORIGINAL --}}
                        <div x-show="pkg.stamp && !pdfError && !isStampBurned" class="absolute" :class="stampPositionClass('original')"
                            :key="`stamp-pdf-original-${stampConfig.original}`">
                            <div class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                :class="[
                                stampOriginClass('original'),
                                isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                            ]" style="transform: scale(0.45);">
                                <div class="w-full text-center border-b-2 py-0.5 px-4 font-semibold tracking-tight"
                                    :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                    <span x-text="stampTopLine('original')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold uppercase px-2"
                                        :class="isEngineering ? 'text-blue-700' : 'text-gray-600'"
                                        x-text="stampCenterOriginal()"></span>
                                </div>
                                <div class="w-full border-t-2 py-0.5 px-4 text-center font-semibold tracking-tight"
                                    :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                    <span x-text="stampBottomLine('original')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- STAMP COPY --}}
                        <div x-show="pkg.stamp && !pdfError && !isStampBurned" class="absolute" :class="stampPositionClass('copy')"
                            :key="`stamp-pdf-copy-${stampConfig.copy}`">
                            <div :class="stampOriginClass('copy')"
                                class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                style="transform: scale(0.45);">
                                <div
                                    class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                    <span x-text="stampTopLine('copy')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                        x-text="stampCenterCopy()"></span>
                                </div>
                                <div
                                    class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                    <span x-text="stampBottomLine('copy')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- STAMP OBSOLETE --}}
                        <div x-show="pkg.stamp?.is_obsolete && !pdfError && !isStampBurned" class="absolute" :class="stampPositionClass('obsolete')"
                            :key="`stamp-pdf-obsolete-${stampConfig.obsolete}`">
                            <div :class="stampOriginClass('obsolete')"
                                class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                style="transform: scale(0.45);">
                                <div
                                    class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                                    <span x-text="stampTopLine('obsolete')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                                        x-text="stampCenterObsolete()"></span>
                                </div>
                                <div class="w-full border-t-2 border-red-600 flex font-semibold tracking-tight">
                                    <div class="flex-1 border-r-2 border-red-600 text-center py-0.5 px-2">
                                        Name : <span x-text="obsoleteName()"></span>
                                    </div>
                                    <div class="flex-1 text-center py-0.5 px-2">
                                        Dept. : <span x-text="obsoleteDept()"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Solid Loading Overlay (PDF) --}}
                <div x-show="pdfLoading" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-20 rounded-lg">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-4"></i>
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Rendering PDF</span>
                    </div>
                </div>

                {{-- Solid Error Overlay (PDF) --}}
                <div x-show="pdfError" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-30 rounded-lg p-6 text-center">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-circle-exclamation text-xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">PDF Loading Failed</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 max-w-[240px] leading-relaxed line-clamp-2" x-text="pdfError"></p>
                    <button @click="loadFile(selectedFile, true)" 
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-md text-[11px] font-bold hover:bg-gray-800 dark:hover:bg-gray-200 transition-all shadow-sm">
                        <i class="fa-solid fa-rotate-right"></i> Try Again
                    </button>
                </div>
            </div>
        </template>

        {{-- TIFF VIEWER --}}
        <template x-if="isTiff(selectedFile?.name)">
            <div class="relative w-full overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                :class="isFullscreen ? 'h-full' : 'h-[70vh]'"
                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                <div class="w-full h-full flex items-center justify-center">
                    <div class="relative inline-block" :style="imageTransformStyle()">
                        <img x-ref="tifImg" alt="TIFF Preview" @load="onImageLoad()"
                            class="block pointer-events-none select-none max-w-full bg-white"
                            :class="isFullscreen ? 'max-h-full' : 'max-h-[70vh]'" />

                        {{-- WHITE BLOCKS (Masking) --}}
                        <template x-if="enableMasking && !tifError">
                             <template x-for="mask in masks" :key="mask.id">
                                <template x-if="mask">
                                    <div x-show="mask.visible" x-cloak :style="maskStyle(mask)"
                                        class="absolute bg-white/100 shadow-sm cursor-move"
                                        :class="{ 'z-50': mask.active, 'z-10': !mask.active }"
                                        @mousedown.stop.prevent="onMaskMouseDown($event, mask)" @click.stop="activateMask(mask)">

                                        <div x-show="mask.active" x-cloak class="absolute inset-0 border border-blue-500 pointer-events-none"></div>

                                        <div x-show="mask.active && mask.editable" x-cloak>
                                            <div class="absolute left-1/2 -translate-x-1/2 -top-8 w-5 h-5 bg-white/80 rounded-full shadow-md border border-gray-200 flex items-center justify-center cursor-alias z-10"
                                                @mousedown.stop.prevent="startMaskRotate($event, mask)">
                                                <i class="fa-solid fa-rotate text-blue-600 text-[10px]"></i>
                                            </div>
                                        </div>

                                        <template x-if="mask.active && mask.editable">
                                            <div>
                                                <div class="absolute inset-x-3 top-0 h-2" :style="{ cursor: getCursorStyle('n', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'n', mask)"></div>
                                                <div class="absolute inset-x-3 bottom-0 h-2" :style="{ cursor: getCursorStyle('s', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 's', mask)"></div>
                                                <div class="absolute inset-y-3 left-0 w-2" :style="{ cursor: getCursorStyle('w', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'w', mask)"></div>
                                                <div class="absolute inset-y-3 right-0 w-2" :style="{ cursor: getCursorStyle('e', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'e', mask)"></div>
                                                
                                                <div class="absolute left-0 top-0 w-3 h-3" :style="{ cursor: getCursorStyle('nw', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'nw', mask)"></div>
                                                <div class="absolute right-0 top-0 w-3 h-3" :style="{ cursor: getCursorStyle('ne', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'ne', mask)"></div>
                                                <div class="absolute left-0 bottom-0 w-3 h-3" :style="{ cursor: getCursorStyle('sw', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'sw', mask)"></div>
                                                <div class="absolute right-0 bottom-0 w-3 h-3" :style="{ cursor: getCursorStyle('se', mask.rotation) }" @mousedown.stop.prevent="startMaskResize($event, 'se', mask)"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </template>
                        </template>

                        {{-- STAMP ORIGINAL (Hidden if burned) --}}
                        <div x-show="pkg.stamp && !isStampBurned" class="absolute" :class="stampPositionClass('original')" 
                            :key="`stamp-tiff-original-${stampConfig.original}`">
                            <div class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                :class="[
                                stampOriginClass('original'),
                                isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                            ]" style="transform: scale(0.45);">
                                <div class="w-full text-center border-b-2 py-0.5 px-4 font-semibold tracking-tight"
                                    :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                    <span x-text="stampTopLine('original')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold uppercase px-2"
                                        :class="isEngineering ? 'text-blue-700' : 'text-gray-600'"
                                        x-text="stampCenterOriginal()"></span>
                                </div>
                                <div class="w-full border-t-2 py-0.5 px-4 text-center font-semibold tracking-tight"
                                    :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                    <span x-text="stampBottomLine('original')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- STAMP COPY (Hidden if burned) --}}
                        <div x-show="pkg.stamp && !isStampBurned" class="absolute" :class="stampPositionClass('copy')" 
                            :key="`stamp-tiff-copy-${stampConfig.copy}`">
                            <div :class="stampOriginClass('copy')"
                                class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                style="transform: scale(0.45);">
                                <div
                                    class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                    <span x-text="stampTopLine('copy')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                        x-text="stampCenterCopy()"></span>
                                </div>
                                <div
                                    class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                    <span x-text="stampBottomLine('copy')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- STAMP OBSOLETE (Hidden if burned) --}}
                        <div x-show="pkg.stamp?.is_obsolete && !isStampBurned" class="absolute" :class="stampPositionClass('obsolete')" 
                            :key="`stamp-tiff-obsolete-${stampConfig.obsolete}`">
                            <div :class="stampOriginClass('obsolete')"
                                class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                style="transform: scale(0.45);">
                                <div
                                    class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                                    <span x-text="stampTopLine('obsolete')"></span>
                                </div>
                                <div class="flex-1 flex items-center justify-center">
                                    <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                                        x-text="stampCenterObsolete()"></span>
                                </div>
                                <div class="w-full border-t-2 border-red-600 flex font-semibold tracking-tight">
                                    <div class="flex-1 border-r-2 border-red-600 text-center py-0.5 px-2">
                                        Name : <span x-text="obsoleteName()"></span>
                                    </div>
                                    <div class="flex-1 text-center py-0.5 px-2">
                                        Dept. : <span x-text="obsoleteDept()"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Solid Loading Overlay (TIFF) --}}
                <div x-show="tifLoading" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-20 rounded-lg">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-4"></i>
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Processing TIFF</span>
                    </div>
                </div>

                {{-- Solid Error Overlay (TIFF) --}}
                <div x-show="tifError" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-30 rounded-lg p-6 text-center">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-circle-exclamation text-xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">TIFF Loading Failed</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 max-w-[240px] leading-relaxed line-clamp-2" x-text="tifError"></p>
                    <button @click="loadFile(selectedFile, true)" 
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-md text-[11px] font-bold hover:bg-gray-800 dark:hover:bg-gray-200 transition-all shadow-sm">
                        <i class="fa-solid fa-rotate-right"></i> Try Again
                    </button>
                </div>
            </div>
        </template>

        {{-- HPGL VIEWER --}}
        <template x-if="isHpgl(selectedFile?.name)">
            <div class="relative w-full overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                :class="isFullscreen ? 'h-full' : 'h-[70vh]'"
                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                <div class="relative w-full h-full flex items-center justify-center" :style="imageTransformStyle()">

                    {{-- Wrapper with relative positioning for stamps --}}
                    <div class="relative inline-block">
                        <canvas x-ref="hpglCanvas" class="pointer-events-none select-none"></canvas>

                        {{-- Stamp overlay positioned exactly over the drawing area --}}
                        <div class="absolute pointer-events-none"
                            :style="`left: ${hpglDrawingBounds.left}px; top: ${hpglDrawingBounds.top}px; width: ${hpglDrawingBounds.width}px; height: ${hpglDrawingBounds.height}px;`">

                            {{-- STAMP ORIGINAL --}}
                            <div x-show="pkg.stamp && !hpglError && !isStampBurned" class="absolute" :class="stampPositionClass('original')" 
                                :key="`stamp-hpgl-original-${stampConfig.original}`">
                                <div :class="[
                                    stampOriginClass('original'),
                                    isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                                ]" class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                    style="transform: scale(0.45);">
                                    <div class="w-full text-center border-b-2 py-0.5 px-4 font-semibold tracking-tight"
                                        :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                        <span x-text="stampTopLine('original')"></span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center">
                                        <span class="text-xs font-extrabold uppercase px-2"
                                            :class="isEngineering ? 'text-blue-700' : 'text-gray-600'"
                                            x-text="stampCenterOriginal()"></span>
                                    </div>
                                    <div class="w-full border-t-2 py-0.5 px-4 text-center font-semibold tracking-tight"
                                        :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                        <span x-text="stampBottomLine('original')"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- STAMP COPY --}}
                            <div x-show="pkg.stamp && !hpglError && !isStampBurned" class="absolute" :class="stampPositionClass('copy')" 
                                :key="`stamp-hpgl-copy-${stampConfig.copy}`">
                                <div :class="stampOriginClass('copy')"
                                    class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                    style="transform: scale(0.45);">
                                    <div
                                        class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                        <span x-text="stampTopLine('copy')"></span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center">
                                        <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                            x-text="stampCenterCopy()"></span>
                                    </div>
                                    <div
                                        class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                        <span x-text="stampBottomLine('copy')"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- STAMP OBSOLETE --}}
                            <div x-show="pkg.stamp?.is_obsolete && !hpglError && !isStampBurned" class="absolute"
                                :class="stampPositionClass('obsolete')" 
                                :key="`stamp-hpgl-obsolete-${stampConfig.obsolete}`">
                                <div :class="stampOriginClass('obsolete')"
                                    class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                    style="transform: scale(0.45);">
                                    <div
                                        class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                                        <span x-text="stampTopLine('obsolete')"></span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center">
                                        <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                                            x-text="stampCenterObsolete()"></span>
                                    </div>
                                    <div class="w-full border-t-2 border-red-600 flex font-semibold tracking-tight">
                                        <div class="flex-1 border-r-2 border-red-600 text-center py-0.5 px-2">
                                            Name : <span x-text="obsoleteName()"></span>
                                        </div>
                                        <div class="flex-1 text-center py-0.5 px-2">
                                            Dept. : <span x-text="obsoleteDept()"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Solid Loading Overlay (HPGL) --}}
                <div x-show="hpglLoading" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-20 rounded-lg">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-4"></i>
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Plotting File</span>
                    </div>
                </div>

                {{-- Solid Error Overlay (HPGL) --}}
                <div x-show="hpglError" x-transition.opacity
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-30 rounded-lg p-6 text-center">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-circle-exclamation text-xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">HPGL Rendering Failed</h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 max-w-[240px] leading-relaxed line-clamp-2" x-text="hpglError"></p>
                    <button @click="loadFile(selectedFile, true)" 
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-md text-[11px] font-bold hover:bg-gray-800 dark:hover:bg-gray-200 transition-all shadow-sm">
                        <i class="fa-solid fa-rotate-right"></i> Try Again
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

        {{-- 3D CAD VIEWER (IGES, STEP, STL, OBJ) - ADVANCED FULL FEATURED --}}
        <template x-if="isCad(selectedFile?.name)">
            <div x-ref="ref3dContainer" class="w-full flex flex-col min-h-[500px] transition-all duration-300"
                :class="isFullscreen ? 'h-[80vh] mb-8' : 'h-[85vh]'">

                <div
                    class="flex-1 relative border border-gray-200 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-900 overflow-hidden group">

                    {{-- Part List Toggle Button --}}
                    <button @click="isPartListOpen = !isPartListOpen" x-show="cadPartsList.length > 0 && !iges.loading && !iges.error"
                        class="absolute top-3 left-3 z-10 px-3 py-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-md border border-gray-200 dark:border-gray-700 rounded text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
                        <i class="fa-solid" :class="isPartListOpen ? 'fa-xmark' : 'fa-list-tree'"></i>
                        <span x-text="isPartListOpen ? 'Close List' : 'Part List'"></span>
                        <span class="bg-gray-200 dark:bg-gray-600 text-[10px] px-1.5 rounded-full ml-1"
                            x-text="cadPartsList.length"></span>
                    </button>

                    {{-- Part List Panel --}}
                    <div x-show="isPartListOpen && !iges.loading && !iges.error" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-x-2"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 -translate-x-2"
                        class="absolute top-12 left-3 bottom-3 z-10 w-64 flex flex-col bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">

                        <div
                            class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-700/50 flex justify-between items-center flex-shrink-0">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-100">Assembly Tree</span>
                            <button @click="isPartListOpen = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-1 custom-scrollbar min-h-0">
                            <ul class="space-y-0.5">
                                <template x-for="part in cadPartsList" :key="part.uuid">
                                    <li @click="highlightPart(part.uuid)"
                                        class="cursor-pointer px-3 py-2 rounded text-xs flex items-center gap-2 truncate select-none transition-colors border border-transparent"
                                        :class="selectedPartUuid === part.uuid ?
                                            'bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800 font-medium' :
                                            'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                                        <i class="fa-solid fa-cube text-[10px]"
                                            :class="selectedPartUuid === part.uuid ? 'text-blue-500' : 'text-gray-400'"></i>
                                        <span x-text="part.name" class="truncate"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        {{-- Part Opacity Control --}}
                        <div x-show="selectedPartUuid" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="p-3 border-t border-blue-100 dark:border-gray-700 bg-blue-50/80 dark:bg-gray-800 flex-shrink-0">

                            <div class="flex items-center justify-between gap-3">
                                <span
                                    class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider min-w-[50px]">
                                    Opacity
                                </span>

                                <input type="range" min="0.1" max="1.0" step="0.1" x-model.number="partOpacity"
                                    @input="updatePartOpacity()"
                                    class="flex-1 h-2 bg-blue-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer accent-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            </div>

                            <div class="flex justify-between mt-1 px-1">
                                <span class="text-[9px] text-gray-400">10%</span>
                                <span class="text-[9px] font-mono text-blue-600"
                                    x-text="Math.round(partOpacity * 100) + '%'"></span>
                                <span class="text-[9px] text-gray-400">100%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Measurement Toggle Button --}}
                    <button @click="isMeasureListOpen = !isMeasureListOpen" x-show="isMeasureActive && !iges.loading && !iges.error"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                        :title="isMeasureListOpen ? 'Hide Measurements' : 'Show Measurements'"
                        class="absolute top-3 right-14 z-10 px-3 py-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-md border border-gray-200 dark:border-gray-700 rounded text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
                        <i class="fa-solid" :class="isMeasureListOpen ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span x-text="isMeasureListOpen ? 'Hide Info' : 'Show Info'"></span>
                        <span class="bg-gray-200 dark:bg-gray-600 text-[10px] px-1.5 rounded-full ml-1"
                            x-text="iges.measure.results.length" x-show="iges.measure.results.length > 0"></span>
                    </button>

                    {{-- Measurement List Panel --}}
                    <div x-show="isMeasureListOpen && !iges.loading && !iges.error" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-x-2"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-2"
                        class="absolute top-12 right-3 z-10 w-64 flex flex-col bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden max-h-[60vh]">


                        <div
                            class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-700/50 flex-shrink-0">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100">Measurements</span>
                                <button @click="clearMeasurements()"
                                    class="text-[10px] text-red-500 hover:underline">Clear
                                    All</button>
                            </div>
                            {{-- Instruction Display --}}
                            <div x-show="iges.measure.enabled" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="text-xs text-blue-600 dark:text-blue-400 italic mt-1 flex items-center gap-1">
                                <i class="fa-solid fa-info-circle text-[10px]"></i>
                                <span x-text="iges.measure.hoverInstruction"></span>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-1 custom-scrollbar min-h-0">
                            <template x-if="iges.measure.results.length === 0">
                                <div class="p-4 text-center text-gray-400 dark:text-gray-500 text-xs italic">
                                    No measurements yet.<br>Select points to measure.
                                </div>
                            </template>
                            <ul class="space-y-1 p-1">
                                <template x-for="(res, idx) in iges.measure.results" :key="idx">
                                    <li
                                        class="bg-white dark:bg-gray-900 p-2 rounded border border-gray-200 dark:border-gray-600 shadow-sm relative group hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                                        <button @click="deleteMeasurement(idx)"
                                            class="absolute top-1 right-1 text-gray-300 hover:text-red-500 p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="fa-solid fa-times-circle text-xs"></i>
                                        </button>

                                        <div class="flex items-center gap-2 mb-1.5">
                                            <div
                                                class="w-5 h-5 rounded bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                                <i class="fa-solid text-[10px]" :class="{
                                                    'fa-ruler-horizontal': res.type === 'point',
                                                    'fa-minus': res.type === 'edge',
                                                    'fa-angle-left': res.type === 'angle',
                                                    'fa-circle-notch': res.type === 'radius',
                                                    'fa-vector-square': res.type === 'face'
                                                }"></i>
                                            </div>
                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase"
                                                x-text="res.type"></span>
                                        </div>

                                        <div class="space-y-1 pl-1">
                                            <template x-if="res.distance !== undefined">
                                                <div class="flex justify-between items-baseline text-xs">
                                                    <span class="text-gray-500">Dist:</span>
                                                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400"
                                                        x-text="Number(res.distance).toFixed(2) + ' mm'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.angle !== undefined">
                                                <div class="flex justify-between items-baseline text-xs">
                                                    <span class="text-gray-500">Angle:</span>
                                                    <span
                                                        class="font-mono font-bold text-purple-600 dark:text-purple-400"
                                                        x-text="Number(res.angle).toFixed(2) + '°'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.radius !== undefined">
                                                <div class="flex justify-between items-baseline text-xs">
                                                    <span class="text-gray-500">Radius:</span>
                                                    <span class="font-mono font-bold text-green-600 dark:text-green-400"
                                                        x-text="Number(res.radius).toFixed(2) + ' mm'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.diameter !== undefined">
                                                <div class="flex justify-between items-baseline text-xs">
                                                    <span class="text-gray-500">Diameter:</span>
                                                    <span class="font-mono font-bold text-teal-600 dark:text-teal-400"
                                                        x-text="Number(res.diameter).toFixed(2) + ' mm'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.area !== undefined">
                                                <div class="flex justify-between items-baseline text-xs">
                                                    <span class="text-gray-500">Area:</span>
                                                    <span
                                                        class="font-mono font-bold text-orange-600 dark:text-orange-400"
                                                        x-text="Number(res.area).toFixed(2) + ' mm²'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.deltaX !== undefined">
                                                <div
                                                    class="flex gap-1 pt-1 mt-1 border-t border-gray-100 dark:border-gray-700 text-[9px] font-mono justify-between">
                                                    <span
                                                        class="text-red-500 bg-red-50 dark:bg-red-900/20 rounded px-1">ΔX:
                                                        <span x-text="Number(res.deltaX).toFixed(1)"></span></span>
                                                    <span
                                                        class="text-green-500 bg-green-50 dark:bg-green-900/20 rounded px-1">ΔY:
                                                        <span x-text="Number(res.deltaY).toFixed(1)"></span></span>
                                                    <span
                                                        class="text-blue-500 bg-blue-50 dark:bg-blue-900/20 rounded px-1">ΔZ:
                                                        <span x-text="Number(res.deltaZ).toFixed(1)"></span></span>
                                                </div>
                                            </template>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    {{-- Fullscreen Toggle --}}
                    <button @click="toggleFullscreen()" title="Toggle Fullscreen"
                        x-show="!iges.loading && !iges.error && @json($enableFullscreen)"
                        class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-md border border-gray-200 dark:border-gray-700 rounded text-gray-600 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                        <i class="fa-solid" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                    </button>

                    {{-- 3D Viewer Container --}}
                    <div x-ref="igesWrap" class="w-full h-full bg-black/5 cursor-grab active:cursor-grabbing">
                    </div>

                    {{-- Solid Loading Overlay (CAD) --}}
                    <div x-show="iges.loading"
                        class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-20 rounded-lg">
                        <div class="flex flex-col items-center">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-4"></i>
                            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Processing Geometry</span>
                        </div>
                    </div>

                    {{-- Solid Error Overlay (CAD) --}}
                    <div x-show="iges.error"
                        class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-30 rounded-lg p-6 text-center">
                        <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mb-4">
                            <i class="fa-solid fa-circle-exclamation text-xl"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">CAD Engine Error</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 max-w-[240px] leading-relaxed line-clamp-2" x-text="iges.error"></p>
                        <button @click="loadFile(selectedFile)" 
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-md text-[11px] font-bold hover:bg-gray-800 dark:hover:bg-gray-200 transition-all shadow-sm">
                            <i class="fa-solid fa-rotate-right"></i> Try Again
                        </button>
                    </div>

                    {{-- Floating 3D Navigation Controls (Right) --}}
                    <div x-show="isCad(selectedFile?.name) && !iges.loading && !iges.error"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        class="absolute bottom-1/2 translate-y-1/2 right-6 flex flex-col items-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-2xl p-1.5 z-10 gap-1.5">

                        <button @click="zoom3d(1.25)"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 transition-all active:scale-75"
                            title="Zoom In (+)">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </button>

                        <div class="w-6 h-px bg-gray-200 dark:bg-gray-700 mx-1"></div>

                        <button @click="zoom3d(0.8)"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 transition-all active:scale-75"
                            title="Zoom Out (-)">
                            <i class="fa-solid fa-minus text-sm"></i>
                        </button>

                        <div class="w-6 h-px bg-gray-200 dark:bg-gray-700 mx-1"></div>

                        <button @click="resetCamera3d()"
                             class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 transition-all active:scale-75"
                            title="Reset View (Home)">
                             <i class="fa-solid fa-house-chimney text-xs"></i>
                        </button>
                    </div>
                     {{-- Advanced 3D Controls Toolbar (Bottom) --}}
                    <div x-show="!iges.loading && !iges.error && @json($showAdvanced3DControls)"
                        class="absolute left-1/2 -translate-x-1/2 z-10 bg-white/40 dark:bg-gray-900/50 backdrop-blur-xl p-2 lg:p-3 rounded-2xl border border-white/40 dark:border-gray-700/50 shadow-lg min-w-[300px] transition-all duration-500 flex flex-wrap items-center justify-center gap-2 lg:gap-3 select-none origin-bottom"
                        :class="isFullscreen ? 'scale-100 bottom-8' : 'scale-90 bottom-6'"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        {{-- Unified Tool Group --}}
                        <div class="flex items-center justify-center gap-2 lg:gap-3">

                            {{-- GROUP 1: Display Settings --}}
                            <div
                                class="flex items-center gap-1.5 lg:gap-2 px-2 lg:px-2.5 py-1 lg:py-1.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <span
                                    class="hidden lg:inline text-[9px] font-bold text-gray-400 uppercase tracking-wider px-1">Display</span>

                                <div
                                    class="inline-flex bg-white dark:bg-gray-700 p-0.5 rounded border border-gray-200 dark:border-gray-600">
                                    <button @click="setDisplayStyle('shaded')"
                                        class="px-2 lg:px-2.5 py-1 text-[10px] lg:text-xs font-semibold rounded transition-all"
                                        :class="currentStyle === 'shaded' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300'">
                                        Shaded
                                    </button>
                                    <button @click="setDisplayStyle('shaded-edges')"
                                        class="px-2 lg:px-2.5 py-1 text-[10px] lg:text-xs font-semibold rounded transition-all"
                                        :class="currentStyle === 'shaded-edges' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300'">
                                        Edges
                                    </button>
                                    <button @click="setDisplayStyle('wireframe')"
                                        class="px-2 lg:px-2.5 py-1 text-[10px] lg:text-xs font-semibold rounded transition-all"
                                        :class="currentStyle === 'wireframe' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300'">
                                        Wire
                                    </button>
                                </div>

                                <div class="h-6 lg:h-7 w-px bg-gray-300 dark:bg-gray-600"></div>

                                <div class="relative">
                                    <button @click="isMatMenuOpen = !isMatMenuOpen"
                                        @click.outside="isMatMenuOpen = false" title="Material"
                                        class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 transition text-xs lg:text-sm"
                                        :class="activeMaterial !== 'default' ? 'text-purple-600 border-purple-300 bg-purple-50' : 'text-gray-600 dark:text-gray-200'">
                                        <i class="fa-solid fa-fill-drip"></i>
                                    </button>

                                    <div x-show="isMatMenuOpen" x-transition
                                        class="absolute bottom-full left-0 mb-2 p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-32 flex flex-col gap-1">
                                        <div
                                            class="text-[9px] font-bold text-gray-400 px-2 py-1 uppercase tracking-wider">
                                            Material</div>

                                        <button @click="setMaterialMode('clay'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='clay' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-circle text-orange-200 text-[8px]"></i> Clay
                                        </button>

                                        <button @click="setMaterialMode('metal'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='metal' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-circle text-gray-400 text-[8px]"></i> Metal
                                        </button>

                                        <button @click="setMaterialMode('normal'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='normal' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-circle text-purple-400 text-[8px]"></i> Normal
                                        </button>

                                        <button @click="setMaterialMode('glass'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='glass' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-square-full text-blue-200 opacity-50 text-[8px]"></i>
                                            Glass
                                        </button>

                                        <button @click="setMaterialMode('ecoat'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='ecoat' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-square text-gray-500 text-[10px]"></i> E-Coat
                                        </button>

                                        <button @click="setMaterialMode('raw-steel'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='raw-steel' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-square text-gray-300 text-[10px]"></i> Raw Steel
                                        </button>

                                        <button @click="setMaterialMode('aluminum'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='aluminum' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i
                                                class="fa-solid fa-square text-white border border-gray-300 text-[10px]"></i>
                                            Aluminum
                                        </button>

                                        <button @click="setMaterialMode('yellow-zinc'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='yellow-zinc' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-circle text-yellow-500 text-[8px]"></i> Yellow Zinc
                                        </button>

                                        <button @click="setMaterialMode('red-oxide'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='red-oxide' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-circle text-red-700 text-[8px]"></i> Red Oxide
                                        </button>

                                        <button @click="setMaterialMode('dark'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='dark' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-circle text-gray-800 text-[8px]"></i> Dark
                                        </button>

                                        <div class="h-px bg-gray-100 dark:bg-gray-700 my-0.5"></div>

                                        <button @click="setMaterialMode('default'); isMatMenuOpen=false"
                                            class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                            :class="activeMaterial==='default' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                            <i class="fa-solid fa-circle text-gray-400 text-[8px]"></i> Default
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- GROUP 2: View Controls --}}
                            <div
                                class="flex items-center gap-1.5 lg:gap-2 px-2 lg:px-2.5 py-1 lg:py-1.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <span
                                    class="hidden lg:inline text-[9px] font-bold text-gray-400 uppercase tracking-wider px-1">View</span>

                                <button @click="toggleCameraMode()"
                                    :title="cameraMode === 'perspective' ? 'View: Perspective (C)' : 'View: Orthographic (C)'"
                                    class="w-7 h-7 lg:w-8 lg:h-8 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 flex flex-col items-center justify-center gap-0.5 transition-all active:scale-95">
                                    <i class="fa-solid text-[10px] lg:text-xs"
                                        :class="cameraMode === 'perspective' ? 'fa-cube' : 'fa-border-none'"></i>
                                    <span x-text="cameraMode === 'perspective' ? 'Persp' : 'Ortho'"
                                        class="text-[7px] lg:text-[8px] font-bold leading-none"></span>
                                </button>

                                <div class="relative">
                                    <button @click="isViewMenuOpen = !isViewMenuOpen"
                                        @click.outside="isViewMenuOpen = false" title="Standard Views"
                                        class="w-7 h-7 lg:w-8 lg:h-8 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 flex items-center justify-center transition text-xs lg:text-sm">
                                        <i class="fa-solid fa-dice-d6"></i>
                                    </button>

                                    <div x-show="isViewMenuOpen" x-transition
                                        class="absolute bottom-full left-0 mb-2 p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-28 lg:w-32 flex flex-col gap-1">
                                        <div
                                            class="text-[9px] font-bold text-gray-400 px-2 py-1 uppercase tracking-wider">
                                            Views
                                        </div>
                                        <button @click="setStandardView('front'); isViewMenuOpen=false"
                                            class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Front
                                            (F)</button>
                                        <button @click="setStandardView('back'); isViewMenuOpen=false"
                                            class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Back
                                            (B)</button>
                                        <button @click="setStandardView('top'); isViewMenuOpen=false"
                                            class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Top
                                            (T)</button>
                                        <button @click="setStandardView('bottom'); isViewMenuOpen=false"
                                            class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Bottom
                                            (D)</button>
                                        <button @click="setStandardView('left'); isViewMenuOpen=false"
                                            class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Left
                                            (L)</button>
                                        <button @click="setStandardView('right'); isViewMenuOpen=false"
                                            class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Right
                                            (R)</button>
                                        <div class="h-px bg-gray-100 dark:bg-gray-700 my-0.5"></div>
                                        <button @click="setStandardView('iso'); isViewMenuOpen=false"
                                            class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Isometric
                                            (I)</button>
                                    </div>
                                </div>

                                <button @click="toggleAutoRotate()" title="Auto Rotate (Space)"
                                    class="w-7 h-7 lg:w-8 lg:h-8 rounded border transition flex items-center justify-center text-xs lg:text-sm"
                                    :class="autoRotate ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 border-gray-200 dark:border-gray-700'">
                                    <i class="fa-solid fa-rotate" :class="autoRotate ? 'fa-spin' : ''"></i>
                                </button>

                                <button @click="toggleHeadlight()" title="Headlight (H)"
                                    class="w-7 h-7 lg:w-8 lg:h-8 rounded border transition flex items-center justify-center text-xs lg:text-sm"
                                    :class="headlight.enabled ? 'bg-yellow-500 text-white border-yellow-500' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 border-gray-200 dark:border-gray-700'">
                                    <i class="fa-solid fa-lightbulb"></i>
                                </button>
                            </div>

                            {{-- GROUP 3: Analysis Tools --}}
                            <div
                                class="flex items-center gap-1.5 lg:gap-2 px-2 lg:px-2.5 py-1 lg:py-1.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <span
                                    class="hidden lg:inline text-[9px] font-bold text-gray-400 uppercase tracking-wider px-1">Analysis</span>

                                <div class="relative">
                                    <button @click="toggleExplodedPanel()"
                                        :class="iges.exploded.enabled ? 'text-blue-600 bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-700 shadow-sm' : 'text-gray-600 dark:text-gray-400'"
                                        class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-xs lg:text-sm active:scale-95"
                                        title="Exploded View (X)">
                                        <i class="fa-solid fa-expand-arrows-alt"
                                            :class="iges.exploded.enabled ? 'scale-110' : ''"></i>
                                    </button>

                                    {{-- Professional Explode Panel --}}
                                    <div x-show="iges.exploded && iges.exploded.panelOpen"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        @click.outside="iges.exploded.panelOpen = false"
                                        class="absolute bottom-full mb-2 left-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-48 lg:w-56 overflow-hidden">

                                        <div
                                            class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                                            <div class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-expand-arrows-alt text-blue-500 text-[10px]"></i>
                                                <span
                                                    class="text-[10px] lg:text-xs font-bold text-gray-700 dark:text-gray-200">Exploded
                                                    View</span>
                                            </div>
                                            <button @click="toggleExplodedView(); iges.exploded.panelOpen = false"
                                                class="text-[9px] text-red-600 dark:text-red-400 hover:underline font-medium">Disable</button>
                                        </div>

                                        <div class="p-3">
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Explosion
                                                        Factor</span>
                                                    <div
                                                        class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-[10px] font-mono font-bold text-blue-600 dark:text-blue-400">
                                                        <span x-text="Math.round(iges.exploded.factor * 100)"></span>%
                                                    </div>
                                                </div>

                                                <input type="range" min="0" max="1" step="0.01"
                                                    x-model.number="iges.exploded.factor"
                                                    @input="updateExplodeFactor(iges.exploded.factor)"
                                                    class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">

                                                <div class="flex items-center justify-between gap-2">
                                                    <button @click="iges.exploded.factor = 0; updateExplodeFactor(0)"
                                                        class="flex-1 text-[9px] py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                        Reset
                                                    </button>
                                                    <button @click="iges.exploded.factor = 1; updateExplodeFactor(1)"
                                                        class="flex-1 text-[9px] py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-semibold">
                                                        Maximum
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Section Cut Multi-Axis Panel --}}
                                <div class="relative">
                                    {{-- Toggle Button --}}
                                    <button @click="toggleClippingPanel()"
                                        :class="hasActiveClipping ? 'text-blue-600 bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-700 shadow-sm' : 'text-gray-600 dark:text-gray-400'"
                                        class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-xs lg:text-sm active:scale-95"
                                        title="Section Cut (S)">
                                        <i class="fa-solid fa-scissors rotate-90"
                                            :class="hasActiveClipping ? 'scale-110' : ''"></i>
                                    </button>

                                    {{-- Compact Panel --}}
                                    <div x-show="iges.clipping && iges.clipping.panelOpen"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        @click.outside="iges.clipping.panelOpen = false"
                                        class="absolute bottom-full mb-2 right-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-64">

                                        {{-- Header --}}
                                        <div
                                            class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-600">
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Section
                                                Cut</span>
                                            <button @click="resetAllClipping()"
                                                class="text-[10px] text-red-600 dark:text-red-400 hover:underline font-medium"
                                                x-show="hasActiveClipping">
                                                Reset
                                            </button>
                                        </div>

                                        {{-- Compact Axis Controls --}}
                                        <div class="p-2 space-y-1.5">
                                            {{-- X Axis --}}
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input type="checkbox" :checked="iges.clipping.x.enabled"
                                                        @change="toggleAxisClipping('x')"
                                                        class="rounded text-red-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                    <div
                                                        class="w-5 h-5 rounded bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                        <span
                                                            class="text-[10px] font-bold text-red-600 dark:text-red-400">X</span>
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-700 dark:text-gray-300 flex-1">X-Axis</span>

                                                    <button @click="togglePlaneHelper('x')"
                                                        x-show="iges.clipping.x.enabled"
                                                        :class="iges.clipping.x.showHelper ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                        title="Toggle Plane Helper">
                                                        <i class="fa-solid fa-eye text-[9px]"></i>
                                                    </button>

                                                    <button @click="flipAxis('x')" x-show="iges.clipping.x.enabled"
                                                        :class="iges.clipping.x.flipped ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                        title="Flip Direction">
                                                        <i class="fa-solid fa-right-left text-[9px]"></i>
                                                    </button>
                                                </div>

                                                <div x-show="iges.clipping.x.enabled" x-transition
                                                    class="pl-6 space-y-1.5">
                                                    <div
                                                        class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                        <span>Position:</span>
                                                        <span
                                                            class="font-mono font-semibold text-red-600 dark:text-red-400"
                                                            x-text="iges.clipping.x.value.toFixed(2)"></span>
                                                    </div>

                                                    <input type="range"
                                                        :min="iges.clipping.x.min !== undefined ? iges.clipping.x.min : iges.clipping.min"
                                                        :max="iges.clipping.x.max !== undefined ? iges.clipping.x.max : iges.clipping.max"
                                                        x-model.number="iges.clipping.x.value"
                                                        @input="updateAxisClipping('x')"
                                                        class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-red-600">

                                                    <div class="flex items-center gap-1">
                                                        <button @click="decrementAxisValue('x')"
                                                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 hover:text-red-600 transition"
                                                            title="Decrease">
                                                            <i class="fa-solid fa-minus text-[9px]"></i>
                                                        </button>

                                                        <input type="number"
                                                            :min="iges.clipping.x.min !== undefined ? iges.clipping.x.min : iges.clipping.min"
                                                            :max="iges.clipping.x.max !== undefined ? iges.clipping.x.max : iges.clipping.max"
                                                            :step="iges.clipping.step"
                                                            x-model.number="iges.clipping.x.value"
                                                            @input="setAxisValueDirect('x', $event.target.value)"
                                                            class="flex-1 px-2 py-1 text-[10px] text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-red-500 focus:border-red-500">

                                                        <button @click="incrementAxisValue('x')"
                                                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 hover:text-red-600 transition"
                                                            title="Increase">
                                                            <i class="fa-solid fa-plus text-[9px]"></i>
                                                        </button>
                                                    </div>

                                                    <div class="flex items-center gap-2 pt-1">
                                                        <label
                                                            class="flex items-center gap-1 text-[10px] text-gray-600 dark:text-gray-400 cursor-pointer">
                                                            <input type="checkbox" x-model="iges.clipping.x.showCap"
                                                                @change="toggleSectionCap('x')"
                                                                class="rounded text-red-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3 h-3">
                                                            <span>Show Cap</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Y Axis --}}
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input type="checkbox" :checked="iges.clipping.y.enabled"
                                                        @change="toggleAxisClipping('y')"
                                                        class="rounded text-green-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                    <div
                                                        class="w-5 h-5 rounded bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                        <span
                                                            class="text-[10px] font-bold text-green-600 dark:text-green-400">Y</span>
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-700 dark:text-gray-300 flex-1">Y-Axis</span>

                                                    <button @click="togglePlaneHelper('y')"
                                                        x-show="iges.clipping.y.enabled"
                                                        :class="iges.clipping.y.showHelper ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                        title="Toggle Plane Helper">
                                                        <i class="fa-solid fa-eye text-[9px]"></i>
                                                    </button>

                                                    <button @click="flipAxis('y')" x-show="iges.clipping.y.enabled"
                                                        :class="iges.clipping.y.flipped ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                        title="Flip Direction">
                                                        <i class="fa-solid fa-right-left text-[9px]"></i>
                                                    </button>
                                                </div>

                                                <div x-show="iges.clipping.y.enabled" x-transition
                                                    class="pl-6 space-y-1.5">
                                                    <div
                                                        class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                        <span>Position:</span>
                                                        <span
                                                            class="font-mono font-semibold text-green-600 dark:text-green-400"
                                                            x-text="iges.clipping.y.value.toFixed(2)"></span>
                                                    </div>

                                                    <input type="range"
                                                        :min="iges.clipping.y.min !== undefined ? iges.clipping.y.min : iges.clipping.min"
                                                        :max="iges.clipping.y.max !== undefined ? iges.clipping.y.max : iges.clipping.max"
                                                        x-model.number="iges.clipping.y.value"
                                                        @input="updateAxisClipping('y')"
                                                        class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-green-600">

                                                    <div class="flex items-center gap-1">
                                                        <button @click="decrementAxisValue('y')"
                                                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-green-100 dark:hover:bg-green-900/30 text-gray-600 dark:text-gray-300 hover:text-green-600 transition"
                                                            title="Decrease">
                                                            <i class="fa-solid fa-minus text-[9px]"></i>
                                                        </button>

                                                        <input type="number"
                                                            :min="iges.clipping.y.min !== undefined ? iges.clipping.y.min : iges.clipping.min"
                                                            :max="iges.clipping.y.max !== undefined ? iges.clipping.y.max : iges.clipping.max"
                                                            :step="iges.clipping.step"
                                                            x-model.number="iges.clipping.y.value"
                                                            @input="setAxisValueDirect('y', $event.target.value)"
                                                            class="flex-1 px-2 py-1 text-[10px] text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-green-500 focus:border-green-500">

                                                        <button @click="incrementAxisValue('y')"
                                                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-green-100 dark:hover:bg-green-900/30 text-gray-600 dark:text-gray-300 hover:text-green-600 transition"
                                                            title="Increase">
                                                            <i class="fa-solid fa-plus text-[9px]"></i>
                                                        </button>
                                                    </div>

                                                    <div class="flex items-center gap-2 pt-1">
                                                        <label
                                                            class="flex items-center gap-1 text-[10px] text-gray-600 dark:text-gray-400 cursor-pointer">
                                                            <input type="checkbox" x-model="iges.clipping.y.showCap"
                                                                @change="toggleSectionCap('y')"
                                                                class="rounded text-green-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3 h-3">
                                                            <span>Show Cap</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Z Axis --}}
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input type="checkbox" :checked="iges.clipping.z.enabled"
                                                        @change="toggleAxisClipping('z')"
                                                        class="rounded text-blue-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                    <div
                                                        class="w-5 h-5 rounded bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                        <span
                                                            class="text-[10px] font-bold text-blue-600 dark:text-blue-400">Z</span>
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-700 dark:text-gray-300 flex-1">Z-Axis</span>

                                                    <button @click="togglePlaneHelper('z')"
                                                        x-show="iges.clipping.z.enabled"
                                                        :class="iges.clipping.z.showHelper ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                        title="Toggle Plane Helper">
                                                        <i class="fa-solid fa-eye text-[9px]"></i>
                                                    </button>

                                                    <button @click="flipAxis('z')" x-show="iges.clipping.z.enabled"
                                                        :class="iges.clipping.z.flipped ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                        title="Flip Direction">
                                                        <i class="fa-solid fa-right-left text-[9px]"></i>
                                                    </button>
                                                </div>

                                                <div x-show="iges.clipping.z.enabled" x-transition
                                                    class="pl-6 space-y-1.5">
                                                    <div
                                                        class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                        <span>Position:</span>
                                                        <span
                                                            class="font-mono font-semibold text-blue-600 dark:text-blue-400"
                                                            x-text="iges.clipping.z.value.toFixed(2)"></span>
                                                    </div>

                                                    <input type="range"
                                                        :min="iges.clipping.z.min !== undefined ? iges.clipping.z.min : iges.clipping.min"
                                                        :max="iges.clipping.z.max !== undefined ? iges.clipping.z.max : iges.clipping.max"
                                                        x-model.number="iges.clipping.z.value"
                                                        @input="updateAxisClipping('z')"
                                                        class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">

                                                    <div class="flex items-center gap-1">
                                                        <button @click="decrementAxisValue('z')"
                                                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-gray-600 dark:text-gray-300 hover:text-blue-600 transition"
                                                            title="Decrease">
                                                            <i class="fa-solid fa-minus text-[9px]"></i>
                                                        </button>

                                                        <input type="number"
                                                            :min="iges.clipping.z.min !== undefined ? iges.clipping.z.min : iges.clipping.min"
                                                            :max="iges.clipping.z.max !== undefined ? iges.clipping.z.max : iges.clipping.max"
                                                            :step="iges.clipping.step"
                                                            x-model.number="iges.clipping.z.value"
                                                            @input="setAxisValueDirect('z', $event.target.value)"
                                                            class="flex-1 px-2 py-1 text-[10px] text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">

                                                        <button @click="incrementAxisValue('z')"
                                                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-gray-600 dark:text-gray-300 hover:text-blue-600 transition"
                                                            title="Increase">
                                                            <i class="fa-solid fa-plus text-[9px]"></i>
                                                        </button>
                                                    </div>

                                                    <div class="flex items-center gap-2 pt-1">
                                                        <label
                                                            class="flex items-center gap-1 text-[10px] text-gray-600 dark:text-gray-400 cursor-pointer">
                                                            <input type="checkbox" x-model="iges.clipping.z.showCap"
                                                                @change="toggleSectionCap('z')"
                                                                class="rounded text-blue-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3 h-3">
                                                            <span>Show Cap</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Measure Tool (Inside GROUP 3) --}}
                                <button @click="toggleMeasure()" title="Measure Tool (M)"
                                    :class="iges.measure.enabled ? 'text-blue-600 bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-700 shadow-sm' : 'text-gray-600 dark:text-gray-400'"
                                    class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-xs lg:text-sm active:scale-95">
                                    <i class="fa-solid fa-ruler-combined"
                                        :class="iges.measure.enabled ? 'scale-110' : ''"></i>
                                </button>

                            </div>

                            {{-- Utilities Section --}}
                            <div class="h-6 lg:h-7 w-px bg-gray-300 dark:bg-gray-600"></div>

                            <button @click="takeScreenshot()" title="Screenshot"
                                class="w-7 h-7 lg:w-8 lg:h-8 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 flex items-center justify-center transition text-xs lg:text-sm active:scale-90 shadow-sm">
                                <i class="fa-solid fa-camera"></i>
                            </button>
                        </div>
                    </div>

                    {{-- NEW MEASURE TOOLBAR (Floating at Top Center of Viewer) --}}
                    <div x-show="iges.measure.enabled && !iges.loading && !iges.error" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute bottom-24 left-1/2 -translate-x-1/2 z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg flex items-center p-1 gap-1 transition-all duration-300">

                        {{-- Mode Buttons --}}
                        <div class="flex items-center gap-0.5 border-r border-gray-200 dark:border-gray-600 pr-1 mr-1">
                            <button @click="setMeasureMode('point')"
                                :class="iges.measure.mode === 'point' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                class="p-2 rounded text-xs transition relative group" title="Point to Point">
                                <i class="fa-solid fa-ruler-horizontal"></i>
                            </button>

                            <button @click="setMeasureMode('edge')"
                                :class="iges.measure.mode === 'edge' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                class="p-2 rounded text-xs transition relative group" title="Edge Length">
                                <i class="fa-solid fa-minus"></i>
                            </button>

                            <button @click="setMeasureMode('angle')"
                                :class="iges.measure.mode === 'angle' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                class="p-2 rounded text-xs transition relative group" title="Angle (3 Points)">
                                <i class="fa-solid fa-angle-left"></i>
                            </button>

                            <button @click="setMeasureMode('radius')"
                                :class="iges.measure.mode === 'radius' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                class="p-2 rounded text-xs transition relative group" title="Radius (3 Points)">
                                <i class="fa-regular fa-circle"></i>
                            </button>

                            {{-- NEW: FACE AREA MODE --}}
                            <button @click="setMeasureMode('face')"
                                :class="iges.measure.mode === 'face' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                class="p-2 rounded text-xs transition relative group" title="Face Area">
                                <i class="fa-solid fa-vector-square"></i>
                            </button>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-0.5">
                            <button @click="iges.measure.snap.enabled = !iges.measure.snap.enabled"
                                class="p-2 rounded text-xs transition relative group"
                                :class="iges.measure.snap.enabled ? 'text-green-600 hover:bg-green-50 dark:text-green-400' : 'text-gray-400 hover:bg-gray-100'"
                                title="Toggle Snap">
                                <i class="fa-solid fa-magnet"></i>
                            </button>

                            <button @click="clearMeasurements()"
                                class="p-2 rounded text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition relative group"
                                title="Clear All">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>

                            <button @click="toggleMeasure()"
                                class="p-2 rounded text-xs text-gray-500 hover:bg-gray-100 transition relative group"
                                title="Close Measure Tool">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                    {{-- Dynamic Instruction --}}
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900/80 backdrop-blur text-white text-[10px] px-3 py-1.5 rounded-full shadow-sm whitespace-nowrap pointer-events-none transition-all duration-300"
                        x-text="iges.measure.hoverInstruction" x-show="iges.measure.enabled">
                    </div>

                    {{-- Unified overlays are now inside view container --}}
                </div>
            </div>
        </template>


        <template
            x-if="!isPreviewable2D(selectedFile?.name) && !isCad(selectedFile?.name)">
            <div class="flex flex-col items-center justify-center py-24 px-6 text-center bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 min-h-[500px] h-[75vh] w-full mt-4">
                <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-8 border border-gray-200 dark:border-gray-700 shadow-xl ring-8 ring-gray-100 dark:ring-gray-800/50">
                    <i class="fa-solid fa-file-circle-question text-5xl text-gray-300 dark:text-gray-600"></i>
                </div>
                <h3 class="text-xl font-extrabold text-gray-900 dark:text-gray-100 mb-3 tracking-tight">Preview Unavailable</h3>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 max-w-[320px] leading-relaxed font-medium">
                    This file format (<span class="font-mono text-blue-600 dark:text-blue-400" x-text="extOf(selectedFile?.name)"></span>) cannot be previewed directly in the browser. 
                    <br><br>
                    <span class="text-gray-400 dark:text-gray-500 text-xs">Please download the file to view its full content.</span>
                </p>
            </div>
        </template>

    </div>
</div>