<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Input }   from '@/Components/ui/input';
import { Label }   from '@/Components/ui/label';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/Components/ui/select';
import { updatePayment } from '@/stores/payments.js';

const props = defineProps({
    buyers:  { type: Array,  default: () => [] },
    prefill: { type: Object, default: () => ({}) },
});

const isEdit = computed(() => !!props.prefill?.ref);

// ── Wizard state ──────────────────────────────────────────────────────────────
const step  = ref(1);
const done  = ref(false);
const toast = ref('');

// Try to find buyer ID from prefill name; fall back to the name itself so the
// synthetic SelectItem (added below) can match by value.
function resolveBuyerId() {
    if (!props.prefill?.buyer) return '';
    const match = props.buyers.find(b => b.name === props.prefill.buyer);
    return match ? String(match.id) : props.prefill.buyer;
}

const form = ref({
    buyer:       resolveBuyerId(),
    project:     props.prefill?.project  || '',
    tower:       '',
    building:    '',
    floor:       '',
    unit:        props.prefill?.unit     || '',
    agreement:   '',
    invoice:     props.prefill?.ref      || '',
    installment: '',
    ptype:       props.prefill?.ptype    || '',
    amount:      props.prefill?.amount   || '',
    method:      props.prefill?.method   || '',
    currency:    'BDT',
    txn: '', refno: '', bank: '', branch: '', depdate: '', verify: '',
    ledger: '', tax: '', vat: '', discount: '', penalty: '', fincode: '',
    finapp: '', mgrapp: '', compliance: '',
    notes: '',
});

const TOTAL_STEPS = 10;

const steps = [
    { label: 'Select Buyer',         sub: 'Who is paying' },
    { label: 'Select Project',       sub: 'Project & unit' },
    { label: 'Payment Information',  sub: 'Invoice & type' },
    { label: 'Payment Method',       sub: 'How it was paid' },
    { label: 'Bank Details',         sub: 'Transaction info' },
    { label: 'Supporting Documents', sub: 'Proof of payment' },
    { label: 'Accounting',           sub: 'Ledger & tax' },
    { label: 'Approval',             sub: 'Sign-off' },
    { label: 'Review',               sub: 'AI validation' },
    { label: 'Complete',             sub: 'Generate receipt' },
];

const progress = computed(() => Math.round((step.value / TOTAL_STEPS) * 100));

// Buyer found via Select (matched by ID)
const selectedBuyer = computed(() =>
    props.buyers.find(b => String(b.id) === String(form.value.buyer))
);

// Buyer to DISPLAY — prefer DB match, fall back to prefill name in edit mode
const displayBuyer = computed(() => {
    if (selectedBuyer.value) return selectedBuyer.value;
    if (isEdit.value && props.prefill?.buyer) {
        return { name: props.prefill.buyer, phone: '', email: '' };
    }
    return null;
});

// ── Shared input style (matches ClientForm) ───────────────────────────────────
const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';

// ── Dummy options — include ALL values used in Records dummy data ──────────────
const OPTS = {
    projects:     ['Lake View Residence', 'Green Park Heights', 'Skyline Towers', 'Riverside Apartments', 'City Central Mall', 'River View Heights'],
    towers:       ['Tower A', 'Tower B', 'Tower C'],
    buildings:    ['Block A', 'Block B', 'Block C', 'Commercial'],
    floors:       ['Floor 1', 'Floor 5', 'Floor 7', 'Floor 9', 'Floor 12'],
    units:        ['A-1205', 'A-903', 'A-0903', 'B-804', 'B-0804', 'B-1102', 'C-302', 'C-1201', 'CM-12', 'D-501', 'E-201', 'F-102'],
    agreements:   ['AGR-2026-0142', 'AGR-2026-0145', 'AGR-2026-0151'],
    invoices:     ['INV-2026-0418', 'INV-2026-0419', 'INV-2026-0420', 'INV-2026-0421', 'INV-2026-0422', 'INV-2026-0423', 'INV-2026-0424', 'INV-2026-0425', 'INV-2026-0426', 'INV-2026-0427', 'Ad-hoc'],
    installments: ['Installment 1', 'Installment 2', 'Installment 3', 'Installment 4', 'N/A'],
    ptypes:       ['Booking', 'Down Payment', 'EMI', 'Registration', 'Maintenance', 'Utility', 'Other'],
    // bKash / Nagad are used in Records dummy data — must be here for the method
    // toggle buttons to highlight them on prefill
    methods:      ['Cash', 'Cheque', 'Bank Transfer', 'Online Banking', 'bKash', 'Nagad', 'Credit Card', 'Debit Card', 'Mobile Banking', 'Wallet'],
    currencies:   ['BDT', 'USD'],
    banks:        ['City Bank', 'BRAC Bank', 'Eastern Bank', 'bKash', 'Nagad', 'Dutch-Bangla Bank'],
    verify:       ['Unverified', 'Verified', 'Pending'],
    ledgers:      ['Sales Receipts', 'Advance Payments', 'Maintenance Income'],
    approvals:    ['Pending', 'Approved'],
    compliance:   ['Pending', 'Cleared'],
};

