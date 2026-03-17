<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const props = defineProps({
    size: {
        type: String,
        default: 'normal', // 'normal' | 'large'
    },
});

const store = useGuardiaStore();
const displayTime = ref('--:--:--');
const isOnline = ref(true);
let timer = null;

const timeClass = computed(() => {
    return props.size === 'large' 
        ? 'text-5xl font-black tracking-widest' 
        : 'text-4xl font-black tracking-wider';
});

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
        <div 
            class="tabular-nums text-white font-mono transition-all duration-300"
            :class="timeClass"
        >
            {{ displayTime }}
        </div>
        <div 
            class="mt-1 flex items-center justify-center gap-1.5"
            :class="{ 'mt-2': size === 'large' }"
        >
            <span
                class="inline-block rounded-full transition-all"
                :class="[
                    isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-red-400',
                    size === 'large' ? 'w-2.5 h-2.5' : 'w-2 h-2'
                ]"
            ></span>
            <span 
                class="font-medium transition-all" 
                :class="[
                    isOnline ? 'text-emerald-400' : 'text-red-400',
                    size === 'large' ? 'text-sm font-bold' : 'text-xs'
                ]"
            >
                {{ isOnline ? 'EN LÍNEA' : 'SIN CONEXIÓN' }}
            </span>
        </div>
    </div>
</template>
