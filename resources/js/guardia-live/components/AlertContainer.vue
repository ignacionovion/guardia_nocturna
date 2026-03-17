<script setup>
import { useAlertsArray, useGuardiaAlerts } from '../composables/useGuardiaAlerts.js';

// Provide the alerts API globally
useGuardiaAlerts();

const alerts = useAlertsArray();

const alertClasses = {
    emergency: 'bg-gradient-to-r from-red-600 to-red-700 border-red-500 text-white shadow-red-900/50',
    success: 'bg-gradient-to-r from-emerald-600 to-emerald-700 border-emerald-500 text-white shadow-emerald-900/50',
    warning: 'bg-gradient-to-r from-amber-500 to-amber-600 border-amber-400 text-slate-900 shadow-amber-900/50',
    info: 'bg-gradient-to-r from-blue-600 to-blue-700 border-blue-500 text-white shadow-blue-900/50',
    error: 'bg-gradient-to-r from-rose-600 to-rose-700 border-rose-500 text-white shadow-rose-900/50',
};

const alertIcons = {
    emergency: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>`,
    success: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>`,
    warning: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>`,
    info: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>`,
    error: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>`,
};

function removeAlert(id) {
    const alert = alerts.value.find(a => a.id === id);
    if (alert) {
        alert.closing = true;
        setTimeout(() => {
            alerts.value = alerts.value.filter(a => a.id !== id);
        }, 300);
    }
}

// Helper to get icon HTML
function getIcon(type) {
    return alertIcons[type] || alertIcons.info;
}
</script>

<template>
    <div class="fixed top-6 right-6 z-[100] flex flex-col gap-4 pointer-events-none">
        <TransitionGroup
            enter-active-class="transition-all duration-400 ease-out"
            enter-from-class="opacity-0 translate-x-12 scale-90"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-12 scale-90"
        >
            <div
                v-for="alert in alerts"
                :key="alert.id"
                class="pointer-events-auto flex items-start gap-4 px-5 py-4 rounded-2xl border-2 backdrop-blur-md shadow-2xl min-w-[360px] max-w-[480px] 2xl:min-w-[420px] 2xl:max-w-[560px]"
                :class="[alertClasses[alert.type], { 'opacity-0 translate-x-4': alert.closing }]"
            >
                <div class="flex-shrink-0 mt-0.5 2xl:mt-1" v-html="getIcon(alert.type)"></div>
                <div class="flex-1 min-w-0">
                    <p v-if="alert.title" class="font-extrabold text-base 2xl:text-lg leading-tight uppercase tracking-wide">{{ alert.title }}</p>
                    <p v-if="alert.message" class="text-sm 2xl:text-base opacity-95 leading-snug mt-1 font-medium">{{ alert.message }}</p>
                </div>
                <button
                    type="button"
                    @click="removeAlert(alert.id)"
                    class="flex-shrink-0 -mr-1 -mt-1 w-7 h-7 2xl:w-8 2xl:h-8 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors"
                >
                    <svg class="w-4 h-4 2xl:w-5 2xl:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
