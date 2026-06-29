<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    projects: { type: Array, required: true },
});

const search = ref('');

const filtered = computed(() =>
    props.projects.filter(p =>
        p.name.toLowerCase().includes(search.value.toLowerCase()) ||
        (p.location ?? '').toLowerCase().includes(search.value.toLowerCase())
    )
);

const STATUS_STYLE = {
    draft:              'bg-slate-100 text-slate-500 dark:bg-slate-700/40 dark:text-slate-400',
    planning:           'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300',
    under_construction: 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300',
    completed:          'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300',
    on_hold:            'bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-300',
};

function statusClass(status) {
    return STATUS_STYLE[status] ?? 'bg-slate-100 text-slate-500';
}
</script>

<template>
    <Head title="Unit Blueprint" />

    <AdminLayout>
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Unit Blueprint</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Select a project to configure its unit inventory.</p>
            </div>
            <Link :href="route('admin.units.index')"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-border bg-admin-surface-card px-4 text-sm font-medium text-foreground hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 12h6M9 16h4"/>
                </svg>
                Unit List
            </Link>
        </div>

        <!-- Search -->
        <div class="mb-4 flex items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                    width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input v-model="search" type="text" placeholder="Search projects…"
                    class="h-9 w-full rounded-lg border border-border bg-white dark:bg-slate-800 pl-9 pr-3 text-sm text-foreground placeholder-muted-foreground focus:border-admin-accent focus:outline-none focus:ring-1 focus:ring-admin-accent/30" />
            </div>
        </div>

        <!-- Empty search result -->
        <div v-if="!filtered.length" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-16 text-center">
            <p class="font-medium text-foreground">No projects found</p>
            <p class="mt-1 text-sm text-muted-foreground">Try a different search or create a project first.</p>
            <Link :href="route('admin.projects.create')"
                class="mt-4 inline-flex rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white hover:bg-admin-accent/90 transition-colors">
                Create Project
            </Link>
        </div>

        <!-- Project grid -->
        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="project in filtered"
                :key="project.id"
                :href="route('admin.blueprint.show', project.id)"
                class="group flex flex-col rounded-xl border border-border bg-admin-surface-card p-5 shadow-sm transition-all hover:border-admin-accent/40 hover:shadow-md"
            >
                <!-- Project name + status -->
                <div class="mb-3 flex items-start justify-between gap-2">
                    <h3 class="font-semibold text-foreground group-hover:text-admin-accent transition-colors line-clamp-2">
                        {{ project.name }}
                    </h3>
                    <span :class="['shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide', statusClass(project.status)]">
                        {{ project.status_label }}
                    </span>
                </div>

                <!-- Location -->
                <p v-if="project.location" class="mb-3 text-xs text-muted-foreground line-clamp-1">
                    {{ project.location }}
                </p>

                <!-- Stats row -->
                <div class="mt-auto flex items-center gap-4 border-t border-border/60 pt-3 text-xs text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                        </svg>
                        {{ project.buildings_count ?? 0 }} buildings
                    </span>
                    <span class="flex items-center gap-1">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="8" width="8" height="13" rx="1"/><rect x="13" y="3" width="8" height="18" rx="1"/>
                        </svg>
                        {{ project.units_count ?? 0 }} units
                    </span>
                    <span class="ml-auto flex items-center gap-1 font-medium text-admin-accent">
                        Open Blueprint
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                    </span>
                </div>
            </Link>
        </div>
    </AdminLayout>
</template>
