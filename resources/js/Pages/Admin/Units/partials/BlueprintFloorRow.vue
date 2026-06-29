<script setup>
import { ref, inject } from 'vue';
import UnitBox from './UnitBox.vue';

const props = defineProps({
    floorData: { type: Object, required: true }, // { floor, unit_count, units[] }
    section:   { type: Object, required: true },
});

const bp      = inject('blueprint');
const openUnit = inject('openUnit');

const generateCount = ref(props.section.planned_units
    ? Math.round(props.section.planned_units / (props.section.floor_end - props.section.floor_start + 1))
    : 4
);
const generating = ref(false);
const deleting   = ref(false);
const confirmDelete = ref(false);

async function generate() {
    if (!generateCount.value || generateCount.value < 1) return;
    generating.value = true;
    try {
        await bp.generateFloor(props.section.id, props.floorData.floor, generateCount.value);
    } finally {
        generating.value = false;
    }
}

async function deleteFloor() {
    if (!confirmDelete.value) { confirmDelete.value = true; return; }
    deleting.value = true;
    confirmDelete.value = false;
    try {
        await bp.deleteFloor(props.section.id, props.floorData.floor);
    } finally {
        deleting.value = false;
    }
}

function cancelDelete() { confirmDelete.value = false; }
</script>

<template>
    <div class="flex min-h-[52px] items-start gap-3 border-b border-border/30 px-4 py-2.5 last:border-0">
        <!-- Floor label -->
        <div class="flex w-16 shrink-0 items-center justify-between pt-0.5">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                F{{ floorData.floor }}
            </span>
        </div>

        <!-- Units area -->
        <div class="flex flex-1 flex-wrap gap-1.5 py-0.5 min-w-0">
            <!-- Generated units -->
            <UnitBox
                v-for="unit in floorData.units"
                :key="unit.id"
                :unit="unit"
                @click="openUnit(unit)"
            />

            <!-- Empty state + generate -->
            <div v-if="!floorData.units.length" class="flex items-center gap-2">
                <input
                    v-model.number="generateCount"
                    type="number" min="1" max="200"
                    class="h-7 w-16 rounded-md border border-border bg-white dark:bg-slate-800 px-2 text-center text-xs focus:border-admin-accent focus:outline-none focus:ring-1 focus:ring-admin-accent/30"
                    placeholder="4"
                    @keydown.enter="generate"
                />
                <button type="button" @click="generate" :disabled="generating"
                    class="flex items-center gap-1 rounded-md border border-admin-accent/40 bg-admin-accent/5 px-2.5 py-1 text-xs font-medium text-admin-accent transition-colors hover:bg-admin-accent/10 disabled:opacity-50">
                    <svg v-if="generating" class="animate-spin" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    <svg v-else width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Generate
                </button>
            </div>
        </div>

        <!-- Floor actions (only if units exist) -->
        <div v-if="floorData.units.length" class="flex shrink-0 items-center gap-1 pt-0.5">
            <span class="text-[10px] text-muted-foreground">{{ floorData.unit_count }} units</span>

            <!-- Delete floor -->
            <template v-if="confirmDelete">
                <span class="text-[10px] text-red-500">Delete all?</span>
                <button type="button" @click="deleteFloor" :disabled="deleting"
                    class="rounded px-1.5 py-0.5 text-[10px] font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
                    Yes
                </button>
                <button type="button" @click="cancelDelete"
                    class="rounded px-1.5 py-0.5 text-[10px] text-muted-foreground hover:text-foreground">
                    No
                </button>
            </template>
            <button v-else type="button" @click="deleteFloor"
                class="ml-1 flex h-6 w-6 items-center justify-center rounded text-muted-foreground/50 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                </svg>
            </button>
        </div>
    </div>
</template>
