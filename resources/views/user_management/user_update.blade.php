@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'My Profile')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">

    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">My Profile</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your account details and credentials.</p>
        </div>
    </div>

    <section id="formSection">
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden w-full">
            <div class="p-6 md:p-8">

                <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Personal Information
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Make sure your personal information is up to date.</p>
                </div>

                <form id="profileForm" action="{{ route('userMaintenance.update', Auth::user()->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label for="f_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Full Name</label>
                            <input type="text" id="f_name" name="name" value="{{ Auth::user()->name }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 transition-colors"
                                required>
                            <p id="err-name" class="text-red-500 text-xs mt-1 hidden"></p>
                        </div>

                        <div>
                            <label for="f_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Address</label>
                            <input type="email" id="f_email" name="email" value="{{ Auth::user()->email }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 transition-colors dark:disabled:bg-gray-800 disabled:bg-gray-200"
                                required
                                @disabled(Auth::user()->id_dept != 3)>
                            <p id="err-email" class="text-red-500 text-xs mt-1 hidden"></p>
                        </div>

                        <div>
                            <label for="f_nik" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NIK</label>
                            <input type="text" id="f_nik" name="nik" value="{{ Auth::user()->nik }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 transition-colors disabled:bg-gray-200 dark:disabled:bg-gray-800"
                                required
                                @disabled(Auth::user()->id_dept != 3)>
                            <p id="err-nik" class="text-red-500 text-xs mt-1 hidden"></p>
                        </div>

                        <div>
                            <label for="f_department" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Department
                            </label>
                            <select id="f_department" name="id_dept"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors disabled:bg-gray-200 dark:disabled:bg-gray-800"
                                @disabled(Auth::user()->id_dept != 3)>
                                @if(Auth::user()->id_dept)
                                <option value="{{ Auth::user()->id_dept }}" selected>
                                    {{ Auth::user()->department->code ?? Auth::user()->department_name ?? 'Unknown Department' }}
                                </option>
                                @endif
                            </select>
                            <p id="err-id_dept" class="text-red-500 text-xs mt-1 hidden"></p>
                        </div>

                        <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-100 dark:border-gray-700 mt-2">
                            <label for="f_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Update Password <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(Optional)</span>
                            </label>
                            <input type="password" id="f_password" name="password" placeholder="••••••••"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full md:w-1/2 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 transition-colors">
                            <p id="err-password" class="text-red-500 text-xs mt-1 hidden"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Leave blank if you don't want to change your current password.</p>
                        </div>

                        <input type="hidden" name="is_active" value="{{ Auth::user()->is_active }}">
                    </div>

                    <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="submit" id="saveBtn"
                            class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-2.5 text-center inline-flex items-center transition-all shadow-sm">
                            <i class="fa-solid fa-save mr-2"></i> Save Changes
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
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

        const spinnerSVG = `
    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>`;

        function beginBusy($btn, text = 'Saving...') {
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

        // Hindari double-click pada semua button
        $(document).on('click', 'button', function(e) {
            const $b = $(this);
            if ($b.data('busy')) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });

        // ==== INIT SELECT2: Department ====
        $('#f_department').select2({
            width: '100%',
            placeholder: 'Select Department',
            ajax: {
                url: '{{ route("userMaintenance.departments.select2") }}',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term || '',
                    page: params.page || 1,
                }),
                processResults: (data, params) => {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: data.pagination
                    };
                },
            }
        });

        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#saveBtn');
            if (!beginBusy($btn, 'Saving Changes...')) return;

            const formData = new FormData(this);

            $('#err-name,#err-email,#err-nik,#err-password').addClass('hidden').text('');

            $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
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
                        themeToast('error', res?.message || 'Failed to update profile');
                        return;
                    }

                    themeToast('success', 'Profile updated successfully!');

                    $('#f_password').val('');

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) $('#err-name').text(errors.name[0]).removeClass('hidden');
                        if (errors.email) $('#err-email').text(errors.email[0]).removeClass('hidden');
                        if (errors.nik) $('#err-nik').text(errors.nik[0]).removeClass('hidden');
                        if (errors.password) $('#err-password').text(errors.password[0]).removeClass('hidden');
                        themeToast('error', 'Please check your inputs');
                    } else {
                        themeToast('error', 'Request failed');
                    }
                })
                .always(() => endBusy($btn));
        });

    });
</script>
@endpush