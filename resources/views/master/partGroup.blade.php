@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Part Group Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Part Group Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <button type="button" id="manage-master-btn" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-list"></i>
                Manage Codes
            </button>
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="partGroupsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="customer_code">Customer Code</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="model_name">Model Code</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="model_status">Status</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code_part_group">Part Group Code</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="planning">Planning</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code_part_group_desc">Description</th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Part Group Modal --}}
<div id="addPartGroupModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add New Part Group</h3>
            <form id="addPartGroupForm" action="{{ route('partGroups.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer <span class="text-red-600">*</span></label>
                    <select name="customer_id" id="customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select Customer</option>
                    </select>
                    <p id="add-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model <span class="text-red-600">*</span></label>
                    <select name="model_id" id="model_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required disabled>
                        <option value="">Select Model</option>
                    </select>
                    <p id="add-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="code_part_group" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Group Code <span class="text-red-600">*</span></label>
                    <div class="flex gap-2">
                        <select name="code_part_group" id="code_part_group" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="">Select Code from Library</option>
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-left">Select Standard Code from Library.</p>
                </div>
                <div class="mb-4">
                    <label for="planning" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Planning <span class="text-red-600">*</span></label>
                    <input type="number" name="planning" id="planning" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 1000" required>
                    <p id="add-planning-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="code_part_group_desc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Description</label>
                    <input type="text" name="code_part_group_desc" id="code_part_group_desc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Engine Components">
                    <p id="add-code_part_group_desc-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Part Group Modal --}}
<div id="editPartGroupModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Part Group</h3>
            <form id="editPartGroupForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer <span class="text-red-600">*</span></label>
                    <select name="customer_id" id="edit_customer_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="">Select Customer</option>
                    </select>
                    <p id="edit-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model <span class="text-red-600">*</span></label>
                    <select name="model_id" id="edit_model_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required disabled>
                        <option value="">Select Model</option>
                    </select>
                    <p id="edit-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_code_part_group" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Group Code <span class="text-red-600">*</span></label>
                    <div class="flex gap-2">
                        <select name="code_part_group" id="edit_code_part_group" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="">Select Code</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="edit_planning" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Planning <span class="text-red-600">*</span></label>
                    <input type="number" name="planning" id="edit_planning" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-planning-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_code_part_group_desc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Description</label>
                    <input type="text" name="code_part_group_desc" id="edit_code_part_group_desc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <p id="edit-code_part_group_desc-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deletePartGroupModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this part group?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">Yes, I'm sure</button>
            </div>
        </div>
    </div>
