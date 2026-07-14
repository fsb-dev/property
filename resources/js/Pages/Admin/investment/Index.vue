<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

const props = defineProps({
    kpis:               { type: Array,  required: true },
    market_growth:      { type: Object, required: true },
    roi_projection:     { type: Object, required: true },
    heatmap_regions:    { type: Array,  required: true },
    investment_summary: { type: Array,  required: true },
    projects:           { type: Array,  required: true },
    insights:           { type: Array,  required: true },
    quick_actions:      { type: Array,  required: true },
});

// ── Icon paths (design-only, keyed by the `icon` field coming from the JSON) ──
const icons = {
    portfolio:  '<path d="M3 7h18v12H3zM3 7l2-3h14l2 3M16 12h.01"/>',
    invest:     '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
    roi:        '<path d="M3 17l6-6 4 4 7-7M14 8h6v6"/>',
    yield:      '<path d="M3 11l9-7 9 7"/><path d="M5 10v9h14v-9"/><path d="M10 19v-5h4v5"/>',
    growth:     '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>',
    occupancy:  '<path d="M3 21V8l7-5 7 5v13M3 21h18M9 21v-5h4v5M7 10h.01M13 10h.01M7 13h.01M13 13h.01"/>',
    demand:     '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    rental:     '<path d="M3 11l9-7 9 7M5 10v9h14v-9"/>',
    building:   '<path d="M3 21h18M5 21V8l7-4 7 4v13"/>',
    report:     '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
    compare:    '<path d="M7 16V4M7 4L3 8M7 4l4 4M17 8v12M17 20l4-4M17 20l-4-4"/>',
    export:     '<path d="M12 3v12M7 10l5 5 5-5M5 21h14"/>',
    analysis:   '<path d="M3 17l6-6 4 4 7-7M14 8h6v6"/>',
    calculator: '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 10h8M8 14h3M15 18h.01"/>',
    forecast:   '<path d="M3 3v18h18M7 14l4-4 3 3 5-6"/>',
    simulator:  '<path d="M12 2v4M12 18v4M2 12h4M18 12h4"/><circle cx="12" cy="12" r="4"/>',
    prediction: '<path d="M21 12a9 9 0 11-6.2-8.5M21 4v5h-5"/>',
    ai:         '<path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z"/><path d="M19 15l.9 2.3L22 18l-2.1.7L19 21l-.9-2.3L16 18l2.1-.7z"/>',
};

// ── Demo-only toast (decorative buttons throughout this page have no backend) ──
const flashMsg = ref('');
let flashTimer;
function flash(message) {
    flashMsg.value = message;
    clearTimeout(flashTimer);
    flashTimer = setTimeout(() => (flashMsg.value = ''), 1800);
}

// ── KPI sparklines ──────────────────────────────────────────────────────────
function sparkPoints(spark) {
    return spark.map((y, i) => `${i * 15},${y}`).join(' ');
}

// ── Market growth line chart geometry ───────────────────────────────────────
// Each period tab (1Y/3Y/5Y/10Y) has its own years + city values in the JSON
// (market_growth.datasets), so switching tabs redraws the chart with real data.
const mg = props.market_growth;
const mgMaster = mg.datasets['10Y'] ?? Object.values(mg.datasets)[0];
const MG_LEFT = 70, MG_RIGHT = 600, MG_TOP = 24, MG_BOTTOM = 250;
const mgGridYs = [24, 69, 114, 159, 204, 250];

const activePeriod = ref(mg.active_period);
const activeDataset = computed(() => mg.datasets[activePeriod.value] ?? mgMaster);
const mgStepX = computed(() => (MG_RIGHT - MG_LEFT) / (activeDataset.value.years.length - 1));

function mgY(value) {
    return MG_BOTTOM - (value / mg.axis_max) * (MG_BOTTOM - MG_TOP);
}

const mgCities = computed(() => activeDataset.value.cities.map(city => {
    const startValue = city.values[0];
    const lastValue = city.values[city.values.length - 1];
    return {
        ...city,
        points: city.values.map((v, i) => `${MG_LEFT + mgStepX.value * i},${mgY(v)}`).join(' '),
        lastX: MG_LEFT + mgStepX.value * (city.values.length - 1),
        lastY: mgY(lastValue),
        startValue,
        lastValue,
        changePct: (((lastValue - startValue) / startValue) * 100).toFixed(1),
    };
}));

// Tooltip label always reflects the selected period's most recent data point,
// not a fixed date — so it visibly changes when you switch 1Y/3Y/5Y/10Y.
const mgTooltipLabel = computed(() => {
    const years = activeDataset.value.years;
    return `${years[years.length - 1]} · ${activePeriod.value} view`;
});

const mgLabels = computed(() => {
    const steps = mg.axis_max / mg.axis_step;
    return Array.from({ length: steps }, (_, i) => ({
        y: mgGridYs[i],
        text: `${mg.unit} ${(mg.axis_max - i * mg.axis_step) / 1000}K`,
    }));
});

// ── ROI projection bar chart ─────────────────────────────────────────────────
// Each year in the "This Year" dropdown has its own series in the JSON
// (roi_projection.datasets), so switching years redraws the bars with real data.
const roi = props.roi_projection;
const activeRoiYear = ref(roi.active_year);
const activeRoiSeries = computed(() => roi.datasets[activeRoiYear.value]?.series ?? Object.values(roi.datasets)[0].series);
function barHeightPct(value) {
    return Math.min(100, (value / roi.axis_max) * 100);
}

// ── Investment heatmap ───────────────────────────────────────────────────────
const heatmapRingRadii = { 'Very High': [26, 15, 7], High: [18, 9, 5], Medium: [13, 5], Low: [10, 4] };
const heatmapRegionsWithRings = computed(() => props.heatmap_regions.map((region, idx) => {
    const radii = heatmapRingRadii[region.level] ?? [12, 5];
    return {
        ...region,
        idx,
        rings: radii.map((radius, ri) => ({
            radius,
            opacity: ri === radii.length - 1 ? 1 : 0.15 + ri * 0.08,
        })),
    };
}));
const activeRegionIdx = ref(0);
const activeRegion = computed(() => props.heatmap_regions[activeRegionIdx.value] ?? props.heatmap_regions[0]);

// ── Investment table: tabs, filter, pagination ──────────────────────────────
// Type/status categories are read off whatever real projects come back from the
// database (Admin\ProjectController's ProjectType/ProjectStatus labels), not a
// fixed hardcoded list — so the tabs always match what's actually in the table.
const activeTab = ref({ field: null, value: 'all' });
const currentPage = ref(1);
const perPage = 6;

const tabs = computed(() => {
    const types = [...new Set(props.projects.map(p => p.type))];
    const statuses = [...new Set(props.projects.map(p => p.status))];
    return [
        { label: 'All Projects', value: 'all', field: null, count: props.projects.length },
        ...types.map(t => ({ label: t, value: t, field: 'type', count: props.projects.filter(p => p.type === t).length })),
        ...statuses.map(s => ({ label: s, value: s, field: 'status', count: props.projects.filter(p => p.status === s).length })),
    ];
});

function matchesTab(project, tab) {
    if (tab.value === 'all') return true;
    return project[tab.field] === tab.value;
}

