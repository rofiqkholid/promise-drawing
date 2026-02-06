@extends('layouts.app')
@section('title', 'Approval Detail - PROMISE')
@section('header-title', 'Approval Detail')

@section('content')
<nav class="flex px-5 py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">

    <li class="inline-flex items-center">
      <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600">
        Monitoring
      </a>
    </li>

    <li aria-current="page">
      <div class="flex items-center">
        <span class="mx-1 text-gray-400">/</span>

        <a href="{{ route('approval') }}" class="text-sm font-semibold px-2.5 py-0.5 hover:text-blue-600 rounded">
          Approval
        </a>
      </div>
    </li>
    <li aria-current="page">
      <div class="flex items-center">
        <span class="mx-1 text-gray-400">/</span>

        <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded">
          Approval Metadata
        </span>
      </div>
    </li>
  </ol>
</nav>
<div
  class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen"
  x-data="approvalDetail()"
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
              <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
              Approval Metadata
            </h2>

            @php
            $backUrl = url()->previous();
            $backUrl = ($backUrl && $backUrl !== url()->current()) ? $backUrl : route('approval');
            @endphp
            <a href="{{ $backUrl }}"
              class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
              <i class="fa-solid fa-arrow-left"></i>
              Back
            </a>
          </div>
        </div>

        <!-- Body: single line with dashes -->
        <div class="px-4 pt-4 pb-2">
          <!-- Meta line -->
          <p class="text-xs md:text-sm text-gray-900 dark:text-gray-100 whitespace-normal break-words leading-snug"
            x-text="metaLine()"
            :title="metaLine()"></p>

          <!-- REVISION NOTE / NO NOTE -->
          <template x-if="pkg.note && pkg.note.trim().length > 0">
            <p
              class="flex items-start gap-1 text-[11px] md:text-xs italic leading-snug
               text-amber-700 dark:text-amber-300 mt-1 mb-0">
              <i class="fa-solid fa-quote-left mt-[2px] text-amber-400"></i>
              <span class="whitespace-pre-line break-words" x-text="pkg.note"></span>
            </p>
          </template>

          <template x-if="!pkg.note || pkg.note.trim().length === 0">
            <p
              class="text-[11px] md:text-xs italic leading-snug
               text-gray-400 dark:text-gray-500 mt-1 mb-0">
              No note is available for this package.
            </p>
          </template>

        </div>


        <!-- Footer (Approve / Reject / Rollback / Share) -->
        <!-- Footer (Approve / Reject / Rollback / Share) -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2">
          <!-- Badge is_finish -->
          <div class="mr-auto">
            <span
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
              :class="isFinished()
        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200'
        : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'">

              <i class="fa-solid fa-circle-check"
                :class="isFinished() ? 'text-blue-500' : 'text-gray-400'"></i>

              <!-- Teks bisa Tuan ganti sesuka hati -->
              <span x-text="isFinished() ? 'Finish / Good' : 'Not Finished'"></span>
            </span>
          </div>

          <!-- Waiting: Reject + Approve -->
          <template x-if="canAct()">
            <div class="flex gap-2">
              <button @click="rejectPackage()"
                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm">
                <i class="fa-solid fa-circle-xmark mr-2"></i> Reject
              </button>
              <button @click="approvePackage()"
                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-sm">
                <i class="fa-solid fa-circle-check mr-2"></i> Approve
              </button>
            </div>
          </template>

          <!-- Approved: Rollback + Share -->
          <!-- Rollback -->
<template x-if="canRollback()">
  <button @click="rollbackPackage()"
    class="inline-flex items-center px-3 py-1.5 bg-amber-600 text-white rounded-md">
    <i class="fa-solid fa-rotate-left mr-2"></i> Rollback
  </button>
</template>

<!-- Share -->
<template x-if="canShare()">
  <button @click="openShareModal()"
    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md">
    <i class="fa-solid fa-share-nodes mr-2"></i> Share
  </button>
