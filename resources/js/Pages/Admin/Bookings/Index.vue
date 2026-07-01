<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    bookings: { type: Object, required: true },
    stats:    { type: Object, required: true },
    enums:    { type: Object, required: true },
    filters:  { type: Object, default: () => ({}) },
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
    reserved:    'bg-amber-50 text-amber-700',
    purchased:   'bg-emerald-50 text-emerald-700',
    cancelled:   'bg-rose-50 text-rose-700',
    handed_over: 'bg-violet-50 text-violet-700',
};

const typeClasses = {
    Sale:         'bg-emerald-50 text-emerald-700',
    Reservation:  'bg-amber-50 text-amber-700',
    Cancelled:    'bg-rose-50 text-rose-700',
};

const cards = [
    { label: 'Total Reservations', value: () => props.stats.total_reservations, change: '▲ 16%', accent: 'bg-violet-50 text-violet-600' },
    { label: 'Total Sales', value: () => props.stats.total_sales, change: '▲ 18%', accent: 'bg-sky-50 text-sky-600' },
    { label: 'Sales Value', value: () => formatCompact(props.stats.sales_value), change: '▲ 22%', accent: 'bg-emerald-50 text-emerald-600' },
    { label: 'Conversion Rate', value: () => props.stats.conversion_rate + '%', change: '▲ 3.4%', accent: 'bg-amber-50 text-amber-600' },
    { label: 'Avg. Sales Cycle', value: () => '21 Days', change: '▼ 2 Days', accent: 'bg-orange-50 text-orange-600' },
    { label: 'Cancelled Deals', value: () => props.stats.cancelled, change: '▼ 20%', accent: 'bg-rose-50 text-rose-600' },
];

const pipeline = computed(() => [
    { label: 'Enquiry / Lead', value: 856, color: '#5B3DF5' },
    { label: 'Interested', value: 312, color: '#3B82F6' },
    { label: 'Site Visit', value: 212, color: '#22C55E' },
    { label: 'Negotiation', value: 168, color: '#F59E0B' },
    { label: 'Reserved', value: props.stats.total_reservations, color: '#EF4444' },
    { label: 'Closed (Sold)', value: props.stats.total_sales, color: '#EC4899' },
]);

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

const upcomingActivities = [
    { icon: 'due', title: 'Payment due from a reserved buyer', sub: 'Check the Reservations tab for upcoming dues', accent: 'bg-violet-50 text-violet-600' },
    { icon: 'visit', title: 'Site visit scheduled', sub: 'Coordinate with the assigned sales rep', accent: 'bg-emerald-50 text-emerald-600' },
    { icon: 'doc', title: 'Agreement signing', sub: 'Legal team to prepare documents', accent: 'bg-sky-50 text-sky-600' },
    { icon: 'due', title: 'Follow up on pending approval', sub: 'Manager sign-off outstanding', accent: 'bg-amber-50 text-amber-600' },
];

