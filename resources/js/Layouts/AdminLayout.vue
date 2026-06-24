<script setup>
import { ref, onMounted } from 'vue';
import AdminSidebar from '@/Components/Admin/Sidebar.vue';
import AdminTopBar  from '@/Components/Admin/TopBar.vue';
import { useTheme } from '@/composables/useTheme';

defineProps({
    title:       { type: String, default: 'Dashboard' },
    breadcrumbs: { type: Array,  default: () => [] },
});

const mobileSidebarOpen = ref(false);
const { init } = useTheme();

onMounted(() => {
    // Sync Vue's reactive isDark with whatever the no-FOUC script already applied.
    init();
});
</script>

<template>
    <div
        class="flex bg-admin-surface-page transition-colors duration-200"
        style="height:100vh; overflow:hidden; font-family:'Plus Jakarta Sans',system-ui,sans-serif;"
    >
        <!-- Sidebar: sticky on desktop, drawer on mobile -->
        <div
            class="fixed inset-y-0 left-0 z-30 transition-transform duration-300 lg:static lg:translate-x-0"
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <AdminSidebar />
        </div>

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
        <div class="flex flex-1 flex-col min-w-0 overflow-hidden">
            <AdminTopBar
                :title="title"
                :breadcrumbs="breadcrumbs"
                @toggle-sidebar="mobileSidebarOpen = !mobileSidebarOpen"
            />

            <!-- Only this scrolls -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-7">
                <slot />
            </main>
        </div>
    </div>
</template>