const filteredProjects = computed(() => props.projects.filter(p => matchesTab(p, activeTab.value) && matchesExtraFilters(p)));
const totalPages = computed(() => Math.max(1, Math.ceil(filteredProjects.value.length / perPage)));
const pagedProjects = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredProjects.value.slice(start, start + perPage);
});

function setTab(tab) {
    activeTab.value = { field: tab.field, value: tab.value };
    currentPage.value = 1;
}
function isActiveTab(tab) {
    return activeTab.value.field === tab.field && activeTab.value.value === tab.value;
}
function goPage(page) {
    currentPage.value = Math.min(Math.max(1, page), totalPages.value);
}

const rowColorPairs = [
    ['rgba(96,165,250,0.15)', '#60A5FA'], ['rgba(52,211,153,0.15)', '#34D399'], ['rgba(167,139,250,0.15)', '#A78BFA'],
    ['rgba(251,191,36,0.15)', '#FBBF24'], ['rgba(248,113,113,0.15)', '#F87171'], ['rgba(34,211,238,0.15)', '#22D3EE'],
];
function rowColors(id) {
    return rowColorPairs[(id - 1) % rowColorPairs.length];
}

// "Top Performing Projects" is the top 5 by ROI straight out of the Project
// Investment Overview table below — not a separate hand-curated list — so it
// always stays in sync with the table's actual data.
const topPerformingProjects = computed(() => [...props.projects]
    .sort((a, b) => b.roi - a.roi)
    .slice(0, 5)
    .map((p, i) => ({
        rank: i + 1,
        id: p.id,
        name: p.name,
        roi: p.roi + '%',
        rental: p.rental + '%',
        growth: p.growth_yoy + '%',
    })));

// Matches App\Enums\ProjectStatus labels (Draft is excluded server-side).
const statusStyle = {
    Planning:            { bg: 'rgba(96,165,250,0.15)', color: '#60A5FA' },
    'Under Construction': { bg: 'rgba(251,191,36,0.15)', color: '#FBBF24' },
    Completed:           { bg: 'rgba(52,211,153,0.15)', color: '#34D399' },
    'On Hold':           { bg: 'rgba(248,113,113,0.15)', color: '#F87171' },
    Cancelled:           { bg: 'rgba(148,138,128,0.15)', color: '#8A8780' },
};
const demandStyle = {
    'Very High': { bg: 'rgba(52,211,153,0.15)', color: '#34D399' },
    High:        { bg: 'rgba(96,165,250,0.15)', color: '#60A5FA' },
    Medium:      { bg: 'rgba(251,191,36,0.15)', color: '#FBBF24' },
    Low:         { bg: 'rgba(138,135,128,0.15)', color: '#8A8780' },
};

function formatBDT(amount) {
    const n = Number(amount) || 0;
    if (compactNumbers.value) {
        if (Math.abs(n) >= 1_000_000_000) return 'BDT ' + (n / 1_000_000_000).toFixed(2) + 'B';
        if (Math.abs(n) >= 1_000_000) return 'BDT ' + (n / 1_000_000).toFixed(2) + 'M';
    }
    return 'BDT ' + n.toLocaleString();
}
function roiWidth(value) {
    return Math.min(100, Math.round(value * (100 / 30)));
}

// ── Extra table filters (Location / Demand) — combined with the tab filter ──
const filterLocation = ref('');
const filterDemand = ref('');
const uniqueLocations = computed(() => [...new Set(props.projects.map(p => p.location))].sort());

function matchesExtraFilters(project) {
    if (filterLocation.value && project.location !== filterLocation.value) return false;
    if (filterDemand.value && project.demand !== filterDemand.value) return false;
    return true;
}
function resetFilters() {
    filterLocation.value = '';
    filterDemand.value = '';
    currentPage.value = 1;
}
function applyFilters() {
    currentPage.value = 1;
    activeTool.value = null;
    flash('Filters applied');
}

// ── Compare mode: select up to 4 rows from the table for a side-by-side view ──
const compareMode = ref(false);
const compareSelection = ref([]);

function toggleCompareMode() {
    compareMode.value = !compareMode.value;
    if (compareMode.value) {
        flash('Select up to 4 projects to compare');
    } else {
        compareSelection.value = [];
    }
}
function toggleCompareSelect(id) {
    const idx = compareSelection.value.indexOf(id);
    if (idx > -1) {
        compareSelection.value.splice(idx, 1);
        return;
    }
    if (compareSelection.value.length >= 4) {
        flash('You can compare up to 4 projects at a time');
        return;
    }
    compareSelection.value.push(id);
}
const compareProjects = computed(() => props.projects.filter(p => compareSelection.value.includes(p.id)));
const compareRows = [
    { label: 'Location',       get: p => p.location },
    { label: 'Type',           get: p => p.type },
    { label: 'Status',         get: p => p.status },
    { label: 'Investment',     get: p => formatBDT(p.investment) },
    { label: 'Current Value',  get: p => formatBDT(p.value) },
    { label: 'ROI',            get: p => p.roi + '%' },
    { label: 'Rental Yield',   get: p => p.rental + '%' },
    { label: 'Occupancy',      get: p => p.occupancy + '%' },
    { label: 'Demand',         get: p => p.demand },
    { label: 'Growth YoY',     get: p => p.growth_yoy + '%' },
];

