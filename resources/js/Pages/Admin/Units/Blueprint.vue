<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    project:   { type: Object, required: true },
    buildings: { type: Array,  required: true },
    enums:     { type: Object, required: true },
});

// ── Selection state ────────────────────────────────────────────────────────
const buildings   = ref(props.buildings);
const activeBldg  = ref(0);
const activeSection = ref(0);
const activeFloor = ref(null);   // floor number
const generateCount = ref(8);
const generating  = ref(false);
const applyingConfig = ref(false);
const toast       = ref(null);

const currentBuilding = computed(() => buildings.value[activeBldg.value] ?? null);
const sections = computed(() => currentBuilding.value?.sections ?? []);
const currentSection = computed(() => sections.value[activeSection.value] ?? null);

const floors = computed(() => currentSection.value?.floors ?? []);

const currentFloor = computed(() =>
    floors.value.find(f => f.floor === activeFloor.value) ?? null
);

// Reset floor selection when section changes
watch([activeBldg, activeSection], () => { activeFloor.value = null; });

const isBlockSection = computed(() => currentSection.value?.is_block_unit ?? false);
const blockUnit      = computed(() => currentSection.value?.block_unit ?? null);
const generatingBlock = ref(false);

// ── Quick config form ──────────────────────────────────────────────────────
const qc = ref({
    type:             '',
    wing:             '',
    bedrooms:         '',
    bathrooms:        '',
    balconies:        '',
    servant_room:     false,
    store_room:       false,
    size_sqft:        '',
    facing_direction: '',
    view:             '',
    price:            '',
    overwrite:        false,
});

// Auto-fill type from section default
watch(currentSection, (s) => {
    if (s && !qc.value.type) qc.value.type = sectionDefaultType(s.type);
}, { immediate: true });

function sectionDefaultType(sectionType) {
    const map = { commercial: 'shop', office: 'office', villa: 'villa' };
    return map[sectionType] ?? '';
}

// ── Status colors ──────────────────────────────────────────────────────────
const STATUS_COLOR = {
    not_configured: 'bg-orange-400 border-orange-500',
    configured:     'bg-blue-500 border-blue-600',
    available:      'bg-emerald-500 border-emerald-600',
    booked:         'bg-red-500 border-red-600',
    sold:           'bg-purple-500 border-purple-600',
};

function unitColor(status) {
    return STATUS_COLOR[status] ?? 'bg-slate-400 border-slate-500';
}

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
            const floorIdx = floors.value.findIndex(f => f.floor === activeFloor.value);
            if (floorIdx !== -1) {
                buildings.value[activeBldg.value].sections[activeSection.value].floors[floorIdx] = {
                    floor:      activeFloor.value,
                    unit_count: res.data.units.length,
                    units:      res.data.units,
                };
            }
            showToast(`Generated ${res.data.units.length} units on Floor ${activeFloor.value}.`, 'success');
        } else {
            showToast(res.data.message, 'info');
        }
    } catch {
        showToast('Generation failed. Try again.', 'error');
    } finally {
        generating.value = false;
    }
}

// ── Generate block unit ────────────────────────────────────────────────────
async function generateBlock() {
    if (!currentSection.value || generatingBlock.value) return;
    generatingBlock.value = true;
    try {
        const res = await axios.post(route('admin.blueprint.generate-block', currentSection.value.id));
        const sIdx = activeSection.value;
        buildings.value[activeBldg.value].sections[sIdx].block_unit = res.data.unit;
        showToast(res.data.skipped ? 'Block unit already exists.' : 'Block unit created.', res.data.skipped ? 'info' : 'success');
    } catch {
        showToast('Failed to create block unit.', 'error');
    } finally {
        generatingBlock.value = false;
    }
}

