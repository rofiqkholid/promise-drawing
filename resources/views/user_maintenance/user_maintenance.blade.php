@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'User Maintenance')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">User Maintenance</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage application users.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- Main Content: Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6">
            <table id="usersTable" class="min-w-full w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">Name</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="email">Email</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="nik">NIK</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-start">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add User Modal --}}
<div id="addUserModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add New User</h3>
            <form id="addUserForm" action="{{ route('userMaintenance.store') }}" method="POST">
                @csrf
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Full Name</label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. John Doe" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Email</label>
                    <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="user@example.com" required>
                    <p id="add-email-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="nik" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">NIK</label>
                    <input type="text" name="nik" id="nik" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 2025-001" required>
                    <p id="add-nik-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Password</label>
                    <input type="password" name="password" id="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="••••••••" required>
                    <p id="add-password-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
    <label for="add_is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
    <input type="checkbox" name="is_active" id="add_is_active" value="1"
        class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:ring-primary-600 focus:border-primary-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600"
        checked>
    <label for="add_is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
    <p id="add-is_active-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
</div>

                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div id="editUserModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit User</h3>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Full Name</label>
                    <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Email</label>
                    <input type="email" name="email" id="edit_email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-email-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_nik" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">NIK</label>
                    <input type="text" name="nik" id="edit_nik" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-nik-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">New Password (optional)</label>
                    <input type="password" name="password" id="edit_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Leave blank to keep current">
                </div>

                <div>
    <label for="add_is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
        class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:ring-primary-600 focus:border-primary-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600"
        checked>
    <label for="add_is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
    <p id="add-is_active-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
