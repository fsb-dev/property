<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const quickLogin = () => {
    form.email = 'admin@homeverse.com';
    form.password = 'password';
    submit();
};

const inputClass = (hasError) => [
    'w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border outline-none transition-colors',
    'bg-background text-foreground placeholder:text-muted-foreground',
    hasError ? 'border-destructive' : 'border-input focus:border-gold',
    'focus:ring-2 focus:ring-ring/25',
];
</script>

<template>
    <Head title="Admin Sign In" />

    <!-- Page background -->
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-admin-surface-page">

        <!-- SVG background decoration: grid dots -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-60" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="dots" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="1.5" cy="1.5" r="1.5" class="fill-admin-accent" fill-opacity="0.18" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dots)" />
        </svg>

        <!-- Gold glow blob top-left -->
        <svg class="absolute -top-24 -left-24 opacity-25 pointer-events-none" width="480" height="480" viewBox="0 0 480 480" fill="none">
            <circle cx="240" cy="240" r="240" fill="url(#blobA)" />
            <defs>
                <radialGradient id="blobA" cx="30%" cy="30%">
                    <stop offset="0%" stop-color="#C6A15B" />
                    <stop offset="100%" stop-color="#C6A15B" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- Gold glow blob bottom-right -->
        <svg class="absolute -bottom-20 -right-20 opacity-15 pointer-events-none" width="400" height="400" viewBox="0 0 400 400" fill="none">
            <circle cx="200" cy="200" r="200" fill="url(#blobB)" />
            <defs>
                <radialGradient id="blobB" cx="70%" cy="70%">
                    <stop offset="0%" stop-color="#A5813F" />
                    <stop offset="100%" stop-color="#A5813F" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md mx-4">
            <div class="bg-card rounded-3xl shadow-2xl overflow-hidden border border-border">

                <!-- Card header band -->
                <div class="px-8 pt-8 pb-6 text-center bg-admin-surface-sidebar">

                    <!-- Logo mark -->
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 bg-gold-gradient shadow-[0_8px_24px_rgba(198,161,91,0.35)]">
                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                            <rect x="5" y="9" width="18" height="17" rx="1" fill="rgba(10,12,16,0.14)" stroke="rgba(10,12,16,0.55)" stroke-width="1.3"/>
                            <rect x="3" y="7.5" width="22" height="2" rx="0.9" fill="#0A0C10" fill-opacity="0.85"/>
                            <rect x="7.5" y="13" width="4.5" height="3" rx="0.5" fill="#0A0C10" fill-opacity="0.85"/>
                            <rect x="16" y="13" width="4.5" height="3" rx="0.5" fill="#0A0C10" fill-opacity="0.85"/>
                            <rect x="7.5" y="18" width="4.5" height="3" rx="0.5" fill="#0A0C10" fill-opacity="0.35"/>
                            <rect x="16" y="18" width="4.5" height="3" rx="0.5" fill="#0A0C10" fill-opacity="0.85"/>
                            <rect x="12" y="22" width="4" height="4" rx="0.6" fill="#0A0C10" fill-opacity="0.75"/>
                        </svg>
                    </div>

                    <div class="text-white text-[22px] font-extrabold tracking-tight">Home Verse</div>
                    <div class="text-white/40 text-[10px] font-bold tracking-[3px] mt-0.5">ADMIN PANEL</div>

                    <!-- Admin tagline -->
                    <div class="mt-4 mb-1">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <div class="h-px w-8 bg-gradient-to-r from-transparent to-gold"></div>
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="text-gold">
                                <rect x="2" y="5" width="10" height="8" rx="0.6" stroke="currentColor" stroke-width="1.2"/>
                                <rect x="1" y="4.2" width="12" height="1.4" rx="0.5" fill="currentColor"/>
                                <rect x="3.5" y="7" width="2.5" height="1.8" rx="0.3" fill="currentColor"/>
                                <rect x="8" y="7" width="2.5" height="1.8" rx="0.3" fill="currentColor"/>
                                <rect x="5.5" y="9.5" width="3" height="3.5" rx="0.4" fill="currentColor"/>
                            </svg>
                            <div class="h-px w-8 bg-gradient-to-l from-transparent to-gold"></div>
                        </div>
                        <p class="text-gold-bright text-[13px] font-bold tracking-[0.06em] uppercase">Where Properties Meet Precision</p>
                        <p class="mt-1 text-white/35 text-[11px] tracking-[0.04em]">Manage · Grow · Deliver</p>
                    </div>
                </div>

                <!-- Form body -->
                <div class="px-8 py-7">

                    <!-- Status -->
                    <div v-if="status"
                        class="mb-5 flex items-center gap-2.5 rounded-xl px-4 py-3 text-sm bg-success/10 border border-success/25 text-success">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5" />
                        </svg>
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-1.5 text-foreground">Email Address</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-muted-foreground">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                        <path d="M2 7l10 7 10-7" />
                                    </svg>
                                </span>
                                <input
                                    id="email" type="email" v-model="form.email"
                                    required autofocus autocomplete="username"
                                    placeholder="admin@example.com"
                                    :class="inputClass(!!form.errors.email)"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-sm font-semibold text-foreground">Password</label>
                                <Link v-if="canResetPassword" :href="route('password.request')"
                                    class="text-xs font-semibold text-brand transition-colors hover:text-gold">
                                    Forgot password?
                                </Link>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-muted-foreground">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </span>
                                <input
                                    id="password" type="password" v-model="form.password"
                                    required autocomplete="current-password"
                                    placeholder="••••••••"
                                    :class="inputClass(!!form.errors.password)"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password" />
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2.5 pt-1">
                            <Checkbox id="remember" name="remember" v-model:checked="form.remember" />
                            <label for="remember" class="text-sm cursor-pointer select-none text-muted-foreground">Keep me signed in</label>
                        </div>

                        <!-- Submit -->
                        <div class="flex items-center gap-2 mt-1">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-on-gold bg-gold-gradient shadow-[0_8px_24px_rgba(198,161,91,0.30)] transition-all duration-200 hover:shadow-gold-glow disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <svg v-if="form.processing" class="animate-spin" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="rgba(10,12,16,0.3)" stroke-width="4" />
                                    <path d="M4 12a8 8 0 018-8" stroke="#0A0C10" stroke-width="4"
                                        stroke-linecap="round" />
                                </svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                    <polyline points="10 17 15 12 10 7" />
                                    <line x1="15" y1="12" x2="3" y2="12" />
                                </svg>
                                {{ form.processing ? 'Signing in…' : 'Sign In to Admin Panel' }}
                            </button>

                            <!-- Demo quick login -->
                            <button
                                type="button"
                                :disabled="form.processing"
                                title="Quick demo login"
                                class="flex-none flex items-center justify-center px-4 h-11 rounded-xl border border-input bg-secondary text-brand text-sm font-semibold whitespace-nowrap transition-colors hover:bg-secondary/70"
                                @click="quickLogin"
                            >
                                Quick Login
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Card footer -->
                <div class="px-8 pb-6 text-center">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-1 h-px bg-border"></div>
                        <span class="text-xs font-medium text-muted-foreground">Secured Access</span>
                        <div class="flex-1 h-px bg-border"></div>
                    </div>
                    <div class="flex items-center justify-center gap-4 text-xs text-muted-foreground">
                        <span class="flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            SSL Encrypted
                        </span>
                        <span class="flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                            Admin Only
                        </span>
                        <span class="flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4l3 3" />
                            </svg>
                            24/7 Access
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
