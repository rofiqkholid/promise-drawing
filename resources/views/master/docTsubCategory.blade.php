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
                        <th scope="col" class="px-6 py-3 sorting" data-column="description">
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
                <div class="mb-4">
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Description</label>
                    <textarea name="description" id="description" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Enter a description"></textarea>
                    <p id="add-description-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
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
                <div class="mb-4">
                    <label for="edit_description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Description</label>
                    <textarea name="description" id="edit_description" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Enter a description"></textarea>
                    <p id="edit-description-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
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

{{-- Alias Management Modal --}}
<div id="aliasManagementModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-4xl h-full md:h-auto">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Manage Aliases for "<span id="aliasSubCategoryName"></span>"
                </h3>
                <button type="button" class="close-modal-button text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark w-5 h-5"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="flex justify-end mb-4">
                <button id="add-alias-button" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700">
                    <i class="fa-solid fa-plus"></i> Add New Alias
                </button>
            </div>
            <div class="overflow-x-auto p-2">
                <table id="aliasesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Alias Name</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Alias Modal --}}
<div id="addAliasModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
             <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Alias</h3>
            <form id="addAliasForm" action="{{ route('docTypeSubCategories.aliases.store') }}" method="POST">
                @csrf
                <input type="hidden" name="doctypesubcategory_id" id="add_alias_doctypesubcategory_id">
                <div class="mb-4">
                    <label for="add_alias_customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer</label>
                    <select name="customer_id" id="add_alias_customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" required>
                        <option value="">Select a customer</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="add_alias_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Alias Name</label>
                    <input type="text" name="name" id="add_alias_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" required>
                </div>
                <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save</button>
            </form>
        </div>
    </div>
</div>

{{-- Edit Alias Modal --}}
<div id="editAliasModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
             <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Alias</h3>
            <form id="editAliasForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_alias_customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer</label>
                    <select name="customer_id" id="edit_alias_customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" required>
                        <option value="">Select a customer</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="edit_alias_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Alias Name</label>
                    <input type="text" name="name" id="edit_alias_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" required>
                </div>
                <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
            </form>
        </div>
    </div>
</div>

