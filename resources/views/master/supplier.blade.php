@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Supplier Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Supplier Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
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
            <table id="suppliersTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">#</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">
                            Supplier Name
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code">
                            Supplier Code
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="is_active">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Supplier Modal --}}
<div id="addSupplierModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Supplier</h3>
            <form id="addSupplierForm" action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Name</label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. ABC Corp" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Code</label>
                    <input type="text" name="code" id="code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. ABC" required>
                    <p id="add-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:ring-primary-600 focus:border-primary-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600" checked>
                        <label for="is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
                    </div>
                    <p id="add-is_active-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Add Supplier
                    </button>
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Supplier Modal --}}
<div id="editSupplierModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Supplier</h3>
            <form id="editSupplierForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Name</label>
                    <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Code</label>
                    <input type="text" name="code" id="edit_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:ring-primary-600 focus:border-primary-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600">
                        <label for="edit_is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
                    </div>
                    <p id="edit-is_active-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="submit" class="text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">
                        Update Supplier
                    </button>
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteSupplierModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this supplier?</p>
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
<style>
  /* Kecilkan ukuran komponen "Show ... entries" saja */
  div.dataTables_length label{
    font-size: 0.75rem;           /* text-xs */
  }
  div.dataTables_length select{
    font-size: 0.75rem;           /* text-xs */
    line-height: 1rem;            /* compact */
    padding: 0.25rem 1.25rem 0.25rem 0.5rem;
    height: 1.875rem;             /* ~30px, lebih kecil dari default */
    width: 4.5rem;                /* cukup untuk 10/25/50 */
  }

  div.dataTables_filter label{
    font-size: 0.75rem; /* text-xs */
  }

  /* Kecilkan input Search DataTables */
  div.dataTables_filter input[type="search"],
  input[type="search"][aria-controls="departmentsTable"]{
    font-size: 0.75rem;              /* text-xs */
    line-height: 1rem;
    padding: 0.25rem 0.5rem;         /* lebih rapat */
    height: 1.875rem;                /* ~30px */
    width: 12rem;                    /* ~192px, lebih kecil dari default */
  }
</style>
@push('scripts')
<script>
$(document).ready(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Initialize DataTable
    const table = $('#suppliersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("suppliers.data") }}',
            type: 'GET',
            data: function (d) {
                d.search = d.search.value;
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            {
                data: 'is_active',
                name: 'is_active',
                render: function (data, type, row) {
                    return data ? 'Active' : 'Inactive';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
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
        lengthMenu: [10, 25, 50],
        order: [[1, 'asc']],
        language: {
            emptyTable: '<div class="text-gray-500 dark:text-gray-400">No suppliers found.</div>'
        },
    });


    const overrideFocusStyles = function() {
            $(this).css({
                'outline': 'none',
                'box-shadow': 'none',
                'border-color': 'gray'
            });
        };
        const restoreBlurStyles = function() {
            $(this).css('border-color', '');
        };
        const elementsToFix = $('.dataTables_filter input, .dataTables_length select');
        elementsToFix.on('focus keyup', overrideFocusStyles);
        elementsToFix.on('blur', restoreBlurStyles);
        elementsToFix.filter(':focus').each(overrideFocusStyles);

    // Modal Handling
    const addModal = $('#addSupplierModal');
    const editModal = $('#editSupplierModal');
    const deleteModal = $('#deleteSupplierModal');
    const addButton = $('#add-button');
    const closeButtons = $('.close-modal-button');
    let supplierIdToDelete = null;

    function showModal(modal) {
        modal.removeClass('hidden').addClass('flex');
    }

    function hideModal(modal) {
        modal.addClass('hidden').removeClass('flex');
    }

    addButton.on('click', () => {
        $('#is_active').prop('checked', true); // Set default to checked (Active)
        showModal(addModal);
    });

    closeButtons.on('click', () => {
        hideModal(addModal);
        hideModal(editModal);
        hideModal(deleteModal);
    });

    // Add Supplier
    $('#addSupplierForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        // Ensure is_active is sent as 1 or 0
        formData.set('is_active', $('#is_active').is(':checked') ? '1' : '0');
        const nameError = $('#add-name-error');
        const codeError = $('#add-code-error');
        const isActiveError = $('#add-is_active-error');
        nameError.addClass('hidden');
        codeError.addClass('hidden');
        isActiveError.addClass('hidden');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(addModal);
                    $('#addSupplierForm')[0].reset();
                    $('#is_active').prop('checked', true); // Reset to checked
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    if (errors.name) {
                        nameError.text(errors.name[0]).removeClass('hidden');
                    }
                    if (errors.code) {
                        codeError.text(errors.code[0]).removeClass('hidden');
                    }
                    if (errors.is_active) {
                        isActiveError.text(errors.is_active[0]).removeClass('hidden');
                    }
                }
            }
        });
    });

    // Edit Supplier
    $(document).on('click', '.edit-button', function () {
        const id = $(this).data('id');
        const nameError = $('#edit-name-error');
        const codeError = $('#edit-code-error');
        const isActiveError = $('#edit-is_active-error');
        nameError.addClass('hidden');
        codeError.addClass('hidden');
        isActiveError.addClass('hidden');

        $.ajax({
            url: `/master/suppliers/${id}`,
            method: 'GET',
            success: function (data) {
                $('#edit_name').val(data.name);
                $('#edit_code').val(data.code);
                $('#edit_is_active').prop('checked', data.is_active);
                $('#editSupplierForm').attr('action', `/master/suppliers/${id}`);
                showModal(editModal);
            }
        });
    });

    $('#editSupplierForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        // Ensure is_active is sent as 1 or 0
        formData.set('is_active', $('#edit_is_active').is(':checked') ? '1' : '0');
        const nameError = $('#edit-name-error');
        const codeError = $('#edit-code-error');
        const isActiveError = $('#edit-is_active-error');
        nameError.addClass('hidden');
        codeError.addClass('hidden');
        isActiveError.addClass('hidden');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(editModal);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    if (errors.name) {
                        nameError.text(errors.name[0]).removeClass('hidden');
                    }
                    if (errors.code) {
                        codeError.text(errors.code[0]).removeClass('hidden');
                    }
                    if (errors.is_active) {
                        isActiveError.text(errors.is_active[0]).removeClass('hidden');
                    }
                }
            }
        });
    });

    // Delete Supplier
    $(document).on('click', '.delete-button', function () {
        supplierIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function () {
        if (supplierIdToDelete) {
            $.ajax({
                url: `/master/suppliers/${supplierIdToDelete}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        supplierIdToDelete = null;
                    } else {
                        alert('Error deleting supplier.');
                    }
                },
                error: function () {
                    alert('Error deleting supplier.');
                }
            });
        }
    });
});
</script>
@endpush
