<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';

const props = defineProps({
    kpis:               { type: Array,  required: true },
    vision:             { type: Object, required: true },
    upcoming_projects:  { type: Array,  required: true },
    engagement_stats:   { type: Array,  required: true },
    activities:         { type: Array,  required: true },
    feedback:           { type: Object, required: true },
    roadmap:            { type: Array,  required: true },
    growth:             { type: Object, required: true },
    sustainability:     { type: Object, required: true },
    impact:             { type: Array,  required: true },
    future_highlights:  { type: Array,  required: true },
});

// ── Icon paths (design-only, keyed by the `icon` field coming from the JSON) ──
const icons = {
    communities: '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
    residents:   '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/>',
    green:       '<path d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6"/>',
    csr:         '<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 000-7.78z"/>',
    future:      '<path d="M3 21h18M5 21V7l8-4 7 4v14M9 9h.01M9 13h.01M9 17h.01"/>',
    wallet:      '<path d="M4 7h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V8a1 1 0 011-1z"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2M12 12h.01"/>',
    growth:      '<path d="M3 17l6-6 4 4 7-7M14 8h6v6"/>',
    spark:       '<path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4L12 3z"/><path d="M19 15l.6 1.6 1.6.6-1.6.6-.6 1.6-.6-1.6-1.6-.6z"/>',
    calendar:    '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/>',
    clock:       '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    heart:       '<path d="M12 21s-7-4.3-7-10a4 4 0 017-2.6A4 4 0 0119 11c0 5.7-7 10-7 10z"/>',
    education:   '<path d="M4 4v15a2 2 0 002 2h13V6a2 2 0 00-2-2H6a2 2 0 00-2 2"/><path d="M19 16H6"/>',
    housing:     '<path d="M3 11l9-7 9 7"/><path d="M5 10v9h14v-9"/>',
    smart:       '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"/>',
    women:       '<circle cx="12" cy="8" r="4"/><path d="M5 21v-1a6 6 0 0114 0v1"/>',
    lightning:   '<path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/>',
    inclusion:   '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20 15 15 0 010-20z"/>',
    jobs:        '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/>',
    business:    '<path d="M3 9l1.5-5h15L21 9M5 9v11h14V9M3 9h18"/>',
    roads:       '<circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h6a4 4 0 000-8h-4a4 4 0 010-8h6"/>',
    waste:       '<path d="M3 6h18M19 6l-1 14H6L5 6M8 6V4h8v2M10 11v5M14 11v5"/>',
};

// ── Population / happiness sparklines (viewBox 0 0 320 90, area + line) ──────
function sparkPath(points, w, h) {
    const step = w / (points.length - 1);
    const max = Math.max(...points);
    return points.map((p, i) => `${i === 0 ? 'M' : 'L'}${i * step} ${h - (p / max) * h}`).join(' ');
}
function sparkArea(points, w, h) {
    return `${sparkPath(points, w, h)} L${w} ${h} L0 ${h} Z`;
}

// ── Sustainability donut (r=60) ───────────────────────────────────────────────
const donutC = 2 * Math.PI * 60;
const sustainabilityDash = computed(() => {
    const filled = (props.sustainability.overall_pct / 100) * donutC;
    return `${filled.toFixed(1)} ${(donutC - filled).toFixed(1)}`;
});

// ── Add Initiative — local-only state, nothing is sent to the server ─────────
// Custom initiatives are appended to the Future Roadmap grid and persisted to
// this browser's localStorage so they survive a page reload, but never reach
// the database.
const STORAGE_KEY = 'admin_community_custom_initiatives';

function loadCustomInitiatives() {
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

const customInitiatives = ref(loadCustomInitiatives());
const combinedRoadmap = computed(() => [...props.roadmap, ...customInitiatives.value]);

const colorChoices = [
    { bg: '#F1ECFF', color: '#5B3DF5' },
    { bg: '#E6F7EE', color: '#22C55E' },
    { bg: '#E8F0FF', color: '#3B82F6' },
    { bg: '#FFF3E0', color: '#F59E0B' },
    { bg: '#FDE8E8', color: '#EF4444' },
    { bg: '#CCFBF1', color: '#0D9488' },
];
const iconChoices = ['spark', 'green', 'smart', 'education', 'heart', 'housing', 'lightning', 'inclusion'];

const showAddInitiative = ref(false);
const initiativeForm = ref({ title: '', desc: '', icon: 'spark', color: colorChoices[0] });

function openAddInitiative() {
    initiativeForm.value = { title: '', desc: '', icon: 'spark', color: colorChoices[0] };
    showAddInitiative.value = true;
}
function submitInitiative() {
    if (!initiativeForm.value.title.trim()) return;
    customInitiatives.value.push({
        title: initiativeForm.value.title.trim(),
        desc: initiativeForm.value.desc.trim(),
        icon: initiativeForm.value.icon,
        bg: initiativeForm.value.color.bg,
        color: initiativeForm.value.color.color,
    });
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(customInitiatives.value));
    showAddInitiative.value = false;
}
</script>

