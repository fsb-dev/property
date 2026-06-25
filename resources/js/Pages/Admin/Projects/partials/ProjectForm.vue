<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import DatePicker from '@/Components/ui/date-picker/DatePicker.vue';
import ImageUpload from '@/Components/ui/media/ImageUpload.vue';
import ImageGallery from '@/Components/ui/media/ImageGallery.vue';
import DocumentList from '@/Components/ui/media/DocumentList.vue';

const props = defineProps({
    form:             { type: Object, required: true },
    enums:            { type: Object, required: true },
    mode:             { type: String, default: 'create' },
    currentCover:     { type: String, default: null },
    currentImages:    { type: Array,  default: () => [] },
    currentDocuments: { type: Array,  default: () => [] },
});

const emit = defineEmits(['submit']);

const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';

// ── Tabs ──────────────────────────────────────────────────────────────────────
const TABS = [
    { key: 'basic',      label: 'Basic Info' },
    { key: 'location',   label: 'Location' },
    { key: 'specs',      label: 'Specifications' },
    { key: 'facilities', label: 'Facilities' },
    { key: 'media',      label: 'Media' },
    { key: 'docs',       label: 'Documents' },
];

const activeTab = ref('basic');

// ── Type → Category cascade ───────────────────────────────────────────────────
const selectedTypeData = computed(() =>
    props.enums.types?.find(t => t.value === props.form.type) ?? null
);

const availableCategories = computed(() =>
    selectedTypeData.value?.categories ?? []
);

const selectedCategoryData = computed(() =>
    availableCategories.value.find(c => c.value === props.form.category) ?? null
);

const specFields = computed(() =>
    selectedCategoryData.value?.specFields ?? []
);

const hasUnits = computed(() =>
    selectedCategoryData.value?.hasUnits ?? true
);

watch(() => props.form.type, () => {
    props.form.category       = null;
    props.form.specifications = {};
    props.form.facilities     = [];
});

watch(() => props.form.category, () => {
    props.form.specifications = {};
    // Keep only facilities that still exist in the new category's list
    const allAvailable = (selectedCategoryData.value?.facilities ?? []).flatMap(g => g.items);
    props.form.facilities = (props.form.facilities ?? []).filter(f => allAvailable.includes(f));
});

// ── Facilities helpers ────────────────────────────────────────────────────────
const facilityGroups = computed(() =>
    selectedCategoryData.value?.facilities ?? []
);

function hasFacility(name) {
    return (props.form.facilities ?? []).includes(name);
}

function toggleFacility(name) {
    if (!props.form.facilities) props.form.facilities = [];
    const idx = props.form.facilities.indexOf(name);
    if (idx === -1) props.form.facilities.push(name);
    else props.form.facilities.splice(idx, 1);
}

function selectAllInGroup(items) {
    if (!props.form.facilities) props.form.facilities = [];
    items.forEach(item => {
        if (!props.form.facilities.includes(item)) props.form.facilities.push(item);
    });
}

function clearGroup(items) {
    props.form.facilities = (props.form.facilities ?? []).filter(f => !items.includes(f));
}

function groupAllSelected(items) {
    return items.every(i => (props.form.facilities ?? []).includes(i));
}

// ── Boolean spec helper (handles "1"/"0" from FormData round-trips) ───────────
function boolSpec(key) {
    const v = props.form.specifications?.[key];
    return v === true || v === 1 || v === '1';
}

function toggleSpec(key) {
    if (!props.form.specifications) props.form.specifications = {};
    props.form.specifications[key] = !boolSpec(key);
}

// ── Tab error indicators ──────────────────────────────────────────────────────
const TAB_FIELDS = {
    basic:      ['name', 'type', 'status', 'handover_date', 'description'],
    location:   ['location', 'address', 'latitude', 'longitude'],
    specs:      ['category', 'total_floors', 'total_units', 'specifications'],
    facilities: ['facilities', 'facilities.*'],
    media:      ['cover', 'new_images'],
    docs:       ['new_documents'],
};

function tabHasError(key) {
    return (TAB_FIELDS[key] ?? []).some(field => !!props.form.errors?.[field]);
}

