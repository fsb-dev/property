<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Input }  from '@/Components/ui/input';
import { Label }  from '@/Components/ui/label';

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('client.login.store'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Client Login" />

    <div class="flex min-h-screen items-center justify-center bg-slate-50 dark:bg-slate-900 p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="mb-8 text-center">
                <div class="mb-2 text-2xl font-extrabold text-admin-accent">HomeVerse</div>
                <p class="text-sm text-muted-foreground">Sign in to your buyer portal</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-border bg-white dark:bg-slate-800 shadow-sm">
                <div class="p-8">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email -->
                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Email</Label>
                            <Input
                                v-model="form.email"
                                type="email"
                                placeholder="your@email.com"  
                                autocomplete="email"
                                required
                            />
                            <p v-if="form.errors.email" class="text-xs text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Password</Label>
                            <Input
                                v-model="form.password"
                                type="password"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                required
                            />
                            <p v-if="form.errors.password" class="text-xs text-destructive">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Remember -->
                        <div class="flex items-center gap-2">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-border text-admin-accent"
                            />
                            <label for="remember" class="text-sm text-muted-foreground">Remember me</label>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-xl bg-admin-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-admin-accent/90 disabled:opacity-60"
                        >
                            {{ form.processing ? 'Signing in…' : 'Sign In' }}
                        </button>
                    </form>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-muted-foreground">
                Admin?
                <a :href="route('login')" class="text-admin-accent hover:underline">Go to admin login</a>
            </p>
        </div>
    </div>
</template>
