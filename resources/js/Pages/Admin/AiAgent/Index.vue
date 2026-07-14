<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { useTheme } from '@/composables/useTheme';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Separator } from '@/Components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

const props = defineProps({
    kpis:             { type: Array,  required: true },
    chips:            { type: Array,  required: true },
    categories:       { type: Array,  required: true },
    knowledgeHealth:  { type: Object, required: true },
    outdatedArticles: { type: Array,  required: true },
    articles:         { type: Array,  required: true },
    searches:         { type: Array,  required: true },
    interactions:     { type: Array,  required: true },
    aiPerformance:    { type: Array,  required: true },
    quickActions:     { type: Array,  required: true },
    suggestions:      { type: Array,  required: true },
});

// ── Icon paths, keyed by the `icon` field coming from the JSON ────────────────
const icons = {
    book:         '<path d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>',
    doc:          '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
    doc_plus:     '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M12 11v6M9 14h6"/>',
    grid:         '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    spark:        '<path d="M12 2l1.6 5.6L19 9l-5.4 1.4L12 16l-1.6-5.6L5 9l5.4-1.4z"/>',
    star:         '<path d="M12 2l2.9 6.3 6.9.6-5.2 4.5 1.6 6.8L12 17.3 5.8 20.7l1.6-6.8L2.2 8.9l6.9-.6z"/>',
    search:       '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/>',
    target:       '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
    leaf:         '<path d="M11 20A7 7 0 019.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6"/>',
    card:         '<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/>',
    crane:        '<path d="M4 16a8 8 0 0116 0M2 16h20M9 9V6a3 3 0 016 0v3"/>',
    users:        '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/>',
    chart:        '<path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/>',
    gear:         '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/>',
    upload:       '<path d="M20 17.6A5 5 0 0018 8h-1.26A8 8 0 104 16.25"/><path d="M8 12l4-4 4 4M12 8v9"/>',
    review:       '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4M9 11h4M11 9v4"/>',
    clock:        '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    alert:        '<path d="M10.3 3.2L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.2a2 2 0 00-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
    arrow:        '<path d="M5 12h14M13 6l6 6-6 6"/>',
    chevron_down: '<path d="M6 9l6 6 6-6"/>',
    send:         '<path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>',
    trash:        '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
    plus:         '<path d="M12 5v14M5 12h14"/>',
};

// ── localStorage helpers — every "create" action on this page is client-side
// only (static JSON fixture, no DB), and is persisted here so it survives reload ─
function loadLocal(key, fallback) {
    try {
        const raw = window.localStorage.getItem(key);
        return raw ? JSON.parse(raw) : fallback;
    } catch {
        return fallback;
    }
}
function saveLocal(key, value) {
    window.localStorage.setItem(key, JSON.stringify(value));
}

const STORAGE_ARTICLES     = 'admin_ai_agent_custom_articles';
const STORAGE_CATEGORIES   = 'admin_ai_agent_custom_categories';
const STORAGE_INTERACTIONS = 'admin_ai_agent_interaction_log';
const STORAGE_DISMISSED    = 'admin_ai_agent_dismissed_suggestions';
const STORAGE_TRAINING     = 'admin_ai_agent_training_snippets';
const STORAGE_DOCS         = 'admin_ai_agent_uploaded_docs';
const STORAGE_REVIEWED     = 'admin_ai_agent_reviewed_outdated';
const STORAGE_SETTINGS     = 'admin_ai_agent_settings';

const customArticles       = ref(loadLocal(STORAGE_ARTICLES, []));
const customCategories     = ref(loadLocal(STORAGE_CATEGORIES, []));
const interactionLog       = ref(loadLocal(STORAGE_INTERACTIONS, []));
const dismissedSuggestions = ref(loadLocal(STORAGE_DISMISSED, []));
const trainingSnippets     = ref(loadLocal(STORAGE_TRAINING, []));
const uploadedDocs         = ref(loadLocal(STORAGE_DOCS, []));
const reviewedOutdated     = ref(loadLocal(STORAGE_REVIEWED, []));

const combinedArticles = computed(() => [...customArticles.value, ...props.articles]);
const combinedCategories = computed(() => [...props.categories, ...customCategories.value]);
const combinedInteractions = computed(() => [...interactionLog.value, ...props.interactions]);
const visibleSuggestions = computed(() => props.suggestions.filter(s => !dismissedSuggestions.value.includes(s.title)));
const remainingOutdated = computed(() => props.outdatedArticles.filter(a => !reviewedOutdated.value.includes(a.name)));

// ── KPI row — Articles / Categories / AI Answers reflect local creations ─────
function parseNumber(str) {
    return parseInt(String(str).replace(/,/g, ''), 10) || 0;
}
function formatNumber(n) {
    return n.toLocaleString('en-US');
}
const liveKpis = computed(() => props.kpis.map((k) => {
    if (k.key === 'articles') return { ...k, value: formatNumber(parseNumber(k.value) + customArticles.value.length) };
    if (k.key === 'categories') return { ...k, value: String(parseNumber(k.value) + customCategories.value.length) };
    if (k.key === 'answers') return { ...k, value: formatNumber(parseNumber(k.value) + interactionLog.value.length) };
    return k;
}));

