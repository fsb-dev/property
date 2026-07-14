<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post(route('client.login.store'), {
        onFinish: () => form.reset('password'),
    });
}

function quickLogin() {
    form.email = 'client@gmail.com';
    form.password = 'password';
    submit();
}

const inputClass = (hasError) => [
    'w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border outline-none transition-colors',
    'bg-background text-foreground placeholder:text-muted-foreground',
    hasError ? 'border-destructive' : 'border-input focus:border-gold',
    'focus:ring-2 focus:ring-ring/25',
];
</script>

<template>
    <Head title="Client Sign In" />

    <!-- Page background — richer, more cinematic than admin -->
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-client-surface-page">

        <!-- Dot grid -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-60" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="dots" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="1.5" cy="1.5" r="1.5" class="fill-client-accent" fill-opacity="0.16" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dots)" />
        </svg>

        <!-- Gold glow blob top-right -->
        <svg class="absolute -top-24 -right-24 opacity-25 pointer-events-none" width="480" height="480" viewBox="0 0 480 480" fill="none">
            <circle cx="240" cy="240" r="240" fill="url(#blobC)" />
            <defs>
                <radialGradient id="blobC" cx="70%" cy="30%">
                    <stop offset="0%" stop-color="#C6A15B" />
                    <stop offset="100%" stop-color="#C6A15B" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- Gold glow blob bottom-left -->
        <svg class="absolute -bottom-20 -left-20 opacity-15 pointer-events-none" width="400" height="400" viewBox="0 0 400 400" fill="none">
            <circle cx="200" cy="200" r="200" fill="url(#blobD)" />
            <defs>
                <radialGradient id="blobD" cx="30%" cy="70%">
                    <stop offset="0%" stop-color="#E7CE8A" />
                    <stop offset="100%" stop-color="#E7CE8A" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md mx-4">
            <div class="bg-card rounded-3xl overflow-hidden border border-border shadow-2xl">

                <!-- Card header — gold top-bar accent -->
                <div class="px-8 pt-8 pb-6 text-center relative border-b border-border">

                    <!-- Gold gradient top-bar -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gold-gradient"></div>

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

                    <div class="text-foreground text-[22px] font-extrabold tracking-tight">Home Verse</div>
                    <div class="text-brand text-[10px] font-bold tracking-[3px] mt-0.5">CLIENT PORTAL</div>

                    <!-- Tagline row -->
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
                        <p class="text-brand text-[13px] font-bold tracking-[0.06em] uppercase">Your Property Journey Starts Here</p>
                        <p class="mt-1 text-muted-foreground text-[11px] tracking-[0.04em]">Track · Manage · Grow</p>
                    </div>
                </div>

                <!-- Form body -->
                <div class="px-8 py-7">
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
                                    placeholder="your@email.com"
                                    :class="inputClass(!!form.errors.email)"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1.5 text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold mb-1.5 text-foreground">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-muted-foreground">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </span>
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    required autocomplete="current-password"
                                    placeholder="••••••••"
                                    :class="[inputClass(!!form.errors.password), 'pr-10']"
                                />
                                <button type="button" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-muted-foreground transition-colors hover:text-brand"
                                    @click="showPassword = !showPassword">
                                    <svg v-if="showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-5 0-9.27-3.11-11-8 .69-1.94 1.86-3.68 3.34-5.06M9.9 4.24A10.94 10.94 0 0 1 12 4c5 0 9.27 3.11 11 8-.53 1.5-1.34 2.86-2.36 4.02" />
                                        <path d="M1 1l22 22" />
                                        <path d="M9.53 9.53a3 3 0 0 0 4.24 4.24" />
                                    </svg>
                                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1.5 text-xs text-destructive">{{ form.errors.password }}</p>
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2.5 pt-1">
                            <input id="remember" type="checkbox" v-model="form.remember"
                                class="h-4 w-4 rounded border-input text-gold accent-[#C6A15B]" />
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
                                    <path d="M4 12a8 8 0 018-8" stroke="#0A0C10" stroke-width="4" stroke-linecap="round" />
                                </svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                    <polyline points="10 17 15 12 10 7" />
                                    <line x1="15" y1="12" x2="3" y2="12" />
                                </svg>
                                {{ form.processing ? 'Signing in…' : 'Sign In to My Portal' }}
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
                        <span class="text-xs font-medium text-muted-foreground">Buyer Access Only</span>
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
                            Verified Buyers
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

                    <div class="mt-4">
                        <Link :href="route('login')"
                            class="text-xs text-muted-foreground transition-colors hover:text-brand">
                            Admin staff?
                            <span class="font-bold text-brand">Go to Admin Panel →</span>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
