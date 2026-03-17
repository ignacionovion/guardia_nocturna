<script setup>
import { computed } from 'vue';
import { useGuardiaStore } from '../stores/guardia';
import LiveClock from './LiveClock.vue';

const store = useGuardiaStore();

// Compute bed stats
const bedStats = computed(() => {
    const total = Object.keys(store.bedByFirefighter).length;
    const occupied = Object.values(store.bedByFirefighter).filter(b => b).length;
    return { total, occupied, available: total - occupied };
});

// Recent activity from novelties and academies
const recentActivity = computed(() => {
    const activities = [];
    
    store.novelties.slice(0, 3).forEach((n, idx) => {
        activities.push({
            id: `novelty-${n.id}`,
            type: 'novelty',
            icon: '📋',
            color: 'text-blue-400',
            bgColor: 'bg-blue-500/10',
            content: n.content?.slice(0, 50) + (n.content?.length > 50 ? '...' : ''),
            time: n.created_at,
            user: n.user_name,
            order: idx,
        });
    });
    
    store.academies.slice(0, 2).forEach((a, idx) => {
        activities.push({
            id: `academy-${a.id}`,
            type: 'academy',
            icon: '🎓',
            color: 'text-purple-400',
            bgColor: 'bg-purple-500/10',
            content: a.content?.slice(0, 50) + (a.content?.length > 50 ? '...' : ''),
            time: a.created_at,
            user: a.firefighter_name,
            order: idx + 10,
        });
    });
    
    return activities.slice(0, 4);
});

function formatTime(isoString) {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <aside class="flex flex-col gap-3 h-full overflow-y-auto">

        <!-- Clock widget - más protagonista -->
        <div class="rounded-xl border border-slate-700/50 bg-gradient-to-br from-slate-800/90 to-slate-900/90 p-4 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Hora Local
                </span>
                <span class="text-[10px] text-slate-500 uppercase">{{ store.guardiaTz }}</span>
            </div>
            <LiveClock size="large" />
        </div>

        <!-- Camas - mejor destacado -->
        <div v-if="bedStats.total > 0" class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🛏️</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Camas</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-lg bg-slate-700/50 text-slate-300">
                    {{ bedStats.occupied }}/{{ bedStats.total }}
                </span>
            </div>
            <div class="grid grid-cols-5 gap-1.5">
                <div
                    v-for="n in bedStats.total"
                    :key="n"
                    class="aspect-square rounded-lg flex items-center justify-center text-xs font-bold transition-all"
                    :class="Object.values(store.bedByFirefighter).includes(n) 
                        ? 'bg-emerald-500/20 border-2 border-emerald-500/40 text-emerald-400' 
                        : 'bg-slate-700/30 border-2 border-slate-600/30 text-slate-500'"
                >
                    {{ n }}
                </div>
            </div>
        </div>

        <!-- Cumpleaños - mejor jerarquía -->
        <div v-if="store.birthdaysThisMonth.length > 0" class="rounded-xl border border-amber-500/20 bg-gradient-to-br from-amber-900/20 to-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎂</span>
                    <span class="text-xs font-semibold text-amber-400 uppercase tracking-widest">Cumpleaños</span>
                </div>
                <span class="text-[10px] text-amber-500/70 uppercase">Este mes</span>
            </div>
            <ul class="space-y-2">
                <li
                    v-for="b in store.birthdaysThisMonth.slice(0, 3)"
                    :key="b.id"
                    class="flex items-center gap-3 p-2 rounded-lg bg-slate-800/50 border border-slate-700/30"
                >
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-lg shadow-lg">
                        🎉
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-200 leading-tight truncate">{{ b.name }}</p>
                        <p v-if="b.cargo" class="text-[10px] text-slate-500 uppercase">{{ b.cargo }}</p>
                    </div>
                    <div class="text-center px-2 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20">
                        <span class="text-xs font-bold text-amber-400">{{ b.day }}</span>
                        <span class="text-[10px] text-amber-500/70 block">MAR</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Actividad reciente -->
        <div v-if="recentActivity.length > 0" class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">⚡</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Actividad</span>
                </div>
            </div>
            <ul class="space-y-2">
                <li
                    v-for="activity in recentActivity"
                    :key="activity.id"
                    class="flex items-start gap-2 p-2 rounded-lg border border-slate-700/30 transition-all hover:bg-slate-700/30"
                    :class="activity.bgColor"
                >
                    <span class="text-sm">{{ activity.icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-slate-300 leading-snug line-clamp-2">{{ activity.content }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-slate-500">{{ formatTime(activity.time) }}</span>
                            <span v-if="activity.user" class="text-[10px] text-slate-500 truncate">• {{ activity.user }}</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Novedades mejor presentadas -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📋</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Novedades</span>
                </div>
                <span v-if="store.novelties.length > 0" class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400">
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

        <!-- Academias mejor presentadas -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🎓</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Academias</span>
                </div>
                <span v-if="store.academies.length > 0" class="text-xs font-bold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-400">
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
