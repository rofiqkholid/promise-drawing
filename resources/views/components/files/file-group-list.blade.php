@props(['title', 'icon', 'category', 'allowDownload' => false])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden mb-4">
    <button @click="toggleSection('{{$category}}')"
        class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
        :aria-expanded="openSections.includes('{{$category}}')">
        <div class="flex flex-col gap-0.5 text-left">
            <div class="flex items-center">
                <i class="fa-solid {{$icon}} mr-3 w-4 text-center text-gray-500 dark:text-gray-400"></i>
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $title }}
                </span>
            </div>
            @if ($category === '3d')
            <div class="ml-7 flex items-center text-[10px] text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-circle-info text-blue-500 mr-1.5 opacity-75"></i>
                <span>Preview available for .igs/.iges, .stp/.step files only</span>
            </div>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full"
                  x-text="`${(pkg.files['{{$category}}']?.length || 0)} files`"></span>
            <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform"
               :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
        </div>
    </button>
    
    <div x-show="openSections.includes('{{$category}}')" x-collapse>
        <div class="p-2 max-h-72 overflow-y-auto">
            <template x-for="file in (pkg.files['{{$category}}'] || [])" :key="file.name">
                <div @click="selectFile(file)"
                     :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}"
                     class="flex items-center {{ $allowDownload ? 'justify-between' : '' }} p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 group"
                     role="button" tabindex="0" @keydown.enter="selectFile(file)">

                    <div class="flex items-center min-w-0 pr-2">
                        <template x-if="file.icon_src">
                            <img :src="file.icon_src" alt="" class="w-5 h-5 mr-3 object-contain" />
                        </template>

                        <template x-if="!file.icon_src">
                            <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 transition-colors group-hover:text-blue-500"></i>
                        </template>
                        <span class="text-sm text-gray-900 dark:text-gray-100 truncate"
                              x-text="file.name" :title="file.name"></span>
                    </div>


                    @if($allowDownload)
                    <button @click.stop="downloadFile(file)"
                        :disabled="isDownloadingFile === file.name"
                        class="flex-shrink-0 text-xs inline-flex items-center gap-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded shadow-sm transition-colors opacity-90 hover:opacity-100 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid" :class="isDownloadingFile === file.name ? 'fa-spinner fa-spin' : 'fa-download'"></i>
                    </button>
                    @endif
                </div>
            </template>

            <template x-if="(pkg.files['{{$category}}'] || []).length === 0">
                <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No files available.</p>
            </template>
        </div>
    </div>
</div>
