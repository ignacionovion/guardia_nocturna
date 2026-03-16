<script setup>
import { ref, computed, onMounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const allFirefighters = ref([]);
const selectedReplacementId = ref(null);
const isSaving = ref(false);
const error = ref(null);

const originalFirefighter = computed(() => store.modalContext?.firefighter || null);

const availableReplacements = computed(() => {
    if (!originalFirefighter.value) return [];

    const currentGuardiaId = store.guardia?.id;
    const originalId = originalFirefighter.value.id;

    // Firefighters from OTHER guardias who are not the original and not already in a replacement
    return allFirefighters.value.filter(f => {
        return f.id !== originalId && f.guardia_id !== currentGuardiaId;
    });
});

onMounted(async () => {
    // Fetch all firefighters
    console.log('[ReemplazoModal] Fetching firefighters...');
    try {
        const res = await fetch('/api/bomberos', { credentials: 'same-origin' });
        console.log('[ReemplazoModal] Response:', res.status, res.ok);
        if (res.ok) {
            const data = await res.json();
            console.log('[ReemplazoModal] Data received:', data);
            allFirefighters.value = Array.isArray(data) ? data : (data.data || []);
            console.log('[ReemplazoModal] Firefighters loaded:', allFirefighters.value.length);
        } else {
            console.error('[ReemplazoModal] Fetch failed:', await res.text());
        }
    } catch (err) {
        console.error('[ReemplazoModal] Failed to load firefighters:', err);
    }
});

async function handleSave() {
    if (!selectedReplacementId.value) {
        error.value = 'Debes seleccionar un reemplazante';
        return;
    }

    if (!originalFirefighter.value) {
        error.value = 'Bombero original no disponible';
        return;
    }

    isSaving.value = true;
    error.value = null;

    const result = await store.assignReplacement(
        originalFirefighter.value.id,
        selectedReplacementId.value
    );

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
            v-if="store.activeModal === 'reemplazo'"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
            @click.self="handleClose"
        >
            <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-600/20 border border-amber-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">Asignar Reemplazo</h2>
                            <p class="text-xs text-slate-400">Selecciona quién reemplazará al bombero</p>
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
                    <!-- Original firefighter info -->
                    <div v-if="originalFirefighter" class="p-4 bg-slate-900/50 border border-slate-700 rounded-lg">
                        <p class="text-xs text-slate-400 mb-2">Bombero a reemplazar:</p>
                        <p class="text-base font-semibold text-slate-100">
                            {{ originalFirefighter.nombres }} {{ originalFirefighter.apellido_paterno }}
                        </p>
                    </div>

                    <!-- Replacement selection -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Reemplazante *</label>
                        <select
                            v-model="selectedReplacementId"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                        >
                            <option :value="null">Seleccionar reemplazante</option>
                            <optgroup v-for="(group, guardiaId) in availableReplacements.reduce((acc, f) => {
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
                            <strong class="text-slate-300">Nota:</strong> El reemplazante será agregado a esta guardia y el bombero original 
                            quedará marcado como "Ausente". El reemplazo estará activo hasta las 07:00 AM del día siguiente.
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
                        class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
                        @click="handleSave"
                        :disabled="isSaving"
                    >
                        <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                        <span>{{ isSaving ? 'Asignando...' : 'Asignar Reemplazo' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
