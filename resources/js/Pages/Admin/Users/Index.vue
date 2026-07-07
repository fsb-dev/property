<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxInput, ComboboxItem, ComboboxList } from '@/Components/ui/combobox';

const props = defineProps({
    users:                      { type: Object, required: true },
    stats:                      { type: Object, required: true },
    enums:                      { type: Object, required: true },
    roleDistribution:           { type: Array,  required: true },
    accessSummary:              { type: Array,  required: true },
    rolesOverview:              { type: Array,  required: true },
    departmentBreakdown:        { type: Array,  required: true },
    recentActivity:             { type: Array,  required: true },
    permissionGroups:           { type: Array,  required: true },
    rolesWithPermissions:       { type: Array,  required: true },
    usersWithDirectPermissions: { type: Array,  required: true },
    filters:                    { type: Object, default: () => ({}) },
});

// ── Filters ────────────────────────────────────────────────────
const search   = ref(props.filters.search ?? '');
const role     = ref(props.filters.role ?? '');
const verified = ref(props.filters.verified ?? '');

let searchTimer;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});
watch(role, applyFilters);

function applyFilters() {
    router.get(route('admin.users.index'), {
        search:   search.value || undefined,
        role:     role.value || undefined,
        verified: verified.value || undefined,
    }, { preserveState: true, replace: true });
}

function setTab(tab) {
    verified.value = tab;
    applyFilters();
}

const tabs = computed(() => ([
    { key: '',           label: 'All Users',  count: props.stats.total },
    { key: 'verified',   label: 'Verified',   count: props.stats.verified },
    { key: 'unverified', label: 'Unverified', count: props.stats.unverified },
]));

// ── Delete ─────────────────────────────────────────────────────
const deleteForm       = useForm({});
const confirmingDelete = ref(null);

function confirmDelete(user) { confirmingDelete.value = user; }
function cancelDelete()      { confirmingDelete.value = null; }
function submitDelete() {
    deleteForm.delete(route('admin.users.destroy', confirmingDelete.value.id), {
        onSuccess: () => { confirmingDelete.value = null; },
    });
}

// ── Create New Role ────────────────────────────────────────────
const showCreateRole = ref(false);
const createRoleForm = useForm({ name: '', permissions: [] });

function submitCreateRole() {
    createRoleForm.post(route('admin.roles.store'), {
        onSuccess: () => { showCreateRole.value = false; createRoleForm.reset(); },
    });
}

// ── Role Permissions ───────────────────────────────────────────
const showRolePermissions = ref(false);
const rolePermissionsForm = useForm({ permissions: [] });
const selectedRoleForPermissions = ref(null);

function onSelectRoleForPermissions(roleValue) {
    const roleData = props.rolesWithPermissions.find(r => r.value === roleValue) ?? null;
    selectedRoleForPermissions.value = roleData;
    rolePermissionsForm.permissions = roleData ? [...roleData.permissions] : [];
}

function submitRolePermissions() {
    if (!selectedRoleForPermissions.value) return;
    rolePermissionsForm.put(route('admin.roles.permissions.update', selectedRoleForPermissions.value.id), {
        onSuccess: () => { showRolePermissions.value = false; selectedRoleForPermissions.value = null; },
    });
}

// ── Assign Permissions (per-user direct overrides) ─────────────
const showAssignPermissions = ref(false);
const assignPermissionsForm = useForm({ permissions: [] });
const userSearchTerm = ref('');
const selectedUserForPermissions = ref(null);

const filteredUsersForPermissions = computed(() => {
    const term = userSearchTerm.value.trim().toLowerCase();
    const list = !term
        ? props.usersWithDirectPermissions
        : props.usersWithDirectPermissions.filter(u =>
            u.name.toLowerCase().includes(term) || u.email.toLowerCase().includes(term)
        );
    return list.slice(0, 20);
});

function onSelectUserForPermissions(user) {
    selectedUserForPermissions.value = user;
    assignPermissionsForm.permissions = user ? [...user.permissions] : [];
}

