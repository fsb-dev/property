<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UnitForm    from './partials/UnitForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    unit:     { type: Object, required: true },
    enums:    { type: Object, required: true },
    projects: { type: Array,  required: true },
});

const form = useForm({
    project_id:    props.unit.project_id,
    unit_number:   props.unit.unit_number,
    block:         props.unit.block ?? '',
    floor:         props.unit.floor,
    type:          props.unit.type,
    bedrooms:      props.unit.bedrooms,
    size_sqft:     props.unit.size_sqft,
    view:          props.unit.view ?? '',
    price:         props.unit.price,
    status:        props.unit.status,
    handover_date: props.unit.handover_date,
    floor_plan:       null,
    remove_floor_plan:false,
    new_images:       [],
    remove_images:    [],
    new_documents:    [],
    remove_documents: [],
});

function submit() {
    form.transform(data => ({ ...data, _method: 'put' }))
        .post(route('admin.units.update', props.unit.id));
}
</script>

<template>
    <Head :title="`Edit — ${unit.unit_number}`" />

    <AdminLayout>
        <div class="mb-6">
            <nav class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                <Link :href="route('admin.units.index')" class="hover:text-admin-accent transition-colors">Units</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-foreground">Edit</span>
            </nav>
            <h1 class="text-xl font-bold text-foreground">{{ unit.unit_number }}</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Update the unit information below.</p>
        </div>

        <UnitForm
            :form="form"
            :enums="enums"
            :projects="projects"
            mode="edit"
            :current-floor-plan="unit.floor_plan || null"
            :current-images="unit.images ?? []"
            :current-documents="unit.documents ?? []"
            @submit="submit"
        />
    </AdminLayout>
</template>
