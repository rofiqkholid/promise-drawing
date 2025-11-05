<header class="fixed top-0 left-20 right-0 z-40 flex justify-between items-center p-4 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300">
    <div>
        <h1 class="titlePromise text-[1.2rem] font-semibold text-gray-700 dark:text-gray-200">Promise</h1>
        <p class="text-[0.7rem] text-gray-400 dark:text-gray-200">Project Management Integrated System Engineering</p>
    </div>

    <div x-data="{ themeDropdownOpen: false }" class="flex items-center space-x-2 sm:space-x-4">

        <div x-data="searchComponent({{ json_encode($menuItems ?? []) }})" class="relative">
            <div class="relative">
                <input
                    type="text"
                    placeholder="Search..."
                    class="w-50 sm:w-64 pl-10 pr-4 py-2 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none"
                    x-model="searchQuery"
                    @focus="showDropdown()"
                    @blur="closeDropdown()"
                    @keydown="handleKeydown($event)"
                    x-ref="searchInput">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
            </div>

            <div x-show="searchOpen"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-72 sm:w-96 bg-white dark:bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
                style="display: none;"
                @mousedown.prevent>

                <div class="search-dropdown-container">
                    <template x-if="!searchQuery.trim()">
                        <div>
                            <div class="search-section-title">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Search History</span>
                                    <button @click.prevent="clearHistory()"
                                        class="text-xs text-blue-500 hover:text-blue-700 focus:outline-none transition-colors"
                                        x-bind:disabled="searchHistory.length === 0">
                                        Clean
                                    </button>
                                </div>
                            </div>

                            <template x-if="searchHistory.length > 0">
                                <ul>
                                    <template x-for="(item, index) in searchHistory" :key="index">
                                        <li>
                                            <button
                                                @mousedown="runHistorySearch(item)"
                                                class="w-full text-left search-history-item flex items-center text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400"
                                                :class="{ 'bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400': selectedIndex === index }">
                                                <i class="fa-solid fa-clock-rotate-left w-4 mr-3 text-gray-400"></i>
                                                <span x-text="item" class="truncate"></span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </template>

                            <template x-if="searchHistory.length === 0">
                                <div class="p-4 text-center">
                                    <i class="fa-solid fa-clock-rotate-left text-gray-400 text-lg mb-2"></i>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Search history is empty</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Your search will appear here</p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Search Results -->
                    <template x-if="searchQuery.trim() && filteredMenus.length > 0">
                        <div>
                            <div class="search-section-title">
                                <span class="text-xs font-semibold text-gray-500 uppercase">
                                    Results (<span x-text="filteredMenus.length"></span>)
                                </span>
                            </div>
                            <ul>
                                <template x-for="(menu, index) in filteredMenus" :key="menu.url">
                                    <li>
                                        <a
                                            :href="menu.url"
                                            @mousedown="addToHistory(menu.name)"
                                            class="block search-menu-item text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                            :class="{ 'bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400': selectedIndex === index + searchHistory.length }">
                                            <span x-html="highlightText(menu.name, searchQuery)" class="leading-relaxed"></span>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- No Results -->
                    <template x-if="searchQuery.trim() && filteredMenus.length === 0">
                        <div class="p-6 text-center">
                            <i class="fa-solid fa-search text-gray-400 text-xl mb-3"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">There is no menu that matches</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">"<span x-text="searchQuery"></span>"</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="relative">
            <button @click="themeDropdownOpen = !themeDropdownOpen" type="button" class="p-2 w-10 h-10 flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none transition-colors duration-200" title="Pilih Tema">
                <i x-show="!darkMode" class="fa-solid fa-sun text-xl"></i>
                <i x-show="darkMode" style="display: none;" class="fa-solid fa-moon text-xl"></i>
            </button>

            <div x-show="themeDropdownOpen"
                @click.away="themeDropdownOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-36 origin-top-right bg-white dark:bg-gray-900 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
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
                <i class="fa-solid fa-right-from-bracket text-xl"></i>
                <span class="ml-2 text-sm hidden md:block"></span>
            </button>
        </form>
    </div>
</header>
<script src="{{ asset('assets/js/search-engine.js') }}"></script>