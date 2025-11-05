@extends('layouts.app')

@section('title', 'Download - File Manager')
@section('header-title', 'File Manager/Download')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900" x-data="{ modalOpen: false }">
  <div class="sm:flex sm:items-center sm:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Export Files</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Find and download your files from the Data Center.</p>
    </div>

    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4 sm:mt-0">
      {{-- Card Total Document --}}
      <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-blue-500 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/50 rounded-full">
          <i class="fa-solid fa-box-archive fa-lg"></i>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Document</p>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            <span id="cardTotal">0</span>
          </p>
        </div>
      </div>
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
    </div>
  </div>

  {{-- Filter section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Filters</h3>
      <button id="btnResetFilters"
        type="button"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600
               bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
        <i class="fa-solid fa-rotate-left"></i>
        Reset Filters
      </button>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-4">
      @foreach(['Customer', 'Model', 'Document Type', 'Category'] as $label)
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
    <div class="overflow-x-auto">
      <table id="exportTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="py-3 px-4 text-left ...">No</th>
            <th class="py-3 px-4 text-left ...">Package Info</th>
            <th class="py-3 px-4 text-left ...">Revision</th>
            <th class="py-3 px-4 text-left ...">ECN No</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doc Group</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sub-Category</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Part Group</th>
            <th class="py-3 px-4 text-left ...">Uploaded At</th>
            <th class="py-3 px-4 text-center ...">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
        </tbody>
      </table>
    </div>
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
    };
  }

  function loadKPI() {
    const params = getCurrentFilters();
    $('#cardTotal, #cardWaiting, #cardApproved, #cardRejected').text('…');

    $.ajax({
      url: '{{ route("api.export.kpi") }}',
      data: params,
      dataType: 'json',
      success: function (res) {
        const c = res.cards || {};
        $('#cardTotal').text(c.total ?? 0);
        $('#cardWaiting').text(c.waiting ?? 0);
        $('#cardApproved').text(c.approved ?? 0);
        $('#cardRejected').text(c.rejected ?? 0);
      },
      error: function (xhr) {
        console.error('KPI error', xhr.responseText);
        $('#cardTotal, #cardWaiting, #cardApproved, #cardRejected').text('0');
      }
    });
  }

  // Initialize DataTable
  function initTable() {
    table = $('#exportTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route("api.export.list") }}',
        type: 'GET',
        data: function (d) {
          const f = getCurrentFilters();
          d.customer  = f.customer;
          d.model     = f.model;
          d.doc_type  = f.doc_type;
          d.category  = f.category;
        }
      },
      order: [[ 1, 'asc' ]],

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
      responsive: true,
      dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
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
    $('#customer, #model, #document-type, #category').on('change', function () {
      if (table) table.ajax.reload(null, true);
      loadKPI();
    });

    $('#btnResetFilters').on('click', function () {
      resetSelect2ToAll($('#customer'));
      resetSelect2ToAll($('#model'));
      resetSelect2ToAll($('#document-type'));
      resetSelect2ToAll($('#category'));

      if (table) table.ajax.reload(null, true);
      loadKPI();
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
              SUBMIT_APPROVAL: { icon: 'fa-paper-plane', color: 'bg-yellow-500', title: 'Approval Submitted' },
              APPROVE: { icon: 'fa-check-double', color: 'bg-green-500', title: 'Package Approved' },
              DEVICE_CONFIRM: { icon: 'fa-check', color: 'bg-teal-500', title: 'Device Confirmed' },
              LDEVICE_CONFIRM: { icon: 'fa-check', color: 'bg-teal-500', title: 'Device Confirmed (L)' },
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

          } else if (['SUBMIT_APPROVAL', 'DEVICE_CONFIRM', 'LDEVICE_CONFIRM', 'REVISE_CONFIRM'].includes(l.activity_code)) {
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
      loaderOverlay.innerHTML = `<div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center gap-3"><div class="loader-border w-8 h-8 border-4 border-blue-400 rounded-full animate-spin"></div><div class="text-sm text-gray-700 dark:text-gray-300">Loading package details...</div></div>`;
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
              const revisionStatus = pkg.revision_status ?? pkg.revisionStatus ?? pkg.status ?? '-';
              const revisionNote = pkg.revision_note ?? pkg.note ?? '';
              const isObsolete = (pkg.is_obsolete === 1 || pkg.is_obsolete === '1');
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
                          <div class="sm:col-span-1">
                              <dt class="text-gray-500">Status</dt>
                              <dd class="font-semibold">
                                  <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                      ${revisionStatus === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' :
                                      (revisionStatus === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' :
                                      (revisionStatus === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' :
                                      'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'))}
                                  ">${revisionStatus}</span>
                              </dd>
                          </div>
                          <div class="sm:col-span-1">
                              <dt class="text-gray-500">Obsolete</dt>
                              <dd class="font-semibold text-gray-900 dark:text-gray-100">${isObsolete ? '<span class="text-red-500 font-bold">Yes</span>' : 'No'}</dd>
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

  window.confirmDownload = function(packageId) {
    packageIdToDownload = packageId;
    
    Swal.fire({
      title: 'Confirm Download',
      text: "Your file will be prepared by the server. Your browser will appear to 'load' until the file is ready. Please wait.",
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Yes (5)',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#d33',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => {
        const confirmButton = Swal.getConfirmButton();
        confirmButton.disabled = true;
        let remaining = 5;
        
        confirmButton.innerHTML = `Yes (${remaining})`;
        
        countdownInterval = setInterval(() => {
          remaining--;
          if (remaining > 0) {
            confirmButton.innerHTML = `Yes (${remaining})`;
          } else {
            clearInterval(countdownInterval);
            confirmButton.innerHTML = 'Yes, start download!';
            confirmButton.disabled = false;
          }
        }, 1000);
      },
      willClose: () => {
        clearInterval(countdownInterval);
      }
    }).then((result) => {
      if (result.isConfirmed) {
        // Proceed with download
        if (packageIdToDownload) {
          window.location.href = `/file-manager.export/download-package/${packageIdToDownload}`;
        }
      }
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
