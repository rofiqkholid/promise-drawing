@props(['selectedFile', 'isFullscreen', 'isCreatingMask', 'isPanning', 'enableMasking', 'masks', 'imageZoom', 'pkg', 'isStampBurned', 'stampConfig', 'isEngineering', 'imgLoading', 'imgError'])

{{-- IMAGE VIEWER (JPG, PNG, GIF, etc.) --}}
<template x-if="isImage(selectedFile?.name)">
    <div x-ref="viewport2d" class="relative w-full overflow-hidden bg-black/5 rounded"
        style="touch-action: none;"
        :class="[isFullscreen ? 'h-full' : 'h-[70vh]', isCreatingMask ? 'cursor-crosshair' : (isPanning ? 'cursor-grabbing' : 'cursor-grab')]"
        @mousedown.prevent="startPan($event)" @touchstart="startPan($event)" @wheel.prevent="onWheelZoom($event)">
        <div class="w-full h-full flex items-center justify-center">
            <div class="relative inline-block" :style="imageTransformStyle()">
                <img x-ref="mainImage" :src="imageDisplayUrl" @load="onImageLoad()"
                    x-on:error="onImageError()" alt="Preview"
                    class="block pointer-events-none select-none max-w-full transition-opacity duration-300" 
                    :class="[isFullscreen ? 'max-h-full' : 'max-h-[70vh]', (imgLoading || imgError) ? 'opacity-0' : 'opacity-100']" 
                    loading="lazy">

                {{-- WHITE BLOCKS (Masking) --}}
                <template x-if="enableMasking">
                        <template x-for="mask in masks" :key="mask.id">
                        <template x-if="mask">
                            <div x-show="mask.visible" x-cloak :style="maskStyle(mask)"
                                class="absolute bg-white cursor-move mask-element"
                                :data-mask-id="mask.id"
                                :class="[{ 'z-50': mask.active, 'z-10': !mask.active }, isCreatingMask ? 'pointer-events-none' : '']"
                                @click.stop="activateMask(mask)">

                                <!-- BORDER (Active Only - Ultra Sharp) -->
                                <div x-show="mask.active" x-cloak class="absolute inset-0 pointer-events-none mask-border" :style="{ border: (1/imageZoom).toFixed(3) + 'px solid #2563eb' }"></div>

                                <!-- RESIZE HANDLES (Z-40 - Proportional 8px) -->
                                <template x-if="mask.active && mask.editable">
                                    <div class="z-40">
                                        <template x-for="type in ['nw', 'ne', 'sw', 'se', 'n', 's', 'w', 'e']">
                                            <div class="mask-handle" :style="getHandleStyle(type, mask.rotation)"
                                                @mousedown.stop.prevent="startMaskResize($event, mask, type)"
                                                @touchstart.stop.prevent="startMaskResize($event, mask, type)">
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <!-- ROTATE HANDLES (Precise Outside - Z-30) -->
                                <template x-if="mask.active && mask.editable">
                                    <div class="z-30">
                                        <template x-for="pos in ['nw', 'ne', 'sw', 'se']">
                                            <div class="mask-handle-rotate" :style="getRotateHandleStyle(pos, mask.rotation)" 
                                                @mousedown.stop.prevent="startMaskRotate($event, mask)" 
                                                @touchstart.stop.prevent="startMaskRotate($event, mask)">
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </template>
                </template>

                {{-- HTML STAMPS REMOVED: Relying on Canvas Burning for Security --}}
            </div>
        </div>
        {{-- Solid Loading Overlay (Image) --}}
        <div x-show="imgLoading"
            class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-40 rounded-lg">
            
            {{-- Spinner --}}
            <div class="relative w-12 h-12 sm:w-16 sm:h-16 mb-4 sm:mb-6">
                <div class="absolute inset-0 border-[3px] sm:border-4 border-gray-200 dark:border-gray-700 rounded-full"></div>
                <div class="absolute inset-0 border-[3px] sm:border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
                <i class="fa-solid fa-image absolute inset-0 m-auto w-fit h-fit text-blue-500 animate-pulse text-lg sm:text-xl"></i>
            </div>

            <h3 class="text-xs sm:text-lg font-bold text-gray-800 dark:text-gray-100 mb-1.5 sm:mb-2">Loading Image</h3>
            
            {{-- Progress Bar --}}
            <div class="w-40 sm:w-64 h-1 sm:h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-blue-500 rounded-full transition-all duration-300 relative overflow-hidden" 
                        :style="`width: ${loadingProgress}%`">
                        <div class="absolute inset-0 bg-white/30 animate-[shimmer_1s_infinite]"></div>
                </div>
            </div>
            
            <p class="text-[9px] sm:text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wide" x-text="loadingStatus || 'Downloading...'"></p>
        </div>

        {{-- Solid Error Overlay (Image) --}}
        <div x-show="imgError" x-transition.opacity
            class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-50 rounded-lg p-6 text-center">
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
