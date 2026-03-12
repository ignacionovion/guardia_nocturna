@extends('layouts.app')

@section('title', 'Marca Personalizada - GuardiAPP')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-8">
            <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-palette text-slate-500 text-2xl"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-slate-900 mb-3">
                Marca Personalizada
            </h1>
            
            <p class="text-slate-600 mb-6">
                Tu plan actual no incluye la funcionalidad de Marca Personalizada.
            </p>
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-amber-800 mb-2">
                    ¿Qué incluye Marca Personalizada?
                </h3>
                <ul class="text-sm text-amber-700 text-left space-y-1">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-xs"></i>
                        Logo personalizado de tu compañía
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-xs"></i>
                        Colores institucionales
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-xs"></i>
                        Nombre de la compañía en el header
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-xs"></i>
                        Favicon personalizado
                    </li>
                </ul>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-800 font-medium">
                    Volver al inicio
                </a>
                
                {{-- Only show upgrade button if there's a plan upgrade path --}}
                @if(auth()->user()->role === 'super_admin')
                    <span class="text-slate-400 text-sm">
                        Contacta al administrador del sistema para actualizar tu plan.
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
