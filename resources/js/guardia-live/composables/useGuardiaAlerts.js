import { ref, provide, inject } from 'vue';

const alerts = ref([]);
let idCounter = 0;

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

const GUARDIA_ALERTS_KEY = Symbol('guardia-alerts');

export function createGuardiaAlerts() {
    function addAlert({ type = 'info', title, message, duration = 5000, persistent = false }) {
        const id = ++idCounter;
        const alert = {
            id,
            type,
            title,
            message,
            persistent,
            closing: false,
        };
        alerts.value.push(alert);

        if (!persistent && duration > 0) {
            setTimeout(() => removeAlert(id), duration);
        }

        return id;
    }

    function removeAlert(id) {
        const alert = alerts.value.find(a => a.id === id);
        if (alert) {
            alert.closing = true;
            setTimeout(() => {
                alerts.value = alerts.value.filter(a => a.id !== id);
            }, 300);
        }
    }

    // Expose on window for external use
    if (typeof window !== 'undefined') {
        window.guardiaLiveAlerts = {
            add: addAlert,
            remove: removeAlert,
            emergency: (title, msg) => addAlert({ type: 'emergency', title, message: msg, duration: 8000 }),
            success: (title, msg) => addAlert({ type: 'success', title, message: msg, duration: 4000 }),
            warning: (title, msg) => addAlert({ type: 'warning', title, message: msg, duration: 6000 }),
            info: (title, msg) => addAlert({ type: 'info', title, message: msg, duration: 4000 }),
            error: (title, msg) => addAlert({ type: 'error', title, message: msg, duration: 6000 }),
        };
    }

    return {
        alerts,
        alertClasses,
        alertIcons,
        addAlert,
        removeAlert,
        emergency: (title, msg) => addAlert({ type: 'emergency', title, message: msg, duration: 8000 }),
        success: (title, msg) => addAlert({ type: 'success', title, message: msg, duration: 4000 }),
        warning: (title, msg) => addAlert({ type: 'warning', title, message: msg, duration: 6000 }),
        info: (title, msg) => addAlert({ type: 'info', title, message: msg, duration: 4000 }),
        error: (title, msg) => addAlert({ type: 'error', title, message: msg, duration: 6000 }),
    };
}

export function provideGuardiaAlerts() {
    const alertsApi = createGuardiaAlerts();
    provide(GUARDIA_ALERTS_KEY, alertsApi);
    return alertsApi;
}

export function useGuardiaAlerts() {
    const alertsApi = inject(GUARDIA_ALERTS_KEY);
    if (!alertsApi) {
        // Fallback to window API if not provided
        if (typeof window !== 'undefined' && window.guardiaLiveAlerts) {
            return window.guardiaLiveAlerts;
        }
        throw new Error('useGuardiaAlerts must be used within a component that provides guardia alerts');
    }
    return alertsApi;
}

// Get alerts array for use in AlertContainer component
export function useAlertsArray() {
    return alerts;
}
