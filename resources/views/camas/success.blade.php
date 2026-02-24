@extends('layouts.public')

@section('title', '¡Asignación Exitosa! - Cama ' . $bed->number)

@section('content')
<div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo/Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500/10 rounded-full border-2 border-green-500/30 mb-4 animate-pulse">
                <i class="fas fa-check text-3xl text-green-400"></i>
            </div>
            <h1 class="text-2xl font-black text-green-400 uppercase tracking-wider">¡Éxito!</h1>
            <p class="text-slate-500 text-sm mt-2">Cama asignada correctamente</p>
        </div>

        {{-- Card --}}
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-2xl overflow-hidden">
            {{-- Header de Cama --}}
            <div class="bg-green-500/10 border-b border-green-500/20 px-6 py-4">
                <div class="text-center">
                    <p class="text-xs text-green-400/70 uppercase tracking-wider font-bold mb-1">Cama Asignada</p>
                    <p class="text-3xl font-black text-green-400">#{{ $bed->number }}</p>
                </div>
            </div>

            {{-- Info del Bombero --}}
            @if($bombero)
            <div class="px-6 py-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user text-cyan-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Asignada a</p>
                        <p class="text-sm font-bold text-slate-100">{{ $bombero->nombres }} {{ $bombero->apellido_paterno }}</p>
                        <p class="text-xs text-slate-500">RUT: {{ $bombero->rut }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Detalles --}}
            <div class="p-6">
                <div class="bg-slate-800/50 rounded-xl p-4 mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <i class="fas fa-clock text-slate-500"></i>
                        <div>
                            <p class="text-xs text-slate-500">Hora de asignación</p>
                            <p class="text-sm font-bold text-slate-100">{{ now()->format('H:i') }} hrs</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-calendar text-slate-500"></i>
                        <div>
                            <p class="text-xs text-slate-500">Fecha</p>
                            <p class="text-sm font-bold text-slate-100">{{ now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('dashboard') }}" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg shadow-cyan-500/20 flex items-center justify-center gap-2">
                        <i class="fas fa-home"></i>
                        Ir al Dashboard
                    </a>

                    <a href="{{ route('camas') }}" class="w-full bg-slate-700 hover:bg-slate-600 text-slate-100 font-bold py-3 px-6 rounded-xl transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-bed"></i>
                        Ver todas las camas
                    </a>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="mt-6 text-center space-y-2">
            <p class="text-xs text-slate-600">
                <i class="fas fa-shield-alt mr-1"></i>
                Sistema de Guardia Nocturna
            </p>
            <p class="text-xs text-slate-700">
                Registro guardado para auditoría
            </p>
        </div>
    </div>
</div>
@endsection
