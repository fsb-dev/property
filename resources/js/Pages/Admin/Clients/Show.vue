<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Badge } from '@/Components/ui/badge';

const props = defineProps({
    client: { type: Object, required: true },
    enums:  { type: Object, required: true },
});

const deleteForm = useForm({});

function destroy() {
    if (!confirm(`Remove ${props.client.name}? This cannot be undone.`)) return;
    deleteForm.delete(route('admin.clients.destroy', props.client.id));
}

const statusVariant = { active: 'success', inactive: 'secondary', blacklisted: 'destructive' };
const avatarInitials = (name) => name?.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() ?? '?';

function fileIcon(mime) {
    if (mime?.includes('pdf')) return 'pdf';
    return 'image';
}

function fileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
}
</script>

<template>
    <Head :title="client.name" />

    <AdminLayout>
        <!-- Breadcrumb -->
        <nav class="mb-4 flex items-center gap-1.5 text-xs text-muted-foreground">
            <Link :href="route('admin.clients.index')" class="hover:text-admin-accent transition-colors">Clients</Link>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            <span class="text-foreground">{{ client.name }}</span>
        </nav>

        <!-- Profile Header Card -->
        <div class="mb-4 overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <!-- Avatar -->
                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-full bg-admin-accent/10 ring-2 ring-admin-accent/20">
                        <img v-if="client.avatar" :src="client.avatar" :alt="client.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-xl font-bold text-admin-accent">
                            {{ avatarInitials(client.name) }}
                        </div>
                    </div>
                    <!-- Name + meta -->
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-bold text-foreground">{{ client.name }}</h1>
                            <Badge :variant="statusVariant[client.status] ?? 'secondary'">{{ client.status_label }}</Badge>
                        </div>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ client.email }}</p>
                        <p v-if="client.phone" class="text-sm text-muted-foreground">{{ client.phone }}</p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex items-center gap-2 sm:flex-shrink-0">
                    <Link
                        :href="route('admin.clients.edit', client.id)"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-transparent px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </Link>
                    <button
                        @click="destroy"
                        :disabled="deleteForm.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 disabled:opacity-60"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Remove
                    </button>
                </div>
            </div>

            <!-- Meta strip -->
            <div class="flex flex-wrap gap-4 border-t border-border px-5 py-3">
                <div v-if="client.source_label" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Source: <span class="text-foreground">{{ client.source_label }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Registered: <span class="text-foreground">{{ client.created_at }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            <!-- Left column: Personal + KYC info -->
            <div class="flex flex-col gap-4 lg:col-span-2">

                <!-- Personal Details -->
                <div class="overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
                    <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                        <p class="text-sm font-semibold text-foreground">Personal Details</p>
                    </div>
                    <dl class="grid grid-cols-1 divide-y divide-border sm:grid-cols-2 sm:divide-y-0 sm:divide-x">
                        <div class="px-5 py-4 sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div v-if="client.gender">
                                <dt class="text-xs text-muted-foreground">Gender</dt>
                                <dd class="mt-0.5 text-sm font-medium text-foreground capitalize">{{ client.gender }}</dd>
                            </div>
                            <div v-if="client.date_of_birth_formatted">
                                <dt class="text-xs text-muted-foreground">Date of Birth</dt>
                                <dd class="mt-0.5 text-sm font-medium text-foreground">{{ client.date_of_birth_formatted }}</dd>
                            </div>
                            <div v-if="client.nationality">
                                <dt class="text-xs text-muted-foreground">Nationality</dt>
                                <dd class="mt-0.5 text-sm font-medium text-foreground">{{ client.nationality }}</dd>
                            </div>
                            <div v-if="client.occupation">
                                <dt class="text-xs text-muted-foreground">Occupation</dt>
                                <dd class="mt-0.5 text-sm font-medium text-foreground">{{ client.occupation }}</dd>
                            </div>
                            <div v-if="client.nid">
                                <dt class="text-xs text-muted-foreground">NID</dt>
                                <dd class="mt-0.5 text-sm font-medium text-foreground font-mono">{{ client.nid }}</dd>
                            </div>
                            <div v-if="client.passport_no">
                                <dt class="text-xs text-muted-foreground">Passport No.</dt>
                                <dd class="mt-0.5 text-sm font-medium text-foreground font-mono">{{ client.passport_no }}</dd>
                            </div>
                            <div v-if="client.address" class="sm:col-span-2">
                                <dt class="text-xs text-muted-foreground">Address</dt>
                                <dd class="mt-0.5 text-sm text-foreground">{{ client.address }}</dd>
                            </div>
                            <div v-if="!client.gender && !client.date_of_birth_formatted && !client.nid && !client.address" class="sm:col-span-2 text-sm text-muted-foreground">
                                No additional details recorded.
                            </div>
                        </div>
                    </dl>
                </div>

                <!-- KYC Documents -->
                <div class="overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
                    <div class="flex items-center justify-between border-b border-border px-5 py-4">
                        <p class="text-sm font-semibold text-foreground">KYC Documents</p>
                        <Link :href="route('admin.clients.edit', client.id)" class="text-xs text-admin-accent hover:underline">Manage</Link>
                    </div>
                    <div class="p-5">
                        <div v-if="client.kyc_documents?.length" class="flex flex-col gap-2">
                            <a
                                v-for="doc in client.kyc_documents"
                                :key="doc.id"
                                :href="doc.url"
                                target="_blank"
                                class="flex items-center gap-3 rounded-lg border border-border px-3 py-2.5 text-sm transition-colors hover:bg-muted"
                            >
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-admin-accent/10">
                                    <svg v-if="fileIcon(doc.mime) === 'pdf'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-admin-accent"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-admin-accent"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-foreground">{{ doc.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ fileSize(doc.size) }}</p>
                                </div>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-muted-foreground"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No KYC documents uploaded yet.</p>
                    </div>
                </div>

                <!-- Bookings placeholder -->
                <div class="overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
                    <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                        <p class="text-sm font-semibold text-foreground">Bookings</p>
                        <span class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">Coming soon</span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-2 py-10 text-center text-muted-foreground">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="text-border"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p class="text-sm">Booking history will appear here once the Sales module is live.</p>
                    </div>
                </div>

            </div>

            <!-- Right column: Notes + quick info -->
            <div class="flex flex-col gap-4">

                <!-- Contact quick-links -->
                <div class="overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
                    <div class="border-b border-border px-5 py-4">
                        <p class="text-sm font-semibold text-foreground">Contact</p>
                    </div>
                    <div class="flex flex-col divide-y divide-border">
                        <a :href="`mailto:${client.email}`" class="flex items-center gap-3 px-5 py-3 text-sm transition-colors hover:bg-muted">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-muted-foreground"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <span class="truncate text-admin-accent hover:underline">{{ client.email }}</span>
                        </a>
                        <a v-if="client.phone" :href="`tel:${client.phone}`" class="flex items-center gap-3 px-5 py-3 text-sm transition-colors hover:bg-muted">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-muted-foreground"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.78a16 16 0 0 0 6.29 6.29l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span class="text-foreground">{{ client.phone }}</span>
                        </a>
                    </div>
                </div>

                <!-- Internal Notes -->
                <div class="overflow-hidden rounded-2xl border border-border bg-admin-surface-card">
                    <div class="flex items-center justify-between border-b border-border px-5 py-4">
                        <p class="text-sm font-semibold text-foreground">Internal Notes</p>
                        <Link :href="route('admin.clients.edit', client.id)" class="text-xs text-admin-accent hover:underline">Edit</Link>
                    </div>
                    <div class="px-5 py-4">
                        <p v-if="client.notes" class="whitespace-pre-line text-sm text-foreground">{{ client.notes }}</p>
                        <p v-else class="text-sm text-muted-foreground">No notes added.</p>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>
