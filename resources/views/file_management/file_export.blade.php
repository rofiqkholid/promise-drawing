@extends('layouts.app')

@section('title', 'Download - File Manager')
@section('header-title', 'File Manager - Download')

@section('content')
<div class="w-full px-2 sm:px-4 lg:px-6 xl:px-4 2xl:px-6">
<div class="w-full">
<nav class="flex px-3 sm:px-5 py-2 sm:py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-md" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">

        <li class="inline-flex items-center">
            <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600 transition-colors">
                <i class="fa-solid fa-chart-line mr-2"></i> Monitoring
            </a>
        </li>

        <li aria-current="page">
            <div class="flex items-center">
                <span class="text-gray-400 mx-1">/</span>

                <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20">
                    Download Files
                </span>
            </div>
        </li>
    </ol>
</nav>
</div>

<div class="w-full p-3 sm:p-4 lg:p-6 bg-gray-50 dark:bg-gray-900" x-data="{ modalOpen: false }">
  <div class="sm:flex sm:items-center sm:justify-between">
    <div>
      <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl lg:text-3xl">Download Files</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Find and download your files from the Data Center.</p>
    </div>

    {{-- KPI Cards - Horizontal Scroll on Mobile --}}
    <div class="mt-4 flex overflow-x-auto pb-2 sm:pb-0 gap-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:mt-0 no-scrollbar">
      <div class="flex-shrink-0 w-[240px] sm:w-auto flex items-center p-3 sm:p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 sm:h-12 sm:w-12 text-blue-500 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/50 rounded-md">
          <i class="fa-solid fa-box-archive text-base sm:text-lg"></i>
        </div>
        <div class="ml-3 sm:ml-4">
          <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Total Packages</p>
          <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
            <span id="cardTotal">0</span>
          </p>
        </div>
      </div>

      <div class="flex-shrink-0 w-[240px] sm:w-auto flex items-center p-3 sm:p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 sm:h-12 sm:w-12 text-yellow-500 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/50 rounded-md">
          <i class="fa-solid fa-layer-group text-base sm:text-lg"></i>
        </div>
        <div class="ml-3 sm:ml-4">
          <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Total Revisions</p>
          <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
            <span id="cardTotalRevisions">0</span>
          </p>
        </div>
      </div>

      <div class="flex-shrink-0 w-[240px] sm:w-auto flex items-center p-3 sm:p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm">
        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 sm:h-12 sm:w-12 text-green-500 dark:text-green-400 bg-green-100 dark:bg-green-900/50 rounded-md">
          <i class="fa-solid fa-cloud-arrow-down text-base sm:text-lg"></i>
        </div>
        <div class="ml-3 sm:ml-4">
          <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Total Download</p>
          <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
            <span id="cardDownload">0</span>
          </p>
        </div>
      </div>
    </div>
  </div>

  {{-- Clean & Unified Search Center --}}
  <div class="mt-4 sm:mt-6 bg-white dark:bg-gray-800 px-4 sm:px-6 py-4 sm:py-6 rounded-md border border-gray-200 dark:border-gray-700 transition-all duration-300">
    
    {{-- Slim Hero Search Bar --}}
    <div class="flex flex-col items-center mb-6">
        <div class="relative w-full max-w-3xl group"> 
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <i id="search-icon-static" class="fa-solid fa-magnifying-glass text-blue-500 text-lg transition-all duration-300"></i>
                <i id="search-icon-loading" class="fa-solid fa-spinner fa-spin text-blue-500 text-lg opacity-0 transition-opacity duration-200 absolute left-5"></i>
            </div>

            <input type="text" 
                id="custom-export-search" 
                class="block w-full pl-12 sm:pl-14 pr-16 sm:pr-20 py-3 sm:py-3.5 border border-gray-300 dark:border-gray-600 rounded-full leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 text-sm sm:text-base transition-all duration-300 group" 
                placeholder="Search drawings..."
                autocomplete="off">

            <div class="absolute inset-y-0 right-0 pr-4 flex items-center gap-3">
                <div class="hidden sm:flex items-center pointer-events-none text-gray-400 text-[10px] font-bold border border-gray-200 dark:border-gray-700 rounded px-1.5 py-0.5 shadow-sm group-focus-within:hidden">/</div>
                <button id="btn-clear-search" type="button" class="hidden text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full"><i class="fa-solid fa-circle-xmark text-lg"></i></button>
            </div>
        </div>

        {{-- Recent Searches Pills - Centered & Collapsable --}}
        <div id="recent-searches-wrapper" class="hidden w-full transition-all duration-300 overflow-hidden">
            <div id="recent-searches-container" class="flex flex-wrap justify-center items-center gap-2 mt-4 min-h-[24px]">
                {{-- Tags injected by JS --}}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-12 gap-3 sm:gap-4 items-end">
      @foreach(['Customer', 'Model', 'Document Type', 'Category', 'Project Status'] as $label)
      <div class="col-span-1 md:col-span-2">
        <label for="{{ Str::slug($label) }}" class="text-[10px] uppercase font-bold text-gray-600 dark:text-gray-400 tracking-wider mb-2 block">{{ $label }}</label>
        <select id="{{ Str::slug($label) }}" class="js-filter appearance-none block w-full pl-3 pr-10 py-2.5 text-xs border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"></select>
      </div>
      @endforeach

      <div class="col-span-2 md:col-span-2 flex items-center gap-2">
            <button id="btnResetFilters" type="button" class="w-9 sm:w-10 h-9 sm:h-[38px] inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white hover:border-gray-400 transition-all" title="Reset Filters">
                <i class="fa-solid fa-rotate-left text-xs"></i>
            </button>

            <button id="btnDownloadSummary" type="button" class="flex-1 h-9 sm:h-[38px] inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold rounded-md bg-emerald-600 text-white hover:bg-emerald-700 active:bg-emerald-800 transition-all shadow-sm">
                <span class="btn-label flex items-center gap-1.5 sm:gap-2"><i class="fa-solid fa-file-excel text-sm sm:text-base"></i><span class="hidden sm:inline">Export</span><span class="sm:hidden">XLS</span></span>
                <span class="btn-spinner hidden"><i class="fa-solid fa-circle-notch fa-spin text-sm sm:text-base"></i></span>
            </button>
      </div>
    </div>
  </div>

  {{-- Tabel section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
      <table id="exportTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-600 dark:text-gray-400 font-bold tracking-tight">
                <tr>
                    <th class="px-4 py-3 w-8 text-center bg-gray-50 dark:bg-gray-700/50">No</th>
                    <th class="px-4 py-3 min-w-[200px]">Package Info</th>
                    <th class="px-4 py-3 w-28">Current Rev</th>
                    <th class="px-4 py-3">ECN</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Part Group</th>
                    <th class="px-4 py-3 min-w-[150px] max-w-[200px]">Revision Note</th> 
                    <th class="px-4 py-3 w-32">Uploaded</th>
                    <th class="px-4 py-3 w-24 text-right">Size</th>
                    <th class="px-4 py-3 w-24 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 border-t border-gray-100 dark:border-gray-700">
                {{-- DataTables fills this --}}
            </tbody>
      </table>
  </div>
  </div>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
  let currentStatus = 'All';

  const urlParams = new URLSearchParams(window.location.search);
  if(urlParams.get('q')) $('#custom-export-search').val(urlParams.get('q'));

  // Power User Feature: Keyboard Shortcut "/"
  $(document).on('keyup', function(e) {
      if (e.key === '/' && !$(e.target).is('input, textarea, select')) {
          $('#custom-export-search').focus();
      }
  });

  function syncUrlWithFilters() {
      const params = new URLSearchParams(window.location.search);
      const q = $('#custom-export-search').val();
      if (q) params.set('q', q); else params.delete('q');
      const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
      window.history.replaceState({path: newUrl}, '', newUrl);
  }

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
        return $('<div class="text-sm py-1">' + (item.text || item.id) + '</div>');
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


    function highlightText(data, searchVal) {
        if (!searchVal || !data) return data;
        // Escape regex characters to prevent crashes if user types special chars
        const safeSearch = searchVal.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${safeSearch})`, 'gi');
        return data.toString().replace(regex, '<span class="bg-yellow-200 text-gray-900">$1</span>');
    }

    // Initialize DataTable
    function initTable() {
    const $staticIcon  = $('#search-icon-static');
    const $loadingIcon = $('#search-icon-loading')

    table = $('#exportTable').DataTable({
      processing: true,
      serverSide: true,
      
      autoWidth: false,
      responsive: false, 
      scrollX: true, 
      scrollCollapse: true,
      deferRender: true,
      stateSave: false,
      dom: '<"p-4"r><"w-full h-full overflow-x-auto"t><"p-4 border-t border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4"ip>',

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
      order: [[ 8, 'desc' ]],
      language: {
          info: `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50/50 dark:bg-indigo-900/20 border border-indigo-100/50 dark:border-indigo-800/50 shadow-sm transition-all hover:bg-indigo-50 dark:hover:bg-indigo-900/40">
                    <i class="fa-solid fa-database text-indigo-500 text-[10px]"></i>
                    <span class="text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-tight">Records</span>
                    <span class="text-gray-900 dark:text-gray-100 text-[11px] font-black font-mono">_START_-_END_</span>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <span class="text-indigo-600 dark:text-indigo-400 text-[11px] font-black font-mono">_TOTAL_</span>
                 </div>`,
          infoEmpty: "No Records Found",
          infoFiltered: "",
          zeroRecords: '<div class="flex flex-col items-center justify-center p-12 text-gray-400"><i class="fa-solid fa-folder-open text-4xl mb-3 opacity-20"></i><span class="text-xs italic">No matching files in your data center</span></div>'
      },
      dom: 't<"flex flex-col sm:flex-row justify-between items-center p-6 border-t border-gray-50 dark:border-gray-800 gap-4" <"flex-1"i> <"flex justify-end"p>>',
      pageLength: 10,

      createdRow: function(row, data, dataIndex) {
        $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 text-gray-900 dark:text-gray-100');
        $('td', row).addClass('py-2 px-4 align-middle'); // Standard padding
      },

      columns: [
        { 
            data: null, 
            name: 'No', 
            orderable: false, 
            searchable: false,
            className: 'text-center text-gray-400 font-mono text-xs'
        },
        {
            data: null,
            name: 'Package Info',
            searchable: true,
            orderable: false,
            render: function(data, type, row) {
                const searchVal = $('#custom-export-search').val();

                // Line 1: Part No (Bold) + Optional Partners (separated by slash)
                let mainText = row.part_no;
                if (row.partners) {
                    let pClean = row.partners.replace(/,/g, ' /'); 
                    mainText += ` / ${pClean}`;
                }
                // Highlight Line 1
                mainText = highlightText(mainText, searchVal);

                // Line 2: Customer - Model
                let subText = `${row.customer} - ${row.model}`;
                // Highlight Line 2
                subText = highlightText(subText, searchVal);

                return `
                    <div class="flex flex-col max-w-[350px]" title="${row.part_no} ${row.partners ? '/ ' + row.partners : ''}">
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">${mainText}</span>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 truncate">
                            ${subText}
                        </div>
                    </div>
                `;
            }
        },
        {
            data: null,
            name: 'Revision',
            searchable: true,
            orderable: false,
            render: function(data, type, row) {
               
                let labelBadges = '';
                if(row.revision_label_name) {
                    labelBadges = `<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 mr-1 whitespace-nowrap">${row.revision_label_name}</span>`;
                }
                
                return `
                    <div class="flex items-center min-w-[100px]">
                        ${labelBadges}
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 whitespace-nowrap">
                            Rev ${row.revision_no}
                        </span>
                    </div>
                `;
            }
        },
        {
            data: 'ecn_no', 
            name: 'ecn_no', 
            searchable: true,
            render: function(data) {
                return data ? `<span class="font-mono text-xs text-gray-600 dark:text-gray-400">${data}</span>` : '<span class="text-gray-300">-</span>';
            }
        },
        {
            data: null, 
            name: 'doctype_group', 
            searchable: true, 
            orderable: true,
            render: function(data, type, row) {
                return `
                    <div class="flex flex-col min-w-[120px]">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">${row.doctype_group}</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">${row.doctype_subcategory || ''}</span>
                    </div>
                `;
            }
        },
        
        {data: 'part_group', name: 'part_group', searchable: true, orderable: true, render: d => `<span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">${d}</span>`},
        {
            data: 'note',
            name: 'revision_note',
            searchable: true,
            orderable: false,
            render: function (data) {
                if (!data) return '<span class="text-gray-300 italic text-xs">-</span>';
                const searchVal = $('#custom-export-search').val();
                const hlData = highlightText(data, searchVal);
                // Matched width with HTML header
                return `<div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2 min-w-[150px] max-w-[200px] whitespace-normal" title="${data}">
                            ${hlData}
                        </div>`;
            }
        },
        {
            data: 'uploaded_at', 
            name: 'uploaded_at', 
            searchable: true,
            render: function(data) {
                if(!data) return '-';
                // Simple date format
                const d = new Date(data);
                return `<span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap" title="${data}">${d.toLocaleDateString()}</span>`;
            }
        },
        {
            data: 'total_size',
            name: 'size',
            searchable: false,
            orderable: true,
            className: 'text-right',
            render: function(data, type, row) {
                if (data === null) return '-';
                const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                let size = parseInt(data);
                if (size === 0) return '<span class="text-gray-300 text-xs">0 B</span>';
                const i = Math.floor(Math.log(size) / Math.log(1024));
                return `<span class="font-mono text-xs text-gray-600 dark:text-gray-400">${parseFloat((size / Math.pow(1024, i)).toFixed(1))} ${sizes[i]}</span>`;
            }
        },
        {
            data: null,
            name: 'Info',
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
                // View Button: Square (rounded-md), Larger Icon, New Icon (fa-up-right-from-square)
                let viewButton = `<button type="button" onclick="openPackageDetails('${row.id}')" 
                    class="p-2 rounded-md hover:bg-blue-100 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:bg-gray-700 transition-colors" 
                    title="View Package Details">
                    <i class="fa-solid fa-up-right-from-square text-base"></i>
                </button>`;
                
                // Download Button: Square (rounded-md), Larger Icon
                let downloadButton = `<button type="button" onclick="confirmDownload('${row.id}')" 
                    class="p-2 rounded-md hover:bg-green-100 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:bg-gray-700 transition-colors ml-1" 
                    title="Download Package">
                    <i class="fa-solid fa-download text-base"></i>
                </button>`;
                
                return `<div class="flex items-center justify-center">${viewButton}${downloadButton}</div>`;
            }
        }
      ],
    }); 

    function getSkeletonHtml() {
        let rows = '';
        for (let i = 0; i < 8; i++) {
            rows += `
                <tr class="animate-pulse border-b border-gray-50 dark:border-gray-800">
                    <td class="px-4 py-4"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-6 mx-auto"></div></td>
                    <td class="px-4 py-4">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-48 mb-2"></div>
                        <div class="h-3 bg-gray-100 dark:bg-gray-800 rounded w-32"></div>
                    </td>
                    <td class="px-4 py-4"><div class="h-5 bg-gray-200 dark:bg-gray-700 rounded-full w-20"></div></td>
                    <td class="px-4 py-4"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16"></div></td>
                    <td class="px-4 py-4">
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-1"></div>
                        <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded w-16"></div>
                    </td>
                    <td class="px-4 py-4"><div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-12"></div></td>
                    <td class="px-4 py-4"><div class="h-3 bg-gray-100 dark:bg-gray-800 rounded w-full line-clamp-2"></div></td>
                    <td class="px-4 py-4"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16"></div></td>
                    <td class="px-4 py-4"><div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-12 ml-auto"></div></td>
                    <td class="px-4 py-4"><div class="flex justify-center gap-2"><div class="h-8 w-8 bg-gray-100 dark:bg-gray-700 rounded-md"></div><div class="h-8 w-8 bg-gray-100 dark:bg-gray-700 rounded-md"></div></div></td>
                </tr>
            `;
        }
        return rows;
    }

    // Skeleton Trigger
    table.on('preXhr.dt', function() {
        $('#exportTable tbody').html(getSkeletonHtml());
    });

    // Inject initial skeleton
    $('#exportTable tbody').html(getSkeletonHtml());

    // Consolidated Draw Handler
    table.on('draw.dt', function () {
      $loadingIcon.removeClass('opacity-100').addClass('opacity-0');
      $staticIcon.removeClass('opacity-0');

      const json = table.ajax.json();
      if (json && json.kpis) {
         $('#cardTotal').text(json.kpis.total || 0);
         $('#cardTotalRevisions').text(json.kpis.total_revisions || 0);
         $('#cardDownload').text(json.kpis.total_download || 0);
      }

      const info = table.page.info();
      table.column(0, { page: 'current' }).nodes().each(function (cell, i) {
        const num = i + 1 + info.start;
        cell.innerHTML = `<span class="text-[12px] font-black text-gray-500 dark:text-gray-400 tracking-tighter">${num}</span>`;
      });
    });

    $('#exportTable tbody').on('click', 'tr', function (e) {
      if ($(e.target).closest('button').length || $(e.target).closest('a').length) return;
      const data = table.row(this).data();
      if (!data || !data.id) return;
      window.location.href = `/file-manager.export/${encodeURIComponent(data.id)}`;
    });
  }

  function bindHandlers() {
    $('#customer, #model, #document-type, #category, #project-status').on('change', function () {
      if (table) table.ajax.reload(null, true);
    });

    $('#btnResetFilters').on('click', function () {
      // SILENT RESET: Temporarily disable the change listener to prevent 5-6 redundant AJAX calls
      const $filters = $('#customer, #model, #document-type, #category, #project-status');
      $filters.off('change'); 

      resetSelect2ToAll($('#customer'));
      resetSelect2ToAll($('#model'));
      resetSelect2ToAll($('#document-type'));
      resetSelect2ToAll($('#category'));
      resetSelect2ToAll($('#project-status'));
      $('#custom-export-search').val('');
      
      // Sync State
      syncUrlWithFilters();

      // Re-enable change listener and do ONE draw
      $filters.on('change', function () {
          if (table) table.ajax.reload(null, true);
      });

      if (table) table.search('').draw();
    });

    $('#btnDownloadSummary').on('click', function() {
        const f = getCurrentFilters();
        const query = $.param(f);
        const url = '{{ route("approvals.summary") }}?' + query;
        const $btn = $(this);

        function setLoading(isLoading) {
          if (isLoading) {
            $btn.prop('disabled', true).addClass('opacity-75 cursor-wait');
            $btn.find('.btn-label').addClass('hidden');
            $btn.find('.btn-spinner').removeClass('hidden');
          } else {
            $btn.prop('disabled', false).removeClass('opacity-75 cursor-wait');
            $btn.find('.btn-label').removeClass('hidden');
            $btn.find('.btn-spinner').addClass('hidden');
          }
        }
        setLoading(true);
        const $iframe = $('<iframe>', { src: url, style: 'display:none;' }).appendTo('body');
        
        // Timeout handling
        setTimeout(() => { setLoading(false); $iframe.remove(); }, 5000);
    });

    // --- Search & Recent Searches Logic ---
    const $inputSearch = $('#custom-export-search');
    const $btnClear    = $('#btn-clear-search');
    let searchTimeout  = null;
    const RECENT_KEY   = 'recent_dwg_searches';
    
    function getRecent() {
        try {
            return JSON.parse(localStorage.getItem(RECENT_KEY)) || [];
        } catch(e) { return []; }
    }

    function saveSearch(term) {
        if (!term || term.length < 2) return;
        let recent = getRecent();
        recent = [term, ...recent.filter(t => t !== term)].slice(0, 5);
        localStorage.setItem(RECENT_KEY, JSON.stringify(recent));
        renderRecent();
    }

    function renderRecent() {
        const recent = getRecent();
        const $wrapper = $('#recent-searches-wrapper');
        const $container = $('#recent-searches-container');
        
        if (recent.length === 0) {
            $wrapper.addClass('hidden');
            $container.html('');
            return;
        }

        $wrapper.removeClass('hidden');

        let html = recent.map(t => `
            <button type="button" class="recent-tag px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-[10px] font-bold text-gray-500 dark:text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-all whitespace-nowrap" data-term="${t}">
                <i class="fa-solid fa-history mr-1 opacity-50"></i> ${t}
            </button>
        `).join('');

        html += `
            <button type="button" id="btn-clear-recent" class="w-6 h-6 inline-flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 hover:text-red-500 transition-all ml-1" title="Clear History">
                <i class="fa-solid fa-xmark text-[10px]"></i>
            </button>
        `;
        $container.html(html);
    }

    $(document).on('click', '.recent-tag', function() {
        const term = $(this).data('term');
        $inputSearch.val(term).trigger('input');
        // Instantly trigger search for tags
        clearTimeout(searchTimeout);
        syncUrlWithFilters();
        if (table) table.search(term).draw();
    });

    $(document).on('click', '#btn-clear-recent', function() {
        localStorage.removeItem(RECENT_KEY);
        renderRecent();
    });

    renderRecent();

    // --- Persistence Logic ---
    const STORAGE_KEY = 'file_export_filters';

    function saveFilterState() {
        const filters = {
            search: $('#custom-export-search').val(),
            customer: { id: $('#customer').val(), text: $('#customer').select2('data')[0]?.text },
            model: { id: $('#model').val(), text: $('#model').select2('data')[0]?.text },
            doc_type: { id: $('#document-type').val(), text: $('#document-type').select2('data')[0]?.text },
            category: { id: $('#category').val(), text: $('#category').select2('data')[0]?.text },
            project_status: { id: $('#project-status').val(), text: $('#project-status').select2('data')[0]?.text },
        };
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
    }

    function loadFilterState() {
        const saved = sessionStorage.getItem(STORAGE_KEY);
        if (!saved) return null;
        try {
            return JSON.parse(saved);
        } catch (e) {
            return null;
        }
    }

    function restoreSelect2($el, data) {
        if (data && data.id && data.id !== 'All') {
            const opt = new Option(data.text || data.id, data.id, true, true);
            $el.append(opt).trigger('change');
        }
    }

    // --- End Persistence Logic ---

    $inputSearch.on('keyup input', function () {
        const val = this.value;
        const $iconStatic = $('#search-icon-static');
        const $iconLoading = $('#search-icon-loading');
        
        $btnClear.toggleClass('hidden', val.length === 0);

        // Show Loading Icon
        $iconStatic.addClass('opacity-0');
        $iconLoading.removeClass('opacity-0').addClass('opacity-100');

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            syncUrlWithFilters();
            saveFilterState(); // Save on search
            if (val && val.length > 2) saveSearch(val);
            if (table) table.search(val).draw();
            
            // Revert to Static Icon
            setTimeout(() => {
                $iconLoading.removeClass('opacity-100').addClass('opacity-0');
                $iconStatic.removeClass('opacity-0');
            }, 300);
        }, 800); 
    });

    $btnClear.on('click', function () {
        $inputSearch.val('').focus();
        $btnClear.addClass('hidden');
        saveFilterState(); // Save on clear
        if (table) table.search('').draw();
    });

    // Trigger Save on any filter change
    $('#customer, #model, #document-type, #category, #project-status').on('change', function() {
        saveFilterState();
    });

    // Handle Reset with Storage Clear
    $('#btnResetFilters').on('click', function () {
      sessionStorage.removeItem(STORAGE_KEY); // Clear saved filters
      
      const $filters = $('#customer, #model, #document-type, #category, #project-status');
      $filters.off('change'); 

      resetSelect2ToAll($('#customer'));
      resetSelect2ToAll($('#model'));
      resetSelect2ToAll($('#document-type'));
      resetSelect2ToAll($('#category'));
      resetSelect2ToAll($('#project-status'));
      $('#custom-export-search').val('');
      
      syncUrlWithFilters();

      $filters.on('change', function () {
          saveFilterState();
          if (table) table.ajax.reload(null, true);
      });

      if (table) table.search('').draw();
    });

    // INITIAL LOAD: Restore filters from sessionStorage
    const savedFilters = loadFilterState();
    if (savedFilters) {
        if (savedFilters.search) {
            $inputSearch.val(savedFilters.search);
            $btnClear.toggleClass('hidden', savedFilters.search.length === 0);
        }
        restoreSelect2($('#customer'), savedFilters.customer);
        restoreSelect2($('#model'), savedFilters.model);
        restoreSelect2($('#document-type'), savedFilters.doc_type);
        restoreSelect2($('#category'), savedFilters.category);
        restoreSelect2($('#project-status'), savedFilters.project_status);
        
        if (table) {
            if (savedFilters.search) table.search(savedFilters.search);
            table.ajax.reload();
        }
    }
  }

  initTable();
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
                    '<div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500"><i class="fa-regular fa-calendar-xmark text-2xl mb-2"></i><p class="text-xs">No activity recorded yet.</p></div>'
                );
                return;
            }

            // Sort logs desc
            logs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            logs.forEach((l, index) => {
                const m = l.meta || {};
                const code = l.activity_code;
                const isLast = index === logs.length - 1;
                
                let icon = 'fa-circle-info';
                let colorClass = 'bg-gray-100 text-gray-600';
                let title = code;

                if (code === 'UPLOAD') {
                    icon = 'fa-cloud-arrow-up';
                    colorClass = 'bg-blue-100 text-blue-600';
                    title = 'Uploaded';
                } else if (code === 'SUBMIT_APPROVAL') {
                    icon = 'fa-paper-plane';
                    colorClass = 'bg-yellow-100 text-yellow-600';
                    title = 'Approval Requested';
                } else if (code === 'APPROVE') {
                    icon = 'fa-check';
                    colorClass = 'bg-green-100 text-green-600';
                    title = 'Approved';
                } else if (code === 'REJECT') {
                    icon = 'fa-xmark';
                    colorClass = 'bg-red-100 text-red-600';
                    title = 'Rejected';
                } else if (code === 'ROLLBACK') {
                    icon = 'fa-rotate-left';
                    colorClass = 'bg-amber-100 text-amber-600';
                    title = 'Rollback';
                } else if (code === 'DOWNLOAD') {
                    icon = 'fa-download';
                    colorClass = 'bg-gray-100 text-gray-600';
                    title = 'Downloaded';
                } else if (code.includes('SHARE')) {
                    icon = 'fa-share-nodes';
                    colorClass = 'bg-indigo-100 text-indigo-600';
                    title = 'Shared';
                } else if (code === 'REVISE_CONFIRM') {
                    icon = 'fa-pen-to-square';
                    colorClass = 'bg-purple-100 text-purple-600';
                    title = 'Revision Confirmed';
                }

                const timeStr = l.created_at ? formatDateTime(l.created_at) : '-';
                const userStr = l.user_name || 'System';

                // Content Logic
                let contentHtml = '';
                
                // Snapshot / Meta Details
                if (m.part_no || m.ecn_no) {
                     contentHtml += `
                        <div class="mt-2 p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded text-xs shadow-sm">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-800 dark:text-gray-200">${m.part_no || '-'}</span>
                                <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded font-mono text-[10px] border border-gray-200 dark:border-gray-600">
                                    Rev ${m.revision_no ?? '-'}
                                </span>
                                ${m.ecn_no ? `<span class="text-blue-600 dark:text-blue-400 font-mono text-[10px] bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800">${m.ecn_no}</span>` : ''}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] flex items-center gap-1">
                                <i class="fa-solid fa-tag text-[9px]"></i>
                                <span>${m.customer_code || m.customer || '-'}</span>
                                <span class="mx-0.5">•</span>
                                <span>${m.model_name || m.model || '-'}</span>
                                ${m.doctype_group ? `<span><span class="mx-0.5">•</span>${m.doctype_group}</span>` : ''}
                            </div>
                            ${code === 'ROLLBACK' && m.previous_status ? `
                                <div class="mt-1.5 pt-1.5 border-t border-gray-100 dark:border-gray-700 flex items-center text-amber-600 dark:text-amber-500 font-medium">
                                    <i class="fa-solid fa-code-branch mr-1.5 text-[10px]"></i>
                                    <span class="capitalize">${m.previous_status}</span>
                                    <i class="fa-solid fa-arrow-right-long mx-1.5 text-[10px]"></i>
                                    <span>Waiting</span>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }

                // Note
                if (m.note) {
                    contentHtml += `
                        <div class="mt-1.5 flex items-start gap-1.5">
                            <i class="fa-solid fa-quote-left text-gray-300 dark:text-gray-600 text-[10px] mt-0.5"></i>
                            <p class="text-xs text-gray-600 dark:text-gray-300 italic">${m.note}</p>
                        </div>
                    `;
                }
                
                // Specific for Share
                if (code.includes('SHARE')) {
                     let target = m.shared_to_dept || m.shared_with || m.shared_to || '-';
                     target = target.replace('[EXP] ', '');
                     
                     contentHtml += `
                        <div class="mt-1.5 text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-1">
                                <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                                <span>To: <strong>${target}</strong></span>
                            </div>
                            ${m.recipients ? `<div class="mt-0.5 ml-3.5 text-[10px] text-gray-500">Recipients: ${m.recipients}</div>` : ''}
                            ${m.expired_at ? `<div class="mt-0.5 ml-3.5 text-[10px] text-red-500">Exp: ${m.expired_at}</div>` : ''}
                        </div>
                     `;
                }
                
                // Specific for Download
                if (code === 'DOWNLOAD' && m.downloaded_file) {
                     contentHtml += `
                        <div class="mt-1.5 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            <i class="fa-solid fa-file text-[10px]"></i>
                            <span>${m.downloaded_file}</span>
                            ${m.file_size ? `<span class="text-gray-400">(${m.file_size})</span>` : ''}
                        </div>
                     `;
                }

                const el = $(`
                    <div class="relative flex gap-3">
                        ${!isLast ? '<div class="absolute top-4 left-3 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></div>' : ''}
                        
                        <div class="relative flex-shrink-0 mt-1">
                             <div class="w-6 h-6 rounded-full flex items-center justify-center ${colorClass} ring-4 ring-white dark:ring-gray-800 z-10 relative">
                                <i class="fa-solid ${icon} text-xs"></i>
                             </div>
                        </div>
                        
                        <div class="flex-1 min-w-0 mb-6 ${isLast ? 'mb-0' : ''}">
                            <div class="p-3 rounded-md bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-bold capitalize">${title}</span>
                                        <span class="text-xs text-gray-500 font-normal">by</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">${userStr}</span>
                                    </p>
                                    <span class="text-[10px] text-gray-400 whitespace-nowrap ml-2">${timeStr}</span>
                                </div>
                                ${contentHtml}
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
                          <div id="activity-log-content" class="max-h-96 overflow-y-auto pr-2 pl-1 pt-1">
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
        /* Ensure DataTable doesn't break mobile layout */
        .dataTables_wrapper {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

        .dataTables_scrollBody {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #exportTable {
            min-width: 1200px !important; /* Force a minimum width to trigger scroll */
            width: 100% !important;
        }

        @media (max-width: 768px) {
            #exportTable {
                min-width: 1000px !important;
            }
        }
    </style>
@endpush
