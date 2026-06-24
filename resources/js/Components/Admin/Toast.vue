<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toasts = ref([]);
let uid = 0;

watch(
    () => page.props.flash?.toast,
    (toast) => {
        if (!toast?.message) return;
        const id = ++uid;
        toasts.value.push({ id, type: toast.type ?? 'info', message: toast.message });
        setTimeout(() => dismiss(id), 4500);
    },
    { immediate: true }
);

function dismiss(id) {
    toasts.value = toasts.value.filter(t => t.id !== id);
}

const config = {
    success: {
        wrap: 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/20',
        text: 'text-emerald-800 dark:text-emerald-300',
        icon: 'text-emerald-500',
        path: '<path d="M20 6 9 17l-5-5"/>',
    },
    error: {
        wrap: 'bg-red-50 border-red-200 dark:bg-red-500/10 dark:border-red-500/20',
        text: 'text-red-800 dark:text-red-300',
        icon: 'text-red-500',
        path: '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>',
    },
    warning: {
        wrap: 'bg-amber-50 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20',
        text: 'text-amber-800 dark:text-amber-300',
        icon: 'text-amber-500',
        path: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><path d="M12 9v4M12 17h.01"/>',
    },
    info: {
        wrap: 'bg-blue-50 border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/20',
        text: 'text-blue-800 dark:text-blue-300',
        icon: 'text-blue-500',
        path: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
    },
};
</script>

<template>
    <Teleport to="body">
        <div class="pointer-events-none fixed bottom-6 right-6 z-[9998] flex flex-col items-end gap-2.5">
            <TransitionGroup
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-x-6 scale-95"
                enter-to-class="opacity-100 translate-x-0 scale-100"
                leave-active-class="transition duration-200 ease-in absolute"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-6"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto flex w-full min-w-[280px] max-w-[380px] items-start gap-3 rounded-xl border px-4 py-3.5 shadow-lg shadow-black/10 dark:shadow-black/40',
                        config[toast.type]?.wrap ?? config.info.wrap,
                    ]"
                >
                    <!-- Icon -->
                    <svg
                        width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                        class="mt-0.5 flex-none"
                        :class="config[toast.type]?.icon ?? config.info.icon"
                        v-html="config[toast.type]?.path ?? config.info.path"
                    />

                    <!-- Message -->
                    <p
                        class="flex-1 text-sm font-medium leading-snug"
                        :class="config[toast.type]?.text ?? config.info.text"
                    >
                        {{ toast.message }}
                    </p>

                    <!-- Dismiss -->
                    <button
                        @click="dismiss(toast.id)"
                        :class="['mt-0.5 flex-none opacity-50 transition-opacity hover:opacity-100', config[toast.type]?.text ?? config.info.text]"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
