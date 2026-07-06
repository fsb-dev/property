<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    bookings: { type: Array, default: () => [] },
});

const DUMMY = [
    {
        id: null, project_name: 'Lake View Residence', project_location: 'Bashundhara, Dhaka',
        unit_number: 'A-1205', type_label: 'Apartment', bedrooms: 3, bathrooms: 2,
        size_sqft: 1450, floor: 12, price_agreed: 9500000,
        status: 'purchased', status_label: 'Purchased', status_color: 'green', cover_image: null,
        eligible_banks: ['BRAC Bank', 'City Bank'], max_loan_amount: 7600000,
    },
    {
        id: null, project_name: 'Sky Tower Gulshan', project_location: 'Gulshan-2, Dhaka',
        unit_number: 'B-0806', type_label: 'Penthouse', bedrooms: 4, bathrooms: 3,
        size_sqft: 2200, floor: 8, price_agreed: 18500000,
        status: 'reserved', status_label: 'Reserved', status_color: 'amber', cover_image: null,
        eligible_banks: [], max_loan_amount: 0,
    },
    {
        id: null, project_name: 'Green Valley Dhanmondi', project_location: 'Dhanmondi, Dhaka',
        unit_number: 'C-0312', type_label: 'Studio', bedrooms: 1, bathrooms: 1,
        size_sqft: 650, floor: 3, price_agreed: 4800000,
        status: 'purchased', status_label: 'Purchased', status_color: 'green', cover_image: null,
        eligible_banks: ['Prime Bank'], max_loan_amount: 3840000,
    },
];

const cards    = computed(() => props.bookings.length > 0 ? props.bookings : DUMMY);
const isDummy  = computed(() => props.bookings.length === 0);

const statusCls = {
    green:  'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400',
    amber:  'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
    purple: 'bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
    red:    'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400',
    slate:  'bg-slate-100 text-slate-600 dark:bg-white/[0.08] dark:text-muted-foreground',
};

function fmtBDT(n) {
    return 'BDT ' + Number(n).toLocaleString('en-US');
}
function calcHref(card) {
    if (card.id) return route('client.mortgage.show', card.id);
    return route('client.mortgage.explore', { price: card.price_agreed });
}
</script>

