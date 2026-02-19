@props(['selectedFile', 'showAdvanced3DControls', 'enableFullscreen', 'isFullscreen', 'pkg', 'isStampBurned', 'stampConfig', 'isEngineering', 'iges', 'cadParts', 'cadFlatParts'])

{{-- 3D CAD VIEWER (IGES, STEP, STL, OBJ) --}}
<template x-if="isCad(selectedFile?.name)">
<div class="relative w-full overflow-hidden transition-all duration-300"
        :class="isFullscreen ? 'h-full' : 'h-[70vh] min-h-[600px]'">
        
        {{-- MAIN VIEWPORT AREA --}}
        <div class="w-full h-full relative bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 group flex">
            
            {{-- SIDEBAR AREA (Dynamic Width) --}}
            <div :class="isPartListOpen ? 'w-[260px] lg:w-[280px] border-r border-white/20 dark:border-white/10' : 'w-0'" 
                 class="h-full relative flex-shrink-0 transition-all duration-300 overflow-hidden bg-white/85 dark:bg-gray-900/95 backdrop-blur-2xl shadow-2xl z-40">
                
                {{-- LEFT PANEL: Tree View & Properties --}}
                <div x-show="isPartListOpen && !iges.loading" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-x-10"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="w-[260px] lg:w-[280px] h-full flex flex-col overflow-hidden"
                     @click.stop>
                    
                    {{-- Header/Tabs --}}
                    <div class="flex items-center border-b border-white/20 dark:border-white/10 bg-white/20 dark:bg-black/40 backdrop-blur-md">
                        <button @click="activeTab = 'structure'" 
                            class="flex-1 py-2.5 text-[11px] font-black uppercase tracking-widest transition-colors relative"
                            :class="activeTab === 'structure' ? 'text-blue-600 dark:text-blue-400 bg-blue-600/5 dark:bg-blue-400/5' : 'text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200'">
                            Structure
                            <div x-show="activeTab === 'structure'" class="absolute bottom-0 left-0 w-full h-1 bg-blue-600 dark:bg-blue-400"></div>
                        </button>
                        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700/50"></div>
                        <button @click="activeTab = 'properties'" 
                            class="flex-1 py-2.5 text-[11px] font-black uppercase tracking-widest transition-colors relative"
                            :class="activeTab === 'properties' ? 'text-blue-600 dark:text-blue-400 bg-blue-600/5 dark:bg-blue-400/5' : 'text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200'">
                            Properties
                            <div x-show="activeTab === 'properties'" class="absolute bottom-0 left-0 w-full h-1 bg-blue-600 dark:bg-blue-400"></div>
                        </button>
                    </div>

                     {{-- 1. Structure Tree Content --}}
                    <div x-show="activeTab === 'structure'" x-ref="cadPartList" class="flex-1 overflow-y-auto p-2 custom-scrollbar min-h-0 bg-white/5 dark:bg-black/5">
                        {{-- Search Box --}}
                        <div class="sticky top-0 z-20 bg-white/40 dark:bg-black/60 backdrop-blur-md p-2 border-b border-white/10 dark:border-white/5 mb-1.5 rounded-xl">
                            <div class="relative group">
                                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors text-xs"></i>
                                <input type="text" x-model="partSearchQuery" x-ref="partSearchInput" placeholder="Quick find component..." 
                                     class="w-full pl-9 pr-8 py-2.5 bg-white/40 dark:bg-black/40 border border-white/10 dark:border-white/5 rounded-lg text-xs font-bold focus:ring-1 focus:ring-blue-500/30 focus:border-blue-500/50 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600 outline-none">
                                <button x-show="partSearchQuery" @click="partSearchQuery = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                                    <i class="fa-solid fa-times-circle text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-0.5 px-0.5">
                            {{-- Result Counter --}}
                            <div class="flex justify-between items-center px-2 py-1.5 mb-1 border-b border-white/10 dark:border-white/5">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <span x-text="getFilteredParts().length"></span> / <span x-text="cadFlatParts.length"></span> Items
                                </span>
                                <span x-show="cadFlatParts.length > 200 && !partSearchQuery" class="text-[10px] font-bold text-amber-500 uppercase">Limited View</span>
                            </div>

                            <template x-if="getFilteredParts().length === 0">
                                <div class="py-12 text-center text-gray-400 flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-1">
                                        <i class="fa-solid fa-sitemap text-xl opacity-20"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <span class="block text-[10px] font-black uppercase tracking-widest">No Matches</span>
                                    </div>
                                </div>
                            </template>
                            
                            {{-- Render Template --}}
                            <template x-for="part in getFilteredParts()" :key="part.id">
                                <div class="select-none">
                                    <div class="group relative flex items-center py-1 px-1.5 rounded-lg hover:bg-blue-600/10 dark:hover:bg-blue-400/10 cursor-pointer transition-all border border-transparent"
                                         :class="{'bg-blue-600/10 border-blue-600/20 text-blue-700 dark:text-blue-300': part.selected, 'opacity-50 grayscale': !part.visible}"
                                         @click="highlightPart(part.id)">
                                        
                                        {{-- Icon --}}
                                        <div class="relative mr-2 flex-shrink-0">
                                            <i class="fa-solid text-[12px]" 
                                               :class="part.type === 'assembly' ? 'fa-folder-tree text-amber-500' : 'fa-cube text-blue-500'"></i>
                                        </div>

                                        {{-- Name --}}
                                        <div class="flex-1 min-w-0 pr-12">
                                            <div class="text-[11px] font-bold truncate tracking-tight" x-text="part.name"></div>
                                        </div>
                                        {{-- Row Actions --}}
                                        <div class="absolute right-0.5 items-center gap-1 flex opacity-0 group-hover:opacity-100 transition-opacity bg-white/95 dark:bg-gray-800/95 rounded-lg p-0.5 shadow-md">
                                            <button @click.stop="focusPart(part.id)" title="Focus" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-blue-500 hover:text-white transition-colors">
                                                <i class="fa-solid fa-crosshairs text-[10px]"></i>
                                            </button>
                                            <button @click.stop="togglePartVisibility(part.id)" :title="part.visible ? 'Hide' : 'Show'" 
                                                    class="w-6 h-6 flex items-center justify-center rounded-md transition-colors"
                                                    :class="part.visible ? 'text-gray-400' : 'bg-red-500 text-white'">
                                                <i class="fa-solid text-[10px]" :class="part.visible ? 'fa-eye' : 'fa-eye-slash'"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                     {{-- 2. Properties Content --}}
                    <div x-show="activeTab === 'properties'" class="flex-1 overflow-y-auto p-3 custom-scrollbar bg-white/5 dark:bg-black/10 min-h-0">
                        <template x-if="getSelectedPart()">
                            <div class="space-y-4">
                                {{-- Main Header Card --}}
                                <div class="bg-white/40 dark:bg-gray-800/40 backdrop-blur-md p-4 rounded-2xl border border-white/40 dark:border-white/10 shadow-sm overflow-hidden relative">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 -mr-8 -mt-8 rounded-full blur-2xl"></div>
                                    
                                    <div class="relative flex items-center gap-3.5 mb-4 pb-4 border-b border-gray-100 dark:border-white/5">
                                        <div class="w-14 h-14 rounded-2xl bg-blue-600/10 dark:bg-blue-400/10 flex items-center justify-center border border-blue-600/5 shadow-inner">
                                            <i class="fa-solid text-2xl text-blue-600 dark:text-blue-400" :class="getSelectedPart().type === 'assembly' ? 'fa-folder-tree' : 'fa-cube'"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-[13px] font-black text-gray-900 dark:text-gray-100 truncate mb-1 leading-tight" x-text="getSelectedPart().name"></h5>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded bg-blue-600 text-white text-[9px] font-black uppercase tracking-widest" x-text="getSelectedPart().type"></span>
                                                <span class="font-mono text-[10px] text-gray-400 font-bold" x-text="`ID: ${getSelectedPart().id.substring(0,8)}`"></span>
                                            </div>
                                        </div>
                                        <button @click="deselectAllParts()" class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 hover:text-red-500 transition-colors shadow-sm" title="Clear Selection">
                                            <i class="fa-solid fa-times text-sm"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-2.5">
                                        <div class="group flex items-center justify-between p-2.5 rounded-xl bg-white/20 dark:bg-black/20 hover:bg-white/40 dark:hover:bg-black/40 transition-colors border border-transparent hover:border-white/10">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                                    <i class="fa-solid fa-palette text-xs"></i>
                                                </div>
                                                <span class="text-[11px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-tight">Active Color</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-5 h-5 rounded-full border-2 border-white dark:border-gray-700 shadow-sm" :style="'background-color: ' + getPartColor(getSelectedPart())"></div>
                                                <span class="font-mono text-[11px] font-black text-gray-700 dark:text-gray-300" x-text="getPartColor(getSelectedPart())"></span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-3 p-3 rounded-xl bg-white/20 dark:bg-black/20 border border-transparent">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                                        <i class="fa-solid fa-circle-half-stroke text-xs"></i>
                                                    </div>
                                                    <span class="text-[11px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-tight">Transparency</span>
                                                </div>
                                                <span class="text-xs font-black font-mono text-blue-600 dark:text-blue-400 bg-blue-600/10 px-2 py-0.5 rounded-lg" x-text="Math.round(partOpacity * 100) + '%'"></span>
                                            </div>
                                            <input type="range" min="0.1" max="1.0" step="0.1" x-model.number="partOpacity"
                                                   class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <button @click="isolatePart(getSelectedPart().id)" class="flex items-center justify-center gap-2 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-500/20 active:scale-95 transition-all">
                                        <i class="fa-solid fa-filter"></i> Isolate
                                    </button>
                                    <button @click="focusPart(getSelectedPart().id)" class="flex items-center justify-center gap-2 py-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-white/5 text-gray-700 dark:text-gray-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 active:scale-95 transition-all shadow-sm">
                                        <i class="fa-solid fa-crosshairs"></i> Focus
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="!getSelectedPart()">
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 text-center py-12">
                                <i class="fa-solid fa-mouse-pointer text-3xl mb-4 opacity-10"></i>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em]">Select a part<br>to view information</p>
                            </div>
                        </template>
                    </div>
                    
                    {{-- Toolbar Footer --}}
                    <div class="p-1.5 border-t border-white/20 dark:border-white/5 bg-white/20 dark:bg-black/50 backdrop-blur-md flex justify-between gap-1 mt-auto">
                        <button @click="showAllParts()" class="flex-1 py-2.5 bg-white/50 dark:bg-gray-800/50 border border-white/20 dark:border-white/10 rounded-lg text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-blue-500 hover:bg-white dark:hover:bg-gray-800 transition-all active:scale-95 shadow-sm">
                            <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                        </button>
                        <button @click="collapseAllTree()" class="flex-1 py-2.5 bg-white/50 dark:bg-gray-800/50 border border-white/20 dark:border-white/10 rounded-lg text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-blue-500 hover:bg-white dark:hover:bg-gray-800 transition-all active:scale-95 shadow-sm">
                            <i class="fa-solid fa-filter-circle-xmark mr-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>

            {{-- CANVAS AREA (MAIN) --}}
            <div class="flex-1 relative h-full overflow-hidden">
            
            {{-- Part List Toggle Button --}}
            <button @click="isPartListOpen = !isPartListOpen" x-show="!iges.loading && !iges.error"
                class="absolute top-4 left-4 z-30 px-3 py-2 bg-white/40 dark:bg-black/40 backdrop-blur-2xl shadow-lg border border-white/40 dark:border-white/10 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition flex items-center gap-2 group/btn">
                <i class="fa-solid" :class="isPartListOpen ? 'fa-xmark' : 'fa-sitemap'" class="text-blue-600 dark:text-blue-400"></i>
                <span x-text="isPartListOpen ? 'Close Structure' : 'Model Structure'"></span>
                <span class="bg-blue-600/10 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400 text-[10px] px-2 py-0.5 rounded-full font-black font-mono"
                    x-text="cadFlatParts.length"></span>
            </button>

            {{-- FPS MONITOR (Comment out the block below to hide) --}}
            <div x-show="fps > 0" 
                class="absolute top-16 left-4 z-30 px-2.5 py-1.5 bg-black/70 backdrop-blur-md rounded-xl text-[10px] font-mono border border-white/10 pointer-events-none flex flex-col gap-0.5 shadow-2xl">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-400 uppercase font-black text-[8px]">Real-time:</span>
                    <span class="text-green-400 font-bold" x-text="fps + ' FPS'"></span>
                </div>
                <div class="h-px bg-white/10 w-full my-0.5"></div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-400 uppercase font-black text-[8px]">Average:</span>
                    <span class="text-blue-400 font-bold" x-text="avgFps.toFixed(1) + ' FPS'"></span>
                </div>
            </div>


                    {{-- Measurement Toggle Button --}}
                    <button @click="isMeasureListOpen = !isMeasureListOpen" x-show="isMeasureActive && !iges.loading && !iges.error"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                        :title="isMeasureListOpen ? 'Hide Measurements' : 'Show Measurements'"
                        class="absolute top-4 right-16 z-[100] px-3 py-2 bg-white/40 dark:bg-black/85 backdrop-blur-3xl shadow-2xl border border-white/40 dark:border-white/20 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white transition-all flex items-center gap-2 group/btn active:scale-95 ring-1 ring-black/5 dark:ring-white/10">
                        <i class="fa-solid" :class="isMeasureListOpen ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span x-text="isMeasureListOpen ? 'Hide Info' : 'Show Info'"></span>
                        <span class="bg-blue-600/10 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400 group-hover:bg-white/20 group-hover:text-white text-[10px] px-2 py-0.5 rounded-full font-black font-mono transition-colors"
                            x-text="iges.measure.results.length" x-show="iges.measure.results.length > 0"></span>
                    </button>

                    {{-- Measurement List Panel (COMPACT) --}}
                    <div x-show="isMeasureListOpen && !iges.loading && !iges.error" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-x-2"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-2"
                        class="absolute top-16 right-4 z-[100] w-64 flex flex-col bg-white/40 dark:bg-black/85 backdrop-blur-3xl border border-white/40 dark:border-white/20 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden max-h-[70vh]">


                        <div class="px-2.5 py-2 border-b border-white/20 dark:border-white/5 bg-white/10 dark:bg-black/40 backdrop-blur-md flex-shrink-0">
                            <div class="flex justify-between items-center mb-0.5">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Measurements</span>
                                <button @click="clearMeasurements()"
                                    class="text-[9px] text-red-500 hover:underline font-bold">Clear</button>
                            </div>
                            {{-- Instruction Display --}}
                            <div x-show="iges.measure.enabled" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="text-[10px] text-blue-600 dark:text-blue-400 italic flex items-center gap-1 font-medium truncate">
                                <i class="fa-solid fa-info-circle text-[9px]"></i>
                                <span x-text="iges.measure.hoverInstruction" class="truncate"></span>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-1.5 custom-scrollbar min-h-0">
                            <template x-if="iges.measure.results.length === 0">
                                <div class="py-4 text-center text-gray-400 dark:text-gray-500 flex flex-col items-center gap-1">
                                    <span class="text-[9px] uppercase font-bold tracking-wider">No measurements</span>
                                    <span class="text-[9px] leading-tight px-2">Select points to measure.</span>
                                </div>
                            </template>
                            <ul class="space-y-1">
                                <template x-for="(res, idx) in iges.measure.results" :key="idx">
                                    <li @click="focusMeasurement(res)" class="bg-white dark:bg-white/5 p-2 rounded-lg border border-gray-100 dark:border-white/5 shadow-sm relative group hover:border-blue-300 dark:hover:border-blue-500/50 transition-all cursor-pointer select-none">
                                        <button @click.stop="deleteMeasurement(idx)"
                                            class="absolute top-1 right-1 text-gray-300 hover:text-red-500 p-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="fa-solid fa-circle-xmark text-[10px]"></i>
                                        </button>

                                        <div class="flex items-center gap-1.5 mb-1">
                                            <div class="w-4 h-4 rounded bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shadow-sm">
                                                <i class="fa-solid text-[8px]" :class="{
                                                    'fa-ruler-horizontal': res.type === 'point',
                                                    'fa-minus': res.type === 'edge',
                                                    'fa-angle-left': res.type === 'angle',
                                                    'fa-circle-notch': res.type === 'radius',
                                                    'fa-vector-square': res.type === 'face'
                                                }"></i>
                                            </div>
                                            <span class="text-[9px] font-black text-gray-700 dark:text-gray-200 uppercase tracking-wider"
                                                x-text="res.type"></span>
                                        </div>

                                        <div class="space-y-0.5 pl-0.5">
                                            <template x-if="res.distance !== undefined">
                                                <div class="flex justify-between items-baseline text-[10px]">
                                                    <span class="text-gray-500 font-medium">Dist:</span>
                                                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400 text-[11px]"
                                                        x-text="Number(res.distance).toFixed(2) + ' mm'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.angle !== undefined">
                                                <div class="flex justify-between items-baseline text-[10px]">
                                                    <span class="text-gray-500 font-medium">Angle:</span>
                                                    <span class="font-mono font-bold text-purple-600 dark:text-purple-400 text-[11px]"
                                                        x-text="Number(res.angle).toFixed(2) + '°'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.radius !== undefined">
                                                <div class="flex justify-between items-baseline text-[10px]">
                                                    <span class="text-gray-500 font-medium">Rad:</span>
                                                    <span class="font-mono font-bold text-green-600 dark:text-green-400 text-[11px]"
                                                        x-text="Number(res.radius).toFixed(2) + ' mm'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.diameter !== undefined">
                                                <div class="flex justify-between items-baseline text-[10px]">
                                                    <span class="text-gray-500 font-medium">Dia:</span>
                                                    <span class="font-mono font-bold text-teal-600 dark:text-teal-400 text-[11px]"
                                                        x-text="Number(res.diameter).toFixed(2) + ' mm'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.area !== undefined">
                                                <div class="flex justify-between items-baseline text-[10px]">
                                                    <span class="text-gray-500 font-medium">Area:</span>
                                                    <span class="font-mono font-bold text-orange-600 dark:text-orange-400 text-[11px]"
                                                        x-text="Number(res.area).toFixed(2) + ' mm²'"></span>
                                                </div>
                                            </template>
                                            <template x-if="res.deltaX !== undefined">
                                                <div class="grid grid-cols-3 gap-0.5 pt-1 mt-0.5 border-t border-gray-100 dark:border-white/5 text-[8px] font-mono text-center">
                                                    <span class="text-red-600 bg-red-50 dark:bg-red-900/20 rounded px-0.5" title="Delta X">X:<span x-text="Number(res.deltaX).toFixed(1)"></span></span>
                                                    <span class="text-green-600 bg-green-50 dark:bg-green-900/20 rounded px-0.5" title="Delta Y">Y:<span x-text="Number(res.deltaY).toFixed(1)"></span></span>
                                                    <span class="text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded px-0.5" title="Delta Z">Z:<span x-text="Number(res.deltaZ).toFixed(1)"></span></span>
                                                </div>
                                            </template>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

            {{-- 3D Canvas --}}
            <div x-ref="cadContainer" class="w-full h-full cursor-grab bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900"
                @dblclick="resetCamera3d()">
            </div>

            {{-- Loading Overlay --}}
            <div x-show="iges.loading" x-transition.opacity.duration.500ms
                class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/95 z-50 backdrop-blur-sm">
                
                {{-- Spinner --}}
                <div class="relative w-16 h-16 mb-6">
                    <div class="absolute inset-0 border-4 border-gray-200 dark:border-gray-700 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
                    <i class="fa-solid fa-cube absolute inset-0 m-auto w-fit h-fit text-blue-500 animate-pulse text-xl"></i>
                </div>

                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2">Processing 3D Model</h3>
                
                {{-- Progress Bar --}}
                <div class="w-64 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                    <div class="h-full bg-blue-500 rounded-full transition-all duration-300 relative overflow-hidden" 
                         :style="`width: ${iges.progress}%`">
                         <div class="absolute inset-0 bg-white/30 animate-[shimmer_1s_infinite]"></div>
                    </div>
                </div>
                
                <p class="text-xs font-mono text-gray-500 dark:text-gray-400" x-text="iges.loadingStatus || 'Initializing...'"></p>
                <p class="text-[10px] text-gray-400 mt-4 max-w-xs text-center animate-pulse">Large models may take a moment to tessellate...</p>
            </div>

            {{-- Solid Error Overlay (CAD) --}}
            <div x-show="iges.error" x-transition.opacity
                class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-gray-900 z-50 rounded-lg p-6 text-center">
                <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-circle-exclamation text-xl"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">3D Model Loading Failed</h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 max-w-[240px] leading-relaxed line-clamp-2" x-text="iges.error"></p>
                <button @click="loadFile(selectedFile, true)" 
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-md text-[11px] font-bold hover:bg-gray-800 dark:hover:bg-gray-200 transition-all shadow-sm">
                    <i class="fa-solid fa-rotate-right"></i> Try Again
                </button>
            </div>

            {{-- Floating 3D Navigation Controls (Right) --}}
            <div x-show="isCad(selectedFile?.name) && !iges.loading && !iges.error"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                class="absolute inset-y-0 right-6 flex flex-col justify-center pointer-events-none z-40">

                <div class="flex flex-col items-center bg-white/40 dark:bg-black/85 backdrop-blur-3xl rounded-2xl border border-white/40 dark:border-white/20 shadow-2xl ring-1 ring-black/5 dark:ring-white/10 p-1.5 gap-2 pointer-events-auto transition-all duration-300 translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 shadow-blue-500/5"
                     :class="isFullscreen ? 'translate-x-0 opacity-100' : ''">

                    <button @click="zoom3d(1.25)"
                        class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white transition-all active:scale-95 shadow-sm border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20"
                        title="Zoom In (+)">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>

                    <div class="w-6 h-px bg-gray-400/30 dark:bg-white/10 mx-1"></div>

                    <button @click="zoom3d(0.8)"
                        class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white transition-all active:scale-95 shadow-sm border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20"
                        title="Zoom Out (-)">
                        <i class="fa-solid fa-minus text-xs"></i>
                    </button>

                    <div class="w-6 h-px bg-gray-400/30 dark:bg-white/10 mx-1"></div>

                    <button @click="resetCamera3d()"
                        class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white transition-all active:scale-95 shadow-sm border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20"
                        title="Reset View (Home)">
                        <i class="fa-solid fa-house-chimney text-xs"></i>
                    </button>

                    <div class="w-6 h-px bg-gray-400/30 dark:bg-white/10 mx-1"></div>

                    <button @click="toggleFullscreen()"
                        class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white transition-all active:scale-95 shadow-sm border border-transparent hover:border-blue-500/50 hover:shadow-blue-500/20"
                        title="Toggle Fullscreen">
                        <i class="fa-solid" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                    </button>
                </div>
            </div>

            {{-- Controls Overlay (Bottom Center) --}}
            @if($showAdvanced3DControls)
            <div x-show="isCad(selectedFile?.name) && !iges.loading && !iges.error"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute bottom-6 inset-x-0 z-40 flex justify-center pointer-events-none transition-all duration-500 origin-bottom translate-y-6 opacity-0 group-hover:translate-y-0 group-hover:opacity-100"
                 :class="isFullscreen ? 'translate-y-0 opacity-100' : ''">
                
                <div class="flex bg-white/40 dark:bg-black/85 backdrop-blur-3xl p-1 lg:p-1.5 rounded-2xl border border-white/40 dark:border-white/20 shadow-2xl ring-1 ring-black/5 dark:ring-white/10 items-center justify-center flex-wrap sm:flex-nowrap gap-1 xl:gap-2.5 pointer-events-auto max-w-[95%] lg:max-w-max mx-auto transition-all duration-300"
                     :class="isFullscreen ? 'scale-100' : 'scale-[0.8] sm:scale-[0.9] lg:scale-95 xl:scale-100'">
                
                    {{-- GROUP 1: Display Settings --}}
                    <div class="flex items-center gap-1 lg:gap-1.5 px-1.5 py-1 bg-gray-900/5 dark:bg-white/5 rounded-xl border border-white/20 dark:border-white/10 flex-shrink-0">
                        <span class="text-[7px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-tighter px-0.5 flex-shrink-0 leading-none">Display</span>
                        
                        <div class="inline-flex bg-white/70 dark:bg-gray-800/70 p-0.5 rounded-lg border border-white/30 dark:border-white/10 shadow-inner overflow-hidden flex-shrink-0">
                            <button @click="setDisplayStyle('shaded')"
                                class="px-1.5 lg:px-2.5 py-1 text-[10px] lg:text-xs font-black rounded-md transition-all cursor-pointer whitespace-nowrap"
                                :class="currentStyle === 'shaded' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-black/5 dark:hover:bg-white/5'">
                                Shaded
                            </button>
                            <button @click="setDisplayStyle('shaded-edges')"
                                class="px-1.5 lg:px-2.5 py-1 text-[10px] lg:text-xs font-black rounded-md transition-all cursor-pointer whitespace-nowrap"
                                :class="currentStyle === 'shaded-edges' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-black/5 dark:hover:bg-white/5'">
                                Edges
                            </button>
                            <button @click="setDisplayStyle('wireframe')"
                                class="px-1.5 lg:px-2.5 py-1 text-[10px] lg:text-xs font-black rounded-md transition-all cursor-pointer whitespace-nowrap"
                                :class="currentStyle === 'wireframe' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-black/5 dark:hover:bg-white/5'">
                                Wire
                            </button>
                        </div>

                        <div class="h-5 w-px bg-gray-400/30 dark:bg-white/10 flex-shrink-0"></div>

                        <div class="relative flex-shrink-0">
                            <button @click="isMatMenuOpen = !isMatMenuOpen"
                                @click.outside="isMatMenuOpen = false" title="Material"
                                class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded-lg bg-white/70 dark:bg-gray-800/70 border border-white/30 dark:border-white/10 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-500 transition-all cursor-pointer active:scale-95"
                                :class="activeMaterial !== 'default' ? 'text-blue-600 dark:text-blue-400 bg-blue-600/5' : 'text-gray-700 dark:text-gray-200'">
                                <i class="fa-solid fa-fill-drip text-[10px] lg:text-xs"></i>
                            </button>

                            <div x-show="isMatMenuOpen" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 p-1.5 bg-white dark:bg-gray-900 border border-white/30 dark:border-white/10 rounded-2xl shadow-2xl z-50 w-36 flex flex-col gap-1">
                                <div class="text-[8px] font-black text-blue-600 dark:text-blue-400 px-2 py-1 uppercase tracking-widest border-b border-gray-100 dark:border-white/5 mb-1">Material</div>
                                
                                <template x-for="mat in ['clay', 'metal', 'normal', 'glass', 'ecoat', 'raw-steel', 'aluminum', 'yellow-zinc', 'red-oxide', 'dark', 'default']" :key="mat">
                                    <button @click="setMaterialMode(mat); isMatMenuOpen=false"
                                        class="text-left px-2 py-1 text-[11px] font-bold rounded-xl hover:bg-blue-600 hover:text-white flex items-center gap-2 transition-colors uppercase tracking-tight cursor-pointer"
                                        :class="activeMaterial === mat ? 'text-blue-600 dark:text-blue-400 bg-blue-600/10' : 'text-gray-600 dark:text-gray-400'">
                                        <div class="w-2 h-2 rounded-full border border-black/10 dark:border-white/20" :class="{
                                            'bg-orange-200': mat === 'clay', 
                                            'bg-gray-400': mat === 'metal', 
                                            'bg-purple-400': mat === 'normal', 
                                            'bg-blue-200 opacity-50': mat === 'glass',
                                            'bg-gray-500': mat === 'ecoat',
                                            'bg-gray-300': mat === 'raw-steel',
                                            'bg-white': mat === 'aluminum',
                                            'bg-yellow-500': mat === 'yellow-zinc',
                                            'bg-red-700': mat === 'red-oxide',
                                            'bg-gray-800': mat === 'dark',
                                            'bg-blue-500': mat === 'default'
                                        }"></div>
                                        <span x-text="mat"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- GROUP 2: View Controls --}}
                    <div class="flex items-center gap-1 lg:gap-1.5 px-1.5 py-1 bg-gray-900/5 dark:bg-white/5 rounded-xl border border-white/20 dark:border-white/10 flex-shrink-0">
                        <span class="text-[7px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-tighter px-0.5 flex-shrink-0 leading-none">View</span>

                        <button @click="toggleCameraMode()"
                            :title="cameraMode === 'perspective' ? 'View: Perspective (C)' : 'View: Orthographic (C)'"
                            class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg bg-white/70 dark:bg-gray-800/70 border border-white/30 dark:border-white/10 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-500 text-gray-700 dark:text-gray-200 flex flex-col items-center justify-center transition-all cursor-pointer active:scale-95 flex-shrink-0">
                            <i class="fa-solid text-[9px] lg:text-[10px]" :class="cameraMode === 'perspective' ? 'fa-cube' : 'fa-border-none'"></i>
                            <span x-text="cameraMode === 'perspective' ? 'Persp' : 'Ortho'" class="text-[6px] font-black uppercase tracking-tighter mt-0.5 leading-none"></span>
                        </button>

                        <div class="relative flex-shrink-0">
                            <button @click="isViewMenuOpen = !isViewMenuOpen" @click.outside="isViewMenuOpen = false" title="Standard Views"
                                class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg bg-white/70 dark:bg-gray-800/70 border border-white/30 dark:border-white/10 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-500 text-gray-700 dark:text-gray-200 flex items-center justify-center transition-all cursor-pointer active:scale-95">
                                <i class="fa-solid fa-dice-d6 text-[10px] lg:text-xs"></i>
                            </button>

                            <div x-show="isViewMenuOpen" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 p-1.5 bg-white dark:bg-gray-900 border border-white/30 dark:border-white/10 rounded-2xl shadow-2xl z-50 w-32 flex flex-col gap-1">
                                <div class="text-[8px] font-black text-blue-600 dark:text-blue-400 px-2 py-1 uppercase tracking-widest border-b border-gray-100 dark:border-white/5 mb-1">Standard</div>
                                <template x-for="v in ['front', 'back', 'top', 'bottom', 'left', 'right', 'iso']" :key="v">
                                    <button @click="setStandardView(v); isViewMenuOpen=false"
                                        class="text-left px-2 py-1 text-[11px] font-bold rounded-xl hover:bg-blue-600 hover:text-white text-gray-700 dark:text-gray-300 transition-colors uppercase tracking-tight cursor-pointer"
                                        x-text="v"></button>
                                </template>
                            </div>
                        </div>

                        <button @click="toggleAutoRotate()" title="Auto Rotate (Space)"
                            class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg transition-all border flex items-center justify-center cursor-pointer active:scale-95 flex-shrink-0"
                            :class="autoRotate ? 'bg-blue-600 text-white border-blue-600' : 'bg-white/70 dark:bg-gray-800/70 text-gray-700 dark:text-gray-200 border-white/30 dark:border-white/10 hover:bg-blue-600 hover:text-white'">
                            <i class="fa-solid fa-rotate text-[10px] lg:text-xs" :class="autoRotate ? 'fa-spin' : ''"></i>
                        </button>

                        <button @click="toggleHeadlight()" title="Headlight (H)"
                            class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg transition-all border flex items-center justify-center cursor-pointer active:scale-95 flex-shrink-0"
                            :class="headlight.enabled ? 'bg-amber-500 text-white border-amber-500' : 'bg-white/70 dark:bg-gray-800/70 text-gray-700 dark:text-gray-200 border-white/30 dark:border-white/10 hover:bg-amber-500 hover:text-white'">
                            <i class="fa-solid fa-lightbulb text-[10px] lg:text-xs"></i>
                        </button>
                    </div>

                    {{-- GROUP 3: Analysis Tools --}}
                    <div class="flex items-center gap-1 lg:gap-1.5 px-1.5 py-1 bg-gray-900/5 dark:bg-white/5 rounded-xl border border-white/20 dark:border-white/10 flex-shrink-0">
                        <span class="text-[7px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-tighter px-0.5 flex-shrink-0 leading-none">Analysis</span>

                        <div class="relative">
                            <button @click="toggleExplodedPanel()"
                                :class="iges.exploded.enabled ? 'text-blue-600 bg-blue-600/10 border-blue-500 shadow-sm' : 'text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-gray-800/60 border-white/30 dark:border-white/10'"
                                class="w-8 h-8 lg:w-9 lg:h-9 flex items-center justify-center rounded-xl border hover:bg-blue-600 hover:text-white transition-all text-sm active:scale-95 cursor-pointer"
                                title="Exploded View (X)">
                                <i class="fa-solid fa-expand-arrows-alt" :class="iges.exploded.enabled ? 'scale-110' : ''"></i>
                            </button>


                            {{-- Exploded View Panel --}}
                            <div x-show="iges.exploded && iges.exploded.panelOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                @click.outside="iges.exploded.panelOpen = false"
                                class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 bg-white/40 dark:bg-black/85 backdrop-blur-3xl border border-white/40 dark:border-white/20 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 z-50 w-48 lg:w-56 overflow-hidden">

                                <div class="flex items-center justify-between px-3 py-2 border-b border-white/20 dark:border-white/5 bg-white/10 dark:bg-black/40 backdrop-blur-md">

                                            <div class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-expand-arrows-alt text-blue-600 dark:text-blue-400 text-[10px]"></i>
                                                <span class="text-[10px] lg:text-xs font-black text-gray-700 dark:text-gray-200 uppercase">Explode</span>
                                            </div>
                                            <button @click="toggleExplodedView(); iges.exploded.panelOpen = false"
                                                class="text-[9px] text-red-600 dark:text-red-400 hover:underline font-black">OFF</button>
                                        </div>

                                        <div class="p-3">
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-bold">Factor</span>
                                                    <div class="px-1.5 py-0.5 rounded bg-blue-600 text-white text-[10px] font-mono font-black">
                                                        <span x-text="Math.round(iges.exploded.factor * 100)"></span>%
                                                    </div>
                                                </div>

                                                <input type="range" min="0" max="1" step="0.01"
                                                    x-model.number="iges.exploded.factor"
                                                    @input="updateExplodeFactor(iges.exploded.factor)"
                                                    class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">

                                                 <div class="flex items-center justify-between gap-2">
                                                    <button @click="iges.exploded.factor = 0; updateExplodeFactor(0)"
                                                        class="flex-1 text-[9px] py-1.5 rounded font-black bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-blue-600 hover:text-white transition-all cursor-pointer">
                                                        RESET
                                                    </button>
                                                    <button @click="toggleExplodedView()"
                                                        class="flex-1 text-[9px] py-1.5 rounded font-black bg-red-600 text-white hover:bg-red-700 transition-all cursor-pointer uppercase">
                                                        Disable
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <button @click="toggleClippingPanel()"
                                        :class="iges.clipping.enabled ? 'text-blue-600 bg-blue-600/10 border-blue-500 shadow-sm' : 'text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-gray-800/60 border-white/30 dark:border-white/10'"
                                        class="w-8 h-8 lg:w-9 lg:h-9 flex items-center justify-center rounded-xl border hover:bg-blue-600 hover:text-white transition-all text-sm active:scale-95 cursor-pointer"
                                        title="Section Cut (S)">
                                        <i class="fa-solid fa-scissors rotate-90" :class="iges.clipping.enabled ? 'scale-110' : ''"></i>
                                    </button>

                            {{-- Section Cut Panel --}}
                            <div x-show="iges.clipping && iges.clipping.panelOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                @click.outside="if (!iges.clipping._dragState) { iges.clipping.panelOpen = false }"
                                class="absolute bottom-full mb-3 right-0 bg-white/40 dark:bg-black/85 backdrop-blur-3xl border border-white/40 dark:border-white/20 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 z-50 w-64 overflow-hidden">

                                <div class="flex items-center justify-between px-3 py-2 border-b border-white/20 dark:border-white/5 bg-white/10 dark:bg-black/40 backdrop-blur-md">

                                            <span class="text-[10px] font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">Section Cut</span>
                                            <button @click="resetAllClipping()"
                                                class="text-[10px] text-red-600 dark:text-red-400 hover:underline font-black uppercase">
                                                Reset
                                            </button>
                                        </div>

                                        <div class="p-2 space-y-1.5 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                            {{-- X Axis --}}
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input type="checkbox" :checked="iges.clipping.x.enabled" @change="toggleAxisClipping('x')" class="rounded text-red-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                    <div class="w-5 h-5 rounded bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                        <span class="text-[10px] font-bold text-red-600 dark:text-red-400">X</span>
                                                    </div>
                                                    <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">X-Axis</span>
                                                    
                                                    <button @click="togglePlaneHelper('x')" x-show="iges.clipping.x.enabled" 
                                                        :class="iges.clipping.x.showHelper ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all" title="Toggle Plane Helper">
                                                        <i class="fa-solid fa-eye text-[9px]"></i>
                                                    </button>
                                                    
                                                    <button @click="flipAxis('x')" x-show="iges.clipping.x.enabled"
                                                        :class="iges.clipping.x.flipped ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all" title="Flip Direction">
                                                        <i class="fa-solid fa-right-left text-[9px]"></i>
                                                    </button>
                                                </div>
                                                
                                                <div x-show="iges.clipping.x.enabled" x-transition class="pl-6 space-y-1.5">
                                                    <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                        <span>Position:</span>
                                                        <span class="font-mono font-semibold text-red-600 dark:text-red-400" x-text="iges.clipping.x.value.toFixed(2)"></span>
                                                    </div>
                                                    
                                                    <input type="range" 
                                                        :min="iges.clipping.x.min !== undefined ? iges.clipping.x.min : iges.clipping.min" 
                                                        :max="iges.clipping.x.max !== undefined ? iges.clipping.x.max : iges.clipping.max" 
                                                        x-model.number="iges.clipping.x.value" @input="updateAxisClipping('x')"
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
                                                </div>
                                            </div>

                                            {{-- Y Axis --}}
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input type="checkbox" :checked="iges.clipping.y.enabled" @change="toggleAxisClipping('y')" class="rounded text-green-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                    <div class="w-5 h-5 rounded bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                        <span class="text-[10px] font-bold text-green-600 dark:text-green-400">Y</span>
                                                    </div>
                                                    <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">Y-Axis</span>
                                                    
                                                    <button @click="togglePlaneHelper('y')" x-show="iges.clipping.y.enabled" 
                                                        :class="iges.clipping.y.showHelper ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all" title="Toggle Plane Helper">
                                                        <i class="fa-solid fa-eye text-[9px]"></i>
                                                    </button>
                                                    
                                                    <button @click="flipAxis('y')" x-show="iges.clipping.y.enabled"
                                                        :class="iges.clipping.y.flipped ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all" title="Flip Direction">
                                                        <i class="fa-solid fa-right-left text-[9px]"></i>
                                                    </button>
                                                </div>
                                                
                                                <div x-show="iges.clipping.y.enabled" x-transition class="pl-6 space-y-1.5">
                                                    <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                        <span>Position:</span>
                                                        <span class="font-mono font-semibold text-green-600 dark:text-green-400" x-text="iges.clipping.y.value.toFixed(2)"></span>
                                                    </div>
                                                    
                                                    <input type="range" 
                                                        :min="iges.clipping.y.min !== undefined ? iges.clipping.y.min : iges.clipping.min" 
                                                        :max="iges.clipping.y.max !== undefined ? iges.clipping.y.max : iges.clipping.max" 
                                                        x-model.number="iges.clipping.y.value" @input="updateAxisClipping('y')"
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
                                                </div>
                                            </div>

                                            {{-- Z Axis --}}
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input type="checkbox" :checked="iges.clipping.z.enabled" @change="toggleAxisClipping('z')" class="rounded text-blue-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                    <div class="w-5 h-5 rounded bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">Z</span>
                                                    </div>
                                                    <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">Z-Axis</span>
                                                    
                                                    <button @click="togglePlaneHelper('z')" x-show="iges.clipping.z.enabled" 
                                                        :class="iges.clipping.z.showHelper ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all" title="Toggle Plane Helper">
                                                        <i class="fa-solid fa-eye text-[9px]"></i>
                                                    </button>
                                                    
                                                    <button @click="flipAxis('z')" x-show="iges.clipping.z.enabled"
                                                        :class="iges.clipping.z.flipped ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
                                                        class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all" title="Flip Direction">
                                                        <i class="fa-solid fa-right-left text-[9px]"></i>
                                                    </button>
                                                </div>
                                                
                                                <div x-show="iges.clipping.z.enabled" x-transition class="pl-6 space-y-1.5">
                                                    <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                        <span>Position:</span>
                                                        <span class="font-mono font-semibold text-blue-600 dark:text-blue-400" x-text="iges.clipping.z.value.toFixed(2)"></span>
                                                    </div>
                                                    
                                                    <input type="range" 
                                                        :min="iges.clipping.z.min !== undefined ? iges.clipping.z.min : iges.clipping.min" 
                                                        :max="iges.clipping.z.max !== undefined ? iges.clipping.z.max : iges.clipping.max" 
                                                        x-model.number="iges.clipping.z.value" @input="updateAxisClipping('z')"
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <button @click="toggleMeasure()"
                            :class="iges.measure.enabled ? 'text-blue-600 bg-blue-600/10 border-blue-500 shadow-sm' : 'text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-gray-800/60 border-white/30 dark:border-white/10'"
                            class="w-8 h-8 lg:w-9 lg:h-9 flex items-center justify-center rounded-xl border hover:bg-blue-600 hover:text-white transition-all text-sm active:scale-95 cursor-pointer"
                            title="Measurement Tool (M)">
                            <i class="fa-solid fa-ruler-combined" :class="iges.measure.enabled ? 'scale-110' : ''"></i>
                        </button>

                        <div class="h-6 lg:h-7 w-px bg-gray-400/30 dark:bg-white/10 mx-1 flex-shrink-0"></div>

                        <button @click="takeScreenshot()" title="Take Screenshot (Camera)"
                            class="w-8 h-8 lg:w-9 lg:h-9 flex items-center justify-center rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/30 dark:border-white/10 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-500 text-gray-700 dark:text-gray-200 transition-all active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif



            <!-- measure toolbar -->
            <div x-show="iges.measure.enabled && !iges.loading && !iges.error" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute bottom-[90px] inset-x-0 z-50 flex justify-center pointer-events-none transition-all duration-300">
                
                <div class="bg-white/40 dark:bg-black/85 backdrop-blur-3xl border border-white/40 dark:border-white/20 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 flex items-center p-1.5 gap-2 pointer-events-auto shadow-blue-500/5">


                    {{-- Dynamic Instruction (Integrated Left) --}}
                    <div class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700/50 rounded-lg text-[10px] font-bold text-gray-500 dark:text-gray-400 whitespace-nowrap min-w-[120px] text-center"
                        x-text="iges.measure.hoverInstruction">
                    </div>

                    <div class="h-6 w-px bg-gray-200 dark:bg-gray-600 mx-0.5"></div>

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

                        <button @click="toggleMeasureLabels()"
                            class="p-2 rounded text-xs transition relative group"
                            :class="iges.measure.showLabels ? 'text-blue-600 hover:bg-blue-50 dark:text-blue-400' : 'text-gray-400 hover:bg-gray-100'"
                            title="Toggle Labels Visibility">
                            <i class="fa-solid" :class="iges.measure.showLabels ? 'fa-comment' : 'fa-comment-slash'"></i>
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
                </div>
            </div>

            {{-- 2D Overlay (Stamps) for CAD? Typically not used in 3D view effectively unless in screen space --}}
            {{-- Keeping 3D clean for now as stamps are not easily projected onto 3D geometry in this viewer implementation yet, usually stamps are for 2D exports --}}
        </div>
    </div>
</template>
