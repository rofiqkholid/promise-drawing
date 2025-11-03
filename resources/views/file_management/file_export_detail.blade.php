@extends('layouts.app')
@section('title', 'Download Detail - File Manager')
@section('header-title', 'File Manager/Download Detail')

@section('content')

<div class="p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="exportDetail()" x-init="init()">

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
    <div class="lg:col-span-4 space-y-6">

      <!-- ===== Meta Card ===== -->
      <div x-ref="metaCard"
           class="self-start bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 md:justify-between">
            <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
              <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
              Package Metadata
            </h2>

            @php
              $backUrl = route('file-manager.export');
            @endphp
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
              <i class="fa-solid fa-arrow-left"></i>
              Back
            </a>
          </div>
        </div>

        <!-- Body: single line with dashes -->
        <div class="p-4">
          <p class="text-sm text-gray-900 dark:text-gray-100 truncate"
             x-text="metaLine()"
             :title="metaLine()"></p>
        </div>

        <!-- Footer (Download All) -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
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

      <!-- ===== File Groups (2D / 3D / ECN) ===== -->
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
            <div @click="selectFile(file)"
                :class="{'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-medium': selectedFile && selectedFile.name === file.name}"
                class="flex items-center justify-between p-3 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                role="button" tabindex="0" @keydown.enter="selectFile(file)">

                <div class="flex items-center min-w-0 pr-2">
                    <i class="fa-solid fa-file text-gray-500 dark:text-gray-400 mr-3"></i>
                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></span>
                </div>

                <button @click.stop="downloadFile(file)" class="flex-shrink-0 text-xs inline-flex items-center gap-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded">
                    <i class="fa-solid fa-download"></i>
                </button>
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
            <a x-show="selectedFile?.url" :href="selectedFile?.url" target="_blank" rel="noopener"
               class="inline-flex items-center px-3 py-1.5 text-xs text-gray-900 dark:text-gray-100 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
              <i class="fa-solid fa-up-right-from-square mr-2"></i> Open
            </a>
          </div>

          <!-- PREVIEW AREA (image/pdf/tiff/cad) -->
          <div class="preview-area bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center w-full">

            <!-- IMAGE -->
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

                <!-- TOOLBAR -->
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

            <!-- FALLBACK -->
            <template x-if="!isImage(selectedFile?.name) && !isPdf(selectedFile?.name) && !isTiff(selectedFile?.name) && !isCad(selectedFile?.name)">
              <div class="text-center">
                <i class="fa-solid fa-file text-6xl text-gray-400 dark:text-gray-500"></i>
                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Preview Unavailable</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">This file type is not supported for preview.</p>
              </div>
            </template>

          </div>
          <!-- /PREVIEW AREA -->
        </div>
      </div>
    </div>
    <!-- ================= /RIGHT COLUMN ================= -->
  </div>
  <!-- ================= /MAIN LAYOUT ================= -->
</div>

<style>
  [x-collapse] { @apply overflow-hidden transition-all duration-300 ease-in-out; }
  .preview-area { @apply bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 min-h-[20rem] flex items-center justify-center; }
  [x-cloak] { display: none !important; }
  .measure-label { user-select: none; white-space: nowrap; }
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
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/",
    "three-mesh-bvh": "https://unpkg.com/three-mesh-bvh@0.7.6/build/index.module.js"
  }
}
</script>

<!-- OCCT: parser STEP/IGES (WASM) -->
<script src="https://cdn.jsdelivr.net/npm/occt-import-js@0.0.23/dist/occt-import-js.js"></script>

