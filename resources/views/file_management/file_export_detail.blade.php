@extends('layouts.app')
@section('title', 'Download Detail - File Manager')
@section('header-title', 'File Manager/Download Detail')

@section('content')
<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="downloadDetail()" x-init="init()">
    <!-- Header & Metadata -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fa-solid fa-box-archive mr-3 text-blue-600"></i> Package Metadata
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('file-manager.export') }}"
                   class="inline-flex items-center text-sm px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to List
                </a>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Customer</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.customer"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Model</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.model"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Part No</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.part_no"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Revision (Current)</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">
                    <span x-text="pkg.metadata.revision"></span>
                    <template x-if="currentRevision">
                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800" x-text="currentRevision.label"></span>
                    </template>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Status</dt>
                <dd class="mt-1">
                    <span :class="{
                        'px-2 py-0.5 text-xs rounded-full font-medium': true,
                        'bg-yellow-100 text-yellow-800': pkg.status==='Waiting',
                        'bg-green-100 text-green-800': pkg.status==='Available',
                        'bg-gray-100 text-gray-800': !['Waiting','Available'].includes(pkg.status)
                    }" x-text="pkg.status"></span>
                </dd>
            </div>
            
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 rounded-b-lg">
    <div class="flex justify-end">
        <button
            @click="downloadMain()"
            class="inline-flex items-center text-sm px-3 py-2 rounded-md
                   bg-blue-600 text-white hover:bg-blue-700
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            <i class="fa-solid fa-download mr-2"></i>
            Download
        </button>
    </div>
</div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar: Revision + File Groups -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Revision History -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button @click="toggleSection('rev')" class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" :aria-expanded="openSections.includes('rev')">
                    <div class="flex items-center">
                        <i class="fa-solid fa-code-branch mr-3 text-gray-500 dark:text-gray-400"></i>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Revision History</span>
                    </div>
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full" x-text="`${pkg.revisions?.length || 0} revs`"></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform" :class="{'rotate-180': openSections.includes('rev')}"></i>
                </button>
                <div x-show="openSections.includes('rev')" x-collapse class="p-2 max-h-72 overflow-y-auto">
                    <template x-for="rev in pkg.revisions" :key="rev.id">
                        <div
                            @click="setRevision(rev)"
                            :class="{
                                'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium border-blue-200 dark:border-blue-800': currentRevision && currentRevision.id === rev.id,
                                'text-gray-900 dark:text-gray-100': !(currentRevision && currentRevision.id === rev.id)
                            }"
                            class="flex items-center justify-between p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors border border-transparent">
                            <div class="min-w-0">
                                <p class="text-sm truncate" x-text="rev.label"></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate" x-text="rev.time"></p>
                            </div>
                            <span class="text-[11px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full"
                                  x-text="`${(rev.files['2d']?.length||0)+(rev.files['3d']?.length||0)+(rev.files['ecn']?.length||0)} files`"></span>
                        </div>
                    </template>
                    <template x-if="(pkg.revisions || []).length === 0">
                        <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No revisions yet.</p>
                    </template>
                </div>
            </div>

            @php
                function renderFileGroup($title, $icon, $category) {
            @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button @click="toggleSection('{{$category}}')" class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" :aria-expanded="openSections.includes('{{$category}}')">
                        <div class="flex items-center">
                            <i class="fa-solid {{$icon}} mr-3 text-gray-500 dark:text-gray-400"></i>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
                        </div>
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full" x-text="`${(currentFiles['{{$category}}']?.length || 0)} files`"></span>
                        <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform" :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
                    </button>
                    <div x-show="openSections.includes('{{$category}}')" x-collapse class="p-2 max-h-72 overflow-y-auto">
                        <template x-for="file in (currentFiles['{{$category}}'] || [])" :key="file.name">
                            <div class="group flex items-center justify-between p-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div @click="selectFile(file)" :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}" class="flex items-center rounded-md cursor-pointer pr-2">
                                    <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 group-hover:text-blue-500"></i>
                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
                                </div>
                                <!-- per-file download (boleh tetap) -->
                                <button @click.stop="downloadFile(file)" class="text-xs inline-flex items-center gap-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded">
                                    <i class="fa-solid fa-download"></i> Download
                                </button>
                            </div>
                        </template>
                        <template x-if="(currentFiles['{{$category}}'] || []).length === 0">
                            <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No files available.</p>
                        </template>
                    </div>
                </div>
            @php } @endphp

            {{ renderFileGroup('2D Drawings', 'fa-drafting-compass', '2d') }}
            {{ renderFileGroup('3D Models', 'fa-cubes', '3d') }}
            {{ renderFileGroup('ECN / Documents', 'fa-file-lines', 'ecn') }}
        </div>

        <!-- Main Panel: Preview -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div x-show="!selectedFile" class="flex flex-col items-center justify-center h-96 p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
                    <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500"></i>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Click a file from the left panel to preview.</p>
                </div>

                <div x-show="selectedFile" x-transition.opacity class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedFile?.name"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Package: <span x-text="currentRevision?.label || pkg.metadata.revision"></span></p>
                        </div>
                        <button @click="downloadFile(selectedFile)" class="inline-flex items-center gap-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md shadow-sm">
                            <i class="fa-solid fa-download fa-sm"></i> Download
                        </button>
                    </div>
                    <div class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center">
                        <template x-if="isImage(selectedFile?.name)">
                            <img :src="selectedFile?.url" alt="File Preview" class="max-w-full max-h-64 object-contain" loading="lazy">
                        </template>
                        <template x-if="!isImage(selectedFile?.name)">
                            <div class="text-center">
                                <i class="fa-solid fa-file text-6xl text-gray-400 dark:text-gray-500"></i>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Preview Unavailable</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">This file type is not supported for preview.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-collapse] { @apply overflow-hidden transition-all duration-300 ease-in-out; }
    .preview-area { @apply bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center; }
