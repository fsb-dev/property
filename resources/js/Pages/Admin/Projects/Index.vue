<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

const props = defineProps({
    projects: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, required: true },
    enums: { type: Object, required: true },
});

// ── Filters ────────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type ?? '');
const status = ref(props.filters.status ?? '');

let searchTimer;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});
watch([type, status], applyFilters);

function applyFilters() {
    router.get(route('admin.projects.index'), {
        search: search.value || undefined,
        type: type.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}

function setTab(val) { status.value = val; }

// ── Delete confirm ─────────────────────────────────────────────
const deleteForm = useForm({});
const confirmingDelete = ref(null);
function confirmDelete(project) { confirmingDelete.value = project; }
function cancelDelete() { confirmingDelete.value = null; }
function submitDelete() {
    deleteForm.delete(route('admin.projects.destroy', confirmingDelete.value.id), {
        onSuccess: () => { confirmingDelete.value = null; },
    });
}

// ── Status badge styles — planning=info, under_construction=warning,
//    completed=success, on_hold=warning; land/mixed_use are project TYPES
//    reusing this same map, given the supporting chart palette. ─────
const statusClass = {
    planning: 'bg-info/15 text-info',
    under_construction: 'bg-warning/15 text-warning',
    completed: 'bg-success/15 text-success',
    on_hold: 'bg-warning/15 text-warning',
    land: 'bg-chart-4/15 text-chart-4',
    mixed_use: 'bg-chart-6/15 text-chart-6',
};
function badgeClass(s) {
    return statusClass[s] ?? 'bg-muted text-muted-foreground';
}
const statusDotColor = {
    planning: '#60A5FA',
    under_construction: '#FBBF24',
    completed: '#34D399',
};

// ── Avatar gradient per project index (decorative, cover fallback) ─
const gradients = [
    'linear-gradient(140deg,#C7D2FE,#818CF8)',
    'linear-gradient(140deg,#BBF7D0,#34D399)',
    'linear-gradient(140deg,#BFDBFE,#60A5FA)',
    'linear-gradient(140deg,#FDE68A,#FBBF24)',
    'linear-gradient(140deg,#DDD6FE,#A78BFA)',
    'linear-gradient(140deg,#A7F3D0,#10B981)',
    'linear-gradient(140deg,#FBCFE8,#F472B6)',
    'linear-gradient(140deg,#99F6E4,#14B8A6)',
];
function projectGradient(id) { return gradients[id % gradients.length]; }

// ── Status overview donut ──────────────────────────────────────
const CIRC = 351.86; // 2π × 56
const donutSegments = computed(() => {
    const total = props.stats.total || 1;
    const raw = [
        { label: 'Planning', count: props.stats.planning ?? 0, color: statusDotColor.planning },
        { label: 'Under Construction', count: props.stats.under_construction ?? 0, color: statusDotColor.under_construction },
        { label: 'Completed', count: props.stats.completed ?? 0, color: statusDotColor.completed },
    ];
    let offset = 0;
    return raw.map(s => {
        const len = (s.count / total) * CIRC;
        const seg = { ...s, dasharray: `${len.toFixed(1)} ${CIRC.toFixed(1)}`, dashoffset: `${(-offset).toFixed(1)}` };
        offset += len;
        return seg;
    });
});

// ── Tab definitions ────────────────────────────────────────────
const tabs = [
    { label: 'All', value: '' },
    { label: 'Planning', value: 'planning' },
    { label: 'Under Construction', value: 'under_construction' },
    { label: 'Completed', value: 'completed' },
];

// ── Dummy sidebar data (static until modules exist) ────────────
const topProjects = [
    { name: 'Lakeside Residences', salesRate: 72, gradient: 'linear-gradient(140deg,#C7D2FE,#818CF8)' },
    { name: 'Skyline Heights', salesRate: 58, gradient: 'linear-gradient(140deg,#A7F3D0,#10B981)' },
    { name: 'Green Valley Villas', salesRate: 45, gradient: 'linear-gradient(140deg,#BBF7D0,#34D399)' },
];

const recentActivities = [
    { icon: 'project', class: 'bg-gold/10 text-gold', text: 'New project "Lakeside Residences" created', time: '2 hours ago' },
    { icon: 'unit', class: 'bg-info/10 text-info', text: 'Unit A-102 added to Lakeside Residences', time: '5 hours ago' },
    { icon: 'build', class: 'bg-warning/10 text-warning', text: 'Construction progress updated to 65% on Skyline Heights', time: '1 day ago' },
    { icon: 'client', class: 'bg-success/10 text-success', text: 'Client Md. Rahim registered from referral source', time: '2 days ago' },
];
</script>

<template>

    <Head title="Projects" />

    <AdminLayout>

        <!-- ── Page Header ─────────────────────────────────────────── -->
        <div class="mb-6 flex items-start justify-between gap-6">
            <div>
                <h1 class="text-[28px] font-extrabold tracking-tight text-foreground leading-none">Projects</h1>
                <p class="mt-1.5 text-sm text-muted-foreground">Manage all your real estate projects in one place.</p>
            </div>
            <Link :href="route('admin.projects.create')"
                class="inline-flex flex-shrink-0 items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-on-gold bg-gold-gradient shadow-[0_8px_20px_rgba(198,161,91,0.30)] transition-all hover:-translate-y-0.5 hover:shadow-gold-glow">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Add New Project
            </Link>
        </div>

        <!-- ── KPI Cards (5-col) ───────────────────────────────────── -->
        <div class="mb-6 grid grid-cols-2 gap-5 lg:grid-cols-5">

            <!-- Total Projects -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 text-gold">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 11l9-7 9 7" />
                            <path d="M5 10v9h14v-9" />
                            <path d="M9 21v-5h6v5" />
                        </svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Total Projects</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground hv-num">{{ stats.total }}</div>
                <div class="mt-2 text-xs text-muted-foreground">
                    Active: <b class="text-foreground">{{ (stats.under_construction ?? 0) + (stats.planning ?? 0) }}</b>
                    &nbsp;·&nbsp;
                    Completed: <b class="text-foreground">{{ stats.completed ?? 0 }}</b>
                </div>
            </div>

            <!-- Total Units (static for now) -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8" />
                        </svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Total Units</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground hv-num">155</div>
                <div class="mt-2 text-xs text-muted-foreground">
                    Sold: <b class="text-foreground">0</b>
                    &nbsp;·&nbsp;
                    Available: <b class="text-foreground">155</b>
                </div>
            </div>

            <!-- Total Sales Value (static) -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-success/10 text-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V8l7-5 7 5v13M10 21v-5h4v5" />
                        </svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Total Sales Value</span>
                </div>
                <div class="text-[22px] font-extrabold tracking-tight leading-none text-foreground hv-num">BDT 0</div>
                <div class="mt-2 text-xs text-muted-foreground">From <b class="text-foreground">0</b> Sold Units</div>
            </div>

            <!-- Avg. Progress (static) -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-chart-4/10 text-chart-4">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 17l6-6 4 4 7-7M14 8h6v6" />
                        </svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Avg. Progress</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground">—</div>
                <div class="mt-2 text-xs text-muted-foreground">Across All Projects</div>
            </div>

            <!-- Upcoming Handover (static) -->
            <div class="rounded-[18px] border border-border bg-card px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                    </div>
                    <span class="text-[12.5px] font-semibold text-muted-foreground">Upcoming Handover</span>
                </div>
                <div class="text-[30px] font-extrabold tracking-tight leading-none text-foreground">
                    0 <span class="text-lg font-semibold text-muted-foreground">Projects</span>
                </div>
                <div class="mt-2 text-xs text-muted-foreground">In Next 6 Months</div>
            </div>

        </div>

        <!-- ── Main Grid: Table Left + Widgets Right ───────────────── -->
        <div class="grid gap-6" style="grid-template-columns:1fr 320px; align-items:start;">

            <!-- LEFT ─────────────────────────────────────────────── -->
            <div class="flex flex-col gap-4 min-w-0">

                <!-- Status tabs + toolbar row -->
                <div class="flex items-center justify-between gap-4 flex-wrap">

                    <!-- Status tabs -->
                    <div class="flex items-center gap-1">
                        <button v-for="tab in tabs" :key="tab.value" @click="setTab(tab.value)"
                            class="rounded-xl px-3.5 py-2 text-sm font-medium transition-all duration-150"
                            :class="status === tab.value
                                ? 'bg-gold-gradient text-on-gold shadow-[0_6px_16px_rgba(198,161,91,0.30)]'
                                : 'text-muted-foreground bg-transparent hover:bg-card hover:text-foreground hover:shadow-sm'">
                            {{ tab.label }}
                        </button>
                    </div>

                    <!-- Type filter + Filter btn -->
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                                width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4-4" />
                            </svg>
                            <input v-model="search" type="text" placeholder="Search..."
                                class="h-9 w-[180px] rounded-xl border border-border bg-background pl-9 pr-3 text-sm text-foreground outline-none transition-colors focus:border-gold focus:ring-2 focus:ring-ring/25" />
                        </div>
                        <Select :model-value="type || undefined" @update:model-value="type = $event ?? ''">
                            <SelectTrigger class="h-9 w-36 rounded-xl border-border bg-background text-sm">
                                <SelectValue placeholder="All types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <button v-if="type" @click="type = ''"
                            class="flex h-9 items-center gap-1.5 rounded-xl border border-border bg-background px-3 text-xs font-medium text-muted-foreground transition-colors hover:border-destructive/40 hover:bg-destructive/10 hover:text-destructive">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M18 6L6 18M6 6l12 12" />
                            </svg>
                            type
                        </button>
                    </div>
                </div>

                <!-- Table card -->
                <div class="overflow-hidden rounded-[20px] border border-border bg-card shadow-card">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] border-collapse">
                            <thead>
                                <tr class="text-left">
                                    <th class="px-4 py-3.5 text-[11.5px] font-semibold uppercase tracking-wider text-muted-foreground">Project</th>
                                    <th class="px-3 py-3.5 text-[11.5px] font-semibold uppercase tracking-wider text-muted-foreground">Type</th>
                                    <th class="px-3 py-3.5 text-[11.5px] font-semibold uppercase tracking-wider text-muted-foreground">Units</th>
                                    <th class="px-3 py-3.5 text-[11.5px] font-semibold uppercase tracking-wider text-muted-foreground">Progress</th>
                                    <th class="px-3 py-3.5 text-[11.5px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                    <th class="px-4 py-3.5 text-right text-[11.5px] font-semibold uppercase tracking-wider text-muted-foreground"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Empty state -->
                                <tr v-if="projects.data.length === 0">
                                    <td colspan="6" class="px-4 py-12 text-center text-muted-foreground">
                                        <svg class="mx-auto mb-3 text-muted-foreground/50" width="40" height="40"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                            <path d="M2 20h20M6 20V10l6-7 6 7v10" />
                                            <path d="M10 20v-5h4v5" />
                                        </svg>
                                        <div class="text-sm font-semibold">No projects found</div>
                                        <div class="mt-1 text-xs">Try adjusting your filters</div>
                                    </td>
                                </tr>

                                <!-- Project rows -->
                                <tr v-for="(project, i) in projects.data" :key="project.id"
                                    class="cursor-pointer transition-colors hover:bg-muted">
                                    <!-- Project name + location -->
                                    <td class="border-t border-border px-4 py-3.5">
                                        <div class="flex items-center gap-3.5">
                                            <div class="flex h-[50px] w-[50px] flex-shrink-0 items-center justify-center overflow-hidden rounded-xl"
                                                :style="project.cover ? '' : `background:${projectGradient(project.id)}`">
                                                <img v-if="project.cover" :src="project.cover" :alt="project.name"
                                                    class="h-full w-full object-cover" />
                                                <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none"
                                                    stroke="#fff" stroke-width="1.6" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path
                                                        d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2" />
                                                </svg>
                                            </div>
                                            <div>
                                                <Link :href="route('admin.projects.edit', project.id)"
                                                    class="text-sm font-bold text-brand hover:underline">{{ project.name }}</Link>
                                                <div v-if="project.location" class="mt-0.5 text-xs text-muted-foreground">{{ project.location }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td class="border-t border-border px-3 py-3.5">
                                        <span class="text-xs font-medium text-muted-foreground">{{ project.type_label }}</span>
                                    </td>

                                    <!-- Units count -->
                                    <td class="border-t border-border px-3 py-3.5 text-sm font-semibold text-foreground hv-num">
                                        {{ project.units_count }}
                                        <span v-if="project.total_units" class="text-xs font-normal text-muted-foreground"> / {{ project.total_units }}</span>
                                    </td>

                                    <!-- Progress bar -->
                                    <td class="border-t border-border px-3 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-1.5 w-[72px] overflow-hidden rounded bg-muted">
                                                <div class="h-full rounded bg-gold-gradient" :style="`width:${project.overall_progress ?? 0}%`" />
                                            </div>
                                            <span class="text-xs font-bold text-foreground hv-num">{{ project.overall_progress ?? 0 }}%</span>
                                        </div>
                                    </td>

                                    <!-- Status badge -->
                                    <td class="border-t border-border px-3 py-3.5">
                                        <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[11.5px] font-bold" :class="badgeClass(project.status)">{{ project.status_label }}</span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="border-t border-border px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1">

                                            <!-- Blueprint -->
                                            <Link :href="route('admin.blueprint.show', project.id)"
                                                class="inline-flex h-7 items-center gap-1.5 whitespace-nowrap rounded-lg bg-gold/10 px-2.5 text-[11px] font-semibold text-brand transition-all hover:bg-gold-gradient hover:text-on-gold"
                                                title="Unit Blueprint">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                                                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                                                </svg>
                                                Blueprint
                                            </Link>

                                            <Link :href="route('admin.projects.edit', project.id)"
                                                class="flex h-[30px] w-[30px] items-center justify-center rounded-lg text-muted-foreground transition-all hover:bg-muted hover:text-gold" title="Edit">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path
                                                        d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </Link>
                                            <button @click="confirmDelete(project)"
                                                class="flex h-[30px] w-[30px] items-center justify-center rounded-lg border-0 bg-transparent text-muted-foreground transition-all hover:bg-destructive/10 hover:text-destructive" title="Delete">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                    <path d="M10 11v6M14 11v6" />
                                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="projects.last_page > 1"
                        class="flex items-center justify-between border-t border-border px-4 py-3">
                        <span class="text-[12.5px] text-muted-foreground">
                            Showing {{ projects.from }}–{{ projects.to }} of {{ projects.total }} projects
                        </span>
                        <div class="flex items-center gap-1.5">
                            <template v-for="link in projects.links" :key="link.label">
                                <span v-if="!link.url"
                                    class="pointer-events-none inline-flex h-8 min-w-[2rem] items-center justify-center rounded-[9px] border border-border bg-card px-2 text-xs text-muted-foreground"><span v-html="link.label" /></span>
                                <Link v-else :href="link.url" :preserve-state="true"
                                    class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-[9px] px-2 text-xs transition-colors"
                                    :class="link.active
                                        ? 'bg-gold-gradient text-on-gold'
                                        : 'border border-border bg-card text-muted-foreground hover:bg-muted'"><span v-html="link.label" /></Link>
                            </template>
                        </div>
                    </div>
                    <!-- Showing count when only 1 page -->
                    <div v-else-if="projects.total > 0" class="border-t border-border px-4 py-3">
                        <span class="text-[12.5px] text-muted-foreground">Showing {{ projects.total }} of {{ projects.total }} projects</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: sticky sidebar widgets ─────────────────────── -->
            <div class="flex flex-col gap-6" style="position:sticky; top:0;">

                <!-- Project Status Overview -->
                <div class="rounded-[20px] border border-border bg-card p-6 shadow-card">
                    <div class="mb-4 text-base font-bold text-foreground">Project Status Overview</div>
                    <div class="flex items-center gap-4">

                        <!-- SVG Donut -->
                        <div class="relative h-[130px] w-[130px] flex-shrink-0">
                            <svg width="130" height="130" viewBox="0 0 140 140" style="transform:rotate(-90deg);">
                                <circle cx="70" cy="70" r="56" fill="none" class="stroke-muted" stroke-width="16" />
                                <circle v-for="seg in donutSegments" :key="seg.label" cx="70" cy="70" r="56" fill="none"
                                    :stroke="seg.color" stroke-width="16" :stroke-dasharray="seg.dasharray"
                                    :stroke-dashoffset="seg.dashoffset" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-2xl font-extrabold text-foreground hv-num">{{ stats.total }}</div>
                                <div class="text-[10px] text-muted-foreground">Total</div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-1 flex-col gap-2.5">
                            <div class="flex items-center justify-between text-[12.5px]">
                                <span class="flex items-center gap-1.5 text-muted-foreground">
                                    <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="`background:${statusDotColor.under_construction}`"></span>
                                    Under Construction
                                </span>
                                <span class="font-bold text-foreground hv-num">{{ stats.under_construction ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[12.5px]">
                                <span class="flex items-center gap-1.5 text-muted-foreground">
                                    <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="`background:${statusDotColor.completed}`"></span>
                                    Completed
                                </span>
                                <span class="font-bold text-foreground hv-num">{{ stats.completed ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[12.5px]">
                                <span class="flex items-center gap-1.5 text-muted-foreground">
                                    <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="`background:${statusDotColor.planning}`"></span>
                                    Planning
                                </span>
                                <span class="font-bold text-foreground hv-num">{{ stats.planning ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Projects -->
                <div class="rounded-[20px] border border-border bg-card p-6 shadow-card">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Top Performing</div>
                        <a class="cursor-pointer text-[12.5px] font-semibold text-brand hover:underline">View All</a>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div v-for="p in topProjects" :key="p.name" class="flex items-center gap-3">
                            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl"
                                :style="`background:${p.gradient}`">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff"
                                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="max-w-[150px] truncate text-[13px] font-bold text-foreground">{{ p.name }}</span>
                                    <span class="text-[12.5px] font-bold text-success hv-num">{{ p.salesRate }}%</span>
                                </div>
                                <div class="my-1 text-[11px] text-muted-foreground">Sales Rate</div>
                                <div class="h-[5px] overflow-hidden rounded bg-muted">
                                    <div class="h-full rounded bg-gold-gradient" :style="`width:${p.salesRate}%`" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="rounded-[20px] border border-border bg-card p-6 shadow-card">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Recent Activities</div>
                        <a class="cursor-pointer text-[12.5px] font-semibold text-brand hover:underline">View All</a>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <div v-for="(act, i) in recentActivities" :key="i"
                            class="flex cursor-pointer gap-2.5 rounded-xl p-2.5 transition-colors hover:bg-muted">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full" :class="act.class">
                                <!-- project icon -->
                                <svg v-if="act.icon === 'project'" width="15" height="15" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11" />
                                </svg>
                                <!-- unit icon -->
                                <svg v-else-if="act.icon === 'unit'" width="15" height="15" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8" />
                                </svg>
                                <!-- build icon -->
                                <svg v-else-if="act.icon === 'build'" width="15" height="15" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M4 16a8 8 0 0116 0M2 16h20M9 9V6a3 3 0 016 0v3" />
                                </svg>
                                <!-- client icon -->
                                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[12.5px] font-semibold leading-snug text-foreground">{{ act.text }}</div>
                                <div class="mt-0.5 text-[11px] text-muted-foreground">{{ act.time }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Delete Confirm Dialog -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="confirmingDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelDelete" />
                    <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-xl">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-destructive/15">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-destructive">
                                <polyline points="3 6 5 6 21 6" />
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                <path d="M10 11v6M14 11v6" />
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-foreground">Delete Project</h3>
                        <p class="mb-5 text-sm text-muted-foreground">
                            Are you sure you want to delete <strong class="text-foreground">{{ confirmingDelete.name
                            }}</strong>? This cannot be undone.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button @click="cancelDelete"
                                class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Cancel</button>
                            <button @click="submitDelete" :disabled="deleteForm.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground transition-colors hover:bg-destructive/90 disabled:opacity-60">
                                <svg v-if="deleteForm.processing" class="animate-spin" width="13" height="13"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
