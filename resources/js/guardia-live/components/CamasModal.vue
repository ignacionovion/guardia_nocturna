<script setup>
import { ref, computed, onMounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();
const emit = defineEmits(['close']);

const beds = ref([]);
const firefighters = ref([]);
const isLoading = ref(false);
const isSaving = ref(false);
const selectedBed = ref(null);
const selectedFirefighter = ref(null);
const errorMessage = ref('');

const availableBeds = computed(() => beds.value.filter(b => b.status === 'available'));
const occupiedBeds = computed(() => beds.value.filter(b => b.status === 'occupied'));

async function loadBeds() {
    isLoading.value = true;
    errorMessage.value = '';
    try {
        const response = await fetch('/api/beds');
        if (!response.ok) throw new Error('Error al cargar camas');
        const data = await response.json();
        beds.value = data.beds || [];
    } catch (error) {
        console.error('Error loading beds:', error);
        errorMessage.value = 'Error al cargar las camas';
    } finally {
        isLoading.value = false;
    }
}

async function loadFirefighters() {
    try {
        const response = await fetch('/api/beds/available-firefighters');
        if (!response.ok) throw new Error('Error al cargar bomberos');
        const data = await response.json();
        firefighters.value = data.firefighters || [];
    } catch (error) {
        console.error('Error loading firefighters:', error);
    }
}

async function assignBed() {
    if (!selectedBed.value || !selectedFirefighter.value) {
        errorMessage.value = 'Debes seleccionar una cama y un bombero';
        return;
    }

    isSaving.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch('/api/beds/assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                bed_id: selectedBed.value,
                firefighter_id: selectedFirefighter.value,
            }),
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error al asignar cama');
        }

        await loadBeds();
        await loadFirefighters();
        await store.refreshState();

        selectedBed.value = null;
        selectedFirefighter.value = null;
    } catch (error) {
        console.error('Error assigning bed:', error);
        errorMessage.value = error.message || 'Error al asignar cama';
    } finally {
        isSaving.value = false;
    }
}

async function releaseBed(bedId) {
    if (!confirm('¿Estás seguro de liberar esta cama?')) return;

    isSaving.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch('/api/beds/release', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ bed_id: bedId }),
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error al liberar cama');
        }

        await loadBeds();
        await loadFirefighters();
        await store.refreshState();
    } catch (error) {
        console.error('Error releasing bed:', error);
        errorMessage.value = error.message || 'Error al liberar cama';
    } finally {
        isSaving.value = false;
    }
}

function selectBedForAssignment(bedId) {
    selectedBed.value = bedId;
}

onMounted(() => {
    loadBeds();
    loadFirefighters();
});
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V6a2 2 0 0 1 2-2h3" />
                        <circle cx="7" cy="9" r="1" />
                    </svg>
                    <h2 class="text-lg font-semibold text-white">Gestión de Camas</h2>
                </div>
                <button
                    type="button"
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-all bg-slate-800 border border-slate-700 hover:bg-slate-700"
                    @click="emit('close')"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error message -->
            <div v-if="errorMessage" class="mx-4 mt-4 p-3 rounded-lg bg-red-900/20 border border-red-700/50">
                <p class="text-sm text-red-400">{{ errorMessage }}</p>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4">
                
                <!-- Loading state -->
                <div v-if="isLoading" class="flex items-center justify-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-8 h-8 animate-spin text-slate-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                        <span class="text-sm text-slate-500">Cargando camas...</span>
                    </div>
                </div>

                <div v-else class="space-y-6">
                    
                    <!-- Assignment section -->
                    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
                        <h3 class="text-sm font-semibold text-white mb-3">Asignar Cama</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Select bed -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-2">Cama</label>
                                <select
                                    v-model="selectedBed"
                                    class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    :disabled="isSaving"
                                >
                                    <option :value="null">Seleccionar cama...</option>
                                    <option v-for="bed in availableBeds" :key="bed.id" :value="bed.id">
                                        Cama {{ bed.number }}
                                    </option>
                                </select>
                            </div>

                            <!-- Select firefighter -->
                            <div>
                                <label class="block text-xs text-slate-400 mb-2">Bombero</label>
                                <select
                                    v-model="selectedFirefighter"
                                    class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    :disabled="isSaving"
                                >
                                    <option :value="null">Seleccionar bombero...</option>
                                    <option v-for="ff in firefighters" :key="ff.id" :value="ff.id">
                                        {{ ff.nombre_completo }}{{ ff.current_bed ? ` (Cama ${ff.current_bed})` : '' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-4 w-full px-4 py-2 rounded-lg border transition-all text-sm font-semibold"
                            :class="isSaving || !selectedBed || !selectedFirefighter
                                ? 'bg-slate-800 border-slate-700 text-slate-500 cursor-not-allowed'
                                : 'bg-emerald-600 border-emerald-500 text-white hover:bg-emerald-500'"
                            :disabled="isSaving || !selectedBed || !selectedFirefighter"
                            @click="assignBed"
                        >
                            <span v-if="isSaving">Asignando...</span>
                            <span v-else>Asignar Cama</span>
                        </button>
                    </div>

                    <!-- Occupied beds -->
                    <div v-if="occupiedBeds.length > 0">
                        <h3 class="text-sm font-semibold text-white mb-3">Camas Ocupadas</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div
                                v-for="bed in occupiedBeds"
                                :key="bed.id"
                                class="rounded-lg border border-emerald-500/30 bg-emerald-900/10 p-3"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V6a2 2 0 0 1 2-2h3" />
                                            <circle cx="7" cy="9" r="1" />
                                        </svg>
                                        <span class="text-sm font-bold text-emerald-400">Cama {{ bed.number }}</span>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">Ocupada</span>
                                </div>
                                <div v-if="bed.assignment" class="space-y-1">
                                    <p class="text-sm font-semibold text-white">{{ bed.assignment.firefighter_name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ bed.assignment.assigned_source === 'qr' ? 'Asignado por QR' : 'Asignado manualmente' }}
                                    </p>
                                    <button
                                        type="button"
                                        class="mt-2 w-full px-3 py-1.5 rounded-lg border border-red-700/50 bg-red-900/20 text-red-400 text-xs font-semibold hover:bg-red-900/40 transition-all"
                                        :disabled="isSaving"
                                        @click="releaseBed(bed.id)"
                                    >
                                        Liberar Cama
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available beds -->
                    <div v-if="availableBeds.length > 0">
                        <h3 class="text-sm font-semibold text-white mb-3">Camas Disponibles</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                            <button
                                v-for="bed in availableBeds"
                                :key="bed.id"
                                type="button"
                                class="aspect-square rounded-lg border transition-all flex flex-col items-center justify-center"
                                :class="selectedBed === bed.id
                                    ? 'border-emerald-500 bg-emerald-500/20 text-emerald-400'
                                    : 'border-slate-700 bg-slate-800/50 text-slate-400 hover:border-slate-600 hover:bg-slate-800'"
                                @click="selectBedForAssignment(bed.id)"
                            >
                                <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V6a2 2 0 0 1 2-2h3" />
                                    <circle cx="7" cy="9" r="1" />
                                </svg>
                                <span class="text-sm font-bold">{{ bed.number }}</span>
                                <span class="text-xs">Disponible</span>
                            </button>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-if="beds.length === 0 && !isLoading" class="text-center py-12">
                        <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V6a2 2 0 0 1 2-2h3" />
                            <circle cx="7" cy="9" r="1" />
                        </svg>
                        <p class="text-sm text-slate-500">No hay camas registradas</p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>
