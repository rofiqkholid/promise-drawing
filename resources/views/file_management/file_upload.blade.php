@extends('layouts.app')
@section('title', 'File Manager - PROMISE')
@section('header-title', 'File Manager/Upload')

@section('content')

<div x-data="{
    isModalOpen: false,
    files: [],
    customer: '',
    model: '',
    partNo: '',
    docType: '',
    category: '',
    revision: ''
}" class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 font-sans">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Upload File</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload and manage your files in the Data Center.</p>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-blue-500 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/50 rounded-full">
                <i class="fa-solid fa-box-archive fa-lg"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Upload</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">512</p>
            </div>
        </div>
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-teal-500 dark:text-teal-400 bg-teal-100 dark:bg-teal-900/50 rounded-full">
                <i class="fa-solid fa-ruler-combined fa-lg"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dwg Study</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">300</p>
            </div>
        </div>
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-purple-500 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/50 rounded-full">
                <i class="fa-solid fa-industry fa-lg"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Go Mfg</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">198</p>
            </div>
        </div>
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-sky-500 dark:text-sky-400 bg-sky-100 dark:bg-sky-900/50 rounded-full">
                <i class="fa-solid fa-layer-group fa-lg"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Others</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">14</p>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-2">

            <button id="openUploadModalBtn" @click="isModalOpen = true; customer = ''; model = ''; partNo = ''; docType = ''; category = ''; revision = ''; files = []"
                class="w-full sm:w-auto inline-flex items-center gap-2 justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                <i class="fa-solid fa-upload"></i>
                Upload File
            </button>
            <div class="relative w-full sm:w-64">
                <input type="text" placeholder="Search..."
                    class="w-full pl-9 pr-2 py-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 dark:placeholder-gray-400 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">File Name</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Part No</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr class="bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700/50">
                        <td class="py-4 px-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">autocad_rev1.dwg</td>
                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">MMKI</td>
                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">5251D644</td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">Rejected</span>
                        </td>
                        <td class="py-4 px-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-4">
                                <button class="text-sky-600 hover:text-sky-900 dark:text-sky-400 dark:hover:text-sky-300" title="Download"><i class="fa-solid fa-download fa-lg"></i></button>
                                <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit"><i class="fa-solid fa-pen-to-square fa-lg"></i></button>
                                <button class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete"><i class="fa-solid fa-trash-can fa-lg"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Section --}}
    <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-60 dark:bg-opacity-80 flex items-center justify-center z-50 p-4" @click.self="isModalOpen = false" style="display: none;">

        <div id="uploadModal" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-xl p-6 sm:p-8 w-full max-w-5xl" @click.away="isModalOpen = false">
            <div class="flex items-center gap-3 mb-6">
                <i class="fa-solid fa-file-arrow-up text-xl text-blue-500"></i>
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">Upload New File</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                {{-- Kolom Drag & Drop --}}
                <div class="md:col-span-2 flex flex-col items-center">
                    <div x-ref="dnd" class="w-full h-56 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex flex-col justify-center items-center text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-center"
                        @dragover.prevent="$refs.dnd.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/50')"
                        @dragleave.prevent="$refs.dnd.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/50')"
                        @drop.prevent="let droppedFiles = $event.dataTransfer.files; if(droppedFiles.length > 0) { files = [droppedFiles[0]] }; $refs.dnd.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/50')">
                        <i class="fa-solid fa-cloud-arrow-up text-5xl text-gray-400 mb-4"></i>
                        <p class="text-lg font-semibold">Drag & Drop a File</p>
                        <p class="text-sm text-gray-400 mt-1">or click "Choose File" below</p>
                    </div>
                    <p class="my-4 text-gray-500 dark:text-gray-400">Or</p>
                    <input type="file" id="file-upload" class="hidden" @change="files = Array.from($event.target.files)" accept=".dwg,.jpg,.jpeg,.png,.pdf,.xls,.xlsx">
                    <label for="file-upload" class="cursor-pointer inline-flex items-center gap-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-2 px-6 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                        <i class="fa-solid fa-folder-open"></i> Choose File
                    </label>
                    <div class="w-full mt-4 space-y-2 max-h-24 overflow-y-auto">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-2 rounded-lg">
                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate" x-text="file.name"></span>
                                <button @click="files.splice(index, 1)" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-500">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="md:col-span-3 bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg border dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label for="customer" class="block text-sm text-gray-700 dark:text-gray-300">Customer</label>
                            <div class="relative mt-1">
                                <select id="customer" name="customer" class="appearance-none block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md shadow-sm py-2 sm:text-sm">
                                    <option value="">Select Customer</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="model" class="block text-sm text-gray-700 dark:text-gray-300">Model</label>
                            <div class="relative mt-1">
                                <select id="model" name="model" class="appearance-none block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md shadow-sm py-2 sm:text-sm">
                                    <option value="">Select Model</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="partNo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Part No</label>
                            <div class="relative mt-1">
                                <select id="partNo" x-model="partNo" class="appearance-none block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md shadow-sm py-2 sm:text-sm">
                                    <option value="">Select Part No</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700 dark:text-gray-400">
                                    <i class="fa-solid fa-chevron-down fa-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="docType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document Type</label>
                            <div class="relative mt-1">
                                <select id="docType" x-model="docType" class="appearance-none block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md shadow-sm py-2 sm:text-sm">
                                    <option value="">Select Document Type</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700 dark:text-gray-400">
                                    <i class="fa-solid fa-chevron-down fa-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <div class="relative mt-1">
                                <select id="category" x-model="category" class="appearance-none block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md shadow-sm py-2 sm:text-sm">
                                    <option value="">Select Category</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700 dark:text-gray-400">
                                    <i class="fa-solid fa-chevron-down fa-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="revision" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision</label>
                            <div class="relative mt-1">
                                <select id="revision" x-model="revision" class="appearance-none block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md shadow-sm py-2 sm:text-sm">
                                    <option value="">Select Revision</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700 dark:text-gray-400">
                                    <i class="fa-solid fa-chevron-down fa-xs"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8 space-x-4">
                <button @click="isModalOpen = false; files = []" class="w-full sm:w-auto inline-flex items-center gap-2 justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button class="w-full sm:w-auto inline-flex items-center gap-2 justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                    <i class="fa-solid fa-upload"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openModalButton = document.getElementById('openUploadModalBtn');
        const modal = document.getElementById('uploadModal');

        openModalButton.addEventListener('click', function() {
            setTimeout(function() {
                if ($('#customer').hasClass('select2-hidden-accessible')) {
                    $('#customer').select2('destroy');
                }
                $('#customer').select2({
                    dropdownParent: $(modal), 
                    width: '100%',
                    placeholder: 'Select Customer',
                    ajax: {
                        url: "{{ route('upload.getDataCustomer') }}",
                        method: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results,
                                pagination: {
                                    more: (params.page * 10) < data.total_count
                                }
                            };
                        },
                        cache: true
                    }
                });

                if ($('#model').hasClass('select2-hidden-accessible')) {
                    $('#model').select2('destroy');
                }
                $('#model').select2({
                    dropdownParent: $(modal),
                    width: '100%',
                    placeholder: 'Select Customer First',
                }).prop('disabled', true); 



                $('#customer').on('change', function() {
                    const customerId = $(this).val();

                    $('#model').val(null).trigger('change');
                    $('#model').select2('destroy'); 

                    if (customerId) {
                        $('#model').prop('disabled', false);
                        $('#model').select2({
                            dropdownParent: $(modal),
                            width: '100%',
                            placeholder: 'Select Model',
                            ajax: {
                                url: "{{ route('upload.getDataModel') }}", 
                                method: 'POST',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        _token: "{{ csrf_token() }}",
                                        q: params.term,
                                        customer_id: customerId 
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.results
                                    };
                                }
                            }
                        });
                    } else {
                        $('#model').prop('disabled', true).select2({
                            placeholder: 'Select Customer First'
                        });
                    }
                });

            }, 100); 
        });

        const alpineComponent = document.querySelector('[x-data]');
        new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'style' && alpineComponent.style.display === 'none') {
                    $('#customer').off('change');
                    if ($('#customer').hasClass('select2-hidden-accessible')) {
                        $('#customer').select2('destroy');
                    }
                    if ($('#model').hasClass('select2-hidden-accessible')) {
                        $('#model').select2('destroy');
                    }
                }
            });
        }).observe(alpineComponent, {
            attributes: true
        });
    });
</script>

@endsection