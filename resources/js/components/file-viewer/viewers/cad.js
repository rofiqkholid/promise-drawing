
export const getCadState = () => ({
    // CAD State
    cadParts: [],
    cadFlatParts: [],
    activeTab: 'structure', // Default to 'structure'
    partSearchQuery: '',
    selectedPartUuid: null,
    partOpacity: 1.0,
    currentStyle: 'shaded-edges',
    activeMaterial: 'default',
    autoRotate: false,
    cameraMode: 'perspective',
    headlight: { enabled: false, object: null },
    fps: 0,
    avgFps: 0,
    cadNeedsRender: false,

    // UI Toggle States
    isPartListOpen: false,
    isMeasureListOpen: false,
    isMatMenuOpen: false,
    isViewMenuOpen: false,
    isMeasureActive: false,

    // IGES Object (internal state)
    iges: {
        renderer: null,
        scene: null,
        camera: null,
        controls: null,
        animId: 0,
        loading: false,
        progress: 0,
        loadingProgress: 0,
        loadingStatus: '',
        loadingMessage: '',
        error: '',
        wireframe: false,
        showGrid: false,
        sessionId: null,
        // Sub-features initialized with safe defaults for Alpine template
        exploded: { enabled: false, factor: 0, panelOpen: false, animating: false, originalPositions: null },
        clipping: {
            enabled: false, panelOpen: false,
            min: -100, max: 100, step: 0.5,
            x: { enabled: false, value: 0, min: -100, max: 100, showHelper: true, flipped: false, showCap: false, plane: null, helper: null },
            y: { enabled: false, value: 0, min: -100, max: 100, showHelper: true, flipped: false, showCap: false, plane: null, helper: null },
            z: { enabled: false, value: 0, min: -100, max: 100, showHelper: true, flipped: false, showCap: false, plane: null, helper: null },
            planes: [],
            activeAxis: null,
            _dragState: null,
            _listenersBound: false
        },
        measure: {
            enabled: false, results: [], mode: 'point', hoverInstruction: 'Select Start Point',
            snap: { enabled: true, type: null },
            group: null, p1: null, p2: null, p3: null
        }
    },

    getSelectedPart() {
        if (!this.selectedPartUuid) return null;
        return this.cadFlatParts.find(p => p.uuid === this.selectedPartUuid) || null;
    },

    deselectAllParts() {
        const root = this.iges.rootModel;
        if (!root || !this.selectedPartUuid) return;

        root.traverse(o => {
            if (o.uuid === this.selectedPartUuid && o.isMesh) {
                const origMat = this._oriMats.get(o);
                if (origMat) {
                    o.material = Array.isArray(origMat) ? origMat.map(m => m.clone()) : origMat.clone();
                }
            }
        });

        this.selectedPartUuid = null;
        this.partOpacity = 1.0;
        this.cadFlatParts.forEach(p => p.selected = false);

        if (this._updateGlobalClipping) this._updateGlobalClipping();
        this.cadNeedsRender = true;
    },

    collapseAllTree() {
        // Since we are using filtered parts based on flat list, 
        // collapse simply resets the search and scrolls to top
        this.partSearchQuery = '';
        const el = this.$refs.cadPartList;
        if (el) el.scrollTop = 0;
    },

    _cadLoading: false,
    _mouseListeners: [],
    _oriMats: new Map(), // Cache for original materials
    _resizeObserver: null
});

