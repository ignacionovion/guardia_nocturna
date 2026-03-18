<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const tasks = ref([
    { id: 1, name: 'Aseo Pieza N°1' },
    { id: 2, name: 'Aseo Pieza N°2' },
    { id: 3, name: 'Aseo Pieza N°3' },
    { id: 4, name: 'Aseo Pieza N°4' },
    { id: 5, name: 'Aseo Pieza N°5' },
    { id: 6, name: 'Aseo Sector Duchas' },
    { id: 7, name: 'Aseo Sector Baños' },
    { id: 8, name: 'Aseo Sala de Estar' },
    { id: 9, name: 'Aseo Cocina Y Quincho' },
]);

const assignments = ref({});
const isSaving = ref(false);
const error = ref(null);
const isLoading = ref(false);

const availableFirefighters = computed(() => 
    store.presentStaff.filter(f => {
        const status = f.draft_attendance_status || f.estado_asistencia;
        return status === 'constituye';
    })
);

// Load existing assignments when modal opens
async function loadAssignments() {
    if (!store.guardia?.id) return;
    
    isLoading.value = true;
    error.value = null;
    
    const startTime = performance.now();
    
    try {
        const res = await fetch(`/api/guardia-live/cleaning-assignments?guardia_id=${store.guardia.id}`, { 
            credentials: 'same-origin' 
        });
        
        const loadTime = performance.now() - startTime;
        console.log(`[AseoModal] Load time: ${loadTime.toFixed(0)}ms`);
        
        if (res.ok) {
            const data = await res.json();
            console.log('[AseoModal] Loaded assignments:', data);
            
            // Map task_id -> firefighter_id
            if (data.assignments) {
                assignments.value = { ...data.assignments };
            }
        } else {
            error.value = 'Error al cargar asignaciones previas';
        }
    } catch (err) {
        console.error('[AseoModal] Failed to load assignments:', err);
        error.value = 'Error de conexión al cargar asignaciones';
    } finally {
        isLoading.value = false;
    }
}

// Watch for modal opening
watch(() => store.activeModal, (newModal) => {
    if (newModal === 'aseo') {
        loadAssignments();
    }
});

onMounted(() => {
    if (store.activeModal === 'aseo') {
        loadAssignments();
    }
});

async function handleSave() {
    isSaving.value = true;
    error.value = null;

    const result = await store.saveAseo(assignments.value);

    isSaving.value = false;

    if (result.ok) {
        store.saveResult = { ok: true, message: result.message };
        setTimeout(() => { store.saveResult = null; }, 5000);
        store.closeModal();
    } else {
        error.value = result.message;
    }
}

function handleClose() {
    if (!isSaving.value) {
        store.closeModal();
    }
}
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="store.activeModal === 'aseo'"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
            @click.self="handleClose"
        >
            <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-600/20 border border-blue-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">Asignar Aseo</h2>
                            <p class="text-xs text-slate-400">Distribuye tareas de aseo entre el personal</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-200 transition-colors"
                        @click="handleClose"
                        :disabled="isSaving"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Error message -->
                <div v-if="error" class="mx-6 mt-4 px-4 py-3 bg-red-900/30 border border-red-600/50 rounded-lg text-sm text-red-200">
                    {{ error }}
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
                    <!-- Loading skeleton -->
                    <template v-if="isLoading">
                        <div v-for="i in 9" :key="`skeleton-${i}`" class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-lg border border-slate-700/50 animate-pulse">
                            <div class="flex-1 h-5 bg-slate-700/50 rounded"></div>
                            <div class="w-[200px] h-9 bg-slate-700/50 rounded-lg"></div>
                        </div>
                    </template>
                    
                    <!-- Actual content -->
                    <template v-else>
                        <div v-for="task in tasks" :key="task.id" class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-lg border border-slate-700/50">
                            <label class="flex-1 text-sm font-medium text-slate-300">
                                {{ task.name }}
                            </label>
                            <select
                                v-model="assignments[task.id]"
                                class="px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]"
                            >
                                <option :value="null">Sin asignar</option>
                                <option v-for="ff in availableFirefighters" :key="ff.id" :value="ff.id">
                                    {{ ff.nombres }} {{ ff.apellido_paterno }}
                                </option>
                            </select>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-slate-700 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-slate-100 transition-colors"
                        @click="handleClose"
                        :disabled="isSaving"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
                        @click="handleSave"
                        :disabled="isSaving"
                    >
                        <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                        <span>{{ isSaving ? 'Guardando...' : 'Guardar Aseo' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
