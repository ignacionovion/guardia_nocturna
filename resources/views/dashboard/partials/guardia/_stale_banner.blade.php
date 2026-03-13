{{-- Banner de asistencia desactualizada --}}
<div id="attendance-stale-banner" class="hidden fixed inset-0 z-[55] flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAttendanceStaleBanner()"></div>
    <div class="relative w-full max-w-lg mx-4 p-6 rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 shadow-2xl">
        <button onclick="closeAttendanceStaleBanner()" class="absolute top-3 right-3 w-8 h-8 rounded-lg icon-box icon-box-amber icon-box-sm">
            <i class="fas fa-times"></i>
        </button>
        <div class="flex items-start gap-4">
            <div class="icon-box icon-box-amber icon-box-md shrink-0">
                <i class="fas fa-triangle-exclamation text-xl"></i>
            </div>
            <div class="min-w-0 pt-1">
                <div class="text-title-sm text-amber-900 dark:text-amber-200 mb-2">Asistencia desactualizada</div>
                <div class="text-body text-amber-800 dark:text-amber-300">Se detectaron cambios después de guardar la asistencia. Debes presionar <span class="font-semibold text-amber-700 dark:text-amber-200">Guardar Asistencia</span> nuevamente para confirmar.</div>
            </div>
        </div>
    </div>
</div>
