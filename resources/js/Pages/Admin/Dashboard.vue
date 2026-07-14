<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
    stats:            { type: Object, required: true },
    recentBookings:   { type: Array,  default: () => [] },
    projectProgress:  { type: Array,  default: () => [] },
    salesInventory:   { type: Object, required: true },
    paymentTrend:     { type: Array,  default: () => [] },
    topPerforming:    { type: Array,  default: () => [] },
    supportOverview:  { type: Object, default: null },
    recentActivity:   { type: Array,  default: () => [] },
    quickActions:     { type: Array,  default: () => [] },
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? 'Admin');
const { isDark } = useTheme();

// ── Formatting helpers ──────────────────────────────────────────
function formatBDT(amount) {
    const n = Number(amount) || 0;
    if (n >= 1_000_000) return `BDT ${(n / 1_000_000).toFixed(2)}M`;
    if (n >= 1_000) return `BDT ${(n / 1_000).toFixed(1)}K`;
    return `BDT ${Math.round(n).toLocaleString()}`;
}
function formatNumber(n) {
    return Number(n ?? 0).toLocaleString();
}

// ── Homeverse chart palette (mode-invariant series colours) ─────
const GOLD    = '#C6A15B';
const SUCCESS = '#34D399';
const WARNING = '#FBBF24';
const INFO    = '#60A5FA';
const VIOLET  = '#A78BFA';
const PINK    = '#F472B6';
const CYAN    = '#22D3EE';

// Mode-aware chart chrome (grid lines, axis/legend text) for ApexCharts,
// since chart libraries need literal colour values, not CSS variables.
const chartMuted = computed(() => (isDark.value ? '#A8A399' : '#6B6355'));
const chartGrid  = computed(() => (isDark.value ? 'rgba(255,255,255,0.06)' : 'rgba(26,22,17,0.08)'));
const chartTheme = computed(() => (isDark.value ? 'dark' : 'light'));

// ── Sales & Inventory donut ─────────────────────────────────────
const salesSegments = computed(() => {
    const s = props.salesInventory;
    const raw = [
        { key: 'sold',      label: 'Sold',      count: s.sold ?? 0,      color: SUCCESS },
        { key: 'booked',    label: 'Reserved',  count: s.booked ?? 0,    color: WARNING },
        { key: 'available', label: 'Available', count: s.available ?? 0, color: INFO },
    ];
    const total = s.total || raw.reduce((a, r) => a + r.count, 0) || 1;
    return raw.map((seg) => ({ ...seg, pct: Math.round((seg.count / total) * 100) }));
});
const salesChartSeries = computed(() => salesSegments.value.map((s) => s.count));
const salesChartOptions = computed(() => ({
    chart: { type: 'donut', fontFamily: 'Plus Jakarta Sans, sans-serif' },
    labels: salesSegments.value.map((s) => s.label),
    colors: salesSegments.value.map((s) => s.color),
    legend: { show: false },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: [isDark.value ? '#151922' : '#FFFFFF'] },
    plotOptions: {
        pie: {
            donut: {
                size: '74%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Total Units',
                        color: chartMuted.value,
                        fontSize: '11px',
                        formatter: () => formatNumber(props.salesInventory.total),
                    },
                    value: { color: isDark.value ? '#F5F2EA' : '#1A1611', fontSize: '24px', fontWeight: 800, offsetY: -4 },
                },
            },
        },
    },
    tooltip: { theme: chartTheme.value, y: { formatter: (v) => formatNumber(v) } },
}));

// ── Support tickets donut ───────────────────────────────────────
const ticketSegments = computed(() => props.supportOverview?.segments ?? []);
const ticketsTotal = computed(() =>
    ticketSegments.value.reduce((a, s) => a + (Number(s.count) || 0), 0)
);
const openTickets = computed(() =>
    ticketSegments.value.find((s) => s.key === 'open')?.count ?? 0
);
const ticketChartSeries = computed(() => ticketSegments.value.map((s) => Number(s.count) || 0));
const ticketChartOptions = computed(() => ({
    chart: { type: 'donut', fontFamily: 'Plus Jakarta Sans, sans-serif' },
    labels: ticketSegments.value.map((s) => s.label),
    colors: ticketSegments.value.map((s) => s.color),
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
                        label: 'Total Tickets',
                        color: chartMuted.value,
                        fontSize: '10px',
                        formatter: () => formatNumber(ticketsTotal.value),
                    },
                    value: { color: isDark.value ? '#F5F2EA' : '#1A1611', fontSize: '22px', fontWeight: 800, offsetY: -4 },
                },
            },
        },
    },
    tooltip: { theme: chartTheme.value, y: { formatter: (v) => formatNumber(v) } },
}));

