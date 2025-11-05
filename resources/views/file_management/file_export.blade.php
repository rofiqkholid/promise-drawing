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
                let viewButton = `<a href="${detailUrl}" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-300 dark:text-blue-400 transition-colors" title="Details"><i class="fa-solid fa-eye fa-lg"></i></a>`;
                let downloadButton = `<a href="/file-manager.export/download-package/${row.id}" class="ml-4 text-green-600 hover:text-green-900 dark:hover:text-green-300 dark:text-green-400 transition-colors" title="Download Package"><i class="fa-solid fa-download fa-lg"></i></a>`;
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

    @include('partials._package_details')
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
