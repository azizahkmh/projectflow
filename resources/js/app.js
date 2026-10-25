import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
Alpine.plugin(focus);

/* ------------------------------------------------------------------ icons --
 * Lucide renders <i data-lucide="folder"></i> into an inline SVG.
 *
 * createIcons() MUTATES the DOM (it replaces the <i> node with an <svg>).
 * If we observe every DOM mutation and re-run it blindly, the mutation
 * re-triggers the observer in an infinite loop — which consistently steals
 * focus so inputs cannot be typed into and buttons cannot be clicked.
 *
 * To avoid that we:
 *   1. render once on load,
 *   2. only react to *added element* nodes (never attribute changes),
 *   3. debounce, and
 *   4. skip nodes that are already rendered SVGs.
 * ------------------------------------------------------------------------ */
function renderIcons(root = document) {
    createIcons({
        icons,
        attrs: { 'stroke-width': 1.75, 'aria-hidden': 'true' },
        nameAttr: 'data-lucide',
    });
}
window.renderIcons = renderIcons;

let iconTimer = null;
let rendering = false;

function queueIcons() {
    if (rendering) return;
    clearTimeout(iconTimer);
    iconTimer = setTimeout(() => {
        rendering = true;
        renderIcons();
        // Allow the observer callbacks triggered by our own render to be
        // discarded before we re-arm mutation handling.
        requestAnimationFrame(() => {
            rendering = false;
        });
    }, 60);
}

document.addEventListener('DOMContentLoaded', () => {
    renderIcons();

    const observer = new MutationObserver((mutations) => {
        if (rendering) return;

        for (const mutation of mutations) {
            // Only care about newly *inserted element* nodes.
            if (!mutation.addedNodes.length) continue;

            for (const node of mutation.addedNodes) {
                if (node.nodeType !== 1) continue;
                // Ignore the <svg>/<path> that Lucide itself just inserted.
                const tag = node.tagName?.toLowerCase();
                if (tag === 'svg' || tag === 'path') continue;
                if (node.closest?.('svg')) continue;

                queueIcons();
                return;
            }
        }
    });

    // childList only — never watch attributes (that was the typing bug).
    observer.observe(document.body, { childList: true, subtree: true });
});

/* --------------------------------------------------------------- charts --- */
window.Chart = Chart;

window.makeAreaChart = function (canvas, labels, datasets) {
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height || 320);
    gradient.addColorStop(0, 'rgba(163, 230, 53, 0.45)');
    gradient.addColorStop(0.6, 'rgba(163, 230, 53, 0.10)');
    gradient.addColorStop(1, 'rgba(163, 230, 53, 0)');

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: datasets.map((ds, index) => ({
                label: ds.label,
                data: ds.data,
                borderColor: ds.color ?? '#84CC16',
                backgroundColor: index === 0 ? gradient : 'transparent',
                borderWidth: 2.5,
                fill: index === 0,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#0F172A',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: datasets.length > 1,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#64748B',
                        font: { family: 'Inter', size: 11, weight: '600' },
                    },
                },
                tooltip: {
                    backgroundColor: '#0F172A',
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: { family: 'Inter', size: 12, weight: '600' },
                    bodyFont: { family: 'Inter', size: 12 },
                    displayColors: false,
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#94A3B8', font: { family: 'Inter', size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(148, 163, 184, 0.16)' },
                    ticks: { color: '#94A3B8', font: { family: 'Inter', size: 11 }, padding: 8 },
                },
            },
        },
    });
};

window.makeBarChart = function (canvas, labels, data) {
    return new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Cashflow',
                data,
                backgroundColor: (ctx) => {
                    const { chart } = ctx;
                    const { ctx: c, chartArea } = chart;
                    if (!chartArea) return '#A3E635';
                    const g = c.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    g.addColorStop(0, 'rgba(132, 204, 22, 0.55)');
                    g.addColorStop(1, 'rgba(163, 230, 53, 1)');
                    return g;
                },
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 34,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        label: (item) => ' $' + Number(item.raw).toLocaleString(),
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: '#94A3B8', font: { family: 'Inter', size: 11 } } },
                y: { display: false, beginAtZero: true },
            },
        },
    });
};