function submitAssignPermissions() {
    if (!selectedUserForPermissions.value) return;
    assignPermissionsForm.put(route('admin.users.permissions.update', selectedUserForPermissions.value.id), {
        onSuccess: () => {
            showAssignPermissions.value = false;
            selectedUserForPermissions.value = null;
            userSearchTerm.value = '';
        },
    });
}

// ── Bulk User Import ────────────────────────────────────────────
const showBulkImport = ref(false);
const importForm = useForm({ file: null });

function onImportFileChange(e) {
    importForm.file = e.target.files[0] ?? null;
}

function submitImport() {
    importForm.post(route('admin.users.import'), {
        forceFormData: true,
        onSuccess: () => { showBulkImport.value = false; importForm.reset(); },
    });
}

// ── Activity Log dialog ─────────────────────────────────────────
const showActivityLog = ref(false);

// ── Shared: checkbox toggle helper ──────────────────────────────
function togglePermission(list, value) {
    const idx = list.indexOf(value);
    if (idx === -1) list.push(value); else list.splice(idx, 1);
}

// ── Helpers ────────────────────────────────────────────────────
const avatarInitials = (name) => name?.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() ?? '?';

function donutSegments(items, radius) {
    const circumference = 2 * Math.PI * radius;
    const total = items.reduce((sum, i) => sum + i.val, 0) || 1;
    let offset = 0;
    return items.map((item) => {
        const length = (item.val / total) * circumference;
        const segment = { ...item, dasharray: `${length} ${circumference}`, dashoffset: -offset };
        offset += length;
        return segment;
    });
}

const roleDonut   = computed(() => donutSegments(props.roleDistribution.map(r => ({ ...r, val: r.val })), 58));
const accessDonut = computed(() => donutSegments(props.accessSummary.map(a => ({ ...a, val: a.val })), 54));
</script>

