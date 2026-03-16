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
    { value: 'constituye', label: 'CONSTITUYE', btnClass: 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-900/50',  dot: 'bg-emerald-500' },
    { value: 'permiso',    label: 'PERMISO',    btnClass: 'bg-blue-600 hover:bg-blue-500 text-white shadow-blue-900/50',     dot: 'bg-blue-500'   },
    { value: 'ausente',    label: 'AUSENTE',    btnClass: 'bg-slate-600 hover:bg-slate-500 text-white shadow-slate-900/50',     dot: 'bg-slate-500'   },
    { value: 'licencia',   label: 'LICENCIA',   btnClass: 'bg-orange-600 hover:bg-orange-500 text-white shadow-orange-900/50',       dot: 'bg-orange-500'    },
    { value: 'falta',      label: 'FALTA',      btnClass: 'bg-red-600 hover:bg-red-500 text-white shadow-red-900/50',       dot: 'bg-red-500'    },
];

const statusConfig = computed(() =>
    STATUSES.find(s => s.value === effectiveStatus.value) ?? STATUSES[0]
);

const cardBorderClass = computed(() => {
    if (isConfirmed.value) return 'border-emerald-500 border-2';
    const map = {
        constituye: 'border-emerald-500/40',
        reemplazo:  'border-purple-500/40',
        permiso:    'border-blue-500/40',
        licencia:   'border-orange-500/40',
        falta:      'border-red-500/40',
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

const serviceLabel = computed(() => {
    const years = f.value.years_service;
    const months = f.value.months_service;
    if (years && months) {
        return `${years}a ${months}m`;
    } else if (years) {
        return `${years} años`;
    } else if (months) {
        return `${months} meses`;
    }
    return '—';
});

const radialNumber = computed(() => f.value.numero_portatil ?? null);

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
        class="group relative rounded-lg border bg-slate-900 overflow-hidden shadow-md hover:shadow-xl transition-all duration-200 h-full flex flex-col"
        :class="cardBorderClass"
    >
        <!-- Confirmed strip -->
        <div v-if="isConfirmed" class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-400 z-20 animate-pulse"></div>

        <!-- Photo block with overlay info (like old dashboard) -->
        <div class="relative w-full h-48 bg-slate-800 overflow-hidden">
            <!-- Photo or initials fallback -->
            <img
                v-if="f.photo_url"
                :src="f.photo_url"
                :alt="fullName"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                style="object-position: center 20%;"
            />
            <div
                v-else
                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800"
            >
                <span class="text-4xl font-bold text-slate-500">{{ initials }}</span>
            </div>

            <!-- Gradient overlay for text readability -->
            <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-900 via-slate-900/90 to-transparent pointer-events-none"></div>

            <!-- Firefighter info overlaid on photo -->
            <div class="absolute inset-x-0 bottom-0 px-2.5 py-2.5 z-10">
                <div class="text-base font-bold text-white leading-tight truncate drop-shadow-lg">
                    {{ fullName }}
                </div>
                <div v-if="f.cargo" class="text-sm text-white/90 truncate mt-0.5">
                    {{ f.cargo }}
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <div v-if="serviceLabel" class="flex items-center gap-1 text-sm text-white/80">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ serviceLabel }}</span>
                    </div>
                    <div v-if="radialNumber" class="flex items-center gap-1 text-sm text-white/80">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                        <span>{{ radialNumber }}</span>
                    </div>
                </div>
            </div>

            <!-- Badges top-right corner -->
            <div class="absolute top-1.5 right-1.5 flex flex-col gap-1 z-10">
                <span v-if="f.es_jefe_guardia" class="w-5 h-5 rounded bg-amber-500/90 flex items-center justify-center shadow" title="Jefe de Guardia">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </span>
            </div>

            <!-- Badge bed top-left corner -->
            <div v-if="bedNumber" class="absolute top-1.5 left-1.5 flex items-center gap-1 bg-slate-900/80 backdrop-blur-sm rounded px-1.5 py-0.5 shadow z-10">
                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-xs font-bold text-white">#{{ bedNumber }}</span>
            </div>

            <!-- Badge refuerzo/reemplazo -->
            <div class="absolute top-1.5 left-1.5 flex flex-col gap-1 z-10" :class="bedNumber ? 'top-8' : ''">
                <span v-if="f.es_refuerzo" class="text-xs font-bold uppercase px-1.5 py-0.5 rounded bg-sky-500/90 text-white shadow">REFUERZO</span>
                <span v-if="f.es_reemplazo" class="text-xs font-bold uppercase px-1.5 py-0.5 rounded bg-purple-500/90 text-white shadow">REEMPLAZO</span>
            </div>

            <!-- Confirmed checkmark overlay -->
            <div v-if="isConfirmed" class="absolute top-2 left-1/2 -translate-x-1/2 flex items-center gap-1.5 bg-emerald-500/90 backdrop-blur-sm rounded-full px-2.5 py-1 shadow-lg z-10 animate-pulse">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-xs font-bold text-white uppercase">Confirmado</span>
            </div>
        </div>

        <!-- Card body -->
        <div class="p-2 space-y-1.5 flex-1 flex flex-col">

            <!-- ── STATUS SELECTOR ── -->
            <div ref="pickerRef" class="relative">

                <!-- Locked status (reemplazo/refuerzo cannot be changed) -->
                <div
                    v-if="statusLocked"
                    class="w-full text-center px-3 py-2 rounded-lg text-xs font-extrabold uppercase shadow-lg"
                    :class="statusConfig.btnClass"
                >{{ statusConfig.label }}</div>

                <!-- Interactive status button -->
                <button
                    v-else
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-extrabold uppercase transition-all duration-200 shadow-lg"
                    :class="[statusConfig.btnClass, isChangingStatus ? 'opacity-60 cursor-wait' : 'hover:scale-105']"
                    :disabled="isChangingStatus"
                    @click.stop="showStatusPicker = !showStatusPicker"
                >
                    <span>{{ statusConfig.label }}</span>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="showStatusPicker ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Status dropdown -->
                <div
                    v-if="showStatusPicker && !statusLocked"
                    class="absolute left-0 right-0 top-full mt-1 rounded-lg border-2 border-slate-700 bg-slate-900 shadow-2xl z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
                >
                    <button
                        v-for="s in STATUSES"
                        :key="s.value"
                        type="button"
                        class="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold uppercase transition-all duration-150"
                        :class="s.value === effectiveStatus ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white hover:pl-4'"
                        @click.stop="selectStatus(s.value)"
                    >
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 shadow-lg" :class="s.dot"></span>
                        {{ s.label }}
                        <svg v-if="s.value === effectiveStatus" class="ml-auto w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Status change error -->
            <p v-if="statusError" class="text-[10px] text-red-400 font-bold px-1 py-1 bg-red-500/10 rounded border border-red-500/30">⚠ {{ statusError }}</p>

            <!-- ── REPLACEMENT BUTTON ── -->
            <button
                v-if="canBeReplaced"
                type="button"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-[11px] font-extrabold uppercase transition-all duration-200 bg-purple-600/20 hover:bg-purple-600/30 border-2 border-purple-600/40 text-purple-400 hover:scale-105 shadow-lg hover:shadow-purple-900/50"
                @click="openReplacementModal"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Reemplazar</span>
            </button>

            <!-- ── CONFIRMATION BOX ── -->
            <template v-if="requiresConfirmation">

                <!-- Already confirmed -->
                <div v-if="isConfirmed" class="rounded-lg border-2 border-emerald-500/50 bg-emerald-500/20 px-3 py-2 flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/30">
                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-extrabold uppercase text-emerald-400 tracking-wide">Confirmado</span>
                </div>

                <!-- Not confirmed yet -->
                <div class="rounded-lg border-2 border-rose-500/40 bg-rose-500/10 px-3 py-2 space-y-2 shadow-lg shadow-rose-900/30">
                    <p class="text-[11px] font-extrabold uppercase text-rose-400 text-center">Sin confirmar</p>
                    <div class="flex items-center gap-1.5">
                        <input
                            v-model="confirmCode"
                            type="password"
                            inputmode="numeric"
                            placeholder="N° Registro"
                            autocomplete="one-time-code"
                            class="flex-1 min-w-0 px-2.5 py-1.5 rounded-md border-2 border-slate-700 bg-slate-800 text-xs font-bold text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60 focus:border-rose-500 transition-all"
                            :disabled="isConfirming"
                            @keydown.enter.prevent="doConfirm"
                        />
                        <button
                            type="button"
                            class="shrink-0 px-3 py-1.5 rounded-md text-xs font-extrabold uppercase transition-all duration-200 shadow-lg"
                            :class="isConfirming ? 'bg-slate-700 text-slate-400 cursor-wait' : 'bg-rose-600 hover:bg-rose-500 text-white hover:scale-105'"
                            :disabled="isConfirming"
                            @click="doConfirm"
                        >
                            <svg v-if="isConfirming" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span v-else>OK</span>
                        </button>
                    </div>
                    <p v-if="confirmError" class="text-[10px] text-red-400 font-bold text-center">⚠ {{ confirmError }}</p>
                </div>

            </template>

        </div>
    </div>
</template>
