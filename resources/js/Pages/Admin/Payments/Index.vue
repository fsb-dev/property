<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    buyers: { type: Array, default: () => [] },
});

// ── Sub-nav ─────────────────────────────────────────────────────────────────
const tabs = [
    { key: 'overview',      label: 'Overview' },
    { key: 'invoices',      label: 'Invoices' },
    { key: 'installments',  label: 'Installments' },
    { key: 'plans',         label: 'Payment Plans' },
    { key: 'receipts',      label: 'Receipts' },
    { key: 'reminders',     label: 'Reminders' },
    { key: 'collections',   label: 'Collections' },
    { key: 'refunds',       label: 'Refunds' },
    { key: 'adjustments',   label: 'Adjustments' },
    { key: 'approvals',     label: 'Approvals' },
    { key: 'reports',       label: 'Financial Reports' },
    { key: 'settings',      label: 'Settings' },
];

// ── KPI cards ────────────────────────────────────────────────────────────────
const kpis = [
    { icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/>`, iconBg: 'bg-gold/10', iconColor: 'text-admin-accent', label: 'Total Collection', sub: 'This Month', value: 'BDT 24.75M', trend: '▲ 22%', trendColor: 'text-success', trendSub: 'from last month' },
    { icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4M9 14l2 2 4-4"/>`, iconBg: 'bg-success/10', iconColor: 'text-success', label: 'Total Collected', sub: 'All Time', value: 'BDT 325.75M', trend: 'From', trendColor: 'text-muted-foreground', trendSub: '632 Units' },
    { icon: `<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>`, iconBg: 'bg-warning/10', iconColor: 'text-warning', label: 'Pending Collection', sub: '', value: 'BDT 8.45M', trend: '23', trendColor: 'text-warning', trendSub: 'Installments' },
    { icon: `<circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>`, iconBg: 'bg-destructive/10', iconColor: 'text-destructive', label: 'Overdue Amount', sub: '', value: 'BDT 2.15M', trend: '15', trendColor: 'text-destructive', trendSub: 'Overdue' },
    { icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4M12 13v4M10 15h4"/>`, iconBg: 'bg-warning/10', iconColor: 'text-warning', label: 'Upcoming', sub: '30 Days', value: 'BDT 6.25M', trend: '45', trendColor: 'text-warning', trendSub: 'Installments' },
    { icon: `<path d="M12 2l8 4.6v8.8L12 20l-8-4.6V6.6z"/><path d="M14.5 10l-5 5M9.5 10l5 5"/>`, iconBg: 'bg-chart-4/10', iconColor: 'text-chart-4', label: 'Failed Payments', sub: '', value: '12', trend: '', trendColor: '', trendSub: 'This Month' },
];

// ── Installment table data ───────────────────────────────────────────────────
const instTab = ref('all');
const instTabs = ['all', 'paid', 'pending', 'overdue', 'upcoming', 'failed'];

const STATUS_STYLE = {
    Paid:     { bg: 'bg-success/15',  text: 'text-success' },
    Upcoming: { bg: 'bg-warning/15',  text: 'text-warning' },
    Partial:  { bg: 'bg-info/15',     text: 'text-info' },
    Overdue:  { bg: 'bg-destructive/15', text: 'text-destructive' },
    Pending:  { bg: 'bg-info/15',     text: 'text-info' },
    Failed:   { bg: 'bg-destructive/15', text: 'text-destructive' },
};

const AVATARS = [
    ['bg-info/15','text-info'], ['bg-chart-5/15','text-chart-5'],
    ['bg-success/15','text-success'], ['bg-warning/15','text-warning'],
    ['bg-chart-4/15','text-chart-4'], ['bg-chart-6/15','text-chart-6'],
    ['bg-gold/15','text-brand'], ['bg-info/15','text-info'],
];

const installments = [
    { buyer: 'Rahim Uddin',     unit: 'A-1205', project: 'Lake View Residence', block: 'Block A', inst: '5 / 20', due: '15 May 2026', amount: '1,250,000', paid: '1,250,000', status: 'Paid',     method: 'bKash' },
    { buyer: 'Nusrat Jahan',    unit: 'B-804',  project: 'Green Park Heights',  block: 'Block B', inst: '3 / 24', due: '20 May 2026', amount: '840,000',   paid: '—',         status: 'Upcoming', method: '—' },
    { buyer: 'Aminul Islam',    unit: 'C-302',  project: 'Skyline Towers',      block: 'Block C', inst: '4 / 18', due: '14 May 2026', amount: '720,000',   paid: '720,000',   status: 'Paid',     method: 'Nagad' },
    { buyer: 'Fatema Akter',    unit: 'A-903',  project: 'Lake View Residence', block: 'Block A', inst: '2 / 20', due: '18 May 2026', amount: '1,680,000', paid: '—',         status: 'Upcoming', method: '—' },
    { buyer: 'Sakib Ahmed',     unit: 'CM-12',  project: 'City Central Mall',   block: 'Commercial', inst: '6 / 24', due: '10 May 2026', amount: '2,100,000', paid: '1,500,000', status: 'Partial',  method: 'Bank Transfer' },
    { buyer: 'Shariful Islam',  unit: 'B-1102', project: 'Green Park Heights',  block: 'Block B', inst: '3 / 24', due: '05 May 2026', amount: '850,000',   paid: '—',         status: 'Overdue',  method: '—' },
    { buyer: 'Tanzil Hasan',    unit: 'C-1201', project: 'Skyline Towers',      block: 'Block C', inst: '2 / 18', due: '02 May 2026', amount: '650,000',   paid: '—',         status: 'Overdue',  method: '—' },
    { buyer: 'Moumita Saha',    unit: 'D-501',  project: 'Riverside Apartments',block: 'Block D', inst: '1 / 24', due: '22 May 2026', amount: '1,100,000', paid: '—',         status: 'Upcoming', method: '—' },
].map((r, i) => ({ ...r, av: AVATARS[i % AVATARS.length], initials: r.buyer.split(' ').map(w => w[0]).slice(0,2).join('') }));

const filteredInstallments = (tab) => {
    if (tab === 'all') return installments;
    return installments.filter(r => r.status.toLowerCase() === tab);
};

// ── Recent payments ──────────────────────────────────────────────────────────
const recentPayments = [
    { text: 'Rahim Uddin paid BDT 1,250,000 for Unit A-1205', time: '15 May 2026, 10:30 AM' },
    { text: 'Aminul Islam paid BDT 720,000 for Unit C-302',    time: '15 May 2026, 09:15 AM' },
    { text: 'Fatema Akter paid BDT 1,680,000 for Unit A-903',  time: '14 May 2026, 04:45 PM' },
    { text: 'Jannatul Ferdaus paid BDT 2,100,000 for Unit CM-12', time: '14 May 2026, 11:30 AM' },
];

// ── Due overview rows ────────────────────────────────────────────────────────
const dueRows = [
    { label: 'Overdue (0-30 Days)',    inst: 15, buyers: 12, amount: '1,250,000', color: 'text-destructive' },
    { label: 'Overdue (31-60 Days)',   inst: 8,  buyers: 7,  amount: '680,000',   color: 'text-destructive' },
    { label: 'Overdue (61-90 Days)',   inst: 4,  buyers: 4,  amount: '220,000',   color: 'text-destructive' },
    { label: 'Overdue (90+ Days)',     inst: 3,  buyers: 3,  amount: '150,000',   color: 'text-destructive' },
    { label: 'Due in Next 7 Days',     inst: 18, buyers: 15, amount: '1,850,000', color: 'text-warning' },
    { label: 'Due in Next 30 Days',    inst: 45, buyers: 32, amount: '6,250,000', color: 'text-foreground' },
];

// ── Quick actions ────────────────────────────────────────────────────────────
const quickActions = [
    { label: 'Record Payment',         color: 'border-admin-accent/30 hover:border-admin-accent hover:bg-gold/10', iconBg: 'bg-gold/10', iconColor: 'text-admin-accent', icon: `<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20M12 14h0"/>`, href: 'admin.payments.record' },
    { label: 'Create Payment Plan',    color: 'border-info/30 hover:border-info hover:bg-info/10',  iconBg: 'bg-info/10',  iconColor: 'text-info',  icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4M12 13v4M10 15h4"/>`, href: null },
    { label: 'Send Reminder',          color: 'border-warning/30 hover:border-warning hover:bg-warning/10', iconBg: 'bg-warning/10', iconColor: 'text-warning', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>`, href: null },
    { label: 'Upload Receipt',         color: 'border-success/30 hover:border-success hover:bg-success/10', iconBg: 'bg-success/10', iconColor: 'text-success', icon: `<path d="M12 15V3M8 7l4-4 4 4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>`, href: null },
    { label: 'Generate Statement',     color: 'border-chart-6/30 hover:border-chart-6 hover:bg-chart-6/10',   iconBg: 'bg-chart-6/10',   iconColor: 'text-chart-6',   icon: `<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>`, href: null },
    { label: 'Payment Reports',        color: 'border-destructive/30 hover:border-destructive hover:bg-destructive/10',   iconBg: 'bg-destructive/10',   iconColor: 'text-destructive',   icon: `<path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/>`, href: null },
];
</script>

<template>
    <Head title="Payments & Installments" />
    <AdminLayout>
        <!-- Page header -->
        <div class="mb-5 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-foreground">Payments &amp; Installments</h1>
                <p class="mt-1 text-sm text-muted-foreground">Track all payments, installments and outstanding balances in real time.</p>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.payments.record')"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-on-gold bg-gold-gradient shadow-[0_8px_20px_rgba(198,161,91,0.30)] transition-all hover:-translate-y-0.5 hover:shadow-gold-glow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Record Payment
                </Link>
                <button class="inline-flex items-center gap-2 rounded-xl border border-border bg-muted px-4 py-2.5 text-sm font-semibold text-foreground shadow-sm hover:bg-muted/70">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg>
                    Payment Settings
                </button>
            </div>
        </div>

        <!-- Sub-nav -->
        <nav class="mb-5 flex flex-wrap gap-2">
            <!-- Overview stays on this page -->
            <button
                :class="[
                    'rounded-xl px-3.5 py-2 text-sm font-semibold transition-colors',
                    'bg-admin-accent text-on-gold'
                ]">
                Overview
            </button>
            <!-- All other tabs navigate to the Records page -->
            <template v-for="tab in tabs.filter(t => t.key !== 'overview')" :key="tab.key">
                <button
                    @click="router.visit(route('admin.payments.records') + '?view=' + tab.key)"
                    class="rounded-xl px-3.5 py-2 text-sm font-semibold transition-colors bg-muted border border-border text-foreground hover:bg-muted/70">
                    {{ tab.label }}
                </button>
            </template>
        </nav>

        <!-- KPI row -->
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            <div v-for="kpi in kpis" :key="kpi.label"
                class="rounded-2xl border border-border bg-card shadow-sm p-5 hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="mb-3 flex items-center gap-2.5">
                    <div :class="['flex h-10 w-10 items-center justify-center rounded-xl flex-shrink-0', kpi.iconBg, kpi.iconColor]">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="kpi.icon" />
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">{{ kpi.label }} <span v-if="kpi.sub" class="text-muted-foreground">({{ kpi.sub }})</span></span>
                </div>
                <div class="text-2xl font-extrabold tracking-tight">{{ kpi.value }}</div>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    <span :class="kpi.trendColor" class="font-bold">{{ kpi.trend }}</span>
                    <span class="text-muted-foreground">{{ kpi.trendSub }}</span>
                </div>
            </div>
        </div>

        <!-- Charts row -->
        <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Collection Overview donut -->
            <div class="rounded-2xl border border-border bg-card shadow-sm p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-foreground">Collection Overview</h3>
                    <span class="text-xs font-bold text-admin-accent cursor-pointer hover:opacity-80">Analytics →</span>
                </div>
                <div class="flex items-center gap-5">
                    <div class="relative h-36 w-36 flex-shrink-0">
                        <svg width="144" height="144" viewBox="0 0 140 140" style="transform:rotate(-90deg)">
                            <circle cx="70" cy="70" r="58" fill="none" class="stroke-muted" stroke-width="18"/>
                            <circle cx="70" cy="70" r="58" fill="none" stroke="#34D399" stroke-width="18" stroke-dasharray="217.2 147.2" stroke-dashoffset="0"/>
                            <circle cx="70" cy="70" r="58" fill="none" stroke="#FBBF24" stroke-width="18" stroke-dasharray="56.1 308.3" stroke-dashoffset="-217.2"/>
                            <circle cx="70" cy="70" r="58" fill="none" stroke="#60A5FA" stroke-width="18" stroke-dasharray="70.1 294.3" stroke-dashoffset="-273.3"/>
                            <circle cx="70" cy="70" r="58" fill="none" stroke="#F87171" stroke-width="18" stroke-dasharray="17.5 346.9" stroke-dashoffset="-343.4"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-lg font-extrabold leading-tight">BDT 24.75M</div>
                            <div class="text-xs text-muted-foreground">This Month</div>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-2.5 text-sm">
                        <div v-for="[color, label, val, pct] in [
                            ['#34D399','Collected','BDT 24.75M','62%'],
                            ['#FBBF24','Upcoming','BDT 6.25M','16%'],
                            ['#60A5FA','Pending','BDT 8.45M','20%'],
                            ['#F87171','Overdue','BDT 2.15M','5%'],
                        ]" :key="label">
                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                <span class="h-2 w-2 rounded-full flex-shrink-0" :style="`background:${color}`" />{{ label }}
                            </div>
                            <div class="ml-4 text-xs font-bold text-foreground">{{ val }} <span class="font-normal text-muted-foreground">({{ pct }})</span></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 border-t border-border pt-3 text-center text-sm font-semibold text-admin-accent cursor-pointer">View Collection Report →</div>
            </div>

            <!-- Collection Trend chart -->
            <div class="rounded-2xl border border-border bg-card shadow-sm p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-foreground">Collection Trend</h3>
                    <div class="flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-foreground cursor-pointer">
                        This Year
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                <svg width="100%" viewBox="0 0 600 220" preserveAspectRatio="none" style="display:block;">
                    <defs>
                        <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C6A15B" stop-opacity="0.18"/>
                            <stop offset="100%" stop-color="#C6A15B" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <g class="stroke-chart-grid/[0.08]" stroke-width="1">
                        <line x1="60" y1="20" x2="590" y2="20"/><line x1="60" y1="60" x2="590" y2="60"/>
                        <line x1="60" y1="100" x2="590" y2="100"/><line x1="60" y1="140" x2="590" y2="140"/>
                        <line x1="60" y1="180" x2="590" y2="180"/>
                    </g>
                    <g class="fill-muted-foreground" font-size="10" font-family="Inter" text-anchor="end">
                        <text x="52" y="24">BDT 40M</text><text x="52" y="64">BDT 30M</text>
                        <text x="52" y="104">BDT 20M</text><text x="52" y="144">BDT 10M</text>
                        <text x="52" y="184">BDT 0</text>
                    </g>
                    <polygon points="60,140 166,125 272,112 378,100 484,90 590,80 590,180 60,180" fill="url(#areaGrad)"/>
                    <polyline points="60,140 166,125 272,112 378,100 484,90 590,80" fill="none" stroke="#C6A15B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <g fill="#fff" stroke="#C6A15B" stroke-width="2.5">
                        <circle cx="60" cy="140" r="4"/><circle cx="166" cy="125" r="4"/>
                        <circle cx="272" cy="112" r="4"/><circle cx="378" cy="100" r="4"/><circle cx="484" cy="90" r="4"/>
                    </g>
                    <circle cx="590" cy="80" r="6" fill="#C6A15B" stroke="#fff" stroke-width="2.5"/>
                    <g class="fill-muted-foreground" font-size="10" font-family="Inter" text-anchor="middle">
                        <text x="60" y="200">Dec 2025</text><text x="166" y="200">Jan 2026</text>
                        <text x="272" y="200">Feb 2026</text><text x="378" y="200">Mar 2026</text>
                        <text x="484" y="200">Apr 2026</text><text x="590" y="200">May 2026</text>
                    </g>
                </svg>
                <div class="mt-3 text-center text-sm font-semibold text-admin-accent cursor-pointer">View Financial Report →</div>
            </div>
        </div>

        <!-- Installments table -->
        <div class="mb-6 rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
            <!-- Table tabs + actions -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-0">
                <div class="flex items-center">
                    <button v-for="t in instTabs" :key="t"
                        @click="instTab = t"
                        :class="[
                            'px-4 py-3.5 text-sm font-medium capitalize transition-colors border-b-2',
                            instTab === t
                                ? 'border-admin-accent text-admin-accent font-semibold'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        ]">
                        {{ t === 'all' ? 'All Installments' : t.charAt(0).toUpperCase() + t.slice(1) }}
                    </button>
                </div>
                <div class="flex items-center gap-2 py-2">
                    <button class="flex items-center gap-1.5 rounded-xl border border-border bg-muted px-3 py-2 text-xs font-semibold text-foreground hover:bg-muted">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        Filters
                    </button>
                    <button class="flex items-center gap-1.5 rounded-xl border border-border bg-muted px-3 py-2 text-xs font-semibold text-foreground hover:bg-muted">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                        Export
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] border-collapse">
                    <thead>
                        <tr>
                            <th v-for="h in ['Buyer / Unit','Project','Installment','Due Date','Amount (BDT)','Paid Amount','Status','Method','']"
                                :key="h"
                                class="px-4 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                                {{ h }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in filteredInstallments(instTab)" :key="row.buyer + row.unit"
                            @click="router.visit(route('admin.payments.record') + '?buyer=' + encodeURIComponent(row.buyer) + '&unit=' + encodeURIComponent(row.unit) + '&project=' + encodeURIComponent(row.project) + '&amount=' + row.amount.replace(/,/g,'') + '&status=' + row.status + '&method=' + encodeURIComponent(row.method === '—' ? '' : row.method))"
                            class="cursor-pointer transition-colors hover:bg-muted">
                            <td class="border-t border-border px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div :class="['flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold', row.av[0], row.av[1]]">
                                        {{ row.initials }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-foreground">{{ row.buyer }}</div>
                                        <div class="text-xs text-muted-foreground">{{ row.unit }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="border-t border-border px-4 py-3">
                                <div class="text-sm font-semibold text-foreground">{{ row.project }}</div>
                                <div class="text-xs text-muted-foreground">{{ row.block }}</div>
                            </td>
                            <td class="border-t border-border px-4 py-3 text-sm text-foreground">Installment {{ row.inst }}</td>
                            <td class="border-t border-border px-4 py-3 text-sm text-foreground whitespace-nowrap">{{ row.due }}</td>
                            <td class="border-t border-border px-4 py-3 text-sm font-bold text-foreground">{{ row.amount }}</td>
                            <td class="border-t border-border px-4 py-3 text-sm font-bold"
                                :class="row.paid === '—' ? 'text-muted-foreground/50' : row.status === 'Paid' ? 'text-success' : 'text-destructive'">
                                {{ row.paid }}
                            </td>
                            <td class="border-t border-border px-4 py-3">
                                <span :class="['inline-block rounded-full px-3 py-1 text-[11px] font-bold whitespace-nowrap',
                                    STATUS_STYLE[row.status]?.bg ?? 'bg-muted',
                                    STATUS_STYLE[row.status]?.text ?? 'text-muted-foreground']">
                                    {{ row.status }}
                                </span>
                            </td>
                            <td class="border-t border-border px-4 py-3 text-sm" :class="row.method === '—' ? 'text-muted-foreground/50' : 'text-foreground'">{{ row.method }}</td>
                            <td class="border-t border-border px-4 py-3">
                                <button class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="flex items-center justify-between border-t border-border px-5 py-3.5">
                <span class="text-xs text-muted-foreground">Showing 1 to 8 of 780 installments</span>
                <div class="flex items-center gap-1.5">
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-sm text-muted-foreground hover:bg-muted">‹</button>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-admin-accent text-sm font-semibold text-on-gold">1</button>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-sm text-foreground hover:bg-muted">2</button>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-sm text-foreground hover:bg-muted">3</button>
                    <span class="w-6 text-center text-muted-foreground">…</span>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-sm text-foreground hover:bg-muted">98</button>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-sm text-muted-foreground hover:bg-muted">›</button>
                </div>
            </div>
        </div>

        <!-- Bottom 3-column grid -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            <!-- Payment Plan Summary -->
            <div class="rounded-2xl border border-border bg-card shadow-sm p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-foreground">Payment Plan Summary</h3>
                    <span class="text-xs font-bold text-admin-accent cursor-pointer">View All</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex flex-1 flex-col gap-3">
                        <div v-for="[color, bg, label, count] in [
                            ['#60A5FA','bg-info/15 text-info','Active Payment Plans',780],
                            ['#34D399','bg-success/15 text-success','Completed Plans',210],
                            ['#FBBF24','bg-warning/15 text-warning','Paused Plans',18],
                            ['#F87171','bg-destructive/15 text-destructive','Cancelled Plans',12],
                        ]" :key="label" class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs text-foreground">
                                <span :class="['flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold', bg]">✓</span>
                                {{ label }}
                            </span>
                            <span class="text-sm font-bold text-foreground">{{ count }}</span>
                        </div>
                    </div>
                    <div class="relative h-24 w-24 flex-shrink-0">
                        <svg width="96" height="96" viewBox="0 0 110 110" style="transform:rotate(-90deg)">
                            <circle cx="55" cy="55" r="46" fill="none" class="stroke-muted" stroke-width="12"/>
                            <circle cx="55" cy="55" r="46" fill="none" stroke="#60A5FA" stroke-width="12" stroke-linecap="round" stroke-dasharray="225.4 289" stroke-dashoffset="0"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-lg font-extrabold">78%</div>
                            <div class="text-[10px] text-muted-foreground">Active</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Installments Due Overview -->
            <div class="rounded-2xl border border-border bg-card shadow-sm p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-bold text-foreground">Installments Due Overview</h3>
                    <span class="text-xs font-bold text-admin-accent cursor-pointer">View All</span>
                </div>
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="pb-2 text-left text-[10.5px] font-semibold uppercase tracking-wide text-muted-foreground">Due Date</th>
                            <th class="pb-2 text-center text-[10.5px] font-semibold uppercase tracking-wide text-muted-foreground">Inst.</th>
                            <th class="pb-2 text-center text-[10.5px] font-semibold uppercase tracking-wide text-muted-foreground">Buyers</th>
                            <th class="pb-2 text-right text-[10.5px] font-semibold uppercase tracking-wide text-muted-foreground">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in dueRows" :key="row.label" class="border-t border-border">
                            <td :class="['py-2.5 text-xs font-semibold', row.color]">{{ row.label }}</td>
                            <td class="py-2.5 text-center text-xs font-semibold text-foreground">{{ row.inst }}</td>
                            <td class="py-2.5 text-center text-xs text-foreground">{{ row.buyers }}</td>
                            <td class="py-2.5 text-right text-xs font-bold text-foreground">{{ row.amount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-border bg-card shadow-sm p-5">
                <h3 class="mb-4 text-base font-bold text-foreground">Quick Actions</h3>
                <div class="grid grid-cols-3 gap-2.5">
                    <component :is="qa.href ? Link : 'button'"
                        v-for="qa in quickActions" :key="qa.label"
                        :href="qa.href ? route(qa.href) : undefined"
                        :class="['flex flex-col items-center gap-2 rounded-2xl border p-3.5 text-center transition-all cursor-pointer', qa.color]">
                        <div :class="['flex h-9 w-9 items-center justify-center rounded-xl', qa.iconBg, qa.iconColor]">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" v-html="qa.icon" />
                        </div>
                        <span class="text-[11px] font-semibold text-foreground leading-tight">{{ qa.label }}</span>
                    </component>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="rounded-2xl border border-border bg-card shadow-sm p-5 lg:col-span-3">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-bold text-foreground">Recent Payments</h3>
                    <span class="text-xs font-bold text-admin-accent cursor-pointer">View All</span>
                </div>
                <div class="divide-y divide-border">
                    <div v-for="p in recentPayments" :key="p.text"
                        class="flex items-center gap-3 py-2.5 cursor-pointer rounded-xl px-2 transition-colors hover:bg-muted">
                        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-success/10 text-success">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-foreground" v-html="p.text.replace(/BDT [\d,]+/, m => `<b>${m}</b>`)" />
                            <div class="mt-0.5 text-xs text-muted-foreground">{{ p.time }}</div>
                        </div>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 cursor-pointer text-muted-foreground"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
