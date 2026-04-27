@extends('central.layouts.app')

@section('title', 'Zona de peligro')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Zona de peligro</h1>
            <p class="text-slate-600 text-sm mt-1">
                Acciones destructivas irreversibles. Solo super administradores. Requiere
                <code class="text-xs bg-slate-100 px-1 rounded">{{ $phrase }}</code> y confirmaciones explícitas.
            </p>
        </div>

        @if(! $enabled)
            <div class="rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                <p class="font-semibold">Zona de peligro deshabilitada en este entorno</p>
                <p class="mt-2 text-amber-800/90">
                    Para habilitarla en staging o VPS, define en <code class="text-xs bg-white/80 px-1 rounded">.env</code>:
                </p>
                <pre class="mt-2 text-xs bg-white/80 border border-amber-200 rounded-lg p-3 overflow-x-auto">SAAS_DANGER_ZONE_ENABLED=true</pre>
                <p class="mt-2 text-xs text-amber-800/90">
                    En <code class="bg-white/80 px-1 rounded">local</code> y <code class="bg-white/80 px-1 rounded">testing</code> queda habilitada por defecto (config).
                </p>
            </div>
        @endif

        <div class="rounded-2xl border border-rose-200 bg-rose-50/80 px-5 py-5 space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-bold text-lg">!</div>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-rose-900 uppercase tracking-wide">Peligro extremo</h2>
                    <p class="text-sm text-rose-900/90 mt-1">
                        Estas acciones borran datos de clientes y operación. No hay deshacer. No sustituyen un backup ni una migración reversible.
                    </p>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-3 text-xs text-rose-900/90 sm:text-sm">
                <div class="rounded-xl bg-white/70 border border-rose-100 px-3 py-2">
                    <dt class="font-semibold text-rose-800">Compañías (tenants)</dt>
                    <dd class="text-lg font-bold text-rose-950">{{ $counts['tenants'] ?? 0 }}</dd>
                </div>
                <div class="rounded-xl bg-white/70 border border-rose-100 px-3 py-2">
                    <dt class="font-semibold text-rose-800">Cuerpos</dt>
                    <dd class="text-lg font-bold text-rose-950">{{ $counts['bodies'] ?? 0 }}</dd>
                </div>
                <div class="rounded-xl bg-white/70 border border-rose-100 px-3 py-2">
                    <dt class="font-semibold text-rose-800">Métricas operativas</dt>
                    <dd class="text-lg font-bold text-rose-950">{{ $counts['operational_metrics'] ?? 0 }}</dd>
                </div>
                <div class="rounded-xl bg-white/70 border border-rose-100 px-3 py-2">
                    <dt class="font-semibold text-rose-800">Alertas operativas</dt>
                    <dd class="text-lg font-bold text-rose-950">{{ $counts['operational_alerts'] ?? 0 }}</dd>
                </div>
            </dl>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <button type="button"
                        @if(! $enabled) disabled @endif
                        onclick="document.getElementById('modal-clear-tenants').classList.remove('hidden')"
                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl text-sm font-semibold border border-rose-300 bg-white text-rose-900 hover:bg-rose-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    Vaciar compañías
                </button>
                <button type="button"
    @if(! $enabled) disabled @endif
    onclick="document.getElementById('modal-reset-saas').classList.remove('hidden')"
    class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl text-sm font-semibold bg-rose-700 !text-white hover:!bg-rose-800 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm">
    Reset total SaaS
