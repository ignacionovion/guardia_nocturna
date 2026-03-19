<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const allFirefighters = ref([]);
const selectedFirefighterId = ref(null);
const isSaving = ref(false);
const error = ref(null);
const searchQuery = ref('');

const selectedFirefighter = computed(() => {
    return allFirefighters.value.find(f => f.id === selectedFirefighterId.value);
});

const availableForRefuerzo = computed(() => {
    const currentGuardiaId = store.guardia?.id;
    
    console.log('[RefuerzoModal] Computing available:', {
        currentGuardiaId,
        totalFirefighters: allFirefighters.value.length,
        hasGuardia: !!currentGuardiaId
    });
    
    if (!currentGuardiaId) {
        console.warn('[RefuerzoModal] No current guardia ID - returning empty');
        return [];
    }

    // Firefighters from OTHER guardias (or without guardia) who are not already in a replacement
    let filtered = allFirefighters.value.filter(f => {
        // Incluir bomberos de otras guardias O sin guardia asignada
        const isDifferentGuardia = f.guardia_id && f.guardia_id !== currentGuardiaId;
        const hasNoGuardia = !f.guardia_id;
        const notAlreadyRefuerzo = !f.es_refuerzo;
        
        return (isDifferentGuardia || hasNoGuardia) && notAlreadyRefuerzo;
    });
    
    console.log('[RefuerzoModal] After filtering:', {
        filteredCount: filtered.length,
        excludedSameGuardia: allFirefighters.value.filter(f => f.guardia_id === currentGuardiaId).length,
        excludedAlreadyRefuerzo: allFirefighters.value.filter(f => f.es_refuerzo).length
    });

    // Apply search filter
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(f => {
            const fullName = `${f.nombres} ${f.apellido_paterno} ${f.apellido_materno || ''}`.toLowerCase();
            return fullName.includes(query);
        });
        console.log('[RefuerzoModal] After search filter:', filtered.length);
    }

    // Group by guardia
    const grouped = filtered.reduce((acc, f) => {
        const gid = f.guardia_id || 'sin-guardia';
        if (!acc[gid]) acc[gid] = [];
        acc[gid].push(f);
        return acc;
    }, {});
    
    
    return grouped;
});


function selectFirefighter(firefighter) {
    selectedFirefighterId.value = firefighter.id;
}

onMounted(async () => {
    
    // Fetch all firefighters
    console.log('[RefuerzoModal] Fetching firefighters...');
    try {
        const res = await fetch('/api/bomberos', { credentials: 'same-origin' });
        console.log('[RefuerzoModal] Response:', res.status, res.ok);
        if (res.ok) {
            const data = await res.json();
            console.log('[RefuerzoModal] Raw data received:', data);
            console.log('[RefuerzoModal] Is array?', Array.isArray(data));
            console.log('[RefuerzoModal] Has data property?', !!data.data);
            allFirefighters.value = Array.isArray(data) ? data : (data.data || []);
            console.log('[RefuerzoModal] Firefighters loaded:', allFirefighters.value.length);
            console.log('[RefuerzoModal] Sample firefighter:', allFirefighters.value[0]);
            console.log('[RefuerzoModal] Current guardia ID:', store.guardia?.id);
        } else {
            console.error('[RefuerzoModal] Failed to load firefighters:', res.status);
            error.value = `Error al cargar bomberos: ${res.status}`;
        }
    } catch (err) {
        console.error('[RefuerzoModal] Error:', err);
        error.value = 'Error de conexión al cargar bomberos';
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
                        <div class="w-12 h-12 rounded-full bg-purple-600/20 flex items-center justify-center">
                            <svg class="w-7 h-7 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">Agregar Refuerzo</h2>
                            <p class="text-xs text-slate-400">Selecciona un bombero de otra guardia</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-colors"
                        @click="handleClose"
                        :disabled="isSaving"
                        title="Cerrar"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Error message -->
                <div v-if="error" class="mx-6 mt-4 px-4 py-3 bg-red-900/30 border border-red-600/50 rounded-lg text-sm text-red-200">
                    {{ error }}
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                    <!-- Bombero seleccionado -->
                    <div v-if="selectedFirefighter" class="p-3 bg-purple-900/20 border border-purple-600/30 rounded-lg">
                        <p class="text-xs text-purple-300 mb-1 font-semibold">Bombero Seleccionado:</p>
                        <p class="text-sm text-slate-100 font-medium">
                            {{ selectedFirefighter.nombres }} {{ selectedFirefighter.apellido_paterno }}
                        </p>
                    </div>

                    <!-- Buscador -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Buscar Bombero</label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Buscar por nombre..."
                                class="w-full px-3 py-2 pl-9 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            />
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <!-- Lista de bomberos -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Bomberos Disponibles</label>
                        <div class="bg-slate-700 border border-slate-600 rounded-lg overflow-hidden" style="height: 320px;">
                            <div class="overflow-y-auto h-full">
                                <div v-if="Object.keys(availableForRefuerzo).length === 0" class="px-4 py-12 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-300 mb-1">No hay bomberos disponibles</p>
                                    <p class="text-xs text-slate-500">No se encontraron bomberos de otras guardias</p>
                                </div>
                                <template v-else v-for="(group, guardiaId) in availableForRefuerzo" :key="guardiaId">
                                    <div class="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-800/70 sticky top-0">
                                        {{ guardiaId === 'sin-guardia' ? 'Sin Guardia Asignada' : `Guardia ${guardiaId}` }}
                                    </div>
                                    <button
                                        v-for="ff in group"
                                        :key="ff.id"
                                        type="button"
                                        @click="selectFirefighter(ff)"
                                        class="w-full px-3 py-2.5 text-left text-sm text-slate-200 hover:bg-purple-600/30 hover:text-white transition-colors flex items-center gap-2 border-b border-slate-700/30"
                                        :class="{ 'bg-purple-600/20 text-white': selectedFirefighterId === ff.id }"
                                    >
                                        <svg v-if="selectedFirefighterId === ff.id" class="w-4 h-4 text-purple-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="w-4 flex-shrink-0" v-else></span>
                                        <span class="flex-1">{{ ff.nombres }} {{ ff.apellido_paterno }}</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Nota informativa -->
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
                        class="px-5 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-slate-600 disabled:cursor-not-allowed disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
                        @click="handleSave"
                        :disabled="isSaving || !selectedFirefighterId"
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
