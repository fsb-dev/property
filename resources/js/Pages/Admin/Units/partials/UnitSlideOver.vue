<script setup>
import { ref, watch, computed, inject } from 'vue';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';

const props = defineProps({
    open:    { type: Boolean, required: true },
    unit:    { type: Object,  default: null },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'save', 'apply-config']);

const enums = inject('enums');

// Local form — clone unit on open so edits don't mutate parent state
const form = ref({});
const applyOverwrite = ref(false);
const showApplyConfirm = ref(false);

watch(() => props.unit, (u) => {
    if (u) form.value = { ...u };
}, { immediate: true });

// Which fields to show based on unit type
const typeConfig = computed(() => {
    const t = enums.unit_types?.find(t => t.value === form.value.type);
    return t?.formConfig ?? { showBedrooms: true, showBlock: true, showFloor: true };
});

const isResidential = computed(() =>
    typeConfig.value.showBedrooms
);

// Dirty check — only save if something changed
const isDirty = computed(() => {
    if (!props.unit) return false;
    return JSON.stringify(form.value) !== JSON.stringify(props.unit);
});

function save() {
    if (!isDirty.value || props.loading) return;
    const { id, ...payload } = form.value;
    emit('save', props.unit.id, payload);
}

function openApplyConfirm() {
    applyOverwrite.value = false;
    showApplyConfirm.value = true;
}

function confirmApply() {
    showApplyConfirm.value = false;
    emit('apply-config', props.unit.id, applyOverwrite.value);
}

function close() {
    showApplyConfirm.value = false;
    emit('close');
}

// Field style shorthand
const f = 'border-border bg-white dark:bg-slate-800/60 focus-visible:ring-admin-accent/30 focus-visible:border-admin-accent';

const STATUS_OPTIONS = [
    { value: 'configured', label: 'Configured' },
    { value: 'available',  label: 'Available' },
    { value: 'booked',     label: 'Booked' },
    { value: 'sold',       label: 'Sold' },
];

const STATUS_PILL = {
    not_configured: 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
    configured:     'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
    available:      'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    booked:         'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
    sold:           'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300',
};
function statusPill(status) { return STATUS_PILL[status] ?? 'bg-slate-100 text-slate-600'; }
</script>

