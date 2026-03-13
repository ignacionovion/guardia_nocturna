@php
    $isGuardiaView = Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia;
@endphp

@extends($isGuardiaView ? 'layouts.guardia-panel' : 'layouts.modern')

@section('title', $isGuardiaView ? 'Panel de Control - ' . ($myGuardia->name ?? 'Guardia') : 'Dashboard - ' . branding()->nombre_empresa)

@unless($isGuardiaView)
    @section('page-title', 'Dashboard')
@endunless

@section('content')
    @if($isGuardiaView)
        @include('dashboard._guardia')
    @else
        @include('dashboard._admin')
    @endif

    @include('dashboard._modals')
@endsection

@push('scripts')
    @include('dashboard._scripts')
@endpush
