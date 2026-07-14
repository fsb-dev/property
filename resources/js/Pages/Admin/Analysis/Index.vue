<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useTheme } from '@/composables/useTheme';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

const props = defineProps({
    kpis:               { type: Array,  required: true },
    revenueOverview:    { type: Object, required: true },
    salesByProject:     { type: Object, required: true },
    profitMargin:       { type: Object, required: true },
    performance:        { type: Object, required: true },
    topProjects:        { type: Array,  required: true },
    funnel:             { type: Object, required: true },
    buyerDemographics:  { type: Object, required: true },
    monthlyComparison:  { type: Object, required: true },
    reportShortcuts:    { type: Array,  required: true },
    recentReports:      { type: Array,  required: true },
    lastUpdated:        { type: String, required: true },
});

// ── Icon paths, keyed by the `icon` field coming from the JSON ────────────────
const icons = {
    revenue:  '<circle cx="12" cy="12" r="9"/><path d="M14.5 9a2.5 2.5 0 00-2.5-1.5c-1.5 0-2.5.8-2.5 2s1 1.7 2.5 2 2.5 1 2.5 2-1 2-2.5 2A2.5 2.5 0 019.5 15M12 6v1.5M12 16.5V18"/>',
    sales:    '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>',
    units:    '<path d="M3 21h18M6 21V9M11 21V5M16 21v-8M21 21V11"/>',
    profit:   '<path d="M21.2 15.9A10 10 0 118.1 2.8"/><path d="M22 12A10 10 0 0012 2v10z"/>',
    projects: '<path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
    buyers:   '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
    target:   '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
    doc:      '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
};

// ── ApexCharts shared theming (mode-aware chrome; series colours stay fixed) ─
const { isDark } = useTheme();
const chartMuted = computed(() => (isDark.value ? '#A8A399' : '#6B6355'));
const chartGrid  = computed(() => (isDark.value ? 'rgba(255,255,255,0.06)' : 'rgba(26,22,17,0.08)'));
const chartTheme = computed(() => (isDark.value ? 'dark' : 'light'));

function donutChartOptions(labels, colors, totalLabel, totalValue, valueFmt) {
    return {
        chart: { type: 'donut', fontFamily: 'Plus Jakarta Sans, sans-serif' },
        labels,
        colors,
        legend: { show: false },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: [isDark.value ? '#151922' : '#FFFFFF'] },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: totalLabel,
                            color: chartMuted.value,
                            fontSize: '10px',
                            formatter: () => String(totalValue),
                        },
                        value: { color: isDark.value ? '#F5F2EA' : '#1A1611', fontSize: '22px', fontWeight: 800, offsetY: -4 },
                    },
                },
            },
        },
        tooltip: { theme: chartTheme.value, y: valueFmt ? { formatter: valueFmt } : undefined },
    };
}

// ── Revenue Overview — ApexCharts area (gold, primary metric) — year switcher ─
const revenueYearOptions = computed(() => props.revenueOverview.years.map(y => y.year));
const selectedRevenueYear = ref(revenueYearOptions.value[0]);
const revenueYearData = computed(() => props.revenueOverview.years.find(y => y.year === selectedRevenueYear.value) ?? props.revenueOverview.years[0]);

const revenueChartSeries = computed(() => [{ name: 'Revenue', data: revenueYearData.value.months.map(m => m.value) }]);
const revenueChartOptions = computed(() => ({
    chart: { type: 'area', fontFamily: 'Plus Jakarta Sans, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
    colors: ['#C6A15B'],
    stroke: { curve: 'smooth', width: 3 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0, stops: [0, 90, 100] } },
    markers: { size: 0, hover: { size: 6 }, colors: ['#C6A15B'], strokeColors: isDark.value ? '#151922' : '#FFFFFF', strokeWidth: 3 },
    dataLabels: { enabled: false },
    grid: { borderColor: chartGrid.value, strokeDashArray: 0, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } }, padding: { left: 8, right: 8 } },
    xaxis: {
        categories: revenueYearData.value.months.map(m => m.label),
        axisBorder: { show: false }, axisTicks: { show: false },
        labels: { style: { colors: chartMuted.value, fontSize: '10px' } },
    },
    yaxis: { labels: { style: { colors: chartMuted.value, fontSize: '10px' }, formatter: (v) => `${v}M` } },
    tooltip: { theme: chartTheme.value, y: { formatter: (v) => `BDT ${v}M` } },
}));

// ── Sales by Project donut (r=60) — year switcher ─────────────────────────────
const salesYearOptions = computed(() => props.salesByProject.years.map(y => y.year));
const selectedSalesYear = ref(salesYearOptions.value[0]);
const salesYearData = computed(() => props.salesByProject.years.find(y => y.year === selectedSalesYear.value) ?? props.salesByProject.years[0]);

