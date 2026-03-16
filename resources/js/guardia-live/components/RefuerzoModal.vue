<script setup>
import { ref, computed, onMounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const allFirefighters = ref([]);
const selectedFirefighterId = ref(null);
const isSaving = ref(false);
const error = ref(null);

const availableForRefuerzo = computed(() => {
    const currentGuardiaId = store.guardia?.id;
    if (!currentGuardiaId) return [];

    // Firefighters from OTHER guardias who are not already in a replacement
    return allFirefighters.value.filter(f => {
        return f.guardia_id !== currentGuardiaId && !f.es_refuerzo;
    });
});

onMounted(async () => {
    // Fetch all firefighters
    try {
        const res = await fetch('/api/bomberos', { credentials: 'same-origin' });
        if (res.ok) {
            const data = await res.json();
            allFirefighters.value = data.data || data || [];
        }
    } catch (err) {
        console.error('[RefuerzoModal] Failed to load firefighters:', err);
    }
});

async function handleSave() {
    if (!selectedFirefighterId.value) {
        error.value = 'Debes seleccionar un bombero';
        return;
    }

    isSaving.value = true;
    error.value = null;

    const result = await store.addRefuerzo(selectedFirefighterId.value);

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
            v-if="store.activeModal === 'refuerzo'"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
            @click.self="handleClose"
        >
            <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-600/20 border border-purple-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">Agregar Refuerzo</h2>
                            <p class="text-xs text-slate-400">Selecciona un bombero de otra guardia</p>
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
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Bombero a Agregar como Refuerzo *</label>
                        <select
                            v-model="selectedFirefighterId"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                            <option :value="null">Seleccionar bombero</option>
                            <optgroup v-for="(group, guardiaId) in availableForRefuerzo.reduce((acc, f) => {
                                const gid = f.guardia_id || 'sin-guardia';
                                if (!acc[gid]) acc[gid] = [];
                                acc[gid].push(f);
                                return acc;
                            }, {})" :key="guardiaId" :label="`Guardia ${guardiaId}`">
                                <option v-for="ff in group" :key="ff.id" :value="ff.id">
                                    {{ ff.nombres }} {{ ff.apellido_paterno }}
                                </option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="p-4 bg-slate-900/50 border border-slate-700 rounded-lg">
                        <p class="text-xs text-slate-400 leading-relaxed">
                            <strong class="text-slate-300">Nota:</strong> El bombero seleccionado será agregado temporalmente a esta guardia como refuerzo. 
                            Al finalizar el turno (07:00 AM), volverá automáticamente a su guardia original.
                        </p>
                    </div>
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
                        class="px-5 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
                        @click="handleSave"
                        :disabled="isSaving"
                    >
                        <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                        <span>{{ isSaving ? 'Agregando...' : 'Agregar Refuerzo' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