</template>

        </div>

      </div>


      <x-files.file-group-list title="2D Drawings" icon="fa-drafting-compass" category="2d" />
      <x-files.file-group-list title="3D Models" icon="fa-cubes" category="3d" />
      <x-files.file-group-list title="ECN / Documents" icon="fa-file-lines" category="ecn" />

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
                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-cloud-arrow-up text-blue-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'approved'">
                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-check text-green-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'rejected'">
                    <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-xmark text-red-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'rollbacked'">
                    <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-rotate-left text-amber-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'downloaded'">
                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-download text-gray-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action.includes('share')">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-share-nodes text-indigo-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'revise_confirm'">
                    <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-pen-to-square text-purple-600 text-xs"></i></div>
                  </template>
                  <template x-if="item.action === 'submit_approval'">
                    <div class="w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-paper-plane text-yellow-600 text-xs"></i></div>
                  </template>

                  <template x-if="!['uploaded','approved','rejected','rollbacked','downloaded','revise_confirm','submit_approval'].includes(item.action) && !item.action.includes('share')">
                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center ring-4 ring-white dark:ring-gray-800 z-10 relative"><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></div>
                  </template>
                </div>

                <div class="flex-1 min-w-0" :class="idx !== (pkg.activityLogs || []).length - 1 ? 'mb-6' : ''">
                  <div class="p-3 rounded-md bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <div class="flex justify-between items-start">
                      <p class="text-sm text-gray-900 dark:text-gray-100">
                        <span class="font-bold capitalize" x-text="item.action.replace('_', ' ')"></span>
                        <span class="text-xs text-gray-500 font-normal">by</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400" x-text="item.user"></span>
                      </p>
                      <span class="text-[10px] text-gray-400 whitespace-nowrap ml-2" x-text="item.time"></span>
                    </div>

                    <!-- Snapshot / Meta -->
                    <template x-if="item.snapshot && (item.snapshot.part_no || item.snapshot.ecn_no)">
                      <div class="mt-2 p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded text-xs shadow-sm">

                        <div class="flex items-center gap-2 mb-1">
                          <span class="font-bold text-gray-800 dark:text-gray-200" x-text="item.snapshot.part_no || '-'"></span>
                          <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded font-mono text-[10px] border border-gray-200 dark:border-gray-600">
                            Rev <span x-text="item.snapshot.revision_no ?? '-'"></span>
                          </span>
                          <template x-if="item.snapshot.ecn_no">
                            <span class="text-blue-600 dark:text-blue-400 font-mono text-[10px] bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800"
                              x-text="item.snapshot.ecn_no"></span>
                          </template>
                        </div>

                        <div class="text-gray-500 dark:text-gray-400 text-[10px] flex items-center gap-1">
                          <i class="fa-solid fa-tag text-[9px]"></i>
                          <span x-text="item.snapshot.customer || '-'"></span>
                          <span class="mx-0.5">•</span>
                          <span x-text="item.snapshot.model || '-'"></span>
                          <template x-if="item.snapshot.doc_type">
                            <span>
                              <span class="mx-0.5">•</span>
                              <span x-text="item.snapshot.doc_type"></span>
                            </span>
                          </template>
                        </div>

                        <template x-if="item.action === 'rollbacked' && item.snapshot.previous_status">
                          <div class="mt-1.5 pt-1.5 border-t border-gray-100 dark:border-gray-700 flex items-center text-amber-600 dark:text-amber-500 font-medium">
                            <i class="fa-solid fa-code-branch mr-1.5 text-[10px]"></i>
                            <span x-text="item.snapshot.previous_status" class="capitalize"></span>
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
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col" style="min-height: 600px;">
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
             <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500 mb-4"></i>
             <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
             <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
          </div>
        </template>
      </div>
    </div>
    <div x-show="false">



    <!-- ================= /RIGHT COLUMN ================= -->
  </div>
  <!-- ================= /MAIN LAYOUT ================= -->

  <!-- ========================== MODALS ========================== -->

  <!-- APPROVE MODAL -->
  <div x-show="showApproveModal"
    x-transition.opacity
    x-cloak
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
        Are you sure you want to <span class="font-semibold">Approve</span> this package?
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
    x-cloak
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

  <!-- ROLLBACK MODAL -->
  <div x-show="showRollbackModal"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
    aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40" @click="closeRollbackModal()"></div>

    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Confirm Rollback</h3>
        <button class="text-gray-400 hover:text-gray-600" @click="closeRollbackModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">
        Set status back to <span class="font-semibold">Waiting</span>?
      </div>

      <div class="px-5 py-4 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
        <button @click="closeRollbackModal()" class="px-3 py-1.5 rounded-md border text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button @click="confirmRollback()" :disabled="processing"
          class="px-3 py-1.5 rounded-md bg-amber-600 text-white text-sm hover:bg-amber-700 disabled:opacity-60">
          <span x-show="!processing">Yes, Rollback</span>
          <span x-show="processing">Processing…</span>
        </button>
      </div>
    </div>
  </div>
  <!-- ======================== /MODALS ========================== -->


  <!-- SHARE MODAL (Detail) -->
  <div x-show="showShareModal"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="closeShareModal()"></div>

    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
          Share Package to Dept (Purchasing / PUD)
        </h3>
        <button class="text-gray-400 hover:text-gray-600" @click="closeShareModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="px-5 py-4 space-y-3 text-sm text-gray-700 dark:text-gray-200">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Package</p>
        <p class="font-medium" x-text="metaLine()"></p>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Note <span class="text-red-500">*</span>
          </label>
          <textarea
            x-model.trim="shareNote"
            rows="3"
            class="mt-1 p-2 block w-full rounded-md border border-gray-300 dark:border-gray-600
                 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
          <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
            This note will be included in the email sent to Purchasing / PUD.
          </p>
          <p class="mt-2 text-xs text-red-500" x-show="shareNoteError">
            Note is required.
          </p>
        </div>
      </div>

      <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
        <button @click="closeShareModal()"
          class="px-3 py-1.5 rounded-md border text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button @click="confirmShare()"
          :disabled="shareProcessing || shareNote.length === 0"
          class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md border border-blue-600
                     bg-blue-600 text-white hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed">
          <span x-show="!shareProcessing" class="inline-flex items-center gap-2">
            <i class="fa-solid fa-share-nodes"></i>
            <span>Share</span>
          </span>
          <span x-show="shareProcessing" class="inline-flex items-center gap-2 text-xs">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Sharing...</span>
          </span>
        </button>
      </div>
    </div>
  </div>


