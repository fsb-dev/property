<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard    from '@/Components/Admin/StatCard.vue';

defineOptions({
    layout: (h, page) => h(AdminLayout, { title: 'Dashboard', breadcrumbs: [{ label: 'Dashboard' }] }, () => page),
});

defineProps({
    stats: {
        type: Object,
        default: () => ({ total_clients: 0, active_projects: 0, units_sold: 0, revenue_collected: 0 }),
    },
    recent_bookings:  { type: Array, default: () => [] },
    project_overview: { type: Array, default: () => [] },
});

const bookingStatusMap = {
    reserved:    { classes: 'bg-admin-accent/10 text-admin-accent',                                   label: 'Reserved'    },
    purchased:   { classes: 'bg-green-50  text-green-700  dark:bg-green-500/15 dark:text-green-400',  label: 'Purchased'   },
    cancelled:   { classes: 'bg-red-50    text-red-700    dark:bg-red-500/15   dark:text-red-400',    label: 'Cancelled'   },
    handed_over: { classes: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400', label: 'Handed Over' },
};

function bookingStatus(status) {
    return bookingStatusMap[status] ?? { classes: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300', label: status };
}

function formatBDT(amount) {
    if (!amount) return '৳ 0';
    return '৳ ' + Number(amount).toLocaleString('en-BD');
}

const quickActions = [
    { label: 'Add Client',      color: 'accent', icon: `<circle cx="10" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/><path d="M19 8v6M22 11h-6"/>` },
    { label: 'New Project',     color: 'green',  icon: `<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-5h6v5"/>` },
    { label: 'Record Payment',  color: 'indigo', icon: `<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 9.5h19"/>` },
    { label: 'Create Booking',  color: 'orange', icon: `<rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/><path d="m9 14 2 2 4-4"/>` },
    { label: 'Add Unit',        color: 'accent', icon: `<rect x="3" y="8" width="8" height="13" rx="1.5"/><rect x="13" y="3" width="8" height="18" rx="1.5"/>` },
    { label: 'Upload Document', color: 'purple', icon: `<path d="M12 3v12M7 10l5-5 5 5"/><path d="M4 20h16"/>` },
];

// Icon colours — accent tracks the CSS variable, others are fixed semantic
const iconStroke = {
    accent: 'text-admin-accent',
    green:  'text-green-600  dark:text-green-400',
    indigo: 'text-indigo-600 dark:text-indigo-400',
    orange: 'text-orange-500 dark:text-orange-400',
    purple: 'text-purple-600 dark:text-purple-400',
};

const iconWell = {
    accent: 'bg-admin-accent/10',
    green:  'bg-green-100  dark:bg-green-500/15',
    indigo: 'bg-indigo-100 dark:bg-indigo-500/15',
    orange: 'bg-orange-100 dark:bg-orange-500/15',
    purple: 'bg-purple-100 dark:bg-purple-500/15',
};
</script>

<template>
    <!-- Stat cards -->
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <StatCard label="Total Clients"     :value="stats.total_clients.toString()"     sub="Active buyers"       color="accent" sub-variant="muted" >
            <template #icon><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M17 11a4 4 0 0 1 0 8"/><path d="M21 21v-2a4 4 0 0 0-3-3.87"/></svg></template>
        </StatCard>

        <StatCard label="Active Projects"   :value="stats.active_projects.toString()"   sub="Under construction"  color="accent" sub-variant="muted" >
            <template #icon><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-5h6v5"/></svg></template>
        </StatCard>

        <StatCard label="Units Sold"        :value="stats.units_sold.toString()"        sub="Confirmed purchases" color="green"  sub-variant="green" >
            <template #icon><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="8" height="13" rx="1.5"/><rect x="13" y="3" width="8" height="18" rx="1.5"/><path d="M6 12h2M6 15.5h2M16 7h2M16 10.5h2M16 14h2"/></svg></template>
        </StatCard>

        <StatCard label="Revenue Collected" :value="formatBDT(stats.revenue_collected)" sub="Completed payments"  color="orange" sub-variant="orange">
            <template #icon><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7"/><path d="M3 12h18"/></svg></template>
        </StatCard>
    </div>

    <!-- Row 1 -->
    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

        <!-- Recent Bookings -->
        <div class="xl:col-span-2 rounded-2xl border bg-admin-surface-card border-slate-100 dark:border-white/[0.06] p-5 shadow-sm transition-colors duration-200">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Recent Bookings</h2>
                <a href="#" class="text-sm font-bold text-admin-accent hover:underline">View All</a>
            </div>

            <div v-if="!recent_bookings.length" class="flex flex-col items-center justify-center py-12 gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-admin-accent/10">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No bookings yet</p>
                <p class="text-xs text-slate-400 dark:text-slate-600">Bookings will appear here once clients are assigned units.</p>
            </div>

            <div v-else class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                            <th class="pb-3 pl-1 text-left text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Client</th>
                            <th class="pb-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Unit</th>
                            <th class="pb-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Project</th>
                            <th class="pb-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Value</th>
                            <th class="pb-3 pr-1 text-right text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in recent_bookings" :key="booking.id"
                            class="border-b border-slate-50 dark:border-white/[0.03] last:border-0">
                            <td class="py-3 pl-1 font-semibold text-slate-900 dark:text-slate-100">{{ booking.client_name }}</td>
                            <td class="py-3 text-slate-500 dark:text-slate-400">{{ booking.unit_number }}</td>
                            <td class="py-3 text-slate-500 dark:text-slate-400">{{ booking.project_name }}</td>
                            <td class="py-3 font-bold text-slate-900 dark:text-slate-100">{{ formatBDT(booking.price_agreed) }}</td>
                            <td class="py-3 pr-1 text-right">
                                <span class="inline-block rounded-lg px-2.5 py-1 text-xs font-bold" :class="bookingStatus(booking.status).classes">
                                    {{ bookingStatus(booking.status).label }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-2xl border bg-admin-surface-card border-slate-100 dark:border-white/[0.06] p-5 shadow-sm transition-colors duration-200">
            <h2 class="mb-4 text-base font-bold text-slate-900 dark:text-slate-100">Quick Actions</h2>
            <div class="flex flex-col gap-2">
                <a
                    v-for="action in quickActions"
                    :key="action.label"
                    href="#"
                    class="flex items-center gap-3 rounded-xl border px-3 py-2.5 transition-colors
                           border-slate-100 dark:border-white/[0.06]
                           hover:border-admin-accent/30
                           hover:bg-slate-50 dark:hover:bg-white/[0.04]"
                >
                    <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg transition-colors" :class="iconWell[action.color]">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="iconStroke[action.color]" v-html="action.icon" />
                    </span>
                    <span class="flex-1 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ action.label }}</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 dark:text-slate-600"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Row 2 -->
    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">

        <!-- Project Overview -->
        <div class="rounded-2xl border bg-admin-surface-card border-slate-100 dark:border-white/[0.06] p-5 shadow-sm transition-colors duration-200">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Project Overview</h2>
                <a href="#" class="text-sm font-bold text-admin-accent hover:underline">Manage</a>
            </div>
            <div v-if="!project_overview.length" class="flex flex-col items-center justify-center py-10 gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-admin-accent/10">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent">
                        <path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-5h6v5"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No projects yet</p>
            </div>
            <div v-else class="flex flex-col gap-5">
                <div v-for="project in project_overview" :key="project.id">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ project.name }}</span>
                        <span class="text-sm font-bold text-admin-accent">{{ project.overall_progress }}%</span>
                    </div>
                    <div class="h-2 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-700">
                        <div class="h-full rounded-full bg-admin-accent" :style="{ width: project.overall_progress + '%' }" />
                    </div>
                    <div class="mt-1.5 flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-slate-500">
                        <span>{{ project.total_units }} units</span><span>·</span><span>{{ project.status }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Installments -->
        <div class="rounded-2xl border bg-admin-surface-card border-slate-100 dark:border-white/[0.06] p-5 shadow-sm transition-colors duration-200">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Upcoming Installments</h2>
                <a href="#" class="text-sm font-bold text-admin-accent hover:underline">View All</a>
            </div>
            <div class="flex flex-col items-center justify-center py-10 gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 dark:bg-orange-500/10">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 dark:text-orange-400">
                        <rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No upcoming installments</p>
                <p class="text-xs text-slate-400 dark:text-slate-600">Installments due in the next 30 days will appear here.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-admin-surface-card border-slate-100 dark:border-white/[0.06] px-6 py-4 shadow-sm transition-colors duration-200">
        <div class="text-sm font-bold text-slate-900 dark:text-slate-100">
            Property Admin <span class="text-xs font-normal text-slate-400 dark:text-slate-500">· Real Estate Management Platform</span>
        </div>
        <div class="text-xs font-medium text-slate-400 dark:text-slate-500">
            Powered by <span class="font-bold text-slate-700 dark:text-slate-300">Future Studios Bangladesh</span>
            · <a href="https://fsb.site" class="font-bold text-admin-accent">fsb.site</a>
            · © 2026
        </div>
    </footer>
</template>
