{{-- Diperbarui dengan Font Awesome dan Dropdown Tema --}}
<header class="flex justify-between items-center p-4 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300">
    <div>
        <h1 class="text-[1.2rem] font-semibold text-gray-700 dark:text-gray-200">PROMISE</h1>
        <p class="text-[0.7rem] text-gray-400 dark:text-gray-200">Engineering Document Management</p>
    </div>

    <div x-data="{ themeDropdownOpen: false }" class="flex items-center space-x-4">
        
        <div class="relative">
            {{-- Tombol untuk membuka dropdown --}}
            <button @click="themeDropdownOpen = !themeDropdownOpen" type="button" class="p-2 w-10 h-10 flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none transition-colors duration-200" title="Pilih Tema">
                <i x-show="!darkMode" class="fa-solid fa-sun text-xl"></i>
                <i x-show="darkMode" style="display: none;" class="fa-solid fa-moon text-xl"></i>
            </button>

            {{-- Menu Dropdown --}}
            <div x-show="themeDropdownOpen" 
                 @click.away="themeDropdownOpen = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-36 origin-top-right bg-white dark:bg-gray-900 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                 style="display: none;">
                <div class="py-1">
                    <a href="#" @click.prevent="darkMode = false; themeDropdownOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fa-solid fa-sun w-5 mr-2"></i>
                        Light
                    </a>
                    <a href="#" @click.prevent="darkMode = true; themeDropdownOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fa-solid fa-moon w-5 mr-2"></i>
                        Dark
                    </a>
                </div>
            </div>
        </div>

        <span class="text-sm text-gray-600 dark:text-gray-300 hidden sm:block">{{ Auth::user()->name }}</span>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-500 focus:outline-none transition-colors duration-200" title="Logout">
                {{-- Ikon Logout Font Awesome --}}
                <i class="fa-solid fa-right-from-bracket text-xl"></i>
                <span class="ml-2 text-sm hidden md:block"></span>
            </button>
        </form>
    </div>
</header>