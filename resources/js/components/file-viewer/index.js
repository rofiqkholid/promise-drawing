import { getCoreState, coreMethods } from './core.js';
import { getStampState, stampMethods } from './features/stamps.js';
import { getMaskState, maskMethods } from './features/masking.js';
import { getImageState, imageMethods } from './viewers/image.js';
import { getPdfState, pdfMethods } from './viewers/pdf.js';
import { getTiffState, tiffMethods } from './viewers/tiff.js';
import { getHpglState, hpglMethods } from './viewers/hpgl.js';
import { getCadState, cadMethods } from './viewers/cad.js';
import { getCadMeasurementsState, cadMeasurementsMethods } from './features/measurements.js';
import { cadClippingState, cadClippingMethods } from './features/clipping.js';
import { cadExplodeState, cadExplodeMethods } from './features/explode.js';

export default (config = {}) => ({
    // ===== CONFIGURATION =====
    pkg: config.pkg || {},
    showStampConfig: config.showStampConfig || false,
    userDeptCode: config.userDeptCode || null,
    userName: config.userName || null,
    isEngineering: config.isEngineering || false,
    stampFormat: config.stampFormat || null,
    enableMasking: config.enableMasking || false,
    stampCopyTopLine: config.stampCopyTopLine || null,
    stampCopyBottomLine: config.stampCopyBottomLine || null,

    // ===== STATE MODULES (Factory functions for isolation) =====
    ...getCoreState(),
    ...getStampState(),
    ...getMaskState(),
    ...getImageState(),
    ...getPdfState(),
    ...getTiffState(),
    ...getHpglState(),
    ...getCadState(),
    ...getCadMeasurementsState(),
    ...cadClippingState, // Empty object is safe
    ...cadExplodeState,   // Empty object is safe

    ...coreMethods,
    ...stampMethods,
    ...maskMethods,
    ...imageMethods,
    ...pdfMethods,
    ...tiffMethods,
    ...hpglMethods,
    ...cadMethods,
    ...cadMeasurementsMethods,
    ...cadClippingMethods,
    ...cadExplodeMethods,

    init() {
        this.initCore();

        // Initialize feature states if needed (some have lazy init)
        if (this.initMeasurements) this.initMeasurements();
        if (this.initClipping) this.initClipping();
        if (this.initExplode) this.initExplode();

        // Watch for file selection changes
        this.$watch('selectedFile', (file) => {
            if (this.cleanupInteract) this.cleanupInteract();
            if (file) {
                if (this.loadStampConfigFor) this.loadStampConfigFor(file);
                this.reloadFile();
            }
        });

        // Watchers
        this.$watch('isFullscreen', (val) => {
            // Recalculate multiple times to catch transition frames
            this.recalculateMasks();
            setTimeout(() => this.recalculateMasks(), 100);
            setTimeout(() => this.recalculateMasks(), 350);
        });

        this.$watch('stampConfig', (newConfig, oldConfig) => {
            // No need to reload file explicitly here.
            // visual updates are handled by onStampChange -> redrawCanvas
            // persistence is handled by onStampChange -> persistStampConfigToDb
        }, { deep: true });

        // Initialize masks interaction when list changes
        this.$watch('masks', (val) => {
            // Use nextTick to allow DOM to update first
            this.$nextTick(() => {
                if (this.initMaskInteractions) this.initMaskInteractions();
            });
        });

        // CRITICAL: 3D Render Watchers
        // These ensure the 3D scene re-renders when state is modified by tools
        this.$watch('iges.clipping', () => { this.cadNeedsRender = true; }, { deep: true });
        this.$watch('iges.measure', () => { this.cadNeedsRender = true; }, { deep: true });
        this.$watch('iges.exploded.factor', () => { this.cadNeedsRender = true; });
        this.$watch('partOpacity', () => { if (this.updatePartOpacity) this.updatePartOpacity(); this.cadNeedsRender = true; });
        this.$watch('activeMaterial', () => { this.cadNeedsRender = true; });
        this.$watch('autoRotate', () => { this.cadNeedsRender = true; });
        this.$watch('cameraMode', () => { this.cadNeedsRender = true; });

        // Initialize masks when loading finishes (DOM rebuilt)
        this.$watch('imgLoading', (val) => {
            if (!val) this.$nextTick(() => this.initMaskInteractions && this.initMaskInteractions());
        });
        this.$watch('pdfLoading', (val) => {
            if (!val) this.$nextTick(() => this.initMaskInteractions && this.initMaskInteractions());
        });
        this.$watch('tifLoading', (val) => {
            if (!val) this.$nextTick(() => this.initMaskInteractions && this.initMaskInteractions());
        });
        this.$watch('hpglLoading', (val) => {
            if (!val) this.$nextTick(() => this.initMaskInteractions && this.initMaskInteractions());
        });
    },

    // Centralized reload function
    reloadFile() {
        if (!this.selectedFile) return;

        this.resetAllViewerStates();
        // Reset view (zoom, pan, rotation) to default whenever a new file is loaded
        this.resetView();

        const file = this.selectedFile;

        if (this.isPdf(file.name)) {
            this.loadPdf(file);
        } else if (this.isTiff(file.name)) {
            this.loadTiff(file);
        } else if (this.isHpgl(file.name)) {
            this.loadHpgl(file);
        } else if (this.isCad(file.name)) {
            this.loadCad(file);
        } else {
            this.loadImage(file);
        }
    },

    resetAllViewerStates() {
        this.isStampBurned = false;
        this.errorMessage = '';
        this.imgLoading = false;
        this.imgError = '';
        this.pdfLoading = false;
        this.pdfError = '';
        this.tifLoading = false;
        this.tifError = '';
        this.hpglLoading = false;
        this.hpglError = '';

        if (this.iges) {
            this.iges.loading = false;
            this.iges.error = '';
        }
        this._cadLoading = false;
    }
});
