<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    bookingsList:      { type: Array,  default: () => [] },
    totalValue:        { type: Number, default: 0 },
    totalPaid:         { type: Number, default: 0 },
    outstanding:       { type: Number, default: 0 },
    paidPct:           { type: Number, default: 0 },
    totalInstallments: { type: Number, default: 0 },
    paidInstallments:  { type: Number, default: 0 },
    recentPayments:    { type: Array,  default: () => [] },
    upcomingPayment:   { type: Object, default: null },
});

// ── Dummy fallback data ────────────────────────────────────────────
const DUMMY_KPI = {
    totalValue: 12500000, totalPaid: 4500000, outstanding: 8000000,
    paidPct: 36, totalInstallments: 40, paidInstallments: 12,
};
const DUMMY_PAYMENTS = [
    { id:1, booking_id:1, date:'15 Jul 2026', installment_no:12, description:'Monthly Installment', amount:75000, method:'bKash',        project_name:'Lake View Residence', unit_number:'A-1205', receipt_path:null },
    { id:2, booking_id:1, date:'15 Jun 2026', installment_no:11, description:'Monthly Installment', amount:75000, method:'Bank Transfer', project_name:'Lake View Residence', unit_number:'A-1205', receipt_path:null },
    { id:3, booking_id:1, date:'15 May 2026', installment_no:10, description:'Monthly Installment', amount:75000, method:'Nagad',         project_name:'Lake View Residence', unit_number:'A-1205', receipt_path:null },
    { id:4, booking_id:1, date:'15 Apr 2026', installment_no:9,  description:'Monthly Installment', amount:75000, method:'Credit Card',   project_name:'Lake View Residence', unit_number:'A-1205', receipt_path:null },
    { id:5, booking_id:1, date:'15 Mar 2026', installment_no:8,  description:'Monthly Installment', amount:75000, method:'Bank Transfer', project_name:'Lake View Residence', unit_number:'A-1205', receipt_path:null },
];
const DUMMY_UPCOMING = {
    installment_number: 13, due_date: '15 Aug 2026',
    amount: 75000, days_remaining: 15, is_overdue: false,
    project_name: 'Lake View Residence', unit_number: 'A-1205',
};
const DUMMY_PLAN = {
    plan_type: 'Construction Linked', start_date: '15 Jan 2026',
    handover_date: 'Dec 2026', duration_months: 40,
    total_installments: 40, paid_installments: 12,
};

// ── Determine if we have real data ────────────────────────────────
const hasData = computed(() => props.bookingsList.length > 0);

const kpi = computed(() => hasData.value
    ? { totalValue: props.totalValue, totalPaid: props.totalPaid, outstanding: props.outstanding,
        paidPct: props.paidPct, totalInstallments: props.totalInstallments, paidInstallments: props.paidInstallments }
    : DUMMY_KPI
);

// ── Property selector ─────────────────────────────────────────────
const selectedBookingId = ref(null);

const selectedBooking = computed(() =>
    selectedBookingId.value
        ? props.bookingsList.find(b => b.id === selectedBookingId.value) ?? null
        : (props.bookingsList[0] ?? null)
);

// KPI for current view (per-booking or aggregate)
const viewKpi = computed(() => {
    if (!hasData.value) return DUMMY_KPI;
    if (selectedBookingId.value && selectedBooking.value) {
        const b = selectedBooking.value;
        const paid = b.total_paid;
        const total = b.price_agreed;
        const pct = total > 0 ? Math.round((paid / total) * 100) : 0;
        return { totalValue: total, totalPaid: paid, outstanding: total - paid,
            paidPct: pct, totalInstallments: b.total_installments, paidInstallments: b.paid_installments };
    }
    return kpi.value;
});

// Plan overview (selected booking or first)
const planOverview = computed(() => {
    if (!hasData.value) return DUMMY_PLAN;
    const b = selectedBooking.value;
    if (!b) return DUMMY_PLAN;
    return {
        plan_type:          b.plan_type ?? '—',
        start_date:         b.start_date,
        handover_date:      b.handover_date,
        duration_months:    b.duration_months ?? '—',
        total_installments: b.total_installments,
        paid_installments:  b.paid_installments,
    };
});