// ── Apply quick config ─────────────────────────────────────────────────────
async function applyQuickConfig() {
    if (!currentSection.value || !activeFloor.value || !qc.value.type) return;
    applyingConfig.value = true;

    const payload = {
        floor:     activeFloor.value,
        type:      qc.value.type,
        overwrite: qc.value.overwrite,
        ...(qc.value.wing             && { wing: qc.value.wing }),
        ...(qc.value.bedrooms !== ''  && { bedrooms: +qc.value.bedrooms }),
        ...(qc.value.bathrooms !== '' && { bathrooms: +qc.value.bathrooms }),
        ...(qc.value.balconies !== '' && { balconies: +qc.value.balconies }),
        ...(qc.value.size_sqft !== '' && { size_sqft: +qc.value.size_sqft }),
        ...(qc.value.price !== ''     && { price: +qc.value.price }),
        ...(qc.value.facing_direction && { facing_direction: qc.value.facing_direction }),
        ...(qc.value.view             && { view: qc.value.view }),
        servant_room: qc.value.servant_room,
        store_room:   qc.value.store_room,
    };

    try {
        const res = await axios.post(
            route('admin.blueprint.quick-config', currentSection.value.id),
            payload
        );
        const count = res.data.updated;
        showToast(`Applied config to ${count} ${qc.value.type} unit${count !== 1 ? 's' : ''} on Floor ${activeFloor.value}.`, 'success');

        // Update unit statuses locally
        const floorIdx = floors.value.findIndex(f => f.floor === activeFloor.value);
        if (floorIdx !== -1) {
            buildings.value[activeBldg.value].sections[activeSection.value].floors[floorIdx].units =
                floors.value[floorIdx].units.map(u =>
                    (u.type === qc.value.type || !u.type) && (qc.value.overwrite || u.status === 'not_configured')
                        ? { ...u, status: 'configured', status_label: 'Configured', status_color: 'blue',
                            ...Object.fromEntries(Object.entries(payload).filter(([k]) =>
                                ['wing','bedrooms','bathrooms','balconies','size_sqft','price'].includes(k))) }
                        : u
                );
        }
    } catch {
        showToast('Failed to apply config.', 'error');
    } finally {
        applyingConfig.value = false;
    }
}

// ── Delete floor ───────────────────────────────────────────────────────────
async function deleteFloor() {
    if (!currentSection.value || !activeFloor.value) return;
    if (!confirm(`Delete all not-configured / configured units on Floor ${activeFloor.value}?`)) return;

    try {
        await axios.delete(route('admin.blueprint.floor.delete', currentSection.value.id), {
            data: { floor: activeFloor.value },
        });

        const floorIdx = floors.value.findIndex(f => f.floor === activeFloor.value);
        if (floorIdx !== -1) {
            buildings.value[activeBldg.value].sections[activeSection.value].floors[floorIdx] = {
                floor: activeFloor.value, unit_count: 0, units: [],
            };
        }
        showToast(`Floor ${activeFloor.value} cleared.`, 'success');
    } catch {
        showToast('Delete failed.', 'error');
    }
}

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    toast.value = { message, type };
    setTimeout(() => { toast.value = null; }, 3500);
}

// ── Helpers ───────────────────────────────────────────────────────────────
function floorUnitCount(floor) {
    return floor.units?.length ?? 0;
}

function floorStatusSummary(floor) {
    const counts = {};
    (floor.units ?? []).forEach(u => { counts[u.status] = (counts[u.status] ?? 0) + 1; });
    return counts;
}
</script>

