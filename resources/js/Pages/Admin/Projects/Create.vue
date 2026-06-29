<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProjectForm from './partials/ProjectForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    enums: { type: Object, required: true },
});

const form = useForm({
    // Step 1 — Identity
    name:          '',
    project_code:  '',
    type:          'residential',
    status:        'draft',
    theme_color:   '#5B3DF5',
    start_date:    null,
    handover_date: null,
    description:   '',

    // Step 2 — Location
    location:  '',
    address:   '',
    latitude:  null,
    longitude: null,

    // Step 3 — Buildings & Sections
    buildings: [],

    // Step 4 — Financials
    developer_id:         null,
    developer_name:       '',
    land_area:            null,
    land_area_unit:       'sqft',
    built_up_area:        null,
    estimated_value:      null,
    booking_amount:       null,
    booking_amount_type:  'fixed',
    commission_pct:       null,
    payment_plan_months:  null,
    service_charge_sqft:  null,
    maintenance_years:    null,

    // Step 5 — Facilities
    facility_ids: [],

    // Step 6 — Compliance
    compliances: [],

    // Step 7 — Media
    cover:            null,
    new_images:       [],
    remove_images:    [],
    new_documents:    [],
    remove_documents: [],
});

function submit() {
    form.post(route('admin.projects.store'));
}
</script>

<template>
    <Head title="New Project" />

    <AdminLayout>
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
