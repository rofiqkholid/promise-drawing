@extends('layouts.app')
@section('title', 'Download Detail - File Manager')
@section('header-title', 'File Manager/Download Detail')

@section('content')
<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="downloadDetail()" x-init="init()">
    <!-- Header & Metadata -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fa-solid fa-box-archive mr-3 text-blue-600"></i> Package Metadata
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('file-manager.export.history', $package_id) }}" class="inline-flex items-center text-sm px-3 py-2 rounded-md ...">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to History
                </a>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Customer</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.customer"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Model</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.model"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Part No</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.part_no"></dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 font-medium">Revision</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100" x-text="pkg.metadata.revision"></dd>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 rounded-b-lg">
            <div class="flex justify-end">
                <button
                    @click="downloadPackage()"
                    class="inline-flex items-center text-sm px-3 py-2 rounded-md
                           bg-blue-600 text-white hover:bg-blue-700
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fa-solid fa-download mr-2"></i>
                    Download All Files
                </button>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar: File Groups -->
        <div class="lg:col-span-1 space-y-6">
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
                        <div class="group flex items-center justify-between p-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div @click="selectFile(file)" :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}" class="flex items-center rounded-md cursor-pointer pr-2">
                                <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3 group-hover:text-blue-500"></i>
                                <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
                            </div>
                            <button @click.stop="downloadFile(file)" class="text-xs inline-flex items-center gap-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded">
                                <i class="fa-solid fa-download"></i> Download
                            </button>
                        </div>
                    </template>
                    <template x-if="(pkg.files['{{$category}}'] || []).length === 0">
                        <p class="p-3 text-center text-xs text-gray-500 dark:text-gray-400">No files available.</p>
                    </template>
                </div>
            </div>
            @php
            }
            @endphp

            {{ renderFileGroup('2D Drawings', 'fa-drafting-compass', '2d') }}
            {{ renderFileGroup('3D Models', 'fa-cubes', '3d') }}
            {{ renderFileGroup('ECN / Documents', 'fa-file-lines', 'ecn') }}
        </div>

        <!-- Main Panel: Preview -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- No File Selected -->
                <div x-show="!selectedFile" x-cloak class="flex flex-col items-center justify-center h-96 p-6 bg-gray-50 dark:bg-gray-900/50 text-center">
                    <i class="fa-solid fa-hand-pointer text-5xl text-gray-400 dark:text-gray-500"></i>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Select a File</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose a file from the left panel to review.</p>
                </div>

                <!-- File Preview -->
                <div x-show="selectedFile" x-transition.opacity x-cloak class="p-6">
                    <!-- Header with Open in new tab -->
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedFile?.name"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Revision: <span x-text="pkg.metadata.revision"></span></p>
                        </div>
                        <a x-show="selectedFile?.url" :href="selectedFile?.url" target="_blank"
                            class="inline-flex items-center px-3 py-1.5 text-xs rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fa-solid fa-up-right-from-square mr-2"></i> Open
                        </a>
                    </div>

                    <!-- >>> PREVIEW AREA (image/pdf/tiff/cad) -->
                    <div class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center w-full">

                        <!-- IMAGE: jpg/jpeg/png/webp/gif/bmp -->
                        <template x-if="isImage(selectedFile?.name)">
                            <img :src="selectedFile?.url" alt="File Preview" class="max-w-full max-h-[70vh] object-contain rounded" loading="lazy">
                        </template>

                        <!-- PDF -->
                        <template x-if="isPdf(selectedFile?.name)">
                            <iframe
                                :src="pdfSrc(selectedFile?.url)"
                                class="w-full h-[70vh] rounded-md border border-gray-200 dark:border-gray-700"
                                title="PDF preview"></iframe>
                        </template>

                        <!-- TIFF -->
                        <template x-if="isTiff(selectedFile?.name)">
                            <div class="w-full">
                                <canvas x-ref="tifCanvas" class="w-full max-h-[70vh] object-contain bg-black/5 rounded"></canvas>
                                <div x-show="tifLoading" class="text-xs text-gray-500 mt-2">Rendering TIFF…</div>
                                <div x-show="tifError" class="text-xs text-red-600 mt-2" x-text="tifError"></div>
                            </div>
                        </template>

                        <!-- CAD: IGES / STEP via occt-import-js -->
                        <template x-if="isCad(selectedFile?.name)">
                            <div class="w-full">
                                <div x-ref="igesWrap" class="w-full h-[70vh] rounded border border-gray-200 dark:border-gray-700 bg-black/5"></div>
                                <div x-show="iges.loading" class="text-xs text-gray-500 mt-2">Loading CAD…</div>
                                <div x-show="iges.error" class="text-xs text-red-600 mt-2" x-text="iges.error"></div>
                            </div>
                        </template>

                        <!-- FALLBACK -->
                        <template x-if="!isImage(selectedFile?.name) && !isPdf(selectedFile?.name) && !isTiff(selectedFile?.name) && !isCad(selectedFile?.name)">
                            <div class="text-center">
                                <i class="fa-solid fa-file text-6xl text-gray-400 dark:text-gray-500"></i>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Preview Unavailable</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">This file type is not supported for preview.</p>
                            </div>
                        </template>

                    </div>
                    <!-- <<< END PREVIEW AREA -->

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
</style>
@endsection

