<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';

const page = usePage();
const user = computed(() => page.props.auth.user);
const profileOpen = ref(false);
const profileRef = ref(null);
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
        label: 'Units', routeName: 'admin.units.blueprint-select', activePattern: 'admin.{units.*,blueprint.*}',
        href: () => route('admin.units.blueprint-select'),
        icon: `<rect x="3" y="8" width="8" height="13" rx="1.5"/><rect x="13" y="3" width="8" height="18" rx="1.5"/>`,
    },
    {
        label: 'Clients', routeName: 'admin.clients.index', activePattern: 'admin.clients.*',
        href: () => route('admin.clients.index'),
        icon: `<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M19 8v6M22 11h-6"/>`,
    },
    {
        label: 'Bookings', routeName: 'admin.bookings.index', activePattern: 'admin.bookings.*',
        href: () => route('admin.bookings.index'),
        icon: `<rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/><path d="m9 14 2 2 4-4"/>`,
    },
    {
        label: 'Investment', routeName: 'admin.investment.index', activePattern: 'admin.investment.*',
        href: () => route('admin.investment.index'),
        icon: `<path d="M3 17l6-6 4 4 7-7"/><path d="M14 8h6v6"/>`,
    },
    {
        label: 'Payments', routeName: null,
        label: 'Payments', routeName: 'admin.payments.index', activePattern: 'admin.payments.*',
        href: () => route('admin.payments.index'),
        icon: `<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 9.5h19"/>`,
    },
    {
        label: 'Construction', routeName: 'admin.construction.index', activePattern: 'admin.construction.*',
        href: () => route('admin.construction.index'),
        icon: `<path d="M2 20h20"/><path d="M6 20V10l6-7 6 7v10"/><path d="M10 20v-5h4v5"/>`,
    },
    {
        label: 'Community', routeName: 'admin.community.index', activePattern: 'admin.community.*',
        href: () => route('admin.community.index'),
        icon: `<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18z"/>`,
    },
    {
        label: 'Documents', routeName: null,
        icon: `<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6"/>`,
    },
    {
        label: 'Support Tickets', routeName: 'admin.support-tickets.index', activePattern: 'admin.support-tickets.*',
        href: () => route('admin.support-tickets.index'),
        icon: `<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="M4.9 4.9l3.5 3.5M15.6 15.6l3.5 3.5M19.1 4.9l-3.5 3.5M8.4 15.6l-3.5 3.5"/>`,
    },
    {
        label: 'Sera AI Knowledge', routeName: 'admin.ai-agent.index', activePattern: 'admin.ai-agent.*',
        href: () => route('admin.ai-agent.index'),
        icon: `<path d="M12 2l1.8 6.2L20 10l-6.2 1.8L12 18l-1.8-6.2L4 10l6.2-1.8z"/>`,
    },
    {
        label: 'Analytics & Reports', routeName: 'admin.analysis.index', activePattern: 'admin.analysis.*',
        href: () => route('admin.analysis.index'),
        icon: `<path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/>`,
    },
];

const profileMenuItems = [
    { label: 'View Profile', icon: `<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>` },
    { label: 'Account Settings', icon: `<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>` },
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
    <aside class="flex flex-col h-full"
        style="width:260px; padding:22px 16px 18px; background:linear-gradient(178deg,#0A1B36 0%,#07162D 60%,#061224 100%); color:#fff;">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-2 mb-6">
            <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl"
                style="background:linear-gradient(145deg,#5B3DF5,#7C5CFF); box-shadow:0 6px 18px rgba(91,61,245,0.45);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l9-7 9 7" />
                    <path d="M5 10v9h5v-5h4v5h5v-9" />
                </svg>
            </div>
            <div style="line-height:1.1;">
                <div style="font-size:19px; font-weight:800; letter-spacing:-0.3px;">Property</div>
                <div style="font-size:10px; font-weight:600; letter-spacing:2px; color:#6E7C95;">ADMIN PANEL</div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex flex-col gap-1 flex-1 overflow-y-auto min-h-0" style="scrollbar-width:none;">
            <template v-for="link in navLinks" :key="link.label">

                <!-- Active / routable link -->
                <Link v-if="link.routeName" :href="link.href()"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all duration-150"
                    :style="isActive(link)
                        ? 'background:linear-gradient(135deg,#5B3DF5,#7C5CFF); color:#fff; font-weight:600; box-shadow:0 8px 20px rgba(91,61,245,0.4);'
                        : 'color:#9AA5BC;'" :class="!isActive(link) && 'hover:bg-white/[0.06] hover:text-white'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" v-html="link.icon" />
                    {{ link.label }}
                </Link>

                <!-- Coming-soon placeholder -->
                <div v-else class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm select-none cursor-default"
                    style="color:#6E7C95;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" v-html="link.icon" />
                    {{ link.label }}
                </div>

            </template>
        </nav>

        <!-- Divider -->
        <div class="my-3 h-px" style="background:rgba(255,255,255,0.07);" />

        <!-- Profile block -->
        <div class="relative" ref="profileRef">
            <!-- Dropdown (always white card, renders above sidebar) -->
            <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="profileOpen"
                    class="absolute bottom-full mb-2 left-0 right-0 rounded-xl border border-slate-200 py-1.5 z-50 bg-white shadow-xl">
                    <div class="px-3 py-2 border-b border-slate-100 mb-1">
                        <div class="text-sm font-bold text-slate-900 truncate">{{ user?.name ?? 'Admin' }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ user?.email ?? '' }}</div>
                    </div>

                    <button v-for="item in profileMenuItems" :key="item.label"
                        class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-slate-600 transition-colors hover:bg-slate-50">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="item.icon" />
                        {{ item.label }}
                    </button>

                    <div class="mx-3 my-1 h-px bg-slate-100" />

                    <button
                        class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-red-500 transition-colors hover:bg-red-50"
                        @click="logout">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        Sign Out
                    </button>
                </div>
            </Transition>

            <!-- Profile trigger -->
            <button
                class="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left transition-colors hover:bg-white/[0.05]"
                @click="profileOpen = !profileOpen">
                <div class="flex h-10 w-10 flex-none items-center justify-center rounded-full text-sm font-bold text-white"
                    style="background:linear-gradient(135deg,#3B4C6B,#1F2C45);">
                    {{ initials(user?.name) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-white truncate">{{ user?.name ?? 'Admin' }}</div>
                    <div class="text-xs truncate" style="color:#6E7C95;">{{ user?.role ?? 'super_admin' }}</div>
                </div>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6E7C95" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="flex-none transition-transform duration-200"
                    :class="profileOpen ? 'rotate-180' : ''">
                    <path d="M6 9l6 6 6-6" />
                </svg>
            </button>
        </div>
    </aside>
</template>
