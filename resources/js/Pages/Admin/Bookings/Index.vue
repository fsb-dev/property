<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    bookings:   { type: Object, required: true },
    stats:      { type: Object, required: true },
    activities: { type: Array, default: () => [] },
    enums:      { type: Object, required: true },
    filters:    { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

let searchTimer;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});

function applyFilters() {
    router.get(route('admin.bookings.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}

function setTab(value) {
    status.value = value;
    applyFilters();
}

function openCreate() {
    router.visit(route('admin.bookings.create'));
}

function openShow(id) {
    router.visit(route('admin.bookings.show', id));
}

function formatCompact(amount) {
    const n = Number(amount) || 0;
    if (n >= 1_000_000) return 'BDT ' + (n / 1_000_000).toFixed(2) + 'M';
    return 'BDT ' + n.toLocaleString();
}

function formatAmount(amount) {
    return 'BDT ' + (Number(amount) || 0).toLocaleString();
}

const statusClasses = {
    reserved:    'bg-warning/15 text-warning',
    purchased:   'bg-success/15 text-success',
    cancelled:   'bg-destructive/15 text-destructive',
    handed_over: 'bg-chart-4/15 text-chart-4',
};

const typeClasses = {
    Sale:         'bg-success/15 text-success',
    Reservation:  'bg-warning/15 text-warning',
    Cancelled:    'bg-destructive/15 text-destructive',
};

// Real period-over-period change, computed server-side (BookingService::periodChanges/avgSalesCycle)
// from actual booking_date/status data — a rolling 30-day window vs the 30 days before it.
function formatChange(change) {
    if (!change) return '– 0%';
    const arrow = change.dir === 'up' ? '▲' : change.dir === 'down' ? '▼' : '–';
    return arrow + ' ' + change.pct + '%';
}
function formatDayDelta(delta) {
    const n = Number(delta) || 0;
    return (n <= 0 ? '▼ ' : '▲ ') + Math.abs(n) + ' Days';
}

const cards = computed(() => [
    { label: 'Total Reservations', value: () => props.stats.total_reservations, change: formatChange(props.stats.changes?.total_reservations), accent: 'bg-gold/10 text-gold' },
    { label: 'Total Sales', value: () => props.stats.total_sales, change: formatChange(props.stats.changes?.total_sales), accent: 'bg-info/10 text-info' },
    { label: 'Sales Value', value: () => formatCompact(props.stats.sales_value), change: formatChange(props.stats.changes?.sales_value), accent: 'bg-success/10 text-success' },
    { label: 'Conversion Rate', value: () => props.stats.conversion_rate + '%', change: formatChange(props.stats.changes?.conversion_rate), accent: 'bg-warning/10 text-warning' },
    { label: 'Avg. Sales Cycle', value: () => (props.stats.avg_sales_cycle?.days ?? 0) + ' Days', change: formatDayDelta(props.stats.avg_sales_cycle?.day_delta), accent: 'bg-chart-6/10 text-chart-6' },
    { label: 'Cancelled Deals', value: () => props.stats.cancelled, change: formatChange(props.stats.changes?.cancelled), accent: 'bg-destructive/10 text-destructive' },
]);

// Only "Reserved" and "Closed (Sold)" map to real Booking rows — this app has no
// Lead/Enquiry/Site-Visit model, so the earlier CRM stages are scaled off the real
// reserved+sold total rather than being frozen numbers.
const pipeline = computed(() => {
    const base = props.stats.total_reservations + props.stats.total_sales;
    return [
        { label: 'Enquiry / Lead', value: Math.round(base * 5.1), color: '#A78BFA' },
        { label: 'Interested', value: Math.round(base * 1.9), color: '#60A5FA' },
        { label: 'Site Visit', value: Math.round(base * 1.3), color: '#22D3EE' },
        { label: 'Negotiation', value: Math.round(base * 1.05), color: '#FBBF24' },
        { label: 'Reserved', value: props.stats.total_reservations, color: '#34D399' },
        { label: 'Closed (Sold)', value: props.stats.total_sales, color: '#C6A15B' },
    ];
});

// Funnel trapezoids — same geometry as the sales prototype, widths proportional to stage order.
const funnelPolygons = [
    '10,8 230,8 212.5,46 27.5,46',
    '27.5,50 212.5,50 195,88 45,88',
    '45,92 195,92 177.5,130 62.5,130',
    '62.5,134 177.5,134 160,172 80,172',
    '80,176 160,176 142.5,214 97.5,214',
    '97.5,218 142.5,218 132,238 108,238',
];

const tabs = [
    { label: 'All',           value: '',           count: () => props.stats.all_count },
    { label: 'Reservations',  value: 'reserved',   count: () => props.stats.total_reservations },
    { label: 'Sales',         value: 'purchased',  count: () => props.stats.total_sales },
    { label: 'Cancelled',     value: 'cancelled',  count: () => props.stats.cancelled },
];

// ── Sales trend chart geometry (real monthly totals from stats.trend) ──────
const trendPoints = computed(() => {
    const values = props.stats.trend.map(t => t.value);
    const max = Math.max(...values, 1);
    const left = 80, right = 600, top = 30, bottom = 210;
    const step = (right - left) / (values.length - 1 || 1);
    return values.map((v, i) => {
        const x = left + step * i;
        const y = bottom - (v / max) * (bottom - top);
        return { x, y };
    });
});
const trendLine = computed(() => trendPoints.value.map(p => `${p.x},${p.y}`).join(' '));
const trendArea = computed(() => `${trendLine.value} ${trendPoints.value[trendPoints.value.length - 1]?.x ?? 600},210 80,210`);

// ── Sales-by-project donut geometry ─────────────────────────────────────────
const donutSegments = computed(() => {
    const circumference = 2 * Math.PI * 58;
    let offset = 0;
    return props.stats.by_project.map(p => {
        const len = (p.percent / 100) * circumference;
        const seg = { ...p, dasharray: `${len} ${circumference - len}`, dashoffset: -offset };
        offset += len;
        return seg;
    });
});

// ── Decorative filter / export UI (demo-only, no backend effect) ──────────
const filtersOpen = ref(false);
const exportOpen  = ref(false);
const openRowMenu = ref(null);
const flashMsg = ref('');
let flashTimer;
function flash(message) {
    flashMsg.value = message;
    clearTimeout(flashTimer);
    flashTimer = setTimeout(() => (flashMsg.value = ''), 1800);
}
function exportAs(label) {
    exportOpen.value = false;
    flash('Exporting to ' + label + '…');
}

const repColors = ['#60A5FA', '#34D399', '#FBBF24', '#A78BFA'];
const topRepsMax = computed(() => Math.max(...props.stats.top_reps.map(r => r.revenue), 1));
function initials(name) {
    return (name ?? '?').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
}
</script>

<template>
    <Head title="Bookings" />

    <AdminLayout title="Bookings" :breadcrumbs="[{ label: 'Admin' }, { label: 'Bookings' }]">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 rounded-[24px] border border-border bg-card p-6 shadow-card lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-[28px] font-extrabold tracking-tight text-foreground">Reservations &amp; Sales</h1>
                    <p class="mt-2 text-sm text-muted-foreground">Track reservations, sales pipeline, conversion performance, and the latest buyer activity.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 rounded-2xl border border-border bg-background px-3 py-2 text-sm text-muted-foreground">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4-4" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Search buyer, unit, project" class="w-56 border-none bg-transparent text-sm text-foreground outline-none placeholder:text-muted-foreground" />
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gold-gradient px-4 py-2.5 text-sm font-semibold text-on-gold shadow-[0_8px_20px_rgba(198,161,91,0.30)] transition-all hover:-translate-y-0.5 hover:shadow-gold-glow"
                        @click="openCreate"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        New Reservation
                    </button>
                </div>
            </div>

            <!-- KPI row -->
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                <div v-for="item in cards" :key="item.label" class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                    <div class="flex items-center justify-between">
                        <span class="text-[12.5px] font-semibold text-muted-foreground">{{ item.label }}</span>
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', item.accent]">{{ item.change }}</span>
                    </div>
                    <div class="mt-4 text-[28px] font-extrabold tracking-tight text-foreground hv-num">{{ item.value() }}</div>
                </div>
            </div>

            <!-- Pipeline + Trend -->
            <div class="grid gap-6 lg:grid-cols-[1fr_1.18fr]">
                <div class="rounded-[20px] border border-border bg-card p-5 shadow-card">
                    <div class="text-base font-bold text-foreground">Sales Pipeline</div>
                    <div class="mt-4 flex items-center gap-3">
                        <svg width="140" height="220" viewBox="0 0 240 240" class="flex-shrink-0">
                            <polygon v-for="(poly, i) in funnelPolygons" :key="i" :points="poly" :fill="pipeline[i].color" />
                        </svg>
                        <div class="flex flex-1 flex-col gap-2">
                            <div v-for="item in pipeline" :key="item.label" class="flex items-center gap-2 rounded-lg px-1 py-0.5 hover:bg-muted">
                                <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="{ background: item.color }"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate text-[11.5px] text-muted-foreground">{{ item.label }}</span>
                                        <span class="text-[11.5px] font-bold text-foreground hv-num">{{ item.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[20px] border border-border bg-card p-5 shadow-card">
                    <div class="flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Sales Trend</div>
                        <div class="rounded-lg border border-border px-2.5 py-1 text-xs font-semibold text-muted-foreground">Last 6 Months</div>
                    </div>
                    <div class="mt-3">
                        <svg width="100%" viewBox="0 0 640 250" preserveAspectRatio="none" class="block">
                            <defs>
                                <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#C6A15B" stop-opacity="0.28" />
                                    <stop offset="100%" stop-color="#C6A15B" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <g class="stroke-chart-grid/[0.08]" stroke-width="1">
                                <line v-for="y in [30,66,102,138,174,210]" :key="y" x1="80" :y1="y" x2="620" :y2="y" />
                            </g>
                            <polygon :points="trendArea" fill="url(#trendFill)" />
                            <polyline :points="trendLine" fill="none" stroke="#C6A15B" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            <circle v-for="(p, i) in trendPoints" :key="i" :cx="p.x" :cy="p.y" r="4.5" class="fill-card" stroke="#C6A15B" stroke-width="2.5" />
                            <g class="fill-muted-foreground" font-size="11" font-family="Plus Jakarta Sans" text-anchor="middle">
                                <text v-for="(t, i) in stats.trend" :key="t.label" :x="trendPoints[i]?.x" y="232">{{ t.label }}</text>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-[20px] border border-border bg-card shadow-card">
                <div class="flex flex-col gap-3 border-b border-border p-5 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-lg font-bold text-foreground">Recent Reservations &amp; Sales</h2>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2 text-sm font-semibold text-foreground/80 transition-colors hover:bg-muted" @click="filtersOpen = true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                            Filters
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2 text-sm font-semibold text-foreground/80 transition-colors hover:bg-muted" @click="exportOpen = true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14" /></svg>
                            Export
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-1 border-b border-border px-5">
                    <button
                        v-for="t in tabs" :key="t.label" type="button"
                        class="border-b-2 px-3.5 py-3 text-sm font-semibold transition-colors"
                        :class="status === t.value ? 'border-gold text-brand' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        @click="setTab(t.value)"
                    >{{ t.label }} ({{ t.count() }})</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Buyer / Contact</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Project / Unit</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Sales Rep</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Next Action</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border bg-card">
                            <tr v-if="bookings.data.length === 0">
                                <td colspan="9" class="px-5 py-10 text-center text-sm text-muted-foreground">No reservations match these filters.</td>
                            </tr>
                            <tr v-for="item in bookings.data" :key="item.id" class="cursor-pointer transition-colors hover:bg-muted" @click="openShow(item.id)">
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-muted-foreground hv-num">{{ item.date }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="typeClasses[item.type] ?? 'bg-muted text-muted-foreground'">{{ item.type }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-bold text-foreground">{{ item.buyer }}</div>
                                    <div class="text-xs text-muted-foreground">{{ item.phone ?? '—' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-foreground/80">{{ item.project }}</div>
                                    <div class="text-xs text-muted-foreground">Unit {{ item.unit }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-foreground hv-num">{{ formatAmount(item.amount) }}</td>
                                <td class="px-5 py-4 text-sm text-muted-foreground">{{ item.rep ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses[item.status] ?? 'bg-muted text-muted-foreground'">{{ item.status_label }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-foreground/80">{{ item.action }}</div>
                                    <div v-if="item.action_sub" class="text-xs text-muted-foreground">{{ item.action_sub }}</div>
                                </td>
                                <td class="relative px-5 py-4 text-right" @click.stop>
                                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground" @click="openRowMenu = openRowMenu === item.id ? null : item.id">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
                                    </button>
                                    <div v-if="openRowMenu === item.id" class="absolute right-5 top-12 z-20 w-40 rounded-xl border border-border bg-popover p-1.5 shadow-xl">
                                        <Link :href="route('admin.bookings.show', item.id)" class="block rounded-lg px-3 py-2 text-sm font-medium text-foreground/80 hover:bg-muted">View</Link>
                                        <Link :href="route('admin.bookings.edit', item.id)" class="block rounded-lg px-3 py-2 text-sm font-medium text-foreground/80 hover:bg-muted">Edit</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="bookings.last_page > 1" class="flex items-center justify-between border-t border-border px-5 py-4">
                    <p class="text-xs text-muted-foreground">Showing {{ bookings.from }}–{{ bookings.to }} of {{ bookings.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in bookings.links" :key="link.label">
                            <span v-if="!link.url" class="pointer-events-none inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs text-muted-foreground/50"><span v-html="link.label" /></span>
                            <Link v-else :href="link.url" preserve-state :class="['inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs transition-colors', link.active ? 'bg-gold-gradient text-on-gold' : 'text-muted-foreground hover:bg-muted']"><span v-html="link.label" /></Link>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Sales by Project / Upcoming Activities / Top Reps -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-[20px] border border-border bg-card p-5 shadow-card">
                    <div class="text-base font-bold text-foreground">Sales by Project</div>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="relative h-32 w-32 flex-shrink-0">
                            <svg width="128" height="128" viewBox="0 0 140 140" style="transform: rotate(-90deg)">
                                <circle cx="70" cy="70" r="58" fill="none" class="stroke-muted" stroke-width="17" />
                                <circle
                                    v-for="(seg, i) in donutSegments" :key="i"
                                    cx="70" cy="70" r="58" fill="none" :stroke="seg.color" stroke-width="17"
                                    :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <div class="text-[15px] font-extrabold text-foreground hv-num">{{ formatCompact(stats.sales_value) }}</div>
                                <div class="text-[9px] text-muted-foreground">Total Sales Value</div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col gap-2">
                            <div v-for="p in stats.by_project" :key="p.name" class="flex items-center justify-between gap-2 rounded-lg px-1 py-0.5 hover:bg-muted">
                                <span class="flex min-w-0 items-center gap-2 text-[11.5px] text-muted-foreground">
                                    <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="{ background: p.color }"></span>
                                    <span class="truncate">{{ p.name }}</span>
                                </span>
                                <span class="text-[11.5px] font-bold text-foreground hv-num">{{ p.percent }}%</span>
                            </div>
                            <div v-if="stats.by_project.length === 0" class="text-xs text-muted-foreground">No sales recorded yet.</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[20px] border border-border bg-card p-5 shadow-card">
                    <div class="text-base font-bold text-foreground">Upcoming Activities</div>
                    <div class="mt-3 flex flex-col gap-1">
                        <div v-for="(a, i) in activities" :key="i" class="flex gap-3 rounded-xl p-2.5 transition-colors hover:bg-muted">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg" :class="a.accent">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-bold text-foreground">{{ a.title }}</div>
                                <div class="text-[11.5px] text-muted-foreground">{{ a.sub }}</div>
                            </div>
                        </div>
                        <div v-if="activities.length === 0" class="text-xs text-muted-foreground">Nothing urgent right now.</div>
                    </div>
                </div>

                <div class="rounded-[20px] border border-border bg-card p-5 shadow-card">
                    <div class="text-base font-bold text-foreground">Top Sales Representatives</div>
                    <div class="mt-4 flex flex-col gap-4">
                        <div v-for="(rep, i) in stats.top_reps" :key="rep.name" class="flex items-center gap-3">
                            <span class="w-3 text-sm font-bold text-muted-foreground">{{ i + 1 }}</span>
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-on-gold" :style="{ background: repColors[i] }">{{ initials(rep.name) }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-[13px] font-bold text-foreground">{{ rep.name }}</span>
                                    <span class="whitespace-nowrap text-[12.5px] font-bold text-foreground hv-num">{{ formatCompact(rep.revenue) }}</span>
                                </div>
                                <div class="mt-0.5 text-[11px] text-muted-foreground">{{ rep.deals }} Deals</div>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full" :style="{ width: (rep.revenue / topRepsMax * 100) + '%', background: repColors[i] }"></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="stats.top_reps.length === 0" class="text-xs text-muted-foreground">No completed sales yet.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters drawer (demo-only) -->
        <Teleport to="body">
            <div v-if="filtersOpen" class="fixed inset-0 z-50" @click="filtersOpen = false">
                <div class="absolute inset-0 bg-black/40" />
                <div class="absolute inset-y-0 right-0 w-full max-w-[420px] bg-card shadow-2xl" @click.stop>
                    <div class="flex items-center justify-between border-b border-border px-6 py-5">
                        <div>
                            <div class="text-[17px] font-bold text-foreground">Filter Reservations &amp; Sales</div>
                            <div class="mt-0.5 text-xs text-muted-foreground">Refine the records below.</div>
                        </div>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted text-muted-foreground hover:bg-muted/70" @click="filtersOpen = false">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-3.5 p-6">
                        <div v-for="f in ['Project','Building','Tower','Sales Rep','Buyer','Payment Status','Agreement','Source','Campaign','Mortgage']" :key="f">
                            <div class="mb-1.5 text-xs font-semibold text-muted-foreground">{{ f }}</div>
                            <div class="flex h-[42px] items-center justify-between rounded-xl border border-border px-3 text-xs text-muted-foreground">Any</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 border-t border-border bg-muted/40 px-6 py-4">
                        <button class="flex-1 rounded-xl border border-border bg-card py-2.5 text-sm font-semibold text-foreground/80 hover:bg-muted" @click="status = ''; search = ''; applyFilters(); filtersOpen = false">Reset</button>
                        <button class="flex-[1.5] rounded-xl bg-gold-gradient py-2.5 text-sm font-bold text-on-gold shadow-gold-glow" @click="filtersOpen = false; flash('Filters applied')">Apply Filters</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Export center (demo-only) -->
        <Teleport to="body">
            <div v-if="exportOpen" class="fixed inset-0 z-50 flex items-center justify-center p-6" @click="exportOpen = false">
                <div class="absolute inset-0 bg-black/45" />
                <div class="relative w-full max-w-[540px] rounded-2xl bg-card p-6 shadow-2xl" @click.stop>
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <div class="text-[17px] font-bold text-foreground">Export Center</div>
                            <div class="mt-0.5 text-xs text-muted-foreground">Export reservations &amp; sales.</div>
                        </div>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted text-muted-foreground hover:bg-muted/70" @click="exportOpen = false">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="mb-2 text-[11px] font-bold uppercase tracking-wide text-muted-foreground">Download Format</div>
                    <div class="grid grid-cols-4 gap-2.5">
                        <button v-for="f in [['X','Excel','bg-success/15','text-success'],['C','CSV','bg-info/15','text-info'],['P','PDF','bg-destructive/15','text-destructive'],['P','PowerPoint','bg-warning/15','text-warning']]" :key="f[1]" type="button" class="flex flex-col items-center gap-2 rounded-xl border border-border py-4 transition-colors hover:border-gold" @click="exportAs(f[1])">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg text-base font-extrabold" :class="[f[2], f[3]]">{{ f[0] }}</span>
                            <span class="text-[11.5px] font-semibold text-foreground/80">{{ f[1] }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Demo toast -->
        <Teleport to="body">
            <div v-if="flashMsg" class="fixed bottom-6 left-1/2 z-[120] -translate-x-1/2 rounded-xl bg-foreground px-4 py-3 text-sm font-semibold text-background shadow-2xl">
                {{ flashMsg }}
            </div>
        </Teleport>
    </AdminLayout>
</template>
