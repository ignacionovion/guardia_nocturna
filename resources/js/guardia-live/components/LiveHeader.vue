<script setup>
import { computed } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const saveBtnDisabled = computed(() =>
    !store.attendanceEnabled || store.isSaving || !store.allConfirmed
);

const saveBtnClass = computed(() => {
    if (store.isSaving) return 'bg-slate-700 text-slate-300 border-slate-600 cursor-wait opacity-80';
    if (!store.attendanceEnabled) return 'bg-slate-800/50 text-slate-500 border-slate-800 cursor-not-allowed opacity-60';
    if (store.hasPendingChanges) return 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500 shadow-md shadow-emerald-900/50';
    return 'bg-slate-800 hover:bg-slate-700 text-white border-slate-700';
});

const saveIconClass = computed(() => {
    if (!store.attendanceEnabled) return 'text-slate-500';
    if (store.hasPendingChanges) return 'text-white';
    return 'text-emerald-400';
});

const attendanceBadgeClass = computed(() => store.attendanceVariantClasses);

async function handleSave() {
    if (saveBtnDisabled.value) return;
    await store.saveAttendance();
}

function openFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        document.exitFullscreen().catch(() => {});
    }
}
</script>

<template>
    <header class="sticky top-0 z-40 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border-b-2 border-slate-700/80 backdrop-blur-md shadow-2xl shadow-black/50">

        <div class="px-4 md:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">

            <!-- Left: brand + guardia info -->
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-600 to-red-700 border-2 border-red-500/50 flex items-center justify-center flex-shrink-0 shadow-lg shadow-red-900/50">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-400 uppercase tracking-wider leading-none mb-1 font-semibold">Panel de Control</p>
                    <h1 class="text-lg font-extrabold text-white truncate leading-tight">
                        {{ store.guardiaName || 'Guardia' }}
                    </h1>
                </div>
            </div>

            <!-- Center: live badge + counters -->
            <div class="hidden lg:flex items-center gap-4">
                <div class="flex items-center gap-2 bg-emerald-500/20 border-2 border-emerald-500/40 rounded-xl px-4 py-2 shadow-lg shadow-emerald-900/30">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shadow-lg shadow-emerald-500/50"></span>
                    <span class="text-sm font-extrabold text-emerald-400 uppercase tracking-wider">EN VIVO</span>
                </div>

                <!-- Reactive counters -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700/50 border border-slate-600/50">
                        <span class="text-sm font-bold text-white tabular-nums">{{ store.visibleCount }}</span>
                        <span class="text-xs text-slate-400">total</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30">
                        <span class="text-sm font-bold text-emerald-400 tabular-nums">{{ store.presentCount }}</span>
                        <span class="text-xs text-emerald-500/70">presentes</span>
                    </div>
                </div>

                <!-- Pending changes indicator -->
                <div
                    v-if="store.hasPendingChanges"
                    class="flex items-center gap-2 bg-amber-500/20 border-2 border-amber-500/40 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-900/30 animate-pulse"
                >
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="text-xs font-extrabold text-amber-400 uppercase tracking-wide">Cambios pendientes</span>
                </div>
            </div>

            <!-- Right: toolbar -->
            <div class="flex items-center gap-3">

                <!-- Attendance save button -->
                <button
                    type="button"
                    :disabled="saveBtnDisabled"
                    class="hidden sm:flex items-center gap-2 text-sm font-extrabold px-4 py-2.5 rounded-xl border-2 transition-all duration-200 shadow-lg"
                    :class="saveBtnClass"
                    @click="handleSave"
                >
                    <!-- Spinner when saving -->
                    <svg v-if="store.isSaving" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <svg v-else class="w-5 h-5" :class="saveIconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span class="hidden md:inline uppercase tracking-wide">{{ store.isSaving ? 'Guardando...' : 'Guardar' }}</span>
                </button>

                <!-- Attendance status badge -->
                <span
                    class="hidden xl:inline-flex items-center text-xs font-extrabold px-3 py-1.5 rounded-lg border-2 tracking-wider uppercase shadow-lg"
                    :class="attendanceBadgeClass"
                >
                    {{ store.attendanceMessage }}
                </span>

                <!-- Toolbar action icons -->
                <div class="flex items-center gap-2">

                    <!-- Aseo -->
                    <button
                        type="button"
                        class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 bg-slate-800/80 border-2 border-slate-700/50 hover:bg-slate-700 hover:border-slate-600 hover:scale-110 shadow-lg"
                        title="Aseo"
                        @click="store.openModal('aseo')"
                    >
                        <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </button>

                    <!-- Emergencias -->
                    <button
                        type="button"
                        class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 bg-red-900/20 border-2 border-red-700/50 hover:bg-red-900/40 hover:border-red-600/70 hover:scale-110 shadow-lg shadow-red-900/30"
                        title="Emergencias"
                        @click="store.openModal('emergencias')"
                    >
                        <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </button>

                    <!-- Refuerzo -->
                    <button
                        type="button"
                        class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 bg-purple-900/20 border-2 border-purple-700/50 hover:bg-purple-900/40 hover:border-purple-600/70 hover:scale-110 shadow-lg shadow-purple-900/30"
                        title="Agregar Refuerzo"
                        @click="store.openModal('refuerzo')"
                    >
                        <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </button>

                    <!-- Fullscreen -->
                    <button
                        type="button"
                        class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 bg-slate-800/80 border-2 border-slate-700/50 hover:bg-slate-700 hover:border-slate-600 hover:scale-110 shadow-lg"
                        title="Pantalla completa"
                        @click="openFullscreen"
                    >
                        <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>

                </div>
            </div>

        </div>
    </header>
</template>
