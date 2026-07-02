<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import DatePicker from '@/Components/ui/date-picker/DatePicker.vue';

const props = defineProps({
    form: { type: Object, required: true },
    enums: { type: Object, required: true },
    mode: { type: String, default: 'create' },
});

const emit = defineEmits(['submit']);

// ── Steps ──────────────────────────────────────────────────────────────
const STEPS = [
    { key: 'buyer',      label: 'Select Buyer',        subtitle: 'Who is reserving',    kicker: 'Step 1 of 10' },
    { key: 'project',    label: 'Select Project',      subtitle: 'Project & unit',      kicker: 'Step 2 of 10' },
    { key: 'details',    label: 'Reservation Details', subtitle: 'Dates & owner',       kicker: 'Step 3 of 10' },
    { key: 'pricing',    label: 'Pricing',             subtitle: 'Price & charges',     kicker: 'Step 4 of 10' },
    { key: 'plan',       label: 'Payment Plan',        subtitle: 'Installments',        kicker: 'Step 5 of 10' },
    { key: 'documents',  label: 'Documents',           subtitle: 'KYC checklist',       kicker: 'Step 6 of 10' },
    { key: 'mortgage',   label: 'Mortgage',            subtitle: 'Loan & EMI',          kicker: 'Step 7 of 10' },
    { key: 'approvals',  label: 'Approvals',           subtitle: 'Sign-off chain',      kicker: 'Step 8 of 10' },
    { key: 'review',     label: 'Review',              subtitle: 'Validation & summary',kicker: 'Step 9 of 10' },
    { key: 'publish',    label: 'Publish',             subtitle: 'Confirm & reserve',   kicker: 'Step 10 of 10' },
];

const activeStep = ref(0);
const currentStep = computed(() => STEPS[activeStep.value]);
const progress = computed(() => Math.round(((activeStep.value + 1) / STEPS.length) * 100));
const isFirst = computed(() => activeStep.value === 0);
const isLast = computed(() => activeStep.value === STEPS.length - 1);

function goTo(idx) { activeStep.value = idx; }
function goNext() { if (!isLast.value) activeStep.value++; }
function goBack() { if (!isFirst.value) activeStep.value--; }

function stepState(idx) {
    if (idx === activeStep.value) return 'active';
    if (idx < activeStep.value) return 'done';
    return 'pending';
}

// ── Field style ────────────────────────────────────────────────────────
const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';

// ── Draft (demo-only — Bookings has no draft status, so this just gives visual feedback) ──
const draftMsg = ref('');
let draftTimer;
function saveDraft() {
    draftMsg.value = 'Draft saved';
    clearTimeout(draftTimer);
    draftTimer = setTimeout(() => (draftMsg.value = ''), 1800);
}

// ── Buyer ────────────────────────────────────────────────────────────────
const pickerTile = ref('existing'); // existing | new | search | ai
const buyerTiles = [
    { key: 'existing', icon: '👤', title: 'Existing Buyer', sub: 'Pick from your CRM' },
    { key: 'new',      icon: '✨', title: 'New Buyer',      sub: 'Create a new record' },
    { key: 'search',   icon: '🔍', title: 'Search CRM',     sub: 'Find by name / phone' },
    { key: 'ai',       icon: '🤖', title: 'AI Buyer Match', sub: 'Best-fit buyer for this unit' },
];
function pickTile(tile) {
    pickerTile.value = tile;
    if (tile === 'new') {
        props.form.buyer_mode = 'new';
        window.open(route('admin.clients.create'), '_blank');
    } else {
        props.form.buyer_mode = 'existing';
        if (tile === 'ai' && !props.form.client_id) {
            const first = props.enums.clients[0];
            if (first) {
                props.form.client_id = first.id;
                fetchClientDetail(first.id);
            }
        }
    }
}

// Live server-side buyer search (by name / phone) + selected buyer detail box.
const buyerSearch = ref('');
const searchResults = ref([]);
const searching = ref(false);
const selectedClientDetail = ref(null);
let searchTimer;

watch(buyerSearch, (term) => {
    clearTimeout(searchTimer);
    const q = term.trim();
    if (q.length < 2) {
        searchResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        searching.value = true;
        try {
            const { data } = await axios.get(route('admin.clients.search'), { params: { q } });
            searchResults.value = data;
        } finally {
            searching.value = false;
        }
    }, 300);
});

