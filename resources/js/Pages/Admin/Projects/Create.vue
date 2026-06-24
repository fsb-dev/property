<script setup>
import AdminLayout  from '@/Layouts/AdminLayout.vue';
import ProjectForm  from './partials/ProjectForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    enums: { type: Object, required: true },
});

const form = useForm({
    name:          '',
    type:          'residential',
    category:      null,
    status:        'planning',
    location:      '',
    address:       '',
    description:   '',
    total_floors:  null,
    total_units:   null,
    handover_date: null,
    latitude:      null,
    longitude:     null,
});

function submit() {
    form.post(route('admin.projects.store'));
}
</script>

<template>
    <Head title="New Project" />

    <AdminLayout>
        <!-- Page header -->
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.projects.index')" class="hover:text-admin-accent transition-colors">Projects</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">New Project</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">Create New Project</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Fill in the details below to register a new project.</p>
        </div>

        <ProjectForm :form="form" :enums="enums" mode="create" @submit="submit" />
    </AdminLayout>
</template>
