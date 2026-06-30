<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientForm  from './partials/ClientForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    client: { type: Object, required: true },
    enums:  { type: Object, required: true },
});

const form = useForm({
    // Step 1 — Personal Information
    name:                   props.client.name,
    father_name:            props.client.father_name            ?? '',
    mother_name:            props.client.mother_name            ?? '',
    date_of_birth:          props.client.date_of_birth          ?? null,
    gender:                 props.client.gender                 ?? null,
    marital_status:         props.client.marital_status         ?? null,
    nationality:            props.client.nationality            ?? 'Bangladeshi',

    // Step 2 — Contact Details
    phone:                  props.client.phone                  ?? '',
    alternate_phone:        props.client.alternate_phone        ?? '',
    email:                  props.client.email,
    whatsapp:               props.client.whatsapp               ?? '',
    preferred_contact:      props.client.preferred_contact      ?? null,
    emergency_contact_name: props.client.emergency_contact_name ?? '',
    emergency_contact_phone:props.client.emergency_contact_phone?? '',

    // Step 3 — National ID / Passport
    id_type:                props.client.id_type                ?? null,
    nid:                    props.client.nid                    ?? '',
    passport_no:            props.client.passport_no            ?? '',
    passport_expiry:        props.client.passport_expiry        ?? null,
    issuing_country:        props.client.issuing_country        ?? null,
    tin:                    props.client.tin                    ?? '',

    // Step 4 — Address
    address:                props.client.address                ?? '',
    city:                   props.client.city                   ?? null,
    area:                   props.client.area                   ?? '',
    postal_code:            props.client.postal_code            ?? '',
    country:                props.client.country                ?? null,
    permanent_address:      props.client.permanent_address      ?? '',

    // Step 5 — Employment
    occupation:             props.client.occupation             ?? '',
    employment_type:        props.client.employment_type        ?? null,
    company:                props.client.company                ?? '',
    designation:            props.client.designation            ?? '',
    industry:               props.client.industry               ?? null,
    office_address:         props.client.office_address         ?? '',
    tenure:                 props.client.tenure                 ?? '',

    // Step 6 — Income
    monthly_income:         props.client.monthly_income         ?? '',
    other_income:           props.client.other_income           ?? '',
    annual_income:          props.client.annual_income          ?? '',
    existing_loans:         props.client.existing_loans         ?? '',
    primary_bank:           props.client.primary_bank           ?? '',

    // Step 7 — Co-applicant
    coapplicant_name:                 props.client.coapplicant_name                 ?? '',
    coapplicant_relationship:         props.client.coapplicant_relationship         ?? '',
    coapplicant_dob:                  props.client.coapplicant_dob                  ?? null,
    coapplicant_phone:                props.client.coapplicant_phone                ?? '',
    coapplicant_email:                props.client.coapplicant_email                ?? '',
    coapplicant_nid:                  props.client.coapplicant_nid                  ?? '',
    coapplicant_occupation:           props.client.coapplicant_occupation           ?? '',
    coapplicant_monthly_income:       props.client.coapplicant_monthly_income       ?? '',
    coapplicant_annual_income:        props.client.coapplicant_annual_income        ?? '',
    coapplicant_tin:                  props.client.coapplicant_tin                  ?? '',
    coapplicant_ownership_percentage: props.client.coapplicant_ownership_percentage ?? '',
    coapplicant_address:              props.client.coapplicant_address              ?? '',
    coapplicant_signature:            props.client.coapplicant_signature            ?? '',


    // Step 9 — Account & Notes
    password:               '',
    source:                 props.client.source                 ?? null,
    status:                 props.client.status,
    tags:                   props.client.tags                   ?? '',
    notes:                  props.client.notes                  ?? '',

    // Step 10 — Photo & KYC
    avatar:                 null,
    remove_avatar:          false,
    new_kyc_documents:      [],
    remove_kyc_documents:   [],
});

function submit() {
    form.put(route('admin.clients.update', props.client.id));
}
</script>

<template>
    <Head :title="`Edit — ${client.name}`" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.clients.index')" class="hover:text-admin-accent transition-colors">Clients</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <Link :href="route('admin.clients.show', client.id)" class="hover:text-admin-accent transition-colors">{{ client.name }}</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">Edit</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">Edit Client</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Update details for {{ client.name }}</p>
        </div>

        <ClientForm
            :form="form"
            :enums="enums"
            mode="edit"
            :current-avatar="client.avatar"
            :current-kyc="client.kyc_documents"
            @submit="submit"
        />
    </AdminLayout>
</template>
