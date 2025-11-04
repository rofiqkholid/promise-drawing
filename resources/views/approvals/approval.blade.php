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
      <i class="fa-solid fa-file-excel"></i>
      Download Summary
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
      @foreach(['Customer', 'Model', 'Document Type', 'Category', 'Status'] as $label)
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
    <div class="overflow-x-hidden">
      <table id="approvalTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Package Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Decision Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
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
  $(function() {
    let table;
    const ENDPOINT = '{{ route("approvals.filters") }}';

    // --- helper: reset Select2 ke "All" (pasti sukses untuk AJAX mode) ---
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
            // Pastikan "All" selalu ada di hasil (paling atas)
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

    // Dependent behavior -> set anak ke "All" (bukan null)
    $('#customer').on('change', function() {
      resetSelect2ToAll($('#model'));
    });
    $('#document-type').on('change', function() {
      resetSelect2ToAll($('#category'));
    });

    function getCurrentFilters() {
      const valOrAll = v => (v && v.length ? v : 'All');
      return {
        customer: valOrAll($('#customer').val()),
        model: valOrAll($('#model').val()),
        doc_type: valOrAll($('#document-type').val()),
        category: valOrAll($('#category').val()),
        status: valOrAll($('#status').val()),
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

    // formatter tanggal (tetap)
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
          }
        },

        // default: Request Date terbaru di atas (kolom index 2)
        order: [
          [2, 'desc']
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
                row.doc_type,
                row.category,
                row.part_no,
                revTxt
              ].filter(Boolean);

              return `<div class="text-sm">${parts.join(' - ')}</div>`;
            }
          },


          // Request Date (was Upload Date)
          {
            data: 'request_date',
            name: 'dpr.requested_at',
            render: function(v) {
              const text = fmtDate(v);
              return `<span title="${v || ''}">${text}</span>`;
            }
          },

          // Decision Date (new)
          {
            data: 'decision_date',
            name: 'dpr.decided_at',
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
        ],

        columnDefs: [{
            targets: 0,
            className: 'text-center w-12',
            width: '48px'
          },
          {
            targets: 2,
            className: 'whitespace-nowrap'
          },
          {
            targets: 3,
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
      $('#customer, #model, #document-type, #category, #status').on('change', function() {
        if (table) table.ajax.reload(null, true);
        loadKPI();
      });

      // tombol reset -> set semua ke "All", reload table & KPI
      $('#btnResetFilters').on('click', function() {
        resetSelect2ToAll($('#customer'));
        resetSelect2ToAll($('#model'));
        resetSelect2ToAll($('#document-type'));
        resetSelect2ToAll($('#category'));
        resetSelect2ToAll($('#status'));

        if (table) table.ajax.reload(null, true);
        loadKPI();
      });

       $('#btnDownloadSummary').on('click', function() {
    const f = getCurrentFilters();
    const query = $.param(f);
    window.location.href = '{{ route("approvals.summary") }}?' + query;
  });

      // klik row -> detail
      $('#approvalTable tbody').on('click', 'tr', function() {
        const row = table.row(this).data();
        if (row && row.id) window.location.href = `/approval/${row.id}`;
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