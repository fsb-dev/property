<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import DatePicker from "@/Components/ui/date-picker/DatePicker.vue";
import ImageUpload from "@/Components/ui/media/ImageUpload.vue";
import DocumentList from "@/Components/ui/media/DocumentList.vue";

const props = defineProps({
    form: { type: Object, required: true },
    enums: { type: Object, required: true },
    mode: { type: String, default: "create" },
    currentAvatar: { type: String, default: null },
    currentKyc: { type: Array, default: () => [] },
});

const emit = defineEmits(["submit"]);

// ── Steps ──────────────────────────────────────────────────────────────
const STEPS = [
    { key: "personal",   label: "Personal Information",  kicker: "Step 1 of 9" },
    { key: "contact",    label: "Contact Details",        kicker: "Step 2 of 9" },
    { key: "id_passport",label: "National ID / Passport", kicker: "Step 3 of 9" },
    { key: "address",    label: "Address",                kicker: "Step 4 of 9" },
    { key: "employment", label: "Employment",             kicker: "Step 5 of 9" },
    { key: "income",     label: "Income & Financials",    kicker: "Step 6 of 9" },
    { key: "family",     label: "Family & Co-applicant",  kicker: "Step 7 of 9" },
    { key: "documents",  label: "Documents",              kicker: "Step 8 of 9" },   
    { key: "photo_kyc",  label: "Profile Photo",  kicker: "Step 9 of 9" },
];

const marital_statuses = [
    { value: "single", label: "Single" },
    { value: "married", label: "Married" },
];

const activeStep = ref(0);

const currentStep = computed(() => STEPS[activeStep.value]);
const progress = computed(() =>
    Math.round(((activeStep.value + 1) / STEPS.length) * 100),
);
const isFirst = computed(() => activeStep.value === 0);
const isLast = computed(() => activeStep.value === STEPS.length - 1);

function goTo(idx) {
    activeStep.value = idx;
}
function goNext() {
    if (!isLast.value) activeStep.value++;
}
function goBack() {
    if (!isFirst.value) activeStep.value--;
}

function stepState(idx) {
    if (idx === activeStep.value) return "active";
    if (idx < activeStep.value) return "done";
    return "pending";
}

