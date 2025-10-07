@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'User Role Management')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">User–Role Maintenance</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola relasi user dengan role.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add Mapping
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6">
            <table id="userRolesTable" class="min-w-full w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 w-16">No</th>
                        <th class="px-6 py-3 sorting" data-column="user">User</th>
                        <th class="px-6 py-3 sorting" data-column="role">Role</th>
                        <th class="px-6 py-3 text-start">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div id="addURModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add User Role</h3>
            <form id="addURForm" action="{{ route('user-role.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">User</label>
                    <select name="user_id" id="add_user_id" class="w-full" required>
                        <option value="">— Select User —</option>
                    </select>
                    <p id="add-user-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-3">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Role</label>
                    <select name="role_id" id="add_role_id" class="w-full" required>
                        <option value="">— Select Role —</option>
                    </select>
                    <p id="add-role-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editURModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit User–Role</h3>
            <form id="editURForm" method="POST" action="{{ route('user-role.pairUpdate') }}">
                @csrf
                @method('PUT')
                {{-- hidden untuk menjaga pair lama --}}
                <input type="hidden" name="original_user_id" id="original_user_id">
                <input type="hidden" name="original_role_id" id="original_role_id">

                <div class="mb-3">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">User</label>
                    <select name="user_id" id="edit_user_id" class="w-full" required>
                        <option value="">— Select User —</option>
                    </select>
                    <p id="edit-user-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-3">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Role</label>
                    <select name="role_id" id="edit_role_id" class="w-full" required>
                        <option value="">— Select Role —</option>
                    </select>
                    <p id="edit-role-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div id="deleteURModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Hapus mapping user role ini?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">Yes, delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    div.dataTables_length label { font-size: 0.75rem; }
    div.dataTables_length select { font-size: 0.75rem; line-height: 1rem; padding: 0.25rem 1.25rem 0.25rem 0.5rem; height: 1.875rem; width: 4.5rem; }
    div.dataTables_filter label { font-size: 0.75rem; }
    div.dataTables_filter input[type="search"], input[type="search"][aria-controls="userRolesTable"] { font-size: 0.75rem; line-height: 1rem; padding: 0.25rem 0.5rem; height: 1.875rem; width: 12rem; }
    div.dataTables_info { font-size: 0.75rem; padding-top: 0.8em; }

    /* Select2 – samakan tinggi & style dengan input */
    .select2-container .select2-selection--single{
        height: 40px; border:1px solid #d1d5db; border-radius: .5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
        line-height: 38px; padding-left: 12px; color:#111827;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height: 38px; right: 8px;
    }
    /* Dark mode basic */
    .dark .select2-container--default .select2-selection--single{
        background-color:#374151; border-color:#4b5563; color:#f9fafb;
    }

    /* === SweetAlert2: tweak kontras saat dark mode (opsional, karena kita juga set inline bg/color) === */
    .dark .swal2-popup.swal2-toast {
        box-shadow: 0 10px 15px -3px rgba(0,0,0,.6), 0 4px 6px -4px rgba(0,0,0,.6);
    }
    .dark .swal2-title, .dark .swal2-html-container { color: #f9fafb !important; }
</style>
@endpush


@push('scripts')
{{-- Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    /* ===== Theme-aware SweetAlert2 Toast ===== */
    const isDark = () => document.documentElement.classList.contains('dark');
    function themeToast(icon, title){
        const dark = isDark();
        const bg   = dark ? '#1f2937' : '#ffffff'; // slate-800 vs white
        const fg   = dark ? '#f9fafb' : '#111827'; // gray-50 vs gray-900
        Swal.fire({
            toast: true, icon, title,
            position: 'top-end', showConfirmButton: false,
            timer: 2200, timerProgressBar: true,
            background: bg, color: fg,
            didOpen: el => {
                const bar = el.querySelector('.swal2-timer-progress-bar');
                if (bar) bar.style.background = dark ? '#10b981' : '#3b82f6';
            }
        });
    }

    /* ===== Global Spinner/Busy Helpers ===== */
    const spinnerSVG = `
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>`;

    function beginBusy($btn, text='Processing...'){
        if ($btn.data('busy')) return false; // already busy
        $btn.data('busy', true);
        if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
        $btn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed');
        $btn.html(`<span class="inline-flex items-center">${spinnerSVG}${text}</span>`);
        return true;
    }
    function endBusy($btn){
        const orig = $btn.data('orig-html');
        if (orig) $btn.html(orig);
        $btn.prop('disabled', false).removeClass('opacity-75 cursor-not-allowed');
        $btn.data('busy', false);
    }

    // Hard-guard: cegah klik semua <button> ketika busy
    $(document).on('click', 'button', function(e){
        const $b = $(this);
        if ($b.data('busy')) { e.preventDefault(); e.stopImmediatePropagation(); return false; }
    });

    /* ===== Modals & helpers ===== */
    const addModal    = $('#addURModal');
    const editModal   = $('#editURModal');
    const deleteModal = $('#deleteURModal');
    const addButton   = $('#add-button');
    const closeButtons= $('.close-modal-button');
    let rowKeyToDelete = null;
    let optionsCache   = null;

    const showModal = m => m.removeClass('hidden').addClass('flex');
    const hideModal = m => m.addClass('hidden').removeClass('flex');

    /* ===== Select2 init ===== */
    function initAddSelect2(){
        $('#add_user_id, #add_role_id').select2({
            width: '100%', dropdownParent: addModal, placeholder: '— Select —', allowClear: true
        });
    }
    function initEditSelect2(){
        $('#edit_user_id, #edit_role_id').select2({
            width: '100%', dropdownParent: editModal, placeholder: '— Select —', allowClear: true
        });
    }
    initAddSelect2(); initEditSelect2();

    /* ===== Dropdown options (users & roles) ===== */
    async function ensureOptionsLoaded(force=false){
        if (optionsCache && !force) return;
        try{
            const res = await $.ajax({
                url: "{{ route('user-role.dropdowns') }}",
                method: "GET", dataType: "json", headers: { 'Accept': 'application/json' }
            });
            optionsCache = res || {users:[], roles:[]};
        }catch{ optionsCache = {users:[], roles:[]}; }
    }
    function fillSelect($el, items){
        const current = $el.val();
        $el.empty().append(`<option value="">— Select —</option>`);
        (items || []).forEach(i => $el.append(`<option value="${i.id}">${i.name}</option>`));
        if (current) $el.val(current);
        $el.trigger('change.select2');
    }

    /* ===== Add Mapping (open) — spinner di tombol Add ===== */
    addButton.on('click', async function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Loading...')) return;
        await ensureOptionsLoaded(true);
        fillSelect($('#add_user_id'), optionsCache.users);
        fillSelect($('#add_role_id'), optionsCache.roles);
        showModal(addModal);
        endBusy($btn);
    });

    /* ===== Close modals — kasih spinner kecil biar konsisten (cepat) ===== */
    closeButtons.on('click', function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Closing...')) return;
        hideModal(addModal); hideModal(editModal); hideModal(deleteModal);
        setTimeout(()=>endBusy($btn), 150); // visual feedback singkat
    });

    /* ===== DataTable ===== */
    const table = $('#userRolesTable').DataTable({
        processing: true, serverSide: true, scrollX: true,
        ajax: { url: '{{ route("user-role.data") }}', type: 'GET', data: d => { d.search = d.search.value; } },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1 },
            { data: null, name: 'user', render: row => (row.user?.name || row.user_name || `ID: ${row.user_id}`) },
            { data: null, name: 'role', render: row => (row.role?.name || row.role_name || `ID: ${row.role_id}`) },
            { data: null, orderable:false, searchable:false,
              render: row => `
                <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit"
                    data-user="${row.user_id}" data-role="${row.role_id}">
                    <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
                </button>
                <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete"
                    data-user="${row.user_id}" data-role="${row.role_id}">
                    <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                </button>` }
        ],
        pageLength: 10, order: [[1,'asc']],
        language: { emptyTable: '<div class="text-gray-500 dark:text-gray-400">No user–role mapping found.</div>' },
        responsive: true, autoWidth: false,
    });

    /* ===== Create (POST) — spinner di tombol Save ===== */
    $('#addURForm').on('submit', function(e){
        e.preventDefault();
        const $btn = $(this).find('button[type=submit]');
        if (!beginBusy($btn, 'Saving...')) return;

        const formData = new FormData(this);
        $('#add-user-error, #add-role-error').addClass('hidden');

        $.ajax({
            url: $(this).attr('action'), method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData, processData: false, contentType: false,
            success: res => {
                if (res.success){
                    table.ajax.reload(null, false);
                    hideModal(addModal);
                    this.reset();
                    $('#add_user_id, #add_role_id').val(null).trigger('change');
                    themeToast('success', 'Mapping created');
                } else { themeToast('error', res.message || 'Failed to create'); }
            },
            error: xhr => {
                const errors = xhr.responseJSON?.errors || {};
                if (errors.user_id) $('#add-user-error').text(errors.user_id[0]).removeClass('hidden');
                if (errors.role_id) $('#add-role-error').text(errors.role_id[0]).removeClass('hidden');
                themeToast('error', 'Validation error');
            },
            complete: () => endBusy($btn)
        });
    });

    /* ===== Edit (open) — spinner di tombol action Edit (delegated) ===== */
    $(document).on('click', '.edit-button', async function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Loading...')) return;

        const userId = $btn.data('user');
        const roleId = $btn.data('role');
        const showUrl = "{{ route('user-role.pairShow') }}" + `?user_id=${userId}&role_id=${roleId}`;

        await ensureOptionsLoaded(); // may fetch; keep spinner
        fillSelect($('#edit_user_id'), optionsCache.users);
        fillSelect($('#edit_role_id'), optionsCache.roles);

        $.get(showUrl, function(data){
            $('#original_user_id').val(data.user_id);
            $('#original_role_id').val(data.role_id);
            $('#edit_user_id').val(data.user_id).trigger('change');
            $('#edit_role_id').val(data.role_id).trigger('change');
            showModal(editModal);
        }).fail(function(){
            themeToast('error', 'Failed to load mapping');
        }).always(function(){ endBusy($btn); });
    });

    /* ===== Update (PUT spoof) — spinner di tombol Save Changes ===== */
    $('#editURForm').on('submit', function(e){
        e.preventDefault();
        const $btn = $(this).find('button[type=submit]');
        if (!beginBusy($btn, 'Updating...')) return;

        const formData = new FormData(this);
        $('#edit-user-error, #edit-role-error').addClass('hidden');

        $.ajax({
            url: $(this).attr('action'), method: 'POST', // @method('PUT')
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData, processData: false, contentType: false,
            success: res => {
                if (res.success){
                    table.ajax.reload(null, false);
                    hideModal(editModal);
                    $('#edit_user_id, #edit_role_id').val(null).trigger('change');
                    themeToast('success', 'Mapping updated');
                } else { themeToast('error', res.message || 'Failed to update'); }
            },
            error: xhr => {
                const errors = xhr.responseJSON?.errors || {};
                if (errors.user_id) $('#edit-user-error').text(errors.user_id[0]).removeClass('hidden');
                if (errors.role_id) $('#edit-role-error').text(errors.role_id[0]).removeClass('hidden');
                themeToast('error', 'Validation error');
            },
            complete: () => endBusy($btn)
        });
    });

    /* ===== Delete (open) — spinner di tombol action Delete (delegated) ===== */
    $(document).on('click', '.delete-button', function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Opening...')) return;
        rowKeyToDelete = { user_id: $btn.data('user'), role_id: $btn.data('role') };
        showModal(deleteModal);
        setTimeout(()=>endBusy($btn), 150); // cukup untuk feedback
    });

    /* ===== Delete (confirm) — spinner di tombol confirm ===== */
    $('#confirmDeleteButton').on('click', function(){
        if (!rowKeyToDelete) return;
        const $btn = $(this);
        if (!beginBusy($btn, 'Deleting...')) return;

        const payload = new FormData();
        payload.append('user_id', rowKeyToDelete.user_id);
        payload.append('role_id', rowKeyToDelete.role_id);
        payload.append('_method', 'DELETE');

        $.ajax({
            url: "{{ route('user-role.pairDestroy') }}",
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken },
            data: payload, processData: false, contentType: false,
            success: res => {
                if (res?.success){
                    table.ajax.reload(null, false);
                    hideModal(deleteModal);
                    rowKeyToDelete = null;
                    themeToast('success', 'Mapping deleted');
                } else {
                    themeToast('error', (res && res.message) || 'Failed to delete');
                }
            },
            error: xhr => {
                let msg = 'Error deleting mapping';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch {}
                themeToast('error', msg);
            },
            complete: () => endBusy($btn)
        });
    });

    /* ===== UX minor: style focus control DT ===== */
    const overrideFocus = function(){ $(this).css({'outline':'none','box-shadow':'none','border-color':'gray'}); };
    const restoreBlur   = function(){ $(this).css('border-color',''); };
    const elementsToFix = $('.dataTables_filter input, .dataTables_length select');
    elementsToFix.on('focus keyup', overrideFocus);
    elementsToFix.on('blur', restoreBlur);
    elementsToFix.filter(':focus').each(overrideFocus);
});
</script>
@endpush



