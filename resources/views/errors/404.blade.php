@extends('layouts.app')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="text-center max-w-lg mx-auto">
            <div class="mb-6 relative">
                <i class="fas fa-map-signs text-9xl text-slate-200"></i>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-search text-5xl text-slate-400"></i>
                </div>
            </div>
            
            <h1 class="text-6xl font-extrabold text-slate-800 mb-2">404</h1>
            <h2 class="text-2xl font-bold text-slate-600 mb-4 uppercase tracking-wide">Página No Encontrada</h2>
            
            <p class="text-slate-500 mb-8 text-lg">
                La ruta solicitada no existe o ha sido movida. Verifique la dirección o regrese al panel principal.
            </p>

            <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-lg shadow-md transition-all transform hover:-translate-y-0.5 uppercase tracking-wide text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Cuartel
            </a>
        </div>
    </div>
@endsection
