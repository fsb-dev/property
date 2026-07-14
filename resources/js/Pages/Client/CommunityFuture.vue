<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import VueApexCharts from 'vue3-apexcharts';
import { useTheme } from '@/composables/useTheme';

const { isDark } = useTheme();

// ─── Area datasets (keyed by area slug) ──────────────────────────────────────
const AREAS = {
    gulshan: {
        label: 'Gulshan, Dhaka',
        investment_score: 87,
        area_growth_pct: 9.2,
        population_growth: 4.1,
        new_projects_2025: 34,
        avg_price_sqft: 11900,
        amenities: { schools: 12, hospitals: 7, metro: 2, parks: 5, malls: 4 },
        price_years:      ['2020','2021','2022','2023','2024','2025','2026','2027','2028','2029','2030'],
        price_historical: [8200, 9100, 9800, 10500, 11200, 11900, 12700, null, null, null, null],
        price_projected:  [null, null, null, null, null, null, 12700, 13800, 15000, 16300, 17700],
        permit_years:     ['2019','2020','2021','2022','2023','2024','2025'],
        permits:          [142, 168, 134, 198, 224, 251, 283],
        infrastructure: [
            { name: 'Metro Line 6 Extension',      status: 'under_construction', year: '2027', pct: 35, color: 'rgb(198,161,91)' },
            { name: 'Gulshan Avenue 6-Lane Widening', status: 'approved',        year: '2026', pct: 12, color: 'hsl(var(--warning))' },
            { name: 'Gulshan Central Mall',         status: 'under_construction', year: '2026', pct: 58, color: 'hsl(var(--success))' },
            { name: 'Dhaka International School',   status: 'approved',           year: '2027', pct: 8,  color: 'hsl(var(--warning))' },
            { name: 'Lake Shore Eco Park',          status: 'planning',           year: '2028', pct: 0,  color: 'hsl(var(--muted-foreground))' },
        ],
        highlights: [
            'Metro access within 800m by 2027 — premium rental uplift projected',
            'Gulshan ranked #1 expat demand zone in Dhaka Metropolitan Area',
            '12 international schools and colleges within a 3 km radius',
            'Embassy zone classification — sustained high-demand rental market',
        ],
    },
    dhanmondi: {
        label: 'Dhanmondi, Dhaka',
        investment_score: 74,
        area_growth_pct: 6.8,
        population_growth: 2.8,
        new_projects_2025: 21,
        avg_price_sqft: 8800,
        amenities: { schools: 18, hospitals: 5, metro: 1, parks: 8, malls: 3 },
        price_years:      ['2020','2021','2022','2023','2024','2025','2026','2027','2028','2029','2030'],
        price_historical: [6200, 6800, 7100, 7600, 8100, 8800, 9400, null, null, null, null],
        price_projected:  [null, null, null, null, null, null, 9400, 10100, 10900, 11800, 12700],
        permit_years:     ['2019','2020','2021','2022','2023','2024','2025'],
        permits:          [98, 112, 89, 134, 156, 178, 201],
        infrastructure: [
            { name: 'Dhanmondi Ring Road Flyover',  status: 'under_construction', year: '2026', pct: 72, color: 'hsl(var(--success))' },
            { name: 'Metro Line 5 Station (Nearby)',status: 'approved',            year: '2028', pct: 5,  color: 'rgb(198,161,91)' },
            { name: 'Dhanmondi City Mall',           status: 'planning',           year: '2027', pct: 0,  color: 'hsl(var(--muted-foreground))' },
            { name: 'Lake District Green Walk',      status: 'under_construction', year: '2026', pct: 44, color: 'hsl(var(--success))' },
            { name: 'Modern Hospital Complex',       status: 'approved',           year: '2027', pct: 10, color: 'hsl(var(--warning))' },
        ],
        highlights: [
            'Dhanmondi Ring Road flyover reduces commute time to Motijheel by 40%',
            '18 schools nearby — one of Dhaka\'s top educational hubs',
            'Lakeside green spaces driving premium lifestyle demand',
            'Strong owner-occupier market ensures stable long-term value',
        ],
    },
};

