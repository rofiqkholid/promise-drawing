@extends('layouts.app')

@section('title', 'System Configuration')

@section('content')
<div class="w-full px-6 py-6 mx-auto">
    
    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">System Configuration</h1>
            <p class="text-gray-500 mt-2">Manage the global application identity, including system naming and versioning.</p>
        </div>
        
        {{-- PREVIEW BUTTON --}}
        <div>
            <button type="button" 
                onclick="window.open('{{ route('about') }}', '_blank')"
                class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 shadow-sm transition-all duration-200 group cursor-pointer">
                <i class="fa-solid fa-arrow-up-right-from-square mr-2 text-gray-400 group-hover:text-blue-600 transition-colors"></i> 
                View "About" Page
            </button>
        </div>
    </div>

    {{-- SUCCESS ALERT --}}
   @if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Changes Saved!',
            text: "{{ session('success') }}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    });
</script>
@endif

    {{-- FORM START --}}
    <form action="{{ route('setProfile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            
            {{-- CARD 1: GENERAL INFORMATION --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <span class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-4 shadow-sm">
                        <i class="fa-solid fa-sliders text-lg"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">General Information</h2>
                        <p class="text-xs text-gray-500">Primary details that will appear across headers and footers.</p>
                    </div>
                </div>
                
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Input: App Name --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Application Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <i class="fa-solid fa-laptop-code"></i>
                            </span>
                            <input type="text" name="app_name" value="{{ old('app_name', $profile->app_name) }}" 
                                class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all font-medium text-gray-700 placeholder-gray-300" 
                                placeholder="e.g. PROMISE System">
                        </div>
                        @error('app_name') <p class="text-xs text-red-500 mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p> @enderror
                    </div>

                    {{-- Input: Version --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Current Version <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <i class="fa-solid fa-code-branch"></i>
                            </span>
                            <input type="text" name="app_version" value="{{ old('app_version', $profile->app_version) }}" 
                                class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all font-medium text-gray-700 placeholder-gray-300" 
                                placeholder="e.g. v1.0.0">
                        </div>
                        @error('app_version') <p class="text-xs text-red-500 mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p> @enderror
                    </div>

                    {{-- Input: Description --}}
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">System Description</label>
                        <textarea name="app_description" rows="3" 
                            class="block w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm leading-relaxed text-gray-600 placeholder-gray-300 resize-none"
                            placeholder="Briefly explain the primary purpose of this system...">{{ old('app_description', $profile->app_description) }}</textarea>
                        <p class="text-xs text-gray-400 text-right">This will be displayed on the About > Overview section.</p>
                    </div>
                </div>
            </div>

            {{-- CARD 2: IDENTITY & PHILOSOPHY --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <span class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-4 shadow-sm">
                        <i class="fa-solid fa-quote-left text-lg"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Identity & Philosophy</h2>
                        <p class="text-xs text-gray-500">The meaning behind the application's visual identity.</p>
                    </div>
                </div>
                
                <div class="p-8 space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Logo Philosophy</label>
                    <textarea name="logo_description" rows="3" 
                        class="block w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-100 focus:border-purple-500 outline-none transition-all text-sm leading-relaxed text-gray-600 placeholder-gray-300 resize-none"
                        placeholder="Describe the meaning behind the logo's shapes, colors, or symbols...">{{ old('logo_description', $profile->logo_description) }}</textarea>
                </div>
            </div>

            {{-- ACTION BUTTON --}}
            <div class="flex justify-end pt-4">
                <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-blue-200 hover:shadow-blue-300 transition-all transform hover:-translate-y-0.5 flex items-center justify-center">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> 
                    Save Configuration
                </button>
            </div>

        </div>
    </form>
</div>
@endsection