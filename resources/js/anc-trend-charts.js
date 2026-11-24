import {
    CategoryScale,
    Chart,
    Filler,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    CategoryScale,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Filler,
    Tooltip
);

const clinicalThresholdPlugin = {
    id: 'clinicalThresholds',
    afterDatasetsDraw(chart, _args, options) {
        const { ctx, chartArea, scales } = chart;
        if (!options || !Array.isArray(options.lines) || !options.lines.length || !chartArea) {
            return;
        }

        ctx.save();
        options.lines.forEach((line) => {
            const y = scales.y.getPixelForValue(line.value);
            if (y < chartArea.top - 4 || y > chartArea.bottom + 4) {
                return;
            }

            ctx.beginPath();
            ctx.setLineDash([5, 4]);
            ctx.strokeStyle = line.color;
            ctx.lineWidth = 1.4;
            ctx.moveTo(chartArea.left, y);
            ctx.lineTo(chartArea.right, y);
            ctx.stroke();

            ctx.setLineDash([]);
            ctx.fillStyle = line.color;
            ctx.font = '700 9px Poppins, sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(String(line.value), chartArea.right - 2, Math.max(chartArea.top + 10, y - 4));
        });
        ctx.restore();
    },
};

const THEMES = {
    rose: {
        primary: '#f43f5e',
        primarySoft: 'rgba(244, 63, 94, 0.14)',
        secondary: '#fb923c',
        tabActive: ['bg-rose-500', 'text-white', 'shadow-sm'],
        tabInactive: ['text-gray-500', 'hover:text-gray-800'],
    },
    blue: {
        primary: '#2563eb',
        primarySoft: 'rgba(37, 99, 235, 0.14)',
        secondary: '#0ea5e9',
        tabActive: ['bg-blue-600', 'text-white', 'shadow-sm'],
        tabInactive: ['text-gray-500', 'hover:text-gray-800'],
    },
};

const METRIC_META = {
    weight: {
        title: 'Berat Badan (kg)',
        unit: 'kg',
        thresholdLabel: 'Pantau kenaikan bertahap',
        defaultMin: 40,
        defaultMax: 80,
        padding: 3,
        thresholds: [],
    },
    bp: {
        title: 'Tekanan Darah (mmHg)',
        unit: 'mmHg',
        thresholdLabel: '≥ 140/90 mmHg (Preeklampsia)',
        defaultMin: 60,
        defaultMax: 155,
        padding: 10,
        thresholds: [
            { value: 140, label: 'Batas Sistolik 140', color: '#ef4444' },
            { value: 90, label: 'Batas Diastolik 90', color: '#f97316' },
        ],
    },
    hb: {
        title: 'Hemoglobin / Hb (g/dL)',
        unit: 'g/dL',
        thresholdLabel: '< 11.0 g/dL (Anemia)',
        defaultMin: 8.5,
        defaultMax: 14.5,
        padding: 0.8,
        thresholds: [{ value: 11.0, label: 'Batas Anemia 11.0', color: '#ef4444' }],
    },
    lila: {
        title: 'Lingkar Lengan Atas / LILA (cm)',
        unit: 'cm',
        thresholdLabel: '< 23.5 cm (KEK)',
        defaultMin: 20.0,
        defaultMax: 30.0,
        padding: 1.2,
        thresholds: [{ value: 23.5, label: 'Batas KEK 23.5', color: '#f97316' }],
    },
};

function computeBounds(values, thresholds, meta) {
    const numeric = values.filter((v) => typeof v === 'number' && !Number.isNaN(v));
    const thresholdVals = thresholds.map((t) => t.value);
    const all = [...numeric, ...thresholdVals];

    if (!all.length) {
        return { min: meta.defaultMin, max: meta.defaultMax };
    }

    const rawMin = Math.min(...all);
    const rawMax = Math.max(...all);
    const span = Math.max(rawMax - rawMin, meta.padding * 2);
    const pad = Math.max(meta.padding, span * 0.18);

    return {
        min: Math.max(0, Math.floor((rawMin - pad) * 10) / 10),
        max: Math.ceil((rawMax + pad) * 10) / 10,
    };
}

function lastNonNull(arr) {
    for (let i = arr.length - 1; i >= 0; i--) {
        if (arr[i] !== null && arr[i] !== undefined) {
            return arr[i];
        }
    }
    return null;
}

