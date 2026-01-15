@extends('layouts.app')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="text-center max-w-lg mx-auto">
            <div class="mb-6 relative">
                <i class="fas fa-cogs text-9xl text-slate-200"></i>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-5xl text-red-400"></i>
                </div>
            </div>
            
            <h1 class="text-6xl font-extrabold text-slate-800 mb-2">500</h1>
            <h2 class="text-2xl font-bold text-slate-600 mb-4 uppercase tracking-wide">Error del Sistema</h2>
            
            <p class="text-slate-500 mb-8 text-lg">
                Ha ocurrido un error interno en el servidor. El incidente ha sido registrado para su revisión técnica.
            </p>

            <div class="flex justify-center gap-4">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg shadow-sm transition-all text-sm uppercase">
                    <i class="fas fa-undo mr-2"></i> Reintentar
                </a>
                <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg shadow-md transition-all text-sm uppercase">
                    <i class="fas fa-home mr-2"></i> Inicio
                </a>
            </div>
        </div>
    </div>
@endsection
