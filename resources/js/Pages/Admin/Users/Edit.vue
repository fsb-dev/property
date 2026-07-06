<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserForm from './partials/UserForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user:  { type: Object, required: true },
    enums: { type: Object, required: true },
});

const form = useForm({
    name:                  props.user.name,
    email:                 props.user.email,
    phone:                 props.user.phone,
    department:            props.user.department,
    password:              '',
    password_confirmation: '',
    role:                  props.user.role,
    avatar:                null,
    remove_avatar:         false,
});

function submit() {
    form.put(route('admin.users.update', props.user.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Edit User" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.users.index')" class="hover:text-admin-accent transition-colors">Users &amp; Roles</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">Edit User</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">Edit User</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Update {{ user.name }}'s account details and role.</p>
        </div>

        <UserForm
            :form="form"
            :enums="enums"
            mode="edit"
            :current-avatar="user.avatar"
            @submit="submit"
        />
    </AdminLayout>
</template>
