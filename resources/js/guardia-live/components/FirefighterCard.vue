<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useGuardiaStore } from '../stores/guardia';

const props = defineProps({
    firefighter: {
        type: Object,
        required: true,
    },
    bedNumber: {
        type: [String, Number],
        default: null,
    },
});

const store = useGuardiaStore();

// ── Local UI state ─────────────────────────────────────────
const showStatusPicker  = ref(false);
const confirmCode       = ref('');
const confirmError      = ref('');
const statusError       = ref('');
const isConfirming      = ref(false);
const isChangingStatus  = ref(false);
const pickerRef         = ref(null);

// ── Derived from prop (reactive via store updates) ─────────
const f           = computed(() => props.firefighter);
const guardiaId   = computed(() => store.guardia?.id);

const effectiveStatus = computed(() =>
    f.value.draft_attendance_status || f.value.estado_asistencia || 'constituye'
);

const isConfirmed          = computed(() => !!f.value.confirmed_at);
const statusLocked         = computed(() => f.value.es_reemplazo || f.value.es_refuerzo);
const requiresConfirmation = computed(() => {
    const s = effectiveStatus.value;
    return ['constituye', 'reemplazo'].includes(s) || f.value.es_refuerzo || f.value.es_reemplazo;
});

const canBeReplaced = computed(() => {
    const s = effectiveStatus.value;
    return s === 'constituye' && !f.value.es_refuerzo && !f.value.es_reemplazo;
});

function openReplacementModal() {
    store.openModal('reemplazo', { firefighter: f.value });
}

// ── Status catalogue ───────────────────────────────────────
const STATUSES = [
    { value: 'constituye', label: 'CONSTITUYE', btnClass: 'bg-emerald-600 hover:bg-emerald-500 text-white',  dot: 'bg-emerald-500' },
    { value: 'permiso',    label: 'PERMISO',    btnClass: 'bg-amber-600 hover:bg-amber-500 text-white',     dot: 'bg-amber-500'   },
    { value: 'ausente',    label: 'AUSENTE',    btnClass: 'bg-slate-600 hover:bg-slate-500 text-white',     dot: 'bg-slate-500'   },
    { value: 'licencia',   label: 'LICENCIA',   btnClass: 'bg-blue-600 hover:bg-blue-500 text-white',       dot: 'bg-blue-500'    },
    { value: 'falta',      label: 'FALTA',      btnClass: 'bg-rose-600 hover:bg-rose-500 text-white',       dot: 'bg-rose-500'    },
];

const statusConfig = computed(() =>
    STATUSES.find(s => s.value === effectiveStatus.value) ?? STATUSES[0]
);

const cardBorderClass = computed(() => {
    if (isConfirmed.value) return 'border-emerald-500/60';
    const map = {
        constituye: 'border-emerald-500/30',
        reemplazo:  'border-purple-500/40',
        permiso:    'border-amber-500/40',
        licencia:   'border-blue-500/40',
        falta:      'border-rose-500/40',
        ausente:    'border-slate-600/50',
    };
    return map[effectiveStatus.value] ?? 'border-slate-700/50';
});

const fullName = computed(() =>
    [f.value.nombres, f.value.apellido_paterno].filter(Boolean).join(' ').toUpperCase()
);

const initials = computed(() => {
    const first = (f.value.nombres ?? '').charAt(0);
    const last  = (f.value.apellido_paterno ?? '').charAt(0);
    return (first + last).toUpperCase();
});

const yearsService = computed(() => f.value.years_service ?? null);

// ── Actions ────────────────────────────────────────────────
async function selectStatus(newStatus) {
    if (newStatus === effectiveStatus.value) { showStatusPicker.value = false; return; }
    showStatusPicker.value = false;
    statusError.value = '';
    isChangingStatus.value = true;
    const result = await store.updateStatus(f.value.id, newStatus);
    isChangingStatus.value = false;
    if (!result.ok) {
        statusError.value = result.message ?? 'Error al cambiar estado';
        setTimeout(() => { statusError.value = ''; }, 4000);
    }
}

async function doConfirm() {
    const code = confirmCode.value.trim();
    if (!code) { confirmError.value = 'Ingresa el número de registro'; return; }
    confirmError.value = '';
    isConfirming.value = true;
    const result = await store.confirmAttendance(f.value.id, guardiaId.value, code);
    isConfirming.value = false;
    if (!result.ok) {
        confirmError.value = result.message ?? 'Código inválido';
    } else {
        confirmCode.value = '';
    }
}

// Close status picker when clicking outside
function onDocClick(e) {
    if (pickerRef.value && !pickerRef.value.contains(e.target)) {
        showStatusPicker.value = false;
    }
}

onMounted(() => document.addEventListener('click', onDocClick, true));
onUnmounted(() => document.removeEventListener('click', onDocClick, true));
</script>

