<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Input }    from '@/Components/ui/input';
import { Label }    from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/Components/ui/select';

const props = defineProps({
    unit:  { type: Object, required: true },
    enums: { type: Object, required: true },
});

// ── Sidebar nav ────────────────────────────────────────────────────────────
const SECTIONS = [
    { id: 'identity',     label: 'Identity',       icon: 'M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1m-4 0h4' },
    { id: 'specs',        label: 'Specifications',  icon: 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2' },
    { id: 'measurements', label: 'Measurements',    icon: 'M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0 0 21 18.382V7.618a1 1 0 0 0-.553-.894L15 4m0 13V4m0 0L9 7' },
    { id: 'pricing',      label: 'Pricing',          icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z' },
    { id: 'floorplan',    label: 'Floor Plan',       icon: 'M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z' },
    { id: 'media',        label: 'Media',             icon: 'M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2 1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z' },
    { id: 'availability', label: 'Availability',     icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z' },
    { id: 'mortgage',     label: 'Mortgage',          icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z' },
];

const activeSection = ref('identity');

// ── Form ───────────────────────────────────────────────────────────────────
const form = useForm({
    _from: props.unit.block_id ? 'blueprint' : null,

    unit_number:  props.unit.unit_number,
    unit_code:    props.unit.unit_code    ?? '',
    type:         props.unit.type         ?? '',
    wing:         props.unit.wing         ?? '',
    description:  props.unit.description  ?? '',
    project_id:   props.unit.project_id,
    block_id:     props.unit.block_id,
    floor_end:    props.unit.floor_end    ?? '',

    bedrooms:          props.unit.bedrooms          ?? '',
    bathrooms:         props.unit.bathrooms         ?? '',
    balconies:         props.unit.balconies         ?? '',
    servant_room:      props.unit.servant_room      ?? false,
    store_room:        props.unit.store_room        ?? false,
    parking_spaces:    props.unit.parking_spaces    ?? '',
    facing_direction:  props.unit.facing_direction  ?? '',
    view:              props.unit.view              ?? '',

    size_sqft:           props.unit.size_sqft           ?? '',
    super_built_up_area: props.unit.super_built_up_area ?? '',
    carpet_area:         props.unit.carpet_area         ?? '',
    ceiling_height:      props.unit.ceiling_height      ?? '',
    terrace_area:        props.unit.terrace_area        ?? '',
    parking_area:        props.unit.parking_area        ?? '',

    price:               props.unit.price               ?? '',
    launch_price:        props.unit.launch_price        ?? '',
    current_price:       props.unit.current_price       ?? '',
    parking_price:       props.unit.parking_price       ?? '',
    registration_fee:    props.unit.registration_fee    ?? '',
    vat_pct:             props.unit.vat_pct             ?? '',
    monthly_maintenance: props.unit.monthly_maintenance ?? '',
    booking_amount:      props.unit.booking_amount      ?? '',

    video_url:    props.unit.video_url    ?? '',
    tour_360_url: props.unit.tour_360_url ?? '',

    status:         props.unit.status         ?? 'not_configured',
    launch_date:    props.unit.launch_date    ?? '',
    available_date: props.unit.available_date ?? '',
    handover_date:  props.unit.handover_date  ?? '',

    eligible_banks:      props.unit.eligible_banks      ?? [],
    max_loan_amount:     props.unit.max_loan_amount      ?? '',
    payment_plan_months: props.unit.payment_plan_months  ?? '',

    floor_plan:            null,
    remove_floor_plan:     false,
    floor_plan_pdf:        null,
    remove_floor_plan_pdf: false,
    cad_dwg:               null,
    remove_cad_dwg:        false,

    new_images: [], remove_images: [],
    new_drone:  [], remove_drone:  [],
    new_interior: [], remove_interior: [],
});

function submit() {
    form.post(route('admin.units.update', props.unit.id), { method: 'put', forceFormData: true });
}

const backHref = computed(() =>
    props.unit.block_id ? route('admin.blueprint.show', props.unit.project_id) : route('admin.units.index')
);

// ── Section "has data" indicators ──────────────────────────────────────────
const sectionFilled = computed(() => ({
    identity:     !!(form.unit_number || form.type),
    specs:        !!(form.bedrooms || form.bathrooms || form.size_sqft),
    measurements: !!(form.size_sqft || form.carpet_area),
    pricing:      !!(form.price || form.current_price),
    floorplan:    !!(floorPlanPreview.value || floorPlanPdfName.value),
    media:        !!(imagesPreviews.value.length || dronePreviews.value.length),
    availability: !!(form.launch_date || form.available_date),
    mortgage:     !!(form.eligible_banks.length || form.max_loan_amount),
}));

// ── Bank toggle ────────────────────────────────────────────────────────────
function toggleBank(bank) {
    const idx = form.eligible_banks.indexOf(bank);
    if (idx === -1) form.eligible_banks.push(bank);
    else            form.eligible_banks.splice(idx, 1);
}

// ── File helpers ───────────────────────────────────────────────────────────
const floorPlanPreview  = ref(props.unit.floor_plan     || null);
const floorPlanPdfName  = ref(props.unit.floor_plan_pdf ? 'Existing PDF' : null);
const cadName           = ref(props.unit.cad_dwg         ? 'Existing file' : null);
const imagesPreviews    = ref([...(props.unit.images   || [])]);
const dronePreviews     = ref([...(props.unit.drone    || [])]);
const interiorPreviews  = ref([...(props.unit.interior || [])]);

function onFloorPlanChange(e) {
    const file = e.target.files[0]; if (!file) return;
    form.floor_plan = file; floorPlanPreview.value = URL.createObjectURL(file);
}
function onFloorPlanPdfChange(e) {
    const file = e.target.files[0]; if (!file) return;
    form.floor_plan_pdf = file; floorPlanPdfName.value = file.name;
}
function onCadChange(e) {
    const file = e.target.files[0]; if (!file) return;
    form.cad_dwg = file; cadName.value = file.name;
}
function addImages(e, col) {
    const files = Array.from(e.target.files);
    if (col === 'images')   { form.new_images.push(...files);   files.forEach(f => imagesPreviews.value.push({ url: URL.createObjectURL(f), name: f.name, _new: true })); }
    if (col === 'drone')    { form.new_drone.push(...files);    files.forEach(f => dronePreviews.value.push({ url: URL.createObjectURL(f), name: f.name, _new: true })); }
    if (col === 'interior') { form.new_interior.push(...files); files.forEach(f => interiorPreviews.value.push({ url: URL.createObjectURL(f), name: f.name, _new: true })); }
    e.target.value = '';
}
function removeImageItem(col, idx) {
    const map = { images: [imagesPreviews, form.remove_images], drone: [dronePreviews, form.remove_drone], interior: [interiorPreviews, form.remove_interior] };
    const [list, removeArr] = map[col];
    const item = list.value[idx];
    if (!item._new && item.id) removeArr.push(item.id);
    list.value.splice(idx, 1);
}

// ── Status color ───────────────────────────────────────────────────────────
const STATUS_COLORS = {
    not_configured: 'bg-orange-100 text-orange-700 border-orange-200',
    configured:     'bg-blue-100 text-blue-700 border-blue-200',
    available:      'bg-emerald-100 text-emerald-700 border-emerald-200',
    booked:         'bg-red-100 text-red-700 border-red-200',
    sold:           'bg-purple-100 text-purple-700 border-purple-200',
};
</script>

<template>
    <Head :title="`Configure — ${unit.unit_number}`" />

    <AdminLayout>
        <!-- ── Page header ──────────────────────────────────────────── -->
        <div class="mb-5 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                    <Link :href="backHref" class="hover:text-admin-accent transition-colors">
                        {{ unit.block_id ? 'Blueprint' : 'Units' }}
                    </Link>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="text-foreground font-medium">{{ unit.unit_number }}</span>
                </nav>
                <h1 class="text-xl font-bold text-foreground">Unit Configuration</h1>

                <!-- Context strip -->
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span v-if="unit.project_name" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs text-muted-foreground">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        {{ unit.project_name }}
                    </span>
                    <span v-if="unit.building_name" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs text-muted-foreground">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        {{ unit.building_name }}
                    </span>
                    <span v-if="unit.section_name" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs text-muted-foreground">
                        Section: {{ unit.section_name }}
                    </span>
                    <span v-if="unit.floor" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs text-muted-foreground">
                        Floor {{ unit.floor }}<template v-if="unit.floor_end">–{{ unit.floor_end }}</template>
                    </span>
                    <span :class="['inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold', STATUS_COLORS[unit.status] ?? '']">
                        {{ enums.statuses?.find(s => s.value === unit.status)?.label ?? unit.status }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link :href="backHref"
                    class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-border bg-white dark:bg-slate-800 px-4 text-sm font-medium text-foreground hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </Link>
                <button @click="submit" :disabled="form.processing"
                    class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-admin-accent px-5 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors disabled:opacity-60 shadow-sm">
                    <svg v-if="form.processing" class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                    Save Unit
                </button>
            </div>
        </div>

        <!-- ── Two-column layout ────────────────────────────────────── -->
        <div class="flex gap-6" style="min-height: calc(100vh - 220px);">

            <!-- ── LEFT: Sidebar nav ─────────────────────────────────── -->
            <div class="w-56 flex-shrink-0">
                <nav class="sticky top-6 rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                    <div class="border-b border-border px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Sections</p>
                    </div>
                    <ul class="p-2 space-y-0.5">
                        <li v-for="sec in SECTIONS" :key="sec.id">
                            <button
                                type="button"
                                @click="activeSection = sec.id"
                                :class="[
                                    'w-full flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-all text-left',
                                    activeSection === sec.id
                                        ? 'bg-admin-accent text-white shadow-sm'
                                        : 'text-muted-foreground hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-foreground'
                                ]"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" v-html="sec.icon" class="flex-shrink-0" />
                                <span class="flex-1">{{ sec.label }}</span>
                                <!-- Filled indicator -->
                                <span v-if="sectionFilled[sec.id]"
                                    :class="['h-1.5 w-1.5 rounded-full flex-shrink-0', activeSection === sec.id ? 'bg-white/60' : 'bg-emerald-500']" />
                            </button>
                        </li>
                    </ul>
                    <!-- Quick save in sidebar -->
                    <div class="border-t border-border p-3">
                        <button @click="submit" :disabled="form.processing"
                            class="w-full inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-admin-accent text-xs font-semibold text-white hover:bg-admin-accent/90 transition-colors disabled:opacity-60">
                            <svg v-if="form.processing" class="animate-spin" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Save Changes
                        </button>
                    </div>
                </nav>
            </div>

            <!-- ── RIGHT: Form content ───────────────────────────────── -->
            <div class="flex-1 min-w-0">
                <form @submit.prevent="submit">

                    <!-- ── IDENTITY ──────────────────────────────────── -->
                    <div v-show="activeSection === 'identity'" class="space-y-6">
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Unit Identity</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">Basic identifiers and type classification</p>
                            </div>
                            <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                                <div class="space-y-2">
                                    <Label>Unit Number <span class="text-red-400">*</span></Label>
                                    <Input v-model="form.unit_number" placeholder="e.g. A-1205"
                                        :class="form.errors.unit_number ? 'border-red-400' : ''" />
                                    <p v-if="form.errors.unit_number" class="text-xs text-red-500">{{ form.errors.unit_number }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label>Unit Code</Label>
                                    <Input v-model="form.unit_code" placeholder="e.g. LVR-A-1205" />
                                </div>

                                <div class="space-y-2">
                                    <Label>Unit Type</Label>
                                    <Select v-model="form.type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label>Wing</Label>
                                    <Input v-model="form.wing" placeholder="e.g. North, A, East" />
                                </div>

                                <!-- Floor Range End — block units only -->
                                <div v-if="unit.floor_end || unit.floor_end === 0" class="space-y-2">
                                    <Label>
                                        Floor Range End
                                        <span class="ml-1.5 inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-1.5 py-0.5 text-[9px] font-semibold text-amber-700 dark:text-amber-400">Block Unit</span>
                                    </Label>
                                    <Input v-model="form.floor_end" type="number" min="1" max="300" :placeholder="String(unit.floor ?? '—')" />
                                    <p class="text-[10px] text-muted-foreground">Spans floor {{ unit.floor }} → {{ form.floor_end || '?' }}</p>
                                </div>

                                <div class="space-y-2 sm:col-span-2 lg:col-span-3">
                                    <Label>Description</Label>
                                    <Textarea v-model="form.description" rows="3" placeholder="Corner unit, lake-facing, premium finishing…" class="resize-none" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SPECIFICATIONS ─────────────────────────────── -->
                    <div v-show="activeSection === 'specs'" class="space-y-6">
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Specifications</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">Room counts, direction, and extras</p>
                            </div>
                            <div class="p-6 grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">

                                <div class="space-y-2">
                                    <Label>Bedrooms</Label>
                                    <Input v-model="form.bedrooms" type="number" min="0" max="20" placeholder="0" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Bathrooms</Label>
                                    <Input v-model="form.bathrooms" type="number" min="0" max="20" placeholder="0" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Balconies</Label>
                                    <Input v-model="form.balconies" type="number" min="0" max="10" placeholder="0" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Parking Spaces</Label>
                                    <Input v-model="form.parking_spaces" type="number" min="0" max="20" placeholder="0" />
                                </div>

                                <div class="space-y-2">
                                    <Label>Facing Direction</Label>
                                    <Select v-model="form.facing_direction">
                                        <SelectTrigger><SelectValue placeholder="Select" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="f in enums.facing" :key="f" :value="f">{{ f }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>View</Label>
                                    <Select v-model="form.view">
                                        <SelectTrigger><SelectValue placeholder="Select" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="v in enums.views" :key="v" :value="v">{{ v }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <!-- Boolean extras -->
                            <div class="border-t border-border px-6 py-4">
                                <Label class="mb-3 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Extra Rooms</Label>
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" @click="form.servant_room = !form.servant_room"
                                        :class="[
                                            'flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-medium transition-all',
                                            form.servant_room
                                                ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                                : 'border-border text-muted-foreground hover:border-admin-accent/40 hover:text-foreground'
                                        ]">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                        Servant Room
                                        <span v-if="form.servant_room" class="h-1.5 w-1.5 rounded-full bg-admin-accent" />
                                    </button>
                                    <button type="button" @click="form.store_room = !form.store_room"
                                        :class="[
                                            'flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-medium transition-all',
                                            form.store_room
                                                ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                                : 'border-border text-muted-foreground hover:border-admin-accent/40 hover:text-foreground'
                                        ]">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9"/><rect x="2" y="3" width="20" height="6" rx="1"/></svg>
                                        Store Room
                                        <span v-if="form.store_room" class="h-1.5 w-1.5 rounded-full bg-admin-accent" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── MEASUREMENTS ───────────────────────────────── -->
                    <div v-show="activeSection === 'measurements'" class="space-y-6">
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Measurements</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">All areas in sqft, ceiling height in ft</p>
                            </div>
                            <div class="p-6 grid grid-cols-2 gap-5 sm:grid-cols-3">
                                <div class="space-y-2">
                                    <Label>Built-up Area (sqft)</Label>
                                    <Input v-model="form.size_sqft" type="number" min="1" placeholder="e.g. 1250" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Super Built-up Area (sqft)</Label>
                                    <Input v-model="form.super_built_up_area" type="number" min="1" placeholder="e.g. 1580" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Net / Carpet Area (sqft)</Label>
                                    <Input v-model="form.carpet_area" type="number" min="1" placeholder="e.g. 1080" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Ceiling Height (ft)</Label>
                                    <Input v-model="form.ceiling_height" type="number" step="0.1" min="0" placeholder="e.g. 10.5" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Terrace Area (sqft)</Label>
                                    <Input v-model="form.terrace_area" type="number" min="0" placeholder="e.g. 120" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Parking Area (sqft)</Label>
                                    <Input v-model="form.parking_area" type="number" min="0" placeholder="e.g. 240" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── PRICING ────────────────────────────────────── -->
                    <div v-show="activeSection === 'pricing'" class="space-y-6">
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Pricing</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">All amounts in BDT</p>
                            </div>
                            <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-2">
                                    <Label>Base Price</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                        <Input v-model="form.price" type="number" min="0" placeholder="12,500,000" class="pl-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Launch Price</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                        <Input v-model="form.launch_price" type="number" min="0" placeholder="11,875,000" class="pl-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Current Price</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                        <Input v-model="form.current_price" type="number" min="0" placeholder="12,500,000" class="pl-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Parking Price</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                        <Input v-model="form.parking_price" type="number" min="0" placeholder="800,000" class="pl-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Registration Fee</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                        <Input v-model="form.registration_fee" type="number" min="0" placeholder="450,000" class="pl-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>VAT (%)</Label>
                                    <div class="relative">
                                        <Input v-model="form.vat_pct" type="number" step="0.01" min="0" max="100" placeholder="e.g. 7.5" class="pr-6" />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">%</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Monthly Maintenance</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                        <Input v-model="form.monthly_maintenance" type="number" min="0" placeholder="4,500" class="pl-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Booking Amount</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                        <Input v-model="form.booking_amount" type="number" min="0" placeholder="500,000" class="pl-6" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── FLOOR PLAN ─────────────────────────────────── -->
                    <div v-show="activeSection === 'floorplan'" class="space-y-6">
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Floor Plan</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">Architectural drawing and technical files</p>
                            </div>
                            <div class="p-6 grid grid-cols-1 gap-6 sm:grid-cols-3">

                                <!-- Image -->
                                <div class="space-y-2">
                                    <Label>Floor Plan Image (PNG / JPG)</Label>
                                    <div class="relative rounded-xl border-2 border-dashed border-border bg-slate-50 dark:bg-slate-800 p-4 text-center transition-colors hover:border-admin-accent/40">
                                        <img v-if="floorPlanPreview" :src="floorPlanPreview" alt="Floor plan" class="mx-auto mb-3 max-h-36 rounded-lg object-contain" />
                                        <div v-else class="py-4">
                                            <svg class="mx-auto mb-2 text-muted-foreground/30" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                            <p class="text-xs text-muted-foreground">Click to upload</p>
                                        </div>
                                        <input type="file" accept=".jpg,.jpeg,.png,.webp" class="absolute inset-0 cursor-pointer opacity-0" @change="onFloorPlanChange" />
                                    </div>
                                    <button v-if="floorPlanPreview" type="button"
                                        @click="form.remove_floor_plan = true; floorPlanPreview = null; form.floor_plan = null"
                                        class="text-xs text-red-500 hover:underline">Remove image</button>
                                </div>

                                <!-- PDF -->
                                <div class="space-y-2">
                                    <Label>Floor Plan PDF</Label>
                                    <div class="relative rounded-xl border-2 border-dashed border-border bg-slate-50 dark:bg-slate-800 p-6 text-center transition-colors hover:border-admin-accent/40">
                                        <svg class="mx-auto mb-2 text-red-400" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <p class="text-xs text-muted-foreground">{{ floorPlanPdfName ?? 'Upload PDF' }}</p>
                                        <input type="file" accept=".pdf" class="absolute inset-0 cursor-pointer opacity-0" @change="onFloorPlanPdfChange" />
                                    </div>
                                    <button v-if="floorPlanPdfName" type="button"
                                        @click="form.remove_floor_plan_pdf = true; floorPlanPdfName = null; form.floor_plan_pdf = null"
                                        class="text-xs text-red-500 hover:underline">Remove PDF</button>
                                </div>

                                <!-- CAD/DWG -->
                                <div class="space-y-2">
                                    <Label>CAD / DWG File</Label>
                                    <div class="relative rounded-xl border-2 border-dashed border-border bg-slate-50 dark:bg-slate-800 p-6 text-center transition-colors hover:border-admin-accent/40">
                                        <svg class="mx-auto mb-2 text-blue-400" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                        <p class="text-xs text-muted-foreground">{{ cadName ?? 'Upload .dwg / .dxf' }}</p>
                                        <input type="file" class="absolute inset-0 cursor-pointer opacity-0" @change="onCadChange" />
                                    </div>
                                    <button v-if="cadName" type="button"
                                        @click="form.remove_cad_dwg = true; cadName = null; form.cad_dwg = null"
                                        class="text-xs text-red-500 hover:underline">Remove file</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── MEDIA ──────────────────────────────────────── -->
                    <div v-show="activeSection === 'media'" class="space-y-5">

                        <!-- Reusable gallery block -->
                        <template v-for="(gallery, gKey) in [
                            { key: 'images',   label: 'Unit Photos',     previews: imagesPreviews },
                            { key: 'drone',    label: 'Drone Shots',     previews: dronePreviews },
                            { key: 'interior', label: 'Interior Photos', previews: interiorPreviews },
                        ]" :key="gKey">
                            <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                                <div class="flex items-center justify-between border-b border-border px-6 py-4">
                                    <h3 class="text-sm font-semibold text-foreground">{{ gallery.label }}</h3>
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-xl border border-border px-3 py-1.5 text-xs font-medium text-foreground hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                        Add Photos
                                        <input type="file" multiple accept=".jpg,.jpeg,.png,.webp" class="sr-only" @change="addImages($event, gallery.key)" />
                                    </label>
                                </div>
                                <div class="p-6">
                                    <div v-if="gallery.previews.length" class="grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-8">
                                        <div v-for="(img, i) in gallery.previews" :key="i"
                                            class="group relative aspect-square rounded-xl overflow-hidden border border-border shadow-sm">
                                            <img :src="img.url" :alt="img.name" class="h-full w-full object-cover" />
                                            <button type="button" @click="removeImageItem(gallery.key, i)"
                                                class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity shadow-sm">
                                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-10 text-center">
                                        <svg class="mb-2 text-muted-foreground/30" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <p class="text-sm text-muted-foreground">No {{ gallery.label.toLowerCase() }} yet</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Video & Tour URLs -->
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h3 class="text-sm font-semibold text-foreground">Video & Virtual Tour</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Video URL (YouTube / Vimeo)</Label>
                                    <Input v-model="form.video_url" type="url" placeholder="https://youtube.com/watch?v=…" />
                                </div>
                                <div class="space-y-2">
                                    <Label>360° Virtual Tour URL</Label>
                                    <Input v-model="form.tour_360_url" type="url" placeholder="Walkthrough link" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── AVAILABILITY ───────────────────────────────── -->
                    <div v-show="activeSection === 'availability'" class="space-y-6">
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Availability</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">Status and key dates</p>
                            </div>
                            <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Status <span class="text-red-400">*</span></Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger><SelectValue placeholder="Select status" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>Launch Date</Label>
                                    <Input v-model="form.launch_date" type="date" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Availability Date</Label>
                                    <Input v-model="form.available_date" type="date" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Expected Handover</Label>
                                    <Input v-model="form.handover_date" type="date" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── MORTGAGE ───────────────────────────────────── -->
                    <div v-show="activeSection === 'mortgage'" class="space-y-6">
                        <div class="rounded-2xl border border-border bg-white dark:bg-slate-900 shadow-sm">
                            <div class="border-b border-border px-6 py-4">
                                <h2 class="text-base font-semibold text-foreground">Mortgage & Financing</h2>
                                <p class="mt-0.5 text-xs text-muted-foreground">Eligible banks and loan parameters</p>
                            </div>
                            <div class="p-6 space-y-5">

                                <!-- Bank chips -->
                                <div class="space-y-2">
                                    <Label>Eligible Banks</Label>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="bank in enums.banks"
                                            :key="bank"
                                            type="button"
                                            @click="toggleBank(bank)"
                                            :class="[
                                                'rounded-xl border px-3 py-1.5 text-sm font-medium transition-all',
                                                form.eligible_banks.includes(bank)
                                                    ? 'border-admin-accent bg-admin-accent/10 text-admin-accent shadow-sm'
                                                    : 'border-border text-muted-foreground hover:border-admin-accent/40 hover:text-foreground'
                                            ]"
                                        >{{ bank }}</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label>Max Loan Amount (BDT)</Label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">৳</span>
                                            <Input v-model="form.max_loan_amount" type="number" min="0" placeholder="8,750,000" class="pl-6" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Payment Plan (months)</Label>
                                        <Input v-model="form.payment_plan_months" type="number" min="1" placeholder="e.g. 24" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom action bar -->
                    <div class="mt-6 flex items-center justify-between rounded-2xl border border-border bg-white dark:bg-slate-900 px-6 py-4 shadow-sm">
                        <Link :href="backHref" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                            Back
                        </Link>
                        <div class="flex items-center gap-3">
                            <!-- Section nav prev/next -->
                            <div class="flex items-center gap-1">
                                <button type="button"
                                    :disabled="SECTIONS.findIndex(s => s.id === activeSection) === 0"
                                    @click="activeSection = SECTIONS[SECTIONS.findIndex(s => s.id === activeSection) - 1].id"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border text-muted-foreground hover:text-foreground hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                                </button>
                                <span class="px-1 text-xs text-muted-foreground">
                                    {{ SECTIONS.findIndex(s => s.id === activeSection) + 1 }} / {{ SECTIONS.length }}
                                </span>
                                <button type="button"
                                    :disabled="SECTIONS.findIndex(s => s.id === activeSection) === SECTIONS.length - 1"
                                    @click="activeSection = SECTIONS[SECTIONS.findIndex(s => s.id === activeSection) + 1].id"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border text-muted-foreground hover:text-foreground hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-admin-accent px-5 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors disabled:opacity-60 shadow-sm">
                                <svg v-if="form.processing" class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                Save Unit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
