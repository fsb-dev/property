<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

// ─── Static dummy data ───────────────────────────────────────────────────────
const DUMMY_PROPERTIES = [
    {
        id: 1,
        project_name: 'Lake View Residence',
        project_location: 'Gulshan, Dhaka',
        unit_number: 'A-702',
        type_label: 'Apartment',
        bedrooms: 3,
        size_sqft: 1850,
        floor: 7,
        purchase_price: 18500000,
        purchase_year: 2023,
        current_value: 21200000,
        monthly_rental: 55000,
        monthly_mortgage: 38000,
        monthly_maintenance: 6500,
        monthly_tax: 3200,
        occupancy_rate: 92,
        appreciation_rate: 7.2,
        area_avg_yield: 4.8,
        area_avg_price_sqft: 9800,
        developer_rating: 4.5,
        liquidity_score: 72,
        location_demand: 88,
        status: 'purchased',
        status_label: 'Purchased',
        status_color: 'green',
    },
    {
        id: 2,
        project_name: 'Sky Tower Gulshan',
        project_location: 'Gulshan 2, Dhaka',
        unit_number: 'B-1204',
        type_label: 'Penthouse',
        bedrooms: 4,
        size_sqft: 2900,
        floor: 12,
        purchase_price: 34000000,
        purchase_year: 2022,
        current_value: 40500000,
        monthly_rental: 95000,
        monthly_mortgage: 72000,
        monthly_maintenance: 10000,
        monthly_tax: 5800,
        occupancy_rate: 88,
        appreciation_rate: 9.1,
        area_avg_yield: 5.1,
        area_avg_price_sqft: 11200,
        developer_rating: 4.8,
        liquidity_score: 65,
        location_demand: 91,
        status: 'purchased',
        status_label: 'Purchased',
        status_color: 'green',
    },
    {
        id: 3,
        project_name: 'Green Valley Dhanmondi',
        project_location: 'Dhanmondi, Dhaka',
        unit_number: 'C-304',
        type_label: 'Apartment',
        bedrooms: 2,
        size_sqft: 1250,
        floor: 3,
        purchase_price: 11000000,
        purchase_year: 2024,
        current_value: 11800000,
        monthly_rental: 32000,
        monthly_mortgage: 24000,
        monthly_maintenance: 4000,
        monthly_tax: 2100,
        occupancy_rate: 95,
        appreciation_rate: 6.5,
        area_avg_yield: 4.3,
        area_avg_price_sqft: 8400,
        developer_rating: 4.2,
        liquidity_score: 80,
        location_demand: 83,
        status: 'reserved',
        status_label: 'Reserved',
        status_color: 'yellow',
    },
];

// ─── State ───────────────────────────────────────────────────────────────────
const selectedId = ref(DUMMY_PROPERTIES[0].id);
const property   = computed(() => DUMMY_PROPERTIES.find(p => p.id === selectedId.value));

// ─── Derived financials ───────────────────────────────────────────────────────
const totalGain       = computed(() => property.value.current_value - property.value.purchase_price);
const totalGainPct    = computed(() => (totalGain.value / property.value.purchase_price) * 100);
const annualRental    = computed(() => property.value.monthly_rental * 12 * (property.value.occupancy_rate / 100));
const grossYield      = computed(() => (annualRental.value / property.value.purchase_price) * 100);
const annualExpenses  = computed(() => (property.value.monthly_maintenance + property.value.monthly_tax) * 12);
const netAnnualRental = computed(() => annualRental.value - annualExpenses.value);
const netYield        = computed(() => (netAnnualRental.value / property.value.purchase_price) * 100);
const netMonthly      = computed(() => property.value.monthly_rental
    - property.value.monthly_mortgage
    - property.value.monthly_maintenance
    - property.value.monthly_tax);
