<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Separator } from '@/Components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

const props = defineProps({
    kpis:                { type: Array,  required: true },
    overview:            { type: Object, required: true },
    priority:            { type: Object, required: true },
    trend:               { type: Array,  required: true },
    tabs:                { type: Array,  required: true },
    tickets:             { type: Array,  required: true },
    statusOverview:      { type: Array,  required: true },
    responsePerformance: { type: Array,  required: true },
    activity:            { type: Array,  required: true },
    quickActions:        { type: Array,  required: true },
});

// ── Icon paths, keyed by the `icon` field coming from the JSON ────────────────
const icons = {
    doc:         '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
    mail:        '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
    spinner:     '<path d="M5 22h14M5 2h14M17 22v-4.2a2 2 0 00-.6-1.4L12 12l-4.4 4.4a2 2 0 00-.6 1.4V22M7 2v4.2a2 2 0 00.6 1.4L12 12l4.4-4.4A2 2 0 0017 6.2V2"/>',
    check:       '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
    archive:     '<path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
    clock:       '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5M9 2h6"/>',
    spark:       '<path d="M12 2l2.9 6.3 6.9.6-5.2 4.5 1.6 6.8L12 17.3 5.8 20.7l1.6-6.8L2.2 8.9l6.9-.6z"/>',
    bolt:        '<path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>',
    plus_ticket: '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2zM12 8v6M9 11h6"/>',
    book:        '<path d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>',
    help:        '<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 015.8 1c0 2-3 3-3 3M12 17h.01"/>',
    list:        '<path d="M4 6h16M4 12h16M4 18h10"/>',
    bar:         '<path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/>',
    feedback:    '<path d="M14 9V5a3 3 0 00-6 0v4M5 9h14l1 12H4L5 9z"/><path d="M9 13c.5 1 1.5 1.5 3 1.5s2.5-.5 3-1.5"/>',
};

// ── Tickets Overview / Priority donuts (r=54, viewBox 0 0 150 150) ────────────
const donutR = 54;
const donutC = 2 * Math.PI * donutR;
function buildSegments(segments) {
    let cursor = 0;
    return segments.map((s) => {
        const filled = (s.pct / 100) * donutC;
        const seg = { ...s, dasharray: `${filled.toFixed(1)} ${(donutC - filled).toFixed(1)}`, dashoffset: -cursor };
        cursor += filled;
        return seg;
    });
}
const overviewSegments = computed(() => buildSegments(props.overview.segments));
const prioritySegments = computed(() => buildSegments(props.priority.segments));

// ── Tickets Trend dual-line chart (viewBox 0 0 460 220, scale 0-500) ─────────
const trendPoints = computed(() => {
    const n = props.trend.length || 1;
    const step = n > 1 ? 384 / (n - 1) : 0;
    const scaleY = (v) => 186 - (v / 500) * 166;
    return props.trend.map((t, i) => ({
        label: t.label,
        x: 68 + i * step,
        openedY: scaleY(t.opened),
        resolvedY: scaleY(t.resolved),
    }));
});
const openedPolyline = computed(() => trendPoints.value.map(p => `${p.x},${p.openedY}`).join(' '));
const resolvedPolyline = computed(() => trendPoints.value.map(p => `${p.x},${p.resolvedY}`).join(' '));
const resolvedAreaPoints = computed(() => {
    if (!trendPoints.value.length) return '';
    const last = trendPoints.value[trendPoints.value.length - 1];
    const first = trendPoints.value[0];
    return `${resolvedPolyline.value} ${last.x},186 ${first.x},186`;
});

// ── Tab filter + search (client-side only — this page is a static demo fixture) ─
const activeTab = ref('All');
const search = ref('');
const localTickets = ref([...props.tickets]);

// ── Filters dialog (category / priority) ──────────────────────────────────────
const showFilters = ref(false);
const filterCategory = ref('');
const filterPriority = ref('');
const activeFilterCount = computed(() => (filterCategory.value ? 1 : 0) + (filterPriority.value ? 1 : 0));

function clearFilters() {
    filterCategory.value = '';
    filterPriority.value = '';
}

const filteredTickets = computed(() => {
    let rows = localTickets.value;
    if (activeTab.value !== 'All') rows = rows.filter(t => t.status === activeTab.value);
    if (filterCategory.value) rows = rows.filter(t => t.cat === filterCategory.value);
    if (filterPriority.value) rows = rows.filter(t => t.prio === filterPriority.value);
    const q = search.value.trim().toLowerCase();
    if (q) {
        rows = rows.filter(t =>
            t.id.toLowerCase().includes(q) ||
            t.subject.toLowerCase().includes(q) ||
            t.cust.toLowerCase().includes(q)
        );
    }
    return rows;
});