<template>
    <Head :title="`Blueprint — ${project.name}`" />

    <AdminLayout>
        <!-- Page header -->
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <nav class="mb-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                    <Link :href="route('admin.units.blueprint-select')" class="hover:text-admin-accent transition-colors">Blueprint</Link>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="text-foreground font-medium">{{ project.name }}</span>
                </nav>
                <h1 class="text-lg font-bold text-foreground">Unit Blueprint</h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-border bg-admin-surface-card px-3 py-2 text-xs">
                    <span v-for="s in enums.unit_statuses" :key="s.value" class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full flex-shrink-0 border" :class="unitColor(s.value)" />
                        <span class="text-muted-foreground">{{ s.label }}</span>
                    </span>
                </div>

            </div>
        </div>

        <!-- No buildings -->
        <div v-if="!buildings.length"
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-20 text-center">
            <p class="font-medium text-foreground">No buildings defined yet</p>
            <p class="mt-1 text-sm text-muted-foreground">Add buildings in the project form first.</p>
            <Link :href="route('admin.projects.edit', project.id)"
                class="mt-4 inline-flex rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white">
                Edit Project
            </Link>
        </div>

        <!-- 3-panel layout -->
        <div v-else class="flex gap-0 rounded-xl border border-border overflow-hidden" style="min-height: calc(100vh - 190px);">

            <!-- ── LEFT PANEL: floor tabs ──────────────────────────────── -->
            <div class="flex flex-col border-r border-border bg-admin-surface-card" style="width:220px; flex-shrink:0;">

                <!-- Building selector (if >1 building) -->
                <div v-if="buildings.length > 1" class="border-b border-border p-2">
                    <select v-model="activeBldg" class="h-8 w-full rounded-lg border border-border px-2 text-xs bg-white dark:bg-slate-800">
                        <option v-for="(b, i) in buildings" :key="b.id" :value="i">{{ b.name }}</option>
                    </select>
                </div>

                <!-- Section tabs (horizontal pills) -->
                <div v-if="sections.length > 1" class="flex flex-wrap gap-1 border-b border-border p-2">
                    <button
                        v-for="(s, i) in sections"
                        :key="s.id"
                        @click="activeSection = i"
                        :class="[
                            'rounded-md px-2 py-1 text-[10px] font-semibold transition-colors',
                            activeSection === i
                                ? 'bg-admin-accent text-white'
                                : 'bg-slate-100 dark:bg-slate-700 text-muted-foreground hover:text-foreground'
                        ]"
                    >{{ s.name }}</button>
                </div>
                <div v-else-if="sections.length === 1" class="border-b border-border px-3 py-2">
                    <span class="text-xs font-semibold text-foreground">{{ sections[0].name }}</span>
                </div>

                <!-- Floor list / block unit indicator -->
                <div class="flex-1 overflow-y-auto py-1">

                    <!-- Block unit section — show single row instead of floor tabs -->
                    <div v-if="isBlockSection" class="px-3 py-3">
                        <div class="rounded-lg border border-amber-300 dark:border-amber-700/50 bg-amber-50 dark:bg-amber-900/20 px-3 py-2.5 text-center">
                            <svg class="mx-auto mb-1 text-amber-500" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 12l2 2 4-4"/>
                            </svg>
                            <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">Block Unit</p>
                            <p class="text-[10px] text-amber-600/70 dark:text-amber-500/70 mt-0.5">
                                Floors {{ currentSection?.floor_start }}–{{ currentSection?.floor_end }}
                            </p>
                            <p class="text-[10px] text-amber-600/70 dark:text-amber-500/70">= 1 unit (whole section)</p>
                        </div>
                    </div>

                    <!-- Normal per-floor list -->
                    <template v-else>
                        <div
                            v-for="floor in floors"
                            :key="floor.floor"
                            @click="activeFloor = floor.floor"
                            :class="[
                                'flex cursor-pointer items-center justify-between px-3 py-2.5 border-b border-border/50 transition-colors',
                                activeFloor === floor.floor
                                    ? 'bg-admin-accent/10 border-l-2 border-l-admin-accent'
                                    : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'
                            ]"
                        >
                            <span :class="['text-xs font-bold', activeFloor === floor.floor ? 'text-admin-accent' : 'text-foreground']">
                                Floor {{ floor.floor }}
                            </span>
                            <span :class="[
                                'text-[10px] font-semibold rounded-full px-1.5 py-0.5',
                                floorUnitCount(floor) > 0
                                    ? 'bg-admin-accent/15 text-admin-accent'
                                    : 'bg-slate-100 dark:bg-slate-700 text-muted-foreground'
                            ]">
                                {{ floorUnitCount(floor) || '—' }}
                            </span>
                        </div>
                        <div v-if="!floors.length" class="px-3 py-8 text-center">
                            <p class="text-xs text-muted-foreground">No floors in this section.</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ── CENTRE PANEL: circle grid ──────────────────────────── -->
            <div class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900/40">

                <!-- Block unit section — whole section is one unit -->
                <div v-if="isBlockSection" class="flex h-full flex-col items-center justify-center gap-5 p-8 text-center">
                    <div class="rounded-2xl border-2 border-amber-300 dark:border-amber-600/50 bg-white dark:bg-slate-800 shadow-lg p-8 max-w-sm w-full">
                        <!-- Icon -->
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-900/30">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-amber-600 dark:text-amber-400">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><rect x="7" y="7" width="10" height="10" rx="1"/>
                            </svg>
                        </div>

                        <h2 class="text-base font-bold text-foreground">Block Unit</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ currentSection?.name }}</p>
                        <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400">
                            Floors {{ currentSection?.floor_start }} – {{ currentSection?.floor_end }}
                            &nbsp;·&nbsp; 1 unit (entire section)
                        </p>

                        <!-- Already generated -->
                        <template v-if="blockUnit">
                            <div class="mt-4 rounded-xl border border-border bg-slate-50 dark:bg-slate-700/50 px-4 py-3 text-left">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-foreground">{{ blockUnit.unit_number }}</span>
                                    <span :class="['text-[10px] font-bold rounded-full px-2 py-0.5', unitColor(blockUnit.status)]">
                                        {{ blockUnit.status_label }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Floors {{ blockUnit.floor }}–{{ blockUnit.floor_end }}
                                    <template v-if="blockUnit.size_sqft"> · {{ blockUnit.size_sqft }} sqft</template>
                                    <template v-if="blockUnit.type_label"> · {{ blockUnit.type_label }}</template>
                                </p>
                            </div>
                            <Link :href="route('admin.units.configure', blockUnit.id)"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-admin-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                                Configure Unit
                            </Link>
                        </template>

                        <!-- Not yet generated -->
                        <template v-else>
                            <p class="mt-4 text-xs text-muted-foreground">No unit has been generated yet for this block section.</p>
                            <button @click="generateBlock" :disabled="generatingBlock"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors disabled:opacity-60">
                                <svg v-if="generatingBlock" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                                </svg>
                                <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Generate Block Unit
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Empty state (no floor selected, normal section) -->
                <div v-else-if="!activeFloor" class="flex h-full flex-col items-center justify-center gap-3 p-8 text-center">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-muted-foreground/20">
                        <path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M12 3v18M3 7l9 4 9-4"/>
                    </svg>
                    <p class="font-medium text-foreground">Select a floor</p>
                    <p class="text-sm text-muted-foreground">Choose a floor from the left panel to view and configure units.</p>
                </div>

                <!-- Floor content -->
                <div v-else class="p-5">
                    <!-- Floor header + generate -->
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-foreground">
                                Floor {{ activeFloor }}
                                <span v-if="currentSection" class="ml-2 text-sm font-normal text-muted-foreground">— {{ currentSection.name }}</span>
                            </h2>
                            <p class="text-xs text-muted-foreground">
                                {{ currentFloor?.units?.length ?? 0 }} units generated
                            </p>
                        </div>

                        <!-- Generate row (only if floor has no units yet OR always show for add more) -->
                        <div class="flex items-center gap-2">
                            <input v-model.number="generateCount" type="number" min="1" max="100"
                                class="h-8 w-20 rounded-lg border border-border bg-white dark:bg-slate-800 px-2 text-center text-sm"
                                placeholder="Count" />
                            <button @click="generateFloor" :disabled="generating || !!currentFloor?.units?.length"
                                :class="[
                                    'inline-flex h-8 items-center gap-1.5 rounded-lg px-3 text-xs font-semibold transition-colors',
                                    currentFloor?.units?.length
                                        ? 'border border-border bg-white dark:bg-slate-800 text-muted-foreground cursor-not-allowed'
                                        : 'bg-admin-accent text-white hover:bg-admin-accent/90'
                                ]"
                                :title="currentFloor?.units?.length ? 'Floor already has units. Delete first to regenerate.' : 'Generate units'"
                            >
                                <svg v-if="generating" class="animate-spin" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                                </svg>
                                <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Generate
                            </button>
                            <button v-if="currentFloor?.units?.length" @click="deleteFloor"
                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20 px-3 text-xs font-medium text-red-600 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M19 6l-1 14H6L5 6M9 6V4h6v2"/>
                                </svg>
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Unit circles grid -->
                    <div v-if="currentFloor?.units?.length" class="flex flex-wrap gap-3">
                        <Link
                            v-for="unit in currentFloor.units"
                            :key="unit.id"
                            :href="route('admin.units.configure', unit.id)"
                            :title="`${unit.unit_number} · ${unit.status_label}${unit.bedrooms ? ' · ' + unit.bedrooms + ' bed' : ''}${unit.size_sqft ? ' · ' + unit.size_sqft + ' sqft' : ''}`"
                            :class="[
                                'group relative flex flex-col items-center justify-center rounded-full border-2 shadow-sm transition-all hover:scale-110 hover:shadow-md',
                                unitColor(unit.status),
                            ]"
                            style="width:56px; height:56px;"
                        >
                            <span class="text-[10px] font-bold text-white leading-tight text-center px-1 line-clamp-2">
                                {{ unit.unit_number.split('-').pop() }}
                            </span>
                            <!-- Tooltip on hover -->
                            <div class="pointer-events-none absolute -top-10 left-1/2 -translate-x-1/2 rounded-lg bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 shadow-lg">
                                {{ unit.unit_number }}
                                <span v-if="unit.bedrooms"> · {{ unit.bedrooms }}B</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty floor -->
                    <div v-else class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border py-12 text-center">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2 text-muted-foreground/30">
                            <rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>
                        </svg>
                        <p class="text-sm font-medium text-foreground">No units on Floor {{ activeFloor }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Enter a count above and click Generate.</p>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT PANEL: quick config ──────────────────────────── -->
            <div class="flex flex-col border-l border-border bg-admin-surface-card" style="width:270px; flex-shrink:0;">
                <div class="border-b border-border px-4 py-3">
                    <h3 class="text-xs font-semibold text-foreground uppercase tracking-wider">Quick Config</h3>
                    <p class="mt-0.5 text-[10px] text-muted-foreground">Apply to all same-type units on this floor</p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <!-- Type -->
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Unit Type</label>
                        <select v-model="qc.type" class="h-8 w-full rounded-lg border border-border px-2 text-xs bg-white dark:bg-slate-800">
                            <option value="">— Select type —</option>
                            <option v-for="t in enums.unit_types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>

                    <!-- Wing -->
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Wing</label>
                        <input v-model="qc.wing" type="text" placeholder="e.g. North, A"
                            class="h-8 w-full rounded-lg border border-border px-2 text-xs" />
                    </div>

                    <!-- Bedrooms / Bathrooms -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Beds</label>
                            <input v-model="qc.bedrooms" type="number" min="0" max="20" placeholder="0"
                                class="h-8 w-full rounded-lg border border-border px-2 text-xs text-center" />
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Baths</label>
                            <input v-model="qc.bathrooms" type="number" min="0" max="20" placeholder="0"
                                class="h-8 w-full rounded-lg border border-border px-2 text-xs text-center" />
                        </div>
                    </div>

                    <!-- Size / Price -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Size (sqft)</label>
                            <input v-model="qc.size_sqft" type="number" min="1" placeholder="1200"
                                class="h-8 w-full rounded-lg border border-border px-2 text-xs" />
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Price (BDT)</label>
                            <input v-model="qc.price" type="number" min="0" placeholder="0"
                                class="h-8 w-full rounded-lg border border-border px-2 text-xs" />
                        </div>
                    </div>

                    <!-- Facing / View -->
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Facing</label>
                        <select v-model="qc.facing_direction" class="h-8 w-full rounded-lg border border-border px-2 text-xs bg-white dark:bg-slate-800">
                            <option value="">— Any —</option>
                            <option v-for="f in enums.facing" :key="f" :value="f">{{ f }}</option>
                        </select>
                    </div>

                    <!-- Servant / Store toggles -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-center justify-between rounded-lg border border-border px-2.5 py-2">
                            <span class="text-[10px] font-medium text-muted-foreground">Servant</span>
                            <button type="button" @click="qc.servant_room = !qc.servant_room"
                                :class="['relative inline-flex h-4 w-7 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                    qc.servant_room ? 'bg-admin-accent' : 'bg-slate-200 dark:bg-slate-600']">
                                <span :class="['inline-block h-3 w-3 transform rounded-full bg-white shadow transition-transform',
                                    qc.servant_room ? 'translate-x-3' : 'translate-x-0']" />
                            </button>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-border px-2.5 py-2">
                            <span class="text-[10px] font-medium text-muted-foreground">Store</span>
                            <button type="button" @click="qc.store_room = !qc.store_room"
                                :class="['relative inline-flex h-4 w-7 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                    qc.store_room ? 'bg-admin-accent' : 'bg-slate-200 dark:bg-slate-600']">
                                <span :class="['inline-block h-3 w-3 transform rounded-full bg-white shadow transition-transform',
                                    qc.store_room ? 'translate-x-3' : 'translate-x-0']" />
                            </button>
                        </div>
                    </div>

                    <!-- Overwrite toggle -->
                    <div class="flex items-center justify-between rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 px-3 py-2">
                        <div>
                            <p class="text-[10px] font-semibold text-amber-700 dark:text-amber-400">Overwrite configured</p>
                            <p class="text-[9px] text-amber-600/80 dark:text-amber-500/80 mt-0.5">Also update already-configured units</p>
                        </div>
                        <button type="button" @click="qc.overwrite = !qc.overwrite"
                            :class="['relative inline-flex h-4 w-7 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                qc.overwrite ? 'bg-amber-500' : 'bg-slate-200 dark:bg-slate-600']">
                            <span :class="['inline-block h-3 w-3 transform rounded-full bg-white shadow transition-transform',
                                qc.overwrite ? 'translate-x-3' : 'translate-x-0']" />
                        </button>
                    </div>
                </div>

                <!-- Apply button -->
                <div class="border-t border-border p-3 space-y-2">
                    <button
                        @click="applyQuickConfig"
                        :disabled="applyingConfig || !activeFloor || !qc.type"
                        class="w-full inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-admin-accent text-xs font-semibold text-white hover:bg-admin-accent/90 transition-colors disabled:opacity-50"
                    >
                        <svg v-if="applyingConfig" class="animate-spin" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                        <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        Apply to {{ qc.type || '…' }} units
                    </button>
                    <p class="text-center text-[10px] text-muted-foreground">
                        Only affects <strong>{{ qc.type || '?' }}</strong> type on Floor {{ activeFloor ?? '?' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2"
            leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0">
            <div v-if="toast" :class="[
                    'fixed bottom-6 right-6 flex items-center gap-2.5 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-xl z-50',
                    toast.type === 'error' ? 'bg-red-500' : toast.type === 'info' ? 'bg-blue-500' : 'bg-emerald-500'
                ]">
                {{ toast.message }}
            </div>
        </Transition>
    </AdminLayout>
</template>
