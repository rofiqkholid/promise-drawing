@extends('layouts.app')
@section('title', 'Approval Detail - PROMISE')
@section('header-title', 'Approval Detail')

@section('content')

<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="approvalDetail()" x-init="init()">
  <!-- TOP ROW: Metadata (8) + Activity Log (4) -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-stretch">
    <!-- Approval Metadata (8 cols) -->
    <div x-ref="metaCard"
         class="lg:col-span-8 min-h-0 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden h-full flex flex-col box-border">
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
          <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
          Approval Metadata
        </h2>
      </div>

      <!-- Body (compact) -->
      <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-sm">
        <div>
          <dt class="text-xs text-gray-500 dark:text-gray-400 font-medium">Customer</dt>
          <dd class="mt-0.5 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.customer"></dd>
        </div>
        <div>
          <dt class="text-xs text-gray-500 dark:text-gray-400 font-medium">Model</dt>
          <dd class="mt-0.5 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.model"></dd>
        </div>
        <div>
          <dt class="text-xs text-gray-500 dark:text-gray-400 font-medium">Part No</dt>
          <dd class="mt-0.5 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.part_no"></dd>
        </div>
        <div>
          <dt class="text-xs text-gray-500 dark:text-gray-400 font-medium">Revision</dt>
          <dd class="mt-0.5 text-gray-900 dark:text-gray-100">
            <span x-text="pkg.metadata.revision"></span>
            <template x-if="currentRevision">
              <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-800" x-text="currentRevision.label"></span>
            </template>
          </dd>
        </div>
        <div>
          <dt class="text-xs text-gray-500 dark:text-gray-400 font-medium">Status</dt>
          <dd class="mt-0.5 text-gray-900 dark:text-gray-100" x-text="pkg.status"></dd>
        </div>
      </div>

      <!-- Footer (Approve / Reject) -->
      <div class="mt-auto px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
        <button @click="rejectPackage()" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm">
          <i class="fa-solid fa-circle-xmark mr-2"></i> Reject
        </button>
        <button @click="approvePackage()" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-sm">
          <i class="fa-solid fa-circle-check mr-2"></i> Approve
        </button>
      </div>
    </div>

    <!-- Activity Log (4 cols) -->
    <div x-ref="actCard"
         class="lg:col-span-4 min-h-0 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden h-full flex flex-col box-border"
         :style="`height:${actCardH}px`">
      <!-- Header -->
      <div x-ref="actHeader"
           class="p-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between sticky top-0 z-10">
        <div class="flex items-center">
          <i class="fa-solid fa-clock-rotate-left mr-2 text-gray-500 dark:text-gray-400"></i>
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Activity Log</span>
        </div>
        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full"
              x-text="`${pkg.activityLogs?.length || 0} events`"></span>
      </div>

      <!-- List (height locked; scroll inside) -->
      <div x-ref="actList"
           class="overflow-y-auto p-2 space-y-2"
           :style="`height:${actListH}px`"
           role="log" aria-label="Activity Log">
        <template x-for="(item, idx) in (pkg.activityLogs || [])" :key="idx">
          <div class="flex items-start gap-3 p-3 rounded-md bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
            <div class="mt-0.5">
              <template x-if="item.action === 'uploaded'"><i class="fa-solid fa-upload text-blue-500"></i></template>
              <template x-if="item.action === 'approved'"><i class="fa-solid fa-circle-check text-green-500"></i></template>
              <template x-if="item.action === 'rejected'"><i class="fa-solid fa-circle-xmark text-red-500"></i></template>
              <template x-if="!['uploaded','approved','rejected'].includes(item.action)"><i class="fa-solid fa-circle-info text-gray-500"></i></template>
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
          <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No activity yet for this package.</p>
        </template>
      </div>
    </div>
  </div>
  <!-- /TOP ROW -->

  <!-- MAIN GRID BELOW -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sidebar: Revision + File Groups -->
    <div class="lg:col-span-1 space-y-6">
      <!-- Revision History -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <button @click="toggleSection('rev')" class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" :aria-expanded="openSections.includes('rev')">
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
              class="flex items-center justify-between p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 border border-transparent">
              <div class="min-w-0">
                <p class="text-sm truncate" x-text="rev.label"></p>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate" x-text="rev.time"></p>
              </div>
              <span class="text-[11px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full" x-text="`${(rev.files['2d']?.length||0)+(rev.files['3d']?.length||0)+(rev.files['ecn']?.length||0)} files`"></span>
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
          <button @click="toggleSection('{{$category}}')" class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" :aria-expanded="openSections.includes('{{$category}}')">
            <div class="flex items-center">
              <i class="fa-solid {{$icon}} mr-3 text-gray-500 dark:text-gray-400"></i>
              <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
            </div>
            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full" x-text="`${(currentFiles['{{$category}}']?.length || 0)} files`"></span>
            <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform" :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
          </button>
          <div x-show="openSections.includes('{{$category}}')" x-collapse class="p-2 max-h-72 overflow-y-auto">
            <template x-for="file in (currentFiles['{{$category}}'] || [])" :key="file.name">
              <div @click="selectFile(file)" :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}" class="flex items-center p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" role="button" tabindex="0" @keydown.enter="selectFile(file)">
                <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 transition-colors group-hover:text-blue-500"></i>
                <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
              </div>
            </template>
            <template x-if="(currentFiles['{{$category}}'] || []).length === 0">
              <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No files available.</p>
            </template>
          </div>
        </div>
      @php
        }
      @endphp

      {{ renderFileGroup('2D Drawings', 'fa-drafting-compass', '2d') }}
      {{ renderFileGroup('3D Models', 'fa-cubes', '3d') }}
      {{ renderFileGroup('ECN / Documents', 'fa-file-lines', 'ecn') }}
    </div>

    <!-- Main Panel: File Preview -->
    <div class="lg:col-span-2">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- No File Selected -->
        <div x-show="!selectedFile" class="flex flex-col items-center justify-center h-96 p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
          <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500"></i>
          <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
        </div>

        <!-- File Preview -->
        <div x-show="selectedFile" x-transition.opacity class="p-6">
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedFile?.name"></h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Last updated: {{ now()->format('M d, Y H:i') }}</p>
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

  <!-- ========================== MODALS ========================== -->

  <!-- APPROVE MODAL -->
  <div x-show="showApproveModal"
       x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center"
       aria-modal="true" role="dialog">
    <!-- overlay -->
    <div class="absolute inset-0 bg-black/40" @click="closeApproveModal()"></div>

    <!-- dialog -->
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Confirm Approve</h3>
        <button class="text-gray-400 hover:text-gray-600" @click="closeApproveModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">
        Are you sure you want to Approve this package?
        <span class="font-semibold">Approved</span>?
      </div>

      <div class="px-5 py-4 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
        <button @click="closeApproveModal()" class="px-3 py-1.5 rounded-md border text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button @click="confirmApprove()" :disabled="processing"
                class="px-3 py-1.5 rounded-md bg-green-600 text-white text-sm hover:bg-green-700 disabled:opacity-60">
          <span x-show="!processing">Yes, Approve</span>
          <span x-show="processing">Processing…</span>
        </button>
      </div>
    </div>
  </div>

  <!-- REJECT MODAL (dengan note wajib) -->
  <div x-show="showRejectModal"
       x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center"
       aria-modal="true" role="dialog">
    <!-- overlay -->
    <div class="absolute inset-0 bg-black/40" @click="closeRejectModal()"></div>

    <!-- dialog -->
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Confirm Reject</h3>
        <button class="text-gray-400 hover:text-gray-600" @click="closeRejectModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="px-5 pt-4 text-sm text-gray-700 dark:text-gray-200">
        Please provide a reason for rejecting this packaage.
      </div>

      <div class="px-5 pb-2">
        <textarea x-model.trim="rejectNote" rows="4" placeholder="Enter rejection note here..."
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
        <p class="mt-1 text-xs text-red-600" x-show="rejectNoteError">Note is required</p>
      </div>

      <div class="px-5 py-4 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
        <button @click="closeRejectModal()" class="px-3 py-1.5 rounded-md border text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button @click="confirmReject()"
                :disabled="processing || rejectNote.length === 0"
                class="px-3 py-1.5 rounded-md bg-red-600 text-white text-sm hover:bg-red-700 disabled:opacity-60">
          <span x-show="!processing">Yes, Reject</span>
          <span x-show="processing">Processing…</span>
        </button>
      </div>
    </div>
  </div>
  <!-- ======================== /MODALS ========================== -->

