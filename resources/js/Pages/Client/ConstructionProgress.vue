<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

// ─── Static dummy data ────────────────────────────────────────────────────────
const DUMMY_PROPERTIES = [
    {
        id: 1,
        project_name: 'Lake View Residence',
        unit_number: 'A-702',
        type_label: 'Apartment',
        floor: 7,
        location: 'Gulshan, Dhaka',
        status: 'purchased',
        status_label: 'Purchased',
        status_color: 'green',
        start_date: 'Jan 2023',
        expected_completion: 'Dec 2026',
        expected_handover: 'Mar 2027',
        overall_pct: 72,
        days_remaining: 180,
        current_phase: 'Finishing & Fixtures',
        developer: 'HomeVerse Developers Ltd.',
        contractor: 'BuildCraft Construction',
        supervisor: 'Engr. Rafiq Hossain',
        phases: [
            {
                name: 'Foundation & Piling',
                pct: 100, status: 'completed',
                date_start: 'Jan 2023', date_end: 'Apr 2023',
                tasks: ['Soil testing & survey', 'Pile driving', 'Foundation casting', 'Waterproofing'],
            },
            {
                name: 'Structural Works',
                pct: 100, status: 'completed',
                date_start: 'Apr 2023', date_end: 'Oct 2023',
                tasks: ['Column & beam casting', 'Slab work (floors 1–4)', 'Slab work (floors 5–8)', 'Structural inspection'],
            },
            {
                name: 'Brickwork & Masonry',
                pct: 100, status: 'completed',
                date_start: 'Oct 2023', date_end: 'Mar 2024',
                tasks: ['External brick walls', 'Internal partition walls', 'Staircase construction', 'Lift shaft'],
            },
            {
                name: 'Plumbing & Electrical (MEP)',
                pct: 100, status: 'completed',
                date_start: 'Mar 2024', date_end: 'Aug 2024',
                tasks: ['Plumbing rough-in', 'Electrical conduit', 'HVAC ducting', 'Fire suppression'],
            },
            {
                name: 'Plastering & Flooring',
                pct: 85, status: 'completed',
                date_start: 'Aug 2024', date_end: 'Jan 2025',
                tasks: ['Internal plastering', 'External rendering', 'Floor screed', 'Tile fixing (common areas)'],
            },
            {
                name: 'Finishing & Fixtures',
                pct: 45, status: 'active',
                date_start: 'Jan 2025', date_end: 'Sep 2026',
                tasks: ['Door & window frames', 'Bathroom fixtures', 'Kitchen units', 'Paint & final finishes'],
            },
            {
                name: 'Handover & Inspection',
                pct: 0, status: 'upcoming',
                date_start: 'Oct 2026', date_end: 'Mar 2027',
                tasks: ['Final snag inspection', 'Utility connections', 'Title deed transfer', 'Key handover'],
            },
        ],
        updates: [
            { id: 1, date: '28 Jun 2026', title: 'Floor 6 & 7 bathroom tiling completed', body: 'All bathroom tiles for floors 6 and 7 have been laid and grouted. Quality inspection passed. Fixture installation commences next week.', icon_color: 'rgb(198,161,91)' },
            { id: 2, date: '14 Jun 2026', title: 'Main entrance lobby marble flooring installed', body: 'Premium white marble flooring has been installed in the ground floor lobby and reception area. Polishing scheduled for July 1st.', icon_color: 'hsl(var(--success))' },
            { id: 3, date: '02 Jun 2026', title: 'Elevator installation in progress', body: 'Two high-speed elevators are being installed. Shaft work is complete. Cab installation expected to finish by end of June.', icon_color: 'hsl(var(--warning))' },
            { id: 4, date: '18 May 2026', title: 'External facade cladding 80% complete', body: 'The glass and aluminium composite panel cladding on the north and east facades is now 80% complete. South facade starts next week.', icon_color: 'rgb(198,161,91)' },
        ],
    },
    {
        id: 2,
        project_name: 'Sky Tower Gulshan',
        unit_number: 'B-1204',
        type_label: 'Penthouse',
        floor: 12,
        location: 'Gulshan 2, Dhaka',
        status: 'purchased',
        status_label: 'Purchased',
        status_color: 'green',
        start_date: 'Jun 2022',
        expected_completion: 'Jun 2026',
        expected_handover: 'Sep 2026',
        overall_pct: 91,
        days_remaining: 62,
        current_phase: 'Finishing & Fixtures',
        developer: 'Skyline Properties Ltd.',
        contractor: 'Prime Construction Co.',
        supervisor: 'Engr. Nasrin Akter',
        phases: [
            {
                name: 'Foundation & Piling',
                pct: 100, status: 'completed',
                date_start: 'Jun 2022', date_end: 'Sep 2022',
                tasks: ['Soil testing & survey', 'Pile driving', 'Foundation casting', 'Waterproofing'],
            },
            {
                name: 'Structural Works',
                pct: 100, status: 'completed',
                date_start: 'Sep 2022', date_end: 'Mar 2023',
                tasks: ['Column & beam casting', 'Slab work (floors 1–6)', 'Slab work (floors 7–12)', 'Structural inspection'],
            },
            {
                name: 'Brickwork & Masonry',
                pct: 100, status: 'completed',
                date_start: 'Mar 2023', date_end: 'Aug 2023',
                tasks: ['External brick walls', 'Internal partition walls', 'Staircase construction', 'Lift shaft'],
            },
            {
                name: 'Plumbing & Electrical (MEP)',
                pct: 100, status: 'completed',
                date_start: 'Aug 2023', date_end: 'Jan 2024',
                tasks: ['Plumbing rough-in', 'Electrical conduit', 'HVAC ducting', 'Fire suppression'],
            },
            {
                name: 'Plastering & Flooring',
                pct: 100, status: 'completed',
                date_start: 'Jan 2024', date_end: 'Jun 2024',
                tasks: ['Internal plastering', 'External rendering', 'Floor screed', 'Tile fixing (common areas)'],
            },
            {
                name: 'Finishing & Fixtures',
                pct: 78, status: 'active',
                date_start: 'Jun 2024', date_end: 'Jun 2026',
                tasks: ['Door & window frames', 'Bathroom fixtures', 'Kitchen units', 'Paint & final finishes'],
            },
            {
                name: 'Handover & Inspection',
                pct: 0, status: 'upcoming',
                date_start: 'Jul 2026', date_end: 'Sep 2026',
                tasks: ['Final snag inspection', 'Utility connections', 'Title deed transfer', 'Key handover'],
            },
        ],
        updates: [
            { id: 1, date: '30 Jun 2026', title: 'Penthouse rooftop terrace waterproofing done', body: 'Rooftop terrace waterproofing membrane installation is complete. Tiling will begin July 5th. Estimated completion: July 20th.', icon_color: 'rgb(198,161,91)' },
            { id: 2, date: '20 Jun 2026', title: 'Smart home wiring installation complete', body: 'All smart home control wiring, including lighting automation, climate control and security system, has been installed in all units.', icon_color: 'hsl(var(--success))' },
            { id: 3, date: '08 Jun 2026', title: 'Swimming pool structure complete', body: 'The rooftop swimming pool reinforced concrete shell is complete. Waterproofing and mosaic tiling begin next week.', icon_color: 'hsl(var(--warning))' },
        ],
    },
    {
        id: 3,
        project_name: 'Green Valley Dhanmondi',
        unit_number: 'C-304',
        type_label: 'Apartment',
        floor: 3,
        location: 'Dhanmondi, Dhaka',
        status: 'reserved',
        status_label: 'Reserved',
        status_color: 'yellow',
        start_date: 'Mar 2024',
        expected_completion: 'Mar 2028',
        expected_handover: 'Jun 2028',
        overall_pct: 28,
        days_remaining: 636,
        current_phase: 'Brickwork & Masonry',
        developer: 'GreenBuild Properties',
        contractor: 'Delta Construction Ltd.',
        supervisor: 'Engr. Tanvir Islam',
        phases: [
            {
                name: 'Foundation & Piling',
                pct: 100, status: 'completed',
                date_start: 'Mar 2024', date_end: 'Jun 2024',
                tasks: ['Soil testing & survey', 'Pile driving', 'Foundation casting', 'Waterproofing'],
            },
            {
                name: 'Structural Works',
                pct: 100, status: 'completed',
                date_start: 'Jun 2024', date_end: 'Dec 2024',
                tasks: ['Column & beam casting', 'Slab work (floors 1–3)', 'Slab work (floors 4–6)', 'Structural inspection'],
            },
            {
                name: 'Brickwork & Masonry',
                pct: 60, status: 'active',
                date_start: 'Dec 2024', date_end: 'Jun 2025',
                tasks: ['External brick walls', 'Internal partition walls', 'Staircase construction', 'Lift shaft'],
            },
            {
                name: 'Plumbing & Electrical (MEP)',
                pct: 0, status: 'upcoming',
                date_start: 'Jun 2025', date_end: 'Dec 2025',
                tasks: ['Plumbing rough-in', 'Electrical conduit', 'HVAC ducting', 'Fire suppression'],
            },
            {
                name: 'Plastering & Flooring',
                pct: 0, status: 'upcoming',
                date_start: 'Dec 2025', date_end: 'Jun 2026',
                tasks: ['Internal plastering', 'External rendering', 'Floor screed', 'Tile fixing (common areas)'],
            },
            {
                name: 'Finishing & Fixtures',
                pct: 0, status: 'upcoming',
                date_start: 'Jun 2026', date_end: 'Mar 2028',
                tasks: ['Door & window frames', 'Bathroom fixtures', 'Kitchen units', 'Paint & final finishes'],
            },
            {
                name: 'Handover & Inspection',
                pct: 0, status: 'upcoming',
                date_start: 'Mar 2028', date_end: 'Jun 2028',
                tasks: ['Final snag inspection', 'Utility connections', 'Title deed transfer', 'Key handover'],
            },
        ],
        updates: [
            { id: 1, date: '25 Jun 2026', title: 'Floors 4–6 brickwork commenced', body: 'Brickwork for floors 4 through 6 has commenced as scheduled. The masonry team is working across two shifts to stay on track.', icon_color: 'hsl(var(--warning))' },
            { id: 2, date: '10 Jun 2026', title: 'Structural inspection report approved', body: 'The structural audit for floors 1–3 has been approved by RAJUK. All RCC work meets the required standards. Moving to floors 4–6 phase.', icon_color: 'hsl(var(--success))' },
            { id: 3, date: '28 May 2026', title: 'Project kickoff — on schedule', body: 'Construction is progressing on schedule. Foundation and structural work for floors 1–3 completed ahead of timeline. Brickwork starts June 1.', icon_color: 'rgb(198,161,91)' },
        ],
    },
];