<template>
    <Head title="Users & Roles" />

    <AdminLayout title="Users & Roles" :breadcrumbs="[{ label: 'Admin' }, { label: 'Users & Roles' }]">

        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Users &amp; Roles</h1>
                <p class="mt-0.5 text-sm text-muted-foreground">Manage user accounts, roles and access across the system.</p>
            </div>
            <Link
                :href="route('admin.users.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add User
            </Link>
        </div>

        <!-- KPIs -->
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-2xl border border-border bg-admin-surface-card p-5 transition-transform hover:-translate-y-0.5">
                <div class="mb-2.5 flex items-center gap-2.5">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10 text-admin-accent">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Total Users</p>
                </div>
                <p class="text-2xl font-bold text-foreground">{{ stats.total }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card p-5 transition-transform hover:-translate-y-0.5">
                <div class="mb-2.5 flex items-center gap-2.5">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Verified</p>
                </div>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.verified }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card p-5 transition-transform hover:-translate-y-0.5">
                <div class="mb-2.5 flex items-center gap-2.5">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/></svg>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Total Roles</p>
                </div>
                <p class="text-2xl font-bold text-foreground">{{ stats.total_roles }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card p-5 transition-transform hover:-translate-y-0.5">
                <div class="mb-2.5 flex items-center gap-2.5">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"/><path d="M10.8 12.2L19 4M16 7l3-3M14 9l2 2"/></svg>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Admins</p>
                </div>
                <p class="text-2xl font-bold text-foreground">{{ stats.admins }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card p-5 transition-transform hover:-translate-y-0.5">
                <div class="mb-2.5 flex items-center gap-2.5">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 9h1M9 13h1M14 9h1M14 13h1"/></svg>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Departments</p>
                </div>
                <p class="text-2xl font-bold text-foreground">{{ stats.departments }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-admin-surface-card p-5 transition-transform hover:-translate-y-0.5">
                <div class="mb-2.5 flex items-center gap-2.5">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-teal-100 text-teal-600 dark:bg-teal-500/15 dark:text-teal-400">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 7-7M14 8h6v6"/></svg>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">New This Month</p>
                </div>
                <p class="text-2xl font-bold text-foreground">{{ stats.new_this_month }}</p>
            </div>
        </div>

        <!-- Body grid -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-stretch">
            <div class="min-w-0 flex flex-col gap-6">

                <!-- Table card -->
                <div class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
                    <!-- Tabs -->
                    <div class="flex items-center gap-6 overflow-x-auto border-b border-border px-5 pt-4">
                        <button
                            v-for="t in tabs" :key="t.key"
                            @click="setTab(t.key)"
                            class="whitespace-nowrap border-b-2 pb-2.5 text-sm font-semibold transition-colors"
                            :class="verified === t.key ? 'border-admin-accent text-admin-accent' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        >
                            {{ t.label }} ({{ t.count }})
                        </button>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-wrap items-center gap-3 p-4">
                        <div class="relative min-w-[200px] flex-1">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <Input v-model="search" placeholder="Search name, email or phone..." class="pl-9" />
                        </div>
                        <Select :model-value="role || undefined" @update:model-value="role = $event ?? ''">
                            <SelectTrigger class="w-[170px]">
                                <SelectValue placeholder="All roles" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="r in enums.roles" :key="r.value" :value="r.value">{{ r.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <button v-if="role" @click="role = ''" class="inline-flex items-center gap-1 rounded-lg border border-border px-2.5 py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-500/30 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            Role
                        </button>
                    </div>

                    <div class="flex-1">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="pl-4">User</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Joined On</TableHead>
                                <TableHead class="pr-4 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="users.data.length === 0">
                                <TableCell colspan="7" class="py-16 text-center text-muted-foreground">
                                    <svg class="mx-auto mb-3 text-border" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    No users found
                                </TableCell>
                            </TableRow>

                            <TableRow v-for="user in users.data" :key="user.id" class="group">
                                <TableCell class="pl-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 flex-shrink-0 overflow-hidden rounded-full bg-admin-accent/10 ring-2 ring-white transition-shadow group-hover:ring-admin-accent/20 dark:ring-admin-surface-card">
                                            <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-xs font-semibold text-admin-accent">
                                                {{ avatarInitials(user.name) }}
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-foreground truncate">{{ user.name }}</p>
                                            <p class="mt-0.5 text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </TableCell>

                                <TableCell>
                                    <span
                                        class="inline-block rounded-md px-2.5 py-1 text-xs font-semibold"
                                        :style="{ background: user.role_color.bg, color: user.role_color.color }"
                                    >{{ user.role_label }}</span>
                                </TableCell>

                                <TableCell class="text-sm text-muted-foreground">{{ user.department }}</TableCell>

                                <TableCell class="text-sm text-muted-foreground">{{ user.phone ?? '—' }}</TableCell>

                                <TableCell>
                                    <Badge :variant="user.verified ? 'success' : 'warning'">
                                        {{ user.verified ? 'Verified' : 'Unverified' }}
                                    </Badge>
                                </TableCell>

                                <TableCell class="text-sm text-muted-foreground">{{ user.created_at }}</TableCell>

                                <TableCell class="pr-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.users.edit', user.id)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-admin-accent"
                                            title="Edit"
                                        >
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </Link>
                                        <button
                                            @click="confirmDelete(user)"
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
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.total > 0" class="flex items-center justify-between border-t border-border px-4 py-3">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ users.from }}–{{ users.to }} of {{ users.total }}
                        </p>
                        <div v-if="users.last_page > 1" class="flex items-center gap-1">
                            <template v-for="link in users.links" :key="link.label">
                                <span v-if="!link.url" class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs pointer-events-none text-muted-foreground/40"><span v-html="link.label" /></span>
                                <Link v-else :href="link.url" :preserve-state="true" :class="['inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-xs transition-colors', link.active ? 'bg-admin-accent text-white' : 'text-muted-foreground hover:bg-muted']"><span v-html="link.label" /></Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="min-w-0 flex flex-col gap-6">

                <!-- Role Distribution -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Role Distribution</div>
                    </div>
                    <div v-if="roleDistribution.length" class="flex flex-wrap items-center justify-center gap-4">
                        <div class="relative h-[150px] w-[150px] flex-shrink-0">
                            <svg width="150" height="150" viewBox="0 0 150 150" style="transform:rotate(-90deg)">
                                <circle cx="75" cy="75" r="58" fill="none" stroke="currentColor" class="text-muted" stroke-width="16" />
                                <circle
                                    v-for="seg in roleDonut" :key="seg.name"
                                    cx="75" cy="75" r="58" fill="none" :stroke="seg.color" stroke-width="16"
                                    :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-2xl font-extrabold text-foreground">{{ stats.total }}</div>
                                <div class="text-[10px] text-muted-foreground">Total Users</div>
                            </div>
                        </div>
                        <div class="flex min-w-[150px] flex-1 flex-col gap-2">
                            <div v-for="r in roleDistribution" :key="r.name" class="flex items-center justify-between gap-2 text-xs">
                                <span class="flex min-w-0 items-center gap-2 text-foreground">
                                    <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="{ background: r.color }" />
                                    <span class="truncate">{{ r.name }}</span>
                                </span>
                                <span class="flex-shrink-0"><b>{{ r.val }}</b> <span class="text-muted-foreground">({{ r.pct }})</span></span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-muted-foreground">No roles assigned yet.</p>
                </div>

                <!-- Recent Activity -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-5">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Recent Activity</div>
                        <button v-if="recentActivity.length" @click="showActivityLog = true" class="text-xs font-semibold text-admin-accent hover:underline">View All</button>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="(a, i) in recentActivity.slice(0, 6)" :key="i" class="flex items-start gap-3 border-b border-border py-2.5 last:border-none">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-admin-accent/10 text-admin-accent">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-bold text-foreground">{{ a.title }}</p>
                                <p class="truncate text-[11px] text-muted-foreground">{{ a.desc }}</p>
                            </div>
                            <div class="flex-shrink-0 whitespace-nowrap text-[11px] text-muted-foreground">{{ a.time }}</div>
                        </div>
                        <p v-if="recentActivity.length === 0" class="py-4 text-center text-sm text-muted-foreground">No activity yet.</p>
                    </div>
                </div>

                <!-- Access Summary -->
                <div class="rounded-2xl border border-border bg-admin-surface-card p-5">
                    <div class="mb-3 text-base font-bold text-foreground">Access Summary</div>
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <div class="relative h-[140px] w-[140px] flex-shrink-0">
                            <svg width="140" height="140" viewBox="0 0 140 140" style="transform:rotate(-90deg)">
                                <circle cx="70" cy="70" r="54" fill="none" stroke="currentColor" class="text-muted" stroke-width="16" />
                                <circle
                                    v-for="seg in accessDonut" :key="seg.name"
                                    cx="70" cy="70" r="54" fill="none" :stroke="seg.color" stroke-width="16"
                                    :stroke-dasharray="seg.dasharray" :stroke-dashoffset="seg.dashoffset"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                <div class="mt-0.5 text-[9.5px] text-muted-foreground">Access<br>Overview</div>
                            </div>
                        </div>
                        <div class="flex min-w-[140px] flex-1 flex-col gap-3">
                            <div v-for="a in accessSummary" :key="a.name" class="flex items-center justify-between gap-2 text-xs">
                                <span class="flex min-w-0 items-center gap-2 text-foreground">
                                    <span class="h-2 w-2 flex-shrink-0 rounded-full" :style="{ background: a.color }" />
                                    <span class="truncate">{{ a.name }}</span>
                                </span>
                                <span class="flex-shrink-0"><b>{{ a.val }}</b> <span class="text-muted-foreground">({{ a.pct }})</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-start gap-2 rounded-xl bg-admin-surface-page p-2.5 text-xs text-muted-foreground">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 flex-shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
                        <div>Total system permissions: <b class="text-foreground">{{ enums.roles.length }}</b> roles active</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom: Roles Overview + Department Users -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)] items-start">
            <div class="min-w-0 rounded-2xl border border-border bg-admin-surface-card p-5">
                <div class="mb-4 text-base font-bold text-foreground">Roles &amp; Permissions Overview</div>
                <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="r in rolesOverview" :key="r.name"
                        class="flex min-w-0 flex-col gap-2 rounded-2xl border border-border p-3.5 transition-all hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl" :style="{ background: r.bg, color: r.color }">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-foreground">{{ r.name }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ r.access }}</p>
                        </div>
                        <p class="text-xs font-semibold" :style="{ color: r.color }">{{ r.count }}</p>
                    </div>
                </div>
            </div>

            <div class="min-w-0 rounded-2xl border border-border bg-admin-surface-card p-5">
                <div class="mb-4 text-base font-bold text-foreground">Department Users</div>
                <div class="flex flex-col gap-3.5">
                    <div v-for="d in departmentBreakdown" :key="d.name" class="grid grid-cols-[130px_1fr_40px] items-center gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" :stroke="d.color" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>
                            <span class="truncate text-xs font-semibold text-foreground">{{ d.name }}</span>
                            <span class="flex-shrink-0 text-[11px] text-muted-foreground">{{ d.count }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full transition-all" :style="{ width: d.pct, background: d.color }" />
                        </div>
                        <div class="text-right text-xs font-bold text-foreground">{{ d.pct }}</div>
                    </div>
                    <p v-if="departmentBreakdown.length === 0" class="py-4 text-center text-sm text-muted-foreground">No departments assigned yet.</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 rounded-2xl border border-border bg-admin-surface-card p-5">
            <div class="mb-4 text-base font-bold text-foreground">Quick Actions</div>
            <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8">
                <Link :href="route('admin.users.create')" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-admin-accent hover:bg-admin-accent/5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-admin-accent/10 text-admin-accent">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">Add New User</span>
                </Link>
                <a :href="route('admin.users.export')" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">Export Users</span>
                </a>
                <button type="button" @click="showCreateRole = true" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-500/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/><path d="M12 9v6M9 12h6"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">Create New Role</span>
                </button>

                <button type="button" @click="showAssignPermissions = true; userSearchTerm = ''; selectedUserForPermissions = null; assignPermissionsForm.permissions = []" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"/><path d="M10.8 12.2L19 4M16 7l3-3M14 9l2 2"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">Assign Permissions</span>
                </button>

                <button type="button" @click="showBulkImport = true" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5-5 5 5M12 5v10"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">Bulk User Import</span>
                </button>

                <button type="button" @click="showActivityLog = true" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-teal-500 hover:bg-teal-50 dark:hover:bg-teal-500/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 text-teal-600 dark:bg-teal-500/15 dark:text-teal-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z"/><path d="M14 3v5h5M9 13l2 2 4-4"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">User Activity Logs</span>
                </button>

                <a :href="route('admin.users.access-report')" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">Access Reports</span>
                </a>

                <button type="button" @click="showRolePermissions = true; selectedRoleForPermissions = null; rolePermissionsForm.permissions = []" class="flex min-h-[70px] flex-col items-center justify-center gap-2 rounded-2xl border border-border p-3 text-center transition-all hover:-translate-y-0.5 hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-violet-500/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="15" r="4"/><path d="M9.8 12.2L19 3M16 6l3-3"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-foreground">Role Permissions</span>
                </button>
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
                        <h3 class="mb-1 font-semibold text-foreground">Remove User</h3>
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

        <!-- Create New Role Dialog -->
        <Dialog v-model:open="showCreateRole">
            <DialogContent class="flex max-h-[85vh] w-full max-w-lg flex-col gap-0 overflow-hidden p-0">
                <DialogHeader class="shrink-0 border-b border-border px-6 py-4">
                    <DialogTitle>Create New Role</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitCreateRole" class="flex min-h-0 flex-1 flex-col">
                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Role Name <span class="text-destructive">*</span></Label>
                            <Input v-model="createRoleForm.name" placeholder="e.g. marketing_lead" :class="createRoleForm.errors.name && 'border-destructive'" />
                            <p v-if="createRoleForm.errors.name" class="text-xs text-destructive">{{ createRoleForm.errors.name }}</p>
                        </div>

                        <div class="mt-5 space-y-3">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Permissions</Label>
                            <div v-for="group in permissionGroups" :key="group.group" class="rounded-xl border border-border p-3">
                                <p class="mb-2 text-xs font-bold text-foreground">{{ group.group }}</p>
                                <div class="flex flex-wrap gap-x-4 gap-y-2">
                                    <label v-for="p in group.permissions" :key="p.value" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <input
                                            type="checkbox"
                                            class="h-3.5 w-3.5 rounded border-border accent-admin-accent"
                                            :checked="createRoleForm.permissions.includes(p.value)"
                                            @change="togglePermission(createRoleForm.permissions, p.value)"
                                        />
                                        {{ p.label }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <DialogFooter class="shrink-0 border-t border-border px-6 py-4">
                        <button type="button" @click="showCreateRole = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Cancel</button>
                        <button type="submit" :disabled="createRoleForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60">Create Role</button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Role Permissions Dialog -->
        <Dialog v-model:open="showRolePermissions">
            <DialogContent class="flex max-h-[85vh] w-full max-w-lg flex-col gap-0 overflow-hidden p-0">
                <DialogHeader class="shrink-0 border-b border-border px-6 py-4">
                    <DialogTitle>Role Permissions</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitRolePermissions" class="flex min-h-0 flex-1 flex-col">
                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Select Role</Label>
                            <Select :model-value="selectedRoleForPermissions?.value" @update:model-value="onSelectRoleForPermissions">
                                <SelectTrigger><SelectValue placeholder="Choose a role" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="r in rolesWithPermissions" :key="r.value" :value="r.value">{{ r.label }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <template v-if="selectedRoleForPermissions">
                            <p v-if="selectedRoleForPermissions.value === 'super_admin'" class="mt-4 rounded-xl bg-admin-surface-page p-3 text-xs text-muted-foreground">
                                Super Admin always has every permission and bypasses this list.
                            </p>
                            <div v-else class="mt-5 space-y-3">
                                <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Permissions</Label>
                                <div v-for="group in permissionGroups" :key="group.group" class="rounded-xl border border-border p-3">
                                    <p class="mb-2 text-xs font-bold text-foreground">{{ group.group }}</p>
                                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                                        <label v-for="p in group.permissions" :key="p.value" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                            <input
                                                type="checkbox"
                                                class="h-3.5 w-3.5 rounded border-border accent-admin-accent"
                                                :checked="rolePermissionsForm.permissions.includes(p.value)"
                                                @change="togglePermission(rolePermissionsForm.permissions, p.value)"
                                            />
                                            {{ p.label }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <DialogFooter class="shrink-0 border-t border-border px-6 py-4">
                        <button type="button" @click="showRolePermissions = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Cancel</button>
                        <button
                            type="submit"
                            :disabled="!selectedRoleForPermissions || selectedRoleForPermissions.value === 'super_admin' || rolePermissionsForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                        >Save Permissions</button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Assign Permissions (per-user) Dialog -->
        <Dialog v-model:open="showAssignPermissions">
            <DialogContent class="flex max-h-[85vh] w-full max-w-lg flex-col gap-0 overflow-hidden p-0">
                <DialogHeader class="shrink-0 border-b border-border px-6 py-4">
                    <DialogTitle>Assign Direct Permissions</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitAssignPermissions" class="flex min-h-0 flex-1 flex-col">
                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Select User</Label>
                            <Combobox :model-value="selectedUserForPermissions" by="id" @update:model-value="onSelectUserForPermissions">
                                <ComboboxAnchor>
                                    <ComboboxInput v-model="userSearchTerm" :display-value="(u) => u?.name ?? ''" placeholder="Search by name or email" />
                                </ComboboxAnchor>
                                <ComboboxList>
                                    <ComboboxEmpty>No matching users.</ComboboxEmpty>
                                    <ComboboxItem v-for="u in filteredUsersForPermissions" :key="u.id" :value="u" :text-value="u.name">
                                        <p class="text-sm font-medium text-foreground">{{ u.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ u.email }}</p>
                                    </ComboboxItem>
                                </ComboboxList>
                            </Combobox>
                            <p class="text-xs text-muted-foreground">These are extra permissions on top of the user's role.</p>
                        </div>

                        <div v-if="selectedUserForPermissions" class="mt-5 space-y-3">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Permissions</Label>
                            <div v-for="group in permissionGroups" :key="group.group" class="rounded-xl border border-border p-3">
                                <p class="mb-2 text-xs font-bold text-foreground">{{ group.group }}</p>
                                <div class="flex flex-wrap gap-x-4 gap-y-2">
                                    <label v-for="p in group.permissions" :key="p.value" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <input
                                            type="checkbox"
                                            class="h-3.5 w-3.5 rounded border-border accent-admin-accent"
                                            :checked="assignPermissionsForm.permissions.includes(p.value)"
                                            @change="togglePermission(assignPermissionsForm.permissions, p.value)"
                                        />
                                        {{ p.label }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <DialogFooter class="shrink-0 border-t border-border px-6 py-4">
                        <button type="button" @click="showAssignPermissions = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Cancel</button>
                        <button
                            type="submit"
                            :disabled="!selectedUserForPermissions || assignPermissionsForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                        >Save Permissions</button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Bulk User Import Dialog -->
        <Dialog v-model:open="showBulkImport">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Bulk User Import</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitImport">
                    <p class="mb-3 text-xs text-muted-foreground">
                        Upload a CSV with columns <b class="text-foreground">name, email, phone, department, role</b>.
                        Rows with an existing email or an unknown role are skipped.
                    </p>
                    <input
                        type="file" accept=".csv,text/csv" @change="onImportFileChange"
                        class="block w-full cursor-pointer rounded-lg border border-border bg-slate-50 text-sm text-muted-foreground file:mr-3 file:rounded-md file:border-0 file:bg-admin-accent file:px-3 file:py-2 file:text-xs file:font-medium file:text-white dark:bg-white/[0.04]"
                    />
                    <p v-if="importForm.errors.file" class="mt-2 text-xs text-destructive">{{ importForm.errors.file }}</p>

                    <DialogFooter class="mt-5">
                        <button type="button" @click="showBulkImport = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Cancel</button>
                        <button type="submit" :disabled="!importForm.file || importForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60">
                            Import Users
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Activity Log Dialog -->
        <Dialog v-model:open="showActivityLog">
            <DialogContent class="flex max-h-[80vh] w-full max-w-md flex-col gap-0 overflow-hidden p-0">
                <DialogHeader class="shrink-0 border-b border-border px-6 py-4">
                    <DialogTitle>User Activity Logs</DialogTitle>
                </DialogHeader>
                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-2">
                    <div v-for="(a, i) in recentActivity" :key="i" class="flex items-start justify-between gap-3 border-b border-border py-3 last:border-none">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold text-foreground">{{ a.title }}</p>
                            <p class="truncate text-[11px] text-muted-foreground">{{ a.desc }}</p>
                        </div>
                        <div class="flex-shrink-0 whitespace-nowrap text-[11px] text-muted-foreground">{{ a.time }}</div>
                    </div>
                    <p v-if="recentActivity.length === 0" class="py-6 text-center text-sm text-muted-foreground">No activity yet.</p>
                </div>
                <DialogFooter class="shrink-0 border-t border-border px-6 py-4">
                    <button type="button" @click="showActivityLog = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">Close</button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
