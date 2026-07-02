<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BookingForm from './partials/BookingForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    enums: { type: Object, required: true },
});

const form = useForm({
    draft_id: null,

    buyer_mode: 'existing',
    client_id: '',
    new_client_name: '',
    new_client_phone: '',
    new_client_email: '',

    unit_id: '',

    booking_date: new Date().toISOString().slice(0, 10),
    reserved_until: '',
    source: '',
    priority: 'Normal',
    sales_rep_id: '',
    notes: '',

    discount_pct: 0,
    price_agreed: 0,

    plan_type: '24-month installment',
    down_payment: '',
    total_installments: 24,

    meta: {
        mortgage: { loan_required: 'No', eligible_bank: '', loan_amount: '', interest_rate: '', indicative_emi: '', status: '' },
        approvals: { sales: 'Pending', finance: 'Pending', manager: 'Pending', legal: 'Pending' },
    },
});

function submit() {
    form.post(route('admin.bookings.store'));
}
</script>

<template>
    <Head title="New Reservation" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.bookings.index')" class="hover:text-admin-accent transition-colors">Bookings</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">New Reservation</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">New Reservation</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Reserve a unit for a buyer with pricing, payment plan and approvals.</p>
        </div>

        <BookingForm
            :form="form"
            :enums="enums"
            mode="create"
            @submit="submit"
        />
    </AdminLayout>
</template>
