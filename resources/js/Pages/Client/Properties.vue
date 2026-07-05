<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    properties:      { type: Array,  default: () => [] },
    purchasedCount:  { type: Number, default: 0 },
    reservedCount:   { type: Number, default: 0 },
    handedOverCount: { type: Number, default: 0 },
    favouriteUnits:  { type: Array,  default: () => [] },
    upcomingHandover:{ type: Object, default: null },
});

const activeTab   = ref('all');
const searchQuery = ref('');

const tabs = computed(() => [
    { key: 'all',         label: 'All Properties', count: props.properties.length },
    { key: 'purchased',   label: 'Purchased',      count: props.purchasedCount },
    { key: 'reserved',    label: 'Reserved',       count: props.reservedCount },
    { key: 'favourites',  label: 'Favourites',     count: props.favouriteUnits.length },
    { key: 'handed_over', label: 'Handed Over',    count: props.handedOverCount },
]);

// ── Helpers ───────────────────────────────────────────────────────
function fmtBDT(n) {
    if (!n) return 'BDT 0';
    return 'BDT ' + Number(n).toLocaleString('en-US');
}
function paidPct(paid, total) {
    if (!total) return 0;
    return Math.min(100, Math.round((paid / total) * 100));
}

const statusMap = {
    draft:       { label: 'Draft',       bg: 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-300' },
    reserved:    { label: 'Reserved',    bg: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' },
    purchased:   { label: 'Purchased',   bg: 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400' },
    cancelled:   { label: 'Cancelled',   bg: 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' },
    handed_over: { label: 'Handed Over', bg: 'bg-[#efeafc] text-violet-700 dark:bg-violet-500/15 dark:text-violet-400' },
};
function statusCls(s) { return statusMap[s]?.bg ?? 'bg-slate-100 text-slate-600'; }
function statusLabel(s) { return statusMap[s]?.label ?? s; }

const constructionColorMap = {
    on_track:   'text-green-600 dark:text-green-400',
    delayed:    'text-red-500 dark:text-red-400',
    completed:  'text-violet-600 dark:text-violet-400',
    inspection: 'text-blue-600 dark:text-blue-400',
    paused:     'text-amber-600 dark:text-amber-400',
};

// ── Computed lists ─────────────────────────────────────────────────
const purchasedProperties  = computed(() => props.properties.filter(p => p.status === 'purchased'));
const reservedProperties   = computed(() => props.properties.filter(p => p.status === 'reserved'));
const handedOverProperties = computed(() => props.properties.filter(p => p.status === 'handed_over'));

function applySearch(list) {
    if (!searchQuery.value.trim()) return list;
    const q = searchQuery.value.toLowerCase();
    return list.filter(p =>
        p.project_name.toLowerCase().includes(q) ||
        (p.unit_number ?? '').toLowerCase().includes(q) ||
        (p.block ?? '').toLowerCase().includes(q) ||
        (p.project_location ?? '').toLowerCase().includes(q)
    );
}

const tableProperties = computed(() => {
    if (activeTab.value === 'all') return applySearch(props.properties);
    if (activeTab.value === 'purchased')   return applySearch(purchasedProperties.value);
    if (activeTab.value === 'reserved')    return applySearch(reservedProperties.value);
    if (activeTab.value === 'handed_over') return applySearch(handedOverProperties.value);
    return [];
});

// Dummy upcoming handover data (shown when no real data)
const dummyHandover = {
    project_name: 'Lake View Residence',
    unit_number: 'A-1205',
    handover_date: 'December 2026',
    days_remaining: 145,
    is_overdue: false,
    cover_image: null,
};
const handoverData = computed(() => props.upcomingHandover ?? dummyHandover);
</script>

<template>
    <Head title="My Properties" />
    <ClientLayout title="My Properties">

        <!-- ── Page header ──────────────────────────────────────────── -->
        <div class="flex items-start justify-between gap-5 flex-wrap">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.025em;">My Properties</h1>
                <p class="mt-1 text-sm font-medium text-muted-foreground">Manage, track and interact with all your properties in one place.</p>
            </div>
            <!-- Search -->
            <div class="flex items-center gap-2.5 h-11 px-3.5 bg-client-surface-card border border-[#ededf3] dark:border-white/[0.06] rounded-xl min-w-[260px]">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#a4a4b4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
                <input
                    v-model="searchQuery"
                    placeholder="Search properties, units…"
                    class="flex-1 bg-transparent border-none outline-none text-sm text-foreground placeholder:text-muted-foreground font-medium"
                />
            </div>
        </div>

        <!-- ── Tabs ─────────────────────────────────────────────────── -->
        <div class="mt-5 flex items-center gap-1 border-b border-[#ededf3] dark:border-white/[0.06]">
            <button
                v-for="tab in tabs" :key="tab.key"
                class="inline-flex items-center gap-1.5 pb-3 px-1 text-sm font-medium transition-colors border-b-2 mr-5 last:mr-0"
                :class="activeTab === tab.key
                    ? 'font-bold text-client-accent border-client-accent'
                    : 'text-muted-foreground border-transparent hover:text-foreground'"
                style="margin-bottom:-1px;"
                @click="activeTab = tab.key"
            >
                {{ tab.label }}
                <span v-if="tab.count > 0"
                    class="text-xs font-bold px-1.5 py-0.5 rounded-md"
                    :class="activeTab === tab.key
                        ? 'bg-client-accent/10 text-client-accent'
                        : 'bg-[#f0f0f5] dark:bg-white/[0.06] text-muted-foreground'"
                >{{ tab.count }}</span>
            </button>
        </div>

        <!-- ── Body: feed + right rail ──────────────────────────────── -->
        <div class="mt-5 flex gap-5 items-start">

            <!-- ===== FEED ===================================== -->
            <div class="flex-1 min-w-0 flex flex-col gap-4">

                <!-- ── ALL TAB ──────────────────────────────────── -->
                <template v-if="activeTab === 'all'">

                    <!-- Purchased -->
                    <template v-if="purchasedProperties.length">
                        <h2 class="text-base font-bold text-foreground">Purchased Properties</h2>
                        <Link v-for="p in purchasedProperties" :key="p.id"
                            :href="p.project_id ? route('client.projects.show', p.project_id) : '#'"
                            class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] overflow-hidden shadow-sm flex hover:shadow-md hover:border-client-accent/30 transition-all"
                        >
                            <!-- Cover image -->
                            <div class="w-[260px] flex-none relative hidden sm:block" style="min-height:220px;">
                                <img v-if="p.cover_image" :src="p.cover_image" :alt="p.project_name"
                                    class="absolute inset-0 w-full h-full object-cover" />
                                <div v-else class="absolute inset-0 flex items-center justify-center" style="background:linear-gradient(135deg,#f0eef9,#e8e4f5);">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#c4bce8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
                                </div>
                                <!-- Status badge -->
                                <span class="absolute top-3 left-3 text-xs font-bold px-2.5 py-1 rounded-lg" :class="statusCls(p.status)">
                                    {{ statusLabel(p.status) }}
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0 p-5 flex flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-foreground tracking-tight">{{ p.project_name }}</h3>
                                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 mt-1.5 text-sm text-muted-foreground font-medium">
                                            <template v-if="p.block">
                                                <span class="inline-flex items-center gap-1">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-client-accent"><path d="M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/></svg>
                                                    Block {{ p.block }}
                                                </span>
                                                <span class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>
                                            </template>
                                            <span v-if="p.floor">Floor {{ p.floor }}</span>
                                            <template v-if="p.bedrooms">
                                                <span class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>
                                                <span>{{ p.bedrooms }} Bed</span>
                                            </template>
                                            <template v-if="p.type_label">
                                                <span class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>
                                                <span>{{ p.type_label }}</span>
                                            </template>
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-foreground shrink-0">Unit {{ p.unit_number }}</span>
                                </div>

                                <!-- 4-col stats -->
                                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 py-4 border-t border-b border-[#f0f0f5] dark:border-white/[0.06]">
                                    <div v-if="p.size_sqft">
                                        <div class="text-xs text-muted-foreground font-medium">Size</div>
                                        <div class="text-sm font-bold text-foreground mt-1">{{ Number(p.size_sqft).toLocaleString() }} sft</div>
                                    </div>
                                    <div v-if="p.view">
                                        <div class="text-xs text-muted-foreground font-medium">View</div>
                                        <div class="text-sm font-bold text-foreground mt-1">{{ p.view }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-muted-foreground font-medium">Price</div>
                                        <div class="text-sm font-bold text-foreground mt-1">{{ fmtBDT(p.price_agreed) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-muted-foreground font-medium">Handover</div>
                                        <div class="text-sm font-bold text-foreground mt-1">{{ p.handover_date }}</div>
                                    </div>
                                </div>

                                <!-- Construction progress -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold text-muted-foreground">Construction Progress</span>
                                        <span class="text-sm font-extrabold text-client-accent">{{ Math.round(p.construction_pct) }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-[#f0eef9] dark:bg-client-accent/10 overflow-hidden">
                                        <div class="h-full rounded-full" style="background:linear-gradient(90deg,#7b63ff,#5132e0);" :style="`width:${Math.round(p.construction_pct)}%`"></div>
                                    </div>
                                </div>

                                <span class="mt-4 self-start inline-flex items-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl text-white"
                                    style="background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 10px 22px -8px rgba(81,50,224,.5);">
                                    View Project Details
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
                                </span>
                            </div>
                        </Link>
                    </template>

                    <!-- Reserved -->
                    <template v-if="reservedProperties.length">
                        <h2 class="text-base font-bold text-foreground mt-2">Reserved Properties</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Link v-for="p in reservedProperties" :key="p.id"
                                :href="p.project_id ? route('client.projects.show', p.project_id) : '#'"
                                class="block rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-4 shadow-sm hover:shadow-md hover:border-client-accent/30 transition-all"
                            >
                                <div class="flex gap-4">
                                    <!-- Thumbnail -->
                                    <div class="w-[100px] h-[100px] flex-none relative rounded-xl overflow-hidden">
                                        <img v-if="p.cover_image" :src="p.cover_image" :alt="p.project_name"
                                            class="absolute inset-0 w-full h-full object-cover" />
                                        <div v-else class="absolute inset-0 flex items-center justify-center bg-[#f0eef9] dark:bg-client-accent/10">
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#c4bce8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                                        </div>
                                        <span class="absolute top-2 left-2 text-xs font-bold px-2 py-0.5 rounded-md" :class="statusCls(p.status)">
                                            {{ statusLabel(p.status) }}
                                        </span>
                                    </div>
                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="text-sm font-extrabold text-foreground">{{ p.project_name }}</h3>
                                            <span class="text-xs font-bold text-foreground shrink-0">Unit {{ p.unit_number }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground font-medium flex-wrap">
                                            <span v-if="p.block">Block {{ p.block }}</span>
                                            <span v-if="p.block && p.floor" class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>
                                            <span v-if="p.floor">Floor {{ p.floor }}</span>
                                            <span v-if="p.bedrooms" class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>
                                            <span v-if="p.bedrooms">{{ p.bedrooms }} Bed</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2 mt-3">
                                            <div>
                                                <div class="text-xs text-muted-foreground font-medium">Price</div>
                                                <div class="text-xs font-bold text-foreground mt-0.5">{{ fmtBDT(p.price_agreed) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-muted-foreground font-medium">Booked</div>
                                                <div class="text-xs font-bold text-foreground mt-0.5">{{ p.booking_date }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-muted-foreground font-medium">Handover</div>
                                                <div class="text-xs font-bold text-red-500 mt-0.5">{{ p.handover_date }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 mt-3">
                                    <button class="flex-1 text-xs font-bold py-2.5 rounded-xl border transition-colors"
                                        style="border-color:#e6e1fb; background:#f6f3ff; color:#5132e0;"
                                        @mouseenter="$event.currentTarget.style.background='#efeafc'"
                                        @mouseleave="$event.currentTarget.style.background='#f6f3ff'"
                                        @click.stop.prevent
                                    >Convert to Purchase</button>
                                    <span class="px-4 text-xs font-bold py-2.5 rounded-xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card text-muted-foreground text-center">
                                        View Details
                                    </span>
                                </div>
                            </Link>
                        </div>
                    </template>

                    <!-- Favourites (dummy) -->
                    <div v-if="favouriteUnits.length" class="flex items-center justify-between mt-2">
                        <h2 class="text-base font-bold text-foreground">Favourite Properties</h2>
                        <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                    </div>
                    <div v-if="favouriteUnits.length" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <Link v-for="u in favouriteUnits" :key="u.id"
                            :href="u.project_id ? route('client.projects.show', u.project_id) : '#'"
                            class="block rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] overflow-hidden shadow-sm hover:shadow-md hover:border-client-accent/30 transition-all"
                        >
                            <!-- Image area -->
                            <div class="relative h-[120px] overflow-hidden">
                                <img v-if="u.cover_image" :src="u.cover_image" :alt="u.project_name"
                                    class="absolute inset-0 w-full h-full object-cover" />
                                <div v-else class="absolute inset-0 flex items-center justify-center" style="background:linear-gradient(135deg,#f0eef9,#e8e4f5);">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#c4bce8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                                </div>
                                <!-- Heart icon (dummy favourite) -->
                                <button class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white shadow-md flex items-center justify-center hover:scale-110 transition-transform" @click.stop.prevent>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#ec4f73"><path d="M12 21s-7-4.6-9.3-9C1.2 9 2.6 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.4 0 4.8 3.5 3.3 6.5C19 16.4 12 21 12 21Z"/></svg>
                                </button>
                            </div>
                            <!-- Info -->
                            <div class="p-3">
                                <h3 class="text-sm font-extrabold text-foreground truncate">{{ u.project_name }}</h3>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground font-medium flex-wrap">
                                    <span v-if="u.block">Block {{ u.block }}</span>
                                    <span v-if="u.floor" class="flex items-center gap-1"><span v-if="u.block" class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>Floor {{ u.floor }}</span>
                                    <span v-if="u.bedrooms" class="flex items-center gap-1"><span class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>{{ u.bedrooms }} Bed</span>
                                    <span v-else-if="u.type_label" class="flex items-center gap-1"><span class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>{{ u.type_label }}</span>
                                </div>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-sm font-extrabold text-client-accent">{{ fmtBDT(u.price) }}</span>
                                    <span class="w-8 h-8 rounded-lg border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card flex items-center justify-center">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-client-accent"><path d="M12 4v16M5 8 3 12h4zM19 8l-2 4h4z"/><path d="M3 12c.7 1.3 2 2 4 2s3.3-.7 4-2M13 12c.7 1.3 2 2 4 2s3.3-.7 4-2"/></svg>
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!properties.length"
                        class="rounded-2xl border border-dashed border-border bg-client-surface-card p-10 text-center"
                    >
                        <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-client-accent/10 text-client-accent">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
                        </div>
                        <p class="text-sm font-bold text-foreground">No properties yet</p>
                        <p class="mt-1 text-xs text-muted-foreground">Your properties will appear here once a booking is made.</p>
                    </div>
                </template>

                <!-- ── TABLE TABS (Purchased / Reserved / Handed Over) ── -->
                <template v-else-if="activeTab !== 'favourites'">
                    <div v-if="tableProperties.length"
                        class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] shadow-sm overflow-hidden"
                    >
                        <!-- Table header -->
                        <div class="grid items-center gap-3 px-5 py-3 border-b border-[#f0f0f5] dark:border-white/[0.06] bg-[#faf9fd] dark:bg-white/[0.02]"
                            style="grid-template-columns:2.2fr 1.2fr 1fr 1.3fr 1.2fr auto;"
                        >
                            <span class="text-xs font-bold uppercase tracking-wide text-muted-foreground">Property</span>
                            <span class="text-xs font-bold uppercase tracking-wide text-muted-foreground">Details</span>
                            <span class="text-xs font-bold uppercase tracking-wide text-muted-foreground">Price / Paid</span>
                            <span class="text-xs font-bold uppercase tracking-wide text-muted-foreground">Construction</span>
                            <span class="text-xs font-bold uppercase tracking-wide text-muted-foreground">Handover</span>
                            <span class="text-xs font-bold uppercase tracking-wide text-muted-foreground text-right">Status</span>
                        </div>

                        <!-- Rows -->
                        <Link v-for="(p, i) in tableProperties" :key="p.id"
                            :href="p.project_id ? route('client.projects.show', p.project_id) : '#'"
                            class="grid items-center gap-3 px-5 py-3.5 transition-colors hover:bg-[#faf9fd] dark:hover:bg-white/[0.02]"
                            :class="i < tableProperties.length - 1 ? 'border-b border-[#f4f4f8] dark:border-white/[0.04]' : ''"
                            style="grid-template-columns:2.2fr 1.2fr 1fr 1.3fr 1.2fr auto;"
                        >
                            <!-- Property -->
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-14 h-14 flex-none relative rounded-xl overflow-hidden">
                                    <img v-if="p.cover_image" :src="p.cover_image" :alt="p.project_name"
                                        class="absolute inset-0 w-full h-full object-cover" />
                                    <div v-else class="absolute inset-0 flex items-center justify-center bg-[#f0eef9] dark:bg-client-accent/10">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c4bce8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-foreground truncate">{{ p.project_name }}</div>
                                    <div class="text-xs text-muted-foreground font-medium mt-0.5">Unit {{ p.unit_number }}</div>
                                    <div class="text-xs text-muted-foreground mt-0.5 truncate">{{ p.project_location }}</div>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="text-xs text-muted-foreground font-medium flex flex-col gap-0.5">
                                <span v-if="p.type_label" class="font-semibold text-foreground">{{ p.type_label }}</span>
                                <span v-if="p.size_sqft">{{ Number(p.size_sqft).toLocaleString() }} sft</span>
                                <span v-if="p.floor">Floor {{ p.floor }}</span>
                                <span v-if="p.bedrooms">{{ p.bedrooms }} Bed{{ p.bathrooms ? ' · ' + p.bathrooms + ' Bath' : '' }}</span>
                            </div>

                            <!-- Price / Paid -->
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-bold text-foreground">{{ fmtBDT(p.price_agreed) }}</span>
                                <span class="text-xs font-semibold text-green-600 dark:text-green-400">Paid {{ fmtBDT(p.total_paid) }}</span>
                                <div class="h-1.5 rounded-full bg-[#f0eef9] dark:bg-client-accent/10 overflow-hidden mt-0.5" style="width:80px;">
                                    <div class="h-full rounded-full bg-green-500" :style="`width:${paidPct(p.total_paid, p.price_agreed)}%`"></div>
                                </div>
                            </div>

                            <!-- Construction -->
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between" style="width:110px;">
                                    <span class="text-xs font-medium" :class="constructionColorMap[p.construction_status] ?? 'text-muted-foreground'">{{ p.construction_label }}</span>
                                    <span class="text-xs font-bold text-client-accent">{{ Math.round(p.construction_pct) }}%</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-[#f0eef9] dark:bg-client-accent/10 overflow-hidden" style="width:110px;">
                                    <div class="h-full rounded-full" style="background:linear-gradient(90deg,#7b63ff,#5132e0);"
                                        :style="`width:${Math.round(p.construction_pct)}%`"></div>
                                </div>
                            </div>

                            <!-- Handover -->
                            <div class="text-xs font-semibold text-foreground">{{ p.handover_date }}</div>

                            <!-- Status -->
                            <span class="inline-flex text-xs font-bold px-2.5 py-1 rounded-lg justify-self-end" :class="statusCls(p.status)">
                                {{ statusLabel(p.status) }}
                            </span>
                        </Link>
                    </div>

                    <!-- Empty state for filtered tab -->
                    <div v-else class="rounded-2xl border border-dashed border-border bg-client-surface-card p-10 text-center">
                        <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-client-accent/10 text-client-accent">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                        </div>
                        <p class="text-sm font-bold text-foreground">No properties found</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ searchQuery ? 'Try a different search term.' : 'No ' + tabs.find(t=>t.key===activeTab)?.label?.toLowerCase() + ' properties yet.' }}
                        </p>
                    </div>
                </template>

                <!-- ── FAVOURITES TAB ─────────────────────────────────── -->
                <template v-else>
                    <div v-if="favouriteUnits.length" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <Link v-for="u in favouriteUnits" :key="u.id"
                            :href="u.project_id ? route('client.projects.show', u.project_id) : '#'"
                            class="block rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] overflow-hidden shadow-sm hover:shadow-md hover:border-client-accent/30 transition-all"
                        >
                            <div class="relative h-[130px] overflow-hidden">
                                <img v-if="u.cover_image" :src="u.cover_image" :alt="u.project_name"
                                    class="absolute inset-0 w-full h-full object-cover" />
                                <div v-else class="absolute inset-0 flex items-center justify-center" style="background:linear-gradient(135deg,#f0eef9,#e8e4f5);">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#c4bce8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                                </div>
                                <button class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white shadow-md flex items-center justify-center hover:scale-110 transition-transform" @click.stop.prevent>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#ec4f73"><path d="M12 21s-7-4.6-9.3-9C1.2 9 2.6 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.4 0 4.8 3.5 3.3 6.5C19 16.4 12 21 12 21Z"/></svg>
                                </button>
                                <span class="absolute bottom-2 left-2 text-xs font-bold px-2 py-0.5 rounded-md bg-green-100 text-green-700">Available</span>
                            </div>
                            <div class="p-3.5">
                                <h3 class="text-sm font-extrabold text-foreground truncate">{{ u.project_name }}</h3>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground font-medium flex-wrap">
                                    <span v-if="u.location" class="truncate max-w-[120px]">{{ u.location }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-xs text-muted-foreground font-medium">
                                    <span v-if="u.bedrooms">{{ u.bedrooms }} Bed</span>
                                    <span v-if="u.bedrooms && u.size_sqft" class="w-1 h-1 rounded-full bg-[#cfcfda]"></span>
                                    <span v-if="u.size_sqft">{{ Number(u.size_sqft).toLocaleString() }} sft</span>
                                    <span v-else>{{ u.type_label }}</span>
                                </div>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-sm font-extrabold text-client-accent">{{ fmtBDT(u.price) }}</span>
                                    <span class="w-8 h-8 rounded-lg border border-[#ededf3] dark:border-white/[0.06] flex items-center justify-center">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-client-accent"><path d="M12 4v16M5 8 3 12h4zM19 8l-2 4h4z"/><path d="M3 12c.7 1.3 2 2 4 2s3.3-.7 4-2M13 12c.7 1.3 2 2 4 2s3.3-.7 4-2"/></svg>
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="rounded-2xl border border-dashed border-border bg-client-surface-card p-10 text-center">
                        <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-400">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.6-9.3-9C1.2 9 2.6 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.4 0 4.8 3.5 3.3 6.5C19 16.4 12 21 12 21Z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-foreground">No available properties to show</p>
                        <p class="mt-1 text-xs text-muted-foreground">Browse the market and save your favourites here.</p>
                    </div>
                </template>
            </div>
            <!-- ===== END FEED ================================= -->

            <!-- ===== RIGHT RAIL =============================== -->
            <aside class="hidden xl:flex flex-col gap-4" style="width:300px; flex:none;">

                <!-- Property Summary -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-client-accent"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
                        <h3 class="text-base font-bold text-foreground">Property Summary</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-[#f0f0f5] dark:border-white/[0.06] p-3.5">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
                            <div class="text-2xl font-extrabold text-foreground mt-2.5 tracking-tight">{{ purchasedCount }}</div>
                            <div class="text-xs text-muted-foreground font-semibold mt-0.5">Purchased</div>
                        </div>
                        <div class="rounded-xl border border-[#f0f0f5] dark:border-white/[0.06] p-3.5">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f08a1d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-6-4-6 4z"/></svg>
                            <div class="text-2xl font-extrabold text-foreground mt-2.5 tracking-tight">{{ reservedCount }}</div>
                            <div class="text-xs text-muted-foreground font-semibold mt-0.5">Reserved</div>
                        </div>
                        <div class="rounded-xl border border-[#f0f0f5] dark:border-white/[0.06] p-3.5">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#ec4f73"><path d="M12 21s-7-4.6-9.3-9C1.2 9 2.6 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.4 0 4.8 3.5 3.3 6.5C19 16.4 12 21 12 21Z"/></svg>
                            <div class="text-2xl font-extrabold text-foreground mt-2.5 tracking-tight">{{ favouriteUnits.length }}</div>
                            <div class="text-xs text-muted-foreground font-semibold mt-0.5">Favourites</div>
                        </div>
                        <div class="rounded-xl border border-[#f0f0f5] dark:border-white/[0.06] p-3.5">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                            <div class="text-2xl font-extrabold text-foreground mt-2.5 tracking-tight">{{ handedOverCount }}</div>
                            <div class="text-xs text-muted-foreground font-semibold mt-0.5">Handed Over</div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Handover -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-4">Upcoming Handover</h3>
                    <div class="flex gap-3 items-center">
                        <div class="w-[68px] h-[68px] flex-none rounded-xl overflow-hidden relative">
                            <img v-if="handoverData.cover_image" :src="handoverData.cover_image" :alt="handoverData.project_name"
                                class="absolute inset-0 w-full h-full object-cover" />
                            <div v-else class="absolute inset-0 flex items-center justify-center bg-[#f0eef9] dark:bg-client-accent/10">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c4bce8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold text-foreground truncate">{{ handoverData.project_name }}</div>
                            <div class="text-xs text-muted-foreground font-medium mt-0.5">Unit {{ handoverData.unit_number }}</div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-3 p-3.5 bg-[#f6f3ff] dark:bg-client-accent/10 rounded-xl">
                        <div>
                            <div class="text-xs text-muted-foreground font-semibold">Estimated Handover</div>
                            <div class="text-sm font-extrabold text-client-accent mt-1">{{ handoverData.handover_date }}</div>
                            <div v-if="handoverData.is_overdue" class="text-xs font-bold text-amber-600 mt-1">Ready for Handover</div>
                            <div v-else-if="handoverData.days_remaining > 0" class="text-xs font-bold text-green-600 dark:text-green-400 mt-1">
                                {{ handoverData.days_remaining }} Days Remaining
                            </div>
                        </div>
                        <div class="w-10 h-10 flex-none rounded-xl bg-white dark:bg-client-surface-card shadow-sm flex items-center justify-center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-client-accent"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>
                        </div>
                    </div>
                    <button class="mt-3 w-full text-sm font-bold py-2.5 rounded-xl border transition-colors"
                        style="border-color:#e6e1fb; color:#5132e0; background:#fff;"
                        @mouseenter="$event.currentTarget.style.background='#f6f3ff'"
                        @mouseleave="$event.currentTarget.style.background='#fff'"
                    >View Timeline</button>
                </div>

                <!-- Quick Actions -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <h3 class="text-base font-bold text-foreground mb-3">Quick Actions</h3>
                    <div class="flex flex-col gap-1">
                        <a v-for="action in [
                            { label:'Compare Properties',  well:'bg-[#efeafc]',                      ic:'text-client-accent', icon:`<path d='M12 4v16M5 8 3 12h4zM19 8l-2 4h4z'/><path d='M3 12c.7 1.3 2 2 4 2s3.3-.7 4-2M13 12c.7 1.3 2 2 4 2s3.3-.7 4-2'/>` },
                            { label:'Schedule Site Visit', well:'bg-blue-100 dark:bg-blue-500/15',   ic:'text-blue-600 dark:text-blue-400', icon:`<rect x='3' y='4.5' width='18' height='16.5' rx='2.5'/><path d='M3 9h18M8 2.5v4M16 2.5v4'/><path d='m9 14 2 2 4-4'/>` },
                            { label:'Download Brochure',   well:'bg-green-100 dark:bg-green-500/15', ic:'text-green-600 dark:text-green-400', icon:`<path d='M12 3v12M7 10l5 5 5-5'/><path d='M4 20h16'/>` },
                            { label:'Chat with Sales',     well:'bg-amber-100 dark:bg-amber-500/15', ic:'text-amber-600 dark:text-amber-400', icon:`<path d='M4 5h16v11H9l-4 4z'/>` },
                        ]" :key="action.label"
                            href="#"
                            class="flex items-center gap-3 p-2.5 rounded-xl transition-colors hover:bg-[#faf9fd] dark:hover:bg-white/[0.04]"
                        >
                            <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg" :class="action.well">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="action.ic" v-html="action.icon" />
                            </span>
                            <span class="flex-1 text-sm font-semibold text-foreground">{{ action.label }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 dark:text-slate-600"><path d="M9 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>

            </aside>
            <!-- ===== END RIGHT RAIL =========================== -->
        </div>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] px-5 py-3.5 shadow-sm">
            <div class="text-sm font-bold text-foreground">
                HomeVerse<sup class="text-xs font-normal text-muted-foreground">™</sup>
                <span class="text-muted-foreground font-normal ml-2">·</span>
                <span class="text-sm font-medium text-muted-foreground ml-2">Real Estate Intelligence Platform</span>
            </div>
            <div class="text-xs font-medium text-muted-foreground">
                Powered by <span class="font-bold text-foreground">Future Studios Bangladesh</span>
                · <a href="#" class="font-bold text-client-accent">www.fsb.site</a>
                · © 2026
            </div>
        </footer>

    </ClientLayout>
</template>
