<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import DatePicker from '@/Components/ui/date-picker/DatePicker.vue';
import ImageUpload from '@/Components/ui/media/ImageUpload.vue';

const props = defineProps({
    kpis:               { type: Object, required: true },
    progressOverview:   { type: Object, required: true },
    progressTrend:      { type: Array,  required: true },
    progressByProject:  { type: Array,  required: true },
    upcomingMilestones: { type: Array,  required: true },
    siteActivity:       { type: Array,  required: true },
    table:              { type: Object, required: true },
    projectOptions:     { type: Array,  required: true },
    filters:            { type: Object, default: () => ({}) },
});

// ── Table search ───────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
let searchTimer;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.construction.index'), { search: search.value || undefined }, {
            preserveState: true, replace: true, preserveScroll: true,
        });
    }, 400);
});

// ── Icon colour cycling (visual only — mirrors the source design) ─
const iconPairs = [
    ['#E8F0FF', '#3B82F6'], ['#E6F7EE', '#22C55E'], ['#EDE9FE', '#7C3AED'],
    ['#FFF3E0', '#F59E0B'], ['#FDE8E8', '#EF4444'], ['#CCFBF1', '#0D9488'],
];
const activityPairs = [
    ['#E6F7EE', '#15803D'], ['#E8F0FF', '#2563EB'], ['#EDE9FE', '#6D28D9'],
    ['#FFF3E0', '#B45309'], ['#CCFBF1', '#0D9488'],
];
const milestonePairs = [
    ['#E6F7EE', '#15803D'], ['#FFF3E0', '#B45309'], ['#E8F0FF', '#2563EB'],
    ['#FDE8E8', '#DC2626'], ['#EDE9FE', '#6D28D9'],
];
const pair = (arr, i) => arr[i % arr.length];

// ── Formatters ─────────────────────────────────────────────────
function formatBDT(amount) {
    if (!amount) return 'BDT 0';
    if (amount >= 1_000_000_000) return `BDT ${(amount / 1_000_000_000).toFixed(2)}B`;
    if (amount >= 1_000_000)     return `BDT ${(amount / 1_000_000).toFixed(1)}M`;
    return `BDT ${Math.round(amount).toLocaleString()}`;
}

function qualityColor(score) {
    if (score >= 85) return '#22C55E';
    if (score >= 70) return '#22C55E';
    if (score >= 50) return '#F59E0B';
    return '#EF4444';
}

function qualityLabel(score) {
    if (score >= 85) return 'Excellent';
    if (score >= 70) return 'Good';
    if (score >= 50) return 'Average';
    return 'Poor';
}

const ringC = 2 * Math.PI * 15; // small quality ring (r=15)
function qualityDash(score) {
    const filled = (score / 100) * ringC;
    return `${filled.toFixed(1)} ${(ringC - filled).toFixed(1)}`;
}

// ── KPI cards ──────────────────────────────────────────────────
const kpiCards = computed(() => ([
    {
        label: 'Active Projects', value: props.kpis.active_projects,
        sub: `${props.kpis.active_pct}% of all projects`,
        bg: '#F1ECFF', color: '#5B3DF5',
        path: 'M7 21V5l11-2v18M7 9l11-2M3 21h18M11 21v-4h3v4',
    },
    {
        label: 'Buildings Under Construction', value: props.kpis.buildings,
        sub: `Across ${props.kpis.active_projects} active projects`,
        bg: '#E8F0FF', color: '#3B82F6',
        path: 'M3 21h18M5 21V7h6v14M11 21V3h8v18M8 10h.01M8 14h.01M15 7h.01M15 11h.01M15 15h.01',
    },
    {
        label: 'Overall Progress', value: `${props.kpis.overall_progress}%`,
        sub: 'Avg. across active projects',
        bg: '#E6F7EE', color: '#22C55E',
        circle: true,
    },
    {
        label: 'On Time Projects', value: props.kpis.on_time,
        sub: `${props.kpis.on_time_pct}% of total`,
        bg: '#FFF3E0', color: '#F59E0B',
        path: null, rect: true,
    },
    {
        label: 'Delayed Projects', value: props.kpis.delayed,
        sub: `${props.kpis.delayed_pct}% of total`,
        bg: '#FDE8E8', color: '#EF4444', valueColor: '#EF4444',
        clock: true,
    },
    {
        label: 'Milestones Completed', value: props.kpis.milestones_done,
        sub: `${props.kpis.milestones_pending} pending`,
        bg: '#EDE9FE', color: '#7C3AED',
        check: true,
    },
    {
        label: 'Budget Utilized', value: formatBDT(props.kpis.budget_used),
        sub: `${props.kpis.budget_used_pct}% of total budget`,
        bg: '#CCFBF1', color: '#0D9488',
        wallet: true,
    },
]));