// Payment history (filtered by selected booking if set)
const paymentHistory = computed(() => {
    if (!hasData.value) return DUMMY_PAYMENTS;
    if (selectedBookingId.value) {
        return props.recentPayments.filter(p => p.booking_id === selectedBookingId.value);
    }
    return props.recentPayments;
});

// Upcoming payment
const upcoming = computed(() => {
    if (!hasData.value) return DUMMY_UPCOMING;
    return props.upcomingPayment ?? null;
});

// ── Timeline stages ────────────────────────────────────────────────
const timelineStages = computed(() => {
    const pct = viewKpi.value.paidPct;
    return [
        { label: 'Booking',             sub: planOverview.value.start_date,  threshold: 0   },
        { label: 'Under Construction',  sub: '25%',                          threshold: 25  },
        { label: 'Interior Work',       sub: '50%',                          threshold: 50  },
        { label: 'Handover',            sub: '100%',                         threshold: 100 },
    ].map((s, i, arr) => {
        const done   = pct >= s.threshold && (i === 0 || pct > s.threshold || pct === 100);
        const active = !done && i > 0 && pct >= arr[i - 1].threshold;
        return { ...s, done: pct >= s.threshold, active };
    });
});

// Progress line width (0–100%) maps payment pct across 3 intervals
const timelineLineWidth = computed(() => {
    const pct = Math.min(100, viewKpi.value.paidPct);
    // 4 stages: 0% at pos 0, 25% at pos 33%, 50% at pos 67%, 100% at pos 100%
    if (pct <= 25)  return (pct / 25) * 33.33;
    if (pct <= 50)  return 33.33 + ((pct - 25) / 25) * 33.33;
    if (pct <= 100) return 66.66 + ((pct - 50) / 50) * 33.34;
    return 100;
});

// ── Helpers ───────────────────────────────────────────────────────
function fmtBDT(n) {
    if (!n) return 'BDT 0';
    return 'BDT ' + Number(n).toLocaleString('en-US');
}
function absRem(n) {
    if (n === null || n === undefined) return '—';
    return Math.abs(n) + (Math.abs(n) === 1 ? ' Day' : ' Days');
}
</script>