<template>
    <Head title="Community & Future Vision" />

    <AdminLayout title="Community & Future Vision" :breadcrumbs="[{ label: 'Admin' }, { label: 'Community & Future Vision' }]">
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2.5 text-[30px] font-extrabold tracking-[-0.6px] text-foreground">
                    Community &amp; Future Vision <span class="text-2xl">💜</span>
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">Building better communities today for a sustainable tomorrow.</p>
            </div>
            <div class="flex items-center gap-3">
                <Button @click="openAddInitiative" class="gap-2 rounded-xl bg-admin-accent px-[18px] py-[11px] text-[13.5px] font-semibold text-white hover:bg-admin-accent/90">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Initiative
                </Button>
                <Button variant="outline" class="gap-2 rounded-xl px-4 py-[11px] text-[13.5px] font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg>
                    Vision Settings
                </Button>
            </div>
        </div>

        <!-- KPI row -->
        <div class="mb-6 grid grid-cols-1 gap-[18px] sm:grid-cols-3 lg:grid-cols-6">
            <div v-for="k in kpis" :key="k.key" class="rounded-[18px] border border-border bg-white p-[18px] shadow-card transition-all hover:-translate-y-[3px] hover:shadow-card-hover">
                <div class="mb-3 flex items-center gap-2.5">
                    <div class="flex h-[38px] w-[38px] flex-none items-center justify-center rounded-[11px]" :style="{ background: k.bg, color: k.color }">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[k.icon]" />
                    </div>
                    <span class="text-xs font-semibold leading-[1.25] text-muted-foreground">{{ k.label }}</span>
                </div>
                <div class="text-[30px] font-extrabold leading-none tracking-[-1px] text-foreground">{{ k.value }}</div>
                <div class="mt-3 flex items-center gap-1 text-xs">
                    <span class="flex items-center gap-[3px] font-bold text-green-500">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                        {{ k.change }}%
                    </span>
                    <span class="text-slate-400">vs last year</span>
                </div>
            </div>
        </div>

        <!-- Body grid -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="flex min-w-0 flex-col gap-6">

                <!-- Top row -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.25fr_1fr]">

                    <!-- Our Community Vision -->
                    <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                        <div class="mb-3.5 text-[17px] font-bold text-foreground">Our Community Vision</div>
                        <div class="grid flex-1 grid-cols-1 gap-[18px] sm:grid-cols-[1fr_0.9fr]">
                            <div class="flex min-w-0 flex-col">
                                <p class="mb-3.5 text-[12.5px] leading-[1.65] text-muted-foreground">{{ vision.text }}</p>
                                <div class="flex flex-1 flex-col gap-3">
                                    <div v-for="pt in vision.points" :key="pt.title" class="flex items-start gap-[11px]">
                                        <div class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px]" :style="{ background: pt.bg, color: pt.color }">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[pt.icon]" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-[13px] font-bold text-foreground">{{ pt.title }}</div>
                                            <div class="mt-px text-[11.5px] text-slate-400">{{ pt.desc }}</div>
                                        </div>
                                    </div>
                                </div>
                                <a class="mt-4 inline-flex cursor-pointer items-center gap-[7px] text-[12.5px] font-semibold text-admin-accent">
                                    View Our Vision Document
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </a>
                            </div>
                            <img
                                src="https://adishankar.org/wp-content/uploads/2024/03/team-spirit-2447163_1920-800x450-1.jpg"
                                alt="Our Community Vision"
                                class="min-h-[300px] w-full rounded-2xl object-cover"
                            />
                        </div>
                    </div>

                    <!-- Upcoming Community Projects -->
                    <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                        <div class="mb-1 flex items-center justify-between">
                            <div class="text-[17px] font-bold text-foreground">Upcoming Community Projects</div>
                            <a class="cursor-pointer text-xs font-semibold text-admin-accent">View All</a>
                        </div>
                        <div class="relative mt-1.5 flex-1">
                            <div class="absolute bottom-[18px] left-[60px] top-[18px] w-[2px] bg-border" />
                            <div v-for="p in upcoming_projects" :key="p.title" class="grid grid-cols-[48px_24px_1fr] items-center py-[9px]">
                                <div class="leading-[1.15]">
                                    <div class="text-[12.5px] font-extrabold text-foreground">{{ p.year }}</div>
                                    <div class="text-[11px] font-semibold text-slate-400">{{ p.quarter }}</div>
                                </div>
                                <div class="flex justify-center">
                                    <span class="z-[1] h-[13px] w-[13px] flex-none rounded-full border-[3px] bg-white" :style="{ borderColor: p.dot_color }" />
                                </div>
                                <div class="flex min-w-0 items-center justify-between gap-2.5">
                                    <div class="min-w-0">
                                        <div class="overflow-hidden text-ellipsis whitespace-nowrap text-[13px] font-bold text-foreground">{{ p.title }}</div>
                                        <div class="mt-px overflow-hidden text-ellipsis whitespace-nowrap text-[11.5px] text-slate-400">{{ p.sub }}</div>
                                    </div>
                                    <div class="flex flex-none flex-col items-end gap-1">
                                        <Badge variant="outline" class="whitespace-nowrap rounded-[20px] border-transparent px-[9px] py-[3px] text-[10.5px] font-bold" :style="{ background: p.status_bg, color: p.status_color }">{{ p.status }}</Badge>
                                        <span class="text-[11px] font-bold text-muted-foreground">{{ p.pct }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Separator class="mt-2" />
                        <a class="mt-3.5 flex cursor-pointer items-center justify-center gap-[7px] text-[12.5px] font-semibold text-admin-accent">
                            View Full Roadmap
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Middle row -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.6fr_1fr] lg:items-stretch">

                    <!-- Community Engagement -->
                    <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                        <div class="mb-3.5 text-[17px] font-bold text-foreground">Community Engagement</div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div v-for="s in engagement_stats" :key="s.label" class="rounded-[14px] border border-border p-[13px]">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-[9px]" :style="{ background: s.bg, color: s.color }">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[s.icon]" />
                                    </div>
                                    <span class="text-[10.5px] font-semibold text-slate-400">{{ s.label }}</span>
                                </div>
                                <div class="mt-2.5 flex items-end justify-between">
                                    <div class="text-[22px] font-extrabold leading-none tracking-[-0.5px] text-foreground">{{ s.value }}</div>
                                    <span class="flex items-center gap-[2px] text-[11px] font-bold text-green-500">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                                        {{ s.change }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-0.5 mt-[18px] text-[13px] font-bold text-foreground">Recent Community Activities</div>
                        <div class="flex flex-col">
                            <template v-for="(a, i) in activities" :key="a.title">
                                <Separator v-if="i > 0" />
                                <div class="flex items-center gap-[13px] py-[11px] transition-colors hover:bg-muted/40">
                                    <div class="flex h-[46px] w-[46px] flex-none items-center justify-center rounded-[11px] text-admin-accent" :style="{ background: a.bg }">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" v-html="icons.green" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="overflow-hidden text-ellipsis whitespace-nowrap text-[13px] font-bold text-foreground">{{ a.title }}</div>
                                        <div class="mt-px overflow-hidden text-ellipsis whitespace-nowrap text-[11.5px] text-slate-400">{{ a.desc }}</div>
                                    </div>
                                    <div class="flex-none whitespace-nowrap text-[11.5px] text-slate-400">{{ a.date }}</div>
                                    <div class="flex min-w-[42px] flex-none items-center justify-end gap-1 text-[11.5px] font-bold text-red-500">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 21s-7-4.3-7-10a4 4 0 017-2.6A4 4 0 0119 11c0 5.7-7 10-7 10z"/></svg>
                                        {{ a.likes }}
                                    </div>
                                </div>
                            </template>
                        </div>
                        <a class="mt-auto flex cursor-pointer items-center justify-center gap-[7px] pt-3.5 text-[12.5px] font-semibold text-admin-accent">
                            View All Activities
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>

                    <!-- Community Feedback -->
                    <div class="flex min-w-0 flex-col rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                        <div class="flex items-center justify-between">
                            <div class="text-[17px] font-bold text-foreground">Community Feedback</div>
                            <div class="flex cursor-pointer items-center gap-1.5 rounded-[9px] border border-border px-2.5 py-1.5 text-[11px] font-semibold text-foreground/80">
                                This Month
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9AA3B4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </div>
                        </div>
                        <div class="mt-[18px] flex items-center gap-2.5">
                            <div class="text-[44px] font-extrabold leading-none tracking-[-1.5px] text-foreground">{{ feedback.average }}</div>
                            <div class="pb-1 text-sm font-bold text-slate-400">/ 5</div>
                            <div class="ml-1.5 flex flex-col gap-[3px]">
                                <div class="flex gap-0.5 text-[#F5B100]">
                                    <svg v-for="n in 5" :key="n" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" :opacity="n <= Math.round(feedback.average) ? 1 : 0.45"><path d="M12 2l2.9 6.3 6.9.6-5.2 4.5 1.6 6.8L12 17.3 5.8 20.7l1.6-6.8L2.2 8.9l6.9-.6z"/></svg>
                                </div>
                                <div class="text-[11px] text-slate-400">Based on {{ feedback.review_count.toLocaleString() }} reviews</div>
                            </div>
                        </div>
                        <div class="mt-5 flex flex-1 flex-col gap-3">
                            <div v-for="b in feedback.breakdown" :key="b.star" class="flex items-center gap-2.5 text-[11.5px]">
                                <span class="w-[46px] flex-none text-muted-foreground">{{ b.star }}</span>
                                <div class="h-2 flex-1 overflow-hidden rounded-[6px] bg-muted">
                                    <div class="h-full rounded-[6px]" :style="{ width: b.pct + '%', background: b.color }" />
                                </div>
                                <span class="w-[34px] flex-none text-right font-bold text-foreground/80">{{ b.pct }}%</span>
                            </div>
                        </div>
                        <Separator class="mt-[18px]" />
                        <a class="mt-3.5 flex cursor-pointer items-center justify-center gap-[7px] text-[12.5px] font-semibold text-admin-accent">
                            View All Feedback
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Future Roadmap & Key Initiatives -->
                <div class="rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-[17px] font-bold text-foreground">Future Roadmap &amp; Key Initiatives</div>
                        <a class="cursor-pointer text-xs font-semibold text-admin-accent">Manage</a>
                    </div>
                    <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-4 xl:grid-cols-8">
                        <div
                            v-for="r in combinedRoadmap" :key="r.title"
                            class="flex flex-col items-center gap-[9px] rounded-[14px] border border-border px-2.5 py-4 text-center transition-all hover:-translate-y-[3px] hover:[background:var(--hbg)] hover:[border-color:var(--hborder)]"
                            :style="{ '--hbg': r.bg, '--hborder': r.color }"
                        >
                            <div class="flex h-10 w-10 items-center justify-center rounded-[11px]" :style="{ background: r.bg, color: r.color }">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[r.icon]" />
                            </div>
                            <div class="text-[12.5px] font-bold text-foreground">{{ r.title }}</div>
                            <div class="text-[10.5px] leading-[1.4] text-slate-400">{{ r.desc }}</div>
                        </div>
                    </div>
                    <a class="mt-[18px] flex cursor-pointer items-center justify-center gap-[7px] text-[12.5px] font-semibold text-admin-accent">
                        View Full Future Vision Plan
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <!-- Community Growth -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                        <div class="mb-1 flex items-center justify-between">
                            <div class="text-[15px] font-bold text-foreground">Population Growth</div>
                            <span class="flex items-center gap-[3px] text-xs font-bold text-green-500">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                                {{ growth.population.change }}%
                            </span>
                        </div>
                        <div class="text-[26px] font-extrabold tracking-[-1px] text-foreground">{{ growth.population.value }}</div>
                        <svg viewBox="0 0 320 90" preserveAspectRatio="none" class="mt-2 h-20 w-full">
                            <defs><linearGradient id="commPop" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#5B3DF5" stop-opacity="0.22"/><stop offset="100%" stop-color="#5B3DF5" stop-opacity="0"/></linearGradient></defs>
                            <path :d="sparkArea(growth.population.points, 320, 90)" fill="url(#commPop)" />
                            <path :d="sparkPath(growth.population.points, 320, 90)" fill="none" stroke="#5B3DF5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <a class="mt-2 flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-admin-accent">
                            View Report
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                    <div class="rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                        <div class="mb-1 flex items-center justify-between">
                            <div class="text-[15px] font-bold text-foreground">Community Happiness</div>
                            <span class="flex items-center gap-[3px] text-xs font-bold text-green-500">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                                {{ growth.happiness.change }}%
                            </span>
                        </div>
                        <div class="text-[26px] font-extrabold tracking-[-1px] text-foreground">{{ growth.happiness.value }}</div>
                        <svg viewBox="0 0 320 90" preserveAspectRatio="none" class="mt-2 h-20 w-full">
                            <defs><linearGradient id="commHap" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#22C55E" stop-opacity="0.22"/><stop offset="100%" stop-color="#22C55E" stop-opacity="0"/></linearGradient></defs>
                            <path :d="sparkArea(growth.happiness.points, 320, 90)" fill="url(#commHap)" />
                            <path :d="sparkPath(growth.happiness.points, 320, 90)" fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <a class="mt-2 flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-admin-accent">
                            View Report
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="flex min-w-0 flex-col gap-6">

                <!-- Sustainability Goals -->
                <div class="rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                    <div class="flex items-center justify-between">
                        <div class="text-[17px] font-bold text-foreground">Sustainability Goals</div>
                        <a class="cursor-pointer text-xs font-semibold text-admin-accent">Details</a>
                    </div>
                    <div class="my-3.5 flex justify-center">
                        <div class="relative h-[150px] w-[150px]">
                            <svg width="150" height="150" viewBox="0 0 150 150" style="transform:rotate(-90deg);">
                                <defs><linearGradient id="commSus" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#34D399"/><stop offset="100%" stop-color="#15803D"/></linearGradient></defs>
                                <circle cx="75" cy="75" r="60" fill="none" stroke="#F1F4F9" stroke-width="16"/>
                                <circle cx="75" cy="75" r="60" fill="none" stroke="url(#commSus)" stroke-width="16" stroke-linecap="round" :stroke-dasharray="sustainabilityDash"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-[30px] font-extrabold tracking-[-1px] text-foreground">{{ sustainability.overall_pct }}%</div>
                                <div class="mt-px text-[10.5px] text-muted-foreground">Overall Progress</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-col gap-[13px]">
                        <div v-for="g in sustainability.goals" :key="g.label">
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-[12.5px] font-semibold text-foreground/80">{{ g.label }}</span>
                                <span class="text-xs font-bold text-foreground">{{ g.pct }}%</span>
                            </div>
                            <div class="h-[7px] overflow-hidden rounded-[6px] bg-muted">
                                <div class="h-full rounded-[6px]" :style="{ width: g.pct + '%', background: g.color }" />
                            </div>
                        </div>
                    </div>
                    <Separator class="mt-[18px]" />
                    <a class="mt-3.5 flex cursor-pointer items-center justify-center gap-[7px] text-[12.5px] font-semibold text-admin-accent">
                        View Sustainability Report
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <!-- Community Impact -->
                <div class="rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                    <div class="mb-3.5 flex items-center justify-between">
                        <div class="text-[17px] font-bold text-foreground">Community Impact</div>
                        <div class="flex cursor-pointer items-center gap-1.5 rounded-[9px] border border-border px-2.5 py-[5px] text-[11px] font-semibold text-foreground/80">
                            This Year
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9AA3B4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="im in impact" :key="im.label" class="rounded-[13px] border border-border p-[13px]">
                            <div class="mb-2 flex h-[30px] w-[30px] items-center justify-center rounded-[9px]" :style="{ background: im.bg, color: im.color }">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[im.icon]" />
                            </div>
                            <div class="text-[19px] font-extrabold tracking-[-0.5px] text-foreground">{{ im.value }}</div>
                            <div class="mt-0.5 flex items-center justify-between">
                                <div class="text-[10.5px] text-slate-400">{{ im.label }}</div>
                                <div class="text-[10.5px] font-bold text-green-500">+{{ im.change }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Future Highlights -->
                <div class="rounded-[18px] border border-border bg-white p-[22px] shadow-card">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-[17px] font-bold text-foreground">Future Highlights</div>
                        <a class="cursor-pointer text-xs font-semibold text-admin-accent">View All</a>
                    </div>
                    <div class="flex flex-col">
                        <template v-for="(f, i) in future_highlights" :key="f.title">
                            <Separator v-if="i > 0" />
                            <div class="flex items-center gap-3 py-[11px]">
                                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: f.bg, color: f.color }">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[f.icon]" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[13px] font-bold text-foreground">{{ f.title }}</div>
                                    <div class="mt-px overflow-hidden text-ellipsis whitespace-nowrap text-[11px] text-slate-400">{{ f.desc }}</div>
                                </div>
                                <span class="flex-none text-xs font-bold text-muted-foreground">{{ f.year }}</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Initiative dialog — local state / localStorage only, nothing is sent to the server -->
        <Dialog v-model:open="showAddInitiative">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Add Initiative</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitInitiative" class="space-y-4 px-6 pb-2">

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Title</Label>
                        <Input v-model="initiativeForm.title" placeholder="e.g. Clean Water Access" required />
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Description</Label>
                        <Textarea v-model="initiativeForm.desc" rows="2" placeholder="Short description of this initiative..." />
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Icon</Label>
                        <Select v-model="initiativeForm.icon">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="ic in iconChoices" :key="ic" :value="ic">{{ ic }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Color</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="c in colorChoices" :key="c.color" type="button"
                                class="flex h-9 w-9 items-center justify-center rounded-[10px] border-2 transition"
                                :style="{ background: c.bg, borderColor: initiativeForm.color.color === c.color ? c.color : 'transparent' }"
                                @click="initiativeForm.color = c"
                            >
                                <svg v-if="initiativeForm.color.color === c.color" width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="c.color" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </button>
                        </div>
                    </div>

                    <DialogFooter class="!px-0 pt-2">
                        <Button type="button" variant="outline" @click="showAddInitiative = false">Cancel</Button>
                        <Button type="submit" class="bg-admin-accent text-white hover:bg-admin-accent/90">Save Initiative</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
