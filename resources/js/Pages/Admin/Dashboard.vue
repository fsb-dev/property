<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

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

// ── Sales & Inventory donut ─────────────────────────────────────
const SALES_R = 70;
const SALES_CIRC = 2 * Math.PI * SALES_R;
const salesSegments = computed(() => {
    const s = props.salesInventory;
    const raw = [
        { key: 'sold',      label: 'Sold',      count: s.sold ?? 0,      color: '#22C55E' },
        { key: 'booked',    label: 'Reserved',  count: s.booked ?? 0,    color: '#F59E0B' },
        { key: 'available', label: 'Available', count: s.available ?? 0, color: '#93C5FD' },
    ];
    const total = s.total || raw.reduce((a, r) => a + r.count, 0) || 1;
    let offset = 0;
    return raw.map((seg) => {
        const len = (seg.count / total) * SALES_CIRC;
        const out = {
            ...seg,
            pct: Math.round((seg.count / total) * 100),
            dasharray: `${len.toFixed(1)} ${SALES_CIRC.toFixed(1)}`,
            dashoffset: `${(-offset).toFixed(1)}`,
        };
        offset += len;
        return out;
    });
});

// ── Support tickets donut ───────────────────────────────────────
const TICKET_R = 60;
const TICKET_CIRC = 2 * Math.PI * TICKET_R;
const ticketSegments = computed(() => {
    const segs = props.supportOverview?.segments ?? [];
    const total = segs.reduce((a, s) => a + (Number(s.count) || 0), 0) || 1;
    let offset = 0;
    return segs.map((seg) => {
        const count = Number(seg.count) || 0;
        const len = (count / total) * TICKET_CIRC;
        const out = {
            ...seg,
            count,
            dasharray: `${len.toFixed(1)} ${TICKET_CIRC.toFixed(1)}`,
            dashoffset: `${(-offset).toFixed(1)}`,
        };
        offset += len;
        return out;
    });
});
const ticketsTotal = computed(() =>
    props.supportOverview?.segments?.reduce((a, s) => a + (Number(s.count) || 0), 0) ?? 0
);
const openTickets = computed(() =>
    props.supportOverview?.segments?.find((s) => s.key === 'open')?.count ?? 0
);

// ── Payment collection trend (line chart) ───────────────────────
const TREND_X_START = 70;
const TREND_X_END = 610;
const TREND_Y_TOP = 24;
const TREND_Y_BOTTOM = 210;

const trendMax = computed(() => Math.max(1, ...props.paymentTrend.map((p) => p.amount)));

const trendPoints = computed(() => {
    const n = props.paymentTrend.length;
    return props.paymentTrend.map((p, i) => ({
        x: n <= 1 ? TREND_X_START : TREND_X_START + (i * (TREND_X_END - TREND_X_START)) / (n - 1),
        y: TREND_Y_BOTTOM - (p.amount / trendMax.value) * (TREND_Y_BOTTOM - TREND_Y_TOP),
        label: p.label,
        amount: p.amount,
    }));
});

