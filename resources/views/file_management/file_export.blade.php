@extends('layouts.app')

@section('title', 'Download - File Manager')
@section('header-title', 'File Manager/Download')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900">
    <div class="mb-8">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 sm:text-3xl">Download Files</h1>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Find and download your files from the Data Center.</p>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 mb-8">
        <div class="p-6">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-filter text-blue-500"></i>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Filter Options</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                    <div class="relative mt-1">
                        <input type="text" id="search" name="search" placeholder="Search by Part No..." class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm py-2.5 px-4">
                    </div>
                </div>

                @foreach(['Customer', 'Model', 'Document Type', 'Category'] as $label)
                    @php $modelName = lcfirst(str_replace(' ', '', $label)); @endphp
                    <div>
                        <label for="{{ $modelName }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <div class="relative mt-1">
                            <select id="{{ $modelName }}" name="{{ $modelName }}" class="appearance-none block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm py-2.5 pl-4 pr-12">
                                <option value="">All</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-chevron-down fa-xs"></i>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    <label for="revision" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Revision</label>
                    <div class="relative mt-1">
                        <select id="revision" name="revision" class="appearance-none block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm py-2.5 pl-4 pr-12">
                            <option value="all">All Revisions</option>
                            <option value="latest">Latest Revision</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-chevron-down fa-xs"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="downloadableFilesTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">No</th>

                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Customer</th>
                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Model</th>
                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Part No</th>
                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Doc Type</th>
                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Sub Category</th>
                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Revision</th>
                        <th scope="col" class="py-3.5 px-4 text-center text-sm font-semibold text-gray-900 dark:text-gray-200">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
                    {{-- DataTables will populate this area --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let table = $('#downloadableFilesTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("api.files.downloadable") }}',
                type: 'GET',
            },
            columns: [
                { data: null, name: 'No', orderable: false, searchable: false },
                { data: 'customer', name: 'Customer' },
                { data: 'model', name: 'Model' },
                { data: 'part_no', name: 'Part No' },
                { data: 'doc_type', name: 'Doc Type' },
                { data: 'sub_category', name: 'Sub Category' },
                { data: 'revision', name: 'Revision' },
                {
                    data: null,
                    name: 'Actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                            <button class="inline-flex items-center gap-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md shadow-sm" onclick="downloadFile(${row.id})">
                                <i class="fa-solid fa-download fa-sm"></i> Download
                            </button>
                        `;
                    }
                }
            ],
            responsive: true,
            dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
        });

        table.on('draw.dt', function () {
            var PageInfo = $('#downloadableFilesTable').DataTable().page.info();
            table.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    });

    function downloadFile(id) {
        alert('Download functionality to be implemented for file ID: ' + id);
    }
</script>
@endpush
