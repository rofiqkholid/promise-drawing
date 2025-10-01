@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Master Data')

@section('content')

<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Stamp Format Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Add New
            </button>
        </div>
    </div>

    {{-- Main Content: Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6">
            {{-- Table Controls --}}
            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
                <div class="w-full md:w-1/4 flex items-center">
                    <label for="entries" class="text-sm font-medium text-gray-700 dark:text-gray-200 mr-2">Show</label>
                    <select id="entries" name="entries" class="block w-auto pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 ml-2">Entries</span>
                </div>
                <div class="w-full md:w-auto flex items-center space-x-2">
                    <div class="relative">
                        <input type="search" placeholder="Search..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                     <button class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                        Filters
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto mt-6">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">#</th>
                            <th scope="col" class="px-6 py-3">
                                <a href="#" class="flex items-center">Prefix</a>
                            </th>
                            <th scope="col" class="px-6 py-3">
                                <a href="#" class="flex items-center">Suffix</a>
                            </th>
                            <th scope="col" class="px-6 py-3">
                                <a href="#" class="flex items-center">In Active</a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4"></td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">4L4SW</td>
                            <td class="px-6 py-4">4L4SW</td>
                            <td class="px-6 py-4">MMKI</td>
                            <td class="px-6 py-4 text-center">
                                <button class="font-medium text-yellow-600 dark:text-yellow-500 hover:underline mr-3 px-3 py-1 bg-yellow-100 dark:bg-yellow-900 rounded-md">Edit</button>
                                <button class="font-medium text-red-600 dark:text-red-500 hover:underline px-3 py-1 bg-red-100 dark:bg-red-900 rounded-md">Delete</button>
                            </td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4"></td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">4L4SW</td>
                            <td class="px-6 py-4">4L4SW</td>
                            <td class="px-6 py-4">HPM</td>
                            <td class="px-6 py-4 text-center">
                                <button class="font-medium text-yellow-600 dark:text-yellow-500 hover:underline mr-3 px-3 py-1 bg-yellow-100 dark:bg-yellow-900 rounded-md">Edit</button>
                                <button class="font-medium text-red-600 dark:text-red-500 hover:underline px-3 py-1 bg-red-100 dark:bg-red-900 rounded-md">Delete</button>
                            </td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4"></td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">4L4SW</td>
                            <td class="px-6 py-4">4L4SW</td>
                            <td class="px-6 py-4">SKELELKI</td>
                            <td class="px-6 py-4 text-center">
                                <button class="font-medium text-yellow-600 dark:text-yellow-500 hover:underline mr-3 px-3 py-1 bg-yellow-100 dark:bg-yellow-900 rounded-md">Edit</button>
                                <button class="font-medium text-red-600 dark:text-red-500 hover:underline px-3 py-1 bg-red-100 dark:bg-red-900 rounded-md">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <nav class="flex items-center justify-between pt-4" aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Showing <span class="font-semibold text-gray-900 dark:text-white">1-3</span> of <span class="font-semibold text-gray-900 dark:text-white">3</span></span>
                <ul class="inline-flex -space-x-px text-sm h-8">
                    <li>
                        <a href="#" class="flex items-center justify-center px-3 h-8 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                           <span class="sr-only">Previous</span>
                           <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                           </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#" aria-current="page" class="flex items-center justify-center px-3 h-8 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">1</a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                           <span class="sr-only">Next</span>
                           <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                           </svg>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection

