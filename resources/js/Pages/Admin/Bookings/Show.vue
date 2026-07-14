<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    booking: { type: Object, required: true },
});

const tab = ref('Overview');
const cancelling = ref(false);
const cancelReason = ref('Buyer financing fell through');

const statusClasses = {
    reserved:    'bg-warning/15 text-warning',
    purchased:   'bg-success/15 text-success',
    cancelled:   'bg-destructive/15 text-destructive',
    handed_over: 'bg-chart-4/15 text-chart-4',
};

const tabs = ['Overview', 'Buyer', 'Project', 'Unit', 'Pricing', 'Payments', 'Mortgage', 'Agreement', 'Approvals', 'Timeline', 'Activity'];

function fmt(n) {
    return 'BDT ' + (Number(n) || 0).toLocaleString();
}

const panels = computed(() => {
    const b = props.booking;
    return {
        Buyer: {
            sub: 'Buyer identity and contact for this reservation.',
            link: b.buyer ? route('admin.clients.show', b.buyer.id) : null,
            linkLabel: 'Open Buyer Profile',
            rows: b.buyer ? [
                ['Name', b.buyer.name], ['Phone', b.buyer.phone ?? '—'], ['Email', b.buyer.email ?? '—'], ['National ID', b.buyer.nid ?? '—'],
            ] : [],
        },
        Project: {
            sub: 'Project this unit belongs to.',
            rows: b.project ? [
                ['Project', b.project.name], ['Location', b.project.location ?? '—'], ['Expected Handover', b.project.handover ?? '—'],
            ] : [],
        },
        Unit: {
            sub: 'Unit specifications.',
            rows: b.unit ? [
                ['Unit', b.unit.unit_number], ['Type', b.unit.type ?? '—'], ['Floor', b.unit.floor ?? '—'],
                ['Bedrooms', b.unit.bedrooms ?? '—'], ['Size', b.unit.size_sqft ? b.unit.size_sqft + ' sqft' : '—'], ['Status', b.unit.status],
            ] : [],
        },
        Pricing: {
            sub: 'Price breakdown for this reservation.',
            rows: [
                ['Base Price', fmt(b.pricing.base_price)],
                ['Discount (' + b.pricing.discount_pct + '%)', '− ' + fmt(b.pricing.discount_amount)],
                ['Final Price', fmt(b.pricing.final_price)],
            ],
        },
        Payments: {
            sub: 'Installment plan and collection.',
            rows: [
                ['Plan', b.payment_plan?.plan_type ?? '—'],
                ['Down Payment', fmt(b.payment_plan?.down_payment)],
                ['Collected', fmt(b.payments.collected) + ' of ' + fmt(b.payments.total)],
                ['Next Due', b.payments.next_due ? b.payments.next_due.due_date + ' · ' + fmt(b.payments.next_due.amount) : 'Fully scheduled'],
            ],
        },
        Mortgage: {
            sub: 'Financing details.',
            rows: b.meta.mortgage ? Object.entries(b.meta.mortgage).map(([k, v]) => [k.replace(/_/g, ' '), v || '—']) : [],
        },
        Agreement: {
            sub: 'Sale agreement status.',
            rows: b.meta.agreement ? Object.entries(b.meta.agreement).map(([k, v]) => [k.replace(/_/g, ' '), v || '—']) : [],
        },
        Approvals: {
            sub: 'Sign-off chain for this reservation.',
            rows: b.meta.approvals ? Object.entries(b.meta.approvals).map(([k, v]) => [k.replace(/_/g, ' '), v || '—']) : [],
        },
        Timeline: {
            sub: 'Key milestones for this reservation.',
            rows: b.timeline.map(t => [t.label, t.date ?? '—']),
        },
        Activity: {
            sub: 'Recent activity on this reservation.',
            rows: b.activity.length ? b.activity.map(a => [a.label, a.date ?? '—']) : [['No activity recorded yet', '']],
        },
    };
});

function convertToSale() {
    if (!confirm('Convert this reservation into a completed sale?')) return;
    router.patch(route('admin.bookings.status', props.booking.id), { status: 'purchased' });
}

function confirmCancel() {
    router.patch(route('admin.bookings.status', props.booking.id), { status: 'cancelled', reason: cancelReason.value }, {
        onSuccess: () => { cancelling.value = false; },
    });
}
</script>

