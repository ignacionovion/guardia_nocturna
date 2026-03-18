<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
  novelty: {
    type: Object,
    required: true
  }
});

// Tiempo de expiración por tipo de novedad (en minutos)
const EXPIRY_MINUTES = {
  'Informativa': 60,
  'Incidente': 120,
  'Mantención': 180,
  'default': 60
};

const timeLeft = ref('');
const isExpired = ref(false);
const isUrgent = ref(false);
let intervalId = null;

function updateCountdown() {
  if (!props.novelty.created_at) {
    timeLeft.value = '';
    return;
  }

  const createdAt = new Date(props.novelty.created_at);
  const expiryMinutes = EXPIRY_MINUTES[props.novelty.type] || EXPIRY_MINUTES.default;
  const expiryTime = new Date(createdAt.getTime() + expiryMinutes * 60000);
  const now = new Date();
  const diff = expiryTime - now;

  if (diff <= 0) {
    isExpired.value = true;
    timeLeft.value = 'Expirada';
    isUrgent.value = false;
    return;
  }

  isExpired.value = false;
  
  // Urgente si quedan menos de 10 minutos
  isUrgent.value = diff < 10 * 60000;

  const minutes = Math.floor(diff / 60000);
  const seconds = Math.floor((diff % 60000) / 1000);
  
  if (minutes > 0) {
    timeLeft.value = `${minutes}m ${seconds}s`;
  } else {
    timeLeft.value = `${seconds}s`;
  }
}

function startCountdown() {
  updateCountdown();
  intervalId = setInterval(updateCountdown, 1000);
}

function stopCountdown() {
  if (intervalId) {
    clearInterval(intervalId);
    intervalId = null;
  }
}

// Badge color por tipo
const typeBadge = computed(() => {
  const badges = {
    'Informativa': { bg: 'bg-blue-500/20', text: 'text-blue-400', border: 'border-blue-500/30', icon: 'ℹ️' },
    'Incidente': { bg: 'bg-red-500/20', text: 'text-red-400', border: 'border-red-500/30', icon: '⚠️' },
    'Mantención': { bg: 'bg-amber-500/20', text: 'text-amber-400', border: 'border-amber-500/30', icon: '🔧' },
    'Academia': { bg: 'bg-purple-500/20', text: 'text-purple-400', border: 'border-purple-500/30', icon: '🎓' }
  };
  return badges[props.novelty.type] || badges['Informativa'];
});

// Formatear hora
const formattedTime = computed(() => {
  if (!props.novelty.created_at) return '';
  const date = new Date(props.novelty.created_at);
  return date.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
});

watch(() => props.novelty, () => {
  stopCountdown();
  startCountdown();
}, { immediate: true });

onUnmounted(() => {
  stopCountdown();
});
</script>

<template>
  <div 
    class="rounded-lg border px-3 py-3 transition-all hover:scale-[1.02]"
    :class="[
      typeBadge.bg,
      typeBadge.border,
      isExpired ? 'opacity-50' : 'opacity-100'
    ]"
  >
    <!-- Header: Badge tipo -->
    <div class="flex items-start justify-between gap-2 mb-2">
      <span 
        class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold whitespace-nowrap"
        :class="[typeBadge.bg, typeBadge.text, typeBadge.border, 'border']"
      >
        {{ typeBadge.icon }} {{ novelty.type }}
      </span>
    </div>

    <!-- Contenido -->
    <p class="text-sm font-medium leading-relaxed mb-3" :class="typeBadge.text">
      {{ novelty.content }}
    </p>

    <!-- Footer: Autor + Hora + Countdown -->
    <div class="flex items-center justify-between gap-2 text-xs">
      <div class="flex items-center gap-2">
        <span class="font-semibold" :class="typeBadge.text">
          {{ novelty.user_name || novelty.user?.name || 'Anónimo' }}
        </span>
        <span class="opacity-60">• {{ formattedTime }}</span>
        <span v-if="novelty.is_permanent" class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400 text-[10px]">
          📌 Permanente
        </span>
      </div>

      <!-- Countdown -->
      <div 
        v-if="!novelty.is_permanent && !isExpired"
        class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold"
        :class="[
          isUrgent ? 'bg-red-500/30 text-red-400 animate-pulse' : 'bg-slate-800/50 text-slate-400'
        ]"
      >
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M12 6v6l4 2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        {{ timeLeft }}
      </div>
      <div 
        v-else-if="isExpired && !novelty.is_permanent"
        class="px-2 py-1 rounded-full text-[10px] font-bold bg-slate-700 text-slate-500"
      >
        ✓ Expirada
      </div>
    </div>
  </div>
</template>
