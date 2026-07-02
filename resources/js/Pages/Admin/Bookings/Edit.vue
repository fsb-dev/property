<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BookingForm from './partials/BookingForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    booking: { type: Object, required: true },
    enums:   { type: Object, required: true },
});

const form = useForm({
    buyer_mode: props.booking.buyer_mode,
    client_id: props.booking.client_id ?? '',
    new_client_name: props.booking.new_client_name ?? '',
    new_client_phone: props.booking.new_client_phone ?? '',
    new_client_email: props.booking.new_client_email ?? '',

    unit_id: props.booking.unit_id ?? '',

    booking_date: props.booking.booking_date ?? new Date().toISOString().slice(0, 10),
    reserved_until: props.booking.reserved_until ?? '',
    source: props.booking.source ?? '',
    priority: props.booking.priority ?? 'Normal',
    sales_rep_id: props.booking.sales_rep_id ?? '',
    notes: props.booking.notes ?? '',

    discount_pct: props.booking.discount_pct ?? 0,
    price_agreed: props.booking.price_agreed ?? 0,

    plan_type: props.booking.plan_type ?? '24-month installment',
    down_payment: props.booking.down_payment ?? '',
    total_installments: props.booking.total_installments ?? 24,

    meta: props.booking.meta,
});

function submit() {
    form.put(route('admin.bookings.update', props.booking.id));
}
</script>

<template>
    <Head :title="'Edit ' + (booking.buyer_name ?? 'Reservation')" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.bookings.index')" class="hover:text-admin-accent transition-colors">Bookings</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <Link :href="route('admin.bookings.show', booking.id)" class="hover:text-admin-accent transition-colors">{{ booking.buyer_name ?? ('RSV-' + booking.id) }}</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">Edit</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">Edit Reservation</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Update buyer, unit, pricing and plan details for this reservation.</p>
        </div>

        <BookingForm
            :form="form"
            :enums="enums"
            mode="edit"
            @submit="submit"
        />
    </AdminLayout>
</template>
