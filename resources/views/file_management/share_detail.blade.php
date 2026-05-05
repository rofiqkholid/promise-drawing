@extends('layouts.app')
@section('title', 'Share Detail - PROMISE')
@section('header-title', 'Share Detail')

@section('content')
<nav class="flex px-3 sm:px-5 py-2 sm:py-3 mb-2 sm:mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 overflow-x-auto whitespace-nowrap no-scrollbar" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">

    <li class="inline-flex items-center">
      <a href="{{ route('monitoring') }}" class="inline-flex items-center text-[12px] sm:text-sm font-medium hover:text-blue-600 transition-colors">
        Monitoring
      </a>
    </li>

    <li aria-current="page">
      <div class="flex items-center">
        <span class="mx-1 text-gray-400 text-xs">/</span>

        <a href="{{ route('file-manager.share') }}" class="text-[12px] sm:text-sm font-semibold px-1 sm:px-2 py-0.5 hover:text-blue-600 transition-colors">
          Share Packages
        </a>
      </div>
    </li>
    <li aria-current="page">
      <div class="flex items-center">
        <span class="mx-1 text-gray-400 text-xs">/</span>

        <span class="text-[12px] sm:text-sm font-bold text-blue-600 px-1 sm:px-2 py-0.5">
          Metadata
        </span>
      </div>
    </li>
  </ol>
