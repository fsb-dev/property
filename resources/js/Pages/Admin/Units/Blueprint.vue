<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/Components/ui/select';
import { Tooltip } from '@/Components/ui/tooltip';

const props = defineProps({
    project: { type: Object, required: true },
    buildings: { type: Array, required: true },
    enums: { type: Object, required: true },
});

// ── State ──────────────────────────────────────────────────────────────────
const buildings = ref(props.buildings);
const activeBldg = ref(0);
const activeSection = ref(0);
const activeFloor = ref(null);
const generateCount = ref(8);
const generating = ref(false);
const generatingBlock = ref(false);
const applyingConfig = ref(false);
const toast = ref(null);

const currentBuilding = computed(() => buildings.value[activeBldg.value] ?? null);
const sections = computed(() => currentBuilding.value?.sections ?? []);
const currentSection = computed(() => sections.value[activeSection.value] ?? null);
const floors = computed(() => currentSection.value?.floors ?? []);
const currentFloor = computed(() => floors.value.find(f => f.floor === activeFloor.value) ?? null);
const isBlockSection = computed(() => currentSection.value?.is_block_unit ?? false);
const blockUnit = computed(() => currentSection.value?.block_unit ?? null);

watch([activeBldg, activeSection], () => { activeFloor.value = null; });

// ── Quick config ───────────────────────────────────────────────────────────
const qc = ref({
    type: '', wing: '', bedrooms: '', bathrooms: '', balconies: '',
    servant_room: false, store_room: false, size_sqft: '',
    facing_direction: '', view: '', price: '', overwrite: false,
});

watch(currentSection, (s) => {
    if (s) qc.value.type = sectionDefaultType(s.type);
}, { immediate: true });

function sectionDefaultType(t) {
    return { commercial: 'shop', office: 'office', villa: 'villa' }[t] ?? '';
}

// ── Status helpers ─────────────────────────────────────────────────────────
const STATUS = {
    not_configured: { border: 'border-orange-400', bg: 'bg-orange-400', text: 'text-orange-600', fill: 'bg-orange-400', light: 'bg-orange-50 dark:bg-orange-900/20' },
    configured: { border: 'border-blue-500', bg: 'bg-blue-500', text: 'text-blue-600', fill: 'bg-blue-500', light: 'bg-blue-50 dark:bg-blue-900/20' },
    available: { border: 'border-emerald-500', bg: 'bg-emerald-500', text: 'text-emerald-600', fill: 'bg-emerald-500', light: 'bg-emerald-50 dark:bg-emerald-900/20' },
    booked: { border: 'border-red-500', bg: 'bg-red-500', text: 'text-red-600', fill: 'bg-red-500', light: 'bg-red-50 dark:bg-red-900/20' },
    sold: { border: 'border-purple-500', bg: 'bg-purple-500', text: 'text-purple-600', fill: 'bg-purple-500', light: 'bg-purple-50 dark:bg-purple-900/20' },
};

function statusBorder(s) { return STATUS[s]?.border ?? 'border-slate-400'; }
function statusBg(s) { return STATUS[s]?.bg ?? 'bg-slate-400'; }

function floorStatusCounts(floor) {
    const counts = {};
    (floor.units ?? []).forEach(u => { counts[u.status] = (counts[u.status] ?? 0) + 1; });
    return counts;
}

// ── Section type color ─────────────────────────────────────────────────────
const SECTION_TYPE_COLOR = {
    residential: 'bg-emerald-500',
    commercial: 'bg-blue-500',
    office: 'bg-indigo-500',
    villa: 'bg-amber-500',
    industrial: 'bg-slate-500',
};
function sectionDot(type) { return SECTION_TYPE_COLOR[type] ?? 'bg-slate-400'; }

// ── Generate floor ─────────────────────────────────────────────────────────
async function generateFloor() {
    if (!currentSection.value || !activeFloor.value || generating.value) return;
    generating.value = true;
    try {
        const res = await axios.post(
            route('admin.blueprint.generate', currentSection.value.id),
            { floor: activeFloor.value, count: generateCount.value }
        );
        if (!res.data.skipped) {
            const idx = floors.value.findIndex(f => f.floor === activeFloor.value);
            if (idx !== -1) {
                buildings.value[activeBldg.value].sections[activeSection.value].floors[idx] = {
                    floor: activeFloor.value, unit_count: res.data.units.length, units: res.data.units,
                };
            }
            showToast(`Generated ${res.data.units.length} units on Floor ${activeFloor.value}.`, 'success');
        } else {
            showToast(res.data.message, 'info');
        }
    } catch { showToast('Generation failed.', 'error'); }
    finally { generating.value = false; }
}

