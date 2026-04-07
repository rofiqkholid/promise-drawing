@extends('layouts.app')

@section('header-title', 'Menu Management')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
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
                        <option value="">No Parent</option>
                    </select>
                    <p id="add-parent_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800" checked>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Is Active</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_visible" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800" checked>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Is Visible</span>
                    </label>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                        <option value="">No Parent</option>
                    </select>
                    <p id="edit-parent_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800">
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Is Active</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_visible" id="edit_is_visible" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800">
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Is Visible</span>
                    </label>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

@push('style')
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Initialize Select2 for parent menu dropdowns
    $('#parent_id').select2({
        dropdownParent: $('#addMenuModal'),
        width: '100%'
    });
    $('#edit_parent_id').select2({
        dropdownParent: $('#editMenuModal'),
        width: '100%'
    });

    // Initialize DataTable
    const table = $('#menuTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("menus.data") }}',
            type: 'GET',
            data: function (d) {
                d.search = d.search.value;
            }
        },
        order: [[5, 'asc']],
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                className: 'text-center'
            },
            {
                data: 'title',
                name: 'title',
                render: function (data) {
                    return data || '-';
                }
            },
            {
                data: 'parent_name',
                name: 'parent.title',
                render: function (data) {
                    return data || '-';
                }
            },
            {
                data: 'route',
                name: 'route',
                render: function (data) {
                    return data ? `<code>${data}</code>` : '-';
                }
            },
            {
                data: 'icon',
                name: 'icon',
                className: 'text-center',
                render: function (data) {
                    return data ? `<i class="${data} text-lg"></i>` : '-';
                }
            },
            {
                data: 'sort_order',
                name: 'sort_order',
                className: 'text-center',
                render: function (data) {
                    return `<span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/70 dark:text-blue-200 rounded-full px-3 py-1"><i class="fa-solid fa-arrow-down-short-wide"></i> ${data}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `
                        <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}"><i class="fa-solid fa-pen-to-square fa-lg m-2"></i></button>
                        <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}"><i class="fa-solid fa-trash-can fa-lg m-2"></i></button>
                    `;
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        responsive: true,
        autoWidth: false,
        scrollX: true,
        language: {
            emptyTable: '<div class="text-gray-500 dark:text-gray-400">No menus found.</div>'
        }
    });

    // Fix DataTables input/select focus styles
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
    const addModal = $('#addMenuModal');
    const editModal = $('#editMenuModal');
    const deleteModal = $('#deleteMenuModal');
    const addButton = $('#add-button');
    const closeButtons = $('.close-modal-button');
    let menuIdToDelete = null;

    // Helper: Show modal
    function showModal(modal) {
        modal.removeClass('hidden').addClass('flex');
    }

    // Helper: Hide modal
    function hideModal(modal) {
        modal.addClass('hidden').removeClass('flex');
    }

    // Helper: Button loading state
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

    // Helper: Disable/enable form fields during request
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

    // Helper: Reset errors
    function resetErrors(prefix) {
        $(`#${prefix}-title-error`).addClass('hidden').text('');
        $(`#${prefix}-sort_order-error`).addClass('hidden').text('');
        $(`#${prefix}-route-error`).addClass('hidden').text('');
        $(`#${prefix}-icon-error`).addClass('hidden').text('');
        $(`#${prefix}-parent_id-error`).addClass('hidden').text('');
    }

    // Helper: Display errors
    function displayErrors(errors, prefix) {
        resetErrors(prefix);
        for (const key in errors) {
            $(`#${prefix}-${key}-error`).text(errors[key][0]).removeClass('hidden');
        }
    }

    // Populate Parent Menu Dropdown
    function populateParentDropdown($select, selectedId = null) {
        $.ajax({
            url: '{{ route("menus.getParents") }}',
            method: 'GET',
            beforeSend: function() {
                $select.prop('disabled', true);
            },
            success: function (parents) {
                $select.empty().append('<option value="">No Parent</option>');
                parents.forEach(parent => {
                    const selected = parent.id == selectedId ? 'selected' : '';
                    $select.append(`<option value="${parent.id}" ${selected}>${parent.title}</option>`);
                });
                $select.trigger('change');
            },
            error: function (xhr) {
                toastError('Error', xhr.responseJSON?.message || 'Failed to load parent menus.');
            },
            complete: function() {
                $select.prop('disabled', false);
            }
        });
    }

    // Add Button Click
    addButton.on('click', () => {
        $('#addMenuForm')[0].reset();
        resetErrors('add');
        populateParentDropdown($('#parent_id'));
        $('#parent_id').val('').trigger('change');
        showModal(addModal);
    });

    // Close Modal Buttons
    closeButtons.on('click', () => {
        hideModal(addModal);
        hideModal(editModal);
        hideModal(deleteModal);
    });

    // Add Menu
    $('#addMenuForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('[type="submit"]');
        resetErrors('add');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            beforeSend: function() {
                setButtonLoading($btn, true, 'Saving...');
                setFormBusy($form, true);
            },
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(addModal);
                    $form[0].reset();
                    $('#parent_id').val('').trigger('change');
                    toastSuccess('Success', 'Menu added successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to add menu.');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    displayErrors(errors, 'add');
                }
                const msg = xhr.responseJSON?.message || 'Failed to add menu.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($btn, false);
                setFormBusy($form, false);
            }
        });
    });

    // Edit Menu
    $(document).on('click', '.edit-button', function () {
        const id = $(this).data('id');
        resetErrors('edit');

        $.ajax({
            url: `/master/menus/${id}`,
            method: 'GET',
            beforeSend: function() {
                setButtonLoading($('.edit-button[data-id="' + id + '"]'), true, '');
            },
            success: function (data) {
                $('#edit_title').val(data.title);
                $('#edit_sort_order').val(data.sort_order);
                $('#edit_route').val(data.route || '');
                $('#edit_icon').val(data.icon || '');
                $('#edit_is_active').prop('checked', !!data.is_active);
                $('#edit_is_visible').prop('checked', !!data.is_visible);
                populateParentDropdown($('#edit_parent_id'), data.parent_id);
                $('#editMenuForm').attr('action', `/master/menus/${id}`);
                showModal(editModal);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to fetch menu data.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($('.edit-button[data-id="' + id + '"]'), false);
            }
        });
    });

    // Submit Edit Form
    $('#editMenuForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('[type="submit"]');
        resetErrors('edit');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            beforeSend: function() {
                setButtonLoading($btn, true, 'Saving...');
                setFormBusy($form, true);
            },
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(editModal);
                    toastSuccess('Success', 'Menu updated successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to update menu.');
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    displayErrors(errors, 'edit');
                }
                const msg = xhr.responseJSON?.message || 'Failed to update menu.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($btn, false);
                setFormBusy($form, false);
            }
        });
    });

    // Delete Menu
    $(document).on('click', '.delete-button', function () {
        menuIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function () {
        if (!menuIdToDelete) return;
        const $btn = $(this);

        $.ajax({
            url: `/master/menus/${menuIdToDelete}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            beforeSend: function() {
                setButtonLoading($btn, true, 'Deleting...');
                setFormBusy($('#deleteMenuModal'), true);
            },
            success: function (data) {
                if (data.success) {
                    table.ajax.reload();
                    hideModal(deleteModal);
                    menuIdToDelete = null;
                    toastSuccess('Success', 'Menu deleted successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to delete menu.');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to delete menu.';
                toastError('Error', msg);
            },
            complete: function() {
                setButtonLoading($btn, false);
                setFormBusy($('#deleteMenuModal'), false);
            }
        });
    });
});
</script>
@endpush