const investmentScore = computed(() => {
    const yieldScore     = Math.min(grossYield.value / 8 * 35, 35);
    const gainScore      = Math.min(totalGainPct.value / 20 * 25, 25);
    const locationScore  = property.value.location_demand * 0.25;
    const liquidityScore = property.value.liquidity_score * 0.15;
    return Math.round(yieldScore + gainScore + locationScore + liquidityScore);
});
const pricePerSqft    = computed(() => Math.round(property.value.current_value / property.value.size_sqft));
const yearsOwned      = computed(() => 2026 - property.value.purchase_year);
const breakEvenYear   = computed(() => {
    let bal = property.value.purchase_price;
    for (let y = 1; y <= 20; y++) {
        bal -= netAnnualRental.value;
        if (bal <= 0) return y;
    }
    return 20;
});

// ─── Capital growth projection (purchase year → +10 years) ───────────────────
const projectionYears = computed(() => {
    const start = property.value.purchase_year;
    const rate  = property.value.appreciation_rate / 100;
    return Array.from({ length: 12 }, (_, i) => {
        const yr = start + i;
        const val = property.value.purchase_price * Math.pow(1 + rate, i);
        return { year: yr, value: Math.round(val) };
    });
});

const chartData = computed(() => {
    const pts  = projectionYears.value;
    const PADL = 68, PADR = 970, TOP = 20, BOT = 260;
    const W    = PADR - PADL;
    const maxV = Math.ceil(Math.max(...pts.map(p => p.value)) / 5000000) * 5000000;
    const minV = Math.floor(Math.min(...pts.map(p => p.value)) / 5000000) * 5000000;
    const rangeV = maxV - minV;

    function px(i) { return PADL + (i / (pts.length - 1)) * W; }
    function py(v) { return BOT - ((v - minV) / rangeV) * (BOT - TOP); }

    const linePoints = pts.map((p, i) => `${px(i)},${py(p.value)}`).join(' ');
    const areaPoints = `${PADL},${BOT} ` + pts.map((p, i) => `${px(i)},${py(p.value)}`).join(' ') + ` ${PADR},${BOT}`;

    const nowIdx  = yearsOwned.value;
    const nowX    = px(Math.min(nowIdx, pts.length - 1));
    const nowY    = py(pts[Math.min(nowIdx, pts.length - 1)].value);

    const yLabels = [];
    const steps   = 5;
    for (let s = 0; s <= steps; s++) {
        const v = minV + (rangeV / steps) * s;
        yLabels.push({ y: py(v), label: fmtM(v) });
    }

    const xLabels = pts
        .filter((_, i) => i % 2 === 0)
        .map((p, i) => ({ x: px(i * 2), label: String(p.year) }));

    return { linePoints, areaPoints, nowX, nowY, yLabels, xLabels, pts, px, py };
});

// ─── ROI timeline table ───────────────────────────────────────────────────────
const roiTimeline = computed(() => {
    const rate = property.value.appreciation_rate / 100;
    return [1, 2, 3, 5, 7, 10].map(y => {
        const val  = Math.round(property.value.purchase_price * Math.pow(1 + rate, y));
        const gain = val - property.value.purchase_price;
        const roi  = (gain / property.value.purchase_price) * 100;
        return { year: y, value: val, gain, roi };
    });
});

// ─── Expense breakdown ────────────────────────────────────────────────────────
const expenses = computed(() => [
    { label: 'Mortgage',     amount: property.value.monthly_mortgage,    color: '#6a4dff' },
    { label: 'Maintenance',  amount: property.value.monthly_maintenance,  color: '#f59e0b' },
    { label: 'Tax & Charges',amount: property.value.monthly_tax,          color: '#ef4444' },
]);

// ─── Formatters ───────────────────────────────────────────────────────────────
function fmtBDT(n) {
    return 'BDT ' + Math.round(n).toLocaleString('en-BD');
}
function fmtM(n) {
    return (n / 1000000).toFixed(1) + 'M';
}
function fmtPct(n) {
    return n.toFixed(1) + '%';
}
function scoreColor(s) {
    if (s >= 75) return '#22c55e';
    if (s >= 55) return '#f59e0b';
    return '#ef4444';
}
function scoreLabel(s) {
    if (s >= 75) return 'Excellent';
    if (s >= 55) return 'Good';
    return 'Fair';
}
function statusStyle(c) {
    const map = {
        green:  { bg: 'rgba(34,197,94,.12)',  text: '#16a34a' },
        yellow: { bg: 'rgba(234,179,8,.12)',  text: '#b45309' },
        red:    { bg: 'rgba(239,68,68,.12)',  text: '#dc2626' },
        blue:   { bg: 'rgba(99,102,241,.12)', text: '#4338ca' },
    };
    return map[c] || map.blue;
}