// ── Progress overview donut (r=58) ────────────────────────────
const donutR = 58;
const donutC = 2 * Math.PI * donutR;
const donutSegments = computed(() => {
    const segs = [
        { key: 'completed',   color: '#22C55E', value: props.progressOverview.completed },
        { key: 'in_progress', color: '#3B82F6', value: props.progressOverview.in_progress },
        { key: 'pending',     color: '#F59E0B', value: props.progressOverview.pending },
        { key: 'delayed',     color: '#EF4444', value: props.progressOverview.delayed },
    ];
    let cursor = 0;
    return segs.map((s) => {
        const filled = (s.value / 100) * donutC;
        const seg = { ...s, dasharray: `${filled.toFixed(1)} ${(donutC - filled).toFixed(1)}`, dashoffset: -cursor };
        cursor += filled;
        return seg;
    });
});

// ── Progress trend chart (straight-line polyline, viewBox 0 0 640 270) ─
const trendPoints = computed(() => {
    const n = props.progressTrend.length || 1;
    const step = n > 1 ? 550 / (n - 1) : 0;
    return props.progressTrend.map((p, i) => ({
        x: 50 + i * step,
        y: 250 - (p.value / 100) * 226,
        ...p,
    }));
});
const trendPolyline  = computed(() => trendPoints.value.map(p => `${p.x},${p.y}`).join(' '));
const trendAreaPoints = computed(() => {
    if (!trendPoints.value.length) return '';
    const last = trendPoints.value[trendPoints.value.length - 1];
    const first = trendPoints.value[0];
    return `${trendPolyline.value} ${last.x},250 ${first.x},250`;
});
const latestTrend = computed(() => trendPoints.value[trendPoints.value.length - 1] ?? null);

// ── Add Site Update dialog ────────────────────────────────────
const showAddUpdate = ref(false);
const form = useForm({
    project_id: '',
    title: '',
    description: '',
    update_date: new Date().toISOString().split('T')[0],
    progress: 0,
    time_progress: 0,
    budget_total: 0,
    budget_used: 0,
    photo: null,
});

// Selecting a project prefills these fields with that project's current
// values — the admin then edits them to reflect this update.
const selectedProjectBaseline = computed(() => {
    const project = props.projectOptions.find((p) => p.id === form.project_id);
    return project ? project.overall_progress : null;
});
const selectedProjectTimeBaseline = computed(() => {
    const project = props.projectOptions.find((p) => p.id === form.project_id);
    return project ? project.time_progress : null;
});
const selectedProjectBudget = computed(() => {
    const project = props.projectOptions.find((p) => p.id === form.project_id);
    return project ? { total: project.budget_total, used: project.budget_used } : null;
});
const budgetUsedPct = computed(() => (
    form.budget_total > 0 ? Math.min(100, Math.round((form.budget_used / form.budget_total) * 100)) : 0
));

watch(() => form.project_id, (id) => {
    const project = props.projectOptions.find((p) => p.id === id);
    form.progress = project ? project.overall_progress : 0;
    form.time_progress = project ? project.time_progress : 0;
    form.budget_total = project ? project.budget_total : 0;
    form.budget_used = project ? project.budget_used : 0;
});

function openAddUpdate() {
    form.reset();
    form.update_date = new Date().toISOString().split('T')[0];
    showAddUpdate.value = true;
}

function submitUpdate() {
    form.post(route('admin.construction.site-updates.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { showAddUpdate.value = false; form.reset(); },
    });
}
</script>

