<script setup>
import { ref, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

// ─── Static property context ──────────────────────────────────────────────────
const PROPERTY = {
    name:             'Lake View Residence',
    unit:             'A-702',
    floor:            7,
    block:            'A',
    type:             '3 Bed Apartment',
    size:             '1,850 sft',
    status:           'Purchased',
    handover:         'Dec 2026',
    total_value:      18500000,
    total_paid:       6660000,
    outstanding:      11840000,
    paid_pct:         36,
    construction_pct: 72,
};

// ─── Quick questions ──────────────────────────────────────────────────────────
const QUICK_QUESTIONS = [
    { label: 'How much do I still owe?',               icon: '💰', bg: '#e6f7ed' },
    { label: 'When is my next payment?',               icon: '📅', bg: '#e6effd' },
    { label: 'Show my payment history',                icon: '📄', bg: '#efeafc' },
    { label: 'How much if I pay extra BDT 500,000?',   icon: '📊', bg: '#fdefe0' },
    { label: 'Show my unit details',                   icon: '🏢', bg: '#e6effd' },
    { label: 'Show construction progress',             icon: '🏗️', bg: '#e6f7ed' },
    { label: 'Nearby schools and hospitals?',          icon: '📍', bg: '#efeafc' },
    { label: 'What is my estimated ROI?',              icon: '📈', bg: '#fdefe0' },
];

// ─── Chat state ───────────────────────────────────────────────────────────────
const messages  = ref([
    { id: 1, role: 'ai',   text: 'Hello! 👋\nHow can I help you with your property today?', time: '10:30 AM', card: null },
    { id: 2, role: 'user', text: 'How much do I still owe on my unit?',                      time: '10:30 AM', card: null },
    { id: 3, role: 'ai',   text: 'Here\'s your outstanding balance:',                         time: '10:31 AM', card: 'balance' },
]);
const inputText = ref('');
const isTyping  = ref(false);
const scrollRef = ref(null);
let   nextId    = 4;

// ─── AI keyword engine ────────────────────────────────────────────────────────
function answerFor(q) {
    const t = q.toLowerCase();
    if (t.includes('owe') || t.includes('balance') || t.includes('outstand'))
        return 'Your current outstanding balance is BDT 11,840,000 — 64% of your total property value of BDT 18,500,000. You\'ve paid BDT 6,660,000 so far. Your next installment of BDT 75,000 is due on 15 Aug 2026.';
    if (t.includes('next') || t.includes('installment') || t.includes('due') || (t.includes('when') && t.includes('pay')))
        return 'Your next installment of BDT 75,000 is due on 15 Aug 2026 — that\'s 41 days away. All previous installments are on schedule. Shall I set up a reminder for you?';
    if (t.includes('history') || t.includes('previous') || t.includes('past'))
        return 'You\'ve completed 12 installments of BDT 75,000 each. Most recent: 15 Jul 2026 via bKash. All payments are on time — great discipline! 🎉';
    if (t.includes('extra') || t.includes('500,000') || t.includes('500000') || t.includes('save'))
        return 'Paying an extra BDT 500,000 today would reduce your balance to BDT 11,340,000, save you approximately BDT 245,000 in interest, and shorten your payment tenure by 6 months. Head to the Mortgage Analyzer to run full scenarios.';
    if (t.includes('unit') || t.includes('detail') || t.includes('worth') || t.includes('value') || t.includes('apartment'))
        return 'Unit A-702 is a 3-bed, 1,850 sft apartment on the 7th floor, Block A. It has 2 bathrooms and a lake-facing balcony. Current estimated market value: BDT 21,200,000 — a gain of BDT 2,700,000 (14.6%) since your purchase. 📈';
    if (t.includes('construction') || t.includes('progress') || t.includes('build') || t.includes('handover'))
        return 'Construction is 72% complete and on track for December 2026 handover. Foundation, structural works, brickwork, and MEP are all 100% done. Finishing & fixtures is currently active at 45%. You\'re in the home stretch!';
    if (t.includes('school') || t.includes('hospital') || t.includes('nearby') || t.includes('clinic') || t.includes('amenity'))
        return 'Within 5 km of your property there are 12 schools & colleges, 7 hospitals, 2 upcoming metro stations, 5 parks, and 4 shopping malls. The area infrastructure score is 87/100 — an excellent zone for families and investment alike.';
    if (t.includes('roi') || t.includes('invest') || t.includes('return') || t.includes('gain'))
        return 'Based on your purchase at BDT 18.5M and Gulshan\'s 9.2% annual appreciation rate, your projected value by 2030 is BDT 26.3M. Gross rental yield: 3.56%, net yield: 2.86%. Overall investment score: 87/100 — Excellent Zone. 🏆';
    if (t.includes('mortgage') || t.includes('loan') || t.includes('bank') || t.includes('financ') || t.includes('interest'))
        return 'Among our partner banks, Islami Bank currently offers the lowest home loan rate at 7.95% for up to 20 years with a 0.5% processing fee. Want me to calculate your estimated monthly EMI based on your unit\'s remaining balance?';
    if (t.includes('future') || t.includes('area') || t.includes('develop') || t.includes('community') || t.includes('gulshan'))
        return 'Gulshan is projected to grow ~75% in property value over the next 10 years. A Metro Line 6 extension (2027), a 6-lane road widening (2026), and Gulshan Central Mall (2026) are all currently under construction nearby. Great timing for your investment!';
    if (t.includes('document') || t.includes('agreement') || t.includes('deed') || t.includes('certificate'))
        return 'You have 4 documents on file: Sale Agreement (signed ✅), Booking Receipt (available ✅), Floor Plan (available ✅), and Title Deed Transfer (pending ⏳ — available after handover). Head to Documents to download them.';
    if (t.includes('support') || t.includes('complaint') || t.includes('help') || t.includes('issue') || t.includes('problem'))
        return 'I\'ve noted your concern. You can raise a formal support ticket via the Support & Help page and our team will respond within 24 hours. Alternatively, call +880 1700-000000 (Mon–Sat, 9 AM–6 PM).';
    return 'Great question! I can assist with payments, mortgage financing, construction progress, investment analysis, community insights, and more — all specific to your property at Lake View Residence (Unit A-702). Could you tell me a bit more about what you need?';
}

// ─── Send & scroll helpers ────────────────────────────────────────────────────
async function scrollBottom() {
    await nextTick();
    if (scrollRef.value) scrollRef.value.scrollTop = scrollRef.value.scrollHeight;
}

async function sendMessage(text) {
    const val = (text ?? inputText.value).trim();
    if (!val || isTyping.value) return;
    inputText.value = '';

    messages.value.push({ id: nextId++, role: 'user', text: val, time: nowStr(), card: null });
    await scrollBottom();

    isTyping.value = true;
    await scrollBottom();

    const delay = 900 + Math.random() * 600;
    setTimeout(async () => {
        isTyping.value = false;
        messages.value.push({ id: nextId++, role: 'ai', text: answerFor(val), time: nowStr(), card: null });
        await scrollBottom();
    }, delay);
}

function onKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function nowStr() {
    return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function fmtBDT(n) { return 'BDT ' + n.toLocaleString('en-BD'); }

// Construction steps
const STEPS = [
    { label: 'Foundation', done: true  },
    { label: 'Structure',  done: true  },
    { label: 'Exterior',   done: true  },
    { label: 'Interior',   done: false, active: true },
    { label: 'Handover',   done: false, active: false },
];
</script>

<template>
    <ClientLayout>
        <div style="display:flex; flex-direction:column; gap:0;">

            <!-- Breadcrumb + title row -->
            <div style="margin-bottom:18px;">
                <nav style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#9a9ab0; margin-bottom:12px;">
                    <Link :href="route('client.dashboard')" style="color:#9a9ab0; text-decoration:none;" class="hover:text-foreground">Home</Link>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    <span style="color:#3d3d55; font-weight:600;">AI Property Advisor</span>
                </nav>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                    <div>
                        <h1 style="font-size:22px; font-weight:800; color:#16162a; letter-spacing:-0.02em; margin:0 0 4px;">AI Property Advisor</h1>
                        <p style="font-size:13.5px; color:#7a7a90; margin:0;">Your intelligent assistant for all property questions, payments, and guidance.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:7px; padding:7px 14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:999px;">
                        <span style="width:8px; height:8px; border-radius:50%; background:#16a34a; animation:sarapulse 2s ease-in-out infinite;"></span>
                        <span style="font-size:12.5px; font-weight:700; color:#16a34a;">Sara AI · Online</span>
                    </div>
                </div>
            </div>

            <!-- 3-column layout -->
            <div style="display:flex; gap:16px; align-items:flex-start;">

                <!-- ── Left panel ─────────────────────────────────────────── -->
                <div style="width:272px; flex-shrink:0; display:flex; flex-direction:column; gap:14px;" class="ai-left">

                    <!-- Quick Questions -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:18px; padding:18px;">
                        <div style="font-size:14.5px; font-weight:700; color:#16162a; margin-bottom:3px;">Quick Questions</div>
                        <div style="font-size:12px; color:#a4a4b4; font-weight:500; margin-bottom:13px;">Tap to get instant answers</div>
                        <div style="display:flex; flex-direction:column; gap:7px;">
                            <button
                                v-for="q in QUICK_QUESTIONS" :key="q.label"
                                @click="sendMessage(q.label)"
                                style="display:flex; align-items:center; gap:10px; width:100%; text-align:left; padding:10px 11px; border:1px solid #f0f0f5; border-radius:11px; background:#fff; cursor:pointer; font-family:inherit; transition:all .15s;"
                                class="hover:bg-[#faf9fd] hover:border-[#e6e1fb]"
                            >
                                <span style="width:30px; height:30px; flex-shrink:0; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px;" :style="{ background: q.bg }">{{ q.icon }}</span>
                                <span style="font-size:12px; font-weight:600; color:#16162a; line-height:1.35;">{{ q.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Language + Sara card -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:18px; padding:18px;">
                        <div style="font-size:13.5px; font-weight:700; color:#16162a; margin-bottom:12px;">Language</div>
                        <div style="display:flex; align-items:center; gap:9px; height:40px; padding:0 13px; border:1px solid #ededf3; border-radius:10px; font-size:13px; font-weight:600; color:#16162a; cursor:pointer; background:#faf9fd;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6a4dff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18"/></svg>
                            English (EN)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b6b6c4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:auto;"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                        <div style="margin-top:16px; padding-top:16px; border-top:1px solid #f0f0f5;">
                            <div style="font-size:13px; font-weight:700; color:#16162a; margin-bottom:12px;">AI Assistant</div>
                            <div style="display:flex; align-items:center; gap:11px;">
                                <div style="width:44px; height:44px; flex-shrink:0; border-radius:50%; background:linear-gradient(135deg,#9a85ff,#6a4dff); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:16px;">S</div>
                                <div>
                                    <div style="font-size:14px; font-weight:800; color:#16162a;">Sara AI</div>
                                    <div style="display:flex; align-items:center; gap:5px; font-size:11.5px; color:#16a34a; font-weight:600; margin-top:2px;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#16a34a;"></span> Online
                                    </div>
                                </div>
                            </div>
                            <button style="margin-top:12px; width:100%; display:flex; align-items:center; justify-content:center; gap:8px; border:1px solid #e6e1fb; background:#f6f3ff; color:#6a4dff; cursor:pointer; font-family:inherit; font-size:12.5px; font-weight:700; padding:10px; border-radius:10px;" class="hover:bg-[#efeafc]">
                                Change Assistant
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- ── Center: Chat ───────────────────────────────────────── -->
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; background:#fff; border:1px solid #ededf3; border-radius:18px; overflow:hidden;" class="ai-chat">

                    <!-- Sara banner -->
                    <div style="position:relative; overflow:hidden; display:flex; align-items:center; gap:16px; padding:18px 22px; background:linear-gradient(110deg,#c5b5ff,#a895f0); flex-shrink:0;">
                        <div style="width:62px; height:62px; flex-shrink:0; border-radius:50%; background:linear-gradient(135deg,#7b63ff,#4f33d6); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:24px; border:3px solid rgba(255,255,255,.55);">S</div>
                        <div style="position:relative; z-index:1;">
                            <div style="font-size:20px; font-weight:800; color:#241a5c; letter-spacing:-0.02em;">Sara AI</div>
                            <div style="font-size:13px; color:#3a2a7a; font-weight:600; margin-top:2px;">Your AI Property Advisor</div>
                            <div style="margin-top:8px;">
                                <span style="font-size:11px; font-weight:700; color:#3a2a7a; background:rgba(255,255,255,.5); padding:4px 12px; border-radius:20px;">Smart · Reliable · 24/7</span>
                            </div>
                        </div>
                        <!-- decorative bars -->
                        <div style="position:absolute; right:24px; bottom:0; display:flex; align-items:flex-end; gap:4px; opacity:.25;">
                            <div style="width:8px; height:28px; border-radius:2px; background:#241a5c;"></div>
                            <div style="width:8px; height:44px; border-radius:2px; background:#241a5c;"></div>
                            <div style="width:8px; height:20px; border-radius:2px; background:#241a5c;"></div>
                            <div style="width:8px; height:36px; border-radius:2px; background:#241a5c;"></div>
                            <div style="width:8px; height:26px; border-radius:2px; background:#241a5c;"></div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div ref="scrollRef" style="flex:1; overflow-y:auto; padding:20px 22px; display:flex; flex-direction:column; gap:16px; min-height:380px; max-height:480px; scrollbar-width:thin;">

                        <template v-for="msg in messages" :key="msg.id">

                            <!-- AI message -->
                            <div v-if="msg.role === 'ai'" style="display:flex; gap:10px; align-items:flex-start; max-width:82%;">
                                <div style="width:30px; height:30px; flex-shrink:0; border-radius:50%; background:linear-gradient(135deg,#9a85ff,#6a4dff); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:12px; margin-top:2px;">S</div>
                                <div style="flex:1;">
                                    <!-- Balance card -->
                                    <div v-if="msg.card === 'balance'" style="background:#fff; border:1px solid #ededf3; border-radius:6px 16px 16px 16px; padding:16px 18px; box-shadow:0 1px 4px rgba(16,24,40,.05);">
                                        <div style="font-size:13.5px; font-weight:700; color:#16162a; margin-bottom:12px;">{{ msg.text }}</div>
                                        <div style="display:flex; flex-direction:column;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f4f4f8;">
                                                <span style="font-size:13px; color:#7a7a90;">Total Property Value</span>
                                                <span style="font-size:13px; font-weight:700; color:#16162a;">{{ fmtBDT(PROPERTY.total_value) }}</span>
                                            </div>
                                            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f4f4f8;">
                                                <span style="font-size:13px; color:#7a7a90;">Total Paid</span>
                                                <span style="font-size:13px; font-weight:700; color:#16a34a;">{{ fmtBDT(PROPERTY.total_paid) }}</span>
                                            </div>
                                            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f4f4f8;">
                                                <span style="font-size:13px; color:#6a4dff; font-weight:700;">Outstanding Balance</span>
                                                <span style="font-size:13.5px; font-weight:800; color:#6a4dff;">{{ fmtBDT(PROPERTY.outstanding) }}</span>
                                            </div>
                                            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0;">
                                                <span style="font-size:13px; color:#7a7a90;">Next Installment</span>
                                                <span style="font-size:13px; font-weight:700; color:#16162a;">15 Aug 2026</span>
                                            </div>
                                        </div>
                                        <div style="display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;">
                                            <Link :href="route('client.payments')" style="font-size:12px; font-weight:700; color:#fff; background:linear-gradient(100deg,#6a4dff,#5132e0); padding:7px 13px; border-radius:9px; text-decoration:none;">Make Payment</Link>
                                            <Link :href="route('client.payments')" style="font-size:12px; font-weight:700; color:#6a4dff; background:#f6f3ff; border:1px solid #e6e1fb; padding:7px 13px; border-radius:9px; text-decoration:none;">View Schedule</Link>
                                        </div>
                                        <div style="font-size:10.5px; color:#a4a4b4; margin-top:10px;">{{ msg.time }}</div>
                                    </div>

                                    <!-- Plain text AI bubble -->
                                    <div v-else style="background:#f5f4fb; border-radius:6px 16px 16px 16px; padding:13px 16px;">
                                        <div style="font-size:13.5px; color:#16162a; font-weight:500; line-height:1.6; white-space:pre-line;">{{ msg.text }}</div>
                                        <div style="font-size:10.5px; color:#a4a4b4; margin-top:8px;">{{ msg.time }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- User message -->
                            <div v-else style="display:flex; justify-content:flex-end; max-width:72%; align-self:flex-end;">
                                <div>
                                    <div style="background:linear-gradient(100deg,#6a4dff,#5132e0); color:#fff; border-radius:16px 4px 16px 16px; padding:12px 16px; font-size:13.5px; font-weight:500; line-height:1.5;">{{ msg.text }}</div>
                                    <div style="text-align:right; font-size:10.5px; color:#a4a4b4; margin-top:5px;">{{ msg.time }}</div>
                                </div>
                            </div>

                        </template>

                        <!-- Typing indicator -->
                        <div v-if="isTyping" style="display:flex; gap:10px; align-items:flex-start;">
                            <div style="width:30px; height:30px; flex-shrink:0; border-radius:50%; background:linear-gradient(135deg,#9a85ff,#6a4dff); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:12px;">S</div>
                            <div style="background:#f5f4fb; border-radius:6px 16px 16px 16px; padding:13px 18px; display:flex; align-items:center; gap:5px;">
                                <span class="dot-bounce" style="width:7px; height:7px; border-radius:50%; background:#9a85ff; display:inline-block;"></span>
                                <span class="dot-bounce" style="width:7px; height:7px; border-radius:50%; background:#9a85ff; display:inline-block; animation-delay:.18s;"></span>
                                <span class="dot-bounce" style="width:7px; height:7px; border-radius:50%; background:#9a85ff; display:inline-block; animation-delay:.36s;"></span>
                            </div>
                        </div>

                    </div>

                    <!-- Input bar -->
                    <div style="border-top:1px solid #f0f0f5; padding:14px 20px 16px; flex-shrink:0;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input
                                v-model="inputText"
                                @keydown="onKey"
                                placeholder="Type your question here..."
                                style="flex:1; height:46px; padding:0 16px; border:1.5px solid #ededf3; border-radius:13px; outline:none; background:#faf9fd; font-family:'Plus Jakarta Sans',system-ui,sans-serif; font-size:13.5px; color:#16162a; transition:border-color .15s;"
                                class="focus:border-[#6a4dff]"
                            />
                            <button
                                @click="sendMessage()"
                                :disabled="!inputText.trim() || isTyping"
                                style="width:46px; height:46px; flex-shrink:0; border:none; cursor:pointer; border-radius:13px; background:linear-gradient(100deg,#6a4dff,#5132e0); display:flex; align-items:center; justify-content:center; box-shadow:0 6px 16px -6px rgba(81,50,224,.6); transition:opacity .15s;"
                                :style="(!inputText.trim() || isTyping) ? 'opacity:.5; cursor:not-allowed;' : ''"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12l16-8-6 16-3.5-6.5z"/></svg>
                            </button>
                        </div>
                        <div style="text-align:center; font-size:11px; color:#b6b6c4; margin-top:9px;">Sara AI can make mistakes. Please verify important financial information independently.</div>
                    </div>
                </div>

                <!-- ── Right rail ─────────────────────────────────────────── -->
                <div style="width:296px; flex-shrink:0; display:flex; flex-direction:column; gap:14px;" class="ai-rail">

                    <!-- My Property -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:18px; padding:18px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:13px;">
                            <div style="font-size:14px; font-weight:700; color:#16162a;">My Property</div>
                            <Link :href="route('client.properties')" style="font-size:12px; font-weight:700; color:#6a4dff; text-decoration:none;">View Details</Link>
                        </div>
                        <!-- Property image placeholder -->
                        <div style="display:flex; gap:12px; align-items:center; margin-bottom:14px;">
                            <div style="width:68px; height:54px; flex-shrink:0; border-radius:11px; background:repeating-linear-gradient(135deg,#e3e8ef,#e3e8ef 7px,#eef1f5 7px,#eef1f5 14px); display:flex; align-items:center; justify-content:center;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#b0b8c4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:800; color:#16162a;">{{ PROPERTY.name }}</div>
                                <div style="font-size:12px; color:#9a9ab0; margin-top:2px;">Block {{ PROPERTY.block }} · Floor {{ PROPERTY.floor }}</div>
                                <span style="display:inline-block; font-size:11px; font-weight:700; color:#6a4dff; background:#f6f3ff; padding:3px 9px; border-radius:6px; margin-top:5px;">Unit {{ PROPERTY.unit }}</span>
                            </div>
                        </div>
                        <div style="display:flex; flex-direction:column;">
                            <div v-for="row in [
                                { label:'Status',    value: PROPERTY.status,   highlight: true  },
                                { label:'Type',      value: PROPERTY.type,     highlight: false },
                                { label:'Size',      value: PROPERTY.size,     highlight: false },
                                { label:'Handover',  value: PROPERTY.handover, highlight: false },
                            ]" :key="row.label"
                                style="display:flex; align-items:center; justify-content:space-between; padding:7px 0; border-bottom:1px solid #f5f4fb;"
                            >
                                <span style="font-size:12.5px; color:#9a9ab0;">{{ row.label }}</span>
                                <span v-if="row.highlight" style="font-size:11.5px; font-weight:700; color:#16a34a; background:#e6f7ed; padding:2px 9px; border-radius:6px;">{{ row.value }}</span>
                                <span v-else style="font-size:12.5px; font-weight:700; color:#16162a;">{{ row.value }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Overview -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:18px; padding:18px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                            <div style="font-size:14px; font-weight:700; color:#16162a;">Payment Overview</div>
                            <Link :href="route('client.payments')" style="font-size:12px; font-weight:700; color:#6a4dff; text-decoration:none;">Details</Link>
                        </div>
                        <div style="display:flex; align-items:center; gap:14px;">
                            <!-- Conic donut -->
                            <div :style="`position:relative; width:96px; height:96px; flex-shrink:0; border-radius:50%; background:conic-gradient(#16a34a 0% ${PROPERTY.paid_pct}%, #f59e0b ${PROPERTY.paid_pct}% 100%);`">
                                <div style="position:absolute; inset:14px; border-radius:50%; background:#fff; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                    <div style="font-size:18px; font-weight:900; color:#16162a; line-height:1;">{{ PROPERTY.paid_pct }}%</div>
                                    <div style="font-size:9.5px; font-weight:600; color:#9a9ab0; margin-top:1px;">Paid</div>
                                </div>
                            </div>
                            <div style="flex:1; display:flex; flex-direction:column; gap:11px;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:6px; font-size:11px; font-weight:600; color:#9a9ab0; margin-bottom:3px;">
                                        <span style="width:7px; height:7px; border-radius:50%; background:#16a34a;"></span> Paid
                                    </div>
                                    <div style="font-size:13px; font-weight:800; color:#16162a;">{{ fmtBDT(PROPERTY.total_paid) }}</div>
                                </div>
                                <div>
                                    <div style="display:flex; align-items:center; gap:6px; font-size:11px; font-weight:600; color:#9a9ab0; margin-bottom:3px;">
                                        <span style="width:7px; height:7px; border-radius:50%; background:#f59e0b;"></span> Outstanding
                                    </div>
                                    <div style="font-size:13px; font-weight:800; color:#16162a;">{{ fmtBDT(PROPERTY.outstanding) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Construction Progress -->
                    <div style="background:#fff; border:1px solid #ededf3; border-radius:18px; padding:18px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                            <div style="font-size:14px; font-weight:700; color:#16162a;">Construction</div>
                            <Link :href="route('client.construction')" style="font-size:12px; font-weight:700; color:#6a4dff; text-decoration:none;">Details</Link>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:12.5px; color:#9a9ab0;">Overall Progress</span>
                            <span style="font-size:15px; font-weight:800; color:#6a4dff;">{{ PROPERTY.construction_pct }}%</span>
                        </div>
                        <div style="height:8px; border-radius:5px; background:#f0eef9; overflow:hidden; margin-bottom:16px;">
                            <div :style="{ width: PROPERTY.construction_pct + '%' }" style="height:100%; border-radius:5px; background:linear-gradient(90deg,#7b63ff,#5132e0); transition:width .4s;"></div>
                        </div>
                        <!-- Steps -->
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div v-for="(step, i) in STEPS" :key="step.label" style="display:flex; flex-direction:column; align-items:center; gap:5px; flex:1;">
                                <div v-if="step.done"
                                    style="width:28px; height:28px; border-radius:50%; background:#16a34a; display:flex; align-items:center; justify-content:center;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                                </div>
                                <div v-else-if="step.active"
                                    style="width:28px; height:28px; border-radius:50%; border:2.5px solid #6a4dff; display:flex; align-items:center; justify-content:center;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:#6a4dff;"></span>
                                </div>
                                <div v-else
                                    style="width:28px; height:28px; border-radius:50%; border:2px solid #e2e0ec;">
                                </div>
                                <span style="font-size:9.5px; font-weight:600;" :style="step.active ? 'color:#6a4dff;' : step.done ? 'color:#9a9ab0;' : 'color:#c2c2cf;'">{{ step.label }}</span>
                                <!-- connector line between steps -->
                                <div v-if="i < STEPS.length - 1" style="position:absolute;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pro Tip -->
                    <div style="background:linear-gradient(135deg,#efeafc,#e7e0ff); border:1px solid #e3daff; border-radius:18px; padding:16px 18px;">
                        <div style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:800; color:#3a25b0; margin-bottom:9px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6a4dff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13c0-3 2.5-5 6-5s6 2 6 5a4 4 0 0 1-1.5 3.2V18a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-1.8A4 4 0 0 1 5 13Z"/></svg>
                            Pro Tip from Sara AI
                        </div>
                        <p style="font-size:12.5px; color:#6a5fae; font-weight:500; line-height:1.55; margin:0 0 12px;">Paying a small extra amount monthly can significantly reduce your total interest and shorten your payment tenure.</p>
                        <Link :href="route('client.mortgage')" style="display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700; color:#6a4dff; text-decoration:none;">
                            See Savings Calculator
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<style scoped>
@keyframes sarapulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: .4; }
}

@keyframes dotbounce {
    0%, 60%, 100% { transform: translateY(0); }
    30%            { transform: translateY(-6px); }
}

.dot-bounce {
    animation: dotbounce 1.1s ease-in-out infinite;
}

@media (max-width: 1200px) {
    .ai-rail { display: none !important; }
}

@media (max-width: 900px) {
    .ai-left { display: none !important; }
}
</style>