@push('scripts')
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Alpine collapse (untuk x-collapse) -->
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

<!-- UTIF.js untuk render TIFF -->
<script src="https://unpkg.com/utif@3.1.0/dist/UTIF.min.js"></script>

<!-- ES Module shims + Import Map untuk Three.js (module) -->
<script async src="https://unpkg.com/es-module-shims@1.10.0/dist/es-module-shims.js"></script>
<script type="importmap">
  {
  "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
  }
}
</script>

<!-- OCCT: parser STEP/IGES (WASM) -->
<script src="https://cdn.jsdelivr.net/npm/occt-import-js@0.0.23/dist/occt-import-js.js"></script>

<script>
  /* util toast — tetap punyamu */
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

  function downloadDetail() {
    return {
      exportId: JSON.parse(`@json($exportId)`),
      pkg: JSON.parse(`@json($detail)`),

      selectedFile: null,
      openSections: [],

      // TIFF state
      tifLoading: false,
      tifError: '',

      // CAD viewer state (pakai three.js + occt)
      iges: {
        renderer: null,
        scene: null,
        camera: null,
        controls: null,
        animId: 0,
        loading: false,
        error: ''
      },
      _onIgesResize: null,

      // ==== helpers jenis file ====
      isImage(name) {
        const ext = (name || '').split('.').pop().toLowerCase();
        return ['png', 'jpg', 'jpeg', 'webp', 'gif', 'bmp'].includes(ext);
      },
      isPdf(name) {
        return (name || '').split('.').pop().toLowerCase() === 'pdf';
      },
      isTiff(name) {
        const ext = (name || '').split('.').pop().toLowerCase();
        return ext === 'tif' || ext === 'tiff';
      },
      isCad(name) {
        const ext = (name || '').split('.').pop().toLowerCase();
        return ['igs', 'iges', 'stp', 'step'].includes(ext);
      },
      pdfSrc(u) {
        return u;
      },

      // ==== TIFF renderer ====
      async renderTiff(url) {
        if (!url || !window.UTIF) return;
        this.tifLoading = true;
        this.tifError = '';
        try {
          const resp = await fetch(url, {
            cache: 'no-store',
            credentials: 'same-origin'
          });
          if (!resp.ok) throw new Error('Gagal mengambil file TIFF');
          const buf = await resp.arrayBuffer();
          const ifds = UTIF.decode(buf);
          UTIF.decodeImages(buf, ifds);
          if (!ifds?.length) throw new Error('TIFF tidak memiliki frame');
          const first = ifds[0];
          const rgba = UTIF.toRGBA8(first);
          const w = first.width,
            h = first.height;
          const canvas = this.$refs.tifCanvas;
          if (!canvas) throw new Error('Canvas TIFF tidak ditemukan');
          const ctx = canvas.getContext('2d');
          canvas.width = w;
          canvas.height = h;
          const imgData = new ImageData(new Uint8ClampedArray(rgba), w, h);
          ctx.putImageData(imgData, 0, 0);
        } catch (e) {
          console.error(e);
          this.tifError = e?.message || 'Gagal render TIFF';
        } finally {
          this.tifLoading = false;
        }
      },

      // ==== build THREE.Group dari hasil OCCT ====
      _buildThreeFromOcct(result, THREE) {
        const group = new THREE.Group();
        const meshes = result.meshes || [];
        for (let i = 0; i < meshes.length; i++) {
          const m = meshes[i];
          const g = new THREE.BufferGeometry();
          g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
          if (m.attributes.normal?.array) {
            g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
          }
          if (m.index?.array) g.setIndex(m.index.array);
          let color = 0xcccccc;
          if (m.color && m.color.length === 3) {
            color = (m.color[0] << 16) | (m.color[1] << 8) | (m.color[2]);
          }
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

      // ==== cleanup viewer CAD ====
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
          error: ''
        };
        this._onIgesResize = null;
      },

      // ==== render CAD via occt-import-js ====
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

          // scene & camera
          const scene = new THREE.Scene();
          scene.background = null;
          const wrap = this.$refs.igesWrap;
          const width = wrap?.clientWidth || 800,
            height = wrap?.clientHeight || 500;

          const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 10000);
          camera.position.set(250, 200, 250);

          // renderer
          const renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true
          });
          renderer.setPixelRatio(window.devicePixelRatio || 1);
          renderer.setSize(width, height);
          wrap.appendChild(renderer.domElement);

          // lights
          const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 0.8);
          hemi.position.set(0, 200, 0);
          scene.add(hemi);
          const dir = new THREE.DirectionalLight(0xffffff, 0.9);
          dir.position.set(150, 200, 100);
          scene.add(dir);

          // controls
          const controls = new OrbitControls(camera, renderer.domElement);
          controls.enableDamping = true;

          // fetch file
          const resp = await fetch(url, {
            cache: 'no-store',
            credentials: 'same-origin'
          });
          if (!resp.ok) throw new Error('Gagal mengambil file CAD');
          const buffer = await resp.arrayBuffer();
          const file = new Uint8Array(buffer);

          // parse dengan occt
          const occt = await window.occtimportjs(); // dari <script> CDN
          const ext = (url.split('?')[0].split('#')[0].split('.').pop() || '').toLowerCase();
          const res = (ext === 'stp' || ext === 'step') ? occt.ReadStepFile(file, null) :
            occt.ReadIgesFile(file, null);
          if (!res?.success) throw new Error('OCCT gagal mem-parsing file');

          // build meshes -> scene
          const group = this._buildThreeFromOcct(res, THREE);
          scene.add(group);

          // auto-fit kamera
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
            this.iges.animId = requestAnimationFrame(animate);
          };
          animate();

          // simpan refs & resize
          this.iges.renderer = renderer;
          this.iges.scene = scene;
          this.iges.camera = camera;
          this.iges.controls = controls;
          this._onIgesResize = () => {
            const w = this.$refs.igesWrap?.clientWidth || 800;
            const h = this.$refs.igesWrap?.clientHeight || 500;
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
          };
          window.addEventListener('resize', this._onIgesResize);

        } catch (e) {
          console.error(e);
          this.iges.error = e?.message || 'Failed to render CAD file';
        } finally {
          this.iges.loading = false;
        }
      },

      // ==== lifecycle ====
      init() {
        window.addEventListener('beforeunload', () => this.disposeCad());
      },

      // ==== UI ====
      toggleSection(c) {
        const i = this.openSections.indexOf(c);
        if (i > -1) this.openSections.splice(i, 1);
        else this.openSections.push(c);
      },

      selectFile(file) {
        // cleanup preview stateful sebelumnya
        if (this.isCad(this.selectedFile?.name)) this.disposeCad();
        if (this.isTiff(this.selectedFile?.name)) {
          this.tifError = '';
          this.tifLoading = false;
        }

        this.selectedFile = {
          ...file
        };

        if (this.isTiff(file?.name)) this.renderTiff(file.url);
        else if (this.isCad(file?.name)) this.renderCadOcct(file.url);
      },

      // download satu file
      downloadFile(file) {
        if (file && file.file_id) {
            window.location.href = `/download/file/${file.file_id}`; // Assuming a route for file download
        } else {
            toastError('Error', 'File ID not found for download.');
        }
      },

      // download semua file di revisi aktif (zip, misalnya)
      downloadPackage() {
        if (this.exportId) {
            window.location.href = `/download/package/${this.exportId}`; // Assuming a route for package download
        } else {
            toastError('Error', 'Package ID not found for download.');
        }
      },
    }
  }
</script>
@endpush
