@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Nueva Emergencia</h1>
                <p class="text-gray-500 text-sm mt-1">Registra una emergencia ocurrida durante la guardia</p>
            </div>
            <a href="{{ route('admin.emergencies.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <form method="POST" action="{{ route('admin.emergencies.store') }}">
            @csrf
            @include('admin.emergencies._form')
        </form>
    </div>
@endsection
