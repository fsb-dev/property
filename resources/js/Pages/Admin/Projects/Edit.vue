<script setup>
import AdminLayout  from '@/Layouts/AdminLayout.vue';
import ProjectForm  from './partials/ProjectForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    project: { type: Object, required: true },
    enums:   { type: Object, required: true },
});

const form = useForm({
    name:          props.project.name,
    type:          props.project.type,
    category:      props.project.category,
    status:        props.project.status,
    location:      props.project.location ?? '',
    address:       props.project.address ?? '',
    description:   props.project.description ?? '',
    total_floors:  props.project.total_floors,
    total_units:   props.project.total_units,
    handover_date: props.project.handover_date,
    latitude:      props.project.latitude,
    longitude:     props.project.longitude,
});

function submit() {
    form.put(route('admin.projects.update', props.project.id));
}
</script>

<template>
    <Head :title="`Edit — ${project.name}`" />

    <AdminLayout>
        <!-- Page header -->
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.projects.index')" class="hover:text-admin-accent transition-colors">Projects</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">Edit</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">{{ project.name }}</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Update the project information below.</p>
        </div>

        <ProjectForm :form="form" :enums="enums" mode="edit" @submit="submit" />
    </AdminLayout>
</template>
