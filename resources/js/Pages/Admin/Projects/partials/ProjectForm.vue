<script setup>
import { Link }       from '@inertiajs/vue3';
import { Input }      from '@/Components/ui/input';
import { Label }      from '@/Components/ui/label';
import { Textarea }   from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import DatePicker     from '@/Components/ui/date-picker/DatePicker.vue';

const props = defineProps({
    form:  { type: Object, required: true },
    enums: { type: Object, required: true },
    mode:  { type: String, default: 'create' },
});

const emit = defineEmits(['submit']);

// Shared field class — lighter bg + softer border for all inputs/selects/textareas
const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-4">

        <!-- ── Basic Information ──────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Basic Information</p>
                    <p class="text-xs text-muted-foreground">Core identity of the project</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <!-- Name -->
                <div class="md:col-span-2 space-y-1.5">
                    <Label for="name" class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Project Name <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="e.g. LakeView Residences"
                        :class="[f, form.errors.name && 'border-destructive']"
                    />
                    <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                </div>

                <!-- Type -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Type <span class="text-destructive">*</span>
                    </Label>
                    <Select :model-value="form.type" @update:model-value="form.type = $event">
                        <SelectTrigger :class="[f, form.errors.type && 'border-destructive']">
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.type" class="text-xs text-destructive">{{ form.errors.type }}</p>
                </div>

                <!-- Category -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Category</Label>
                    <Select :model-value="form.category || undefined" @update:model-value="form.category = $event ?? null">
                        <SelectTrigger :class="f">
                            <SelectValue placeholder="— None —" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="c in enums.categories" :key="c.value" :value="c.value">{{ c.label }}</SelectItem>
                        </SelectContent>
                    </Select>
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
                            <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.status" class="text-xs text-destructive">{{ form.errors.status }}</p>
                </div>

                <!-- Handover Date -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Handover Date
                    </Label>
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

        <!-- ── Location ───────────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Location</p>
                    <p class="text-xs text-muted-foreground">Where the project is situated</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <div class="space-y-1.5">
                    <Label for="location" class="text-xs font-medium text-slate-500 dark:text-slate-400">Area / Neighbourhood</Label>
                    <Input id="location" v-model="form.location" placeholder="e.g. Bashundhara R/A, Dhaka" :class="[f, form.errors.location && 'border-destructive']" />
                    <p v-if="form.errors.location" class="text-xs text-destructive">{{ form.errors.location }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label for="address" class="text-xs font-medium text-slate-500 dark:text-slate-400">Full Address</Label>
                    <Input id="address" v-model="form.address" placeholder="Plot 12, Road 5, Block C..." :class="[f, form.errors.address && 'border-destructive']" />
                    <p v-if="form.errors.address" class="text-xs text-destructive">{{ form.errors.address }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label for="latitude" class="text-xs font-medium text-slate-500 dark:text-slate-400">Latitude</Label>
                    <Input id="latitude" type="number" step="any" v-model="form.latitude" placeholder="23.8103" :class="[f, form.errors.latitude && 'border-destructive']" />
                    <p v-if="form.errors.latitude" class="text-xs text-destructive">{{ form.errors.latitude }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label for="longitude" class="text-xs font-medium text-slate-500 dark:text-slate-400">Longitude</Label>
                    <Input id="longitude" type="number" step="any" v-model="form.longitude" placeholder="90.4125" :class="[f, form.errors.longitude && 'border-destructive']" />
                    <p v-if="form.errors.longitude" class="text-xs text-destructive">{{ form.errors.longitude }}</p>
                </div>

            </div>
        </div>

        <!-- ── Project Details ────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <path d="M2 20h20M6 20V10l6-7 6 7v10"/><path d="M10 20v-5h4v5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Project Details</p>
                    <p class="text-xs text-muted-foreground">Structural and descriptive information</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <div class="space-y-1.5">
                    <Label for="total_floors" class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Floors</Label>
                    <Input id="total_floors" type="number" min="1" v-model="form.total_floors" placeholder="e.g. 12" :class="[f, form.errors.total_floors && 'border-destructive']" />
                    <p v-if="form.errors.total_floors" class="text-xs text-destructive">{{ form.errors.total_floors }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label for="total_units" class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Units</Label>
                    <Input id="total_units" type="number" min="1" v-model="form.total_units" placeholder="e.g. 48" :class="[f, form.errors.total_units && 'border-destructive']" />
                    <p v-if="form.errors.total_units" class="text-xs text-destructive">{{ form.errors.total_units }}</p>
                </div>

                <div class="md:col-span-2 space-y-1.5">
                    <Label for="description" class="text-xs font-medium text-slate-500 dark:text-slate-400">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Overview of the project..."
                        rows="4"
                        :class="[f, 'resize-none', form.errors.description && 'border-destructive']"
                    />
                    <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                </div>

            </div>
        </div>

        <!-- ── Footer ─────────────────────────────────────────── -->
        <div class="flex items-center justify-between rounded-xl border border-border bg-admin-surface-card px-5 py-4">
            <p class="text-xs text-muted-foreground">
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
                    <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                    </svg>
                    <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    {{ mode === 'edit' ? 'Save Changes' : 'Create Project' }}
                </button>
            </div>
        </div>

    </form>
</template>
