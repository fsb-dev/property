<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Badge } from '@/Components/ui/badge/index.js';
import { Separator } from '@/Components/ui/separator/index.js';

const props = defineProps({
    profileUser: { type: Object, required: true },
});

function initials(name) {
    if (!name) return 'A';
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
}

const roleLabel = computed(() => {
    const r = props.profileUser.role ?? 'user';
    return String(r).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
});

const roleStyle = computed(() => {
    const map = {
        super_admin:   { bg: '#F1ECFF', color: '#5B3DF5' },
        company_admin: { bg: '#E8F0FF', color: '#3B82F6' },
        sales_manager: { bg: '#FFF3E0', color: '#F59E0B' },
        accountant:    { bg: '#E6F7EE', color: '#22C55E' },
        site_engineer: { bg: '#E0F2FE', color: '#0EA5E9' },
    };
    return map[props.profileUser.role] ?? { bg: '#EEF1F6', color: '#64748B' };
});

const joinedDate = computed(() => {
    if (!props.profileUser.created_at) return '—';
    return new Date(props.profileUser.created_at).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
    });
});

const memberDays = computed(() => {
    if (!props.profileUser.created_at) return 0;
    return Math.floor((Date.now() - new Date(props.profileUser.created_at)) / 86400000);
});

const lastUpdated = computed(() => {
    if (!props.profileUser.updated_at) return '—';
    return new Date(props.profileUser.updated_at).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
    });
});

const permGroupColors = ['#5B3DF5','#3B82F6','#22C55E','#F59E0B','#0EA5E9','#EC4899','#0D9488','#64748B'];
</script>

