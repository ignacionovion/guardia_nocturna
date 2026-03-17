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
    <section>
        <!-- Section header -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1.5 h-6 bg-gradient-to-b from-red-500 to-red-600 rounded-full shadow-lg shadow-red-900/50"></div>
            <h2 class="text-base font-extrabold text-white uppercase tracking-wider">
                Personal en Guardia
            </h2>
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
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"
        >
            <FirefighterCard
                v-for="member in sortedStaff"
                :key="member.id"
                :firefighter="member"
                :bed-number="store.bedByFirefighter[member.id] ?? null"
            />
        </div>
    </section>
</template>
