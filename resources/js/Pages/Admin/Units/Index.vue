<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Input } from '@/Components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

const props = defineProps({
    units:    { type: Object, required: true },
    filters:  { type: Object, default: () => ({}) },
    stats:    { type: Object, required: true },
    enums:    { type: Object, required: true },
    projects: { type: Array,  required: true },
    sections: { type: Array,  required: true },
});

// ── Filters ────────────────────────────────────────────────────────────────
const search     = ref(props.filters.search     ?? '');
const project_id = ref(props.filters.project_id ?? '');
const block_id   = ref(props.filters.block_id   ?? '');
const type       = ref(props.filters.type       ?? '');
const status     = ref(props.filters.status     ?? '');

// Sections visible for the currently selected project
const filteredSections = computed(() => {
    if (!project_id.value) return [];
    return props.sections.filter(s => String(s.project_id) === String(project_id.value));
});

// Reset building when project changes
watch(project_id, () => { block_id.value = ''; });

let searchTimer;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});
watch([project_id, block_id, type, status], applyFilters);

function applyFilters() {
    router.get(route('admin.units.index'), {
        search:     search.value     || undefined,
        project_id: project_id.value || undefined,
        block_id:   block_id.value   || undefined,
        type:       type.value       || undefined,
        status:     status.value     || undefined,
    }, { preserveState: true, replace: true });
}

function clearAll() {
    search.value = project_id.value = block_id.value = type.value = status.value = '';
}

const hasFilters = computed(() =>
    search.value || project_id.value || block_id.value || type.value || status.value
);

// ── Delete ─────────────────────────────────────────────────────────────────
const deleteForm       = useForm({});
const confirmingDelete = ref(null);

function submitDelete() {
    deleteForm.delete(route('admin.units.destroy', confirmingDelete.value.id), {
        onSuccess: () => { confirmingDelete.value = null; },
    });
}

// ── Status styles ──────────────────────────────────────────────────────────
const STATUS_STYLE = {
    not_configured: 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
    configured:     'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
    available:      'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    booked:         'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
    sold:           'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300',
};

function statusClass(s) { return STATUS_STYLE[s] ?? 'bg-slate-100 text-slate-500'; }

function formatPrice(price) {
    if (!price) return '—';
    return '৳ ' + Number(price).toLocaleString('en-BD');
}
</script>

