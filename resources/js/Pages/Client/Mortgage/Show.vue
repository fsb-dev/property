<script setup>
import { reactive, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    property: { type: Object, default: null },
    isDemo: { type: Boolean, default: false },
    defaultPrice: { type: Number, default: null },
});

// ── Reactive calculator state ─────────────────────────────────────
const calc = reactive({
    price: props.property?.price_agreed ?? props.defaultPrice ?? 15000000,
    downPct: 20,
    ratePct: 8.5,
    years: 10,
});

const TENURE = [5, 10, 15, 20, 25];

// ── Derived computeds ─────────────────────────────────────────────
const calcDown = computed(() => calc.price * calc.downPct / 100);
const calcLoan = computed(() => calc.price - calcDown.value);
const calcLoanPct = computed(() => 100 - calc.downPct);
const calcN = computed(() => calc.years * 12);

const calcMonthly = computed(() => {
    const loan = calcLoan.value;
    const r = calc.ratePct / 100 / 12;
    const n = calcN.value;
    if (r === 0 || n === 0) return n > 0 ? loan / n : 0;
    const pow = Math.pow(1 + r, n);
    return loan * r * pow / (pow - 1);
});

const calcTotalPay = computed(() => calcMonthly.value * calcN.value);
const calcTotalInterest = computed(() => Math.max(0, calcTotalPay.value - calcLoan.value));
const calcPrincipalPct = computed(() =>
    calcTotalPay.value > 0 ? Math.round(calcLoan.value / calcTotalPay.value * 100) : 0
);
const calcInterestPct = computed(() => 100 - calcPrincipalPct.value);

// Affordability
const ASSUMED_INCOME = 500000;
const affordability = computed(() => {
    const dti = calcMonthly.value / ASSUMED_INCOME;
    if (dti >= 0.45) return { label: 'High Risk', color: '#ff9a8a', bg: 'rgba(239,68,68,.22)' };
    if (dti >= 0.35) return { label: 'Moderate', color: '#ffc24a', bg: 'rgba(245,158,11,.2)' };
    return { label: 'Affordable', color: '#5ee79a', bg: 'rgba(34,197,94,.18)' };
});

// AI suggestion
const aiSuggestion = computed(() => {
    if (calc.downPct < 25) return 'Increasing your down payment to 25% would meaningfully lower your monthly payment.';
    if (calc.years > 15) return 'Reducing the tenure to 15 years cuts total interest, with a manageable payment increase.';
    if (calc.ratePct > 9) return `Some partner banks offer rates below your selected ${calc.ratePct.toFixed(2)}% — worth comparing.`;
    return 'Shortening your loan tenure could save you significant interest over the life of the loan.';
});

// Dates
const today = new Date();
const todayStr = today.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
const completionStr = computed(() => {
    const end = new Date(today);
    end.setFullYear(end.getFullYear() + calc.years);
    return end.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
});

// ── ApexCharts configs ────────────────────────────────────────────
const projectionData = computed(() => {
    const loan = calcLoan.value;
    const r = calc.ratePct / 100 / 12;
    const years = calc.years;
    const monthly = calcMonthly.value;
    const n = years * 12;
    const pow = r > 0 ? Math.pow(1 + r, n) : 1;
    const balances = [], cumInts = [], cumPays = [];
    for (let i = 0; i <= years; i++) {
        let b = r === 0
            ? loan * (1 - i / years)
            : i >= years ? 0 : loan * ((pow - Math.pow(1 + r, 12 * i)) / (pow - 1));
        const cp = monthly * 12 * i;
        const ci = Math.max(0, cp - (loan - b));
        balances.push(Math.round(b));
        cumInts.push(Math.round(ci));
        cumPays.push(Math.round(cp));
    }
    return { balances, cumInts, cumPays };
});

const projectionSeries = computed(() => [
    { name: 'Principal Balance', type: 'area', data: projectionData.value.balances },
    { name: 'Total Interest',    type: 'area', data: projectionData.value.cumInts  },
    { name: 'Total Payment',     type: 'line', data: projectionData.value.cumPays  },
]);

