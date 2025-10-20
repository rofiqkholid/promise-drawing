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
          <dd class="mt-0.5 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.revision"></dd>
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
    <!-- Sidebar: File Groups -->
    <div class="lg:col-span-1 space-y-6">
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
              <div @click="selectFile(file)" :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}" class="flex items-center p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" role="button" tabindex="0" @keydown.enter="selectFile(file)">
                <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 transition-colors group-hover:text-blue-500"></i>
                <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
              </div>
            </template>
            <template x-if="(pkg.files['{{$category}}'] || []).length === 0">
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
    <div class="absolute inset-0 bg-black/40" @click="closeApproveModal()"></div>

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

  <!-- REJECT MODAL -->
  <div x-show="showRejectModal"
       x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center"
       aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40" @click="closeRejectModal()"></div>

    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Confirm Reject</h3>
        <button class="text-gray-400 hover:text-gray-600" @click="closeRejectModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="px-5 pt-4 text-sm text-gray-700 dark:text-gray-200">
        Please provide a reason for rejecting this package.
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
{{-- SweetAlert2 + Toast helper (sama seperti halaman Customer) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function detectTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    return isDark ? {
      mode: 'dark',
      bg: 'rgba(30, 41, 59, 0.95)',
      fg: '#E5E7EB',
      border: 'rgba(71, 85, 105, 0.5)',
      progress: 'rgba(255,255,255,.9)',
      icon: { success:'#22c55e', error:'#ef4444', warning:'#f59e0b', info:'#3b82f6' }
    } : {
      mode: 'light',
      bg: 'rgba(255, 255, 255, 0.98)',
      fg: '#0f172a',
      border: 'rgba(226, 232, 240, 1)',
      progress: 'rgba(15,23,42,.8)',
      icon: { success:'#16a34a', error:'#dc2626', warning:'#d97706', info:'#2563eb' }
    };
  }

  const BaseToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2600,
    timerProgressBar: true,
    showClass: { popup: 'swal2-animate-toast-in' },
    hideClass: { popup: 'swal2-animate-toast-out' },
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });

  function renderToast({ icon='success', title='Success', text='' } = {}) {
    const t = detectTheme();
    BaseToast.fire({
      icon, title, text,
      iconColor: t.icon[icon] || t.icon.success,
      background: t.bg,
      color: t.fg,
      customClass: { popup: 'swal2-toast border', title:'', timerProgressBar:'' },
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

  function toastSuccess(title='Berhasil', text='Operasi berhasil dijalankan.') { renderToast({icon:'success', title, text}); }
  function toastError(title='Gagal', text='Terjadi kesalahan.') {
    BaseToast.update({ timer: 3400 });
    renderToast({icon:'error', title, text});
    BaseToast.update({ timer: 2600 });
  }
  function toastWarning(title='Peringatan', text='Periksa kembali data Anda.') { renderToast({icon:'warning', title, text}); }
  function toastInfo(title='Informasi', text='') { renderToast({icon:'info', title, text}); }

  window.toastSuccess = toastSuccess;
  window.toastError   = toastError;
  window.toastWarning = toastWarning;
  window.toastInfo    = toastInfo;
</script>

<script>
function approvalDetail() {
  return {
    approvalId: @json($approvalId),

    // ---- state ----
    pkg: @json($detail), // langsung dari controller

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

    // lock heights to metadata
    syncHeights() {
      this.$nextTick(() => {
        const metaH = this.$refs.metaCard?.getBoundingClientRect().height || 0;
        const headH = this.$refs.actHeader?.getBoundingClientRect().height || 0;
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
      this.syncHeights();
      this.setupObservers();
    },

    // ---- modal triggers ----
    approvePackage() { this.showApproveModal = true; },
    rejectPackage()  { this.rejectNote = ''; this.rejectNoteError = false; this.showRejectModal = true; },
    closeApproveModal() { if (!this.processing) this.showApproveModal = false; },
    closeRejectModal()  { if (!this.processing) this.showRejectModal = false; },

    // ---- confirm actions ----
    async confirmApprove() {
      try {
        this.processing = true;

        // Gunakan Blade untuk membuat URL yang benar
        const url = `{{ route('approvals.approve', ['id' => $approvalId]) }}`;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // Ambil CSRF token dari meta tag
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        // Cek jika response BUKAN JSON, ini untuk debugging
        const responseText = await response.text();
        const result = JSON.parse(responseText);

        if (!response.ok) throw new Error(result.message || 'Server returned an error.');

        this.pkg.status = 'Approved';
        this.addPkgActivity('approved', '{{ auth()->user()->name ?? "Reviewer" }}');
        this.showApproveModal = false;
        toastSuccess('Success', result.message);

      } catch (err) {
        console.error('Fetch Error:', err);
        if (err instanceof SyntaxError) {
             toastError('Error', 'Received an invalid response from server. Check console.');
        } else {
             toastError('Error', err.message || 'Approve failed');
        }
      } finally {
        this.processing = false;
        this.syncHeights();
      }
    },

    async confirmReject() {
      if (!this.rejectNote || this.rejectNote.trim().length === 0) {
        this.rejectNoteError = true;
        toastWarning('Warning', 'Rejection note is required.');
        return;
      }
      this.rejectNoteError = false;

      try {
        this.processing = true;
        // Gunakan Blade untuk membuat URL yang benar
        const url = `{{ route('approvals.reject', ['id' => $approvalId]) }}`;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // Ambil CSRF token dari meta tag
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ note: this.rejectNote })
        });

        const responseText = await response.text();
        const result = JSON.parse(responseText);

        if (!response.ok) throw new Error(result.message || 'Server returned an error.');

        this.pkg.status = 'Rejected';
        this.addPkgActivity('rejected', '{{ auth()->user()->name ?? "Reviewer" }}', this.rejectNote);
        this.showRejectModal = false;
        toastSuccess('Rejected', result.message);

      } catch (err) {
        console.error('Fetch Error:', err);
        if (err instanceof SyntaxError) {
             toastError('Error', 'Received an invalid response from server. Check console.');
        } else {
             toastError('Error', err.message || 'Reject failed');
        }
      } finally {
        this.processing = false;
        this.syncHeights();
      }
    },

    // ---- helpers ----
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