// Ensure a prefill value always appears in a select list even if not in OPTS
function optsWith(list, value) {
    if (!value || list.includes(value)) return list;
    return [value, ...list];
}

// ── AI checks (step 9) ────────────────────────────────────────────────────────
const checks = [
    { icon: '✓', bg: 'bg-success/15',  color: 'text-success', label: 'No duplicate payment',      note: 'No matching transaction in last 30 days' },
    { icon: '✓', bg: 'bg-success/15',  color: 'text-success', label: 'Amount matches invoice',    note: `BDT ${form.value.amount || '340,000'}` },
    { icon: '✓', bg: 'bg-success/15',  color: 'text-success', label: 'Bank reference present',    note: 'Transaction ID captured' },
    { icon: '!', bg: 'bg-warning/15',  color: 'text-warning', label: 'Finance approval pending',  note: 'Required before posting' },
];

function stepState(idx) {
    if (idx + 1 === step.value) return 'active';
    if (idx + 1 < step.value)  return 'done';
    return 'pending';
}

function showToast(msg) {
    toast.value = msg;
    setTimeout(() => { toast.value = ''; }, 2000);
}

function goTo(n) { step.value = n; done.value = false; }
function next()  { if (step.value < TOTAL_STEPS) step.value++; }
function back()  { if (step.value > 1) step.value--; }

function post() {
    // Persist the edit so Records list reflects it after navigation
    if (isEdit.value && props.prefill?.ref) {
        updatePayment(props.prefill.ref, {
            buyer:   displayBuyer.value?.name  || props.prefill.buyer || '',
            unit:    form.value.unit            || props.prefill.unit || '',
            project: form.value.project         || props.prefill.project || '',
            amount:  'BDT ' + (form.value.amount || props.prefill.amount || '0'),
            method:  form.value.method          || props.prefill.method || '',
            ptype:   form.value.ptype           || props.prefill.ptype || '',
            status:  'Paid',
        });
    }
    done.value = true;
}