// ─── State ────────────────────────────────────────────────────────────────────
const selectedId = ref(DUMMY_PROPERTIES[0].id);
const property   = computed(() => DUMMY_PROPERTIES.find(p => p.id === selectedId.value));

// ─── Helpers ──────────────────────────────────────────────────────────────────
function statusStyle(c) {
    const map = {
        green:  { bg: 'rgba(52,211,153,.15)',  text: 'hsl(var(--success))' },
        yellow: { bg: 'rgba(251,191,36,.15)',  text: 'hsl(var(--warning))' },
        red:    { bg: 'rgba(248,113,113,.15)',  text: 'hsl(var(--destructive))' },
        blue:   { bg: 'rgba(96,165,250,.15)', text: 'hsl(var(--info))' },
    };
    return map[c] || map.blue;
}

function progressColor(pct) {
    if (pct >= 80) return 'hsl(var(--success))';
    if (pct >= 50) return 'rgb(198,161,91)';
    return 'hsl(var(--warning))';
}

function initials(name) {
    return name.split(' ').filter(w => /^[A-Z]/.test(w)).slice(0, 2).map(w => w[0]).join('');
}
</script>

<template>
    <ClientLayout>
        <div>
            <!-- Breadcrumb -->
            <nav style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:hsl(var(--muted-foreground)); margin-bottom:20px;">
                <Link :href="route('client.dashboard')" style="color:hsl(var(--muted-foreground)); text-decoration:none;" class="hover:text-foreground">Home</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                <span style="color:hsl(var(--foreground)); font-weight:600;">Construction Progress</span>
            </nav>

            <!-- Page title -->
            <div style="margin-bottom:24px;">
                <h1 style="font-size:22px; font-weight:800; color:hsl(var(--foreground)); letter-spacing:-0.02em; margin:0 0 4px;">Construction Progress</h1>
                <p style="font-size:13.5px; color:hsl(var(--muted-foreground)); margin:0;">Track live construction phases, milestones and site updates for your properties.</p>
            </div>

            <!-- Property selector pills -->
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:24px;">
                <button
                    v-for="p in DUMMY_PROPERTIES"
                    :key="p.id"
                    @click="selectedId = p.id"
                    :style="selectedId === p.id
                        ? 'background:linear-gradient(100deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-deep))); color:hsl(var(--card)); border:1.5px solid transparent; box-shadow:0 4px 14px -4px rgba(81,50,224,.45);'
                        : 'background:hsl(var(--card)); color:hsl(var(--foreground)); border:1.5px solid hsl(var(--border));'"
                    style="display:inline-flex; align-items:center; gap:9px; padding:9px 16px; border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; transition:all .18s; font-family:inherit;"
                >
                    <span style="width:7px; height:7px; border-radius:50%; flex-shrink:0;"
                        :style="selectedId === p.id ? 'background:rgba(255,255,255,.7)' : 'background:rgb(var(--hv-gold))'"></span>
                    {{ p.project_name }} · {{ p.unit_number }}
                    <span
                        :style="{
                            background: selectedId === p.id ? 'rgba(255,255,255,.18)' : statusStyle(p.status_color).bg,
                            color:      selectedId === p.id ? 'hsl(var(--card))' : statusStyle(p.status_color).text,
                        }"
                        style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px;"
                    >{{ p.status_label }}</span>
                </button>
            </div>

            <!-- Progress Hero -->
            <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:20px 24px; margin-bottom:20px;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:16px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                            <span style="font-size:18px; font-weight:800; color:hsl(var(--foreground));">{{ property.project_name }}</span>
                            <span
                                :style="{ background: statusStyle(property.status_color).bg, color: statusStyle(property.status_color).text }"
                                style="font-size:11.5px; font-weight:700; padding:3px 10px; border-radius:999px;"
                            >{{ property.status_label }}</span>
                        </div>
                        <div style="font-size:13px; color:hsl(var(--muted-foreground));">Unit {{ property.unit_number }} · Floor {{ property.floor }} · {{ property.location }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:32px; font-weight:900; color:hsl(var(--foreground)); line-height:1;" :style="{ color: progressColor(property.overall_pct) }">{{ property.overall_pct }}%</div>
                        <div style="font-size:12px; color:hsl(var(--muted-foreground)); margin-top:2px;">Overall Complete</div>
                    </div>
                </div>
                <!-- Wide progress bar -->
                <div style="height:10px; background:hsl(var(--muted)); border-radius:8px; overflow:hidden; margin-bottom:12px;">
                    <div
                        :style="{ width: property.overall_pct + '%', background: `linear-gradient(90deg, ${progressColor(property.overall_pct)}, ${progressColor(property.overall_pct)}cc)` }"
                        style="height:100%; border-radius:8px; transition:width .5s ease;"
                    ></div>
                </div>
                <!-- Meta row -->
                <div style="display:flex; flex-wrap:wrap; gap:20px; font-size:12.5px; color:hsl(var(--muted-foreground));">
                    <span style="display:flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted-foreground))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Started: <strong style="color:hsl(var(--foreground));">{{ property.start_date }}</strong>
                    </span>
                    <span style="display:flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted-foreground))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Expected Completion: <strong style="color:hsl(var(--foreground));">{{ property.expected_completion }}</strong>
                    </span>
                    <span style="display:flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted-foreground))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Handover: <strong style="color:hsl(var(--foreground));">{{ property.expected_handover }}</strong>
                    </span>
                    <span style="display:flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted-foreground))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        Current Phase: <strong style="color:rgb(var(--brand-text));">{{ property.current_phase }}</strong>
                    </span>
                    <span style="display:flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted-foreground))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <strong style="color:hsl(var(--foreground));">{{ property.days_remaining }}</strong> days remaining
                    </span>
                </div>
            </div>

            <!-- Body: feed + right rail -->
            <div style="display:flex; gap:20px; align-items:flex-start;">

                <!-- Feed -->
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:18px;">

                    <!-- Phase Timeline -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; overflow:hidden;">
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid hsl(var(--muted));">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(106,77,255,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:hsl(var(--foreground));">Construction Phases</div>
                                <div style="font-size:12px; color:hsl(var(--muted-foreground));">{{ property.phases.filter(p => p.status === 'completed').length }} of {{ property.phases.length }} phases complete</div>
                            </div>
                        </div>

                        <div style="padding:8px 20px 20px;">
                            <div v-for="(phase, idx) in property.phases" :key="phase.name" style="display:flex; gap:16px; padding-top:20px;">

                                <!-- Step indicator column -->
                                <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0; width:32px;">
                                    <!-- Icon circle -->
                                    <div style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative; z-index:1;"
                                        :style="phase.status === 'completed'
                                            ? 'background:linear-gradient(135deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-bright))); box-shadow:0 4px 10px -3px rgba(106,77,255,.5);'
                                            : phase.status === 'active'
                                                ? 'background:hsl(var(--card)); border:2.5px solid rgb(var(--hv-gold));'
                                                : 'background:hsl(var(--muted)); border:2px solid hsl(var(--border));'"
                                    >
                                        <!-- Completed: checkmark -->
                                        <svg v-if="phase.status === 'completed'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--card))" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        <!-- Active: pulse dot -->
                                        <span v-else-if="phase.status === 'active'" style="width:10px; height:10px; border-radius:50%; background:rgb(var(--hv-gold));" class="phase-pulse"></span>
                                        <!-- Upcoming: number -->
                                        <span v-else style="font-size:11px; font-weight:700; color:hsl(var(--muted-foreground));">{{ idx + 1 }}</span>
                                    </div>
                                    <!-- Connector line -->
                                    <div v-if="idx < property.phases.length - 1"
                                        style="width:2px; flex:1; min-height:24px; margin-top:4px;"
                                        :style="phase.status === 'completed' ? 'background:linear-gradient(180deg,rgb(var(--hv-gold-bright)),hsl(var(--border)))' : 'background:hsl(var(--muted))'"
                                    ></div>
                                </div>

                                <!-- Phase content -->
                                <div style="flex:1; min-width:0; padding-bottom:4px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-bottom:6px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:14px; font-weight:700;"
                                                :style="phase.status === 'upcoming' ? 'color:hsl(var(--muted-foreground))' : 'color:hsl(var(--foreground))'"
                                            >{{ phase.name }}</span>
                                            <span style="font-size:11px; font-weight:700; padding:2px 9px; border-radius:999px;"
                                                :style="phase.status === 'completed'
                                                    ? 'background:rgba(106,77,255,.1); color:rgb(var(--brand-text));'
                                                    : phase.status === 'active'
                                                        ? 'background:rgba(34,197,94,.1); color:hsl(var(--success));'
                                                        : 'background:hsl(var(--muted)); color:hsl(var(--muted-foreground));'"
                                            >{{ phase.status === 'completed' ? 'Done' : phase.status === 'active' ? 'In Progress' : 'Upcoming' }}</span>
                                        </div>
                                        <span style="font-size:11.5px; color:hsl(var(--muted-foreground)); flex-shrink:0;">{{ phase.date_start }} — {{ phase.date_end }}</span>
                                    </div>

                                    <!-- Progress bar (for active/completed) -->
                                    <div v-if="phase.status !== 'upcoming'" style="height:5px; background:hsl(var(--muted)); border-radius:4px; overflow:hidden; margin-bottom:10px;">
                                        <div
                                            :style="{ width: phase.pct + '%', background: phase.status === 'completed' ? 'linear-gradient(90deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-bright)))' : 'linear-gradient(90deg,hsl(var(--success)),#6EE7B7)' }"
                                            style="height:100%; border-radius:4px; transition:width .4s;"
                                        ></div>
                                    </div>

                                    <!-- Task checklist -->
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px 16px;">
                                        <div v-for="(task, ti) in phase.tasks" :key="ti"
                                            style="display:flex; align-items:center; gap:6px; font-size:12px;"
                                            :style="phase.status === 'upcoming' ? 'color:hsl(var(--muted-foreground) / 0.6)' : 'color:hsl(var(--muted-foreground))'"
                                        >
                                            <svg v-if="phase.status === 'completed' || (phase.status === 'active' && ti < 2)"
                                                width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <svg v-else-if="phase.status === 'active'"
                                                width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="9"/>
                                            </svg>
                                            <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="9"/>
                                            </svg>
                                            {{ task }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Site Updates Feed -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; overflow:hidden;">
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px 14px; border-bottom:1px solid hsl(var(--muted));">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(34,197,94,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--success))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:hsl(var(--foreground));">Site Updates</div>
                                <div style="font-size:12px; color:hsl(var(--muted-foreground));">Latest reports from the construction site</div>
                            </div>
                        </div>

                        <div style="padding:4px 0;">
                            <div v-for="(update, idx) in property.updates" :key="update.id"
                                style="display:flex; gap:14px; padding:16px 20px;"
                                :style="idx < property.updates.length - 1 ? 'border-bottom:1px solid hsl(var(--muted));' : ''"
                            >
                                <!-- Icon -->
                                <div style="width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;"
                                    :style="{ background: update.icon_color + '18' }">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="update.icon_color" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                        <polyline points="9 22 9 12 15 12 15 22"/>
                                    </svg>
                                </div>
                                <!-- Text -->
                                <div style="flex:1; min-width:0;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:4px; flex-wrap:wrap;">
                                        <span style="font-size:13.5px; font-weight:700; color:hsl(var(--foreground));">{{ update.title }}</span>
                                        <span style="font-size:11.5px; color:hsl(var(--muted-foreground)); flex-shrink:0;">{{ update.date }}</span>
                                    </div>
                                    <p style="font-size:12.5px; color:hsl(var(--muted-foreground)); line-height:1.6; margin:0;">{{ update.body }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Rail -->
                <div style="width:300px; flex-shrink:0; display:flex; flex-direction:column; gap:16px;" class="cp-rail">

                    <!-- Next milestone countdown -->
                    <div style="background:linear-gradient(135deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-deep))); border-radius:16px; padding:20px; color:hsl(var(--card)); position:relative; overflow:hidden;">
                        <div style="position:absolute; top:-20px; right:-20px; width:100px; height:100px; border-radius:50%; background:rgba(255,255,255,.06);"></div>
                        <div style="position:absolute; bottom:-30px; right:10px; width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,.04);"></div>
                        <div style="font-size:11.5px; font-weight:700; opacity:.7; text-transform:uppercase; letter-spacing:.06em; margin-bottom:10px;">Next Milestone</div>
                        <div style="font-size:15px; font-weight:800; margin-bottom:6px; line-height:1.3;">{{ property.current_phase }}</div>
                        <div style="font-size:28px; font-weight:900; line-height:1; margin-bottom:4px;">{{ property.days_remaining }}</div>
                        <div style="font-size:12px; opacity:.75; margin-bottom:14px;">days to handover</div>
                        <div style="height:5px; background:rgba(255,255,255,.2); border-radius:4px; overflow:hidden;">
                            <div :style="{ width: property.overall_pct + '%' }" style="height:100%; background:hsl(var(--card)); border-radius:4px; transition:width .5s;"></div>
                        </div>
                        <div style="font-size:11px; opacity:.7; margin-top:6px;">{{ property.overall_pct }}% overall complete</div>
                    </div>

                    <!-- Project Team -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:16px 18px;">
                        <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:14px;">Project Team</div>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div v-for="(member, role) in {
                                'Developer':   { name: property.developer,   color: 'rgb(198,161,91)' },
                                'Contractor':  { name: property.contractor,  color: 'hsl(var(--warning))' },
                                'Site Supervisor': { name: property.supervisor, color: 'hsl(var(--success))' },
                            }" :key="role" style="display:flex; align-items:center; gap:10px;">
                                <div style="width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; color:hsl(var(--card)); flex-shrink:0;"
                                    :style="{ background: member.color }">
                                    {{ initials(member.name) }}
                                </div>
                                <div style="min-width:0; flex:1;">
                                    <div style="font-size:12px; font-weight:600; color:hsl(var(--foreground)); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ member.name }}</div>
                                    <div style="font-size:11px; color:hsl(var(--muted-foreground));">{{ role }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key Documents -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:16px 18px;">
                        <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:12px;">Key Documents</div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <div v-for="doc in [
                                { name:'Approved Building Plan', available: true  },
                                { name:'Structural Inspection Report', available: true  },
                                { name:'RAJUK Approval Certificate', available: true  },
                                { name:'Final Handover Certificate', available: false },
                            ]" :key="doc.name"
                                style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:9px 12px; border:1px solid hsl(var(--muted)); border-radius:10px;"
                                :style="doc.available ? '' : 'opacity:.5'"
                            >
                                <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <span style="font-size:12px; font-weight:500; color:hsl(var(--foreground)); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ doc.name }}</span>
                                </div>
                                <button v-if="doc.available"
                                    style="flex-shrink:0; width:28px; height:28px; border-radius:7px; background:rgba(106,77,255,.08); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v13"/><path d="M7 11l5 5 5-5"/><path d="M3 21h18"/></svg>
                                </button>
                                <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Notify Me -->
                    <div style="background:rgba(198,161,91,0.08); border:1px solid hsl(var(--muted)); border-radius:16px; padding:16px 18px;">
                        <div style="display:flex; align-items:flex-start; gap:12px;">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(106,77,255,.12); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:3px;">Get Notified</div>
                                <div style="font-size:12px; color:hsl(var(--muted-foreground)); line-height:1.5; margin-bottom:12px;">Receive SMS & email alerts on phase completions and site updates.</div>
                                <button style="width:100%; padding:9px; border-radius:10px; background:linear-gradient(100deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-deep))); color:hsl(var(--card)); font-size:12.5px; font-weight:700; border:none; cursor:pointer; font-family:inherit; box-shadow:0 4px 12px -4px rgba(81,50,224,.5);">
                                    Enable Notifications
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<style scoped>
@media (max-width: 1100px) {
    .cp-rail { display: none !important; }
}

@keyframes pulse-ring {
    0%   { transform: scale(1);   opacity: 1; }
    70%  { transform: scale(1.8); opacity: 0; }
    100% { transform: scale(1.8); opacity: 0; }
}

.phase-pulse {
    animation: pulse-ring 1.8s ease-out infinite;
}
</style>