<template>
    <Head title="Construction Updates" />

    <AdminLayout title="Construction Updates" :breadcrumbs="[{ label: 'Admin' }, { label: 'Construction Updates' }]">
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-foreground">
                    Construction Updates <span class="text-lg">👷</span>
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Real-time progress tracking, site updates and construction management.</p>
            </div>
            <button
                @click="openAddUpdate"
                class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add Site Update
            </button>
        </div>

        <!-- KPI row -->
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
            <div v-for="card in kpiCards" :key="card.label" class="rounded-2xl border border-border bg-admin-surface-card p-4 transition-transform hover:-translate-y-0.5">
                <div class="mb-2.5 flex items-center gap-2">
                    <div class="flex flex-none items-center justify-center rounded-[10px]" :style="{ background: card.bg, color: card.color, width: '34px', height: '34px' }">
                        <svg v-if="card.path" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path :d="card.path"/></svg>
                        <svg v-else-if="card.circle" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 016.4 15.4L12 12z"/></svg>
                        <svg v-else-if="card.rect" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4M9 15l2 2 4-4"/></svg>
                        <svg v-else-if="card.clock" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <svg v-else-if="card.check" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        <svg v-else-if="card.wallet" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20M6 15h4"/></svg>
                    </div>
                    <span class="text-[11px] font-semibold leading-tight text-muted-foreground">{{ card.label }}</span>
                </div>
                <div class="text-2xl font-extrabold leading-none tracking-tight" :style="card.valueColor ? { color: card.valueColor } : {}">{{ card.value }}</div>
                <div class="mt-2.5 text-xs font-medium" :style="{ color: card.color }">{{ card.sub }}</div>
            </div>
        </div>

        <!-- Analytics grid -->
        <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-4">

            <!-- Progress Overview donut -->
            <div class="flex flex-col rounded-2xl border border-border bg-admin-surface-card p-5">
                <div class="text-base font-bold text-foreground">Progress Overview</div>
                <div class="mt-4 flex flex-1 items-center gap-4">
                    <div class="relative h-[130px] w-[130px] flex-none">
                        <svg width="130" height="130" viewBox="0 0 150 150" style="transform:rotate(-90deg);">
                            <circle cx="75" cy="75" r="58" fill="none" stroke="#F1F4F9" stroke-width="18"/>
                            <circle
                                v-for="seg in donutSegments" :key="seg.key"
                                cx="75" cy="75" r="58" fill="none" :stroke="seg.color" stroke-width="18"
                                :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-xl font-extrabold tracking-tight">{{ kpis.overall_progress }}%</div>
                            <div class="mt-0.5 text-[10px] text-muted-foreground">Overall Progress</div>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-2.5 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 flex-none rounded-full" style="background:#22C55E;"></span>Completed</span>
                            <b>{{ progressOverview.completed }}%</b>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 flex-none rounded-full" style="background:#3B82F6;"></span>In Progress</span>
                            <b>{{ progressOverview.in_progress }}%</b>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 flex-none rounded-full" style="background:#F59E0B;"></span>Pending</span>
                            <b>{{ progressOverview.pending }}%</b>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 text-foreground/80"><span class="h-2.5 w-2.5 flex-none rounded-full" style="background:#EF4444;"></span>Delayed</span>
                            <b>{{ progressOverview.delayed }}%</b>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Construction Progress Trend -->
            <div class="flex flex-col rounded-2xl border border-border bg-admin-surface-card p-5">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-base font-bold text-foreground">Construction Progress Trend</div>
                    <span class="rounded-lg border border-border px-2.5 py-1 text-[11px] font-semibold text-foreground/80">6 Months</span>
                </div>
                <div class="relative mt-3 min-h-[220px] flex-1">
                    <svg viewBox="0 0 640 270" preserveAspectRatio="none" class="block h-full w-full">
                        <defs><linearGradient id="cArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#5B3DF5" stop-opacity="0.22"/><stop offset="100%" stop-color="#5B3DF5" stop-opacity="0"/></linearGradient></defs>
                        <line v-for="y in [24,69,114,159,204,250]" :key="y" x1="40" :y1="y" x2="640" :y2="y" stroke="#F1F1F6" stroke-width="1"/>
                        <polygon :points="trendAreaPoints" fill="url(#cArea)"/>
                        <polyline :points="trendPolyline" fill="none" stroke="#5B3DF5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle v-for="p in trendPoints" :key="p.label" :cx="p.x" :cy="p.y" r="4" fill="#fff" stroke="#5B3DF5" stroke-width="2.5"/>
                    </svg>
                    <div class="absolute left-0 top-[14px] text-[9.5px] text-muted-foreground">100%</div>
                    <div class="absolute bottom-0 left-0 text-[9.5px] text-muted-foreground">0%</div>
                    <div v-if="latestTrend" class="absolute right-4 top-4 rounded-lg border border-border bg-admin-surface-card px-3 py-1.5 text-center shadow-md">
                        <div class="text-[10px] font-semibold text-muted-foreground">{{ latestTrend.label }}</div>
                        <div class="mt-0.5 text-sm font-extrabold text-admin-accent">{{ latestTrend.value }}%</div>
                    </div>
                </div>
                <div class="flex items-center justify-between px-1 pt-1 text-[10px] text-muted-foreground">
                    <span v-for="p in progressTrend" :key="p.label">{{ p.label }}</span>
                </div>
            </div>

            <!-- Progress by Project -->
            <div class="flex flex-col rounded-2xl border border-border bg-admin-surface-card p-5">
                <div class="mb-1 flex items-center justify-between">
                    <div class="text-base font-bold text-foreground">Progress by Project</div>
                </div>
                <div class="flex flex-1 flex-col justify-center divide-y divide-border/60">
                    <div v-if="progressByProject.length === 0" class="py-8 text-center text-xs text-muted-foreground">No active projects</div>
                    <div v-for="(pr, i) in progressByProject" :key="pr.name" class="flex items-center gap-2.5 py-2.5" :class="i > 0 && 'border-t border-border/60'">
                        <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[9px]" :style="{ background: pair(iconPairs, i)[0], color: pair(iconPairs, i)[1] }">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <span class="truncate text-[12.5px] font-semibold">{{ pr.name }}</span>
                                <b class="flex-none text-[12.5px]">{{ pr.pct }}%</b>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full" :style="{ width: pr.pct + '%', background: pr.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Milestones -->
            <div class="flex flex-col rounded-2xl border border-border bg-admin-surface-card p-5">
                <div class="mb-1 text-base font-bold text-foreground">Upcoming Milestones</div>
                <div class="flex flex-1 flex-col">
                    <div v-if="upcomingMilestones.length === 0" class="flex flex-1 items-center justify-center text-xs text-muted-foreground">No upcoming milestones</div>
                    <div v-for="(m, i) in upcomingMilestones" :key="m.title + m.project" class="flex items-center gap-2.5 py-2.5" :class="i > 0 && 'border-t border-border/60'">
                        <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: pair(milestonePairs, i)[0], color: pair(milestonePairs, i)[1] }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[12.5px] font-bold">{{ m.title }}</div>
                            <div class="truncate text-[11px] text-muted-foreground">{{ m.project }}</div>
                        </div>
                        <div class="flex-none whitespace-nowrap text-[11.5px] font-semibold text-foreground/80">{{ m.date }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second section -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_360px]">

            <!-- Construction Status table -->
            <div class="min-w-0 rounded-2xl border border-border bg-admin-surface-card p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3 px-2">
                    <div class="text-base font-bold text-foreground">Projects Construction Status</div>
                    <div class="relative w-full max-w-[260px]">
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <Input v-model="search" placeholder="Search project, location..." class="pl-9" />
                    </div>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="pl-4">Project</TableHead>
                            <TableHead>Location</TableHead>
                            <TableHead>Overall Progress</TableHead>
                            <TableHead>Time Progress</TableHead>
                            <TableHead>Quality Score</TableHead>
                            <TableHead>Budget Used</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Next Milestone</TableHead>
                            <TableHead class="pr-4">Target Date</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="table.data.length === 0">
                            <TableCell colspan="9" class="py-14 text-center text-muted-foreground">No projects found</TableCell>
                        </TableRow>
                        <TableRow v-for="row in table.data" :key="row.id">
                            <TableCell class="pl-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[9px]" :style="{ background: row.color + '22', color: row.color }">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h.01M15 17h.01"/></svg>
                                    </div>
                                    <span class="whitespace-nowrap text-[13px] font-bold">{{ row.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="whitespace-nowrap text-[12.5px] text-muted-foreground">{{ row.location }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <span class="w-9 flex-none text-[12.5px] font-bold" :style="{ color: row.color }">{{ row.overall }}%</span>
                                    <div class="h-1.5 min-w-[56px] flex-1 overflow-hidden rounded-full bg-muted"><div class="h-full rounded-full" :style="{ width: row.overall + '%', background: row.color }"></div></div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <span class="w-9 flex-none text-[12.5px] font-semibold">{{ row.time }}%</span>
                                    <div class="h-1.5 min-w-[56px] flex-1 overflow-hidden rounded-full bg-muted"><div class="h-full rounded-full bg-foreground/50" :style="{ width: Math.min(100, row.time) + '%' }"></div></div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <svg width="28" height="28" viewBox="0 0 36 36" style="transform:rotate(-90deg);">
                                        <circle cx="18" cy="18" r="15" fill="none" stroke="#F1F4F9" stroke-width="4"/>
                                        <circle cx="18" cy="18" r="15" fill="none" :stroke="qualityColor(row.quality)" stroke-width="4" stroke-linecap="round" :stroke-dasharray="qualityDash(row.quality)"/>
                                    </svg>
                                    <div class="leading-tight">
                                        <div class="text-[12.5px] font-bold">{{ row.quality }}%</div>
                                        <div class="text-[10.5px] font-semibold" :style="{ color: qualityColor(row.quality) }">{{ row.quality_label }}</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="whitespace-nowrap text-[13px] font-bold">{{ formatBDT(row.budget_used) }}</div>
                                <div class="text-[11px] text-muted-foreground">{{ row.budget_pct }}% used</div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="row.status_variant">{{ row.status_label }}</Badge>
                            </TableCell>
                            <TableCell class="whitespace-nowrap text-[12.5px] font-semibold text-foreground/80">{{ row.milestone }}</TableCell>
                            <TableCell class="pr-4 whitespace-nowrap text-[12.5px] text-muted-foreground">{{ row.target }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div v-if="table.last_page > 1" class="flex items-center justify-between border-t border-border px-4 pt-3">
                    <p class="text-xs text-muted-foreground">Showing {{ table.from }}–{{ table.to }} of {{ table.total }} projects</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in table.links" :key="link.label">
                            <span v-if="!link.url" class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs pointer-events-none text-muted-foreground/40"><span v-html="link.label" /></span>
                            <Link v-else :href="link.url" preserve-scroll preserve-state :class="['inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs transition-colors', link.active ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:bg-muted']"><span v-html="link.label" /></Link>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right stack -->
            <div class="flex min-w-0 flex-col gap-5">

                <!-- Site Activity Feed -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-5">
                    <div class="mb-2 text-base font-bold text-foreground">Site Activity Feed</div>
                    <div class="flex max-h-[330px] flex-col overflow-y-auto">
                        <div v-if="siteActivity.length === 0" class="py-8 text-center text-xs text-muted-foreground">No site updates yet</div>
                        <div v-for="(ac, i) in siteActivity" :key="ac.id" class="flex items-start gap-2.5 py-3" :class="i > 0 && 'border-t border-border/60'">
                            <img v-if="ac.photo" :src="ac.photo" class="h-8 w-8 flex-none rounded-[9px] object-cover" alt="" />
                            <div v-else class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: pair(activityPairs, i)[0], color: pair(activityPairs, i)[1] }">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18V8l7-4 7 4v13M9 12h.01M15 12h.01M9 16h.01M15 16h.01"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[12.5px] font-semibold leading-snug text-foreground">{{ ac.text }}</div>
                                <div class="mt-0.5 text-[11px] text-foreground/70">{{ ac.project }}</div>
                                <div class="mt-0.5 text-[10.5px] text-muted-foreground">{{ ac.time }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-5 rounded-2xl border border-border bg-admin-surface-card p-5">
            <div class="mb-3.5 text-base font-bold text-foreground">Quick Actions</div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9">
                <button
                    @click="openAddUpdate"
                    class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center transition-all hover:-translate-y-0.5 hover:border-admin-accent hover:bg-admin-accent/5"
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#F1ECFF; color:#5B3DF5;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Add Site Update</span>
                </button>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#E8F0FF; color:#3B82F6;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="9" cy="11" r="2"/><path d="M3 17l5-4 4 3 3-2 6 5"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Upload Photos</span>
                </div>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#EDE9FE; color:#7C3AED;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v18l7-4 7 4V3z"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Add Milestone</span>
                </div>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#E6F7EE; color:#22C55E;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Inspect Quality</span>
                </div>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#FFF3E0; color:#F59E0B;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 7-7M14 8h6v6"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Update Progress</span>
                </div>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#E6F4FB; color:#0EA5E9;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Manage Documents</span>
                </div>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#CCFBF1; color:#0D9488;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Create Report</span>
                </div>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#FDE8E8; color:#EF4444;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Safety Audit</span>
                </div>
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-border p-4 text-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]" style="background:#F1ECFF; color:#5B3DF5;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/></svg></div>
                    <span class="text-[11px] font-semibold text-foreground/80">Resource Planner</span>
                </div>
            </div>
        </div>

        <!-- Add Site Update Dialog -->
        <Dialog v-model:open="showAddUpdate">
            <DialogContent class="flex max-h-[88vh] w-full max-w-lg flex-col gap-0 overflow-hidden p-0">
                <DialogHeader class="shrink-0 border-b border-border px-6 py-4">
                    <DialogTitle>Add Site Update</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitUpdate" class="flex min-h-0 flex-1 flex-col">
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Project</Label>
                        <Select
                            :model-value="form.project_id ? String(form.project_id) : undefined"
                            @update:model-value="form.project_id = $event ? Number($event) : ''"
                        >
                            <SelectTrigger><SelectValue placeholder="Select a project" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="p in projectOptions" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.project_id" class="text-xs text-destructive">{{ form.errors.project_id }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Title</Label>
                        <Input v-model="form.title" placeholder="e.g. Concrete casting completed for 12th floor" />
                        <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Update Date</Label>
                        <DatePicker
                            :model-value="form.update_date"
                            @update:model-value="form.update_date = $event"
                            class="rounded-lg border border-border bg-transparent"
                        />
                        <p v-if="form.errors.update_date" class="text-xs text-destructive">{{ form.errors.update_date }}</p>
                    </div>

                    <div class="space-y-2.5 rounded-xl border border-border bg-muted/30 p-4" :class="!form.project_id && 'opacity-50'">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs font-medium text-muted-foreground">Progress</Label>
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="selectedProjectBaseline !== null && form.progress !== selectedProjectBaseline"
                                    class="flex items-center gap-0.5 text-[11px] font-semibold"
                                    :class="form.progress > selectedProjectBaseline ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                                >
                                    <svg v-if="form.progress > selectedProjectBaseline" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                                    <svg v-else width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                    {{ Math.abs(form.progress - selectedProjectBaseline) }}%
                                </span>
                                <span class="text-xl font-extrabold leading-none tabular-nums tracking-tight text-admin-accent">{{ form.progress }}<span class="text-sm font-bold">%</span></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-admin-accent transition-all duration-200" :style="{ width: form.progress + '%' }"></div>
                            </div>
                            <input
                                type="number" min="0" max="100" step="1"
                                :value="form.progress"
                                :disabled="!form.project_id"
                                @input="form.progress = Math.min(100, Math.max(0, Number($event.target.value) || 0))"
                                class="h-9 w-16 flex-none rounded-md border border-input bg-background px-2 text-center text-sm font-semibold tabular-nums focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed"
                            />
                        </div>

                        <p class="text-[11px] text-muted-foreground">
                            <template v-if="selectedProjectBaseline !== null">Current project progress: <b class="text-foreground/80">{{ selectedProjectBaseline }}%</b> — saving will update it to the value above.</template>
                            <template v-else>Select a project first to set its progress.</template>
                        </p>
                        <p v-if="form.errors.progress" class="text-xs text-destructive">{{ form.errors.progress }}</p>
                    </div>

                    <div class="space-y-2.5 rounded-xl border border-border bg-muted/30 p-4" :class="!form.project_id && 'opacity-50'">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs font-medium text-muted-foreground">Time Progress</Label>
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="selectedProjectTimeBaseline !== null && form.time_progress !== selectedProjectTimeBaseline"
                                    class="flex items-center gap-0.5 text-[11px] font-semibold"
                                    :class="form.time_progress > selectedProjectTimeBaseline ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                                >
                                    <svg v-if="form.time_progress > selectedProjectTimeBaseline" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                                    <svg v-else width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                    {{ Math.abs(form.time_progress - selectedProjectTimeBaseline) }}%
                                </span>
                                <span class="text-xl font-extrabold leading-none tabular-nums tracking-tight text-blue-600 dark:text-blue-400">{{ form.time_progress }}<span class="text-sm font-bold">%</span></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-blue-500 transition-all duration-200" :style="{ width: form.time_progress + '%' }"></div>
                            </div>
                            <input
                                type="number" min="0" max="100" step="1"
                                :value="form.time_progress"
                                :disabled="!form.project_id"
                                @input="form.time_progress = Math.min(100, Math.max(0, Number($event.target.value) || 0))"
                                class="h-9 w-16 flex-none rounded-md border border-input bg-background px-2 text-center text-sm font-semibold tabular-nums focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed"
                            />
                        </div>

                        <p class="text-[11px] text-muted-foreground">
                            <template v-if="selectedProjectTimeBaseline !== null">Current time progress: <b class="text-foreground/80">{{ selectedProjectTimeBaseline }}%</b> — schedule-based progress shown on the status table.</template>
                            <template v-else>Select a project first to set its time progress.</template>
                        </p>
                        <p v-if="form.errors.time_progress" class="text-xs text-destructive">{{ form.errors.time_progress }}</p>
                    </div>

                    <div class="space-y-2.5 rounded-xl border border-border bg-muted/30 p-4" :class="!form.project_id && 'opacity-50'">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs font-medium text-muted-foreground">Budget</Label>
                            <span class="text-xs font-semibold text-teal-600 dark:text-teal-400">{{ budgetUsedPct }}% used</span>
                        </div>

                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-teal-500 transition-all duration-200" :style="{ width: budgetUsedPct + '%' }"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <Label class="text-[11px] text-muted-foreground">Total Budget</Label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-medium text-muted-foreground">৳</span>
                                    <input
                                        type="number" min="0" step="1000"
                                        :value="form.budget_total"
                                        :disabled="!form.project_id"
                                        @input="form.budget_total = Math.max(0, Number($event.target.value) || 0)"
                                        class="h-9 w-full rounded-md border border-input bg-background py-2 pl-6 pr-2 text-sm font-semibold tabular-nums focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed"
                                    />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-[11px] text-muted-foreground">Used So Far</Label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-medium text-muted-foreground">৳</span>
                                    <input
                                        type="number" min="0" step="1000"
                                        :value="form.budget_used"
                                        :disabled="!form.project_id"
                                        @input="form.budget_used = Math.max(0, Number($event.target.value) || 0)"
                                        class="h-9 w-full rounded-md border border-input bg-background py-2 pl-6 pr-2 text-sm font-semibold tabular-nums focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed"
                                    />
                                </div>
                            </div>
                        </div>

                        <p class="text-[11px] text-muted-foreground">
                            <template v-if="selectedProjectBudget">Current: <b class="text-foreground/80">{{ formatBDT(selectedProjectBudget.total) }}</b> total, <b class="text-foreground/80">{{ formatBDT(selectedProjectBudget.used) }}</b> used.</template>
                            <template v-else>Select a project first to set its budget.</template>
                        </p>
                        <p v-if="form.errors.budget_total" class="text-xs text-destructive">{{ form.errors.budget_total }}</p>
                        <p v-if="form.errors.budget_used" class="text-xs text-destructive">{{ form.errors.budget_used }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Description</Label>
                        <Textarea v-model="form.description" rows="3" placeholder="Optional details about this update..." />
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Site Photo</Label>
                        <ImageUpload
                            :file="form.photo"
                            @update:file="form.photo = $event"
                            hint="JPG, PNG, WEBP · Max 5 MB"
                        />
                    </div>
                </div>

                    <DialogFooter class="shrink-0 border-t border-border px-6 py-4">
                        <button type="button" @click="showAddUpdate = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60">
                            <svg v-if="form.processing" class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            Save Update
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