// ── Export the currently filtered tickets to a CSV file (client-side) ────────
function csvCell(value) {
    const s = String(value ?? '');
    return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
}

function exportCsv() {
    const headers = ['Ticket ID', 'Subject', 'Category', 'Customer', 'Phone', 'Priority', 'Status', 'Assigned To', 'Last Update'];
    const rows = filteredTickets.value.map(t => [
        t.id, t.subject, t.cat, t.cust, t.phone, t.prio, t.status, t.agent, `${t.date} ${t.time}`,
    ]);
    const csv = [headers, ...rows].map(row => row.map(csvCell).join(',')).join('\r\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `support-tickets-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}

// ── New Ticket dialog — client-side only, nothing is sent to the server ──────
const categoryOptions = ['Payments', 'Documents', 'Site Visit', 'Financing', 'General', 'Reservations', 'Projects'];
const priorityOptions = [
    { value: 'High',   bg: 'rgba(248,113,113,0.15)', color: '#F87171' },
    { value: 'Medium', bg: 'rgba(251,191,36,0.15)', color: '#FBBF24' },
    { value: 'Low',    bg: 'rgba(52,211,153,0.15)', color: '#34D399' },
];
const avatarColors = ['#A78BFA', '#22D3EE', '#FBBF24', '#F472B6', '#60A5FA', '#A78BFA', '#60A5FA', '#F472B6'];

const showNewTicket = ref(false);
const newTicketForm = ref({ subject: '', cust: '', phone: '', dept: categoryOptions[0], prio: 'Medium' });

function openNewTicket() {
    newTicketForm.value = { subject: '', cust: '', phone: '', dept: categoryOptions[0], prio: 'Medium' };
    showNewTicket.value = true;
}

function initials(name) {
    return name.trim().split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase() || '?';
}

function submitNewTicket() {
    const f = newTicketForm.value;
    if (!f.subject.trim() || !f.cust.trim()) return;

    const prio = priorityOptions.find(p => p.value === f.prio) ?? priorityOptions[1];
    const nextNum = 2500 + localTickets.value.length + 1;
    const now = new Date();

    localTickets.value.unshift({
        id: `TKT-${nextNum}`,
        subject: f.subject.trim(),
        dept: f.dept,
        cust: f.cust.trim(),
        phone: f.phone.trim() || '—',
        av: initials(f.cust),
        avBg: avatarColors[localTickets.value.length % avatarColors.length],
        cat: f.dept,
        prio: prio.value,
        prioBg: prio.bg,
        prioColor: prio.color,
        status: 'Open',
        statusBg: 'rgba(96,165,250,0.15)',
        statusColor: '#60A5FA',
        agent: 'Unassigned',
        role: '—',
        agAv: '—',
        agBg: '#8A8780',
        date: now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
        time: now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }),
    });

    activeTab.value = 'All';
    showNewTicket.value = false;
}

// ── Assign Agent dialog — client-side only, nothing is sent to the server ────
const agentOptions = [
    { name: 'Jannatul Islam', role: 'Support Agent', av: 'JI', bg: '#F472B6' },
    { name: 'Fahim Ahmed',    role: 'Support Lead',  av: 'FA', bg: '#C6A15B' },
    { name: 'Tanvir Hasan',   role: 'Support Agent', av: 'TH', bg: '#60A5FA' },
    { name: 'Rasel Hossain',  role: 'Support Agent', av: 'RH', bg: '#22D3EE' },
    { name: 'Nusrat Jahan',   role: 'Support Lead',  av: 'NJ', bg: '#F472B6' },
];

const showAssign = ref(false);
const assigningTicket = ref(null);
const assignAgentName = ref('');

function openAssign(ticket) {
    assigningTicket.value = ticket;
    assignAgentName.value = ticket.agent !== 'Unassigned' ? ticket.agent : '';
    showAssign.value = true;
}

function submitAssign() {
    if (!assignAgentName.value || !assigningTicket.value) return;
    const agent = agentOptions.find(a => a.name === assignAgentName.value);
    const t = localTickets.value.find(t => t.id === assigningTicket.value.id);
    if (t && agent) {
        t.agent = agent.name;
        t.role = agent.role;
        t.agAv = agent.av;
        t.agBg = agent.bg;
        if (t.status === 'Open') {
            t.status = 'In Progress';
            t.statusBg = 'rgba(167,139,250,0.15)';
            t.statusColor = '#A78BFA';
        }
    }
    showAssign.value = false;
}

