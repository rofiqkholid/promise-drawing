@extends('layouts.app')
@section('title', 'Download Detail - File Manager')
@section('header-title', 'File Manager/Download Detail')

@section('content')

<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="exportDetail()" x-init="init()">

    <div x-show="isLoadingRevision" x-transition
           class="absolute inset-0 bg-gray-100/75 dark:bg-gray-900/75 z-10 flex items-center justify-center rounded-lg">
          <div class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
              <div class="w-6 h-6 border-4 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Loading Revision...</span>
          </div>
    </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
    <div class="lg:col-span-4 space-y-6 relative">

      <!-- ===== Meta Card ===== -->
      <div x-ref="metaCard"
           class="self-start bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 md:justify-between">
            <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
              <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
              Package Info
            </h2>
            @php
              $backUrl = route('file-manager.export'); //
            @endphp
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
              <i class="fa-solid fa-arrow-left"></i>
              Back
            </a>
          </div>
        </div>

        <div class="p-4 space-y-4" x-data="{ openClassification: false }">

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Package</label>
                <div class="flex items-start justify-between gap-2 mt-0.5">

                    <div class="flex-grow flex items-center gap-x-2 gap-y-1 flex-wrap min-w-0">
                        <p class="text-sm text-gray-900 dark:text-gray-100"
                           x-text="metaLine()"
                           :title="metaLine()">
                        </p>

                        <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                     bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                              x-text="revisionBadgeText()"
                              :title="revisionBadgeText()">
                        </span>
                    </div>

                    <button @click="openClassification = !openClassification"
                            class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 p-1 rounded-full transition-transform"
                            :class="{'rotate-180': openClassification}"
                            title="Toggle classification details">
                        <i class="fa-solid fa-chevron-down fa-xs"></i>
                    </button>
                </div>
            </div>

            <div x-show="openClassification" x-collapse class="pl-2 ml-1 border-l-2 border-gray-200 dark:border-gray-700">
                <dl class="space-y-2 text-sm" x-if="pkg.classification">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Doc. Group</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100" x-text="pkg.classification.doctype_group"></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Sub-Category</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100" x-text="pkg.classification.doctype_subcategory"></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Part Group</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100" x-text="pkg.classification.part_group"></dd>
                    </div>
                </dl>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <label for="revision-selector" class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-history fa-sm mr-1"></i>
                    Revision History
                </label>
                <select id="revision-selector"
                        x-model="selectedRevisionId"
                        @change="onRevisionChange()"
                        :disabled="isLoadingRevision"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-gray-700">

                  <template x-for="revision in revisionList" :key="revision.id">
                    <option :value="revision.id" x-text="revision.text"></option>
                  </template>

                  <template x-if="revisionList.length === 0">
                    <option value="" disabled>No other revisions found</option>
                  </template>
                </select>
            </div>
        </div>


        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
            <button
                @click="downloadPackage()"
                class="inline-flex items-center text-sm px-3 py-2 rounded-md
                        bg-blue-600 text-white hover:bg-blue-700
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fa-solid fa-download mr-2"></i>
                Download All Files
            </button>
        </div>
      </div>

      <!-- ===== File Groups (2D / 3D / ECN) ===== -->
      @php
        function renderFileGroup($title, $icon, $category) {
      @endphp
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <button @click="toggleSection('{{$category}}')" class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" :aria-expanded="openSections.includes('{{$category}}')">
          <div class="flex items-center">
            <i class="fa-solid {{$icon}} mr-3 text-gray-500 dark:text-gray-400"></i>
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
          </div>
          <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full" x-text="`${(pkg.files['{{$category}}']?.length || 0)} files`"></span>
          <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform" :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
        </button>
        <div x-show="openSections.includes('{{$category}}')" x-collapse class="p-2 max-h-72 overflow-y-auto">
          <template x-for="file in (pkg.files['{{$category}}'] || [])" :key="file.name">
            <div @click="selectFile(file)"
                :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}"
                class="flex items-center justify-between p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                role="button" tabindex="0" @keydown.enter="selectFile(file)">

                <div class="flex items-center min-w-0 pr-2">
                    <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3"></i>
                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
                </div>

                <button @click.stop="downloadFile(file)" class="flex-shrink-0 text-xs inline-flex items-center gap-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded">
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
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- No File Selected -->
        <div x-show="!selectedFile" x-cloak class="flex flex-col items-center justify-center h-96 p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
          <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500"></i>
          <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
        </div>

        <!-- File Preview -->
        <div x-show="selectedFile" x-transition.opacity x-cloak class="p-6">
          <!-- Header with Open in new tab -->
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedFile?.name"></h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">Revision: <span x-text="pkg.metadata.revision"></span></p>
            </div>
            <a x-show="selectedFile?.url" :href="selectedFile?.url" target="_blank" rel="noopener"
               class="inline-flex items-center px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
              <i class="fa-solid fa-up-right-from-square mr-2"></i> Open
            </a>
          </div>

          <!-- ZOOM TOOLBAR untuk JPG/PNG/TIFF/HPGL -->
          <div x-show="isImage(selectedFile?.name) || isTiff(selectedFile?.name) || isHpgl(selectedFile?.name) || isPdf(selectedFile?.name)"
            class="mb-3 flex items-center justify-end gap-2 text-xs text-gray-700 dark:text-gray-200">
            <span x-text="Math.round(imageZoom * 100) + '%'"></span>
            <button @click="zoomOut()"
              class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              -
            </button>
            <button @click="resetZoom()"
              class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              Fit
            </button>
            <button @click="zoomIn()"
              class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              +
            </button>
          </div>

          <!-- PREVIEW AREA (image/pdf/tiff/cad) -->
          <div class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center w-full relative">

            <!-- IMAGE (JPG/PNG/...) -->
            <template x-if="isImage(selectedFile?.name)">
              <div
                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                @mousedown.prevent="startPan($event)"
                @wheel.prevent="onWheelZoom($event)">
                <div class="w-full h-full flex items-center justify-center">
                  <div
                    class="relative inline-block"
                    :style="imageTransformStyle()">
                    <img
                      :src="selectedFile?.url"
                      alt="File Preview"
                      class="block pointer-events-none select-none max-w-full max-h-[70vh]"
                      loading="lazy">

                    <!-- STAMP ORIGINAL (kanan bawah, skala 0.45) -->
                    <div
                      x-show="pkg.stamp"
                      class="absolute bottom-4 right-4 origin-bottom-right"
                      style="transform: scale(0.45); transform-origin: bottom right;">
                      <div
                        class="w-56 h-40 border-2 border-blue-600 rounded-sm
                   text-[10px] text-blue-700 flex flex-col items-center
                   justify-between px-2 py-1"
                        style="background-color: transparent;">
                        <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                          <span x-text="stampTopLine()"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold tracking-[0.25em] text-blue-700 uppercase"
                            x-text="stampCenterOriginal()"></span>
                        </div>
                        <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                          <span x-text="stampBottomLine()"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP OBSOLETE (kiri bawah, skala 0.45) -->
                    <div
                      x-show="pkg.stamp?.is_obsolete"
                      class="absolute bottom-4 left-4 origin-bottom-left"
                      style="transform: scale(0.45); transform-origin: bottom left;">
                      <div
                        class="w-56 h-40 border-2 border-blue-600 rounded-sm
                   text-[10px] text-blue-700 flex flex-col items-center
                   justify-between px-2 py-1"
                        style="background-color: transparent;">
                        <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                          <span x-text="stampTopLine()"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold tracking-[0.25em] text-blue-700 uppercase"
                            x-text="stampCenterObsolete()"></span>
                        </div>
                        <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                          <span x-text="stampBottomLine()"></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- PDF -->
            <template x-if="isPdf(selectedFile?.name)">
              <div
                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                @mousedown.prevent="startPan($event)"
                @wheel.prevent="onWheelZoom($event)">
                <div class="w-full h-full flex items-center justify-center">
                  <div class="relative inline-block" :style="imageTransformStyle()">
                    <iframe
                      :src="pdfSrc(selectedFile?.url)"
                      class="block pointer-events-none select-none max-w-full max-h-[70vh] border-0"
                      title="PDF preview"></iframe>

                    <!-- STAMP ORIGINAL (kanan bawah, skala 0.45) -->
                    <div
                      x-show="pkg.stamp"
                      class="absolute bottom-4 right-4 origin-bottom-right"
                      style="transform: scale(0.45); transform-origin: bottom right;">
                      <div
                        class="w-56 h-40 border-2 border-blue-600 rounded-sm
                   text-[10px] text-blue-700 flex flex-col items-center
                   justify-between px-2 py-1"
                        style="background-color: transparent;">
                        <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                          <span x-text="stampTopLine()"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold tracking-[0.25em] text-blue-700 uppercase"
                            x-text="stampCenterOriginal()"></span>
                        </div>
                        <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                          <span x-text="stampBottomLine()"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP OBSOLETE (kiri bawah, skala 0.45) -->
                    <div
                      x-show="pkg.stamp?.is_obsolete"
                      class="absolute bottom-4 left-4 origin-bottom-left"
                      style="transform: scale(0.45); transform-origin: bottom left;">
                      <div
                        class="w-56 h-40 border-2 border-blue-600 rounded-sm
                   text-[10px] text-blue-700 flex flex-col items-center
                   justify-between px-2 py-1"
                        style="background-color: transparent;">
                        <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                          <span x-text="stampTopLine()"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold tracking-[0.25em] text-blue-700 uppercase"
                            x-text="stampCenterObsolete()"></span>
                        </div>
                        <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                          <span x-text="stampBottomLine()"></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- TIFF -->
            <template x-if="isTiff(selectedFile?.name)">
              <div
                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                @mousedown.prevent="startPan($event)"
                @wheel.prevent="onWheelZoom($event)">
                <div class="w-full h-full flex items-center justify-center">
                  <!-- wrapper yang di-zoom + pan -->
                  <div class="relative inline-block" :style="imageTransformStyle()">
                    <canvas
                      x-ref="tifCanvas"
                      class="block pointer-events-none select-none max-w-full max-h-[70vh]">
                    </canvas>

                    <!-- STAMP ORIGINAL (kanan bawah) -->
                    <div
                      x-show="pkg.stamp"
                      class="absolute bottom-4 right-4 origin-bottom-right"
                      style="transform: scale(0.45); transform-origin: bottom right;">
                      <div
                        class="w-56 h-40 border-2 border-blue-600 rounded-sm
                   text-[10px] text-blue-700 flex flex-col items-center
                   justify-between px-2 py-1"
                        style="background-color: transparent;">
                        <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                          <span x-text="stampTopLine()"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold tracking-[0.25em] uppercase text-blue-700"
                            x-text="stampCenterOriginal()"></span>
                        </div>
                        <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                          <span x-text="stampBottomLine()"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP OBSOLETE (kiri bawah) -->
                    <div
                      x-show="pkg.stamp?.is_obsolete"
                      class="absolute bottom-4 left-4 origin-bottom-left"
                      style="transform: scale(0.45); transform-origin: bottom left;">
                      <div
                        class="w-56 h-40 border-2 border-blue-600 rounded-sm
                   text-[10px] text-blue-700 flex flex-col items-center
                   justify-between px-2 py-1"
                        style="background-color: transparent;">
                        <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                          <span x-text="stampTopLine()"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold tracking-[0.25em] uppercase text-blue-700"
                            x-text="stampCenterObsolete()"></span>
                        </div>
                        <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                          <span x-text="stampBottomLine()"></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- status render -->
                <div
                  x-show="tifLoading"
                  class="absolute bottom-3 right-3 text-xs text-gray-700 dark:text-gray-200 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded">
                  Rendering TIFF…
                </div>
                <div
                  x-show="tifError"
                  class="absolute bottom-3 left-3 text-xs text-red-600 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded"
                  x-text="tifError"></div>
              </div>
            </template>

            <!-- HPGL -->
            <template x-if="isHpgl(selectedFile?.name)">
              <div
                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                @mousedown.prevent="startPan($event)"
                @wheel.prevent="onWheelZoom($event)">
                <div class="relative w-full h-full flex items-center justify-center" :style="imageTransformStyle()">
                  <canvas
                    x-ref="hpglCanvas"
                    class="pointer-events-none select-none"></canvas>

                  <!-- STAMP ORIGINAL (kanan bawah, skala 0.45) -->
                  <div
                    x-show="pkg.stamp"
                    class="absolute bottom-4 right-4 origin-bottom-right"
                    style="transform: scale(0.45); transform-origin: bottom right;">
                    <div
                      class="w-56 h-40 border-2 border-blue-600 rounded-sm
                 text-[10px] text-blue-700 flex flex-col items-center
                 justify-between px-2 py-1"
                      style="background-color: transparent;">
                      <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                        <span x-text="stampTopLine()"></span>
                      </div>
                      <div class="flex-1 flex items-center justify-center">
                        <span class="text-xs font-extrabold tracking-[0.25em] text-blue-700 uppercase"
                          x-text="stampCenterOriginal()"></span>
                      </div>
                      <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                        <span x-text="stampBottomLine()"></span>
                      </div>
                    </div>
                  </div>

                  <!-- STAMP OBSOLETE (kiri bawah, skala 0.45) -->
                  <div
                    x-show="pkg.stamp?.is_obsolete"
                    class="absolute bottom-4 left-4 origin-bottom-left"
                    style="transform: scale(0.45); transform-origin: bottom left;">
                    <div
                      class="w-56 h-40 border-2 border-blue-600 rounded-sm
                 text-[10px] text-blue-700 flex flex-col items-center
                 justify-between px-2 py-1"
                      style="background-color: transparent;">
                      <div class="w-full text-center border-b border-blue-600 pb-0.5 font-semibold tracking-tight">
                        <span x-text="stampTopLine()"></span>
                      </div>
                      <div class="flex-1 flex items-center justify-center">
                        <span class="text-xs font-extrabold tracking-[0.25em] text-blue-700 uppercase"
                          x-text="stampCenterObsolete()"></span>
                      </div>
                      <div class="w-full border-t border-blue-600 pt-0.5 text-center tracking-tight">
                        <span x-text="stampBottomLine()"></span>
                      </div>
                    </div>
                  </div>

                </div>

                <div
                  x-show="hpglLoading"
                  class="absolute bottom-3 right-3 text-xs text-gray-700 dark:text-gray-200 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded">
                  Rendering HPGL…
                </div>
                <div
                  x-show="hpglError"
                  class="absolute bottom-3 left-3 text-xs text-red-600 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded"
                  x-text="hpglError"></div>
              </div>
            </template>

            <!-- CAD: IGES / STEP via occt-import-js -->
            <template x-if="isCad(selectedFile?.name)">
              <div class="w-full">
                <div x-ref="igesWrap" class="w-full h-[70vh] rounded border border-gray-200 dark:border-gray-700 bg-black/5"></div>

                <!-- TOOLBAR -->
                <div class="mt-3 flex flex-wrap items-center gap-2">
                  <div class="inline-flex rounded-md shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                    <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700" @click="setDisplayStyle('shaded')">Shaded</button>
                  </div>
                  <div class="inline-flex rounded-md shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                    <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700" @click="setDisplayStyle('shaded-edges')">Shaded+Edges</button>
                  </div>

                  <div class="inline-flex items-center gap-2 ml-2">
                    <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="{'bg-blue-50 dark:bg-blue-900/30': iges.measure.enabled}"
                            @click="toggleMeasure()">
                      Measure
                    </button>
                    <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700"
                            @click="clearMeasurements()">
                      Clear
                    </button>
                  </div>
                </div>

                <div x-show="iges.loading" class="text-xs text-gray-500 mt-2">Loading CAD…</div>
                <div x-show="iges.error" class="text-xs text-red-600 mt-2" x-text="iges.error"></div>
              </div>
            </template>

            <!-- FALLBACK -->
            <template x-if="!isImage(selectedFile?.name) && !isPdf(selectedFile?.name) && !isTiff(selectedFile?.name) && !isCad(selectedFile?.name) && !isHpgl(selectedFile?.name)">
              <div class="text-center">
                <i class="fa-solid fa-file text-6xl text-gray-400 dark:text-gray-500"></i>
                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Preview Unavailable</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">This file type is not supported for preview.</p>
              </div>
            </template>

          </div>
          <!-- /PREVIEW AREA -->
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  [x-collapse] { @apply overflow-hidden transition-all duration-300 ease-in-out; }
  .preview-area { @apply bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center; }
  [x-cloak] { display: none !important; }
  .measure-label { user-select: none; white-space: nowrap; }
