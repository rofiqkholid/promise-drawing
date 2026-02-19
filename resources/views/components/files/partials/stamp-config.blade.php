@props(['showStampConfig', 'selectedFile', 'applyToAllProcessing', 'stampConfig'])

@if($showStampConfig)
    <div x-show="isPreviewable2D(selectedFile?.name)" 
         class="mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm transition-all duration-300">
         
        <div class="px-4 py-3">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-stamp"></i> Stamp Configuration
                </span>

                {{-- Apply to All Files Button --}}
                <button type="button" @click="applyStampToAll()" :disabled="applyToAllProcessing"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-blue-500 text-[11px] font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                    <span x-show="!applyToAllProcessing">
                        <i class="fa-solid fa-layer-group mr-1"></i>
                        Apply to All Files
                    </span>
                    <span x-show="applyToAllProcessing" class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        Applying...
                    </span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Original Stamp Position --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position: Original</label>
                    <div class="relative">
                        <select x-model="stampConfig.original" @change="onStampChange()"
                            class="block w-full pl-3 pr-8 py-2 text-xs text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-blue-500">
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
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position: Copy</label>
                    <div class="relative">
                        <select x-model="stampConfig.copy" @change="onStampChange()"
                            class="block w-full pl-3 pr-8 py-2 text-xs text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-blue-500">
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
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Position: Obsolete</label>
                    <div class="relative">
                        <select x-model="stampConfig.obsolete" @change="onStampChange()"
                            class="block w-full pl-3 pr-8 py-2 text-xs text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:ring-blue-500">
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
