@extends('layouts.app')

@section('title', 'Activity Logs - Trace')
@section('header-title', 'Activity Logs')

@section('content')
<nav class="flex px-5 py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">

    <li class="inline-flex items-center">
      <a href="{{ route('monitoring') }}" class="inline-flex items-center text-xs sm:text-sm font-medium hover:text-blue-600 transition-colors">
        Monitoring
      </a>
    </li>

    <li aria-current="page">
      <div class="flex items-center">
        <span class="mx-1 text-gray-400 text-xs sm:text-sm">/</span>

        <span class="text-xs sm:text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded-none">
          Activity Logs
        </span>
      </div>
    </li>
  </ol>
</nav>
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900">
  <div class="sm:flex sm:items-center sm:justify-between">
    <div>
      <h2 class="text-lg sm:text-3xl font-bold text-gray-900 dark:text-gray-100 leading-tight">Activity Logs</h2>
      <p class="mt-0.5 sm:mt-1 text-[10px] sm:text-sm text-gray-500 dark:text-gray-400">Trace and monitor all system activities.</p>
    </div>
  </div>

  {{-- Filter section --}}
  <div class="mt-4 sm:mt-6 bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-none shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
      <div class="relative w-full sm:w-80 group">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i id="search-icon-static" class="fa-solid fa-magnifying-glass text-gray-400 transition-opacity duration-200"></i>
          <i id="search-icon-loading" class="fa-solid fa-spinner fa-spin text-blue-500 opacity-0 transition-opacity duration-200 absolute left-3"></i>
        </div>

        <input type="text"
          id="custom-search"
          class="block w-full pl-10 pr-10 py-1.5 sm:py-2 border border-gray-200 dark:border-gray-600 rounded-none sm:rounded-full leading-5 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-[12px] sm:text-sm transition-all"
          placeholder="Search User, Activity, etc..."
          autocomplete="off">

        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
          <button id="btn-clear-search"
            type="button"
            class="hidden text-gray-400 hover:text-red-500 focus:outline-none transition-colors p-1">
            <i class="fa-solid fa-circle-xmark text-lg"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <button id="btnDownloadExcel"
          type="button"
          class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-none bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm">
          <i class="fa-solid fa-file-excel"></i>
          <span class="whitespace-nowrap">Download Summary</span>
        </button>

        <button id="btnResetFilters"
          type="button"
          class="w-12 sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-2 text-xs rounded-none border border-gray-300 dark:border-gray-600
             bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm" title="Reset Filters">
          <i class="fa-solid fa-rotate-left"></i>
          <span class="hidden sm:inline">Reset</span>
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 sm:gap-5">
      <div class="col-span-2 sm:col-span-1">
        <label for="date_range_input" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1.5 block">Date Range</label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <i class="fa-solid fa-calendar-days text-gray-400"></i>
          </div>
          <input type="text" id="date_range_input" class="block w-full rounded-none sm:rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-[12px] sm:text-sm outline-none py-1.5 sm:py-2 pl-10 pr-3 transition-all" placeholder="Select Date Range">
        </div>
      </div>

      <div class="col-span-1 sm:col-span-1">
        <label for="user" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1.5 block">User</label>
        <div class="relative">
          <select id="user" class="js-filter appearance-none block w-full pl-3 pr-10 py-1.5 sm:py-2 text-[12px] sm:text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-none sm:rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
            <option value="All" selected>All</option>
          </select>
        </div>
      </div>

      <div class="col-span-1 sm:col-span-1">
        <label for="activity_code" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1.5 block">Activity Type</label>
        <div class="relative">
          <select id="activity_code" class="js-filter appearance-none block w-full pl-3 pr-10 py-1.5 sm:py-2 text-[12px] sm:text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-none sm:rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
            <option value="All" selected>All</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel section --}}
  <div class="mt-4 sm:mt-6 bg-white dark:bg-gray-800 rounded-none border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <table id="activityTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-700/50 text-[12px] text-gray-600 dark:text-gray-400 font-bold tracking-tight">
        <tr>
          <th class="py-2 px-4 w-8 text-center bg-gray-50 dark:bg-gray-700/50">No</th>
          <th class="py-2 px-4 w-32">Date</th>
          <th class="py-2 px-4 w-32">User</th>
          <th class="py-2 px-4 w-28">Activity</th>
          <th class="py-2 px-4 w-24">ECN</th>
          <th class="py-2 px-4 min-w-[250px]">Description</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300 text-[12px]">
      </tbody>
    </table>
  </div>

