<script setup>
import { useTheme } from '@/composables/useTheme';

defineProps({
    title:       { type: String, default: '' },
    breadcrumbs: { type: Array,  default: () => [] },
});

const emit = defineEmits(['toggle-sidebar']);
const { isDark, toggle } = useTheme();
</script>

<template>
    <header
        class="flex flex-none items-center gap-4 px-6 border-b border-border bg-client-surface-topbar transition-colors duration-200"
        style="height: 64px; z-index: 10; box-shadow: 0 1px 0 rgba(0,0,0,0.05);"
    >
        <!-- Mobile hamburger -->
        <button
            class="flex h-9 w-9 flex-none items-center justify-center rounded-lg border border-border text-muted-foreground transition-colors hover:bg-muted lg:hidden"
            @click="emit('toggle-sidebar')"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>

        <!-- Breadcrumb / Title -->
        <div class="flex flex-1 min-w-0 items-center gap-2">
            <template v-if="breadcrumbs.length">
                <template v-for="(crumb, i) in breadcrumbs" :key="i">
                    <a v-if="crumb.href" :href="crumb.href"
                        class="truncate text-sm font-medium text-muted-foreground transition-colors hover:text-client-accent">
                        {{ crumb.label }}
                    </a>
                    <span v-else class="truncate text-sm font-semibold text-foreground">{{ crumb.label }}</span>
                    <svg v-if="i < breadcrumbs.length - 1"
                        width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="flex-none text-muted-foreground/40">
                        <path d="M9 6l6 6-6 6"/>
                    </svg>
                </template>
            </template>
            <h1 v-else class="truncate text-base font-bold text-foreground">{{ title }}</h1>
        </div>

        <!-- Right actions -->
        <div class="flex flex-none items-center gap-2">

            <!-- Notifications -->
            <button class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-client-surface-card text-muted-foreground transition-colors hover:bg-muted">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8.5a6 6 0 1 0-12 0c0 6-2.5 7.5-2.5 7.5h17S18 14.5 18 8.5"/>
                    <path d="M10.5 20a2 2 0 0 0 3 0"/>
                </svg>
            </button>

            <!-- Dark / Light toggle -->
            <button
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-client-surface-card text-muted-foreground transition-colors hover:bg-muted"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                @click="toggle"
            >
                <svg v-if="isDark" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>
    </header>
</template>
