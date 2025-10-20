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
                        <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Info</th>
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
                        name: 'Info',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            // We'll render a clickable info icon; but we also make the whole row clickable below
                            return `<button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400" title="Details" onclick="openPackageDetails(${row.id})"><i class="fa-solid fa-circle-info fa-lg"></i></button>`;
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

            // make rows clickable to open details modal
            $('#fileTable tbody').on('click', 'tr', function (e) {
                // avoid triggering when clicking the info button itself
                if ($(e.target).closest('button').length) return;
                const data = table.row(this).data();
                if (data && data.id) {
                    openPackageDetails(data.id);
                }
            });
        });

        function deleteFile(id) {
            if (confirm('Are you sure you want to delete this file?')) {
                // Implement delete logic here (e.g., AJAX call to delete endpoint)
                alert('Delete functionality to be implemented for ID: ' + id);
            }
        }

        // Modal utilities
        function bytesToSize(bytes) {
            if (!bytes && bytes !== 0) return '0 Bytes';
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes === 0) return '0 Bytes';
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function formatDate(dt) {
            if (!dt) return '-';
            // expect ISO string from backend; fallback to raw value
            try {
                const d = new Date(dt);
                if (isNaN(d.getTime())) return dt;
                return d.toLocaleString();
            } catch (e) { return dt; }
        }

        function openPackageDetails(id) {
            // fetch details from API endpoint and build modal dynamically
            // show a transient loader while fetching
            const existing = document.getElementById('package-details-modal');
            if (existing) existing.remove();

            const loaderOverlay = document.createElement('div');
            loaderOverlay.id = 'package-details-modal';
            loaderOverlay.className = 'fixed inset-0 bg-black bg-opacity-40 p-4 flex items-center justify-center z-50';
            loaderOverlay.innerHTML = `<div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center gap-3"><div class="loader-border w-8 h-8 border-4 border-blue-400 rounded-full animate-spin"></div><div class="text-sm text-gray-700 dark:text-gray-300">Loading package details...</div></div>`;
            document.body.appendChild(loaderOverlay);

            fetch('{{ url('/files') }}' + '/' + id)
                .then(res => {
                    if (!res.ok) throw new Error('Failed to load details');
                    return res.json();
                })
                .then(json => {
                    const pkg = json.package || {};
                    const files = json.files || { count: 0, size_bytes: 0 };

                    // remove loader
                    const loader = document.getElementById('package-details-modal');
                    if (loader) loader.remove();

                    // normalize fields (support both package- and revision-based keys)
                    const packageNo = pkg.package_no ?? pkg.packageNo ?? 'N/A';
                    const revisionNo = pkg.revision_no ?? pkg.current_revision_no ?? pkg.revisionNo ?? 0;
                    const customerName = pkg.customer_name ?? pkg.customerCode ?? pkg.customer_code ?? '-';
                    const modelName = pkg.model_name ?? pkg.modelName ?? '-';
                    const partNo = pkg.part_no ?? pkg.partNo ?? '-';
                    const docgroupName = pkg.docgroup_name ?? pkg.docgroupName ?? '-';
                    const subcatName = pkg.subcategory_name ?? pkg.subcategoryName ?? '-';
                    const partGroup = pkg.code_part_group ?? pkg.codePartGroup ?? '-';
                    const createdAt = formatDate(pkg.created_at ?? pkg.revision_created_at ?? pkg.createdAt);
                    const updatedAt = formatDate(pkg.updated_at ?? pkg.updatedAt);
                    const revisionStatus = pkg.revision_status ?? pkg.revisionStatus ?? pkg.status ?? '-';
                    const revisionNote = pkg.revision_note ?? pkg.note ?? '';
                    const isObsolete = pkg.is_obsolete || pkg.isObsolete ? true : false;

                    // build overlay and dialog
                    const overlay = document.createElement('div');
                    overlay.id = 'package-details-modal';
                    overlay.className = 'fixed inset-0 bg-black bg-opacity-40 p-4 flex items-center justify-center z-50';
                    overlay.addEventListener('click', function (ev) {
                        if (ev.target === overlay) closePackageDetails();
                    });

                    const dialog = document.createElement('div');
                    dialog.className = 'bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-2xl w-full overflow-hidden';
                    dialog.setAttribute('role', 'dialog');
                    dialog.setAttribute('aria-modal', 'true');
                    dialog.innerHTML = `
                        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 text-blue-500 bg-blue-100 dark:bg-blue-900/50 rounded-full">
                                    <i class="fa-solid fa-box-archive"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Package Details</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Package No: <span class="font-semibold text-gray-700 dark:text-gray-200">${packageNo}</span></p>
                                </div>
                            </div>
                            <button id="pkg-close-btn" class="text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                                <i class="fa-solid fa-xmark fa-xl"></i>
                            </button>
                        </div>

                        <div class="p-5 max-h-[70vh] overflow-y-auto space-y-6">
                            <!-- Revision & Status -->
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3">Revision & Status</h4>
                                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                                    <div class="sm:col-span-1">
                                        <dt class="text-gray-500">Revision No.</dt>
                                        <dd class="font-semibold text-gray-900 dark:text-gray-100">${revisionNo}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-gray-500">Status</dt>
                                        <dd class="font-semibold">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${revisionStatus === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : (revisionStatus === 'Rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300')}">${revisionStatus}</span>
                                        </dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-gray-500">Obsolete</dt>
                                        <dd class="font-semibold text-gray-900 dark:text-gray-100">${isObsolete ? '<span class="text-red-500">Yes</span>' : 'No'}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Product Info -->
                                <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Product Information</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Customer</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${customerName}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Model</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${modelName}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Part No.</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${partNo}</dd></div>
                                    </dl>
                                </div>
                                <!-- Document Info -->
                                <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Document Classification</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Document Group</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${docgroupName}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Sub Category</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${subcatName}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Part Group</dt><dd class="font-medium text-gray-800 dark:text-gray-200 text-right">${partGroup}</dd></div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Revision Note -->
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Revision Note</h4>
                                <div class="p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${revisionNote || '<span class="italic text-gray-400">No note provided.</span>'}</div>
                            </div>

                            <!-- File & Date Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">File Summary</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Total Files</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${files.count}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Total Size</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${bytesToSize(files.size_bytes)}</dd></div>
                                    </dl>
                                </div>
                                 <div class="space-y-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Timestamps</h4>
                                    <dl class="text-sm space-y-2">
                                        <div class="flex justify-between"><dt class="text-gray-500">Created At</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${createdAt}</dd></div>
                                        <div class="flex justify-between"><dt class="text-gray-500">Last Updated</dt><dd class="font-medium text-gray-800 dark:text-gray-200">${updatedAt}</dd></div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                            <button id="pkg-download-btn" class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                                <i class="fa-solid fa-download"></i>
                                Download Package
                            </button>
                            <button id="pkg-close-btn-2" class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                <i class="fa-solid fa-xmark"></i>
                                Close
                            </button>
                        </div>
                    `;

                    overlay.appendChild(dialog);
                    document.body.appendChild(overlay);

                    // focus management
                    const closeBtn = document.getElementById('pkg-close-btn');
                    const closeBtn2 = document.getElementById('pkg-close-btn-2');
                    if (closeBtn) closeBtn.addEventListener('click', closePackageDetails);
                    if (closeBtn2) closeBtn2.addEventListener('click', closePackageDetails);
                    if (closeBtn) closeBtn.focus();

                    // close on ESC
                    function escHandler(e) { if (e.key === 'Escape') closePackageDetails(); }
                    document.addEventListener('keydown', escHandler);

                    // remove listener when modal removed
                    overlay._cleanup = function() { document.removeEventListener('keydown', escHandler); };
                })
                .catch(err => {
                    const loader = document.getElementById('package-details-modal');
                    if (loader) loader.remove();
                    alert('Unable to load package details: ' + err.message);
                });
        }

        function closePackageDetails() {
            const modal = document.getElementById('package-details-modal');
            if (modal) {
                if (modal._cleanup) try { modal._cleanup(); } catch(e) {}
                modal.remove();
            }
        }
    </script>
@endpush

