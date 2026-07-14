<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { User, Layers, Home, Ruler, Maximize2, Wallet, Calendar, CalendarCheck } from 'lucide-vue-next';

const props = defineProps({
    project: { type: Object, required: true },
});

const activeImage = ref(props.project.cover || null);

// ── Units ──────────────────────────────────────────────────────────
const statusBadgeVariant = {
    green: 'success',
    amber: 'warning',
    orange: 'warning',
    blue: 'info',
    purple: 'purple',
    red: 'destructive',
    slate: 'secondary',
};

const activeUnitFilter = ref('all');

const unitFilters = computed(() => {
    const units = props.project.units ?? [];
    const filters = [{ key: 'all', label: 'All Units', count: units.length }];
    const seen = new Map();
    for (const u of units) {
        if (!seen.has(u.status)) seen.set(u.status, { key: u.status, label: u.status_label, count: 0 });
        seen.get(u.status).count++;
    }
    return filters.concat([...seen.values()]);
});

const filteredUnits = computed(() => {
    const units = props.project.units ?? [];
    if (activeUnitFilter.value === 'all') return units;
    return units.filter(u => u.status === activeUnitFilter.value);
});

// Group units by floor, highest floor first, so it reads top-down like the building
const unitsByFloor = computed(() => {
    const groups = new Map();
    for (const u of filteredUnits.value) {
        const key = u.floor ?? '—';
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(u);
    }
    return [...groups.entries()]
        .sort((a, b) => (b[0] === '—' ? -Infinity : b[0]) - (a[0] === '—' ? -Infinity : a[0]))
        .map(([floor, units]) => ({ floor, units }));
});

const statusMap = {
    draft:              { cls: 'bg-slate-100 text-slate-600' },
    planning:           { cls: 'bg-blue-100 text-blue-700' },
    under_construction: { cls: 'bg-amber-100 text-amber-700' },
    completed:          { cls: 'bg-green-100 text-green-700' },
    on_hold:            { cls: 'bg-red-100 text-red-600' },
};
const statusCls = computed(() => statusMap[props.project.status]?.cls ?? 'bg-slate-100 text-slate-600');

const allImages = computed(() => {
    const imgs = [];
    if (props.project.cover) imgs.push(props.project.cover);
    props.project.images?.forEach(img => { if (img !== props.project.cover) imgs.push(img); });
    return imgs;
});

const progress = computed(() => Math.min(100, Math.round(Number(props.project.construction_pct) || 0)));

function fmtBDT(n) {
    if (!n) return '—';
    if (n >= 10000000) return 'BDT ' + (n / 10000000).toFixed(2) + ' Cr';
    if (n >= 100000)   return 'BDT ' + (n / 100000).toFixed(2) + ' Lac';
    return 'BDT ' + Number(n).toLocaleString('en-US');
}

const complianceStatus = {
    obtained:     { cls: 'bg-green-100 text-green-700', label: 'Obtained' },
    pending:      { cls: 'bg-amber-100 text-amber-700', label: 'Pending' },
    not_required: { cls: 'bg-slate-100 text-slate-500', label: 'N/A' },
};
</script>

