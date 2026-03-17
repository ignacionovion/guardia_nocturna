<script setup>
import { useAlertsArray, useGuardiaAlerts } from '../composables/useGuardiaAlerts.js';

// Provide the alerts API globally
useGuardiaAlerts();

const alerts = useAlertsArray();

const alertClasses = {
    emergency: 'bg-red-600/90 border-red-500 text-white shadow-red-900/50',
    success: 'bg-emerald-600/90 border-emerald-500 text-white shadow-emerald-900/50',
    warning: 'bg-amber-500/90 border-amber-400 text-slate-900 shadow-amber-900/50',
    info: 'bg-blue-600/90 border-blue-500 text-white shadow-blue-900/50',
    error: 'bg-rose-600/90 border-rose-500 text-white shadow-rose-900/50',
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
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-3 pointer-events-none">
        <TransitionGroup
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-8 scale-95"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-8 scale-95"
        >
            <div
                v-for="alert in alerts"
                :key="alert.id"
                class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border-2 backdrop-blur-sm shadow-2xl min-w-[320px] max-w-[400px]"
                :class="[alertClasses[alert.type], { 'opacity-0 translate-x-4': alert.closing }]"
            >
                <div class="flex-shrink-0 mt-0.5" v-html="getIcon(alert.type)"></div>
                <div class="flex-1 min-w-0">
                    <p v-if="alert.title" class="font-bold text-sm leading-tight">{{ alert.title }}</p>
                    <p v-if="alert.message" class="text-sm opacity-90 leading-snug mt-0.5">{{ alert.message }}</p>
                </div>
                <button
                    type="button"
                    @click="removeAlert(alert.id)"
                    class="flex-shrink-0 -mr-1 -mt-1 w-6 h-6 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
