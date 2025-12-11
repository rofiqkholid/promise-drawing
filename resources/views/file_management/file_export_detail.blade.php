@extends('layouts.app')
@section('title', 'Download Detail - File Manager')
@section('header-title', 'File Manager - Download Detail')

@section('content')
<nav class="flex px-5 py-3 mb-3 text-gray-700 bg-gray-50 shadow-sm" aria-label="Breadcrumb">
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
    @mouseleave.window="endPan()">

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


                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button @click="downloadPackage()"
                        class="inline-flex items-center text-sm px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fa-solid fa-download mr-2"></i>
                        Download All Files
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
                    <div class="flex items-center">
                        <i class="fa-solid {{$icon}} mr-3 text-gray-500 dark:text-gray-400"></i>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
                    </div>
                    <span
                        class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full"
                        x-text="`${(pkg.files['{{$category}}']?.length || 0)} files`"></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform"
                        :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
                </button>
                <div x-show="openSections.includes('{{$category}}')" x-collapse class="p-2 max-h-72 overflow-y-auto">
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
                                    x-text="file.name"></span>
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
            @php } @endphp

            {{ renderFileGroup('2D Drawings', 'fa-drafting-compass', '2d') }}
            {{ renderFileGroup('3D Models', 'fa-cubes', '3d') }}
            {{ renderFileGroup('ECN / Documents', 'fa-file-lines', 'ecn') }}
        </div>

        <div class="lg:col-span-8">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
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
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-50 backdrop-blur-sm">
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
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-20 backdrop-blur-sm">
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
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-20 backdrop-blur-sm">
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
                                    <canvas x-ref="hpglCanvas" class="pointer-events-none select-none"></canvas>

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

                                <div x-show="hpglLoading"
                                    x-transition.opacity
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-20 backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-green-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Rendering Plotter File...</span>
                                </div>

                                <div x-show="hpglError" class="..." x-text="hpglError"></div>
                            </div>
                    </div>
                    </template>

                    <template x-if="isCad(selectedFile?.name)">
                        <div x-ref="cadContainer"
                            class="w-full flex flex-col transition-all duration-300"
                            :class="isFullscreen ? 'fixed inset-0 z-50 h-screen bg-gray-100 dark:bg-gray-900 p-4 overflow-y-auto' : 'h-[75vh]'">

                            <div class="flex-1 relative border border-gray-200 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-900 overflow-hidden group">

                                <button @click="isPartListOpen = !isPartListOpen"
                                    x-show="cadPartsList.length > 0"
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
                                    class="absolute top-12 left-3 bottom-3 z-20 w-64 flex flex-col bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">

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

                                <button @click="toggleFullscreen()" title="Toggle Fullscreen"
                                    class="absolute top-3 right-3 z-30 w-8 h-8 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm shadow-md border border-gray-200 dark:border-gray-700 rounded text-gray-600 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                                    <i class="fa-solid" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                                </button>

                                <div x-ref="igesWrap" class="w-full h-full bg-black/5 cursor-grab active:cursor-grabbing">
                                </div>

                                <div x-show="iges.loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 dark:bg-gray-900/80 z-50 backdrop-blur-sm">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Processing CAD Geometry...</span>
                                </div>

                                <div x-show="iges.error" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-red-100 border border-red-300 text-red-700 rounded-md shadow-lg text-xs" x-text="iges.error"></div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 select-none"
                                x-data="{ isViewMenuOpen: false, isMatMenuOpen: false }">
                                <div class="flex items-center gap-2">

                                    <div class="inline-flex bg-gray-100 dark:bg-gray-700 p-1 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <button @click="setDisplayStyle('shaded')" class="px-2 py-1.5 text-[10px] font-semibold rounded transition-all"
                                            :class="currentStyle === 'shaded' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-200 dark:text-gray-300'">
                                            Shaded
                                        </button>
                                        <button @click="setDisplayStyle('shaded-edges')" class="px-2 py-1.5 text-[10px] font-semibold rounded transition-all"
                                            :class="currentStyle === 'shaded-edges' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-200 dark:text-gray-300'">
                                            Edges
                                        </button>
                                        <button @click="setDisplayStyle('wireframe')" class="px-2 py-1.5 text-[10px] font-semibold rounded transition-all"
                                            :class="currentStyle === 'wireframe' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-200 dark:text-gray-300'">
                                            Wire
                                        </button>
                                    </div>

                                    <div class="relative">
                                        <button @click="isMatMenuOpen = !isMatMenuOpen" @click.outside="isMatMenuOpen = false" title="Change Material"
                                            class="w-[34px] h-[34px] flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 transition shadow-sm"
                                            :class="activeMaterial !== 'default' ? 'text-purple-600 border-purple-200 bg-purple-50' : 'text-gray-600 dark:text-gray-200'">
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
                                </div>

                                <div class="flex items-center gap-2">

                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm h-[34px]">
                                        <label class="flex items-center gap-1 cursor-pointer" title="Exploded View">
                                            <input type="checkbox" class="rounded text-blue-600 focus:ring-0 border-gray-300 w-3 h-3"
                                                :checked="explode.enabled" @change="toggleExplode()">
                                            <i class="fa-solid fa-expand-arrows-alt text-xs" :class="explode.enabled ? 'text-blue-500' : 'text-gray-400'"></i>
                                        </label>
                                        <div x-show="explode.enabled" x-transition class="flex items-center gap-1 pl-2 border-l border-gray-200">
                                            <input type="range" min="0" max="100" x-model.number="explode.value" @input="updateExplode()"
                                                class="w-16 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm h-[34px]">
                                        <label class="flex items-center gap-1 cursor-pointer" title="Section Cut">
                                            <input type="checkbox" class="rounded text-blue-600 focus:ring-0 border-gray-300 w-3 h-3"
                                                :checked="clipping.enabled" @change="toggleClipping()">
                                            <i class="fa-solid fa-scissors rotate-90 text-xs" :class="clipping.enabled ? 'text-blue-500' : 'text-gray-400'"></i>
                                        </label>
                                        <div x-show="clipping.enabled" x-transition class="flex items-center gap-1 pl-2 border-l border-gray-200">
                                            <input type="range" :min="clipping.min" :max="clipping.max" x-model.number="clipping.value" @input="updateClippingVal()"
                                                class="w-16 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                        </div>
                                    </div>

                                    <button @click="toggleAutoRotate()" title="Auto Rotate"
                                        class="w-[34px] h-[34px] flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 transition shadow-sm"
                                        :class="{'text-blue-600 border-blue-200 bg-blue-50': autoRotate, 'text-gray-600': !autoRotate}">
                                        <i class="fa-solid fa-sync" :class="{'fa-spin': autoRotate}"></i>
                                    </button>

                                    <button @click="toggleHeadlight()" title="Flashlight (H)"
                                        class="w-[34px] h-[34px] flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 transition shadow-sm"
                                        :class="{'text-yellow-500 border-yellow-200 bg-yellow-50': headlight.enabled, 'text-gray-600': !headlight.enabled}">
                                        <i class="fa-solid fa-lightbulb"></i>
                                    </button>

                                </div>

                                <div class="flex items-center gap-2">

                                    <button @click="toggleAxes()" title="Toggle Axes Helper"
                                        class="w-[34px] h-[34px] flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 transition shadow-sm"
                                        :class="axesHelper.active ? 'text-red-500 border-red-200 bg-red-50' : 'text-gray-600 dark:text-gray-200'">
                                        <i class="fa-solid fa-arrows-to-dot"></i>
                                    </button>

                                    <div class="relative">
                                        <button @click="isViewMenuOpen = !isViewMenuOpen" @click.outside="isViewMenuOpen = false"
                                            class="px-2 py-1.5 h-[34px] text-xs font-medium rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 transition shadow-sm flex items-center gap-1">
                                            <i class="fa-solid fa-cube text-gray-500"></i> Views
                                        </button>
                                        <div x-show="isViewMenuOpen" x-transition class="absolute bottom-full right-0 mb-2 p-2 bg-white border border-gray-200 rounded-lg shadow-xl z-50 w-32 grid grid-cols-3 gap-1">
                                            <button @click="setStandardView('iso'); isViewMenuOpen=false" class="col-span-3 p-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded">ISO HOME</button>
                                            <button @click="setStandardView('top'); isViewMenuOpen=false" class="p-1 hover:bg-gray-100 text-[10px] rounded border">T</button>
                                            <button @click="setStandardView('front'); isViewMenuOpen=false" class="p-1 hover:bg-gray-100 text-[10px] rounded border">F</button>
                                            <button @click="setStandardView('right'); isViewMenuOpen=false" class="p-1 hover:bg-gray-100 text-[10px] rounded border">R</button>
                                            <button @click="setStandardView('left'); isViewMenuOpen=false" class="p-1 hover:bg-gray-100 text-[10px] rounded border">L</button>
                                            <button @click="setStandardView('back'); isViewMenuOpen=false" class="p-1 hover:bg-gray-100 text-[10px] rounded border">B</button>
                                            <button @click="setStandardView('bottom'); isViewMenuOpen=false" class="p-1 hover:bg-gray-100 text-[10px] rounded border">Bt</button>
                                        </div>
                                    </div>

                                    <button @click="toggleCameraMode()" title="Toggle Perspective/Orthographic"
                                        class="w-[34px] h-[34px] rounded border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 text-[10px] font-bold shadow-sm flex items-center justify-center">
                                        <span x-text="cameraMode === 'perspective' ? '3D' : '2D'"></span>
                                    </button>

                                    <button @click="takeScreenshot()" title="Take Screenshot"
                                        class="w-[34px] h-[34px] rounded border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 shadow-sm flex items-center justify-center">
                                        <i class="fa-solid fa-camera"></i>
                                    </button>

                                    <div class="flex items-center rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden h-[34px]">
                                        <button @click="toggleMeasure()" title="Measure Tool"
                                            class="w-[34px] h-full flex items-center justify-center transition"
                                            :class="iges.measure.enabled ? 'bg-blue-600 text-white' : 'hover:bg-gray-50 text-gray-600'">
                                            <i class="fa-solid fa-ruler-combined"></i>
                                        </button>

                                        <button @click="clearMeasurements()" x-show="iges.measure.enabled" title="Clear Measurements"
                                            x-transition
                                            class="w-[34px] h-full flex items-center justify-center border-l border-gray-200 dark:border-gray-700 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
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
    [x-collapse] {
        @apply overflow-hidden transition-all duration-300 ease-in-out;
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
</style>
@endpush

@push('scripts')
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Alpine collapse (untuk x-collapse) -->
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

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

<!-- OCCT: parser STEP/IGES (WASM) -->
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
            clipping: {
                enabled: false,
                value: 0,
                plane: null,
                min: -100,
                max: 100
            },
            explode: {
                enabled: false,
                value: 0
            },
            autoRotate: false,
            snapMarker: null,
            headlight: {
                enabled: false,
                object: null
            },
            isFullscreen: false,
            partInfo: {
                volume: '-',
                area: '-'
            },
            axesHelper: {
                active: false,
                object: null
            },
            activeMaterial: 'default',

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
                        return 'bottom-right';
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
                        original: this.positionIntToKey(file.ori_position ?? 2),
                        copy: this.positionIntToKey(file.copy_position ?? 1),
                        obsolete: this.positionIntToKey(file.obslt_position ?? 0),
                    };
                }

                this.stampConfig = this.stampPerFile[key];
            },

            saveStampConfigForCurrent() {},

            stampPositionClass(which = 'original') {
                const pos = (this.stampConfig && this.stampConfig[which]) || this.stampDefaults[which];

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
                    default:
                        return 'bottom-4 right-4';
                }
            },

            stampOriginClass(which = 'original') {
                const pos = (this.stampConfig && this.stampConfig[which]) || this.stampDefaults[which];

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
                    default:
                        return 'origin-bottom-right';
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
                THREE: null,
                measure: {
                    enabled: false,
                    group: null,
                    p1: null,
                    p2: null
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
                return ['igs', 'iges', 'stp', 'step'].includes(this.extOf(name));
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
                    if (!resp.ok) throw new Error('Gagal mengambil file HPGL');
                    const text = await resp.text();

                    const commands = text.replace(/\s+/g, '').split(';');

                    let penDown = false;
                    let x = 0,
                        y = 0;
                    const segments = [];
                    let minX = Infinity,
                        minY = Infinity,
                        maxX = -Infinity,
                        maxY = -Infinity;

                    const addPoint = (nx, ny) => {
                        if (penDown) {
                            segments.push({
                                x1: x,
                                y1: y,
                                x2: nx,
                                y2: ny
                            });
                            minX = Math.min(minX, x, nx);
                            minY = Math.min(minY, y, ny);
                            maxX = Math.max(maxX, x, nx);
                            maxY = Math.max(maxY, y, ny);
                        } else {
                            minX = Math.min(minX, nx);
                            minY = Math.min(minY, ny);
                            maxX = Math.max(maxX, nx);
                            maxY = Math.max(maxY, ny);
                        }
                        x = nx;
                        y = ny;
                    };

                    for (const raw of commands) {
                        if (!raw) continue;
                        const cmd = raw.toUpperCase();
                        const op = cmd.slice(0, 2);
                        const argsStr = cmd.slice(2);

                        const parseCoords = () => {
                            if (!argsStr) return [];
                            return argsStr.split(',').map(Number).filter(v => !isNaN(v));
                        };

                        if (op === 'IN') {
                            penDown = false;
                            x = 0;
                            y = 0;
                        } else if (op === 'SP') {} else if (op === 'PU') {
                            penDown = false;
                            const coords = parseCoords();
                            for (let i = 0; i < coords.length; i += 2) {
                                addPoint(coords[i], coords[i + 1]);
                            }
                        } else if (op === 'PD') {
                            penDown = true;
                            const coords = parseCoords();
                            for (let i = 0; i < coords.length; i += 2) {
                                addPoint(coords[i], coords[i + 1]);
                            }
                        } else if (op === 'PA') {
                            const coords = parseCoords();
                            for (let i = 0; i < coords.length; i += 2) {
                                addPoint(coords[i], coords[i + 1]);
                            }
                        }
                    }

                    await this.$nextTick();
                    const canvas = this.$refs.hpglCanvas;
                    if (!canvas) throw new Error('Canvas HPGL tidak ditemukan');

                    const parent = canvas.parentElement;
                    const w = parent.clientWidth || 800;
                    const h = parent.clientHeight || 500;

                    const dpr = window.devicePixelRatio || 1;
                    const logicalScale = 4 * dpr;
                    canvas.width = w * logicalScale;
                    canvas.height = h * logicalScale;
                    canvas.style.width = w + 'px';
                    canvas.style.height = h + 'px';

                    const ctx = canvas.getContext('2d');
                    ctx.setTransform(logicalScale, 0, 0, logicalScale, 0, 0);
                    ctx.clearRect(0, 0, w, h);
                    ctx.lineWidth = 1 / logicalScale;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    ctx.strokeStyle = '#000';

                    if (!segments.length) return;

                    const dx = maxX - minX || 1;
                    const dy = maxY - minY || 1;
                    const scale = 0.9 * Math.min(w / dx, h / dy);
                    const offX = w - dx * scale - minX * scale;
                    const offY = h + minY * scale;

                    ctx.beginPath();
                    for (const s of segments) {
                        const sx = s.x1 * scale + offX;
                        const sy = -s.y1 * scale + offY;
                        const ex = s.x2 * scale + offX;
                        const ey = -s.y2 * scale + offY;
                        ctx.moveTo(sx, sy);
                        ctx.lineTo(ex, ey);
                    }
                    ctx.stroke();
                } catch (e) {
                    console.error(e);
                    this.hpglError = e?.message || 'Gagal render HPGL';
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
                        p2: null
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

            /* ===== Measure (2-click) ===== */
            toggleMeasure() {
                const M = this.iges.measure;
                M.enabled = !M.enabled;
                if (M.enabled && !M.group) {
                    const THREE = this.iges.THREE;
                    M.group = new THREE.Group();
                    this.iges.scene.add(M.group);
                    this._bindMeasureEvents(true);
                }
                if (!M.enabled) {
                    this._bindMeasureEvents(false);
                    M.p1 = M.p2 = null;
                }
            },
            clearMeasurements() {
                const g = this.iges.measure.group;
                if (!g) return;
                (g.children || []).forEach(ch => ch.userData?.dispose?.());
                g.clear();
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
                this.iges.camera = newCamera;
                controls.object = newCamera;
                controls.update();

                // Perbarui measure tool jika aktif (karena raycaster butuh kamera yg benar)
                if (this.iges.measure.enabled) {
                    this.toggleMeasure(); // matikan dulu
                    this.toggleMeasure(); // nyalakan lagi dengan kamera baru
                }

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

            toggleClipping() {
                this.clipping.enabled = !this.clipping.enabled;
                const {
                    THREE
                } = this.iges; // Kita butuh THREE untuk bikin Plane

                if (this.clipping.enabled) {
                    // Reset slider ke 0
                    this.clipping.value = 0;

                    // Buat Plane Baru
                    this.clipping.plane = new THREE.Plane(new THREE.Vector3(1, 0, 0), 0);
                } else {
                    this.clipping.plane = null;
                }
                this.setDisplayStyle(this.currentStyle);
            },

            updateClippingVal() {
                if (this.clipping.plane) {
                    // Update posisi irisan berdasarkan slider
                    // Negatif agar arah gesernya intuitif (Kanan = Maju, Kiri = Mundur)
                    this.clipping.plane.constant = -this.clipping.value;
                }
            },

            // Helper untuk menempelkan plane ke material
            _updateMaterialsWithClipping() {
                const {
                    rootModel
                } = this.iges;
                if (!rootModel) return;

                const planes = this.clipping.enabled && this.clipping.plane ? [this.clipping.plane] : [];

                rootModel.traverse((o) => {
                    if (o.isMesh && o.material) {
                        // Kita handle jika materialnya Array atau Single
                        const mats = Array.isArray(o.material) ? o.material : [o.material];

                        mats.forEach(m => {
                            m.clippingPlanes = planes;
                            m.clipShadows = true; // Agar bayangan ikut terpotong (opsional)
                            m.needsUpdate = true; // Beri tahu Three.js ada perubahan
                        });
                    }
                });
            },

            _bindMeasureEvents(on) {
                const canvas = this.iges.renderer?.domElement;
                if (!canvas) return;

                if (on) {
                    // Event Klik (Untuk ambil titik 1 dan 2)
                    this._onMeasureClick = (ev) => {
                        if (!this.iges.measure.enabled) return;
                        const p = this._pickPoint(ev); // Ini sudah pakai snapping baru
                        if (!p) return;

                        const M = this.iges.measure;
                        if (!M.p1) {
                            M.p1 = p;
                            // Opsional: Beri tanda visual di P1
                        } else {
                            M.p2 = p;
                            this._drawMeasurement(M.p1, M.p2);
                            M.p1 = null;
                            M.p2 = null; // Reset setelah garis terbentuk
                        }
                    };

                    // Event Hover (Untuk efek magnet/preview)
                    this._onMeasureMove = (ev) => {
                        if (!this.iges.measure.enabled) return;
                        this._pickPoint(ev); // Panggil ini cuma buat update posisi Marker merah
                    };

                    canvas.addEventListener('click', this._onMeasureClick); // Ganti dblclick jadi click biar lebih responsif
                    canvas.addEventListener('mousemove', this._onMeasureMove);
                } else {
                    if (this._onMeasureClick) canvas.removeEventListener('click', this._onMeasureClick);
                    if (this._onMeasureMove) canvas.removeEventListener('mousemove', this._onMeasureMove);

                    // Sembunyikan marker saat mode measure mati
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

                // Gunakan boundsTree untuk performa (karena Anda sudah fix BVH sebelumnya)
                raycaster.firstHitOnly = true;

                const hits = raycaster.intersectObjects(rootModel.children, true);
                if (!hits.length) {
                    if (this.snapMarker) this.snapMarker.visible = false;
                    return null;
                }

                const hit = hits[0];
                let finalPoint = hit.point.clone(); // Default: titik di permukaan

                // === LOGIKA SNAPPING (MAGNET) ===
                // Cek 3 titik sudut (vertex) dari segitiga yang kena raycast
                if (hit.face) {
                    const mesh = hit.object;
                    const pos = mesh.geometry.attributes.position;

                    // Ambil koordinat 3 sudut segitiga tersebut
                    const vA = new THREE.Vector3().fromBufferAttribute(pos, hit.face.a).applyMatrix4(mesh.matrixWorld);
                    const vB = new THREE.Vector3().fromBufferAttribute(pos, hit.face.b).applyMatrix4(mesh.matrixWorld);
                    const vC = new THREE.Vector3().fromBufferAttribute(pos, hit.face.c).applyMatrix4(mesh.matrixWorld);

                    // Hitung jarak kursor ke masing-masing sudut
                    const distA = hit.point.distanceTo(vA);
                    const distB = hit.point.distanceTo(vB);
                    const distC = hit.point.distanceTo(vC);

                    // Tentukan threshold (jarak toleransi). Misal: 5% dari ukuran layar atau angka fix
                    // Disini kita pakai angka fix dulu agar gampang. Misal 2 unit world.
                    // Untuk lebih canggih, hitung berdasarkan jarak kamera.
                    const snapThreshold = hit.distance * 0.05; // 5% dari jarak mata ke objek

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

                    // Jika ada yang dekat, ganti titik akhir jadi vertex tersebut
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

                // Jika marker belum ada, buat dulu
                if (!this.snapMarker) {
                    const geom = new THREE.SphereGeometry(2, 16, 16); // Ukuran bola sesuaikan skala
                    const mat = new THREE.MeshBasicMaterial({
                        color: 0xff0000, // Warna Merah
                        transparent: true,
                        opacity: 0.8,
                        depthTest: false // Agar terlihat di atas objek (always on top)
                    });
                    this.snapMarker = new THREE.Mesh(geom, mat);
                    this.snapMarker.renderOrder = 999;
                    scene.add(this.snapMarker);
                }

                this.snapMarker.visible = true;
                this.snapMarker.position.copy(position);

                // Skalakan marker agar ukurannya konsisten di layar (opsional tapi bagus)
                const scale = this.iges.camera.position.distanceTo(position) * 0.01;
                this.snapMarker.scale.set(scale, scale, scale);
            },

            _drawMeasurement(a, b) {
                const THREE = this.iges.THREE;
                const group = new THREE.Group();

                // 1. Gambar Garis (Line)
                const geom = new THREE.BufferGeometry().setFromPoints([a, b]);
                const line = new THREE.Line(geom, new THREE.LineBasicMaterial({
                    color: 0xffffff
                })); // Putih biar kontras
                group.add(line);

                // 2. Gambar Titik Ujung (Sphere)
                const s = Math.max(0.4, a.distanceTo(b) / 160); // Ukuran dinamis
                const sg = new THREE.SphereGeometry(s, 16, 16);
                const sm = new THREE.MeshBasicMaterial({
                    color: 0xff0000
                }); // Merah
                const s1 = new THREE.Mesh(sg, sm);
                s1.position.copy(a);
                group.add(s1);
                const s2 = new THREE.Mesh(sg, sm);
                s2.position.copy(b);
                group.add(s2);

                // 3. Label (HTML DOM Overlay)
                const wrap = this.$refs.igesWrap;
                const lbl = document.createElement('div');
                lbl.className = 'measure-label';

                // === PERBAIKAN CSS DI SINI ===
                Object.assign(lbl.style, {
                    position: 'absolute',
                    top: '0px', // <--- WAJIB: Paksa mulai dari atas
                    left: '0px', // <--- WAJIB: Paksa mulai dari kiri
                    padding: '4px 8px',
                    background: 'rgba(0, 0, 0, 0.8)',
                    color: '#fff',
                    borderRadius: '4px',
                    fontSize: '11px',
                    fontFamily: 'monospace',
                    pointerEvents: 'none', // Agar klik tembus ke canvas
                    zIndex: '50', // Pastikan di atas canvas
                    whiteSpace: 'nowrap',
                    boxShadow: '0 2px 4px rgba(0,0,0,0.5)'
                });
                // =============================

                wrap.appendChild(lbl);

                // Fungsi Update Posisi (Dipanggil setiap frame di animate loop)
                const updateLabel = () => {
                    if (!this.iges.camera) return;

                    // Ambil titik tengah antara A dan B
                    const mid = a.clone().add(b).multiplyScalar(0.5);

                    // Proyeksikan titik 3D ke Layar 2D
                    // Hasil project() adalah range -1 s/d 1 (NDC)
                    mid.project(this.iges.camera);

                    const w = wrap.clientWidth;
                    const h = wrap.clientHeight;

                    // Konversi NDC (-1 s/d 1) ke Pixel Layar
                    const x = (mid.x * 0.5 + 0.5) * w;
                    const y = (-mid.y * 0.5 + 0.5) * h;

                    // Terapkan posisi. Translate -50% agar titik tengah label pas di titik ukur
                    lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -150%)`; // -150% biar label agak naik dikit di atas garis

                    // Update teks jarak
                    const dist = a.distanceTo(b);
                    lbl.textContent = `${dist.toFixed(2)} mm`;
                };

                group.userData.update = updateLabel;

                // Fungsi pembersihan saat tombol Clear ditekan
                group.userData.dispose = () => {
                    if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                    // Dispose geometry three.js
                    geom.dispose();
                    line.material.dispose();
                    sg.dispose();
                    sm.dispose();
                };

                // Jalankan update sekali di awal biar gak nge-blink
                updateLabel();

                this.iges.measure.group.add(group);
            },

            setStandardView(view) {
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
                        newPos.set(0, fitDist, 0);
                        break;
                    case 'bottom':
                        newPos.set(0, -fitDist, 0);
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

                // 3. Pindahkan Kamera (Bisa pakai animasi tweening kalau mau halus, tapi langsung set juga oke)
                camera.position.copy(newPos);
                camera.lookAt(center);

                // Reset rotasi kamera (PENTING untuk mode Orthographic/2D)
                camera.up.set(0, 1, 0);
                if (view === 'top' || view === 'bottom') {
                    // Khusus pandangan atas/bawah, sumbu UP harus diubah agar tidak pusing
                    camera.up.set(0, 0, -1);
                }

                controls.target.copy(center);
                controls.update();

                // Refresh clipping agar tidak glitch
                if (this.clipping.enabled) this._updateMaterialsWithClipping();
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
                            // Simpan posisi asli (Pivot)
                            this.iges.originalPositions.set(child.uuid, child.position.clone());

                            // 2. HITUNG PUSAT FISIK PART (PENTING!)
                            // Kita hitung di mana geometri benda ini sebenarnya berada
                            // Karena pivotnya mungkin 'ngaco' di 0,0,0
                            if (!child.geometry.boundingBox) child.geometry.computeBoundingBox();
                            const meshCenter = new THREE.Vector3();
                            child.geometry.boundingBox.getCenter(meshCenter);

                            // Konversi titik tengah lokal ke World Space
                            meshCenter.applyMatrix4(child.matrixWorld);

                            // Simpan "Arah Ledakan" yang spesifik untuk part ini
                            // Arah = (Titik Tengah Part) - (Titik Tengah Assembly)
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
                    this.explode.value = 50; // Default langsung meledak setengah
                } else {
                    this.explode.value = 0; // Reset rapat
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

                // 2. CEK KEAMANAN & KUPAS PROXY (SOLUSI ERROR)
                // Kita gunakan Alpine.raw() untuk mengambil objek asli yang belum dibungkus
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
                if (this.iges.controls) {
                    this.iges.controls.autoRotate = this.autoRotate;
                    this.iges.controls.autoRotateSpeed = 2.0; // Kecepatan putar
                }
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
                        const spot = new THREE.SpotLight(0xffffee, 2.5);

                        spot.position.set(0, 0, 0);
                        spot.target.position.set(0, 0, -1);
                        spot.angle = 0.6;
                        spot.penumbra = 1.0;
                        spot.decay = 0; // Tetap 0 agar jangkauan jauh
                        spot.distance = 5000;

                        rawCamera.add(spot.target);
                        this.headlight.object = spot;
                    }
                    rawCamera.add(this.headlight.object);
                } else {
                    if (this.headlight.object) {
                        rawCamera.remove(this.headlight.object);
                    }
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

            setupKeyboardShortcuts() {
                window.addEventListener('keydown', (e) => {
                    // Jangan jalan jika user sedang mengetik di input text (jika ada)
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                    // Hanya jalan jika mode CAD aktif
                    if (!this.isCad(this.selectedFile?.name)) return;

                    switch (e.key.toLowerCase()) {
                        case 'f':
                            this.setStandardView('front');
                            break;
                        case 't':
                            this.setStandardView('top');
                            break;
                        case 'r':
                            this.setStandardView('right');
                            break;
                        case 'i':
                            this.setStandardView('iso');
                            break;
                        case ' ': // Spasi untuk Auto Rotate
                            e.preventDefault(); // Cegah scroll halaman
                            this.toggleAutoRotate();
                            break;
                        case 'h': // H untuk Headlight/Flashlight
                            this.toggleHeadlight();
                            break;
                    }
                });
            },

            toggleAxes() {
                this.axesHelper.active = !this.axesHelper.active;
                const {
                    scene,
                    rootModel,
                    THREE
                } = this.iges;

                // 1. Ambil Scene ASLI (Raw)
                const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;

                if (this.axesHelper.active) {
                    if (!this.axesHelper.object) {
                        // Hitung ukuran helper
                        // Gunakan Alpine.raw pada rootModel juga untuk keamanan
                        const rawRoot = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(rootModel) : rootModel;

                        const box = new THREE.Box3().setFromObject(rawRoot);
                        const size = new THREE.Vector3();
                        box.getSize(size);
                        const maxDim = Math.max(size.x, size.y, size.z);

                        const axes = new THREE.AxesHelper(maxDim * 0.8);

                        axes.material.depthTest = false;
                        axes.renderOrder = 999;

                        this.axesHelper.object = axes;
                    }

                    // 2. PENTING: Kupas object AxesHelper sebelum dimasukkan ke Scene
                    // Ini kuncinya agar tidak freeze!
                    const rawAxes = (typeof Alpine !== 'undefined' && Alpine.raw) ?
                        Alpine.raw(this.axesHelper.object) :
                        this.axesHelper.object;

                    rawScene.add(rawAxes);

                } else {
                    if (this.axesHelper.object) {
                        // Saat menghapus pun, gunakan object raw
                        const rawAxes = (typeof Alpine !== 'undefined' && Alpine.raw) ?
                            Alpine.raw(this.axesHelper.object) :
                            this.axesHelper.object;

                        rawScene.remove(rawAxes);
                    }
                }

                // Paksa render ulang frame baru agar langsung muncul
                const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.renderer) : this.iges.renderer;
                const rawCamera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.camera) : this.iges.camera;
                if (rawRenderer && rawCamera) {
                    rawRenderer.render(rawScene, rawCamera);
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
                    if (this.clipping.enabled) this._updateMaterialsWithClipping();
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
                    // Sangat bagus untuk cek orientasi permukaan
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

                const {
                    THREE,
                    scene
                } = this.iges;
                this.setupKeyboardShortcuts();
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
                        this.renderCadOcct(file);
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
            downloadFile(file) {
                if (!file || !file.file_id) {
                    toastError('Error', 'File ID not found.');
                    return;
                }
                window.location.href = `/download/file/${file.file_id}`;
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
                                        window.location.href = data.download_url;
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
                        preserveDrawingBuffer: true
                    });
                    renderer.setPixelRatio(window.devicePixelRatio || 1);
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
                    const ext = (url.split('?')[0].split('#')[0].split('.').pop() || '').toLowerCase();
                    const params = {
                        linearUnit: 'millimeter',
                        linearDeflectionType: 'bounding_box_ratio',
                        linearDeflection: 0.1,
                        angularDeflection: 0.1,
                    };

                    let res = null;

                    if (ext === 'stp' || ext === 'step') {
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
                    } else {
                        res = occt.ReadIgesFile(mainBuf, params);
                        console.log('OCCT IGES result:', res);
                    }

                    if (!res || !res.success) {
                        const msg = res?.error || res?.message || 'File is not a valid STEP/IGES or is not supported by OCCT.';
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

                    // 3. ATUR RANGE SLIDER CLIPPING (Berdasarkan ukuran benda)
                    const maxDim = Math.max(size.x, size.y, size.z) || 100;
                    const rangeLimit = Math.ceil((maxDim / 2) * 1.2); // Setengah ukuran + 20% buffer

                    this.clipping.min = -rangeLimit;
                    this.clipping.max = rangeLimit;
                    this.clipping.value = 0; // Reset slider ke tengah

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
        }
    }
</script>
@endpush