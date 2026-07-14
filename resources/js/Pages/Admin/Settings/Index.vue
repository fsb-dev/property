<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, nextTick } from 'vue';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Separator } from '@/Components/ui/separator';
import { Textarea } from '@/Components/ui/textarea';

const props = defineProps({
    navItems:          { type: Array,  required: true },
    generalSettings:   { type: Object, required: true },
    systemPreferences: { type: Array,  required: true },
    security:          { type: Array,  required: true },
    notifications:     { type: Array,  required: true },
    emailSettings:     { type: Object, required: true },
    backup:            { type: Object, required: true },
    integrations:      { type: Array,  required: true },
    apiKeys:           { type: Array,  required: true },
    systemInfo:        { type: Object, required: true },
    systemLogs:        { type: Array,  required: true },
});

// ── Icon paths, keyed by the `icon` field coming from the JSON ────────────────
const icons = {
    general:    '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    security:   '<path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/>',
    bell:       '<path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"/>',
    mail:       '<path d="M3 5h18v14H3zM3 7l9 6 9-6"/>',
    sliders:    '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
    palette:    '<path d="M12 2a10 10 0 100 20 2 2 0 002-2 2 2 0 011.5-3.5H18a4 4 0 004-4 8 8 0 00-10-10.5zM7.5 11.5a1 1 0 110-2 1 1 0 010 2zM10.5 7.5a1 1 0 110-2 1 1 0 010 2zM14.5 7.5a1 1 0 110-2 1 1 0 010 2z"/>',
    globe:      '<circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15 15 0 010 20 15 15 0 010-20"/>',
    plug:       '<path d="M9 2v6M15 2v6M6 8h12v3a6 6 0 01-12 0zM12 17v5"/>',
    backup:     '<path d="M12 3c4.4 0 8 1.3 8 3s-3.6 3-8 3-8-1.3-8-3 3.6-3 8-3M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
    database:   '<path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
    billing:    '<path d="M2 5h20v14H2zM2 10h20"/>',
    logs:       '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
    lock:       '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>',
    lockAlert:  '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 014-4M12 15v2"/>',
    shield:     '<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M12 7h.01M9 17h6"/>',
    users:      '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>',
    sms:        '<path d="M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zM11 18h2"/>',
    megaphone:  '<path d="M3 11l18-5v12L3 13zM11.6 16.8a3 3 0 01-5.8-1.6"/>',
    card:       '<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/>',
    cloud:      '<path d="M20 17.6A5 5 0 0018 8h-1.26A8 8 0 104 16.25"/>',
    bar:        '<path d="M3 21h18M7 21V11M12 21V4M17 21v-8"/>',
    check:      '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
    chevron:    '<path d="M9 18l6-6-6-6"/>',
    key:        '<circle cx="8" cy="15" r="4"/><path d="M10.5 12.5L20 3M17 6l3 3M14 9l2 2"/>',
    doc:        '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
};

// ── Toast (lightweight, client-side only — mirrors the pattern used on other static pages) ─
const toast = ref('');
let toastTimer = null;
function showToast(msg) {
    toast.value = msg;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.value = ''; }, 2600);
}

// ── Left settings nav — scrolls to the matching card, or opens a "coming soon" ─
const activeNav = ref(props.navItems[0]?.key ?? 'general');
const generalCardEl = ref(null);
const systemCardEl = ref(null);
const securityCardEl = ref(null);
const notificationsCardEl = ref(null);
const emailCardEl = ref(null);
const backupCardEl = ref(null);
const integrationsCardEl = ref(null);
const auditCardEl = ref(null);
const cardEls = { general: generalCardEl, system: systemCardEl, security: securityCardEl, notifications: notificationsCardEl, email: emailCardEl, backup: backupCardEl, integrations: integrationsCardEl, audit: auditCardEl };
const showComingSoon = ref(false);
const comingSoonLabel = ref('');

