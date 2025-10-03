@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Document Type Subcategory Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Document Type Subcategory Master</h2>
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
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="docTypeSubCategoriesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="docTypeGroup">
                            Document Type Group
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">
                            Subcategory Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Document Type Subcategory Modal --}}
<div id="addDocTypeSubCategoryModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Document Type Subcategory</h3>
            <form id="addDocTypeSubCategoryForm" action="{{ route('docTypeSubCategories.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="doctype_group_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Document Type Group</label>
                    <select name="doctype_group_id" id="doctype_group_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select a group</option>
                    </select>
                    <p id="add-doctype_group_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Subcategory Name</label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Agreements" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
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

{{-- Edit Document Type Subcategory Modal --}}
<div id="editDocTypeSubCategoryModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Document Type Subcategory</h3>
            <form id="editDocTypeSubCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_doctype_group_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Document Type Group</label>
                    <select name="doctype_group_id" id="edit_doctype_group_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select a group</option>
                    </select>
                    <p id="edit-doctype_group_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Subcategory Name</label>
                    <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
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
<div id="deleteDocTypeSubCategoryModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this document type subcategory?</p>
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
    div.dataTables_length label{
        font-size: 0.75rem;
    }
    div.dataTables_length select{
        font-size: 0.75rem;
        line-height: 1rem;
        padding: 0.25rem 1.25rem 0.25rem 0.5rem;
        height: 1.875rem;
        width: 4.5rem;
    }
    div.dataTables_filter label{
        font-size: 0.75rem;
    }
    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="departmentsTable"]{
        font-size: 0.75rem;
        line-height: 1rem;
        padding: 0.25rem 0.5rem;
        height: 1.875rem;
        width: 12rem;
    }
    div.dataTables_info {
        font-size: 0.75rem;
        padding-top: 0.8em;
    }

    .select2-container--default .select2-selection--single {
        display: flex;
        align-items: center;
        justify-content: flex-start !important;
        text-align: left !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        text-align: left !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        right: 10px !important;
    }

    div.dataTables_wrapper div.dataTables_scrollBody::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    div.dataTables_wrapper div.dataTables_scrollBody {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }

    input::placeholder {
        text-align: left;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('#doctype_group_id').select2({
        dropdownParent: $('#addDocTypeSubCategoryModal'),
        width: '100%'
    });
    $('#edit_doctype_group_id').select2({
        dropdownParent: $('#editDocTypeSubCategoryModal'),
        width: '100%'
    });

    const table = $('#docTypeSubCategoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("docTypeSubCategories.data") }}',
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
            {
                data: 'doc_type_group',
                name: 'doc_type_group',
                render: function (data, type, row) {
                    return data ? data.name : '-';
                }
            },
            { data: 'name', name: 'name' },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `
                        <button class="edit-button text-gary-400 hover:text-gary-700 dark:text-gary-400 dark:hover:text-gary-300" title="Edit" data-id="${row.id}">
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
        order: [[2, 'asc']],
        language: {
            emptyTable: '<div class="text-gray-500 dark:text-gray-400">No document type subcategories found.</div>'
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

    const addModal = $('#addDocTypeSubCategoryModal');
    const editModal = $('#editDocTypeSubCategoryModal');
    const deleteModal = $('#deleteDocTypeSubCategoryModal');
    const addButton = $('#add-button');
    const closeButtons = $('.close-modal-button');
    let docTypeSubCategoryIdToDelete = null;

    function showModal(modal) {
        modal.removeClass('hidden').addClass('flex');
    }

    function hideModal(modal) {
        modal.addClass('hidden').removeClass('flex');
    }

    function populateDocTypeGroupDropdown(selectElement, selectedId = null) {
        $.ajax({
            url: '{{ route("docTypeSubCategories.getDocTypeGroups") }}',
            method: 'GET',
            success: function (data) {
                selectElement.empty().append('<option value="">Select a group</option>');
                data.forEach(function (group) {
                    const selected = group.id == selectedId ? 'selected' : '';
                    selectElement.append(`<option value="${group.id}" ${selected}>${group.name}</option>`);
                });
                selectElement.trigger('change');
            },
            error: function () {
                alert('Error loading document type groups.');
            }
        });
    }

    addButton.on('click', () => {
        $('#addDocTypeSubCategoryForm')[0].reset();
        populateDocTypeGroupDropdown($('#doctype_group_id'));
        $('#doctype_group_id').val(null).trigger('change');
        showModal(addModal);
    });

    closeButtons.on('click', () => {
        hideModal(addModal);
        hideModal(editModal);
        hideModal(deleteModal);
    });

    $('#addDocTypeSubCategoryForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const doctypeGroupIdError = $('#add-doctype_group_id-error');
        const nameError = $('#add-name-error');
        doctypeGroupIdError.addClass('hidden');
        nameError.addClass('hidden');

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
                    $('#addDocTypeSubCategoryForm')[0].reset();
                    $('#doctype_group_id').val('').trigger('change');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    if (errors.doctype_group_id) {
                        doctypeGroupIdError.text(errors.doctype_group_id[0]).removeClass('hidden');
                    }
                    if (errors.name) {
                        nameError.text(errors.name[0]).removeClass('hidden');
                    }
                }
            }
        });
    });

    $(document).on('click', '.edit-button', function () {
        const id = $(this).data('id');
        const doctypeGroupIdError = $('#edit-doctype_group_id-error');
        const nameError = $('#edit-name-error');
        doctypeGroupIdError.addClass('hidden');
        nameError.addClass('hidden');

        $.ajax({
            url: `/master/docTypeSubCategories/${id}`,
            method: 'GET',
            success: function (data) {
                $('#edit_name').val(data.name);
                populateDocTypeGroupDropdown($('#edit_doctype_group_id'), data.doctype_group_id);
                $('#editDocTypeSubCategoryForm').attr('action', `/master/docTypeSubCategories/${id}`);
                showModal(editModal);
            }
        });
    });

    $('#editDocTypeSubCategoryForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const doctypeGroupIdError = $('#edit-doctype_group_id-error');
        const nameError = $('#edit-name-error');
        doctypeGroupIdError.addClass('hidden');
        nameError.addClass('hidden');

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
                    if (errors.doctype_group_id) {
                        doctypeGroupIdError.text(errors.doctype_group_id[0]).removeClass('hidden');
                    }
                    if (errors.name) {
                        nameError.text(errors.name[0]).removeClass('hidden');
                    }
                }
            }
        });
    });

    $(document).on('click', '.delete-button', function () {
        docTypeSubCategoryIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function () {
        if (docTypeSubCategoryIdToDelete) {
            $.ajax({
                url: `/master/docTypeSubCategories/${docTypeSubCategoryIdToDelete}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        docTypeSubCategoryIdToDelete = null;
                    } else {
                        alert('Error deleting document type subcategory.');
                    }
                },
                error: function () {
                    alert('Error deleting document type subcategory.');
                }
            });
        }
    });
});
</script>
@endpush
