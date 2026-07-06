<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

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
</script>

<template>
    <Head title="Client Sign In" />

    <!-- Page background — same light gradient + dot grid as admin -->
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden"
        style="background: linear-gradient(145deg, #F0F2F8 0%, #E8EBF5 50%, #EEF0FA 100%);">

        <!-- Dot grid -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="dots" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="1.5" cy="1.5" r="1.5" fill="#C5CAE0" fill-opacity="0.55" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dots)" />
        </svg>

        <!-- Purple glow blob top-right -->
        <svg class="absolute -top-24 -right-24 opacity-20 pointer-events-none" width="480" height="480" viewBox="0 0 480 480" fill="none">
            <circle cx="240" cy="240" r="240" fill="url(#blobC)" />
            <defs>
                <radialGradient id="blobC" cx="70%" cy="30%">
                    <stop offset="0%" stop-color="#5B3DF5" />
                    <stop offset="100%" stop-color="#7C5CFF" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- Soft purple blob bottom-left -->
        <svg class="absolute -bottom-20 -left-20 opacity-15 pointer-events-none" width="400" height="400" viewBox="0 0 400 400" fill="none">
            <circle cx="200" cy="200" r="200" fill="url(#blobD)" />
            <defs>
                <radialGradient id="blobD" cx="30%" cy="70%">
                    <stop offset="0%" stop-color="#5B3DF5" />
                    <stop offset="100%" stop-color="#5B3DF5" stop-opacity="0" />
                </radialGradient>
            </defs>
        </svg>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md mx-4">
            <div class="bg-white rounded-3xl overflow-hidden"
                style="box-shadow: 0 24px 64px rgba(91,61,245,0.13), 0 4px 16px rgba(91,61,245,0.09);">

                <!-- Card header — white with purple top-bar accent -->
                <div class="px-8 pt-8 pb-6 text-center relative"
                    style="background:#fff; border-bottom:1px solid #EDE8FF;">

                    <!-- Purple gradient top-bar -->
                    <div style="position:absolute; top:0; left:0; right:0; height:4px;
                                background:linear-gradient(90deg,#5B3DF5 0%,#7C5CFF 50%,#5B3DF5 100%);"></div>

                    <!-- Logo mark — same purple gradient as admin -->
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4"
                        style="background:linear-gradient(145deg,#5B3DF5,#7C5CFF);
                               box-shadow:0 8px 24px rgba(91,61,245,0.40);">
                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                            <rect x="5" y="9" width="18" height="17" rx="1" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.6)" stroke-width="1.3"/>
                            <rect x="3" y="7.5" width="22" height="2" rx="0.9" fill="white" fill-opacity="0.92"/>
                            <rect x="7.5" y="13" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.95"/>
                            <rect x="16" y="13" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.95"/>
                            <rect x="7.5" y="18" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.4"/>
                            <rect x="16" y="18" width="4.5" height="3" rx="0.5" fill="white" fill-opacity="0.95"/>
                            <rect x="12" y="22" width="4" height="4" rx="0.6" fill="white" fill-opacity="0.85"/>
                        </svg>
                    </div>

                    <div style="font-size:22px; font-weight:800; color:#1E2A3B; letter-spacing:-0.3px;">Home Verse</div>
                    <div style="font-size:10px; font-weight:700; letter-spacing:3px; color:#5B3DF5; margin-top:2px;">CLIENT PORTAL</div>

                    <!-- Tagline row — same pattern as admin, purple tones on white -->
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
                        <p style="font-size:13px; font-weight:700; letter-spacing:0.06em;
                                  color:#5B3DF5; text-transform:uppercase;">Your Property Journey Starts Here</p>
                        <p class="mt-1" style="font-size:11px; color:#8890A6; letter-spacing:0.04em;">Track · Manage · Grow</p>
                    </div>
                </div>

                <!-- Form body -->
                <div class="px-8 py-7">
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
                                    placeholder="your@email.com"
                                    class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border transition-all duration-150 outline-none"
                                    :style="form.errors.email
                                        ? 'border-color:#FCA5A5; background:#FFF5F5; color:#1E2A3B;'
                                        : 'border-color:#DDE2EF; background:#F7F8FC; color:#1E2A3B;'"
                                    @focus="e => e.target.style.borderColor='#5B3DF5'"
                                    @blur="e => e.target.style.borderColor = form.errors.email ? '#FCA5A5' : '#DDE2EF'"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1.5 text-xs" style="color:#EF4444;">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold mb-1.5"
                                style="color:#1E2A3B;">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9AA5BC"
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
                                    class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border transition-all duration-150 outline-none"
                                    :style="form.errors.password
                                        ? 'border-color:#FCA5A5; background:#FFF5F5; color:#1E2A3B;'
                                        : 'border-color:#DDE2EF; background:#F7F8FC; color:#1E2A3B;'"
                                    @focus="e => e.target.style.borderColor='#5B3DF5'"
                                    @blur="e => e.target.style.borderColor = form.errors.password ? '#FCA5A5' : '#DDE2EF'"
                                />
                                <button type="button" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 transition-colors"
                                    style="color:#9AA5BC;"
                                    @click="showPassword = !showPassword"
                                    @mouseover="e => e.currentTarget.style.color='#5B3DF5'"
                                    @mouseleave="e => e.currentTarget.style.color='#9AA5BC'">
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
                            <p v-if="form.errors.password" class="mt-1.5 text-xs" style="color:#EF4444;">{{ form.errors.password }}</p>
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2.5 pt-1">
                            <input id="remember" type="checkbox" v-model="form.remember"
                                class="h-4 w-4 rounded"
                                style="accent-color:#5B3DF5;" />
                            <label for="remember" class="text-sm cursor-pointer select-none"
                                style="color:#5A6478;">Keep me signed in</label>
                        </div>

                        <!-- Submit — same purple gradient button as admin -->
                        <div class="flex items-center gap-2 mt-1">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white transition-all duration-200"
                                style="background:linear-gradient(135deg,#5B3DF5,#7C5CFF);
                                       box-shadow:0 8px 24px rgba(91,61,245,0.35);"
                                @mouseover="e => !form.processing && (e.currentTarget.style.boxShadow='0 12px 32px rgba(91,61,245,0.5)')"
                                @mouseleave="e => e.currentTarget.style.boxShadow='0 8px 24px rgba(91,61,245,0.35)'"
                                :style="form.processing ? 'opacity:0.65; cursor:not-allowed;' : ''">
                                <svg v-if="form.processing" class="animate-spin" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4" />
                                    <path d="M4 12a8 8 0 018-8" stroke="#fff" stroke-width="4" stroke-linecap="round" />
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
                                class="flex-none flex items-center justify-center px-4 h-11 rounded-xl border text-sm font-semibold whitespace-nowrap transition-all duration-150"
                                style="border-color:#DDE2EF; background:#F7F8FC; color:#5B3DF5;"
                                @click="quickLogin"
                                @mouseover="e => e.currentTarget.style.background='#EEF0FA'"
                                @mouseleave="e => e.currentTarget.style.background='#F7F8FC'">
                                Quick Login
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Card footer — same structure as admin -->
                <div class="px-8 pb-6 text-center">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-1 h-px" style="background:#EEF0F8;"></div>
                        <span class="text-xs font-medium" style="color:#B0B8CC;">Buyer Access Only</span>
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
                        <a :href="route('login')"
                            class="text-xs transition-colors"
                            style="color:#9AA5BC;"
                            @mouseover="e => e.currentTarget.style.color='#5B3DF5'"
                            @mouseleave="e => e.currentTarget.style.color='#9AA5BC'">
                            Admin staff?
                            <span style="font-weight:700; color:#5B3DF5;">Go to Admin Panel →</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
