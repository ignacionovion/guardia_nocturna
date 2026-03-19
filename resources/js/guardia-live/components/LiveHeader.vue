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
            <div class="flex items-center gap-4">
                <!-- Logo profesional -->
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-600 via-red-700 to-red-800 flex items-center justify-center shadow-xl border-2 border-red-500/40 relative overflow-hidden">
                        <!-- Efecto de brillo -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent"></div>
                        <svg class="w-6 h-6 text-white relative z-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12.75c1.148 0 2.278.08 3.383.237 1.037.146 1.866.966 1.866 2.013 0 3.728-2.35 6.75-5.25 6.75S6.75 18.728 6.75 15c0-1.046.83-1.867 1.866-2.013A24.204 24.204 0 0112 12.75zm4.911-6.836a.75.75 0 01-.744.832l-1.833-.24a.75.75 0 01-.661-.667l-.24-1.833a.75.75 0 01.832-.744l1.833.24a.75.75 0 01.667.661l.24 1.833z" />
                        </svg>
                    </div>
                    <!-- Indicador de estado -->
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-slate-900 shadow-lg animate-pulse"></div>
                </div>
                
                <!-- Información de guardia -->
                <div class="hidden sm:block">
                    <div class="flex items-center gap-2 mb-0.5">
                        <h1 class="text-base font-black text-white tracking-tight">
                            {{ store.guardia?.name || store.guardia?.nombre || 'Guardia' }}
                        </h1>
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase rounded-full border border-emerald-500/30">
                            En Línea
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Sistema de Gestión en Vivo</p>
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
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                        <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
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
                <div class="flex flex-col gap-3">

                    <!-- Calendario -->
                    <button
                        type="button"
                        @click="openCalendarPopup"
                        class="w-12 h-12 rounded-xl flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Calendario de Guardias"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Camas -->
                    <button
                        type="button"
                        class="w-12 h-12 rounded-xl flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Gestión de Camas"
                        @click="store.openModal('camas')"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>

                    <!-- Aseo -->
                    <button
                        type="button"
                        class="w-12 h-12 rounded-xl flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Aseo"
                        @click="store.openModal('aseo')"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Emergencias -->
                    <button
                        type="button"
                        class="w-12 h-12 rounded-xl flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Emergencias"
                        @click="store.openModal('emergencias')"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Refuerzo -->
                    <button
                        type="button"
                        class="w-12 h-12 rounded-xl flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                        title="Agregar Refuerzo"
                        @click="store.openModal('refuerzo')"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                    </button>

                    <!-- Fullscreen -->
                    <button
                        type="button"
                        class="w-12 h-12 rounded-xl flex items-center justify-center transition-all border"
                        :class="isFullscreen
                            ? 'bg-emerald-600 border-emerald-500 hover:bg-emerald-500'
                            : 'bg-slate-800 border-slate-700 hover:bg-slate-700'"
                        :title="isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa'"
                        @click="openFullscreen"
                    >
                        <svg v-if="!isFullscreen" class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" />
                        </svg>
                        <svg v-else class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                </div>
            </div>

        </div>
    </header>
</template>
