<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    existing:  { type: Array, default: () => [] }, // [{id, thumb, name, url}]
    newFiles:  { type: Array, default: () => [] }, // File[]   — v-model:new-files
    removeIds: { type: Array, default: () => [] }, // number[] — v-model:remove-ids
    accept:    { type: String, default: 'image/jpeg,image/png,image/webp' },
});

const emit = defineEmits(['update:newFiles', 'update:removeIds']);

const input = ref(null);

const visibleExisting = computed(() =>
    props.existing.filter(img => !props.removeIds.includes(img.id))
);

const newPreviews = computed(() =>
    props.newFiles.map(f => URL.createObjectURL(f))
);

const isEmpty = computed(() =>
    visibleExisting.value.length === 0 && newPreviews.value.length === 0
);

function onFileChange(e) {
    const files = Array.from(e.target.files);
    emit('update:newFiles', [...props.newFiles, ...files]);
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
        <!-- Header row -->
        <div class="flex items-center justify-between">
            <slot name="label" />
            <button
                type="button"
                @click="input?.click()"
                class="flex items-center gap-1 text-xs font-medium text-admin-accent transition-colors hover:text-admin-accent/80"
            >
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add images
            </button>
        </div>

        <!-- Thumbnail grid -->
        <div class="grid grid-cols-3 gap-2">
            <!-- Existing server images -->
            <div
                v-for="img in visibleExisting"
                :key="img.id"
                class="group relative aspect-square overflow-hidden rounded-lg border border-border"
            >
                <img :src="img.thumb" :alt="img.name" class="h-full w-full object-cover" />
                <button
                    type="button"
                    @click="removeExisting(img.id)"
                    class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-500"
                >
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Newly selected file previews -->
            <div
                v-for="(src, i) in newPreviews"
                :key="`new-${i}`"
                class="group relative aspect-square overflow-hidden rounded-lg border-2 border-dashed border-admin-accent/40"
            >
                <img :src="src" class="h-full w-full object-cover" alt="" />
                <button
                    type="button"
                    @click="removeNew(i)"
                    class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-500"
                >
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Empty state -->
            <div
                v-if="isEmpty"
                class="col-span-3 flex h-24 cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-border text-xs text-muted-foreground transition-colors hover:border-admin-accent/40"
                @click="input?.click()"
            >
                No gallery images yet — click to add
            </div>
        </div>

        <input ref="input" type="file" :accept="accept" multiple class="sr-only" @change="onFileChange" />
    </div>
</template>
