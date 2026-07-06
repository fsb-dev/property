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
</script>

<template>
    <Head title="Admin Sign In" />

    <!-- Page background -->
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden"
        style="background: linear-gradient(145deg, #F0F2F8 0%, #E8EBF5 50%, #EEF0FA 100%);">

        <!-- SVG background decoration: grid dots -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="dots" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="1.5" cy="1.5" r="1.5" fill="#C5CAE0" fill-opacity="0.55" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dots)" />
        </svg>

        <!-- SVG blob top-left -->
        <svg class="absolute -top-24 -left-24 opacity-20 pointer-events-none" width="480" height="480" viewBox="0 0 480 480" fill="none">
            <circle cx="240" cy="240" r="240" fill="url(#blobA)" />
            <defs>
                <radialGradient id="blobA" cx="30%" cy="30%">
                    <stop offset="0%" stop-color="#5B3DF5" />
                    <stop offset="100%" stop-color="#7C5CFF" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- SVG blob bottom-right -->
        <svg class="absolute -bottom-20 -right-20 opacity-15 pointer-events-none" width="400" height="400" viewBox="0 0 400 400" fill="none">
            <circle cx="200" cy="200" r="200" fill="url(#blobB)" />
            <defs>
                <radialGradient id="blobB" cx="70%" cy="70%">
                    <stop offset="0%" stop-color="#0A1B36" />
                    <stop offset="100%" stop-color="#0A1B36" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md mx-4">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden"
                style="box-shadow: 0 24px 64px rgba(10,27,54,0.13), 0 4px 16px rgba(91,61,245,0.08);">

                <!-- Card header band -->
                <div class="px-8 pt-8 pb-6 text-center"
                    style="background: linear-gradient(135deg, #0A1B36 0%, #07162D 60%, #0D1F3C 100%);">

                    <!-- Logo mark -->
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4"
                        style="background: linear-gradient(145deg,#5B3DF5,#7C5CFF); box-shadow: 0 8px 24px rgba(91,61,245,0.45);">
                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                            <!-- Building body -->
                            <rect x="5" y="9" width="18" height="17" rx="1" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.6)" stroke-width="1.3"/>
                            <!-- Roof cap -->
                            <rect x="3" y="7.5" width="22" height="2" rx="0.9" fill="white" fill-opacity="0.92"/>
                            <!-- Windows row 1 -->
                            <rect x="7.5" y="13" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.95"/>
                            <rect x="16" y="13" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.95"/>
                            <!-- Windows row 2 -->
                            <rect x="7.5" y="18" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.4"/>
                            <rect x="16" y="18" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.95"/>
                            <!-- Door -->
                            <rect x="12" y="22" width="4" height="4" rx="0.6" fill="white" fill-opacity="0.85"/>
                        </svg>
                    </div>

                    <div style="font-size:22px; font-weight:800; color:#fff; letter-spacing:-0.3px;">Home Verse</div>
                    <div style="font-size:10px; font-weight:700; letter-spacing:3px; color:#6E7C95; margin-top:2px;">ADMIN PANEL</div>

                    <!-- Admin tagline -->
                    <div class="mt-4 mb-1">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <div class="h-px w-8" style="background:linear-gradient(to right,transparent,#5B3DF5);"></div>
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <rect x="2" y="5" width="10" height="8" rx="0.6" stroke="#5B3DF5" stroke-width="1.2"/>
                                <rect x="1" y="4.2" width="12" height="1.4" rx="0.5" fill="#5B3DF5"/>
                                <rect x="3.5" y="7" width="2.5" height="1.8" rx="0.3" fill="#5B3DF5"/>
                                <rect x="8" y="7" width="2.5" height="1.8" rx="0.3" fill="#5B3DF5"/>
                                <rect x="5.5" y="9.5" width="3" height="3.5" rx="0.4" fill="#5B3DF5"/>
                            </svg>
                            <div class="h-px w-8" style="background:linear-gradient(to left,transparent,#5B3DF5);"></div>
                        </div>
                        <p style="font-size:13px; font-weight:700; letter-spacing:0.06em; color:#A89BFF; text-transform:uppercase;">Where Properties Meet Precision</p>
                        <p class="mt-1" style="font-size:11px; color:#5A6478; letter-spacing:0.04em;">Manage · Grow · Deliver</p>
                    </div>


                </div>

                <!-- Form body -->
                <div class="px-8 py-7">

                    <!-- Status -->
                    <div v-if="status"
                        class="mb-5 flex items-center gap-2.5 rounded-xl px-4 py-3 text-sm"
                        style="background:#F0FDF4; border:1px solid #BBF7D0; color:#15803D;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5" />
                        </svg>
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-1.5"
                                style="color:#1E2A3B;">Email Address</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9AA5BC"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                        <path d="M2 7l10 7 10-7" />
                                    </svg>
                                </span>
                                <input
                                    id="email" type="email" v-model="form.email"
                                    required autofocus autocomplete="username"
                                    placeholder="admin@example.com"
                                    class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border transition-all duration-150 outline-none"
                                    :style="form.errors.email
                                        ? 'border-color:#FCA5A5; background:#FFF5F5; color:#1E2A3B;'
                                        : 'border-color:#DDE2EF; background:#F7F8FC; color:#1E2A3B;'"
                                    style="focus:ring:2px solid #5B3DF5;"
                                    @focus="e => e.target.style.borderColor='#5B3DF5'"
                                    @blur="e => e.target.style.borderColor = form.errors.email ? '#FCA5A5' : '#DDE2EF'"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-sm font-semibold"
                                    style="color:#1E2A3B;">Password</label>
                                <Link v-if="canResetPassword" :href="route('password.request')"
                                    class="text-xs font-semibold transition-colors"
                                    style="color:#5B3DF5;"
                                    @mouseover="e => e.target.style.color='#7C5CFF'"
                                    @mouseleave="e => e.target.style.color='#5B3DF5'">
                                    Forgot password?
                                </Link>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9AA5BC"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </span>
                                <input
                                    id="password" type="password" v-model="form.password"
                                    required autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border transition-all duration-150 outline-none"
                                    :style="form.errors.password
                                        ? 'border-color:#FCA5A5; background:#FFF5F5; color:#1E2A3B;'
                                        : 'border-color:#DDE2EF; background:#F7F8FC; color:#1E2A3B;'"
                                    @focus="e => e.target.style.borderColor='#5B3DF5'"
                                    @blur="e => e.target.style.borderColor = form.errors.password ? '#FCA5A5' : '#DDE2EF'"
                                />
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password" />
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2.5 pt-1">
                            <Checkbox id="remember" name="remember" v-model:checked="form.remember" />
                            <label for="remember" class="text-sm cursor-pointer select-none"
                                style="color:#5A6478;">Keep me signed in</label>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white transition-all duration-200 mt-1"
                            style="background: linear-gradient(135deg,#5B3DF5,#7C5CFF); box-shadow: 0 8px 24px rgba(91,61,245,0.35);"
                            @mouseover="e => !form.processing && (e.currentTarget.style.boxShadow='0 12px 32px rgba(91,61,245,0.5)')"
                            @mouseleave="e => e.currentTarget.style.boxShadow='0 8px 24px rgba(91,61,245,0.35)'"
                            :style="form.processing ? 'opacity:0.65; cursor:not-allowed;' : ''">
                            <svg v-if="form.processing" class="animate-spin" width="16" height="16"
                                viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4" />
                                <path d="M4 12a8 8 0 018-8" stroke="#fff" stroke-width="4"
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

                    </form>
                </div>

                <!-- Card footer -->
                <div class="px-8 pb-6 text-center">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-1 h-px" style="background:#EEF0F8;"></div>
                        <span class="text-xs font-medium" style="color:#B0B8CC;">Secured Access</span>
                        <div class="flex-1 h-px" style="background:#EEF0F8;"></div>
                    </div>
                    <div class="flex items-center justify-center gap-4 text-xs" style="color:#9AA5BC;">
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
