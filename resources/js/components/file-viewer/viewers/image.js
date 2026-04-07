
export const getImageState = () => ({
    imgLoading: false,
    imgError: '',
    _lastImageUrl: null,
    imageDisplayUrl: null, // Reactive URL for the <img> tag
});

export const imageMethods = {
    isImage(filename) {
        if (!filename) return false;
        const ext = filename.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext);
    },

    async loadImage(file) {
        this.imgLoading = true;
        this.imgError = '';
        this.imageZoom = 1;
        this.loadingProgress = 0;
        this.loadingStatus = 'Downloading Image...';

        if (this.initBlocksForFile) this.initBlocksForFile(file);

        // Revoke previous URLs
        if (this._lastImageUrl) {
            URL.revokeObjectURL(this._lastImageUrl);
            this._lastImageUrl = null;
        }
        this._originalBlob = null;

        try {
            // 1. Fetch the original image with progress
            const response = await fetch(file.url);
            if (!response.ok) throw new Error('Network response was not ok');

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

            const blob = new Blob(chunks);
            this._originalBlob = blob;

            this.loadingProgress = 60;
            this.loadingStatus = 'Processing Image...';

            this._processBlobToStampedImage(blob);

        } catch (err) {
            console.error('[FileViewer] Image fetch error:', err);
            this.imgError = 'Failed to download image';
            this.imgLoading = false;
        }
    },

    // Helper using createImageBitmap to avoid creating intermediate Blob URLs
    _processBlobToStampedImage(blob) {
        createImageBitmap(blob)
            .then(imageBitmap => {
                // imageBitmap has width/height and can be drawn to canvas
                const canvas = document.createElement('canvas');
                canvas.width = imageBitmap.width;
                canvas.height = imageBitmap.height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(imageBitmap, 0, 0);

                // BURN STAMPS
                if (this._burnStampsToCanvas) {
                    this._burnStampsToCanvas(ctx, imageBitmap.width, imageBitmap.height);
                }

                // Close bitmap to save memory
                imageBitmap.close();

                // Convert to Blob URL (Final Stamped Result)
                canvas.toBlob((finalBlob) => {
                    const url = URL.createObjectURL(finalBlob);
                    // Update reactive URL
                    if (this._lastImageUrl) {
                        URL.revokeObjectURL(this._lastImageUrl);
                    }
                    this._lastImageUrl = url;
                    this.imageDisplayUrl = url;

                    // If for any reason the element isn't rendering yet, ensure loading still finishes
                    this.$nextTick(() => {
                        const img = this.$refs.mainImage;
                        if (!img || img.complete) {
                            this.imgLoading = false;
                        }
                    });
                }, 'image/png');
            })
            .catch(e => {
                console.error('[FileViewer] Bitmap creation error:', e);
                // Fallback (very rare)
                this.imgError = 'Failed to process image';
                this.imgLoading = false;
            });
    },

    onImageLoad() {
        this.imgLoading = false;
        this.imgError = '';
        this.recalculateMasks();
        
        this.$nextTick(() => {
            if (this.recalculateLayout) this.recalculateLayout();
        });
    },

    onImageError() {
        // Only show error if we have a URL but it failed to load
        if (!this.imageDisplayUrl) return;
        this.imgLoading = false;
        this.imgError = 'The image could not be loaded. Please check the file source.';
    },

    // Helper to redraw if stamps change
    redrawCanvas() {
        if (!this.selectedFile || !this.isImage(this.selectedFile.name)) return;

        // Use cached RAW blob to redraw stamps (No network request, No extra blob URL)
        if (this._originalBlob) {
            this._processBlobToStampedImage(this._originalBlob);
        } else {
            // Fallback if cache missing
            this.loadImage(this.selectedFile);
        }
    },

    imageTransformStyle() {
        const x = this.panX || 0;
        const y = this.panY || 0;
        const scale = this.imageZoom || 1;
        const rotate = this.rotation || 0;
        return {
            transform: `translate3d(${x}px, ${y}px, 0) scale(${scale}) rotate(${rotate}deg)`,
            transformOrigin: 'center center',
            transition: this.getTransitionStyle ? this.getTransitionStyle() : 'none'
        };
    }
};
