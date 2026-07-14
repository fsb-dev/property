<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

// ─── FAQ data ─────────────────────────────────────────────────────────────────
const FAQ_GROUPS = [
    {
        id: 'payment',
        label: 'Payment & Installments',
        color: 'rgb(198,161,91)',
        bg: 'rgba(106,77,255,.1)',
        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z',
        faqs: [
            {
                q: 'When is my next installment due?',
                a: 'Your installment schedule is available on the Payments & Installments page. You will also receive an email and SMS reminder 7 days before each due date. If you haven\'t received reminders, please check your registered contact details.',
            },
            {
                q: 'What payment methods are accepted?',
                a: 'We accept bank transfers (BKASH, Nagad, Rocket), direct bank deposit to our designated accounts, and cheques. Credit/debit card payments are available for down payment only. Please share payment proof after each transaction.',
            },
            {
                q: 'What happens if I miss an installment deadline?',
                a: 'A grace period of 15 days applies after each due date. After that, a late fee of 2% per month on the overdue amount is charged. Persistent defaults beyond 3 months may result in agreement review as per your sale deed clauses.',
            },
            {
                q: 'How do I get a payment receipt?',
                a: 'Receipts are automatically uploaded to your Documents page within 3–5 business days of payment confirmation. If a receipt is missing after 5 days, raise a support ticket with your payment reference and transaction screenshot.',
            },
            {
                q: 'Can I pay installments ahead of schedule?',
                a: 'Yes, pre-payment is welcomed. Please contact us before making an advance payment so we can apply it correctly to your schedule. Early full payment qualifies for a 1.5% rebate subject to management approval.',
            },
        ],
    },
    {
        id: 'documents',
        label: 'Documents',
        color: 'hsl(var(--success))',
        bg: 'rgba(22,163,74,.1)',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z',
        faqs: [
            {
                q: 'Why are some of my documents locked/pending?',
                a: 'Certain documents are released at specific milestones — for example, the Title Deed is only issued after full payment and handover. The reason for each lock is shown next to the document. If a document shows "Pending" despite the milestone being met, please raise a ticket.',
            },
            {
                q: 'How do I request a document not listed in my portal?',
                a: 'Use the "Submit a Ticket" form on this page, select the "Documents" category, and describe the document you need. Our team typically responds within 2 business days. Alternatively, ask Sara AI — she can guide you to the right document or escalate the request.',
            },
            {
                q: 'Are my documents legally valid for bank loan submission?',
                a: 'Yes. All documents on this portal are original signed copies issued by the developer. The Booking Agreement, Sale & Purchase Agreement, and Deed of Agreement are all acceptable by major banks in Bangladesh for home loan applications.',
            },
            {
                q: 'Can I download documents on behalf of a co-owner?',
                a: 'Each portal account is tied to the registered buyer. If there is a co-owner, they must have their own account. For joint ownership document requests, please submit a ticket with both owners\' details and we will issue a combined copy.',
            },
        ],
    },
    {
        id: 'construction',
        label: 'Construction',
        color: 'hsl(var(--info))',
        bg: 'rgba(59,130,246,.1)',
        icon: 'M2 20h20M4 20V10l8-6 8 6v10M10 20v-5h4v5',
        faqs: [
            {
                q: 'How often is the construction progress updated?',
                a: 'The Construction Progress page is updated every two weeks. Major phase completions trigger an immediate update and an email notification to all unit holders. If you notice the progress hasn\'t been updated for over 3 weeks, please notify us.',
            },
            {
                q: 'Can I visit the construction site?',
                a: 'Site visits are arranged on the 1st Saturday of every month. Register your interest by submitting a ticket with the subject "Site Visit Request" at least 5 days in advance. Hard hats and closed-toe shoes are mandatory.',
            },
            {
                q: 'What does the handover process look like?',
                a: 'Once construction reaches 100% and RAJUK occupancy clearance is obtained, you will be notified for a pre-handover inspection. After snag-list resolution, the formal handover ceremony takes place where you receive keys, utility meters, and ownership documents.',
            },
            {
                q: 'What if I spot a defect during or after handover?',
                a: 'All units come with a 12-month defect liability period (DLP) starting from handover date. Report any snags via the support ticket system with photos. Structural defects are covered for 3 years as per BNBC guidelines.',
            },
        ],
    },
    {
        id: 'general',
        label: 'General',
        color: 'hsl(var(--warning))',
        bg: 'rgba(245,158,11,.1)',
        icon: 'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01',
        faqs: [
            {
                q: 'How do I update my contact information?',
                a: 'Contact information updates must be submitted via a support ticket for identity verification purposes. Include your current registered phone number and email along with the new details. Updates are processed within 3 business days.',
            },
            {
                q: 'Can I transfer my unit to another person?',
                a: 'Unit transfers are subject to management approval and a transfer fee of 1% of the unit value. Submit a written request via support ticket. Both original buyer and new buyer must be present for the transfer documentation signing.',
            },
            {
                q: 'What are the maintenance charges after handover?',
                a: 'Monthly service charges will be applicable post-handover, covering security, common area maintenance, electricity for common areas, and elevator maintenance. Exact rates will be communicated 2 months before handover.',
            },
            {
                q: 'How do I access the client portal on mobile?',
                a: 'The client portal is fully responsive and works on all modern mobile browsers. Bookmark the URL for quick access. A dedicated mobile app is currently in development and will be available before handover.',
            },
        ],
    },
];

