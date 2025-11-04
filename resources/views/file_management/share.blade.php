@extends('layouts.app')
{{-- DIUBAH: Judul Halaman --}}
@section('title', 'Share Packages - PROMISE')
@section('header-title', 'Share Packages')

@section('content')

{{--
  DIV x-data ini sudah ada, kita hanya perlu menambahkan modal di dalamnya.
  'modalOpen: false' akan kita gunakan untuk modal share. 
--}}
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900" x-data="{ modalOpen: false }">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            {{-- DIUBAH: Judul Konten --}}
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Share Packages</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Share packages with other roles.</p>
        </div>

        {{-- DIHAPUS: Seluruh bagian 4 KPI Card di sini --}}

    </div>

    {{-- Filter section --}}
    <div class="mt-8 bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Filters</h3>
            <div class="flex items-center gap-2">

                {{-- DIHAPUS: Tombol Download Summary --}}

                <button id="btnResetFilters"
                    type="button"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600
                bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-rotate-left"></i>
                    Reset Filters
                </button>
            </div>
        </div>


        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5">
            @foreach(['Customer', 'Model', 'Document Type', 'Category', 'Status'] as $label)
            <div>
                <label for="{{ Str::slug($label) }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                <div class="relative mt-1">
                    <select id="{{ Str::slug($label) }}"
                        class="js-filter appearance-none block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="All" selected>All</option>
                    </select>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tabel section --}}
    <div class="mt-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-hidden">
            <table id="approvalTable" class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Package Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Decision Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-300">
                </tbody>
            </table>
        </div>
    </div>


    {{-- ====================================================== --}}
    {{-- HTML MODAL UNTUK SHARE (TETAP)            --}}
    {{-- ====================================================== --}}
    <div x-show="modalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75"
        @click.away="modalOpen = false"
        style="display: none;"
        x-cloak>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Bagikan Paket Dokumen</h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fa-solid fa-times fa-lg"></i>
                </button>
            </div>

            <div class="p-6">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    Pilih role untuk membagikan paket ini. Kolom `share_to` akan di-update sesuai pilihan Anda.
                </p>

                <div>
                    <label for="selectRole" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Role</label>
                    <select id="selectRole" name="role_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Memuat role...</option>
                    </select>
                    <p id="shareError" class="text-red-500 text-sm mt-2" style="display: none;"></p>
                </div>

                <input type="hidden" id="hiddenPackageId" value="">
            </div>

            <div class="flex justify-end p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 rounded-b-lg space-x-3">
                <button @click="modalOpen = false"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Batal
                </button>
                <button id="btnSaveShare"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan & Bagikan
                </button>
            </div>
        </div>
    </div>


</div>

