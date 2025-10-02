@extends('layouts.app')

@section('header-title', 'Menu Management')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Menu Management</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage application menu using DataTables.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- Main Content: Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6">
            <table id="menuTable" class="min-w-full w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-center">No</th>
                        <th scope="col" class="px-6 py-3">Title</th>
                        <th scope="col" class="px-6 py-3">Parent</th>
                        <th scope="col" class="px-6 py-3">Route</th>
                        <th scope="col" class="px-6 py-3 text-center">Icon</th>
                        <th scope="col" class="px-6 py-3 text-center">Order</th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Menu Modal --}}
<div id="addMenuModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white text-center">Add New Menu</h3>
            <form id="addMenuForm" action="{{ route('menus.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Title</label>
                    <input type="text" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Dashboard" required>
                    <p id="add-title-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sort_order" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Sort Order</label>
                        <input type="number" name="sort_order" value="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <p id="add-sort_order-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                    <div>
                        <label for="route" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Route</label>
                        <input type="text" name="route" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. dashboard">
                        <p id="add-route-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                </div>
                <div>
                    <label for="icon" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Icon</label>
                    <input type="text" name="icon" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. fas fa-home">
                    <p id="add-icon-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="parent_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Parent Menu</label>
                    <select name="parent_id" id="parent_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                        <option value="">-- No Parent --</option>
                    </select>
                    <p id="add-parent_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Menu Modal --}}
<div id="editMenuModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5 text-center">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Menu</h3>
            <form id="editMenuForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Title</label>
                    <input type="text" name="title" id="edit_title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-title-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_sort_order" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <p id="edit-sort_order-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                    <div>
                        <label for="edit_route" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Route</label>
                        <input type="text" name="route" id="edit_route" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                        <p id="edit-route-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                </div>
                <div>
                    <label for="edit_icon" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Icon</label>
                    <input type="text" name="icon" id="edit_icon" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <p id="edit-icon-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div>
                    <label for="edit_parent_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Parent Menu</label>
                    <select name="parent_id" id="edit_parent_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                        <option value="">-- No Parent --</option>
                    </select>
                     <p id="edit-parent_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteMenuModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this menu? Child menus will be preserved.</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600">
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
    div.dataTables_length label {
        font-size: 0.75rem;
    }
    div.dataTables_length select {
        font-size: 0.75rem;
        line-height: 1rem;
        padding: 0.25rem 1.25rem 0.25rem 0.5rem;
        height: 1.875rem;
        width: 4.5rem;
    }
    div.dataTables_filter label {
        font-size: 0.75rem;
    }
    div.dataTables_filter input[type="search"] {
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
</style>

@push('scripts')
<script>
$(document).ready(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    const table = $('#menuTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("menus.data") }}',
        order: [[5, 'asc']],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'title', name: 'title' },
            { data: 'parent_name', name: 'parent.title', defaultContent: '-' },
            { data: 'route', name: 'route', render: data => data ? `<code>${data}</code>` : '-' },
            { data: 'icon', name: 'icon', className: 'text-center', render: data => data ? `<i class="${data} text-lg"></i>` : '-' },
            { data: 'sort_order', name: 'sort_order', className: 'text-center', render: data => `<span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/70 dark:text-blue-200 rounded-full px-3 py-1"><i class="fa-solid fa-arrow-down-short-wide"></i> ${data}</span>` },
            { data: null, orderable: false, searchable: false, className: 'text-center', render: (data, type, row) => `
                <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}"><i class="fa-solid fa-pen-to-square fa-lg m-2"></i></button>
                <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}"><i class="fa-solid fa-trash-can fa-lg m-2"></i></button>
            `}
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        responsive: true,
        autoWidth: false,
        scrollX: true,
    });

    const addModal = $('#addMenuModal');
    const editModal = $('#editMenuModal');
    const deleteModal = $('#deleteMenuModal');
    let menuIdToDelete = null;

    const showModal = modal => modal.removeClass('hidden').addClass('flex');
    const hideModal = modal => modal.addClass('hidden').removeClass('flex');

    const resetErrors = () => {
        $('.error-message').addClass('hidden').text('');
    };

    const displayErrors = (errors, prefix) => {
        resetErrors();
        for (const key in errors) {
            $(`#${prefix}-${key}-error`).text(errors[key][0]).removeClass('hidden');
        }
    };

    const populateParentDropdown = ($select, selectedId = null) => {
        $.get('{{ route("menus.getParents") }}', function(parents) {
            $select.empty().append('<option value="">-- No Parent --</option>');
            parents.forEach(parent => {
                const isSelected = selectedId == parent.id ? ' selected' : '';
                $select.append(`<option value="${parent.id}"${isSelected}>${parent.title}</option>`);
            });
        });
    };

    $('#add-button').on('click', () => {
        $('#addMenuForm')[0].reset();
        resetErrors();
        populateParentDropdown($('#parent_id'));
        showModal(addModal);
    });

    $('.close-modal-button').on('click', () => {
        hideModal(addModal);
        hideModal(editModal);
        hideModal(deleteModal);
    });

    $('#addMenuForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: () => {
                table.ajax.reload();
                hideModal(addModal);
            },
            error: xhr => displayErrors(xhr.responseJSON.errors, 'add')
        });
    });

    $(document).on('click', '.edit-button', function () {
        const id = $(this).data('id');
        resetErrors();
        $.get(`/master/menus/${id}`, function (data) {
            $('#edit_title').val(data.title);
            $('#edit_sort_order').val(data.sort_order);
            $('#edit_route').val(data.route);
            $('#edit_icon').val(data.icon);
            populateParentDropdown($('#edit_parent_id'), data.parent_id);
            $('#editMenuForm').attr('action', `/master/menus/${id}`);
            showModal(editModal);
        });
    });

    $('#editMenuForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: () => {
                table.ajax.reload();
                hideModal(editModal);
            },
            error: xhr => displayErrors(xhr.responseJSON.errors, 'edit')
        });
    });

    $(document).on('click', '.delete-button', function () {
        menuIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function () {
        if (menuIdToDelete) {
            $.ajax({
                url: `/master/menus/${menuIdToDelete}`,
                method: 'DELETE',
                success: () => {
                    table.ajax.reload();
                    hideModal(deleteModal);
                    menuIdToDelete = null;
                }
            });
        }
    });
});
</script>
@endpush