// ── Payment collection trend (area chart, gold = primary series) ─
const trendChartSeries = computed(() => [
    { name: 'Collected', data: props.paymentTrend.map((p) => p.amount) },
]);
const trendChartOptions = computed(() => ({
    chart: {
        type: 'area',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        toolbar: { show: false },
        zoom: { enabled: false },
    },
    colors: [GOLD],
    stroke: { curve: 'smooth', width: 3 },
    fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: 0.32, opacityTo: 0, stops: [0, 90, 100] },
    },
    markers: { size: 0, hover: { size: 6 }, colors: [GOLD], strokeColors: isDark.value ? '#151922' : '#FFFFFF', strokeWidth: 3 },
    dataLabels: { enabled: false },
    grid: {
        borderColor: chartGrid.value,
        strokeDashArray: 0,
        yaxis: { lines: { show: true } },
        xaxis: { lines: { show: false } },
        padding: { left: 8, right: 8 },
    },
    xaxis: {
        categories: props.paymentTrend.map((p) => p.label),
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: chartMuted.value, fontSize: '11px' } },
    },
    yaxis: {
        labels: { style: { colors: chartMuted.value, fontSize: '11px' }, formatter: (v) => formatBDT(v) },
    },
    tooltip: { theme: chartTheme.value, y: { formatter: (v) => formatBDT(v) } },
}));

// ── Booking status → badge variant ──────────────────────────────
const statusVariantMap = { slate: 'secondary', amber: 'warning', green: 'success', red: 'destructive', purple: 'gold' };
function statusVariant(color) {
    return statusVariantMap[color] ?? 'secondary';
}

// ── Quick action styling ────────────────────────────────────────
const actionColors = {
    accent: { icon: 'text-gold',      well: 'bg-gold/10',      hover: 'hover:border-gold hover:bg-gold/5' },
    blue:   { icon: 'text-info',      well: 'bg-info/10',      hover: 'hover:border-info hover:bg-info/5' },
    green:  { icon: 'text-success',   well: 'bg-success/10',   hover: 'hover:border-success hover:bg-success/5' },
    orange: { icon: 'text-warning',   well: 'bg-warning/10',   hover: 'hover:border-warning hover:bg-warning/5' },
    red:    { icon: 'text-destructive', well: 'bg-destructive/10', hover: 'hover:border-destructive hover:bg-destructive/5' },
    sky:    { icon: 'text-chart-6',   well: 'bg-chart-6/10',   hover: 'hover:border-chart-6 hover:bg-chart-6/5' },
};
function actionStyle(color) {
    return actionColors[color] ?? actionColors.blue;
}

// ── Project progress bar colours (cycled, gold reserved for the primary trend) ─
const progressColors = [INFO, SUCCESS, VIOLET, WARNING, CYAN, PINK];
</script>

