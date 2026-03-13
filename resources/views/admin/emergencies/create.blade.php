@extends('layouts.modern')

@section('content')
    <div class="max-w-7xl mx-auto">
        <x-ui.page-header title="Nueva Emergencia" subtitle="Registra una emergencia ocurrida durante la guardia" icon="fas fa-truck-medical" iconVariant="red">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergencies.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <form id="emergency-form" method="POST" action="{{ route('admin.emergencies.store') }}">
            @csrf
            @include('admin.emergencies._form')
        </form>
    </div>
@endsection
