@extends('layouts.app')
@section('title', 'File Manager - PROMISE')
@section('header-title', 'File Manager/Dashboard')

@section('content')

<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 font-sans">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Upload Files</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage and upload your files to the Data Center.</p>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Card 1 --}}
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-blue-500 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/50 rounded-full">
                <i class="fa-solid fa-box-archive fa-lg"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Upload</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">512</p>
            </div>
        </div>
        {{-- Card 2 --}}
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-teal-500 dark:text-teal-400 bg-teal-100 dark:bg-teal-900/50 rounded-full">
                <i class="fa-solid fa-ruler-combined fa-lg"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dwg Study</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">300</p>
            </div>
        </div>
        {{-- Card 3 --}}
        <div class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 text-purple-500 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/50 rounded-full">
                <i class="fa-solid fa-industry fa-lg"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Go Mfg</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">198</p>
            </div>
        </div>
        {{-- Card 4 --}}
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

    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-2">
            <a href="{{ route('drawing.upload') }}"
                class="w-full sm:w-auto inline-flex items-center gap-2 justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                <i class="fa-solid fa-upload"></i>
                Upload Drawing Package
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table id="fileTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Part No</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revision</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uploaded At</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#fileTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route("api.files.list") }}',
                    type: 'GET',
                },
                columns: [
                    { data: null, name: 'No', orderable: false, searchable: false },
                    { data: 'customer', name: 'Customer' },
                    { data: 'model', name: 'Model' },
                    { data: 'part_no', name: 'Part No' },
                    { data: 'revision', name: 'Revision' },
                    { data: 'uploaded_at', name: 'Uploaded At' },
                    {
                        data: 'status',
                        name: 'Status',
                        render: function(data, type, row) {
                            let colorClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
                            if (data === 'Rejected') {
                                colorClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                            } else if (data === 'Approved') {
                                colorClass = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
                            }
                            return `<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${colorClass}">${data}</span>`;
                        }
                    },
                    {
                        data: null,
                        name: 'Action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex items-center justify-center gap-4">
                                    <a href="/drawing-upload/${row.id}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit"><i class="fa-solid fa-pen-to-square fa-lg"></i></a>
                                    <button class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" onclick="deleteFile(${row.id})"><i class="fa-solid fa-trash-can fa-lg"></i></button>
                                </div>
                            `;
                        }
                    }
                ],
                responsive: true,
                dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
            });

            table.on('draw.dt', function () {
                var PageInfo = $('#fileTable').DataTable().page.info();
                table.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });
        });

        function deleteFile(id) {
            if (confirm('Are you sure you want to delete this file?')) {
                // Implement delete logic here (e.g., AJAX call to delete endpoint)
                alert('Delete functionality to be implemented for ID: ' + id);
            }
        }
    </script>
@endpush
