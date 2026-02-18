@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="max-w-2xl mx-auto">
        <div class="bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 shadow-xl">
            <div class="p-8 text-center">
                @if(file_exists(public_path('brand/guardiappcheck.png')))
                    <img src="{{ asset('brand/guardiappcheck.png') }}?v={{ filemtime(public_path('brand/guardiappcheck.png')) }}" alt="GuardiaAPP" class="mx-auto h-[70px] w-auto drop-shadow-sm">
                @endif
                <div class="mt-2 text-xs font-black uppercase tracking-widest text-slate-400">Inventario</div>
                <div class="text-2xl font-extrabold text-white">Identificación</div>
                <div class="text-sm text-slate-400 mt-1">Ingresa el RUT de la persona que retira.</div>
            </div>

            <div class="p-6 border-t border-slate-800 bg-white/5">
                @if(isset($bombero) && $bombero)
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-4">
                            <div class="text-xs font-black uppercase tracking-widest text-emerald-100/80">Confirmación</div>
                            <div class="mt-1 text-lg font-extrabold text-white">¿Eres {{ trim((string)($bombero->nombres ?? '') . ' ' . (string)($bombero->apellido_paterno ?? '')) }}?</div>
                            <div class="mt-1 text-sm text-emerald-100/80 font-semibold font-mono">{{ $bombero->rut }}</div>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('inventario.retiro.form') }}" class="w-1/2 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                                <i class="fas fa-check"></i>
                                Sí, continuar
                            </a>
                            <a href="{{ route('inventario.retiro.identificar.form', ['reset' => 1]) }}" class="w-1/2 inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-white font-black py-3 px-6 rounded-xl text-[11px] uppercase tracking-widest border border-slate-700">
                                <i class="fas fa-user-edit"></i>
                                No, cambiar RUT
                            </a>
                        </div>

                        <a href="{{ route('inventario.dashboard') }}" class="inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] uppercase tracking-widest border border-slate-800 w-full">
                            <i class="fas fa-arrow-left"></i>
                            Volver
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('inventario.retiro.identificar.store') }}" class="space-y-4">
                        @csrf

                        @if($errors->any())
                            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                                <div class="text-sm font-extrabold">Revisa el RUT e intenta nuevamente.</div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">RUT</label>
                            <input type="text" name="rut" value="{{ old('rut') }}" required placeholder="18485962-9" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold font-mono" autocomplete="off" />
                            @error('rut')
                                <div class="mt-2 text-sm text-rose-200 font-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('inventario.dashboard') }}" class="w-1/2 inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-white font-black py-3 px-6 rounded-xl text-[11px] uppercase tracking-widest border border-slate-700">
                                <i class="fas fa-arrow-left"></i>
                                Volver
                            </a>
                            <button type="submit" class="w-1/2 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                                <i class="fas fa-arrow-right"></i>
                                Continuar
                            </button>
                        </div>

                        <div class="text-xs text-slate-400">
                            Si no existe, el sistema mostrará: <span class="font-bold">Bombero no existe en nuestra base de datos.</span>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
