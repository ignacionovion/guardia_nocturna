<script setup>
import { computed, ref } from 'vue';
import { useGuardiaStore } from '../stores/guardia';
import LiveClock from './LiveClock.vue';

const store = useGuardiaStore();

// Operational activity feed - tracks real-time events
const operationalEvents = ref([]);
const maxEvents = 5;

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
        <div class="rounded-xl border border-slate-700/50 bg-gradient-to-br from-slate-800 to-slate-900 p-4 shadow-xl">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Hora Local
                </span>
            </div>
            <LiveClock size="large" />
        </div>

        <!-- Operational Status -->
        <div class="rounded-xl border bg-gradient-to-br p-4 shadow-xl"
            :class="{
                'border-emerald-500/30 from-emerald-900/10 to-slate-800': operationalStatus.statusColor === 'emerald',
                'border-amber-500/30 from-amber-900/10 to-slate-800': operationalStatus.statusColor === 'amber',
                'border-slate-600/30 from-slate-800 to-slate-900': operationalStatus.statusColor === 'slate',
            }"
        >
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl"
                    :class="{
                        'bg-emerald-500/20': operationalStatus.statusColor === 'emerald',
                        'bg-amber-500/20': operationalStatus.statusColor === 'amber',
                        'bg-slate-700/50': operationalStatus.statusColor === 'slate',
                    }"
                >
                    {{ operationalStatus.statusColor === 'emerald' ? '✓' : operationalStatus.statusColor === 'amber' ? '⚠' : '○' }}
                </div>
                <div class="flex-1">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wide font-semibold">Estado</p>
                    <p class="text-base font-bold uppercase tracking-wide"
                        :class="{
                            'text-emerald-400': operationalStatus.statusColor === 'emerald',
                            'text-amber-400': operationalStatus.statusColor === 'amber',
                            'text-slate-400': operationalStatus.statusColor === 'slate',
                        }"
                    >
                        {{ operationalStatus.statusText }}
                    </p>
                </div>
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
        <div v-if="operationalEvents.length > 0" class="rounded-xl border border-blue-500/30 bg-gradient-to-br from-blue-900/10 to-slate-800 p-3 shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-base">⚡</span>
                <span class="text-xs font-bold text-blue-400 uppercase tracking-wide">Actividad</span>
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
        <div v-if="bedStats.total > 0" class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">🛏️</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Camas</span>
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
        <div v-if="store.birthdaysThisMonth.length > 0" class="rounded-xl border border-amber-500/30 bg-gradient-to-br from-amber-900/10 to-slate-800 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎂</span>
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-wide">Cumpleaños</span>
                </div>
                <span class="text-[10px] text-amber-500/70 uppercase font-semibold">Este mes</span>
            </div>
            <ul class="space-y-1.5">
                <li
                    v-for="b in store.birthdaysThisMonth.slice(0, 3)"
                    :key="b.id"
                    class="flex items-center gap-2 p-2 rounded-lg bg-slate-800/50 border border-slate-700/30"
                >
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-base">
                        🎉
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
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">📋</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Novedades</span>
                </div>
                <span v-if="store.novelties.length > 0" class="text-xs font-bold px-2 py-0.5 rounded bg-blue-500/20 text-blue-400">
                    {{ store.novelties.length }}
                </span>
            </div>

            <div v-if="store.novelties.length === 0" class="text-xs text-slate-600 py-3 text-center bg-slate-700/20 rounded-lg">
                Sin novedades recientes
            </div>
            <ul v-else class="space-y-1.5">
                <li
                    v-for="n in store.novelties.slice(0, 3)"
                    :key="n.id"
                    class="rounded-lg bg-slate-700/30 hover:bg-slate-700/50 px-2.5 py-2 border border-slate-700/30 transition-all cursor-pointer"
                >
                    <p class="text-xs text-slate-300 leading-snug line-clamp-2">{{ n.content }}</p>
                    <div class="flex items-center justify-between mt-1">
                        <p v-if="n.user_name" class="text-[10px] text-slate-500">{{ n.user_name }}</p>
                        <span v-if="n.is_permanent" class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400">Permanente</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Academias -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-3 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎓</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Academias</span>
                </div>
                <span v-if="store.academies.length > 0" class="text-xs font-bold px-2 py-0.5 rounded bg-purple-500/20 text-purple-400">
                    {{ store.academies.length }}
                </span>
            </div>

            <div v-if="store.academies.length === 0" class="text-xs text-slate-600 py-3 text-center bg-slate-700/20 rounded-lg">
                Sin academias registradas
            </div>
            <ul v-else class="space-y-1.5">
                <li
                    v-for="a in store.academies.slice(0, 3)"
                    :key="a.id"
                    class="rounded-lg bg-gradient-to-r from-purple-900/10 to-slate-700/30 hover:from-purple-900/20 hover:to-slate-700/40 px-2.5 py-2 border border-purple-500/20 transition-all cursor-pointer"
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
