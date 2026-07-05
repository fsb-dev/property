<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import ClientSidebar from '@/Components/Client/Sidebar.vue';
import ClientTopBar  from '@/Components/Client/TopBar.vue';
import Toast         from '@/Components/Admin/Toast.vue';
import { useTheme }  from '@/composables/useTheme';

defineProps({
    title:       { type: String, default: 'Dashboard' },
    breadcrumbs: { type: Array,  default: () => [] },
});

const mobileSidebarOpen = ref(false);
const { init } = useTheme();

onMounted(() => {
    init();
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
});

onBeforeUnmount(() => {
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="flex bg-client-surface-page" style="height: 100vh; overflow: hidden;">

        <!-- Sidebar: handles its own fixed/sticky and drawer transition -->
        <ClientSidebar
            :drawer-open="mobileSidebarOpen"
            @close="mobileSidebarOpen = false"
        />

        <!-- Mobile scrim -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-20 bg-black/50 lg:hidden"
                @click="mobileSidebarOpen = false"
            />
        </Transition>

        <!-- Right column: topbar + scrollable page -->
        <div class="flex flex-1 min-w-0 flex-col overflow-hidden">
            <ClientTopBar
                :title="title"
                :breadcrumbs="breadcrumbs"
                @toggle-sidebar="mobileSidebarOpen = !mobileSidebarOpen"
            />

            <!-- Only this scrolls -->
            <main class="min-h-0 flex-1 overflow-y-auto p-6 lg:p-7">
                <slot />
            </main>
        </div>
    </div>

    <Toast />
</template>
