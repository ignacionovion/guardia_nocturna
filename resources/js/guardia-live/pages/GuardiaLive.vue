<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';
import LiveHeader from '../components/LiveHeader.vue';
import FirefighterGrid from '../components/FirefighterGrid.vue';
import Sidebar from '../components/Sidebar.vue';

const store = useGuardiaStore();
let refreshInterval = null;

onMounted(() => {
    refreshInterval = setInterval(() => {
        store.refreshState();
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
    <div class="w-full min-h-screen text-slate-100" style="font-family: 'Inter', system-ui, sans-serif;">
        <LiveHeader />

        <main class="px-4 md:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_400px] gap-6">
                <FirefighterGrid />
                <Sidebar />
            </div>
        </main>
    </div>
</template>
