<script setup>
import { useGuardiaStore } from '../stores/guardia';
import FirefighterCard from './FirefighterCard.vue';

const store = useGuardiaStore();
</script>

<template>
    <section>
        <!-- Section header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-1 h-5 bg-red-500 rounded-full"></div>
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-widest">
                    Personal en Guardia
                </h2>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span>{{ store.visibleCount }} en pantalla</span>
                <span class="text-slate-600">·</span>
                <span class="text-emerald-400 font-semibold">{{ store.presentCount }} presentes</span>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="store.isLoading && store.staff.length === 0" class="flex items-center justify-center py-20">
            <div class="flex flex-col items-center gap-3 text-slate-500">
                <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                </svg>
                <span class="text-sm">Cargando personal...</span>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-else-if="store.staff.length === 0"
            class="flex flex-col items-center justify-center py-16 rounded-xl border border-slate-700/50 bg-slate-800/30"
        >
            <svg class="w-12 h-12 text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <p class="text-slate-500 text-sm">Sin personal asignado</p>
        </div>

        <!-- Grid -->
        <div
            v-else
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5"
        >
            <FirefighterCard
                v-for="member in store.staff"
                :key="member.id"
                :firefighter="member"
                :bed-number="store.bedByFirefighter[member.id] ?? null"
            />
        </div>
    </section>
</template>