function selectNav(item) {
    activeNav.value = item.key;
    if (!item.hasCard) {
        comingSoonLabel.value = item.label;
        showComingSoon.value = true;
        return;
    }
    nextTick(() => cardEls[item.key]?.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
}

// ── General Settings form ─────────────────────────────────────────────────────
const generalForm = reactive({ ...props.generalSettings });

function saveGeneral() {
    if (!generalForm.platformName.trim() || !generalForm.adminEmail.trim()) {
        showToast('Platform name and admin email are required.');
        return;
    }
    showToast('General settings saved successfully.');
}

// ── System Preferences ────────────────────────────────────────────────────────
const sysPrefs = reactive(props.systemPreferences.map(p => ({ ...p })));

// ── Security & Authentication ─────────────────────────────────────────────────
const securityItems = reactive(props.security.map(s => ({
    ...s,
    rules: s.rules ? s.rules.map(r => ({ ...r })) : undefined,
    activeSessions: s.activeSessions ? s.activeSessions.map(a => ({ ...a })) : undefined,
    ips: s.ips ? [...s.ips] : undefined,
})));
const showSecurityDetail = ref(false);
const activeSecurity = ref(null);

function openSecurity(item) {
    activeSecurity.value = item;
    showSecurityDetail.value = true;
}

function revokeSession(item, session) {
    item.activeSessions = item.activeSessions.filter(s => s.key !== session.key);
    showToast(`Session on ${session.device} revoked.`);
}

function saveSecurity() {
    showSecurityDetail.value = false;
    showToast(`${activeSecurity.value.title} updated.`);
}

// ── Notifications ─────────────────────────────────────────────────────────────
const notifItems = reactive(props.notifications.map(n => ({ ...n })));

// ── Email Settings ────────────────────────────────────────────────────────────
const testingEmail = ref(false);
function testEmailConfig() {
    testingEmail.value = true;
    setTimeout(() => {
        testingEmail.value = false;
        showToast(`Test email sent to ${props.emailSettings.senderEmail}.`);
    }, 1000);
}

// ── Backup & Restore ──────────────────────────────────────────────────────────
const backupState = reactive({ ...props.backup });
const backingUp = ref(false);

function backupNow() {
    backingUp.value = true;
    setTimeout(() => {
        backingUp.value = false;
        backupState.lastBackupTime = new Date().toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        showToast('Backup completed successfully.');
    }, 1200);
}

const showRetention = ref(false);
function saveRetention() {
    showRetention.value = false;
    showToast(`Backup retention set to ${backupState.retentionDays}.`);
}

const showRestoreConfirm = ref(false);
function confirmRestore() {
    showRestoreConfirm.value = false;
    showToast('System restored from the last backup.');
}

// ── Integrations ───────────────────────────────────────────────────────────────
const integrationItems = reactive(props.integrations.map(i => ({ ...i })));
const showIntegrationDetail = ref(false);
const activeIntegration = ref(null);

function openIntegration(item) {
    activeIntegration.value = item;
    showIntegrationDetail.value = true;
}

function toggleIntegration(item) {
    item.connected = !item.connected;
    showToast(item.connected ? `${item.title} reconnected.` : `${item.title} disconnected.`);
}

const showApiKeys = ref(false);

// ── System Information / Logs ─────────────────────────────────────────────────
const showLogs = ref(false);

const logLevelColor = { info: 'text-info', success: 'text-success', warning: 'text-warning' };
</script>

<template>
    <Head title="Settings" />

    <AdminLayout title="Settings" :breadcrumbs="[{ label: 'Admin' }, { label: 'Settings' }]">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="flex items-center gap-2 text-xl font-bold text-foreground">
                Settings
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg>
            </h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Manage system preferences, configurations and account settings.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[300px_minmax(0,1fr)] lg:items-start">
            <!-- LEFT NAV -->
            <div class="flex flex-col gap-4 lg:sticky lg:top-4">
                <div class="rounded-2xl border border-border bg-admin-surface-card p-3 shadow-card">
                    <button
                        v-for="i in navItems" :key="i.key" type="button" @click="selectNav(i)"
                        class="flex w-full items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-left text-[13.5px] transition-colors"
                        :class="activeNav === i.key ? 'bg-admin-accent/10 font-bold text-admin-accent' : 'font-medium text-foreground/80 hover:bg-muted'"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="flex-none" v-html="icons[i.icon]" />
                        <span class="truncate">{{ i.label }}</span>
                    </button>
                </div>

                <div class="rounded-2xl border border-admin-accent/15 bg-admin-accent/5 p-5">
                    <div class="mb-2.5 flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-[11px] bg-admin-surface-card text-admin-accent shadow-sm">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v-3a8 8 0 0116 0v3"/><path d="M18 19a2 2 0 01-2 2h-2"/><rect x="2" y="13" width="4" height="7" rx="1.5"/><rect x="18" y="13" width="4" height="7" rx="1.5"/></svg>
                        </div>
                        <div class="text-sm font-bold text-foreground">Need Help?</div>
                    </div>
                    <p class="mb-3.5 text-xs leading-relaxed text-muted-foreground">Visit our help center or contact support for assistance.</p>
                    <Link :href="route('admin.support-tickets.index')" class="flex w-full items-center justify-center gap-1.5 rounded-[10px] bg-admin-accent px-3 py-2.5 text-xs font-semibold text-on-gold transition-transform hover:-translate-y-0.5">
                        Go to Help Center
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
                    </Link>
                </div>
            </div>

            <!-- CONTENT GRID -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                <!-- GENERAL SETTINGS (span 2) -->
                <div ref="generalCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card md:col-span-2 xl:col-span-2">
                    <div class="mb-5 flex flex-wrap items-start justify-between gap-3.5">
                        <div>
                            <div class="text-lg font-bold text-foreground">General Settings</div>
                            <div class="mt-0.5 text-[13px] text-muted-foreground">Configure basic system settings and platform preferences.</div>
                        </div>
                        <Button @click="saveGeneral" class="gap-1.5 bg-admin-accent text-on-gold hover:bg-admin-accent/90">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                            Save Changes
                        </Button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Platform Name</Label>
                            <Input v-model="generalForm.platformName" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Admin Email</Label>
                            <Input v-model="generalForm.adminEmail" type="email" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Platform Tagline</Label>
                            <Input v-model="generalForm.tagline" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Company Name</Label>
                            <Input v-model="generalForm.companyName" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Default Timezone</Label>
                            <Select v-model="generalForm.timezone">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in generalSettings.timezoneOptions" :key="t.value" :value="t.value">{{ t.label }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Default Currency</Label>
                            <Select v-model="generalForm.currency">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in generalSettings.currencyOptions" :key="c.value" :value="c.value">{{ c.label }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Date Format</Label>
                            <Select v-model="generalForm.dateFormat">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="d in generalSettings.dateFormatOptions" :key="d.value" :value="d.value">{{ d.label }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-muted-foreground">Time Format</Label>
                            <Select v-model="generalForm.timeFormat">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in generalSettings.timeFormatOptions" :key="t.value" :value="t.value">{{ t.label }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- SYSTEM PREFERENCES -->
                <div ref="systemCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card">
                    <div class="text-lg font-bold text-foreground">System Preferences</div>
                    <div class="mb-4 mt-0.5 text-[13px] text-muted-foreground">Manage system behavior and performance.</div>

                    <div class="flex flex-col gap-4">
                        <div v-for="p in sysPrefs" :key="p.key" class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-[13.5px] font-bold text-foreground">{{ p.title }}</div>
                                <div class="mt-0.5 text-[11.5px] text-muted-foreground">{{ p.desc }}</div>
                            </div>

                            <Select v-if="p.type === 'dropdown'" v-model="p.value">
                                <SelectTrigger class="h-8 w-[110px] flex-none text-xs"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="o in p.options" :key="o" :value="o">{{ o }}</SelectItem>
                                </SelectContent>
                            </Select>

                            <button
                                v-else type="button" role="switch" :aria-checked="p.value" @click="p.value = !p.value"
                                class="relative h-[23px] w-10 flex-none rounded-full transition-colors"
                                :class="p.value ? 'bg-admin-accent' : 'bg-muted-foreground/30'"
                            >
                                <span class="absolute top-0.5 left-0.5 h-[18px] w-[18px] rounded-full bg-white shadow transition-transform" :class="p.value ? 'translate-x-[17px]' : 'translate-x-0'" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECURITY & AUTHENTICATION -->
                <div ref="securityCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card">
                    <div class="text-lg font-bold text-foreground">Security &amp; Authentication</div>
                    <div class="mb-3 mt-0.5 text-[13px] text-muted-foreground">Manage security settings and access control.</div>

                    <div class="flex flex-col">
                        <button
                            v-for="s in securityItems" :key="s.key" type="button" @click="openSecurity(s)"
                            class="flex items-center gap-3 rounded-xl p-2.5 text-left transition-colors hover:bg-muted"
                        >
                            <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: s.bg, color: s.color }">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[s.icon]" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-bold text-foreground">{{ s.title }}</div>
                                <div class="truncate text-[11.5px] text-muted-foreground">{{ s.desc }}</div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="flex-none text-muted-foreground/60" v-html="icons.chevron" />
                        </button>
                    </div>
                </div>

                <!-- NOTIFICATIONS -->
                <div ref="notificationsCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card">
                    <div class="text-lg font-bold text-foreground">Notifications</div>
                    <div class="mb-4 mt-0.5 text-[13px] text-muted-foreground">Configure how and when you receive notifications.</div>

                    <div class="flex flex-col gap-3.5">
                        <div v-for="n in notifItems" :key="n.key" class="flex items-center gap-3">
                            <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: n.iconBg, color: n.iconColor }">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[n.icon]" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-bold text-foreground">{{ n.title }}</div>
                                <div class="truncate text-[11.5px] text-muted-foreground">{{ n.desc }}</div>
                            </div>
                            <button
                                type="button" role="switch" :aria-checked="n.value" @click="n.value = !n.value"
                                class="relative h-[23px] w-10 flex-none rounded-full transition-colors"
                                :class="n.value ? 'bg-admin-accent' : 'bg-muted-foreground/30'"
                            >
                                <span class="absolute top-0.5 left-0.5 h-[18px] w-[18px] rounded-full bg-white shadow transition-transform" :class="n.value ? 'translate-x-[17px]' : 'translate-x-0'" />
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="comingSoonLabel = 'Notification Templates'; showComingSoon = true" class="mt-4 flex w-full items-center justify-between gap-1.5 border-t border-border pt-4 text-xs font-semibold text-admin-accent">
                        Manage Notification Templates
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>

                <!-- EMAIL SETTINGS -->
                <div ref="emailCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card">
                    <div class="text-lg font-bold text-foreground">Email Settings</div>
                    <div class="mb-4 mt-0.5 text-[13px] text-muted-foreground">Configure outgoing email server and preferences.</div>

                    <div class="flex flex-col gap-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">SMTP Server</span>
                            <span class="text-[13px] font-bold text-foreground">{{ emailSettings.smtpServer }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">SMTP Port</span>
                            <span class="text-[13px] font-bold text-foreground">{{ emailSettings.smtpPort }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">Sender Email</span>
                            <span class="text-[13px] font-bold text-foreground">{{ emailSettings.senderEmail }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">Email Encryption</span>
                            <span class="text-[13px] font-bold text-foreground">{{ emailSettings.encryption }}</span>
                        </div>
                    </div>

                    <button type="button" @click="testEmailConfig" :disabled="testingEmail" class="mt-5 flex items-center gap-1.5 border-t border-border pt-4 text-xs font-semibold text-admin-accent disabled:opacity-60">
                        {{ testingEmail ? 'Sending test email…' : 'Test Email Configuration' }}
                        <svg v-if="!testingEmail" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
                    </button>
                </div>

                <!-- BACKUP & RESTORE -->
                <div ref="backupCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card">
                    <div class="text-lg font-bold text-foreground">Backup &amp; Restore</div>
                    <div class="mb-4 mt-0.5 text-[13px] text-muted-foreground">Manage system backups and restore points.</div>

                    <div class="flex items-center gap-3 rounded-[13px] border border-success/25 bg-success/10 p-3.5">
                        <div class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[9px] bg-success/15 text-success">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.check" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[13px] font-bold text-success">{{ backupState.lastBackupStatus }}</div>
                            <div class="text-[11.5px] text-success/70">{{ backupState.lastBackupTime }}</div>
                        </div>
                        <Button type="button" size="sm" variant="outline" @click="backupNow" :disabled="backingUp" class="flex-none gap-1.5 border-success/30 text-success hover:bg-success/10">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 17.6A5 5 0 0018 8h-1.26A8 8 0 104 16.25"/><path d="M8 16l4 4 4-4M12 12v8"/></svg>
                            {{ backingUp ? 'Backing up…' : 'Backup Now' }}
                        </Button>
                    </div>

                    <div class="mt-2 flex flex-col">
                        <div class="flex items-center justify-between gap-3 border-b border-border py-3.5">
                            <div>
                                <div class="text-[13px] font-bold text-foreground">Automatic Backups</div>
                                <div class="text-[11.5px] text-muted-foreground">{{ backupState.automaticSchedule }}</div>
                            </div>
                            <span class="text-[12.5px] font-bold" :class="backupState.automaticEnabled ? 'text-success' : 'text-muted-foreground'">{{ backupState.automaticEnabled ? 'On' : 'Off' }}</span>
                        </div>
                        <button type="button" @click="showRetention = true" class="flex items-center justify-between gap-3 border-b border-border py-3.5 text-left transition-colors hover:bg-muted">
                            <div>
                                <div class="text-[13px] font-bold text-foreground">Backup Retention</div>
                                <div class="text-[11.5px] text-muted-foreground">{{ backupState.retentionDays }}</div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/60" v-html="icons.chevron" />
                        </button>
                        <button type="button" @click="showRestoreConfirm = true" class="flex items-center justify-between gap-3 py-3.5 text-left transition-colors hover:bg-muted">
                            <div>
                                <div class="text-[13px] font-bold text-foreground">Restore System</div>
                                <div class="text-[11.5px] text-muted-foreground">Restore from backup</div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/60" v-html="icons.chevron" />
                        </button>
                    </div>
                </div>

                <!-- INTEGRATIONS -->
                <div ref="integrationsCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card">
                    <div class="text-lg font-bold text-foreground">Integrations</div>
                    <div class="mb-3 mt-0.5 text-[13px] text-muted-foreground">Manage third-party integrations and API connections.</div>

                    <div class="flex flex-col">
                        <button
                            v-for="i in integrationItems" :key="i.key" type="button" @click="openIntegration(i)"
                            class="flex items-center gap-3 rounded-xl p-2.5 text-left transition-colors hover:bg-muted"
                        >
                            <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: i.bg, color: i.color }">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[i.icon]" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-bold text-foreground">{{ i.title }}</div>
                                <div class="flex items-center gap-1.5 text-[11.5px] text-muted-foreground">
                                    <span class="h-1.5 w-1.5 flex-none rounded-full" :class="i.connected ? 'bg-success' : 'bg-muted-foreground/40'" />
                                    {{ i.connected ? 'Connected' : 'Disconnected' }} • {{ i.provider }}
                                </div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="flex-none text-muted-foreground/60" v-html="icons.chevron" />
                        </button>
                    </div>

                    <button type="button" @click="showApiKeys = true" class="mt-3 flex w-full items-center justify-between gap-1.5 border-t border-border pt-4 text-xs font-semibold text-admin-accent">
                        Manage API Keys
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>

                <!-- SYSTEM INFORMATION -->
                <div ref="auditCardEl" class="rounded-2xl border border-border bg-admin-surface-card p-6 shadow-card">
                    <div class="text-lg font-bold text-foreground">System Information</div>
                    <div class="mb-4 mt-0.5 text-[13px] text-muted-foreground">View system information and platform details.</div>

                    <div class="flex flex-col gap-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">Current Version</span>
                            <span class="text-[13px] font-bold text-foreground">{{ systemInfo.version }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">Environment</span>
                            <span class="text-[13px] font-bold text-foreground">{{ systemInfo.environment }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">Server Time</span>
                            <span class="text-[13px] font-bold text-foreground">{{ systemInfo.serverTime }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">Database</span>
                            <span class="text-[13px] font-bold text-foreground">{{ systemInfo.database }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-muted-foreground">Server</span>
                            <span class="text-[13px] font-bold text-foreground">{{ systemInfo.server }}</span>
                        </div>
                    </div>

                    <button type="button" @click="showLogs = true" class="mt-5 flex w-full items-center justify-between gap-1.5 border-t border-border pt-4 text-xs font-semibold text-admin-accent">
                        View System Logs
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast (local, client-side only) -->
        <Transition
            enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0"
        >
            <div v-if="toast" class="fixed bottom-6 right-6 z-[9999] rounded-xl border border-border bg-admin-surface-card px-4 py-3 text-sm font-medium text-foreground shadow-lg">
                {{ toast }}
            </div>
        </Transition>

        <!-- Security detail dialog -->
        <Dialog v-model:open="showSecurityDetail">
            <DialogContent v-if="activeSecurity" class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: activeSecurity.bg, color: activeSecurity.color }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[activeSecurity.icon]" />
                        </div>
                        {{ activeSecurity.title }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 px-6 pb-2">
                    <p class="text-xs text-muted-foreground">{{ activeSecurity.detail }}</p>

                    <!-- Password Policy -->
                    <template v-if="activeSecurity.key === 'password'">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Minimum Length</Label>
                            <Input type="number" min="6" max="32" v-model.number="activeSecurity.minLength" />
                        </div>
                        <div v-for="r in activeSecurity.rules" :key="r.key" class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-foreground">{{ r.label }}</span>
                            <button type="button" role="switch" :aria-checked="r.value" @click="r.value = !r.value" class="relative h-[22px] w-[38px] flex-none rounded-full transition-colors" :class="r.value ? 'bg-admin-accent' : 'bg-muted-foreground/30'">
                                <span class="absolute top-0.5 left-0.5 h-[16px] w-[16px] rounded-full bg-white shadow transition-transform" :class="r.value ? 'translate-x-4' : 'translate-x-0'" />
                            </button>
                        </div>
                    </template>

                    <!-- Two-Factor Authentication -->
                    <template v-else-if="activeSecurity.key === 'twofa'">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-foreground">Enforce 2FA for admin users</span>
                            <button type="button" role="switch" :aria-checked="activeSecurity.value" @click="activeSecurity.value = !activeSecurity.value" class="relative h-[22px] w-[38px] flex-none rounded-full transition-colors" :class="activeSecurity.value ? 'bg-admin-accent' : 'bg-muted-foreground/30'">
                                <span class="absolute top-0.5 left-0.5 h-[16px] w-[16px] rounded-full bg-white shadow transition-transform" :class="activeSecurity.value ? 'translate-x-4' : 'translate-x-0'" />
                            </button>
                        </div>
                    </template>

                    <!-- Login Session Management -->
                    <template v-else-if="activeSecurity.key === 'sessions'">
                        <div class="space-y-1">
                            <div v-for="s in activeSecurity.activeSessions" :key="s.key" class="flex items-center justify-between gap-3 border-b border-border/60 py-2.5 last:border-b-0">
                                <div class="min-w-0">
                                    <div class="truncate text-[13px] font-semibold text-foreground">{{ s.device }} <span v-if="s.current" class="ml-1 text-[10.5px] font-bold text-success">(this device)</span></div>
                                    <div class="truncate text-[11.5px] text-muted-foreground">{{ s.location }} · {{ s.lastActive }}</div>
                                </div>
                                <Button v-if="!s.current" type="button" size="sm" variant="outline" class="flex-none" @click="revokeSession(activeSecurity, s)">Revoke</Button>
                            </div>
                        </div>
                    </template>

                    <!-- IP Whitelist -->
                    <template v-else-if="activeSecurity.key === 'ipWhitelist'">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[13px] text-foreground">Restrict access by IP address</span>
                            <button type="button" role="switch" :aria-checked="activeSecurity.value" @click="activeSecurity.value = !activeSecurity.value" class="relative h-[22px] w-[38px] flex-none rounded-full transition-colors" :class="activeSecurity.value ? 'bg-admin-accent' : 'bg-muted-foreground/30'">
                                <span class="absolute top-0.5 left-0.5 h-[16px] w-[16px] rounded-full bg-white shadow transition-transform" :class="activeSecurity.value ? 'translate-x-4' : 'translate-x-0'" />
                            </button>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">Allowed IP Addresses (one per line)</Label>
                            <Textarea :model-value="activeSecurity.ips.join('\n')" @update:model-value="activeSecurity.ips = $event.split('\n').map(v => v.trim()).filter(Boolean)" rows="3" />
                        </div>
                    </template>

                    <!-- Account Lockout Policy -->
                    <template v-else-if="activeSecurity.key === 'lockout'">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <Label class="text-xs font-medium text-muted-foreground">Failed Attempts</Label>
                                <Input type="number" min="1" max="20" v-model.number="activeSecurity.maxAttempts" />
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-xs font-medium text-muted-foreground">Lockout Duration (min)</Label>
                                <Input type="number" min="1" max="120" v-model.number="activeSecurity.lockoutMinutes" />
                            </div>
                        </div>
                    </template>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showSecurityDetail = false">Cancel</Button>
                    <Button type="button" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90" @click="saveSecurity">Save</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Backup retention dialog -->
        <Dialog v-model:open="showRetention">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Backup Retention</DialogTitle>
                </DialogHeader>
                <div class="space-y-1.5 px-6 pb-2">
                    <Label class="text-xs font-medium text-muted-foreground">Keep backups for</Label>
                    <Select v-model="backupState.retentionDays">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="o in backup.retentionOptions" :key="o" :value="o">{{ o }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showRetention = false">Cancel</Button>
                    <Button type="button" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90" @click="saveRetention">Save</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Restore confirmation dialog -->
        <Dialog v-model:open="showRestoreConfirm">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Restore System?</DialogTitle>
                </DialogHeader>
                <p class="px-6 pb-2 text-sm text-muted-foreground">
                    This will restore the platform to the last backup taken on <span class="font-semibold text-foreground">{{ backupState.lastBackupTime }}</span>. Any changes made after that point will be lost.
                </p>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showRestoreConfirm = false">Cancel</Button>
                    <Button type="button" class="bg-destructive text-destructive-foreground hover:bg-destructive/90" @click="confirmRestore">Restore Now</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Integration detail dialog -->
        <Dialog v-model:open="showIntegrationDetail">
            <DialogContent v-if="activeIntegration" class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: activeIntegration.bg, color: activeIntegration.color }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[activeIntegration.icon]" />
                        </div>
                        {{ activeIntegration.title }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 px-6 pb-2">
                    <Separator />
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Provider</span>
                        <span class="font-semibold text-foreground">{{ activeIntegration.provider }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-muted-foreground">Status</span>
                        <span class="flex items-center gap-1.5 font-semibold" :class="activeIntegration.connected ? 'text-success' : 'text-muted-foreground'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="activeIntegration.connected ? 'bg-success' : 'bg-muted-foreground/40'" />
                            {{ activeIntegration.connected ? 'Connected' : 'Disconnected' }}
                        </span>
                    </div>
                </div>

                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showIntegrationDetail = false">Close</Button>
                    <Button type="button" :class="activeIntegration.connected ? 'bg-destructive hover:bg-destructive/90' : 'bg-admin-accent hover:bg-admin-accent/90'" class="text-on-gold" @click="toggleIntegration(activeIntegration)">
                        {{ activeIntegration.connected ? 'Disconnect' : 'Reconnect' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- API Keys dialog -->
        <Dialog v-model:open="showApiKeys">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>API Keys</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 px-6 pb-4">
                    <div v-for="k in apiKeys" :key="k.key" class="flex items-center justify-between gap-3 rounded-xl border border-border p-3">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-foreground">{{ k.label }}</div>
                            <div class="truncate font-mono text-[11.5px] text-muted-foreground">{{ k.value }}</div>
                        </div>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="flex-none text-muted-foreground" v-html="icons.key" />
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showApiKeys = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- System Logs dialog -->
        <Dialog v-model:open="showLogs">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>System Logs</DialogTitle>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-1 overflow-y-auto px-6 pb-4">
                    <div v-for="l in systemLogs" :key="l.key" class="flex items-start gap-2.5 border-b border-border/60 py-2.5 last:border-b-0">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 flex-none" :class="logLevelColor[l.level]" v-html="icons.doc" />
                        <div class="min-w-0 flex-1">
                            <div class="text-xs leading-snug text-foreground/80">{{ l.message }}</div>
                            <div class="mt-0.5 text-[11px] text-muted-foreground">{{ l.time }}</div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showLogs = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Coming soon dialog (nav categories without a dedicated card yet) -->
        <Dialog v-model:open="showComingSoon">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>{{ comingSoonLabel }}</DialogTitle>
                </DialogHeader>
                <p class="px-6 pb-2 text-sm text-muted-foreground">This settings section is coming soon.</p>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showComingSoon = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