// ── Sales by Project — ApexCharts donut ───────────────────────────────────────
const salesChartSeries = computed(() => salesYearData.value.segments.map(s => s.pct));
const salesChartOptions = computed(() => donutChartOptions(
    salesYearData.value.segments.map(s => s.name),
    salesYearData.value.segments.map(s => s.color),
    'Total Sales',
    salesYearData.value.total,
    (v, { seriesIndex }) => salesYearData.value.segments[seriesIndex]?.value ?? `${v}%`,
));

// ── Buyer Demographics — ApexCharts donut — year switcher ────────────────────
const buyerYearOptions = computed(() => props.buyerDemographics.years.map(y => y.year));
const selectedBuyerYear = ref(buyerYearOptions.value[0]);
const buyerYearData = computed(() => props.buyerDemographics.years.find(y => y.year === selectedBuyerYear.value) ?? props.buyerDemographics.years[0]);
const buyerChartSeries = computed(() => buyerYearData.value.segments.map(s => s.pct));
const buyerChartOptions = computed(() => donutChartOptions(
    buyerYearData.value.segments.map(s => s.name),
    buyerYearData.value.segments.map(s => s.color),
    'Total Buyers',
    buyerYearData.value.total,
    (v, { seriesIndex }) => buyerYearData.value.segments[seriesIndex]?.value ?? `${v}%`,
));

// ── Sales Funnel Analysis — year switcher (kept as a custom SVG; ApexCharts
// has no first-class funnel type that matches this trapezoid shape) ─────────
const funnelYearOptions = computed(() => props.funnel.years.map(y => y.year));
const selectedFunnelYear = ref(funnelYearOptions.value[0]);
const funnelYearData = computed(() => props.funnel.years.find(y => y.year === selectedFunnelYear.value) ?? props.funnel.years[0]);

// ── Profit & Margin Analysis — ApexCharts combo (bar + line) — year switcher ─
const profitYearOptions = computed(() => props.profitMargin.years.map(y => y.year));
const selectedProfitYear = ref(profitYearOptions.value[0]);
const profitMonths = computed(() => props.profitMargin.years.find(y => y.year === selectedProfitYear.value)?.months ?? []);

