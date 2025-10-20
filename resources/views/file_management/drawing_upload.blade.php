@extends('layouts.app')
@section('title', 'Upload Drawing Package - PROMISE')
@section('header-title', 'File Manager/Upload Drawing Package')

@section('content')
    <div x-data="drawingUploader" x-init="init()" class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 font-sans">

        <div class="mb-8">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Upload New Drawing Package</h2>
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Fill in the metadata and upload all related drawing
                files in one go.</p>
        </div>

        <form @submit.prevent="submitForm" id="uploadDrawingForm" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 space-y-8">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            <i class="fa-solid fa-file-invoice mr-2 text-blue-500"></i>
                            Drawing Metadata
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">This information will determine the file
                            storage
                            location.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div wire:ignore>
                                <label for="customer"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                                <select id="customer" name="customer" class="mt-1 block w-full"></select>
                            </div>
                            <div wire:ignore>
                                <label for="model"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                <select id="model" name="model" class="mt-1 block w-full"></select>
                            </div>
                            <div wire:ignore>
                                <label for="partNo"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part
                                    No</label>
                                <select id="partNo" name="partNo" class="mt-1 block w-full"></select>
                            </div>
                            <div wire:ignore>
                                <label for="docType"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document
                                    Type</label>
                                <select id="docType" name="docType" class="mt-1 block w-full"></select>
                            </div>
                            <div wire:ignore>
                                <label for="category"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                <select id="category" name="category" class="mt-1 block w-full"></select>
                            </div>
                            <div wire:ignore>
                                <label for="partGroup"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>
                                <select id="partGroup" name="partGroup" class="mt-1 block w-full"></select>
                            </div>
                            <div class="sm:col-span-2" wire:ignore>
                                <label for="project_status"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project
                                    Status</label>
                                <select id="project_status" name="project_status" class="mt-1 block w-full"></select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="note"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision Note
                                    (optional)</label>
                                <textarea x-model="note" id="note" name="note" rows="3"
                                    class="mt-1 block w-full p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                    :disabled="!isMetadataFilled || draftSaved"></textarea>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">A short note that will be saved on
                                    the selected package revision.</p>
                            </div>
                        </div>
                    </div>

                    <div id="revision-options" x-show="isMetadataFilled" x-transition.opacity
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">

                        <div class="flex items-center mb-6">
                            <i class="fa-solid fa-history mr-3 text-blue-500 text-xl"></i>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revision Options</h3>
                        </div>

                        <div x-show="packageExists === null" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <div class="flex items-center justify-center py-8">
                                <i class="fa-solid fa-spinner fa-spin text-blue-500 text-3xl mr-4"></i>
                                <p class="text-base text-gray-600 dark:text-gray-300" x-html="detectionMessage"></p>
                            </div>
                        </div>

                        <div x-show="packageExists !== null" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

                            <div x-show="!packageExists">
                                <div class="p-4 rounded-md bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fa-solid fa-sparkles text-green-500 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-green-800 dark:text-green-200" x-html="detectionMessage"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="packageExists" class="space-y-5">
                                <div class="p-4 rounded-md bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-800">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fa-solid fa-box-archive text-yellow-500 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200" x-html="detectionMessage"></p>
                                        </div>
                                    </div>
                                </div>

                                <div id="mode-selection" class="space-y-3">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload Mode</label>

                                    <label class="relative block p-4 border rounded-lg cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/50 hover:border-blue-400"
                                        :class="uploadMode === 'new-rev' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/50 ring-2 ring-blue-300 dark:ring-blue-700' : 'border-gray-300 dark:border-gray-600'">
                                        <div class="flex items-center">
                                            <input type="radio" name="upload_mode" value="new-rev" x-model="uploadMode" class="peer h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                            <div class="ml-3 text-sm">
                                                <span class="font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                                    <i class="fa-solid fa-plus mr-2 text-gray-500"></i>
                                                    Create new revision (rev <span x-text="suggested_rev" class="font-semibold ml-1"></span>)
                                                </span>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative block p-4 border rounded-lg cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/50 hover:border-blue-400"
                                        :class="uploadMode === 'existing' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/50 ring-2 ring-blue-300 dark:ring-blue-700' : 'border-gray-300 dark:border-gray-600'">
                                        <div class="flex items-center">
                                            <input type="radio" name="upload_mode" value="existing" x-model="uploadMode" class="peer h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                            <div class="ml-3 text-sm">
                                                <span class="font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                                    <i class="fa-solid fa-layer-group mr-2 text-gray-500"></i>
                                                    Upload to existing revision
                                                </span>
                                            </div>
                                        </div>

                                        <div x-show="uploadMode === 'existing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                            class="mt-4 pl-8 space-y-4 border-l-2 border-gray-200 dark:border-gray-700 ml-1">

                                            <div id="existing-rev-select" wire:ignore>
                                                <label for="target_rev" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Revision</label>
                                                <select id="target_rev" name="target_rev" class="select2-revision w-full"></select>
                                            </div>

                                            <div id="conflict-options">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">If File Name Conflicts</label>
                                                <div class="flex flex-col space-y-2">
                                                    <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                                        <input type="radio" name="conflict_mode" value="append" x-model="conflictMode" class="mr-2 text-blue-600 focus:ring-blue-500">
                                                        <span class="text-sm text-gray-800 dark:text-gray-200">Append suffix (do not replace)</span>
                                                    </label>
                                                    <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                                        <input type="radio" name="conflict_mode" value="replace" x-model="conflictMode" class="mr-2 text-blue-600 focus:ring-blue-500">
                                                        <span class="text-sm text-gray-800 dark:text-gray-200">Replace existing file</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 h-96 lg:h-full flex flex-col">
                        <div class="p-6 pb-4 flex-shrink-0">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <i class="fa-solid fa-clipboard-list mr-2 text-blue-500"></i>
                                Activity Log
                            </h4>
                        </div>
                        <div id="activity-log" class="relative flex-1 min-h-0">
                            <div id="log-content-wrapper" class="absolute inset-0 p-6 overflow-y-auto space-y-4">
                                <p class="italic text-center pt-8">No activity yet. This panel will display recent package
                                    activities and approvals.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="drawing-files-section" :class="{ 'opacity-50 pointer-events-none': !isMetadataFilled }"
                class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-opacity">

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1 flex items-center">
                        <i class="fa-solid fa-file-arrow-up mr-3 text-blue-500 text-xl"></i>
                        Drawing Files
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Drag & drop files into their respective categories below or click to browse.</p>
                </div>

                <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Enable Categories</label>
                    <div class="flex items-center space-x-6">
                        <template x-for="cat in availableCategories" :key="cat.id">
                            <label :for="`enable_${cat.id}`" class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" :id="`enable_${cat.id}`" :value="cat.id" x-model="enabledCategories" class="sr-only peer">
                                    <div class="block bg-gray-200 dark:bg-gray-600 w-12 h-7 rounded-full transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition transform peer-checked:translate-x-full peer-checked:bg-blue-600"></div>
                                </div>
                                <div class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="cat.name"></div>
                            </label>
                        </template>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Only enabled categories will have folders created and accept files.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <template x-for="cat in availableCategories" :key="cat.id">
                        <div x-show="isCategoryEnabled(cat.id)" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <div class="upload-card-container border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm h-full flex flex-col bg-white dark:bg-gray-800"
                                :data-category="cat.id">

                                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center flex-shrink-0">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                                        <i :class="cat.icon" class="mr-2 text-gray-500 dark:text-gray-400"></i>
                                        <span x-text="cat.name"></span>
                                    </h4>
                                    <span class="text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-0.5 rounded-full">
                                        <span x-text="fileStores[cat.id].length">0</span> Files
                                    </span>
                                </div>

                                <div class="p-4 upload-area flex-grow flex flex-col" :id="`upload-area-${cat.id}`"
                                    @dragover.prevent="handleDragOver($event, cat.id)"
                                    @dragleave.prevent="handleDragLeave($event, cat.id)"
                                    @drop.prevent="handleDrop($event, cat.id)">

                                    <input type="file" :id="`files-${cat.id}-input`" multiple class="hidden" @change="handleFileSelect($event, cat.id)">

                                    <div class="upload-drop-zone-placeholder mb-4 flex-shrink-0" @click="browseFiles(cat.id)">
                                        <div class="text-center">
                                            <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 dark:text-gray-500"></i>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Drag files here or <span class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer">browse</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="file-list-container flex-grow min-h-0" :id="`file-list-${cat.id}`">
                                        <template x-for="(fileWrapper, index) in fileStores[cat.id]" :key="index">
                                            <div class="file-preview-item flex items-center space-x-3 p-2 rounded-md" :data-file-index="index">
                                                <div class="file-icon text-white w-10 h-10 flex items-center justify-center rounded flex-shrink-0" :class="getFileIcon(fileWrapper.name).color">
                                                    <i class="fa-solid" :class="getFileIcon(fileWrapper.name).icon"></i>
                                                </div>
                                                <div class="file-details flex-1 min-w-0">
                                                    <p class="file-name text-sm text-gray-800 dark:text-gray-200 truncate" :title="fileWrapper.name" x-text="fileWrapper.name"></p>
                                                    <p class="file-size text-xs text-gray-500 dark:text-gray-400" x-text="formatBytes(fileWrapper.size)"></p>

                                                    <div x-show="fileWrapper.status === 'uploading' || fileWrapper.status === 'retrying'" class="progress-bar-container mt-1">
                                                        <div class="progress-bar" :style="`width: ${fileWrapper.progress || 0}%`"></div>
                                                    </div>

                                                    <div class="status-container mt-1 h-5 flex items-center">
                                                        <template x-if="fileWrapper.status === 'uploaded' || fileWrapper.uploaded"><i class="fa-solid fa-check-circle text-green-500"></i></template>
                                                        <template x-if="fileWrapper.status === 'failed'"><i class="fa-solid fa-circle-exclamation text-red-500"></i></template>
                                                        <template x-if="fileWrapper.status === 'uploading' || fileWrapper.status === 'retrying'"><i class="fa-solid fa-spinner fa-spin text-blue-500"></i></template>
                                                        <p class="status-text text-xs ml-1.5" :class="{
                                                            'text-green-600 dark:text-green-400': fileWrapper.status === 'uploaded' || fileWrapper.uploaded,
                                                            'text-red-600 dark:text-red-400': fileWrapper.status === 'failed',
                                                            'text-blue-600 dark:text-blue-400': fileWrapper.status === 'uploading' || fileWrapper.status === 'retrying',
                                                            'text-gray-500 dark:text-gray-400': fileWrapper.status === 'added' || !fileWrapper.status
                                                        }" x-text="fileWrapper.statusText || 'Added'"></p>
                                                    </div>
                                                </div>

                                                <div class="flex-shrink-0 ml-2">
                                                    <button type="button" x-show="fileWrapper.status === 'failed'" class="action-btn" @click.prevent="uploadFile(fileWrapper, cat.id)" title="Retry Upload">
                                                        <i class="fa-solid fa-rotate-right text-blue-500 hover:text-blue-700"></i>
                                                    </button>
                                                    <button type="button" x-show="fileWrapper.status !== 'uploading' && fileWrapper.status !== 'retrying'" class="action-btn" @click.prevent="removeFile(cat.id, index)" title="Remove File">
                                                        <i class="fa-solid fa-trash-can text-red-500 hover:text-red-700"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end pt-4 gap-4">
                <button type="button" x-show="draftSaved" @click="requestApproval"
                    :disabled="!draftSaved || approvalRequested" class="px-4 py-2 bg-yellow-500 text-white rounded-md"
                    :class="{ 'opacity-50 cursor-not-allowed': !draftSaved || approvalRequested }">
                    <span x-show="!approvalRequested">Request to Approval</span>
                    <span x-show="approvalRequested">Requested</span>
                </button>
                <button type="submit" id="submit-button"
                    :disabled="!isMetadataFilled || isUploading || (draftSaved && !hasNewFiles)"
                    class="inline-flex items-center gap-2 justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                    :class="{ 'opacity-50 cursor-not-allowed': !isMetadataFilled || isUploading || (draftSaved && !
                        hasNewFiles) }">
                    <i class="fa-solid" :class="{ 'fa-upload': !isUploading, 'fa-spinner fa-spin': isUploading }"></i>
                    <span x-text="isUploading ? 'Uploading...' : 'Upload to Draft'"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Toast functions (remains global)
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
        window.toastSuccess = (title = 'Success', text = 'Operation completed successfully.') => renderToast({
            icon: 'success',
            title,
            text
        });
        window.toastError = (title = 'Error', text = 'An error occurred.') => {
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
        };
        window.toastWarning = (title = 'Warning', text = 'Please check your data.') => renderToast({
            icon: 'warning',
            title,
            text
        });
        window.toastInfo = (title = 'Info', text = '') => renderToast({
            icon: 'info',
            title,
            text
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('drawingUploader', () => ({
                // --- STATE ---
                customer: null,
                model: null,
                partNo: null,
                docType: null,
                category: null,
                partGroup: null,
                project_status: null,
                note: '',

                packageExists: null, // null (initial), true, false
                revisions: [],
                latest_rev: null,
                suggested_rev: '0',
                detectionMessage: 'Please fill out the drawing metadata to see revision options.',
                uploadMode: 'new-rev',
                conflictMode: 'append',

                availableCategories: [{
                        id: '2d',
                        name: '2D Drawings',
                        icon: 'fa-solid fa-drafting-compass'
                    },
                    {
                        id: '3d',
                        name: '3D Models',
                        icon: 'fa-solid fa-cubes'
                    },
                    {
                        id: 'ecn',
                        name: 'ECN / Documents',
                        icon: 'fa-solid fa-file-lines'
                    }
                ],
                enabledCategories: ['2d', '3d', 'ecn'],
                fileStores: {
                    '2d': [],
                    '3d': [],
                    'ecn': []
                },

                isUploading: false,
                draftSaved: false,
                approvalRequested: false,
                savedPackageId: null,
                savedRevisionId: null,
                savedRevisionNo: null,
                hasNewFiles: false,

                // --- COMPUTED ---
                get isMetadataFilled() {
                    return this.customer && this.model && this.partNo && this.docType && this
                        .category && this.partGroup && this.project_status;
                },

                // --- METHODS ---
                init() {
                    this.initSelect2('customer', 'Select Customer',
                        "{{ route('upload.getCustomerData') }}");
                    this.disableSelect2('model', 'Select Customer First');
                    this.disableSelect2('partNo', 'Select Model First');
                    this.disableSelect2('docType', 'Select Part No First');
                    this.disableSelect2('category', 'Select Document Group First');
                    this.disableSelect2('partGroup', 'Select Sub Category First');
                    this.disableSelect2('project_status', 'Select Part Group First');

                    $('#target_rev').select2({
                        width: '100%',
                        placeholder: 'Select Revision'
                    });

                    this.$watch('customer', (val) => {
                        this.resetAndDisable(['model', 'partNo', 'docType', 'category',
                            'partGroup', 'project_status'
                        ]);
                        if (val) this.initSelect2('model', 'Select Model',
                            "{{ route('upload.getModelData') }}", {
                                customer_id: val
                            });
                    });

                    this.$watch('model', (val) => {
                        this.resetAndDisable(['partNo', 'docType', 'category', 'partGroup',
                            'project_status'
                        ]);
                        if (val) this.initSelect2('partNo', 'Select Part No',
                            "{{ route('upload.getProductData') }}", {
                                model_id: val
                            });
                    });

                    this.$watch('partNo', (val) => {
                        this.resetAndDisable(['docType', 'category', 'partGroup',
                            'project_status'
                        ]);
                        if (val) this.initSelect2('docType', 'Select Document Type',
                            "{{ route('upload.getDocumentGroupData') }}");
                    });

                    this.$watch('docType', (val) => {
                        this.resetAndDisable(['category', 'partGroup', 'project_status']);
                        if (val) this.initSelect2('category', 'Select Category',
                            "{{ route('upload.getSubCategoryData') }}", {
                                document_group_id: val
                            });
                    });

                    this.$watch('category', (val) => {
                        this.resetAndDisable(['partGroup', 'project_status']);
                        if (val) this.initSelect2('partGroup', 'Select Part Group',
                            "{{ route('upload.getPartGroupData') }}", {
                                customer_id: this.customer,
                                model_id: this.model
                            });
                    });

                    this.$watch('partGroup', (val) => {
                        this.resetAndDisable(['project_status']);
                        if (val) this.initSelect2('project_status', 'Select Project Status',
                            "{{ route('upload.getProjectStatusData') }}");
                    });

                    this.$watch('isMetadataFilled', (isFilled) => {
                        if (isFilled) {
                            this.checkExistingFile();
                            this.fetchActivityLogs();
                        } else {
                            this.packageExists = null;
                            this.detectionMessage =
                                'Please fill out the drawing metadata to see revision options.';
                        }
                    });

                    this.fetchActivityLogs();
                    this.$watch('enabledCategories', (newValue, oldValue) => {
                        const removed = oldValue.filter(x => !newValue.includes(x));
                        removed.forEach(catId => {
                            this.fileStores[catId] = [];
                        });
                    });
                },

                initSelect2(propName, placeholder, url, additionalData = {}) {
                    const el = $(`#${propName}`);
                    if (el.hasClass("select2-hidden-accessible")) {
                        el.select2('destroy');
                    }

                    el.prop('disabled', false).select2({
                        width: '100%',
                        placeholder: placeholder,
                        ajax: {
                            url: url,
                            method: 'POST',
                            dataType: 'json',
                            delay: 250,
                            data: params => ({
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                ...additionalData
                            }),
                            processResults: data => ({
                                results: data.results
                            })
                        }
                    }).on('change', (e) => {
                        this[propName] = e.target.value;
                    });
                },

                resetAndDisable(propNames) {
                    propNames.forEach(prop => {
                        this[prop] = null;
                        const el = $(`#${prop}`);
                        if (el.hasClass("select2-hidden-accessible")) el.select2('destroy');
                        el.val(null).trigger('change');
                    });
                    if (propNames.includes('model')) this.disableSelect2('model',
                        'Select Customer First');
                    if (propNames.includes('partNo')) this.disableSelect2('partNo',
                        'Select Model First');
                    if (propNames.includes('docType')) this.disableSelect2('docType',
                        'Select Part No First');
                    if (propNames.includes('category')) this.disableSelect2('category',
                        'Select Document Group First');
                    if (propNames.includes('partGroup')) this.disableSelect2('partGroup',
                        'Select Sub Category First');
                    if (propNames.includes('project_status')) this.disableSelect2('project_status',
                        'Select Part Group First');

                    this.packageExists = null;
                },

                disableSelect2(propName, placeholder) {
                    $(`#${propName}`).prop('disabled', true).select2({
                        width: '100%',
                        placeholder: placeholder
                    });
                },

                checkExistingFile() {
                    if (!this.isMetadataFilled) return;

                    this.packageExists = null;
                    this.detectionMessage = 'Checking for existing revisions...';

                    $.ajax({
                        url: "{{ route('upload.drawing.check') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            customer: this.customer,
                            model: this.model,
                            partNo: this.partNo,
                            docType: this.docType,
                            category: this.category,
                            partGroup: this.partGroup,
                        },
                        success: (res) => {
                            this.packageExists = res.exists;
                            if (!res.exists) {
                                this.detectionMessage =
                                    '<strong>New document</strong> will be created as <strong>rev0</strong>.';
                                this.suggested_rev = '0';
                                this.uploadMode = 'new-rev';
                            } else {
                                this.detectionMessage =
                                    `Found <strong>${res.revisions.length} revisions</strong>, latest: <strong>rev${res.latest_rev}</strong>.`;
                                this.suggested_rev = res.suggested_rev;
                                this.revisions = res.revisions;
                                this.latest_rev = res.latest_rev;
                                this.uploadMode = 'new-rev';

                                const $targetRev = $('#target_rev');
                                $targetRev.empty();
                                res.revisions.forEach(rev => {
                                    $targetRev.append(new Option(`rev${rev}`, rev,
                                        false, false));
                                });
                                $targetRev.trigger('change');
                            }
                        },
                        error: (xhr) => {
                            this.packageExists = null;
                            this.detectionMessage = '<span class="text-red-500">Failed to check revision status. Please try again.</span>';
                            toastError('Error', xhr.responseJSON?.message ||
                                'Failed to check revision status.');
                        }
                    });
                },

                isCategoryEnabled(catId) {
                    return this.enabledCategories.includes(catId);
                },
                handleFileSelect(event, category) {
                    this.addFiles(event.target.files, category);
                    event.target.value = null;
                },
                handleDrop(event, category) {
                    this.addFiles(event.dataTransfer.files, category);
                    this.handleDragLeave(event, category);
                },
                handleDragOver(event, category) {
                    const area = document.getElementById(`upload-area-${category}`);
                    if (area) area.classList.add('drag-over');
                },
                handleDragLeave(event, category) {
                    const area = document.getElementById(`upload-area-${category}`);
                    if (area) area.classList.remove('drag-over');
                },
                browseFiles(category) {
                    document.getElementById(`files-${category}-input`).click();
                },
                addFiles(files, category) {
                    let addedCount = 0;
                    Array.from(files).forEach(file => {
                        if (!this.fileStores[category].some(f => f.name === file.name && f
                                .size === file.size)) {
                            this.fileStores[category].push({
                                file: file,
                                name: file.name,
                                size: file.size,
                                uploaded: false,
                                status: 'added',
                                statusText: 'Added',
                                progress: 0
                            });
                            addedCount++;
                        }
                    });
                    if (addedCount > 0) {
                        toastInfo('Files Added',
                            `Added ${addedCount} file${addedCount > 1 ? 's' : ''} to ${category.toUpperCase()} category.`
                            );
                        if (this.draftSaved) {
                            this.hasNewFiles = true;
                            this.approvalRequested = false;
                        }
                    }
                },
                removeFile(category, index) {
                    this.fileStores[category].splice(index, 1);
                    if (this.draftSaved) {
                        this.hasNewFiles = true;
                    }
                },
                getFileIcon(fileName) {
                    const ext = fileName.split('.').pop().toLowerCase();
                    const map = {
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
                    return map[ext] || {
                        icon: 'fa-file',
                        color: 'bg-gray-400'
                    };
                },
                formatBytes(bytes, decimals = 2) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const dm = decimals < 0 ? 0 : decimals;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
                },
                submitForm() {
                    const totalFiles = this.enabledCategories.reduce((acc, cat) => acc + this.fileStores[cat].filter(f => !f.uploaded).length, 0);

                    if (totalFiles === 0) {
                        if (this.draftSaved) {
                            toastInfo('No new files', 'There are no new files to upload.');
                        } else {
                            toastWarning('Warning', 'Please select at least one file to upload.');
                        }
                        return;
                    }

                    if (this.uploadMode === 'existing' && !$('#target_rev').val()) {
                        toastWarning('Warning', 'Please select an existing revision.');
                        return;
                    }

                    this.isUploading = true;
                    this.uploadBatch();
                },
                uploadBatch() {
                    const formData = new FormData();
                    const filesToUpload = [];

                    formData.append('mode', this.uploadMode);
                    formData.append('revision', this.uploadMode === 'existing' ? $('#target_rev').val() : this.suggested_rev);
                    if (this.uploadMode === 'existing') {
                        formData.append('target_rev', $('#target_rev').val());
                        formData.append('conflict', this.conflictMode);
                    }
                    formData.append('customer', this.customer);
                    formData.append('model', this.model);
                    formData.append('partNo', this.partNo);
                    formData.append('docType', this.docType);
                    formData.append('category', this.category || '');
                    formData.append('partGroup', this.partGroup);
                    formData.append('project_status', this.project_status);
                    formData.append('note', this.note || '');
                    this.enabledCategories.forEach(c => formData.append('enabled_categories[]', c));
                    formData.append('as_draft', '1');
                    formData.append('_token', "{{ csrf_token() }}");

                    this.enabledCategories.forEach(cat => {
                        this.fileStores[cat].forEach(fileWrapper => {
                            if (!fileWrapper.uploaded) {
                                formData.append(`files_${cat}[]`, fileWrapper.file, fileWrapper.name);
                                filesToUpload.push(fileWrapper);
                            }
                        });
                    });

                    filesToUpload.forEach(fw => {
                        fw.status = 'uploading';
                        fw.statusText = 'Uploading...';
                        fw.progress = 0;
                    });

                    $.ajax({
                        url: "{{ route('upload.drawing.store') }}",
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        xhr: () => {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener('progress', (evt) => {
                                if (evt.lengthComputable) {
                                    const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                    filesToUpload.forEach(fw => {
                                        fw.progress = percentComplete;
                                    });
                                }
                            }, false);
                            return xhr;
                        },
                        success: (res) => {
                            filesToUpload.forEach(fw => {
                                fw.progress = 100;
                                fw.uploaded = true;
                                fw.status = 'uploaded';
                                fw.statusText = 'Uploaded';
                            });

                            if (res.package_id) this.savedPackageId = res.package_id;
                            if (res.revision_id) this.savedRevisionId = res.revision_id;
                            if (res.rev !== undefined) this.savedRevisionNo = res.rev;

                            toastSuccess('Success', `Successfully uploaded ${filesToUpload.length} file(s).`);

                            this.draftSaved = true;
                            this.hasNewFiles = false;
                            this.approvalRequested = false;
                            this.enableMetadataEditing(false);
                            this.fetchActivityLogs();
                        },
                        error: (xhr) => {
                            filesToUpload.forEach(fw => {
                                fw.status = 'failed';
                                fw.statusText = 'Failed';
                                fw.progress = 0;
                            });

                            toastError('Upload Failed', xhr.responseJSON?.message || 'A server error occurred. The operation was rolled back.');
                        },
                        complete: () => {
                            this.isUploading = false;
                        }
                    });
                },
                enableMetadataEditing(allow) {
                    const selectors = ['#customer', '#model', '#partNo', '#docType', '#category',
                        '#partGroup', '#project_status'
                    ];
                    selectors.forEach(s => {
                        $(s).prop('disabled', !allow).trigger('change.select2');
                    });
                },
                requestApproval() {
                    if (!this.draftSaved || !this.savedPackageId) {
                        toastWarning('No Draft', 'No draft saved yet to request approval for.');
                        return;
                    }
                    this.approvalRequested = true;
                    $.ajax({
                        url: "{{ route('upload.drawing.request-approval') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            package_id: this.savedPackageId,
                            revision_no: this.savedRevisionNo
                        },
                        success: (res) => {
                            toastSuccess('Requested', 'Draft set to pending for approval.');
                            this.fetchActivityLogs();
                        },
                        error: (xhr) => {
                            toastError('Error', xhr.responseJSON?.message ||
                                'Failed to request approval.');
                            this.approvalRequested = false;
                        }
                    });
                },
                fetchActivityLogs() {
                    const data = {
                        _token: "{{ csrf_token() }}"
                    };
                    if (this.isMetadataFilled) {
                        Object.assign(data, {
                            customer: this.customer,
                            model: this.model,
                            partNo: this.partNo,
                            docType: this.docType,
                            category: this.category,
                            partGroup: this.partGroup
                        });
                    }
                    $.ajax({
                        url: "{{ route('upload.drawing.activity-logs') }}",
                        method: 'POST',
                        data: data,
                        success: (res) => renderActivityLogs(res.logs || []),
                        error: (xhr) => console.warn('Failed to load activity logs', xhr
                            .responseText)
                    });
                }
            }));
        });

        function renderActivityLogs(logs) {
        const container = $('#log-content-wrapper');
            container.empty();
            if (!logs || logs.length === 0) {
                container.html(
                    '<p class="italic text-center pt-8">No activity yet. This panel will display recent package activities and approvals.</p>'
                    );
                return;
            }
            logs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            logs.forEach((l, index) => {
                const m = l.meta || {};
                const revisionNo = m.revision_no !== undefined ? `rev${m.revision_no}` : '';
                const activity = {
                    UPLOAD: {
                        icon: 'fa-upload',
                        color: 'bg-blue-500',
                        title: 'Package Uploaded'
                    },
                    REQUEST_APPROVAL: {
                        icon: 'fa-paper-plane',
                        color: 'bg-yellow-500',
                        title: 'Approval Requested'
                    },
                    APPROVE: {
                        icon: 'fa-check-double',
                        color: 'bg-green-500',
                        title: 'Package Approved'
                    },
                    REJECT: {
                        icon: 'fa-times-circle',
                        color: 'bg-red-500',
                        title: 'Package Rejected'
                    },
                    default: {
                        icon: 'fa-info-circle',
                        color: 'bg-gray-500',
                        title: l.activity_code
                    }
                };
                const {
                    icon,
                    color,
                    title
                } = activity[l.activity_code] || activity.default;
                const timeAgo = l.created_at ? formatTimeAgo(new Date(l.created_at)) : '';
                const userLabel = l.user_name ? `${l.user_name}` : (l.user_id ? `User #${l.user_id}` : 'System');
                let detailsHtml = '';
                if (l.activity_code === 'UPLOAD') {
                    const details = {
                        "Part No": m.part_no,
                        "Customer": m.customer_code,
                        "Model": m.model_name,
                        "Doc Group": m.doctype_group,
                        "Sub-Category": m.doctype_subcategory,
                        "Note": m.note
                    };
                    detailsHtml = Object.entries(details).filter(([_, val]) => val).map(([key, val]) =>
                        `<div class="text-xs"><span class="font-semibold text-gray-600 dark:text-gray-400">${key}:</span> <span class="text-gray-800 dark:text-gray-200">${val}</span></div>`
                        ).join('');
                } else if (m.note) {
                    detailsHtml = `<p class="text-sm italic text-gray-600 dark:text-gray-400">"${m.note}"</p>`;
                }
                const isLast = index === logs.length - 1;
                const el = $(`
                    <div class="relative">
                        ${!isLast ? '<div class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gray-300 dark:bg-gray-700"></div>' : ''}
                        <div class="relative flex items-start space-x-4 pb-8">
                            <div class="flex-shrink-0">
                                <span class="flex items-center justify-center h-10 w-10 rounded-full ${color} text-white shadow-md z-10"><i class="fa-solid ${icon}"></i></span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                        ${title}
                                        ${revisionNo ? `<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">${revisionNo}</span>` : ''}
                                    </p>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">${timeAgo}</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">by <strong>${userLabel}</strong></p>
                                ${detailsHtml ? `<div class="mt-2 space-y-1 p-3 bg-gray-100 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700/50">${detailsHtml}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `);
                container.append(el);
            });
        }

        function formatTimeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + "y ago";
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + "mo ago";
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + "d ago";
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + "h ago";
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + "m ago";
            return Math.floor(seconds) + "s ago";
        }
    </script>
@endsection


    <style>
        .opacity-50 {
            opacity: 0.5;
        }

        .cursor-not-allowed {
            cursor: not-allowed;
        }

        .pointer-events-none {
            pointer-events: none;
        }

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
            cursor: pointer;
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
