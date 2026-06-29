<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import DatePicker from '@/Components/ui/date-picker/DatePicker.vue';
import MapPicker from '@/Components/MapPicker.vue';
import ImageUpload from '@/Components/ui/media/ImageUpload.vue';
import ImageGallery from '@/Components/ui/media/ImageGallery.vue';
import DocumentList from '@/Components/ui/media/DocumentList.vue';

const props = defineProps({
    form: { type: Object, required: true },
    enums: { type: Object, required: true },
    mode: { type: String, default: 'create' },
    currentCover: { type: String, default: null },
    currentImages: { type: Array, default: () => [] },
    currentDocuments: { type: Array, default: () => [] },
});

const emit = defineEmits(['submit']);

// ── Steps ──────────────────────────────────────────────────────────────
const STEPS = [
    { key: 'identity', label: 'Identity', subtitle: 'Name, code & status', kicker: 'Step 1 of 7' },
    { key: 'location', label: 'Location', subtitle: 'Area & map coordinates', kicker: 'Step 2 of 7' },
    { key: 'buildings', label: 'Buildings', subtitle: 'Blocks & specifications', kicker: 'Step 3 of 7' },
    { key: 'financials', label: 'Financials', subtitle: 'Developer & pricing', kicker: 'Step 4 of 7' },
    { key: 'facilities', label: 'Facilities', subtitle: 'Amenities & services', kicker: 'Step 5 of 7' },
    { key: 'compliance', label: 'Compliance', subtitle: 'Approvals & certifications', kicker: 'Step 6 of 7' },
    { key: 'media', label: 'Media & Docs', subtitle: 'Cover, gallery & files', kicker: 'Step 7 of 7' },
];

const activeStep = ref(0);

const currentStep = computed(() => STEPS[activeStep.value]);
const progress = computed(() => Math.round(((activeStep.value + 1) / STEPS.length) * 100));
const isFirst = computed(() => activeStep.value === 0);
const isLast = computed(() => activeStep.value === STEPS.length - 1);

function goTo(idx) { activeStep.value = idx; }
function goNext() { if (!isLast.value) activeStep.value++; }
function goBack() { if (!isFirst.value) activeStep.value--; }

function stepState(idx) {
    if (idx === activeStep.value) return 'active';
    if (idx < activeStep.value) return 'done';
    return 'pending';
}

// ── Field style ────────────────────────────────────────────────────────
const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';

// ── Step 1: auto project_code ─────────────────────────────────────────
// Format: {2 name initials}-{2 type initials}-{last 4 of ms timestamp}
// Fully dynamic — works for any type value without a hardcoded map.
// e.g. "Green Valley Villas" + residential  → GV-RE-4821
//      "Skyline Heights"     + mixed_use    → SH-MU-3907
const SKIP_WORDS = new Set(['the', 'a', 'an', 'of', 'at', 'in', 'and', 'by', 'for', 'with']);

function buildCode() {
    const name = props.form.name?.trim() ?? '';
    const type = props.form.type ?? '';
    if (!name || !type) return;

    const nameWords = name.split(/\s+/).filter(w => !SKIP_WORDS.has(w.toLowerCase()));
    const namePart = nameWords.slice(0, 2).map(w => w[0].toUpperCase()).join('');

    // Split on underscore or space so mixed_use → MU, residential → RE
    const typePart = type.split(/[_\s]+/).map(w => w[0].toUpperCase()).join('').slice(0, 2);

    const tsPart = String(Date.now()).slice(-4);

    props.form.project_code = `${namePart}-${typePart}-${tsPart}`;
}

watch([() => props.form.name, () => props.form.type], () => {
    if (props.mode === 'edit') return;
    buildCode();
});

// ── Step 3: Buildings & Sections ─────────────────────────────────────

function addBuilding() {
    props.form.buildings.push({
        id: null,
        name: `Building ${props.form.buildings.length + 1}`,
        total_floors: null,
        specifications: {},
        sections: [],
    });
}

function removeBuilding(bIdx) {
    props.form.buildings.splice(bIdx, 1);
    expandedSection.value = null;
    expandedBuildingSpecs.value = null;
}

function addSection(bIdx) {
    const building  = props.form.buildings[bIdx];
    const sections  = building.sections;
    const prevEnd   = sections.length > 0 ? (sections[sections.length - 1].floor_end ?? 0) : 0;
    const nextStart = sections.length === 0 ? 1 : prevEnd + 1;

    sections.push({
        id: null, name: '', type: 'residential',
        floor_start: nextStart, floor_end: null, planned_units: null, specifications: {},
    });
}

function removeSection(bIdx, sIdx) {
    const sections = props.form.buildings[bIdx].sections;
    sections.splice(sIdx, 1);

    if (expandedSection.value?.b === bIdx && expandedSection.value?.s === sIdx) {
        expandedSection.value = null;
    }

    // After removal, recascade floor_starts for remaining sections.
    // e.g. sections were [1-4, 5-10, 11-18], delete middle (5-10):
    //   → sections become [1-4, 11-18] → recascade → [1-4, 5-18]
    // The deleted section's floor range is absorbed by the section that follows it.
    recascade(bIdx);
}