function mountChartWidget(container) {
    const payloadEl = container.querySelector('[data-chart-payload]');
    const canvas = container.querySelector('canvas[data-chart-canvas]');
    if (!payloadEl || !canvas) {
        return;
    }

    let payload;
    try {
        payload = JSON.parse(payloadEl.textContent || '{}');
    } catch {
        return;
    }

    const points = Array.isArray(payload.points) ? payload.points : [];
    if (!points.length) {
        return;
    }

    const themeKey = container.dataset.theme === 'blue' ? 'blue' : 'rose';
    const theme = THEMES[themeKey];
    let currentMetric = payload.defaultMetric || 'bp';
    let chartInstance = null;

    const tabs = container.querySelectorAll('[data-metric-tab]');
    const titleEl = container.querySelector('[data-metric-title]');
    const latestEl = container.querySelector('[data-latest-value]');
    const thresholdEl = container.querySelector('[data-threshold-text]');
    const statusBadgeEl = container.querySelector('[data-status-badge]');
    const emptyMetricEl = container.querySelector('[data-empty-metric]');
    const canvasWrapEl = container.querySelector('[data-canvas-wrap]');
    const legendEl = container.querySelector('[data-chart-legend]');

    function renderLegend(metric, meta) {
        if (!legendEl) return;
        legendEl.textContent = '';

        const leftGroup = document.createElement('div');
        leftGroup.className = 'flex items-center gap-3 flex-wrap';

        if (metric === 'bp') {
            [
                { label: 'Sistolik', color: theme.primary },
                { label: 'Diastolik', color: theme.secondary },
            ].forEach((item) => {
                const span = document.createElement('span');
                span.className = 'inline-flex items-center gap-1';
                const dot = document.createElement('span');
                dot.className = 'w-2.5 h-2.5 rounded-full inline-block';
                dot.style.backgroundColor = item.color;
                span.appendChild(dot);
                span.appendChild(document.createTextNode(item.label));
                leftGroup.appendChild(span);
            });
            legendEl.appendChild(leftGroup);

            const thSpan = document.createElement('span');
            thSpan.className = 'text-red-500 font-semibold';
            thSpan.textContent = '--- Batas 140/90';
            legendEl.appendChild(thSpan);
        } else {
            const span = document.createElement('span');
            span.className = 'inline-flex items-center gap-1';
            const dot = document.createElement('span');
            dot.className = 'w-2.5 h-2.5 rounded-full inline-block';
            dot.style.backgroundColor = theme.primary;
            span.appendChild(dot);
            span.appendChild(document.createTextNode('Riwayat Pengukuran'));
            leftGroup.appendChild(span);
            legendEl.appendChild(leftGroup);

            const rightSpan = document.createElement('span');
            if (meta.thresholds.length) {
                rightSpan.className = 'text-red-500 font-semibold';
                rightSpan.textContent = `--- ${meta.thresholds[0].label}`;
            } else {
                rightSpan.className = 'text-gray-400';
                rightSpan.textContent = `${points.length} kunjungan tercatat`;
            }
            legendEl.appendChild(rightSpan);
        }
    }

    function updateStatusBadge(isRisk, label) {
        if (!statusBadgeEl) return;
        statusBadgeEl.textContent = label;
        statusBadgeEl.className = isRisk
            ? 'text-[10px] font-bold px-2.5 py-1 rounded-full bg-red-50 text-red-600 border border-red-200 whitespace-nowrap'
            : 'text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap';
    }

    function renderMetric(metric) {
        currentMetric = metric;
        const meta = METRIC_META[metric] || METRIC_META.weight;

        tabs.forEach((btn) => {
            const active = btn.dataset.metricTab === metric;
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
            btn.classList.remove(...theme.tabActive, ...theme.tabInactive);
            btn.classList.add(...(active ? theme.tabActive : theme.tabInactive));
        });

        if (titleEl) titleEl.textContent = meta.title;
        if (thresholdEl) thresholdEl.textContent = meta.thresholdLabel;

        const labels = points.map((p) => p.label);
        const dates = points.map((p) => p.date);

        let hasValues = false;
        let datasets = [];
        let allValues = [];

        if (metric === 'bp') {
            const sys = points.map((p) => p.systolic);
            const dia = points.map((p) => p.diastolic);
            const lastS = lastNonNull(sys);
            const lastD = lastNonNull(dia);
            hasValues = lastS !== null || lastD !== null;
            allValues = [...sys, ...dia];

            if (latestEl) {
                latestEl.textContent = hasValues ? `${lastS ?? '–'}/${lastD ?? '–'} mmHg` : '—';
            }

            if (hasValues) {
                const isHigh = (lastS !== null && lastS >= 140) || (lastD !== null && lastD >= 90);
                updateStatusBadge(isHigh, isHigh ? 'Waspada Preeklampsia' : 'TD Normal');
            } else {
                updateStatusBadge(false, 'Belum Ada Data');
            }

            datasets = [
                {
                    label: 'Sistolik',
                    data: sys,
                    borderColor: theme.primary,
                    backgroundColor: theme.primarySoft,
                    fill: true,
                    spanGaps: true,
                    tension: 0.32,
                    borderWidth: 2.5,
                    pointRadius: 4.5,
                    pointHoverRadius: 6.5,
                    pointBackgroundColor: sys.map((v) => (v !== null && v >= 140 ? '#ef4444' : '#ffffff')),
                    pointBorderColor: sys.map((v) => (v !== null && v >= 140 ? '#b91c1c' : theme.primary)),
                    pointBorderWidth: 2.2,
                },
                {
                    label: 'Diastolik',
                    data: dia,
                    borderColor: theme.secondary,
                    backgroundColor: 'transparent',
                    fill: false,
                    spanGaps: true,
                    tension: 0.32,
                    borderWidth: 2.2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: dia.map((v) => (v !== null && v >= 90 ? '#ef4444' : '#ffffff')),
                    pointBorderColor: dia.map((v) => (v !== null && v >= 90 ? '#b91c1c' : theme.secondary)),
                    pointBorderWidth: 2,
                },
            ];
        } else {
            const vals = points.map((p) => p[metric]);
            const latest = lastNonNull(vals);
            hasValues = latest !== null;
            allValues = vals;

            if (latestEl) {
                latestEl.textContent = hasValues ? `${latest} ${meta.unit}` : '—';
            }

            if (!hasValues) {
                updateStatusBadge(false, 'Belum Ada Data');
            } else if (metric === 'hb') {
                updateStatusBadge(latest < 11.0, latest < 11.0 ? 'Waspada Anemia' : 'Hb Normal');
            } else if (metric === 'lila') {
                updateStatusBadge(latest < 23.5, latest < 23.5 ? 'Waspada KEK' : 'LILA Normal');
            } else {
                updateStatusBadge(false, 'Terpantau');
            }

            datasets = [
                {
                    label: meta.title,
                    data: vals,
                    borderColor: theme.primary,
                    backgroundColor: theme.primarySoft,
                    fill: true,
                    spanGaps: true,
                    tension: 0.32,
                    borderWidth: 2.5,
                    pointRadius: 4.5,
                    pointHoverRadius: 6.5,
                    pointBackgroundColor: vals.map((v) => {
                        if (v === null) return '#ffffff';
                        const warn = (metric === 'hb' && v < 11.0) || (metric === 'lila' && v < 23.5);
                        return warn ? '#ef4444' : '#ffffff';
                    }),
                    pointBorderColor: vals.map((v) => {
                        if (v === null) return theme.primary;
                        const warn = (metric === 'hb' && v < 11.0) || (metric === 'lila' && v < 23.5);
                        return warn ? '#b91c1c' : theme.primary;
                    }),
                    pointBorderWidth: 2.2,
                },
            ];
        }

        renderLegend(metric, meta);

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        if (!hasValues) {
            if (canvasWrapEl) canvasWrapEl.classList.add('hidden');
            if (emptyMetricEl) {
                emptyMetricEl.textContent = `Belum ada angka ${meta.title} yang tercatat pada kunjungan ANC.`;
                emptyMetricEl.classList.remove('hidden');
            }
            return;
        }

        if (canvasWrapEl) canvasWrapEl.classList.remove('hidden');
        if (emptyMetricEl) emptyMetricEl.classList.add('hidden');

        const bounds = computeBounds(allValues, meta.thresholds, meta);

        chartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels, datasets },
            plugins: [clinicalThresholdPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    clinicalThresholds: { lines: meta.thresholds },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleFont: { family: 'Poppins, sans-serif', size: 11, weight: '600' },
                        bodyFont: { family: 'Poppins, sans-serif', size: 11 },
                        padding: 10,
                        cornerRadius: 12,
                        callbacks: {
                            title: (items) => {
                                const idx = items[0]?.dataIndex ?? 0;
                                return `${items[0]?.label ?? ''} (${dates[idx] ?? '-'})`;
                            },
                            label: (item) => ` ${item.dataset.label}: ${item.formattedValue} ${meta.unit}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Poppins, sans-serif', size: 10, weight: '600' },
                            color: '#64748b',
                        },
                    },
                    y: {
                        min: bounds.min,
                        max: bounds.max,
                        grid: { color: '#e2e8f0' },
                        ticks: {
                            font: { family: 'Poppins, sans-serif', size: 10 },
                            color: '#94a3b8',
                            maxTicksLimit: 5,
                        },
                    },
                },
            },
        });
    }

    tabs.forEach((btn) => {
        btn.addEventListener('click', () => {
            renderMetric(btn.dataset.metricTab);
        });
    });

    renderMetric(currentMetric);
}

export function initAncTrendCharts() {
    document.querySelectorAll('[data-anc-trend-chart]').forEach((container) => {
        if (container.dataset.chartMounted === 'true') {
            return;
        }
        container.dataset.chartMounted = 'true';
        mountChartWidget(container);
    });
}