<template>
    <Head title="My Profile" />
    <AdminLayout title="My Profile" :breadcrumbs="[{ label: 'Profile' }]">

        <div class="mx-auto max-w-6xl space-y-6">

            <!-- ── HERO CARD ─────────────────────────────────────────── -->
            <div class="rounded-2xl border border-border bg-white shadow-card overflow-hidden">

                <!-- Banner with mesh pattern -->
                <div class="relative h-36 w-full overflow-hidden"
                    style="background: linear-gradient(135deg,#0A1B36 0%,#5B3DF5 55%,#7C5CFF 80%,#3B82F6 100%);">
                    <!-- decorative circles -->
                    <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full opacity-10" style="background:#fff;" />
                    <div class="absolute right-24 top-6 h-24 w-24 rounded-full opacity-10" style="background:#fff;" />
                    <div class="absolute left-1/3 -bottom-6 h-32 w-32 rounded-full opacity-5" style="background:#fff;" />
                    <!-- grid lines -->
                    <svg class="absolute inset-0 h-full w-full opacity-10" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M 32 0 L 0 0 0 32" fill="none" stroke="white" stroke-width="0.5"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>

                <div class="px-8 pb-7">
                    <!-- Avatar row -->
                    <div class="flex flex-wrap items-end justify-between gap-4 -mt-14 mb-5">
                        <!-- Avatar -->
                        <div class="relative">
                            <div v-if="profileUser.avatar_url"
                                class="h-28 w-28 rounded-2xl border-4 border-white shadow-xl overflow-hidden">
                                <img :src="profileUser.avatar_url" :alt="profileUser.name" class="h-full w-full object-cover" />
                            </div>
                            <div v-else
                                class="flex h-28 w-28 items-center justify-center rounded-2xl border-4 border-white shadow-xl text-3xl font-black text-white select-none"
                                style="background: linear-gradient(145deg,#5B3DF5,#7C5CFF);">
                                {{ initials(profileUser.name) }}
                            </div>
                            <!-- verified dot -->
                            <div v-if="profileUser.email_verified_at"
                                class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white"
                                style="background:#22C55E;" title="Email Verified">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 pb-1">
                            <Link :href="route('admin.users.edit', profileUser.id)"
                                class="flex items-center gap-2 rounded-xl border border-border bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:shadow">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit Profile
                            </Link>
                        </div>
                    </div>

                    <!-- Name + badges -->
                    <div class="flex flex-wrap items-center gap-3 mb-1">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ profileUser.name }}</h1>
                        <span class="rounded-full px-3 py-0.5 text-xs font-bold"
                            :style="`background:${roleStyle.bg}; color:${roleStyle.color};`">
                            {{ roleLabel }}
                        </span>
                        <span v-if="profileUser.email_verified_at"
                            class="flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            style="background:#E6F7EE; color:#16A34A;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Verified
                        </span>
                        <span v-else class="rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background:#FEF3C7; color:#D97706;">
                            Unverified
                        </span>
                    </div>
                    <p class="text-sm text-slate-500">{{ profileUser.email }}</p>

                    <!-- Quick meta strip -->
                    <div class="mt-4 flex flex-wrap gap-5">
                        <div v-if="profileUser.department" class="flex items-center gap-1.5 text-sm text-slate-500">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            <span class="font-medium text-slate-700">{{ profileUser.department }}</span>
                        </div>
                        <div v-if="profileUser.phone" class="flex items-center gap-1.5 text-sm text-slate-500">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span class="font-medium text-slate-700">{{ profileUser.phone }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-sm text-slate-500">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Joined <span class="font-medium text-slate-700 ml-1">{{ joinedDate }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── STATS ROW ──────────────────────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-border bg-white px-5 py-4 shadow-card text-center">
                    <div class="text-3xl font-black text-slate-900" style="letter-spacing:-1px;">{{ memberDays }}</div>
                    <div class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Days Member</div>
                </div>
                <div class="rounded-2xl border border-border bg-white px-5 py-4 shadow-card text-center">
                    <div class="text-3xl font-black" style="letter-spacing:-1px; color:#5B3DF5;">{{ profileUser.permissions_count }}</div>
                    <div class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Permissions</div>
                </div>
                <div class="rounded-2xl border border-border bg-white px-5 py-4 shadow-card text-center">
                    <div class="text-3xl font-black" style="letter-spacing:-1px; color:#22C55E;">{{ profileUser.activity_count }}</div>
                    <div class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Actions Logged</div>
                </div>
                <div class="rounded-2xl border border-border bg-white px-5 py-4 shadow-card text-center">
                    <div class="text-3xl font-black" style="letter-spacing:-1px; color:#3B82F6;">{{ profileUser.permission_groups?.length ?? 0 }}</div>
                    <div class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Access Areas</div>
                </div>
            </div>

            <!-- ── TWO-COLUMN BODY ────────────────────────────────────── -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                <!-- LEFT: Account details + Permissions -->
                <div class="space-y-6 lg:col-span-1">

                    <!-- Account Details -->
                    <div class="rounded-2xl border border-border bg-white shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-border flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg" style="background:#F1ECFF; color:#5B3DF5;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            </div>
                            <h2 class="text-sm font-bold text-slate-800">Account Details</h2>
                        </div>
                        <div class="divide-y divide-border">
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Full Name</span>
                                <span class="text-sm font-semibold text-slate-800">{{ profileUser.name }}</span>
                            </div>
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</span>
                                <span class="text-sm font-medium text-slate-700 truncate max-w-[160px]">{{ profileUser.email }}</span>
                            </div>
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phone</span>
                                <span class="text-sm font-medium text-slate-700">{{ profileUser.phone || '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Department</span>
                                <span class="text-sm font-medium text-slate-700">{{ profileUser.department || '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Role</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :style="`background:${roleStyle.bg}; color:${roleStyle.color};`">
                                    {{ roleLabel }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</span>
                                <span v-if="profileUser.email_verified_at"
                                    class="flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    style="background:#E6F7EE; color:#16A34A;">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500 inline-block" />
                                    Active
                                </span>
                                <span v-else class="flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    style="background:#FEF3C7; color:#D97706;">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 inline-block" />
                                    Pending
                                </span>
                            </div>
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Member Since</span>
                                <span class="text-sm font-medium text-slate-700">{{ joinedDate }}</span>
                            </div>
                            <div class="flex items-center justify-between px-6 py-3.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Last Updated</span>
                                <span class="text-sm font-medium text-slate-700">{{ lastUpdated }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Permission Groups -->
                    <div v-if="profileUser.permission_groups?.length" class="rounded-2xl border border-border bg-white shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-border flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg" style="background:#E8F0FF; color:#3B82F6;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <h2 class="text-sm font-bold text-slate-800">Access Permissions</h2>
                            <span class="ml-auto rounded-full px-2 py-0.5 text-xs font-bold" style="background:#F1ECFF; color:#5B3DF5;">
                                {{ profileUser.permissions_count }}
                            </span>
                        </div>
                        <div class="p-5 space-y-3">
                            <div v-for="(group, i) in profileUser.permission_groups" :key="group.group"
                                class="rounded-xl border border-border p-3.5">
                                <div class="flex items-center gap-2 mb-2.5">
                                    <div class="h-2 w-2 rounded-full flex-none" :style="`background:${permGroupColors[i % permGroupColors.length]}`" />
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">{{ group.group }}</span>
                                    <span class="ml-auto text-xs text-slate-400">{{ group.items.length }}</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-for="item in group.items" :key="item"
                                        class="rounded-md px-2 py-0.5 text-xs font-medium"
                                        :style="`background:${permGroupColors[i % permGroupColors.length]}18; color:${permGroupColors[i % permGroupColors.length]};`">
                                        {{ item }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No permissions fallback -->
                    <div v-else class="rounded-2xl border border-border bg-white shadow-card px-6 py-8 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full" style="background:#F1F5F9;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">No permissions assigned</p>
                    </div>
                </div>

                <!-- RIGHT: Activity Feed -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Recent Activity -->
                    <div class="rounded-2xl border border-border bg-white shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-border flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg" style="background:#E6F7EE; color:#22C55E;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                            <h2 class="text-sm font-bold text-slate-800">Recent Activity</h2>
                            <span class="ml-auto text-xs text-slate-400">Last 8 actions</span>
                        </div>

                        <!-- Has activity -->
                        <div v-if="profileUser.recent_activity?.length" class="divide-y divide-border">
                            <div v-for="(log, i) in profileUser.recent_activity" :key="i"
                                class="flex items-start gap-4 px-6 py-4 hover:bg-slate-50/60 transition-colors">
                                <!-- Timeline dot -->
                                <div class="relative flex flex-col items-center pt-0.5">
                                    <div class="h-2.5 w-2.5 rounded-full border-2 flex-none"
                                        style="border-color:#5B3DF5; background:#F1ECFF;" />
                                    <div v-if="i < profileUser.recent_activity.length - 1"
                                        class="mt-1 w-px flex-1 min-h-[28px]" style="background:#E2E8F0;" />
                                </div>
                                <div class="flex-1 min-w-0 pb-1">
                                    <p class="text-sm font-medium text-slate-800 leading-snug">{{ log.description }}</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-xs text-slate-400">{{ log.time }}</span>
                                        <span class="text-slate-200">·</span>
                                        <span class="text-xs text-slate-400">{{ log.date }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full" style="background:#F8FAFC;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-500">No activity yet</p>
                            <p class="text-xs text-slate-400 mt-1">Actions you take will appear here</p>
                        </div>
                    </div>

                    <!-- Profile Completeness -->
                    <div class="rounded-2xl border border-border bg-white shadow-card px-6 py-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg" style="background:#FFF3E0; color:#F59E0B;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <h2 class="text-sm font-bold text-slate-800">Profile Completeness</h2>
                            </div>
                            <span class="text-sm font-black" style="color:#5B3DF5;">
                                {{ [profileUser.name, profileUser.email, profileUser.phone, profileUser.department, profileUser.avatar_url].filter(Boolean).length * 20 }}%
                            </span>
                        </div>

                        <!-- Progress bar -->
                        <div class="h-2.5 w-full rounded-full overflow-hidden" style="background:#F1F5F9;">
                            <div class="h-full rounded-full transition-all duration-700"
                                style="background: linear-gradient(90deg,#5B3DF5,#7C5CFF);"
                                :style="`width:${[profileUser.name, profileUser.email, profileUser.phone, profileUser.department, profileUser.avatar_url].filter(Boolean).length * 20}%`" />
                        </div>

                        <!-- Checklist -->
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div v-for="item in [
                                { label: 'Full name set',       done: !!profileUser.name },
                                { label: 'Email address',       done: !!profileUser.email },
                                { label: 'Phone number',        done: !!profileUser.phone },
                                { label: 'Department assigned', done: !!profileUser.department },
                                { label: 'Profile photo',       done: !!profileUser.avatar_url },
                            ]" :key="item.label"
                                class="flex items-center gap-2.5 rounded-xl px-3 py-2.5"
                                :style="item.done ? 'background:#F0FDF4;' : 'background:#F8FAFC;'">
                                <div class="flex h-5 w-5 flex-none items-center justify-center rounded-full"
                                    :style="item.done ? 'background:#22C55E;' : 'background:#E2E8F0;'">
                                    <svg v-if="item.done" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <svg v-else width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </div>
                                <span class="text-xs font-medium" :class="item.done ? 'text-green-700' : 'text-slate-500'">
                                    {{ item.label }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AdminLayout>
</template>
