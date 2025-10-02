@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Project Status Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Project Status Master</h2>
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
            <table id="projectStatusTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">#</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">
                            Project Status Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-start">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Project Status Modal --}}
<div id="addProjectStatusModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Project Status</h3>
            <form id="addProjectStatusForm" action="{{ route('projectStatus.store') }}" method="POST">
                @csrf
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Project Status Name</label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. In Progress" required>
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

{{-- Edit Project Status Modal --}}
<div id="editProjectStatusModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Project Status</h3>
            <form id="editProjectStatusForm" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Project Status Name</label>
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
<div id="deleteProjectStatusModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this project status?</p>
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
    div.dataTables_length label {
        font-size: 0.75rem;
        /* text-xs */
    }

    div.dataTables_length select {
        font-size: 0.75rem;
        /* text-xs */
        line-height: 1rem;
        /* compact */
        padding: 0.25rem 1.25rem 0.25rem 0.5rem;
        height: 1.875rem;
        /* ~30px, lebih kecil dari default */
        width: 4.5rem;
        /* cukup untuk 10/25/50 */
    }

    div.dataTables_filter label {
        font-size: 0.75rem;
        /* text-xs */
    }

    /* Kecilkan input Search DataTables */
    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="departmentsTable"] {
        font-size: 0.75rem;
        /* text-xs */
        line-height: 1rem;
        padding: 0.25rem 0.5rem;
        /* lebih rapat */
        height: 1.875rem;
        /* ~30px */
        width: 12rem;
        /* ~192px, lebih kecil dari default */
    }

    div.dataTables_info {
        font-size: 0.75rem;
        /* Ukuran teks kecil (text-xs) */
        padding-top: 0.8em;
        /* Sesuaikan padding agar sejajar dengan pagination */
    }
</style>
@push('scripts')
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Initialize DataTable
        const table = $('#projectStatusTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{{ route("projectStatus.data") }}',
                type: 'GET',
                data: function(d) {
                    d.search = d.search.value;
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
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
            order: [
                [1, 'asc']
            ],
            language: {
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No project statuses found.</div>'
            },
        });

        // Modal Handling
        const addModal = $('#addProjectStatusModal');
        const editModal = $('#editProjectStatusModal');
        const deleteModal = $('#deleteProjectStatusModal');
        const addButton = $('#add-button');
        const closeButtons = $('.close-modal-button');
        let projectStatusIdToDelete = null;

        function showModal(modal) {
            modal.removeClass('hidden').addClass('flex');
        }

        function hideModal(modal) {
            modal.addClass('hidden').removeClass('flex');
        }

        addButton.on('click', () => {
            $('#addProjectStatusForm')[0].reset();
            showModal(addModal);
        });

        closeButtons.on('click', () => {
            hideModal(addModal);
            hideModal(editModal);
            hideModal(deleteModal);
        });

        // Add Project Status
        $('#addProjectStatusForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const nameError = $('#add-name-error');
            nameError.addClass('hidden');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(addModal);
                        $('#addProjectStatusForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors && errors.name) {
                        nameError.text(errors.name[0]).removeClass('hidden');
                    }
                }
            });
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

        // Edit Project Status
        $(document).on('click', '.edit-button', function() {
            const id = $(this).data('id');
            const nameError = $('#edit-name-error');
            nameError.addClass('hidden');

            $.ajax({
                url: `/master/projectStatus/${id}`,
                method: 'GET',
                success: function(data) {
                    $('#edit_name').val(data.name);
                    $('#editProjectStatusForm').attr('action', `/master/projectStatus/${id}`);
                    showModal(editModal);
                }
            });
        });

        $('#editProjectStatusForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const nameError = $('#edit-name-error');
            nameError.addClass('hidden');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(editModal);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors && errors.name) {
                        nameError.text(errors.name[0]).removeClass('hidden');
                    }
                }
            });
        });

        // Delete Project Status
        $(document).on('click', '.delete-button', function() {
            projectStatusIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });

        $('#confirmDeleteButton').on('click', function() {
            if (projectStatusIdToDelete) {
                $.ajax({
                    url: `/master/projectStatus/${projectStatusIdToDelete}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(data) {
                        if (data.success) {
                            table.ajax.reload();
                            hideModal(deleteModal);
                            projectStatusIdToDelete = null;
                        } else {
                            alert('Error deleting project status.');
                        }
                    },
                    error: function() {
                        alert('Error deleting project status.');
                    }
                });
            }
        });
    });
</script>
@endpush