// ─── Ticket form ──────────────────────────────────────────────────────────────
const TICKET_CATEGORIES = [
    'Payment & Installments',
    'Documents',
    'Construction Progress',
    'Handover & Ownership',
    'Technical / Portal Issue',
    'General Enquiry',
    'Other',
];

const ticketForm = ref({
    subject:  '',
    category: '',
    priority: 'normal',
    message:  '',
});
const ticketSubmitted = ref(false);
const submitting      = ref(false);

function submitTicket() {
    if (!ticketForm.value.subject || !ticketForm.value.category || !ticketForm.value.message) return;
    submitting.value = true;
    setTimeout(() => {
        submitting.value  = false;
        ticketSubmitted.value = true;
        ticketForm.value  = { subject: '', category: '', priority: 'normal', message: '' };
        setTimeout(() => { ticketSubmitted.value = false; }, 5000);
    }, 1200);
}

const formValid = computed(() =>
    ticketForm.value.subject.trim() &&
    ticketForm.value.category &&
    ticketForm.value.message.trim().length >= 20
);

// ─── FAQ state ────────────────────────────────────────────────────────────────
const searchQuery  = ref('');
const openFaq      = ref(null);
const activeGroup  = ref('all');

function toggleFaq(id) {
    openFaq.value = openFaq.value === id ? null : id;
}

const filteredGroups = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();
    return FAQ_GROUPS
        .filter(g => activeGroup.value === 'all' || g.id === activeGroup.value)
        .map(g => ({
            ...g,
            faqs: g.faqs.filter(f =>
                !q || f.q.toLowerCase().includes(q) || f.a.toLowerCase().includes(q)
            ),
        }))
        .filter(g => g.faqs.length > 0);
});

const totalFaqs = computed(() => FAQ_GROUPS.reduce((s, g) => s + g.faqs.length, 0));

// ─── My tickets (dummy) ───────────────────────────────────────────────────────
const MY_TICKETS = [
    { id: 'TKT-0041', subject: 'Missing payment receipt for June installment', category: 'Payment & Installments', date: '28 Jun 2026', status: 'resolved',    status_label: 'Resolved' },
    { id: 'TKT-0038', subject: 'Unit floor plan not downloading',               category: 'Documents',             date: '14 Jun 2026', status: 'resolved',    status_label: 'Resolved' },
    { id: 'TKT-0045', subject: 'Request for site visit — July batch',           category: 'Construction Progress', date: '02 Jul 2026', status: 'in_progress', status_label: 'In Progress' },
];

function ticketStatusStyle(s) {
    return {
        resolved:    { bg: 'rgba(22,163,74,.1)',   text: 'hsl(var(--success))' },
        in_progress: { bg: 'rgba(59,130,246,.1)',  text: 'hsl(var(--info))' },
        open:        { bg: 'rgba(245,158,11,.1)',  text: 'hsl(var(--warning))' },
        closed:      { bg: 'rgba(154,154,176,.1)', text: 'hsl(var(--muted-foreground))' },
    }[s] || { bg: 'rgba(106,77,255,.1)', text: 'rgb(198,161,91)' };
}
</script>

