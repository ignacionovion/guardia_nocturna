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
@endsection

@push('modals')
    @if($isGuardiaView)
        {{-- Mismo patrón que modales: fuera del árbol del main para evitar clipping/stacking raro --}}
        @include('dashboard.partials.guardia._stale_banner')
    @endif
    @include('dashboard._modals')
@endpush

@push('scripts')
    @include('dashboard._scripts')
@endpush
