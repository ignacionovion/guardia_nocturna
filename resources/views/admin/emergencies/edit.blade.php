@extends('layouts.modern')

@section('content')
    <div class="max-w-7xl mx-auto">
        <x-ui.page-header title="Editar Emergencia" subtitle="Actualiza los datos de la emergencia" icon="fas fa-truck-medical" iconVariant="red">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergencies.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <form id="emergency-form" method="POST" action="{{ route('admin.emergencies.update', $emergency->id) }}">
            @csrf
            @method('PUT')
            @include('admin.emergencies._form', ['emergency' => $emergency])
        </form>
    </div>
@endsection
