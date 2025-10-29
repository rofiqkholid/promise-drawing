@extends('layouts.app')

@section('title', 'Revision History - File Manager')
@section('header-title', 'File Manager/Revision History')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen">

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fa-solid fa-history mr-3 text-blue-600"></i> Revision History
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('file-manager.export') }}"
                   class="inline-flex items-center text-sm px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Package List
                </a>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Customer</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $package->customer_code ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Model</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $package->model_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Part No</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $package->part_no ?? '-' }}</dd>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="historyTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revision Label</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revision No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ECN No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Release Date</th>
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
$(function () {
    let table = $('#historyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("api.export.history.list", ["package_id" => $package_id]) }}',
            type: 'GET',
        },
        order: [[ 4, 'desc' ]],

        columns: [
            { data: null, orderable: false, searchable: false },
            {
                data: 'revision_label_name',
                name: 'crl.label',
                defaultContent: '-'
            },
            {
                data: 'revision_no',
                name: 'dpr.revision_no'
            },
            {
                data: 'ecn_no',
                name: 'dpr.ecn_no',
                defaultContent: '-'
            },
            {
                data: 'release_date',
                name: 'dpr.created_at',
                render: (data) => data ? new Date(data).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
            },
        ],
        responsive: true,
        dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
    });

    table.on('draw.dt', function () {
        const info = table.page.info();
        table.column(0, { page: 'current' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + info.start;
        });
    });

    $('#historyTable tbody').on('click', 'tr', function () {
        const row = table.row(this).data();
        if (row && row.id) {
            window.location.href = `/file-manager.export/${row.id}`;
        }
    }).on('mouseenter', 'tr', function () {
        $(this).css('cursor', 'pointer');
    });
});
</script>
@endpush