const projectionOptions = computed(() => ({
    chart: {
        type: 'line',
        toolbar: { show: false },
        zoom: { enabled: false },
        background: 'transparent',
        animations: { enabled: true, speed: 300, dynamicAnimation: { enabled: true, speed: 150 } },
    },
    stroke: { curve: 'smooth', width: [2.5, 2.5, 2], dashArray: [0, 0, 6] },
    fill: {
        type: ['gradient', 'gradient', 'solid'],
        gradient: { type: 'vertical', opacityFrom: 0.12, opacityTo: 0.01 },
        opacity: [1, 1, 0],
    },
    colors: ['#5b3fe8', '#16a34a', '#c2c2cf'],
    dataLabels: { enabled: false },
    markers: { size: 0 },
    xaxis: {
        categories: Array.from({ length: calc.years + 1 }, (_, i) => i === 0 ? 'Start' : 'Yr ' + i),
        tickAmount: Math.min(calc.years, 10),
        labels: {
            style: { colors: '#b0b0c0', fontSize: '11px', fontFamily: "'Plus Jakarta Sans', system-ui, sans-serif" },
        },
        axisBorder: { show: false },
        axisTicks:  { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val) => val >= 1000000 ? (val / 1000000).toFixed(0) + 'M' : val >= 1000 ? (val / 1000).toFixed(0) + 'K' : '0',
            style: { colors: '#b0b0c0', fontSize: '11px', fontFamily: "'Plus Jakarta Sans', system-ui, sans-serif" },
        },
    },
    grid: {
        borderColor: '#f1f1f6',
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: { top: 0, right: 4, bottom: 0, left: 4 },
    },
    legend: { show: false },
    tooltip: { theme: 'light', y: { formatter: (val) => fmtBDT(val) } },
}));

const amortizationDonutSeries  = computed(() => [calcLoan.value, calcTotalInterest.value]);
const amortizationDonutOptions = {
    chart: { type: 'donut', toolbar: { show: false }, background: 'transparent', sparkline: { enabled: true } },
    plotOptions: {
        pie: { donut: { size: '68%', labels: { show: false } } },
    },
    colors: ['#5b3fe8', '#16a34a'],
    labels: ['Principal', 'Total Interest'],
    legend: { show: false },
    dataLabels: { enabled: false },
    stroke: { width: 2, colors: ['transparent'] },
    tooltip: { y: { formatter: (val) => fmtBDT(val) } },
    states: {
        hover:  { filter: { type: 'darken', value: 0.08 } },
        active: { filter: { type: 'none' } },
    },
};

// ── Helpers ───────────────────────────────────────────────────────
function fmt(n) { return Math.round(n).toLocaleString('en-US'); }
function fmtBDT(n) { return 'BDT ' + fmt(n); }
function fmtM(n) { return (n / 1000000).toFixed(2) + 'M'; }

function onPriceRange(e) { calc.price = +e.target.value; }
function onDownRange(e) { calc.downPct = +e.target.value; }
function onLoanRange(e) { calc.downPct = 100 - +e.target.value; }
function onRateRange(e) { calc.ratePct = +e.target.value; }
function resetCalc() {
    calc.price = props.property?.price_agreed ?? props.defaultPrice ?? 15000000;
    calc.downPct = 20; calc.ratePct = 8.5; calc.years = 10;
}

const statusCls = {
    green: 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400',
    amber: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
    purple: 'bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
    red: 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400',
    slate: 'bg-slate-100 text-slate-600 dark:bg-white/[0.08] dark:text-muted-foreground',
};
</script>