const repColors = ['#3B82F6', '#22C55E', '#F59E0B', '#7C3AED'];
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
            <div class="flex flex-col gap-4 rounded-[24px] border border-slate-200 bg-white p-6 shadow-[0_8px_24px_rgba(20,20,40,0.04)] lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-[28px] font-[800] tracking-[-0.5px] text-slate-900">Reservations &amp; Sales</h1>
                    <p class="mt-2 text-sm text-slate-500">Track reservations, sales pipeline, conversion performance, and the latest buyer activity.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4-4" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Search buyer, unit, project" class="w-56 border-none bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400" />
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5"
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
                <div v-for="item in cards" :key="item.label" class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
                    <div class="flex items-center justify-between">
                        <span class="text-[12.5px] font-semibold text-slate-500">{{ item.label }}</span>
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', item.accent]">{{ item.change }}</span>
                    </div>
                    <div class="mt-4 text-[28px] font-[800] tracking-[-1px] text-slate-900">{{ item.value() }}</div>
                </div>
            </div>

            <!-- Pipeline + Trend -->
            <div class="grid gap-6 lg:grid-cols-[1fr_1.18fr]">
                <div class="rounded-[20px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
                    <div class="text-base font-bold text-slate-900">Sales Pipeline</div>
                    <div class="mt-4 flex items-center gap-3">
                        <svg width="140" height="220" viewBox="0 0 240 240" class="flex-shrink-0">
                            <polygon v-for="(poly, i) in funnelPolygons" :key="i" :points="poly" :fill="pipeline[i].color" />
                        </svg>
                        <div class="flex flex-1 flex-col gap-2">
                            <div v-for="item in pipeline" :key="item.label" class="flex items-center gap-2 rounded-lg px-1 py-0.5 hover:bg-slate-50">
                                <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="{ background: item.color }"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate text-[11.5px] text-slate-500">{{ item.label }}</span>
                                        <span class="text-[11.5px] font-bold text-slate-900">{{ item.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[20px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
                    <div class="flex items-center justify-between">
                        <div class="text-base font-bold text-slate-900">Sales Trend</div>
                        <div class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">Last 6 Months</div>
                    </div>
                    <div class="mt-3">
                        <svg width="100%" viewBox="0 0 640 250" preserveAspectRatio="none" class="block">
                            <defs>
                                <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#5B3DF5" stop-opacity="0.18" />
                                    <stop offset="100%" stop-color="#5B3DF5" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <g stroke="#F1F4F9" stroke-width="1">
                                <line v-for="y in [30,66,102,138,174,210]" :key="y" x1="80" :y1="y" x2="620" :y2="y" />
                            </g>
                            <polygon :points="trendArea" fill="url(#trendFill)" />
                            <polyline :points="trendLine" fill="none" stroke="#5B3DF5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            <circle v-for="(p, i) in trendPoints" :key="i" :cx="p.x" :cy="p.y" r="4.5" fill="#fff" stroke="#5B3DF5" stroke-width="2.5" />
                            <g fill="#A0A8B8" font-size="11" font-family="Inter" text-anchor="middle">
                                <text v-for="(t, i) in stats.trend" :key="t.label" :x="trendPoints[i]?.x" y="232">{{ t.label }}</text>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-[20px] border border-slate-200 bg-white shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-lg font-bold text-slate-900">Recent Reservations &amp; Sales</h2>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" @click="filtersOpen = true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                            Filters
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" @click="exportOpen = true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14" /></svg>
                            Export
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-1 border-b border-slate-100 px-5">
                    <button
                        v-for="t in tabs" :key="t.label" type="button"
                        class="border-b-2 px-3.5 py-3 text-sm font-semibold transition"
                        :class="status === t.value ? 'border-violet-600 text-violet-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        @click="setTab(t.value)"
                    >{{ t.label }} ({{ t.count() }})</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Buyer / Contact</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Project / Unit</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sales Rep</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Next Action</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-if="bookings.data.length === 0">
                                <td colspan="9" class="px-5 py-10 text-center text-sm text-slate-500">No reservations match these filters.</td>
                            </tr>
                            <tr v-for="item in bookings.data" :key="item.id" class="cursor-pointer transition hover:bg-slate-50" @click="openShow(item.id)">
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ item.date }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="typeClasses[item.type] ?? 'bg-slate-50 text-slate-700'">{{ item.type }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-bold text-slate-900">{{ item.buyer }}</div>
                                    <div class="text-xs text-slate-400">{{ item.phone ?? '—' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-slate-700">{{ item.project }}</div>
                                    <div class="text-xs text-slate-400">Unit {{ item.unit }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-slate-900">{{ formatAmount(item.amount) }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ item.rep ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses[item.status] ?? 'bg-slate-50 text-slate-700'">{{ item.status_label }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-slate-700">{{ item.action }}</div>
                                    <div v-if="item.action_sub" class="text-xs text-slate-400">{{ item.action_sub }}</div>
                                </td>
                                <td class="relative px-5 py-4 text-right" @click.stop>
                                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="openRowMenu = openRowMenu === item.id ? null : item.id">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
                                    </button>
                                    <div v-if="openRowMenu === item.id" class="absolute right-5 top-12 z-20 w-40 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                                        <Link :href="route('admin.bookings.show', item.id)" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">View</Link>
                                        <Link :href="route('admin.bookings.edit', item.id)" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Edit</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="bookings.last_page > 1" class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                    <p class="text-xs text-slate-500">Showing {{ bookings.from }}–{{ bookings.to }} of {{ bookings.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in bookings.links" :key="link.label">
                            <span v-if="!link.url" class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs pointer-events-none text-slate-300"><span v-html="link.label" /></span>
                            <Link v-else :href="link.url" preserve-state :class="['inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs transition-colors', link.active ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100']"><span v-html="link.label" /></Link>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Sales by Project / Upcoming Activities / Top Reps -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-[20px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
                    <div class="text-base font-bold text-slate-900">Sales by Project</div>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="relative h-32 w-32 flex-shrink-0">
                            <svg width="128" height="128" viewBox="0 0 140 140" style="transform: rotate(-90deg)">
                                <circle cx="70" cy="70" r="58" fill="none" stroke="#F1F4F9" stroke-width="17" />
                                <circle
                                    v-for="(seg, i) in donutSegments" :key="i"
                                    cx="70" cy="70" r="58" fill="none" :stroke="seg.color" stroke-width="17"
                                    :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <div class="text-[15px] font-[800] text-slate-900">{{ formatCompact(stats.sales_value) }}</div>
                                <div class="text-[9px] text-slate-400">Total Sales Value</div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col gap-2">
                            <div v-for="p in stats.by_project" :key="p.name" class="flex items-center justify-between gap-2 rounded-lg px-1 py-0.5 hover:bg-slate-50">
                                <span class="flex min-w-0 items-center gap-2 text-[11.5px] text-slate-500">
                                    <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="{ background: p.color }"></span>
                                    <span class="truncate">{{ p.name }}</span>
                                </span>
                                <span class="text-[11.5px] font-bold text-slate-900">{{ p.percent }}%</span>
                            </div>
                            <div v-if="stats.by_project.length === 0" class="text-xs text-slate-400">No sales recorded yet.</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[20px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
                    <div class="text-base font-bold text-slate-900">Upcoming Activities</div>
                    <div class="mt-3 flex flex-col gap-1">
                        <div v-for="(a, i) in upcomingActivities" :key="i" class="flex gap-3 rounded-xl p-2.5 transition hover:bg-slate-50">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg" :class="a.accent">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-bold text-slate-900">{{ a.title }}</div>
                                <div class="text-[11.5px] text-slate-400">{{ a.sub }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[20px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(0,0,0,0.04)]">
                    <div class="text-base font-bold text-slate-900">Top Sales Representatives</div>
                    <div class="mt-4 flex flex-col gap-4">
                        <div v-for="(rep, i) in stats.top_reps" :key="rep.name" class="flex items-center gap-3">
                            <span class="w-3 text-sm font-bold text-slate-400">{{ i + 1 }}</span>
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" :style="{ background: repColors[i] }">{{ initials(rep.name) }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-[13px] font-bold text-slate-900">{{ rep.name }}</span>
                                    <span class="whitespace-nowrap text-[12.5px] font-bold text-slate-900">{{ formatCompact(rep.revenue) }}</span>
                                </div>
                                <div class="mt-0.5 text-[11px] text-slate-400">{{ rep.deals }} Deals</div>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full" :style="{ width: (rep.revenue / topRepsMax * 100) + '%', background: repColors[i] }"></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="stats.top_reps.length === 0" class="text-xs text-slate-400">No completed sales yet.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters drawer (demo-only) -->
        <Teleport to="body">
            <div v-if="filtersOpen" class="fixed inset-0 z-50" @click="filtersOpen = false">
                <div class="absolute inset-0 bg-black/40" />
                <div class="absolute inset-y-0 right-0 w-full max-w-[420px] bg-white shadow-2xl" @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                        <div>
                            <div class="text-[17px] font-bold text-slate-900">Filter Reservations &amp; Sales</div>
                            <div class="mt-0.5 text-xs text-slate-400">Refine the records below.</div>
                        </div>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200" @click="filtersOpen = false">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-3.5 p-6">
                        <div v-for="f in ['Project','Building','Tower','Sales Rep','Buyer','Payment Status','Agreement','Source','Campaign','Mortgage']" :key="f">
                            <div class="mb-1.5 text-xs font-semibold text-slate-500">{{ f }}</div>
                            <div class="flex h-[42px] items-center justify-between rounded-xl border border-slate-200 px-3 text-xs text-slate-600">Any</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="status = ''; search = ''; applyFilters(); filtersOpen = false">Reset</button>
                        <button class="flex-[1.5] rounded-xl bg-violet-600 py-2.5 text-sm font-bold text-white shadow-lg shadow-violet-600/30" @click="filtersOpen = false; flash('Filters applied')">Apply Filters</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Export center (demo-only) -->
        <Teleport to="body">
            <div v-if="exportOpen" class="fixed inset-0 z-50 flex items-center justify-center p-6" @click="exportOpen = false">
                <div class="absolute inset-0 bg-black/45" />
                <div class="relative w-full max-w-[540px] rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <div class="text-[17px] font-bold text-slate-900">Export Center</div>
                            <div class="mt-0.5 text-xs text-slate-400">Export reservations &amp; sales.</div>
                        </div>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200" @click="exportOpen = false">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">Download Format</div>
                    <div class="grid grid-cols-4 gap-2.5">
                        <button v-for="f in [['X','Excel','#E6F7EE','#15803D'],['C','CSV','#E8F0FF','#2563EB'],['P','PDF','#FDE8E8','#DC2626'],['P','PowerPoint','#FFF3E0','#B45309']]" :key="f[1]" type="button" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 py-4 hover:border-violet-300" @click="exportAs(f[1])">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg text-base font-[800]" :style="{ background: f[2], color: f[3] }">{{ f[0] }}</span>
                            <span class="text-[11.5px] font-semibold text-slate-700">{{ f[1] }}</span>
                        </button>
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
