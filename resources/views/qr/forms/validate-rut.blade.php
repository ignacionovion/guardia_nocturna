@extends('layouts.qr')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        
        {{-- Card principal --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            
            {{-- Header con icono --}}
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-8 text-center">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-white text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Formularios</h1>
                <p class="text-slate-300 text-sm">Acceso por QR</p>
            </div>

            {{-- Contenido --}}
            <div class="p-8">
                
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                <div class="mb-6 text-center">
                    <p class="text-sm text-slate-600">Ingresa tu RUT para acceder a los formularios disponibles</p>
                </div>

                <form action="{{ route('qr.forms.process') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="rut" class="block text-sm font-semibold text-slate-900 mb-2">
                            RUT
                        </label>
                        <input 
                            type="text" 
                            name="rut" 
                            id="rut" 
                            required
                            placeholder="12345678-9"
                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all"
                            value="{{ old('rut') }}"
                            autofocus>
                        @error('rut')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button 
                        type="submit"
                        class="w-full bg-slate-900 text-white px-6 py-4 rounded-xl font-bold text-base hover:bg-slate-800 transition-all shadow-lg hover:shadow-xl">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Continuar
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-200">
                    <p class="text-xs text-slate-500 text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Solo personal registrado puede acceder
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 text-center">
            <p class="text-xs text-slate-500">GuardiAPP - Sistema de Gestión</p>
        </div>
    </div>
</div>
@endsection
