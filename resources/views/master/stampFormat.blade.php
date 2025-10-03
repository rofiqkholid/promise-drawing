@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Stamp Format Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Stamp Format Master</h2>
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
            <table id="stampFormatsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="prefix">
                            Prefix
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="suffix">
                            Suffix
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="is_active">
                            Is Active
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Stamp Format Modal --}}
<div id="addStampFormatModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Stamp Format</h3>
            <form id="addStampFormatForm" action="{{ route('stampFormat.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="mb-4">
                        <label for="prefix" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Prefix</label>
                        <input type="text" name="prefix" id="prefix" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 4L45W" required>
                        <p id="add-prefix-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                    <div class="mb-4">
                        <label for="suffix" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Suffix</label>
                        <input type="text" name="suffix" id="suffix" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 4L45W" required>
                        <p id="add-suffix-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                    <div class="flex items-center justify-start mb-4">
                        <input id="is_active" type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Is Active</label>
                    </div>
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

{{-- Edit Stamp Format Modal --}}
<div id="editStampFormatModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Stamp Format</h3>
            <form id="editStampFormatForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="mb-4">
                        <label for="edit_prefix" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Prefix</label>
                        <input type="text" name="prefix" id="edit_prefix" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <p id="edit-prefix-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                    <div class="mb-4">
                        <label for="edit_suffix" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Suffix</label>
                        <input type="text" name="suffix" id="edit_suffix" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <p id="edit-suffix-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                    </div>
                    <div class="flex items-center justify-start mb-4">
                        <input id="edit_is_active" type="checkbox" name="is_active" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="edit_is_active" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Is Active</label>
                    </div>
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
<div id="deleteStampFormatModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this stamp format?</p>
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
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Initialize DataTable
        const table = $('#stampFormatsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("stampFormat.data") }}', // Updated route
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
                    data: 'prefix',
                    name: 'prefix'
                }, // Updated column
                {
                    data: 'suffix',
                    name: 'suffix'
                }, // Updated column
                {
                    data: 'is_active',
                    name: 'is_active',
                    orderable: true,
                    searchable: false,
                    className: 'px-6 py-4 whitespace-nowrap text-center',
                    render: function(data, type, row) {
                        const isActive = data == 1;
                        const badgeClass = isActive ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                        const text = isActive ? 'Active' : 'Inactive';
                        return `<span class="px-2 py-0.5 font-medium rounded-full text-xs inline-flex items-center justify-center ${badgeClass}">${text}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'px-6 py-4 whitespace-nowrap text-center',
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
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No stamp formats found.</div>'
            },
        });

        // Modal Handling
        const addModal = $('#addStampFormatModal');
        const editModal = $('#editStampFormatModal');
        const deleteModal = $('#deleteStampFormatModal');
        const addButton = $('#add-button');
        const closeButtons = $('.close-modal-button');
        let stampFormatIdToDelete = null;

        function showModal(modal) {
            modal.removeClass('hidden').addClass('flex');
        }

        function hideModal(modal) {
            modal.addClass('hidden').removeClass('flex');
        }

        addButton.on('click', () => {
            $('#addStampFormatForm')[0].reset();
            $('#is_active').prop('checked', true); // Ensure 'Is Active' is checked by default for add
            showModal(addModal);
        });

        closeButtons.on('click', () => {
            hideModal(addModal);
            hideModal(editModal);
            hideModal(deleteModal);
        });

        // Fix DataTables search/length focus styles (retained from original request)
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

        // Add Stamp Format
        $('#addStampFormatForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const prefixError = $('#add-prefix-error');
            const suffixError = $('#add-suffix-error');
            prefixError.addClass('hidden');
            suffixError.addClass('hidden');

            // Manually add unchecked 'is_active' if not present
            if (!$('#is_active').is(':checked')) {
                formData.set('is_active', '0');
            }

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
                        $('#addStampFormatForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.prefix) {
                            prefixError.text(errors.prefix[0]).removeClass('hidden');
                        }
                        if (errors.suffix) {
                            suffixError.text(errors.suffix[0]).removeClass('hidden');
                        }
                    }
                }
            });
        });

        // Edit Stamp Format - Fetch Data
        $(document).on('click', '.edit-button', function() {
            const id = $(this).data('id');
            const prefixError = $('#edit-prefix-error');
            const suffixError = $('#edit-suffix-error');
            prefixError.addClass('hidden');
            suffixError.addClass('hidden');

            $.ajax({
                url: `/master/stampFormat/${id}`, // Updated URL
                method: 'GET',
                success: function(data) {
                    $('#edit_prefix').val(data.prefix);
                    $('#edit_suffix').val(data.suffix);
                    // Set checkbox state based on is_active (1 or 0)
                    $('#edit_is_active').prop('checked', data.is_active == 1);
                    $('#editStampFormatForm').attr('action', `/master/stampFormat/${id}`); // Updated URL
                    showModal(editModal);
                }
            });
        });

        // Edit Stamp Format - Submit Update
        $('#editStampFormatForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(this);
            const prefixError = $('#edit-prefix-error');
            const suffixError = $('#edit-suffix-error');
            prefixError.addClass('hidden');
            suffixError.addClass('hidden');

            // Manually handle PUT/PATCH request method
            formData.append('_method', 'PUT');

            // Manually add unchecked 'is_active' if not present
            if (!$('#edit_is_active').is(':checked')) {
                formData.set('is_active', '0');
            } else {
                // Ensure checked sends '1'
                formData.set('is_active', '1');
            }

            // Remove the existing PUT method field to prevent double submission
            formData.delete('_method');

            $.ajax({
                url: form.attr('action'),
                method: 'POST', // Use POST method to send PUT data via FormData
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
                    if (errors) {
                        if (errors.prefix) {
                            prefixError.text(errors.prefix[0]).removeClass('hidden');
                        }
                        if (errors.suffix) {
                            suffixError.text(errors.suffix[0]).removeClass('hidden');
                        }
                    }
                }
            });
        });

        // Delete Stamp Format - Confirmation
        $(document).on('click', '.delete-button', function() {
            stampFormatIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });

        // Delete Stamp Format - Execute
        $('#confirmDeleteButton').on('click', function() {
            if (stampFormatIdToDelete) {
                $.ajax({
                    url: `/master/stampFormat/${stampFormatIdToDelete}`, // Updated URL
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(data) {
                        if (data.success) {
                            table.ajax.reload();
                            hideModal(deleteModal);
                            stampFormatIdToDelete = null;
                        } else {
                            // Using custom modal/message box instead of alert()
                            console.error('Error deleting stamp format.');
                        }
                    },
                    error: function() {
                        // Using custom modal/message box instead of alert()
                        console.error('Error deleting stamp format.');
                    }
                });
            }
        });
    });
</script>
@endpush
