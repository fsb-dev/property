<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UnitForm    from './partials/UnitForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    enums:    { type: Object, required: true },
    projects: { type: Array,  required: true },
});

const form = useForm({
    project_id:    null,
    unit_number:   '',
    block:         '',
    floor:         null,
    type:          null,
    bedrooms:      null,
    size_sqft:     null,
    view:          '',
    price:         '',
    status:        'available',
    handover_date: null,
    floor_plan:       null,
    new_images:       [],
    remove_images:    [],
    new_documents:    [],
    remove_documents: [],
});

function submit() {
    form.post(route('admin.units.store'));
}
</script>

<template>
    <Head title="New Unit" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.units.index')" class="hover:text-admin-accent transition-colors">Units</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">New Unit</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">Create New Unit</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Fill in the details below to register a new unit.</p>
        </div>

        <UnitForm :form="form" :enums="enums" :projects="projects" mode="create" @submit="submit" />
    </AdminLayout>
</template>
