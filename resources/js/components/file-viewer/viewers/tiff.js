
export const getTiffState = () => ({
    tifLoading: false,
    tifError: '',
    tifPageNum: 1,
    tifNumPages: 1,
    tifIfds: [],
    tifDecoder: null,
    _lastTiffUrl: null,
    tiffDisplayUrl: null, // Reactive URL
});

export const tiffMethods = {
    isTiff(filename) {
        if (!filename) return false;
        const ext = filename.split('.').pop().toLowerCase();
        return ['tif', 'tiff'].includes(ext);
    },

    async loadTiff(file) {
        this.tifLoading = true;
        this.tifError = '';
        this.tifPageNum = 1;
        this.loadingProgress = 0;
        this.loadingStatus = 'Downloading TIFF...';

        // Force the browser to paint the loading UI before proceeding with heavy operations
        await new Promise(resolve => setTimeout(resolve, 50));

        // Resolve UTIF library (handle potential nesting as in reference)
        const U = (window.UTIF && typeof window.UTIF.decode === 'function') ? window.UTIF :
            (window.UTIF && window.UTIF.UTIF && typeof window.UTIF.UTIF.decode === 'function') ? window.UTIF.UTIF :
                null;

        if (!U) {
            this.tifError = 'UTIF library not found or incompatible';
            console.error('[FileViewer] UTIF.js not available');
            this.tifLoading = false;
            return;
        }

        try {
            const response = await fetch(file.url, {
                cache: 'no-store',
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Failed to fetch TIFF file');

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
                    this.loadingProgress = Math.round((receivedLength / contentLength) * 50);
                }
            }

            const buf = new Uint8Array(receivedLength);
            let position = 0;
            for (let chunk of chunks) {
                buf.set(chunk, position);
                position += chunk.length;
            }

            this.loadingProgress = 60;
            this.loadingStatus = 'Decoding TIFF...';

            // Allow UI update
            await new Promise(r => setTimeout(r, 50));

            const ifds = U.decode(buf);
            if (!ifds || !ifds.length) throw new Error('TIFF file does not contain any frame');

            this.loadingProgress = 80;
            this.loadingStatus = 'Processing Frames...';
            await new Promise(r => setTimeout(r, 20));

            // Decode all frames/images immediately
            if (typeof U.decodeImages === 'function') {
                U.decodeImages(buf, ifds);
            } else if (typeof U.decodeImage === 'function') {
                ifds.forEach(ifd => U.decodeImage(buf, ifd));
            }

            // Store state
            this.tifDecoder = U;
            this.tifIfds = ifds;
            this.tifNumPages = ifds.length;
            this.tifPageNum = 1;

            if (this.initBlocksForFile) this.initBlocksForFile(file);

            this.loadingProgress = 95;
            this.loadingStatus = 'Finalizing...';
            await new Promise(r => setTimeout(r, 20));

            this.renderTiffPage();

            this.loadingProgress = 100;
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

            // SECURITY: Burn stamps into the canvas pixel data
            if (this._burnStampsToCanvas) {
                this._burnStampsToCanvas(ctx, w, h);
            }

            // Use toBlob instead of toDataURL for better memory efficiency with large files
            canvas.toBlob((blob) => {
                const url = URL.createObjectURL(blob);

                // Cleanup previous URL
                if (this._lastTiffUrl) {
                    URL.revokeObjectURL(this._lastTiffUrl);
                }
                this._lastTiffUrl = url;
                this.tiffDisplayUrl = url;

                // We don't set tifLoading = false here anymore. 
                // We rely on the @load="onTiffLoad" in the template for better synchronization.
                // However, we still do a safety check in nextTick in case the img element is missing.
                this.$nextTick(() => {
                    if (!this.$refs.tifImg) {
                        this.tifLoading = false;
                    }
                });
            }, 'image/png');
        } catch (error) {
            console.error('[FileViewer] TIFF rendering error:', error);
            this.tifError = 'Failed to render page: ' + error.message;
            this.tifLoading = false;
        }
    },

    nextTifPage() {
        if (this.tifPageNum < this.tifNumPages) {
            this.tifPageNum++;
            this.resetView(); // Reset zoom/pan on page change
            if (this.initBlocksForFile) this.initBlocksForFile(this.selectedFile);
            this.renderTiffPage();
        }
    },

    prevTifPage() {
        if (this.tifPageNum > 1) {
            this.tifPageNum--;
            this.resetView(); // Reset zoom/pan on page change
            if (this.initBlocksForFile) this.initBlocksForFile(this.selectedFile);
            this.renderTiffPage();
        }
    },

    onTiffLoad() {
        this.tifLoading = false;
        this.tifError = '';
        this.recalculateMasks();
        
        // Ensure layout is recalculated after image is fully rendered
        setTimeout(() => {
            if (this.recalculateLayout) this.recalculateLayout();
        }, 50);
    },

    onTiffError() {
        if (!this.tiffDisplayUrl) return;
        this.tifLoading = false;
        this.tifError = 'The TIFF image could not be rendered.';
    },

    redrawCanvas() {
        if (this.isTiff(this.selectedFile?.name)) {
            // Re-render current page locally to apply new stamps
            // Since `tifIfds` and `tifDecoder` are in memory, no fetch needed.
            this.renderTiffPage();
        }
    }
};
