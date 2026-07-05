<script setup>
import { Head } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

defineProps({
    client: { type: Object, default: () => ({}) },
});
</script>

<template>
    <Head title="My Dashboard" />
    <ClientLayout title="Dashboard">

        <!-- KPI stat cards -->
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <!-- Total Property Value -->
            <div class="flex flex-col gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-[#efeafc]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5b3fe8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-muted-foreground">Total Property Value</span>
                </div>
                <div class="text-2xl font-extrabold tracking-tight text-foreground">BDT 12,500,000</div>
            </div>

            <!-- Paid Amount -->
            <div class="flex flex-col gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-green-100 dark:bg-green-500/15">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7"/><path d="M3 12h18"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-muted-foreground">Paid Amount</span>
                </div>
                <div>
                    <div class="text-2xl font-extrabold tracking-tight text-foreground">BDT 4,500,000</div>
                    <div class="mt-1 text-sm font-bold text-green-600 dark:text-green-400">36% <span class="font-medium text-muted-foreground">of Total</span></div>
                </div>
            </div>

            <!-- Outstanding Balance -->
            <div class="flex flex-col gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f08a1d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="10.5" width="16" height="10.5" rx="2.5"/><path d="M7.5 10.5V8a4.5 4.5 0 0 1 9 0v2.5"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-muted-foreground">Outstanding Balance</span>
                </div>
                <div>
                    <div class="text-2xl font-extrabold tracking-tight text-foreground">BDT 8,000,000</div>
                    <div class="mt-1 text-sm font-bold text-amber-600 dark:text-amber-400">64% <span class="font-medium text-muted-foreground">Remaining</span></div>
                </div>
            </div>

            <!-- Next Payment -->
            <div class="flex flex-col gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/15">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2f6bdb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-muted-foreground">Next Payment</span>
                </div>
                <div>
                    <div class="text-2xl font-extrabold tracking-tight text-foreground">BDT 75,000</div>
                    <div class="mt-1 text-sm font-medium text-muted-foreground">Due 15 Aug 2026</div>
                </div>
            </div>
        </div>

        <!-- Hero section -->
        <div class="mt-5 flex gap-4">
            <!-- Property hero card -->
            <div class="flex-1 min-w-0 overflow-hidden rounded-2xl shadow-sm" style="background:linear-gradient(115deg,#241a5c 0%,#2c2270 45%,#3a2a8f 100%);">
                <div class="flex items-stretch">
                    <div class="w-[260px] flex-none relative overflow-hidden hidden sm:block">
                        <img src="uploads/property_01_modern_residence_day.png" alt="Property" class="absolute inset-0 w-full h-full object-cover" />
                    </div>
                    <div class="flex-1 min-w-0 p-5 flex flex-col justify-center gap-1">
                        <div class="text-xs font-semibold tracking-widest" style="color:#b3a9f0;">YOUR PROPERTY</div>
                        <h2 class="font-bold text-white mt-1" style="font-size:18px; letter-spacing:-0.02em;">Lake View Residence</h2>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm font-medium mt-1" style="color:#d2cbf5;">
                            <span>Unit A-1205</span>
                            <span class="w-1 h-1 rounded-full" style="background:#7c70c8;"></span>
                            <span>12th Floor</span>
                            <span class="w-1 h-1 rounded-full" style="background:#7c70c8;"></span>
                            <span>2 Bed Apartment</span>
                        </div>
                        <div class="mt-3">
                            <span class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1 rounded-full" style="background:rgba(34,197,94,.16); color:#5ee79a;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:#34d870;"></span>Reserved
                            </span>
                        </div>
                        <button class="mt-4 self-start flex items-center gap-2 bg-white font-semibold px-4 py-2 rounded-xl hover:bg-[#f0ecff] transition-colors text-sm" style="color:#2c2270;">
                            View Property
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Next installment card -->
            <div class="w-[240px] flex-none flex flex-col rounded-2xl p-5 shadow-sm" style="background:linear-gradient(160deg,#312981,#241a5c); border:1px solid rgba(255,255,255,.08);">
                <div class="text-xs font-semibold" style="color:#b3a9f0;">Next Installment</div>
                <div class="flex items-center gap-2.5 mt-3">
                    <div class="w-9 h-9 flex-none rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,.1);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#cdbcff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>
                    </div>
                    <div class="text-lg font-bold text-white tracking-tight">15 Aug 2026</div>
                </div>
                <div class="mt-2 text-sm font-bold" style="color:#ffc24a;">15 Days Remaining</div>
                <button class="mt-auto w-full font-bold py-3 rounded-xl text-white text-sm shadow-lg hover:brightness-105 transition-all" style="background:linear-gradient(100deg,#7b63ff,#5132e0); box-shadow:0 8px 18px -6px rgba(123,99,255,.6); margin-top:20px;">
                    Pay Now
                </button>
            </div>
        </div>

        <!-- Row 1: Payment Progress + Recent Payments + Quick Actions -->
        <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

            <!-- Payment Progress -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <h3 class="text-base font-bold text-foreground">Payment Progress</h3>
                <div class="flex items-center gap-5 mt-4">
                    <!-- Donut chart -->
                    <div class="relative w-28 h-28 flex-none rounded-full" style="background:conic-gradient(#16a34a 0% 36%, #f59e0b 36% 100%);">
                        <div class="absolute inset-[14px] rounded-full bg-client-surface-card flex flex-col items-center justify-center">
                            <div class="text-xl font-extrabold tracking-tight text-foreground">36%</div>
                            <div class="text-xs font-semibold text-muted-foreground">Paid</div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 flex-1">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>Paid Amount
                            </div>
                            <div class="text-base font-extrabold text-foreground mt-1">BDT 4,500,000</div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>Remaining Balance
                            </div>
                            <div class="text-base font-extrabold text-foreground mt-1">BDT 8,000,000</div>
                        </div>
                    </div>
                </div>
                <button class="mt-5 w-full border border-violet-200 dark:border-violet-500/30 bg-[#f6f3ff] dark:bg-client-accent/10 hover:bg-[#efeafc] dark:hover:bg-client-accent/15 text-client-accent font-semibold py-2.5 rounded-xl transition-colors text-sm">
                    View Details
                </button>
            </div>

            <!-- Recent Payments -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-foreground">Recent Payments</h3>
                    <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                </div>
                <!-- Header row -->
                <div class="grid text-xs font-bold uppercase tracking-wide text-muted-foreground pb-2 border-b border-[#f0f0f5] dark:border-white/[0.06]" style="grid-template-columns:1fr 1.1fr 1fr auto; gap:0 8px;">
                    <span>Date</span><span>Installment</span><span>Amount</span><span class="text-right">Status</span>
                </div>
                <div class="flex flex-col">
                    <div v-for="row in [
                        { date:'15 Jul 2026', name:'Installment 6', amount:'BDT 75,000', status:'Paid',     sc:'bg-[#e6f7ed] text-green-700 dark:bg-green-500/15 dark:text-green-400' },
                        { date:'15 Aug 2026', name:'Installment 7', amount:'BDT 75,000', status:'Upcoming', sc:'bg-[#eef0fe] text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400' },
                        { date:'15 Sep 2026', name:'Installment 8', amount:'BDT 75,000', status:'Pending',  sc:'bg-[#fdf2e1] text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' },
                        { date:'15 Jun 2026', name:'Installment 5', amount:'BDT 75,000', status:'Paid',     sc:'bg-[#e6f7ed] text-green-700 dark:bg-green-500/15 dark:text-green-400' },
                        { date:'15 May 2026', name:'Installment 4', amount:'BDT 75,000', status:'Paid',     sc:'bg-[#e6f7ed] text-green-700 dark:bg-green-500/15 dark:text-green-400' },
                    ]" :key="row.date + row.name"
                        class="grid items-center border-b border-[#f4f4f8] dark:border-white/[0.04] last:border-0 text-sm"
                        style="grid-template-columns:1fr 1.1fr 1fr auto; gap:8px; padding:10px 0;"
                    >
                        <span class="text-muted-foreground font-medium text-xs">{{ row.date }}</span>
                        <span class="font-semibold text-foreground text-xs">{{ row.name }}</span>
                        <span class="font-bold text-foreground text-xs">{{ row.amount }}</span>
                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-lg justify-self-end" :class="row.sc">{{ row.status }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <h3 class="text-base font-bold text-foreground mb-3">Quick Actions</h3>
                <div class="flex flex-col gap-2">
                    <a v-for="action in [
                        { label:'Make a Payment',      well:'bg-[#efeafc]',                      ic:'text-client-accent',                 icon:`<rect x='2.5' y='5' width='19' height='14' rx='2.5'/><path d='M2.5 9.5h19'/>` },
                        { label:'Download Statement',  well:'bg-green-100 dark:bg-green-500/15', ic:'text-green-600 dark:text-green-400', icon:`<path d='M12 3v12M7 10l5 5 5-5'/><path d='M4 20h16'/>` },
                        { label:'View Payment Plan',   well:'bg-blue-100 dark:bg-blue-500/15',   ic:'text-blue-600 dark:text-blue-400',   icon:`<rect x='3' y='4' width='18' height='17' rx='2.5'/><path d='M3 9h18M8 13h4M8 17h8'/>` },
                        { label:'Apply for Financing', well:'bg-amber-100 dark:bg-amber-500/15', ic:'text-amber-600 dark:text-amber-400', icon:`<path d='M3 11 12 4l9 7'/><path d='M5 10v10h14V10'/>` },
                        { label:'Chat with AI Advisor',well:'bg-[#efeafc]',                      ic:'text-client-accent',                 icon:`<path d='M4 5h16v11H9l-4 4z'/><path d='M9 10h6M9 13h4'/>` },
                        { label:'Book a Meeting',      well:'bg-blue-100 dark:bg-blue-500/15',   ic:'text-blue-600 dark:text-blue-400',   icon:`<rect x='3' y='4.5' width='18' height='16.5' rx='2.5'/><path d='M3 9h18M8 2.5v4M16 2.5v4'/><path d='m9 14 2 2 4-4'/>` },
                    ]" :key="action.label"
                        href="#"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-[#f0f0f5] dark:border-white/[0.06] hover:border-client-accent/30 hover:bg-[#faf9fd] dark:hover:bg-white/[0.04] transition-colors"
                    >
                        <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg" :class="action.well">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="action.ic" v-html="action.icon" />
                        </span>
                        <span class="flex-1 text-sm font-semibold text-foreground">{{ action.label }}</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 dark:text-slate-600"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Row 2: Construction Progress + Latest Updates + Recommended -->
        <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

            <!-- Construction Progress -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <h3 class="text-base font-bold text-foreground">Construction Progress</h3>
                <div class="text-xs font-semibold text-muted-foreground mt-4">Overall Progress</div>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-extrabold tracking-tight text-foreground">78%</span>
                    <span class="text-sm font-medium text-muted-foreground">Complete</span>
                </div>
                <div class="h-2 rounded-full bg-[#f0eef9] dark:bg-client-accent/15 mt-3 overflow-hidden">
                    <div class="h-full rounded-full" style="width:78%; background:linear-gradient(90deg,#7b63ff,#5132e0);"></div>
                </div>
                <div class="flex items-end justify-between gap-3 mt-4">
                    <div>
                        <span class="inline-flex items-center gap-2 text-sm font-bold text-green-600 dark:text-green-400">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>On Track
                        </span>
                        <div class="text-xs text-muted-foreground mt-2 font-medium">Estimated Handover</div>
                        <div class="text-sm font-extrabold text-foreground mt-0.5">December 2026</div>
                    </div>
                    <div class="relative w-[100px] h-[68px] flex-none rounded-xl overflow-hidden">
                        <img src="uploads/property_07_construction_site_wide.png" alt="Construction" class="absolute inset-0 w-full h-full object-cover" />
                        <button class="absolute right-2 bottom-2 w-6 h-6 rounded-full flex items-center justify-center border-none cursor-pointer" style="background:#5b3fe8; box-shadow:0 4px 10px -2px rgba(81,50,224,.6);">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Latest Updates -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-foreground">Latest Updates</h3>
                    <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                </div>
                <div class="flex flex-col gap-1">
                    <div v-for="item in [
                        { title:'Tower A – 12th Floor Completed',  sub:'2 days ago',  src:'uploads/property_05_lakeview_tower_dusk.png' },
                        { title:'Landscaping Work In Progress',     sub:'5 days ago',  src:'uploads/property_01_modern_residence_day.png' },
                        { title:'Interior Finishing Started',       sub:'1 week ago',  src:'uploads/property_09_luxury_interior.png' },
                        { title:'Drone Progress Video Uploaded',    sub:'2 weeks ago', src:'uploads/property_06_twin_towers_night.png', video:true },
                    ]" :key="item.title"
                        class="flex items-center gap-3 p-2 rounded-xl hover:bg-[#faf9fd] dark:hover:bg-white/[0.04] transition-colors cursor-pointer"
                    >
                        <div class="relative w-12 h-12 flex-none rounded-xl overflow-hidden">
                            <img :src="item.src" :alt="item.title" class="absolute inset-0 w-full h-full object-cover" />
                            <template v-if="item.video">
                                <div class="absolute inset-0 bg-black/30"></div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" class="absolute inset-0 m-auto"><path d="M8 5v14l11-7z"/></svg>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-foreground truncate">{{ item.title }}</div>
                            <div class="text-xs text-muted-foreground mt-0.5 font-medium">{{ item.sub }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended For You -->
            <div class="rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-foreground">Recommended For You</h3>
                    <a href="#" class="text-sm font-bold text-client-accent hover:underline">View All</a>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div v-for="prop in [
                        { name:'Green Park Heights', beds:'3 Bed', sqft:'1,450 sft', price:'BDT 14,800,000', src:'uploads/property_01_modern_residence_day.png' },
                        { name:'Skyline Towers',     beds:'2 Bed', sqft:'1,250 sft', price:'BDT 11,250,000', src:'uploads/property_03_twin_towers_sunset.png' },
                    ]" :key="prop.name"
                        class="rounded-xl overflow-hidden border border-[#f0f0f5] dark:border-white/[0.06] hover:border-client-accent/30 transition-colors cursor-pointer"
                    >
                        <div class="relative h-[80px] overflow-hidden">
                            <img :src="prop.src" :alt="prop.name" class="absolute inset-0 w-full h-full object-cover" />
                        </div>
                        <div class="p-3">
                            <div class="text-sm font-bold text-foreground">{{ prop.name }}</div>
                            <div class="text-xs text-muted-foreground mt-1 font-medium">{{ prop.beds }} · {{ prop.sqft }}</div>
                            <div class="text-sm font-extrabold text-client-accent mt-2">{{ prop.price }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-client-surface-card border-[#ededf3] dark:border-white/[0.06] px-5 py-3.5 shadow-sm">
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
