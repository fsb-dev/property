<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Input }  from '@/Components/ui/input';
import { Badge }  from '@/Components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

const props = defineProps({
    projects: { type: Object, required: true },
    filters:  { type: Object, default: () => ({}) },
    stats:    { type: Object, required: true },
    enums:    { type: Object, required: true },
});

// ── Filters ────────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
const type   = ref(props.filters.type   ?? '');
const status = ref(props.filters.status ?? '');

let searchTimer;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});
watch([type, status], applyFilters);

function applyFilters() {
    router.get(route('admin.projects.index'), {
        search: search.value || undefined,
        type:   type.value   || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}

// ── Delete confirm ─────────────────────────────────────────────
const deleteForm        = useForm({});
const confirmingDelete  = ref(null);

function confirmDelete(project) { confirmingDelete.value = project; }
function cancelDelete()         { confirmingDelete.value = null; }
function submitDelete() {
    deleteForm.delete(route('admin.projects.destroy', confirmingDelete.value.id), {
        onSuccess: () => { confirmingDelete.value = null; },
    });
}

// ── Badge helpers ──────────────────────────────────────────────
const statusVariant = {
    planning:           'secondary',
    under_construction: 'warning',
    completed:          'success',
};
const typeVariant = {
    residential: 'default',
    commercial:  'secondary',
    mixed_use:   'outline',
    villa:       'success',
    plot:        'warning',
};
</script>

<template>
    <Head title="Projects" />

    <AdminLayout>
        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-foreground">Projects</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Manage all real-estate projects</p>
            </div>
            <Link
                :href="route('admin.projects.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                New Project
            </Link>
        </div>

        <!-- Stats Row -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Total</p>
                <p class="mt-1 text-2xl font-bold text-foreground">{{ stats.total }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Planning</p>
                <p class="mt-1 text-2xl font-bold text-muted-foreground">{{ stats.planning }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Under Construction</p>
                <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ stats.under_construction }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Completed</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.completed }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="relative min-w-[200px] flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <Input v-model="search" placeholder="Search projects..." class="pl-9" />
            </div>

            <Select :model-value="type || undefined" @update:model-value="type = $event ?? ''">
                <SelectTrigger class="w-[160px]">
                    <SelectValue placeholder="All types" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}</SelectItem>
                </SelectContent>
            </Select>
            <button v-if="type" @click="type = ''" class="text-xs text-muted-foreground hover:text-foreground">✕ type</button>

            <Select :model-value="status || undefined" @update:model-value="status = $event ?? ''">
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}</SelectItem>
                </SelectContent>
            </Select>
            <button v-if="status" @click="status = ''" class="text-xs text-muted-foreground hover:text-foreground">✕ status</button>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-border bg-admin-surface-card overflow-hidden">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="pl-4">Project</TableHead>
                        <TableHead>Type</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Units</TableHead>
                        <TableHead>Progress</TableHead>
                        <TableHead class="pr-4 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="projects.data.length === 0">
                        <TableCell colspan="6" class="py-16 text-center text-muted-foreground">
                            <svg class="mx-auto mb-3 text-border" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M2 20h20M6 20V10l6-7 6 7v10"/><path d="M10 20v-5h4v5"/></svg>
                            No projects found
                        </TableCell>
                    </TableRow>

                    <TableRow v-for="project in projects.data" :key="project.id">
                        <!-- Project name + location -->
                        <TableCell class="pl-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-16 flex-shrink-0 overflow-hidden rounded-lg bg-muted">
                                    <img v-if="project.cover" :src="project.cover" :alt="project.name" class="h-full w-full object-cover" />
                                    <div v-else class="flex h-full w-full items-center justify-center">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted-foreground"><path d="M2 20h20M6 20V10l6-7 6 7v10"/></svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-foreground">{{ project.name }}</p>
                                    <p v-if="project.location" class="mt-0.5 flex items-center gap-1 truncate text-xs text-muted-foreground">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                        {{ project.location }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell>
                            <Badge :variant="typeVariant[project.type] ?? 'outline'">
                                {{ project.type_label }}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            <Badge :variant="statusVariant[project.status] ?? 'secondary'">
                                {{ project.status_label }}
                            </Badge>
                        </TableCell>

                        <TableCell class="text-muted-foreground">
                            <span class="font-medium text-foreground">{{ project.units_count }}</span>
                            <span v-if="project.total_units"> / {{ project.total_units }}</span>
                        </TableCell>

                        <TableCell>
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-admin-accent transition-all"
                                        :style="{ width: `${project.overall_progress ?? 0}%` }"
                                    />
                                </div>
                                <span class="text-xs text-muted-foreground">{{ project.overall_progress ?? 0 }}%</span>
                            </div>
                        </TableCell>

                        <TableCell class="pr-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Link
                                    :href="route('admin.projects.edit', project.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-admin-accent"
                                    title="Edit"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </Link>
                                <button
                                    @click="confirmDelete(project)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                    title="Delete"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Pagination -->
            <div v-if="projects.last_page > 1" class="flex items-center justify-between border-t border-border px-4 py-3">
                <p class="text-xs text-muted-foreground">
                    Showing {{ projects.from }}–{{ projects.to }} of {{ projects.total }}
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in projects.links" :key="link.label">
                        <!-- Active page or disabled (no url) → plain span -->
                        <span
                            v-if="!link.url"
                            class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs pointer-events-none text-muted-foreground/40"
                        ><span v-html="link.label" /></span>

                        <!-- Navigable page → Inertia Link -->
                        <Link
                            v-else
                            :href="link.url"
                            :preserve-state="true"
                            :class="[
                                'inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs transition-colors',
                                link.active ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:bg-muted',
                            ]"
                        ><span v-html="link.label" /></Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirm Dialog -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="confirmingDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelDelete" />
                    <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-admin-surface-card p-6 shadow-xl">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 dark:text-red-400"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-foreground">Delete Project</h3>
                        <p class="mb-5 text-sm text-muted-foreground">
                            Are you sure you want to delete <strong class="text-foreground">{{ confirmingDelete.name }}</strong>? This cannot be undone.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button
                                @click="cancelDelete"
                                class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                            >
                                Cancel
                            </button>
                            <button
                                @click="submitDelete"
                                :disabled="deleteForm.processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-60"
                            >
                                <svg v-if="deleteForm.processing" class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