</nav>
<div
  class="p-3 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen"
  x-data="shareDetail()"
  @mousemove.window="onPan($event)"
  @mouseup.window="endPan()"
  @mouseleave.window="endPan()">


  <!-- ================= MAIN LAYOUT: LEFT STACK + RIGHT PREVIEW ================= -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
    <!-- ================= LEFT COLUMN (lg:span 4) ================= -->
    <div class="lg:col-span-4 space-y-6">

      <!-- ===== Meta Card ===== -->
      <div x-ref="metaCard"
        class="self-start bg-white dark:bg-gray-800 rounded-none border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
          <div class="flex flex-col gap-3 sm:gap-4">
            <div class="flex items-center justify-between">
              <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fa-solid fa-share-nodes mr-2 text-blue-600 text-xs sm:text-sm"></i>
                Share Metadata
              </h2>

              @php
              $backUrl = url()->previous();
              $backUrl = ($backUrl && $backUrl !== url()->current()) ? $backUrl : route('file-manager.share');
              @endphp

              <a href="{{ $backUrl }}"
                class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 text-[11px] sm:text-xs font-semibold text-gray-600 bg-white border border-gray-300 rounded-none hover:bg-gray-50 transition-colors dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                <i class="fa-solid fa-arrow-left"></i>
                Back
              </a>
            </div>

            <div class="flex items-center gap-2">
              <button
                type="button"
                id="btnOpenShareFromDetail"
                data-id="{{ $detail['metadata']['revision_id'] ?? $revisionId ?? '' }}"
                class="flex-1 inline-flex items-center gap-1.5 justify-center px-3 py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-xs font-bold rounded-none transition-colors">
                <i class="fa-solid fa-paper-plane text-[10px] sm:text-xs"></i>
                Share
              </button>

              <button
                @click="prepareZip()"
                class="flex-1 inline-flex items-center gap-1.5 justify-center px-3 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] sm:text-xs font-bold rounded-none transition-colors">
                <i class="fa-solid fa-box-archive text-[10px] sm:text-xs"></i>
                <span>Download All</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Body: ringkasan metadata -->
        <div class="px-3 sm:px-4 py-3 sm:py-4 space-y-3">
          <!-- satu baris ringkas -->
          <p class="text-[12px] sm:text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed" x-text="metaLine()"></p>
        </div>
      </div>

      <x-files.file-group-list title="2D Drawings" icon="fa-drafting-compass" category="2d" allowDownload="true" />
      <x-files.file-group-list title="3D Models" icon="fa-cubes" category="3d" allowDownload="true" />
      <x-files.file-group-list title="ECN / Documents" icon="fa-file-lines" category="ecn" allowDownload="true" />

      <!-- ===== Activity Log (below ECN) ===== -->
      <div class="bg-white dark:bg-gray-800 rounded-none border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-3 py-2 sm:py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
          <div class="flex items-center">
            <i class="fa-solid fa-clock-rotate-left mr-2 text-gray-400 text-xs sm:text-sm"></i>
            <span class="text-[12px] sm:text-sm font-bold text-gray-900 dark:text-gray-100">Activity Log</span>
          </div>
          <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-1.5 py-0.5 rounded-none font-semibold"
            x-text="`${pkg.activityLogs?.length || 0} events`"></span>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden">

          <div
            class="p-2"
            :class="(pkg.activityLogs?.length || 0) > 3 ? 'max-h-96 overflow-y-auto pr-1 pl-1 pt-1' : ''"
            role="log"
            aria-label="Activity Log">

            <template x-for="(item, idx) in (pkg.activityLogs || [])" :key="idx">
              <div class="relative flex gap-3">
                <!-- Line -->
                <template x-if="idx !== (pkg.activityLogs || []).length - 1">
                  <div class="absolute top-4 left-3 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></div>
                </template>

                <div class="relative flex-shrink-0 mt-1">
                  <template x-if="item.action === 'uploaded'">
                    <div class="w-6 h-6 rounded-none bg-blue-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-cloud-arrow-up text-blue-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'approved'">
                    <div class="w-6 h-6 rounded-none bg-green-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-check text-green-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'rejected'">
                    <div class="w-6 h-6 rounded-none bg-red-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-xmark text-red-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'rollbacked'">
                    <div class="w-6 h-6 rounded-none bg-amber-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-rotate-left text-amber-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'downloaded'">
                    <div class="w-6 h-6 rounded-none bg-gray-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-download text-gray-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action.includes('share')">
                    <div class="w-6 h-6 rounded-none bg-blue-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-share-nodes text-blue-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'revise_confirm'">
                    <div class="w-6 h-6 rounded-none bg-purple-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-pen-to-square text-purple-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'submit_approval'">
                    <div class="w-6 h-6 rounded-none bg-yellow-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-paper-plane text-yellow-600 text-xs"></i></div>
                  </template>

                  <template x-if="!['uploaded','approved','rejected','rollbacked','downloaded','revise_confirm','submit_approval'].includes(item.action) && !item.action.includes('share')">
                    <div class="w-6 h-6 rounded-none bg-gray-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></div>
                  </template>
                </div>

                <div class="flex-1 min-w-0" :class="idx !== (pkg.activityLogs || []).length - 1 ? 'mb-4 sm:mb-6' : ''">
                  <div class="p-2 sm:p-3 rounded-none bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <div class="flex justify-between items-start">
                      <p class="text-[11px] sm:text-sm text-gray-900 dark:text-gray-100">
                        <span class="font-bold " x-text="item.action.replace('_', ' ')"></span>
                        <span class="text-[10px] text-gray-400 font-normal">by</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400" x-text="item.user"></span>
                      </p>
                      <span class="text-[9px] sm:text-[10px] text-gray-400 whitespace-nowrap ml-2" x-text="item.time"></span>
                    </div>

                    <!-- Snapshot / Meta -->
                    <template x-if="item.snapshot && (item.snapshot.part_no || item.snapshot.ecn_no)">
                      <div class="mt-2 p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-none text-xs">

                        <div class="flex items-center gap-2 mb-1">
                          <span class="font-bold text-gray-800 dark:text-gray-200" x-text="item.snapshot.part_no || '-'"></span>
                          <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-none text-[10px] border border-gray-200 dark:border-gray-600">
                            Rev <span x-text="item.snapshot.revision_no ?? '-'"></span>
                          </span>
                          <template x-if="item.snapshot.ecn_no">
                            <span class="text-blue-600 dark:text-blue-400 text-[10px] bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded-none border border-blue-100 dark:border-blue-800"
                              x-text="item.snapshot.ecn_no"></span>
                          </template>
                        </div>

                        <div class="text-gray-500 dark:text-gray-400 text-[10px] flex items-center gap-1">
                          <i class="fa-solid fa-tag text-[9px]"></i>
                          <span x-text="item.snapshot.customer || '-'"></span>
                          <span class="mx-0.5">â€¢</span>
                          <span x-text="item.snapshot.model || '-'"></span>
                          <template x-if="item.snapshot.doc_type">
                            <span>
                              <span class="mx-0.5">â€¢</span>
                              <span x-text="item.snapshot.doc_type"></span>
                            </span>
                          </template>
                        </div>

                        <template x-if="item.action === 'rollbacked' && item.snapshot.previous_status">
                          <div class="mt-1.5 pt-1.5 border-t border-gray-100 dark:border-gray-700 flex items-center text-amber-600 dark:text-amber-500 font-medium">
                            <i class="fa-solid fa-code-branch mr-1.5 text-[10px]"></i>
                            <span x-text="item.snapshot.previous_status" class=""></span>
                            <i class="fa-solid fa-arrow-right-long mx-1.5 text-[10px]"></i>
                            <span>Waiting</span>
                          </div>
                        </template>
                      </div>
                    </template>

                    <!-- Note -->
                    <template x-if="item.note">
                      <div class="mt-1.5 flex items-start gap-1.5">
                        <i class="fa-solid fa-quote-left text-gray-300 dark:text-gray-600 text-[10px] mt-0.5"></i>
                        <p class="text-xs text-gray-600 dark:text-gray-300 italic" x-text="item.note"></p>
                      </div>
                    </template>

                    <!-- Share Details -->
                    <template x-if="item.action.includes('share') && item.snapshot">
                      <div class="mt-1.5 text-xs text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-1">
                          <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                          <span>To: <strong x-text="(item.snapshot.shared_to_dept || item.snapshot.shared_with || item.snapshot.shared_to || '-').replace('[EXP] ', '')"></strong></span>
                        </div>
                        <template x-if="item.snapshot.recipients">
                          <div class="mt-0.5 ml-3.5 text-[10px] text-gray-500">Recipients: <span x-text="item.snapshot.recipients"></span></div>
                        </template>
                        <template x-if="item.snapshot.expired_at">
                          <div class="mt-0.5 ml-3.5 text-[10px] text-red-500">Exp: <span x-text="item.snapshot.expired_at"></span></div>
                        </template>
                      </div>
                    </template>

                    <!-- Download Details -->
                    <template x-if="item.action === 'downloaded' && item.snapshot && item.snapshot.downloaded_file">
                      <div class="mt-1.5 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                        <i class="fa-solid fa-file text-[10px]"></i>
                        <span x-text="item.snapshot.downloaded_file"></span>
                        <template x-if="item.snapshot.file_size">
                          <span class="text-gray-400" x-text="`(${item.snapshot.file_size})`"></span>
                        </template>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </template>

            <template x-if="(pkg.activityLogs || []).length === 0">
              <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                <i class="fa-regular fa-calendar-xmark text-2xl mb-2"></i>
                <p class="text-xs">No activity recorded yet.</p>
              </div>
            </template>
          </div>
        </div>
      </div>

    </div>
    <!-- ================= /LEFT COLUMN ================= -->

    <!-- ================= RIGHT COLUMN (lg:span 8) Preview ================= -->
    <div class="lg:col-span-8">
      <div class="bg-white dark:bg-gray-800 rounded-none border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col min-h-[400px] lg:min-h-[600px]">
        <template x-if="selectedFile">
          <div class="flex-1 flex flex-col">
            @include('components.files.file-viewer', [
            'enableMasking' => true,
            'showStampConfig' => true,
            ])
          </div>
        </template>

        <template x-if="!selectedFile">
          <div class="flex-1 flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
            <i class="fa-solid fa-hand-pointer text-4xl sm:text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">Select a File</h3>
            <p class="mt-1 text-[12px] sm:text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
          </div>
        </template>
      </div>
    </div>
    <!-- ================= /RIGHT COLUMN ================= -->
  </div>
  <!-- ================= /MAIN LAYOUT ================= -->


  <x-files.share-modal />

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



    [x-cloak] {
      display: none !important;
    }

    .measure-label {
      user-select: none;
      white-space: nowrap;
    }
  </style>

  <!-- Download ZIP Modal Component -->
  <x-files.download-zip-modal />
  @endsection

  @push('scripts')


  <script>
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

    function shareDetail() {
      const shares = JSON.parse(atob("{{ base64_encode(json_encode($detail['shares'] ?? [])) }}"));
      const suppCode = (Array.isArray(shares) && shares.length > 0) ?
        (shares[0].supplier_code || '--') :
        (typeof shares === 'object' && Object.keys(shares).length > 0) ?
        (Object.values(shares)[0].supplier_code || '--') :
        '--';

      const viewer = fileViewerComponent({
        pkg: JSON.parse(atob("{{ base64_encode(json_encode($detail)) }}")),
        stampFormat: JSON.parse(atob("{{ base64_encode(json_encode($detail['stamp_formats'] ?? [])) }}")),
        showStampConfig: true,
        enableMasking: true,
        userDeptCode: JSON.parse(atob("{{ base64_encode(json_encode($userDeptCode ?? null)) }}")),
        userName: JSON.parse(atob("{{ base64_encode(json_encode($userName ?? null)) }}")),
        isEngineering: JSON.parse(atob("{{ base64_encode(json_encode($isEngineering ?? false)) }}")),
        stampCopyBottomLine: `External - Distributed To Supplier ${suppCode}`
      });

      return {
        ...viewer,

        openSections: ['2d'],
        isDownloadingFile: null,

        init() {
          if (viewer.init) viewer.init.call(this);

          window.addEventListener('masks-updated', (e) => {
            const blocks = Array.isArray(e.detail) ? e.detail : (e.detail.masks || e.detail);
            const p = this.currentPageForSelectedFile();
            this.saveBlocks(blocks, p);
          });

          window.addEventListener('masks-applied-to-all', (e) => {
            const blocks = Array.isArray(e.detail) ? e.detail : (e.detail.masks || e.detail);
            const isAppend = e.detail.append_mode || false;
            this.saveBlocksToAllPages(blocks, isAppend);
          });
        },

        toggleSection(id) {
          if (this.openSections.includes(id)) {
            this.openSections = this.openSections.filter(s => s !== id);
          } else {
            this.openSections.push(id);
          }
        },

        selectFile(file) {
          this.selectedFile = file;
        },

        downloadFile(file) {
          if (!file.download_url) return;

          this.isDownloadingFile = file.name;

          const link = document.createElement('a');
          link.href = file.download_url;
          link.download = file.name;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);

          // Reset loading state after a brief delay since actual download completion 
          // is hard to detect via simple <a> tag click
          setTimeout(() => {
            this.isDownloadingFile = null;
          }, 1000);
        },

        prepareZip() {
          // Calculate stats for modal
          let totalCount = 0;
          let totalSizeBytes = 0;

          const filesMap = this.pkg?.files || {};
          Object.values(filesMap).forEach(list => {
            if (Array.isArray(list)) {
              totalCount += list.length;
              list.forEach(f => {
                totalSizeBytes += parseInt(f.size || 0);
              });
            }
          });

          // Format size for display
          let sizeStr = '0 Bytes';
          if (totalSizeBytes > 0) {
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(totalSizeBytes) / Math.log(k));
            sizeStr = parseFloat((totalSizeBytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
          }

          const id = JSON.parse(atob("{{ base64_encode(json_encode($shareId)) }}"));
          let baseUrl = `{{ route('export.prepare-zip', ['revision_id' => '__ID__']) }}`;
          let url = baseUrl.replace('__ID__', encodeURIComponent(id));

          this.$dispatch('open-download-zip', {
            url: url,
            count: totalCount,
            size: sizeStr
          });
        },

        updateStampUrlTemplate: `{{ route('approvals.files.updateStamp', ['fileId' => '__FILE_ID__']) }}`,
        updateBlocksUrlTemplate: `{{ route('share.files.updateBlocks', ['fileId' => '__FILE_ID__']) }}`,

        metaLine() {
          const m = this.pkg?.metadata || {};
          return [
            m.customer, m.model, m.part_no, m.part_group,
            m.doc_type, m.category, m.ecn_no, m.revision,
            this.pkg?.status
          ].filter(v => v && String(v).trim().length > 0).join(' - ');
        },

        currentPageForSelectedFile() {
          if (!this.selectedFile) return 1;
          const name = this.selectedFile.name || '';
          if (this.isPdf(name)) return this.pdfPageNum;
          if (this.isTiff(name)) return this.tifPageNum;
          return 1;
        },

        async saveBlocks(blocks, page) {
          if (!this.selectedFile?.id) return;

          const url = this.updateBlocksUrlTemplate.replace('__FILE_ID__', this.selectedFile.id);

          try {
            const res = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                page: page,
                blocks: blocks
              })
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Failed to save blocks');

            // Update local state
            if (this.selectedFile) {
              if (typeof this.selectedFile.blocks_position !== 'object' || this.selectedFile.blocks_position === null) {
                this.selectedFile.blocks_position = {};
              }
              const cleanBlocks = Array.isArray(blocks) ? blocks : [];
              this.selectedFile.blocks_position[String(page)] = cleanBlocks;
            }

            toastSuccess('Saved', 'Blocks saved successfully');
          } catch (e) {
            console.error(e);
            toastError('Error', e.message);
          }
        },

        async saveBlocksToAllPages(blocks, isAppend = false) {
          if (!this.selectedFile?.id) return;

          let totalPages = 1;
          const name = this.selectedFile.name || '';
          if (this.isPdf(name)) totalPages = this.pdfNumPages;
          else if (this.isTiff(name)) totalPages = this.tifNumPages;

          if (totalPages <= 1) {
            return this.saveBlocks(blocks, 1);
          }

          const url = this.updateBlocksUrlTemplate.replace('__FILE_ID__', this.selectedFile.id);

          try {
            const res = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                apply_to_all: true,
                append_mode: isAppend,
                total_pages: totalPages,
                blocks: blocks
              })
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Failed to save blocks');

            // Update local state for all pages
            if (this.selectedFile) {
              if (typeof this.selectedFile.blocks_position !== 'object' || this.selectedFile.blocks_position === null) {
                this.selectedFile.blocks_position = {};
              }

              const cleanBlocks = Array.isArray(blocks) ? blocks : [];
              for (let p = 1; p <= totalPages; p++) {
                const pageKey = String(p);
                if (isAppend) {
                  let pageBlocks = this.selectedFile.blocks_position[pageKey] || [];
                  // Merge
                  cleanBlocks.forEach(nb => {
                    let foundIdx = pageBlocks.findIndex(ob => ob.id === nb.id);
                    if (foundIdx >= 0) pageBlocks[foundIdx] = nb;
                    else pageBlocks.push(nb);
                  });
                  this.selectedFile.blocks_position[pageKey] = pageBlocks;
                } else {
                  this.selectedFile.blocks_position[pageKey] = cleanBlocks;
                }
              }
            }

            const msg = isAppend ? 'Block applied to all pages' : 'All blocks applied to all pages';
            toastSuccess('Success', msg);
          } catch (e) {
            console.error(e);
            toastError('Error', e.message);
          }
        }
      };
    }
    // ========== SHARE TRIGGER FOR DETAIL PAGE ==========
    $(function() {
      $('#btnOpenShareFromDetail').on('click', function() {
        const id = $(this).data('id');
        const detail = JSON.parse(atob("{{ base64_encode(json_encode($detail ?? [])) }}"));
        const meta = detail.metadata || {};

        const name = meta.part_no || 'Document Package';
        const info = [
          [meta.customer_code, meta.model].filter(v => v && String(v).trim()).join(' - '),
          meta.doc_type
        ].filter(v => v && String(v).trim()).join(' - ') || '--';

        if (window.openShareModal) {
          window.openShareModal(id, name, info);
        } else {
          console.error('Share modal component not loaded');
        }
      });
    });
  </script>

  @endpush