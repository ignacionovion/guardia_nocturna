<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();
const isFullscreen = ref(false);

const saveBtnDisabled = computed(() =>
    !store.attendanceEnabled || store.isSaving || !store.allConfirmed
);

const saveBtnClass = computed(() => {
    if (store.isSaving) return 'bg-slate-700 text-slate-300 border-slate-600 cursor-wait opacity-80';
    if (!store.attendanceEnabled) return 'bg-slate-800/50 text-slate-500 border-slate-800 cursor-not-allowed opacity-60';
    if (!store.allConfirmed) return 'bg-slate-700 text-slate-400 border-slate-600 cursor-not-allowed opacity-60';
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

function openCalendarPopup() {
    if (window.openCalendarPopup) {
        window.openCalendarPopup();
    }
}

function openFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        document.exitFullscreen().catch(() => {});
    }
}

function updateFullscreenState() {
    isFullscreen.value = !!document.fullscreenElement;
}

onMounted(() => {
    document.addEventListener('fullscreenchange', updateFullscreenState);
    updateFullscreenState();
});

onUnmounted(() => {
    document.removeEventListener('fullscreenchange', updateFullscreenState);
});
</script>

<template>
    <header 
        class="sticky top-0 z-40 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border-b-2 border-slate-700/80 backdrop-blur-md shadow-2xl shadow-black/50 transition-all duration-300"
        :class="{ 'py-3': isFullscreen, 'py-4': !isFullscreen }"
    >

        <div class="px-4 md:px-6 lg:px-8 flex items-center justify-between gap-4">

            <!-- Left: branding -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-600 to-red-700 flex items-center justify-center shadow-lg border border-red-500/30">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12.75c1.148 0 2.278.08 3.383.237 1.037.146 1.866.966 1.866 2.013 0 3.728-2.35 6.75-5.25 6.75S6.75 18.728 6.75 15c0-1.046.83-1.867 1.866-2.013A24.204 24.204 0 0112 12.75zm4.911-6.836a.75.75 0 01-.744.832l-1.833-.24a.75.75 0 01-.661-.667l-.24-1.833a.75.75 0 01.832-.744l1.833.24a.75.75 0 01.667.661l.24 1.833z" />
                    </svg>
                </div>
                <div class="hidden sm:block">
                    <h1 class="text-sm font-bold text-white">
                        {{ store.guardia?.nombre || 'Guardia' }}
                    </h1>
                    <p class="text-xs text-slate-500">Panel en vivo</p>
                </div>
            </div>

            <!-- Center: empty (badge eliminado) -->
            <div class="hidden lg:flex items-center gap-2">
            </div>

            <!-- Right: toolbar -->
            <div class="flex items-center gap-3">

                <!-- Attendance save button -->
                <button
                    type="button"
                    :disabled="saveBtnDisabled"
                    class="hidden sm:flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg border transition-all"
                    :class="saveBtnClass"
                    @click="handleSave"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden md:inline">Guardar</span>
                </button>

                <!-- Attendance badge -->
                <!-- Attendance status badge -->
                <span
                    class="hidden xl:inline-flex items-center text-xs font-extrabold px-3 py-1.5 rounded-lg border-2 tracking-wider uppercase shadow-lg"
                    :class="attendanceBadgeClass"
                >
                    {{ store.attendanceMessage }}
                </span>

                <!-- Toolbar action icons -->
                <div class="flex items-center gap-2">

                    <!-- Calendario -->
                    <button
                        type="button"
                        @click="openCalendarPopup"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Calendario de Guardias"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <!-- Camas -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Gestión de Camas"
                        @click="store.openModal('camas')"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V6a2 2 0 0 1 2-2h3" />
                            <circle cx="7" cy="9" r="1" />
                        </svg>
                    </button>

                    <!-- Aseo -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Aseo"
                        @click="store.openModal('aseo')"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>

                    <!-- Emergencias -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Emergencias"
                        @click="store.openModal('emergencias')"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </button>

                    <!-- Refuerzo -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Agregar Refuerzo"
                        @click="store.openModal('refuerzo')"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </button>

                    <!-- Fullscreen -->
                    <button
                        type="button"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-all border"
                        :class="isFullscreen
                            ? 'bg-emerald-600 border-emerald-500 hover:bg-emerald-500'
                            : 'bg-slate-800 border-slate-700 hover:bg-slate-700'"
                        :title="isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa'"
                        @click="openFullscreen"
                    >
                        <svg v-if="!isFullscreen" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <svg v-else class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                </div>
            </div>

        </div>
    </header>
</template>
