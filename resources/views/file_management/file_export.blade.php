@extends('layouts.app')

@section('title', 'Download - File Manager')
@section('header-title', 'File Manager/Download')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900">
    <div class="mb-8">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 sm:text-3xl">Download Files</h1>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Find and download your files from the Data Center.</p>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 mb-8">
        <div class="p-6">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-filter text-blue-500"></i>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Filter Options</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach(['Customer', 'Model', 'Document Type', 'Category'] as $label)
                    @php $modelName = lcfirst(str_replace(' ', '', $label)); @endphp
                    <div>
                        <label for="{{ $modelName }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <div class="relative mt-1">
                            <select id="{{ $modelName }}" name="{{ $modelName }}" class="appearance-none block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm py-2.5 pl-4 pr-12">
                                <option value="">All</option>
                                {{-- Add options here --}}
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-chevron-down fa-xs"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-span-1 sm:col-span-2 lg:col-span-1 flex items-end">
                    <button class="w-full inline-flex items-center gap-2 justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Show</span>
                    <select class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-sm">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Entries</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        @foreach(['File Name', 'Customer', 'Model', 'Part No', 'Doc Type', 'Sub Category'] as $header)
                        <th scope="col" class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">
                            <a href="#" class="group inline-flex items-center gap-2">
                                {{ $header }}
                                <i class="fa-solid fa-sort text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300"></i>
                            </a>
                        </th>
                        @endforeach
                         <th scope="col" class="py-3.5 px-4 text-center text-sm font-semibold text-gray-900 dark:text-gray-200">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @for ($i = 0; $i < 5; $i++)
                    <tr class="{{ $i % 2 == 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/50' }} hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="whitespace-nowrap py-4 px-4 text-sm font-medium text-gray-900 dark:text-white">MMKI-4L45W_rev1.dwg</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-500 dark:text-gray-400">MMKI</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-500 dark:text-gray-400">4L45W</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-500 dark:text-gray-400">5251D644</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-500 dark:text-gray-400">Part DWG</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $i % 2 == 0 ? '2D' : '3D' }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            <button class="inline-flex items-center gap-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md shadow-sm">
                                <i class="fa-solid fa-download fa-sm"></i> Download
                            </button>
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">97</span> results
                </p>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 dark:text-gray-500 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 cursor-not-allowed">
                        <span class="sr-only">Previous</span>
                        <i class="fa-solid fa-chevron-left fa-sm"></i>
                    </a>
                    <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20">1</a>
                     <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">2</a>
                    <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <span class="sr-only">Next</span>
                        <i class="fa-solid fa-chevron-right fa-sm"></i>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection