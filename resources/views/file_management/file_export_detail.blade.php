@extends('layouts.app')
@section('title', 'Download Detail - File Manager')
@section('header-title', 'File Manager - Download Detail')

@section('content')
<nav class="flex px-5 py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">

        <li class="inline-flex items-center">
            <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600">
                Monitoring
            </a>
        </li>

        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>

                <a href="{{ route('file-manager.export') }}" class="text-sm font-semibold text-gray-500 px-2.5 py-0.5 hover:text-blue-600 rounded">
                    Download Files
                </a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>

                <span class="text-sm font-semibold text-blue-800 px-2.5 py-0.5 rounded">
                    Download Detail Files
                </span>
            </div>
        </li>
    </ol>
</nav>
<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen"
    x-data="exportDetail({
        userDeptCode: @js($userDeptCode ?? null),
        isEngineering: @js($isEngineering ?? false)
    })"
    x-init="init()"
    @mousemove.window="onPan($event)"
    @mouseup.window="endPan()"
    @mouseleave.window="endPan()"
    @keydown.window="handleShortcut($event)">

    <div x-show="isLoadingRevision" x-transition
        class="absolute inset-0 bg-gray-100/75 dark:bg-gray-900/75 z-10 flex items-center justify-center rounded-lg">
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
            <div class="w-6 h-6 border-4 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Loading Revision...</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
        <div class="lg:col-span-4 space-y-6 relative">

            <div x-ref="metaCard"
                class="self-start bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 md:justify-between">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
                            Package Info
                        </h2>
                        @php
                        $backUrl = route('file-manager.export');
                        @endphp
                        <a href="{{ $backUrl }}"
                            class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back
                        </a>
                    </div>
                </div>

                <div class="p-4 space-y-4">

                    <div>
                        {{-- <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Metadata</label>
                            --}}
                        <div class="flex items-start justify-between gap-2 mt-0.5">

                            <div class="flex-grow flex items-center gap-x-2 gap-y-1 flex-wrap min-w-0">
                                <div class="w-full flex items-start justify-start gap-2">
                                    <span
                                        class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        x-text="revisionBadgeText()" :title="revisionBadgeText()">
                                    </span>
                                </div>

                                <p class="text-sm text-gray-900 dark:text-gray-100 w-full mt-1" x-text="metaLine()"
                                    :title="metaLine()">
                                </p>

                                <template x-if="pkg.metadata?.linked_partners && pkg.metadata.linked_partners.length > 0">
                                    <div class="w-full mt-1 flex items-center gap-2">
                                        <span class="text-xs text-gray-500 italic">Also applicable for:</span>
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="partner in pkg.metadata.linked_partners" :key="partner">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800">
                                                    <i class="fa-solid fa-link mr-1 text-[9px]"></i>
                                                    <span x-text="partner"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <label for="revision-selector"
                            class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-history fa-sm mr-1"></i>
                            Revision History
                        </label>
                        <select id="revision-selector" x-ref="revisionSelector"
                            :disabled="isLoadingRevision"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-gray-700">
                        </select>
                    </div>
                </div>


                <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Content</span>
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200">
                            <span x-text="getTotalPackageStats().count + ' Files'"></span>
                            <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                            <span x-text="formatBytes(getTotalPackageStats().size)"></span>
                        </div>
                    </div>

                    <button @click="downloadPackage()"
                        class="group flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs lg:text-sm font-semibold rounded-md shadow-sm transition-all active:scale-95 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:ring-offset-gray-800">
                        <i class="fa-solid fa-cloud-arrow-down transition-transform group-hover:-translate-y-0.5"></i>
                        <span>Download All</span>
                    </button>
                </div>
            </div>
            @php
            function renderFileGroup($title, $icon, $category)
            {
            @endphp
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button @click="toggleSection('{{$category}}')"
                    class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                    :aria-expanded="openSections.includes('{{$category}}')">
                    <div class="flex flex-col gap-0.5">
                        <div class="flex items-center">
                            <i class="fa-solid {{$icon}} mr-3 w-4 text-center text-gray-500 dark:text-gray-400"></i>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $title }}
                            </span>
                        </div>

                        @if ($category === '3d')
                        <div class="ml-7 flex items-center text-[10px] text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-circle-info text-blue-500 mr-1.5 opacity-75"></i>
                            <span>Preview available for .igs/.iges, .stp/.step files only</span>
                        </div>
                    @endif
                    </div>
                    <span
                        class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full"
                        x-text="`${(pkg.files['{{$category}}']?.length || 0)} files`"></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform"
                        :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
                </button>
                <div x-show="openSections.includes('{{$category}}')" x-collapse>
                    <div class="p-2 max-h-72 overflow-y-auto">
                        <template x-for="file in (pkg.files['{{$category}}'] || [])" :key="file.name">
                            <div @click="selectFile(file)"
                                :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}"
                                class="flex items-center justify-between p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                role="button" tabindex="0" @keydown.enter="selectFile(file)">

                                <div class="flex items-center min-w-0 pr-2">
                                    <template x-if="file.icon_src">
                                        <img :src="file.icon_src" alt="" class="w-5 h-5 mr-3 object-contain" />
                                    </template>

                                    <template x-if="!file.icon_src">
                                        <i
                                            class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 transition-colors group-hover:text-blue-500"></i>
                                    </template>
                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate"
                                        x-text="file.name" :title="file.name"></span>
                                </div>

                                <button @click.stop="downloadFile(file)"
                                    class="flex-shrink-0 text-xs inline-flex items-center gap-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="(pkg.files['{{$category}}'] || []).length === 0">
                            <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No files available.</p>
                        </template>
                    </div>
                </div>
            </div>
            @php } @endphp

            {{ renderFileGroup('2D Drawings', 'fa-drafting-compass', '2d') }}
            {{ renderFileGroup('3D Models', 'fa-cubes', '3d') }}
            {{ renderFileGroup('ECN / Documents', 'fa-file-lines', 'ecn') }}
        </div>

        <div class="lg:col-span-8">
           <div x-ref="container2D" 
         class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden relative flex flex-col"
         :class="is2DFullscreen ? 'fixed inset-0 z-[100] rounded-none border-none' : ''">
                <div x-show="!selectedFile" x-cloak
                    class="flex flex-col items-center justify-center h-96 p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
                    <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500"></i>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to
                        review.</p>
                </div>

                <div x-show="selectedFile" x-transition.opacity x-cloak class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate"
                                x-text="selectedFile?.name"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="fileSizeInfo()">
                            </p>
                        </div>
                        <button @click="toggle2DFullscreen()" 
                        class="p-2 ml-4 text-gray-500 hover:text-blue-600 transition-colors flex items-center gap-2 text-sm font-medium" 
                        title="Toggle Fullscreen">
                    <span x-text="is2DFullscreen ? 'Exit Fullscreen' : 'Fullscreen'"></span>
                    <i class="fa-solid" :class="is2DFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                </button>
                    </div>


                    <div x-show="isImage(selectedFile?.name) || isTiff(selectedFile?.name) || isHpgl(selectedFile?.name) || isPdf(selectedFile?.name)" class="px-4 py-2 mb-4 flex items-center justify-end gap-3 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">

                        <div class="flex items-center bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm">
                            <button @click="zoomOut()" class="p-1.5 px-3 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-l-md transition-colors" title="Zoom Out">
                                <i class="fa-solid fa-minus fa-xs"></i>
                            </button>
                            <span class="px-2 text-xs font-mono font-semibold text-gray-600 dark:text-gray-300 border-l border-r border-gray-200 dark:border-gray-600 min-w-[3.5rem] text-center"
                                x-text="Math.round(imageZoom * 100) + '%'"></span>
                            <button @click="zoomIn()" class="p-1.5 px-3 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors" title="Zoom In">
                                <i class="fa-solid fa-plus fa-xs"></i>
                            </button>
                            <button @click="resetZoom()" class="p-1.5 px-3 text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:bg-gray-50 dark:hover:bg-gray-600 border-l border-gray-200 dark:border-gray-600 rounded-r-md transition-colors" title="Reset Fit">
                                <i class="fa-solid fa-compress fa-xs"></i>
                            </button>
                        </div>

                        <div x-show="isPdf(selectedFile?.name)" class="flex items-center gap-2 pl-3 border-l border-gray-300 dark:border-gray-600">
                            <button @click="prevPdfPage()" :disabled="pdfPageNum <= 1"
                                class="p-1.5 text-gray-500 hover:text-blue-600 disabled:opacity-30 disabled:hover:text-gray-500 transition-colors">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap min-w-[3rem] text-center">
                                <span x-text="pdfPageNum"></span> / <span x-text="pdfNumPages"></span>
                            </span>
                            <button @click="nextPdfPage()" :disabled="pdfPageNum >= pdfNumPages"
                                class="p-1.5 text-gray-500 hover:text-blue-600 disabled:opacity-30 disabled:hover:text-gray-500 transition-colors">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                        <div x-show="isTiff(selectedFile?.name) && tifNumPages > 1"
                            class="flex items-center gap-2 pl-3 border-l border-gray-300 dark:border-gray-600">
                            <button @click="prevTifPage()" :disabled="tifPageNum <= 1"
                                class="p-1.5 text-gray-500 hover:text-blue-600 disabled:opacity-30 disabled:hover:text-gray-500 transition-colors">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap min-w-[3rem] text-center">
                                <span x-text="tifPageNum"></span> / <span x-text="tifNumPages"></span>
                            </span>
                            <button @click="nextTifPage()" :disabled="tifPageNum >= tifNumPages"
                                class="p-1.5 text-gray-500 hover:text-blue-600 disabled:opacity-30 disabled:hover:text-gray-500 transition-colors">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div
                        class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center w-full relative">

                        <template x-if="isImage(selectedFile?.name)">
                            <div class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="relative inline-block" :style="imageTransformStyle()">
                                        <img x-ref="mainImage"
                                            :src="selectedFile?.url"
                                            @@load="imgLoading = false"
                                            @@error="imgLoading = false"
                                            alt="Preview"
                                            class="..."
                                            loading="lazy">

                                        <!-- STAMP ORIGINAL -->
                                        <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('original')">
                                            <div
                                                class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"

                                                :class="[
                                                    stampOriginClass('original'),
                                                    isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                                                ]"

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

                                        <!-- STAMP COPY -->
                                        <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('copy')">
                                            <div :class="stampOriginClass('copy')"
                                                class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                style="transform: scale(0.45);">
                                                <div
                                                    class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                                    <span x-text="stampTopLine('copy')"></span>
                                                </div>
                                                <div class="flex-1 flex items-center justify-center">
                                                    <span
                                                        class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                                        x-text="stampCenterCopy()"></span>
                                                </div>
                                                <div
                                                    class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                                    <span x-text="stampBottomLine('copy')"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- STAMP OBSOLETE -->
                                        <div x-show="pkg.stamp?.is_obsolete"
                                            class="absolute"
                                            :class="stampPositionClass('obsolete')">
                                            <div
                                                :class="stampOriginClass('obsolete')"
                                                class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                style="transform: scale(0.45);">
                                                <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
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
                                <div x-show="imgLoading"
                                    x-transition.opacity
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-10 backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Loading Image...</span>
                                </div>
                            </div>
                        </template>

                        <template x-if="isPdf(selectedFile?.name)">
                            <div class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="relative inline-block" :style="imageTransformStyle()">
                                        <canvas x-ref="pdfCanvas"
                                            class="block pointer-events-none select-none max-w-full max-h-[70vh]">
                                        </canvas>

                                        <!-- STAMP ORIGINAL -->
                                        <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('original')">
                                            <div
                                                class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"

                                                :class="[
                                                    stampOriginClass('original'),
                                                    isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                                                ]"

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

                                        <!-- STAMP COPY -->
                                        <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('copy')">
                                            <div :class="stampOriginClass('copy')"
                                                class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                style="transform: scale(0.45);">
                                                <div
                                                    class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                                    <span x-text="stampTopLine('copy')"></span>
                                                </div>
                                                <div class="flex-1 flex items-center justify-center">
                                                    <span
                                                        class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                                        x-text="stampCenterCopy()"></span>
                                                </div>
                                                <div
                                                    class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                                    <span x-text="stampBottomLine('copy')"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- STAMP OBSOLETE -->
                                        <div x-show="pkg.stamp?.is_obsolete"
                                            class="absolute"
                                            :class="stampPositionClass('obsolete')">
                                            <div
                                                :class="stampOriginClass('obsolete')"
                                                class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                style="transform: scale(0.45);">
                                                <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
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

                                <div x-show="pdfLoading"
                                    x-transition.opacity
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-10 backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-red-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Rendering PDF...</span>
                                </div>
                                <div x-show="pdfError" class="..." x-text="pdfError"></div>
                            </div>
                        </template>


                        <template x-if="isTiff(selectedFile?.name)">
                            <div class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="relative inline-block" :style="imageTransformStyle()">
                                        <img x-ref="tifImg" alt="TIFF Preview"
                                            class="block pointer-events-none select-none max-w-full max-h-[70vh]" />

                                        <!-- STAMP ORIGINAL -->
                                        <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('original')">
                                            <div
                                                class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"

                                                :class="[
                                                    stampOriginClass('original'),
                                                    isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                                                ]"

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

                                        <!-- STAMP COPY -->
                                        <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('copy')">
                                            <div :class="stampOriginClass('copy')"
                                                class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                style="transform: scale(0.45);">
                                                <div
                                                    class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                                    <span x-text="stampTopLine('copy')"></span>
                                                </div>
                                                <div class="flex-1 flex items-center justify-center">
                                                    <span
                                                        class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                                        x-text="stampCenterCopy()"></span>
                                                </div>
                                                <div
                                                    class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                                    <span x-text="stampBottomLine('copy')"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- STAMP OBSOLETE -->
                                        <div x-show="pkg.stamp?.is_obsolete"
                                            class="absolute"
                                            :class="stampPositionClass('obsolete')">
                                            <div
                                                :class="stampOriginClass('obsolete')"
                                                class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                style="transform: scale(0.45);">
                                                <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
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

                                <div x-show="tifLoading"
                                    x-transition.opacity
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-10 backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Processing TIFF...</span>
                                </div>

                                <div x-show="tifError" class="..." x-text="tifError"></div>
                            </div>
                        </template>


                        <template x-if="isHpgl(selectedFile?.name)">
                            <div class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                                @mousedown.prevent="startPan($event)" @wheel.prevent="onWheelZoom($event)">
                                <div class="relative w-full h-full flex items-center justify-center"
                                    :style="imageTransformStyle()">

                                    <!-- Wrapper with relative positioning for stamps -->
                                    <div class="relative inline-block">
                                        <canvas x-ref="hpglCanvas" class="pointer-events-none select-none"></canvas>

                                        <!-- Stamp overlay positioned exactly over the drawing area -->
                                        <div class="absolute pointer-events-none"
                                             :style="`left: ${hpglDrawingBounds.left}px; top: ${hpglDrawingBounds.top}px; width: ${hpglDrawingBounds.width}px; height: ${hpglDrawingBounds.height}px;`">

                                            <!-- STAMP ORIGINAL -->
                                            <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('original')">
                                                <div :class="[
                                                        stampOriginClass('original'),
                                                        isEngineering ? 'border-blue-600 text-blue-700' : 'border-gray-500 text-gray-600'
                                                    ]"
                                                    class="min-w-65 w-auto h-20 border-2 rounded-sm text-[10px] opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                    style="transform: scale(0.45);">
                                                    <div
                                                        class="w-full text-center border-b-2 py-0.5 px-4 font-semibold tracking-tight"
                                                        :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                                        <span x-text="stampTopLine('original')"></span>
                                                    </div>
                                                    <div class="flex-1 flex items-center justify-center">
                                                        <span
                                                            class="text-xs font-extrabold uppercase px-2"
                                                            :class="isEngineering ? 'text-blue-700' : 'text-gray-600'"
                                                            x-text="stampCenterOriginal()"></span>
                                                    </div>
                                                    <div
                                                        class="w-full border-t-2 py-0.5 px-4 text-center font-semibold tracking-tight"
                                                        :class="isEngineering ? 'border-blue-600' : 'border-gray-500'">
                                                        <span x-text="stampBottomLine('original')"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- STAMP COPY -->
                                            <div x-show="pkg.stamp" class="absolute" :class="stampPositionClass('copy')">
                                                <div :class="stampOriginClass('copy')"
                                                    class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                    style="transform: scale(0.45);">
                                                    <div
                                                        class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                                                        <span x-text="stampTopLine('copy')"></span>
                                                    </div>
                                                    <div class="flex-1 flex items-center justify-center">
                                                        <span
                                                            class="text-xs font-extrabold uppercase text-blue-700 px-2"
                                                            x-text="stampCenterCopy()"></span>
                                                    </div>
                                                    <div
                                                        class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                                                        <span x-text="stampBottomLine('copy')"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- STAMP OBSOLETE -->
                                            <div x-show="pkg.stamp?.is_obsolete"
                                                class="absolute"
                                                :class="stampPositionClass('obsolete')">
                                                <div
                                                    :class="stampOriginClass('obsolete')"
                                                    class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                                                    style="transform: scale(0.45);">
                                                    <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
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

                                <div x-show="hpglLoading"
                                    x-transition.opacity
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-10 backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-green-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Rendering Plotter File...</span>
                                </div>

                                <div x-show="hpglError" class="absolute bottom-3 left-3 text-xs text-red-600 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded" x-text="hpglError"></div>
                            </div>
                    </div>
    

                    </template>

                    <template x-if="isCad(selectedFile?.name)">
                        <div x-ref="cadContainer"
                            class="w-full flex flex-col transition-all duration-300"
                            :class="isFullscreen ? 'fixed inset-0 z-50 h-screen bg-gray-100 dark:bg-gray-900 p-4 overflow-y-auto' : 'h-[75vh]'">

                            <div class="flex-1 relative border border-gray-200 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-900 overflow-hidden group">

                                <button @click="isPartListOpen = !isPartListOpen"
                                    x-show="cadPartsList.length > 0 && !iges.loading"
                                    class="absolute top-3 left-3 z-30 px-3 py-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-md border border-gray-200 dark:border-gray-700 rounded text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
                                    <i class="fa-solid" :class="isPartListOpen ? 'fa-xmark' : 'fa-list-tree'"></i>
                                    <span x-text="isPartListOpen ? 'Close List' : 'Part List'"></span>
                                    <span class="bg-gray-200 dark:bg-gray-600 text-[10px] px-1.5 rounded-full ml-1"
                                        x-text="cadPartsList.length"></span>
                                </button>

                                <div x-show="isPartListOpen"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-x-2"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-x-0"
                                    x-transition:leave-end="opacity-0 -translate-x-2"
                                    class="absolute top-12 left-3 bottom-3 z-50 w-64 flex flex-col bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">

                                    <div class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-700/50 flex justify-between items-center flex-shrink-0">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">Assembly Tree</span>
                                        <button @click="isPartListOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
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

                                    <div x-show="selectedPartUuid"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="p-3 border-t border-blue-100 dark:border-gray-700 bg-blue-50/80 dark:bg-gray-800 flex-shrink-0">

                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider min-w-[50px]">
                                                Opacity
                                            </span>

                                            <input type="range"
                                                min="0.1" max="1.0" step="0.1"
                                                x-model.number="partOpacity"
                                                @input="updatePartOpacity()"
                                                class="flex-1 h-2 bg-blue-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer accent-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                                        </div>

                                        <div class="flex justify-between mt-1 px-1">
                                            <span class="text-[9px] text-gray-400">10%</span>
                                            <span class="text-[9px] font-mono text-blue-600" x-text="Math.round(partOpacity * 100) + '%'"></span>
                                            <span class="text-[9px] text-gray-400">100%</span>
                                        </div>
                                    </div>

                                    <div x-show="selectedPartUuid" class="...">
                                        <div class="mt-3 pt-2 border-t border-blue-100 dark:border-gray-600 grid grid-cols-2 gap-2">
                                            <div class="bg-white dark:bg-gray-900/50 p-1.5 rounded border border-gray-100 dark:border-gray-600">
                                                <div class="text-[9px] text-gray-400 uppercase font-bold">Volume</div>
                                                <div class="text-[10px] text-gray-700 dark:text-gray-200 font-mono" x-text="partInfo.volume"></div>
                                            </div>
                                            <div class="bg-white dark:bg-gray-900/50 p-1.5 rounded border border-gray-100 dark:border-gray-600">
                                                <div class="text-[9px] text-gray-400 uppercase font-bold">Area</div>
                                                <div class="text-[10px] text-gray-700 dark:text-gray-200 font-mono" x-text="partInfo.area"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Measurement Toggle -->
                                <!-- Measurement Visibility Toggle (Only when Active) -->
                                <button @click="isMeasureListOpen = !isMeasureListOpen"
                                    x-show="isMeasureActive && !iges.loading"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-90"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    :title="isMeasureListOpen ? 'Hide Measurements' : 'Show Measurements'"
                                    class="absolute top-3 right-14 z-30 px-3 py-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-md border border-gray-200 dark:border-gray-700 rounded text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
                                    <i class="fa-solid" :class="isMeasureListOpen ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    <span x-text="isMeasureListOpen ? 'Hide Info' : 'Show Info'"></span>
                                    <span class="bg-gray-200 dark:bg-gray-600 text-[10px] px-1.5 rounded-full ml-1"
                                        x-text="iges.measure.results.length" x-show="iges.measure.results.length > 0"></span>
                                </button>

                                <!-- Measurement List Panel -->
                                <div x-show="isMeasureListOpen"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-x-2"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-x-0"
                                    x-transition:leave-end="opacity-0 translate-x-2"
                                    class="absolute top-12 right-3 z-50 w-64 flex flex-col bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden max-h-[60vh]">

                                    <div class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-700/50 flex justify-between items-center flex-shrink-0">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">Measurements</span>
                                        <button @click="clearMeasurements()" class="text-[10px] text-red-500 hover:underline">Clear All</button>
                                    </div>

                                    <div class="flex-1 overflow-y-auto p-1 custom-scrollbar min-h-0">
                                        <template x-if="iges.measure.results.length === 0">
                                            <div class="p-4 text-center text-gray-400 dark:text-gray-500 text-xs italic">
                                                No measurements yet.<br>Select points to measure.
                                            </div>
                                        </template>
                                        <ul class="space-y-1 p-1">
                                            <template x-for="(res, idx) in iges.measure.results" :key="idx">
                                                <li class="bg-white dark:bg-gray-900 p-2 rounded border border-gray-200 dark:border-gray-600 shadow-sm relative group hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                                                    <button @click="deleteMeasurement(idx)" class="absolute top-1 right-1 text-gray-300 hover:text-red-500 p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <i class="fa-solid fa-times-circle text-xs"></i>
                                                    </button>

                                                    <div class="flex items-center gap-2 mb-1.5">
                                                        <div class="w-5 h-5 rounded bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                                            <i class="fa-solid text-[10px]"
                                                               :class="{
                                                                   'fa-ruler-horizontal': res.type === 'point',
                                                                   'fa-minus': res.type === 'edge',
                                                                   'fa-angle-left': res.type === 'angle',
                                                                   'fa-circle-notch': res.type === 'radius',
                                                                   'fa-vector-square': res.type === 'face'
                                                               }"></i>
                                                        </div>
                                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase" x-text="res.type"></span>
                                                    </div>

                                                    <div class="space-y-1 pl-1">
                                                        <template x-if="res.distance !== undefined">
                                                            <div class="flex justify-between items-baseline text-xs">
                                                                <span class="text-gray-500">Dist:</span>
                                                                <span class="font-mono font-bold text-blue-600 dark:text-blue-400" x-text="Number(res.distance).toFixed(2) + ' mm'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="res.angle !== undefined">
                                                            <div class="flex justify-between items-baseline text-xs">
                                                                <span class="text-gray-500">Angle:</span>
                                                                <span class="font-mono font-bold text-purple-600 dark:text-purple-400" x-text="Number(res.angle).toFixed(2) + '°'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="res.radius !== undefined">
                                                            <div class="flex justify-between items-baseline text-xs">
                                                                <span class="text-gray-500">Radius:</span>
                                                                <span class="font-mono font-bold text-green-600 dark:text-green-400" x-text="Number(res.radius).toFixed(2) + ' mm'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="res.diameter !== undefined">
                                                            <div class="flex justify-between items-baseline text-xs">
                                                                <span class="text-gray-500">Diameter:</span>
                                                                <span class="font-mono font-bold text-teal-600 dark:text-teal-400" x-text="Number(res.diameter).toFixed(2) + ' mm'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="res.area !== undefined">
                                                            <div class="flex justify-between items-baseline text-xs">
                                                                <span class="text-gray-500">Area:</span>
                                                                <span class="font-mono font-bold text-orange-600 dark:text-orange-400" x-text="Number(res.area).toFixed(2) + ' mm²'"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="res.deltaX !== undefined">
                                                            <div class="flex gap-1 pt-1 mt-1 border-t border-gray-100 dark:border-gray-700 text-[9px] font-mono justify-between">
                                                                <span class="text-red-500 bg-red-50 dark:bg-red-900/20 rounded px-1">ΔX: <span x-text="Number(res.deltaX).toFixed(1)"></span></span>
                                                                <span class="text-green-500 bg-green-50 dark:bg-green-900/20 rounded px-1">ΔY: <span x-text="Number(res.deltaY).toFixed(1)"></span></span>
                                                                <span class="text-blue-500 bg-blue-50 dark:bg-blue-900/20 rounded px-1">ΔZ: <span x-text="Number(res.deltaZ).toFixed(1)"></span></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                                


                                <button @click="toggleFullscreen()" title="Toggle Fullscreen"
                                    x-show="!iges.loading"
                                    class="absolute top-3 right-3 z-30 w-8 h-8 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-md border border-gray-200 dark:border-gray-700 rounded text-gray-600 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                                    <i class="fa-solid" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                                </button>

                                <div x-ref="igesWrap" class="w-full h-full bg-black/5 cursor-grab active:cursor-grabbing">
                                </div>

                                <div x-show="iges.loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 dark:bg-gray-900/80 z-10 backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Processing CAD Geometry...</span>
                                </div>

                                <div x-show="iges.error" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-red-100 border border-red-300 text-red-700 rounded-md shadow-lg text-xs" x-text="iges.error"></div>

                                <!-- Floating 3D Navigation Controls -->
                                <div x-show="isCad(selectedFile?.name) && !iges.loading"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-x-4"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    class="absolute bottom-36 right-6 flex flex-col items-center bg-white/40 dark:bg-gray-900/50 backdrop-blur-xl rounded-2xl border border-white/40 dark:border-gray-700/50 shadow-lg p-1 z-40">
                                    
                                    <button @click="zoom3d(1.25)" 
                                        class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-800/50 hover:text-blue-600 transition-all active:scale-75"
                                        title="Zoom In (+)">
                                        <i class="fa-solid fa-plus text-sm"></i>
                                    </button>
                                    
                                    <div class="w-6 h-px bg-gray-400/20 dark:bg-gray-600/30 my-1"></div>

                                    <button @click="zoom3d(0.8)"
                                        class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-800/50 hover:text-blue-600 transition-all active:scale-75"
                                        title="Zoom Out (-)">
                                        <i class="fa-solid fa-minus text-sm"></i>
                                    </button>

                                    <div class="w-6 h-px bg-gray-400/20 dark:bg-gray-600/30 my-1"></div>

                                    <button @click="resetCamera3d()"
                                        class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-800/50 hover:text-blue-600 transition-all active:scale-75"
                                        title="Reset View (Home)">
                                        <i class="fa-solid fa-house-chimney text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="absolute left-1/2 -translate-x-1/2 z-40 bg-white/40 dark:bg-gray-900/50 backdrop-blur-xl p-2 lg:p-3 rounded-2xl border border-white/40 dark:border-gray-700/50 shadow-lg min-w-[300px] transition-all duration-500 flex flex-wrap items-center justify-center gap-2 lg:gap-3 select-none origin-bottom"
                                :class="isFullscreen ? 'scale-100 bottom-8' : 'scale-90 bottom-6'"
                                x-data="{ isViewMenuOpen: false, isMatMenuOpen: false }"
                                x-show="!iges.loading"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0">

                                <!-- Unified Tool Group -->
                                <div class="flex flex-wrap items-center justify-center gap-2 lg:gap-3">

                                    <!-- GROUP 1: Display Settings -->
                                    <div class="flex items-center gap-1.5 lg:gap-2 px-2 lg:px-2.5 py-1 lg:py-1.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <span class="hidden lg:inline text-[9px] font-bold text-gray-400 uppercase tracking-wider px-1">Display</span>

                                        <div class="inline-flex bg-white dark:bg-gray-700 p-0.5 rounded border border-gray-200 dark:border-gray-600">
                                            <button @click="setDisplayStyle('shaded')" class="px-2 lg:px-2.5 py-1 text-[10px] lg:text-xs font-semibold rounded transition-all"
                                                :class="currentStyle === 'shaded' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300'">
                                                Shaded
                                            </button>
                                            <button @click="setDisplayStyle('shaded-edges')" class="px-2 lg:px-2.5 py-1 text-[10px] lg:text-xs font-semibold rounded transition-all"
                                                :class="currentStyle === 'shaded-edges' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300'">
                                                Edges
                                            </button>
                                            <button @click="setDisplayStyle('wireframe')" class="px-2 lg:px-2.5 py-1 text-[10px] lg:text-xs font-semibold rounded transition-all"
                                                :class="currentStyle === 'wireframe' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300'">
                                                Wire
                                            </button>
                                        </div>

                                        <div class="h-6 lg:h-7 w-px bg-gray-300 dark:bg-gray-600"></div>

                                        <div class="relative">
                                            <button @click="isMatMenuOpen = !isMatMenuOpen" @click.outside="isMatMenuOpen = false" title="Material"
                                                class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 transition text-xs lg:text-sm"
                                                :class="activeMaterial !== 'default' ? 'text-purple-600 border-purple-300 bg-purple-50' : 'text-gray-600 dark:text-gray-200'">
                                                <i class="fa-solid fa-fill-drip"></i>
                                            </button>

                                        <div x-show="isMatMenuOpen" x-transition
                                            class="absolute bottom-full left-0 mb-2 p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-32 flex flex-col gap-1">
                                            <div class="text-[9px] font-bold text-gray-400 px-2 py-1 uppercase tracking-wider">Material</div>



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
                                                <i class="fa-solid fa-square-full text-blue-200 opacity-50 text-[8px]"></i> Glass
                                            </button>

                                            <button @click="setMaterialMode('ecoat'); isMatMenuOpen=false"
                                                class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                :class="activeMaterial==='ecoat' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                                <i class="fa-solid fa-square text-gray-500 text-[10px]"></i> E-Coat
                                            </button>

                                            <button @click="setMaterialMode('steel'); isMatMenuOpen=false"
                                                class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                :class="activeMaterial==='steel' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                                <i class="fa-solid fa-square text-gray-300 text-[10px]"></i> Raw Steel
                                            </button>

                                            <button @click="setMaterialMode('aluminum'); isMatMenuOpen=false"
                                                class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                :class="activeMaterial==='aluminum' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                                <i class="fa-solid fa-square text-white border border-gray-300 text-[10px]"></i> Aluminum
                                            </button>

                                            <button @click="setMaterialMode('zinc'); isMatMenuOpen=false"
                                                class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                :class="activeMaterial==='zinc' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
                                                <i class="fa-solid fa-circle text-yellow-500 text-[8px]"></i> Yellow Zinc
                                            </button>

                                            <button @click="setMaterialMode('redox'); isMatMenuOpen=false"
                                                class="text-left px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                :class="activeMaterial==='redox' ? 'font-bold text-blue-600' : 'text-gray-700 dark:text-gray-200'">
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

                                    <!-- GROUP 2: View Controls -->
                                    <div class="flex items-center gap-1.5 lg:gap-2 px-2 lg:px-2.5 py-1 lg:py-1.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <span class="hidden lg:inline text-[9px] font-bold text-gray-400 uppercase tracking-wider px-1">View</span>

                                        <button @click="toggleCameraMode()"
                                            :title="cameraMode === 'perspective' ? 'View: Perspective (C)' : 'View: Orthographic (C)'"
                                            class="w-7 h-7 lg:w-8 lg:h-8 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 flex flex-col items-center justify-center gap-0.5 transition-all active:scale-95">
                                            <i class="fa-solid text-[10px] lg:text-xs" :class="cameraMode === 'perspective' ? 'fa-cube' : 'fa-border-none'"></i>
                                            <span x-text="cameraMode === 'perspective' ? 'Persp' : 'Ortho'" class="text-[7px] lg:text-[8px] font-bold leading-none"></span>
                                        </button>

                                        <div class="relative">
                                            <button @click="isViewMenuOpen = !isViewMenuOpen" @click.outside="isViewMenuOpen = false" title="Standard Views"
                                                class="w-7 h-7 lg:w-8 lg:h-8 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 flex items-center justify-center transition text-xs lg:text-sm">
                                                <i class="fa-solid fa-dice-d6"></i>
                                            </button>

                                            <div x-show="isViewMenuOpen" x-transition
                                                class="absolute bottom-full left-0 mb-2 p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-28 lg:w-32 flex flex-col gap-1">
                                                <div class="text-[9px] font-bold text-gray-400 px-2 py-1 uppercase tracking-wider">Views</div>
                                                <button @click="setStandardView('front'); isViewMenuOpen=false" class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Front (F)</button>
                                                <button @click="setStandardView('back'); isViewMenuOpen=false" class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Back (B)</button>
                                                <button @click="setStandardView('top'); isViewMenuOpen=false" class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Top (T)</button>
                                                <button @click="setStandardView('bottom'); isViewMenuOpen=false" class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Bottom (D)</button>
                                                <button @click="setStandardView('left'); isViewMenuOpen=false" class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Left (L)</button>
                                                <button @click="setStandardView('right'); isViewMenuOpen=false" class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Right (R)</button>
                                                <div class="h-px bg-gray-100 dark:bg-gray-700 my-0.5"></div>
                                                <button @click="setStandardView('iso'); isViewMenuOpen=false" class="text-left px-2 py-1.5 lg:py-2 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">Isometric (I)</button>
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

                                    <!-- GROUP 3: Analysis Tools -->
                                    <div class="flex items-center gap-1.5 lg:gap-2 px-2 lg:px-2.5 py-1 lg:py-1.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <span class="hidden lg:inline text-[9px] font-bold text-gray-400 uppercase tracking-wider px-1">Analysis</span>

                                        <div class="relative">
                                            <button @click="if(!explode.enabled) toggleExplode(); else explode.panelOpen = !explode.panelOpen"
                                                :class="explode.enabled ? 'text-blue-600 bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-700 shadow-sm' : 'text-gray-600 dark:text-gray-400'"
                                                class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-xs lg:text-sm active:scale-95"
                                                title="Exploded View (X)">
                                                <i class="fa-solid fa-expand-arrows-alt" :class="explode.enabled ? 'scale-110' : ''"></i>
                                            </button>

                                            <!-- Professional Explode Panel -->
                                            <div x-show="explode.panelOpen"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                @click.outside="explode.panelOpen = false"
                                                class="absolute bottom-full mb-2 left-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-48 lg:w-56 overflow-hidden">
                                                
                                                <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                                                    <div class="flex items-center gap-1.5">
                                                        <i class="fa-solid fa-expand-arrows-alt text-blue-500 text-[10px]"></i>
                                                        <span class="text-[10px] lg:text-xs font-bold text-gray-700 dark:text-gray-200">Exploded View</span>
                                                    </div>
                                                    <button @click="toggleExplode()" class="text-[9px] text-red-600 dark:text-red-400 hover:underline font-medium">Disable</button>
                                                </div>

                                                <div class="p-3">
                                                     <div class="space-y-3">
                                                         <div class="flex items-center justify-between">
                                                             <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Explosion Factor</span>
                                                             <div class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-[10px] font-mono font-bold text-blue-600 dark:text-blue-400">
                                                                 <span x-text="explode.value"></span>%
                                                             </div>
                                                         </div>
                                                         
                                                         <input type="range" min="0" max="100" x-model.number="explode.value" @input="updateExplode()"
                                                             class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                                         
                                                         <div class="flex items-center justify-between gap-2">
                                                             <button @click="explode.value = 0; updateExplode()" 
                                                                 class="flex-1 text-[9px] py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                                 Reset
                                                             </button>
                                                             <button @click="explode.value = 100; updateExplode()" 
                                                                 class="flex-1 text-[9px] py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-semibold">
                                                                 Maximum
                                                             </button>
                                                         </div>
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>

                                        <!-- Section Cut Multi-Axis Panel -->
                                        <div class="relative">
                                            <!-- Toggle Button -->
                                            <button @click="clipping.panelOpen = !clipping.panelOpen"
                                                :class="hasActiveClipping ? 'text-blue-600 bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-700 shadow-sm' : 'text-gray-600 dark:text-gray-400'"
                                                class="w-7 h-7 lg:w-8 lg:h-8 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-xs lg:text-sm active:scale-95"
                                                title="Section Cut (S)">
                                                <i class="fa-solid fa-scissors rotate-90" :class="hasActiveClipping ? 'scale-110' : ''"></i>
                                            </button>

                                        <!-- Compact Panel -->
                                        <div x-show="clipping.panelOpen"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                            @click.outside="clipping.panelOpen = false"
                                            class="absolute bottom-full mb-2 right-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 w-64">

                                            <!-- Header -->
                                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-600">
                                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Section Cut</span>
                                                <button @click="resetAllClipping()"
                                                    class="text-[10px] text-red-600 dark:text-red-400 hover:underline font-medium"
                                                    x-show="hasActiveClipping">
                                                    Reset
                                                </button>
                                            </div>

                                            <!-- Compact Axis Controls -->
                                            <div class="p-2 space-y-1.5">
                                                <!-- X Axis -->
                                                <div>
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <input type="checkbox"
                                                            :checked="clipping.x.enabled"
                                                            @change="toggleAxisClipping('x')"
                                                            class="rounded text-red-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                        <div class="w-5 h-5 rounded bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                            <span class="text-[10px] font-bold text-red-600 dark:text-red-400">X</span>
                                                        </div>
                                                        <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">X-Axis</span>

                                                        <!-- Helper Toggle -->
                                                        <button @click="togglePlaneHelper('x')"
                                                            x-show="clipping.x.enabled"
                                                            :class="clipping.x.showHelper ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400'"
                                                            class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                            title="Toggle Plane Helper">
                                                            <i class="fa-solid fa-eye text-[9px]"></i>
                                                        </button>

                                                        <!-- Flip Button -->
                                                        <button @click="flipAxis('x')"
                                                            x-show="clipping.x.enabled"
                                                            :class="clipping.x.flipped ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400'"
                                                            class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                            title="Flip Direction">
                                                            <i class="fa-solid fa-right-left text-[9px]"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Enhanced Controls -->
                                                    <div x-show="clipping.x.enabled" x-transition class="pl-6 space-y-1.5">
                                                        <!-- Value Display -->
                                                        <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                            <span>Position:</span>
                                                            <span class="font-mono font-semibold text-red-600 dark:text-red-400" x-text="clipping.x.value.toFixed(2)"></span>
                                                        </div>

                                                        <!-- Slider -->
                                                        <input type="range"
                                                            :min="clipping.x.min !== undefined ? clipping.x.min : clipping.min"
                                                            :max="clipping.x.max !== undefined ? clipping.x.max : clipping.max"
                                                            x-model.number="clipping.x.value"
                                                            @input="updateAxisClipping('x')"
                                                            class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-red-600">

                                                        <!-- Numeric Input + Step Controls -->
                                                        <div class="flex items-center gap-1">
                                                            <!-- Decrement -->
                                                            <button @click="decrementAxisValue('x')"
                                                                class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 hover:text-red-600 transition"
                                                                title="Decrease">
                                                                <i class="fa-solid fa-minus text-[9px]"></i>
                                                            </button>

                                                            <!-- Numeric Input -->
                                                            <input type="number"
                                                                :min="clipping.x.min !== undefined ? clipping.x.min : clipping.min"
                                                                :max="clipping.x.max !== undefined ? clipping.x.max : clipping.max"
                                                                :step="clipping.step"
                                                                x-model.number="clipping.x.value"
                                                                @input="setAxisValueDirect('x', $event.target.value)"
                                                                class="flex-1 px-2 py-1 text-[10px] text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-red-500 focus:border-red-500">

                                                            <!-- Increment -->
                                                            <button @click="incrementAxisValue('x')"
                                                                class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 hover:text-red-600 transition"
                                                                title="Increase">
                                                                <i class="fa-solid fa-plus text-[9px]"></i>
                                                            </button>
                                                        </div>

                                                        <!-- Additional Options -->
                                                        <div class="flex items-center gap-2 pt-1">
                                                            <label class="flex items-center gap-1 text-[10px] text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="checkbox"
                                                                    x-model="clipping.x.showCap"
                                                                    @change="toggleSectionCap('x')"
                                                                    class="rounded text-red-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3 h-3">
                                                                <span>Show Cap</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Y Axis -->
                                                <div>
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <input type="checkbox"
                                                            :checked="clipping.y.enabled"
                                                            @change="toggleAxisClipping('y')"
                                                            class="rounded text-green-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                        <div class="w-5 h-5 rounded bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                            <span class="text-[10px] font-bold text-green-600 dark:text-green-400">Y</span>
                                                        </div>
                                                        <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">Y-Axis</span>

                                                        <button @click="togglePlaneHelper('y')"
                                                            x-show="clipping.y.enabled"
                                                            :class="clipping.y.showHelper ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                                            class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                            title="Toggle Plane Helper">
                                                            <i class="fa-solid fa-eye text-[9px]"></i>
                                                        </button>

                                                        <button @click="flipAxis('y')"
                                                            x-show="clipping.y.enabled"
                                                            :class="clipping.y.flipped ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                                            class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                            title="Flip Direction">
                                                            <i class="fa-solid fa-right-left text-[9px]"></i>
                                                        </button>
                                                    </div>

                                                    <div x-show="clipping.y.enabled" x-transition class="pl-6 space-y-1.5">
                                                        <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                            <span>Position:</span>
                                                            <span class="font-mono font-semibold text-green-600 dark:text-green-400" x-text="clipping.y.value.toFixed(2)"></span>
                                                        </div>

                                                        <input type="range"
                                                            :min="clipping.y.min !== undefined ? clipping.y.min : clipping.min"
                                                            :max="clipping.y.max !== undefined ? clipping.y.max : clipping.max"
                                                            x-model.number="clipping.y.value"
                                                            @input="updateAxisClipping('y')"
                                                            class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-green-600">

                                                        <div class="flex items-center gap-1">
                                                            <button @click="decrementAxisValue('y')"
                                                                class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-green-100 dark:hover:bg-green-900/30 text-gray-600 dark:text-gray-300 hover:text-green-600 transition"
                                                                title="Decrease">
                                                                <i class="fa-solid fa-minus text-[9px]"></i>
                                                            </button>

                                                            <input type="number"
                                                                :min="clipping.y.min !== undefined ? clipping.y.min : clipping.min"
                                                                :max="clipping.y.max !== undefined ? clipping.y.max : clipping.max"
                                                                :step="clipping.step"
                                                                x-model.number="clipping.y.value"
                                                                @input="setAxisValueDirect('y', $event.target.value)"
                                                                class="flex-1 px-2 py-1 text-[10px] text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-green-500 focus:border-green-500">

                                                            <button @click="incrementAxisValue('y')"
                                                                class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-green-100 dark:hover:bg-green-900/30 text-gray-600 dark:text-gray-300 hover:text-green-600 transition"
                                                                title="Increase">
                                                                <i class="fa-solid fa-plus text-[9px]"></i>
                                                            </button>
                                                        </div>

                                                        <div class="flex items-center gap-2 pt-1">
                                                            <label class="flex items-center gap-1 text-[10px] text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="checkbox"
                                                                    x-model="clipping.y.showCap"
                                                                    @change="toggleSectionCap('y')"
                                                                    class="rounded text-green-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3 h-3">
                                                                <span>Show Cap</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Z Axis -->
                                                <div>
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <input type="checkbox"
                                                            :checked="clipping.z.enabled"
                                                            @change="toggleAxisClipping('z')"
                                                            class="rounded text-blue-600 focus:ring-0 border-gray-300 dark:border-gray-600 w-3.5 h-3.5">
                                                        <div class="w-5 h-5 rounded bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                            <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">Z</span>
                                                        </div>
                                                        <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">Z-Axis</span>

                                                        <button @click="togglePlaneHelper('z')"
                                                            x-show="clipping.z.enabled"
                                                            :class="clipping.z.showHelper ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
                                                            class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                            title="Toggle Plane Helper">
                                                            <i class="fa-solid fa-eye text-[9px]"></i>
                                                        </button>

                                                        <button @click="flipAxis('z')"
                                                            x-show="clipping.z.enabled"
                                                            :class="clipping.z.flipped ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
                                                            class="w-5 h-5 flex items-center justify-center rounded hover:scale-110 transition-all"
                                                            title="Flip Direction">
                                                            <i class="fa-solid fa-right-left text-[9px]"></i>
                                                        </button>
                                                    </div>

                                                    <div x-show="clipping.z.enabled" x-transition class="pl-6 space-y-1.5">
                                                        <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400">
                                                            <span>Position:</span>
                                                            <span class="font-mono font-semibold text-blue-600 dark:text-blue-400" x-text="clipping.z.value.toFixed(2)"></span>
                                                        </div>

                                                        <input type="range"
                                                            :min="clipping.z.min !== undefined ? clipping.z.min : clipping.min"
                                                            :max="clipping.z.max !== undefined ? clipping.z.max : clipping.max"
                                                            x-model.number="clipping.z.value"
                                                            @input="updateAxisClipping('z')"
                                                            class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">

                                                        <div class="flex items-center gap-1">
                                                            <button @click="decrementAxisValue('z')"
                                                                class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-gray-600 dark:text-gray-300 hover:text-blue-600 transition"
                                                                title="Decrease">
                                                                <i class="fa-solid fa-minus text-[9px]"></i>
                                                            </button>

                                                            <input type="number"
                                                                :min="clipping.z.min !== undefined ? clipping.z.min : clipping.min"
                                                                :max="clipping.z.max !== undefined ? clipping.z.max : clipping.max"
                                                                :step="clipping.step"
                                                                x-model.number="clipping.z.value"
                                                                @input="setAxisValueDirect('z', $event.target.value)"
                                                                class="flex-1 px-2 py-1 text-[10px] text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">

                                                            <button @click="incrementAxisValue('z')"
                                                                class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-gray-600 dark:text-gray-300 hover:text-blue-600 transition"
                                                                title="Increase">
                                                                <i class="fa-solid fa-plus text-[9px]"></i>
                                                            </button>
                                                        </div>

                                                        <div class="flex items-center gap-2 pt-1">
                                                            <label class="flex items-center gap-1 text-[10px] text-gray-600 dark:text-gray-400 cursor-pointer">
                                                                <input type="checkbox"
                                                                    x-model="clipping.z.showCap"
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

                                        <button @click="toggleMeasure()" title="Measure Tool (M)"
                                            class="w-7 h-7 lg:w-8 lg:h-8 rounded border transition flex items-center justify-center text-xs lg:text-sm active:scale-95"
                                            :class="iges.measure.enabled ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white hover:bg-gray-50 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 dark:bg-gray-800'">
                                            <i class="fa-solid fa-ruler-combined" :class="iges.measure.enabled ? 'scale-110' : ''"></i>
                                        </button>
                                        <!-- Utilities -->
                                        <div class="h-6 lg:h-7 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>
                                        <button @click="takeScreenshot()" title="Screenshot"
                                            class="w-7 h-7 lg:w-8 lg:h-8 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-200 flex items-center justify-center transition text-xs lg:text-sm active:scale-90 shadow-sm">
                                            <i class="fa-solid fa-camera"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- NEW MEASURE TOOLBAR (Floating at Top Center of Viewer) -->
                                    <div x-show="iges.measure.enabled"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-4"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="absolute bottom-24 left-1/2 -translate-x-1/2 z-40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg flex items-center p-1 gap-1 transition-all duration-300">

                                        <!-- Mode Buttons -->
                                        <div class="flex items-center gap-0.5 border-r border-gray-200 dark:border-gray-600 pr-1 mr-1">
                                            <button @click="setMeasureMode('point')" :class="iges.measure.mode === 'point' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'" class="p-2 rounded text-xs transition relative group" title="Point to Point">
                                                <i class="fa-solid fa-ruler-horizontal"></i>
                                                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap z-50">Point to Point</span>
                                            </button>

                                            <button @click="setMeasureMode('edge')" :class="iges.measure.mode === 'edge' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'" class="p-2 rounded text-xs transition relative group" title="Edge Length">
                                                <i class="fa-solid fa-minus"></i>
                                                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap z-50">Edge Length</span>
                                            </button>

                                            <button @click="setMeasureMode('angle')" :class="iges.measure.mode === 'angle' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'" class="p-2 rounded text-xs transition relative group" title="Angle (3 Points)">
                                                <i class="fa-solid fa-angle-left"></i>
                                                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap z-50">Angle (3 Pts)</span>
                                            </button>

                                            <button @click="setMeasureMode('radius')" :class="iges.measure.mode === 'radius' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'" class="p-2 rounded text-xs transition relative group" title="Radius (3 Points)">
                                                <i class="fa-regular fa-circle"></i>
                                                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap z-50">Radius (3 Pts)</span>
                                            </button>

                                            <!-- NEW: FACE AREA MODE -->
                                            <button @click="setMeasureMode('face')" :class="iges.measure.mode === 'face' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'" class="p-2 rounded text-xs transition relative group" title="Face Area">
                                                <i class="fa-solid fa-vector-square"></i>
                                                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap z-50">Face Area</span>
                                            </button>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-0.5">
                                            <button @click="iges.measure.snap.enabled = !iges.measure.snap.enabled"
                                                class="p-2 rounded text-xs transition relative group"
                                                :class="iges.measure.snap.enabled ? 'text-green-600 hover:bg-green-50 dark:text-green-400' : 'text-gray-400 hover:bg-gray-100'"
                                                title="Toggle Snap">
                                                <i class="fa-solid fa-magnet"></i>
                                            </button>

                                            <button @click="clearMeasurements()" class="p-2 rounded text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition relative group" title="Clear All">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>

                                            <button @click="toggleMeasure()" class="p-2 rounded text-xs text-gray-500 hover:bg-gray-100 transition relative group" title="Close Measure Tool">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>

                                        <!-- Dynamic Instruction -->
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900/80 backdrop-blur text-white text-[10px] px-3 py-1.5 rounded-full shadow-sm whitespace-nowrap pointer-events-none transition-all duration-300"
                                            x-text="iges.measure.hoverInstruction"
                                            x-show="iges.measure.enabled">
                                        </div>

                                </div>
                            </div>
                        </div>
                    </template>

                    <template
                        x-if="!isImage(selectedFile?.name) && !isPdf(selectedFile?.name) && !isTiff(selectedFile?.name) && !isCad(selectedFile?.name) && !isHpgl(selectedFile?.name)">
                        <div class="text-center">
                            <i class="fa-solid fa-file text-6xl text-gray-400 dark:text-gray-500"></i>
                            <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Preview Unavailable
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">This file type is not supported for
                                preview.</p>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('style')
