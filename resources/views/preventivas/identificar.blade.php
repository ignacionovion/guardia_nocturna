@extends('layouts.app')

@section('content')
<div class="w-full min-h-screen bg-slate-900 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            @if(file_exists(public_path('brand/guardiappcheck.png')))
                <img src="{{ asset('brand/guardiappcheck.png') }}?v={{ filemtime(public_path('brand/guardiappcheck.png')) }}" alt="GuardiaAPP" class="mx-auto h-[80px] w-auto drop-shadow-sm">
            @else
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-600 text-white mb-4 shadow-2xl">
                    <i class="fas fa-shield-alt text-4xl"></i>
                </div>
            @endif
            <div class="mt-4 text-xs font-black uppercase tracking-widest text-slate-400">Preventivas</div>
            <div class="text-3xl font-extrabold text-white mt-1">Identificación</div>
            <div class="text-sm text-slate-400 mt-2">Ingresa tu RUT para registrar tu asistencia.</div>
        </div>

        <div class="bg-slate-800/50 backdrop-blur-sm rounded-2xl border border-slate-700 p-6 shadow-2xl">
            @if($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-rose-500/20 border border-rose-500/30 text-rose-200 text-sm font-bold">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('preventivas.public.identificar.store', $token) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="rut" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">RUT</label>
                    <input type="text" 
                           name="rut" 
                           id="rut" 
                           required 
                           placeholder="Ej: 18485962-9"
                           value="{{ old('rut') }}"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-600 bg-slate-700/50 text-white text-center font-bold text-lg tracking-wider placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 uppercase">
                    <p class="mt-2 text-[10px] text-slate-500 font-semibold">Formato: 12345678-9 (con guion y dígito verificador)</p>
                </div>

                <button type="submit" class="w-full py-4 px-6 bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest rounded-xl border border-blue-500 shadow-lg shadow-blue-600/20 transition-all active:scale-[0.98]">
                    <i class="fas fa-fingerprint mr-2"></i>
                    Identificar
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('preventivas.public.show', $token) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-300 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Volver a la lista
                </a>
            </div>
        </div>

        <div class="mt-8 text-center">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-600">{{ $event->title }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('rut').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9kK]/g, '').toUpperCase();
        if (value.length > 8) {
            value = value.slice(0, -1) + '-' + value.slice(-1);
        }
        e.target.value = value;
    });
</script>
@endpush
