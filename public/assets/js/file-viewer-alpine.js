/**
 * File Viewer Alpine.js Component
 * 
 * Self-contained Alpine.js component for viewing multiple file types
 * with stamp configuration and advanced 3D CAD viewer.
 * 
 * Supported File Types:
 * - Images (JPG, PNG, GIF, BMP, WebP)
 * - PDF documents
 * - TIFF images (multi-page)
 * - HPGL plotter files
 * - 3D CAD files (IGES, STEP, STL, OBJ)
 * 
 * Usage:
 * <div x-data="fileViewerComponent({ 
 *     pkg: packageData, 
 *     showStampConfig: true,
 *     userDeptCode: 'ENG',
 *     userName: 'John Doe',
 *     isEngineering: true
 * })">
 *     <x-file-viewer ... />
 * </div>
 * 
 * @requires Alpine.js
 * @requires UTIF.js (for TIFF rendering)
 * @requires PDF.js (for PDF rendering)
 * @requires Three.js (for 3D CAD rendering)
 * @requires OCCT-import-js (for IGES/STEP parsing)
 */

/**
 * Create file viewer Alpine.js component
 * @param {Object} config - Configuration object
 * @param {Object} config.pkg - Package data with files and stamp info
 * @param {boolean} config.showStampConfig - Show stamp configuration UI
 * @param {string} config.userDeptCode - User department code
 * @param {string} config.userName - User name
 * @param {boolean} config.isEngineering - Is engineering department
 * @param {Object} config.stampFormat - Stamp format configuration
 * @returns {Object} Alpine.js component data
 */