<script>
  /* ========== Toast Utilities ========== */
  function detectTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    return isDark ? {
      mode: 'dark',
      bg: 'rgba(30, 41, 59, 0.95)',
      fg: '#E5E7EB',
      border: 'rgba(71, 85, 105, 0.5)',
      progress: 'rgba(255,255,255,.9)',
      icon: { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' }
    } : {
      mode: 'light',
      bg: 'rgba(255, 255, 255, 0.98)',
      fg: '#0f172a',
      border: 'rgba(226, 232, 240, 1)',
      progress: 'rgba(15,23,42,.8)',
      icon: { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' }
    };
  }
  const BaseToast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false,
    timer: 2600, timerProgressBar: true,
    showClass: { popup: 'swal2-animate-toast-in' },
    hideClass: { popup: 'swal2-animate-toast-out' },
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
  function renderToast({ icon = 'success', title = 'Success', text = '' } = {}) {
    const t = detectTheme();
    BaseToast.fire({
      icon, title, text,
      iconColor: t.icon[icon] || t.icon.success,
      background: t.bg, color: t.fg,
      customClass: { popup: 'swal2-toast border', title: '', timerProgressBar: '' },
      didOpen: (toast) => {
        const bar = toast.querySelector('.swal2-timer-progress-bar'); if (bar) bar.style.background = t.progress;
        const popup = toast.querySelector('.swal2-popup'); if (popup) popup.style.borderColor = t.border;
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });
  }
  function toastSuccess(title='Berhasil', text='Operasi berhasil dijalankan.') { renderToast({icon:'success', title, text}); }
  function toastError(title='Gagal', text='Terjadi kesalahan.') { BaseToast.update({timer:3400}); renderToast({icon:'error', title, text}); BaseToast.update({timer:2600}); }
  function toastWarning(title='Peringatan', text='Periksa kembali data Anda.') { renderToast({icon:'warning', title, text}); }
  function toastInfo(title='Informasi', text='') { renderToast({icon:'info', title, text}); }
  window.toastSuccess = toastSuccess; window.toastError = toastError; window.toastWarning = toastWarning; window.toastInfo = toastInfo;

  /* ========== Alpine Component ========== */
  function exportDetail() {
    return {
      exportId: JSON.parse(`@json($exportId)`),
      pkg: JSON.parse(`@json($detail)`),

      selectedFile: null,
      openSections: [],

      // TIFF state
      tifLoading: false, tifError: '',

      // CAD viewer state
      iges: {
        renderer: null, scene: null, camera: null, controls: null, animId: 0,
        loading: false, error: '',
        rootModel: null,
        THREE: null,
        measure: { enabled: false, group: null, p1: null, p2: null }
      },
      _onIgesResize: null,

      /* ===== Helpers jenis file ===== */
      extOf(name){ const i = (name||'').lastIndexOf('.'); return i>-1 ? (name||'').slice(i+1).toLowerCase() : ''; },
      isImage(name) { return ['png','jpg','jpeg','webp','gif','bmp'].includes(this.extOf(name)); },
      isPdf(name)   { return this.extOf(name) === 'pdf'; },
      isTiff(name)  { return ['tif','tiff'].includes(this.extOf(name)); },
      isCad(name)   { return ['igs','iges','stp','step'].includes(this.extOf(name)); },
      pdfSrc(u) { return u; },

      /* ===== TIFF renderer ===== */
      async renderTiff(url) {
        if (!url || !window.UTIF) return;
        this.tifLoading = true; this.tifError = '';
        try {
          const resp = await fetch(url, { cache:'no-store', credentials:'same-origin' });
          if (!resp.ok) throw new Error('Gagal mengambil file TIFF');
          const buf = await resp.arrayBuffer();
          const ifds = UTIF.decode(buf);
          UTIF.decodeImages(buf, ifds);
          if (!ifds?.length) throw new Error('TIFF tidak memiliki frame');
          const first = ifds[0];
          const rgba = UTIF.toRGBA8(first);
          const w = first.width, h = first.height;
          const canvas = this.$refs.tifCanvas; if (!canvas) throw new Error('Canvas TIFF tidak ditemukan');
          const ctx = canvas.getContext('2d');
          canvas.width = w; canvas.height = h;
          const imgData = new ImageData(new Uint8ClampedArray(rgba), w, h);
          ctx.putImageData(imgData, 0, 0);
        } catch (e) { console.error(e); this.tifError = e?.message || 'Gagal render TIFF'; }
        finally { this.tifLoading = false; }
      },

      /* ===== OCCT result -> THREE meshes ===== */
      _buildThreeFromOcct(result, THREE) {
        const group = new THREE.Group();
        const meshes = result.meshes || [];
        for (let i=0; i<meshes.length; i++) {
          const m = meshes[i];
          const g = new THREE.BufferGeometry();
          g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
          if (m.attributes.normal?.array) g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
          if (m.index?.array) g.setIndex(m.index.array);
          let color = 0xcccccc;
          if (m.color && m.color.length === 3) color = (m.color[0] << 16) | (m.color[1] << 8) | (m.color[2]);
          const mat = new THREE.MeshStandardMaterial({ color, metalness: 0, roughness: 1, side: THREE.DoubleSide });
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
          const { renderer, scene, controls } = this.iges || {};
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
          if (wrap) while (wrap.firstChild) wrap.removeChild(wrap.firstChild);
        } catch {}
        this.iges = {
          renderer: null, scene: null, camera: null, controls: null, animId: 0,
          loading: false, error: '',
          rootModel: null, THREE: null,
          measure: { enabled: false, group: null, p1: null, p2: null }
        };
        this._onIgesResize = null;
      },

      /* ===== Meta line formatter ===== */
      metaLine() {
        const m = this.pkg?.metadata || {};
        // urutan: Customer - Model - Part No - Revision
        return [m.customer, m.model, m.part_no, m.revision]
          .filter(v => v && String(v).trim().length > 0)
          .join(' - ');
      },

      /* ===== Display Styles / Edges ===== */
      _oriMats: new Map(),
      _cacheOriginalMaterials(root, THREE){
        root.traverse(o=>{
          if (o.isMesh && !this._oriMats.has(o)) {
            const m = o.material;
            this._oriMats.set(o, Array.isArray(m) ? m.map(mm=>mm.clone()) : m.clone());
          }
        });
      },
      _restoreMaterials(root){
        root.traverse(o=>{
          if (!o.isMesh) return;
          const m = this._oriMats.get(o); if (!m) return;
          o.material = Array.isArray(m) ? m.map(mm=>mm.clone()) : m.clone();
        });
        this._setWireframe(root, false);
        this._toggleEdges(root, false);
        this._setPolygonOffset(root, false);
      },
      _setWireframe(root, on=true){
        root.traverse(o=>{
          if (!o.isMesh) return;
          (Array.isArray(o.material)?o.material:[o.material]).forEach(m=> m.wireframe = on);
        });
      },
      _setPolygonOffset(root, on=true, factor=1, units=1){
        root.traverse(o=>{
          if (!o.isMesh) return;
          (Array.isArray(o.material)?o.material:[o.material]).forEach(m=>{
            m.polygonOffset = on; m.polygonOffsetFactor = factor; m.polygonOffsetUnits = units;
          });
        });
      },
      _addEdges(mesh, THREE, threshold=30){
        if (mesh.userData.edges) return mesh.userData.edges;
        const edgesGeo = new THREE.EdgesGeometry(mesh.geometry, threshold);
        const edgesMat = new THREE.LineBasicMaterial({ transparent:true, opacity:0.6, depthTest:false });
        const edges = new THREE.LineSegments(edgesGeo, edgesMat);
        edges.renderOrder = 999;
        mesh.add(edges);
        mesh.userData.edges = edges;
        return edges;
      },
      _toggleEdges(root, on=true, color=0x000000){
        const THREE = this.iges.THREE;
        root.traverse(o=>{
          if (!o.isMesh) return;
          if (on){
            const e = this._addEdges(o, THREE, 30);
            e.material.color = new THREE.Color(color);
          } else if (o.userData.edges){
            o.remove(o.userData.edges);
            o.userData.edges.geometry.dispose();
            o.userData.edges.material.dispose();
            o.userData.edges = null;
          }
        });
      },
      setDisplayStyle(mode){
        const root = this.iges.rootModel; if (!root) return;
        this._restoreMaterials(root);
        if (mode === 'shaded') return;
        if (mode === 'shaded-edges'){
          this._setPolygonOffset(root, true, 1, 1);
          this._toggleEdges(root, true, 0x000000);
          return;
        }
      },

      /* ===== Measure (2-click) ===== */
      toggleMeasure(){
        const M = this.iges.measure;
        M.enabled = !M.enabled;
        if (M.enabled && !M.group) {
          const THREE = this.iges.THREE;
          M.group = new THREE.Group();
          this.iges.scene.add(M.group);
          this._bindMeasureEvents(true);
        }
        if (!M.enabled){
          this._bindMeasureEvents(false);
          M.p1 = M.p2 = null;
        }
      },
      clearMeasurements(){
        const g = this.iges.measure.group;
        if (!g) return;
        (g.children||[]).forEach(ch => ch.userData?.dispose?.());
        g.clear();
      },
      _bindMeasureEvents(on){
        const canvas = this.iges.renderer?.domElement; if (!canvas) return;
        if (on){
          this._onMeasureDblClick = (ev)=>{
            if (!this.iges.measure.enabled) return;
            const p = this._pickPoint(ev); if (!p) return;
            const M = this.iges.measure;
            if (!M.p1) { M.p1 = p; return; }
            M.p2 = p; this._drawMeasurement(M.p1, M.p2); M.p1 = M.p2 = null;
          };
          canvas.addEventListener('dblclick', this._onMeasureDblClick);
        } else {
          canvas.removeEventListener('dblclick', this._onMeasureDblClick);
        }
      },
      _pickPoint(ev){
        const { THREE, camera, rootModel } = this.iges;
        const rect = this.iges.renderer.domElement.getBoundingClientRect();
        const mouse = new THREE.Vector2(
          ((ev.clientX - rect.left)/rect.width)*2 - 1,
          -((ev.clientY - rect.top)/rect.height)*2 + 1
        );
        const raycaster = new THREE.Raycaster();
        raycaster.setFromCamera(mouse, camera);
        const hits = raycaster.intersectObjects(rootModel.children, true);
        if (!hits.length) return null;
        return hits[0].point.clone();
      },
      _drawMeasurement(a, b){
        const THREE = this.iges.THREE;
        const group = new THREE.Group();

        // line
        const geom = new THREE.BufferGeometry().setFromPoints([a,b]);
        const line = new THREE.Line(geom, new THREE.LineBasicMaterial({}));
        group.add(line);

        // end points
        const s = Math.max(0.4, a.distanceTo(b)/160);
        const sg = new THREE.SphereGeometry(s, 16, 16);
        const sm = new THREE.MeshBasicMaterial({});
        const s1 = new THREE.Mesh(sg, sm); s1.position.copy(a); group.add(s1);
        const s2 = new THREE.Mesh(sg, sm); s2.position.copy(b); group.add(s2);

        // label (DOM)
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

        const updateLabel = ()=>{
          const mid = a.clone().add(b).multiplyScalar(0.5).project(this.iges.camera);
          const w = wrap.clientWidth, h = wrap.clientHeight;
          const x = (mid.x * 0.5 + 0.5) * w;
          const y = (-mid.y * 0.5 + 0.5) * h;
          lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
          lbl.textContent = `${a.distanceTo(b).toFixed(2)} mm`;
        };

        group.userData.update = updateLabel;
        group.userData.dispose = ()=> lbl.remove();
        updateLabel();

        this.iges.measure.group.add(group);
      },

      /* ===== Lifecycle ===== */
      init() {
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
        if (this.isTiff(this.selectedFile?.name)) { this.tifError = ''; this.tifLoading = false; }

        this.selectedFile = { ...file };

        if (this.isTiff(file?.name)) this.renderTiff(file.url);
        else if (this.isCad(file?.name)) this.renderCadOcct(file.url);
      },

      /* ===== Download ===== */
      downloadFile(file) {
        if (file && file.file_id) {
            window.location.href = `/download/file/${file.file_id}`;
        } else {
            toastError('Error', 'File ID not found for download.');
        }
      },

      downloadPackage() {
        if (this.exportId) {
            window.location.href = `/download/package/${this.exportId}`;
        } else {
            toastError('Error', 'Package ID not found for download.');
        }
      },

      /* ===== render CAD via occt-import-js ===== */
      async renderCadOcct(url) {
        if (!url) return;
        this.disposeCad();
        this.iges.loading = true; this.iges.error = '';

        try {
          const THREE = await import('three');
          const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');
          const bvh = await import('three-mesh-bvh');
          THREE.Mesh.prototype.raycast = bvh.acceleratedRaycast;
          THREE.BufferGeometry.prototype.computeBoundsTree = bvh.computeBoundsTree;
          THREE.BufferGeometry.prototype.disposeBoundsTree  = bvh.disposeBoundsTree;

          // scene & camera
          const scene = new THREE.Scene();
          scene.background = null;
          const wrap = this.$refs.igesWrap;
          const width = wrap?.clientWidth || 800, height = wrap?.clientHeight || 500;

          const camera = new THREE.PerspectiveCamera(50, width/height, 0.1, 10000);
          camera.position.set(250, 200, 250);

          const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
          renderer.setPixelRatio(window.devicePixelRatio || 1);
          renderer.setSize(width, height);
          wrap.appendChild(renderer.domElement);
          wrap.style.position = 'relative';
          wrap.style.overflow  = 'hidden';

          // lights
          const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 0.8); hemi.position.set(0, 200, 0); scene.add(hemi);
          const dir  = new THREE.DirectionalLight(0xffffff, 0.9); dir.position.set(150, 200, 100); scene.add(dir);

          // controls
          const controls = new OrbitControls(camera, renderer.domElement);
          controls.enableDamping = true;

          // fetch file
          const resp = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });
          if (!resp.ok) throw new Error('Gagal mengambil file CAD');
          const buffer = await resp.arrayBuffer();
          const file = new Uint8Array(buffer);

          // parse dengan occt
          const occt = await window.occtimportjs(); // dari <script> CDN
          const ext = (url.split('?')[0].split('#')[0].split('.').pop() || '').toLowerCase();
          const res = (ext === 'stp' || ext === 'step') ? occt.ReadStepFile(file, null) : occt.ReadIgesFile(file, null);
          if (!res?.success) throw new Error('OCCT gagal mem-parsing file');

          // build meshes -> scene
          const group = this._buildThreeFromOcct(res, THREE);
          scene.add(group);

          // simpan refs
          this.iges.rootModel = group;
          this.iges.scene = scene;
          this.iges.camera = camera;
          this.iges.renderer = renderer;
          this.iges.controls = controls;
          this.iges.THREE = THREE;

          // cache material asli
          this._cacheOriginalMaterials(group, THREE);

          // auto-fit kamera
          const box = new THREE.Box3().setFromObject(group);
          const size = new THREE.Vector3(); box.getSize(size);
          const center = new THREE.Vector3(); box.getCenter(center);
          const maxDim = Math.max(size.x, size.y, size.z) || 100;
          const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
          camera.position.copy(center.clone().add(new THREE.Vector3(1,1,1).normalize().multiplyScalar(fitDist * 1.6)));
          camera.near = Math.max(maxDim / 100, 0.1);
          camera.far  = Math.max(maxDim * 100, 1000);
          camera.updateProjectionMatrix();
          controls.target.copy(center);
          controls.update();

          // render loop + update label measure
          const animate = () => {
            controls.update();
            renderer.render(scene, camera);
            const g = this.iges.measure.group;
            if (g) g.children.forEach(ch => ch.userData?.update?.());
            this.iges.animId = requestAnimationFrame(animate);
          };
          animate();

          // resize
          this._onIgesResize = () => {
            const w = this.$refs.igesWrap?.clientWidth || 800;
            const h = this.$refs.igesWrap?.clientHeight || 500;
            camera.aspect = w / h; camera.updateProjectionMatrix();
            renderer.setSize(w, h);
          };
          window.addEventListener('resize', this._onIgesResize);

          // default style
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
