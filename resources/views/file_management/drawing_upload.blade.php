@extends('layouts.app')
@section('title', 'Upload Drawing Package - PROMISE')
@section('header-title', 'File Manager/Upload Drawing Package')

@section('content')

    <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 font-sans">

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Upload New Drawing Package</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Fill in the metadata and upload all related drawing
                files in one go.</p>
        </div>

        <form id="uploadDrawingForm" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 space-y-8">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            <i class="fa-solid fa-file-invoice mr-2 text-blue-500"></i>
                            Drawing Metadata
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">This information will determine the file storage
                            location.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="customer"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                                <select id="customer" name="customer" class="mt-1 block w-full"></select>
                            </div>
                            <div>
                                <label for="model"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                <select id="model" name="model" class="mt-1 block w-full"></select>
                            </div>
                            <div>
                                <label for="partNo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part
                                    No</label>
                                <select id="partNo" name="partNo" class="mt-1 block w-full"></select>
                            </div>
                            <div>
                                <label for="docType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document
                                    Type</label>
                                <select id="docType" name="docType" class="mt-1 block w-full"></select>
                            </div>
                            <div>
                                <label for="category"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                <select id="category" name="category" class="mt-1 block w-full"></select>
                            </div>
                            <div>
                                <label for="partGroup"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>
                                <select id="partGroup" name="partGroup" class="mt-1 block w-full"></select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="project_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Status</label>
                                <select id="project_status" name="project_status" class="mt-1 block w-full"></select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision Note (optional)</label>
                                <textarea id="note" name="note" rows="3" class="mt-1 block w-full p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">A short note that will be saved on the selected package revision.</p>
                            </div>
                        </div>
                    </div>

                    <div id="revision-options" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <i class="fa-solid fa-history mr-2 text-blue-500 text-lg"></i>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revision Options</h3>
                        </div>
                        <div class="flex items-center mb-4">
                            <span id="status-badge" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mr-2"></span>
                            <p id="detection-message" class="text-sm text-gray-600 dark:text-gray-300 italic">Please fill out the drawing metadata to see revision options.</p>
                        </div>
                        <div id="revision-controls" class="space-y-4">
                            <div id="mode-selection" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload Mode</label>
                                <div class="flex flex-col space-y-3">
                                    <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                        <input type="radio" name="upload_mode" value="new-rev" class="mr-2 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-800 dark:text-gray-200">Create new revision (rev <span id="suggested-rev" class="font-semibold"></span>)</span>
                                    </label>
                                    <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                        <input type="radio" name="upload_mode" value="existing" class="mr-2 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-800 dark:text-gray-200">Upload to existing revision</span>
                                    </label>
                                </div>
                            </div>
                            <div id="existing-rev-select" class="hidden">
                                <label for="target_rev" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Existing Revision</label>
                                <select id="target_rev" name="target_rev" class="select2-revision w-full"></select>
                            </div>
                            <div id="conflict-options" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">If File Name Conflicts</label>
                                <div class="flex flex-col space-y-3">
                                    <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                        <input type="radio" name="conflict_mode" value="append" class="mr-2 text-blue-600 focus:ring-blue-500" checked>
                                        <span class="text-sm text-gray-800 dark:text-gray-200">Append suffix (do not replace)</span>
                                    </label>
                                    <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                        <input type="radio" name="conflict_mode" value="replace" class="mr-2 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-800 dark:text-gray-200">Replace existing file</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 h-full">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            <i class="fa-solid fa-clipboard-list mr-2 text-blue-500"></i>
                            Activity Log
                        </h4>
                        <div id="activity-log" class="text-sm text-gray-600 dark:text-gray-400 space-y-4">
                            <p class="italic text-center pt-8">No activity yet. This panel will display recent package activities and approvals.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="drawing-files-section" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    <i class="fa-solid fa-file-arrow-up mr-2 text-blue-500"></i>
                    Drawing Files
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Drag & drop files into their respective categories
                    below or click to browse.</p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Enable Categories</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center space-x-2">
                            <input type="checkbox" id="enable_2d" name="enabled_categories[]" value="2d" class="category-toggle" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">2D</span>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input type="checkbox" id="enable_3d" name="enabled_categories[]" value="3d" class="category-toggle" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">3D</span>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input type="checkbox" id="enable_ecn" name="enabled_categories[]" value="ecn" class="category-toggle" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">ECN</span>
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Only enabled categories will have folders created and accept files.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Uploader untuk 2D Drawings --}}
                    <div class="upload-card-container border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
                        data-category="2d">
                        <div
                            class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                                <i class="fa-solid fa-drafting-compass mr-2 text-gray-500 dark:text-gray-400"></i>
                                2D Drawings
                            </h4>
                            <span id="file-count-2d"
                                class="text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-0.5 rounded-full">0
                                Files</span>
                        </div>
                        <div class="p-4 upload-area" id="upload-area-2d">
                            <input type="file" id="files-2d-input" multiple class="hidden">

                            <div class="upload-drop-zone-placeholder mb-4">
                                <div class="text-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 dark:text-gray-500"></i>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Drag files here or <span
                                            class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer browse-link">browse</span>
                                    </p>
                                </div>
                            </div>

                            <div class="file-list-container" id="file-list-2d">
                            </div>
                        </div>
                    </div>

                    {{-- Uploader untuk 3D Models --}}
                    <div class="upload-card-container border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
                        data-category="3d">
                        <div
                            class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                                <i class="fa-solid fa-cubes mr-2 text-gray-500 dark:text-gray-400"></i>
                                3D Models
                            </h4>
                            <span id="file-count-3d"
                                class="text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-0.5 rounded-full">0
                                Files</span>
                        </div>
                        <div class="p-4 upload-area" id="upload-area-3d">
                            <input type="file" id="files-3d-input" multiple class="hidden">
                            <div class="upload-drop-zone-placeholder mb-4">
                                <div class="text-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 dark:text-gray-500"></i>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Drag files here or <span
                                            class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer browse-link">browse</span>
                                    </p>
                                </div>
                            </div>
                            <div class="file-list-container" id="file-list-3d"></div>
                        </div>
                    </div>

                    {{-- Uploader untuk ECN / Documents (Struktur serupa) --}}
                    <div class="upload-card-container border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
                        data-category="ecn">
                        <div
                            class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                                <i class="fa-solid fa-file-lines mr-2 text-gray-500 dark:text-gray-400"></i>
                                ECN / Documents
                            </h4>
                            <span id="file-count-ecn"
                                class="text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-0.5 rounded-full">0
                                Files</span>
                        </div>
                        <div class="p-4 upload-area" id="upload-area-ecn">
                            <input type="file" id="files-ecn-input" multiple class="hidden">
                            <div class="upload-drop-zone-placeholder mb-4">
                                <div class="text-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 dark:text-gray-500"></i>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Drag files here or <span
                                            class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer browse-link">browse</span>
                                    </p>
                                </div>
                            </div>
                            <div class="file-list-container" id="file-list-ecn"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                    <button type="button" id="request-approval-button" class="hidden px-4 py-2 bg-yellow-500 text-white rounded-md" disabled>
                        Request to Approval
                    </button>
                    <button type="submit" id="submit-button"
                        class="inline-flex items-center gap-2 justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        <i class="fa-solid fa-upload"></i>
                        Upload to Draft
                    </button>
            </div>
        </form>
    </div>

    <style>
        .opacity-50 { opacity: 0.5; }
        .cursor-not-allowed { cursor: not-allowed; }
        .pointer-events-none { pointer-events: none; }
        .upload-card-container {
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            transition: box-shadow 0.2s ease-in-out, border-color 0.2s ease-in-out;
            height: 100%;
        }

        .dark .upload-card-container {
            background-color: #1f2937;
        }

        .upload-area {
            transition: background-color 0.2s ease-in-out;
            flex-grow: 1;
        }

        .upload-area.drag-over {
            background-color: #f0f9ff;
        }

        .dark .upload-area.drag-over {
            background-color: rgba(30, 58, 138, 0.2);
        }

        .upload-drop-zone-placeholder {
            border: 2px dashed #d1d5db;
            border-radius: 0.75rem;
            padding: 2rem 1rem;
            transition: border-color 0.2s, background-color 0.2s, transform 0.2s;
        }

        .dark .upload-drop-zone-placeholder {
            border-color: #4b5563;
        }

        .upload-area.drag-over .upload-drop-zone-placeholder {
            border-color: #60a5fa;
            background-color: #dbeafe;
            transform: scale(1.02);
        }

        .dark .upload-area.drag-over .upload-drop-zone-placeholder {
            border-color: #60a5fa;
            background-color: rgba(30, 58, 138, 0.4);
        }

        .file-list-container {
            max-height: 250px;
            overflow-y: auto;
            padding-right: 8px;
            margin-right: -8px;
        }

        .file-list-container::-webkit-scrollbar {
            width: 6px;
        }

        .file-list-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .file-list-container::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 20px;
        }

        .dark .file-list-container::-webkit-scrollbar-thumb {
            background-color: #4b5563;
        }

        .file-list-container:hover::-webkit-scrollbar-thumb {
            background-color: #93c5fd;
        }

        .dark .file-list-container:hover::-webkit-scrollbar-thumb {
            background-color: #60a5fa;
        }

        .file-preview-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background-color: transparent;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s ease-in-out;
            cursor: pointer;
        }

        .file-preview-item:first-child {
            border-top: 1px solid #e5e7eb;
        }

        .file-preview-item:hover {
            background-color: #f9fafb;
        }

        .dark .file-preview-item {
            border-color: #374151;
        }

        .dark .file-preview-item:hover {
            background-color: #2c3748;
        }

        .file-icon {
            flex-shrink: 0;
            width: 2.25rem;
            height: 2.25rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            margin-right: 0.75rem;
        }

        .file-details {
            flex-grow: 1;
            overflow: hidden;
        }

        .file-name {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dark .file-name {
            color: #f9fafb;
        }

        .file-size {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .dark .file-size {
            color: #9ca3af;
        }

        .progress-bar-container {
            height: 4px;
            width: 100%;
            background-color: #e5e7eb;
            border-radius: 9999px;
            margin-top: 0.25rem;
            overflow: hidden;
        }

        .dark .progress-bar-container {
            background-color: #4b5563;
        }

        .progress-bar {
            height: 100%;
            width: 100%;
            background-color: #3b82f6;
            border-radius: 9999px;
        }

        .remove-file-btn {
            margin-left: 1rem;
            padding: 0.5rem;
            border-radius: 9999px;
            line-height: 1;
            transition: background-color 0.2s;
        }

        .remove-file-btn:hover {
            background-color: #fee2e2;
        }

        .dark .remove-file-btn:hover {
            background-color: #450a0a;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // SweetAlert2 Toast Configuration
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
                customClass: { popup: 'swal2-toast border', title: '', timerProgressBar: '' },
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

        function toastSuccess(title = 'Success', text = 'Operation completed successfully.') {
            renderToast({ icon: 'success', title, text });
        }

        function toastError(title = 'Error', text = 'An error occurred.') {
            BaseToast.update({ timer: 3400 });
            renderToast({ icon: 'error', title, text });
            BaseToast.update({ timer: 2600 });
        }

        function toastWarning(title = 'Warning', text = 'Please check your data.') {
            renderToast({ icon: 'warning', title, text });
        }

        function toastInfo(title = 'Info', text = '') {
            renderToast({ icon: 'info', title, text });
        }

        window.toastSuccess = toastSuccess;
        window.toastError = toastError;
        window.toastWarning = toastWarning;
        window.toastInfo = toastInfo;

        // Initialize Select2
        function initializeSelect2s() {
            $('#customer').select2({
                width: '100%',
                placeholder: 'Select Customer',
                ajax: {
                    url: "{{ route('upload.getCustomerData') }}",
                    method: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ _token: "{{ csrf_token() }}", q: params.term }),
                    processResults: data => ({ results: data.results })
                }
            });
            $('#model').select2({
                width: '100%',
                placeholder: 'Select Customer First'
            }).prop('disabled', true);
            $('#partNo').select2({
                width: '100%',
                placeholder: 'Select Model First'
            }).prop('disabled', true);
            $('#docType').select2({
                width: '100%',
                placeholder: 'Select Part No First'
            }).prop('disabled', true);
            $('#category').select2({
                width: '100%',
                placeholder: 'Select Document Group First'
            }).prop('disabled', true);
            $('#partGroup').select2({
                width: '100%',
                placeholder: 'Select Sub Category First'
            }).prop('disabled', true);
            $('#target_rev').select2({
                width: '100%',
                placeholder: 'Select Revision',
                minimumResultsForSearch: Infinity
            });
        }
        initializeSelect2s();
        checkMetadataFilled();

        $('#customer').on('change', function() {
            const customerId = $(this).val();
            $('#model, #partNo, #docType, #category, #partGroup').val(null).trigger('change').prop('disabled', true);
            if (customerId) {
                $('#model').prop('disabled', false).select2({
                    width: '100%',
                    placeholder: 'Select Model',
                    ajax: {
                        url: "{{ route('upload.getModelData') }}",
                        method: 'POST',
                        dataType: 'json',
                        data: params => ({ _token: "{{ csrf_token() }}", q: params.term, customer_id: customerId }),
                        processResults: data => ({ results: data.results })
                    }
                });
            }
        });

        $('#model').on('change', function() {
            const modelId = $(this).val();
            $('#partNo, #docType, #category, #partGroup').val(null).trigger('change').prop('disabled', true);
            if (modelId) {
                $('#partNo').prop('disabled', false).select2({
                    width: '100%',
                    placeholder: 'Select Part No',
                    ajax: {
                        url: "{{ route('upload.getProductData') }}",
                        method: 'POST',
                        dataType: 'json',
                        data: params => ({ _token: "{{ csrf_token() }}", q: params.term, model_id: modelId }),
                        processResults: data => ({ results: data.results })
                    }
                });
            }
        });

        $('#partNo').on('change', function() {
            const partNoId = $(this).val();
            $('#docType, #category, #partGroup').val(null).trigger('change').prop('disabled', true);
            if (partNoId) {
                $('#docType').prop('disabled', false).select2({
                    width: '100%',
                    placeholder: 'Select Document Group',
                    ajax: {
                        url: "{{ route('upload.getDocumentGroupData') }}",
                        method: 'POST',
                        dataType: 'json',
                        data: params => ({ _token: "{{ csrf_token() }}", q: params.term }),
                        processResults: data => ({ results: data.results })
                    }
                });
            }
        });

        $('#docType').on('change', function() {
            const docTypeId = $(this).val();
            $('#category, #partGroup').val(null).trigger('change').prop('disabled', true);
            if (docTypeId) {
                $('#category').prop('disabled', false).select2({
                    width: '100%',
                    placeholder: 'Select Sub Category',
                    ajax: {
                        url: "{{ route('upload.getSubCategoryData') }}",
                        method: 'POST',
                        dataType: 'json',
                        data: params => ({ _token: "{{ csrf_token() }}", q: params.term, document_group_id: docTypeId }),
                        processResults: data => ({ results: data.results })
                    }
                });
            }
        });

        $('#category').on('change', function() {
            const categoryId = $(this).val();
            $('#partGroup').val(null).trigger('change').prop('disabled', true);
            if (categoryId) {
                $('#partGroup').prop('disabled', false).select2({
                    width: '100%',
                    placeholder: 'Select Part Group',
                    ajax: {
                        url: "{{ route('upload.getPartGroupData') }}",
                        method: 'POST',
                        dataType: 'json',
                        data: params => ({
                            _token: "{{ csrf_token() }}",
                            q: params.term,
                            customer_id: $('#customer').val(),
                            model_id: $('#model').val()
                        }),
                        processResults: data => {
                            if (data.total_count === 0) {
                                toastWarning('Warning', 'No Part Groups available for selected Customer and Model.');
                            }
                            return { results: data.results };
                        }
                    }
                });
            }
        });

        $('#partGroup').on('change', function() {
            const partGroupId = $(this).val();
            if (partGroupId) {
                $('#project_status').prop('disabled', false).select2({
                    width: '100%',
                    placeholder: 'Select Project Status',
                    ajax: {
                        url: "{{ route('upload.getProjectStatusData') }}",
                        method: 'POST',
                        dataType: 'json',
                        data: params => ({
                            _token: "{{ csrf_token() }}",
                            q: params.term
                        }),
                        processResults: data => ({
                            results: data.results
                        })
                    }
                });
            } else {
                $('#project_status').val(null).trigger('change').prop('disabled', true).select2({
                    width: '100%',
                    placeholder: 'Select Part Group First'
                });
            }

            if ($('#customer').val() && $('#model').val() && $('#partNo').val() && $('#docType').val() && $('#category').val() && $(this).val()) {
                checkExisting();
            }

            // fetch activity logs when partGroup changes (metadata completed)
            fetchActivityLogs();
        });

        // Category toggles: enable/disable upload areas
        function setCategoryEnabled(cat, enabled) {
            const uploadArea = document.getElementById(`upload-area-${cat}`);
            const input = document.getElementById(`files-${cat}-input`);
            const countBadge = document.getElementById(`file-count-${cat}`);
            if (enabled) {
                uploadArea.classList.remove('pointer-events-none', 'opacity-50');
                input.disabled = false;
                countBadge.classList.remove('opacity-50');
            } else {
                uploadArea.classList.add('pointer-events-none', 'opacity-50');
                input.disabled = true;
                // clear stored files for that category
                fileStores[cat] = [];
                renderFilePreviews(cat);
                countBadge.classList.add('opacity-50');
            }
        }

        document.querySelectorAll('.category-toggle').forEach(cb => {
            cb.addEventListener('change', (e) => {
                setCategoryEnabled(e.target.value, e.target.checked);
            });
            // initialize
            setCategoryEnabled(cb.value, cb.checked);
        });

        // Load project_status Select2
        $('#project_status').select2({
            width: '100%',
            placeholder: 'Select Part Group First'
        }).prop('disabled', true);

        $('#project_status').on('change', function() { checkMetadataFilled(); });

        function checkExisting() {
            $.ajax({
                url: "{{ route('upload.drawing.check') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    customer: $('#customer').val(),
                    model: $('#model').val(),
                    partNo: $('#partNo').val(),
                    docType: $('#docType').val(),
                    category: $('#category').val(),
                    partGroup: $('#partGroup').val(),
                },
                success: function(res) {
                    $('#revision-options').removeClass('hidden');
                    $('#mode-selection, #existing-rev-select, #conflict-options').addClass('hidden');

                    if (!res.exists) {
                        $('#status-badge').text('New').addClass('bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300').removeClass('bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300');
                        $('#detection-message').html('<strong>New document</strong> will be created as <strong>rev0</strong>.');
                        $('#suggested-rev').text('0');
                        toastInfo('New Document', 'No existing revisions found. Will create rev0.');
                    } else {
                        $('#status-badge').text('Existing').addClass('bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300').removeClass('bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300');
                        $('#detection-message').html(`Found <strong>${res.revisions.length} revisions</strong>, latest: <strong>rev${res.latest_rev}</strong>.`);
                        $('#suggested-rev').text(res.suggested_rev);
                        $('#mode-selection').removeClass('hidden');
                        $('#target_rev').empty();
                        res.revisions.forEach(rev => {
                            $('#target_rev').append(`<option value="${rev}">rev${rev}</option>`);
                        });
                        $('#target_rev').trigger('change');
                        $('input[name="upload_mode"][value="new-rev"]').prop('checked', true).trigger('change');
                        toastInfo('Existing Revisions', `Found ${res.revisions.length} revisions, latest rev${res.latest_rev}.`);
                    }
                },
                error: function(xhr) {
                    $('#revision-options').addClass('hidden');
                    toastError('Error', xhr.responseJSON?.message || 'Failed to check revision status.');
                }
            });
        }

        $('input[name="upload_mode"]').on('change', function() {
            const mode = $(this).val();
            $('#existing-rev-select, #conflict-options').addClass('hidden');
            if (mode === 'existing') {
                $('#existing-rev-select, #conflict-options').removeClass('hidden');
            }
        });

    const fileStores = { '2d': [], '3d': [], 'ecn': [] };
    // draft state: when upload to draft completed these are populated
    let draftSaved = false;
    let savedPackageId = null;
    let savedRevisionId = null; // DB revision id
    let savedRevisionNo = null; // human readable rev number

        function getFileIcon(fileName) {
            const extension = fileName.split('.').pop().toLowerCase();
            const iconMap = {
                'pdf': { icon: 'fa-file-pdf', color: 'bg-red-500' },
                'dwg': { icon: 'fa-file-pen', color: 'bg-blue-500' },
                'dxf': { icon: 'fa-file-pen', color: 'bg-blue-500' },
                'step': { icon: 'fa-cube', color: 'bg-yellow-500' },
                'stp': { icon: 'fa-cube', color: 'bg-yellow-500' },
                'iges': { icon: 'fa-cube', color: 'bg-yellow-500' },
                'igs': { icon: 'fa-cube', color: 'bg-yellow-500' },
                'sldprt': { icon: 'fa-cube', color: 'bg-green-500' },
                'x_t': { icon: 'fa-cube', color: 'bg-green-500' },
                'doc': { icon: 'fa-file-word', color: 'bg-blue-600' },
                'docx': { icon: 'fa-file-word', color: 'bg-blue-600' },
                'xls': { icon: 'fa-file-excel', color: 'bg-green-600' },
                'xlsx': { icon: 'fa-file-excel', color: 'bg-green-600' },
                'ppt': { icon: 'fa-file-powerpoint', color: 'bg-orange-500' },
                'pptx': { icon: 'fa-file-powerpoint', color: 'bg-orange-500' },
                'zip': { icon: 'fa-file-archive', color: 'bg-gray-500' },
                'rar': { icon: 'fa-file-archive', color: 'bg-gray-500' },
                'png': { icon: 'fa-file-image', color: 'bg-purple-500' },
                'jpg': { icon: 'fa-file-image', color: 'bg-purple-500' },
                'jpeg': { icon: 'fa-file-image', color: 'bg-purple-500' },
            };
            return iconMap[extension] || { icon: 'fa-file', color: 'bg-gray-400' };
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function checkMetadataFilled() {
            const isFilled = $('#customer').val() && $('#model').val() && $('#partNo').val() && $('#docType').val() && $('#category').val() && $('#partGroup').val() && $('#project_status').val();
            const $submitButton = $('#submit-button');
            const $drawingFilesSection = $('#drawing-files-section');
            const $categoryToggles = $('.category-toggle');
            const $fileInputs = $drawingFilesSection.find('input[type="file"]');
            const $noteTextArea = $('#note');

            if (isFilled) {
                $submitButton.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
                $drawingFilesSection.removeClass('opacity-50 pointer-events-none');
                $categoryToggles.prop('disabled', false);
                $fileInputs.prop('disabled', false);
                $noteTextArea.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
            } else {
                $submitButton.addClass('opacity-50 cursor-not-allowed').prop('disabled', true);
                $drawingFilesSection.addClass('opacity-50 pointer-events-none');
                $categoryToggles.prop('disabled', true);
                $fileInputs.prop('disabled', true);
                $noteTextArea.addClass('opacity-50 cursor-not-allowed').prop('disabled', true);
            }

                    // always refresh activity log when metadata fill state changes
                    fetchActivityLogs();
        }

        $('#customer, #model, #partNo, #docType, #category, #partGroup').on('change', checkMetadataFilled);

        function renderFilePreviews(category) {
            const fileListContainer = document.getElementById(`file-list-${category}`);
            const fileCountBadge = document.getElementById(`file-count-${category}`);
            const dropzonePlaceholder = document.querySelector(`#upload-area-${category} .upload-drop-zone-placeholder`);

            fileListContainer.innerHTML = '';

            if (fileStores[category].length === 0) {
                dropzonePlaceholder.querySelector('p').innerHTML = `Drag files here or <span class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer browse-link">browse</span>`;
            } else {
                dropzonePlaceholder.querySelector('p').innerHTML = `Drag more files here or <span class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer browse-link">browse</span>`;
            }

            fileCountBadge.textContent = `${fileStores[category].length} Files`;

            fileStores[category].forEach((fileObj, index) => {
                // support two representations: raw File or wrapped object {file, name, size, uploaded}
                const name = fileObj.name || (fileObj.file && fileObj.file.name) || 'unknown';
                const size = fileObj.size || (fileObj.file && fileObj.file.size) || 0;
                const uploaded = !!fileObj.uploaded;
                const { icon, color } = getFileIcon(name);
                const fileRow = document.createElement('div');
                fileRow.className = 'file-preview-item flex items-center space-x-3 p-2 rounded-md';
                fileRow.setAttribute('data-file-index', index);
                fileRow.innerHTML = `
                    <div class="file-icon ${color} text-white w-10 h-10 flex items-center justify-center rounded">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                    <div class="file-details flex-1">
                        <span class="file-name text-sm text-gray-800 dark:text-gray-200" title="${name}">${name}</span>
                        <span class="file-size text-xs text-gray-500 dark:text-gray-400">${formatBytes(size)}</span>
                        <div class="status-container mt-1 h-5 flex items-center">
                            ${uploaded ? `<i class="fa-solid fa-check-circle text-green-500 mr-1"></i><span class="text-xs text-green-600">Uploaded</span>` : `<span class="status-text text-xs text-gray-500 dark:text-gray-400">Added</span>`}
                        </div>
                    </div>
                    <button type="button" class="remove-file-btn" data-category="${category}" data-index="${index}" title="Remove File">
                        <i class="fa-solid fa-trash-can text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-500"></i>
                    </button>
                `;
                fileListContainer.appendChild(fileRow);
            });
        }

        function handleFiles(files, category) {
            let addedCount = 0;
            Array.from(files).forEach(file => {
                if (!fileStores[category].some(f => (f.name || (f.file && f.file.name)) === file.name && (f.size || (f.file && f.file.size)) === file.size)) {
                    // Wrap file in object to allow storing uploaded flag later
                    fileStores[category].push({ file: file, name: file.name, size: file.size, uploaded: false });
                    addedCount++;
                }
            });
            if (addedCount > 0) {
                toastInfo('Files Added', `Added ${addedCount} file${addedCount > 1 ? 's' : ''} to ${category.toUpperCase()} category.`);
                // If we previously saved a draft, adding new files should re-enable Upload and hide Request Approval
                if (draftSaved) {
                    draftSaved = false;
                    savedPackageId = null;
                    savedRevisionId = null;
                    savedRevisionNo = null;
                    $('#request-approval-button').addClass('hidden').prop('disabled', true);
                    $('#submit-button').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    enableMetadataEditing(true);
                }
            }
            renderFilePreviews(category);
        }

        document.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-file-btn');
            if (removeBtn) {
                e.preventDefault();
                const category = removeBtn.dataset.category;
                const index = parseInt(removeBtn.dataset.index);
                fileStores[category].splice(index, 1);
                renderFilePreviews(category);
            }

            const browseLink = e.target.closest('.browse-link');
            if (browseLink) {
                const uploadArea = browseLink.closest('.upload-area');
                const fileInput = uploadArea.querySelector('input[type="file"]');
                if (!fileInput.disabled) {
                    fileInput.click();
                }
            }
        });

        document.querySelectorAll('.upload-card-container').forEach(container => {
            const category = container.dataset.category;
            const uploadArea = document.getElementById(`upload-area-${category}`);
            const fileInput = document.getElementById(`files-${category}-input`);

            fileInput.addEventListener('change', (e) => {
                if (!fileInput.disabled) {
                    handleFiles(e.target.files, category);
                    e.target.value = null;
                }
            });

            uploadArea.addEventListener('dragover', (e) => {
                if (!uploadArea.classList.contains('pointer-events-none')) {
                    e.preventDefault();
                    uploadArea.classList.add('drag-over');
                }
            });

            uploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
            });

            uploadArea.addEventListener('drop', (e) => {
                if (!uploadArea.classList.contains('pointer-events-none')) {
                    e.preventDefault();
                    uploadArea.classList.remove('drag-over');
                    handleFiles(e.dataTransfer.files, category);
                }
            });

            renderFilePreviews(category);
        });

        document.getElementById('uploadDrawingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (fileStores['2d'].length === 0 && fileStores['3d'].length === 0 && fileStores['ecn'].length === 0) {
                toastWarning('Warning', 'Please upload at least one file.');
                return;
            }

            if (!($('#customer').val() && $('#model').val() && $('#partNo').val() && $('#docType').val() && $('#category').val() && $('#partGroup').val())) {
                toastWarning('Warning', 'Please complete all required metadata.');
                return;
            }

            if ($('input[name="upload_mode"]:checked').val() === 'existing' && !$('#target_rev').val()) {
                toastWarning('Warning', 'Please select an existing revision.');
                return;
            }

            if ($('input[name="upload_mode"]:checked').val() === 'existing' && !$('input[name="conflict_mode"]:checked').val()) {
                toastWarning('Warning', 'Please select a conflict resolution mode (append or replace).');
                return;
            }

            const $submitButton = $('#submit-button');
            const mode = $('input[name="upload_mode"]:checked').val() || 'new-rev';
            const revision = mode === 'existing' ? $('#target_rev').val() : $('#suggested-rev').text();
            // Only include files from enabled categories
            const enabledCats = Array.from(document.querySelectorAll('.category-toggle:checked')).map(i => i.value);
            let filesToUpload = [];
            enabledCats.forEach(cat => {
                filesToUpload.push(...fileStores[cat].map((f, index) => ({ file: (f.file || f), category: cat, wrapperIndex: index })));
            });

            let successfulUploads = 0;
            let failedUploads = 0;
            const totalFiles = filesToUpload.length;
            let lastRevision = revision;
            const maxRetries = 3;

            $submitButton
                .prop('disabled', true)
                .addClass('opacity-50 cursor-not-allowed')
                .html('<i class="fa-solid fa-spinner fa-spin mr-2"></i>Uploading...');

            function uploadFile(item, retryCount = 0) {
                const conflict = $('input[name="conflict_mode"]:checked').val() || 'append'; // Default to 'append' if no selection
                const formData = new FormData();
                formData.append(`files_${item.category}[]`, item.file);
                formData.append('mode', mode);
                formData.append('revision', lastRevision);
                if (mode === 'existing') {
                    formData.append('target_rev', lastRevision);
                    formData.append('conflict', conflict);
                }
                formData.append('customer', $('#customer').val());
                formData.append('model', $('#model').val());
                formData.append('partNo', $('#partNo').val());
                formData.append('docType', $('#docType').val());
                formData.append('category', $('#category').val() || '');
                formData.append('partGroup', $('#partGroup').val());
                formData.append('project_status', $('#project_status').val());
                formData.append('note', $('#note').val() || '');
                // pass enabled categories so server knows which folders to create
                enabledCats.forEach(c => formData.append('enabled_categories[]', c));
                formData.append('as_draft', '1');
                formData.append('_token', "{{ csrf_token() }}");

                const fileRow = document.querySelector(`#file-list-${item.category} .file-preview-item[data-file-index="${item.wrapperIndex}"] .status-container`);
                if (fileRow) {
                    fileRow.innerHTML = `
                        <div class="progress-bar-container h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="progress-bar h-full bg-blue-500" style="width: 0%"></div>
                        </div>
                    `;
                }

                $.ajax({
                    url: "{{ route('upload.drawing.store') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 60000, // Reduced timeout to 60 seconds
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = (e.loaded / e.total) * 100;
                                const progressBar = fileRow?.querySelector('.progress-bar');
                                if (progressBar) progressBar.style.width = `${percentComplete}%`;
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(res) {
                        successfulUploads++;
                        lastRevision = res.rev || lastRevision;
                        // store package/revision info returned by server
                        if (res.package_id) savedPackageId = res.package_id;
                        if (res.revision_id) savedRevisionId = res.revision_id;
                        if (res.rev !== undefined) savedRevisionNo = res.rev;
                        // mark wrapper object as uploaded so UI shows Uploaded status
                        const wrapper = fileStores[item.category] && fileStores[item.category][item.wrapperIndex];
                        if (wrapper) { wrapper.uploaded = true; }
                        if (fileRow) {
                            fileRow.innerHTML = `<i class="fa-solid fa-check-circle text-green-500"></i> <span class="text-xs text-green-500">Uploaded</span>`;
                        }
                        activeUploads--;
                        processQueue();
                        checkUploadCompletion();
                        // refresh activity log after successful upload
                        fetchActivityLogs();
                    },
                    error: function(xhr) {
                        if (retryCount < maxRetries) {
                            setTimeout(() => uploadFile(item, retryCount + 1), 2000);
                            if (fileRow) {
                                fileRow.innerHTML = `<i class="fa-solid fa-sync-alt text-yellow-500"></i> <span class="text-xs text-yellow-500">Retrying (${retryCount + 1}/${maxRetries})</span>`;
                            }
                            return;
                        }
                        failedUploads++;
                        if (fileRow) {
                            const progressBar = fileRow.querySelector('.progress-bar-container .progress-bar');
                            if (progressBar) {
                                progressBar.style.width = '100%';
                                progressBar.classList.remove('bg-blue-500');
                                progressBar.classList.add('bg-red-500');
                            }
                            setTimeout(() => {
                                fileRow.innerHTML = `<i class="fa-solid fa-times-circle text-red-500"></i> <span class="text-xs text-red-500">Failed after ${maxRetries} retries</span>`;
                            }, 500);
                        }
                        const msg = xhr.responseJSON?.message || 'Failed to upload file. Check file size, network, or server logs.';
                        toastError('Error', msg);
                        activeUploads--;
                        processQueue();
                        // Reset button if all uploads failed
                        if ((successfulUploads + failedUploads) === totalFiles && successfulUploads === 0) {
                            $submitButton
                                .prop('disabled', false)
                                .removeClass('opacity-50 cursor-not-allowed')
                                .html('<i class="fa-solid fa-upload"></i> Submit Drawing Package');
                            toastError('Upload Failed', 'All uploads failed. Please check server logs or try again.');
                        }
                        checkUploadCompletion();
                    }
                });
            }

            function enableMetadataEditing(allow) {
                // allow = true to enable editing, false to lock metadata as read-only
                const selectors = ['#customer', '#model', '#partNo', '#docType', '#category', '#partGroup', '#project_status'];
                selectors.forEach(s => {
                    try {
                        if ($(s).data('select2')) {
                            $(s).prop('disabled', !allow).trigger('change.select2');
                        } else {
                            $(s).prop('disabled', !allow);
                        }
                    } catch (e) {}
                });
                // note textarea
                $('#note').prop('disabled', !allow).toggleClass('opacity-50 cursor-not-allowed', !allow);
                // keep category toggles and upload areas usable even when metadata is locked so user can add files to draft
                // (do not disable category toggles or upload areas here)
            }

            function checkUploadCompletion() {
                if ((successfulUploads + failedUploads) === totalFiles) {
                    if (failedUploads === 0) {
                        toastSuccess('Success', `All ${totalFiles} files uploaded successfully to rev${lastRevision}.`);
                        // keep uploaded files visible and mark draft saved
                        draftSaved = true;
                        savedRevisionNo = lastRevision;
                        // lock metadata fields (read-only)
                        enableMetadataEditing(false);
                        // show Request to Approval button
                        $('#request-approval-button').removeClass('hidden').prop('disabled', false);
                        // disable Upload to Draft until new files added
                        $submitButton.prop('disabled', true).addClass('opacity-50 cursor-not-allowed').html('<i class="fa-solid fa-upload"></i> Upload to Draft');
                    } else {
                        toastWarning('Upload Incomplete', `${successfulUploads} files succeeded, ${failedUploads} files failed after retries. Check server logs or try again.`);
                        $submitButton.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').html('<i class="fa-solid fa-upload"></i> Upload to Draft');
                    }
                }
            }

            let activeUploads = 0;
            const maxConcurrent = 3;

            function processQueue() {
                while (activeUploads < maxConcurrent && filesToUpload.length > 0) {
                    activeUploads++;
                    const item = filesToUpload.shift();
                    uploadFile(item);
                }
            }

            filesToUpload.forEach(item => {
                item.retries = 0;
            });

            processQueue();
        });

        // Request to Approval button
        $('#request-approval-button').on('click', function() {
            if (!draftSaved || !savedPackageId) {
                toastWarning('No Draft', 'No draft saved yet to request approval for.');
                return;
            }
            const $btn = $(this);
            $btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed').text('Requesting...');
            $.ajax({
                url: "{{ route('upload.drawing.request-approval') }}",
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", package_id: savedPackageId, revision_no: savedRevisionNo },
                success: function(res) {
                    toastSuccess('Requested', 'Draft set to pending for approval.');
                    // reflect change on UI: hide request button and keep form locked
                    $btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                    $btn.text('Requested');
                    // refresh activity log after request approval
                    fetchActivityLogs();
                },
                error: function(xhr) {
                    toastError('Error', xhr.responseJSON?.message || 'Failed to request approval.');
                    $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').text('Request to Approval');
                }
            });
        });

        // Fetch activity logs from server. If metadata is fully filled use package/revision filter, otherwise fetch recent global logs.
        function fetchActivityLogs() {
            const data = { _token: "{{ csrf_token() }}" };
            // if metadata filled, pass identifying fields
            const metaFilled = $('#customer').val() && $('#model').val() && $('#partNo').val() && $('#docType').val() && $('#category').val() && $('#partGroup').val();
            if (metaFilled) {
                data.customer = $('#customer').val();
                data.model = $('#model').val();
                data.partNo = $('#partNo').val();
                data.docType = $('#docType').val();
                data.category = $('#category').val() || '';
                data.partGroup = $('#partGroup').val();
            }

            $.ajax({
                url: "{{ route('upload.drawing.activity-logs') }}",
                method: 'POST',
                data: data,
                success: function(res) {
                    renderActivityLogs(res.logs || []);
                },
                error: function(xhr) {
                    console.warn('Failed to load activity logs', xhr.responseText);
                }
            });
        }

        function renderActivityLogs(logs) {
            const container = $('#activity-log');
            container.empty();
            if (!logs || logs.length === 0) {
                container.html('<p class="italic text-center pt-8">No activity yet. This panel will display recent package activities and approvals.</p>');
                return;
            }
            logs.forEach(l => {
                const created = l.created_at ? new Date(l.created_at).toLocaleString() : '';
                let text = l.activity_code;
                try {
                    const m = l.meta || {};
                    if (l.activity_code === 'UPLOAD') {
                        // Format: Upload - Part no - doctype group - Cust - Model - doctype Subcategory - notes
                        const parts = [
                            'Upload',
                            m.part_no || '',
                            m.doctype_group || '',
                            m.customer_code || '',
                            m.model_name || '',
                            m.doctype_subcategory || '',
                            m.note || ''
                        ];
                        // join non-empty parts with ' - '
                        text = parts.filter(p => p !== null && p !== undefined && String(p).trim() !== '').join(' - ');
                    } else if (l.activity_code === 'REQUEST_APPROVAL' || l.activity_code === 'APPROVE') {
                        const m = l.meta || {};
                        text = `Request Approval - rev${ m.revision_no ?? '' }`;
                    } else {
                        text = l.activity_code + (l.meta && l.meta.package_no ? (' - ' + l.meta.package_no) : '');
                    }
                } catch (e) {
                    text = l.activity_code;
                }

                const userLabel = l.user_name ? `${l.user_name}` : (l.user_id ? `User ID: ${l.user_id}` : '');

                const el = $(`<div class="flex items-start space-x-3">
                    <div class="text-blue-500"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="flex-1">
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">${ text }</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">${ userLabel ? (userLabel + ' · ') : '' }${ created }</div>
                    </div>
                </div>`);
                container.append(el);
            });
        }

        // initial load
        fetchActivityLogs();
    });
</script>


@endsection
