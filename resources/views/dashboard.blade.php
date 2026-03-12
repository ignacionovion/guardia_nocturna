@extends('layouts.modern')

@section('title', 'Dashboard - ' . branding()->nombre_empresa)
@section('page-title', 'Dashboard')

@section('content')
    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
        @include('dashboard._guardia')
    @else
        @include('dashboard._admin')
    @endif

    @include('dashboard._modals')
@endsection

@push('scripts')
    @include('dashboard._scripts')
@endpush
