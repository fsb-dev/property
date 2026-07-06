<script setup>
import { ref, computed } from 'vue'
import { Head, usePage, useForm } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'

const page = usePage()

const user = computed(() => page.props.user)
const props = defineProps({
    user: Object,
})

const initials = computed(() => {
    if (!props.user) return ''

    return props.user.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase()
})

// Edit Profile Dialog
const editDialogOpen = ref(false)

const editForm = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone: props.user?.phone || '',
})

const submitEdit = () => {
    editForm.put(route('client.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            editDialogOpen.value = false
        },
    })
}

// Change Password Dialog
const passwordDialogOpen = ref(false)

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submitPassword = () => {
    passwordForm.put(route('client.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordDialogOpen.value = false
            passwordForm.reset()
        },
    })
}

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<template>

    <Head title="My Profile" />
    <ClientLayout title="My Profile">
        <div class="mx-auto max-w-4xl space-y-5 p-4 sm:p-6">
            <!-- Success Message -->
            <div v-if="$page.props.flash?.success"
                class="rounded-xl border border-green-200 bg-green-50/80 px-4 py-2.5 text-sm font-medium text-green-700 shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $page.props.flash.success }}
                </div>
            </div>

            <!-- Profile Header Card -->
            <Card class="overflow-hidden rounded-2xl border-0 shadow-md">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-violet-600 to-indigo-600 opacity-10" />
                    <div class="relative flex flex-col gap-5 p-5 sm:flex-row sm:items-center">
                        <!-- Avatar -->
                        <div class="relative shrink-0">
                            <div
                                class="absolute inset-0 rounded-full bg-gradient-to-r from-violet-600 to-indigo-600 blur-[3px]" />
                            <div
                                class="relative flex h-16 w-16 items-center justify-center rounded-full bg-white text-xl font-bold shadow-inner">
                                <span
                                    class="bg-gradient-to-r from-violet-600 to-indigo-600 bg-clip-text text-transparent">
                                    {{ initials }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-bold text-gray-900 truncate">
                                {{ user?.name }}
                            </h2>
                            <p class="text-sm text-gray-500 truncate">
                                {{ user?.email }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <Badge
                                    class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white border-0 px-2 py-0.5 text-xs">
                                    Client
                                </Badge>
                                <Badge variant="outline" class="border-violet-200 text-violet-700 px-2 py-0.5 text-xs">
                                    Active
                                </Badge>
                            </div>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <Dialog v-model:open="editDialogOpen">
                                <DialogTrigger as-child>
                                    <Button size="sm"
                                        class="bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-md shadow-violet-500/20 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 rounded-lg text-sm h-9">
                                        Edit Profile
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[400px] rounded-xl">
                                    <DialogHeader>
                                        <DialogTitle class="text-lg font-bold">Edit Profile</DialogTitle>
                                        <DialogDescription class="text-sm text-gray-500">
                                            Update your personal information.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="submitEdit" class="space-y-4">
                                        <div class="space-y-1.5">
                                            <Label for="name" class="text-xs font-medium">Full Name</Label>
                                            <Input id="name" v-model="editForm.name"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.name }"
                                                class="rounded-lg h-9 text-sm" />
                                            <p v-if="editForm.errors.name" class="text-xs text-red-500">
                                                {{ editForm.errors.name }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <Label for="email" class="text-xs font-medium">Email Address</Label>
                                            <Input id="email" type="email" v-model="editForm.email"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.email }"
                                                class="rounded-lg h-9 text-sm" />
                                            <p v-if="editForm.errors.email" class="text-xs text-red-500">
                                                {{ editForm.errors.email }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <Label for="phone" class="text-xs font-medium">Phone</Label>
                                            <Input id="phone" type="tel" v-model="editForm.phone"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.phone }"
                                                class="rounded-lg h-9 text-sm" />
                                            <p v-if="editForm.errors.phone" class="text-xs text-red-500">
                                                {{ editForm.errors.phone }}
                                            </p>
                                        </div>

                                        <div class="flex justify-end gap-2 pt-1">
                                            <Button type="button" variant="outline" size="sm"
                                                @click="editDialogOpen = false" class="rounded-lg text-sm h-9">
                                                Cancel
                                            </Button>
                                            <Button type="submit" size="sm" :disabled="editForm.processing"
                                                class="rounded-lg bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-md shadow-violet-500/20 text-sm h-9">
                                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                                            </Button>
                                        </div>
                                    </form>
                                </DialogContent>
                            </Dialog>

                            <Dialog v-model:open="passwordDialogOpen">
                                <DialogTrigger as-child>
                                    <Button variant="outline" size="sm"
                                        class="rounded-lg border-2 border-violet-200 text-violet-700 hover:bg-violet-50 hover:border-violet-300 transition-all duration-300 text-sm h-9">
                                        Password
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[400px] rounded-xl">
                                    <DialogHeader>
                                        <DialogTitle class="text-lg font-bold">Change Password</DialogTitle>
                                        <DialogDescription class="text-sm text-gray-500">
                                            Enter your current and new password.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="submitPassword" class="space-y-4">
                                        <div class="space-y-1.5">
                                            <Label for="current_password" class="text-xs font-medium">Current
                                                Password</Label>
                                            <Input id="current_password" type="password"
                                                v-model="passwordForm.current_password"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': passwordForm.errors.current_password }"
                                                class="rounded-lg h-9 text-sm" />
                                            <p v-if="passwordForm.errors.current_password" class="text-xs text-red-500">
                                                {{ passwordForm.errors.current_password }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <Label for="password" class="text-xs font-medium">New Password</Label>
                                            <Input id="password" type="password" v-model="passwordForm.password"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': passwordForm.errors.password }"
                                                class="rounded-lg h-9 text-sm" />
                                            <p v-if="passwordForm.errors.password" class="text-xs text-red-500">
                                                {{ passwordForm.errors.password }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <Label for="password_confirmation" class="text-xs font-medium">Confirm
                                                Password</Label>
                                            <Input id="password_confirmation" type="password"
                                                v-model="passwordForm.password_confirmation"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': passwordForm.errors.password_confirmation }"
                                                class="rounded-lg h-9 text-sm" />
                                            <p v-if="passwordForm.errors.password_confirmation"
                                                class="text-xs text-red-500">
                                                {{ passwordForm.errors.password_confirmation }}
                                            </p>
                                        </div>

                                        <div class="flex justify-end gap-2 pt-1">
                                            <Button type="button" variant="outline" size="sm"
                                                @click="passwordDialogOpen = false" class="rounded-lg text-sm h-9">
                                                Cancel
                                            </Button>
                                            <Button type="submit" size="sm" :disabled="passwordForm.processing"
                                                class="rounded-lg bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-md shadow-violet-500/20 text-sm h-9">
                                                {{ passwordForm.processing ? 'Updating...' : 'Update' }}
                                            </Button>
                                        </div>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Personal Information -->
            <Card class="rounded-2xl border-0 shadow-md hover:shadow-lg transition-shadow duration-300">
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-bold">Personal Information</CardTitle>
                    <CardDescription class="text-xs text-gray-500">
                        Your account information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-4">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                Full Name
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ user?.name }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-4">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                Email Address
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5 truncate">
                                {{ user?.email }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-4">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                Phone
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ user?.phone ?? 'Not provided' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-4">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                Member Since
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ formatDate(user?.created_at) }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Security -->
            <Card class="rounded-2xl border-0 shadow-md hover:shadow-lg transition-shadow duration-300">
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-bold">Security</CardTitle>
                    <CardDescription class="text-xs text-gray-500">
                        Manage your account security.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        class="flex items-center justify-between rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">
                                Password
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Keep your account secure with a strong password.
                            </p>
                        </div>
                        <Dialog v-model:open="passwordDialogOpen">
                            <DialogTrigger as-child>
                                <Button variant="outline" size="sm"
                                    class="rounded-lg border-2 border-violet-200 text-violet-700 hover:bg-violet-50 hover:border-violet-300 transition-all duration-300 text-xs h-8">
                                    Change
                                </Button>
                            </DialogTrigger>
                        </Dialog>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ClientLayout>
</template>