<template>
    <Head :title="booking.code" />

    <AdminLayout :title="booking.code" :breadcrumbs="[{ label: 'Admin' }, { label: 'Bookings', href: route('admin.bookings.index') }, { label: booking.code }]">
        <div class="space-y-6">
            <!-- Hero -->
            <section class="flex flex-wrap items-center justify-between gap-6 rounded-[24px] border border-border bg-card p-6 shadow-card">
                <div class="flex items-center gap-4">
                    <div class="flex h-[62px] w-[62px] items-center justify-center rounded-2xl bg-gold-gradient text-on-gold">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="text-[22px] font-extrabold tracking-tight text-foreground">{{ booking.code }}</div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClasses[booking.status] ?? 'bg-muted text-muted-foreground'">{{ booking.status_label }}</span>
                        </div>
                        <div class="mt-1 text-sm text-muted-foreground">{{ booking.buyer?.name }} · Unit {{ booking.unit?.unit_number }} · {{ booking.project?.name }} · Reserved {{ booking.booking_date }}</div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.bookings.edit', booking.id)" class="rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground/80 transition-colors hover:bg-muted">Edit</Link>
                    <button v-if="booking.status === 'reserved'" type="button" class="rounded-xl border border-destructive/30 bg-card px-4 py-2.5 text-sm font-semibold text-destructive transition-colors hover:bg-destructive/10" @click="cancelling = true">Cancel Reservation</button>
                    <button v-if="booking.status === 'reserved'" type="button" class="rounded-xl bg-gold-gradient px-4 py-2.5 text-sm font-semibold text-on-gold shadow-gold-glow" @click="convertToSale">Convert to Sale</button>
                </div>
            </section>

            <!-- Tabs -->
            <div class="flex items-center gap-1 overflow-x-auto border-b border-border">
                <button
                    v-for="t in tabs" :key="t" type="button"
                    class="whitespace-nowrap border-b-2 px-3.5 py-3 text-sm font-semibold transition-colors"
                    :class="tab === t ? 'border-gold text-brand' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="tab = t"
                >{{ t }}</button>
            </div>

            <!-- Overview -->
            <div v-if="tab === 'Overview'" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="flex flex-col gap-5">
                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-4 text-base font-semibold text-foreground">Reservation Summary</div>
                        <div class="grid grid-cols-3 gap-4">
                            <div><div class="text-xs text-muted-foreground">Status</div><div class="mt-1 font-semibold text-foreground">{{ booking.status_label }}</div></div>
                            <div><div class="text-xs text-muted-foreground">Reserved On</div><div class="mt-1 font-semibold text-foreground">{{ booking.booking_date }}</div></div>
                            <div><div class="text-xs text-muted-foreground">Expires</div><div class="mt-1 font-semibold text-warning">{{ booking.reserved_until ?? '—' }}</div></div>
                            <div><div class="text-xs text-muted-foreground">Down Payment</div><div class="mt-1 font-semibold text-foreground">{{ fmt(booking.payment_plan?.down_payment) }}</div></div>
                            <div><div class="text-xs text-muted-foreground">Final Price</div><div class="mt-1 font-semibold text-foreground">{{ fmt(booking.pricing.final_price) }}</div></div>
                            <div><div class="text-xs text-muted-foreground">Source</div><div class="mt-1 font-semibold text-foreground">{{ booking.source ?? '—' }}</div></div>
                        </div>
                    </div>

                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-4 text-base font-semibold text-foreground">Payment Progress</div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Collected: <b class="text-foreground">{{ fmt(booking.payments.collected) }}</b> of {{ fmt(booking.payments.total) }}</span>
                            <span class="font-semibold text-brand">{{ booking.payments.percent }}%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-gold-gradient" :style="{ width: booking.payments.percent + '%' }"></div>
                        </div>
                    </div>

                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-4 text-base font-semibold text-foreground">Approval Status</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="(v, k) in (booking.meta.approvals ?? {})" :key="k" class="flex items-center gap-2 rounded-xl border border-border px-3 py-2.5">
                                <span class="flex h-[22px] w-[22px] items-center justify-center rounded-md text-xs font-bold" :class="v === 'Approved' ? 'bg-success/15 text-success' : 'bg-muted text-muted-foreground'">{{ v === 'Approved' ? '✓' : '·' }}</span>
                                <span class="text-sm font-semibold capitalize text-foreground/80">{{ k }} Approval</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-5">
                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-3 text-xs font-bold uppercase tracking-wider text-muted-foreground">Buyer</div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-[46px] w-[46px] items-center justify-center rounded-full bg-gold-gradient text-sm font-bold text-on-gold">{{ (booking.buyer?.name ?? '?').split(' ').map(w => w[0]).slice(0,2).join('') }}</div>
                            <div><div class="font-semibold text-foreground">{{ booking.buyer?.name }}</div><div class="text-xs text-muted-foreground">{{ booking.buyer?.phone }}</div></div>
                        </div>
                        <Link v-if="booking.buyer" :href="route('admin.clients.show', booking.buyer.id)" class="mt-4 block text-xs font-bold text-brand hover:underline">View Profile →</Link>
                    </div>
                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-3 text-xs font-bold uppercase tracking-wider text-muted-foreground">Unit &amp; Project</div>
                        <div class="text-[15px] font-extrabold text-foreground">Unit {{ booking.unit?.unit_number }}</div>
                        <div class="mt-1 text-[12.5px] text-muted-foreground">{{ booking.project?.name }} · Floor {{ booking.unit?.floor ?? '—' }} · {{ booking.unit?.bedrooms ?? '—' }} Bed · {{ booking.unit?.size_sqft ?? '—' }} sqft</div>
                    </div>
                    <div class="rounded-[18px] border border-border bg-card p-5 shadow-card">
                        <div class="mb-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">Sales Representative</div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-[42px] w-[42px] items-center justify-center rounded-full bg-chart-5/15 text-sm font-bold text-chart-5">{{ (booking.sales_rep?.name ?? '?').split(' ').map(w => w[0]).slice(0,2).join('') }}</div>
                            <div><div class="font-semibold text-foreground">{{ booking.sales_rep?.name ?? '—' }}</div><div class="text-xs text-muted-foreground">{{ booking.sales_rep?.deals ?? 0 }} deals</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generic tab panels -->
            <div v-else class="rounded-[18px] border border-border bg-card p-6 shadow-card">
                <div class="mb-1 text-lg font-semibold text-foreground">{{ tab }}</div>
                <div class="mb-5 text-xs text-muted-foreground">{{ panels[tab]?.sub }}</div>
                <div class="flex flex-col gap-2.5">
                    <div v-for="(row, i) in panels[tab]?.rows" :key="i" class="flex items-center justify-between gap-3 rounded-xl border border-border px-4 py-3.5">
                        <span class="text-sm capitalize text-muted-foreground">{{ row[0] }}</span>
                        <span class="text-sm font-semibold text-foreground">{{ row[1] }}</span>
                    </div>
                    <div v-if="!panels[tab]?.rows?.length" class="py-6 text-center text-sm text-muted-foreground">No data recorded.</div>
                </div>
                <Link v-if="panels[tab]?.link" :href="panels[tab].link" class="mt-4 inline-flex items-center gap-1 rounded-xl bg-gold/10 px-4 py-2 text-xs font-bold text-brand hover:bg-gold-gradient hover:text-on-gold">{{ panels[tab].linkLabel }} →</Link>
            </div>
        </div>

        <!-- Cancel modal -->
        <Teleport to="body">
            <div v-if="cancelling" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="cancelling = false" />
                <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-xl">
                    <h3 class="mb-1 font-semibold text-foreground">Cancel Reservation</h3>
                    <p class="mb-4 text-sm text-muted-foreground">This will release the unit back to Available. Select a reason.</p>
                    <select v-model="cancelReason" class="mb-5 h-[44px] w-full rounded-xl border border-border bg-background px-3 text-sm text-foreground">
                        <option>Buyer financing fell through</option>
                        <option>Buyer chose another unit</option>
                        <option>Price renegotiation failed</option>
                        <option>Other</option>
                    </select>
                    <div class="flex justify-end gap-3">
                        <button class="rounded-xl border border-border px-4 py-2 text-sm font-semibold text-foreground/80 hover:bg-muted" @click="cancelling = false">Back</button>
                        <button class="rounded-xl bg-destructive px-4 py-2 text-sm font-semibold text-destructive-foreground hover:bg-destructive/90" @click="confirmCancel">Confirm Cancellation</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
