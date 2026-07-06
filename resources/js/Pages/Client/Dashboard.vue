<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    client: { type: Object, default: () => ({}) },
    latestBooking: { type: Object, default: null },
    totalPropertyValue: { type: Number, default: 0 },
    totalPaid: { type: Number, default: 0 },
    outstandingBalance: { type: Number, default: 0 },
    paymentCards: { type: Array, default: () => [] },
    recommendedProjects: { type: Array, default: () => [] },
    constructionCards: { type: Array, default: () => [] },
    recentPayments: { type: Array, default: () => [] },
    coverImage: { type: String, default: null },
});

// ── Helpers ──────────────────────────────────────────────────────────
function fmtBDT(n) {
    if (!n) return 'BDT 0';
    return 'BDT ' + Number(n).toLocaleString('en-US');
}
function paidPct(paid, total) {
    if (!total) return 0;
    return Math.round((paid / total) * 100);
}

const unitTypeLabels = {
    studio: 'Studio', apartment: 'Apartment', penthouse: 'Penthouse', duplex: 'Duplex',
    villa: 'Villa', townhouse: 'Townhouse', shop: 'Shop', office: 'Office',
    commercial_space: 'Commercial Space', warehouse: 'Warehouse',
};

const constructionStatusMap = {
    on_track: { cls: 'text-green-600 dark:text-green-400' },
    delayed: { cls: 'text-red-500 dark:text-red-400' },
    completed: { cls: 'text-violet-600 dark:text-violet-400' },
    inspection: { cls: 'text-blue-600 dark:text-blue-400' },
    paused: { cls: 'text-amber-600 dark:text-amber-400' },
};

// ── Hero computed ─────────────────────────────────────────────────────
const unit = computed(() => props.latestBooking?.unit ?? null);
const project = computed(() => props.latestBooking?.unit?.project ?? null);

const totalPaidPct = computed(() => paidPct(props.totalPaid, props.totalPropertyValue));

const installmentDate = computed(() => {
    if (!props.latestBooking?.booking_date) return null;
    const d = new Date(props.latestBooking.booking_date);
    d.setDate(d.getDate() + 30);
    return d;
});
const nextInstallmentDisplay = computed(() => {
    if (!installmentDate.value) return '—';
    return installmentDate.value.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
});
const daysRemaining = computed(() => {
    if (!installmentDate.value) return null;
    const days = Math.ceil((installmentDate.value - new Date()) / 86400000);
    if (days < 0) return 'Overdue';
    if (days === 0) return 'Due Today';
    return `${days} Days Remaining`;
});

