@extends('layouts.modern')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Editar Emergencia</h1>
                <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Actualiza los datos de la emergencia</p>
            </div>
            <a href="{{ route('admin.emergencies.index') }}" class="inline-flex items-center text-slate-600 dark:text-slate-400 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <form id="emergency-form" method="POST" action="{{ route('admin.emergencies.update', $emergency->id) }}">
            @csrf
            @method('PUT')
            @include('admin.emergencies._form', ['emergency' => $emergency])
        </form>
    </div>
@endsection
