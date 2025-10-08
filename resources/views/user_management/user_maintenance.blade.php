@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'User Maintenance')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">

  {{-- Header --}}
  <div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">User Maintenance</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage application users.</p>
    </div>
    <div class="mt-4 sm:mt-0">
      <button type="button" id="add-button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
        <i class="fa-solid fa-plus"></i>
        Add New
      </button>
    </div>
  </div>

  {{-- SECTION: LIST (landing) --}}
  <section id="listSection" class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
    <div class="p-4 md:p-6">
      <table id="usersTable" class="min-w-full w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th class="px-6 py-3 w-16">No</th>
            <th class="px-6 py-3">Name</th>
            <th class="px-6 py-3">Email</th>
            <th class="px-6 py-3">NIK</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3 text-start">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  {{-- SECTION: FORM (Add/Edit) --}}
  <section id="formSection" class="hidden">
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
      <div class="p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
            <span id="formTitle">Add New User</span>
          </h3>
          <button type="button" id="backToList"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md border hover:bg-gray-50 dark:hover:bg-gray-700">
            <i class="fa-solid fa-arrow-left"></i> Back
          </button>
        </div>

        <form id="userForm" action="{{ route('userMaintenance.store') }}" method="POST">
          @csrf
          <input type="hidden" name="_method" id="methodSpoof" value="POST">

          <div class="flex flex-col md:flex-row md:items-end md:flex-wrap gap-4">
            <div class="flex-1 min-w-[220px]">
              <label for="f_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Full Name</label>
              <input type="text" id="f_name" name="name"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                required>
              <p id="err-name" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>

            <div class="flex-1 min-w-[250px]">
              <label for="f_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
              <input type="email" id="f_email" name="email"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                required>
              <p id="err-email" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>

            <div class="flex-1 min-w-[160px]">
              <label for="f_nik" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NIK</label>
              <input type="text" id="f_nik" name="nik"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                required>
              <p id="err-nik" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>

            <div class="flex-1 min-w-[220px]">
              <label for="f_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                Password <span id="pwdHint" class="text-xs text-gray-400">(required)</span>
              </label>
              <input type="password" id="f_password" name="password" placeholder="••••••••"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <p id="err-password" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>

            <div class="w-full md:w-auto md:min-w-[180px]">
              <label for="f_status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
              <select id="f_status" name="is_active"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>

            <div class="flex gap-3 md:ml-auto md:pt-6">
              <button type="button" id="cancelForm"
                class="text-gray-700 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                Cancel
              </button>
              <button type="submit" id="saveForm"
                class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                Save
              </button>
            </div>
          </div>
        </form>

        {{-- === USER ROLES (edit only) === --}}
        <section id="roleSection" class="mt-6 hidden">
          <div class=" dark:bg-gray-800  overflow-hidden">
            <div class="p-4 md:p-6">
              <div class="flex items-center justify-between">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">User Roles</h4>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                  Manage roles
                </span>
              </div>

              <div id="roleLocked" class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Save the user first to manage roles.
              </div>

              <div id="roleContent" class="mt-4 hidden">
                <div id="roleList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2"></div>
                <div class="flex justify-end mt-4">
                  <button type="button" id="saveRolesBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Save Roles
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- === MENU ACCESS PER USER (edit only) === --}}
        <section id="userMenuSection" class="mt-6 hidden">
          <div class=" dark:bg-gray-800  overflow-hidden">
            <div class="p-4 md:p-6">
              <div class="flex items-center justify-between">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Menu Access</h4>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                  Choose what this user can access
                </span>
              </div>

              <p id="userMenuLocked" class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Save the user first to manage menu access.
              </p>

              <div id="userMenuContent" class="mt-4 hidden">
                <div id="userMenuList" class="space-y-2"></div>
                <div class="flex justify-end mt-4">
                  <button type="button" id="saveUserMenusBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Save Menu Access
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>
  </section>

</div>
@endsection

