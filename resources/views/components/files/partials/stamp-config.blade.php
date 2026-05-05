@props(['showStampConfig', 'selectedFile', 'applyToAllProcessing', 'stampConfig'])

@if($showStampConfig)
    <div x-show="isPreviewable2D(selectedFile?.name)" 
         class="mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-none shadow-sm transition-all duration-300">
         
        <div class="px-4 py-4">
            <div class="flex items-start justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="hidden sm:flex w-11 h-11 items-center justify-center text-gray-400 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-none">
                         <i class="fa-solid fa-stamp text-xl"></i>
                    </div>
                    <div class="flex flex-col -space-y-0.5">
                        <span class="text-[11px] font-semibold text-gray-400 tracking-wide">Stamp</span>
                        <span class="text-[15px] font-bold text-gray-800 dark:text-gray-200">Configuration</span>
                    </div>
                </div>

                {{-- Apply to All Button --}}
                <button type="button" @click="applyStampToAll()" :disabled="applyToAllProcessing"
                    class="inline-flex flex-col items-center justify-center px-4 sm:px-5 py-2 sm:py-3 rounded-none border border-blue-600 text-[11px] font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-all min-w-[100px] sm:min-w-[130px] h-10 sm:h-[72px]">
                    <span x-show="!applyToAllProcessing" class="flex flex-col items-center gap-0.5 sm:gap-1">
                        <i class="hidden sm:block fa-solid fa-layer-group text-sm sm:text-base mb-0.5"></i>
                        <span class="leading-none">Apply to All</span>
                    </span>
                    <span x-show="applyToAllProcessing" class="inline-flex flex-col items-center gap-1">
                        <i class="fa-solid fa-circle-notch fa-spin text-sm text-white"></i>
                        <span>Applying...</span>
                    </span>
                </button>
            </div>

            <div class="flex flex-col gap-6 max-w-2xl">
                {{-- Original Stamp Position --}}
                <div class="space-y-2">
                    <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 px-0.5">Position: Original</label>
                    <div class="relative">
                        <select x-model="stampConfig.original" @change="onStampChange()"
                            class="block w-full h-11 pl-4 pr-10 text-[14px] text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-none focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22M6%208l4%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat">
                            <option value="top-left">Top Left</option>
                            <option value="top-center">Top Center</option>
                            <option value="top-right">Top Right</option>
                            <option value="bottom-left">Bottom Left</option>
                            <option value="bottom-center">Bottom Center</option>
                            <option value="bottom-right">Bottom Right</option>
                        </select>
                    </div>
                </div>

                {{-- Copy Stamp Position --}}
                <div class="space-y-2">
                    <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 px-0.5">Position: Copy</label>
                    <div class="relative">
                        <select x-model="stampConfig.copy" @change="onStampChange()"
                            class="block w-full h-11 pl-4 pr-10 text-[14px] text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-none focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22M6%208l4%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat">
                            <option value="top-left">Top Left</option>
                            <option value="top-center">Top Center</option>
                            <option value="top-right">Top Right</option>
                            <option value="bottom-left">Bottom Left</option>
                            <option value="bottom-center">Bottom Center</option>
                            <option value="bottom-right">Bottom Right</option>
                        </select>
                    </div>
                </div>

                {{-- Obsolete Stamp Position --}}
                <div class="space-y-2">
                    <label class="block text-[13px] font-bold text-gray-500 dark:text-gray-400 px-0.5">Position: Obsolete</label>
                    <div class="relative">
                        <select x-model="stampConfig.obsolete" @change="onStampChange()"
                            class="block w-full h-11 pl-4 pr-10 text-[14px] text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-none focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22M6%208l4%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat">
                            <option value="top-left">Top Left</option>
                            <option value="top-center">Top Center</option>
                            <option value="top-right">Top Right</option>
                            <option value="bottom-left">Bottom Left</option>
                            <option value="bottom-center">Bottom Center</option>
                            <option value="bottom-right">Bottom Right</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
