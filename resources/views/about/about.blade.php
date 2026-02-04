@extends('layouts.app')

@section('title', 'About System')

@section('content')
{{-- CHANGE 1: Using w-full for full width, removed max-w-5xl --}}
<div class="w-full px-6 py-6 mx-auto">

    {{-- HEADER SECTION --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">System Information</h1>
            <p class="text-gray-500 mt-2">Profiles, identities, and system guidelines for Drawing Management.</p>
        </div>
        <!-- <div class="hidden md:block text-right">
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100">
                <i class="fa-solid fa-circle-check mr-2"></i> System Active
            </span>
        </div> -->
    </div>

    {{-- CARD CONTAINER --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[500px]">

        {{-- TAB NAVIGATION --}}
        <div class="border-b border-gray-200 bg-gray-50/50">
            <nav class="flex -mb-px px-6 gap-8" aria-label="Tabs">
                <button type="button"
                    class="tab-btn group inline-flex items-center py-5 px-1 border-b-2 font-medium text-sm transition-all duration-200 border-blue-600 text-blue-600"
                    onclick="showTab('overview', this)">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                    Overview
                </button>

                <button type="button"
                    class="tab-btn group inline-flex items-center py-5 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200"
                    onclick="showTab('logo', this)">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center mr-3 group-hover:bg-gray-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-image"></i>
                    </span>
                    Logo & Identity
                </button>

                <button type="button"
                    class="tab-btn group inline-flex items-center py-5 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200"
                    onclick="showTab('help', this)">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center mr-3 group-hover:bg-gray-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-life-ring"></i>
                    </span>
                    Help Center
                </button>
            </nav>
        </div>

        {{-- CONTENT AREA --}}
        <div class="p-8">

            {{-- 1. SKELETON LOADING --}}
            <div id="about-loading" class="animate-pulse space-y-6 w-full">
                <div class="flex items-center space-x-4">
                    <div class="h-12 w-12 bg-gray-200 rounded-full"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-48"></div>
                        <div class="h-3 bg-gray-200 rounded w-32"></div>
                    </div>
                </div>
                <div class="h-4 bg-gray-200 rounded w-full"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="pt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="h-32 bg-gray-200 rounded col-span-3"></div>
                    <div class="h-32 bg-gray-200 rounded col-span-1"></div>
                </div>
            </div>

            {{-- 2. ERROR STATE --}}
            <div id="error-message" class="hidden flex flex-col items-center justify-center text-center py-10 h-full">
                <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Failed to Load Data</h3>
                <p class="text-gray-500 max-w-md mx-auto mt-2">An error occurred while fetching the system profile. Please check your connection.</p>
                <button onclick="window.location.reload()" class="mt-6 px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 text-sm">
                    Refresh Page
                </button>
            </div>

            {{-- 3. CONTENT TABS --}}

            {{-- TAB: OVERVIEW --}}
            <div id="overview" class="tab-content hidden opacity-0 transition-opacity duration-300">
                {{-- CHANGE 2: Responsive grid (xl:grid-cols-4) --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-8">

                    {{-- Left Side: Main Content --}}
                    <div class="lg:col-span-2 xl:col-span-3 space-y-8">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Application Name</label>
                            <h2 id="app-name" class="text-4xl font-extrabold text-gray-800 mt-2"></h2>
                        </div>

                        <div class="prose max-w-none">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 block border-b pb-2">System Description</label>
                            <p id="app-description" class="text-gray-600 leading-loose text-lg whitespace-pre-line"></p>
                        </div>
                    </div>

                    {{-- Right Side: Technical Info --}}
                    <div class="xl:col-span-1">
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 shadow-sm sticky top-6">
                            <h4 class="font-bold text-gray-800 mb-6 flex items-center text-lg">
                                <i class="fa-solid fa-circle-info text-blue-500 mr-2"></i> Technical Info
                            </h4>
                            <div class="space-y-6">
                                <div>
                                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold block">Current Version</span>
                                    <span id="app-version" class="inline-flex items-center px-3 py-1 rounded text-base font-medium bg-blue-100 text-blue-800 mt-1 border border-blue-200">
                                        v1.0.0
                                    </span>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold block">Last Updated</span>
                                    <span id="last-updated" class="text-gray-800 font-medium text-base">
                                        -
                                    </span>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold block">Developer</span>
                                    <div class="flex items-center mt-1">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold mr-2">ICT</div>
                                        <span class="text-gray-800 font-medium text-base">ICT Dept</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: LOGO --}}
            <div id="logo" class="tab-content hidden opacity-0 transition-opacity duration-300">
                <div class="flex flex-col items-center justify-center py-12 min-h-[400px]">
                    <div id="logo-wrapper" class="relative group mb-10">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        <div class="relative bg-white p-12 rounded-xl border border-gray-100 shadow-lg">
                            <img src="{{ asset('assets/image/logo-promise.png') }}" alt="Logo" class="h-20 w-20 object-contain flex-shrink-0">
                        </div>
                    </div>

                    <div class="max-w-3xl text-center">
                        <h4 class="text-xl font-bold text-gray-800 mb-4">Philosophy & Identity</h4>
                        <div class="w-24 h-1.5 bg-blue-500 mx-auto rounded-full mb-6"></div>
                        <p id="logo-description" class="text-gray-600 italic leading-relaxed text-lg"></p>
                    </div>
                </div>
            </div>

            {{-- TAB: HELP --}}
<div id="help" class="tab-content hidden opacity-0 transition-opacity duration-300">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        {{-- 1. Upload Drawing --}}
        <div onclick="openVideoModal('upload', 'https://www.youtube.com/embed/LINK_VIDEO_1')" 
             class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-all duration-300 cursor-pointer bg-white group hover:-translate-y-1">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-2">Upload Drawing</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Guide on how to upload technical documents (DWG/PDF) to the central server with full metadata.</p>
        </div>

        {{-- 2. Approval Flow --}}
        <div onclick="openVideoModal('approval', 'https://www.youtube.com/embed/LINK_VIDEO_2')" 
             class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-all duration-300 cursor-pointer bg-white group hover:-translate-y-1">
            <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-green-600 group-hover:text-white transition-colors shadow-sm">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-2">Approval Flow</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Multi-level document approval workflow from Engineer to Manager before official release.</p>
        </div>

        {{-- 3. Versioning Control --}}
        <div onclick="openVideoModal('versioning', 'https://www.youtube.com/embed/LINK_VIDEO_3')" 
             class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-all duration-300 cursor-pointer bg-white group hover:-translate-y-1">
            <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-orange-600 group-hover:text-white transition-colors shadow-sm">
                <i class="fa-solid fa-code-branch"></i>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-2">Versioning Control</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Automatic revision tracking to ensure you are always working with the latest drawing version.</p>
        </div>

        {{-- 4. Distribution --}}
        <div onclick="openVideoModal('distribution', 'https://www.youtube.com/embed/LINK_VIDEO_4')" 
             class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-all duration-300 cursor-pointer bg-white group hover:-translate-y-1">
            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors shadow-sm">
                <i class="fa-solid fa-download"></i>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-2">Distribution</h3>
            <p class="text-gray-500 text-sm leading-relaxed">Secure download mechanisms and history logs for sharing documents with vendors.</p>
        </div>

    </div>
</div>
        </div>
    </div>
</div>
<div id="videoModal" class="fixed inset-0 z-[99] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-7xl overflow-hidden shadow-2xl relative flex flex-col md:flex-row h-auto md:h-[80vh]">
        
        <div class="w-full md:w-1/4 p-8 border-b md:border-b-0 md:border-r border-gray-100 overflow-y-auto bg-gray-50">
            <h3 id="modalTitle" class="text-2xl font-extrabold text-gray-800 mb-6 tracking-tight">Tutorial</h3>
            <div id="stepContent" class="text-gray-600 space-y-4 leading-relaxed text-base">
                </div>
        </div>
        
        <div class="w-full md:w-3/4 flex flex-col bg-black relative">
            <button onclick="closeVideoModal()" class="absolute top-4 right-4 z-10 w-12 h-12 bg-white/10 hover:bg-white/30 text-white rounded-full flex items-center justify-center transition-all backdrop-blur-md">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
            
            <div class="flex-grow w-full h-full">
                <iframe id="tutorialVideo" class="w-full h-full" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
            
            <div class="p-4 bg-white border-t flex justify-end items-center gap-4">
                <span class="text-sm text-gray-400 mr-auto ml-2 hidden md:block italic">Press Esc to close</span>
                <button onclick="closeVideoModal()" class="px-8 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition-all shadow-lg active:scale-95">
                    Close Tutorial
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // --- 1. FUNGSI UNTUK MODAL VIDEO (BARU) ---
   // Data teks untuk panduan sebelah kiri
    const tutorialData = {
    'upload': {
        title: 'Drawing Upload Guide',
        steps: `
            <div class="space-y-3">
                <p class="font-semibold text-blue-600 text-sm uppercase tracking-wider">Step-by-step Instructions:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                    <li>Prepare technical files in <b>DWG</b> or <b>PDF</b> format.</li>
                    <li>Ensure the file size does not exceed the <b>50MB</b> limit.</li>
                    <li>Click the upload button and complete the metadata (Project, Discipline, Date).</li>
                    <li>The system will validate the file before it is stored in the central server.</li>
                </ul>
            </div>`
    },
    'approval': {
        title: 'Approval Workflow Guide',
        steps: `
            <div class="space-y-3">
                <p class="font-semibold text-green-600 text-sm uppercase tracking-wider">Verification Process:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                    <li>Uploaded documents will enter the <b>Engineer Review</b> queue.</li>
                    <li>Once reviewed, the status will change to <b>Pending Manager</b>.</li>
                    <li>The Manager will provide a <b>Digital Signature</b> for final approval.</li>
                    <li>Automatic notifications will be sent to all relevant stakeholders.</li>
                </ul>
            </div>`
    },
    'versioning': {
        title: 'Versioning Control Guide',
        steps: `
            <div class="space-y-3">
                <p class="font-semibold text-orange-600 text-sm uppercase tracking-wider">Revision Management:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                    <li>Use consistent file naming for automatic version detection.</li>
                    <li>Previous versions will be automatically moved to <b>Archives</b>.</li>
                    <li>Every change is recorded in the system's <b>Change Log</b>.</li>
                    <li>Always ensure you are retrieving data from the <b>Latest</b> status.</li>
                </ul>
            </div>`
    },
    'distribution': {
        title: 'Distribution Guide',
        steps: `
            <div class="space-y-3">
                <p class="font-semibold text-purple-600 text-sm uppercase tracking-wider">Vendor Distribution:</p>
                <ul class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                    <li>Select documents that have reached <b>Released</b> status.</li>
                    <li>Use the 'Share' feature to generate a secure download link.</li>
                    <li>Links can be secured with a <b>Password</b> and expiration date.</li>
                    <li>Check the <b>Access Log</b> to track who has downloaded the files.</li>
                </ul>
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
        
        // Clean URL and add professional YouTube parameters
        const cleanUrl = videoUrl.split('?')[0];
        iframe.src = `${cleanUrl}?autoplay=1&rel=0&modestbranding=1&showinfo=0`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    } else {
        console.warn("Tutorial data not found for type: " + type);
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

    // Tambahkan event listener untuk menutup dengan tombol Escape jika belum ada
document.addEventListener('keydown', function(e) {
    if (e.key === "Escape") closeVideoModal();
});



    // --- 2. FUNGSI TAB & FETCH DATA (KODE LAMA ANDA) ---
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

                // Populate Data
                document.getElementById('app-name').innerText = data.app_name || '-';
                document.getElementById('app-description').innerText = data.app_description || '-';
                document.getElementById('app-version').innerText = data.app_version || 'v1.0';
                document.getElementById('last-updated').innerText = data.updated_at ? new Date(data.updated_at).toLocaleString('en-US') : '-';
                document.getElementById('logo-description').innerText = data.logo_description || 'No philosophy description available.';

                // Show Default Tab
                const overviewTab = document.getElementById('overview');
                overviewTab.classList.remove('hidden');
                setTimeout(() => overviewTab.classList.remove('opacity-0'), 50);
            })
            .catch(err => {
                console.error('Error fetching profile:', err);
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
            });
    });

    function showTab(tabId, btnElement) {
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden', 'opacity-0');
        });

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-600', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
            const iconBox = btn.querySelector('span');
            if (iconBox) {
                iconBox.classList.remove('bg-blue-100', 'text-blue-600');
                iconBox.classList.add('bg-gray-100', 'text-gray-500');
            }
        });

        const activeContent = document.getElementById(tabId);
        activeContent.classList.remove('hidden');
        void activeContent.offsetWidth; 
        activeContent.classList.remove('opacity-0');

        if (btnElement) {
            btnElement.classList.remove('border-transparent', 'text-gray-500');
            btnElement.classList.add('border-blue-600', 'text-blue-600');
            const activeIconBox = btnElement.querySelector('span');
            if (activeIconBox) {
                activeIconBox.classList.remove('bg-gray-100', 'text-gray-500');
                activeIconBox.classList.add('bg-blue-100', 'text-blue-600');
            }
        }
    }
</script>
@endpush