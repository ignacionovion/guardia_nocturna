<script setup>
import { computed } from 'vue';

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

const fullName = computed(() => {
    const f = props.firefighter;
    return [f.nombres, f.apellido_paterno].filter(Boolean).join(' ').toUpperCase();
});

const initials = computed(() => {
    const f = props.firefighter;
    const first = (f.nombres ?? '').charAt(0);
    const last = (f.apellido_paterno ?? '').charAt(0);
    return (first + last).toUpperCase();
});

const attendanceStatus = computed(() =>
    props.firefighter.draft_attendance_status || props.firefighter.estado_asistencia || 'constituye'
);

const isConfirmed = computed(() => !!props.firefighter.confirmed_at);
const isRefuerzo  = computed(() => !!props.firefighter.es_refuerzo);
const isReemplazo = computed(() => !!props.firefighter.es_reemplazo);
const isJefe      = computed(() => !!props.firefighter.es_jefe_guardia);

const statusConfig = computed(() => {
    const map = {
        constituye: { label: 'CONSTITUYE', bg: 'bg-emerald-600',    text: 'text-white' },
        reemplazo:  { label: 'REEMPLAZO',  bg: 'bg-blue-600',       text: 'text-white' },
        falta:      { label: 'FALTA',      bg: 'bg-red-600',        text: 'text-white' },
        permiso:    { label: 'PERMISO',    bg: 'bg-amber-600',      text: 'text-white' },
        licencia:   { label: 'LICENCIA',   bg: 'bg-purple-600',     text: 'text-white' },
        ausente:    { label: 'AUSENTE',    bg: 'bg-slate-600',      text: 'text-white' },
        refuerzo:   { label: 'REFUERZO',   bg: 'bg-sky-600',        text: 'text-white' },
    };
    return map[attendanceStatus.value] ?? map['constituye'];
});

const cardBorderClass = computed(() => {
    if (isConfirmed.value) return 'border-emerald-500/40';
    if (attendanceStatus.value === 'falta') return 'border-red-500/30';
    if (attendanceStatus.value === 'permiso') return 'border-amber-500/30';
    if (attendanceStatus.value === 'licencia') return 'border-purple-500/30';
    return 'border-slate-700/50';
});

const yearsService = computed(() => props.firefighter.years_service ?? null);
</script>

<template>
    <div
        class="relative rounded-xl border bg-slate-800/80 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-black/40"
        :class="cardBorderClass"
    >
        <!-- Confirmed indicator strip -->
        <div
            v-if="isConfirmed"
            class="absolute top-0 left-0 right-0 h-0.5 bg-emerald-500"
        ></div>

        <!-- Badge row (jefe / refuerzo / reemplazo) -->
        <div class="absolute top-1.5 left-1.5 flex flex-col gap-0.5 z-10">
            <span
                v-if="isJefe"
                class="text-[9px] font-bold bg-amber-500 text-black px-1.5 py-0.5 rounded-full leading-none"
            >JG</span>
            <span
                v-if="isRefuerzo"
                class="text-[9px] font-bold bg-sky-500 text-black px-1.5 py-0.5 rounded-full leading-none"
            >R</span>
            <span
                v-if="isReemplazo"
                class="text-[9px] font-bold bg-blue-500 text-white px-1.5 py-0.5 rounded-full leading-none"
            >RE</span>
        </div>

        <!-- Bed badge -->
        <div v-if="bedNumber" class="absolute top-1.5 right-1.5 z-10">
            <span class="text-[9px] font-bold bg-indigo-500 text-white px-1.5 py-0.5 rounded-full leading-none">
                {{ bedNumber }}
            </span>
        </div>

        <!-- Avatar -->
        <div class="flex flex-col items-center pt-5 pb-3 px-2">
            <div class="relative">
                <img
                    v-if="firefighter.photo_url"
                    :src="firefighter.photo_url"
                    :alt="fullName"
                    class="w-14 h-14 rounded-xl object-cover border-2"
                    :class="isConfirmed ? 'border-emerald-500' : 'border-slate-600'"
                />
                <div
                    v-else
                    class="w-14 h-14 rounded-xl flex items-center justify-center text-lg font-bold border-2"
                    :class="isConfirmed
                        ? 'bg-emerald-900/50 border-emerald-500 text-emerald-300'
                        : 'bg-slate-700/80 border-slate-600 text-slate-300'"
                >
                    {{ initials }}
                </div>
                <!-- Confirm check -->
                <div
                    v-if="isConfirmed"
                    class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center"
                >
                    <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Name & rank -->
            <p class="mt-2 text-[11px] font-bold text-white text-center leading-tight line-clamp-2">
                {{ fullName }}
            </p>
            <p v-if="firefighter.cargo" class="text-[9px] text-slate-400 text-center mt-0.5 uppercase tracking-wide">
                {{ firefighter.cargo }}
            </p>

            <!-- Service years -->
            <p v-if="yearsService !== null" class="text-[9px] text-slate-500 mt-0.5">
                {{ yearsService }} año{{ yearsService !== 1 ? 's' : '' }}
            </p>

            <!-- Status badge -->
            <span
                class="mt-2 inline-block text-[10px] font-bold px-2 py-0.5 rounded-full"
                :class="[statusConfig.bg, statusConfig.text]"
            >
                {{ statusConfig.label }}
            </span>
        </div>
    </div>
</template>