<template>
    <Head title="Units" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-foreground">Units</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">All units across every project</p>
            </div>
            <Link :href="route('admin.units.blueprint-select')"
                class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-semibold text-white hover:bg-admin-accent/90 transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Create Unit
            </Link>
        </div>

        <!-- Stats -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Total</p>
                <p class="mt-1 text-2xl font-bold text-foreground">{{ stats.total }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Available</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.available }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Booked</p>
                <p class="mt-1 text-2xl font-bold text-red-500 dark:text-red-400">{{ stats.booked }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card px-4 py-3">
                <p class="text-xs text-muted-foreground">Sold</p>
                <p class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.sold }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 rounded-xl border border-border bg-admin-surface-card p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">

                <!-- Search -->
                <div class="relative lg:col-span-1 xl:col-span-1">
                    <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Search</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                            width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <Input v-model="search" placeholder="Unit number…" class="pl-8 h-9" />
                    </div>
                </div>

                <!-- Project -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Project</label>
                    <Select :model-value="project_id || undefined" @update:model-value="project_id = $event ?? ''">
                        <SelectTrigger class="h-9 w-full">
                            <SelectValue placeholder="All Projects" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="p in projects" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Building/Section -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Section</label>
                    <Select :model-value="block_id || undefined" @update:model-value="block_id = $event ?? ''"
                        :disabled="!project_id">
                        <SelectTrigger class="h-9 w-full" :class="!project_id && 'opacity-50'">
                            <SelectValue :placeholder="project_id ? 'All Sections' : 'Select project first'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in filteredSections" :key="s.id" :value="String(s.id)">{{ s.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Type -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Type</label>
                    <Select :model-value="type || undefined" @update:model-value="type = $event ?? ''">
                        <SelectTrigger class="h-9 w-full">
                            <SelectValue placeholder="All Types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="t in enums.types" :key="t.value" :value="t.value">{{ t.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Status -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Status</label>
                    <Select :model-value="status || undefined" @update:model-value="status = $event ?? ''">
                        <SelectTrigger class="h-9 w-full">
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Active filter chips + clear -->
            <div v-if="hasFilters" class="mt-3 flex flex-wrap items-center gap-2">
                <span class="text-xs text-muted-foreground">Active:</span>
                <span v-if="search" class="inline-flex items-center gap-1 rounded-full bg-admin-accent/10 px-2.5 py-0.5 text-xs font-medium text-admin-accent">
                    "{{ search }}"
                    <button @click="search = ''" class="hover:text-admin-accent/70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </span>
                <span v-if="project_id" class="inline-flex items-center gap-1 rounded-full bg-admin-accent/10 px-2.5 py-0.5 text-xs font-medium text-admin-accent">
                    {{ projects.find(p => String(p.id) === project_id)?.name }}
                    <button @click="project_id = ''" class="hover:text-admin-accent/70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </span>
                <span v-if="block_id" class="inline-flex items-center gap-1 rounded-full bg-admin-accent/10 px-2.5 py-0.5 text-xs font-medium text-admin-accent">
                    {{ sections.find(s => String(s.id) === block_id)?.name }}
                    <button @click="block_id = ''" class="hover:text-admin-accent/70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </span>
                <span v-if="type" class="inline-flex items-center gap-1 rounded-full bg-admin-accent/10 px-2.5 py-0.5 text-xs font-medium text-admin-accent">
                    {{ enums.types.find(t => t.value === type)?.label }}
                    <button @click="type = ''" class="hover:text-admin-accent/70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </span>
                <span v-if="status" class="inline-flex items-center gap-1 rounded-full bg-admin-accent/10 px-2.5 py-0.5 text-xs font-medium text-admin-accent">
                    {{ enums.statuses.find(s => s.value === status)?.label }}
                    <button @click="status = ''" class="hover:text-admin-accent/70">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </span>
                <button @click="clearAll" class="ml-auto text-xs text-muted-foreground underline-offset-2 hover:underline">
                    Clear all
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse">
                    <thead>
                        <tr class="border-b border-border bg-slate-50/60 dark:bg-slate-800/30">
                            <th class="py-2.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Unit</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Project / Section</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Type</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Size</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Price</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">Status</th>
                            <th class="py-2.5 pl-3 pr-4 text-right text-xs font-semibold uppercase tracking-wide text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!units.data.length">
                            <td colspan="7" class="py-16 text-center">
                                <svg class="mx-auto mb-3 text-muted-foreground/20" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                    <rect x="3" y="8" width="8" height="13" rx="1.5"/><rect x="13" y="3" width="8" height="18" rx="1.5"/>
                                </svg>
                                <p class="text-sm font-medium text-foreground">No units found</p>
                                <p class="mt-0.5 text-xs text-muted-foreground">Try adjusting your filters or create units via the Blueprint.</p>
                            </td>
                        </tr>

                        <tr v-for="unit in units.data" :key="unit.id"
                            class="border-b border-border/60 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-800/30">

                            <!-- Unit number + floor -->
                            <td class="py-3 pl-4 pr-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-admin-accent/10 text-admin-accent">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-foreground">{{ unit.unit_number }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            <span v-if="unit.wing">{{ unit.wing }} · </span>
                                            <span v-if="unit.floor">Floor {{ unit.floor }}</span>
                                            <span v-if="!unit.wing && !unit.floor">—</span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Project / Section -->
                            <td class="px-3 py-3">
                                <p class="text-sm text-foreground">{{ unit.project_name }}</p>
                                <p v-if="unit.section_name" class="text-xs text-muted-foreground">{{ unit.section_name }}</p>
                            </td>

                            <!-- Type + bedrooms -->
                            <td class="px-3 py-3">
                                <p class="text-sm text-foreground">{{ unit.type_label ?? '—' }}</p>
                                <p v-if="unit.bedrooms" class="text-xs text-muted-foreground">{{ unit.bedrooms }} bed</p>
                            </td>

                            <!-- Size -->
                            <td class="px-3 py-3 text-sm text-muted-foreground">
                                {{ unit.size_sqft ? unit.size_sqft.toLocaleString() + ' sqft' : '—' }}
                            </td>

                            <!-- Price -->
                            <td class="px-3 py-3 text-sm font-medium text-foreground">
                                {{ formatPrice(unit.price) }}
                            </td>

                            <!-- Status badge -->
                            <td class="px-3 py-3">
                                <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold', statusClass(unit.status)]">
                                    {{ unit.status_label }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 pl-3 pr-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('admin.units.configure', unit.id)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-admin-accent/10 hover:text-admin-accent"
                                        title="Configure">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </Link>
                                    <button @click="confirmingDelete = unit"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                        title="Delete">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="units.last_page > 1" class="flex items-center justify-between border-t border-border px-4 py-3">
                <p class="text-xs text-muted-foreground">
                    Showing {{ units.from }}–{{ units.to }} of {{ units.total }} units
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in units.links" :key="link.label">
                        <span v-if="!link.url"
                            class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-lg px-2 text-xs text-muted-foreground/40"
                            v-html="link.label" />
                        <Link v-else :href="link.url" preserve-state
                            :class="['inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-lg px-2 text-xs transition-colors',
                                link.active ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:bg-muted']"
                            v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete confirm dialog -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0"
                leave-active-class="transition duration-100 ease-in" leave-to-class="opacity-0">
                <div v-if="confirmingDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="confirmingDelete = null" />
                    <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-admin-surface-card p-6 shadow-xl">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600 dark:text-red-400">
                                <path d="M3 6h18M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/>
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-foreground">Delete Unit</h3>
                        <p class="mb-5 text-sm text-muted-foreground">
                            Delete unit <strong class="text-foreground">{{ confirmingDelete.unit_number }}</strong>? This cannot be undone.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button @click="confirmingDelete = null"
                                class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground hover:bg-muted transition-colors">
                                Cancel
                            </button>
                            <button @click="submitDelete" :disabled="deleteForm.processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-60 transition-colors">
                                <svg v-if="deleteForm.processing" class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
