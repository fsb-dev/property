<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    file:    { default: null },          // v-model:file  — File | null
    removed: { type: Boolean, default: false }, // v-model:removed — true = delete server image
    preview: { type: String,  default: null },  // URL of existing server image
    hint:    { type: String,  default: 'JPG, PNG, WEBP · Max 5 MB' },
    accept:  { type: String,  default: 'image/jpeg,image/png,image/webp' },
    label:   { type: String,  default: 'Click to upload' },
});

const emit = defineEmits(['update:file', 'update:removed']);

const input = ref(null);

const displayUrl = computed(() => {
    if (props.removed)              return null;
    if (props.file instanceof File) return URL.createObjectURL(props.file);
    return props.preview ?? null;
});

const isPdf = computed(() => {
    if (props.file instanceof File) return props.file.type === 'application/pdf';
    const url = props.preview ?? '';
    return url.toLowerCase().endsWith('.pdf');
});

const displayName = computed(() => {
    if (props.file instanceof File) return props.file.name;
    if (props.preview) return decodeURIComponent(props.preview.split('/').pop().split('?')[0]);
    return null;
});

function onChange(e) {
    const f = e.target.files[0];
    if (f) { emit('update:file', f); emit('update:removed', false); }
    e.target.value = '';
}

function remove() {
    if (props.file instanceof File) {
        emit('update:file', null);
    } else {
        emit('update:removed', true);
    }
    if (input.value) input.value.value = '';
}
</script>

<template>
    <div>
        <div
            class="group relative flex h-44 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-slate-50 transition-colors dark:bg-white/[0.02]"
            :class="displayUrl ? 'border-border' : 'border-border hover:border-admin-accent/50'"
            @click="input?.click()"
        >
            <!-- PDF preview -->
            <div v-if="displayUrl && isPdf" class="flex flex-col items-center gap-2 px-4 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 dark:bg-red-500/20">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 dark:text-red-400">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                </div>
                <p class="max-w-[90%] truncate text-sm font-medium text-foreground">{{ displayName }}</p>
                <p class="text-xs text-muted-foreground">PDF Document</p>
            </div>

            <!-- Image preview -->
            <img v-else-if="displayUrl" :src="displayUrl" class="h-full w-full object-contain" alt="" />

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center gap-2 text-muted-foreground">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <p class="text-sm font-medium">{{ label }}</p>
                <p class="text-xs opacity-60">{{ hint }}</p>
            </div>

            <!-- Replace overlay (shows on hover when image present) -->
            <div v-if="displayUrl" class="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/20">
                <span class="rounded-lg bg-black/60 px-3 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                    Click to replace
                </span>
            </div>

            <!-- Remove button -->
            <button
                v-if="displayUrl"
                type="button"
                @click.stop="remove"
                class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-500"
            >
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <input ref="input" type="file" :accept="accept" class="sr-only" @change="onChange" />
    </div>
</template>