<template>
    <ClientLayout>
        <div>

            <!-- Breadcrumb -->
            <nav style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:hsl(var(--muted-foreground)); margin-bottom:20px;">
                <Link :href="route('client.dashboard')" style="color:hsl(var(--muted-foreground)); text-decoration:none;" class="hover:text-foreground">Home</Link>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                <span style="color:hsl(var(--foreground)); font-weight:600;">Support & Help</span>
            </nav>

            <!-- Page title -->
            <div style="margin-bottom:22px;">
                <h1 style="font-size:22px; font-weight:800; color:hsl(var(--foreground)); letter-spacing:-0.02em; margin:0 0 4px;">Support & Help</h1>
                <p style="font-size:13.5px; color:hsl(var(--muted-foreground)); margin:0;">Get answers instantly or reach our team — we're here to help.</p>
            </div>

            <!-- Body -->
            <div style="display:flex; gap:20px; align-items:flex-start;">

                <!-- Main column -->
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:18px;">

                    <!-- Quick contact cards -->
                    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;" class="contact-grid">
                        <!-- WhatsApp -->
                        <a href="https://wa.me/8801700000000" target="_blank"
                            style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:16px; text-decoration:none; display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center; transition:box-shadow .18s; cursor:pointer;"
                            class="hover:shadow-md">
                            <div style="width:44px; height:44px; border-radius:13px; background:rgba(37,211,102,.12); display:flex; align-items:center; justify-content:center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#25d366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.999 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2.004 22l4.952-1.398A9.953 9.953 0 0 0 12 22c5.523 0 10-4.477 10-10S17.522 2 12 2h-.001z"/></svg>
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:2px;">WhatsApp</div>
                                <div style="font-size:11.5px; color:hsl(var(--muted-foreground)); line-height:1.4;">Instant reply<br>Mon–Sat 9am–7pm</div>
                            </div>
                        </a>

                        <!-- Phone -->
                        <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:16px; display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center; cursor:pointer; transition:box-shadow .18s;" class="hover:shadow-md">
                            <div style="width:44px; height:44px; border-radius:13px; background:rgba(59,130,246,.1); display:flex; align-items:center; justify-content:center;">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--info))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6.08 6.08l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:2px;">Call Us</div>
                                <div style="font-size:11.5px; color:hsl(var(--muted-foreground)); line-height:1.4;">+880 1700 000000<br>Mon–Fri 9am–6pm</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:16px; display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center; cursor:pointer; transition:box-shadow .18s;" class="hover:shadow-md">
                            <div style="width:44px; height:44px; border-radius:13px; background:rgba(245,158,11,.1); display:flex; align-items:center; justify-content:center;">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--warning))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:2px;">Email</div>
                                <div style="font-size:11.5px; color:hsl(var(--muted-foreground)); line-height:1.4;">support@lakeview.com<br>Reply within 24h</div>
                            </div>
                        </div>

                        <!-- AI Advisor -->
                        <Link :href="route('client.ai.advisor')"
                            style="background:linear-gradient(135deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-deep))); border:none; border-radius:16px; padding:16px; display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center; cursor:pointer; text-decoration:none; transition:box-shadow .18s; box-shadow:0 6px 18px -6px rgba(81,50,224,.45);"
                            class="hover:shadow-xl">
                            <div style="width:44px; height:44px; border-radius:13px; background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--card))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7v5c0 5 3.5 8 8 9 4.5-1 8-4 8-9V7z"/><path d="M9.5 12l1.8 1.8L15 10"/></svg>
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:hsl(var(--card)); margin-bottom:2px;">Sara AI</div>
                                <div style="font-size:11.5px; color:rgba(255,255,255,.7); line-height:1.4;">Instant answers<br>Available 24/7</div>
                            </div>
                        </Link>
                    </div>

                    <!-- Submit a ticket -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:18px; overflow:hidden;">
                        <!-- Header -->
                        <div style="padding:18px 22px 16px; border-bottom:1px solid hsl(var(--muted)); display:flex; align-items:center; gap:12px;">
                            <div style="width:36px; height:36px; border-radius:10px; background:rgba(106,77,255,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:800; color:hsl(var(--foreground));">Submit a Ticket</div>
                                <div style="font-size:12px; color:hsl(var(--muted-foreground)); margin-top:1px;">Our team responds within 2 business days</div>
                            </div>
                        </div>

                        <!-- Success banner -->
                        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                            <div v-if="ticketSubmitted" style="margin:16px 22px; background:rgba(22,163,74,.08); border:1px solid rgba(22,163,74,.2); border-radius:12px; padding:12px 16px; display:flex; align-items:center; gap:10px;">
                                <div style="width:28px; height:28px; border-radius:8px; background:hsl(var(--success)); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--card))" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:13px; font-weight:700; color:hsl(var(--success));">Ticket submitted successfully!</div>
                                    <div style="font-size:12px; color:hsl(var(--success)); margin-top:2px;">You'll receive a confirmation email shortly. Reference number: TKT-0046</div>
                                </div>
                            </div>
                        </Transition>

                        <!-- Form -->
                        <div style="padding:18px 22px 22px; display:flex; flex-direction:column; gap:14px;">
                            <!-- Subject + Category row -->
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;" class="form-two-col">
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; color:hsl(var(--muted-foreground)); margin-bottom:6px;">Subject <span style="color:hsl(var(--destructive));">*</span></label>
                                    <input
                                        v-model="ticketForm.subject"
                                        placeholder="Briefly describe your issue"
                                        style="width:100%; height:40px; padding:0 13px; border:1.5px solid hsl(var(--border)); border-radius:10px; outline:none; font-family:'Plus Jakarta Sans',system-ui,sans-serif; font-size:13px; color:hsl(var(--foreground)); background:hsl(var(--background)); box-sizing:border-box; transition:border-color .15s;"
                                        class="focus:border-[rgb(var(--hv-gold))]"
                                    />
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:700; color:hsl(var(--muted-foreground)); margin-bottom:6px;">Category <span style="color:hsl(var(--destructive));">*</span></label>
                                    <select
                                        v-model="ticketForm.category"
                                        style="width:100%; height:40px; padding:0 13px; border:1.5px solid hsl(var(--border)); border-radius:10px; outline:none; font-family:'Plus Jakarta Sans',system-ui,sans-serif; font-size:13px; color:hsl(var(--foreground)); background:hsl(var(--background)); box-sizing:border-box; cursor:pointer; transition:border-color .15s; appearance:none;"
                                        class="focus:border-[rgb(var(--hv-gold))]"
                                    >
                                        <option value="" disabled>Select a category</option>
                                        <option v-for="cat in TICKET_CATEGORIES" :key="cat" :value="cat">{{ cat }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:hsl(var(--muted-foreground)); margin-bottom:8px;">Priority</label>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <button
                                        v-for="p in [{ id:'low', label:'Low', color:'hsl(var(--success))' }, { id:'normal', label:'Normal', color:'hsl(var(--info))' }, { id:'urgent', label:'Urgent', color:'hsl(var(--destructive))' }]"
                                        :key="p.id"
                                        @click="ticketForm.priority = p.id"
                                        :style="ticketForm.priority === p.id
                                            ? `background:${p.color}15; border-color:${p.color}; color:${p.color};`
                                            : 'background:hsl(var(--card)); border-color:hsl(var(--border)); color:hsl(var(--muted-foreground));'"
                                        style="padding:6px 16px; border-radius:999px; font-size:12px; font-weight:700; border:1.5px solid; cursor:pointer; font-family:inherit; transition:all .15s;"
                                    >{{ p.label }}</button>
                                </div>
                            </div>

                            <!-- Message -->
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:hsl(var(--muted-foreground)); margin-bottom:6px;">Message <span style="color:hsl(var(--destructive));">*</span></label>
                                <textarea
                                    v-model="ticketForm.message"
                                    placeholder="Describe your issue in detail (minimum 20 characters)..."
                                    rows="5"
                                    style="width:100%; padding:12px 13px; border:1.5px solid hsl(var(--border)); border-radius:10px; outline:none; font-family:'Plus Jakarta Sans',system-ui,sans-serif; font-size:13px; color:hsl(var(--foreground)); background:hsl(var(--background)); resize:vertical; box-sizing:border-box; line-height:1.6; transition:border-color .15s;"
                                    class="focus:border-[rgb(var(--hv-gold))]"
                                ></textarea>
                                <div style="font-size:11.5px; color:hsl(var(--muted-foreground)); margin-top:4px; text-align:right;">
                                    {{ ticketForm.message.length }} characters
                                    <span v-if="ticketForm.message.length > 0 && ticketForm.message.length < 20" style="color:hsl(var(--destructive));"> — min 20 required</span>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:12px; padding-top:4px;">
                                <button
                                    @click="submitTicket"
                                    :disabled="!formValid || submitting"
                                    :style="formValid && !submitting
                                        ? 'background:linear-gradient(100deg,rgb(var(--hv-gold)),rgb(var(--hv-gold-deep))); color:hsl(var(--card)); box-shadow:0 6px 16px -4px rgba(81,50,224,.45); cursor:pointer;'
                                        : 'background:hsl(var(--muted)); color:hsl(var(--muted-foreground)); cursor:not-allowed;'"
                                    style="display:inline-flex; align-items:center; gap:8px; padding:10px 24px; border-radius:11px; font-size:13.5px; font-weight:700; border:none; font-family:inherit; transition:all .18s;"
                                >
                                    <svg v-if="submitting" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                    <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    {{ submitting ? 'Submitting…' : 'Submit Ticket' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ section -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:18px; overflow:hidden;">
                        <!-- Header + search -->
                        <div style="padding:18px 22px 16px; border-bottom:1px solid hsl(var(--muted));">
                            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(245,158,11,.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--warning))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    </div>
                                    <div>
                                        <div style="font-size:14px; font-weight:800; color:hsl(var(--foreground));">Frequently Asked Questions</div>
                                        <div style="font-size:12px; color:hsl(var(--muted-foreground)); margin-top:1px;">{{ totalFaqs }} questions across 4 topics</div>
                                    </div>
                                </div>
                                <!-- Search -->
                                <div style="position:relative; width:220px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted-foreground))" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:11px; top:50%; transform:translateY(-50%);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
                                    <input
                                        v-model="searchQuery"
                                        placeholder="Search FAQs…"
                                        style="width:100%; height:36px; padding:0 11px 0 32px; border:1.5px solid hsl(var(--border)); border-radius:9px; outline:none; font-family:'Plus Jakarta Sans',system-ui,sans-serif; font-size:12.5px; color:hsl(var(--foreground)); background:hsl(var(--background)); box-sizing:border-box; transition:border-color .15s;"
                                        class="focus:border-[rgb(var(--hv-gold))]"
                                    />
                                </div>
                            </div>

                            <!-- Category tabs -->
                            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:14px;">
                                <button
                                    v-for="tab in [{ id:'all', label:'All Topics' }, ...FAQ_GROUPS.map(g => ({ id: g.id, label: g.label }))]"
                                    :key="tab.id"
                                    @click="activeGroup = tab.id"
                                    :style="activeGroup === tab.id
                                        ? 'background:rgb(var(--hv-gold)); color:hsl(var(--card)); border-color:rgb(var(--brand-text));'
                                        : 'background:hsl(var(--card)); color:hsl(var(--muted-foreground)); border-color:hsl(var(--border));'"
                                    style="padding:5px 13px; border-radius:999px; font-size:12px; font-weight:600; border:1.5px solid; cursor:pointer; font-family:inherit; transition:all .15s;"
                                >{{ tab.label }}</button>
                            </div>
                        </div>

                        <!-- FAQ accordion -->
                        <div style="padding:14px 22px 20px; display:flex; flex-direction:column; gap:12px;">
                            <template v-if="filteredGroups.length > 0">
                                <div v-for="group in filteredGroups" :key="group.id">
                                    <!-- Group label -->
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                        <div style="width:6px; height:6px; border-radius:50%;" :style="{ background: group.color }"></div>
                                        <span style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;"
                                            :style="{ color: group.color }">{{ group.label }}</span>
                                    </div>
                                    <!-- Questions -->
                                    <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:8px;">
                                        <div
                                            v-for="(faq, fi) in group.faqs"
                                            :key="group.id + '-' + fi"
                                            style="border:1.5px solid hsl(var(--border)); border-radius:12px; overflow:hidden; transition:border-color .15s;"
                                            :style="openFaq === group.id + fi ? 'border-color:rgb(var(--hv-gold-bright));' : ''"
                                        >
                                            <!-- Question row -->
                                            <button
                                                @click="toggleFaq(group.id + fi)"
                                                style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:13px 16px; background:transparent; border:none; cursor:pointer; font-family:inherit; text-align:left;"
                                            >
                                                <span style="font-size:13.5px; font-weight:600; color:hsl(var(--foreground)); line-height:1.4;">{{ faq.q }}</span>
                                                <svg
                                                    width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="hsl(var(--muted-foreground))" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                                    style="flex-shrink:0; transition:transform .2s;"
                                                    :style="openFaq === group.id + fi ? 'transform:rotate(180deg); stroke:rgb(var(--hv-gold));' : ''"
                                                >
                                                    <path d="M6 9l6 6 6-6"/>
                                                </svg>
                                            </button>
                                            <!-- Answer -->
                                            <Transition
                                                enter-active-class="transition-all duration-200 ease-out"
                                                enter-from-class="opacity-0"
                                                enter-to-class="opacity-100"
                                                leave-active-class="transition-all duration-150"
                                                leave-from-class="opacity-100"
                                                leave-to-class="opacity-0"
                                            >
                                                <div v-if="openFaq === group.id + fi"
                                                    style="padding:0 16px 14px; border-top:1px solid hsl(var(--muted));">
                                                    <p style="font-size:13px; color:hsl(var(--muted-foreground)); line-height:1.7; margin:10px 0 0;">{{ faq.a }}</p>
                                                </div>
                                            </Transition>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <!-- Empty state -->
                            <div v-else style="text-align:center; padding:32px 0;">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(198,161,91,0.08)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
                                <div style="font-size:13.5px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:5px;">No results found</div>
                                <div style="font-size:13px; color:hsl(var(--muted-foreground)); margin-bottom:14px;">Try different keywords or browse all topics.</div>
                                <button @click="searchQuery=''; activeGroup='all'" style="padding:7px 18px; border-radius:9px; background:hsl(var(--muted)); border:1px solid hsl(var(--border)); font-size:13px; font-weight:600; color:rgb(var(--brand-text)); cursor:pointer; font-family:inherit;">Clear search</button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right rail -->
                <div style="width:284px; flex-shrink:0; display:flex; flex-direction:column; gap:14px;" class="support-rail">

                    <!-- My tickets -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; overflow:hidden;">
                        <div style="padding:14px 18px 12px; border-bottom:1px solid hsl(var(--muted)); display:flex; align-items:center; justify-content:space-between;">
                            <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground));">My Tickets</div>
                            <span style="font-size:11.5px; font-weight:700; padding:3px 9px; border-radius:999px; background:rgba(106,77,255,.1); color:rgb(var(--brand-text));">{{ MY_TICKETS.length }}</span>
                        </div>
                        <div>
                            <div
                                v-for="(ticket, idx) in MY_TICKETS" :key="ticket.id"
                                style="padding:12px 18px; transition:background .15s;"
                                :style="idx < MY_TICKETS.length - 1 ? 'border-bottom:1px solid hsl(var(--muted));' : ''"
                                class="hover:bg-[hsl(var(--background))]"
                            >
                                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:5px;">
                                    <span style="font-size:12.5px; font-weight:600; color:hsl(var(--foreground)); line-height:1.4; flex:1;">{{ ticket.subject }}</span>
                                    <span
                                        :style="{ background: ticketStatusStyle(ticket.status).bg, color: ticketStatusStyle(ticket.status).text }"
                                        style="font-size:10.5px; font-weight:700; padding:2px 7px; border-radius:999px; flex-shrink:0; white-space:nowrap;"
                                    >{{ ticket.status_label }}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:11px; font-weight:700; color:hsl(var(--muted-foreground));">{{ ticket.id }}</span>
                                    <span style="font-size:11px; color:hsl(var(--muted-foreground));">·</span>
                                    <span style="font-size:11px; color:hsl(var(--muted-foreground));">{{ ticket.date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Office hours -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:18px;">
                        <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="rgb(var(--hv-gold))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Office Hours
                        </div>
                        <div style="display:flex; flex-direction:column; gap:0;">
                            <div v-for="row in [
                                { day: 'Sunday – Thursday', time: '9:00 AM – 6:00 PM', open: true },
                                { day: 'Saturday',          time: '10:00 AM – 4:00 PM', open: true },
                                { day: 'Friday',            time: 'Closed',             open: false },
                            ]" :key="row.day"
                                style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid hsl(var(--muted));">
                                <span style="font-size:12px; color:hsl(var(--muted-foreground)); font-weight:500;">{{ row.day }}</span>
                                <span style="font-size:12px; font-weight:700;"
                                    :style="row.open ? 'color:hsl(var(--success));' : 'color:hsl(var(--destructive));'">{{ row.time }}</span>
                            </div>
                        </div>
                        <div style="margin-top:12px; padding:10px 12px; background:rgba(198,161,91,0.08); border-radius:10px; display:flex; align-items:center; gap:8px;">
                            <div style="width:7px; height:7px; border-radius:50%; background:hsl(var(--success)); flex-shrink:0; box-shadow:0 0 0 3px rgba(22,163,74,.2);"></div>
                            <span style="font-size:12px; font-weight:600; color:hsl(var(--muted-foreground));">Support is currently <strong style="color:hsl(var(--success));">open</strong></span>
                        </div>
                    </div>

                    <!-- Escalation -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:18px;">
                        <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:4px;">Escalation Contact</div>
                        <div style="font-size:12px; color:hsl(var(--muted-foreground)); margin-bottom:14px;">For urgent or unresolved issues beyond 5 business days</div>
                        <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(198,161,91,0.08); border:1px solid hsl(var(--muted)); border-radius:11px;">
                            <div style="width:36px; height:36px; border-radius:9px; background:linear-gradient(135deg,rgb(var(--hv-gold-bright)),rgb(var(--hv-gold))); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; font-weight:800; color:hsl(var(--card));">SR</div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground));">Salma Rahman</div>
                                <div style="font-size:11.5px; color:hsl(var(--muted-foreground));">Client Relations Manager</div>
                                <div style="font-size:11.5px; color:rgb(var(--brand-text)); margin-top:2px; font-weight:600;">salma@lakeview.com</div>
                            </div>
                        </div>
                    </div>

                    <!-- Useful links -->
                    <div style="background:hsl(var(--card)); border:1px solid hsl(var(--border)); border-radius:16px; padding:18px;">
                        <div style="font-size:13px; font-weight:700; color:hsl(var(--foreground)); margin-bottom:12px;">Quick Links</div>
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <Link
                                v-for="link in [
                                    { label: 'View Payment Schedule',  href: route('client.payments') },
                                    { label: 'Download Documents',     href: route('client.documents') },
                                    { label: 'Construction Progress',  href: route('client.construction') },
                                    { label: 'Ask Sara AI',            href: route('client.ai.advisor') },
                                ]" :key="link.label"
                                :href="link.href"
                                style="display:flex; align-items:center; justify-content:space-between; padding:9px 12px; border-radius:9px; font-size:13px; font-weight:600; color:hsl(var(--foreground)); text-decoration:none; transition:background .15s;"
                                class="hover:bg-[hsl(var(--muted))] hover:text-[rgb(var(--hv-gold))]"
                            >
                                {{ link.label }}
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 1100px) {
    .support-rail { display: none !important; }
}
@media (max-width: 860px) {
    .contact-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .form-two-col { grid-template-columns: 1fr !important; }
}
@media (max-width: 480px) {
    .contact-grid { grid-template-columns: 1fr 1fr !important; }
}
</style>
