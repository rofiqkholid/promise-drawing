
export const getCoreState = () => ({
    isLoading: true,
    fileUrl: '',
    fileName: '',
    fileType: 'unknown',
    errorMessage: '',
    selectedFile: null,
    lastLoadedUrl: null,

    // Loading States
    isLoadingRevision: false,
    isLoadingPackage: false,
    loadingProgress: 0,
    loadingStatus: '',

    // View State
    imageZoom: 1,
    minZoom: 0.5,
    maxZoom: 5,
    zoomStep: 0.25,
    touchZoomDistance: 0,

    panX: 0,
    panY: 0,
    isPanning: false,
    panStartX: 0,
    panStartY: 0,
    panOriginX: 0,
    panOriginY: 0,
    panGap: 100,

    rotation: 0,
    isDragging: false,
    isFullscreen: false,

    // Tab/Mode State
    activeTab: 'view', // view, info, layers

    // Feature Flags/Config
    enableStamps: true,
    enableMasking: true,

    // Mobile Detection
    isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
});

export const coreMethods = {
    initCore() {
        // console.log('[FileViewer] Core Initialized');

        // Handle window resize
        window.addEventListener('resize', this.debounce(() => this.recalculateLayout(), 100).bind(this));

        // Handle fullscreen change
        document.addEventListener('fullscreenchange', this.onFullscreenChange.bind(this));

        // Handle keyboard shortcuts
        window.addEventListener('keydown', this.onKeydown.bind(this));

        // Global mouse/touch listeners for panning
        document.addEventListener('mousemove', (e) => this.onPan(e));
        document.addEventListener('mouseup', () => this.endPan());
        document.addEventListener('touchmove', (e) => {
            if (e.touches && e.touches[0]) this.onPan(e.touches[0]);
        }, { passive: false });
        document.addEventListener('touchend', () => this.endPan());
    },

    onResize() {
        this.recalculateLayout();
    },

    onFullscreenChange() {
        this.isFullscreen = !!document.fullscreenElement;
        // Reset pan and zoom to prevent coordinate drift
        this.resetView();
        this.recalculateLayout();
    },

    onKeydown(e) {
        if (e.key === 'Escape' && this.isFullscreen) {
            this.toggleFullscreen();
        }
    },

    toggleFullscreen() {
        const el = this.$refs.refMainContainer;
        if (!el) return;

        // Reset pan and zoom to prevent coordinate drift
        this.resetView();

        if (!document.fullscreenElement) {
            el.requestFullscreen().then(() => {
                this.isFullscreen = true;
            }).catch(err => {
                console.error(`Error fullscreen: ${err.message}`);
                // Fallback for Safari
                if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
            });
        } else {
            document.exitFullscreen().then(() => {
                this.isFullscreen = false;
            });
        }
    },

    setLoading(state) {
        this.isLoading = state;
    },

    setError(msg) {
        this.errorMessage = msg;
        this.isLoading = false;
    },

    getTransitionStyle() {
        // Disable transition during panning for instant feedback
        // Enable transition during zoom/reset for smooth animation
        return this.isPanning ? 'none' : 'transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)';
    },

    getContentSize() {
        const el = this.$refs.mainImage || this.$refs.pdfCanvas || this.$refs.tifImg || this.$refs.hpglCanvas;
        if (!el) return null;
        return {
            width: el.clientWidth || el.width || 0,
            height: el.clientHeight || el.height || 0
        };
    },

    applyZoom(newZoom, focalX, focalY) {
        const oldZoom = this.imageZoom;
        if (oldZoom === newZoom) return;

        const container = this.$refs.viewport2d || this.$refs.ref2dContainer || this.$refs.refMainContainer;
        if (!container) {
            this.imageZoom = newZoom;
            return;
        }

        const rect = container.getBoundingClientRect();

        // Default to center if no focal point provided
        let mx, my;
        if (focalX !== undefined && focalY !== undefined) {
            mx = focalX - rect.left;
            my = focalY - rect.top;
        } else {
            mx = rect.width / 2;
            my = rect.height / 2;
        }

        const cw = rect.width;
        const ch = rect.height;

        const ratio = newZoom / oldZoom;
        let targetX = (mx - cw / 2) - (mx - cw / 2 - this.panX) * ratio;
        let targetY = (my - ch / 2) - (my - ch / 2 - this.panY) * ratio;

        // Apply pan constraints during zoom
        const content = this.getContentSize();
        if (content) {
            const zoomedW = content.width * newZoom;
            const zoomedH = content.height * newZoom;

            // Allow more flexible constraints: image edges can be moved to viewport edges
            // regardless of whether the image is larger or smaller than the screen.
            const limitX = Math.abs(zoomedW - cw) / 2;
            const limitY = Math.abs(zoomedH - ch) / 2;
            const gap = 60; // Reduced for more precise boundary feel

            targetX = Math.min(limitX + gap, Math.max(-limitX - gap, targetX));
            targetY = Math.min(limitY + gap, Math.max(-limitY - gap, targetY));
        }

        this.panX = targetX;
        this.panY = targetY;
        this.imageZoom = Math.max(0.1, newZoom);
    },

    onWheelZoom(e) {
        const delta = e.deltaY;
        const step = this.zoomStep;
        let targetZoom;

        if (delta < 0) {
            targetZoom = Math.min(this.imageZoom + step, this.maxZoom);
        } else if (delta > 0) {
            targetZoom = Math.max(this.imageZoom - step, this.minZoom);
        } else {
            return;
        }

        this.applyZoom(targetZoom, e.clientX, e.clientY);
    },

    zoomIn() {
        const newZoom = Math.min(this.imageZoom + this.zoomStep, this.maxZoom);
        this.applyZoom(newZoom);
    },

    zoomOut() {
        const newZoom = Math.max(this.imageZoom - this.zoomStep, this.minZoom);
        this.applyZoom(newZoom);
    },

    resetZoom() {
        this.resetView();
    },

    startPan(e) {
        if (!e || (this.isMaskInteracting)) return;

        // Check if we are in mask creation mode
        if (this.isCreatingMask) {
            if (this.startCreatingBlock) this.startCreatingBlock(e);
            return;
        }

        // Detect if the target is a mask or a handle to prevent background panning
        const target = e.target || (e.touches && e.touches[0] ? e.touches[0].target : null);
        if (target && (target.closest('.mask-element') || target.closest('.mask-handle'))) {
            return;
        }

        // Deactivate active mask when clicking the background
        if (this.deactivateMask) this.deactivateMask();

        const input = e.touches ? e.touches[0] : e;
        if (!input || input.clientX === undefined) return;

        this.isPanning = true;
        this.panStartX = input.clientX;
        this.panStartY = input.clientY;
        this.panOriginX = this.panX;
        this.panOriginY = this.panY;
    },

    onPan(e) {
        if (!this.isPanning) return;

        const clientX = e.clientX !== undefined ? e.clientX : (e.touches ? e.touches[0].clientX : 0);
        const clientY = e.clientY !== undefined ? e.clientY : (e.touches ? e.touches[0].clientY : 0);

        const dx = clientX - this.panStartX;
        const dy = clientY - this.panStartY;
        this.panX = this.panOriginX + dx;
        this.panY = this.panOriginY + dy;

        // Apply constraints
        const container = this.$refs.viewport2d || this.$refs.ref2dContainer || this.$refs.refMainContainer;
        const content = this.getContentSize();

        if (container && content) {
            const rect = container.getBoundingClientRect();
            // Current displayed size
            const currentW = content.width * this.imageZoom;
            const currentH = content.height * this.imageZoom;

            // Calculate max overflow (positive implies content > viewport)
            const overflowX = (currentW - rect.width) / 2;
            const overflowY = (currentH - rect.height) / 2;

            const gap = 100; // buffer

            if (currentW > rect.width) {
                this.panX = Math.min(overflowX + gap, Math.max(-overflowX - gap, this.panX));
            } else {
                // Content smaller than view
                const limitX = (rect.width - currentW) / 2;
                this.panX = Math.min(limitX + gap, Math.max(-limitX - gap, this.panX));
            }

            if (currentH > rect.height) {
                this.panY = Math.min(overflowY + gap, Math.max(-overflowY - gap, this.panY));
            } else {
                // Content smaller than view
                const limitY = (rect.height - currentH) / 2;
                this.panY = Math.min(limitY + gap, Math.max(-limitY - gap, this.panY));
            }
        }
    },

    endPan() {
        this.isPanning = false;
    },

    resetView() {
        this.imageZoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.rotation = 0;
        // Wait for DOM updates or transitions to finish before recalculating layout
        setTimeout(() => this.recalculateLayout(), 50);
    },

    recalculateLayout() {
        // Implement "Fit to Container" logic
        const container = this.$refs.viewport2d || this.$refs.ref2dContainer || this.$refs.refMainContainer;
        const content = this.getContentSize();

        if (container && content && content.width > 0 && content.height > 0) {
            const rect = container.getBoundingClientRect();
            const containerW = rect.width;
            const containerH = rect.height;

            const contentW = content.width;
            const contentH = content.height;

            // Calculate scale to fit
            const scaleX = containerW / contentW;
            const scaleY = containerH / contentH;

            // "Fit" behavior (contain): use the smaller scale so entire image is visible
            // If you wanted "Fill" (cover), uses Math.max
            let fitScale = Math.min(scaleX, scaleY);

            // Optional: Cap the default max zoom to 1.0 if image is smaller than screen?
            // User requested "fit to width/height", usually means scaling UP is okay if image is tiny.
            // But let's keep it sane. If image is tiny, maybe just 1.0 is fine?
            // Usually "Fit to Screen" implies scaling up or down.

            // Apply a small padding (e.g. 95%) so it's not touching edges perfectly
            fitScale = fitScale * 0.98;

            // Update zoom
            this.imageZoom = fitScale;
            this.panX = 0;
            this.panY = 0;
        }

        if (this.recalculateMasks) this.recalculateMasks();
    },

    // Utility: Simple debounce
    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    // Utility: Copy to clipboard
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            window.dispatchEvent(new CustomEvent('toast-show', {
                detail: { type: 'success', message: 'Copied to clipboard' }
            }));
        } catch (err) {
            console.error('Failed to copy:', err);
        }
    },

    formatBytes(bytes, decimals = 2) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    },

    // File Type Helper
    isPreviewable2D(name) {
        if (!name) return false;
        const ext = name.split('.').pop().toLowerCase();
        const images = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        const pdfs = ['pdf'];
        const tiffs = ['tif', 'tiff'];
        const hpgl = ['hpgl', 'plt', 'hpg'];

        return images.includes(ext) || pdfs.includes(ext) || tiffs.includes(ext) || hpgl.includes(ext);
    },

    currentPageForSelectedFile() {
        if (!this.selectedFile) return 1;
        const name = this.selectedFile.name || '';
        if (this.isPdf && this.isPdf(name)) return this.pdfPageNum || 1;
        if (this.isTiff && this.isTiff(name)) return this.tifPageNum || 1;
        return 1;
    }
};
