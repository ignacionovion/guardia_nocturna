<script setup>
import { computed } from 'vue';
import { useGuardiaStore } from '../stores/guardia';
import FirefighterCard from './FirefighterCard.vue';

const store = useGuardiaStore();

// Sort by seniority: higher years_service first (left to right)
const sortedStaff = computed(() => {
    return [...store.staff].sort((a, b) => {
        const yearsA = a.years_service ?? 0;
        const yearsB = b.years_service ?? 0;
        const monthsA = a.months_service ?? 0;
        const monthsB = b.months_service ?? 0;
        
        // Sort by years descending, then by months descending
        if (yearsB !== yearsA) {
            return yearsB - yearsA;
        }
        return monthsB - monthsA;
    });
});
</script>

<template>
    <section class="flex flex-col h-full">
        <!-- Section header with stats -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-gradient-to-b from-red-500 to-red-600 rounded-full shadow-lg shadow-red-900/50"></div>
                <h2 class="text-base font-extrabold text-white uppercase tracking-wider">
                    Personal en Guardia
                </h2>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-800/50 border border-slate-700/50 text-xs text-slate-400">
                    <span class="font-bold text-white">{{ store.visibleCount }}</span> total
                </span>
            </div>
            
            <!-- View density indicator for large screens -->
            <div class="hidden 2xl:flex items-center gap-2 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Modo Cuartel Activo</span>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="store.isLoading && store.staff.length === 0" class="flex items-center justify-center py-20 flex-1">
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
            class="flex flex-col items-center justify-center py-16 rounded-xl border border-slate-700/50 bg-slate-800/30 flex-1"
        >
            <svg class="w-12 h-12 text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <p class="text-slate-500 text-sm">Sin personal asignado</p>
        </div>

        <!-- Grid - Optimized for large screens (modo cuartel) -->
        <div
            v-else
            class="grid gap-3 2xl:gap-4 flex-1 content-start"
            :class="{
                // Mobile: 2 columns
                'grid-cols-2': true,
                // Small screens: 3 columns  
                'sm:grid-cols-3': true,
                // Medium screens: 4 columns
                'md:grid-cols-4': true,
                // Large screens: 4-5 columns
                'lg:grid-cols-4': true,
                'xl:grid-cols-5': true,
                // Extra large: 6 columns (modo cuartel)
                '2xl:grid-cols-6': true,
                // Ultra wide: 7-8 columns for massive screens
                '3xl:grid-cols-7': true,
                '4xl:grid-cols-8': true,
            }"
        >
            <FirefighterCard
                v-for="member in sortedStaff"
                :key="member.id"
                :firefighter="member"
                :bed-number="store.bedByFirefighter[member.id] ?? null"
                class="h-full"
            />
        </div>
        
        <!-- Footer info for large screens -->
        <div class="hidden 2xl:flex items-center justify-between mt-4 pt-4 border-t border-slate-700/30">
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ store.presentCount }} presentes
                </span>
                <span v-if="store.unconfirmedCount > 0" class="flex items-center gap-1.5 text-amber-400">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ store.unconfirmedCount }} por confirmar
                </span>
            </div>
            <div class="text-xs text-slate-600">
                Ordenado por antigüedad
            </div>
        </div>
    </section>
</template>