export const cadMethods = {
    isCad(filename) {
        if (!filename) return false;
        const ext = filename.split('.').pop().toLowerCase();
        return ['igs', 'iges', 'stp', 'step', 'stl', 'obj'].includes(ext);
    },

    async loadCad(file) {
        if (this._cadLoading) {
            console.warn('[FileViewer] Already loading a CAD file, ignoring duplicate call');
            return;
        }
        this._cadLoading = true;

        const sessionId = Math.random();
        this.iges.sessionId = sessionId;

        this.disposeCad();

        this.iges.loading = true;
        this.iges.error = '';
        this.cadNeedsRender = true;

        // Reset tool states & UI
        this.isPartListOpen = false;
        this.activeTab = 'structure';
        this.selectedPartUuid = null;
        this.partSearchQuery = '';
        this.partOpacity = 1.0;
        this.currentStyle = 'shaded-edges';
        this.activeMaterial = 'default';
        this.autoRotate = false;
        this.cameraMode = 'perspective';
        this.isMeasureActive = false;
        this.isMeasureListOpen = false;

        if (this.iges.exploded) {
            this.iges.exploded.enabled = false;
            this.iges.exploded.factor = 0;
            this.iges.exploded.originalPositions = null;
            this.iges.exploded.panelOpen = false;
        }
        if (this.iges.clipping) {
            this.iges.clipping.enabled = false;
            this.iges.clipping.panelOpen = false;
            this.iges.clipping.x.enabled = false;
            this.iges.clipping.y.enabled = false;
            this.iges.clipping.z.enabled = false;
        }
        if (this.iges.measure) {
            this.iges.measure.enabled = false;
            this.iges.measure.results = [];
        }
        if (this.iges.tourInterval) {
            clearInterval(this.iges.tourInterval);
            this.iges.tourInterval = null;
        }

        try {
            // Wait for Three.js globals (loaded via CDN importmap in Blade)
            if (!window.THREE) {
                // console.log('[FileViewer] Waiting for Three.js CDN...');
                await new Promise(resolve => {
                    const check = () => {
                        if (window.THREE && window.THREE.OrbitControls && window.MeshBVHLib) {
                            resolve();
                        } else {
                            setTimeout(check, 100);
                        }
                    };
                    check();
                });
            }

            // Wait for OCCT
            if (!window.occtimportjs) {
                // console.log('[FileViewer] Waiting for OCCT CDN...');
                await new Promise(resolve => {
                    const check = () => {
                        if (window.occtimportjs) {
                            resolve();
                        } else {
                            setTimeout(check, 100);
                        }
                    };
                    check();
                });
            }

            const THREE = window.THREE;
            const OrbitControls = window.THREE.OrbitControls;
            const bvh = window.MeshBVHLib;

            // Enable BVH acceleration
            if (THREE.Mesh.prototype.raycast !== bvh.acceleratedRaycast) {
                THREE.Mesh.prototype.raycast = bvh.acceleratedRaycast;
                THREE.BufferGeometry.prototype.computeBoundsTree = bvh.computeBoundsTree;
                THREE.BufferGeometry.prototype.disposeBoundsTree = bvh.disposeBoundsTree;
            }

            // Initialize scene
            const scene = new THREE.Scene();
            scene.background = null;

            // Wait for DOM
            let wrap = null;
            for (let i = 0; i < 20; i++) {
                wrap = this.$refs.cadContainer;
                if (wrap && wrap.clientWidth > 0) break;
                await new Promise(r => setTimeout(r, 50));
            }

            if (!wrap) {
                throw new Error('CAD container (igesWrap) not found');
            }

            const width = wrap.clientWidth || 800;
            const height = wrap.clientHeight || 500;

            const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 10000);
            camera.position.set(250, 200, 250);

            const renderer = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true,
                preserveDrawingBuffer: true,
                powerPreference: 'high-performance',
                depth: true,
                precision: 'mediump' // Optimized for performance
            });

            // Cap pixel ratio at 1.5 instead of 2.0 for 44% less fragment processing on high-res screens
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
            renderer.setSize(width, height);
            renderer.localClippingEnabled = true;
            renderer.outputColorSpace = THREE.SRGBColorSpace;
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.0;
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFShadowMap; // Less overhead than PCFSoftShadowMap

            // Assign early so features can use it
            this.iges.renderer = renderer;

            renderer.domElement.style.display = 'block';
            renderer.domElement.style.width = '100%';
            renderer.domElement.style.height = '100%';
            renderer.domElement.style.touchAction = 'none'; // Critical for mobile/touch
            // renderer.domElement.style.touchAction = 'none'; // Duplicate removed
            wrap.appendChild(renderer.domElement);

            // console.log('[FileViewer] Renderer initialized and appended to wrap');

            // Lights
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.4); // Reduced for better contrast
            scene.add(ambientLight);

            const camLightGroup = new THREE.Group();
            camera.add(camLightGroup);
            scene.add(camera);

            const keyLight = new THREE.DirectionalLight(0xffffff, 1.2); // Increased intensity to compensate for less ambient
            keyLight.position.set(50, 100, 100);
            keyLight.castShadow = true;
            keyLight.shadow.mapSize.width = 1024;
            keyLight.shadow.mapSize.height = 1024;
            keyLight.shadow.bias = -0.00005;
            keyLight.shadow.normalBias = 0.02;

            const d = 500;
            keyLight.shadow.camera.left = -d;
            keyLight.shadow.camera.right = d;
            keyLight.shadow.camera.top = d;
            keyLight.shadow.camera.bottom = -d;
            keyLight.shadow.camera.far = 2000;

            camLightGroup.add(keyLight);
            this.iges.keyLight = keyLight;

            const fillLight = new THREE.DirectionalLight(0xffffff, 0.5);
            fillLight.position.set(-50, -50, 100);
            camLightGroup.add(fillLight);

            // Controls
            const controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.enabled = true;
            controls.enableRotate = true;
            controls.enableZoom = true;
            controls.enablePan = true;
            controls.addEventListener('change', () => { this.cadNeedsRender = true; });

            // Fetch CAD file
            // Fetch CAD file with Progress
            this.iges.loadingMessage = 'Downloading CAD file...';
            this.iges.progress = 0;

            const response = await fetch(file.url, { cache: 'no-store', credentials: 'same-origin' });
            if (!response.ok) throw new Error('Failed to fetch CAD file');

            const reader = response.body.getReader();
            const contentLength = +response.headers.get('Content-Length');
            let receivedLength = 0;
            const chunks = [];

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                chunks.push(value);
                receivedLength += value.length;
                if (contentLength) {
                    // Download phase: 0% to 50%
                    this.iges.progress = Math.round((receivedLength / contentLength) * 50);
                }
            }

            const mainBuf = new Uint8Array(receivedLength);
            let position = 0;
            for (let chunk of chunks) {
                mainBuf.set(chunk, position);
                position += chunk.length;
            }

            this.iges.progress = 55;
            this.iges.loadingMessage = 'Processing geometry...';

            // Allow UI to update before blocking main thread
            await new Promise(r => setTimeout(r, 50));

            const occt = await window.occtimportjs();
            const fileName = file.name || '';
            const ext = fileName.split('.').pop().toLowerCase();

            const fileSizeMB = mainBuf.byteLength / (1024 * 1024);
            const isLargeFile = fileSizeMB > 30;
            const isVeryLargeFile = fileSizeMB > 70;

            const params = {
                linearUnit: 'millimeter',
                linearDeflectionType: 'bounding_box_ratio',
                linearDeflection: isVeryLargeFile ? 0.04 : (isLargeFile ? 0.02 : 0.01),
                angularDeflection: isVeryLargeFile ? 0.2 : (isLargeFile ? 0.15 : 0.1),
            };

            let res = null;
            if (ext === 'stp' || ext === 'step') {
                res = occt.ReadStepFile(mainBuf, params);
            } else if (ext === 'brep') {
                res = occt.ReadBrepFile(mainBuf, params);
            } else {
                res = occt.ReadIgesFile(mainBuf, params);
            }

            if (!res || !res.success) {
                throw new Error('OCCT failed to parse file: ' + (res?.error || 'Unknown error'));
            }

            this.iges.progress = 75;
            this.iges.loadingMessage = 'Building 3D meshes...';
            // Yield UI update
            await new Promise(r => setTimeout(r, 50));

            // Build Meshes
            const { group, box } = await this._buildThreeFromOcct(res, THREE);
            this.iges.progress = 90;
            this.iges.loadingMessage = 'Optimizing scene...';
            await new Promise(r => setTimeout(r, 50));

            scene.add(group);
            // Re-enabled for the main group to ensure initial positioning works, 
            // the thousands of meshes inside still have it disabled for performance.
            group.matrixAutoUpdate = true;

            this.iges.rootModel = group;
            this.iges.scene = scene;
            this.iges.camera = camera;
            this.iges.renderer = renderer;
            this.iges.controls = controls;
            this.iges.THREE = THREE;

            // Center model at world origin (0,0,0) for stable rotation
            const size = new THREE.Vector3();
            box.getSize(size);
            const center = new THREE.Vector3();
            box.getCenter(center);

            // Move group so its visual center is at 0,0,0
            group.position.set(-center.x, -center.y, -center.z);
            group.updateMatrix(); // Manually update local matrix
            group.updateMatrixWorld(true);

            // Fit camera
            const maxDim = Math.max(size.x, size.y, size.z) || 100;

            if (this.iges.keyLight) {
                const sCam = this.iges.keyLight.shadow.camera;
                const d = maxDim * 0.8;
                sCam.left = -d; sCam.right = d; sCam.top = d; sCam.bottom = -d;
                sCam.far = maxDim * 4;
                sCam.updateProjectionMatrix();
                this.iges.keyLight.shadow.bias = -0.0001;
                this.iges.keyLight.shadow.normalBias = 0.05;
            }

            const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
            camera.position.copy(new THREE.Vector3(1, 1, 1).normalize().multiplyScalar(fitDist * 1.6));
            camera.near = maxDim / 100;
            camera.far = maxDim * 100;
            camera.updateProjectionMatrix();

            // Set initial rotation center to absolute 0,0,0 (center of model)
            controls.target.set(0, 0, 0);
            controls.update();

            this.iges.progress = 100;
            this.iges.loadingMessage = 'Ready';

            this.setDisplayStyle('shaded-edges');

            // Allow UI to animate to 100%
            await new Promise(r => setTimeout(r, 200));

            this.iges.renderer = renderer;

            // Re-initialize features to bind listeners to new renderer
            if (this.initClipping) this.initClipping();
            if (this.initMeasurements) this.initMeasurements();
            if (this.initExplode) this.initExplode();

            // Animation Loop (using local variables for performance and stability)
            let lastFrameTime = performance.now();
            let isIdle = true;
            let frameCount = 0;

            const animate = () => {
                // Check if this viewer is still the active one using stable sessionId
                if (this.iges.sessionId !== sessionId) {
                    // console.log('[FileViewer] Stopping obsolete animation loop (Session Mismatch)');
                    return;
                }

                const rawIges = Alpine.raw(this.iges);
                const rawRenderer = rawIges.renderer;
                const rawScene = rawIges.scene;

                if (!rawRenderer) {
                    // console.log('[FileViewer] Renderer not available, re-scheduling animation loop');
                    this.iges.animId = requestAnimationFrame(animate);
                    return;
                }

                const now = performance.now();
                if (isIdle && now - lastFrameTime > 500) this.fps = 0;

                // Update controls using local variable (raw)
                const controlsChanged = controls.update();
                frameCount++;

                const exploded = rawIges.exploded;
                const isExploding = exploded && (exploded.animating || exploded.factor > 0);

                // Force first few frames to ensure UI stabilizes and model shows
                if (this.cadNeedsRender || controlsChanged || isExploding || frameCount < 60) {
                    isIdle = false;

                    const delta = now - lastFrameTime;
                    lastFrameTime = now;
                    if (delta > 0) {
                        const currentFps = 1000 / delta;
                        this.fps = Math.round(currentFps);
                        // Smooth moving average (EWMA)
                        this.avgFps = this.avgFps === 0 ? currentFps : (this.avgFps * 0.96 + currentFps * 0.04);
                    }

                    const rawCamera = Alpine.raw(this.iges.camera) || camera;
                    rawRenderer.render(rawScene, rawCamera);
                    this.cadNeedsRender = false;

                    // Update measurement labels ONLY when rendering happens
                    const measure = rawIges.measure;
                    if (measure?.group) {
                        const g = Alpine.raw(measure.group);
                        if (g && g.children) {
                            g.children.forEach(ch => ch.userData?.update?.());
                        }
                    }
                } else {
                    isIdle = true;
                }

                this.iges.animId = requestAnimationFrame(animate);
            };
            animate();

            // Resize Observer
            this._resizeObserver = new ResizeObserver(() => {
                if (!wrap) return;
                const w = wrap.clientWidth;
                const h = wrap.clientHeight;
                if (w === 0 || h === 0) return;

                if (camera.isOrthographicCamera) {
                    // .. update ortho metrics
                    const aspect = w / h;
                    const frustumHeight = (camera.top - camera.bottom);
                    const frustumWidth = frustumHeight * aspect;
                    camera.left = -frustumWidth / 2;
                    camera.right = frustumWidth / 2;
                    // ...
                    camera.updateProjectionMatrix();
                } else {
                    camera.aspect = w / h;
                    camera.updateProjectionMatrix();
                }
                renderer.setSize(w, h, false);
                this.cadNeedsRender = true;
            });
            this._resizeObserver.observe(wrap);

            this.iges.loading = false;
            this._cadLoading = false;
            this.cadNeedsRender = true;
            // console.log('[FileViewer] CAD loading complete');

        } catch (error) {
            console.error('[FileViewer] 3D CAD loading error:', error);
            this.iges.error = 'Failed to load 3D CAD: ' + error.message;
            this.iges.loading = false;
            this._cadLoading = false;
        }
    },

    async _buildThreeFromOcct(result, THREE) {
        const group = new THREE.Group();
        const meshes = result.meshes || [];
        const box = new THREE.Box3();

        this.cadParts = [];
        this.cadFlatParts = [];
        this._oriMats = new Map();

        const materialCache = {};
        const getMaterial = (colorArr) => {
            const key = colorArr ? colorArr.join(',') : 'default';
            if (!materialCache[key]) {
                let colorVal = 0xcccccc;
                if (colorArr && colorArr.length === 3) {
                    colorVal = (colorArr[0] << 16) | (colorArr[1] << 8) | (colorArr[2]);
                }
                materialCache[key] = new THREE.MeshStandardMaterial({
                    color: colorVal,
                    metalness: 0.1,
                    roughness: 0.5,
                    side: THREE.DoubleSide, // Reverted to DoubleSide to ensure all CAD surfaces are visible
                    flatShading: false
                });
            }
            return materialCache[key];
        };

        const DRAW_CALL_THRESHOLD = 500;
        const useMerging = meshes.length > DRAW_CALL_THRESHOLD;

        if (useMerging) {
            const groupsByColor = {};
            // Group by color
            for (let i = 0; i < meshes.length; i++) {
                const m = meshes[i];
                const key = m.color ? m.color.join(',') : 'default';
                if (!groupsByColor[key]) groupsByColor[key] = [];

                const g = new THREE.BufferGeometry();
                g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
                if (m.attributes.normal?.array) {
                    g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
                } else {
                    g.computeVertexNormals();
                }
                if (m.index?.array) g.setIndex(m.index.array);
                groupsByColor[key].push(g);

                const part = {
                    uuid: 'merged-' + i,
                    id: 'merged-' + i,
                    name: m.name || `Part ${i + 1}`,
                    isMerged: true,
                    visible: true
                };
                this.cadFlatParts.push(part);
                this.cadParts.push(part);
            }

            const BufferGeometryUtils = window.THREE && window.THREE.BufferGeometryUtils;

            if (BufferGeometryUtils && BufferGeometryUtils.mergeGeometries) {
                try {
                    for (const colorKey of Object.keys(groupsByColor)) {
                        const geoms = groupsByColor[colorKey];
                        const mergedGeom = BufferGeometryUtils.mergeGeometries(geoms, false);
                        const colorArr = colorKey === 'default' ? null : colorKey.split(',').map(Number);
                        const mat = getMaterial(colorArr);
                        const mesh = new THREE.Mesh(mergedGeom, mat);

                        mesh.castShadow = true; // Allow casting shadows
                        mesh.receiveShadow = false; // Disable self-shadowing on parts (Major FPS boost)
                        mesh.matrixAutoUpdate = false;
                        group.add(mesh);
                        box.expandByObject(mesh);
                        this._oriMats.set(mesh, mat.clone());

                        geoms.forEach(g => g.dispose());
                    }
                } catch (e) {
                    console.warn('Merge failed', e);
                }
            } else {
                console.warn('BufferGeometryUtils not available, skipping merge optimization');
                // Fallback to standard (non-merged)
                for (let i = 0; i < meshes.length; i++) {
                    const m = meshes[i];
                    const g = new THREE.BufferGeometry();
                    g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
                    if (m.attributes.normal?.array) {
                        g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
                    }
                    if (m.index?.array) g.setIndex(m.index.array);

                    if (g.attributes.position.count > 0 && typeof g.computeBoundsTree === 'function') {
                        g.computeBoundsTree();
                    }

                    const mat = getMaterial(m.color);
                    const mesh = new THREE.Mesh(g, mat);
                    mesh.name = m.name || `Part ${i + 1}`;
                    mesh.castShadow = true;
                    mesh.receiveShadow = false; // Disable self-shadowing on parts (Major FPS boost)
                    mesh.matrixAutoUpdate = false;

                    group.add(mesh);
                    this._oriMats.set(mesh, Array.isArray(mat) ? mat.map(mm => mm.clone()) : mat.clone());
                    box.expandByObject(mesh);

                    const part = { uuid: mesh.uuid, id: mesh.uuid, name: mesh.name, visible: true };
                    this.cadFlatParts.push(part);
                    this.cadParts.push(part);
                }
            }

        } else {
            // Standard
            for (let i = 0; i < meshes.length; i++) {
                const m = meshes[i];
                const g = new THREE.BufferGeometry();
                g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
                if (m.attributes.normal?.array) {
                    g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
                }
                if (m.index?.array) g.setIndex(m.index.array);

                if (g.attributes.position.count > 0 && typeof g.computeBoundsTree === 'function') {
                    g.computeBoundsTree();
                }

                const mat = getMaterial(m.color);
                const mesh = new THREE.Mesh(g, mat);
                mesh.name = m.name || `Part ${i + 1}`;
                mesh.castShadow = true;

                group.add(mesh);
                this._oriMats.set(mesh, Array.isArray(mat) ? mat.map(mm => mm.clone()) : mat.clone());
                box.expandByObject(mesh);

                const part = { uuid: mesh.uuid, id: mesh.uuid, name: mesh.name, visible: true };
                this.cadFlatParts.push(part);
                this.cadParts.push(part);
            }
        }
        return { group, box };
    },

    disposeCad() {
        if (this.iges.animId) cancelAnimationFrame(this.iges.animId);
        if (this._resizeObserver) this._resizeObserver.disconnect();

        // Remove listeners
        this._mouseListeners.forEach(l => l.element.removeEventListener(l.type, l.handler));
        this._mouseListeners = [];

        // Dispose Three.js objects
        const { renderer, scene, controls } = this.iges;
        if (controls) controls.dispose();
        if (renderer) {
            renderer.dispose();
            renderer.domElement.remove();
        }
        if (scene) {
            scene.traverse(o => {
                if (o.geometry) o.geometry.dispose();
                if (o.material) {
                    if (Array.isArray(o.material)) o.material.forEach(m => m.dispose());
                    else o.material.dispose();
                }
            });
        }

        // Reset State
        this.iges.renderer = null;
        this.iges.scene = null;
        if (this.iges.clipping) this.iges.clipping._listenersBound = false;
        this.cadParts = [];
        this.cadFlatParts = [];
        this._oriMats.clear();
        this._cadLoading = false;
    },

    setDisplayStyle(mode) {
        this.currentStyle = mode;
        const root = this.iges.rootModel;
        const THREE = this.iges.THREE;
        if (!root || !THREE) return;

        this._restoreMaterials(root);

        if (mode === 'shaded-edges') {
            this._setPolygonOffset(root, true, 1, 1);
            this._toggleEdges(root, true, 0x000000);
        } else if (mode === 'wireframe') {
            this._setWireframe(root, true);
        }

        // Re-apply clipping if any
        if (this._updateGlobalClipping) this._updateGlobalClipping();

        this.cadNeedsRender = true;
    },

    setMaterialMode(mode) {
        this.activeMaterial = mode;
        const root = this.iges.rootModel;
        const THREE = this.iges.THREE;
        if (!root || !THREE) return;

        // 1. Reset to original materials first
        this._restoreMaterials(root);

        // 2. If mode is 'default', we're done (already restored and clipping applied via setDisplayStyle caller or here)
        if (mode === 'default') {
            if (this._updateGlobalClipping) this._updateGlobalClipping();
            this.cadNeedsRender = true;
            return;
        }

        // 3. Prepare new Material
        const commonProps = {
            side: THREE.DoubleSide,
            clipShadows: true
        };

        let newMat;
        switch (mode) {
            case 'clay':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0xdddddd, roughness: 1.0, metalness: 0.0 });
                break;
            case 'metal':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0xffffff, roughness: 0.2, metalness: 1.0 });
                break;
            case 'glass':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0xffffff, metalness: 0.1, roughness: 0.1, transparent: true, opacity: 0.3, depthWrite: false });
                break;
            case 'ecoat':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0x222222, roughness: 0.7, metalness: 0.1 });
                break;
            case 'raw-steel':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0xc0c6c9, roughness: 0.4, metalness: 0.8 });
                break;
            case 'aluminum':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0xffffff, roughness: 0.5, metalness: 0.7 });
                break;
            case 'yellow-zinc':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0xd4af37, roughness: 0.5, metalness: 0.6 });
                break;
            case 'red-oxide':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0x803020, roughness: 0.9, metalness: 0.0 });
                break;
            case 'dark':
                newMat = new THREE.MeshStandardMaterial({ ...commonProps, color: 0x1a1a1a, roughness: 0.6, metalness: 0.2 });
                break;
            case 'normal':
                newMat = new THREE.MeshNormalMaterial({ ...commonProps });
                break;
        }

        if (newMat) {
            root.traverse(o => {
                if (o.isMesh) {
                    o.material = Array.isArray(o.material) ? o.material.map(() => newMat.clone()) : newMat.clone();
                }
            });
        }

        // Re-apply clipping
        if (this._updateGlobalClipping) this._updateGlobalClipping();

        this.cadNeedsRender = true;
    },

    _restoreMaterials(root) {
        root.traverse(o => {
            if (!o.isMesh) return;
            const m = this._oriMats.get(o);
            if (m) {
                o.material = Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone();
            }
        });
        this._setWireframe(root, false);
        this._toggleEdges(root, false);
        this._setPolygonOffset(root, false);
    },

    _setWireframe(root, on) {
        root.traverse(o => {
            if (o.isMesh) {
                const mats = Array.isArray(o.material) ? o.material : [o.material];
                mats.forEach(m => m.wireframe = on);
            }
        });
    },

    _toggleEdges(root, on, color) {
        const THREE = this.iges.THREE;
        root.traverse(o => {
            if (!o.isMesh) return;
            if (on) {
                if (!o.userData.edges) {
                    const edgesGeo = new THREE.EdgesGeometry(o.geometry, 40); // Increased threshold to 40 for fewer lines
                    const edgesMat = new THREE.LineBasicMaterial({ color: color, transparent: true, opacity: 0.6, depthTest: true });

                    // Support clipping on edges
                    if (this.iges.clipping && this.iges.clipping.enabled) {
                        const planes = [];
                        const { x, y, z } = this.iges.clipping;
                        if (x.enabled && x.plane) planes.push(Alpine.raw(x.plane));
                        if (y.enabled && y.plane) planes.push(Alpine.raw(y.plane));
                        if (z.enabled && z.plane) planes.push(Alpine.raw(z.plane));
                        edgesMat.clippingPlanes = planes;
                    }

                    const edges = new THREE.LineSegments(edgesGeo, edgesMat);
                    edges.renderOrder = 1;
                    o.add(edges);
                    o.userData.edges = edges;
                }
            } else if (o.userData.edges) {
                o.remove(o.userData.edges);
                o.userData.edges.geometry.dispose();
                o.userData.edges.material.dispose();
                o.userData.edges = null;
            }
        });
    },

    _setPolygonOffset(root, on, factor, units) {
        root.traverse(o => {
            if (o.isMesh) {
                const mats = Array.isArray(o.material) ? o.material : [o.material];
                mats.forEach(m => {
                    m.polygonOffset = on;
                    m.polygonOffsetFactor = factor;
                    m.polygonOffsetUnits = units;
                });
            }
        });
    },

    // ===== INTERACTION METHODS =====
    highlightPart(uuid) {
        const root = this.iges.rootModel;
        const THREE = this.iges.THREE;
        if (!root || !THREE) return;

        // 1. If clicking the SAME part that's already selected, DESELECT it
        if (this.selectedPartUuid === uuid) {
            this.deselectAllParts();
            return;
        }

        // 2. Reset previous highlight
        if (this.selectedPartUuid) {
            root.traverse(o => {
                if (o.uuid === this.selectedPartUuid && o.isMesh) {
                    const origMat = this._oriMats.get(o);
                    if (origMat) {
                        o.material = Array.isArray(origMat) ? origMat.map(m => m.clone()) : origMat.clone();
                    }
                }
            });
        }

        // 3. Select New Part
        this.selectedPartUuid = uuid;
        this.partOpacity = 1.0;

        root.traverse(o => {
            if (o.uuid === uuid && o.isMesh) {
                const mats = Array.isArray(o.material) ? o.material : [o.material];
                mats.forEach(m => {
                    m.emissive = new THREE.Color(0x4444ff);
                    m.emissiveIntensity = 0.4; // More precise highlight
                    if (m.metalness !== undefined) m.metalness *= 0.5; // Slight adjustment for visibility
                });
            }
        });

        // 4. Update the visual state of the tree/sidebar (if needed by Alpine)
        this.cadFlatParts.forEach(p => p.selected = (p.uuid === uuid));

        // Re-apply clipping if any
        if (this._updateGlobalClipping) this._updateGlobalClipping();

        this.cadNeedsRender = true;
    },

    deselectAllParts() {
        const root = this.iges.rootModel;
        if (!root || !this.selectedPartUuid) return;

        root.traverse(o => {
            if (o.uuid === this.selectedPartUuid && o.isMesh) {
                const origMat = this._oriMats.get(o);
                if (origMat) {
                    o.material = Array.isArray(origMat) ? origMat.map(m => m.clone()) : origMat.clone();
                }
            }
        });

        this.selectedPartUuid = null;
        this.partOpacity = 1.0;
        this.cadFlatParts.forEach(p => p.selected = false);

        // Re-apply clipping if any
        if (this._updateGlobalClipping) this._updateGlobalClipping();

        this.cadNeedsRender = true;
    },

    updatePartOpacity() {
        const root = this.iges.rootModel;
        if (!root || !this.selectedPartUuid) return;

        root.traverse(o => {
            if (o.uuid === this.selectedPartUuid && o.isMesh) {
                const mats = Array.isArray(o.material) ? o.material : [o.material];
                mats.forEach(m => {
                    m.transparent = this.partOpacity < 1.0;
                    m.opacity = this.partOpacity;
                    m.needsUpdate = true;
                });
            }
        });
        this.cadNeedsRender = true;
    },

    isolatePart(uuid) {
        const root = this.iges.rootModel;
        if (!root) return;

        root.traverse(o => {
            if (o.isMesh) {
                o.visible = (o.uuid === uuid);
                const p = this.cadFlatParts.find(part => part.uuid === o.uuid);
                if (p) p.visible = o.visible;
            }
        });
        this.highlightPart(uuid);
        this.focusPart(uuid);
        this.cadNeedsRender = true;
    },

    focusPart(uuid) {
        const root = this.iges.rootModel;
        const camera = this.iges.camera;
        const controls = this.iges.controls;
        const THREE = this.iges.THREE;
        if (!root || !camera || !controls || !THREE) return;

        const target = root.getObjectByProperty('uuid', uuid);
        if (!target) return;

        const box = new THREE.Box3().setFromObject(target);
        const center = new THREE.Vector3();
        box.getCenter(center);
        const size = new THREE.Vector3();
        box.getSize(size);

        const maxDim = Math.max(size.x, size.y, size.z);
        const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));

        const dir = camera.position.clone().sub(controls.target).normalize();
        const newPos = center.clone().add(dir.multiplyScalar(fitDist * 2));

        this._animateCamera(newPos, center, camera.up, null, 600);
    },

    showAllParts() {
        const root = this.iges.rootModel;
        if (!root) return;

        root.traverse(o => {
            if (o.isMesh) {
                o.visible = true;
                const p = this.cadFlatParts.find(part => part.uuid === o.uuid);
                if (p) p.visible = true;
            }
        });

        // Re-apply clipping if any
        if (this._updateGlobalClipping) this._updateGlobalClipping();

        this.cadNeedsRender = true;
    },

    togglePartVisibility(uuid) {
        const root = this.iges.rootModel;
        if (!root) return;

        const target = root.getObjectByProperty('uuid', uuid);
        if (target) {
            target.visible = !target.visible;
            const p = this.cadFlatParts.find(part => part.uuid === uuid);
            if (p) p.visible = target.visible;
        }
        this.cadNeedsRender = true;
    },

    getSelectedPart() {
        if (!this.selectedPartUuid) return null;
        return this.cadFlatParts.find(p => p.uuid === this.selectedPartUuid) || null;
    },

    getPartColor(part) { return '#3b82f6'; },

    toggleAutoRotate() {
        this.autoRotate = !this.autoRotate;
        const controls = this.iges.controls;

        if (this.autoRotate) {
            // START TOUR ANIMATION
            // Disable standard OrbitControls autoRotate to prevent conflict
            if (controls) controls.autoRotate = false;

            const views = ['front', 'right', 'back', 'left', 'top', 'bottom', 'iso'];
            let currentViewIndex = 0;

            const nextTourStep = () => {
                if (!this.autoRotate) return;
                const view = views[currentViewIndex];
                this.setStandardView(view, 1500); // 1.5s animation (smoother)
                currentViewIndex = (currentViewIndex + 1) % views.length;
            };

            // Run first step immediately
            nextTourStep();

            // Schedule next steps (Animation 1.5s + Pause 2.5s = 4s interval)
            this.iges.tourInterval = setInterval(nextTourStep, 4000);

            // console.log('[FileViewer] Started View Tour Animation');
        } else {
            // STOP TOUR ANIMATION
            if (controls) controls.autoRotate = false;

            if (this.iges.tourInterval) {
                clearInterval(this.iges.tourInterval);
                this.iges.tourInterval = null;
            }

            // console.log('[FileViewer] Stopped View Tour Animation');
        }
    },

    toggleHeadlight() {
        this.headlight.enabled = !this.headlight.enabled;
        const { camera, THREE } = this.iges;

        const rawCamera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(camera) : camera;
        if (!rawCamera || !THREE) return;

        if (this.headlight.enabled) {
            if (!this.headlight.object) {
                // Use SpotLight for better directional lighting
                const spot = new THREE.SpotLight(0xffffee, 2.5);
                spot.position.set(0, 0, 0);
                spot.target.position.set(0, 0, -1); // Face forward
                spot.angle = 0.6;
                spot.penumbra = 1.0;
                spot.decay = 0;
                spot.distance = 0; // Unlimited distance
                this.headlight.object = spot;
            }

            // Attach light and target to camera
            rawCamera.add(this.headlight.object);
            if (this.headlight.object.target) {
                rawCamera.add(this.headlight.object.target);
            }
        } else {
            if (this.headlight.object) {
                rawCamera.remove(this.headlight.object);
                if (this.headlight.object.target) {
                    rawCamera.remove(this.headlight.object.target);
                }
            }
        }

        this._forceRender();
        // console.log('[FileViewer] Headlight:', this.headlight.enabled);
    },

    takeScreenshot() {
        let { renderer, scene, camera } = this.iges;
        // Unwrap proxies for screenshot
        if (typeof Alpine !== 'undefined' && Alpine.raw) {
            renderer = Alpine.raw(renderer);
            scene = Alpine.raw(scene);
            camera = Alpine.raw(camera);
        }
        if (!renderer || !scene || !camera) {
            console.warn('[FileViewer] Cannot take screenshot: renderer not ready');
            return;
        }

        try {
            // Force render to ensure latest state
            renderer.render(scene, camera);

            // Get canvas data as PNG
            const dataURL = renderer.domElement.toDataURL('image/png');

            // Create download link
            const link = document.createElement('a');
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
            link.download = `3d-viewer-${timestamp}.png`;
            link.href = dataURL;
            link.click();

            // console.log('[FileViewer] Screenshot saved');
        } catch (err) {
            console.error('[FileViewer] Screenshot failed:', err);
        }
    },

    _forceRender() {
        try {
            const { renderer, scene, camera } = this.iges;
            if (!renderer || !scene || !camera) return;

            const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(renderer) : renderer;
            const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
            const rawCamera = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(camera) : camera;

            if (rawRenderer && rawScene && rawCamera) {
                rawRenderer.render(rawScene, rawCamera);
            }
        } catch (error) {
            console.error('[FileViewer] Force render error:', error);
        }
    },

    toggleWireframe() {
        const mode = this.currentStyle === 'wireframe' ? 'shaded-edges' : 'wireframe';
        this.setDisplayStyle(mode);
    },

    toggleGrid() {
        this.iges.showGrid = !this.iges.showGrid;
    },

    // Camera Controls
    zoom3d(factor) {
        const camera = this.iges.camera;
        if (!camera) return;
        if (camera.isPerspectiveCamera) {
            camera.position.multiplyScalar(1 / factor);
        } else if (camera.isOrthographicCamera) {
            camera.zoom *= factor;
            camera.updateProjectionMatrix();
        }
    },

    resetCamera3d() {
        const camera = this.iges.camera;
        const controls = this.iges.controls;
        if (!camera || !controls) return;

        const root = this.iges.rootModel;
        if (root) {
            const THREE = this.iges.THREE;
            const box = new THREE.Box3().setFromObject(root);
            const size = new THREE.Vector3();
            box.getSize(size);
            const maxDim = Math.max(size.x, size.y, size.z) || 100;
            const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
            const viewDirection = new THREE.Vector3(1, 1, 1).normalize();

            camera.position.copy(viewDirection.multiplyScalar(fitDist * 1.6));
            camera.up.set(0, 1, 0); // Reset to Y-up
            camera.updateProjectionMatrix();
        }
        controls.target.set(0, 0, 0);
        controls.update();
    },

    toggleCameraMode() {
        if (!this.iges.camera) return;
        const THREE = this.iges.THREE;
        const oldCam = this.iges.camera;
        const w = this.iges.renderer.domElement.clientWidth;
        const h = this.iges.renderer.domElement.clientHeight;
        const aspect = w / h;

        let newCam;
        if (oldCam.isPerspectiveCamera) {
            const box = new THREE.Box3().setFromObject(this.iges.rootModel);
            const size = new THREE.Vector3();
            box.getSize(size);
            const maxDim = Math.max(size.x, size.y, size.z) || 100;
            const frustumSize = maxDim * 2;

            newCam = new THREE.OrthographicCamera(
                frustumSize * aspect / -2, frustumSize * aspect / 2,
                frustumSize / 2, frustumSize / -2,
                0.1, 100000
            );
            this.cameraMode = 'orthographic';
        } else {
            newCam = new THREE.PerspectiveCamera(50, aspect, 0.1, 100000);
            this.cameraMode = 'perspective';
        }

        newCam.position.copy(oldCam.position);
        newCam.quaternion.copy(oldCam.quaternion);
        newCam.up.copy(oldCam.up);

        while (oldCam.children.length > 0) newCam.add(oldCam.children[0]);

        if (this.iges.scene) {
            const rawScene = Alpine.raw(this.iges.scene);
            rawScene.remove(oldCam);
            rawScene.add(newCam);
        }

        this.iges.camera = newCam;
        if (this.iges.controls) {
            const rawControls = Alpine.raw(this.iges.controls);
            rawControls.object = newCam;
            rawControls.update();
        }
        this.cadNeedsRender = true;
    },

    setStandardView(view, duration = 800) {
        const { camera, controls, rootModel, THREE } = this.iges;
        if (!rootModel || !camera || !controls) return;

        const box = new THREE.Box3().setFromObject(rootModel);
        const size = new THREE.Vector3(); box.getSize(size);
        const center = new THREE.Vector3(0, 0, 0); // Always use absolute 0,0,0 since we center model in loadCad
        const maxDim = Math.max(size.x, size.y, size.z);
        const fitDist = maxDim * 1.5;

        let newPos = new THREE.Vector3();
        switch (view) {
            case 'front': newPos.set(0, 0, fitDist); break;
            case 'back': newPos.set(0, 0, -fitDist); break;
            case 'top': newPos.set(0, fitDist, 0.01); break;
            case 'bottom': newPos.set(0, -fitDist, 0.01); break;
            case 'right': newPos.set(fitDist, 0, 0); break;
            case 'left': newPos.set(-fitDist, 0, 0); break;
            case 'iso': default: newPos.set(fitDist, fitDist, fitDist).normalize().multiplyScalar(fitDist); break;
        }

        // Maintain consistent World-Up (Y-axis) for stable OrbitControls behavior
        const newUp = new THREE.Vector3(0, 1, 0);

        this._animateCamera(newPos, center, newUp, null, duration);
    },

    getFilteredParts() {
        if (!this.partSearchQuery) {
            // If huge model, limit initial render for performance
            if (this.cadFlatParts.length > 200) {
                return this.cadFlatParts.slice(0, 200);
            }
            return this.cadParts;
        }

        const query = this.partSearchQuery.toLowerCase();
        // Since cadParts might be flat, we filter cadFlatParts for faster search
        return this.cadFlatParts.filter(p =>
            p.name.toLowerCase().includes(query)
        ).slice(0, 300); // Limit search results too
    },

    _animateCamera(targetPos, targetTarget, targetUp, onComplete, duration = 800) {
        const { camera, controls } = this.iges;
        const startPos = camera.position.clone();
        const startTarget = controls.target.clone();
        const startUp = camera.up.clone();

        const startTime = performance.now();

        const animate = (time) => {
            let elapsed = time - startTime;
            let t = Math.min(1, elapsed / duration);
            t = 1 - Math.pow(1 - t, 3);

            camera.position.lerpVectors(startPos, targetPos, t);
            controls.target.lerpVectors(startTarget, targetTarget, t);
            camera.up.lerpVectors(startUp, targetUp, t);

            controls.update();
            this.cadNeedsRender = true;

            if (t < 1) {
                requestAnimationFrame(animate);
            } else {
                if (onComplete) onComplete();
            }
        };
        requestAnimationFrame(animate);
    },

};