<template>
    <div
        class="relative rounded-xl border bg-slate-800/80 backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-black/40"
        :class="cardBorderClass"
    >
        <!-- Confirmed strip -->
        <div v-if="isConfirmed" class="absolute top-0 left-0 right-0 h-0.5 bg-emerald-500 rounded-t-xl"></div>

        <!-- Top-left badges -->
        <div class="absolute top-1.5 left-1.5 flex flex-col gap-0.5 z-10">
            <span v-if="f.es_jefe_guardia" class="text-[9px] font-bold bg-amber-500 text-black px-1.5 py-0.5 rounded-full leading-none">JG</span>
            <span v-if="f.es_refuerzo"     class="text-[9px] font-bold bg-sky-500 text-black px-1.5 py-0.5 rounded-full leading-none">RF</span>
            <span v-if="f.es_reemplazo"    class="text-[9px] font-bold bg-purple-500 text-white px-1.5 py-0.5 rounded-full leading-none">RE</span>
        </div>

        <!-- Bed badge -->
        <div v-if="bedNumber" class="absolute top-1.5 right-1.5 z-10">
            <span class="text-[9px] font-bold bg-indigo-500 text-white px-1.5 py-0.5 rounded-full leading-none">#{{ bedNumber }}</span>
        </div>

        <!-- Avatar + name -->
        <div class="flex flex-col items-center pt-5 pb-2 px-2">
            <div class="relative">
                <img
                    v-if="f.photo_url"
                    :src="f.photo_url"
                    :alt="fullName"
                    class="w-14 h-14 rounded-xl object-cover border-2 transition-colors"
                    :class="isConfirmed ? 'border-emerald-500' : 'border-slate-600'"
                />
                <div
                    v-else
                    class="w-14 h-14 rounded-xl flex items-center justify-center text-lg font-bold border-2 transition-colors"
                    :class="isConfirmed ? 'bg-emerald-900/50 border-emerald-500 text-emerald-300' : 'bg-slate-700/80 border-slate-600 text-slate-300'"
                >{{ initials }}</div>

                <!-- Confirmed checkmark -->
                <div v-if="isConfirmed" class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center shadow">
                    <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <p class="mt-2 text-[11px] font-bold text-white text-center leading-tight line-clamp-2">{{ fullName }}</p>
            <p v-if="f.cargo" class="text-[9px] text-slate-400 text-center mt-0.5 uppercase tracking-wide">{{ f.cargo }}</p>
            <p v-if="yearsService !== null" class="text-[9px] text-slate-500 mt-0.5">{{ yearsService }}a</p>
        </div>

        <!-- Card body -->
        <div class="px-2 pb-2.5 space-y-1.5">

            <!-- ── STATUS SELECTOR ── -->
            <div ref="pickerRef" class="relative">

                <!-- Locked status (reemplazo/refuerzo cannot be changed) -->
                <div
                    v-if="statusLocked"
                    class="w-full text-center px-2 py-1.5 rounded-lg text-xs font-bold uppercase"
                    :class="statusConfig.btnClass"
                >{{ statusConfig.label }}</div>

                <!-- Interactive status button -->
                <button
                    v-else
                    type="button"
                    class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all duration-150"
                    :class="[statusConfig.btnClass, isChangingStatus ? 'opacity-60 cursor-wait' : '']"
                    :disabled="isChangingStatus"
                    @click.stop="showStatusPicker = !showStatusPicker"
                >
                    <span>{{ statusConfig.label }}</span>
                    <svg class="w-3 h-3 transition-transform" :class="showStatusPicker ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Status dropdown -->
                <div
                    v-if="showStatusPicker && !statusLocked"
                    class="absolute left-0 right-0 top-full mt-1 rounded-lg border border-slate-700 bg-slate-900 shadow-xl z-50 overflow-hidden"
                >
                    <button
                        v-for="s in STATUSES"
                        :key="s.value"
                        type="button"
                        class="w-full flex items-center gap-2 px-2.5 py-1.5 text-xs font-bold uppercase transition-colors"
                        :class="s.value === effectiveStatus ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        @click.stop="selectStatus(s.value)"
                    >
                        <span class="w-2 h-2 rounded-full flex-shrink-0" :class="s.dot"></span>
                        {{ s.label }}
                        <svg v-if="s.value === effectiveStatus" class="ml-auto w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Status change error -->
            <p v-if="statusError" class="text-[10px] text-red-400 font-medium px-0.5">⚠ {{ statusError }}</p>

            <!-- ── REPLACEMENT BUTTON ── -->
            <button
                v-if="canBeReplaced"
                type="button"
                class="w-full flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-colors bg-amber-600/20 hover:bg-amber-600/30 border border-amber-600/40 text-amber-400"
                @click="openReplacementModal"
            >
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Reemplazar</span>
            </button>

            <!-- ── CONFIRMATION BOX ── -->
            <template v-if="requiresConfirmation">

                <!-- Already confirmed -->
                <div v-if="isConfirmed" class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-2 py-1.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[10px] font-bold uppercase text-emerald-400">Confirmado</span>
                </div>

                <!-- Not confirmed yet -->
                <div v-else class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-2 py-1.5 space-y-1">
                    <p class="text-[10px] font-bold uppercase text-rose-400">Sin confirmar</p>
                    <div class="flex items-center gap-1">
                        <input
                            v-model="confirmCode"
                            type="password"
                            inputmode="numeric"
                            placeholder="N° Registro"
                            autocomplete="one-time-code"
                            class="flex-1 min-w-0 px-2 py-1 rounded border border-slate-700 bg-slate-800 text-xs font-semibold text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-rose-500/60"
                            :disabled="isConfirming"
                            @keydown.enter.prevent="doConfirm"
                        />
                        <button
                            type="button"
                            class="shrink-0 px-2.5 py-1 rounded text-xs font-bold uppercase transition-colors"
                            :class="isConfirming ? 'bg-slate-700 text-slate-400 cursor-wait' : 'bg-rose-600 hover:bg-rose-500 text-white'"
                            :disabled="isConfirming"
                            @click="doConfirm"
                        >
                            <svg v-if="isConfirming" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span v-else>OK</span>
                        </button>
                    </div>
                    <p v-if="confirmError" class="text-[10px] text-red-400 font-medium">⚠ {{ confirmError }}</p>
                </div>

            </template>

        </div>
    </div>
</template>
