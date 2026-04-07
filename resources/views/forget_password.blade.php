<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image/logo-promise.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus-within {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-glow {
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.3));
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-2xl h-[500px] bg-white rounded-xs border border-gray-200 overflow-hidden p-8 space-y-8">

        <div class="text-center">
            <a href="{{ route('login') }}">
                <img src="{{ asset('assets/image/logo-promise.png') }}" alt="PROMISE Logo" class="mx-auto h-[80px] w-auto mb-4 logo-glow">
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Forget Password</h1>
            <p class="text-gray-500 text-sm mt-2">Enter your NIK to reset your password</p>
        </div>

        <div id="error-container" class="hidden bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg" role="alert">
            <p id="error-message" class="text-sm"></p>
        </div>

        <!-- Step 1: Search NIK -->
        <div id="step-1" class="space-y-6">
            <div>
                <label for="nik" class="block text-sm font-semibold text-gray-700 mb-2">Employee ID (NIK)</label>
                <div class="relative mt-1 input-field">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input id="nik" type="text" required
                        class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                        placeholder="e.g., 202577-001">
                </div>
            </div>

            <button type="button" id="search-btn"
                class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-blue-800 to-cyan-700 hover:from-blue-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                Search
            </button>
        </div>

        <!-- Step 2: Show Details & Send Link -->
        <div id="step-2" class="hidden space-y-6 fade-in">
            <div class="bg-blue-50 rounded-xs p-6 border border-blue-100 shadow-sm">
                <div class="grid grid-cols-3 gap-y-4 text-sm">
                    <div class="text-gray-500 font-medium">NIK</div>
                    <div class="col-span-2 text-gray-700 font-bold" id="display-nik"></div>

                    <div class="text-gray-500 font-medium">Name</div>
                    <div class="col-span-2 text-gray-700 font-bold" id="display-name"></div>

                    <div class="text-gray-500 font-medium">Email</div>
                    <div class="col-span-2 text-blue-800 font-bold" id="display-email"></div>
                </div>
            </div>

            <div class="space-y-3">
                <button type="button" id="send-link-btn"
                    class="w-full flex justify-center py-3 px-4 border text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    Send link to reset password
                </button>
                <button type="button" onclick="window.location.reload()"
                    class="w-full text-sm text-gray-500 hover:text-gray-700 font-medium py-2">
                    Search different NIK
                </button>
            </div>
        </div>

        <!-- Step 3: Success -->
        <div id="step-3" class="hidden space-y-6 fade-in text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Email Sent!</h2>
            <p class="text-gray-600 text-sm">A password reset link has been sent to your email address. <br> Please check your inbox (and spam folder).</p>

            <div class="mt-8">
                <a href="{{ route('login') }}" class="text-sm text-blue-800 hover:text-blue-800 font-semibold">Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const step3 = document.getElementById('step-3');
        const searchBtn = document.getElementById('search-btn');
        const sendLinkBtn = document.getElementById('send-link-btn');
        const nikInput = document.getElementById('nik');
        const errorContainer = document.getElementById('error-container');
        const errorMessage = document.getElementById('error-message');
        const displayNik = document.getElementById('display-nik');
        const displayName = document.getElementById('display-name');
        const displayEmail = document.getElementById('display-email');
        const resultLink = document.getElementById('result-link');

        const spinnerSVG = `
            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
        `;

        function showError(msg) {
            errorMessage.innerText = msg;
            errorContainer.classList.remove('hidden');
        }

        function hideError() {
            errorContainer.classList.add('hidden');
        }

        searchBtn.addEventListener('click', async () => {
            const nik = nikInput.value.trim();
            if (!nik) {
                showError('Please enter your NIK');
                return;
            }

            hideError();
            searchBtn.disabled = true;
            const originalHTML = searchBtn.innerHTML;
            searchBtn.innerHTML = spinnerSVG + 'Searching...';

            try {
                const response = await fetch('{{ route("forget_password.search_nik") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nik
                    })
                });

                const data = await response.json();

                if (data.success) {
                    displayNik.innerText = nik;
                    displayName.innerText = data.name;
                    displayEmail.innerText = data.email;
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                } else {
                    showError(data.message || 'NIK not found in our database');
                }
            } catch (error) {
                showError('Something went wrong. Please try again.');
            } finally {
                searchBtn.disabled = false;
                searchBtn.innerHTML = originalHTML;
            }
        });

        sendLinkBtn.addEventListener('click', async () => {
            const nik = displayNik.innerText;

            sendLinkBtn.disabled = true;
            const originalHTML = sendLinkBtn.innerHTML;
            sendLinkBtn.innerHTML = spinnerSVG + 'Generating link...';

            try {
                const response = await fetch('{{ route("forget_password.send_link") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nik
                    })
                });

                const data = await response.json();

                if (data.success) {
                    step2.classList.add('hidden');
                    step3.classList.remove('hidden');
                } else {
                    showError(data.message || 'Failed to generate reset link');
                }
            } catch (error) {
                showError('An error occurred. Please try again.');
            } finally {
                sendLinkBtn.disabled = false;
                sendLinkBtn.innerHTML = originalHTML;
            }
        });
    </script>
</body>

</html>