</style>

@endsection

@push('scripts')
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Alpine collapse (untuk x-collapse) -->
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

<!-- UTIF.js untuk render TIFF -->
<script src="https://unpkg.com/utif@3.1.0/dist/UTIF.min.js"></script>

<!-- ES Module shims + Import Map untuk Three.js (module) -->
<script async src="https://unpkg.com/es-module-shims@1.10.0/dist/es-module-shims.js"></script>
<script type="importmap">
{
  "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/",
    "three-mesh-bvh": "https://unpkg.com/three-mesh-bvh@0.7.6/build/index.module.js"
  }
}
</script>

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
      icon: { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' }
    } : {
      mode: 'light',
      bg: 'rgba(255, 255, 255, 0.98)',
      fg: '#0f172a',
      border: 'rgba(226, 232, 240, 1)',
      progress: 'rgba(15,23,42,.8)',
      icon: { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' }
    };
  }
  const BaseToast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false,
    timer: 2600, timerProgressBar: true,
    showClass: { popup: 'swal2-animate-toast-in' },
    hideClass: { popup: 'swal2-animate-toast-out' },
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
  function renderToast({ icon = 'success', title = 'Success', text = '' } = {}) {
    const t = detectTheme();
    BaseToast.fire({
      icon, title, text,
      iconColor: t.icon[icon] || t.icon.success,
      background: t.bg, color: t.fg,
      customClass: { popup: 'swal2-toast border', title: '', timerProgressBar: '' },
      didOpen: (toast) => {
        const bar = toast.querySelector('.swal2-timer-progress-bar'); if (bar) bar.style.background = t.progress;
        const popup = toast.querySelector('.swal2-popup'); if (popup) popup.style.borderColor = t.border;
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });
  }
  function toastSuccess(title='Berhasil', text='Operasi berhasil dijalankan.') { renderToast({icon:'success', title, text}); }
  function toastError(title='Gagal', text='Terjadi kesalahan.') { BaseToast.update({timer:3400}); renderToast({icon:'error', title, text}); BaseToast.update({timer:2600}); }
  function toastWarning(title='Peringatan', text='Periksa kembali data Anda.') { renderToast({icon:'warning', title, text}); }
  function toastInfo(title='Informasi', text='') { renderToast({icon:'info', title, text}); }
  window.toastSuccess = toastSuccess; window.toastError = toastError; window.toastWarning = toastWarning; window.toastInfo = toastInfo;

  /* ========== Alpine Component ========== */
  function exportDetail() {
    return {
      exportId: JSON.parse(`@json($exportId)`),
      pkg: JSON.parse(`@json($detail)`),
      revisionList: JSON.parse(`@json($revisionList ?? [])`),
      selectedRevisionId: JSON.parse(`@json($exportId)`),
      isLoadingRevision: false,
      stampFormat: JSON.parse(`@json($stampFormat ?? null)`),

      selectedFile: null,
      openSections: [],

      // ZOOM + PAN untuk image / TIFF / HPGL
      imageZoom: 1,
      minZoom: 0.5,
      maxZoom: 4,
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
      formatStampDate(d) {
        return d || '';
      },

      // teks tengah stamp ORIGINAL
      stampCenterOriginal() {
        return 'ORIGINAL';
      },
      // teks tengah stamp OBSOLETE
      stampCenterObsolete() {
        return 'OBSOLETE';
      },

      stampTopLine() {
        const d = this.pkg?.stamp?.receipt_date;
        if (!d) return '';
        const label = (this.stampFormat && this.stampFormat.prefix) ?
          this.stampFormat.prefix :
          'DATE RECEIVED';
        return `${label} : ${this.formatStampDate(d)}`;
      },

      stampBottomLine() {
        const d = this.pkg?.stamp?.upload_date;
        if (!d) return '';
        const label = (this.stampFormat && this.stampFormat.suffix) ?
          this.stampFormat.suffix :
          'DATE UPLOADED';
        return `${label} : ${this.formatStampDate(d)}`;
      },

      // TIFF state
      tifLoading: false, tifError: '',

      // HPGL state
      hpglLoading: false,
      hpglError: '',

      // CAD viewer state
      iges: {
        renderer: null, scene: null, camera: null, controls: null, animId: 0,
        loading: false, error: '',
        rootModel: null,
        THREE: null,
        measure: { enabled: false, group: null, p1: null, p2: null }
      },
      _onIgesResize: null,

      /* ===== Helpers jenis file ===== */
      extOf(name){ const i = (name||'').lastIndexOf('.'); return i>-1 ? (name||'').slice(i+1).toLowerCase() : ''; },
      isImage(name) { return ['png','jpg','jpeg','webp','gif','bmp'].includes(this.extOf(name)); },
      isPdf(name)   { return this.extOf(name) === 'pdf'; },
      isTiff(name)  { return ['tif','tiff'].includes(this.extOf(name)); },
      isHpgl(name)  { return ['plt', 'hpgl', 'hpg', 'prn'].includes(this.extOf(name)); },
      isCad(name)   { return ['igs','iges','stp','step'].includes(this.extOf(name)); },
      pdfSrc(u) { return u; },

      /* ===== TIFF renderer ===== */
      async renderTiff(url) {
        if (!url || !window.UTIF) return;
        this.tifLoading = true; this.tifError = '';
        try {
          const resp = await fetch(url, { cache:'no-store', credentials:'same-origin' });
          if (!resp.ok) throw new Error('Gagal mengambil file TIFF');
          const buf = await resp.arrayBuffer();
          const ifds = UTIF.decode(buf);
          UTIF.decodeImages(buf, ifds);
          if (!ifds?.length) throw new Error('TIFF tidak memiliki frame');
          const first = ifds[0];
          const rgba = UTIF.toRGBA8(first);
          const w = first.width, h = first.height;
          const canvas = this.$refs.tifCanvas; if (!canvas) throw new Error('Canvas TIFF tidak ditemukan');
          const ctx = canvas.getContext('2d');
          canvas.width = w; canvas.height = h;
          const imgData = new ImageData(new Uint8ClampedArray(rgba), w, h);
          ctx.putImageData(imgData, 0, 0);
        } catch (e) { console.error(e); this.tifError = e?.message || 'Gagal render TIFF'; }
        finally { this.tifLoading = false; }
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
          const offX = (w - dx * scale) / 2 - minX * scale;
          const offY = (h - dy * scale) / 2 + maxY * scale;

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
        for (let i=0; i<meshes.length; i++) {
          const m = meshes[i];
          const g = new THREE.BufferGeometry();
          g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
          if (m.attributes.normal?.array) g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
          if (m.index?.array) g.setIndex(m.index.array);
          let color = 0xcccccc;
          if (m.color && m.color.length === 3) color = (m.color[0] << 16) | (m.color[1] << 8) | (m.color[2]);
          const mat = new THREE.MeshStandardMaterial({ color, metalness: 0, roughness: 1, side: THREE.DoubleSide });
          const mesh = new THREE.Mesh(g, mat);
          mesh.name = m.name || `mesh_${i}`;
          group.add(mesh);
        }
        return group;
      },

      /* ===== Cleanup CAD ===== */
      disposeCad() {
        try {
          cancelAnimationFrame(this.iges.animId || 0);
          if (this._onIgesResize) window.removeEventListener('resize', this._onIgesResize);
          const { renderer, scene, controls } = this.iges || {};
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
          if (wrap) while (wrap.firstChild) wrap.removeChild(wrap.firstChild);
        } catch {}
        this.iges = {
          renderer: null, scene: null, camera: null, controls: null, animId: 0,
          loading: false, error: '',
          rootModel: null, THREE: null,
          measure: { enabled: false, group: null, p1: null, p2: null }
        };
        this._onIgesResize = null;
      },

      /* ===== Meta line formatter ===== */
      metaLine() {
            const m = this.pkg?.metadata || {};
            return [m.customer, m.model, m.part_no]
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
      _cacheOriginalMaterials(root, THREE){
        root.traverse(o=>{
          if (o.isMesh && !this._oriMats.has(o)) {
            const m = o.material;
            this._oriMats.set(o, Array.isArray(m) ? m.map(mm=>mm.clone()) : m.clone());
          }
        });
      },
      _restoreMaterials(root){
        root.traverse(o=>{
          if (!o.isMesh) return;
          const m = this._oriMats.get(o); if (!m) return;
          o.material = Array.isArray(m) ? m.map(mm=>mm.clone()) : m.clone();
        });
        this._setWireframe(root, false);
        this._toggleEdges(root, false);
        this._setPolygonOffset(root, false);
      },
      _setWireframe(root, on=true){
        root.traverse(o=>{
          if (!o.isMesh) return;
          (Array.isArray(o.material)?o.material:[o.material]).forEach(m=> m.wireframe = on);
        });
      },
      _setPolygonOffset(root, on=true, factor=1, units=1){
        root.traverse(o=>{
          if (!o.isMesh) return;
          (Array.isArray(o.material)?o.material:[o.material]).forEach(m=>{
            m.polygonOffset = on; m.polygonOffsetFactor = factor; m.polygonOffsetUnits = units;
          });
        });
      },
      _addEdges(mesh, THREE, threshold=30){
        if (mesh.userData.edges) return mesh.userData.edges;
        const edgesGeo = new THREE.EdgesGeometry(mesh.geometry, threshold);
        const edgesMat = new THREE.LineBasicMaterial({ transparent:true, opacity:0.6, depthTest:false });
        const edges = new THREE.LineSegments(edgesGeo, edgesMat);
        edges.renderOrder = 999;
        mesh.add(edges);
        mesh.userData.edges = edges;
        return edges;
      },
      _toggleEdges(root, on=true, color=0x000000){
        const THREE = this.iges.THREE;
        root.traverse(o=>{
          if (!o.isMesh) return;
          if (on){
            const e = this._addEdges(o, THREE, 30);
            e.material.color = new THREE.Color(color);
          } else if (o.userData.edges){
            o.remove(o.userData.edges);
            o.userData.edges.geometry.dispose();
            o.userData.edges.material.dispose();
            o.userData.edges = null;
          }
        });
      },
      setDisplayStyle(mode){
        const root = this.iges.rootModel; if (!root) return;
        this._restoreMaterials(root);
        if (mode === 'shaded') return;
        if (mode === 'shaded-edges'){
          this._setPolygonOffset(root, true, 1, 1);
          this._toggleEdges(root, true, 0x000000);
          return;
        }
      },

      /* ===== Measure (2-click) ===== */
      toggleMeasure(){
        const M = this.iges.measure;
        M.enabled = !M.enabled;
        if (M.enabled && !M.group) {
          const THREE = this.iges.THREE;
          M.group = new THREE.Group();
          this.iges.scene.add(M.group);
          this._bindMeasureEvents(true);
        }
        if (!M.enabled){
          this._bindMeasureEvents(false);
          M.p1 = M.p2 = null;
        }
      },
      clearMeasurements(){
        const g = this.iges.measure.group;
        if (!g) return;
        (g.children||[]).forEach(ch => ch.userData?.dispose?.());
        g.clear();
      },
      _bindMeasureEvents(on){
        const canvas = this.iges.renderer?.domElement; if (!canvas) return;
        if (on){
          this._onMeasureDblClick = (ev)=>{
            if (!this.iges.measure.enabled) return;
            const p = this._pickPoint(ev); if (!p) return;
            const M = this.iges.measure;
            if (!M.p1) { M.p1 = p; return; }
            M.p2 = p; this._drawMeasurement(M.p1, M.p2); M.p1 = M.p2 = null;
          };
          canvas.addEventListener('dblclick', this._onMeasureDblClick);
        } else {
          canvas.removeEventListener('dblclick', this._onMeasureDblClick);
        }
      },
      _pickPoint(ev){
        const { THREE, camera, rootModel } = this.iges;
        const rect = this.iges.renderer.domElement.getBoundingClientRect();
        const mouse = new THREE.Vector2(
          ((ev.clientX - rect.left)/rect.width)*2 - 1,
          -((ev.clientY - rect.top)/rect.height)*2 + 1
        );
        const raycaster = new THREE.Raycaster();
        raycaster.setFromCamera(mouse, camera);
        const hits = raycaster.intersectObjects(rootModel.children, true);
        if (!hits.length) return null;
        return hits[0].point.clone();
      },
      _drawMeasurement(a, b){
        const THREE = this.iges.THREE;
        const group = new THREE.Group();

        // line
        const geom = new THREE.BufferGeometry().setFromPoints([a,b]);
        const line = new THREE.Line(geom, new THREE.LineBasicMaterial({}));
        group.add(line);

        // end points
        const s = Math.max(0.4, a.distanceTo(b)/160);
        const sg = new THREE.SphereGeometry(s, 16, 16);
        const sm = new THREE.MeshBasicMaterial({});
        const s1 = new THREE.Mesh(sg, sm); s1.position.copy(a); group.add(s1);
        const s2 = new THREE.Mesh(sg, sm); s2.position.copy(b); group.add(s2);

        // label (DOM)
        const wrap = this.$refs.igesWrap;
        const lbl = document.createElement('div');
        lbl.className = 'measure-label';
        lbl.style.position = 'absolute';
        lbl.style.pointerEvents = 'none';
        lbl.style.font = '12px/1.2 monospace';
        lbl.style.padding = '2px 6px';
        lbl.style.background = 'rgba(0,0,0,.75)';
        lbl.style.color = '#fff';
        lbl.style.borderRadius = '4px';
        lbl.style.zIndex = '20';
        wrap.appendChild(lbl);

        const updateLabel = ()=>{
          const mid = a.clone().add(b).multiplyScalar(0.5).project(this.iges.camera);
          const w = wrap.clientWidth, h = wrap.clientHeight;
          const x = (mid.x * 0.5 + 0.5) * w;
          const y = (-mid.y * 0.5 + 0.5) * h;
          lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
          lbl.textContent = `${a.distanceTo(b).toFixed(2)} mm`;
        };

        group.userData.update = updateLabel;
        group.userData.dispose = ()=> lbl.remove();
        updateLabel();

        this.iges.measure.group.add(group);
      },

      /* ===== Lifecycle ===== */
      init() {
        window.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            // Handle escape key if needed
          }
        });
        window.addEventListener('beforeunload', () => this.disposeCad());
        window.addEventListener('mousemove', (e) => this.onPan(e));
        window.addEventListener('mouseup', () => this.endPan());
        window.addEventListener('mouseleave', () => this.endPan());
      },

      /* ===== UI ===== */
      toggleSection(c) {
        const i = this.openSections.indexOf(c);
        if (i > -1) this.openSections.splice(i, 1);
        else this.openSections.push(c);
      },

      selectFile(file) {
        if (this.isCad(this.selectedFile?.name)) this.disposeCad();
        if (this.isTiff(this.selectedFile?.name)) { this.tifError = ''; this.tifLoading = false; }
        if (this.isHpgl(this.selectedFile?.name)) {
          this.hpglError = '';
          this.hpglLoading = false;
        }

        // Reset zoom and pan when selecting a new file
        this.imageZoom = 1;
        this.panX = 0;
        this.panY = 0;

        this.selectedFile = { ...file };

        if (this.isTiff(file?.name)) this.renderTiff(file.url);
        else if (this.isCad(file?.name)) this.renderCadOcct(file.url);
        else if (this.isHpgl(file?.name)) this.renderHpgl(file.url);
      },

      onRevisionChange() {
          if (this.selectedRevisionId === this.exportId) {
              return;
          }

          this.isLoadingRevision = true;
          this.selectedFile = null;
          this.disposeCad();
          this.tifError = ''; this.tifLoading = false;
          this.hpglError = ''; this.hpglLoading = false;

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
                  toastSuccess('Revision Loaded', `Displaying ${data.pkg.metadata.revision}.`);
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
          customClass: { popup: 'swal2-popup border' },
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
              customClass: { popup: 'swal2-popup border' },
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
                      customClass: { popup: 'swal2-popup border' },
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
              if (error.name === 'AbortError' || error.message === 'Aborted') {
                  console.log('File preparation canceled by user.');
                  const t_cancel = detectTheme();
                  Swal.fire({
                      title: 'Canceled',
                      text: 'File preparation was canceled.',
                      icon: 'info',
                      iconColor: t_cancel.icon.info,
                      background: t_cancel.bg,
                      color: t_cancel.fg,
                      customClass: { popup: 'swal2-popup border' },
                      didOpen: (popup) => {
                          const p = popup.querySelector('.swal2-popup');
                          if (p) p.style.borderColor = t_cancel.border;
                      },
                      confirmButtonColor: '#2563eb',
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
                  customClass: { popup: 'swal2-popup border' },
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
      async renderCadOcct(url) {
        if (!url) return;
        this.disposeCad();
        this.iges.loading = true; this.iges.error = '';

        try {
          const THREE = await import('three');
          const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');
          const bvh = await import('three-mesh-bvh');
          THREE.Mesh.prototype.raycast = bvh.acceleratedRaycast;
          THREE.BufferGeometry.prototype.computeBoundsTree = bvh.computeBoundsTree;
          THREE.BufferGeometry.prototype.disposeBoundsTree  = bvh.disposeBoundsTree;

          // scene & camera
          const scene = new THREE.Scene();
          scene.background = null;
          const wrap = this.$refs.igesWrap;
          const width = wrap?.clientWidth || 800, height = wrap?.clientHeight || 500;

          const camera = new THREE.PerspectiveCamera(50, width/height, 0.1, 10000);
          camera.position.set(250, 200, 250);

          const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
          renderer.setPixelRatio(window.devicePixelRatio || 1);
          renderer.setSize(width, height);
          wrap.appendChild(renderer.domElement);
          wrap.style.position = 'relative';
          wrap.style.overflow  = 'hidden';

          // lights
          const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 0.8); hemi.position.set(0, 200, 0); scene.add(hemi);
          const dir  = new THREE.DirectionalLight(0xffffff, 0.9); dir.position.set(150, 200, 100); scene.add(dir);

          // controls
          const controls = new OrbitControls(camera, renderer.domElement);
          controls.enableDamping = true;

          // fetch file
          const resp = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });
          if (!resp.ok) throw new Error('Gagal mengambil file CAD');
          const buffer = await resp.arrayBuffer();
          const file = new Uint8Array(buffer);

          // parse dengan occt
          const occt = await window.occtimportjs(); // dari <script> CDN
          const ext = (url.split('?')[0].split('#')[0].split('.').pop() || '').toLowerCase();
          const res = (ext === 'stp' || ext === 'step') ? occt.ReadStepFile(file, null) : occt.ReadIgesFile(file, null);
          if (!res?.success) throw new Error('OCCT gagal mem-parsing file');

          // build meshes -> scene
          const group = this._buildThreeFromOcct(res, THREE);
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

          // auto-fit kamera
          const box = new THREE.Box3().setFromObject(group);
          const size = new THREE.Vector3(); box.getSize(size);
          const center = new THREE.Vector3(); box.getCenter(center);
          const maxDim = Math.max(size.x, size.y, size.z) || 100;
          const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
          camera.position.copy(center.clone().add(new THREE.Vector3(1,1,1).normalize().multiplyScalar(fitDist * 1.6)));
          camera.near = Math.max(maxDim / 100, 0.1);
          camera.far  = Math.max(maxDim * 100, 1000);
          camera.updateProjectionMatrix();
          controls.target.copy(center);
          controls.update();

          // render loop + update label measure
          const animate = () => {
            controls.update();
            renderer.render(scene, camera);
            const g = this.iges.measure.group;
            if (g) g.children.forEach(ch => ch.userData?.update?.());
            this.iges.animId = requestAnimationFrame(animate);
          };
          animate();

          // resize
          this._onIgesResize = () => {
            const w = this.$refs.igesWrap?.clientWidth || 800;
            const h = this.$refs.igesWrap?.clientHeight || 500;
            camera.aspect = w / h; camera.updateProjectionMatrix();
            renderer.setSize(w, h);
          };
          window.addEventListener('resize', this._onIgesResize);

          // default style
          this.setDisplayStyle('shaded-edges');

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