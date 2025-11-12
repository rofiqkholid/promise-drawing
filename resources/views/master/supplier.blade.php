@extends('layouts.app')
@section('title', 'Master Data Management')
@section('header-title', 'Supplier Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Supplier Master</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage master data for the application.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- Main Content: Table Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <table id="suppliersTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-16">No</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="name">Supplier Name</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="code">Supplier Code</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="email">Email</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="phone">Phone</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="address">Address</th>
                        <th scope="col" class="px-6 py-3 sorting" data-column="is_active">Status</th>
                        <th scope="col" class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Supplier Modal --}}
<div id="addSupplierModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Add New Supplier</h3>
            <form id="addSupplierForm" action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. ABC Corp" required>
                    <p id="add-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" id="code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. ABC" required>
                    <p id="add-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Email</label>
                    <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. contact@abccorp.com">
                    <p id="add-email-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Phone</label>
                    <input type="text" name="phone" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. +1234567890">
                    <p id="add-phone-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Address</label>
                    <textarea name="address" id="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g. 123 Main St, City, Country"></textarea>
                    <p id="add-address-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600" checked>
                        <label for="is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
                    </div>
                    <p id="add-is_active-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Supplier Modal --}}
<div id="editSupplierModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white">Edit Supplier</h3>
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-name-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Supplier Code <span class="text-red-600">*</span></label>
                    <input type="text" name="code" id="edit_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <p id="edit-code-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Email</label>
                    <input type="email" name="email" id="edit_email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <p id="edit-email-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Phone</label>
                    <input type="text" name="phone" id="edit_phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <p id="edit-phone-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Address</label>
                    <textarea name="address" id="edit_address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea>
                    <p id="edit-address-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="edit_is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="bg-gray-50 border border-gray-300 text-primary-600 rounded focus:ring-primary-600 focus:border-primary-600 h-4 w-4 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600">
                        <label for="edit_is_active" class="ml-2 text-sm text-gray-900 dark:text-white">Active</label>
                    </div>
                    <p id="edit-is_active-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteSupplierModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this supplier?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                    No, cancel
                </button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                    Yes, I'm sure
                </button>
            </div>
        </div>
    </div>
</div>

{{-- User Link List Modal (has Add & Action column) --}}
<div id="userLinkListModal" tabindex="-1" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-3xl h-full md:h-auto">
        <div
            class="relative p-4 pt-10 pr-12 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-6 sm:pt-10 sm:pr-12">

            <button type="button"
                class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>

            <div class="sm:flex sm:items-center sm:justify-between mb-4">
                <h3 id="userLinkListTitle" class="text-xl font-medium text-gray-900 dark:text-white">
                    User Link List
                </h3>
                <button type="button" id="add-link-button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                    <i class="fa-solid fa-plus"></i> Add New
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
                <div class="p-4 md:p-6 overflow-x-auto">
                    <table id="userLinksTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3 w-16">No</th>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- [MODIFIED] Add User Link Modal --}}
<div id="addUserLinkModal" tabindex="-1" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-left bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <h3 class="mb-4 text-xl text-center font-medium text-gray-900 dark:text-white ">
                Add User Link
            </h3>

            <form id="addUserLinkForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="ul_user_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white text-left">User <span class="text-red-600">*</span></label>

                    {{-- This is the new dropdown --}}
                    <select name="user_id" id="ul_user_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-0 focus:border-gray-300 dark:focus:border-gray-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" style="width: 100%;" required>
                        {{-- Select2 will populate this --}}
                    </select>

                    <p id="add-ul-user_id-error" class="text-red-500 text-xs mt-1 text-left hidden"></p>
                </div>

                <div class="flex items-center space-x-4 mt-6">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- [REMOVED] Edit User Link Modal --}}
{{-- This modal is no longer needed --}}