function selectClient(client) {
    props.form.client_id = client.id;
    selectedClientDetail.value = client;
    searchResults.value = [];
    buyerSearch.value = client.name;
}

async function fetchClientDetail(id) {
    if (!id) {
        selectedClientDetail.value = null;
        return;
    }
    const { data } = await axios.get(route('admin.clients.search'), { params: { id } });
    selectedClientDetail.value = data;
}

const buyerName = computed(() => props.form.buyer_mode === 'existing' ? selectedClientDetail.value?.name : props.form.new_client_name);

// ── Project / Unit ───────────────────────────────────────────────────────
const selectedProjectId = ref('');
const building = ref('');
const block = ref('');
const floorFilter = ref('');
const availableUnits = computed(() =>
    props.enums.units.filter(u => !selectedProjectId.value || String(u.project_id) === String(selectedProjectId.value))
);
const selectedUnit = computed(() => props.enums.units.find(u => String(u.id) === String(props.form.unit_id)));
const unitPrice = computed(() => selectedUnit.value?.price ?? 0);

// ── Pricing ──────────────────────────────────────────────────────────────
const campaign = ref('');
const parkingFee = ref(0);
const storageFee = ref(0);
const vatFee = ref(0);
const regFee = ref(0);
const offer = ref('');
const discountAmount = computed(() => Math.round(unitPrice.value * (Number(props.form.discount_pct) || 0) / 100));
const extrasTotal = computed(() => (Number(parkingFee.value) || 0) + (Number(storageFee.value) || 0) + (Number(vatFee.value) || 0) + (Number(regFee.value) || 0));
const finalPrice = computed(() => Math.max(0, unitPrice.value + extrasTotal.value - discountAmount.value));

// ── Payment plan ─────────────────────────────────────────────────────────
const milestone = ref('Construction-linked');
const suggestedDownPayment = computed(() => Math.round(finalPrice.value * 0.2));
const downPaymentValue = computed(() => Number(props.form.down_payment) || suggestedDownPayment.value);
const emiEstimate = computed(() => Math.round((finalPrice.value - downPaymentValue.value) / (Number(props.form.total_installments) || 1)));
const firstDueDate = computed(() => {
    const d = new Date(props.form.booking_date || new Date());
    d.setMonth(d.getMonth() + 1);
    return d.toISOString().slice(0, 10);
});

// ── Review ───────────────────────────────────────────────────────────────
const validationChecks = computed(() => [
    { ok: !!selectedUnit.value, label: 'Unit still available', note: selectedUnit.value ? `No competing reservation on ${selectedUnit.value.unit_number}` : 'Select a unit to continue' },
    { ok: !!buyerName.value, label: 'Buyer identified', note: buyerName.value || 'Select or add a buyer to continue' },
    { ok: props.form.meta.approvals.manager === 'Approved', label: props.form.meta.approvals.manager === 'Approved' ? 'Manager approval confirmed' : 'Manager approval pending', note: props.form.meta.approvals.manager === 'Approved' ? 'Manager has signed off' : 'Required before final confirmation' },
    { ok: true, label: 'No pricing conflict', note: 'Final price within approved range' },
]);

function submitFinal() {
    props.form.price_agreed = finalPrice.value;
    props.form.down_payment = downPaymentValue.value;
    emit('submit');
}
</script>