const statusBadge = computed(() => {
    const map = {
        draft: { label: 'Draft', cls: 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-300' },
        reserved: { label: 'Reserved', cls: 'bg-[#e6f7ed] text-green-700 dark:bg-green-500/15 dark:text-green-400' },
        purchased: { label: 'Sold', cls: 'bg-[#e6effd] text-blue-700 dark:bg-blue-500/15 dark:text-blue-400' },
        cancelled: { label: 'Cancelled', cls: 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' },
        handed_over: { label: 'Handed Over', cls: 'bg-[#efeafc] text-violet-700 dark:bg-violet-500/15 dark:text-violet-400' },
    };
    return map[props.latestBooking?.status] ?? { label: props.latestBooking?.status ?? '—', cls: 'bg-slate-100 text-slate-600' };
});

// ── ApexCharts radialBar helpers ──────────────────────────────────────
function radialBarBase(color, trackColor) {
    return {
        chart: { type: 'radialBar', sparkline: { enabled: true } },
        plotOptions: {
            radialBar: {
                hollow: { size: '52%' },
                track: { background: trackColor, strokeWidth: '100%' },
                dataLabels: {
                    name: { show: false },
                    value: {
                        show: true,
                        fontSize: '11px',
                        fontFamily: "'Plus Jakarta Sans', system-ui, sans-serif",
                        fontWeight: 900,
                        color: '#16162a',
                        offsetY: 4,
                        formatter: (val) => Math.round(val) + '%',
                    },
                },
            },
        },
        colors: [color],
        states: { hover: { filter: { type: 'none' } }, active: { filter: { type: 'none' } } },
    };
}
const paymentChartOpts      = () => radialBarBase('#16a34a', 'rgba(22,163,74,0.1)');
const constructionChartOpts = () => radialBarBase('#7b63ff', 'rgba(123,99,255,0.1)');

// ── Dummy data ────────────────────────────────────────────────────────
const latestUpdates = [
    { title: 'Tower A – 12th Floor Completed', sub: '2 days ago' },
    { title: 'Landscaping Work In Progress', sub: '5 days ago' },
    { title: 'Interior Finishing Started', sub: '1 week ago' },
    { title: 'Drone Progress Video Uploaded', sub: '2 weeks ago' },
];
// const recommended = [
//     { name: 'Green Park Heights', beds: '3 Bed', sqft: '1,450 sft', price: 'BDT 14,800,000' },
//     { name: 'Skyline Towers', beds: '2 Bed', sqft: '1,250 sft', price: 'BDT 11,250,000' },
//     { name: 'River View Plaza', beds: '3 Bed', sqft: '1,600 sft', price: 'BDT 17,200,000' },
//     { name: 'Palm Garden Villa', beds: '4 Bed', sqft: '2,100 sft', price: 'BDT 24,500,000' },
// ];
</script>

<template>

    <Head title="My Dashboard" />
    <ClientLayout title="Dashboard">

        <!-- ── KPI cards ─────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                class="flex flex-col gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-[#efeafc]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5b3fe8" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10.5 12 3l9 7.5" />
                            <path d="M5 9.5V21h14V9.5" />
                            <path d="M10 21v-6h4v6" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-muted-foreground">Total Property Value</span>
                </div>
                <div class="text-2xl font-extrabold tracking-tight text-foreground">{{ fmtBDT(totalPropertyValue) }}
                </div>
            </div>

            <div
                class="flex flex-col gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-green-100 dark:bg-green-500/15">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="7" width="18" height="13" rx="2.5" />
                            <path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7" />
                            <path d="M3 12h18" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-muted-foreground">Paid Amount</span>
                </div>
                <div>
                    <div class="text-2xl font-extrabold tracking-tight text-foreground">{{ fmtBDT(totalPaid) }}</div>
                    <div class="mt-1 text-sm font-bold text-green-600 dark:text-green-400">
                        {{ totalPaidPct }}% <span class="font-medium text-muted-foreground">of Total</span>
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f08a1d" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="10.5" width="16" height="10.5" rx="2.5" />
                            <path d="M7.5 10.5V8a4.5 4.5 0 0 1 9 0v2.5" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-muted-foreground">Outstanding Balance</span>
                </div>
                <div>
                    <div class="text-2xl font-extrabold tracking-tight text-foreground">{{ fmtBDT(outstandingBalance) }}
                    </div>
                    <div class="mt-1 text-sm font-bold text-amber-600 dark:text-amber-400">
                        {{ 100 - totalPaidPct }}% <span class="font-medium text-muted-foreground">Remaining</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Hero ──────────────────────────────────────────────── -->
        <div v-if="latestBooking" class="mt-5 flex gap-4">
            <div class="flex-1 min-w-0 overflow-hidden rounded-2xl shadow-sm"
                style="background:linear-gradient(115deg,#241a5c 0%,#2c2270 45%,#3a2a8f 100%);">
                <div class="flex items-stretch">
                    <div class="w-[200px] flex-none relative overflow-hidden hidden sm:block"
                        style="background:linear-gradient(135deg,#2c2270,#3a2a8f);">
                        <img v-if="coverImage" :src="coverImage" :alt="project?.name"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div v-else class="absolute inset-0 flex items-center justify-center">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.15)"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 10.5 12 3l9 7.5" />
                                <path d="M5 9.5V21h14V9.5" />
                                <path d="M10 21v-6h4v6" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0 p-5 flex flex-col justify-center gap-1">
                        <div class="text-xs font-semibold tracking-widest" style="color:#b3a9f0;">YOUR PROPERTY</div>
                        <h2 class="font-bold text-white mt-1" style="font-size:18px; letter-spacing:-0.02em;">{{
                            project?.name ?? '—' }}</h2>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm font-medium mt-1"
                            style="color:#d2cbf5;">
                            <span v-if="unit?.unit_number">Unit {{ unit.unit_number }}</span>
                            <template v-if="unit?.floor">
                                <span class="w-1 h-1 rounded-full" style="background:#7c70c8;"></span>
                                <span>Floor {{ unit.floor }}</span>
                            </template>
                            <template v-if="unit?.type">
                                <span class="w-1 h-1 rounded-full" style="background:#7c70c8;"></span>
                                <span>{{ unit.bedrooms ? unit.bedrooms + ' Bed ' : '' }}{{ unitTypeLabels[unit.type] ??
                                    unit.type }}</span>
                            </template>
                        </div>
                        <div class="mt-3">
                            <span class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1 rounded-full"
                                :class="statusBadge.cls">{{
                                    statusBadge.label }}</span>
                        </div>
                        <button
                            class="mt-4 self-start flex items-center gap-2 bg-white font-semibold px-4 py-2 rounded-xl hover:bg-[#f0ecff] transition-colors text-sm"
                            style="color:#2c2270;">
                            View Property
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h13M13 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="w-[210px] flex-none flex flex-col rounded-2xl p-5 shadow-sm"
                style="background:linear-gradient(160deg,#312981,#241a5c); border:1px solid rgba(255,255,255,.08);">
                <div class="text-xs font-semibold" style="color:#b3a9f0;">Next Installment</div>
                <div class="flex items-center gap-2.5 mt-3">
                    <div class="w-9 h-9 flex-none rounded-xl flex items-center justify-center"
                        style="background:rgba(255,255,255,.1);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#cdbcff" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4.5" width="18" height="16.5" rx="2.5" />
                            <path d="M3 9h18M8 2.5v4M16 2.5v4" />
                        </svg>
                    </div>
                    <div class="text-base font-bold text-white tracking-tight">{{ nextInstallmentDisplay }}</div>
                </div>
                <div v-if="daysRemaining" class="mt-2 text-sm font-bold" style="color:#ffc24a;">{{ daysRemaining }}
                </div>
                <button
                    class="w-full font-bold py-2.5 rounded-xl text-white text-sm hover:brightness-105 transition-all"
                    style="background:linear-gradient(100deg,#7b63ff,#5132e0); box-shadow:0 8px 18px -6px rgba(123,99,255,.6); margin-top:20px;">
                    Pay Now
                </button>
            </div>
        </div>

        <!-- No bookings -->
        <div v-else class="mt-5 rounded-2xl border border-dashed border-border bg-client-surface-card p-8 text-center">
            <div
                class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-client-accent/10 text-client-accent">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5 9.5V21h14V9.5" />
                    <path d="M10 21v-6h4v6" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-foreground">No property booked yet</p>
            <p class="mt-1 text-xs text-muted-foreground">Your property details will appear here once a booking is
                assigned.</p>
        </div>

        <!-- ── Payment Progress mini cards ───────────────────────── -->
        <div class="mt-5">
            <h3 class="text-sm font-bold text-foreground mb-3">Payment Progress</h3>
            <div v-if="paymentCards.length" class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                <div v-for="card in paymentCards" :key="card.id"
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-4 shadow-sm flex flex-col items-center text-center gap-3">
                    <!-- Mini donut -->
                    <VueApexCharts
                        type="radialBar"
                        height="80"
                        width="80"
                        :options="paymentChartOpts()"
                        :series="[paidPct(card.total_paid, card.price_agreed)]"
                    />
                    <!-- Labels -->
                    <div class="w-full min-w-0">
                        <div class="text-sm font-bold text-foreground truncate">{{ card.project_name }}</div>
                        <div class="text-xs text-muted-foreground mt-0.5">Unit {{ card.unit_number }}</div>
                    </div>
                    <!-- Amounts -->
                    <div class="w-full border-t border-[#f0f0f5] dark:border-white/[0.06] pt-2.5 flex flex-col gap-1">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-muted-foreground font-medium">Paid</span>
                            <span class="font-bold text-green-600 dark:text-green-400">{{ fmtBDT(card.total_paid)
                            }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-muted-foreground font-medium">Total</span>
                            <span class="font-semibold text-foreground">{{ fmtBDT(card.price_agreed) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else
                class="rounded-2xl border border-dashed border-border bg-client-surface-card p-6 text-center text-sm text-muted-foreground">
                No payment data available.
            </div>
        </div>

        <!-- ── Recent Payments + Quick Actions ───────────────────── -->
        <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">

            <!-- Recent Payments -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-foreground">Recent Payments</h3>
                    <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                </div>

                <div v-if="recentPayments.length">
                    <div class="grid text-xs font-bold uppercase tracking-wide text-muted-foreground pb-2 border-b border-[#f0f0f5] dark:border-white/[0.06]"
                        style="grid-template-columns:1fr 1.2fr 1fr auto; gap:0 8px;">
                        <span>Date</span><span>Description</span><span>Amount</span><span
                            class="text-right">Status</span>
                    </div>
                    <div v-for="row in recentPayments" :key="row.date + row.label"
                        class="grid items-center border-b border-[#f4f4f8] dark:border-white/[0.04] last:border-0"
                        style="grid-template-columns:1fr 1.2fr 1fr auto; gap:8px; padding:10px 0;">
                        <span class="text-xs text-muted-foreground font-medium">{{ row.date }}</span>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-foreground truncate">{{ row.label }}</div>
                            <div class="text-xs text-muted-foreground truncate">{{ row.project }}</div>
                        </div>
                        <span class="text-xs font-bold text-foreground">{{ fmtBDT(row.amount) }}</span>
                        <span
                            class="inline-block text-xs font-bold px-2 py-1 rounded-lg justify-self-end bg-[#e6f7ed] text-green-700 dark:bg-green-500/15 dark:text-green-400">Paid</span>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-8 gap-2 text-center">
                    <div
                        class="h-10 w-10 rounded-xl bg-client-accent/10 flex items-center justify-center text-client-accent">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
                            <path d="M2.5 9.5h19" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-foreground">No payments yet</p>
                    <p class="text-xs text-muted-foreground">Payments will appear here once recorded.</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <h3 class="text-base font-bold text-foreground mb-3">Quick Actions</h3>
                <div class="flex flex-col gap-2">
                    <a v-for="action in [
                        { label: 'Make a Payment', well: 'bg-[#efeafc]', ic: 'text-client-accent', icon: `<rect x='2.5' y='5' width='19' height='14' rx='2.5'/><path d='M2.5 9.5h19'/>` },
                        { label: 'Download Statement', well: 'bg-green-100 dark:bg-green-500/15', ic: 'text-green-600 dark:text-green-400', icon: `<path d='M12 3v12M7 10l5 5 5-5'/><path d='M4 20h16'/>` },
                        { label: 'View Payment Plan', well: 'bg-blue-100 dark:bg-blue-500/15', ic: 'text-blue-600 dark:text-blue-400', icon: `<rect x='3' y='4' width='18' height='17' rx='2.5'/><path d='M3 9h18M8 13h4M8 17h8'/>` },
                        { label: 'Apply for Financing', well: 'bg-amber-100 dark:bg-amber-500/15', ic: 'text-amber-600 dark:text-amber-400', icon: `<path d='M3 11 12 4l9 7'/><path d='M5 10v10h14V10'/>` },
                        { label: 'Chat with AI Advisor', well: 'bg-[#efeafc]', ic: 'text-client-accent', icon: `<path d='M4 5h16v11H9l-4 4z'/><path d='M9 10h6M9 13h4'/>` },
                        { label: 'Book a Meeting', well: 'bg-blue-100 dark:bg-blue-500/15', ic: 'text-blue-600 dark:text-blue-400', icon: `<rect x='3' y='4.5' width='18' height='16.5' rx='2.5'/><path d='M3 9h18M8 2.5v4M16 2.5v4'/><path d='m9 14 2 2 4-4'/>` },
                    ]" :key="action.label" href="#"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-[#f0f0f5] dark:border-white/[0.06] hover:border-client-accent/30 hover:bg-[#faf9fd] dark:hover:bg-white/[0.04] transition-colors">
                        <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg"
                            :class="action.well">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="action.ic"
                                v-html="action.icon" />
                        </span>
                        <span class="flex-1 text-sm font-semibold text-foreground">{{ action.label }}</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"
                            class="text-slate-300 dark:text-slate-600">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Construction Progress mini cards ──────────────────── -->
        <div class="mt-5">
            <h3 class="text-sm font-bold text-foreground mb-3">Construction Progress</h3>
            <div v-if="constructionCards.length" class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                <div v-for="card in constructionCards" :key="card.project_name"
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-4 shadow-sm flex flex-col items-center text-center gap-3">
                    <!-- Mini progress ring -->
                    <VueApexCharts
                        type="radialBar"
                        height="80"
                        width="80"
                        :options="constructionChartOpts()"
                        :series="[Math.round(card.time_progress)]"
                    />
                    <!-- Labels -->
                    <div class="w-full min-w-0">
                        <div class="text-sm font-bold text-foreground truncate">{{ card.project_name }}</div>
                        <div class="text-xs mt-0.5 font-semibold"
                            :class="constructionStatusMap[card.status]?.cls ?? 'text-muted-foreground'">
                            {{ card.status_label }}
                        </div>
                    </div>
                    <!-- Handover date -->
                    <div class="w-full border-t border-[#f0f0f5] dark:border-white/[0.06] pt-2.5">
                        <div class="text-xs text-muted-foreground font-medium">Est. Handover</div>
                        <div class="text-xs font-bold text-foreground mt-0.5">{{ card.handover_date }}</div>
                    </div>
                </div>
            </div>
            <div v-else
                class="rounded-2xl border border-dashed border-border bg-client-surface-card p-6 text-center text-sm text-muted-foreground">
                No construction data available.
            </div>
        </div>

        <!-- ── Latest Updates + Recommended ──────────────────────── -->
        <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">

            <!-- Latest Updates (dummy) -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-foreground">Latest Updates</h3>
                    <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                </div>
                <div class="flex flex-col gap-1">
                    <div v-for="item in latestUpdates" :key="item.title"
                        class="flex items-center gap-3 p-2 rounded-xl hover:bg-[#faf9fd] dark:hover:bg-white/[0.04] transition-colors cursor-pointer">
                        <div
                            class="w-10 h-10 flex-none rounded-xl bg-[#f0eef9] dark:bg-client-accent/10 flex items-center justify-center text-client-accent">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 10.5 12 3l9 7.5" />
                                <path d="M5 9.5V21h14V9.5" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-foreground truncate">{{ item.title }}</div>
                            <div class="text-xs text-muted-foreground mt-0.5 font-medium">{{ item.sub }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended For You (dummy) -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-foreground">Recommended For You</h3>
                    <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div v-for="prop in recommendedProjects" :key="prop.id"
                        class="rounded-xl overflow-hidden border border-[#f0f0f5] dark:border-white/[0.06] hover:border-client-accent/30 transition-colors cursor-pointer">
                        <div class="relative h-[68px] bg-[#f0eef9] dark:bg-client-accent/10 flex items-center justify-center">
                            <img v-if="prop.cover_image" :src="prop.cover_image" :alt="prop.name"
                                class="absolute inset-0 w-full h-full object-cover" />
                            <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="opacity-30 text-client-accent"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                        </div>
                        <div class="p-2.5">
                            <div class="text-sm font-bold text-foreground truncate">{{ prop.name }}</div>
                            <div class="flex items-center gap-1 text-xs text-muted-foreground mt-0.5 font-medium">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/></svg>
                                <span class="truncate">{{ prop.location }}</span>
                            </div>
                            <div class="text-sm font-extrabold text-client-accent mt-1.5">{{ fmtBDT(prop.estimated_value) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer
            class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] px-5 py-3.5 shadow-sm">
            <div class="text-sm font-bold text-foreground">
                HomeVerse<sup class="text-xs font-normal text-muted-foreground">™</sup>
                <span class="text-muted-foreground font-normal ml-2">·</span>
                <span class="text-sm font-medium text-muted-foreground ml-2">Real Estate Intelligence Platform</span>
            </div>
            <div class="text-xs font-medium text-muted-foreground">
                Powered by <span class="font-bold text-foreground">Future Studios Bangladesh</span>
                · <a href="#" class="font-bold text-client-accent">www.fsb.site</a>
                · © 2026
            </div>
        </footer>

    </ClientLayout>
</template>