// ── Knowledge Health donut — "Review Outdated Articles" moves counts live ────
const { isDark } = useTheme();
const liveHealthSegments = computed(() => {
    const segs = props.knowledgeHealth.segments.map(s => ({ ...s }));
    const accurate = segs.find(s => s.key === 'accurate');
    const needsUpdate = segs.find(s => s.key === 'needs_update');
    const outdated = segs.find(s => s.key === 'outdated');
    let resolved = reviewedOutdated.value.length;
    const takeFrom = (seg) => {
        if (!seg || resolved <= 0) return;
        const take = Math.min(resolved, seg.count);
        seg.count -= take;
        resolved -= take;
        accurate.count += take;
    };
    takeFrom(outdated);
    takeFrom(needsUpdate);
    const total = segs.reduce((a, s) => a + s.count, 0);
    segs.forEach((s) => { s.pct = total ? Math.round((s.count / total) * 1000) / 10 : 0; });
    return segs;
});
const healthTotal = computed(() => liveHealthSegments.value.reduce((a, s) => a + s.count, 0));
const healthOverallPct = computed(() => {
    const accurate = liveHealthSegments.value.find(s => s.key === 'accurate');
    return healthTotal.value ? Math.round((accurate.count / healthTotal.value) * 100) : 0;
});
// ── Knowledge Health — ApexCharts donut ──────────────────────────────────────
const healthChartSeries = computed(() => liveHealthSegments.value.map(s => s.count));
const healthChartOptions = computed(() => ({
    chart: { type: 'donut', fontFamily: 'Plus Jakarta Sans, sans-serif' },
    labels: liveHealthSegments.value.map(s => s.label),
    colors: liveHealthSegments.value.map(s => s.color),
    legend: { show: false },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: [isDark.value ? '#151922' : '#FFFFFF'] },
    plotOptions: {
        pie: {
            donut: {
                size: '74%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Overall Health',
                        color: isDark.value ? '#A8A399' : '#6B6355',
                        fontSize: '10px',
                        formatter: () => `${healthOverallPct.value}%`,
                    },
                    value: { color: isDark.value ? '#F5F2EA' : '#1A1611', fontSize: '24px', fontWeight: 800, offsetY: -4 },
                },
            },
        },
    },
    tooltip: { theme: isDark.value ? 'dark' : 'light', y: { formatter: (v) => `${v} articles` } },
}));

// ── Ask Sera AI — client-side canned answers, logged into Recent Interactions ─
const askQuery = ref('');
const askInputEl = ref(null);
const askPanelEl = ref(null);

const tagPool = [
    { tag: 'Payments',     tagBg: 'rgba(198,161,91,0.12)', tagColor: '#C6A15B' },
    { tag: 'Construction', tagBg: 'rgba(96,165,250,0.15)', tagColor: '#60A5FA' },
    { tag: 'Documents',    tagBg: 'rgba(96,165,250,0.15)', tagColor: '#60A5FA' },
    { tag: 'Reports',      tagBg: 'rgba(248,113,113,0.15)', tagColor: '#F87171' },
    { tag: 'General',      tagBg: 'rgba(52,211,153,0.15)', tagColor: '#34D399' },
];
function pickTag(query) {
    const q = query.toLowerCase();
    if (q.includes('payment') || q.includes('installment') || q.includes('receipt')) return tagPool[0];
    if (q.includes('construction') || q.includes('site')) return tagPool[1];
    if (q.includes('document')) return tagPool[2];
    if (q.includes('report') || q.includes('analytic')) return tagPool[3];
    return tagPool[4];
}

const showAnswer = ref(false);
const answerQuestion = ref('');
const answerText = ref('');

// Word-overlap search over the (localStorage-backed) knowledge base — dynamic,
// so any article added at runtime becomes matchable immediately, no reload needed.
const STOPWORDS = new Set(['how', 'to', 'is', 'the', 'a', 'an', 'of', 'for', 'and', 'what', 'are', 'do', 'does', 'on', 'in', 'i', 'my', 'me', 'can', 'you']);
function tokenize(s) {
    return s.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').split(/\s+/).filter(w => w.length > 2 && !STOPWORDS.has(w));
}
function searchArticles(query) {
    const queryTokens = tokenize(query);
    if (!queryTokens.length) return [];
    return combinedArticles.value
        .map((a) => {
            const articleTokens = new Set(tokenize(`${a.name} ${a.cat}`));
            const score = queryTokens.filter(t => articleTokens.has(t)).length;
            return { article: a, score };
        })
        .filter(s => s.score > 0)
        .sort((a, b) => b.score - a.score)
        .map(s => s.article);
}

function askSera(query) {
    const q = (query ?? askQuery.value).trim();
    if (!q) return;
    const matches = searchArticles(q);

    answerQuestion.value = q;
    answerText.value = matches.length
        ? `I found ${matches.length} related article${matches.length > 1 ? 's' : ''} in the knowledge base, including "${matches[0].name}" under ${matches[0].cat}. Check that article for full step-by-step details.`
        : `I couldn't find an exact article for "${q}" yet. This has been logged as a knowledge gap — consider adding an article so Sera AI can answer it next time.`;

    const tag = pickTag(q);
    interactionLog.value.unshift({ q, time: 'Just now', ...tag });
    saveLocal(STORAGE_INTERACTIONS, interactionLog.value);

    askQuery.value = '';
    showAnswer.value = true;
}

function askChip(c) {
    askPanelEl.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    askSera(c);
}

function focusAskPanel() {
    askPanelEl.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    nextTick(() => askInputEl.value?.focus());
}

// ── Knowledge Settings dialog — persisted form, client-side only ─────────────
const showSettings = ref(false);
const settingsForm = ref(loadLocal(STORAGE_SETTINGS, {
    autoSuggest: 'enabled',
    defaultCategory: props.categories[0]?.name ?? '',
    confidence: 'balanced',
}));
function saveSettings() {
    saveLocal(STORAGE_SETTINGS, settingsForm.value);
    showSettings.value = false;
}

// ── Knowledge Categories — "View All" dialog + click-to-filter the table ─────
const showAllCategories = ref(false);
const categoryFilter = ref('');
const articlesTableEl = ref(null);