</button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600 space-y-3">
            <h3 class="font-semibold text-slate-900">Qué hace cada modo</h3>
            <div>
                <p class="font-medium text-slate-800">1) Vaciar compañías</p>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs sm:text-sm">
                    <li>Elimina todos los <strong>tenants</strong> (stancl: bases de datos de tenant vía evento de borrado).</li>
                    <li>Elimina <strong>dominios</strong>, <strong>tenant_billing</strong> y <strong>payments</strong> ligados (cascadas / limpieza central).</li>
                    <li>Elimina filas de <strong>auditoría central</strong> asociadas a un <code class="text-[11px] bg-slate-100 px-1 rounded">tenant_id</code>.</li>
                    <li>Elimina todos los <strong>cuerpos</strong> (<code class="text-[11px] bg-slate-100 px-1 rounded">bodies</code>).</li>
                    <li>Elimina archivos de <strong>backup</strong> en disco para cada tenant (<code class="text-[11px] bg-slate-100 px-1 rounded">storage/app/backups/tenant_*</code>).</li>
                </ul>
                <p class="mt-2 text-xs text-emerald-800 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2">
                    No borra: <strong>central_admins</strong>, <strong>planes</strong>, ni la capacidad de volver a entrar al panel.
                </p>
            </div>
            <div>
                <p class="font-medium text-slate-800">2) Reset total SaaS</p>
                <p class="text-xs sm:text-sm mt-1">Incluye todo lo anterior y además:</p>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs sm:text-sm">
                    <li>Vacía <strong>operational_metrics</strong> y <strong>operational_alerts</strong>.</li>
                    <li>Borra <strong>todo</strong> el historial de <strong>central_audit_logs</strong> (luego se registra solo el evento de finalización).</li>
                </ul>
                <p class="mt-2 text-xs text-rose-900 bg-rose-100 border border-rose-200 rounded-lg px-3 py-2">
                    La traza previa a la operación queda fuera de la tabla de auditoría; queda una línea en <code class="text-[11px] bg-white/70 px-1 rounded">storage/logs</code> (nivel critical) justo antes del borrado masivo de auditoría.
                </p>
            </div>
        </div>
    </div>

    {{-- Modal: vaciar compañías --}}
    <div id="modal-clear-tenants" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-rose-900">Vaciar compañías</h3>
                <button type="button" onclick="document.getElementById('modal-clear-tenants').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('central.danger-zone.clear-tenants') }}" class="px-6 py-5 space-y-4">
                @csrf
                <p class="text-sm text-slate-700">
                    Se eliminarán <strong>todos los tenants</strong> y datos centrales ligados (dominios, facturación, pagos, auditoría por tenant, cuerpos, backups en disco de tenants).
                </p>
                <label class="flex items-start gap-3 text-sm text-slate-800">
                    <input type="checkbox" name="confirm_irreversible" value="1" class="mt-1 rounded border-slate-300 text-rose-600 focus:ring-rose-500" required>
                    <span>Confirmo que esta acción es <strong>irreversible</strong> y que tengo un respaldo adecuado si lo necesito.</span>
                </label>
                <label class="flex items-start gap-3 text-sm text-slate-800">
                    <input type="checkbox" name="confirm_environment" value="1" class="mt-1 rounded border-slate-300 text-rose-600 focus:ring-rose-500" required>
                    <span>Confirmo que estoy en un <strong>entorno adecuado</strong> (p. ej. staging / VPS de pruebas), no en producción accidentalmente.</span>
                </label>
                <div>
                    <label for="written-clear" class="block text-xs font-semibold text-slate-600 mb-1">
                        Escribe exactamente (mayúsculas y sin espacios extra):
                    </label>
                    <input id="written-clear" name="written_confirmation" type="text" autocomplete="off"
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                           placeholder="{{ $phrase }}" required>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-clear-tenants').classList.add('hidden')"
                            class="w-full sm:w-auto px-4 py-2.5 text-sm font-medium text-slate-700 border border-slate-300 rounded-xl hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2.5 text-sm font-semibold bg-rose-700 text-white rounded-xl hover:bg-rose-800">
                        Confirmar vaciado
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: reset total --}}
    <div id="modal-reset-saas" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-rose-200 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-rose-100 flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-rose-950">Reset total SaaS</h3>
                <button type="button" onclick="document.getElementById('modal-reset-saas').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('central.danger-zone.reset-saas') }}" class="px-6 py-5 space-y-4">
                @csrf
                <p class="text-sm text-slate-800">
                    Ejecuta el vaciado de compañías <strong>y además</strong> borra métricas/alertas operativas y <strong>todo</strong> el historial de auditoría central.
                </p>
                <label class="flex items-start gap-3 text-sm text-slate-800">
                    <input type="checkbox" name="confirm_irreversible" value="1" class="mt-1 rounded border-slate-300 text-rose-600 focus:ring-rose-500" required>
                    <span>Confirmo que esta acción es <strong>irreversible</strong>.</span>
                </label>
                <label class="flex items-start gap-3 text-sm text-slate-800">
                    <input type="checkbox" name="confirm_environment" value="1" class="mt-1 rounded border-slate-300 text-rose-600 focus:ring-rose-500" required>
                    <span>Confirmo entorno adecuado (staging / pruebas).</span>
                </label>
                <label class="flex items-start gap-3 text-sm text-rose-900">
                    <input type="checkbox" name="confirm_audit_wipe" value="1" class="mt-1 rounded border-rose-400 text-rose-600 focus:ring-rose-500" required>
                    <span>Acepto el borrado <strong>completo</strong> de <code class="text-[11px] bg-rose-50 px-1 rounded">central_audit_logs</code> y entiendo que perderé el historial en base de datos.</span>
                </label>
                <div>
                    <label for="written-reset" class="block text-xs font-semibold text-slate-600 mb-1">
                        Escribe exactamente:
                    </label>
                    <input id="written-reset" name="written_confirmation" type="text" autocomplete="off"
                           class="w-full px-3 py-2.5 border border-rose-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-rose-600 focus:border-rose-600"
                           placeholder="{{ $phrase }}" required>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-reset-saas').classList.add('hidden')"
                            class="w-full sm:w-auto px-4 py-2.5 text-sm font-medium text-slate-700 border border-slate-300 rounded-xl hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2.5 text-sm font-semibold bg-rose-900 text-white rounded-xl hover:bg-black">
                        Confirmar reset total
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.addEventListener('click', function (e) {
            ['modal-clear-tenants', 'modal-reset-saas'].forEach(function (id) {
                var el = document.getElementById(id);
                if (!el || el.classList.contains('hidden')) return;
                if (e.target === el) el.classList.add('hidden');
            });
        });
    </script>
@endsection
