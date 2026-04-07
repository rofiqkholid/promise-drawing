export const cadExplodeState = {
    // State is initialized in initExplode
};

export const cadExplodeMethods = {
    initExplode() {
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
    },

    toggleExplodedPanel() {
        this.initExplode();
        if (!this.iges.exploded.enabled) {
            this.iges.exploded.enabled = true;
            if (!this.iges.exploded.factor) this.iges.exploded.factor = 0.5;
            this.updateExplodeFactor(this.iges.exploded.factor);
        }
        this.iges.exploded.panelOpen = !this.iges.exploded.panelOpen;
    },

    toggleExplodedView() {
        this.iges.exploded.enabled = !this.iges.exploded.enabled;
        if (this.iges.exploded.enabled) {
            if (!this.iges.exploded.factor) this.iges.exploded.factor = 0.5;
            this.updateExplodeFactor(this.iges.exploded.factor);
            this.iges.exploded.panelOpen = true;
        } else {
            this.updateExplodeFactor(0);
            this.iges.exploded.panelOpen = false;
        }
        this.cadNeedsRender = true;
    },

    updateExplodeFactor(val) {
        const factor = parseFloat(val);
        this.iges.exploded.factor = factor;
        const { rootModel, THREE } = this.iges;
        if (!rootModel || !THREE) return;

        if (!this.iges.exploded.originalPositions) {
            this.iges.exploded.originalPositions = new Map();
            this.iges.exploded.meshes = []; // Cache list of meshes for faster updates
            const globalBox = new THREE.Box3().setFromObject(rootModel);
            const globalCenter = new THREE.Vector3();
            globalBox.getCenter(globalCenter);

            rootModel.traverse(child => {
                if (child.isMesh) {
                    this.iges.exploded.originalPositions.set(child.uuid, child.position.clone());
                    this.iges.exploded.meshes.push(child);

                    if (!child.geometry.boundingBox) child.geometry.computeBoundingBox();
                    const meshBox = child.geometry.boundingBox.clone();
                    const meshCenter = new THREE.Vector3();
                    meshBox.getCenter(meshCenter);

                    const worldMatrix = child.matrixWorld.clone();
                    meshCenter.applyMatrix4(worldMatrix);

                    const dir = new THREE.Vector3().subVectors(meshCenter, globalCenter);
                    if (dir.length() < 0.01) {
                        dir.set(Math.random() - 0.5, Math.random() - 0.5, Math.random() - 0.5);
                    }
                    dir.normalize();
                    child.userData.explodeDir = dir;
                }
            });
        }

        const scalar = factor * 500;
        const meshes = this.iges.exploded.meshes || [];
        const offset = new THREE.Vector3(); // Reuse vector for speed

        for (let i = 0; i < meshes.length; i++) {
            const child = meshes[i];
            const dir = child.userData.explodeDir;
            if (dir) {
                const orig = this.iges.exploded.originalPositions.get(child.uuid);
                if (orig) {
                    offset.copy(dir).multiplyScalar(scalar);
                    child.position.copy(orig).add(offset);
                }
            }
        }

        this.cadNeedsRender = true;
    }
};
