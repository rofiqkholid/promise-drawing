<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
<aside
    x-data="{ openMenu: '' }"
    @mouseleave="openMenu = ''"
    class="no-scrollbar fixed top-0 left-0 h-screen z-30 group w-20 hover:w-64 p-4 bg-white dark:bg-gray-900 flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out overflow-y-auto overflow-x-hidden shadow-lg border-r border-gray-200 dark:border-gray-700">

    {{-- Header/Logo Section --}}
    <div class="relative flex items-center justify-center h-16 mb-10 flex-shrink-0">
        <div class="absolute transition-opacity duration-200 opacity-100 group-hover:opacity-0">
            <svg class="h-8 w-8 text-blue-700 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
        </div>
        <div class="absolute whitespace-nowrap transition-opacity duration-200 opacity-0 group-hover:opacity-100">
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-500">PROMISE</span>
            <p class="text-[0.7rem] text-gray-400 dark:text-gray-500">Engineering Document Management</p>
        </div>
    </div>

    {{-- Navigasi --}}
    @php
    $activeParentId = null;
    if (isset($menus)) {
    foreach ($menus as $menu) {
    if ($menu->children->isNotEmpty()) {
    foreach ($menu->children as $child) {
    if (request()->routeIs($child->route)) {
    $activeParentId = $menu->id;
    break;
    }
    }
    }
    if ($activeParentId) break;
    }
    }
    @endphp

    <nav class="flex-grow" x-data="{ openMenu: {{ $activeParentId ?? 'null' }} }">
        <ul>
            @foreach ($menus as $menu)
            {{-- PERBAIKAN 1: Tambahkan kondisi IF untuk cek hak akses menu --}}
            @if (session()->has('allowed_menus') && in_array($menu->id, session('allowed_menus')))

            @if ($menu->children->isNotEmpty())
            <li class="mb-2">
                <button type="button" @click.prevent="openMenu = (openMenu === {{ $menu->id }} ? null : {{ $menu->id }})"
                    class="w-full flex items-center justify-between p-3 rounded-lg transition-colors duration-200 text-sm font-medium text-left
                                    hover:bg-blue-50 hover:text-blue-800 dark:hover:bg-gray-800 dark:hover:text-gray-200
                                    {{ ($activeParentId == $menu->id) ? 'bg-blue-50 text-blue-800 dark:bg-gray-800 dark:text-gray-200' : 'text-gray-600 dark:text-gray-400' }}">

                    <div class="flex items-center">
                        <span class="flex items-center justify-center w-5 mr-3 flex-shrink-0">
                            <i class="{{ $menu->icon }}"></i>
                        </span>
                        <span class="whitespace-nowrap transition-opacity duration-200 opacity-0 group-hover:opacity-100">{{ $menu->title }}</span>
                    </div>

                    <span class="whitespace-nowrap transition-opacity duration-200 opacity-0 group-hover:opacity-100">
                        <i class="fa-solid fa-chevron-down h-4 w-4 transform transition-transform duration-200" :class="{'rotate-180': openMenu === {{ $menu->id }}}"></i>
                    </span>
                </button>

                <ul x-show="openMenu === {{ $menu->id }}" x-transition class="mt-1 pl-4 space-y-1 hidden group-hover:block" style="display: none;">
                    @foreach ($menu->children as $child)
                    {{-- PERBAIKAN 2: Tambahkan kondisi IF juga untuk sub-menu --}}
                    @if (session()->has('allowed_menus') && in_array($child->id, session('allowed_menus')))
                    <li>
                        <a href="{{ route($child->route) }}" class="flex items-center p-3 rounded-lg transition-colors duration-200 text-xs font-medium
                                                hover:bg-blue-50 hover:text-blue-800 dark:hover:bg-gray-800 dark:hover:text-gray-200
                                                {{ request()->routeIs($child->route) ? 'font-bold text-blue-700 dark:text-white' : 'text-gray-600 dark:text-gray-400' }}">
                            <span class="flex items-center justify-center w-5 mr-3 flex-shrink-0">
                                <i class="{{ $child->icon }}"></i>
                            </span>
                            <span class="whitespace-nowrap transition-opacity duration-200 opacity-0 group-hover:opacity-100">{{ $child->title }}</span>
                        </a>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </li>
            @else
            <li class="mb-2">
                <a href="{{ route($menu->route) }}" class="flex items-center p-3 rounded-lg transition-colors duration-200 text-sm font-medium
                                hover:bg-blue-50 hover:text-blue-800 dark:hover:bg-gray-800 dark:hover:text-gray-200
                                {{ request()->routeIs($menu->route) ? 'bg-blue-50 text-blue-800 dark:bg-gray-800 dark:text-gray-200' : 'text-gray-600 dark:text-gray-400' }}">
                    <span class="flex items-center justify-center w-5 mr-3 flex-shrink-0">
                        <i class="{{ $menu->icon }}"></i>
                    </span>
                    <span class="whitespace-nowrap transition-opacity duration-200 opacity-0 group-hover:opacity-100">{{ $menu->title }}</span>
                </a>
            </li>
            @endif

            @endif {{-- Penutup dari @if hak akses --}}
            @endforeach
        </ul>
    </nav>

    <div class="flex-shrink-0 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button @click="darkMode = !darkMode" type="button" class="w-full flex items-center p-3 rounded-lg transition-colors duration-200 text-sm font-medium
            text-gray-600 dark:text-gray-400 hover:bg-blue-700 hover:text-white dark:hover:bg-gray-700">
            <div class="flex items-center justify-center w-5 flex-shrink-0">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </div>
            <span class="ml-3 whitespace-nowrap transition-opacity duration-200 opacity-0 group-hover:opacity-100">
            </span>
        </button>
    </div>
</aside>