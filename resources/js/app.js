import './bootstrap';

function bootAncTrendCharts() {
    if (!document.querySelector('[data-anc-trend-chart]')) {
        return;
    }

    import('./anc-trend-charts').then(({ initAncTrendCharts }) => {
        initAncTrendCharts();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAncTrendCharts);
} else {
    bootAncTrendCharts();
}