// ─── Properties ───────────────────────────────────────────────────────────────
const PROPERTIES = [
    { id: 1, project_name: 'Lake View Residence',     unit_number: 'A-702',  status_label: 'Purchased', status_color: 'green',  areaKey: 'gulshan'    },
    { id: 2, project_name: 'Sky Tower Gulshan',        unit_number: 'B-1204', status_label: 'Purchased', status_color: 'green',  areaKey: 'gulshan'    },
    { id: 3, project_name: 'Green Valley Dhanmondi',   unit_number: 'C-304',  status_label: 'Reserved',  status_color: 'yellow', areaKey: 'dhanmondi'  },
];

// ─── State ────────────────────────────────────────────────────────────────────
const selectedId = ref(1);
const property   = computed(() => PROPERTIES.find(p => p.id === selectedId.value));
const area       = computed(() => AREAS[property.value.areaKey]);

// ─── Chart: Area Price Growth ─────────────────────────────────────────────────
const priceSeries = computed(() => [
    { name: 'Historical', data: area.value.price_historical },
    { name: 'Projected',  data: area.value.price_projected  },
]);

const priceChartOptions = computed(() => ({
    chart: {
        type: 'area', height: 260,
        toolbar: { show: false }, zoom: { enabled: false },
        fontFamily: 'Plus Jakarta Sans, system-ui, sans-serif',
        background: 'transparent',
        animations: { enabled: true, speed: 600, animateGradually: { enabled: true, delay: 80 } },
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: [2.5, 2], dashArray: [0, 6] },
    fill: {
        type: ['gradient', 'solid'],
        gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.01, stops: [0, 95] },
        opacity: [1, 0],
    },
    colors: ['rgb(198,161,91)', 'hsl(var(--warning))'],
    xaxis: {
        categories: area.value.price_years,
        axisBorder: { show: false }, axisTicks: { show: false },
        labels: { style: { colors: 'hsl(var(--muted-foreground))', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, system-ui' } },
    },
    yaxis: {
        labels: {
            style: { colors: 'hsl(var(--muted-foreground))', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, system-ui' },
            formatter: v => v != null ? `${(v / 1000).toFixed(0)}k` : '',
        },
    },
    grid: { borderColor: 'hsl(var(--muted))', strokeDashArray: 4, xaxis: { lines: { show: false } } },
    legend: {
        position: 'top', horizontalAlign: 'right',
        fontFamily: 'Plus Jakarta Sans, system-ui', fontSize: '12px', fontWeight: 600,
        markers: { radius: 4 },
    },
    tooltip: {
        y: { formatter: v => v != null ? `BDT ${v.toLocaleString()}/sqft` : 'N/A' },
        style: { fontFamily: 'Plus Jakarta Sans, system-ui' },
    },
    annotations: {
        xaxis: [{
            x: '2026',
            borderColor: 'hsl(var(--warning))', borderWidth: 1.5,
            label: {
                text: 'Today', position: 'top', offsetY: -4,
                style: { color: 'hsl(var(--warning))', background: 'rgba(251,191,36,0.1)', fontFamily: 'Plus Jakarta Sans, system-ui', fontSize: '11px', fontWeight: 700, padding: { left: 8, right: 8, top: 4, bottom: 4 } },
            },
        }],
    },
    theme: { mode: isDark.value ? 'dark' : 'light' },
}));

// ─── Chart: Development Activity (bar) ────────────────────────────────────────
const permitSeries = computed(() => [
    { name: 'New Permits', data: area.value.permits },
]);

const permitChartOptions = computed(() => ({
    chart: {
        type: 'bar', height: 220,
        toolbar: { show: false },
        fontFamily: 'Plus Jakarta Sans, system-ui, sans-serif',
        background: 'transparent',
        animations: { enabled: true, speed: 500 },
    },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '52%', borderRadiusApplication: 'end' } },
    dataLabels: { enabled: false },
    colors: ['rgb(198,161,91)'],
    fill: {
        type: 'gradient',
        gradient: { shade: 'light', type: 'vertical', shadeIntensity: 0.12, opacityFrom: 1, opacityTo: 0.72 },
    },
    xaxis: {
        categories: area.value.permit_years,
        axisBorder: { show: false }, axisTicks: { show: false },
        labels: { style: { colors: 'hsl(var(--muted-foreground))', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, system-ui' } },
    },
    yaxis: {
        labels: { style: { colors: 'hsl(var(--muted-foreground))', fontSize: '11px', fontFamily: 'Plus Jakarta Sans, system-ui' } },
    },
    grid: { borderColor: 'hsl(var(--muted))', strokeDashArray: 4, xaxis: { lines: { show: false } } },
    tooltip: {
        y: { formatter: v => `${v} new permits` },
        style: { fontFamily: 'Plus Jakarta Sans, system-ui' },
    },
    theme: { mode: isDark.value ? 'dark' : 'light' },
}));

// ─── Chart: Investment Climate Score (radialBar) ──────────────────────────────
const scoreSeries  = computed(() => [area.value.investment_score]);
const scoreColor   = computed(() => area.value.investment_score >= 75 ? 'hsl(var(--success))' : area.value.investment_score >= 55 ? 'rgb(198,161,91)' : 'hsl(var(--warning))');
const scoreLabel   = computed(() => area.value.investment_score >= 75 ? 'Excellent' : area.value.investment_score >= 55 ? 'Good' : 'Fair');

const scoreChartOptions = computed(() => ({
    chart: {
        type: 'radialBar', height: 210,
        fontFamily: 'Plus Jakarta Sans, system-ui, sans-serif',
        background: 'transparent',
    },
    plotOptions: {
        radialBar: {
            hollow: { size: '62%' },
            track: { background: 'hsl(var(--muted))', strokeWidth: '100%' },
            dataLabels: {
                name: { show: false },
                value: {
                    fontSize: '28px', fontWeight: 900, color: 'hsl(var(--foreground))',
                    fontFamily: 'Plus Jakarta Sans, system-ui',
                    offsetY: 8, formatter: v => String(v),
                },
            },
        },
    },
    colors: [scoreColor.value],
    stroke: { lineCap: 'round' },
    theme: { mode: isDark.value ? 'dark' : 'light' },
}));

// ─── Helpers ──────────────────────────────────────────────────────────────────
function statusStyle(c) {
    return { green: { bg: 'rgba(52,211,153,.15)', text: 'hsl(var(--success))' }, yellow: { bg: 'rgba(251,191,36,.15)', text: 'hsl(var(--warning))' } }[c] || { bg: 'rgba(96,165,250,.15)', text: 'hsl(var(--info))' };
}

function infraBadge(s) {
    return {
        under_construction: { bg: 'rgba(245,158,11,.12)', text: 'hsl(var(--warning))', label: 'Under Construction' },
        approved:           { bg: 'rgba(106,77,255,.12)', text: 'rgb(198,161,91)', label: 'Approved'            },
        planning:           { bg: 'rgba(154,154,176,.12)', text: 'hsl(var(--muted-foreground))', label: 'Planning'           },
    }[s] || {};
}
</script>

<template>
    <ClientLayout>
        <div>

            <!-- Breadcrumb -->
            <nav style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:hsl(var(--muted-foreground)); margin-bottom:20px;">
                <Link :href="route('client.dashboard')" style="color:hsl(var(--muted-foreground)); text-decoration:none;" class="hover:text-foreground">Home</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                <span style="color:hsl(var(--foreground)); font-weight:600;">Community & Future</span>
            </nav>

            <!-- Page title -->
            <div style="margin-bottom:24px;">
                <h1 style="font-size:22px; font-weight:800; color:hsl(var(--foreground)); letter-spacing:-0.02em; margin:0 0 4px;">Community & Future Vision</h1>
                <p style="font-size:13.5px; color:hsl(var(--muted-foreground)); margin:0;">Explore area growth trends, upcoming infrastructure, and the long-term outlook for your neighbourhood.</p>
            </div>

            <!-- Property selector pills -->
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:24px;">
                <button
                    v-for="p in PROPERTIES" :key="p.id"
                    @click="selectedId = p.id"
                    :style="selectedId === p.id
                        ? 'background:linear-gradient(100deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-deep))); color:hsl(var(--card)); border:1.5px solid transparent; box-shadow:0 4px 14px -4px rgba(81,50,224,.45);'
                        : 'background:hsl(var(--card)); color:hsl(var(--foreground)); border:1.5px solid hsl(var(--border));'"
                    style="display:inline-flex; align-items:center; gap:9px; padding:9px 16px; border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; transition:all .18s; font-family:inherit;"
                >
                    <span style="width:7px; height:7px; border-radius:50%; flex-shrink:0;"
                        :style="selectedId === p.id ? 'background:rgba(255,255,255,.7)' : 'background:rgb(var(--hv-gold))'"></span>
                    {{ p.project_name }} · {{ p.unit_number }}
                    <span
                        :style="{
                            background: selectedId === p.id ? 'rgba(255,255,255,.18)' : statusStyle(p.status_color).bg,
                            color:      selectedId === p.id ? 'hsl(var(--card))' : statusStyle(p.status_color).text,
                        }"
                        style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px;"
                    >{{ p.status_label }}</span>
                </button>
            </div>

            <!-- KPI row (amenities) -->
            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:22px;" class="kpi-grid">
                <div v-for="kpi in [
                    { label:'Schools & Colleges', value: area.amenities.schools, icon:'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z M9 22V12h6v10', color:'rgb(198,161,91)', bg:'rgba(106,77,255,.1)' },
                    { label:'Hospitals',           value: area.amenities.hospitals, icon:'M22 12h-4l-3 9L9 3l-3 9H2', color:'hsl(var(--destructive))', bg:'rgba(239,68,68,.1)' },
                    { label:'Metro Stations',      value: area.amenities.metro,    icon:'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5', color:'hsl(var(--info))', bg:'rgba(59,130,246,.1)' },
                    { label:'Parks & Green',       value: area.amenities.parks,    icon:'M12 22V12M12 12C12 12 7 10 7 6a5 5 0 0 1 10 0c0 4-5 6-5 6z', color:'hsl(var(--success))', bg:'rgba(34,197,94,.1)' },
                    { label:'Shopping Malls',      value: area.amenities.malls,    icon:'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0', color:'hsl(var(--warning))', bg:'rgba(245,158,11,.1)' },
                ]" :key="kpi.label"
                    style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:14px; padding:16px 18px;"
                >
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <div style="width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center;" :style="{ background: kpi.bg }">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" :stroke="kpi.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="'<path d=\''+kpi.icon+'\'/>'"></svg>
                        </div>
                    </div>
                    <div style="font-size:24px; font-weight:900; color:hsl(var(--foreground)); line-height:1; margin-bottom:4px;">{{ kpi.value }}</div>
                    <div style="font-size:11.5px; color:hsl(var(--muted-foreground)); font-weight:500;">{{ kpi.label }}</div>
                </div>
            </div>

            <!-- Body: feed + right rail -->
            <div style="display:flex; gap:20px; align-items:flex-start;">

                <!-- Feed -->
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:18px;">

                    <!-- Area Price Growth Chart -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; overflow:hidden;">
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid hsl(var(--muted));">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(106,77,255,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:hsl(var(--foreground));">Area Price Growth (BDT/sqft)</div>
                                <div style="font-size:12px; color:hsl(var(--muted-foreground));">{{ area.label }} · {{ area.area_growth_pct }}% avg annual appreciation</div>
                            </div>
                            <div style="margin-left:auto; background:rgba(34,197,94,.1); color:hsl(var(--success)); font-size:12px; font-weight:700; padding:4px 12px; border-radius:999px;">
                                +{{ area.area_growth_pct }}% p.a.
                            </div>
                        </div>
                        <div style="padding:16px 16px 8px;">
                            <VueApexCharts
                                :key="'price-' + property.areaKey"
                                type="area"
                                :height="260"
                                :options="priceChartOptions"
                                :series="priceSeries"
                            />
                        </div>
                        <div style="margin:0 20px 16px; padding:12px 16px; background:rgba(198,161,91,0.08); border-radius:10px; display:flex; align-items:center; gap:10px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span style="font-size:12.5px; color:hsl(var(--muted-foreground)); font-weight:500;">
                                Projected price/sqft by 2030:
                                <strong style="color:rgb(var(--brand-text));">BDT {{ area.price_projected.filter(Boolean).at(-1)?.toLocaleString() }}/sqft</strong>
                                — based on current infrastructure pipeline and demand trends.
                            </span>
                        </div>
                    </div>

                    <!-- Development Activity chart -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; overflow:hidden;">
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid hsl(var(--muted));">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(245,158,11,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--warning))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="6" height="18" rx="1"/><rect x="9" y="8" width="6" height="13" rx="1"/><rect x="16" y="5" width="6" height="16" rx="1"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:hsl(var(--foreground));">Development Activity</div>
                                <div style="font-size:12px; color:hsl(var(--muted-foreground));">New building permits approved per year — {{ area.label }}</div>
                            </div>
                            <div style="margin-left:auto; background:rgba(245,158,11,.1); color:hsl(var(--warning)); font-size:12px; font-weight:700; padding:4px 12px; border-radius:999px;">
                                {{ area.new_projects_2025 }} projects in 2025
                            </div>
                        </div>
                        <div style="padding:16px 16px 8px;">
                            <VueApexCharts
                                :key="'permits-' + property.areaKey"
                                type="bar"
                                :height="220"
                                :options="permitChartOptions"
                                :series="permitSeries"
                            />
                        </div>
                    </div>

                    <!-- Infrastructure Pipeline -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; overflow:hidden;">
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid hsl(var(--muted));">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(34,197,94,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--success))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:hsl(var(--foreground));">Upcoming Infrastructure Pipeline</div>
                                <div style="font-size:12px; color:hsl(var(--muted-foreground));">Government-approved developments near {{ area.label }}</div>
                            </div>
                        </div>
                        <div style="padding:4px 0 8px;">
                            <div v-for="(item, idx) in area.infrastructure" :key="item.name"
                                style="padding:14px 20px;"
                                :style="idx < area.infrastructure.length - 1 ? 'border-bottom:1px solid hsl(var(--muted))' : ''"
                            >
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:8px; flex-wrap:wrap;">
                                    <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                        <div style="width:8px; height:8px; border-radius:50%; flex-shrink:0;" :style="{ background: item.color }"></div>
                                        <span style="font-size:13.5px; font-weight:600; color:hsl(var(--foreground));">{{ item.name }}</span>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                                        <span style="font-size:11.5px; color:hsl(var(--muted-foreground));">Target: {{ item.year }}</span>
                                        <span style="font-size:11px; font-weight:700; padding:2px 9px; border-radius:999px;"
                                            :style="{ background: infraBadge(item.status).bg, color: infraBadge(item.status).text }">
                                            {{ infraBadge(item.status).label }}
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="flex:1; height:5px; background:hsl(var(--muted)); border-radius:4px; overflow:hidden;">
                                        <div :style="{ width: item.pct + '%', background: item.color }" style="height:100%; border-radius:4px; transition:width .4s;"></div>
                                    </div>
                                    <span style="font-size:11.5px; font-weight:700; color:hsl(var(--muted-foreground)); flex-shrink:0; width:30px; text-align:right;">{{ item.pct }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Rail -->
                <div style="width:300px; flex-shrink:0; display:flex; flex-direction:column; gap:16px;" class="cf-rail">

                    <!-- Investment Climate Score -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:20px 18px;">
                        <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:4px;">Investment Climate</div>
                        <div style="font-size:12px; color:hsl(var(--muted-foreground)); margin-bottom:4px;">{{ area.label }}</div>
                        <VueApexCharts
                            :key="'score-' + property.areaKey"
                            type="radialBar"
                            :height="210"
                            :options="scoreChartOptions"
                            :series="scoreSeries"
                        />
                        <div style="text-align:center; margin-top:-8px; margin-bottom:14px;">
                            <span style="font-size:13px; font-weight:700; padding:4px 14px; border-radius:999px;"
                                :style="{ background: scoreColor + '18', color: scoreColor }">
                                {{ scoreLabel }} Investment Zone
                            </span>
                        </div>
                        <!-- Area snapshot stats -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; padding-top:4px; border-top:1px solid hsl(var(--muted)); margin-top:4px;">
                            <div style="text-align:center; padding:10px 8px;">
                                <div style="font-size:17px; font-weight:800; color:hsl(var(--foreground));">{{ area.area_growth_pct }}%</div>
                                <div style="font-size:11px; color:hsl(var(--muted-foreground)); margin-top:2px;">Price Growth p.a.</div>
                            </div>
                            <div style="text-align:center; padding:10px 8px; border-left:1px solid hsl(var(--muted));">
                                <div style="font-size:17px; font-weight:800; color:hsl(var(--foreground));">{{ area.population_growth }}%</div>
                                <div style="font-size:11px; color:hsl(var(--muted-foreground)); margin-top:2px;">Population Growth</div>
                            </div>
                            <div style="text-align:center; padding:10px 8px; border-top:1px solid hsl(var(--muted));">
                                <div style="font-size:17px; font-weight:800; color:hsl(var(--foreground));">BDT {{ (area.avg_price_sqft / 1000).toFixed(1) }}k</div>
                                <div style="font-size:11px; color:hsl(var(--muted-foreground)); margin-top:2px;">Avg Price/sqft</div>
                            </div>
                            <div style="text-align:center; padding:10px 8px; border-top:1px solid hsl(var(--muted)); border-left:1px solid hsl(var(--muted));">
                                <div style="font-size:17px; font-weight:800; color:hsl(var(--foreground));">{{ area.new_projects_2025 }}</div>
                                <div style="font-size:11px; color:hsl(var(--muted-foreground)); margin-top:2px;">New Projects 2025</div>
                            </div>
                        </div>
                    </div>

                    <!-- Key Highlights -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:16px 18px;">
                        <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:12px;">Area Highlights</div>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div v-for="(h, i) in area.highlights" :key="i"
                                style="display:flex; align-items:flex-start; gap:10px; font-size:12.5px; color:hsl(var(--muted-foreground)); line-height:1.5;">
                                <span style="width:20px; height:20px; border-radius:6px; background:rgba(106,77,255,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                {{ h }}
                            </div>
                        </div>
                    </div>

                    <!-- Download CTA -->
                    <div style="background:linear-gradient(135deg,rgba(198,161,91,0.1),rgba(198,161,91,0.08)); border:1px solid rgba(198,161,91,0.08); border-radius:16px; padding:18px;">
                        <div style="font-size:14px; font-weight:800; color:rgb(var(--hv-gold-deep)); margin-bottom:6px;">Area Development Report</div>
                        <p style="font-size:12px; color:rgb(var(--brand-text)); line-height:1.5; margin:0 0 14px;">Get a comprehensive PDF report on {{ area.label }} infrastructure, pricing trends, and 10-year outlook.</p>
                        <button style="width:100%; padding:10px; border-radius:10px; background:linear-gradient(100deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-deep))); color:hsl(var(--card)); font-size:12.5px; font-weight:700; border:none; cursor:pointer; font-family:inherit; box-shadow:0 4px 12px -4px rgba(81,50,224,.5); display:flex; align-items:center; justify-content:center; gap:7px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--card))" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v13"/><path d="M7 11l5 5 5-5"/><path d="M3 21h18"/></svg>
                            Download Free Report
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<style scoped>
@media (max-width: 1100px) {
    .cf-rail { display: none !important; }
}
@media (max-width: 860px) {
    .kpi-grid { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 500px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