</div>

                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteUserModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this user?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                    No, cancel
                </button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                    Yes, I'm sure
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('style')
<style>
    /* Ikuti style Department: kecilkan kontrol DataTables */
    div.dataTables_length label { font-size: 0.75rem; }
    div.dataTables_length select {
        font-size: 0.75rem; line-height: 1rem;
        padding: 0.25rem 1.25rem 0.25rem 0.5rem; height: 1.875rem; width: 4.5rem;
    }
    div.dataTables_filter label { font-size: 0.75rem; }
    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="usersTable"] {
        font-size: 0.75rem; line-height: 1rem; padding: 0.25rem 0.5rem; height: 1.875rem; width: 12rem;
    }
    div.dataTables_info {
        font-size: 0.75rem;
        /* Ukuran teks kecil (text-xs) */
        padding-top: 0.8em;
        /* Sesuaikan padding agar sejajar dengan pagination */
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    /* ===== Helpers modal ===== */
    const addModal    = $('#addUserModal');
    const editModal   = $('#editUserModal');
    const deleteModal = $('#deleteUserModal');
    const addButton   = $('#add-button');
    const closeButtons= $('.close-modal-button');
    let userIdToDelete = null;

    function showModal(modal){ modal.removeClass('hidden').addClass('flex'); }
    function hideModal(modal){ modal.addClass('hidden').removeClass('flex'); }

    addButton.on('click', () => showModal(addModal));
    closeButtons.on('click', () => { hideModal(addModal); hideModal(editModal); hideModal(deleteModal); });

    /* ===== DataTable ===== */
    const table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{{ route("userMaintenance.data") }}',
            type: 'GET',
            data: function(d){ d.search = d.search.value; }
        },
        columns: [
            { // No
                data: null,
                render: function(data, type, row, meta){ return meta.row + meta.settings._iDisplayStart + 1; }
            },
            { data: 'name',  name: 'name'  },
            { data: 'email', name: 'email' },
            { data: 'nik',   name: 'nik'   },
            { // Status (Active / Inactive dari is_active: 1/0)
    data: 'is_active',
    render: function(val){
        const active = String(val) === '1';
        return active
            ? `<span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>`
            : `<span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>`;
    }
            },
            { // Action
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row){
                    return `
                        <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}">
                            <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
                        </button>
                        <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}">
                            <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                        </button>
                    `;
                }
            }
        ],
        pageLength: 10,
        order: [[1,'asc']],
        language: { emptyTable: '<div class="text-gray-500 dark:text-gray-400">No users found.</div>' },
        responsive: true,
        autoWidth: false,
    });

    /* ===== Create ===== */
    $('#addUserForm').on('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        formData.set('is_active', $('#add_is_active').is(':checked') ? '1' : '0');
        const nameErr = $('#add-name-error'), emailErr = $('#add-email-error'), nikErr = $('#add-nik-error'), passErr = $('#add-password-error');
        nameErr.addClass('hidden'); emailErr.addClass('hidden'); nikErr.addClass('hidden'); passErr.addClass('hidden');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData,
            processData: false, contentType: false,
            success: function(res){
                if (res.success){
                    table.ajax.reload(null, false);
                    hideModal(addModal);
                    $('#addUserForm')[0].reset();
                }
            },
            error: function(xhr){
                const errors = xhr.responseJSON?.errors;
                if (!errors) return;
                if (errors.name)     nameErr.text(errors.name[0]).removeClass('hidden');
                if (errors.email)    emailErr.text(errors.email[0]).removeClass('hidden');
                if (errors.nik)      nikErr.text(errors.nik[0]).removeClass('hidden');
                if (errors.password) passErr.text(errors.password[0]).removeClass('hidden');
            }
        });
    });

    /* ===== Read (prefill edit) ===== */
    $(document).on('click', '.edit-button', function () {
    const id = $(this).data('id');

    const showUrl   = "{{ route('userMaintenance.show', ':id') }}".replace(':id', id);
    const updateUrl = "{{ route('userMaintenance.update', ':id') }}".replace(':id', id);

    // reset error
    $('#edit-name-error, #edit-email-error, #edit-nik-error').addClass('hidden');

    $.get(showUrl, function (data) {
        $('#edit_name').val(data.name);
        $('#edit_email').val(data.email);
        $('#edit_nik').val(data.nik);
        $('#edit_is_active').prop('checked', data.is_active == 1);
        $('#editUserForm').attr('action', updateUrl);
        $('#editUserModal').removeClass('hidden').addClass('flex');
    });
});

    /* ===== Update ===== */
    $('#editUserForm').on('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        formData.set('is_active', $('#edit_is_active').is(':checked') ? '1' : '0');
        $('#edit-name-error, #edit-email-error, #edit-nik-error').addClass('hidden');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST', // pakai @method('PUT')
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData,
            processData: false, contentType: false,
            success: function(res){
                if (res.success){
                    table.ajax.reload(null, false);
                    hideModal(editModal);
                }
            },
            error: function(xhr){
                const errors = xhr.responseJSON?.errors;
                if (!errors) return;
                if (errors.name)  $('#edit-name-error').text(errors.name[0]).removeClass('hidden');
                if (errors.email) $('#edit-email-error').text(errors.email[0]).removeClass('hidden');
                if (errors.nik)   $('#edit-nik-error').text(errors.nik[0]).removeClass('hidden');
            }
        });
    });

    /* ===== Delete ===== */
    $(document).on('click', '.delete-button', function(){
        userIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function(){
        if (!userIdToDelete) return;
        $.ajax({
            url: `/master/userMaintenance/${userIdToDelete}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(res){
                if (res.success){
                    table.ajax.reload(null, false);
                    hideModal(deleteModal);
                    userIdToDelete = null;
                } else {
                    alert('Error deleting user.');
                }
            },
            error: function(){ alert('Error deleting user.'); }
        });
    });

    /* ===== Focus tweak untuk kontrol length/search (opsional) ===== */
    const overrideFocus = function(){ $(this).css({'outline':'none','box-shadow':'none','border-color':'gray'}); };
    const restoreBlur   = function(){ $(this).css('border-color',''); };
    const elementsToFix = $('.dataTables_filter input, .dataTables_length select');
    elementsToFix.on('focus keyup', overrideFocus);
    elementsToFix.on('blur', restoreBlur);
    elementsToFix.filter(':focus').each(overrideFocus);
});
</script>
@endpush
