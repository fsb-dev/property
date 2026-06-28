<script setup>
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

defineProps({
    title:       { type: String, default: '' },
    breadcrumbs: { type: Array,  default: () => [] },
});

const emit = defineEmits(['toggle-sidebar']);
const { isDark, toggle } = useTheme();
</script>

<template>
    <header class="
        flex flex-none items-center gap-4 px-6
        bg-white dark:bg-admin-surface-topbar
        border-b border-border
        transition-colors duration-200
    " style="height:64px; z-index:10; box-shadow:0 1px 0 #EEF2F8;">

        <!-- Mobile hamburger -->
        <button
            class="flex h-9 w-9 flex-none items-center justify-center rounded-lg
                   border border-slate-200 dark:border-white/[0.08]
                   bg-white dark:bg-white/[0.04]
                   text-slate-600 dark:text-slate-400
                   hover:bg-slate-50 dark:hover:bg-white/[0.08]
                   transition-colors lg:hidden"
            @click="emit('toggle-sidebar')"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>

        <!-- Breadcrumb / title -->
        <div class="flex flex-1 items-center gap-2 min-w-0">
            <template v-if="breadcrumbs.length">
                <template v-for="(crumb, i) in breadcrumbs" :key="i">
                    <Link
                        v-if="crumb.href"
                        :href="crumb.href"
                        class="text-sm font-medium truncate text-slate-500 dark:text-slate-400 hover:text-admin-accent transition-colors"
                    >{{ crumb.label }}</Link>
                    <span
                        v-else
                        class="text-sm font-semibold truncate text-slate-900 dark:text-slate-100"
                    >{{ crumb.label }}</span>
                    <svg
                        v-if="i < breadcrumbs.length - 1"
                        width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="flex-none text-slate-300 dark:text-slate-600"
                    ><path d="M9 6l6 6-6 6"/></svg>
                </template>
            </template>
            <h1 v-else class="text-base font-bold truncate text-slate-900 dark:text-slate-100">{{ title }}</h1>
        </div>

        <!-- Right actions -->
        <div class="flex items-center gap-2 flex-none">

            <!-- Search -->
            <div class="relative hidden md:flex items-center">
                <svg class="absolute left-3 pointer-events-none text-slate-400" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/>
                </svg>
                <input
                    type="text"
                    placeholder="Search by project, unit, client..."
                    class="h-9 w-64 rounded-xl border pl-9 pr-3 text-sm outline-none transition-colors
                           bg-white dark:bg-white/[0.04]
                           border-border dark:border-white/[0.08]
                           text-foreground dark:text-slate-100
                           placeholder:text-muted-foreground
                           focus:border-admin-accent
                           focus:ring-2 focus:ring-admin-accent/20"
                    style="box-shadow:0 2px 8px rgba(0,0,0,0.03);"
                >
            </div>

            <!-- Notifications -->
            <button class="relative flex h-9 w-9 items-center justify-center rounded-lg transition-colors
                           border border-slate-200 dark:border-white/[0.08]
                           bg-admin-surface-card
                           text-slate-500 dark:text-slate-400
                           hover:bg-admin-surface-page">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8.5a6 6 0 1 0-12 0c0 6-2.5 7.5-2.5 7.5h17S18 14.5 18 8.5"/>
                    <path d="M10.5 20a2 2 0 0 0 3 0"/>
                </svg>
                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-admin-surface-topbar bg-red-500 text-white" style="font-size:9px;font-weight:700;">3</span>
            </button>

            <!-- Dark / Light toggle -->
            <button
                class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors
                       border border-slate-200 dark:border-white/[0.08]
                       bg-white dark:bg-white/[0.04]
                       text-slate-500 dark:text-slate-400
                       hover:bg-slate-50 dark:hover:bg-white/[0.08]"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                @click="toggle"
            >
                <!-- Sun (shown in dark mode) -->
                <svg v-if="isDark" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <!-- Moon (shown in light mode) -->
                <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>
    </header>
</template>