@endsection
@push('scripts')
<script>
    $(function() {
        let table;
        // DIUBAH: Pastikan route ini masih valid untuk filter
        const ENDPOINT = '{{ route("approvals.filters") }}';

        // --- helper: reset Select2 ke "All" (pasti sukses untuk AJAX mode) ---
        function resetSelect2ToAll($el) {
            $el.empty();
            const opt = new Option('All', 'All', true, true);
            $el.append(opt);
            $el.trigger('change');
            $el.trigger('select2:select');
        }

        // --- Select2 AJAX (server-side) helper ---
        function makeSelect2($el, field, extraParamsFn) {
            $el.select2({
                width: '100%',
                placeholder: 'All',
                allowClear: false,
                minimumResultsForSearch: 0,
                ajax: {
                    url: ENDPOINT,
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function(params) {
                        const p = {
                            select2: field,
                            q: params.term || '',
                            page: params.page || 1
                        };
                        if (typeof extraParamsFn === 'function') {
                            Object.assign(p, extraParamsFn());
                        }
                        return p;
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        // Pastikan "All" selalu ada di hasil (paling atas)
                        const results = Array.isArray(data.results) ? data.results.slice() : [];
                        if (params.page === 1 && !results.some(r => r.id === 'All')) {
                            results.unshift({
                                id: 'All',
                                text: 'All'
                            });
                        }
                        return {
                            results,
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    }
                },
                templateResult: function(item) {
                    if (item.loading) return item.text;
                    return $('<div class="text-sm">' + (item.text || item.id) + '</div>');
                },
                templateSelection: function(item) {
                    return item.text || item.id || 'All';
                }
            });
        }

        // Inisialisasi Select2 server-side + dependent params
        makeSelect2($('#customer'), 'customer');
        makeSelect2($('#model'), 'model', () => ({
            customer_code: $('#customer').val() || ''
        }));
        makeSelect2($('#document-type'), 'doc_type');
        makeSelect2($('#category'), 'category', () => ({
            doc_type: $('#document-type').val() || ''
        }));
        makeSelect2($('#status'), 'status');

        // Dependent behavior -> set anak ke "All" (bukan null)
        $('#customer').on('change', function() {
            resetSelect2ToAll($('#model'));
        });
        $('#document-type').on('change', function() {
            resetSelect2ToAll($('#category'));
        });

        function getCurrentFilters() {
            const valOrAll = v => (v && v.length ? v : 'All');
            return {
                customer: valOrAll($('#customer').val()),
                model: valOrAll($('#model').val()),
                doc_type: valOrAll($('#document-type').val()),
                category: valOrAll($('#category').val()),
                status: valOrAll($('#status').val()),
            };
        }

        // formatter tanggal (tetap)
        function fmtDate(v) {
            if (!v) return '';
            const d = new Date(v);
            if (isNaN(d)) return v;
            const pad = n => n.toString().padStart(2, '0');
            const dd = pad(d.getDate());
            const MM = pad(d.getMonth() + 1);
            const yyyy = d.getFullYear();
            const HH = pad(d.getHours());
            const mm = pad(d.getMinutes());
            return `${dd}-${MM}-${yyyy} ${HH}:${mm}`;
        }


        function initTable() {
            table = $('#approvalTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    // ======================================================
                    // DIUBAH KEMBALI: Mengambil data dari route 'approvals.list'
                    // agar datanya SAMA PERSIS dengan halaman Approvals
                    // ======================================================
                    url: '{{ route("approvals.list") }}',
                    type: 'GET',
                    data: function(d) {
                        const f = getCurrentFilters();
                        d.customer = f.customer;
                        d.model = f.model;
                        d.doc_type = f.doc_type;
                        d.category = f.category;
                        d.status = f.status;
                    }
                },

                // default: Request Date terbaru di atas (kolom index 2)
                order: [
                    [2, 'desc']
                ],

                columns: [
                    // No (index 0)
                    {
                        data: null,
                        orderable: false,
                        searchable: false
                    },

                    // Package Data (index 1)
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(row) {
                            const revVal = row.revision ?? row.revision_no;
                            const revTxt = (revVal !== undefined && revVal !== null && revVal !== '') ?
                                `rev ${revVal}` :
                                '';

                            const parts = [
                                row.customer,
                                row.model,
                                row.doc_type,
                                row.category,
                                row.part_no,
                                revTxt
                            ].filter(Boolean);

                            return `<div class="text-sm">${parts.join(' - ')}</div>`;
                        }
                    },


                    // Request Date (index 2)
                    {
                        data: 'request_date',
                        name: 'dpr.requested_at',
                        render: function(v) {
                            const text = fmtDate(v);
                            return `<span title="${v || ''}">${text}</span>`;
                        }
                    },

                    // Decision Date (index 3)
                    {
                        data: 'decision_date',
                        name: 'dpr.decided_at',
                        render: function(v, t, row) {
                            if (!v || row.status === 'Waiting')
                                return '<span class="text-gray-400">—</span>';
                            const text = fmtDate(v);
                            return `<span title="${row.status} at ${v}">${text}</span>`;
                        }
                    },

                    // Status (index 4)
                    {
                        data: 'status',
                        name: 'dpr.revision_status',
                        render: function(data) {
                            let cls = '';
                            if (data === 'Rejected') cls = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                            if (data === 'Waiting') cls = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
                            if (data === 'Approved') cls = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
                            return `<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${cls}">${data ?? ''}</span>`;
                        }
                    },

                    // --- Kolom Actions (index 5) (TETAP) ---
                    {
                        // Pastikan route 'approvals.list' Anda MENGIRIMKAN 'id'
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center whitespace-nowrap',
                        render: function(packageId, type, row) {
                            // Tombol Share
                            return `
                                <button 
                                    type="button" 
                                    class="btn-share px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-300 
                                           bg-blue-100 dark:bg-blue-900/50 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/80"
                                    data-id="${packageId}" 
                                    title="Share package ${packageId}">
                                    <i class="fa-solid fa-share-nodes fa-fw"></i> Share
                                </button>
                            `;
                        }
                    }
                ],

                columnDefs: [{
                        targets: 0,
                        className: 'text-center w-12',
                        width: '48px'
                    },
                    {
                        targets: [2, 3], // Request & Decision Date
                        className: 'whitespace-nowrap'
                    }
                ],

                responsive: true,
                dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 p-2 text-gray-700 dark:text-gray-300"lf>t<"flex items-center justify-between mt-4"<"text-sm text-gray-500 dark:text-gray-400"i><"flex justify-end"p>>',
                createdRow: function(row) {
                    $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700/50');
                }
            });

            // Penomoran ulang setiap draw
            table.on('draw.dt', function() {
                const info = table.page.info();
                table.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + info.start;
                });
            });
        }


        function bindHandlers() {
            // perubahan filter -> reload 
            $('#customer, #model, #document-type, #category, #status').on('change', function() {
                if (table) table.ajax.reload(null, true);
            });

            // tombol reset -> set semua ke "All", reload table
            $('#btnResetFilters').on('click', function() {
                resetSelect2ToAll($('#customer'));
                resetSelect2ToAll($('#model'));
                resetSelect2ToAll($('#document-type'));
                resetSelect2ToAll($('#category'));
                resetSelect2ToAll($('#status'));

                if (table) table.ajax.reload(null, true);
            });

            // --- Logika Modal Share (TETAP) ---
            const $rootEl = $('[x-data="{ modalOpen: false }"]');
            const $selectRole = $('#selectRole');
            const $hiddenPackageId = $('#hiddenPackageId');
            const $shareError = $('#shareError');
            const $btnSaveShare = $('#btnSaveShare');

            // 1. Fungsi untuk memuat roles ke <select>
            function loadRoles() {
                $selectRole.html('<option value="">Memuat role...</option>').prop('disabled', true);
                $.ajax({
                    url: '{{ route("share.getRoles") }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(roles) {
                        $selectRole.empty().prop('disabled', false);
                        $selectRole.append('<option value="" selected disabled>-- Pilih Role --</option>');
                        if (roles && roles.length > 0) {
                            roles.forEach(function(role) {
                                $selectRole.append(new Option(role.name, role.id));
                            });
                        } else {
                            $selectRole.append('<option value="">Tidak ada role ditemukan</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Gagal memuat roles:', xhr.responseText);
                        $selectRole.html('<option value="">Gagal memuat role</option>');
                    }
                });
            }

            // 2. Listener untuk tombol "Share" di setiap baris tabel
            $('#approvalTable tbody').on('click', '.btn-share', function(e) {
                e.stopPropagation();

                const packageId = $(this).data('id');
                if (!packageId) return;

                $hiddenPackageId.val(packageId);
                $shareError.hide().text('');
                $btnSaveShare.prop('disabled', false).text('Simpan & Bagikan');

                loadRoles();

                if ($rootEl[0] && $rootEl[0].__x) {
                    $rootEl[0].__x.data.modalOpen = true;
                }
            });

            // 3. Listener untuk tombol "Simpan & Bagikan" di modal
            $btnSaveShare.on('click', function() {
                const packageId = $hiddenPackageId.val();
                const roleId = $selectRole.val();

                if (!packageId || !roleId) {
                    $shareError.text('Silakan pilih role terlebih dahulu.').show();
                    return;
                }

                $shareError.hide().text('');
                $btnSaveShare.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...');

                $.ajax({
                    url: `/share/package/${packageId}`, // Gunakan route yang kita buat
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        role_id: roleId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if ($rootEl[0] && $rootEl[0].__x) {
                            $rootEl[0].__x.data.modalOpen = false;
                        }

                        alert(response.success || 'Berhasil dibagikan!');

                        $btnSaveShare.prop('disabled', false).text('Simpan & Bagikan');

                        // table.ajax.reload(null, false); // Opsional
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        console.error('Gagal share:', xhr.responseText);
                        $shareError.text(errorMsg).show();
                        $btnSaveShare.prop('disabled', false).text('Simpan & Bagikan');
                    }
                });
            });
            // --- SELESAI: Logika Modal Share ---


            // DIHAPUS: Blok 'click tr' untuk pindah halaman
            // $('#approvalTable tbody').on('click', 'tr', function(e) { ... });

        }

        // start
        initTable();
        bindHandlers();
    });
</script>
@endpush