</div>
{{-- Manage Master Modal (Contains Table & Form) --}}
<div id="manageMasterModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-4xl h-full md:h-auto">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-manage-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Manage Part Group Codes Library</h3>
            
            {{-- Inline Add Form --}}
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h4 class="text-sm font-semibold mb-2 dark:text-gray-200">Add / Edit Code</h4>
                <form id="masterForm" class="flex flex-col sm:flex-row gap-4 items-end">
                    <input type="hidden" name="id" id="master_id_input">
                    <div class="w-full sm:w-1/3">
                        <label class="block mb-1 text-xs text-gray-600 dark:text-gray-300">Code <span class="text-red-600">*</span></label>
                        <input type="text" name="code" id="master_code_input" class="bg-white border border-gray-300 text-gray-900 text-xs rounded-lg block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white" required>
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label class="block mb-1 text-xs text-gray-600 dark:text-gray-300">Description</label>
                        <input type="text" name="description" id="master_desc_input" class="bg-white border border-gray-300 text-gray-900 text-xs rounded-lg block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div class="w-full sm:w-auto flex gap-2">
                        <button type="submit" id="save-master-btn" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700">Add</button>
                        <button type="button" id="cancel-master-btn" class="hidden px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Master Table --}}
            <div class="overflow-x-auto">
                <table id="masterTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Code</th>
                            <th scope="col" class="px-4 py-3">Description</th>
                            <th scope="col" class="px-4 py-3 w-20 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
    div.dataTables_length label {
        font-size: .75rem;
    }

    div.dataTables_length select {
        font-size: .75rem;
        line-height: 1rem;
        padding: .25rem 1.25rem .25rem .5rem;
        height: 1.875rem;
        width: 4.5rem;
    }

    div.dataTables_filter label {
        font-size: .75rem;
    }

    div.dataTables_filter input[type="search"],
    input[type="search"][aria-controls="departmentsTable"] {
        font-size: .75rem;
        line-height: 1rem;
        padding: .25rem .5rem;
        height: 1.875rem;
        width: 12rem;
    }

    div.dataTables_info {
        font-size: .75rem;
        padding-top: .8em;
    }

    .select2-container--default .select2-selection--single {
        display: flex;
        align-items: center;
        justify-content: flex-start !important;
        text-align: left !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        text-align: left !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        right: 10px !important;
    }

    div.dataTables_wrapper div.dataTables_scrollBody::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    div.dataTables_wrapper div.dataTables_scrollBody {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }

    input::placeholder {
        text-align: left;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // ===== Select2 (Server-side) =====
        function setSelect2Value($select, id, text) {
            if (!id) {
                $select.val(null).trigger('change');
                return;
            }
            const opt = new Option(text ?? id, id, true, true);
            $select.append(opt).trigger('change');
        }

        function initCustomerSelect2($el, parentModal) {
            $el.select2({
                dropdownParent: parentModal,
                width: '100%',
                placeholder: 'Select Customer',
                ajax: {
                    url: '{{ route("partGroups.select2.customers") }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: params => ({
                        q: params.term || '',
                        page: params.page || 1
                    }),
                    processResults: data => ({
                        results: data.results || [],
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    })
                },
                templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.id) + '</span>'),
                templateSelection: it => it.text || it.id || ''
            });
        }

        function initModelSelect2($el, parentModal, customerSelector) {
            $el.select2({
                dropdownParent: parentModal,
                width: '100%',
                placeholder: 'Select Model',
                ajax: {
                    url: '{{ route("partGroups.select2.models") }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: params => ({
                        q: params.term || '',
                        page: params.page || 1,
                        customer_id: $(customerSelector).val()
                    }),
                    processResults: data => ({
                        results: data.results || [],
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    })
                },
                templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.id) + '</span>'),
                templateSelection: it => it.text || it.id || ''
            });
        }

        // INIT add modal
        initCustomerSelect2($('#customer_id'), $('#addPartGroupModal'));
        initModelSelect2($('#model_id'), $('#addPartGroupModal'), '#customer_id');
        $('#customer_id').on('change', function() {
            $('#model_id').val(null).trigger('change');
            $('#model_id').prop('disabled', !$(this).val());
        }).trigger('change');

        // INIT edit modal
        initCustomerSelect2($('#edit_customer_id'), $('#editPartGroupModal'));
        initModelSelect2($('#edit_model_id'), $('#editPartGroupModal'), '#edit_customer_id');
        $('#edit_customer_id').on('change', function() {
            $('#edit_model_id').val(null).trigger('change');
            $('#edit_model_id').val(null).trigger('change');
            $('#edit_model_id').prop('disabled', !$(this).val());
        });

        // ===== Part Group Master Logic =====
        function initCodeSelect2($el, parentModal) {
             $el.select2({
                dropdownParent: parentModal,
                width: '100%',
                placeholder: 'Select Code',
                ajax: {
                    url: '{{ route("partGroups.master.select2") }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: params => ({ q: params.term || '', page: params.page || 1 }),
                    processResults: data => ({ results: data.results })
                },
                templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.id) + '</span>'),
                templateSelection: it => it.text || it.id || ''
            });
        }

        // Init Code Selects
        initCodeSelect2($('#code_part_group'), $('#addPartGroupModal'));
        initCodeSelect2($('#edit_code_part_group'), $('#editPartGroupModal'));

        // ===== Manage Master (Button & Modal & Table) =====
        const manageModal = $('#manageMasterModal');
        let masterTable;

        $('#manage-master-btn').on('click', function() {
            showModal(manageModal);
            if (!masterTable) {
                masterTable = $('#masterTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    autoWidth: false,
                    ajax: '{{ route("partGroups.master.data") }}',
                    columns: [
                        { data: 'code', name: 'code', className: 'font-medium text-gray-900 dark:text-white' },
                        { data: 'description', name: 'description' },
                        {
                            data: null,
                            className: 'text-center',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center gap-2">
                                        <button class="edit-master text-blue-600 hover:text-blue-900 dark:text-blue-500 dark:hover:text-blue-400" data-id="${row.id}" data-code="${row.code}" data-desc="${row.description || ''}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete-master text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" data-id="${row.id}" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    pageLength: 5,
                    lengthMenu: [5, 10, 25],
                    language: {
                        emptyTable: '<div class="text-gray-500 dark:text-gray-400 py-4">No codes found in library.</div>',
                        processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                    },
                    drawCallback: function() {
                    }
                });
            } else {
                masterTable.ajax.reload();
            }
            resetMasterForm();
        });

        $('.close-manage-modal-button').on('click', function() {
            hideModal(manageModal);
        });

        // Master Form Logic (Add/Edit)
        function resetMasterForm() {
            $('#masterForm')[0].reset();
            $('#master_id_input').val('');
            $('#save-master-btn').text('Add');
            $('#cancel-master-btn').addClass('hidden');
        }

        $('#cancel-master-btn').on('click', resetMasterForm);

        $(document).on('click', '.edit-master', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            const desc = $(this).data('desc');

            $('#master_id_input').val(id);
            $('#master_code_input').val(code);
            $('#master_desc_input').val(desc);
            $('#save-master-btn').text('Update');
            $('#cancel-master-btn').removeClass('hidden');
        });

        $('#masterForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#master_id_input').val();
            const isEdit = !!id;
            const url = isEdit ? `/partGroups/master/${id}` : `{{ route('partGroups.master.store') }}`;
            const method = isEdit ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: {
                    code: $('#master_code_input').val(),
                    description: $('#master_desc_input').val()
                },
                success: (data) => {
                    if (data.success) {
                        toastSuccess('Success', data.message);
                        resetMasterForm();
                        masterTable.ajax.reload();
                    } else {
                        toastError('Error', data.message);
                    }
                },
                error: (xhr) => toastError('Error', xhr.responseJSON?.message || 'Failed to save code.')
            });
        });

        $(document).on('click', '.delete-master', function() {
            const id = $(this).data('id');
            const $btn = $(this);
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/partGroups/master/${id}`,
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        beforeSend: () => $btn.prop('disabled', true),
                        success: (data) => {
                            if (data.success) {
                                toastSuccess('Success', data.message);
                                masterTable.ajax.reload();
                            } else {
                                toastError('Error', data.message);
                            }
                        },
                        error: (xhr) => toastError('Error', 'Failed to delete.'),
                        complete: () => $btn.prop('disabled', false)
                    });
                }
            });
        });


        // ===== DataTable =====
        const table = $('#partGroupsTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{{ route("partGroups.data") }}',
                type: 'GET',
                data: d => {
                    d.search = d.search.value;
                }
            },
            columns: [{
                    data: null,
                    render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                },
                {
                    data: 'customer_code',
                    name: 'customer_code'
                },
                {
                    data: 'model_name',
                    name: 'model_name'
                },
                {
                    data: 'model_status',
                    name: 'model_status'
                },
                {
                    data: 'code_part_group',
                    name: 'code_part_group'
                },
                {
                    data: 'planning',
                    name: 'planning'
                },
                {
                    data: 'code_part_group_desc',
                    name: 'code_part_group_desc'
                },
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
                    </button>`
                }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [
                [1, 'asc']
            ],
            language: {
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No part groups found.</div>'
            },
            responsive: true,
            autoWidth: false,
        });

        // ===== Modal helpers =====
        const addModal = $('#addPartGroupModal');
        const editModal = $('#editPartGroupModal');
        const deleteModal = $('#deletePartGroupModal');
        const addButton = $('#add-button');
        let partGroupIdToDelete = null;

        function showModal(m) {
            m.removeClass('hidden').addClass('flex');
        }

        function hideModal(m) {
            m.addClass('hidden').removeClass('flex');
        }

        function setButtonLoading($btn, isLoading, loadingText = 'Processing...') {
            if (!$btn || !$btn.length) return;
            if (isLoading) {
                if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
                $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed')
                    .html(`<span class="inline-flex items-center gap-2">
                        <svg aria-hidden="true" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>${loadingText}
                      </span>`);
            } else {
                const orig = $btn.data('orig-html');
                if (orig) $btn.html(orig);
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            }
        }

        function setFormBusy($form, busy) {
            $form.find('input,select,textarea,button').prop('disabled', busy);
        }

        // ===== Toast helpers (SAMAKAN DENGAN SAMPLE) =====
        function detectTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            return isDark ? {
                bg: 'rgba(30,41,59,.95)',
                fg: '#E5E7EB',
                border: 'rgba(71,85,105,.5)',
                progress: 'rgba(255,255,255,.9)',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444',
                    warning: '#f59e0b',
                    info: '#3b82f6'
                }
            } : {
                bg: 'rgba(255,255,255,.98)',
                fg: '#0f172a',
                border: 'rgba(226,232,240,1)',
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
            didOpen: (el) => {
                el.addEventListener('mouseenter', Swal.stopTimer);
                el.addEventListener('mouseleave', Swal.resumeTimer);
                const t = detectTheme();
                const bar = el.querySelector('.swal2-timer-progress-bar');
                if (bar) bar.style.background = t.progress;
                const popup = el.querySelector('.swal2-popup');
                if (popup) popup.style.borderColor = t.border;
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
                    popup: 'swal2-toast border'
                }
            });
        }

        function toastSuccess(title = 'Success', text = 'Operation success') {
            renderToast({
                icon: 'success',
                title,
                text
            });
        }

        function toastError(title = 'Error', text = 'Something went wrong') {
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
        }

        // ===== Add =====
        addButton.on('click', () => {
            $('#addPartGroupForm')[0].reset();
            $('#customer_id').val(null).trigger('change');
            $('#model_id').val(null).trigger('change').prop('disabled', true);
            showModal(addModal);
        });
        $(document).on('click', '.close-modal-button', function() {
            hideModal($(this).closest('[tabindex="-1"]'));
        });

        $('#addPartGroupForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this),
                $btn = $form.find('[type="submit"]');
            $('#add-customer_id-error,#add-model_id-error,#add-code_part_group-error,#add-planning,#add-code_part_group_desc-error').addClass('hidden');

            const formData = new FormData(this);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: (data) => {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(addModal);
                        $form[0].reset();
                        $('#customer_id').val(null).trigger('change');
                        $('#model_id').val(null).trigger('change').prop('disabled', true);
                        toastSuccess('Success', 'Part group added successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to add part group.');
                    }
                },
                error: (xhr) => {
                    const e = xhr.responseJSON?.errors || {};
                    if (e.customer_id) $('#add-customer_id-error').text(e.customer_id[0]).removeClass('hidden');
                    if (e.model_id) $('#add-model_id-error').text(e.model_id[0]).removeClass('hidden');
                    if (e.code_part_group) $('#add-code_part_group-error').text(e.code_part_group[0]).removeClass('hidden');
                    if (e.planning) $('#add-planning-error').text(e.planning[0]).removeClass('hidden');
                    if (e.code_part_group_desc) $('#add-code_part_group_desc-error').text(e.code_part_group_desc[0]).removeClass('hidden');
                    toastError('Error', xhr.responseJSON?.message || 'Failed to add part group.');
                },
                complete: () => {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // ===== Edit (prefill Select2 dengan label) =====
        $(document).on('click', '.edit-button', function() {
            const id = $(this).data('id');
            $.ajax({
                url: `/master/partGroups/${id}`,
                method: 'GET',
                beforeSend: () => {
                    setButtonLoading($(`.edit-button[data-id="${id}"]`), true, '');
                },
                success: (data) => {
                    setSelect2Value($('#edit_code_part_group'), data.code_part_group, data.code_part_group);
                    $('#edit_planning').val(data.planning);
                    $('#edit_code_part_group_desc').val(data.code_part_group_desc);
                    $('#editPartGroupForm').attr('action', `/master/partGroups/${id}`);

                    setSelect2Value($('#edit_customer_id'), data.customer_id, data.customer_label);
                    $('#edit_model_id').prop('disabled', !data.customer_id);
                    setSelect2Value($('#edit_model_id'), data.model_id, data.model_label);

                    showModal(editModal);
                },
                error: (xhr) => toastError('Error', xhr.responseJSON?.message || 'Failed to fetch part group data.'),
                complete: () => {
                    setButtonLoading($(`.edit-button[data-id="${id}"]`), false);
                }
            });
        });

        $('#editPartGroupForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this),
                $btn = $form.find('[type="submit"]');
            $('#edit-customer_id-error,#edit-model_id-error,#edit-code_part_group-error,#edit-planning-error,#edit-code_part_group_desc-error').addClass('hidden');

            const formData = new FormData(this);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: (data) => {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(editModal);
                        toastSuccess('Success', 'Part group updated successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to update part group.');
                    }
                },
                error: (xhr) => {
                    const e = xhr.responseJSON?.errors || {};
                    if (e.customer_id) $('#edit-customer_id-error').text(e.customer_id[0]).removeClass('hidden');
                    if (e.model_id) $('#edit-model_id-error').text(e.model_id[0]).removeClass('hidden');
                    if (e.code_part_group) $('#edit-code_part_group-error').text(e.code_part_group[0]).removeClass('hidden');
                    if (e.planning) $('#edit-planning-error').text(e.planning[0]).removeClass('hidden');
                    if (e.code_part_group_desc) $('#edit-code_part_group_desc-error').text(e.code_part_group_desc[0]).removeClass('hidden');
                    toastError('Error', xhr.responseJSON?.message || 'Failed to update part group.');
                },
                complete: () => {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // ===== Delete =====
        $(document).on('click', '.delete-button', function() {
            partGroupIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });

        $('#confirmDeleteButton').on('click', function() {
            if (!partGroupIdToDelete) return;
            const $btn = $(this);
            $.ajax({
                url: `/master/partGroups/${partGroupIdToDelete}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                beforeSend: () => {
                    setButtonLoading($btn, true, 'Deleting...');
                    setFormBusy($('#deletePartGroupModal'), true);
                },
                success: (data) => {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        partGroupIdToDelete = null;
                        toastSuccess('Success', 'Part group deleted successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to delete part group.');
                    }
                },
                error: (xhr) => toastError('Error', xhr.responseJSON?.message || 'Failed to delete part group.'),
                complete: () => {
                    setButtonLoading($btn, false);
                    setFormBusy($('#deletePartGroupModal'), false);
                }
            });
        });

        // Minor UX focus fix
        const overrideFocusStyles = function() {
            $(this).css({
                'outline': 'none',
                'box-shadow': 'none',
                'border-color': 'gray'
            });
        };
        const restoreBlurStyles = function() {
            $(this).css('border-color', '');
        };
        const elementsToFix = $('.dataTables_filter input, .dataTables_length select');
        elementsToFix.on('focus keyup', overrideFocusStyles);
        elementsToFix.on('blur', restoreBlurStyles);
        elementsToFix.filter(':focus').each(overrideFocusStyles);
    });
</script>
@endpush