function fileViewerComponent(config = {}) {
    return {
        // ===== CONFIGURATION =====
        pkg: config.pkg || {},
        showStampConfig: config.showStampConfig || false,
        userDeptCode: config.userDeptCode || null,
        userName: config.userName || null,
        isEngineering: config.isEngineering || false,
        stampFormat: config.stampFormat || null,

        // ===== FILE SELECTION =====
        selectedFile: null,
        lastLoadedUrl: null,

        // ===== ZOOM & PAN STATE =====
        imageZoom: 1,
        minZoom: 0.5,
        maxZoom: 5,
        zoomStep: 0.25,
        panX: 0,
        panY: 0,
        isPanning: false,
        panStartX: 0,
        panStartY: 0,
        panOriginX: 0,
        panOriginY: 0,

        // ===== IMAGE STATE =====
        imgLoading: false,
        imgError: '',

        // ===== PDF STATE =====
        pdfLoading: false,
        pdfError: '',
        pdfPageNum: 1,
        pdfNumPages: 1,
        pdfScale: 1.0,
        pdfDoc: null,
        pdfRenderTask: null,

        // ===== TIFF STATE =====
        tifLoading: false,
        tifError: '',
        tifPageNum: 1,
        tifNumPages: 1,
        tifIfds: [],
        tifDecoder: null,

        // ===== HPGL STATE =====
        hpglLoading: false,
        hpglError: '',
        hpglDrawingBounds: { left: 0, top: 0, width: 0, height: 0 },

        // ===== 3D CAD STATE =====
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
                p2: null,
                p3: null,
                mode: 'point',
                snap: {
                    enabled: true,
                    type: null,
                    point: null,
                    normal: null,
                    edge: null
                },
                results: [],
                hoverInstruction: 'Select Start Point'
            },
            clipping: {
                panelOpen: false,
                min: -100, max: 100, step: 1,
                x: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                y: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                z: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false }
            },
            exploded: {
                enabled: false,
                factor: 0, // 0 = collapsed, 1 = fully exploded
                center: null, // Model center point
                originalPositions: null, // Store original positions (initialized in loadCad)
                animating: false,
                panelOpen: false
            }
        },
        cadPartsList: [],
        selectedPartUuid: null,
        isPartListOpen: false,
        isMeasureListOpen: false,
        isMeasureActive: false,
        isViewMenuOpen: false,
        isMatMenuOpen: false,
        partOpacity: 1.0,
        currentStyle: 'shaded-edges',
        activeMaterial: 'default',
        enableMasking: config.enableMasking || false,
        masks: [],
        activeMask: null,
        isFullscreen: false,
        autoRotate: false,
        headlight: { enabled: false, object: null },
        cameraMode: 'perspective',

        // ===== STAMP CONFIGURATION =====
        stampDefaults: {
            original: 'bottom-left',
            copy: 'bottom-center',
            obsolete: 'bottom-right',
        },
        stampPerFile: {},
        stampConfig: {
            original: 'bottom-left',
            copy: 'bottom-center',
            obsolete: 'bottom-right',
        },
        applyToAllProcessing: false,

        // ===== INITIALIZATION =====
        init() {
            // console.log('[FileViewer] Component initialized', {
            //     showStampConfig: this.showStampConfig,
            //     pkg: this.pkg?.package_number
            // });

            // Watch for file selection changes
            this.$watch('selectedFile', (file) => {
                if (file) {
                    // console.log('[FileViewer] File selected:', file.name);
                    this.loadStampConfigFor(file);
                    this.loadFile(file);
                }
            });

            // Initialize masks from package
            if (this.pkg && this.pkg.white_blocks) {
                try {
                    const blocks = typeof this.pkg.white_blocks === 'string' ? JSON.parse(this.pkg.white_blocks) : this.pkg.white_blocks;
                    if (Array.isArray(blocks)) {
                        this.masks = blocks.map(b => ({
                            id: b.id || Date.now() + Math.random(),
                            x: b.x || 0,
                            y: b.y || 0,
                            width: b.width || 100,
                            height: b.height || 50,
                            rotation: b.rotation || 0,
                            active: false,
                            visible: true,
                            editable: true
                        }));
                    }
                } catch (e) {
                    console.error('[FileViewer] Failed to parse white_blocks', e);
                }
            }

            // Debug: Log initial iges state
            // Initialize iges object

            // Verify and repair critical state objects if undefined
            if (!this.iges.exploded) {
                // console.warn('[FileViewer] Reparing iges.exploded state');
                this.iges.exploded = {
                    enabled: false,
                    factor: 0,
                    center: null,
                    originalPositions: null,
                    animating: false,
                    panelOpen: false
                };
            }
            if (!this.iges.clipping) {
                // console.warn('[FileViewer] Reparing iges.clipping state');
                this.iges.clipping = {
                    panelOpen: false,
                    min: -100, max: 100, step: 1,
                    x: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                    y: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                    z: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false }
                };
            }

            // Debug: Log final iges state after repair
            // console.log('[FileViewer] Init - After repair, iges.exploded:', this.iges.exploded);
            // console.log('[FileViewer] Init - After repair, iges.clipping:', this.iges.clipping);

            // Setup mouse event listeners for pan
            document.addEventListener('mousemove', (e) => this.onPan(e));
            document.addEventListener('mouseup', () => this.endPan());

            // Sync isFullscreen with native browser fullscreen state (for Esc key support)
            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
            });
        },

        // ===== FILE TYPE DETECTION =====
        extOf(name) {
            const i = (name || '').lastIndexOf('.');
            return i > -1 ? (name || '').slice(i + 1).toLowerCase() : '';
        },

        isImage(name) {
            const ext = this.extOf(name);
            return ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext);
        },

        isPdf(name) {
            return this.extOf(name) === 'pdf';
        },

        isTiff(name) {
            const ext = this.extOf(name);
            return ['tif', 'tiff'].includes(ext);
        },

        isHpgl(name) {
            const ext = this.extOf(name);
            return ['hpgl', 'plt', 'hpg'].includes(ext);
        },

        isCad(name) {
            const ext = this.extOf(name);
            return ['igs', 'iges', 'stp', 'step', 'stl', 'obj'].includes(ext);
        },

        isPreviewable2D(name) {
            return this.isImage(name) || this.isPdf(name) || this.isTiff(name) || this.isHpgl(name);
        },

        // ===== FILE LOADING =====
        loadFile(file) {
            if (!file) return;

            // Prevent redundant loading if it's the same file
            // This also fixes the issue where imgLoading gets stuck because @load doesn't fire for identical src
            if (this.lastLoadedUrl === file.url) {
                // If mirroring data from file to component (like blocks/masks), do it here without full reset
                if (this.enableMasking) {
                    this.initBlocksForFile(file);
                }
                return;
            }

            this.lastLoadedUrl = file.url;

            // Reset states
            this.resetViewerStates();

            // Load masks if enabled
            if (this.enableMasking) {
                this.initBlocksForFile(file);
            }

            // Load based on file type
            if (this.isImage(file.name)) {
                this.loadImage(file);
            } else if (this.isPdf(file.name)) {
                this.loadPdf(file);
            } else if (this.isTiff(file.name)) {
                this.loadTiff(file);
            } else if (this.isHpgl(file.name)) {
                this.loadHpgl(file);
            } else if (this.isCad(file.name)) {
                this.loadCad(file);
            }
        },

        getFileType(name) {
            if (this.isImage(name)) return 'image';
            if (this.isPdf(name)) return 'pdf';
            if (this.isTiff(name)) return 'tiff';
            if (this.isHpgl(name)) return 'hpgl';
            if (this.isCad(name)) return 'cad';
            return 'unknown';
        },

        resetViewerStates() {
            // Reset zoom/pan
            this.imageZoom = 1;
            this.panX = 0;
            this.panY = 0;

            // Reset loading states
            this.imgLoading = false;
            this.pdfLoading = false;
            this.tifLoading = false;
            this.hpglLoading = false;

            // Reset errors
            this.imgError = '';
            this.pdfError = '';
            this.tifError = '';
            this.hpglError = '';
            this.iges.error = '';

            // Reset page counters
            this.pdfPageNum = 1;
            this.pdfNumPages = 1;
            this.tifPageNum = 1;
            this.tifNumPages = 1;

            // Cancel any active PDF render task
            if (this.pdfRenderTask) {
                Alpine.raw(this.pdfRenderTask).cancel();
                this.pdfRenderTask = null;
            }
        },

        // ===== IMAGE VIEWER =====
        loadImage(file) {
            // console.log('[FileViewer] Loading image:', file.name);
            this.imgLoading = true;
            // Image loading is handled by browser's <img> tag
            // Loading state will be cleared by @load event
        },

        // ===== PDF VIEWER =====
        async loadPdf(file) {
            // console.log('[FileViewer] Loading PDF:', file.name);

            if (!window.pdfjsLib) {
                this.pdfError = 'PDF.js library not loaded';
                console.error('[FileViewer] PDF.js not available');
                return;
            }

            this.pdfLoading = true;
            this.pdfError = '';
            this.pdfPageNum = 1;

            try {
                const loadingTask = pdfjsLib.getDocument(file.url);
                this.pdfDoc = await loadingTask.promise;

                // Use Alpine.raw to access properties safely
                const rawDoc = Alpine.raw(this.pdfDoc);
                this.pdfNumPages = rawDoc.numPages;

                // console.log('[FileViewer] PDF loaded, pages:', this.pdfNumPages);

                await this.renderPdfPage();
            } catch (error) {
                console.error('[FileViewer] PDF loading error:', error);
                this.pdfError = 'Failed to load PDF: ' + error.message;
                this.pdfLoading = false;
            }
        },

        async renderPdfPage() {
            if (!this.pdfDoc) return;

            const canvas = this.$refs.pdfCanvas;
            if (!canvas) {
                console.error('[FileViewer] PDF canvas not found');
                return;
            }

            // Cancel previous render task if any and wait for it to settle
            if (this.pdfRenderTask) {
                try {
                    const rawPrevTask = Alpine.raw(this.pdfRenderTask);
                    rawPrevTask.cancel();
                    await rawPrevTask.promise;
                } catch (e) {
                    // Ignore cancel errors
                }
                this.pdfRenderTask = null;
            }

            try {
                // Use Alpine.raw to access the original PDF objects, bypassing Alpine's Proxy
                // which causes issues with private class members (#) in newer PDF.js versions.
                const rawDoc = Alpine.raw(this.pdfDoc);
                const page = await rawDoc.getPage(this.pdfPageNum);
                const rawPage = Alpine.raw(page);

                const viewport = rawPage.getViewport({ scale: this.pdfScale });

                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                // Store current task for potential cancellation
                const task = rawPage.render(renderContext);
                this.pdfRenderTask = task;

                await task.promise;
                this.pdfRenderTask = null; // Clear task on success

                this.pdfLoading = false;
                this.recalculateMasks();
                // console.log('[FileViewer] PDF page rendered:', this.pdfPageNum);
            } catch (error) {
                // If the error is 'Rendering cancelled', we ignore it
                if (error.name === 'RenderingCancelledException' || error.message.includes('cancelled')) {
                    // console.log('[FileViewer] PDF render task cancelled');
                    return;
                }

                console.error('[FileViewer] PDF rendering error:', error);
                this.pdfError = 'Failed to render page: ' + error.message;
                this.pdfLoading = false;
            }
        },

        nextPdfPage() {
            if (this.pdfPageNum < this.pdfNumPages) {
                this.pdfPageNum++;
                if (this.enableMasking) this.initBlocksForFile(this.selectedFile);
                this.renderPdfPage();
            }
        },

        prevPdfPage() {
            if (this.pdfPageNum > 1) {
                this.pdfPageNum--;
                if (this.enableMasking) this.initBlocksForFile(this.selectedFile);
                this.renderPdfPage();
            }
        },

        // ===== TIFF VIEWER =====
        async loadTiff(file) {
            // console.log('[FileViewer] Loading TIFF:', file.name);

            // Resolve UTIF library (handle potential nesting as in reference)
            const U = (window.UTIF && typeof window.UTIF.decode === 'function') ? window.UTIF :
                (window.UTIF && window.UTIF.UTIF && typeof window.UTIF.UTIF.decode === 'function') ? window.UTIF.UTIF :
                    null;

            if (!U) {
                this.tifError = 'UTIF library not found or incompatible';
                console.error('[FileViewer] UTIF.js not available');
                return;
            }

            this.tifLoading = true;
            this.tifError = '';
            this.tifPageNum = 1;

            try {
                const resp = await fetch(file.url, {
                    cache: 'no-store',
                    credentials: 'same-origin'
                });
                if (!resp.ok) throw new Error('Failed to fetch TIFF file');
                const buf = await resp.arrayBuffer();

                const ifds = U.decode(buf);
                if (!ifds || !ifds.length) throw new Error('TIFF file does not contain any frame');

                // Decode all frames/images immediately
                if (typeof U.decodeImages === 'function') {
                    U.decodeImages(buf, ifds);
                } else if (typeof U.decodeImage === 'function') {
                    ifds.forEach(ifd => U.decodeImage(buf, ifd));
                }

                // Store state
                this.tifDecoder = U; // Store reference to library
                this.tifIfds = ifds;
                this.tifNumPages = ifds.length;
                this.tifPageNum = 1;

                this.renderTiffPage();
            } catch (error) {
                console.error('[FileViewer] TIFF loading error:', error);
                this.tifError = 'Failed to load TIFF: ' + error.message;
                this.tifLoading = false;
            }
        },

        renderTiffPage() {
            if (!this.tifDecoder || !this.tifIfds || this.tifIfds.length === 0) return;

            const img = this.$refs.tifImg;
            if (!img) {
                console.error('[FileViewer] TIFF img element not found');
                return;
            }

            try {
                const U = this.tifDecoder;
                const ifd = this.tifIfds[this.tifPageNum - 1];
                if (!ifd) return;

                const rgba = U.toRGBA8(ifd);
                const w = ifd.width;
                const h = ifd.height;

                if (!w || !h) {
                    throw new Error(`Invalid dimensions: ${w}x${h}`);
                }

                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;

                const ctx = canvas.getContext('2d');
                const imageData = ctx.createImageData(w, h);
                imageData.data.set(new Uint8ClampedArray(rgba));
                ctx.putImageData(imageData, 0, 0);

                img.src = canvas.toDataURL('image/png');
                this.tifLoading = false;

                // console.log('[FileViewer] TIFF page rendered:', this.tifPageNum, `${w}x${h}`);
            } catch (error) {
                console.error('[FileViewer] TIFF rendering error:', error);
                this.tifError = 'Failed to render page: ' + error.message;
                this.tifLoading = false;
            }
        },

        nextTifPage() {
            if (this.tifPageNum < this.tifNumPages) {
                this.tifPageNum++;
                if (this.enableMasking) this.initBlocksForFile(this.selectedFile);
                this.renderTiffPage();
            }
        },

        prevTifPage() {
            if (this.tifPageNum > 1) {
                this.tifPageNum--;
                if (this.enableMasking) this.initBlocksForFile(this.selectedFile);
                this.renderTiffPage();
            }
        },

        // ===== HPGL VIEWER =====
        async loadHpgl(file) {
            // console.log('[FileViewer] Loading HPGL:', file.name);

            this.hpglLoading = true;
            this.hpglError = '';

            try {
                const resp = await fetch(file.url, {
                    cache: 'no-store',
                    credentials: 'same-origin'
                });
                if (!resp.ok) throw new Error('Failed to fetch HPGL file');
                const text = await resp.text();

                await this.renderHpgl(text);
            } catch (error) {
                console.error('[FileViewer] HPGL loading error:', error);
                this.hpglError = 'Failed to load HPGL: ' + error.message;
                this.hpglLoading = false;
            }
        },

        async renderHpgl(text) {
            const canvas = this.$refs.hpglCanvas;
            if (!canvas) {
                console.error('[FileViewer] HPGL canvas not found');
                return;
            }

            try {
                // Standardize separators: replace newlines with ';', then split by ';'
                let commands = text.replace(/[\r\n]+/g, ';').split(';');

                // many HPGL files concatenate commands without semicolons
                const expandedCommands = [];
                for (const cmd of commands) {
                    if (!cmd || !cmd.trim()) continue;

                    if (cmd.length > 10000) {
                        let i = 0;
                        while (i < cmd.length) {
                            if (i + 1 < cmd.length && /[A-Z]/.test(cmd[i]) && /[A-Z]/.test(cmd[i + 1])) {
                                const opcode = cmd.substring(i, i + 2);
                                i += 2;
                                let args = '';
                                while (i < cmd.length && !(/[A-Z]/.test(cmd[i]) && i + 1 < cmd.length && /[A-Z]/.test(cmd[i + 1]))) {
                                    args += cmd[i];
                                    i++;
                                }
                                expandedCommands.push(opcode + args);
                            } else {
                                i++;
                            }
                        }
                    } else {
                        const parts = cmd.match(/[A-Z]{2}[^A-Z]*/g);
                        if (parts && parts.length > 1) {
                            expandedCommands.push(...parts);
                        } else {
                            expandedCommands.push(cmd);
                        }
                    }
                }

                commands = expandedCommands;

                let penDown = false;
                let isRelative = false;
                let x = 0, y = 0;
                const segments = [];

                const parseCoords = (str) => {
                    if (!str || !str.trim()) return [];
                    return str.replace(/,/g, ' ').trim().split(/\s+/).map(Number).filter(v => !isNaN(v));
                };

                const addSegment = (x1, y1, x2, y2) => {
                    segments.push({ x1, y1, x2, y2 });
                };

                const addArc = (cx, cy, radius, startAngle, endAngle, steps = 30) => {
                    if (steps < 4) steps = 4;
                    const angleStep = (endAngle - startAngle) / steps;
                    let prevX = cx + radius * Math.cos(startAngle * Math.PI / 180);
                    let prevY = cy + radius * Math.sin(startAngle * Math.PI / 180);

                    for (let i = 1; i <= steps; i++) {
                        const angle = startAngle + angleStep * i;
                        const nx = cx + radius * Math.cos(angle * Math.PI / 180);
                        const ny = cy + radius * Math.sin(angle * Math.PI / 180);
                        addSegment(prevX, prevY, nx, ny);
                        prevX = nx;
                        prevY = ny;
                    }
                };

                for (const raw of commands) {
                    if (!raw || !raw.trim()) continue;

                    const cmd = raw.trim().toUpperCase();
                    const op = cmd.slice(0, 2);
                    const argsStr = cmd.slice(2);
                    const coords = parseCoords(argsStr);

                    const processMove = () => {
                        for (let i = 0; i < coords.length; i += 2) {
                            if (i + 1 >= coords.length) break;
                            let nx = coords[i], ny = coords[i + 1];

                            if (isRelative) {
                                nx += x;
                                ny += y;
                            }

                            if (penDown) addSegment(x, y, nx, ny);
                            x = nx; y = ny;
                        }
                    };

                    if (op === 'IN') {
                        penDown = false; isRelative = false; x = 0; y = 0;
                    } else if (op === 'PU') {
                        penDown = false; if (coords.length > 0) processMove();
                    } else if (op === 'PD') {
                        penDown = true; if (coords.length > 0) processMove();
                    } else if (op === 'PA') {
                        isRelative = false; if (coords.length > 0) processMove();
                    } else if (op === 'PR') {
                        isRelative = true; if (coords.length > 0) processMove();
                    } else if (op === 'CI') {
                        if (coords.length >= 1) {
                            const radius = Math.abs(coords[0]);
                            addArc(x, y, radius, 0, 360, 64);
                        }
                    } else if (op === 'AA') {
                        if (coords.length >= 3) {
                            const cx = coords[0], cy = coords[1], sweepAngle = coords[2];
                            const radius = Math.sqrt((x - cx) ** 2 + (y - cy) ** 2);
                            const startAngle = Math.atan2(y - cy, x - cx) * 180 / Math.PI;
                            const endAngle = startAngle + sweepAngle;
                            addArc(cx, cy, radius, startAngle, endAngle, Math.max(16, Math.abs(sweepAngle) / 5));
                            x = cx + radius * Math.cos(endAngle * Math.PI / 180);
                            y = cy + radius * Math.sin(endAngle * Math.PI / 180);
                        }
                    } else if (op === 'AR') {
                        if (coords.length >= 3) {
                            const cx = x + coords[0], cy = y + coords[1], sweepAngle = coords[2];
                            const radius = Math.sqrt(coords[0] ** 2 + coords[1] ** 2);
                            const startAngle = Math.atan2(-coords[1], -coords[0]) * 180 / Math.PI;
                            const endAngle = startAngle + sweepAngle;
                            addArc(cx, cy, radius, startAngle, endAngle, Math.max(16, Math.abs(sweepAngle) / 5));
                            x = cx + radius * Math.cos(endAngle * Math.PI / 180);
                            y = cy + radius * Math.sin(endAngle * Math.PI / 180);
                        }
                    }
                }

                if (!segments.length) throw new Error('No drawable content found');

                // 2. Bounds Calculation
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                for (const s of segments) {
                    minX = Math.min(minX, s.x1, s.x2);
                    maxX = Math.max(maxX, s.x1, s.x2);
                    minY = Math.min(minY, s.y1, s.y2);
                    maxY = Math.max(maxY, s.y1, s.y2);
                }

                // 3. Viewport setup
                let container = canvas.parentElement;
                while (container && (container.clientWidth < 400 || container.clientHeight < 300)) {
                    container = container.parentElement;
                    if (!container || container === document.body) break;
                }
                const viewW = Math.max(container?.clientWidth || 800, 800);
                const viewH = Math.max(container?.clientHeight || 600, 600);

                const dpr = window.devicePixelRatio || 1;
                const totalScale = dpr * 5; // Support zoom

                canvas.width = viewW * totalScale;
                canvas.height = viewH * totalScale;
                canvas.style.width = viewW + 'px';
                canvas.style.height = viewH + 'px';

                const ctx = canvas.getContext('2d');
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.scale(totalScale, totalScale);

                ctx.lineWidth = 0.2;
                ctx.strokeStyle = '#000';

                const dx = maxX - minX || 1, dy = maxY - minY || 1;
                const fitScale = 0.98 * Math.min(viewW / dx, viewH / dy);
                const transX = viewW / 2 - (minX + dx / 2) * fitScale;
                const transY = viewH / 2 + (minY + dy / 2) * fitScale;

                ctx.beginPath();
                for (const s of segments) {
                    ctx.moveTo(s.x1 * fitScale + transX, -s.y1 * fitScale + transY);
                    ctx.lineTo(s.x2 * fitScale + transX, -s.y2 * fitScale + transY);
                }
                ctx.stroke();

                this.hpglDrawingBounds = {
                    left: minX * fitScale + transX,
                    top: -maxY * fitScale + transY,
                    width: dx * fitScale,
                    height: dy * fitScale
                };

                this.hpglLoading = false;
                this.recalculateMasks();
                // HPGL rendered
            } catch (error) {
                console.error('[FileViewer] HPGL rendering error:', error);
                this.hpglError = 'Failed to render: ' + error.message;
                this.hpglLoading = false;
            }
        },


        // ===== 3D CAD VIEWER - PHASE 1: SCENE INITIALIZATION =====
        async loadCad(file) {
            // console.log('[FileViewer] Loading 3D CAD:', file.name);

            if (!window.occtimportjs) {
                this.iges.error = 'OCCT-import-js library not loaded';
                console.error('[FileViewer] OCCT-import-js not available');
                return;
            }

            // CRITICAL: Prevent concurrent loads
            if (this._cadLoading) {
                console.warn('[FileViewer] Already loading a CAD file, ignoring duplicate call');
                return;
            }
            this._cadLoading = true;

            // Cleanup previous CAD scene
            this.disposeCad();

            this.iges.loading = true;
            this.iges.error = '';

            // Reset tool states
            if (this.iges.exploded) {
                this.iges.exploded.enabled = false;
                this.iges.exploded.originalPositions = null;
            }
            if (this.iges.clipping) {
                this.iges.clipping.panelOpen = false;
            }

            try {
                // Import Three.js and dependencies
                const THREE = await import('three');
                const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');
                const bvh = await import('three-mesh-bvh');

                // Enable BVH acceleration for raycasting
                THREE.Mesh.prototype.raycast = bvh.acceleratedRaycast;
                THREE.BufferGeometry.prototype.computeBoundsTree = bvh.computeBoundsTree;
                THREE.BufferGeometry.prototype.disposeBoundsTree = bvh.disposeBoundsTree;

                // Initialize scene
                const scene = new THREE.Scene();
                scene.background = null;

                // Wait for DOM and container dimensions
                let wrap = null;
                for (let i = 0; i < 20; i++) {
                    wrap = this.$refs.igesWrap;
                    if (wrap && wrap.clientWidth > 0) break;
                    await new Promise(r => setTimeout(r, 50));
                }

                if (!wrap) {
                    throw new Error('CAD container (igesWrap) not found or has no size after waiting');
                }

                const width = wrap?.clientWidth || 800;
                const height = wrap?.clientHeight || 500;

                // Create camera
                const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 10000);
                camera.position.set(250, 200, 250);

                // Create renderer
                const renderer = new THREE.WebGLRenderer({
                    antialias: true,
                    alpha: true,
                    preserveDrawingBuffer: true,
                    // logarithmicDepthBuffer: true,
                    powerPreference: 'high-performance'
                });
                renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
                renderer.setSize(width, height);
                renderer.localClippingEnabled = true;
                renderer.outputColorSpace = THREE.SRGBColorSpace;
                wrap.appendChild(renderer.domElement);
                wrap.style.position = 'relative';
                wrap.style.overflow = 'hidden';

                // Add lights
                const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
                scene.add(ambientLight);
                scene.add(camera);

                const keyLight = new THREE.DirectionalLight(0xffffff, 0.7);
                keyLight.position.set(50, 50, 100);
                camera.add(keyLight);

                const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
                fillLight.position.set(-50, -50, 100);
                camera.add(fillLight);

                // Create controls
                const controls = new OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.dampingFactor = 0.05;
                controls.enabled = true;
                controls.enableRotate = true;
                controls.enableZoom = true;
                controls.enablePan = true;

                // console.log('[FileViewer] OrbitControls created');

                // Store event listeners for cleanup
                this._mouseListeners = [];

                // Test: Add direct mouse listeners to verify events are working
                const mousedownHandler = (e) => {
                    // console.log('[FileViewer] Mouse event detected:', e.type);
                };

                const mousemoveHandler = (e) => {
                    // console.log('[FileViewer] Mouse move tracked:', e.clientX, e.clientY);
                };

                renderer.domElement.addEventListener('mousedown', mousedownHandler);
                renderer.domElement.addEventListener('mousemove', mousemoveHandler);

                // Store for cleanup
                this._mouseListeners.push(
                    { element: renderer.domElement, type: 'mousedown', handler: mousedownHandler },
                    { element: renderer.domElement, type: 'mousemove', handler: mousemoveHandler }
                );

                // Fetch CAD file
                const resp = await fetch(file.url, {
                    cache: 'no-store',
                    credentials: 'same-origin'
                });
                if (!resp.ok) throw new Error('Failed to fetch CAD file');
                const mainBuf = new Uint8Array(await resp.arrayBuffer());

                // console.log('[FileViewer] CAD file loaded');

                // Parse CAD file with OCCT
                const occt = await window.occtimportjs();
                const fileName = file?.name || '';
                const ext = this.extOf(fileName);
                // console.log('[FileViewer] Extension detected:', ext);

                const params = {
                    linearUnit: 'millimeter',
                    linearDeflectionType: 'bounding_box_ratio',
                    linearDeflection: 0.1,
                    angularDeflection: 0.1,
                };

                let res = null;

                if (ext === 'stp' || ext === 'step') {
                    // console.log('[FileViewer] Parsing STEP');
                    res = occt.ReadStepFile(mainBuf, params);

                    if (!res || !res.success) {
                        // console.warn('[FileViewer] STEP failed, trying IGES fallback');
                        const igesFile = this._findIgesSibling(file);
                        if (igesFile?.url) {
                            const igResp = await fetch(igesFile.url, {
                                cache: 'no-store',
                                credentials: 'same-origin'
                            });
                            if (igResp.ok) {
                                const igBuf = new Uint8Array(await igResp.arrayBuffer());
                                res = occt.ReadIgesFile(igBuf, params);
                            }
                        }
                    }
                } else if (ext === 'brep') {
                    // console.log('[FileViewer] Parsing BREP');
                    res = occt.ReadBrepFile(mainBuf, params);
                } else {
                    // console.log('[FileViewer] Parsing IGES');
                    res = occt.ReadIgesFile(mainBuf, params);
                }

                if (!res || !res.success) {
                    const msg = res?.error || res?.message || 'File is not a valid STEP/IGES/BREP';
                    throw new Error('OCCT failed to parse file: ' + msg);
                }

                // Build Three.js meshes from OCCT result
                const group = this._buildThreeFromOcct(res, THREE);
                scene.add(group);

                // Save references
                this.iges.rootModel = group;
                this.iges.scene = scene;
                this.iges.camera = camera;
                this.iges.renderer = renderer;
                this.iges.controls = controls;
                this.iges.THREE = THREE;

                // Cache original materials
                this._cacheOriginalMaterials(group, THREE);

                // Calculate bounding box and auto-fit camera
                const box = new THREE.Box3().setFromObject(group);
                const size = new THREE.Vector3();
                box.getSize(size);
                const center = new THREE.Vector3();
                box.getCenter(center);

                // Center model at origin
                group.position.sub(center);

                // Auto-fit camera
                const maxDim = Math.max(size.x, size.y, size.z) || 100;
                const fitDist = maxDim / (2 * Math.tan((camera.fov * Math.PI) / 360));
                const viewDirection = new THREE.Vector3(1, 1, 1).normalize();

                camera.position.copy(viewDirection.multiplyScalar(fitDist * 1.6));
                camera.near = 0.1;
                camera.far = 100000;
                camera.updateProjectionMatrix();

                controls.target.set(0, 0, 0);
                controls.update();

                // Set default display style
                this.setDisplayStyle('shaded-edges');

                // Animation loop
                // console.log('[FileViewer] Animation loop starting');

                let frameCount = 0;
                const animate = () => {
                    try {
                        // Use local controls variable directly (not from this.iges)
                        if (!controls) {
                            console.error('[FileViewer] Controls is null in animation loop!');
                            return;
                        }

                        controls.update();

                        frameCount++;
                        // if (frameCount % 60 === 0) {
                        //     console.log('[FileViewer] Animation frame:', frameCount);
                        // }

                        // Use Alpine.raw to avoid reactivity issues
                        const rawRenderer = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(renderer) : renderer;
                        const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;
                        let activeCam = this.iges.camera;
                        if (typeof Alpine !== 'undefined' && Alpine.raw) {
                            activeCam = Alpine.raw(activeCam);
                        }

                        if (activeCam && rawRenderer && rawScene) {
                            rawRenderer.render(rawScene, activeCam);
                        }

                        // Update measurement labels
                        const g = this.iges.measure.group;
                        if (g) {
                            const rawGroup = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(g) : g;
                            if (rawGroup && rawGroup.children) {
                                rawGroup.children.forEach(ch => ch.userData?.update?.());
                            }
                        }

                        this.iges.animId = requestAnimationFrame(animate);
                    } catch (error) {
                        console.error('[FileViewer] Animation loop error:', error);
                        this.iges.animId = requestAnimationFrame(animate);
                    }
                };
                animate();

                // Resize observer
                const resizeObserver = new ResizeObserver(() => {
                    const w = wrap.clientWidth;
                    const h = wrap.clientHeight;

                    if (w === 0 || h === 0) return;

                    if (camera && camera.updateProjectionMatrix) {
                        if (camera.isOrthographicCamera) {
                            const aspect = w / h;
                            const frustumHeight = (camera.top - camera.bottom);
                            const frustumWidth = frustumHeight * aspect;
                            camera.left = -frustumWidth / 2;
                            camera.right = frustumWidth / 2;
                            camera.top = frustumHeight / 2;
                            camera.bottom = -frustumHeight / 2;
                        } else {
                            camera.aspect = w / h;
                        }
                        camera.updateProjectionMatrix();
                    }

                    if (renderer) {
                        renderer.setSize(w, h);
                    }
                });

                resizeObserver.observe(wrap);
                this._resizeObserver = resizeObserver;

                this.iges.loading = false;
                this._cadLoading = false;
                // console.log('[FileViewer] 3D CAD loaded successfully');

            } catch (error) {
                console.error('[FileViewer] 3D CAD loading error:', error);
                this.iges.error = 'Failed to load 3D CAD: ' + error.message;
                this.iges.loading = false;
                this._cadLoading = false;
            }
        },

        // Build Three.js meshes from OCCT result
        _buildThreeFromOcct(result, THREE) {
            const group = new THREE.Group();
            const meshes = result.meshes || [];

            this.cadPartsList = [];

            for (let i = 0; i < meshes.length; i++) {
                const m = meshes[i];

                const g = new THREE.BufferGeometry();
                g.setAttribute('position', new THREE.Float32BufferAttribute(m.attributes.position.array, 3));
                if (m.attributes.normal?.array) {
                    g.setAttribute('normal', new THREE.Float32BufferAttribute(m.attributes.normal.array, 3));
                }
                if (m.index?.array) {
                    g.setIndex(m.index.array);
                }

                if (g.attributes.position.count > 0) {
                    g.computeBoundsTree();
                }

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
                mesh.name = m.name || `Part ${i + 1}`;
                group.add(mesh);

                this.cadPartsList.push({
                    uuid: mesh.uuid,
                    name: mesh.name
                });
            }

            return group;
        },

        // Find IGES sibling file for STEP fallback
        _findIgesSibling(mainFile) {
            if (!mainFile) return null;
            const name = mainFile.name || '';
            const base = name.replace(/\.(stp|step)$/i, '');

            const candidates = [];
            if (base) {
                candidates.push(base + '.igs', base + '.iges');
            }
            candidates.push('temp.igs', 'temp.iges');

            for (const cand of candidates) {
                const f = this.findFileByNameInsensitive(cand);
                if (f) return f;
            }

            const groups = this.pkg.files || {};
            for (const key of Object.keys(groups)) {
                const list = groups[key] || [];
                const hit = list.find(f => /\.(igs|iges)$/i.test(f.name || ''));
                if (hit) return hit;
            }

            return null;
        },

        findFileByNameInsensitive(name) {
            if (!name) return null;
            const target = name.toLowerCase();
            const groups = this.pkg.files || {};

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

        // Cache original materials for display style switching
        _oriMats: new Map(),
        _cacheOriginalMaterials(root, THREE) {
            root.traverse(o => {
                if (o.isMesh && !this._oriMats.has(o)) {
                    const m = o.material;
                    this._oriMats.set(o, Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone());
                }
            });
        },

        // Cleanup CAD scene
        disposeCad() {
            this.cameraMode = 'perspective';
            this.autoRotate = false;
            this.selectedPartUuid = null;
            this.partOpacity = 1.0;

            if (this.headlight) {
                this.headlight.enabled = false;
                this.headlight.object = null;
            }

            try {
                // console.log('[FileViewer] === disposeCad START ===');

                // Cancel Tour Interval
                if (this.iges.tourInterval) {
                    clearInterval(this.iges.tourInterval);
                    this.iges.tourInterval = null;
                }

                // CRITICAL: Cancel animation BEFORE resetting iges
                const currentAnimId = this.iges?.animId || 0;
                // console.log('[FileViewer] Canceling animation frame:', currentAnimId);
                if (currentAnimId) {
                    cancelAnimationFrame(currentAnimId);
                }

                if (this._resizeObserver) {
                    this._resizeObserver.disconnect();
                    this._resizeObserver = null;
                }

                // Remove mouse event listeners
                if (this._mouseListeners) {
                    this._mouseListeners.forEach(({ element, type, handler }) => {
                        if (element && handler) {
                            element.removeEventListener(type, handler);
                        }
                    });
                    this._mouseListeners = [];
                }

                // Unbind measurement events if active
                if (this.iges.measure?.enabled) {
                    this._bindMeasureEvents(false);
                }

                const { renderer, scene, controls } = this.iges || {};
                // console.log('[FileViewer] Cleanup state:', {
                //     hasRenderer: !!renderer,
                //     hasScene: !!scene,
                //     hasControls: !!controls,
                //     mouseListeners: this._mouseListeners?.length || 0,
                //     animIdWas: currentAnimId
                // });

                if (controls) {
                    try {
                        // console.log('[FileViewer] Disposing controls');
                        controls.dispose();
                    } catch (e) {
                        console.error('[FileViewer] Controls dispose error:', e);
                    }
                }

                if (scene) {
                    scene.traverse(o => {
                        if (o.geometry) o.geometry.dispose();
                        if (o.material) {
                            const m = o.material;
                            if (Array.isArray(m)) {
                                m.forEach(mm => mm.dispose());
                            } else {
                                m.dispose();
                            }
                        }
                    });
                }

                if (renderer) {
                    try {
                        renderer.dispose();
                        const canvas = renderer.domElement;
                        if (canvas && canvas.parentNode) {
                            canvas.parentNode.removeChild(canvas);
                        }
                    } catch (e) { }
                }

                const wrap = this.$refs.igesWrap;
                if (wrap) {
                    // console.log('[FileViewer] Clearing wrap, children:', wrap.childNodes.length);
                    while (wrap.firstChild) wrap.removeChild(wrap.firstChild);
                }

                // console.log('[FileViewer] === disposeCad END ===');
            } catch (e) {
                console.error('[FileViewer] Cleanup error:', e);
            }

            // FULL RESET - Preserving structure to avoid Alpine.js expression errors
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
                    p2: null,
                    p3: null,
                    mode: 'point',
                    snap: {
                        enabled: true,
                        type: null,
                        point: null,
                        normal: null,
                        edge: null
                    },
                    results: [],
                    hoverInstruction: 'Select Start Point'
                },
                clipping: {
                    panelOpen: false,
                    min: -100, max: 100, step: 1,
                    x: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                    y: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                    z: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false }
                },
                exploded: {
                    enabled: false,
                    factor: 0,
                    center: null,
                    originalPositions: null,
                    animating: false,
                    panelOpen: false
                }
            };
            this.cadPartsList = [];
            if (this._oriMats) this._oriMats.clear();
        },

        // ===== 3D CAD CONTROLS - PHASE 2: DISPLAY STYLES =====
        setDisplayStyle(mode) {
            const root = this.iges.rootModel;
            if (!root) {
                console.warn('[FileViewer] No 3D model loaded');
                return;
            }

            // console.log('[FileViewer] Setting display style:', mode);
            this.currentStyle = mode;

            // Fix: If a material skin is active, re-apply it (this will also apply the new style edges)
            if (this.activeMaterial && this.activeMaterial !== 'default') {
                this.setMaterialMode(this.activeMaterial);
            } else {
                // Standard logic for default material
                this._restoreMaterials(root);

                if (mode === 'shaded') {
                    // Normal shaded mode - materials already restored
                } else if (mode === 'shaded-edges') {
                    // Shaded with edges
                    this._setPolygonOffset(root, true, 1, 1);
                    this._toggleEdges(root, true, 0x000000);
                } else if (mode === 'wireframe') {
                    // Pure wireframe mode
                    this._setWireframe(root, true);
                }
            }

            // Fix: Sync clipping after style change
            this._updateMaterialsWithClipping();
            this._forceRender();
        },

        // Restore original materials
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

            // Fix: Sync clipping after restoration
            this._updateMaterialsWithClipping();
        },

        // Set wireframe mode
        _setWireframe(root, on = true) {
            root.traverse(o => {
                if (!o.isMesh) return;
                (Array.isArray(o.material) ? o.material : [o.material]).forEach(m => m.wireframe = on);
            });
        },

        // Set polygon offset (for edges rendering)
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

        // Add edge geometry to mesh
        _addEdges(mesh, THREE, threshold = 30) {
            if (mesh.userData.edges) return mesh.userData.edges;

            const edgesGeo = new THREE.EdgesGeometry(mesh.geometry, threshold);
            const edgesMat = new THREE.LineBasicMaterial({
                color: 0x000000,
                transparent: true,
                opacity: 0.6,
                depthTest: true // Match reference: depthTest should be true
            });
            const edges = new THREE.LineSegments(edgesGeo, edgesMat);
            edges.renderOrder = 1; // Match reference: renderOrder should be low
            mesh.add(edges);
            mesh.userData.edges = edges;
            return edges;
        },

        // Toggle edges on/off
        _toggleEdges(root, on = true, color = 0x000000) {
            const THREE = this.iges.THREE;
            if (!THREE) return;

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

        // ===== 3D CAD CONTROLS - PHASE 3: PART HIGHLIGHTING & OPACITY =====
        highlightPart(uuid) {
            const root = this.iges.rootModel;
            const THREE = this.iges.THREE;
            if (!root || !THREE) return;

            console.log('[FileViewer] Highlighting part:', uuid);

            // Reset previous highlight
            if (this.selectedPartUuid) {
                root.traverse(o => {
                    if (o.uuid === this.selectedPartUuid && o.isMesh) {
                        // Restore original material
                        const origMat = this._oriMats.get(o);
                        if (origMat) {
                            o.material = Array.isArray(origMat) ? origMat.map(m => m.clone()) : origMat.clone();
                        }
                    }
                });
            }

            // Set new selection
            this.selectedPartUuid = uuid;
            this.partOpacity = 1.0; // Reset opacity

            // Highlight new part
            root.traverse(o => {
                if (o.uuid === uuid && o.isMesh) {
                    const mats = Array.isArray(o.material) ? o.material : [o.material];
                    mats.forEach(m => {
                        m.emissive = new THREE.Color(0x4444ff);
                        m.emissiveIntensity = 0.3;
                    });
                }
            });
        },

        updatePartOpacity() {
            const root = this.iges.rootModel;
            const THREE = this.iges.THREE;
            if (!root || !THREE || !this.selectedPartUuid) return;

            console.log('[FileViewer] Updating part opacity:', this.partOpacity);

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
        },


        // ===== 3D CAD CONTROLS - PHASE 4: MEASUREMENT TOOLS =====

        // Set measurement mode
        setMeasureMode(mode) {
            const M = this.iges.measure;
            M.mode = mode;
            M.p1 = null;
            M.p2 = null;
            M.p3 = null;

            // Update instruction based on mode
            switch (mode) {
                case 'point': M.hoverInstruction = 'Click 1st Point'; break;
                case 'edge': M.hoverInstruction = 'Click an Edge or 1st Point'; break;
                case 'angle': M.hoverInstruction = 'Click 1st Point (Start)'; break;
                case 'radius': M.hoverInstruction = 'Click 1st Point on Curve'; break;
                case 'face': M.hoverInstruction = 'Click a Planar Face'; break;
            }
            console.log('[FileViewer] Measure mode set to:', mode);
        },

        toggleMeasure() {
            const M = this.iges.measure;
            M.enabled = !M.enabled;

            // Sync UI state
            this.isMeasureActive = M.enabled;
            this.isMeasureListOpen = M.enabled;

            if (M.enabled) {
                // Create measurement group if not exists
                if (!M.group) {
                    const THREE = this.iges.THREE;
                    M.group = new THREE.Group();
                    M.group.renderOrder = 999;
                    this.iges.scene.add(M.group);
                }
                this._bindMeasureEvents(true);
                this.setMeasureMode(M.mode);

                // Keep controls enabled for rotation
                const rawControls = Alpine.raw(this.iges.controls);
                if (rawControls) rawControls.enabled = true;
            } else {
                this._bindMeasureEvents(false);
                if (this.snapMarker) this.snapMarker.visible = false;
                M.p1 = M.p2 = M.p3 = null;

                // Re-enable controls
                const rawControls = Alpine.raw(this.iges.controls);
                if (rawControls) rawControls.enabled = true;
            }

            // Measure tool toggled
        },

        clearMeasurements() {
            const M = this.iges.measure;
            const g = M.group;
            if (!g) return;

            // Dispose all children properly
            (g.children || []).slice().forEach(ch => {
                if (ch.userData?.dispose) ch.userData.dispose();
                g.remove(ch);
            });

            g.clear();
            M.results = [];
            M.p1 = M.p2 = M.p3 = null;
            M.hoverInstruction = 'Measurements Cleared';
            setTimeout(() => this.setMeasureMode(M.mode), 1500);

            // Measurements cleared
        },

        deleteMeasurement(index) {
            const M = this.iges.measure;
            if (index < 0 || index >= M.results.length) return;

            // Remove from scene
            const res = M.results[index];
            if (res.objectUuid && M.group) {
                const obj = M.group.getObjectByProperty('uuid', res.objectUuid);
                if (obj) {
                    if (obj.userData?.dispose) obj.userData.dispose();
                    M.group.remove(obj);
                }
            }

            // Remove from array
            M.results.splice(index, 1);
        },

        // Bind/unbind measurement event listeners
        _bindMeasureEvents(on) {
            const canvas = this.iges.renderer?.domElement;
            if (!canvas) return;

            if (on) {
                // Event Click - for picking measurement points
                this._onMeasureClick = (ev) => {
                    if (!this.iges.measure.enabled) return;

                    ev.stopPropagation();

                    const M = this.iges.measure;
                    const pickResult = this._pickPointAdvanced(ev);
                    if (!pickResult) return;

                    const p = pickResult.point;

                    // --- POINT TO POINT ---
                    if (M.mode === 'point') {
                        if (!M.p1) {
                            M.p1 = p;
                            M.snap.type = pickResult.snapType;
                            M.hoverInstruction = 'Click 2nd Point';
                        } else {
                            this._drawMeasurement(M.p1, p, 'point');
                            M.p1 = null;
                            M.hoverInstruction = 'Click 1st Point';
                        }
                    }
                    // --- EDGE (Auto Length + Manual Fallback) ---
                    else if (M.mode === 'edge') {
                        if (pickResult.edge && (pickResult.snapType === 'edge' || pickResult.snapType === 'midpoint')) {
                            this._drawMeasurement(pickResult.edge.start, pickResult.edge.end, 'edge');
                            M.p1 = null;
                            M.hoverInstruction = 'Click another Edge';
                        } else {
                            if (!M.p1) {
                                M.p1 = p;
                                M.snap.type = pickResult.snapType;
                                M.hoverInstruction = 'Click 2nd Point (Manual)';
                            } else {
                                this._drawMeasurement(M.p1, p, 'edge');
                                M.p1 = null;
                                M.hoverInstruction = 'Click 1st Point or Select Edge';
                            }
                        }
                    }
                    // --- ANGLE (3 Points) ---
                    else if (M.mode === 'angle') {
                        if (!M.p1) {
                            M.p1 = p;
                            M.hoverInstruction = 'Click Vertex (2nd Point)';
                        } else if (!M.p2) {
                            M.p2 = p;
                            M.hoverInstruction = 'Click End Point (3rd Point)';
                        } else {
                            this._drawAngleMeasurement(M.p1, M.p2, p);
                            M.p1 = M.p2 = null;
                            M.hoverInstruction = 'Click 1st Point (Start)';
                        }
                    }
                    // --- RADIUS (3 Points) ---
                    else if (M.mode === 'radius') {
                        if (!M.p1) {
                            M.p1 = p;
                            M.hoverInstruction = 'Click 2nd Point on Curve';
                        } else if (!M.p2) {
                            M.p2 = p;
                            M.hoverInstruction = 'Click 3rd Point on Curve';
                        } else {
                            M.p3 = p;
                            const circle = this._calculateCircleFrom3Points(M.p1, M.p2, M.p3);
                            if (circle) {
                                this._drawRadiusMeasurement(circle, M.p1, M.p2, M.p3);
                            } else {
                                console.warn('[FileViewer] Points are collinear, cannot calculate circle');
                            }
                            M.p1 = M.p2 = M.p3 = null;
                            M.hoverInstruction = 'Click 1st Point on Curve';
                        }
                    }
                    // --- FACE AREA ---
                    else if (M.mode === 'face') {
                        if (pickResult.hit && pickResult.hit.face) {
                            const area = this._calculateFaceArea(pickResult.hit.object, pickResult.hit.faceIndex);
                            this._drawFaceAreaMeasurement(pickResult.point, area, pickResult.normal, pickResult.hit.object);
                            M.hoverInstruction = 'Click another Face';
                        }
                    }
                };

                // Event Hover - for snap preview
                this._onMeasureMove = (ev) => {
                    if (!this.iges.measure.enabled) return;
                    this._pickPointAdvanced(ev);
                };

                // Right-click to cancel
                this._onMeasureRightClick = (ev) => {
                    ev.preventDefault();
                    const M = this.iges.measure;
                    M.p1 = null;
                    M.p2 = null;
                    M.p3 = null;
                    this.setMeasureMode(M.mode);
                    if (this.snapMarker) this.snapMarker.visible = false;
                };

                canvas.addEventListener('click', this._onMeasureClick);
                canvas.addEventListener('mousemove', this._onMeasureMove);
                canvas.addEventListener('contextmenu', this._onMeasureRightClick);
            } else {
                if (this._onMeasureClick) canvas.removeEventListener('click', this._onMeasureClick);
                if (this._onMeasureMove) canvas.removeEventListener('mousemove', this._onMeasureMove);
                if (this._onMeasureRightClick) canvas.removeEventListener('contextmenu', this._onMeasureRightClick);

                if (this.snapMarker) this.snapMarker.visible = false;
            }
        },


        // Advanced raycasting with snap detection
        _pickPointAdvanced(ev) {
            const { THREE, camera, rootModel, renderer } = this.iges;
            if (!renderer) return null;

            const rect = renderer.domElement.getBoundingClientRect();
            const mouse = new THREE.Vector2(
                ((ev.clientX - rect.left) / rect.width) * 2 - 1,
                -((ev.clientY - rect.top) / rect.height) * 2 + 1
            );

            const raycaster = new THREE.Raycaster();
            raycaster.setFromCamera(mouse, camera);
            raycaster.firstHitOnly = true;

            const hits = raycaster.intersectObjects(rootModel.children, true);
            if (!hits.length) {
                if (this.snapMarker) this.snapMarker.visible = false;
                return null;
            }

            const hit = hits[0];
            let finalPoint = hit.point.clone();
            let snapType = 'surface';
            let edgeInfo = null;
            let faceNormal = null;

            if (hit.face) {
                const mesh = hit.object;
                const pos = mesh.geometry.attributes.position;

                // Get triangle vertices
                const vA = new THREE.Vector3().fromBufferAttribute(pos, hit.face.a).applyMatrix4(mesh.matrixWorld);
                const vB = new THREE.Vector3().fromBufferAttribute(pos, hit.face.b).applyMatrix4(mesh.matrixWorld);
                const vC = new THREE.Vector3().fromBufferAttribute(pos, hit.face.c).applyMatrix4(mesh.matrixWorld);

                // Store face normal
                faceNormal = hit.face.normal.clone().transformDirection(mesh.matrixWorld);

                // Get edge midpoints
                const midAB = vA.clone().add(vB).multiplyScalar(0.5);
                const midBC = vB.clone().add(vC).multiplyScalar(0.5);
                const midCA = vC.clone().add(vA).multiplyScalar(0.5);

                const snapThreshold = hit.distance * 0.05;
                const edgeSnapThreshold = hit.distance * 0.03;

                // Check vertex snap
                const distA = hit.point.distanceTo(vA);
                const distB = hit.point.distanceTo(vB);
                const distC = hit.point.distanceTo(vC);

                let closest = null;
                let minDist = snapThreshold;

                if (distA < minDist) { closest = vA; minDist = distA; snapType = 'vertex'; }
                if (distB < minDist) { closest = vB; minDist = distB; snapType = 'vertex'; }
                if (distC < minDist) { closest = vC; minDist = distC; snapType = 'vertex'; }

                // Check midpoint snap
                if (!closest) {
                    const distMidAB = hit.point.distanceTo(midAB);
                    const distMidBC = hit.point.distanceTo(midBC);
                    const distMidCA = hit.point.distanceTo(midCA);

                    if (distMidAB < edgeSnapThreshold) { closest = midAB; snapType = 'midpoint'; edgeInfo = { start: vA, end: vB }; }
                    else if (distMidBC < edgeSnapThreshold) { closest = midBC; snapType = 'midpoint'; edgeInfo = { start: vB, end: vC }; }
                    else if (distMidCA < edgeSnapThreshold) { closest = midCA; snapType = 'midpoint'; edgeInfo = { start: vC, end: vA }; }
                }

                // Check edge snap
                if (!closest) {
                    const edges = [
                        { start: vA, end: vB },
                        { start: vB, end: vC },
                        { start: vC, end: vA }
                    ];

                    for (const edge of edges) {
                        const projected = this._projectPointOnLine(hit.point, edge.start, edge.end);
                        if (projected && hit.point.distanceTo(projected) < edgeSnapThreshold) {
                            closest = projected;
                            snapType = 'edge';
                            edgeInfo = edge;
                            break;
                        }
                    }
                }

                if (closest) {
                    finalPoint = closest;
                }
            }

            if (!this.iges.measure.snap.enabled) {
                snapType = 'surface';
                finalPoint = hit.point.clone();
            } else {
                this._updateSnapMarkerAdvanced(finalPoint, snapType);
            }

            return {
                point: finalPoint,
                snapType: snapType,
                edge: edgeInfo,
                normal: faceNormal,
                hit: hit
            };
        },

        // Project point onto a line segment
        _projectPointOnLine(point, lineStart, lineEnd) {
            const THREE = this.iges.THREE;
            const line = lineEnd.clone().sub(lineStart);
            const lineLen = line.length();
            if (lineLen === 0) return null;

            const lineDir = line.normalize();
            const pointVec = point.clone().sub(lineStart);
            const t = pointVec.dot(lineDir);

            // Check if projection is within line segment
            if (t < 0 || t > lineLen) return null;

            return lineStart.clone().add(lineDir.multiplyScalar(t));
        },

        // Update snap marker with color based on snap type
        _updateSnapMarkerAdvanced(position, snapType) {
            const { THREE, scene } = this.iges;

            if (!this.snapMarker) {
                const geom = new THREE.SphereGeometry(1.2, 16, 16);
                const mat = new THREE.MeshBasicMaterial({
                    color: 0xff0000,
                    transparent: true,
                    opacity: 0.7,
                    depthTest: false
                });
                this.snapMarker = new THREE.Mesh(geom, mat);
                this.snapMarker.renderOrder = 999;
                scene.add(this.snapMarker);
            }

            // Color based on snap type
            const colors = {
                vertex: 0xff0000,    // Red for vertex
                edge: 0x00ff00,      // Green for edge
                midpoint: 0xffff00,  // Yellow for midpoint
                surface: 0x0088ff    // Blue for surface
            };

            this.snapMarker.material.color.setHex(colors[snapType] || 0xff0000);
            this.snapMarker.visible = true;
            this.snapMarker.position.copy(position);

            // Scale based on camera distance
            let scale;
            if (this.iges.camera.isOrthographicCamera) {
                const height = (this.iges.camera.top - this.iges.camera.bottom) / this.iges.camera.zoom;
                scale = height * 0.008;
            } else {
                scale = this.iges.camera.position.distanceTo(position) * 0.007;
            }
            this.snapMarker.scale.set(scale, scale, scale);
        },


        // Draw point-to-point or edge measurement
        _drawMeasurement(a, b, measureType = 'point') {
            const THREE = this.iges.THREE;
            const group = new THREE.Group();

            // Calculate measurement data
            const distance = a.distanceTo(b);
            const deltaX = Math.abs(b.x - a.x);
            const deltaY = Math.abs(b.y - a.y);
            const deltaZ = Math.abs(b.z - a.z);

            const uId = THREE.MathUtils.generateUUID();
            group.name = uId;

            // Store measurement result
            const measureResult = {
                id: uId,
                distance: distance,
                deltaX: deltaX,
                deltaY: deltaY,
                deltaZ: deltaZ,
                pointA: a.clone(),
                pointB: b.clone(),
                type: measureType,
                objectUuid: uId
            };

            this.iges.measure.results.push(measureResult);

            // 1. Draw main measurement line (White)
            const mainLineGeom = new THREE.BufferGeometry().setFromPoints([a, b]);
            const mainLine = new THREE.Line(mainLineGeom, new THREE.LineBasicMaterial({
                color: 0xffffff,
                linewidth: 2,
                depthTest: false
            }));
            mainLine.renderOrder = 999;
            group.add(mainLine);

            // 2. Draw Delta Lines (X=Red, Y=Green, Z=Blue)
            if (deltaX > 0.01) {
                const xLineGeom = new THREE.BufferGeometry().setFromPoints([a, new THREE.Vector3(b.x, a.y, a.z)]);
                const xLine = new THREE.Line(xLineGeom, new THREE.LineDashedMaterial({
                    color: 0xff4444,
                    dashSize: 3,
                    gapSize: 2,
                    depthTest: false
                }));
                xLine.computeLineDistances();
                xLine.renderOrder = 998;
                group.add(xLine);
            }

            if (deltaY > 0.01) {
                const yStart = new THREE.Vector3(b.x, a.y, a.z);
                const yEnd = new THREE.Vector3(b.x, b.y, a.z);
                const yLineGeom = new THREE.BufferGeometry().setFromPoints([yStart, yEnd]);
                const yLine = new THREE.Line(yLineGeom, new THREE.LineDashedMaterial({
                    color: 0x44ff44,
                    dashSize: 3,
                    gapSize: 2,
                    depthTest: false
                }));
                yLine.computeLineDistances();
                yLine.renderOrder = 998;
                group.add(yLine);
            }

            if (deltaZ > 0.01) {
                const zStart = new THREE.Vector3(b.x, b.y, a.z);
                const zEnd = b;
                const zLineGeom = new THREE.BufferGeometry().setFromPoints([zStart, zEnd]);
                const zLine = new THREE.Line(zLineGeom, new THREE.LineDashedMaterial({
                    color: 0x4444ff,
                    dashSize: 3,
                    gapSize: 2,
                    depthTest: false
                }));
                zLine.computeLineDistances();
                zLine.renderOrder = 998;
                group.add(zLine);
            }

            // 3. Draw endpoint spheres (Red)
            const sphereSize = Math.max(0.5, distance / 100);
            const sphereGeom = new THREE.SphereGeometry(sphereSize, 16, 16);
            const sphereMat = new THREE.MeshBasicMaterial({
                color: 0xff0000,
                depthTest: false
            });

            const sphere1 = new THREE.Mesh(sphereGeom, sphereMat);
            sphere1.position.copy(a);
            sphere1.renderOrder = 1000;
            group.add(sphere1);

            const sphere2 = new THREE.Mesh(sphereGeom, sphereMat);
            sphere2.position.copy(b);
            sphere2.renderOrder = 1000;
            group.add(sphere2);

            // 4. Create HTML label
            const wrap = this.$refs.igesWrap;
            const lbl = document.createElement('div');
            lbl.className = 'measure-label-detailed';

            Object.assign(lbl.style, {
                position: 'absolute',
                left: '0',
                top: '0',
                padding: '6px 10px',
                background: 'rgba(0, 0, 0, 0.85)',
                color: '#fff',
                borderRadius: '6px',
                fontSize: '10px',
                fontFamily: 'monospace',
                pointerEvents: 'none',
                zIndex: '10',
                border: '1px solid rgba(255, 255, 255, 0.2)',
                backdropFilter: 'blur(4px)'
            });

            wrap.appendChild(lbl);

            // Update function for label
            const updateLabel = () => {
                if (!this.iges.camera) return;

                const mid = a.clone().add(b).multiplyScalar(0.5);
                mid.project(this.iges.camera);

                const w = wrap.clientWidth;
                const h = wrap.clientHeight;
                const x = (mid.x * 0.5 + 0.5) * w;
                const y = (-mid.y * 0.5 + 0.5) * h;

                lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;

                lbl.innerHTML = `
                    <div class="text-blue-400 font-bold mb-1"><i class="fa-solid fa-ruler mr-1"></i>${distance.toFixed(2)} mm</div>
                    <div class="grid grid-cols-3 gap-1 text-[9px] opacity-80">
                        <span class="text-red-400">ΔX: ${deltaX.toFixed(2)}</span>
                        <span class="text-green-400">ΔY: ${deltaY.toFixed(2)}</span>
                        <span class="text-blue-400">ΔZ: ${deltaZ.toFixed(2)}</span>
                    </div>
                `;
            };

            group.userData.update = updateLabel;
            group.userData.measureResult = measureResult;

            // Dispose function
            group.userData.dispose = () => {
                if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                group.traverse(child => {
                    if (child.geometry) child.geometry.dispose();
                    if (child.material) child.material.dispose();
                });
            };

            group.uuid = uId;
            updateLabel();
            this.iges.measure.group.add(group);
        },

        // Draw angle measurement between three points
        _drawAngleMeasurement(p1, vertex, p3) {
            const THREE = this.iges.THREE;
            const group = new THREE.Group();

            // Calculate vectors from vertex to other points
            const v1 = p1.clone().sub(vertex).normalize();
            const v2 = p3.clone().sub(vertex).normalize();

            // Calculate angle in degrees
            const dotProduct = v1.dot(v2);
            const angleRad = Math.acos(Math.max(-1, Math.min(1, dotProduct)));
            const angleDeg = angleRad * (180 / Math.PI);

            // Calculate distances
            const dist1 = vertex.distanceTo(p1);
            const dist2 = vertex.distanceTo(p3);
            const dist3 = p1.distanceTo(p3);

            const uId = THREE.MathUtils.generateUUID();
            group.name = uId;

            // Store measurement result
            const measureResult = {
                id: uId,
                angle: angleDeg,
                distance: dist3,
                deltaX: Math.abs(p3.x - p1.x),
                deltaY: Math.abs(p3.y - p1.y),
                deltaZ: Math.abs(p3.z - p1.z),
                vertex: vertex.clone(),
                pointA: p1.clone(),
                pointB: p3.clone(),
                type: 'angle',
                objectUuid: uId
            };

            this.iges.measure.results.push(measureResult);

            // Draw lines from vertex to both points
            const arcRadius = Math.min(dist1, dist2) * 0.3;

            // Line 1 (vertex to p1)
            const line1Geom = new THREE.BufferGeometry().setFromPoints([vertex, p1]);
            const line1 = new THREE.Line(line1Geom, new THREE.LineBasicMaterial({
                color: 0xffff00,
                depthTest: false
            }));
            line1.renderOrder = 999;
            group.add(line1);

            // Line 2 (vertex to p3)
            const line2Geom = new THREE.BufferGeometry().setFromPoints([vertex, p3]);
            const line2 = new THREE.Line(line2Geom, new THREE.LineBasicMaterial({
                color: 0xffff00,
                depthTest: false
            }));
            line2.renderOrder = 999;
            group.add(line2);

            // Draw arc between the two lines
            const arcPoints = [];
            const arcSegments = 32;
            for (let i = 0; i <= arcSegments; i++) {
                const t = i / arcSegments;
                const currentAngle = t * angleRad;

                // Interpolate direction
                const dir = v1.clone().applyAxisAngle(
                    v1.clone().cross(v2).normalize(),
                    currentAngle
                );
                arcPoints.push(vertex.clone().add(dir.multiplyScalar(arcRadius)));
            }
            const arcGeom = new THREE.BufferGeometry().setFromPoints(arcPoints);
            const arcLine = new THREE.Line(arcGeom, new THREE.LineBasicMaterial({
                color: 0xff00ff,
                depthTest: false
            }));
            arcLine.renderOrder = 999;
            group.add(arcLine);

            // Draw spheres at all three points
            const sphereSize = arcRadius * 0.15;
            const sphereGeom = new THREE.SphereGeometry(sphereSize, 16, 16);

            const sphere1 = new THREE.Mesh(sphereGeom, new THREE.MeshBasicMaterial({ color: 0xff0000, depthTest: false }));
            sphere1.position.copy(p1);
            sphere1.renderOrder = 1000;
            group.add(sphere1);

            const sphereVertex = new THREE.Mesh(sphereGeom, new THREE.MeshBasicMaterial({ color: 0xffff00, depthTest: false }));
            sphereVertex.position.copy(vertex);
            sphereVertex.renderOrder = 1000;
            group.add(sphereVertex);

            const sphere3 = new THREE.Mesh(sphereGeom, new THREE.MeshBasicMaterial({ color: 0xff0000, depthTest: false }));
            sphere3.position.copy(p3);
            sphere3.renderOrder = 1000;
            group.add(sphere3);

            // Create HTML label for angle
            const wrap = this.$refs.igesWrap;
            const lbl = document.createElement('div');
            lbl.className = 'measure-angle-label';

            Object.assign(lbl.style, {
                position: 'absolute',
                left: '0',
                top: '0',
                padding: '6px 10px',
                background: 'rgba(0, 0, 0, 0.85)',
                color: '#fff',
                borderRadius: '6px',
                fontSize: '10px',
                fontFamily: 'monospace',
                pointerEvents: 'none',
                zIndex: '50',
                border: '1px solid rgba(255, 0, 255, 0.5)',
                backdropFilter: 'blur(4px)'
            });

            wrap.appendChild(lbl);

            const updateLabel = () => {
                if (!this.iges.camera) return;

                const labelPos = vertex.clone().add(v1.clone().add(v2).normalize().multiplyScalar(arcRadius * 1.5));
                labelPos.project(this.iges.camera);

                const w = wrap.clientWidth;
                const h = wrap.clientHeight;
                const x = (labelPos.x * 0.5 + 0.5) * w;
                const y = (-labelPos.y * 0.5 + 0.5) * h;

                lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;

                lbl.innerHTML = `
                    <div class="text-purple-400 font-bold mb-1"><i class="fa-solid fa-angle-left mr-1"></i>Angle: ${angleDeg.toFixed(2)}°</div>
                    <div class="text-blue-300 mb-1">Dist: ${dist3.toFixed(2)} mm</div>
                    <div class="grid grid-cols-3 gap-1 text-[9px] opacity-80">
                        <span class="text-red-400">ΔX: ${measureResult.deltaX.toFixed(2)}</span>
                        <span class="text-green-400">ΔY: ${measureResult.deltaY.toFixed(2)}</span>
                        <span class="text-blue-400">ΔZ: ${measureResult.deltaZ.toFixed(2)}</span>
                    </div>
                `;
            };

            group.userData.update = updateLabel;
            group.userData.measureResult = measureResult;

            group.userData.dispose = () => {
                if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                group.traverse(child => {
                    if (child.geometry) child.geometry.dispose();
                    if (child.material) child.material.dispose();
                });
            };

            group.uuid = uId;
            updateLabel();
            this.iges.measure.group.add(group);
        },

        // Draw radius measurement
        _drawRadiusMeasurement(circle, p1, p2, p3) {
            const { center, radius, normal } = circle;
            const THREE = this.iges.THREE;
            const group = new THREE.Group();
            const uId = THREE.MathUtils.generateUUID();
            group.name = uId;

            const curve = new THREE.EllipseCurve(
                0, 0,            // ax, aY
                radius, radius,  // xRadius, yRadius
                0, 2 * Math.PI,  // aStartAngle, aEndAngle
                false,           // aClockwise
                0                // aRotation
            );

            const points = curve.getPoints(64);
            const geometry = new THREE.BufferGeometry().setFromPoints(points);
            const material = new THREE.LineBasicMaterial({ color: 0x00ff00, depthTest: false });
            const circleMesh = new THREE.Line(geometry, material);

            const defaultNormal = new THREE.Vector3(0, 0, 1);
            const quaternion = new THREE.Quaternion().setFromUnitVectors(defaultNormal, normal);
            circleMesh.setRotationFromQuaternion(quaternion);
            circleMesh.position.copy(center);
            group.add(circleMesh);

            // Draw Center Point
            const centerGeom = new THREE.SphereGeometry(radius * 0.05, 16, 16);
            const centerMesh = new THREE.Mesh(centerGeom, new THREE.MeshBasicMaterial({ color: 0x00ff00, depthTest: false }));
            centerMesh.position.copy(center);
            group.add(centerMesh);

            // Draw lines to the 3 points
            const linesGeom = new THREE.BufferGeometry().setFromPoints([p1, center, p2, center, p3]);
            const lines = new THREE.LineSegments(linesGeom, new THREE.LineDashedMaterial({ color: 0x00ff00, dashSize: 0.5, gapSize: 0.5 }));
            lines.computeLineDistances();
            group.add(lines);

            // HTML Label
            const wrap = this.$refs.igesWrap;
            const lbl = document.createElement('div');
            lbl.className = 'measure-label-detailed';

            Object.assign(lbl.style, {
                position: 'absolute',
                left: '0',
                top: '0',
                padding: '6px 10px',
                background: 'rgba(0, 0, 0, 0.85)',
                color: '#fff',
                borderRadius: '6px',
                fontSize: '10px',
                fontFamily: 'monospace',
                pointerEvents: 'none',
                zIndex: '10',
                border: '1px solid rgba(0, 255, 0, 0.5)',
                backdropFilter: 'blur(4px)'
            });
            wrap.appendChild(lbl);

            const diameter = radius * 2;

            // Store result
            const measureResult = {
                id: uId,
                type: 'radius',
                radius: radius,
                diameter: diameter,
                center: center,
                objectUuid: uId
            };
            this.iges.measure.results.push(measureResult);

            const updateLabel = () => {
                if (!this.iges.camera) return;
                const pos = center.clone();
                pos.project(this.iges.camera);

                const x = (pos.x * 0.5 + 0.5) * wrap.clientWidth;
                const y = (-pos.y * 0.5 + 0.5) * wrap.clientHeight;

                lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
                lbl.innerHTML = `
                    <div class="text-green-400 font-bold mb-1"><i class="fa-regular fa-circle mr-1"></i>Radius: ${radius.toFixed(2)} mm</div>
                    <div class="text-teal-400">Ø Diameter: ${diameter.toFixed(2)} mm</div>
                `;
            };

            group.userData.update = updateLabel;
            group.userData.dispose = () => {
                if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                geometry.dispose();
                material.dispose();
                centerGeom.dispose();
                linesGeom.dispose();
            };

            group.uuid = uId;
            updateLabel();
            this.iges.measure.group.add(group);
        },

        // Draw face area measurement
        _drawFaceAreaMeasurement(centerPoint, area, normal, targetMesh) {
            const THREE = this.iges.THREE;
            const uId = THREE.MathUtils.generateUUID();

            // Save original material if not already saved
            if (targetMesh && targetMesh.isMesh) {
                if (!this._oriMats.has(targetMesh)) {
                    const m = targetMesh.material;
                    this._oriMats.set(targetMesh, Array.isArray(m) ? m.map(mm => mm.clone()) : m.clone());
                }

                // Create highlight material (Red)
                const highlightMat = new THREE.MeshBasicMaterial({
                    color: 0xff0000,
                    opacity: 0.6,
                    transparent: true,
                    depthTest: false,
                    side: THREE.DoubleSide
                });

                targetMesh.material = highlightMat;
            }

            // Create label at center point
            const wrap = this.$refs.igesWrap;
            const lbl = document.createElement('div');
            lbl.className = 'measure-label-detailed';
            Object.assign(lbl.style, {
                position: 'absolute',
                left: '0',
                top: '0',
                padding: '6px 10px',
                background: 'rgba(0, 0, 0, 0.85)',
                color: '#fff',
                borderRadius: '6px',
                fontSize: '10px',
                fontFamily: 'monospace',
                pointerEvents: 'none',
                zIndex: '10',
                border: '1px solid rgba(255, 0, 0, 0.5)',
                backdropFilter: 'blur(4px)'
            });
            wrap.appendChild(lbl);

            // Store result
            const measureResult = {
                id: uId,
                type: 'face',
                area: area,
                meshUuid: targetMesh ? targetMesh.uuid : null,
                objectUuid: uId
            };
            this.iges.measure.results.push(measureResult);

            // Update & Dispose
            const updateLabel = () => {
                if (!this.iges.camera) return;
                const pos = centerPoint.clone();
                pos.project(this.iges.camera);

                const x = (pos.x * 0.5 + 0.5) * wrap.clientWidth;
                const y = (-pos.y * 0.5 + 0.5) * wrap.clientHeight;

                lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
                lbl.innerHTML = `<i class="fa-solid fa-vector-square text-red-500 mr-1"></i> Area: ${area.toFixed(2)} mm²`;
            };

            // Create a dummy group to hold the label update function
            const group = new THREE.Group();
            group.uuid = uId;
            group.userData.meshUuid = targetMesh ? targetMesh.uuid : null;
            group.userData.update = updateLabel;
            group.userData.dispose = () => {
                if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
                // Restore original material
                if (group.userData.meshUuid) {
                    const mesh = this.iges.rootModel.getObjectByProperty('uuid', group.userData.meshUuid);
                    if (mesh && this._oriMats.has(mesh)) {
                        const originalMat = this._oriMats.get(mesh);
                        mesh.material = Array.isArray(originalMat)
                            ? originalMat.map(m => m.clone())
                            : originalMat.clone();
                    }
                }
            };

            updateLabel();
            this.iges.measure.group.add(group);
        },

        // Calculate circle from 3 points
        _calculateCircleFrom3Points(p1, p2, p3) {
            const THREE = this.iges.THREE;

            // Midpoints
            const m1 = p1.clone().add(p2).multiplyScalar(0.5);
            const m2 = p2.clone().add(p3).multiplyScalar(0.5);

            // Vectors for chords
            const v12 = p2.clone().sub(p1);
            const v23 = p3.clone().sub(p2);

            // Normal of the plane defined by 3 points
            const normal = v12.clone().cross(v23).normalize();

            // Directions of bisectors (perpendicular to chords and lying on the plane)
            const dir1 = v12.clone().cross(normal).normalize();
            const dir2 = v23.clone().cross(normal).normalize();

            // Solve intersection of perpendicular bisectors
            const vMatch = m2.clone().sub(m1);
            const cross12 = dir1.clone().cross(dir2);
            const denom = cross12.lengthSq();

            if (denom < 1e-6) return null; // Parallel or colinear

            const t = vMatch.clone().cross(dir2).dot(cross12) / denom;
            const center = m1.clone().add(dir1.multiplyScalar(t));
            const radius = center.distanceTo(p1);

            return { center, radius, normal };
        },

        // Calculate face area using flood-fill algorithm
        _calculateFaceArea(mesh, faceIndex) {
            const THREE = this.iges.THREE;
            if (!mesh || !mesh.geometry) return 0;

            const geom = mesh.geometry;
            const pos = geom.attributes.position;
            const index = geom.index;

            // Get normal of picked face
            const iA = index.getX(faceIndex * 3);
            const iB = index.getX(faceIndex * 3 + 1);
            const iC = index.getX(faceIndex * 3 + 2);

            const vA = new THREE.Vector3().fromBufferAttribute(pos, iA).applyMatrix4(mesh.matrixWorld);
            const vB = new THREE.Vector3().fromBufferAttribute(pos, iB).applyMatrix4(mesh.matrixWorld);
            const vC = new THREE.Vector3().fromBufferAttribute(pos, iC).applyMatrix4(mesh.matrixWorld);

            const triNormal = new THREE.Vector3().crossVectors(vB.clone().sub(vA), vC.clone().sub(vA)).normalize();

            let totalArea = 0;
            const threshold = 0.95; // Cosine similarity

            const p1 = new THREE.Vector3();
            const p2 = new THREE.Vector3();
            const p3 = new THREE.Vector3();

            // Pre-calculate target normal in Local Space
            const invMat = mesh.matrixWorld.clone().invert();
            const localTriNormal = triNormal.clone().transformDirection(invMat).normalize();

            const start = geom.drawRange.start || 0;
            const drawCount = (geom.drawRange.count !== Infinity && geom.drawRange.count !== undefined)
                ? geom.drawRange.count
                : index.count;
            const end = start + drawCount;

            // Iterate all triangles and sum areas of those with similar normals
            for (let i = start; i < end; i += 3) {
                const idx1 = index.getX(i);
                const idx2 = index.getX(i + 1);
                const idx3 = index.getX(i + 2);

                p1.fromBufferAttribute(pos, idx1);
                p2.fromBufferAttribute(pos, idx2);
                p3.fromBufferAttribute(pos, idx3);

                const fn = new THREE.Vector3().crossVectors(p2.clone().sub(p1), p3.clone().sub(p1)).normalize();

                if (fn.dot(localTriNormal) > threshold) {
                    // Transform points to world for accurate area
                    const wp1 = p1.clone().applyMatrix4(mesh.matrixWorld);
                    const wp2 = p2.clone().applyMatrix4(mesh.matrixWorld);
                    const wp3 = p3.clone().applyMatrix4(mesh.matrixWorld);

                    const edge1 = wp2.clone().sub(wp1);
                    const edge2 = wp3.clone().sub(wp1);
                    const area = edge1.cross(edge2).length() * 0.5;
                    totalArea += area;
                }
            }

            return totalArea;
        },

        // ===== SECTION CUTS / CLIPPING PLANES =====
        toggleClipping() {
            this.iges.clipping.enabled = !this.iges.clipping.enabled;

            if (this.iges.clipping.enabled) {
                // Calculate model bounds if not exists
                if (!this.iges.clipping.modelBounds && this.iges.rootModel) {
                    const box = new this.iges.THREE.Box3().setFromObject(this.iges.rootModel);
                    this.iges.clipping.modelBounds = box;
                }

                // Create clipping plane if not exists
                if (!this.iges.clipping.plane) {
                    const normal = new this.iges.THREE.Vector3(
                        this.iges.clipping.normal.x,
                        this.iges.clipping.normal.y,
                        this.iges.clipping.normal.z
                    );
                    this.iges.clipping.plane = new this.iges.THREE.Plane(normal, 0);
                }

                // Enable clipping in renderer
                this.iges.renderer.clippingPlanes = [this.iges.clipping.plane];
                this.iges.renderer.localClippingEnabled = true;

                // Update all materials
                this._updateMaterialsForClipping(true);
            } else {
                // Disable clipping
                this.iges.renderer.clippingPlanes = [];
                this.iges.renderer.localClippingEnabled = false;
                this._updateMaterialsForClipping(false);
            }
        },

        setClippingAxis(axis) {
            this.iges.clipping.axis = axis;

            // Update normal based on axis
            switch (axis) {
                case 'x':
                    this.iges.clipping.normal = { x: 1, y: 0, z: 0 };
                    break;
                case 'y':
                    this.iges.clipping.normal = { x: 0, y: 1, z: 0 };
                    break;
                case 'z':
                    this.iges.clipping.normal = { x: 0, y: 0, z: 1 };
                    break;
            }

            // Update plane normal
            if (this.iges.clipping.plane) {
                this.iges.clipping.plane.normal.set(
                    this.iges.clipping.normal.x,
                    this.iges.clipping.normal.y,
                    this.iges.clipping.normal.z
                );
            }

            // Recalculate distance based on new axis
            this.updateClippingDistance(this.iges.clipping.distance);
        },

        updateClippingDistance(distance) {
            this.iges.clipping.distance = distance;

            if (!this.iges.clipping.plane) return;

            // Update plane constant (distance from origin along normal)
            this.iges.clipping.plane.constant = -distance;
        },

        _updateMaterialsForClipping() {
            if (!this.iges.rootModel) return;
            const planes = this.iges.renderer.clippingPlanes;
            const enabled = planes && planes.length > 0;

            this.iges.rootModel.traverse((child) => {
                if (child.isMesh && child.material) {
                    const materials = Array.isArray(child.material) ? child.material : [child.material];

                    materials.forEach(mat => {
                        mat.clippingPlanes = planes;
                        mat.clipShadows = enabled;
                        mat.needsUpdate = true;
                    });
                }
            });
        },

        // ===== EXPLODED VIEW =====
        toggleExplodedView() {
            this.iges.exploded.enabled = !this.iges.exploded.enabled;

            if (this.iges.exploded.enabled) {
                // Calculate model center if not exists
                if (!this.iges.exploded.center && this.iges.rootModel) {
                    const box = new this.iges.THREE.Box3().setFromObject(this.iges.rootModel);
                    this.iges.exploded.center = box.getCenter(new this.iges.THREE.Vector3());
                }

                // Store original positions
                this._storeOriginalPositions();

                // Start with factor 0, user can adjust slider
                this.iges.exploded.factor = 0;
            } else {
                // Restore original positions
                this._restoreOriginalPositions();
                this.iges.exploded.factor = 0;
            }
        },

        updateExplodeFactor(factor) {
            if (!this.iges.exploded.enabled || !this.iges.exploded.center) return;

            this.iges.exploded.factor = Math.max(0, Math.min(1, factor));

            // Update all mesh positions
            this.iges.rootModel.traverse((child) => {
                if (child.isMesh && this.iges.exploded.originalPositions.has(child.uuid)) {
                    const originalPos = this.iges.exploded.originalPositions.get(child.uuid);
                    const newPos = this._calculateExplodedPosition(
                        originalPos,
                        this.iges.exploded.center,
                        this.iges.exploded.factor
                    );
                    child.position.copy(newPos);
                }
            });
        },

        _calculateExplodedPosition(originalPos, center, factor) {
            // Calculate direction from center to mesh
            const direction = originalPos.clone().sub(center);

            // If mesh is at center, move it slightly in a default direction
            if (direction.length() < 0.001) {
                direction.set(1, 1, 1).normalize();
            }

            // Scale direction by explosion factor
            // Use exponential scaling for better visual effect
            const explosionDistance = direction.length() * factor * 3; // 3x distance at full explosion
            const explodedPos = center.clone().add(
                direction.normalize().multiplyScalar(direction.length() + explosionDistance)
            );

            return explodedPos;
        },

        _storeOriginalPositions() {
            if (!this.iges.rootModel) return;

            this.iges.exploded.originalPositions.clear();

            this.iges.rootModel.traverse((child) => {
                if (child.isMesh) {
                    this.iges.exploded.originalPositions.set(
                        child.uuid,
                        child.position.clone()
                    );
                }
            });
        },

        _restoreOriginalPositions() {
            if (!this.iges.rootModel) return;

            this.iges.rootModel.traverse((child) => {
                if (child.isMesh && this.iges.exploded.originalPositions.has(child.uuid)) {
                    const originalPos = this.iges.exploded.originalPositions.get(child.uuid);
                    child.position.copy(originalPos);
                }
            });

            this.iges.exploded.originalPositions.clear();
        },

        // ===== 3D CAMERA CONTROLS =====
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

            // Reset to default isometric view
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
                camera.updateProjectionMatrix();
            }

            controls.target.set(0, 0, 0);
            controls.update();
        },

        toggleFullscreen() {
            const el = this.$refs.refMainContainer;
            if (!el) return;

            if (!document.fullscreenElement) {
                el.requestFullscreen().then(() => {
                    this.isFullscreen = true;
                }).catch(err => {
                    console.error(`Error fullscreen: ${err.message}`);
                    // Fallback for Safari/older browsers
                    if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
                });
            } else {
                document.exitFullscreen().then(() => {
                    this.isFullscreen = false;
                });
            }
        },

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

                // Schedule next steps (Animation 1.5s + Pause 3.5s = 5s interval)
                this.iges.tourInterval = setInterval(nextTourStep, 4000);

                console.log('[FileViewer] Started View Tour Animation');
            } else {
                // STOP TOUR ANIMATION
                if (controls) controls.autoRotate = false;

                if (this.iges.tourInterval) {
                    clearInterval(this.iges.tourInterval);
                    this.iges.tourInterval = null;
                }

                console.log('[FileViewer] Stopped View Tour Animation');
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
            console.log('[FileViewer] Headlight:', this.headlight.enabled);
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

                console.log('[FileViewer] Screenshot saved');
            } catch (err) {
                console.error('[FileViewer] Screenshot failed:', err);
            }
        },

        _forceRender() {
            const { renderer, scene, camera } = this.iges;
            if (renderer && scene && camera) {
                renderer.render(scene, camera);
            }
        },

        toggleCameraMode() {
            if (!this.iges.camera) return;
            const THREE = this.iges.THREE;
            const oldCam = this.iges.camera;
            const w = this.$refs.igesWrap?.clientWidth || 800;
            const h = this.$refs.igesWrap?.clientHeight || 600;
            const aspect = w / h;

            let newCam;
            if (oldCam.isPerspectiveCamera) {
                // Switch to Orthographic
                const box = new THREE.Box3().setFromObject(this.iges.rootModel);
                const size = new THREE.Vector3();
                box.getSize(size);
                const maxDim = Math.max(size.x, size.y, size.z) || 100;
                const frustumSize = maxDim * 2;

                newCam = new THREE.OrthographicCamera(
                    frustumSize * aspect / -2,
                    frustumSize * aspect / 2,
                    frustumSize / 2,
                    frustumSize / -2,
                    0.1,
                    100000
                );
                this.cameraMode = 'orthographic';
            } else {
                // Switch to Perspective
                newCam = new THREE.PerspectiveCamera(50, aspect, 0.1, 100000);
                this.cameraMode = 'perspective';
            }

            // Copy position and rotation
            newCam.position.copy(oldCam.position);
            newCam.quaternion.copy(oldCam.quaternion);
            newCam.up.copy(oldCam.up);

            // Transfer lights (children) from old camera to new camera
            while (oldCam.children.length > 0) {
                newCam.add(oldCam.children[0]);
            }

            // Replace camera in scene
            if (this.iges.scene) {
                const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.scene) : this.iges.scene;
                rawScene.remove(oldCam);
                rawScene.add(newCam);
            }

            // Update camera reference
            this.iges.camera = newCam;

            // Update controls
            if (this.iges.controls) {
                this.iges.controls.object = newCam;
                this.iges.controls.update();
            }

            this._forceRender();
            console.log('[FileViewer] Camera mode:', this.cameraMode);
        },

        setStandardView(view, duration = 800) {
            const { camera, controls, rootModel, THREE } = this.iges;
            if (!rootModel || !camera || !controls) return;

            // Calculate model bounds for proper camera distance
            const box = new THREE.Box3().setFromObject(rootModel);
            const center = new THREE.Vector3();
            box.getCenter(center); // Should be (0,0,0) if already centered
            const size = new THREE.Vector3();
            box.getSize(size);
            const maxDim = Math.max(size.x, size.y, size.z);

            // Ideal camera distance (Fit Distance)
            const fitDist = maxDim * 1.5;

            // Determine new position based on view
            let newPos = new THREE.Vector3();

            switch (view) {
                case 'front':
                    newPos.set(0, 0, fitDist);
                    break;
                case 'back':
                    newPos.set(0, 0, -fitDist);
                    break;
                case 'top':
                    newPos.set(0, fitDist, -0.01); // Offset -Z to avoid singularity
                    break;
                case 'bottom':
                    newPos.set(0, -fitDist, 0.01); // Offset +Z to avoid singularity
                    break;
                case 'right':
                    newPos.set(fitDist, 0, 0);
                    break;
                case 'left':
                    newPos.set(-fitDist, 0, 0);
                    break;
                case 'iso':
                default:
                    // Isometric view
                    newPos.set(fitDist, fitDist, fitDist).normalize().multiplyScalar(fitDist);
                    break;
            }

            // Determine target up vector
            const newUp = new THREE.Vector3(0, 1, 0);
            if (view === 'top') {
                newUp.set(0, 0, -1);
            } else if (view === 'bottom') {
                newUp.set(0, 0, 1);
            }

            // Animate camera transition
            this._animateCamera(newPos, center, newUp, () => {
                // Callback after animation completes
                console.log('[FileViewer] View set to:', view);
            }, duration);
        },

        _animateCamera(targetPos, targetTarget, targetUp, onComplete, duration = 800) {
            let { camera, controls } = this.iges;
            if (typeof Alpine !== 'undefined' && Alpine.raw) {
                camera = Alpine.raw(camera);
                controls = Alpine.raw(controls);
            }

            // Use separate ID for transition
            if (this.iges.transitionAnimId) cancelAnimationFrame(this.iges.transitionAnimId);

            const startPos = camera.position.clone();
            const startTarget = controls.target.clone();
            const startUp = camera.up.clone();
            const startTime = performance.now();

            const animate = (time) => {
                let elapsed = time - startTime;
                let t = elapsed / duration;
                if (t > 1) t = 1;

                // Ease In Out Cubic
                const ease = t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;

                camera.position.lerpVectors(startPos, targetPos, ease);
                controls.target.lerpVectors(startTarget, targetTarget, ease);
                camera.up.lerpVectors(startUp, targetUp, ease).normalize();

                controls.update();

                if (t < 1) {
                    this.iges.transitionAnimId = requestAnimationFrame(animate);
                } else {
                    // Ensure final values
                    camera.position.copy(targetPos);
                    controls.target.copy(targetTarget);
                    camera.up.copy(targetUp);
                    controls.update();

                    this.iges.transitionAnimId = null;
                    if (onComplete) onComplete();
                }
            };
            this.iges.transitionAnimId = requestAnimationFrame(animate);
        },

        // ===== ZOOM & PAN =====
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

        // ===== STAMP FUNCTIONS =====
        // NOTE: This section continues in next part due to file size...
        // The complete implementation would include all stamp-related functions

        formatStampDate(dateString) {
            if (!dateString) return '';
            const d = new Date(dateString);
            if (isNaN(d.getTime())) return dateString;

            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const monthName = months[d.getMonth()];
            const day = d.getDate();
            const year = d.getFullYear();

            const j = day % 10, k = day % 100;
            let suffix = "ᵗʰ";
            if (j == 1 && k != 11) suffix = "ˢᵗ";
            else if (j == 2 && k != 12) suffix = "ⁿᵈ";
            else if (j == 3 && k != 13) suffix = "ʳᵈ";

            return `${monthName}.${day}${suffix} ${year}`;
        },

        stampCenterOriginal() {
            return 'SAI-DRAWING ORIGINAL';
        },

        stampCenterCopy() {
            return 'SAI-DRAWING CONTROLLED COPY';
        },

        stampCenterObsolete() {
            return 'SAI-DRAWING OBSOLETE';
        },

        getNormalFormat() {
            const list = this.stampFormat || [];
            if (Array.isArray(list) && list.length > 0) {
                return list[0];
            }
            return { prefix: 'Date Received', suffix: 'Date Uploaded' };
        },

        getObsoleteInfo() {
            return this.pkg?.stamp?.obsolete_info || {};
        },

        obsoleteName() {
            const s = this.pkg?.stamp || {};
            const info = s.obsolete_info || {};
            return info.name || '';
        },

        obsoleteDept() {
            const s = this.pkg?.stamp || {};
            const info = s.obsolete_info || {};
            return info.dept || '';
        },

        stampTopLine(which = 'original') {
            const s = this.pkg?.stamp || {};
            let date, fmt;

            if (which === 'obsolete') {
                const info = this.getObsoleteInfo();
                date = info.date_text || s.obsolete_date || s.upload_date || '';
                return date ? `Date : ${date}` : '';
            } else if (which === 'original') {
                fmt = this.getNormalFormat();
                date = s.receipt_date || s.upload_date || '';
                const label = fmt.prefix || 'Date Received';
                return date ? `${label} : ${this.formatStampDate(date)}` : '';
            } else if (which === 'copy') {
                const now = new Date();
                const dateStr = this.formatStampDate(now.toISOString().split('T')[0]);
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const timeStr = `${hours}:${minutes}:${seconds}`;
                const deptCode = this.userDeptCode || '--';
                return `SAI / ${deptCode} / ${dateStr} ${timeStr}`;
            }
            return '';
        },

        stampBottomLine(which = 'original') {
            const s = this.pkg?.stamp || {};
            let fmt;

            if (which === 'copy') {
                const userName = this.userName || '--';
                return `Downloaded By ${userName}`;
            } else if (which === 'obsolete') {
                const info = this.getObsoleteInfo();
                const name = info.name || '';
                const dept = info.dept || '';
                return name && dept ? `${name} / ${dept}` : (name || dept || '');
            } else {
                fmt = this.getNormalFormat();
                const date = s.upload_date || '';
                const label = fmt.suffix || 'DATE UPLOADED';
                return date ? `${label} : ${this.formatStampDate(date)}` : '';
            }
        },

        positionIntToKey(pos) {
            switch (Number(pos)) {
                case 0: return 'bottom-left';
                case 1: return 'bottom-center';
                case 2: return 'bottom-right';
                case 3: return 'top-left';
                case 4: return 'top-center';
                case 5: return 'top-right';
                default: return 'bottom-left';
            }
        },

        loadStampConfigFor(file) {
            const key = this.getFileKey(file);
            if (!key) {
                this.stampConfig = { ...this.stampDefaults };
                return;
            }

            if (!this.stampPerFile[key]) {
                this.stampPerFile[key] = {
                    original: this.positionIntToKey(file.ori_position ?? 0),
                    copy: this.positionIntToKey(file.copy_position ?? 1),
                    obsolete: this.positionIntToKey(file.obslt_position ?? 2),
                };
            }

            this.stampConfig = this.stampPerFile[key];
        },

        stampPositionClass(which = 'original') {
            const configVal = this.stampConfig && this.stampConfig[which];
            const pos = configVal || this.stampDefaults[which] || 'bottom-left';

            switch (pos) {
                case 'top-left': return 'top-4 left-4';
                case 'top-center': return 'top-4 left-1/2 -translate-x-1/2';
                case 'top-right': return 'top-4 right-4';
                case 'bottom-left': return 'bottom-4 left-4';
                case 'bottom-center': return 'bottom-4 left-1/2 -translate-x-1/2';
                case 'bottom-right': return 'bottom-4 right-4';
                default:
                    if (which === 'original') return 'bottom-4 left-4';
                    if (which === 'copy') return 'bottom-4 left-1/2 -translate-x-1/2';
                    if (which === 'obsolete') return 'bottom-4 right-4';
                    return 'bottom-4 left-4';
            }
        },

        stampOriginClass(which = 'original') {
            const configVal = this.stampConfig && this.stampConfig[which];
            const pos = configVal || this.stampDefaults[which] || 'bottom-left';

            switch (pos) {
                case 'top-left': return 'origin-top-left';
                case 'top-center': return 'origin-top';
                case 'top-right': return 'origin-top-right';
                case 'bottom-left': return 'origin-bottom-left';
                case 'bottom-center': return 'origin-bottom';
                case 'bottom-right': return 'origin-bottom-right';
                default:
                    if (which === 'original') return 'origin-bottom-left';
                    if (which === 'copy') return 'origin-bottom';
                    if (which === 'obsolete') return 'origin-bottom-right';
                    return 'origin-bottom-left';
            }
        },

        onStampChange() {
            const key = this.getFileKey(this.selectedFile);
            if (key) {
                this.stampPerFile[key] = { ...this.stampConfig };
            }
            console.log('[FileViewer] Stamp config changed:', this.stampConfig);
        },

        async applyStampToAll() {
            if (this.applyToAllProcessing) return;

            this.applyToAllProcessing = true;
            console.log('[FileViewer] Applying stamp config to all files');

            // Apply current config to all files
            const allFiles = [
                ...(this.pkg.files?.['2d'] || []),
                ...(this.pkg.files?.['3d'] || []),
                ...(this.pkg.files?.ecn || [])
            ];

            allFiles.forEach(file => {
                const key = this.getFileKey(file);
                if (key) {
                    this.stampPerFile[key] = { ...this.stampConfig };
                }
            });

            // Simulate API call delay
            await new Promise(resolve => setTimeout(resolve, 500));

            this.applyToAllProcessing = false;
            console.log('[FileViewer] Stamp config applied to all files');
        },

        getFileKey(file) {
            return (file?.id ?? file?.name ?? '').toString();
        },

        // ===== UTILITY FUNCTIONS =====
        formatBytes(bytes) {
            if (!bytes || bytes <= 0) return '-';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let i = 0;
            let value = bytes;
            while (value >= 1024 && i < units.length - 1) {
                value /= 1024;
                i++;
            }
            const fixed = value >= 10 || i === 0 ? value.toFixed(0) : value.toFixed(1);
            return `${fixed} ${units[i]}`;
        },

        metaLine() {
            if (!this.selectedFile) return '';
            const f = this.selectedFile;
            const size = this.formatBytes(f.size ?? f.filesize ?? 0);
            return f.name + ' • ' + size;
        },

        fileSizeInfo() {
            if (!this.selectedFile) return '';
            const bytes = this.selectedFile.size ?? this.selectedFile.filesize ?? 0;
            if (!bytes) return 'Size: -';
            return 'Size: ' + this.formatBytes(bytes);
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

        _updateClippingPlanes() {
            const { renderer, scene } = this.iges;
            if (!renderer || !scene) return;

            const planes = [];
            const { x, y, z } = this.iges.clipping;

            if (x.enabled && x.plane) planes.push(x.plane);
            if (y.enabled && y.plane) planes.push(y.plane);
            if (z.enabled && z.plane) planes.push(z.plane);

            renderer.clippingPlanes = planes;

            scene.traverse(child => {
                if (child.isMesh && child.material) {
                    if (Array.isArray(child.material)) {
                        child.material.forEach(m => {
                            m.clippingPlanes = planes;
                            m.clipShadows = true;
                            m.needsUpdate = true;
                        });
                    } else {
                        child.material.clippingPlanes = planes;
                        child.material.clipShadows = true;
                        child.material.needsUpdate = true;
                    }
                }
            });

            this._forceRender();
        },

        _updateMaterialsWithClipping() {
            this._updateClippingPlanes();
        },

        /* ===== ADVANCED 3D TOOLS (ADDED) ===== */
        get hasActiveClipping() {
            return (this.iges.clipping.x && this.iges.clipping.x.enabled) ||
                (this.iges.clipping.y && this.iges.clipping.y.enabled) ||
                (this.iges.clipping.z && this.iges.clipping.z.enabled);
        },

        toggleExplodedPanel() {
            console.log('[FileViewer] toggleExplodedPanel called');
            console.log('[FileViewer] this.iges:', this.iges);
            console.log('[FileViewer] this.iges.exploded:', this.iges.exploded);

            if (!this.iges || !this.iges.exploded) {
                console.error('[FileViewer] CRITICAL: iges.exploded is undefined!');
                // Force repair
                if (!this.iges.exploded) {
                    this.iges.exploded = {
                        enabled: false,
                        factor: 0,
                        center: null,
                        originalPositions: null,
                        animating: false,
                        panelOpen: false
                    };
                }
            }

            if (!this.iges.exploded.enabled) {
                // Enable exploded view first
                this.iges.exploded.enabled = true;
                if (!this.iges.exploded.factor) this.iges.exploded.factor = 0.5;
                this.updateExplodeFactor(this.iges.exploded.factor);
            }
            // Toggle panel
            this.iges.exploded.panelOpen = !this.iges.exploded.panelOpen;
            console.log('[FileViewer] Panel open:', this.iges.exploded.panelOpen);
        },

        toggleExplodedView() {
            console.log('[FileViewer] toggleExplodedView called, current state:', this.iges.exploded);
            this.iges.exploded.enabled = !this.iges.exploded.enabled;
            if (this.iges.exploded.enabled) {
                if (!this.iges.exploded.factor) this.iges.exploded.factor = 0.5;
                this.updateExplodeFactor(this.iges.exploded.factor);
                this.iges.exploded.panelOpen = true;
                console.log('[FileViewer] Exploded view enabled, panel should open');
            } else {
                this.updateExplodeFactor(0);
                this.iges.exploded.panelOpen = false;
                console.log('[FileViewer] Exploded view disabled');
            }
            this._forceRender();
        },

        updateExplodeFactor(val) {
            const factor = parseFloat(val);
            this.iges.exploded.factor = factor;
            const { rootModel, THREE } = this.iges;
            if (!rootModel || !THREE) return;

            if (!this.iges.exploded.originalPositions) {
                this.iges.exploded.originalPositions = new Map();

                // Calculate global bounding box center as reference point
                const globalBox = new THREE.Box3().setFromObject(rootModel);
                const globalCenter = new THREE.Vector3();
                globalBox.getCenter(globalCenter);

                console.log('[FileViewer] Explode - Global center:', globalCenter);
                let meshCount = 0;

                rootModel.traverse(child => {
                    if (child.isMesh) {
                        meshCount++;
                        // Store original position
                        this.iges.exploded.originalPositions.set(child.uuid, child.position.clone());

                        // Calculate individual mesh center in world space
                        if (!child.geometry.boundingBox) child.geometry.computeBoundingBox();
                        const meshBox = child.geometry.boundingBox.clone();
                        const meshCenter = new THREE.Vector3();
                        meshBox.getCenter(meshCenter);

                        // Transform to world space
                        const worldMatrix = child.matrixWorld.clone();
                        meshCenter.applyMatrix4(worldMatrix);

                        // Calculate direction from global center to this mesh's center
                        const dir = new THREE.Vector3().subVectors(meshCenter, globalCenter);

                        // If direction is too small (mesh at center), use a default direction
                        if (dir.length() < 0.01) {
                            dir.set(Math.random() - 0.5, Math.random() - 0.5, Math.random() - 0.5);
                        }

                        dir.normalize();
                        child.userData.explodeDir = dir;

                        console.log(`[FileViewer] Mesh ${meshCount} - Center:`, meshCenter, 'Dir:', dir);
                    }
                });

                console.log(`[FileViewer] Explode initialized for ${meshCount} meshes`);
            }

            const scalar = factor * 500;

            rootModel.traverse(child => {
                if (child.isMesh && child.userData.explodeDir) {
                    const orig = this.iges.exploded.originalPositions.get(child.uuid);
                    if (orig) {
                        // Cloning direction is important
                        const offset = child.userData.explodeDir.clone().multiplyScalar(scalar);
                        child.position.copy(orig).add(offset);
                    }
                }
            });

            this._forceRender();
        },

        toggleClippingPanel() {
            console.log('[FileViewer] toggleClippingPanel called');
            console.log('[FileViewer] this.iges:', this.iges);
            console.log('[FileViewer] this.iges.clipping:', this.iges.clipping);

            if (!this.iges || !this.iges.clipping) {
                console.error('[FileViewer] CRITICAL: iges.clipping is undefined!');
                // Force repair
                if (!this.iges.clipping) {
                    this.iges.clipping = {
                        panelOpen: false,
                        min: -100, max: 100, step: 1,
                        x: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                        y: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false },
                        z: { enabled: false, value: 0, min: -100, max: 100, plane: null, showHelper: false, helper: null, flipped: false, showCap: false }
                    };
                }
            }

            this.iges.clipping.panelOpen = !this.iges.clipping.panelOpen;
            console.log('[FileViewer] Clipping panel open:', this.iges.clipping.panelOpen);
        },

        // CLIPPING
        toggleAxisClipping(axis) {
            const axisData = this.iges.clipping[axis];
            if (!axisData) return;
            axisData.enabled = !axisData.enabled;
            const { THREE } = this.iges;

            if (axisData.enabled) {
                // Reset value to 0 to match reference behavior
                axisData.value = 0;

                // Auto-enable helper when activating cut
                axisData.showHelper = true;

                // Create plane with correct normal vector
                const normals = {
                    x: new THREE.Vector3(axisData.flipped ? -1 : 1, 0, 0),
                    y: new THREE.Vector3(0, axisData.flipped ? -1 : 1, 0),
                    z: new THREE.Vector3(0, 0, axisData.flipped ? -1 : 1)
                };

                axisData.plane = new THREE.Plane(normals[axis], 0);

                // Create helper immediately
                this._createPlaneHelper(axis);
            } else {
                axisData.plane = null;
                // Remove plane helper when axis is disabled
                if (axisData.helper) {
                    const scene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.scene) : this.iges.scene;
                    const helper = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(axisData.helper) : axisData.helper;
                    scene.remove(helper);
                    if (helper.geometry) helper.geometry.dispose();
                    if (helper.material) helper.material.dispose();
                    axisData.helper = null;
                }
            }

            this._updateClippingPlanes();

            // Match reference: Enable polygon offset on model meshes when clipping is active to prevent Z-fighting
            const root = this.iges.rootModel;
            if (root) {
                if (this.hasActiveClipping) {
                    this._setPolygonOffset(root, true, 1, 1);
                } else if (this.currentStyle !== 'shaded-edges') {
                    // Only disable if not in shaded-edges mode (which needs it anyway)
                    this._setPolygonOffset(root, false);
                }
            }
        },

        updateAxisClipping(axis) {
            const conf = this.iges.clipping[axis];
            if (!conf || !conf.plane) return;

            const THREE = this.iges.THREE;
            // Normal direction is handled by flipAxis
            // Match reference logic for normals: flipped ? -1 : 1
            const normals = {
                x: new THREE.Vector3(conf.flipped ? -1 : 1, 0, 0),
                y: new THREE.Vector3(0, conf.flipped ? -1 : 1, 0),
                z: new THREE.Vector3(0, 0, conf.flipped ? -1 : 1)
            };

            conf.plane.normal.copy(normals[axis]);

            // Only update constant value for slider movement
            // Match reference logic for constant: flipped ? value : -value
            conf.plane.constant = conf.flipped ? conf.value : -conf.value;

            // Update PlaneHelper position
            this._updatePlaneHelper(axis);

            // Update Cap position
            if (conf.capMesh && conf.showCap) {
                const distance = -conf.plane.constant;
                conf.capMesh.position.copy(conf.plane.normal.clone().multiplyScalar(distance));
                conf.capMesh.lookAt(conf.capMesh.position.clone().add(conf.plane.normal));
            }

            this._updateClippingPlanes();
        },

        flipAxis(axis) {
            const axisData = this.iges.clipping[axis];
            axisData.flipped = !axisData.flipped;

            if (axisData.enabled && axisData.plane) {
                const { THREE } = this.iges;
                const normals = {
                    x: new THREE.Vector3(axisData.flipped ? -1 : 1, 0, 0),
                    y: new THREE.Vector3(0, axisData.flipped ? -1 : 1, 0),
                    z: new THREE.Vector3(0, 0, axisData.flipped ? -1 : 1)
                };

                axisData.plane.normal.copy(normals[axis]);
                // Reference Logic: constant relies on flipped state explicitly
                axisData.plane.constant = axisData.flipped ? axisData.value : -axisData.value;

                this._updateMaterialsWithClipping();

                if (axisData.helper && axisData.showHelper) {
                    this._createPlaneHelper(axis);
                }
            }
            this._forceRender();
        },

        setAxisValueDirect(axis, val) {
            const conf = this.iges.clipping[axis];
            conf.value = parseFloat(val);
            this.updateAxisClipping(axis);
        },

        incrementAxisValue(axis) {
            const conf = this.iges.clipping[axis];
            conf.value += this.iges.clipping.step;
            this.updateAxisClipping(axis);
        },

        decrementAxisValue(axis) {
            const conf = this.iges.clipping[axis];
            conf.value -= this.iges.clipping.step;
            this.updateAxisClipping(axis);
        },

        togglePlaneHelper(axis) {
            const axisData = this.iges.clipping[axis];
            axisData.showHelper = !axisData.showHelper;

            if (axisData.showHelper && !axisData.helper) {
                this._createPlaneHelper(axis);
            } else if (!axisData.showHelper && axisData.helper) {
                this.iges.scene.remove(axisData.helper);
                if (axisData.helper.geometry) axisData.helper.geometry.dispose();
                if (axisData.helper.material) axisData.helper.material.dispose();
                axisData.helper = null;
            }
            this._forceRender();
        },

        _createPlaneHelper(axis) {
            const { THREE, scene, rootModel } = this.iges;
            if (!THREE || !scene || !rootModel) return;

            const axisData = this.iges.clipping[axis];
            const rawScene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(scene) : scene;

            // Remove old helper if exists (using Alpine.raw to ensure clean removal)
            if (axisData.helper) {
                const helper = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(axisData.helper) : axisData.helper;
                rawScene.remove(helper);
                if (helper.geometry) helper.geometry.dispose();
                if (helper.material) helper.material.dispose();
                axisData.helper = null;
            }

            // Calculate model bounding box
            const box = new THREE.Box3().setFromObject(rootModel);
            const size = new THREE.Vector3();
            box.getSize(size);

            // Determine plane dimensions based on axis
            let width, height;
            if (axis === 'x') {
                // X-plane covers Y-Z plane
                width = size.z;
                height = size.y;
            } else if (axis === 'y') {
                // Y-plane covers X-Z plane
                width = size.x;
                height = size.z;
            } else {
                // Z-plane covers X-Y plane
                width = size.x;
                height = size.y;
            }

            // Add margins (10% buffer)
            width *= 1.1;
            height *= 1.1;

            // Color mapping - Match reference exactly
            const colors = {
                x: 0xff0000, // Pure Red
                y: 0x00ff00, // Pure Green
                z: 0x0000ff  // Pure Blue
            };

            // Create plane geometry - Match reference (10x10 segments)
            // Create plane geometry - Use 1x1 segment to prevent internal triangulation artifacts (Z-fighting holes)
            const planeGeometry = new THREE.PlaneGeometry(width, height, 1, 1);

            // Match reference exactly: MeshBasicMaterial with opacity 0.2
            const planeMaterial = new THREE.MeshBasicMaterial({
                color: colors[axis],
                side: THREE.DoubleSide,
                transparent: true,
                opacity: 0.2, // Reference uses 0.2
                wireframe: false,
                depthTest: true,
                depthWrite: false
            });

            const planeMesh = new THREE.Mesh(planeGeometry, planeMaterial);
            planeMesh.renderOrder = 999; // Reference uses 999
            planeMesh.raycast = THREE.Mesh.prototype.raycast;

            // Add wireframe edges
            const wireframeGeometry = new THREE.EdgesGeometry(planeGeometry);
            const wireframeMaterial = new THREE.LineBasicMaterial({
                color: colors[axis],
                opacity: 0.6,
                transparent: true
            });
            const wireframe = new THREE.LineSegments(wireframeGeometry, wireframeMaterial);
            planeMesh.add(wireframe);

            // Orient plane based on axis
            if (axis === 'x') {
                planeMesh.rotation.y = Math.PI / 2;
            } else if (axis === 'y') {
                planeMesh.rotation.x = Math.PI / 2;
            }
            // Z-axis needs no rotation (default orientation)

            // Position plane
            planeMesh.position.set(
                axis === 'x' ? axisData.value : 0,
                axis === 'y' ? axisData.value : 0,
                axis === 'z' ? axisData.value : 0
            );

            // Make plane interactive for dragging
            planeMesh.userData.axis = axis;
            planeMesh.userData.isDraggable = true;

            // Store reference and add to scene (unproxied add)
            axisData.helper = planeMesh;
            rawScene.add(planeMesh);

            this._setupPlaneHelperDrag(axis);

            console.log(`[FileViewer] PlaneHelper matched to reference - Axis: ${axis}`);
        },

        _setupPlaneHelperDrag(axis) {
            if (this._planeHelperDragInitialized) return;
            this._planeHelperDragInitialized = true;

            const { renderer, camera, controls } = this.iges;
            if (!renderer || !camera) return;

            const canvas = renderer.domElement;

            // Store drag state globally
            this._planeDragState = {
                isDragging: false,
                axis: null,
                dragPlane: null,
                offset: 0
            };

            // Mouse down handler
            const onMouseDown = (event) => {
                // Skip if measure tool is active
                if (this.iges.measure?.enabled) return;

                const rect = canvas.getBoundingClientRect();
                const mouse = new this.iges.THREE.Vector2(
                    ((event.clientX - rect.left) / rect.width) * 2 - 1,
                    -((event.clientY - rect.top) / rect.height) * 2 + 1
                );

                const raycaster = new this.iges.THREE.Raycaster();
                raycaster.setFromCamera(mouse, camera);

                // Increase threshold for better click detection on thin planes
                raycaster.params.Mesh.threshold = 0.1;

                // Check all active plane helpers
                for (const axisName of ['x', 'y', 'z']) {
                    const axisData = this.iges.clipping[axisName];
                    if (!axisData.helper || !axisData.showHelper || !axisData.enabled) continue;

                    // Unwrap for reliable intersection
                    const helper = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(axisData.helper) : axisData.helper;

                    // Intersect only the mesh, not children (wireframe)
                    const intersects = raycaster.intersectObject(helper, false);

                    if (intersects.length > 0) {
                        this._planeDragState.isDragging = true;
                        this._planeDragState.axis = axisName;

                        const intersectionPoint = intersects[0].point;
                        const normal = new this.iges.THREE.Vector3();
                        camera.getWorldDirection(normal);

                        const plane = new this.iges.THREE.Plane();
                        plane.setFromNormalAndCoplanarPoint(normal, intersectionPoint);
                        this._planeDragState.dragPlane = plane;

                        let clickValue;
                        if (axisName === 'x') clickValue = intersectionPoint.x;
                        else if (axisName === 'y') clickValue = intersectionPoint.y;
                        else clickValue = intersectionPoint.z;

                        this._planeDragState.offset = clickValue - axisData.value;

                        // Disable orbit controls while dragging
                        if (controls) controls.enabled = false;

                        // Change cursor
                        canvas.style.cursor = 'move';
                        event.preventDefault();
                        event.stopPropagation();
                        break;
                    }
                }
            };

            // Mouse move handler - drag plane
            const onMouseMove = (event) => {
                if (!this._planeDragState.isDragging) {
                    // Hover detection for cursor feedback
                    const rect = canvas.getBoundingClientRect();
                    const mouse = new this.iges.THREE.Vector2(
                        ((event.clientX - rect.left) / rect.width) * 2 - 1,
                        -((event.clientY - rect.top) / rect.height) * 2 + 1
                    );

                    const raycaster = new this.iges.THREE.Raycaster();
                    raycaster.setFromCamera(mouse, camera);
                    raycaster.params.Mesh.threshold = 0.1;

                    let isOverPlane = false;
                    for (const axisName of ['x', 'y', 'z']) {
                        const axisData = this.iges.clipping[axisName];
                        if (!axisData.helper || !axisData.showHelper || !axisData.enabled) continue;

                        const helper = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(axisData.helper) : axisData.helper;
                        const intersects = raycaster.intersectObject(helper, false);

                        if (intersects.length > 0) {
                            isOverPlane = true;
                            break;
                        }
                    }

                    canvas.style.cursor = isOverPlane ? 'pointer' : 'default';
                    return;
                }

                const axis = this._planeDragState.axis;
                const axisData = this.iges.clipping[axis];

                const rect = canvas.getBoundingClientRect();
                const mouse = new this.iges.THREE.Vector2(
                    ((event.clientX - rect.left) / rect.width) * 2 - 1,
                    -((event.clientY - rect.top) / rect.height) * 2 + 1
                );

                const raycaster = new this.iges.THREE.Raycaster();
                raycaster.setFromCamera(mouse, camera);

                // Intersect with the virtual drag plane
                const targetPoint = new this.iges.THREE.Vector3();
                raycaster.ray.intersectPlane(this._planeDragState.dragPlane, targetPoint);

                if (targetPoint) {
                    // Project the target point onto our axis to get the raw value
                    let rawValue;
                    if (axis === 'x') rawValue = targetPoint.x;
                    else if (axis === 'y') rawValue = targetPoint.y;
                    else rawValue = targetPoint.z;

                    // Apply the initial offset
                    let newValue = rawValue - this._planeDragState.offset;

                    // Clamp
                    const min = axisData.min !== undefined ? axisData.min : this.iges.clipping.min;
                    const max = axisData.max !== undefined ? axisData.max : this.iges.clipping.max;
                    newValue = Math.max(min, Math.min(max, newValue));

                    // Round to 2 decimal places
                    newValue = Math.round(newValue * 100) / 100;

                    // Update
                    axisData.value = newValue;
                    this.updateAxisClipping(axis);
                }
            };

            // Mouse up handler
            const onMouseUp = (event) => {
                if (this._planeDragState.isDragging) {
                    this._planeDragState.isDragging = false;
                    this._planeDragState.axis = null;
                    this._planeDragState.dragPlane = null;

                    // Re-enable orbit controls
                    if (controls) controls.enabled = true;

                    // Reset cursor
                    canvas.style.cursor = 'default';
                }
            };

            // Add event listeners once
            canvas.addEventListener('mousedown', onMouseDown, false);
            canvas.addEventListener('mousemove', onMouseMove, false);
            canvas.addEventListener('mouseup', onMouseUp, false);
            canvas.addEventListener('mouseleave', onMouseUp, false);

            console.log('[FileViewer] Plane helper drag interaction initialized');
        },
        _updatePlaneHelper(axis) {
            const axisData = this.iges.clipping[axis];
            if (!axisData.helper || !axisData.showHelper) return;

            // Update plane position
            axisData.helper.position.set(
                axis === 'x' ? axisData.value : 0,
                axis === 'y' ? axisData.value : 0,
                axis === 'z' ? axisData.value : 0
            );

            this._forceRender();
        },

        toggleSectionCap(axis) {
            const conf = this.iges.clipping[axis];
            if (!conf) return;

            // Log only (matches reference) to avoid z-fighting with helper
            console.log(`Section cap for ${axis}-axis toggled:`, conf.showCap);

            // Clean up any existing cap mesh to prevent glitches
            if (conf.capMesh) {
                this.iges.scene.remove(conf.capMesh);
                if (conf.capMesh.geometry) conf.capMesh.geometry.dispose();
                conf.capMesh = null;
            }

            this._forceRender();
        },

        resetAllClipping() {
            ['x', 'y', 'z'].forEach(axis => {
                const axisData = this.iges.clipping[axis];
                axisData.enabled = false;
                axisData.value = 0;
                axisData.flipped = false;
                axisData.plane = null;
                if (axisData.helper) {
                    const scene = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.scene) : this.iges.scene;
                    const helper = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(axisData.helper) : axisData.helper;
                    scene.remove(helper);
                    if (helper.geometry) helper.geometry.dispose();
                    if (helper.material) helper.material.dispose();
                    axisData.helper = null;
                }
            });
            this._updateClippingPlanes();
        },

        _updateClippingPlanes() {
            const planes = [];
            ['x', 'y', 'z'].forEach(ax => {
                if (this.iges.clipping[ax].enabled && this.iges.clipping[ax].plane) {
                    planes.push(this.iges.clipping[ax].plane);
                }
            });
            this.iges.renderer.clippingPlanes = planes;
            this.iges.renderer.localClippingEnabled = planes.length > 0;
            this._updateMaterialsWithClipping();
            this._forceRender();
        },

        // Set material mode (Clay, Metal, Glass, etc.)
        _updateMaterialsWithClipping() {
            const { rootModel, renderer } = this.iges;
            if (!rootModel || !renderer) return;
            const planes = renderer.clippingPlanes;

            rootModel.traverse(child => {
                if ((child.isMesh || child.isLineSegments) && child.material) {
                    const mats = Array.isArray(child.material) ? child.material : [child.material];
                    mats.forEach(m => {
                        m.clippingPlanes = planes;
                        m.clipShadows = true;
                        m.needsUpdate = true;
                    });
                }
            });
        },

        setMaterialMode(mode) {
            this.activeMaterial = mode;
            const { rootModel, THREE } = this.iges;
            if (!rootModel || !THREE) return;

            // 1. Reset to original materials first (to ensure a clean state)
            this._restoreMaterials(rootModel);

            // 2. If mode is 'default', we're done (already restored)
            if (mode === 'default') {
                this._updateMaterialsWithClipping();
                this._forceRender();
                return;
            }

            // 3. Prepare new Material based on Mode (align with reference)
            const commonProps = {
                side: THREE.DoubleSide,
                clippingPlanes: (this.iges.renderer.clippingPlanes) || [],
                clipShadows: true
            };

            let newMat;
            switch (mode) {
                case 'clay':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xdddddd,
                        roughness: 1.0,
                        metalness: 0.0
                    });
                    break;
                case 'metal':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xffffff,
                        roughness: 0.2,
                        metalness: 1.0
                    });
                    break;
                case 'glass':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xffffff,
                        metalness: 0.1,
                        roughness: 0.1,
                        transparent: true,
                        opacity: 0.3,
                        depthWrite: false
                    });
                    break;
                case 'ecoat':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0x222222,
                        roughness: 0.7,
                        metalness: 0.1
                    });
                    break;
                case 'raw-steel':
                case 'steel':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xc0c6c9,
                        roughness: 0.4,
                        metalness: 0.8
                    });
                    break;
                case 'aluminum':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xffffff,
                        roughness: 0.5,
                        metalness: 0.7
                    });
                    break;
                case 'yellow-zinc':
                case 'zinc':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0xd4af37,
                        roughness: 0.5,
                        metalness: 0.6
                    });
                    break;
                case 'red-oxide':
                case 'redox':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0x803020,
                        roughness: 0.9,
                        metalness: 0.0
                    });
                    break;
                case 'dark':
                    newMat = new THREE.MeshStandardMaterial({
                        ...commonProps,
                        color: 0x1a1a1a,
                        roughness: 0.6,
                        metalness: 0.2
                    });
                    break;
                case 'normal':
                    newMat = new THREE.MeshNormalMaterial({
                        ...commonProps
                    });
                    break;
                default:
                    this._restoreMaterials(rootModel);
                    return;
            }

            // 4. Apply to all meshes
            rootModel.traverse((child) => {
                if (child.isMesh) {
                    if (Array.isArray(child.material)) {
                        child.material = child.material.map(() => newMat.clone());
                    } else {
                        child.material = newMat.clone();
                    }
                }
            });

            // Maintain style consistency
            if (this.currentStyle === 'shaded-edges') {
                this._setPolygonOffset(rootModel, true, 1, 1);
                this._toggleEdges(rootModel, true, 0x000000);
            } else if (this.currentStyle === 'wireframe') {
                this._setWireframe(rootModel, true);
            }

            this._updateMaterialsWithClipping();
            this._forceRender();
        },

        toggleExplode() { this.toggleExplodedView(); },

        updateExplode(val) {
            const factor = parseFloat(val) / 100;
            this.updateExplodeFactor(factor);
        },

        setMeasureMode(mode) {
            this.iges.measure.mode = mode;
            if (!this.iges.measure.enabled) this.toggleMeasure();
        },

        clearMeasurements() {
            this.iges.measure.results = [];
            // Clear scene objects
            if (this.iges.measure.group) {
                const rawGroup = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.measure.group) : this.iges.measure.group;
                while (rawGroup.children.length > 0) {
                    const child = rawGroup.children[0];
                    if (child.userData?.dispose) child.userData.dispose();
                    rawGroup.remove(child);
                }
            }
            this._forceRender();
        },

        _forceRender() {
            if (this.iges.renderer && this.iges.scene && this.iges.camera) {
                const r = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.renderer) : this.iges.renderer;
                const s = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.scene) : this.iges.scene;
                const c = (typeof Alpine !== 'undefined' && Alpine.raw) ? Alpine.raw(this.iges.camera) : this.iges.camera;
                if (r && s && c) r.render(s, c);
            }
        },

        // ===== MASKING / BLOCKING FEATURE =====

        addMask() {
            const id = 'blk-' + Date.now();
            this.masks.push({
                id: id,
                x: 100,
                y: 100,
                width: 150,
                height: 80,
                rotation: 0,
                active: true,
                editable: true,
                visible: true
            });
            this.activeMask = this.masks[this.masks.length - 1];
        },

        activateMask(mask) {
            if (!this.enableMasking || !mask) return;
            this.masks.forEach(m => {
                if (m) m.active = (m.id === mask.id);
            });
            this.activeMask = mask;
        },

        deactivateMask() {
            this.masks.forEach(m => m.active = false);
            this.activeMask = null;
        },

        removeActiveMask() {
            if (this.activeMask) {
                this.masks = this.masks.filter(m => m.id !== this.activeMask.id);
                this.activeMask = null;
            }
        },

        getActiveMask() {
            return this.activeMask;
        },

        saveCurrentMask() {
            // Find current display element to get dimensions
            const img = this.$refs.mainImage || this.$refs.pdfCanvas || this.$refs.tifImg || this.$refs.hpglCanvas;
            let displayW = 0;
            let displayH = 0;

            if (img) {
                displayW = img.clientWidth || img.width || 0;
                displayH = img.clientHeight || img.height || 0;
            }

            const maskData = this.masks.map(m => {
                const data = {
                    id: String(m.id),
                    x: m.x,
                    y: m.y,
                    width: m.width,
                    height: m.height,
                    rotation: m.rotation,
                    // If we have display dimensions, calculate normalized u,v,w,h (0..1)
                    u: (displayW > 0) ? m.x / displayW : (m.u || 0),
                    v: (displayH > 0) ? m.y / displayH : (m.v || 0),
                    w: (displayW > 0) ? m.width / displayW : (m.w || 0),
                    h: (displayH > 0) ? m.height / displayH : (m.h || 0)
                };
                return data;
            });

            this.$dispatch('masks-updated', maskData);

            window.dispatchEvent(new CustomEvent('toast-show', {
                detail: { type: 'success', message: 'Masks ready to be saved' }
            }));

            return maskData;
        },

        maskStyle(mask) {
            if (!mask) return {};
            return {
                left: (mask.x || 0) + 'px',
                top: (mask.y || 0) + 'px',
                width: (mask.width || 0) + 'px',
                height: (mask.height || 0) + 'px',
                transform: `rotate(${mask.rotation || 0}deg)`,
                position: 'absolute'
            };
        },

        onMaskMouseDown(e, mask) {
            if (!mask || !mask.editable) return;
            this.activateMask(mask);

            const startX = e.clientX;
            const startY = e.clientY;
            const initialX = mask.x;
            const initialY = mask.y;
            const currentZoom = this.getCurrentZoomLevel();

            const onMouseMove = (ev) => {
                const dx = (ev.clientX - startX) / currentZoom;
                const dy = (ev.clientY - startY) / currentZoom;
                mask.x = initialX + dx;
                mask.y = initialY + dy;
            };

            const onMouseUp = () => {
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        },

        startMaskResize(e, handle, mask) {
            if (!mask.editable) return;

            const startX = e.clientX;
            const startY = e.clientY;
            const startWidth = mask.width;
            const startHeight = mask.height;
            const startLeft = mask.x;
            const startTop = mask.y;
            const rotationRad = mask.rotation * (Math.PI / 180);
            const currentZoom = this.getCurrentZoomLevel();

            const onMouseMove = (ev) => {
                let dx = (ev.clientX - startX) / currentZoom;
                let dy = (ev.clientY - startY) / currentZoom;

                const localDx = dx * Math.cos(-rotationRad) - dy * Math.sin(-rotationRad);
                const localDy = dx * Math.sin(-rotationRad) + dy * Math.cos(-rotationRad);

                if (handle.includes('e')) mask.width = Math.max(20, startWidth + localDx);
                if (handle.includes('s')) mask.height = Math.max(20, startHeight + localDy);
                if (handle.includes('w')) {
                    const newWidth = Math.max(20, startWidth - localDx);
                    if (mask.rotation === 0) mask.x = startLeft + localDx;
                    else {
                        // Approximate x/y shift for rotation
                        mask.width = newWidth;
                        // This part is imperfect for rotation without full matrix math, 
                        // but acceptable for 0-rotation which is 99% usage.
                    }
                    if (mask.rotation === 0) mask.width = newWidth;
                }
                if (handle.includes('n')) {
                    const newHeight = Math.max(20, startHeight - localDy);
                    if (mask.rotation === 0) mask.y = startTop + localDy;
                    if (mask.rotation === 0) mask.height = newHeight;
                }
            };

            const onMouseUp = () => {
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        },

        startMaskRotate(e, mask) {
            const startX = e.clientX;
            const startRotation = mask.rotation;

            const onMouseMove = (ev) => {
                const dx = ev.clientX - startX;
                mask.rotation = (startRotation + dx) % 360;
            };

            const onMouseUp = () => {
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        },

        getCurrentZoomLevel() {
            if (this.isImage(this.selectedFile?.name) || this.isTiff(this.selectedFile?.name) || this.isPdf(this.selectedFile?.name)) {
                return this.imageZoom;
            }
            return 1;
        },

        initBlocksForFile(file) {
            if (!file) return;

            const raw = file.blocks_position || {};
            let map = {};
            if (Array.isArray(raw)) {
                map['1'] = raw;
            } else {
                map = raw;
            }

            // Get current page
            let pageNum = 1;
            const name = file.name || '';
            if (this.isPdf(name)) pageNum = this.pdfPageNum;
            else if (this.isTiff(name)) pageNum = this.tifPageNum;

            const blocks = map[String(pageNum)] || [];
            this.masks = this.buildMasksFromBlocks(blocks);

            // If image is already loaded, calculate pixel coordinates immediately
            this.$nextTick(() => {
                this.recalculateMasks();
            });
        },

        recalculateMasks() {
            const img = this.$refs.mainImage || this.$refs.pdfCanvas || this.$refs.tifImg || this.$refs.hpglCanvas;
            if (!img) return;

            const displayW = img.clientWidth || img.width || 0;
            const displayH = img.clientHeight || img.height || 0;

            if (displayW === 0 || displayH === 0) return;

            this.masks.forEach(m => {
                if (!m) return;
                // If we have normalized coordinates and no pixel coordinates yet (or we want to refresh)
                if (m.u !== undefined && m.v !== undefined) {
                    m.x = m.u * displayW;
                    m.y = m.v * displayH;
                    m.width = (m.w || 0) * displayW;
                    m.height = (m.h || 0) * displayH;
                }
            });
        },

        onImageLoad() {
            this.imgLoading = false;
            this.imgError = '';
            this.recalculateMasks();
        },

        buildMasksFromBlocks(blocks) {
            if (!Array.isArray(blocks)) return [];
            return blocks
                .filter(b => b && typeof b === 'object')
                .map((b, i) => ({
                    id: String(b.id || ('blk-' + Date.now() + i)),
                    x: b.x || 0,
                    y: b.y || 0,
                    width: b.width || 100,
                    height: b.height || 50,
                    rotation: b.rotation || 0,
                    u: b.u, v: b.v, w: b.w, h: b.h,
                    active: false, visible: true, editable: true
                }));
        },

        getCursorStyle(handle, rotation) {
            // Simplified cursor style
            return 'pointer';
        }
    };
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { fileViewerComponent };
}
