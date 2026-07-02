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
    reserved:    'bg-amber-50 text-amber-700',
    purchased:   'bg-emerald-50 text-emerald-700',
    cancelled:   'bg-rose-50 text-rose-700',
    handed_over: 'bg-violet-50 text-violet-700',
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
            <section class="flex flex-wrap items-center justify-between gap-6 rounded-[24px] border border-slate-200 bg-white p-6 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                <div class="flex items-center gap-4">
                    <div class="flex h-[62px] w-[62px] items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-violet-500 text-white">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="text-[22px] font-[800] tracking-[-0.4px] text-slate-900">{{ booking.code }}</div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClasses[booking.status] ?? 'bg-slate-50 text-slate-700'">{{ booking.status_label }}</span>
                        </div>
                        <div class="mt-1 text-sm text-slate-500">{{ booking.buyer?.name }} · Unit {{ booking.unit?.unit_number }} · {{ booking.project?.name }} · Reserved {{ booking.booking_date }}</div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.bookings.edit', booking.id)" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Edit</Link>
                    <button v-if="booking.status === 'reserved'" type="button" class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50" @click="cancelling = true">Cancel Reservation</button>
                    <button v-if="booking.status === 'reserved'" type="button" class="rounded-xl bg-gradient-to-br from-violet-600 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/25" @click="convertToSale">Convert to Sale</button>
                </div>
            </section>

            <!-- Tabs -->
            <div class="flex items-center gap-1 overflow-x-auto border-b border-slate-200">
                <button
                    v-for="t in tabs" :key="t" type="button"
                    class="whitespace-nowrap border-b-2 px-3.5 py-3 text-sm font-semibold transition"
                    :class="tab === t ? 'border-violet-600 text-violet-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    @click="tab = t"
                >{{ t }}</button>
            </div>

            <!-- Overview -->
            <div v-if="tab === 'Overview'" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="flex flex-col gap-5">
                    <div class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                        <div class="mb-4 text-base font-semibold text-slate-900">Reservation Summary</div>
                        <div class="grid grid-cols-3 gap-4">
                            <div><div class="text-xs text-slate-400">Status</div><div class="mt-1 font-semibold text-slate-900">{{ booking.status_label }}</div></div>
                            <div><div class="text-xs text-slate-400">Reserved On</div><div class="mt-1 font-semibold text-slate-900">{{ booking.booking_date }}</div></div>
                            <div><div class="text-xs text-slate-400">Expires</div><div class="mt-1 font-semibold text-amber-600">{{ booking.reserved_until ?? '—' }}</div></div>
                            <div><div class="text-xs text-slate-400">Down Payment</div><div class="mt-1 font-semibold text-slate-900">{{ fmt(booking.payment_plan?.down_payment) }}</div></div>
                            <div><div class="text-xs text-slate-400">Final Price</div><div class="mt-1 font-semibold text-slate-900">{{ fmt(booking.pricing.final_price) }}</div></div>
                            <div><div class="text-xs text-slate-400">Source</div><div class="mt-1 font-semibold text-slate-900">{{ booking.source ?? '—' }}</div></div>
                        </div>
                    </div>

                    <div class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                        <div class="mb-4 text-base font-semibold text-slate-900">Payment Progress</div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="text-slate-500">Collected: <b class="text-slate-900">{{ fmt(booking.payments.collected) }}</b> of {{ fmt(booking.payments.total) }}</span>
                            <span class="font-semibold text-violet-600">{{ booking.payments.percent }}%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-violet-600 to-violet-500" :style="{ width: booking.payments.percent + '%' }"></div>
                        </div>
                    </div>

                    <div class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                        <div class="mb-4 text-base font-semibold text-slate-900">Approval Status</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="(v, k) in (booking.meta.approvals ?? {})" :key="k" class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5">
                                <span class="flex h-[22px] w-[22px] items-center justify-center rounded-md text-xs font-bold" :class="v === 'Approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400'">{{ v === 'Approved' ? '✓' : '·' }}</span>
                                <span class="text-sm font-semibold capitalize">{{ k }} Approval</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-5">
                    <div class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                        <div class="mb-3 text-xs font-bold uppercase tracking-[0.4px] text-slate-400">Buyer</div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-[46px] w-[46px] items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">{{ (booking.buyer?.name ?? '?').split(' ').map(w => w[0]).slice(0,2).join('') }}</div>
                            <div><div class="font-semibold text-slate-900">{{ booking.buyer?.name }}</div><div class="text-xs text-slate-400">{{ booking.buyer?.phone }}</div></div>
                        </div>
                        <Link v-if="booking.buyer" :href="route('admin.clients.show', booking.buyer.id)" class="mt-4 block text-xs font-bold text-violet-600">View Profile →</Link>
                    </div>
                    <div class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                        <div class="mb-3 text-xs font-bold uppercase tracking-[0.4px] text-slate-400">Unit &amp; Project</div>
                        <div class="text-[15px] font-[800] text-slate-900">Unit {{ booking.unit?.unit_number }}</div>
                        <div class="mt-1 text-[12.5px] text-slate-500">{{ booking.project?.name }} · Floor {{ booking.unit?.floor ?? '—' }} · {{ booking.unit?.bedrooms ?? '—' }} Bed · {{ booking.unit?.size_sqft ?? '—' }} sqft</div>
                    </div>
                    <div class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                        <div class="mb-2 text-xs font-bold uppercase tracking-[0.4px] text-slate-400">Sales Representative</div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-[42px] w-[42px] items-center justify-center rounded-full bg-pink-100 text-sm font-bold text-pink-700">{{ (booking.sales_rep?.name ?? '?').split(' ').map(w => w[0]).slice(0,2).join('') }}</div>
                            <div><div class="font-semibold text-slate-900">{{ booking.sales_rep?.name ?? '—' }}</div><div class="text-xs text-slate-400">{{ booking.sales_rep?.deals ?? 0 }} deals</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generic tab panels -->
            <div v-else class="rounded-[18px] border border-slate-200 bg-white p-6 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                <div class="mb-1 text-lg font-semibold text-slate-900">{{ tab }}</div>
                <div class="mb-5 text-xs text-slate-400">{{ panels[tab]?.sub }}</div>
                <div class="flex flex-col gap-2.5">
                    <div v-for="(row, i) in panels[tab]?.rows" :key="i" class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 px-4 py-3.5">
                        <span class="text-sm capitalize text-slate-500">{{ row[0] }}</span>
                        <span class="text-sm font-semibold text-slate-900">{{ row[1] }}</span>
                    </div>
                    <div v-if="!panels[tab]?.rows?.length" class="py-6 text-center text-sm text-slate-400">No data recorded.</div>
                </div>
                <a v-if="panels[tab]?.link" :href="panels[tab].link" class="mt-4 inline-flex items-center gap-1 rounded-xl bg-violet-50 px-4 py-2 text-xs font-bold text-violet-600">{{ panels[tab].linkLabel }} →</a>
            </div>
        </div>

        <!-- Cancel modal -->
        <Teleport to="body">
            <div v-if="cancelling" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="cancelling = false" />
                <div class="relative z-10 w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                    <h3 class="mb-1 font-semibold text-slate-900">Cancel Reservation</h3>
                    <p class="mb-4 text-sm text-slate-500">This will release the unit back to Available. Select a reason.</p>
                    <select v-model="cancelReason" class="mb-5 h-[44px] w-full rounded-xl border border-slate-200 px-3 text-sm text-slate-700">
                        <option>Buyer financing fell through</option>
                        <option>Buyer chose another unit</option>
                        <option>Price renegotiation failed</option>
                        <option>Other</option>
                    </select>
                    <div class="flex justify-end gap-3">
                        <button class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="cancelling = false">Back</button>
                        <button class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700" @click="confirmCancel">Confirm Cancellation</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
