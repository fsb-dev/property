<template>
    <div class="group/tip relative inline-flex">
        <slot />
        <div
            class="pointer-events-none absolute bottom-[calc(100%+6px)] left-1/2 z-50 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 dark:bg-slate-700 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-md transition-opacity duration-150 group-hover/tip:opacity-100"
        >
            {{ text }}
            <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-slate-700" />
        </div>
    </div>
</template>

<script setup>
defineProps({ text: { type: String, required: true } });
</script>
