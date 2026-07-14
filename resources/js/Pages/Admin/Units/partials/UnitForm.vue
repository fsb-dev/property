<script setup>
import { computed } from 'vue';
import { Link }     from '@inertiajs/vue3';
import { Input }    from '@/Components/ui/input';
import { Label }    from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import DatePicker  from '@/Components/ui/date-picker/DatePicker.vue';
import ImageUpload from '@/Components/ui/media/ImageUpload.vue';
import ImageGallery from '@/Components/ui/media/ImageGallery.vue';
import DocumentList from '@/Components/ui/media/DocumentList.vue';

const props = defineProps({
    form:             { type: Object, required: true },
    enums:            { type: Object, required: true },
    projects:         { type: Array,  required: true },
    mode:             { type: String, default: 'create' },
    currentFloorPlan: { type: String, default: null },
    currentImages:    { type: Array,  default: () => [] },
    currentDocuments: { type: Array,  default: () => [] },
});

const emit = defineEmits(['submit']);

const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';

// ── Type-aware form config ────────────────────────────────────────────────────
const DEFAULT_CONFIG = {
    showBlock: true, blockLabel: 'Block',
    showFloor: true, floorLabel: 'Floor',
    showBedrooms: true,
};

const typeConfig = computed(() => {
    const t = props.enums.types?.find(t => t.value === props.form.type);
    return t?.formConfig ?? DEFAULT_CONFIG;
});
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-4">

        <!-- ── Unit Identity ──────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <rect x="3" y="8" width="8" height="13" rx="1.5"/><rect x="13" y="3" width="8" height="18" rx="1.5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Unit Identity</p>
                    <p class="text-xs text-muted-foreground">Project, type and locator information</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <!-- Project -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Project <span class="text-destructive">*</span>
                    </Label>
                    <Select
                        :model-value="form.project_id ? String(form.project_id) : undefined"
                        @update:model-value="form.project_id = $event ? parseInt($event) : null"
                    >
                        <SelectTrigger :class="[f, form.errors.project_id && 'border-destructive']">
                            <SelectValue placeholder="Select project" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="p in projects" :key="p.id" :value="String(p.id)">
                                {{ p.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.project_id" class="text-xs text-destructive">{{ form.errors.project_id }}</p>
                </div>

                <!-- Unit Type — drives form layout below -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Unit Type</Label>
                    <Select :model-value="form.type || undefined" @update:model-value="form.type = $event ?? null">
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

                <!-- Unit Number -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Unit Number <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        v-model="form.unit_number"
                        :placeholder="form.type === 'villa' ? 'e.g. Villa-01' : form.type === 'townhouse' ? 'e.g. TH-01' : form.type === 'shop' ? 'e.g. S-GF-01' : 'e.g. A-1205'"
                        :class="[f, form.errors.unit_number && 'border-destructive']"
                    />
                    <p v-if="form.errors.unit_number" class="text-xs text-destructive">{{ form.errors.unit_number }}</p>
                </div>

                <!-- Block (conditional) -->
                <div v-if="typeConfig.showBlock" class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        {{ typeConfig.blockLabel }}
                    </Label>
                    <Input
                        v-model="form.block"
                        :placeholder="typeConfig.blockLabel === 'Phase' ? 'e.g. Phase 1' : typeConfig.blockLabel === 'Phase / Row' ? 'e.g. Row A' : 'e.g. Block A'"
                        :class="[f, form.errors.block && 'border-destructive']"
                    />
                    <p v-if="form.errors.block" class="text-xs text-destructive">{{ form.errors.block }}</p>
                </div>

                <!-- Floor (conditional) -->
                <div v-if="typeConfig.showFloor" class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        {{ typeConfig.floorLabel }}
                    </Label>
                    <Input
                        type="number"
                        min="0"
                        v-model="form.floor"
                        :placeholder="typeConfig.floorLabel === 'Level' ? 'e.g. 1 (Ground = 0)' : typeConfig.floorLabel === 'Starting Floor' ? 'e.g. 10' : 'e.g. 12'"
                        :class="[f, form.errors.floor && 'border-destructive']"
                    />
                    <p v-if="form.errors.floor" class="text-xs text-destructive">{{ form.errors.floor }}</p>
                </div>

            </div>
        </div>

        <!-- ── Specifications ─────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4m0 0h18"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Specifications</p>
                    <p class="text-xs text-muted-foreground">Size, pricing and availability details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <!-- Bedrooms (conditional — hidden for commercial) -->
                <div v-if="typeConfig.showBedrooms" class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Bedrooms</Label>
                    <Input
                        type="number" min="0" max="10"
                        v-model="form.bedrooms"
                        placeholder="e.g. 3"
                        :class="[f, form.errors.bedrooms && 'border-destructive']"
                    />
                    <p v-if="form.errors.bedrooms" class="text-xs text-destructive">{{ form.errors.bedrooms }}</p>
                </div>

                <!-- Size -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Size <span class="text-muted-foreground font-normal">(sqft)</span>
                    </Label>
                    <Input
                        type="number" min="1"
                        v-model="form.size_sqft"
                        :placeholder="form.type === 'duplex' ? 'e.g. 2800 (both levels)' : 'e.g. 1450'"
                        :class="[f, form.errors.size_sqft && 'border-destructive']"
                    />
                    <p v-if="form.errors.size_sqft" class="text-xs text-destructive">{{ form.errors.size_sqft }}</p>
                </div>

                <!-- View -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">View</Label>
                    <Input
                        v-model="form.view"
                        placeholder="e.g. Lake View, City View, Garden View"
                        :class="[f, form.errors.view && 'border-destructive']"
                    />
                    <p v-if="form.errors.view" class="text-xs text-destructive">{{ form.errors.view }}</p>
                </div>

                <!-- Price -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Price <span class="text-muted-foreground font-normal">(BDT)</span>
                        <span class="text-destructive"> *</span>
                    </Label>
                    <Input
                        type="number" min="0" step="1"
                        v-model="form.price"
                        placeholder="e.g. 12000000"
                        :class="[f, form.errors.price && 'border-destructive']"
                    />
                    <p v-if="form.errors.price" class="text-xs text-destructive">{{ form.errors.price }}</p>
                </div>

                <!-- Status -->
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

                <!-- Handover Date -->
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

            </div>
        </div>

        <!-- ── Media ──────────────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Media</p>
                    <p class="text-xs text-muted-foreground">Floor plan and room gallery images</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <div class="space-y-2">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Floor Plan</Label>
                    <ImageUpload
                        :file="form.floor_plan"
                        @update:file="form.floor_plan = $event"
                        :removed="form.remove_floor_plan ?? false"
                        @update:removed="form.remove_floor_plan = $event"
                        :preview="currentFloorPlan"
                        label="Click to upload floor plan"
                        hint="JPG, PNG, WEBP, PDF · Max 5 MB"
                        accept="image/jpeg,image/png,image/webp,application/pdf"
                    />
                    <p v-if="form.errors?.floor_plan" class="text-xs text-destructive">{{ form.errors.floor_plan }}</p>
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
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Room Gallery</Label>
                        </template>
                    </ImageGallery>
                    <p v-if="form.errors?.new_images" class="text-xs text-destructive">{{ form.errors.new_images }}</p>
                </div>

            </div>
        </div>

        <!-- ── Documents ──────────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Documents</p>
                    <p class="text-xs text-muted-foreground">Brochures, specs, legal docs (PDF, Word, Excel)</p>
                </div>
            </div>

            <div class="p-5">
                <DocumentList
                    :existing="currentDocuments"
                    :new-files="form.new_documents ?? []"
                    @update:new-files="form.new_documents = $event"
                    :remove-ids="form.remove_documents ?? []"
                    @update:remove-ids="form.remove_documents = $event"
                />
                <p v-if="form.errors?.new_documents" class="mt-2 text-xs text-destructive">{{ form.errors.new_documents }}</p>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between border-t border-border px-5 py-4">
                <p class="text-xs text-muted-foreground">
                    <span class="text-destructive">*</span> Required fields
                </p>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.units.index')"
                        class="inline-flex h-9 items-center rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-4 text-sm font-medium text-on-gold transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                        </svg>
                        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        {{ mode === 'edit' ? 'Save Changes' : 'Create Unit' }}
                    </button>
                </div>
            </div>

        </div>

    </form>
</template>