</div>

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
</style>

@endsection

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
  }
</script>

<!-- OCCT: parser STEP/IGES (WASM) -->
<script src="https://cdn.jsdelivr.net/npm/occt-import-js@0.0.23/dist/occt-import-js.js"></script>

<script src="{{ asset('assets/js/file-viewer-alpine.js') }}"></script>

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
  function approvalDetail() {
    const viewer = fileViewerComponent({
      pkg: @js($detail),
      showStampConfig: true,
      userDeptCode: @js($userDeptCode ?? null),
      userName: @js($userName ?? null),
      isEngineering: @js($isEngineering ?? false),
      stampFormat: @js($stampFormats[0] ?? null),
      enableMasking: true
    });

    return {
      ...viewer,
      approvalId: @js($approvalId),
      approvalLevel: @js($approvalLevel ?? 0),


      /* ===== State & Modal ===== */
      updateStampUrlTemplate: `{{ route('approvals.files.updateStamp', ['fileId' => '__FILE_ID__']) }}`,
      showApproveModal: false,
      showRejectModal: false,
      showRollbackModal: false,
      showShareModal: false,
      processing: false,
      rejectNote: '',
      rejectNoteError: false,
      shareNote: '',
      shareNoteError: false,
      shareProcessing: false,
      openSections: [],



      /* ===== Stamp Management Overrides ===== */
      positionKeyToInt(key) {
        switch (key) {
          case 'bottom-left': return 0;
          case 'bottom-center': return 1;
          case 'bottom-right': return 2;
          case 'top-left': return 3;
          case 'top-center': return 4;
          case 'top-right': return 5;
          default: return 0;
        }
      },











      /* ===== Lifecycle Override ===== */
      init() {
        if (typeof viewer !== 'undefined' && viewer.init) {
          viewer.init.call(this);
        }
        window.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            this.showApproveModal = false;
            this.showRejectModal = false;
            this.showRollbackModal = false;
            this.showShareModal = false;
          }
        });
        window.addEventListener('beforeunload', () => {
          if (typeof this.disposeCad === 'function') this.disposeCad();
        });
      },

      /* ===== UI Methods ===== */
      toggleSection(c) {
        const i = this.openSections.indexOf(c);
        if (i > -1) this.openSections.splice(i, 1);
        else this.openSections.push(c);
      },

      selectFile(file) {
        if (this.selectedFile && typeof viewer !== 'undefined' && viewer.onStampChange) {
          viewer.onStampChange.call(this);
        }
        if (typeof this.isCad === 'function' && this.isCad(this.selectedFile?.name)) {
          if (typeof this.disposeCad === 'function') this.disposeCad();
        }
        this.selectedFile = { ...file };
      },

      /* ===== Stamp Management ===== */
      async onStampChange() {
        // Save to memory first (this triggers Alpine.js reactivity)
        this.saveStampConfigForCurrent();
        
        if (!this.selectedFile?.id) return;

        const url = this.updateStampUrlTemplate.replace('__FILE_ID__', this.selectedFile.id);
        const payload = {
          ori_position: this.positionKeyToInt(this.stampConfig.original),
          copy_position: this.positionKeyToInt(this.stampConfig.copy),
          obslt_position: this.positionKeyToInt(this.stampConfig.obsolete),
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
          if (!res.ok) throw new Error('Failed to save stamp position');
          toastSuccess('Saved', 'Stamp position updated.');
        } catch (e) {
          console.error(e);
          toastError('Error', 'Failed to save stamp position');
        }
      },

      async applyStampToAll() {
        if (!this.selectedFile) {
          toastWarning('Warning', 'Please select a file first.');
          return;
        }
        this.applyToAllProcessing = true;
        const currentConfig = { ...this.stampConfig };
        const groups = this.pkg.files || {};
        const payload = {
          ori_position: this.positionKeyToInt(currentConfig.original),
          copy_position: this.positionKeyToInt(currentConfig.copy),
          obslt_position: this.positionKeyToInt(currentConfig.obsolete),
        };

        let successCount = 0;
        let failCount = 0;

        try {
          for (const groupKey of Object.keys(groups)) {
            const list = groups[groupKey] || [];
            for (const file of list) {
              if (!file.id) continue;
              const url = this.updateStampUrlTemplate.replace('__FILE_ID__', file.id);
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
                if (res.ok) successCount++;
                else failCount++;
              } catch (err) { failCount++; }
            }
          }
          if (successCount > 0) toastSuccess('Saved', `Applied to ${successCount} files.`);
          if (failCount > 0) toastWarning('Warning', `Failed on ${failCount} files.`);
        } finally {
          this.applyToAllProcessing = false;
        }
      },

      /* ===== Helpers ===== */
      metaLine() {
        const m = this.pkg?.metadata || {};
        return [
            m.customer, m.model, m.part_no, m.part_group, m.doc_type, m.category, m.ecn_no, m.revision,
            this.pkg?.status
          ]
          .filter(v => v && String(v).trim().length > 0)
          .join(' - ');
      },

      addPkgActivity(action, user, note = '') {
        this.pkg.activityLogs.unshift({
          action, user, note: note || '',
          time: new Date().toLocaleString()
        });
      },

      /* ===== Helper status ===== */
      isWaiting() {
        const s = (this.pkg.status || '').toLowerCase();
        return s === 'waiting l1' || s === 'waiting l2' || s === 'waiting l3';
      },
      isApproved() {
        return (this.pkg.status || '').toLowerCase() === 'approved';
      },
      isFinished() {
        const flag = this.pkg?.is_finish ?? this.pkg?.metadata?.is_finish ?? 0;
        return Number(flag) === 1 || flag === true;
      },
      currentWaitingLevel() {
        const s = (this.pkg.status || '').toLowerCase();
        if (s === 'waiting l1') return 1;
        if (s === 'waiting l2') return 2;
        if (s === 'waiting l3') return 3;
        return 0;
      },
      canAct() {
        return this.isWaiting() && this.approvalLevel === this.currentWaitingLevel();
      },
      canRollback() {
        const s = (this.pkg.status || '').toLowerCase();
        if (s === 'waiting l2') return this.approvalLevel === 1;
        if (s === 'waiting l3') return this.approvalLevel === 2;
        if (s === 'approved' || s === 'rejected') return this.approvalLevel === 3;
        return false;
      },
      canShare() {
        const s = (this.pkg.status || '').toLowerCase();
        return s === 'approved';
      },

      /* ===== Actions ===== */
      approvePackage() {
        this.showApproveModal = true;
      },
      rejectPackage() {
        this.rejectNote = '';
        this.rejectNoteError = false;
        this.showRejectModal = true;
      },
      rollbackPackage() {
        this.showRollbackModal = true;
      },
      closeApproveModal() {
        if (!this.processing) this.showApproveModal = false;
      },
      closeRejectModal() {
        if (!this.processing) this.showRejectModal = false;
      },
      closeRollbackModal() {
        if (!this.processing) this.showRollbackModal = false;
      },


      async confirmApprove() {
        if (this.processing) return;
        this.processing = true;
        try {
          const url = `{{ route('approvals.approve', ['id' => $approvalId]) }}`;
          let res = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });

          let text = await res.text();
          let json = {};
          try {
            json = JSON.parse(text);
          } catch {}

          if (!res.ok) {

            if (res.status === 409 && json?.needs_confirmation && json?.code === 'EMAIL_FAILED') {
              const ask = await Swal.fire({
                icon: 'warning',
                title: 'Email Failed',
                text: 'Failed to send email. Approve anyway? The team will not receive email.',
                showCancelButton: true,
                confirmButtonText: 'Approve without email',
                cancelButtonText: 'Cancel',
              });
              if (ask.isConfirmed) {

                res = await fetch(url, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                  },
                  body: JSON.stringify({
                    confirm_without_email: true
                  })
                });
                text = await res.text();
                json = {};
                try {
                  json = JSON.parse(text);
                } catch {}
                if (!res.ok) throw new Error(json.message || 'Approve failed.');
              } else {
                throw new Error('Approval is canceled.');
              }
            } else {
              if (res.status === 422) throw new Error(json.message || 'Revision is not in a state that can be approved.');
              if (res.status === 403) throw new Error(json.message || 'You do not have permission to approve.');
              if (res.status === 409) throw new Error(json.message || 'Revision has already been approved by someone else.');
              throw new Error(json.message || 'Server returned an error.');
            }
          }


          // tentukan status berikutnya berdasarkan level
          if (this.approvalLevel === 1) {
            this.pkg.status = 'Waiting L2';
          } else if (this.approvalLevel === 2) {
            this.pkg.status = 'Waiting L3';
          } else if (this.approvalLevel === 3) {
            this.pkg.status = 'Approved';
          }

          // activity log
          this.addPkgActivity(
            'approved',
            '{{ auth()->user()->name ?? "Reviewer" }}'
          );

          this.showApproveModal = false;
          toastSuccess('Success', json.message || 'Revision approved successfully!');
        } catch (err) {
          console.error('Approve Error:', err);
          toastError('Error', err.message || 'Approve failed');
        } finally {
          this.processing = false;
        }
      },

      async confirmReject() {
        if (this.processing) return;
        if (!this.rejectNote || this.rejectNote.trim().length === 0) {
          this.rejectNoteError = true;
          toastWarning('Warning', 'Rejection note is required.');
          return;
        }
        this.rejectNoteError = false;
        this.processing = true;
        try {
          const url = `{{ route('approvals.reject', ['id' => $approvalId]) }}`;
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              note: this.rejectNote
            })
          });
          const text = await response.text();
          let result = {};
          try {
            result = JSON.parse(text);
          } catch {}
          if (!response.ok) {
            if (response.status === 403) throw new Error(result.message || 'You do not have permission to reject.');
            if (response.status === 422) throw new Error(result.message || 'Revision is not in a state that can be rejected.');
            throw new Error(result.message || 'Server returned an error.');
          }

          this.pkg.status = 'Rejected';
          this.addPkgActivity('rejected', '{{ auth()->user()->name ?? "Reviewer" }}', this.rejectNote);
          this.showRejectModal = false;
          toastSuccess('Rejected', result.message || 'Revision rejected successfully!');
        } catch (err) {
          console.error('Reject Error:', err);
          if (err instanceof SyntaxError) toastError('Error', 'Received an invalid response from server.');
          else toastError('Error', err.message || 'Reject failed');
        } finally {
          this.processing = false;
        }
      },

      async confirmRollback() {
        if (this.processing) return;
        this.processing = true;
        try {
          const url = `{{ route('approvals.rollback', ['id' => $approvalId]) }}`;
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });
          const text = await response.text();
          let result = {};
          try {
            result = JSON.parse(text);
          } catch {}
          if (!response.ok) {
            if (response.status === 403) throw new Error(result.message || 'You do not have permission to rollback.');
            if (response.status === 422) throw new Error(result.message || 'Revision is not in a state that can be rolled back.');
            throw new Error(result.message || 'Server returned an error.');
          }

          if (this.approvalLevel === 1) {
            this.pkg.status = 'Waiting L1';
          } else if (this.approvalLevel === 2) {
            this.pkg.status = 'Waiting L2';
          } else if (this.approvalLevel === 3) {
            this.pkg.status = 'Waiting L3';
          }

          this.addPkgActivity('rollbacked', '{{ auth()->user()->name ?? "Reviewer" }}', 'Status set to Waiting');
          this.showRollbackModal = false;
          toastSuccess('Rolled back', result.message || 'Status has been set back to Waiting.');
        } catch (err) {
          console.error('Rollback Error:', err);
          if (err instanceof SyntaxError) toastError('Error', 'Received an invalid response from server.');
          else toastError('Error', err.message || 'Rollback failed');
        } finally {
          this.processing = false;
        }
      },
      openShareModal() {
        this.shareNote = '';
        this.shareNoteError = false;
        this.showShareModal = true;
      },

      closeShareModal() {
        if (this.shareProcessing) return;
        this.showShareModal = false;
      },

      async confirmShare() {
        if (this.shareProcessing) return;

        if (!this.shareNote || this.shareNote.trim().length === 0) {
          this.shareNoteError = true;
          toastWarning('Warning', 'Note is required.');
          return;
        }

        this.shareNoteError = false;
        this.shareProcessing = true;

        try {
          const url = `{{ route('approvals.share') }}`;
          const res = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              revision_id: this.approvalId,
              note: this.shareNote
            })
          });

          const text = await res.text();
          let json = {};
          try {
            json = JSON.parse(text);
          } catch {}

          if (!res.ok) {

            throw new Error(json.message || 'Failed to share revision.');
          }

          this.showShareModal = false;
          toastSuccess('Shared', json.message || 'Revision has been successfully shared to the department.');
        } catch (e) {
          console.error('Share Error:', e);
          toastError('Share Failed', e.message || 'Failed to share revision.');
        } finally {
          this.shareProcessing = false;
        }
      },




    }
  }
</script>
@endpush