// ── Generate block unit ────────────────────────────────────────────────────
async function generateBlock() {
    if (!currentSection.value || generatingBlock.value) return;
    generatingBlock.value = true;
    try {
        const res = await axios.post(route('admin.blueprint.generate-block', currentSection.value.id));
        buildings.value[activeBldg.value].sections[activeSection.value].block_unit = res.data.unit;
        showToast(res.data.skipped ? 'Block unit already exists.' : 'Block unit created.', res.data.skipped ? 'info' : 'success');
    } catch { showToast('Failed to create block unit.', 'error'); }
    finally { generatingBlock.value = false; }
}

// ── Apply quick config ─────────────────────────────────────────────────────
async function applyQuickConfig() {
    if (!currentSection.value || !activeFloor.value || !qc.value.type) return;
    applyingConfig.value = true;
    const payload = {
        floor: activeFloor.value, type: qc.value.type, overwrite: qc.value.overwrite,
        ...(qc.value.wing && { wing: qc.value.wing }),
        ...(qc.value.bedrooms !== '' && { bedrooms: +qc.value.bedrooms }),
        ...(qc.value.bathrooms !== '' && { bathrooms: +qc.value.bathrooms }),
        ...(qc.value.balconies !== '' && { balconies: +qc.value.balconies }),
        ...(qc.value.size_sqft !== '' && { size_sqft: +qc.value.size_sqft }),
        ...(qc.value.price !== '' && { price: +qc.value.price }),
        ...(qc.value.facing_direction && { facing_direction: qc.value.facing_direction }),
        ...(qc.value.view && { view: qc.value.view }),
        servant_room: qc.value.servant_room,
        store_room: qc.value.store_room,
    };
    try {
        const res = await axios.post(route('admin.blueprint.quick-config', currentSection.value.id), payload);
        showToast(`Applied to ${res.data.updated} unit${res.data.updated !== 1 ? 's' : ''} on Floor ${activeFloor.value}.`, 'success');
        const idx = floors.value.findIndex(f => f.floor === activeFloor.value);
        if (idx !== -1) {
            buildings.value[activeBldg.value].sections[activeSection.value].floors[idx].units =
                floors.value[idx].units.map(u =>
                    (u.type === qc.value.type || !u.type) && (qc.value.overwrite || u.status === 'not_configured')
                        ? { ...u, status: 'configured', status_label: 'Configured' } : u
                );
        }
    } catch { showToast('Failed to apply config.', 'error'); }
    finally { applyingConfig.value = false; }
}

// ── Delete floor ───────────────────────────────────────────────────────────
async function deleteFloor() {
    if (!currentSection.value || !activeFloor.value) return;
    if (!confirm(`Delete all configurable units on Floor ${activeFloor.value}?`)) return;
    try {
        await axios.delete(route('admin.blueprint.floor.delete', currentSection.value.id), { data: { floor: activeFloor.value } });
        const idx = floors.value.findIndex(f => f.floor === activeFloor.value);
        if (idx !== -1) {
            buildings.value[activeBldg.value].sections[activeSection.value].floors[idx] = { floor: activeFloor.value, unit_count: 0, units: [] };
        }
        showToast(`Floor ${activeFloor.value} cleared.`, 'success');
    } catch { showToast('Delete failed.', 'error'); }
}

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    toast.value = { message, type };
    setTimeout(() => { toast.value = null; }, 3500);
}

// ── Section unit summary ───────────────────────────────────────────────────
// Booked and sold units are committed to a client — not reconfigurable
function isLocked(status) {
    return status === 'booked' || status === 'sold';
}

// True when the active floor has at least one booked/sold unit — blocks clear + regenerate
const floorHasLockedUnits = computed(() =>
    (currentFloor.value?.units ?? []).some(u => isLocked(u.status))
);

function sectionUnitCount(section) {
    if (section.is_block_unit) return section.block_unit ? 1 : 0;
    return section.floors?.reduce((s, f) => s + (f.units?.length ?? 0), 0) ?? 0;
}
function sectionPlanned(section) {
    if (section.is_block_unit) return 1;
    return section.planned_units ?? 0;
}
</script>

