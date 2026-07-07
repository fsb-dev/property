<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { getOverride } from '@/stores/payments.js';

const props = defineProps({
    initialView: { type: String, default: 'invoices' },
});

// ── View definitions ──────────────────────────────────────────────────────────
const views = [
    { key: 'invoices',      label: 'Invoices' },
    { key: 'installments',  label: 'Installments' },
    { key: 'receipts',      label: 'Receipts' },
    { key: 'reminders',     label: 'Reminders' },
    { key: 'collections',   label: 'Collections' },
    { key: 'refunds',       label: 'Refunds' },
    { key: 'adjustments',   label: 'Adjustments' },
    { key: 'plans',         label: 'Payment Plans' },
    { key: 'reports',       label: 'Financial Reports' },
];

const activeView = ref(props.initialView);

// ── View meta: title, subtitle, 4 KPI cards, prefix, statuses ────────────────
const META = {
    invoices: {
        title: 'Invoices',
        subtitle: 'All invoices issued to buyers across projects',
        prefix: 'INV',
        statuses: ['All', 'Paid', 'Pending', 'Overdue'],
        kpis: [
            { label: 'Total Invoiced',   value: 'BDT 312M',  sub: '1,240 invoices',        iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5"/>` },
            { label: 'Paid',             value: 'BDT 186M',  sub: '62% collected',          iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13l2 2 4-4"/>` },
            { label: 'Outstanding',      value: 'BDT 126M',  sub: '506 invoices',           iconBg: 'bg-amber-50', iconColor: 'text-amber-600', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>` },
            { label: 'Overdue',          value: 'BDT 4.2M',  sub: '34 invoices',            iconBg: 'bg-red-50',   iconColor: 'text-red-600',   icon: `<circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>` },
        ],
    },
    installments: {
        title: 'Installments',
        subtitle: 'Monthly installment schedule and collection status',
        prefix: 'INST',
        statuses: ['All', 'Paid', 'Pending', 'Overdue', 'Upcoming'],
        kpis: [
            { label: 'Due This Month',   value: 'BDT 18.4M', sub: '74 installments',        iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/>` },
            { label: 'Collected',        value: 'BDT 14.2M', sub: '77% of monthly target',  iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'Overdue',          value: 'BDT 4.2M',  sub: '23 installments',        iconBg: 'bg-red-50',   iconColor: 'text-red-600',   icon: `<circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>` },
            { label: 'Next 30 Days',     value: 'BDT 22.1M', sub: '89 installments',        iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4M12 13v4M10 15h4"/>` },
        ],
    },
    receipts: {
        title: 'Receipts',
        subtitle: 'Payment receipts issued after confirmed payments',
        prefix: 'REC',
        statuses: ['All', 'Issued', 'Pending'],
        kpis: [
            { label: 'Total Receipts',   value: '864',        sub: 'All time',               iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<path d="M6 2H3a1 1 0 00-1 1v18a1 1 0 001 1h3M18 2h3a1 1 0 011 1v18a1 1 0 01-1 1h-3M6 2v20M18 2v20"/>` },
            { label: 'Issued Today',     value: '12',         sub: 'Receipts generated',     iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'Total Value',      value: 'BDT 186M',   sub: 'Receipted amount',        iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/>` },
            { label: 'Pending Issue',    value: '28',         sub: 'Awaiting generation',    iconBg: 'bg-amber-50', iconColor: 'text-amber-600', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>` },
        ],
    },
    reminders: {
        title: 'Reminders',
        subtitle: 'Payment reminders sent to buyers with overdue or upcoming payments',
        prefix: 'REM',
        statuses: ['All', 'Sent', 'Pending', 'Failed'],
        kpis: [
            { label: 'Total Sent',       value: '1,240',      sub: 'All time',               iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013 11.5a19.79 19.79 0 01-3.07-8.67A2 2 0 011.91 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>` },
            { label: 'Sent Today',       value: '48',         sub: 'Auto-triggered',         iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'Pending Send',     value: '23',         sub: 'Queued reminders',       iconBg: 'bg-amber-50', iconColor: 'text-amber-600', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>` },
            { label: 'Response Rate',    value: '68%',        sub: 'Payment within 3 days',  iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/>` },
        ],
    },
    collections: {
        title: 'Collections',
        subtitle: 'Active collection cases for overdue buyers',
        prefix: 'COL',
        statuses: ['All', 'Pending', 'Overdue', 'Paid', 'Escalated'],
        kpis: [
            { label: 'Open Cases',       value: '34',         sub: 'Active collection',      iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>` },
            { label: 'Recovered',        value: 'BDT 12M',   sub: 'This quarter',           iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'At Risk',          value: 'BDT 8.3M',  sub: '18 accounts',            iconBg: 'bg-red-50',   iconColor: 'text-red-600',   icon: `<circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>` },
            { label: 'Recovery Rate',    value: '82%',        sub: 'vs 78% last quarter',    iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/>` },
        ],
    },
    refunds: {
        title: 'Refunds',
        subtitle: 'Buyer refund requests and processing status',
        prefix: 'REF',
        statuses: ['All', 'Pending', 'Approved', 'Paid', 'Rejected'],
        kpis: [
            { label: 'Total Requests',   value: '18',         sub: 'This year',              iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<path d="M3 12a9 9 0 109 9M3 12l4-4M3 12l4 4"/>` },
            { label: 'Approved',         value: '11',         sub: 'BDT 3.2M total',         iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'Pending',          value: '5',          sub: 'Under review',           iconBg: 'bg-amber-50', iconColor: 'text-amber-600', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>` },
            { label: 'Refunded Amount',  value: 'BDT 2.8M',  sub: 'Disbursed this year',    iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/>` },
        ],
    },
    adjustments: {
        title: 'Adjustments',
        subtitle: 'Manual payment adjustments and credit notes',
        prefix: 'ADJ',
        statuses: ['All', 'Approved', 'Pending', 'Rejected'],
        kpis: [
            { label: 'Total Adjustments','value': '42',       sub: 'All time',               iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>` },
            { label: 'Approved',         value: '36',         sub: 'BDT 1.4M adjusted',     iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'Pending Review',   value: '6',          sub: 'Awaiting approval',      iconBg: 'bg-amber-50', iconColor: 'text-amber-600', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>` },
            { label: 'Total Value',      value: 'BDT 1.4M',  sub: 'Net adjustments',        iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/>` },
        ],
    },
    plans: {
        title: 'Payment Plans',
        subtitle: 'Installment plans set up for buyers across all projects',
        prefix: 'PLN',
        statuses: ['All', 'Active', 'Completed', 'Paused', 'Cancelled'],
        kpis: [
            { label: 'Active Plans',     value: '780',        sub: '78% of all plans',       iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/>` },
            { label: 'Completed Plans',  value: '210',        sub: 'Fully paid off',          iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'Paused Plans',     value: '18',         sub: 'On hold',                 iconBg: 'bg-amber-50', iconColor: 'text-amber-600', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>` },
            { label: 'Cancelled Plans',  value: '12',         sub: 'Terminated',              iconBg: 'bg-red-50',   iconColor: 'text-red-600',   icon: `<circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>` },
        ],
    },
    reports: {
        title: 'Financial Reports',
        subtitle: 'Monthly collection statements and financial summaries across projects',
        prefix: 'RPT',
        statuses: ['All', 'Generated', 'Pending'],
        kpis: [
            { label: 'Total Collected',  value: 'BDT 325.75M', sub: 'All time',              iconBg: 'bg-[#F1ECFF]', iconColor: 'text-admin-accent', icon: `<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/>` },
            { label: 'This Month',       value: 'BDT 24.75M', sub: '▲ 22% from last month',  iconBg: 'bg-green-50', iconColor: 'text-green-600', icon: `<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>` },
            { label: 'Outstanding',      value: 'BDT 8.45M',  sub: '23 installments pending', iconBg: 'bg-amber-50', iconColor: 'text-amber-600', icon: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>` },
            { label: 'Reports Generated','value': '48',        sub: 'This year',              iconBg: 'bg-blue-50',  iconColor: 'text-blue-600',  icon: `<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5"/>` },
        ],
    },
};

// ── Dummy row data (shared pool, adapted per view) ────────────────────────────
const BUYERS = [
    { name: 'Rahim Uddin',    unit: 'A-1205', project: 'Lake View Residence',  amount: 1250000 },
    { name: 'Nusrat Jahan',   unit: 'B-804',  project: 'Green Park Heights',   amount: 840000  },
    { name: 'Aminul Islam',   unit: 'C-302',  project: 'Skyline Towers',       amount: 720000  },
    { name: 'Fatema Akter',   unit: 'A-903',  project: 'Lake View Residence',  amount: 1680000 },
    { name: 'Sakib Ahmed',    unit: 'CM-12',  project: 'City Central Mall',    amount: 2100000 },
    { name: 'Shariful Islam', unit: 'B-1102', project: 'Green Park Heights',   amount: 850000  },
    { name: 'Tanzil Hasan',   unit: 'C-1201', project: 'Skyline Towers',       amount: 650000  },
    { name: 'Moumita Saha',   unit: 'D-501',  project: 'Riverside Apartments', amount: 1100000 },
    { name: 'Jannatul Ferdaus',unit:'E-201',  project: 'River View Heights',   amount: 980000  },
    { name: 'Karim Molla',    unit: 'F-102',  project: 'Green Park Heights',   amount: 560000  },
];

const STATUS_POOL = {
    invoices:     ['Paid','Paid','Pending','Overdue','Paid','Overdue','Pending','Paid','Pending','Paid'],
    installments: ['Paid','Upcoming','Paid','Upcoming','Partial','Overdue','Overdue','Upcoming','Paid','Pending'],
    receipts:     ['Issued','Issued','Pending','Issued','Issued','Issued','Pending','Issued','Issued','Issued'],
    reminders:    ['Sent','Pending','Sent','Sent','Failed','Sent','Pending','Sent','Sent','Failed'],
    collections:  ['Paid','Pending','Escalated','Pending','Paid','Overdue','Overdue','Pending','Paid','Escalated'],
    refunds:      ['Approved','Pending','Paid','Rejected','Pending','Approved','Paid','Pending','Approved','Rejected'],
    adjustments:  ['Approved','Pending','Approved','Approved','Pending','Rejected','Approved','Approved','Pending','Approved'],
    reports:      ['Generated','Generated','Pending','Generated','Generated','Generated','Pending','Generated','Generated','Generated'],
    plans:        ['Active','Active','Completed','Active','Paused','Active','Completed','Cancelled','Active','Completed'],
};

const METHODS = ['Bank Transfer','bKash','Nagad','Cash','Cheque','Credit Card','Online Banking'];

const DATES = [
    '05 May 2026','08 May 2026','10 May 2026','12 May 2026','14 May 2026',
    '15 May 2026','18 May 2026','20 May 2026','22 May 2026','25 May 2026',
];

function padNum(n) { return String(n).padStart(4, '0'); }
function fmtAmt(n) { return 'BDT ' + n.toLocaleString(); }

function buildRows(view) {
    const prefix = META[view]?.prefix ?? 'REF';
    const statuses = STATUS_POOL[view] ?? [];
    return BUYERS.map((b, i) => {
        const ref = `${prefix}-2026-${padNum(418 + i)}`;
        const override = getOverride(ref);
        const base = {
            ref,
            buyer:     b.name,
            unit:      b.unit,
            project:   b.project,
            date:      DATES[i],
            amount:    fmtAmt(b.amount),
            rawAmount: b.amount,
            status:    statuses[i] ?? 'Pending',
            method:    view === 'reminders' || view === 'adjustments' ? '—' : METHODS[i % METHODS.length],
        };
        if (!override) return base;
        return {
            ...base,
            buyer:   override.buyer   ?? base.buyer,
            unit:    override.unit    ?? base.unit,
            project: override.project ?? base.project,
            amount:  override.amount  ?? base.amount,
            method:  override.method  ?? base.method,
            status:  override.status  ?? base.status,
        };
    });
}

// ── Status badge styles ───────────────────────────────────────────────────────
const STATUS_STYLE = {
    Paid:      { bg: 'bg-green-50',   text: 'text-green-700' },
    Approved:  { bg: 'bg-green-50',   text: 'text-green-700' },
    Issued:    { bg: 'bg-green-50',   text: 'text-green-700' },
    Generated: { bg: 'bg-green-50',   text: 'text-green-700' },
    Active:    { bg: 'bg-green-50',   text: 'text-green-700' },
    Upcoming:  { bg: 'bg-blue-50',    text: 'text-blue-700' },
    Sent:      { bg: 'bg-blue-50',    text: 'text-blue-700' },
    Completed: { bg: 'bg-blue-50',    text: 'text-blue-700' },
    Partial:   { bg: 'bg-amber-50',   text: 'text-amber-700' },
    Pending:   { bg: 'bg-yellow-50',  text: 'text-yellow-700' },
    Paused:    { bg: 'bg-amber-50',   text: 'text-amber-700' },
    Overdue:   { bg: 'bg-red-50',     text: 'text-red-700' },
    Escalated: { bg: 'bg-red-100',    text: 'text-red-800' },
    Failed:    { bg: 'bg-slate-100',  text: 'text-slate-500' },
    Rejected:  { bg: 'bg-rose-50',    text: 'text-rose-700' },
    Cancelled: { bg: 'bg-rose-50',    text: 'text-rose-700' },
};

// ── Search + filter ───────────────────────────────────────────────────────────
const search    = ref('');
const statusFilter = ref('All');

const currentMeta = computed(() => META[activeView.value] ?? META.invoices);

const allRows = computed(() => buildRows(activeView.value));

const rows = computed(() => {
    let r = allRows.value;
    if (statusFilter.value !== 'All') r = r.filter(x => x.status === statusFilter.value);
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        r = r.filter(x =>
            x.ref.toLowerCase().includes(q) ||
            x.buyer.toLowerCase().includes(q) ||
            x.project.toLowerCase().includes(q) ||
            x.unit.toLowerCase().includes(q) ||
            x.status.toLowerCase().includes(q)
        );
    }
    return r;
});

// ── Switch view ───────────────────────────────────────────────────────────────
function switchView(key) {
    activeView.value = key;
    statusFilter.value = 'All';
    search.value = '';
}

// ── Row click → go to Record form with prefill ────────────────────────────────
function editRow(row) {
    const params = new URLSearchParams({
        ref:     row.ref,
        buyer:   row.buyer,
        unit:    row.unit,
        project: row.project,
        amount:  String(row.rawAmount),
        ptype:   currentMeta.value.prefix === 'INST' ? 'EMI' : '',
        method:  row.method === '—' ? '' : row.method,
        status:  row.status,
    });
    router.visit(route('admin.payments.record') + '?' + params.toString());
}

// Avatar color pool
const AVATARS = [
    ['bg-blue-100','text-blue-700'], ['bg-pink-100','text-pink-700'],
    ['bg-green-100','text-green-700'], ['bg-amber-100','text-amber-700'],
    ['bg-purple-100','text-purple-700'], ['bg-teal-100','text-teal-700'],
    ['bg-indigo-100','text-indigo-700'], ['bg-sky-100','text-sky-700'],
    ['bg-rose-100','text-rose-700'], ['bg-orange-100','text-orange-700'],
];
function avatarFor(i) { return AVATARS[i % AVATARS.length]; }
function initials(name) { return name.split(' ').map(w => w[0]).slice(0,2).join(''); }
</script>

<template>
    <Head :title="currentMeta.title" />
    <AdminLayout>

        <!-- Page header -->
        <div class="mb-5 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <div class="flex items-center gap-2 text-xs text-muted-foreground mb-1">
                    <Link :href="route('admin.payments.index')" class="hover:text-foreground transition-colors">Payments</Link>
                    <span>›</span>
                    <span class="text-foreground font-semibold">{{ currentMeta.title }}</span>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-foreground">{{ currentMeta.title }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ currentMeta.subtitle }}</p>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.payments.record')"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-transform hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,#5B3DF5,#7C5CFF); box-shadow:0 8px 20px rgba(91,61,245,0.35);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Record Payment
                </Link>
                <button class="inline-flex items-center gap-2 rounded-xl border border-border bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-foreground shadow-sm hover:bg-slate-50">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                    Export
                </button>
            </div>
        </div>

        <!-- View tabs -->
        <nav class="mb-5 flex flex-wrap gap-2">
            <Link :href="route('admin.payments.index')"
                class="rounded-xl px-3.5 py-2 text-sm font-semibold transition-colors bg-white dark:bg-slate-800 border border-border text-foreground hover:bg-slate-50">
                ← Overview
            </Link>
            <button v-for="v in views" :key="v.key"
                @click="switchView(v.key)"
                :class="[
                    'rounded-xl px-3.5 py-2 text-sm font-semibold transition-colors',
                    activeView === v.key
                        ? 'bg-admin-accent text-white'
                        : 'bg-white dark:bg-slate-800 border border-border text-foreground hover:bg-slate-50 dark:hover:bg-slate-700'
                ]">
                {{ v.label }}
            </button>
        </nav>

        <!-- KPI cards -->
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div v-for="kpi in currentMeta.kpis" :key="kpi.label"
                class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm p-5 hover:-translate-y-1 transition-transform cursor-pointer">
                <div class="mb-3 flex items-center gap-2.5">
                    <div :class="['flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl', kpi.iconBg, kpi.iconColor]">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="kpi.icon" />
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground leading-tight">{{ kpi.label }}</span>
                </div>
                <div class="text-2xl font-extrabold tracking-tight text-foreground">{{ kpi.value }}</div>
                <div class="mt-1 text-xs text-muted-foreground">{{ kpi.sub }}</div>
            </div>
        </div>

        <!-- Table card -->
        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm overflow-hidden">

            <!-- Table toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
                <!-- Search -->
                <div class="relative w-full max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <input v-model="search" type="text" placeholder="Search ref, buyer, project…"
                        class="w-full rounded-xl border border-border bg-slate-50 dark:bg-slate-800 py-2 pl-9 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-admin-accent focus:outline-none" />
                </div>
                <!-- Status filter pills -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button v-for="s in currentMeta.statuses" :key="s"
                        @click="statusFilter = s"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors',
                            statusFilter === s
                                ? 'bg-admin-accent text-white'
                                : 'border border-border bg-white dark:bg-slate-800 text-foreground hover:bg-slate-50'
                        ]">
                        {{ s }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] border-collapse">
                    <thead>
                        <tr>
                            <th v-for="h in ['Reference', 'Buyer', 'Project / Unit', 'Date', 'Amount', 'Method', 'Status', '']"
                                :key="h"
                                class="px-4 py-3.5 text-left text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                                {{ h }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="rows.length === 0">
                            <td colspan="8" class="border-t border-border px-4 py-10 text-center text-sm text-muted-foreground">
                                No records match your search.
                            </td>
                        </tr>
                        <tr v-for="(row, i) in rows" :key="row.ref"
                            @click="editRow(row)"
                            class="cursor-pointer transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <!-- Reference -->
                            <td class="border-t border-border px-4 py-3">
                                <span class="text-sm font-bold text-admin-accent">{{ row.ref }}</span>
                            </td>
                            <!-- Buyer -->
                            <td class="border-t border-border px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div :class="['flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold', avatarFor(i)[0], avatarFor(i)[1]]">
                                        {{ initials(row.buyer) }}
                                    </div>
                                    <span class="text-sm font-semibold text-foreground whitespace-nowrap">{{ row.buyer }}</span>
                                </div>
                            </td>
                            <!-- Project / Unit -->
                            <td class="border-t border-border px-4 py-3">
                                <div class="text-sm font-semibold text-foreground">{{ row.project }}</div>
                                <div class="text-xs text-muted-foreground">Unit {{ row.unit }}</div>
                            </td>
                            <!-- Date -->
                            <td class="border-t border-border px-4 py-3 text-sm text-foreground whitespace-nowrap">{{ row.date }}</td>
                            <!-- Amount -->
                            <td class="border-t border-border px-4 py-3 text-sm font-bold text-foreground whitespace-nowrap">{{ row.amount }}</td>
                            <!-- Method -->
                            <td class="border-t border-border px-4 py-3 text-sm" :class="row.method === '—' ? 'text-slate-300' : 'text-foreground'">{{ row.method }}</td>
                            <!-- Status -->
                            <td class="border-t border-border px-4 py-3">
                                <span :class="['inline-block rounded-full px-3 py-1 text-[11px] font-bold whitespace-nowrap',
                                    STATUS_STYLE[row.status]?.bg ?? 'bg-slate-100',
                                    STATUS_STYLE[row.status]?.text ?? 'text-slate-500']">
                                    {{ row.status }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="border-t border-border px-4 py-3">
                                <div class="flex items-center gap-1.5" @click.stop>
                                    <button @click="editRow(row)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-admin-accent hover:bg-[#F1ECFF] transition-colors"
                                        title="Edit">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-slate-100 transition-colors" title="More">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between border-t border-border px-5 py-3.5">
                <span class="text-xs text-muted-foreground">
                    Showing {{ rows.length }} of {{ allRows.length }} {{ currentMeta.title.toLowerCase() }}
                </span>
                <div class="flex items-center gap-1.5">
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-white text-sm text-muted-foreground hover:bg-slate-50">‹</button>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-admin-accent text-sm font-semibold text-white">1</button>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-white text-sm text-foreground hover:bg-slate-50">2</button>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-white text-sm text-foreground hover:bg-slate-50">3</button>
                    <span class="w-6 text-center text-muted-foreground">…</span>
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-white text-sm text-muted-foreground hover:bg-slate-50">›</button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