<style>
    /* Alpine collapse animation - smooth accordion */
    [x-collapse] {
        overflow: hidden !important;
        transition: height 300ms cubic-bezier(0.4, 0, 0.2, 1) !important;
        will-change: height;
    }

    [x-collapse].x-collapse-transitioning {
        overflow: hidden !important;
    }

    .preview-area {
        @apply bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center;
    }

    [x-cloak] {
        display: none !important;
    }

    .measure-label {
        user-select: none;
        white-space: nowrap;
    }

    /* Smooth transition for side panels */
    .transition-width {
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush

@push('scripts')
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- UTIF.js untuk render TIFF (v2 classic API) -->
<script src="https://unpkg.com/utif@2.0.1/UTIF.js"></script>

<!-- pdf.js 2.x (lebih stabil untuk UMD) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    if (window['pdfjsLib']) {
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    }
</script>

<!-- ES Module shims + Import Map untuk Three.js (module) -->
<script async src="https://unpkg.com/es-module-shims@1.10.0/dist/es-module-shims.js"></script>
<script type="importmap">
    {
    "imports": {
        "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
        "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/",
        "three-mesh-bvh": "https://unpkg.com/three-mesh-bvh@0.7.6/build/index.module.js"
    }
    }</script>

<!-- OCCT: parser STEP/IGES/BREP (WASM) + Three.js loaders for STL, OBJ, FBX, GLTF, GLB, 3DS -->
<script src="https://cdn.jsdelivr.net/npm/occt-import-js@0.0.23/dist/occt-import-js.js"></script>

<script>
    /* ========== Toast Utilities ========== */
    function detectTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        return isDark ? {
            mode: 'dark',
            bg: 'rgba(30, 41, 59, 0.95)',
            fg: '#E5E7EB',
            border: 'rgba(71, 85, 105, 0.5)',
            progress: 'rgba(255,255,255,.9)',
            icon: {
                success: '#22c55e',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            }
        } : {
            mode: 'light',
            bg: 'rgba(255, 255, 255, 0.98)',
            fg: '#0f172a',
            border: 'rgba(226, 232, 240, 1)',
            progress: 'rgba(15,23,42,.8)',
            icon: {
                success: '#16a34a',
                error: '#dc2626',
                warning: '#d97706',
                info: '#2563eb'
            }
        };
    }
    const BaseToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2600,
        timerProgressBar: true,
        showClass: {
            popup: 'swal2-animate-toast-in'
        },
        hideClass: {
            popup: 'swal2-animate-toast-out'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function renderToast({
        icon = 'success',
        title = 'Success',
        text = ''
    } = {}) {
        const t = detectTheme();
        BaseToast.fire({
            icon,
            title,
            text,
            iconColor: t.icon[icon] || t.icon.success,
            background: t.bg,
            color: t.fg,
            customClass: {
                popup: 'swal2-toast border',
                title: '',
                timerProgressBar: ''
            },
            didOpen: (toast) => {
                const bar = toast.querySelector('.swal2-timer-progress-bar');
                if (bar) bar.style.background = t.progress;
                const popup = toast.querySelector('.swal2-popup');
                if (popup) popup.style.borderColor = t.border;
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    function toastSuccess(title = 'Berhasil', text = 'Operasi berhasil dijalankan.') {
        renderToast({
            icon: 'success',
            title,
            text
        });
    }

    function toastError(title = 'Gagal', text = 'Terjadi kesalahan.') {
        BaseToast.update({
            timer: 3400
        });
        renderToast({
            icon: 'error',
            title,
            text
        });
        BaseToast.update({
            timer: 2600
        });
    }

    function toastWarning(title = 'Peringatan', text = 'Periksa kembali data Anda.') {
        renderToast({
            icon: 'warning',
            title,
            text
        });
    }

    function toastInfo(title = 'Informasi', text = '') {
        renderToast({
            icon: 'info',
            title,
            text
        });
    }
    window.toastSuccess = toastSuccess;
    window.toastError = toastError;
    window.toastWarning = toastWarning;
    window.toastInfo = toastInfo;

    /* ========== Alpine Component ========== */
    function exportDetail(config = {}) {
        let pdfDoc = null;
        const loadedRevisionList = JSON.parse(`@json($revisionList ?? [])`);
        const loadedExportId = JSON.parse(`@json($exportId)`);
        if (loadedRevisionList.length > 0) {
            loadedRevisionList[0].is_latest = true;
        }
        const defaultRevisionId = (loadedRevisionList[0] && loadedRevisionList[0].id) ? loadedRevisionList[0].id : loadedExportId;
        return {
            exportId: JSON.parse(`@json($exportId)`),
            pkg: JSON.parse(`@json($detail)`),
            revisionList: loadedRevisionList,
            selectedRevisionId: JSON.parse(`@json($exportId)`),
            isLoadingRevision: false,
            revisionSelect2: null,
            stampFormat: JSON.parse(`@json($stampFormat ?? null)`),
            userDeptCode: config.userDeptCode || null,
            userName: JSON.parse(`@json($userName ?? null)`),
            isEngineering: config.isEngineering || false,
            imgLoading: false,
            activeLoadId: 0,
            cameraMode: 'perspective',
            partOpacity: 1.0,
            currentStyle: 'shaded-edges',
            cadPartsList: [],
            selectedPartUuid: null,
            isPartListOpen: false,
            isPartListOpen: false,
            isMeasureListOpen: false,
            isMeasureActive: false, // Reactive state for Measure Tool
            clipping: {
                x: { enabled: false, value: 0, flipped: false, plane: null, helper: null, showHelper: true, showCap: false },
                y: { enabled: false, value: 0, flipped: false, plane: null, helper: null, showHelper: true, showCap: false },
                z: { enabled: false, value: 0, flipped: false, plane: null, helper: null, showHelper: true, showCap: false },
                min: -100,
                max: 100,
                step: 1, // Step size for increment/decrement
                panelOpen: false, // For UI toggle
                animation: {
                    playing: false,
                    axis: null, // 'x', 'y', or 'z'
                    speed: 1, // Units per frame
                    direction: 1 // 1 for forward, -1 for reverse
                }
            },

            explode: {
                enabled: false,
                value: 0,
                panelOpen: false
            },
            autoRotate: false,
            snapMarker: null,
            headlight: {
                enabled: false,
                object: null
            },
            isFullscreen: false,
            is2DFullscreen: false,
            partInfo: {
                volume: '-',
                area: '-'
            },
            activeMaterial: 'default',
            currentStyle: 'shaded', // Display style: 'shaded', 'shaded-edges', 'wireframe'


            // ==== KONFIGURASI POSISI STAMP ====
            stampDefaults: {
                original: 'bottom-left',
                copy: 'bottom-center',
                obsolete: 'bottom-right',
            },
            stampPerFile: {}, // { [fileKey]: { original, copy, obsolete } }
            stampConfig: {
                original: 'bottom-left',
                copy: 'bottom-center',
                obsolete: 'bottom-right',
            },

            selectedFile: null,
            openSections: [],

            formatRevisionForSelect2(revision) {
                if (!revision.id) {
                    return revision.text;
                }
                let badge = '';
                if (revision.is_obsolete) {
                    badge = '<span style="background-color: #FECACA; color: #DC2626; font-size: 0.75em; font-weight: 600; margin-left: 8px; padding: 2px 6px; border-radius: 99px;">OBSOLETE</span>';
                }
                return $(`<span>${revision.text}</span>`).append(badge);
            },

            // mapping dari integer DB -> key string (0-5) - HARUS SAMA DENGAN APPROVAL DETAIL
            positionIntToKey(pos) {
                switch (Number(pos)) {
                    case 0:
                        return 'bottom-left';
                    case 1:
                        return 'bottom-center';
                    case 2:
                        return 'bottom-right';
                    case 3:
                        return 'top-left';
                    case 4:
                        return 'top-center';
                    case 5:
                        return 'top-right';
                    default:
                        return 'bottom-left'; // Default to bottom-left for original stamp
                }
            },

            // Ambil posisi stamp dari file dan set stampConfig
            loadStampConfigFor(file) {
                const key = this.getFileKey(file);
                if (!key) {
                    this.stampConfig = {
                        ...this.stampDefaults
                    };
                    return;
                }

                if (!this.stampPerFile[key]) {
                    this.stampPerFile[key] = {
                        original: this.positionIntToKey(file.ori_position ?? 0),   // 0 = bottom-left
                        copy: this.positionIntToKey(file.copy_position ?? 1),       // 1 = bottom-center
                        obsolete: this.positionIntToKey(file.obslt_position ?? 2),  // 2 = bottom-right
                    };
                }

                this.stampConfig = this.stampPerFile[key];
            },

            saveStampConfigForCurrent() {},

            stampPositionClass(which = 'original') {
                // Use stampConfig if it has a valid (non-null) value, otherwise use stampDefaults
                const configVal = this.stampConfig && this.stampConfig[which];
                const pos = configVal || this.stampDefaults[which] || 'bottom-left';

                switch (pos) {
                    case 'top-left':
                        return 'top-4 left-4';
                    case 'top-center':
                        return 'top-4 left-1/2 -translate-x-1/2';
                    case 'top-right':
                        return 'top-4 right-4';
                    case 'bottom-left':
                        return 'bottom-4 left-4';
                    case 'bottom-center':
                        return 'bottom-4 left-1/2 -translate-x-1/2';
                    case 'bottom-right':
                        return 'bottom-4 right-4';
                    default:
                        // Fallback based on stamp type
                        if (which === 'original') return 'bottom-4 left-4';
                        if (which === 'copy') return 'bottom-4 left-1/2 -translate-x-1/2';
                        if (which === 'obsolete') return 'bottom-4 right-4';
                        return 'bottom-4 left-4';
                }
            },

            stampOriginClass(which = 'original') {
                const configVal = this.stampConfig && this.stampConfig[which];
                const pos = configVal || this.stampDefaults[which] || 'bottom-left';

                switch (pos) {
                    case 'top-left':
                        return 'origin-top-left';
                    case 'top-center':
                        return 'origin-top';
                    case 'top-right':
                        return 'origin-top-right';
                    case 'bottom-left':
                        return 'origin-bottom-left';
                    case 'bottom-center':
                        return 'origin-bottom';
                    case 'bottom-right':
                        return 'origin-bottom-right';
                    default:
                        // Fallback based on stamp type
                        if (which === 'original') return 'origin-bottom-left';
                        if (which === 'copy') return 'origin-bottom';
                        if (which === 'obsolete') return 'origin-bottom-right';
                        return 'origin-bottom-left';
                }
            }, // ZOOM + PAN untuk image / TIFF / HPGL
            imageZoom: 1,
            minZoom: 0.5,
            maxZoom: 5,
            zoomStep: 0.25,
            panX: 0,
            panY: 0,
            isPanning: false,
            panStartX: 0,
            panStartY: 0,
            panOriginX: 0,
            panOriginY: 0,

            zoomIn() {
                this.imageZoom = Math.min(this.imageZoom + this.zoomStep, this.maxZoom);
            },
            zoomOut() {
                this.imageZoom = Math.max(this.imageZoom - this.zoomStep, this.minZoom);
            },
            resetZoom() {
                this.imageZoom = 1;
                this.panX = 0;
                this.panY = 0;
            },
            onWheelZoom(e) {
                const delta = e.deltaY;
                const step = this.zoomStep;

                if (delta < 0) {
                    this.imageZoom = Math.min(this.imageZoom + step, this.maxZoom);
                } else if (delta > 0) {
                    this.imageZoom = Math.max(this.imageZoom - step, this.minZoom);
                }
            },
            startPan(e) {
                this.isPanning = true;
                this.panStartX = e.clientX;
                this.panStartY = e.clientY;
                this.panOriginX = this.panX;
                this.panOriginY = this.panY;
            },
            onPan(e) {
                if (!this.isPanning) return;
                const dx = e.clientX - this.panStartX;
                const dy = e.clientY - this.panStartY;
                this.panX = this.panOriginX + dx;
                this.panY = this.panOriginY + dy;
            },
            endPan() {
                this.isPanning = false;
            },
            imageTransformStyle() {
                return `transform: translate(${this.panX}px, ${this.panY}px) scale(${this.imageZoom}); transform-origin: center center;`;
            },

            // Stamp functions
            formatStampDate(dateString) {
                if (!dateString) return '';
                const d = new Date(dateString);
                if (isNaN(d.getTime())) return dateString;

                const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                const monthName = months[d.getMonth()];
                const day = d.getDate();
                const year = d.getFullYear();

                const j = day % 10,
                    k = day % 100;

                let suffix = "ᵗʰ";
                if (j == 1 && k != 11) {
                    suffix = "ˢᵗ";
                } else if (j == 2 && k != 12) {
                    suffix = "ⁿᵈ";
                } else if (j == 3 && k != 13) {
                    suffix = "ʳᵈ";
                }
                return `${monthName}.${day}${suffix} ${year}`;
            },

            // teks tengah stamp ORIGINAL
            stampCenterOriginal() {
                return 'SAI-DRAWING ORIGINAL';
            },

            // teks tengah stamp Control Copy
            stampCenterCopy() {
                return 'SAI-DRAWING CONTROLLED COPY';
            },

            // teks tengah stamp OBSOLETE
            stampCenterObsolete() {
                return 'SAI-DRAWING OBSOLETE';
            },

            getNormalFormat() {
                const list = this.stampFormat || [];
                if (Array.isArray(list) && list.length > 0) {
                    return list[0];
                }
                return {
                    prefix: 'Date Received',
                    suffix: 'Date Uploaded'
                };
            },

            // getObsoleteFormat() {
            //     const list = this.stampFormat || [];
            //     if (Array.isArray(list) && list.length > 1) {
            //         return list[1];
            //     }
            //     return { prefix: 'DATE UPLOAD', suffix: 'Dept' };
            // },

            getObsoleteInfo() {
                return this.pkg?.stamp?.obsolete_info || {};
            },

            obsoleteName() {
                const s = this.pkg?.stamp || {};
                const info = s.obsolete_info || {};
                return info.name || '';
            },

            obsoleteDept() {
                const s = this.pkg?.stamp || {};
                const info = s.obsolete_info || {};
                return info.dept || '';
            },

            stampTopLine(which = 'original') {
                const s = this.pkg?.stamp || {};
                let date;
                let fmt;

                if (which === 'obsolete') {
                    const info = this.getObsoleteInfo();
                    date = info.date_text || s.obsolete_date || s.upload_date || '';
                    return date ? `Date : ${date}` : '';
                } else if (which === 'original') {
                    fmt = this.getNormalFormat();
                    date = s.receipt_date || s.upload_date || '';
                    const label = fmt.prefix || 'Date Received';
                    return date ? `${label} : ${this.formatStampDate(date)}` : '';
                } else if (which === 'copy') {
                    const now = new Date();
                    const dateStr = this.formatStampDate(now.toISOString().split('T')[0]);

                    // Format Jam (HH:MM:SS)
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    const timeStr = `${hours}:${minutes}:${seconds}`;

                    const deptCode = this.userDeptCode || '--';

                    return `SAI / ${deptCode} / ${dateStr} ${timeStr}`;

                } else {
                    fmt = this.getNormalFormat();
                    date = s.receipt_date || s.upload_date || '';
                    const label = fmt.prefix || 'Date Received';
                    return date ? `${label} : ${this.formatStampDate(date)}` : '';
                }
            },

            stampBottomLine(which = 'original') {
                const s = this.pkg?.stamp || {};
                let fmt;

                if (which === 'copy') {
                    const userName = this.userName || '--';
                    return `Downloaded By ${userName}`;
                } else if (which === 'obsolete') {
                    fmt = this.getObsoleteFormat();
                    const info = this.getObsoleteInfo();
                    const name = info.name || '';
                    const dept = info.dept || '';
                    let value = '';
                    if (name && dept) {
                        value = `${name} / ${dept}`;
                    } else {
                        value = name || dept || '';
                    }
                    const label = fmt.suffix || 'BY';
                    return value ? `${label} : ${value}` : '';
                } else {
                    fmt = this.getNormalFormat();
                    const date = s.upload_date || '';
                    const label = fmt.suffix || 'DATE UPLOADED';
                    return date ? `${label} : ${this.formatStampDate(date)}` : '';
                }
            },

            // Helper functions for file size display
            formatBytes(bytes) {
                if (!bytes || bytes <= 0) return '-';
                const units = ['B', 'KB', 'MB', 'GB', 'TB'];
                let i = 0;
                let value = bytes;
                while (value >= 1024 && i < units.length - 1) {
                    value /= 1024;
                    i++;
                }
                const fixed = value >= 10 || i === 0 ? value.toFixed(0) : value.toFixed(1);
                return `${fixed} ${units[i]}`;
            },

            fileSizeInfo() {
                if (!this.selectedFile) return '';
                const bytes = this.selectedFile.size ?? this.selectedFile.filesize ?? 0;
                if (!bytes) return 'Size: -';
                return 'Size: ' + this.formatBytes(bytes);
            },

            // PDF page navigation functions
            nextPdfPage() {
                if (this.pdfPageNum < this.pdfNumPages) {
                    this.pdfPageNum++;
                    this.renderPdfPage();
                }
            },

            prevPdfPage() {
                if (this.pdfPageNum > 1) {
                    this.pdfPageNum--;
                    this.renderPdfPage();
                }
            },

            getTotalPackageStats() {
                if (!this.pkg || !this.pkg.files) return { size: 0, count: 0 };
                
                let totalSize = 0;
                let totalCount = 0;
                
                ['2d', '3d', 'ecn'].forEach(cat => {
                    const files = this.pkg.files[cat] || [];
                    totalCount += files.length;
                    files.forEach(f => {
                         // Asumsikan 'size' dalam bytes jika ada, atau 0
                        totalSize += Number(f.size || 0);
                    });
                });
                return { size: totalSize, count: totalCount };
            },

            // ===== helper posisi stamp per file =====
            getFileKey(file) {
                return (file?.id ?? file?.name ?? '').toString();
            },

            // TIFF state
            tifLoading: false,
            tifError: '',
            tifPageNum: 1,
            tifNumPages: 1,
            tifIfds: [],
            tifDecoder: null,


            // HPGL state
            hpglLoading: false,
            hpglError: '',
            // HPGL drawing bounds in CSS pixels (for stamp positioning)
            hpglDrawingBounds: { left: 0, top: 0, width: 0, height: 0 },

            // PDF state
            pdfLoading: false,
            pdfError: '',
            pdfPageNum: 1,
            pdfNumPages: 1,
            pdfScale: 1.0,


            // CAD viewer state
            iges: {
                renderer: null,
                scene: null,
                camera: null,
                controls: null,
                animId: 0,
                loading: false,
                error: '',
                rootModel: null,
                THREE: null, // Three.js instance

                // --- MEASURE STATE ---
                measure: {
                    enabled: false,
                    group: null, // THREE.Group for drawings

                    // Interaction Points
                    p1: null,
                    p2: null,
                    p3: null, // For 3-point circle

                    // Current Mode
                    mode: 'point', // 'point', 'edge', 'radius', 'angle', 'face'

                    // Snapping Data
                    snap: {
                        enabled: true,
                        type: null,
                        point: null,
                        normal: null,
                        edge: null
                    },

                    // Measurement Storage
                    results: [], // Array of { id, type, value, ... }

                    // UI State
                    hoverInstruction: 'Select Start Point',
                }
            },
            _onIgesResize: null,

            /* ===== Helpers jenis file ===== */
            extOf(name) {
                const i = (name || '').lastIndexOf('.');
                return i > -1 ? (name || '').slice(i + 1).toLowerCase() : '';
            },
            isImage(name) {
                return ['png', 'jpg', 'jpeg', 'webp', 'gif', 'bmp'].includes(this.extOf(name));
            },
            isPdf(name) {
                return this.extOf(name) === 'pdf';
            },
            isTiff(name) {
                return ['tif', 'tiff'].includes(this.extOf(name));
            },
            isHpgl(name) {
                return ['plt', 'hpgl', 'hpg', 'prn'].includes(this.extOf(name));
            },
            isCad(name) {
                // OCCT formats: igs, iges, stp, step, brep
                // Three.js formats: stl, obj, fbx, gltf, glb, 3ds
                return ['igs', 'iges', 'stp', 'step', 'brep', 'stl', 'obj', 'fbx', 'gltf', 'glb', '3ds'].includes(this.extOf(name));
            },
            // Helper to determine if format uses OCCT loader
            isOcctFormat(name) {
                return ['igs', 'iges', 'stp', 'step', 'brep'].includes(this.extOf(name));
            },
            // Helper to determine if format uses Three.js loader
            isThreeFormat(name) {
                return ['stl', 'obj', 'fbx', 'gltf', 'glb', '3ds'].includes(this.extOf(name));
            },
            // pdfSrc(u) { return u; },

            findFileByNameInsensitive(name) {
                if (!name) return null;
                const target = name.toLowerCase();
                const groups = this.pkg.files || {};

                for (const key of Object.keys(groups)) {
                    const list = groups[key] || [];
                    for (const f of list) {
                        const n = (f.name || '').toLowerCase();
                        if (n === target || n.endsWith('/' + target) || n.endsWith('\\' + target)) {
                            return f;
                        }
                    }
                }
                return null;
            },

            _findIgesSibling(mainFile) {
                if (!mainFile) return null;
                const name = mainFile.name || '';
                const base = name.replace(/\.(stp|step)$/i, '');

                const candidates = [];
                if (base) {
                    candidates.push(base + '.igs', base + '.iges');
                }
                candidates.push('temp.igs', 'temp.iges');

                for (const cand of candidates) {
                    const f = this.findFileByNameInsensitive(cand);
                    if (f) return f;
                }

                const groups = this.pkg.files || {};
                for (const key of Object.keys(groups)) {
                    const list = groups[key] || [];
                    const hit = list.find(f => /\.(igs|iges)$/i.test(f.name || ''));
                    if (hit) return hit;
                }

                return null;
            },


            /* ===== TIFF renderer (multi-page) ===== */
            async renderTiff(url) {
                if (!url || typeof window.UTIF === 'undefined') return;

                this.tifLoading = true;
                this.tifError = '';
                this.tifIfds = [];
                this.tifDecoder = null;
                this.tifPageNum = 1;
                this.tifNumPages = 1;

                try {
                    const resp = await fetch(url, {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error('Failed to fetch TIFF file');
                    const buf = await resp.arrayBuffer();

                    const U =
                        (window.UTIF && typeof window.UTIF.decode === 'function') ? window.UTIF :
                        (window.UTIF && window.UTIF.UTIF && typeof window.UTIF.UTIF.decode === 'function') ? window.UTIF.UTIF :
                        null;

                    if (!U) throw new Error('UTIF library is not compatible (decode() not found)');

                    const ifds = U.decode(buf);
                    if (!ifds || !ifds.length) throw new Error('TIFF file does not contain any frame');

                    // decode semua frame
                    if (typeof U.decodeImages === 'function') {
                        U.decodeImages(buf, ifds);
                    } else if (typeof U.decodeImage === 'function') {
                        ifds.forEach(ifd => U.decodeImage(buf, ifd));
                    }

                    // simpan ke state untuk multi-page
                    this.tifDecoder = U;
                    this.tifIfds = ifds;
                    this.tifNumPages = ifds.length;
                    this.tifPageNum = 1;

                    await this.renderTiffPage();
                } catch (e) {
                    console.error(e);
                    this.tifError = e?.message || 'Failed to render TIFF';
                } finally {
                    this.tifLoading = false;
                }
            },

            async renderTiffPage() {
                if (!this.tifDecoder || !this.tifIfds || !this.tifIfds.length) return;

                const pageIndex = this.tifPageNum - 1;
                const ifd = this.tifIfds[pageIndex];
                if (!ifd) return;

                try {
                    const U = this.tifDecoder;
                    const rgba = U.toRGBA8(ifd);
                    const w = ifd.width;
                    const h = ifd.height;

                    const off = document.createElement('canvas');
                    const ctx = off.getContext('2d');
                    off.width = w;
                    off.height = h;

                    const imgData = ctx.createImageData(w, h);
                    imgData.data.set(rgba);
                    ctx.putImageData(imgData, 0, 0);

                    const dataUrl = off.toDataURL('image/png');

                    await this.$nextTick();
                    const img = this.$refs.tifImg;
                    if (img) img.src = dataUrl;
                } catch (e) {
                    console.error(e);
                    this.tifError = e?.message || 'Failed to render TIFF page';
                }
            },

            nextTifPage() {
                if (this.tifPageNum >= this.tifNumPages) return;
                this.tifPageNum++;
                this.renderTiffPage();
            },

            prevTifPage() {
                if (this.tifPageNum <= 1) return;
                this.tifPageNum--;
                this.renderTiffPage();
            },



            /* ===== PDF renderer (pdf.js) ===== */
            async renderPdf(url) {
                if (!url || !window['pdfjsLib']) return;

                this.pdfLoading = true;
                this.pdfError = '';
                pdfDoc = null;
                this.pdfPageNum = 1;
                this.pdfScale = 1.0;

                try {
                    await this.$nextTick();
                    const canvas = this.$refs.pdfCanvas;
                    if (!canvas) throw new Error('PDF canvas not found');

                    const loadingTask = window.pdfjsLib.getDocument(url);

                    const pdf = await loadingTask.promise;
                    pdfDoc = pdf;
                    this.pdfNumPages = pdf.numPages;

                    await this.renderPdfPage();
                } catch (e) {
                    console.error(e);
                    this.pdfError = e?.message || 'Failed to render PDF';
                } finally {
                    this.pdfLoading = false;
                }
            },

            async renderPdfPage() {
                if (!pdfDoc) return;
                try {
                    const page = await pdfDoc.getPage(this.pdfPageNum);
                    const viewport = page.getViewport({
                        scale: this.pdfScale
                    });

                    await this.$nextTick();
                    const canvas = this.$refs.pdfCanvas;
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: ctx,
                        viewport
                    };
                    await page.render(renderContext).promise;
                } catch (e) {
                    console.error(e);
                    this.pdfError = e?.message || 'Failed to render PDF page';
                }
            },

            /* ===== HPGL renderer ===== */
            async renderHpgl(url) {
                if (!url) return;

                this.hpglLoading = true;
                this.hpglError = '';

                try {
                    const resp = await fetch(url, {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error('Failed to fetch HPGL file');
                    const text = await resp.text();

                    // Standardize separators: replace newlines with ';', then split by ';'
                    let commands = text.replace(/[\r\n]+/g, ';').split(';');

                    // Many HPGL files concatenate commands without semicolons
                    // Use iterative parsing to avoid stack overflow on very long strings
                    const expandedCommands = [];
                    for (const cmd of commands) {
                        if (!cmd || !cmd.trim()) continue;

                        // For very long commands, split manually
                        if (cmd.length > 10000) {
                            let i = 0;
                            while (i < cmd.length) {
                                // Find next opcode (2 uppercase letters)
                                if (i + 1 < cmd.length && /[A-Z]/.test(cmd[i]) && /[A-Z]/.test(cmd[i+1])) {
                                    const opcode = cmd.substring(i, i+2);
                                    i += 2;

                                    // Collect arguments until next opcode or end
                                    let args = '';
                                    while (i < cmd.length && !(/[A-Z]/.test(cmd[i]) && i+1 < cmd.length && /[A-Z]/.test(cmd[i+1]))) {
                                        args += cmd[i];
                                        i++;
                                    }

                                    expandedCommands.push(opcode + args);
                                } else {
                                    i++;
                                }
                            }
                        } else {
                            // For shorter commands, use regex (faster)
                            const parts = cmd.match(/[A-Z]{2}[^A-Z]*/g);
                            if (parts && parts.length > 1) {
                                expandedCommands.push(...parts);
                            } else {
                                expandedCommands.push(cmd);
                            }
                        }
                    }

                    commands = expandedCommands;

                    let penDown = false;
                    let isRelative = false;
                    let x = 0, y = 0;
                    const segments = [];

                    // Parse coordinates
                    const parseCoords = (str) => {
                        if (!str || !str.trim()) return [];
                        return str.replace(/,/g, ' ').trim().split(/\s+/).map(Number).filter(v => !isNaN(v));
                    };

                    const addSegment = (x1, y1, x2, y2) => {
                        segments.push({ x1, y1, x2, y2 });
                    };

                    // Helper to approximate arc/circle with line segments
                    const addArc = (cx, cy, radius, startAngle, endAngle, steps = 32) => {
                        const angleStep = (endAngle - startAngle) / steps;
                        let prevX = cx + radius * Math.cos(startAngle * Math.PI / 180);
                        let prevY = cy + radius * Math.sin(startAngle * Math.PI / 180);

                        for (let i = 1; i <= steps; i++) {
                            const angle = startAngle + angleStep * i;
                            const nx = cx + radius * Math.cos(angle * Math.PI / 180);
                            const ny = cy + radius * Math.sin(angle * Math.PI / 180);
                            addSegment(prevX, prevY, nx, ny);
                            prevX = nx;
                            prevY = ny;
                        }
                    };

                    for (const raw of commands) {
                        if (!raw || !raw.trim()) continue;

                        const cmd = raw.trim().toUpperCase();
                        const op = cmd.slice(0, 2);
                        const argsStr = cmd.slice(2);
                        const coords = parseCoords(argsStr);

                        const processMove = () => {
                            for (let i = 0; i < coords.length; i += 2) {
                                if (i + 1 >= coords.length) break;
                                let nx = coords[i];
                                let ny = coords[i+1];

                                if (isRelative) {
                                    nx = x + nx;
                                    ny = y + ny;
                                }

                                if (penDown) {
                                    addSegment(x, y, nx, ny);
                                }

                                x = nx;
                                y = ny;
                            }
                        };

                        if (op === 'IN') {
                            penDown = false;
                            isRelative = false;
                            x = 0; y = 0;
                        } else if (op === 'SP') {
                            // Select Pen - ignore
                        } else if (op === 'PU') {
                            penDown = false;
                            if (coords.length > 0) processMove();
                        } else if (op === 'PD') {
                            penDown = true;
                            if (coords.length > 0) processMove();
                        } else if (op === 'PA') {
                            isRelative = false;
                            if (coords.length > 0) processMove();
                        } else if (op === 'PR') {
                            isRelative = true;
                            if (coords.length > 0) processMove();
                        } else if (op === 'CI') {
                            // Circle: CI radius[,chord_angle]
                            if (coords.length >= 1) {
                                const radius = Math.abs(coords[0]);
                                addArc(x, y, radius, 0, 360, 64);
                            }
                        } else if (op === 'AA') {
                            // Arc Absolute: AA cx,cy,angle[,chord_angle]
                            if (coords.length >= 3) {
                                const cx = coords[0];
                                const cy = coords[1];
                                const sweepAngle = coords[2];
                                const radius = Math.sqrt((x - cx) ** 2 + (y - cy) ** 2);
                                const startAngle = Math.atan2(y - cy, x - cx) * 180 / Math.PI;
                                const endAngle = startAngle + sweepAngle;

                                addArc(cx, cy, radius, startAngle, endAngle, Math.max(16, Math.abs(sweepAngle) / 5));

                                // Update position to end of arc
                                x = cx + radius * Math.cos(endAngle * Math.PI / 180);
                                y = cy + radius * Math.sin(endAngle * Math.PI / 180);
                            }
                        } else if (op === 'AR') {
                            // Arc Relative: AR dx,dy,angle[,chord_angle]
                            if (coords.length >= 3) {
                                const cx = x + coords[0];
                                const cy = y + coords[1];
                                const sweepAngle = coords[2];
                                const radius = Math.sqrt(coords[0] ** 2 + coords[1] ** 2);
                                const startAngle = Math.atan2(-coords[1], -coords[0]) * 180 / Math.PI;
                                const endAngle = startAngle + sweepAngle;

                                addArc(cx, cy, radius, startAngle, endAngle, Math.max(16, Math.abs(sweepAngle) / 5));

                                x = cx + radius * Math.cos(endAngle * Math.PI / 180);
                                y = cy + radius * Math.sin(endAngle * Math.PI / 180);
                            }
                        } else if (op === 'LT') {
                            // Line Type - ignore
                        } else if (op === 'PG') {
                            // Page Feed - ignore
                        }
                    }

                    if (!segments.length) {
                        throw new Error('No drawable content found in HPGL file');
                    }

                    // Calculate bounds ONLY from drawn segments to ensure tight fit
                    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                    for (const s of segments) {
                        if (s.x1 < minX) minX = s.x1;
                        if (s.x2 < minX) minX = s.x2;
                        if (s.x1 > maxX) maxX = s.x1;
                        if (s.x2 > maxX) maxX = s.x2;

                        if (s.y1 < minY) minY = s.y1;
                        if (s.y2 < minY) minY = s.y2;
                        if (s.y1 > maxY) maxY = s.y1;
                        if (s.y2 > maxY) maxY = s.y2;
                    }

                    // Reset Render State
                    this.hpglError = '';
                    this.imageZoom = 1;
                    this.panX = 0;
                    this.panY = 0;

                    await this.$nextTick();
                    await new Promise(r => setTimeout(r, 50));

                    const canvas = this.$refs.hpglCanvas;
                    if (!canvas) throw new Error('HPGL canvas not found');

                    // Find the correct viewport container
                    let container = canvas.parentElement;
                    while (container && (container.clientWidth < 400 || container.clientHeight < 300)) {
                        container = container.parentElement;
                        if (!container || container === document.body) break;
                    }

                    // Use the found container, or fallback to reasonable defaults
                    const viewW = Math.max(container?.clientWidth || window.innerWidth * 0.6, 800);
                    const viewH = Math.max(container?.clientHeight || window.innerHeight * 0.7, 500);

                    // Setup Canvas with High Resolution for Zooming
                    const dpr = window.devicePixelRatio || 1;
                    const zoomCapability = 5; // Support up to 5x zoom without blur
                    const totalScale = dpr * zoomCapability;

                    canvas.width = viewW * totalScale;
                    canvas.height = viewH * totalScale;
                    canvas.style.width = viewW + 'px';
                    canvas.style.height = viewH + 'px';

                    const ctx = canvas.getContext('2d');

                    // Reset and configure context
                    ctx.setTransform(1, 0, 0, 1, 0, 0);
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    // Scale drawing context
                    ctx.scale(totalScale, totalScale);

                    // Line thickness configuration
                    ctx.lineWidth = 0.2;
                    ctx.lineCap = 'butt';
                    ctx.lineJoin = 'miter';
                    ctx.strokeStyle = '#000';

                    const dx = maxX - minX || 1;
                    const dy = maxY - minY || 1;

                    // Calculate scale to fit viewport (98% fit)
                    const scale = 0.98 * Math.min(viewW / dx, viewH / dy);

                    // Center the drawing
                    const transX = viewW / 2 - (minX + dx / 2) * scale;
                    const transY = viewH / 2 + (minY + dy / 2) * scale;

                    ctx.beginPath();
                    // Render segments
                    for (const s of segments) {
                        const sx = s.x1 * scale + transX;
                        const sy = -s.y1 * scale + transY;
                        const ex = s.x2 * scale + transX;
                        const ey = -s.y2 * scale + transY;

                        ctx.moveTo(sx, sy);
                        ctx.lineTo(ex, ey);
                    }
                    ctx.stroke();

                    // Calculate actual drawing bounds in CSS pixels for stamp positioning
                    const drawingLeft = minX * scale + transX;
                    const drawingTop = -maxY * scale + transY;
                    const drawingWidth = dx * scale;
                    const drawingHeight = dy * scale;

                    this.hpglDrawingBounds = {
                        left: drawingLeft,
                        top: drawingTop,
                        width: drawingWidth,
                        height: drawingHeight
                    };

                    this.viewportVersion++;
                } catch (e) {
                    console.error(e);
                    this.hpglError = e?.message || 'Failed to render HPGL';
                } finally {
                    this.hpglLoading = false;
                }
            },

            /* ===== OCCT result -> THREE meshes ===== */
            _buildThreeFromOcct(result, THREE) {
                const group = new THREE.Group();
                const meshes = result.meshes || [];

                // 1. Bersihkan list lama agar tidak duplikat saat reload
                this.cadPartsList = [];

                for (let i = 0; i < meshes.length; i++) {
                    const m = meshes[i];

                    // ... (Kode pembuatan geometry g & attributes tetap sama) ...
                    const g = new THREE.BufferGeometry();
                    g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
                    if (m.attributes.normal?.array) g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
                    if (m.index?.array) g.setIndex(m.index.array);

                    // Pastikan BVH (dari langkah sebelumnya) tetap ada
                    if (g.attributes.position.count > 0) g.computeBoundsTree();

                    // ... (Kode warna color tetap sama) ...
                    let color = 0xcccccc;
                    if (m.color && m.color.length === 3) color = (m.color[0] << 16) | (m.color[1] << 8) | (m.color[2]);
                    const mat = new THREE.MeshStandardMaterial({
                        color,
                        metalness: 0,
                        roughness: 1,
                        side: THREE.DoubleSide
                    });

                    const mesh = new THREE.Mesh(g, mat);

                    // 2. Beri nama yang jelas. Jika kosong, pakai "Part X"
                    mesh.name = m.name || `Part ${i + 1}`;

                    group.add(mesh);

                    // 3. Masukkan ke dalam Alpine State (Daftar Part)
                    this.cadPartsList.push({
                        uuid: mesh.uuid, // ID unik dari Three.js
                        name: mesh.name
                    });
                }
                return group;
            },


            /* ===== Cleanup CAD ===== */
            disposeCad() {
                // Reset Tool States to ensure clean slate for next file
                this.cameraMode = 'perspective';
                this.autoRotate = false;
                this.activeMaterial = 'default';
                this.selectedPartUuid = null;
                this.partOpacity = 1.0;

                if (this.headlight) {
                    this.headlight.enabled = false;
                    this.headlight.object = null;
                }

                if (this.explode) {
                    this.explode.enabled = false;
                    this.explode.value = 0;
                }

                if (this.clipping) {
                    this.clipping.enabled = false;
                    this.clipping.panelOpen = false;
                    ['x', 'y', 'z'].forEach(axis => {
                        if (this.clipping[axis]) {
                            this.clipping[axis].enabled = false;
                            this.clipping[axis].value = 0;
                            this.clipping[axis].flipped = false;
                            this.clipping[axis].showHelper = false;
                            this.clipping[axis].helper = null;
                            this.clipping[axis].plane = null;
                        }
                    });
                }
                
                // Reset Interaction Flags
                this._planeHelperDragInitialized = false;

                try {
                    cancelAnimationFrame(this.iges.animId || 0);
                    if (this._onIgesResize) window.removeEventListener('resize', this._onIgesResize);
                    if (this._resizeObserver) {
                        this._resizeObserver.disconnect();
                        this._resizeObserver = null;
                    }
                    const {
                        renderer,
                        scene,
                        controls
                    } = this.iges || {};
                    controls?.dispose?.();
                    scene?.traverse?.(o => {
                        o.geometry?.dispose?.();
                        if (o.material) {
                            const m = o.material;
                            Array.isArray(m) ? m.forEach(mm => mm.dispose?.()) : m.dispose?.();
                        }
                    });
                    renderer?.dispose?.();
                    const wrap = this.$refs.igesWrap;
                    if (wrap)
                        while (wrap.firstChild) wrap.removeChild(wrap.firstChild);
                } catch {}
                this.iges = {
                    renderer: null,
                    scene: null,
                    camera: null,
                    controls: null,
                    animId: 0,
                    loading: false,
                    error: '',
                    rootModel: null,
                    THREE: null,
                    measure: {
                        enabled: false,
                        group: null,
                        p1: null,
                        p2: null,
                        mode: 'point',
                        snap: {
                            type: null,
                            point: null,
                            normal: null,
                            edge: null
                        },
                        results: [],
                        currentResult: null,
                        showPanel: false
                    }
                };
                this._onIgesResize = null;
            },

            /* ===== Meta line formatter ===== */
            metaLine() {
                const m = this.pkg?.metadata || {};
                return [
                        m.customer,
                        m.model,
                        m.part_no,
                        m.doc_type,
                        m.category,
                        m.part_group,
                        m.ecn_no
                    ]
                    .filter(v => v && String(v).trim().length > 0)
                    .join(' - ');
            },

            revisionBadgeText() {
                const m = this.pkg?.metadata || {};
                if (m.revision_label && String(m.revision_label).trim().length > 0) {
                    return `${m.revision} | ${m.revision_label}`;
                }
                return m.revision || '';
            },

            /* ===== Display Styles / Edges ===== */
            _oriMats: new Map(),
            _cacheOriginalMaterials(root, THREE) {
                root.traverse(o => {
                    if (o.isMesh && !this._oriMats.has(o)) {
                        const m = o.material;
                        this._oriMats.set(o, Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone());
                    }
                });
            },
            _restoreMaterials(root) {
                root.traverse(o => {
                    if (!o.isMesh) return;
                    const m = this._oriMats.get(o);
                    if (!m) return;
                    o.material = Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone();
                });
                this._setWireframe(root, false);
                this._toggleEdges(root, false);
                this._setPolygonOffset(root, false);

                if (this.clipping.enabled) {
                    this._updateMaterialsWithClipping();
                }
            },
            _setWireframe(root, on = true) {
                root.traverse(o => {
                    if (!o.isMesh) return;
                    (Array.isArray(o.material) ? o.material : [o.material]).forEach(m => m.wireframe = on);
                });
            },
            _setPolygonOffset(root, on = true, factor = 1, units = 1) {
                root.traverse(o => {
                    if (!o.isMesh) return;
                    (Array.isArray(o.material) ? o.material : [o.material]).forEach(m => {
                        m.polygonOffset = on;
                        m.polygonOffsetFactor = factor;
                        m.polygonOffsetUnits = units;
                    });
                });
            },
            _addEdges(mesh, THREE, threshold = 30) {
                if (mesh.userData.edges) return mesh.userData.edges;
                const edgesGeo = new THREE.EdgesGeometry(mesh.geometry, threshold);
                const edgesMat = new THREE.LineBasicMaterial({
                    transparent: true,
                    opacity: 0.6,
                    depthTest: false
                });
                const edges = new THREE.LineSegments(edgesGeo, edgesMat);
                edges.renderOrder = 999;
                mesh.add(edges);
                mesh.userData.edges = edges;
                return edges;
            },
            _toggleEdges(root, on = true, color = 0x000000) {
                const THREE = this.iges.THREE;
                root.traverse(o => {
                    if (!o.isMesh) return;
                    if (on) {
                        const e = this._addEdges(o, THREE, 30);
                        e.material.color = new THREE.Color(color);
                    } else if (o.userData.edges) {
                        o.remove(o.userData.edges);
                        o.userData.edges.geometry.dispose();
                        o.userData.edges.material.dispose();
                        o.userData.edges = null;
                    }
                });
            },
            setDisplayStyle(mode) {
                const root = this.iges.rootModel;
                if (!root) return;
                this.currentStyle = mode;
                this._restoreMaterials(root);
                if (mode === 'shaded') {
                    // Normal
                } else if (mode === 'shaded-edges') {
                    this._setPolygonOffset(root, true, 1, 1);
                    this._toggleEdges(root, true, 0x000000);
                } else if (mode === 'wireframe') {
                    // Mode Wireframe Murni
                    root.traverse(o => {
                        if (o.isMesh && o.material) {
                            // Ubah material jadi wireframe
                            const mats = Array.isArray(o.material) ? o.material : [o.material];
                            mats.forEach(m => {
                                m.wireframe = true;
                            });
                        }
                    });
                }

                if (this.clipping.enabled) this._updateMaterialsWithClipping();
            },

            /* ===== Measure (eDrawings-like) ===== */
            setMeasureMode(mode) {
                const M = this.iges.measure;
                M.mode = mode;
                M.p1 = null;
                M.p2 = null;
                M.p3 = null;

                // Update instruction based on mode
                switch(mode) {
                    case 'point': M.hoverInstruction = 'Click 1st Point'; break;
                    case 'edge': M.hoverInstruction = 'Click an Edge or 1st Point'; break;
                    case 'angle': M.hoverInstruction = 'Click 1st Point (Start)'; break;
                    case 'radius': M.hoverInstruction = 'Click 1st Point on Curve'; break;
                    case 'face': M.hoverInstruction = 'Click a Planar Face'; break;
                }
                console.log('Measure mode set to:', mode);
            },

            toggleMeasure() {
                const M = this.iges.measure;
                M.enabled = !M.enabled;
                
                // Sync UI state
                this.isMeasureActive = M.enabled;
                this.isMeasureListOpen = M.enabled;

                if (M.enabled) {
                    if (!M.group) {
                        const THREE = this.iges.THREE;
                        M.group = new THREE.Group();
                        // Ensure text labels always render on top
                        M.group.renderOrder = 999;
                        this.iges.scene.add(M.group);
                    }
                    this._bindMeasureEvents(true);
                    this.setMeasureMode(M.mode); // Initialize instruction
                } else {
                    this._bindMeasureEvents(false);
                    this.snapMarker.visible = false;
                    M.p1 = M.p2 = M.p3 = null;
                }
            },

            clearMeasurements() {
                const M = this.iges.measure;
                const g = M.group;
                if (!g) return;

                // Dispose all children properly
                (g.children || []).slice().forEach(ch => {
                    if (ch.userData?.dispose) ch.userData.dispose();
                    g.remove(ch);
                });

                g.clear();
                M.results = [];
                M.p1 = M.p2 = M.p3 = null;
                M.hoverInstruction = 'Measurements Cleared';
                setTimeout(() => this.setMeasureMode(M.mode), 1500);
            },

            deleteMeasurement(index) {
                const M = this.iges.measure;
                if (index < 0 || index >= M.results.length) return;

                // Remove from scene
                const res = M.results[index];
                if (res.objectUuid && M.group) {
                    const obj = M.group.getObjectByProperty('uuid', res.objectUuid);
                    if (obj) {
                        if (obj.userData?.dispose) obj.userData.dispose();
                        M.group.remove(obj);
                    }
                }

                // Remove from array
                M.results.splice(index, 1);
            },

            // --- GEOMETRY HELPERS ---

            _calculateCircleFrom3Points(p1, p2, p3) {
                const THREE = this.iges.THREE;
                // Create two chords: p1-p2 and p2-p3
                // The center of the circle lies on the intersection of the perpendicular bisectors of these chords.

                // Midpoints
                const m1 = p1.clone().add(p2).multiplyScalar(0.5);
                const m2 = p2.clone().add(p3).multiplyScalar(0.5);

                // Vectors for chords
                const v12 = p2.clone().sub(p1);
                const v23 = p3.clone().sub(p2);

                // Normal of the plane defined by 3 points
                const normal = v12.clone().cross(v23).normalize();

                // Directions of bisectors (perpendicular to chords and lying on the plane)
                const dir1 = v12.clone().cross(normal).normalize();
                const dir2 = v23.clone().cross(normal).normalize();

                // Line 1: m1 + t * dir1
                // Line 2: m2 + u * dir2
                // Intersection: m1 + t * dir1 = m2 + u * dir2
                // t * dir1 - u * dir2 = m2 - m1

                // Solve minimal least squares/intersection
                // Use standard formula for intersection of two lines in 3D (assuming coplanar)
                const vMatch = m2.clone().sub(m1);
                const cross12 = dir1.clone().cross(dir2);
                const denom = cross12.lengthSq();

                if (denom < 1e-6) return null; // Parallel or colinear

                // t = (vMatch x dir2) . (dir1 x dir2) / |dir1 x dir2|^2
                // But since they are coplanar, simpler vector math works

                const t = vMatch.clone().cross(dir2).dot(cross12) / denom;
                const center = m1.clone().add(dir1.multiplyScalar(t));
                const radius = center.distanceTo(p1);

                return { center, radius, normal };
            },

            _calculateFaceArea(mesh, faceIndex) {
                 const THREE = this.iges.THREE;
                 if (!mesh || !mesh.geometry) return 0;

                 const geom = mesh.geometry;
                 const pos = geom.attributes.position;
                 const index = geom.index;
                 const normal = geom.attributes.normal;

                 // Strategy:
                 // 1. Identify the normal of the clicked face.
                 // 2. Flood fill or search for all connected triangles with same/similar normal (planar face).

                 // Get normal of picked face
                 const iA = index.getX(faceIndex * 3);
                 const iB = index.getX(faceIndex * 3 + 1);
                 const iC = index.getX(faceIndex * 3 + 2);

                 const vA = new THREE.Vector3().fromBufferAttribute(pos, iA).applyMatrix4(mesh.matrixWorld);
                 const vB = new THREE.Vector3().fromBufferAttribute(pos, iB).applyMatrix4(mesh.matrixWorld);
                 const vC = new THREE.Vector3().fromBufferAttribute(pos, iC).applyMatrix4(mesh.matrixWorld);

                 const triNormal = new THREE.Vector3().crossVectors(vB.clone().sub(vA), vC.clone().sub(vA)).normalize();


                 let totalArea = 0;
                 const threshold = 0.95; // Cosine similarity - lowered to capture slightly curved faces

                 const p1 = new THREE.Vector3();
                 const p2 = new THREE.Vector3();
                 const p3 = new THREE.Vector3();

                 // Pre-calculate target normal in Local Space to avoid matrix mul for every face
                 const invMat = mesh.matrixWorld.clone().invert();
                 const localTriNormal = triNormal.clone().transformDirection(invMat).normalize();

                 // Respect drawRange (important for merged geometries or optimization)
                 const start = geom.drawRange.start || 0;
                 const drawCount = (geom.drawRange.count !== Infinity && geom.drawRange.count !== undefined)
                                   ? geom.drawRange.count
                                   : index.count;
                 const end = start + drawCount;

                 // Simple approach: iterate all triangles and sum areas of those with similar normals
                 for(let i = start; i < end; i += 3) {
                     const idx1 = index.getX(i);
                     const idx2 = index.getX(i+1);
                     const idx3 = index.getX(i+2);

                     p1.fromBufferAttribute(pos, idx1);
                     p2.fromBufferAttribute(pos, idx2);
                     p3.fromBufferAttribute(pos, idx3);

                     const fn = new THREE.Vector3().crossVectors(p2.clone().sub(p1), p3.clone().sub(p1)).normalize();

                     if (fn.dot(localTriNormal) > threshold) {
                         // Clone and transform points to world for accurate area
                         const wp1 = p1.clone().applyMatrix4(mesh.matrixWorld);
                         const wp2 = p2.clone().applyMatrix4(mesh.matrixWorld);
                         const wp3 = p3.clone().applyMatrix4(mesh.matrixWorld);

                         const edge1 = wp2.clone().sub(wp1);
                         const edge2 = wp3.clone().sub(wp1);
                         const area = edge1.cross(edge2).length() * 0.5;
                         totalArea += area;
                     }
                 }

                 return totalArea;
            },

            toggleCameraMode() {
                const {
                    scene,
                    camera,
                    renderer,
                    controls,
                    THREE
                } = this.iges;
                if (!scene || !camera) return;

                const width = renderer.domElement.clientWidth;
                const height = renderer.domElement.clientHeight;
                const aspect = width / height;

                // Simpan posisi & target lama agar transisi mulus
                const oldPos = camera.position.clone();
                const target = controls.target.clone();
                const direction = new THREE.Vector3().subVectors(oldPos, target);
                const dist = direction.length();

                let newCamera;

                if (this.cameraMode === 'perspective') {
                    // Switch to Orthographic
                    const frustumSize = dist; // Estimasi ukuran frustum berdasarkan jarak
                    newCamera = new THREE.OrthographicCamera(
                        frustumSize * aspect / -2,
                        frustumSize * aspect / 2,
                        frustumSize / 2,
                        frustumSize / -2,
                        0.1,
                        10000
                    );
                    this.cameraMode = 'orthographic';
                } else {
                    // Switch back to Perspective
                    newCamera = new THREE.PerspectiveCamera(50, aspect, 0.1, 10000);
                    this.cameraMode = 'perspective';
                }

                // Set posisi & orientasi kamera baru
                newCamera.position.copy(oldPos);
                newCamera.lookAt(target);
                newCamera.updateProjectionMatrix();

                // Update controls & scene
                if (camera.parent) camera.parent.remove(camera); // Remove old camera safely
                scene.add(newCamera); // CRITICAL: Camera MUST be in scene for attached lights to work

                this.iges.camera = newCamera;

                const sunLight = new THREE.DirectionalLight(0xffffff, 1.2); // Intensity 1.2 biar terang
                sunLight.name = 'CameraMainLight';
                sunLight.position.set(0, 0, 0);
                sunLight.target.position.set(0, 0, -1);
                newCamera.add(sunLight);
                newCamera.add(sunLight.target);

                // 2024-Fix: Re-initialize Headlight (Reset & Recreate)
                // Ini memastikan lampu & target dibuat ulang dengan benar dan menempel ke kamera baru
                if (this.headlight.enabled) {
                    this.headlight.enabled = false; // Reset state
                    this.headlight.object = null;   // Buang object lama
                    this.toggleHeadlight();         // Nyalakan ulang (Fresh Start)
                }
                controls.object = newCamera;
                controls.update();

                // Force render untuk update visual
                this._forceRender();
            },

            highlightPart(uuid) {
                const {
                    rootModel,
                    THREE
                } = this.iges;
                if (!rootModel) return;

                // A. Jika klik part yang sama (Deselect)
                if (this.selectedPartUuid === uuid) {
                    this._restoreMaterials(rootModel); // _restoreMaterials yg baru sudah otomatis handle clipping
                    this.selectedPartUuid = null;
                    return;
                }

                // B. Reset part lain ke kondisi normal
                this._restoreMaterials(rootModel);

                this.selectedPartUuid = uuid;
                this.partOpacity = 1.0;

                const target = this.iges.rootModel.getObjectByProperty('uuid', uuid);
                if (target) {
                    this.calculateGeoProperties(target);
                } else {
                    this.partInfo = {
                        volume: '-',
                        area: '-'
                    };
                }

                // C. Cari part target
                const targetMesh = rootModel.getObjectByProperty('uuid', uuid);
                if (targetMesh && targetMesh.isMesh) {

                    // Simpan material asli jika belum ada
                    if (!this._oriMats.has(targetMesh)) {
                        const m = targetMesh.material;
                        this._oriMats.set(targetMesh, Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone());
                    }

                    // === PERBAIKAN UTAMA DI SINI ===
                    // Siapkan Clipping Plane jika aktif
                    const currentPlanes = (this.clipping.enabled && this.clipping.plane) ? [this.clipping.plane] : [];

                    // Buat material Highlight (Merah) DENGAN data clipping
                    const highlightMat = new THREE.MeshBasicMaterial({
                        color: 0xff0000,
                        opacity: 0.6,
                        transparent: true,
                        depthTest: false,
                        // Langsung masukkan clipping planes di sini saat inisialisasi
                        clippingPlanes: currentPlanes,
                        clipShadows: true
                    });

                    targetMesh.material = highlightMat;
                }
            },

            // Multi-axis clipping functions
            toggleAxisClipping(axis) {
                const axisData = this.clipping[axis];
                axisData.enabled = !axisData.enabled;
                const { THREE } = this.iges;

                if (axisData.enabled) {
                    // Reset value to 0
                    axisData.value = 0;

                    // Auto-enable helper when activating cut
                    axisData.showHelper = true;

                    // Create plane with correct normal vector
                    const normals = {
                        x: new THREE.Vector3(axisData.flipped ? -1 : 1, 0, 0),
                        y: new THREE.Vector3(0, axisData.flipped ? -1 : 1, 0),
                        z: new THREE.Vector3(0, 0, axisData.flipped ? -1 : 1)
                    };

                    axisData.plane = new THREE.Plane(normals[axis], 0);

                    this._createPlaneHelper(axis);
                } else {
                    axisData.plane = null;

                    // Remove plane helper when axis is disabled
                    if (axisData.helper) {
                        this.iges.scene.remove(axisData.helper);
                        axisData.helper = null;
                    }
                }

                this._updateMaterialsWithClipping();
            },

            updateAxisClipping(axis) {
                const axisData = this.clipping[axis];
                if (axisData.plane) {
                    axisData.plane.constant = axisData.flipped ? axisData.value : -axisData.value;
                    this._updatePlaneHelper(axis);
                }
            },

            flipAxis(axis) {
                const axisData = this.clipping[axis];
                axisData.flipped = !axisData.flipped;

                if (axisData.enabled && axisData.plane) {
                    const { THREE } = this.iges;
                    const normals = {
                        x: new THREE.Vector3(axisData.flipped ? -1 : 1, 0, 0),
                        y: new THREE.Vector3(0, axisData.flipped ? -1 : 1, 0),
                        z: new THREE.Vector3(0, 0, axisData.flipped ? -1 : 1)
                    };

                    axisData.plane.normal.copy(normals[axis]);
                    axisData.plane.constant = axisData.flipped ? axisData.value : -axisData.value;
                    this._updateMaterialsWithClipping();

                    if (axisData.helper && axisData.showHelper) {
                        this._createPlaneHelper(axis);
                    }
                }
            },

            resetAllClipping() {
                ['x', 'y', 'z'].forEach(axis => {
                    const axisData = this.clipping[axis];
                    axisData.enabled = false;
                    axisData.value = 0;
                    axisData.flipped = false;
                    axisData.plane = null;
                    if (axisData.helper) {
                        this.iges.scene.remove(axisData.helper);
                        axisData.helper = null;
                    }
                });
                this._updateMaterialsWithClipping();
            },

            incrementAxisValue(axis) {
                const axisData = this.clipping[axis];
                const newValue = Math.min(axisData.value + this.clipping.step, this.clipping.max);
                axisData.value = newValue;
                this.updateAxisClipping(axis);
                this._updatePlaneHelper(axis);
            },

            decrementAxisValue(axis) {
                const axisData = this.clipping[axis];
                const newValue = Math.max(axisData.value - this.clipping.step, this.clipping.min);
                axisData.value = newValue;
                this.updateAxisClipping(axis);
                this._updatePlaneHelper(axis);
            },

            setAxisValueDirect(axis, value) {
                const axisData = this.clipping[axis];
                const numValue = parseFloat(value);

                // Validate and clamp value
                if (isNaN(numValue)) return;

                // Round to 2 decimal places
                const roundedValue = Math.round(numValue * 100) / 100;
                axisData.value = Math.max(this.clipping.min, Math.min(this.clipping.max, roundedValue));

                this.updateAxisClipping(axis);
                this._updatePlaneHelper(axis);
            },

            togglePlaneHelper(axis) {
                const axisData = this.clipping[axis];
                axisData.showHelper = !axisData.showHelper;

                if (axisData.showHelper && !axisData.helper) {
                    this._createPlaneHelper(axis);
                } else if (!axisData.showHelper && axisData.helper) {
                    this.iges.scene.remove(axisData.helper);
                    axisData.helper = null;
                }
            },

            toggleSectionCap(axis) {
                const axisData = this.clipping[axis];
                console.log(`Section cap for ${axis}-axis:`, axisData.showCap);
            },

            _createPlaneHelper(axis) {
                const { THREE, scene, rootModel, renderer, camera, controls } = this.iges;
                if (!THREE || !scene || !rootModel) return;

                const axisData = this.clipping[axis];

                if (axisData.helper) {
                    scene.remove(axisData.helper);
                }

                const box = new THREE.Box3().setFromObject(rootModel);
                const size = new THREE.Vector3();
                box.getSize(size);

                let width, height;
                if (axis === 'x') {
                    // X-plane covers Y-Z plane
                    width = size.z;
                    height = size.y;
                } else if (axis === 'y') {
                    // Y-plane covers X-Z plane
                    width = size.x;
                    height = size.z;
                } else {
                    // Z-plane covers X-Y plane
                    width = size.x;
                    height = size.y;
                }

                // Add margins (10% buffer)
                width *= 1.1;
                height *= 1.1;

                // Color mapping
                const colors = {
                    x: 0xff0000, // Red
                    y: 0x00ff00, // Green
                    z: 0x0000ff  // Blue
                };

                // Create plane geometry
                const planeGeometry = new THREE.PlaneGeometry(width, height, 10, 10);
                const planeMaterial = new THREE.MeshBasicMaterial({
                    color: colors[axis],
                    side: THREE.DoubleSide,
                    transparent: true,
                    opacity: 0.2,
                    wireframe: false,
                    depthTest: true,
                    depthWrite: false
                });

                const planeMesh = new THREE.Mesh(planeGeometry, planeMaterial);

                planeMesh.renderOrder = 999;
                planeMesh.raycast = THREE.Mesh.prototype.raycast;

                const wireframeGeometry = new THREE.EdgesGeometry(planeGeometry);
                const wireframeMaterial = new THREE.LineBasicMaterial({
                    color: colors[axis],
                    opacity: 0.6,
                    transparent: true
                });
                const wireframe = new THREE.LineSegments(wireframeGeometry, wireframeMaterial);
                planeMesh.add(wireframe);

                // Orient plane based on axis
                if (axis === 'x') {
                    planeMesh.rotation.y = Math.PI / 2;
                } else if (axis === 'y') {
                    planeMesh.rotation.x = Math.PI / 2;
                }

                // Position plane
                planeMesh.position.set(
                    axis === 'x' ? axisData.value : 0,
                    axis === 'y' ? axisData.value : 0,
                    axis === 'z' ? axisData.value : 0
                );

                // Make plane interactive for dragging
                planeMesh.userData.axis = axis;
                planeMesh.userData.isDraggable = true;

                axisData.helper = planeMesh;
                scene.add(planeMesh);

                this._setupPlaneHelperDrag(axis);
            },

            _setupPlaneHelperDrag(axis) {
                if (this._planeHelperDragInitialized) return;
                this._planeHelperDragInitialized = true;

                const { renderer, camera, controls } = this.iges;
                if (!renderer || !camera) return;

                const canvas = renderer.domElement;

                // Store drag state globally
                this._planeDragState = {
                    isDragging: false,
                    axis: null,
                    dragPlane: null,
                    offset: 0
                };

                // Mouse down handler
                const onMouseDown = (event) => {
                    // Skip if measure tool is active
                    if (this.iges.measure?.enabled) return;

                    const rect = canvas.getBoundingClientRect();
                    const mouse = new this.iges.THREE.Vector2(
                        ((event.clientX - rect.left) / rect.width) * 2 - 1,
                        -((event.clientY - rect.top) / rect.height) * 2 + 1
                    );

                    const raycaster = new this.iges.THREE.Raycaster();
                    raycaster.setFromCamera(mouse, camera);

                    // Check all active plane helpers
                    for (const axisName of ['x', 'y', 'z']) {
                        const axisData = this.clipping[axisName];
                        if (!axisData.helper || !axisData.showHelper || !axisData.enabled) continue;

                        const intersects = raycaster.intersectObject(axisData.helper, true);

                        if (intersects.length > 0) {
                            this._planeDragState.isDragging = true;
                            this._planeDragState.axis = axisName;

                            const intersectionPoint = intersects[0].point;
                            const normal = new this.iges.THREE.Vector3();
                            camera.getWorldDirection(normal);

                            const plane = new this.iges.THREE.Plane();
                            plane.setFromNormalAndCoplanarPoint(normal, intersectionPoint);
                            this._planeDragState.dragPlane = plane;

                            let clickValue;
                            if (axisName === 'x') clickValue = intersectionPoint.x;
                            else if (axisName === 'y') clickValue = intersectionPoint.y;
                            else clickValue = intersectionPoint.z;

                            this._planeDragState.offset = clickValue - axisData.value;

                            // Disable orbit controls while dragging
                            if (controls) controls.enabled = false;

                            // Change cursor
                            canvas.style.cursor = 'move';
                            event.preventDefault();
                            event.stopPropagation();
                            break;
                        }
                    }
                };

                // Mouse move handler - drag plane
                const onMouseMove = (event) => {
                    if (!this._planeDragState.isDragging) return;

                    const axis = this._planeDragState.axis;
                    const axisData = this.clipping[axis];

                    const rect = canvas.getBoundingClientRect();
                    const mouse = new this.iges.THREE.Vector2(
                        ((event.clientX - rect.left) / rect.width) * 2 - 1,
                        -((event.clientY - rect.top) / rect.height) * 2 + 1
                    );

                    const raycaster = new this.iges.THREE.Raycaster();
                    raycaster.setFromCamera(mouse, camera);

                    // Intersect with the virtual drag plane
                    const targetPoint = new this.iges.THREE.Vector3();
                    raycaster.ray.intersectPlane(this._planeDragState.dragPlane, targetPoint);

                    if (targetPoint) {
                        // Project the target point onto our axis to get the raw value
                        let rawValue;
                        if (axis === 'x') rawValue = targetPoint.x;
                        else if (axis === 'y') rawValue = targetPoint.y;
                        else rawValue = targetPoint.z;

                        // Apply the initial offset
                        let newValue = rawValue - this._planeDragState.offset;

                        // Clamp
                        const min = axisData.min !== undefined ? axisData.min : this.clipping.min;
                        const max = axisData.max !== undefined ? axisData.max : this.clipping.max;
                        newValue = Math.max(min, Math.min(max, newValue));

                        // Round to 2 decimal places for cleaner display
                        newValue = Math.round(newValue * 100) / 100;

                        // Update
                        axisData.value = newValue;
                        this.updateAxisClipping(axis);
                    }

                    event.preventDefault();
                };

                // Mouse up handler
                const onMouseUp = (event) => {
                    if (this._planeDragState.isDragging) {
                        this._planeDragState.isDragging = false;
                        this._planeDragState.axis = null;
                        this._planeDragState.dragPlane = null;

                        // Re-enable orbit controls
                        if (controls) controls.enabled = true;

                        // Reset cursor
                        canvas.style.cursor = 'default';
                    }
                };

                // Add event listeners once
                canvas.addEventListener('mousedown', onMouseDown, false);
                canvas.addEventListener('mousemove', onMouseMove, false);
                canvas.addEventListener('mouseup', onMouseUp, false);
                canvas.addEventListener('mouseleave', onMouseUp, false);

                console.log('Plane helper drag interaction initialized (Ray-to-Plane v3)');
            },

            // NEW: Update plane helper position
            _updatePlaneHelper(axis) {
                const axisData = this.clipping[axis];
                if (!axisData.helper || !axisData.showHelper) return;

                // Update position based on current value
                if (axis === 'x') {
                    axisData.helper.position.x = axisData.value;
                } else if (axis === 'y') {
                    axisData.helper.position.y = axisData.value;
                } else if (axis === 'z') {
                    axisData.helper.position.z = axisData.value;
                }
            },

            // Helper to check if any clipping is active
            get hasActiveClipping() {
                return this.clipping.x.enabled || this.clipping.y.enabled || this.clipping.z.enabled;
            },

            // Helper untuk menempelkan plane ke material
            _updateMaterialsWithClipping() {
                const { rootModel } = this.iges;
                if (!rootModel) return;

                // Collect all active clipping planes
                const planes = [];
                ['x', 'y', 'z'].forEach(axis => {
                    const axisData = this.clipping[axis];
                    if (axisData.enabled && axisData.plane) {
                        planes.push(axisData.plane);
                    }
                });

                rootModel.traverse((o) => {
                    if (o.isMesh && o.material) {
                        const mats = Array.isArray(o.material) ? o.material : [o.material];

                        mats.forEach(m => {
                            m.clippingPlanes = planes;
                            m.clipShadows = true;
                            m.needsUpdate = true;
                        });
                    }
                });
            },

            _bindMeasureEvents(on) {
                const canvas = this.iges.renderer?.domElement;
                if (!canvas) return;

                if (on) {
                    // Event Click - for picking measurement points
                    this._onMeasureClick = (ev) => {
                        if (!this.iges.measure.enabled) return;

                        const M = this.iges.measure;
                        const pickResult = this._pickPointAdvanced(ev);
                        if (!pickResult) return;

                        const p = pickResult.point;

                        // --- POINT TO POINT & EDGE ---
                        // --- POINT TO POINT ---
                        if (M.mode === 'point') {
                             if (!M.p1) {
                                M.p1 = p;
                                M.snap.type = pickResult.snapType;
                                M.hoverInstruction = 'Click 2nd Point';
                            } else {
                                this._drawMeasurement(M.p1, p, 'point');
                                M.p1 = null;
                                M.hoverInstruction = 'Click 1st Point';
                            }
                        }
                        // --- EDGE (Auto Length + Manual Fallback) ---
                        else if (M.mode === 'edge') {
                            // If user clicked an edge (detected via snap)
                            if (pickResult.edge && (pickResult.snapType === 'edge' || pickResult.snapType === 'midpoint')) {
                                this._drawMeasurement(pickResult.edge.start, pickResult.edge.end, 'edge');
                                M.p1 = null;
                                M.hoverInstruction = 'Click another Edge';
                            } else {
                                // Fallback to manual 2-point measurement
                                if (!M.p1) {
                                    M.p1 = p;
                                    M.snap.type = pickResult.snapType;
                                    M.hoverInstruction = 'Click 2nd Point (Manual)';
                                } else {
                                    this._drawMeasurement(M.p1, p, 'edge');
                                    M.p1 = null;
                                    M.hoverInstruction = 'Click 1st Point or Select Edge';
                                }
                            }
                        }
                        // --- ANGLE (3 Points) ---
                        else if (M.mode === 'angle') {
                            if (!M.p1) {
                                M.p1 = p;
                                M.hoverInstruction = 'Click Vertex (2nd Point)';
                            } else if (!M.p2) {
                                M.p2 = p; // Vertex
                                M.hoverInstruction = 'Click End Point (3rd Point)';
                            } else {
                                this._drawAngleMeasurement(M.p1, M.p2, p);
                                M.p1 = M.p2 = null;
                                M.hoverInstruction = 'Click 1st Point (Start)';
                            }
                        }
                        // --- RADIUS (3 Points) ---
                        else if (M.mode === 'radius') {
                            if (!M.p1) {
                                M.p1 = p;
                                M.hoverInstruction = 'Click 2nd Point on Curve';
                            } else if (!M.p2) {
                                M.p2 = p;
                                M.hoverInstruction = 'Click 3rd Point on Curve';
                            } else {
                                M.p3 = p;
                                const circle = this._calculateCircleFrom3Points(M.p1, M.p2, M.p3);
                                if (circle) {
                                    this._drawRadiusMeasurement(circle, M.p1, M.p2, M.p3);
                                } else {
                                    console.warn("Points are collinear, cannot calculate circle.");
                                }
                                M.p1 = M.p2 = M.p3 = null;
                                M.hoverInstruction = 'Click 1st Point on Curve';
                            }
                        }
                        // --- FACE AREA ---
                        else if (M.mode === 'face') {
                            if (pickResult.hit && pickResult.hit.face) {
                                const area = this._calculateFaceArea(pickResult.hit.object, pickResult.hit.faceIndex);
                                this._drawFaceAreaMeasurement(pickResult.point, area, pickResult.normal, pickResult.hit.object);
                                M.hoverInstruction = 'Click another Face';
                            }
                        }
                    };

                    // Event Hover - for snap preview
                    this._onMeasureMove = (ev) => {
                        if (!this.iges.measure.enabled) return;
                        this._pickPointAdvanced(ev);
                    };

                    // Right-click to cancel current interactions
                    this._onMeasureRightClick = (ev) => {
                        ev.preventDefault();
                        const M = this.iges.measure;
                        M.p1 = null;
                        M.p2 = null;
                        M.p3 = null;

                        // Reset instruction
                        this.setMeasureMode(M.mode);

                        if (this.snapMarker) this.snapMarker.visible = false;
                        console.log('Measurement cancelled');
                    };

                    canvas.addEventListener('click', this._onMeasureClick);
                    canvas.addEventListener('mousemove', this._onMeasureMove);
                    canvas.addEventListener('contextmenu', this._onMeasureRightClick);
                } else {
                    if (this._onMeasureClick) canvas.removeEventListener('click', this._onMeasureClick);
                    if (this._onMeasureMove) canvas.removeEventListener('mousemove', this._onMeasureMove);
                    if (this._onMeasureRightClick) canvas.removeEventListener('contextmenu', this._onMeasureRightClick);

                    // Hide marker when measure mode is off
                    if (this.snapMarker) this.snapMarker.visible = false;
                }
            },
            _pickPoint(ev) {
                const {
                    THREE,
                    camera,
                    rootModel,
                    renderer
                } = this.iges;
                if (!renderer) return null;

                const rect = renderer.domElement.getBoundingClientRect();
                const mouse = new THREE.Vector2(
                    ((ev.clientX - rect.left) / rect.width) * 2 - 1,
                    -((ev.clientY - rect.top) / rect.height) * 2 + 1
                );

                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(mouse, camera);

                raycaster.firstHitOnly = true;

                const hits = raycaster.intersectObjects(rootModel.children, true);
                if (!hits.length) {
                    if (this.snapMarker) this.snapMarker.visible = false;
                    return null;
                }

                const hit = hits[0];
                let finalPoint = hit.point.clone();

                // === LOGIKA SNAPPING (MAGNET) ===
                if (hit.face) {
                    const mesh = hit.object;
                    const pos = mesh.geometry.attributes.position;

                    const vA = new THREE.Vector3().fromBufferAttribute(pos, hit.face.a).applyMatrix4(mesh.matrixWorld);
                    const vB = new THREE.Vector3().fromBufferAttribute(pos, hit.face.b).applyMatrix4(mesh.matrixWorld);
                    const vC = new THREE.Vector3().fromBufferAttribute(pos, hit.face.c).applyMatrix4(mesh.matrixWorld);
                    const distA = hit.point.distanceTo(vA);
                    const distB = hit.point.distanceTo(vB);
                    const distC = hit.point.distanceTo(vC);

                    const snapThreshold = hit.distance * 0.05;

                    let closest = null;
                    let minInfo = snapThreshold;

                    if (distA < minInfo) {
                        closest = vA;
                        minInfo = distA;
                    }
                    if (distB < minInfo) {
                        closest = vB;
                        minInfo = distB;
                    }
                    if (distC < minInfo) {
                        closest = vC;
                        minInfo = distC;
                    }

                    if (closest) {
                        finalPoint = closest;
                    }
                }

                // Update Visual Marker
                this._updateSnapMarker(finalPoint);

                return finalPoint;
            },
            _updateSnapMarker(position) {
                const {
                    THREE,
                    scene
                } = this.iges;

                if (!this.snapMarker) {
                    const geom = new THREE.SphereGeometry(2, 16, 16);
                    const mat = new THREE.MeshBasicMaterial({
                        color: 0xff0000,
                        transparent: true,
                        opacity: 0.8,
                        depthTest: false
                    });
                    this.snapMarker = new THREE.Mesh(geom, mat);
                    this.snapMarker.renderOrder = 999;
                    scene.add(this.snapMarker);
                }

                this.snapMarker.visible = true;
                this.snapMarker.position.copy(position);

                const scale = this.iges.camera.position.distanceTo(position) * 0.01;
                this.snapMarker.scale.set(scale, scale, scale);
            },

            _pickPointAdvanced(ev) {
                const {
                    THREE,
                    camera,
                    rootModel,
                    renderer
                } = this.iges;
                if (!renderer) return null;

                const rect = renderer.domElement.getBoundingClientRect();
                const mouse = new THREE.Vector2(
                    ((ev.clientX - rect.left) / rect.width) * 2 - 1,
                    -((ev.clientY - rect.top) / rect.height) * 2 + 1
                );

                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(mouse, camera);
                raycaster.firstHitOnly = true;

                const hits = raycaster.intersectObjects(rootModel.children, true);
                if (!hits.length) {
                    if (this.snapMarker) this.snapMarker.visible = false;
                    return null;
                }

                const hit = hits[0];
                let finalPoint = hit.point.clone();
                let snapType = 'surface';
                let edgeInfo = null;
                let faceNormal = null;

                if (hit.face) {
                    const mesh = hit.object;
                    const pos = mesh.geometry.attributes.position;

                    // Get triangle vertices
                    const vA = new THREE.Vector3().fromBufferAttribute(pos, hit.face.a).applyMatrix4(mesh.matrixWorld);
                    const vB = new THREE.Vector3().fromBufferAttribute(pos, hit.face.b).applyMatrix4(mesh.matrixWorld);
                    const vC = new THREE.Vector3().fromBufferAttribute(pos, hit.face.c).applyMatrix4(mesh.matrixWorld);

                    // Store face normal for angle calculations
                    faceNormal = hit.face.normal.clone().transformDirection(mesh.matrixWorld);

                    // Get edge midpoints
                    const midAB = vA.clone().add(vB).multiplyScalar(0.5);
                    const midBC = vB.clone().add(vC).multiplyScalar(0.5);
                    const midCA = vC.clone().add(vA).multiplyScalar(0.5);

                    const snapThreshold = hit.distance * 0.05;
                    const edgeSnapThreshold = hit.distance * 0.03;

                    // Check vertex snap
                    const distA = hit.point.distanceTo(vA);
                    const distB = hit.point.distanceTo(vB);
                    const distC = hit.point.distanceTo(vC);

                    let closest = null;
                    let minDist = snapThreshold;

                    if (distA < minDist) { closest = vA; minDist = distA; snapType = 'vertex'; }
                    if (distB < minDist) { closest = vB; minDist = distB; snapType = 'vertex'; }
                    if (distC < minDist) { closest = vC; minDist = distC; snapType = 'vertex'; }

                    // Check midpoint snap (if no vertex snap)
                    if (!closest) {
                        const distMidAB = hit.point.distanceTo(midAB);
                        const distMidBC = hit.point.distanceTo(midBC);
                        const distMidCA = hit.point.distanceTo(midCA);

                        if (distMidAB < edgeSnapThreshold) { closest = midAB; snapType = 'midpoint'; edgeInfo = { start: vA, end: vB }; }
                        else if (distMidBC < edgeSnapThreshold) { closest = midBC; snapType = 'midpoint'; edgeInfo = { start: vB, end: vC }; }
                        else if (distMidCA < edgeSnapThreshold) { closest = midCA; snapType = 'midpoint'; edgeInfo = { start: vC, end: vA }; }
                    }

                    // Check edge snap (project point to nearest edge)
                    if (!closest) {
                        const edges = [
                            { start: vA, end: vB },
                            { start: vB, end: vC },
                            { start: vC, end: vA }
                        ];

                        for (const edge of edges) {
                            const projected = this._projectPointOnLine(hit.point, edge.start, edge.end);
                            if (projected && hit.point.distanceTo(projected) < edgeSnapThreshold) {
                                closest = projected;
                                snapType = 'edge';
                                edgeInfo = edge;
                                break;
                            }
                        }
                    }

                    if (closest) {
                        finalPoint = closest;
                    }
                }

                if (!this.iges.measure.snap.enabled) {
                    snapType = 'surface';
                    finalPoint = hit.point.clone();
                } else {
                   this._updateSnapMarkerAdvanced(finalPoint, snapType);
                }

                return {
                    point: finalPoint,
                    snapType: snapType,
                    edge: edgeInfo,
                    normal: faceNormal,
                    hit: hit
                };
            },

            // Project point onto a line segment
            _projectPointOnLine(point, lineStart, lineEnd) {
                const THREE = this.iges.THREE;
                const line = lineEnd.clone().sub(lineStart);
                const lineLen = line.length();
                if (lineLen === 0) return null;

                const lineDir = line.normalize();
                const pointVec = point.clone().sub(lineStart);
                const t = pointVec.dot(lineDir);

                // Check if projection is within line segment
                if (t < 0 || t > lineLen) return null;

                return lineStart.clone().add(lineDir.multiplyScalar(t));
            },

            // Update snap marker with color based on snap type
            _updateSnapMarkerAdvanced(position, snapType) {
                const { THREE, scene } = this.iges;

                if (!this.snapMarker) {
                    // Balanced size - visible but not too large
                    const geom = new THREE.SphereGeometry(1.2, 16, 16); // Sweet spot between 0.8 and 2
                    const mat = new THREE.MeshBasicMaterial({
                        color: 0xff0000,
                        transparent: true,
                        opacity: 0.7, // More visible than 0.5, less solid than 0.9
                        depthTest: false
                    });
                    this.snapMarker = new THREE.Mesh(geom, mat);
                    this.snapMarker.renderOrder = 999;
                    scene.add(this.snapMarker);
                }

                // Color based on snap type
                const colors = {
                    vertex: 0xff0000,    // Red for vertex
                    edge: 0x00ff00,      // Green for edge
                    midpoint: 0xffff00,  // Yellow for midpoint
                    surface: 0x0088ff    // Blue for surface
                };

                this.snapMarker.material.color.setHex(colors[snapType] || 0xff0000);
                this.snapMarker.visible = true;
                this.snapMarker.position.copy(position);

                // Balanced scale - visible but not overwhelming
                let scale;
                if (this.iges.camera.isOrthographicCamera) {
                    const height = (this.iges.camera.top - this.iges.camera.bottom) / this.iges.camera.zoom;
                    scale = height * 0.008;
                } else {
                    scale = this.iges.camera.position.distanceTo(position) * 0.007;
                }
                this.snapMarker.scale.set(scale, scale, scale);
            },

            // Draw edge length measurement
            _drawEdgeMeasurement(edge) {
                if (!edge || !edge.start || !edge.end) return;
                this._drawMeasurement(edge.start, edge.end, 'edge');
            },

            // Draw face area measurement
            _drawFaceAreaMeasurement(centerPoint, area, normal, targetMesh) {
                const THREE = this.iges.THREE;
                const uId = THREE.MathUtils.generateUUID();

                // 1. Save original material if not already saved
                if (targetMesh && targetMesh.isMesh) {
                    if (!this._oriMats.has(targetMesh)) {
                        const m = targetMesh.material;
                        this._oriMats.set(targetMesh, Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone());
                    }

                    // Prepare clipping planes if active
                    const currentPlanes = (this.clipping.enabled && this.clipping.plane) ? [this.clipping.plane] : [];

                    // Create highlight material (Red) with clipping support
                    const highlightMat = new THREE.MeshBasicMaterial({
                        color: 0xff0000,
                        opacity: 0.6,
                        transparent: true,
                        depthTest: false,
                        side: THREE.DoubleSide, // Render both front and back faces
                        clippingPlanes: currentPlanes,
                        clipShadows: true
                    });

                    targetMesh.material = highlightMat;
                }

                // 2. Create label at center point
                const wrap = this.$refs.igesWrap;
                const lbl = document.createElement('div');
                lbl.className = 'measure-label-detailed';
                Object.assign(lbl.style, {
                    position: 'absolute',
                    left: '0',
                    top: '0',
                    padding: '6px 10px',
                    background: 'rgba(0, 0, 0, 0.85)',
                    color: '#fff',
                    borderRadius: '6px',
                    fontSize: '10px',
                    fontFamily: 'monospace',
                    pointerEvents: 'none',
                    zIndex: '10',
                    border: '1px solid rgba(255, 0, 0, 0.5)',
                    backdropFilter: 'blur(4px)'
                });
                wrap.appendChild(lbl);

                // 3. Store result
                const measureResult = {
                    id: uId,
                    type: 'face',
                    area: area,
                    meshUuid: targetMesh ? targetMesh.uuid : null,
                    objectUuid: uId
                };
                this.iges.measure.results.push(measureResult);

                // 4. Update & Dispose
                const updateLabel = () => {
                   if (!this.iges.camera) return;
                   const pos = centerPoint.clone();
                   pos.project(this.iges.camera);

                   const x = (pos.x * 0.5 + 0.5) * wrap.clientWidth;
                   const y = (-pos.y * 0.5 + 0.5) * wrap.clientHeight;

                   lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
                   lbl.innerHTML = `<i class="fa-solid fa-vector-square text-red-500 mr-1"></i> Area: ${area.toFixed(2)} mm²`;
                };

                // Create a dummy group to hold the label update function
                const group = new THREE.Group();
                group.uuid = uId;
                group.userData.meshUuid = targetMesh ? targetMesh.uuid : null; // Store for disposal
                group.userData.update = updateLabel;
                group.userData.dispose = () => {
                    if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                    // Restore original material using stored meshUuid
                    if (group.userData.meshUuid) {
                        const mesh = this.iges.rootModel.getObjectByProperty('uuid', group.userData.meshUuid);
                        if (mesh && this._oriMats.has(mesh)) {
                            const originalMat = this._oriMats.get(mesh);
                            mesh.material = Array.isArray(originalMat)
                                ? originalMat.map(m => m.clone())
                                : originalMat.clone();
                        }
                    }
                };

                updateLabel();
                this.iges.measure.group.add(group);
            },

            // Draw radius measurement
            _drawRadiusMeasurement(circle, p1, p2, p3) {
                 const { center, radius, normal } = circle;
                 const THREE = this.iges.THREE;
                 const group = new THREE.Group();
                 const uId = THREE.MathUtils.generateUUID();
                 group.name = uId;

                 const curve = new THREE.EllipseCurve(
                     0, 0,            // ax, aY
                     radius, radius,  // xRadius, yRadius
                     0, 2 * Math.PI,  // aStartAngle, aEndAngle
                     false,           // aClockwise
                     0                // aRotation
                 );

                 const points = curve.getPoints(64);
                 const geometry = new THREE.BufferGeometry().setFromPoints(points);
                 const material = new THREE.LineBasicMaterial({ color: 0x00ff00, depthTest: false });
                 const circleMesh = new THREE.Line(geometry, material);

                 circleMesh.lookAt(normal);

                 const defaultNormal = new THREE.Vector3(0, 0, 1);
                 const quaternion = new THREE.Quaternion().setFromUnitVectors(defaultNormal, normal);
                 circleMesh.setRotationFromQuaternion(quaternion);
                 circleMesh.position.copy(center);
                 group.add(circleMesh);

                 // 2. Draw Center Point
                 const centerGeom = new THREE.SphereGeometry(radius * 0.05, 16, 16);
                 const centerMesh = new THREE.Mesh(centerGeom, new THREE.MeshBasicMaterial({ color: 0x00ff00, depthTest: false }));
                 centerMesh.position.copy(center);
                 group.add(centerMesh);

                 // 3. Draw lines to the 3 points
                 const linesGeom = new THREE.BufferGeometry().setFromPoints([p1, center, p2, center, p3]);
                 const lines = new THREE.LineSegments(linesGeom, new THREE.LineDashedMaterial({ color: 0x00ff00, dashSize: 0.5, gapSize: 0.5 }));
                 lines.computeLineDistances();
                 group.add(lines);

                 // 4. HTML Label
                 const wrap = this.$refs.igesWrap;
                 const lbl = document.createElement('div');
                 lbl.className = 'measure-label-detailed';

                 Object.assign(lbl.style, {
                    position: 'absolute',
                    left: '0',
                    top: '0',
                    padding: '6px 10px',
                    background: 'rgba(0, 0, 0, 0.85)',
                    color: '#fff',
                    borderRadius: '6px',
                    fontSize: '10px',
                    fontFamily: 'monospace',
                    pointerEvents: 'none',
                    zIndex: '10',
                    border: '1px solid rgba(0, 255, 0, 0.5)',
                    backdropFilter: 'blur(4px)'
                 });
                 wrap.appendChild(lbl);

                 const diameter = radius * 2;

                  // 5. Store result
                const measureResult = {
                    id: uId,
                    type: 'radius',
                    radius: radius,
                    diameter: diameter,
                    center: center,
                    objectUuid: uId
                };
                this.iges.measure.results.push(measureResult);

                 const updateLabel = () => {
                    if (!this.iges.camera) return;
                    const pos = center.clone();
                    pos.project(this.iges.camera);

                    const x = (pos.x * 0.5 + 0.5) * wrap.clientWidth;
                    const y = (-pos.y * 0.5 + 0.5) * wrap.clientHeight;

                    lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
                    lbl.innerHTML = `
                        <div class="text-green-400 font-bold mb-1"><i class="fa-regular fa-circle mr-1"></i>Radius: ${radius.toFixed(2)} mm</div>
                        <div class="text-teal-400">Ø Diameter: ${diameter.toFixed(2)} mm</div>
                    `;
                 };

                 group.userData.update = updateLabel;
                 group.userData.dispose = () => {
                     if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                     geometry.dispose();
                     material.dispose();
                     centerGeom.dispose();
                     linesGeom.dispose();
                 };

                 // CRITICAL: Set group.uuid for deletion to work
                 group.uuid = uId;

                 updateLabel();
                 this.iges.measure.group.add(group);
            },

            // Draw angle measurement between three points
            _drawAngleMeasurement(p1, vertex, p3) {
                const THREE = this.iges.THREE;
                const group = new THREE.Group();

                // Calculate vectors from vertex to other points
                const v1 = p1.clone().sub(vertex).normalize();
                const v2 = p3.clone().sub(vertex).normalize();

                // Calculate angle in degrees
                const dotProduct = v1.dot(v2);
                const angleRad = Math.acos(Math.max(-1, Math.min(1, dotProduct)));
                const angleDeg = angleRad * (180 / Math.PI);

                // Calculate distances
                const dist1 = vertex.distanceTo(p1);
                const dist2 = vertex.distanceTo(p3);
                const dist3 = p1.distanceTo(p3);

                const uId = THREE.MathUtils.generateUUID();
                group.name = uId;

                // Store measurement result
                const measureResult = {
                    id: uId,
                    angle: angleDeg,
                    distance: dist3,
                    deltaX: Math.abs(p3.x - p1.x),
                    deltaY: Math.abs(p3.y - p1.y),
                    deltaZ: Math.abs(p3.z - p1.z),
                    vertex: vertex.clone(),
                    pointA: p1.clone(),
                    pointB: p3.clone(),
                    type: 'angle',
                    objectUuid: uId
                };

                this.iges.measure.currentResult = measureResult;
                this.iges.measure.results.push(measureResult);

                // Draw lines from vertex to both points
                const arcRadius = Math.min(dist1, dist2) * 0.3;

                // Line 1 (vertex to p1)
                const line1Geom = new THREE.BufferGeometry().setFromPoints([vertex, p1]);
                const line1 = new THREE.Line(line1Geom, new THREE.LineBasicMaterial({
                    color: 0xffff00,
                    depthTest: false
                }));
                line1.renderOrder = 999;
                group.add(line1);

                // Line 2 (vertex to p3)
                const line2Geom = new THREE.BufferGeometry().setFromPoints([vertex, p3]);
                const line2 = new THREE.Line(line2Geom, new THREE.LineBasicMaterial({
                    color: 0xffff00,
                    depthTest: false
                }));
                line2.renderOrder = 999;
                group.add(line2);

                // Draw arc between the two lines
                const arcPoints = [];
                const arcSegments = 32;
                for (let i = 0; i <= arcSegments; i++) {
                    const t = i / arcSegments;
                    const currentAngle = t * angleRad;

                    // Interpolate direction
                    const dir = v1.clone().applyAxisAngle(
                        v1.clone().cross(v2).normalize(),
                        currentAngle
                    );
                    arcPoints.push(vertex.clone().add(dir.multiplyScalar(arcRadius)));
                }
                const arcGeom = new THREE.BufferGeometry().setFromPoints(arcPoints);
                const arcLine = new THREE.Line(arcGeom, new THREE.LineBasicMaterial({
                    color: 0xff00ff,
                    depthTest: false
                }));
                arcLine.renderOrder = 999;
                group.add(arcLine);

                // Draw spheres at all three points
                const sphereSize = arcRadius * 0.15;
                const sphereGeom = new THREE.SphereGeometry(sphereSize, 16, 16);

                const sphere1 = new THREE.Mesh(sphereGeom, new THREE.MeshBasicMaterial({ color: 0xff0000, depthTest: false }));
                sphere1.position.copy(p1);
                sphere1.renderOrder = 1000;
                group.add(sphere1);

                const sphereVertex = new THREE.Mesh(sphereGeom, new THREE.MeshBasicMaterial({ color: 0xffff00, depthTest: false }));
                sphereVertex.position.copy(vertex);
                sphereVertex.renderOrder = 1000;
                group.add(sphereVertex);

                const sphere3 = new THREE.Mesh(sphereGeom, new THREE.MeshBasicMaterial({ color: 0xff0000, depthTest: false }));
                sphere3.position.copy(p3);
                sphere3.renderOrder = 1000;
                group.add(sphere3);

                // Create HTML label for angle
                const wrap = this.$refs.igesWrap;
                const lbl = document.createElement('div');
                lbl.className = 'measure-angle-label';

                Object.assign(lbl.style, {
                    position: 'absolute',
                    left: '0',
                    top: '0',
                    padding: '6px 10px',
                    background: 'rgba(0, 0, 0, 0.85)',
                    color: '#fff',
                    borderRadius: '6px',
                    fontSize: '10px',
                    fontFamily: 'monospace',
                    pointerEvents: 'none',
                    zIndex: '50',
                    border: '1px solid rgba(255, 0, 255, 0.5)',
                    backdropFilter: 'blur(4px)'
                });

                wrap.appendChild(lbl);

                const updateLabel = () => {
                    if (!this.iges.camera) return;

                    const labelPos = vertex.clone().add(v1.clone().add(v2).normalize().multiplyScalar(arcRadius * 1.5));
                    labelPos.project(this.iges.camera);

                    const w = wrap.clientWidth;
                    const h = wrap.clientHeight;
                    const x = (labelPos.x * 0.5 + 0.5) * w;
                    const y = (-labelPos.y * 0.5 + 0.5) * h;

                    lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;

                    lbl.innerHTML = `
                        <div class="text-purple-400 font-bold mb-1"><i class="fa-solid fa-angle-left mr-1"></i>Angle: ${angleDeg.toFixed(2)}°</div>
                        <div class="text-blue-300 mb-1">Dist: ${dist3.toFixed(2)} mm</div>
                        <div class="grid grid-cols-3 gap-1 text-[9px] opacity-80">
                            <span class="text-red-400">ΔX: ${measureResult.deltaX.toFixed(2)}</span>
                            <span class="text-green-400">ΔY: ${measureResult.deltaY.toFixed(2)}</span>
                            <span class="text-blue-400">ΔZ: ${measureResult.deltaZ.toFixed(2)}</span>
                        </div>
                    `;
                };

                group.userData.update = updateLabel;
                group.userData.measureResult = measureResult;

                group.userData.dispose = () => {
                    if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                    group.traverse(child => {
                        if (child.geometry) child.geometry.dispose();
                        if (child.material) child.material.dispose();
                    });
                };

                // CRITICAL: Set group.uuid for deletion to work
                group.uuid = uId;

                updateLabel();
                this.iges.measure.group.add(group);

                console.log('Angle measurement created:', angleDeg.toFixed(3) + '°');
            },

            _drawMeasurement(a, b, measureType = 'point') {
                const THREE = this.iges.THREE;
                const group = new THREE.Group();

                // Calculate measurement data
                const distance = a.distanceTo(b);
                const deltaX = Math.abs(b.x - a.x);
                const deltaY = Math.abs(b.y - a.y);
                const deltaZ = Math.abs(b.z - a.z);

                // Calculate angle from horizontal (XY plane)
                const horizontalDist = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
                const angle = Math.atan2(deltaZ, horizontalDist) * (180 / Math.PI);

                const uId = THREE.MathUtils.generateUUID();
                group.name = uId;

                // Store measurement result for panel display
                const measureResult = {
                    id: uId,
                    distance: distance,
                    deltaX: deltaX,
                    deltaY: deltaY,
                    deltaZ: deltaZ,
                    angle: Math.abs(angle),
                    pointA: a.clone(),
                    pointB: b.clone(),
                    type: measureType,
                    objectUuid: uId,
                    totalLength: (measureType === 'edge' || measureType === 'point') ? distance : undefined
                };

                this.iges.measure.currentResult = measureResult;
                this.iges.measure.results.push(measureResult);

                // 1. Draw main measurement line (White)
                const mainLineGeom = new THREE.BufferGeometry().setFromPoints([a, b]);
                const mainLine = new THREE.Line(mainLineGeom, new THREE.LineBasicMaterial({
                    color: 0xffffff,
                    linewidth: 2,
                    depthTest: false
                }));
                mainLine.renderOrder = 999;
                group.add(mainLine);

                // 2. Draw Delta Lines (X=Red, Y=Green, Z=Blue) - eDrawings style
                const cornerXY = new THREE.Vector3(b.x, b.y, a.z);
                const cornerX = new THREE.Vector3(b.x, a.y, a.z);

                // Delta X line (Red) - from A to corner along X
                if (deltaX > 0.01) {
                    const xLineGeom = new THREE.BufferGeometry().setFromPoints([a, new THREE.Vector3(b.x, a.y, a.z)]);
                    const xLine = new THREE.Line(xLineGeom, new THREE.LineDashedMaterial({
                        color: 0xff4444,
                        dashSize: 3,
                        gapSize: 2,
                        depthTest: false
                    }));
                    xLine.computeLineDistances();
                    xLine.renderOrder = 998;
                    group.add(xLine);
                }

                // Delta Y line (Green)
                if (deltaY > 0.01) {
                    const yStart = new THREE.Vector3(b.x, a.y, a.z);
                    const yEnd = new THREE.Vector3(b.x, b.y, a.z);
                    const yLineGeom = new THREE.BufferGeometry().setFromPoints([yStart, yEnd]);
                    const yLine = new THREE.Line(yLineGeom, new THREE.LineDashedMaterial({
                        color: 0x44ff44,
                        dashSize: 3,
                        gapSize: 2,
                        depthTest: false
                    }));
                    yLine.computeLineDistances();
                    yLine.renderOrder = 998;
                    group.add(yLine);
                }

                // Delta Z line (Blue)
                if (deltaZ > 0.01) {
                    const zStart = cornerXY;
                    const zEnd = b;
                    const zLineGeom = new THREE.BufferGeometry().setFromPoints([zStart, zEnd]);
                    const zLine = new THREE.Line(zLineGeom, new THREE.LineDashedMaterial({
                        color: 0x4444ff,
                        dashSize: 3,
                        gapSize: 2,
                        depthTest: false
                    }));
                    zLine.computeLineDistances();
                    zLine.renderOrder = 998;
                    group.add(zLine);
                }

                // 3. Draw endpoint spheres (Red)
                const sphereSize = Math.max(0.5, distance / 100);
                const sphereGeom = new THREE.SphereGeometry(sphereSize, 16, 16);
                const sphereMat = new THREE.MeshBasicMaterial({
                    color: 0xff0000,
                    depthTest: false
                });

                const sphere1 = new THREE.Mesh(sphereGeom, sphereMat);
                sphere1.position.copy(a);
                sphere1.renderOrder = 1000;
                group.add(sphere1);

                const sphere2 = new THREE.Mesh(sphereGeom, sphereMat);
                sphere2.position.copy(b);
                sphere2.renderOrder = 1000;
                group.add(sphere2);

                // 4. Create HTML label with detailed info
                const wrap = this.$refs.igesWrap;
                const lbl = document.createElement('div');
                lbl.className = 'measure-label-detailed';

                Object.assign(lbl.style, {
                    position: 'absolute',
                    left: '0',
                    top: '0',
                    padding: '6px 10px',
                    background: 'rgba(0, 0, 0, 0.85)',
                    color: '#fff',
                    borderRadius: '6px',
                    fontSize: '10px',
                    fontFamily: 'monospace',
                    pointerEvents: 'none',
                    zIndex: '10',
                    border: '1px solid rgba(255, 255, 255, 0.2)',
                    backdropFilter: 'blur(4px)'
                });

                wrap.appendChild(lbl);

                // Update function for label position and content
                const updateLabel = () => {
                    if (!this.iges.camera) return;

                    const mid = a.clone().add(b).multiplyScalar(0.5);
                    mid.project(this.iges.camera);

                    const w = wrap.clientWidth;
                    const h = wrap.clientHeight;
                    const x = (mid.x * 0.5 + 0.5) * w;
                    const y = (-mid.y * 0.5 + 0.5) * h;

                    lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;

                    // Rich label content
                    lbl.innerHTML = `
                        <div class="text-blue-400 font-bold mb-1"><i class="fa-solid fa-ruler mr-1"></i>${distance.toFixed(2)} mm</div>
                        <div class="grid grid-cols-3 gap-1 text-[9px] opacity-80">
                            <span class="text-red-400">ΔX: ${deltaX.toFixed(2)}</span>
                            <span class="text-green-400">ΔY: ${deltaY.toFixed(2)}</span>
                            <span class="text-blue-400">ΔZ: ${deltaZ.toFixed(2)}</span>
                        </div>
                    `;
                };

                group.userData.update = updateLabel;
                group.userData.measureResult = measureResult;

                // Dispose function
                group.userData.dispose = () => {
                    if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                    mainLineGeom.dispose();
                    mainLine.material.dispose();
                    sphereGeom.dispose();
                    sphereMat.dispose();
                    // Dispose delta lines
                    group.traverse(child => {
                        if (child.geometry) child.geometry.dispose();
                        if (child.material) child.material.dispose();
                    });
                };

                // CRITICAL: Set group.uuid for deletion to work
                group.uuid = uId;

                updateLabel();
                this.iges.measure.group.add(group);

                console.log('Measurement created:', measureResult);
            },

            setStandardView(view, duration = 800) {
                const {
                    camera,
                    controls,
                    rootModel,
                    THREE
                } = this.iges;
                if (!rootModel || !camera) return;

                // 1. Hitung ulang ukuran benda agar jarak kamera pas
                const box = new THREE.Box3().setFromObject(rootModel);
                const center = new THREE.Vector3();
                box.getCenter(center); // Harusnya (0,0,0) kalau sudah di-center
                const size = new THREE.Vector3();
                box.getSize(size);
                const maxDim = Math.max(size.x, size.y, size.z);

                // Jarak kamera ideal (Fit Distance)
                const fitDist = maxDim * 1.5; // Faktor pengali jarak

                // 2. Tentukan posisi baru berdasarkan View
                let newPos = new THREE.Vector3();

                switch (view) {
                    case 'front':
                        newPos.set(0, 0, fitDist);
                        break;
                    case 'back':
                        newPos.set(0, 0, -fitDist);
                        break;
                    case 'top':
                        newPos.set(0, fitDist, -0.01); // Offset -Z to avoid singularity with Y-Up logic
                        break;
                    case 'bottom':
                        newPos.set(0, -fitDist, 0.01); // Offset +Z to avoid singularity
                        break;
                    case 'right':
                        newPos.set(fitDist, 0, 0);
                        break;
                    case 'left':
                        newPos.set(-fitDist, 0, 0);
                        break;
                    case 'iso':
                    default:
                        // Isometric (Pojok)
                        newPos.set(fitDist, fitDist, fitDist).normalize().multiplyScalar(fitDist);
                        break;
                }

                // 3. Tentukan Target Up Vector
                const newUp = new THREE.Vector3(0, 1, 0);
                if (view === 'top') {
                    newUp.set(0, 0, -1);
                } else if (view === 'bottom') {
                    newUp.set(0, 0, 1);
                }

                // 4. Animate Camera Transition
                this._animateCamera(newPos, center, newUp, () => {
                    // This callback runs when animation finishes
                    
                    // FIX: Auto-restore Turntable Orbit (Y-Up) on interaction
                    // Only attach this listener if NOT in auto-rotate mode (to prevent conflict)
                    if (!this.autoRotate) {
                        if (controls._resetUpListener) {
                            controls.removeEventListener('start', controls._resetUpListener);
                            controls._resetUpListener = null;
                        }

                        if (view === 'top' || view === 'bottom') {
                            controls._resetUpListener = () => {
                                camera.up.set(0, 1, 0);
                                controls.update();
                                controls.removeEventListener('start', controls._resetUpListener);
                                controls._resetUpListener = null;
                            };
                            controls.addEventListener('start', controls._resetUpListener);
                        }
                    }
                }, duration);
            },

            _animateCamera(targetPos, targetTarget, targetUp, onComplete, duration = 800) {
                // Use Raw objects to avoid Alpine proxy overhead during animation
                let { camera, controls } = this.iges;
                if (typeof Alpine !== 'undefined' && Alpine.raw) {
                    camera = Alpine.raw(camera);
                    controls = Alpine.raw(controls);
                }

                // Use a separate ID for transition, DO NOT touch this.iges.animId (Main Loop)
                if (this.iges.transitionAnimId) cancelAnimationFrame(this.iges.transitionAnimId);

                const startPos = camera.position.clone();
                const startTarget = controls.target.clone();
                const startUp = camera.up.clone();
                const startTime = performance.now();

                const animate = (time) => {
                    let elapsed = time - startTime;
                    let t = elapsed / duration;
                    if (t > 1) t = 1;

                    // Ease In Out Cubic (Smoother start and end)
                    const ease = t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;

                    camera.position.lerpVectors(startPos, targetPos, ease);
                    controls.target.lerpVectors(startTarget, targetTarget, ease);
                    
                    // Lerp Up vector
                    camera.up.lerpVectors(startUp, targetUp, ease).normalize();

                    controls.update();

                    if (t < 1) {
                        this.iges.transitionAnimId = requestAnimationFrame(animate);
                    } else {
                        // Ensure final values
                        camera.position.copy(targetPos);
                        controls.target.copy(targetTarget);
                        camera.up.copy(targetUp);
                        controls.update();
                        
                        this.iges.transitionAnimId = null;
                        if (this.clipping.enabled) this._updateMaterialsWithClipping();
                        if (onComplete) onComplete();
                    }
                };
                this.iges.transitionAnimId = requestAnimationFrame(animate);
            },

            updatePartOpacity() {
                const {
                    rootModel,
                    THREE
                } = this.iges;
                if (!this.selectedPartUuid) return;

                const target = rootModel.getObjectByProperty('uuid', this.selectedPartUuid);
                if (!target) return;

                // Kita ubah material highlight yang sedang aktif
                if (target.material) {
                    target.material.transparent = true; // Wajib true
                    target.material.opacity = this.partOpacity;
                    target.material.depthWrite = this.partOpacity > 0.5; // Trik agar rendering urutannya benar
                    target.material.needsUpdate = true;
                }
            },

            updateExplode() {
                const {
                    rootModel,
                    THREE
                } = this.iges;
                if (!rootModel) return;

                // A. Caching Data Awal (Hanya sekali saat pertama kali dijalankan)
                if (!this.iges.originalPositions) {
                    this.iges.originalPositions = new Map();

                    // 1. Hitung Pusat Global Assembly (Titik Tengah Total)
                    const globalBox = new THREE.Box3().setFromObject(rootModel);
                    this.iges.center = new THREE.Vector3();
                    globalBox.getCenter(this.iges.center);

                    rootModel.traverse(child => {
                        if (child.isMesh) {
                            this.iges.originalPositions.set(child.uuid, child.position.clone());

                            if (!child.geometry.boundingBox) child.geometry.computeBoundingBox();
                            const meshCenter = new THREE.Vector3();
                            child.geometry.boundingBox.getCenter(meshCenter);
                            meshCenter.applyMatrix4(child.matrixWorld);

                            const direction = new THREE.Vector3().subVectors(meshCenter, this.iges.center).normalize();

                            // Simpan di userData agar tidak perlu hitung ulang terus
                            child.userData.explodeDirection = direction;
                        }
                    });
                }

                // B. Eksekusi Gerakan
                const scalar = this.explode.value * 2.0; // Faktor jarak (Naikkan biar lebih terasa)

                rootModel.traverse(child => {
                    if (child.isMesh && this.iges.originalPositions.has(child.uuid)) {
                        const originalPos = this.iges.originalPositions.get(child.uuid);

                        if (this.explode.value === 0 || !this.explode.enabled) {
                            // Reset ke posisi rapat
                            child.position.copy(originalPos);
                        } else {
                            // Ambil arah ledakan yang sudah kita hitung dengan akurat tadi
                            const direction = child.userData.explodeDirection;

                            if (direction) {
                                // Pindahkan: Posisi Awal + (Arah * Kekuatan Slider)
                                child.position.copy(originalPos).add(direction.clone().multiplyScalar(scalar));
                            }
                        }
                    }
                });

                // Update clipping plane agar ikut bergerak
                if (this.clipping.enabled) this._updateMaterialsWithClipping();
            },

            toggleExplode() {
                this.explode.enabled = !this.explode.enabled;
                if (this.explode.enabled) {
                    if (this.explode.value === 0) this.explode.value = 50;
                    this.explode.panelOpen = true;
                } else {
                    this.explode.panelOpen = false;
                }
                this.updateExplode();
            },

            takeScreenshot() {
                // 1. Ambil objek dari state
                let {
                    renderer,
                    scene,
                    camera
                } = this.iges;

                if (typeof Alpine !== 'undefined' && Alpine.raw) {
                    renderer = Alpine.raw(renderer);
                    scene = Alpine.raw(scene);
                    camera = Alpine.raw(camera);
                }

                if (!renderer || !scene || !camera) {
                    console.warn("Renderer/Scene belum siap.");
                    return;
                }

                try {
                    // 3. Render ulang menggunakan objek MENTAH (Raw)
                    renderer.render(scene, camera);

                    // 4. Ambil data gambar
                    const imgData = renderer.domElement.toDataURL('image/png');

                    // 5. Generate nama file
                    const rawName = this.selectedFile?.name || 'model_3d';
                    const cleanName = rawName.replace(/\.[^/.]+$/, "") || 'screenshot';
                    const fileName = `${cleanName}_view.png`;

                    // 6. Download
                    const link = document.createElement('a');
                    link.download = fileName;
                    link.href = imgData;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                } catch (e) {
                    console.error("Screenshot Error:", e);
                    // Tampilkan error ke user (gunakan fungsi toast Anda)
                    if (typeof toastError === 'function') {
                        toastError('Gagal Screenshot', 'Terjadi konflik Proxy. Pastikan Alpine.raw digunakan.');
                    } else {
                        alert('Gagal Screenshot: ' + e.message);
                    }
                }
            },

            toggleAutoRotate() {
                this.autoRotate = !this.autoRotate;
                
                if (this.autoRotate) {
                    // Start sequential view animation like E-Drawings
                    this.iges.autoViewIndex = 0;
                    this.iges.viewSequence = ['front', 'right', 'back', 'left', 'top', 'bottom', 'iso'];
                    this._playNextView();
                } else {
                    // Stop animation
                    if (this.iges.autoViewTimer) clearTimeout(this.iges.autoViewTimer);
                    if (this.iges.transitionAnimId) cancelAnimationFrame(this.iges.transitionAnimId);
                    this.iges.transitionAnimId = null;
                }
            },

            _playNextView() {
                if (!this.autoRotate) return;
                
                const views = this.iges.viewSequence;
                const view = views[this.iges.autoViewIndex];
                
                // Advance index for next turn
                this.iges.autoViewIndex = (this.iges.autoViewIndex + 1) % views.length;

                // Use slower duration (2500ms) for smoother auto-play
                this.setStandardView(view, 2500);
                
                // Wait for animation (2500ms) + Pause (1000ms) before next view
                this.iges.autoViewTimer = setTimeout(() => {
                    this._playNextView();
                }, 3500); 
            },

            toggleHeadlight() {
                this.headlight.enabled = !this.headlight.enabled;
                const {
                    camera,
                    THREE
                } = this.iges;

                const rawCamera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(camera) : camera;
                if (!rawCamera) return;

                if (this.headlight.enabled) {
                    if (!this.headlight.object) {
                        // Revert to SpotLight (User Preference) but with UNLIMITED distance fix
                        const spot = new THREE.SpotLight(0xffffee, 2.5);

                        spot.position.set(0, 0, 0);
                        spot.target.position.set(0, 0, -1); // Face forward
                        spot.angle = 0.6; // Original angle
                        spot.penumbra = 1.0; // Original softness
                        spot.decay = 0;
                        spot.distance = 0; // CRITICAL FIX: Unlimited distance

                        this.headlight.object = spot;
                    }

                    // Attach Light dan Target ke Kamera
                    rawCamera.add(this.headlight.object);
                    if (this.headlight.object.target) {
                        rawCamera.add(this.headlight.object.target);
                    }
                } else {
                    if (this.headlight.object) {
                        rawCamera.remove(this.headlight.object);
                        if (this.headlight.object.target) {
                            rawCamera.remove(this.headlight.object.target);
                        }
                    }
                }

                this._forceRender();
            },
            toggle2DFullscreen() {
    const el = this.$refs.container2D;

    if (!document.fullscreenElement) {
        if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        }
        // State is2DFullscreen akan diupdate via event listener di init()
    } else {
        document.exitFullscreen();
    }
},

            toggleFullscreen() {
                // Ganti target ke container paling luar yang mencakup toolbar
                const el = this.$refs.cadContainer;

                if (!document.fullscreenElement) {
                    el.requestFullscreen().then(() => {
                        this.isFullscreen = true;
                    }).catch(err => {
                        console.error(`Error fullscreen: ${err.message}`);
                        // Fallback untuk Safari/browser lama jika perlu
                        if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
                    });
                } else {
                    document.exitFullscreen().then(() => {
                        this.isFullscreen = false;
                    });
                }
            },

            calculateGeoProperties(mesh) {
                if (!mesh || !mesh.geometry) return;

                const {
                    THREE
                } = this.iges;
                const geom = mesh.geometry;

                // Hitung Volume (Signed Volume of Triangles)
                let vol = 0;
                // Hitung Area
                let area = 0;

                // Pastikan ada index/position
                const pos = geom.attributes.position;
                const index = geom.index;

                if (pos && index) {
                    const p1 = new THREE.Vector3(),
                        p2 = new THREE.Vector3(),
                        p3 = new THREE.Vector3();
                    for (let i = 0; i < index.count; i += 3) {
                        // Ambil 3 titik segitiga
                        p1.fromBufferAttribute(pos, index.getX(i));
                        p2.fromBufferAttribute(pos, index.getX(i + 1));
                        p3.fromBufferAttribute(pos, index.getX(i + 2));

                        // Rumus Volume (Signed)
                        vol += p1.dot(p2.cross(p3)) / 6.0;

                        // Rumus Area (Cross Product / 2)
                        const edge1 = new THREE.Vector3().subVectors(p2, p1);
                        const edge2 = new THREE.Vector3().subVectors(p3, p1);
                        area += new THREE.Vector3().crossVectors(edge1, edge2).length() * 0.5;
                    }
                }

                // Konversi ke satuan yang enak dibaca (cm3 / mm3)
                // Asumsi unit CAD adalah milimeter
                this.partInfo.volume = Math.abs(vol).toFixed(2) + ' mm³';
                this.partInfo.area = area.toFixed(2) + ' mm²';
            },



            handleShortcut(e) {
                // 1. Ignore if typing in Input/Textarea
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;

                // 2. Ignore if no 3D model is loaded
                if (!this.iges || !this.iges.rootModel) return;

                switch (e.key.toLowerCase()) {
                    // --- Standard Views ---
                    case 'f': this.setStandardView('front'); break;
                    case 't': this.setStandardView('top'); break;
                    case 'r': this.setStandardView('right'); break;
                    case 'l': this.setStandardView('left'); break;
                    case 'b': this.setStandardView('back'); break;
                    case 'd': this.setStandardView('bottom'); break;
                    case 'i': this.setStandardView('iso'); break;

                    // --- Tools ---
                    case ' ': // Auto Rotate
                        e.preventDefault();
                        this.toggleAutoRotate();
                        break;
                    case 'h': // Headlight
                        this.toggleHeadlight();
                        break;
                    case 'c': // Camera Mode
                        this.toggleCameraMode();
                        break;
                    case 'x': // Explode
                        this.toggleExplode();
                        break;
                    case 'm': // Measure
                        this.toggleMeasure();
                        break;
                    case 's': // Section Cut
                        this.clipping.panelOpen = !this.clipping.panelOpen;
                        break;
                    
                    // --- Navigation ---
                    case 'home':
                        e.preventDefault();
                        this.resetCamera3d();
                        break;
                    case '=':
                    case '+':
                        e.preventDefault();
                        this.zoom3d(1.1);
                        break;
                    case '-':
                    case '_':
                        e.preventDefault();
                        this.zoom3d(0.9);
                        break;
                }
            },

            setMaterialMode(mode) {
                const {
                    rootModel,
                    THREE
                } = this.iges;
                if (!rootModel) return;

                this.activeMaterial = mode;

                // 1. Reset ke material asli dulu (supaya bersih)
                this._restoreMaterials(rootModel);

                // 2. Jika mode 'default', berhenti di sini (sudah di-reset)
                if (mode === 'default') {
                    // Jangan lupa update clipping agar tidak hilang saat reset
                    if (this.hasActiveClipping) this._updateMaterialsWithClipping();
                    return;
                }

                // 3. Siapkan Material Baru berdasarkan Mode
                let newMat;
                const commonProps = {
                    side: THREE.DoubleSide,
                    clippingPlanes: (this.clipping.enabled && this.clipping.plane) ? [this.clipping.plane] : [],
                    clipShadows: true
                };

                if (mode === 'clay') {
                    // Tampilan Tanah Liat (Matte, mudah melihat bentuk)
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xdddddd, // Putih abu
                        roughness: 1.0, // Kasar (tidak memantul)
                        metalness: 0.0
                    });
                } else if (mode === 'metal') {
                    // Tampilan Logam (Chrome)
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xffffff,
                        roughness: 0.2, // Licin
                        metalness: 1.0 // Logam murni
                    });
                } else if (mode === 'normal') {
                    // Tampilan Normal (Warna-warni berdasarkan arah)
                    newMat = new THREE.MeshNormalMaterial({
                        ...commonProps
                        // MeshNormalMaterial tidak butuh color/roughness
                    });
                } else if (mode === 'glass') {
                    // Tampilan Kaca/Akrilik Transparan
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xffffff,
                        metalness: 0.1,
                        roughness: 0.1,
                        transparent: true,
                        opacity: 0.3, // 30% terlihat, 70% tembus
                        depthWrite: false // Agar rendering urutan transparansi benar
                    });
                } else if (mode === 'ecoat') {
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0x757a75, // Abu-abu kehijauan (Olive Grey)
                        roughness: 0.7, // Matte / Doff (tidak mantul)
                        metalness: 0.1 // Sedikit sifat logam
                    });
                }

                // 2. COLD ROLLED STEEL (BAJA MENTAH)
                // Warna dasar plat body sebelum diapa-apakan.
                else if (mode === 'steel') {
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xc0c6c9, // Abu-abu dingin (kebiruan dikit)
                        roughness: 0.4, // Semi-mengkilap (permukaan oli tipis)
                        metalness: 0.8 // Sangat logam
                    });
                }

                // 3. YELLOW ZINC (PENGGANTI GOLD)
                // Untuk Baut, Mur, Bracket, Clamp.
                else if (mode === 'zinc') {
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xd4af37, // Kuning Zinc (lebih pudar dari emas)
                        roughness: 0.5,
                        metalness: 0.6
                    });
                }

                // 4. ALUMINUM (CASTING/BLOCK)
                // Untuk blok mesin atau velg. Lebih putih dari baja.
                else if (mode === 'aluminum') {
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xffffff, // Putih terang
                        roughness: 0.5, // Agak kasar (kulit jeruk casting)
                        metalness: 0.7
                    });
                }

                // 5. RED OXIDE (CHASSIS PRIMER)
                // Primer merah bata.
                else if (mode === 'redox') {
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0x803020, // Merah Bata Gelap
                        roughness: 0.9, // Sangat matte
                        metalness: 0.0
                    });
                } else if (mode === 'dark') {
                    // Tampilan Plastik Hitam / Besi Cor
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0x222222, // Hampir hitam
                        roughness: 0.6, // Agak kasar
                        metalness: 0.2
                    });
                }

                // 4. Terapkan Material ke Semua Mesh
                rootModel.traverse(o => {
                    if (o.isMesh) {
                        o.material = newMat;
                    }
                });

                // 5. UPDATE: Terapkan clipping planes ke material baru
                if (this.hasActiveClipping) {
                    this._updateMaterialsWithClipping();
                }

                // Force render to prevent freeze
                this._forceRender();
            },

            // NEW: Set display style (Shaded / Edges / Wireframe)
            setDisplayStyle(style) {
                const { rootModel, THREE } = this.iges;
                if (!rootModel) return;

                this.currentStyle = style;

                rootModel.traverse(o => {
                    if (o.isMesh && o.material) {
                        const materials = Array.isArray(o.material) ? o.material : [o.material];

                        materials.forEach(mat => {
                            if (style === 'wireframe') {
                                // Wireframe only
                                mat.wireframe = true;
                            } else if (style === 'shaded-edges') {
                                // Shaded with edges - need to add EdgesGeometry
                                mat.wireframe = false;

                                // Remove existing edges if any
                                const existingEdges = o.children.find(child => child.userData.isEdgesHelper);
                                if (existingEdges) {
                                    o.remove(existingEdges);
                                }

                                // Add new edges
                                const edges = new THREE.EdgesGeometry(o.geometry, 30); // 30 degree threshold
                                const line = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({
                                    color: 0x000000,
                                    linewidth: 1
                                }));
                                line.userData.isEdgesHelper = true;
                                o.add(line);
                            } else {
                                // Shaded only (default)
                                mat.wireframe = false;

                                // Remove edges if any
                                const existingEdges = o.children.find(child => child.userData.isEdgesHelper);
                                if (existingEdges) {
                                    o.remove(existingEdges);
                                }
                            }

                            mat.needsUpdate = true;
                        });
                    }
                });

                // CRITICAL: Re-apply clipping planes after style change
                if (this.hasActiveClipping) {
                    this._updateMaterialsWithClipping();
                }

                // Force render to prevent freeze
                this._forceRender();
            },

            // Helper: Force a single render frame (useful after view changes)
            _forceRender() {
                try {
                    const { renderer, scene, camera } = this.iges;
                    if (!renderer || !scene || !camera) return;

                    const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(renderer) : renderer;
                    const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
                    const rawCamera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(camera) : camera;

                    if (rawRenderer && rawScene && rawCamera) {
                        rawRenderer.render(rawScene, rawCamera);
                    }
                } catch (error) {
                    console.error('Force render error:', error);
                }
            },

            // Helper: Create professional snap marker (small cursor for measurement picking)
            _createSnapMarker(position, color = 0xffff00, size = 1.2) {
                const { THREE } = this.iges;
                if (!THREE) return null;

                const group = new THREE.Group();

                // 1. Thin outer ring - hollow and small
                const outerRingGeometry = new THREE.RingGeometry(size * 0.85, size, 20);
                const outerRingMaterial = new THREE.MeshBasicMaterial({
                    color: color,
                    side: THREE.DoubleSide,
                    transparent: true,
                    opacity: 0.7,
                    depthTest: false
                });
                const outerRing = new THREE.Mesh(outerRingGeometry, outerRingMaterial);
                outerRing.renderOrder = 1001;
                group.add(outerRing);

                // 2. Tiny center dot - for precision
                const dotGeometry = new THREE.CircleGeometry(size * 0.12, 8);
                const dotMaterial = new THREE.MeshBasicMaterial({
                    color: color,
                    side: THREE.DoubleSide,
                    transparent: true,
                    opacity: 0.9,
                    depthTest: false
                });
                const dot = new THREE.Mesh(dotGeometry, dotMaterial);
                dot.renderOrder = 1002;
                group.add(dot);

                // 3. Crosshair - thin lines for precision
                const crosshairMaterial = new THREE.LineBasicMaterial({
                    color: color,
                    transparent: true,
                    opacity: 0.8,
                    depthTest: false
                });

                // Horizontal line
                const hLineGeometry = new THREE.BufferGeometry().setFromPoints([
                    new THREE.Vector3(-size * 1.4, 0, 0),
                    new THREE.Vector3(size * 1.4, 0, 0)
                ]);
                const hLine = new THREE.Line(hLineGeometry, crosshairMaterial);
                hLine.renderOrder = 1001;
                group.add(hLine);

                // Vertical line
                const vLineGeometry = new THREE.BufferGeometry().setFromPoints([
                    new THREE.Vector3(0, -size * 1.4, 0),
                    new THREE.Vector3(0, size * 1.4, 0)
                ]);
                const vLine = new THREE.Line(vLineGeometry, crosshairMaterial);
                vLine.renderOrder = 1001;
                group.add(vLine);

                // Position the marker
                group.position.copy(position);

                // Make it always face camera
                group.userData.update = () => {
                    const camera = this.iges.camera;
                    if (camera) {
                        const rawCamera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(camera) : camera;
                        group.quaternion.copy(rawCamera.quaternion);
                    }
                };

                return group;
            },

            // Helper: Update snap marker position
            _updateSnapMarker(position, color = 0xffff00) {
                const { scene } = this.iges;
                if (!scene) return;

                // Remove old marker
                if (this.snapMarker) {
                    const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
                    const rawMarker = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.snapMarker) : this.snapMarker;
                    rawScene.remove(rawMarker);
                }

                // Create new marker at position
                if (position) {
                    this.snapMarker = this._createSnapMarker(position, color, 1.2);
                    const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
                    rawScene.add(this.snapMarker);
                }
            },

            // Helper: Hide snap marker
            _hideSnapMarker() {
                if (this.snapMarker) {
                    const { scene } = this.iges;
                    if (scene) {
                        const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
                        const rawMarker = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.snapMarker) : this.snapMarker;
                        rawScene.remove(rawMarker);
                    }
                    this.snapMarker = null;
                }
            },

            formatRevisionForSelect2(revision) {
                if (!revision.id) {
                    return revision.text;
                }

                let badgesHtml = '';
                if (revision.is_latest) {
                    badgesHtml += '<span style="background-color: #DBEAFE; color: #2563EB; font-size: 0.75em; font-weight: 600; margin-left: 8px; padding: 2px 6px; border-radius: 99px;">LATEST</span>';
                }
                if (revision.is_obsolete) {
                    badgesHtml += '<span style="background-color: #FECACA; color: #DC2626; font-size: 0.75em; font-weight: 600; margin-left: 8px; padding: 2px 6px; border-radius: 99px;">OBSOLETE</span>';
                }
                return $(`<span>${revision.text}</span>`).append(badgesHtml);
            },

            /* ===== Lifecycle ===== */
            init() {
                const fsChangeHandler = () => {
        this.is2DFullscreen = !!document.fullscreenElement;
        
        // Paksa render ulang untuk memperbaiki bug 'double' pada beberapa browser
        this.$nextTick(() => {
            window.dispatchEvent(new Event('resize'));
        });
    };

    document.addEventListener('fullscreenchange', fsChangeHandler);
    document.addEventListener('webkitfullscreenchange', fsChangeHandler);
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {}
                });
                window.addEventListener('beforeunload', () => this.disposeCad());

                const sel = $(this.$refs.revisionSelector);
                this.revisionSelect2 = sel.select2({
                    data: this.revisionList,
                    templateResult: this.formatRevisionForSelect2.bind(this),
                    templateSelection: this.formatRevisionForSelect2.bind(this),
                    width: '100%',
                    dropdownCssClass: 'select2-dropdown-tailwind',
                    containerCssClass: 'select2-container-tailwind'
                });

                const defaultValue = this.revisionList.length > 0 ? this.revisionList[0].id : this.exportId;
                this.selectedRevisionId = defaultValue;
                sel.val(defaultValue).trigger('change.select2');

                sel.on('change', (e) => {
                    const newValue = e.target.value;
                    if (newValue !== this.selectedRevisionId) {
                        this.selectedRevisionId = newValue;
                        this.onRevisionChange();
                    }
                });

                this.$watch('isLoadingRevision', (isLoading) => {
                    sel.prop('disabled', isLoading);
                });

                this.$watch('selectedRevisionId', (newId) => {
                    if (sel.val() !== newId) {
                        sel.val(newId).trigger('change.select2');
                    }
                });

                // Auto-enable snap when measurement mode is activated
                this.$watch('iges.measure.enabled', (isEnabled) => {
                    if (isEnabled && this.iges.measure.snap) {
                        this.iges.measure.snap.enabled = true;
                    }
                });

                this.$watch('iges.measure.enabled', (isEnabled) => {
                    if (isEnabled && this.iges.measure.snap) {
                        this.iges.measure.snap.enabled = true;
                    }
                });
            },

            /* ===== UI ===== */
            toggleSection(c) {
                const i = this.openSections.indexOf(c);
                if (i > -1) this.openSections.splice(i, 1);
                else this.openSections.push(c);
            },

            selectFile(file) {
                if (this.selectedFile && this.getFileKey(this.selectedFile) === this.getFileKey(file)) return;

                if (this.selectedFile) this.saveStampConfigForCurrent();
                const currentId = ++this.activeLoadId;

                this.tifLoading = false;
                this.tifError = '';
                this.hpglLoading = false;
                this.hpglError = '';
                this.pdfLoading = false;
                this.pdfError = '';
                this.imgLoading = false;

                if (this.isCad(this.selectedFile?.name)) this.disposeCad();

                if (this.isTiff(this.selectedFile?.name)) {
                    this.tifError = '';
                    this.tifLoading = false;
                    this.tifIfds = [];
                    this.tifDecoder = null;
                    this.tifPageNum = 1;
                    this.tifNumPages = 1;
                    if (this.$refs.tifImg) this.$refs.tifImg.src = '';
                }


                if (this.isHpgl(this.selectedFile?.name)) {
                    this.hpglError = '';
                    this.hpglLoading = false;
                    if (this.$refs.hpglCanvas) {
                        const c = this.$refs.hpglCanvas;
                        const ctx = c.getContext('2d');
                        ctx && ctx.clearRect(0, 0, c.width, c.height);
                    }
                }
                if (this.isPdf(this.selectedFile?.name)) {
                    this.pdfError = '';
                    this.pdfLoading = false;
                    pdfDoc = null;
                    if (this.$refs.pdfCanvas) {
                        const c = this.$refs.pdfCanvas;
                        const ctx = c.getContext('2d');
                        ctx && ctx.clearRect(0, 0, c.width, c.height);
                    }
                }

                this.imageZoom = 1;
                this.panX = 0;
                this.panY = 0;

                this.selectedFile = {
                    ...file
                };
                this.loadStampConfigFor(this.selectedFile);

                this.selectedFile = {
                    ...file
                };
                this.loadStampConfigFor(this.selectedFile);

                this.$nextTick(() => {
                    if (this.activeLoadId !== currentId) return;

                    if (this.isTiff(file?.name)) {
                        this.tifLoading = true;
                        this.renderTiff(file.url, currentId);
                    } else if (this.isCad(file?.name)) {
                        // Route to appropriate renderer based on format
                        if (this.isOcctFormat(file?.name)) {
                            this.renderCadOcct(file);
                        } else if (this.isThreeFormat(file?.name)) {
                            this.renderCadThree(file);
                        }
                    } else if (this.isHpgl(file?.name)) {
                        this.hpglLoading = true;
                        this.renderHpgl(file.url, currentId);
                    } else if (this.isPdf(file?.name)) {
                        this.pdfLoading = true;
                        this.renderPdf(file.url, currentId);
                    } else if (this.isImage(file?.name)) {
                        this.imgLoading = true;

                        const img = this.$refs.mainImage;
                        if (img && img.complete && img.naturalWidth > 0) {
                            this.imgLoading = false;
                        }
                    }
                });
            },

            onRevisionChange() {
                if (this.selectedRevisionId === this.exportId) {
                    return;
                }

                this.isLoadingRevision = true;
                this.selectedFile = null;
                this.disposeCad();
                this.tifError = '';
                this.tifLoading = false;
                this.hpglError = '';
                this.hpglLoading = false;
                this.pdfError = '';
                this.pdfLoading = false;
                pdfDoc = null;

                const routeUrl = `/api/export/revision-detail/${this.selectedRevisionId}`;

                fetch(routeUrl, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw new Error(err.message || `Error ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.pkg = data.pkg;
                            this.exportId = data.exportId;
                            this.stampFormat = data.stampFormat || null;
                            let displayText = data.pkg.metadata.revision;
                            if (data.pkg.metadata.revision_label) {
                                displayText += ` (${data.pkg.metadata.revision_label})`;
                            }
                            if (typeof data.isEngineering !== 'undefined') {
                                this.isEngineering = data.isEngineering;
                            }
                            toastSuccess('Revision Loaded', `Displaying ${displayText}.`);
                        } else {
                            throw new Error(data.message || 'Failed to load revision data.');
                        }
                    })
                    .catch(error => {
                        console.error('Failed to load revision:', error);
                        toastError('Load Failed', error.message);
                        this.selectedRevisionId = this.exportId;
                    })
                    .finally(() => {
                        this.isLoadingRevision = false;
                    });
            },

            /* ===== Download ===== */
            _safeDownload(url) {
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', ''); // Hint to browser to download
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                setTimeout(() => {
                    document.body.removeChild(link);
                }, 100);
            },

            downloadFile(file) {
                if (!file || !file.file_id) {
                    toastError('Error', 'File ID not found.');
                    return;
                }
                this._safeDownload(`/download/file/${file.file_id}`);
            },

            downloadPackage() {
                if (!this.exportId) {
                    toastError('Error', 'Package ID not found for download.');
                    return;
                }

                const packageIdToDownload = this.exportId;
                const t = detectTheme();

                Swal.fire({
                    title: 'Confirm Download',
                    text: "This package will be prepared on the server first. Do you want to continue?",
                    icon: 'info',
                    iconColor: t.icon.info,
                    background: t.bg,
                    color: t.fg,
                    customClass: {
                        popup: 'swal2-popup border'
                    },
                    didOpen: (popup) => {
                        const p = popup.querySelector('.swal2-popup');
                        if (p) p.style.borderColor = t.border;
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Prepare It!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#6b7280',
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    if (this._downloadAbortController) {
                        this._downloadAbortController.abort('New download started');
                    }
                    this._downloadAbortController = new AbortController();
                    const signal = this._downloadAbortController.signal;

                    const t_prep = detectTheme();
                    Swal.fire({
                        title: 'Preparing your file...',
                        text: 'This may take a moment. Please wait.',
                        icon: 'info',
                        iconColor: t_prep.icon.info,
                        background: t_prep.bg,
                        color: t_prep.fg,
                        customClass: {
                            popup: 'swal2-popup border'
                        },
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
                        showConfirmButton: false,
                        didOpen: (popup) => {
                            Swal.showLoading();
                            const p = popup.querySelector('.swal2-popup');
                            if (p) p.style.borderColor = t_prep.border;
                        },
                    }).then((modalResult) => {
                        if (modalResult.dismiss === Swal.DismissReason.cancel) {
                            if (this._downloadAbortController) {
                                this._downloadAbortController.abort('User canceled preparing');
                            }
                        }
                    });

                    fetch(`/api/export/prepare-zip/${packageIdToDownload}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            signal: signal
                        })
                        .then(response => {
                            if (signal.aborted) {
                                throw new Error('Aborted');
                            }
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw new Error(err.message || 'Server error. Could not prepare file.');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (signal.aborted) return;
                            this._downloadAbortController = null;

                            if (data.success && data.download_url) {
                                const t_ready = detectTheme();
                                Swal.fire({
                                    title: 'File is Ready!',
                                    text: `Your file (${data.file_name}) has been prepared.`,
                                    icon: 'success',
                                    iconColor: t_ready.icon.success,
                                    background: t_ready.bg,
                                    color: t_ready.fg,
                                    customClass: {
                                        popup: 'swal2-popup border'
                                    },
                                    didOpen: (popup) => {
                                        const p = popup.querySelector('.swal2-popup');
                                        if (p) p.style.borderColor = t_ready.border;
                                    },
                                    confirmButtonText: '<i class="fa-solid fa-download mr-1"></i> Download Now',
                                    confirmButtonColor: '#28a745',
                                    allowOutsideClick: false,
                                    showCancelButton: true,
                                    cancelButtonText: 'Close',
                                    cancelButtonColor: '#6b7280',
                                }).then((dlResult) => {
                                    if (dlResult.isConfirmed) {
                                        this._safeDownload(data.download_url);
                                    }
                                });
                            } else {
                                throw new Error(data.message || 'Failed to prepare file response.');
                            }
                        })
                        .catch(error => {
                            if (signal.aborted || error.name === 'AbortError' || error.message === 'Aborted' || error === 'Aborted') {
                                console.log('Download canceled by user.');
                                const t_cancel = detectTheme();
                                BaseToast.fire({
                                    icon: 'info',
                                    title: 'Canceled',
                                    text: 'Download preparation canceled.',
                                    background: t_cancel.bg,
                                    color: t_cancel.fg
                                });
                                return;
                            }

                            this._downloadAbortController = null;
                            const t_err = detectTheme();
                            Swal.fire({
                                title: 'An Error Occurred',
                                text: error.message || 'Could not prepare the file. Please try again.',
                                icon: 'error',
                                iconColor: t_err.icon.error,
                                background: t_err.bg,
                                color: t_err.fg,
                                customClass: {
                                    popup: 'swal2-popup border'
                                },
                                didOpen: (popup) => {
                                    const p = popup.querySelector('.swal2-popup');
                                    if (p) p.style.borderColor = t_err.border;
                                },
                                confirmButtonColor: '#2563eb',
                            });
                        });
                });
            },

            /* ===== render CAD via occt-import-js ===== */
            async renderCadOcct(fileObj) {
                const url = fileObj?.url;
                if (!url) return;
                this.disposeCad();
                this.iges.loading = true;
                this.iges.error = '';

                try {
                    const THREE = await import('three');
                    const {
                        OrbitControls
                    } = await import('three/addons/controls/OrbitControls.js');
                    const bvh = await import('three-mesh-bvh');
                    THREE.Mesh.prototype.raycast = bvh.acceleratedRaycast;
                    THREE.BufferGeometry.prototype.computeBoundsTree = bvh.computeBoundsTree;
                    THREE.BufferGeometry.prototype.disposeBoundsTree = bvh.disposeBoundsTree;

                    // scene & camera
                    const scene = new THREE.Scene();
                    scene.background = null;
                    const wrap = this.$refs.igesWrap;
                    const width = wrap?.clientWidth || 800,
                        height = wrap?.clientHeight || 500;

                    const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 10000);
                    camera.position.set(250, 200, 250);

                    const renderer = new THREE.WebGLRenderer({
                        antialias: true,
                        alpha: true,
                        preserveDrawingBuffer: true,
                        powerPreference: 'high-performance'
                    });
                    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
                    renderer.setSize(width, height);
                    renderer.localClippingEnabled = true;
                    wrap.appendChild(renderer.domElement);
                    wrap.style.position = 'relative';
                    wrap.style.overflow = 'hidden';

                    // lights
                    const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
                    scene.add(ambientLight);
                    scene.add(camera);

                    const keyLight = new THREE.DirectionalLight(0xffffff, 0.7);
                    keyLight.position.set(50, 50, 100);
                    camera.add(keyLight);

                    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
                    fillLight.position.set(-50, -50, 100);
                    camera.add(fillLight);

                    // controls
                    const controls = new OrbitControls(camera, renderer.domElement);
                    controls.enableDamping = true;

                    const resp = await fetch(url, {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error('Failed to fetch CAD file');
                    const mainBuf = new Uint8Array(await resp.arrayBuffer());

                    console.log('CAD size (bytes):', mainBuf.byteLength);

                    const occt = await window.occtimportjs();
                    // Use fileObj.name for extension detection (more reliable than URL which may be encoded)
                    const fileName = fileObj?.name || '';
                    const ext = this.extOf(fileName);
                    console.log('CAD file name:', fileName, '| Detected extension:', ext);

                    const params = {
                        linearUnit: 'millimeter',
                        linearDeflectionType: 'bounding_box_ratio',
                        linearDeflection: 0.1,
                        angularDeflection: 0.1,
                    };

                    let res = null;

                    if (ext === 'stp' || ext === 'step') {
                        console.log('Attempting to parse as STEP file...');
                        res = occt.ReadStepFile(mainBuf, params);
                        console.log('OCCT STEP result:', res);

                        if (!res || !res.success) {
                            console.warn('STEP failed, trying IGES fallback...');
                            const igesFile = this._findIgesSibling(fileObj);
                            if (igesFile?.url) {
                                console.log('Fallback IGES file:', igesFile.name, igesFile.url);
                                const igResp = await fetch(igesFile.url, {
                                    cache: 'no-store',
                                    credentials: 'same-origin'
                                });
                                if (!igResp.ok) throw new Error('Failed to fetch fallback IGES file');
                                const igBuf = new Uint8Array(await igResp.arrayBuffer());
                                res = occt.ReadIgesFile(igBuf, params);
                                console.log('OCCT IGES fallback result:', res);
                            }
                        }
                    } else if (ext === 'brep') {
                        // BREP format - native OpenCASCADE format
                        res = occt.ReadBrepFile(mainBuf, params);
                        console.log('OCCT BREP result:', res);
                    } else {
                        // Default to IGES for .igs, .iges files
                        res = occt.ReadIgesFile(mainBuf, params);
                        console.log('OCCT IGES result:', res);
                    }

                    if (!res || !res.success) {
                        const msg = res?.error || res?.message || 'File is not a valid STEP/IGES/BREP or is not supported by OCCT.';
                        throw new Error('OCCT failed to parse file: ' + msg);
                    }

                    const group = this._buildThreeFromOcct(res, THREE);
                    scene.add(group); // Pastikan ini sudah ada sebelumnya

                    // simpan refs
                    this.iges.rootModel = group;
                    this.iges.scene = scene;
                    this.iges.camera = camera;
                    this.iges.renderer = renderer;
                    this.iges.controls = controls;
                    this.iges.THREE = THREE;

                    // cache material asli
                    this._cacheOriginalMaterials(group, THREE);


                    // 1. HITUNG BOUNDING BOX AWAL
                    const box = new THREE.Box3().setFromObject(group);
                    const size = new THREE.Vector3();
                    box.getSize(size);
                    const center = new THREE.Vector3();
                    box.getCenter(center);

                    // 2. GESER BENDA KE TITIK NOL (0,0,0) - PENTING UNTUK CLIPPING
                    group.position.sub(center);

                    // 3. ATUR RANGE SLIDER CLIPPING (Berdasarkan ukuran benda PER AXIS)
                    const maxDim = Math.max(size.x, size.y, size.z) || 100;

                    // Set range per-axis untuk kontrol yang lebih presisi
                    const buffer = 1.1; // 10% buffer
                    this.clipping.min = -Math.ceil(maxDim * buffer / 2);
                    this.clipping.max = Math.ceil(maxDim * buffer / 2);

                    // Store individual axis ranges for better control
                    this.clipping.x.min = -Math.ceil(size.x * buffer / 2);
                    this.clipping.x.max = Math.ceil(size.x * buffer / 2);
                    this.clipping.y.min = -Math.ceil(size.y * buffer / 2);
                    this.clipping.y.max = Math.ceil(size.y * buffer / 2);
                    this.clipping.z.min = -Math.ceil(size.z * buffer / 2);
                    this.clipping.z.max = Math.ceil(size.z * buffer / 2);

                    // 4. ATUR KAMERA (AUTO-FIT)
                    // Karena benda sudah digeser ke (0,0,0), target kontrol sekarang adalah (0,0,0)
                    const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
                    const viewDirection = new THREE.Vector3(1, 1, 1).normalize(); // Sudut pandang isometrik

                    camera.position.copy(viewDirection.multiplyScalar(fitDist * 1.6));
                    camera.near = 0.1;
                    camera.far = 100000;
                    camera.updateProjectionMatrix();

                    controls.target.set(0, 0, 0); // Target selalu di tengah
                    controls.update();

                    // default style
                    this.setDisplayStyle('shaded-edges');

                    this._updateMaterialsWithClipping();

                    const animate = () => {
                        try {
                            controls.update();
                            const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(renderer) : renderer;
                            const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;

                            let activeCam = this.iges.camera;
                            if (typeof Alpine !== 'undefined' && Alpine.raw) {
                                activeCam = Alpine.raw(activeCam);
                            }

                            if (activeCam && rawRenderer && rawScene) {
                                rawRenderer.render(rawScene, activeCam);
                            }

                            // Update measurement labels
                            const g = this.iges.measure.group;
                            if (g) {
                                const rawGroup = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(g) : g;
                                if (rawGroup && rawGroup.children) {
                                    rawGroup.children.forEach(ch => ch.userData?.update?.());
                                }
                            }

                            this.iges.animId = requestAnimationFrame(animate);
                        } catch (error) {
                            console.error('Animation loop error:', error);
                            // Try to restart animation loop after error
                            this.iges.animId = requestAnimationFrame(animate);
                        }
                    };
                    animate();

                    // resize
                    const resizeObserver = new ResizeObserver(() => {
                        // 1. Cek ukuran wadah pembungkus saat ini
                        const w = wrap.clientWidth;
                        const h = wrap.clientHeight;

                        // Safety check: Jangan update jika elemen tersembunyi/kecil
                        if (w === 0 || h === 0) return;

                        // 2. Update Kamera (Agar aspek rasio tidak gepeng)
                        // Cek dulu apakah camera punya method update (karena bisa jadi null saat awal)
                        if (camera && camera.updateProjectionMatrix) {
                            // Logika khusus: Orthographic vs Perspective butuh rumus beda
                            if (camera.isOrthographicCamera) {
                                // Update frustum untuk Orthographic
                                // Kita perlu hitung ulang frustum berdasarkan zoom/dist yang ada
                                // TAPI, cara paling aman untuk viewer sederhana adalah sekedar update aspek:
                                const aspect = w / h;

                                // Ambil size frustum lama (trik sederhana)
                                const frustumHeight = (camera.top - camera.bottom);
                                const frustumWidth = frustumHeight * aspect;

                                camera.left = -frustumWidth / 2;
                                camera.right = frustumWidth / 2;
                                camera.top = frustumHeight / 2;
                                camera.bottom = -frustumHeight / 2;
                            } else {
                                // Update aspect untuk Perspective
                                camera.aspect = w / h;
                            }
                            camera.updateProjectionMatrix();
                        }

                        // 3. Update Renderer (Ubah ukuran canvas fisik)
                        if (renderer) {
                            renderer.setSize(w, h);
                            // Paksa render ulang frame baru agar tidak flickering hitam
                            // Gunakan Alpine.raw untuk keamanan
                            const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(renderer) : renderer;
                            const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
                            const rawCam = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(camera) : camera;
                            rawRenderer.render(rawScene, rawCam);
                        }
                    });

                    // Mulai pantau wadah pembungkus
                    resizeObserver.observe(wrap);

                    // Simpan reference untuk dibersihkan nanti saat dispose
                    this._resizeObserver = resizeObserver;

                } catch (e) {
                    console.error(e);
                    this.iges.error = e?.message || 'Failed to render CAD file';
                } finally {
                    this.iges.loading = false;
                }
            },

            /* ===== render CAD via Three.js loaders (STL, OBJ, FBX, GLTF, 3DS) ===== */
            async renderCadThree(fileObj) {
                const url = fileObj?.url;
                if (!url) return;
                this.disposeCad();
                this.iges.loading = true;
                this.iges.error = '';

                try {
                    const THREE = await import('three');
                    const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');
                    const bvh = await import('three-mesh-bvh');
                    THREE.Mesh.prototype.raycast = bvh.acceleratedRaycast;
                    THREE.BufferGeometry.prototype.computeBoundsTree = bvh.computeBoundsTree;
                    THREE.BufferGeometry.prototype.disposeBoundsTree = bvh.disposeBoundsTree;

                    // scene & camera
                    const scene = new THREE.Scene();
                    scene.background = null;
                    const wrap = this.$refs.igesWrap;
                    const width = wrap?.clientWidth || 800,
                        height = wrap?.clientHeight || 500;

                    const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 10000);
                    camera.position.set(250, 200, 250);

                    const renderer = new THREE.WebGLRenderer({
                        antialias: true,
                        alpha: true,
                        preserveDrawingBuffer: true,
                        powerPreference: 'high-performance'
                    });
                    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
                    renderer.setSize(width, height);
                    renderer.localClippingEnabled = true;
                    wrap.appendChild(renderer.domElement);
                    wrap.style.position = 'relative';
                    wrap.style.overflow = 'hidden';

                    // lights
                    const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
                    scene.add(ambientLight);
                    scene.add(camera);

                    const keyLight = new THREE.DirectionalLight(0xffffff, 0.7);
                    keyLight.position.set(50, 50, 100);
                    camera.add(keyLight);

                    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
                    fillLight.position.set(-50, -50, 100);
                    camera.add(fillLight);

                    // controls
                    const controls = new OrbitControls(camera, renderer.domElement);
                    controls.enableDamping = true;

                    // Determine file extension and load accordingly
                    const ext = (url.split('?')[0].split('#')[0].split('.').pop() || '').toLowerCase();
                    let group = new THREE.Group();

                    console.log('Loading Three.js format:', ext, 'from:', url);

                    if (ext === 'stl') {
                        // STL Loader
                        const { STLLoader } = await import('three/addons/loaders/STLLoader.js');
                        const loader = new STLLoader();
                        const geometry = await new Promise((resolve, reject) => {
                            loader.load(url, resolve, undefined, reject);
                        });
                        const material = new THREE.MeshStandardMaterial({
                            color: 0xcccccc,
                            metalness: 0,
                            roughness: 1,
                            side: THREE.DoubleSide
                        });
                        const mesh = new THREE.Mesh(geometry, material);
                        mesh.name = fileObj.name || 'STL Model';
                        if (geometry.attributes.position.count > 0) geometry.computeBoundsTree();
                        group.add(mesh);
                        this.cadPartsList = [{ uuid: mesh.uuid, name: mesh.name }];
                        console.log('STL loaded successfully');

                    } else if (ext === 'obj') {
                        // OBJ Loader
                        const { OBJLoader } = await import('three/addons/loaders/OBJLoader.js');
                        const loader = new OBJLoader();
                        const obj = await new Promise((resolve, reject) => {
                            loader.load(url, resolve, undefined, reject);
                        });
                        this.cadPartsList = [];
                        obj.traverse(child => {
                            if (child.isMesh) {
                                child.material = new THREE.MeshStandardMaterial({
                                    color: 0xcccccc,
                                    metalness: 0,
                                    roughness: 1,
                                    side: THREE.DoubleSide
                                });
                                if (child.geometry && child.geometry.attributes.position.count > 0) {
                                    child.geometry.computeBoundsTree();
                                }
                                this.cadPartsList.push({ uuid: child.uuid, name: child.name || 'OBJ Part' });
                            }
                        });
                        group = obj;
                        console.log('OBJ loaded successfully');

                    } else if (ext === 'fbx') {
                        // FBX Loader
                        const { FBXLoader } = await import('three/addons/loaders/FBXLoader.js');
                        const loader = new FBXLoader();
                        const fbx = await new Promise((resolve, reject) => {
                            loader.load(url, resolve, undefined, reject);
                        });
                        this.cadPartsList = [];
                        fbx.traverse(child => {
                            if (child.isMesh) {
                                // Keep original material if available, otherwise create default
                                if (!child.material) {
                                    child.material = new THREE.MeshStandardMaterial({
                                        color: 0xcccccc,
                                        metalness: 0,
                                        roughness: 1,
                                        side: THREE.DoubleSide
                                    });
                                }
                                if (child.geometry && child.geometry.attributes.position.count > 0) {
                                    child.geometry.computeBoundsTree();
                                }
                                this.cadPartsList.push({ uuid: child.uuid, name: child.name || 'FBX Part' });
                            }
                        });
                        group = fbx;
                        console.log('FBX loaded successfully');

                    } else if (ext === 'gltf' || ext === 'glb') {
                        // GLTF/GLB Loader
                        const { GLTFLoader } = await import('three/addons/loaders/GLTFLoader.js');
                        const { DRACOLoader } = await import('three/addons/loaders/DRACOLoader.js');
                        const loader = new GLTFLoader();

                        // Setup DRACO decoder for compressed GLTF
                        const dracoLoader = new DRACOLoader();
                        dracoLoader.setDecoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/draco/');
                        loader.setDRACOLoader(dracoLoader);

                        const gltf = await new Promise((resolve, reject) => {
                            loader.load(url, resolve, undefined, reject);
                        });
                        this.cadPartsList = [];
                        gltf.scene.traverse(child => {
                            if (child.isMesh) {
                                if (child.geometry && child.geometry.attributes.position.count > 0) {
                                    child.geometry.computeBoundsTree();
                                }
                                this.cadPartsList.push({ uuid: child.uuid, name: child.name || 'GLTF Part' });
                            }
                        });
                        group = gltf.scene;
                        console.log('GLTF/GLB loaded successfully');

                    } else if (ext === '3ds') {
                        // 3DS Loader
                        const { TDSLoader } = await import('three/addons/loaders/TDSLoader.js');
                        const loader = new TDSLoader();
                        const tds = await new Promise((resolve, reject) => {
                            loader.load(url, resolve, undefined, reject);
                        });
                        this.cadPartsList = [];
                        tds.traverse(child => {
                            if (child.isMesh) {
                                if (!child.material) {
                                    child.material = new THREE.MeshStandardMaterial({
                                        color: 0xcccccc,
                                        metalness: 0,
                                        roughness: 1,
                                        side: THREE.DoubleSide
                                    });
                                }
                                if (child.geometry && child.geometry.attributes.position.count > 0) {
                                    child.geometry.computeBoundsTree();
                                }
                                this.cadPartsList.push({ uuid: child.uuid, name: child.name || '3DS Part' });
                            }
                        });
                        group = tds;
                        console.log('3DS loaded successfully');

                    } else {
                        throw new Error(`Unsupported Three.js format: ${ext}`);
                    }

                    scene.add(group);

                    // simpan refs
                    this.iges.rootModel = group;
                    this.iges.scene = scene;
                    this.iges.camera = camera;
                    this.iges.renderer = renderer;
                    this.iges.controls = controls;
                    this.iges.THREE = THREE;

                    // cache material asli
                    this._cacheOriginalMaterials(group, THREE);

                    // 1. HITUNG BOUNDING BOX AWAL
                    const box = new THREE.Box3().setFromObject(group);
                    const size = new THREE.Vector3();
                    box.getSize(size);
                    const center = new THREE.Vector3();
                    box.getCenter(center);

                    // 2. GESER BENDA KE TITIK NOL (0,0,0) - PENTING UNTUK CLIPPING
                    group.position.sub(center);

                    // 3. ATUR RANGE SLIDER CLIPPING (Berdasarkan ukuran benda PER AXIS)
                    const maxDim = Math.max(size.x, size.y, size.z) || 100;

                    // Set range per-axis untuk kontrol yang lebih presisi
                    const buffer = 1.1; // 10% buffer
                    this.clipping.min = -Math.ceil(maxDim * buffer / 2);
                    this.clipping.max = Math.ceil(maxDim * buffer / 2);

                    // Store individual axis ranges for better control
                    this.clipping.x.min = -Math.ceil(size.x * buffer / 2);
                    this.clipping.x.max = Math.ceil(size.x * buffer / 2);
                    this.clipping.y.min = -Math.ceil(size.y * buffer / 2);
                    this.clipping.y.max = Math.ceil(size.y * buffer / 2);
                    this.clipping.z.min = -Math.ceil(size.z * buffer / 2);
                    this.clipping.z.max = Math.ceil(size.z * buffer / 2);

                    // 4. ATUR KAMERA (AUTO-FIT)
                    const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
                    const viewDirection = new THREE.Vector3(1, 1, 1).normalize();

                    camera.position.copy(viewDirection.multiplyScalar(fitDist * 1.6));
                    camera.near = 0.1;
                    camera.far = 100000;
                    camera.updateProjectionMatrix();

                    controls.target.set(0, 0, 0);
                    controls.update();

                    // default style
                    this.setDisplayStyle('shaded-edges');

                    this._updateMaterialsWithClipping();

                    const animate = () => {
                        controls.update();
                        const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(renderer) : renderer;
                        const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;

                        let activeCam = this.iges.camera;
                        if (typeof Alpine !== 'undefined' && Alpine.raw) {
                            activeCam = Alpine.raw(activeCam);
                        }

                        if (activeCam) {
                            rawRenderer.render(rawScene, activeCam);
                        }
                        const g = this.iges.measure.group;
                        if (g) g.children.forEach(ch => ch.userData?.update?.());

                        this.iges.animId = requestAnimationFrame(animate);
                    };
                    animate();

                    // resize
                    const resizeObserver = new ResizeObserver(() => {
                        const w = wrap.clientWidth;
                        const h = wrap.clientHeight;

                        if (w === 0 || h === 0) return;

                        if (camera && camera.updateProjectionMatrix) {
                            if (camera.isOrthographicCamera) {
                                const aspect = w / h;
                                const frustumHeight = (camera.top - camera.bottom);
                                const frustumWidth = frustumHeight * aspect;

                                camera.left = -frustumWidth / 2;
                                camera.right = frustumWidth / 2;
                                camera.top = frustumHeight / 2;
                                camera.bottom = -frustumHeight / 2;
                            } else {
                                camera.aspect = w / h;
                            }
                            camera.updateProjectionMatrix();
                        }

                        if (renderer) {
                            renderer.setSize(w, h);
                            const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(renderer) : renderer;
                            const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
                            const rawCam = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(camera) : camera;
                            rawRenderer.render(rawScene, rawCam);
                        }
                    });

                    resizeObserver.observe(wrap);
                    this._resizeObserver = resizeObserver;

                } catch (e) {
                    console.error(e);
                    this.iges.error = e?.message || 'Failed to render 3D file';
                } finally {
                    this.iges.loading = false;
                }
            },

            /* ===== Navigation Helpers ===== */
            zoom3d(factor) {
                if (!this.iges.controls || !this.iges.camera) return;
                const controls = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.controls) : this.iges.controls;
                const camera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.camera) : this.iges.camera;
                const THREE = this.iges.THREE;

                if (camera.isOrthographicCamera) {
                    camera.zoom *= factor;
                    camera.updateProjectionMatrix();
                } else {
                    // Perspective: Move camera relative to target
                    const offset = new THREE.Vector3().subVectors(camera.position, controls.target);
                    // factor > 1 (zoom in) -> offset gets smaller (1/factor)
                    // factor < 1 (zoom out) -> offset gets larger (1/factor)
                    offset.multiplyScalar(1 / factor);
                    camera.position.addVectors(controls.target, offset);
                }
                controls.update();
            },

            resetCamera3d() {
                if (!this.iges.rootModel || !this.iges.camera || !this.iges.controls) return;
                const THREE = this.iges.THREE;
                const camera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.camera) : this.iges.camera;
                const controls = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.controls) : this.iges.controls;
                const group = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.rootModel) : this.iges.rootModel;

                // Hitung ulang bounding box untuk auto-fit
                const box = new THREE.Box3().setFromObject(group);
                const size = new THREE.Vector3();
                box.getSize(size);
                const maxDim = Math.max(size.x, size.y, size.z) || 100;

                // Jika kamera orthographic, reset zoom level
                if (camera.isOrthographicCamera) {
                    camera.zoom = 1;
                    camera.updateProjectionMatrix();
                }

                const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
                const viewDirection = new THREE.Vector3(1, 1, 1).normalize();

                camera.position.copy(viewDirection.multiplyScalar(fitDist * 1.6));
                camera.updateProjectionMatrix();
                
                controls.target.set(0, 0, 0);
                controls.update();
            },
        }
    }
</script>
@endpush
