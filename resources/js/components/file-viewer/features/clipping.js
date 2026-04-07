
export const cadClippingState = {
    // State is initialized in initClipping
};

export const cadClippingMethods = {
    initClipping() {
        if (!this.iges.clipping) {
            this.iges.clipping = {};
        }

        // Ensure all properties exist
        const defaults = {
            panelOpen: false,
            min: -100, max: 100, step: 0.5,
            x: { enabled: false, value: 0, min: -100, max: 100, showHelper: true, flipped: false, plane: null, helper: null },
            y: { enabled: false, value: 0, min: -100, max: 100, showHelper: true, flipped: false, plane: null, helper: null },
            z: { enabled: false, value: 0, min: -100, max: 100, showHelper: true, flipped: false, plane: null, helper: null },
            planes: [],
            activeAxis: null,
            _dragState: null,
            _listenersBound: false
        };

        Object.keys(defaults).forEach(key => {
            if (this.iges.clipping[key] === undefined) {
                this.iges.clipping[key] = defaults[key];
            }
        });

        // Always try to setup listeners if renderer exists and they aren't bound yet
        if (this.iges.renderer && !this.iges.clipping._listenersBound) {
            this._setupClippingDragListeners();
            this.iges.clipping._listenersBound = true;
        }
    },

    _setupClippingDragListeners() {
        const { renderer, THREE } = this.iges;
        if (!renderer) return;

        const el = renderer.domElement;
        const raycaster = new THREE.Raycaster();
        const mouse = new THREE.Vector2();

        // Store drag state
        this.iges.clipping._dragState = null;

        // Mouse down handler
        const onMouseDown = (event) => {
            const iges = this.iges;
            const camera = Alpine.raw(iges.camera);
            const controls = Alpine.raw(iges.controls);

            // Skip if measure tool is active
            if (iges.measure?.enabled) return;

            const rect = el.getBoundingClientRect();
            mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            raycaster.params.Mesh.threshold = 0.5;

            // Check all active plane helpers
            for (const axisName of ['x', 'y', 'z']) {
                const axisData = iges.clipping[axisName];
                if (!axisData.helper || !axisData.showHelper || !axisData.enabled) continue;

                // Unwrap for reliable intersection
                const helper = Alpine.raw(axisData.helper);

                // Intersect with helper group
                const intersects = raycaster.intersectObjects([helper], true);

                if (intersects.length > 0) {
                    const intersectionPoint = intersects[0].point;
                    const normal = new THREE.Vector3();
                    camera.getWorldDirection(normal);

                    const plane = new THREE.Plane();
                    plane.setFromNormalAndCoplanarPoint(normal, intersectionPoint);

                    let clickValue;
                    if (axisName === 'x') clickValue = intersectionPoint.x;
                    else if (axisName === 'y') clickValue = intersectionPoint.y;
                    else clickValue = intersectionPoint.z;

                    iges.clipping._dragState = {
                        isDragging: true,
                        axis: axisName,
                        dragPlane: plane,
                        offset: clickValue - axisData.value
                    };

                    // Disable orbit controls while dragging
                    if (controls) controls.enabled = false;

                    // Add highlight effect when dragging
                    this._setHelperHighlight(helper, true);

                    // Change cursor
                    el.style.cursor = 'move';
                    event.preventDefault();
                    event.stopPropagation();

                    // console.log('[Clipping] Drag started on axis:', axisName);
                    break;
                }
            }
        };

        // Mouse move handler - drag plane
        let hoverRequest = null;
        let dragRequest = null;
        const onMouseMove = (event) => {
            const iges = this.iges;
            const drag = iges.clipping._dragState;

            if (!drag || !drag.isDragging) {
                // If clipping panel not open, don't waste CPU on hover detection
                if (!iges.clipping?.panelOpen) return;

                if (hoverRequest) cancelAnimationFrame(hoverRequest);
                hoverRequest = requestAnimationFrame(() => {
                    // Hover detection for cursor feedback
                    const camera = Alpine.raw(iges.camera);
                    const rect = el.getBoundingClientRect();

                    // Use persistent objects if available, else local (mouse/raycaster already in outer scope)
                    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                    raycaster.setFromCamera(mouse, camera);
                    raycaster.params.Mesh.threshold = 0.5;

                    let isOverPlane = false;
                    for (const axisName of ['x', 'y', 'z']) {
                        const axisData = iges.clipping[axisName];
                        if (!axisData.helper || !axisData.showHelper || !axisData.enabled) continue;

                        const helper = Alpine.raw(axisData.helper);
                        const intersects = raycaster.intersectObjects([helper], true);

                        if (intersects.length > 0) {
                            isOverPlane = true;
                            break;
                        }
                    }

                    el.style.cursor = isOverPlane ? 'pointer' : 'default';
                    hoverRequest = null;
                });
                return;
            }

            // Dragging Logic - Throttled
            if (dragRequest) cancelAnimationFrame(dragRequest);
            dragRequest = requestAnimationFrame(() => {
                const axis = drag.axis;
                const axisData = iges.clipping[axis];
                const camera = Alpine.raw(iges.camera);

                const rect = el.getBoundingClientRect();
                mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                raycaster.setFromCamera(mouse, camera);

                // Intersect with the virtual drag plane
                const targetPoint = new THREE.Vector3();
                raycaster.ray.intersectPlane(drag.dragPlane, targetPoint);

                if (targetPoint) {
                    // Project the target point onto our axis to get the raw value
                    let rawValue;
                    if (axis === 'x') rawValue = targetPoint.x;
                    else if (axis === 'y') rawValue = targetPoint.y;
                    else rawValue = targetPoint.z;

                    // Apply the initial offset
                    let newValue = rawValue - drag.offset;

                    // Clamp
                    const min = axisData.min !== undefined ? axisData.min : iges.clipping.min;
                    const max = axisData.max !== undefined ? axisData.max : iges.clipping.max;
                    newValue = Math.max(min, Math.min(max, newValue));

                    // Round to step
                    const step = iges.clipping.step || 0.5;
                    newValue = Math.round(newValue / step) * step;

                    // Update only if there's a significant change
                    if (Math.abs(axisData.value - newValue) > 0.0001) {
                        axisData.value = newValue;
                        this.updateAxisClipping(axis);
                    }
                }
                dragRequest = null;
            });
        };

        // Mouse up handler
        const onMouseUp = (event) => {
            const iges = this.iges;
            const drag = iges.clipping._dragState;

            if (drag && drag.isDragging) {
                // Remove highlight effect
                const axisData = iges.clipping[drag.axis];
                if (axisData && axisData.helper) {
                    this._setHelperHighlight(Alpine.raw(axisData.helper), false);
                }

                // Reset drag state
                iges.clipping._dragState = null;

                // Re-enable orbit controls
                const controls = Alpine.raw(iges.controls);
                if (controls) {
                    controls.enabled = true;
                    // console.log('[Clipping] Controls re-enabled');
                }

                // Reset cursor
                el.style.cursor = 'default';

                // console.log('[Clipping] Drag successfully ended');
            }
        };

        // Add event listeners once
        el.addEventListener('mousedown', onMouseDown, false);
        el.addEventListener('mousemove', onMouseMove, false);
        el.addEventListener('mouseup', onMouseUp, false);
        el.addEventListener('mouseleave', onMouseUp, false);

        this._mouseListeners.push({ element: el, type: 'mousedown', handler: onMouseDown });
        this._mouseListeners.push({ element: el, type: 'mousemove', handler: onMouseMove });
        this._mouseListeners.push({ element: el, type: 'mouseup', handler: onMouseUp });
        this._mouseListeners.push({ element: el, type: 'mouseleave', handler: onMouseUp });

        // console.log('[Clipping] Drag listeners initialized with mouse events');
    },

    /**
     * Set highlight state for a clipping helper group
     */
    _setHelperHighlight(group, active) {
        if (!group) return;
        const rawGroup = Alpine.raw(group);
        rawGroup.traverse(o => {
            if (o.isMesh && o.material) {
                // Subtle highlight: 0.15 (normal) -> 0.35 (active/dragging)
                o.material.opacity = active ? 0.35 : 0.15;
                o.material.transparent = true;
                o.material.needsUpdate = true;
            }
            if (o.isLine && o.material) {
                // Edge highlight: 0.6 (normal) -> 0.9 (active/dragging)
                o.material.opacity = active ? 0.9 : 0.6;
                o.material.needsUpdate = true;
            }
        });
        this.cadNeedsRender = true;
    },

    toggleSectioning() {
        this.toggleClippingPanel();
    },

    toggleClippingPanel() {
        this.initClipping();
        this.iges.clipping.panelOpen = !this.iges.clipping.panelOpen;

        if (this.iges.clipping.panelOpen) {
            this._updateClippingBounds();
            // Removed auto-activation of X-axis - let user choose which axis to enable
        } else {
            // If panel closed, maybe hide transform controls?
        }
    },

    _updateClippingBounds() {
        const box = new this.iges.THREE.Box3().setFromObject(this.iges.rootModel);
        const size = new this.iges.THREE.Vector3();
        box.getSize(size);
        const center = new this.iges.THREE.Vector3();
        box.getCenter(center);

        // Store global center for helper positioning
        this.iges.clipping._modelCenter = center;

        const updateAxisParams = (axis, centerVal, axisSize) => {
            const conf = this.iges.clipping[axis];
            // Extend range by 60% (0.8 * size) to allow dragging well beyond the model
            conf.min = Math.floor(centerVal - axisSize * 0.8);
            conf.max = Math.ceil(centerVal + axisSize * 0.8);
            if (conf.value === 0) conf.value = centerVal;
        };

        updateAxisParams('x', center.x, size.x);
        updateAxisParams('y', center.y, size.y);
        updateAxisParams('z', center.z, size.z);
    },

    toggleAxisClipping(axis) {
        const axisData = this.iges.clipping[axis];
        if (!axisData) return;

        axisData.enabled = !axisData.enabled;
        const { THREE } = this.iges;

        if (axisData.enabled) {
            const normal = this._getPlaneNormal(axis, axisData.flipped);
            axisData.plane = new THREE.Plane(normal, -axisData.value);
            axisData.showHelper = true;
            this._createPlaneHelper(axis);
        } else {
            axisData.plane = null;
            this._removePlaneHelper(axis);
        }

        this._updateGlobalClipping();
    },

    _getPlaneNormal(axis, flipped) {
        const THREE = this.iges.THREE;
        const dir = flipped ? -1 : 1;
        switch (axis) {
            case 'x': return new THREE.Vector3(dir, 0, 0);
            case 'y': return new THREE.Vector3(0, dir, 0);
            case 'z': return new THREE.Vector3(0, 0, dir);
        }
        return new THREE.Vector3(1, 0, 0);
    },

    updateAxisClipping(axis) {
        const conf = this.iges.clipping[axis];
        if (!conf || !conf.plane) return;

        const THREE = this.iges.THREE;
        const rawPlane = Alpine.raw(conf.plane);
        const normal = rawPlane.normal;

        let dot = 0;
        if (axis === 'x') dot = normal.x * conf.value;
        if (axis === 'y') dot = normal.y * conf.value;
        if (axis === 'z') dot = normal.z * conf.value;

        rawPlane.constant = -dot;

        if (conf.helper) {
            const rawHelper = Alpine.raw(conf.helper);
            const center = this.iges.clipping._modelCenter || new THREE.Vector3();

            // Reuse persistent vectors to avoid GC pressure during drag
            if (!this.iges.clipping._pos) this.iges.clipping._pos = new THREE.Vector3();
            if (!this.iges.clipping._up) this.iges.clipping._up = new THREE.Vector3(0, 0, 1);

            const pos = this.iges.clipping._pos;
            pos.copy(center);

            if (axis === 'x') pos.x = conf.value;
            if (axis === 'y') pos.y = conf.value;
            if (axis === 'z') pos.z = conf.value;
            rawHelper.position.copy(pos);

            rawHelper.quaternion.setFromUnitVectors(this.iges.clipping._up, normal);
        }

        this.cadNeedsRender = true;
    },

    flipAxis(axis) {
        const conf = this.iges.clipping[axis];
        if (!conf || !conf.plane) return;
        conf.flipped = !conf.flipped;
        const normal = this._getPlaneNormal(axis, conf.flipped);
        conf.plane.normal.copy(normal);
        this.updateAxisClipping(axis);
    },

    togglePlaneHelper(axis) {
        const conf = this.iges.clipping[axis];
        if (!conf) return;
        conf.showHelper = !conf.showHelper;
        if (conf.showHelper && conf.plane && !conf.helper) {
            this._createPlaneHelper(axis);
        } else if (!conf.showHelper && conf.helper) {
            this._removePlaneHelper(axis);
        }
        this.cadNeedsRender = true;
    },

    // ===== HELPERS =====

    async _createPlaneHelper(axis) {
        const conf = this.iges.clipping[axis];
        const { scene, THREE } = this.iges;
        if (!conf.plane || !scene || !THREE) return;

        this._removePlaneHelper(axis);
        this.iges.clipping.activeAxis = axis;

        // Colors
        const colors = { x: 0xef4444, y: 0x10b981, z: 0x3b82f6 };
        let color = colors[axis] || 0xef4444;

        const box = new THREE.Box3().setFromObject(this.iges.rootModel);
        const size = box.getSize(new THREE.Vector3());

        let w = 0, h = 0;
        if (axis === 'x') { w = size.z; h = size.y; }
        else if (axis === 'y') { w = size.x; h = size.z; }
        else if (axis === 'z') { w = size.x; h = size.y; }

        // Generous fit + 40% margin to ensure model is fully covered
        const helperW = w * 1.4;
        const helperH = h * 1.4;

        const group = new THREE.Group();
        group.name = `clipping_helper_${axis}`;
        group.userData.isClippingHelper = true;

        const faceGeo = new THREE.PlaneGeometry(helperW, helperH);
        const faceMat = new THREE.MeshBasicMaterial({
            color: color,
            transparent: true,
            opacity: 0.15,
            side: THREE.DoubleSide,
            depthWrite: false,
            polygonOffset: true,
            polygonOffsetFactor: -1.0,
            polygonOffsetUnits: -4.0
        });
        group.add(new THREE.Mesh(faceGeo, faceMat));

        const edgesGeo = new THREE.EdgesGeometry(faceGeo);
        const edgesMat = new THREE.LineBasicMaterial({ color: color, transparent: true, opacity: 0.6 });
        group.add(new THREE.LineSegments(edgesGeo, edgesMat));

        conf.helper = group;
        Alpine.raw(scene).add(group);
        this.updateAxisClipping(axis);
    },

    _removePlaneHelper(axis) {
        const conf = this.iges.clipping[axis];
        if (conf && conf.helper) {
            const rawScene = Alpine.raw(this.iges.scene);
            const rawHelper = Alpine.raw(conf.helper);
            if (rawScene && rawHelper) {
                rawScene.remove(rawHelper);
                rawHelper.traverse(o => {
                    if (o.geometry) o.geometry.dispose();
                    if (o.material) o.material.dispose();
                });
            }
            conf.helper = null;
        }
    },

    _updateGlobalClipping() {
        const { renderer } = this.iges;
        if (!renderer) return;

        const planes = [];
        const { x, y, z } = this.iges.clipping;
        if (x.enabled && x.plane) planes.push(Alpine.raw(x.plane));
        if (y.enabled && y.plane) planes.push(Alpine.raw(y.plane));
        if (z.enabled && z.plane) planes.push(Alpine.raw(z.plane));

        const rawRenderer = Alpine.raw(renderer);
        rawRenderer.clippingPlanes = [];
        rawRenderer.localClippingEnabled = true;

        this._updateMaterialsForClipping(planes);
        this.cadNeedsRender = true;
    },

    _updateMaterialsForClipping(planes) {
        const root = this.iges.rootModel;
        if (!root) return;

        Alpine.raw(root).traverse(child => {
            if (child.isMesh && child.material) {
                const mats = Array.isArray(child.material) ? child.material : [child.material];
                mats.forEach(m => {
                    m.clippingPlanes = planes;
                    m.clipShadows = true;
                    m.needsUpdate = true;
                });
            }
        });
    },

    decrementAxisValue(axis) {
        const conf = this.iges.clipping[axis];
        if (!conf) return;
        conf.value = Math.max(conf.min, conf.value - this.iges.clipping.step);
        this.updateAxisClipping(axis);
    },

    incrementAxisValue(axis) {
        const conf = this.iges.clipping[axis];
        if (!conf) return;
        conf.value = Math.min(conf.max, conf.value + this.iges.clipping.step);
        this.updateAxisClipping(axis);
    },

    setAxisValueDirect(axis, val) {
        const conf = this.iges.clipping[axis];
        if (!conf) return;
        conf.value = parseFloat(val);
        this.updateAxisClipping(axis);
    },

    resetAllClipping() {
        if (!this.iges.clipping) return;
        ['x', 'y', 'z'].forEach(axis => {
            const conf = this.iges.clipping[axis];
            conf.enabled = false;
            conf.value = 0;
            this._removePlaneHelper(axis);
            conf.plane = null;
        });
        this._updateGlobalClipping();
    },

    get hasActiveClipping() {
        if (!this.iges || !this.iges.clipping) return false;
        return (this.iges.clipping.x?.enabled) || (this.iges.clipping.y?.enabled) || (this.iges.clipping.z?.enabled);
    }
};