</div>

<style>
  [x-collapse] { @apply overflow-hidden transition-all duration-300 ease-in-out; }
  .preview-area { @apply bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center; }
</style>

@endsection

@push('scripts')
<script>
function approvalDetail() {
  return {
    approvalId: {{ $approvalId }},

    // ---- state ----
    pkg: {
      status: 'Waiting',
      metadata: {},
      files: { '2d': [], '3d': [], 'ecn': [] },
      revisions: [],
      activityLogs: []
    },

    selectedRevisionId: null,
    get currentRevision() { return this.pkg.revisions.find(r => r.id === this.selectedRevisionId) || null; },
    get currentFiles() { return this.pkg.files; },

    selectedFile: null,
    openSections: [],

    // height locks
    actCardH: 0,
    actListH: 0,
    _resizeObs: null,

    // modal states
    showApproveModal: false,
    showRejectModal: false,
    processing: false,
    rejectNote: '',
    rejectNoteError: false,

    // --- Toast helper (pakai SweetAlert2 kalau tersedia; fallback alert) ---
    toast(title = 'Done', icon = 'success') {
      if (window.Swal) {
        const T = Swal.mixin({
          toast: true, position: 'top-end', showConfirmButton: false,
          timer: 2000, timerProgressBar: true,
          didOpen: (t) => { t.addEventListener('mouseenter', Swal.stopTimer); t.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        T.fire({ icon, title });
      } else {
        // fallback sederhana
        console.log(`[${icon}] ${title}`);
      }
    },

    // lock heights to metadata
    syncHeights() {
      this.$nextTick(() => {
        const metaH   = this.$refs.metaCard?.getBoundingClientRect().height || 0;
        const headH   = this.$refs.actHeader?.getBoundingClientRect().height || 0;
        // lock activity card to EXACT metadata height
        this.actCardH = Math.max(Math.floor(metaH), 200);
        this.actListH = Math.max(Math.floor(metaH - headH - 1), 120);
      });
    },
    setupObservers() {
      if (window.ResizeObserver && this.$refs.metaCard) {
        this._resizeObs = new ResizeObserver(() => this.syncHeights());
        this._resizeObs.observe(this.$refs.metaCard);
      }
      window.addEventListener('resize', this.syncHeights);
    },

    // ---- lifecycle ----
    init() {
      this.loadData();
      if (this.pkg.revisions.length > 0) this.setRevision(this.pkg.revisions[0]);
      this.syncHeights();
      this.setupObservers();
    },

    // ---- data ----
    loadData() {
      // dummy metadata
      this.pkg.metadata = { customer: 'MMKI', model: '4L45W', part_no: '5251D644', revision: 'Rev Base' };
      this.pkg.status = 'Waiting';
      // dummy revisions + files
      this.pkg.revisions = [
        { id: 2, label: 'Rev-2', time: '2025-10-12 13:47',
          files: {
            '2d': [{ name: '5251D644_drawing_assy_rev2.pdf', url: 'https://wiratech.co.id/wp-content/uploads/2014/12/1.gif' },
                   { name: '5251D644_component_01_rev2.dwg', url: '#' }],
            '3d': [{ name: '5251D644_assy_rev2.step', url: '#' }],
            'ecn': [{ name: 'ECN-2025-018.pdf', url: 'https://via.placeholder.com/800x600.png?text=ECN+018' }]
          }
        },
        { id: 1, label: 'Rev-1', time: '2025-10-10 09:20',
          files: {
            '2d': [{ name: '5251D644_drawing_assy.pdf', url: 'https://via.placeholder.com/800x600.png?text=Assy+PDF' },
                   { name: '5251D644_component_01.dwg', url: '#' },
                   { name: '5251D644_component_02.dwg', url: '#' }],
            '3d': [{ name: '5251D644_assy.step', url: '#' }, { name: '5251D644_solid.x_t', url: '#' }],
            'ecn': [{ name: 'ECN-2025-001.pdf', url: 'https://via.placeholder.com/800x600.png?text=ECN+001' },
                    { name: 'material_spec.xlsx', url: '#' },
                    { name: 'inspection_report.pdf', url: 'https://via.placeholder.com/800x600.png?text=Report+PDF' }]
          }
        }
      ];
      const now = new Date().toLocaleString();
      this.pkg.activityLogs = [{ action: 'uploaded', user: 'Kusno', time: now, note: 'Initial package upload' }];

      this.syncHeights();
    },

    // ---- modal triggers ----
    approvePackage() { // buka modal approve
      this.showApproveModal = true;
    },
    rejectPackage() {  // buka modal reject
      this.rejectNote = '';
      this.rejectNoteError = false;
      this.showRejectModal = true;
    },
    closeApproveModal() {
      if (this.processing) return;
      this.showApproveModal = false;
    },
    closeRejectModal() {
      if (this.processing) return;
      this.showRejectModal = false;
    },

    // ---- confirm actions ----
    async confirmApprove() {
      try {
        this.processing = true;

        // TODO: panggil API server (opsional)
        // await axios.post(`/approvals/${this.approvalId}/approve`);

        // update state
        this.pkg.status = 'Approved';
        this.addPkgActivity('approved', '{{ auth()->user()->name ?? "Reviewer" }}');

        this.showApproveModal = false;
        this.toast('Package approved', 'success');
      } catch (err) {
        console.error(err);
        alert((err && (err.response?.data?.message || err.message)) || 'Approve failed');
      } finally {
        this.processing = false;
        this.syncHeights();
      }
    },

    async confirmReject() {
      if (!this.rejectNote || this.rejectNote.trim().length === 0) {
        this.rejectNoteError = true;
        return;
      }
      this.rejectNoteError = false;

      try {
        this.processing = true;

        // TODO: panggil API server (opsional)
        // await axios.post(`/approvals/${this.approvalId}/reject`, { note: this.rejectNote });

        // update state
        this.pkg.status = 'Rejected';
        this.addPkgActivity('rejected', '{{ auth()->user()->name ?? "Reviewer" }}', this.rejectNote);

        this.showRejectModal = false;
        this.toast('Package rejected', 'error');
      } catch (err) {
        console.error(err);
        alert((err && (err.response?.data?.message || err.message)) || 'Reject failed');
      } finally {
        this.processing = false;
        this.syncHeights();
      }
    },

    // ---- other actions ----
    setRevision(rev) {
      if (!rev) return;
      this.selectedRevisionId = rev.id;
      this.pkg.files = JSON.parse(JSON.stringify(rev.files || { '2d':[], '3d':[], 'ecn':[] }));
      this.pkg.metadata.revision = rev.label;
      this.selectedFile = null;
      this.syncHeights();
    },

    toggleSection(category) {
      const i = this.openSections.indexOf(category);
      if (i > -1) this.openSections.splice(i, 1);
      else this.openSections.push(category);
    },

    selectFile(file) { this.selectedFile = { ...file }; },

    addPkgActivity(action, user, note = '') {
      this.pkg.activityLogs.unshift({ action, user, note: note || '', time: new Date().toLocaleString() });
      this.syncHeights();
    },

    isImage(fileName) {
      return fileName && ['png','jpg','jpeg','gif','pdf'].includes(fileName.split('.').pop().toLowerCase());
    }
  }
}
</script>
@endpush
