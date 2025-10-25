<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('header-title', 'PROMISE')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

    <link rel="stylesheet" href="{{ asset('assets/css/select2.css') }}">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>


    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">

    <style>
        #main-content {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .loader-spinner {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3b82f6;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        .dark .loader-spinner {
            border-color: #4a5568;
            border-top-color: #60a5fa;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .swal2-container {
            z-index: 99999 !important;
        }

        .swal2-toast {
            font-size: 0.85rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .15), 0 4px 8px rgba(0, 0, 0, .08);
            backdrop-filter: saturate(140%) blur(6px);
        }

        .swal2-title {
            font-weight: 600;
            letter-spacing: .1px;
        }

        .swal2-html-container {
            font-size: 0.8rem;
            opacity: .9;
        }

        .swal2-timer-progress-bar {
            height: 2px;
            opacity: .6;
        }

        @keyframes toast-in {
            from {
                transform: translateX(18px) translateY(0);
                opacity: 0;
            }

            to {
                transform: translateX(0) translateY(0);
                opacity: 1;
            }
        }

        @keyframes toast-out {
            from {
                transform: translateX(0) translateY(0);
                opacity: 1;
            }

            to {
                transform: translateX(18px) translateY(0);
                opacity: 0;
            }
        }

        .swal2-animate-toast-in {
            animation: toast-in .36s cubic-bezier(.22, .61, .36, 1) both;
        }

        .swal2-animate-toast-out {
            animation: toast-out .28s cubic-bezier(.4, 0, .2, 1) both;
        }

        @media (prefers-reduced-motion: reduce) {

            .swal2-animate-toast-in,
            .swal2-animate-toast-out {
                animation: none !important;
            }
        }
    </style>

    @stack('style')
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 transition-colors duration-300">

    <div id="loader" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="loader-spinner"></div>
    </div>

    <div id="main-content" class="relative min-h-screen flex">

        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col pl-20">
            @include('layouts.header')

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.onload = function() {
            const loader = document.getElementById('loader');
            const content = document.getElementById('main-content');

            loader.style.display = 'none';

            content.style.visibility = 'visible';
            content.style.opacity = '1';
        };
    </script>

    @stack('scripts')
</body>

</html>