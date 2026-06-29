<script setup>
import { ref, inject } from 'vue';
import BlueprintFloorRow from './BlueprintFloorRow.vue';

const props = defineProps({
    section: { type: Object, required: true },
});

const bp      = inject('blueprint');
const collapsed = ref(false);

const TYPE_BADGE = {
    residential: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
    commercial:  'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    office:      'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
    villa:       'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
    industrial:  'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300',
    mixed:       'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
};

function typeBadge(type) {
    return TYPE_BADGE[type] ?? 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <div>
        <!-- Section header -->
        <button type="button"
            @click="collapsed = !collapsed"
            class="flex w-full items-center gap-3 bg-white dark:bg-transparent px-5 py-3 text-left text-sm transition-colors hover:bg-slate-50/60 dark:hover:bg-white/[0.02]">

            <span :class="['rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide', typeBadge(section.type)]">
                {{ section.type }}
            </span>

            <span class="font-medium text-foreground">{{ section.name || 'Unnamed Section' }}</span>

            <span class="text-xs text-muted-foreground">
                Floors {{ section.floor_start }}–{{ section.floor_end }}
            </span>

            <span v-if="section.planned_units" class="ml-auto text-xs text-muted-foreground">
                {{ section.floors.reduce((t, f) => t + f.unit_count, 0) }} /
                {{ section.planned_units }} units
            </span>

            <svg :class="['shrink-0 text-muted-foreground transition-transform', collapsed && 'rotate-180', !section.planned_units && 'ml-auto']"
                width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </button>

        <!-- Floor rows -->
        <div v-show="!collapsed" class="border-t border-border/50 bg-slate-50/40 dark:bg-white/[0.01]">
            <BlueprintFloorRow
                v-for="floorData in [...section.floors].reverse()"
                :key="floorData.floor"
                :floor-data="floorData"
                :section="section"
            />
        </div>
    </div>
</template>