// ── View Ticket dialog — read-only detail view ────────────────────────────────
const showView = ref(false);
const viewingTicket = ref(null);

function openView(ticket) {
    viewingTicket.value = ticket;
    showView.value = true;
}

function assignFromView() {
    showView.value = false;
    openAssign(viewingTicket.value);
}

// ── Overview / Priority report dialogs — expand on the donut breakdowns above ─
const showOverviewReport = ref(false);
const showPriorityReport = ref(false);

// ── Ticket Status Overview "View All" — jump to that status in the table ─────
const showStatusOverview = ref(false);
function goToStatusTab(name) {
    activeTab.value = name;
    showStatusOverview.value = false;
}

// ── Recent Activity "View All" ────────────────────────────────────────────────
const showActivityAll = ref(false);

// ── Quick Actions dispatcher ──────────────────────────────────────────────────
const ticketTableEl = ref(null);

function viewAllTickets() {
    activeTab.value = 'All';
    search.value = '';
    ticketTableEl.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

const showComingSoon = ref(false);
const comingSoonLabel = ref('');
function openComingSoon(label) {
    comingSoonLabel.value = label;
    showComingSoon.value = true;
}

function runQuickAction(action) {
    switch (action.icon) {
        case 'plus_ticket': openNewTicket(); break;
        case 'list':        viewAllTickets(); break;
        case 'bar':         showOverviewReport.value = true; break;
        default:            openComingSoon(action.label);
    }
}
</script>

<template>
    <Head title="Support Tickets" />

    <AdminLayout title="Support Tickets" :breadcrumbs="[{ label: 'Admin' }, { label: 'Support Tickets' }]">
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-foreground">
                    Support Tickets
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent"><path d="M4 14v-3a8 8 0 0116 0v3"/><path d="M18 19a2 2 0 01-2 2h-2"/><rect x="2" y="13" width="4" height="7" rx="1.5"/><rect x="18" y="13" width="4" height="7" rx="1.5"/></svg>
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Manage customer support requests and ensure timely resolutions.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openNewTicket" class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-on-gold transition-colors hover:bg-admin-accent/90">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    New Ticket
                </button>
                <button class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg>
                    Ticket Settings
                </button>
            </div>
        </div>

        <!-- KPI row -->
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
            <div v-for="k in kpis" :key="k.key" class="rounded-2xl border border-border bg-admin-surface-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-2.5 flex items-center gap-2">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: k.bg, color: k.color }">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[k.icon]" />
                    </div>
                    <span class="text-[11.5px] font-semibold leading-tight text-muted-foreground">{{ k.label }}</span>
                </div>
                <div class="text-2xl font-extrabold leading-none tracking-tight text-foreground">{{ k.value }}</div>
                <div class="mt-2.5 flex items-center justify-between text-[11px]">
                    <span class="text-muted-foreground">{{ k.sub }}</span>
                    <span class="flex items-center gap-0.5 font-bold" :style="{ color: k.changeColor }">
                        <svg v-if="k.dir === 'up'" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                        <svg v-else width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9l-6 6-6-6"/></svg>
                        {{ k.change }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Body grid -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="flex min-w-0 flex-col gap-6">

                <!-- Analytics row -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    <!-- Tickets Overview -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-2 text-base font-bold text-foreground">Tickets Overview</div>
                        <div class="flex flex-1 flex-wrap items-center justify-center gap-4">
                            <div class="relative h-[150px] w-[150px] flex-none">
                                <svg width="150" height="150" viewBox="0 0 150 150" style="transform:rotate(-90deg);">
                                    <circle cx="75" cy="75" r="54" fill="none" class="stroke-muted" stroke-width="16"/>
                                    <circle v-for="seg in overviewSegments" :key="seg.key" cx="75" cy="75" r="54" fill="none" :stroke="seg.color" stroke-width="16" :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <div class="text-2xl font-extrabold tracking-tight text-foreground">{{ overview.total }}</div>
                                    <div class="text-[10px] text-muted-foreground">Total Tickets</div>
                                </div>
                            </div>
                            <div class="flex min-w-[170px] flex-1 flex-col gap-2.5">
                                <div v-for="s in overview.segments" :key="s.key" class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2 w-2 flex-none rounded-full" :style="{ background: s.color }"></span>{{ s.label }}</span>
                                    <span class="font-bold text-foreground">{{ s.count }} <span class="font-medium text-muted-foreground">({{ s.pct }}%)</span></span>
                                </div>
                                <div v-for="s in overview.extra" :key="s.key" class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2 w-2 flex-none rounded-full" :style="{ background: s.color }"></span>{{ s.label }}</span>
                                    <span class="font-bold text-foreground">{{ s.count }} <span class="font-medium text-muted-foreground">({{ s.pct_label }})</span></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="showOverviewReport = true" class="mt-3.5 flex cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-xs font-semibold text-admin-accent">
                            View Detailed Report
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>

                    <!-- Tickets Trend -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <div class="text-base font-bold text-foreground">Tickets Trend</div>
                            <span class="flex items-center gap-1.5 rounded-[9px] border border-border px-2.5 py-1.5 text-[11px] font-semibold text-foreground/80">This Year
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="stroke-muted-foreground" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </span>
                        </div>
                        <div class="mb-1.5 flex items-center gap-4 text-[11.5px]">
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2 w-2 rounded-full bg-admin-accent"></span>Opened</span>
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2 w-2 rounded-full" style="background:#34D399;"></span>Resolved</span>
                        </div>
                        <div class="relative min-h-[200px] flex-1">
                            <svg viewBox="0 0 460 220" preserveAspectRatio="none" class="block h-full w-full">
                                <defs><linearGradient id="stResG" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#34D399" stop-opacity="0.18"/><stop offset="100%" stop-color="#34D399" stop-opacity="0"/></linearGradient></defs>
                                <line x1="36" y1="20" x2="36" y2="186" class="stroke-chart-grid/[0.08]" stroke-width="1"/>
                                <line x1="36" y1="186" x2="450" y2="186" class="stroke-chart-grid/[0.08]" stroke-width="1"/>
                                <line v-for="y in [145,104,63]" :key="y" x1="36" :y1="y" x2="450" :y2="y" class="stroke-chart-grid/[0.06]" stroke-width="1"/>
                                <text x="26" y="190" font-size="9" class="fill-muted-foreground" text-anchor="end">100</text>
                                <text x="26" y="149" font-size="9" class="fill-muted-foreground" text-anchor="end">200</text>
                                <text x="26" y="108" font-size="9" class="fill-muted-foreground" text-anchor="end">300</text>
                                <text x="26" y="67" font-size="9" class="fill-muted-foreground" text-anchor="end">400</text>
                                <text x="26" y="26" font-size="9" class="fill-muted-foreground" text-anchor="end">500</text>
                                <polygon :points="resolvedAreaPoints" fill="url(#stResG)"/>
                                <polyline :points="openedPolyline" fill="none" stroke="#C6A15B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline :points="resolvedPolyline" fill="none" stroke="#34D399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle v-for="p in trendPoints" :key="'o-'+p.label" :cx="p.x" :cy="p.openedY" r="3" fill="#C6A15B"/>
                                <circle v-for="p in trendPoints" :key="'r-'+p.label" :cx="p.x" :cy="p.resolvedY" r="3" fill="#34D399"/>
                                <text v-for="p in trendPoints" :key="'l-'+p.label" :x="p.x" y="202" font-size="9" class="fill-muted-foreground" text-anchor="middle">{{ p.label }}</text>
                            </svg>
                        </div>
                    </div>

                    <!-- Tickets by Priority -->
                    <div class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                        <div class="mb-2 text-base font-bold text-foreground">Tickets by Priority</div>
                        <div class="flex flex-1 flex-wrap items-center justify-center gap-4">
                            <div class="relative h-[150px] w-[150px] flex-none">
                                <svg width="150" height="150" viewBox="0 0 150 150" style="transform:rotate(-90deg);">
                                    <circle cx="75" cy="75" r="54" fill="none" class="stroke-muted" stroke-width="16"/>
                                    <circle v-for="seg in prioritySegments" :key="seg.key" cx="75" cy="75" r="54" fill="none" :stroke="seg.color" stroke-width="16" :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <div class="text-2xl font-extrabold tracking-tight text-foreground">{{ priority.total }}</div>
                                    <div class="text-[10px] text-muted-foreground">Total</div>
                                </div>
                            </div>
                            <div class="flex min-w-[170px] flex-1 flex-col gap-3.5">
                                <div v-for="s in priority.segments" :key="s.key" class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2 w-2 flex-none rounded-full" :style="{ background: s.color }"></span>{{ s.label }}</span>
                                    <span class="font-bold text-foreground">{{ s.count }} <span class="font-medium text-muted-foreground">({{ s.pct }}%)</span></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="showPriorityReport = true" class="mt-3.5 flex cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-xs font-semibold text-admin-accent">
                            View Priority Report
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Support Ticket table -->
                <div ref="ticketTableEl" class="min-w-0 rounded-2xl border border-border bg-admin-surface-card shadow-card">
                    <div class="flex flex-wrap items-center justify-between gap-4 p-[18px] pb-0">
                        <div class="text-base font-bold text-foreground">All Support Tickets</div>
                        <div class="flex items-center gap-2.5">
                            <div class="relative w-full max-w-[260px]">
                                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                                <Input v-model="search" placeholder="Search ticket ID, subject, customer..." class="pl-9" />
                            </div>
                            <button type="button" @click="showFilters = true" class="relative inline-flex items-center gap-1.5 rounded-[10px] border border-border px-3 py-1.5 text-xs font-semibold text-foreground/80 transition-colors hover:bg-muted">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                                Filters
                                <span v-if="activeFilterCount > 0" class="flex h-4 w-4 items-center justify-center rounded-full bg-admin-accent text-[10px] font-bold text-on-gold">{{ activeFilterCount }}</span>
                            </button>
                            <button type="button" @click="exportCsv" class="inline-flex items-center gap-1.5 rounded-[10px] border border-border px-3 py-1.5 text-xs font-semibold text-foreground/80 transition-colors hover:bg-muted">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3M8 7l4-4 4 4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                                Export
                            </button>
                        </div>
                    </div>

                    <!-- Status tabs -->
                    <div class="mt-2 flex items-center gap-6 overflow-x-auto border-b border-border px-[18px] pt-2">
                        <button
                            v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
                            class="whitespace-nowrap border-b-[2.5px] pb-2.5 text-[13.5px] font-semibold transition-colors"
                            :class="activeTab === t.key ? 'border-admin-accent text-admin-accent' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        >
                            {{ t.label }} ({{ t.count }})
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <Table class="min-w-[900px]">
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="pl-[18px]">Ticket ID</TableHead>
                                    <TableHead>Subject</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Priority</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Assigned To</TableHead>
                                    <TableHead>Last Update</TableHead>
                                    <TableHead class="pr-[18px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="filteredTickets.length === 0">
                                    <TableCell colspan="9" class="py-14 text-center text-muted-foreground">No tickets found</TableCell>
                                </TableRow>
                                <TableRow v-for="t in filteredTickets" :key="t.id">
                                    <TableCell class="pl-[18px] text-[12.5px] font-bold text-admin-accent">{{ t.id }}</TableCell>
                                    <TableCell class="min-w-[180px]">
                                        <div class="truncate text-[13px] font-semibold text-foreground">{{ t.subject }}</div>
                                        <div class="truncate text-[11px] text-muted-foreground">{{ t.dept }}</div>
                                    </TableCell>
                                    <TableCell class="min-w-[150px]">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-full text-[10.5px] font-bold text-white" :style="{ background: t.avBg }">{{ t.av }}</div>
                                            <div class="min-w-0">
                                                <div class="truncate text-xs font-semibold text-foreground">{{ t.cust }}</div>
                                                <div class="truncate text-[11px] text-muted-foreground">{{ t.phone }}</div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap text-xs font-semibold text-foreground/80">{{ t.cat }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="whitespace-nowrap rounded-full border-transparent px-[11px] py-1 text-[11px] font-bold" :style="{ background: t.prioBg, color: t.prioColor }">{{ t.prio }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="whitespace-nowrap rounded-full border-transparent px-[11px] py-1 text-[11px] font-bold" :style="{ background: t.statusBg, color: t.statusColor }">{{ t.status }}</Badge>
                                    </TableCell>
                                    <TableCell class="min-w-[150px]">
                                        <button
                                            v-if="t.agent === 'Unassigned'"
                                            @click="openAssign(t)"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-dashed border-border px-2.5 py-1 text-[11px] font-semibold text-muted-foreground transition-colors hover:border-admin-accent hover:text-admin-accent"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                                            Assign
                                        </button>
                                        <button v-else @click="openAssign(t)" class="flex items-center gap-2.5 rounded-lg text-left transition-colors hover:bg-muted" title="Reassign ticket">
                                            <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-full text-[10.5px] font-bold text-white" :style="{ background: t.agBg }">{{ t.agAv }}</div>
                                            <div class="min-w-0">
                                                <div class="truncate text-xs font-semibold text-foreground">{{ t.agent }}</div>
                                                <div class="truncate text-[11px] text-muted-foreground">{{ t.role }}</div>
                                            </div>
                                        </button>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap">
                                        <div class="text-xs font-semibold text-foreground">{{ t.date }}</div>
                                        <div class="text-[11px] text-muted-foreground">{{ t.time }}</div>
                                    </TableCell>
                                    <TableCell class="pr-[18px]">
                                        <button @click="openView(t)" title="View ticket" class="flex h-[30px] w-[30px] items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>
                                        </button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3.5 border-t border-border p-[18px]">
                        <div class="text-xs text-muted-foreground">Showing {{ filteredTickets.length }} of {{ tickets.length }} tickets</div>
                        <div class="flex items-center gap-1.5">
                            <button class="flex h-8 w-8 items-center justify-center rounded-[9px] border border-border text-muted-foreground transition-colors hover:bg-muted">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                            </button>
                            <span class="flex h-8 w-8 items-center justify-center rounded-[9px] bg-admin-accent text-xs font-bold text-on-gold">1</span>
                            <button class="flex h-8 w-8 items-center justify-center rounded-[9px] border border-border text-xs font-semibold text-foreground/80 transition-colors hover:bg-muted">2</button>
                            <button class="flex h-8 w-8 items-center justify-center rounded-[9px] border border-border text-xs font-semibold text-foreground/80 transition-colors hover:bg-muted">3</button>
                            <span class="flex h-8 w-8 items-center justify-center text-xs text-muted-foreground">…</span>
                            <button class="flex h-8 w-[42px] items-center justify-center rounded-[9px] border border-border text-xs font-semibold text-foreground/80 transition-colors hover:bg-muted">178</button>
                            <button class="flex h-8 w-8 items-center justify-center rounded-[9px] border border-border text-foreground/80 transition-colors hover:bg-muted">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="flex min-w-0 flex-col gap-6">

                <!-- Ticket Status Overview -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Ticket Status Overview</div>
                        <button type="button" @click="showStatusOverview = true" class="cursor-pointer text-xs font-semibold text-admin-accent">View All</button>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="s in statusOverview" :key="s.name" class="flex items-center gap-3 border-b border-border/60 py-2.5 last:border-b-0">
                            <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: s.bg, color: s.color }">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.mail" />
                            </div>
                            <div class="flex-1 text-[13px] font-semibold text-foreground">{{ s.name }}</div>
                            <div class="text-[13.5px] font-bold text-foreground/80">{{ s.count }}</div>
                        </div>
                    </div>
                </div>

                <!-- Response Performance -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-3.5 text-base font-bold text-foreground">Response Performance <span class="text-xs font-medium text-muted-foreground">(This Month)</span></div>
                    <div class="flex flex-col gap-4">
                        <div v-for="r in responsePerformance" :key="r.label">
                            <div class="mb-2 flex items-center gap-2.5">
                                <div class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[9px]" :style="{ background: r.bg, color: r.color }">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[r.icon]" />
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-muted-foreground">{{ r.label }}</div>
                                    <div class="text-[15px] font-extrabold text-foreground">{{ r.value }}</div>
                                </div>
                                <span class="flex items-center gap-0.5 text-[11px] font-bold text-success">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9l-6 6-6-6"/></svg>
                                    {{ r.change }}%
                                </span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full" :style="{ width: r.barPct + '%', background: r.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Recent Activity</div>
                        <button type="button" @click="showActivityAll = true" class="cursor-pointer text-xs font-semibold text-admin-accent">View All</button>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="a in activity" :key="a.txt" class="flex items-start gap-2.5 border-b border-border/60 py-2.5 last:border-b-0">
                            <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-[8px]" :style="{ background: a.bg, color: a.color }">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" v-html="icons.mail" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs leading-snug text-foreground/80">{{ a.txt }}</div>
                                <div class="mt-0.5 text-[11px] text-muted-foreground">{{ a.time }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-3.5 text-base font-bold text-foreground">Quick Actions</div>
                    <div class="grid grid-cols-3 gap-3">
                        <button
                            v-for="a in quickActions" :key="a.label"
                            type="button"
                            @click="runQuickAction(a)"
                            class="flex flex-col items-center justify-center gap-2 rounded-[14px] border border-border p-3.5 text-center transition-all hover:-translate-y-[3px]"
                            :style="{ '--hbg': a.bg, '--hborder': a.color }"
                            @mouseenter="$event.currentTarget.style.background = 'var(--hbg)'; $event.currentTarget.style.borderColor = 'var(--hborder)'"
                            @mouseleave="$event.currentTarget.style.background = ''; $event.currentTarget.style.borderColor = ''"
                        >
                            <div class="flex h-[34px] w-[34px] items-center justify-center rounded-[10px]" :style="{ background: a.bg, color: a.color }">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[a.icon]" />
                            </div>
                            <span class="text-[11px] font-semibold text-foreground/80">{{ a.label }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Ticket dialog — client-side only, nothing is sent to the server -->
        <Dialog v-model:open="showNewTicket">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>New Ticket</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitNewTicket" class="space-y-4 px-6 pb-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Subject</Label>
                        <Input v-model="newTicketForm.subject" placeholder="e.g. Payment not reflected in account" required />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Customer Name</Label>
                            <Input v-model="newTicketForm.cust" placeholder="e.g. Rahim Uddin" required />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Phone</Label>
                            <Input v-model="newTicketForm.phone" placeholder="+880 17XX-XXXXXX" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Category</Label>
                            <Select v-model="newTicketForm.dept">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Priority</Label>
                            <Select v-model="newTicketForm.prio">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="p in priorityOptions" :key="p.value" :value="p.value">{{ p.value }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter class="!px-0 pt-2">
                        <Button type="button" variant="outline" @click="showNewTicket = false">Cancel</Button>
                        <Button type="submit" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90">Create Ticket</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Assign Agent dialog — client-side only, nothing is sent to the server -->
        <Dialog v-model:open="showAssign">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Assign Ticket</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitAssign" class="space-y-4 px-6 pb-2">
                    <p class="-mt-1 text-xs text-muted-foreground">
                        <span class="font-semibold text-foreground">{{ assigningTicket?.id }}</span> — {{ assigningTicket?.subject }}
                    </p>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Agent</Label>
                        <Select v-model="assignAgentName">
                            <SelectTrigger><SelectValue placeholder="Select an agent" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="a in agentOptions" :key="a.name" :value="a.name">{{ a.name }} — {{ a.role }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <DialogFooter class="!px-0 pt-2">
                        <Button type="button" variant="outline" @click="showAssign = false">Cancel</Button>
                        <Button type="submit" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90" :disabled="!assignAgentName">Assign</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- View Ticket dialog — read-only detail view -->
        <Dialog v-model:open="showView">
            <DialogContent v-if="viewingTicket" class="w-full max-w-lg">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2.5">
                        <span class="text-admin-accent">{{ viewingTicket.id }}</span>
                        <Badge variant="outline" class="rounded-full border-transparent px-[11px] py-1 text-[11px] font-bold" :style="{ background: viewingTicket.statusBg, color: viewingTicket.statusColor }">{{ viewingTicket.status }}</Badge>
                        <Badge variant="outline" class="rounded-full border-transparent px-[11px] py-1 text-[11px] font-bold" :style="{ background: viewingTicket.prioBg, color: viewingTicket.prioColor }">{{ viewingTicket.prio }}</Badge>
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 px-6 pb-2">
                    <div>
                        <div class="text-[15px] font-bold text-foreground">{{ viewingTicket.subject }}</div>
                        <div class="mt-0.5 text-xs text-muted-foreground">{{ viewingTicket.dept }} · {{ viewingTicket.cat }}</div>
                    </div>

                    <Separator />

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="mb-1.5 text-[11px] font-semibold text-muted-foreground">Customer</div>
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-full text-xs font-bold text-white" :style="{ background: viewingTicket.avBg }">{{ viewingTicket.av }}</div>
                                <div class="min-w-0">
                                    <div class="truncate text-xs font-semibold text-foreground">{{ viewingTicket.cust }}</div>
                                    <div class="truncate text-[11px] text-muted-foreground">{{ viewingTicket.phone }}</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1.5 text-[11px] font-semibold text-muted-foreground">Assigned To</div>
                            <div v-if="viewingTicket.agent === 'Unassigned'" class="flex items-center gap-2.5">
                                <span class="text-xs text-muted-foreground">Unassigned</span>
                            </div>
                            <div v-else class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-full text-xs font-bold text-white" :style="{ background: viewingTicket.agBg }">{{ viewingTicket.agAv }}</div>
                                <div class="min-w-0">
                                    <div class="truncate text-xs font-semibold text-foreground">{{ viewingTicket.agent }}</div>
                                    <div class="truncate text-[11px] text-muted-foreground">{{ viewingTicket.role }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Last Update</span>
                        <span class="font-semibold text-foreground">{{ viewingTicket.date }} · {{ viewingTicket.time }}</span>
                    </div>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showView = false">Close</Button>
                    <Button type="button" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90" @click="assignFromView">
                        {{ viewingTicket.agent === 'Unassigned' ? 'Assign Ticket' : 'Reassign Ticket' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Tickets Overview — detailed report dialog -->
        <Dialog v-model:open="showOverviewReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Tickets Overview — Detailed Report</DialogTitle>
                </DialogHeader>

                <div class="space-y-4 px-6 pb-4">
                    <div class="flex items-center justify-between rounded-xl border border-border p-3.5">
                        <span class="text-xs font-semibold text-muted-foreground">Total Tickets</span>
                        <span class="text-lg font-extrabold text-foreground">{{ overview.total }}</span>
                    </div>

                    <div class="space-y-3">
                        <div v-for="s in overview.segments" :key="s.key">
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="flex items-center gap-1.5 font-semibold text-foreground/80"><span class="h-2 w-2 rounded-full" :style="{ background: s.color }"></span>{{ s.label }}</span>
                                <span class="font-bold text-foreground">{{ s.count }} <span class="font-medium text-muted-foreground">({{ s.pct }}%)</span></span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full" :style="{ width: s.pct + '%', background: s.color }"></div>
                            </div>
                        </div>
                        <div v-for="s in overview.extra" :key="s.key" class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 font-semibold text-foreground/80"><span class="h-2 w-2 rounded-full" :style="{ background: s.color }"></span>{{ s.label }}</span>
                            <span class="font-bold text-foreground">{{ s.count }} <span class="font-medium text-muted-foreground">({{ s.pct_label }})</span></span>
                        </div>
                    </div>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showOverviewReport = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Tickets by Priority — detailed report dialog -->
        <Dialog v-model:open="showPriorityReport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Tickets by Priority — Detailed Report</DialogTitle>
                </DialogHeader>

                <div class="space-y-4 px-6 pb-4">
                    <div class="flex items-center justify-between rounded-xl border border-border p-3.5">
                        <span class="text-xs font-semibold text-muted-foreground">Total Tickets</span>
                        <span class="text-lg font-extrabold text-foreground">{{ priority.total }}</span>
                    </div>

                    <div class="space-y-3">
                        <div v-for="s in priority.segments" :key="s.key">
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="flex items-center gap-1.5 font-semibold text-foreground/80"><span class="h-2 w-2 rounded-full" :style="{ background: s.color }"></span>{{ s.label }}</span>
                                <span class="font-bold text-foreground">{{ s.count }} <span class="font-medium text-muted-foreground">({{ s.pct }}%)</span></span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full" :style="{ width: s.pct + '%', background: s.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showPriorityReport = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Ticket Status Overview — View All dialog (click a status to filter the table) -->
        <Dialog v-model:open="showStatusOverview">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Ticket Status Overview</DialogTitle>
                </DialogHeader>

                <div class="space-y-1 px-6 pb-4">
                    <button
                        v-for="s in statusOverview" :key="s.name" type="button"
                        @click="goToStatusTab(s.name)"
                        class="flex w-full items-center gap-3 rounded-lg px-2 py-2.5 text-left transition-colors hover:bg-muted"
                    >
                        <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: s.bg, color: s.color }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.mail" />
                        </div>
                        <div class="flex-1 text-[13px] font-semibold text-foreground">{{ s.name }}</div>
                        <div class="text-[13.5px] font-bold text-foreground/80">{{ s.count }}</div>
                    </button>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showStatusOverview = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Recent Activity — View All dialog -->
        <Dialog v-model:open="showActivityAll">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Recent Activity</DialogTitle>
                </DialogHeader>

                <div class="max-h-[60vh] space-y-1 overflow-y-auto px-6 pb-4">
                    <div v-for="a in activity" :key="a.txt" class="flex items-start gap-2.5 border-b border-border/60 py-2.5 last:border-b-0">
                        <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-[8px]" :style="{ background: a.bg, color: a.color }">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" v-html="icons.mail" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs leading-snug text-foreground/80">{{ a.txt }}</div>
                            <div class="mt-0.5 text-[11px] text-muted-foreground">{{ a.time }}</div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showActivityAll = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Quick Actions — coming soon dialog (Knowledge Base / FAQ / Customer Feedback) -->
        <Dialog v-model:open="showComingSoon">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>{{ comingSoonLabel }}</DialogTitle>
                </DialogHeader>
                <p class="px-6 pb-2 text-sm text-muted-foreground">This feature is coming soon.</p>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showComingSoon = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Filters dialog — narrows the table by category / priority -->
        <Dialog v-model:open="showFilters">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Filter Tickets</DialogTitle>
                </DialogHeader>

                <div class="space-y-4 px-6 pb-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Category</Label>
                        <Select :model-value="filterCategory || undefined" @update:model-value="filterCategory = $event ?? ''">
                            <SelectTrigger><SelectValue placeholder="All categories" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Priority</Label>
                        <Select :model-value="filterPriority || undefined" @update:model-value="filterPriority = $event ?? ''">
                            <SelectTrigger><SelectValue placeholder="All priorities" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="p in priorityOptions" :key="p.value" :value="p.value">{{ p.value }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="clearFilters">Clear</Button>
                    <Button type="button" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90" @click="showFilters = false">Apply</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