<template>
    <Head :title="project.name" />
    <ClientLayout :title="project.name">

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm mb-4 flex-wrap">
            <Link :href="route('client.mortgage')"
                class="inline-flex items-center gap-1.5 font-bold text-muted-foreground hover:text-foreground transition-colors">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Mortgage
            </Link>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#c2c2cf" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
            <span class="font-bold text-foreground truncate">{{ project.name }}</span>
        </div>

        <div class="flex gap-6 items-start">

            <!-- ── MAIN COLUMN ─────────────────────────────────────── -->
            <div class="flex-1 min-w-0 space-y-5">

                <!-- Hero image -->
                <div class="rounded-2xl overflow-hidden border border-[#ededf3] dark:border-white/[0.06] shadow-sm bg-client-surface-card">
                    <div class="relative h-[320px]" style="background:linear-gradient(135deg,#e8e3fb,#d8d0f5);">
                        <img v-if="activeImage" :src="activeImage" :alt="project.name"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div v-else class="absolute inset-0 flex items-center justify-center">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#b8aee8" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
                        </div>
                        <!-- Badges -->
                        <div class="absolute top-4 left-4 flex items-center gap-2">
                            <span class="text-xs font-bold px-3 py-1 rounded-lg" :class="statusCls">{{ project.status_label }}</span>
                            <span class="text-xs font-bold px-3 py-1 rounded-lg bg-white/90 text-slate-700">{{ project.type_label }}</span>
                        </div>
                        <!-- Progress overlay -->
                        <div class="absolute bottom-0 left-0 right-0 px-5 py-3" style="background:linear-gradient(to top,rgba(0,0,0,0.55),transparent);">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-bold text-white/80">Construction Progress</span>
                                <span class="text-sm font-extrabold text-white">{{ progress }}%</span>
                            </div>
                            <div class="h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.25);">
                                <div class="h-full rounded-full" style="background:linear-gradient(90deg,#7b63ff,#5132e0);" :style="`width:${progress}%`" />
                            </div>
                        </div>
                    </div>

                    <!-- Thumbnail strip -->
                    <div v-if="allImages.length > 1" class="flex gap-2 p-3 overflow-x-auto" style="scrollbar-width:none;">
                        <button v-for="(img, i) in allImages" :key="i"
                            class="flex-none w-16 h-16 rounded-xl overflow-hidden border-2 transition-all"
                            :class="activeImage === img ? 'border-client-accent' : 'border-transparent opacity-60 hover:opacity-100'"
                            @click="activeImage = img">
                            <img :src="img" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Project info -->
                <div class="rounded-2xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                        <div>
                            <h1 class="text-2xl font-extrabold text-foreground tracking-tight" style="letter-spacing:-0.025em;">{{ project.name }}</h1>
                            <div class="flex items-center gap-1.5 mt-1.5 text-sm text-muted-foreground font-medium">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                {{ project.location || project.address || '—' }}
                            </div>
                        </div>
                        <span v-if="project.project_code" class="text-xs font-bold px-3 py-1.5 rounded-xl border border-[#ededf3] dark:border-white/[0.06] text-muted-foreground">
                            {{ project.project_code }}
                        </span>
                    </div>

                    <p v-if="project.description" class="text-sm font-medium text-muted-foreground leading-relaxed mb-5">{{ project.description }}</p>

                    <!-- Key specs grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="spec in [
                            { label: 'Developer',     value: project.developer_name || '—', icon: User },
                            { label: 'Total Floors',  value: project.total_floors ? project.total_floors + ' Floors' : '—', icon: Layers },
                            { label: 'Total Units',   value: project.total_units || '—', icon: Home },
                            { label: 'Land Area',     value: project.land_area ? project.land_area + ' ' + (project.land_area_unit || '') : '—', icon: Ruler },
                            { label: 'Built-up Area', value: project.built_up_area ? Number(project.built_up_area).toLocaleString() + ' sqft' : '—', icon: Maximize2 },
                            { label: 'Est. Value',    value: fmtBDT(project.estimated_value), icon: Wallet },
                            { label: 'Start Date',    value: project.start_date || '—', icon: Calendar },
                            { label: 'Handover',      value: project.handover_date || '—', icon: CalendarCheck },
                        ]" :key="spec.label"
                            class="flex items-center gap-3 rounded-xl border border-[#f0f0f5] dark:border-white/[0.06] p-3.5">
                            <span class="flex h-9 w-9 flex-none items-center justify-center rounded-lg bg-[#efeafc] text-client-accent">
                                <component :is="spec.icon" :size="16" :stroke-width="2" />
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-muted-foreground truncate">{{ spec.label }}</div>
                                <div class="text-sm font-bold text-foreground mt-0.5 truncate">{{ spec.value }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities -->
                <div v-if="project.facilities?.length" class="rounded-2xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card shadow-sm p-6">
                    <h2 class="text-base font-bold text-foreground mb-4">Facilities &amp; Amenities</h2>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="f in project.facilities" :key="f.name"
                            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl border border-[#ededf3] dark:border-white/[0.06] bg-[#faf9fd] text-foreground">
                            {{ f.name }}
                        </span>
                    </div>
                </div>

                <!-- Compliances -->
                <div v-if="project.compliances?.length" class="rounded-2xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card shadow-sm p-6">
                    <h2 class="text-base font-bold text-foreground mb-4">Approvals &amp; Compliance</h2>
                    <div class="divide-y divide-[#f4f4f8] dark:divide-white/[0.05]">
                        <div v-for="c in project.compliances" :key="c.name" class="flex items-center justify-between py-3">
                            <div>
                                <div class="text-sm font-semibold text-foreground">{{ c.name }}</div>
                                <div class="text-xs text-muted-foreground capitalize mt-0.5">{{ c.type }}</div>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-lg"
                                :class="complianceStatus[c.status]?.cls ?? 'bg-slate-100 text-slate-500'">
                                {{ complianceStatus[c.status]?.label ?? c.status }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── RIGHT RAIL ──────────────────────────────────────── -->
            <aside class="hidden xl:flex flex-col gap-4" style="width:300px; flex:none;">

                <!-- Unit availability -->
                <div class="rounded-2xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card shadow-sm p-5">
                    <h3 class="text-base font-bold text-foreground mb-4">Unit Availability</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="stat in [
                            { label: 'Total',     value: project.unit_stats.total,     color: '#5B3DF5', bg: '#F1ECFF' },
                            { label: 'Available', value: project.unit_stats.available, color: '#22C55E', bg: '#E6F7EE' },
                            { label: 'Sold',      value: project.unit_stats.sold,      color: '#3B82F6', bg: '#E8F0FF' },
                            { label: 'Reserved',  value: project.unit_stats.reserved,  color: '#F59E0B', bg: '#FFF3E0' },
                        ]" :key="stat.label"
                            class="rounded-xl p-3.5 text-center" :style="`background:${stat.bg};`">
                            <div class="text-2xl font-extrabold" :style="`color:${stat.color};`">{{ stat.value }}</div>
                            <div class="text-xs font-semibold mt-1" :style="`color:${stat.color};`">{{ stat.label }}</div>
                        </div>
                    </div>
                </div>

                <!-- Construction progress -->
                <div class="rounded-2xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card shadow-sm p-5">
                    <h3 class="text-base font-bold text-foreground mb-4">Construction Progress</h3>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-muted-foreground">Overall</span>
                        <span class="text-lg font-extrabold text-client-accent">{{ progress }}%</span>
                    </div>
                    <div class="h-3 rounded-full overflow-hidden" style="background:#f0eef9;">
                        <div class="h-full rounded-full transition-all duration-700"
                            style="background:linear-gradient(90deg,#7b63ff,#5132e0);"
                            :style="`width:${progress}%`" />
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs font-medium text-muted-foreground">
                        <span>Started: {{ project.start_date || '—' }}</span>
                        <span>Handover: {{ project.handover_date || '—' }}</span>
                    </div>
                </div>

                <!-- CTA -->
                <div class="rounded-2xl overflow-hidden border border-[#e3daff]" style="background:linear-gradient(135deg,#efeafc,#e7e0ff);">
                    <div class="p-5">
                        <div class="text-sm font-bold text-[#3a25b0] mb-1.5">Interested in this project?</div>
                        <p class="text-xs font-medium text-[#6a5fae] leading-relaxed mb-4">Calculate your mortgage or contact our sales team for more details.</p>
                        <Link :href="route('client.mortgage')"
                            class="flex items-center justify-center gap-2 text-sm font-bold py-2.5 rounded-xl text-white"
                            style="background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 8px 18px -6px rgba(81,50,224,.45);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M8 2.5v4M16 2.5v4M3 9h18M8 13h8M8 16.5h5"/></svg>
                            Calculate Mortgage
                        </Link>
                    </div>
                </div>

            </aside>

        </div>

        <!-- ── Units (full width) ────────────────────────────────── -->
        <div v-if="project.units?.length" class="mt-5 rounded-2xl border border-[#ededf3] dark:border-white/[0.06] bg-client-surface-card shadow-sm p-6">
            <div class="flex items-center justify-between gap-4 flex-wrap mb-4">
                <h2 class="text-base font-bold text-foreground">Units — {{ project.name }}</h2>
            </div>

            <!-- Filter tabs -->
            <div class="flex items-center gap-2 flex-wrap mb-5">
                <Button v-for="f in unitFilters" :key="f.key" type="button" size="sm" variant="outline"
                    class="rounded-xl text-xs font-bold border-[#ededf3] dark:border-white/[0.08]"
                    :class="activeUnitFilter === f.key
                        ? 'bg-client-accent text-on-gold border-client-accent hover:bg-client-accent/90 hover:text-on-gold'
                        : 'text-muted-foreground hover:text-client-accent'"
                    @click="activeUnitFilter = f.key">
                    {{ f.label }} ({{ f.count }})
                </Button>
            </div>

            <!-- Units grouped by floor -->
            <div class="flex flex-col gap-6">
                <div v-for="group in unitsByFloor" :key="group.floor">
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="flex h-6 w-6 flex-none items-center justify-center rounded-md bg-[#efeafc] text-client-accent text-xs font-extrabold">
                            {{ group.floor === '—' ? '?' : group.floor }}
                        </span>
                        <h3 class="text-sm font-bold text-foreground">
                            {{ group.floor === '—' ? 'Floor Unassigned' : group.floor == 0 ? 'Ground Floor' : `Floor ${group.floor}` }}
                        </h3>
                        <span class="text-xs font-semibold text-muted-foreground">({{ group.units.length }} unit{{ group.units.length > 1 ? 's' : '' }})</span>
                    </div>

                    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));">
                        <Card v-for="u in group.units" :key="u.id"
                            class="bg-client-surface-card border-[#f0f0f5] dark:border-white/[0.06] shadow-sm hover:shadow-md hover:border-client-accent/30 transition-all">
                            <CardContent class="p-4 flex flex-col gap-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-sm font-extrabold text-foreground truncate">Unit {{ u.unit_number }}</div>
                                        <div class="text-xs text-muted-foreground font-medium mt-0.5 truncate">
                                            {{ u.type_label }}
                                            <span v-if="u.bedrooms"> · {{ u.bedrooms }} Bed</span>
                                            <span v-if="u.bathrooms"> · {{ u.bathrooms }} Bath</span>
                                        </div>
                                    </div>
                                    <Badge :variant="statusBadgeVariant[u.status_color] ?? 'secondary'" class="flex-none">
                                        {{ u.status_label }}
                                    </Badge>
                                </div>

                                <div v-if="u.size_sqft" class="inline-flex items-center gap-1 text-xs text-muted-foreground font-medium">
                                    <Ruler :size="13" /> {{ Number(u.size_sqft).toLocaleString() }} sqft
                                </div>

                                <div class="pt-3 border-t border-[#f0f0f5] dark:border-white/[0.06] text-sm font-extrabold text-foreground" style="letter-spacing:-0.01em;">
                                    {{ u.price ? fmtBDT(u.price) : '—' }}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            <p v-if="!filteredUnits.length" class="text-sm text-muted-foreground text-center py-6">No units match this filter.</p>
        </div>

        <!-- Footer -->
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
