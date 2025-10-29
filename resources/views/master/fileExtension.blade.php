@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'File Extension Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">File Extension Master</h2>
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
            <table id="fileExtensionsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">
                            File Extension Name
                        </th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code">
                            File Extension Code
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">Icon</th>
                        <th scope="col" class="px-6 py-3 text-center">Is Viewer</th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add File Extension Modal --}}
<div id="addFileExtensionModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New File Extension</h3>
            <form id="addFileExtensionForm" action="{{ route('fileExtensions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">File Extension Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Portable Document Format" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">File Extension Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" id="code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. PDF" required>
                    <p id="add-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Categories</label>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="categories[]" value="2D" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">2D</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="categories[]" value="3D" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">3D</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="categories[]" value="ECN" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">ECN</span>
                        </label>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="icon" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">
                        Icon
                    </label>
                    <input type="file" name="icon" id="icon"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        placeholder="e.g. fa-solid fa-file-pdf">
                    <p id="add-icon-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_viewer" id="is_viewer" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Is Viewer?</span>
                    </label>
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

{{-- Edit File Extension Modal --}}
<div id="editFileExtensionModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit File Extension</h3>
            <form id="editFileExtensionForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">File Extension Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">File Extension Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" id="edit_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Categories</label>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="categories[]" value="2D" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">2D</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="categories[]" value="3D" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">3D</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="categories[]" value="ECN" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">ECN</span>
                        </label>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="edit_icon" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">
                        Icon
                    </label>
                    <input type="file" name="icon" id="edit_icon"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <p id="edit-icon-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_viewer" id="edit_is_viewer" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Is Viewer?</span>
                    </label>
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
<div id="deleteFileExtensionModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this file extension?</p>
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
    input[type="search"][aria-controls="fileExtensionsTable"] {
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // ========= Toast utils =========
        function detectTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            return isDark ? {
                bg: 'rgba(30,41,59,0.95)',
                fg: '#E5E7EB',
                border: 'rgba(71,85,105,.5)',
                progress: 'rgba(255,255,255,.9)',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444',
                    warning: '#f59e0b',
                    info: '#3b82f6'
                }
            } : {
                bg: 'rgba(255,255,255,.98)',
                fg: '#0f172a',
                border: 'rgba(226,232,240,1)',
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
            didOpen: (t) => {
                t.addEventListener('mouseenter', Swal.stopTimer);
                t.addEventListener('mouseleave', Swal.resumeTimer);
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
                    popup: 'swal2-toast border'
                },
                didOpen: (toast) => {
                    const bar = toast.querySelector('.swal2-timer-progress-bar');
                    if (bar) bar.style.background = t.progress;
                    const popup = toast.querySelector('.swal2-popup');
                    if (popup) popup.style.borderColor = t.border;
                }
            });
        }

        function toastSuccess(t = 'Berhasil', m = 'Operasi berhasil dijalankan.') {
            renderToast({
                icon: 'success',
                title: t,
                text: m
            });
        }

        function toastError(t = 'Gagal', m = 'Terjadi kesalahan.') {
            BaseToast.update({
                timer: 3400
            });
            renderToast({
                icon: 'error',
                title: t,
                text: m
            });
            BaseToast.update({
                timer: 2600
            });
        }

        // ========= Buttons & forms state =========
        function setButtonLoading($btn, isLoading, loadingText = 'Processing...') {
            if (!$btn || $btn.length === 0) return;
            if (isLoading) {
                if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
                $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').html(`
        <span class="inline-flex items-center gap-2">
          <svg aria-hidden="true" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>${loadingText}
        </span>`);
            } else {
                const orig = $btn.data('orig-html');
                if (orig) $btn.html(orig);
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            }
        }

        function setFormBusy($form, busy) {
            $form.find('input,select,textarea,button').prop('disabled', busy);
        }

        // ========= Modals =========
        const addModal = $('#addFileExtensionModal');
        const editModal = $('#editFileExtensionModal');
        const deleteModal = $('#deleteFileExtensionModal');
        const addButton = $('#add-button');
        let fileExtensionIdToDelete = null;

        function showModal(m) {
            m.removeClass('hidden').addClass('flex');
        }

        function hideModal(m) {
            m.addClass('hidden').removeClass('flex');
        }

        $('.close-modal-button').on('click', () => {
            hideModal(addModal);
            hideModal(editModal);
            hideModal(deleteModal);
        });
        addButton.on('click', () => {
            $('#addFileExtensionForm')[0].reset();
            $('#add_icon_preview_wrap').remove();
            showModal(addModal);
        });

        // ========= DataTables (guard agar tidak reinit) =========
        let table;
        if ($.fn.DataTable.isDataTable('#fileExtensionsTable')) {
            table = $('#fileExtensionsTable').DataTable(); // reuse
        } else {
            table = $('#fileExtensionsTable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '{{ route("fileExtensions.data") }}',
                    type: 'GET',
                },
                columns: [{
                        data: null,
                        render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
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
                        data: 'icon_src',
                        name: 'icon_src', // nama apa saja, tidak dipakai sorting server
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function(src) {
                            return src ? `<img src="${src}" alt="icon" class="inline-block w-6 h-6">` : '-';
                        }
                    },

                    {
                        data: 'is_viewer',
                        name: 'is_viewer',
                        className: 'text-center',
                        render: (v) => v ? '<i class="fa-solid fa-check text-green-500"></i>' : '<i class="fa-solid fa-times text-red-500"></i>'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (d, t, row) => `
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
                    [1, 'asc']
                ],
                language: {
                    emptyTable: '<div class="text-gray-500 dark:text-gray-400">No file extensions found.</div>'
                },
                responsive: true,
                autoWidth: false,
            });
        }

        // ========= Preview ADD =========
        $(document).on('change', '#icon', function(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) {
                $('#add_icon_preview_wrap').remove();
                return;
            }
            const reader = new FileReader();
            reader.onload = function(ev) {
                if ($('#add_icon_preview_wrap').length === 0) {
                    $('#icon').after(`
          <div id="add_icon_preview_wrap" class="mt-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Preview:</span>
            <img id="add_icon_preview_img" class="inline-block w-8 h-8 align-middle rounded border border-gray-200 dark:border-gray-700 ml-2" />
          </div>`);
                }
                $('#add_icon_preview_img').attr('src', ev.target.result);
            };
            reader.readAsDataURL(file);
        });

        // ========= Preview EDIT (current & on change) =========
       function setEditIconPreview(data) {
  if ($('#edit_icon_preview_wrap').length === 0) {
    $('#edit_icon').after(`
      <div id="edit_icon_preview_wrap" class="mt-2" style="display:none;">
        <span class="text-xs text-gray-500 dark:text-gray-400">Current:</span>
        <img id="edit_icon_preview_img" class="inline-block w-8 h-8 align-middle rounded border border-gray-200 dark:border-gray-700 ml-2" />
      </div>`);
  }
  const $wrap = $('#edit_icon_preview_wrap');
  const $img  = $('#edit_icon_preview_img');

  const src = data.icon_src; // <- pakai accessor
  if (src) {
    $img.attr('src', src);
    $wrap.show();
  } else {
    $wrap.hide();
  }
}


        $(document).on('change', '#edit_icon', function(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                if ($('#edit_icon_preview_wrap').length === 0) {
                    $('#edit_icon').after(`
          <div id="edit_icon_preview_wrap" class="mt-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Preview:</span>
            <img id="edit_icon_preview_img" class="inline-block w-8 h-8 align-middle rounded border border-gray-200 dark:border-gray-700 ml-2" />
          </div>`);
                }
                $('#edit_icon_preview_img').attr('src', ev.target.result);
                $('#edit_icon_preview_wrap').show();
            };
            reader.readAsDataURL(file);
        });

        // ========= Submit ADD =========
        $('#addFileExtensionForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this),
                $btn = $form.find('[type="submit"]');
            $('#add-name-error, #add-code-error').addClass('hidden');
            const formData = new FormData(this);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: (resp) => {
                    if (resp.success) {
                        table.ajax.reload();
                        hideModal(addModal);
                        $form[0].reset();
                        $('#add_icon_preview_wrap').remove();
                        toastSuccess('Success', 'File extension added successfully.');
                    } else {
                        toastError('Error', resp.message || 'Failed to add file extension.');
                    }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) $('#add-name-error').text(errors.name[0]).removeClass('hidden');
                        if (errors.code) $('#add-code-error').text(errors.code[0]).removeClass('hidden');
                    }
                    toastError('Error', xhr.responseJSON?.message || 'Failed to add file extension.');
                },
                complete: () => {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // ========= Open EDIT =========
        $(document).on('click', '.edit-button', function() {
            const id = $(this).data('id');
            $('#edit-name-error, #edit-code-error').addClass('hidden');
            const $btn = $(`.edit-button[data-id="${id}"]`);
            $.ajax({
                url: `/master/fileExtensions/${id}`,
                method: 'GET',
                beforeSend: () => setButtonLoading($btn, true, ''),
                success: (data) => {
                    $('#edit_name').val(data.name);
                    $('#edit_code').val(data.code);
                    // JANGAN set value file input via js
                    $('#edit_is_viewer').prop('checked', !!data.is_viewer);
                    $('#editFileExtensionForm').attr('action', `/master/fileExtensions/${id}`);
                    $('input[name="categories[]"]').prop('checked', false);
                    if (Array.isArray(data.categories)) {
                        data.categories.forEach((c) => $(`input[name="categories[]"][value="${c}"]`).prop('checked', true));
                    }
                    setEditIconPreview(data);
                    showModal(editModal);
                },
                error: (xhr) => toastError('Error', xhr.responseJSON?.message || 'Failed to fetch file extension data.'),
                complete: () => setButtonLoading($btn, false)
            });
        });

        // ========= Submit EDIT =========
        $('#editFileExtensionForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this),
                $btn = $form.find('[type="submit"]');
            $('#edit-name-error, #edit-code-error').addClass('hidden');
            const formData = new FormData(this);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: (resp) => {
                    if (resp.success) {
                        table.ajax.reload();
                        hideModal(editModal);
                        toastSuccess('Success', 'File extension updated successfully.');
                    } else {
                        toastError('Error', resp.message || 'Failed to update file extension.');
                    }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) $('#edit-name-error').text(errors.name[0]).removeClass('hidden');
                        if (errors.code) $('#edit-code-error').text(errors.code[0]).removeClass('hidden');
                    }
                    toastError('Error', xhr.responseJSON?.message || 'Failed to update file extension.');
                },
                complete: () => {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // ========= Delete =========
        $(document).on('click', '.delete-button', function() {
            fileExtensionIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });
        $('#confirmDeleteButton').on('click', function() {
            if (!fileExtensionIdToDelete) return;
            const $btn = $(this);
            $.ajax({
                url: `/master/fileExtensions/${fileExtensionIdToDelete}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                beforeSend: () => {
                    setButtonLoading($btn, true, 'Deleting...');
                    setFormBusy($('#deleteFileExtensionModal'), true);
                },
                success: (resp) => {
                    if (resp.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        fileExtensionIdToDelete = null;
                        toastSuccess('Success', 'File extension deleted successfully.');
                    } else {
                        toastError('Error', resp.message || 'Failed to delete file extension.');
                    }
                },
                error: (xhr) => toastError('Error', xhr.responseJSON?.message || 'Failed to delete file extension.'),
                complete: () => {
                    setButtonLoading($btn, false);
                    setFormBusy($('#deleteFileExtensionModal'), false);
                }
            });
        });

        // ========= Cosmetic focus =========
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