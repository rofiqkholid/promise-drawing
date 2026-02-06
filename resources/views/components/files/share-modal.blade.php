@once
<style>
    /* Clean Select2 Input for Share Modal */
    .ms-style-select2 .select2-container--default .select2-selection--multiple {
        border: 1px solid #e5e7eb;
        background-color: #f9fafb !important;
        border-radius: 0.375rem;
        min-height: 40px; /* Adjusted height */
        padding: 4px 10px; /* Adjusted padding */
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }
    .dark .ms-style-select2 .select2-container--default .select2-selection--multiple {
        border-color: #374151;
        background-color: #1f2937 !important;
    }
    .ms-style-select2 .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #6366f1;
        box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.1);
    }
    .ms-style-select2 .select2-selection__choice {
        display: none !important;
    }
    .ms-style-select2 .select2-search__field {
        font-size: 13px !important;
        margin-top: 0 !important;
        padding: 0 !important;
        height: 24px !important; /* Fixed line-height match */
        line-height: 24px !important;
        color: #374151 !important;
       font-family: inherit !important;
    }
    .dark .ms-style-select2 .select2-search__field {
        color: #e5e7eb !important;
    }
    /* Force 100% width to prevent collapse */
    .ms-style-select2 .select2-container {
        width: 100% !important;
        display: block;
    }
    /* Ensure Select2 Dropdown is above Modal */
    .select2-container--open {
        z-index: 999999 !important;
    }
    .select2-dropdown {
        z-index: 999999 !important;
    }
    [x-cloak] { display: none !important; }
</style>
@endonce

