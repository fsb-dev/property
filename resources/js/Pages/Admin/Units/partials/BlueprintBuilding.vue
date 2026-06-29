<script setup>
import { ref } from 'vue';
import BlueprintSection from './BlueprintSection.vue';

defineProps({
    building: { type: Object, required: true },
});

const collapsed = ref(false);
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card shadow-sm">
        <!-- Building header -->
        <button type="button"
            @click="collapsed = !collapsed"
            class="flex w-full items-center gap-3 border-b border-border bg-slate-50/80 dark:bg-white/[0.03] px-5 py-3.5 text-left transition-colors hover:bg-slate-100/60 dark:hover:bg-white/[0.05]">
            <svg class="shrink-0 text-admin-accent" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
            </svg>
            <span class="font-semibold text-foreground text-sm">{{ building.name }}</span>
            <span v-if="building.total_floors" class="text-xs text-muted-foreground">
                · {{ building.total_floors }} floors
            </span>

            <!-- Section count badge -->
            <span class="ml-1 rounded-full bg-admin-accent/10 px-2 py-0.5 text-[10px] font-medium text-admin-accent">
                {{ building.sections.length }} {{ building.sections.length === 1 ? 'section' : 'sections' }}
            </span>

            <svg :class="['ml-auto shrink-0 text-muted-foreground transition-transform', collapsed && 'rotate-180']"
                width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </button>

        <!-- Sections -->
        <div v-show="!collapsed" class="divide-y divide-border">
            <BlueprintSection
                v-for="section in building.sections"
                :key="section.id"
                :section="section"
            />

            <div v-if="!building.sections.length"
                class="flex h-20 items-center justify-center text-sm text-muted-foreground">
                No sections defined for this building.
            </div>
        </div>
    </div>
</template>
