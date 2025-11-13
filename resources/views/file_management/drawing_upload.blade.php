@extends('layouts.app')
@section('title', 'Upload Drawing Package - PROMISE')
@section('header-title', 'File Manager/Upload Drawing Package')

@section('content')
    <div x-data="drawingUploader" x-init="init()" class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 font-sans">

        <div class="mb-8">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl"
                    x-text="isCreatingNewRevision ? 'Create New Revision' : 'Drawing Package Details'">Upload New Drawing
                    Package</h2>
                <div class="flex items-center gap-2">

                    <button type="button" @click.prevent="startNewRevision"
                        x-show="savedRevisionId && isReadOnly && !isCreatingNewRevision"
                        class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-green-300 text-sm font-medium rounded-md shadow-sm text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-700 dark:text-green-100 dark:border-green-600 dark:hover:bg-green-600 dark:focus:ring-offset-gray-800">
                        <i class="fa-solid fa-plus-circle"></i>
                        Create New Revision
                    </button>

                    <button type="button" onclick="reviseConfirm()"
                        x-show="revisionStatus === 'pending' || revisionStatus === 'rejected'"
                        class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                        <i class="fa-solid fa-file-pen"></i>
                    </button>

                    <button type="button" @click.prevent="deleteCurrentRevision"
                        x-show="draftSaved && revisionStatus === 'draft' && !isReadOnly"
                        class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md shadow-sm text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900 dark:text-red-300 dark:border-red-700 dark:hover:bg-red-800 dark:focus:ring-offset-gray-800">
                        <i class="fa-solid fa-trash-can"></i>
                        Delete Draft
                    </button>

                    <a href="{{ url('file-manager.upload') }}"
                        class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                        <i class="fa-solid fa-arrow-left"></i>
                        {{-- Back --}}
                    </a>
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Fill in the metadata and upload all related drawing
                files in one go.</p>
        </div>

        <form @submit.prevent="submitForm" id="uploadDrawingForm" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-12 space-y-8">
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
                            <div>
                                <label for="customer"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                                <select id="customer" name="customer" class="mt-1 block w-full"
                                    :disabled="isMetadataLocked"></select>
                            </div>
                            <div>
                                <label for="model"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                <select id="model" name="model" class="mt-1 block w-full"
                                    :disabled="isMetadataLocked || !customer"></select>
                            </div>
                            <div>
                                <label for="partNo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part
                                    No</label>
                                <select id="partNo" name="partNo" class="mt-1 block w-full"
                                    :disabled="isMetadataLocked || !model"></select>
                            </div>
                            <div>
                                <label for="docType"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document
                                    Type</label>
                                <select id="docType" name="docType" class="mt-1 block w-full"
                                    :disabled="isMetadataLocked || !partNo"></select>
                            </div>
                            <div>
                                <label for="category"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                <select id="category" name="category" class="mt-1 block w-full"
                                    :disabled="isMetadataLocked || !docType"></select>
                            </div>
                            <div>
                                <label for="partGroup"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part Group</label>
                                <select id="partGroup" name="partGroup" class="mt-1 block w-full"
                                    :disabled="isMetadataLocked || !category"></select>
                            </div>

                            <div class="sm:col-span-2 grid grid-cols-2 gap-6">
                                <div>
                                    <label for="ecn_no"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">ECN
                                        Number</label>
                                    <input type="text" x-model.debounce.500ms="ecn_no" id="ecn_no" name="ecn_no"
                                        class="mt-1 block w-full p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                        :disabled="isReadOnly || !isMetadataFilled">
                                </div>
                                <div>
                                    <label for="receipt_date"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt
                                        Date</label>
                                    <input type="date" x-model="receipt_date" id="receipt_date" name="receipt_date"
                                        class="mt-1 block w-full p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                        :disabled="isReadOnly || !isMetadataFilled">
                                </div>
                            </div>

                            <div class="sm:col-span-2" x-show="customerHasLabels" x-transition>
                                <label for="revision_label_id"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision
                                    Label</label>
                                <select id="revision_label_id" name="revision_label_id" class="mt-1 block w-full"
                                    :disabled="isReadOnly || !isMetadataFilled"></select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Finish Good Drawing
                                </label>
                                <div class="flex items-center mt-2">
                                    <label for="is_finish" class="relative flex items-center cursor-pointer">
                                        <input type="checkbox" id="is_finish" x-model="is_finish" class="sr-only peer"
                                            :disabled="isReadOnly || !isMetadataFilled">
                                        <div class="block bg-gray-200 dark:bg-gray-600 w-12 h-7 rounded-full transition">
                                        </div>
                                        <div
                                            class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition transform peer-checked:translate-x-full peer-checked:bg-blue-600">
                                        </div>
                                    </label>
                                    <div class="ml-3 text-sm" :class="{
                                                                                        'text-gray-900 dark:text-gray-100 font-semibold': is_finish,
                                                                                        'text-gray-500 dark:text-gray-400': !is_finish
                                                                                    }">
                                        <span x-text="is_finish ? 'Yes' : 'No'"></span>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Mark if this is a revision of
                                    the <strong>Finish Good (FG)</strong>.</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="note"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision Note
                                    (optional)</label>
                                <textarea x-model="note" id="note" name="note" rows="3"
                                    class="mt-1 block w-full p-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                    :disabled="isReadOnly || !isMetadataFilled"></textarea>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">A short note that will be saved on
                                    the selected package revision.</p>
                            </div>

                        </div>
                    </div>

                    <div x-show="isReadOnly && revisionStatus"
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <i class="fa-solid fa-info-circle mr-3 text-blue-500 text-xl"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Current Status</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">This revision is currently <strong
                                        class="font-semibold" :class="{
                                                                                                                            'text-green-600 dark:text-green-400': revisionStatus === 'approved',
                                                                                                                            'text-red-600 dark:text-red-400': revisionStatus === 'rejected',
                                                                                                                            'text-yellow-600 dark:text-yellow-400': revisionStatus === 'pending',
                                                                                                                            'text-gray-700 dark:text-gray-300': revisionStatus === 'draft'
                                                                                                                        }"
                                        x-text="revisionStatus ? revisionStatus.charAt(0).toUpperCase() + revisionStatus.slice(1) : ''"></strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="revision-options" x-show="isFormReady && !isReadOnly" x-transition.opacity
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">

                        <div class="flex items-center mb-6">
                            <i class="fa-solid fa-history mr-3 text-blue-500 text-xl"></i>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revision Status</h3>
                        </div>

                        <div x-show="revisionCheck.status === 'checking'" x-transition>
                            <div class="flex items-center justify-center py-8">
                                <i class="fa-solid fa-spinner fa-spin text-blue-500 text-3xl mr-4"></i>
                                <p class="text-base text-gray-600 dark:text-gray-300">Checking ECN status...</p>
                            </div>
                        </div>

                        <div x-show="revisionCheck.status === 'locked'" x-transition>
                            <div
                                class="p-4 rounded-md bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fa-solid fa-lock text-red-500 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800 dark:text-red-200"
                                            x-html="revisionCheck.message"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="revisionCheck.status === 'edit_draft'" x-transition>
                            <div
                                class="p-4 rounded-md bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fa-solid fa-pencil-alt text-blue-500 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Editing existing
                                            draft for ECN <strong x-text="ecn_no"></strong>. Revision: <strong
                                                x-text="revisionCheck.revision ? revisionCheck.revision.revision_no : ''"></strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="revisionCheck.status === 'create_new'" x-transition>
                            <div
                                class="p-4 rounded-md bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fa-solid fa-sparkles text-green-500 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800 dark:text-green-200">This will create a
                                            new revision: <strong>Rev <span x-text="revisionCheck.next_rev"></span></strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div id="drawing-files-section" :class="{ 'opacity-50 pointer-events-none': !isMetadataFilled || isReadOnly }"
                class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-opacity">

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1 flex items-center">
                        <i class="fa-solid fa-file-arrow-up mr-3 text-blue-500 text-xl"></i>
                        Drawing Files
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Drag & drop files into their respective categories
                        below or click to browse.</p>
                </div>

                <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Enable Categories</label>
                    <div class="flex items-center space-x-6">
                        <template x-for="cat in availableCategories" :key="cat.id">
                            <label :for="`enable_${cat.id}`" class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" :id="`enable_${cat.id}`" :value="cat.id"
                                        x-model="enabledCategories" class="sr-only peer" :disabled="isReadOnly">
                                    <div class="block bg-gray-200 dark:bg-gray-600 w-12 h-7 rounded-full transition"></div>
                                    <div
                                        class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition transform peer-checked:translate-x-full peer-checked:bg-blue-600">
                                    </div>
                                </div>
                                <div class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="cat.name"></div>
                            </label>
                        </template>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Only enabled categories will have folders
                        created and accept files.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <template x-for="cat in availableCategories" :key="cat.id">
                        <div x-show="isCategoryEnabled(cat.id)" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <div class="upload-card-container border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm h-full flex flex-col bg-white dark:bg-gray-800"
                                :data-category="cat.id">

                                <div
                                    class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center flex-shrink-0">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                                        <i :class="cat.icon" class="mr-2 text-gray-500 dark:text-gray-400"></i>
                                        <span x-text="cat.name"></span>
                                    </h4>
                                    <span
                                        class="text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-0.5 rounded-full">
                                        <span x-text="fileStores[cat.id].length">0</span> Files
                                    </span>
                                </div>

                                <div class="p-4 upload-area flex-grow flex flex-col" :id="`upload-area-${cat.id}`"
                                    @dragover.prevent="handleDragOver($event, cat.id)"
                                    @dragleave.prevent="handleDragLeave($event, cat.id)"
                                    @drop.prevent="handleDrop($event, cat.id)">

                                    <input type="file" :id="`files-${cat.id}-input`" multiple class="hidden"
                                        @change="handleFileSelect($event, cat.id)">

                                    <div class="upload-drop-zone-placeholder mb-4 flex-shrink-0"
                                        @click="!isReadOnly ? browseFiles(cat.id) : null">
                                        <div class="text-center">
                                            <i
                                                class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 dark:text-gray-500"></i>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Drag files here or <span
                                                    class="font-semibold text-blue-600 dark:text-blue-400 cursor-pointer">browse</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="file-list-container flex-grow min-h-0" :id="`file-list-${cat.id}`">

                                        <h5 x-show="fileStores[cat.id].some(f => !f.uploaded)"
                                            class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-2 mb-1 px-2"
                                            style="border-bottom: 1px dashed #d1d5db; padding-bottom: 4px; margin-bottom: 8px;">
                                            New Files
                                        </h5>
                                        <template x-for="fileWrapper in fileStores[cat.id].filter(f => !f.uploaded)"
                                            :key="fileWrapper._uid">
                                            <div class="file-preview-item flex items-center space-x-3 p-2 rounded-md">
                                                <div x-data="{ icon: getIcon(fileWrapper.name) }"
                                                    class="file-icon text-white w-10 h-10 flex items-center justify-center rounded flex-shrink-0"
                                                    :class="icon.is_image ? 'bg-white p-0.5 border border-gray-200 dark:border-gray-700' : 'bg-gray-400 text-white'">
                                                    <template x-if="icon.is_image">
                                                        <img :src="icon.src" alt="file icon"
                                                            class="w-full h-full object-contain rounded-sm">
                                                    </template>
                                                    <template x-if="!icon.is_image">
                                                        <i class="fa-solid fa-file"></i>
                                                    </template>
                                                </div>
                                                <div class="file-details flex-1 min-w-0">
                                                    <p class="file-name text-sm text-gray-800 dark:text-gray-200 truncate"
                                                        :title="fileWrapper.name" x-text="fileWrapper.name"></p>
                                                    <p class="file-size text-xs text-gray-500 dark:text-gray-400"
                                                        x-text="formatBytes(fileWrapper.size)"></p>
                                                    <div x-show="fileWrapper.status === 'uploading' || fileWrapper.status === 'retrying'"
                                                        class="progress-bar-container mt-1">
                                                        <div class="progress-bar"
                                                            :style="`width: ${fileWrapper.progress || 0}%`"></div>
                                                    </div>
                                                    <div class="status-container mt-1 h-5 flex items-center">
                                                        <template x-if="fileWrapper.status === 'failed'"><i
                                                                class="fa-solid fa-circle-exclamation text-red-500"></i></template>
                                                        <template
                                                            x-if="fileWrapper.status === 'uploading' || fileWrapper.status === 'retrying'"><i
                                                                class="fa-solid fa-spinner fa-spin text-blue-500"></i></template>
                                                        <p class="status-text text-xs ml-1.5" :class="{
                                                                                                                    'text-red-600 dark:text-red-400': fileWrapper.status === 'failed',
                                                                                                                    'text-blue-600 dark:text-blue-400': fileWrapper.status === 'uploading' || fileWrapper.status === 'retrying',
                                                                                                                    'text-gray-500 dark:text-gray-400': fileWrapper.status === 'added' || !fileWrapper.status
                                                                                                                }"
                                                            x-text="fileWrapper.statusText || 'Added'">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 ml-2">
                                                    <button type="button"
                                                        x-show="!isReadOnly && fileWrapper.status === 'failed'"
                                                        class="action-btn" @click.prevent="uploadFile(fileWrapper, cat.id)"
                                                        title="Retry Upload">
                                                        <i
                                                            class="fa-solid fa-rotate-right text-blue-500 hover:text-blue-700"></i>
                                                    </button>
                                                    <button type="button"
                                                        x-show="!isReadOnly && fileWrapper.status !== 'uploading' && fileWrapper.status !== 'retrying'"
                                                        class="action-btn" @click.prevent="removeFile(cat.id, fileWrapper)"
                                                        title="Remove File">
                                                        <i
                                                            class="fa-solid fa-trash-can text-red-500 hover:text-red-700"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <h5 x-show="fileStores[cat.id].some(f => f.uploaded)"
                                            class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-4 mb-1 px-2"
                                            style="border-bottom: 1px dashed #d1d5db; padding-bottom: 4px; margin-bottom: 8px;">
                                            On Server
                                        </h5>
                                        <template x-for="fileWrapper in fileStores[cat.id].filter(f => f.uploaded)"
                                            :key="fileWrapper._uid">
                                            <div class="file-preview-item flex items-center space-x-3 p-2 rounded-md">
                                                <div class="file-icon text-white w-10 h-10 flex items-center justify-center rounded flex-shrink-0"
                                                    :class="iconMap[fileWrapper.name.split('.').pop().toLowerCase()]
                                                                                                                        ? 'bg-white p-0.5 border border-gray-200 dark:border-gray-700'
                                                                                                                        : 'bg-gray-400 text-white'">
                                                    <template
                                                        x-if="iconMap[fileWrapper.name.split('.').pop().toLowerCase()]">
                                                        <img :src="iconMap[fileWrapper.name.split('.').pop().toLowerCase()]"
                                                            alt="file icon" class="w-full h-full object-contain rounded-sm">
                                                    </template>

                                                    <template
                                                        x-if="!iconMap[fileWrapper.name.split('.').pop().toLowerCase()]">
                                                        <i class="fa-solid fa-file"></i> </template>

                                                </div>
                                                <div class="file-details flex-1 min-w-0">
                                                    <p class="file-name text-sm text-gray-800 dark:text-gray-200 truncate"
                                                        :title="fileWrapper.name" x-text="fileWrapper.name"></p>
                                                    <p class="file-size text-xs text-gray-500 dark:text-gray-400"
                                                        x-text="formatBytes(fileWrapper.size)"></p>
                                                    <div class="status-container mt-1 h-5 flex items-center">
                                                        <i class="fa-solid fa-check-circle text-green-500"></i>
                                                        <p class="status-text text-xs ml-1.5 text-green-600 dark:text-green-400"
                                                            x-text="fileWrapper.statusText || 'On Server'"></p>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 ml-2">
                                                    <button type="button" x-show="!isReadOnly" class="action-btn"
                                                        @click.prevent="removeFile(cat.id, fileWrapper)"
                                                        title="Remove File">
                                                        <i
                                                            class="fa-solid fa-trash-can text-red-500 hover:text-red-700"></i>
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

            <div class="flex justify-end pt-4 gap-4" x-show="!isReadOnly" x-transition>
                <button type="submit" id="submit-button"
                    :disabled="!isMetadataFilled || isUploading || (draftSaved && !isDirty)"
                    class="inline-flex items-center gap-2 justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 enabled:hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-800">
                    <i class="fa-solid" :class="{ 'fa-upload': !isUploading, 'fa-spinner fa-spin': isUploading }"></i>
                    <span x-text="isUploading ? 'Uploading...' : (draftSaved ? 'Update Draft' : 'Save to Draft')"></span>
                </button>

                <button type="button" @click="requestApproval" :disabled="!draftSaved || approvalRequested || isDirty"
                    class="px-4 py-2 bg-yellow-500 enabled:hover:bg-yellow-600 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!approvalRequested"><i class="fa-solid fa-paper-plane mr-1"></i> Request Approval</span>
                    <span x-show="approvalRequested"><i class="fa-solid fa-check mr-1"></i> Requested</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function reviseConfirm() {
            const t = detectTheme();
            const urlParams = new URLSearchParams(window.location.search);
            const revisionId = urlParams.get('revision_id');

            if (!revisionId) {
                Swal.fire({
                    title: 'Error',
                    text: 'No revision ID found in the URL.',
                    icon: 'error',
                    background: t.bg,
                    color: t.fg,
                    customClass: {
                        popup: 'border',
                    },
                    didOpen: (popup) => {
                        popup.style.borderColor = t.border;
                    }
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to revert this approved revision to a draft. This action will be logged.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, revise it!',
                background: t.bg,
                color: t.fg,
                customClass: {
                    popup: 'border',
                },
                didOpen: (popup) => {
                    popup.style.borderColor = t.border;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reviseUrl = "{{ route('upload.drawing.revise-confirm') }}";
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while the revision is being updated.',
                        allowOutsideClick: false,
                        background: t.bg,
                        color: t.fg,
                        customClass: {
                            popup: 'border',
                        },
                        didOpen: () => {
                            Swal.showLoading();
                            const popup = Swal.getPopup();
                            if (popup) {
                                popup.style.borderColor = t.border;
                            }
                        }
                    });

                    fetch(reviseUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            revision_id: revisionId
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errorData => {
                                    throw new Error(errorData.message || `Server Error: ${response.status}`);
                                }).catch(() => {
                                    throw new Error(`Server Error: ${response.status}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                background: t.bg,
                                color: t.fg,
                                customClass: {
                                    popup: 'border',
                                },
                                didOpen: (popup) => {
                                    popup.style.borderColor = t.border;
                                }
                            }).then(() => {
                                const baseUrl = "{{ url('/drawing-upload') }}";
                                window.location.href = `${baseUrl}?revision_id=${revisionId}&read_only=false`;
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Request Failed!',
                                text: error.message,
                                icon: 'error',
                                background: t.bg,
                                color: t.fg,
                                customClass: {
                                    popup: 'border',
                                },
                                didOpen: (popup) => {
                                    popup.style.borderColor = t.border;
                                }
                            });
                        });
                }
            });
        }
        // Toast functions
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
                ecn_no: '',
                receipt_date: '',
                revision_label_id: null,
                note: '',
                revisionStatus: null,
                is_finish: false,

                originalDraftData: {
                    ecn_no: '',
                    receipt_date: '',
                    note: '',
                    revision_label_id: null,
                    is_finish: false
                },

                customerHasLabels: false,
                revisionCheck: {
                    status: null, // null, 'checking', 'create_new', 'edit_draft', 'locked'
                    message: '',
                    next_rev: 0,
                    revision: null,
                    files: {},
                },

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
                filesToDelete: [],

                allowedExtensions: {},
                iconMap: {},
                conflictFiles: [],

                isUploading: false,
                draftSaved: false,
                approvalRequested: false,
                savedPackageId: null,
                savedRevisionId: null,
                hasNewFiles: false,
                // isDirty: false,
                isLoadingDraft: false,
                // originalEcnNo: '',
                isReadOnly: false,
                isCreatingNewRevision: false,
                originalRevisionStatus: null,

                // --- COMPUTED ---
                get isDirty() {
                    if (this.isLoadingDraft) return false;
                    if (this.hasNewFiles) return true;
                    if (this.filesToDelete.length > 0) return true;
                    if (this.ecn_no !== this.originalDraftData.ecn_no) return true;
                    if (this.receipt_date !== this.originalDraftData.receipt_date) return true;
                    if ((this.note || '') !== this.originalDraftData.note) return true;
                    if (this.revision_label_id != this.originalDraftData.revision_label_id) return true;
                    if (this.is_finish !== this.originalDraftData.is_finish) return true;
                    return false;
                },

                get isMetadataFilled() {
                    return this.customer && this.model && this.partNo && this.docType && this.partGroup;
                },

                get isFormReady() {
                    return this.isMetadataFilled && this.ecn_no.trim() !== '';
                },

                get isMetadataLocked() {
                    if (this.isLoadingDraft) return true;
                    if (this.draftSaved) return true;
                    if (this.isCreatingNewRevision) return true;
                    if (this.isReadOnly) return true;
                    return false;
                },

                // --- METHODS ---
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const revisionId = urlParams.get('revision_id');
                    const readOnlyParam = urlParams.get('read_only');

                    if (revisionId) {
                        this.isLoadingDraft = true;
                        this.loadDraftData(revisionId, readOnlyParam === 'true');
                    } else {
                        this.initSelect2('customer', 'Select Customer',
                            "{{ route('upload.getCustomerData') }}");
                        this.initDisabledSelect2('model', 'Select Customer First');
                        this.initDisabledSelect2('partNo', 'Select Model First');
                        this.initDisabledSelect2('docType', 'Select Part No First');
                        this.initDisabledSelect2('category', 'Select Document Group First');
                        this.initDisabledSelect2('partGroup', 'Select Sub Category First');
                        this.initRevisionLabelSelect();
                        this.fetchAllowedExtensions();
                    }

                    this.debouncedCheckConflicts = this.debounce(this.checkFileConflicts, 500);

                    this.$watch('customer', (val) => {
                        if (this.isLoadingDraft) return;
                        this.resetAndDisable(['model', 'partNo', 'docType', 'category',
                            'partGroup'
                        ]);
                        this.customerHasLabels = false;
                        this.revision_label_id = null;
                        if (val) {
                            this.initSelect2('model', 'Select Model',
                                "{{ route('upload.getModelData') }}", {
                                customer_id: val
                            });
                            this.fetchRevisionLabels(val);
                        }
                    });

                    this.$watch('model', (val) => {
                        if (this.isLoadingDraft) return;
                        this.resetAndDisable(['partNo', 'docType', 'category', 'partGroup']);
                        if (val) this.initSelect2('partNo', 'Select Part No',
                            "{{ route('upload.getProductData') }}", {
                            model_id: val
                        });
                    });

                    this.$watch('partNo', (val) => {
                        if (this.isLoadingDraft) return;
                        this.resetAndDisable(['docType', 'category', 'partGroup']);
                        if (val) this.initSelect2('docType', 'Select Document Type',
                            "{{ route('upload.getDocumentGroupData') }}");
                    });

                    this.$watch('docType', (val) => {
                        if (this.isLoadingDraft) return;
                        this.resetAndDisable(['category', 'partGroup']);
                        if (val) this.initSelect2('category', 'Select Category',
                            "{{ route('upload.getSubCategoryData') }}", {
                            document_group_id: val
                        });
                    });

                    this.$watch('category', (val) => {
                        if (this.isLoadingDraft) return;
                        this.resetAndDisable(['partGroup']);
                        if (val) this.initSelect2('partGroup', 'Select Part Group',
                            "{{ route('upload.getPartGroupData') }}", {
                            customer_id: this.customer,
                            model_id: this.model
                        });
                    });

                    // this.$watch('ecn_no', (newValue, oldValue) => {
                    //     if (this.isLoadingDraft) return;
                    //     if (newValue !== this.originalEcnNo) {
                    //         this.isDirty = true;
                    //     } else {
                    //         this.isDirty = false;
                    //     }
                    // });

                    // this.$watch('receipt_date', () => {
                    //     if (!this.isLoadingDraft) this.isDirty = true;
                    // });

                    // this.$watch('note', () => {
                    //     if (!this.isLoadingDraft) this.isDirty = true;
                    // });

                    this.$watch('isFormReady', (isReady) => {
                        if (isReady && !this.isLoadingDraft && !this.isReadOnly) {
                            this.checkRevisionStatus();
                        } else if (!isReady) {
                            this.revisionCheck.status = null;
                        }
                    });

                    this.$watch('revision_label_id', () => {
                        if (this.isFormReady && !this.isLoadingDraft && !this.isReadOnly)
                            this.checkRevisionStatus();
                    });

                    this.$watch('enabledCategories', (newValue, oldValue) => {
                        const removed = oldValue.filter(x => !newValue.includes(x));
                        removed.forEach(catId => {
                            this.fileStores[catId] = [];
                        });
                    });
                    this.fetchAllowedExtensions();
                },

                fetchAllowedExtensions() {
                    $.ajax({
                        url: "{{ route('upload.drawing.allowed-extensions') }}",
                        method: 'GET',
                        success: (res) => {
                            this.allowedExtensions = res.validation;
                            this.iconMap = res.icons;
                        },
                        error: (xhr) => {
                            console.error('Failed to fetch allowed extensions.');
                        }
                    });
                },

                initSelect2(propName, placeholder, url, additionalData = {}) {
                    const el = $(`#${propName}`);
                    if (el.hasClass("select2-hidden-accessible")) {
                        el.select2('destroy');
                    }

                    el.select2({
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

                initSelect2WithData(propName, placeholder, url, data) {
                    const el = $(`#${propName}`);
                    if (el.hasClass("select2-hidden-accessible")) {
                        el.select2('destroy');
                    }

                    var option = new Option(data.text, data.id, true, true);
                    el.append(option).trigger('change');

                    el.select2({
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
                                ...data.ajaxData
                            }),
                            processResults: data => ({
                                results: data.results
                            })
                        }
                    }).on('change', (e) => {
                        this[propName] = e.target.value;
                    });
                },

                loadDraftData(revisionId, isReadOnly = false) {
                    const url = `{{ url('/files') }}` + '/' + revisionId;

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: (json) => {
                            const pkg = json.package;
                            if (!pkg) {
                                toastError('Error', 'Revision not found.');
                                this.isLoadingDraft = false;
                                setTimeout(() => {
                                    window.location.href = '{{ route("file-manager.upload") }}';
                                }, 1000);
                                return;
                            }

                            this.revisionStatus = pkg.revision_status;
                            this.originalRevisionStatus = pkg.revision_status;

                            // this.originalEcnNo = pkg.ecn_no;

                            if (isReadOnly) {
                                this.isReadOnly = true;
                            } else if (pkg.revision_status !== 'draft') {
                                toastWarning('Info', 'This file cannot be edited. Open in Read-Only mode.');
                                this.isReadOnly = true;
                            }

                            this.ecn_no = pkg.ecn_no;
                            this.receipt_date = pkg.receipt_date ? pkg.receipt_date.split(' ')[0] : '';
                            this.note = pkg.revision_note;

                            this.is_finish = pkg.is_finish == 1;
                            this.revision_label_id = pkg.revision_label_id;

                            this.originalDraftData = {
                                ecn_no: this.ecn_no,
                                receipt_date: this.receipt_date,
                                note: this.note || '',
                                revision_label_id: this.revision_label_id || null,
                                is_finish: this.is_finish
                            };

                            this.customer = pkg.customer_id;
                            this.model = pkg.model_id;
                            this.partNo = pkg.product_id;
                            this.docType = pkg.docgroup_id;
                            this.category = pkg.subcategory_id;
                            this.partGroup = pkg.part_group_id;

                            this.savedRevisionId = pkg.id;
                            this.savedPackageId = pkg.package_id;

                            if (!this.isReadOnly) {
                                this.draftSaved = true;
                                this.approvalRequested = (pkg.revision_status === 'pending');
                            }

                            this.initSelect2WithData('customer', 'Select Customer', "{{ route('upload.getCustomerData') }}", {
                                id: pkg.customer_id,
                                text: pkg.customer_code,
                                ajaxData: {}
                            });
                            this.initSelect2WithData('model', 'Select Model', "{{ route('upload.getModelData') }}", {
                                id: pkg.model_id,
                                text: pkg.model_name,
                                ajaxData: {
                                    customer_id: pkg.customer_id
                                }
                            });
                            this.initSelect2WithData('partNo', 'Select Part No', "{{ route('upload.getProductData') }}", {
                                id: pkg.product_id,
                                text: pkg.part_no,
                                ajaxData: {
                                    model_id: pkg.model_id
                                }
                            });
                            this.initSelect2WithData('docType', 'Select Document Type', "{{ route('upload.getDocumentGroupData') }}", {
                                id: pkg.docgroup_id,
                                text: pkg.docgroup_name,
                                ajaxData: {}
                            });
                            this.initSelect2WithData('category', 'Select Category', "{{ route('upload.getSubCategoryData') }}", {
                                id: pkg.subcategory_id,
                                text: pkg.subcategory_name,
                                ajaxData: {
                                    document_group_id: pkg.docgroup_id
                                }
                            });
                            this.initSelect2WithData('partGroup', 'Select Part Group', "{{ route('upload.getPartGroupData') }}", {
                                id: pkg.part_group_id,
                                text: pkg.code_part_group,
                                ajaxData: {
                                    customer_id: pkg.customer_id,
                                    model_id: pkg.model_id
                                }
                            });

                            this.initRevisionLabelSelect();
                            this.fetchRevisionLabels(pkg.customer_id, () => {
                                $('#revision_label_id').val(pkg.revision_label_id).trigger('change');
                            });

                            if (json.file_list) {
                                this.populateFileStores(json.file_list);
                            }

                            this.fetchAllowedExtensions();

                            this.$nextTick(() => {
                                this.isLoadingDraft = false;
                            });
                        },
                        error: (xhr) => {
                            toastError('Failed to Load', xhr.responseJSON?.message || 'Failed to load draft details.');
                            this.isLoadingDraft = false;
                            setTimeout(() => {
                                window.location.href = '{{ route("file-manager.upload") }}';
                            }, 2000);
                        }
                    });
                },

                startNewRevision() {
                    this.isCreatingNewRevision = true;
                    this.isReadOnly = false;
                    this.revisionStatus = 'draft';
                    this.draftSaved = false;
                    this.approvalRequested = false;
                    this.savedRevisionId = null;

                    this.ecn_no = '';
                    this.receipt_date = '';
                    this.note = '';
                    this.revision_label_id = null;
                    this.is_finish = false;
                    $('#revision_label_id').val(null).trigger('change');

                    this.originalDraftData = { ecn_no: '', receipt_date: '', note: '', revision_label_id: null, is_finish: false };

                    this.clearFileStores();
                    this.filesToDelete = [];
                    this.hasNewFiles = false;

                    this.$nextTick(() => {
                        document.getElementById('ecn_no').focus();
                        this.checkRevisionStatus();
                    });

                    toastInfo('New Revision Mode', 'Metadata dikunci. Masukkan ECN baru dan upload file.');
                },

                resetAndDisable(propNames) {
                    propNames.forEach(prop => {
                        this[prop] = null;
                        const el = $(`#${prop}`);
                        if (el.hasClass("select2-hidden-accessible")) el.select2('destroy');
                        el.val(null).trigger('change');
                    });

                    if (propNames.includes('model')) this.initDisabledSelect2('model',
                        'Select Customer First');
                    if (propNames.includes('partNo')) this.initDisabledSelect2('partNo',
                        'Select Model First');
                    if (propNames.includes('docType')) this.initDisabledSelect2('docType',
                        'Select Part No First');
                    if (propNames.includes('category')) this.initDisabledSelect2('category',
                        'Select Document Group First');
                    if (propNames.includes('partGroup')) this.initDisabledSelect2('partGroup',
                        'Select Sub Category First');
                },

                initDisabledSelect2(propName, placeholder) {
                    const el = $(`#${propName}`);
                    if (el.hasClass("select2-hidden-accessible")) {
                        el.select2('destroy');
                    }
                    el.select2({
                        width: '100%',
                        placeholder: placeholder
                    });
                },

                fetchRevisionLabels(customerId, callback = null) {
                    const url = "{{ route('upload.drawing.get-labels', ':id') }}".replace(':id', customerId);
                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: (res) => {
                            this.customerHasLabels = res.has_labels;
                            if (res.has_labels) {
                                const $select = $('#revision_label_id');
                                $select.empty().append(new Option('-- No Label --', '', true, true));
                                res.labels.forEach(label => {
                                    $select.append(new Option(label.text, label.id, false, false));
                                });
                                $select.trigger('change');
                            }
                            if (callback) callback();
                        },
                        error: () => {
                            this.customerHasLabels = false;
                            if (callback) callback();
                        }
                    });
                },

                initRevisionLabelSelect() {
                    const $select = $('#revision_label_id');
                    $select.select2({
                        width: '100%',
                        placeholder: 'Select Revision Label'
                    }).on('change', (e) => {
                        this.revision_label_id = e.target.value;
                    });
                },

                checkRevisionStatus() {
                    if (!this.isFormReady) return;

                    this.revisionCheck.status = 'checking';
                    const currentRevId = this.savedRevisionId || (this.revisionCheck.revision ? this.revisionCheck.revision.id : null);

                    $.ajax({
                        url: "{{ route('upload.drawing.check-status') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            customer_id: this.customer,
                            model_id: this.model,
                            partNo: this.partNo,
                            docType: this.docType,
                            category: this.category || null,
                            partGroup: this.partGroup,
                            ecn_no: this.ecn_no,
                            revision_label_id: this.revision_label_id || null,
                            existing_revision_id: currentRevId
                        },
                        success: (res) => {
                            this.revisionCheck.status = res.mode;
                            if (res.mode === 'create_new') {
                                this.revisionCheck.next_rev = res.next_rev;
                                this.revisionCheck.revision = null;
                                this.revisionCheck.files = {};
                                this.filesToDelete = [];
                            } else if (res.mode === 'edit_draft') {
                                this.revisionCheck.revision = res.revision;
                                this.revisionCheck.files = res.files;
                                if (!this.draftSaved) {
                                    this.note = res.revision.note;
                                    this.receipt_date = res.revision.receipt_date ? res.revision.receipt_date.split(' ')[0] : '';
                                    this.is_finish = res.revision.is_finish == 1;
                                }
                                this.filesToDelete = [];
                                this.populateFileStores(res.files);
                            } else if (res.mode === 'locked') {
                                this.revisionCheck.message = res.message;
                            }
                        },
                        error: (xhr) => {
                            this.revisionCheck.status = 'locked';
                            const res = xhr.responseJSON;
                            if (res) {
                                this.revisionCheck.message = res.message || 'Failed to check revision status.';
                                if (res.revision) {
                                    this.revisionCheck.revision = res.revision;
                                    if (!this.draftSaved) {
                                        this.note = res.revision.note;
                                        this.receipt_date = res.revision.receipt_date ? res.revision.receipt_date.split(' ')[0] : '';
                                        this.is_finish = res.revision.is_finish == 1;
                                    }
                                    if (res.files) {
                                        this.populateFileStores(res.files);
                                    }
                                }
                            } else {
                                this.revisionCheck.message = 'Failed to check revision status.';
                            }
                            toastError('Error', this.revisionCheck.message);
                        }
                    });
                },

                debounce(func, timeout = 300) {
                    let timer;
                    return (...args) => {
                        clearTimeout(timer);
                        timer = setTimeout(() => {
                            func.apply(this, args);
                        }, timeout);
                    };
                },

                populateFileStores(filesByCategory) {
                    this.clearFileStores();
                    for (const category in filesByCategory) {
                        if (this.fileStores.hasOwnProperty(category)) {
                            this.fileStores[category] = filesByCategory[category].map(file => ({
                                _uid: file.id,
                                id: file.id,
                                name: file.name,
                                size: file.size,
                                uploaded: true,
                                status: 'uploaded',
                                statusText: 'On Server',
                                progress: 100
                            }));
                        }
                    }
                },

                clearFileStores() {
                    for (const category in this.fileStores) {
                        this.fileStores[category] = [];
                    }
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
                    if (this.isReadOnly) return;
                    let addedCount = 0;
                    let rejectedCount = 0;
                    let rejectedMessages = [];

                    const allowed = this.allowedExtensions[category] || [];

                    Array.from(files).forEach(file => {
                        const extension = file.name.split('.').pop().toLowerCase();
                        if (allowed.length > 0 && !allowed.includes(extension)) {
                            rejectedCount++;
                            rejectedMessages.push(`'${file.name}' (type '${extension}')`);
                            return;
                        }

                        if (!this.fileStores[category].some(f => f.name === file.name && f.size === file.size)) {
                            this.fileStores[category].push({
                                _uid: Date.now() + Math.random(),
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

                    if (rejectedCount > 0) {
                        const plural = rejectedCount > 1;
                        toastError(`File Type${plural ? 's' : ''} Not Allowed`,
                            `Rejected ${rejectedMessages.join(', ')}.`);
                    }

                    if (addedCount > 0) {
                        toastInfo('Files Added',
                            `Added ${addedCount} file${addedCount > 1 ? 's' : ''} to ${category.toUpperCase()} category.`
                        );
                        if (this.draftSaved) {
                            this.hasNewFiles = true;
                            this.approvalRequested = false;
                            // this.isDirty = true;
                        }
                    }

                    const hasNewFiles = Object.values(this.fileStores).some(categoryFiles =>
                        categoryFiles.some(file => !file.uploaded)
                    );
                    this.hasNewFiles = hasNewFiles;

                    this.checkFileConflicts();
                },
                removeFile(category, fileWrapper) {
                    if (fileWrapper.uploaded) {
                        this.filesToDelete.push(fileWrapper.id);
                    }

                    const index = this.fileStores[category].indexOf(fileWrapper);
                    if (index > -1) {
                        this.fileStores[category].splice(index, 1);
                    }

                    // this.isDirty = true;

                    if (this.draftSaved) {
                        const hasNewFiles = Object.values(this.fileStores).some(categoryFiles =>
                            categoryFiles.some(file => !file.uploaded)
                        );
                        this.hasNewFiles = hasNewFiles;
                    }
                },
                getIcon(fileName) {
                    if (!fileName) {
                        return { is_image: false, src: null };
                    }

                    const ext = fileName.split('.').pop().toLowerCase();
                    const iconSrc = this.iconMap[ext];

                    if (iconSrc) {
                        return { is_image: true, src: iconSrc };
                    }
                    return { is_image: false, src: null };
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
                    if (!this.isDirty) {
                        toastInfo('No Changes', 'There are no changes to save.');
                        return;
                    }

                    const totalFiles = this.enabledCategories.reduce((acc, cat) => acc + this.fileStores[cat].filter(f => !f.uploaded).length, 0);
                    if (!this.draftSaved && totalFiles === 0) {
                        toastWarning('New Draft Requires Files', 'Please select at least one file to upload for a new draft.');
                        return;
                    }

                    this.isUploading = true;
                    this.uploadBatch();
                },
                uploadBatch() {
                    const formData = new FormData();
                    const filesToUpload = [];

                    formData.append('customer', this.customer);
                    formData.append('model', this.model);
                    formData.append('partNo', this.partNo);
                    formData.append('docType', this.docType);
                    formData.append('category', this.category || '');
                    formData.append('partGroup', this.partGroup);
                    formData.append('ecn_no', this.ecn_no);
                    formData.append('receipt_date', this.receipt_date);
                    formData.append('revision_label_id', this.revision_label_id || '');
                    formData.append('note', this.note || '');
                    formData.append('is_finish', this.is_finish ? 1 : 0);

                    let revNo;
                    if (this.revisionCheck.status === 'edit_draft' && this.revisionCheck.revision) {
                        revNo = this.revisionCheck.revision.revision_no;
                    } else {
                        revNo = this.revisionCheck.next_rev;
                    }
                    formData.append('revision_no', revNo);

                    if (this.revisionCheck.status === 'edit_draft') {
                        formData.append('existing_revision_id', this.revisionCheck.revision.id);
                        if (this.filesToDelete.length > 0) {
                            this.filesToDelete.forEach(id => formData.append('files_to_delete[]', id));
                        }
                    }

                    this.enabledCategories.forEach(c => formData.append('enabled_categories[]', c));
                    formData.append('_token', "{{ csrf_token() }}");

                    this.enabledCategories.forEach(cat => {
                        this.fileStores[cat].forEach(fileWrapper => {
                            if (!fileWrapper.uploaded) {
                                formData.append(`files_${cat}[]`, fileWrapper.file, fileWrapper.name);
                                formData.append(`options[${cat}][${fileWrapper.name}]`, fileWrapper.action);
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

                            toastSuccess('Success', res.message || `Successfully saved draft.`);

                            this.draftSaved = true;
                            this.hasNewFiles = false;
                            this.approvalRequested = false;
                            this.filesToDelete = [];
                            // this.isDirty = false;
                            // this.originalEcnNo = this.ecn_no;

                            this.originalDraftData = {
                                ecn_no: this.ecn_no,
                                receipt_date: this.receipt_date,
                                note: this.note || '',
                                revision_label_id: this.revision_label_id || '',
                                is_finish: this.is_finish
                            };

                            // this.enableMetadataEditing(false);
                            this.checkRevisionStatus();
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
                            revision_id: this.savedRevisionId
                        },
                        success: (res) => {
                            toastSuccess('Requested', 'Draft set to pending for approval.');
                            if (res.revision_id) {
                                const baseUrl = "{{ url('/drawing-upload') }}";
                                window.location.href = `${baseUrl}?revision_id=${res.revision_id}&read_only=true`;
                            } else {
                                window.location.reload();
                            }
                        },
                        error: (xhr) => {
                            toastError('Error', xhr.responseJSON?.message ||
                                'Failed to request approval.');
                            this.approvalRequested = false;
                        }
                    });
                },

                deleteCurrentRevision() {
                    if (!this.savedRevisionId) {
                        toastError('Error', 'Revision ID not found.');
                        return;
                    }

                    const t = detectTheme();

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You are about to permanently delete this draft. This will also delete all related files from the server. This action cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        background: t.bg,
                        color: t.fg
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const deleteUrl = `{{ url('/upload/drawing/revision') }}/${this.savedRevisionId}`;
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while the draft is being removed.',
                                allowOutsideClick: false,
                                background: t.bg,
                                color: t.fg,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            fetch(deleteUrl, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        return response.json().then(errorData => {
                                            throw new Error(errorData.message || `Server Error: ${response.status}`);
                                        });
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message,
                                        icon: 'success',
                                        background: t.bg,
                                        color: t.fg
                                    }).then(() => {
                                        window.location.href = "{{ route('file-manager.upload') }}";
                                    });
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: 'Request Failed!',
                                        text: error.message,
                                        icon: 'error',
                                        background: t.bg,
                                        color: t.fg
                                    });
                                });
                        }
                    });
                },

                showConflictModal() {
                    const t = detectTheme();
                    const conflictHtml = this.conflictFiles.map((fileWrapper, index) => {
                        const iconInfo = this.getFileIcon(fileWrapper.name);
                        return `
                                                                                    <div class="conflict-item" style="display: flex; align-items: center; padding: 12px 8px; border-bottom: 1px solid ${t.border};">
                                                                                        <div style="flex-shrink: 0; margin-right: 12px;">
                                                                                            <span style="width: 40px; height: 40px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; border-radius: 0.375rem;" class="${iconInfo.color} text-white">
                                                                                                <i class="fa-solid ${iconInfo.icon}"></i>
                                                                                            </span>
                                                                                        </div>
                                                                                        <div style="flex-grow: 1; min-width: 0;">
                                                                                            <p style="font-weight: 500; color: ${t.fg}; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${fileWrapper.name}">${fileWrapper.name}</p>
                                                                                            <p style="font-size: 0.75rem; color: #6b7280;">${this.formatBytes(fileWrapper.size)}</p>
                                                                                        </div>
                                                                                        <div style="display: flex; gap: 16px; white-space: nowrap; margin-left: 16px;">
                                                                                            <label style="display: flex; align-items: center; cursor: pointer; color: ${t.fg}; font-size: 0.875rem;">
                                                                                                <input type="radio" name="conflict_action_${index}" value="replace" class="form-radio h-4 w-4 text-orange-600" style="margin-right: 6px;"> Replace
                                                                                            </label>
                                                                                            <label style="display: flex; align-items: center; cursor: pointer; color: ${t.fg}; font-size: 0.875rem;">
                                                                                                <input type="radio" name="conflict_action_${index}" value="suffix" checked class="form-radio h-4 w-4 text-blue-600" style="margin-right: 6px;"> Add New
                                                                                            </label>
                                                                                        </div>
                                                                                    </div>
                                                                                `;
                    }).join('');

                    Swal.fire({
                        title: '<i class="fa-solid fa-copy mr-2"></i> File Already Exists',
                        html: `
                                                                                    <p style="margin-bottom: 1.5rem; color: ${t.fg}; text-align: left;">Some of the files you uploaded already exist on the server. Select an action for each file below.</p>
                                                                                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid ${t.border}; border-radius: 8px; background-color: ${t.mode === 'dark' ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.02)'};">
                                                                                        ${conflictHtml}
                                                                                    </div>
                                                                                `,
                        width: '48rem',
                        icon: 'warning',
                        showCloseButton: true,
                        allowOutsideClick: false,
                        backdrop: true,
                        timer: null,
                        confirmButtonText: '<i class="fa-solid fa-check-circle mr-2"></i> Terapkan Pilihan',
                        confirmButtonColor: '#3b82f6',
                        background: t.bg,
                        color: t.fg,
                        customClass: {
                            popup: 'border',
                            header: 'pb-0',
                            title: 'text-xl font-bold',
                            htmlContainer: 'mt-0'
                        },
                        preConfirm: () => {
                            this.conflictFiles.forEach((fileWrapper, index) => {
                                const action = document.querySelector(`input[name='conflict_action_${index}']:checked`).value;
                                fileWrapper.action = action;
                            });
                            return true;
                        },
                        didOpen: (popup) => {
                            popup.style.borderColor = t.border;
                            const items = popup.querySelectorAll('.conflict-item');
                            if (items.length > 0) {
                                items[items.length - 1].style.borderBottom = 'none';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.conflictFiles.forEach(fw => fw.conflict = false);
                            this.conflictFiles = [];
                            toastSuccess('Options saved', 'Actions for the same file have been set.');
                        } else if (result.isDismissed) {
                            const conflictFileWrappers = this.conflictFiles;
                            ['2d', '3d', 'ecn'].forEach(cat => {
                                this.fileStores[cat] = this.fileStores[cat].filter(fw => !conflictFileWrappers.includes(fw));
                            });
                            this.conflictFiles = [];
                            toastWarning('Upload Cancelled', 'Conflicting files have been removed from the upload list.');
                        }
                    });
                },

                checkFileConflicts(addedCount = 0, category = '') {
                    if (!this.isFormReady) return;

                    const filesToCheck = {
                        '2d': this.fileStores['2d'].filter(f => !f.uploaded).map(f => f.name),
                        '3d': this.fileStores['3d'].filter(f => !f.uploaded).map(f => f.name),
                        'ecn': this.fileStores['ecn'].filter(f => !f.uploaded).map(f => f.name),
                    };

                    const totalFilesToCheck = filesToCheck['2d'].length + filesToCheck['3d'].length + filesToCheck['ecn'].length;
                    if (totalFilesToCheck === 0) {
                        if (addedCount > 0) {
                            toastInfo('Files Added', `Added ${addedCount} file${addedCount > 1 ? 's' : ''} to ${category.toUpperCase()} category.`);
                        }
                        return;
                    }

                    const data = {
                        _token: "{{ csrf_token() }}",
                        customer: this.customer,
                        model: this.model,
                        partNo: this.partNo,
                        docType: this.docType,
                        partGroup: this.partGroup,
                        ecn_no: this.ecn_no,
                        revision_label_id: this.revision_label_id || null,
                        revision_no: this.revisionCheck.revision ? this.revisionCheck.revision.revision_no : this.revisionCheck.next_rev,
                        files_2d: filesToCheck['2d'],
                        files_3d: filesToCheck['3d'],
                        files_ecn: filesToCheck['ecn'],
                    };

                    $.ajax({
                        url: "{{ route('upload.drawing.check-conflicts') }}",
                        method: 'POST',
                        data: data,
                        success: (res) => {
                            this.conflictFiles = [];
                            ['2d', '3d', 'ecn'].forEach(cat => {
                                this.fileStores[cat].forEach(fileWrapper => {
                                    if (fileWrapper.uploaded) return;

                                    if (res.conflicts[cat].includes(fileWrapper.name)) {
                                        fileWrapper.conflict = true;
                                        if (!fileWrapper.action || fileWrapper.action === 'add') {
                                            fileWrapper.action = 'suffix';
                                        }
                                        this.conflictFiles.push(fileWrapper);
                                    } else {
                                        fileWrapper.conflict = false;
                                        fileWrapper.action = 'add';
                                    }
                                });
                            });

                            if (this.conflictFiles.length > 0) {
                                this.showConflictModal();
                            } else if (addedCount > 0) {
                                toastInfo('Files Added', `Added ${addedCount} file${addedCount > 1 ? 's' : ''} to ${category.toUpperCase()} category.`);
                            }
                        },
                        error: (xhr) => {
                            console.warn('Failed to check file conflicts.');
                            toastError('Error', 'Failed to check file conflicts on the server.');
                        }
                    });
                },
            }));
        });
    </script>
@endsection

@push('style')
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
            max-height: 300px;
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
@endpush
