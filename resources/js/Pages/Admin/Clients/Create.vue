<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientForm  from './partials/ClientForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    enums: { type: Object, required: true },
});

const form = useForm({
    // Step 1 — Personal Information
    name:                   '',
    father_name:            '',
    mother_name:            '',
    date_of_birth:          null,
    gender:                 null,
    marital_status:         null,
    nationality:            'Bangladeshi',

    // Step 2 — Contact Details
    email:                  '',
    phone:                  '',
    alternate_phone:        '',
    whatsapp:               '',
    emergency_contact_name: '',
    emergency_contact_phone:'',

    // Step 3 — National ID / Passport
    nid:                       '',
    birth_certificate_number:  '',
    passport_no:               '',
    passport_expiry:           null,

    // Step 4 — Address
    address:                '',
    country:                null,
    city:                   '',
    state:                  '',
    postal_code:            '',
    permanent_address:      '',

    // Step 5 — Employment
    occupation:             '',
    employment_type:        null,
    company:                '',
    designation:            '',
    industry:               null,
    office_address:         '',
    tenure:                 '',

    // Step 6 — Income & Financials
    monthly_income:         '',
    annual_income:          '',
    other_income:           '',
    existing_loans:         '',
    bank_name:              '',
    account_number:         '',

    // Step 7 — Co-applicant
    coapplicant_name:                 '',
    coapplicant_relationship:         '',
    coapplicant_dob:                  null,
    coapplicant_phone:                '',
    coapplicant_email:                '',
    coapplicant_nid:                  '',
    coapplicant_occupation:           '',
    coapplicant_monthly_income:       '',
    coapplicant_annual_income:        '',
    coapplicant_tin:                  '',
    coapplicant_ownership_percentage: '',
    coapplicant_address:              '',
    coapplicant_signature:            '',

    // Meta
    is_draft:               false,
    password:               '',
    source:                 null,
    status:                 'active',
    notes:                  '',

    // Step 8 & 9 — Documents & Photo
    avatar:                 null,
    remove_avatar:          false,
    new_kyc_documents:      [],
    remove_kyc_documents:   [],
});

function submit(type = 'final') {
    form.is_draft = type === 'draft';
    form.post(route('admin.clients.store'), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="New Client" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.clients.index')" class="hover:text-admin-accent transition-colors">Clients</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">New Client</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">Add New Client</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Register a new client with their contact, identity and financial details.</p>
        </div>

        <ClientForm
            :form="form"
            :enums="enums"
            mode="create"
            :current-avatar="null"
            :current-kyc="[]"
            @submit="submit"
        />
    </AdminLayout>
</template>
