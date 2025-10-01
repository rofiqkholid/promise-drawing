@extends('layouts.app')
@section('title', 'Document Management')
@section('header-title', 'Stamp Format Master')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.css">
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">

  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-2xl font-bold sm:text-3xl">Stamp Format Master</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
    </div>
    <div class="mt-4 sm:mt-0">
      <button id="btn-add" type="button"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md font-semibold text-xs uppercase hover:bg-blue-500">
        <i class="fa-solid fa-plus"></i> Add New
      </button>
    </div>
  </div>

  {{-- Card --}}
  <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
    <div class="p-4 md:p-6">
      {{-- Controls --}}
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-2">
          <label class="text-sm">Show</label>
          <select id="dt-length" class="pl-3 pr-10 py-2 border rounded-md bg-white dark:bg-gray-700">
            <option>10</option><option>25</option><option>50</option><option>100</option>
          </select>
          <span class="text-sm">Entries</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <input id="dt-search" type="search" placeholder="Search..."
              class="block w-64 pl-10 pr-3 py-2 border rounded-md bg-white dark:bg-gray-700">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            </div>
          </div>
          <button id="btn-filter" type="button"
            class="px-4 py-2 border rounded-md bg-white dark:bg-gray-700 text-sm">Filter</button>
        </div>
      </div>

      {{-- Table --}}
      <div class="overflow-x-auto mt-6">
        <table id="sf-table" class="w-full text-sm text-left">
          <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 w-16">#</th>
              <th class="px-6 py-3">Prefix</th>
              <th class="px-6 py-3">Suffix</th>
              <th class="px-6 py-3">Is Active</th>
              <th class="px-6 py-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </div>
</div>

{{-- MODAL: Add --}}
<div id="modal-add" class="hidden fixed inset-0 z-50 bg-black/50 items-center justify-center">
  <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Add Stamp Format</h3>
      <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="form-add" action="{{ route('stampFormat.store') }}" method="POST">
      @csrf
      <label class="block text-sm mb-1">Prefix</label>
      <input name="prefix" class="w-full mb-2 rounded-lg border p-2.5">
      <p id="add-err-prefix" class="hidden text-xs text-red-500 mb-2"></p>

      <label class="block text-sm mb-1">Suffix</label>
      <input name="suffix" class="w-full mb-2 rounded-lg border p-2.5">
      <p id="add-err-suffix" class="hidden text-xs text-red-500 mb-2"></p>

      <label class="inline-flex items-center gap-2 mt-1">
        <input type="checkbox" name="is_active" value="1" class="rounded">
        <span>Is Active</span>
      </label>

      <div class="flex gap-3 mt-6">
        <button type="button" class="w-full modal-close border border-red-500 text-red-600 rounded-lg px-5 py-2.5">Close</button>
        <button type="submit" class="w-full bg-blue-600 text-white rounded-lg px-5 py-2.5">Save</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Edit --}}
