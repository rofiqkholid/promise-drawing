@extends('layouts.app')

@section('title', 'About System')

@section('content')
{{-- MAIN WRAPPER: Improved spacing and dark mode contrast --}}
<div class="w-full px-4 sm:px-6 lg:px-8 py-4 sm:py-8 mx-auto dark:text-gray-100">

    {{-- HEADER SECTION: Logo-based Gradient (Dark Blue to Slate) --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 sm:p-8 mb-6 sm:mb-8 shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    About the System
                </h1>
                <p class="text-slate-300 mt-2 text-base sm:text-lg max-w-2xl">
                    Discover our system’s core purpose, the philosophy behind our visual identity, and comprehensive guides to help you navigate every feature.
                </p>
            </div>
            <div class="hidden md:block">
                <i class="fa-solid fa-circle-info text-6xl text-white/10"></i>
            </div>
        </div>
        
        {{-- Decorative circles --}}
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 rounded-full bg-sky-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 rounded-full bg-blue-500/20 blur-3xl"></div>
    </div>

    {{-- CARD CONTAINER --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden min-h-[600px] flex flex-col">

        {{-- TAB NAVIGATION: Underline Style with Logo Colors --}}
        <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky top-0 z-20 overflow-x-auto no-scrollbar">
            <nav class="flex px-4 sm:px-6 gap-6 sm:gap-8 min-w-max" aria-label="Tabs">
                {{-- 
                    NOTE: Warna disesuaikan dengan Logo (Blue/Sky/Slate).
                --}}
                {{-- OVERVIEW TAB --}}
                <button type="button"
                    class="tab-btn group inline-flex items-center py-4 px-1 border-b-2 border-blue-700 font-medium text-sm text-blue-800 dark:text-blue-400 transition-all duration-200 focus:outline-none"
                    onclick="showTab('overview', this)">
                    <span class="mr-3 flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                    Overview
                </button>

                {{-- LOGO TAB --}}
                <button type="button"
                    class="tab-btn group inline-flex items-center py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600 transition-all duration-200 focus:outline-none"
                    onclick="showTab('logo', this)">
                    <span class="mr-3 flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 dark:bg-slate-700/50 dark:text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-slate-700 transition-colors">
                        <i class="fa-solid fa-image"></i>
                    </span>
                    Logo & Identity
                </button>

                {{-- HELP TAB --}}
                <button type="button"
                    class="tab-btn group inline-flex items-center py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600 transition-all duration-200 focus:outline-none"
                    onclick="showTab('help', this)">
                    <span class="mr-3 flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 dark:bg-slate-700/50 dark:text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-slate-700 transition-colors">
                        <i class="fa-solid fa-life-ring"></i>
                    </span>
                    Help Center
                </button>
            </nav>
        </div>

        {{-- CONTENT AREA --}}
        <div class="p-4 sm:p-8">

            {{-- 1. SKELETON LOADING (Updated colors for dark mode) --}}
            <div id="about-loading" class="animate-pulse space-y-6 w-full">
                <div class="flex items-center space-x-4">
                    <div class="h-12 w-12 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-48"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                    </div>
                </div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                <div class="pt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded col-span-3"></div>
                    <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded col-span-1"></div>
                </div>
            </div>

            {{-- 2. ERROR STATE --}}
            <div id="error-message" class="hidden flex flex-col items-center justify-center text-center py-10 h-full">
                <div class="w-16 h-16 bg-red-100 text-red-500 dark:bg-red-900/20 dark:text-red-400 rounded-full flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Failed to Load Data</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-2">An error occurred while fetching the system profile. Please check your connection.</p>
                <button onclick="window.location.reload()" class="mt-6 px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-sm">
                    Refresh Page
                </button>
            </div>

            {{-- 3. CONTENT TABS --}}

            {{-- TAB: OVERVIEW --}}
            <div id="overview" class="tab-content hidden opacity-0 transition-all duration-500 ease-in-out">
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 sm:gap-8 p-0 sm:p-6">

                    {{-- Left Side: Main Content --}}
                    <div class="xl:col-span-8 space-y-4 sm:space-y-8">
                        {{-- App Name Card (Blue Theme) --}}
                        <div class="bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 sm:p-8 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">

                            <label class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-widest mb-2 block">
                                Application Name
                            </label>
                            <h2 id="app-name" class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 dark:text-white mt-1 mb-4 relative z-10"></h2>
                            <div class="h-1 w-20 bg-blue-700 rounded-full"></div>
                        </div>

                        {{-- Description Card (Enhanced) --}}
                        <div class="relative bg-white dark:bg-gray-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 sm:p-10 shadow-lg overflow-hidden group">
                            {{-- Decorative Background --}}
                            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-blue-50 dark:bg-blue-900/10 rounded-full blur-2xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-colors duration-500"></div>
                            <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-32 h-32 bg-slate-50 dark:bg-slate-800/50 rounded-full blur-2xl"></div>
                            
                            {{-- Watermark Icon --}}


                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                    <span class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                                        <i class="fa-solid fa-quote-left text-sm"></i>
                                    </span>
                                    <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                        System Description
                                    </h3>
                                </div>

                                <div class="prose prose-lg dark:prose-invert max-w-none">
                                    <p id="app-description" class="text-slate-700 dark:text-slate-300 leading-relaxed text-base sm:text-lg whitespace-pre-line">
                                        {{-- Content filled by JS --}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Side: Technical Info --}}
                    <div class="xl:col-span-4">
                        {{-- Sticky Box --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-0 border border-slate-200 dark:border-slate-700 shadow-sm sticky top-24 overflow-hidden">
                            <div class="bg-slate-50 dark:bg-gray-900/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                                <h4 class="font-bold text-slate-800 dark:text-white flex items-center text-lg">
                                    <i class="fa-solid fa-server text-blue-700 mr-3"></i> Technical Details
                                </h4>
                            </div>
                            
                            <div class="p-6 space-y-6">
                                <div class="flex items-center justify-between group">
                                    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">Current Version</span>
                                    <span id="app-version" class="px-3 py-1 rounded-full text-sm font-bold bg-blue-50 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        v1.0.0
                                    </span>
                                </div>
                                
                                <div class="border-t border-dashed border-slate-200 dark:border-slate-700 pt-4 flex items-center justify-between">
                                    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">Last Updated</span>
                                    <span id="last-updated" class="text-slate-800 dark:text-slate-200 font-semibold text-sm">
                                        -
                                    </span>
                                </div>

                                <div class="border-t border-dashed border-slate-200 dark:border-slate-700 pt-4">
                                    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium block mb-3">Developed By</span>
                                    <div class="flex items-center p-3 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-100 dark:border-slate-700">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white text-xs font-bold mr-3 shadow-lg ring-2 ring-white dark:ring-slate-600">
                                            ICT
                                        </div>
                                        <div>
                                            <div class="text-slate-900 dark:text-white font-bold text-sm">ICT Dept</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Internal Development</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: LOGO --}}
            {{-- TAB: LOGO --}}
            {{-- TAB: LOGO --}}
            {{-- TAB: LOGO --}}
            <div id="logo" class="tab-content hidden opacity-0 transition-all duration-500 ease-in-out">
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 sm:gap-8 p-0 sm:p-6">

                    {{-- LEFT: LOGO DISPLAY (Takes 4 columns - Smaller) --}}
                    <div class="xl:col-span-4 flex justify-center items-start">
                        <div class="relative group w-full max-w-sm mx-auto">

                            
                            <div class="relative w-full aspect-square bg-white dark:bg-gray-800 rounded-[1.8rem] flex items-center justify-center border border-slate-200 dark:border-gray-700 p-6 sm:p-8">
                                
                                {{-- Grid Pattern Background --}}
                                <div class="absolute inset-0 opacity-[0.05] dark:opacity-[0.1]" 
                                     style="background-image: radial-gradient(#64748b 1px, transparent 1px); background-size: 20px 20px;">
                                </div>

                                <img
                                    src="{{ asset('assets/image/logo-promise.png') }}"
                                    alt="Logo"
                                    class="w-full h-full object-contain relative z-10 transform transition-transform duration-500 group-hover:scale-105" />
                            </div>
                        </div>
                    </div>

                    <div class="xl:col-span-8">
                        <div class="relative bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-2xl p-6 sm:p-10 overflow-hidden group h-full">
                            {{-- Decorative Background --}}
                            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-blue-50 dark:bg-blue-900/10 rounded-full blur-2xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/20 transition-colors duration-500"></div>
                            <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-32 h-32 bg-slate-50 dark:bg-slate-800/50 rounded-full blur-2xl"></div>
                            
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                    <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                        Philosophy & Identity
                                    </h3>
                                </div>

                                <div class="prose prose-lg dark:prose-invert max-w-none">
                                    <p id="logo-description" class="text-slate-700 dark:text-slate-300 leading-relaxed text-base sm:text-lg whitespace-pre-line">
                                        {{-- Content filled by JS --}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- TAB: HELP --}}
            <div id="help" class="tab-content hidden opacity-0 transition-all duration-500 ease-in-out">
                <div class="p-0 sm:p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                        
                        {{-- Helper untuk card --}}
                        @php
                            $cardClasses = "group relative bg-white dark:bg-gray-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden";
                            $iconBaseClasses = "w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6 transition-transform duration-300 group-hover:scale-110 shadow-sm";
                        @endphp

                        {{-- 1. Upload Drawing (Dark Blue) --}}
                        <div onclick="openVideoModal('upload', 'https://www.youtube.com/embed/upload')" class="{{ $cardClasses }}">

                            <div class="{{ $iconBaseClasses }} bg-blue-50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-3 group-hover:text-blue-800 dark:group-hover:text-blue-300 transition-colors">Upload Drawing</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed relative z-10">
                                Guide on how to upload technical documents (DWG/PDF) to the central server with full metadata.
                            </p>
                        </div>

                        {{-- 2. Approval Flow (Sky Blue) --}}
                        <div onclick="openVideoModal('approval', 'https://www.youtube.com/embed/approval')" class="{{ $cardClasses }}">
 
                            <div class="{{ $iconBaseClasses }} bg-sky-50 text-sky-600 dark:bg-sky-900/20 dark:text-sky-300">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Approval Flow</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed relative z-10">
                                Multi-level document approval workflow from Engineer to Manager before official release.
                            </p>
                        </div>

                        {{-- 3. Versioning Control (Slate/Silver) --}}
                        <div onclick="openVideoModal('versioning', 'https://www.youtube.com/embed/LINK_VIDEO_3')" class="{{ $cardClasses }}">
 
                            <div class="{{ $iconBaseClasses }} bg-slate-50 text-slate-600 dark:bg-slate-700/30 dark:text-slate-300">
                                <i class="fa-solid fa-code-branch"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-3 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors">Versioning Control</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed relative z-10">
                                Automatic revision tracking to ensure you are always working with the latest drawing version.
                            </p>
                        </div>

                        {{-- 4. Distribution (Cyan) --}}
                        <div onclick="openVideoModal('distribution', 'https://www.youtube.com/embed/1ET7h2JAeEs?si=1ufAYP4x_KrI_Nvq')" class="{{ $cardClasses }}">
 
                            <div class="{{ $iconBaseClasses }} bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-300">
                                <i class="fa-solid fa-share-nodes"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-3 group-hover:text-cyan-600 dark:group-hover:text-cyan-300 transition-colors">Share to Supplier</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed relative z-10">
                                Securely share document packages with suppliers via generated links.
                            </p>
                        </div>

                        {{-- 5. Download (Indigo) --}}
                        <div onclick="openVideoModal('download', 'https://www.youtube.com/embed/C-973OZMfnM?si=sh3XGMyPQIhcq8z94')" class="{{ $cardClasses }}">
 
                            <div class="{{ $iconBaseClasses }} bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300">
                                <i class="fa-solid fa-download"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors">Download</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed relative z-10">
                                Securely download documents with complete access history tracking.
                            </p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL VIDEO --}}