// ── CSV export (real client-side download, no backend involved) ────────────
function escapeCsv(val) {
    const s = String(val ?? '');
    return /[",\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
}
function downloadCsv(rows, filename) {
    const csv = rows.map(row => row.map(escapeCsv).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}
function projectsToCsvRows(list) {
    const header = ['Project', 'Location', 'Type', 'Status', 'Investment (BDT)', 'Current Value (BDT)', 'ROI (%)', 'Rental (%)', 'Occupancy (%)', 'Demand', 'Growth YoY (%)'];
    const body = list.map(p => [p.name, p.location, p.type, p.status, p.investment, p.value, p.roi, p.rental, p.occupancy, p.demand, p.growth_yoy]);
    return [header, ...body];
}
function exportFiltered() {
    downloadCsv(projectsToCsvRows(filteredProjects.value), 'investment-projects-filtered.csv');
    flash(`Exported ${filteredProjects.value.length} projects to CSV`);
}
function exportAll() {
    downloadCsv(projectsToCsvRows(props.projects), 'investment-projects-all.csv');
    flash(`Exported all ${props.projects.length} projects to CSV`);
}

// ── Generic tool modal — every button below is wired to a real computation ──
// over the JSON props (no backend calls; this is still a static-data page).
const activeTool = ref(null);
function openTool(key) {
    activeTool.value = key;
}
function closeTool() {
    activeTool.value = null;
}

// Market Settings — genuinely changes how the table renders numbers, and which
// heatmap region is shown by default.
const compactNumbers = ref(false);

// Investment Report — pulled straight from the props already on the page.
const reportGeneratedAt = computed(() => new Date().toLocaleString());
function printReport() {
    window.print();
}

// Market Analysis — grouped by location, computed from the projects list.
const marketAnalysisRows = computed(() => {
    const groups = {};
    for (const p of props.projects) {
        if (!groups[p.location]) {
            groups[p.location] = { location: p.location, count: 0, investment: 0, value: 0, roiSum: 0, rentalSum: 0 };
        }
        const g = groups[p.location];
        g.count++;
        g.investment += p.investment;
        g.value += p.value;
        g.roiSum += p.roi;
        g.rentalSum += p.rental;
    }
    return Object.values(groups)
        .map(g => ({
            location: g.location,
            count: g.count,
            investment: g.investment,
            value: g.value,
            avgRoi: +(g.roiSum / g.count).toFixed(1),
            avgRental: +(g.rentalSum / g.count).toFixed(1),
        }))
        .sort((a, b) => b.avgRoi - a.avgRoi);
});

// ROI Calculator — projects a hypothetical investment using a project's real ROI/rental figures.
const calcAmount = ref(1000000);
const calcProjectId = ref(props.projects[0]?.id ?? null);
const calcProject = computed(() => props.projects.find(p => p.id === calcProjectId.value) ?? props.projects[0]);
const calcResult = computed(() => {
    const project = calcProject.value;
    const amount = Number(calcAmount.value) || 0;
    if (!project || !amount) return null;
    const projectedValue = amount * (1 + project.roi / 100);
    const annualRental = amount * (project.rental / 100);
    const capitalGain = projectedValue - amount;
    return { projectedValue, annualRental, capitalGain };
});

// Forecast — extrapolates each city's own historical growth rate from the 10-year
// dataset (the longest, most stable trend) regardless of which period tab is active.
const forecastYearStep = mgMaster.years[1] - mgMaster.years[0];
const forecastYearLabels = computed(() => {
    const lastYear = mgMaster.years[mgMaster.years.length - 1];
    return [1, 2, 3].map(n => lastYear + forecastYearStep * n);
});
const forecastRows = computed(() => mgMaster.cities.map(city => {
    const periods = city.values.length - 1;
    const rate = Math.pow(city.values[city.values.length - 1] / city.values[0], 1 / periods) - 1;
    const last = city.values[city.values.length - 1];
    return {
        name: city.name,
        color: city.color,
        ratePct: (rate * 100).toFixed(1),
        future: [1, 2, 3].map(n => Math.round(last * Math.pow(1 + rate, n))),
    };
}));

// Price Simulator — same per-period rate, applied year-by-year on a slider.
const simCity = ref(mgMaster.cities[0].name);
const simYears = ref(5);
const simResult = computed(() => {
    const city = mgMaster.cities.find(c => c.name === simCity.value) ?? mgMaster.cities[0];
    const periods = city.values.length - 1;
    const perPeriodRate = Math.pow(city.values[city.values.length - 1] / city.values[0], 1 / periods) - 1;
    const perYearRate = Math.pow(1 + perPeriodRate, 1 / forecastYearStep) - 1;
    const current = city.values[city.values.length - 1];
    const projected = Math.round(current * Math.pow(1 + perYearRate, simYears.value));
    return { current, projected, perYearRatePct: (perYearRate * 100).toFixed(2) };
});

// Demand Prediction — projects the Market Demand Index KPI forward using its own trend.
const demandKpi = computed(() => props.kpis.find(k => k.key === 'market_demand'));
const demandForecast = computed(() => {
    const current = Number(demandKpi.value?.value ?? 0);
    const quarterlyGrowth = Number(demandKpi.value?.change ?? 0) / 4;
    const q1 = Math.min(100, Math.round(current * (1 + quarterlyGrowth / 100)));
    const q2 = Math.min(100, Math.round(q1 * (1 + quarterlyGrowth / 100)));
    return { current, q1, q2 };
});

// AI Recommendations — deterministic rules over the same project data (no external AI call).
const aiRecommendations = computed(() => {
    const list = props.projects;
    if (list.length === 0) return [];
    const topRoi = [...list].sort((a, b) => b.roi - a.roi)[0];
    const topRental = [...list].sort((a, b) => b.rental - a.rental)[0];
    const topGrowth = [...list].sort((a, b) => b.growth_yoy - a.growth_yoy)[0];
    const underperformer = [...list].filter(p => p.status !== 'Cancelled').sort((a, b) => a.roi - b.roi)[0] ?? topRoi;
    return [
        { text: `${topRoi.name} in ${topRoi.location} delivers the portfolio's highest ROI at ${topRoi.roi}% — prioritise capital allocation here.`, color: '#34D399', bg: 'rgba(52,211,153,0.15)' },
        { text: `${topRental.name} offers the strongest rental yield (${topRental.rental}%), suited to income-focused investors.`, color: '#60A5FA', bg: 'rgba(96,165,250,0.15)' },
        { text: `${topGrowth.name} is growing fastest year-over-year (${topGrowth.growth_yoy}%) — a candidate for early-stage entry.`, color: '#C6A15B', bg: 'rgba(198,161,91,0.12)' },
        { text: `${underperformer.name} is underperforming its peers at ${underperformer.roi}% ROI — review pricing or occupancy strategy.`, color: '#FBBF24', bg: 'rgba(251,191,36,0.15)' },
    ];
});

function handleQuickAction(key) {
    switch (key) {
        case 'generate_report':     openTool('report'); break;
        case 'compare_projects':
            compareMode.value = true;
            flash('Select up to 4 projects to compare');
            document.getElementById('investment-table')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            break;
        case 'export_data':         exportAll(); break;
        case 'market_analysis':     openTool('marketAnalysis'); break;
        case 'roi_calculator':      openTool('roiCalculator'); break;
        case 'forecast':            openTool('forecast'); break;
        case 'price_simulator':     openTool('priceSimulator'); break;
        case 'demand_prediction':   openTool('demandPrediction'); break;
        case 'ai_recommendations':  openTool('aiRecommendations'); break;
        default:                    flash(key);
    }
}
</script>

<template>
    <Head title="Investment Data" />

    <AdminLayout title="Investment Data" :breadcrumbs="[{ label: 'Admin' }, { label: 'Investment Data' }]">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 rounded-[24px] border border-border bg-card p-6 shadow-card lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-[28px] font-extrabold tracking-[-0.5px] text-foreground">Investment Data</h1>
                    <p class="mt-2 text-sm text-muted-foreground">Analyze company-wide investment performance, market trends and project profitability.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button class="gap-2 rounded-xl bg-gold-gradient px-4 py-2.5 text-sm font-semibold text-on-gold shadow-gold-glow hover:-translate-y-0.5 hover:shadow-gold-glow" @click="openTool('report')">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z" /><path d="M14 3v5h5M9 13h6M9 17h4" /></svg>
                        Generate Investment Report
                    </Button>
                    <Button variant="outline" class="gap-2 rounded-xl border-border px-4 py-2.5 text-sm font-semibold text-foreground/80 hover:bg-muted" @click="openTool('settings')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2" /></svg>
                        Market Settings
                    </Button>
                </div>
            </div>

            <!-- KPI row -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7">
                <div v-for="k in kpis" :key="k.key" class="rounded-[18px] border border-border bg-card px-4 py-4 shadow-card transition hover:-translate-y-0.5 hover:shadow-card-hover">
                    <div class="mb-2.5 flex items-center gap-2">
                        <span class="flex h-[34px] w-[34px] flex-shrink-0 items-center justify-center rounded-[10px]" :style="{ background: k.bg, color: k.color }">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[k.icon]" />
                        </span>
                        <span class="text-[11px] font-semibold leading-tight text-muted-foreground">{{ k.label }}</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-extrabold tracking-tight text-foreground">{{ k.value }}</span>
                        <span v-if="k.suffix" class="text-[13px] font-semibold text-muted-foreground">{{ k.suffix }}</span>
                    </div>
                    <div class="mt-2.5 flex items-end justify-between gap-1.5">
                        <div>
                            <div class="flex items-center gap-0.5 text-xs font-bold text-success">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6" /></svg>
                                {{ k.change }}%
                            </div>
                            <div class="mt-0.5 text-[10px] text-muted-foreground">vs last month</div>
                        </div>
                        <svg width="74" height="30" viewBox="0 0 120 36" fill="none" preserveAspectRatio="none">
                            <polyline :points="sparkPoints(k.spark)" fill="none" :stroke="k.color" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Analytics grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-[2fr_1.15fr_1.15fr_1fr]">

                <!-- Market Growth -->
                <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-card p-5 shadow-card">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="text-base font-bold text-foreground">Market Growth</div>
                        <div class="flex items-center gap-0.5 rounded-[10px] bg-muted p-[3px]">
                            <button v-for="p in mg.periods" :key="p" type="button"
                                class="rounded-lg px-3 py-1.5 text-[11.5px] font-semibold transition"
                                :class="activePeriod === p ? 'bg-gold-gradient text-on-gold' : 'text-muted-foreground hover:text-foreground/80'"
                                @click="activePeriod = p"
                            >{{ p }}</button>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-muted-foreground">Property Value Index (BDT)</div>
                    <div class="relative mt-2.5 flex-1" style="min-height:280px;">
                        <svg viewBox="0 0 660 280" preserveAspectRatio="none" class="block h-full w-full">
                            <line x1="60" y1="24" x2="60" y2="250" stroke="#EEF1F6" stroke-width="1" />
                            <line x1="60" y1="250" x2="640" y2="250" stroke="#EEF1F6" stroke-width="1" />
                            <line v-for="g in mgGridYs.slice(0, 5)" :key="g" x1="60" :y1="g" x2="640" :y2="g" stroke="#F4F6FA" stroke-width="1" />
                            <polyline v-for="c in mgCities" :key="c.name" :points="c.points" fill="none" :stroke="c.color" :stroke-width="c.name === 'Dhaka' ? 3 : 2.6" stroke-linecap="round" stroke-linejoin="round" />
                            <circle v-for="c in mgCities" :key="c.name + '-dot'" :cx="c.lastX" :cy="c.lastY" r="4" :fill="c.color" />
                        </svg>
                        <div v-for="lbl in mgLabels" :key="lbl.text" class="absolute left-0 text-[9.5px] text-muted-foreground" :style="{ top: (lbl.y - 6) + 'px' }">{{ lbl.text }}</div>
                        <div class="absolute right-3.5 top-2 min-w-[166px] rounded-[11px] border border-border bg-card p-2.5 shadow-lg">
                            <div class="mb-1.5 text-[10.5px] font-bold text-muted-foreground">{{ mgTooltipLabel }}</div>
                            <div v-for="c in mgCities" :key="c.name + '-tip'" class="mb-1 flex items-center justify-between gap-3.5 text-[11px] last:mb-0">
                                <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2 w-2 rounded-full" :style="{ background: c.color }" />{{ c.name }}</span>
                                <span class="flex items-center gap-1.5">
                                    <b>{{ mg.unit }} {{ c.lastValue.toLocaleString() }}</b>
                                    <b class="text-success">+{{ c.changePct }}%</b>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pl-14 pt-1.5 text-[10.5px] text-muted-foreground">
                        <span v-for="y in activeDataset.years" :key="y">{{ y }}</span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-4 border-t border-border pt-3.5">
                        <span v-for="c in mgCities" :key="c.name + '-legend'" class="flex items-center gap-1.5 text-[11.5px] font-medium text-foreground/80">
                            <span class="h-2.5 w-2.5 rounded-full" :style="{ background: c.color }" />{{ c.name }}
                        </span>
                    </div>
                    <a class="mt-3.5 flex cursor-pointer items-center justify-center gap-1.5 text-[12.5px] font-semibold text-brand" @click="openTool('forecast')">
                        View Market Report
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                </div>

                <!-- ROI Projection -->
                <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-card p-5 shadow-card">
                    <div class="flex items-center justify-between gap-2.5">
                        <div class="text-base font-bold text-foreground">ROI Projection</div>
                        <Select v-model="activeRoiYear">
                            <SelectTrigger class="h-8 w-[84px] gap-1 rounded-[9px] border-border px-2.5 text-[11.5px] font-semibold text-foreground/80 shadow-none">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="y in roi.years" :key="y" :value="y">{{ y }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="mt-3.5 flex flex-wrap items-center gap-3.5">
                        <span v-for="s in activeRoiSeries" :key="s.label" class="flex items-center gap-1.5 text-[11px] font-medium text-foreground/80">
                            <span class="h-2 w-2 rounded" :style="{ background: s.color }" />{{ s.label }}
                        </span>
                    </div>
                    <div class="relative mt-3.5 flex flex-1 items-end border-b border-border pl-8" style="min-height:260px;">
                        <div class="absolute inset-y-0 left-0 flex flex-col justify-between pb-6 text-right text-[9.5px] text-muted-foreground" style="width:30px;">
                            <span>40%</span><span>30%</span><span>20%</span><span>10%</span><span>0%</span>
                        </div>
                        <div class="flex h-full flex-1 items-end justify-around pb-6">
                            <div v-for="(cat, ci) in roi.categories" :key="cat" class="flex h-full items-end gap-1">
                                <div v-for="s in activeRoiSeries" :key="s.label" class="w-[11px] rounded-t transition-all" :style="{ height: barHeightPct(s.values[ci]) + '%', background: s.color }" />
                            </div>
                        </div>
                        <div class="absolute inset-x-8 bottom-0 flex justify-around text-center text-[9.5px] text-muted-foreground">
                            <span v-for="cat in roi.categories" :key="cat + '-lbl'">{{ cat }}</span>
                        </div>
                    </div>
                    <a class="mt-4 flex cursor-pointer items-center justify-center gap-1.5 text-[12.5px] font-semibold text-brand" @click="openTool('roiCalculator')">
                        View Projection Report
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                </div>

                <!-- Investment Heatmap -->
                <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-card p-5 shadow-card">
                    <div class="flex items-center justify-between gap-2.5">
                        <div class="text-base font-bold text-foreground">Investment Heatmap</div>
                        <div class="flex cursor-pointer items-center gap-1.5 rounded-[9px] border border-border px-2.5 py-1.5 text-[11.5px] font-semibold text-foreground/80">
                            ROI Potential
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9AA3B4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6" /></svg>
                        </div>
                    </div>
                    <div class="relative mt-2.5 flex flex-1 items-center justify-center" style="min-height:240px;">
                        <svg viewBox="0 0 200 250" style="height:240px; max-width:100%;">
                            <path d="M96 14 C112 12 122 22 120 36 L132 42 C144 44 146 58 138 66 L150 78 C162 92 158 112 146 120 L154 142 C158 162 148 178 136 184 L140 200 C136 214 122 220 112 214 L104 224 C92 232 78 226 78 214 L66 210 C52 206 48 190 56 180 L44 166 C34 152 40 134 52 130 L46 108 C40 92 50 78 62 78 L58 56 C56 40 68 28 82 30 L86 18 C88 12 92 13 96 14 Z" fill="#D7F0DD" stroke="#B6E2C2" stroke-width="1.2" />
                            <path d="M62 78 L52 130 L56 180 L78 214 L66 210 L56 180 L44 166 C34 152 40 134 52 130 L46 108 C40 92 50 78 62 78 Z" fill="#C4E9CF" opacity="0.7" />
                            <path d="M120 36 L138 66 L146 120 L136 184 L140 200 C136 214 122 220 112 214 L120 200 L130 160 L132 100 L120 50 Z" fill="#A9DFB8" opacity="0.6" />
                            <g v-for="region in heatmapRegionsWithRings" :key="region.name" style="cursor:pointer;" @click="activeRegionIdx = region.idx">
                                <circle v-for="(ring, ri) in region.rings" :key="ri" :cx="region.x" :cy="region.y" :r="ring.radius" :fill="region.color" :opacity="ring.opacity" />
                            </g>
                        </svg>
                        <div class="absolute right-0 top-1.5 min-w-[142px] rounded-[11px] border border-border bg-card p-2.5 shadow-lg">
                            <div class="mb-2 text-xs font-extrabold text-foreground">{{ activeRegion.name }}</div>
                            <div class="mb-1.5 flex items-center justify-between gap-2.5 text-[10.5px]"><span class="text-muted-foreground">Avg. Price</span><b>{{ activeRegion.avg_price }}</b></div>
                            <div class="mb-1.5 flex items-center justify-between gap-2.5 text-[10.5px]"><span class="text-muted-foreground">ROI</span><b class="text-success">{{ activeRegion.roi }}</b></div>
                            <div class="mb-1.5 flex items-center justify-between gap-2.5 text-[10.5px]"><span class="text-muted-foreground">Rental Yield</span><b>{{ activeRegion.rental_yield }}</b></div>
                            <div class="mb-1.5 flex items-center justify-between gap-2.5 text-[10.5px]"><span class="text-muted-foreground">Projects</span><b>{{ activeRegion.projects }}</b></div>
                            <div class="flex items-center justify-between gap-2.5 text-[10.5px]"><span class="text-muted-foreground">Pop. Growth</span><b>{{ activeRegion.pop_growth }}</b></div>
                        </div>
                        <div class="absolute bottom-1.5 left-0 flex flex-col gap-1.5">
                            <span class="flex items-center gap-1.5 text-[10px] text-muted-foreground"><span class="h-2.5 w-2.5 rounded" style="background:#F87171;" />Very High</span>
                            <span class="flex items-center gap-1.5 text-[10px] text-muted-foreground"><span class="h-2.5 w-2.5 rounded" style="background:#FBBF24;" />High</span>
                            <span class="flex items-center gap-1.5 text-[10px] text-muted-foreground"><span class="h-2.5 w-2.5 rounded" style="background:#34D399;" />Medium</span>
                            <span class="flex items-center gap-1.5 text-[10px] text-muted-foreground"><span class="h-2.5 w-2.5 rounded" style="background:#A9DFB8;" />Low</span>
                        </div>
                    </div>
                    <a class="mt-3.5 flex cursor-pointer items-center justify-center gap-1.5 text-[12.5px] font-semibold text-brand" @click="openTool('demandPrediction')">
                        View Heatmap
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                </div>

                <!-- Investment Summary -->
                <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-card p-5 shadow-card">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-base font-bold text-foreground">Investment Summary</div>
                        <div class="flex cursor-pointer items-center gap-1 text-[11px] font-semibold text-muted-foreground">
                            This Year
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9AA3B4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6" /></svg>
                        </div>
                    </div>
                    <div class="mt-3.5 flex flex-1 flex-col gap-0.5">
                        <div v-for="item in investment_summary" :key="item.label" class="flex items-center justify-between border-b border-border py-2.5 last:border-b-0">
                            <span class="flex items-center gap-2 text-[12.5px] text-foreground/80">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg" :style="{ background: item.bg, color: item.color }">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[item.icon]" />
                                </span>
                                {{ item.label }}
                            </span>
                            <b class="text-[13.5px]" :style="item.highlight ? 'color:#34D399' : ''">{{ item.value }}</b>
                        </div>
                    </div>
                    <a class="mt-2 flex cursor-pointer items-center justify-center gap-1.5 rounded-[11px] bg-gold/10 py-2.5 text-[12.5px] font-semibold text-brand" @click="openTool('report')">
                        View Detailed Report
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </a>
                </div>
            </div>

            <!-- Investment table + right stack -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_360px]">

                <!-- Project Investment Overview -->
                <div id="investment-table" class="min-w-0 scroll-mt-6 rounded-[18px] border border-border bg-card px-2 pb-3.5 pt-4">
                    <div class="flex flex-wrap items-center justify-between gap-4 px-3.5 pb-2">
                        <div class="text-[17px] font-bold text-foreground">Project Investment Overview</div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <Button variant="outline" size="sm" class="gap-1.5 rounded-[10px] border-border text-[13px] font-semibold text-foreground/80" :class="(filterLocation || filterDemand) && 'border-gold/40 bg-gold/10 text-brand'" @click="openTool('filters')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                                Filters<template v-if="filterLocation || filterDemand"> (1)</template>
                            </Button>
                            <Button variant="outline" size="sm" class="gap-1.5 rounded-[10px] border-border text-[13px] font-semibold text-foreground/80" @click="exportFiltered">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14" /></svg>
                                Export
                            </Button>
                            <Button variant="outline" size="sm" class="gap-1.5 rounded-[10px] border-border text-[13px] font-semibold text-foreground/80" :class="compareMode && 'border-gold/40 bg-gold/10 text-brand'" @click="toggleCompareMode">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4M7 4L3 8M7 4l4 4M17 8v12M17 20l4-4M17 20l-4-4" /></svg>
                                {{ compareMode ? 'Cancel Compare' : 'Compare' }}
                            </Button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center border-b border-border px-3.5">
                        <button v-for="t in tabs" :key="t.field + '-' + t.value" type="button"
                            class="border-b-2 px-3 py-2.5 text-[13.5px] font-semibold transition"
                            :class="isActiveTab(t) ? 'border-gold text-brand' : 'border-transparent text-muted-foreground hover:text-foreground'"
                            @click="setTab(t)"
                        >{{ t.label }} ({{ t.count }})</button>
                    </div>

                    <div class="overflow-x-auto">
                        <Table class="min-w-[1080px]">
                            <TableHeader>
                                <TableRow class="hover:bg-transparent">
                                    <TableHead v-if="compareMode" class="h-auto w-10 py-3.5 pl-4"></TableHead>
                                    <TableHead class="h-auto py-3.5 pl-4 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Project</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Location</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Investment (BDT)</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Current Value</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">ROI</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Rental</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Occupancy</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Demand</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Growth YoY</TableHead>
                                    <TableHead class="h-auto py-3.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="pagedProjects.length === 0">
                                    <TableCell :colspan="compareMode ? 11 : 10" class="py-10 text-center text-sm text-muted-foreground">No projects match this filter.</TableCell>
                                </TableRow>
                                <TableRow v-for="p in pagedProjects" :key="p.id" class="cursor-pointer" :class="compareSelection.includes(p.id) && 'bg-gold/10'">
                                    <TableCell v-if="compareMode" class="py-3 pl-4" @click.stop="toggleCompareSelect(p.id)">
                                        <input type="checkbox" class="h-4 w-4 accent-[#C6A15B]" :checked="compareSelection.includes(p.id)" @click.stop @change="toggleCompareSelect(p.id)" />
                                    </TableCell>
                                    <TableCell class="py-3 pl-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px]" :style="{ background: rowColors(p.id)[0], color: rowColors(p.id)[1] }">
                                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h.01M15 17h.01" /></svg>
                                            </span>
                                            <span class="whitespace-nowrap text-[13px] font-bold text-foreground">{{ p.name }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap py-3 text-[12.5px] text-foreground/80">{{ p.location }}</TableCell>
                                    <TableCell class="whitespace-nowrap py-3 text-[13px] font-semibold text-foreground">{{ formatBDT(p.investment) }}</TableCell>
                                    <TableCell class="whitespace-nowrap py-3 text-[13px] font-bold text-foreground">{{ formatBDT(p.value) }}</TableCell>
                                    <TableCell class="py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-10 text-[12.5px] font-bold text-success">{{ p.roi }}%</span>
                                            <div class="h-1.5 min-w-[54px] flex-1 overflow-hidden rounded-full bg-success/15">
                                                <div class="h-full rounded-full bg-success" :style="{ width: roiWidth(p.roi) + '%' }" />
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap py-3 text-[12.5px] font-semibold text-foreground/80">{{ p.rental }}%</TableCell>
                                    <TableCell class="py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-8 text-[12.5px] font-semibold text-foreground">{{ p.occupancy }}%</span>
                                            <div class="h-1.5 min-w-[54px] flex-1 overflow-hidden rounded-full bg-info/15">
                                                <div class="h-full rounded-full bg-info" :style="{ width: p.occupancy + '%' }" />
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <Badge variant="outline" class="whitespace-nowrap rounded-full border-transparent px-2.5 py-1 text-[11px] font-bold" :style="{ background: demandStyle[p.demand]?.bg, color: demandStyle[p.demand]?.color }">{{ p.demand }}</Badge>
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <span class="flex items-center gap-1 text-[12.5px] font-bold text-success">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8" /></svg>
                                            {{ p.growth_yoy }}%
                                        </span>
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <Badge variant="outline" class="whitespace-nowrap rounded-full border-transparent px-2.5 py-1 text-[11px] font-bold" :style="{ background: statusStyle[p.status]?.bg, color: statusStyle[p.status]?.color }">{{ p.status }}</Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2.5 border-t border-border px-3.5 pb-1 pt-4">
                        <span class="text-[12.5px] text-muted-foreground">
                            Showing {{ filteredProjects.length === 0 ? 0 : (currentPage - 1) * perPage + 1 }}
                            to {{ Math.min(currentPage * perPage, filteredProjects.length) }}
                            of {{ filteredProjects.length }} projects
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" class="flex h-[34px] w-[34px] items-center justify-center rounded-[9px] border border-border text-muted-foreground disabled:opacity-40" :disabled="currentPage === 1" @click="goPage(currentPage - 1)">‹</button>
                            <button v-for="n in totalPages" :key="n" type="button"
                                class="flex h-[34px] w-[34px] items-center justify-center rounded-[9px] text-[13px] font-semibold"
                                :class="currentPage === n ? 'bg-gold-gradient text-on-gold' : 'border border-border text-foreground/80'"
                                @click="goPage(n)"
                            >{{ n }}</button>
                            <button type="button" class="flex h-[34px] w-[34px] items-center justify-center rounded-[9px] border border-border text-muted-foreground disabled:opacity-40" :disabled="currentPage === totalPages" @click="goPage(currentPage + 1)">›</button>
                        </div>
                    </div>
                </div>

                <!-- Right stack -->
                <div class="flex min-w-0 flex-col gap-6">

                    <!-- Top Performing Projects -->
                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-1.5 flex items-center justify-between">
                            <div class="text-base font-bold text-foreground">Top Performing Projects</div>
                            <a class="cursor-pointer text-xs font-semibold text-brand" @click="openTool('marketAnalysis')">View All</a>
                        </div>
                        <div class="flex flex-col">
                            <div v-for="t in topPerformingProjects" :key="t.rank" class="flex items-center gap-2.5 border-t border-border py-2.5 first:border-t-0">
                                <div class="w-3.5 flex-shrink-0 text-[13px] font-extrabold text-muted-foreground/50">{{ t.rank }}</div>
                                <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px]" :style="{ background: rowColorPairs[(t.rank - 1) % rowColorPairs.length][0], color: rowColorPairs[(t.rank - 1) % rowColorPairs.length][1] }">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01" /></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-[13px] font-bold text-foreground">{{ t.name }}</div>
                                    <div class="mt-0.5 flex items-center gap-2.5">
                                        <span class="text-[11px] text-muted-foreground">ROI <b class="text-foreground/80">{{ t.roi }}</b></span>
                                        <span class="text-[11px] text-muted-foreground">{{ t.rental }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-shrink-0 items-center gap-0.5 text-xs font-bold text-success">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8" /></svg>
                                    {{ t.growth }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Market Insights -->
                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-2 flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <div class="text-base font-bold text-foreground">Market Insights</div>
                                <span class="flex items-center gap-1 rounded-full bg-gold/10 px-2 py-0.5 text-[10px] font-bold text-brand">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z" /></svg>
                                    AI
                                </span>
                            </div>
                            <a class="cursor-pointer text-xs font-semibold text-brand" @click="openTool('aiRecommendations')">View All</a>
                        </div>
                        <div class="flex max-h-[280px] flex-col overflow-y-auto">
                            <div v-for="(ins, i) in insights" :key="i" class="flex items-start gap-2.5 border-t border-border py-3 first:border-t-0">
                                <span class="flex h-[30px] w-[30px] flex-shrink-0 items-center justify-center rounded-[9px]" :style="{ background: ins.bg, color: ins.color }">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z" /></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[12.5px] font-semibold leading-snug text-foreground">{{ ins.text }}</div>
                                    <div class="mt-1 text-[11px] text-muted-foreground">{{ ins.time }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Investment Actions -->
            <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                <div class="mb-3.5 text-base font-bold text-foreground">Quick Investment Actions</div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-9">
                    <button v-for="qa in quick_actions" :key="qa.key" type="button"
                        class="flex flex-col items-center gap-2 rounded-[14px] border border-border px-1.5 py-3.5 transition hover:-translate-y-0.5"
                        @click="handleQuickAction(qa.key)"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-[10px]" :style="{ background: qa.bg, color: qa.color }">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[qa.icon]" />
                        </span>
                        <span class="text-center text-[11px] font-semibold text-foreground/80">{{ qa.label }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating "compare selected" bar -->
        <Teleport to="body">
            <div v-if="compareMode && compareSelection.length > 0" class="fixed bottom-6 left-1/2 z-[110] flex -translate-x-1/2 items-center gap-3 rounded-2xl bg-slate-900 px-5 py-3 text-white shadow-2xl">
                <span class="text-sm font-semibold">{{ compareSelection.length }} project{{ compareSelection.length > 1 ? 's' : '' }} selected</span>
                <button type="button" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20" @click="compareSelection = []">Clear</button>
                <button type="button" class="rounded-lg bg-gold-gradient px-3 py-1.5 text-xs font-semibold text-on-gold hover:shadow-gold-glow" :disabled="compareSelection.length < 2" :class="compareSelection.length < 2 && 'cursor-not-allowed opacity-50'" @click="openTool('compareView')">Compare Now</button>
            </div>
        </Teleport>

        <!-- Filters drawer -->
        <Teleport to="body">
            <div v-if="activeTool === 'filters'" class="fixed inset-0 z-50" @click="closeTool">
                <div class="absolute inset-0 bg-black/40" />
                <div class="absolute inset-y-0 right-0 w-full max-w-[380px] bg-card shadow-2xl" @click.stop>
                    <div class="flex items-center justify-between border-b border-border px-6 py-5">
                        <div>
                            <div class="text-[17px] font-bold text-foreground">Filter Projects</div>
                            <div class="mt-0.5 text-xs text-muted-foreground">Refine the investment table below.</div>
                        </div>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted text-muted-foreground hover:bg-muted/70" @click="closeTool">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="flex flex-col gap-4 p-6">
                        <div>
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">Location</div>
                            <select v-model="filterLocation" class="h-[42px] w-full rounded-xl border border-border px-3 text-sm text-foreground/80 outline-none focus:border-gold">
                                <option value="">Any location</option>
                                <option v-for="loc in uniqueLocations" :key="loc" :value="loc">{{ loc }}</option>
                            </select>
                        </div>
                        <div>
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">Demand</div>
                            <select v-model="filterDemand" class="h-[42px] w-full rounded-xl border border-border px-3 text-sm text-foreground/80 outline-none focus:border-gold">
                                <option value="">Any demand level</option>
                                <option v-for="lvl in Object.keys(demandStyle)" :key="lvl" :value="lvl">{{ lvl }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 border-t border-border bg-muted px-6 py-4">
                        <button class="flex-1 rounded-xl border border-border bg-card py-2.5 text-sm font-semibold text-foreground/80 hover:bg-muted" @click="resetFilters">Reset</button>
                        <button class="flex-[1.5] rounded-xl bg-gold-gradient py-2.5 text-sm font-bold text-on-gold shadow-gold-glow" @click="applyFilters">Apply Filters</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Generic tool dialog: report / settings / analysis / calculators / compare view -->
        <Teleport to="body">
            <div v-if="activeTool && activeTool !== 'filters'" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click="closeTool">
                <div class="absolute inset-0 bg-black/45" />
                <div class="relative z-10 max-h-[85vh] w-full overflow-y-auto rounded-2xl bg-card p-6 shadow-2xl" :class="activeTool === 'report' || activeTool === 'compareView' ? 'max-w-2xl' : 'max-w-md'" @click.stop>
                    <div class="mb-5 flex items-center justify-between">
                        <div class="text-[17px] font-bold text-foreground">
                            <template v-if="activeTool === 'report'">Investment Report</template>
                            <template v-else-if="activeTool === 'settings'">Market Settings</template>
                            <template v-else-if="activeTool === 'marketAnalysis'">Market Analysis by Location</template>
                            <template v-else-if="activeTool === 'roiCalculator'">ROI Calculator</template>
                            <template v-else-if="activeTool === 'forecast'">Property Value Forecast</template>
                            <template v-else-if="activeTool === 'priceSimulator'">Price Simulator</template>
                            <template v-else-if="activeTool === 'demandPrediction'">Demand Prediction</template>
                            <template v-else-if="activeTool === 'aiRecommendations'">AI Recommendations</template>
                            <template v-else-if="activeTool === 'compareView'">Compare Projects</template>
                        </div>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted text-muted-foreground hover:bg-muted/70" @click="closeTool">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Investment Report -->
                    <div v-if="activeTool === 'report'">
                        <div class="mb-4 text-xs text-muted-foreground">Generated {{ reportGeneratedAt }}</div>
                        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div v-for="k in kpis" :key="k.key" class="rounded-xl border border-border p-3">
                                <div class="text-[10.5px] font-semibold text-muted-foreground">{{ k.label }}</div>
                                <div class="mt-1 text-base font-extrabold text-foreground">{{ k.value }}{{ k.suffix }}</div>
                            </div>
                        </div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-muted-foreground">Investment Summary</div>
                        <div class="mb-5 flex flex-col">
                            <div v-for="item in investment_summary" :key="item.label" class="flex items-center justify-between border-b border-border py-2 last:border-b-0">
                                <span class="text-[12.5px] text-muted-foreground">{{ item.label }}</span>
                                <b class="text-[13px]">{{ item.value }}</b>
                            </div>
                        </div>
                        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-muted-foreground">Top Performing Projects</div>
                        <div class="mb-5 flex flex-col">
                            <div v-for="t in topPerformingProjects" :key="t.rank" class="flex items-center justify-between border-b border-border py-2 last:border-b-0 text-[12.5px]">
                                <span class="text-foreground/80">{{ t.rank }}. {{ t.name }}</span>
                                <span class="font-bold text-success">ROI {{ t.roi }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2.5">
                            <button class="flex-1 rounded-xl border border-border py-2.5 text-sm font-semibold text-foreground/80 hover:bg-muted" @click="exportAll">Download Data (CSV)</button>
                            <button class="flex-1 rounded-xl bg-gold-gradient py-2.5 text-sm font-bold text-on-gold shadow-gold-glow" @click="printReport">Print / Save as PDF</button>
                        </div>
                    </div>

                    <!-- Market Settings -->
                    <div v-else-if="activeTool === 'settings'">
                        <div class="mb-5">
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">Table number format</div>
                            <div class="flex gap-2">
                                <button type="button" class="flex-1 rounded-xl border px-3 py-2.5 text-xs font-semibold" :class="!compactNumbers ? 'border-gold bg-gold/10 text-brand' : 'border-border text-muted-foreground'" @click="compactNumbers = false">Full — BDT 1,850,000,000</button>
                                <button type="button" class="flex-1 rounded-xl border px-3 py-2.5 text-xs font-semibold" :class="compactNumbers ? 'border-gold bg-gold/10 text-brand' : 'border-border text-muted-foreground'" @click="compactNumbers = true">Compact — BDT 1.85B</button>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">Default heatmap region</div>
                            <select v-model.number="activeRegionIdx" class="h-[42px] w-full rounded-xl border border-border px-3 text-sm text-foreground/80 outline-none focus:border-gold">
                                <option v-for="(r, i) in heatmap_regions" :key="r.name" :value="i">{{ r.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Market Analysis -->
                    <div v-else-if="activeTool === 'marketAnalysis'" class="overflow-x-auto">
                        <table class="w-full min-w-[480px] border-collapse text-sm">
                            <thead>
                                <tr>
                                    <th class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">Location</th>
                                    <th class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">Projects</th>
                                    <th class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">Investment</th>
                                    <th class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">Avg ROI</th>
                                    <th class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">Avg Rental</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in marketAnalysisRows" :key="row.location" class="border-t border-border">
                                    <td class="px-2 py-2.5 font-semibold text-foreground">{{ row.location }}</td>
                                    <td class="px-2 py-2.5 text-muted-foreground">{{ row.count }}</td>
                                    <td class="px-2 py-2.5 text-muted-foreground">{{ formatBDT(row.investment) }}</td>
                                    <td class="px-2 py-2.5 font-bold text-success">{{ row.avgRoi }}%</td>
                                    <td class="px-2 py-2.5 text-muted-foreground">{{ row.avgRental }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ROI Calculator -->
                    <div v-else-if="activeTool === 'roiCalculator'">
                        <div class="mb-4">
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">Investment amount (BDT)</div>
                            <input v-model.number="calcAmount" type="number" min="0" step="10000" class="h-[42px] w-full rounded-xl border border-border px-3 text-sm outline-none focus:border-gold" />
                        </div>
                        <div class="mb-5">
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">Reference project</div>
                            <select v-model.number="calcProjectId" class="h-[42px] w-full rounded-xl border border-border px-3 text-sm text-foreground/80 outline-none focus:border-gold">
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }} ({{ p.roi }}% ROI)</option>
                            </select>
                        </div>
                        <div v-if="calcResult" class="flex flex-col gap-2 rounded-xl bg-muted p-4">
                            <div class="flex items-center justify-between text-sm"><span class="text-muted-foreground">Projected Value (Year 1)</span><b class="text-foreground">{{ formatBDT(calcResult.projectedValue) }}</b></div>
                            <div class="flex items-center justify-between text-sm"><span class="text-muted-foreground">Annual Rental Income</span><b class="text-foreground">{{ formatBDT(calcResult.annualRental) }}</b></div>
                            <div class="flex items-center justify-between text-sm"><span class="text-muted-foreground">Capital Gain</span><b class="text-success">{{ formatBDT(calcResult.capitalGain) }}</b></div>
                        </div>
                    </div>

                    <!-- Forecast -->
                    <div v-else-if="activeTool === 'forecast'" class="overflow-x-auto">
                        <table class="w-full min-w-[420px] border-collapse text-sm">
                            <thead>
                                <tr>
                                    <th class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">City</th>
                                    <th class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">Growth Rate</th>
                                    <th v-for="y in forecastYearLabels" :key="y" class="px-2 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">{{ y }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in forecastRows" :key="row.name" class="border-t border-border">
                                    <td class="px-2 py-2.5 font-semibold text-foreground"><span class="mr-1.5 inline-block h-2 w-2 rounded-full" :style="{ background: row.color }" />{{ row.name }}</td>
                                    <td class="px-2 py-2.5 font-bold text-success">{{ row.ratePct }}%</td>
                                    <td v-for="(val, i) in row.future" :key="i" class="px-2 py-2.5 text-foreground/80">{{ mg.unit }} {{ val.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="mt-3 text-xs text-muted-foreground">Projected using each city's own historical compound growth rate from the Market Growth chart.</p>
                    </div>

                    <!-- Price Simulator -->
                    <div v-else-if="activeTool === 'priceSimulator'">
                        <div class="mb-4">
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">City</div>
                            <select v-model="simCity" class="h-[42px] w-full rounded-xl border border-border px-3 text-sm text-foreground/80 outline-none focus:border-gold">
                                <option v-for="c in mgMaster.cities" :key="c.name" :value="c.name">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="mb-5">
                            <div class="mb-1.5 flex items-center justify-between text-xs font-semibold text-muted-foreground">
                                <span>Years ahead</span><span>{{ simYears }} year{{ simYears > 1 ? 's' : '' }}</span>
                            </div>
                            <input v-model.number="simYears" type="range" min="1" max="10" step="1" class="w-full accent-[#C6A15B]" />
                        </div>
                        <div class="flex flex-col gap-2 rounded-xl bg-muted p-4 text-sm">
                            <div class="flex items-center justify-between"><span class="text-muted-foreground">Current price/sqft</span><b class="text-foreground">{{ mg.unit }} {{ simResult.current.toLocaleString() }}</b></div>
                            <div class="flex items-center justify-between"><span class="text-muted-foreground">Projected price/sqft</span><b class="text-success">{{ mg.unit }} {{ simResult.projected.toLocaleString() }}</b></div>
                            <div class="flex items-center justify-between"><span class="text-muted-foreground">Est. annual growth</span><b class="text-foreground">{{ simResult.perYearRatePct }}%</b></div>
                        </div>
                    </div>

                    <!-- Demand Prediction -->
                    <div v-else-if="activeTool === 'demandPrediction'">
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-xl bg-muted p-4"><div class="text-[11px] font-semibold text-muted-foreground">Current</div><div class="mt-1 text-xl font-extrabold text-foreground">{{ demandForecast.current }}</div></div>
                            <div class="rounded-xl bg-muted p-4"><div class="text-[11px] font-semibold text-muted-foreground">Next Quarter</div><div class="mt-1 text-xl font-extrabold text-foreground">{{ demandForecast.q1 }}</div></div>
                            <div class="rounded-xl bg-muted p-4"><div class="text-[11px] font-semibold text-muted-foreground">In 2 Quarters</div><div class="mt-1 text-xl font-extrabold text-success">{{ demandForecast.q2 }}</div></div>
                        </div>
                        <p class="mt-4 text-xs text-muted-foreground">Projected from the Market Demand Index KPI's own month-over-month change, compounded per quarter.</p>
                        <div class="mt-4 flex flex-col gap-2">
                            <div v-for="r in heatmap_regions" :key="r.name" class="flex items-center justify-between rounded-lg border border-border px-3 py-2 text-sm">
                                <span class="font-semibold text-foreground/80">{{ r.name }}</span>
                                <Badge variant="outline" class="border-transparent text-[11px] font-bold" :style="{ background: demandStyle[r.level]?.bg, color: demandStyle[r.level]?.color }">{{ r.level }}</Badge>
                            </div>
                        </div>
                    </div>

                    <!-- AI Recommendations -->
                    <div v-else-if="activeTool === 'aiRecommendations'" class="flex flex-col gap-3">
                        <div v-for="(rec, i) in aiRecommendations" :key="i" class="flex items-start gap-3 rounded-xl border border-border p-3">
                            <span class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg" :style="{ background: rec.bg, color: rec.color }">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4z" /></svg>
                            </span>
                            <div class="text-[13px] leading-snug text-foreground/80">{{ rec.text }}</div>
                        </div>
                    </div>

                    <!-- Compare View -->
                    <div v-else-if="activeTool === 'compareView'" class="overflow-x-auto">
                        <table class="w-full min-w-[560px] border-collapse text-sm">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase text-muted-foreground">Metric</th>
                                    <th v-for="p in compareProjects" :key="p.id" class="px-3 py-2 text-left text-[12.5px] font-bold text-foreground">{{ p.name }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in compareRows" :key="row.label" class="border-t border-border">
                                    <td class="px-3 py-2 text-xs font-semibold text-muted-foreground">{{ row.label }}</td>
                                    <td v-for="p in compareProjects" :key="p.id + row.label" class="px-3 py-2 text-sm font-semibold text-foreground">{{ row.get(p) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Demo toast -->
        <Teleport to="body">
            <div v-if="flashMsg" class="fixed bottom-6 left-1/2 z-[120] -translate-x-1/2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-2xl">
                {{ flashMsg }}
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
@media print {
    :deep(aside),
    :deep(header) {
        display: none !important;
    }
}
</style>
