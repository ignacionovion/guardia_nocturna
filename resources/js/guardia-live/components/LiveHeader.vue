<script setup>
import { computed } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const attendanceBtnClass = computed(() => {
    if (!store.attendanceEnabled) {
        return 'bg-slate-800/50 text-slate-500 border-slate-800 cursor-not-allowed opacity-60';
    }
    return 'bg-slate-800 hover:bg-slate-700 text-white border-slate-700 cursor-pointer';
});

const attendanceBadgeClass = computed(() => store.attendanceVariantClasses);

function openFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        document.exitFullscreen().catch(() => {});
    }
}

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.dispatchEvent(new CustomEvent('open-modal'));
    // Fallback: trigger existing Blade modal if available
    if (typeof window.openModal === 'function') window.openModal(id);
}
</script>

<template>
    <header class="sticky top-0 z-40 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border-b border-slate-700/60 backdrop-blur-sm">
        <div class="px-4 md:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">

            <!-- Left: brand + guardia info -->
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg bg-red-600/20 border border-red-500/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500 uppercase tracking-widest leading-none mb-0.5">Panel de Control</p>
                    <h1 class="text-base font-bold text-white truncate leading-tight">
                        {{ store.guardiaName || 'Guardia' }}
                    </h1>
                </div>
            </div>

            <!-- Center: live badge + counters -->
            <div class="hidden sm:flex items-center gap-3">
                <div class="flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-3 py-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wide">Guardia en Vivo</span>
                </div>
                <div class="flex items-center gap-1 text-sm text-slate-400">
                    <span class="font-semibold text-white">{{ store.visibleCount }}</span>
                    <span>en pantalla</span>
                    <span class="text-slate-600 mx-1">·</span>
                    <span class="font-semibold text-emerald-400">{{ store.presentCount }}</span>
                    <span>presentes</span>
                </div>
            </div>

            <!-- Right: toolbar -->
            <div class="flex items-center gap-2">

                <!-- Attendance save button -->
                <button
                    type="button"
                    :disabled="!store.attendanceEnabled"
                    class="hidden sm:flex items-center gap-2 text-xs font-semibold px-3 py-2 rounded-lg border transition-all duration-200"
                    :class="attendanceBtnClass"
                    @click="openModal('guardia-attendance-modal')"
                >
                    <svg class="w-4 h-4" :class="store.attendanceEnabled ? 'text-emerald-400' : 'text-slate-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span class="hidden md:inline">Guardar</span>
                </button>

                <!-- Attendance status badge -->
                <span
                    class="hidden md:inline-flex items-center text-[10px] font-bold px-2.5 py-1 rounded-full border tracking-wide"
                    :class="attendanceBadgeClass"
                >
                    {{ store.attendanceMessage }}
                </span>

                <!-- Toolbar action icons -->
                <div class="flex items-center gap-1">

                    <!-- Aseo -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200 bg-slate-800/80 border border-slate-700/50 hover:bg-slate-700 hover:border-slate-600"
                        title="Aseo"
                        @click="openModal('aseo-modal')"
                    >
                        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </button>

                    <!-- Emergencias -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200 bg-slate-800/80 border border-slate-700/50 hover:bg-red-900/40 hover:border-red-700/50"
                        title="Emergencias"
                        @click="openModal('emergencias-modal')"
                    >
                        <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </button>

                    <!-- Fullscreen -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200 bg-slate-800/80 border border-slate-700/50 hover:bg-slate-700 hover:border-slate-600"
                        title="Pantalla completa"
                        @click="openFullscreen"
                    >
                        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>

                </div>
            </div>

        </div>
    </header>
</template>
