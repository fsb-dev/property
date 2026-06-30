<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Input }  from '@/Components/ui/input';
import { Badge }  from '@/Components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

const props = defineProps({
    clients: { type: Object, required: true },
    stats:   { type: Object, required: true },
    enums:   { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

// ── Filters ────────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const source = ref(props.filters.source ?? '');

let searchTimer;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});
watch([status, source], applyFilters);

function applyFilters() {
    router.get(route('admin.clients.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        source: source.value || undefined,
    }, { preserveState: true, replace: true });
}

// ── Delete ─────────────────────────────────────────────────────
const deleteForm       = useForm({});
const confirmingDelete = ref(null);

function confirmDelete(client) { confirmingDelete.value = client; }
function cancelDelete()        { confirmingDelete.value = null; }
function submitDelete() {
    deleteForm.delete(route('admin.clients.destroy', confirmingDelete.value.id), {
        onSuccess: () => { confirmingDelete.value = null; },
    });
}

// ── Helpers ────────────────────────────────────────────────────
const statusVariant = {
    active:      'success',
    inactive:    'secondary',
    blacklisted: 'destructive',
};

const avatarInitials = (name) => name?.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() ?? '?';
</script>

<template>
    <Head title="Clients" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-foreground">Clients</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Manage all registered clients</p>
            </div>
            <Link
                :href="route('admin.clients.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                New Client
            </Link>
        </div>

        <!-- Stats -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Total</p>
                <p class="mt-1 text-2xl font-bold text-foreground">{{ stats.total }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Active</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.active }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Inactive</p>
                <p class="mt-1 text-2xl font-bold text-muted-foreground">{{ stats.inactive }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Blacklisted</p>
                <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.blacklisted }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="relative min-w-[200px] flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <Input v-model="search" placeholder="Search name, email or phone..." class="pl-9" />
            </div>

            <Select :model-value="status || undefined" @update:model-value="status = $event ?? ''">
                <SelectTrigger class="w-[150px]">
                    <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}</SelectItem>
                </SelectContent>
            </Select>
            <button v-if="status" @click="status = ''" class="text-xs text-muted-foreground hover:text-foreground">✕ status</button>


        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="pl-4">Client</TableHead>
                        <TableHead>Phone</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Created </TableHead>
                        <TableHead class="pr-4 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="clients.data.length === 0">
                        <TableCell colspan="5" class="py-16 text-center text-muted-foreground">
                            <svg class="mx-auto mb-3 text-border" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            No clients found
                        </TableCell>
                    </TableRow>

                    <TableRow v-for="client in clients.data" :key="client.id" class="group">

                        <!-- Name + email -->
                        <TableCell class="pl-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 flex-shrink-0 overflow-hidden rounded-full bg-admin-accent/10">
                                    <img v-if="client.avatar" :src="client.avatar" :alt="client.name" class="h-full w-full object-cover" />
                                    <div v-else class="flex h-full w-full items-center justify-center text-xs font-semibold text-admin-accent">
                                        {{ avatarInitials(client.name) }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <Link :href="route('admin.clients.show', client.id)" class="font-medium text-foreground hover:text-admin-accent transition-colors">
                                        {{ client.name }}
                                    </Link>
                                    <p class="mt-0.5 text-xs text-muted-foreground truncate">{{ client.email }}</p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="text-sm text-muted-foreground">{{ client.phone ?? '—' }}</TableCell>

                        <TableCell>
                            <Badge :variant="statusVariant[client.status] ?? 'secondary'">
                                {{ client.status_label }}
                            </Badge>
                        </TableCell>

                        <TableCell class="text-sm text-muted-foreground">{{ client.created_at }}</TableCell>

                        <!-- Actions -->
                        <TableCell class="pr-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Link
                                    :href="route('admin.clients.show', client.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-admin-accent"
                                    title="View"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </Link>
                                <Link
                                    :href="route('admin.clients.edit', client.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-admin-accent"
                                    title="Edit"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </Link>
                                <button
                                    @click="confirmDelete(client)"
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
            <div v-if="clients.last_page > 1" class="flex items-center justify-between border-t border-border px-4 py-3">
                <p class="text-xs text-muted-foreground">
                    Showing {{ clients.from }}–{{ clients.to }} of {{ clients.total }}
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in clients.links" :key="link.label">
                        <span v-if="!link.url" class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs pointer-events-none text-muted-foreground/40"><span v-html="link.label" /></span>
                        <Link v-else :href="link.url" :preserve-state="true" :class="['inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs transition-colors', link.active ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:bg-muted']"><span v-html="link.label" /></Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Dialog -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="confirmingDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancelDelete" />
                    <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-admin-surface-card p-6 shadow-xl">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600 dark:text-red-400"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-foreground">Remove Client</h3>
                        <p class="mb-5 text-sm text-muted-foreground">
                            Are you sure you want to remove <strong class="text-foreground">{{ confirmingDelete.name }}</strong>? This cannot be undone.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button @click="cancelDelete" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Cancel</button>
                            <button @click="submitDelete" :disabled="deleteForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-60">
                                <svg v-if="deleteForm.processing" class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                Remove
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
