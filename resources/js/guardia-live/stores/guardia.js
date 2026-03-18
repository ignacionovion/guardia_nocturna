import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

const JSON_HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
};

export const useGuardiaStore = defineStore('guardia', () => {
    // ── State ──────────────────────────────────────────────
    const guardia = ref(null);
    const staff = ref([]);
    const novelties = ref([]);
    const academies = ref([]);
    const birthdaysThisMonth = ref([]);
    const bedByFirefighter = ref({});
    const attendanceEnabled = ref(false);
    const attendanceSaved = ref(false);
    const attendanceMessage = ref('');
    const attendanceVariant = ref('default');
    const draftEditable = ref(false);
    const localTimeIso = ref(null);
    const guardiaTz = ref('America/Santiago');
    const bulkUpdateUrl = ref('');
    const isLoading = ref(false);
    const lastRefreshedAt = ref(null);
    const hasPendingChanges = ref(false);
    const isSaving = ref(false);
    const saveResult = ref(null);

    // Phase 4: Modal state
    const activeModal = ref(null); // 'aseo' | 'emergencias' | 'refuerzo' | 'reemplazo' | null
    const modalContext = ref(null); // Context data for active modal (e.g., firefighter for replacement)

    // Phase 5: Broadcasting state
    const broadcastConnected = ref(false);
    const echoChannel = ref(null);

    // ── Computed ───────────────────────────────────────────
    const PRESENT_STATUSES = ['constituye', 'reemplazo'];

    const presentStaff = computed(() =>
        staff.value.filter(s => {
            const status = s.draft_attendance_status || s.estado_asistencia || 'constituye';
            return PRESENT_STATUSES.includes(status);
        })
    );

    // Check if all present staff requiring confirmation are confirmed
    const allConfirmed = computed(() => {
        const requiringConfirmation = presentStaff.value.filter(s => {
            const status = s.draft_attendance_status || s.estado_asistencia || 'constituye';
            return ['constituye', 'reemplazo'].includes(status) || s.es_refuerzo || s.es_reemplazo;
        });
        // If no one requires confirmation, return false (button disabled)
        if (requiringConfirmation.length === 0) return false;
        // All must be confirmed
        return requiringConfirmation.every(s => !!s.confirmed_at);
    });

    // Count of staff requiring confirmation but not yet confirmed
    const unconfirmedCount = computed(() => {
        const requiringConfirmation = presentStaff.value.filter(s => {
            const status = s.draft_attendance_status || s.estado_asistencia || 'constituye';
            return ['constituye', 'reemplazo'].includes(status) || s.es_refuerzo || s.es_reemplazo;
        });
        return requiringConfirmation.filter(s => !s.confirmed_at).length;
    });

    const presentCount = computed(() => presentStaff.value.length);
    const visibleCount = computed(() => staff.value.length);

    // Phase 5: Computed properties for broadcasting
    const guardiaId = computed(() => guardia.value?.id ?? null);
    const tenantId = computed(() => {
        // Extract tenant ID from current URL or meta tag
        const hostname = window.location.hostname;
        const parts = hostname.split('.');
        if (parts.length >= 3) {
            return parts[0]; // e.g., 'cuarta-temuco' from 'cuarta-temuco.sas.dev-app.cl'
        }
        return null;
    });

    const attendanceVariantClasses = computed(() => {
        const map = {
            success:  'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            warning:  'bg-amber-500/20 text-amber-400 border-amber-500/30',
            danger:   'bg-red-500/20 text-red-400 border-red-500/30',
            default:  'bg-slate-700/50 text-slate-400 border-slate-600/30',
        };
        return map[attendanceVariant.value] ?? map.default;
    });

    const guardiaName = computed(() => guardia.value?.name ?? '');
    const guardiaNumber = computed(() => guardia.value?.numero_guardia ?? null);

    // ── Actions ────────────────────────────────────────────
    function initFromServer(data) {
        if (!data) return;

        guardia.value              = data.guardia;
        // Process staff to add photo_url from photo_path
        staff.value                = (data.staff ?? []).map(s => ({
            ...s,
            photo_url: s.photo_path ? `/media/${s.photo_path}` : null,
        }));
        novelties.value            = data.novelties ?? [];
        hasPendingChanges.value    = false;
        saveResult.value           = null;
        academies.value            = data.academies ?? [];
        console.log('[Store] Academies loaded:', academies.value.length, academies.value);
        birthdaysThisMonth.value   = data.birthdays_this_month ?? [];
        bedByFirefighter.value     = data.bed_by_firefighter ?? {};
        attendanceEnabled.value    = data.attendance_enabled ?? false;
        attendanceSaved.value      = data.attendance_saved ?? false;
        attendanceMessage.value    = data.attendance_message ?? '';
        attendanceVariant.value    = data.attendance_variant ?? 'default';
        draftEditable.value        = data.draft_editable ?? false;
        localTimeIso.value         = data.local_time_iso ?? null;
        guardiaTz.value            = data.guardia_tz ?? 'America/Santiago';
        bulkUpdateUrl.value        = data.bulk_update_url ?? '';
        lastRefreshedAt.value      = new Date();
    }

    async function refreshState() {
        // Don't overwrite local state while there are unsaved changes
        if (hasPendingChanges.value) {
            console.log('[GuardiaLive] refreshState skipped: pending changes');
            return;
        }
        try {
            isLoading.value = true;
            const response = await fetch('/api/guardia-live/state', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                console.warn('[GuardiaLive] refreshState failed:', response.status);
                return;
            }

            const data = await response.json();
            if (data.ok) {
                initFromServer(data);
            }
        } catch (err) {
            console.error('[GuardiaLive] refreshState error:', err);
        } finally {
            isLoading.value = false;
        }
    }

    function updateStaffAttendance(firefighterId, status) {
        const idx = staff.value.findIndex(s => s.id === firefighterId);
        if (idx !== -1) {
            staff.value[idx] = { ...staff.value[idx], draft_attendance_status: status };
        }
    }

    async function updateStatus(firefighterId, newStatus) {
        const idx = staff.value.findIndex(s => s.id === firefighterId);
        if (idx === -1) return { ok: false, message: 'Bombero no encontrado' };

        const prev = { ...staff.value[idx] };
        const prevPending = hasPendingChanges.value;

        // Optimistic update: status + pending indicator appear immediately
        staff.value[idx] = {
            ...staff.value[idx],
            draft_attendance_status: newStatus,
            confirmed_at: null,
            confirm_token: null,
        };
        hasPendingChanges.value = true;

        try {
            const res = await fetch('/draft/turno/item', {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ firefighter_id: firefighterId, attendance_status: newStatus }),
                credentials: 'same-origin',
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.ok) {
                staff.value[idx] = prev;
                hasPendingChanges.value = prevPending;
                return { ok: false, message: data.message ?? `HTTP ${res.status}` };
            }

            return { ok: true };
        } catch (err) {
            staff.value[idx] = prev;
            hasPendingChanges.value = prevPending;
            return { ok: false, message: err.message };
        }
    }

    async function saveAttendance() {
        console.log('[GuardiaLive] saveAttendance called', { isSaving: isSaving.value, url: bulkUpdateUrl.value, staffCount: staff.value.length });
        if (isSaving.value) return { ok: false, message: 'Ya guardando...' };

        const url = bulkUpdateUrl.value;
        if (!url) {
            console.warn('[GuardiaLive] saveAttendance: no bulkUpdateUrl');
            saveResult.value = { ok: false, message: 'URL de guardado no disponible' };
            return { ok: false, message: 'URL de guardado no disponible' };
        }

        const users = {};
        for (const s of staff.value) {
            const effectiveStatus = s.draft_attendance_status || s.estado_asistencia || 'constituye';
            users[s.id] = {
                estado_asistencia: effectiveStatus,
                confirm_token: s.confirm_token ?? '',
            };
        }

        isSaving.value = true;
        saveResult.value = null;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ users }),
                credentials: 'same-origin',
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.ok) {
                const msg = data.errors?.confirm?.[0]
                    ?? data.message
                    ?? `Error HTTP ${res.status}`;
                saveResult.value = { ok: false, message: msg };
                return { ok: false, message: msg };
            }

            hasPendingChanges.value = false;
            attendanceSaved.value = true;
            attendanceMessage.value = 'ASISTENCIA REGISTRADA';
            attendanceVariant.value = 'success';
            saveResult.value = { ok: true, message: 'Asistencia guardada ✓' };
            setTimeout(() => { saveResult.value = null; }, 5000);
            return { ok: true };
        } catch (err) {
            const msg = err.message ?? 'Error de red';
            saveResult.value = { ok: false, message: msg };
            return { ok: false, message: msg };
        } finally {
            isSaving.value = false;
        }
    }

    async function confirmAttendance(firefighterId, guardiaId, numeroRegistro) {
        const idx = staff.value.findIndex(s => s.id === firefighterId);
        if (idx === -1) return { ok: false, message: 'Bombero no encontrado' };

        try {
            const res = await fetch(`/admin/guardias/${guardiaId}/bomberos/${firefighterId}/confirm`, {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ numero_registro: numeroRegistro }),
                credentials: 'same-origin',
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.ok) {
                return { ok: false, message: data.message ?? 'Código inválido' };
            }

            const token = data.token;

            fetch('/draft/turno/confirm', {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ firefighter_id: firefighterId, confirm_token: token }),
                credentials: 'same-origin',
            }).catch(() => {});

            staff.value[idx] = {
                ...staff.value[idx],
                confirmed_at: new Date().toISOString(),
                confirm_token: token,
            };

            return { ok: true };
        } catch (err) {
            return { ok: false, message: err.message };
        }
    }

    // ── Phase 4: Modal Management ──────────────────────────
    function openModal(modalName, context = null) {
        activeModal.value = modalName;
        modalContext.value = context;
    }

    function closeModal() {
        activeModal.value = null;
        modalContext.value = null;
    }

    // ── Phase 4: Aseo ──────────────────────────────────────
    async function saveAseo(assignments) {
        try {
            const res = await fetch('/aseo', {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({
                    assigned_date: new Date().toISOString().split('T')[0],
                    assignments,
                }),
                credentials: 'same-origin',
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.success) {
                return { ok: false, message: data.message ?? `HTTP ${res.status}` };
            }

            return { ok: true, message: data.message ?? 'Aseo guardado correctamente' };
        } catch (err) {
            return { ok: false, message: err.message };
        }
    }

    // ── Phase 4: Emergencias ───────────────────────────────
    async function saveEmergencia(emergencyData) {
        try {
            const res = await fetch('/admin/emergencies', {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(emergencyData),
                credentials: 'same-origin',
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                return { ok: false, message: data.message ?? `HTTP ${res.status}` };
            }

            return { ok: true, message: 'Emergencia registrada correctamente' };
        } catch (err) {
            return { ok: false, message: err.message };
        }
    }

    // ── Phase 4: Refuerzo ──────────────────────────────────
    async function addRefuerzo(firefighterId) {
        if (!guardia.value?.id) {
            return { ok: false, message: 'Guardia no disponible' };
        }

        try {
            const res = await fetch('/admin/guardias/refuerzo', {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({
                    guardia_id: guardia.value.id,
                    firefighter_id: firefighterId,
                }),
                credentials: 'same-origin',
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                return { ok: false, message: data.message ?? `HTTP ${res.status}` };
            }

            // Refresh state to show the new refuerzo in the grid
            await refreshState();
            return { ok: true, message: 'Refuerzo agregado correctamente' };
        } catch (err) {
            return { ok: false, message: err.message };
        }
    }

    async function removeRefuerzo(firefighterId) {
        if (!guardia.value?.id) {
            return { ok: false, message: 'Guardia no disponible' };
        }

        try {
            const res = await fetch('/admin/guardias/refuerzo/remove', {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({
                    guardia_id: guardia.value.id,
                    firefighter_id: firefighterId,
                }),
                credentials: 'same-origin',
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                return { ok: false, message: data.message ?? `HTTP ${res.status}` };
            }

            await refreshState();
            return { ok: true, message: 'Refuerzo removido correctamente' };
        } catch (err) {
            return { ok: false, message: err.message };
        }
    }

    // ── Phase 4: Reemplazo ─────────────────────────────────
    async function assignReplacement(originalFirefighterId, replacementFirefighterId) {
        if (!guardia.value?.id) {
            return { ok: false, message: 'Guardia no disponible' };
        }

        try {
            const res = await fetch('/admin/guardias/replacement', {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({
                    guardia_id: guardia.value.id,
                    original_firefighter_id: originalFirefighterId,
                    replacement_firefighter_id: replacementFirefighterId,
                }),
                credentials: 'same-origin',
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                return { ok: false, message: data.message ?? `HTTP ${res.status}` };
            }

            await refreshState();
            return { ok: true, message: 'Reemplazo asignado correctamente' };
        } catch (err) {
            return { ok: false, message: err.message };
        }
    }

    async function undoReplacement(replacementId) {
        try {
            const res = await fetch(`/admin/guardias/replacement/${replacementId}/undo`, {
                method: 'POST',
                headers: { ...JSON_HEADERS, 'X-CSRF-TOKEN': csrfToken() },
                credentials: 'same-origin',
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                return { ok: false, message: data.message ?? `HTTP ${res.status}` };
            }

            await refreshState();
            return { ok: true, message: 'Reemplazo deshecho correctamente' };
        } catch (err) {
            return { ok: false, message: err.message };
        }
    }

    return {
        // state
        guardia,
        staff,
        novelties,
        academies,
        birthdaysThisMonth,
        bedByFirefighter,
        attendanceEnabled,
        attendanceSaved,
        attendanceMessage,
        attendanceVariant,
        draftEditable,
        localTimeIso,
        guardiaTz,
        bulkUpdateUrl,
        isLoading,
        lastRefreshedAt,
        hasPendingChanges,
        isSaving,
        saveResult,
        activeModal,
        modalContext,
        // computed
        presentStaff,
        presentCount,
        visibleCount,
        allConfirmed,
        unconfirmedCount,
        attendanceVariantClasses,
        guardiaName,
        guardiaNumber,
        guardiaId,
        tenantId,
        broadcastConnected,
        // actions
        initFromServer,
        refreshState,
        updateStaffAttendance,
        updateStatus,
        confirmAttendance,
        saveAttendance,
        // Phase 4 actions
        openModal,
        closeModal,
        saveAseo,
        saveEmergencia,
        addRefuerzo,
        removeRefuerzo,
        assignReplacement,
        undoReplacement,
    };
});
