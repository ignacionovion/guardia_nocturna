<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();
const displayTime = ref('--:--:--');
const isOnline = ref(true);
let timer = null;

function tick() {
    const tz = store.guardiaTz || 'America/Santiago';
    try {
        displayTime.value = new Intl.DateTimeFormat('es-CL', {
            timeZone: tz,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        }).format(new Date());
        isOnline.value = true;
    } catch {
        displayTime.value = new Date().toLocaleTimeString('es-CL', { hour12: false });
    }
}

onMounted(() => {
    tick();
    timer = setInterval(tick, 1000);
    window.addEventListener('online', () => (isOnline.value = true));
    window.addEventListener('offline', () => (isOnline.value = false));
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<template>
    <div class="text-center">
        <div class="text-4xl font-black tabular-nums tracking-wider text-white font-mono">
            {{ displayTime }}
        </div>
        <div class="mt-1 flex items-center justify-center gap-1.5">
            <span
                class="inline-block w-2 h-2 rounded-full"
                :class="isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-red-400'"
            ></span>
            <span class="text-xs font-medium" :class="isOnline ? 'text-emerald-400' : 'text-red-400'">
                {{ isOnline ? 'EN LÍNEA' : 'SIN CONEXIÓN' }}
            </span>
        </div>
    </div>
</template>
