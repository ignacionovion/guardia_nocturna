<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { useGuardiaStore } from '../stores/guardia';
import LiveHeader from '../components/LiveHeader.vue';
import FirefighterGrid from '../components/FirefighterGrid.vue';
import Sidebar from '../components/Sidebar.vue';
import AseoModal from '../components/AseoModal.vue';
import EmergenciasModal from '../components/EmergenciasModal.vue';
import RefuerzoModal from '../components/RefuerzoModal.vue';
import ReemplazoModal from '../components/ReemplazoModal.vue';

const store = useGuardiaStore();
let refreshInterval = null;
const broadcastConnected = ref(false);

// Phase 5: Initialize broadcasting with polling fallback
function initBroadcasting() {
    if (!window.Echo || !store.guardiaId || !store.tenantId) {
        console.warn('[Broadcasting] Echo not available or missing IDs');
        return false;
    }

    try {
        const channelName = `tenant.${store.tenantId}.guardia.${store.guardiaId}`;
        console.log('[Broadcasting] Connecting to channel:', channelName);

        const channel = window.Echo.channel(channelName);

        // Listen to all events
        channel.listen('.bombero.status.updated', (event) => {
            console.log('[Broadcasting] Bombero status updated:', event);
            store.refreshState();
        });

        channel.listen('.bombero.confirmed', (event) => {
            console.log('[Broadcasting] Bombero confirmed:', event);
            store.refreshState();
        });

        channel.listen('.refuerzo.added', (event) => {
            console.log('[Broadcasting] Refuerzo added:', event);
            store.refreshState();
        });

        channel.listen('.replacement.assigned', (event) => {
            console.log('[Broadcasting] Replacement assigned:', event);
            store.refreshState();
        });

        channel.listen('.emergencia.created', (event) => {
            console.log('[Broadcasting] Emergencia created:', event);
            store.refreshState();
        });

        channel.listen('.aseo.updated', (event) => {
            console.log('[Broadcasting] Aseo updated:', event);
            store.refreshState();
        });

        broadcastConnected.value = true;
        console.log('[Broadcasting] Connected successfully');
        return true;
    } catch (error) {
        console.error('[Broadcasting] Failed to connect:', error);
        return false;
    }
}

onMounted(() => {
    // Try to initialize broadcasting
    const connected = initBroadcasting();

    // Start polling with adaptive interval
    // 60s if broadcasting is active, 30s if not
    const pollingInterval = connected ? 60000 : 30000;
    
    refreshInterval = setInterval(() => {
        store.refreshState();
    }, pollingInterval);

    console.log(`[Polling] Started with ${pollingInterval}ms interval (broadcasting: ${connected})`);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        console.log('[Polling] Stopped');
    }
    
    // Disconnect from broadcasting
    if (broadcastConnected.value && window.Echo && store.guardiaId && store.tenantId) {
        const channelName = `tenant.${store.tenantId}.guardia.${store.guardiaId}`;
        window.Echo.leave(channelName);
        console.log('[Broadcasting] Disconnected');
    }
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

        <!-- Save result toast (fixed, always visible) -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div
                v-if="store.saveResult"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl border text-sm font-semibold max-w-sm w-full"
                :class="store.saveResult.ok
                    ? 'bg-emerald-900/95 border-emerald-600/50 text-emerald-200 shadow-emerald-900/60'
                    : 'bg-red-900/95 border-red-600/50 text-red-200 shadow-red-900/60'"
            >
                <!-- Icon -->
                <span v-if="store.saveResult.ok" class="text-emerald-400 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span v-else class="text-red-400 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </span>
                <span class="flex-1 leading-snug">{{ store.saveResult.message }}</span>
                <button
                    type="button"
                    class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity"
                    @click="store.saveResult = null"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </Transition>

        <!-- Phase 4 Modals -->
        <AseoModal />
        <EmergenciasModal />
        <RefuerzoModal />
        <ReemplazoModal />
    </div>
</template>
