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
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="suppliersTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">Supplier Name</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code">Supplier Code</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="email">Email</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="phone">Phone</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="address">Address</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="is_active">Status</th>
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
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add New Supplier</h3>
            <form id="addSupplierForm" action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Name</label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. ABC Corp" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Code</label>
                    <input type="text" name="code" id="code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. ABC" required>
                    <p id="add-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Email</label>
                    <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. contact@abccorp.com">
                    <p id="add-email-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Phone</label>
                    <input type="text" name="phone" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. +1234567890">
                    <p id="add-phone-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Address</label>
                    <textarea name="address" id="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 123 Main St, City, Country"></textarea>
                    <p id="add-address-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:ring-primary-600 focus:border-primary-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600" checked>
                        <label for="is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
                    </div>
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

{{-- Edit Supplier Modal --}}
<div id="editSupplierModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Supplier</h3>
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Name</label>
                    <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Code</label>
                    <input type="text" name="code" id="edit_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Email</label>
                    <input type="email" name="email" id="edit_email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <p id="edit-email-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Phone</label>
                    <input type="text" name="phone" id="edit_phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <p id="edit-phone-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Address</label>
                    <textarea name="address" id="edit_address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea>
                    <p id="edit-address-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:ring-primary-600 focus:border-primary-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600">
                        <label for="edit_is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
                    </div>
                    <p id="edit-is_active-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Save Changes
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

    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="suppliersTable"] {
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

    input::placeholder,
    textarea::placeholder {
        text-align: left;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Initialize DataTable
        const table = $('#suppliersTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{{ route("suppliers.data") }}',
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
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        return data ?
                            '<span class="inline-block px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>' :
                            '<span class="inline-block px-3 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Inactive</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
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
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No suppliers found.</div>'
            },
            responsive: true,
            autoWidth: false,
        });

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
            $('#addSupplierForm')[0].reset();
            $('#is_active').prop('checked', true);
            showModal(addModal);
        });

        closeButtons.on('click', () => {
            hideModal(addModal);
            hideModal(editModal);
            hideModal(deleteModal);
        });

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
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
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
            const hasDarkClass = document.documentElement.classList.contains('dark');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = hasDarkClass || prefersDark;

            return isDark ? {
                mode: 'dark',
                bg: 'rgba(15, 23, 42, 0.94)',
                fg: '#E5E7EB',
                border: 'rgba(148, 163, 184, .22)',
                progress: 'rgba(255,255,255,.9)',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444',
                    warning: '#f59e0b',
                    info: '#60a5fa'
                }
            } : {
                mode: 'light',
                bg: 'rgba(255, 255, 255, 0.98)',
                fg: '#0f172a',
                border: 'rgba(15, 23, 42, .10)',
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

        // Add Supplier
        $('#addSupplierForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('[type="submit"]');
            const nameError = $('#add-name-error');
            const codeError = $('#add-code-error');
            const emailError = $('#add-email-error');
            const phoneError = $('#add-phone-error');
            const addressError = $('#add-address-error');
            const isActiveError = $('#add-is_active-error');
            nameError.addClass('hidden');
            codeError.addClass('hidden');
            emailError.addClass('hidden');
            phoneError.addClass('hidden');
            addressError.addClass('hidden');
            isActiveError.addClass('hidden');

            const formData = new FormData(this);
            formData.set('is_active', $('#is_active').is(':checked') ? '1' : '0');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(addModal);
                        $form[0].reset();
                        $('#is_active').prop('checked', true);
                        toastSuccess('Success', 'Supplier added successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to add supplier.');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) nameError.text(errors.name[0]).removeClass('hidden');
                        if (errors.code) codeError.text(errors.code[0]).removeClass('hidden');
                        if (errors.email) emailError.text(errors.email[0]).removeClass('hidden');
                        if (errors.phone) phoneError.text(errors.phone[0]).removeClass('hidden');
                        if (errors.address) addressError.text(errors.address[0]).removeClass('hidden');
                        if (errors.is_active) isActiveError.text(errors.is_active[0]).removeClass('hidden');
                    }
                    const msg = xhr.responseJSON?.message || 'Failed to add supplier.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // Edit Supplier
        $(document).on('click', '.edit-button', function() {
            const id = $(this).data('id');
            const nameError = $('#edit-name-error');
            const codeError = $('#edit-code-error');
            const emailError = $('#edit-email-error');
            const phoneError = $('#edit-phone-error');
            const addressError = $('#edit-address-error');
            const isActiveError = $('#edit-is_active-error');
            nameError.addClass('hidden');
            codeError.addClass('hidden');
            emailError.addClass('hidden');
            phoneError.addClass('hidden');
            addressError.addClass('hidden');
            isActiveError.addClass('hidden');

            $.ajax({
                url: `/master/suppliers/${id}`,
                method: 'GET',
                beforeSend: function() {
                    setButtonLoading($('.edit-button[data-id="' + id + '"]'), true, '');
                },
                success: function(data) {
                    $('#edit_name').val(data.name);
                    $('#edit_code').val(data.code);
                    $('#edit_email').val(data.email);
                    $('#edit_phone').val(data.phone);
                    $('#edit_address').val(data.address);
                    $('#edit_is_active').prop('checked', data.is_active);
                    $('#editSupplierForm').attr('action', `/master/suppliers/${id}`);
                    showModal(editModal);
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to fetch supplier data.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($('.edit-button[data-id="' + id + '"]'), false);
                }
            });
        });

        $('#editSupplierForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('[type="submit"]');
            const nameError = $('#edit-name-error');
            const codeError = $('#edit-code-error');
            const emailError = $('#edit-email-error');
            const phoneError = $('#edit-phone-error');
            const addressError = $('#edit-address-error');
            const isActiveError = $('#edit-is_active-error');
            nameError.addClass('hidden');
            codeError.addClass('hidden');
            emailError.addClass('hidden');
            phoneError.addClass('hidden');
            addressError.addClass('hidden');
            isActiveError.addClass('hidden');

            const formData = new FormData(this);
            formData.set('is_active', $('#edit_is_active').is(':checked') ? '1' : '0');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(editModal);
                        toastSuccess('Success', 'Supplier updated successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to update supplier.');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) nameError.text(errors.name[0]).removeClass('hidden');
                        if (errors.code) codeError.text(errors.code[0]).removeClass('hidden');
                        if (errors.email) emailError.text(errors.email[0]).removeClass('hidden');
                        if (errors.phone) phoneError.text(errors.phone[0]).removeClass('hidden');
                        if (errors.address) addressError.text(errors.address[0]).removeClass('hidden');
                        if (errors.is_active) isActiveError.text(errors.is_active[0]).removeClass('hidden');
                    }
                    const msg = xhr.responseJSON?.message || 'Failed to update supplier.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // Delete Supplier
        $(document).on('click', '.delete-button', function() {
            supplierIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });

        $('#confirmDeleteButton').on('click', function() {
            if (!supplierIdToDelete) return;
            const $btn = $(this);

            $.ajax({
                url: `/master/suppliers/${supplierIdToDelete}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Deleting...');
                    setFormBusy($('#deleteSupplierModal'), true);
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        supplierIdToDelete = null;
                        toastSuccess('Success', 'Supplier deleted successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to delete supplier.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to delete supplier.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($('#deleteSupplierModal'), false);
                }
            });
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
    });
</script>
@endpush