const trendPolylinePoints = computed(() =>
    trendPoints.value.map((p) => `${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ')
);
const trendAreaPoints = computed(() => {
    if (!trendPoints.value.length) return '';
    const first = trendPoints.value[0];
    const last = trendPoints.value[trendPoints.value.length - 1];
    return `${trendPolylinePoints.value} ${last.x.toFixed(1)},${TREND_Y_BOTTOM} ${first.x.toFixed(1)},${TREND_Y_BOTTOM}`;
});
const trendLatest = computed(() => trendPoints.value[trendPoints.value.length - 1] ?? null);

const trendGridLines = computed(() => {
    const steps = 5;
    return Array.from({ length: steps + 1 }, (_, i) => ({
        y: TREND_Y_TOP + (i * (TREND_Y_BOTTOM - TREND_Y_TOP)) / steps,
        value: trendMax.value - (trendMax.value * i) / steps,
    }));
});

// ── Booking status → badge variant ──────────────────────────────
const statusVariantMap = { slate: 'secondary', amber: 'warning', green: 'success', red: 'destructive', purple: 'purple' };
function statusVariant(color) {
    return statusVariantMap[color] ?? 'secondary';
}

// ── Quick action styling ────────────────────────────────────────
const actionColors = {
    accent: { icon: 'text-admin-accent', well: 'bg-admin-accent/10', hover: 'hover:border-admin-accent hover:bg-admin-accent/5' },
    blue:   { icon: 'text-blue-600',    well: 'bg-blue-100',       hover: 'hover:border-blue-400 hover:bg-blue-50' },
    green:  { icon: 'text-green-600',   well: 'bg-green-100',      hover: 'hover:border-green-400 hover:bg-green-50' },
    orange: { icon: 'text-orange-500',  well: 'bg-orange-100',     hover: 'hover:border-orange-400 hover:bg-orange-50' },
    red:    { icon: 'text-red-500',     well: 'bg-red-100',        hover: 'hover:border-red-400 hover:bg-red-50' },
    sky:    { icon: 'text-sky-500',     well: 'bg-sky-100',        hover: 'hover:border-sky-400 hover:bg-sky-50' },
};
function actionStyle(color) {
    return actionColors[color] ?? actionColors.blue;
}

// ── Progress bar colors (cycled) ────────────────────────────────
const progressColors = ['#5B3DF5', '#22C55E', '#3B82F6', '#F59E0B', '#7C5CFF', '#EC4899'];
</script>

<template>

    <Head title="Dashboard" />

    <AdminLayout title="Dashboard">

        <!-- ── Page Header ─────────────────────────────────────────── -->
        <div class="mb-6">
            <h1 style="font-size:28px; font-weight:800; letter-spacing:-0.5px; color:#151B2E; line-height:1.1;">
                Dashboard Overview <span style="font-size:24px;"></span>
            </h1>
            <p style="font-size:14px; color:#697386; margin-top:5px;">
                Welcome back, {{ userName }}! Here's what's happening with your projects today.
            </p>
        </div>

        <!-- ── KPI ROW ──────────────────────────────────────────────── -->
        <div class="mb-6 grid grid-cols-2 gap-5 lg:grid-cols-3 xl:grid-cols-6">

            <!-- Total Projects -->
            <div class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#F1ECFF; color:#5B3DF5;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M19 21V11l-4-2"/></svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Projects</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">{{ stats.projects.total }}</div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">
                    Active: <b style="color:#151B2E;">{{ stats.projects.active }}</b> &nbsp;·&nbsp;
                    Completed: <b style="color:#151B2E;">{{ stats.projects.completed }}</b>
                </div>
            </div>

            <!-- Total Units -->
            <div class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#E8F0FF; color:#3B82F6;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8"/></svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Units</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">{{ formatNumber(stats.units.total) }}</div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">
                    Sold: <b style="color:#151B2E;">{{ formatNumber(stats.units.sold) }}</b> &nbsp;·&nbsp;
                    Available: <b style="color:#151B2E;">{{ formatNumber(stats.units.available) }}</b>
                </div>
            </div>

            <!-- Total Buyers -->
            <div class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#E6F7EE; color:#22C55E;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/></svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Buyers</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">{{ formatNumber(stats.clients.total) }}</div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">
                    This Month: <b style="color:#151B2E;">{{ formatNumber(stats.clients.new_this_month) }}</b> new
                </div>
            </div>

            <!-- Monthly Collection -->
            <div class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#FFF3E0; color:#F59E0B;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10h18M16 14h2"/></svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Monthly Collection</span>
                </div>
                <div style="font-size:24px; font-weight:800; letter-spacing:-0.5px; line-height:1; color:#151B2E;">{{ formatBDT(stats.collection.this_month) }}</div>
                <div style="font-size:12px; margin-top:8px; display:flex; align-items:center; gap:5px;">
                    <span :style="`font-weight:700; color:${stats.collection.growth_pct >= 0 ? '#22C55E' : '#EF4444'};`">
                        {{ stats.collection.growth_pct >= 0 ? '▲' : '▼' }} {{ Math.abs(stats.collection.growth_pct) }}%
                    </span>
                    <span style="color:#697386;">from last month</span>
                </div>
            </div>

            <!-- Pending Payments -->
            <div class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#FDE8E8; color:#EF4444;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Pending Payments</span>
                </div>
                <div style="font-size:24px; font-weight:800; letter-spacing:-0.5px; line-height:1; color:#151B2E;">{{ formatBDT(stats.pending.amount) }}</div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">
                    Overdue: <b style="color:#EF4444;">{{ formatBDT(stats.pending.overdue) }}</b>
                </div>
            </div>

            <!-- Open Tickets -->
            <div class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#E6F4FB; color:#0EA5E9;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2z"/></svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Open Tickets</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">{{ formatNumber(openTickets) }}</div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">
                    Total Tracked: <b style="color:#151B2E;">{{ formatNumber(ticketsTotal) }}</b>
                </div>
            </div>
        </div>

        <!-- ── ANALYTICS ROW ────────────────────────────────────────── -->
        <div class="mb-6 grid gap-6" style="grid-template-columns:2fr 1fr;">

            <!-- Sales & Inventory + Payment Trend -->
            <div class="grid overflow-hidden rounded-[20px] border border-border bg-white shadow-card" style="grid-template-columns:1fr 1.25fr;">

                <!-- Donut -->
                <div class="flex flex-col p-6" style="border-right:1px solid #F1F4F9;">
                    <div style="font-size:17px; font-weight:700; color:#151B2E;">Sales &amp; Inventory</div>
                    <div class="flex flex-1 items-center gap-4" style="margin-top:18px;">
                        <div style="position:relative; width:170px; height:170px; flex-shrink:0;">
                            <svg width="170" height="170" viewBox="0 0 180 180" style="transform:rotate(-90deg);">
                                <circle cx="90" cy="90" r="70" fill="none" stroke="#F1F4F9" stroke-width="22"/>
                                <circle v-for="seg in salesSegments" :key="seg.key" cx="90" cy="90" r="70" fill="none"
                                    :stroke="seg.color" stroke-width="22" stroke-linecap="round"
                                    :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset" />
                            </svg>
                            <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <div style="font-size:26px; font-weight:800; letter-spacing:-0.5px; color:#151B2E;">{{ formatNumber(salesInventory.total) }}</div>
                                <div style="font-size:11px; color:#697386;">Total Units</div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3.5">
                            <div v-for="seg in salesSegments" :key="seg.key">
                                <div style="display:flex; align-items:center; gap:7px; font-size:12px; color:#697386;">
                                    <span style="width:9px; height:9px; border-radius:3px;" :style="`background:${seg.color}`"></span>{{ seg.label }}
                                </div>
                                <div style="font-size:15px; font-weight:700; margin-left:16px; color:#151B2E;">
                                    {{ formatNumber(seg.count) }} <span style="color:#697386; font-weight:500; font-size:12px;">({{ seg.pct }}%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Link :href="route('admin.units.index')" style="margin-top:14px; font-size:13px; font-weight:600; color:#5B3DF5;" class="inline-flex items-center gap-1 hover:underline">
                        View Detailed Report →
                    </Link>
                </div>

                <!-- Payment trend line chart -->
                <div class="flex flex-col p-6">
                    <div style="font-size:17px; font-weight:700; color:#151B2E;">Payment Collection Trend</div>
                    <div class="relative flex-1" style="margin-top:14px;">
                        <svg width="100%" viewBox="0 0 620 250" preserveAspectRatio="none" style="display:block;">
                            <defs>
                                <linearGradient id="hvArea" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#5B3DF5" stop-opacity="0.18"/>
                                    <stop offset="100%" stop-color="#5B3DF5" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <g stroke="#F1F4F9" stroke-width="1">
                                <line v-for="(g, i) in trendGridLines" :key="i" x1="70" :y1="g.y" x2="610" :y2="g.y" />
                            </g>
                            <g fill="#A0A8B8" font-size="11" font-family="Inter" text-anchor="end">
                                <text v-for="(g, i) in trendGridLines" :key="i" x="58" :y="g.y + 4">{{ formatBDT(g.value) }}</text>
                            </g>
                            <polygon v-if="trendPoints.length" :points="trendAreaPoints" fill="url(#hvArea)"/>
                            <polyline v-if="trendPoints.length" :points="trendPolylinePoints" fill="none" stroke="#5B3DF5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <g fill="#fff" stroke="#5B3DF5" stroke-width="2.5">
                                <circle v-for="(p, i) in trendPoints.slice(0, -1)" :key="i" :cx="p.x" :cy="p.y" r="4.5"/>
                            </g>
                            <circle v-if="trendLatest" :cx="trendLatest.x" :cy="trendLatest.y" r="7" fill="#5B3DF5" stroke="#fff" stroke-width="3"/>
                            <g fill="#A0A8B8" font-size="11" font-family="Inter" text-anchor="middle">
                                <text v-for="(p, i) in trendPoints" :key="i" :x="p.x" y="232">{{ p.label }}</text>
                            </g>
                            <g v-if="trendLatest">
                                <rect x="500" y="14" width="108" height="40" rx="9" fill="#151B2E"/>
                                <text x="554" y="31" fill="#9AA3B4" font-size="11" font-family="Inter" text-anchor="middle">{{ trendLatest.label }}</text>
                                <text x="554" y="46" fill="#fff" font-size="13" font-weight="700" font-family="Inter" text-anchor="middle">{{ formatBDT(trendLatest.amount) }}</text>
                            </g>
                        </svg>
                    </div>
                    <Link :href="route('admin.payments.index')" style="margin-top:6px; font-size:13px; font-weight:600; color:#5B3DF5;" class="inline-flex items-center gap-1 hover:underline">
                        View Financial Report →
                    </Link>
                </div>
            </div>

            <!-- Project Progress -->
            <div class="flex flex-col rounded-[20px] border border-border bg-white shadow-card p-6">
                <div style="font-size:17px; font-weight:700; color:#151B2E; margin-bottom:18px;">Project Progress</div>
                <div class="flex flex-1 flex-col gap-[18px]">
                    <div v-if="projectProgress.length === 0" style="font-size:13px; color:#9AA3B4; text-align:center; padding:24px 0;">
                        No active projects yet.
                    </div>
                    <div v-for="(p, i) in projectProgress" :key="p.id" class="flex items-center gap-3">
                        <div class="flex h-[42px] w-[42px] flex-shrink-0 items-center justify-center rounded-[10px]"
                            :style="`background:${progressColors[i % progressColors.length]}22;`">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="progressColors[i % progressColors.length]" stroke-width="1.6">
                                <path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between" style="margin-bottom:6px;">
                                <span style="font-size:13.5px; font-weight:600; color:#151B2E;">{{ p.name }}</span>
                                <span style="font-size:13px; font-weight:700; color:#151B2E;">{{ p.progress }}%</span>
                            </div>
                            <div style="height:7px; background:#F1F4F9; border-radius:4px; overflow:hidden;">
                                <div :style="`height:100%; width:${p.progress}%; background:${progressColors[i % progressColors.length]}; border-radius:4px;`"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <Link :href="route('admin.projects.index')" style="margin-top:16px; font-size:13px; font-weight:600; color:#5B3DF5; align-self:center;" class="hover:underline">
                    View All Projects →
                </Link>
            </div>
        </div>

        <!-- ── LOWER SECTION ────────────────────────────────────────── -->
        <div class="grid gap-6" style="grid-template-columns:2fr 1fr; align-items:start;">

            <!-- LEFT COLUMN -->
            <div class="flex flex-col gap-6 min-w-0">

                <!-- Recent Reservations -->
                <div class="rounded-[20px] border border-border bg-white shadow-card p-6">
                    <div class="flex items-center justify-between" style="margin-bottom:8px;">
                        <div style="font-size:18px; font-weight:700; color:#151B2E;">Recent Reservations</div>
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
                                    <TableCell colspan="7" style="text-align:center; color:#9AA3B4; padding:32px 8px;">
                                        No reservations yet.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="b in recentBookings" :key="b.id">
                                    <TableCell>
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-[30px] w-[30px] flex-shrink-0 items-center justify-center rounded-full" style="background:#EDE9FE; color:#6D28D9; font-size:11px; font-weight:700;">{{ b.initials }}</span>
                                            <span style="font-size:13.5px; font-weight:600; color:#151B2E;">{{ b.buyer }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell style="font-size:13px; color:#3A4256;">{{ b.project }}</TableCell>
                                    <TableCell style="font-size:13px; color:#3A4256;">{{ b.unit }}</TableCell>
                                    <TableCell style="font-size:13px; color:#3A4256;">{{ b.type }}</TableCell>
                                    <TableCell style="font-size:13px; font-weight:600; color:#151B2E;">{{ formatBDT(b.amount) }}</TableCell>
                                    <TableCell style="font-size:13px; color:#697386;">{{ b.date }}</TableCell>
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
                    <div class="rounded-[20px] border border-border bg-white shadow-card p-6">
                        <div class="flex items-center justify-between" style="margin-bottom:6px;">
                            <div style="font-size:16px; font-weight:700; color:#151B2E;">Support Tickets Overview</div>
                            <Link :href="route('admin.support-tickets.index')" style="font-size:12.5px; font-weight:600; color:#5B3DF5;" class="hover:underline">View All →</Link>
                        </div>
                        <div v-if="supportOverview" class="flex items-center gap-3.5" style="margin-top:14px;">
                            <div style="position:relative; width:140px; height:140px; flex-shrink:0;">
                                <svg width="140" height="140" viewBox="0 0 150 150" style="transform:rotate(-90deg);">
                                    <circle cx="75" cy="75" r="60" fill="none" stroke="#F1F4F9" stroke-width="18"/>
                                    <circle v-for="seg in ticketSegments" :key="seg.key" cx="75" cy="75" r="60" fill="none"
                                        :stroke="seg.color" stroke-width="18"
                                        :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset" />
                                </svg>
                                <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                    <div style="font-size:24px; font-weight:800; color:#151B2E;">{{ formatNumber(ticketsTotal) }}</div>
                                    <div style="font-size:10px; color:#697386;">Total Tickets</div>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col gap-2.5">
                                <div v-for="seg in ticketSegments" :key="seg.key" class="flex items-center justify-between" style="font-size:12.5px;">
                                    <span style="display:flex; align-items:center; gap:7px; color:#697386;">
                                        <span style="width:8px; height:8px; border-radius:50%;" :style="`background:${seg.color}`"></span>{{ seg.label }}
                                    </span>
                                    <span style="font-weight:700; color:#151B2E;">{{ formatNumber(seg.count) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else style="font-size:13px; color:#9AA3B4; text-align:center; padding:32px 0;">No ticket data available.</div>
                    </div>

                    <!-- Top Performing Projects -->
                    <div class="rounded-[20px] border border-border bg-white shadow-card p-6">
                        <div class="flex items-center justify-between" style="margin-bottom:10px;">
                            <div style="font-size:16px; font-weight:700; color:#151B2E;">Top Performing Projects</div>
                            <Link :href="route('admin.projects.index')" style="font-size:12.5px; font-weight:600; color:#5B3DF5;" class="hover:underline">Full Report →</Link>
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead style="padding-left:4px;">Project</TableHead>
                                    <TableHead style="text-align:right;">Sold</TableHead>
                                    <TableHead style="text-align:right;">Total</TableHead>
                                    <TableHead style="text-align:right; padding-right:4px;">Conv.</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="topPerforming.length === 0">
                                    <TableCell colspan="4" style="text-align:center; color:#9AA3B4; padding:20px 4px;">No data yet.</TableCell>
                                </TableRow>
                                <TableRow v-for="p in topPerforming" :key="p.id">
                                    <TableCell style="font-size:12.5px; font-weight:600; color:#151B2E; padding-left:4px;">{{ p.name }}</TableCell>
                                    <TableCell style="font-size:12.5px; color:#3A4256; text-align:right;">{{ formatNumber(p.sold) }}</TableCell>
                                    <TableCell style="font-size:12.5px; color:#3A4256; text-align:right;">{{ formatNumber(p.total) }}</TableCell>
                                    <TableCell style="text-align:right; padding-right:4px;">
                                        <span style="font-size:12px; font-weight:700; color:#15803D;">{{ p.conversion }}%</span>
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
                <div class="rounded-[20px] border border-border bg-white shadow-card p-6">
                    <div style="font-size:17px; font-weight:700; color:#151B2E; margin-bottom:16px;">Quick Actions</div>
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
                            <span style="font-size:11.5px; font-weight:600; color:#3A4256;">{{ action.label }}</span>
                        </Link>
                    </div>
                </div>

                <!-- System Activity -->
                <div class="rounded-[20px] border border-border bg-white shadow-card p-6 flex-1">
                    <div style="font-size:17px; font-weight:700; color:#151B2E; margin-bottom:14px;">System Activity</div>
                    <div v-if="recentActivity.length === 0" style="font-size:13px; color:#9AA3B4; text-align:center; padding:24px 0;">
                        No recent activity.
                    </div>
                    <div class="flex flex-col gap-1">
                        <div v-for="(act, i) in recentActivity" :key="i" class="flex gap-3 rounded-[13px] p-3 transition-colors hover:bg-[#FAFBFE]">
                            <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-xl" style="background:#F1ECFF; color:#5B3DF5;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size:13.5px; font-weight:700; color:#151B2E;">{{ act.title }}</div>
                                <div style="font-size:12px; color:#697386; margin-top:2px; line-height:1.4;">{{ act.desc }}</div>
                                <div style="font-size:11px; color:#9AA3B4; margin-top:5px;">{{ act.time }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── FOOTER ───────────────────────────────────────────────── -->
        <footer class="flex items-center justify-between" style="padding:20px 4px 4px; font-size:12.5px; color:#9AA3B4;">
            <div>© {{ new Date().getFullYear() }} HomeVerse Admin</div>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5"><span style="width:7px; height:7px; border-radius:50%; background:#22C55E;"></span>System Online</span>
            </div>
        </footer>

    </AdminLayout>
</template>
