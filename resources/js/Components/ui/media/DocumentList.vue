<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    existing:  { type: Array,  default: () => [] }, // [{id, name, mime, size, url}]
    newFiles:  { type: Array,  default: () => [] }, // File[] — v-model:new-files
    removeIds: { type: Array,  default: () => [] }, // number[] — v-model:remove-ids
    accept:    { type: String, default: '.pdf,.doc,.docx,.xls,.xlsx' },
});

const emit = defineEmits(['update:newFiles', 'update:removeIds']);

const input = ref(null);

const visibleExisting = computed(() =>
    props.existing.filter(doc => !props.removeIds.includes(doc.id))
);

const isEmpty = computed(() =>
    visibleExisting.value.length === 0 && props.newFiles.length === 0
);

function formatSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
}

function iconColor(mimeOrName) {
    const s = (mimeOrName ?? '').toLowerCase();
    if (s.includes('pdf'))   return 'text-red-500   bg-red-50   dark:bg-red-500/10';
    if (s.includes('word') || s.includes('.doc'))
                             return 'text-blue-500  bg-blue-50  dark:bg-blue-500/10';
    if (s.includes('sheet') || s.includes('excel') || s.includes('.xls'))
                             return 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10';
    return 'text-slate-500 bg-slate-100 dark:bg-white/[0.06]';
}

function onFileChange(e) {
    emit('update:newFiles', [...props.newFiles, ...Array.from(e.target.files)]);
    e.target.value = '';
}

function removeExisting(id) {
    emit('update:removeIds', [...props.removeIds, id]);
}

function removeNew(idx) {
    emit('update:newFiles', props.newFiles.filter((_, i) => i !== idx));
}
</script>

<template>
    <div class="space-y-2">

        <!-- File list -->
        <div v-if="!isEmpty" class="overflow-hidden rounded-xl border border-border divide-y divide-border">

            <!-- Existing server documents -->
            <div
                v-for="doc in visibleExisting"
                :key="doc.id"
                class="flex items-center gap-3 px-4 py-3"
            >
                <div :class="['flex h-9 w-9 flex-none items-center justify-center rounded-lg text-xs font-bold', iconColor(doc.mime ?? doc.name)]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <a :href="doc.url" target="_blank" class="truncate block text-sm font-medium text-foreground hover:text-admin-accent transition-colors">
                        {{ doc.name }}
                    </a>
                    <p v-if="doc.size" class="text-xs text-muted-foreground">{{ formatSize(doc.size) }}</p>
                </div>
                <button
                    type="button"
                    @click="removeExisting(doc.id)"
                    class="flex h-7 w-7 flex-none items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Newly selected (pending upload) -->
            <div
                v-for="(file, i) in newFiles"
                :key="`new-${i}`"
                class="flex items-center gap-3 bg-admin-accent/[0.03] px-4 py-3"
            >
                <div :class="['flex h-9 w-9 flex-none items-center justify-center rounded-lg text-xs font-bold', iconColor(file.type ?? file.name)]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-foreground">{{ file.name }}</p>
                    <p class="text-xs text-admin-accent">Will upload on save · {{ formatSize(file.size) }}</p>
                </div>
                <button
                    type="button"
                    @click="removeNew(i)"
                    class="flex h-7 w-7 flex-none items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

        </div>

        <!-- Empty state -->
        <div
            v-if="isEmpty"
            class="flex h-20 cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-border text-xs text-muted-foreground transition-colors hover:border-admin-accent/40"
            @click="input?.click()"
        >
            No documents attached — click to add
        </div>

        <!-- Add button -->
        <button
            type="button"
            @click="input?.click()"
            class="flex items-center gap-1.5 text-xs font-medium text-admin-accent transition-colors hover:text-admin-accent/80"
        >
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add documents
        </button>

        <input ref="input" type="file" :accept="accept" multiple class="sr-only" @change="onFileChange" />
    </div>
</template>
