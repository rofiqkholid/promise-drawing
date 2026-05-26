<header class="fixed top-0 left-0 md:left-20 right-0 z-40 flex justify-between items-center py-2 px-4 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300">
    <div class="flex items-center gap-2 sm:gap-3">
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 -ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <div class="flex flex-col">
            <h1 class="titlePromise text-[1.5rem] font-semibold text-gray-700 dark:text-gray-200 leading-none">Promise <span class="text-gray-400 dark:text-gray-600 mx-1 font-light">|</span> <span class="text-[0.7rem] font-normal text-gray-500 dark:text-gray-400 tracking-widest">Drawing</span></h1>
            <p class="hidden sm:block text-[0.7rem] text-gray-400 dark:text-gray-200 mt-1">Project Management Integrated System Engineering</p>
        </div>
    </div>

    <div class="flex items-center space-x-2 sm:space-x-4">

        <div class="hidden gap-2 md:flex items-center space-x-1 border-r border-gray-200 dark:border-gray-700 pr-5 mr-5">

            @if (session()->has('allowed_menus') && in_array(3, session('allowed_menus')))
            <a href="{{ route('file-manager.upload') }}"
                class="relative p-2 w-10 h-10 flex items-center justify-center rounded-full text-gray-500 hover:bg-green-100 hover:text-green-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-green-400 transition-colors duration-200"
                title="Upload">
                <i class="fa-solid fa-cloud-arrow-up text-lg"></i>

                @if(isset($notifUpload) && $notifUpload > 0)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[0.6rem] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full border-2 border-white dark:border-gray-800">
                    {{ $notifUpload > 99 ? '99+' : $notifUpload }}
                </span>
                @endif
            </a>
            @endif

            @if (session()->has('allowed_menus') && in_array(4, session('allowed_menus')))
            <a href="{{ route('file-manager.export') }}"
                class="relative p-2 w-10 h-10 flex items-center justify-center rounded-full text-gray-500 hover:bg-yellow-100 hover:text-yellow-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-yellow-400 transition-colors duration-200"
                title="Download">
                <i class="fa-solid fa-cloud-arrow-down text-lg"></i>

                {{-- Badge Notif --}}
                @if(isset($notifExport) && $notifExport > 0)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[0.6rem] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full border-2 border-white dark:border-gray-800">
                    {{ $notifExport > 99 ? '99+' : $notifExport }}
                </span>
                @endif
            </a>
            @endif

            @if (session()->has('allowed_menus') && in_array(29, session('allowed_menus')))
            <a href="{{ route('file-manager.share') }}"
                class="relative p-2 w-10 h-10 flex items-center justify-center rounded-full text-gray-500 hover:bg-purple-100 hover:text-purple-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-purple-400 transition-colors duration-200"
                title="Shared Files">
                <i class="fa-solid fa-share-nodes text-lg"></i>

                @if(isset($notifShare) && $notifShare > 0)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[0.6rem] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full border-2 border-white dark:border-gray-800">
                    {{ $notifShare > 99 ? '99+' : $notifShare }}
                </span>
                @endif
            </a>
            @endif

        </div>

        <div x-data="searchComponent(@js($menuItems ?? []))" class="hidden sm:block relative flex-1 sm:flex-initial sm:w-64 ml-2 sm:ml-0">
            <div class="relative">
                <input
                    type="text"
                    placeholder="Search..."
                    class="w-full pl-10 pr-4 py-2 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                    x-model="searchQuery"
                    @focus="showDropdown()"
                    @blur="closeDropdown()"
                    @keydown="handleKeydown($event)"
                    x-ref="searchInput">
                <span class="absolute left-3 inset-y-0 flex items-center text-gray-400 dark:text-gray-500">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
            </div>

            <div x-show="searchOpen"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="fixed left-4 right-4 top-14 w-auto sm:absolute sm:top-auto sm:left-auto sm:right-0 sm:w-96 sm:mt-2 bg-white dark:bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
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

        <!-- 9-Dots Apps Menu -->
        <div x-data="{ appsDropdownOpen: false }" class="relative ml-1 sm:ml-2 flex-shrink-0">
            <button @click="appsDropdownOpen = !appsDropdownOpen"
                class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none text-gray-500 dark:text-gray-400" title="Apps Menu">
                <i class="fa-solid fa-grip text-xl"></i>
            </button>

            <!-- Desktop Apps Dropdown -->
            <div x-show="appsDropdownOpen"
                @click.away="appsDropdownOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="hidden sm:block absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-slate-100 dark:border-gray-700 p-3 z-50 origin-top-right"
                style="display: none;">
                
                <div class="grid grid-cols-3 gap-1">
                    <a href="{{ env('APP_DRAWING_URL') }}"
                        class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                            <i class="fa-solid fa-pen-ruler text-sm"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">Drawing</span>
                    </a>

                    <a href="{{ env('APP_INVENTORY_URL') }}"
                        class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                            <i class="fa-solid fa-boxes-stacked text-sm"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">Inventory</span>
                    </a>

                    <a href="{{ env('APP_NPC_URL') }}"
                        class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                            <i class="fa-solid fa-users-gear text-sm"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">NPC</span>
                    </a>

                    <a href="{{ env('APP_ALL_DASHBOARD_URL') }}"
                        class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center bg-teal-50 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                            <i class="fa-solid fa-chart-pie text-sm"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">All Dashboard</span>
                    </a>

                    <a href="{{ env('APP_MNG_URL') }}"
                        class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                            <i class="fa-solid fa-briefcase text-sm"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">Management</span>
                    </a>
                </div>
            </div>

            <!-- Mobile Apps Drawer (Slide-over from Right) -->
            <div x-show="appsDropdownOpen" 
                 class="sm:hidden fixed inset-0 z-50" 
                 style="display: none;">
                <!-- Backdrop -->
                <div x-show="appsDropdownOpen"
                     x-transition:enter="transition-opacity ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="appsDropdownOpen = false"
                     class="fixed inset-0 bg-black/40"></div>

                <!-- Drawer Content Panel -->
                <div x-show="appsDropdownOpen"
                     x-transition:enter="transition-transform ease-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition-transform ease-in duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="fixed right-0 top-0 bottom-0 w-64 bg-white dark:bg-gray-800 p-4 shadow-2xl flex flex-col z-50">
                     
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-300 dark:border-gray-700">
                        <span class="text-sm font-bold text-gray-800 dark:text-white tracking-wider">Select App</span>
                        <button @click="appsDropdownOpen = false" class="p-1 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Apps List (Vertical Stack) -->
                    <div class="flex flex-col gap-2.5">
                        <a href="{{ env('APP_DRAWING_URL') }}"
                            class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-indigo-500/50 hover:bg-indigo-50/10 dark:hover:bg-indigo-950/10 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-none flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-indigo-100 dark:border-indigo-900/20">
                                    <i class="fa-solid fa-pen-ruler text-sm"></i>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Drawing</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                        </a>

                        <a href="{{ env('APP_INVENTORY_URL') }}"
                            class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-blue-500/50 hover:bg-blue-50/10 dark:hover:bg-blue-950/10 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-none flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-blue-100 dark:border-blue-900/20">
                                    <i class="fa-solid fa-boxes-stacked text-sm"></i>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Inventory</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                        </a>

                        <a href="{{ env('APP_NPC_URL') }}"
                            class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-purple-500/50 hover:bg-purple-50/10 dark:hover:bg-purple-950/10 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-none flex items-center justify-center bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-purple-100 dark:border-purple-900/20">
                                    <i class="fa-solid fa-users-gear text-sm"></i>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">NPC</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                        </a>

                        <a href="{{ env('APP_ALL_DASHBOARD_URL') }}"
                            class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-teal-500/50 hover:bg-teal-50/10 dark:hover:bg-teal-950/10 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-none flex items-center justify-center bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-teal-100 dark:border-teal-900/20">
                                    <i class="fa-solid fa-chart-pie text-sm"></i>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">All Dashboard</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                        </a>

                        <a href="{{ env('APP_MNG_URL') }}"
                            class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-emerald-500/50 hover:bg-emerald-50/10 dark:hover:bg-emerald-950/10 transition-all duration-200 group">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-none flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-emerald-100 dark:border-emerald-900/20">
                                    <i class="fa-solid fa-briefcase text-sm"></i>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Management</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="{ userDropdownOpen: false }" class="relative ml-1 sm:ml-2 flex-shrink-0">

            <button @click="userDropdownOpen = !userDropdownOpen"
                class="flex items-center space-x-2 p-1 sm:p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none">

                <div class="hidden sm:flex flex-col text-right ml-1">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 leading-tight">{{ Auth::user()->name }}</span>
                    <span class="text-[0.65rem] text-gray-500 dark:text-gray-400">{{ Auth::user()->department->code ?? '' }}</span>
                </div>

                <div class="relative w-8 h-8 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                    <i class="fa-solid fa-circle-user text-2xl"></i>
                </div>

                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 hidden sm:block pr-1 transition-transform duration-200"
                    :class="{'rotate-180': userDropdownOpen}"></i>
            </button>

            <div x-show="userDropdownOpen"
                @click.away="userDropdownOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-1.5 mr-1.5 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50 origin-top-right"
                style="display: none;">

                <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                    <div class="hidden sm:flex flex-col text-left mb-2">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ Auth::user()->name }}</span>
                        <span class="text-[0.65rem] text-gray-500 dark:text-gray-400">{{ Auth::user()->email}}</span>
                    </div>

                    <a href="#" @click.prevent="darkMode = false"
                        class="flex items-center px-2 py-1.5 text-sm rounded-md transition-colors"
                        :class="!darkMode ? 'bg-blue-50 text-blue-600 dark:bg-gray-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'">
                        <i class="fa-solid fa-sun w-5"></i>
                        <span class="ml-2">Light Mode</span>
                    </a>

                    <a href="#" @click.prevent="darkMode = true"
                        class="flex items-center px-2 py-1.5 text-sm rounded-md transition-colors mt-1"
                        :class="darkMode ? 'bg-blue-50 text-blue-600 dark:bg-gray-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'">
                        <i class="fa-solid fa-moon w-5"></i>
                        <span class="ml-2">Dark Mode</span>
                    </a>
                </div>
                <!-- App URLs moved to 9-dots menu -->

                <div class="px-1 py-1">
                    <form method="GET" action="{{ route('user.update') }}">
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                            <i class="fa-solid fa-user w-5"></i>
                            <span class="ml-2">Profile</span>
                        </button>
                    </form>
                </div>
                <div class="px-1 py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                            <i class="fa-solid fa-right-from-bracket w-5"></i>
                            <span class="ml-2">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
<script src="{{ asset('assets/js/search-engine.js') }}"></script>