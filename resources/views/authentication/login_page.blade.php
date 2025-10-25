<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - PROMISE Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .theme-transition {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-2xl shadow-lg border border-gray-200">

        <div class="text-center">

            <img src="{{ asset('assets/image/favicon.ico') }}" alt="PROMISE Logo" class="mx-auto h-24 w-auto mb-4">

            <h1 class="text-3xl font-semibold text-gray-900">PROMISE</h1>
            <h2 class="mt-2 text-xl font-semibold text-gray-800">
                Sign In to Your Account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Project Management Integrated System Engineering
            </p>
        </div>

        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-md" role="alert">
            <p class="font-bold">Authentication Failed</p>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form class="space-y-6" action="{{ route('login_post') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700">Employee ID</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 2a1.5 1.5 0 00-1.5 1.5V5.25a.75.75 0 001.5 0V3.5A1.5 1.5 0 0010 2zM5.25 5.25a.75.75 0 000 1.5h1.5a.75.75 0 000-1.5H5.25zM12 8a4 4 0 11-8 0 4 4 0 018 0zM15 11.25a.75.75 0 00-1.5 0v1.5a.75.75 0 001.5 0v-1.5z" clip-rule="evenodd" />
                                <path d="M3 10a7 7 0 1114 0 7 7 0 01-14 0zM10 4a6 6 0 100 12 6 6 0 000-12z" />
                            </svg>
                        </div>
                        <input id="nik" name="nik" type="text" autocomplete="username" required
                            value="{{ old('nik') }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 bg-white text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="e.g., 202577-001">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md placeholder-gray-400 bg-white text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" id="toggle-password" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <svg id="eye-icon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                <svg id="eye-slash-icon" class="h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074L3.707 2.293zM10.75 7.5a2.5 2.5 0 00-3.536 3.536l2.5-2.5a1.5 1.5 0 011.036-1.036z" />
                                    <path d="M10 5c.104 0 .207.004.31.011l-1.054 1.054A3.001 3.001 0 007 10c0 .398.076.78.217 1.132l-1.44 1.44A9.963 9.963 0 01.458 10C1.732 5.943 5.522 5 10 5z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Sign In
                </button>
            </div>
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');

        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.classList.toggle('hidden', isPassword);
            eyeSlashIcon.classList.toggle('hidden', !isPassword);
        });

        const form = document.querySelector('form');
        const submitBtn = document.querySelector('button[type="submit"]');
        let isSubmitting = false;
        const originalBtnHTML = submitBtn.innerHTML;


        const spinnerSVG = `
    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
  `;

        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;


            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            submitBtn.setAttribute('aria-busy', 'true');
            submitBtn.setAttribute('aria-disabled', 'true');
            submitBtn.innerHTML = `
      <span class="flex items-center justify-center gap-2">
        ${spinnerSVG}
        <span>Signing in...</span>
      </span>
    `;

        });
    </script>
</body>

</html>