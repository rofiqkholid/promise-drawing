// interactjs is loaded via CDN in app.blade.php
// import interact from 'interactjs'; // Removed: Using CDN

export const getMaskState = () => ({
    masks: [],
    activeMask: null,
    isCreatingMask: false,
    isMaskInteracting: false,
    interactables: [],
});

export const maskMethods = {
    initMasking() {
        // console.log('[FileViewer] Masking Initialized');
        // Listen for internal events if needed
    },

    addMask() {
        if (this.isCreatingMask) {
            this.isCreatingMask = false;
            window.dispatchEvent(new CustomEvent('toast-show', {
                detail: { type: 'info', message: 'Block creation cancelled' }
            }));
            return;
        }

        this.isCreatingMask = true;
        this.deactivateMask();

        window.dispatchEvent(new CustomEvent('toast-show', {
            detail: { type: 'info', message: 'Click and drag on the image to create a block' }
        }));
    },

    startCreatingBlock(e) {
        e.preventDefault();
        e.stopPropagation();

        const img = this.$refs.mainImage || this.$refs.pdfCanvas || this.$refs.tifImg || this.$refs.hpglCanvas;
        if (!img) return;

        const isTouch = e.type.startsWith('touch');
        const clientX = isTouch ? e.touches[0].clientX : e.clientX;
        const clientY = isTouch ? e.touches[0].clientY : e.clientY;

        const rect = img.getBoundingClientRect();
        const currentZoom = this.getCurrentZoomLevel ? this.getCurrentZoomLevel() : 1;

        const startX = (clientX - rect.left) / currentZoom;
        const startY = (clientY - rect.top) / currentZoom;

        const id = 'blk-' + Date.now();
        const initialMask = {
            id: id,
            x: startX,
            y: startY,
            width: 0,
            height: 0,
            rotation: 0,
            active: true,
            editable: true,
            visible: true
        };

        this.masks.push(initialMask);
        const reactiveMask = this.masks[this.masks.length - 1];
        this.activeMask = reactiveMask;

        const onMove = (ev) => {
            const isT = ev.type.startsWith('touch');
            const currX = isT ? ev.touches[0].clientX : ev.clientX;
            const currY = isT ? ev.touches[0].clientY : ev.clientY;

            const currentRelX = (currX - rect.left) / currentZoom;
            const currentRelY = (currY - rect.top) / currentZoom;

            const width = currentRelX - startX;
            const height = currentRelY - startY;

            reactiveMask.x = width < 0 ? currentRelX : startX;
            reactiveMask.y = height < 0 ? currentRelY : startY;
            reactiveMask.width = Math.abs(width);
            reactiveMask.height = Math.abs(height);
        };

        const onEnd = () => {
            this.isCreatingMask = false;

            if (reactiveMask.width < 5 || reactiveMask.height < 5) {
                this.masks = this.masks.filter(m => m.id !== reactiveMask.id);
                this.activeMask = null;
                window.dispatchEvent(new CustomEvent('toast-show', {
                    detail: { type: 'warning', message: 'Block too small, ignored' }
                }));
            } else {
                this.normalizeMask(reactiveMask);
                this.activeMask = reactiveMask;
            }

            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onEnd);
            window.removeEventListener('touchmove', onMove);
            window.removeEventListener('touchend', onEnd);
        };

        if (isTouch) {
            window.addEventListener('touchmove', onMove, { passive: false });
            window.addEventListener('touchend', onEnd);
        } else {
            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', onEnd);
        }
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

    // Convert current pixel coordinates to normalized 0-1 coordinates (u,v,w,h)
    normalizeMask(mask) {
        if (!mask) return;
        const img = this.$refs.mainImage || this.$refs.pdfCanvas || this.$refs.tifImg || this.$refs.hpglCanvas;
        if (!img) return;

        const displayW = img.clientWidth || img.width || 1;
        const displayH = img.clientHeight || img.height || 1;

        if (displayW > 0) {
            mask.u = mask.x / displayW;
            mask.w = mask.width / displayW;
        }
        if (displayH > 0) {
            mask.v = mask.y / displayH;
            mask.h = mask.height / displayH;
        }
    },

    // Recalculate pixel coordinates from normalized coordinates (responsive resize)
    recalculateMasks() {
        // Only run if we are in a 2D view mode
        if (this.isCadFile && this.isCadFile(this.fileName)) return;

        const img = this.$refs.mainImage || this.$refs.pdfCanvas || this.$refs.tifImg || this.$refs.hpglCanvas;
        if (!img) return;

        const displayW = img.clientWidth || img.width || 0;
        const displayH = img.clientHeight || img.height || 0;

        if (displayW === 0 || displayH === 0) {
            if (!this._recalcRetryCount) this._recalcRetryCount = 0;
            if (this._recalcRetryCount < 10) {
                this._recalcRetryCount++;
                requestAnimationFrame(() => this.recalculateMasks());
            }
            return;
        }
        this._recalcRetryCount = 0;

        this.masks.forEach(m => {
            if (!m) return;
            if (m.u !== undefined && m.v !== undefined) {
                m.x = Math.round(m.u * displayW);
                m.y = Math.round(m.v * displayH);
                m.width = Math.round((m.w || 0) * displayW);
                m.height = Math.round((m.h || 0) * displayH);
            }
        });
    },

    initBlocksForFile(file) {
        if (!file) {
            this.masks = [];
            return;
        }

        const page = this.currentPageForSelectedFile ? this.currentPageForSelectedFile() : 1;
        let blocks = [];

        // Handle blocks_position which might be already parsed or JSON string
        const blocksPos = file.blocks_position;
        if (blocksPos) {
            let parsed = blocksPos;
            if (typeof blocksPos === 'string') {
                try {
                    parsed = JSON.parse(blocksPos);
                } catch (e) { console.warn('[Masking] Failed to parse blocks_position string', e); }
            }

            if (typeof parsed === 'object') {
                blocks = parsed[String(page)] || [];
            } else if (Array.isArray(parsed)) {
                // Backward compatibility for flat array
                blocks = parsed;
            }
        }

        this.masks = this.buildMasksFromBlocks(blocks);
        // Recalculate will be called by watchers or after image load
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

    getCurrentZoomLevel() {
        return this.imageZoom || 1;
    },

    // Style helper for mask element
    maskStyle(mask) {
        if (!mask) return {};

        const w = Math.round(mask.width || 0);
        const h = Math.round(mask.height || 0);
        const rx = Math.round(mask.x || 0);
        const ry = Math.round(mask.y || 0);

        const cx = rx + w / 2;
        const cy = ry + h / 2;
        const rot = (mask.rotation || 0).toFixed(2);

        const zoom = this.imageZoom || 1;
        return {
            left: '0px',
            top: '0px',
            width: w + 'px',
            height: h + 'px',
            transform: `translate3d(${cx}px, ${cy}px, 0) translate(-50%, -50%) rotate(${rot}deg)`,
            transformOrigin: 'center center',
            position: 'absolute',
            border: `${(1 / zoom).toFixed(3)}px solid rgba(0, 0, 0, 0.05)`, // Even more subtle border
            boxSizing: 'border-box'
            // backfaceVisibility: 'hidden' - Removed to prevent pixelation during zoom
        };
    },

    getHandleStyle(type, rotation) {
        const size = 10;
        const zoom = this.imageZoom || 1;
        const style = {
            position: 'absolute',
            width: `${size}px`,
            height: `${size}px`,
            backgroundColor: 'white',
            border: '1px solid #2563eb',
            borderRadius: '50%',
            zIndex: 40,
            transform: `translate(-50%, -50%) scale(${(1 / zoom).toFixed(3)})`,
            pointerEvents: 'auto'
        };

        // Calculate rotated cursor
        const cursors = ['n', 'ne', 'e', 'se', 's', 'sw', 'w', 'nw'];
        const baseAngles = { 'n': 0, 'ne': 45, 'e': 90, 'se': 135, 's': 180, 'sw': 225, 'w': 270, 'nw': 315 };

        const rot = parseFloat(rotation) || 0;
        const baseAngle = baseAngles[type] !== undefined ? baseAngles[type] : 0;
        const rotatedAngle = (baseAngle + rot + 360) % 360;
        const index = Math.round(rotatedAngle / 45) % 8;
        style.cursor = cursors[index] + '-resize';

        switch (type) {
            case 'nw': style.top = '0%'; style.left = '0%'; break;
            case 'n': style.top = '0%'; style.left = '50%'; break;
            case 'ne': style.top = '0%'; style.left = '100%'; break;
            case 'e': style.top = '50%'; style.left = '100%'; break;
            case 'se': style.top = '100%'; style.left = '100%'; break;
            case 's': style.top = '100%'; style.left = '50%'; break;
            case 'sw': style.top = '100%'; style.left = '0%'; break;
            case 'w': style.top = '50%'; style.left = '0%'; break;
        }

        return style;
    },

    getRotateHandleStyle(pos, rotation) {
        const style = this.getHandleStyle(pos, rotation);
        style.backgroundColor = '#2563eb';
        style.zIndex = 30;
        style.cursor = 'grab';

        const zoom = this.imageZoom || 1;
        const offset = 12 / zoom;
        switch (pos) {
            case 'nw': style.marginLeft = `-${offset}px`; style.marginTop = `-${offset}px`; break;
            case 'ne': style.marginLeft = `${offset}px`; style.marginTop = `-${offset}px`; break;
            case 'sw': style.marginLeft = `-${offset}px`; style.marginTop = `${offset}px`; break;
            case 'se': style.marginLeft = `${offset}px`; style.marginTop = `${offset}px`; break;
        }
        return style;
    },

    startMaskRotate(e, mask) {
        e.preventDefault();
        e.stopPropagation();

        const input = e.touches ? e.touches[0] : e;
        const startX = input.clientX;
        const startY = input.clientY;

        // Find center of the mask
        const el = e.target.closest('.mask-element');
        if (!el) return;

        const rect = el.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        // Initial angle of the mouse relative to center
        const startAngle = Math.atan2(startY - centerY, startX - centerX) * 180 / Math.PI;
        const initialRotation = parseFloat(mask.rotation) || 0;

        this.isMaskInteracting = true;

        const onMove = (ev) => {
            const currInput = ev.touches ? ev.touches[0] : ev;
            const currX = currInput.clientX;
            const currY = currInput.clientY;

            // Current angle of the mouse relative to center
            const angle = Math.atan2(currY - centerY, currX - centerX) * 180 / Math.PI;

            // Calculate delta
            const delta = angle - startAngle;

            // Apply delta to initial rotation
            let newRotation = (initialRotation + delta);

            // Normalize to 0-360
            newRotation = (newRotation % 360);
            if (newRotation < 0) newRotation += 360;

            mask.rotation = newRotation;
        };

        const onEnd = () => {
            this.isMaskInteracting = false;
            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onEnd);
            window.removeEventListener('touchmove', onMove);
            window.removeEventListener('touchend', onEnd);
        };

        if (e.touches) {
            window.addEventListener('touchmove', onMove, { passive: false });
            window.addEventListener('touchend', onEnd);
        } else {
            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', onEnd);
        }
    },

    startMaskResize(e, mask, handleType) {
        e.preventDefault();
        e.stopPropagation();

        // Get the mask element
        const maskEl = e.target.closest('.mask-element');
        if (!maskEl) return;

        const input = e.touches ? e.touches[0] : e;
        const startX = input.clientX;
        const startY = input.clientY;

        // Freeze initial state
        const state = {
            width: mask.width,
            height: mask.height,
            x: mask.x,
            y: mask.y,
            cx: mask.x + mask.width / 2,
            cy: mask.y + mask.height / 2,
            rotation: parseFloat(mask.rotation) || 0
        };

        const rotRad = state.rotation * (Math.PI / 180);
        const cosA = Math.cos(rotRad);
        const sinA = Math.sin(rotRad);

        this.isMaskInteracting = true;
        this.isPanning = false;

        const onMove = (ev) => {
            const currInput = ev.touches ? ev.touches[0] : ev;
            const currX = currInput.clientX;
            const currY = currInput.clientY;

            const zoom = this.getCurrentZoomLevel();
            const dx = (currX - startX) / zoom;
            const dy = (currY - startY) / zoom;

            // Project screen delta onto local (rotated) axes
            const localDx = dx * cosA + dy * sinA;
            const localDy = -dx * sinA + dy * cosA;

            let newWidth = state.width;
            let newHeight = state.height;
            let localShiftX = 0;
            let localShiftY = 0;

            // Calculate new dimensions and local center shift based on handle
            switch (handleType) {
                case 'nw':
                    newWidth = Math.max(10, state.width - localDx);
                    newHeight = Math.max(10, state.height - localDy);
                    localShiftX = -(newWidth - state.width) / 2;
                    localShiftY = -(newHeight - state.height) / 2;
                    break;
                case 'ne':
                    newWidth = Math.max(10, state.width + localDx);
                    newHeight = Math.max(10, state.height - localDy);
                    localShiftX = (newWidth - state.width) / 2;
                    localShiftY = -(newHeight - state.height) / 2;
                    break;
                case 'sw':
                    newWidth = Math.max(10, state.width - localDx);
                    newHeight = Math.max(10, state.height + localDy);
                    localShiftX = -(newWidth - state.width) / 2;
                    localShiftY = (newHeight - state.height) / 2;
                    break;
                case 'se':
                    newWidth = Math.max(10, state.width + localDx);
                    newHeight = Math.max(10, state.height + localDy);
                    localShiftX = (newWidth - state.width) / 2;
                    localShiftY = (newHeight - state.height) / 2;
                    break;
                case 'n':
                    newHeight = Math.max(10, state.height - localDy);
                    localShiftY = -(newHeight - state.height) / 2;
                    break;
                case 's':
                    newHeight = Math.max(10, state.height + localDy);
                    localShiftY = (newHeight - state.height) / 2;
                    break;
                case 'w':
                    newWidth = Math.max(10, state.width - localDx);
                    localShiftX = -(newWidth - state.width) / 2;
                    break;
                case 'e':
                    newWidth = Math.max(10, state.width + localDx);
                    localShiftX = (newWidth - state.width) / 2;
                    break;
            }

            // Convert local shift to screen space
            const screenShiftX = localShiftX * cosA - localShiftY * sinA;
            const screenShiftY = localShiftX * sinA + localShiftY * cosA;

            // Calculate new center
            const newCx = state.cx + screenShiftX;
            const newCy = state.cy + screenShiftY;

            // Update DOM directly (bypass Alpine reactivity during drag)
            maskEl.style.width = Math.round(newWidth) + 'px';
            maskEl.style.height = Math.round(newHeight) + 'px';
            maskEl.style.transform = `translate3d(${newCx.toFixed(1)}px, ${newCy.toFixed(1)}px, 0) translate(-50%, -50%) rotate(${state.rotation}deg)`;
        };

        const onEnd = () => {
            this.isMaskInteracting = false;

            // Now commit the final values to reactive properties
            const finalWidth = parseFloat(maskEl.style.width);
            const finalHeight = parseFloat(maskEl.style.height);

            // Extract center from transform
            const transformMatch = maskEl.style.transform.match(/translate3d\(([^,]+)px,\s*([^,]+)px/);
            if (transformMatch) {
                const finalCx = parseFloat(transformMatch[1]);
                const finalCy = parseFloat(transformMatch[2]);

                mask.width = finalWidth;
                mask.height = finalHeight;
                mask.x = finalCx - finalWidth / 2;
                mask.y = finalCy - finalHeight / 2;
                this.normalizeMask(mask);
            }

            // Clear inline styles to let Alpine take over again
            maskEl.style.width = '';
            maskEl.style.height = '';
            maskEl.style.transform = '';

            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onEnd);
            window.removeEventListener('touchmove', onMove);
            window.removeEventListener('touchend', onEnd);
        };

        if (e.touches) {
            window.addEventListener('touchmove', onMove, { passive: false });
            window.addEventListener('touchend', onEnd);
        } else {
            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', onEnd);
        }
    },
    initMaskInteractions() {
        // interact is available globally via CDN
        if (typeof window.interact === 'undefined') {
            console.warn('[FileViewer] interactjs not loaded');
            return;
        }
        this.cleanupInteract();

        this.$nextTick(() => {
            const elements = this.$el.querySelectorAll('.mask-element');
            elements.forEach(el => {
                const maskId = el.getAttribute('data-mask-id');
                const mask = this.masks.find(m => String(m.id) === String(maskId));
                if (!mask || !mask.editable) return;

                const i = window.interact(el)
                    .draggable({
                        ignoreFrom: '.mask-handle, .mask-handle-rotate',
                        inertia: false,
                        listeners: {
                            start: (event) => {
                                this.isMaskInteracting = true;
                                this.isPanning = false; // Disable pan while dragging mask
                                event.target._dragState = { x: mask.x, y: mask.y };
                            },
                            move: (event) => {
                                const zoom = this.getCurrentZoomLevel();
                                const state = event.target._dragState;
                                if (!state) return;

                                // Calculate delta in screen space
                                const dx = event.dx / zoom;
                                const dy = event.dy / zoom;

                                // Update position state directly (screen space movement)
                                state.x += dx;
                                state.y += dy;

                                // Update visual element via CSS transform
                                const cx = state.x + mask.width / 2;
                                const cy = state.y + mask.height / 2;
                                const rot = (mask.rotation || 0);

                                event.target.style.transform = `translate3d(${cx.toFixed(1)}px, ${cy.toFixed(1)}px, 0) translate(-50%, -50%) rotate(${rot}deg)`;
                            },
                            end: (event) => {
                                const state = event.target._dragState;
                                if (state) {
                                    mask.x = state.x;
                                    mask.y = state.y;
                                    this.normalizeMask(mask);
                                }
                                this.isMaskInteracting = false;
                                delete event.target._dragState;
                            }
                        }
                    });

                this.interactables.push(i);
            });
        });
    },

    cleanupInteract() {
        if (this.interactables) {
            this.interactables.forEach(i => i.unset());
        }
        this.interactables = [];
    },

    selectAvailableMask() {
        if (!this.masks || this.masks.length === 0) {
            window.dispatchEvent(new CustomEvent('toast-show', {
                detail: { type: 'info', message: 'No blocks available to focus' }
            }));
            return;
        }

        // Cycle through masks
        let nextIndex = 0;
        if (this.activeMask) {
            const currId = String(this.activeMask.id);
            const currIndex = this.masks.findIndex(m => String(m.id) === currId);
            if (currIndex !== -1) {
                nextIndex = (currIndex + 1) % this.masks.length;
            }
        }

        const nextMask = this.masks[nextIndex];
        this.activateMask(nextMask);

        // Small delay to ensure any pending recalculations or activation rituals settle
        this.$nextTick(() => {
            this.focusOnMask(nextMask);
        });
    },

    focusOnMask(mask) {
        if (!mask) return;

        // Ensure we have current display geometry
        const content = this.getContentSize();
        if (!content || content.width === 0) return;

        // Target zoom level for focusing
        const targetZoom = Math.max(this.imageZoom, 1.5);

        // Calculate point to center (center of the mask in image coordinates)
        const mx = (mask.x || 0) + (mask.width || 0) / 2;
        const my = (mask.y || 0) + (mask.height || 0) / 2;

        const cw = content.width;
        const ch = content.height;

        // Calculate pan required to bring (mx, my) to viewport center
        // With transform-origin: center center:
        // panX = (imageCenter_initial - mouseX) * zoom
        let targetPanX = (cw / 2 - mx) * targetZoom;
        let targetPanY = (ch / 2 - my) * targetZoom;

        // Apply constraints (logic from core.js onPan)
        const container = this.$refs.viewport2d || this.$refs.ref2dContainer || this.$refs.refMainContainer;
        if (container) {
            const rect = container.getBoundingClientRect();
            const zoomedW = cw * targetZoom;
            const zoomedH = ch * targetZoom;

            const maxPanX = Math.abs(zoomedW - rect.width) / 2 + (this.panGap || 100);
            const maxPanY = Math.abs(zoomedH - rect.height) / 2 + (this.panGap || 100);

            targetPanX = Math.min(maxPanX, Math.max(-maxPanX, targetPanX));
            targetPanY = Math.min(maxPanY, Math.max(-maxPanY, targetPanY));
        }

        // Set state
        this.imageZoom = targetZoom;
        this.panX = targetPanX;
        this.panY = targetPanY;

        window.dispatchEvent(new CustomEvent('toast-show', {
            detail: { type: 'info', message: `Focussed on block ${mask.id}` }
        }));
    },

    saveCurrentMask() {
        // Dispatch event for parent component to handle saving
        // Normalizing all masks first to ensure they have correct u,v,w,h
        this.masks.forEach(m => this.normalizeMask(m));

        const event = new CustomEvent('masks-updated', {
            detail: {
                masks: JSON.parse(JSON.stringify(this.masks)) // Clone to avoid proxy issues
            }
        });
        window.dispatchEvent(event);

        // console.log('[FileViewer] Dispatched masks-updated:', this.masks);
    },

    applyActiveMaskToAll() {
        if (!this.activeMask) return;

        this.normalizeMask(this.activeMask);

        const event = new CustomEvent('masks-applied-to-all', {
            detail: {
                masks: [JSON.parse(JSON.stringify(this.activeMask))],
                append_mode: true
            }
        });
        window.dispatchEvent(event);

        window.dispatchEvent(new CustomEvent('toast-show', {
            detail: { type: 'info', message: 'Applying current block pattern to all pages...' }
        }));
    }
};
