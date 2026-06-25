<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';

const page = usePage();
const user = computed(() => page.props.auth.user);
const profileOpen = ref(false);
const profileRef  = ref(null);
onClickOutside(profileRef, () => { profileOpen.value = false; });

const navLinks = [
    {
        label: 'Dashboard', routeName: 'admin.dashboard',
        href: () => route('admin.dashboard'),
        icon: `<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>`,
    },
    {
        label: 'Projects', routeName: 'admin.projects.index', activePattern: 'admin.projects.*',
        href: () => route('admin.projects.index'),
        icon: `<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-5h6v5"/>`,
    },
    {
        label: 'Units', routeName: 'admin.units.index', activePattern: 'admin.units.*',
        href: () => route('admin.units.index'),
        icon: `<rect x="3" y="8" width="8" height="13" rx="1.5"/><rect x="13" y="3" width="8" height="18" rx="1.5"/>`,
    },
    {
        label: 'Clients', routeName: 'admin.clients.index', activePattern: 'admin.clients.*',
        href: () => route('admin.clients.index'),
        icon: `<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M19 8v6M22 11h-6"/>`,
    },
    {
        label: 'Bookings', routeName: null,
        icon: `<rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/><path d="m9 14 2 2 4-4"/>`,
    },
    {
        label: 'Payments', routeName: null,
        icon: `<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 9.5h19"/>`,
    },
    {
        label: 'Construction', routeName: null,
        icon: `<path d="M2 20h20"/><path d="M6 20V10l6-7 6 7v10"/><path d="M10 20v-5h4v5"/>`,
    },
    {
        label: 'Documents', routeName: null,
        icon: `<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6"/>`,
    },
];

const profileMenuItems = [
    { label: 'View Profile',      icon: `<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>` },
    { label: 'Account Settings',  icon: `<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>` },
];

function isActive(link) {
    try { return route().current(link.activePattern ?? link.routeName); } catch { return false; }
}

function logout() {
    profileOpen.value = false;
    router.post(route('logout'));
}

function initials(name) {
    if (!name) return 'A';
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
}
</script>

<template>
    <aside class="
        flex flex-col h-full
        bg-admin-surface-sidebar
        border-r border-slate-200 dark:border-white/[0.06]
        transition-colors duration-200
    " style="width:260px; padding:20px 14px;">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-2 mb-7">
            <div class="flex items-end gap-1" style="height:30px;">
                <div class="rounded-sm bg-admin-accent" style="width:5px;height:16px;border-radius:3px;"></div>
                <div style="width:5px;height:30px;border-radius:3px;background:linear-gradient(180deg,rgb(var(--admin-accent)/0.7),rgb(var(--admin-accent)));"></div>
                <div class="bg-admin-accent/70" style="width:5px;height:22px;border-radius:3px;"></div>
            </div>
            <div>
                <div class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">Property</div>
                <div class="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-600" style="letter-spacing:0.3em;">ADMIN</div>
            </div>
        </div>

        <!-- Section label -->
        <div class="px-3 mb-2 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-600">
            Main Menu
        </div>

        <!-- Nav -->
        <nav class="flex flex-col gap-0.5 flex-1 overflow-y-auto min-h-0" style="scrollbar-width:none;">
            <template v-for="link in navLinks" :key="link.label">

                <!-- Has a real route → Inertia Link -->
                <Link
                    v-if="link.routeName"
                    :href="link.href()"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150"
                    :class="isActive(link)
                        ? 'bg-admin-accent/10 text-admin-accent font-semibold'
                        : 'text-slate-500 dark:text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-white/[0.05] dark:hover:text-slate-300'"
                >
                    <span
                        class="flex h-7 w-7 flex-none items-center justify-center rounded-lg transition-colors"
                        :class="isActive(link) ? 'bg-admin-accent/15' : 'bg-slate-100 dark:bg-white/[0.04]'"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="link.icon" />
                    </span>
                    {{ link.label }}
                    <span v-if="isActive(link)" class="ml-auto h-1.5 w-1.5 rounded-full bg-admin-accent" />
                </Link>

                <!-- No route yet → plain div, no navigation -->
                <div
                    v-else
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium select-none opacity-40 cursor-default"
                >
                    <span class="flex h-7 w-7 flex-none items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.04]">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="link.icon" />
                    </span>
                    <span class="text-slate-400 dark:text-slate-600">{{ link.label }}</span>
                </div>

            </template>
        </nav>

        <!-- Divider -->
        <div class="my-3 h-px bg-slate-200 dark:bg-white/[0.06]" />

        <!-- Profile block -->
        <div class="relative" ref="profileRef">
            <!-- Dropdown (opens upward) -->
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="profileOpen" class="absolute bottom-full mb-2 left-0 right-0 rounded-xl border py-1.5 z-50 bg-admin-surface-card border-slate-200 dark:border-white/[0.08] shadow-xl dark:shadow-black/40">
                    <div class="px-3 py-2 border-b border-slate-100 dark:border-white/[0.06] mb-1">
                        <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ user?.name ?? 'Admin' }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-500 truncate">{{ user?.email ?? '' }}</div>
                    </div>

                    <button
                        v-for="item in profileMenuItems"
                        :key="item.label"
                        class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-slate-600 dark:text-slate-400 transition-colors hover:bg-slate-50 dark:hover:bg-white/[0.05]"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="item.icon" />
                        {{ item.label }}
                    </button>

                    <div class="mx-3 my-1 h-px bg-slate-100 dark:bg-white/[0.06]" />

                    <button
                        class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-500/10"
                        @click="logout"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Sign Out
                    </button>
                </div>
            </Transition>

            <!-- Profile trigger -->
            <button
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition-colors hover:bg-slate-100 dark:hover:bg-white/[0.05]"
                @click="profileOpen = !profileOpen"
            >
                <div class="flex h-8 w-8 flex-none items-center justify-center rounded-lg text-xs font-bold text-white"
                    style="background:linear-gradient(135deg,rgb(var(--admin-accent)/0.8),rgb(var(--admin-accent)));">
                    {{ initials(user?.name) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ user?.name ?? 'Admin' }}</div>
                    <div class="text-xs text-slate-400 dark:text-slate-600 truncate">{{ user?.role ?? 'company_admin' }}</div>
                </div>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="flex-none text-slate-400 dark:text-slate-600 transition-transform duration-200"
                    :class="profileOpen ? 'rotate-180' : ''">
                    <path d="M18 15l-6-6-6 6"/>
                </svg>
            </button>
        </div>
    </aside>
</template>
