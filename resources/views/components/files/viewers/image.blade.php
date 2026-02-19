@props(['selectedFile', 'isFullscreen', 'isCreatingMask', 'isPanning', 'enableMasking', 'masks', 'imageZoom', 'pkg', 'isStampBurned', 'stampConfig', 'isEngineering', 'imgLoading', 'imgError'])

{{-- IMAGE VIEWER (JPG, PNG, GIF, etc.) --}}
<template x-if="isImage(selectedFile?.name)">
    <div x-ref="viewport2d" class="relative w-full overflow-hidden bg-black/5 rounded"
        style="touch-action: none;"
        :class="[isFullscreen ? 'h-full' : 'h-[70vh]', isCreatingMask ? 'cursor-crosshair' : (isPanning ? 'cursor-grabbing' : 'cursor-grab')]"
        @mousedown.prevent="startPan($event)" @touchstart="startPan($event)" @wheel.prevent="onWheelZoom($event)">
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

                {{-- STAMP ORIGINAL --}}
                <div x-show="pkg.stamp && !isStampBurned" class="absolute" :class="stampPositionClass('original')" 
                    :key="`stamp-original-${stampConfig.original}`">
                    <div class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        :class="[
                            stampOriginClass('original'),
                            pkg.stamp?.is_obsolete ? 'border-gray-500 text-gray-600' : (isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600')
                        ]" style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 py-0.5 px-4 font-semibold tracking-tight"
                            :class="pkg.stamp?.is_obsolete ? 'border-gray-500' : (isEngineering ? 'border-blue-600' : 'border-gray-500')">
                            <span x-text="stampTopLine('original')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                            <span class="text-xs font-extrabold uppercase px-2"
                                :class="pkg.stamp?.is_obsolete ? 'text-gray-600' : (isEngineering ? 'text-blue-700' : 'text-gray-600')"
                                x-text="stampCenterOriginal()"></span>
                        </div>
                        <div class="w-full border-t-2 py-0.5 px-4 text-center font-semibold tracking-tight"
                            :class="pkg.stamp?.is_obsolete ? 'border-gray-500' : (isEngineering ? 'border-blue-600'  : 'border-gray-500')">
                            <span x-text="stampBottomLine('original')"></span>
                        </div>
                    </div>
                </div>

                {{-- STAMP COPY --}}
                <div x-show="pkg.stamp && !isStampBurned" class="absolute" :class="stampPositionClass('copy')" 
                    :key="`stamp-copy-${stampConfig.copy}`">
                    <div class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        :class="[
                            stampOriginClass('copy'),
                            pkg.stamp?.is_obsolete ? 'border-gray-500 text-gray-600' : 'border-blue-600 text-blue-700'
                        ]"
                        style="transform: scale(0.45);">
                        <div
                            class="w-full text-center border-b-2 py-0.5 px-4 font-semibold tracking-tight"
                            :class="pkg.stamp?.is_obsolete ? 'border-gray-500' : 'border-blue-600'">
                            <span x-text="stampTopLine('copy')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                            <span class="text-xs font-extrabold uppercase px-2"
                                :class="pkg.stamp?.is_obsolete ? 'text-gray-600' : 'text-blue-700'"
                                x-text="stampCenterCopy()"></span>
                        </div>
                        <div
                            class="w-full border-t-2 py-0.5 px-4 text-center font-semibold tracking-tight"
                            :class="pkg.stamp?.is_obsolete ? 'border-gray-500' : 'border-blue-600'">
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
        <div x-show="imgLoading" x-transition.opacity.duration.300ms
            class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-40 rounded-lg">
            
            {{-- Spinner --}}
            <div class="relative w-12 h-12 mb-4">
                <div class="absolute inset-0 border-4 border-gray-200 dark:border-gray-700 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
                <i class="fa-solid fa-image absolute inset-0 m-auto w-fit h-fit text-blue-500 animate-pulse text-sm"></i>
            </div>

            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-2">Loading Image</h3>
            
            {{-- Progress Bar --}}
            <div class="w-48 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-blue-500 rounded-full transition-all duration-300 relative overflow-hidden" 
                        :style="`width: ${loadingProgress}%`">
                        <div class="absolute inset-0 bg-white/30 animate-[shimmer_1s_infinite]"></div>
                </div>
            </div>
            
            <p class="text-[10px] font-mono text-gray-500 dark:text-gray-400" x-text="loadingStatus || 'Downloading...'"></p>
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