{{-- Delete User Link Modal --}}
<div id="deleteUserLinkModal" tabindex="-1" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this user link?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                    No, cancel
                </button>
                <button type="button" id="confirmDeleteLinkButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                    Yes, I'm sure
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/select2.css') }}" rel="stylesheet" />
{{-- Add Select2 theme styles if needed, e.g., for dark mode --}}
<style>
    /* Fix Select2 in modal styling */
    .select2-container--open {
        z-index: 9999 !important;
    }

    /* Simple dark mode for Select2 */
    .dark .select2-container--default .select2-selection--single,
    .dark .select2-dropdown {
        background-color: #374151;
        /* gray-700 */
        border-color: #4b5563;
        /* gray-600 */
        color: #f3f4f6;
        /* gray-100 */
    }

    .dark .select2-container--default .select2-selection--single .select2-selection__rendered,
    .dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
        color: #f3f4f6;
        /* gray-100 */
    }

    .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6;
        /* blue-500 */
    }

    .dark .select2-search--dropdown .select2-search__field {
        background-color: #4b5563;
        /* gray-600 */
        border-color: #4b5563;
        /* gray-600 */
        color: #f3f4f6;
    }

    .dark .select2-results__option {
        color: #f3f4f6;
    }
</style>
@endpush

@push('scripts')
{{-- Make sure you have select2.min.js loaded in your app.blade.php or here --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}

<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Suppliers DataTable
        const table = $('#suppliersTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{{ route("suppliers.data") }}',
                type: 'GET',
                data: function(d) {
                    d.search = d.search.value;
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data, type, row) {
                        return `<button class="view-links"
                                    data-id="${row.id}" data-sname="${data}" title="View User Link List">
                                ${data}
                                </button>`;
                    }
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data) {
                        return data ?
                            '<span class="inline-block px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>' :
                            '<span class="inline-block px-3 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Inactive</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                        <button class="edit-button text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit" data-id="${row.id}">
                            <i class="fa-solid fa-pen-to-square fa-lg m-2"></i>
                        </button>
                        <button class="delete-button text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete" data-id="${row.id}">
                            <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                        </button>`;
                    }
                }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [
                [1, 'asc']
            ],
            language: {
                emptyTable: '<div class="text-gray-500 dark:text-gray-400">No suppliers found.</div>'
            },
            responsive: true,
            autoWidth: false,
        });

        // Modals
        const addModal = $('#addSupplierModal');
        const editModal = $('#editSupplierModal');
        const deleteModal = $('#deleteSupplierModal');
        const userLinkListModal = $('#userLinkListModal');
        const addUserLinkModal = $('#addUserLinkModal');
        // const editUserLinkModal = $('#editUserLinkModal'); // <-- REMOVED
        const deleteUserLinkModal = $('#deleteUserLinkModal');

        const addButton = $('#add-button');
        const closeButtons = $('.close-modal-button');

        // State vars
        let supplierIdToDelete = null;
        let currentSupplierIdForLinks = null;
        let currentUserIdToUnlink = null; // Renamed from currentLinkIdToDelete
        let userLinksDT = null;

        // Helpers
        function showModal(modal) {
            modal.removeClass('hidden').addClass('flex');
        }

        function hideModal(modal) {
            modal.addClass('hidden').removeClass('flex');
        }

        function setButtonLoading($btn, isLoading, loadingText = 'Processing...') {
            if (!$btn || !$btn.length) return;
            if (isLoading) {
                if (!$btn.data('orig-html')) $btn.data('orig-html', $btn.html());
                $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed')
                    .html(`<span class="inline-flex items-center gap-2">
                            <svg aria-hidden="true" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>${loadingText}</span>`);
            } else {
                const orig = $btn.data('orig-html');
                if (orig) $btn.html(orig);
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            }
        }

        function setFormBusy($form, busy) {
            $form.find('input,select,textarea,button').prop('disabled', busy);
        }

        function detectTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            return isDark ? {
                mode: 'dark',
                bg: 'rgba(30,41,59,.95)',
                fg: '#E5E7EB',
                border: 'rgba(71,85,105,.5)',
                progress: 'rgba(255,255,255,.9)',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444',
                    warning: '#f59e0b',
                    info: '#3b82f6'
                }
            } : {
                mode: 'light',
                bg: 'rgba(255,255,255,.98)',
                fg: '#0f172a',
                border: 'rgba(226,232,240,1)',
                progress: 'rgba(15,23,42,.8)',
                icon: {
                    success: '#16a34a',
                    error: '#dc2626',
                    warning: '#d97706',
                    info: '#2563eb'
                }
            };
        }

        // -- HANYA DI MIXIN: hover + styling --
        const BaseToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2600,
            timerProgressBar: true,
            showClass: {
                popup: 'swal2-animate-toast-in'
            },
            hideClass: {
                popup: 'swal2-animate-toast-out'
            },
            didOpen: (el) => {
                // PAUSE saat hover
                el.addEventListener('mouseenter', Swal.stopTimer);
                el.addEventListener('mouseleave', Swal.resumeTimer);

                // Styling progress & border
                const t = detectTheme();
                const bar = el.querySelector('.swal2-timer-progress-bar');
                if (bar) bar.style.background = t.progress;
                const popup = el.querySelector('.swal2-popup');
                if (popup) popup.style.borderColor = t.border;
            }
        });

        // -- TIDAK ADA didOpen DI SINI --
        function renderToast({
            icon = 'success',
            title = 'Success',
            text = ''
        } = {}) {
            const t = detectTheme();
            BaseToast.fire({
                icon,
                title,
                text,
                iconColor: t.icon[icon] || t.icon.success,
                background: t.bg,
                color: t.fg,
                customClass: {
                    popup: 'swal2-toast border',
                    title: '',
                    timerProgressBar: ''
                }
            });
        }

        function toastSuccess(title = 'Berhasil', text = 'Operasi berhasil dijalankan.') {
            renderToast({
                icon: 'success',
                title,
                text
            });
        }

        function toastError(title = 'Gagal', text = 'Terjadi kesalahan.') {
            BaseToast.update({
                timer: 3400
            });
            renderToast({
                icon: 'error',
                title,
                text
            });
            BaseToast.update({
                timer: 2600
            });
        }

        function toastWarning(title = 'Peringatan', text = 'Periksa kembali data Anda.') {
            renderToast({
                icon: 'warning',
                title,
                text
            });
        }

        function toastInfo(title = 'Informasi', text = '') {
            renderToast({
                icon: 'info',
                title,
                text
            });
        }

        window.toastSuccess = toastSuccess;
        window.toastError = toastError;
        window.toastWarning = toastWarning;
        window.toastInfo = toastInfo;


        addButton.on('click', () => {
            $('#addSupplierForm')[0].reset();
            $('#is_active').prop('checked', true);
            showModal(addModal);
        });
        closeButtons.on('click', () => {
            hideModal(addModal);
            hideModal(editModal);
            hideModal(deleteModal);
            hideModal(userLinkListModal);
            hideModal(addUserLinkModal);
            // hideModal(editUserLinkModal); // <-- REMOVED
            hideModal(deleteUserLinkModal);
        });

        // Add Supplier
        $('#addSupplierForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this),
                $btn = $form.find('[type="submit"]');
            $('#add-name-error,#add-code-error,#add-email-error,#add-phone-error,#add-address-error,#add-is_active-error').addClass('hidden');
            const formData = new FormData(this);
            formData.set('is_active', $('#is_active').is(':checked') ? '1' : '0');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(addModal);
                        $form[0].reset();
                        $('#is_active').prop('checked', true);
                        toastSuccess('Success', 'Supplier added successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to add supplier.');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) $('#add-name-error').text(errors.name[0]).removeClass('hidden');
                        if (errors.code) $('#add-code-error').text(errors.code[0]).removeClass('hidden');
                        if (errors.email) $('#add-email-error').text(errors.email[0]).removeClass('hidden');
                        if (errors.phone) $('#add-phone-error').text(errors.phone[0]).removeClass('hidden');
                        if (errors.address) $('#add-address-error').text(errors.address[0]).removeClass('hidden');
                        if (errors.is_active) $('#add-is_active-error').text(errors.is_active[0]).removeClass('hidden');
                    }
                    toastError('Error', xhr.responseJSON?.message || 'Failed to add supplier.');
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // Edit Supplier (open)
        $(document).on('click', '.edit-button', function() {
            const id = $(this).data('id');
            $('#edit-name-error,#edit-code-error,#edit-email-error,#edit-phone-error,#edit-address-error,#edit-is_active-error').addClass('hidden');
            $.ajax({
                url: `/master/suppliers/${id}`,
                method: 'GET',
                beforeSend: function() {
                    setButtonLoading($(`.edit-button[data-id="${id}"]`), true, '');
                },
                success: function(data) {
                    $('#edit_name').val(data.name);
                    $('#edit_code').val(data.code);
                    $('#edit_email').val(data.email);
                    $('#edit_phone').val(data.phone);
                    $('#edit_address').val(data.address);
                    $('#edit_is_active').prop('checked', data.is_active);
                    $('#editSupplierForm').attr('action', `/master/suppliers/${id}`);
                    showModal(editModal);
                },
                error: function(xhr) {
                    toastError('Error', xhr.responseJSON?.message || 'Failed to fetch supplier data.');
                },
                complete: function() {
                    setButtonLoading($(`.edit-button[data-id="${id}"]`), false);
                }
            });
        });

        // Edit Supplier (submit)
        $('#editSupplierForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this),
                $btn = $form.find('[type="submit"]');
            $('#edit-name-error,#edit-code-error,#edit-email-error,#edit-phone-error,#edit-address-error,#edit-is_active-error').addClass('hidden');
            const formData = new FormData(this);
            formData.set('is_active', $('#edit_is_active').is(':checked') ? '1' : '0');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(editModal);
                        toastSuccess('Success', 'Supplier updated successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to update supplier.');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.name) $('#edit-name-error').text(errors.name[0]).removeClass('hidden');
                        if (errors.code) $('#edit-code-error').text(errors.code[0]).removeClass('hidden');
                        if (errors.email) $('#edit-email-error').text(errors.email[0]).removeClass('hidden');
                        if (errors.phone) $('#edit-phone-error').text(errors.phone[0]).removeClass('hidden');
                        if (errors.address) $('#edit-address-error').text(errors.address[0]).removeClass('hidden');
                        if (errors.is_active) $('#edit-is_active-error').text(errors.is_active[0]).removeClass('hidden');
                    }
                    toastError('Error', xhr.responseJSON?.message || 'Failed to update supplier.');
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // Delete Supplier
        $(document).on('click', '.delete-button', function() {
            supplierIdToDelete = $(this).data('id');
            showModal(deleteModal);
        });
        $('#confirmDeleteButton').on('click', function() {
            if (!supplierIdToDelete) return;
            const $btn = $(this);
            $.ajax({
                url: `/master/suppliers/${supplierIdToDelete}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Deleting...');
                    setFormBusy($('#deleteSupplierModal'), true);
                },
                success: function(data) {
                    if (data.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        supplierIdToDelete = null;
                        toastSuccess('Success', 'Supplier deleted successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to delete supplier.');
                    }
                },
                error: function(xhr) {
                    toastError('Error', xhr.responseJSON?.message || 'Failed to delete supplier.');
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($('#deleteSupplierModal'), false);
                }
            });
        });

        // ===== [MODIFIED] User Link List =====
        function initOrReloadUserLinksDT(supplierId) {
            // [MODIFIED] New URL
            // [BENAR]
            const ajaxUrl = `/master/suppliers/${supplierId}/links/data`;
            if (userLinksDT) {
                userLinksDT.ajax.url(ajaxUrl).load();
                return;
            }
            userLinksDT = $('#userLinksTable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: ajaxUrl, // [MODIFIED]
                    type: 'GET',
                    data: function(d) {
                        d.search = d.search.value;
                    }
                },
                columns: [{
                        data: null,
                        render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                    },
                    {
                        data: 'name', // from users table
                        name: 'name'
                    },
                    {
                        data: 'email', // from users table
                        name: 'email'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            // [MODIFIED] Only show delete button. 'row.id' is now the USER_ID
                            return `
                            <button class="delete-link-button text-red-600 hover:text-red-900"
                                    title="Delete" data-id="${row.id}" data-supplier="${currentSupplierIdForLinks}">
                                <i class="fa-solid fa-trash-can fa-lg m-2"></i>
                            </button>`;
                        }
                    },
                ],
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                order: [
                    [1, 'asc']
                ], // sort by name
                language: {
                    emptyTable: '<div class="text-gray-500 dark:text-gray-400">No linked users.</div>'
                },
                responsive: true,
                autoWidth: false,
            });
        }

        // click supplier name -> open user links modal
        $(document).on('click', '.view-links', function() {
            currentSupplierIdForLinks = $(this).data('id');
            const sname = $(this).data('sname');
            $('#userLinkListTitle').text(`User Link List — ${sname}`);
            initOrReloadUserLinksDT(currentSupplierIdForLinks);
            showModal(userLinkListModal);
        });

        // [MODIFIED] Add Link button -> open add form
        $('#add-link-button').on('click', function() {
            if (!currentSupplierIdForLinks) return;

            $('#addUserLinkForm')[0].reset();
            $('#add-ul-user_id-error').addClass('hidden');

            // [MODIFIED] Set form action
            $('#addUserLinkForm').attr('action', `/master/suppliers/${currentSupplierIdForLinks}/links`);
            const $select = $('#ul_user_id');

            // Destroy previous Select2 instance if it exists
            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2('destroy');
            }

            // Clear old options
            $select.html('');

            // [NEW] Initialize Select2 for the user dropdown
            $select.select2({
                placeholder: 'Search user...',
                allowClear: true,
                dropdownParent: $('#addUserLinkModal'), // Penting untuk modal
                ajax: {
                    // [MODIFIKASI] URL ke route 'suppliers.links.available' baru
                    url: `/master/suppliers/${currentSupplierIdForLinks}/links/available-users`,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term // search term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
            });

            showModal(addUserLinkModal);
        });

        // [MODIFIED] Submit Add User Link
        $('#addUserLinkForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this),
                $btn = $form.find('[type="submit"]');

            $('#add-ul-user_id-error').addClass('hidden'); // [MODIFIED] error ID
            const formData = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Saving...');
                    setFormBusy($form, true);
                },
                success: function(data) {
                    if (data.success) {
                        if (userLinksDT) userLinksDT.ajax.reload(null, false);
                        hideModal(addUserLinkModal);
                        toastSuccess('Success', 'User linked successfully.');
                    } else {
                        toastError('Error', data.message || 'Failed to add user link.');
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        // [MODIFIED] Check for user_id error
                        if (errors.user_id) $('#add-ul-user_id-error').text(errors.user_id[0]).removeClass('hidden');
                    }
                    toastError('Error', xhr.responseJSON?.message || 'Failed to add user link.');
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy($form, false);
                }
            });
        });

        // [REMOVED] Edit Link (open)

        // [REMOVED] Edit Link (submit)

        // [MODIFIED] Delete Link
        $(document).on('click', '.delete-link-button', function() {
            currentUserIdToUnlink = $(this).data('id'); // This is now USER_ID
            $('#confirmDeleteLinkButton').data('supplier', $(this).data('supplier'));
            showModal(deleteUserLinkModal);
        });

        $('#confirmDeleteLinkButton').on('click', function() {
            const supplierId = $(this).data('supplier');
            const $btn = $(this);
            if (!currentUserIdToUnlink || !supplierId) return;

            // [MODIFIED] New URL structure
            $.ajax({
                url: `/master/suppliers/${supplierId}/users/${currentUserIdToUnlink}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                beforeSend: function() {
                    setButtonLoading($btn, true, 'Deleting...');
                    setFormBusy(deleteUserLinkModal, true);
                },
                success: function(resp) {
                    if (resp.success) {
                        if (userLinksDT) userLinksDT.ajax.reload(null, false);
                        hideModal(deleteUserLinkModal);
                        currentUserIdToUnlink = null;
                        toastSuccess('Success', 'User link deleted successfully.');
                    } else {
                        toastError('Error', resp.message || 'Failed to delete user link.');
                    }
                },
                error: function(xhr) {
                    toastError('Error', xhr.responseJSON?.message || 'Failed to delete user link.');
                },
                complete: function() {
                    setButtonLoading($btn, false);
                    setFormBusy(deleteUserLinkModal, false);
                }
            });
        });

        // Fix DataTables input/select focus styles
        const overrideFocusStyles = function() {
            $(this).css({
                'outline': 'none',
                'box-shadow': 'none',
                'border-color': 'gray'
            });
        };
        const restoreBlurStyles = function() {
            $(this).css('border-color', '');
        };
        const elementsToFix = $('.dataTables_filter input, .dataTables_length select');
        elementsToFix.on('focus keyup', overrideFocusStyles);
        elementsToFix.on('blur', restoreBlurStyles);
        elementsToFix.filter(':focus').each(overrideFocusStyles);
    });
</script>
@endpush