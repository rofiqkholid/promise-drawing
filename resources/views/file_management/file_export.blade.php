@extends('layouts.app')

@section('title', 'Download - File Manager')
@section('header-title', 'File Manager - Download')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900" x-data="{ modalOpen: false }">
  <div class="sm:flex sm:items-center sm:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Download Files</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Find and download your files from the Data Center.</p>
    </div>

    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4 sm:mt-0">
      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-blue-500 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/50 rounded-full">
          <i class="fa-solid fa-box-archive fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Packages</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardTotal">0</span>
          </p>
        </div>
      </div>

      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-yellow-500 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/50 rounded-full">
          <i class="fa-solid fa-layer-group fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revisions</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardTotalRevisions">0</span>
          </p>
        </div>
      </div>

      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-green-500 dark:text-green-400 bg-green-100 dark:bg-green-900/50 rounded-full">
          <i class="fa-solid fa-cloud-arrow-down fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Download</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardDownload">0</span>
          </p>
        </div>
      </div>
    </div>
  </div>

  {{-- Filter section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
      {{-- <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Filters</h3> --}}
      <div class="relative w-full sm:w-72"> <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i id="search-icon-static" class="fa-solid fa-magnifying-glass text-gray-400 transition-opacity duration-200"></i>
              
              <i id="search-icon-loading" class="fa-solid fa-spinner fa-spin text-blue-500 opacity-0 transition-opacity duration-200 absolute left-3"></i>
          </div>

          <input type="text" 
              id="custom-export-search" 
              class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-full leading-5 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out shadow-sm" 
              placeholder="Search ECN, Model, Etc..."
              autocomplete="off">

          <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
              <button id="btn-clear-search" 
                      type="button"
                      class="hidden text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none transition-colors p-1">
                  <i class="fa-solid fa-circle-xmark"></i>
              </button>
          </div>
      </div>
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

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5">
      @foreach(['Customer', 'Model', 'Document Type', 'Category', 'Project Status'] as $label)
      <div>
        <label for="{{ Str::slug($label) }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
        <div class="relative mt-1">
          <select id="{{ Str::slug($label) }}"
            class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <option value="All" selected>All</option>
          </select>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Tabel section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- <div class="overflow-x-auto"> -->
      <table id="exportTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="py-3 px-4 text-left ...">No</th>
            <th class="py-3 px-4 text-left ...">Package Info</th>
            <th class="py-3 px-4 text-left ...">Current Revision</th>
            <th class="py-3 px-4 text-left ...">ECN No</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doc Group</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sub-Category</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Part Group</th>
            <th class="py-3 px-4 text-left ...">Uploaded At</th>
            <th class="py-3 px-4 text-left ...">Size</th>
            <th class="py-3 px-4 text-center ...">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
        </tbody>
      </table>
    <!-- </div> -->
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
  let table;
  const ENDPOINT = '{{ route("api.export.filters") }}';

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
        data: function (params) {
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
        processResults: function (data, params) {
          params.page = params.page || 1;
          const results = Array.isArray(data.results) ? data.results.slice() : [];
          if (!results.some(r => r.id === 'All')) {
            results.unshift({ id: 'All', text: 'All' });
          }
          return {
            results,
            pagination: { more: data.pagination ? data.pagination.more : false }
          };
        }
      },
      templateResult: function (item) {
        if (item.loading) return item.text;
        return $('<div class="text-sm">' + (item.text || item.id) + '</div>');
      },
      templateSelection: function (item) {
        return item.text || item.id || 'All';
      }
    });
  }

  makeSelect2($('#customer'),      'customer');
  makeSelect2($('#model'),         'model',      () => ({ customer_code: $('#customer').val() || '' }));
  makeSelect2($('#document-type'), 'doc_type');
  makeSelect2($('#category'),      'category',   () => ({ doc_type: $('#document-type').val() || '' }));
  makeSelect2($('#project-status'), 'project_status');

  $('#customer').on('change', function () {
    resetSelect2ToAll($('#model'));
  });
  $('#document-type').on('change', function () {
    resetSelect2ToAll($('#category'));
  });

  function getCurrentFilters() {
    const valOrAll = v => (v && v.length ? v : 'All');
    return {
      customer:  valOrAll($('#customer').val()),
      model:     valOrAll($('#model').val()),
      doc_type:  valOrAll($('#document-type').val()),
      category:  valOrAll($('#category').val()),
      project_status: valOrAll($('#project-status').val()),
    };
  }

  function loadKPI() {
    const params = getCurrentFilters();
    $('#cardTotal, #cardDownload, #cardTotalRevisions').text('…');

    $.ajax({
      url: '{{ route("api.export.kpi") }}',
      data: params,
      dataType: 'json',
      success: function (res) {
        const c = res.cards || {};
        $('#cardTotal').text(c.total ?? 0);
        $('#cardDownload').text(c.total_download ?? 0);
        $('#cardTotalRevisions').text(c.total_revisions ?? 0);
      },
      error: function (xhr) {
        console.error('KPI error', xhr.responseText);
        $('#cardTotal, #cardDownload, #cardTotalRevisions').text('0');
      }
    });
  }

  // Initialize DataTable
  function initTable() {
    const $staticIcon  = $('#search-icon-static');
    const $loadingIcon = $('#search-icon-loading')

    table = $('#exportTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
      ajax: {
        url: '{{ route("api.export.list") }}',
        type: 'GET',
        data: function (d) {
          const f = getCurrentFilters();
          d.customer  = f.customer;
          d.model     = f.model;
          d.doc_type  = f.doc_type;
          d.category  = f.category;
          d.project_status = f.project_status;
        },
        error: function (xhr, error, thrown) {
                console.error('DataTable Error:', error);
                $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
                $staticIcon.removeClass('opacity-0');
            }
      },
      order: [[ 7, 'desc' ]],

      createdRow: function(row, data, dataIndex) {
        $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer');
      },

      columns: [
        { data: null, name: 'No', orderable: false, searchable: false },
        {
            data: null,
            name: 'Package Info',
            searchable: true,
            orderable: false,
            render: function(data, type, row) {
                return `${row.customer} - ${row.model} - ${row.part_no}`;
            }
        },
        {
            data: null,
            name: 'Revision',
            searchable: true,
            orderable: false,
            render: function(data, type, row) {
                let revStr = `Rev${row.revision_no}`;
                if (row.revision_label_name) {
                    return `${row.revision_label_name} - ${revStr}`;
                }
                return revStr;
            }
        },
        {data: 'ecn_no', name: 'ecn_no', searchable: true},
        {data: 'doctype_group', name: 'doctype_group', searchable: true, orderable: true},
        {data: 'doctype_subcategory', name: 'doctype_subcategory', searchable: true, orderable: true},
        {data: 'part_group', name: 'part_group', searchable: true, orderable: true},
        {data: 'uploaded_at', name: 'uploaded_at', searchable: true},
        {
            data: 'total_size',
            name: 'size',
            searchable: false,
            orderable: true,
            render: function(data, type, row) {
                if (data === null) return '0 KB';
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                let size = parseInt(data);
                if (size === 0) return '0 Bytes';
                const i = Math.floor(Math.log(size) / Math.log(1024));
                return parseFloat((size / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
            }
        },
        {
            data: null,
            name: 'Info',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                const detailUrl = `/file-manager.export/${encodeURIComponent(row.id)}`;
                let viewButton = `<button type="button" onclick="openPackageDetails('${row.id}')" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-300 dark:text-blue-400 transition-colors" title="Details">
                <i class="fa-solid fa-eye fa-lg"></i></button>`;
                let downloadButton = `<button type="button" onclick="confirmDownload('${row.id}')" class="ml-4 text-green-600 hover:text-green-900 dark:hover:text-green-300 dark:text-green-400 transition-colors" title="Download Package"><i class="fa-solid fa-download fa-lg"></i></button>`;
                return `<div class="text-center">${viewButton}${downloadButton}</div>`;
            }
        }
      ],
    });

    table.on('processing.dt', function (e, settings, processing) {
        if (processing) {
            $staticIcon.addClass('opacity-0');
            $loadingIcon.removeClass('opacity-0').addClass('opacity-100');
        } else {
            $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
            $staticIcon.removeClass('opacity-0');
        }
    });
    
    $('#dt-custom-search').on('keyup', function () {
        const val = this.value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.search(val).draw(); 
        }, 500); // Delay 500ms
    });

    table.on('draw.dt', function () {
      const info = table.page.info();
      table.column(0, { page: 'current' }).nodes().each(function (cell, i) {
        cell.innerHTML = i + 1 + info.start;
      });
    });

    $('#exportTable tbody').on('click', 'tr', function (e) {
      if ($(e.target).closest('button').length || $(e.target).closest('a').length) return;

      const data = table.row(this).data();
      if (!data || !data.id) return;

      const encryptedId = encodeURIComponent(data.id);
      window.location.href = `/file-manager.export/${encryptedId}`;
    });
  }

  function bindHandlers() {
    $('#customer, #model, #document-type, #category, #project-status').on('change', function () {
      if (table) table.ajax.reload(null, true);
      loadKPI();
    });

    $('#btnResetFilters').on('click', function () {
      resetSelect2ToAll($('#customer'));
      resetSelect2ToAll($('#model'));
      resetSelect2ToAll($('#document-type'));
      resetSelect2ToAll($('#category'));
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

    const $inputSearch = $('#custom-export-search');
    const $btnClear    = $('#btn-clear-search');
    let searchTimeout  = null;

    // Handler Input (Typing)
    $inputSearch.on('keyup input', function () {
        const val = this.value;

        // Toggle tombol Clear (X)
        if (val.length > 0) {
            $btnClear.removeClass('hidden');
        } else {
            $btnClear.addClass('hidden');
        }
        
        // Debounce Search
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (table.search() !== val) {
                table.search(val).draw(); 
            }
        }, 600);
    });

    // 2. Handler Tombol Clear (X)
    $btnClear.on('click', function () {
        $inputSearch.val('').focus(); // Kosongkan input & balikin fokus
        $btnClear.addClass('hidden'); // Sembunyikan tombol X
        
        // Reset search datatable
        table.search('').draw();
    });
  }

  // start
  initTable();
  loadKPI();
  bindHandlers();


  // Modal utilities
  function bytesToSize(bytes) {
      if (!bytes && bytes !== 0) return '0 Bytes';
      const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
      if (bytes === 0) return '0 Bytes';
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
  }

  function formatDateTime(dt) {
      if (!dt) return '-';
      try {
          const d = new Date(dt);
          if (isNaN(d.getTime())) return dt;
          return d.toLocaleString();
      } catch (e) { return dt; }
  }

  function formatDateOnly(dt) {
      if (!dt) return '-';
      try {
          const datePart = dt.split('T')[0];
          const parts = datePart.split('-');

          if (parts.length === 3 && parts[0].length === 4) {
              const year = parts[0];
              const month = parseInt(parts[1], 10);
              const day = parseInt(parts[2], 10);

              return `${day}/${month}/${year}`;
          } else {
              const d = new Date(dt);
              if (isNaN(d.getTime())) return dt;
              return `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
          }
      } catch (e) { return dt; }
  }

  function closePackageDetails() {
      const modal = document.getElementById('package-details-modal');
      if (modal) {
          if (modal._cleanup) try { modal._cleanup(); } catch(e) {}
          modal.remove();
      }
  }

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
  function toastSuccess(title = 'Berhasil', text = 'Operasi berhasil dijalankan.') { renderToast({ icon: 'success', title, text }); }
  function toastError(title = 'Gagal', text = 'Terjadi kesalahan.') { BaseToast.update({ timer: 3400 }); renderToast({ icon: 'error', title, text }); BaseToast.update({ timer: 2600 }); }
  function toastWarning(title = 'Peringatan', text = 'Periksa kembali data Anda.') { renderToast({ icon: 'warning', title, text }); }
  function toastInfo(title = 'Informasi', text = '') { renderToast({ icon: 'info', title, text }); }
  window.toastSuccess = toastSuccess; window.toastError = toastError; window.toastWarning = toastWarning; window.toastInfo = toastInfo;

  function formatTimeAgo(date) {
      const seconds = Math.floor((new Date() - date) / 1000);
      let interval = seconds / 31536000;
      if (interval > 1) return Math.floor(interval) + "y ago";
      interval = seconds / 2592000;
      if (interval > 1) return Math.floor(interval) + "mo ago";
      interval = seconds / 86400;
      if (interval > 1) return Math.floor(interval) + "d ago";
      interval = seconds / 3600;
      if (interval > 1) return Math.floor(interval) + "h ago";
      interval = seconds / 60;
      if (interval > 1) return Math.floor(interval) + "m ago";
      return Math.floor(seconds) + "s ago";
  }

  function renderActivityLogs(logs) {
      const container = $('#activity-log-content');
      container.empty();

      if (!logs || !logs.length) {
          container.html(
              '<p class="italic text-center text-gray-500 dark:text-gray-400">No activity yet. This panel will display recent package activities and approvals.</p>'
          );
          return;
      }

      container.off('click', '.log-toggle-btn').on('click', '.log-toggle-btn', function() {
          const target = $(this).data('target');
          const $targetEl = $(target);

          if ($targetEl.hasClass('hidden')) {
              $targetEl.removeClass('hidden');
              $(this).html('Hide additional details <i class="fa-solid fa-chevron-up fa-xs ml-1"></i>');
          } else {
              $targetEl.addClass('hidden');
              $(this).html('Show additional details <i class="fa-solid fa-chevron-down fa-xs ml-1"></i>');
          }
      });

      logs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

      const createPrimaryDetail = (key, val) => {
          if (val === null || val === undefined || val === '') return '';
          return `<div class="text-xs"><span class="font-semibold text-gray-600 dark:text-gray-400">${key}:</span> <span class="text-gray-800 dark:text-gray-200">${val}</span></div>`;
      };
      const createPrimaryNote = (note) => {
          if (note === null || note === undefined || note === '') return '';
          return `<div class="text-xs mt-1 italic"><span class="font-semibold text-gray-600 dark:text-gray-400">Note:</span> <span class="text-gray-800 dark:text-gray-200">"${note}"</span></div>`;
      };
      const createCollapsibleItem = (key, val) => {
          if (val === null || val === undefined || val === '') return '';
          return `<div class="text-xs flex justify-between items-center space-x-2">
                      <span class="text-gray-500 dark:text-gray-400">${key}</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200 text-right">${val}</span>
                  </div>`;
      };

      logs.forEach((l, index) => {
          const m = l.meta || {};
          const revisionNo = m.revision_no !== undefined ? `rev${m.revision_no}` : '';

          const activity = {
              UPLOAD: { icon: 'fa-upload', color: 'bg-blue-500', title: 'Draft Saved / Uploaded' },
              DOWNLOAD: { icon: 'fa-download', color: 'bg-green-500', title: 'Downloaded' },
              SUBMIT_APPROVAL: { icon: 'fa-paper-plane', color: 'bg-yellow-500', title: 'Approval Submitted' },
              APPROVE: { icon: 'fa-check-double', color: 'bg-green-500', title: 'Package Approved' },
              REVISE_CONFIRM: { icon: 'fa-pen-to-square', color: 'bg-purple-500', title: 'Revision Confirmed' },
              REJECT: { icon: 'fa-times-circle', color: 'bg-red-500', title: 'Package Rejected' },
              ROLLBACK: { icon: 'fa-undo', color: 'bg-orange-500', title: 'Revision Rolled Back' },
              default: { icon: 'fa-info-circle', color: 'bg-gray-500', title: l.activity_code }
          };

          const { icon, color, title } = activity[l.activity_code] || activity.default;

          const timeAgo = l.created_at ? formatTimeAgo(new Date(l.created_at)) : '';
          const fullTimestamp = l.created_at ? formatDateTime(l.created_at) : '';
          const userLabel = l.user_name ? `${l.user_name}` : (l.user_id ? `User #${l.user_id}` : 'System');

          const logId = `log-details-${l.id}`;

          let alwaysVisibleHtml = '';
          let collapsibleHtml = '';

          if (l.activity_code === 'UPLOAD') {
              alwaysVisibleHtml = [
                  createPrimaryDetail("ECN", m.ecn_no),
                  createPrimaryDetail("Label", m.revision_label),
                  createPrimaryNote(m.note)
              ].filter(Boolean).join('');

              const productInfo = [
                  createCollapsibleItem("Customer", m.customer_code),
                  createCollapsibleItem("Model", m.model_name),
                  createCollapsibleItem("Part No", m.part_no)
              ].filter(Boolean).join('');

              const docInfo = [
                  createCollapsibleItem("Doc Group", m.doctype_group),
                  createCollapsibleItem("Sub-Category", m.doctype_subcategory)
              ].filter(Boolean).join('');

              collapsibleHtml = [
                  productInfo,
                  docInfo ? `<div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700/50 space-y-1">${docInfo}</div>` : ''
              ].filter(Boolean).join('');

          } else if (['SUBMIT_APPROVAL', 'REVISE_CONFIRM'].includes(l.activity_code)) {
              alwaysVisibleHtml = [
                  createPrimaryDetail("ECN", m.ecn_no),
                  createPrimaryDetail("Label", m.revision_label),
                  createPrimaryDetail("Previous Status", m.previous_status),
                  createPrimaryNote(m.note)
              ].filter(Boolean).join('');

          } else {
              alwaysVisibleHtml = createPrimaryNote(m.note);
          }


          const isLast = index === logs.length - 1;

          const el = $(`
              <div class="relative">
                  ${!isLast ? '<div class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gray-300 dark:bg-gray-700"></div>' : ''}
                  <div class="relative flex items-start space-x-4 pb-8">
                      <div class="flex-shrink-0">
                          <span class="flex items-center justify-center h-10 w-10 rounded-full ${color} text-white shadow-md z-10"><i class="fa-solid ${icon}"></i></span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5">
                          <div class="flex justify-between items-center">
                              <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                  ${title}
                                  ${revisionNo ? `<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">${revisionNo}</span>` : ''}
                              </p>
                              <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                  ${fullTimestamp} | ${timeAgo}
                              </span>
                          </div>
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">by <strong>${userLabel}</strong></p>

                          ${alwaysVisibleHtml ? `<div class="mt-2 space-y-1">${alwaysVisibleHtml}</div>` : ''}

                          ${collapsibleHtml ? `
                              <div class="mt-2">
                                  <button class="log-toggle-btn text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline" data-target="#${logId}">
                                      Show additional details <i class="fa-solid fa-chevron-down fa-xs ml-1"></i>
                                  </button>
                                  <div id="${logId}" class="hidden mt-2 space-y-2 p-3 bg-gray-100 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700/50">
                                      ${collapsibleHtml}
                                  </div>
                              </div>
                          ` : ''}
                      </div>
                  </div>
              </div>
          `);
          container.append(el);
      });
  }

  window.openPackageDetails = function(id) {
      const existing = document.getElementById('package-details-modal');
      if (existing) existing.remove();

      const loaderOverlay = document.createElement('div');
      loaderOverlay.id = 'package-details-modal';
      loaderOverlay.className = 'fixed inset-0 bg-black bg-opacity-40 p-4 flex items-center justify-center z-50';
      loaderOverlay.innerHTML = `<div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center gap-3"><div class="w-8 h-8 border-4 border-blue-400 border-t-transparent rounded-full animate-spin"></div><div class="text-sm text-gray-700 dark:text-gray-300">Loading package details...</div></div>`;
      document.body.appendChild(loaderOverlay);

      fetch(`{{ url('/files') }}` + '/' + encodeURIComponent(id))
          .then(res => {
              if (!res.ok) throw new Error('Failed to load details');
              return res.json();
          })
          .then(json => {
              const pkg = json.package || {};
              const files = json.files || { count: 0, size_bytes: 0 };

              const loader = document.getElementById('package-details-modal');
              if (loader) loader.remove();

              const packageNo = pkg.package_no ?? pkg.packageNo ?? 'N/A';
              const revisionNo = pkg.revision_no ?? pkg.current_revision_no ?? pkg.revisionNo ?? 0;
              const customerCode = pkg.customer_code ?? pkg.customerCode ?? pkg.customer_code ?? '-';
              const modelName = pkg.model_name ?? pkg.modelName ?? '-';
              const partNo = pkg.part_no ?? pkg.partNo ?? '-';
              const docgroupName = pkg.docgroup_name ?? pkg.docgroupName ?? '-';
              const subcatName = pkg.subcategory_name ?? pkg.subcategoryName ?? '-';
              const partGroup = pkg.code_part_group ?? pkg.codePartGroup ?? '-';
              const createdAt = formatDateTime(pkg.created_at ?? pkg.revision_created_at ?? pkg.createdAt);
              const updatedAt = formatDateTime(pkg.updated_at ?? pkg.updatedAt);
              const receiptDate = formatDateOnly(pkg.receipt_date);
              const revisionNote = pkg.revision_note ?? pkg.note ?? '';
              const ecnNo = pkg.ecn_no ?? '-';
              const revisionLabel = pkg.revision_label_name ?? '-';

              const overlay = document.createElement('div');
              overlay.id = 'package-details-modal';
              overlay.className = 'fixed inset-0 bg-black bg-opacity-40 p-4 flex items-center justify-center z-50';
              overlay.addEventListener('click', function (ev) {
                  if (ev.target === overlay) closePackageDetails();
              });

              const dialog = document.createElement('div');
              dialog.className = 'bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-4xl w-full overflow-hidden';
              dialog.setAttribute('role', 'dialog');
              dialog.setAttribute('aria-modal', 'true');
              dialog.innerHTML = `
                  <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
                      <div class="flex items-center gap-3">
                          <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 text-blue-500 bg-blue-100 dark:bg-blue-900/50 rounded-full">
                              <i class="fa-solid fa-box-archive"></i>
                          </div>
                          <div>
                              <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Package Details</h3>
                              <p class="text-sm text-gray-500 dark:text-gray-400">Package No: <span class="font-semibold text-gray-700 dark:text-gray-200">${packageNo}</span></p>
                          </div>
                      </div>
                      <button id="pkg-close-btn" class="text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                          <i class="fa-solid fa-xmark fa-xl"></i>
                      </button>
                  </div>

                  <div class="p-5 max-h-[70vh] overflow-y-auto space-y-6">
                      <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                      <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3">Revision & Status</h4>
                      <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                          <div class="sm:col-span-1">
                              <dt class="text-gray-500">Revision No.</dt>
                              <dd class="font-semibold text-gray-900 dark:text-gray-100">${revisionNo}</dd>
                          </div>
                          <div class="sm:col-span-1">
                              <dt class="text-gray-500">Revision Label</dt>
                              <dd class="font-semibold text-gray-900 dark:text-gray-100">${revisionLabel}</dd>
                          </div>
                          <div class="sm:col-span-1">
                              <dt class="text-gray-500">ECN No.</dt>
                              <dd class="font-semibold text-gray-900 dark:text-gray-100">${ecnNo}</dd>
                          </div>
                          
                      </dl>
                  </div>

                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                          <div class="space-y-3">
                              <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Product Information</h4>
                              <dl class="text-sm space-y-2">
                                  <div class="flex justify-between"><dt class="text-gray-500">Customer</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${customerCode}</dd></div>
                                  <div class="flex justify-between"><dt class="text-gray-500">Model</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${modelName}</dd></div>
                                  <div class="flex justify-between"><dt class="text-gray-500">Part No.</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${partNo}</dd></div>
                              </dl>
                          </div>
                          <div class="space-y-3">
                              <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Document Classification</h4>
                              <dl class="text-sm space-y-2">
                                  <div class="flex justify-between"><dt class="text-gray-500">Document Group</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${docgroupName}</dd></div>
                                  <div class="flex justify-between"><dt class="text-gray-500">Sub Category</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${subcatName}</dd></div>
                                  <div class="flex justify-between"><dt class="text-gray-500">Part Group</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${partGroup}</dd></div>
                              </dl>
                          </div>
                      </div>

                      <div>
                          <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Revision Note</h4>
                          <div class="p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${revisionNote || '<span class="italic text-gray-400">No note provided.</span>'}</div>
                      </div>

                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                          <div class="space-y-3">
                              <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">File Summary</h4>
                              <dl class="text-sm space-y-2">
                                  <div class="flex justify-between"><dt class="text-gray-500">Total Files</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${files.count}</dd></div>
                                  <div class="flex justify-between"><dt class="text-gray-500">Total Size</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${bytesToSize(files.size_bytes)}</dd></div>
                              </dl>
                          </div>
                          <div class="space-y-3">
                              <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Timestamps</h4>
                              <dl class="text-sm space-y-2">
                                  <div class="flex justify-between"><dt class="text-gray-500">Receipt Date</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${receiptDate}</dd></div>
                                  <div class="flex justify-between"><dt class="text-gray-500">Created At</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${createdAt}</dd></div>
                                  <div class="flex justify-between"><dt class="text-gray-500">Last Updated</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${updatedAt}</dd></div>
                              </dl>
                          </div>
                      </div>

                      <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                          <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center">
                              <i class="fa-solid fa-clipboard-list mr-2 text-blue-500"></i>
                              Activity Log
                          </h4>
                          <div id="activity-log-content" class="space-y-4 max-h-96 overflow-y-auto pr-2">
                              <p class="italic text-center text-gray-500 dark:text-gray-400">Loading activity logs...</p>
                          </div>
                      </div>
                  </div>

                  <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                      <button id="pkg-close-btn-2" class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                          <i class="fa-solid fa-xmark"></i>
                          Close
                      </button>
                  </div>
              `;

              overlay.appendChild(dialog);
              document.body.appendChild(overlay);

              const closeBtn = document.getElementById('pkg-close-btn');
              const closeBtn2 = document.getElementById('pkg-close-btn-2');
              if (closeBtn) closeBtn.addEventListener('click', closePackageDetails);
              if (closeBtn2) closeBtn2.addEventListener('click', closePackageDetails);
              if (closeBtn) closeBtn.focus();

              function escHandler(e) { if (e.key === 'Escape') closePackageDetails(); }
              document.addEventListener('keydown', escHandler);

              overlay._cleanup = function() { document.removeEventListener('keydown', escHandler); };

              fetch(`{{ route('upload.drawing.activity-logs') }}`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                  },
                  body: JSON.stringify({
                      customer: pkg.customer_id,
                      model: pkg.model_id,
                      partNo: pkg.product_id,
                      docType: pkg.docgroup_id,
                      category: pkg.subcategory_id || null,
                      partGroup: pkg.part_group_id,
                      revision_no: pkg.revision_no
                  })
              })
                  .then(res => {
                      if (!res.ok) throw new Error('Failed to load activity logs');
                      return res.json();
                  })
                  .then(data => {
                      renderActivityLogs(data.logs || []);
                  })
                  .catch(err => {
                      console.error('Error fetching activity logs:', err);
                      $('#activity-log-content').html('<p class="italic text-center text-gray-500 dark:text-gray-400">Failed to load activity logs.</p>');
                  });
          })
          .catch(err => {
              const loader = document.getElementById('package-details-modal');
              if (loader) loader.remove();
              alert('Unable to load package details: ' + err.message);
          });
  }

  // Download confirmation functions
  let countdownInterval;
  let packageIdToDownload;
  let downloadAbortController = null;

  window.confirmDownload = function(packageId) {
    packageIdToDownload = packageId;
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
                    customClass: { popup: 'swal2-popup border' },
                    didOpen: (popup) => {
                        const p = popup.querySelector('.swal2-popup');
                        if (p) p.style.borderColor = t_err.border;
                    },
                    confirmButtonColor: '#2563eb',
                });
            });
    });
  };

});
</script>
@endpush

@push('styles')
    <style>
        #activity-log-content::-webkit-scrollbar {
            width: 6px;
        }
        #activity-log-content::-webkit-scrollbar-track {
            background: transparent;
        }
        #activity-log-content::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 20px;
        }
        .dark #activity-log-content::-webkit-scrollbar-thumb {
            background-color: #4b5563;
        }
        #activity-log-content:hover::-webkit-scrollbar-thumb {
            background-color: #93c5fd;
        }
        .dark #activity-log-content:hover::-webkit-scrollbar-thumb {
            background-color: #60a5fa;
        }
    </style>
@endpush
