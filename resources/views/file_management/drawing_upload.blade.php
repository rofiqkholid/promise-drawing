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
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    <i class="fa-solid fa-file-invoice mr-2 text-blue-500"></i>
                    Drawing Metadata
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">This information will determine the file storage
                    location.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
                        <label for="revision"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision</label>
                        <input type="text" id="revision" name="revision" placeholder="e.g., Rev-1, A, etc."
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    <i class="fa-solid fa-file-arrow-up mr-2 text-blue-500"></i>
                    Drawing Files
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Drag & drop files into their respective categories
                    below or click to browse.</p>

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
                <button type="submit"
                    class="inline-flex items-center gap-2 justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                    <i class="fa-solid fa-upload"></i>
                    Submit Drawing Package
                </button>
            </div>
        </form>
    </div>

    <style>
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

            function initializeSelect2s() {
                $('#customer').select2({
                    width: '100%',
                    placeholder: 'Select Customer',
                    ajax: {
                        url: "{{ route('upload.getCustomerData') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.results
                            };
                        }
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
            }
            initializeSelect2s();
            $('#customer').on('change', function() {
                const customerId = $(this).val();
                $('#model, #partNo, #docType, #category').val(null).trigger('change').prop('disabled',
                true);
                if (customerId) {
                    $('#model').prop('disabled', false).select2({
                        width: '100%',
                        placeholder: 'Select Model',
                        ajax: {
                            url: "{{ route('upload.getModelData') }}",
                            method: 'POST',
                            dataType: 'json',
                            data: params => ({
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                customer_id: customerId
                            }),
                            processResults: data => ({
                                results: data.results
                            })
                        }
                    });
                }
            });
            $('#model').on('change', function() {
                const modelId = $(this).val();
                $('#partNo, #docType, #category').val(null).trigger('change').prop('disabled', true);
                if (modelId) {
                    $('#partNo').prop('disabled', false).select2({
                        width: '100%',
                        placeholder: 'Select Part No',
                        ajax: {
                            url: "{{ route('upload.getProductData') }}",
                            method: 'POST',
                            dataType: 'json',
                            data: params => ({
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                model_id: modelId
                            }),
                            processResults: data => ({
                                results: data.results
                            })
                        }
                    });
                }
            });
            $('#partNo').on('change', function() {
                const partNoId = $(this).val();
                $('#docType, #category').val(null).trigger('change').prop('disabled', true);
                if (partNoId) {
                    $('#docType').prop('disabled', false).select2({
                        width: '100%',
                        placeholder: 'Select Document Group',
                        ajax: {
                            url: "{{ route('upload.getDocumentGroupData') }}",
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
                }
            });
            $('#docType').on('change', function() {
                const docTypeId = $(this).val();
                $('#category').val(null).trigger('change').prop('disabled', true);
                if (docTypeId) {
                    $('#category').prop('disabled', false).select2({
                        width: '100%',
                        placeholder: 'Select Sub Category',
                        ajax: {
                            url: "{{ route('upload.getSubCategoryData') }}",
                            method: 'POST',
                            dataType: 'json',
                            data: params => ({
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                document_group_id: docTypeId
                            }),
                            processResults: data => ({
                                results: data.results
                            })
                        }
                    });
                }
            });

            const fileStores = {
                '2d': [],
                '3d': [],
                'ecn': []
            };

            function getFileIcon(fileName) {
                const extension = fileName.split('.').pop().toLowerCase();
                const iconMap = {
                    'pdf': {
                        icon: 'fa-file-pdf',
                        color: 'bg-red-500'
                    },
                    'dwg': {
                        icon: 'fa-file-pen',
                        color: 'bg-blue-500'
                    },
                    'dxf': {
                        icon: 'fa-file-pen',
                        color: 'bg-blue-500'
                    },
                    'step': {
                        icon: 'fa-cube',
                        color: 'bg-yellow-500'
                    },
                    'stp': {
                        icon: 'fa-cube',
                        color: 'bg-yellow-500'
                    },
                    'iges': {
                        icon: 'fa-cube',
                        color: 'bg-yellow-500'
                    },
                    'igs': {
                        icon: 'fa-cube',
                        color: 'bg-yellow-500'
                    },
                    'sldprt': {
                        icon: 'fa-cube',
                        color: 'bg-green-500'
                    },
                    'x_t': {
                        icon: 'fa-cube',
                        color: 'bg-green-500'
                    },
                    'doc': {
                        icon: 'fa-file-word',
                        color: 'bg-blue-600'
                    },
                    'docx': {
                        icon: 'fa-file-word',
                        color: 'bg-blue-600'
                    },
                    'xls': {
                        icon: 'fa-file-excel',
                        color: 'bg-green-600'
                    },
                    'xlsx': {
                        icon: 'fa-file-excel',
                        color: 'bg-green-600'
                    },
                    'ppt': {
                        icon: 'fa-file-powerpoint',
                        color: 'bg-orange-500'
                    },
                    'pptx': {
                        icon: 'fa-file-powerpoint',
                        color: 'bg-orange-500'
                    },
                    'zip': {
                        icon: 'fa-file-archive',
                        color: 'bg-gray-500'
                    },
                    'rar': {
                        icon: 'fa-file-archive',
                        color: 'bg-gray-500'
                    },
                    'png': {
                        icon: 'fa-file-image',
                        color: 'bg-purple-500'
                    },
                    'jpg': {
                        icon: 'fa-file-image',
                        color: 'bg-purple-500'
                    },
                    'jpeg': {
                        icon: 'fa-file-image',
                        color: 'bg-purple-500'
                    },
                };
                return iconMap[extension] || {
                    icon: 'fa-file',
                    color: 'bg-gray-400'
                };
            }

            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            function renderFilePreviews(category) {
                const fileListContainer = document.getElementById(`file-list-${category}`);
                const fileCountBadge = document.getElementById(`file-count-${category}`);
                const dropzonePlaceholder = document.querySelector(
                    `#upload-area-${category} .upload-drop-zone-placeholder`);

                fileListContainer.innerHTML = '';

                if (fileStores[category].length === 0) {
                    dropzonePlaceholder.querySelector('p').innerHTML =
                        `Drag files here or <span class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer browse-link">browse</span>`;
                } else {
                    dropzonePlaceholder.querySelector('p').innerHTML =
                        `Drag more files here or <span class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer browse-link">browse</span>`;
                }

                fileCountBadge.textContent = `${fileStores[category].length} Files`;

                fileStores[category].forEach((file, index) => {
                    const {
                        icon,
                        color
                    } = getFileIcon(file.name);
                    const fileRow = document.createElement('div');
                    fileRow.className = 'file-preview-item';
                    fileRow.innerHTML = `
                <div class="file-icon ${color} text-white">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <div class="file-details">
                    <span class="file-name" title="${file.name}">${file.name}</span>
                    <span class="file-size">${formatBytes(file.size)}</span>
                    <div class="progress-bar-container">
                        <div class="progress-bar"></div>
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
                Array.from(files).forEach(file => {
                    if (!fileStores[category].some(f => f.name === file.name && f.size === file.size)) {
                        fileStores[category].push(file);
                    }
                });
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
                    uploadArea.querySelector('input[type="file"]').click();
                }
            });

            document.querySelectorAll('.upload-card-container').forEach(container => {
                const category = container.dataset.category;
                const uploadArea = document.getElementById(`upload-area-${category}`);
                const fileInput = document.getElementById(`files-${category}-input`);

                fileInput.addEventListener('change', (e) => {
                    handleFiles(e.target.files, category);
                    e.target.value = null;
                });

                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.classList.add('drag-over');
                });
                uploadArea.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    uploadArea.classList.remove('drag-over');
                });
                uploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadArea.classList.remove('drag-over');
                    handleFiles(e.dataTransfer.files, category);
                });

                renderFilePreviews(category);
            });

            document.getElementById('uploadDrawingForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData();
                formData.append('customer', $('#customer').val());
                formData.append('model', $('#model').val());
                formData.append('partNo', $('#partNo').val());
                formData.append('docType', $('#docType').val());
                formData.append('category', $('#category').val());
                formData.append('revision', $('#revision').val());
                formData.append('_token', "{{ csrf_token() }}");
                fileStores['2d'].forEach(file => formData.append('files_2d[]', file));
                fileStores['3d'].forEach(file => formData.append('files_3d[]', file));
                fileStores['ecn'].forEach(file => formData.append('files_ecn[]', file));
                console.log("FormData is ready to be sent to the backend!");
                for (let [key, value] of formData.entries()) {
                    if (value instanceof File) {
                        console.log(`${key}: ${value.name}`);
                    } else {
                        console.log(`${key}: ${value}`);
                    }
                }
                alert(
                    "Form data has been prepared! Check the browser console (F12) to see what would be sent to the backend.");
            });
        });
    </script>

@endsection
