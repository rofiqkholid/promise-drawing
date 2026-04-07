
export const getStampState = () => ({
    stampConfig: {
        original: 'bottom-left',
        copy: 'bottom-center',
        obsolete: 'bottom-right'
    },
    stampDefaults: {
        original: 'bottom-left',
        copy: 'bottom-center',
        obsolete: 'bottom-right'
    },
    isStampBurned: false,
    applyToAllProcessing: false,
});

export const stampMethods = {
    initStamps() {
        // console.log('[FileViewer] Stamps Initialized');
        // Watch for stamp config changes
        this.$watch('stampConfig', (newConfig) => {
            if (this.isLoadingStampConfig) return;
            this.onStampChange();
        }, { deep: true });
    },

    onStampChange() {
        // Build map for quick lookup
        const config = this.stampConfig;

        // Save locally first
        if (this.selectedFile) {
            this.saveStampConfigForCurrent();
        }

        // Trigger re-render or update depending on viewer type
        if (this.selectedFile && this.isCad(this.selectedFile.name)) {
            // CAD uses HTML overlay, just trigger update
            // Vue/Alpine reactivity handles this automatically
        } else {
            // Image/PDF might need canvas redraw
            if (this.redrawCanvas) this.redrawCanvas();
        }

        // Persist to Database (Debounced)
        if (this.selectedFile && this.selectedFile.id) {
            clearTimeout(this._saveStampTimeout);
            this._saveStampTimeout = setTimeout(() => {
                this.persistStampConfigToDb(this.selectedFile.id, config);
            }, 1000);
        }
    },

    async persistStampConfigToDb(fileId, config) {
        try {
            const payload = {
                ori_position: this.positionKeyToInt(config.original),
                copy_position: this.positionKeyToInt(config.copy),
                obslt_position: this.positionKeyToInt(config.obsolete)
            };

            const response = await fetch(`/approvals/files/${fileId}/stamp-positions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                console.error('[FileViewer] Failed to save stamp config', await response.text());
                // Optional: Revert UI or show notification
            } else {
                // console.log('[FileViewer] Stamp config saved successfully');
                // Update local file object to keep it in sync
                if (this.selectedFile && this.selectedFile.id === fileId) {
                    this.selectedFile.ori_position = payload.ori_position;
                    this.selectedFile.copy_position = payload.copy_position;
                    this.selectedFile.obslt_position = payload.obslt_position;
                }
            }
        } catch (e) {
            console.error('[FileViewer] Error saving stamp config:', e);
        }
    },

    // Stamp Content Generators
    stampTopLine(type) {
        if (type === 'copy' && this.stampCopyTopLine) {
            return this.stampCopyTopLine;
        }

        if (!this.pkg || !this.pkg.stamp) return '';
        const s = this.pkg.stamp;

        if (type === 'original') {
            const date = s.receipt_date || s.upload_date || '';
            return date ? `DATE RECEIVED : ${this.formatStampDate(date)}` : '';
        }

        if (type === 'copy') {
            const now = new Date();
            const dateStr = this.formatStampDate(now.toISOString().split('T')[0]);
            const timeStr = now.toTimeString().split(' ')[0];
            const deptCode = this.userDeptCode || '--';
            return `SAI / ${deptCode} / ${dateStr} ${timeStr}`;
        }

        if (type === 'obsolete') {
            const info = s.obsolete_info || {};
            const date = info.date_text || s.obsolete_date || s.upload_date || '';
            return date ? `DATE : ${this.formatStampDate(date)}` : '';
        }
        return '';
    },

    stampCenterOriginal() {
        return 'SAI-DRAWING ORIGINAL';
    },

    stampCenterCopy() {
        const status = (this.pkg?.metadata?.project_status_name || '').toLowerCase();
        if (status === 'feasibility study') {
            return 'SAI-DRAWING UNCONTROLLED COPY';
        }
        return 'SAI-DRAWING CONTROLLED COPY';
    },

    stampCenterObsolete() {
        return 'SAI-DRAWING OBSOLETE';
    },

    stampBottomLine(type) {
        if (!this.pkg || !this.pkg.stamp) return '';
        const s = this.pkg.stamp;

        if (type === 'copy') {
            const userName = this.userName || '--';
            return `Downloaded By ${userName}`;
        }

        if (type === 'original') {
            const date = s.upload_date || '';
            return date ? `Date Uploaded : ${this.formatStampDate(date)}` : '';
        }

        if (type === 'obsolete') {
            const info = s.obsolete_info || {};
            const name = info.name || '';
            const dept = info.dept || '';
            if (name && dept) return `By : ${name} / ${dept}`;
            return (name || dept) ? `By : ${name || dept}` : '';
        }
        return '';
    },

    formatStampDate(dateStr) {
        if (!dateStr) return '';

        // Handle cases where dateStr is already formatted or invalid
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;

        const day = d.getDate();
        const year = d.getFullYear();
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[d.getMonth()];

        let suffix = 'th';
        if (day % 100 < 11 || day % 100 > 13) {
            switch (day % 10) {
                case 1: suffix = 'st'; break;
                case 2: suffix = 'nd'; break;
                case 3: suffix = 'rd'; break;
            }
        }

        const superscripts = { 'st': 'ˢᵗ', 'nd': 'ⁿᵈ', 'rd': 'ʳᵈ', 'th': 'ᵗʰ' };
        // Format: Feb.13ᵗʰ 2026
        return `${month}.${day}${superscripts[suffix]} ${year}`;
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

    // Canvas Drawing Logic
    _burnStampsToCanvas(ctx, width, height) {
        if (!this.pkg || !this.pkg.stamp) return;

        const stampTypes = ['original', 'copy'];
        if (this.pkg.stamp.is_obsolete) stampTypes.push('obsolete');

        stampTypes.forEach(type => {
            const position = this.stampConfig[type] || this.stampDefaults[type];
            this._drawSingleStamp(ctx, width, height, type, position);
        });

        // Mark as burned
        this.$nextTick(() => {
            this.isStampBurned = true;
        });
    },

    _drawSingleStamp(ctx, w, h, type, position) {
        // 1. Scale & Size
        let stampW = Math.max(w * 0.15, 250);
        stampW = Math.min(stampW, w * 0.4);
        const stampH = stampW * 0.35;
        const margin = Math.min(w, h) * 0.04;

        // 2. Coordinates
        let x, y;
        const [vPos, hPos] = position.split('-');

        if (hPos === 'left') x = margin;
        else if (hPos === 'center') x = (w - stampW) / 2;
        else x = w - stampW - margin;

        if (vPos === 'top') y = margin;
        else if (vPos === 'center') y = (h - stampH) / 2;
        else y = h - stampH - margin;

        // 3. Styles
        const isEng = true; // Assuming True for now or pass prop
        const isObsolete = !!(this.pkg?.stamp?.is_obsolete);

        let color = '#4b5563';
        let borderColor = '#6b7280';

        if (type === 'original') {
            // Original stamp is now always gray
        } else if (type === 'copy') {
            if (!isObsolete) {
                color = '#1d4ed8'; borderColor = '#2563eb';
            }
        } else if (type === 'obsolete') {
            color = '#b91c1c'; borderColor = '#dc2626';
        }

        // Helper
        const hexToRgba = (hex, alpha) => {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        };

        ctx.save();

        // Background
        ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.fillRect(x, y, stampW, stampH);

        // Border
        ctx.strokeStyle = hexToRgba(borderColor, 0.4);
        ctx.lineWidth = Math.max(2, stampW * 0.008);
        ctx.strokeRect(x, y, stampW, stampH);

        // Text Content
        let topLineText = '';
        let centerText = '';
        let bottomLineText = '';

        if (type === 'original') {
            topLineText = this.stampTopLine('original');
            centerText = this.stampCenterOriginal();
            bottomLineText = this.stampBottomLine('original');
        } else if (type === 'copy') {
            topLineText = this.stampTopLine('copy');
            centerText = this.stampCenterCopy();
            bottomLineText = this.stampBottomLine('copy');
        } else if (type === 'obsolete') {
            topLineText = this.stampTopLine('obsolete');
            centerText = this.stampCenterObsolete();
        }

        ctx.fillStyle = hexToRgba(color, 0.4);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        const rowH = stampH / 3;
        const cx = x + stampW / 2;
        const textMaxWidth = stampW * 0.95;

        // Top
        ctx.font = `600 ${rowH * 0.45}px sans-serif`;
        ctx.fillText(topLineText, cx, y + rowH * 0.5, textMaxWidth);

        // Sep 1
        ctx.lineWidth = Math.max(1, stampW * 0.005);
        ctx.beginPath();
        ctx.moveTo(x, y + rowH);
        ctx.lineTo(x + stampW, y + rowH);
        ctx.stroke();

        // Center
        ctx.font = `800 ${rowH * 0.7}px "Arial Narrow", sans-serif`;
        ctx.fillText(centerText, cx, y + rowH * 1.5, textMaxWidth);

        // Sep 2
        ctx.beginPath();
        ctx.moveTo(x, y + rowH * 2);
        ctx.lineTo(x + stampW, y + rowH * 2);
        ctx.stroke();

        // Bottom
        if (type === 'obsolete') {
            const name = this.obsoleteName();
            const dept = this.obsoleteDept();
            const splitRatio = 0.65;
            const splitX = stampW * splitRatio;
            const leftCenter = splitX / 2;
            const rightCenter = splitX + ((stampW - splitX) / 2);

            ctx.font = `600 ${rowH * 0.4}px sans-serif`;
            ctx.fillText(`Name : ${name}`, x + leftCenter, y + rowH * 2.5, splitX * 0.95);
            ctx.fillText(`Dept. : ${dept}`, x + rightCenter, y + rowH * 2.5, (stampW - splitX) * 0.95);

            ctx.beginPath();
            ctx.moveTo(x + splitX, y + rowH * 2);
            ctx.lineTo(x + splitX, y + stampH);
            ctx.stroke();
        } else {
            ctx.font = `600 ${rowH * 0.45}px sans-serif`;
            ctx.fillText(bottomLineText, cx, y + rowH * 2.5, textMaxWidth);
        }

    },

    // Note: cleanupInteract is handled by maskMethods

    loadStampConfigFor(file) {
        this.isLoadingStampConfig = true; // Prevent watcher from triggering

        const key = (file?.id ?? file?.name ?? '').toString();
        if (!key) {
            this.stampConfig = { ...this.stampDefaults };
            this.isLoadingStampConfig = false;
            return;
        }

        if (!this.stampPerFile) this.stampPerFile = {};

        // Helper to safely parse position with default fallback
        const parsePos = (val, def) => {
            if (val === null || val === undefined) return def;
            const parsed = parseInt(val, 10);
            return isNaN(parsed) ? def : parsed;
        };

        // Always re-evaluate to ensure we get latest data from file object
        // 0=bottom-left, 1=bottom-center, 2=bottom-right
        const ori = parsePos(file.ori_position, 0);
        const cpy = parsePos(file.copy_position, 1);
        const obs = parsePos(file.obslt_position, 2);

        this.stampPerFile[key] = {
            original: this.positionIntToKey(ori),
            copy: this.positionIntToKey(cpy),
            obsolete: this.positionIntToKey(obs),
        };

        this.stampConfig = { ...this.stampPerFile[key] };
        this.isLoadingStampConfig = true;

        // Use nextTick to allow Alpine to settle the new stampConfig before enabling the watcher
        this.$nextTick(() => {
            this.$nextTick(() => {
                this.isLoadingStampConfig = false;
            });
        });
    },

    saveStampConfigForCurrent() {
        const key = (this.selectedFile?.id ?? this.selectedFile?.name ?? '').toString();
        if (!key) return;

        if (!this.stampPerFile) this.stampPerFile = {};

        // Save to stampPerFile
        this.stampPerFile[key] = { ...this.stampConfig };

        // Force Alpine.js reactivity by creating new object reference
        this.stampConfig = { ...this.stampConfig };
    },

    positionIntToKey(int) {
        const val = parseInt(int, 10);
        switch (isNaN(val) ? 0 : val) {
            case 0: return 'bottom-left';
            case 1: return 'bottom-center';
            case 2: return 'bottom-right';
            case 3: return 'top-left';
            case 4: return 'top-center';
            case 5: return 'top-right';
            default: return 'bottom-left';
        }
    },

    positionKeyToInt(key) {
        switch (key) {
            case 'bottom-left': return 0;
            case 'bottom-center': return 1;
            case 'bottom-right': return 2;
            case 'top-left': return 3;
            case 'top-center': return 4;
            case 'top-right': return 5;
            default: return 0;
        }
    }
};
