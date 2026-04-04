@extends('layouts.qr')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        
        {{-- Card principal --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            
            {{-- Header con icono --}}
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-8 text-center">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-white text-5xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">¡Formulario Enviado!</h1>
                <p class="text-emerald-100 text-sm">{{ $bomberoName }}</p>
            </div>

            {{-- Contenido --}}
            <div class="p-8 text-center">
                
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                <div class="mb-8">
                    <p class="text-slate-600 mb-4">Tu formulario ha sido enviado correctamente y ya no puede ser modificado.</p>
                    <p class="text-sm text-slate-500">Gracias por completar el formulario.</p>
                </div>

                <div class="space-y-3">
                    <a 
                        href="{{ route('qr.forms.list') }}"
                        class="block w-full bg-slate-900 text-white px-6 py-4 rounded-xl font-bold text-base hover:bg-slate-800 transition-all shadow-lg hover:shadow-xl">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Ver Otros Formularios
                    </a>

                    <form action="{{ route('qr.forms.logout') }}" method="POST">
                        @csrf
                        <button 
                            type="submit"
                            class="w-full bg-white text-slate-700 border border-slate-200 px-6 py-4 rounded-xl font-bold text-base hover:bg-slate-50 transition-all">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-200">
                    <p class="text-xs text-slate-500">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Tus datos han sido guardados de forma segura
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
