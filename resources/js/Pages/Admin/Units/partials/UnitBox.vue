<script setup>
defineProps({
    unit: { type: Object, required: true },
});
defineEmits(['click']);

const STATUS_CLASSES = {
    not_configured: 'bg-orange-400 hover:bg-orange-500 border-orange-500/30',
    configured:     'bg-blue-500  hover:bg-blue-600  border-blue-600/30',
    available:      'bg-emerald-500 hover:bg-emerald-600 border-emerald-600/30',
    booked:         'bg-red-500   hover:bg-red-600   border-red-600/30',
    sold:           'bg-purple-500 hover:bg-purple-600 border-purple-600/30',
};

function boxClass(status) {
    return STATUS_CLASSES[status] ?? 'bg-slate-400 hover:bg-slate-500 border-slate-500/30';
}
</script>

<template>
    <button
        type="button"
        @click="$emit('click', unit)"
        :title="`${unit.unit_number}${unit.type_label ? ' · ' + unit.type_label : ''} · ${unit.status_label}`"
        :class="[
            'group relative flex h-8 min-w-[2rem] cursor-pointer items-center justify-center rounded border px-1 transition-all duration-150 hover:scale-110 hover:z-10 hover:shadow-md',
            boxClass(unit.status)
        ]"
    >
        <span class="text-[9px] font-bold leading-none text-white drop-shadow-sm select-none">
            {{ unit.unit_number.split('-').pop() }}
        </span>

        <!-- Tooltip on hover -->
        <div class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-1.5 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 dark:bg-slate-700">
            <div class="font-semibold">{{ unit.unit_number }}</div>
            <div v-if="unit.type_label" class="text-slate-300">{{ unit.type_label }}</div>
            <div v-if="unit.size_sqft" class="text-slate-300">{{ unit.size_sqft.toLocaleString() }} sqft</div>
            <div v-if="unit.price" class="text-slate-300">৳{{ Number(unit.price).toLocaleString() }}</div>
            <!-- Tooltip arrow -->
            <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-slate-700" />
        </div>
    </button>
</template>
