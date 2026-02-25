@extends('layouts.public')

@section('title', 'Escanear QR - Cama ' . $bed->number)

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
                        @php
                            $currentAssignment = \App\Models\BedAssignment::query()
                                ->where('bed_id', $bed->id)
                                ->whereNull('released_at')
                                ->first();
                        @endphp
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

            {{-- Formulario RUT o Mensaje fuera de horario --}}
            <div class="p-6">
                @if(!$withinGuardiaHours)
                    {{-- Fuera de horario --}}
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-500/10 rounded-full mb-3">
                            <i class="fas fa-clock text-2xl text-amber-400"></i>
                        </div>
                        <p class="text-lg font-bold text-slate-100 mb-2">Horario no disponible</p>
                        <p class="text-sm text-slate-400 mb-4">Las asignaciones de camas solo están disponibles durante el horario de guardia.</p>
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-2">Horario de guardia</p>
                            <p class="text-sm text-slate-300">
                                <span class="text-amber-400 font-bold">Domingo a Jueves:</span> 23:00 - 07:00
                            </p>
                            <p class="text-sm text-slate-300 mt-1">
                                <span class="text-amber-400 font-bold">Viernes y Sábado:</span> 22:00 - 07:00
                            </p>
                        </div>
                    </div>
                @elseif($bombero)
                    {{-- Ya identificado --}}
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-14 h-14 bg-green-500/10 rounded-full mb-3">
                            <i class="fas fa-user-check text-2xl text-green-400"></i>
                        </div>
                        <p class="text-sm text-slate-500 mb-1">Identificado como</p>
                        <p class="text-lg font-bold text-slate-100">{{ $bombero->nombres }} {{ $bombero->apellido_paterno }}</p>
                        <p class="text-xs text-slate-500 mt-1">RUT: {{ $bombero->rut }}</p>
                    </div>

                    <form method="POST" action="{{ route('camas.scan.rut', ['bedId' => $bed->id]) }}">
                        @csrf
                        <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg shadow-cyan-500/20 flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-right"></i>
                            Continuar
                        </button>
                    </form>

                    <a href="{{ route('camas.scan.form', ['bedId' => $bed->id, 'reset' => 1]) }}" class="block text-center mt-4 text-sm text-slate-500 hover:text-slate-300 transition-colors">
                        <i class="fas fa-rotate-left mr-1"></i> Cambiar bombero
                    </a>
                @else
                    {{-- Formulario de RUT --}}
                    <form method="POST" action="{{ route('camas.scan.rut', ['bedId' => $bed->id]) }}">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="rut" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                                Ingresa tu RUT
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-slate-500"></i>
                                </div>
                                <input 
                                    type="text" 
                                    id="rut" 
                                    name="rut" 
                                    value="{{ old('rut') }}"
                                    placeholder="12345678-5"
                                    class="w-full bg-slate-950 border border-slate-700 text-slate-100 text-center font-bold text-lg rounded-xl py-4 pl-12 pr-4 placeholder-slate-600 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('rut')
                                <p class="mt-2 text-sm text-red-400 flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg shadow-cyan-500/20 flex items-center justify-center gap-2">
                            <i class="fas fa-search"></i>
                            Verificar
                        </button>
                    </form>
                @endif
            </div>

            {{-- Footer --}}
            <div class="bg-slate-800/30 px-6 py-4 border-t border-slate-800">
                <a href="{{ route('camas') }}" class="flex items-center justify-center gap-2 text-sm text-slate-500 hover:text-slate-300 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Dashboard de Camas
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

@push('scripts')
<script>
    // Auto-formatear RUT mientras escribe
    document.getElementById('rut')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9kK]/g, '');
        if (value.length > 1) {
            let body = value.slice(0, -1);
            let dv = value.slice(-1).toLowerCase();
            if (body.length > 8) body = body.slice(0, 8);
            e.target.value = body + '-' + dv;
        }
    });
</script>
@endpush
