@extends('layouts.modern')

@section('title', 'Marca Personalizada - GuardiAPP')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Marca Personalizada</h1>
        <p class="text-slate-600 dark:text-slate-400">Personaliza la apariencia de GuardiAPP con los colores y logo de tu compañía.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Logo Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Logo de la Compañía</h2>
            
            <div class="mb-4">
                @if($branding?->logo_path)
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-4 flex items-center justify-center mb-4">
                        <img src="{{ Storage::url($branding->logo_path) }}" alt="Logo actual" class="max-h-16">
                    </div>
                    <form action="{{ route('admin.branding.remove-logo') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-700 text-sm font-medium">
                            Eliminar logo
                        </button>
                    </form>
                @else
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-4 flex items-center justify-center mb-4">
                        <span class="text-slate-400 text-sm">No hay logo personalizado</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.branding.upload-logo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label for="logo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Subir nuevo logo
                        </label>
                        <input type="file" name="logo" id="logo" accept="image/*"
                            class="w-full text-sm text-slate-600 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Formatos: JPG, PNG. Máximo 2MB.</p>
                        @error('logo')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Subir Logo
                    </button>
                </div>
            </form>
        </div>

        {{-- Favicon Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Favicon</h2>
            
            <div class="mb-4">
                @if($branding?->favicon_path)
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-4 flex items-center justify-center mb-4">
                        <img src="{{ Storage::url($branding->favicon_path) }}" alt="Favicon actual" class="w-8 h-8">
                    </div>
                    <form action="{{ route('admin.branding.remove-favicon') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-700 text-sm font-medium">
                            Eliminar favicon
                        </button>
                    </form>
                @else
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-4 flex items-center justify-center mb-4">
                        <span class="text-slate-400 text-sm">No hay favicon personalizado</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.branding.upload-favicon') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label for="favicon" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Subir nuevo favicon
                        </label>
                        <input type="file" name="favicon" id="favicon" accept=".png,.ico"
                            class="w-full text-sm text-slate-600 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Formatos: PNG, ICO. Máximo 1MB.</p>
                        @error('favicon')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Subir Favicon
                    </button>
                </div>
            </form>
        </div>

        {{-- Colors & Name Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Colores y Nombre</h2>
            
            <form action="{{ route('admin.branding.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Company Name --}}
                    <div class="md:col-span-2">
                        <label for="nombre_empresa" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Nombre de la Compañía
                        </label>
                        <input type="text" name="nombre_empresa" id="nombre_empresa"
                            value="{{ old('nombre_empresa', $branding?->nombre_empresa ?? $defaults['nombre_empresa']) }}"
                            class="w-full rounded-lg border-slate-300 dark:border-slate-600 focus:border-amber-500 focus:ring-amber-500"
                            placeholder="GuardiAPP">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Este nombre aparecerá en el header del sistema.</p>
                        @error('nombre_empresa')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Primary Color --}}
                    <div>
                        <label for="color_primario" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Color Primario
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color_primario" id="color_primario"
                                value="{{ old('color_primario', $branding?->color_primario ?? $defaults['color_primario']) }}"
                                class="h-10 w-20 rounded border-slate-300 dark:border-slate-600 cursor-pointer">
                            <input type="text" 
                                value="{{ old('color_primario', $branding?->color_primario ?? $defaults['color_primario']) }}"
                                class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 text-sm font-mono uppercase"
                                readonly>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Usado en botones y elementos principales.</p>
                        @error('color_primario')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Secondary Color --}}
                    <div>
                        <label for="color_secundario" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Color Secundario
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color_secundario" id="color_secundario"
                                value="{{ old('color_secundario', $branding?->color_secundario ?? $defaults['color_secundario']) }}"
                                class="h-10 w-20 rounded border-slate-300 dark:border-slate-600 cursor-pointer">
                            <input type="text" 
                                value="{{ old('color_secundario', $branding?->color_secundario ?? $defaults['color_secundario']) }}"
                                class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 text-sm font-mono uppercase"
                                readonly>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Usado en elementos de contraste.</p>
                        @error('color_secundario')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sidebar Color --}}
                    <div>
                        <label for="color_sidebar" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Color del Sidebar
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color_sidebar" id="color_sidebar"
                                value="{{ old('color_sidebar', $branding?->color_sidebar ?? $defaults['color_sidebar']) }}"
                                class="h-10 w-20 rounded border-slate-300 dark:border-slate-600 cursor-pointer">
                            <input type="text" 
                                value="{{ old('color_sidebar', $branding?->color_sidebar ?? $defaults['color_sidebar']) }}"
                                class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 text-sm font-mono uppercase"
                                readonly>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Color de fondo del menú lateral.</p>
                        @error('color_sidebar')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Preview --}}
                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-4 mb-6">
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
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                        Guardar Cambios
                    </button>
                    <a href="{{ route('dashboard') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:text-white">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