@push('style')
<style>
  /* kecilkan kontrol DataTables */
  div.dataTables_length label {
    font-size: 0.75rem;
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
  input[type="search"][aria-controls="usersTable"] {
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  $(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    /* ========= Toast ========= */
    const isDark = () => document.documentElement.classList.contains('dark');

    function themeToast(icon, title) {
      const dark = isDark();
      Swal.fire({
        toast: true,
        icon,
        title,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true,
        background: dark ? '#1f2937' : '#ffffff',
        color: dark ? '#f9fafb' : '#111827',
        didOpen: el => {
          const bar = el.querySelector('.swal2-timer-progress-bar');
          if (bar) bar.style.background = dark ? '#10b981' : '#3b82f6';
        }
      });
    }

    /* ========= Busy helpers ========= */
    const spinnerSVG = `
    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>`;

    function beginBusy($btn, text = 'Processing...') {
      if ($btn.data('busy')) return false;
      $btn.data('busy', true);
      if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
      $btn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed');
      $btn.html(`<span class="inline-flex items-center">${spinnerSVG}${text}</span>`);
      return true;
    }

    function endBusy($btn) {
      const orig = $btn.data('orig-html');
      if (orig) $btn.html(orig);
      $btn.prop('disabled', false).removeClass('opacity-75 cursor-not-allowed');
      $btn.data('busy', false);
    }
    $(document).on('click', 'button', function(e) {
      const $b = $(this);
      if ($b.data('busy')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }
    });

    /* ========= Sections & State ========= */
    const $list = $('#listSection');
    const $form = $('#formSection');
    const $formEl = $('#userForm');
    const $methodSpoof = $('#methodSpoof');
    const $pwd = $('#f_password');
    const $pwdHint = $('#pwdHint');
    let editingId = null; // null = ADD mode

    function showList() {
      $form.addClass('hidden');
      $list.removeClass('hidden');
      $formEl[0].reset();
      $('#err-name,#err-email,#err-nik,#err-password').addClass('hidden').text('');
      editingId = null;
      $methodSpoof.val('POST');
      $formEl.attr('action', `{{ route('userMaintenance.store') }}`);
      $('#f_status').val('1');
      $pwd.prop('required', true);
      $pwdHint.text('(required)');
      // hide role & user menu panels
      showRoleSection(false);
      toggleRoleSectionEnabled(false);
      setUserMenuVisible(false);
      toggleUserMenuEnabled(false);
    }

    function showForm() {
      $list.addClass('hidden');
      $form.removeClass('hidden');
    }

    // ===== Role panel =====
    function showRoleSection(show) {
      show ? $('#roleSection').removeClass('hidden') : $('#roleSection').addClass('hidden');
    }

    function toggleRoleSectionEnabled(enabled) {
      if (enabled) {
        $('#roleLocked').addClass('hidden');
        $('#roleContent').removeClass('hidden');
      } else {
        $('#roleLocked').removeClass('hidden');
        $('#roleContent').addClass('hidden');
      }
    }

    // ===== User Menu panel =====
    function setUserMenuVisible(show) {
      show ? $('#userMenuSection').removeClass('hidden') : $('#userMenuSection').addClass('hidden');
    }

    function toggleUserMenuEnabled(enabled) {
      if (enabled) {
        $('#userMenuLocked').addClass('hidden');
        $('#userMenuContent').removeClass('hidden');
      } else {
        $('#userMenuLocked').removeClass('hidden');
        $('#userMenuContent').addClass('hidden');
      }
    }

    // === Roles ===
    function loadRolesForUser(userId) {
      return $.get("{{ route('role.data') }}", {
          user_id: userId,
          length: 1000
        })
        .then(res => {
          const rows = Array.isArray(res?.data) ? res.data : [];
          const $l = $('#roleList').empty();
          rows.forEach(r => {
            const checked = String(r.selected) === '1' || r.selected === 1 || r.selected === true;
            $l.append(`
            <label class="inline-flex items-center gap-2 p-2 rounded border dark:border-gray-700">
              <input type="checkbox" class="role-checkbox h-4 w-4" value="${r.id}" ${checked ? 'checked' : ''}>
              <span class="text-sm text-gray-800 dark:text-gray-200">${r.role_name}</span>
            </label>
          `);
          });
          return rows.length;
        });
    }

    // === User Menus (GATED PERMISSIONS) ===
    function loadMenusForUser(userId) {
      if (!userId) return;
      return $.get(`{{ route('role-menu.byUser', ':id') }}`.replace(':id', userId))
        .then(res => {
          const rows = Array.isArray(res?.data) ? res.data : [];
          const $list = $('#userMenuList').empty();

          if (rows.length === 0) {
            $list.append('<div class="text-sm text-gray-500 dark:text-gray-400">No menus.</div>');
            return;
          }

          rows.forEach(m => {
            const enabled = String(m.selected) === '1' || m.selected === 1 || m.selected === true;
            const row = $(`
            <div class="menu-row flex items-center justify-between gap-3 p-2 border rounded dark:border-gray-700" data-id="${m.id}">
              <div class="flex items-center gap-3">
                <input type="checkbox" class="menu-enable h-4 w-4" ${enabled ? 'checked' : ''}>
                <span class="text-sm text-gray-800 dark:text-gray-200">${m.title}</span>
              </div>
              <div class="perm-group flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" class="perm perm-view h-4 w-4" ${Number(m.can_view) ? 'checked' : ''}>
                  <span>View</span>
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" class="perm perm-upload h-4 w-4" ${Number(m.can_upload) ? 'checked' : ''}>
                  <span>Upload</span>
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" class="perm perm-download h-4 w-4" ${Number(m.can_download) ? 'checked' : ''}>
                  <span>Download</span>
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" class="perm perm-delete h-4 w-4" ${Number(m.can_delete) ? 'checked' : ''}>
                  <span>Delete</span>
                </label>
              </div>
            </div>
          `);

            // Gating: disable permission ketika menu belum enable
            row.find('.perm').prop('disabled', !enabled);
            row.find('.perm-group').toggleClass('opacity-50', !enabled);

            $list.append(row);
          });
        });
    }

    // Handler: toggle menu-enable -> atur permission
    $(document).on('change', '.menu-row .menu-enable', function() {
      const $row = $(this).closest('.menu-row');
      const enabled = $(this).is(':checked');

      if (!enabled) {
        // OFF -> clear semua permission
        $row.find('.perm').prop('checked', false);
      }
      // enable/disable permission + efek visual
      $row.find('.perm').prop('disabled', !enabled);
      $row.find('.perm-group').toggleClass('opacity-50', !enabled);
    });

    // ===== Modes =====
    function enterAddMode() {
      editingId = null;
      $('#formTitle').text('Add New User');
      $methodSpoof.val('POST');
      $formEl.attr('action', `{{ route('userMaintenance.store') }}`);
      $pwd.prop('required', true);
      $pwdHint.text('(required)');
      showRoleSection(false);
      toggleRoleSectionEnabled(false);
      setUserMenuVisible(false);
      toggleUserMenuEnabled(false);
    }

    function enterEditMode(user) {
      editingId = user.id;
      $('#formTitle').text('Edit User');
      $methodSpoof.val('PUT');
      $formEl.attr('action', `{{ route('userMaintenance.update', ':id') }}`.replace(':id', editingId));

      $('#f_name').val(user.name);
      $('#f_email').val(user.email);
      $('#f_nik').val(user.nik);
      $('#f_status').val(String(user.is_active));
      $pwd.val('').prop('required', false);
      $pwdHint.text('(optional)');

      // show & load roles
      showRoleSection(true);
      toggleRoleSectionEnabled(true);
      loadRolesForUser(editingId).catch(() => themeToast('error', 'Failed to load roles'));

      // show & load user menus (gated)
      setUserMenuVisible(true);
      toggleUserMenuEnabled(true);
      loadMenusForUser(editingId).catch(() => themeToast('error', 'Failed to load menus'));
    }

    /* ========= DataTable ========= */
    const table = $('#usersTable').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: '{{ route("userMaintenance.data") }}',
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
          data: 'name',
          name: 'name'
        },
        {
          data: 'email',
          name: 'email'
        },
        {
          data: 'nik',
          name: 'nik'
        },
        {
          data: 'is_active',
          render: v => String(v) === '1' ?
            `<span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>` :
            `<span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>`
        },
        {
          data: null,
          orderable: false,
          searchable: false,
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
      order: [
        [1, 'asc']
      ],
      language: {
        emptyTable: '<div class="text-gray-500 dark:text-gray-400">No users found.</div>'
      },
      responsive: true,
      autoWidth: false,
    });

    /* ========= Handlers ========= */

    // Add New -> form
    $('#add-button').on('click', function() {
      const $btn = $(this);
      if (!beginBusy($btn, 'Opening...')) return;
      enterAddMode();
      showForm();
      endBusy($btn);
    });

    // Back / Cancel -> list
    $('#backToList, #cancelForm').on('click', function() {
      showList();
    });

    // Edit
    $(document).on('click', '.edit-button', function() {
      const $btn = $(this);
      if (!beginBusy($btn, 'Loading...')) return;
      const id = $(this).data('id');
      const showUrl = "{{ route('userMaintenance.show', ':id') }}".replace(':id', id);

      $.get(showUrl, function(data) {
        showForm();
        enterEditMode(data);
      }).fail(function() {
        themeToast('error', 'Failed to load user');
      }).always(function() {
        endBusy($btn);
      });
    });

    // Delete
    $(document).on('click', '.delete-button', function() {
      const id = $(this).data('id');
      Swal.fire({
        title: 'Delete user?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        background: isDark() ? '#1f2937' : '#ffffff',
        color: isDark() ? '#f9fafb' : '#111827',
      }).then((result) => {
        if (!result.isConfirmed) return;
        $.ajax({
          url: `/master/userMaintenance/${id}`,
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          success: res => {
            if (res.success) {
              themeToast('success', 'User deleted');
              table.ajax.reload(null, false);
            } else {
              themeToast('error', res.message || 'Failed to delete');
            }
          },
          error: () => themeToast('error', 'Error deleting user'),
        });
      });
    });

    // Save (Create/Update)
    $('#userForm').on('submit', function(e) {
      e.preventDefault();
      const $btn = $('#saveForm');
      if (!beginBusy($btn, 'Saving...')) return;

      const formData = new FormData(this);
      formData.set('is_active', $('#f_status').val());
      $('#err-name,#err-email,#err-nik,#err-password').addClass('hidden').text('');

      $.ajax({
          url: $(this).attr('action'),
          method: 'POST', // spoof PUT via _method
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          data: formData,
          processData: false,
          contentType: false,
        })
        .done(res => {
          if (!res || !res.success) {
            themeToast('error', res?.message || 'Failed to save');
            return;
          }
          table.ajax.reload(null, false);

          if (!editingId) {
            const newId = res.id || res.data?.id;
            if (!newId) {
              themeToast('error', 'Server did not return new user id.');
              return;
            }
            const userObj = res.data || {
              id: newId,
              name: $('#f_name').val(),
              email: $('#f_email').val(),
              nik: $('#f_nik').val(),
              is_active: $('#f_status').val()
            };
            showForm();
            enterEditMode(userObj);
            themeToast('success', 'User saved. Assign roles & menu access.');
          } else {
            themeToast('success', 'User updated');
            showRoleSection(true);
            toggleRoleSectionEnabled(true);
            loadRolesForUser(editingId).catch(() => {});
            setUserMenuVisible(true);
            toggleUserMenuEnabled(true);
            loadMenusForUser(editingId).catch(() => {});
          }
        })
        .fail(xhr => {
          const errors = xhr.responseJSON?.errors;
          if (errors) {
            if (errors.name) $('#err-name').text(errors.name[0]).removeClass('hidden');
            if (errors.email) $('#err-email').text(errors.email[0]).removeClass('hidden');
            if (errors.nik) $('#err-nik').text(errors.nik[0]).removeClass('hidden');
            if (errors.password) $('#err-password').text(errors.password[0]).removeClass('hidden');
            themeToast('error', 'Validation error');
          } else {
            themeToast('error', 'Request failed');
          }
        })
        .always(() => endBusy($btn));
    });

    // Save Roles
    $('#saveRolesBtn').on('click', function() {
      if (!editingId) return;
      const $btn = $(this);
      if (!beginBusy($btn, 'Saving...')) return;

      const roleIds = $('.role-checkbox:checked').map(function() {
        return $(this).val();
      }).get();

      $.ajax({
          url: `{{ route('userMaintenance.update', ':id') }}`.replace(':id', editingId),
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          data: {
            _method: 'PUT',
            role_ids: roleIds
          },
        })
        .done(() => themeToast('success', 'Roles updated'))
        .fail(() => themeToast('error', 'Request failed'))
        .always(() => endBusy($btn));
    });

    // Save Menu Access (enabled diambil HANYA dari menu-enable)
    $('#saveUserMenusBtn').on('click', function() {
      if (!editingId) return;
      const $btn = $(this);
      if (!beginBusy($btn, 'Saving...')) return;

      const payload = $('.menu-row').map(function() {
        const $r = $(this);
        const id = $r.data('id');
        const en = $r.find('.menu-enable').is(':checked') ? 1 : 0; // gating ketat
        const get = cls => $r.find(cls).is(':checked') ? 1 : 0;
        return {
          menu_id: id,
          enabled: en,
          can_view: get('.perm-view'),
          can_upload: get('.perm-upload'),
          can_download: get('.perm-download'),
          can_delete: get('.perm-delete'),
        };
      }).get();

      $.ajax({
          url: '{{ route("role-menu.syncByUser", ":id") }}'.replace(':id', editingId),
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          data: JSON.stringify({
            menus: payload
          }),
        })
        .done(() => themeToast('success', 'Menu access updated'))
        .fail(() => themeToast('error', 'Failed to update menu access'))
        .always(() => endBusy($btn));
    });

    // Init
    showList();
  });
</script>
@endpush