// Re-lock all floor_starts and clamp floor_ends for a building.
// Called explicitly (not via deep watcher) to avoid re-entrancy loops.
function recascade(bIdx) {
    const b       = props.form.buildings[bIdx];
    const max     = b.total_floors ? Number(b.total_floors) : null;
    b.sections.forEach((s, idx) => {
        s.floor_start = idx === 0 ? 1 : (b.sections[idx - 1].floor_end ?? 0) + 1;
        if (max && s.floor_end > max) s.floor_end = max;
    });
}

// Called from floor_end @change and total_floors @change in the template
function onFloorEndChange(bIdx) { recascade(bIdx); }
function onTotalFloorsChange(bIdx) { recascade(bIdx); }

// Whether another section can still be added (not all floors covered)
function canAddSection(bIdx) {
    const b = props.form.buildings[bIdx];
    if (!b.total_floors) return true;
    const sections = b.sections;
    if (!sections.length) return true;
    const lastEnd = sections[sections.length - 1].floor_end ?? 0;
    return lastEnd < Number(b.total_floors);
}

// Track which section has its spec panel open: { b: buildingIdx, s: sectionIdx }
const expandedSection = ref(null);
function toggleSectionSpecs(bIdx, sIdx) {
    const same = expandedSection.value?.b === bIdx && expandedSection.value?.s === sIdx;
    expandedSection.value = same ? null : { b: bIdx, s: sIdx };
}
function isSectionExpanded(bIdx, sIdx) {
    return expandedSection.value?.b === bIdx && expandedSection.value?.s === sIdx;
}

// Track which building has its shared specs panel open
const expandedBuildingSpecs = ref(null);
function toggleBuildingSpecs(bIdx) {
    expandedBuildingSpecs.value = expandedBuildingSpecs.value === bIdx ? null : bIdx;
}

// Spec helpers — use object spread so Vue always sees a new object reference,
// which guarantees reactivity even when adding keys to a previously empty {}.
function updateBuildingSpec(bIdx, key, val) {
    const b = props.form.buildings[bIdx];
    b.specifications = { ...b.specifications, [key]: val };
}

function updateSectionSpec(bIdx, sIdx, key, val) {
    const s = props.form.buildings[bIdx].sections[sIdx];
    s.specifications = { ...s.specifications, [key]: val };
}

// ── Step 4: Developer toggle ──────────────────────────────────────────
const devMode = ref(props.form.developer_id ? 'select' : 'type');

function setDevMode(mode) {
    devMode.value = mode;
    if (mode === 'select') props.form.developer_name = '';
    else props.form.developer_id = null;
}

// ── Step 5: Facilities ────────────────────────────────────────────────
function hasFacility(id) {
    return (props.form.facility_ids ?? []).includes(id);
}

function toggleFacility(id) {
    if (!props.form.facility_ids) props.form.facility_ids = [];
    const idx = props.form.facility_ids.indexOf(id);
    if (idx === -1) props.form.facility_ids.push(id);
    else props.form.facility_ids.splice(idx, 1);
}

function groupAllSelected(items) {
    return items.every(i => hasFacility(i.id));
}

function selectAllInGroup(items) {
    items.forEach(i => { if (!hasFacility(i.id)) props.form.facility_ids.push(i.id); });
}

function clearGroup(items) {
    props.form.facility_ids = (props.form.facility_ids ?? []).filter(id => !items.some(i => i.id === id));
}

// ── Step 6: Compliance ────────────────────────────────────────────────
function addCompliance() {
    props.form.compliances.push({ id: null, name: '', type: 'approval', status: 'pending', obtained_date: null });
}

