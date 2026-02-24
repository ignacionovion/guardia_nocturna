@extends('layouts.public')

@section('title', 'Confirmar Asignación - Cama ' . $bed->number)

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
            {{-- Header de Cama --}}
            <div class="bg-slate-800/50 px-6 py-4 border-b border-slate-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-cyan-500/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-bed text-cyan-400"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Cama</p>
                            <p class="text-lg font-black text-slate-100">#{{ $bed->number }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($currentAssignment)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                                <i class="fas fa-circle text-[8px] mr-1.5 animate-pulse"></i>
                                Ocupada
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20">
                                <i class="fas fa-circle text-[8px] mr-1.5"></i>
                                Disponible
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info del Bombero --}}
            <div class="px-6 py-4 bg-green-500/5 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-500/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-check text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Bombero</p>
                        <p class="text-sm font-bold text-slate-100">{{ $bombero->nombres }} {{ $bombero->apellido_paterno }}</p>
                        <p class="text-xs text-slate-500">RUT: {{ $bombero->rut }}</p>
                    </div>
                </div>
            </div>

            {{-- Confirmación --}}
            <div class="p-6">
                @if($currentAssignment && $currentAssignment->firefighter)
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-triangle-exclamation text-amber-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-amber-400 mb-1">Esta cama está ocupada</p>
                                <p class="text-xs text-amber-400/70">
                                    Actualmente está asignada a <span class="font-bold">{{ $currentAssignment->firefighter->nombres }} {{ $currentAssignment->firefighter->apellido_paterno }}</span>. 
                                    Al continuar, se liberará automáticamente.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @php
                    // Verificar si el bombero ya tiene otra cama
                    $existingBed = \App\Models\BedAssignment::query()
                        ->where('firefighter_id', $bombero->id)
                        ->whereNull('released_at')
                        ->with('bed')
                        ->first();
                @endphp

                @if($existingBed)
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-blue-400 mb-1">Ya tienes una cama asignada</p>
                                <p class="text-xs text-blue-400/70">
                                    Actualmente estás en la cama <span class="font-bold">#{{ $existingBed->bed->number }}</span>. 
                                    Al continuar, se te cambiará a esta cama.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <p class="text-slate-400 text-center mb-6">
                    ¿Confirmas que quieres asignarte la <span class="text-slate-100 font-bold">Cama #{{ $bed->number }}</span>?
                </p>

                <form method="POST" action="{{ route('camas.scan.assign.store', ['bedId' => $bed->id]) }}">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg shadow-green-500/20 flex items-center justify-center gap-2">
                        <i class="fas fa-check"></i>
                        Confirmar Asignación
                    </button>
                </form>

                <a href="{{ route('camas.scan.form', ['bedId' => $bed->id, 'reset' => 1]) }}" class="block text-center mt-4 text-sm text-slate-500 hover:text-slate-300 transition-colors">
                    <i class="fas fa-rotate-left mr-1"></i> Cancelar y cambiar RUT
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
