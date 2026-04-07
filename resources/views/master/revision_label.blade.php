@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Customer Revision Label')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Customer Revision Label</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola label revisi per customer.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="revLabelTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 w-16">No</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Label</th>
                        <th class="px-6 py-3">Sort</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ADD MODAL --}}
<div id="addRevLabelModal" tabindex="-1" aria-hidden="true"
     class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50">
  <div class="relative p-4 w-full max-w-md">
    <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
      <button type="button"
        class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
        <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close</span>
      </button>
      <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add Revision Label</h3>

      <form id="addRevLabelForm" action="{{ route('rev-label.store') }}" method="POST">
        @csrf
        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer (Code) <span class="text-red-600">*</span></label>
          <select name="customer_id" id="customer_id"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <option value="">Select Customer</option>
          </select>
          <p id="add-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
        </div>

        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Label <span class="text-red-600">*</span></label>
          <input type="text" name="label" id="label" maxlength="30"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
          <p id="add-label-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
        </div>

        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Sort</label>
          <input type="number" name="sort_order" id="sort_order"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          <p id="add-sort_order-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
        </div>

        <div class="flex items-center mb-5">
          <input id="is_active" name="is_active" type="checkbox" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
          <label for="is_active" class="ml-2 text-sm text-gray-900 dark:text-gray-300">Active</label>
        </div>

        <div class="flex items-center space-x-4 mt-6">
          <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
          <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editRevLabelModal" tabindex="-1" aria-hidden="true"
     class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50">
  <div class="relative p-4 w-full max-w-md">
    <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
      <button type="button"
        class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
        <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close</span>
      </button>
      <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Revision Label</h3>

      <form id="editRevLabelForm" method="POST">
        @csrf @method('PUT')

        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Customer (Code) <span class="text-red-600">*</span></label>
          <select name="customer_id" id="edit_customer_id"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <option value="">Select Customer</option>
          </select>
          <p id="edit-customer_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
        </div>

        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Label <span class="text-red-600">*</span></label>
          <input type="text" name="label" id="edit_label" maxlength="30"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
          <p id="edit-label-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
        </div>

        <div class="mb-4">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Sort</label>
          <input type="number" name="sort_order" id="edit_sort_order"
                 class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          <p id="edit-sort_order-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
        </div>

        <div class="flex items-center mb-5">
          <input id="edit_is_active" name="is_active" type="checkbox" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
          <label for="edit_is_active" class="ml-2 text-sm text-gray-900 dark:text-gray-300">Active</label>
        </div>

        <div class="flex items-center space-x-4 mt-6">
          <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">Cancel</button>
          <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- DELETE MODAL --}}
<div id="deleteRevLabelModal" tabindex="-1" aria-hidden="true"
     class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50">
  <div class="relative p-4 w-full max-w-md">
    <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
      <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
        <i class="fa-solid fa-xmark w-5 h-5"></i><span class="sr-only">Close</span>
      </button>
      <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
        <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
      </div>
      <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this revision label?</p>
      <div class="flex justify-center items-center space-x-4">
        <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none">
          No, cancel
        </button>
        <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none">
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

