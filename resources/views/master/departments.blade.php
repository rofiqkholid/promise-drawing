@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Department Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Department Master</h2>
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
            <table id="departmentsTable" class="min-w-full w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">
                            Department Name
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code">
                            Department Code
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Department Modal --}}
<div id="addDepartmentModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Department</h3>
            <form id="addDepartmentForm" action="{{ route('departments.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Department Name</label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Engineering" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Department Code</label>
                    <input type="text" name="code" id="code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. ENG" required>
                    <p id="add-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 w-full">
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

{{-- Edit Department Modal --}}
<div id="editDepartmentModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Department</h3>
            <form id="editDepartmentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Department Name</label>
                    <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Department Code</label>
                    <input type="text" name="code" id="edit_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 w-full">
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
<div id="deleteDepartmentModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this department?</p>
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
    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="departmentsTable"] {
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
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Initialize DataTable
        const table = $('#departmentsTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{{ route("departments.data") }}',
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
            order: [
                [1, 'asc']
            ],
            language: {
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No departments found.</div>'
            },
            responsive: true,
            autoWidth: false,
        });

        // Modal Handling
        const addModal = $('#addDepartmentModal');
        const editModal = $('#editDepartmentModal');
        const deleteModal = $('#deleteDepartmentModal');
        const addButton = $('#add-button');
        const closeButtons = $('.close-modal-button');
        let departmentIdToDelete = null;

        function showModal(modal) {
            modal.removeClass('hidden').addClass('flex');
        }

        function hideModal(modal) {
            modal.addClass('hidden').removeClass('flex');
        }

        addButton.on('click', () => showModal(addModal));
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
            error:   '#ef4444',
            warning: '#f59e0b',
            info:    '#60a5fa'
        }
        } : {
        mode: 'light',
        bg: 'rgba(255, 255, 255, 0.98)',
        fg: '#0f172a',
        border: 'rgba(15, 23, 42, .10)',
        progress: 'rgba(15,23,42,.8)',
        icon: {
            success: '#16a34a',
            error:   '#dc2626',
            warning: '#d97706',
            info:    '#2563eb'
        }
        };
    }

    const BaseToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2600,
        timerProgressBar: true,
        showClass: { popup: 'swal2-animate-toast-in' },
        hideClass: { popup: 'swal2-animate-toast-out' },
        didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function renderToast({ icon = 'success', title = 'Success', text = '' } = {}) {
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
        renderToast({ icon: 'success', title, text });
    }
    function toastError(title = 'Gagal', text = 'Terjadi kesalahan.') {
        BaseToast.update({ timer: 3400 });
        renderToast({ icon: 'error', title, text });
        BaseToast.update({ timer: 2600 });
    }
    function toastWarning(title = 'Peringatan', text = 'Periksa kembali data Anda.') {
        renderToast({ icon: 'warning', title, text });
    }
    function toastInfo(title = 'Informasi', text = '') {
        renderToast({ icon: 'info', title, text });
    }

    window.toastSuccess = toastSuccess;
    window.toastError = toastError;
    window.toastWarning = toastWarning;
    window.toastInfo = toastInfo;

        // Add Department
        $('#addDepartmentForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('[type="submit"]');
            const nameError = $('#add-name-error');
            const codeError = $('#add-code-error');
            nameError.addClass('hidden');
            codeError.addClass('hidden');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: new FormData(this),
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
                        toastSuccess('Success', 'Department added successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to add department.');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) nameError.text(errors.name[0]).removeClass('hidden');
                        if (errors.code) codeError.text(errors.code[0]).removeClass('hidden');
                    }
                    const msg = xhr.responseJSON?.message || 'Failed to add department.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // Edit Department
        $(document).on('click', '.edit-button', function() {
            const id = $(this).data('id');
            const nameError = $('#edit-name-error');
            const codeError = $('#edit-code-error');
            nameError.addClass('hidden');
            codeError.addClass('hidden');

            $.ajax({
                url: `/master/departments/${id}`,
                method: 'GET',
                beforeSend: function() {
                    setButtonLoading($('.edit-button[data-id="' + id + '"]'), true, '');
                },
                success: function(data) {
                    $('#edit_name').val(data.name);
                    $('#edit_code').val(data.code);
                    $('#editDepartmentForm').attr('action', `/master/departments/${id}`);
                    showModal(editModal);
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to fetch department data.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($('.edit-button[data-id="' + id + '"]'), false);
                }
            });
        });

        $('#editDepartmentForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('[type="submit"]');
            const nameError = $('#edit-name-error');
            const codeError = $('#edit-code-error');
            nameError.addClass('hidden');
            codeError.addClass('hidden');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: new FormData(this),
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
                        toastSuccess('Success', 'Department updated successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to update department.');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) nameError.text(errors.name[0]).removeClass('hidden');
                        if (errors.code) codeError.text(errors.code[0]).removeClass('hidden');
                    }
                    const msg = xhr.responseJSON?.message || 'Failed to update department.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // Delete Department
        $(document).on('click', '.delete-button', function() {
            departmentIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });

        $('#confirmDeleteButton').on('click', function() {
            if (!departmentIdToDelete) return;
            const $btn = $(this);

            $.ajax({
                url: `/master/departments/${departmentIdToDelete}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Deleting...');
                    setFormBusy($('#deleteDepartmentModal'), true);
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        departmentIdToDelete = null;
                        toastSuccess('Success', 'Department deleted successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to delete department.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to delete department.';
                    toastError('Error', msg);
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($('#deleteDepartmentModal'), false);
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
