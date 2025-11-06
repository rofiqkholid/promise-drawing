@extends('layouts.app')
@section('title', 'Receipt Detail - PROMISE')
@section('header-title', 'Receipt Detail')

@section('content')

<div
    class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen"
    x-data="receiptDetail()"
    x-init="init()"
    @mousemove.window="onPan($event)"
    @mouseup.window="endPan()"
    @mouseleave.window="endPan()">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
        <div class="lg:col-span-4 space-y-6">

            <div x-ref="metaCard"
                class="self-start bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 md:justify-between">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
                            Receipt Detail
                        </h2>

                        @php
                        $backUrl = url()->previous();
                        $backUrl = ($backUrl && $backUrl !== url()->current()) ? $backUrl : route('receipts');
                        @endphp
                        <a href="{{ $backUrl }}"
                            class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back
                        </a>
                    </div>
                </div>

                <div class="p-4">
                    <p class="text-sm text-gray-900 dark:text-gray-100 truncate"
                        x-text="metaLine()"
                        :title="metaLine()"></p>
                </div>

            </div>

            @php
            function renderFileGroup($title, $icon, $category) {
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button @click="toggleSection('{{$category}}')" class="w-full p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" :aria-expanded="openSections.includes('{{$category}}')">
                    <div class="flex items-center">
                        <i class="fa-solid {{$icon}} mr-3 text-gray-500 dark:text-gray-400"></i>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
                    </div>
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full" x-text="`${(pkg.files['{{$category}}']?.length || 0)} files`"></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 transition-transform" :class="{'rotate-180': openSections.includes('{{$category}}')}"></i>
                </button>
                <div x-show="openSections.includes('{{$category}}')" x-collapse class="p-2 max-h-72 overflow-y-auto">
                    <template x-for="file in (pkg.files['{{$category}}'] || [])" :key="file.name">
                        <div @click="selectFile(file)" :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}" class="flex items-center p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200" role="button" tabindex="0" @keydown.enter="selectFile(file)">
                            <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 transition-colors group-hover:text-blue-500"></i>
                            <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
                        </div>
                    </template>
                    <template x-if="(pkg.files['{{$category}}'] || []).length === 0">
                        <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No files available.</p>
                    </template>
                </div>
            </div>
            @php } @endphp

            {{ renderFileGroup('2D Drawings', 'fa-drafting-compass', '2d') }}
            {{ renderFileGroup('3D Models', 'fa-cubes', '3d') }}
            {{ renderFileGroup('ECN / Documents', 'fa-file-lines', 'ecn') }}

        </div>
        <div class="lg:col-span-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div x-show="!selectedFile" x-cloak class="flex flex-col items-center justify-center h-96 p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
                    <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500"></i>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
                </div>

                <div x-show="selectedFile" x-transition.opacity x-cloak class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedFile?.name"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Last updated: {{ now()->format('M d, Y H:i') }}</p>
                        </div>
                        <a x-show="selectedFile?.url" :href="selectedFile?.url" target="_blank" rel="noopener"
                            class="inline-flex items-center px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fa-solid fa-up-right-from-square mr-2"></i> Open
                        </a>
                    </div>

                    <div x-show="isImage(selectedFile?.name) || isTiff(selectedFile?.name) || isHpgl(selectedFile?.name)"
                        class="mb-3 flex items-center justify-end gap-2 text-xs text-gray-700 dark:text-gray-200">
                        <span x-text="Math.round(imageZoom * 100) + '%'"></span>
                        <button @click="zoomOut()"
                            class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                            -
                        </button>
                        <button @click="resetZoom()"
                            class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                            Fit
                        </button>
                        <button @click="zoomIn()"
                            class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                            +
                        </button>
                    </div>

                    <div class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center w-full">

                        <template x-if="isImage(selectedFile?.name)">
                            <div
                                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing flex items-center justify-center"
                                @mousedown.prevent="startPan($event)"
                                @wheel.prevent="onWheelZoom($event)">
                                <img
                                    :src="selectedFile?.url"
                                    alt="File Preview"
                                    class="pointer-events-none select-none"
                                    loading="lazy"
                                    :style="imageTransformStyle()">
                            </div>
                        </template>

                        <template x-if="isPdf(selectedFile?.name)">
                            <iframe
                                :src="pdfSrc(selectedFile?.url)"
                                class="w-full h-[70vh] rounded-md border border-gray-200 dark:border-gray-700"
                                title="PDF preview"></iframe>
                        </template>

                        <template x-if="isTiff(selectedFile?.name)">
                            <div
                                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing flex items-center justify-center"
                                @mousedown.prevent="startPan($event)"
                                @wheel.prevent="onWheelZoom($event)">
                                <img
                                    x-ref="tifImg"
                                    alt="TIFF Preview"
                                    class="pointer-events-none select-none"
                                    :style="imageTransformStyle()" />
                                <div x-show="tifLoading" class="absolute bottom-3 right-3 text-xs text-gray-700 dark:text-gray-200 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded">
                                    Rendering TIFF…
                                </div>
                                <div x-show="tifError" class="absolute bottom-3 left-3 text-xs text-red-600 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded" x-text="tifError"></div>
                            </div>
                        </template>

                        <template x-if="isHpgl(selectedFile?.name)">
                            <div
                                class="relative w-full h-[70vh] overflow-hidden bg-black/5 rounded cursor-grab active:cursor-grabbing flex items-center justify-center"
                                @mousedown.prevent="startPan($event)"
                                @wheel.prevent="onWheelZoom($event)">
                                <canvas
                                    x-ref="hpglCanvas"
                                    class="pointer-events-none select-none"
                                    :style="imageTransformStyle()"></canvas>

                                <div
                                    x-show="hpglLoading"
                                    class="absolute bottom-3 right-3 text-xs text-gray-700 dark:text-gray-200 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded">
                                    Rendering HPGL…
                                </div>
                                <div
                                    x-show="hpglError"
                                    class="absolute bottom-3 left-3 text-xs text-red-600 bg-white/80 dark:bg-gray-900/80 px-2 py-1 rounded"
                                    x-text="hpglError"></div>
                            </div>
                        </template>

                        <template x-if="isCad(selectedFile?.name)">
                            <div class="w-full">
                                <div x-ref="igesWrap" class="w-full h-[70vh] rounded border border-gray-200 dark:border-gray-700 bg-black/5"></div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <div class="inline-flex rounded-md shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                                        <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700" @click="setDisplayStyle('shaded')">Shaded</button>
                                    </div>
                                    <div class="inline-flex rounded-md shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                                        <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700" @click="setDisplayStyle('shaded-edges')">Shaded+Edges</button>
                                    </div>

                                    <div class="inline-flex items-center gap-2 ml-2">
                                        <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            :class="{'bg-blue-50 dark:bg-blue-900/30': iges.measure.enabled}"
                                            @click="toggleMeasure()">
                                            Measure
                                        </button>
                                        <button class="px-2 py-1 text-xs text-gray-900 dark:text-gray-100 rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="clearMeasurements()">
                                            Clear
                                        </button>
                                    </div>
                                </div>

                                <div x-show="iges.loading" class="text-xs text-gray-500 mt-2">Loading CAD…</div>
                                <div x-show="iges.error" class="text-xs text-red-600 mt-2" x-text="iges.error"></div>
                            </div>
                        </template>

                        <template
                            x-if="
                                !isImage(selectedFile?.name)
                                && !isPdf(selectedFile?.name)
                                && !isTiff(selectedFile?.name)
                                && !isCad(selectedFile?.name)
                                && !isHpgl(selectedFile?.name)
                            ">
                            <div class="text-center">
                                <i class="fa-solid fa-file text-6xl text-gray-400 dark:text-gray-500"></i>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Preview Unavailable</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">This file type is not supported for preview.</p>
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-collapse] {
        @apply overflow-hidden transition-all duration-300 ease-in-out;
    }

    .preview-area {
        @apply bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center;
    }

    [x-cloak] {
        display: none !important;
    }

    .measure-label {
        user-select: none;
        white-space: nowrap;
    }