<template>
    <Head title="Mortgage & Financing" />
    <ClientLayout title="Mortgage & Financing">

        <!-- ── Header ─────────────────────────────────────────────── -->
        <div class="flex items-start justify-between gap-5">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-foreground" style="letter-spacing:-0.025em;">Mortgage &amp; Financing</h1>
                <p class="mt-1 text-sm font-medium text-muted-foreground">Plan your mortgage and explore the best financing options for your properties.</p>
            </div>
            <Link :href="route('client.mortgage.explore')"
                class="hidden sm:inline-flex items-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl text-white"
                style="background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 10px 22px -8px rgba(81,50,224,.5); flex:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M8 2.5v4M16 2.5v4M3 9h18"/></svg>
                Free Calculator
            </Link>
        </div>

        <!-- ── Demo notice ─────────────────────────────────────────── -->
        <div v-if="isDummy" class="flex items-center gap-3 p-4 rounded-2xl border"
            style="background:#fffbeb; border-color:#fde68a;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-none"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            <p class="text-sm font-medium text-amber-800">You have no linked properties yet. Sample properties are shown below — each calculator is fully interactive with real mortgage math.</p>
        </div>

        <!-- ── Body ──────────────────────────────────────────────────  -->
        <div class="flex gap-5 items-start">

            <!-- ===== Property cards ================================ -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-bold text-foreground">
                        {{ isDummy ? 'Sample Properties' : 'Your Properties' }}
                        <span class="ml-1.5 inline-block text-xs font-bold text-muted-foreground bg-muted px-2 py-0.5 rounded-full">{{ cards.length }}</span>
                    </span>
                    <span v-if="isDummy" class="text-xs font-bold text-amber-600 px-2.5 py-1 bg-amber-50 rounded-lg border border-amber-200">Demo Mode</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div v-for="card in cards" :key="card.id ?? card.unit_number"
                        class="relative rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">

                        <!-- Cover image -->
                        <div class="relative h-[160px] bg-gradient-to-br flex items-center justify-center flex-none"
                            style="background:linear-gradient(135deg,#e8e3fb,#d8d0f5);">
                            <img v-if="card.cover_image" :src="card.cover_image" :alt="card.project_name"
                                class="absolute inset-0 w-full h-full object-cover" />
                            <svg v-else width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9b8de6" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" class="opacity-70">
                                <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><rect x="9.5" y="13" width="5" height="8"/>
                            </svg>
                            <!-- Status badge -->
                            <span class="absolute top-3 right-3 text-xs font-bold px-2.5 py-1 rounded-lg"
                                :class="statusCls[card.status_color] ?? statusCls.slate">
                                {{ card.status_label }}
                            </span>
                            <!-- Demo badge -->
                            <span v-if="isDummy" class="absolute top-3 left-3 text-xs font-bold px-2.5 py-1 rounded-lg bg-black/40 text-white backdrop-blur-sm">Sample</span>
                        </div>

                        <!-- Card body -->
                        <div class="p-4 flex-1 flex flex-col">
                            <div>
                                <div class="text-sm font-extrabold text-foreground leading-tight truncate">{{ card.project_name }}</div>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground font-medium">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                    <span class="truncate">{{ card.project_location }}</span>
                                </div>
                            </div>

                            <!-- Specs row -->
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#efeafc] text-client-accent">{{ card.type_label }}</span>
                                <span class="flex items-center gap-1 text-xs text-muted-foreground font-medium">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20V10l9-8 9 8v10"/><rect x="9" y="14" width="6" height="6"/></svg>
                                    Unit {{ card.unit_number }}
                                </span>
                                <span v-if="card.floor" class="text-xs text-muted-foreground font-medium">Floor {{ card.floor }}</span>
                                <span v-if="card.bedrooms" class="text-xs text-muted-foreground font-medium">{{ card.bedrooms }} Bed</span>
                                <span v-if="card.size_sqft" class="text-xs text-muted-foreground font-medium">{{ Number(card.size_sqft).toLocaleString() }} sqft</span>
                            </div>

                            <!-- Price -->
                            <div class="mt-4 pt-3.5 border-t border-[#f0f0f5] dark:border-white/[0.06]">
                                <div class="text-xs font-semibold text-muted-foreground">Property Price</div>
                                <div class="text-lg font-extrabold text-foreground mt-0.5" style="letter-spacing:-0.02em;">{{ fmtBDT(card.price_agreed) }}</div>
                            </div>

                            <!-- Eligible banks (if any) -->
                            <div v-if="card.eligible_banks?.length" class="mt-2 flex flex-wrap gap-1.5">
                                <span v-for="bank in card.eligible_banks" :key="bank"
                                    class="text-xs font-semibold px-2 py-0.5 rounded-md bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 border border-green-200 dark:border-green-500/20">
                                    {{ bank }}
                                </span>
                            </div>

                            <!-- Max loan hint -->
                            <div v-if="card.max_loan_amount > 0" class="mt-2 text-xs font-medium text-muted-foreground">
                                Max loan: <span class="font-bold text-foreground">{{ fmtBDT(card.max_loan_amount) }}</span>
                            </div>

                            <!-- CTA -->
                            <div class="mt-auto pt-4">
                                <Link :href="calcHref(card)"
                                    class="flex items-center justify-center gap-2 w-full text-sm font-bold py-2.5 rounded-xl text-white transition-opacity hover:opacity-90"
                                    style="background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 8px 18px -6px rgba(81,50,224,.45);">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M8 2.5v4M16 2.5v4M3 9h18M8 13h8M8 16.5h5"/></svg>
                                    Calculate Mortgage
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ===== END cards ===================================== -->

            <!-- ===== Right rail ==================================== -->
            <aside class="hidden xl:flex flex-col gap-4" style="width:296px; flex:none;">

                <!-- What is a Mortgage? -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-3.5">
                        <span class="w-9 h-9 flex-none rounded-xl bg-[#efeafc] flex items-center justify-center text-client-accent">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                        </span>
                        <h3 class="text-sm font-bold text-foreground">What is a Mortgage?</h3>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground leading-relaxed">A mortgage is a loan used to purchase property. You borrow from a bank and repay in monthly installments with interest over a fixed term.</p>
                    <div class="mt-3.5 flex flex-col gap-2.5">
                        <div class="flex items-start gap-2 text-xs text-muted-foreground font-medium">
                            <span class="w-4 h-4 flex-none rounded-full bg-[#efeafc] flex items-center justify-center text-[8.5px] font-extrabold text-client-accent mt-0.5">1</span>
                            Choose a property and agree on the price.
                        </div>
                        <div class="flex items-start gap-2 text-xs text-muted-foreground font-medium">
                            <span class="w-4 h-4 flex-none rounded-full bg-[#efeafc] flex items-center justify-center text-[8.5px] font-extrabold text-client-accent mt-0.5">2</span>
                            Pay a down payment (typically 20–40%).
                        </div>
                        <div class="flex items-start gap-2 text-xs text-muted-foreground font-medium">
                            <span class="w-4 h-4 flex-none rounded-full bg-[#efeafc] flex items-center justify-center text-[8.5px] font-extrabold text-client-accent mt-0.5">3</span>
                            Bank finances the balance over 5–25 years.
                        </div>
                    </div>
                </div>

                <!-- Free Calculator CTA -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-9 h-9 flex-none rounded-xl bg-blue-100 dark:bg-blue-500/15 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2.5"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                        </span>
                        <h3 class="text-sm font-bold text-foreground">Free Calculator</h3>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground leading-relaxed mb-3.5">Explore any property price scenario with our interactive mortgage calculator.</p>
                    <Link :href="route('client.mortgage.explore')"
                        class="flex items-center justify-center gap-2 text-sm font-bold py-2.5 rounded-xl border transition-colors text-client-accent"
                        style="border-color:#e6e1fb; background:#f6f3ff;">
                        Open Calculator →
                    </Link>
                </div>

                <!-- Banking Partners -->
                <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-foreground">Our Banking Partners</h3>
                        <a href="#" class="text-xs font-bold text-client-accent hover:underline">View All</a>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="flex items-center justify-center h-12 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold" style="color:#1b3a6b;">BRAC Bank</div>
                        <div class="flex items-center justify-center h-12 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold" style="color:#c0392b;">City Bank</div>
                        <div class="flex items-center justify-center h-12 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold" style="color:#1d7a46;">Islami Bank</div>
                        <div class="flex items-center justify-center h-12 border border-[#f0f0f5] dark:border-white/[0.06] rounded-xl text-xs font-extrabold" style="color:#2c3e8c;">Prime Bank</div>
                    </div>
                </div>

                <!-- Get Pre-Approved promo -->
                <div class="relative overflow-hidden rounded-2xl p-5 border border-[#e3daff]"
                    style="background:linear-gradient(135deg,#efeafc,#e7e0ff);">
                    <div style="max-width:170px;">
                        <div class="text-sm font-bold text-[#3a25b0]">Get Pre-Approved</div>
                        <div class="text-xs font-medium leading-relaxed text-[#6a5fae] mt-1.5 mb-4">Connect with our banking partners for the best rates on your property.</div>
                        <button class="text-xs font-bold px-4 py-2.5 rounded-xl text-white border-0 cursor-pointer"
                            style="background:linear-gradient(100deg,#6a4dff,#5132e0); box-shadow:0 6px 14px -4px rgba(81,50,224,.55);">
                            Apply Now
                        </button>
                    </div>
                    <div class="absolute right-[-6px] bottom-[-6px] w-[76px] h-[76px] rounded-2xl flex items-center justify-center"
                        style="background:linear-gradient(160deg,#7b63ff,#4f33d6); box-shadow:0 12px 24px -8px rgba(79,51,214,.6);">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#cdbcff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-5h6v5"/></svg>
                    </div>
                </div>
            </aside>
            <!-- ===== END right rail ================================= -->

        </div>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] px-5 py-3.5 shadow-sm">
            <div class="text-sm font-bold text-foreground">
                HomeVerse<sup class="text-xs font-normal text-muted-foreground">™</sup>
                <span class="text-muted-foreground font-normal ml-2">·</span>
                <span class="text-sm font-medium text-muted-foreground ml-2">Real Estate Intelligence Platform</span>
            </div>
            <div class="text-xs font-medium text-muted-foreground">
                Powered by <span class="font-bold text-foreground">Future Studios Bangladesh</span>
                · <a href="#" class="font-bold text-client-accent">www.fsb.site</a>
                · © 2026
            </div>
        </footer>

    </ClientLayout>
</template>