{{-- Delete Alias Confirmation Modal --}}
<div id="deleteAliasModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5"><i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i></div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this alias?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100">No, cancel</button>
                <button type="button" id="confirmDeleteAliasButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700">Yes, I'm sure</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    div.dataTables_wrapper div.dataTables_filter input:focus,
    div.dataTables_wrapper div.dataTables_length select:focus {
        outline: none;
        box-shadow: none;
        border-color: #6b7280;
    }

    html.dark #addAliasModal input,
    html.dark #addAliasModal select,
    html.dark #editAliasModal input,
    html.dark #editAliasModal select {
        color: #f3f4f6;
        background-color: #374151;
        border-color: #4b5563;
    }

    html.dark div.dataTables_wrapper div.dataTables_filter input,
    html.dark div.dataTables_wrapper div.dataTables_length select {
        background-color: #374151;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    html.dark div.dataTables_wrapper div.dataTables_filter input::placeholder {
        color: #9ca3af;
    }

    html.dark div.dataTables_wrapper div.dataTables_length label,
    html.dark div.dataTables_wrapper div.dataTables_filter label,
    html.dark div.dataTables_wrapper div.dataTables_info,
    html.dark #aliasManagementModal .dataTables_length label,
    html.dark #aliasManagementModal .dataTables_filter label,
    html.dark #aliasManagementModal .dataTables_info {
        color: #d1d5db;
    }

    html.dark div.dataTables_wrapper div.dataTables_paginate .paginate_button {
        color: #d1d5db !important;
    }
    html.dark div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled {
        color: #6b7280 !important;
    }
    html.dark div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
    html.dark div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover {
        background: #3b82f6 !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }
    html.dark div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover {
        background: #374151 !important;
        border-color: #4b5563 !important;
    }

    html.dark .dataTable tbody tr {
        border-bottom-color: #374151;
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        scrollX: true,
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
                data: 'description',
                name: 'description',
                render: function (data) {
                    return data || '-';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `
                        <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}">
                            <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
                        </button>
                        <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}">
                            <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                        </button>
                        <button class="alias-button text-blue-600 hover:text-blue-900 dark:text-blue-500 dark:hover:text-blue-400" title="Manage Aliases" data-id="${row.id}" data-name="${row.name}">
                            <i class="fa-solid fa-tags fa-lg m-2"></i>
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
        responsive: true,
        autoWidth: false,
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

    function setButtonLoading($btn, isLoading, loadingText = 'Processing...') {
        if (!$btn || $btn.length === 0) return;
        if (isLoading) {
            if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
            $btn.prop('disabled', true);
            $btn.addClass('opacity-70 cursor-not-allowed');
            $btn.html(`
                <span class="inline-flex items-center gap-2">
                <svg aria-hidden="true" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                ${loadingText}
                </span>
            `);
        } else {
            const orig = $btn.data('orig-html');
            if (orig) $btn.html(orig);
            $btn.prop('disabled', false);
            $btn.removeClass('opacity-70 cursor-not-allowed');
        }
    }

    function setFormBusy($form, busy) {
        $form.find('input, select, textarea, button').prop('disabled', busy);
    }

    // Helper: SweetAlert notifications
        function detectTheme() {
            const isDark = document.documentElement.classList.contains('dark');

            return isDark ? {
                mode: 'dark',
                bg: 'rgba(30, 41, 59, 0.95)',
                fg: '#E5E7EB',
                border: 'rgba(71, 85, 105, 0.5)',
                progress: 'rgba(255,255,255,.9)',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444',
                    warning: '#f59e0b',
                    info: '#3b82f6'
                }
            } : {
                mode: 'light',
                bg: 'rgba(255, 255, 255, 0.98)',
                fg: '#0f172a',
                border: 'rgba(226, 232, 240, 1)',
                progress: 'rgba(15,23,42,.8)',
                icon: {
                    success: '#16a34a',
                    error: '#dc2626',
                    warning: '#d97706',
                    info: '#2563eb'
                }
            };
        }

        const BaseToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2600,
            timerProgressBar: true,
            showClass: {
                popup: 'swal2-animate-toast-in'
            },
            hideClass: {
                popup: 'swal2-animate-toast-out'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        function renderToast({
            icon = 'success',
            title = 'Success',
            text = ''
        } = {}) {
            const t = detectTheme();

            BaseToast.fire({
                icon,
                title,
                text,
                iconColor: t.icon[icon] || t.icon.success,
                background: t.bg,
                color: t.fg,
                customClass: {
                    popup: 'swal2-toast border',
                    title: '',
                    timerProgressBar: ''
                },
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

        function toastSuccess(title = 'Berhasil', text = 'Operasi berhasil dijalankan.') {
            renderToast({
                icon: 'success',
                title,
                text
            });
        }

        function toastError(title = 'Gagal', text = 'Terjadi kesalahan.') {
            BaseToast.update({
                timer: 3400
            });
            renderToast({
                icon: 'error',
                title,
                text
            });
            BaseToast.update({
                timer: 2600
            });
        }

        function toastWarning(title = 'Peringatan', text = 'Periksa kembali data Anda.') {
            renderToast({
                icon: 'warning',
                title,
                text
            });
        }

        function toastInfo(title = 'Informasi', text = '') {
            renderToast({
                icon: 'info',
                title,
                text
            });
        }

        window.toastSuccess = toastSuccess;
        window.toastError = toastError;
        window.toastWarning = toastWarning;
        window.toastInfo = toastInfo;

    function populateDocTypeGroupDropdown(selectElement, selectedId = null) {
        $.ajax({
            url: '{{ route("docTypeSubCategories.getDocTypeGroups") }}',
            method: 'GET',
            beforeSend: function() {
                selectElement.prop('disabled', true);
            },
            success: function (data) {
                selectElement.empty().append('<option value="">Select a group</option>');
                data.forEach(function (group) {
                    const selected = group.id == selectedId ? 'selected' : '';
                    selectElement.append(`<option value="${group.id}" ${selected}>${group.name}</option>`);
                });
                selectElement.trigger('change');
            },
            error: function (xhr) {
                toastError('Error', xhr.responseJSON?.message || 'Failed to load document type groups.');
            },
            complete: function() {
                selectElement.prop('disabled', false);
            }
        });
    }

    // Add Button Click
    addButton.on('click', () => {
        $('#addDocTypeSubCategoryForm')[0].reset();
        $('#add-doctype_group_id-error').addClass('hidden');
        $('#add-name-error').addClass('hidden');
        $('#add-description-error').addClass('hidden');
        populateDocTypeGroupDropdown($('#doctype_group_id'));
        $('#doctype_group_id').val(null).trigger('change');
        showModal(addModal);
    });

    // Close Modal Buttons
    closeButtons.on('click', () => {
        hideModal(addModal);
        hideModal(editModal);
        hideModal(deleteModal);
    });

    // Add Document Type Subcategory
    $('#addDocTypeSubCategoryForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('[type="submit"]');
        const doctypeGroupIdError = $('#add-doctype_group_id-error');
        const nameError = $('#add-name-error');
        const descriptionError = $('#add-description-error');
        doctypeGroupIdError.addClass('hidden');
        nameError.addClass('hidden');
        descriptionError.addClass('hidden');

        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                setButtonLoading($btn, true, 'Saving...');
                setFormBusy($form, true);
            },
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(addModal);
                    $form[0].reset();
                    $('#doctype_group_id').val('').trigger('change');
                    toastSuccess('Success', 'Document type subcategory added successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to add document type subcategory.');
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
                    if (errors.description) {
                        descriptionError.text(errors.description[0]).removeClass('hidden');
                    }
                }
                const msg = xhr.responseJSON?.message || 'Failed to add document type subcategory.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($btn, false);
                setFormBusy($form, false);
            }
        });
    });

    // Edit Document Type Subcategory
    $(document).on('click', '.edit-button', function () {
        const id = $(this).data('id');
        const doctypeGroupIdError = $('#edit-doctype_group_id-error');
        const nameError = $('#edit-name-error');
        const descriptionError = $('#edit-description-error');
        doctypeGroupIdError.addClass('hidden');
        nameError.addClass('hidden');
        descriptionError.addClass('hidden');

        $.ajax({
            url: `/master/docTypeSubCategories/${id}`,
            method: 'GET',
            beforeSend: function() {
                setButtonLoading($('.edit-button[data-id="' + id + '"]'), true, '');
            },
            success: function (data) {
                $('#edit_name').val(data.name);
                $('#edit_description').val(data.description || '');
                populateDocTypeGroupDropdown($('#edit_doctype_group_id'), data.doctype_group_id);
                $('#editDocTypeSubCategoryForm').attr('action', `/master/docTypeSubCategories/${id}`);
                showModal(editModal);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to fetch document type subcategory data.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($('.edit-button[data-id="' + id + '"]'), false);
            }
        });
    });

    // Submit Edit Form
    $('#editDocTypeSubCategoryForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('[type="submit"]');
        const doctypeGroupIdError = $('#edit-doctype_group_id-error');
        const nameError = $('#edit-name-error');
        const descriptionError = $('#edit-description-error');
        doctypeGroupIdError.addClass('hidden');
        nameError.addClass('hidden');
        descriptionError.addClass('hidden');

        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                setButtonLoading($btn, true, 'Saving...');
                setFormBusy($form, true);
            },
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(editModal);
                    toastSuccess('Success', 'Document type subcategory updated successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to update document type subcategory.');
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
                    if (errors.description) {
                        descriptionError.text(errors.description[0]).removeClass('hidden');
                    }
                }
                const msg = xhr.responseJSON?.message || 'Failed to update document type subcategory.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($btn, false);
                setFormBusy($form, false);
            }
        });
    });

    // Delete Document Type Subcategory
    $(document).on('click', '.delete-button', function () {
        docTypeSubCategoryIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function () {
        if (!docTypeSubCategoryIdToDelete) return;
        const $btn = $(this);

        $.ajax({
            url: `/master/docTypeSubCategories/${docTypeSubCategoryIdToDelete}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            beforeSend: function() {
                setButtonLoading($btn, true, 'Deleting...');
                setFormBusy($('#deleteDocTypeSubCategoryModal'), true);
            },
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(deleteModal);
                    docTypeSubCategoryIdToDelete = null;
                    toastSuccess('Success', 'Document type subcategory deleted successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to delete document type subcategory.');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to delete document type subcategory.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($btn, false);
                setFormBusy($('#deleteDocTypeSubCategoryModal'), false);
            }
        });
    });

    // Alias Management
    const aliasManagementModal = $('#aliasManagementModal');
    const addAliasModal = $('#addAliasModal');
    const editAliasModal = $('#editAliasModal');
    const deleteAliasModal = $('#deleteAliasModal');
    let aliasTable;
    let currentSubcategoryId = null;
    let aliasIdToDelete = null;

    $('#add_alias_customer_id').select2({ dropdownParent: $('#addAliasModal'), width: '100%' });
    $('#edit_alias_customer_id').select2({ dropdownParent: $('#editAliasModal'), width: '100%' });

    function populateCustomersDropdown(selectElement, selectedId = null) {
        $.ajax({
            url: '{{ route("docTypeSubCategories.getCustomers") }}',
            method: 'GET',
            success: function (data) {
                selectElement.empty().append('<option value="">Select a customer</option>');
                data.forEach(function (customer) {
                    const selected = customer.id == selectedId ? 'selected' : '';
                    selectElement.append(`<option value="${customer.id}" ${selected}>${customer.name}</option>`);
                });
                selectElement.trigger('change');
            },
            error: function () { toastError('Error', 'Failed to load customers.'); }
        });
    }

    $(document).on('click', '.alias-button', function () {
        currentSubcategoryId = $(this).data('id');
        const subCategoryName = $(this).data('name');
        $('#aliasSubCategoryName').text(subCategoryName);

        if ($.fn.DataTable.isDataTable('#aliasesTable')) {
            aliasTable.ajax.url(`/master/docTypeSubCategories/${currentSubcategoryId}/aliases`).load();
        } else {
            aliasTable = $('#aliasesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/master/docTypeSubCategories/${currentSubcategoryId}/aliases`,
                    type: 'GET'
                },
                columns: [
                    { data: null, searchable: false, orderable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'customer.name', name: 'customer' },
                    { data: 'name', name: 'name' },
                    {
                        data: null, orderable: false, searchable: false, className: 'text-center',
                        render: function (data, type, row) {
                            return `
                                <button class="edit-alias-button text-gray-400 hover:text-gray-700 mx-2" title="Edit Alias" data-id="${row.id}"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button class="delete-alias-button text-red-600 hover:text-red-900 mx-2" title="Delete Alias" data-id="${row.id}"><i class="fa-solid fa-trash-can"></i></button>
                            `;
                        }
                    }
                ],
                order: [[1, 'asc']]
            });
        }
        showModal(aliasManagementModal);
    });

    $('#add-alias-button').on('click', function () {
        $('#addAliasForm')[0].reset();
        $('#add_alias_doctypesubcategory_id').val(currentSubcategoryId);
        populateCustomersDropdown($('#add_alias_customer_id'));
        showModal(addAliasModal);
    });

    $('#addAliasForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function (data) {
                if (data.success) {
                    aliasTable.ajax.reload();
                    hideModal(addAliasModal);
                    toastSuccess('Success', 'Alias added successfully.');
                }
            },
            error: function (xhr) { toastError('Error', xhr.responseJSON.message); }
        });
    });

    $(document).on('click', '.edit-alias-button', function () {
        const aliasId = $(this).data('id');
        $.ajax({
            url: `/master/aliases/${aliasId}`,
            method: 'GET',
            success: function (data) {
                $('#edit_alias_name').val(data.name);
                populateCustomersDropdown($('#edit_alias_customer_id'), data.customer_id);
                $('#editAliasForm').attr('action', `/master/aliases/${aliasId}`);
                showModal(editAliasModal);
            },
            error: function () { toastError('Error', 'Failed to fetch alias data.'); }
        });
    });

    $('#editAliasForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function (data) {
                if (data.success) {
                    aliasTable.ajax.reload();
                    hideModal(editAliasModal);
                    toastSuccess('Success', 'Alias updated successfully.');
                }
            },
            error: function (xhr) { toastError('Error', xhr.responseJSON.message); }
        });
    });

    $(document).on('click', '.delete-alias-button', function () {
        aliasIdToDelete = $(this).data('id');
        showModal(deleteAliasModal);
    });

    $('#confirmDeleteAliasButton').on('click', function () {
        $.ajax({
            url: `/master/aliases/${aliasIdToDelete}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (data) {
                if (data.success) {
                    aliasTable.ajax.reload();
                    hideModal(deleteAliasModal);
                    aliasIdToDelete = null;
                    toastSuccess('Success', 'Alias deleted successfully.');
                }
            },
            error: function (xhr) { toastError('Error', xhr.responseJSON.message); }
        });
    });

    $(document).on('click', '.close-modal-button', function() {
        const modalToClose = $(this).closest('[tabindex="-1"]');
        hideModal(modalToClose);
    });
});
</script>
@endpush
