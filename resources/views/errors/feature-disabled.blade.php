@extends('layouts.app')

@section('title', 'Funcionalidad no disponible en tu plan')

@section('content')
@php
    $label = $featureName ?? ($label ?? \App\Exceptions\PlanAccessDeniedException::featureLabel($feature ?? null));
@endphp
<div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6">
    <div class="max-w-lg w-full">
        <div class="rounded-3xl border border-slate-200/90 bg-white p-8 sm:p-10 shadow-xl shadow-slate-200/40 text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500">
                <i class="fas fa-lock text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Funcionalidad no disponible en tu plan
            </h1>
            <p class="mt-3 text-slate-600 leading-relaxed">
                Para acceder a <strong>{{ $label }}</strong> necesitás actualizar tu plan.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('tenant.upgrade') }}"
                   class="inline-flex justify-center items-center rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 transition-colors">
                    Ver planes y actualizar
                </a>
                <a href="{{ route('dashboard') }}"
                   class="inline-flex justify-center items-center rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 transition-colors">
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