function removeCompliance(idx) {
    props.form.compliances.splice(idx, 1);
}
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <div class="grid items-start gap-7" style="grid-template-columns: 280px minmax(0,1fr)">

            <!-- ══ LEFT RAIL — Step navigation ═══════════════════════════════ -->
            <div class="sticky top-6 overflow-hidden rounded-xl border border-border bg-admin-surface-card p-2">
                <nav class="flex flex-col gap-0.5">
                    <button v-for="(step, idx) in STEPS" :key="step.key" type="button" @click="goTo(idx)" :class="[
                        'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors',
                        stepState(idx) === 'active'
                            ? 'bg-[#F1ECFF] dark:bg-admin-accent/10'
                            : 'hover:bg-slate-50 dark:hover:bg-white/[0.03]',
                    ]">
                        <!-- Circle -->
                        <div :class="[
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-colors',
                            stepState(idx) === 'active' ? 'bg-admin-accent text-white' :
                                stepState(idx) === 'done' ? 'bg-green-500 text-white' :
                                    'bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400',
                        ]">
                            <svg v-if="stepState(idx) === 'done'" width="11" height="11" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <!-- Labels -->
                        <div class="min-w-0">
                            <p :class="[
                                'truncate text-sm font-semibold',
                                stepState(idx) === 'active' ? 'text-admin-accent' : 'text-foreground',
                            ]">{{ step.label }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ step.subtitle }}</p>
                        </div>
                    </button>
                </nav>

                <!-- Cancel link at bottom -->
                <div class="mt-2 border-t border-border pt-2">
                    <Link :href="route('admin.projects.index')"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-slate-50 hover:text-foreground dark:hover:bg-white/[0.03]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5" />
                            <polyline points="12 19 5 12 12 5" />
                        </svg>
                        Back to Projects
                    </Link>
                </div>
            </div>

            <!-- ══ RIGHT CARD — Content ═══════════════════════════════════════ -->
            <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">

                <!-- Step Header -->
                <div class="border-b border-border px-6 py-5">
                    <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest text-admin-accent">
                        {{ currentStep.kicker }}
                    </p>
                    <h2 class="text-lg font-bold text-foreground">{{ currentStep.label }}</h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">{{ currentStep.subtitle }}</p>
                    <!-- Progress bar -->
                    <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                        <div class="h-full rounded-full bg-admin-accent transition-all duration-500"
                            :style="{ width: progress + '%' }" />
                    </div>
                </div>

                <!-- ── Step 1: Identity ─────────────────────────────────────── -->
                <div v-show="activeStep === 0" class="grid grid-cols-2 gap-5 p-6">

                    <!-- Row 1: Project Name (full width) -->
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Project Name <span class="text-destructive">*</span>
                        </Label>
                        <Input v-model="form.name" placeholder="e.g. Lakeside Residences"
                            :class="[f, form.errors.name && 'border-destructive']" />
                        <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <!-- Row 2: Type (left) | Project Code (right) — code auto-generates from name+type -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Type <span class="text-destructive">*</span>
                        </Label>
                        <Select :model-value="form.type" @update:model-value="form.type = $event">
                            <SelectTrigger :class="[f, form.errors.type && 'border-destructive']">
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.type" class="text-xs text-destructive">{{ form.errors.type }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Project Code</Label>
                        <Input v-model="form.project_code" placeholder="Auto-generated" maxlength="20"
                            :class="[f, form.errors.project_code && 'border-destructive']" />
                        <p v-if="form.errors.project_code" class="text-xs text-destructive">{{ form.errors.project_code
                        }}</p>
                        <p v-else class="text-xs text-muted-foreground">Auto-generated · editable</p>
                    </div>

                    <!-- Row 3: Start Date | Handover Date -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Start Date</Label>
                        <DatePicker :model-value="form.start_date" @update:model-value="form.start_date = $event"
                            placeholder="Pick a date" :class="f" />
                        <p v-if="form.errors.start_date" class="text-xs text-destructive">{{ form.errors.start_date }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Handover Date</Label>
                        <DatePicker :model-value="form.handover_date" @update:model-value="form.handover_date = $event"
                            placeholder="Pick a date" :class="f" />
                        <p v-if="form.errors.handover_date" class="text-xs text-destructive">{{
                            form.errors.handover_date }}</p>
                    </div>

                    <!-- Row 4: Status (left) | Theme Color (right) -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Status <span class="text-destructive">*</span>
                        </Label>
                        <Select :model-value="form.status" @update:model-value="form.status = $event">
                            <SelectTrigger :class="[f, form.errors.status && 'border-destructive']">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.status" class="text-xs text-destructive">{{ form.errors.status }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Theme Color</Label>
                        <div class="flex items-center gap-2 pt-1">
                            <button v-for="hex in enums.themes" :key="hex" type="button" @click="form.theme_color = hex"
                                :title="hex" :style="{ backgroundColor: hex }" :class="[
                                    'h-7 w-7 rounded-full border-2 transition-all',
                                    form.theme_color === hex ? 'border-foreground scale-110 shadow-md' : 'border-transparent hover:scale-105',
                                ]" />
                            <input type="color" :value="form.theme_color"
                                @input="form.theme_color = $event.target.value"
                                class="h-7 w-7 cursor-pointer rounded-full border border-border bg-transparent p-0.5"
                                title="Custom color" />
                        </div>
                    </div>

                    <!-- Row 5: Description (full width) -->
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Description</Label>
                        <Textarea v-model="form.description" placeholder="Brief overview of the project..." rows="4"
                            :class="[f, 'resize-none', form.errors.description && 'border-destructive']" />
                        <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}
                        </p>
                    </div>

                </div>

                <!-- ── Step 2: Location ─────────────────────────────────────── -->
                <div v-show="activeStep === 1" class="grid grid-cols-2 gap-5 p-6">

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Area /
                            Neighbourhood</Label>
                        <Input v-model="form.location" placeholder="e.g. Bashundhara R/A, Dhaka"
                            :class="[f, form.errors.location && 'border-destructive']" />
                        <p v-if="form.errors.location" class="text-xs text-destructive">{{ form.errors.location }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Full Address</Label>
                        <Input v-model="form.address" placeholder="Plot 12, Road 5, Block C..."
                            :class="[f, form.errors.address && 'border-destructive']" />
                        <p v-if="form.errors.address" class="text-xs text-destructive">{{ form.errors.address }}</p>
                    </div>

                    <!-- Lat / Lng — read-only display, filled by map pin -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Latitude</Label>
                        <Input type="number" step="any" v-model="form.latitude" placeholder="Set via map"
                            :class="[f, form.errors.latitude && 'border-destructive']" />
                        <p v-if="form.errors.latitude" class="text-xs text-destructive">{{ form.errors.latitude }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Longitude</Label>
                        <Input type="number" step="any" v-model="form.longitude" placeholder="Set via map"
                            :class="[f, form.errors.longitude && 'border-destructive']" />
                        <p v-if="form.errors.longitude" class="text-xs text-destructive">{{ form.errors.longitude }}</p>
                    </div>

                    <!-- Interactive Google Map -->
                    <div class="col-span-2">
                        <MapPicker :lat="form.latitude ? Number(form.latitude) : null"
                            :lng="form.longitude ? Number(form.longitude) : null" @update:lat="form.latitude = $event"
                            @update:lng="form.longitude = $event" />
                    </div>

                </div>

                <!-- ── Step 3: Buildings ────────────────────────────────────── -->
                <div v-show="activeStep === 2" class="p-6">

                    <!-- Add Building button -->
                    <button type="button" @click="addBuilding"
                        class="mb-5 inline-flex items-center gap-2 rounded-lg border border-dashed border-admin-accent/50 bg-admin-accent/5 px-4 py-2.5 text-sm font-medium text-admin-accent transition-colors hover:bg-admin-accent/10">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Add Building
                    </button>

                    <!-- Empty state -->
                    <div v-if="form.buildings.length === 0"
                        class="flex h-44 flex-col items-center justify-center rounded-xl border-2 border-dashed border-border text-center text-sm text-muted-foreground">
                        <svg class="mb-2 text-slate-300" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <path d="M3 9h18M9 21V9" />
                        </svg>
                        No buildings yet.<br>
                        <span class="mt-1 text-xs">Each building holds multiple floor sections — e.g. floors 1–2 Retail,
                            floors 3–15 Apartments.</span>
                    </div>

                    <!-- Buildings list -->
                    <div class="space-y-4">
                        <div v-for="(building, bIdx) in form.buildings" :key="bIdx"
                            class="overflow-hidden rounded-xl border border-border">

                            <!-- ── Building header ───────────────────────────────────── -->
                            <div
                                class="flex items-center gap-3 border-b border-border bg-slate-50 dark:bg-white/[0.03] px-4 py-3">
                                <svg class="shrink-0 text-admin-accent" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <path d="M3 9h18M9 21V9" />
                                </svg>

                                <Input v-model="building.name" placeholder="Building name (e.g. Tower A)"
                                    :class="[f, 'h-8 flex-1 text-sm font-semibold']" />

                                <div class="flex shrink-0 items-center gap-1.5">
                                    <span class="text-xs text-muted-foreground whitespace-nowrap">Total Floors</span>
                                    <Input type="number" min="1" v-model="building.total_floors"
                                        @change="onTotalFloorsChange(bIdx)"
                                        :class="[f, 'h-8 w-20 text-center text-sm']" />
                                </div>

                                <!-- Building Infrastructure (shared specs) toggle -->
                                <button type="button" @click="toggleBuildingSpecs(bIdx)" :class="['flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors',
                                    expandedBuildingSpecs === bIdx
                                        ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                        : 'border-border text-muted-foreground hover:text-foreground']">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3" />
                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                                    </svg>
                                    Infrastructure
                                    <svg :class="['transition-transform', expandedBuildingSpecs === bIdx && 'rotate-180']"
                                        width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </button>

                                <button type="button" @click="addSection(bIdx)"
                                    :disabled="!canAddSection(bIdx)"
                                    :class="['flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors',
                                        canAddSection(bIdx)
                                            ? 'border-border text-muted-foreground hover:border-admin-accent hover:text-admin-accent'
                                            : 'border-border/40 text-muted-foreground/40 cursor-not-allowed']">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Add Section
                                </button>

                                <button type="button" @click="removeBuilding(bIdx)"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                        <path d="M9 6V4h6v2" />
                                    </svg>
                                </button>
                            </div>

                            <!-- ── Shared building infrastructure specs ──────────────── -->
                            <!-- Lifts, parking, lobby, security, generator belong to the whole building -->
                            <div v-show="expandedBuildingSpecs === bIdx"
                                class="border-b border-border bg-admin-accent/[0.03] px-5 py-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-admin-accent/70">
                                    Shared Infrastructure — applies to the whole building
                                </p>
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                    <div class="space-y-1.5">
                                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Passenger
                                            Lifts</Label>
                                        <Input type="number" min="0"
                                            :model-value="building.specifications.passenger_lifts"
                                            @update:model-value="updateBuildingSpec(bIdx, 'passenger_lifts', $event)"
                                            placeholder="e.g. 3" :class="[f, 'text-sm']" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Service
                                            Lifts</Label>
                                        <Input type="number" min="0"
                                            :model-value="building.specifications.service_lifts"
                                            @update:model-value="updateBuildingSpec(bIdx, 'service_lifts', $event)"
                                            placeholder="e.g. 1" :class="[f, 'text-sm']" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Parking
                                            Levels (B)</Label>
                                        <Input type="number" min="0"
                                            :model-value="building.specifications.parking_levels"
                                            @update:model-value="updateBuildingSpec(bIdx, 'parking_levels', $event)"
                                            placeholder="e.g. 2" :class="[f, 'text-sm']" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Parking
                                            Capacity</Label>
                                        <Input type="number" min="0"
                                            :model-value="building.specifications.parking_capacity"
                                            @update:model-value="updateBuildingSpec(bIdx, 'parking_capacity', $event)"
                                            placeholder="e.g. 80" :class="[f, 'text-sm']" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Lobby
                                            Type</Label>
                                        <Select :model-value="building.specifications.lobby_type || undefined"
                                            @update:model-value="updateBuildingSpec(bIdx, 'lobby_type', $event)">
                                            <SelectTrigger :class="[f, 'text-sm']">
                                                <SelectValue placeholder="Select" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="opt in enums.block_spec_options?.lobby_types"
                                                    :key="opt" :value="opt">{{ opt }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Security
                                            System</Label>
                                        <Select :model-value="building.specifications.security || undefined"
                                            @update:model-value="updateBuildingSpec(bIdx, 'security', $event)">
                                            <SelectTrigger :class="[f, 'text-sm']">
                                                <SelectValue placeholder="Select" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="opt in enums.block_spec_options?.security" :key="opt"
                                                    :value="opt">{{ opt }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label
                                            class="text-xs font-medium text-slate-500 dark:text-slate-400">Generator</Label>
                                        <Select :model-value="building.specifications.generator || undefined"
                                            @update:model-value="updateBuildingSpec(bIdx, 'generator', $event)">
                                            <SelectTrigger :class="[f, 'text-sm']">
                                                <SelectValue placeholder="Select" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="opt in enums.block_spec_options?.generators"
                                                    :key="opt" :value="opt">{{ opt }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </div>

                            <!-- ── Floor sections within this building ───────────────── -->
                            <div v-if="building.sections.length === 0"
                                class="flex h-16 items-center justify-center text-xs text-muted-foreground">
                                No sections yet — click "Add Section" to define floor ranges.
                            </div>

                            <div class="divide-y divide-border">
                                <div v-for="(section, sIdx) in building.sections" :key="sIdx">

                                    <!-- Section row -->
                                    <div class="flex items-center gap-3 px-4 py-3">
                                        <!-- Floor range pill (live preview) -->
                                        <div
                                            class="flex shrink-0 items-center gap-1 rounded-lg bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-xs font-mono font-medium text-foreground min-w-[64px] justify-center">
                                            <span v-if="section.floor_start != null && section.floor_end != null">
                                                F{{ section.floor_start }}–{{ section.floor_end }}
                                            </span>
                                            <span v-else class="text-muted-foreground">Floors</span>
                                        </div>

                                        <Input v-model="section.name" placeholder="Label (e.g. Retail Zone, Apartments)"
                                            :class="[f, 'h-8 flex-1 text-sm']" />

                                        <Select :model-value="section.type" @update:model-value="section.type = $event">
                                            <SelectTrigger :class="[f, 'h-8 w-40 shrink-0 text-xs']">
                                                <SelectValue placeholder="Type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="bt in enums.block_types" :key="bt.value"
                                                    :value="bt.value">{{ bt.label }}</SelectItem>
                                            </SelectContent>
                                        </Select>

                                        <!-- Section-specific specs toggle (HVAC, cargo, internet) -->
                                        <button type="button" @click="toggleSectionSpecs(bIdx, sIdx)" :class="['flex h-8 shrink-0 items-center gap-1.5 rounded-lg border px-3 text-xs font-medium transition-colors',
                                            isSectionExpanded(bIdx, sIdx)
                                                ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                                : 'border-border text-muted-foreground hover:text-foreground']">
                                            Specs
                                            <svg :class="['transition-transform', isSectionExpanded(bIdx, sIdx) && 'rotate-180']"
                                                width="9" height="9" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5">
                                                <polyline points="6 9 12 15 18 9" />
                                            </svg>
                                        </button>

                                        <button type="button" @click="removeSection(bIdx, sIdx)"
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6l-1 14H6L5 6" />
                                                <path d="M10 11v6M14 11v6" />
                                                <path d="M9 6V4h6v2" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Floor range + planned units -->
                                    <div class="flex items-center gap-3 border-t border-border/60 bg-slate-50/60 dark:bg-white/[0.015] px-4 py-2.5">
                                        <span class="text-[11px] font-medium text-slate-400 shrink-0">Floor</span>

                                        <!-- floor_start: always auto-calculated, readonly -->
                                        <div class="relative">
                                            <Input type="number" :model-value="section.floor_start" readonly
                                                :class="[f, 'h-8 w-20 text-center text-sm cursor-not-allowed opacity-60 select-none']" />
                                            <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-400" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                            </svg>
                                        </div>

                                        <span class="text-sm text-slate-300">—</span>

                                        <!-- floor_end: user sets this, cascades to next section's start -->
                                        <Input type="number" min="0"
                                            :max="building.total_floors || undefined"
                                            v-model="section.floor_end"
                                            @change="onFloorEndChange(bIdx)"
                                            placeholder="End"
                                            :class="[f, 'h-8 w-20 text-center text-sm']" />

                                        <div class="mx-1 h-4 w-px bg-border shrink-0" />
                                        <span class="text-[11px] font-medium text-slate-400 shrink-0">Units</span>
                                        <Input type="number" min="0" v-model="section.planned_units" placeholder="0"
                                            :class="[f, 'h-8 w-24 text-center text-sm']" />
                                    </div>

                                    <!-- Section-specific specs (HVAC, cargo access, internet — can differ per floor range) -->
                                    <div v-show="isSectionExpanded(bIdx, sIdx)"
                                        class="border-t border-border bg-slate-50/50 dark:bg-white/[0.01] px-5 py-4">
                                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            Section Specs — specific to this floor range
                                        </p>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <Label
                                                    class="text-xs font-medium text-slate-500 dark:text-slate-400">HVAC
                                                    System</Label>
                                                <Select :model-value="section.specifications.hvac || undefined"
                                                    @update:model-value="updateSectionSpec(bIdx, sIdx, 'hvac', $event)">
                                                    <SelectTrigger :class="[f, 'text-sm']">
                                                        <SelectValue placeholder="Select" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="opt in enums.block_spec_options?.hvac"
                                                            :key="opt" :value="opt">{{ opt }}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div class="space-y-1.5">
                                                <Label
                                                    class="text-xs font-medium text-slate-500 dark:text-slate-400">Cargo
                                                    Access</Label>
                                                <Select :model-value="section.specifications.cargo_access || undefined"
                                                    @update:model-value="updateSectionSpec(bIdx, sIdx, 'cargo_access', $event)">
                                                    <SelectTrigger :class="[f, 'text-sm']">
                                                        <SelectValue placeholder="Select" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            v-for="opt in enums.block_spec_options?.cargo_access"
                                                            :key="opt" :value="opt">{{ opt }}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div class="space-y-1.5">
                                                <Label
                                                    class="text-xs font-medium text-slate-500 dark:text-slate-400">Internet</Label>
                                                <Select :model-value="section.specifications.internet || undefined"
                                                    @update:model-value="updateSectionSpec(bIdx, sIdx, 'internet', $event)">
                                                    <SelectTrigger :class="[f, 'text-sm']">
                                                        <SelectValue placeholder="Select" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="opt in enums.block_spec_options?.internet"
                                                            :key="opt" :value="opt">{{ opt }}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div class="space-y-1.5">
                                                <Label
                                                    class="text-xs font-medium text-slate-500 dark:text-slate-400">Electrical
                                                    Capacity</Label>
                                                <Input
                                                    :model-value="section.specifications.electrical_capacity"
                                                    @update:model-value="updateSectionSpec(bIdx, sIdx, 'electrical_capacity', $event)"
                                                    placeholder="e.g. 2.5 MVA" :class="[f, 'text-sm']" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- ── Step 4: Financials ───────────────────────────────────── -->
                <div v-show="activeStep === 3" class="space-y-6 p-6">

                    <!-- Developer section -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Developer</Label>
                            <div class="flex overflow-hidden rounded-lg border border-border text-xs">
                                <button type="button" @click="setDevMode('select')"
                                    :class="['px-3 py-1.5 font-medium transition-colors', devMode === 'select' ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:text-foreground']">From
                                    List</button>
                                <button type="button" @click="setDevMode('type')"
                                    :class="['px-3 py-1.5 font-medium transition-colors', devMode === 'type' ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:text-foreground']">Type
                                    Name</button>
                            </div>
                        </div>
                        <Select v-if="devMode === 'select'"
                            :model-value="form.developer_id ? String(form.developer_id) : undefined"
                            @update:model-value="form.developer_id = $event ? Number($event) : null">
                            <SelectTrigger :class="f">
                                <SelectValue placeholder="Select developer" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="d in enums.developers" :key="d.id" :value="String(d.id)">{{ d.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Input v-else v-model="form.developer_name" placeholder="e.g. Skyline Builders Ltd."
                            :class="f" />
                    </div>

                    <div class="grid grid-cols-2 gap-5">

                        <!-- Land area + unit -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Land Area</Label>
                            <div class="flex gap-2">
                                <Input type="number" step="any" min="0" v-model="form.land_area" placeholder="18"
                                    :class="[f, 'flex-1']" />
                                <Select :model-value="form.land_area_unit"
                                    @update:model-value="form.land_area_unit = $event">
                                    <SelectTrigger :class="[f, 'w-28 shrink-0']">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="u in enums.land_area_units" :key="u" :value="u">{{ u }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Built-up Area
                                (sqft)</Label>
                            <Input type="number" min="0" v-model="form.built_up_area" placeholder="52000" :class="f" />
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Estimated Project
                                Value (BDT)</Label>
                            <Input type="number" min="0" v-model="form.estimated_value" placeholder="480000000"
                                :class="f" />
                        </div>

                        <!-- Booking amount + type toggle -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Booking Amount</Label>
                            <div class="flex gap-2">
                                <Input type="number" step="any" min="0" v-model="form.booking_amount"
                                    placeholder="500000" :class="[f, 'flex-1']" />
                                <div class="flex overflow-hidden rounded-lg border border-border shrink-0">
                                    <button v-for="bt in enums.booking_amount_types" :key="bt.value" type="button"
                                        @click="form.booking_amount_type = bt.value"
                                        :class="['px-3 text-xs font-semibold transition-colors', form.booking_amount_type === bt.value ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:text-foreground']">{{
                                            bt.value === 'fixed' ? 'BDT' : '%' }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Commission (%)</Label>
                            <div class="relative">
                                <Input type="number" step="0.1" min="0" max="100" v-model="form.commission_pct"
                                    placeholder="2.5" :class="[f, 'pr-8']" />
                                <span
                                    class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">%</span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Payment Plan
                                (months)</Label>
                            <Input type="number" min="1" v-model="form.payment_plan_months" placeholder="36"
                                :class="f" />
                        </div>

                    </div>

                    <!-- ── Sale terms (non-lease) ─────────────────────────────── -->
                    <div class="rounded-xl border border-border">
                        <div class="border-b border-border px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Sale Terms</p>
                        </div>
                        <div class="grid grid-cols-2 gap-5 p-4">

                            <!-- Service charge -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    Service Charge <span class="font-normal text-slate-400">(BDT / sqft / month)</span>
                                </Label>
                                <div class="relative">
                                    <Input type="number" step="0.01" min="0" v-model="form.service_charge_sqft"
                                        placeholder="e.g. 5" :class="[f, 'pr-20']" />
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">BDT/sqft</span>
                                </div>
                                <p class="text-[11px] text-slate-400">Monthly fee paid by owner for common areas —
                                    lifts, cleaning, security, generator.</p>
                            </div>

                            <!-- Maintenance contract -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    Maintenance Contract <span class="font-normal text-slate-400">(years
                                        included)</span>
                                </Label>
                                <div class="relative">
                                    <Input type="number" min="0" max="50" v-model="form.maintenance_years"
                                        placeholder="e.g. 2" :class="[f, 'pr-14']" />
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">yrs</span>
                                </div>
                                <p class="text-[11px] text-slate-400">Free maintenance period included with purchase.
                                    After this, owner pays separately.</p>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- ── Step 5: Facilities ───────────────────────────────────── -->
                <div v-show="activeStep === 4" class="p-6">

                    <div v-if="!enums.facility_groups || enums.facility_groups.length === 0"
                        class="flex h-32 items-center justify-center text-sm text-muted-foreground">
                        No facilities found. Run the FacilitySeeder to populate.
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="group in enums.facility_groups" :key="group.group">
                            <div class="mb-3 flex items-center justify-between">
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    {{ group.group }}
                                </p>
                                <button type="button"
                                    @click="groupAllSelected(group.items) ? clearGroup(group.items) : selectAllInGroup(group.items)"
                                    class="text-xs text-admin-accent hover:underline">
                                    {{ groupAllSelected(group.items) ? 'Clear all' : 'Select all' }}
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="item in group.items" :key="item.id" type="button"
                                    @click="toggleFacility(item.id)" :class="[
                                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                                        hasFacility(item.id)
                                            ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                            : 'border-border bg-transparent text-muted-foreground hover:border-admin-accent/40 hover:text-foreground',
                                    ]">
                                    <svg v-if="hasFacility(item.id)" width="9" height="9" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    {{ item.name }}
                                </button>
                            </div>
                        </div>

                        <p class="text-xs text-muted-foreground">
                            {{ (form.facility_ids ?? []).length }} facilit{{ (form.facility_ids ?? []).length === 1 ?
                                'y' : 'ies' }} selected
                        </p>
                    </div>

                </div>

                <!-- ── Step 6: Compliance ───────────────────────────────────── -->
                <div v-show="activeStep === 5" class="p-6">

                    <button type="button" @click="addCompliance"
                        class="mb-5 inline-flex items-center gap-2 rounded-lg border border-dashed border-admin-accent/50 bg-admin-accent/5 px-4 py-2.5 text-sm font-medium text-admin-accent transition-colors hover:bg-admin-accent/10">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Add Approval / Certification
                    </button>

                    <div v-if="form.compliances.length === 0"
                        class="flex h-40 flex-col items-center justify-center rounded-xl border-2 border-dashed border-border text-center text-sm text-muted-foreground">
                        <svg class="mb-2 text-slate-300" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                        No compliance records yet.
                    </div>

                    <div class="space-y-3">
                        <div v-for="(item, idx) in form.compliances" :key="idx" class="rounded-xl border border-border">
                            <div class="grid grid-cols-[1fr_140px_160px_140px_36px] items-end gap-3 p-4">
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Name</Label>
                                    <Input v-model="item.name" placeholder="e.g. RAJUK Approval"
                                        :class="[f, 'text-sm']" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Type</Label>
                                    <Select :model-value="item.type" @update:model-value="item.type = $event">
                                        <SelectTrigger :class="[f, 'text-sm']">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="approval">Approval</SelectItem>
                                            <SelectItem value="certification">Certification</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Status</Label>
                                    <Select :model-value="item.status" @update:model-value="item.status = $event">
                                        <SelectTrigger :class="[f, 'text-sm']">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="obtained">Obtained</SelectItem>
                                            <SelectItem value="not_required">Not Required</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Date
                                        Obtained</Label>
                                    <DatePicker :model-value="item.obtained_date"
                                        @update:model-value="item.obtained_date = $event" placeholder="Pick a date"
                                        :class="[f, 'text-sm']" :disabled="item.status !== 'obtained'" />
                                </div>
                                <button type="button" @click="removeCompliance(idx)"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center self-end rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                        <path d="M9 6V4h6v2" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── Step 7: Media & Docs ─────────────────────────────────── -->
                <div v-show="activeStep === 6" class="p-6">
                    <div class="grid grid-cols-2 gap-6">

                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Cover Image</Label>
                            <ImageUpload :file="form.cover" @update:file="form.cover = $event"
                                :removed="form.remove_cover ?? false" @update:removed="form.remove_cover = $event"
                                :preview="currentCover" hint="JPG, PNG, WEBP · Max 5 MB" />
                            <p v-if="form.errors?.cover" class="text-xs text-destructive">{{ form.errors.cover }}</p>
                        </div>

                        <div class="space-y-2">
                            <ImageGallery :existing="currentImages" :new-files="form.new_images ?? []"
                                @update:new-files="form.new_images = $event" :remove-ids="form.remove_images ?? []"
                                @update:remove-ids="form.remove_images = $event">
                                <template #label>
                                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Gallery
                                        Images</Label>
                                </template>
                            </ImageGallery>
                            <p v-if="form.errors?.new_images" class="text-xs text-destructive">{{ form.errors.new_images
                                }}</p>
                        </div>

                        <div class="col-span-2 space-y-2">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Documents</Label>
                            <DocumentList :existing="currentDocuments" :new-files="form.new_documents ?? []"
                                @update:new-files="form.new_documents = $event"
                                :remove-ids="form.remove_documents ?? []"
                                @update:remove-ids="form.remove_documents = $event" />
                            <p v-if="form.errors?.new_documents" class="text-xs text-destructive">{{
                                form.errors.new_documents }}</p>
                        </div>

                    </div>
                </div>

                <!-- ── Footer ───────────────────────────────────────────────── -->
                <div class="flex items-center justify-between border-t border-border px-6 py-4">
                    <!-- Back -->
                    <button type="button" @click="goBack" :disabled="isFirst"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5" />
                            <polyline points="12 19 5 12 12 5" />
                        </svg>
                        Back
                    </button>

                    <div class="flex items-center gap-3">
                        <!-- Save Draft -->
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:opacity-60">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            Save Draft
                        </button>

                        <!-- Next (not last step) -->
                        <button v-if="!isLast" type="button" @click="goNext"
                            class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-4 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90">
                            Next
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </button>

                        <!-- Publish (last step) -->
                        <button v-else type="submit" :disabled="form.processing"
                            class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-5 text-sm font-semibold text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60">
                            <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 2L11 13" />
                                <path d="M22 2L15 22l-4-9-9-4 19-7z" />
                            </svg>
                            {{ mode === 'edit' ? 'Save Changes' : 'Publish Project' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</template>
