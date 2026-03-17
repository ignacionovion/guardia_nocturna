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
    <aside class="flex flex-col gap-3 h-full overflow-y-auto">

        <!-- Clock widget - operational prominence -->
        <div class="rounded-xl border-2 border-slate-700/50 bg-gradient-to-br from-slate-800 to-slate-900 p-5 shadow-2xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shadow-lg shadow-emerald-500/50"></span>
                    Hora Local
                </span>
                <span class="text-[10px] text-slate-500 uppercase font-semibold">{{ store.guardiaTz }}</span>
            </div>
            <LiveClock size="large" />
        </div>

        <!-- Operational Status -->
        <div class="rounded-xl border-2 bg-gradient-to-br p-4 shadow-xl"
            :class="{
                'border-emerald-500/40 from-emerald-900/20 to-slate-800': operationalStatus.statusColor === 'emerald',
                'border-amber-500/40 from-amber-900/20 to-slate-800': operationalStatus.statusColor === 'amber',
                'border-slate-600/40 from-slate-800 to-slate-900': operationalStatus.statusColor === 'slate',
            }"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl shadow-lg"
                        :class="{
                            'bg-emerald-500/20': operationalStatus.statusColor === 'emerald',
                            'bg-amber-500/20': operationalStatus.statusColor === 'amber',
                            'bg-slate-700/50': operationalStatus.statusColor === 'slate',
                        }"
                    >
                        {{ operationalStatus.statusColor === 'emerald' ? '✓' : operationalStatus.statusColor === 'amber' ? '⚠' : '○' }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Estado</p>
                        <p class="text-lg font-extrabold uppercase tracking-wide"
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
            </div>
            <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-700/30">
                <div class="text-center p-2 rounded-lg bg-slate-800/50">
                    <p class="text-xl font-bold text-emerald-400 tabular-nums">{{ store.presentCount }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wide">Presentes</p>
                </div>
                <div class="text-center p-2 rounded-lg bg-slate-800/50">
                    <p class="text-xl font-bold tabular-nums"
                        :class="store.unconfirmedCount > 0 ? 'text-amber-400' : 'text-emerald-400'"
                    >
                        {{ store.unconfirmedCount }}
                    </p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wide">Sin confirmar</p>
                </div>
            </div>
        </div>

        <!-- Operational Activity Feed -->
        <div v-if="operationalEvents.length > 0" class="rounded-xl border border-blue-500/30 bg-gradient-to-br from-blue-900/20 to-slate-800 p-4 shadow-xl">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">⚡</span>
                <span class="text-xs font-bold text-blue-400 uppercase tracking-widest">Actividad Operativa</span>
            </div>
            <ul class="space-y-2">
                <li
                    v-for="event in operationalEvents"
                    :key="event.id"
                    class="flex items-start gap-2 p-2.5 rounded-lg bg-slate-800/50 border border-slate-700/30"
                >
                    <span class="text-base">{{ event.icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium leading-snug" :class="event.color">{{ event.message }}</p>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">{{ formatTime(event.timestamp) }}</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Camas - operational bed status -->
        <div v-if="bedStats.total > 0" class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛏️</span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">Camas</span>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-400">
                    {{ bedStats.occupied }}/{{ bedStats.total }}
                </span>
            </div>
            <div class="grid grid-cols-5 gap-2">
                <div
                    v-for="n in bedStats.total"
                    :key="n"
                    class="aspect-square rounded-lg flex items-center justify-center text-sm font-bold transition-all shadow-md"
                    :class="Object.values(store.bedByFirefighter).includes(n) 
                        ? 'bg-emerald-500/30 border-2 border-emerald-500/50 text-emerald-300 shadow-emerald-900/50' 
                        : 'bg-slate-700/40 border-2 border-slate-600/40 text-slate-500'"
                >
                    {{ n }}
                </div>
            </div>
        </div>

        <!-- Cumpleaños - operational prominence -->
        <div v-if="store.birthdaysThisMonth.length > 0" class="rounded-xl border-2 border-amber-500/30 bg-gradient-to-br from-amber-900/20 to-slate-800 p-4 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🎂</span>
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">Cumpleaños</span>
                </div>
                <span class="text-[10px] text-amber-500/70 uppercase font-semibold px-2 py-1 rounded-lg bg-amber-500/10">Este mes</span>
            </div>
            <ul class="space-y-2">
                <li
                    v-for="b in store.birthdaysThisMonth.slice(0, 3)"
                    :key="b.id"
                    class="flex items-center gap-3 p-2 rounded-lg bg-slate-800/50 border border-slate-700/30"
                >
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-xl shadow-xl">
                        🎉
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white leading-tight truncate">{{ b.name }}</p>
                        <p v-if="b.cargo" class="text-[10px] text-amber-400/70 uppercase font-semibold">{{ b.cargo }}</p>
                    </div>
                    <div class="text-center px-2.5 py-1.5 rounded-lg bg-amber-500/20 border-2 border-amber-500/30">
                        <span class="text-sm font-bold text-amber-300">{{ b.day }}</span>
                        <span class="text-[10px] text-amber-500/70 block uppercase font-semibold">MAR</span>
                    </div>
                </li>
            </ul>
        </div>


        <!-- Novedades - operational presentation -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📋</span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">Novedades</span>
                </div>
                <span v-if="store.novelties.length > 0" class="text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-500/20 border border-blue-500/30 text-blue-400">
                    {{ store.novelties.length }}
                </span>
            </div>

            <div v-if="store.novelties.length === 0" class="text-xs text-slate-600 py-3 text-center bg-slate-700/20 rounded-lg">
                Sin novedades recientes
            </div>
            <ul v-else class="space-y-2">
                <li
                    v-for="n in store.novelties.slice(0, 3)"
                    :key="n.id"
                    class="group rounded-lg bg-slate-700/30 hover:bg-slate-700/50 px-3 py-2.5 border border-slate-700/30 transition-all cursor-pointer"
                >
                    <p class="text-xs text-slate-300 leading-snug line-clamp-2 group-hover:text-white transition-colors">{{ n.content }}</p>
                    <div class="flex items-center justify-between mt-1.5">
                        <p v-if="n.user_name" class="text-[10px] text-slate-500">{{ n.user_name }}</p>
                        <span v-if="n.is_permanent" class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400">Permanente</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Academias - operational presentation -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎓</span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">Academias</span>
                </div>
                <span v-if="store.academies.length > 0" class="text-xs font-bold px-2.5 py-1 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400">
                    {{ store.academies.length }}
                </span>
            </div>

            <div v-if="store.academies.length === 0" class="text-xs text-slate-600 py-3 text-center bg-slate-700/20 rounded-lg">
                Sin academias registradas
            </div>
            <ul v-else class="space-y-2">
                <li
                    v-for="a in store.academies.slice(0, 3)"
                    :key="a.id"
                    class="group rounded-lg bg-gradient-to-r from-purple-900/20 to-slate-700/30 hover:from-purple-900/30 hover:to-slate-700/40 px-3 py-2.5 border border-purple-500/20 transition-all cursor-pointer"
                >
                    <p class="text-xs text-slate-300 leading-snug line-clamp-2 group-hover:text-white transition-colors">{{ a.content }}</p>
                    <p v-if="a.firefighter_name" class="text-[10px] text-purple-400/80 mt-1">
                        👤 {{ a.firefighter_name }}
                    </p>
                </li>
            </ul>
        </div>

    </aside>
</template>
