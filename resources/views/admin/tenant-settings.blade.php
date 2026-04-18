@extends('layouts.modern')

@section('title', 'Configuración de la Compañía')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-ui.page-header title="Configuración de la Compañía" subtitle="Personaliza la información y apariencia de tu compañía" icon="fas fa-gear" iconVariant="slate" />

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if($errors->any())
        <x-ui.alert type="danger" icon="fas fa-exclamation-triangle" class="mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.tenant-settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Información General --}}
        <x-ui.card class="mb-6">
            <div class="card-padding">
                <h2 class="text-title-md mb-4">Información General</h2>

                <div class="space-y-5">
                    <div>
                        <label for="tenant_display_name" class="form-label">Nombre para mostrar</label>
                        <input type="text" id="tenant_display_name" name="tenant_display_name"
                               value="{{ old('tenant_display_name', $settings['tenant_display_name'] ?? $tenant->nombre) }}"
                               placeholder="{{ $tenant->nombre }}"
                               class="form-input">
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Nombre que se mostrará en la cabecera y reportes. Si está vacío se usa el nombre del tenant.</p>
                    </div>

                    <div>
                        <label for="tenant_timezone" class="form-label">Zona Horaria</label>
                        <select id="tenant_timezone" name="tenant_timezone" class="form-input">
                            <option value="">— Usar default del servidor —</option>
                            @foreach(['America/Santiago', 'America/Argentina/Buenos_Aires', 'America/Bogota', 'America/Lima', 'America/Mexico_City', 'America/New_York', 'UTC'] as $tz)
                                <option value="{{ $tz }}" {{ ($settings['tenant_timezone'] ?? '') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Logo --}}
        <x-ui.card class="mb-6">
            <div class="card-padding">
            <h2 class="text-title-md mb-4">Logo</h2>

            <div class="flex items-start space-x-6">
                <div class="flex-shrink-0">
                    @if($settings['tenant_logo'])
                        <img src="{{ asset('storage/' . $settings['tenant_logo']) }}" alt="Logo"
                             class="w-24 h-24 object-contain rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2">
                    @else
                        <div class="w-24 h-24 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <input type="file" id="tenant_logo" name="tenant_logo" accept="image/*"
                           class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-white dark:bg-slate-800 file:text-slate-700 dark:text-slate-300 hover:file:bg-slate-200">
                    <p class="text-xs text-slate-400 mt-2">PNG, JPG o SVG. Máximo 2 MB. Se recomienda un formato cuadrado.</p>
                    @if($settings['tenant_logo'])
                        <label class="flex items-center space-x-2 mt-3">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 dark:border-slate-600 text-red-500 focus:ring-red-500">
                            <span class="text-xs text-red-600">Eliminar logo actual</span>
                        </label>
                    @endif
                </div>
            </div>
            </div>
        </x-ui.card>

        {{-- Email --}}
        <x-ui.card class="mb-6">
            <div class="card-padding">
            <h2 class="text-title-md mb-4">Correo Electrónico</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tenant_email_from" class="form-label">Correo Remitente</label>
                    <input type="email" id="tenant_email_from" name="tenant_email_from"
                           value="{{ old('tenant_email_from', $settings['tenant_email_from'] ?? '') }}"
                           placeholder="noreply@micompania.cl"
                           class="form-input">
                </div>
                <div>
                    <label for="tenant_email_name" class="form-label">Nombre Remitente</label>
                    <input type="text" id="tenant_email_name" name="tenant_email_name"
                           value="{{ old('tenant_email_name', $settings['tenant_email_name'] ?? '') }}"
                           placeholder="Tercera Compañía Temuco"
                           class="form-input">
                </div>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Estos datos se usarán como remitente en los correos automáticos del sistema.</p>
            </div>
        </x-ui.card>

        {{-- Branding --}}
        @if(feature('custom_branding'))
        <x-ui.card class="mb-6">
            <div class="card-padding">
            <h2 class="text-title-md mb-4">Personalización Visual</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tenant_primary_color" class="form-label">Color Primario</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" id="tenant_primary_color" name="tenant_primary_color"
                               value="{{ old('tenant_primary_color', $settings['tenant_primary_color'] ?? '#f59e0b') }}"
                               class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer">
                        <input type="text" value="{{ old('tenant_primary_color', $settings['tenant_primary_color'] ?? '#f59e0b') }}"
                               class="form-input bg-white dark:bg-slate-800" readonly
                               id="primary_color_text">
                    </div>
                </div>
                <div>
                    <label for="tenant_secondary_color" class="form-label">Color Secundario</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" id="tenant_secondary_color" name="tenant_secondary_color"
                               value="{{ old('tenant_secondary_color', $settings['tenant_secondary_color'] ?? '#1e293b') }}"
                               class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer">
                        <input type="text" value="{{ old('tenant_secondary_color', $settings['tenant_secondary_color'] ?? '#1e293b') }}"
                               class="form-input bg-white dark:bg-slate-800" readonly
                               id="secondary_color_text">
                    </div>
                </div>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Estos colores se aplicarán a la interfaz del sistema para tu compañía.</p>
            </div>
        </x-ui.card>
        @else
        <div class="card-base p-6 mb-6 opacity-60">
            <h2 class="text-lg font-semibold text-slate-500 dark:text-slate-400 mb-2">Personalización Visual</h2>
            <p class="text-sm text-slate-400 dark:text-slate-500">Esta funcionalidad no está disponible en tu plan actual. Contacta al administrador para actualizar tu plan.</p>
        </div>
        @endif

        {{-- Plan Info --}}
        <div class="card-base bg-white dark:bg-slate-800/50 p-6 mb-6">
            <h2 class="text-title-md mb-3">Tu Plan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-label">Plan Actual</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-1">{{ tenant_plan_label() }}</p>
                </div>
                <div>
                    <p class="text-label">Estado</p>
                    <p class="mt-1"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tenant->estadoBadgeClass() }}">{{ $tenant->estadoLabel() }}</span></p>
                </div>
                <div>
                    <p class="text-label">Vencimiento</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-1">{{ $tenant->fecha_vencimiento ? $tenant->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                Guardar Configuración
            </x-ui.button>
        </div>
    </form>
</div>

<script>
    document.getElementById('tenant_primary_color')?.addEventListener('input', function(e) {
        document.getElementById('primary_color_text').value = e.target.value;
    });
    document.getElementById('tenant_secondary_color')?.addEventListener('input', function(e) {
        document.getElementById('secondary_color_text').value = e.target.value;
    });
</script>
@endsection