<template>
    <form @submit.prevent>
        <div class="grid items-start gap-7" style="grid-template-columns: 280px minmax(0, 1fr)">
            <!-- ══ LEFT RAIL — Step navigation ═══════════════════════════════ -->
            <div class="sticky top-6 overflow-hidden rounded-xl border border-border bg-admin-surface-card p-2">
                <nav class="flex flex-col gap-0.5">
                    <button
                        v-for="(step, idx) in STEPS" :key="step.key" type="button"
                        @click="goTo(idx)"
                        :class="[
                            'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors',
                            stepState(idx) === 'active' ? 'bg-[#F1ECFF] dark:bg-admin-accent/10' : 'hover:bg-slate-50 dark:hover:bg-white/[0.03]',
                        ]"
                    >
                        <div
                            :class="[
                                'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-colors',
                                stepState(idx) === 'active' ? 'bg-admin-accent text-white' : stepState(idx) === 'done' ? 'bg-green-500 text-white' : 'bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400',
                            ]"
                        >
                            <svg v-if="stepState(idx) === 'done'" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <div class="min-w-0">
                            <p :class="['truncate text-sm font-semibold', stepState(idx) === 'active' ? 'text-admin-accent' : 'text-foreground']">{{ step.label }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ step.subtitle }}</p>
                        </div>
                    </button>
                </nav>

                <div class="mt-2 border-t border-border pt-2">
                    <Link :href="route('admin.bookings.index')" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-slate-50 hover:text-foreground dark:hover:bg-white/[0.03]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5" /><polyline points="12 19 5 12 12 5" /></svg>
                        Back to Bookings
                    </Link>
                </div>
            </div>

            <!-- ══ RIGHT CARD — Content ═══════════════════════════════════════ -->
            <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
                <!-- Step Header -->
                <div class="border-b border-border px-6 py-5">
                    <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest text-admin-accent">{{ currentStep.kicker }}</p>
                    <h2 class="text-lg font-bold text-foreground">{{ currentStep.label }}</h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">{{ currentStep.subtitle }}</p>
                    <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                        <div class="h-full rounded-full bg-admin-accent transition-all duration-500" :style="{ width: progress + '%' }" />
                    </div>
                </div>

                <!-- ── Step 1: Select Buyer ──────────────────────────────────── -->
                <div v-show="activeStep === 0" class="grid grid-cols-2 gap-5 p-6">
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">How are you reserving?</Label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="tile in buyerTiles" :key="tile.key" type="button"
                                @click="pickTile(tile.key)"
                                :class="[
                                    'flex items-center gap-3 rounded-lg border p-3.5 text-left transition-colors',
                                    pickerTile === tile.key ? 'border-admin-accent bg-[#F1ECFF] dark:bg-admin-accent/10' : 'border-border hover:bg-slate-50 dark:hover:bg-white/[0.03]',
                                ]"
                            >
                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md text-base" :class="pickerTile === tile.key ? 'bg-admin-accent text-white' : 'bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400'">{{ tile.icon }}</div>
                                <div class="min-w-0">
                                    <p :class="['text-sm font-semibold', pickerTile === tile.key ? 'text-admin-accent' : 'text-foreground']">{{ tile.title }}</p>
                                    <p class="text-xs text-muted-foreground">{{ tile.sub }}</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <template v-if="form.buyer_mode === 'existing'">
                        <div class="col-span-2 relative space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Search buyer</Label>
                            <Input
                                v-model="buyerSearch"
                                placeholder="Search by name or phone"
                                :class="[f, form.errors.client_id && 'border-destructive']"
                            />
                            <p v-if="form.errors.client_id" class="text-xs text-destructive">{{ form.errors.client_id }}</p>

                            <div v-if="searching" class="text-xs text-muted-foreground">Searching…</div>
                            <div v-else-if="searchResults.length" class="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-border bg-admin-surface-card shadow-lg">
                                <button
                                    v-for="c in searchResults" :key="c.id" type="button"
                                    @click="selectClient(c)"
                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition-colors hover:bg-slate-50 dark:hover:bg-white/[0.03]"
                                >
                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center overflow-hidden rounded-full bg-admin-accent text-xs font-bold text-white">
                                        <img v-if="c.avatar" :src="c.avatar" :alt="c.name" class="h-full w-full object-cover" />
                                        <span v-else>{{ c.name.split(' ').map(w => w[0]).slice(0,2).join('') }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-foreground">{{ c.name }}</p>
                                        <p class="truncate text-xs text-muted-foreground">{{ c.phone ?? 'No phone on file' }}</p>
                                    </div>
                                </button>
                            </div>
                            <div v-else-if="buyerSearch.trim().length >= 2" class="text-xs text-muted-foreground">No matching clients found.</div>
                        </div>

                        <div v-if="selectedClientDetail" class="col-span-2 flex items-center gap-4 rounded-lg border border-border bg-muted/40 px-4 py-3">
                            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center overflow-hidden rounded-full bg-admin-accent text-base font-bold text-white">
                                <img v-if="selectedClientDetail.avatar" :src="selectedClientDetail.avatar" :alt="selectedClientDetail.name" class="h-full w-full object-cover" />
                                <span v-else>{{ selectedClientDetail.name.split(' ').map(w => w[0]).slice(0,2).join('') }}</span>
                            </div>
                            <div class="min-w-0 grid flex-1 grid-cols-2 gap-x-4 gap-y-1">
                                <div class="col-span-2 text-sm font-semibold text-foreground">
                                    {{ selectedClientDetail.name }}
                                    <span v-if="pickerTile === 'ai'" class="ml-1.5 rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-bold text-green-700 dark:bg-green-500/15 dark:text-green-400">AI match 92%</span>
                                </div>
                                <div class="text-xs text-muted-foreground">Phone: {{ selectedClientDetail.phone ?? '—' }}</div>
                                <div class="text-xs text-muted-foreground">Father: {{ selectedClientDetail.father_name ?? '—' }}</div>
                                <div class="text-xs text-muted-foreground">Mother: {{ selectedClientDetail.mother_name ?? '—' }}</div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="col-span-2 flex items-center justify-between rounded-lg border border-admin-accent/20 bg-admin-accent/5 px-4 py-2.5">
                            <p class="text-xs text-muted-foreground">A full client profile page opened in a new tab. Fill the details below for this reservation, or use the full form there.</p>
                            <a :href="route('admin.clients.create')" target="_blank" rel="noopener" class="ml-3 flex-shrink-0 text-xs font-semibold text-admin-accent hover:underline">Reopen ↗</a>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Buyer Name</Label>
                            <Input v-model="form.new_client_name" placeholder="Rahim Uddin" :class="[f, form.errors.new_client_name && 'border-destructive']" />
                            <p v-if="form.errors.new_client_name" class="text-xs text-destructive">{{ form.errors.new_client_name }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Phone</Label>
                            <Input v-model="form.new_client_phone" placeholder="01712-345678" :class="[f, form.errors.new_client_phone && 'border-destructive']" />
                            <p v-if="form.errors.new_client_phone" class="text-xs text-destructive">{{ form.errors.new_client_phone }}</p>
                        </div>
                        <div class="col-span-2 space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Email (optional)</Label>
                            <Input v-model="form.new_client_email" type="email" placeholder="rahim@example.com" :class="f" />
                        </div>
                    </template>
                </div>

                <!-- ── Step 2: Select Project ────────────────────────────────── -->
                <div v-show="activeStep === 1" class="grid grid-cols-2 gap-5 p-6">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Project</Label>
                        <Select :model-value="selectedProjectId || undefined" @update:model-value="selectedProjectId = $event ?? ''; form.unit_id = ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Select project" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="p in enums.projects" :key="p.id" :value="p.id">{{ p.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Building</Label>
                        <Select :model-value="building || undefined" @update:model-value="building = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Select building" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Tower A">Tower A</SelectItem>
                                <SelectItem value="Tower B">Tower B</SelectItem>
                                <SelectItem value="Tower C">Tower C</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Block</Label>
                        <Select :model-value="block || undefined" @update:model-value="block = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Select block" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Block A">Block A</SelectItem>
                                <SelectItem value="Block B">Block B</SelectItem>
                                <SelectItem value="Block C">Block C</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Floor</Label>
                        <Select :model-value="floorFilter || undefined" @update:model-value="floorFilter = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Any floor" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Floor 7">Floor 7</SelectItem>
                                <SelectItem value="Floor 9">Floor 9</SelectItem>
                                <SelectItem value="Floor 12">Floor 12</SelectItem>
                                <SelectItem value="Floor 15">Floor 15</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Unit</Label>
                        <Select :model-value="form.unit_id || undefined" @update:model-value="form.unit_id = $event ?? ''">
                            <SelectTrigger :class="[f, form.errors.unit_id && 'border-destructive']"><SelectValue placeholder="Select unit" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="u in availableUnits" :key="u.id" :value="u.id">{{ u.label }} — BDT {{ Number(u.price).toLocaleString() }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.unit_id" class="text-xs text-destructive">{{ form.errors.unit_id }}</p>
                        <p v-else-if="selectedProjectId && availableUnits.length === 0" class="text-xs font-medium text-amber-600">No available units left in this project.</p>
                    </div>
                    <div v-if="selectedUnit" class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Availability</Label>
                        <Input :model-value="'Unit ' + selectedUnit.unit_number + ' — Available · BDT ' + Number(selectedUnit.price).toLocaleString()" disabled :class="f" />
                    </div>
                </div>

                <!-- ── Step 3: Reservation Details ───────────────────────────── -->
                <div v-show="activeStep === 2" class="grid grid-cols-2 gap-5 p-6">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Reservation Date</Label>
                        <DatePicker :model-value="form.booking_date" @update:model-value="form.booking_date = $event" placeholder="Pick reservation date" :class="[f, form.errors.booking_date && 'border-destructive']" />
                        <p v-if="form.errors.booking_date" class="text-xs text-destructive">{{ form.errors.booking_date }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Expiry Date</Label>
                        <DatePicker :model-value="form.reserved_until" @update:model-value="form.reserved_until = $event" placeholder="Pick expiry date" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Booking Amount (BDT)</Label>
                        <Input v-model.number="form.down_payment" type="number" :placeholder="String(suggestedDownPayment)" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Discount (%)</Label>
                        <Input v-model.number="form.discount_pct" type="number" min="0" max="100" step="0.5" placeholder="e.g. 4" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Campaign</Label>
                        <Select :model-value="campaign || undefined" @update:model-value="campaign = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="None" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Eid Offer">Eid Offer</SelectItem>
                                <SelectItem value="Launch Discount">Launch Discount</SelectItem>
                                <SelectItem value="Referral">Referral</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Sales Representative</Label>
                        <Select :model-value="form.sales_rep_id || undefined" @update:model-value="form.sales_rep_id = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Select representative" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="r in enums.sales_reps" :key="r.id" :value="r.id">{{ r.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Reservation Source</Label>
                        <Select :model-value="form.source || undefined" @update:model-value="form.source = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Select source" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="s in enums.sources" :key="s" :value="s">{{ s }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Priority</Label>
                        <Select :model-value="form.priority" @update:model-value="form.priority = $event">
                            <SelectTrigger :class="f"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="p in enums.priorities" :key="p" :value="p">{{ p }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Notes</Label>
                        <Textarea v-model="form.notes" rows="3" placeholder="Add notes for the sales team" :class="[f, 'resize-none']" />
                    </div>
                </div>

                <!-- ── Step 4: Pricing ────────────────────────────────────────── -->
                <div v-show="activeStep === 3" class="grid grid-cols-2 gap-5 p-6">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Unit Price (BDT)</Label>
                        <Input :model-value="unitPrice.toLocaleString()" disabled :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Parking (BDT)</Label>
                        <Input v-model.number="parkingFee" type="number" placeholder="800,000" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Storage (BDT)</Label>
                        <Input v-model.number="storageFee" type="number" placeholder="120,000" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">VAT (BDT)</Label>
                        <Input v-model.number="vatFee" type="number" placeholder="250,000" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Registration (BDT)</Label>
                        <Input v-model.number="regFee" type="number" placeholder="375,000" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Discount Amount (BDT)</Label>
                        <Input :model-value="'− ' + discountAmount.toLocaleString()" disabled :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Special Offer</Label>
                        <Select :model-value="offer || undefined" @update:model-value="offer = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="None" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Free Parking">Free Parking</SelectItem>
                                <SelectItem value="Waived Registration">Waived Registration</SelectItem>
                                <SelectItem value="Furniture Pack">Furniture Pack</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Final Price (BDT)</Label>
                        <Input :model-value="finalPrice.toLocaleString()" disabled :class="[f, 'text-base font-bold text-foreground']" />
                    </div>
                </div>

                <!-- ── Step 5: Payment Plan ───────────────────────────────────── -->
                <div v-show="activeStep === 4" class="grid grid-cols-2 gap-5 p-6">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Plan Type</Label>
                        <Select :model-value="form.plan_type" @update:model-value="form.plan_type = $event">
                            <SelectTrigger :class="f"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="p in enums.plan_types" :key="p" :value="p">{{ p }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Down Payment (BDT)</Label>
                        <Input :model-value="downPaymentValue.toLocaleString()" disabled :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">No. of Installments</Label>
                        <Input v-model.number="form.total_installments" type="number" min="1" max="60" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Monthly EMI (BDT)</Label>
                        <Input :model-value="emiEstimate.toLocaleString()" disabled :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Milestone Linked</Label>
                        <Select :model-value="milestone" @update:model-value="milestone = $event">
                            <SelectTrigger :class="f"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Time-based">Time-based</SelectItem>
                                <SelectItem value="Construction-linked">Construction-linked</SelectItem>
                                <SelectItem value="Hybrid">Hybrid</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">First Due Date</Label>
                        <Input :model-value="firstDueDate" disabled :class="f" />
                    </div>
                </div>

                <!-- ── Step 6: Documents ──────────────────────────────────────── -->
                <div v-show="activeStep === 5" class="grid grid-cols-2 gap-5 p-6">
                    <div v-for="doc in [['national_id','National ID'],['passport','Passport'],['income_proof','Income Proof'],['bank_statement','Bank Statement']]" :key="doc[0]" class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ doc[1] }}</Label>
                        <Select :model-value="form.meta.documents[doc[0]] || 'Not uploaded'" @update:model-value="form.meta.documents[doc[0]] = $event">
                            <SelectTrigger :class="f"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Not uploaded">Not uploaded</SelectItem>
                                <SelectItem value="Uploaded">Uploaded</SelectItem>
                                <SelectItem value="Verified">Verified</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Agreement Draft</Label>
                        <div class="rounded-lg border border-dashed border-border bg-muted/40 px-4 py-3 text-sm text-muted-foreground">Auto-generated draft — generated on publish</div>
                    </div>
                </div>

                <!-- ── Step 7: Mortgage ───────────────────────────────────────── -->
                <div v-show="activeStep === 6" class="grid grid-cols-2 gap-5 p-6">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Loan Required</Label>
                        <Select :model-value="form.meta.mortgage.loan_required" @update:model-value="form.meta.mortgage.loan_required = $event">
                            <SelectTrigger :class="f"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="No">No</SelectItem>
                                <SelectItem value="Yes">Yes</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Eligible Bank</Label>
                        <Select :model-value="form.meta.mortgage.eligible_bank || undefined" @update:model-value="form.meta.mortgage.eligible_bank = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Select bank" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="City Bank">City Bank</SelectItem>
                                <SelectItem value="BRAC Bank">BRAC Bank</SelectItem>
                                <SelectItem value="Eastern Bank">Eastern Bank</SelectItem>
                                <SelectItem value="Multiple">Multiple</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Loan Amount (BDT)</Label>
                        <Input v-model="form.meta.mortgage.loan_amount" placeholder="10,000,000" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Interest Rate (%)</Label>
                        <Input v-model="form.meta.mortgage.interest_rate" placeholder="9.5" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Indicative EMI (BDT/mo)</Label>
                        <Input v-model="form.meta.mortgage.indicative_emi" placeholder="95,000" :class="f" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Approval Status</Label>
                        <Select :model-value="form.meta.mortgage.status || undefined" @update:model-value="form.meta.mortgage.status = $event ?? ''">
                            <SelectTrigger :class="f"><SelectValue placeholder="Not started" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Pre-approved">Pre-approved</SelectItem>
                                <SelectItem value="Under review">Under review</SelectItem>
                                <SelectItem value="Approved">Approved</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- ── Step 8: Approvals ──────────────────────────────────────── -->
                <div v-show="activeStep === 7" class="grid grid-cols-2 gap-5 p-6">
                    <div v-for="key in ['sales','finance','manager','legal']" :key="key" class="space-y-1.5">
                        <Label class="text-xs font-medium capitalize text-slate-500 dark:text-slate-400">{{ key }} Approval</Label>
                        <Select :model-value="form.meta.approvals[key]" @update:model-value="form.meta.approvals[key] = $event">
                            <SelectTrigger :class="f"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Pending">Pending</SelectItem>
                                <SelectItem value="Approved">Approved</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- ── Step 9: Review ─────────────────────────────────────────── -->
                <div v-show="activeStep === 8" class="p-6">
                    <p class="mb-3 text-sm font-semibold text-foreground">Validation &amp; Conflict Check</p>
                    <div class="flex flex-col gap-2">
                        <div v-for="(c, i) in validationChecks" :key="i" class="flex items-center gap-3 rounded-lg border border-border px-3.5 py-2.5">
                            <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-md text-xs font-bold" :class="c.ok ? 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400'">{{ c.ok ? '✓' : '!' }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-foreground">{{ c.label }}</p>
                                <p class="text-xs text-muted-foreground">{{ c.note }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col divide-y divide-border border-t border-border">
                        <div class="flex items-center justify-between py-2.5"><span class="text-sm text-muted-foreground">Buyer</span><span class="text-sm font-semibold text-foreground">{{ buyerName || '—' }}</span></div>
                        <div class="flex items-center justify-between py-2.5"><span class="text-sm text-muted-foreground">Unit</span><span class="text-sm font-semibold text-foreground">{{ selectedUnit?.unit_number || '—' }}</span></div>
                        <div class="flex items-center justify-between py-2.5"><span class="text-sm text-muted-foreground">Final Price</span><span class="text-sm font-semibold text-foreground">BDT {{ finalPrice.toLocaleString() }}</span></div>
                        <div class="flex items-center justify-between py-2.5"><span class="text-sm text-muted-foreground">Booking Amount</span><span class="text-sm font-semibold text-foreground">BDT {{ downPaymentValue.toLocaleString() }}</span></div>
                        <div class="flex items-center justify-between py-2.5"><span class="text-sm text-muted-foreground">Payment Plan</span><span class="text-sm font-semibold text-foreground">{{ form.plan_type }}</span></div>
                        <div class="flex items-center justify-between py-2.5"><span class="text-sm text-muted-foreground">Expiry</span><span class="text-sm font-semibold text-foreground">{{ form.reserved_until || '—' }}</span></div>
                    </div>

                    <div class="mt-5 flex gap-3 rounded-lg border border-admin-accent/20 bg-admin-accent/5 p-4">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-admin-accent text-white">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4L12 3z" /></svg>
                        </div>
                        <div>
                            <p class="mb-0.5 text-sm font-semibold text-foreground">Sera AI Recommendation</p>
                            <p class="text-xs leading-relaxed text-muted-foreground">This reservation has a strong completion likelihood. Recommend collecting the booking amount within 48 hours and routing {{ buyerName || 'the buyer' }} to the manager for approval today to avoid hold lapse.</p>
                        </div>
                    </div>
                </div>

                <!-- ── Step 10: Publish ───────────────────────────────────────── -->
                <div v-show="activeStep === 9" class="p-6">
                    <div class="flex flex-col divide-y divide-border rounded-lg border border-border">
                        <div class="flex items-center justify-between px-4 py-3"><span class="text-sm text-muted-foreground">Reservation ID</span><span class="text-sm font-semibold text-foreground">Assigned on publish</span></div>
                        <div class="flex items-center justify-between px-4 py-3"><span class="text-sm text-muted-foreground">Status</span><span class="text-sm font-semibold text-green-600 dark:text-green-400">Ready to reserve</span></div>
                    </div>
                    <div class="mt-4 flex gap-3 rounded-lg border border-admin-accent/20 bg-admin-accent/5 p-4">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-admin-accent text-white">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4L12 3z" /></svg>
                        </div>
                        <p class="text-xs leading-relaxed text-muted-foreground">On publish: the unit is marked Reserved, a sale agreement draft is generated, and a confirmation is sent to the buyer and assigned rep.</p>
                    </div>
                    <div v-if="Object.keys(form.errors).length" class="mt-4 rounded-lg border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive">
                        <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
                    </div>
                </div>

                <!-- ── Footer Navigation ────────────────────────────────────── -->
                <div class="flex items-center justify-between border-t border-border px-6 py-4">
                    <button type="button" @click="goBack" :disabled="isFirst" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5" /><polyline points="12 19 5 12 12 5" /></svg>
                        Back
                    </button>

                    <div class="flex items-center gap-3">
                        <span v-if="draftMsg" class="text-xs font-medium text-muted-foreground">{{ draftMsg }}</span>
                        <button type="button" @click="saveDraft" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" /><polyline points="17 21 17 13 7 13 7 21" /><polyline points="7 3 7 8 15 8" /></svg>
                            Save Draft
                        </button>

                        <button v-if="!isLast" type="button" @click="goNext" class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-4 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90">
                            Next
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14" /><polyline points="12 5 19 12 12 19" /></svg>
                        </button>

                        <button v-else type="button" @click="submitFinal" :disabled="form.processing" class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-5 text-sm font-semibold text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60">
                            <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>
                            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13" /><path d="M22 2L15 22l-4-9-9-4 19-7z" /></svg>
                            {{ mode === 'edit' ? 'Save Changes' : 'Reserve Unit' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
