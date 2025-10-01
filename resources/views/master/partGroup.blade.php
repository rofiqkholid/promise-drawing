@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Part Group Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Part Group Master</h2>
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
            <table id="partGroupsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">#</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="customer_code">
                            Customer Code
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="model_code">
                            Model Code
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code_part_group">
                            Part Group Code
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code_part_group_desc">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Part Group Modal --}}
<div id="addPartGroupModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Part Group</h3>
            <form id="addPartGroupForm" action="{{ route('partGroups.store') }}" method="POST">
                @csrf
                <div>
                    <label for="customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer</label>
                    <select name="customer_id" id="customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select Customer</option>
                        @foreach(App\Models\Customers::select('id', 'code')->get() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->code }}</option>
                        @endforeach
                    </select>
                    <p id="add-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model</label>
                    <select name="model_id" id="model_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select Model</option>
                    </select>
                    <p id="add-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="code_part_group" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Group Code</label>
                    <input type="text" name="code_part_group" id="code_part_group" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. PG001" required>
                    <p id="add-code_part_group-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="code_part_group_desc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Description</label>
                    <input type="text" name="code_part_group_desc" id="code_part_group_desc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Engine Components" required>
                    <p id="add-code_part_group_desc-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Add Part Group
                    </button>
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Part Group Modal --}}
<div id="editPartGroupModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Part Group</h3>
            <form id="editPartGroupForm" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer</label>
                    <select name="customer_id" id="edit_customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select Customer</option>
                        @foreach(App\Models\Customers::select('id', 'code')->get() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->code }}</option>
                        @endforeach
                    </select>
                    <p id="edit-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model</label>
                    <select name="model_id" id="edit_model_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select Model</option>
                    </select>
                    <p id="edit-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_code_part_group" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Group Code</label>
                    <input type="text" name="code_part_group" id="edit_code_part_group" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-code_part_group-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_code_part_group_desc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Description</label>
                    <input type="text" name="code_part_group_desc" id="edit_code_part_group_desc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-code_part_group_desc-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="submit" class="text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">
                        Update Part Group
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
<div id="deletePartGroupModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this part group?</p>
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
    const table = $('#partGroupsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("partGroups.data") }}',
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
            { data: 'customer_code', name: 'customer_code' },
            { data: 'model_code', name: 'model_code' },
            { data: 'code_part_group', name: 'code_part_group' },
            { data: 'code_part_group_desc', name: 'code_part_group_desc' },
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
        order: [[3, 'asc']],
        language: {
            emptyTable: '<div class="text-gray-500 dark:text-gray-400">No part groups found.</div>'
        },
    });

    // Modal Handling
    const addModal = $('#addPartGroupModal');
    const editModal = $('#editPartGroupModal');
    const deleteModal = $('#deletePartGroupModal');
    const addButton = $('#add-button');
    const closeButtons = $('.close-modal-button');
    let partGroupIdToDelete = null;

    function showModal(modal) {
        modal.removeClass('hidden').addClass('flex');
    }

    function hideModal(modal) {
        modal.addClass('hidden').removeClass('flex');
    }

    // Function to load models based on customer selection
    function loadModels(customerId, modelSelect, selectedModelId = null) {
        if (!customerId) {
            modelSelect.html('<option value="">Select Model</option>');
            return;
        }
        $.ajax({
            url: '{{ route("partGroups.getModelsByCustomer") }}',
            method: 'GET',
            data: { customer_id: customerId },
            success: function (data) {
                modelSelect.html('<option value="">Select Model</option>');
                data.forEach(function (model) {
                    modelSelect.append(`<option value="${model.id}">${model.code}</option>`);
                });
                if (selectedModelId) {
                    modelSelect.val(selectedModelId);
                }
            },
            error: function () {
                modelSelect.html('<option value="">Select Model</option>');
            }
        });
    }

    // Add Modal: Load models when customer changes
    $('#customer_id').on('change', function () {
        const customerId = $(this).val();
        const modelSelect = $('#model_id');
        loadModels(customerId, modelSelect);
    });

    // Edit Modal: Load models when customer changes
    $('#edit_customer_id').on('change', function () {
        const customerId = $(this).val();
        const modelSelect = $('#edit_model_id');
        loadModels(customerId, modelSelect);
    });

    addButton.on('click', () => {
        $('#addPartGroupForm')[0].reset();
        $('#model_id').html('<option value="">Select Model</option>');
        showModal(addModal);
    });

    closeButtons.on('click', () => {
        hideModal(addModal);
        hideModal(editModal);
        hideModal(deleteModal);
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

    // Add Part Group
    $('#addPartGroupForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const customerIdError = $('#add-customer_id-error');
        const modelIdError = $('#add-model_id-error');
        const codePartGroupError = $('#add-code_part_group-error');
        const codePartGroupDescError = $('#add-code_part_group_desc-error');
        customerIdError.addClass('hidden');
        modelIdError.addClass('hidden');
        codePartGroupError.addClass('hidden');
        codePartGroupDescError.addClass('hidden');

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
                    $('#addPartGroupForm')[0].reset();
                    $('#model_id').html('<option value="">Select Model</option>');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    if (errors.customer_id) {
                        customerIdError.text(errors.customer_id[0]).removeClass('hidden');
                    }
                    if (errors.model_id) {
                        modelIdError.text(errors.model_id[0]).removeClass('hidden');
                    }
                    if (errors.code_part_group) {
                        codePartGroupError.text(errors.code_part_group[0]).removeClass('hidden');
                    }
                    if (errors.code_part_group_desc) {
                        codePartGroupDescError.text(errors.code_part_group_desc[0]).removeClass('hidden');
                    }
                }
            }
        });
    });

    // Edit Part Group
    $(document).on('click', '.edit-button', function () {
        const id = $(this).data('id');
        const customerIdError = $('#edit-customer_id-error');
        const modelIdError = $('#edit-model_id-error');
        const codePartGroupError = $('#edit-code_part_group-error');
        const codePartGroupDescError = $('#edit-code_part_group_desc-error');
        customerIdError.addClass('hidden');
        modelIdError.addClass('hidden');
        codePartGroupError.addClass('hidden');
        codePartGroupDescError.addClass('hidden');

        $.ajax({
            url: `/master/partGroups/${id}`,
            method: 'GET',
            success: function (data) {
                $('#edit_customer_id').val(data.customer_id);
                $('#edit_code_part_group').val(data.code_part_group);
                $('#edit_code_part_group_desc').val(data.code_part_group_desc);
                $('#editPartGroupForm').attr('action', `/master/partGroups/${id}`);
                loadModels(data.customer_id, $('#edit_model_id'), data.model_id);
                showModal(editModal);
            }
        });
    });

    $('#editPartGroupForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const customerIdError = $('#edit-customer_id-error');
        const modelIdError = $('#edit-model_id-error');
        const codePartGroupError = $('#edit-code_part_group-error');
        const codePartGroupDescError = $('#edit-code_part_group_desc-error');
        customerIdError.addClass('hidden');
        modelIdError.addClass('hidden');
        codePartGroupError.addClass('hidden');
        codePartGroupDescError.addClass('hidden');

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
                    if (errors.customer_id) {
                        customerIdError.text(errors.customer_id[0]).removeClass('hidden');
                    }
                    if (errors.model_id) {
                        modelIdError.text(errors.model_id[0]).removeClass('hidden');
                    }
                    if (errors.code_part_group) {
                        codePartGroupError.text(errors.code_part_group[0]).removeClass('hidden');
                    }
                    if (errors.code_part_group_desc) {
                        codePartGroupDescError.text(errors.code_part_group_desc[0]).removeClass('hidden');
                    }
                }
            }
        });
    });

    // Delete Part Group
    $(document).on('click', '.delete-button', function () {
        partGroupIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function () {
        if (partGroupIdToDelete) {
            $.ajax({
                url: `/master/partGroups/${partGroupIdToDelete}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        partGroupIdToDelete = null;
                    } else {
                        alert('Error deleting part group.');
                    }
                },
                error: function () {
                    alert('Error deleting part group.');
                }
            });
        }
    });
});
</script>
@endpush