// Auto-switch to first tab that has errors after a submit attempt
watch(() => props.form.errors, (errors) => {
    if (!errors || Object.keys(errors).length === 0) return;
    for (const tab of TABS) {
        if (tabHasError(tab.key)) {
            activeTab.value = tab.key;
            break;
        }
    }
});
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-0">

        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">

            <!-- ── Tab navigation ──────────────────────────────────── -->
            <div class="flex items-center gap-0.5 border-b border-border px-4 pt-1 overflow-x-auto">
                <button
                    v-for="tab in TABS"
                    :key="tab.key"
                    type="button"
                    @click="activeTab = tab.key"
                    :class="[
                        'relative flex shrink-0 items-center gap-1.5 px-3 py-2.5 text-sm font-medium transition-colors whitespace-nowrap',
                        activeTab === tab.key
                            ? 'text-admin-accent after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:rounded-t-full after:bg-admin-accent'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    {{ tab.label }}
                    <span
                        v-if="tabHasError(tab.key)"
                        class="h-1.5 w-1.5 rounded-full bg-destructive"
                    />
                </button>
            </div>

            <!-- ── Basic Info ─────────────────────────────────────── -->
            <div v-show="activeTab === 'basic'" class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <div class="md:col-span-2 space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Project Name <span class="text-destructive">*</span>
                    </Label>
                    <Input v-model="form.name" placeholder="e.g. LakeView Residences"
                        :class="[f, form.errors.name && 'border-destructive']" />
                    <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Type <span class="text-destructive">*</span>
                    </Label>
                    <Select :model-value="form.type" @update:model-value="form.type = $event">
                        <SelectTrigger :class="[f, form.errors.type && 'border-destructive']">
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">
                                {{ t.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.type" class="text-xs text-destructive">{{ form.errors.type }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Status <span class="text-destructive">*</span>
                    </Label>
                    <Select :model-value="form.status" @update:model-value="form.status = $event">
                        <SelectTrigger :class="[f, form.errors.status && 'border-destructive']">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">
                                {{ s.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.status" class="text-xs text-destructive">{{ form.errors.status }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Handover Date</Label>
                    <DatePicker
                        :model-value="form.handover_date"
                        @update:model-value="form.handover_date = $event"
                        placeholder="Pick a date"
                        :class="[f, form.errors.handover_date && 'border-destructive']"
                    />
                    <p v-if="form.errors.handover_date" class="text-xs text-destructive">{{ form.errors.handover_date }}</p>
                </div>

                <div class="md:col-span-2 space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Description</Label>
                    <Textarea v-model="form.description" placeholder="Overview of the project..." rows="4"
                        :class="[f, 'resize-none', form.errors.description && 'border-destructive']" />
                    <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                </div>

            </div>

            <!-- ── Location ───────────────────────────────────────── -->
            <div v-show="activeTab === 'location'" class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Area / Neighbourhood</Label>
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

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Latitude</Label>
                    <Input type="number" step="any" v-model="form.latitude" placeholder="23.8103"
                        :class="[f, form.errors.latitude && 'border-destructive']" />
                    <p v-if="form.errors.latitude" class="text-xs text-destructive">{{ form.errors.latitude }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Longitude</Label>
                    <Input type="number" step="any" v-model="form.longitude" placeholder="90.4125"
                        :class="[f, form.errors.longitude && 'border-destructive']" />
                    <p v-if="form.errors.longitude" class="text-xs text-destructive">{{ form.errors.longitude }}</p>
                </div>

            </div>

            <!-- ── Specifications ──────────────────────────────────── -->
            <div v-show="activeTab === 'specs'" class="space-y-5 p-5">

                <!-- Category (cascades from Type in Basic Info) -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Category
                        <span v-if="!form.type" class="ml-1 font-normal text-muted-foreground">
                            — select a Type in Basic Info first
                        </span>
                    </Label>
                    <Select
                        :model-value="form.category || undefined"
                        @update:model-value="form.category = $event ?? null"
                        :disabled="!form.type || availableCategories.length === 0"
                    >
                        <SelectTrigger :class="[f, form.errors.category && 'border-destructive']">
                            <SelectValue :placeholder="form.type ? 'Select category' : '— Select Type first —'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="c in availableCategories" :key="c.value" :value="c.value">
                                {{ c.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.category" class="text-xs text-destructive">{{ form.errors.category }}</p>
                </div>

                <!-- Total Floors + Total Units — shown when category hasUnits (or no category selected yet) -->
                <div v-if="!selectedCategoryData || hasUnits" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Floors</Label>
                        <Input type="number" min="1" v-model="form.total_floors" placeholder="e.g. 12"
                            :class="[f, form.errors.total_floors && 'border-destructive']" />
                        <p v-if="form.errors.total_floors" class="text-xs text-destructive">{{ form.errors.total_floors }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Units</Label>
                        <Input type="number" min="1" v-model="form.total_units" placeholder="e.g. 48"
                            :class="[f, form.errors.total_units && 'border-destructive']" />
                        <p v-if="form.errors.total_units" class="text-xs text-destructive">{{ form.errors.total_units }}</p>
                    </div>
                </div>

                <!-- Dynamic spec fields from selected category -->
                <div v-if="specFields.length > 0" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div
                        v-for="field in specFields"
                        :key="field.key"
                        :class="['space-y-1.5', field.span === 2 && 'md:col-span-2']"
                    >
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ field.label }}</Label>

                        <!-- Number -->
                        <div v-if="field.type === 'number'" class="relative">
                            <Input
                                type="number"
                                min="0"
                                step="any"
                                v-model="form.specifications[field.key]"
                                placeholder="0"
                                :class="[f, field.suffix && 'pr-14']"
                            />
                            <span v-if="field.suffix"
                                class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">
                                {{ field.suffix }}
                            </span>
                        </div>

                        <!-- Text -->
                        <Input
                            v-else-if="field.type === 'text'"
                            v-model="form.specifications[field.key]"
                            placeholder=""
                            :class="f"
                        />

                        <!-- Select -->
                        <Select
                            v-else-if="field.type === 'select'"
                            :model-value="form.specifications[field.key] || undefined"
                            @update:model-value="form.specifications[field.key] = $event ?? null"
                        >
                            <SelectTrigger :class="f">
                                <SelectValue :placeholder="`Select ${field.label}`" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Boolean toggle -->
                        <div v-else-if="field.type === 'boolean'" class="flex items-center gap-3 py-1">
                            <button
                                type="button"
                                @click="toggleSpec(field.key)"
                                :class="[
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-admin-accent',
                                    boolSpec(field.key) ? 'bg-admin-accent' : 'bg-slate-200 dark:bg-white/20',
                                ]"
                            >
                                <span :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    boolSpec(field.key) ? 'translate-x-5' : 'translate-x-0',
                                ]" />
                            </button>
                            <span class="text-sm text-muted-foreground">{{ boolSpec(field.key) ? 'Yes' : 'No' }}</span>
                        </div>

                    </div>
                </div>

                <!-- Empty state when no category is selected -->
                <div v-if="!form.category"
                    class="flex h-24 items-center justify-center rounded-xl border-2 border-dashed border-border text-sm text-muted-foreground">
                    Select a category above to see specification fields
                </div>

            </div>

            <!-- ── Facilities ─────────────────────────────────────── -->
            <div v-show="activeTab === 'facilities'" class="p-5">

                <!-- Empty state -->
                <div v-if="!form.category"
                    class="flex h-24 items-center justify-center rounded-xl border-2 border-dashed border-border text-sm text-muted-foreground">
                    Select a category in the Specifications tab to see available facilities
                </div>

                <!-- Facility groups -->
                <div v-else class="space-y-6">
                    <div v-for="group in facilityGroups" :key="group.group">

                        <!-- Group header with select-all toggle -->
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ group.group }}
                            </p>
                            <button
                                type="button"
                                @click="groupAllSelected(group.items) ? clearGroup(group.items) : selectAllInGroup(group.items)"
                                class="text-xs text-admin-accent hover:underline"
                            >
                                {{ groupAllSelected(group.items) ? 'Clear all' : 'Select all' }}
                            </button>
                        </div>

                        <!-- Facility chips -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="item in group.items"
                                :key="item"
                                type="button"
                                @click="toggleFacility(item)"
                                :class="[
                                    'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                                    hasFacility(item)
                                        ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                        : 'border-border bg-transparent text-muted-foreground hover:border-admin-accent/50 hover:text-foreground',
                                ]"
                            >
                                <!-- Check icon when selected -->
                                <svg v-if="hasFacility(item)" width="10" height="10" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                {{ item }}
                            </button>
                        </div>

                    </div>

                    <!-- Selected count summary -->
                    <p class="text-xs text-muted-foreground">
                        {{ (form.facilities ?? []).length }} facilit{{ (form.facilities ?? []).length === 1 ? 'y' : 'ies' }} selected
                    </p>
                </div>

            </div>

            <!-- ── Media ───────────────────────────────────────────── -->
            <div v-show="activeTab === 'media'" class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <div class="space-y-2">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Cover Image</Label>
                    <ImageUpload
                        :file="form.cover"
                        @update:file="form.cover = $event"
                        :removed="form.remove_cover ?? false"
                        @update:removed="form.remove_cover = $event"
                        :preview="currentCover"
                        hint="JPG, PNG, WEBP · Max 5 MB"
                    />
                    <p v-if="form.errors?.cover" class="text-xs text-destructive">{{ form.errors.cover }}</p>
                </div>

                <div class="space-y-2">
                    <ImageGallery
                        :existing="currentImages"
                        :new-files="form.new_images ?? []"
                        @update:new-files="form.new_images = $event"
                        :remove-ids="form.remove_images ?? []"
                        @update:remove-ids="form.remove_images = $event"
                    >
                        <template #label>
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Gallery</Label>
                        </template>
                    </ImageGallery>
                    <p v-if="form.errors?.new_images" class="text-xs text-destructive">{{ form.errors.new_images }}</p>
                </div>

            </div>

            <!-- ── Documents ──────────────────────────────────────── -->
            <div v-show="activeTab === 'docs'" class="p-5">
                <DocumentList
                    :existing="currentDocuments"
                    :new-files="form.new_documents ?? []"
                    @update:new-files="form.new_documents = $event"
                    :remove-ids="form.remove_documents ?? []"
                    @update:remove-ids="form.remove_documents = $event"
                />
                <p v-if="form.errors?.new_documents" class="mt-2 text-xs text-destructive">{{ form.errors.new_documents }}</p>
            </div>

            <!-- ── Footer (always visible) ──────────────────────────── -->
            <div class="flex items-center justify-between border-t border-border px-5 py-4">
                <p class="text-xs text-muted-foreground">
                    <span class="text-destructive">*</span> Required fields
                </p>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.projects.index')"
                        class="inline-flex h-9 items-center rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-4 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                        </svg>
                        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        {{ mode === 'edit' ? 'Save Changes' : 'Create Project' }}
                    </button>
                </div>
            </div>

        </div>

    </form>
</template>
