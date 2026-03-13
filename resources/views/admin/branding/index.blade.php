@extends('layouts.modern')

@section('title', 'Marca Personalizada - GuardiAPP')

@section('content')
<div class="w-full">
    <x-ui.page-header title="Marca Personalizada" subtitle="Personaliza la apariencia de GuardiAPP con los colores y logo de tu compañía" icon="fas fa-paint-brush" iconVariant="violet" />

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" icon="fas fa-exclamation-circle" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Logo Section --}}
        <x-ui.card>
            <h2 class="text-title-sm mb-4">Logo de la Compañía</h2>
            
            <div class="mb-4">
                @if($branding?->logo_path)
                    <div class="card !bg-slate-50 dark:!bg-slate-800 mb-4 flex items-center justify-center">
                        <img src="{{ Storage::url($branding->logo_path) }}" alt="Logo actual" class="max-h-16">
                    </div>
                    <form action="{{ route('admin.branding.remove-logo') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="ghost" size="sm" icon="fas fa-trash">
                            Eliminar logo
                        </x-ui.button>
                    </form>
                @else
                    <div class="card !bg-slate-50 dark:!bg-slate-800 mb-4 flex items-center justify-center">
                        <span class="text-slate-400 text-sm">No hay logo personalizado</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.branding.upload-logo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label for="logo" class="form-label">Subir nuevo logo</label>
                        <input type="file" name="logo" id="logo" accept="image/*"
                            class="form-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        <p class="text-caption mt-1">Formatos: JPG, PNG. Máximo 2MB.</p>
                        @error('logo')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-ui.button type="submit" variant="primary" size="md" class="w-full" icon="fas fa-upload">
                        Subir Logo
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- Favicon Section --}}
        <x-ui.card>
            <h2 class="text-title-sm mb-4">Favicon</h2>
            
            <div class="mb-4">
                @if($branding?->favicon_path)
                    <div class="card !bg-slate-50 dark:!bg-slate-800 mb-4 flex items-center justify-center">
                        <img src="{{ Storage::url($branding->favicon_path) }}" alt="Favicon actual" class="w-8 h-8">
                    </div>
                    <form action="{{ route('admin.branding.remove-favicon') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="ghost" size="sm" icon="fas fa-trash">
                            Eliminar favicon
                        </x-ui.button>
                    </form>
                @else
                    <div class="card !bg-slate-50 dark:!bg-slate-800 mb-4 flex items-center justify-center">
                        <span class="text-slate-400 text-sm">No hay favicon personalizado</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.branding.upload-favicon') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label for="favicon" class="form-label">Subir nuevo favicon</label>
                        <input type="file" name="favicon" id="favicon" accept=".png,.ico"
                            class="form-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        <p class="text-caption mt-1">Formatos: PNG, ICO. Máximo 1MB.</p>
                        @error('favicon')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-ui.button type="submit" variant="primary" size="md" class="w-full" icon="fas fa-upload">
                        Subir Favicon
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- Colors & Name Section --}}
        <x-ui.card class="lg:col-span-2">
            <h2 class="text-title-sm mb-4">Colores y Nombre</h2>
            
            <form action="{{ route('admin.branding.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Company Name --}}
                    <div class="md:col-span-2">
                        <label for="nombre_empresa" class="form-label">Nombre de la Compañía</label>
                        <input type="text" name="nombre_empresa" id="nombre_empresa"
                            value="{{ old('nombre_empresa', $branding?->nombre_empresa ?? $defaults['nombre_empresa']) }}"
                            class="form-input"
                            placeholder="GuardiAPP">
                        <p class="text-caption mt-1">Este nombre aparecerá en el header del sistema.</p>
                        @error('nombre_empresa')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Primary Color --}}
                    <div>
                        <label for="color_primario" class="form-label">Color Primario</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color_primario" id="color_primario"
                                value="{{ old('color_primario', $branding?->color_primario ?? $defaults['color_primario']) }}"
                                class="h-10 w-20 rounded cursor-pointer">
                            <input type="text" 
                                value="{{ old('color_primario', $branding?->color_primario ?? $defaults['color_primario']) }}"
                                class="form-input flex-1 font-mono uppercase text-sm" readonly>
                        </div>
                        <p class="text-caption mt-1">Usado en botones y elementos principales.</p>
                        @error('color_primario')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Secondary Color --}}
                    <div>
                        <label for="color_secundario" class="form-label">Color Secundario</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color_secundario" id="color_secundario"
                                value="{{ old('color_secundario', $branding?->color_secundario ?? $defaults['color_secundario']) }}"
                                class="h-10 w-20 rounded cursor-pointer">
                            <input type="text" 
                                value="{{ old('color_secundario', $branding?->color_secundario ?? $defaults['color_secundario']) }}"
                                class="form-input flex-1 font-mono uppercase text-sm" readonly>
                        </div>
                        <p class="text-caption mt-1">Usado en elementos de contraste.</p>
                        @error('color_secundario')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sidebar Color --}}
                    <div>
                        <label for="color_sidebar" class="form-label">Color del Sidebar</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color_sidebar" id="color_sidebar"
                                value="{{ old('color_sidebar', $branding?->color_sidebar ?? $defaults['color_sidebar']) }}"
                                class="h-10 w-20 rounded cursor-pointer">
                            <input type="text" 
                                value="{{ old('color_sidebar', $branding?->color_sidebar ?? $defaults['color_sidebar']) }}"
                                class="form-input flex-1 font-mono uppercase text-sm" readonly>
                        </div>
                        <p class="text-caption mt-1">Color de fondo del menú lateral.</p>
                        @error('color_sidebar')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Preview --}}
                <div class="card !bg-slate-50 dark:!bg-slate-800 mb-6">
                    <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Vista previa</h3>
                    <div class="flex items-center gap-4 p-3 rounded-lg"
                         style="background-color: {{ old('color_sidebar', $branding?->color_sidebar ?? $defaults['color_sidebar']) }}">
                        @if($branding?->logo_path)
                            <img src="{{ Storage::url($branding->logo_path) }}" alt="Logo" class="h-8 max-w-[120px]">
                        @else
                            <span class="text-white font-bold text-lg">{{ old('nombre_empresa', $branding?->nombre_empresa ?? $defaults['nombre_empresa']) }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-ui.button type="submit" variant="warning" size="md" icon="fas fa-save">
                        Guardar Cambios
                    </x-ui.button>
                    <x-ui.button variant="ghost" size="md" href="{{ route('dashboard') }}">
                        Cancelar
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>
@endsection
