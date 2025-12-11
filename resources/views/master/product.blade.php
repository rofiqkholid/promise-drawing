@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Product Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Product Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="productsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="customer_code">Customer</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="model_name">Model</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="status">Status</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="part_no">Part No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="part_name">Part Name</th>
                        <th scope="col" class="px-6 py-3 text-center">Group</th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add --}}
<div id="addProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add New Product</h3>

            <form id="addProductForm" action="{{ route('products.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer <span class="text-red-600">*</span></label>
                    <select name="customer_id" id="customer_id" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Customer</option>
                    </select>
                    <p id="add-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>

                <div class="mb-4">
                    <label for="model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model <span class="text-red-600">*</span></label>
                    <select name="model_id" id="model_id" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                        <option value="">Select Model</option>
                    </select>
                    <p id="add-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>

                <div class="mb-4">
                    <label for="part_no" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part No <span class="text-red-600">*</span></label>
                    <input type="text" name="part_no" id="part_no" maxlength="20" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 123-ABC-45" required>
                    <p id="add-part_no-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>

                <div class="mb-4">
                    <label for="part_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Name <span class="text-red-600">*</span></label>
                    <input type="text" name="part_name" id="part_name" maxlength="50" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. Bracket Assembly" required>
                    <p id="add-part_name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="add_has_pair" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Link to Partner Product</span>
                    </label>
                </div>

                <div class="mb-4 hidden" id="add_partner_container">
                    <label for="add_partner_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Select Partner</label>
                    <select name="partner_id" id="add_partner_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Search Product...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Search by Part No or Name (must be same Customer/Model generally)</p>
                </div>

                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit --}}
<div id="editProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close</span>
            </button>
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Product</h3>

            <form id="editProductForm" method="POST">
                @csrf @method('PUT')

                <div class="grid gap-6 mb-6 md:grid-cols-2">
                    {{-- Left Column: Product Details --}}
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Product Details</h4>
                        
                        <div class="mb-4">
                            <label for="edit_customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer <span class="text-red-600">*</span></label>
                            <select name="customer_id" id="edit_customer_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select Customer</option>
                            </select>
                            <p id="edit-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_model_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Model <span class="text-red-600">*</span></label>
                            <select name="model_id" id="edit_model_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                                <option value="">Select Model</option>
                            </select>
                            <p id="edit-model_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_part_no" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part No <span class="text-red-600">*</span></label>
                            <input type="text" name="part_no" id="edit_part_no" maxlength="20" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                            <p id="edit-part_no-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                        </div>

                        <div class="mb-4">
                            <label for="edit_part_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Part Name <span class="text-red-600">*</span></label>
                            <input type="text" name="part_name" id="edit_part_name" maxlength="50" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                            <p id="edit-part_name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                        </div>
                    </div>

                    {{-- Right Column: Pairing Info --}}
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Pairing Configuration</h4>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 h-auto">
                            <div id="edit_current_partner_info" class="mb-6 hidden">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Connection:</p>
                                <div class="bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600 shadow-sm">
                                    <div class="flex items-start justify-between">
                                        <div class="flex flex-wrap items-center" id="edit_partner_label_display">
                                            {{-- Badges will be injected here --}}
                                        </div>
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <label class="inline-flex items-center cursor-pointer select-none">
                                            <input type="checkbox" name="unlink_pair" value="1" class="sr-only peer">
                                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                            <span class="ml-2 text-xs font-medium text-gray-600 dark:text-gray-400 peer-checked:text-red-600 dark:peer-checked:text-red-400">Unlink Product</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
        
                            <div id="edit_new_partner_section">
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Link to Partner</label>
                                <select name="partner_id" id="edit_partner_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Search Product...</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Select an existing product to pair with.</p>
                                
                                <div class="relative flex py-4 items-center">
                                    <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
                                    <span class="flex-shrink-0 mx-4 text-gray-400 text-xs uppercase">Or</span>
                                    <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
                                </div>
                                
                                <div>
                                    <label class="inline-flex items-center cursor-pointer mb-3">
                                        <input type="checkbox" name="create_new_partner" id="edit_create_new_partner" value="1" class="sr-only peer">
                                        <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Create New Partner</span>
                                    </label>
                                    
                                    <div id="edit_create_partner_fields" class="hidden space-y-3 pt-2">
                                         <div>
                                            <input type="text" name="new_partner_part_no" id="new_partner_part_no" maxlength="20" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" placeholder="Part No">
                                            <p id="edit-new_partner_part_no-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                                        </div>
                                        <div>
                                            <input type="text" name="new_partner_part_name" id="new_partner_part_name" maxlength="50" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" placeholder="Part Name">
                                            <p id="edit-new_partner_part_name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete --}}
