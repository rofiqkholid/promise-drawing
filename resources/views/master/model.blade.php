@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Model Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Model Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- Main Content: Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="modelsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="customer">Customer Code</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">Model Name</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="status">Status</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="planning">Planning</th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Model Modal --}}
<div id="addModelModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Model</h3>
            <form id="addModelForm" action="{{ route('models.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer</label>
                    <select name="customer_id" id="customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <option value="">Select Customer</option>
                    </select>
                    <p id="add-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model Name</label>
                    <input type="text" name="name" id="name" maxlength="50" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Product X" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="status_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <select name="status_id" id="status_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <option value="">Select Status</option>
                    </select>
                    <p id="add-status_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="planning" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Planning (Qty)</label>
                    <input type="number" name="planning" id="planning" min="0" step="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 200" required>
                    <p id="add-planning-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Model Modal --}}
<div id="editModelModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Model</h3>
            <form id="editModelForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer</label>
                    <select name="customer_id" id="edit_customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <option value="">Select Customer</option>
                    </select>
                    <p id="edit-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model Name</label>
                    <input type="text" name="name" id="edit_name" maxlength="50" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_status_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <select name="status_id" id="edit_status_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                        <option value="">Select Status</option>
                    </select>
                    <p id="edit-status_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_planning" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Planning (Qty)</label>
                    <input type="number" name="planning" id="edit_planning" min="0" step="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-planning-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModelModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this model?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                    No, cancel
                </button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                    Yes, I'm sure
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/select2.css') }}" rel="stylesheet" />
@endpush

