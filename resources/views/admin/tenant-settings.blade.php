@extends('layouts.modern')

@section('title', 'Configuración de la Compañía')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Configuración de la Compañía</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Personaliza la información y apariencia de tu compañía</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm mb-6 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
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

    <form method="POST" action="{{ route('admin.tenant-settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Información General --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Información General</h2>

            <div class="space-y-5">
                <div>
                    <label for="tenant_display_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre para mostrar</label>
                    <input type="text" id="tenant_display_name" name="tenant_display_name"
                           value="{{ old('tenant_display_name', $settings['tenant_display_name'] ?? $tenant->nombre) }}"
                           placeholder="{{ $tenant->nombre }}"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                    <p class="text-xs text-slate-400 mt-1">Nombre que se mostrará en la cabecera y reportes. Si está vacío se usa el nombre del tenant.</p>
                </div>

                <div>
                    <label for="tenant_timezone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Zona Horaria</label>
                    <select id="tenant_timezone" name="tenant_timezone"
                            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white">
                        <option value="">— Usar default del servidor —</option>
                        @foreach(['America/Santiago', 'America/Argentina/Buenos_Aires', 'America/Bogota', 'America/Lima', 'America/Mexico_City', 'America/New_York', 'UTC'] as $tz)
                            <option value="{{ $tz }}" {{ ($settings['tenant_timezone'] ?? '') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Logo --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Logo</h2>

            <div class="flex items-start space-x-6">
                <div class="flex-shrink-0">
                    @if($settings['tenant_logo'])
                        <img src="{{ asset('storage/' . $settings['tenant_logo']) }}" alt="Logo"
                             class="w-24 h-24 object-contain rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2">
                    @else
                        <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <input type="file" id="tenant_logo" name="tenant_logo" accept="image/*"
                           class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 dark:bg-slate-800 file:text-slate-700 dark:text-slate-300 hover:file:bg-slate-200">
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

        {{-- Email --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Correo Electrónico</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="tenant_email_from" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Correo Remitente</label>
                    <input type="email" id="tenant_email_from" name="tenant_email_from"
                           value="{{ old('tenant_email_from', $settings['tenant_email_from'] ?? '') }}"
                           placeholder="noreply@micompania.cl"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label for="tenant_email_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre Remitente</label>
                    <input type="text" id="tenant_email_name" name="tenant_email_name"
                           value="{{ old('tenant_email_name', $settings['tenant_email_name'] ?? '') }}"
                           placeholder="Tercera Compañía Temuco"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Estos datos se usarán como remitente en los correos automáticos del sistema.</p>
        </div>

        {{-- Branding --}}
        @if(feature('custom_branding'))
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Personalización Visual</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="tenant_primary_color" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Color Primario</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" id="tenant_primary_color" name="tenant_primary_color"
                               value="{{ old('tenant_primary_color', $settings['tenant_primary_color'] ?? '#f59e0b') }}"
                               class="w-10 h-10 rounded-lg border border-slate-300 dark:border-slate-600 cursor-pointer">
                        <input type="text" value="{{ old('tenant_primary_color', $settings['tenant_primary_color'] ?? '#f59e0b') }}"
                               class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl text-sm bg-slate-50 dark:bg-slate-800" readonly
                               id="primary_color_text">
                    </div>
                </div>
                <div>
                    <label for="tenant_secondary_color" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Color Secundario</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" id="tenant_secondary_color" name="tenant_secondary_color"
                               value="{{ old('tenant_secondary_color', $settings['tenant_secondary_color'] ?? '#1e293b') }}"
                               class="w-10 h-10 rounded-lg border border-slate-300 dark:border-slate-600 cursor-pointer">
                        <input type="text" value="{{ old('tenant_secondary_color', $settings['tenant_secondary_color'] ?? '#1e293b') }}"
                               class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl text-sm bg-slate-50 dark:bg-slate-800" readonly
                               id="secondary_color_text">
                    </div>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">Estos colores se aplicarán a la interfaz del sistema para tu compañía.</p>
        </div>
        @else
        <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6 opacity-60">
            <h2 class="text-lg font-semibold text-slate-500 dark:text-slate-400 mb-2">Personalización Visual</h2>
            <p class="text-sm text-slate-400">Esta funcionalidad está disponible en el plan <strong>Enterprise</strong>. Contacta al administrador para actualizar tu plan.</p>
        </div>
        @endif

        {{-- Plan Info --}}
        <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-3">Tu Plan</h2>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-slate-400 text-xs uppercase font-medium">Plan Actual</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ ucfirst($tenant->plan) }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-medium">Estado</p>
                    <p class="mt-1"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tenant->estadoBadgeClass() }}">{{ $tenant->estadoLabel() }}</span></p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-medium">Vencimiento</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ $tenant->fecha_vencimiento ? $tenant->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento' }}</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition">
                Guardar Configuración
            </button>
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
