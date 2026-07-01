<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    booking: { type: Object, required: true },
    enums:   { type: Object, required: true },
});

const form = useForm({
    status:         props.booking.status,
    price_agreed:   props.booking.price_agreed,
    discount_pct:   props.booking.discount_pct,
    reserved_until: props.booking.reserved_until ?? '',
    sales_rep_id:   props.booking.sales_rep_id ?? '',
    source:         props.booking.source ?? '',
    priority:       props.booking.priority ?? 'Normal',
    notes:          props.booking.notes ?? '',
});

function submit() {
    form.put(route('admin.bookings.update', props.booking.id));
}
</script>

<template>
    <Head :title="'Edit ' + booking.buyer_name" />

    <AdminLayout title="Edit Reservation" :breadcrumbs="[{ label: 'Admin' }, { label: 'Bookings', href: route('admin.bookings.index') }, { label: 'Edit' }]">
        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-[18px] border border-slate-200 bg-white px-6 py-4 shadow-[0_8px_24px_rgba(20,20,40,0.05)]">
                <div class="flex items-center gap-3">
                    <button type="button" class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-[11px] border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50" @click="router.visit(route('admin.bookings.show', booking.id))">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6" /></svg>
                    </button>
                    <div>
                        <div class="text-[18px] font-[800] tracking-[-0.4px] text-slate-900">Edit Reservation</div>
                        <div class="text-xs text-slate-400">{{ booking.buyer_name }} · Unit {{ booking.unit_number }}</div>
                    </div>
                </div>
                <button type="button" class="rounded-[10px] border border-slate-200 bg-white px-3.5 py-2 text-[12.5px] font-semibold text-slate-700 transition hover:bg-slate-50" @click="router.visit(route('admin.bookings.show', booking.id))">
                    Cancel
                </button>
            </div>

            <!-- Form card -->
            <form class="grid gap-x-5 gap-y-4 rounded-[18px] border border-slate-200 bg-white p-7 shadow-[0_8px_24px_rgba(20,20,40,0.05)] sm:grid-cols-2" @submit.prevent="submit">
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Status</span>
                    <select v-model="form.status" class="h-[46px] w-full rounded-[11px] border border-slate-200 px-3.5 text-[13.5px] text-slate-700">
                        <option v-for="s in enums.statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-xs text-rose-600">{{ form.errors.status }}</p>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Final Price (BDT)</span>
                    <input v-model.number="form.price_agreed" type="number" min="0" class="h-[46px] w-full rounded-[11px] border border-slate-200 px-3.5 text-[13.5px] text-slate-700" />
                    <p v-if="form.errors.price_agreed" class="mt-1 text-xs text-rose-600">{{ form.errors.price_agreed }}</p>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Discount (%)</span>
                    <input v-model.number="form.discount_pct" type="number" min="0" max="100" step="0.5" class="h-[46px] w-full rounded-[11px] border border-slate-200 px-3.5 text-[13.5px] text-slate-700" />
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Expiry Date</span>
                    <input v-model="form.reserved_until" type="date" class="h-[46px] w-full rounded-[11px] border border-slate-200 px-3.5 text-[13.5px] text-slate-700" />
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Sales Representative</span>
                    <select v-model="form.sales_rep_id" class="h-[46px] w-full rounded-[11px] border border-slate-200 px-3.5 text-[13.5px] text-slate-700">
                        <option value="">Unassigned</option>
                        <option v-for="r in enums.sales_reps" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Source</span>
                    <select v-model="form.source" class="h-[46px] w-full rounded-[11px] border border-slate-200 px-3.5 text-[13.5px] text-slate-700">
                        <option value="">—</option>
                        <option v-for="s in enums.sources" :key="s" :value="s">{{ s }}</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Priority</span>
                    <select v-model="form.priority" class="h-[46px] w-full rounded-[11px] border border-slate-200 px-3.5 text-[13.5px] text-slate-700">
                        <option v-for="p in enums.priorities" :key="p" :value="p">{{ p }}</option>
                    </select>
                </label>

                <label class="col-span-full block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500">Notes</span>
                    <textarea v-model="form.notes" rows="4" class="w-full rounded-[11px] border border-slate-200 px-3.5 py-2.5 text-[13.5px] text-slate-700"></textarea>
                </label>

                <div class="col-span-full mt-2 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <button type="button" class="rounded-[11px] border border-slate-200 px-4 py-2.5 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50" @click="router.visit(route('admin.bookings.show', booking.id))">
                        Discard
                    </button>
                    <button type="submit" :disabled="form.processing" class="rounded-[11px] bg-gradient-to-br from-violet-600 to-violet-500 px-5 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-violet-600/30 transition disabled:opacity-60">
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