</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" />
<style>
  /* DataTables Info & Pagination Premium Style */
  #activityTable td {
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    font-size: 12px !important;
    line-height: 1.25 !important;
  }

  #activityTable th {
    padding-top: 8px !important;
    padding-bottom: 8px !important;
    font-size: 12px !important;
    white-space: nowrap !important;
  }

  .dataTables_info {
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #9ca3af !important;
    text-transform: uppercase;
    letter-spacing: 0.025em;
  }

  .dataTables_paginate {
    display: flex !important;
    gap: 0 !important;
    justify-content: center !important;
    width: 100% !important;
  }

  .dataTables_paginate .paginate_button {
    border: 1px solid #e5e7eb !important;
    border-radius: 0 !important;
    padding: 6px 14px !important;
    margin: 0 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    background: white !important;
    color: #4b5563 !important;
    transition: all 0.2s;
  }

  .dark .dataTables_paginate .paginate_button {
    background: #1f2937 !important;
    border-color: #374151 !important;
    color: #9ca3af !important;
  }

  .dataTables_paginate .paginate_button:hover {
    background: #f9fafb !important;
    color: #2563eb !important;
    border-color: #d1d5db !important;
  }

  .dataTables_paginate .paginate_button.current {
    background: #2563eb !important;
    color: white !important;
    border-color: #2563eb !important;
  }

  .dataTables_paginate .paginate_button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .dataTables_empty {
    padding: 40px !important;
    font-weight: 600;
    color: #9ca3af;
    font-size: 13px;
    text-align: center !important;
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script>
  $(function() {
    let table;
    let refreshTimeout;
    const ENDPOINT = '{{ route("activity-logs.filters") }}';
    let dateStart = '';
    let dateEnd = '';
    let dateRangeInstance = null;

    function initDateRange() {
      const now = new Date();
      const year = now.getFullYear();
      const month = (now.getMonth() + 1).toString().padStart(2, '0');
      const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();

      // Default to current month
      dateStart = `${year}-${month}-01`;
      dateEnd = `${year}-${month}-${lastDay}`;

      dateRangeInstance = new Litepicker({
        element: document.getElementById('date_range_input'),
        singleMode: false,
        allowRepick: true,
        format: 'DD MMM YYYY',
        startDate: dateStart,
        endDate: dateEnd,
        setup: (picker) => {
          picker.on('selected', (d1, d2) => {
            dateStart = formatDateJS(d1.dateInstance);
            dateEnd = formatDateJS(d2.dateInstance);
            if (table) table.ajax.reload(null, true);
          });
          picker.on('show', () => {
            const isDarkMode = document.documentElement.classList.contains('dark');
            isDarkMode ? picker.ui.classList.add('dark') : picker.ui.classList.remove('dark');
          });
        }
      });
    }

    function formatDateJS(date) {
      if (!date) return '';
      const d = new Date(date);
      if (isNaN(d.getTime())) return '';
      let month = '' + (d.getMonth() + 1);
      let day = '' + d.getDate();
      const year = d.getFullYear();
      if (month.length < 2) month = '0' + month;
      if (day.length < 2) day = '0' + day;
      return [year, month, day].join('-');
    }

    function resetSelect2ToAll($el) {
      $el.empty();
      const opt = new Option('All', 'All', true, true);
      $el.append(opt);
      $el.trigger('change');
      $el.trigger('select2:select');
    }

    function makeSelect2($el, field) {
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
            return {
              select2: field,
              q: params.term || '',
              page: params.page || 1
            };
          },
          processResults: function(data, params) {
            params.page = params.page || 1;
            const results = Array.isArray(data.results) ? data.results.slice() : [];
            if (params.page === 1 && !results.some(r => r.id === 'All')) {
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
          },
          templateResult: function(item) {
            if (item.loading) return item.text;
            return $('<div class="text-sm">' + (item.text || item.id) + '</div>');
          },
          templateSelection: function(item) {
            return item.text || item.id || 'All';
          }
        }
      });
    }

    makeSelect2($('#user'), 'user');
    makeSelect2($('#activity_code'), 'activity_code');

    function getCurrentFilters() {
      const valOrAll = v => (v && v.length ? v : 'All');
      return {
        user_id: valOrAll($('#user').val()),
        activity_code: valOrAll($('#activity_code').val()),
        date_start: dateStart,
        date_end: dateEnd
      };
    }

    // Initialize DataTable
    function initTable() {
      const $staticIcon = $('#search-icon-static');
      const $loadingIcon = $('#search-icon-loading')

      table = $('#activityTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        autoWidth: true,
        dom: 't<"flex justify-center items-center p-4 sm:p-6 border-t border-gray-50 dark:border-gray-800" p>',
        language: {
          emptyTable: `
            <div class="flex flex-col items-center justify-center py-6 text-gray-400">
              <i class="fa-solid fa-clipboard-list text-3xl mb-3 opacity-20"></i>
              <p class="font-bold text-sm">No activity logs found matching your criteria</p>
            </div>
          `
        },
        ajax: {
          url: '{{ route("activity-logs.list") }}',
          type: 'GET',
          data: function(d) {
            const f = getCurrentFilters();
            d.user_id = f.user_id;
            d.activity_code = f.activity_code;
            d.date_start = f.date_start;
            d.date_end = f.date_end;
          },
          error: function(xhr, error, thrown) {
            console.error('DataTable Error:', error);
            $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
            $staticIcon.removeClass('opacity-0');
          }
        },
        order: [
          [1, 'desc']
        ],

        createdRow: function(row, data, dataIndex) {
          $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0 text-gray-900 dark:text-gray-100');
          $('td', row).addClass('py-2 px-4 align-middle');
        },

        columns: [{
            data: null,
            name: 'No',
            orderable: false,
            searchable: false,
            className: 'text-center text-gray-400'
          },
          {
            data: 'created_at',
            name: 'created_at',
            searchable: false,
            render: function(data) {
              if (!data) return '-';
              const d = new Date(data);
              return d.toLocaleString('id-ID');
            }
          },
          {
            data: 'user_name',
            name: 'user_name',
            searchable: true,
            defaultContent: 'System',
            render: function(data) {
              return `<span class="text-[12px]">${data || 'System'}</span>`;
            }
          },
          {
            data: 'activity_code',
            name: 'activity_code',
            searchable: true,
            render: function(data) {
              const colors = {
                'UPLOAD': 'bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                'APPROVE': 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900 dark:text-green-300',
                'REJECT': 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900 dark:text-red-300',
                'DOWNLOAD': 'bg-yellow-50 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                'SUBMIT_APPROVAL': 'bg-purple-50 border border-purple-200 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                'SHARE_PACKAGE': 'bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                'SHARE_INTERNAL': 'bg-teal-50 border border-teal-200 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
                'ROLLBACK': 'bg-orange-50 border border-orange-200 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                'REVISE_CONFIRM': 'bg-teal-50 border border-teal-200 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
                'DELETE_PACKAGE': 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900 dark:text-red-300',
              };
              const colorClass = colors[data] || 'bg-gray-50 border border-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
              const labelText = data ? data.toLowerCase().replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
              return `<span class="inline-flex items-center px-2 py-0.5 rounded-none text-[10px] font-bold ${colorClass}">${labelText}</span>`;
            }
          },
          {
            data: 'meta',
            name: 'ecn_no',
            orderable: true,
            searchable: true,
            render: function(data) {
              return (data && data.ecn_no) ? `<span class="text-xs font-semibold">${data.ecn_no}</span>` : '-';
            }
          },
          {
            data: 'meta',
            name: 'meta',
            orderable: false,
            searchable: true,
            render: function(data, type, row) {
              if (!data) return '-';

              const code = row.activity_code;

              // --- Helper Styles ---
              const mainTextClass = "text-sm font-bold text-gray-800 dark:text-gray-200 block";
              const subTextClass = "text-xs text-gray-500 dark:text-gray-400 mt-0.5 block";
              const badgeRev = (rev) => `<span class="ml-1 px-1.5 py-0.5 rounded-none text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Rev ${rev ?? '-'}</span>`;
              const badgeLabel = (label) => label ? `<span class="ml-1 px-1.5 py-0.5 rounded-none text-[10px] bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800">${label}</span>` : '';

              // 1. UPLOAD
              if (code === 'UPLOAD') {
                const fileInfo = data.file_count ? `<span class="ml-1 text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-1 rounded border border-gray-200 dark:border-gray-600">${data.file_count} Files (${data.file_types || '-'})</span>` : '';
                return `
                        <div class="flex flex-col">
                            <div class="${mainTextClass}">
                                <i class="fa-solid fa-cloud-arrow-up text-blue-500 mr-1"></i>
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)} ${badgeLabel(data.revision_label)}
                            </div>
                            <div class="${subTextClass}">
                                ${data.customer_code || ''} • ${data.model_name || ''} • ${data.doctype_group || ''}
                                ${fileInfo}
                            </div>
                            ${data.note ? `<div class="text-xs italic text-gray-400 mt-0.5">"${data.note}"</div>` : ''}
                        </div>`;
              }

              // 2. SUBMIT_APPROVAL
              if (code === 'SUBMIT_APPROVAL') {
                return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-purple-600 dark:text-purple-400">
                                <i class="fa-solid fa-file-signature mr-1"></i> Request Approval
                            </span>
                            <div class="${subTextClass}">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)} ${badgeLabel(data.revision_label)}
                                <span class="mx-1">•</span>
                                ${data.customer_code || ''} ${data.model_name ? '• ' + data.model_name : ''}
                            </div>
                             ${data.ecn_no ? `<div class="text-[10px] text-gray-400 mt-0.5">ECN: ${data.ecn_no}</div>` : ''}
                        </div>`;
              }

              // 3. APPROVE & REJECT
              if (code === 'APPROVE' || code === 'REJECT') {
                const isApprove = code === 'APPROVE';

                // Warna & Icon untuk JUDUL saja
                const colorClass = isApprove ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                const iconClass = isApprove ? 'fa-circle-check' : 'fa-circle-xmark';
                const titleText = isApprove ? 'Approved' : 'Rejected';

                // Fallback data
                const customer = data.customer || data.customer_code || '-';
                const model = data.model || data.model_name || '';

                return `
        <div class="flex flex-col items-start">
            
            <span class="text-sm font-bold ${colorClass}">
                <i class="fa-regular ${iconClass} mr-1"></i> ${titleText}
            </span>

            <div class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                <span>${data.previous_status || 'Pending'}</span>
                <i class="fa-solid fa-arrow-right-long text-[10px] mx-0.5 text-gray-400"></i>
                
                <span class="text-gray-700 dark:text-gray-200">
                    ${data.current_status || titleText}
                </span>
            </div>

            <div class="${subTextClass} mt-0.5">
                <span class="text-gray-600 dark:text-gray-300 font-semibold">${data.part_no || '-'}</span> 
                ${badgeRev(data.revision_no)}
                <span class="mx-1 text-gray-300">•</span>
                <span>${customer}</span>
                ${model ? `<span class="mx-1 text-gray-300">•</span> ${model}` : ''}
            </div>

            ${data.note ? `
                <div class="text-xs italic text-gray-500 mt-1 pl-2 border-l-2 ${isApprove ? 'border-green-200' : 'border-red-200'}">
                    "${data.note}"
                </div>
            ` : ''}
        </div>
    `;
              }

              // 4. SHARE_PACKAGE
              if (code === 'SHARE_PACKAGE') {
                let target = data.shared_to || data.recipients || 'Unknown';
                // Clean up [EXP] prefix if present for display
                target = target.replace('[EXP] ', '');
                const displayTarget = target.length > 90 ? target.substring(0, 90) + '...' : target;

                return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-blue-600 dark:text-blue-400">
                                <i class="fa-solid fa-share-nodes mr-1"></i> Shared Package
                            </span>
                            <div class="${subTextClass}" title="${target}">
                                <span class="font-medium">To:</span> ${displayTarget}
                            </div>
                            <div class="text-[10px] text-gray-500 mt-0.5">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)} • Exp: ${data.expired_at || '-'}
                            </div>
                        </div>`;
              }

              // 5. DOWNLOAD
              if (code === 'DOWNLOAD') {
                let fileName = data.downloaded_file || '-';
                const shortName = fileName.length > 60 ? fileName.substring(0, 57) + '...' : fileName;

                return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} font-normal" title="${fileName}">
                                <i class="fa-solid fa-file-arrow-down text-gray-500 mr-1"></i> ${shortName}
                            </span>
                            <div class="${subTextClass}">
                                ${data.part_no || ''} ${badgeRev(data.revision_no)} ${badgeLabel(data.revision_label)}
                                ${data.file_size ? `<span class="mx-1">•</span> ${data.file_size}` : ''}
                            </div>
                        </div>`;
              }

              // 6. ROLLBACK
              if (code === 'ROLLBACK') {
                return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-amber-600 dark:text-amber-500">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Rollback
                            </span>
                            <div class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                <span>${data.previous_status || '?'}</span>
                                <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
                                <span>${data.current_status || '?'}</span>
                            </div>
                            <div class="text-[10px] text-gray-500 mt-0.5">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)}
                            </div>
                            ${data.note ? `<div class="text-xs italic text-gray-400 mt-0.5">"${data.note}"</div>` : ''}
                        </div>
                    `;
              }

              // 7. REVISE_CONFIRM
              if (code === 'REVISE_CONFIRM') {
                return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-teal-600 dark:text-teal-400">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Revision Confirmed
                            </span>
                            <div class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                <span>${data.previous_status || '?'}</span>
                                <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
                                <span>${data.current_status || '?'}</span>
                            </div>
                            <div class="text-[10px] text-gray-500 mt-0.5">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)} ${badgeLabel(data.revision_label)}
                            </div>
                        </div>`;
              }

              // 8. SHARE_INTERNAL
              if (code === 'SHARE_INTERNAL') {
                let target = data.shared_to_dept ? `Dept: ${data.shared_to_dept}` : (data.recipients || 'Unknown');
                const displayTarget = target.length > 90 ? target.substring(0, 90) + '...' : target;

                return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-teal-600 dark:text-teal-400">
                                <i class="fa-solid fa-share-from-square mr-1"></i> Shared Internal
                            </span>
                            <div class="${subTextClass}" title="${target}">
                                <span class="font-medium">To:</span> ${displayTarget}
                            </div>
                             <div class="text-[10px] text-gray-500 mt-0.5">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)}
                            </div>
                            ${data.note ? `<div class="text-xs italic text-gray-400 mt-0.5">"${data.note}"</div>` : ''}
                        </div>`;
              }

              // 9. DELETE_PACKAGE
              if (code === 'DELETE_PACKAGE') {
                return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-red-600 dark:text-red-400">
                                <i class="fa-solid fa-trash-can mr-1"></i> Package Deleted
                            </span>
                             <div class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                <span>Status: ${data.revision_status || 'Draft'}</span>
                            </div>
                            <div class="text-[10px] text-gray-500 mt-0.5">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)} ${badgeLabel(data.revision_label)}
                            </div>
                             ${data.ecn_no ? `<div class="text-[10px] text-gray-400 mt-0.5">ECN: ${data.ecn_no}</div>` : ''}
                        </div>`;
              }

              // Default Fallback
              return `<span class="text-xs text-gray-500 break-all">${JSON.stringify(data).substring(0, 50)}...</span>`;
            }
          }
        ],
      });

      table.on('processing.dt', function(e, settings, processing) {
        if (processing) {
          $staticIcon.addClass('opacity-0');
          $loadingIcon.removeClass('opacity-0').addClass('opacity-100');
        } else {
          $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
          $staticIcon.removeClass('opacity-0');
        }
      });

      $('#custom-search').on('keyup', function() {
        const val = this.value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
          table.search(val).draw();
        }, 500);
      });

      table.on('draw.dt', function() {
        $('.dataTables_scrollBody').addClass('custom-scrollbar');
        const info = table.page.info();
        table.column(0, {
          page: 'current'
        }).nodes().each(function(cell, i) {
          const num = i + 1 + info.start;
          cell.innerHTML = `<span class="text-[12px] font-black text-gray-500 dark:text-gray-400 tracking-tighter">${num}</span>`;
        });

        // Force column width recalculation
        setTimeout(() => {
          table.columns.adjust();
        }, 50);
      });
    }


    function refreshData() {
      clearTimeout(refreshTimeout);
      refreshTimeout = setTimeout(() => {
        if (table) table.ajax.reload(null, true);
      }, 50);
    }

    function bindHandlers() {
      $('#user, #activity_code').on('change', refreshData);

      $('#btnResetFilters').on('click', function() {
        try {
          resetSelect2ToAll($('#user'));
          resetSelect2ToAll($('#activity_code'));

          // Reset Date Range to current month
          const now = new Date();
          const year = now.getFullYear();
          const month = (now.getMonth() + 1).toString().padStart(2, '0');
          const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
          dateStart = `${year}-${month}-01`;
          dateEnd = `${year}-${month}-${lastDay}`;

          if (dateRangeInstance) {
            dateRangeInstance.setDateRange(dateStart, dateEnd);
          }
        } finally {
          refreshData();
        }
      });

      const $inputSearch = $('#custom-search');
      const $btnClear = $('#btn-clear-search');
      let searchTimeout = null;

      $inputSearch.on('keyup input', function() {
        const val = this.value;
        if (val.length > 0) {
          $btnClear.removeClass('hidden');
        } else {
          $btnClear.addClass('hidden');
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
          if (table.search() !== val) {
            table.search(val).draw();
          }
        }, 600);
      });

      $btnClear.on('click', function() {
        $inputSearch.val('').focus();
        $btnClear.addClass('hidden');
        table.search('').draw();
      });

      $('#btnDownloadExcel').on('click', function() {
        const filters = getCurrentFilters();
        const searchValue = $('#custom-search').val();

        const params = new URLSearchParams({
          user_id: filters.user_id,
          activity_code: filters.activity_code,
          date_start: filters.date_start,
          date_end: filters.date_end,
          search_value: searchValue
        });

        window.location.href = '{{ route("activity-logs.export") }}?' + params.toString();
      });
    }

    initDateRange();
    initTable();
    bindHandlers();
  });
</script>
@endpush