</style>
@endsection

@push('scripts')
<script>
function downloadDetail() {
  return {
    packageId: "{{ $id }}",

    pkg: {
      status: 'Waiting',
      metadata: {},
      files: { '2d': [], '3d': [], 'ecn': [] },
      revisions: []
    },

    selectedRevisionId: null,
    get currentRevision() { return this.pkg.revisions.find(r => r.id === this.selectedRevisionId) || null; },
    get currentFiles() { return this.pkg.files; },

    selectedFile: null,
    openSections: [],

    init() {
      this.loadData();
      if (this.pkg.revisions.length > 0) this.setRevision(this.pkg.revisions[0]);
    },

    loadData() {
      this.pkg.metadata = { customer: 'MMKI', model: '4L45W', part_no: '5251D644', revision: 'Rev Base' };
      this.pkg.status = 'Available';
      this.pkg.revisions = [
        {
          id: 2, label: 'Rev-2', time: '2025-10-12 13:47',
          files: {
            '2d': [
              { name: '5251D644_drawing_assy_rev2.pdf', url: 'https://via.placeholder.com/800x600.png?text=Assy+PDF+Rev2' },
              { name: '5251D644_component_01_rev2.dwg', url: '#' }
            ],
            '3d': [ { name: '5251D644_assy_rev2.step', url: '#' } ],
            'ecn': [ { name: 'ECN-2025-018.pdf', url: 'https://via.placeholder.com/800x600.png?text=ECN+018' } ]
          }
        },
        {
          id: 1, label: 'Rev-1', time: '2025-10-10 09:20',
          files: {
            '2d': [
              { name: '5251D644_drawing_assy.pdf', url: 'https://via.placeholder.com/800x600.png?text=Assy+PDF' },
              { name: '5251D644_component_01.dwg', url: '#' },
              { name: '5251D644_component_02.dwg', url: '#' }
            ],
            '3d': [
              { name: '5251D644_assy.step', url: '#' },
              { name: '5251D644_solid.x_t', url: '#' }
            ],
            'ecn': [
              { name: 'ECN-2025-001.pdf', url: 'https://via.placeholder.com/800x600.png?text=ECN+001' },
              { name: 'material_spec.xlsx', url: '#' },
              { name: 'inspection_report.pdf', url: 'https://via.placeholder.com/800x600.png?text=Report+PDF' }
            ]
          }
        }
      ];
    },

    setRevision(rev) {
      if (!rev) return;
      this.selectedRevisionId = rev.id;
      this.pkg.files = JSON.parse(JSON.stringify(rev.files || { '2d':[], '3d':[], 'ecn':[] }));
      this.pkg.metadata.revision = rev.label;
      this.selectedFile = null;
    },

    toggleSection(category) {
      const i = this.openSections.indexOf(category);
      if (i > -1) this.openSections.splice(i, 1);
      else this.openSections.push(category);
    },

    selectFile(file) { this.selectedFile = { ...file }; },

    // tombol utama di header
    downloadMain() {
      if (this.selectedFile) {
        this.downloadFile(this.selectedFile);
      } else {
        this.downloadPackage(); // fallback: download paket (rev aktif)
      }
    },

    // download satu file
    downloadFile(file) {
      // TODO: ganti dengan route backend Tuan
      // window.location.href = `{{ url('/download/file') }}?pkg=${this.packageId}&rev=${this.selectedRevisionId}&name=${encodeURIComponent(file.name)}`
      alert(`Download file: ${file.name} (rev: ${this.currentRevision?.label || '-'})`);
    },

    // download semua file di revisi aktif (zip, misalnya)
    downloadPackage() {
      // TODO: ganti dengan route backend Tuan
      // window.location.href = `{{ url('/download/package') }}?pkg=${this.packageId}&rev=${this.selectedRevisionId}`
      alert(`Download package (rev: ${this.currentRevision?.label || '-'})`);
    },

    isImage(fileName) {
      return fileName && ['png','jpg','jpeg','gif','pdf'].includes(fileName.split('.').pop().toLowerCase());
    }
  }
}
</script>
@endpush
