// file-previewer.js
// Reusable mixin preview file (image / PDF / TIFF / HPGL / CAD STEP-IGES) untuk Alpine

function filePreviewMixin(initialFiles = {}, initialStampFormat = null, initialStamp = null) {
  // private untuk PDF.js
  let pdfDoc = null;

  return {
    // ---- input dari luar ----
    files: initialFiles,          // struktur: { 2d: [...], 3d: [...], ecn: [...] } atau lainnya
    stampFormat: initialStampFormat,
    stamp: initialStamp,

    // ---- state umum preview ----
    selectedFile: null,

    // TIFF
    tifLoading: false,
    tifError: '',

    // HPGL
    hpglLoading: false,
    hpglError: '',

    // PDF
    pdfLoading: false,
    pdfError: '',
    pdfPageNum: 1,
    pdfNumPages: 1,
    pdfScale: 1.0,

    // ZOOM + PAN
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

    // CAD (OCCT + Three.js)
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
    _oriMats: new Map(),

    /* ===== Helper jenis file ===== */
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

    findFileByNameInsensitive(name) {
      if (!name) return null;
      const target = name.toLowerCase();
      const groups = this.files || {};

      for (const key of Object.keys(groups)) {
        const list = groups[key] || [];
        for (const f of list) {
          const n = (f.name || '').toLowerCase();
          if (n === target || n.endsWith('/' + target) || n.endsWith('\\' + target)) {
            return f;
          }
        }
      }
      return null;
    },

    _findIgesSibling(mainFile) {
      if (!mainFile) return null;
      const name = mainFile.name || '';
      const base = name.replace(/\.(stp|step)$/i, '');

      const candidates = [];
      if (base) {
        candidates.push(base + '.igs', base + '.iges');
      }
      // banyak STEP CATIA pakai nama temp.igs
      candidates.push('temp.igs', 'temp.iges');

      // 1) coba nama kandidat di semua group
      for (const cand of candidates) {
        const f = this.findFileByNameInsensitive(cand);
        if (f) return f;
      }

      // 2) fallback: ambil IGES apa pun yang ada di paket
      const groups = this.files || {};
      for (const key of Object.keys(groups)) {
        const list = groups[key] || [];
        const hit = list.find(f => /\.(igs|iges)$/i.test(f.name || ''));
        if (hit) return hit;
      }

      return null;
    },

    /* ===== Stamp text ===== */
    formatStampDate(d) {
      return d || '';
    },
    stampCenterOriginal() {
      return 'ORIGINAL';
    },
    stampCenterObsolete() {
      return 'OBSOLETE';
    },
    stampTopLine() {
      const d = this.stamp?.receipt_date;
      if (!d) return '';
      const label = (this.stampFormat && this.stampFormat.prefix)
        ? this.stampFormat.prefix
        : 'DATE RECEIVED';
      return `${label} : ${this.formatStampDate(d)}`;
    },
    stampBottomLine() {
      const d = this.stamp?.upload_date;
      if (!d) return '';
      const label = (this.stampFormat && this.stampFormat.suffix)
        ? this.stampFormat.suffix
        : 'DATE UPLOADED';
      return `${label} : ${this.formatStampDate(d)}`;
    },

    /* ===== ZOOM + PAN ===== */
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
        this.imageZoom = Math.min(this.imageZoom + step, this.maxZoom);
      } else if (delta > 0) {
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

    /* ===== TIFF renderer ===== */
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

    /* ===== PDF renderer (pdf.js) ===== */
    async renderPdf(url) {
      if (!url || !window['pdfjsLib']) return;

      this.pdfLoading = true;
      this.pdfError = '';
      pdfDoc = null;
      this.pdfPageNum = 1;
      this.pdfScale = 1.0;

      try {
        await this.$nextTick();
        const canvas = this.$refs.pdfCanvas;
        if (!canvas) throw new Error('Canvas PDF tidak ditemukan');

        const loadingTask = window.pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;
        pdfDoc = pdf;
        this.pdfNumPages = pdf.numPages;

        await this.renderPdfPage();
      } catch (e) {
        console.error(e);
        this.pdfError = e?.message || 'Gagal render PDF';
      } finally {
        this.pdfLoading = false;
      }
    },

    async renderPdfPage() {
      if (!pdfDoc) return;
      try {
        const page = await pdfDoc.getPage(this.pdfPageNum);
        const viewport = page.getViewport({ scale: this.pdfScale });

        await this.$nextTick();
        const canvas = this.$refs.pdfCanvas;
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
          canvasContext: ctx,
          viewport
        };
        await page.render(renderContext).promise;
      } catch (e) {
        console.error(e);
        this.pdfError = e?.message || 'Gagal render halaman PDF';
      }
    },

    /* ===== HPGL renderer ===== */
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

        const commands = text.replace(/\s+/g, '').split(';');

        let penDown = false;
        let x = 0, y = 0;
        const segments = [];
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

        const addPoint = (nx, ny) => {
          if (penDown) {
            segments.push({ x1: x, y1: y, x2: nx, y2: ny });
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
            // ignore pen select
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

        const dpr = window.devicePixelRatio || 1;
        const logicalScale = 4 * dpr;
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
        const scale = 0.9 * Math.min(w / dx, h / dy);
        const offX = (w - dx * scale) / 2 - minX * scale;
        const offY = (h - dy * scale) / 2 + maxY * scale;

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
      } catch (e) {
        console.error(e);
      }
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

    /* ===== Display Styles / Edges ===== */
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
      const { THREE, camera, rootModel } = this.iges;
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

    /* ===== pilih file (dipanggil dari list kiri) ===== */
    selectFile(file) {
      // bersihkan CAD sebelumnya
      if (this.isCad(this.selectedFile?.name)) this.disposeCad();

      // reset TIFF
      if (this.isTiff(this.selectedFile?.name)) {
        this.tifError = '';
        this.tifLoading = false;
        if (this.$refs.tifImg) this.$refs.tifImg.src = '';
      }

      // reset HPGL
      if (this.isHpgl(this.selectedFile?.name)) {
        this.hpglError = '';
        this.hpglLoading = false;
        if (this.$refs.hpglCanvas) {
          const c = this.$refs.hpglCanvas;
          const ctx = c.getContext('2d');
          ctx && ctx.clearRect(0, 0, c.width, c.height);
        }
      }

      // reset PDF
      if (this.isPdf(this.selectedFile?.name)) {
        this.pdfError = '';
        this.pdfLoading = false;
        pdfDoc = null;
        if (this.$refs.pdfCanvas) {
          const c = this.$refs.pdfCanvas;
          const ctx = c.getContext('2d');
          ctx && ctx.clearRect(0, 0, c.width, c.height);
        }
      }

      // reset zoom/pan
      this.imageZoom = 1;
      this.panX = 0;
      this.panY = 0;

      this.selectedFile = { ...file };

      this.$nextTick(() => {
        if (this.isTiff(file?.name)) {
          this.renderTiff(file.url);
        } else if (this.isCad(file?.name)) {
          this.renderCadOcct(file);
        } else if (this.isHpgl(file?.name)) {
          this.renderHpgl(file.url);
        } else if (this.isPdf(file?.name)) {
          this.renderPdf(file.url);
        }
      });
    },

    /* ===== render CAD via occt-import-js (STEP/IGES + fallback IGES) ===== */
    async renderCadOcct(fileObj) {
      const url = fileObj?.url;
      if (!url) return;

      this.disposeCad();
      this.iges.loading = true;
      this.iges.error = '';

      try {
        const THREE = await import('three');
        const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');
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

        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
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

        // ---- ambil file utama (STEP/IGES) ----
        const resp = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });
        if (!resp.ok) throw new Error('Gagal mengambil file CAD');
        const mainBuf = new Uint8Array(await resp.arrayBuffer());

        console.log('CAD size (bytes):', mainBuf.byteLength);

        const occt = await window.occtimportjs();
        const ext = (url.split('?')[0].split('#')[0].split('.').pop() || '').toLowerCase();
        const params = {
          linearUnit: 'millimeter',
          linearDeflectionType: 'bounding_box_ratio',
          linearDeflection: 0.1,
          angularDeflection: 0.1,
        };

        let res = null;

        if (ext === 'stp' || ext === 'step') {
          // 1) coba parse STEP
          res = occt.ReadStepFile(mainBuf, params);
          console.log('OCCT STEP result:', res);

          // 2) kalau gagal → fallback ke IGES dari paket
          if (!res || !res.success) {
            console.warn('STEP gagal, coba fallback ke IGES...');
            const igesFile = this._findIgesSibling(fileObj);
            if (igesFile?.url) {
              console.log('Fallback IGES file:', igesFile.name, igesFile.url);
              const igResp = await fetch(igesFile.url, {
                cache: 'no-store',
                credentials: 'same-origin'
              });
              if (!igResp.ok) throw new Error('Gagal mengambil file IGES fallback');
              const igBuf = new Uint8Array(await igResp.arrayBuffer());
              res = occt.ReadIgesFile(igBuf, params);
              console.log('OCCT IGES fallback result:', res);
            }
          }
        } else {
          // kalau yang dipilih memang IGES → parse IGES
          res = occt.ReadIgesFile(mainBuf, params);
          console.log('OCCT IGES result:', res);
        }

        if (!res || !res.success) {
          const msg = res?.error || res?.message || 'File bukan STEP/IGES yang valid atau tidak didukung OCCT.';
          throw new Error('OCCT gagal mem-parsing file: ' + msg);
        }

        // ---- build mesh & fit camera ----
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
        const center = new THREE.Vector3();
        box.getSize(size);
        box.getCenter(center);

        const maxDim = Math.max(size.x, size.y, size.z) || 100;
        const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
        camera.position.copy(
          center.clone().add(new THREE.Vector3(1, 1, 1).normalize().multiplyScalar(fitDist * 1.6))
        );
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
  };
}
