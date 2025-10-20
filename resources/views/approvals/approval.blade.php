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
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">512</p>
                </div>
            </div>
            {{-- Card Waiting --}}
            <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-yellow-500 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/50 rounded-full">
                    <i class="fa-solid fa-clock fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Waiting</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">300</p>
                </div>
            </div>
            {{-- Card Approved --}}
            <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-green-500 dark:text-green-400 bg-green-100 dark:bg-green-900/50 rounded-full">
                    <i class="fa-solid fa-circle-check fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">198</p>
                </div>
            </div>
            {{-- Card Rejected --}}
            <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-red-500 dark:text-red-400 bg-red-100 dark:bg-red-900/50 rounded-full">
                    <i class="fa-solid fa-circle-xmark fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">14</p>
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
                    <select id="{{ Str::slug($label) }}" class="appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option>All</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700 dark:text-gray-400">
                        <i class="fa-solid fa-chevron-down fa-xs"></i>
                    </div>
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

  function fill(sel, items, useNameAsValue = false) {
    const $s = $(sel);
    $s.empty().append('<option value="All">All</option>');
    (items || []).forEach(it => $s.append(new Option(it.name, useNameAsValue ? it.name : it.id)));
  }

  $.getJSON('{{ route("api.approvals.filters") }}', function (res) {
    fill('#customer',  res.customers,  true);
    fill('#model',     res.models,     true);
    fill('#document-type',  res.doc_types,  true);
    fill('#category',  res.categories, true);
    fill('#status',    res.statuses,   true);

    initTable();
    bindHandlers();
  });

  function initTable() {
    table = $('#approvalTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route("api.approvals.list") }}',
        type: 'GET',
        data: function (d) {
          d.customer  = $('#customer').val();
          d.model     = $('#model').val();
          d.doc_type  = $('#doc-type').val();
          d.category  = $('#category').val();
          d.status    = $('#status').val();
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
    $('#customer, #model, #document-type, #category, #status').on('change', function () {
      table.ajax.reload();
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
