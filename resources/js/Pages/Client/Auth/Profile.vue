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
        <div class="mx-auto max-w-5xl space-y-8 p-6">
            <!-- Success Message -->
            <div v-if="$page.props.flash?.success"
                class="animate-in slide-in-from-top-2 rounded-2xl border border-green-200 bg-green-50/80 p-4 text-sm font-medium text-green-700 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $page.props.flash.success }}
                </div>
            </div>

            <!-- Profile Header Card -->
            <Card class="overflow-hidden rounded-3xl border-0 shadow-lg">
                <div class="relative">
                    <!-- Gradient Background -->
                    <div class="absolute inset-0 bg-gradient-to-r from-violet-600 to-indigo-600 opacity-10" />

                    <div class="relative flex flex-col gap-8 p-8 md:flex-row md:items-center">
                        <!-- Avatar with gradient border -->
                        <div class="relative">
                            <div
                                class="absolute inset-0 rounded-full bg-gradient-to-r from-violet-600 to-indigo-600 blur-sm" />
                            <div
                                class="relative flex h-24 w-24 items-center justify-center rounded-full bg-white text-3xl font-bold shadow-inner">
                                <span
                                    class="bg-gradient-to-r from-violet-600 to-indigo-600 bg-clip-text text-transparent">
                                    {{ initials }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1 space-y-2">
                            <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                                {{ user?.name }}
                            </h2>
                            <p class="text-lg text-gray-500">
                                {{ user?.email }}
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <Badge
                                    class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white border-0 px-3 py-1">
                                    Client
                                </Badge>
                                <Badge variant="outline" class="border-violet-200 text-violet-700 px-3 py-1">
                                    Active
                                </Badge>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <Dialog v-model:open="editDialogOpen">
                                <DialogTrigger as-child>
                                    <Button
                                        class="bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-lg shadow-violet-500/25 transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/30 hover:-translate-y-0.5 rounded-xl">
                                        Edit Profile
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[425px] rounded-2xl">
                                    <DialogHeader>
                                        <DialogTitle class="text-2xl font-bold">Edit Profile</DialogTitle>
                                        <DialogDescription class="text-gray-500">
                                            Update your personal information.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="submitEdit" class="space-y-5">
                                        <div class="space-y-2">
                                            <Label for="name" class="text-sm font-medium">Full Name</Label>
                                            <Input id="name" v-model="editForm.name"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.name }"
                                                class="rounded-xl" />
                                            <p v-if="editForm.errors.name" class="text-sm text-red-500">
                                                {{ editForm.errors.name }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="email" class="text-sm font-medium">Email Address</Label>
                                            <Input id="email" type="email" v-model="editForm.email"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.email }"
                                                class="rounded-xl" />
                                            <p v-if="editForm.errors.email" class="text-sm text-red-500">
                                                {{ editForm.errors.email }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="phone" class="text-sm font-medium">Phone</Label>
                                            <Input id="phone" type="tel" v-model="editForm.phone"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.phone }"
                                                class="rounded-xl" />
                                            <p v-if="editForm.errors.phone" class="text-sm text-red-500">
                                                {{ editForm.errors.phone }}
                                            </p>
                                        </div>

                                        <div class="flex justify-end gap-3 pt-2">
                                            <Button type="button" variant="outline" @click="editDialogOpen = false"
                                                class="rounded-xl">
                                                Cancel
                                            </Button>
                                            <Button type="submit" :disabled="editForm.processing"
                                                class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-lg shadow-violet-500/25">
                                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                                            </Button>
                                        </div>
                                    </form>
                                </DialogContent>
                            </Dialog>

                            <Dialog v-model:open="passwordDialogOpen">
                                <DialogTrigger as-child>
                                    <Button variant="outline"
                                        class="rounded-xl border-2 border-violet-200 text-violet-700 hover:bg-violet-50 hover:border-violet-300 transition-all duration-300">
                                        Change Password
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[425px] rounded-2xl">
                                    <DialogHeader>
                                        <DialogTitle class="text-2xl font-bold">Change Password</DialogTitle>
                                        <DialogDescription class="text-gray-500">
                                            Enter your current password and a new password.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="submitPassword" class="space-y-5">
                                        <div class="space-y-2">
                                            <Label for="current_password" class="text-sm font-medium">Current
                                                Password</Label>
                                            <Input id="current_password" type="password"
                                                v-model="passwordForm.current_password"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': passwordForm.errors.current_password }"
                                                class="rounded-xl" />
                                            <p v-if="passwordForm.errors.current_password" class="text-sm text-red-500">
                                                {{ passwordForm.errors.current_password }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="password" class="text-sm font-medium">New Password</Label>
                                            <Input id="password" type="password" v-model="passwordForm.password"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': passwordForm.errors.password }"
                                                class="rounded-xl" />
                                            <p v-if="passwordForm.errors.password" class="text-sm text-red-500">
                                                {{ passwordForm.errors.password }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="password_confirmation" class="text-sm font-medium">Confirm New
                                                Password</Label>
                                            <Input id="password_confirmation" type="password"
                                                v-model="passwordForm.password_confirmation"
                                                :class="{ 'border-red-500 focus-visible:ring-red-500': passwordForm.errors.password_confirmation }"
                                                class="rounded-xl" />
                                            <p v-if="passwordForm.errors.password_confirmation"
                                                class="text-sm text-red-500">
                                                {{ passwordForm.errors.password_confirmation }}
                                            </p>
                                        </div>

                                        <div class="flex justify-end gap-3 pt-2">
                                            <Button type="button" variant="outline" @click="passwordDialogOpen = false"
                                                class="rounded-xl">
                                                Cancel
                                            </Button>
                                            <Button type="submit" :disabled="passwordForm.processing"
                                                class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white shadow-lg shadow-violet-500/25">
                                                {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
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
            <Card class="rounded-3xl border-0 shadow-lg hover:shadow-xl transition-shadow duration-300">
                <CardHeader class="pb-4">
                    <CardTitle class="text-xl font-bold">Personal Information</CardTitle>
                    <CardDescription class="text-gray-500">
                        Your account information.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-8 md:grid-cols-2">
                        <div class="space-y-1.5 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-5">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                                Full Name
                            </p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ user?.name }}
                            </p>
                        </div>

                        <div class="space-y-1.5 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-5">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                                Email Address
                            </p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ user?.email }}
                            </p>
                        </div>

                        <div class="space-y-1.5 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-5">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                                Phone
                            </p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ user?.phone ?? 'Not provided' }}
                            </p>
                        </div>

                        <div class="space-y-1.5 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-5">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                                Member Since
                            </p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ formatDate(user?.created_at) }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Security -->
            <Card class="rounded-3xl border-0 shadow-lg hover:shadow-xl transition-shadow duration-300">
                <CardHeader class="pb-4">
                    <CardTitle class="text-xl font-bold">Security</CardTitle>
                    <CardDescription class="text-gray-500">
                        Manage your account security.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        class="flex items-center justify-between rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-6">
                        <div class="space-y-1">
                            <h4 class="text-lg font-semibold text-gray-900">
                                Password
                            </h4>
                            <p class="text-sm text-gray-500">
                                Change your password regularly to keep your account secure.
                            </p>
                        </div>

                        <Dialog v-model:open="passwordDialogOpen">
                            <DialogTrigger as-child>
                                <Button variant="outline"
                                    class="rounded-xl border-2 border-violet-200 text-violet-700 hover:bg-violet-50 hover:border-violet-300 transition-all duration-300">
                                    Change Password
                                </Button>
                            </DialogTrigger>
                        </Dialog>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ClientLayout>
</template>