<template>
    <!-- Backdrop -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
        leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="open" class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm" @click="close" />
    </Transition>

    <!-- Panel -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-x-full opacity-0"
        enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-full opacity-0"
    >
        <div v-if="open && unit"
            class="fixed right-0 top-0 z-50 flex h-full w-full max-w-md flex-col bg-admin-surface-card shadow-2xl">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div>
                    <p class="text-xs text-muted-foreground">Unit Configuration</p>
                    <h2 class="font-bold text-foreground">{{ unit.unit_number }}</h2>
                </div>

                <!-- Status pill -->
                <div class="flex items-center gap-3">
                    <span :class="['rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide', statusPill(unit.status)]">
                        {{ unit.status_label }}
                    </span>
                    <button type="button" @click="close"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Scrollable form body -->
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">

                <!-- Unit number + type -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500">Unit Number</Label>
                        <Input v-model="form.unit_number" :class="[f, 'text-sm']" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500">Type</Label>
                        <Select :model-value="form.type" @update:model-value="form.type = $event">
                            <SelectTrigger :class="[f, 'text-sm']"><SelectValue placeholder="Select type" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in enums.unit_types" :key="t.value" :value="t.value">
                                    {{ t.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Wing + Floor (read-only) -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500">Wing</Label>
                        <Input v-model="form.wing" :class="[f, 'text-sm']" placeholder="e.g. A, B, East…" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500">Floor</Label>
                        <Input :model-value="unit.floor" readonly
                            class="border-border bg-slate-50 dark:bg-slate-800/40 text-sm cursor-not-allowed opacity-60" />
                    </div>
                </div>

                <!-- Residential fields -->
                <template v-if="isResidential">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500">Bedrooms</Label>
                            <Input v-model.number="form.bedrooms" type="number" min="0" max="10"
                                :class="[f, 'text-sm']" placeholder="0" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500">Bathrooms</Label>
                            <Input v-model.number="form.bathrooms" type="number" min="0" max="10"
                                :class="[f, 'text-sm']" placeholder="0" />
                        </div>
                    </div>

                    <!-- Balcony + Servant room toggles -->
                    <div class="flex gap-6">
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <input type="checkbox" v-model="form.balcony"
                                class="h-4 w-4 rounded border-border text-admin-accent accent-admin-accent" />
                            <span class="text-sm text-foreground">Balcony</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <input type="checkbox" v-model="form.servant_room"
                                class="h-4 w-4 rounded border-border text-admin-accent accent-admin-accent" />
                            <span class="text-sm text-foreground">Servant Room</span>
                        </label>
                    </div>
                </template>

                <!-- Size + View -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500">Size (sqft)</Label>
                        <Input v-model.number="form.size_sqft" type="number" min="1"
                            :class="[f, 'text-sm']" placeholder="e.g. 1150" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500">View</Label>
                        <Select :model-value="form.view || undefined" @update:model-value="form.view = $event">
                            <SelectTrigger :class="[f, 'text-sm']"><SelectValue placeholder="Select" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="v in enums.views" :key="v" :value="v">{{ v }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Price -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500">Price (BDT)</Label>
                    <Input v-model.number="form.price" type="number" min="0"
                        :class="[f, 'text-sm']" placeholder="e.g. 8500000" />
                </div>

                <!-- Status -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500">Status</Label>
                    <Select :model-value="form.status" @update:model-value="form.status = $event">
                        <SelectTrigger :class="[f, 'text-sm']"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in STATUS_OPTIONS" :key="s.value" :value="s.value">
                                {{ s.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Handover date -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500">Handover Date</Label>
                    <Input v-model="form.handover_date" type="date" :class="[f, 'text-sm']" />
                </div>

                <!-- Apply config to floor -->
                <div class="rounded-xl border border-border bg-slate-50/60 dark:bg-white/[0.02] p-4">
                    <p class="mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Apply Config to Floor</p>
                    <p class="mb-3 text-xs text-muted-foreground">
                        Copy this unit's configuration to all other units on floor {{ unit.floor }}.
                    </p>

                    <template v-if="showApplyConfirm">
                        <label class="mb-3 flex cursor-pointer items-center gap-2 text-xs text-foreground">
                            <input type="checkbox" v-model="applyOverwrite"
                                class="h-3.5 w-3.5 accent-admin-accent" />
                            Also overwrite already-configured units
                        </label>
                        <div class="flex gap-2">
                            <button type="button" @click="confirmApply" :disabled="loading"
                                class="flex-1 rounded-lg bg-admin-accent py-1.5 text-xs font-semibold text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-50">
                                Confirm Apply
                            </button>
                            <button type="button" @click="showApplyConfirm = false"
                                class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted-foreground hover:text-foreground">
                                Cancel
                            </button>
                        </div>
                    </template>
                    <button v-else type="button" @click="openApplyConfirm"
                        class="w-full rounded-lg border border-admin-accent/30 py-1.5 text-xs font-medium text-admin-accent transition-colors hover:bg-admin-accent/5">
                        Apply to all unconfigured on this floor →
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center gap-3 border-t border-border px-5 py-4">
                <button type="button" @click="close"
                    class="rounded-lg border border-border px-4 py-2 text-sm text-muted-foreground transition-colors hover:text-foreground">
                    Cancel
                </button>
                <button type="button" @click="save" :disabled="!isDirty || loading"
                    :class="['flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-semibold transition-colors',
                        isDirty && !loading
                            ? 'bg-admin-accent text-white hover:bg-admin-accent/90'
                            : 'bg-admin-accent/30 text-white/60 cursor-not-allowed']">
                    <svg v-if="loading" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    {{ loading ? 'Saving…' : 'Save Unit' }}
                </button>
            </div>
        </div>
    </Transition>
</template>