<template>

    <Head :title="`Blueprint — ${project.name}`" />

    <AdminLayout>
        <!-- ── Page header ──────────────────────────────────────────── -->
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <nav class="mb-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                    <Link :href="route('admin.units.blueprint-select')"
                        class="hover:text-admin-accent transition-colors">Blueprint</Link>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                    <span class="text-foreground font-medium">{{ project.name }}</span>
                </nav>
                <h1 class="text-xl font-bold text-foreground">Unit Blueprint</h1>
            </div>

            <!-- Legend -->
            <div
                class="flex flex-wrap items-center gap-2.5 rounded-xl border border-border bg-admin-surface-card px-4 py-2">
                <span v-for="s in enums.unit_statuses" :key="s.value" class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full flex-shrink-0" :class="statusBg(s.value)" />
                    <span class="text-xs text-muted-foreground">{{ s.label }}</span>
                </span>
            </div>
        </div>

        <!-- ── No buildings ─────────────────────────────────────────── -->
        <div v-if="!buildings.length"
            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border py-24 text-center">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                class="mb-3 text-muted-foreground/30">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            <p class="font-semibold text-foreground">No buildings defined yet</p>
            <p class="mt-1 text-sm text-muted-foreground">Add buildings in the project form first.</p>
            <Link :href="route('admin.projects.edit', project.id)"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-admin-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors">
                Edit Project
            </Link>
        </div>

        <!-- ── 3-panel layout ───────────────────────────────────────── -->
        <div v-else class="flex rounded-2xl border border-border bg-admin-surface-card overflow-hidden shadow-sm"
            style="min-height: calc(100vh - 195px);">

            <!-- ──────────── LEFT PANEL ──────────────────────────── -->
            <div class="flex flex-col border-r border-border bg-white dark:bg-slate-900/60"
                style="width:250px; flex-shrink:0;">

                <!-- Building tabs -->
                <div class="border-b border-border p-3">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Building
                    </p>
                    <div class="flex flex-col gap-1">
                        <button v-for="(b, i) in buildings" :key="b.id" @click="activeBldg = i; activeSection = 0;"
                            :class="[
                                'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-all text-left',
                                activeBldg === i
                                    ? 'bg-admin-accent/10 text-admin-accent border border-admin-accent/25'
                                    : 'text-foreground hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent'
                            ]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" class="flex-shrink-0">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <path d="M3 9h18M9 21V9" />
                            </svg>
                            <span class="flex-1 truncate">{{ b.name }}</span>
                            <span class="text-[10px] text-muted-foreground">{{ b.total_floors ? b.total_floors + ' F' :
                                'No Floor Available' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Section list -->
                <div class="border-b border-border p-3">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Section</p>
                    <div class="flex flex-col gap-1">
                        <button v-for="(s, i) in sections" :key="s.id" @click="activeSection = i" :class="[
                            'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition-all text-left border',
                            activeSection === i
                                ? 'bg-admin-accent/10 border-admin-accent/25 font-semibold text-admin-accent'
                                : 'border-transparent text-foreground hover:bg-slate-50 dark:hover:bg-slate-800 font-medium'
                        ]">
                            <span class="mt-0.5 h-2 w-2 flex-shrink-0 rounded-full" :class="sectionDot(s.type)" />
                            <span class="flex-1 truncate text-xs">{{ s.name }}</span>
                            <span v-if="s.is_block_unit"
                                class="rounded-md bg-amber-100 dark:bg-amber-900/30 px-1 py-0.5 text-[9px] font-bold text-amber-700 dark:text-amber-400">
                                BLK
                            </span>
                            <span class="text-[10px] font-semibold text-muted-foreground">
                                {{ sectionUnitCount(s) }}/{{ sectionPlanned(s) }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Floor list (normal sections only) -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Block section indicator -->
                    <div v-if="isBlockSection" class="p-3">
                        <div
                            class="rounded-xl border border-amber-200 dark:border-amber-700/40 bg-amber-50 dark:bg-amber-900/20 p-3 text-center">
                            <svg class="mx-auto mb-1.5 text-amber-500" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <rect x="7" y="7" width="10" height="10" rx="1" />
                            </svg>
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">Block Unit Section</p>
                            <p class="mt-0.5 text-[10px] text-amber-600/70">Floors {{ currentSection?.floor_start }}–{{
                                currentSection?.floor_end }}</p>
                            <p class="text-[10px] text-amber-600/70">Single unit spans all floors</p>
                        </div>
                    </div>

                    <!-- Floor rows -->
                    <template v-else>
                        <div class="px-3 pt-3 pb-1">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Floors
                            </p>
                        </div>
                        <div v-for="floor in floors" :key="floor.floor" @click="activeFloor = floor.floor" :class="[
                            'group mx-2 mb-1 cursor-pointer rounded-xl border px-3 py-2.5 transition-all',
                            activeFloor === floor.floor
                                ? 'border-admin-accent/30 bg-admin-accent/8 shadow-sm'
                                : 'border-transparent hover:border-border hover:bg-slate-50 dark:hover:bg-slate-800/60'
                        ]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span :class="[
                                        'flex h-6 w-8 items-center justify-center rounded-md text-[10px] font-bold transition-colors',
                                        activeFloor === floor.floor
                                            ? 'bg-admin-accent text-white'
                                            : 'bg-slate-100 dark:bg-slate-700 text-foreground group-hover:bg-slate-200 dark:group-hover:bg-slate-600'
                                    ]">{{ floor.floor }}</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ floor.units?.length ?? 0 }} unit{{ floor.units?.length !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                            <!-- Status strip -->
                            <div v-if="floor.units?.length"
                                class="mt-2 flex h-1.5 w-full gap-px overflow-hidden rounded-full">
                                <template v-for="(count, status) in floorStatusCounts(floor)" :key="status">
                                    <div :class="statusBg(status)" :style="{ flex: count }"
                                        :title="`${count} ${status}`" />
                                </template>
                            </div>
                            <div v-else class="mt-2 h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-700" />
                        </div>

                        <div v-if="!floors.length" class="p-4 text-center">
                            <p class="text-xs text-muted-foreground">No floor range configured.</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ──────────── CENTRE PANEL ─────────────────────────── -->
            <div class="flex-1 overflow-y-auto bg-slate-50/60 dark:bg-slate-900/30">

                <!-- Block section view -->
                <div v-if="isBlockSection" class="flex h-full min-h-[400px] flex-col items-center justify-center p-8">
                    <div
                        class="w-full max-w-md rounded-2xl border border-amber-200 dark:border-amber-700/40 bg-white dark:bg-slate-800 shadow-md p-8 text-center">
                        <div
                            class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-900/30">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="text-amber-600 dark:text-amber-400">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <rect x="7" y="7" width="10" height="10" rx="1" />
                            </svg>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400 mb-3">
                            Block Unit · Floors {{ currentSection?.floor_start }}–{{ currentSection?.floor_end }}
                        </span>
                        <h2 class="text-lg font-bold text-foreground">{{ currentSection?.name }}</h2>
                        <p class="mt-1 text-sm text-muted-foreground">This entire section is sold as one unit spanning
                            multiple
                            floors.</p>

                        <template v-if="blockUnit">
                            <div
                                class="mt-5 rounded-xl border border-border bg-slate-50 dark:bg-slate-700/50 p-4 text-left">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-foreground">{{ blockUnit.unit_number }}</p>
                                        <p class="mt-0.5 text-xs text-muted-foreground">
                                            Floor {{ blockUnit.floor }}–{{ blockUnit.floor_end }}
                                            <template v-if="blockUnit.size_sqft"> · {{
                                                blockUnit.size_sqft.toLocaleString() }} sqft</template>
                                            <template v-if="blockUnit.type_label"> · {{ blockUnit.type_label
                                            }}</template>
                                        </p>
                                    </div>
                                    <span
                                        :class="['flex-shrink-0 rounded-lg px-2.5 py-1 text-[10px] font-bold text-white', statusBg(blockUnit.status)]">
                                        {{ blockUnit.status_label }}
                                    </span>
                                </div>
                            </div>
                            <Link :href="route('admin.units.configure', blockUnit.id)"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-admin-accent px-5 py-3 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="3" />
                                    <path
                                        d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" />
                                </svg>
                                Configure Unit
                            </Link>
                        </template>

                        <template v-else>
                            <p class="mt-4 text-sm text-muted-foreground">No unit has been generated for this section
                                yet.</p>
                            <button @click="generateBlock" :disabled="generatingBlock"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-3 text-sm font-semibold text-white transition-colors disabled:opacity-60">
                                <svg v-if="generatingBlock" class="animate-spin" width="14" height="14"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                </svg>
                                <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                Generate Block Unit
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Empty state (no floor selected) -->
                <div v-else-if="!activeFloor"
                    class="flex h-full min-h-[400px] flex-col items-center justify-center gap-4 p-8 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" class="text-muted-foreground/50">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <path d="M9 22V12h6v10" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground">Select a floor</p>
                        <p class="mt-1 text-sm text-muted-foreground">Pick a floor from the left panel to view and
                            configure its units.
                        </p>
                    </div>
                </div>

                <!-- Floor content -->
                <div v-else class="p-6">
                    <!-- Floor header -->
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-admin-accent text-sm font-bold text-white shadow-sm">
                                {{ activeFloor }}
                            </div>
                            <div>
                                <h2 class="font-semibold text-foreground leading-tight">
                                    Floor {{ activeFloor }}
                                    <span v-if="currentSection" class="text-sm font-normal text-muted-foreground">— {{
                                        currentSection.name }}</span>
                                </h2>
                                <p class="text-xs text-muted-foreground">{{ currentFloor?.units?.length ?? 0 }} units on
                                    this floor</p>
                            </div>
                        </div>

                        <!-- Generate controls -->
                        <div class="flex items-center gap-2">
                            <div
                                class="flex items-center gap-2 rounded-xl border border-border bg-white dark:bg-slate-800 px-3 py-1.5 shadow-sm">
                                <label class="text-xs text-muted-foreground whitespace-nowrap">Count</label>
                                <Input v-model.number="generateCount" type="number" min="1" max="100"
                                    class="h-7 w-16 border-0 p-0 text-center text-sm shadow-none focus-visible:ring-0" />
                            </div>
                            <button @click="generateFloor" :disabled="generating || !!currentFloor?.units?.length"
                                :title="currentFloor?.units?.length ? 'Floor already has units, clear first to regenerate' : 'Generate units on this floor'"
                                :class="[
                                    'inline-flex h-9 items-center gap-1.5 rounded-xl px-4 text-sm font-semibold transition-all',
                                    currentFloor?.units?.length
                                        ? 'border border-border bg-white dark:bg-slate-800 text-muted-foreground cursor-not-allowed opacity-60'
                                        : 'bg-admin-accent text-white hover:bg-admin-accent/90 shadow-sm'
                                ]">
                                <svg v-if="generating" class="animate-spin" width="13" height="13" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                </svg>
                                <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                Generate
                            </button>
                            <Tooltip v-if="currentFloor?.units?.length"
                                :text="floorHasLockedUnits ? 'This floor has booked or sold units and cannot be cleared.' : 'Remove all configurable units on this floor.'">
                                <button @click="deleteFloor"
                                    :disabled="floorHasLockedUnits"
                                    :class="[
                                        'inline-flex h-9 items-center gap-1.5 rounded-xl border px-3 text-sm font-medium transition-colors',
                                        floorHasLockedUnits
                                            ? 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-500'
                                            : 'border-red-200 dark:border-red-800/40 bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/40'
                                    ]">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18M19 6l-1 14H6L5 6M9 6V4h6v2" />
                                    </svg>
                                    Clear
                                </button>
                            </Tooltip>
                        </div>
                    </div>

                    <!-- Unit grid -->
                    <div v-if="currentFloor?.units?.length" class="grid gap-2.5"
                        style="grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));">

                        <template v-for="unit in currentFloor.units" :key="unit.id">

                            <!-- ── LOCKED: booked / sold — not reconfigurable ── -->
                            <div v-if="isLocked(unit.status)"
                                :class="[
                                    'group/tip relative flex flex-col rounded-xl border-2 bg-slate-50 dark:bg-slate-800/50 p-3 shadow-sm opacity-70 cursor-not-allowed select-none',
                                    statusBorder(unit.status)
                                ]"
                            >
                                <!-- Tooltip -->
                                <div class="pointer-events-none absolute bottom-[calc(100%+6px)] left-1/2 z-50 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 dark:bg-slate-700 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-md transition-opacity duration-150 group-hover/tip:opacity-100">
                                    This unit is {{ unit.status_label }} and cannot be reconfigured.
                                    <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-slate-700" />
                                </div>

                                <!-- Lock badge -->
                                <div class="absolute top-2 right-2">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-muted-foreground/60">
                                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </div>

                                <div class="flex items-start justify-between gap-1 mb-2 pr-4">
                                    <span class="text-sm font-bold text-foreground leading-tight">{{ unit.unit_number }}</span>
                                    <span class="h-2 w-2 flex-shrink-0 mt-1 rounded-full" :class="statusBg(unit.status)" />
                                </div>

                                <span v-if="unit.type_label"
                                    class="mb-1.5 self-start rounded-md bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                                    {{ unit.type_label }}
                                </span>

                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-muted-foreground">
                                    <span v-if="unit.bedrooms">{{ unit.bedrooms }}bd</span>
                                    <span v-if="unit.bathrooms">{{ unit.bathrooms }}ba</span>
                                    <span v-if="unit.size_sqft">{{ unit.size_sqft.toLocaleString() }} ft²</span>
                                </div>

                                <div class="mt-2 pt-2 border-t border-border/50">
                                    <span class="text-[10px] font-semibold" :class="STATUS[unit.status]?.text ?? 'text-slate-500'">
                                        {{ unit.status_label }}
                                    </span>
                                </div>
                            </div>

                            <!-- ── CONFIGURABLE: not_configured / configured / available ── -->
                            <Link v-else
                                :href="route('admin.units.configure', unit.id)"
                                :class="[
                                    'group relative flex flex-col rounded-xl border-2 bg-white dark:bg-slate-800 p-3 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5',
                                    statusBorder(unit.status)
                                ]"
                            >
                                <div class="flex items-start justify-between gap-1 mb-2">
                                    <span class="text-sm font-bold text-foreground leading-tight">{{ unit.unit_number }}</span>
                                    <span class="h-2 w-2 flex-shrink-0 mt-1 rounded-full" :class="statusBg(unit.status)" />
                                </div>

                                <span v-if="unit.type_label"
                                    class="mb-1.5 self-start rounded-md bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                                    {{ unit.type_label }}
                                </span>

                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-muted-foreground">
                                    <span v-if="unit.bedrooms" class="flex items-center gap-0.5">
                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 22V12a9 9 0 0 1 18 0v10"/><path d="M3 18h18"/></svg>
                                        {{ unit.bedrooms }}bd
                                    </span>
                                    <span v-if="unit.bathrooms" class="flex items-center gap-0.5">
                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                                        {{ unit.bathrooms }}ba
                                    </span>
                                    <span v-if="unit.size_sqft">{{ unit.size_sqft.toLocaleString() }} ft²</span>
                                </div>

                                <div class="mt-2 pt-2 border-t border-border/50">
                                    <span class="text-[10px] font-semibold" :class="STATUS[unit.status]?.text ?? 'text-slate-500'">
                                        {{ unit.status_label }}
                                    </span>
                                </div>

                                <!-- Hover overlay — configure prompt -->
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center rounded-[10px] bg-admin-accent/90 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-xs font-semibold text-white">Configure →</span>
                                </div>
                            </Link>

                        </template>
                    </div>

                    <!-- Empty floor -->
                    <div v-else
                        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-border py-16 text-center">
                        <div
                            class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="text-muted-foreground/50">
                                <rect x="3" y="3" width="7" height="9" rx="1" />
                                <rect x="14" y="3" width="7" height="5" rx="1" />
                                <rect x="14" y="12" width="7" height="9" rx="1" />
                                <rect x="3" y="16" width="7" height="5" rx="1" />
                            </svg>
                        </div>
                        <p class="font-semibold text-foreground">No units on Floor {{ activeFloor }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">Set a count above and click Generate to create
                            units.</p>
                    </div>
                </div>
            </div>

            <!-- ──────────── RIGHT PANEL: Quick Config ────────────── -->
            <div class="flex flex-col border-l border-border bg-white dark:bg-slate-900/60"
                style="width:280px; flex-shrink:0;">
                <!-- Panel header -->
                <div class="border-b border-border px-5 py-4">
                    <h3 class="text-sm font-semibold text-foreground">Quick Config</h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">Bulk-apply settings to same-type units on the
                        selected floor</p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-4">

                    <!-- Unit Type -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Unit
                            Type</Label>
                        <Select v-model="qc.type">
                            <SelectTrigger class="h-9 text-sm">
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in enums.unit_types" :key="t.value" :value="t.value">{{ t.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Wing -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Wing</Label>
                        <Input v-model="qc.wing" placeholder="e.g. North, A" class="h-9 text-sm" />
                    </div>

                    <!-- Beds / Baths / Balconies -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Rooms</Label>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="space-y-1">
                                <p class="text-[10px] text-muted-foreground text-center">Beds</p>
                                <Input v-model="qc.bedrooms" type="number" min="0" max="20" placeholder="0"
                                    class="h-8 text-xs text-center px-1" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] text-muted-foreground text-center">Baths</p>
                                <Input v-model="qc.bathrooms" type="number" min="0" max="20" placeholder="0"
                                    class="h-8 text-xs text-center px-1" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] text-muted-foreground text-center">Balc.</p>
                                <Input v-model="qc.balconies" type="number" min="0" max="10" placeholder="0"
                                    class="h-8 text-xs text-center px-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Size / Price -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Size &
                            Price</Label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <p class="text-[10px] text-muted-foreground">Size (sqft)</p>
                                <Input v-model="qc.size_sqft" type="number" min="1" placeholder="1200"
                                    class="h-8 text-xs" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] text-muted-foreground">Price (BDT)</p>
                                <Input v-model="qc.price" type="number" min="0" placeholder="0" class="h-8 text-xs" />
                            </div>
                        </div>
                    </div>

                    <!-- Facing -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Facing</Label>
                        <Select v-model="qc.facing_direction">
                            <SelectTrigger class="h-9 text-sm">
                                <SelectValue placeholder="Any direction" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="f in enums.facing" :key="f" :value="f">{{ f }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Servant / Store room -->
                    <div class="space-y-2">
                        <Label
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Extras</Label>
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                <span class="text-sm text-foreground">Servant Room</span>
                                <button type="button" @click="qc.servant_room = !qc.servant_room" :class="['relative inline-flex h-5 w-9 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                    qc.servant_room ? 'bg-admin-accent' : 'bg-slate-200 dark:bg-slate-600']">
                                    <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                        qc.servant_room ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>
                            <div class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                <span class="text-sm text-foreground">Store Room</span>
                                <button type="button" @click="qc.store_room = !qc.store_room" :class="['relative inline-flex h-5 w-9 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                    qc.store_room ? 'bg-admin-accent' : 'bg-slate-200 dark:bg-slate-600']">
                                    <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                        qc.store_room ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Overwrite -->
                    <div
                        class="rounded-xl border border-amber-200 dark:border-amber-700/40 bg-amber-50 dark:bg-amber-900/20 px-3 py-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold text-amber-800 dark:text-amber-300">Overwrite configured
                                </p>
                                <p class="mt-0.5 text-[10px] text-amber-700/70 dark:text-amber-400/70">Also update
                                    already-configured units</p>
                            </div>
                            <button type="button" @click="qc.overwrite = !qc.overwrite" :class="['relative mt-0.5 inline-flex h-5 w-9 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                qc.overwrite ? 'bg-amber-500' : 'bg-slate-200 dark:bg-slate-600']">
                                <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                    qc.overwrite ? 'translate-x-4' : 'translate-x-0']" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Apply button -->
                <div class="border-t border-border p-4 space-y-2">
                    <button @click="applyQuickConfig" :disabled="applyingConfig || !activeFloor || !qc.type"
                        class="w-full inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-admin-accent text-sm font-semibold text-white hover:bg-admin-accent/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                        <svg v-if="applyingConfig" class="animate-spin" width="13" height="13" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                        </svg>
                        <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M9 12l2 2 4-4" />
                            <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                        </svg>
                        Apply to {{ qc.type || '…' }}
                    </button>
                    <p v-if="activeFloor && qc.type" class="text-center text-[10px] text-muted-foreground">
                        Targets <strong>{{ qc.type }}</strong> units on Floor {{ activeFloor }}
                    </p>
                    <p v-else class="text-center text-[10px] text-muted-foreground">Select a floor and type first</p>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <Transition enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2 scale-95" leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0">
            <div v-if="toast" :class="[
                'fixed bottom-6 right-6 flex items-center gap-2.5 rounded-xl px-5 py-3.5 text-sm font-medium text-white shadow-xl z-50 border',
                toast.type === 'error' ? 'bg-red-500 border-red-600' :
                    toast.type === 'info' ? 'bg-blue-500 border-blue-600' :
                        'bg-emerald-500 border-emerald-600'
            ]">
                <svg v-if="toast.type === 'success'" width="15" height="15" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
                <svg v-else-if="toast.type === 'error'" width="15" height="15" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                {{ toast.message }}
            </div>
        </Transition>
    </AdminLayout>
</template>
