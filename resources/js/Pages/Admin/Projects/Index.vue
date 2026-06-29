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

// ── Status badge styles ────────────────────────────────────────
const statusStyle = {
    planning: { color: '#1D4ED8', bg: '#E8F0FF' },
    under_construction: { color: '#15803D', bg: '#E6F7EE' },
    completed: { color: '#15803D', bg: '#ECFDF3', border: '#BBF7D0' },
    on_hold: { color: '#B45309', bg: '#FFF3E0' },
    land: { color: '#6D28D9', bg: '#EDE9FE' },
    mixed_use: { color: '#0369A1', bg: '#E0F2FE' },
};

function badgeStyle(s) {
    const st = statusStyle[s] ?? { color: '#697386', bg: '#F1F4F9' };
    return `color:${st.color}; background:${st.bg}; ${st.border ? `border:1px solid ${st.border};` : ''}`;
}

// ── Avatar gradient per project index ─────────────────────────
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
        { label: 'Planning', count: props.stats.planning ?? 0, color: '#3B82F6' },
        { label: 'Under Construction', count: props.stats.under_construction ?? 0, color: '#22C55E' },
        { label: 'Completed', count: props.stats.completed ?? 0, color: '#5B3DF5' },
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
    { icon: 'project', color: '#5B3DF5', bg: '#F1ECFF', text: 'New project "Lakeside Residences" created', time: '2 hours ago' },
    { icon: 'unit', color: '#3B82F6', bg: '#E8F0FF', text: 'Unit A-102 added to Lakeside Residences', time: '5 hours ago' },
    { icon: 'build', color: '#F59E0B', bg: '#FFF3E0', text: 'Construction progress updated to 65% on Skyline Heights', time: '1 day ago' },
    { icon: 'client', color: '#22C55E', bg: '#E6F7EE', text: 'Client Md. Rahim registered from referral source', time: '2 days ago' },
];
</script>