const profitChartSeries = computed(() => [
    { name: 'Gross Profit (BDT)', type: 'column', data: profitMonths.value.map(m => m.profit) },
    { name: 'Profit Margin (%)', type: 'line', data: profitMonths.value.map(m => m.margin) },
]);
const profitChartOptions = computed(() => ({
    chart: { type: 'line', fontFamily: 'Plus Jakarta Sans, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
    colors: ['#C6A15B', '#34D399'],
    stroke: { width: [0, 3], curve: 'smooth' },
    markers: { size: [0, 4], colors: ['#34D399'], strokeColors: isDark.value ? '#151922' : '#FFFFFF', strokeWidth: 2 },
    plotOptions: { bar: { columnWidth: '45%', borderRadius: 3 } },
    dataLabels: { enabled: false },
    grid: { borderColor: chartGrid.value, xaxis: { lines: { show: false } } },
    xaxis: {
        categories: profitMonths.value.map(m => m.label),
        axisBorder: { show: false }, axisTicks: { show: false },
        labels: { style: { colors: chartMuted.value, fontSize: '9.5px' } },
    },
    yaxis: [
        { seriesName: 'Gross Profit (BDT)', labels: { style: { colors: chartMuted.value, fontSize: '9.5px' }, formatter: (v) => `${v}M` } },
        { seriesName: 'Profit Margin (%)', opposite: true, labels: { style: { colors: chartMuted.value, fontSize: '9.5px' }, formatter: (v) => `${v}%` } },
    ],
    legend: { show: false },
    tooltip: { theme: chartTheme.value, shared: true },
}));

// ── Monthly Comparison — ApexCharts grouped bar — metric switcher ────────────
const comparisonMetricOptions = computed(() => props.monthlyComparison.metrics.map(m => m.key));
const selectedComparisonMetric = ref(comparisonMetricOptions.value[0]);
const comparisonMetricData = computed(() =>
    props.monthlyComparison.metrics.find(m => m.key === selectedComparisonMetric.value) ?? props.monthlyComparison.metrics[0]
);
const comparisonAxisLabels = computed(() => {
    const { axisMax, unitPrefix, unitSuffix } = comparisonMetricData.value;
    const fmt = (v) => `${unitPrefix}${v}${unitSuffix}`;
    return [
        { y: 20,  text: fmt(axisMax) },
        { y: 62,  text: fmt(Math.round(axisMax * 2 / 3)) },
        { y: 104, text: fmt(Math.round(axisMax / 3)) },
        { y: 146, text: '0' },
    ];
});
const comparisonBars = computed(() => {
    const { axisMax, months } = comparisonMetricData.value;
    const scaleH = (v) => (v / axisMax) * 152;
    const n = months.length || 1;
    const step = n > 1 ? 386 / (n - 1) : 0;
    return months.map((m, i) => {
        const lastH = scaleH(m.lastYear);
        const thisH = scaleH(m.thisYear);
        return { label: m.label, x: 44 + i * step, lastH, lastY: 172 - lastH, thisH, thisY: 172 - thisH };
    });
});

// ── Performance Overview tabs — switches the metric cards below ──────────────
const activeTab = ref(props.performance.tabs[0]);
const activeMetrics = computed(() => props.performance.metricsByTab[activeTab.value] ?? []);

// ── Sparkline path builder for Performance Overview metric cards ─────────────
function sparkPoints(values) {
    const n = values.length || 1;
    const max = Math.max(...values, 1);
    const step = n > 1 ? 100 / (n - 1) : 0;
    return values.map((v, i) => `${(i * step).toFixed(1)},${(26 - (v / max) * 24).toFixed(1)}`).join(' ');
}

// ── Report Shortcuts → mapped to the matching detail dialog ──────────────────
const showRevenueReport = ref(false);
const showSalesReport = ref(false);
const showProfitReport = ref(false);
const showBuyerReport = ref(false);
const showComparisonReport = ref(false);
const showTopProjects = ref(false);
const showRecentReports = ref(false);
const showComingSoon = ref(false);
const comingSoonLabel = ref('');

function openComingSoon(label) {
    comingSoonLabel.value = label;
    showComingSoon.value = true;
}

function openShortcut(shortcut) {
    switch (shortcut.key) {
        case 'sales_perf': showSalesReport.value = true; break;
        case 'financial':  showProfitReport.value = true; break;
        case 'project':    showTopProjects.value = true; break;
        case 'buyer':      showBuyerReport.value = true; break;
        default:           openComingSoon(shortcut.name);
    }
}

// ── Create Report dialog — client-side only, nothing is sent to the server ───
const reportTypeOptions = [
    { value: 'Sales Performance',  color: '#C6A15B', bg: 'rgba(198,161,91,0.12)' },
    { value: 'Financial Summary',  color: '#FBBF24', bg: 'rgba(251,191,36,0.15)' },
    { value: 'Project Progress',   color: '#F87171', bg: 'rgba(248,113,113,0.15)' },
    { value: 'Buyer Demographics', color: '#60A5FA', bg: 'rgba(96,165,250,0.15)' },
    { value: 'Marketing Campaign', color: '#34D399', bg: 'rgba(52,211,153,0.15)' },
    { value: 'Inventory Status',   color: '#22D3EE', bg: 'rgba(34,211,238,0.15)' },
];
const reportFormatOptions = ['PDF', 'Excel', 'CSV'];

const localRecentReports = ref([...props.recentReports]);
const showCreateReport = ref(false);
const createReportForm = ref({ title: '', type: reportTypeOptions[0].value, format: 'PDF' });

function openCreateReport() {
    createReportForm.value = { title: '', type: reportTypeOptions[0].value, format: 'PDF' };
    showCreateReport.value = true;
}

function submitCreateReport() {
    const f = createReportForm.value;
    if (!f.title.trim()) return;

    const type = reportTypeOptions.find(t => t.value === f.type) ?? reportTypeOptions[0];
    const now = new Date();
    const gen = `Generated ${now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}, ${now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;

    localRecentReports.value.unshift({ name: f.title.trim(), gen, color: type.color, bg: type.bg });
    showCreateReport.value = false;
}

// ── Report Settings dialog — client-side only, nothing is sent to the server ─
const showReportSettings = ref(false);
const reportSettings = ref({
    defaultFormat: 'PDF',
    retention: '12 months',
    autoGenerate: true,
    emailAdmins: false,
});

function saveReportSettings() {
    showReportSettings.value = false;
}
</script>

<template>
    <Head title="Analytics & Reports" />

    <AdminLayout title="Analytics & Reports" :breadcrumbs="[{ label: 'Admin' }, { label: 'Analytics & Reports' }]">
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-foreground">
                    Analytics &amp; Reports
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Comprehensive insights and data analysis to drive smarter business decisions.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openCreateReport" class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-on-gold transition-colors hover:bg-admin-accent/90">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M12 11v6M9 14h6"/></svg>
                    Create Report
                </button>
                <button @click="showReportSettings = true" class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg>
                    Report Settings
                </button>
            </div>
        </div>

        <!-- KPI row -->
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
            <div v-for="k in kpis" :key="k.key" class="rounded-2xl border border-border bg-admin-surface-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-2.5 flex items-center gap-2">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: k.bg, color: k.color }">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[k.icon]" />
                    </div>
                    <span class="text-[11.5px] font-semibold leading-tight text-muted-foreground">{{ k.label }}</span>
                </div>
                <div class="text-xl font-extrabold leading-none tracking-tight text-foreground">{{ k.value }}</div>
                <div class="mt-2.5 flex items-center gap-1 text-[11px]">
                    <span class="flex items-center gap-0.5 font-bold text-success">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                        {{ k.change }}{{ k.suffix }}
                    </span>
                    <span class="text-muted-foreground">vs last year</span>
                </div>
            </div>
        </div>

        <!-- Primary analytics row -->
        <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

            <!-- Revenue Overview -->
            <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                <div class="mb-3.5 flex items-center justify-between">
                    <div class="text-base font-bold text-foreground">Revenue Overview</div>
                    <Select v-model="selectedRevenueYear">
                        <SelectTrigger class="h-auto w-auto gap-1.5 rounded-[9px] border-border px-2.5 py-1.5 text-[11px] font-semibold text-foreground/80 [&>svg]:h-3 [&>svg]:w-3">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="y in revenueYearOptions" :key="y" :value="y">{{ y }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="text-[11.5px] text-muted-foreground">Total Revenue</div>
                <div class="mt-0.5 flex items-center gap-2">
                    <div class="text-[22px] font-extrabold tracking-tight text-foreground">{{ revenueYearData.total }}</div>
                    <span class="flex items-center gap-0.5 text-xs font-bold text-success">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                        {{ revenueYearData.change }}%
                    </span>
                    <span class="text-[11.5px] text-muted-foreground">vs last year</span>
                </div>
                <div class="relative mt-3.5 min-h-[210px] flex-1">
                    <apexchart type="area" height="100%" :series="revenueChartSeries" :options="revenueChartOptions" />
                </div>
                <button type="button" @click="showRevenueReport = true" class="mt-2 flex cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-xs font-semibold text-admin-accent">
                    View Detailed Report
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </div>

            <!-- Sales by Project -->
            <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                <div class="mb-2 flex items-center justify-between">
                    <div class="text-base font-bold text-foreground">Sales by Project</div>
                    <Select v-model="selectedSalesYear">
                        <SelectTrigger class="h-auto w-auto gap-1.5 rounded-[9px] border-border px-2.5 py-1.5 text-[11px] font-semibold text-foreground/80 [&>svg]:h-3 [&>svg]:w-3">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="y in salesYearOptions" :key="y" :value="y">{{ y }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex flex-1 flex-wrap items-center justify-center gap-4">
                    <div class="w-[160px] flex-none">
                        <apexchart type="donut" height="160" :series="salesChartSeries" :options="salesChartOptions" />
                    </div>
                    <div class="flex min-w-[175px] flex-1 flex-col gap-2.5">
                        <div v-for="s in salesYearData.segments" :key="s.key" class="flex items-center justify-between gap-2 text-[11.5px]">
                            <span class="flex min-w-0 items-center gap-1.5 text-foreground/80">
                                <span class="h-2 w-2 flex-none rounded-full" :style="{ background: s.color }"></span>
                                <span class="truncate">{{ s.name }}</span>
                            </span>
                            <span class="flex-none text-muted-foreground"><b class="text-foreground">{{ s.pct }}%</b> {{ s.value }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="showSalesReport = true" class="mt-3.5 flex cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-xs font-semibold text-admin-accent">
                    View Sales Report
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </div>

            <!-- Profit & Margin Analysis -->
            <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                <div class="mb-2 flex items-center justify-between">
                    <div class="text-base font-bold text-foreground">Profit &amp; Margin Analysis</div>
                    <Select v-model="selectedProfitYear">
                        <SelectTrigger class="h-auto w-auto gap-1.5 rounded-[9px] border-border px-2.5 py-1.5 text-[11px] font-semibold text-foreground/80 [&>svg]:h-3 [&>svg]:w-3">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="y in profitYearOptions" :key="y" :value="y">{{ y }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="mb-1.5 flex items-center gap-4 text-[11.5px]">
                    <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 rounded-[3px] bg-admin-accent"></span>Gross Profit (BDT)</span>
                    <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 rounded-full" style="background:#34D399;"></span>Profit Margin (%)</span>
                </div>
                <div class="relative mt-1 min-h-[210px] flex-1">
                    <apexchart type="line" height="100%" :series="profitChartSeries" :options="profitChartOptions" />
                </div>
                <button type="button" @click="showProfitReport = true" class="mt-2 flex cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-xs font-semibold text-admin-accent">
                    View Profit Report
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </div>
        </div>

        <!-- Body grid -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="flex min-w-0 flex-col gap-6">

                <!-- Performance Overview + Top Projects -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.5fr_1fr]">

                    <!-- Performance Overview -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-2 text-base font-bold text-foreground">Performance Overview</div>
                        <div class="mb-4 flex items-center gap-5 overflow-x-auto border-b border-border">
                            <button
                                v-for="t in performance.tabs" :key="t" type="button" @click="activeTab = t"
                                class="whitespace-nowrap border-b-[2.5px] pb-2.5 text-[13px] font-semibold transition-colors"
                                :class="activeTab === t ? 'border-admin-accent text-admin-accent' : 'border-transparent text-muted-foreground hover:text-foreground'"
                            >
                                {{ t }}
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-4">
                            <div v-for="m in activeMetrics" :key="m.key" class="min-w-0 rounded-[13px] border border-border p-3.5">
                                <div class="flex items-center justify-between gap-1.5">
                                    <span class="text-[11px] text-muted-foreground">{{ m.label }}</span>
                                    <span class="flex items-center gap-px text-[10.5px] font-bold" :class="m.dir === 'up' ? 'text-success' : 'text-destructive'">
                                        <svg v-if="m.dir === 'up'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                                        <svg v-else width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9l-6 6-6-6"/></svg>
                                        {{ m.change }}{{ m.suffix ?? '%' }}
                                    </span>
                                </div>
                                <div class="mt-1 text-base font-extrabold tracking-tight text-foreground">{{ m.value }}</div>
                                <svg viewBox="0 0 100 30" preserveAspectRatio="none" class="mt-1.5 h-[26px] w-full">
                                    <polyline :points="sparkPoints(m.spark)" fill="none" :stroke="m.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performing Projects -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="text-base font-bold text-foreground">Top Performing Projects</div>
                            <button type="button" @click="showTopProjects = true" class="cursor-pointer text-xs font-semibold text-admin-accent">View All</button>
                        </div>
                        <div class="grid grid-cols-[1.7fr_1.1fr_0.8fr_0.9fr] gap-2 border-b border-border pb-2 text-[11px] font-bold text-muted-foreground">
                            <div>Project</div><div>Sales (BDT)</div><div>Units</div><div>Growth</div>
                        </div>
                        <div v-for="p in topProjects" :key="p.key" class="grid grid-cols-[1.7fr_1.1fr_0.8fr_0.9fr] items-center gap-2 border-b border-border/60 py-[11px] last:border-b-0">
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div class="h-7 w-7 flex-none rounded-[7px] opacity-90" :style="{ background: p.bg }"></div>
                                <div class="truncate text-xs font-semibold text-foreground">{{ p.name }}</div>
                            </div>
                            <div class="text-xs font-semibold text-foreground">{{ p.sales }}</div>
                            <div class="text-xs font-semibold text-foreground">{{ p.units }}</div>
                            <div class="flex items-center gap-0.5 text-[11.5px] font-bold text-success">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                                {{ p.growth }}%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Funnel + Demographics + Monthly -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    <!-- Sales Funnel Analysis -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-3.5 flex items-center justify-between">
                            <div class="text-base font-bold text-foreground">Sales Funnel Analysis</div>
                            <Select v-model="selectedFunnelYear">
                                <SelectTrigger class="h-auto w-auto gap-1.5 rounded-lg border-border px-2 py-1.5 text-[10.5px] font-semibold text-foreground/80 [&>svg]:h-[11px] [&>svg]:w-[11px]">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="y in funnelYearOptions" :key="y" :value="y">{{ y }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex flex-1 items-center gap-3.5">
                            <svg width="150" height="180" viewBox="0 0 150 180" class="flex-none">
                                <path d="M5 4 L145 4 L120 36 L30 36 Z" fill="#60A5FA"/>
                                <path d="M31 40 L119 40 L101 72 L49 72 Z" fill="#60A5FA"/>
                                <path d="M50 76 L100 76 L86 108 L64 108 Z" fill="#22D3EE"/>
                                <path d="M65 112 L85 112 L79 144 L71 144 Z" fill="#34D399"/>
                                <path d="M71.5 148 L78.5 148 L76 176 L74 176 Z" fill="#34D399"/>
                                <text x="75" y="24" font-size="11" fill="#fff" font-weight="700" text-anchor="middle">Leads</text>
                                <text x="75" y="60" font-size="10" fill="#fff" font-weight="700" text-anchor="middle">Qualified</text>
                                <text x="75" y="96" font-size="9" fill="#fff" font-weight="700" text-anchor="middle">Visits</text>
                                <text x="75" y="131" font-size="8" fill="#fff" font-weight="700" text-anchor="middle">Book</text>
                            </svg>
                            <div class="flex min-w-0 flex-1 flex-col gap-2.5">
                                <div v-for="f in funnelYearData.stages" :key="f.key" class="flex min-w-0 items-center justify-between gap-2">
                                    <span class="truncate text-xs font-semibold text-foreground/80">{{ f.name }}</span>
                                    <span class="flex flex-none items-baseline gap-1.5">
                                        <b class="text-[12.5px] text-foreground">{{ f.count }}</b>
                                        <span class="text-[11px] font-semibold text-muted-foreground">{{ f.pct }}%</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer Demographics -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-2 flex items-center justify-between">
                            <div class="text-base font-bold text-foreground">Buyer Demographics</div>
                            <Select v-model="selectedBuyerYear">
                                <SelectTrigger class="h-auto w-auto gap-1.5 rounded-lg border-border px-2 py-1.5 text-[10.5px] font-semibold text-foreground/80 [&>svg]:h-[11px] [&>svg]:w-[11px]">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="y in buyerYearOptions" :key="y" :value="y">{{ y }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex flex-1 flex-wrap items-center justify-center gap-3.5">
                            <div class="w-[140px] flex-none">
                                <apexchart type="donut" height="140" :series="buyerChartSeries" :options="buyerChartOptions" />
                            </div>
                            <div class="flex min-w-[130px] flex-1 flex-col gap-2.5">
                                <div v-for="b in buyerYearData.segments" :key="b.key" class="flex min-w-0 items-center justify-between gap-2 text-[11.5px]">
                                    <span class="flex min-w-0 items-center gap-1.5 text-foreground/80">
                                        <span class="h-2 w-2 flex-none rounded-full" :style="{ background: b.color }"></span>
                                        <span class="truncate">{{ b.name }}</span>
                                    </span>
                                    <span class="flex-none"><b class="text-foreground">{{ b.pct }}%</b> <span class="text-muted-foreground">({{ b.value }})</span></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="showBuyerReport = true" class="mt-3.5 flex cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-xs font-semibold text-admin-accent">
                            View Buyer Report
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>

                    <!-- Monthly Comparison -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-2 flex items-center justify-between">
                            <div class="text-base font-bold text-foreground">Monthly Comparison</div>
                            <Select v-model="selectedComparisonMetric">
                                <SelectTrigger class="h-auto w-auto gap-1.5 rounded-lg border-border px-2 py-1.5 text-[10.5px] font-semibold text-foreground/80 [&>svg]:h-[11px] [&>svg]:w-[11px]">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="m in monthlyComparison.metrics" :key="m.key" :value="m.key">{{ m.label }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="mb-1.5 flex items-center gap-4 text-[11px]">
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 rounded-[3px] bg-admin-accent"></span>This Year (2026)</span>
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 rounded-[3px]" style="background:rgba(138,135,128,0.3);"></span>Last Year (2025)</span>
                        </div>
                        <div class="relative mt-1 min-h-[190px] flex-1">
                            <svg viewBox="0 0 440 200" preserveAspectRatio="none" class="block h-full w-full">
                                <text v-for="lbl in comparisonAxisLabels" :key="lbl.y" x="30" :y="lbl.y" font-size="8" class="fill-muted-foreground" text-anchor="end">{{ lbl.text }}</text>
                                <line x1="36" y1="172" x2="430" y2="172" class="stroke-chart-grid/[0.08]" stroke-width="1"/>
                                <template v-for="c in comparisonBars" :key="c.label">
                                    <rect :x="c.x" :y="c.lastY" width="9" :height="c.lastH" rx="2" class="fill-muted"/>
                                    <rect :x="c.x + 10" :y="c.thisY" width="9" :height="c.thisH" rx="2" fill="#C6A15B"/>
                                </template>
                                <text v-for="c in comparisonBars" :key="'l-'+c.label" :x="c.x + 9" y="186" font-size="7.5" class="fill-muted-foreground" text-anchor="middle">{{ c.label }}</text>
                            </svg>
                        </div>
                        <button type="button" @click="showComparisonReport = true" class="mt-2 flex cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-xs font-semibold text-admin-accent">
                            View Comparison Report
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right rail -->
            <div class="flex min-w-0 flex-col gap-6">

                <!-- Report Shortcuts -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-2 text-base font-bold text-foreground">Report Shortcuts</div>
                    <div class="flex flex-col">
                        <button
                            v-for="s in reportShortcuts" :key="s.key" type="button" @click="openShortcut(s)"
                            class="relative flex items-center gap-3 border-b border-border/60 py-[11px] text-left transition-colors last:border-b-0 hover:bg-muted/40"
                        >
                            <div class="min-w-0 flex-1 truncate text-[12.5px] font-semibold text-foreground" style="margin-left:1px;">
                                <span class="mr-3 inline-flex h-8 w-8 items-center justify-center rounded-[9px] align-middle" :style="{ background: s.bg, color: s.color }">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.doc" />
                                </span>
                                {{ s.name }}
                            </div>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="flex-none stroke-muted-foreground"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Recent Reports -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Recent Reports</div>
                        <button type="button" @click="showRecentReports = true" class="cursor-pointer text-xs font-semibold text-admin-accent">View All</button>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="r in localRecentReports.slice(0, 5)" :key="r.name" class="flex items-start gap-2.5 border-b border-border/60 py-[11px] last:border-b-0">
                            <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: r.bg, color: r.color }">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.doc" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-[12.5px] font-bold text-foreground">{{ r.name }}</div>
                                <div class="mt-0.5 text-[11px] text-muted-foreground">{{ r.gen }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-1.5 px-0.5 text-xs text-muted-foreground">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
            All data is updated as of {{ lastUpdated }}
        </div>

        <!-- Create Report dialog — client-side only, nothing is sent to the server -->
        <Dialog v-model:open="showCreateReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Create Report</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitCreateReport" class="space-y-4 px-6 pb-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Report Title</Label>
                        <Input v-model="createReportForm.title" placeholder="e.g. Sales Performance - June 2026" required />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Report Type</Label>
                            <Select v-model="createReportForm.type">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in reportTypeOptions" :key="t.value" :value="t.value">{{ t.value }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Format</Label>
                            <Select v-model="createReportForm.format">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="f in reportFormatOptions" :key="f" :value="f">{{ f }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter class="!px-0 pt-2">
                        <Button type="button" variant="outline" @click="showCreateReport = false">Cancel</Button>
                        <Button type="submit" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90">Create Report</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Report Settings dialog — client-side only, nothing is sent to the server -->
        <Dialog v-model:open="showReportSettings">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Report Settings</DialogTitle>
                </DialogHeader>

                <div class="space-y-4 px-6 pb-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Default Export Format</Label>
                        <Select v-model="reportSettings.defaultFormat">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="f in reportFormatOptions" :key="f" :value="f">{{ f }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Report Retention</Label>
                        <Select v-model="reportSettings.retention">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="r in ['3 months', '6 months', '12 months']" :key="r" :value="r">{{ r }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-foreground">
                        <input type="checkbox" v-model="reportSettings.autoGenerate" class="h-4 w-4 rounded border-border accent-[#C6A15B]" />
                        Auto-generate monthly reports
                    </label>
                    <label class="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-foreground">
                        <input type="checkbox" v-model="reportSettings.emailAdmins" class="h-4 w-4 rounded border-border accent-[#C6A15B]" />
                        Email reports to admins
                    </label>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showReportSettings = false">Cancel</Button>
                    <Button type="button" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90" @click="saveReportSettings">Save Settings</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Revenue Overview — detailed report dialog -->
        <Dialog v-model:open="showRevenueReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Revenue Overview — Detailed Report ({{ selectedRevenueYear }})</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 px-6 pb-4">
                    <div class="flex items-center justify-between rounded-xl border border-border p-3.5">
                        <span class="text-xs font-semibold text-muted-foreground">Total Revenue (YTD)</span>
                        <span class="text-lg font-extrabold text-foreground">{{ revenueYearData.total }}</span>
                    </div>
                    <div class="max-h-[300px] space-y-2 overflow-y-auto">
                        <div v-for="m in revenueYearData.months" :key="m.label" class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-foreground/80">{{ m.label }}</span>
                            <span class="font-bold text-foreground">BDT {{ m.value.toFixed(1) }}M</span>
                        </div>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showRevenueReport = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Sales by Project — detailed report dialog -->
        <Dialog v-model:open="showSalesReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Sales by Project — Detailed Report ({{ selectedSalesYear }})</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 px-6 pb-4">
                    <div class="flex items-center justify-between rounded-xl border border-border p-3.5">
                        <span class="text-xs font-semibold text-muted-foreground">Total Sales (YTD)</span>
                        <span class="text-lg font-extrabold text-foreground">{{ salesYearData.total }}</span>
                    </div>
                    <div class="space-y-3">
                        <div v-for="s in salesYearData.segments" :key="s.key">
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="flex items-center gap-1.5 font-semibold text-foreground/80"><span class="h-2 w-2 rounded-full" :style="{ background: s.color }"></span>{{ s.name }}</span>
                                <span class="font-bold text-foreground">{{ s.value }} <span class="font-medium text-muted-foreground">({{ s.pct }}%)</span></span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full" :style="{ width: s.pct + '%', background: s.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showSalesReport = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Profit & Margin — detailed report dialog -->
        <Dialog v-model:open="showProfitReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Profit &amp; Margin — Detailed Report ({{ selectedProfitYear }})</DialogTitle>
                </DialogHeader>
                <div class="max-h-[360px] space-y-2 overflow-y-auto px-6 pb-4">
                    <div v-for="m in profitMonths" :key="m.label" class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-foreground/80">{{ m.label }}</span>
                        <span class="flex items-center gap-3">
                            <span class="font-bold text-foreground">BDT {{ m.profit }}M</span>
                            <span class="font-semibold text-success">{{ m.margin }}%</span>
                        </span>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showProfitReport = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Buyer Demographics — detailed report dialog -->
        <Dialog v-model:open="showBuyerReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Buyer Demographics — Detailed Report ({{ selectedBuyerYear }})</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 px-6 pb-4">
                    <div class="flex items-center justify-between rounded-xl border border-border p-3.5">
                        <span class="text-xs font-semibold text-muted-foreground">Total Buyers</span>
                        <span class="text-lg font-extrabold text-foreground">{{ buyerYearData.total }}</span>
                    </div>
                    <div class="space-y-3">
                        <div v-for="b in buyerYearData.segments" :key="b.key">
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="flex items-center gap-1.5 font-semibold text-foreground/80"><span class="h-2 w-2 rounded-full" :style="{ background: b.color }"></span>{{ b.name }}</span>
                                <span class="font-bold text-foreground">{{ b.value }} <span class="font-medium text-muted-foreground">({{ b.pct }}%)</span></span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full" :style="{ width: b.pct + '%', background: b.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showBuyerReport = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Monthly Comparison — detailed report dialog -->
        <Dialog v-model:open="showComparisonReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Monthly Comparison — Detailed Report ({{ comparisonMetricData.label }})</DialogTitle>
                </DialogHeader>
                <div class="max-h-[360px] space-y-2 overflow-y-auto px-6 pb-4">
                    <div v-for="m in comparisonMetricData.months" :key="m.label" class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-foreground/80">{{ m.label }}</span>
                        <span class="flex items-center gap-3">
                            <span class="font-bold text-foreground">{{ comparisonMetricData.unitPrefix }}{{ m.thisYear }}{{ comparisonMetricData.unitSuffix }}</span>
                            <span class="font-medium text-muted-foreground">vs {{ comparisonMetricData.unitPrefix }}{{ m.lastYear }}{{ comparisonMetricData.unitSuffix }}</span>
                        </span>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showComparisonReport = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Top Performing Projects — View All dialog -->
        <Dialog v-model:open="showTopProjects">
            <DialogContent class="w-full max-w-lg">
                <DialogHeader>
                    <DialogTitle>Top Performing Projects</DialogTitle>
                </DialogHeader>
                <div class="overflow-x-auto px-6 pb-4">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Project</TableHead>
                                <TableHead>Sales (BDT)</TableHead>
                                <TableHead>Units</TableHead>
                                <TableHead>Growth</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="p in topProjects" :key="p.key">
                                <TableCell>
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-6 w-6 flex-none rounded-[6px] opacity-90" :style="{ background: p.bg }"></div>
                                        <span class="text-xs font-semibold text-foreground">{{ p.name }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-xs font-semibold">{{ p.sales }}</TableCell>
                                <TableCell class="text-xs font-semibold">{{ p.units }}</TableCell>
                                <TableCell class="text-xs font-bold text-success">+{{ p.growth }}%</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showTopProjects = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Recent Reports — View All dialog -->
        <Dialog v-model:open="showRecentReports">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Recent Reports</DialogTitle>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-1 overflow-y-auto px-6 pb-4">
                    <div v-for="r in localRecentReports" :key="r.name" class="flex items-start gap-2.5 border-b border-border/60 py-2.5 last:border-b-0">
                        <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: r.bg, color: r.color }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.doc" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[12.5px] font-bold text-foreground">{{ r.name }}</div>
                            <div class="mt-0.5 text-[11px] text-muted-foreground">{{ r.gen }}</div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showRecentReports = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Report Shortcuts — coming soon dialog (Marketing Campaign / Inventory Status) -->
        <Dialog v-model:open="showComingSoon">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>{{ comingSoonLabel }}</DialogTitle>
                </DialogHeader>
                <p class="px-6 pb-2 text-sm text-muted-foreground">This feature is coming soon.</p>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showComingSoon = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
