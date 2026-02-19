
export const getCadMeasurementsState = () => ({
    // State is initialized in initCadViewer or similar, but defaults here for reference
    isMeasureActive: false,
    isMeasureListOpen: false,
    snapMarker: null,
});

export const cadMeasurementsMethods = {
    // Initialize measurement state in the main component
    initMeasurements() {
        if (!this.iges.measure) {
            this.iges.measure = {
                enabled: false,
                group: null,
                p1: null, p2: null, p3: null,
                mode: 'point',
                snap: { enabled: true, type: null },
                showLabels: true,
                hoverInstruction: 'Select Start Point',
                results: []
            };
            this._oriMats = new Map();
        } else if (this.iges.measure.showLabels === undefined) {
            this.iges.measure.showLabels = true;
        }
    },

    setMeasureMode(mode) {
        const M = this.iges.measure;
        M.mode = mode;
        M.p1 = null; M.p2 = null; M.p3 = null;

        switch (mode) {
            case 'point': M.hoverInstruction = 'Click 1st Point'; break;
            case 'edge': M.hoverInstruction = 'Click an Edge or 1st Point'; break;
            case 'angle': M.hoverInstruction = 'Click 1st Point (Start)'; break;
            case 'radius': M.hoverInstruction = 'Click 1st Point on Curve'; break;
            case 'face': M.hoverInstruction = 'Click a Planar Face'; break;
        }
    },

    toggleMeasure() {
        this.initMeasurements();
        const M = this.iges.measure;
        M.enabled = !M.enabled;

        this.isMeasureActive = M.enabled;
        this.isMeasureListOpen = M.enabled;

        if (M.enabled) {
            if (!M.group) {
                const THREE = this.iges.THREE;
                M.group = new THREE.Group();
                M.group.renderOrder = 999;
                this.iges.scene.add(M.group);
            }
            this._bindMeasureEvents(true);
            this.setMeasureMode(M.mode);
            // Ensure controls enabled
            if (this.iges.controls) this.iges.controls.enabled = true;
        } else {
            this._bindMeasureEvents(false);
            if (this.snapMarker) this.snapMarker.visible = false;
            M.p1 = null; M.p2 = null; M.p3 = null;
            if (this.iges.controls) this.iges.controls.enabled = true;
        }
    },

    clearMeasurements() {
        const M = this.iges.measure;
        const g = M.group;
        if (!g) return;

        (g.children || []).slice().forEach(ch => {
            if (ch.userData?.dispose) ch.userData.dispose();
            g.remove(ch);
        });
        g.clear();
        M.results = [];
        M.p1 = null; M.p2 = null; M.p3 = null;
        M.hoverInstruction = 'Measurements Cleared';
        setTimeout(() => this.setMeasureMode(M.mode), 1500);
        this.cadNeedsRender = true;
    },

    deleteMeasurement(index) {
        const M = this.iges.measure;
        if (index < 0 || index >= M.results.length) return;

        const res = M.results[index];
        if (res.objectUuid && M.group) {
            // Traverse to ensure we find it even if nested or changed
            let foundObj = null;
            M.group.traverse(obj => {
                if (obj.uuid === res.objectUuid || obj.name === res.objectUuid) {
                    foundObj = obj;
                }
            });

            if (foundObj) {
                if (foundObj.userData?.dispose) foundObj.userData.dispose();
                M.group.remove(foundObj);
                // console.log('[Measure] Deleted object:', res.objectUuid);
            }
        }
        M.results.splice(index, 1);
        this.cadNeedsRender = true;
    },

    toggleMeasureLabels() {
        const M = this.iges.measure;
        M.showLabels = !M.showLabels;

        // Immediate update of all labels
        if (M.group) {
            M.group.traverse(obj => {
                if (obj.userData?.update) obj.userData.update();
            });
        }
        this.cadNeedsRender = true;
    },

    focusMeasurement(res) {
        if (!res || !this.iges.controls || !this.iges.camera) return;

        let targetPos = null;
        if (res.center) targetPos = res.center;
        else if (res.pointA && res.pointB) {
            targetPos = res.pointA.clone().add(res.pointB).multiplyScalar(0.5);
        } else if (res.vertex) targetPos = res.vertex;
        else if (res.point) targetPos = res.point;

        if (targetPos) {
            const controls = Alpine.raw(this.iges.controls);
            const camera = Alpine.raw(this.iges.camera);

            // Move target and animate camera slightly for "impact"
            const dist = camera.position.distanceTo(controls.target);
            const dir = camera.position.clone().sub(controls.target).normalize();

            controls.target.copy(targetPos);
            camera.position.copy(targetPos).add(dir.multiplyScalar(dist));

            controls.update();
            this.cadNeedsRender = true;
            // console.log('[Measure] Focused on:', targetPos);
        }
    },

    _bindMeasureEvents(on) {
        const canvas = this.iges.renderer?.domElement;
        if (!canvas) return;

        if (on) {
            this._onMeasureClick = (ev) => {
                if (!this.iges.measure.enabled) return;
                ev.stopPropagation();

                const M = this.iges.measure;
                const pickResult = this._pickPointAdvanced(ev);
                if (!pickResult) return;

                const p = pickResult.point;

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
                } else if (M.mode === 'edge') {
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
                } else if (M.mode === 'angle') {
                    if (!M.p1) {
                        M.p1 = p;
                        M.hoverInstruction = 'Click Vertex (2nd Point)';
                    } else if (!M.p2) {
                        M.p2 = p;
                        M.hoverInstruction = 'Click End Point (3rd Point)';
                    } else {
                        this._drawAngleMeasurement(M.p1, M.p2, p);
                        M.p1 = null; M.p2 = null;
                        M.hoverInstruction = 'Click 1st Point (Start)';
                    }
                } else if (M.mode === 'radius') {
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
                        }
                        M.p1 = null; M.p2 = null; M.p3 = null;
                        M.hoverInstruction = 'Click 1st Point on Curve';
                    }
                } else if (M.mode === 'face') {
                    if (pickResult.hit && pickResult.hit.face) {
                        const result = this._calculateFaceArea(pickResult.hit.object, pickResult.hit.faceIndex);

                        // Check for duplicate using centroid and area
                        const isDuplicate = M.results.some(r =>
                            r.type === 'face' &&
                            r.meshUuid === pickResult.hit.object.uuid &&
                            Math.abs(r.area - result.area) < 0.1 && // Slightly more relaxed area check
                            (r.centroid ? r.centroid.distanceTo(result.centroid) < 1.0 : r.center.distanceTo(pickResult.point) < 10)
                        );

                        if (isDuplicate) {
                            M.hoverInstruction = 'Already measured';
                            setTimeout(() => this.setMeasureMode('face'), 2000);
                            return;
                        }

                        this._drawFaceAreaMeasurement(pickResult.point, result.area, pickResult.normal, pickResult.hit.object, result.vertices, result.centroid);
                        M.hoverInstruction = 'Click another Face';
                    }
                }
            };

            let moveRequest = null;
            this._onMeasureMove = (ev) => {
                const M = this.iges.measure;
                if (!M || !M.enabled) return;

                if (moveRequest) cancelAnimationFrame(moveRequest);
                moveRequest = requestAnimationFrame(() => {
                    const pick = this._pickPointAdvanced(ev);
                    canvas.style.cursor = pick ? 'pointer' : 'default';
                    moveRequest = null;
                });
            };

            this._onMeasureRightClick = (ev) => {
                ev.preventDefault();
                const M = this.iges.measure;
                M.p1 = null; M.p2 = null; M.p3 = null;
                this.setMeasureMode(M.mode);
                if (this.snapMarker) this.snapMarker.visible = false;
                this.cadNeedsRender = true;
            };

            canvas.addEventListener('click', this._onMeasureClick);
            canvas.addEventListener('mousemove', this._onMeasureMove);
            canvas.addEventListener('contextmenu', this._onMeasureRightClick);

        } else {
            if (this._onMeasureClick) canvas.removeEventListener('click', this._onMeasureClick);
            if (this._onMeasureMove) canvas.removeEventListener('mousemove', this._onMeasureMove);
            if (this._onMeasureRightClick) canvas.removeEventListener('contextmenu', this._onMeasureRightClick);
            if (this.snapMarker) this.snapMarker.visible = false;
            canvas.style.cursor = 'default';
        }
    },

    _pickPointAdvanced(ev) {
        const { THREE, camera, rootModel, renderer } = this.iges;
        if (!renderer || !rootModel || !camera) return null;

        const rect = renderer.domElement.getBoundingClientRect();

        // Reuse Vector2 and Raycaster
        if (!this.iges._mouse) this.iges._mouse = new THREE.Vector2();
        if (!this.iges._raycaster) {
            this.iges._raycaster = new THREE.Raycaster();
            this.iges._raycaster.firstHitOnly = true;
        }

        const mouse = this.iges._mouse;
        mouse.set(
            ((ev.clientX - rect.left) / rect.width) * 2 - 1,
            -((ev.clientY - rect.top) / rect.height) * 2 + 1
        );

        const raycaster = this.iges._raycaster;
        raycaster.setFromCamera(mouse, camera);

        const hits = raycaster.intersectObjects(rootModel.children, true);
        if (!hits.length) {
            if (this.snapMarker && this.snapMarker.visible) {
                this.snapMarker.visible = false;
                this.cadNeedsRender = true;
            }
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

            const vA = new THREE.Vector3().fromBufferAttribute(pos, hit.face.a).applyMatrix4(mesh.matrixWorld);
            const vB = new THREE.Vector3().fromBufferAttribute(pos, hit.face.b).applyMatrix4(mesh.matrixWorld);
            const vC = new THREE.Vector3().fromBufferAttribute(pos, hit.face.c).applyMatrix4(mesh.matrixWorld);

            faceNormal = hit.face.normal.clone().transformDirection(mesh.matrixWorld);

            const midAB = vA.clone().add(vB).multiplyScalar(0.5);
            const midBC = vB.clone().add(vC).multiplyScalar(0.5);
            const midCA = vC.clone().add(vA).multiplyScalar(0.5);

            const snapThreshold = hit.distance * 0.05;
            const edgeSnapThreshold = hit.distance * 0.03;

            let closest = null;
            let minDist = snapThreshold;

            const checkSnap = (pt, type) => {
                const d = hit.point.distanceTo(pt);
                if (d < minDist) {
                    closest = pt; minDist = d; snapType = type;
                    return true;
                }
                return false;
            };

            checkSnap(vA, 'vertex');
            checkSnap(vB, 'vertex');
            checkSnap(vC, 'vertex');

            if (!closest) {
                if (hit.point.distanceTo(midAB) < edgeSnapThreshold) { closest = midAB; snapType = 'midpoint'; edgeInfo = { start: vA, end: vB }; }
                else if (hit.point.distanceTo(midBC) < edgeSnapThreshold) { closest = midBC; snapType = 'midpoint'; edgeInfo = { start: vB, end: vC }; }
                else if (hit.point.distanceTo(midCA) < edgeSnapThreshold) { closest = midCA; snapType = 'midpoint'; edgeInfo = { start: vC, end: vA }; }
            }

            if (!closest) {
                // Project on edges
                const edges = [{ s: vA, e: vB }, { s: vB, e: vC }, { s: vC, e: vA }];
                for (const e of edges) {
                    const proj = this._projectPointOnLine(hit.point, e.s, e.e);
                    if (proj && hit.point.distanceTo(proj) < edgeSnapThreshold) {
                        closest = proj; snapType = 'edge'; edgeInfo = { start: e.s, end: e.e };
                        break;
                    }
                }
            }

            if (closest) finalPoint = closest;
        }

        if (this.iges.measure.snap.enabled) {
            this._updateSnapMarkerAdvanced(finalPoint, snapType);
        } else {
            snapType = 'surface';
        }

        return { point: finalPoint, snapType, edge: edgeInfo, normal: faceNormal, hit: hit };
    },

    _projectPointOnLine(point, lineStart, lineEnd) {
        const line = lineEnd.clone().sub(lineStart);
        const len = line.length();
        if (len === 0) return null;
        const dir = line.normalize();
        const t = point.clone().sub(lineStart).dot(dir);
        if (t < 0 || t > len) return null;
        return lineStart.clone().add(dir.multiplyScalar(t));
    },

    _updateSnapMarkerAdvanced(position, snapType) {
        const { THREE, scene, camera } = this.iges;
        if (!this.snapMarker) {
            const geom = new THREE.SphereGeometry(1, 16, 16);
            const mat = new THREE.MeshBasicMaterial({ color: 0xff0000, transparent: true, opacity: 0.7, depthTest: false });
            this.snapMarker = new THREE.Mesh(geom, mat);
            this.snapMarker.renderOrder = 999;
            scene.add(this.snapMarker);
        }

        // Optimization: skip if position is virtually identical to prevent redundant renders
        if (this.snapMarker.visible && this.snapMarker.position.distanceToSquared(position) < 0.0001) {
            return;
        }

        const colors = { vertex: 0xff0000, edge: 0x00ff00, midpoint: 0xffff00, surface: 0x0088ff };
        this.snapMarker.material.color.setHex(colors[snapType] || 0xff0000);
        this.snapMarker.visible = true;
        this.snapMarker.position.copy(position);

        let scale;
        if (camera.isOrthographicCamera) {
            const h = (camera.top - camera.bottom) / camera.zoom;
            scale = h * 0.008;
        } else {
            scale = camera.position.distanceTo(position) * 0.007;
        }
        this.snapMarker.scale.set(scale, scale, scale);
        this.cadNeedsRender = true;
    },

    _drawMeasurement(a, b, measureType) {
        this.cadNeedsRender = true;
        const THREE = this.iges.THREE;
        const group = new THREE.Group();
        const uId = THREE.MathUtils.generateUUID();
        group.name = uId;

        const distance = a.distanceTo(b);
        const dX = Math.abs(b.x - a.x);
        const dY = Math.abs(b.y - a.y);
        const dZ = Math.abs(b.z - a.z);

        this.iges.measure.results.push({
            id: uId, distance, deltaX: dX, deltaY: dY, deltaZ: dZ,
            pointA: a.clone(), pointB: b.clone(), type: measureType, objectUuid: uId,
            center: a.clone().add(b).multiplyScalar(0.5)
        });

        const matWhite = new THREE.LineBasicMaterial({ color: 0xffffff, depthTest: false });
        const line = new THREE.Line(new THREE.BufferGeometry().setFromPoints([a, b]), matWhite);
        line.renderOrder = 999;
        group.add(line);

        // Delta lines (Red/Green/Blue)
        const addDelta = (pts, col) => {
            const l = new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts),
                new THREE.LineDashedMaterial({ color: col, dashSize: 3, gapSize: 2, depthTest: false }));
            l.computeLineDistances(); l.renderOrder = 998; group.add(l);
        };
        if (dX > 0.01) addDelta([a, new THREE.Vector3(b.x, a.y, a.z)], 0xff4444);
        if (dY > 0.01) addDelta([new THREE.Vector3(b.x, a.y, a.z), new THREE.Vector3(b.x, b.y, a.z)], 0x44ff44);
        if (dZ > 0.01) addDelta([new THREE.Vector3(b.x, b.y, a.z), b], 0x4444ff);

        // Spheres
        const sGeom = new THREE.SphereGeometry(Math.max(0.5, distance / 100), 16, 16);
        const sMat = new THREE.MeshBasicMaterial({ color: 0xff0000, depthTest: false });
        const s1 = new THREE.Mesh(sGeom, sMat); s1.position.copy(a); group.add(s1);
        const s2 = new THREE.Mesh(sGeom, sMat); s2.position.copy(b); group.add(s2);

        // Label
        const lbl = this._createLabel(group);
        group.userData.update = () => {
            this._updateLabelPosition(lbl, a.clone().add(b).multiplyScalar(0.5));
            lbl.innerHTML = `
                <div class="text-blue-400 font-bold mb-1"><i class="fa-solid fa-ruler mr-1"></i>${distance.toFixed(2)} mm</div>
                 <div class="grid grid-cols-3 gap-1 text-[9px] opacity-80">
                    <span class="text-red-400">ΔX: ${dX.toFixed(2)}</span>
                    <span class="text-green-400">ΔY: ${dY.toFixed(2)}</span>
                    <span class="text-blue-400">ΔZ: ${dZ.toFixed(2)}</span>
                </div>`;
        };
        group.userData.dispose = () => {
            if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
        };
        group.userData.update();
        this.iges.measure.group.add(group);
    },

    _drawAngleMeasurement(p1, vertex, p3) {
        this.cadNeedsRender = true;
        const THREE = this.iges.THREE;
        const group = new THREE.Group();
        const uId = THREE.MathUtils.generateUUID();
        group.name = uId;

        const v1 = p1.clone().sub(vertex).normalize();
        const v2 = p3.clone().sub(vertex).normalize();
        const dot = v1.dot(v2);
        const angleDeg = Math.acos(Math.max(-1, Math.min(1, dot))) * (180 / Math.PI);
        const dist3 = p1.distanceTo(p3);

        this.iges.measure.results.push({
            id: uId, angle: angleDeg, distance: dist3,
            vertex: vertex.clone(), pointA: p1.clone(), pointB: p3.clone(),
            type: 'angle', objectUuid: uId, center: vertex.clone()
        });

        const matY = new THREE.LineBasicMaterial({ color: 0xffff00, depthTest: false });
        group.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([vertex, p1]), matY));
        group.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([vertex, p3]), matY));

        // Arc
        const arcPoints = [];
        const r = Math.min(vertex.distanceTo(p1), vertex.distanceTo(p3)) * 0.3;
        const steps = 32;
        const axis = v1.clone().cross(v2).normalize();
        const angleRad = angleDeg * Math.PI / 180;

        for (let i = 0; i <= steps; i++) {
            const t = i / steps;
            const dir = v1.clone().applyAxisAngle(axis, t * angleRad);
            arcPoints.push(vertex.clone().add(dir.multiplyScalar(r)));
        }
        group.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(arcPoints),
            new THREE.LineBasicMaterial({ color: 0xff00ff, depthTest: false })));

        // Label
        const lbl = this._createLabel(group, 'rgba(255, 0, 255, 0.5)');
        group.userData.update = () => {
            const labelPos = vertex.clone().add(v1.clone().add(v2).normalize().multiplyScalar(r * 1.5));
            this._updateLabelPosition(lbl, labelPos);
            lbl.innerHTML = `<div class="text-purple-400 font-bold mb-1">Angle: ${angleDeg.toFixed(2)}°</div>`;
        };
        group.userData.dispose = () => {
            if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
        };
        group.userData.update();
        this.iges.measure.group.add(group);
    },

    _drawRadiusMeasurement(circle, p1, p2, p3) {
        this.cadNeedsRender = true;
        const { center, radius, normal } = circle;
        const THREE = this.iges.THREE;
        const group = new THREE.Group();
        const uId = THREE.MathUtils.generateUUID();
        group.name = uId;

        this.iges.measure.results.push({
            id: uId, type: 'radius', radius, diameter: radius * 2, center: center.clone(), objectUuid: uId
        });

        const curve = new THREE.EllipseCurve(0, 0, radius, radius, 0, 2 * Math.PI, false, 0);
        const pts = curve.getPoints(64);
        const cMesh = new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts),
            new THREE.LineBasicMaterial({ color: 0x00ff00, depthTest: false }));

        cMesh.position.copy(center);
        cMesh.lookAt(center.clone().add(normal)); // Simple orientation
        // Better orientation: quaternion from Z to normal
        cMesh.setRotationFromQuaternion(new THREE.Quaternion().setFromUnitVectors(new THREE.Vector3(0, 0, 1), normal));
        group.add(cMesh);

        // Lines to points
        const lGeom = new THREE.BufferGeometry().setFromPoints([p1, center, p2, center, p3]);
        const l = new THREE.LineSegments(lGeom, new THREE.LineDashedMaterial({ color: 0x00ff00, dashSize: 0.5, gapSize: 0.5 }));
        l.computeLineDistances();
        group.add(l);

        // Label
        const lbl = this._createLabel(group, 'rgba(0, 255, 0, 0.5)');
        group.userData.update = () => {
            this._updateLabelPosition(lbl, center);
            lbl.innerHTML = `<div class="text-green-400 font-bold mb-1">Radius: ${radius.toFixed(2)}</div>
                             <div class="text-teal-400">Diameter: ${(radius * 2).toFixed(2)}</div>`;
        };
        group.userData.dispose = () => {
            if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
        };
        group.userData.update();
        this.iges.measure.group.add(group);
    },

    _drawFaceAreaMeasurement(center, area, normal, mesh, surfaceVertices, centroid) {
        this.cadNeedsRender = true;
        const THREE = this.iges.THREE;
        const uId = THREE.MathUtils.generateUUID();

        const group = new THREE.Group();
        group.name = uId;

        // Create a localized highlight mesh for the surface
        if (surfaceVertices && surfaceVertices.length > 0) {
            const hGeom = new THREE.BufferGeometry().setFromPoints(surfaceVertices);
            const hMat = new THREE.MeshBasicMaterial({
                color: 0xff0000,
                opacity: 0.4,
                transparent: true,
                depthTest: true,
                side: THREE.DoubleSide,
                polygonOffset: true,
                polygonOffsetFactor: -1,
                polygonOffsetUnits: -1
            });
            const hMesh = new THREE.Mesh(hGeom, hMat);
            hMesh.renderOrder = 1000;
            group.add(hMesh);
        }

        this.iges.measure.results.push({
            id: uId, type: 'face', area, meshUuid: mesh ? mesh.uuid : null, objectUuid: uId,
            center: center.clone(), centroid: centroid ? centroid.clone() : center.clone()
        });

        const lbl = this._createLabel(group, 'rgba(255, 0, 0, 0.5)');
        group.userData.update = () => {
            this._updateLabelPosition(lbl, center);
            lbl.innerHTML = `<i class="fa-solid fa-vector-square text-red-500 mr-1"></i> Area: ${area.toFixed(2)} mm²`;
        };
        group.userData.dispose = () => {
            if (lbl.parentNode) lbl.parentNode.removeChild(lbl);
        };
        group.userData.update();
        this.iges.measure.group.add(group);
    },

    _calculateCircleFrom3Points(p1, p2, p3) {
        const m1 = p1.clone().add(p2).multiplyScalar(0.5);
        const m2 = p2.clone().add(p3).multiplyScalar(0.5);
        const v12 = p2.clone().sub(p1);
        const v23 = p3.clone().sub(p2);
        const normal = v12.clone().cross(v23).normalize();

        const dir1 = v12.clone().cross(normal).normalize();
        const dir2 = v23.clone().cross(normal).normalize();

        const cross = dir1.clone().cross(dir2);
        const denom = cross.lengthSq();
        if (denom < 1e-6) return null;

        const t = m2.clone().sub(m1).cross(dir2).dot(cross) / denom;
        const center = m1.clone().add(dir1.multiplyScalar(t));
        return { center, radius: center.distanceTo(p1), normal };
    },

    _calculateFaceArea(mesh, faceIdx) {
        const THREE = this.iges.THREE;
        const geom = mesh.geometry;
        const pos = geom.attributes.position;
        const idx = geom.index;

        const getV = (i) => new THREE.Vector3().fromBufferAttribute(pos, idx.getX(i)).applyMatrix4(mesh.matrixWorld);
        const vA = getV(faceIdx * 3), vB = getV(faceIdx * 3 + 1), vC = getV(faceIdx * 3 + 2);
        const norm = vB.clone().sub(vA).cross(vC.clone().sub(vA)).normalize();

        let area = 0;
        const surfaceVertices = [];
        const localNorm = norm.clone().transformDirection(mesh.matrixWorld.clone().invert()).normalize();
        const centroid = new THREE.Vector3(0, 0, 0);
        let count = 0;

        for (let i = 0; i < idx.count; i += 3) {
            const p1 = new THREE.Vector3().fromBufferAttribute(pos, idx.getX(i));
            const p2 = new THREE.Vector3().fromBufferAttribute(pos, idx.getX(i + 1));
            const p3 = new THREE.Vector3().fromBufferAttribute(pos, idx.getX(i + 2));
            const fn = p2.clone().sub(p1).cross(p3.clone().sub(p1)).normalize();

            if (fn.dot(localNorm) > 0.95) {
                const w1 = p1.clone().applyMatrix4(mesh.matrixWorld);
                const w2 = p2.clone().applyMatrix4(mesh.matrixWorld);
                const w3 = p3.clone().applyMatrix4(mesh.matrixWorld);

                area += w2.clone().sub(w1).cross(w3.clone().sub(w1)).length() * 0.5;
                surfaceVertices.push(w1, w2, w3);
                centroid.add(w1).add(w2).add(w3);
                count++;
            }
        }
        if (count > 0) centroid.divideScalar(count * 3);
        return { area, vertices: surfaceVertices, centroid };
    },

    _createLabel(group, borderColor = 'rgba(255, 255, 255, 0.2)') {
        const wrap = this.$refs.cadContainer;
        const lbl = document.createElement('div');
        lbl.className = 'measure-label-detailed';
        Object.assign(lbl.style, {
            position: 'absolute', left: '0', top: '0', padding: '4px 8px',
            background: 'rgba(0,0,0,0.65)', color: '#fff', borderRadius: '5px',
            fontSize: '9px', fontFamily: 'Inter, sans-serif', pointerEvents: 'none',
            zIndex: '10', border: `1px solid ${borderColor}`, backdropFilter: 'blur(3px)',
            boxShadow: '0 4px 12px rgba(0,0,0,0.2)', transition: 'opacity 0.2s ease-in-out'
        });
        wrap.appendChild(lbl);
        return lbl;
    },

    _updateLabelPosition(lbl, worldPos) {
        if (!this.iges.camera || !this.$refs.cadContainer) return;

        // Global visibility check
        if (!this.iges.measure.showLabels) {
            lbl.style.opacity = '0';
            lbl.style.pointerEvents = 'none';
            return;
        } else {
            lbl.style.opacity = '1';
        }

        const pos = worldPos.clone();
        pos.project(this.iges.camera);
        const w = this.$refs.cadContainer.clientWidth;
        const h = this.$refs.cadContainer.clientHeight;
        const x = (pos.x * 0.5 + 0.5) * w;
        const y = (-pos.y * 0.5 + 0.5) * h;
        lbl.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
    }
};
