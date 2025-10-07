@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Product Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Product Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="productsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="model">Model</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="part_no">Part No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="part_name">Part Name</th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="addProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Product</h3>
            <form id="addProductForm" action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model</label>
                    <select name="model_id" id="model_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <option value="">Select Model</option>
                    </select>
                    <p id="add-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="part_no" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part No</label>
                    <input type="text" name="part_no" id="part_no" maxlength="20" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 123-ABC-45" required>
                    <p id="add-part_no-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="part_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Name</label>
                    <input type="text" name="part_name" id="part_name" maxlength="50" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Bracket Assembly" required>
                    <p id="add-part_name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
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

<div id="editProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Product</h3>
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model</label>
                    <select name="model_id" id="edit_model_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <option value="">Select Model</option>
                    </select>
                    <p id="edit-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_part_no" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part No</label>
                    <input type="text" name="part_no" id="edit_part_no" maxlength="20" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-part_no-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_part_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Name</label>
                    <input type="text" name="part_name" id="edit_part_name" maxlength="50" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-part_name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
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

<div id="deleteProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this product?</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        const isDark = () => document.documentElement.classList.contains('dark');

        function toast(icon, title) {
            const dark = isDark();
            Swal.fire({
                toast: true,
                icon,
                title,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
                background: dark ? '#1f2937' : '#ffffff',
                color: dark ? '#f9fafb' : '#111827',
                didOpen: el => {
                    const bar = el.querySelector('.swal2-timer-progress-bar');
                    if (bar) bar.style.background = dark ? '#10b981' : '#3b82f6';
                }
            });
        }

        const spinnerSVG = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>`;

        function beginBusy($btn, text = 'Processing...') {
            if ($btn.data('busy')) return false;
            $btn.data('busy', true);
            if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
            $btn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed');
            $btn.html(`<span class="inline-flex items-center">${spinnerSVG}${text}</span>`);
            return true;
        }

        function endBusy($btn) {
            const orig = $btn.data('orig-html');
            if (orig) $btn.html(orig);
            $btn.prop('disabled', false).removeClass('opacity-75 cursor-not-allowed');
            $btn.data('busy', false);
        }
        $(document).on('click', 'button', function(e) {
            const $b = $(this);
            if ($b.data('busy')) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });

        $('#model_id').select2({
            dropdownParent: $('#addProductModal'),
            width: '100%'
        });
        $('#edit_model_id').select2({
            dropdownParent: $('#editProductModal'),
            width: '100%'
        });

        const table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("products.data") }}',
                type: 'GET',
                data: d => {
                    d.search = d.search.value;
                }
            },
            columns: [{
                    data: null,
                    render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                },
                {
                    data: 'model_name',
                    name: 'model_name'
                },
                {
                    data: 'part_no',
                    name: 'part_no'
                },
                {
                    data: 'part_name',
                    name: 'part_name'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: row => `
                        <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}">
                            <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
                        </button>
                        <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}">
                            <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                        </button>`
                }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [
                [2, 'asc']
            ],
            language: {
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No products found.</div>'
            },
        });

        const addModal = $('#addProductModal');
        const editModal = $('#editProductModal');
        const deleteModal = $('#deleteProductModal');
        const addButton = $('#add-button');
        const closeButtons = $('.close-modal-button');
        let productIdToDelete = null;

        function showModal(modal) {
            modal.removeClass('hidden').addClass('flex');
        }

        function hideModal(modal) {
            modal.addClass('hidden').removeClass('flex');
        }

        function populateModelDropdown($select, selectedId = null) {
            $.get('{{ route("products.getModels") }}', function(models) {
                $select.empty().append('<option value="">Select Model</option>');
                models.forEach(function(m) {
                    const label = m.label || [m.code, m.name].filter(Boolean).join(' — ');
                    $select.append(`<option value="${m.id}"${selectedId == m.id ? ' selected' : ''}>${label}</option>`);
                });
                $select.trigger('change');
            }).fail(() => toast('error', 'Failed to load models'));
        }

        addButton.on('click', function() {
            const $btn = $(this);
            if (!beginBusy($btn, 'Opening...')) return;
            $('#addProductForm')[0].reset();
            $('#add-model_id-error,#add-part_no-error,#add-part_name-error').addClass('hidden').text('');
            populateModelDropdown($('#model_id'));
            $('#model_id').val(null).trigger('change');
            showModal(addModal);
            setTimeout(() => endBusy($btn), 150);
        });

        closeButtons.on('click', function() {
            const $btn = $(this);
            if (!beginBusy($btn, 'Closing...')) return;
            hideModal(addModal);
            hideModal(editModal);
            hideModal(deleteModal);
            setTimeout(() => endBusy($btn), 150);
        });

        $('#addProductForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type=submit]');
            if (!beginBusy($btn, 'Saving...')) return;

            const formData = new FormData(this);
            const eModel = $('#add-model_id-error'),
                eNo = $('#add-part_no-error'),
                eName = $('#add-part_name-error');
            eModel.addClass('hidden');
            eNo.addClass('hidden');
            eName.addClass('hidden');

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
                        table.ajax.reload(null, false);
                        hideModal(addModal);
                        $('#addProductForm')[0].reset();
                        $('#model_id').val(null).trigger('change');
                        toast('success', 'Product created');
                    } else {
                        toast('error', data.message || 'Failed to create');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    if (errors.model_id) eModel.text(errors.model_id[0]).removeClass('hidden');
                    if (errors.part_no) eNo.text(errors.part_no[0]).removeClass('hidden');
                    if (errors.part_name) eName.text(errors.part_name[0]).removeClass('hidden');
                    toast('error', 'Validation error');
                },
                complete: function() {
                    endBusy($btn);
                }
            });
        });

        $(document).on('click', '.edit-button', function() {
            const $btn = $(this);
            if (!beginBusy($btn, '')) return;

            const id = $btn.data('id');
            $('#edit-model_id-error,#edit-part_no-error,#edit-part_name-error').addClass('hidden').text('');

            $.ajax({
                url: `/master/products/${id}`,
                method: 'GET',
                success: function(data) {
                    populateModelDropdown($('#edit_model_id'), data.model_id);
                    $('#edit_part_no').val(data.part_no);
                    $('#edit_part_name').val(data.part_name);
                    $('#editProductForm').attr('action', `/master/products/${id}`);
                    showModal(editModal);
                },
                error: function() {
                    toast('error', 'Failed to load product');
                },
                complete: function() {
                    endBusy($btn);
                }
            });
        });

        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type=submit]');
            if (!beginBusy($btn, 'Updating...')) return;

            const formData = new FormData(this);
            const eModel = $('#edit-model_id-error'),
                eNo = $('#edit-part_no-error'),
                eName = $('#edit-part_name-error');
            eModel.addClass('hidden');
            eNo.addClass('hidden');
            eName.addClass('hidden');

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
                        table.ajax.reload(null, false);
                        hideModal(editModal);
                        toast('success', 'Product updated');
                    } else {
                        toast('error', data.message || 'Failed to update');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    if (errors.model_id) eModel.text(errors.model_id[0]).removeClass('hidden');
                    if (errors.part_no) eNo.text(errors.part_no[0]).removeClass('hidden');
                    if (errors.part_name) eName.text(errors.part_name[0]).removeClass('hidden');
                    toast('error', 'Validation error');
                },
                complete: function() {
                    endBusy($btn);
                }
            });
        });

        $(document).on('click', '.delete-button', function() {
            const $btn = $(this);
            if (!beginBusy($btn, '')) return;
            productIdToDelete = $(this).data('id');
            showModal(deleteModal);
            setTimeout(() => endBusy($btn), 150);
        });

        $('#confirmDeleteButton').on('click', function() {
            if (!productIdToDelete) return;
            const $btn = $(this);
            if (!beginBusy($btn, 'Deleting...')) return;

            $.ajax({
                url: `/master/products/${productIdToDelete}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload(null, false);
                        hideModal(deleteModal);
                        productIdToDelete = null;
                        toast('success', 'Product deleted');
                    } else {
                        toast('error', data.message || 'Failed to delete');
                    }
                },
                error: function() {
                    toast('error', 'Error deleting product');
                },
                complete: function() {
                    endBusy($btn);
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
    });
</script>
@endpush