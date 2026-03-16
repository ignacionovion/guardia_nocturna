<script setup>
import { ref, computed, onMounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const store = useGuardiaStore();

const emergencyKeys = ref([]);
const emergencyUnits = ref([]);
const isSaving = ref(false);
const error = ref(null);

const formData = ref({
    emergency_key_id: null,
    officer_in_charge_firefighter_id: null,
    dispatched_at: '',
    arrived_at: '',
    call_details: '',
    unit_ids: [],
});

const availableFirefighters = computed(() => 
    store.presentStaff.filter(f => {
        const status = f.draft_attendance_status || f.estado_asistencia;
        return ['constituye', 'reemplazo'].includes(status);
    })
);

onMounted(async () => {
    // Fetch emergency keys and units
    console.log('[EmergenciasModal] Fetching data...');
    try {
        const [keysRes, unitsRes] = await Promise.all([
            fetch('/api/emergency-keys', { credentials: 'same-origin' }),
            fetch('/api/emergency-units', { credentials: 'same-origin' }),
        ]);

        console.log('[EmergenciasModal] Keys response:', keysRes.status, keysRes.ok);
        console.log('[EmergenciasModal] Units response:', unitsRes.status, unitsRes.ok);

        if (keysRes.ok) {
            const keysData = await keysRes.json();
            console.log('[EmergenciasModal] Keys data:', keysData);
            emergencyKeys.value = Array.isArray(keysData) ? keysData : (keysData.data || []);
            console.log('[EmergenciasModal] Keys loaded:', emergencyKeys.value.length);
        } else {
            console.error('[EmergenciasModal] Keys fetch failed:', await keysRes.text());
        }

        if (unitsRes.ok) {
            const unitsData = await unitsRes.json();
            console.log('[EmergenciasModal] Units data:', unitsData);
            const allUnits = Array.isArray(unitsData) ? unitsData : (unitsData.data || []);
            emergencyUnits.value = allUnits.filter(u => u.is_active !== false);
            console.log('[EmergenciasModal] Units loaded:', emergencyUnits.value.length);
        } else {
            console.error('[EmergenciasModal] Units fetch failed:', await unitsRes.text());
        }
    } catch (err) {
        console.error('[EmergenciasModal] Failed to load data:', err);
    }

    // Set default dispatched_at to now
    const now = new Date();
    formData.value.dispatched_at = now.toISOString().slice(0, 16);
});

async function handleSave() {
    if (!formData.value.emergency_key_id) {
        error.value = 'Debes seleccionar una clave de emergencia';
        return;
    }

    isSaving.value = true;
    error.value = null;

    const payload = {
        ...formData.value,
        guardia_id: store.guardia?.id,
    };

    const result = await store.saveEmergencia(payload);

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

function toggleUnit(unitId) {
    const idx = formData.value.unit_ids.indexOf(unitId);
    if (idx > -1) {
        formData.value.unit_ids.splice(idx, 1);
    } else {
        formData.value.unit_ids.push(unitId);
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
            v-if="store.activeModal === 'emergencias'"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
            @click.self="handleClose"
        >
            <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-600/20 border border-red-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">Registrar Emergencia</h2>
                            <p class="text-xs text-slate-400">Ingresa los datos de la emergencia</p>
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
                    <!-- Clave de emergencia -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Clave de Emergencia *</label>
                        <select
                            v-model="formData.emergency_key_id"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            <option :value="null">Seleccionar clave</option>
                            <option v-for="key in emergencyKeys" :key="key.id" :value="key.id">
                                {{ key.code }} - {{ key.description }}
                            </option>
                        </select>
                    </div>

                    <!-- A cargo -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Bombero a Cargo</label>
                        <select
                            v-model="formData.officer_in_charge_firefighter_id"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            <option :value="null">Sin asignar</option>
                            <option v-for="ff in availableFirefighters" :key="ff.id" :value="ff.id">
                                {{ ff.nombres }} {{ ff.apellido_paterno }}
                            </option>
                        </select>
                    </div>

                    <!-- Unidades -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Unidades Despachadas</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="unit in emergencyUnits"
                                :key="unit.id"
                                type="button"
                                @click="toggleUnit(unit.id)"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-colors border"
                                :class="formData.unit_ids.includes(unit.id)
                                    ? 'bg-red-600 border-red-500 text-white'
                                    : 'bg-slate-700 border-slate-600 text-slate-300 hover:bg-slate-600'"
                            >
                                {{ unit.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Hora despacho -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Hora de Despacho</label>
                        <input
                            type="datetime-local"
                            v-model="formData.dispatched_at"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                        />
                    </div>

                    <!-- Hora llegada -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Hora de Llegada</label>
                        <input
                            type="datetime-local"
                            v-model="formData.arrived_at"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                        />
                    </div>

                    <!-- Detalles -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Detalles de la Llamada</label>
                        <textarea
                            v-model="formData.call_details"
                            rows="3"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
                            placeholder="Dirección, detalles adicionales..."
                        ></textarea>
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
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
                        @click="handleSave"
                        :disabled="isSaving"
                    >
                        <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                        <span>{{ isSaving ? 'Guardando...' : 'Registrar Emergencia' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