function filterByCategory(name) {
    categoryFilter.value = categoryFilter.value === name ? '' : name;
    showAllCategories.value = false;
    articlesTableEl.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Popular Knowledge Articles — search + Add Article dialog ─────────────────
const articleSearch = ref('');
const filteredArticles = computed(() => {
    let rows = combinedArticles.value;
    if (categoryFilter.value) rows = rows.filter(a => a.cat === categoryFilter.value);
    const q = articleSearch.value.trim().toLowerCase();
    if (q) rows = rows.filter(a => a.name.toLowerCase().includes(q) || a.cat.toLowerCase().includes(q));
    return rows;
});

function viewAllArticles() {
    categoryFilter.value = '';
    articleSearch.value = '';
    articlesPage.value = 1;
    articlesTableEl.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Popular Knowledge Articles — pagination ───────────────────────────────────
const articlesPageSize = 5;
const articlesPage = ref(1);
const articlesTotalPages = computed(() => Math.max(1, Math.ceil(filteredArticles.value.length / articlesPageSize)));
const paginatedArticles = computed(() => {
    const start = (articlesPage.value - 1) * articlesPageSize;
    return filteredArticles.value.slice(start, start + articlesPageSize);
});
watch([articleSearch, categoryFilter], () => { articlesPage.value = 1; });
watch(filteredArticles, () => {
    if (articlesPage.value > articlesTotalPages.value) articlesPage.value = articlesTotalPages.value;
});
function goToArticlesPage(p) {
    articlesPage.value = Math.min(Math.max(1, p), articlesTotalPages.value);
}
const articlesPageNumbers = computed(() => {
    const total = articlesTotalPages.value;
    const cur = articlesPage.value;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [1];
    if (cur > 3) pages.push('…');
    for (let p = Math.max(2, cur - 1); p <= Math.min(total - 1, cur + 1); p++) pages.push(p);
    if (cur < total - 2) pages.push('…');
    pages.push(total);
    return pages;
});

const showAddArticle = ref(false);
const addArticleForm = ref({ name: '', cat: props.categories[0]?.name ?? '', rating: '5.0' });
const pendingSuggestionTitle = ref(null);

function openAddArticle(prefill) {
    addArticleForm.value = {
        name: prefill?.title ?? '',
        cat: prefill?.cat ?? (props.categories[0]?.name ?? ''),
        rating: '5.0',
    };
    pendingSuggestionTitle.value = prefill?.title ?? null;
    showAddArticle.value = true;
}

function submitAddArticle() {
    const f = addArticleForm.value;
    if (!f.name.trim()) return;
    const now = new Date();
    customArticles.value.unshift({
        name: f.name.trim(),
        cat: f.cat,
        views: '0',
        helpful: '—',
        rating: f.rating,
        date: now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
    });
    saveLocal(STORAGE_ARTICLES, customArticles.value);

    if (pendingSuggestionTitle.value) {
        dismissedSuggestions.value.push(pendingSuggestionTitle.value);
        saveLocal(STORAGE_DISMISSED, dismissedSuggestions.value);
        pendingSuggestionTitle.value = null;
    }

    showAddArticle.value = false;
    categoryFilter.value = '';
}

// ── Manage Categories dialog — add / remove custom categories ────────────────
const showManageCategories = ref(false);
const colorChoices = [
    { bg: 'rgba(198,161,91,0.12)', color: '#C6A15B' },
    { bg: 'rgba(52,211,153,0.15)', color: '#34D399' },
    { bg: 'rgba(96,165,250,0.15)', color: '#60A5FA' },
    { bg: 'rgba(251,191,36,0.15)', color: '#FBBF24' },
    { bg: 'rgba(248,113,113,0.15)', color: '#F87171' },
    { bg: 'rgba(34,211,238,0.15)', color: '#22D3EE' },
];
const newCategoryForm = ref({ name: '', color: colorChoices[0] });

function submitCategory() {
    if (!newCategoryForm.value.name.trim()) return;
    customCategories.value.push({
        name: newCategoryForm.value.name.trim(),
        count: '0 Articles',
        color: newCategoryForm.value.color.color,
        bg: newCategoryForm.value.color.bg,
        icon: 'doc',
    });
    saveLocal(STORAGE_CATEGORIES, customCategories.value);
    newCategoryForm.value = { name: '', color: colorChoices[0] };
}

function removeCategory(name) {
    customCategories.value = customCategories.value.filter(c => c.name !== name);
    saveLocal(STORAGE_CATEGORIES, customCategories.value);
}

// ── Review Outdated Articles dialog — "resolving" one feeds the health ring ──
const showOutdated = ref(false);
function markReviewed(article) {
    reviewedOutdated.value.push(article.name);
    saveLocal(STORAGE_REVIEWED, reviewedOutdated.value);
}

// ── Upload Documents dialog — client-side only, files are never sent anywhere ─
const showUpload = ref(false);
function formatBytes(bytes) {
    if (!bytes) return '0 KB';
    const kb = bytes / 1024;
    return kb < 1024 ? `${kb.toFixed(1)} KB` : `${(kb / 1024).toFixed(1)} MB`;
}
function handleFileChange(e) {
    const files = Array.from(e.target.files || []);
    const now = new Date();
    files.forEach((f) => {
        uploadedDocs.value.unshift({
            name: f.name,
            size: formatBytes(f.size),
            date: now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
        });
    });
    saveLocal(STORAGE_DOCS, uploadedDocs.value);
    e.target.value = '';
}
function removeDoc(name) {
    uploadedDocs.value = uploadedDocs.value.filter(d => d.name !== name);
    saveLocal(STORAGE_DOCS, uploadedDocs.value);
}

// ── AI Training Data dialog — add / remove text snippets ─────────────────────
const showTraining = ref(false);
const newTrainingSnippet = ref('');
function addTrainingSnippet() {
    if (!newTrainingSnippet.value.trim()) return;
    trainingSnippets.value.unshift({
        text: newTrainingSnippet.value.trim(),
        date: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
    });
    saveLocal(STORAGE_TRAINING, trainingSnippets.value);
    newTrainingSnippet.value = '';
}
function removeTrainingSnippet(idx) {
    trainingSnippets.value.splice(idx, 1);
    saveLocal(STORAGE_TRAINING, trainingSnippets.value);
}

// ── Analytics Report quick action — export the visible articles as CSV ───────
function csvCell(value) {
    const s = String(value ?? '');
    return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
}
function exportArticlesCsv() {
    const headers = ['Article', 'Category', 'Views', 'Helpful', 'Rating', 'Last Updated'];
    const rows = filteredArticles.value.map(a => [a.name, a.cat, a.views, a.helpful, a.rating, a.date]);
    const csv = [headers, ...rows].map(row => row.map(csvCell).join(',')).join('\r\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `knowledge-articles-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}

// ── Quick Actions dispatcher ───────────────────────────────────────────────────
function runQuickAction(action) {
    switch (action.key) {
        case 'add_article':       openAddArticle(); break;
        case 'upload_docs':       showUpload.value = true; break;
        case 'manage_categories': showManageCategories.value = true; break;
        case 'review_articles':   showOutdated.value = true; break;
        case 'training_data':     showTraining.value = true; break;
        case 'analytics_report':  exportArticlesCsv(); break;
    }
}

// ── "View All" dialogs for the smaller list panels ────────────────────────────
const showAllSearches = ref(false);
const showAllInteractions = ref(false);
const showAllSuggestions = ref(false);
</script>

<template>
    <Head title="Sera AI Knowledge" />

    <AdminLayout title="Sera AI Knowledge" :breadcrumbs="[{ label: 'Admin' }, { label: 'Sera AI Knowledge' }]">
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-extrabold tracking-[-0.6px] text-foreground sm:text-[30px]">
                    Sera AI Knowledge
                    <Badge class="gap-1.5 rounded-full border-transparent bg-admin-accent/10 px-[11px] py-[5px] text-[11px] font-bold text-admin-accent hover:bg-admin-accent/10">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.8 6.2L20 10l-6.2 1.8L12 18l-1.8-6.2L4 10l6.2-1.8z"/></svg>
                        AI Powered
                    </Badge>
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">AI-powered knowledge base for smarter insights, faster answers, and intelligent assistance.</p>
            </div>
            <div class="flex items-center gap-3">
                <Button @click="focusAskPanel" class="gap-2 rounded-xl bg-admin-accent px-[18px] py-[11px] text-[13.5px] font-semibold text-on-gold hover:bg-admin-accent/90">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.8 6.2L20 10l-6.2 1.8L12 18l-1.8-6.2L4 10l6.2-1.8z"/></svg>
                    Ask Sera AI
                </Button>
                <Button variant="outline" @click="showSettings = true" class="gap-2 rounded-xl px-4 py-[11px] text-[13.5px] font-semibold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg>
                    Knowledge Settings
                </Button>
            </div>
        </div>

        <!-- KPI row -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
            <div v-for="k in liveKpis" :key="k.key" class="rounded-2xl border border-border bg-admin-surface-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <div class="mb-2.5 flex items-center gap-2">
                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: k.bg, color: k.color }">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[k.icon]" />
                    </div>
                    <span class="text-[11.5px] font-semibold leading-tight text-muted-foreground">{{ k.label }}</span>
                </div>
                <div class="text-2xl font-extrabold leading-none tracking-tight text-foreground">{{ k.value }}</div>
                <div class="mt-2.5 flex items-center justify-between text-[11px]">
                    <span class="text-muted-foreground">{{ k.sub }}</span>
                    <span class="flex items-center gap-0.5 font-bold text-success">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 15l6-6 6 6"/></svg>
                        {{ k.change }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Row A -->
        <div class="mb-6 grid grid-cols-1 items-start gap-6 xl:grid-cols-[1.25fr_1.05fr_1.1fr]">

            <!-- Ask Sera AI -->
            <div ref="askPanelEl" class="flex min-w-0 flex-col rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                <div class="text-[17px] font-bold text-foreground">Ask Sera AI</div>
                <div class="mt-0.5 text-xs text-muted-foreground">Get instant answers from your AI assistant</div>
                <div class="mt-4 flex items-center gap-2.5 rounded-2xl border border-border bg-muted/40 p-2 pl-4 transition-colors focus-within:border-admin-accent">
                    <input
                        ref="askInputEl" v-model="askQuery" placeholder="Ask anything about projects, payments, buyers, construction..."
                        class="min-w-0 flex-1 border-none bg-transparent text-[13px] text-foreground outline-none"
                        @keydown.enter="askSera()"
                    />
                    <button type="button" @click="askSera()" class="flex h-[42px] w-[42px] flex-none items-center justify-center rounded-[11px] bg-admin-accent text-on-gold shadow-lg transition-transform hover:-translate-y-0.5">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.send" />
                    </button>
                </div>
                <div class="mb-3 mt-[18px] text-xs font-semibold text-muted-foreground">Try asking:</div>
                <div class="flex flex-wrap gap-2.5">
                    <button
                        v-for="c in chips" :key="c" type="button" @click="askChip(c)"
                        class="rounded-[10px] border border-border bg-muted/40 px-[13px] py-2 text-xs font-semibold text-foreground/80 transition-all hover:border-admin-accent hover:bg-admin-accent/10 hover:text-admin-accent"
                    >
                        {{ c }}
                    </button>
                </div>
            </div>

            <!-- Knowledge Categories -->
            <div class="min-w-0 rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                <div class="mb-3.5 flex items-center justify-between">
                    <div class="text-[17px] font-bold text-foreground">Knowledge Categories</div>
                    <button type="button" @click="showAllCategories = true" class="cursor-pointer text-xs font-semibold text-admin-accent">View All</button>
                </div>
                <div class="grid grid-cols-1 gap-[11px] sm:grid-cols-2">
                    <button
                        v-for="c in categories" :key="c.name" type="button" @click="filterByCategory(c.name)"
                        class="flex min-w-0 items-center gap-[11px] rounded-[13px] border border-border p-[11px] text-left transition-all hover:border-admin-accent hover:bg-admin-accent/5"
                        :class="categoryFilter === c.name && 'border-admin-accent bg-admin-accent/10'"
                    >
                        <div class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[9px]" :style="{ background: c.bg, color: c.color }">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[c.icon]" />
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-[12.5px] font-bold text-foreground">{{ c.name }}</div>
                            <div class="mt-px text-[11px] text-muted-foreground">{{ c.count }}</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Knowledge Health -->
            <div class="min-w-0 rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                <div class="mb-2 flex items-center justify-between">
                    <div class="text-[17px] font-bold text-foreground">Knowledge Health</div>
                    <span class="flex items-center gap-1.5 rounded-[9px] border border-border px-2.5 py-1.5 text-[11px] font-semibold text-foreground/80">This Month
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="stroke-muted-foreground" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.chevron_down" />
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-center gap-[18px]">
                    <div class="w-[140px] flex-none">
                        <apexchart type="donut" height="140" :series="healthChartSeries" :options="healthChartOptions" />
                    </div>
                    <div class="flex min-w-[150px] flex-1 flex-col gap-3.5">
                        <div v-for="s in liveHealthSegments" :key="s.key" class="flex items-center justify-between text-[12.5px]">
                            <span class="flex items-center gap-2 text-foreground/80"><span class="h-[9px] w-[9px] flex-none rounded-full" :style="{ background: s.color }"></span>{{ s.label }}</span>
                            <b class="text-foreground">{{ s.count }} <span class="font-medium text-muted-foreground">({{ s.pct }}%)</span></b>
                        </div>
                    </div>
                </div>
                <div class="mb-3 mt-4 text-center text-[11.5px] text-muted-foreground">Keep your knowledge base updated for better AI performance.</div>
                <button type="button" @click="showOutdated = true" class="flex w-full cursor-pointer items-center justify-center gap-1.5 border-t border-border pt-3.5 text-[12.5px] font-semibold text-admin-accent">
                    Review Outdated Articles
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.arrow" />
                </button>
            </div>
        </div>

        <!-- Row B -->
        <div class="mb-6 grid grid-cols-1 items-start gap-6 xl:grid-cols-[2fr_1.05fr_1.05fr]">

            <!-- Popular Knowledge Articles -->
            <div ref="articlesTableEl" class="min-w-0 rounded-2xl border border-border bg-admin-surface-card shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 p-[22px] pb-3.5">
                    <div class="text-base font-bold text-foreground">Popular Knowledge Articles</div>
                    <div class="flex items-center gap-2.5">
                        <div class="relative w-full max-w-[220px]">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                            <Input v-model="articleSearch" placeholder="Search articles..." class="pl-9" />
                        </div>
                        <button type="button" @click="viewAllArticles" class="cursor-pointer whitespace-nowrap text-xs font-semibold text-admin-accent">View All</button>
                    </div>
                </div>
                <div v-if="categoryFilter" class="flex items-center gap-2 px-[22px] pb-2 text-xs text-muted-foreground">
                    Filtered by <Badge variant="outline" class="rounded-full border-admin-accent/30 bg-admin-accent/10 text-admin-accent">{{ categoryFilter }}</Badge>
                    <button type="button" @click="categoryFilter = ''" class="font-semibold text-admin-accent hover:underline">Clear</button>
                </div>
                <div class="overflow-x-auto">
                    <Table class="min-w-[680px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead class="pl-[22px]">Article</TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Views</TableHead>
                                <TableHead>Helpful</TableHead>
                                <TableHead>Rating</TableHead>
                                <TableHead class="pr-[22px]">Last Updated</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="filteredArticles.length === 0">
                                <TableCell colspan="6" class="py-14 text-center text-muted-foreground">No articles found</TableCell>
                            </TableRow>
                            <TableRow v-for="a in paginatedArticles" :key="a.name">
                                <TableCell class="min-w-[200px] pl-[22px]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-lg bg-admin-accent/10 text-admin-accent">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.doc" />
                                        </div>
                                        <div class="truncate text-[12.5px] font-semibold text-foreground">{{ a.name }}</div>
                                    </div>
                                </TableCell>
                                <TableCell class="whitespace-nowrap text-xs text-foreground/80">{{ a.cat }}</TableCell>
                                <TableCell class="text-[12.5px] font-semibold text-foreground">{{ a.views }}</TableCell>
                                <TableCell class="text-[12.5px] font-bold text-success">{{ a.helpful }}</TableCell>
                                <TableCell>
                                    <span class="flex items-center gap-1">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#F5B100"><path d="M12 2l2.9 6.3 6.9.6-5.2 4.5 1.6 6.8L12 17.3 5.8 20.7l1.6-6.8L2.2 8.9l6.9-.6z"/></svg>
                                        <span class="text-[12.5px] font-bold text-foreground">{{ a.rating }}</span>
                                    </span>
                                </TableCell>
                                <TableCell class="whitespace-nowrap pr-[22px] text-xs text-foreground/80">{{ a.date }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3.5 border-t border-border p-[18px]">
                    <div class="text-[12.5px] text-muted-foreground">Showing {{ paginatedArticles.length }} of {{ filteredArticles.length }} articles</div>
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button" :disabled="articlesPage === 1" @click="goToArticlesPage(articlesPage - 1)"
                            class="flex h-[30px] w-[30px] items-center justify-center rounded-lg border border-border text-foreground/80 transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                        <template v-for="(p, idx) in articlesPageNumbers" :key="idx">
                            <span v-if="p === '…'" class="flex h-[30px] w-[30px] items-center justify-center text-[12.5px] text-muted-foreground">…</span>
                            <button
                                v-else type="button" @click="goToArticlesPage(p)"
                                class="flex h-[30px] w-[30px] items-center justify-center rounded-lg text-[12.5px] font-semibold transition-colors"
                                :class="p === articlesPage ? 'bg-admin-accent text-on-gold font-bold' : 'border border-border text-foreground/80 hover:bg-muted'"
                            >
                                {{ p }}
                            </button>
                        </template>
                        <button
                            type="button" :disabled="articlesPage === articlesTotalPages" @click="goToArticlesPage(articlesPage + 1)"
                            class="flex h-[30px] w-[30px] items-center justify-center rounded-lg border border-border text-foreground/80 transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MIDDLE: Top Search Queries + AI Performance -->
            <div class="flex min-w-0 flex-col gap-6">
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Top Search Queries</div>
                        <button type="button" @click="showAllSearches = true" class="cursor-pointer text-xs font-semibold text-admin-accent">View All</button>
                    </div>
                    <div class="flex items-center justify-between border-b border-border pb-1.5 text-[11.5px] font-bold text-muted-foreground">
                        <span>Query</span><span>Searches</span>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="s in searches.slice(0, 5)" :key="s.q" class="flex items-center justify-between gap-3 border-b border-border/60 py-[11px] last:border-b-0">
                            <span class="min-w-0 truncate text-[12.5px] text-foreground/80">{{ s.q }}</span>
                            <span class="flex-none text-[12.5px] font-bold text-foreground">{{ s.n }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-3.5 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">AI Performance Insights</div>
                        <span class="flex items-center gap-1.5 rounded-[9px] border border-border px-2.5 py-[5px] text-[11px] font-semibold text-foreground/80">This Month
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="stroke-muted-foreground" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.chevron_down" />
                        </span>
                    </div>
                    <div class="flex flex-col gap-3.5">
                        <div v-for="p in aiPerformance" :key="p.key" class="flex items-center gap-[11px]">
                            <div class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[9px]" :style="{ background: p.bg, color: p.color }">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[p.icon]" />
                            </div>
                            <div class="min-w-0 flex-1 text-xs font-semibold text-foreground">{{ p.label }}</div>
                            <div class="text-right text-sm font-extrabold text-foreground">{{ p.value }}</div>
                            <span class="flex flex-none items-center gap-0.5 text-[11px] font-bold text-success">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9l-6 6-6-6"/></svg>
                                {{ p.change }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Recent AI Interactions + Quick Actions -->
            <div class="flex min-w-0 flex-col gap-6">
                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-base font-bold text-foreground">Recent AI Interactions</div>
                        <button type="button" @click="showAllInteractions = true" class="cursor-pointer text-xs font-semibold text-admin-accent">View All</button>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="i in combinedInteractions.slice(0, 5)" :key="i.q + i.time" class="flex items-start gap-[11px] border-b border-border/60 py-[11px] last:border-b-0">
                            <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-lg bg-admin-accent/10 text-[11px] font-extrabold text-admin-accent">Q</div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[12.5px] font-semibold leading-snug text-foreground">{{ i.q }}</div>
                                <div class="mt-0.5 text-[11px] text-muted-foreground">{{ i.time }}</div>
                            </div>
                            <span class="flex-none whitespace-nowrap rounded-md px-2 py-[3px] text-[10px] font-bold" :style="{ background: i.tagBg, color: i.tagColor }">{{ i.tag }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
                    <div class="mb-3.5 text-base font-bold text-foreground">Quick Actions</div>
                    <div class="grid grid-cols-2 gap-[11px]">
                        <button
                            v-for="a in quickActions" :key="a.key" type="button" @click="runQuickAction(a)"
                            class="flex items-center gap-2.5 rounded-xl border border-border p-[11px] text-left transition-all"
                            :style="{ '--hbg': a.hoverBg, '--hborder': a.hoverBorder }"
                            @mouseenter="$event.currentTarget.style.background = 'var(--hbg)'; $event.currentTarget.style.borderColor = 'var(--hborder)'"
                            @mouseleave="$event.currentTarget.style.background = ''; $event.currentTarget.style.borderColor = ''"
                        >
                            <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-lg" :style="{ background: a.bg, color: a.color }">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[a.icon]" />
                            </div>
                            <span class="text-[11.5px] font-semibold text-foreground/80">{{ a.label }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Suggested Knowledge Updates -->
        <div class="rounded-2xl border border-border bg-admin-surface-card p-[22px] shadow-card">
            <div class="mb-4 flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="text-admin-accent"><path d="M12 2l1.6 5.6L19 9l-5.4 1.4L12 16l-1.6-5.6L5 9l5.4-1.4z"/></svg>
                <div class="text-base font-bold text-foreground">AI Suggested Knowledge Updates</div>
            </div>
            <div v-if="visibleSuggestions.length === 0" class="py-8 text-center text-sm text-muted-foreground">All suggestions have been handled — nice work.</div>
            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <div v-for="s in visibleSuggestions" :key="s.title" class="flex min-w-0 flex-col gap-2.5 rounded-2xl border border-border p-4 transition-all hover:-translate-y-[3px] hover:border-admin-accent hover:shadow-lg">
                    <div class="flex h-[38px] w-[38px] items-center justify-center rounded-[11px]" :style="{ background: s.bg, color: s.color }">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.doc" />
                    </div>
                    <div class="text-[13px] font-bold leading-tight text-foreground">{{ s.title }}</div>
                    <div class="flex-1 text-[11.5px] text-muted-foreground">{{ s.desc }}</div>
                    <button type="button" @click="openAddArticle(s)" class="flex w-full items-center justify-center gap-1.5 rounded-[10px] bg-admin-accent/10 py-[9px] text-xs font-bold text-admin-accent transition-all hover:bg-admin-accent hover:text-on-gold">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.plus" />
                        Create Article
                    </button>
                </div>
                <button type="button" @click="showAllSuggestions = true" class="flex min-w-0 flex-col items-center justify-center gap-2.5 rounded-2xl border-[1.5px] border-dashed border-border p-4 transition-all hover:border-admin-accent hover:bg-admin-accent/5">
                    <div class="flex h-[38px] w-[38px] items-center justify-center rounded-[11px] bg-admin-accent/10 text-admin-accent">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.arrow" />
                    </div>
                    <div class="text-center text-[12.5px] font-bold text-admin-accent">View All Suggestions</div>
                </button>
            </div>
        </div>

        <!-- Ask Sera AI — answer dialog -->
        <Dialog v-model:open="showAnswer">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" class="text-admin-accent"><path d="M12 2l1.8 6.2L20 10l-6.2 1.8L12 18l-1.8-6.2L4 10l6.2-1.8z"/></svg>
                        Sera AI
                    </DialogTitle>
                </DialogHeader>
                <div class="space-y-3 px-6 pb-4">
                    <p class="text-xs font-semibold text-muted-foreground">{{ answerQuestion }}</p>
                    <Separator />
                    <p class="text-sm leading-relaxed text-foreground">{{ answerText }}</p>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showAnswer = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Knowledge Settings dialog -->
        <Dialog v-model:open="showSettings">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Knowledge Settings</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="saveSettings" class="space-y-4 px-6 pb-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Auto-Suggest Articles</Label>
                        <Select v-model="settingsForm.autoSuggest">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="enabled">Enabled</SelectItem>
                                <SelectItem value="disabled">Disabled</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Default Category</Label>
                        <Select v-model="settingsForm.defaultCategory">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in combinedCategories" :key="c.name" :value="c.name">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">AI Confidence Threshold</Label>
                        <Select v-model="settingsForm.confidence">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="strict">Strict</SelectItem>
                                <SelectItem value="balanced">Balanced</SelectItem>
                                <SelectItem value="lenient">Lenient</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DialogFooter class="!px-0 pt-2">
                        <Button type="button" variant="outline" @click="showSettings = false">Cancel</Button>
                        <Button type="submit" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90">Save Settings</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Knowledge Categories — View All dialog -->
        <Dialog v-model:open="showAllCategories">
            <DialogContent class="w-full max-w-lg">
                <DialogHeader>
                    <DialogTitle>All Knowledge Categories</DialogTitle>
                </DialogHeader>
                <div class="grid max-h-[60vh] grid-cols-1 gap-2.5 overflow-y-auto px-6 pb-4 sm:grid-cols-2">
                    <button
                        v-for="c in combinedCategories" :key="c.name" type="button" @click="filterByCategory(c.name)"
                        class="flex min-w-0 items-center gap-[11px] rounded-[13px] border border-border p-[11px] text-left transition-all hover:border-admin-accent hover:bg-admin-accent/5"
                    >
                        <div class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[9px]" :style="{ background: c.bg, color: c.color }">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[c.icon]" />
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-[12.5px] font-bold text-foreground">{{ c.name }}</div>
                            <div class="mt-px text-[11px] text-muted-foreground">{{ c.count }}</div>
                        </div>
                    </button>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showAllCategories = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Add / Create Article dialog (also reached from suggestion cards) -->
        <Dialog v-model:open="showAddArticle">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ pendingSuggestionTitle ? 'Create Article from Suggestion' : 'Add New Article' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitAddArticle" class="space-y-4 px-6 pb-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Title</Label>
                        <Input v-model="addArticleForm.name" placeholder="e.g. How to Process a Refund" required />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs font-medium text-muted-foreground">Category</Label>
                        <Select v-model="addArticleForm.cat">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in combinedCategories" :key="c.name" :value="c.name">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DialogFooter class="!px-0 pt-2">
                        <Button type="button" variant="outline" @click="showAddArticle = false">Cancel</Button>
                        <Button type="submit" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90">Create Article</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Manage Categories dialog -->
        <Dialog v-model:open="showManageCategories">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Manage Categories</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 px-6 pb-4">
                    <form @submit.prevent="submitCategory" class="flex items-end gap-2.5">
                        <div class="flex-1 space-y-1.5">
                            <Label class="text-xs font-medium text-muted-foreground">New Category</Label>
                            <Input v-model="newCategoryForm.name" placeholder="e.g. Marketing & Ads" required />
                        </div>
                        <Button type="submit" class="bg-admin-accent text-on-gold hover:bg-admin-accent/90">Add</Button>
                    </form>
                    <Separator />
                    <div class="max-h-[45vh] space-y-2 overflow-y-auto">
                        <div v-for="c in combinedCategories" :key="c.name" class="flex items-center gap-2.5 rounded-xl border border-border p-2.5">
                            <div class="flex h-8 w-8 flex-none items-center justify-center rounded-[9px]" :style="{ background: c.bg, color: c.color }">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons[c.icon]" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-xs font-semibold text-foreground">{{ c.name }}</div>
                                <div class="text-[11px] text-muted-foreground">{{ c.count }}</div>
                            </div>
                            <button v-if="customCategories.some(cc => cc.name === c.name)" type="button" @click="removeCategory(c.name)" class="flex h-7 w-7 flex-none items-center justify-center rounded-lg text-destructive hover:bg-destructive/10">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.trash" />
                            </button>
                        </div>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showManageCategories = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Review Outdated Articles dialog -->
        <Dialog v-model:open="showOutdated">
            <DialogContent class="w-full max-w-lg">
                <DialogHeader>
                    <DialogTitle>Review Outdated Articles</DialogTitle>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-2 overflow-y-auto px-6 pb-4">
                    <p v-if="remainingOutdated.length === 0" class="py-8 text-center text-sm text-muted-foreground">All flagged articles have been reviewed.</p>
                    <div v-for="a in remainingOutdated" :key="a.name" class="flex items-center gap-3 rounded-xl border border-border p-3">
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[13px] font-semibold text-foreground">{{ a.name }}</div>
                            <div class="mt-0.5 text-[11px] text-muted-foreground">{{ a.cat }} · Updated {{ a.lastUpdated }}</div>
                        </div>
                        <Badge variant="outline" class="flex-none rounded-full border-transparent text-[11px] font-bold" :style="a.status === 'Outdated' ? { background: 'rgba(248,113,113,0.15)', color: '#F87171' } : { background: 'rgba(251,191,36,0.15)', color: '#FBBF24' }">{{ a.status }}</Badge>
                        <Button type="button" size="sm" class="flex-none bg-admin-accent text-on-gold hover:bg-admin-accent/90" @click="markReviewed(a)">Mark Reviewed</Button>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showOutdated = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Upload Documents dialog -->
        <Dialog v-model:open="showUpload">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>Upload Documents</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 px-6 pb-4">
                    <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-[1.5px] border-dashed border-border p-6 text-center transition-colors hover:border-admin-accent hover:bg-admin-accent/5">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-admin-accent" v-html="icons.upload" />
                        <span class="text-xs font-semibold text-foreground">Click to choose files</span>
                        <input type="file" multiple class="hidden" @change="handleFileChange" />
                    </label>
                    <div v-if="uploadedDocs.length" class="max-h-[35vh] space-y-2 overflow-y-auto">
                        <div v-for="d in uploadedDocs" :key="d.name + d.date" class="flex items-center gap-2.5 rounded-xl border border-border p-2.5">
                            <div class="flex h-8 w-8 flex-none items-center justify-center rounded-lg bg-admin-accent/10 text-admin-accent">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.doc" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-xs font-semibold text-foreground">{{ d.name }}</div>
                                <div class="text-[11px] text-muted-foreground">{{ d.size }} · {{ d.date }}</div>
                            </div>
                            <button type="button" @click="removeDoc(d.name)" class="flex h-7 w-7 flex-none items-center justify-center rounded-lg text-destructive hover:bg-destructive/10">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.trash" />
                            </button>
                        </div>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showUpload = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- AI Training Data dialog -->
        <Dialog v-model:open="showTraining">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>AI Training Data</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 px-6 pb-4">
                    <form @submit.prevent="addTrainingSnippet" class="space-y-2">
                        <Label class="text-xs font-medium text-muted-foreground">Add a Q&amp;A or knowledge snippet</Label>
                        <Textarea v-model="newTrainingSnippet" rows="3" placeholder="e.g. Refunds are processed within 7 business days after approval." />
                        <Button type="submit" class="w-full bg-admin-accent text-on-gold hover:bg-admin-accent/90">Add Snippet</Button>
                    </form>
                    <Separator />
                    <div v-if="trainingSnippets.length" class="max-h-[30vh] space-y-2 overflow-y-auto">
                        <div v-for="(t, idx) in trainingSnippets" :key="t.date + idx" class="flex items-start gap-2.5 rounded-xl border border-border p-2.5">
                            <div class="min-w-0 flex-1">
                                <div class="text-xs leading-snug text-foreground">{{ t.text }}</div>
                                <div class="mt-1 text-[11px] text-muted-foreground">{{ t.date }}</div>
                            </div>
                            <button type="button" @click="removeTrainingSnippet(idx)" class="flex h-7 w-7 flex-none items-center justify-center rounded-lg text-destructive hover:bg-destructive/10">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons.trash" />
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-center text-xs text-muted-foreground">No training snippets added yet.</p>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showTraining = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Top Search Queries — View All dialog -->
        <Dialog v-model:open="showAllSearches">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Top Search Queries</DialogTitle>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-1 overflow-y-auto px-6 pb-4">
                    <div v-for="s in searches" :key="s.q" class="flex items-center justify-between gap-3 border-b border-border/60 py-2.5 last:border-b-0">
                        <span class="min-w-0 truncate text-[12.5px] text-foreground/80">{{ s.q }}</span>
                        <span class="flex-none text-[12.5px] font-bold text-foreground">{{ s.n }}</span>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showAllSearches = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Recent AI Interactions — View All dialog -->
        <Dialog v-model:open="showAllInteractions">
            <DialogContent class="w-full max-w-sm">
                <DialogHeader>
                    <DialogTitle>Recent AI Interactions</DialogTitle>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-1 overflow-y-auto px-6 pb-4">
                    <div v-for="i in combinedInteractions" :key="i.q + i.time" class="flex items-start gap-[11px] border-b border-border/60 py-2.5 last:border-b-0">
                        <div class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-lg bg-admin-accent/10 text-[11px] font-extrabold text-admin-accent">Q</div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[12.5px] font-semibold leading-snug text-foreground">{{ i.q }}</div>
                            <div class="mt-0.5 text-[11px] text-muted-foreground">{{ i.time }}</div>
                        </div>
                        <span class="flex-none whitespace-nowrap rounded-md px-2 py-[3px] text-[10px] font-bold" :style="{ background: i.tagBg, color: i.tagColor }">{{ i.tag }}</span>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showAllInteractions = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- AI Suggested Knowledge Updates — View All dialog -->
        <Dialog v-model:open="showAllSuggestions">
            <DialogContent class="w-full max-w-md">
                <DialogHeader>
                    <DialogTitle>AI Suggested Knowledge Updates</DialogTitle>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-2 overflow-y-auto px-6 pb-4">
                    <p v-if="visibleSuggestions.length === 0" class="py-8 text-center text-sm text-muted-foreground">All suggestions have been handled.</p>
                    <div v-for="s in visibleSuggestions" :key="s.title" class="flex items-center gap-3 rounded-xl border border-border p-3">
                        <div class="flex h-9 w-9 flex-none items-center justify-center rounded-[10px]" :style="{ background: s.bg, color: s.color }">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" v-html="icons.doc" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[13px] font-bold text-foreground">{{ s.title }}</div>
                            <div class="truncate text-[11px] text-muted-foreground">{{ s.desc }}</div>
                        </div>
                        <Button type="button" size="sm" class="flex-none bg-admin-accent text-on-gold hover:bg-admin-accent/90" @click="showAllSuggestions = false; openAddArticle(s);">Create</Button>
                    </div>
                </div>
                <DialogFooter class="px-6 pb-4">
                    <Button type="button" variant="outline" @click="showAllSuggestions = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
