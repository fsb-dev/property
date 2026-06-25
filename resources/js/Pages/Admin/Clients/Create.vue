<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientForm  from './partials/ClientForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    enums: { type: Object, required: true },
});

const form = useForm({
    name:           '',
    email:          '',
    phone:          '',
    password:       '',
    gender:         null,
    date_of_birth:  null,
    nationality:    'Bangladeshi',
    nid:            '',
    passport_no:    '',
    occupation:     '',
    address:        '',
    source:         null,
    notes:          '',
    status:         'active',

    avatar:               null,
    remove_avatar:        false,
    new_kyc_documents:    [],
    remove_kyc_documents: [],
});

function submit() {
    form.post(route('admin.clients.store'));
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
            <p class="mt-0.5 text-sm text-muted-foreground">Register a new client with their contact and identity details.</p>
        </div>

        <ClientForm :form="form" :enums="enums" mode="create" @submit="submit" />
    </AdminLayout>
</template>
