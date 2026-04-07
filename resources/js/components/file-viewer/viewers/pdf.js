
export const getPdfState = () => ({
    pdfDoc: null,
    pdfPageNum: 1,
    pdfNumPages: 1, // Corrected from pdfTotalPages to match original
    pdfRendering: false,
    pdfPagePending: null,
    pdfScale: 1.0,
    pdfGetScale() { return this.pdfScale || 1.0; }, // Helper
    pdfRenderTask: null,
    pdfError: '',
    pdfLoading: false,
});

export const pdfMethods = {
    isPdf(filename) {
        return filename && filename.toLowerCase().endsWith('.pdf');
    },

    async loadPdf(file) {
        if (!window.pdfjsLib) {
            this.pdfError = 'PDF.js library not loaded';
            console.error('[FileViewer] PDF.js not available');
            return;
        }

        this.pdfLoading = true;
        this.pdfError = '';
        this.pdfPageNum = 1;

        try {
            // 1. Fetch data manually as ArrayBuffer with Progress
            this.loadingProgress = 0;
            this.loadingStatus = 'Downloading PDF...';

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
                    this.loadingProgress = Math.round((receivedLength / contentLength) * 80);
                }
            }

            const arrayBuffer = new Uint8Array(receivedLength);
            let position = 0;
            for (let chunk of chunks) {
                arrayBuffer.set(chunk, position);
                position += chunk.length;
            }

            this.loadingProgress = 90;
            this.loadingStatus = 'Rendering PDF...';

            // Cache data for redraws if needed
            this._pdfData = arrayBuffer;

            // 2. Load PDF from Data
            const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
            this.pdfDoc = await loadingTask.promise;

            // Use Alpine.raw to access properties safely
            const rawDoc = Alpine.raw(this.pdfDoc);
            this.pdfNumPages = rawDoc.numPages;

            if (this.initBlocksForFile) this.initBlocksForFile(file);

            await this.renderPdfPage();

            this.loadingProgress = 100;
            // Short delay to let the bar finish
            setTimeout(() => { this.pdfLoading = false; }, 400);

        } catch (error) {
            console.error('[FileViewer] PDF loading error:', error);
            this.pdfError = 'Failed to load PDF: ' + error.message;
            this.pdfLoading = false;
        }
    },

    async renderPdfPage() {
        if (!this.pdfDoc) return;
        this.loadingStatus = 'Rendering Page ' + this.pdfPageNum + '...';

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
            const rawDoc = Alpine.raw(this.pdfDoc);
            const page = await rawDoc.getPage(this.pdfPageNum);
            const rawPage = Alpine.raw(page);

            // Improve render quality: Use devicePixelRatio and a fidelity multiplier (1.5x)
            const pixelRatio = window.devicePixelRatio || 1;
            const fidelity = 1.5;
            const finalScale = this.pdfScale * pixelRatio * fidelity;

            const viewport = rawPage.getViewport({ scale: finalScale });

            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            const task = rawPage.render(renderContext);
            this.pdfRenderTask = task;

            await task.promise;
            this.pdfRenderTask = null;

            // SECURITY: Burn stamps into the canvas pixel data
            // Access _burnStampsToCanvas from stampMixin
            if (this._burnStampsToCanvas) {
                this._burnStampsToCanvas(context, canvas.width, canvas.height);
            }

            this.pdfLoading = false;
            this.pdfLoading = false;
            this.recalculateMasks();

            // Force layout recalculation after render
            setTimeout(() => {
                if (this.recalculateLayout) this.recalculateLayout();
            }, 50);
        } catch (error) {
            if (error.name === 'RenderingCancelledException' || error.message.includes('cancelled')) {
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
            this.resetView(); // Reset zoom/pan on page change
            if (this.initBlocksForFile) this.initBlocksForFile(this.selectedFile);
            this.renderPdfPage();
        }
    },

    prevPdfPage() {
        if (this.pdfPageNum > 1) {
            this.pdfPageNum--;
            this.resetView(); // Reset zoom/pan on page change
            if (this.initBlocksForFile) this.initBlocksForFile(this.selectedFile);
            this.renderPdfPage();
        }
    },

    redrawCanvas() {
        if (this.isPdf(this.selectedFile?.name)) {
            // Re-render current page to apply new stamp positions
            this.renderPdfPage();
        }
    }
};