</style>

@endsection

@push('scripts')
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

<script src="https://unpkg.com/utif@2.0.1/UTIF.js"></script>

<script async src="https://unpkg.com/es-module-shims@1.10.0/dist/es-module-shims.js"></script>
<script type="importmap">
    {
    "imports": {
      "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
      "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/",
      "three-mesh-bvh": "https://unpkg.com/three-mesh-bvh@0.7.6/build/index.module.js"
    }
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/occt-import-js@0.0.23/dist/occt-import-js.js"></script>

<script>
    /* ========== Toast Utilities (Dihapus) ========== */

    /* ========== Alpine Component ========== */
    function receiptDetail() {
        return {
            // Ganti $approvalId menjadi $receiptId (pastikan dikirim dari controller)
            receiptId: JSON.parse(`@json($receiptId)`),
            pkg: JSON.parse(`@json($detail)`),

            selectedFile: null,
            openSections: [],

            // modal state (Dihapus)

            // TIFF state
            tifLoading: false,
            tifError: '',

            // HPGL state
            hpglLoading: false,
            hpglError: '',

            // ZOOM + PAN untuk image / TIFF / HPGL
            imageZoom: 1,
            minZoom: 0.5,
            maxZoom: 4,
            zoomStep: 0.25,
            panX: 0,
            panY: 0,
            isPanning: false,
            panStartX: 0,
            panStartY: 0,
            panOriginX: 0,
            panOriginY: 0,

            zoomIn() {
                this.imageZoom = Math.min(this.imageZoom + this.zoomStep, this.maxZoom);
            },
            zoomOut() {
                this.imageZoom = Math.max(this.imageZoom - this.zoomStep, this.minZoom);
            },
            resetZoom() {
                this.imageZoom = 1;
                this.panX = 0;
                this.panY = 0;
            },
            onWheelZoom(e) {
                const delta = e.deltaY;
                const step = this.zoomStep;

                if (delta < 0) {
                    // scroll ke atas = zoom in
                    this.imageZoom = Math.min(this.imageZoom + step, this.maxZoom);
                } else if (delta > 0) {
                    // scroll ke bawah = zoom out
                    this.imageZoom = Math.max(this.imageZoom - step, this.minZoom);
                }
            },
            startPan(e) {
                this.isPanning = true;
                this.panStartX = e.clientX;
                this.panStartY = e.clientY;
                this.panOriginX = this.panX;
                this.panOriginY = this.panY;
            },
            onPan(e) {
                if (!this.isPanning) return;
                const dx = e.clientX - this.panStartX;
                const dy = e.clientY - this.panStartY;
                this.panX = this.panOriginX + dx;
                this.panY = this.panOriginY + dy;
            },
            endPan() {
                this.isPanning = false;
            },
            imageTransformStyle() {
                return `transform: translate(${this.panX}px, ${this.panY}px) scale(${this.imageZoom}); transform-origin: center center;`;
            },

            // CAD viewer state
            iges: {
                renderer: null,
                scene: null,
                camera: null,
                controls: null,
                animId: 0,
                loading: false,
                error: '',
                rootModel: null,
                THREE: null,
                measure: {
                    enabled: false,
                    group: null,
                    p1: null,
                    p2: null
                }
            },
            _onIgesResize: null,

            /* ===== Helpers jenis file ===== */
            extOf(name) {
                const i = (name || '').lastIndexOf('.');
                return i > -1 ? (name || '').slice(i + 1).toLowerCase() : '';
            },
            isImage(name) {
                return ['png', 'jpg', 'jpeg', 'webp', 'gif', 'bmp'].includes(this.extOf(name));
            },
            isPdf(name) {
                return this.extOf(name) === 'pdf';
            },
            isTiff(name) {
                return ['tif', 'tiff'].includes(this.extOf(name));
            },
            isHpgl(name) {
                return ['plt', 'hpgl', 'hpg', 'prn'].includes(this.extOf(name));
            },
            isCad(name) {
                return ['igs', 'iges', 'stp', 'step'].includes(this.extOf(name));
            },
            pdfSrc(u) {
                return u;
            },

            /* ===== TIFF renderer: convert ke PNG dataURL & taruh ke <img> ===== */
            async renderTiff(url) {
                if (!url || typeof window.UTIF === 'undefined') return;

                this.tifLoading = true;
                this.tifError = '';

                try {
                    const resp = await fetch(url, {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error('Gagal mengambil file TIFF');
                    const buf = await resp.arrayBuffer();

                    const U =
                        (window.UTIF && typeof window.UTIF.decode === 'function') ? window.UTIF :
                        (window.UTIF && window.UTIF.UTIF && typeof window.UTIF.UTIF.decode === 'function') ? window.UTIF.UTIF :
                        null;

                    if (!U) throw new Error('Library UTIF tidak sesuai (decode() tidak ditemukan)');

                    const ifds = U.decode(buf);
                    if (!ifds || !ifds.length) throw new Error('TIFF tidak memiliki frame');

                    const first = ifds[0];

                    if (typeof U.decodeImage === 'function') {
                        U.decodeImage(buf, first);
                    } else if (typeof U.decodeImages === 'function') {
                        U.decodeImages(buf, ifds);
                    }

                    const rgba = U.toRGBA8(first);
                    const w = first.width;
                    const h = first.height;

                    const off = document.createElement('canvas');
                    const ctx = off.getContext('2d');
                    off.width = w;
                    off.height = h;

                    const imgData = ctx.createImageData(w, h);
                    imgData.data.set(rgba);
                    ctx.putImageData(imgData, 0, 0);

                    const dataUrl = off.toDataURL('image/png');

                    await this.$nextTick();
                    const img = this.$refs.tifImg;
                    if (img) img.src = dataUrl;
                } catch (e) {
                    console.error(e);
                    this.tifError = e?.message || 'Gagal render TIFF';
                } finally {
                    this.tifLoading = false;
                }
            },

            /* ===== HPGL renderer: parse PU/PD/PA & gambar ke canvas (hi-res) ===== */
            async renderHpgl(url) {
                if (!url) return;

                this.hpglLoading = true;
                this.hpglError = '';

                try {
                    const resp = await fetch(url, {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error('Gagal mengambil file HPGL');
                    const text = await resp.text();

                    // buang spasi & pecah per ';'
                    const commands = text.replace(/\s+/g, '').split(';');

                    let penDown = false;
                    let x = 0,
                        y = 0;
                    const segments = [];
                    let minX = Infinity,
                        minY = Infinity,
                        maxX = -Infinity,
                        maxY = -Infinity;

                    const addPoint = (nx, ny) => {
                        if (penDown) {
                            segments.push({
                                x1: x,
                                y1: y,
                                x2: nx,
                                y2: ny
                            });
                            minX = Math.min(minX, x, nx);
                            minY = Math.min(minY, y, ny);
                            maxX = Math.max(maxX, x, nx);
                            maxY = Math.max(maxY, y, ny);
                        } else {
                            minX = Math.min(minX, nx);
                            minY = Math.min(minY, ny);
                            maxX = Math.max(maxX, nx);
                            maxY = Math.max(maxY, ny);
                        }
                        x = nx;
                        y = ny;
                    };

                    for (const raw of commands) {
                        if (!raw) continue;
                        const cmd = raw.toUpperCase();
                        const op = cmd.slice(0, 2);
                        const argsStr = cmd.slice(2);

                        const parseCoords = () => {
                            if (!argsStr) return [];
                            return argsStr.split(',').map(Number).filter(v => !isNaN(v));
                        };

                        if (op === 'IN') {
                            penDown = false;
                            x = 0;
                            y = 0;
                        } else if (op === 'SP') {
                            // abaikan warna
                        } else if (op === 'PU') {
                            penDown = false;
                            const coords = parseCoords();
                            for (let i = 0; i < coords.length; i += 2) {
                                addPoint(coords[i], coords[i + 1]);
                            }
                        } else if (op === 'PD') {
                            penDown = true;
                            const coords = parseCoords();
                            for (let i = 0; i < coords.length; i += 2) {
                                addPoint(coords[i], coords[i + 1]);
                            }
                        } else if (op === 'PA') {
                            const coords = parseCoords();
                            for (let i = 0; i < coords.length; i += 2) {
                                addPoint(coords[i], coords[i + 1]);
                            }
                        }
                    }

                    await this.$nextTick();
                    const canvas = this.$refs.hpglCanvas;
                    if (!canvas) throw new Error('Canvas HPGL tidak ditemukan');

                    const parent = canvas.parentElement;
                    const w = parent.clientWidth || 800;
                    const h = parent.clientHeight || 500;

                    // ==== HIGH-RES CANVAS (supaya pas di-zoom tetap tajam) ====
                    const dpr = window.devicePixelRatio || 1;
                    const logicalScale = 4 * dpr; // bisa diubah 3/5 sesuai kebutuhan
                    canvas.width = w * logicalScale;
                    canvas.height = h * logicalScale;
                    canvas.style.width = w + 'px';
                    canvas.style.height = h + 'px';

                    const ctx = canvas.getContext('2d');
                    ctx.setTransform(logicalScale, 0, 0, logicalScale, 0, 0);
                    ctx.clearRect(0, 0, w, h);
                    ctx.lineWidth = 1 / logicalScale;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    ctx.strokeStyle = '#000';

                    if (!segments.length) return;

                    const dx = maxX - minX || 1;
                    const dy = maxY - minY || 1;
                    const scale = 0.9 * Math.min(w / dx, h / dy); // padding 10%
                    const offX = (w - dx * scale) / 2 - minX * scale;
                    const offY = (h - dy * scale) / 2 + maxY * scale; // Y dibalik

                    ctx.beginPath();
                    for (const s of segments) {
                        const sx = s.x1 * scale + offX;
                        const sy = -s.y1 * scale + offY;
                        const ex = s.x2 * scale + offX;
                        const ey = -s.y2 * scale + offY;
                        ctx.moveTo(sx, sy);
                        ctx.lineTo(ex, ey);
                    }
                    ctx.stroke();
                } catch (e) {
                    console.error(e);
                    this.hpglError = e?.message || 'Gagal render HPGL';
                } finally {
                    this.hpglLoading = false;
                }
            },

            /* ===== OCCT result -> THREE meshes ===== */
            _buildThreeFromOcct(result, THREE) {
                const group = new THREE.Group();
                const meshes = result.meshes || [];
                for (let i = 0; i < meshes.length; i++) {
                    const m = meshes[i];
                    const g = new THREE.BufferGeometry();
                    g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
                    if (m.attributes.normal?.array) g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
                    if (m.index?.array) g.setIndex(m.index.array);
                    let color = 0xcccccc;
                    if (m.color && m.color.length === 3) color = (m.color[0] << 16) | (m.color[1] << 8) | (m.color[2]);
                    const mat = new THREE.MeshStandardMaterial({
                        color,
                        metalness: 0,
                        roughness: 1,
                        side: THREE.DoubleSide
                    });
                    const mesh = new THREE.Mesh(g, mat);
                    mesh.name = m.name || `mesh_${i}`;
                    group.add(mesh);
                }
                return group;
            },

            /* ===== Cleanup CAD ===== */
            disposeCad() {
                try {
                    cancelAnimationFrame(this.iges.animId || 0);
                    if (this._onIgesResize) window.removeEventListener('resize', this._onIgesResize);
                    const {
                        renderer,
                        scene,
                        controls
                    } = this.iges || {};
                    controls?.dispose?.();
                    scene?.traverse?.(o => {
                        o.geometry?.dispose?.();
                        if (o.material) {
                            const m = o.material;
                            Array.isArray(m) ? m.forEach(mm => mm.dispose?.()) : m.dispose?.();
                        }
                    });
                    renderer?.dispose?.();
                    const wrap = this.$refs.igesWrap;
                    if (wrap)
                        while (wrap.firstChild) wrap.removeChild(wrap.firstChild);
                } catch {}
                this.iges = {
                    renderer: null,
                    scene: null,
                    camera: null,
                    controls: null,
                    animId: 0,
                    loading: false,
                    error: '',
                    rootModel: null,
                    THREE: null,
                    measure: {
                        enabled: false,
                        group: null,
                        p1: null,
                        p2: null
                    }
                };
                this._onIgesResize = null;
            },

            /* ===== Meta line formatter ===== */
            metaLine() {
                const m = this.pkg?.metadata || {};
                return [m.customer, m.model, m.part_no, m.revision, this.pkg?.status]
                    .filter(v => v && String(v).trim().length > 0)
                    .join(' - ');
            },

            /* ===== Display Styles / Edges ===== */
            _oriMats: new Map(),
            _cacheOriginalMaterials(root, THREE) {
                root.traverse(o => {
                    if (o.isMesh && !this._oriMats.has(o)) {
                        const m = o.material;
                        this._oriMats.set(o, Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone());
                    }
                });
            },
            _restoreMaterials(root) {
                root.traverse(o => {
                    if (!o.isMesh) return;
                    const m = this._oriMats.get(o);
                    if (!m) return;
                    o.material = Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone();
                });
                this._setWireframe(root, false);
                this._toggleEdges(root, false);
                this._setPolygonOffset(root, false);
            },
            _setWireframe(root, on = true) {
                root.traverse(o => {
                    if (!o.isMesh) return;
                    (Array.isArray(o.material) ? o.material : [o.material]).forEach(m => m.wireframe = on);
                });
            },
            _setPolygonOffset(root, on = true, factor = 1, units = 1) {
                root.traverse(o => {
                    if (!o.isMesh) return;
                    (Array.isArray(o.material) ? o.material : [o.material]).forEach(m => {
                        m.polygonOffset = on;
                        m.polygonOffsetFactor = factor;
                        m.polygonOffsetUnits = units;
                    });
                });
            },
            _addEdges(mesh, THREE, threshold = 30) {
                if (mesh.userData.edges) return mesh.userData.edges;
                const edgesGeo = new THREE.EdgesGeometry(mesh.geometry, threshold);
                const edgesMat = new THREE.LineBasicMaterial({
                    transparent: true,
                    opacity: 0.6,
                    depthTest: false
                });
                const edges = new THREE.LineSegments(edgesGeo, edgesMat);
                edges.renderOrder = 999;
                mesh.add(edges);
                mesh.userData.edges = edges;
                return edges;
            },
            _toggleEdges(root, on = true, color = 0x000000) {
                const THREE = this.iges.THREE;
                root.traverse(o => {
                    if (!o.isMesh) return;
                    if (on) {
                        const e = this._addEdges(o, THREE, 30);
                        e.material.color = new THREE.Color(color);
                    } else if (o.userData.edges) {
                        o.remove(o.userData.edges);
                        o.userData.edges.geometry.dispose();
                        o.userData.edges.material.dispose();
                        o.userData.edges = null;
                    }
                });
            },
            setDisplayStyle(mode) {
                const root = this.iges.rootModel;
                if (!root) return;
                this._restoreMaterials(root);
                if (mode === 'shaded') return;
                if (mode === 'shaded-edges') {
                    this._setPolygonOffset(root, true, 1, 1);
                    this._toggleEdges(root, true, 0x000000);
                }
            },

            /* ===== Measure (2-click) ===== */
            toggleMeasure() {
                const M = this.iges.measure;
                M.enabled = !M.enabled;
                if (M.enabled && !M.group) {
                    const THREE = this.iges.THREE;
                    M.group = new THREE.Group();
                    this.iges.scene.add(M.group);
                    this._bindMeasureEvents(true);
                }
                if (!M.enabled) {
                    this._bindMeasureEvents(false);
                    M.p1 = M.p2 = null;
                }
            },
            clearMeasurements() {
                const g = this.iges.measure.group;
                if (!g) return;
                (g.children || []).forEach(ch => ch.userData?.dispose?.());
                g.clear();
            },
            _bindMeasureEvents(on) {
                const canvas = this.iges.renderer?.domElement;
                if (!canvas) return;
                if (on) {
                    this._onMeasureDblClick = (ev) => {
                        if (!this.iges.measure.enabled) return;
                        const p = this._pickPoint(ev);
                        if (!p) return;
                        const M = this.iges.measure;
                        if (!M.p1) {
                            M.p1 = p;
                            return;
                        }
                        M.p2 = p;
                        this._drawMeasurement(M.p1, M.p2);
                        M.p1 = M.p2 = null;
                    };
                    canvas.addEventListener('dblclick', this._onMeasureDblClick);
                } else {
                    canvas.removeEventListener('dblclick', this._onMeasureDblClick);
                }
            },
            _pickPoint(ev) {
                const {
                    THREE,
                    camera,
                    rootModel
                } = this.iges;
                const rect = this.iges.renderer.domElement.getBoundingClientRect();
                const mouse = new THREE.Vector2(
                    ((ev.clientX - rect.left) / rect.width) * 2 - 1,
                    -((ev.clientY - rect.top) / rect.height) * 2 + 1
                );
                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(mouse, camera);
                const hits = raycaster.intersectObjects(rootModel.children, true);
                if (!hits.length) return null;
                return hits[0].point.clone();
            },
            _drawMeasurement(a, b) {
                const THREE = this.iges.THREE;
                const group = new THREE.Group();

                const geom = new THREE.BufferGeometry().setFromPoints([a, b]);
                const line = new THREE.Line(geom, new THREE.LineBasicMaterial({}));
                group.add(line);

                const s = Math.max(0.4, a.distanceTo(b) / 160);
                const sg = new THREE.SphereGeometry(s, 16, 16);
                const sm = new THREE.MeshBasicMaterial({});
                const s1 = new THREE.Mesh(sg, sm);
                s1.position.copy(a);
                group.add(s1);
                const s2 = new THREE.Mesh(sg, sm);
                s2.position.copy(b);
                group.add(s2);

                const wrap = this.$refs.igesWrap;
                const lbl = document.createElement('div');
                lbl.className = 'measure-label';
                lbl.style.position = 'absolute';
                lbl.style.pointerEvents = 'none';
                lbl.style.font = '12px/1.2 monospace';
                lbl.style.padding = '2px 6px';
                lbl.style.background = 'rgba(0,0,0,.75)';
                lbl.style.color = '#fff';
                lbl.style.borderRadius = '4px';
                lbl.style.zIndex = '20';
                wrap.appendChild(lbl);

                const updateLabel = () => {
                    const mid = a.clone().add(b).multiplyScalar(0.5).project(this.iges.camera);
                    const w = wrap.clientWidth,
                        h = wrap.clientHeight;
                    const x = (mid.x * 0.5 + 0.5) * w;
                    const y = (-mid.y * 0.5 + 0.5) * h;
                    lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
                    lbl.textContent = `${a.distanceTo(b).toFixed(2)} mm`;
                };

                group.userData.update = updateLabel;
                group.userData.dispose = () => lbl.remove();
                updateLabel();

                this.iges.measure.group.add(group);
            },

            /* ===== Lifecycle ===== */
            init() {
                // Hapus event listener 'Escape' karena modal sudah tidak ada
                window.addEventListener('beforeunload', () => this.disposeCad());
            },

            /* ===== UI ===== */
            toggleSection(c) {
                const i = this.openSections.indexOf(c);
                if (i > -1) this.openSections.splice(i, 1);
                else this.openSections.push(c);
            },

            selectFile(file) {
                if (this.isCad(this.selectedFile?.name)) this.disposeCad();

                if (this.isTiff(this.selectedFile?.name)) {
                    this.tifError = '';
                    this.tifLoading = false;
                    if (this.$refs.tifImg) this.$refs.tifImg.src = '';
                }

                if (this.isHpgl(this.selectedFile?.name)) {
                    this.hpglError = '';
                    this.hpglLoading = false;
                    if (this.$refs.hpglCanvas) {
                        const c = this.$refs.hpglCanvas;
                        const ctx = c.getContext('2d');
                        ctx && ctx.clearRect(0, 0, c.width, c.height);
                    }
                }

                // reset zoom & pan setiap ganti file
                this.imageZoom = 1;
                this.panX = 0;
                this.panY = 0;

                this.selectedFile = {
                    ...file
                };

                this.$nextTick(() => {
                    if (this.isTiff(file?.name)) {
                        this.renderTiff(file.url);
                    } else if (this.isCad(file?.name)) {
                        this.renderCadOcct(file.url);
                    } else if (this.isHpgl(file?.name)) {
                        this.renderHpgl(file.url);
                    }
                });
            },

            // Fungsi addPkgActivity, isWaiting, approve/reject/rollback (Dihapus)

            /* ===== render CAD via occt-import-js ===== */
            async renderCadOcct(url) {
                if (!url) return;
                this.disposeCad();
                this.iges.loading = true;
                this.iges.error = '';

                try {
                    const THREE = await import('three');
                    const {
                        OrbitControls
                    } = await import('three/addons/controls/OrbitControls.js');
                    const bvh = await import('three-mesh-bvh');
                    THREE.Mesh.prototype.raycast = bvh.acceleratedRaycast;
                    THREE.BufferGeometry.prototype.computeBoundsTree = bvh.computeBoundsTree;
                    THREE.BufferGeometry.prototype.disposeBoundsTree = bvh.disposeBoundsTree;

                    const scene = new THREE.Scene();
                    scene.background = null;
                    const wrap = this.$refs.igesWrap;
                    const width = wrap?.clientWidth || 800;
                    const height = wrap?.clientHeight || 500;

                    const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 10000);
                    camera.position.set(250, 200, 250);

                    const renderer = new THREE.WebGLRenderer({
                        antialias: true,
                        alpha: true
                    });
                    renderer.setPixelRatio(window.devicePixelRatio || 1);
                    renderer.setSize(width, height);
                    wrap.appendChild(renderer.domElement);
                    wrap.style.position = 'relative';
                    wrap.style.overflow = 'hidden';

                    const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 0.8);
                    hemi.position.set(0, 200, 0);
                    scene.add(hemi);
                    const dir = new THREE.DirectionalLight(0xffffff, 0.9);
                    dir.position.set(150, 200, 100);
                    scene.add(dir);

                    const controls = new OrbitControls(camera, renderer.domElement);
                    controls.enableDamping = true;

                    const resp = await fetch(url, {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error('Gagal mengambil file CAD');
                    const buffer = await resp.arrayBuffer();
                    const file = new Uint8Array(buffer);

                    const occt = await window.occtimportjs();
                    const ext = (url.split('?')[0].split('#')[0].split('.').pop() || '').toLowerCase();
                    const res = (ext === 'stp' || ext === 'step') ? occt.ReadStepFile(file, null) : occt.ReadIgesFile(file, null);
                    if (!res?.success) throw new Error('OCCT gagal mem-parsing file');

                    const group = this._buildThreeFromOcct(res, THREE);
                    scene.add(group);

                    this.iges.rootModel = group;
                    this.iges.scene = scene;
                    this.iges.camera = camera;
                    this.iges.renderer = renderer;
                    this.iges.controls = controls;
                    this.iges.THREE = THREE;

                    this._cacheOriginalMaterials(group, THREE);

                    const box = new THREE.Box3().setFromObject(group);
                    const size = new THREE.Vector3();
                    box.getSize(size);
                    const center = new THREE.Vector3();
                    box.getCenter(center);
                    const maxDim = Math.max(size.x, size.y, size.z) || 100;
                    const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
                    camera.position.copy(center.clone().add(new THREE.Vector3(1, 1, 1).normalize().multiplyScalar(fitDist * 1.6)));
                    camera.near = Math.max(maxDim / 100, 0.1);
                    camera.far = Math.max(maxDim * 100, 1000);
                    camera.updateProjectionMatrix();
                    controls.target.copy(center);
                    controls.update();

                    const animate = () => {
                        controls.update();
                        renderer.render(scene, camera);
                        const g = this.iges.measure.group;
                        if (g) g.children.forEach(ch => ch.userData?.update?.());
                        this.iges.animId = requestAnimationFrame(animate);
                    };
                    animate();

                    this._onIgesResize = () => {
                        const w = this.$refs.igesWrap?.clientWidth || 800;
                        const h = this.$refs.igesWrap?.clientHeight || 500;
                        camera.aspect = w / h;
                        camera.updateProjectionMatrix();
                        renderer.setSize(w, h);
                    };
                    window.addEventListener('resize', this._onIgesResize);

                    this.setDisplayStyle('shaded-edges');

                } catch (e) {
                    console.error(e);
                    this.iges.error = e?.message || 'Failed to render CAD file';
                } finally {
                    this.iges.loading = false;
                }
            },
        }
    }
</script>
@endpush