function reset() {
    step.value = 1;
    done.value = false;
    Object.keys(form.value).forEach(k => { form.value[k] = k === 'currency' ? 'BDT' : ''; });
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Payment' : 'Record Payment'" />
    <AdminLayout>

        <!-- Breadcrumb + title -->
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.payments.index')" class="hover:text-admin-accent transition-colors">Payments</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <Link :href="route('admin.payments.records')" class="hover:text-admin-accent transition-colors">Records</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">{{ isEdit ? 'Edit Payment' : 'New Payment' }}</span>
            </nav>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-foreground">{{ isEdit ? 'Edit Payment' : 'Record Payment' }}</h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        <span v-if="isEdit">Editing reference <b class="text-foreground">{{ prefill.ref }}</b></span>
                        <span v-else>Capture and post a buyer payment to the ledger</span>
                    </p>
                </div>
                <div class="flex items-center gap-2.5 flex-shrink-0">
                    <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <span class="h-1.5 w-1.5 rounded-full bg-success" />
                        Draft autosaved
                    </span>
                    <Link :href="route('admin.payments.index')"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                        Cancel
                    </Link>
                </div>
            </div>
        </div>

        <!-- Wizard layout — same inline grid style as ClientForm -->
        <div class="grid items-start gap-7" style="grid-template-columns: 280px minmax(0, 1fr)">

            <!-- ══ LEFT RAIL ═══════════════════════════════════════════════════ -->
            <div class="sticky top-6 overflow-hidden rounded-xl border border-border bg-admin-surface-card p-2">
                <nav class="flex flex-col gap-0.5">
                    <button
                        v-for="(s, idx) in steps"
                        :key="idx"
                        type="button"
                        @click="goTo(idx + 1)"
                        :class="[
                            'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors',
                            stepState(idx) === 'active'
                                ? 'bg-admin-accent/10'
                                : 'hover:bg-slate-50 dark:hover:bg-white/[0.03]',
                        ]"
                    >
                        <div :class="[
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-colors',
                            stepState(idx) === 'active' ? 'bg-admin-accent text-on-gold' :
                            stepState(idx) === 'done'   ? 'bg-success text-on-gold' :
                                                          'bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400',
                        ]">
                            <svg v-if="stepState(idx) === 'done'" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <div class="min-w-0">
                            <p :class="['truncate text-sm font-semibold', stepState(idx) === 'active' ? 'text-admin-accent' : 'text-foreground']">{{ s.label }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ s.sub }}</p>
                        </div>
                    </button>
                </nav>

                <div class="mt-2 border-t border-border pt-2">
                    <Link :href="route('admin.payments.records')"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-slate-50 hover:text-foreground dark:hover:bg-white/[0.03]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back to Records
                    </Link>
                </div>
            </div>

            <!-- ══ RIGHT CARD ══════════════════════════════════════════════════ -->
            <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">

                <!-- ── Success screen ─────────────────────────────────────────── -->
                <div v-if="done" class="flex flex-col items-center py-16 text-center px-6">
                    <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-success/15 text-success">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-foreground">
                        {{ isEdit ? 'Payment Updated' : 'Payment Recorded' }}
                    </h2>
                    <p class="mt-2 max-w-md text-sm text-muted-foreground">
                        Payment <b class="text-foreground">{{ isEdit ? prefill.ref : 'PAY-2026-1188' }}</b>
                        {{ isEdit ? 'has been updated.' : 'has been posted to the ledger.' }}
                        A receipt has been generated and emailed to the buyer.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        <Link :href="route('admin.payments.records')"
                            class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-5 py-2.5 text-sm font-semibold text-on-gold transition-colors hover:bg-admin-accent/90">
                            Back to Records
                        </Link>
                        <Link :href="route('admin.payments.index')"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-transparent px-5 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                            Go to Payments
                        </Link>
                        <button @click="reset"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-transparent px-5 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                            Record Another
                        </button>
                    </div>
                </div>

                <!-- ── Wizard ─────────────────────────────────────────────────── -->
                <template v-else>

                    <!-- Step header -->
                    <div class="border-b border-border px-6 py-5">
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest text-admin-accent">
                            Step {{ step }} of {{ TOTAL_STEPS }}
                        </p>
                        <h2 class="text-lg font-bold text-foreground">{{ steps[step - 1].label }}</h2>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ steps[step - 1].sub }}</p>
                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-admin-accent transition-all duration-500" :style="{ width: progress + '%' }" />
                        </div>
                    </div>

                    <!-- ── Step 1: Select Buyer ────────────────────────────────── -->
                    <div v-show="step === 1" class="grid grid-cols-2 gap-5 p-6">
                        <div class="col-span-2 space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                Buyer <span class="text-destructive">*</span>
                            </Label>
                            <Select v-model="form.buyer">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Search buyer from CRM…">
                                        <template v-if="form.buyer">
                                            {{ selectedBuyer?.name ?? form.buyer }}
                                        </template>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <!-- Synthetic entry for prefilled buyer not found in DB -->
                                    <SelectItem
                                        v-if="isEdit && prefill.buyer && !buyers.some(b => b.name === prefill.buyer)"
                                        :value="prefill.buyer"
                                        class="text-warning">
                                        {{ prefill.buyer }}
                                        <span class="ml-1 text-xs opacity-70">(pre-filled)</span>
                                    </SelectItem>
                                    <SelectItem v-for="b in buyers" :key="b.id" :value="String(b.id)">
                                        {{ b.name }}
                                        <span class="ml-1 text-muted-foreground">({{ b.phone ?? b.email }})</span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Buyer card: shows DB match OR prefill name in edit mode -->
                        <div v-if="displayBuyer"
                            class="col-span-2 flex items-center gap-4 rounded-xl border border-admin-accent/20 bg-admin-accent/10 p-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-admin-accent text-sm font-bold text-on-gold">
                                {{ displayBuyer.name.split(' ').map(w => w[0]).slice(0, 2).join('') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-foreground">{{ displayBuyer.name }}</div>
                                <div class="mt-0.5 text-xs text-muted-foreground">
                                    <span v-if="displayBuyer.phone || displayBuyer.email">
                                        {{ displayBuyer.phone || displayBuyer.email }} &nbsp;·&nbsp;
                                    </span>
                                    Outstanding <b class="text-warning">BDT 8.3M</b>
                                    &nbsp;·&nbsp; Last paid 22 May
                                </div>
                            </div>
                            <span v-if="isEdit && !selectedBuyer"
                                class="shrink-0 rounded-md bg-warning/15 px-2 py-1 text-[11px] font-semibold text-warning">
                                Pre-filled
                            </span>
                        </div>

                        <div v-else-if="!buyers.length"
                            class="col-span-2 rounded-xl border border-warning/30 bg-warning/10 p-4 text-sm text-warning">
                            No clients found. Please add clients first.
                        </div>
                    </div>

                    <!-- ── Step 2: Select Project ──────────────────────────────── -->
                    <div v-show="step === 2" class="grid grid-cols-2 gap-5 p-6">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Project</Label>
                            <Select v-model="form.project">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select project"><template v-if="form.project">{{ form.project }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in optsWith(OPTS.projects, form.project)" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Tower</Label>
                            <Select v-model="form.tower">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select tower"><template v-if="form.tower">{{ form.tower }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.towers" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Building / Block</Label>
                            <Select v-model="form.building">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select building"><template v-if="form.building">{{ form.building }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.buildings" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Floor</Label>
                            <Select v-model="form.floor">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select floor"><template v-if="form.floor">{{ form.floor }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.floors" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Unit</Label>
                            <Select v-model="form.unit">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select unit"><template v-if="form.unit">{{ form.unit }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in optsWith(OPTS.units, form.unit)" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Agreement</Label>
                            <Select v-model="form.agreement">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select agreement"><template v-if="form.agreement">{{ form.agreement }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.agreements" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- ── Step 3: Payment Information ─────────────────────────── -->
                    <div v-show="step === 3" class="grid grid-cols-2 gap-5 p-6">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Against Invoice</Label>
                            <Select v-model="form.invoice">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select invoice"><template v-if="form.invoice">{{ form.invoice }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in optsWith(OPTS.invoices, form.invoice)" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Installment</Label>
                            <Select v-model="form.installment">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select installment"><template v-if="form.installment">{{ form.installment }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.installments" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Payment Type</Label>
                            <Select v-model="form.ptype">
                                <SelectTrigger :class="f">
                                    <SelectValue placeholder="Select type"><template v-if="form.ptype">{{ form.ptype }}</template></SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in optsWith(OPTS.ptypes, form.ptype)" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                Amount (BDT) <span class="text-destructive">*</span>
                            </Label>
                            <Input v-model="form.amount" placeholder="e.g. 340,000" :class="f" />
                        </div>
                    </div>

                    <!-- ── Step 4: Payment Method ───────────────────────────────── -->
                    <div v-show="step === 4" class="grid grid-cols-2 gap-5 p-6">
                        <div class="col-span-2 space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                Payment Method <span class="text-destructive">*</span>
                            </Label>
                            <div class="grid grid-cols-4 gap-2">
                                <button v-for="m in optsWith(OPTS.methods, form.method)" :key="m"
                                    type="button"
                                    @click="form.method = m"
                                    :class="[
                                        'rounded-lg border px-3 py-2.5 text-sm font-medium transition-all text-center',
                                        form.method === m
                                            ? 'border-admin-accent bg-admin-accent/10 text-admin-accent'
                                            : 'border-slate-200 dark:border-white/[0.09] bg-slate-50 dark:bg-white/[0.04] text-foreground hover:border-admin-accent/50',
                                    ]">
                                    {{ m }}
                                </button>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Currency</Label>
                            <Select v-model="form.currency">
                                <SelectTrigger :class="f"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.currencies" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- ── Step 5: Bank Details ────────────────────────────────── -->
                    <div v-show="step === 5" class="grid grid-cols-2 gap-5 p-6">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Transaction ID</Label>
                            <Input v-model="form.txn" placeholder="TXN-..." :class="f" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Reference Number</Label>
                            <Input v-model="form.refno" placeholder="REF-..." :class="f" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Bank</Label>
                            <Select v-model="form.bank">
                                <SelectTrigger :class="f"><SelectValue placeholder="Select bank" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.banks" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Branch</Label>
                            <Input v-model="form.branch" placeholder="e.g. Gulshan" :class="f" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Deposit Date</Label>
                            <Input v-model="form.depdate" type="date" :class="f" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Verification Status</Label>
                            <Select v-model="form.verify">
                                <SelectTrigger :class="f"><SelectValue placeholder="Select status" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.verify" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- ── Step 6: Supporting Documents ───────────────────────── -->
                    <div v-show="step === 6" class="p-6">
                        <div class="grid grid-cols-3 gap-4 mb-5">
                            <div v-for="doc in [['receipt','Receipt','PDF / image'],['cheque','Cheque Image','Image'],['slip','Transfer Slip','PDF / image']]"
                                :key="doc[0]" class="space-y-1.5">
                                <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ doc[1] }}</Label>
                                <div class="flex flex-col items-center gap-2 rounded-lg border-2 border-dashed border-slate-200 dark:border-white/[0.09] bg-slate-50 dark:bg-white/[0.04] p-5 text-center cursor-pointer hover:border-admin-accent/50 transition-colors">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-admin-accent/10 text-admin-accent">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
                                    </div>
                                    <p class="text-xs font-medium text-foreground">Upload {{ doc[1].toLowerCase() }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ doc[2] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Notes / Remarks</Label>
                            <textarea v-model="form.notes" placeholder="Any remarks about this payment…" rows="3"
                                :class="['w-full resize-none px-3.5 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none', f]" />
                        </div>
                    </div>

                    <!-- ── Step 7: Accounting ──────────────────────────────────── -->
                    <div v-show="step === 7" class="grid grid-cols-2 gap-5 p-6">
                        <div class="col-span-2 space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Ledger Account</Label>
                            <Select v-model="form.ledger">
                                <SelectTrigger :class="f"><SelectValue placeholder="Select ledger" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.ledgers" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Tax (%)</Label>
                            <Input v-model="form.tax" placeholder="e.g. 0" type="number" :class="f" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">VAT (BDT)</Label>
                            <Input v-model="form.vat" placeholder="e.g. 0" type="number" :class="f" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Discount (BDT)</Label>
                            <Input v-model="form.discount" placeholder="e.g. 0" type="number" :class="f" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Late Penalty (BDT)</Label>
                            <Input v-model="form.penalty" placeholder="e.g. 0" type="number" :class="f" />
                        </div>
                        <div class="col-span-2 space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Finance Code</Label>
                            <Input v-model="form.fincode" placeholder="e.g. FC-1042" :class="f" />
                        </div>
                    </div>

                    <!-- ── Step 8: Approval ────────────────────────────────────── -->
                    <div v-show="step === 8" class="grid grid-cols-2 gap-5 p-6">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Finance Approval</Label>
                            <Select v-model="form.finapp">
                                <SelectTrigger :class="f"><SelectValue placeholder="Select status" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.approvals" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Manager Approval</Label>
                            <Select v-model="form.mgrapp">
                                <SelectTrigger :class="f"><SelectValue placeholder="Select status" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.approvals" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="col-span-2 space-y-1.5">
                            <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Compliance Check</Label>
                            <Select v-model="form.compliance">
                                <SelectTrigger :class="f"><SelectValue placeholder="Select status" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in OPTS.compliance" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- ── Step 9: Review ──────────────────────────────────────── -->
                    <div v-show="step === 9" class="flex flex-col gap-5 p-6">
                        <div>
                            <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-foreground">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4L12 3z"/></svg>
                                AI Validation &amp; Duplicate Check
                            </div>
                            <div class="flex flex-col gap-2">
                                <div v-for="c in checks" :key="c.label"
                                    class="flex items-center gap-3 rounded-lg border border-border bg-slate-50 dark:bg-white/[0.02] p-3">
                                    <div :class="['flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-xs font-bold', c.bg, c.color]">
                                        {{ c.icon }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-foreground">{{ c.label }}</div>
                                        <div class="text-xs text-muted-foreground">{{ c.note }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-border pt-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Summary</p>
                            <div class="divide-y divide-border rounded-lg border border-border overflow-hidden">
                                <div v-for="[label, value] in [
                                    ['Ref',     form.invoice  || prefill?.ref || '—'],
                                    ['Buyer',   displayBuyer?.name || '—'],
                                    ['Unit',    form.unit     || prefill?.unit    || '—'],
                                    ['Type',    form.ptype    || prefill?.ptype   || '—'],
                                    ['Method',  form.method   || prefill?.method  || '—'],
                                    ['Amount',  'BDT ' + (form.amount || prefill?.amount || '—')],
                                ]" :key="label" class="flex items-center justify-between px-4 py-3">
                                    <span class="text-sm text-muted-foreground">{{ label }}</span>
                                    <span class="text-sm font-semibold text-foreground">{{ value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Step 10: Complete ───────────────────────────────────── -->
                    <div v-show="step === 10" class="p-6">
                        <div class="rounded-lg border border-border divide-y divide-border overflow-hidden mb-4">
                            <div v-for="[label, value] in [
                                ['Payment ID',  isEdit ? prefill.ref : 'PAY-2026-1188'],
                                ['Receipt',     'REC-2026-1188 (will be generated)'],
                                ['Action',      'Receipt · Email · Post to ledger'],
                            ]" :key="label" class="flex items-center justify-between px-4 py-3.5">
                                <span class="text-sm text-muted-foreground">{{ label }}</span>
                                <span class="text-sm font-semibold text-foreground">{{ value }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Clicking <b>Post Payment</b> will lock this record, generate a receipt, and email it to the buyer.
                        </p>
                    </div>

                    <!-- ── Footer navigation ──────────────────────────────────── -->
                    <div class="flex items-center justify-between border-t border-border px-6 py-4">
                        <button type="button" @click="back" :disabled="step === 1"
                            class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/>
                            </svg>
                            Back
                        </button>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="showToast('Draft saved')"
                                class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                                    <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                                </svg>
                                Save Draft
                            </button>

                            <button v-if="step < TOTAL_STEPS" type="button" @click="next"
                                class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-4 text-sm font-medium text-on-gold transition-colors hover:bg-admin-accent/90">
                                Next
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/>
                                </svg>
                            </button>

                            <button v-else type="button" @click="post"
                                class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-5 text-sm font-semibold text-on-gold transition-colors hover:bg-admin-accent/90">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4 19-7z"/>
                                </svg>
                                {{ isEdit ? 'Update Payment' : 'Post Payment' }}
                            </button>
                        </div>
                    </div>

                </template>
            </div>
        </div>

        <!-- Toast -->
        <Transition enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0 translate-y-2"
            enter-active-class="transition duration-200" leave-active-class="transition duration-150">
            <div v-if="toast"
                class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 flex items-center gap-2.5 rounded-xl bg-foreground px-4 py-3 text-sm font-semibold text-background shadow-2xl">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-success">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#0A0C10" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                </span>
                {{ toast }}
            </div>
        </Transition>

    </AdminLayout>
</template>
