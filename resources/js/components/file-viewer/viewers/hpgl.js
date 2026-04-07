
export const getHpglState = () => ({
    hpglLoading: false,
    hpglError: '',
    hpglDrawingBounds: { left: 0, top: 0, width: 0, height: 0 },
});

export const hpglMethods = {
    isHpgl(filename) {
        if (!filename) return false;
        const ext = filename.split('.').pop().toLowerCase();
        return ['hpgl', 'plt', 'hpg'].includes(ext);
    },

    async loadHpgl(file) {
        this.hpglLoading = true;
        this.hpglError = '';
        this.loadingProgress = 0;
        this.loadingStatus = 'Downloading HPGL...';

        if (this.initBlocksForFile) this.initBlocksForFile(file);

        try {
            const response = await fetch(file.url, {
                cache: 'no-store',
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Failed to fetch HPGL file');

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
                    this.loadingProgress = Math.round((receivedLength / contentLength) * 70);
                }
            }

            // Convert chunks to text
            const decoder = new TextDecoder("utf-8");
            let text = '';
            for (let chunk of chunks) {
                text += decoder.decode(chunk, { stream: true });
            }
            text += decoder.decode();

            this.loadingProgress = 80;
            this.loadingStatus = 'Plotting vector...';

            // Allow UI update
            await new Promise(r => setTimeout(r, 50));

            await this.renderHpgl(text);

            this.loadingProgress = 100;
        } catch (error) {
            console.error('[FileViewer] HPGL loading error:', error);
            this.hpglError = 'Failed to load HPGL: ' + error.message;
            this.hpglLoading = false;
        }
    },

    async renderHpgl(text) {
        const canvas = this.$refs.hpglCanvas;
        if (!canvas) {
            console.error('[FileViewer] HPGL canvas not found');
            return;
        }

        try {
            // Standardize separators: replace newlines with ';', then split by ';'
            let commands = text.replace(/[\r\n]+/g, ';').split(';');

            // Helper to expand concatenated commands (e.g. PU100,100PD200,200)
            const expandedCommands = [];
            for (const cmd of commands) {
                if (!cmd || !cmd.trim()) continue;

                if (cmd.length > 10000) {
                    // Safety break for extremely long lines, simplified check
                    let i = 0;
                    while (i < cmd.length) {
                        if (i + 1 < cmd.length && /[A-Z]/.test(cmd[i]) && /[A-Z]/.test(cmd[i + 1])) {
                            const opcode = cmd.substring(i, i + 2);
                            i += 2;
                            let args = '';
                            while (i < cmd.length && !(/[A-Z]/.test(cmd[i]) && i + 1 < cmd.length && /[A-Z]/.test(cmd[i + 1]))) {
                                args += cmd[i];
                                i++;
                            }
                            expandedCommands.push(opcode + args);
                        } else {
                            i++;
                        }
                    }
                } else {
                    const parts = cmd.match(/[A-Z]{2}[^A-Z]*/g);
                    if (parts && parts.length > 1) {
                        expandedCommands.push(...parts);
                    } else {
                        expandedCommands.push(cmd);
                    }
                }
            }

            commands = expandedCommands;

            let penDown = false;
            let isRelative = false;
            let x = 0, y = 0;
            const segments = [];

            const parseCoords = (str) => {
                if (!str || !str.trim()) return [];
                return str.replace(/,/g, ' ').trim().split(/\s+/).map(Number).filter(v => !isNaN(v));
            };

            const addSegment = (x1, y1, x2, y2) => {
                segments.push({ x1, y1, x2, y2 });
            };

            const addArc = (cx, cy, radius, startAngle, endAngle, steps = 30) => {
                if (steps < 4) steps = 4;
                const angleStep = (endAngle - startAngle) / steps;
                let prevX = cx + radius * Math.cos(startAngle * Math.PI / 180);
                let prevY = cy + radius * Math.sin(startAngle * Math.PI / 180);

                for (let i = 1; i <= steps; i++) {
                    const angle = startAngle + angleStep * i;
                    const nx = cx + radius * Math.cos(angle * Math.PI / 180);
                    const ny = cy + radius * Math.sin(angle * Math.PI / 180);
                    addSegment(prevX, prevY, nx, ny);
                    prevX = nx;
                    prevY = ny;
                }
            };

            for (const raw of commands) {
                if (!raw || !raw.trim()) continue;

                const cmd = raw.trim().toUpperCase();
                const op = cmd.slice(0, 2);
                const argsStr = cmd.slice(2);
                const coords = parseCoords(argsStr);

                const processMove = () => {
                    for (let i = 0; i < coords.length; i += 2) {
                        if (i + 1 >= coords.length) break;
                        let nx = coords[i], ny = coords[i + 1];

                        if (isRelative) {
                            nx += x;
                            ny += y;
                        }

                        if (penDown) addSegment(x, y, nx, ny);
                        x = nx; y = ny;
                    }
                };

                // Basic HP-GL commands
                if (op === 'IN') {
                    penDown = false; isRelative = false; x = 0; y = 0;
                } else if (op === 'PU') {
                    penDown = false; if (coords.length > 0) processMove();
                } else if (op === 'PD') {
                    penDown = true; if (coords.length > 0) processMove();
                } else if (op === 'PA') {
                    isRelative = false; if (coords.length > 0) processMove();
                } else if (op === 'PR') {
                    isRelative = true; if (coords.length > 0) processMove();
                } else if (op === 'CI') {
                    if (coords.length >= 1) {
                        const radius = Math.abs(coords[0]);
                        addArc(x, y, radius, 0, 360, 64);
                    }
                } else if (op === 'AA') {
                    if (coords.length >= 3) {
                        const cx = coords[0], cy = coords[1], sweepAngle = coords[2];
                        const radius = Math.sqrt((x - cx) ** 2 + (y - cy) ** 2);
                        const startAngle = Math.atan2(y - cy, x - cx) * 180 / Math.PI;
                        const endAngle = startAngle + sweepAngle;
                        addArc(cx, cy, radius, startAngle, endAngle, Math.max(16, Math.abs(sweepAngle) / 5));
                        x = cx + radius * Math.cos(endAngle * Math.PI / 180);
                        y = cy + radius * Math.sin(endAngle * Math.PI / 180);
                    }
                } else if (op === 'AR') {
                    if (coords.length >= 3) {
                        const cx = x + coords[0], cy = y + coords[1], sweepAngle = coords[2];
                        const radius = Math.sqrt(coords[0] ** 2 + coords[1] ** 2);
                        const startAngle = Math.atan2(-coords[1], -coords[0]) * 180 / Math.PI;
                        const endAngle = startAngle + sweepAngle;
                        addArc(cx, cy, radius, startAngle, endAngle, Math.max(16, Math.abs(sweepAngle) / 5));
                        x = cx + radius * Math.cos(endAngle * Math.PI / 180);
                        y = cy + radius * Math.sin(endAngle * Math.PI / 180);
                    }
                }
            }

            if (!segments.length) throw new Error('No drawable content found');

            // 2. Bounds Calculation
            let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
            for (const s of segments) {
                minX = Math.min(minX, s.x1, s.x2);
                maxX = Math.max(maxX, s.x1, s.x2);
                minY = Math.min(minY, s.y1, s.y2);
                maxY = Math.max(maxY, s.y1, s.y2);
            }

            // 3. Viewport setup
            // Basic container check
            let container = canvas.parentElement;
            const viewW = Math.max(container?.clientWidth || 800, 800);
            const viewH = Math.max(container?.clientHeight || 600, 600);

            const dpr = window.devicePixelRatio || 1;
            const totalScale = dpr * 5; // Support zoom quality

            canvas.width = viewW * totalScale;
            canvas.height = viewH * totalScale;
            canvas.style.width = viewW + 'px';
            canvas.style.height = viewH + 'px';

            const ctx = canvas.getContext('2d');
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            // White paper background
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.scale(totalScale, totalScale);

            ctx.lineWidth = 0.2;
            ctx.strokeStyle = '#000';

            const dx = maxX - minX || 1, dy = maxY - minY || 1;
            const fitScale = 0.98 * Math.min(viewW / dx, viewH / dy);
            const transX = viewW / 2 - (minX + dx / 2) * fitScale;
            const transY = viewH / 2 + (minY + dy / 2) * fitScale;

            ctx.beginPath();
            for (const s of segments) {
                ctx.moveTo(s.x1 * fitScale + transX, -s.y1 * fitScale + transY);
                ctx.lineTo(s.x2 * fitScale + transX, -s.y2 * fitScale + transY);
            }
            ctx.stroke();

            // SECURITY: Burn stamps
            if (this._burnStampsToCanvas) {
                ctx.save();
                ctx.setTransform(1, 0, 0, 1, 0, 0); // Reset transform
                this._burnStampsToCanvas(ctx, canvas.width, canvas.height);
                ctx.restore();
            }

            this.hpglDrawingBounds = {
                left: minX * fitScale + transX,
                top: -maxY * fitScale + transY,
                width: dx * fitScale,
                height: dy * fitScale
            };

            this.hpglLoading = false;
            this.hpglLoading = false;
            this.recalculateMasks();

            // Force layout recalculation after render
            setTimeout(() => {
                if (this.recalculateLayout) this.recalculateLayout();
            }, 50);
        } catch (error) {
            console.error('[FileViewer] HPGL rendering error:', error);
            this.hpglError = 'Failed to render: ' + error.message;
            this.hpglLoading = false;
        }
    }
};
