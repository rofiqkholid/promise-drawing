@extends('layouts.app')
@section('title', 'Share Detail - PROMISE')
@section('header-title', 'Share Detail')

@section('content')

<div
  class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen"
  x-data="shareDetail()"
  x-init="init()"
  @mousemove.window="onPan($event)"
  @mouseup.window="endPan()"
  @mouseleave.window="endPan()">


  <!-- ================= MAIN LAYOUT: LEFT STACK + RIGHT PREVIEW ================= -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
    <!-- ================= LEFT COLUMN (lg:span 4) ================= -->
    <div class="lg:col-span-4 space-y-6">

      <!-- ===== Meta Card ===== -->
      <div x-ref="metaCard"
        class="self-start bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 md:justify-between">
            <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
              <i class="fa-solid fa-share-nodes mr-2 text-blue-600"></i>
              Share Metadata
            </h2>

            @php
            $backUrl = url()->previous();
            $backUrl = ($backUrl && $backUrl !== url()->current())
            ? $backUrl
            : route('file-manager.share'); // fallback ke list share
            @endphp

            <div class="flex items-center gap-2">
              {{-- tombol Share dari halaman detail --}}
              <button
                type="button"
                id="btnOpenShareFromDetail"
                data-id="{{ $detail['metadata']['revision_id'] ?? $revisionId ?? '' }}"
                class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-blue-500 text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-400 dark:focus:ring-offset-gray-800">
                <i class="fa-solid fa-paper-plane"></i>
                Share
              </button>


              {{-- tombol Back lama --}}
              <a href="{{ $backUrl }}"
                class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                <i class="fa-solid fa-arrow-left"></i>
                Back
              </a>
            </div>
          </div>

        </div>

        <!-- Body: ringkasan metadata -->
        <div class="px-4 py-4 space-y-3">
          <!-- satu baris ringkas -->
          <p class="text-sm text-gray-700 dark:text-gray-200" x-text="metaLine()"></p>
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
            <div
              @click="selectFile(file)"
              :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}"
              class="flex items-center p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
              role="button" tabindex="0" @keydown.enter="selectFile(file)">

              <!-- ICON DARI MASTER FILE EXTENSION -->
              <template x-if="file.icon_src">
                <img :src="file.icon_src"
                  alt=""
                  class="w-5 h-5 mr-3 object-contain" />
              </template>

              <!-- FALLBACK KALAU TIDAK ADA ICON DI MASTER -->
              <template x-if="!file.icon_src">
                <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 transition-colors group-hover:text-blue-500"></i>
              </template>

              <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
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

      <!-- ===== Activity Log (below ECN) ===== -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
          <div class="flex items-center">
            <i class="fa-solid fa-clock-rotate-left mr-2 text-gray-500 dark:text-gray-400"></i>
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Activity Log</span>
          </div>
          <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full"
            x-text="`${pkg.activityLogs?.length || 0} events`"></span>
        </div>

        <div
          class="p-2 space-y-2"
          :class="(pkg.activityLogs?.length || 0) > 3 ? 'max-h-64 overflow-y-auto pr-1' : ''"
          role="log"
          aria-label="Activity Log">
          <template x-for="(item, idx) in (pkg.activityLogs || [])" :key="idx">
            <div class="flex items-start gap-3 p-3 rounded-md bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
              <div class="mt-0.5">
                <template x-if="item.action === 'uploaded'"><i class="fa-solid fa-upload text-blue-500"></i></template>
                <template x-if="item.action === 'approved'"><i class="fa-solid fa-circle-check text-green-500"></i></template>
                <template x-if="item.action === 'rejected'"><i class="fa-solid fa-circle-xmark text-red-500"></i></template>
                <template x-if="item.action === 'rollbacked'"><i class="fa-solid fa-rotate-left text-amber-500"></i></template>
                <template x-if="!['uploaded','approved','rejected','rollbacked'].includes(item.action)"><i class="fa-solid fa-circle-info text-gray-500"></i></template>
              </div>
              <div class="min-w-0">
                <p class="text-sm text-gray-900 dark:text-gray-100">
                  <span class="font-medium capitalize" x-text="item.action"></span>
                  <span class="mx-1">by</span>
                  <span class="font-medium" x-text="item.user"></span>
                  <template x-if="item.note">
                    <span class="text-gray-600 dark:text-gray-400">— <span x-text="item.note"></span></span>
                  </template>
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="item.time"></p>
              </div>
            </div>
          </template>

          <template x-if="(pkg.activityLogs || []).length === 0">
            <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">
              No activity yet for this package.
            </p>
          </template>
        </div>
      </div>

    </div>
    <!-- ================= /LEFT COLUMN ================= -->

    <!-- ================= RIGHT COLUMN (lg:span 8) Preview ================= -->
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
              <p
                class="text-xs text-gray-500 dark:text-gray-400"
                x-text="fileSizeInfo()">
              </p>
            </div>
          </div>

          <!-- STAMP + BLOCK + ZOOM TOOLBAR -->
          <div x-show="selectedFile" x-cloak class="mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">

            <div x-show="!isCad(selectedFile?.name)" class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                  <i class="fa-solid fa-stamp"></i> Stamp Configuration
                </span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position: Original</label>
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

                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Type: Copy</label>
                  <div class="relative">
                    <select x-model="copyType" @change="onStampChange()"
                      class="block w-full pl-3 pr-8 py-2 text-xs text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-blue-500">
                      <option value="controlled">Controlled Copy</option>
                      <option value="uncontrolled">Uncontrolled Copy</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position: Obsolete</label>
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

            <div x-show="!isCad(selectedFile?.name)" class="px-4 py-2 bg-gray-50 dark:bg-gray-800/50 flex flex-col md:flex-row items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-700">

              <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mr-2 flex-shrink-0">
                  Blocks
                </span>
                <div class="inline-flex rounded-md shadow-sm isolate flex-shrink-0">
                  <button type="button" @click.stop="addMask()"
                    class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                    <i class="fa-solid fa-plus mr-1.5 text-green-600"></i> Add
                  </button>
                  <button type="button" @click="saveCurrentMask()"
                    class="relative -ml-px inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5 text-blue-600"></i> Save
                  </button>
                  <button type="button" @click.stop="removeActiveMask()" x-show="getActiveMask()" x-cloak
                    class="relative -ml-px inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-white border border-gray-300 rounded-r-md hover:bg-red-50 focus:z-10 focus:border-red-500 focus:ring-1 focus:ring-red-500 dark:bg-gray-700 dark:text-red-400 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                    <i class="fa-solid fa-trash-can mr-1.5"></i> Delete
                  </button>
                </div>
              </div>

              <div class="flex items-center gap-4 w-full md:w-auto justify-end">

                <div x-show="isImage(selectedFile?.name) || isTiff(selectedFile?.name) || isHpgl(selectedFile?.name) || isPdf(selectedFile?.name)"
                  class="flex items-center bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm">
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

            </div>
          </div>

          <!-- PREVIEW AREA (image/pdf/tiff/cad) -->
          <div
            class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center w-full relative"
            @mousedown="deactivateMask()">

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

                    <!-- WHITE BLOCKS (MULTI) -->
                    <template x-for="mask in masks" :key="mask.id">
                      <div
                        x-show="mask.visible"
                        x-cloak
                        :style="maskStyle(mask)"
                        class="absolute bg-white/100 shadow-sm cursor-move"
                        @mousedown.stop.prevent="onMaskMouseDown($event, mask)"
                        @click.stop="activateMask(mask)">

                        <!-- BORDER hanya saat aktif -->
                        <div
                          x-show="mask.active"
                          x-cloak
                          class="absolute inset-0 border border-blue-500 pointer-events-none">
                        </div>

                        <!-- HANDLE ROTATE (bulatan di atas, hanya aktif + editable) -->
                        <div
                          x-show="mask.active && mask.editable"
                          x-cloak
                          class="w-3 h-3 absolute left-1/2 -translate-x-1/2 -top-4 rounded-full border border-gray-600 bg-gray-200 cursor-alias"
                          @mousedown.stop.prevent="startMaskRotate($event, mask)">
                        </div>

                        <!-- ZONA RESIZE: transparan di tepian blok -->
                        <template x-if="mask.active && mask.editable">
                          <div>
                            <!-- atas / bawah -->
                            <div class="absolute inset-x-3 top-0 h-2 cursor-n-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'n', mask)"></div>
                            <div class="absolute inset-x-3 bottom-0 h-2 cursor-s-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 's', mask)"></div>

                            <!-- kiri / kanan -->
                            <div class="absolute inset-y-3 left-0 w-2 cursor-w-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'w', mask)"></div>
                            <div class="absolute inset-y-3 right-0 w-2 cursor-e-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'e', mask)"></div>

                            <!-- pojok -->
                            <div class="absolute left-0  top-0    w-3 h-3 cursor-nw-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'nw', mask)"></div>
                            <div class="absolute right-0 top-0    w-3 h-3 cursor-ne-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'ne', mask)"></div>
                            <div class="absolute left-0  bottom-0 w-3 h-3 cursor-sw-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'sw', mask)"></div>
                            <div class="absolute right-0 bottom-0 w-3 h-3 cursor-se-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'se', mask)"></div>
                          </div>
                        </template>
                      </div>
                    </template>



                    <!-- STAMP ORIGINAL -->
                    <div
                      x-show="pkg.stamp && !isUncontrolledCopy()"
                      class="absolute"
                      :class="stampPositionClass('original')">
                      <div
                        :class="stampOriginClass('original')"
                        class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                          <span x-text="stampTopLine('original')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold uppercase text-blue-700 px-2 px-2"
                            x-text="stampCenterOriginal()"></span>
                        </div>
                        <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                          <span x-text="stampBottomLine('original')"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP COPY -->
                    <div
                      x-show="pkg.stamp"
                      x-cloak
                      class="absolute"
                      :class="stampPositionClass('copy')">
                      <div
                        :class="stampOriginClass('copy')"
                        class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                          <span x-text="stampTopLine('copy')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span
                            class="text-xs font-extrabold uppercase text-blue-700 px-2"
                            x-text="stampCenterCopy()"></span>
                        </div>
                        <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                          <span x-text="stampBottomLine('copy')"></span>
                        </div>
                      </div>
                    </div>


                    <!-- STAMP OBSOLETE -->
                    <div
                      x-show="pkg.stamp?.is_obsolete"
                      class="absolute"
                      :class="stampPositionClass('obsolete')">
                      <div
                        :class="stampOriginClass('obsolete')"
                        class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                          <!-- top line: Date : Oct.25th 2025 -->
                          <span x-text="stampTopLine('obsolete')"></span>
                        </div>

                        <div class="flex-1 flex items-center justify-center">
                          <!-- middle: SAI-DRAWING OBSOLETE -->
                          <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                            x-text="stampCenterObsolete()"></span>
                        </div>

                        <!-- bottom: Name & Dept -->
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
            </template>

            <!-- PDF via pdf.js + canvas -->
            <template x-if="isPdf(selectedFile?.name)">
              <div
                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing"
                @mousedown.prevent="startPan($event)"
                @wheel.prevent="onWheelZoom($event)">
                <div class="w-full h-full flex items-center justify-center">
                  <div class="relative inline-block" :style="imageTransformStyle()">
                    <canvas
                      x-ref="pdfCanvas"
                      class="block pointer-events-none select-none max-w-full max-h-[70vh]">
                    </canvas>


                    <!-- WHITE BLOCKS (MULTI) -->
                    <template x-for="mask in masks" :key="mask.id">
                      <div
                        x-show="mask.visible"
                        x-cloak
                        :style="maskStyle(mask)"
                        class="absolute bg-white/100 shadow-sm cursor-move"
                        @mousedown.stop.prevent="onMaskMouseDown($event, mask)"
                        @click.stop="activateMask(mask)">

                        <!-- BORDER hanya saat aktif -->
                        <div
                          x-show="mask.active"
                          x-cloak
                          class="absolute inset-0 border border-blue-500 pointer-events-none">
                        </div>

                        <!-- HANDLE ROTATE (bulatan di atas, hanya aktif + editable) -->
                        <div
                          x-show="mask.active && mask.editable"
                          x-cloak
                          class="w-3 h-3 absolute left-1/2 -translate-x-1/2 -top-4 rounded-full border border-gray-600 bg-gray-200 cursor-alias"
                          @mousedown.stop.prevent="startMaskRotate($event, mask)">
                        </div>

                        <!-- ZONA RESIZE: transparan di tepian blok -->
                        <template x-if="mask.active && mask.editable">
                          <div>
                            <!-- atas / bawah -->
                            <div class="absolute inset-x-3 top-0 h-2 cursor-n-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'n', mask)"></div>
                            <div class="absolute inset-x-3 bottom-0 h-2 cursor-s-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 's', mask)"></div>

                            <!-- kiri / kanan -->
                            <div class="absolute inset-y-3 left-0 w-2 cursor-w-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'w', mask)"></div>
                            <div class="absolute inset-y-3 right-0 w-2 cursor-e-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'e', mask)"></div>

                            <!-- pojok -->
                            <div class="absolute left-0  top-0    w-3 h-3 cursor-nw-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'nw', mask)"></div>
                            <div class="absolute right-0 top-0    w-3 h-3 cursor-ne-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'ne', mask)"></div>
                            <div class="absolute left-0  bottom-0 w-3 h-3 cursor-sw-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'sw', mask)"></div>
                            <div class="absolute right-0 bottom-0 w-3 h-3 cursor-se-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'se', mask)"></div>
                          </div>
                        </template>
                      </div>
                    </template>


                    <!-- STAMP ORIGINAL -->
                    <div
                      x-show="pkg.stamp && !isUncontrolledCopy()"
                      class="absolute"
                      :class="stampPositionClass('original')">
                      <div
                        :class="stampOriginClass('original')"
                        class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                          <span x-text="stampTopLine('original')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                            x-text="stampCenterOriginal()"></span>
                        </div>
                        <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                          <span x-text="stampBottomLine('original')"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP COPY -->
                    <div
                      x-show="pkg.stamp"
                      x-cloak
                      class="absolute"
                      :class="stampPositionClass('copy')">
                      <div
                        :class="stampOriginClass('copy')"
                        class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                          <span x-text="stampTopLine('copy')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                            x-text="stampCenterCopy()"></span>
                        </div>
                        <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                          <span x-text="stampBottomLine('copy')"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP OBSOLETE -->
                    <div
                      x-show="pkg.stamp?.is_obsolete"
                      class="absolute"
                      :class="stampPositionClass('obsolete')">
                      <div
                        :class="stampOriginClass('obsolete')"
                        class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                          <!-- top line: Date : Oct.25th 2025 -->
                          <span x-text="stampTopLine('obsolete')"></span>
                        </div>

                        <div class="flex-1 flex items-center justify-center">
                          <!-- middle: SAI-DRAWING OBSOLETE -->
                          <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                            x-text="stampCenterObsolete()"></span>
                        </div>

                        <!-- bottom: Name & Dept -->
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

                <!-- status render PDF -->
                <div
                  x-show="pdfLoading"
                  class="absolute bottom-3 right-3 text-xs text-gray-700 dark:text-gray-200 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded">
                  Rendering PDF…
                </div>
                <div
                  x-show="pdfError"
                  class="absolute bottom-3 left-3 text-xs text-red-600 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded"
                  x-text="pdfError"></div>
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
                    <img
                      x-ref="tifImg"
                      alt="TIFF Preview"
                      class="block pointer-events-none select-none max-w-full max-h-[70vh]" />


                    <!-- WHITE BLOCKS (MULTI) -->
                    <template x-for="mask in masks" :key="mask.id">
                      <div
                        x-show="mask.visible"
                        x-cloak
                        :style="maskStyle(mask)"
                        class="absolute bg-white/100 shadow-sm cursor-move"
                        @mousedown.stop.prevent="onMaskMouseDown($event, mask)"
                        @click.stop="activateMask(mask)">

                        <!-- BORDER hanya saat aktif -->
                        <div
                          x-show="mask.active"
                          x-cloak
                          class="absolute inset-0 border border-blue-500 pointer-events-none">
                        </div>

                        <!-- HANDLE ROTATE (bulatan di atas, hanya aktif + editable) -->
                        <div
                          x-show="mask.active && mask.editable"
                          x-cloak
                          class="w-3 h-3 absolute left-1/2 -translate-x-1/2 -top-4 rounded-full border border-gray-600 bg-gray-200 cursor-alias"
                          @mousedown.stop.prevent="startMaskRotate($event, mask)">
                        </div>

                        <!-- ZONA RESIZE: transparan di tepian blok -->
                        <template x-if="mask.active && mask.editable">
                          <div>
                            <!-- atas / bawah -->
                            <div class="absolute inset-x-3 top-0 h-2 cursor-n-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'n', mask)"></div>
                            <div class="absolute inset-x-3 bottom-0 h-2 cursor-s-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 's', mask)"></div>

                            <!-- kiri / kanan -->
                            <div class="absolute inset-y-3 left-0 w-2 cursor-w-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'w', mask)"></div>
                            <div class="absolute inset-y-3 right-0 w-2 cursor-e-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'e', mask)"></div>

                            <!-- pojok -->
                            <div class="absolute left-0  top-0    w-3 h-3 cursor-nw-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'nw', mask)"></div>
                            <div class="absolute right-0 top-0    w-3 h-3 cursor-ne-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'ne', mask)"></div>
                            <div class="absolute left-0  bottom-0 w-3 h-3 cursor-sw-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'sw', mask)"></div>
                            <div class="absolute right-0 bottom-0 w-3 h-3 cursor-se-resize"
                              @mousedown.stop.prevent="startMaskResize($event, 'se', mask)"></div>
                          </div>
                        </template>
                      </div>
                    </template>



                    <!-- STAMP ORIGINAL -->
                    <div
                      x-show="pkg.stamp && !isUncontrolledCopy()"
                      class="absolute"
                      :class="stampPositionClass('original')">
                      <div
                        :class="stampOriginClass('original')"
                        class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                          <span x-text="stampTopLine('original')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                            x-text="stampCenterOriginal()"></span>
                        </div>
                        <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                          <span x-text="stampBottomLine('original')"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP COPY -->
                    <div
                      x-show="pkg.stamp"
                      x-cloak
                      class="absolute"
                      :class="stampPositionClass('copy')">
                      <div
                        :class="stampOriginClass('copy')"
                        class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                          <span x-text="stampTopLine('copy')"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                          <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                            x-text="stampCenterCopy()"></span>
                        </div>
                        <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                          <span x-text="stampBottomLine('copy')"></span>
                        </div>
                      </div>
                    </div>

                    <!-- STAMP OBSOLETE -->
                    <div
                      x-show="pkg.stamp?.is_obsolete"
                      class="absolute"
                      :class="stampPositionClass('obsolete')">
                      <div
                        :class="stampOriginClass('obsolete')"
                        class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                        style="transform: scale(0.45);">
                        <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                          <!-- top line: Date : Oct.25th 2025 -->
                          <span x-text="stampTopLine('obsolete')"></span>
                        </div>

                        <div class="flex-1 flex items-center justify-center">
                          <!-- middle: SAI-DRAWING OBSOLETE -->
                          <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                            x-text="stampCenterObsolete()"></span>
                        </div>

                        <!-- bottom: Name & Dept -->
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


                  <!-- WHITE BLOCKS (MULTI) -->
                  <template x-for="mask in masks" :key="mask.id">
                    <div
                      x-show="mask.visible"
                      x-cloak
                      :style="maskStyle(mask)"
                      class="absolute bg-white/100 shadow-sm cursor-move"
                      @mousedown.stop.prevent="onMaskMouseDown($event, mask)"
                      @click.stop="activateMask(mask)">

                      <!-- BORDER hanya saat aktif -->
                      <div
                        x-show="mask.active"
                        x-cloak
                        class="absolute inset-0 border border-blue-500 pointer-events-none">
                      </div>

                      <!-- HANDLE ROTATE (bulatan di atas, hanya aktif + editable) -->
                      <div
                        x-show="mask.active && mask.editable"
                        x-cloak
                        class="w-3 h-3 absolute left-1/2 -translate-x-1/2 -top-4 rounded-full border border-gray-600 bg-gray-200 cursor-alias"
                        @mousedown.stop.prevent="startMaskRotate($event, mask)">
                      </div>

                      <!-- ZONA RESIZE: transparan di tepian blok -->
                      <template x-if="mask.active && mask.editable">
                        <div>
                          <!-- atas / bawah -->
                          <div class="absolute inset-x-3 top-0 h-2 cursor-n-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 'n', mask)"></div>
                          <div class="absolute inset-x-3 bottom-0 h-2 cursor-s-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 's', mask)"></div>

                          <!-- kiri / kanan -->
                          <div class="absolute inset-y-3 left-0 w-2 cursor-w-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 'w', mask)"></div>
                          <div class="absolute inset-y-3 right-0 w-2 cursor-e-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 'e', mask)"></div>

                          <!-- pojok -->
                          <div class="absolute left-0  top-0    w-3 h-3 cursor-nw-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 'nw', mask)"></div>
                          <div class="absolute right-0 top-0    w-3 h-3 cursor-ne-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 'ne', mask)"></div>
                          <div class="absolute left-0  bottom-0 w-3 h-3 cursor-sw-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 'sw', mask)"></div>
                          <div class="absolute right-0 bottom-0 w-3 h-3 cursor-se-resize"
                            @mousedown.stop.prevent="startMaskResize($event, 'se', mask)"></div>
                        </div>
                      </template>
                    </div>
                  </template>



                  <!-- STAMP ORIGINAL -->
                  <div
                    x-show="pkg.stamp && !isUncontrolledCopy()"
                    class="absolute"
                    :class="stampPositionClass('original')">
                    <div
                      :class="stampOriginClass('original')"
                      class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                      style="transform: scale(0.45);">
                      <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                        <span x-text="stampTopLine('original')"></span>
                      </div>
                      <div class="flex-1 flex items-center justify-center">
                        <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                          x-text="stampCenterOriginal()"></span>
                      </div>
                      <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                        <span x-text="stampBottomLine('original')"></span>
                      </div>
                    </div>
                  </div>

                  <!-- STAMP COPY -->
                  <div
                    x-show="pkg.stamp"
                    x-cloak
                    class="absolute"
                    :class="stampPositionClass('copy')">
                    <div
                      :class="stampOriginClass('copy')"
                      class="min-w-65 w-auto h-20 border-2 border-blue-600 rounded-sm text-[10px] text-blue-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                      style="transform: scale(0.45);">
                      <div class="w-full text-center border-b-2 border-blue-600 py-0.5 px-4 font-semibold tracking-tight">
                        <span x-text="stampTopLine('copy')"></span>
                      </div>
                      <div class="flex-1 flex items-center justify-center">
                        <span class="text-xs font-extrabold uppercase text-blue-700 px-2"
                          x-text="stampCenterCopy()"></span>
                      </div>
                      <div class="w-full border-t-2 border-blue-600 py-0.5 px-4 text-center font-semibold tracking-tight">
                        <span x-text="stampBottomLine('copy')"></span>
                      </div>
                    </div>
                  </div>

                  <!-- STAMP OBSOLETE -->
                  <div
                    x-show="pkg.stamp?.is_obsolete"
                    class="absolute"
                    :class="stampPositionClass('obsolete')">
                    <div
                      :class="stampOriginClass('obsolete')"
                      class="min-w-65 w-auto h-20 border-2 border-red-600 rounded-sm text-[10px] text-red-700 opacity-50 flex flex-col justify-between bg-transparent whitespace-nowrap"
                      style="transform: scale(0.45);">
                      <div class="w-full text-center border-b-2 border-red-600 py-0.5 px-4 font-semibold tracking-tight">
                        <!-- top line: Date : Oct.25th 2025 -->
                        <span x-text="stampTopLine('obsolete')"></span>
                      </div>

                      <div class="flex-1 flex items-center justify-center">
                        <!-- middle: SAI-DRAWING OBSOLETE -->
                        <span class="text-xs font-extrabold text-red-700 uppercase px-2"
                          x-text="stampCenterObsolete()"></span>
                      </div>

                      <!-- bottom: Name & Dept -->
                      <div class="w-full border-t border-red-600 pt-0.5 px-1 flex justify-between tracking-tight">
                        <span>
                          Name :
                          <span x-text="obsoleteName()"></span>
                        </span>
                        <span>
                          <span x-text="(getObsoleteFormat().suffix || 'Dept.') + ' :'"></span>
                          <span x-text="obsoleteDept()"></span>
                        </span>
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
            <template
              x-if="
                !isImage(selectedFile?.name)
                && !isPdf(selectedFile?.name)
                && !isTiff(selectedFile?.name)
                && !isCad(selectedFile?.name)
                && !isHpgl(selectedFile?.name)
              ">
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
    <!-- ================= /RIGHT COLUMN ================= -->
  </div>
  <!-- ================= /MAIN LAYOUT ================= -->


  <!-- ====== MODAL SHARE (COPY DARI HALAMAN LIST) ====== -->
  <div id="shareModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75"
    style="display: none;">

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
      <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Share Document Package</h3>
        <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <i class="fa-solid fa-times fa-lg"></i>
        </button>
      </div>

      <div class="p-6">
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
          Select one or more suppliers to share this package with.
        </p>

        <div>
          <label for="supplierListContainer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select to Share</label>
          <div class="relative mt-1">
            <select id="supplierListContainer" name="supplierListContainer" class="w-full"></select>
          </div>
          <div id="selectedSupplierContainer" class="mt-2"></div>
        </div>

        <input type="hidden" id="hiddenPackageId" value="">
      </div>

      <div class="flex justify-end p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 rounded-b-lg space-x-3">
        <button type="button"
          class="btn-close-modal px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
          Cancel
        </button>
        <button id="btnSaveShare"
          type="button"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Share
        </button>
      </div>

    </div>
  </div>
  <!-- ====== /MODAL SHARE ====== -->

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

  @endsection

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

    function toastSuccess(title = 'Success', text = 'Operation completed successfully.') {
      renderToast({
        icon: 'success',
        title,
        text
      });
    }

    function toastError(title = 'Error', text = 'An error occurred.') {
      renderToast({
        icon: 'error',
        title,
        text
      });
    }

    function toastWarning(title = 'Warning', text = 'Please check your data.') {
      renderToast({
        icon: 'warning',
        title,
        text
      });
    }

    function toastInfo(title = 'Information', text = '') {
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
    function shareDetail() {
      // private var untuk pdf.js
      let pdfDoc = null;

      return {
        // data dari backend
        pkg: JSON.parse(`@json($detail)`),
        stampFormats: JSON.parse(`@json($stampFormats)`),

        // URL template update posisi stamp per file
        updateStampUrlTemplate: `{{ route('approvals.files.updateStamp', ['fileId' => '__FILE_ID__']) }}`,

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

        // per file: jenis Copy stamp
        copyTypePerFile: {}, // { [fileKey]: 'controlled' | 'uncontrolled' }
        copyType: 'controlled',


        // ===== WHITE BLOCKS (MULTI MASK) =====
        masks: [],
        nextMaskId: 1,



        selectedFile: null,
        openSections: [],

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

        // ZOOM + PAN
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
          if (delta < 0) {
            this.imageZoom = Math.min(this.imageZoom + this.zoomStep, this.maxZoom);
          } else if (delta > 0) {
            this.imageZoom = Math.max(this.imageZoom - this.zoomStep, this.minZoom);
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

        // mapping posisi
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
        positionKeyToInt(key) {
          switch (key) {
            case 'bottom-left':
              return 0;
            case 'bottom-center':
              return 1;
            case 'bottom-right':
              return 2;
            case 'top-left':
              return 3;
            case 'top-center':
              return 4;
            case 'top-right':
              return 5;
            default:
              return 2;
          }
        },

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

        // ==== format stamp normal vs obsolete ====
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
          const statusId = this.pkg?.metadata?.model_status_id;
          if (statusId == 4) {
            return 'SAI-DRAWING UNCONTROLLED COPY';
          }
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

        isUncontrolledCopy() {
          const statusId = this.pkg?.metadata?.model_status_id;

          // Kalau user pilih dari dropdown => pakai itu
          if (this.copyType === 'uncontrolled') {
            return true;
          }

          // Fallback: kalau masih mau pakai rule dari status_id (misal =4 = uncontrolled)
          if (Number(statusId) === 4) {
            return true;
          }

          return false;
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
            const statusId = this.pkg?.metadata?.model_status_id;

            if (statusId == 4) {
              // UNCONTROLLED
              return 'SAI / PUD / For Quotation';
            } else {
              // CONTROLLED (Existing logic)
              const now = new Date();
              const dateStr = this.formatStampDate(now.toISOString().split('T')[0]);

              const hours = String(now.getHours()).padStart(2, '0');
              const minutes = String(now.getMinutes()).padStart(2, '0');
              const seconds = String(now.getSeconds()).padStart(2, '0');
              const timeStr = `${hours}:${minutes}:${seconds}`;

              const deptCode = this.userDeptCode || '--';

              return `SAI / ${deptCode} / ${dateStr} ${timeStr}`;
            }

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
            const statusId = this.pkg?.metadata?.model_status_id;

            if (statusId == 4) {
              // UNCONTROLLED
              // Mengambil tanggal shared_at dari pkg.stamp
              const sharedDate = s.shared_at || '';
              return `Date Share : ${this.formatStampDate(sharedDate)}`;
            } else {
              // CONTROLLED (Existing logic)
              const userName = this.userName || '--';
              return `External - Ditributed To Supplier`;
            }
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

        // ===== helper key per file =====
        getFileKey(file) {
          return (file?.id ?? file?.name ?? '').toString();
        },

        loadStampConfigFor(file) {
          const key = this.getFileKey(file);
          if (!key) {
            this.stampConfig = {
              ...this.stampDefaults
            };
            this.copyType = 'controlled';
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

          // load type Copy per file
          if (!this.copyTypePerFile[key]) {
            // kalau backend nanti kirim copy_type di file, pakai itu
            const initial =
              file.copy_type === 'uncontrolled' ?
              'uncontrolled' :
              'controlled';

            this.copyTypePerFile[key] = initial;
            this.copyType = initial;
          } else {
            this.copyType = this.copyTypePerFile[key];
          }
        },




        saveStampConfigForCurrent() {
          const key = this.getFileKey(this.selectedFile);
          if (!key) return;

          this.stampPerFile[key] = {
            ...this.stampConfig,
          };

          this.copyTypePerFile[key] = this.copyType || 'controlled';
        },



        async onStampChange() {
          this.saveStampConfigForCurrent();
          if (!this.selectedFile?.id) return;

          const url = this.updateStampUrlTemplate.replace('__FILE_ID__', this.selectedFile.id);

          const payload = {
            ori_position: this.positionKeyToInt(this.stampConfig.original),
            copy_position: this.positionKeyToInt(this.stampConfig.copy),
            obslt_position: this.positionKeyToInt(this.stampConfig.obsolete),
            copy_type: this.copyType || 'controlled',
          };

          try {
            const res = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify(payload),
            });

            const text = await res.text();
            let json = {};
            try {
              json = JSON.parse(text);
            } catch {}

            if (!res.ok) {
              throw new Error(json.message || 'Failed to save stamp position');
            }

            toastSuccess('Saved', json.message || 'Stamp position saved.');
          } catch (e) {
            console.error(e);
            toastError('Error', e.message || 'Failed to save stamp position');
          }
        },

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
        },

        /* ===== TIFF renderer ===== */
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


        nextPdfPage() {
          if (!pdfDoc) return;
          if (this.pdfPageNum >= this.pdfNumPages) return;
          this.pdfPageNum++;
          this.renderPdfPage();
        },
        prevPdfPage() {
          if (!pdfDoc) return;
          if (this.pdfPageNum <= 1) return;
          this.pdfPageNum--;
          this.renderPdfPage();
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
              } else if (op === 'SP') {
                // ignore pen select
              } else if (op === 'PU') {
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
            if (!canvas) throw new Error('HPGL canvas not found');

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
            this.hpglError = e?.message || 'Failed to render HPGL';
          } finally {
            this.hpglLoading = false;
          }
        },

        /* ===== OCCT result -> THREE meshes ===== */
        _buildThreeFromOcct(result, THREE) {
          const group = new THREE.Group();
          const meshes = result.meshes || [];
          for (let i = 0; i < meshes.length; i++) {
            const m = meshes[i];
            const g = new THREE.BufferGeometry();
            g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
            if (m.attributes.normal?.array) g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
            if (m.index?.array) g.setIndex(m.index.array);
            let color = 0xcccccc;
            if (m.color && m.color.length === 3) color = (m.color[0] << 16) | (m.color[1] << 8) | (m.color[2]);
            const mat = new THREE.MeshStandardMaterial({
              color,
              metalness: 0,
              roughness: 1,
              side: THREE.DoubleSide
            });
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
              m.part_group,
              m.doc_type,
              m.category,
              m.ecn_no,
              m.revision,
              this.pkg?.status
            ]
            .filter(v => v && String(v).trim().length > 0)
            .join(' - ');
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
          this._restoreMaterials(root);
          if (mode === 'shaded') return;
          if (mode === 'shaded-edges') {
            this._setPolygonOffset(root, true, 1, 1);
            this._toggleEdges(root, true, 0x000000);
          }
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
        _bindMeasureEvents(on) {
          const canvas = this.iges.renderer?.domElement;
          if (!canvas) return;
          if (on) {
            this._onMeasureDblClick = (ev) => {
              if (!this.iges.measure.enabled) return;
              const p = this._pickPoint(ev);
              if (!p) return;
              const M = this.iges.measure;
              if (!M.p1) {
                M.p1 = p;
                return;
              }
              M.p2 = p;
              this._drawMeasurement(M.p1, M.p2);
              M.p1 = M.p2 = null;
            };
            canvas.addEventListener('dblclick', this._onMeasureDblClick);
          } else {
            canvas.removeEventListener('dblclick', this._onMeasureDblClick);
          }
        },
        _pickPoint(ev) {
          const {
            THREE,
            camera,
            rootModel
          } = this.iges;
          const rect = this.iges.renderer.domElement.getBoundingClientRect();
          const mouse = new THREE.Vector2(
            ((ev.clientX - rect.left) / rect.width) * 2 - 1,
            -((ev.clientY - rect.top) / rect.height) * 2 + 1
          );
          const raycaster = new THREE.Raycaster();
          raycaster.setFromCamera(mouse, camera);
          const hits = raycaster.intersectObjects(rootModel.children, true);
          if (!hits.length) return null;
          return hits[0].point.clone();
        },
        _drawMeasurement(a, b) {
          const THREE = this.iges.THREE;
          const group = new THREE.Group();

          const geom = new THREE.BufferGeometry().setFromPoints([a, b]);
          const line = new THREE.Line(geom, new THREE.LineBasicMaterial({}));
          group.add(line);

          const s = Math.max(0.4, a.distanceTo(b) / 160);
          const sg = new THREE.SphereGeometry(s, 16, 16);
          const sm = new THREE.MeshBasicMaterial({});
          const s1 = new THREE.Mesh(sg, sm);
          s1.position.copy(a);
          group.add(s1);
          const s2 = new THREE.Mesh(sg, sm);
          s2.position.copy(b);
          group.add(s2);

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

          const updateLabel = () => {
            const mid = a.clone().add(b).multiplyScalar(0.5).project(this.iges.camera);
            const w = wrap.clientWidth,
              h = wrap.clientHeight;
            const x = (mid.x * 0.5 + 0.5) * w;
            const y = (-mid.y * 0.5 + 0.5) * h;
            lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
            lbl.textContent = `${a.distanceTo(b).toFixed(2)} mm`;
          };

          group.userData.update = updateLabel;
          group.userData.dispose = () => lbl.remove();
          updateLabel();

          this.iges.measure.group.add(group);
        },

        /* ===== Lifecycle ===== */
        init() {
          // kalau nanti ada modal lain buat share, Escape handler bisa ditaruh di sini
          window.addEventListener('beforeunload', () => this.disposeCad());
          this._onMaskMouseMove = (ev) => this.handleMaskMouseMove(ev);
          this._onMaskMouseUp = () => this.handleMaskMouseUp();
        },

        /* ===== UI ===== */
        toggleSection(c) {
          const i = this.openSections.indexOf(c);
          if (i > -1) this.openSections.splice(i, 1);
          else this.openSections.push(c);
        },

        selectFile(file) {
          // simpan posisi stamp file sebelumnya
          if (this.selectedFile) {
            this.saveStampConfigForCurrent();
          }

          // cleanup viewer sebelumnya
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

          // load konfigurasi posisi stamp untuk file yang dipilih
          this.loadStampConfigFor(this.selectedFile);

          // ==== RESET & LOAD BLOCKS DARI DB UNTUK FILE INI ====
          this.masks = [];
          this.nextMaskId = 1;

          // kalau backend sudah kirim blocks_position (array), pakai itu
          const blocks = file.blocks_position || [];

          blocks.forEach((b, idx) => {
            const idNum = (() => {
              if (!b.id) return idx + 1;
              const match = b.id.toString().match(/\d+$/);
              return match ? parseInt(match[0], 10) : idx + 1;
            })();

            this.masks.push({
              id: idNum,
              visible: true,
              editable: true,
              active: idx === 0, // blok pertama jadi aktif

              x: b.x ?? 150,
              y: b.y ?? 150,
              width: b.width ?? 250,
              height: b.height ?? 60,
              rotation: b.rotation ?? 0,

              _mode: null,
              _edge: null,
              _startX: 0,
              _startY: 0,
              _startLeft: 0,
              _startTop: 0,
              _startW: 0,
              _startH: 0,
              _centerX: 0,
              _centerY: 0,
              _startRot: 0,
            });

            this.nextMaskId = Math.max(this.nextMaskId, idNum + 1);
          });

          // kalau nggak ada blok dari DB, boleh otomatis buat 1 blok awal (opsional)
          // if (this.masks.length === 0) this.addMask();


          this.$nextTick(() => {
            if (this.isTiff(file?.name)) {
              this.renderTiff(file.url);
            } else if (this.isCad(file?.name)) {
              this.renderCadOcct(file);
            } else if (this.isHpgl(file?.name)) {
              this.renderHpgl(file.url);
            } else if (this.isPdf(file?.name)) {
              this.renderPdf(file.url);
            }
          });
        },

        addPkgActivity(action, user, note = '') {
          this.pkg.activityLogs.unshift({
            action,
            user,
            note: note || '',
            time: new Date().toLocaleString()
          });
        },

        // Helper status (kalau nanti mau dipakai di Share)
        isWaiting() {
          return (this.pkg.status || '').toLowerCase() === 'waiting';
        },

        /* ===== render CAD via occt-import-js (STEP/IGES + fallback IGES) ===== */
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

            const scene = new THREE.Scene();
            scene.background = null;

            const wrap = this.$refs.igesWrap;
            const width = wrap?.clientWidth || 800;
            const height = wrap?.clientHeight || 500;

            const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 10000);
            camera.position.set(250, 200, 250);

            const renderer = new THREE.WebGLRenderer({
              antialias: true,
              alpha: true
            });
            renderer.setPixelRatio(window.devicePixelRatio || 1);
            renderer.setSize(width, height);
            wrap.appendChild(renderer.domElement);
            wrap.style.position = 'relative';
            wrap.style.overflow = 'hidden';

            const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 0.8);
            hemi.position.set(0, 200, 0);
            scene.add(hemi);

            const dir = new THREE.DirectionalLight(0xffffff, 0.9);
            dir.position.set(150, 200, 100);
            scene.add(dir);

            const controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;

            const resp = await fetch(url, {
              cache: 'no-store',
              credentials: 'same-origin'
            });
            if (!resp.ok) throw new Error('Failed to fetch CAD file');
            const mainBuf = new Uint8Array(await resp.arrayBuffer());

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
              if (!res || !res.success) {
                const igesFile = this._findIgesSibling(fileObj);
                if (igesFile?.url) {
                  const igResp = await fetch(igesFile.url, {
                    cache: 'no-store',
                    credentials: 'same-origin'
                  });
                  if (!igResp.ok) throw new Error('Failed to fetch fallback IGES file');
                  const igBuf = new Uint8Array(await igResp.arrayBuffer());
                  res = occt.ReadIgesFile(igBuf, params);
                }
              }
            } else {
              res = occt.ReadIgesFile(mainBuf, params);
            }

            if (!res || !res.success) {
              const msg = res?.error || res?.message || 'File is not a valid STEP/IGES or is not supported by OCCT.';
              throw new Error('OCCT failed to parse file: ' + msg);
            }

            const group = this._buildThreeFromOcct(res, THREE);
            scene.add(group);

            this.iges.rootModel = group;
            this.iges.scene = scene;
            this.iges.camera = camera;
            this.iges.renderer = renderer;
            this.iges.controls = controls;
            this.iges.THREE = THREE;

            this._cacheOriginalMaterials(group, THREE);

            const box = new THREE.Box3().setFromObject(group);
            const size = new THREE.Vector3();
            const center = new THREE.Vector3();
            box.getSize(size);
            box.getCenter(center);

            const maxDim = Math.max(size.x, size.y, size.z) || 100;
            const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
            camera.position.copy(
              center.clone().add(new THREE.Vector3(1, 1, 1).normalize().multiplyScalar(fitDist * 1.6))
            );
            camera.near = Math.max(maxDim / 100, 0.1);
            camera.far = Math.max(maxDim * 100, 1000);
            camera.updateProjectionMatrix();
            controls.target.copy(center);
            controls.update();

            const animate = () => {
              controls.update();
              renderer.render(scene, camera);
              const g = this.iges.measure.group;
              if (g) g.children.forEach(ch => ch.userData?.update?.());
              this.iges.animId = requestAnimationFrame(animate);
            };
            animate();

            this._onIgesResize = () => {
              const w = this.$refs.igesWrap?.clientWidth || 800;
              const h = this.$refs.igesWrap?.clientHeight || 500;
              camera.aspect = w / h;
              camera.updateProjectionMatrix();
              renderer.setSize(w, h);
            };
            window.addEventListener('resize', this._onIgesResize);

            this.setDisplayStyle('shaded-edges');
          } catch (e) {
            console.error(e);
            this.iges.error = e?.message || 'Failed to render CAD file';
          } finally {
            this.iges.loading = false;
          }
        },

        // ===== White block helper (MULTI) =====
        addMask() {
          const m = {
            id: this.nextMaskId++,
            visible: true,
            editable: true,
            active: true,

            x: 150,
            y: 150,
            width: 250,
            height: 60,
            rotation: 0,

            _mode: null,
            _edge: null,
            _startX: 0,
            _startY: 0,
            _startLeft: 0,
            _startTop: 0,
            _startW: 0,
            _startH: 0,
            _centerX: 0,
            _centerY: 0,
            _startRot: 0,
          };

          // matikan blok lain lalu aktifkan blok baru
          this.masks.forEach(mm => (mm.active = false));
          this.masks.push(m);
        },

        getActiveMask() {
          return this.masks.find(m => m.active) || null;
        },

        activateMask(mask) {
          this.masks.forEach(m => (m.active = (m.id === mask.id)));
        },

        deactivateMask() {
          this.masks.forEach(m => {
            m.active = false;
            m._mode = null;
          });
        },

        maskStyle(mask) {
          return `
          left:${mask.x}px;
          top:${mask.y}px;
          width:${mask.width}px;
          height:${mask.height}px;
          transform: rotate(${mask.rotation}deg);
          transform-origin:center center;
        `;
        },

        onMaskMouseDown(e, mask) {
          this.activateMask(mask);
          if (!mask.editable) return;

          mask._mode = 'move';
          mask._startX = e.clientX;
          mask._startY = e.clientY;
          mask._startLeft = mask.x;
          mask._startTop = mask.y;

          window.addEventListener('mousemove', this._onMaskMouseMove);
          window.addEventListener('mouseup', this._onMaskMouseUp);
        },

        startMaskResize(e, edge, mask) {
          this.activateMask(mask);
          if (!mask.editable) return;

          mask._mode = 'resize';
          mask._edge = edge;
          mask._startX = e.clientX;
          mask._startY = e.clientY;
          mask._startLeft = mask.x;
          mask._startTop = mask.y;
          mask._startW = mask.width;
          mask._startH = mask.height;

          window.addEventListener('mousemove', this._onMaskMouseMove);
          window.addEventListener('mouseup', this._onMaskMouseUp);
        },

        startMaskRotate(e, mask) {
          this.activateMask(mask);
          if (!mask.editable) return;

          const rect = e.currentTarget.parentElement.getBoundingClientRect();
          const cx = rect.left + rect.width / 2;
          const cy = rect.top + rect.height / 2;

          mask._mode = 'rotate';
          mask._centerX = cx;
          mask._centerY = cy;
          mask._startRot = mask.rotation;

          window.addEventListener('mousemove', this._onMaskMouseMove);
          window.addEventListener('mouseup', this._onMaskMouseUp);
        },

        handleMaskMouseMove(ev) {
          const mask = this.masks.find(m => m._mode);
          if (!mask) return;

          const dx = ev.clientX - mask._startX;
          const dy = ev.clientY - mask._startY;

          if (mask._mode === 'move') {
            mask.x = mask._startLeft + dx;
            mask.y = mask._startTop + dy;

          } else if (mask._mode === 'resize') {
            const minW = 20,
              minH = 20;

            if (mask._edge.includes('e')) {
              mask.width = Math.max(minW, mask._startW + dx);
            }
            if (mask._edge.includes('s')) {
              mask.height = Math.max(minH, mask._startH + dy);
            }
            if (mask._edge.includes('w')) {
              mask.x = mask._startLeft + dx;
              mask.width = Math.max(minW, mask._startW - dx);
            }
            if (mask._edge.includes('n')) {
              mask.y = mask._startTop + dy;
              mask.height = Math.max(minH, mask._startH - dy);
            }

          } else if (mask._mode === 'rotate') {
            const angle = Math.atan2(ev.clientY - mask._centerY, ev.clientX - mask._centerX);
            mask.rotation = (angle * 180 / Math.PI) + 90;
          }
        },

        handleMaskMouseUp() {
          const mask = this.masks.find(m => m._mode);
          if (mask) mask._mode = null;

          window.removeEventListener('mousemove', this._onMaskMouseMove);
          window.removeEventListener('mouseup', this._onMaskMouseUp);
        },

        removeActiveMask() {
          const active = this.getActiveMask();
          if (!active) return;
          this.masks = this.masks.filter(m => m.id !== active.id);
        },

        updateBlocksUrlTemplate: `{{ route('share.files.updateBlocks', ['fileId' => '__FILE_ID__']) }}`,

        async saveBlocks(blocks) {
          if (!this.selectedFile?.id) return;

          const url = this.updateBlocksUrlTemplate.replace('__FILE_ID__', this.selectedFile.id);

          try {
            const res = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify({
                blocks
              }),
            });

            const json = await res.json();

            if (!res.ok) {
              throw new Error(json.message || 'Failed to save blocks position');
            }

            toastSuccess('Saved', json.message || 'Blocks position saved.');
          } catch (e) {
            console.error(e);
            toastError('Error', e.message || 'Failed to save blocks position');
          }
        },
        saveCurrentMask() {
          if (!this.selectedFile?.id) {
            toastWarning('No file selected', 'Pilih file dulu sebelum menyimpan block.');
            return;
          }

          if (this.masks.length === 0) {
            toastWarning('No block', 'Tambah block dulu sebelum menyimpan.');
            return;
          }

          // kalau mau pakai hanya blok aktif:
          // const active = this.getActiveMask();
          // if (!active) { ... }

          const blocks = this.masks.map((m, idx) => ({
            id: m.id ? `blk-${m.id}` : `blk-${idx + 1}`,
            x: m.x,
            y: m.y,
            width: m.width,
            height: m.height,
            rotation: m.rotation,
          }));

          this.saveBlocks(blocks);
        }






      }
    }
    // ========== SHARE DARI HALAMAN DETAIL ==========
    document.addEventListener('DOMContentLoaded'),
      function() {
        const $ = window.jQuery;
        if (!$) return; // jaga-jaga kalau jQuery belum ada

        const $shareModal = $('#shareModal');
        const $supplierListContainer = $('#supplierListContainer');
        const $hiddenPackageId = $('#hiddenPackageId');
        const $btnSaveShare = $('#btnSaveShare');

        let selectedSupplier = [];

        function renderSelectSuppliers() {
          const $container = $('#selectedSupplierContainer');
          $container.empty();

          selectedSupplier.forEach(supplier => {
            const item = $(`
          <div class="flex items-center justify-between 
                      bg-gray-200 dark:bg-gray-700 
                      text-gray-700 dark:text-gray-200 
                      px-3 py-2 rounded-md mb-1 transition-colors">
            <span class="text-sm">${supplier.text}</span>
            <button 
              type="button" 
              class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-gray-300 
                     remove-select transition"
              data-id="${supplier.id}">
              &times;
            </button>
          </div>
        `);

            $container.append(item);
          });
        }

        function loadSuppliers() {
          $supplierListContainer.empty();

          $supplierListContainer.select2({
            dropdownParent: $('#shareModal'),
            width: '100%',
            placeholder: 'Select suppliers...',
            allowClear: true,
            ajax: {
              url: "{{ route('share.getSuppliers') }}",
              method: 'GET',
              dataType: 'json',
              delay: 250,
              data: function(params) {
                return {
                  q: params.term || '',
                  page: params.page || 1
                };
              },
              processResults: function(data) {
                const formatted = data.map(item => ({
                  id: item.id,
                  text: item.code
                }));
                return {
                  results: formatted
                };
              },
              cache: true
            }
          });

          $supplierListContainer.off('select2:select').on('select2:select', function(e) {
            const data = e.params.data;
            const exists = selectedSupplier.find(r => r.id === data.id);

            if (!exists) {
              selectedSupplier.push(data);
              renderSelectSuppliers();
            }

            // reset dropdown setelah pilih
            $supplierListContainer.val(null).trigger('change');
          });
        }

        // 🔹 tombol di halaman detail untuk buka modal share
        // pastikan Tuan punya button dengan id="btnOpenShareFromDetail" dan data-id="{{ $revisionId }}"
        $('#btnOpenShareFromDetail').on('click', function() {
          const packageId = $(this).data('id');
          if (!packageId) {
            toastError('Error', 'Revision / package ID tidak ditemukan.');
            return;
          }

          selectedSupplier = [];
          renderSelectSuppliers();

          $hiddenPackageId.val(packageId);
          loadSuppliers();
          $btnSaveShare.prop('disabled', false).text('Share');

          $shareModal.show();
        });

        // tombol close (ikon X & tombol Cancel)
        $('body').on('click', '.btn-close-modal', function() {
          $shareModal.hide();
        });

        // klik backdrop tutup modal
        $shareModal.on('click', function(e) {
          if ($(e.target).is($shareModal)) {
            $shareModal.hide();
          }
        });

        // hapus supplier yang terpilih (badge kecil X)
        $(document).on('click', '.remove-select', function() {
          const id = $(this).data('id');
          $(this).parent().fadeOut(150, function() {
            selectedSupplier = selectedSupplier.filter(r => r.id != id);
            renderSelectSuppliers();
          });
        });

        // klik Share -> kirim ke controller yang sama (ShareController@saveShare)
        $btnSaveShare.on('click', function() {
          const $this = $(this);
          const packageId = $hiddenPackageId.val();
          // Ambil ID dari array object selectedSupplier
          const selectedSupplierIds = selectedSupplier.map(r => r.id);

          if (!packageId) {
            toastError('Error', 'Package ID tidak ditemukan.');
            return;
          }

          if (selectedSupplierIds.length === 0) {
            toastWarning('No supplier', 'Pilih minimal satu supplier.');
            return;
          }

          $this.prop('disabled', true).text('Sharing...');

          $.ajax({
            url: '{{ route("share.save") }}', // Pastikan route ini mengarah ke ShareController@saveShare
            type: 'POST',
            data: {
              _token: $('meta[name="csrf-token"]').attr('content'),
              package_id: packageId,
              supplier_ids: selectedSupplierIds
              // expired_date: '2025-12-31' // (Opsional) Jika nanti ada input date di modal
            },
            dataType: 'json',
            success: function(response) {
              $shareModal.hide();

              // Tampilkan notifikasi sukses
              toastSuccess('Shared', response.message || 'Package shared successfully!');

              // Opsional: Reload halaman agar list "Shared To" di sidebar terupdate
              setTimeout(() => {
                window.location.reload();
              }, 1500);
            },
            error: function(xhr) {
              console.error('Failed to share:', xhr.responseText);
              let errorMsg = 'Failed to share package.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
              }
              toastError('Error', errorMsg);
            },
            complete: function() {
              $this.prop('disabled', false).text('Share');
            }

          });
        });
      }
  </script>

  @endpush