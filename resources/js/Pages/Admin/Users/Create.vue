<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserForm from './partials/UserForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    enums: { type: Object, required: true },
});

const form = useForm({
    name:                  '',
    email:                 '',
    phone:                 '',
    department:            '',
    password:              '',
    password_confirmation: '',
    role:                  null,
    avatar:                null,
    remove_avatar:         false,
});

function submit() {
    form.post(route('admin.users.store'), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="New User" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.users.index')" class="hover:text-admin-accent transition-colors">Users &amp; Roles</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">New User</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">Add New User</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Create a new admin/staff account and assign it a role.</p>
        </div>

        <UserForm
            :form="form"
            :enums="enums"
            mode="create"
            :current-avatar="null"
            @submit="submit"
        />
    </AdminLayout>
</template>
