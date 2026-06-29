<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    unit:  { type: Object, required: true },
    enums: { type: Object, required: true },
});

// ── Tabs ──────────────────────────────────────────────────────────────────
const TABS = [
    { id: 'identity',      label: 'Identity',      icon: 'M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1m-4 0h4' },
    { id: 'specs',         label: 'Specifications', icon: 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2' },
    { id: 'measurements',  label: 'Measurements',  icon: 'M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0 0 21 18.382V7.618a1 1 0 0 0-.553-.894L15 4m0 13V4m0 0L9 7' },
    { id: 'pricing',       label: 'Pricing',        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z' },
    { id: 'floorplan',     label: 'Floor Plan',     icon: 'M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z' },
    { id: 'media',         label: 'Media',          icon: 'M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2 1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z' },
    { id: 'availability',  label: 'Availability',   icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z' },
    { id: 'mortgage',      label: 'Mortgage',       icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z' },
];

const activeTab = ref('identity');

// ── Form ──────────────────────────────────────────────────────────────────
const form = useForm({
    _from: props.unit.block_id ? 'blueprint' : null,

    // Identity
    unit_number:  props.unit.unit_number,
    unit_code:    props.unit.unit_code    ?? '',
    type:         props.unit.type         ?? '',
    wing:         props.unit.wing         ?? '',
    description:  props.unit.description  ?? '',
    project_id:   props.unit.project_id,
    block_id:     props.unit.block_id,

    // Specifications
    bedrooms:          props.unit.bedrooms       ?? '',
    bathrooms:         props.unit.bathrooms      ?? '',
    balconies:         props.unit.balconies      ?? '',
    servant_room:      props.unit.servant_room   ?? false,
    store_room:        props.unit.store_room     ?? false,
    parking_spaces:    props.unit.parking_spaces ?? '',
    facing_direction:  props.unit.facing_direction ?? '',
    view:              props.unit.view            ?? '',

    // Measurements
    size_sqft:           props.unit.size_sqft           ?? '',
    super_built_up_area: props.unit.super_built_up_area ?? '',
    carpet_area:         props.unit.carpet_area         ?? '',
    ceiling_height:      props.unit.ceiling_height      ?? '',
    terrace_area:        props.unit.terrace_area        ?? '',
    parking_area:        props.unit.parking_area        ?? '',

    // Pricing
    price:               props.unit.price               ?? '',
    launch_price:        props.unit.launch_price        ?? '',
    current_price:       props.unit.current_price       ?? '',
    parking_price:       props.unit.parking_price       ?? '',
    registration_fee:    props.unit.registration_fee    ?? '',
    vat_pct:             props.unit.vat_pct             ?? '',
    monthly_maintenance: props.unit.monthly_maintenance ?? '',
    booking_amount:      props.unit.booking_amount      ?? '',

    // Media URLs
    video_url:    props.unit.video_url    ?? '',
    tour_360_url: props.unit.tour_360_url ?? '',

    // Availability
    status:         props.unit.status         ?? 'not_configured',
    launch_date:    props.unit.launch_date    ?? '',
    available_date: props.unit.available_date ?? '',
    handover_date:  props.unit.handover_date  ?? '',

    // Mortgage
    eligible_banks:       props.unit.eligible_banks       ?? [],
    max_loan_amount:      props.unit.max_loan_amount      ?? '',
    payment_plan_months:  props.unit.payment_plan_months  ?? '',

    // Floor plan files
    floor_plan:            null,
    remove_floor_plan:     false,
    floor_plan_pdf:        null,
    remove_floor_plan_pdf: false,
    cad_dwg:               null,
    remove_cad_dwg:        false,

    // Media galleries
    new_images:    [],
    remove_images: [],
    new_drone:     [],
    remove_drone:  [],
    new_interior:  [],
    remove_interior: [],
});

function submit() {
    form.post(route('admin.units.update', props.unit.id), {
        method: 'put',
        forceFormData: true,
    });
}

// ── Back URL ──────────────────────────────────────────────────────────────
const backHref = computed(() =>
    props.unit.block_id
        ? route('admin.blueprint.show', props.unit.project_id)
        : route('admin.units.index')
);

// ── Bank toggle ───────────────────────────────────────────────────────────
function toggleBank(bank) {
    const idx = form.eligible_banks.indexOf(bank);
    if (idx === -1) form.eligible_banks.push(bank);
    else form.eligible_banks.splice(idx, 1);
}

// ── File helpers ──────────────────────────────────────────────────────────
const floorPlanPreview   = ref(props.unit.floor_plan    || null);
const floorPlanPdfName   = ref(props.unit.floor_plan_pdf ? 'Existing PDF' : null);
const cadName            = ref(props.unit.cad_dwg        ? 'Existing file' : null);
const imagesPreviews     = ref([...(props.unit.images   || [])]);
const dronePreviews      = ref([...(props.unit.drone    || [])]);
const interiorPreviews   = ref([...(props.unit.interior || [])]);

function onFloorPlanChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.floor_plan = file;
    floorPlanPreview.value = URL.createObjectURL(file);
}

function onFloorPlanPdfChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.floor_plan_pdf = file;
    floorPlanPdfName.value = file.name;
}

function onCadChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.cad_dwg = file;
    cadName.value = file.name;
}

function addImages(e, collection) {
    const files = Array.from(e.target.files);
    if (collection === 'images') {
        form.new_images.push(...files);
        files.forEach(f => imagesPreviews.value.push({ url: URL.createObjectURL(f), name: f.name, _new: true }));
    } else if (collection === 'drone') {
        form.new_drone.push(...files);
        files.forEach(f => dronePreviews.value.push({ url: URL.createObjectURL(f), name: f.name, _new: true }));
    } else if (collection === 'interior') {
        form.new_interior.push(...files);
        files.forEach(f => interiorPreviews.value.push({ url: URL.createObjectURL(f), name: f.name, _new: true }));
    }
    e.target.value = '';
}

function removeImageItem(collection, idx) {
    if (collection === 'images') {
        const item = imagesPreviews.value[idx];
        if (!item._new && item.id) form.remove_images.push(item.id);
        imagesPreviews.value.splice(idx, 1);
    } else if (collection === 'drone') {
        const item = dronePreviews.value[idx];
        if (!item._new && item.id) form.remove_drone.push(item.id);
        dronePreviews.value.splice(idx, 1);
    } else if (collection === 'interior') {
        const item = interiorPreviews.value[idx];
        if (!item._new && item.id) form.remove_interior.push(item.id);
        interiorPreviews.value.splice(idx, 1);
    }
}
</script>

<template>
    <Head :title="`Configure — ${unit.unit_number}`" />

    <AdminLayout>
        <!-- Page header -->
        <div class="mb-5 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                    <Link :href="backHref" class="hover:text-admin-accent transition-colors">
                        {{ unit.block_id ? 'Blueprint' : 'Units' }}
                    </Link>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="text-foreground font-medium">Configure {{ unit.unit_number }}</span>
                </nav>
                <h1 class="text-xl font-bold text-foreground">Unit Configuration</h1>

                <!-- Context strip -->
                <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                    <span v-if="unit.project_name" class="flex items-center gap-1">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/></svg>
                        {{ unit.project_name }}
                    </span>
                    <span v-if="unit.building_name" class="flex items-center gap-1">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        {{ unit.building_name }}
                    </span>
                    <span v-if="unit.section_name">Block: {{ unit.section_name }}</span>
                    <span v-if="unit.floor">Floor {{ unit.floor }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link :href="backHref"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-border bg-white dark:bg-slate-800 px-4 text-sm font-medium text-foreground hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </Link>
                <button @click="submit" :disabled="form.processing"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-admin-accent px-4 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors disabled:opacity-60">
                    <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    Save Unit
                </button>
            </div>
        </div>

        <!-- Tab bar -->
        <div class="mb-6 flex items-center gap-0 overflow-x-auto rounded-xl border border-border bg-admin-surface-card p-1">
            <button
                v-for="tab in TABS"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                    'flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-medium whitespace-nowrap transition-all',
                    activeTab === tab.id
                        ? 'bg-admin-accent text-white shadow-sm'
                        : 'text-muted-foreground hover:text-foreground hover:bg-slate-100 dark:hover:bg-slate-700'
                ]"
            >
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="tab.icon" />
                {{ tab.label }}
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-6">

            <!-- ── TAB: IDENTITY ───────────────────────────────────────── -->
            <div v-show="activeTab === 'identity'" class="rounded-xl border border-border bg-admin-surface-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Unit Identity</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Unit Number <span class="text-red-400">*</span></label>
                        <input v-model="form.unit_number" type="text" placeholder="e.g. A-1205"
                            class="h-9 w-full rounded-lg border px-3 text-sm"
                            :class="form.errors.unit_number ? 'border-red-400' : 'border-border'" />
                        <p v-if="form.errors.unit_number" class="mt-1 text-xs text-red-500">{{ form.errors.unit_number }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Unit Code</label>
                        <input v-model="form.unit_code" type="text" placeholder="e.g. LVR-A-1205"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Unit Type</label>
                        <select v-model="form.type" class="h-9 w-full rounded-lg border border-border px-3 text-sm bg-white dark:bg-slate-800">
                            <option value="">— Select type —</option>
                            <option v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Wing</label>
                        <input v-model="form.wing" type="text" placeholder="e.g. North, A, East"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Description</label>
                        <textarea v-model="form.description" rows="3" placeholder="Corner unit, lake-facing, premium finishing…"
                            class="w-full rounded-lg border border-border px-3 py-2 text-sm resize-none" />
                    </div>
                </div>
            </div>

            <!-- ── TAB: SPECIFICATIONS ─────────────────────────────────── -->
            <div v-show="activeTab === 'specs'" class="rounded-xl border border-border bg-admin-surface-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Specifications</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Bedrooms</label>
                        <input v-model="form.bedrooms" type="number" min="0" max="20" placeholder="0"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Bathrooms</label>
                        <input v-model="form.bathrooms" type="number" min="0" max="20" placeholder="0"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Balconies</label>
                        <input v-model="form.balconies" type="number" min="0" max="10" placeholder="0"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Parking Spaces</label>
                        <input v-model="form.parking_spaces" type="number" min="0" max="20" placeholder="0"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Facing Direction</label>
                        <select v-model="form.facing_direction" class="h-9 w-full rounded-lg border border-border px-3 text-sm bg-white dark:bg-slate-800">
                            <option value="">— Select —</option>
                            <option v-for="f in enums.facing" :key="f" :value="f">{{ f }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">View</label>
                        <select v-model="form.view" class="h-9 w-full rounded-lg border border-border px-3 text-sm bg-white dark:bg-slate-800">
                            <option value="">— Select —</option>
                            <option v-for="v in enums.views" :key="v" :value="v">{{ v }}</option>
                        </select>
                    </div>

                    <!-- Boolean toggles -->
                    <div class="flex items-center justify-between rounded-lg border border-border bg-slate-50 dark:bg-slate-800/50 px-3 py-2.5 col-span-1">
                        <span class="text-xs font-medium text-foreground">Servant Room</span>
                        <button type="button" @click="form.servant_room = !form.servant_room"
                            :class="['relative inline-flex h-5 w-9 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                form.servant_room ? 'bg-admin-accent' : 'bg-slate-200 dark:bg-slate-700']">
                            <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                form.servant_room ? 'translate-x-4' : 'translate-x-0']" />
                        </button>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-border bg-slate-50 dark:bg-slate-800/50 px-3 py-2.5 col-span-1">
                        <span class="text-xs font-medium text-foreground">Store Room</span>
                        <button type="button" @click="form.store_room = !form.store_room"
                            :class="['relative inline-flex h-5 w-9 flex-none cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                form.store_room ? 'bg-admin-accent' : 'bg-slate-200 dark:bg-slate-700']">
                            <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                form.store_room ? 'translate-x-4' : 'translate-x-0']" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── TAB: MEASUREMENTS ───────────────────────────────────── -->
            <div v-show="activeTab === 'measurements'" class="rounded-xl border border-border bg-admin-surface-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Measurements <span class="text-xs font-normal text-muted-foreground">(all in sqft, ceiling in ft)</span></h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Built-up Area (sqft)</label>
                        <input v-model="form.size_sqft" type="number" min="1" placeholder="e.g. 1250"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Super Built-up Area (sqft)</label>
                        <input v-model="form.super_built_up_area" type="number" min="1" placeholder="e.g. 1580"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Net / Carpet Area (sqft)</label>
                        <input v-model="form.carpet_area" type="number" min="1" placeholder="e.g. 1080"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Ceiling Height (ft)</label>
                        <input v-model="form.ceiling_height" type="number" step="0.1" min="0" placeholder="e.g. 10.5"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Terrace Area (sqft)</label>
                        <input v-model="form.terrace_area" type="number" min="0" placeholder="e.g. 120"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Parking Area (sqft)</label>
                        <input v-model="form.parking_area" type="number" min="0" placeholder="e.g. 240"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                </div>
            </div>

            <!-- ── TAB: PRICING ────────────────────────────────────────── -->
            <div v-show="activeTab === 'pricing'" class="rounded-xl border border-border bg-admin-surface-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Pricing <span class="text-xs font-normal text-muted-foreground">(BDT)</span></h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Base Price</label>
                        <input v-model="form.price" type="number" min="0" placeholder="e.g. 12500000"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Launch Price</label>
                        <input v-model="form.launch_price" type="number" min="0" placeholder="e.g. 11875000"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Current Price</label>
                        <input v-model="form.current_price" type="number" min="0" placeholder="e.g. 12500000"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Parking Price</label>
                        <input v-model="form.parking_price" type="number" min="0" placeholder="e.g. 800000"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Registration Fee</label>
                        <input v-model="form.registration_fee" type="number" min="0" placeholder="e.g. 450000"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">VAT (%)</label>
                        <input v-model="form.vat_pct" type="number" step="0.01" min="0" max="100" placeholder="e.g. 7.5"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Monthly Maintenance</label>
                        <input v-model="form.monthly_maintenance" type="number" min="0" placeholder="e.g. 4500"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Booking Amount</label>
                        <input v-model="form.booking_amount" type="number" min="0" placeholder="e.g. 500000"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                </div>
            </div>

            <!-- ── TAB: FLOOR PLAN ─────────────────────────────────────── -->
            <div v-show="activeTab === 'floorplan'" class="rounded-xl border border-border bg-admin-surface-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Floor Plan</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">

                    <!-- Floor plan image -->
                    <div>
                        <label class="mb-2 block text-xs font-medium text-muted-foreground">Floor Plan (PNG / JPG)</label>
                        <div class="relative rounded-xl border-2 border-dashed border-border p-4 text-center">
                            <img v-if="floorPlanPreview" :src="floorPlanPreview" alt="Floor plan" class="mx-auto mb-3 max-h-40 rounded-lg object-contain" />
                            <svg v-else class="mx-auto mb-2 text-muted-foreground/40" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                            </svg>
                            <p class="text-xs text-muted-foreground mb-2">{{ floorPlanPreview ? 'Click to replace' : 'Upload floor plan image' }}</p>
                            <input type="file" accept=".jpg,.jpeg,.png,.webp" class="absolute inset-0 cursor-pointer opacity-0" @change="onFloorPlanChange" />
                        </div>
                        <button v-if="floorPlanPreview" type="button" @click="form.remove_floor_plan = true; floorPlanPreview = null; form.floor_plan = null"
                            class="mt-1.5 text-xs text-red-500 hover:underline">Remove</button>
                    </div>

                    <!-- PDF -->
                    <div>
                        <label class="mb-2 block text-xs font-medium text-muted-foreground">Floor Plan PDF</label>
                        <div class="relative rounded-xl border-2 border-dashed border-border p-6 text-center">
                            <svg class="mx-auto mb-2 text-red-400" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <p class="text-xs text-muted-foreground">{{ floorPlanPdfName ?? 'Upload PDF' }}</p>
                            <input type="file" accept=".pdf" class="absolute inset-0 cursor-pointer opacity-0" @change="onFloorPlanPdfChange" />
                        </div>
                        <button v-if="floorPlanPdfName" type="button" @click="form.remove_floor_plan_pdf = true; floorPlanPdfName = null; form.floor_plan_pdf = null"
                            class="mt-1.5 text-xs text-red-500 hover:underline">Remove</button>
                    </div>

                    <!-- CAD/DWG -->
                    <div>
                        <label class="mb-2 block text-xs font-medium text-muted-foreground">CAD / DWG</label>
                        <div class="relative rounded-xl border-2 border-dashed border-border p-6 text-center">
                            <svg class="mx-auto mb-2 text-blue-400" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                            <p class="text-xs text-muted-foreground">{{ cadName ?? 'Upload .dwg / .dxf' }}</p>
                            <input type="file" class="absolute inset-0 cursor-pointer opacity-0" @change="onCadChange" />
                        </div>
                        <button v-if="cadName" type="button" @click="form.remove_cad_dwg = true; cadName = null; form.cad_dwg = null"
                            class="mt-1.5 text-xs text-red-500 hover:underline">Remove</button>
                    </div>
                </div>
            </div>

            <!-- ── TAB: MEDIA ──────────────────────────────────────────── -->
            <div v-show="activeTab === 'media'" class="space-y-6">

                <!-- Unit Images -->
                <div class="rounded-xl border border-border bg-admin-surface-card p-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-foreground">Unit Images</h3>
                        <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-foreground hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                            Add Photos
                            <input type="file" multiple accept=".jpg,.jpeg,.png,.webp" class="sr-only" @change="addImages($event, 'images')" />
                        </label>
                    </div>
                    <div v-if="imagesPreviews.length" class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                        <div v-for="(img, i) in imagesPreviews" :key="i" class="relative aspect-square rounded-lg overflow-hidden border border-border group">
                            <img :src="img.url" :alt="img.name" class="h-full w-full object-cover" />
                            <button type="button" @click="removeImageItem('images', i)"
                                class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs text-muted-foreground py-4 text-center">No images yet. Click "Add Photos" to upload.</p>
                </div>

                <!-- Drone Shots -->
                <div class="rounded-xl border border-border bg-admin-surface-card p-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-foreground">Drone Shots</h3>
                        <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-foreground hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                            Add
                            <input type="file" multiple accept=".jpg,.jpeg,.png,.webp" class="sr-only" @change="addImages($event, 'drone')" />
                        </label>
                    </div>
                    <div v-if="dronePreviews.length" class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                        <div v-for="(img, i) in dronePreviews" :key="i" class="relative aspect-square rounded-lg overflow-hidden border border-border group">
                            <img :src="img.url" :alt="img.name" class="h-full w-full object-cover" />
                            <button type="button" @click="removeImageItem('drone', i)"
                                class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs text-muted-foreground py-4 text-center">No drone shots yet.</p>
                </div>

                <!-- Interior Photos -->
                <div class="rounded-xl border border-border bg-admin-surface-card p-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-foreground">Interior Photos</h3>
                        <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-foreground hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                            Add
                            <input type="file" multiple accept=".jpg,.jpeg,.png,.webp" class="sr-only" @change="addImages($event, 'interior')" />
                        </label>
                    </div>
                    <div v-if="interiorPreviews.length" class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                        <div v-for="(img, i) in interiorPreviews" :key="i" class="relative aspect-square rounded-lg overflow-hidden border border-border group">
                            <img :src="img.url" :alt="img.name" class="h-full w-full object-cover" />
                            <button type="button" @click="removeImageItem('interior', i)"
                                class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs text-muted-foreground py-4 text-center">No interior photos yet.</p>
                </div>

                <!-- Video & Tour URLs -->
                <div class="rounded-xl border border-border bg-admin-surface-card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-foreground">Video & Virtual Tour</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Video URL (YouTube / Vimeo)</label>
                            <input v-model="form.video_url" type="url" placeholder="https://youtube.com/watch?v=..."
                                class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-muted-foreground">360° Virtual Tour URL</label>
                            <input v-model="form.tour_360_url" type="url" placeholder="Walkthrough link"
                                class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB: AVAILABILITY ───────────────────────────────────── -->
            <div v-show="activeTab === 'availability'" class="rounded-xl border border-border bg-admin-surface-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Availability</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Status <span class="text-red-400">*</span></label>
                        <select v-model="form.status" class="h-9 w-full rounded-lg border border-border px-3 text-sm bg-white dark:bg-slate-800">
                            <option v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Launch Date</label>
                        <input v-model="form.launch_date" type="date"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Availability Date</label>
                        <input v-model="form.available_date" type="date"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Expected Handover</label>
                        <input v-model="form.handover_date" type="date"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                </div>
            </div>

            <!-- ── TAB: MORTGAGE ───────────────────────────────────────── -->
            <div v-show="activeTab === 'mortgage'" class="rounded-xl border border-border bg-admin-surface-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-foreground">Mortgage & Financing</h2>

                <!-- Eligible Banks -->
                <div class="mb-5">
                    <label class="mb-2 block text-xs font-medium text-muted-foreground">Eligible Banks</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="bank in enums.banks"
                            :key="bank"
                            type="button"
                            @click="toggleBank(bank)"
                            :class="[
                                'rounded-full border px-3 py-1 text-xs font-medium transition-colors',
                                form.eligible_banks.includes(bank)
                                    ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                    : 'border-border text-muted-foreground hover:border-admin-accent/50'
                            ]"
                        >{{ bank }}</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Max Loan Amount (BDT)</label>
                        <input v-model="form.max_loan_amount" type="number" min="0" placeholder="e.g. 8750000"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Payment Plan (months)</label>
                        <input v-model="form.payment_plan_months" type="number" min="1" placeholder="e.g. 24"
                            class="h-9 w-full rounded-lg border border-border px-3 text-sm" />
                    </div>
                </div>
            </div>

            <!-- Bottom save bar -->
            <div class="flex items-center justify-between border-t border-border pt-4 pb-2">
                <Link :href="backHref" class="text-sm text-muted-foreground hover:text-foreground transition-colors">← Back</Link>
                <button type="submit" :disabled="form.processing"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-admin-accent px-5 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors disabled:opacity-60">
                    <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    Save Unit
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
