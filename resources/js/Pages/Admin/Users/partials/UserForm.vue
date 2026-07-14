<script setup>
import { Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import ImageUpload from '@/Components/ui/media/ImageUpload.vue';

const props = defineProps({
    form:          { type: Object, required: true },
    enums:         { type: Object, required: true },
    mode:          { type: String, default: 'create' },
    currentAvatar: { type: String, default: null },
});

const emit = defineEmits(['submit']);

const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <div class="grid items-start gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">

            <!-- Avatar -->
            <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card p-5">
                <Label class="mb-2 block text-xs font-medium text-slate-500 dark:text-slate-400">Profile Photo</Label>
                <ImageUpload
                    v-model:file="form.avatar"
                    v-model:removed="form.remove_avatar"
                    :preview="currentAvatar"
                    label="Upload photo"
                    hint="JPG, PNG, WEBP · Max 2 MB"
                />
                <p v-if="form.errors.avatar" class="mt-2 text-xs text-destructive">{{ form.errors.avatar }}</p>
            </div>

            <!-- Fields -->
            <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card p-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Full Name <span class="text-destructive">*</span></Label>
                        <Input v-model="form.name" placeholder="e.g. John Doe" :class="[f, form.errors.name && 'border-destructive']" />
                        <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Email <span class="text-destructive">*</span></Label>
                        <Input v-model="form.email" type="email" placeholder="name@company.com" :class="[f, form.errors.email && 'border-destructive']" />
                        <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Phone</Label>
                        <Input v-model="form.phone" placeholder="+880 1XXX-XXXXXX" :class="[f, form.errors.phone && 'border-destructive']" />
                        <p v-if="form.errors.phone" class="text-xs text-destructive">{{ form.errors.phone }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Department</Label>
                        <Input v-model="form.department" placeholder="e.g. Sales & Marketing" :class="[f, form.errors.department && 'border-destructive']" />
                        <p v-if="form.errors.department" class="text-xs text-destructive">{{ form.errors.department }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Role <span class="text-destructive">*</span></Label>
                        <Select v-model="form.role">
                            <SelectTrigger :class="[f, form.errors.role && 'border-destructive']">
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="r in enums.roles" :key="r.value" :value="r.value">{{ r.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.role" class="text-xs text-destructive">{{ form.errors.role }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Password <span v-if="mode === 'create'" class="text-destructive">*</span>
                            <span v-else class="font-normal text-muted-foreground">(leave blank to keep current)</span>
                        </Label>
                        <Input v-model="form.password" type="password" placeholder="Min. 8 characters" :class="[f, form.errors.password && 'border-destructive']" />
                        <p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Confirm Password <span v-if="mode === 'create'" class="text-destructive">*</span>
                        </Label>
                        <Input v-model="form.password_confirmation" type="password" placeholder="Re-enter password" :class="[f, form.errors.password_confirmation && 'border-destructive']" />
                        <p v-if="form.errors.password_confirmation" class="text-xs text-destructive">{{ form.errors.password_confirmation }}</p>
                    </div>

                </div>

                <!-- Footer -->
                <div class="mt-7 flex items-center justify-between border-t border-border pt-5">
                    <Link :href="route('admin.users.index')" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-admin-accent px-5 py-2 text-sm font-medium text-on-gold transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        {{ mode === 'create' ? 'Create User' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>