<template>

    <Head :title="property ? `Mortgage – ${property.project_name}` : 'Mortgage Calculator'" />
    <ClientLayout :title="property ? 'Mortgage Calculator' : 'Mortgage &amp; Financing'">

        <!-- ── Breadcrumb ─────────────────────────────────────────── -->
        <div class="flex items-center gap-2 text-sm flex-wrap mb-3">
            <Link :href="route('client.mortgage')"
                class="inline-flex items-center gap-1.5 font-bold text-muted-foreground hover:text-foreground transition-colors">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Mortgage &amp; Financing
            </Link>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#c2c2cf" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 6l6 6-6 6" />
            </svg>
            <span class="font-bold text-foreground truncate">
                {{ property ? property.project_name + ' – Unit ' + property.unit_number : 'Sample Calculator' }}
            </span>
        </div>

        <!-- ── Property context bar ───────────────────────────────── -->
        <div v-if="property"
            class="flex items-center gap-4 p-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] mb-3 shadow-sm">
            <!-- Thumbnail -->
            <div class="relative w-[68px] h-[52px] flex-none rounded-xl overflow-hidden"
                style="background:linear-gradient(135deg,#e8e3fb,#d8d0f5);">
                <img v-if="property.cover_image" :src="property.cover_image" :alt="property.project_name"
                    class="w-full h-full object-cover" />
                <svg v-else class="absolute inset-0 m-auto opacity-60" width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="#8b6df0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5 9.5V21h14V9.5" />
                </svg>
            </div>
            <!-- Info -->
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-foreground truncate">{{ property.project_name }}</div>
                <div class="flex items-center flex-wrap gap-x-3 gap-y-0.5 mt-1">
                    <span class="text-xs text-muted-foreground font-medium">Unit {{ property.unit_number }}</span>
                    <span v-if="property.floor" class="text-xs text-muted-foreground font-medium">Floor {{
                        property.floor }}</span>
                    <span v-if="property.bedrooms" class="text-xs text-muted-foreground font-medium">{{
                        property.bedrooms }} Bed</span>
                    <span v-if="property.size_sqft" class="text-xs text-muted-foreground font-medium">{{
                        Number(property.size_sqft).toLocaleString() }} sqft</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#efeafc] text-client-accent">{{
                        property.type_label }}</span>
                </div>
            </div>
            <!-- Price + status -->
            <div class="text-right flex-none">
                <div class="text-sm font-extrabold text-foreground" style="letter-spacing:-0.015em;">{{
                    fmtBDT(property.price_agreed) }}</div>
                <span class="inline-block mt-1 text-xs font-bold px-2.5 py-1 rounded-lg"
                    :class="statusCls[property.status_color] ?? statusCls.slate">{{ property.status_label }}</span>
            </div>
        </div>

        <!-- Demo notice -->
        <div v-else class="flex items-center gap-3 px-4 py-3 rounded-xl border"
            style="background:#fffbeb; border-color:#fde68a;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" class="flex-none">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 8v4M12 16h.01" />
            </svg>
            <p class="text-xs font-medium text-amber-800">Sample calculation — use the sliders to explore any scenario.
            </p>
        </div>

        <!-- ── KPI 5-col row ──────────────────────────────────────── -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-3">
            <!-- Property Price -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="h-11 w-11 flex-none flex items-center justify-center rounded-xl bg-[#efeafc]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5b3fe8" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10.5 12 3l9 7.5" />
                            <path d="M5 9.5V21h14V9.5" />
                            <path d="M10 21v-6h4v6" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Property Price</span>
                </div>
                <div class="mt-3.5 text-base font-extrabold text-foreground"
                    style="letter-spacing:-0.02em; word-break:break-all;">{{ fmtBDT(calc.price) }}</div>
            </div>
            <!-- Down Payment -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div
                        class="h-11 w-11 flex-none flex items-center justify-center rounded-xl bg-green-100 dark:bg-green-500/15">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="7" width="18" height="13" rx="2.5" />
                            <path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7" />
                            <path d="M3 12h18" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Down Payment</span>
                </div>
                <div class="mt-3.5 text-base font-extrabold text-foreground" style="letter-spacing:-0.02em;">{{
                    fmtM(calcDown) }}</div>
                <div class="mt-1 text-xs font-bold text-green-600 dark:text-green-400">{{ calc.downPct }}% <span
                        class="font-medium text-muted-foreground">of Price</span></div>
            </div>
            <!-- Loan Amount -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="h-11 w-11 flex-none flex items-center justify-center rounded-xl bg-[#efeafc]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5b3fe8" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 11 12 4l9 7" />
                            <path d="M5 10v10h14V10" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Loan Amount</span>
                </div>
                <div class="mt-3.5 text-base font-extrabold text-foreground" style="letter-spacing:-0.02em;">{{
                    fmtM(calcLoan) }}</div>
                <div class="mt-1 text-xs font-bold text-client-accent">{{ calcLoanPct }}% <span
                        class="font-medium text-muted-foreground">of Price</span></div>
            </div>
            <!-- Interest Rate -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div
                        class="h-11 w-11 flex-none flex items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f08a1d" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="7" cy="7" r="3" />
                            <circle cx="17" cy="17" r="3" />
                            <path d="M6 18 18 6" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Interest Rate</span>
                </div>
                <div class="mt-3.5 text-base font-extrabold text-foreground" style="letter-spacing:-0.02em;">{{
                    calc.ratePct.toFixed(2) }}%</div>
                <div class="mt-1 text-xs font-medium text-muted-foreground">Per Annum</div>
            </div>
            <!-- Loan Tenure -->
            <div
                class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div
                        class="h-11 w-11 flex-none flex items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/15">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2f6bdb" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4.5" width="18" height="16.5" rx="2.5" />
                            <path d="M3 9h18M8 2.5v4M16 2.5v4" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">Loan Tenure</span>
                </div>
                <div class="mt-3.5 text-base font-extrabold text-foreground" style="letter-spacing:-0.02em;">{{
                    calc.years }} Years</div>
                <div class="mt-1 text-xs font-medium text-muted-foreground">{{ calcN }} Months</div>
            </div>
        </div>

        <!-- ── Body ──────────────────────────────────────────────── -->
        <div class="flex gap-5 items-start">

            <!-- ===== FEED ===================================== -->
            <div class="flex-1 min-w-0 flex flex-col gap-5">

                <!-- Calculator card -->
                <div
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-5">Mortgage Calculator</h3>

                    <!-- Two columns: sliders | result -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- ── Sliders ── -->
                        <div class="flex flex-col gap-5">

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-muted-foreground">Property Price</span>
                                    <span class="text-xs font-extrabold text-client-accent">{{ fmtBDT(calc.price)
                                    }}</span>
                                </div>
                                <input type="range" min="1000000" max="50000000" step="500000" :value="calc.price"
                                    @input="onPriceRange" class="calc-range" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-muted-foreground">Down Payment</span>
                                    <span class="text-xs font-extrabold text-client-accent">{{ calc.downPct }}%</span>
                                </div>
                                <input type="range" min="0" max="60" step="1" :value="calc.downPct" @input="onDownRange"
                                    class="calc-range" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-muted-foreground">Loan Amount</span>
                                    <span class="text-xs font-extrabold text-client-accent">{{ calcLoanPct }}%</span>
                                </div>
                                <input type="range" min="40" max="100" step="1" :value="calcLoanPct"
                                    @input="onLoanRange" class="calc-range" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-muted-foreground">Interest Rate (Per
                                        Annum)</span>
                                    <span class="text-xs font-extrabold text-client-accent">{{ calc.ratePct.toFixed(2)
                                    }}%</span>
                                </div>
                                <input type="range" min="4" max="14" step="0.05" :value="calc.ratePct"
                                    @input="onRateRange" class="calc-range" />
                            </div>

                            <!-- Tenure buttons -->
                            <div>
                                <span class="block text-xs font-semibold text-muted-foreground mb-2.5">Loan
                                    Tenure</span>
                                <div class="flex gap-2">
                                    <button v-for="yr in TENURE" :key="yr"
                                        class="flex-1 py-2 rounded-xl text-xs font-bold border cursor-pointer transition-all"
                                        :class="calc.years === yr
                                            ? 'text-white border-transparent'
                                            : 'text-muted-foreground border-[#ededf3] dark:border-white/[0.08] bg-client-surface-card hover:border-client-accent/40 hover:text-client-accent'"
                                        :style="calc.years === yr ? 'background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 6px 14px -5px rgba(81,50,224,.5);' : ''"
                                        @click="calc.years = yr">{{ yr }}yr</button>
                                </div>
                            </div>

                            <!-- Reset -->
                            <button
                                class="self-start inline-flex items-center gap-2 border border-[#ededf3] dark:border-white/[0.08] bg-client-surface-card text-muted-foreground hover:bg-muted hover:text-foreground cursor-pointer text-xs font-bold px-4 py-2.5 rounded-xl transition-colors"
                                @click="resetCalc">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4v5h5M20 20v-5h-5" />
                                    <path d="M4 9a8 8 0 0 1 14-3M20 15a8 8 0 0 1-14 3" />
                                </svg>
                                Reset to Defaults
                            </button>
                        </div>

                        <!-- ── Results ── -->
                        <div class="flex flex-col gap-4">
                            <!-- Dark result card -->
                            <div class="rounded-2xl p-5"
                                style="background:linear-gradient(150deg,#3a2a8f,#241a5c); box-shadow:0 14px 30px -14px rgba(36,26,92,.5);">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <span class="text-xs font-semibold leading-tight" style="color:#b3a9f0;">Estimated
                                        Monthly Payment</span>
                                    <span
                                        class="flex-none inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap"
                                        :style="`background:${affordability.bg}; color:${affordability.color};`">
                                        <span class="w-1.5 h-1.5 rounded-full flex-none"
                                            :style="`background:${affordability.color};`"></span>
                                        {{ affordability.label }}
                                    </span>
                                </div>
                                <div class="text-3xl font-extrabold text-white mt-1 mb-5"
                                    style="letter-spacing:-0.03em;">BDT {{ fmt(calcMonthly) }}</div>

                                <div class="flex flex-col gap-0">
                                    <div class="flex items-center justify-between py-3 border-b"
                                        style="border-color:rgba(255,255,255,.1);">
                                        <span class="text-xs font-medium" style="color:#bcb2ec;">Principal Amount</span>
                                        <span class="text-xs font-bold text-white">{{ fmtBDT(calcLoan) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-3 border-b"
                                        style="border-color:rgba(255,255,255,.1);">
                                        <span class="text-xs font-medium" style="color:#bcb2ec;">Total Interest</span>
                                        <span class="text-xs font-bold text-white">{{ fmtBDT(calcTotalInterest)
                                        }}</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-3">
                                        <span class="text-sm font-bold text-white">Total Payment</span>
                                        <span class="text-sm font-extrabold" style="color:#ffc24a;">{{
                                            fmtBDT(calcTotalPay) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- AI tip -->
                            <div class="flex items-start gap-3 p-4 rounded-xl border border-[#efeafc] bg-[#f7f6fd]">
                                <div
                                    class="w-9 h-9 flex-none rounded-lg bg-[#efeafc] flex items-center justify-center text-client-accent">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M5 13c0-3 2.5-5 6-5s6 2 6 5a4 4 0 0 1-1.5 3.2V18a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-1.8A4 4 0 0 1 5 13Z" />
                                        <circle cx="17.5" cy="9.5" r="1" />
                                    </svg>
                                </div>
                                <p class="text-xs font-medium leading-relaxed text-[#5a4fa0] mt-0.5">{{ aiSuggestion }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projection chart -->
                <div
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                        <h3 class="text-base font-bold text-foreground">Payment Projection Over Tenure</h3>
                        <div class="flex items-center gap-4 text-xs font-semibold text-muted-foreground flex-wrap">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-0.5 rounded-full bg-client-accent inline-block flex-none"></span>
                                Principal Balance
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-0.5 rounded-full bg-green-500 inline-block flex-none"></span>
                                Total Interest
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg width="14" height="4" viewBox="0 0 14 4" fill="none" class="flex-none">
                                    <line x1="0" y1="2" x2="14" y2="2" stroke="#c2c2cf" stroke-width="2.5"
                                        stroke-dasharray="4 3" />
                                </svg>
                                Total Payment
                            </span>
                        </div>
                    </div>
                    <VueApexCharts type="line" height="260" :options="projectionOptions" :series="projectionSeries" />
                </div>

                <!-- Tips banner -->
                <div
                    class="flex items-center gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div
                        class="w-11 h-11 flex-none rounded-xl bg-[#efeafc] flex items-center justify-center text-client-accent">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M5 13c0-3 2.5-5 6-5s6 2 6 5a4 4 0 0 1-1.5 3.2V18a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-1.8A4 4 0 0 1 5 13Z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-foreground">Tips to Save More</div>
                        <div class="text-xs font-medium text-muted-foreground mt-0.5 leading-relaxed">Paying an extra
                            BDT 10,000 monthly can reduce your loan tenure and save you significant interest.</div>
                    </div>
                    <button
                        class="hidden sm:inline-flex items-center gap-2 text-xs font-bold px-4 py-2.5 rounded-xl border flex-none transition-colors text-client-accent"
                        style="border-color:#e6e1fb; background:#f6f3ff;">
                        View Details
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h13M13 6l6 6-6 6" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- ===== END FEED ================================= -->

            <!-- ===== RIGHT RAIL =============================== -->
            <aside class="hidden xl:flex flex-col gap-4" style="width:300px; flex:none;">

                <!-- Amortization donut -->
                <div
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-4">Amortization Overview</h3>
                    <div class="flex items-center gap-4">
                        <div class="relative flex-none" style="width:130px; height:130px;">
                            <VueApexCharts type="donut" width="130" height="130"
                                :options="amortizationDonutOptions" :series="amortizationDonutSeries" />
                            <div class="absolute rounded-full bg-client-surface-card flex flex-col items-center justify-center pointer-events-none"
                                style="inset:26px;">
                                <div class="text-[9px] font-bold text-muted-foreground uppercase tracking-wider">BDT
                                </div>
                                <div class="text-lg font-extrabold text-foreground leading-none"
                                    style="letter-spacing:-0.025em;">{{ (calcTotalPay / 1000000).toFixed(1) }}M</div>
                                <div
                                    class="text-[10px] font-semibold text-muted-foreground text-center leading-tight mt-1">
                                    Total<br>Payment</div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div>
                                <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                                    <span class="w-2.5 h-2.5 rounded-full bg-client-accent flex-none"></span>Principal
                                </div>
                                <div class="text-sm font-extrabold text-foreground mt-1">{{ fmtBDT(calcLoan) }}</div>
                                <div class="text-xs font-semibold text-muted-foreground">{{ calcPrincipalPct }}%</div>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-none"></span>Total Interest
                                </div>
                                <div class="text-sm font-extrabold text-foreground mt-1">{{ fmtBDT(calcTotalInterest) }}
                                </div>
                                <div class="text-xs font-semibold text-muted-foreground">{{ calcInterestPct }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mortgage Summary -->
                <div
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-1">Mortgage Summary</h3>
                    <div class="flex flex-col divide-y divide-[#f4f4f8] dark:divide-white/[0.05]">
                        <div class="flex items-center justify-between py-3">
                            <span class="text-xs font-medium text-muted-foreground">Monthly Payment</span>
                            <span class="text-xs font-bold text-foreground">BDT {{ fmt(calcMonthly) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-xs font-medium text-muted-foreground">Total Interest</span>
                            <span class="text-xs font-bold text-foreground">{{ fmtBDT(calcTotalInterest) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-xs font-medium text-muted-foreground">Total Repayment</span>
                            <span class="text-xs font-bold text-foreground">{{ fmtBDT(calcTotalPay) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-xs font-medium text-muted-foreground">Start Date</span>
                            <span class="text-xs font-bold text-foreground">{{ todayStr }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-xs font-medium text-muted-foreground">Est. Completion</span>
                            <span class="text-xs font-bold text-client-accent">{{ completionStr }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-3">Quick Actions</h3>
                    <div class="flex flex-col gap-0.5">
                        <a v-for="(action, i) in [
                            { label: 'Apply for Financing', bg: 'bg-[#efeafc]', stroke: '#5b3fe8', path: 'M3 11 12 4l9 7 M5 10v10h14V10' },
                            { label: 'Compare Loan Offers', bg: 'bg-green-100 dark:bg-green-500/15', stroke: '#16a34a', path: 'M9 4v16M15 4v16M4 9h16M4 15h16' },
                            { label: 'Download Loan Summary', bg: 'bg-amber-100 dark:bg-amber-500/15', stroke: '#f08a1d', path: 'M12 3v12M7 10l5 5 5-5 M4 20h16' },
                            { label: 'Talk to Finance Advisor', bg: 'bg-blue-100 dark:bg-blue-500/15', stroke: '#2f6bdb', path: 'M4 5h16v11H9l-4 4z' },
                        ]" :key="i" href="#"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#faf9fd] dark:hover:bg-white/[0.04] transition-colors text-foreground">
                            <span :class="['w-8 h-8 flex-none rounded-lg flex items-center justify-center', action.bg]">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="action.stroke"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    v-html="action.path.split(' ').map(p => `<path d='${p}'/>`).join('')" />
                            </span>
                            <span class="flex-1 text-xs font-semibold">{{ action.label }}</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#c2c2cf"
                                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Banking Partners -->
                <div
                    class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3.5">
                        <h3 class="text-base font-bold text-foreground">Banking Partners</h3>
                        <a href="#" class="text-xs font-bold text-client-accent hover:underline">View All</a>
                    </div>
                    <div v-if="property?.eligible_banks?.length" class="flex flex-col gap-2">
                        <div v-for="bank in property.eligible_banks" :key="bank"
                            class="flex items-center gap-2.5 px-3 py-2.5 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl">
                            <span
                                class="w-7 h-7 flex-none rounded-lg bg-[#efeafc] flex items-center justify-center text-client-accent">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 21h18M5 21V10M19 21V10M3 10l9-6 9 6" />
                                </svg>
                            </span>
                            <span class="text-xs font-bold text-foreground">{{ bank }}</span>
                        </div>
                    </div>
                    <div v-else class="grid grid-cols-2 gap-2">
                        <div class="flex items-center justify-center h-11 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold"
                            style="color:#1b3a6b;">BRAC Bank</div>
                        <div class="flex items-center justify-center h-11 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold"
                            style="color:#c0392b;">City Bank</div>
                        <div class="flex items-center justify-center h-11 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold"
                            style="color:#1d7a46;">Islami Bank</div>
                        <div class="flex items-center justify-center h-11 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold"
                            style="color:#2c3e8c;">Prime Bank</div>
                    </div>
                    <div v-if="property?.max_loan_amount > 0"
                        class="mt-3.5 pt-3.5 border-t border-[#f0f0f5] dark:border-white/[0.06]">
                        <div class="text-xs font-semibold text-muted-foreground">Max Loan Available</div>
                        <div class="text-sm font-extrabold text-foreground mt-0.5">{{ fmtBDT(property.max_loan_amount)
                        }}</div>
                    </div>
                </div>
            </aside>
            <!-- ===== END RIGHT RAIL =========================== -->
        </div>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer
            class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] px-5 py-3.5 shadow-sm">
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

<style scoped>
.calc-range {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 6px;
    border-radius: 4px;
    background: #ece9f6;
    outline: none;
    cursor: pointer;
    display: block;
}

.calc-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #5b3fe8;
    border: 3px solid #fff;
    box-shadow: 0 2px 6px rgba(81, 50, 224, 0.4);
    cursor: pointer;
}

.calc-range::-moz-range-thumb {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #5b3fe8;
    border: 3px solid #fff;
    box-shadow: 0 2px 6px rgba(81, 50, 224, 0.4);
    cursor: pointer;
}
</style>