@push('scripts')
<script>
$(function () {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  // ========= Toast seperti sample Supplier =========
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
  function renderToast({icon='success', title='Success', text='' }={}) {
    const t = detectTheme();
    BaseToast.fire({
      icon, title, text, iconColor: t.icon[icon] || t.icon.success,
      background: t.bg, color: t.fg, customClass:{ popup:'swal2-toast border' }
    });
  }
  function toastSuccess(t='Success', m='Operation success'){ renderToast({icon:'success', title:t, text:m}); }
  function toastError(t='Error', m='Something went wrong'){ BaseToast.update({timer:3400}); renderToast({icon:'error', title:t, text:m}); BaseToast.update({timer:2600}); }

  // ========= Busy helpers =========
  const spinnerSVG = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>`;
  function setButtonLoading($btn, isLoading, text='Processing...'){
    if(!$btn?.length) return;
    if(isLoading){
      if(!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
      $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed')
          .html(`<span class="inline-flex items-center gap-2">${spinnerSVG}${text}</span>`);
    }else{
      const o = $btn.data('orig-html'); if(o) $btn.html(o);
      $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
    }
  }
  function setFormBusy($form, busy){ $form.find('input,select,textarea,button').prop('disabled', busy); }

  // ========= Select2 (preload + searchable) =========
  function initCustomerSelect2($el, $parentModal){
    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
    $el.select2({
      dropdownParent: $parentModal,
      width: '100%',
      placeholder: 'Select Customer',
      minimumInputLength: 0,
      ajax: {
        url: `{{ route('rev-label.dropdowns') }}`,
        method: 'POST',
        dataType: 'json',
        delay: 250,
        data: params => ({ _token: csrfToken, q: params.term || '', page: params.page || 1, per_page: 20 }),
        processResults: (data, params) => {
          params.page = params.page || 1;
          return { results: data.results || [], pagination: { more: (params.page * 20) < (data.total_count || 0) } };
        },
        cache: true
      }
    });
    // Preload halaman 1 agar langsung tampil
    $.post(`{{ route('rev-label.dropdowns') }}`, { _token: csrfToken, q:'', page:1, per_page:20 })
      .done(res => {
        (res.results || []).forEach(o => {
          if (!$el.find(`option[value="${o.id}"]`).length) $el.append(new Option(o.text, o.id, false, false));
        });
      });
  }
  function setSelect2Value($el, id, text){
    if(!id){ $el.val(null).trigger('change'); return; }
    const opt = new Option(text ?? id, id, true, true);
    $el.append(opt).trigger('change');
  }

  // ========= DataTable =========
  const table = $('#revLabelTable').DataTable({
    processing:true, serverSide:true,
    ajax:{ url:'{{ route("rev-label.data") }}', type:'GET', data: d => { d.search = d.search.value; } },
    columns:[
      { data:null, render:(d,t,r,m)=> m.row + m.settings._iDisplayStart + 1 },
      { data:'customer_code' },
      { data:'label' },
      { data:'sort_order' },
      { data:'is_active', render:v=> v
        ? '<span class="inline-block px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>'
        : '<span class="inline-block px-3 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Inactive</span>' },
      { data:'id', orderable:false, searchable:false, className:'text-center',
        render:id=>`
          <button class="edit-button text-gray-400 hover:text-gray-700" title="Edit" data-id="${id}">
            <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
          </button>
          <button class="delete-button text-red-600 hover:text-red-900" title="Delete" data-id="${id}">
            <i class="fa-solid fa-trash-can fa-lg m-2"></i>
          </button>` }
    ],
    pageLength:10, lengthMenu:[10,25,50], order:[[3,'asc']],
    language:{ emptyTable:'<div class="text-gray-500 dark:text-gray-400">No data found.</div>' }
  });

  // ========= Modals helpers =========
  const addModal    = $('#addRevLabelModal');
  const editModal   = $('#editRevLabelModal');
  const deleteModal = $('#deleteRevLabelModal');
  const addButton   = $('#add-button');
  let rowIdToDelete = null;

  const showModal = m => m.removeClass('hidden').addClass('flex');
  const hideModal = m => m.addClass('hidden').removeClass('flex');

  // Open Add
  addButton.on('click', ()=>{
    $('#addRevLabelForm')[0].reset();
    $('#add-customer_id-error,#add-label-error,#add-sort_order-error').addClass('hidden').text('');
    initCustomerSelect2($('#customer_id'), addModal);
    $('#is_active').prop('checked', true);
    showModal(addModal);
  });
  // Close buttons
  $(document).on('click','.close-modal-button', function(){ hideModal($(this).closest('[id$="Modal"]')); });

  // ========= Create =========
  $('#addRevLabelForm').on('submit', function(e){
    e.preventDefault();
    const $form=$(this), $btn=$form.find('[type="submit"]');
    $('#add-customer_id-error,#add-label-error,#add-sort_order-error').addClass('hidden').text('');
    const fd = new FormData(this);
    fd.set('is_active', $('#is_active').is(':checked') ? '1' : '0');

    $.ajax({
      url:$form.attr('action'), method:'POST', headers:{'X-CSRF-TOKEN': csrfToken},
      data:fd, processData:false, contentType:false,
      beforeSend(){ setButtonLoading($btn,true,'Saving...'); setFormBusy($form,true); },
      success(res){
        if(res.success){
          table.ajax.reload(null,false);
          hideModal(addModal); $form[0].reset(); toastSuccess('Success','Revision label added.');
        }else{
          toastError('Error', res.message || 'Failed to add revision label.');
        }
      },
      error(xhr){
        const e=xhr.responseJSON?.errors||{};
        if(e.customer_id) $('#add-customer_id-error').text(e.customer_id[0]).removeClass('hidden');
        if(e.label)       $('#add-label-error').text(e.label[0]).removeClass('hidden');
        if(e.sort_order)  $('#add-sort_order-error').text(e.sort_order[0]).removeClass('hidden');
        toastError('Validation error','Please check your input.');
      },
      complete(){ setButtonLoading($btn,false); setFormBusy($form,false); }
    });
  });

  // ========= Open Edit =========
  $(document).on('click','.edit-button', function(){
    const id=$(this).data('id');
    $('#edit-customer_id-error,#edit-label-error,#edit-sort_order-error').addClass('hidden').text('');
    $.get(`{{ route('rev-label.show', ':id') }}`.replace(':id', id))
      .done(row=>{
        $('#editRevLabelForm').attr('action', `{{ route('rev-label.update', ':id') }}`.replace(':id', id));
        initCustomerSelect2($('#edit_customer_id'), editModal);
        setSelect2Value($('#edit_customer_id'), row.customer_id, row.customer_code || row.customer_id);
        $('#edit_label').val(row.label || '');
        $('#edit_sort_order').val(row.sort_order ?? '');
        $('#edit_is_active').prop('checked', !!row.is_active);
        showModal(editModal);
      })
      .fail(()=> toastError('Error','Failed to load data.'));
  });

  // ========= Update =========
  $('#editRevLabelForm').on('submit', function(e){
    e.preventDefault();
    const $form=$(this), $btn=$form.find('[type="submit"]');
    $('#edit-customer_id-error,#edit-label-error,#edit-sort_order-error').addClass('hidden').text('');
    const fd = new FormData(this); fd.set('_method','PUT'); fd.set('is_active',$('#edit_is_active').is(':checked')?'1':'0');

    $.ajax({
      url:$form.attr('action'), method:'POST', headers:{'X-CSRF-TOKEN': csrfToken},
      data:fd, processData:false, contentType:false,
      beforeSend(){ setButtonLoading($btn,true,'Saving...'); setFormBusy($form,true); },
      success(res){
        if(res.success){
          table.ajax.reload(null,false);
          hideModal(editModal);
          toastSuccess('Success','Revision label updated.');
        }else{
          toastError('Error', res.message || 'Failed to update.');
        }
      },
      error(xhr){
        const e=xhr.responseJSON?.errors||{};
        if(e.customer_id) $('#edit-customer_id-error').text(e.customer_id[0]).removeClass('hidden');
        if(e.label)       $('#edit-label-error').text(e.label[0]).removeClass('hidden');
        if(e.sort_order)  $('#edit-sort_order-error').text(e.sort_order[0]).removeClass('hidden');
        toastError('Validation error','Please check your input.');
      },
      complete(){ setButtonLoading($btn,false); setFormBusy($form,false); }
    });
  });

  // ========= Delete =========
  $(document).on('click','.delete-button', function(){
    rowIdToDelete = $(this).data('id'); showModal(deleteModal);
  });
  $('#confirmDeleteButton').on('click', function(){
    if(!rowIdToDelete) return;
    const $btn=$(this);
    $.ajax({
      url:`{{ route('rev-label.destroy', ':id') }}`.replace(':id', rowIdToDelete),
      method:'POST', headers:{'X-CSRF-TOKEN': csrfToken}, data:{ _method:'DELETE' },
      beforeSend(){ setButtonLoading($btn,true,'Deleting...'); setFormBusy($('#deleteRevLabelModal'),true); },
      success(res){
        if(res.success){
          table.ajax.reload(null,false);
          hideModal(deleteModal); rowIdToDelete=null;
          toastSuccess('Success','Revision label deleted.');
        }else{
          toastError('Error', res.message || 'Failed to delete.');
        }
      },
      error(){ toastError('Error','Failed to delete.'); },
      complete(){ setButtonLoading($btn,false); setFormBusy($('#deleteRevLabelModal'),false); }
    });
  });

  // Perbaiki fokus input/select DataTables
  const overrideFocus=function(){ $(this).css({'outline':'none','box-shadow':'none','border-color':'gray'}); };
  const restoreFocus=function(){ $(this).css('border-color',''); };
  $('.dataTables_filter input, .dataTables_length select').on('focus keyup',overrideFocus).on('blur',restoreFocus);
});
</script>
@endpush
