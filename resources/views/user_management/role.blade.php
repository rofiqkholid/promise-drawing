@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Role Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Role Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="roleTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 w-16">No</th>
                        <th class="px-6 py-3">Role Name</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Role Modal --}}
<div id="addRoleModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Role</h3>
            <form id="addRoleForm" action="{{ route('role.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Role Name</label>
                    <input type="text" name="role_name" id="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Admin" required>
                    <p id="add-role-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Role Modal --}}
<div id="editRoleModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Role</h3>
            <form id="editRoleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Role Name</label>
                    <input type="text" name="role_name" id="edit_role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-role-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteRoleModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this role?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">Yes, I'm sure</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    div.dataTables_length label{ font-size: .75rem; }
    div.dataTables_length select{
        font-size:.75rem; line-height:1rem; padding:.25rem 1.25rem .25rem .5rem; height:1.875rem; width:4.5rem;
    }
    div.dataTables_filter label{ font-size:.75rem; }
    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="departmentsTable"]{
        font-size:.75rem; line-height:1rem; padding:.25rem .5rem; height:1.875rem; width:12rem;
    }
    div.dataTables_info { font-size:.75rem; padding-top:.8em; }
    div.dataTables_wrapper div.dataTables_scrollBody::-webkit-scrollbar{ display:none!important; width:0!important; height:0!important; }
    div.dataTables_wrapper div.dataTables_scrollBody{ -ms-overflow-style:none!important; scrollbar-width:none!important; }
    input::placeholder { text-align:left; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    /* ========= Theme-aware SweetAlert2 ========= */
    const isDark = () => document.documentElement.classList.contains('dark');
    function themeToast(icon, title){
        const dark = isDark();
        Swal.fire({
            toast: true, icon, title,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
            background: dark ? '#1f2937' : '#ffffff',
            color:       dark ? '#f9fafb' : '#111827',
            didOpen: el => {
                const bar = el.querySelector('.swal2-timer-progress-bar');
                if (bar) bar.style.background = dark ? '#10b981' : '#3b82f6';
            }
        });
    }

    /* ========= Spinner helpers ========= */
    const spinnerSVG = `
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>`;

    function beginBusy($btn, text='Processing...'){
        if ($btn.data('busy')) return false;
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

    // Anti double-click global
    $(document).on('click', 'button', function(e){
        const $b = $(this);
        if ($b.data('busy')) { e.preventDefault(); e.stopImmediatePropagation(); return false; }
    });

    /* ========= Modal helpers ========= */
    const addModal = $('#addRoleModal');
    const editModal = $('#editRoleModal');
    const deleteModal = $('#deleteRoleModal');
    const addButton = $('#add-button');
    const closeButtons = $('.close-modal-button');
    let roleIdToDelete = null;

    const showModal = m => m.removeClass('hidden').addClass('flex');
    const hideModal = m => m.addClass('hidden').removeClass('flex');

    addButton.on('click', function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Opening...')) return;
        $('#addRoleForm')[0].reset();
        $('#add-role-error').addClass('hidden');
        showModal(addModal);
        setTimeout(()=>endBusy($btn), 150);
    });

    closeButtons.on('click', function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Closing...')) return;
        hideModal(addModal); hideModal(editModal); hideModal(deleteModal);
        setTimeout(()=>endBusy($btn), 150);
    });

    /* ========= DataTable ========= */
    const table = $('#roleTable').DataTable({
        processing: true, serverSide: true,
        ajax: { url: '{{ route("role.data") }}', type: 'GET', data: d => { d.search = d.search?.value ?? ''; } },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1 },
            { data: 'role_name', name: 'role_name' },
            { data: null, orderable:false, searchable:false, className:'text-center',
              render: row => `
                <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}">
                    <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
                </button>
                <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}">
                    <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                </button>`
            }
        ],
        pageLength: 10, order: [[1,'asc']],
        language: { emptyTable: '<div class="text-gray-500 dark:text-gray-400">No roles found.</div>' }
    });

    /* ========= Create ========= */
    $('#addRoleForm').on('submit', function(e){
        e.preventDefault();
        const $btn = $(this).find('button[type=submit]');
        if (!beginBusy($btn, 'Saving...')) return;

        const formData = new FormData(this);
        const nameError = $('#add-role-error').addClass('hidden');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' },
            data: formData, processData:false, contentType:false,
            success: res => {
                if(res.success){
                    table.ajax.reload(null,false);
                    hideModal(addModal);
                    this.reset();
                    themeToast('success','Role created');
                } else themeToast('error', res.message || 'Failed to create');
            },
            error: xhr => {
                const errs = xhr.responseJSON?.errors;
                if (errs?.role_name) nameError.text(errs.role_name[0]).removeClass('hidden');
                themeToast('error','Validation error');
            },
            complete: () => endBusy($btn)
        });
    });

    /* ========= Edit (open) ========= */
    $(document).on('click', '.edit-button', function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Loading...')) return;
        const id = $btn.data('id');
        $('#edit-role-error').addClass('hidden');
        $.get(`/user_management/role/${id}`, data=>{
            $('#edit_role').val(data.role_name);
            $('#editRoleForm').attr('action', `/user_management/role/${id}`);
            showModal(editModal);
        }).fail(()=>themeToast('error','Failed to load role'))
          .always(()=>endBusy($btn));
    });

    /* ========= Edit (submit) ========= */
    $('#editRoleForm').on('submit', function(e){
        e.preventDefault();
        const $btn = $(this).find('button[type=submit]');
        if (!beginBusy($btn, 'Updating...')) return;
        const fd = new FormData(this);
        const nameError = $('#edit-role-error').addClass('hidden');
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST', // spoof PUT
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' },
            data: fd, processData:false, contentType:false,
            success: res=>{
                if(res.success){
                    table.ajax.reload(null,false);
                    hideModal(editModal);
                    themeToast('success','Role updated');
                } else themeToast('error', res.message || 'Failed to update');
            },
            error: xhr=>{
                const errs = xhr.responseJSON?.errors;
                if (errs?.role_name) nameError.text(errs.role_name[0]).removeClass('hidden');
                themeToast('error','Validation error');
            },
            complete: ()=>endBusy($btn)
        });
    });

    /* ========= Delete (open) ========= */
    $(document).on('click','.delete-button',function(){
        const $btn = $(this);
        if (!beginBusy($btn, 'Opening...')) return;
        roleIdToDelete = $btn.data('id');
        showModal(deleteModal);
        setTimeout(()=>endBusy($btn),150);
    });

    /* ========= Delete (confirm) ========= */
    $('#confirmDeleteButton').on('click',function(){
        if(!roleIdToDelete) return;
        const $btn = $(this);
        if (!beginBusy($btn, 'Deleting...')) return;
        $.ajax({
            url: `/user_management/role/${roleIdToDelete}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' },
            success: res=>{
                if(res.success){
                    table.ajax.reload(null,false);
                    hideModal(deleteModal);
                    roleIdToDelete=null;
                    themeToast('success','Role deleted');
                } else themeToast('error', res.message || 'Failed to delete');
            },
            error: ()=>themeToast('error','Error deleting role'),
            complete: ()=>endBusy($btn)
        });
    });

    /* ========= Minor UI focus tweak ========= */
    const overrideFocus = function(){ $(this).css({'outline':'none','box-shadow':'none','border-color':'gray'}); };
    const restoreBlur   = function(){ $(this).css('border-color',''); };
    const fixEls = $('.dataTables_filter input, .dataTables_length select');
    fixEls.on('focus keyup', overrideFocus);
    fixEls.on('blur', restoreBlur);
    fixEls.filter(':focus').each(overrideFocus);
});
</script>
@endpush


