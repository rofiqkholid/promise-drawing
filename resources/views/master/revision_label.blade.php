@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Customer Revision Label')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Customer Revision Label</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola label revisi per customer.</p>
    </div>
    <div class="mt-4 sm:mt-0">
      <button type="button" id="add-button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
        <i class="fa-solid fa-plus"></i>
        Add New
      </button>
    </div>
  </div>

  {{-- Card: Tabel saja (pencarian bawaan DataTables) --}}
  <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
    <div class="p-4 md:p-6">
      <div class="overflow-x-hidden">
        <table id="revLabelTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th class="px-6 py-3 w-16">No</th>
              <th class="px-6 py-3">Customer Code</th>
              <th class="px-6 py-3">Label</th>
              <th class="px-6 py-3">Sort</th>
              <th class="px-6 py-3">Status</th>
              <th class="px-6 py-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Create/Edit --}}
<div id="revModal" tabindex="-1" aria-hidden="true"
  class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
  <div class="relative p-4 w-full max-w-md h-full md:h-auto">
    <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
      <button type="button"
        class="close-modal text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
        <i class="fa-solid fa-xmark w-5 h-5"></i>
        <span class="sr-only">Close modal</span>
      </button>
      <h3 id="modalTitle" class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add Revision Label</h3>

      <form id="revForm">
        @csrf
        <input type="hidden" id="row_id">

        <div class="mb-4 text-left">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer (Code) <span class="text-red-500">*</span></label>
          <select id="customer_id" class="w-full"></select>
          <p id="err-customer_id" class="text-red-500 text-xs mt-1 hidden"></p>
        </div>

        <div class="mb-4 text-left">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Label <span class="text-red-500">*</span></label>
          <input type="text" id="label" maxlength="30"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
            required>
          <p id="err-label" class="text-red-500 text-xs mt-1 hidden"></p>
        </div>

        <div class="mb-4 text-left">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sort</label>
          <input type="number" id="sort_order"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
          <p id="err-sort_order" class="text-red-500 text-xs mt-1 hidden"></p>
        </div>

        <div class="flex items-center justify-start mb-4">
          <input id="is_active" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
          <label for="is_active" class="ml-2 text-sm text-gray-900 dark:text-gray-300">Active</label>
        </div>

        <div class="flex items-center space-x-4 mt-6">
          <button type="button" class="close-modal text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
          <button type="submit" id="btnSave" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteRevLabelModal" tabindex="-1" aria-hidden="true"
  class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
  <div class="relative p-4 w-full max-w-md h-full md:h-auto">
    <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
      <button type="button"
        class="close-confirm text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
        <i class="fa-solid fa-xmark w-5 h-5"></i>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
        <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
      </div>
      <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this revision label?</p>
      <div class="flex justify-center items-center space-x-4">
        <button type="button" class="close-confirm py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600">
          No, cancel
        </button>
        <button type="button" id="confirmDeleteBtn" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
          Yes, I'm sure
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function detectTheme() {
      const isDark = document.documentElement.classList.contains('dark');
      return isDark ? {
        mode: 'dark',
        bg: 'rgba(30, 41, 59, 0.95)',
        fg: '#E5E7EB',
        border: 'rgba(71, 85, 105, 0.5)',
        progress: 'rgba(255,255,255,.9)',
        icon: { success:'#22c55e', error:'#ef4444', warning:'#f59e0b', info:'#3b82f6' }
      } : {
        mode: 'light',
        bg: 'rgba(255, 255, 255, 0.98)',
        fg: '#0f172a',
        border: 'rgba(226, 232, 240, 1)',
        progress: 'rgba(15,23,42,.8)',
        icon: { success:'#16a34a', error:'#dc2626', warning:'#d97706', info:'#2563eb' }
      };
    }

    // Toasts (SweetAlert2)
    const BaseToast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2600,
      timerProgressBar: true,
      showClass: { popup: 'swal2-animate-toast-in' },
      hideClass: { popup: 'swal2-animate-toast-out' },
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });

    function renderToast({icon='success', title='Success', text=''}={}) {
      const t = detectTheme();
      BaseToast.fire({
        icon, title, text,
        iconColor: t.icon[icon] || t.icon.success,
        background: t.bg, color: t.fg,
        customClass: { popup:'swal2-toast border', title:'', timerProgressBar:'' },
        didOpen: (toast) => {
          const bar = toast.querySelector('.swal2-timer-progress-bar');
          if (bar) bar.style.background = t.progress;
          const popup = toast.querySelector('.swal2-popup');
          if (popup) popup.style.borderColor = t.border;
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });
    }

    function toastSuccess(title='Berhasil', text='Operasi berhasil dijalankan.') { renderToast({icon:'success', title, text}); }
    function toastError(title='Gagal', text='Terjadi kesalahan.') {
      BaseToast.update({ timer: 3400 });
      renderToast({icon:'error', title, text});
      BaseToast.update({ timer: 2600 });
    }
    function toastWarning(title='Peringatan', text='Periksa kembali data Anda.') { renderToast({icon:'warning', title, text}); }
    function toastInfo(title='Informasi', text='') { renderToast({icon:'info', title, text}); }

    window.toastSuccess = toastSuccess;
    window.toastError   = toastError;
    window.toastWarning = toastWarning;
    window.toastInfo    = toastInfo;

    /* ========= Busy button helpers ========= */
    const spinnerSVG = `
      <svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>`;

    function beginBusy($btn, text='') {
      if ($btn.data('busy')) return false;
      $btn.data('busy', true);
      if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
      $btn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed');
      const isActionBtn = $btn.is('.edit-btn, .del-btn, #confirmDeleteBtn');
      if (isActionBtn) {
        $btn.html(spinnerSVG);
      } else {
        const label = text || '';
        $btn.html(label ? `<span class="inline-flex items-center gap-1">${spinnerSVG}<span>${label}</span></span>` : spinnerSVG);
      }
      return true;
    }
    function endBusy($btn) {
      const orig = $btn.data('orig-html');
      if (orig) $btn.html(orig);
      $btn.prop('disabled', false).removeClass('opacity-75 cursor-not-allowed');
      $btn.data('busy', false);
    }
    // Anti double click global
    $(document).on('click', 'button', function(e) {
      const $b = $(this);
      if ($b.data('busy')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }
    });

    // set busy pada scope (untuk delete modal)
    function setFormBusy($scope, busy) {
      $scope.find('button, input, select, textarea').prop('disabled', busy);
    }

    /* ========= Select2 (untuk MODAL saja) ========= */
    const $revModal = $('#revModal');
    const $customerSelect = $('#customer_id').select2({
      placeholder: 'Choose customer',
      allowClear: true,
      width: '100%',
      dropdownParent: $revModal,
      ajax: {
        url: `{{ route('rev-label.dropdowns') }}`,
        dataType: 'json',
        delay: 250,
        data: params => ({ term: params.term || '', page: params.page || 1 }),
        processResults: data => ({ results: data.results || [], pagination: { more: data?.pagination?.more || false } })
      }
    });

    /* ========= DataTables (samakan dengan pkgFormat) ========= */
    const dt = $('#revLabelTable').DataTable({
      processing: true,
      serverSide: true,
      paging: true,
      searching: true,          // boleh eksplisit, defaultnya juga true
      order: [[3, 'asc']],
      ajax: {
        url: `{{ route('rev-label.data') }}`,
        type: 'GET',
        data: function (d) {
          // samakan pola pkgFormat: flatten search ke string
          d.search = d.search?.value ?? '';
        }
      },
      columns: [
        { data: null, orderable: false, searchable: false,
          render: (d,t,r,m)=> m.row + m.settings._iDisplayStart + 1 },
        { data: 'customer_code' },
        { data: 'label' },
        { data: 'sort_order' },
        { data: 'is_active',
          render: val => val
            ? '<span class="inline-block px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>'
            : '<span class="inline-block px-3 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Inactive</span>'
        },
        { data: 'id', orderable: false, searchable: false, className: 'text-center',
          render: id => `
            <button class="edit-btn text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${id}">
              <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
            </button>
            <button class="del-btn text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${id}">
              <i class="fa-solid fa-trash-can fa-lg m-2"></i>
            </button>`
        }
      ],
      pageLength: 10,
      language: { emptyTable: '<div class="text-gray-500 dark:text-gray-400">No data available.</div>' }
      // tidak set 'dom', mengikuti default (seperti pkgFormat)
    });

    /* ========= Modal helpers ========= */
    const showModal = () => $revModal.removeClass('hidden').addClass('flex');
    const hideModal = () => $revModal.addClass('hidden').removeClass('flex');
    const hideErrors = () => $('[id^=err-]').addClass('hidden').text('');
    const resetForm = () => {
      $('#row_id').val('');
      $('#modalTitle').text('Add Revision Label');
      $customerSelect.val(null).trigger('change');
      $('#label').val('');
      $('#sort_order').val('');
      $('#is_active').prop('checked', true);
      hideErrors();
    };

    // Open modal (Add)
    $('#add-button').on('click', function() {
      const $btn = $(this);
      if (!beginBusy($btn, 'Opening...')) return;
      resetForm();
      showModal();
      setTimeout(() => endBusy($btn), 150);
    });

    // Close modal buttons
    $(document).on('click', '.close-modal', function() {
      const $btn = $(this);
      if (!beginBusy($btn, 'Closing...')) return;
      hideModal();
      setTimeout(() => endBusy($btn), 150);
    });

    /* ========= Edit ========= */
    $('#revLabelTable').on('click', '.edit-btn', function() {
      const $btn = $(this);
      if (!beginBusy($btn, 'Loading...')) return;
      const id = $btn.data('id');

      $.get(`{{ route('rev-label.show', ':id') }}`.replace(':id', id), (row) => {
        $('#modalTitle').text('Edit Revision Label');
        $('#row_id').val(row.id);
        $('#label').val(row.label || '');
        $('#sort_order').val(row.sort_order || '');
        $('#is_active').prop('checked', !!row.is_active);

        const opt = new Option(row.customer_code || row.customer_id, row.customer_id, true, true);
        $customerSelect.append(opt).trigger('change');

        showModal();
      })
      .fail(() => toastError('error', 'Failed to load data'))
      .always(() => endBusy($btn));
    });

    /* ========= Delete (custom modal) ========= */
    const $confirmModal = $('#deleteRevLabelModal');
    let deleteId = null;

    function showConfirm() { $confirmModal.removeClass('hidden').addClass('flex'); }
    function hideConfirm() { $confirmModal.addClass('hidden').removeClass('flex'); }

    // open confirm modal
    $('#revLabelTable').on('click', '.del-btn', function () {
      deleteId = $(this).data('id') || null;
      showConfirm();
    });

    // close confirm modal
    $(document).on('click', '.close-confirm', function () {
      hideConfirm();
      deleteId = null;
    });

    // confirm delete
    $('#confirmDeleteBtn').on('click', function () {
      if (!deleteId) return;
      const $btn = $(this);

      $.ajax({
        url: `{{ route('rev-label.destroy', ':id') }}`.replace(':id', deleteId),
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        data: { _method: 'DELETE' },
        beforeSend: function () {
          if (!beginBusy($btn, 'Deleting...')) return false;
          setFormBusy($confirmModal, true);
        },
        success: function () {
          dt.ajax.reload(null, false);
          hideConfirm();
          deleteId = null;
          toastSuccess('Success', 'Revision label deleted successfully.');
        },
        error: function (xhr) {
          const msg = xhr.status === 419 ? 'CSRF token invalid/missing.' : (xhr.responseJSON?.message || 'Failed to delete');
          toastError('Error', msg);
        },
        complete: function () {
          endBusy($btn);
          setFormBusy($confirmModal, false);
        }
      });
    });

    /* ========= Submit (Create/Update) ========= */
    $('#revForm').on('submit', function(e) {
      e.preventDefault();
      hideErrors();

      const $btn = $('#btnSave');
      if (!beginBusy($btn, 'Saving...')) return;

      const id = $('#row_id').val();
      const method = id ? 'PUT' : 'POST';
      const url = id
        ? `{{ route('rev-label.update', ':id') }}`.replace(':id', id)
        : `{{ route('rev-label.store') }}`;

      const payload = {
        customer_id: $('#customer_id').val(),
        label: $('#label').val(),
        sort_order: $('#sort_order').val() || null,
        is_active: $('#is_active').is(':checked') ? 1 : 0,
      };

      $.ajax({
        url,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        data: { ...payload, _method: method }
      })
      .done(() => {
        hideModal();
        dt.ajax.reload(null, false);
        toastSuccess('success', id ? 'Revision label updated successfully' : 'Revision label added successfully');
      })
      .fail(xhr => {
        const res = xhr.responseJSON || {};
        if (res.errors) {
          Object.entries(res.errors).forEach(([f, msgs]) => {
            $(`#err-${f}`).removeClass('hidden').text(Array.isArray(msgs) ? msgs[0] : msgs);
          });
          toastError('error', 'Validation error');
        } else {
          toastError('error', 'Failed to save');
        }
      })
      .always(() => endBusy($btn));
    });
  });
</script>
@endpush