/* --------------------------------------------------------------- donut ---- */
/**
 * Doughnut / ring chart. Center label is drawn by the caller in HTML overlay.
 * @param {HTMLCanvasElement} canvas
 * @param {string[]} labels
 * @param {number[]} data
 * @param {string[]} colors
 */
window.makeDonutChart = function (canvas, labels, data, colors) {
    return new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 6,
                spacing: 1,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 14,
                        color: '#64748B',
                        font: { family: 'Inter', size: 11, weight: '600' },
                    },
                },
                tooltip: {
                    backgroundColor: '#0F172A',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: true,
                    boxWidth: 8,
                    boxHeight: 8,
                    usePointStyle: true,
                    titleFont: { family: 'Inter', size: 12, weight: '600' },
                    bodyFont: { family: 'Inter', size: 12 },
                },
            },
        },
    });
};

/* ---------------------------------------------------- horizontal bars ---- */
/**
 * Horizontal comparison bars — good for "budget vs spent" style figures.
 * @param {HTMLCanvasElement} canvas
 * @param {string[]} labels
 * @param {number[]} data
 * @param {string[]} colors
 */
window.makeHorizontalBarChart = function (canvas, labels, data, colors) {
    return new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors,
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 22,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        label: (item) => ' ' + Number(item.raw).toLocaleString(),
                    },
                },
            },
            scales: {
                x: { grid: { color: 'rgba(148, 163, 184, 0.16)' }, border: { display: false }, ticks: { color: '#94A3B8', font: { family: 'Inter', size: 11 } } },
                y: { grid: { display: false }, border: { display: false }, ticks: { color: '#475569', font: { family: 'Inter', size: 11, weight: '600' } } },
            },
        },
    });
};

/* --------------------------------------------------------------- radar ---- */
/**
 * Radar chart for multi-axis comparisons (e.g. team capability / quality).
 * @param {HTMLCanvasElement} canvas
 * @param {string[]} labels
 * @param {{label:string,data:number[],color:string}[]} series
 */
window.makeRadarChart = function (canvas, labels, series) {
    return new Chart(canvas.getContext('2d'), {
        type: 'radar',
        data: {
            labels,
            datasets: series.map((s) => ({
                label: s.label,
                data: s.data,
                borderColor: s.color,
                backgroundColor: s.color + '26',
                borderWidth: 2,
                pointBackgroundColor: s.color,
                pointBorderColor: '#fff',
                pointBorderWidth: 1.5,
                pointRadius: 3,
                pointHoverRadius: 5,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 14,
                        color: '#64748B',
                        font: { family: 'Inter', size: 11, weight: '600' },
                    },
                },
                tooltip: {
                    backgroundColor: '#0F172A',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    bodyFont: { family: 'Inter', size: 12 },
                },
            },
            scales: {
                r: {
                    beginAtZero: true,
                    suggestedMax: 100,
                    angleLines: { color: 'rgba(148, 163, 184, 0.2)' },
                    grid: { color: 'rgba(148, 163, 184, 0.2)' },
                    pointLabels: { color: '#475569', font: { family: 'Inter', size: 11, weight: '600' } },
                    ticks: { display: false, stepSize: 25 },
                },
            },
        },
    });
};

/* --------------------------------------------------------- alpine stores --- */
Alpine.store('modals', {
    open: [],
    isOpen(name) {
        return this.open.includes(name);
    },
    show(name) {
        if (! this.open.includes(name)) this.open.push(name);
        document.documentElement.classList.add('overflow-hidden');
    },
    close(name) {
        this.open = this.open.filter((n) => n !== name);
        if (this.open.length === 0) document.documentElement.classList.remove('overflow-hidden');
    },
});

Alpine.store('toasts', {
    items: [],
    push(message, type = 'success') {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type });
        setTimeout(() => this.dismiss(id), 4000);
    },
    dismiss(id) {
        this.items = this.items.filter((t) => t.id !== id);
    },
});

Alpine.start();
