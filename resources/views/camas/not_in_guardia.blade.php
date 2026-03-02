@extends('layouts.public')

@section('title', 'No estás en guardia - Cama ' . $bed->number)

@section('content')
<div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo/Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 rounded-2xl border border-slate-800 mb-4">
                <i class="fas fa-bed text-2xl text-cyan-400"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-100 uppercase tracking-wider">Guardia Nocturna</h1>
            <p class="text-slate-500 text-sm mt-2">Sistema de Asignación de Camas</p>
        </div>

        {{-- Card --}}
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-2xl overflow-hidden">
            {{-- Alerta --}}
            <div class="bg-amber-500/10 border-b border-amber-500/20 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-triangle-exclamation text-2xl text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-400 uppercase tracking-wider">Atención</p>
                        <p class="text-xs text-amber-400/70">No estás en la guardia activa</p>
                    </div>
                </div>
            </div>

            {{-- Contenido --}}
            <div class="p-6 text-center">
                @if($bombero)
                    <p class="text-slate-400 mb-2">Hola <span class="text-slate-100 font-bold">{{ $bombero->nombres }} {{ $bombero->apellido_paterno }}</span>,</p>
                @endif
                
                <p class="text-slate-400 mb-6">
                    No estás registrado en la guardia de hoy, por lo que no puedes asignarte una cama.
                </p>

                <div class="bg-slate-800/50 rounded-xl p-4 mb-6">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-2">¿Qué puedes hacer?</p>
                    <ul class="text-sm text-slate-400 text-left space-y-2">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-cyan-400 mt-0.5"></i>
                            <span>Verifica que estes ingresado en el sistema</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-cyan-400 mt-0.5"></i>
                            <span>Contacta a tu Jefe de Guardia</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-cyan-400 mt-0.5"></i>
                            <span>Contacta a tu Cápitan</span>
                        </li>
                    </ul>
                </div>

                <a href="{{ route('dashboard') }}" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg shadow-cyan-500/20 flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i>
                    Ir al Dashboard
                </a>

                <a href="{{ route('camas.scan.form', ['bedId' => $bed->id, 'reset' => 1]) }}" class="block text-center mt-4 text-sm text-slate-500 hover:text-slate-300 transition-colors">
                    <i class="fas fa-rotate-left mr-1"></i> Intentar con otro RUT
                </a>
            </div>
        </div>

        {{-- Info --}}
        <div class="mt-6 text-center">
            <p class="text-xs text-slate-600">
                <i class="fas fa-shield-alt mr-1"></i>
                Sistema de Guardia Nocturna
            </p>
        </div>
    </div>
</div>
@endsection
