<script setup>
import { useGuardiaStore } from '../stores/guardia';
import LiveClock from './LiveClock.vue';

const store = useGuardiaStore();
</script>

<template>
    <aside class="flex flex-col gap-4">

        <!-- Clock widget -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Hora Local</span>
            </div>
            <LiveClock />
        </div>

        <!-- Birthdays this month -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎂</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Cumpleaños</span>
                </div>
                <span class="text-xs text-slate-500">Este mes</span>
            </div>

            <div v-if="store.birthdaysThisMonth.length === 0" class="text-xs text-slate-600 py-2 text-center">
                Sin cumpleaños este mes
            </div>
            <ul v-else class="space-y-1.5">
                <li
                    v-for="b in store.birthdaysThisMonth"
                    :key="b.id"
                    class="flex items-center justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-slate-200 leading-tight">{{ b.name }}</p>
                        <p v-if="b.cargo" class="text-[10px] text-slate-500 uppercase">{{ b.cargo }}</p>
                    </div>
                    <span class="text-xs font-bold text-amber-400 tabular-nums">{{ b.day }}</span>
                </li>
            </ul>
        </div>

        <!-- Novelties -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">📋</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Novedades</span>
                </div>
            </div>

            <div v-if="store.novelties.length === 0" class="text-xs text-slate-600 py-2 text-center">
                Sin novedades recientes
            </div>
            <ul v-else class="space-y-2">
                <li
                    v-for="n in store.novelties"
                    :key="n.id"
                    class="rounded-lg bg-slate-700/40 px-3 py-2 border border-slate-700/30"
                >
                    <p class="text-xs text-slate-300 leading-snug line-clamp-2">{{ n.content }}</p>
                    <p v-if="n.user_name" class="text-[10px] text-slate-500 mt-1">{{ n.user_name }}</p>
                </li>
            </ul>
        </div>

        <!-- Academies -->
        <div class="rounded-xl border border-slate-700/50 bg-slate-800/80 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎓</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Academias</span>
                </div>
            </div>

            <div v-if="store.academies.length === 0" class="text-xs text-slate-600 py-2 text-center">
                Sin academias registradas
            </div>
            <ul v-else class="space-y-2">
                <li
                    v-for="a in store.academies"
                    :key="a.id"
                    class="rounded-lg bg-slate-700/40 px-3 py-2 border border-slate-700/30"
                >
                    <p class="text-xs text-slate-300 leading-snug line-clamp-2">{{ a.content }}</p>
                    <p v-if="a.firefighter_name" class="text-[10px] text-slate-500 mt-1">
                        {{ a.firefighter_name }}
                    </p>
                </li>
            </ul>
        </div>

    </aside>
</template>
