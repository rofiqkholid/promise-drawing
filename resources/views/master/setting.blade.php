@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Master Data')

@section('content')

<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100" x-data="{ tab: 'doc_group' }">
    {{-- Header Halaman --}}
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Add New Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
    </div>

    {{-- Konten Utama --}}
    <div class="mt-8">
        {{-- Navigasi Tab --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="#" @click.prevent="tab = 'doc_group'"
                   :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'doc_group', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'doc_group' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150">
                    Document Group
                </a>
                <a href="#" @click.prevent="tab = 'sub_type'"
                   :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'sub_type', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'sub_type' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150">
                    Sub Type
                </a>
                <a href="#" @click.prevent="tab = 'customer'"
                   :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'customer', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'customer' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150">
                    Customer
                </a>
                <a href="#" @click.prevent="tab = 'model'"
                   :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'model', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'model' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150">
                    Model
                </a>
            </nav>
        </div>

        {{-- Panel Konten Tab --}}
        <div class="mt-6">
            {{-- Panel Document Group --}}
            <div x-show="tab === 'doc_group'" x-transition class="bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Manage Document Group</h3>
                <form action="#" method="POST" class="flex items-end gap-4 mb-6">
                    @csrf
                    <div class="flex-grow">
                        <label for="doc_group_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group Name</label>
                        <input type="text" name="doc_group_name" id="doc_group_name" placeholder="e.g., DRAWING"
                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-0 focus:border-blue-500 dark:focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        Add New
                    </button>
                </form>
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">DRAWING</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">2024-05-21 10:00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400">Delete</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panel Sub Type --}}
            <div x-show="tab === 'sub_type'" x-transition class="bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" style="display: none;">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Manage Sub Type</h3>
                 <form action="#" method="POST" class="flex items-end gap-4 mb-6">
                    @csrf
                    <div class="flex-grow">
                        <label for="sub_type_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sub Type Name</label>
                        <input type="text" name="sub_type_name" id="sub_type_name" placeholder="e.g., PART LIST"
                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-0 focus:border-blue-500 dark:focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        Add New
                    </button>
                </form>
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                             <tr class="bg-white dark:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">PART LIST</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">2024-05-19 11:30</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400">Delete</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panel Customer --}}
            <div x-show="tab === 'customer'" x-transition class="bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" style="display: none;">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Manage Customer</h3>
                <form action="#" method="POST" class="grid grid-cols-1 sm:grid-cols-3 items-end gap-4 mb-6">
                    @csrf
                    <div class="sm:col-span-1">
                        <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" placeholder="e.g., Mitsubishi Motors"
                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-0 focus:border-blue-500 dark:focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                     <div class="sm:col-span-1">
                        <label for="customer_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Code</label>
                        <input type="text" name="customer_code" id="customer_code" placeholder="e.g., MMKI"
                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-0 focus:border-blue-500 dark:focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        Add New
                    </button>
                </form>
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer Code</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" value="" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">Mitsubishi Motors</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">MMKI</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/70 dark:text-green-300">Active</span>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 dark:bg-gray-900/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" value="" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">Astra Daihatsu Motor</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">ADM</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panel Model --}}
            <div x-show="tab === 'model'" x-transition class="bg-white dark:bg-gray-800 p-7 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" style="display: none;">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Manage Model</h3>
                <form action="#" method="POST" class="grid grid-cols-1 sm:grid-cols-4 items-end gap-4 mb-6">
                    @csrf
                    <div class="sm:col-span-1">
                        <label for="model_customer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                        <select id="model_customer" name="model_customer" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 border rounded-md focus:outline-none focus:ring-0 focus:border-blue-500 dark:focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option>Select Customer</option>
                            <option>MMKI</option>
                            <option>ADM</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1">
                        <label for="model_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model Name</label>
                        <input type="text" name="model_name" id="model_name" placeholder="e.g., Pajero Sport"
                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-0 focus:border-blue-500 dark:focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                     <div class="sm:col-span-1">
                        <label for="model_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model Code</label>
                        <input type="text" name="model_code" id="model_code" placeholder="e.g., 4L45W"
                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-0 focus:border-blue-500 dark:focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        Add New
                    </button>
                </form>
                 <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model Code</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">MMKI</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Pajero Sport</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">4L45W</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400">Delete</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection