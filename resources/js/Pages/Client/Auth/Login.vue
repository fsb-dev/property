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

const features = [
    { label: 'My Properties',   icon: `<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><rect x="9.5" y="13" width="5" height="8"/>` },
    { label: 'Payments & Plans', icon: `<rect x="4" y="3" width="16" height="18" rx="2.5"/><path d="M8 8h8M8 12h8M8 16h5"/>` },
    { label: 'AI Property Advisor', icon: `<path d="M12 3 4 7v5c0 5 3.5 8 8 9 4.5-1 8-4 8-9V7z"/><path d="M9.5 12l1.8 1.8L15 10"/>` },
];
</script>

<template>
    <Head title="Client Login" />

    <div class="grid min-h-screen lg:grid-cols-2 bg-client-surface-page">
        <!-- ── Left: brand / hero panel ─────────────────────────────── -->
        <div class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between"
            style="background: linear-gradient(150deg,#3d2799 0%,#5132e0 45%,#6a4dff 100%); padding:56px 56px 0;">

            <!-- dot grid texture -->
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-30" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="clientDots" x="0" y="0" width="26" height="26" patternUnits="userSpaceOnUse">
                        <circle cx="1.4" cy="1.4" r="1.4" fill="#ffffff" fill-opacity="0.18" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#clientDots)" />
            </svg>

            <!-- soft glow blobs -->
            <svg class="pointer-events-none absolute -top-16 -right-16 opacity-40" width="360" height="360" viewBox="0 0 360 360" fill="none">
                <circle cx="180" cy="180" r="180" fill="url(#glowA)" />
                <defs>
                    <radialGradient id="glowA" cx="35%" cy="35%">
                        <stop offset="0%" stop-color="#b8a6ff" />
                        <stop offset="100%" stop-color="#b8a6ff" stop-opacity="0" />
                    </radialGradient>
                </defs>
            </svg>

            <!-- Top: logo -->
            <div class="relative z-10 flex items-center gap-3">
                <div style="display:flex; align-items:flex-end; gap:3px; height:34px;">
                    <div style="width:7px; height:20px; border-radius:3px; background:#ffffff;"></div>
                    <div style="width:7px; height:34px; border-radius:3px; background:linear-gradient(180deg,#fff,#cfc2ff);"></div>
                    <div style="width:7px; height:26px; border-radius:3px; background:rgba(255,255,255,0.55);"></div>
                </div>
                <div style="line-height:1;">
                    <div style="font-size:19px; font-weight:800; letter-spacing:-0.02em; color:#fff;">Home Verse</div>
                </div>
            </div>

            <!-- Middle: headline + features -->
            <div class="relative z-10 max-w-md" style="margin-top:64px;">
                <p style="font-size:12px; font-weight:700; letter-spacing:0.14em; color:#c9bdff; text-transform:uppercase;">Buyer Portal</p>
                <h1 style="font-size:34px; line-height:1.25; font-weight:800; color:#fff; margin-top:12px; letter-spacing:-0.02em;">
                    Your property journey,<br />all in one place.
                </h1>
                <p style="font-size:14.5px; line-height:1.6; color:#d9d0ff; margin-top:14px;">
                    Track your unit, payments, mortgage and construction progress 
                    with an AI advisor by your side every step of the way.
                </p>

                <div style="display:flex; flex-direction:column; gap:10px; margin-top:32px;">
                    <div v-for="f in features" :key="f.label"
                        style="display:flex; align-items:center; gap:12px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.14); border-radius:14px; padding:12px 16px; backdrop-filter:blur(6px);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" style="flex:none;" v-html="f.icon" />
                        <span style="font-size:13.5px; font-weight:600; color:#f2effc;">{{ f.label }}</span>
                    </div>
                </div>
            </div>

            <!-- Bottom: skyline illustration -->
            <svg class="relative z-10" viewBox="0 0 560 200" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%; height:auto; margin-top:40px;">
                <g opacity="0.9">
                    <rect x="0" y="120" width="60" height="80" fill="#2a1b78" />
                    <rect x="66" y="90" width="46" height="110" fill="#33218c" />
                    <rect x="118" y="60" width="58" height="140" fill="#3c27a3" />
                    <rect x="182" y="100" width="42" height="100" fill="#33218c" />
                    <rect x="230" y="40" width="66" height="160" fill="#4830b8" />
                    <rect x="302" y="78" width="48" height="122" fill="#3c27a3" />
                    <rect x="356" y="55" width="60" height="145" fill="#4830b8" />
                    <rect x="422" y="95" width="44" height="105" fill="#33218c" />
                    <rect x="472" y="70" width="52" height="130" fill="#3c27a3" />
                    <rect x="530" y="115" width="30" height="85" fill="#2a1b78" />

                    <!-- lit windows -->
                    <g fill="#ffd76a" fill-opacity="0.9">
                        <rect x="8" y="132" width="6" height="8" />
                        <rect x="22" y="132" width="6" height="8" />
                        <rect x="8" y="150" width="6" height="8" />
                        <rect x="78" y="104" width="6" height="8" />
                        <rect x="92" y="104" width="6" height="8" />
                        <rect x="78" y="124" width="6" height="8" />
                        <rect x="132" y="76" width="6" height="8" />
                        <rect x="150" y="76" width="6" height="8" />
                        <rect x="132" y="96" width="6" height="8" />
                        <rect x="150" y="96" width="6" height="8" />
                        <rect x="132" y="116" width="6" height="8" />
                        <rect x="244" y="56" width="6" height="8" />
                        <rect x="262" y="56" width="6" height="8" />
                        <rect x="244" y="76" width="6" height="8" />
                        <rect x="262" y="96" width="6" height="8" />
                        <rect x="316" y="94" width="6" height="8" />
                        <rect x="330" y="94" width="6" height="8" />
                        <rect x="370" y="72" width="6" height="8" />
                        <rect x="388" y="72" width="6" height="8" />
                        <rect x="370" y="92" width="6" height="8" />
                        <rect x="436" y="112" width="6" height="8" />
                        <rect x="486" y="86" width="6" height="8" />
                        <rect x="500" y="86" width="6" height="8" />
                    </g>
                </g>
                <rect x="0" y="196" width="560" height="4" fill="#1a1050" />
            </svg>
        </div>

        <!-- ── Right: form panel ────────────────────────────────────── -->
        <div class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-sm">
                <!-- Mobile-only logo -->
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <div style="display:flex; align-items:flex-end; gap:3px; height:30px;">
                        <div style="width:6px; height:18px; border-radius:3px; background:#5b3fe8;"></div>
                        <div style="width:6px; height:30px; border-radius:3px; background:linear-gradient(180deg,#7b63ff,#4f33d6);"></div>
                        <div style="width:6px; height:23px; border-radius:3px; background:#241a5c;"></div>
                    </div>
                    <div style="line-height:1;">
                        <div class="text-foreground" style="font-size:17px; font-weight:800; letter-spacing:-0.02em;">Home Verse</div>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-extrabold text-foreground">Welcome back</h2>
                    <p class="mt-1.5 text-sm text-muted-foreground">Sign in to your buyer portal to continue.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-foreground">Email Address</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    class="text-muted-foreground" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2" />
                                    <path d="M2 7l10 7 10-7" />
                                </svg>
                            </span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                autofocus
                                required
                                placeholder="your@email.com"
                                class="w-full rounded-xl border bg-client-surface-page py-2.5 pl-10 pr-4 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/70 focus:border-client-accent"
                                :class="form.errors.email ? 'border-destructive' : 'border-border'"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-foreground">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    class="text-muted-foreground" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                                class="w-full rounded-xl border bg-client-surface-page py-2.5 pl-10 pr-10 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/70 focus:border-client-accent"
                                :class="form.errors.password ? 'border-destructive' : 'border-border'"
                            />
                            <button
                                type="button"
                                tabindex="-1"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-muted-foreground hover:text-foreground"
                                @click="showPassword = !showPassword"
                            >
                                <svg v-if="showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-5 0-9.27-3.11-11-8 .69-1.94 1.86-3.68 3.34-5.06M9.9 4.24A10.94 10.94 0 0 1 12 4c5 0 9.27 3.11 11 8-.53 1.5-1.34 2.86-2.36 4.02" />
                                    <path d="M1 1l22 22" />
                                    <path d="M9.53 9.53a3 3 0 0 0 4.24 4.24" />
                                </svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-destructive">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-muted-foreground">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-border text-client-accent focus:ring-client-accent"
                            />
                            Remember me
                        </label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-xl py-3 text-sm font-bold text-white transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-60"
                        style="background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 8px 20px -6px rgba(81,50,224,0.55);"
                    >
                        <svg v-if="form.processing" class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="4" />
                            <path d="M4 12a8 8 0 018-8" stroke="#fff" stroke-width="4" stroke-linecap="round" />
                        </svg>
                        {{ form.processing ? 'Signing in…' : 'Sign In' }}
                    </button>
                </form>

                <p class="mt-8 text-center text-xs text-muted-foreground">
                    Admin?
                    <a :href="route('login')" class="font-semibold text-client-accent hover:underline">Go to admin login</a>
                </p>
            </div>
        </div>
    </div>
</template>