// ─── Donut arc helper ─────────────────────────────────────────────────────────
function donutDash(pct, r) {
    const circ = 2 * Math.PI * r;
    return `${(pct / 100) * circ} ${circ}`;
}
</script>

<template>
    <ClientLayout>
        <div>

            <!-- Breadcrumb -->
            <nav style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#9a9ab0; margin-bottom:20px;">
                <Link :href="route('client.dashboard')" style="color:#9a9ab0; text-decoration:none; transition:color .15s;" class="hover:text-foreground">Home</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                <span style="color:#3d3d55; font-weight:600;">Investment Analyzer</span>
            </nav>

            <!-- Page title -->
            <div style="margin-bottom:24px;">
                <h1 style="font-size:22px; font-weight:800; color:#16162a; letter-spacing:-0.02em; margin:0 0 4px;">Investment Analyzer</h1>
                <p style="font-size:13.5px; color:#7a7a90; margin:0;">Analyze returns, yields, and growth projections across your property portfolio.</p>
            </div>

            <!-- Property selector pills -->
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:28px;">
                <button
                    v-for="p in DUMMY_PROPERTIES"
                    :key="p.id"
                    @click="selectedId = p.id"
                    :style="selectedId === p.id
                        ? 'background:linear-gradient(100deg,#6a4dff,#5132e0); color:#fff; border:1.5px solid transparent; box-shadow:0 4px 14px -4px rgba(81,50,224,.45);'
                        : 'background:#fff; color:#3d3d55; border:1.5px solid #e8e6f0;'"
                    style="display:inline-flex; align-items:center; gap:9px; padding:9px 16px; border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; transition:all .18s; font-family:inherit;"
                >
                    <span style="width:7px; height:7px; border-radius:50%; flex-shrink:0;"
                        :style="selectedId === p.id ? 'background:rgba(255,255,255,.7)' : 'background:#6a4dff'"></span>
                    {{ p.project_name }} · {{ p.unit_number }}
                    <span
                        :style="{
                            background: selectedId === p.id ? 'rgba(255,255,255,.18)' : statusStyle(p.status_color).bg,
                            color:      selectedId === p.id ? '#fff' : statusStyle(p.status_color).text,
                        }"
                        style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px;"
                    >{{ p.status_label }}</span>
                </button>
            </div>

            <!-- KPI row -->
            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:24px;" class="kpi-grid">
                <!-- Purchase Price -->
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:16px 18px;">
                    <div style="font-size:11.5px; font-weight:600; color:#9a9ab0; margin-bottom:8px; text-transform:uppercase; letter-spacing:.04em;">Purchase Price</div>
                    <div style="font-size:15px; font-weight:800; color:#16162a; line-height:1.2;">{{ fmtM(property.purchase_price) }}</div>
                    <div style="font-size:11px; color:#b0b0c0; margin-top:3px;">BDT</div>
                </div>
                <!-- Current Value -->
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:16px 18px;">
                    <div style="font-size:11.5px; font-weight:600; color:#9a9ab0; margin-bottom:8px; text-transform:uppercase; letter-spacing:.04em;">Current Value</div>
                    <div style="font-size:15px; font-weight:800; color:#16162a; line-height:1.2;">{{ fmtM(property.current_value) }}</div>
                    <div style="font-size:11px; color:#b0b0c0; margin-top:3px;">BDT · Est.</div>
                </div>
                <!-- Total Gain -->
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:16px 18px;">
                    <div style="font-size:11.5px; font-weight:600; color:#9a9ab0; margin-bottom:8px; text-transform:uppercase; letter-spacing:.04em;">Total Gain</div>
                    <div style="font-size:15px; font-weight:800; line-height:1.2;" :style="totalGain >= 0 ? 'color:#16a34a' : 'color:#dc2626'">
                        {{ totalGain >= 0 ? '+' : '' }}{{ fmtM(totalGain) }}
                    </div>
                    <div style="font-size:11px; color:#b0b0c0; margin-top:3px;">{{ fmtPct(totalGainPct) }} overall</div>
                </div>
                <!-- Gross Yield -->
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:16px 18px;">
                    <div style="font-size:11.5px; font-weight:600; color:#9a9ab0; margin-bottom:8px; text-transform:uppercase; letter-spacing:.04em;">Gross Yield</div>
                    <div style="font-size:15px; font-weight:800; color:#16162a; line-height:1.2;">{{ fmtPct(grossYield) }}</div>
                    <div style="font-size:11px; color:#b0b0c0; margin-top:3px;">Net {{ fmtPct(netYield) }}</div>
                </div>
                <!-- Monthly Cash Flow -->
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:16px 18px;">
                    <div style="font-size:11.5px; font-weight:600; color:#9a9ab0; margin-bottom:8px; text-transform:uppercase; letter-spacing:.04em;">Monthly Cash Flow</div>
                    <div style="font-size:15px; font-weight:800; line-height:1.2;" :style="netMonthly >= 0 ? 'color:#16a34a' : 'color:#dc2626'">
                        {{ netMonthly >= 0 ? '+' : '' }}{{ fmtBDT(netMonthly) }}
                    </div>
                    <div style="font-size:11px; color:#b0b0c0; margin-top:3px;">After all expenses</div>
                </div>
            </div>

            <!-- Body: feed + right rail -->
            <div style="display:flex; gap:20px; align-items:flex-start;">

                <!-- Feed -->
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:18px;">

                    <!-- Capital Growth Chart -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; overflow:hidden;">
                        <!-- card header -->
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid #f0eff7;">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(106,77,255,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6a4dff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:#16162a;">Capital Growth Projection</div>
                                <div style="font-size:12px; color:#9a9ab0;">Purchase year → 10-year forecast at {{ fmtPct(property.appreciation_rate) }} p.a.</div>
                            </div>
                            <div style="margin-left:auto; display:flex; align-items:center; gap:16px; font-size:12px; color:#9a9ab0;">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <span style="display:inline-block; width:24px; height:3px; border-radius:2px; background:linear-gradient(90deg,#6a4dff,#9b87ff);"></span>
                                    Value
                                </span>
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <svg width="14" height="4" viewBox="0 0 14 4" fill="none">
                                        <line x1="0" y1="2" x2="14" y2="2" stroke="#f59e0b" stroke-width="2" stroke-dasharray="4 3"/>
                                    </svg>
                                    Today
                                </span>
                            </div>
                        </div>
                        <div style="padding:16px 20px 12px;">
                            <svg viewBox="0 0 1000 310" class="w-full" style="height:220px; display:block;">
                                <defs>
                                    <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#6a4dff" stop-opacity="0.18"/>
                                        <stop offset="100%" stop-color="#6a4dff" stop-opacity="0.01"/>
                                    </linearGradient>
                                </defs>
                                <!-- grid lines -->
                                <line v-for="yl in chartData.yLabels" :key="'g'+yl.label"
                                    x1="68" :y1="yl.y" x2="970" :y2="yl.y"
                                    stroke="#f0eff7" stroke-width="1"/>
                                <!-- y-axis labels -->
                                <text v-for="yl in chartData.yLabels" :key="'y'+yl.label"
                                    x="62" :y="yl.y+4" text-anchor="end" font-size="11" fill="#b0b0c0"
                                    font-family="Plus Jakarta Sans,system-ui,sans-serif">{{ yl.label }}</text>
                                <!-- area fill -->
                                <polygon :points="chartData.areaPoints" fill="url(#areaGrad)"/>
                                <!-- value line -->
                                <polyline :points="chartData.linePoints" fill="none" stroke="#6a4dff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <!-- "today" dashed vertical line -->
                                <line :x1="chartData.nowX" y1="20" :x2="chartData.nowX" y2="260"
                                    stroke="#f59e0b" stroke-width="1.5" stroke-dasharray="5 4"/>
                                <!-- today dot -->
                                <circle :cx="chartData.nowX" :cy="chartData.nowY" r="5" fill="#fff" stroke="#6a4dff" stroke-width="2.5"/>
                                <!-- x-axis labels -->
                                <text v-for="xl in chartData.xLabels" :key="'xl'+xl.label"
                                    :x="xl.x" y="292" text-anchor="middle" font-size="11" fill="#b0b0c0"
                                    font-family="Plus Jakarta Sans,system-ui,sans-serif">{{ xl.label }}</text>
                            </svg>
                        </div>
                    </div>

                    <!-- Rental Yield Analysis -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; overflow:hidden;">
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid #f0eff7;">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(34,197,94,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:#16162a;">Rental Yield Analysis</div>
                                <div style="font-size:12px; color:#9a9ab0;">{{ property.occupancy_rate }}% occupancy · vs {{ fmtPct(property.area_avg_yield) }} area avg</div>
                            </div>
                        </div>
                        <div style="padding:18px 20px;">
                            <!-- Yield comparison bars -->
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                                <div style="background:#f8f7fe; border-radius:12px; padding:14px 16px;">
                                    <div style="font-size:11.5px; color:#9a9ab0; font-weight:600; margin-bottom:6px;">GROSS YIELD</div>
                                    <div style="font-size:22px; font-weight:800; color:#6a4dff; margin-bottom:8px;">{{ fmtPct(grossYield) }}</div>
                                    <div style="height:6px; background:#ece9f6; border-radius:4px; overflow:hidden;">
                                        <div :style="{ width: Math.min(grossYield/10*100,100)+'%' }" style="height:100%; background:linear-gradient(90deg,#6a4dff,#9b87ff); border-radius:4px;"></div>
                                    </div>
                                    <div style="font-size:11px; color:#9a9ab0; margin-top:5px;">Area avg: {{ fmtPct(property.area_avg_yield) }}</div>
                                </div>
                                <div style="background:#f0fdf4; border-radius:12px; padding:14px 16px;">
                                    <div style="font-size:11.5px; color:#9a9ab0; font-weight:600; margin-bottom:6px;">NET YIELD</div>
                                    <div style="font-size:22px; font-weight:800; color:#16a34a; margin-bottom:8px;">{{ fmtPct(netYield) }}</div>
                                    <div style="height:6px; background:#dcfce7; border-radius:4px; overflow:hidden;">
                                        <div :style="{ width: Math.min(netYield/10*100,100)+'%' }" style="height:100%; background:linear-gradient(90deg,#22c55e,#4ade80); border-radius:4px;"></div>
                                    </div>
                                    <div style="font-size:11px; color:#9a9ab0; margin-top:5px;">After expenses &amp; tax</div>
                                </div>
                            </div>
                            <!-- Income vs expenses table -->
                            <div style="border:1px solid #f0eff7; border-radius:10px; overflow:hidden;">
                                <div style="display:grid; grid-template-columns:1fr auto auto; gap:0; font-size:11.5px; font-weight:700; color:#9a9ab0; background:#faf9fd; padding:10px 16px; text-transform:uppercase; letter-spacing:.04em;">
                                    <span>Item</span><span style="text-align:right;">Monthly</span><span style="text-align:right; padding-left:24px;">Annual</span>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr auto auto; gap:0; padding:11px 16px; border-top:1px solid #f0eff7; font-size:13px;">
                                    <span style="color:#16162a; font-weight:600; display:flex; align-items:center; gap:7px;">
                                        <span style="width:8px; height:8px; border-radius:50%; background:#22c55e; display:inline-block;"></span>
                                        Rental Income
                                    </span>
                                    <span style="text-align:right; color:#16a34a; font-weight:700;">+{{ fmtBDT(property.monthly_rental) }}</span>
                                    <span style="text-align:right; color:#16a34a; font-weight:700; padding-left:24px;">+{{ fmtBDT(property.monthly_rental * 12) }}</span>
                                </div>
                                <div v-for="e in expenses" :key="e.label"
                                    style="display:grid; grid-template-columns:1fr auto auto; gap:0; padding:11px 16px; border-top:1px solid #f0eff7; font-size:13px;">
                                    <span style="color:#16162a; font-weight:500; display:flex; align-items:center; gap:7px;">
                                        <span style="width:8px; height:8px; border-radius:50%; display:inline-block;" :style="{ background: e.color }"></span>
                                        {{ e.label }}
                                    </span>
                                    <span style="text-align:right; color:#7a7a90;">−{{ fmtBDT(e.amount) }}</span>
                                    <span style="text-align:right; color:#7a7a90; padding-left:24px;">−{{ fmtBDT(e.amount * 12) }}</span>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr auto auto; gap:0; padding:12px 16px; border-top:2px solid #ededf3; font-size:13.5px; background:#faf9fd;">
                                    <span style="color:#16162a; font-weight:700;">Net Cash Flow</span>
                                    <span style="text-align:right; font-weight:800;" :style="netMonthly>=0?'color:#16a34a':'color:#dc2626'">
                                        {{ netMonthly>=0?'+':'' }}{{ fmtBDT(netMonthly) }}
                                    </span>
                                    <span style="text-align:right; font-weight:800; padding-left:24px;" :style="netMonthly>=0?'color:#16a34a':'color:#dc2626'">
                                        {{ netMonthly>=0?'+':'' }}{{ fmtBDT(netMonthly * 12) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Market Comparison -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; overflow:hidden;">
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid #f0eff7;">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(245,158,11,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-7"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:#16162a;">Market Comparison</div>
                                <div style="font-size:12px; color:#9a9ab0;">{{ property.project_location }} benchmarks</div>
                            </div>
                        </div>
                        <div style="padding:18px 20px; display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                            <!-- Price/sqft -->
                            <div style="border:1px solid #f0eff7; border-radius:12px; padding:14px 16px;">
                                <div style="font-size:11.5px; color:#9a9ab0; font-weight:600; text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">Price per Sq.ft</div>
                                <div style="display:flex; align-items:flex-end; gap:10px; margin-bottom:10px;">
                                    <div>
                                        <div style="font-size:11px; color:#9a9ab0; margin-bottom:2px;">Your property</div>
                                        <div style="font-size:18px; font-weight:800; color:#16162a;">{{ fmtBDT(pricePerSqft) }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#9a9ab0; margin-bottom:2px;">Area average</div>
                                        <div style="font-size:18px; font-weight:800; color:#9a9ab0;">{{ fmtBDT(property.area_avg_price_sqft) }}</div>
                                    </div>
                                </div>
                                <div style="font-size:12px; font-weight:600;"
                                    :style="pricePerSqft > property.area_avg_price_sqft ? 'color:#dc2626' : 'color:#16a34a'">
                                    {{ pricePerSqft > property.area_avg_price_sqft ? '▲' : '▼' }}
                                    {{ Math.abs(((pricePerSqft - property.area_avg_price_sqft) / property.area_avg_price_sqft * 100)).toFixed(1) }}%
                                    {{ pricePerSqft > property.area_avg_price_sqft ? 'above' : 'below' }} market
                                </div>
                            </div>
                            <!-- Rental yield vs market -->
                            <div style="border:1px solid #f0eff7; border-radius:12px; padding:14px 16px;">
                                <div style="font-size:11.5px; color:#9a9ab0; font-weight:600; text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">Rental Yield</div>
                                <div style="display:flex; align-items:flex-end; gap:10px; margin-bottom:10px;">
                                    <div>
                                        <div style="font-size:11px; color:#9a9ab0; margin-bottom:2px;">Your property</div>
                                        <div style="font-size:18px; font-weight:800; color:#16162a;">{{ fmtPct(grossYield) }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#9a9ab0; margin-bottom:2px;">Area average</div>
                                        <div style="font-size:18px; font-weight:800; color:#9a9ab0;">{{ fmtPct(property.area_avg_yield) }}</div>
                                    </div>
                                </div>
                                <div style="font-size:12px; font-weight:600;"
                                    :style="grossYield >= property.area_avg_yield ? 'color:#16a34a' : 'color:#dc2626'">
                                    {{ grossYield >= property.area_avg_yield ? '▲' : '▼' }}
                                    {{ Math.abs(grossYield - property.area_avg_yield).toFixed(2) }}%
                                    {{ grossYield >= property.area_avg_yield ? 'above' : 'below' }} area avg
                                </div>
                            </div>
                            <!-- Break-even -->
                            <div style="border:1px solid #f0eff7; border-radius:12px; padding:14px 16px; grid-column:1/-1;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                    <div style="font-size:11.5px; color:#9a9ab0; font-weight:600; text-transform:uppercase; letter-spacing:.04em;">Break-Even Analysis</div>
                                    <span style="font-size:12px; font-weight:700; color:#6a4dff;">Year {{ breakEvenYear }}</span>
                                </div>
                                <div style="height:8px; background:#ece9f6; border-radius:6px; overflow:hidden; margin-bottom:8px;">
                                    <div :style="{ width: Math.min(yearsOwned/breakEvenYear*100,100)+'%' }"
                                        style="height:100%; background:linear-gradient(90deg,#6a4dff,#9b87ff); border-radius:6px; transition:width .4s;"></div>
                                </div>
                                <div style="font-size:12px; color:#9a9ab0;">
                                    {{ yearsOwned }} of {{ breakEvenYear }} years completed via rental income — {{ yearsOwned >= breakEvenYear ? 'Break-even reached!' : Math.max(0, breakEvenYear - yearsOwned) + ' years remaining' }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Rail -->
                <div style="width:300px; flex-shrink:0; display:flex; flex-direction:column; gap:16px;" class="right-rail">

                    <!-- Investment Score donut -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; padding:20px; text-align:center;">
                        <div style="font-size:13px; font-weight:700; color:#16162a; margin-bottom:16px;">Investment Score</div>
                        <div style="position:relative; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;">
                            <svg width="110" height="110" viewBox="0 0 110 110">
                                <circle cx="55" cy="55" r="44" fill="none" stroke="#f0eff7" stroke-width="10"/>
                                <circle cx="55" cy="55" r="44" fill="none"
                                    :stroke="scoreColor(investmentScore)" stroke-width="10"
                                    :stroke-dasharray="donutDash(investmentScore, 44)"
                                    stroke-dashoffset="69.1"
                                    stroke-linecap="round"
                                    transform="rotate(-90 55 55)"/>
                            </svg>
                            <div style="position:absolute; text-align:center;">
                                <div style="font-size:26px; font-weight:900; color:#16162a; line-height:1;">{{ investmentScore }}</div>
                                <div style="font-size:11px; font-weight:600;" :style="{ color: scoreColor(investmentScore) }">{{ scoreLabel(investmentScore) }}</div>
                            </div>
                        </div>
                        <!-- Risk indicators -->
                        <div style="display:flex; flex-direction:column; gap:8px; text-align:left;">
                            <div style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;">
                                <span style="color:#7a7a90; display:flex; align-items:center; gap:7px;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:#22c55e; flex-shrink:0;"></span>
                                    Location Demand
                                </span>
                                <span style="font-weight:700; color:#16162a;">{{ property.location_demand }}/100</span>
                            </div>
                            <div style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;">
                                <span style="color:#7a7a90; display:flex; align-items:center; gap:7px;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:#f59e0b; flex-shrink:0;"></span>
                                    Market Liquidity
                                </span>
                                <span style="font-weight:700; color:#16162a;">{{ property.liquidity_score }}/100</span>
                            </div>
                            <div style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;">
                                <span style="color:#7a7a90; display:flex; align-items:center; gap:7px;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:#6a4dff; flex-shrink:0;"></span>
                                    Developer Rating
                                </span>
                                <span style="font-weight:700; color:#16162a;">{{ property.developer_rating }}/5.0</span>
                            </div>
                        </div>
                    </div>

                    <!-- ROI Timeline table -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; overflow:hidden;">
                        <div style="padding:14px 16px 12px; border-bottom:1px solid #f0eff7;">
                            <div style="font-size:13px; font-weight:700; color:#16162a;">ROI Timeline</div>
                            <div style="font-size:11.5px; color:#9a9ab0; margin-top:2px;">{{ fmtPct(property.appreciation_rate) }} appreciation p.a.</div>
                        </div>
                        <div>
                            <div style="display:grid; grid-template-columns:40px 1fr 1fr 1fr; font-size:11px; font-weight:700; color:#9a9ab0; background:#faf9fd; padding:8px 12px; text-transform:uppercase; letter-spacing:.04em;">
                                <span>Yr</span><span>Value</span><span>Gain</span><span style="text-align:right;">ROI</span>
                            </div>
                            <div v-for="row in roiTimeline" :key="row.year"
                                style="display:grid; grid-template-columns:40px 1fr 1fr 1fr; font-size:12px; padding:9px 12px; border-top:1px solid #f5f4fb;"
                                :style="row.year === yearsOwned ? 'background:#f8f7fe;' : ''">
                                <span style="font-weight:700; color:#6a4dff;">{{ row.year }}</span>
                                <span style="color:#16162a; font-weight:600;">{{ fmtM(row.value) }}</span>
                                <span style="color:#16a34a; font-weight:600;">+{{ fmtM(row.gain) }}</span>
                                <span style="text-align:right; font-weight:700; color:#16a34a;">{{ fmtPct(row.roi) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; padding:16px;">
                        <div style="font-size:13px; font-weight:700; color:#16162a; margin-bottom:12px;">Quick Actions</div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <button style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; border:1px solid #f0eff7; cursor:pointer; font-size:13px; font-weight:600; color:#16162a; font-family:inherit; background:#fff; text-align:left; width:100%;" class="hover:bg-[#faf9fd]">
                                <span style="width:32px; height:32px; border-radius:8px; background:rgba(106,77,255,.08); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6a4dff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v13"/><path d="M7 11l5 5 5-5"/><path d="M3 21h18"/></svg>
                                </span>
                                Download Report
                            </button>
                            <button style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; border:1px solid #f0eff7; cursor:pointer; font-size:13px; font-weight:600; color:#16162a; font-family:inherit; background:#fff; text-align:left; width:100%;" class="hover:bg-[#faf9fd]">
                                <span style="width:32px; height:32px; border-radius:8px; background:rgba(245,158,11,.08); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h4M3 12h18M3 18h4M9 6h12M9 18h12"/></svg>
                                </span>
                                Compare Properties
                            </button>
                            <button style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; border:1px solid #f0eff7; cursor:pointer; font-size:13px; font-weight:600; color:#16162a; font-family:inherit; background:#fff; text-align:left; width:100%;" class="hover:bg-[#faf9fd]">
                                <span style="width:32px; height:32px; border-radius:8px; background:rgba(34,197,94,.08); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                </span>
                                Contact Advisor
                            </button>
                        </div>
                    </div>

                    <!-- Disclaimer -->
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:12px 14px;">
                        <div style="display:flex; align-items:flex-start; gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <p style="font-size:11.5px; color:#92400e; line-height:1.5; margin:0;">Projections are illustrative estimates only. Past appreciation does not guarantee future returns. Consult a financial advisor before investment decisions.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<style scoped>
@media (max-width: 1100px) {
    .right-rail { display: none !important; }
}
@media (max-width: 860px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 500px) {
    .kpi-grid { grid-template-columns: 1fr 1fr !important; }
}
</style>