<template>

    <Head title="Dashboard" />

    <AdminLayout title="Dashboard">

        <!-- ── Page Header ─────────────────────────────────────────── -->
        <div class="mb-6">
            <h1 class="text-[28px] font-extrabold tracking-tight text-foreground leading-none">
                Dashboard Overview
            </h1>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Welcome back, {{ userName }}! Here's what's happening with your projects today.
            </p>
        </div>

        <!-- ── KPI ROW ──────────────────────────────────────────────── -->
        <div class="mb-6 grid grid-cols-2 gap-5 lg:grid-cols-3 xl:grid-cols-6">

            <!-- Total Projects -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 text-gold">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M19 21V11l-4-2"/></svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Total Projects</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground hv-num">{{ stats.projects.total }}</div>
                <div class="mt-2 text-xs text-muted-foreground">
                    Active: <b class="text-foreground">{{ stats.projects.active }}</b> &nbsp;·&nbsp;
                    Completed: <b class="text-foreground">{{ stats.projects.completed }}</b>
                </div>
            </div>

            <!-- Total Units -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8"/></svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Total Units</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground hv-num">{{ formatNumber(stats.units.total) }}</div>
                <div class="mt-2 text-xs text-muted-foreground">
                    Sold: <b class="text-foreground">{{ formatNumber(stats.units.sold) }}</b> &nbsp;·&nbsp;
                    Available: <b class="text-foreground">{{ formatNumber(stats.units.available) }}</b>
                </div>
            </div>

            <!-- Total Buyers -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-success/10 text-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/></svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Total Buyers</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground hv-num">{{ formatNumber(stats.clients.total) }}</div>
                <div class="mt-2 text-xs text-muted-foreground">
                    This Month: <b class="text-foreground">{{ formatNumber(stats.clients.new_this_month) }}</b> new
                </div>
            </div>

            <!-- Monthly Collection -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10h18M16 14h2"/></svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Monthly Collection</span>
                </div>
                <div class="text-2xl font-extrabold tracking-tight leading-none text-foreground hv-num">{{ formatBDT(stats.collection.this_month) }}</div>
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    <span class="font-bold hv-num" :class="stats.collection.growth_pct >= 0 ? 'text-success' : 'text-destructive'">
                        {{ stats.collection.growth_pct >= 0 ? '▲' : '▼' }} {{ Math.abs(stats.collection.growth_pct) }}%
                    </span>
                    <span class="text-muted-foreground">from last month</span>
                </div>
            </div>

            <!-- Pending Payments -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-destructive/10 text-destructive">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Pending Payments</span>
                </div>
                <div class="text-2xl font-extrabold tracking-tight leading-none text-foreground hv-num">{{ formatBDT(stats.pending.amount) }}</div>
                <div class="mt-2 text-xs text-muted-foreground">
                    Overdue: <b class="text-destructive hv-num">{{ formatBDT(stats.pending.overdue) }}</b>
                </div>
            </div>

            <!-- Open Tickets -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-chart-6/10 text-chart-6">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2z"/></svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Open Tickets</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground hv-num">{{ formatNumber(openTickets) }}</div>
                <div class="mt-2 text-xs text-muted-foreground">
                    Total Tracked: <b class="text-foreground">{{ formatNumber(ticketsTotal) }}</b>
                </div>
            </div>
        </div>

        <!-- ── ANALYTICS ROW ────────────────────────────────────────── -->
        <div class="mb-6 grid gap-6" style="grid-template-columns:2fr 1fr;">

            <!-- Sales & Inventory + Payment Trend -->
            <div class="grid overflow-hidden rounded-[20px] border border-border bg-card shadow-card" style="grid-template-columns:1fr 1.25fr;">

                <!-- Donut -->
                <div class="flex flex-col p-6 border-r border-border">
                    <div class="text-[17px] font-bold text-foreground">Sales &amp; Inventory</div>
                    <div class="flex flex-1 items-center gap-4 mt-4">
                        <div class="w-[170px] flex-shrink-0">
                            <apexchart type="donut" height="170" :series="salesChartSeries" :options="salesChartOptions" />
                        </div>
                        <div class="flex flex-col gap-3.5">
                            <div v-for="seg in salesSegments" :key="seg.key">
                                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                    <span class="h-2 w-2 rounded-[3px]" :style="`background:${seg.color}`"></span>{{ seg.label }}
                                </div>
                                <div class="ml-4 text-[15px] font-bold text-foreground hv-num">
                                    {{ formatNumber(seg.count) }} <span class="text-xs font-medium text-muted-foreground">({{ seg.pct }}%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Link :href="route('admin.units.index')" class="mt-3.5 inline-flex items-center gap-1 text-[13px] font-semibold text-brand hover:underline">
                        View Detailed Report →
                    </Link>
                </div>

                <!-- Payment trend area chart -->
                <div class="flex flex-col p-6">
                    <div class="text-[17px] font-bold text-foreground">Payment Collection Trend</div>
                    <div class="relative flex-1 mt-3.5">
                        <apexchart type="area" height="220" :series="trendChartSeries" :options="trendChartOptions" />
                    </div>
                    <Link :href="route('admin.payments.index')" class="mt-1.5 inline-flex items-center gap-1 text-[13px] font-semibold text-brand hover:underline">
                        View Financial Report →
                    </Link>
                </div>
            </div>

            <!-- Project Progress -->
            <div class="flex flex-col rounded-[20px] border border-border bg-card shadow-card p-6">
                <div class="text-[17px] font-bold text-foreground mb-[18px]">Project Progress</div>
                <div class="flex flex-1 flex-col gap-[18px]">
                    <div v-if="projectProgress.length === 0" class="text-center text-sm text-muted-foreground py-6">
                        No active projects yet.
                    </div>
                    <div v-for="(p, i) in projectProgress" :key="p.id" class="flex items-center gap-3">
                        <div class="flex h-[42px] w-[42px] flex-shrink-0 items-center justify-center rounded-[10px]"
                            :style="`background:${progressColors[i % progressColors.length]}1A;`">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="progressColors[i % progressColors.length]" stroke-width="1.6">
                                <path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-[13.5px] font-semibold text-foreground">{{ p.name }}</span>
                                <span class="text-[13px] font-bold text-foreground hv-num">{{ p.progress }}%</span>
                            </div>
                            <div class="h-[7px] rounded overflow-hidden bg-muted">
                                <div class="h-full rounded" :style="`width:${p.progress}%; background:${progressColors[i % progressColors.length]};`"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <Link :href="route('admin.projects.index')" class="mt-4 self-center text-[13px] font-semibold text-brand hover:underline">
                    View All Projects →
                </Link>
            </div>
        </div>

        <!-- ── LOWER SECTION ────────────────────────────────────────── -->
        <div class="grid gap-6" style="grid-template-columns:2fr 1fr; align-items:start;">

            <!-- LEFT COLUMN -->
            <div class="flex flex-col gap-6 min-w-0">

                <!-- Recent Reservations -->
                <div class="rounded-[20px] border border-border bg-card shadow-card p-6">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-lg font-bold text-foreground">Recent Reservations</div>
                        <Button as-child variant="outline" size="sm">
                            <Link :href="route('admin.bookings.index')">View All →</Link>
                        </Button>
                    </div>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Buyer</TableHead>
                                    <TableHead>Project</TableHead>
                                    <TableHead>Unit</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="recentBookings.length === 0">
                                    <TableCell colspan="7" class="text-center text-muted-foreground py-8 px-2">
                                        No reservations yet.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="b in recentBookings" :key="b.id">
                                    <TableCell>
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-[30px] w-[30px] flex-shrink-0 items-center justify-center rounded-full bg-gold/10 text-[11px] font-bold text-brand">{{ b.initials }}</span>
                                            <span class="text-[13.5px] font-semibold text-foreground">{{ b.buyer }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-[13px] text-foreground/80">{{ b.project }}</TableCell>
                                    <TableCell class="text-[13px] text-foreground/80">{{ b.unit }}</TableCell>
                                    <TableCell class="text-[13px] text-foreground/80">{{ b.type }}</TableCell>
                                    <TableCell class="text-[13px] font-semibold text-foreground hv-num">{{ formatBDT(b.amount) }}</TableCell>
                                    <TableCell class="text-[13px] text-muted-foreground hv-num">{{ b.date }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="statusVariant(b.status_color)">{{ b.status }}</Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <!-- Support tickets + Top performing -->
                <div class="grid grid-cols-2 gap-6">

                    <!-- Support Tickets Overview -->
                    <div class="rounded-[20px] border border-border bg-card shadow-card p-6">
                        <div class="mb-1.5 flex items-center justify-between">
                            <div class="text-base font-bold text-foreground">Support Tickets Overview</div>
                            <Link :href="route('admin.support-tickets.index')" class="text-xs font-semibold text-brand hover:underline">View All →</Link>
                        </div>
                        <div v-if="supportOverview" class="flex items-center gap-3.5 mt-3.5">
                            <div class="w-[140px] flex-shrink-0">
                                <apexchart type="donut" height="140" :series="ticketChartSeries" :options="ticketChartOptions" />
                            </div>
                            <div class="flex flex-1 flex-col gap-2.5">
                                <div v-for="seg in ticketSegments" :key="seg.key" class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5 text-muted-foreground">
                                        <span class="h-2 w-2 rounded-full" :style="`background:${seg.color}`"></span>{{ seg.label }}
                                    </span>
                                    <span class="font-bold text-foreground hv-num">{{ formatNumber(seg.count) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-sm text-muted-foreground py-8">No ticket data available.</div>
                    </div>

                    <!-- Top Performing Projects -->
                    <div class="rounded-[20px] border border-border bg-card shadow-card p-6">
                        <div class="mb-2.5 flex items-center justify-between">
                            <div class="text-base font-bold text-foreground">Top Performing Projects</div>
                            <Link :href="route('admin.projects.index')" class="text-xs font-semibold text-brand hover:underline">Full Report →</Link>
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="pl-1">Project</TableHead>
                                    <TableHead class="text-right">Sold</TableHead>
                                    <TableHead class="text-right">Total</TableHead>
                                    <TableHead class="text-right pr-1">Conv.</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="topPerforming.length === 0">
                                    <TableCell colspan="4" class="text-center text-muted-foreground py-5 px-1">No data yet.</TableCell>
                                </TableRow>
                                <TableRow v-for="p in topPerforming" :key="p.id">
                                    <TableCell class="text-[12.5px] font-semibold text-foreground pl-1">{{ p.name }}</TableCell>
                                    <TableCell class="text-[12.5px] text-foreground/80 text-right hv-num">{{ formatNumber(p.sold) }}</TableCell>
                                    <TableCell class="text-[12.5px] text-foreground/80 text-right hv-num">{{ formatNumber(p.total) }}</TableCell>
                                    <TableCell class="text-right pr-1">
                                        <span class="text-xs font-bold text-success hv-num">{{ p.conversion }}%</span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="flex flex-col gap-6" style="position:sticky; top:0;">

                <!-- Quick Actions -->
                <div class="rounded-[20px] border border-border bg-card shadow-card p-6">
                    <div class="text-[17px] font-bold text-foreground mb-4">Quick Actions</div>
                    <div class="grid grid-cols-3 gap-3">
                        <Link v-for="action in quickActions" :key="action.label" :href="action.href"
                            class="flex flex-col items-center gap-2 rounded-[14px] border border-border p-4 text-center transition-all"
                            :class="actionStyle(action.color).hover">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="[actionStyle(action.color).well, actionStyle(action.color).icon]">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path v-if="action.icon === 'project'" d="M3 21h12V5a1 1 0 00-1-1H4a1 1 0 00-1 1zM7 8h2M7 12h2"/>
                                    <path v-else-if="action.icon === 'unit'" d="M3 11l9-7 9 7M5 10v9h14v-9M12 14v5"/>
                                    <template v-else-if="action.icon === 'buyer'">
                                        <circle cx="9" cy="8" r="4"/>
                                        <path d="M3 20v-1a5 5 0 015-5h2a5 5 0 015 5v1M18 8v6M21 11h-6"/>
                                    </template>
                                    <path v-else-if="action.icon === 'payment'" d="M3 5h18v14H3zM3 10h18M7 15h4"/>
                                    <path v-else-if="action.icon === 'document'" d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5zM14 3v5h5M9 13h6M9 17h6"/>
                                    <path v-else d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/>
                                </svg>
                            </div>
                            <span class="text-[11.5px] font-semibold text-foreground/80">{{ action.label }}</span>
                        </Link>
                    </div>
                </div>

                <!-- System Activity -->
                <div class="rounded-[20px] border border-border bg-card shadow-card p-6 flex-1">
                    <div class="text-[17px] font-bold text-foreground mb-3.5">System Activity</div>
                    <div v-if="recentActivity.length === 0" class="text-center text-sm text-muted-foreground py-6">
                        No recent activity.
                    </div>
                    <div class="flex flex-col gap-1">
                        <div v-for="(act, i) in recentActivity" :key="i" class="flex gap-3 rounded-[13px] p-3 transition-colors hover:bg-muted">
                            <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-xl bg-gold/10 text-gold">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[13.5px] font-bold text-foreground">{{ act.title }}</div>
                                <div class="mt-0.5 text-xs leading-snug text-muted-foreground">{{ act.desc }}</div>
                                <div class="mt-1 text-[11px] text-muted-foreground/70">{{ act.time }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── FOOTER ───────────────────────────────────────────────── -->
        <footer class="flex items-center justify-between py-5 px-1 text-xs text-muted-foreground">
            <div>© {{ new Date().getFullYear() }} HomeVerse Admin</div>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5"><span class="h-[7px] w-[7px] rounded-full bg-success"></span>System Online</span>
            </div>
        </footer>

    </AdminLayout>
</template>