// ── Field style ────────────────────────────────────────────────────────
const f =
    "rounded-lg bg-slate-50 dark:bg-white/[0.04] border-slate-200 dark:border-white/[0.09] focus-visible:ring-1";
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <div
            class="grid items-start gap-7"
            style="grid-template-columns: 280px minmax(0, 1fr)"
        >
            <!-- ══ LEFT RAIL — Step navigation ═══════════════════════════════ -->
            <div
                class="sticky top-6 overflow-hidden rounded-xl border border-border bg-admin-surface-card p-2"
            >
                <nav class="flex flex-col gap-0.5">
                    <button
                        v-for="(step, idx) in STEPS"
                        :key="step.key"
                        type="button"
                        @click="goTo(idx)"
                        :class="[
                            'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors',
                            stepState(idx) === 'active'
                                ? 'bg-[#F1ECFF] dark:bg-admin-accent/10'
                                : 'hover:bg-slate-50 dark:hover:bg-white/[0.03]',
                        ]"
                    >
                        <!-- Circle -->
                        <div
                            :class="[
                                'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-colors',
                                stepState(idx) === 'active'
                                    ? 'bg-admin-accent text-white'
                                    : stepState(idx) === 'done'
                                      ? 'bg-green-500 text-white'
                                      : 'bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400',
                            ]"
                        >
                            <svg
                                v-if="stepState(idx) === 'done'"
                                width="11"
                                height="11"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <!-- Labels -->
                        <div class="min-w-0">
                            <p
                                :class="[
                                    'truncate text-sm font-semibold',
                                    stepState(idx) === 'active'
                                        ? 'text-admin-accent'
                                        : 'text-foreground',
                                ]"
                            >
                                {{ step.label }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ step.subtitle }}
                            </p>
                        </div>
                    </button>
                </nav>

                <!-- Cancel link at bottom -->
                <div class="mt-2 border-t border-border pt-2">
                    <Link
                        :href="route('admin.clients.index')"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-slate-50 hover:text-foreground dark:hover:bg-white/[0.03]"
                    >
                        <svg
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M19 12H5" />
                            <polyline points="12 19 5 12 12 5" />
                        </svg>
                        Back to Clients
                    </Link>
                </div>
            </div>

            <!-- ══ RIGHT CARD — Content ═══════════════════════════════════════ -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-admin-surface-card"
            >
                <!-- Step Header -->
                <div class="border-b border-border px-6 py-5">
                    <p
                        class="mb-0.5 text-xs font-semibold uppercase tracking-widest text-admin-accent"
                    >
                        {{ currentStep.kicker }}
                    </p>
                    <h2 class="text-lg font-bold text-foreground">
                        {{ currentStep.label }}
                    </h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        {{ currentStep.subtitle }}
                    </p>
                    <!-- Progress bar -->
                    <div
                        class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"
                    >
                        <div
                            class="h-full rounded-full bg-admin-accent transition-all duration-500"
                            :style="{ width: progress + '%' }"
                        />
                    </div>
                </div>

                <!-- ── Step 1: Personal Information ─────────────────────────── -->
                <div
                    v-show="activeStep === 0"
                    class="grid grid-cols-2 gap-5 p-6"
                >
                    <!-- Full Name -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            Full Name <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            v-model="form.name"
                            placeholder="e.g. Rahim Uddin"
                            :class="[
                                f,
                                form.errors.name && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Father's Name -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Father's Name</Label
                        >
                        <Input
                            v-model="form.father_name"
                            placeholder="Full name"
                            :class="[
                                f,
                                form.errors.father_name && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.father_name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.father_name }}
                        </p>
                    </div>

                    <!-- Mother's Name -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Mother's Name</Label
                        >
                        <Input
                            v-model="form.mother_name"
                            placeholder="Full name"
                            :class="[
                                f,
                                form.errors.mother_name && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.mother_name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.mother_name }}
                        </p>
                    </div>

                    <!-- Date of Birth -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Date of Birth</Label
                        >
                        <DatePicker
                            :model-value="form.date_of_birth"
                            @update:model-value="form.date_of_birth = $event"
                            placeholder="Pick date of birth"
                            :class="[
                                f,
                                form.errors.date_of_birth &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.date_of_birth"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.date_of_birth }}
                        </p>
                    </div>

                    <!-- Gender -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Gender</Label
                        >
                        <Select
                            :model-value="form.gender || undefined"
                            @update:model-value="form.gender = $event ?? null"
                        >
                            <SelectTrigger
                                :class="[
                                    f,
                                    form.errors.gender && 'border-destructive',
                                ]"
                            >
                                <SelectValue placeholder="Select gender" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="g in enums.genders"
                                    :key="g.value"
                                    :value="g.value"
                                >
                                    {{ g.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.gender"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.gender }}
                        </p>
                    </div>

                    <!-- Marital Status -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Marital Status</Label
                        >
                        <Select
                            :model-value="form.marital_status || undefined"
                            @update:model-value="
                                form.marital_status = $event ?? null
                            "
                        >
                            <SelectTrigger
                                :class="[
                                    f,
                                    form.errors.marital_status &&
                                        'border-destructive',
                                ]"
                            >
                                <SelectValue
                                    placeholder="Select marital status"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="s in marital_statuses"
                                    :key="s.value"
                                    :value="s.value"
                                >
                                    {{ s.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.marital_status"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.marital_status }}
                        </p>
                    </div>

                    <!-- Nationality -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Nationality</Label
                        >
                        <Input
                            v-model="form.nationality"
                            placeholder="e.g. Bangladeshi"
                            :class="[
                                f,
                                form.errors.nationality && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.nationality"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.nationality }}
                        </p>
                    </div>
                </div>

                <!-- ── Step 2: Contact Details ──────────────────────────────── -->
                <div
                    v-show="activeStep === 1"
                    class="grid grid-cols-2 gap-5 p-6"
                >
                    <!-- Mobile -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            Mobile Number
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            v-model="form.phone"
                            placeholder="+880 1712-345678"
                            :class="[
                                f,
                                form.errors.phone && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- Alternate Phone -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Alternate Phone</Label
                        >
                        <Input
                            v-model="form.alternate_phone"
                            placeholder="+880 ..."
                            :class="[
                                f,
                                form.errors.alternate_phone &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.alternate_phone"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.alternate_phone }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            Email <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            v-model="form.email"
                            type="email"
                            placeholder="name@email.com"
                            :class="[
                                f,
                                form.errors.email && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.email"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- WhatsApp -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >WhatsApp Number</Label
                        >
                        <Input
                            v-model="form.whatsapp"
                            placeholder="+880 ..."
                            :class="[
                                f,
                                form.errors.whatsapp && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.whatsapp"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.whatsapp }}
                        </p>
                    </div>

                    

                    <!-- Divider/Heading inside Step 2 -->
                    <div class="col-span-2 border-t border-border pt-4 mt-2">
                        <h3 class="text-sm font-semibold text-foreground">
                            Emergency Contact
                        </h3>
                    </div>

                    <!-- Emergency Contact Name -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Emergency Contact Name</Label
                        >
                        <Input
                            v-model="form.emergency_contact_name"
                            placeholder="Full name"
                            :class="[
                                f,
                                form.errors.emergency_contact_name &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.emergency_contact_name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.emergency_contact_name }}
                        </p>
                    </div>

                    <!-- Emergency Contact Phone -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Emergency Contact Phone</Label
                        >
                        <Input
                            v-model="form.emergency_contact_phone"
                            placeholder="+880 ..."
                            :class="[
                                f,
                                form.errors.emergency_contact_phone &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.emergency_contact_phone"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.emergency_contact_phone }}
                        </p>
                    </div>
                </div>

                <!-- ── Step 3: National ID / Passport ───────────────────────── -->
                <div
                    v-show="activeStep === 2"
                    class="grid grid-cols-2 gap-5 p-6"
                >
                    

                    <!-- NID -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >NID Number / ID Number</Label
                        >
                        <Input
                            v-model="form.nid"
                            placeholder="e.g. 1234567890"
                            :class="[
                                f,
                                form.errors.nid && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.nid"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.nid }}
                        </p>
                    </div>

                    <!-- Birth Certificate -->

                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Birth Certificate Number</Label
                        >
                        <Input
                            v-model="form.birth_certificate_number"
                            placeholder="e.g. 1234567890"
                            :class="[
                                f,
                                form.errors.birth_certificate_number && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.birth_certificate_number"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.birth_certificate_number }}
                        </p>
                    </div>

                    <!-- Passport No -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Passport Number</Label
                        >
                        <Input
                            v-model="form.passport_no"
                            placeholder="e.g. BX0123456"
                            :class="[
                                f,
                                form.errors.passport_no && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.passport_no"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.passport_no }}
                        </p>
                    </div>

                    <!-- Passport Expiry -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Passport Expiry</Label
                        >
                        <DatePicker
                            :model-value="form.passport_expiry"
                            @update:model-value="form.passport_expiry = $event"
                            placeholder="Pick expiry date"
                            :class="[
                                f,
                                form.errors.passport_expiry &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.passport_expiry"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.passport_expiry }}
                        </p>
                    </div>

                  

                    
                </div>

                <!-- ── Step 4: Address ──────────────────────────────────────── -->
                <div
                    v-show="activeStep === 3"
                    class="grid grid-cols-2 gap-5 p-6"
                >
                 

                    <!-- Country -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Country</Label
                        >
                        <Select
                            :model-value="form.country || undefined"
                            @update:model-value="form.country = $event ?? null"
                        >
                            <SelectTrigger
                                :class="[
                                    f,
                                    form.errors.country && 'border-destructive',
                                ]"
                            >
                                <SelectValue placeholder="Select country" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="c in enums.countries"
                                    :key="c.value"
                                    :value="c.value"
                                >
                                    {{ c.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.country"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.country }}
                        </p>
                    </div>

                    <!-- City -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >City</Label
                        >
                        <Input
                            v-model="form.city"
                            placeholder="e.g. Dhaka"
                            :class="[
                                f,
                                form.errors.city && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.city"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.city }}
                        </p>
                    </div>

                    <!-- State -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >State</Label
                        >
                        <Input
                            v-model="form.state"
                            placeholder="e.g. Dhaka Division"
                            :class="[
                                f,
                                form.errors.state && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.state"                            
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.state }}
                        </p>
                    </div>

                    <!-- Postal Code -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Postal Code / Zip Code</Label
                        >
                        <Input
                            v-model="form.postal_code"
                            placeholder="e.g. 1212"
                            :class="[
                                f,
                                form.errors.postal_code && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.postal_code"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.postal_code }}
                        </p>
                    </div>

                    

                    <!-- Permanent Address -->
                    <div
                        class="col-span-2 space-y-1.5 border-t border-border pt-4 mt-2"
                    >
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Permanent Address</Label
                        >
                        <Textarea
                            v-model="form.permanent_address"
                            rows="2"
                            placeholder="Village / city, district..."
                            :class="[
                                f,
                                'resize-none',
                                form.errors.permanent_address &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.permanent_address"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.permanent_address }}
                        </p>
                    </div>
                </div>

                

                <!-- ── Step 5: Employment ───────────────────────────────────── -->
                <div
                    v-show="activeStep === 4"
                    class="grid grid-cols-2 gap-5 p-6"
                >
                    <!-- Employment Type -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Employment Type</Label
                        >
                        <Select
                            :model-value="form.employment_type || undefined"
                            @update:model-value="
                                form.employment_type = $event ?? null
                            "
                        >
                            <SelectTrigger
                                :class="[
                                    f,
                                    form.errors.employment_type &&
                                        'border-destructive',
                                ]"
                            >
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="e in enums.employment_types"
                                    :key="e.value"
                                    :value="e.value"
                                >
                                    {{ e.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.employment_type"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.employment_type }}
                        </p>
                    </div>

                    <!-- Occupation -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Occupation / Role</Label
                        >
                        <Input
                            v-model="form.occupation"
                            placeholder="e.g. Business Owner, Engineer"
                            :class="[
                                f,
                                form.errors.occupation && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.occupation"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.occupation }}
                        </p>
                    </div>

                    <!-- Company -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Company / Business Name</Label
                        >
                        <Input
                            v-model="form.company"
                            placeholder="Organization name"
                            :class="[
                                f,
                                form.errors.company && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.company"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.company }}
                        </p>
                    </div>

                    <!-- Designation -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Designation</Label
                        >
                        <Input
                            v-model="form.designation"
                            placeholder="e.g. General Manager"
                            :class="[
                                f,
                                form.errors.designation && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.designation"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.designation }}
                        </p>
                    </div>

                    <!-- Industry -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Industry</Label
                        >
                        <Select
                            :model-value="form.industry || undefined"
                            @update:model-value="form.industry = $event ?? null"
                        >
                            <SelectTrigger
                                :class="[
                                    f,
                                    form.errors.industry &&
                                        'border-destructive',
                                ]"
                            >
                                <SelectValue placeholder="Select industry" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="i in enums.industries"
                                    :key="i.value"
                                    :value="i.value"
                                >
                                    {{ i.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.industry"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.industry }}
                        </p>
                    </div>

                    <!-- Office Address -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Office Address</Label
                        >
                        <Input
                            v-model="form.office_address"
                            placeholder="Office location"
                            :class="[
                                f,
                                form.errors.office_address &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.office_address"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.office_address }}
                        </p>
                    </div>

                    <!-- Tenure -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Years in Current Role</Label
                        >
                        <Input
                            v-model="form.tenure"
                            placeholder="e.g. 6 years"
                            :class="[
                                f,
                                form.errors.tenure && 'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.tenure"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.tenure }}
                        </p>
                    </div>
                </div>

                <!-- ── Step 6: Income & Financials ──────────────────────────── -->
                <div
                    v-show="activeStep === 5"
                    class="grid grid-cols-2 gap-5 p-6"
                >
                    <!-- Monthly Income -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Monthly Income (BDT)</Label
                        >
                        <Input
                            v-model="form.monthly_income"
                            placeholder="e.g. 350,000"
                            :class="[
                                f,
                                form.errors.monthly_income &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.monthly_income"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.monthly_income }}
                        </p>
                    </div>

                    <!-- Annual Income -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Annual Income (BDT)</Label
                        >
                        <Input
                            v-model="form.annual_income"
                            placeholder="e.g. 4,200,000"
                            :class="[
                                f,
                                form.errors.annual_income &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.annual_income"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.annual_income }}
                        </p>
                    </div>

                    <!-- Other Income -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Other Income Sources</Label
                        >
                        <Input
                            v-model="form.other_income"
                            placeholder="Rent, dividends..."
                            :class="[
                                f,
                                form.errors.other_income &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.other_income"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.other_income }}
                        </p>
                    </div>

                    <!-- Existing Loans -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Existing Loans / EMIs (BDT)</Label
                        >
                        <Input
                            v-model="form.existing_loans"
                            placeholder="e.g. 45,000 / month"
                            :class="[
                                f,
                                form.errors.existing_loans &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.existing_loans"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.existing_loans }}
                        </p>
                    </div>

                    <!-- Bank Name -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Bank Name<template v-slot:name>defaultcontent</template></Label
                        >
                        <Input
                            v-model="form.bank_name"
                            placeholder="e.g. BRAC Bank"
                            :class="[
                                f,
                                form.errors.bank_name &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.bank_name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.bank_name }}
                        </p>
                    </div>


                    <!-- Account Number -->
                    <div class="space-y-1.5">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Account Number<template v-slot:name>defaultcontent</template></Label
                        >
                        <Input
                            v-model="form.account_number"
                            placeholder="e.g. 123456789012"
                            :class="[
                                f,
                                form.errors.account_number &&
                                    'border-destructive',
                            ]"
                        />
                        <p
                            v-if="form.errors.account_number"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.account_number }}
                        </p>
                    </div>





                </div>

                <!-- ── Step 7: Family & Co-applicant ────────────────────────── -->
                <div
                    v-show="activeStep === 6"
                    class="grid grid-cols-2 gap-5 p-6"
                >
                    <!-- Full Name -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Full Name</Label>
                        <Input
                            v-model="form.coapplicant_name"
                            placeholder="Full name"
                            :class="[f, form.errors.coapplicant_name && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_name" class="text-xs text-destructive">{{ form.errors.coapplicant_name }}</p>
                    </div>

                    <!-- Relationship to Primary Buyer -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Relationship to Primary Buyer</Label>
                        <Input
                            v-model="form.coapplicant_relationship"
                            placeholder="e.g. Spouse, Parent, Sibling"
                            :class="[f, form.errors.coapplicant_relationship && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_relationship" class="text-xs text-destructive">{{ form.errors.coapplicant_relationship }}</p>
                    </div>

                    <!-- Date of Birth -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Date of Birth</Label>
                        <DatePicker
                            :model-value="form.coapplicant_dob"
                            @update:model-value="form.coapplicant_dob = $event"
                            placeholder="Pick date of birth"
                            :class="[f, form.errors.coapplicant_dob && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_dob" class="text-xs text-destructive">{{ form.errors.coapplicant_dob }}</p>
                    </div>

                    <!-- Mobile Number -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Mobile Number</Label>
                        <Input
                            v-model="form.coapplicant_phone"
                            placeholder="+880 ..."
                            :class="[f, form.errors.coapplicant_phone && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_phone" class="text-xs text-destructive">{{ form.errors.coapplicant_phone }}</p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Email</Label>
                        <Input
                            v-model="form.coapplicant_email"
                            type="email"
                            placeholder="name@email.com"
                            :class="[f, form.errors.coapplicant_email && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_email" class="text-xs text-destructive">{{ form.errors.coapplicant_email }}</p>
                    </div>

                    <!-- NID / Passport Number -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">NID / Passport Number</Label>
                        <Input
                            v-model="form.coapplicant_nid"
                            placeholder="e.g. 1234567890"
                            :class="[f, form.errors.coapplicant_nid && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_nid" class="text-xs text-destructive">{{ form.errors.coapplicant_nid }}</p>
                    </div>

                    <!-- Occupation -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Occupation</Label>
                        <Input
                            v-model="form.coapplicant_occupation"
                            placeholder="e.g. Business Owner, Engineer"
                            :class="[f, form.errors.coapplicant_occupation && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_occupation" class="text-xs text-destructive">{{ form.errors.coapplicant_occupation }}</p>
                    </div>

                    <!-- Monthly Income -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Monthly Income (BDT)</Label>
                        <Input
                            v-model="form.coapplicant_monthly_income"
                            placeholder="e.g. 350,000"
                            :class="[f, form.errors.coapplicant_monthly_income && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_monthly_income" class="text-xs text-destructive">{{ form.errors.coapplicant_monthly_income }}</p>
                    </div>

                    <!-- Annual Income -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Annual Income (BDT)</Label>
                        <Input
                            v-model="form.coapplicant_annual_income"
                            placeholder="e.g. 4,200,000"
                            :class="[f, form.errors.coapplicant_annual_income && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_annual_income" class="text-xs text-destructive">{{ form.errors.coapplicant_annual_income }}</p>
                    </div>

                    <!-- TIN Number -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">TIN Number</Label>
                        <Input
                            v-model="form.coapplicant_tin"
                            placeholder="e.g. 123456789"
                            :class="[f, form.errors.coapplicant_tin && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_tin" class="text-xs text-destructive">{{ form.errors.coapplicant_tin }}</p>
                    </div>

                    <!-- Ownership Percentage -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Ownership Percentage</Label>
                        <Input
                            v-model="form.coapplicant_ownership_percentage"
                            placeholder="e.g. 50%, 30%, 20%"
                            :class="[f, form.errors.coapplicant_ownership_percentage && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_ownership_percentage" class="text-xs text-destructive">{{ form.errors.coapplicant_ownership_percentage }}</p>
                    </div>

                    <!-- Address -->
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Address</Label>
                        <Textarea
                            v-model="form.coapplicant_address"
                            rows="2"
                            placeholder="House, road, area..."
                            :class="[f, 'resize-none', form.errors.coapplicant_address && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_address" class="text-xs text-destructive">{{ form.errors.coapplicant_address }}</p>
                    </div>

                    <!-- Signature -->
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400">Signature</Label>
                        <Input
                            v-model="form.coapplicant_signature"
                            placeholder="Type full name as signature"
                            :class="[f, form.errors.coapplicant_signature && 'border-destructive']"
                        />
                        <p v-if="form.errors.coapplicant_signature" class="text-xs text-destructive">{{ form.errors.coapplicant_signature }}</p>
                    </div>
                </div>

                <!-- ── Step 8: Documents ────────────────────────────────────── -->
                <div v-show="activeStep === 7" class="p-6">
                    <div class="space-y-2">
                        <Label
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Add Documents</Label
                        >
                        <DocumentList
                            :existing="currentKyc"
                            :new-files="form.new_kyc_documents ?? []"
                            @update:new-files="form.new_kyc_documents = $event"
                            :remove-ids="form.remove_kyc_documents ?? []"
                            @update:remove-ids="
                                form.remove_kyc_documents = $event
                            "
                        />
                        <p class="text-xs text-muted-foreground">
                            NID copy, passport, utility bill (JPG, PNG, PDF ·
                            Max 5 MB each)
                        </p>
                        <p
                            v-if="form.errors?.new_kyc_documents"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.new_kyc_documents }}
                        </p>
                    </div>
                </div>
 
              

            

                <!-- ── Step 9: Profile Photo ────────────────────────── -->
                <div v-show="activeStep === 8" class="grid grid-cols-2 gap-5 p-6">
                    <!-- Profile Photo -->
                    <div class="col-span-2 space-y-1.5">
                        <Label class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Profile Photo</Label
                        >
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
                        <p v-if="form.errors?.avatar" class="text-xs text-destructive">
                            {{ form.errors.avatar }}
                        </p>
                    </div>
                </div>

                <!-- ── Footer Navigation ────────────────────────────────────── -->
                <div
                    class="flex items-center justify-between border-t border-border px-6 py-4"
                >
                    <!-- Back Button -->
                    <button
                        type="button"
                        @click="goBack"
                        :disabled="isFirst"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40"
                    >
                        <svg
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M19 12H5" />
                            <polyline points="12 19 5 12 12 5" />
                        </svg>
                        Back
                    </button>

                    <div class="flex items-center gap-3">
                        <!-- Save Draft / Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-transparent px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:opacity-60"
                        >
                            <svg
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"
                                />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            Save Draft
                        </button>

                        <!-- Next (not last step) -->
                        <button
                            v-if="!isLast"
                            type="button"
                            @click="goNext"
                            class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-4 text-sm font-medium text-white transition-colors hover:bg-admin-accent/90"
                        >
                            Next
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M5 12h14" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </button>

                        <!-- Publish (last step) -->
                        <button
                            v-else
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-9 items-center gap-2 rounded-lg bg-admin-accent px-5 text-sm font-semibold text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                        >
                            <svg
                                v-if="form.processing"
                                class="animate-spin"
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"
                                />
                            </svg>
                            <svg
                                v-else
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M22 2L11 13" />
                                <path d="M22 2L15 22l-4-9-9-4 19-7z" />
                            </svg>
                            {{
                                mode === "edit" ? "Save Changes" : "Add Client"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