<div id="deleteProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this product?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">Yes, I'm sure</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // === SweetAlert2 Toast helpers ===
        function detectTheme() {
            const dark = document.documentElement.classList.contains('dark');
            return dark ?
                {
                    bg: 'rgba(30,41,59,.95)',
                    fg: '#E5E7EB',
                    icon: {
                        success: '#22c55e',
                        error: '#ef4444',
                        warning: '#f59e0b',
                        info: '#3b82f6'
                    }
                } :
                {
                    bg: 'rgba(255,255,255,.98)',
                    fg: '#0f172a',
                    icon: {
                        success: '#16a34a',
                        error: '#dc2626',
                        warning: '#d97706',
                        info: '#2563eb'
                    }
                };
        }
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2600,
            timerProgressBar: true,
            didOpen: (el) => {
                el.addEventListener('mouseenter', Swal.stopTimer);
                el.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        function toast(icon = 'success', title = 'Success', text = '') {
            const t = detectTheme();
            Toast.fire({
                icon,
                title,
                text,
                background: t.bg,
                color: t.fg,
                iconColor: t.icon[icon] || t.icon.success
            });
        }

        // Busy helpers
        const spinnerSVG = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>`;

        function beginBusy($btn, text = 'Processing...') {
            if ($btn.data('busy')) return false;
            $btn.data('busy', true);
            if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
            $btn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed').html(`<span class="inline-flex items-center">${spinnerSVG}${text}</span>`);
            return true;
        }

        function endBusy($btn) {
            const o = $btn.data('orig-html');
            if (o) $btn.html(o);
            $btn.prop('disabled', false).removeClass('opacity-75 cursor-not-allowed');
            $btn.data('busy', false);
        }

        // ===== Select2 helpers (server-side) =====
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
                minimumInputLength: 0, // tampil saat dibuka
                ajax: {
                    url: '{{ route("products.getCustomers") }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: params => ({
                        q: params.term || '',
                        page: params.page || 1
                    }),
                    processResults: data => ({
                        results: data.results || data,
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    })
                },
                templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.code || it.id) + '</span>'),
                templateSelection: it => it.text || it.code || it.id || ''
            });
        }

        function initModelSelect2($el, parentModal, customerSelector) {
            $el.select2({
                dropdownParent: parentModal,
                width: '100%',
                placeholder: 'Select Model',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route("products.getModels") }}',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: params => ({
                        q: params.term || '',
                        page: params.page || 1,
                        customer_id: $(customerSelector).val()
                    }),
                    processResults: data => ({
                        results: data.results || data,
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    })
                },
                templateResult: it => it.loading ? it.text : $('<span class="text-sm">' + (it.text || it.name || it.id) + '</span>'),
                templateSelection: it => it.text || it.name || it.id || ''
            });
        }

        // Add modal Select2
        initCustomerSelect2($('#customer_id'), $('#addProductModal'));
        initModelSelect2($('#model_id'), $('#addProductModal'), '#customer_id');
        $('#customer_id').on('change', function() {
            $('#model_id').val(null).trigger('change');
            $('#model_id').prop('disabled', !$(this).val());
        }).trigger('change');

        // Edit modal Select2
        initCustomerSelect2($('#edit_customer_id'), $('#editProductModal'));
        initModelSelect2($('#edit_model_id'), $('#editProductModal'), '#edit_customer_id');
        $('#edit_customer_id').off('change').on('change', function() {
            $('#edit_model_id').val(null).trigger('change');
            $('#edit_model_id').prop('disabled', !$(this).val());
        });

        // Partner Select2 Helpers
        function initPartnerSelect2($el, parentModal, customerSelector, excludeId = null) {
            $el.select2({
                dropdownParent: parentModal,
                width: '100%',
                placeholder: 'Search Product...',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route("products.getPairable") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term,
                        customer_id: $(customerSelector).val(),
                        exclude_id: excludeId
                    }),
                    processResults: data => ({
                        results: data.results
                    })
                }
            });
        }

        // Add Modal Partner Logic
        initPartnerSelect2($('#add_partner_id'), $('#addProductModal'), '#customer_id');
        
        $('#add_has_pair').on('change', function() {
            if ($(this).is(':checked')) {
                $('#add_partner_container').removeClass('hidden');
            } else {
                $('#add_partner_container').addClass('hidden');
                $('#add_partner_id').val(null).trigger('change');
            }
        });

        // Edit Modal Partner Logic
        $('#edit_create_new_partner').on('change', function() {
            if ($(this).is(':checked')) {
                // Disable the select2
                $('#edit_partner_id').val(null).trigger('change').prop('disabled', true);
                
                // Show the new inputs
                $('#edit_create_partner_fields').removeClass('hidden');
            } else {
                // Re-enable select2
                $('#edit_partner_id').prop('disabled', false);
                
                // Hide new inputs and clear them
                $('#edit_create_partner_fields').addClass('hidden');
                $('#new_partner_part_no').val('');
                $('#new_partner_part_name').val('');
            }
        });


        // ===== DataTable =====
        const table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("products.data") }}',
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
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'part_no',
                    name: 'part_no'
                },
                {
                    data: 'part_name',
                    name: 'part_name'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: (d) => {
                        if (d.group_id) {
                            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        <i class="fa-solid fa-link mr-1"></i> Paired
                                    </span>`;
                        } else {
                             return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Single
                                    </span>`;
                        }
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: row => `
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
                [0, 'desc']
            ],
            language: {
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No products found.</div>'
            }
        });

        // ===== Modals =====
        const addModal = $('#addProductModal');
        const editModal = $('#editProductModal');
        const deleteModal = $('#deleteProductModal');
        const addButton = $('#add-button');
        let productIdToDelete = null;

        function showModal(m) {
            m.removeClass('hidden').addClass('flex');
        }

        function hideModal(m) {
            m.addClass('hidden').removeClass('flex');
        }

        addButton.on('click', function() {
            const $b = $(this);
            if (!beginBusy($b, 'Opening...')) return;
            $('#addProductForm')[0].reset();
            $('#customer_id').val(null).trigger('change');
            $('#model_id').val(null).trigger('change').prop('disabled', true);
            showModal(addModal);
            setTimeout(() => endBusy($b), 150);
        });

        $(document).on('click', '.close-modal-button', function() {
            hideModal($(this).closest('[tabindex="-1"]'));
        });

        // Create
        $('#addProductForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('[type=submit]');
            if (!beginBusy($btn, 'Saving...')) return;
            $('#add-model_id-error,#add-part_no-error,#add-part_name-error,#add-customer_id-error').addClass('hidden').text('');

            const formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                success: (res) => {
                    if (res.success) {
                        table.ajax.reload(null, false);
                        hideModal(addModal);
                        this.reset();
                        $('#customer_id').val(null).trigger('change');
                        $('#model_id').val(null).trigger('change').prop('disabled', true);
                        
                        // Reset Partner fields
                        $('#add_has_pair').prop('checked', false).trigger('change');
                        $('#add_partner_id').val(null).trigger('change');

                        toast('success', 'Success', 'Product added');
                    } else {
                        toast('error', 'Failed', res.message || 'Failed to create');
                    }
                },
                error: (xhr) => {
                    const e = xhr.responseJSON?.errors || {};
                    if (e.customer_id) $('#add-customer_id-error').text(e.customer_id[0]).removeClass('hidden');
                    if (e.model_id) $('#add-model_id-error').text(e.model_id[0]).removeClass('hidden');
                    if (e.part_no) $('#add-part_no-error').text(e.part_no[0]).removeClass('hidden');
                    if (e.part_name) $('#add-part_name-error').text(e.part_name[0]).removeClass('hidden');
                    toast('error', 'Error', xhr.responseJSON?.message || 'Failed to create');
                },
                complete: () => endBusy($btn)
            });
        });

        // Open Edit
        $(document).on('click', '.edit-button', function() {
            const $b = $(this);
            if (!beginBusy($b, '')) return;
            const id = $b.data('id');

            $('#edit-model_id-error,#edit-part_no-error,#edit-part_name-error,#edit-customer_id-error').addClass('hidden').text('');

            $.ajax({
                url: `/master/products/${id}`,
                method: 'GET',
                success: (data) => {
                    $('#edit_part_no').val(data.part_no);
                    $('#edit_part_name').val(data.part_name);
                    $('#editProductForm').attr('action', `/master/products/${id}`);

                    setSelect2Value($('#edit_customer_id'), data.customer_id, data.customer_label || '');
                    $('#edit_model_id').prop('disabled', !data.customer_id);
                    setSelect2Value($('#edit_model_id'), data.model_id, data.model_label || '');

                    showModal(editModal);

                    // Setup Partner Select2 with exclude ID
                    // Destroy first to reset with new exclude_id
                    if ($('#edit_partner_id').data('select2')) {
                        $('#edit_partner_id').select2('destroy');
                    }
                    initPartnerSelect2($('#edit_partner_id'), $('#editProductModal'), '#edit_customer_id', id);

                    // Handle display of current partner
                    if (data.group_id && data.partner_label && Array.isArray(data.partner_label)) {
                        $('#edit_current_partner_info').removeClass('hidden');
                        
                        let badgesHtml = '';
                        data.partner_label.forEach(p => {
                            badgesHtml += `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 mr-1 mb-1">
                                <i class="fa-solid fa-cube mr-1"></i> ${p.text}
                            </span>`;
                        });
                        
                        $('#edit_partner_label_display').html(badgesHtml).removeClass('text-sm font-medium'); // clear old text styling and use html
                        
                        // Reset unlink checkbox
                        $('input[name="unlink_pair"]').prop('checked', false);
                    } else if (data.group_id && data.partner_label) {
                         // Fallback for string (legacy)
                         $('#edit_current_partner_info').removeClass('hidden');
                         $('#edit_partner_label_display').text(data.partner_label);
                    } else {
                        $('#edit_current_partner_info').addClass('hidden');
                        $('#edit_partner_label_display').empty();
                    }
                    
                    // Clear new partner selection
                    $('#edit_partner_id').val(null).trigger('change');
                },
                error: (xhr) => {
                    toast('error', 'Error', xhr.responseJSON?.message || 'Failed to load product');
                },
                complete: () => endBusy($b)
            });
        });

        // Update
        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('[type=submit]');
            if (!beginBusy($btn, 'Updating...')) return;
            $('#edit-model_id-error,#edit-part_no-error,#edit-part_name-error,#edit-customer_id-error').addClass('hidden').text('');

            const formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                success: (res) => {
                    if (res.success) {
                        table.ajax.reload(null, false);
                        hideModal(editModal);
                        toast('success', 'Success', 'Product updated');
                    } else {
                        toast('error', 'Failed', res.message || 'Failed to update');
                    }
                },
                error: (xhr) => {
                    const e = xhr.responseJSON?.errors || {};
                    if (e.customer_id) $('#edit-customer_id-error').text(e.customer_id[0]).removeClass('hidden');
                    if (e.model_id) $('#edit-model_id-error').text(e.model_id[0]).removeClass('hidden');
                    if (e.part_no) $('#edit-part_no-error').text(e.part_no[0]).removeClass('hidden');
                    if (e.part_name) $('#edit-part_name-error').text(e.part_name[0]).removeClass('hidden');
                    toast('error', 'Error', xhr.responseJSON?.message || 'Failed to update');
                },
                complete: () => endBusy($btn)
            });
        });

        // Delete
        $(document).on('click', '.delete-button', function() {
            productIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });

        $('#confirmDeleteButton').on('click', function() {
            if (!productIdToDelete) return;
            const $b = $(this);
            if (!beginBusy($b, 'Deleting...')) return;
            $.ajax({
                url: `/master/products/${productIdToDelete}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: (res) => {
                    if (res.success) {
                        table.ajax.reload(null, false);
                        hideModal(deleteModal);
                        productIdToDelete = null;
                        toast('success', 'Success', 'Product deleted');
                    } else {
                        toast('error', 'Failed', res.message || 'Failed to delete');
                    }
                },
                error: (xhr) => {
                    toast('error', 'Error', xhr.responseJSON?.message || 'Error deleting product');
                },
                complete: () => endBusy($b)
            });
        });

        // Focus UX for DT
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
        elementsToFix.on('focus keyup', overrideFocusStyles).on('blur', restoreBlurStyles).filter(':focus').each(overrideFocusStyles);
    });
</script>
@endpush