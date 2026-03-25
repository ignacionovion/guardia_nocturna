@extends('layouts.app')

@section('title', 'Funcionalidad No Disponible')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <i class="fas fa-lock text-6xl text-gray-400 mb-4"></i>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Funcionalidad No Disponible
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                El módulo <strong>{{ $featureName ?? 'solicitado' }}</strong> no está incluido en tu plan actual.
            </p>
        </div>
        
        <div class="rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        Para acceder a esta funcionalidad, actualiza tu plan o contacta al administrador.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
