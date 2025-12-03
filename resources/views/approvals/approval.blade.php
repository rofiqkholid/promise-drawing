@extends('layouts.app')
@section('title', 'Approval - PROMISE')
@section('header-title', 'Approval')

@section('content')

<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900" x-data="{ modalOpen: false }">
  <div class="sm:flex sm:items-center sm:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Approval</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage Your File in Data Center</p>
    </div>

    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:mt-0">
      {{-- Card Total Document --}}
      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-blue-500 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/50 rounded-full">
          <i class="fa-solid fa-box-archive fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Package</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardTotal">0</span>
          </p>
        </div>
      </div>
      {{-- Card Waiting --}}
      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-yellow-500 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/50 rounded-full">
          <i class="fa-solid fa-clock fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Waiting</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardWaiting">0</span>
          </p>
        </div>
      </div>
      {{-- Card Approved --}}
      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-green-500 dark:text-green-400 bg-green-100 dark:bg-green-900/50 rounded-full">
          <i class="fa-solid fa-circle-check fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardApproved">0</span>
          </p>
        </div>
      </div>
      {{-- Card Rejected --}}
      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-red-500 dark:text-red-400 bg-red-100 dark:bg-red-900/50 rounded-full">
          <i class="fa-solid fa-circle-xmark fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardRejected">0</span>
          </p>
        </div>
      </div>
    </div>
  </div>

  {{-- Filter section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Filters</h3>
      <div class="flex items-center gap-2">
        <button id="btnDownloadSummary"
          type="button"
          class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border border-green-500
     bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-200
     hover:bg-green-100 dark:hover:bg-green-900/60">
          {{-- normal state --}}
          <span class="btn-label inline-flex items-center gap-2">
            <i class="fa-solid fa-file-excel"></i>
            <span>Download Summary</span>
          </span>

          {{-- loading state --}}
          <span class="btn-spinner hidden inline-flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Preparing...</span>
          </span>
        </button>


        <button id="btnResetFilters"
          type="button"
          class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600
             bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
          <i class="fa-solid fa-rotate-left"></i>
          Reset Filters
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-6">
  {{-- Customer --}}
  <div>
    <label for="customer" class="text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
    <div class="relative mt-1">
      <select id="customer"
        class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <option value="All" selected>All</option>
      </select>
    </div>
  </div>

  {{-- Model --}}
  <div>
    <label for="model" class="text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
    <div class="relative mt-1">
      <select id="model"
        class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <option value="All" selected>All</option>
      </select>
    </div>
  </div>

  {{-- Document Type --}}
  <div>
    <label for="document-type" class="text-sm font-medium text-gray-700 dark:text-gray-300">Document Type</label>
    <div class="relative mt-1">
      <select id="document-type"
        class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <option value="All" selected>All</option>
      </select>
    </div>
  </div>

  {{-- Category --}}
  <div>
    <label for="category" class="text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
    <div class="relative mt-1">
      <select id="category"
        class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <option value="All" selected>All</option>
      </select>
    </div>
  </div>

  {{-- Revision Status (Waiting/Approved/Rejected) --}}
  <div>
    <label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
    <div class="relative mt-1">
      <select id="status"
        class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <option value="All" selected>All</option>
      </select>
    </div>
  </div>

  {{-- Project Status (BARU, sama konsep dengan dashboard #project_status) --}}
  <div>
    <label for="project-status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Project Status</label>
    <div class="relative mt-1">
      <select id="project-status"
        class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <option value="All" selected>All</option>
      </select>
    </div>
  </div>
</div>

  </div>

  {{-- Tabel section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-hidden">
      <table id="approvalTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Package Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Receive Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Decision Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
        </tbody>
      </table>
    </div>
  </div>

  {{-- Modal Share ke Dept --}}
  <div id="shareModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 dark:bg-black/60">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4">
      <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
          Share Package to Dept (Purchasing / PUD)
        </h3>
        <button type="button" id="btnCloseShare"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="px-5 py-4 space-y-3">
        <div>
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Package</p>
          <p id="sharePackageInfo"
            class="text-sm text-gray-900 dark:text-gray-100 font-medium">
            <!-- diisi oleh JS -->
          </p>
        </div>

        <div>
          <label for="shareNote"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Note <span class="text-red-500">*</span>
          </label>
          <textarea id="shareNote"
            rows="3"
            class="mt-1 p-2 block w-full rounded-md border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                           text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
          <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
            Note ini akan menjadi isi email ke user dept. Wajib diisi.
          </p>
          <p id="shareError"
            class="mt-2 text-xs text-red-500 hidden">
          </p>
        </div>
      </div>

      <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
        <button type="button" id="btnCancelShare"
          class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200
                       hover:bg-gray-50 dark:hover:bg-gray-600">
          Cancel
        </button>
        <button type="button" id="btnConfirmShare"
          class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md border border-blue-600
                       bg-blue-600 text-white hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed"
          disabled>
          <span class="btn-label inline-flex items-center gap-2">
            <i class="fa-solid fa-share-nodes"></i>
            <span>Share</span>
          </span>
          <span class="btn-spinner hidden inline-flex items-center gap-2 text-xs">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Sharing...</span>
          </span>
        </button>
      </div>
    </div>
  </div>

</div>

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

  $(function() {
    let table;
    const ENDPOINT = '{{ route("approvals.filters") }}';
    const SHARE_URL = '{{ route("approvals.share") }}';

    let shareRevisionId = null;
    let shareRowData = null;

    // --- helper: reset Select2 ke "All"
    function resetSelect2ToAll($el) {
      $el.empty();
      const opt = new Option('All', 'All', true, true);
      $el.append(opt);
      $el.trigger('change');
      $el.trigger('select2:select');
    }

    // --- Select2 AJAX (server-side) helper ---
    function makeSelect2($el, field, extraParamsFn) {
      $el.select2({
        width: '100%',
        placeholder: 'All',
        allowClear: false,
        minimumResultsForSearch: 0,
        ajax: {
          url: ENDPOINT,
          dataType: 'json',
          delay: 250,
          cache: true,
          data: function(params) {
            const p = {
              select2: field,
              q: params.term || '',
              page: params.page || 1
            };
            if (typeof extraParamsFn === 'function') {
              Object.assign(p, extraParamsFn());
            }
            return p;
          },
          processResults: function(data, params) {
            params.page = params.page || 1;
            const results = Array.isArray(data.results) ? data.results.slice() : [];
            if (!results.some(r => r.id === 'All')) {
              results.unshift({
                id: 'All',
                text: 'All'
              });
            }
            return {
              results,
              pagination: {
                more: data.pagination ? data.pagination.more : false
              }
            };
          }
        },
        templateResult: function(item) {
          if (item.loading) return item.text;
          return $('<div class="text-sm">' + (item.text || item.id) + '</div>');
        },
        templateSelection: function(item) {
          return item.text || item.id || 'All';
        }
      });
    }

    // Inisialisasi Select2 server-side + dependent params
    makeSelect2($('#customer'), 'customer');
    makeSelect2($('#model'), 'model', () => ({
      customer_code: $('#customer').val() || ''
    }));
    makeSelect2($('#document-type'), 'doc_type');
    makeSelect2($('#category'), 'category', () => ({
      doc_type: $('#document-type').val() || ''
    }));
    makeSelect2($('#status'), 'status');
    makeSelect2($('#project-status'), 'project_status');


    // Dependent behavior -> set anak ke "All"
    $('#customer').on('change', function() {
      resetSelect2ToAll($('#model'));
    });
    $('#document-type').on('change', function() {
      resetSelect2ToAll($('#category'));
    });

    function getCurrentFilters() {
  const valOrAll = v => (v && v.length ? v : 'All');

  // ambil data select2 untuk Project Status, sama gaya dengan Dashboard
  const psData = $('#project-status').select2('data');
  const psText = (psData && psData.length > 0) ? (psData[0].text || '').trim() : '';
  const projectStatus = psText && psText !== 'ALL' ? psText : 'ALL';  // 'ALL' artinya no filter

  return {
    customer:       valOrAll($('#customer').val()),
    model:          valOrAll($('#model').val()),
    doc_type:       valOrAll($('#document-type').val()),
    category:       valOrAll($('#category').val()),
    status:         valOrAll($('#status').val()),
    project_status: valOrAll($('#project-status').val()),
  };
}


    function loadKPI() {
      const params = getCurrentFilters();
      $('#cardTotal, #cardWaiting, #cardApproved, #cardRejected').text('…');

      $.ajax({
        url: '{{ route("approvals.kpi") }}',
        data: params,
        dataType: 'json',
        success: function(res) {
          const c = res.cards || {};
          $('#cardTotal').text(c.total ?? 0);
          $('#cardWaiting').text(c.waiting ?? 0);
          $('#cardApproved').text(c.approved ?? 0);
          $('#cardRejected').text(c.rejected ?? 0);
        },
        error: function(xhr) {
          console.error('KPI error', xhr.responseText);
          $('#cardTotal, #cardWaiting, #cardApproved, #cardRejected').text('0');
        }
      });
    }

    // formatter tanggal
    function fmtDate(v) {
      if (!v) return '';
      const d = new Date(v);
      if (isNaN(d)) return v;
      const pad = n => n.toString().padStart(2, '0');
      const dd = pad(d.getDate());
      const MM = pad(d.getMonth() + 1);
      const yyyy = d.getFullYear();
      const HH = pad(d.getHours());
      const mm = pad(d.getMinutes());
      return `${dd}-${MM}-${yyyy} ${HH}:${mm}`;
    }

    function openShareModal(rowData) {
      shareRevisionId = rowData.id;
      shareRowData = rowData;

      // isi info package di modal
      const revVal = rowData.revision ?? rowData.revision_no;
      const revTxt = (revVal !== undefined && revVal !== null && revVal !== '') ? `rev ${revVal}` : '';

      const parts = [
        rowData.customer,
        rowData.model,
        rowData.part_no,
        rowData.doc_type,
        rowData.category,
        rowData.part_group,
        rowData.ecn_no,
        revTxt
      ].filter(Boolean);

      $('#sharePackageInfo').text(parts.join(' - '));

      // reset form
      $('#shareNote').val('');
      $('#shareError').addClass('hidden').text('');
      updateShareButtonState();

      $('#shareModal').removeClass('hidden').addClass('flex');
      $('body').addClass('overflow-hidden');
    }

    function closeShareModal() {
      $('#shareModal').addClass('hidden').removeClass('flex');
      $('body').removeClass('overflow-hidden');
      shareRevisionId = null;
      shareRowData = null;
    }

    function updateShareButtonState() {
      const note = $('#shareNote').val().trim();
      const $btn = $('#btnConfirmShare');
      if (note.length > 0) {
        $btn.prop('disabled', false);
      } else {
        $btn.prop('disabled', true);
      }
    }

    function setShareButtonLoading(isLoading) {
      const $btn = $('#btnConfirmShare');
      if (isLoading) {
        $btn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
        $btn.find('.btn-label').addClass('hidden');
        $btn.find('.btn-spinner').removeClass('hidden');
      } else {
        $btn.find('.btn-label').removeClass('hidden');
        $btn.find('.btn-spinner').addClass('hidden');
        updateShareButtonState(); // balik ke state normal tergantung note
      }
    }


    function initTable() {
      table = $('#approvalTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route("approvals.list") }}',
          type: 'GET',
          data: function(d) {
            const f = getCurrentFilters();
            d.customer = f.customer;
            d.model = f.model;
            d.doc_type = f.doc_type;
            d.category = f.category;
            d.status = f.status;
            d.project_status = f.project_status;
          }
        },

        // default: Request Date terbaru di atas (kolom index 3)
        order: [
          [3, 'desc']
        ],

        columns: [
          // No
          {
            data: null,
            orderable: false,
            searchable: false
          },

          // Package Data
          {
            data: null,
            orderable: false,
            searchable: false,
            render: function(row) {
              const revVal = row.revision ?? row.revision_no;
              const revTxt = (revVal !== undefined && revVal !== null && revVal !== '') ?
                `rev ${revVal}` :
                '';

              const parts = [
                row.customer,
                row.model,
                row.part_no,
                row.doc_type,
                row.category,
                row.part_group,
                row.ecn_no,
                revTxt
              ].filter(Boolean);

              return `<div class="text-sm">${parts.join(' - ')}</div>`;
            }
          },

          // Receipt Date
          {
            data: 'receipt_date',
            name: 'dpr.receipt_date',
            render: function(v) {
              const text = fmtDate(v);
              return `<span title="${v || ''}">${text}</span>`;
            }
          },

          // Request Date
          {
            data: 'request_date',
            name: 'pa.requested_at',
            render: function(v) {
              const text = fmtDate(v);
              return `<span title="${v || ''}">${text}</span>`;
            }
          },

          // Decision Date
          {
            data: 'decision_date',
            name: 'pa.decided_at',
            render: function(v, t, row) {
              if (!v || row.status === 'Waiting')
                return '<span class="text-gray-400">—</span>';
              const text = fmtDate(v);
              return `<span title="${row.status} at ${v}">${text}</span>`;
            }
          },

          // Status
          {
            data: 'status',
            name: 'dpr.revision_status',
            render: function(data) {
              let cls = '';
              if (data === 'Rejected') cls = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
              if (data === 'Waiting') cls = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
              if (data === 'Approved') cls = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
              return `<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${cls}">${data ?? ''}</span>`;
            }
          },
          {
            data: 'id',
            orderable: false,
            searchable: false,
            className: 'text-center whitespace-nowrap',
            render: function(packageId, type, row) {
              return `
                                <button 
                                    type="button" 
                                    class="btn-share px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-300 
                                           bg-blue-100 dark:bg-blue-900/50 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/80"
                                    data-id="${packageId}" 
                                    title="Share package ${packageId}">
                                    <i class="fa-solid fa-share-nodes fa-fw"></i> Share
                                </button>
                            `;
            }
          }
        ],

        columnDefs: [{
            targets: 0,
            className: 'text-center w-12',
            width: '48px'
          },
          {
            targets: 2, // Receipt Date
            className: 'whitespace-nowrap'
          },
          {
            targets: 3, // Request Date
            className: 'whitespace-nowrap'
          },
          {
            targets: 4, // Decision Date
            className: 'whitespace-nowrap'
          }
        ],

        responsive: true,
        dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
        createdRow: function(row) {
          $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer');
        }
      });

      // Penomoran ulang setiap draw
      table.on('draw.dt', function() {
        const info = table.page.info();
        table.column(0, {
          page: 'current'
        }).nodes().each(function(cell, i) {
          cell.innerHTML = i + 1 + info.start;
        });
      });
    }

    function bindHandlers() {
      // perubahan filter -> reload & refresh KPI
      $('#customer, #model, #document-type, #category, #status, #project-status').on('change', function() {
        if (table) table.ajax.reload(null, true);
        loadKPI();
      });

      // tombol reset
      $('#btnResetFilters').on('click', function() {
        resetSelect2ToAll($('#customer'));
        resetSelect2ToAll($('#model'));
        resetSelect2ToAll($('#document-type'));
        resetSelect2ToAll($('#category'));
        resetSelect2ToAll($('#status'));
        resetSelect2ToAll($('#project-status'));

        if (table) table.ajax.reload(null, true);
        loadKPI();
      });

      $('#btnDownloadSummary').on('click', function() {
        const f = getCurrentFilters();
        const query = $.param(f);
        const url = '{{ route("approvals.summary") }}?' + query;

        const $btn = $(this);

        function setLoading(isLoading) {
          if (isLoading) {
            $btn.prop('disabled', true)
              .addClass('opacity-60 cursor-not-allowed');
            $btn.find('.btn-label').addClass('hidden');
            $btn.find('.btn-spinner').removeClass('hidden');
          } else {
            $btn.prop('disabled', false)
              .removeClass('opacity-60 cursor-not-allowed');
            $btn.find('.btn-label').removeClass('hidden');
            $btn.find('.btn-spinner').addClass('hidden');
          }
        }

        setLoading(true);

        const $iframe = $('<iframe>', {
          src: url,
          style: 'display:none;'
        }).appendTo('body');

        let done = false;

        function finish() {
          if (done) return;
          done = true;
          setLoading(false);
          $iframe.remove();
        }

        const timeoutId = setTimeout(finish, 7000);

        $iframe.on('load', function() {
          clearTimeout(timeoutId);
          setTimeout(finish, 1000);
        });
      });

      // ========= SHARE =========

      // enable/disable tombol Share di modal ketika note diketik
      $('#shareNote').on('input', function() {
        updateShareButtonState();
      });

      // klik tombol Share di tabel -> buka modal
      $('#approvalTable tbody').on('click', '.btn-share', function(e) {
        e.stopPropagation(); // jangan trigger klik row
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        if (!rowData) return;
        openShareModal(rowData);
      });

      // klik Cancel / X -> tutup modal
      $('#btnCancelShare, #btnCloseShare').on('click', function() {
        closeShareModal();
      });

      // klik backdrop (area gelap) -> tutup modal
      $('#shareModal').on('click', function(e) {
        if ($(e.target).is('#shareModal')) {
          closeShareModal();
        }
      });

      // klik tombol Share di modal -> AJAX ke controller
      $('#btnConfirmShare').on('click', function() {
        const note = $('#shareNote').val().trim();
        if (!shareRevisionId || !note.length) return;

        setShareButtonLoading(true);
        $('#shareError').addClass('hidden').text('');

        $.ajax({
          url: SHARE_URL,
          type: 'POST',
          dataType: 'json',
          data: {
            _token: '{{ csrf_token() }}',
            revision_id: shareRevisionId,
            note: note
          },
          success: function(res) {
            closeShareModal();
            toastSuccess('Shared', res.message || 'Revision has been successfully shared to the department.');
            if (table) {
              table.ajax.reload(null, false);
            }
          },
          error: function(xhr) {

            let msg = 'Failed to share revision.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            }

            $('#shareError').removeClass('hidden').text(msg);
            toastError('Share Failed', msg);
          },
          complete: function() {
            setShareButtonLoading(false);
          }
        });
      });

      // klik row -> detail (kecuali kalau klik di tombol Share)
      $('#approvalTable tbody').on('click', 'tr', function(e) {
        if ($(e.target).closest('.btn-share').length) return;

        const row = table.row(this).data();
        if (row && row.hash) {
          window.location.href = `{{ url('/approval') }}/${encodeURIComponent(row.hash)}`;
        }
      }).on('mouseenter', 'tr', function() {
        $(this).css('cursor', 'pointer');
      });
    }


    // start
    initTable();
    loadKPI();
    bindHandlers();
  });
</script>
@endpush