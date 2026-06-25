<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientForm  from './partials/ClientForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    client: { type: Object, required: true },
    enums:  { type: Object, required: true },
});

const form = useForm({
    name:           props.client.name,
    email:          props.client.email,
    phone:          props.client.phone          ?? '',
    password:       '',
    gender:         props.client.gender         ?? null,
    date_of_birth:  props.client.date_of_birth  ?? null,
    nationality:    props.client.nationality    ?? 'Bangladeshi',
    nid:            props.client.nid            ?? '',
    passport_no:    props.client.passport_no    ?? '',
    occupation:     props.client.occupation     ?? '',
    address:        props.client.address        ?? '',
    source:         props.client.source         ?? null,
    notes:          props.client.notes          ?? '',
    status:         props.client.status,

    avatar:               null,
    remove_avatar:        false,
    new_kyc_documents:    [],
    remove_kyc_documents: [],
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