<template>

    <Head title="Projects" />

    <AdminLayout>

        <!-- ── Page Header ─────────────────────────────────────────── -->
        <div class="mb-6 flex items-start justify-between gap-6">
            <div>
                <h1 style="font-size:28px; font-weight:800; letter-spacing:-0.5px; color:#151B2E; line-height:1.1;">
                    Projects</h1>
                <p style="font-size:14px; color:#697386; margin-top:5px;">Manage all your real estate projects in one
                    place.</p>
            </div>
            <Link :href="route('admin.projects.create')"
                class="inline-flex flex-shrink-0 items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-0.5"
                style="background:linear-gradient(135deg,#5B3DF5,#7C5CFF); box-shadow:0 8px 20px rgba(91,61,245,0.35);">
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
            <div
                class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                        style="background:#F1ECFF; color:#5B3DF5;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 11l9-7 9 7" />
                            <path d="M5 10v9h14v-9" />
                            <path d="M9 21v-5h6v5" />
                        </svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Projects</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">{{
                    stats.total }}</div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">
                    Active: <b style="color:#151B2E;">{{ (stats.under_construction ?? 0) + (stats.planning ?? 0) }}</b>
                    &nbsp;·&nbsp;
                    Completed: <b style="color:#151B2E;">{{ stats.completed ?? 0 }}</b>
                </div>
            </div>

            <!-- Total Units (static for now) -->
            <div
                class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                        style="background:#E8F0FF; color:#3B82F6;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8" />
                        </svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Units</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">155
                </div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">
                    Sold: <b style="color:#151B2E;">0</b>
                    &nbsp;·&nbsp;
                    Available: <b style="color:#151B2E;">155</b>
                </div>
            </div>

            <!-- Total Sales Value (static) -->
            <div
                class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                        style="background:#EDE9FE; color:#6D28D9;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V8l7-5 7 5v13M10 21v-5h4v5" />
                        </svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Sales Value</span>
                </div>
                <div style="font-size:22px; font-weight:800; letter-spacing:-0.5px; line-height:1; color:#151B2E;">BDT 0
                </div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">From <b style="color:#151B2E;">0</b> Sold
                    Units</div>
            </div>

            <!-- Avg. Progress (static) -->
            <div
                class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                        style="background:#E6F7EE; color:#22C55E;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 17l6-6 4 4 7-7M14 8h6v6" />
                        </svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Avg. Progress</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">—</div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">Across All Projects</div>
            </div>

            <!-- Upcoming Handover (static) -->
            <div
                class="rounded-[18px] border border-border bg-white px-5 py-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-3.5 flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                        style="background:#FFF3E0; color:#F59E0B;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#697386;">Upcoming Handover</span>
                </div>
                <div style="font-size:30px; font-weight:800; letter-spacing:-1px; line-height:1; color:#151B2E;">
                    0 <span style="font-size:18px; font-weight:600; color:#697386;">Projects</span>
                </div>
                <div style="font-size:12px; color:#697386; margin-top:8px;">In Next 6 Months</div>
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
                            class="rounded-xl px-3.5 py-2 text-sm font-medium transition-all duration-150" :style="status === tab.value
                                ? 'background:linear-gradient(135deg,#5B3DF5,#7C5CFF); color:#fff; box-shadow:0 6px 16px rgba(91,61,245,0.35);'
                                : 'color:#697386; background:transparent;'"
                            :class="status !== tab.value && 'hover:bg-white hover:text-foreground hover:shadow-sm'">{{
                                tab.label }}</button>
                    </div>

                    <!-- Type filter + Filter btn -->
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2"
                                style="color:#A0A8B8;" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4-4" />
                            </svg>
                            <input v-model="search" type="text" placeholder="Search..."
                                class="h-9 rounded-xl border border-border bg-white pl-9 pr-3 text-sm outline-none transition-colors focus:border-admin-accent focus:ring-2 focus:ring-admin-accent/20"
                                style="width:180px; box-shadow:0 2px 6px rgba(0,0,0,0.03);" />
                        </div>
                        <Select :model-value="type || undefined" @update:model-value="type = $event ?? ''">
                            <SelectTrigger class="h-9 w-36 rounded-xl border-border bg-white text-sm"
                                style="box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                                <SelectValue placeholder="All types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <button v-if="type" @click="type = ''"
                            class="flex h-9 items-center gap-1.5 rounded-xl border border-border bg-white px-3 text-xs font-medium text-muted-foreground transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-500">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M18 6L6 18M6 6l12 12" />
                            </svg>
                            type
                        </button>
                    </div>
                </div>

                <!-- Table card -->
                <div class="overflow-hidden rounded-[20px] border border-border bg-white shadow-card">
                    <div class="overflow-x-auto">
                        <table style="width:100%; min-width:640px; border-collapse:collapse;">
                            <thead>
                                <tr style="text-align:left;">
                                    <th
                                        style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:14px 16px;">
                                        Project</th>
                                    <th
                                        style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:14px 12px;">
                                        Type</th>
                                    <th
                                        style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:14px 12px;">
                                        Units</th>
                                    <th
                                        style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:14px 12px;">
                                        Progress</th>
                                    <th
                                        style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:14px 12px;">
                                        Status</th>
                                    <th
                                        style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:14px 16px; text-align:right;">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Empty state -->
                                <tr v-if="projects.data.length === 0">
                                    <td colspan="6" style="padding:48px 16px; text-align:center; color:#9AA3B4;">
                                        <svg style="margin:0 auto 12px; color:#D6DBE6;" width="40" height="40"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                            <path d="M2 20h20M6 20V10l6-7 6 7v10" />
                                            <path d="M10 20v-5h4v5" />
                                        </svg>
                                        <div style="font-size:14px; font-weight:600;">No projects found</div>
                                        <div style="font-size:12px; margin-top:4px;">Try adjusting your filters</div>
                                    </td>
                                </tr>

                                <!-- Project rows -->
                                <tr v-for="(project, i) in projects.data" :key="project.id"
                                    style="transition:background .15s; cursor:pointer;" class="hover:bg-[#FAFBFE]">
                                    <!-- Project name + location -->
                                    <td style="padding:14px 16px; border-top:1px solid #F5F7FB;">
                                        <div style="display:flex; align-items:center; gap:13px;">
                                            <div style="width:50px; height:50px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden;"
                                                :style="project.cover ? '' : `background:${projectGradient(project.id)}`">
                                                <img v-if="project.cover" :src="project.cover" :alt="project.name"
                                                    style="width:100%; height:100%; object-fit:cover;" />
                                                <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none"
                                                    stroke="#fff" stroke-width="1.6" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path
                                                        d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2" />
                                                </svg>
                                            </div>
                                            <div>
                                                <Link :href="route('admin.projects.edit', project.id)"
                                                    style="font-size:14px; font-weight:700; color:#5B3DF5; text-decoration:none;"
                                                    class="hover:underline">{{ project.name }}</Link>
                                                <div v-if="project.location"
                                                    style="font-size:12px; color:#9AA3B4; margin-top:2px;">{{
                                                        project.location }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td style="padding:14px 12px; border-top:1px solid #F5F7FB;">
                                        <span style="font-size:12px; color:#697386; font-weight:500;">{{
                                            project.type_label }}</span>
                                    </td>

                                    <!-- Units count -->
                                    <td
                                        style="padding:14px 12px; border-top:1px solid #F5F7FB; font-size:14px; font-weight:600; color:#151B2E;">
                                        {{ project.units_count }}
                                        <span v-if="project.total_units"
                                            style="color:#9AA3B4; font-size:12px; font-weight:400;"> / {{
                                                project.total_units }}</span>
                                    </td>

                                    <!-- Progress bar -->
                                    <td style="padding:14px 12px; border-top:1px solid #F5F7FB;">
                                        <div style="display:flex; align-items:center; gap:9px;">
                                            <div
                                                style="width:72px; height:6px; background:#F1F4F9; border-radius:4px; overflow:hidden;">
                                                <div
                                                    :style="`height:100%; width:${project.overall_progress ?? 0}%; background:#5B3DF5; border-radius:4px;`" />
                                            </div>
                                            <span style="font-size:12px; font-weight:700; color:#3A4256;">{{
                                                project.overall_progress ?? 0 }}%</span>
                                        </div>
                                    </td>

                                    <!-- Status badge -->
                                    <td style="padding:14px 12px; border-top:1px solid #F5F7FB;">
                                        <span
                                            style="font-size:11.5px; font-weight:700; padding:5px 11px; border-radius:20px; white-space:nowrap;"
                                            :style="badgeStyle(project.status)">{{ project.status_label }}</span>
                                    </td>

                                    <!-- Actions -->
                                    <td style="padding:14px 16px; border-top:1px solid #F5F7FB; text-align:right;">
                                        <div
                                            style="display:flex; align-items:center; justify-content:flex-end; gap:4px;">

                                            <!-- Blueprint -->
                                            <Link :href="route('admin.blueprint.show', project.id)"
                                                style="height:28px; border-radius:8px; display:inline-flex; align-items:center; gap:5px; padding:0 10px; font-size:11px; font-weight:600; color:#5B3DF5; background:#EEF2FF; text-decoration:none; transition:all .15s; white-space:nowrap;"
                                                class="hover:bg-[#5B3DF5] hover:!text-white" title="Unit Blueprint">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                                                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                                                </svg>
                                                Blueprint
                                            </Link>

                                            <Link :href="route('admin.projects.edit', project.id)"
                                                style="width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#9AA3B4; text-decoration:none; transition:all .15s;"
                                                class="hover:bg-[#F1F4F9] hover:text-[#5B3DF5]" title="Edit">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path
                                                        d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </Link>
                                            <button @click="confirmDelete(project)"
                                                style="width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#9AA3B4; border:none; background:transparent; cursor:pointer; transition:all .15s;"
                                                class="hover:bg-red-50 hover:text-red-500" title="Delete">
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
                        <span style="font-size:12.5px; color:#697386;">
                            Showing {{ projects.from }}–{{ projects.to }} of {{ projects.total }} projects
                        </span>
                        <div class="flex items-center gap-1.5">
                            <template v-for="link in projects.links" :key="link.label">
                                <span v-if="!link.url"
                                    class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-[9px] px-2 text-xs pointer-events-none"
                                    style="color:#9AA3B4; border:1px solid #EEF2F8; background:#fff;"><span
                                        v-html="link.label" /></span>
                                <Link v-else :href="link.url" :preserve-state="true"
                                    class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-[9px] px-2 text-xs transition-colors"
                                    :style="link.active
                                        ? 'background:#5B3DF5; color:#fff; border:none;'
                                        : 'background:#fff; color:#9AA3B4; border:1px solid #EEF2F8;'"><span
                                        v-html="link.label" /></Link>
                            </template>
                        </div>
                    </div>
                    <!-- Showing count when only 1 page -->
                    <div v-else-if="projects.total > 0" class="border-t border-border px-4 py-3">
                        <span style="font-size:12.5px; color:#697386;">Showing {{ projects.total }} of {{ projects.total
                        }} projects</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: sticky sidebar widgets ─────────────────────── -->
            <div class="flex flex-col gap-6" style="position:sticky; top:0;">

                <!-- Project Status Overview -->
                <div class="rounded-[20px] border border-border bg-white shadow-card" style="padding:24px;">
                    <div style="font-size:16px; font-weight:700; color:#151B2E; margin-bottom:16px;">Project Status
                        Overview</div>
                    <div style="display:flex; align-items:center; gap:16px;">

                        <!-- SVG Donut -->
                        <div style="position:relative; width:130px; height:130px; flex-shrink:0;">
                            <svg width="130" height="130" viewBox="0 0 140 140" style="transform:rotate(-90deg);">
                                <circle cx="70" cy="70" r="56" fill="none" stroke="#F1F4F9" stroke-width="16" />
                                <circle v-for="seg in donutSegments" :key="seg.label" cx="70" cy="70" r="56" fill="none"
                                    :stroke="seg.color" stroke-width="16" :stroke-dasharray="seg.dasharray"
                                    :stroke-dashoffset="seg.dashoffset" />
                            </svg>
                            <div
                                style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <div style="font-size:24px; font-weight:800; color:#151B2E;">{{ stats.total }}</div>
                                <div style="font-size:10px; color:#697386;">Total</div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div style="flex:1; display:flex; flex-direction:column; gap:10px;">
                            <div
                                style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;">
                                <span style="display:flex; align-items:center; gap:7px; color:#697386;">
                                    <span
                                        style="width:8px; height:8px; border-radius:50%; background:#22C55E; flex-shrink:0;"></span>
                                    Under Construction
                                </span>
                                <span style="font-weight:700; color:#151B2E;">{{ stats.under_construction ?? 0 }}</span>
                            </div>
                            <div
                                style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;">
                                <span style="display:flex; align-items:center; gap:7px; color:#697386;">
                                    <span
                                        style="width:8px; height:8px; border-radius:50%; background:#5B3DF5; flex-shrink:0;"></span>
                                    Completed
                                </span>
                                <span style="font-weight:700; color:#151B2E;">{{ stats.completed ?? 0 }}</span>
                            </div>
                            <div
                                style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;">
                                <span style="display:flex; align-items:center; gap:7px; color:#697386;">
                                    <span
                                        style="width:8px; height:8px; border-radius:50%; background:#3B82F6; flex-shrink:0;"></span>
                                    Planning
                                </span>
                                <span style="font-weight:700; color:#151B2E;">{{ stats.planning ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Projects -->
                <div class="rounded-[20px] border border-border bg-white shadow-card" style="padding:24px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                        <div style="font-size:16px; font-weight:700; color:#151B2E;">Top Performing</div>
                        <a style="font-size:12.5px; font-weight:600; color:#5B3DF5; cursor:pointer;">View All</a>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div v-for="p in topProjects" :key="p.name" style="display:flex; align-items:center; gap:12px;">
                            <div style="width:44px; height:44px; border-radius:11px; flex-shrink:0; display:flex; align-items:center; justify-content:center;"
                                :style="`background:${p.gradient}`">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff"
                                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11" />
                                </svg>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span
                                        style="font-size:13px; font-weight:700; color:#151B2E; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:150px;">{{
                                            p.name }}</span>
                                    <span style="font-size:12.5px; font-weight:700; color:#22C55E;">{{ p.salesRate
                                    }}%</span>
                                </div>
                                <div style="font-size:11px; color:#9AA3B4; margin:3px 0 6px;">Sales Rate</div>
                                <div style="height:5px; background:#F1F4F9; border-radius:3px; overflow:hidden;">
                                    <div
                                        :style="`height:100%; width:${p.salesRate}%; background:#5B3DF5; border-radius:3px;`" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="rounded-[20px] border border-border bg-white shadow-card" style="padding:24px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                        <div style="font-size:16px; font-weight:700; color:#151B2E;">Recent Activities</div>
                        <a style="font-size:12.5px; font-weight:600; color:#5B3DF5; cursor:pointer;">View All</a>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <div v-for="(act, i) in recentActivities" :key="i"
                            style="display:flex; gap:11px; padding:10px 6px; border-radius:11px; cursor:pointer; transition:background .15s;"
                            class="hover:bg-[#FAFBFE]">
                            <div style="width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center;"
                                :style="`background:${act.bg}; color:${act.color};`">
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
                                <div style="font-size:12.5px; font-weight:600; color:#151B2E; line-height:1.4;">{{
                                    act.text }}</div>
                                <div style="font-size:11px; color:#9AA3B4; margin-top:2px;">{{ act.time }}</div>
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
                    <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-white p-6 shadow-xl">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600">
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
                                class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-60">
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
