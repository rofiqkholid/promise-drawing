@extends('layouts.app')
@section('title', 'Approval Detail - PROMISE')
@section('header-title', 'Approval Detail')

@section('content')

<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="approvalDetail()" x-init="init()">
    <!-- Header and Metadata Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fa-solid fa-file-invoice mr-3 text-blue-600"></i> Approval Metadata
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Customer</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="metadata.customer"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Model</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="metadata.model"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Part No</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="metadata.part_no"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Revision</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="metadata.revision"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Status</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="metadata.status"></dd>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar: File Navigation -->
        <div class="lg:col-span-1 space-y-6">
            @php
                function renderFileGroup($title, $icon, $category) {
            @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <button @click="toggleSection('{{$category}}')" class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" :aria-expanded="openSections.includes('{{$category}}')">
                            <div class="flex items-center">
                                <i class="fa-solid {{$icon}} mr-3 text-gray-500 dark:text-gray-400"></i>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
                            </div>
                            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full" x-text="`${files['{{$category}}']?.length || 0} files`"></span>
                            <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform" :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
                        </button>
                        <div x-show="openSections.includes('{{$category}}')" x-collapse class="p-2 max-h-72 overflow-y-auto">
                            <template x-for="file in files['{{$category}}']" :key="file.name">
                                <div @click="selectFile(file)" :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}" class="flex items-center p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" role="button" tabindex="0" @keydown.enter="selectFile(file)">
                                    <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 transition-colors group-hover:text-blue-500"></i>
                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
                                </div>
                            </template>
                            <template x-if="files['{{$category}}'].length === 0">
                                <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No files available.</p>
                            </template>
                        </div>
                    </div>
            @php
                }
            @endphp

            {{ renderFileGroup('2D Drawings', 'fa-drafting-compass', '2d') }}
            {{ renderFileGroup('3D Models', 'fa-cubes', '3d') }}
            {{ renderFileGroup('ECN / Documents', 'fa-file-lines', 'ecn') }}
        </div>

        <!-- Main Panel: File Preview -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- No File Selected -->
                <div x-show="!selectedFile" class="flex flex-col items-center justify-center h-96 p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
                    <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500"></i>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
                </div>

                <!-- File Preview -->
                <div x-show="selectedFile" x-transition.opacity class="p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedFile?.name"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Last updated: {{ now()->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center">
                        <template x-if="isImage(selectedFile?.name)">
                            <img :src="selectedFile?.url" alt="File Preview" class="max-w-full max-h-64 object-contain" loading="lazy">
                        </template>
                        <template x-if="!isImage(selectedFile?.name)">
                            <div class="text-center">
                                <i class="fa-solid fa-file text-6xl text-gray-400 dark:text-gray-500"></i>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Preview Unavailable</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">This file type is not supported for preview.</p>
                            </div>
                        </template>
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button @click="rejectFile(selectedFile)" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                            <i class="fa-solid fa-circle-xmark mr-2"></i> Reject
                        </button>
                        <button @click="approveFile(selectedFile)" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200">
                            <i class="fa-solid fa-circle-check mr-2"></i> Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-collapse] { @apply overflow-hidden transition-all duration-300 ease-in-out; }
    .file-group-card { @apply bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden; }
    .file-item { @apply flex items-center p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200; }
    .preview-area { @apply bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center; }
    .action-btn { @apply inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200; }
</style>

@endsection

@push('scripts')
<script>
    function approvalDetail() {
        return {
            approvalId: {{ $approvalId }},
            metadata: {},
            files: { '2d': [], '3d': [], 'ecn': [] },
            selectedFile: null,
            openSections: [],

            init() {
                this.loadData();
            },

            loadData() {
                this.metadata = {
                    customer: 'MMKI',
                    model: '4L45W',
                    part_no: '5251D644',
                    revision: 'Rev1',
                    status: 'Waiting'
                };
                this.files = {
                    '2d': [
                        { name: '5251D644_drawing_assy.pdf', url: 'https://via.placeholder.com/800x600.png?text=Assy+PDF' },
                        { name: '5251D644_component_01.dwg', url: '#' },
                        { name: '5251D644_component_02.dwg', url: '#' },
                    ],
                    '3d': [
                        { name: '5251D644_assy.step', url: '#' },
                        { name: '5251D644_solid.x_t', url: '#' },
                    ],
                    'ecn': [
                        { name: 'ECN-2025-001.pdf', url: 'https://via.placeholder.com/800x600.png?text=ECN+PDF' },
                        { name: 'material_spec.xlsx', url: '#' },
                        { name: 'inspection_report.pdf', url: 'https://via.placeholder.com/800x600.png?text=Report+PDF' },
                    ]
                };
            },

            toggleSection(category) {
                const index = this.openSections.indexOf(category);
                if (index > -1) this.openSections.splice(index, 1);
                else this.openSections.push(category);
            },

            selectFile(file) {
                this.selectedFile = { ...file };
            },

            approveFile(file) {
                alert(`File "${file.name}" has been approved successfully.`);
                this.selectedFile = null;
            },

            rejectFile(file) {
                alert(`File "${file.name}" has been rejected successfully.`);
                this.selectedFile = null;
            },

            isImage(fileName) {
                return fileName && ['png', 'jpg', 'jpeg', 'gif', 'pdf'].includes(fileName.split('.').pop().toLowerCase());
            }
        }
    }
</script>
@endpush
