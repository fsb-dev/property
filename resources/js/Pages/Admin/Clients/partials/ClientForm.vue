<script setup>
import { Link } from '@inertiajs/vue3';
import { Input }    from '@/Components/ui/input';
import { Label }    from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import DatePicker   from '@/Components/ui/date-picker/DatePicker.vue';
import ImageUpload  from '@/Components/ui/media/ImageUpload.vue';
import DocumentList from '@/Components/ui/media/DocumentList.vue';

defineProps({
    form:           { type: Object,  required: true },
    enums:          { type: Object,  required: true },
    mode:           { type: String,  default: 'create' },
    currentAvatar:  { type: String,  default: null },
    currentKyc:     { type: Array,   default: () => [] },
});

const emit = defineEmits(['submit']);

const f = 'rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1';
</script>

<template>
    <form @submit.prevent="emit('submit')" class="flex flex-col gap-4">

        <!-- ── Personal Information ───────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Personal Information</p>
                    <p class="text-xs text-muted-foreground">Basic contact details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <!-- Name -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Full Name <span class="text-destructive">*</span>
                    </Label>
                    <Input v-model="form.name" placeholder="e.g. Rahim Uddin" :class="[f, form.errors.name && 'border-destructive']" />
                    <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Email <span class="text-destructive">*</span>
                    </Label>
                    <Input v-model="form.email" type="email" placeholder="e.g. rahim@email.com" :class="[f, form.errors.email && 'border-destructive']" />
                    <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Phone</Label>
                    <Input v-model="form.phone" placeholder="e.g. 01711-000000" :class="[f, form.errors.phone && 'border-destructive']" />
                    <p v-if="form.errors.phone" class="text-xs text-destructive">{{ form.errors.phone }}</p>
                </div>

                <!-- Gender -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Gender</Label>
                    <Select :model-value="form.gender || undefined" @update:model-value="form.gender = $event ?? null">
                        <SelectTrigger :class="[f, form.errors.gender && 'border-destructive']">
                            <SelectValue placeholder="Select gender" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="g in enums.genders" :key="g.value" :value="g.value">
                                {{ g.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.gender" class="text-xs text-destructive">{{ form.errors.gender }}</p>
                </div>

                <!-- Date of Birth -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Date of Birth</Label>
                    <DatePicker
                        :model-value="form.date_of_birth"
                        @update:model-value="form.date_of_birth = $event"
                        placeholder="Pick date of birth"
                        :class="[f, form.errors.date_of_birth && 'border-destructive']"
                    />
                    <p v-if="form.errors.date_of_birth" class="text-xs text-destructive">{{ form.errors.date_of_birth }}</p>
                </div>

                <!-- Occupation -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Occupation</Label>
                    <Input v-model="form.occupation" placeholder="e.g. Business Owner, Engineer" :class="[f, form.errors.occupation && 'border-destructive']" />
                    <p v-if="form.errors.occupation" class="text-xs text-destructive">{{ form.errors.occupation }}</p>
                </div>

            </div>
        </div>

        <!-- ── ID & Address ───────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">ID & Address</p>
                    <p class="text-xs text-muted-foreground">KYC identity and location details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <!-- NID -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">National ID (NID)</Label>
                    <Input v-model="form.nid" placeholder="e.g. 1234567890123" :class="[f, form.errors.nid && 'border-destructive']" />
                    <p v-if="form.errors.nid" class="text-xs text-destructive">{{ form.errors.nid }}</p>
                </div>

                <!-- Passport -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Passport No.</Label>
                    <Input v-model="form.passport_no" placeholder="e.g. AA1234567" :class="[f, form.errors.passport_no && 'border-destructive']" />
                    <p v-if="form.errors.passport_no" class="text-xs text-destructive">{{ form.errors.passport_no }}</p>
                </div>

                <!-- Nationality -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Nationality</Label>
                    <Input v-model="form.nationality" placeholder="e.g. Bangladeshi" :class="[f, form.errors.nationality && 'border-destructive']" />
                    <p v-if="form.errors.nationality" class="text-xs text-destructive">{{ form.errors.nationality }}</p>
                </div>

                <!-- Address (full width) -->
                <div class="space-y-1.5 md:col-span-2">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Address</Label>
                    <Textarea
                        v-model="form.address"
                        rows="2"
                        placeholder="House, Road, Area, City"
                        :class="[f, 'resize-none', form.errors.address && 'border-destructive']"
                    />
                    <p v-if="form.errors.address" class="text-xs text-destructive">{{ form.errors.address }}</p>
                </div>

            </div>
        </div>

        <!-- ── Account Details ────────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Account Details</p>
                    <p class="text-xs text-muted-foreground">Status, source and internal notes</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <!-- Status -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Status <span class="text-destructive">*</span>
                    </Label>
                    <Select :model-value="form.status" @update:model-value="form.status = $event">
                        <SelectTrigger :class="[f, form.errors.status && 'border-destructive']">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in enums.statuses" :key="s.value" :value="s.value">
                                {{ s.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.status" class="text-xs text-destructive">{{ form.errors.status }}</p>
                </div>

                <!-- Source -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Lead Source</Label>
                    <Select :model-value="form.source || undefined" @update:model-value="form.source = $event ?? null">
                        <SelectTrigger :class="[f, form.errors.source && 'border-destructive']">
                            <SelectValue placeholder="How did they find you?" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in enums.sources" :key="s.value" :value="s.value">
                                {{ s.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.source" class="text-xs text-destructive">{{ form.errors.source }}</p>
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Password
                        <span v-if="mode === 'create'" class="text-muted-foreground font-normal">(auto-generated if blank)</span>
                        <span v-else class="text-muted-foreground font-normal">(leave blank to keep current)</span>
                    </Label>
                    <Input v-model="form.password" type="password" placeholder="Min. 8 characters" :class="[f, form.errors.password && 'border-destructive']" />
                    <p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p>
                </div>

                <!-- Notes (full width) -->
                <div class="space-y-1.5 md:col-span-2">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Internal Notes</Label>
                    <Textarea
                        v-model="form.notes"
                        rows="3"
                        placeholder="Any notes visible only to admins..."
                        :class="[f, 'resize-none', form.errors.notes && 'border-destructive']"
                    />
                    <p v-if="form.errors.notes" class="text-xs text-destructive">{{ form.errors.notes }}</p>
                </div>

            </div>
        </div>

        <!-- ── Media & Documents ──────────────────────────────── -->
        <div class="overflow-hidden rounded-xl border border-border bg-admin-surface-card">
            <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-admin-accent/10">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-foreground">Photo & KYC Documents</p>
                    <p class="text-xs text-muted-foreground">Profile photo and identity documents</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                <!-- Avatar -->
                <div class="space-y-2">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Profile Photo</Label>
                    <ImageUpload
                        :file="form.avatar"
                        @update:file="form.avatar = $event"
                        :removed="form.remove_avatar ?? false"
                        @update:removed="form.remove_avatar = $event"
                        :preview="currentAvatar"
                        label="Click to upload photo"
                        hint="JPG, PNG, WEBP · Max 2 MB"
                        accept="image/jpeg,image/png,image/webp"
                    />
                    <p v-if="form.errors?.avatar" class="text-xs text-destructive">{{ form.errors.avatar }}</p>
                </div>

                <!-- KYC Documents -->
                <div class="space-y-2">
                    <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">KYC Documents</Label>
                    <DocumentList
                        :existing="currentKyc"
                        :new-files="form.new_kyc_documents ?? []"
                        @update:new-files="form.new_kyc_documents = $event"
                        :remove-ids="form.remove_kyc_documents ?? []"
                        @update:remove-ids="form.remove_kyc_documents = $event"
                    />
                    <p class="text-xs text-muted-foreground">NID copy, passport, utility bill (JPG, PNG, PDF · Max 5 MB each)</p>
                    <p v-if="form.errors?.new_kyc_documents" class="text-xs text-destructive">{{ form.errors.new_kyc_documents }}</p>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between border-t border-border px-5 py-4">
                <p class="text-xs text-muted-foreground">
                    <span class="text-destructive">*</span> Required fields
                </p>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.clients.index')"
                        class="inline-flex h-9 items-center rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-4 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                        </svg>
                        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        {{ mode === 'edit' ? 'Save Changes' : 'Add Client' }}
                    </button>
                </div>
            </div>

        </div>

    </form>
</template>
