@extends('layouts.app')

@section('title', 'Activity Logs - Trace')
@section('header-title', 'Activity Logs')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900">
  <div class="sm:flex sm:items-center sm:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Activity Logs</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Trace and monitor all system activities.</p>
    </div>
  </div>

  {{-- Filter section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
      <div class="relative w-full sm:w-72">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i id="search-icon-static" class="fa-solid fa-magnifying-glass text-gray-400 transition-opacity duration-200"></i>
              <i id="search-icon-loading" class="fa-solid fa-spinner fa-spin text-blue-500 opacity-0 transition-opacity duration-200 absolute left-3"></i>
          </div>

          <input type="text" 
              id="custom-search" 
              class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-full leading-5 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out shadow-sm" 
              placeholder="Search User, Activity, etc..."
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
        <button id="btnDownloadExcel"
          type="button"
          class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border border-green-500 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-200 hover:bg-green-100 dark:hover:bg-green-900/60">
          {{-- normal state --}}
          <span class="btn-label inline-flex items-center gap-2">
            <i class="fa-solid fa-file-excel"></i>
            <span>Download Summary</span>
          </span>

          {{-- loading state --}}
          <!-- <span class="btn-spinner hidden inline-flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Preparing...</span>
          </span> -->
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

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
      <div>
        <label for="date_range_input" class="text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
        <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid fa-calendar-days text-gray-400"></i>
            </div>
            <input type="text" id="date_range_input" class="block w-full rounded-md border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-0 focus:outline-none sm:text-sm py-2 pl-10 pr-3" placeholder="Select Date Range">
        </div>
      </div>
      
      <div>
        <label for="user" class="text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
        <div class="relative mt-1">
          <select id="user" class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <option value="All" selected>All</option>
          </select>
        </div>
      </div>

      <div>
        <label for="activity_code" class="text-sm font-medium text-gray-700 dark:text-gray-300">Activity Type</label>
        <div class="relative mt-1">
          <select id="activity_code" class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <option value="All" selected>All</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table id="activityTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activity</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ECN</th>
            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
        </tbody>
      </table>
  </div>

</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script>
$(function () {
  let table;
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
        data: function (params) {
          return {
            select2: field,
            q: params.term || '',
            page: params.page || 1
          };
        },
        processResults: function (data, params) {
          params.page = params.page || 1;
          const results = Array.isArray(data.results) ? data.results.slice() : [];
          if (params.page === 1 && !results.some(r => r.id === 'All')) {
            results.unshift({ id: 'All', text: 'All' });
          }
          return {
            results,
            pagination: { more: data.pagination ? data.pagination.more : false }
          };
        },
        templateResult: function (item) {
          if (item.loading) return item.text;
          return $('<div class="text-sm">' + (item.text || item.id) + '</div>');
        },
        templateSelection: function (item) {
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
      user_id:       valOrAll($('#user').val()),
      activity_code: valOrAll($('#activity_code').val()),
      date_start:    dateStart,
      date_end:      dateEnd
    };
  }

  // Initialize DataTable
  function initTable() {
    const $staticIcon  = $('#search-icon-static');
    const $loadingIcon = $('#search-icon-loading')

    table = $('#activityTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
      ajax: {
        url: '{{ route("activity-logs.list") }}',
        type: 'GET',
        data: function (d) {
          const f = getCurrentFilters();
          d.user_id       = f.user_id;
          d.activity_code = f.activity_code;
          d.date_start    = f.date_start;
          d.date_end      = f.date_end;
        },
        error: function (xhr, error, thrown) {
            console.error('DataTable Error:', error);
            $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
            $staticIcon.removeClass('opacity-0');
        }
      },
      order: [[ 1, 'desc' ]],

      createdRow: function(row, data, dataIndex) {
        $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700');
      },

      columns: [
        { data: null, name: 'No', orderable: false, searchable: false },
        { 
            data: 'created_at', 
            name: 'created_at', 
            searchable: false,
            render: function(data) {
                if(!data) return '-';
                const d = new Date(data);
                return d.toLocaleString('id-ID');
            }
        },
        { data: 'user_name', name: 'user_name', searchable: true, defaultContent: 'System' },
        { 
            data: 'activity_code', 
            name: 'activity_code', 
            searchable: true,
            render: function(data) {
                const colors = {
                    'UPLOAD': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                    'APPROVE': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                    'REJECT': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                    'DOWNLOAD': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                    'SUBMIT_APPROVAL': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                    'SHARE_PACKAGE': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
                    'SHARE_INTERNAL': 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
                    'ROLLBACK': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                    'REVISE_CONFIRM': 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
                };
                const colorClass = colors[data] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">${data}</span>`;
            }
        },
        { 
            data: 'ecn_no', 
            name: 'ecn_no', 
            orderable: true, 
            searchable: true,
            render: function(data) {
                return data ? `<span class="font-mono text-sm">${data}</span>` : '-';
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
                
                // --- Helper Styles (Agar konsisten) ---
                const mainTextClass = "text-sm font-bold text-gray-800 dark:text-gray-200 block";
                const subTextClass  = "text-xs text-gray-500 dark:text-gray-400 mt-0.5 block";
                const badgeRev = (rev) => `<span class="ml-1 px-1.5 py-0.5 rounded text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 font-mono">Rev ${rev ?? '-'}</span>`;

                // 1. SUBMIT APPROVAL (Baru ditambahkan)
                if (code === 'SUBMIT_APPROVAL') {
                    return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-purple-600 dark:text-purple-400">
                                <i class="fa-solid fa-file-signature mr-1"></i> Request Approval
                            </span>
                            <div class="${subTextClass}">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)}
                                <span class="mx-1">•</span>
                                ${data.customer_code || ''} ${data.model_name ? '• ' + data.model_name : ''}
                            </div>
                        </div>`;
                }

                // 2. REVISE CONFIRM (Baru ditambahkan)
                if (code === 'REVISE_CONFIRM') {
                    return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-teal-600 dark:text-teal-400">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Revision Confirmed
                            </span>
                            <div class="${subTextClass}">
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)}
                                <div class="mt-0.5 flex items-center gap-1 text-[10px] opacity-80">
                                    <span>${data.previous_status || 'Approved'}</span>
                                    <i class="fa-solid fa-arrow-right-long mx-0.5"></i>
                                    <span>Draft</span>
                                </div>
                            </div>
                        </div>`;
                }

                // 3. APPROVE & REJECT
                if (code === 'APPROVE' || code === 'REJECT') {
                    const isApprove = code === 'APPROVE';
                    const colorClass = isApprove ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                    const iconClass  = isApprove ? 'fa-circle-check' : 'fa-circle-xmark';
                    const labelText  = isApprove ? 'Approved' : 'Rejected';

                    let detailHtml = '';
                    if (data.part_no) {
                        detailHtml = `
                            <div class="mt-1 p-1.5 bg-gray-50 dark:bg-gray-700/50 rounded border border-gray-100 dark:border-gray-600 inline-block min-w-[200px]">
                                <div class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                    ${data.part_no} ${badgeRev(data.revision_no)}
                                </div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    ${data.customer_code || '-'} • ${data.model_name || '-'} • ${data.ecn_no || '-'}
                                </div>
                            </div>
                        `;
                    } else if (data.note) {
                        // Fallback untuk data lama yg cuma punya note
                        detailHtml = `<div class="text-xs italic text-gray-500 mt-1">"${data.note}"</div>`;
                    }

                    return `
                        <div class="flex flex-col items-start">
                            <span class="${mainTextClass} ${colorClass}">
                                <i class="fa-regular ${iconClass} mr-1"></i> ${labelText}
                            </span>
                            ${detailHtml}
                            ${code === 'REJECT' && data.note ? `<div class="text-xs italic text-red-400 mt-1">"${data.note}"</div>` : ''}
                        </div>
                    `;
                }

                // 4. ROLLBACK
                if (code === 'ROLLBACK') {
                    let statusTransition = '';
                    if (data.previous_status) {
                        statusTransition = `
                            <div class="flex items-center gap-1 text-xs font-medium text-amber-600 dark:text-amber-500 mt-0.5">
                                <span>${data.previous_status}</span>
                                <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
                                <span>${data.current_status || 'Waiting'}</span>
                            </div>
                        `;
                    }
                    let snapshotHtml = '';
                    if (data.part_no) {
                        snapshotHtml = `<div class="text-[10px] text-gray-500 mt-0.5">${data.part_no} ${badgeRev(data.revision_no)} • ${data.ecn_no || ''}</div>`;
                    }

                    return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-amber-600 dark:text-amber-500">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Rollback
                            </span>
                            ${statusTransition}
                            ${snapshotHtml}
                            ${data.reason || data.note ? `<div class="text-xs italic text-gray-400 mt-1">"${data.reason || data.note}"</div>` : ''}
                        </div>
                    `;
                }

                // 5. UPLOAD
                if (code === 'UPLOAD') {
                    const fileInfo = data.file_count ? `<span class="ml-1 text-[10px] bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-1 rounded">${data.file_count} Files</span>` : '';
                    return `
                        <div class="flex flex-col">
                            <div class="${mainTextClass}">
                                <i class="fa-solid fa-cloud-arrow-up text-blue-500 mr-1"></i>
                                ${data.part_no || '-'} ${badgeRev(data.revision_no)}
                            </div>
                            <div class="${subTextClass}">
                                ${data.customer_code || ''} • ${data.model_name || ''} ${fileInfo}
                            </div>
                        </div>`;
                }

                // 6. DOWNLOAD
                if (code === 'DOWNLOAD') {
                    let fileName = data.downloaded_file || '-';
                    const shortName = fileName.length > 30 ? fileName.substring(0, 27) + '...' : fileName;
                    
                    return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} font-normal" title="${fileName}">
                                <i class="fa-solid fa-file-arrow-down text-gray-400 mr-1"></i> ${shortName}
                            </span>
                            <div class="${subTextClass}">
                                ${data.part_no || ''} ${data.revision_no ? badgeRev(data.revision_no) : ''}
                                ${data.file_size ? `<span class="mx-1">•</span> ${data.file_size}` : ''}
                            </div>
                        </div>`;
                }

                // 7. SHARE (PACKAGE & INTERNAL)
                if (code === 'SHARE_PACKAGE' || code === 'SHARE_INTERNAL') {
                    let target = data.shared_to || data.shared_with || data.shared_to_dept || 'Unknown';
                    // Cek jika target berbentuk array/list nama, potong biar tidak terlalu panjang
                    const displayTarget = target.length > 40 ? target.substring(0, 40) + '...' : target;

                    return `
                        <div class="flex flex-col">
                            <span class="${mainTextClass} text-indigo-600 dark:text-indigo-400">
                                <i class="fa-solid fa-share-nodes mr-1"></i> Sent Package
                            </span>
                            <span class="${subTextClass}" title="${target}">
                                To: ${displayTarget}
                            </span>
                            ${data.expired_at ? `<span class="text-[10px] text-gray-400">Exp: ${data.expired_at}</span>` : ''}
                        </div>`;
                }

                // Default Fallback (Untuk data lama atau tipe lain)
                return `<span class="text-xs text-gray-500 break-all">${JSON.stringify(data).substring(0, 50)}...</span>`;
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
    
    $('#custom-search').on('keyup', function () {
        const val = this.value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.search(val).draw(); 
        }, 500);
    });

    table.on('draw.dt', function () {
      const info = table.page.info();
      table.column(0, { page: 'current' }).nodes().each(function (cell, i) {
        cell.innerHTML = i + 1 + info.start;
      });
    });
  }

  function bindHandlers() {
    $('#user, #activity_code').on('change', function () {
      if (table) table.ajax.reload(null, true);
    });

    $('#btnResetFilters').on('click', function () {
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

      if (table) table.ajax.reload(null, true);
    });

    const $inputSearch = $('#custom-search');
    const $btnClear    = $('#btn-clear-search');
    let searchTimeout  = null;

    $inputSearch.on('keyup input', function () {
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

    $btnClear.on('click', function () {
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