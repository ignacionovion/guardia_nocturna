<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const allFirefighters = ref([]);
const selectedFirefighterId = ref(null);
const isSaving = ref(false);
const error = ref(null);
const searchQuery = ref('');
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

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
    
    console.log('[RefuerzoModal] ========== ESTRUCTURA FINAL ==========');
    console.log('[RefuerzoModal] typeof grouped:', typeof grouped);
    console.log('[RefuerzoModal] Array.isArray(grouped):', Array.isArray(grouped));
    console.log('[RefuerzoModal] Object.keys(grouped):', Object.keys(grouped));
    console.log('[RefuerzoModal] Object.keys(grouped).length:', Object.keys(grouped).length);
    console.log('[RefuerzoModal] Primer grupo:', Object.keys(grouped)[0]);
    console.log('[RefuerzoModal] Bomberos en primer grupo:', grouped[Object.keys(grouped)[0]]?.length);
    console.log('[RefuerzoModal] Primer bombero del primer grupo:', grouped[Object.keys(grouped)[0]]?.[0]);
    console.log('[RefuerzoModal] ESTRUCTURA COMPLETA:', JSON.stringify(grouped, null, 2));
    console.log('[RefuerzoModal] =====================================');
    
    return grouped;
});

function toggleDropdown() {
    console.log('[RefuerzoModal] toggleDropdown called, current state:', isDropdownOpen.value);
    isDropdownOpen.value = !isDropdownOpen.value;
    console.log('[RefuerzoModal] isDropdownOpen =', isDropdownOpen.value);
    if (isDropdownOpen.value) {
        console.log('[RefuerzoModal] Opening dropdown, clearing search');
        searchQuery.value = '';
    } else {
        console.log('[RefuerzoModal] Closing dropdown');
    }
}

function selectFirefighter(firefighter) {
    selectedFirefighterId.value = firefighter.id;
    isDropdownOpen.value = false;
    searchQuery.value = '';
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        console.log('[RefuerzoModal] Click outside detected, closing dropdown');
        isDropdownOpen.value = false;
    }
}

onMounted(async () => {
    document.addEventListener('click', handleClickOutside);
    
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

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
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
                    <!-- Custom Dropdown with Search -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Bombero a Agregar como Refuerzo *</label>
                        
                        <!-- Dropdown Trigger -->
                        <button
                            type="button"
                            @click.stop="toggleDropdown"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-left flex items-center justify-between transition-colors hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            :class="selectedFirefighterId ? 'text-slate-100' : 'text-slate-400'"
                        >
                            <span>
                                {{ selectedFirefighter 
                                    ? `${selectedFirefighter.nombres} ${selectedFirefighter.apellido_paterno}` 
                                    : 'Seleccionar bombero' 
                                }}
                            </span>
                            <svg 
                                class="w-5 h-5 text-slate-400 transition-transform" 
                                :class="{ 'rotate-180': isDropdownOpen }"
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor" 
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="isDropdownOpen"
                            ref="dropdownRef"
                            class="absolute z-50 w-full mt-1 bg-slate-700 border-4 border-red-500 rounded-lg shadow-xl h-96 overflow-hidden flex flex-col"
                            style="background-color: rgba(255, 255, 0, 0.1);"
                        >
                            <!-- Search Input inside dropdown -->
                            <div class="p-2 border-b border-slate-600">
                                <div class="relative">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Buscar bombero..."
                                        class="w-full px-3 py-2 pl-9 bg-slate-800 border border-slate-600 rounded-lg text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        @click.stop
                                    />
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Options List -->
                            <div class="overflow-y-auto flex-1 min-h-[200px]">
                                <div v-if="Object.keys(availableForRefuerzo).length === 0" class="px-3 py-8 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-300 mb-1">No hay bomberos disponibles</p>
                                    <p class="text-xs text-slate-500">No se encontraron bomberos de otras guardias que puedan ser agregados como refuerzo</p>
                                </div>
                                <div v-else class="py-1">
                                    <template v-for="(group, guardiaId) in availableForRefuerzo" :key="guardiaId">
                                        <div class="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-800/50 sticky top-0">
                                            Guardia {{ guardiaId === 'sin-guardia' ? 'Sin Guardia' : guardiaId }}
                                        </div>
                                        <button
                                            v-for="ff in group"
                                            :key="ff.id"
                                            type="button"
                                            @click="selectFirefighter(ff)"
                                            class="w-full px-3 py-2.5 text-left text-sm text-slate-200 hover:bg-purple-600/30 hover:text-white transition-colors flex items-center gap-2 border-b border-slate-700/30"
                                            :class="{ 'bg-purple-600/20': selectedFirefighterId === ff.id }"
                                        >
                                            <svg v-if="selectedFirefighterId === ff.id" class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span class="w-4 flex-shrink-0" v-else></span>
                                            <span class="flex-1">{{ ff.nombres }} {{ ff.apellido_paterno }}</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
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
