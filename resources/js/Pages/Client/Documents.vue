<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

// ─── Unit document data ───────────────────────────────────────────────────────
const UNITS = [
    {
        id: 1,
        project_name: 'Lake View Residence',
        unit_number:  'A-702',
        type_label:   '3 Bed Apartment',
        floor:        7,
        location:     'Gulshan, Dhaka',
        status:       'purchased',
        status_label: 'Purchased',
        status_color: 'green',
        size_sqft:    1850,
        categories: [
            {
                id: 'agreements',
                label: 'Agreements & Contracts',
                icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z',
                color: '#6a4dff',
                docs: [
                    { id: 1,  name: 'Booking Form',                   type: 'PDF',  date: '12 Jan 2023', size: '245 KB',  available: true  },
                    { id: 2,  name: 'Booking Agreement',               type: 'PDF',  date: '15 Jan 2023', size: '1.2 MB',  available: true  },
                    { id: 3,  name: 'Sale & Purchase Agreement',       type: 'PDF',  date: '04 Mar 2023', size: '2.8 MB',  available: true  },
                    { id: 4,  name: 'Deed of Agreement (Notarized)',   type: 'PDF',  date: '18 Mar 2023', size: '3.1 MB',  available: true  },
                ],
            },
            {
                id: 'payments',
                label: 'Payment Documents',
                icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z',
                color: '#16a34a',
                docs: [
                    { id: 5,  name: 'Payment Schedule (Full Plan)',    type: 'PDF',  date: '15 Jan 2023', size: '189 KB',  available: true  },
                    { id: 6,  name: 'Booking Token Receipt',           type: 'PDF',  date: '12 Jan 2023', size: '98 KB',   available: true  },
                    { id: 7,  name: 'Down Payment Receipt',            type: 'PDF',  date: '04 Mar 2023', size: '104 KB',  available: true  },
                    { id: 8,  name: 'Installment Receipts (Jan–Jun)',  type: 'XLSX', date: '01 Jul 2026', size: '320 KB',  available: true  },
                    { id: 9,  name: 'Installment Receipts (Jul–Dec)',  type: 'XLSX', date: 'Pending',     size: '—',       available: false, reason: 'Available after Dec 2026' },
                ],
            },
            {
                id: 'technical',
                label: 'Technical Documents',
                icon: 'M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18',
                color: '#3b82f6',
                docs: [
                    { id: 10, name: 'Unit Floor Plan',                 type: 'PDF',  date: '15 Jan 2023', size: '4.2 MB',  available: true  },
                    { id: 11, name: 'Building Layout (All Floors)',    type: 'PDF',  date: '20 Jan 2023', size: '8.6 MB',  available: true  },
                    { id: 12, name: 'RAJUK Approved Plan',             type: 'PDF',  date: '22 Feb 2023', size: '5.1 MB',  available: true  },
                    { id: 13, name: 'Structural Inspection Report',    type: 'PDF',  date: '10 Jun 2026', size: '2.3 MB',  available: true  },
                    { id: 14, name: 'Fire Safety Certificate',         type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after project completion' },
                ],
            },
            {
                id: 'legal',
                label: 'Legal & Ownership',
                icon: 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 0 0 6.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 0 0 6.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                color: '#f59e0b',
                docs: [
                    { id: 15, name: 'Title Acknowledgement Letter',    type: 'PDF',  date: '04 Mar 2023', size: '310 KB',  available: true  },
                    { id: 16, name: 'Power of Attorney (if any)',      type: 'PDF',  date: '04 Mar 2023', size: '520 KB',  available: true  },
                    { id: 17, name: 'Mutation & Registration Slip',    type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after handover' },
                    { id: 18, name: 'Title Deed (Final)',              type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after handover & full payment' },
                ],
            },
        ],
    },
    {
        id: 2,
        project_name: 'Sky Tower Gulshan',
        unit_number:  'B-1204',
        type_label:   'Penthouse',
        floor:        12,
        location:     'Gulshan 2, Dhaka',
        status:       'purchased',
        status_label: 'Purchased',
        status_color: 'green',
        size_sqft:    2900,
        categories: [
            {
                id: 'agreements',
                label: 'Agreements & Contracts',
                icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z',
                color: '#6a4dff',
                docs: [
                    { id: 1,  name: 'Booking Form',                   type: 'PDF',  date: '05 Jun 2022', size: '238 KB',  available: true  },
                    { id: 2,  name: 'Booking Agreement',              type: 'PDF',  date: '10 Jun 2022', size: '1.4 MB',  available: true  },
                    { id: 3,  name: 'Sale & Purchase Agreement',      type: 'PDF',  date: '18 Aug 2022', size: '3.2 MB',  available: true  },
                    { id: 4,  name: 'Deed of Agreement (Notarized)',  type: 'PDF',  date: '02 Sep 2022', size: '3.5 MB',  available: true  },
                ],
            },
            {
                id: 'payments',
                label: 'Payment Documents',
                icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z',
                color: '#16a34a',
                docs: [
                    { id: 5,  name: 'Payment Schedule (Full Plan)',   type: 'PDF',  date: '10 Jun 2022', size: '201 KB',  available: true  },
                    { id: 6,  name: 'Booking Token Receipt',          type: 'PDF',  date: '05 Jun 2022', size: '112 KB',  available: true  },
                    { id: 7,  name: 'Down Payment Receipt',           type: 'PDF',  date: '18 Aug 2022', size: '118 KB',  available: true  },
                    { id: 8,  name: 'Installment Receipts (2022–24)', type: 'XLSX', date: '01 Jan 2025', size: '540 KB',  available: true  },
                    { id: 9,  name: 'Installment Receipts (2025–26)', type: 'XLSX', date: '01 Jul 2026', size: '310 KB',  available: true  },
                ],
            },
            {
                id: 'technical',
                label: 'Technical Documents',
                icon: 'M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18',
                color: '#3b82f6',
                docs: [
                    { id: 10, name: 'Penthouse Floor Plan',           type: 'PDF',  date: '10 Jun 2022', size: '6.8 MB',  available: true  },
                    { id: 11, name: 'Building Layout (All Floors)',   type: 'PDF',  date: '15 Jun 2022', size: '9.2 MB',  available: true  },
                    { id: 12, name: 'RAJUK Approved Plan',            type: 'PDF',  date: '20 Jul 2022', size: '5.8 MB',  available: true  },
                    { id: 13, name: 'Structural Inspection Report',   type: 'PDF',  date: '15 May 2026', size: '2.7 MB',  available: true  },
                    { id: 14, name: 'Fire Safety Certificate',        type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after project completion' },
                ],
            },
            {
                id: 'legal',
                label: 'Legal & Ownership',
                icon: 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 0 0 6.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 0 0 6.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                color: '#f59e0b',
                docs: [
                    { id: 15, name: 'Title Acknowledgement Letter',   type: 'PDF',  date: '02 Sep 2022', size: '298 KB',  available: true  },
                    { id: 16, name: 'Power of Attorney',              type: 'PDF',  date: '02 Sep 2022', size: '488 KB',  available: true  },
                    { id: 17, name: 'Mutation & Registration Slip',   type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after handover' },
                    { id: 18, name: 'Title Deed (Final)',             type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after handover & full payment' },
                ],
            },
        ],
    },
    {
        id: 3,
        project_name: 'Green Valley Dhanmondi',
        unit_number:  'C-304',
        type_label:   '2 Bed Apartment',
        floor:        3,
        location:     'Dhanmondi, Dhaka',
        status:       'reserved',
        status_label: 'Reserved',
        status_color: 'yellow',
        size_sqft:    1250,
        categories: [
            {
                id: 'agreements',
                label: 'Agreements & Contracts',
                icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z',
                color: '#6a4dff',
                docs: [
                    { id: 1,  name: 'Booking Form',                   type: 'PDF',  date: '18 Mar 2024', size: '221 KB',  available: true  },
                    { id: 2,  name: 'Booking Agreement',              type: 'PDF',  date: '22 Mar 2024', size: '1.1 MB',  available: true  },
                    { id: 3,  name: 'Sale & Purchase Agreement',      type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Issued after full down payment' },
                    { id: 4,  name: 'Deed of Agreement (Notarized)',  type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Issued after sale agreement signing' },
                ],
            },
            {
                id: 'payments',
                label: 'Payment Documents',
                icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z',
                color: '#16a34a',
                docs: [
                    { id: 5,  name: 'Payment Schedule (Full Plan)',   type: 'PDF',  date: '22 Mar 2024', size: '177 KB',  available: true  },
                    { id: 6,  name: 'Booking Token Receipt',          type: 'PDF',  date: '18 Mar 2024', size: '91 KB',   available: true  },
                    { id: 7,  name: 'Down Payment Receipt',           type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after down payment' },
                    { id: 8,  name: 'Installment Receipts',           type: 'XLSX', date: 'Pending',     size: '—',       available: false, reason: 'Available after first installment' },
                ],
            },
            {
                id: 'technical',
                label: 'Technical Documents',
                icon: 'M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18',
                color: '#3b82f6',
                docs: [
                    { id: 9,  name: 'Unit Floor Plan',                type: 'PDF',  date: '22 Mar 2024', size: '3.4 MB',  available: true  },
                    { id: 10, name: 'Building Layout',                type: 'PDF',  date: '22 Mar 2024', size: '7.1 MB',  available: true  },
                    { id: 11, name: 'RAJUK Approved Plan',            type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Under government review' },
                    { id: 12, name: 'Structural Inspection Report',   type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after structural phase' },
                ],
            },
            {
                id: 'legal',
                label: 'Legal & Ownership',
                icon: 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 0 0 6.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 0 0 6.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                color: '#f59e0b',
                docs: [
                    { id: 13, name: 'Title Acknowledgement Letter',   type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Issued after sale agreement' },
                    { id: 14, name: 'Mutation & Registration Slip',   type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after handover' },
                    { id: 15, name: 'Title Deed (Final)',             type: 'PDF',  date: 'Pending',     size: '—',       available: false, reason: 'Available after handover & full payment' },
                ],
            },
        ],
    },
];

// ─── State ────────────────────────────────────────────────────────────────────
const selectedId     = ref(UNITS[0].id);
const searchQuery    = ref('');
const activeCategory = ref('all');

const unit = computed(() => UNITS.find(u => u.id === selectedId.value));

const allDocs = computed(() =>
    unit.value.categories.flatMap(c => c.docs.map(d => ({ ...d, categoryId: c.id, categoryLabel: c.label })))
);

const totalDocs     = computed(() => allDocs.value.length);
const availableDocs = computed(() => allDocs.value.filter(d => d.available).length);
const pendingDocs   = computed(() => totalDocs.value - availableDocs.value);

const filteredCategories = computed(() => {
    const q = searchQuery.value.toLowerCase();
    return unit.value.categories
        .filter(c => activeCategory.value === 'all' || c.id === activeCategory.value)
        .map(c => ({
            ...c,
            docs: c.docs.filter(d => !q || d.name.toLowerCase().includes(q)),
        }))
        .filter(c => c.docs.length > 0);
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
function statusStyle(c) {
    return {
        green:  { bg: 'rgba(34,197,94,.12)',  text: '#16a34a' },
        yellow: { bg: 'rgba(234,179,8,.12)',  text: '#b45309' },
    }[c] || { bg: 'rgba(106,77,255,.12)', text: '#6a4dff' };
}

function fileStyle(type) {
    return {
        PDF:  { bg: 'rgba(239,68,68,.1)',   text: '#dc2626', icon: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M11 13h2M11 16h2M8 13h.01M8 16h.01' },
        XLSX: { bg: 'rgba(34,197,94,.1)',   text: '#16a34a', icon: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13l2 2 4-4' },
        IMG:  { bg: 'rgba(59,130,246,.1)',  text: '#2563eb', icon: 'M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2l1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z' },
    }[type] || { bg: 'rgba(154,154,176,.1)', text: '#9a9ab0', icon: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6' };
}

const CATEGORY_LABELS = {
    agreements: 'Agreements',
    payments:   'Payments',
    technical:  'Technical',
    legal:      'Legal',
};
</script>

<template>
    <ClientLayout>
        <div>

            <!-- Breadcrumb -->
            <nav style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#9a9ab0; margin-bottom:20px;">
                <Link :href="route('client.dashboard')" style="color:#9a9ab0; text-decoration:none;" class="hover:text-foreground">Home</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                <span style="color:#3d3d55; font-weight:600;">Documents</span>
            </nav>

            <!-- Page title -->
            <div style="margin-bottom:22px;">
                <h1 style="font-size:22px; font-weight:800; color:#16162a; letter-spacing:-0.02em; margin:0 0 4px;">Documents</h1>
                <p style="font-size:13.5px; color:#7a7a90; margin:0;">All your property documents organized by unit — download anytime.</p>
            </div>

            <!-- Unit selector pills -->
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:22px;">
                <button
                    v-for="u in UNITS" :key="u.id"
                    @click="selectedId = u.id; activeCategory = 'all'; searchQuery = ''"
                    :style="selectedId === u.id
                        ? 'background:linear-gradient(100deg,#6a4dff,#5132e0); color:#fff; border:1.5px solid transparent; box-shadow:0 4px 14px -4px rgba(81,50,224,.45);'
                        : 'background:#fff; color:#3d3d55; border:1.5px solid #e8e6f0;'"
                    style="display:inline-flex; align-items:center; gap:9px; padding:9px 16px; border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; transition:all .18s; font-family:inherit;"
                >
                    <span style="width:7px; height:7px; border-radius:50%; flex-shrink:0;"
                        :style="selectedId === u.id ? 'background:rgba(255,255,255,.7)' : 'background:#6a4dff'"></span>
                    {{ u.project_name }} · {{ u.unit_number }}
                    <span
                        :style="{
                            background: selectedId === u.id ? 'rgba(255,255,255,.2)' : statusStyle(u.status_color).bg,
                            color:      selectedId === u.id ? '#fff'                 : statusStyle(u.status_color).text,
                        }"
                        style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px;"
                    >{{ u.status_label }}</span>
                </button>
            </div>

            <!-- Summary KPIs -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:22px; max-width:540px;">
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:14px 18px;">
                    <div style="font-size:11px; font-weight:600; color:#9a9ab0; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Total</div>
                    <div style="font-size:22px; font-weight:900; color:#16162a; line-height:1;">{{ totalDocs }}</div>
                    <div style="font-size:11.5px; color:#b0b0c0; margin-top:2px;">Documents</div>
                </div>
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:14px 18px;">
                    <div style="font-size:11px; font-weight:600; color:#9a9ab0; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Available</div>
                    <div style="font-size:22px; font-weight:900; color:#16a34a; line-height:1;">{{ availableDocs }}</div>
                    <div style="font-size:11.5px; color:#b0b0c0; margin-top:2px;">Ready to download</div>
                </div>
                <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:14px 18px;">
                    <div style="font-size:11px; font-weight:600; color:#9a9ab0; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Pending</div>
                    <div style="font-size:22px; font-weight:900; color:#f59e0b; line-height:1;">{{ pendingDocs }}</div>
                    <div style="font-size:11.5px; color:#b0b0c0; margin-top:2px;">Not yet available</div>
                </div>
            </div>

            <!-- Body: document list + right rail -->
            <div style="display:flex; gap:20px; align-items:flex-start;">

                <!-- Main document area -->
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:16px;">

                    <!-- Search + category filter bar -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:14px; padding:14px 16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <!-- Search -->
                        <div style="position:relative; flex:1; min-width:180px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9a9ab0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:12px; top:50%; transform:translateY(-50%);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
                            <input
                                v-model="searchQuery"
                                placeholder="Search documents..."
                                style="width:100%; height:38px; padding:0 12px 0 36px; border:1.5px solid #ededf3; border-radius:10px; outline:none; font-family:'Plus Jakarta Sans',system-ui,sans-serif; font-size:13px; color:#16162a; background:#faf9fd; transition:border-color .15s;"
                                class="focus:border-[#6a4dff]"
                            />
                        </div>
                        <!-- Category pills -->
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <button
                                v-for="cat in [{ id: 'all', label: 'All' }, ...unit.categories.map(c => ({ id: c.id, label: CATEGORY_LABELS[c.id] }))]"
                                :key="cat.id"
                                @click="activeCategory = cat.id"
                                :style="activeCategory === cat.id
                                    ? 'background:#6a4dff; color:#fff; border-color:#6a4dff;'
                                    : 'background:#fff; color:#5a5a6e; border-color:#e8e6f0;'"
                                style="padding:6px 14px; border-radius:999px; font-size:12px; font-weight:600; border:1.5px solid; cursor:pointer; font-family:inherit; transition:all .15s;"
                            >{{ cat.label }}</button>
                        </div>
                    </div>

                    <!-- Document categories -->
                    <div v-if="filteredCategories.length > 0" style="display:flex; flex-direction:column; gap:14px;">
                        <div v-for="category in filteredCategories" :key="category.id"
                            style="background:#fff; border:1px solid #ededf3; border-radius:16px; overflow:hidden;">

                            <!-- Category header -->
                            <div style="display:flex; align-items:center; gap:12px; padding:14px 18px; border-bottom:1px solid #f0eff7;">
                                <div style="width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                                    :style="{ background: category.color + '15' }">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" :stroke="category.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path :d="category.icon"/>
                                    </svg>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:13.5px; font-weight:700; color:#16162a;">{{ category.label }}</div>
                                    <div style="font-size:11.5px; color:#9a9ab0; margin-top:1px;">
                                        {{ category.docs.filter(d => d.available).length }} of {{ category.docs.length }} available
                                    </div>
                                </div>
                                <div style="font-size:12px; font-weight:700; padding:4px 10px; border-radius:999px;"
                                    :style="{ background: category.color + '12', color: category.color }">
                                    {{ category.docs.length }} docs
                                </div>
                            </div>

                            <!-- Document rows -->
                            <div>
                                <div
                                    v-for="(doc, idx) in category.docs" :key="doc.id"
                                    style="display:flex; align-items:center; gap:14px; padding:13px 18px; transition:background .15s;"
                                    :style="[
                                        idx < category.docs.length - 1 ? 'border-bottom:1px solid #f5f4fb;' : '',
                                        !doc.available ? 'opacity:.65;' : '',
                                    ]"
                                    class="hover:bg-[#faf9fd]"
                                >
                                    <!-- File type icon -->
                                    <div style="width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                                        :style="{ background: fileStyle(doc.type).bg }">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="fileStyle(doc.type).text" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path :d="fileStyle(doc.type).icon"/>
                                        </svg>
                                    </div>

                                    <!-- Doc info -->
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:13.5px; font-weight:600; color:#16162a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ doc.name }}</div>
                                        <div style="display:flex; align-items:center; gap:10px; margin-top:3px; flex-wrap:wrap;">
                                            <span style="font-size:11px; font-weight:700; padding:2px 7px; border-radius:5px;"
                                                :style="{ background: fileStyle(doc.type).bg, color: fileStyle(doc.type).text }">
                                                {{ doc.type }}
                                            </span>
                                            <span style="font-size:11.5px; color:#9a9ab0;">{{ doc.date }}</span>
                                            <span v-if="doc.available" style="font-size:11.5px; color:#b0b0c0;">{{ doc.size }}</span>
                                            <span v-if="!doc.available && doc.reason" style="font-size:11.5px; color:#f59e0b; font-weight:600;">{{ doc.reason }}</span>
                                        </div>
                                    </div>

                                    <!-- Action -->
                                    <div style="flex-shrink:0;">
                                        <!-- Download button -->
                                        <button v-if="doc.available"
                                            style="display:flex; align-items:center; gap:6px; padding:7px 14px; border-radius:9px; background:rgba(106,77,255,.08); border:1px solid rgba(106,77,255,.2); color:#6a4dff; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; transition:all .15s;"
                                            class="hover:bg-[#6a4dff] hover:text-white hover:border-[#6a4dff]"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v13"/><path d="M7 11l5 5 5-5"/><path d="M3 21h18"/></svg>
                                            Download
                                        </button>
                                        <!-- Locked state -->
                                        <div v-else
                                            style="display:flex; align-items:center; gap:5px; padding:7px 12px; border-radius:9px; background:#f5f4fb; border:1px solid #ededf3; color:#b0b0c0; font-size:12px; font-weight:600;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            Locked
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else style="background:#fff; border:1px solid #ededf3; border-radius:16px; padding:48px 24px; text-align:center;">
                        <div style="width:52px; height:52px; border-radius:14px; background:#f5f4fb; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#b0b0c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:#16162a; margin-bottom:6px;">No documents found</div>
                        <div style="font-size:13px; color:#9a9ab0;">Try a different search or category filter.</div>
                        <button @click="searchQuery = ''; activeCategory = 'all'"
                            style="margin-top:16px; padding:8px 20px; border-radius:10px; background:#f5f4fb; border:1px solid #ededf3; font-size:13px; font-weight:600; color:#6a4dff; cursor:pointer; font-family:inherit;">
                            Clear filters
                        </button>
                    </div>

                </div>

                <!-- Right rail -->
                <div style="width:284px; flex-shrink:0; display:flex; flex-direction:column; gap:14px;" class="docs-rail">

                    <!-- Unit summary -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; padding:18px;">
                        <div style="font-size:13px; font-weight:700; color:#16162a; margin-bottom:14px;">Selected Unit</div>
                        <!-- Property placeholder image -->
                        <div style="width:100%; height:100px; border-radius:12px; background:repeating-linear-gradient(135deg,#e3e8ef,#e3e8ef 8px,#eef1f5 8px,#eef1f5 16px); display:flex; align-items:center; justify-content:center; margin-bottom:14px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#b0b8c4" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <div style="font-size:14px; font-weight:800; color:#16162a; margin-bottom:3px;">{{ unit.project_name }}</div>
                        <div style="font-size:12px; color:#9a9ab0; margin-bottom:10px;">Unit {{ unit.unit_number }} · Floor {{ unit.floor }} · {{ unit.location }}</div>
                        <div style="display:flex; flex-direction:column; gap:0;">
                            <div v-for="row in [
                                { label: 'Status',   value: unit.status_label, badge: true },
                                { label: 'Type',     value: unit.type_label,   badge: false },
                                { label: 'Size',     value: unit.size_sqft + ' sqft', badge: false },
                            ]" :key="row.label"
                                style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f5f4fb;">
                                <span style="font-size:12px; color:#9a9ab0;">{{ row.label }}</span>
                                <span v-if="row.badge"
                                    style="font-size:11px; font-weight:700; padding:2px 9px; border-radius:999px;"
                                    :style="{ background: statusStyle(unit.status_color).bg, color: statusStyle(unit.status_color).text }">
                                    {{ row.value }}
                                </span>
                                <span v-else style="font-size:12.5px; font-weight:700; color:#16162a;">{{ row.value }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Document availability breakdown -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:16px; padding:18px;">
                        <div style="font-size:13px; font-weight:700; color:#16162a; margin-bottom:14px;">By Category</div>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div v-for="cat in unit.categories" :key="cat.id">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:5px;">
                                    <span style="font-size:12px; font-weight:600; color:#5a5a6e;">{{ CATEGORY_LABELS[cat.id] }}</span>
                                    <span style="font-size:11.5px; font-weight:700; color:#16162a;">
                                        {{ cat.docs.filter(d => d.available).length }}/{{ cat.docs.length }}
                                    </span>
                                </div>
                                <div style="height:5px; background:#f0eff7; border-radius:4px; overflow:hidden;">
                                    <div
                                        :style="{ width: (cat.docs.filter(d => d.available).length / cat.docs.length * 100) + '%', background: cat.color }"
                                        style="height:100%; border-radius:4px; transition:width .4s;"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Request document CTA -->
                    <div style="background:#f8f7fe; border:1px solid #ece9f6; border-radius:16px; padding:16px 18px;">
                        <div style="display:flex; align-items:flex-start; gap:10px;">
                            <div style="width:34px; height:34px; border-radius:9px; background:rgba(106,77,255,.12); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6a4dff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:#16162a; margin-bottom:4px;">Need a Document?</div>
                                <div style="font-size:12px; color:#7a7a90; line-height:1.5; margin-bottom:12px;">Can't find what you need? Raise a request and our team will respond within 2 business days.</div>
                                <Link :href="route('client.ai.advisor')" style="display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700; color:#6a4dff; text-decoration:none;">
                                    Ask Sara AI
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
                                </Link>
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
    .docs-rail { display: none !important; }
}
</style>
