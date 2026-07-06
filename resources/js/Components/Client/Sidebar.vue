<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';

const props = defineProps({
    drawerOpen: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const page = usePage();
const client = computed(() => page.props.auth?.client);

const profileOpen = ref(false);
const profileRef = ref(null);
onClickOutside(profileRef, () => { profileOpen.value = false; });

const navLinks = [
    {
        label: 'Dashboard',
        routeName: 'client.dashboard',
        icon: `<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>`,
    },
    {
        label: 'My Properties',
        routeName: 'client.properties',
        icon: `<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><rect x="9.5" y="13" width="5" height="8"/>`,
    },
    {
        label: 'Payments & Installments',
        routeName: 'client.payments',
        icon: `<rect x="4" y="3" width="16" height="18" rx="2.5"/><path d="M8 8h8M8 12h8M8 16h5"/>`,
    },
    {
        label: 'Mortgage & Financing',
        routeName: 'client.mortgage',
        icon: `<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-5h6v5"/>`,
    },
    {
        label: 'Investment Analyzer',
        routeName: 'client.investment',
        icon: `<path d="M4 19h16"/><path d="M5 19V9l5-3 5 3v10"/><path d="M9 19v-4h2v4"/><path d="M17 19v-7l3-2v9"/>`,
    },
    {
        label: 'Construction Progress',
        routeName: 'client.construction',
        icon: `<circle cx="8" cy="8" r="2.5"/><circle cx="17" cy="9" r="2"/><path d="M3 19c0-3 2.2-5 5-5s5 2 5 5"/><path d="M15 19c0-2.5 1-4 3.5-4s2.5 1.5 2.5 4"/>`,
    },
    {
        label: 'Community & Future',
        routeName: 'client.community.future',
        icon: `<rect x="3" y="8" width="8" height="13" rx="1.5"/><rect x="13" y="3" width="8" height="18" rx="1.5"/><path d="M6 12h2M6 15.5h2M16 7h2M16 10.5h2M16 14h2"/>`,
    },
    {
        label: 'AI Property Advisor',
        routeName: 'client.ai.advisor',
        icon: `<path d="M12 3 4 7v5c0 5 3.5 8 8 9 4.5-1 8-4 8-9V7z"/><path d="M9.5 12l1.8 1.8L15 10"/>`,
    },
    {
        label: 'Documents',
        routeName: 'client.documents',
        icon: `<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6"/>`,
    },
    {
        label: 'Support & Help',
        routeName: 'client.support',
        icon: `<circle cx="12" cy="12" r="9"/><path d="M9.2 9.2a3 3 0 0 1 5.6 1.3c0 2-3 2.3-3 4"/><path d="M12 17.5h.01"/>`,
    },
];

function isActive(link) {
    if (!link.routeName) return false;
    try { return route().current(link.routeName); } catch { return false; }
}

function logout() {
    profileOpen.value = false;
    router.post(route('client.logout'));
}

function initials(name) {
    if (!name) return 'C';
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
}
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col bg-client-surface-sidebar border-r border-[#ededf3] dark:border-white/[0.06] transition-transform duration-300 lg:sticky lg:top-0 lg:z-auto lg:translate-x-0"
        :class="drawerOpen ? 'translate-x-0' : '-translate-x-full'"
        style="width:276px; padding:26px 18px 18px; height:100vh; overflow-y:auto; scrollbar-width:thin;">
        <!-- Logo row -->
        <div style="display:flex; align-items:center; gap:11px; padding:4px 8px 22px; flex-shrink:0;">
            <!-- Three vertical bars -->
            <div style="display:flex; align-items:flex-end; gap:3px; height:34px; flex:none;">
                <div style="width:7px; height:20px; border-radius:3px; background:#5b3fe8;"></div>
                <div
                    style="width:7px; height:34px; border-radius:3px; background:linear-gradient(180deg,#7b63ff,#4f33d6);">
                </div>
                <div style="width:7px; height:26px; border-radius:3px; background:#241a5c;"></div>
            </div>
            <div style="line-height:1; flex:1;">
                <div class="text-foreground" style="font-size:19px; font-weight:800; letter-spacing:-0.02em;">LakeView
                </div>
                <div style="font-size:9.5px; font-weight:600; letter-spacing:0.32em; color:#9a9aac; margin-top:3px;">
                    RESIDENCES</div>
            </div>
            <!-- Close button (mobile/tablet only) -->
            <button
                class="lg:hidden flex items-center justify-center border border-border rounded-xl cursor-pointer bg-client-surface-card hover:bg-muted transition-colors"
                style="width:38px; height:38px; flex:none;" @click="emit('close')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <!-- Nav -->
        <nav style="display:flex; flex-direction:column; gap:3px; flex:1;">
            <template v-for="link in navLinks" :key="link.label">
                <Link v-if="link.routeName" :href="route(link.routeName)"
                    class="flex items-center transition-all duration-150"
                    :class="isActive(link)
                        ? 'text-white'
                        : 'text-[#5a5a6e] hover:bg-[#f5f4fb] hover:text-[#16162a] dark:text-muted-foreground dark:hover:bg-client-accent/10 dark:hover:text-foreground'"
                    :style="isActive(link)
                        ? 'background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 8px 18px -6px rgba(81,50,224,.5); font-weight:600; gap:13px; padding:13px 14px; border-radius:12px; font-size:14px; text-decoration:none;'
                        : 'gap:13px; padding:13px 14px; border-radius:12px; font-size:14px; font-weight:500; text-decoration:none;'"
                    @click="emit('close')">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" style="flex:none;" v-html="link.icon" />
                    {{ link.label }}
                </Link>

                <div v-else
                    class="flex items-center select-none text-[#5a5a6e] hover:bg-[#f5f4fb] hover:text-[#16162a] dark:text-muted-foreground dark:hover:bg-client-accent/10 dark:hover:text-foreground transition-all duration-150 cursor-default"
                    style="gap:13px; padding:13px 14px; border-radius:12px; font-size:14px; font-weight:500;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" style="flex:none;" v-html="link.icon" />
                    {{ link.label }}
                </div>
            </template>
        </nav>

        <!-- Bottom section -->
        <div style="margin-top:auto; display:flex; flex-direction:column; gap:12px; padding-top:14px; flex-shrink:0;">
            <!-- AI help promo card -->
            <div
                style="position:relative; overflow:hidden; border-radius:18px; padding:16px; background:linear-gradient(135deg,#efeafc,#e7e0ff); border:1px solid #e3daff;">
                <div style="max-width:128px;">
                    <div
                        style="display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:700; color:#3a25b0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#5b3fe8">
                            <path d="M12 2l1.8 5.6L19.5 9l-4.6 3.4L16.5 18 12 14.7 7.5 18l1.6-5.6L4.5 9l5.7-1.4z" />
                        </svg>
                        Need Help?
                    </div>
                    <div style="font-size:12px; line-height:1.5; color:#6a5fae; margin:7px 0 12px; font-weight:500;">
                        Chat with
                        your AI Property Advisor.</div>
                    <!-- <button
                        style="border:none; cursor:pointer; background:linear-gradient(100deg,#6a4dff,#5132e0); color:#fff; font-size:12.5px; font-weight:700; padding:9px 18px; border-radius:10px; box-shadow:0 6px 14px -4px rgba(81,50,224,.55); font-family:inherit;">
                        Chat Now
                    </button> -->
                    <Link :href="route('client.ai.advisor')"
                        style="border:none; cursor:pointer; background:linear-gradient(100deg,#6a4dff,#5132e0); color:#fff; font-size:12.5px; font-weight:700; padding:9px 18px; border-radius:10px; box-shadow:0 6px 14px -4px rgba(81,50,224,.55); font-family:inherit;">
                        Chat Now
                    </Link>
                </div>
                <!-- Robot icon -->
                <div
                    style="position:absolute; right:-6px; bottom:-6px; width:88px; height:88px; border-radius:24px; background:linear-gradient(160deg,#7b63ff,#4f33d6); display:flex; align-items:center; justify-content:center; box-shadow:0 12px 24px -8px rgba(79,51,214,.6);">
                    <div style="width:46px; height:38px; background:#1a123f; border-radius:13px; position:relative;">
                        <div
                            style="position:absolute; top:11px; left:9px; width:9px; height:9px; border-radius:50%; background:#8fb4ff;">
                        </div>
                        <div
                            style="position:absolute; top:11px; right:9px; width:9px; height:9px; border-radius:50%; background:#8fb4ff;">
                        </div>
                        <div
                            style="position:absolute; top:-7px; left:50%; transform:translateX(-50%); width:2px; height:7px; background:#cdbcff;">
                        </div>
                        <div
                            style="position:absolute; top:-9px; left:50%; transform:translateX(-50%); width:5px; height:5px; border-radius:50%; background:#cdbcff;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile row with dropdown -->
            <div ref="profileRef" class="relative">
                <!-- Dropdown -->
                <Transition enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2">
                    <div v-if="profileOpen"
                        class="absolute bottom-full mb-2 left-0 right-0 z-50 overflow-hidden rounded-xl border border-border bg-client-surface-card shadow-xl">
                        <div class="border-b border-border px-3 py-2.5">
                            <div class="truncate text-sm font-bold text-foreground">{{ client?.name ?? 'Buyer' }}</div>
                            <div class="truncate text-xs text-muted-foreground">{{ client?.email ?? '' }}</div>
                        </div>
                        <Link :href="route('client.profile.show')" @click="profileOpen = false"
                            class="flex w-full items-center gap-2.5 px-3 py-2.5 text-sm text-foreground transition-colors hover:bg-muted">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                            My Profile
                        </Link>
                        <div class="mx-3 my-1 h-px bg-border" />
                        <button
                            class="flex w-full items-center gap-2.5 px-3 py-2.5 text-sm text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-500/10"
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
                    class="flex w-full items-center border border-border rounded-xl cursor-pointer hover:bg-[#faf9fd] dark:hover:bg-client-accent/10 transition-colors text-left"
                    style="gap:11px; padding:11px 12px;" @click="profileOpen = !profileOpen">
                    <div class="flex-none flex items-center justify-center rounded-full text-white"
                        style="width:40px; height:40px; background:linear-gradient(135deg,#c9bdf7,#8b6df0); font-size:15px; font-weight:700;">
                        {{ initials(client?.name) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-foreground font-bold truncate" style="font-size:13.5px;">{{ client?.name ??
                            'Buyer' }}
                        </div>
                        <div class="text-muted-foreground truncate"
                            style="font-size:11.5px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{
                                client?.email ?? '' }}</div>
                    </div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#b6b6c4" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round" class="flex-none">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
            </div>
        </div>
    </aside>
</template>
