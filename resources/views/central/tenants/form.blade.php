@extends('central.layouts.app')

@section('title', $tenant ? 'Editar Compañía' : 'Nueva Compañía')

@section('content')
    @php
        $forceTrialOnCreate = (bool) config('billing.enabled_trial_on_create');
        $defaultTrialDays = max(1, min(365, (int) config('billing.default_trial_days', 14)));
    @endphp
    <div id="billing-onboarding-config" class="hidden"
         data-default-trial-days="{{ $defaultTrialDays }}"
         data-force-trial="{{ $forceTrialOnCreate ? '1' : '0' }}"></div>
    <div class="mb-8">
        <a href="{{ route('central.tenants.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a compañías</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">{{ $tenant ? 'Editar Compañía' : 'Nueva Compañía' }}</h1>
        @if($tenant)
            <p class="text-slate-500 text-sm mt-1">Editando: {{ $tenant->nombre }} ({{ $tenant->id }})</p>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8 max-w-2xl">
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                <p class="font-medium">{{ session('error') }}</p>
                @if(session('steps'))
                    <div class="mt-2 pt-2 border-t border-red-200">
                        <p class="text-xs font-medium text-red-600 mb-1">Pasos completados antes del error:</p>
                        <ul class="text-xs space-y-0.5">
                            @foreach(session('steps') as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $tenant ? route('central.tenants.update', $tenant->id) : route('central.tenants.store') }}">
            @csrf
            @if($tenant) @method('PUT') @endif

            <div class="space-y-6">
                @unless($tenant)
                <div>
                    <label for="id" class="block text-sm font-medium text-slate-700 mb-1.5">Slug (ID)</label>
                    <div class="relative">
                        <input type="text" id="id" name="id" value="{{ old('id') }}" required
                               pattern="[a-z0-9\-]+" placeholder="tercera-temuco"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
                               autocomplete="off">
                        <div id="slug-status" class="absolute right-3 top-2.5 text-xs font-medium"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Solo minúsculas, números y guiones. Será el subdominio y nombre de la base de datos.</p>
                    @error('id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endunless

                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre de la Compañía</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $tenant?->nombre) }}" required
                           placeholder="Tercera Compañía de Bomberos Temuco"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="numero" class="block text-sm font-medium text-slate-700 mb-1.5">Número</label>
                        <input type="number" id="numero" name="numero" value="{{ old('numero', $tenant?->numero) }}" min="1"
                               placeholder="3"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                    </div>
                    <div>
                        <label for="plan_id" class="block text-sm font-medium text-slate-700 mb-1.5">Plan</label>
                        <select id="plan_id" name="plan_id" required
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white billing-input">
                            @if(isset($plans) && $plans->count() > 0)
                                @foreach($plans as $planOption)
                                    <option value="{{ $planOption->id }}"
                                            data-precio-mensual="{{ $planOption->precio_mensual }}"
                                            data-precio-anual="{{ $planOption->precio_anual ?? $planOption->precio_mensual * 12 }}"
                                            {{ (string) old('plan_id', $tenant?->plan_id) === (string) $planOption->id ? 'selected' : '' }}>
                                        {{ $planOption->nombre }}
                                    </option>
                                @endforeach
                            @else
                                <option value="">No hay planes activos disponibles</option>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Sección de Facturación (alta: sin fecha manual; vigencia desde backend / pagos) --}}
                @unless($tenant)
                <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-credit-card text-slate-500"></i>
                        Configuración de Facturación
                    </h3>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        La vigencia inicial se calcula automáticamente según el ciclo, la política de trial del SaaS y la facturación;
                        el calendario comercial lo actualizan los pagos registrados (no se ingresa vencimiento manual en el alta).
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="billing_cycle" class="block text-sm font-medium text-slate-700 mb-1.5">Ciclo de Facturación</label>
                            <select id="billing_cycle" name="billing_cycle" required
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white billing-input">
                                <option value="monthly" {{ old('billing_cycle', 'monthly') === 'monthly' ? 'selected' : '' }}>Mensual (30 días)</option>
                                <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Anual (365 días)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Monto Estimado</label>
                            <div class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-900" id="monto-preview">
                                Calculando...
                            </div>
                        </div>
                    </div>

                    @if($forceTrialOnCreate)
                        <div class="rounded-lg border border-blue-200 bg-blue-50/80 px-3 py-2.5 text-xs text-blue-900">
                            <span class="font-semibold">Política central:</span> las nuevas compañías inician en <strong>trial</strong>
                            de <strong>{{ $defaultTrialDays }}</strong> días (<code class="text-[11px]">BILLING_ENABLED_TRIAL_ON_CREATE</code> /
                            <code class="text-[11px]">BILLING_DEFAULT_TRIAL_DAYS</code>).
                        </div>
                    @else
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="tiene_trial" name="tiene_trial" value="1" {{ old('tiene_trial') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-amber-500 focus:ring-amber-500 billing-input">
                            <label for="tiene_trial" class="text-sm text-slate-700">
                                <span class="font-medium">Activar período de prueba (Trial)</span>
                                <span class="block text-xs text-slate-500 mt-0.5">Duración fija: {{ $defaultTrialDays }} días (configuración central).</span>
                            </label>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-200">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Estado inicial (referencia)</label>
                            <div id="estado-preview" class="text-sm font-medium text-slate-700">
                                Pendiente
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Vigencia (referencia)</label>
                            <div id="vencimiento-preview" class="text-sm font-medium text-slate-700">
                                Calculando...
                            </div>
                        </div>
                    </div>
                </div>
                @endunless

                <div>
                    <label for="body_id" class="block text-sm font-medium text-slate-700 mb-1.5">Cuerpo de Bomberos</label>
                    <select id="body_id" name="body_id"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white">
                        <option value="">— Sin cuerpo —</option>
                        @foreach($bodies as $body)
                            <option value="{{ $body->id }}" {{ old('body_id', $tenant?->body_id) == $body->id ? 'selected' : '' }}>
                                {{ $body->nombre }} {{ $body->ciudad ? "({$body->ciudad})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($tenant)
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Vigencia y vencimiento</span>
                    provienen de la facturación central (<code class="text-[11px]">tenant_billing</code>) y de los pagos;
                    para ajustar períodos usá <a href="{{ route('central.billing.index') }}" class="text-amber-700 font-medium hover:underline">Facturación</a>
                    o <a href="{{ route('central.payments.index') }}" class="text-amber-700 font-medium hover:underline">Pagos</a>.
                    Fecha mostrada hoy en la ficha: <strong class="text-slate-800">{{ $tenant->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</strong>.
                </div>
                @endif

                @if($tenant)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="estado" class="block text-sm font-medium text-slate-700 mb-1.5">Estado</label>
                        <select id="estado" name="estado" required
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white">
                            <option value="trial" {{ old('estado', $tenant->estado) === 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="activo" {{ old('estado', $tenant->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="suspendido" {{ old('estado', $tenant->estado) === 'suspendido' ? 'selected' : '' }}>Suspendido</option>
                            <option value="vencido" {{ old('estado', $tenant->estado) === 'vencido' ? 'selected' : '' }}>Vencido</option>
                            <option value="cancelado" {{ old('estado', $tenant->estado) === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label for="grace_days" class="block text-sm font-medium text-slate-700 mb-1.5">Días de Gracia</label>
                        <input type="number" id="grace_days" name="grace_days" min="0" max="30"
                               value="{{ old('grace_days', $tenant->grace_days ?? 5) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                        <p class="text-xs text-slate-400 mt-1">Días permitidos después del vencimiento antes de suspender.</p>
                    </div>
                </div>
                @endif

                @unless($tenant)
                <div class="flex items-center space-x-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <input type="checkbox" id="seed" name="seed" value="1" checked
                           class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <label for="seed" class="text-sm text-blue-800">
                        <span class="font-medium">Poblar con datos iniciales</span>
                        <span class="block text-xs text-blue-600 mt-0.5">Crea usuarios admin, camas, tareas de limpieza y configuración del sistema</span>
                    </label>
                </div>
                @endunless
            </div>

            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-slate-200">
                <a href="{{ route('central.tenants.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-white transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition">
                    {{ $tenant ? 'Guardar Cambios' : 'Crear Compañía' }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slugInput = document.getElementById('id');
    const statusDiv = document.getElementById('slug-status');
    let debounceTimer;

    if (slugInput) {
        slugInput.addEventListener('input', function() {
            const slug = this.value.trim().toLowerCase();

            // Clear previous status
            statusDiv.textContent = '';
            statusDiv.className = 'absolute right-3 top-2.5 text-xs font-medium';
            slugInput.classList.remove('border-emerald-500', 'border-red-500');

            if (!slug) return;

            // Validate format
            if (!/^[a-z0-9\-]+$/.test(slug)) {
                statusDiv.textContent = 'Formato inválido';
                statusDiv.classList.add('text-amber-600');
                slugInput.classList.add('border-amber-500');
                return;
            }

            // Debounce AJAX call
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                statusDiv.textContent = 'Verificando...';
                statusDiv.classList.add('text-slate-400');

                fetch(`{{ route('central.check-slug') }}?slug=${encodeURIComponent(slug)}`)
                    .then(response => response.json())
                    .then(data => {
                        statusDiv.textContent = data.message;
                        if (data.available) {
                            statusDiv.classList.add('text-emerald-600');
                            slugInput.classList.add('border-emerald-500');
                        } else {
                            statusDiv.classList.add('text-red-600');
                            slugInput.classList.add('border-red-500');
                        }
                    })
                    .catch(() => {
                        statusDiv.textContent = '';
                    });
            }, 300);
        });
    }

    // Billing calculation logic
    const planSelect = document.getElementById('plan_id');
    const billingCycleSelect = document.getElementById('billing_cycle');
    const billingConfigEl = document.getElementById('billing-onboarding-config');
    const defaultTrialDays = billingConfigEl
        ? Math.max(1, Math.min(365, parseInt(billingConfigEl.dataset.defaultTrialDays || '14', 10) || 14))
        : 14;
    const forceTrialOnCreate = billingConfigEl && billingConfigEl.dataset.forceTrial === '1';

    const tieneTrialCheckbox = document.getElementById('tiene_trial');
    const montoPreview = document.getElementById('monto-preview');
    const estadoPreview = document.getElementById('estado-preview');
    const vencimientoPreview = document.getElementById('vencimiento-preview');

    function formatCurrency(amount) {
        return '$' + amount.toLocaleString('es-CL');
    }

    function formatDate(date) {
        return date.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function calcularFacturacion() {
        if (!planSelect || !billingCycleSelect) return;

        const selectedPlan = planSelect.options[planSelect.selectedIndex];
        const billingCycle = billingCycleSelect.value;
        const tieneTrial = forceTrialOnCreate || (tieneTrialCheckbox && tieneTrialCheckbox.checked);
        const trialDays = defaultTrialDays;

        const precioMensual = parseInt(selectedPlan.dataset.precioMensual) || 0;
        const precioAnual = parseInt(selectedPlan.dataset.precioAnual) || 0;

        // Calcular monto
        const monto = billingCycle === 'yearly' ? precioAnual : precioMensual;
        montoPreview.textContent = tieneTrial ? formatCurrency(0) + ' (Trial)' : formatCurrency(monto);

        // Calcular estado
        const estado = tieneTrial ? 'Trial' : 'Pendiente';
        estadoPreview.textContent = estado;
        estadoPreview.className = 'text-sm font-medium ' + (tieneTrial ? 'text-blue-600' : 'text-yellow-600');

        // Calcular vencimiento
        const hoy = new Date();
        let vencimiento;
        if (tieneTrial) {
            vencimiento = new Date(hoy);
            vencimiento.setDate(vencimiento.getDate() + trialDays);
            vencimientoPreview.textContent = 'Finaliza trial: ' + formatDate(vencimiento);
        } else {
            vencimiento = new Date(hoy);
            if (billingCycle === 'yearly') {
                vencimiento.setDate(vencimiento.getDate() + 365);
            } else {
                vencimiento.setDate(vencimiento.getDate() + 30);
            }
            vencimientoPreview.textContent = formatDate(vencimiento);
        }
    }

    if (tieneTrialCheckbox) {
        tieneTrialCheckbox.addEventListener('change', function() {
            calcularFacturacion();
        });
    }

    if (planSelect) {
        planSelect.addEventListener('change', calcularFacturacion);
    }

    if (billingCycleSelect) {
        billingCycleSelect.addEventListener('change', calcularFacturacion);
    }

    // Initial calculation
    calcularFacturacion();
});
</script>
@endpush
