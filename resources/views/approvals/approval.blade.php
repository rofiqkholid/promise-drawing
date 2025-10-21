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
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Document</p>
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
          {{-- opsional tampilkan % WIP --}}
          {{-- <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">WIP Rate: <span id="kpiWipRate">0%</span></p> --}}
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
          {{-- opsional tampilkan % approval --}}
          {{-- <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Approval Rate: <span id="kpiApprovalRate">0%</span></p> --}}
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
          {{-- opsional tampilkan % rejection --}}
          {{-- <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Rejection Rate: <span id="kpiRejectRate">0%</span></p> --}}
        </div>
      </div>
    </div>
  </div>

  {{-- Filter section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5">
      @foreach(['Customer', 'Model', 'Document Type', 'Category', 'Status'] as $label)
      <div>
        <label for="{{ Str::slug($label) }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
        <div class="relative mt-1">
          <select id="{{ Str::slug($label) }}"
            class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <option value="All">All</option>
          </select>
          
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Tabel section --}}
  <div class="mt-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
      <table id="approvalTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Doc Type</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Part No</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revision</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
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

  function initSelect2() {
    $('.js-filter').each(function() {
      const $s = $(this);
      $s.select2({
        width: '100%',
        placeholder: 'All',
      });
    });
  }

  function fill(sel, items, useNameAsValue = false) {
  const $s = $(sel);
  const valBefore = $s.val();
  $s.empty().append('<option value="All">All</option>');
  (items || []).forEach(it => {
    const value = useNameAsValue ? (it.name ?? it.code ?? it.id) : (it.id ?? it.code ?? it.name);
    const label = it.label 
      ?? (it.code && it.name ? `${it.code} — ${it.name}` : (it.name ?? it.code ?? value));
    $s.append(new Option(label, value));
  });
  $s.val(
  valBefore && $s.find(`option[value="${valBefore}"]`).length ? valBefore : 'All'
).trigger('change.select2').trigger('change');
}

  $.getJSON('{{ route("api.approvals.filters") }}', function (res) {

    fill('#customer',  normalize(res.customers),  true);
    fill('#model',     normalize(res.models),     true);
    fill('#document-type',  normalize(res.doc_types),  true);
    fill('#category',  normalize(res.categories), true);
    fill('#status',    normalize(res.statuses),   true);

    initSelect2();

    initTable();
    loadKPI();
    bindHandlers();
  });

  function normalize(list) {
    return (list || []).map(it => {
      if (!('name' in it) && 'code' in it) return { id: it.id, name: it.code };
      return it;
    });
  }

 function reloadModelsByCustomer() {
  const custCode = $('#customer').val();
  const params = custCode && custCode !== 'All' ? { customer_code: custCode } : {};
  $.getJSON('{{ route("api.approvals.filters") }}', params, function (res) {
    fill('#model', normalize(res.models), true); // value = name
    if (!res.models || res.models.length === 0) {
      $('#model').val('All').trigger('change.select2').trigger('change');
    }
  });
}

function reloadCategoriesByDocType() {
  const docType = $('#document-type').val(); // value = name (karena useNameAsValue=true)
  const params = (docType && docType !== 'All') ? { doc_type: docType } : {};
  $.getJSON('{{ route("api.approvals.filters") }}', params, function (res) {
    fill('#category', normalize(res.categories), true); // value = name
    if (!res.categories || res.categories.length === 0) {
      $('#category').val('All').trigger('change.select2').trigger('change');
    }
  });
}

  function getCurrentFilters() {
    return {
      customer:  $('#customer').val() || 'All',
      model:     $('#model').val() || 'All',
      doc_type:  $('#document-type').val() || 'All',
      category:  $('#category').val() || 'All',
      status:    $('#status').val() || 'All',
    };
  }

  function loadKPI() {
    const params = getCurrentFilters();
    $('#cardTotal, #cardWaiting, #cardApproved, #cardRejected').text('…');

    $.ajax({
      url: '{{ route("api.approvals.kpi") }}',
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
    table = $('#approvalTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route("api.approvals.list") }}',
        type: 'GET',
        data: function (d) {
          const f = getCurrentFilters();
          d.customer  = f.customer;
          d.model     = f.model;
          d.doc_type  = f.doc_type;
          d.category  = f.category;
          d.status    = f.status;
        }
      },
      order: [[ 1, 'asc' ]],
      columns: [
        { data: null, orderable: false, searchable: false },
        { data: 'customer', name: 'c.code' },
        { data: 'model', name: 'm.name' },
        { data: 'doc_type', name: 'dtg.name' },
        { data: 'category', name: 'category' },
        { data: 'part_no', name: 'p.part_no' },
        { data: 'revision', name: 'dpr.revision_no' },
        {
          data: 'status',
          name: 'dpr.revision_status',
          render: function (data) {
            let cls = '';
            if (data === 'Reject')   cls = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
            if (data === 'Waiting')  cls = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
            if (data === 'Complete') cls = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
            return `<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${cls}">${data ?? ''}</span>`;
          }
        },
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
  }

  function bindHandlers() {
    $('#customer').on('change', function() {
      reloadModelsByCustomer();
    });
   $('#document-type').on('change', function () {
  reloadCategoriesByDocType();
});
    $('#customer, #model, #document-type, #category, #status').on('change', function () {
      table.ajax.reload();
      loadKPI();
    });

    $('#approvalTable tbody').on('click', 'tr', function () {
      const row = table.row(this).data();
      if (row && row.id) window.location.href = `/approval/${row.id}`;
    }).on('mouseenter', 'tr', function () {
      $(this).css('cursor', 'pointer');
    });
    
  }
});
</script>
@endpush