<div x-data="shareModalComponent()"
     x-cloak
     x-show="isOpen"
     @open-share-modal.window="openModal($event.detail)"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60"
     style="display: none;">

    <div class="bg-white dark:bg-gray-800 rounded-md shadow-2xl w-full max-w-sm overflow-hidden border border-gray-200 dark:border-gray-700"
         @click.outside="closeModal()">

        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Share Package</h3>
                <p class="text-[10px] text-gray-500 font-medium">Distribute drawings to suppliers</p>
            </div>
            <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-5">
            {{-- Package Context --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 rounded-md">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="fa-solid fa-file-shield text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate" x-text="packageName"></p>
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest leading-none mt-1" x-text="packageInfo"></p>
                    </div>
                </div>
            </div>

            {{-- Recipient Selection --}}
            <div class="space-y-3">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Add Recipients</label>
                    
                    <!-- Select2 Container -->
                    <div class="ms-style-select2" wire:ignore>
                        <select id="supplierSelectInput" class="w-full h-10 block" multiple="multiple" style="width: 100%"></select>
                    </div>
                </div>
                
                {{-- Selected Recipients List (Alpine Managed) --}}
                <div class="max-h-[150px] overflow-y-auto space-y-1 p-1 no-scrollbar">
                    <template x-if="selectedSuppliers.length === 0">
                        <div class="flex flex-col items-center justify-center py-6 border border-dashed border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-800/50">
                            <i class="fa-solid fa-user-group text-gray-300 mb-2"></i>
                            <p class="text-[11px] text-gray-400 font-medium">No recipients added</p>
                        </div>
                    </template>

                    <template x-for="supplier in selectedSuppliers" :key="supplier.id">
                        <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm group hover:border-blue-300 dark:hover:border-blue-700 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 font-bold text-[10px] border border-gray-100 dark:border-gray-600"
                                     x-text="getInitials(supplier.code)">
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold text-gray-800 dark:text-gray-200 leading-tight truncate max-w-[180px]" x-text="supplier.text"></p>
                                    <p class="text-[9px] text-gray-400 font-mono mt-0.5" x-text="supplier.code"></p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors self-center" 
                                    @click="removeSupplier(supplier.id)">
                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
            <button type="button" @click="closeModal()" class="text-[10px] font-bold text-gray-500 hover:text-gray-700 uppercase tracking-widest transition-colors">
                Cancel
            </button>
            <button type="button" @click="submitShare()" 
                    :disabled="isSending"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-[11px] font-bold rounded-md shadow-sm transition-all uppercase tracking-wide flex items-center gap-2">
                <template x-if="isSending">
                    <i class="fa-solid fa-spinner fa-spin text-[10px]"></i>
                </template>
                <template x-if="!isSending">
                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                </template>
                <span x-text="isSending ? 'Sending...' : 'Notify & Share'"></span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function shareModalComponent() {
        return {
            isOpen: false,
            packageId: null,
            packageName: '',
            packageInfo: '',
            selectedSuppliers: [],
            isSending: false,

            init() {
                this.$watch('isOpen', value => {
                    if (value) {
                        setTimeout(() => {
                            this.initSelect2();
                        }, 100);
                    } else {
                         const $select = $('#supplierSelectInput');
                         if ($select && $select.length > 0 && $select.hasClass("select2-hidden-accessible")) {
                            $select.select2('destroy');
                         }
                    }
                });
            },

            openModal(detail) {
                this.packageId = detail.id;
                this.packageName = detail.name || 'Document Package';
                this.packageInfo = detail.info || 'Details';
                this.selectedSuppliers = [];
                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
                this.resetForm();
            },

            resetForm() {
                this.packageId = null;
                this.packageName = '';
                this.packageInfo = '';
                this.selectedSuppliers = [];
                this.isSending = false;
                
                // Safe Destroy
                const $select = $('#supplierSelectInput');
                if ($select && $select.length > 0 && $select.hasClass("select2-hidden-accessible")) {
                    $select.select2('destroy');
                }
            },

            initSelect2() {
                const $select = $('#supplierSelectInput'); 
                
                // Safety check: Element must exist
                if (!$select.length) return;

                // Destroy if already init
                if ($select.hasClass("select2-hidden-accessible")) {
                   $select.select2('destroy');
                }
                
                $select.empty();

                $select.select2({
                    // dropdownParent removed to attach to body (safer for z-index/Alpine)
                    width: '100%',
                    placeholder: 'Search supplier...',
                    allowClear: true,
                    multiple: true,
                    closeOnSelect: true,
                    ajax: {
                        url: "{{ route('share.getSuppliers') }}",
                        method: 'GET',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term || '', // Standardize on 'search' or 'q' based on backend
                                q: params.term || '', // Send both for compatibility
                                page: params.page || 1
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(item => ({
                                    id: item.id,
                                    text: item.name || item.code, 
                                    code: item.code
                                }))
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (data.loading) return data.text;
                        return $(`
                            <div class="flex items-center justify-between px-2 py-1">
                                <span class="font-medium text-sm text-gray-700 dark:text-gray-200">${data.text}</span>
                                <span class="text-[10px] text-gray-500 font-mono bg-gray-100 dark:bg-gray-600 px-1.5 py-0.5 rounded">${data.code}</span>
                            </div>
                        `);
                    },
                    templateSelection: function(data) {
                        return data.text || data.id;
                    }
                });

                // Re-bind events
                $select.off('select2:select');
                $select.on('select2:select', (e) => {
                    const data = e.params.data;
                    this.addSupplier({
                        id: data.id,
                        text: data.text,
                        code: data.code
                    });
                    $select.val(null).trigger('change');
                });
            },

            addSupplier(supplier) {
                if (!this.selectedSuppliers.find(s => s.id == supplier.id)) {
                    this.selectedSuppliers.push(supplier);
                }
            },

            removeSupplier(id) {
                this.selectedSuppliers = this.selectedSuppliers.filter(s => s.id != id);
            },

            getInitials(code) {
                return (code || '??').substring(0, 2).toUpperCase();
            },

            submitShare() {
                if (!this.packageId) {
                    toastError('Error', 'Package ID not found.');
                    return;
                }
                if (this.selectedSuppliers.length === 0) {
                    toastWarning('Add recipients', 'Please select at least one supplier.');
                    return;
                }

                this.isSending = true;
                const supplierIds = this.selectedSuppliers.map(s => s.id);

                $.ajax({
                    url: '{{ route("share.save") }}',
                    type: 'POST',
                    data: {
                        package_id: this.packageId,
                        supplier_ids: supplierIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: (response) => {
                        this.closeModal();
                        toastSuccess('Sent', response.message || 'Shared successfully!');
                        
                        $(document).trigger('package:shared', [this.packageId, response]);
                        window.dispatchEvent(new CustomEvent('package-shared', { detail: { packageId: this.packageId, response } }));

                        // Safer Legacy Callbacks
                        if (typeof window.loadKpis === 'function') window.loadKpis();
                        if (typeof window.loadHistory === 'function') window.loadHistory();
                        if (typeof window.table !== 'undefined' && window.table.ajax) window.table.ajax.reload(null, false);
                    },
                    error: (xhr) => {
                        console.error('Failed to share:', xhr.responseText);
                        const msg = xhr.responseJSON?.message || 'Failed to share package.';
                        if (xhr.status === 422) toastWarning('Check input', msg);
                        else toastError('Error', msg);
                    },
                    complete: () => {
                        this.isSending = false;
                    }
                });
            }
        };
    }

    // Bridge
    window.openShareModal = function(id, name, info) {
        window.dispatchEvent(new CustomEvent('open-share-modal', {
            detail: { id, name, info }
        }));
    };
</script>
@endpush