<template>
    <Head title="Payments & Installments" />
    <ClientLayout title="Payments & Installments">

        <!-- ── Header ─────────────────────────────────────────────── -->
        <div class="flex items-start justify-between gap-5 flex-wrap">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.025em;">Payments &amp; Installments</h1>
                <p class="mt-1 text-sm font-medium text-muted-foreground">Track your payments, installments and outstanding balance.</p>
            </div>
        </div>

        <!-- ── KPI 5-col row ──────────────────────────────────────── -->
        <div class="mt-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
            <!-- Total Property Value -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-[#efeafc]">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#5b3fe8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Total Property Value</span>
                </div>
                <div class="mt-3.5 text-xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.02em;">{{ fmtBDT(viewKpi.totalValue) }}</div>
            </div>
            <!-- Paid Amount -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-green-100 dark:bg-green-500/15">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7"/><path d="M3 12h18"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Paid Amount</span>
                </div>
                <div class="mt-3.5 text-xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.02em;">{{ fmtBDT(viewKpi.totalPaid) }}</div>
                <div class="mt-1 text-xs font-bold text-green-600 dark:text-green-400">
                    {{ viewKpi.paidPct }}% <span class="font-medium text-muted-foreground">of Total</span>
                </div>
            </div>
            <!-- Outstanding -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#f08a1d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10.5" width="16" height="10.5" rx="2.5"/><path d="M7.5 10.5V8a4.5 4.5 0 0 1 9 0v2.5"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Outstanding Balance</span>
                </div>
                <div class="mt-3.5 text-xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.02em;">{{ fmtBDT(viewKpi.outstanding) }}</div>
                <div class="mt-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                    {{ 100 - viewKpi.paidPct }}% <span class="font-medium text-muted-foreground">Remaining</span>
                </div>
            </div>
            <!-- Total Installments -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/15">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#2f6bdb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Total Installments</span>
                </div>
                <div class="mt-3.5 text-xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.02em;">{{ viewKpi.totalInstallments }}</div>
                <div class="mt-1 text-xs font-medium text-muted-foreground">Installments</div>
            </div>
            <!-- Paid Installments -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-[#efeafc]">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#5b3fe8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/><path d="m9 14 2 2 4-4"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Paid Installments</span>
                </div>
                <div class="mt-3.5 text-xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.02em;">{{ viewKpi.paidInstallments }}</div>
                <div class="mt-1 text-xs font-medium text-muted-foreground">Installments</div>
            </div>
        </div>

        <!-- ── Property selector (multiple bookings only) ─────────── -->
        <div v-if="bookingsList.length > 1" class="mt-4 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-muted-foreground mr-1">Filter by property:</span>
            <button
                class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-colors"
                :class="selectedBookingId === null
                    ? 'border-client-accent bg-client-accent/10 text-client-accent'
                    : 'border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card text-muted-foreground hover:border-client-accent/40'"
                @click="selectedBookingId = null"
            >All Properties</button>
            <button
                v-for="b in bookingsList" :key="b.id"
                class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-colors"
                :class="selectedBookingId === b.id
                    ? 'border-client-accent bg-client-accent/10 text-client-accent'
                    : 'border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card text-muted-foreground hover:border-client-accent/40'"
                @click="selectedBookingId = b.id"
            >{{ b.project_name }} – {{ b.unit_number }}</button>
        </div>

        <!-- ── Body: feed + right rail ────────────────────────────── -->
        <div class="mt-5 flex gap-5 items-start">

            <!-- ===== FEED ===================================== -->
            <div class="flex-1 min-w-0 flex flex-col gap-5">

                <!-- Payment Progress + Installment Summary -->
                <div class="grid grid-cols-1 xl:grid-cols-[0.95fr_1.35fr] gap-5">

                    <!-- Donut -->
                    <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                        <h3 class="text-base font-bold text-foreground">Payment Progress</h3>
                        <div class="flex items-center gap-5 mt-5">
                            <!-- Conic donut -->
                            <div class="relative rounded-full flex-none" style="width:148px; height:148px;"
                                :style="`background: conic-gradient(#16a34a 0% ${viewKpi.paidPct}%, #f59e0b ${viewKpi.paidPct}% 100%);`"
                            >
                                <div class="absolute rounded-full bg-client-surface-card flex flex-col items-center justify-center" style="inset:18px;">
                                    <div class="text-2xl font-extrabold text-foreground" style="letter-spacing:-0.02em;">{{ viewKpi.paidPct }}%</div>
                                    <div class="text-xs font-semibold text-muted-foreground">Paid</div>
                                </div>
                            </div>
                            <!-- Legend -->
                            <div class="flex-1 flex flex-col gap-4">
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-none"></span>
                                        Paid Amount
                                    </div>
                                    <div class="text-base font-extrabold text-foreground mt-1.5">{{ fmtBDT(viewKpi.totalPaid) }}</div>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 flex-none"></span>
                                        Outstanding Balance
                                    </div>
                                    <div class="text-base font-extrabold text-foreground mt-1.5">{{ fmtBDT(viewKpi.outstanding) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Installment Timeline -->
                    <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm flex flex-col">
                        <h3 class="text-base font-bold text-foreground">Installment Summary</h3>

                        <!-- Timeline -->
                        <div class="relative flex justify-between mt-8 mx-2">
                            <!-- Track -->
                            <div class="absolute top-[13px] left-6 right-6 h-[3px] rounded-full bg-[#eceaf4] dark:bg-white/[0.08]"></div>
                            <!-- Fill -->
                            <div class="absolute top-[13px] left-6 h-[3px] rounded-full bg-green-500 transition-all duration-700"
                                :style="`width: calc((100% - 48px) * ${timelineLineWidth / 100})`"
                            ></div>

                            <div v-for="(stage, i) in timelineStages" :key="stage.label"
                                class="relative flex flex-col items-center gap-2.5" style="width:25%;">
                                <!-- Dot -->
                                <div v-if="stage.done"
                                    class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center z-10"
                                    style="box-shadow: 0 0 0 4px #e6f7ed;"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                                </div>
                                <div v-else-if="stage.active"
                                    class="w-7 h-7 rounded-full flex items-center justify-center z-10"
                                    style="background:#5b3fe8; box-shadow: 0 0 0 4px #ece7fb;"
                                >
                                    <span class="w-2.5 h-2.5 rounded-full bg-white"></span>
                                </div>
                                <div v-else
                                    class="w-7 h-7 rounded-full bg-client-surface-card border-[3px] border-[#e2e0ec] dark:border-white/[0.12] z-10"
                                ></div>

                                <!-- Labels -->
                                <div class="text-center" style="margin-top:2px;">
                                    <div class="text-xs font-bold" :class="stage.done ? 'text-foreground' : 'text-muted-foreground'">{{ stage.label }}</div>
                                    <div class="text-[10.5px] font-medium text-muted-foreground mt-0.5">{{ stage.sub }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Info banner -->
                        <div class="mt-auto pt-5">
                            <div class="flex items-center gap-3 p-3.5 bg-[#f7f6fd] dark:bg-client-accent/10 rounded-xl">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-client-accent flex-none"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                                <span class="text-xs text-muted-foreground font-medium leading-relaxed">Please check your payment schedule and make sure your next installment is paid on time.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-base font-bold text-foreground">Payment History</h3>
                        <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                    </div>

                    <div v-if="paymentHistory.length">
                        <!-- Header -->
                        <div class="grid text-xs font-bold uppercase tracking-wide text-muted-foreground py-3 border-b border-[#f0f0f5] dark:border-white/[0.06]"
                            style="grid-template-columns:1fr 1.1fr 1.5fr 1fr 1.1fr 0.75fr 0.5fr; gap:10px; padding-left:6px; padding-right:6px;"
                        >
                            <span>Date</span>
                            <span>Installment</span>
                            <span>Description</span>
                            <span>Amount</span>
                            <span>Method</span>
                            <span>Status</span>
                            <span class="text-right">Receipt</span>
                        </div>
                        <!-- Rows -->
                        <div v-for="(row, i) in paymentHistory" :key="row.id"
                            class="grid items-center transition-colors hover:bg-[#faf9fd] dark:hover:bg-white/[0.02]"
                            :class="i < paymentHistory.length - 1 ? 'border-b border-[#f4f4f8] dark:border-white/[0.04]' : ''"
                            style="grid-template-columns:1fr 1.1fr 1.5fr 1fr 1.1fr 0.75fr 0.5fr; gap:10px; padding:13px 6px; font-size:13px;"
                        >
                            <span class="text-muted-foreground font-medium">{{ row.date }}</span>
                            <span class="font-bold text-foreground">
                                {{ row.installment_no ? 'Installment ' + row.installment_no : 'Payment' }}
                            </span>
                            <span class="text-muted-foreground truncate">
                                {{ row.description || (bookingsList.length > 1 ? row.project_name + ' – ' + row.unit_number : 'Monthly Installment') }}
                            </span>
                            <span class="font-bold text-foreground">{{ fmtBDT(row.amount) }}</span>
                            <span class="text-muted-foreground font-medium">{{ row.method }}</span>
                            <span>
                                <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-lg bg-[#e6f7ed] text-green-700 dark:bg-green-500/15 dark:text-green-400">Paid</span>
                            </span>
                            <span class="text-right">
                                <button class="inline-flex items-center justify-center hover:text-client-accent text-muted-foreground transition-colors" title="Download Receipt">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 20h16"/></svg>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div v-else class="py-8 text-center">
                        <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-client-accent/10 text-client-accent">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 9.5h19"/></svg>
                        </div>
                        <p class="text-sm font-bold text-foreground">No payments recorded yet</p>
                        <p class="mt-1 text-xs text-muted-foreground">Payments will appear here once recorded.</p>
                    </div>
                </div>

                <!-- Download Statements + Need Help -->
                <div class="grid grid-cols-1 xl:grid-cols-[1.45fr_1fr] gap-5">

                    <!-- Download Statements -->
                    <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                        <h3 class="text-base font-bold text-foreground mb-4">Download Statements</h3>
                        <div class="grid grid-cols-3 gap-3">
                            <a href="#" class="flex items-center gap-3 p-3.5 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl hover:bg-[#faf9fd] hover:border-[#e6e1fb] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="w-9 h-9 flex-none rounded-lg bg-[#fdebe8] flex items-center justify-center text-[9px] font-extrabold text-[#e0584b]">PDF</span>
                                <span>
                                    <span class="block text-xs font-bold text-foreground">Payment Statement</span>
                                    <span class="block text-xs text-muted-foreground mt-0.5">PDF Format</span>
                                </span>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3.5 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl hover:bg-[#faf9fd] hover:border-[#e6e1fb] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="w-9 h-9 flex-none rounded-lg bg-[#e6f4ec] flex items-center justify-center text-[9px] font-extrabold text-[#1d8a4e]">XLS</span>
                                <span>
                                    <span class="block text-xs font-bold text-foreground">Payment Statement</span>
                                    <span class="block text-xs text-muted-foreground mt-0.5">Excel Format</span>
                                </span>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3.5 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl hover:bg-[#faf9fd] hover:border-[#e6e1fb] dark:hover:bg-white/[0.04] transition-colors">
                                <span class="w-9 h-9 flex-none rounded-lg bg-[#efeafc] flex items-center justify-center text-client-accent">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="m9 14 2 2 4-4"/></svg>
                                </span>
                                <span>
                                    <span class="block text-xs font-bold text-foreground">Tax Certificate</span>
                                    <span class="block text-xs text-muted-foreground mt-0.5">Financial Year 2026</span>
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- Need Help -->
                    <div class="relative overflow-hidden rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                        <div style="max-width:230px;">
                            <h3 class="text-base font-bold text-foreground">Need Help?</h3>
                            <p class="text-xs text-muted-foreground font-medium leading-relaxed mt-2 mb-4">Our support team is here to help you with any payment related queries.</p>
                            <button class="inline-flex items-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl border transition-colors text-client-accent"
                                style="border-color:#e6e1fb; background:#f6f3ff;"
                                @mouseenter="$event.currentTarget.style.background='#efeafc'"
                                @mouseleave="$event.currentTarget.style.background='#f6f3ff'"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13a8 8 0 0 1 16 0M4 13v3a2 2 0 0 0 2 2h1v-5H6a2 2 0 0 0-2 2zm16 0v3a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2z"/></svg>
                                Contact Support
                            </button>
                        </div>
                        <!-- Decorative circle -->
                        <div class="absolute right-4 bottom-4 w-[68px] h-[68px] rounded-full flex items-center justify-center" style="background:linear-gradient(160deg,#efeafc,#e0d7fa);">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#7b63ff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13a8 8 0 0 1 16 0M4 13v3a2 2 0 0 0 2 2h1v-5H6a2 2 0 0 0-2 2zm16 0v3a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2z"/></svg>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ===== END FEED ================================= -->

            <!-- ===== RIGHT RAIL =============================== -->
            <aside class="hidden xl:flex flex-col gap-4" style="width:310px; flex:none;">

                <!-- Upcoming Payment -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-4">Upcoming Payment</h3>
                    <div v-if="upcoming" class="border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-extrabold text-foreground">Installment {{ upcoming.installment_number }}</span>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-lg"
                                :class="upcoming.is_overdue
                                    ? 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400'
                                    : 'bg-[#eef0fe] text-[#3f5bd6]'"
                            >{{ upcoming.is_overdue ? 'Overdue' : 'Upcoming' }}</span>
                        </div>
                        <div class="mt-4">
                            <div class="text-xs text-muted-foreground font-semibold">Due Date</div>
                            <div class="text-xl font-extrabold text-foreground mt-1" style="letter-spacing:-0.02em;">{{ upcoming.due_date }}</div>
                        </div>
                        <div class="mt-3.5 pt-3.5 border-t border-dashed border-[#e6e6ee] dark:border-white/[0.08]">
                            <div class="text-xs text-muted-foreground font-semibold">Amount</div>
                            <div class="text-xl font-extrabold text-foreground mt-1" style="letter-spacing:-0.02em;">{{ fmtBDT(upcoming.amount) }}</div>
                        </div>
                        <div class="mt-3.5 pt-3.5 border-t border-dashed border-[#e6e6ee] dark:border-white/[0.08]">
                            <div class="text-xs text-muted-foreground font-semibold">
                                {{ upcoming.is_overdue ? 'Overdue By' : 'Days Remaining' }}
                            </div>
                            <div class="text-xl font-extrabold mt-1" style="letter-spacing:-0.02em;"
                                :class="upcoming.is_overdue ? 'text-red-500' : 'text-green-600 dark:text-green-400'"
                            >{{ absRem(upcoming.days_remaining) }}</div>
                        </div>
                        <div v-if="upcoming.project_name !== '—'" class="mt-3.5 pt-3.5 border-t border-[#f0f0f5] dark:border-white/[0.06]">
                            <div class="text-xs text-muted-foreground font-medium truncate">{{ upcoming.project_name }} – Unit {{ upcoming.unit_number }}</div>
                        </div>
                    </div>
                    <div v-else class="border border-dashed border-border rounded-xl p-6 text-center text-xs text-muted-foreground font-medium">
                        No upcoming installments found.
                    </div>
                    <button class="mt-4 w-full text-sm font-bold py-3 rounded-xl text-white"
                        style="background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 10px 22px -8px rgba(81,50,224,.55);">
                        Make a Payment
                    </button>
                    <button class="mt-2.5 w-full flex items-center justify-center gap-2 text-sm font-bold py-2.5 rounded-xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card hover:bg-[#faf9fd] dark:hover:bg-white/[0.04] transition-colors text-foreground">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-client-accent"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>
                        View Payment Plan
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#c2c2cf" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="ml-auto"><path d="M9 6l6 6-6 6"/></svg>
                    </button>
                </div>

                <!-- Payment Plan Overview -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-4">Payment Plan Overview</h3>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-4">
                        <div class="flex gap-2.5">
                            <span class="w-8 h-8 flex-none rounded-lg bg-[#efeafc] flex items-center justify-center text-client-accent">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2.5"/><path d="M3 9h18M8 13h4"/></svg>
                            </span>
                            <div>
                                <div class="text-xs text-muted-foreground font-semibold">Plan Type</div>
                                <div class="text-xs font-bold text-foreground mt-0.5">{{ planOverview.plan_type || 'Construction Linked' }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2.5">
                            <span class="w-8 h-8 flex-none rounded-lg bg-blue-100 dark:bg-blue-500/15 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>
                            </span>
                            <div>
                                <div class="text-xs text-muted-foreground font-semibold">Start Date</div>
                                <div class="text-xs font-bold text-foreground mt-0.5">{{ planOverview.start_date }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2.5">
                            <span class="w-8 h-8 flex-none rounded-lg bg-green-100 dark:bg-green-500/15 flex items-center justify-center text-green-600 dark:text-green-400">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/></svg>
                            </span>
                            <div>
                                <div class="text-xs text-muted-foreground font-semibold">Handover Date</div>
                                <div class="text-xs font-bold text-foreground mt-0.5">{{ planOverview.handover_date }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2.5">
                            <span class="w-8 h-8 flex-none rounded-lg bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            </span>
                            <div>
                                <div class="text-xs text-muted-foreground font-semibold">Total Duration</div>
                                <div class="text-xs font-bold text-foreground mt-0.5">
                                    {{ planOverview.duration_months ? planOverview.duration_months + ' Months' : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="mt-4 w-full flex items-center justify-center gap-2 text-sm font-bold py-2.5 rounded-xl border transition-colors text-client-accent"
                        style="border-color:#e6e1fb; background:#f6f3ff;"
                        @mouseenter="$event.currentTarget.style.background='#efeafc'"
                        @mouseleave="$event.currentTarget.style.background='#f6f3ff'"
                    >
                        Download Payment Plan (PDF)
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 20h16"/></svg>
                    </button>
                </div>

                <!-- Payment Methods -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-4">Payment Methods We Accept</h3>
                    <div class="flex flex-wrap gap-2.5">
                        <span class="flex items-center justify-center h-10 px-4 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-sm font-extrabold" style="color:#e2136e;">bKash</span>
                        <span class="flex items-center justify-center h-10 px-4 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-sm font-extrabold" style="color:#ee7421;">Nagad</span>
                        <span class="flex items-center justify-center h-10 px-4 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-sm font-extrabold italic" style="color:#1a1f71;">VISA</span>
                        <!-- Mastercard dots -->
                        <span class="flex items-center justify-center h-10 px-3.5 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl gap-[-4px]">
                            <span class="w-4 h-4 rounded-full" style="background:#eb001b;"></span>
                            <span class="w-4 h-4 rounded-full -ml-2" style="background:#f79e1b;"></span>
                        </span>
                        <span class="flex items-center gap-2 h-10 px-3.5 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-bold text-muted-foreground">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V10M19 21V10M3 10l9-6 9 6M9 21v-6h6v6"/></svg>
                            Bank Transfer
                        </span>
                    </div>
                </div>
            </aside>
            <!-- ===== END RIGHT RAIL =========================== -->
        </div>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] px-5 py-3.5 shadow-sm">
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
