<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const allFirefighters = ref([]);
const selectedReplacementId = ref(null);
const isSaving = ref(false);
const error = ref(null);
const searchQuery = ref('');
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

const originalFirefighter = computed(() => store.modalContext?.firefighter || null);

const selectedReplacement = computed(() => {
    return allFirefighters.value.find(f => f.id === selectedReplacementId.value);
});

const availableReplacements = computed(() => {
    if (!originalFirefighter.value) return [];

    const currentGuardiaId = store.guardia?.id;
    const originalId = originalFirefighter.value.id;

    // Firefighters from OTHER guardias who are not the original and not already in a replacement
    let filtered = allFirefighters.value.filter(f => {
        return f.id !== originalId && f.guardia_id !== currentGuardiaId;
    });

    // Apply search filter
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(f => {
            const fullName = `${f.nombres} ${f.apellido_paterno} ${f.apellido_materno || ''}`.toLowerCase();
            return fullName.includes(query);
        });
    }

    // Group by guardia
    return filtered.reduce((acc, f) => {
        const gid = f.guardia_id || 'sin-guardia';
        if (!acc[gid]) acc[gid] = [];
        acc[gid].push(f);
        return acc;
    }, {});
});

function toggleDropdown() {
    isDropdownOpen.value = !isDropdownOpen.value;
    if (isDropdownOpen.value) {
        searchQuery.value = '';
    }
}

function selectReplacement(firefighter) {
    selectedReplacementId.value = firefighter.id;
    isDropdownOpen.value = false;
    searchQuery.value = '';
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
}

onMounted(async () => {
    document.addEventListener('click', handleClickOutside);
    
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

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
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

                    <!-- Custom Dropdown with Search -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Reemplazante *</label>
                        
                        <!-- Dropdown Trigger -->
                        <button
                            type="button"
                            @click="toggleDropdown"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-left flex items-center justify-between transition-colors hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            :class="selectedReplacementId ? 'text-slate-100' : 'text-slate-400'"
                        >
                            <span>
                                {{ selectedReplacement 
                                    ? `${selectedReplacement.nombres} ${selectedReplacement.apellido_paterno}` 
                                    : 'Seleccionar reemplazante' 
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
                            class="absolute z-50 w-full mt-1 bg-slate-700 border border-slate-600 rounded-lg shadow-xl max-h-80 overflow-hidden flex flex-col"
                        >
                            <!-- Search Input inside dropdown -->
                            <div class="p-2 border-b border-slate-600">
                                <div class="relative">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Buscar reemplazante..."
                                        class="w-full px-3 py-2 pl-9 bg-slate-800 border border-slate-600 rounded-lg text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500"
                                        @click.stop
                                    />
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Options List -->
                            <div class="overflow-y-auto flex-1">
                                <div v-if="Object.keys(availableReplacements).length === 0" class="px-3 py-4 text-sm text-slate-400 text-center">
                                    No se encontraron reemplazantes
                                </div>
                                <template v-for="(group, guardiaId) in availableReplacements" :key="guardiaId">
                                    <div class="px-3 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-800/50">
                                        Guardia {{ guardiaId === 'sin-guardia' ? 'Sin Guardia' : guardiaId }}
                                    </div>
                                    <button
                                        v-for="ff in group"
                                        :key="ff.id"
                                        type="button"
                                        @click="selectReplacement(ff)"
                                        class="w-full px-3 py-2 text-left text-sm text-slate-200 hover:bg-amber-600/30 hover:text-white transition-colors flex items-center gap-2"
                                        :class="{ 'bg-amber-600/20': selectedReplacementId === ff.id }"
                                    >
                                        <svg v-if="selectedReplacementId === ff.id" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="w-4" v-else></span>
                                        {{ ff.nombres }} {{ ff.apellido_paterno }}
                                    </button>
                                </template>
                            </div>
                        </div>
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
                        class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:bg-slate-600 disabled:cursor-not-allowed disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
                        @click="handleSave"
                        :disabled="isSaving || !selectedReplacementId"
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