<div id="modal-edit" class="hidden fixed inset-0 z-50 bg-black/50 items-center justify-center">
  <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Edit Stamp Format</h3>
      <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="form-edit" method="POST">
      @csrf @method('PUT')
      <label class="block text-sm mb-1">Prefix</label>
      <input id="edit-prefix" name="prefix" class="w-full mb-2 rounded-lg border p-2.5">
      <p id="edit-err-prefix" class="hidden text-xs text-red-500 mb-2"></p>

      <label class="block text-sm mb-1">Suffix</label>
      <input id="edit-suffix" name="suffix" class="w-full mb-2 rounded-lg border p-2.5">
      <p id="edit-err-suffix" class="hidden text-xs text-red-500 mb-2"></p>

      <label class="inline-flex items-center gap-2 mt-1">
        <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="rounded">
        <span>Is Active</span>
      </label>

      <div class="flex gap-3 mt-6">
        <button type="button" class="w-full modal-close border border-red-500 text-red-600 rounded-lg px-5 py-2.5">Close</button>
        <button type="submit" class="w-full bg-blue-600 text-white rounded-lg px-5 py-2.5">Save</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
  {{-- jQuery + DataTables --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.jquery.min.js"></script>

  <script>
  $(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');

    // Modal helpers
    const openModal  = (sel) => $(sel).removeClass('hidden').addClass('flex');
    const closeModal = (sel) => $(sel).addClass('hidden').removeClass('flex');
    $('.modal-close').on('click', () => { closeModal('#modal-add'); closeModal('#modal-edit'); });
    $('#btn-add').on('click', () => openModal('#modal-add'));

    // DataTable
    const table = $('#sf-table').DataTable({
      ajax: {
        url: "{{ route('stampFormat.index') }}",
        dataSrc: 'data',
        headers: { 'Accept': 'application/json' },
        data: function (d) { d._ts = Date.now(); } // anti-cache
      },
      columns: [
        { data: null, className: 'px-6 py-3', render: (_d,_t,_r,m) => m.row + 1 },
        { data: 'prefix', className: 'px-6 py-3 font-medium text-gray-900 dark:text-white' },
        { data: 'suffix', className: 'px-6 py-3' },
        { data: 'is_active', className: 'px-6 py-3',
          render: function(v){
            const on = (+v === 1);
            return on
              ? '<span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>'
              : '<span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Inactive</span>';
          }
        },
        { data: 'id', className: 'px-6 py-3 text-center text-nowrap',
          render: id => `
            <button class="btn-edit text-yellow-600 hover:underline mx-1" title="Edit" data-id="${id}">
              <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn-del text-red-600 hover:underline mx-1" title="Delete" data-id="${id}">
              <i class="fa-solid fa-trash-can"></i>
            </button>`
        }
      ],
      ordering: true,
      searching: true,
      paging: true,
      lengthChange: false
    });

    // Controls
    $('#dt-search').on('input', function(){ table.search(this.value).draw(); });
    $('#dt-length').on('change', function(){ table.page.len(+this.value).draw(); });

    const reload = () => table.ajax.reload(null, false);

    // ADD
    $('#form-add').on('submit', function(e){
      e.preventDefault();
      $('#add-err-prefix,#add-err-suffix').addClass('hidden');

      const fd = new FormData(this);
      if (!fd.has('is_active')) fd.append('is_active', '0');

      $.ajax({
        url: this.action,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        success: function(){
          closeModal('#modal-add'); $('#form-add')[0].reset();
          reload();
        },
        error: function(xhr){
          const b = xhr.responseJSON || {};
          if (b.errors?.prefix) { $('#add-err-prefix').text(b.errors.prefix[0]).removeClass('hidden'); }
          if (b.errors?.suffix) { $('#add-err-suffix').text(b.errors.suffix[0]).removeClass('hidden'); }
        }
      });
    });

    // EDIT open
    $('#sf-table').on('click', '.btn-edit', function(){
      const id = $(this).data('id');
      $.ajax({
        url: `{{ url('master/stampFormat') }}/${id}`,
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        success: function(data){
          $('#edit-prefix').val(data.prefix ?? '');
          $('#edit-suffix').val(data.suffix ?? '');
          $('#edit-is-active').prop('checked', (+data.is_active === 1));
          $('#form-edit').attr('action', `{{ url('master/stampFormat') }}/${id}`);
          openModal('#modal-edit');
        }
      });
    });

    // DELETE
    $('#sf-table').on('click', '.btn-del', function(){
      const id = $(this).data('id');
      if (!confirm('Delete this item?')) return;
      $.ajax({
        url: `{{ url('master/stampFormat') }}/${id}`,
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        success: function(){ reload(); },
        error: function(){ alert('Delete failed'); }
      });
    });

    // EDIT submit
    $('#form-edit').on('submit', function(e){
      e.preventDefault();
      $('#edit-err-prefix,#edit-err-suffix').addClass('hidden');

      const fd = new FormData(this);
      if (!fd.has('is_active')) fd.append('is_active', '0'); // unchecked -> 0

      $.ajax({
        url: this.action,
        method: 'POST', // _method=PUT di form
        data: fd,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        success: function(){
          closeModal('#modal-edit');
          reload();
        },
        error: function(xhr){
          const b = xhr.responseJSON || {};
          if (b.errors?.prefix) { $('#edit-err-prefix').text(b.errors.prefix[0]).removeClass('hidden'); }
          if (b.errors?.suffix) { $('#edit-err-suffix').text(b.errors.suffix[0]).removeClass('hidden'); }
        }
      });
    });
  });
  </script>
@endpush