<div id="videoModal" class="fixed inset-0 z-[99] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    {{-- Modal Container: bg-white -> dark:bg-gray-800 --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-7xl overflow-hidden shadow-2xl relative flex flex-col-reverse md:flex-row h-auto md:h-[80vh]">

        {{-- Sidebar: bg-gray-50 -> dark:bg-gray-900, border-gray-100 -> dark:border-gray-700 --}}
        <div class="w-full md:w-1/4 p-8 border-t md:border-t-0 md:border-r border-gray-100 dark:border-gray-700 overflow-y-auto bg-gray-50 dark:bg-gray-900 max-h-[40vh] md:max-h-full md:h-auto">
            <h3 id="modalTitle" class="text-2xl font-extrabold text-gray-800 dark:text-white mb-6 tracking-tight">Tutorial</h3>
            <div id="stepContent" class="text-gray-600 dark:text-gray-300 space-y-4 leading-relaxed text-base">
            </div>
        </div>

        <div class="w-full md:w-3/4 flex flex-col bg-black relative aspect-video md:aspect-auto md:h-full">
            <button onclick="closeVideoModal()" class="absolute top-4 right-4 z-10 w-12 h-12 bg-white/10 hover:bg-white/30 text-white rounded-full flex items-center justify-center transition-all backdrop-blur-md">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>

            <div class="flex-grow w-full h-full">
                <iframe id="tutorialVideo" class="w-full h-full" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>

            {{-- Footer Modal: bg-white -> dark:bg-gray-800 --}}
            <div class="p-4 bg-white dark:bg-gray-800 border-t dark:border-gray-700 flex justify-end items-center gap-4">
                <span class="text-sm text-gray-400 mr-auto ml-2 hidden md:block italic">Press Esc to close</span>
                <button onclick="closeVideoModal()" class="px-8 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition-all shadow-lg active:scale-95">
                    Close Tutorial
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // --- DATA TUTORIAL (Teks diupdate sedikit di bagian class HTML string untuk support dark mode) ---
    // Note: Karena string HTML ini di-inject via JS, kita tambahkan class dark:text... langsung di sini
    const tutorialData = {
        'upload': {
            title: 'Drawing Upload Guide',
            steps: `
            <div class="space-y-3">
                <p class="font-semibold text-blue-600 dark:text-blue-400 text-sm uppercase tracking-wider">Step-by-step Instructions:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li>Navigate to the Upload menu.</li>
                    <li>Click the <b>Upload New Drawing</b> button.</li>
                    <li>Carefully complete the upload form, especially when selecting whether the item is Finish Good or not.</li>
                    <li>Upload the file according to its category (for example, upload 2D files to the 2D category).</li>
                    <li>Click the <b>Save to Draft</b> button.</li>
                    <li>Click the <b>Request Approval</b> button.</li>
                </ul>
                <p class="font-semibold text-green-600 dark:text-green-400 text-sm uppercase tracking-wider">Proccess Completed</p>
            </div>`
        },
        'approval': {
            title: 'Approval Workflow Guide',
            steps: `
            <div class="space-y-3">
                <p class="font-semibold text-green-600 dark:text-green-400 text-sm uppercase tracking-wider">Verification Process:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li>Navigate to the <b>Approval</b> menu.</li>
                    <li>Search for documents with <b>Waiting</b> status that match your assigned role.</li>
                    <li>Click the document row that you want to review or approve.</li>
                    <li>Click the file to preview its contents.</li>
                    <li>Click <b>Approve</b> or <b>Reject</b> according to your decision.</li>
                </ul>
                <p class="font-semibold text-green-600 dark:text-green-400 text-sm uppercase tracking-wider">Proccess Completed</p>
            </div>`
        },
        'versioning': {
            title: 'Versioning Control Guide',
            steps: `
            <div class="space-y-3">
                <p class="font-semibold text-orange-600 dark:text-orange-400 text-sm uppercase tracking-wider">Revision Management:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li>Use consistent file naming.</li>
                    <li>Previous versions move to <b>Archives</b>.</li>
                    <li>Changes recorded in <b>Change Log</b>.</li>
                    <li>Always use the <b>Latest</b> status data.</li>
                </ul>
            </div>`
        },
        'distribution': {
            title: 'Distribution Guide',
            steps: `
            <div class="space-y-3">
                <p class="font-semibold text-purple-600 dark:text-purple-400 text-sm uppercase tracking-wider">Vendor Distribution:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li>Select <b>Released</b> documents.</li>
                    <li>Use 'Share' to generate link.</li>
                    <li>Secure with <b>Password</b> if needed.</li>
                    <li>Check <b>Access Log</b> for tracking.</li>
                </ul>
            </div>`
        },
        'download': {
            title: 'Download Guide',
            steps: `
            <div class="space-y-3">
                <p class="font-semibold text-purple-600 dark:text-purple-400 text-sm uppercase tracking-wider">Step-by-step Instructions:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li>Navigate to <b>Download</b> menu.</li>
                    <li>Select document version.</li>
                    <li>Click <b>Download.</b></li>
                </ul>
                <p class="font-semibold text-purple-600 dark:text-purple-400 text-sm uppercase tracking-wider">End of Guide</p>
            </div>`
        }
    };

    function openVideoModal(type, videoUrl) {
        const modal = document.getElementById('videoModal');
        const iframe = document.getElementById('tutorialVideo');
        const titleEl = document.getElementById('modalTitle');
        const contentEl = document.getElementById('stepContent');

        const data = tutorialData[type];

        if (data) {
            titleEl.innerText = data.title;
            contentEl.innerHTML = data.steps;
            const cleanUrl = videoUrl.split('?')[0];
            iframe.src = `${cleanUrl}?autoplay=1&rel=0&modestbranding=1&showinfo=0&iv_load_policy=3`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeVideoModal() {
        const modal = document.getElementById('videoModal');
        const iframe = document.getElementById('tutorialVideo');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        iframe.src = "";
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === "Escape") closeVideoModal();
    });

    // --- LOGIKA TAB & FETCH DATA (DIPERBARUI UNTUK DARK MODE) ---
    document.addEventListener('DOMContentLoaded', function() {
        const loadingEl = document.getElementById('about-loading');
        const errorEl = document.getElementById('error-message');

        fetch("{{ route('about.profile') }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                loadingEl.classList.add('hidden');
                if (!data) {
                    errorEl.classList.remove('hidden');
                    return;
                }
                document.getElementById('app-name').innerText = data.app_name || '-';
                document.getElementById('app-description').innerText = data.app_description || '-';
                document.getElementById('app-version').innerText = data.app_version || 'v1.0';
                document.getElementById('last-updated').innerText = data.updated_at ? new Date(data.updated_at).toLocaleString('en-US') : '-';
                document.getElementById('logo-description').innerText = data.logo_description || 'No philosophy description available.';

                const overviewTab = document.getElementById('overview');
                overviewTab.classList.remove('hidden');
                setTimeout(() => overviewTab.classList.remove('opacity-0'), 50);
            })
            .catch(err => {
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
            });
    });

    // FUNGSI INI DIUPDATE PENTING UNTUK GAYA UNDERLINE & DARK MODE
    function showTab(tabId, btnElement) {
        // 1. Sembunyikan semua konten tab
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden', 'opacity-0');
        });

        // 2. Reset semua tombol ke state "Tidak Aktif"
        document.querySelectorAll('.tab-btn').forEach(btn => {
            // Style Button Tidak Aktif (Underline Style)
            // Hapus style Aktif
            btn.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-400');
            
            // Tambah style Tidak Aktif
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:text-gray-400', 'dark:hover:text-gray-200', 'dark:hover:border-gray-600');

            // Reset Icon
            const iconBox = btn.querySelector('span');
            if (iconBox) {
                // Hapus style Icon Aktif
                iconBox.classList.remove('bg-blue-50', 'text-blue-600', 'dark:bg-blue-900/30', 'dark:text-blue-300');
                // Tambah style Icon Tidak Aktif
                iconBox.classList.add('bg-gray-50', 'text-gray-500', 'dark:bg-gray-700/50', 'dark:text-gray-400');
            }
        });

        // 3. Tampilkan konten yang dipilih
        const activeContent = document.getElementById(tabId);
        if (activeContent) {
            activeContent.classList.remove('hidden');
            // Force reflow
            void activeContent.offsetWidth; 
            activeContent.classList.remove('opacity-0');
        }

        // 4. Set tombol yang diklik ke state "Aktif"
        if (btnElement) {
            // Hapus style Tidak Aktif
            btnElement.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:text-gray-400', 'dark:hover:text-gray-200', 'dark:hover:border-gray-600');
            
            // Tambah style Aktif
            btnElement.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400');

            // Set Icon Aktif
            const activeIconBox = btnElement.querySelector('span');
            if (activeIconBox) {
                activeIconBox.classList.remove('bg-gray-50', 'text-gray-500', 'dark:bg-gray-700/50', 'dark:text-gray-400');
                activeIconBox.classList.add('bg-blue-50', 'text-blue-600', 'dark:bg-blue-900/30', 'dark:text-blue-300');
            }
        }
    }

    // Initialize first tab state properly on load
    document.addEventListener('DOMContentLoaded', function() {
        const firstBtn = document.querySelector('.tab-btn');
        if (firstBtn) {
            // Kita panggil showTab secara manual untuk tab pertama (Overview)
            // agar visualnya sinkron dengan konten yang ditampilkan
            showTab('overview', firstBtn);
        }
    });
</script>
@endpush