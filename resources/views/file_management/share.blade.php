@extends('layouts.app')
@section('title', 'Share Packages - PROMISE')
@section('header-title', 'Share Packages')

@section('content')
<nav class="flex px-5 py-3 mb-3 text-gray-500 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">

        <li class="inline-flex items-center">
            <a href="{{ route('monitoring') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600">
                Monitoring
            </a>
        </li>

        <li aria-current="page">
            <div class="flex items-center">
                <span class="mx-1 text-gray-400">/</span>

                <span class="text-sm font-semibold text-blue-600 px-2.5 py-0.5 rounded-xs">
                    Share Packages
                </span>
            </div>
        </li>
    </ol>
</nav>

<div class="w-full p-3 sm:p-4 lg:p-6 bg-gray-50 dark:bg-gray-900 font-sans">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Share Packages</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Share and monitor package distribution.</p>
        </div>

        <button onclick="window.dispatchEvent(new CustomEvent('open-reshare-modal'))"
            class="relative group inline-flex items-center gap-2 px-6 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-xs transition-all focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 shrink-0">
            <i class="fa-solid fa-bell-concierge"></i>
            <span class="text-[13px] font-semibold">Request Monitoring</span>

            {{-- Notification Badge --}}
            <div id="reshare-badge" class="absolute -top-2 -right-2 hidden">
                <span class="flex h-5 w-5 items-center justify-center rounded-xs bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900 animate-bounce">
                    0
                </span>
            </div>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row gap-3">
        {{-- Sidebar History (Left) --}}
        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xs border border-gray-200 dark:border-gray-700 sticky top-24">
                <div class="flex items-center justify-between mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 bg-blue-500 rounded-xs"></div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-[13px]">Activity Log</h3>
                    </div>
                    <button onclick="loadHistory()" class="text-[13px] text-gray-400 hover:text-blue-500 transition-colors">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>

                <div class="max-h-[calc(100vh-320px)] overflow-y-auto custom-scrollbar pr-1 pl-2 pb-4">
                    <div id="shareHistoryList" class="relative border-l-2 border-gray-100 dark:border-gray-700 ml-2 space-y-6 pb-4 pt-1">
                        <div class="flex items-center justify-center py-10 opacity-30 -ml-2">
                            <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Content Segment (Right) --}}
        <main class="flex-1 min-w-0 space-y-6">
            {{-- KPI Row --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach([
                ['id' => 'totalShared', 'label' => 'Total Shared', 'icon' => 'fa-share-nodes', 'color' => 'blue', 'clickable' => false],
                ['id' => 'totalActive', 'label' => 'Active', 'icon' => 'fa-check-circle', 'color' => 'green', 'clickable' => false],
                ['id' => 'totalExpired', 'label' => 'Expired', 'icon' => 'fa-clock-rotate-left', 'color' => 'red', 'clickable' => false],
                ['id' => 'totalSuppliers', 'label' => 'Total Suppliers', 'icon' => 'fa-users-gear', 'color' => 'blue', 'clickable' => false]
                ] as $card)
                <div id="card-{{ $card['id'] }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-xs border border-gray-200 dark:border-gray-700 flex items-center gap-4 group hover:border-{{ $card['color'] }}-100 dark:hover:border-{{ $card['color'] }}-500/30 transition-all {{ $card['clickable'] ? 'cursor-pointer hover:' : '' }}"
                    @if($card['clickable'])
                    @click="window.dispatchEvent(new CustomEvent('open-reshare-modal'))"
                    @endif>
                    <div class="w-10 h-10 rounded-xs bg-{{ $card['color'] }}-100 dark:bg-{{ $card['color'] }}-900/30 flex items-center justify-center text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400 group-hover:scale-110 transition-transform">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-gray-400 dark:text-gray-500">{{ $card['label'] }}</p>
                        <div class="flex items-center gap-2">
                            <p id="{{ $card['id'] }}" class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none mt-1">0</p>
                            @if($card['clickable'])
                            <i class="fa-solid fa-arrow-right text-[10px] text-blue-500 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Filter Bar (Inside Right Segment) --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xs border border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-end gap-2 px-1">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[13px] font-semibold text-gray-400 mb-1.5 px-0.5">Search Package</label>
                        <div class="relative group">
                            <input type="text" id="custom-share-search"
                                class="block w-full pl-9 pr-4 py-2 bg-gray-50/50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-xs text-xs font-semibold focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all dark:text-gray-100 group-hover:border-blue-300 dark:group-hover:border-blue-500/50 placeholder:font-normal"
                                placeholder="ECN, Part No, or Model...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i id="share-search-icon" class="fa-solid fa-magnifying-glass text-gray-400 text-[10px]"></i>
                            </div>
                        </div>
                    </div>

                    @foreach([
                    ['id' => 'customer', 'label' => 'Customer', 'w' => 'w-32'],
                    ['id' => 'model', 'label' => 'Model', 'w' => 'w-36'],
                    ['id' => 'document-type', 'label' => 'Doc Type', 'w' => 'w-32'],
                    ['id' => 'status', 'label' => 'Status', 'w' => 'w-28']
                    ] as $f)
                    <div class="{{ $f['w'] }}">
                        <label class="block text-[13px] font-semibold text-gray-400 mb-1.5 px-0.5">{{ $f['label'] }}</label>
                        <select id="{{ $f['id'] }}" class="js-filter w-full"></select>
                    </div>
                    @endforeach

                    <div class="flex-shrink-0 mb-0.5">
                        <button id="btnResetFilters" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xs transition-all h-[34px] w-[34px] flex items-center justify-center border border-transparent hover:border-blue-100 dark:hover:border-blue-500/30" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div>
                    <table id="approvalTable" class="w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 font-semibold text-left">
                            <tr>
                                <th class="px-5 py-4 w-12 text-center">No</th>
                                <th class="px-5 py-4 min-w-[120px]">Package Info</th>
                                <th class="px-5 py-4 w-32 border-l border-gray-100/50 dark:border-gray-700/50">Rev / ECN</th>
                                <th class="px-5 py-4 w-36 border-l border-gray-100/50 dark:border-gray-700/50">Category</th>
                                <th class="px-5 py-4 w-24 text-center border-l border-gray-100/50 dark:border-gray-700/50">Status</th>
                                <th class="px-5 py-4 w-46 text-center border-l border-gray-100/50 dark:border-gray-700/50">Receive At</th>
                                <th class="px-5 py-4 w-32 border-l border-gray-100/50 dark:border-gray-700/50">Recipients</th>
                                <th class="px-5 py-4 w-20 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <x-files.share-modal />
    <x-files.download-zip-modal />

    {{-- Recipients Detail Modal (Original) --}}
    <div id="shareDetailsModal" class="fixed inset-0 z-[150] hidden" style="background: rgba(15, 23, 42, 0.6);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xs w-full max-w-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-2xl">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i class="fa-solid fa-users-viewfinder text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Share Distribution</h3>
                            <p class="text-[13px] text-gray-400 font-semibold">Detail of recipients</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close-share-details text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-2">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div id="shareDetailsBody" class="p-6 space-y-3 max-h-[450px] overflow-y-auto custom-scrollbar bg-gray-50/10 dark:bg-gray-900/10">
                    <!-- Dynamic Content From JS -->
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end">
                    <button type="button" class="btn-close-share-details px-8 py-2 text-[13px] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs transition-all">
                        Close Detail
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Adjusted Access Manager Modal --}}
    {{-- Reshare Management Modal --}}
    <div id="reshareRequestsModal"
        class="relative z-[105]"
        x-cloak
        x-show="showReshareModal"
        x-data="reshareRequestsModalComponent()"
        @open-reshare-modal.window="openModal()">
        {{-- Backdrop for Main Modal --}}
        <div x-show="showReshareModal"
            class="fixed inset-0 bg-gray-900/60 z-[105]"
            @click="showReshareModal = false">
        </div>

        {{-- Main Modal Content --}}
        <div x-show="showReshareModal"
            class="fixed inset-0 z-[106] flex items-center justify-center p-4 pointer-events-none shadow-2xl">

            <div class="bg-white dark:bg-gray-800 rounded-xs w-full max-w-4xl h-[650px] flex flex-col overflow-hidden border border-gray-200 dark:border-gray-700 pointer-events-auto">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-white dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xs bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="fa-solid fa-envelope-open-text text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 tracking-wider">Re-share Requests</h3>
                            <p class="text-[13px] text-gray-400 font-semibold tracking-wide">Manage supplier access requests</p>
                        </div>
                    </div>
                    <button type="button" @click="showReshareModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-2">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Tabs --}}
                <div class="px-6 py-2 bg-gray-50/50 dark:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700 flex gap-4">
                    <template x-for="t in [
                    {id: 'pending', label: 'Pending', icon: 'fa-clock text-[10px]'},
                    {id: 'approved', label: 'Approved', icon: 'fa-check-circle text-[10px]'},
                    {id: 'rejected', label: 'Rejected', icon: 'fa-times-circle text-[10px]'},
                    {id: 'all', label: 'All History', icon: 'fa-history text-[10px]'}
                ]">
                        <button @click="status = t.id"
                            class="px-4 py-2 text-[13px] font-semibold flex items-center gap-2 border-b-2 transition-all"
                            :class="status === t.id ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'">
                            <i class="fa-solid" :class="t.icon"></i>
                            <span x-text="t.label"></span>
                        </button>
                    </template>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-white dark:bg-gray-900/20 custom-scrollbar">
                    <div x-show="loading" class="flex flex-col items-center justify-center py-20 gap-4">
                        <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                        <p class="text-[13px] font-semibold text-gray-400">Fetching requests...</p>
                    </div>

                    <div x-show="!loading && requests.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-300 dark:text-gray-600">
                        <i class="fa-solid fa-folder-open text-6xl opacity-20 mb-4"></i>
                        <p class="text-[13px] text-gray-400">No reshare requests found in this category.</p>
                    </div>

                    <div x-show="!loading && requests.length > 0" class="space-y-3">
                        <template x-for="req in requests" :key="req.id">
                            <div class="group bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xs overflow-hidden hover:border-blue-200 dark:hover:border-blue-900 transition-all duration-300">
                                <div class="p-4 flex flex-col lg:flex-row lg:items-center gap-6">
                                    {{-- Left: Supplier & Package --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="px-2 py-0.5 rounded-xs bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-[9px] font-black tracking-wider uppercase border border-blue-200/50 dark:border-blue-800/50" x-text="req.supplier_code"></span>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate" x-text="req.supplier_name"></h4>
                                            <span class="text-[10px] text-gray-400 font-medium ml-auto lg:ml-0" x-text="new Date(req.reshare_requested_at).toLocaleDateString()"></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <i class="fa-solid fa-box-archive text-[10px] opacity-50"></i>
                                            <span class="font-medium">Package:</span>
                                            <span class="text-gray-900 dark:text-gray-200 font-bold" x-text="`${req.part_no} (Rev ${req.revision_no})`"></span>
                                        </div>
                                    </div>

                                    {{-- Middle: Reason --}}
                                    <div class="lg:w-1/3 p-3 bg-gray-50/80 dark:bg-gray-900/40 rounded-xs border border-gray-100 dark:border-gray-800 flex items-start gap-3">
                                        <div class="mt-0.5 text-gray-300 dark:text-gray-600">
                                            <i class="fa-solid fa-quote-left text-xs"></i>
                                        </div>
                                        <div class="flex-1 lg:max-w-[250px]">
                                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Reason</p>
                                            <p class="text-[11px] text-gray-600 dark:text-gray-300 italic leading-snug line-clamp-2 hover:line-clamp-none transition-all" x-text="req.reshare_reason || 'No reason provided.'"></p>
                                        </div>
                                    </div>

                                    {{-- Right: Actions/Status --}}
                                    <div class="shrink-0 lg:w-56">
                                        <template x-if="req.reshare_status === 'pending'">
                                            <div class="flex items-center gap-2">
                                                <button @click="approve(req)" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xs text-[10px] font-black uppercase tracking-widest transition-all">
                                                    <i class="fa-solid fa-check"></i> Approve
                                                </button>
                                                <button @click="selectedId = req.id; showReject = true; rejectReason = ''" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xs text-[10px] font-black uppercase tracking-widest transition-all">
                                                    <i class="fa-solid fa-xmark"></i> Reject
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="req.reshare_status !== 'pending'">
                                            <div class="flex flex-col items-center justify-center py-1">
                                                <div class="flex items-center gap-2 px-4 py-1.5 rounded-xs border text-[9px] font-black uppercase tracking-widest"
                                                    :class="{
                                                    'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-900/30': req.reshare_status === 'approved',
                                                    'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-900/10 dark:border-rose-900/30': req.reshare_status === 'rejected'
                                                }">
                                                    <i class="fa-solid" :class="req.reshare_status === 'approved' ? 'fa-circle-check' : 'fa-circle-xmark'"></i>
                                                    <span x-text="req.reshare_status"></span>
                                                </div>
                                                <p class="text-[9px] text-gray-400 mt-2 font-mono" x-text="new Date(req.reshare_processed_at).toLocaleDateString()"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Rejection Detail (if any) --}}
                                <template x-if="req.reshare_status === 'rejected' && req.reshare_reject_reason">
                                    <div class="px-4 py-2 bg-rose-50/30 dark:bg-rose-900/5 border-t border-rose-100/30 dark:border-rose-900/10">
                                        <p class="text-[10px] text-rose-600/70 dark:text-rose-400/70 italic flex items-center gap-2">
                                            <i class="fa-solid fa-circle-info text-[9px]"></i>
                                            Rejected for: <span class="font-bold ml-1" x-text="req.reshare_reject_reason"></span>
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div> {{-- End List Container (line 232) --}}
                </div> {{-- End Body div (line 221) --}}

                {{-- Fixed Footer - Strictly Pinned --}}
                <div class="mt-auto px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-end shrink-0">
                    <button type="button" @click="showReshareModal = false"
                        class="px-8 py-2 text-[13px] font-semibold text-gray-700 hover:text-gray-800 dark:text-gray-500 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xs transition-none active:bg-gray-200">
                        Close Window
                    </button>
                </div>
            </div> {{-- End Modal Container (line 186) --}}
        </div> {{-- End Center Wrapper (line 184) --}}

        {{-- Nested Reject Reason Modal --}}
        <div x-show="showReject"
            class="fixed inset-0 z-[120]"
            x-cloak>
            {{-- Backdrop for Reject Modal --}}
            <div class="fixed inset-0 bg-gray-900/60 z-[120]"
                @click="!submittingReject && (showReject = false)">
            </div>

            {{-- Reject Modal Content --}}
            <div class="fixed inset-0 z-[121] flex items-center justify-center p-4 pointer-events-none shadow-2xl">
                <div class="bg-white dark:bg-gray-800 rounded-xs w-full max-w-md border border-gray-200 dark:border-gray-700 overflow-hidden pointer-events-auto">

                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-rose-50/30 dark:bg-rose-900/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xs bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Reject Request</h4>
                                <p class="text-[12px] text-gray-500 font-semibold tracking-wide">Provide rejection reason</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2 px-1">
                            Reason <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="rejectReason"
                            placeholder="E.g., Incorrect supplier selected..."
                            class="w-full h-32 px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xs text-sm outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all text-gray-800 dark:text-gray-200 placeholder:text-gray-400"></textarea>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" @click="showReject = false" :disabled="submittingReject"
                            class="px-5 py-2 text-[10px] font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 uppercase tracking-widest transition-colors">
                            Cancel
                        </button>
                        <button type="button" @click="reject()" :disabled="submittingReject || !rejectReason.trim()"
                            class="inline-flex items-center gap-2 px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold uppercase tracking-widest rounded transition-all disabled:opacity-50 disabled:grayscale">
                            <span x-show="!submittingReject">Reject Now</span>
                            <span x-show="submittingReject"><i class="fa-solid fa-spinner fa-spin"></i> Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function reshareRequestsModalComponent() {
            return {
                showReshareModal: false,
                status: 'pending',
                requests: [],
                loading: false,
                selectedId: null,
                showReject: false,
                rejectReason: '',
                submittingReject: false,

                init() {
                    this.$watch('status', () => this.load());
                },

                openModal() {
                    this.showReshareModal = true;
                    this.load();
                },

                async load() {
                    this.loading = true;
                    try {
                        const res = await fetch(`{{ route('share.reshare-requests') }}?status=${this.status}`);
                        this.requests = await res.json();
                    } catch (e) {
                        console.error('Failed to load reshare requests:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                async approve(req) {
                    const result = await Swal.fire({
                        title: 'Approve Re-share?',
                        text: `Extend access for ${req.supplier_name} for 14 days?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Approve',
                        confirmButtonColor: '#16a34a'
                    });

                    if (result.isConfirmed) {
                        const res = await fetch(`{{ route('share.approve-reshare') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: req.id,
                                expiry_days: 14
                            })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            toastSuccess('Approved', data.message);
                            this.load();
                            if (window.loadKpis) window.loadKpis();
                            if (window.table) window.table.ajax.reload(null, false);
                        } else {
                            toastError('Error', data.message);
                        }
                    }
                },

                async reject() {
                    if (!this.rejectReason.trim()) return toastWarning('Required', 'Please provide a reason for rejection.');

                    this.submittingReject = true;
                    try {
                        const res = await fetch(`{{ route('share.reject-reshare') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: this.selectedId,
                                reason: this.rejectReason.trim()
                            })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            toastSuccess('Rejected', data.message);
                            this.showReject = false;
                            this.rejectReason = '';
                            this.load();
                            if (window.loadKpis) window.loadKpis();
                        } else {
                            toastError('Error', data.message);
                        }
                    } catch (e) {
                        toastError('Error', 'Failed to reject request.');
                    } finally {
                        this.submittingReject = false;
                    }
                }
            };
        }
    </script>
    @endpush

    @push('style')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @endpush
    @endsection

    @push('scripts')
    <script>
        $(function() {

            function detectTheme() {
                const isDark = document.documentElement.classList.contains('dark');

                return isDark ? {
                    mode: 'dark',
                    bg: 'rgba(30, 41, 59, 0.95)',
                    fg: '#E5E7EB',
                    border: 'rgba(71, 85, 105, 0.5)',
                    progress: 'rgba(255,255,255,.9)',
                    icon: {
                        success: '#22c55e',
                        error: '#ef4444',
                        warning: '#f59e0b',
                        info: '#3b82f6'
                    }
                } : {
                    mode: 'light',
                    bg: 'rgba(255, 255, 255, 0.98)',
                    fg: '#0f172a',
                    border: 'rgba(226, 232, 240, 1)',
                    progress: 'rgba(15,23,42,.8)',
                    icon: {
                        success: '#16a34a',
                        error: '#dc2626',
                        warning: '#d97706',
                        info: '#2563eb'
                    }
                };
            }

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
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

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
                    },
                    didOpen: (toast) => {
                        const bar = toast.querySelector('.swal2-timer-progress-bar');
                        if (bar) bar.style.background = t.progress;
                        const popup = toast.querySelector('.swal2-popup');
                        if (popup) popup.style.borderColor = t.border;
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            }

            function toastSuccess(title = 'Success', text = 'Operation completed successfully.') {
                renderToast({
                    icon: 'success',
                    title,
                    text
                });
            }

            function toastError(title = 'Error', text = 'Something went wrong.') {
                renderToast({
                    icon: 'error',
                    title,
                    text
                });
            }

            function toastWarning(title = 'Warning', text = 'Please check your input.') {
                renderToast({
                    icon: 'warning',
                    title,
                    text
                });
            }

            // Expose toasts globally for components
            window.toastSuccess = toastSuccess;
            window.toastError = toastError;
            window.toastWarning = toastWarning;


            let table;
            let refreshTimeout;
            const ENDPOINT = '{{ route("share.filters") }}';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function resetSelect2ToAll($el) {
                $el.empty();
                const opt = new Option('All', 'All', true, true);
                $el.append(opt);
                $el.trigger('change');
                $el.trigger('select2:select');
            }

            function makeSelect2($el, field, extraParamsFn) {
                $el.select2({
                    width: '100%',
                    placeholder: 'All',
                    allowClear: false,
                    minimumResultsForSearch: 0,
                    ajax: {
                        url: ENDPOINT,
                        dataType: 'json',
                        delay: 250,
                        cache: true,
                        data: function(params) {
                            const p = {
                                select2: field,
                                q: params.term || '',
                                page: params.page || 1
                            };
                            if (typeof extraParamsFn === 'function') {
                                Object.assign(p, extraParamsFn());
                            }
                            return p;
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            const results = Array.isArray(data.results) ? data.results.slice() : [];
                            if (params.page === 1 && !results.some(r => r.id === 'All')) {
                                results.unshift({
                                    id: 'All',
                                    text: 'All'
                                });
                            }
                            return {
                                results,
                                pagination: {
                                    more: data.pagination ? data.pagination.more : false
                                }
                            };
                        }
                    },
                    templateResult: function(item) {
                        if (item.loading) return item.text;
                        return $('<div class="text-sm">' + (item.text || item.id) + '</div>');
                    },
                    templateSelection: function(item) {
                        return item.text || item.id || 'All';
                    }
                });
            }

            makeSelect2($('#customer'), 'customer');
            makeSelect2($('#model'), 'model', () => ({
                customer_code: $('#customer').val() || ''
            }));
            makeSelect2($('#document-type'), 'doc_type');
            makeSelect2($('#category'), 'category', () => ({
                doc_type: $('#document-type').val() || ''
            }));
            makeSelect2($('#status'), 'status');

            $('#customer').on('change', function() {
                resetSelect2ToAll($('#model'));
            });
            $('#document-type').on('change', function() {
                resetSelect2ToAll($('#category'));
            });

            function getCurrentFilters() {
                const valOrAll = v => (v && v.length ? v : 'All');
                return {
                    customer: valOrAll($('#customer').val()),
                    model: valOrAll($('#model').val()),
                    doc_type: valOrAll($('#document-type').val()),
                    category: valOrAll($('#category').val()),
                    status: valOrAll($('#status').val()),
                };
            }

            function fmtDate(v) {
                if (!v) return '';
                const d = new Date(v);
                if (isNaN(d)) return v;
                const pad = n => n.toString().padStart(2, '0');
                const dd = pad(d.getDate());
                const MM = pad(d.getMonth() + 1);
                const yyyy = d.getFullYear();
                const HH = pad(d.getHours());
                const mm = pad(d.getMinutes());
                return `${dd}-${MM}-${yyyy} ${HH}:${mm}`;
            }


            // Helper for highlighting search terms
            function highlightText(data, searchVal) {
                if (!searchVal || !data) return data;
                const safeSearch = searchVal.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${safeSearch})`, 'gi');
                return data.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 dark:text-gray-100 p-0">$1</mark>');
            }

            function initTable() {
                table = $('#approvalTable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: true,
                    responsive: false,
                    scrollX: true,
                    scrollCollapse: true,
                    deferRender: true,
                    ajax: {
                        url: '{{ route("share.list") }}',
                        type: 'GET',
                        data: function(d) {
                            const f = getCurrentFilters();
                            d.customer = f.customer;
                            d.model = f.model;
                            d.doc_type = f.doc_type;
                            d.category = f.category;
                            d.status = f.status;
                            d.search_term = $('#custom-share-search').val() || '';
                        }
                    },

                    order: [
                        [5, 'desc']
                    ],

                    language: {
                        infoEmpty: "No Records Found",
                        infoFiltered: "",
                        zeroRecords: '<div class="flex flex-col items-center justify-center p-12 text-gray-400"><i class="fa-solid fa-folder-open text-4xl mb-3 opacity-20"></i><span class="text-xs italic">No matching files found</span></div>'
                    },
                    dom: 't<"flex flex-col sm:flex-row justify-between items-center p-6 border-t border-gray-50 dark:border-gray-800 gap-4" <"flex-1"i> <"flex justify-end"p>>',

                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: null,
                            name: 'Package Info',
                            render: function(data, type, row) {
                                const searchVal = $('#custom-share-search').val();

                                // Combine Part No and Partners (consistent with file_upload.blade.php)
                                let mainText = row.part_no;
                                if (row.partners) {
                                    let pClean = row.partners.replace(/,/g, ' / ');
                                    mainText += ` / ${pClean}`;
                                }
                                mainText = highlightText(mainText, searchVal);

                                let subText = highlightText(`${row.customer} - ${row.model}`, searchVal);

                                return `
                                <div class="flex flex-col" title="${row.part_no} ${row.partners ? '/ ' + row.partners : ''}">
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">${mainText}</span>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                        ${subText}
                                    </div>
                                </div>
                            `;
                            }
                        },
                        {
                            data: null,
                            name: 'Revision',
                            render: function(data, type, row) {
                                const revVal = row.revision ?? row.revision_no;
                                const ecnStr = row.ecn_no ? highlightText(row.ecn_no, $('#custom-share-search').val()) : '-';
                                return `
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center">
                                        <span class="px-2 py-0.5 rounded-xs text-[10px] font-black bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 uppercase tracking-tighter">
                                            REV ${revVal}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-bold truncate">
                                        ${ecnStr}
                                    </div>
                                </div>
                            `;
                            }
                        },
                        {
                            data: null,
                            name: 'Categorization',
                            render: function(data, type, row) {
                                return `
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 capitalize">${row.category || row.doc_type}</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">${row.part_group || '-'}</span>
                                </div>
                            `;
                            }
                        },
                        {
                            data: 'project_status',
                            name: 'project_status',
                            className: 'text-center',
                            render: function(data, type, row) {
                                const value = row.project_status ?? row.project_status_name ?? data ?? '';
                                if (!value) return '<span class="text-xs text-gray-400 dark:text-gray-500">–</span>';

                                let colors = 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800';
                                if (value === 'Regular') colors = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800';
                                else if (value === 'Feasibility Study') colors = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800';

                                return `<span class="px-2 py-2 text-[12px] font-bold border ${colors} rounded-xs">${value}</span>`;
                            }
                        },
                        {
                            data: 'decision_date',
                            name: 'dpr.decided_at',
                            className: 'text-center',
                            render: function(v) {
                                const text = fmtDate(v);
                                return `<div class="text-[13px] text-gray-500 dark:text-gray-400 font-semibold">${text || '—'}</div>`;
                            }
                        },
                        {
                            data: 'share_to',
                            name: 'psr.share_to',
                            render: function(data, type, row) {
                                if (!data || data.length === 0) {
                                    return '<span class="text-gray-400 text-xs italic">Not yet distributed</span>';
                                }
                                const json = JSON.stringify(data).replace(/"/g, '&quot;');
                                return `
                                <button type="button" 
                                    class="btn-view-shares inline-flex items-center gap-1.5 px-4 py-1.5 text-[13px] font-semibold uppercase text-white bg-blue-600 hover:bg-blue-700 rounded-xs transition-all"
                                    data-shares="${json}">
                                    <i class="fa-solid fa-users text-[13px]"></i> ${data.length} Recipients
                                </button>
                            `;
                            }
                        },
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            className: 'text-center whitespace-nowrap',
                            render: function(packageId, type, row) {
                                return `
                                <button 
                                    type="button" 
                                    class="btn-share inline-flex items-center justify-center w-10 h-10 rounded-sm bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 border border-transparent"
                                    data-id="${packageId}" 
                                    title="Share package">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </button>
                                <button 
                                    type="button" 
                                    class="btn-download inline-flex items-center justify-center w-10 h-10 rounded-sm bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 transition-all duration-200 border border-transparent ml-1"
                                    data-id="${packageId}" 
                                    data-file-count="${row.file_count || 0}"
                                    data-file-size="${row.total_size || 0}"
                                    title="Download package">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            `;
                            }
                        }
                    ],

                    columnDefs: [{
                        targets: 0,
                        className: 'text-center text-[10px] font-bold text-gray-400',
                        width: '40px'
                    }],

                    createdRow: function(row) {
                        $(row).addClass('hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 text-gray-900 dark:text-gray-100');
                        $('td', row).addClass('py-4 px-4 align-middle');
                    }
                });

                // Expose table globally
                window.table = table;

                table.on('draw.dt', function() {
                    $('.dataTables_scrollBody').addClass('custom-scrollbar');
                    const info = table.page.info();
                    table.column(0, {
                        page: 'current'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1 + info.start;
                    });
                });

                // Debounced Search Handler
                let searchTimer;
                $('#custom-share-search').on('keyup', function() {
                    clearTimeout(searchTimer);
                    const $icon = $('#share-search-icon');
                    $icon.removeClass('fa-magnifying-glass').addClass('fa-spinner fa-spin text-blue-500');

                    searchTimer = setTimeout(() => {
                        table.ajax.reload();
                        loadKpis();
                        $icon.removeClass('fa-spinner fa-spin text-blue-500').addClass('fa-magnifying-glass');
                    }, 500);
                });
            }

            // Global functions for access from HTML onclick and Modals
            window.loadKpis = function() {
                const f = getCurrentFilters();
                const $cards = $('#totalShared, #totalActive, #totalExpired, #totalSuppliers');

                // Subtle pulse while loading
                $cards.addClass('animate-pulse opacity-50');

                $.get('{{ route("share.kpi") }}', f, function(data) {
                    const countUp = (id, val) => {
                        const $el = $('#' + id);
                        const current = parseInt($el.text()) || 0;
                        if (current === val) return;

                        $({
                            val: current
                        }).animate({
                            val: val
                        }, {
                            duration: 600,
                            step: function() {
                                $el.text(Math.ceil(this.val));
                            },
                            complete: function() {
                                $el.text(val);
                            }
                        });
                    };

                    countUp('totalShared', data.totalShared);
                    countUp('totalActive', data.totalActive);
                    countUp('totalExpired', data.totalExpired);
                    countUp('totalSuppliers', data.totalSuppliers);

                    // Update Header Badge
                    const $badge = $('#reshare-badge');
                    if (data.totalRequest > 0) {
                        $badge.find('span').text(data.totalRequest);
                        $badge.removeClass('hidden');
                    } else {
                        $badge.addClass('hidden');
                    }
                }).always(() => {
                    $cards.removeClass('animate-pulse opacity-50');
                });
            };

            window.loadHistory = function() {
                const $container = $('#shareHistoryList');

                $.get('{{ route("share.history") }}', function(logs) {
                    $container.empty();

                    if (logs.length === 0) {
                        $container.html('<div class="text-xs text-gray-400 text-center py-6 italic -ml-2">No recent activity</div>');
                        $container.removeClass('border-l-2 border-gray-100 dark:border-gray-700 ml-2');
                        return;
                    } else {
                        $container.addClass('border-l-2 border-gray-100 dark:border-gray-700 ml-2');
                    }

                    logs.forEach(log => {
                        const expStatus = log.is_expired ?
                            `<div class="flex items-center gap-1.5 text-rose-500 font-bold text-[10px] border border-rose-200 dark:border-rose-900/40 rounded-xs px-2 py-1 bg-rose-50 dark:bg-rose-900/20">
                                Expired: ${log.expired_at || '-'}
                            </div>` :
                            `<div class="flex items-center gap-1.5 text-gray-400 font-bold text-[10px] border border-gray-200 dark:border-gray-700 rounded-xs px-2 py-1 bg-gray-50 dark:bg-gray-800/40">
                                Exp: ${log.expired_at || '-'}
                            </div>`;

                        const node = `
                        <div class="relative pl-6 group/item">
                            <div class="absolute -left-[7px] top-1.5 w-3 h-3 rounded-full bg-gray-300 ring-4 ring-white dark:ring-gray-800 z-10"></div>
                            
                            <div class="flex flex-col gap-2 p-0.5 rounded-xs hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-all duration-200">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[12px] font-bold text-blue-600 dark:text-blue-400">${log.user}</span>
                                    <span class="text-[10px] text-gray-400 font-medium" title="${log.full_time}">${log.time}</span>
                                </div>
                                
                                <div class="text-[12px] font-black text-gray-900 dark:text-gray-100 leading-tight">
                                    ${log.part_no}
                                </div>

                                <div class="text-[11px] text-gray-500 dark:text-gray-400 bg-gray-50/80 dark:bg-gray-800/50 p-2.5 rounded-xs border border-gray-100 dark:border-gray-700/50">
                                    <span class="opacity-60 block mb-1 font-semibold text-[12px] text-gray-600 dark:text-gray-400">Recipient</span> 
                                    <span class="text-gray-700 dark:text-gray-300 leading-normal line-clamp-2">${log.shared_to}</span>
                                </div>

                                <div class="pt-1">
                                    ${expStatus}
                                </div>
                            </div>
                        </div>
                        `;
                        $container.append(node);
                    });
                });
            };

            function refreshData() {
                clearTimeout(refreshTimeout);
                refreshTimeout = setTimeout(() => {
                    if (table) table.ajax.reload(null, true);
                    loadKpis();
                }, 50);
            }

            function bindHandlers() {
                $('#customer, #model, #document-type, #category, #status').on('change', refreshData);

                $('#btnResetFilters').on('click', function() {
                    try {
                        $('#custom-share-search').val('');
                        resetSelect2ToAll($('#customer'));
                        resetSelect2ToAll($('#model'));
                        resetSelect2ToAll($('#document-type'));
                        resetSelect2ToAll($('#category'));
                        resetSelect2ToAll($('#status'));
                    } finally {
                        refreshData();
                    }
                });
            }


            // --- Share Details Modal Logic ---
            const $shareDetailsModal = $('#shareDetailsModal');
            const $shareDetailsBody = $('#shareDetailsBody');

            $('body').on('click', '.btn-close-share-details', function() {
                $shareDetailsModal.hide();
            });

            $shareDetailsModal.on('click', function(e) {
                if ($(e.target).is($shareDetailsModal)) {
                    $(this).hide();
                }
            });

            $('#approvalTable tbody').on('click', '.btn-view-shares', function(e) {
                e.stopPropagation();
                const json = $(this).attr('data-shares');
                if (!json) return;

                const shares = JSON.parse(json);
                $shareDetailsBody.empty();

                shares.forEach(s => {
                    const sharedDate = fmtDate(s.shared_at);
                    const expiredDate = s.expired_at ? fmtDate(s.expired_at).split(' ')[0] : '-';

                    let isExpired = false;
                    if (s.expired_at) {
                        isExpired = new Date(s.expired_at) < new Date();
                    }

                    const statusTxt = isExpired ? 'Expired' : 'Active';
                    const statusBg = isExpired ? 'bg-red-50 text-red-600 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100';

                    // Get initials (first 2 chars of code)
                    const initials = (s.code || '??').substring(0, 2).toUpperCase();

                    const item = `
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xs bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs border border-blue-100 dark:border-blue-800 group-hover:scale-110 transition-transform">
                                ${initials}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 leading-tight">${s.name || s.code}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Shared: ${sharedDate.split(' ')[0]}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2 py-0.5 rounded-xs text-[13px] font-black border ${statusBg}">${statusTxt}</span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5">${expiredDate}</p>
                        </div>
                    </div>
                `;
                    $shareDetailsBody.append(item);
                });

                $shareDetailsModal.show();
            });

            // Initialize Share Action
            $('#approvalTable tbody').on('click', '.btn-share', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                const row = table.row($(this).parents('tr')).data();

                if (window.openShareModal) {
                    window.openShareModal(
                        id,
                        row.part_no || 'Document Package',
                        `${row.customer} - ${row.model}`, {
                            revision: row.revision,
                            ecn: row.ecn_no,
                            status: row.project_status,
                            category: row.category || row.doc_type,
                            partners: row.partners
                        }
                    );
                } else {
                    console.error('Share modal component not loaded');
                }
            });

            // Listen for package shared event from modal to refresh data
            $(document).on('package:shared', function() {
                if (table) {
                    table.ajax.reload(null, false); // Reload table without resetting pagination
                }
                loadKpis();
                loadHistory();
            });

            // Initialize Data & Handlers
            initTable();
            // Check if bindHandlers exists before calling, or define it if it was lost
            if (typeof bindHandlers === 'function') {
                bindHandlers();
            }
            loadKpis();
            loadHistory();

            $('#approvalTable tbody').on('click', '.btn-download', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                const fileCount = $(this).data('file-count');
                const totalSize = parseInt($(this).data('file-size')) || 0;

                // Format size
                let sizeStr = '0 Bytes';
                if (totalSize > 0) {
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(totalSize) / Math.log(k));
                    sizeStr = parseFloat((totalSize / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }

                let baseUrl = `{{ route('export.prepare-zip', ['revision_id' => '__ID__']) }}`;
                let url = baseUrl.replace('__ID__', id);

                // trigger alpine event for modal
                window.dispatchEvent(new CustomEvent('open-download-zip', {
                    detail: {
                        url: url,
                        count: fileCount,
                        size: sizeStr
                    }
                }));
            });

            $('#approvalTable tbody').on('click', 'tr', function(e) {
                if ($(e.target).closest('.btn-share, .btn-view-shares, .btn-download').length) return;

                const rowData = table.row(this).data();
                if (!rowData || !rowData.hash) return;

                const url = '{{ route("share.detail", ["id" => "__ID__"]) }}'.replace('__ID__', rowData.hash);
                window.location.href = url;
            });
        });
    </script>
    @endpush