@push('style')
<style>
    div.dataTables_length label { font-size: 0.75rem; }
    div.dataTables_length select {
        font-size: 0.75rem; line-height: 1rem; padding: 0.25rem 1.25rem 0.25rem 0.5rem;
        height: 1.875rem; width: 4.5rem;
    }
    div.dataTables_filter label { font-size: 0.75rem; }
    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="modelsTable"] {
        font-size: 0.75rem; line-height: 1rem; padding: 0.25rem 0.5rem;
        height: 1.875rem; width: 12rem;
    }
    div.dataTables_info { font-size: 0.75rem; padding-top: 0.8em; }
    .select2-container--default .select2-selection--single {
        display: flex; align-items: center; justify-content: flex-start !important; text-align: left !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { text-align: left !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { right: 10px !important; }
    div.dataTables_wrapper div.dataTables_scrollBody::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    div.dataTables_wrapper div.dataTables_scrollBody { -ms-overflow-style: none !important; scrollbar-width: none !important; }
    input::placeholder { text-align: left; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ========= Toast seperti sample =========
    function detectTheme(){
        const isDark = document.documentElement.classList.contains('dark');
        return isDark ? {
            bg:'rgba(30,41,59,.95)', fg:'#E5E7EB', border:'rgba(71,85,105,.5)', progress:'rgba(255,255,255,.9)',
            icon:{success:'#22c55e', error:'#ef4444', warning:'#f59e0b', info:'#3b82f6'}
        } : {
            bg:'rgba(255,255,255,.98)', fg:'#0f172a', border:'rgba(226,232,240,1)', progress:'rgba(15,23,42,.8)',
            icon:{success:'#16a34a', error:'#dc2626', warning:'#d97706', info:'#2563eb'}
        };
    }
    const BaseToast = Swal.mixin({
        toast:true, position:'top-end', showConfirmButton:false, timer:2600, timerProgressBar:true,
        showClass:{popup:'swal2-animate-toast-in'}, hideClass:{popup:'swal2-animate-toast-out'},
        didOpen:(el)=>{
            el.addEventListener('mouseenter', Swal.stopTimer);
            el.addEventListener('mouseleave', Swal.resumeTimer);
            const t = detectTheme();
            const bar = el.querySelector('.swal2-timer-progress-bar'); if(bar) bar.style.background = t.progress;
            const popup = el.querySelector('.swal2-popup'); if(popup) popup.style.borderColor = t.border;
        }
    });
    function renderToast({icon='success', title='Success', text=''} = {}){
        const t = detectTheme();
        BaseToast.fire({
            icon, title, text,
            iconColor: t.icon[icon] || t.icon.success,
            background: t.bg, color: t.fg,
            customClass:{ popup:'swal2-toast border' }
        });
    }
    function toastSuccess(t='Success', m='Operation success'){ renderToast({icon:'success', title:t, text:m}); }
    function toastError(t='Error', m='Something went wrong'){ BaseToast.update({timer:3400}); renderToast({icon:'error', title:t, text:m}); BaseToast.update({timer:2600}); }

    // ========= Select2 AJAX endpoints =========
    const SELECT2_CUSTOMERS_URL = '{{ route("models.customers.select2") }}';
    const SELECT2_STATUSES_URL  = '{{ route("models.statuses.select2") }}';

    // Init Select2 (server-side) for modal Add
    $('#customer_id').select2({
        dropdownParent: $('#addModelModal'),
        width: '100%',
        placeholder: 'Select Customer',
        ajax: {
            url: SELECT2_CUSTOMERS_URL,
            dataType: 'json',
            delay: 250,
            cache: true,
            data: params => ({ q: params.term || '', page: params.page || 1 }),
            processResults: data => ({
                results: data.results || [],
                pagination: { more: data.pagination ? data.pagination.more : false }
            })
        },
        // tampilkan code saja
        templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.id) + '</span>'),
        templateSelection: it => it.text || it.id || ''
    });
    $('#status_id').select2({
        dropdownParent: $('#addModelModal'),
        width: '100%',
        placeholder: 'Select Status',
        ajax: {
            url: SELECT2_STATUSES_URL,
            dataType: 'json',
            delay: 250,
            cache: true,
            data: params => ({ q: params.term || '', page: params.page || 1 }),
            processResults: data => ({
                results: data.results || [],
                pagination: { more: data.pagination ? data.pagination.more : false }
            })
        },
        templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.id) + '</span>'),
        templateSelection: it => it.text || it.id || ''
    });

    // Init Select2 (server-side) untuk modal Edit
    $('#edit_customer_id').select2({
        dropdownParent: $('#editModelModal'),
        width: '100%',
        placeholder: 'Select Customer',
        ajax: {
            url: SELECT2_CUSTOMERS_URL,
            dataType: 'json',
            delay: 250,
            cache: true,
            data: params => ({ q: params.term || '', page: params.page || 1 }),
            processResults: data => ({
                results: data.results || [],
                pagination: { more: data.pagination ? data.pagination.more : false }
            })
        },
        templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.id) + '</span>'),
        templateSelection: it => it.text || it.id || ''
    });
    $('#edit_status_id').select2({
        dropdownParent: $('#editModelModal'),
        width: '100%',
        placeholder: 'Select Status',
        ajax: {
            url: SELECT2_STATUSES_URL,
            dataType: 'json',
            delay: 250,
            cache: true,
            data: params => ({ q: params.term || '', page: params.page || 1 }),
            processResults: data => ({
                results: data.results || [],
                pagination: { more: data.pagination ? data.pagination.more : false }
            })
        },
        templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.id) + '</span>'),
        templateSelection: it => it.text || it.id || ''
    });

    // Helper set value Select2 saat edit (insert option manual: value=id, text=label)
    function setSelect2Value($select, id, label) {
        if (!id) { $select.val(null).trigger('change'); return; }
        const opt = new Option(label ?? id, id, true, true);
        $select.append(opt).trigger('change');
    }

    // ========= DataTable =========
    const table = $('#modelsTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{{ route("models.data") }}',
            type: 'GET',
            data: d => { d.search = d.search.value; }
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'customer.code', name: 'customer' },
            { data: 'name',         name: 'name' },
            { data: 'status.name',  name: 'status' },
            { data: 'planning',     name: 'planning' },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: (data, type, row) => `
                    <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}">
                        <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
                    </button>
                    <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}">
                        <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                    </button>
                `
            }
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        order: [[2, 'asc']],
        language: { emptyTable: '<div class="text-gray-500 dark:text-gray-400">No models found.</div>' },
        responsive: true,
        autoWidth: false,
    });

    // ========= Modal helpers =========
    const addModal   = $('#addModelModal');
    const editModal  = $('#editModelModal');
    const deleteModal= $('#deleteModelModal');
    const addButton  = $('#add-button');
    let modelIdToDelete = null;

    function showModal(modal){ modal.removeClass('hidden').addClass('flex'); }
    function hideModal(modal){ modal.addClass('hidden').removeClass('flex'); }

    // Button loading & form busy helpers
    const spinnerSVG = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>`;
    function setButtonLoading($btn, isLoading, text='Processing...'){
        if(!$btn || !$btn.length) return;
        if(isLoading){
            if(!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
            $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed')
                .html(`<span class="inline-flex items-center gap-2">${spinnerSVG}${text}</span>`);
        }else{
            const o = $btn.data('orig-html'); if(o) $btn.html(o);
            $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
        }
    }
    function setFormBusy($form, busy){ $form.find('input, select, textarea, button').prop('disabled', busy); }

    // ========= Add button → open modal + reset fields =========
    addButton.on('click', () => {
        $('#addModelForm')[0].reset();
        $('#customer_id').val(null).trigger('change');
        $('#status_id').val(null).trigger('change');
        $('#planning').val('');
        showModal(addModal);
    });

    $(document).on('click', '.close-modal-button', function(){ hideModal($(this).closest('[id$="Modal"]')); });

    // ========= Create =========
    $('#addModelForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this), $btn = $form.find('[type="submit"]');
        $('#add-customer_id-error,#add-name-error,#add-status_id-error,#add-planning-error').addClass('hidden').text('');

        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData, processData: false, contentType: false,
            beforeSend: () => { setButtonLoading($btn, true, 'Saving...'); setFormBusy($form, true); },
            success: (data) => {
                if (data.success) {
                    table.ajax.reload(null, false);
                    hideModal(addModal);
                    $form[0].reset();
                    $('#customer_id').val(null).trigger('change');
                    $('#status_id').val(null).trigger('change');
                    toastSuccess('Success', 'Model added successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to add model.');
                }
            },
            error: (xhr) => {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    if (errors.customer_id) $('#add-customer_id-error').text(errors.customer_id[0]).removeClass('hidden');
                    if (errors.name)        $('#add-name-error').text(errors.name[0]).removeClass('hidden');
                    if (errors.status_id)   $('#add-status_id-error').text(errors.status_id[0]).removeClass('hidden');
                    if (errors.planning)    $('#add-planning-error').text(errors.planning[0]).removeClass('hidden');
                }
                toastError('Validation error', xhr.responseJSON?.message || 'Please check your input.');
            },
            complete: () => { setButtonLoading($btn, false); setFormBusy($form, false); }
        });
    });

    // ========= Open Edit =========
    $(document).on('click', '.edit-button', function() {
        const id = $(this).data('id');
        $('#edit-customer_id-error,#edit-name-error,#edit-status_id-error,#edit-planning-error').addClass('hidden').text('');

        $.ajax({
            url: `/master/models/${id}`,
            method: 'GET',
            beforeSend: () => { setButtonLoading($(`.edit-button[data-id="${id}"]`), true, ''); },
            success: (data) => {
                // data: {customer_id, customer_code, status_id, status_name, name, planning}
                setSelect2Value($('#edit_customer_id'), data.customer_id, data.customer_code || data.customer_id);
                setSelect2Value($('#edit_status_id'),  data.status_id,  data.status_name  || data.status_id);
                $('#edit_name').val(data.name || '');
                $('#edit_planning').val(data.planning ?? '');
                $('#editModelForm').attr('action', `/master/models/${id}`);
                showModal(editModal);
            },
            error: (xhr) => {
                toastError('Error', xhr.responseJSON?.message || 'Failed to fetch model data.');
            },
            complete: () => { setButtonLoading($(`.edit-button[data-id="${id}"]`), false); }
        });
    });

    // ========= Update =========
    $('#editModelForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this), $btn = $form.find('[type="submit"]');
        $('#edit-customer_id-error,#edit-name-error,#edit-status_id-error,#edit-planning-error').addClass('hidden').text('');

        const formData = new FormData(this); // @method('PUT') sudah ada

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData, processData: false, contentType: false,
            beforeSend: () => { setButtonLoading($btn, true, 'Saving...'); setFormBusy($form, true); },
            success: (data) => {
                if (data.success) {
                    table.ajax.reload(null, false);
                    hideModal(editModal);
                    toastSuccess('Success', 'Model updated successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to update model.');
                }
            },
            error: (xhr) => {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    if (errors.customer_id) $('#edit-customer_id-error').text(errors.customer_id[0]).removeClass('hidden');
                    if (errors.name)        $('#edit-name-error').text(errors.name[0]).removeClass('hidden');
                    if (errors.status_id)   $('#edit-status_id-error').text(errors.status_id[0]).removeClass('hidden');
                    if (errors.planning)    $('#edit-planning-error').text(errors.planning[0]).removeClass('hidden');
                }
                toastError('Validation error', xhr.responseJSON?.message || 'Please check your input.');
            },
            complete: () => { setButtonLoading($btn, false); setFormBusy($form, false); }
        });
    });

    // ========= Delete =========
    $(document).on('click', '.delete-button', function() {
        modelIdToDelete = $(this).data('id');
        showModal(deleteModal);
    });

    $('#confirmDeleteButton').on('click', function() {
        if (!modelIdToDelete) return;
        const $btn = $(this);

        $.ajax({
            url: `/master/models/${modelIdToDelete}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            beforeSend: () => { setButtonLoading($btn, true, 'Deleting...'); setFormBusy($('#deleteModelModal'), true); },
            success: (data) => {
                if (data.success) {
                    table.ajax.reload(null, false);
                    hideModal(deleteModal);
                    modelIdToDelete = null;
                    toastSuccess('Success', 'Model deleted successfully.');
                } else {
                    toastError('Error', data.message || 'Failed to delete model.');
                }
            },
            error: (xhr) => {
                toastError('Error', xhr.responseJSON?.message || 'Failed to delete model.');
            },
            complete: () => { setButtonLoading($btn, false); setFormBusy($('#deleteModelModal'), false); }
        });
    });

    // Minor UX: perbaiki focus style DT controls
    const overrideFocus = function(){ $(this).css({'outline':'none','box-shadow':'none','border-color':'gray'}); };
    const restoreFocus  = function(){ $(this).css('border-color',''); };
    const elementsToFix = $('.dataTables_filter input, .dataTables_length select');
    elementsToFix.on('focus keyup', overrideFocus).on('blur', restoreFocus).filter(':focus').each(overrideFocus);
});
</script>
@endpush
