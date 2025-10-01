@extends('layouts.app')
@section('title', 'User Maintenance - PROMISE')
@section('header-title', 'User Maintenance')

@section('content')

{{-- Wrapper utama --}}
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100" x-data="{ modalOpen: false, selectedUser: {} }">

    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">User Management</h2>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add, view, and manage application users.</p>
        </div>
    </div>

    {{-- Form Tambah User --}}
    <div class="mt-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3 mb-6">
            <i class="fa-solid fa-user-plus text-xl text-blue-500"></i>
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Add New User</h3>
        </div>
        <form action="#" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Input Fields with Icons --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="name" id="name" placeholder="e.g., John Doe" class="block w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" placeholder="e.g., user@example.com" class="block w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                </div>
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-id-card text-gray-400"></i>
                        </div>
                        <input type="text" name="nik" id="nik" placeholder="e.g., 123456789" class="block w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" placeholder="Enter password" class="block w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                </div>
                <div>
                    <label for="role_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Group</label>
                    <select id="role_group" name="role_group" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <option>Select Group</option>
                        <option>Engineering</option>
                        <option>Production</option>
                        <option>Administrator</option>
                    </select>
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select id="role" name="role" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <option>Select Role</option>
                        <option>Designer</option>
                        <option>Checker</option>
                        <option>Approver</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save User
                </button>
            </div>
        </form>
    </div>

    {{-- Tabel User --}}
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Registered Users</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIK</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role Group</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    {{-- Table Row 1 --}}
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">Budi Santoso</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">budi.s@example.com</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">1234567890</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Engineering</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Designer</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/70 dark:text-green-300">
                                <i class="fa-solid fa-circle-check"></i> Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center gap-4">
                                <button @click="modalOpen = true; selectedUser = { id: 1, name: 'Budi Santoso', email: 'budi.s@example.com', nik: '1234567890', role_group: 'Engineering', role: 'Designer' }" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                                    <i class="fa-solid fa-pen-to-square fa-lg"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete">
                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    {{-- Table Row 2 --}}
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">Citra Lestari</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">citra.l@example.com</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">0987654321</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Production</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Approver</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                <i class="fa-solid fa-circle-xmark"></i> Inactive
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center gap-4">
                                <button @click="modalOpen = true; selectedUser = { id: 2, name: 'Citra Lestari', email: 'citra.l@example.com', nik: '0987654321', role_group: 'Production', role: 'Approver' }" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                                    <i class="fa-solid fa-pen-to-square fa-lg"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400" title="Delete">
                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit User --}}
    <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="modalOpen = false" class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-user-pen text-xl text-blue-500"></i>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                            Edit User
                        </h3>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row items-center gap-8">
                        <div class="flex-shrink-0 w-full sm:w-auto flex justify-center">
                            <div class="flex items-center justify-center h-28 w-28 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-circle-user fa-5x"></i>
                            </div>
                        </div>
                        <div class="flex-grow w-full">
                            {{-- Form di dalam modal mengikuti pola input dengan ikon --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fa-solid fa-user text-gray-400"></i></div>
                                        <input type="text" id="edit_name" x-model="selectedUser.name" class="block w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                                    </div>
                                </div>
                                <div>
                                    <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fa-solid fa-envelope text-gray-400"></i></div>
                                        <input type="email" id="edit_email" x-model="selectedUser.email" class="block w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                                    </div>
                                </div>
                                <div>
                                    <label for="edit_nik" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK</label>
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fa-solid fa-id-card text-gray-400"></i></div>
                                        <input type="text" id="edit_nik" x-model="selectedUser.nik" class="block w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                                    </div>
                                </div>
                                <div>
                                    <label for="edit_role_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Group</label>
                                    <select id="edit_role_group" x-model="selectedUser.role_group" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-md sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                                        <option>Engineering</option>
                                        <option>Production</option>
                                        <option>Administrator</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="edit_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                    <select id="edit_role" x-model="selectedUser.role" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-md sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                                        <option>Designer</option>
                                        <option>Checker</option>
                                        <option>Approver</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/80 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="modalOpen = false" class="w-full inline-flex items-center gap-2 justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 sm:w-auto sm:text-sm">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>
                    <button type="button" @click="modalOpen = false" class="w-full inline-flex items-center gap-2 justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 sm:w-auto sm:text-sm">
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection