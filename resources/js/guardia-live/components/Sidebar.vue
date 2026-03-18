<script setup>
import { computed, ref } from 'vue';
import { useGuardiaStore } from '../stores/guardia';
import LiveClock from './LiveClock.vue';
import NoveltyCard from './NoveltyCard.vue';

const store = useGuardiaStore();

// Operational activity feed - tracks real-time events
const operationalEvents = ref([]);
const maxEvents = 5;

function openNoveltyModal() {
    if (window.openNoveltyModal) {
        window.openNoveltyModal();
    }
}

function openAcademyModal() {
    if (window.openAcademyModal) {
        window.openAcademyModal();
    }
}

function addOperationalEvent(type, message, icon, color) {
    operationalEvents.value.unshift({
        id: Date.now() + Math.random(),
        type,
        message,
        icon,
        color,
        timestamp: new Date().toISOString(),
    });
    
    if (operationalEvents.value.length > maxEvents) {
        operationalEvents.value = operationalEvents.value.slice(0, maxEvents);
    }
}

// Expose method globally for event tracking
if (typeof window !== 'undefined') {
    window.addGuardiaEvent = addOperationalEvent;
}

// Compute bed stats
const bedStats = computed(() => {
    const total = Object.keys(store.bedByFirefighter).length;
    const occupied = Object.values(store.bedByFirefighter).filter(b => b).length;
    return { total, occupied, available: total - occupied };
});

// Operational stats
const operationalStatus = computed(() => {
    const allConfirmed = store.unconfirmedCount === 0;
    const hasPresence = store.presentCount > 0;
    
    return {
        allConfirmed,
        hasPresence,
        statusText: allConfirmed && hasPresence ? 'Operativo' : allConfirmed ? 'Sin presencia' : 'Pendiente',
        statusColor: allConfirmed && hasPresence ? 'emerald' : allConfirmed ? 'slate' : 'amber',
    };
});

function formatTime(isoString) {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <aside class="flex flex-col gap-4 h-full overflow-y-auto">

        <!-- Clock widget -->
        <div class="rounded-lg border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="12 6 12 12 16 14" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Hora Local</span>
            </div>
            <LiveClock size="large" />
        </div>

        <!-- Operational Status -->
        <div class="rounded-lg border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Estado Operativo</span>
            </div>
            <div class="mb-3">
                <p class="text-base font-bold"
                    :class="{
                        'text-emerald-400': operationalStatus.statusColor === 'emerald',
                        'text-amber-400': operationalStatus.statusColor === 'amber',
                        'text-slate-400': operationalStatus.statusColor === 'slate',
                    }"
                >
                    {{ operationalStatus.statusText }}
                </p>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div class="text-center p-2 rounded-lg bg-slate-800/50">
                    <p class="text-lg font-bold text-emerald-400 tabular-nums">{{ store.presentCount }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wide">Presentes</p>
                </div>
                <div class="text-center p-2 rounded-lg bg-slate-800/50">
                    <p class="text-lg font-bold tabular-nums"
                        :class="store.unconfirmedCount > 0 ? 'text-amber-400' : 'text-emerald-400'"
                    >
                        {{ store.unconfirmedCount }}
                    </p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wide">Sin confirmar</p>
                </div>
            </div>
        </div>

        <!-- Operational Activity Feed -->
        <div v-if="operationalEvents.length > 0" class="rounded-lg border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Actividad</span>
            </div>
            <ul class="space-y-1.5">
                <li
                    v-for="event in operationalEvents"
                    :key="event.id"
                    class="flex items-start gap-2 p-2 rounded-lg bg-slate-800/50 border border-slate-700/30"
                >
                    <span class="text-sm">{{ event.icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium leading-snug" :class="event.color">{{ event.message }}</p>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">{{ formatTime(event.timestamp) }}</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Camas -->
        <div v-if="bedStats.total > 0" class="rounded-lg border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V6a2 2 0 0 1 2-2h3" />
                        <circle cx="7" cy="9" r="1" />
                    </svg>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Camas</span>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">
                    {{ bedStats.occupied }}/{{ bedStats.total }}
                </span>
            </div>
            <div class="grid grid-cols-5 gap-1.5">
                <div
                    v-for="n in bedStats.total"
                    :key="n"
                    class="aspect-square rounded flex items-center justify-center text-xs font-bold transition-all"
                    :class="Object.values(store.bedByFirefighter).includes(n) 
                        ? 'bg-emerald-500/30 border border-emerald-500/50 text-emerald-300' 
                        : 'bg-slate-700/40 border border-slate-600/40 text-slate-500'"
                >
                    {{ n }}
                </div>
            </div>
        </div>

        <!-- Cumpleaños -->
        <div v-if="store.birthdaysThisMonth.length > 0" class="rounded-lg border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Cumpleaños</span>
                </div>
                <span class="text-[10px] text-slate-500 uppercase font-semibold">Este mes</span>
            </div>
            <ul class="space-y-1.5">
                <li
                    v-for="b in store.birthdaysThisMonth.slice(0, 3)"
                    :key="b.id"
                    class="flex items-center gap-2 p-2 rounded-lg bg-slate-800/50 border border-slate-700/30"
                >
                    <div class="w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white leading-tight truncate">{{ b.name }}</p>
                        <p v-if="b.cargo" class="text-[10px] text-amber-400/70 uppercase">{{ b.cargo }}</p>
                    </div>
                    <div class="text-center px-2 py-1 rounded bg-amber-500/20">
                        <span class="text-xs font-bold text-amber-300">{{ b.day }}</span>
                    </div>
                </li>
            </ul>
        </div>


        <!-- Novedades -->
        <div class="rounded-lg border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Novedades</span>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="store.novelties.length > 0" class="text-xs font-bold px-2 py-0.5 rounded bg-blue-500/20 text-blue-400">
                        {{ store.novelties.length }}
                    </span>
                    <button
                        @click="openNoveltyModal"
                        class="w-6 h-6 rounded flex items-center justify-center transition-all bg-slate-700 border border-slate-600 hover:bg-slate-600"
                        title="Agregar Novedad"
                    >
                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="store.novelties.length === 0" class="text-xs text-slate-600 py-3 text-center bg-slate-700/20 rounded-lg">
                Sin novedades recientes
            </div>
            <div v-else class="space-y-2">
                <NoveltyCard 
                    v-for="n in store.novelties.slice(0, 3)" 
                    :key="n.id" 
                    :novelty="n" 
                />
            </div>
        </div>

        <!-- Academias -->
        <div class="rounded-lg border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Academias</span>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="store.academies.length > 0" class="text-xs font-bold px-2 py-0.5 rounded bg-purple-500/20 text-purple-400">
                        {{ store.academies.length }}
                    </span>
                    <button
                        @click="openAcademyModal"
                        class="w-6 h-6 rounded flex items-center justify-center transition-all bg-slate-700 border border-slate-600 hover:bg-slate-600"
                        title="Agregar Academia"
                    >
                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="store.academies.length === 0" class="text-xs text-slate-600 py-3 text-center bg-slate-700/20 rounded-lg">
                Sin academias registradas
            </div>
            <ul v-else class="space-y-1.5">
                <li
                    v-for="a in store.academies.slice(0, 3)"
                    :key="a.id"
                    class="rounded-lg bg-slate-700/30 hover:bg-slate-700/50 px-2.5 py-2 border border-slate-700/30 transition-all cursor-pointer"
                >
                    <p class="text-xs text-slate-300 leading-snug line-clamp-2">{{ a.content }}</p>
                    <p v-if="a.firefighter_name" class="text-[10px] text-purple-400/80 mt-1">
                        👤 {{ a.firefighter_name }}
                    </p>
                </li>
            </ul>
        </div>

    </aside>
</template>
