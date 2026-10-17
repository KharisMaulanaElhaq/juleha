/**
 * DPD JULEHA Brebes - Chart.js Enhancements for Filament
 * Lightweight canvas-based value labels, dynamic theme adapting, and responsive multiline axis ticks.
 */
(function () {
    const julehaChartPlugin = {
        id: 'julehaChartEnhancements',

        beforeInit(chart) {
            // Apply only to horizontal bar charts
            if (chart.config?.type !== 'bar' || chart.options?.indexAxis !== 'y') {
                return;
            }

            // 1. Tooltip enhancements
            if (!chart.options.plugins) chart.options.plugins = {};
            if (!chart.options.plugins.tooltip) chart.options.plugins.tooltip = {};

            const origCallbacks = chart.options.plugins.tooltip.callbacks || {};

            chart.options.plugins.tooltip.callbacks = {
                ...origCallbacks,
                title(items) {
                    if (!items || !items.length) return '';
                    const idx = items[0].dataIndex;
                    const full = chart.data?.fullTitles ? chart.data.fullTitles[idx] : null;
                    if (full) return full;
                    const label = items[0].label;
                    return Array.isArray(label) ? label.join(' · ') : label;
                },
                label(context) {
                    const val = context.raw || 0;
                    return ' ' + context.dataset.label + ': ' + val + ' orang';
                },
                footer(items) {
                    if (!items || !items.length) return '';
                    const idx = items[0].dataIndex;
                    const quota = chart.data?.quotaTexts ? chart.data.quotaTexts[idx] : null;
                    return quota ? ('Kuota: ' + quota) : '';
                }
            };

            // 2. Responsive Y-Axis tick formatting (multiline on mobile)
            if (!chart.options.scales) chart.options.scales = {};
            if (!chart.options.scales.y) chart.options.scales.y = {};
            if (!chart.options.scales.y.ticks) chart.options.scales.y.ticks = {};

            chart.options.scales.y.ticks.callback = function (value) {
                const rawLabel = this.getLabelForValue ? this.getLabelForValue(value) : value;
                if (typeof rawLabel === 'string' && rawLabel.includes(' · ') && window.innerWidth < 640) {
                    return rawLabel.split(' · ');
                }
                return rawLabel;
            };
        },

        beforeDraw(chart) {
            if (chart.config?.type !== 'bar' || chart.options?.indexAxis !== 'y') {
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const datasets = chart.data?.datasets;
            if (datasets && datasets.length >= 3) {
                // Dataset 0: Diterima
                datasets[0].backgroundColor = isDark ? '#8b5cf6' : '#5a3d9e';
                datasets[0].borderColor = isDark ? '#a78bfa' : '#482f80';

                // Dataset 1: Menunggu diperiksa
                datasets[1].backgroundColor = isDark ? '#c084fc' : '#b9a2f0';
                datasets[1].borderColor = isDark ? '#e9d5ff' : '#9333ea';

                // Dataset 2: Tidak diterima
                datasets[2].backgroundColor = isDark ? '#64748b' : '#94a3b8';
                datasets[2].borderColor = isDark ? '#475569' : '#6b7280';
            }
        },

        afterDatasetsDraw(chart) {
            if (chart.config?.type !== 'bar' || chart.options?.indexAxis !== 'y') {
                return;
            }

            const ctx = chart.ctx;
            const data = chart.data;
            const isDark = document.documentElement.classList.contains('dark');

            const rowTotals = {};
            const rowMaxX = {};
            const rowY = {};

            // 1. Gambar angka di dalam segmen batang (bila cukup lebar)
            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                if (!meta.hidden && meta.data) {
                    meta.data.forEach((bar, index) => {
                        const val = Number(dataset.data[index]) || 0;
                        rowTotals[index] = (rowTotals[index] || 0) + val;

                        if (bar.x > (rowMaxX[index] || 0)) {
                            rowMaxX[index] = bar.x;
                        }
                        rowY[index] = bar.y;

                        // Segmen bernilai 0 tidak diberi label
                        if (val > 0) {
                            const barWidth = Math.abs(bar.x - bar.base);
                            // Bila lebar segmen >= 22px, cetak angka di tengah segmen
                            if (barWidth >= 22) {
                                ctx.save();
                                ctx.font = 'bold 11px Inter, system-ui, sans-serif';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.fillStyle = (datasetIndex === 1 && !isDark) ? '#4c1d95' : '#ffffff';
                                const textX = (bar.x + bar.base) / 2;
                                ctx.fillText(val, textX, bar.y);
                                ctx.restore();
                            }
                        }
                    });
                }
            });

            // 2. Gambar total di ujung batang
            const labels = data.labels || [];
            const quotaTexts = data.quotaTexts || [];
            const chartWidth = chart.width;

            labels.forEach((_, index) => {
                const total = rowTotals[index] || 0;
                const endX = rowMaxX[index] !== undefined ? rowMaxX[index] : (chart.scales.x?.left || 0);
                const y = rowY[index];
                if (y === undefined) return;

                ctx.save();
                ctx.font = '600 11px Inter, system-ui, sans-serif';
                ctx.fillStyle = isDark ? '#e2e8f0' : '#334155';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';

                // Tampilkan total, dan jika ruang cukup (desktop), tambahkan info kuota
                let text = String(total);
                if (total > 0 && quotaTexts[index] && chartWidth > 580) {
                    text = total + ' (' + quotaTexts[index] + ')';
                }

                ctx.fillText(text, endX + 8, y);
                ctx.restore();
            });
        }
    };

    window.filamentChartJsPlugins = window.filamentChartJsPlugins || [];
    window.filamentChartJsPlugins.push(julehaChartPlugin);
})();
