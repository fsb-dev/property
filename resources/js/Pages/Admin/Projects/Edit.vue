<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProjectForm from './partials/ProjectForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    project: { type: Object, required: true },
    enums:   { type: Object, required: true },
});

const form = useForm({
    // Step 1 — Identity
    name:          props.project.name          ?? '',
    project_code:  props.project.project_code  ?? '',
    type:          props.project.type          ?? 'residential',
    status:        props.project.status        ?? 'draft',
    theme_color:   props.project.theme_color   ?? '#5B3DF5',
    start_date:    props.project.start_date    ?? null,
    handover_date: props.project.handover_date ?? null,
    description:   props.project.description   ?? '',

    // Step 2 — Location
    location:  props.project.location  ?? '',
    address:   props.project.address   ?? '',
    latitude:  props.project.latitude  ?? null,
    longitude: props.project.longitude ?? null,

    // Step 3 — Buildings & Sections
    buildings: props.project.buildings ?? [],

    // Step 4 — Financials
    developer_id:        props.project.developer_id        ?? null,
    developer_name:      props.project.developer_name      ?? '',
    land_area:           props.project.land_area           ?? null,
    land_area_unit:      'sqft',
    built_up_area:       props.project.built_up_area       ?? null,
    estimated_value:     props.project.estimated_value     ?? null,
    booking_amount:      props.project.booking_amount      ?? null,
    booking_amount_type: props.project.booking_amount_type ?? 'fixed',
    commission_pct:      props.project.commission_pct      ?? null,
    payment_plan_months: props.project.payment_plan_months ?? null,
    service_charge_sqft: props.project.service_charge_sqft ?? null,
    maintenance_years:   props.project.maintenance_years   ?? null,

    // Step 5 — Facilities
    facility_ids: props.project.facility_ids ?? [],

    // Step 6 — Compliance
    compliances: props.project.compliances ?? [],

    // Step 7 — Media
    cover:            null,
    remove_cover:     false,
    new_images:       [],
    remove_images:    [],
    new_documents:    [],
    remove_documents: [],
});

function submit() {
    form.transform(data => ({ ...data, _method: 'put' }))
        .post(route('admin.projects.update', props.project.id));
}
</script>

<template>
    <Head :title="`Edit — ${project.name}`" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.projects.index')" class="hover:text-admin-accent transition-colors">Projects</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">Edit</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">{{ project.name }}</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Update the project information below.</p>
        </div>

        <ProjectForm
            :form="form"
            :enums="enums"
            mode="edit"
            :current-cover="project.cover || null"
            :current-images="project.images ?? []"
            :current-documents="project.documents ?? []"
            @submit="submit"
        />
    </AdminLayout>
</template>
