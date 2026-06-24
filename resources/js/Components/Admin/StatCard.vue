<script setup>
/*
 * color variants → predefined light + dark bg for the icon well.
 * Usage: <StatCard color="blue" label="..." value="...">
 *   <template #icon><svg .../></template>
 * </StatCard>
 */
const colorMap = {
    // Portal accent — tracks --admin-accent or --client-accent CSS var automatically.
    accent: { well: 'bg-admin-accent/10',              text: 'text-admin-accent'                    },
    // Semantic colours — fixed regardless of portal theme.
    blue:   { well: 'bg-blue-100   dark:bg-blue-500/15',   text: 'text-blue-600   dark:text-blue-400'   },
    green:  { well: 'bg-green-100  dark:bg-green-500/15',  text: 'text-green-600  dark:text-green-400'  },
    orange: { well: 'bg-orange-100 dark:bg-orange-500/15', text: 'text-orange-500 dark:text-orange-400' },
    indigo: { well: 'bg-indigo-100 dark:bg-indigo-500/15', text: 'text-indigo-600 dark:text-indigo-400' },
    red:    { well: 'bg-red-100    dark:bg-red-500/15',    text: 'text-red-600    dark:text-red-400'    },
    purple: { well: 'bg-purple-100 dark:bg-purple-500/15', text: 'text-purple-600 dark:text-purple-400' },
};

const props = defineProps({
    label:   { type: String,  required: true },
    value:   { type: String,  required: true },
    sub:     { type: String,  default: null  },
    color:   { type: String,  default: 'blue' },
    subVariant: {
        type: String,
        default: 'muted', // 'muted' | 'green' | 'orange' | 'blue' | 'red'
    },
});

const subColorMap = {
    muted:  'text-slate-500 dark:text-slate-400',
    green:  'text-green-600 dark:text-green-400',
    orange: 'text-orange-500 dark:text-orange-400',
    blue:   'text-blue-600  dark:text-blue-400',
    red:    'text-red-600   dark:text-red-400',
};

const variant    = colorMap[props.color]   ?? colorMap.blue;
const subColor   = subColorMap[props.subVariant] ?? subColorMap.muted;
</script>

<template>
    <div class="flex flex-col gap-4 rounded-2xl border bg-admin-surface-card border-slate-100 dark:border-white/[0.06] p-5 shadow-sm transition-colors duration-200">
        <div class="flex items-center gap-3">
            <div
                class="flex h-11 w-11 flex-none items-center justify-center rounded-xl transition-colors"
                :class="variant.well"
            >
                <span :class="variant.text">
                    <slot name="icon" />
                </span>
            </div>
            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ label }}</span>
        </div>
        <div>
            <div class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">{{ value }}</div>
            <div v-if="sub" class="mt-1 text-sm font-medium" :class="subColor">{{ sub }}</div>
        </div>
    </div>
</template>
