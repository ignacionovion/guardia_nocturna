@extends('central.layouts.app')

@section('title', 'Nuevo pago')

@section('content')
    <div class="mb-6">
        <a href="{{ route('central.payments.index') }}" class="text-sm text-slate-600 hover:text-slate-900">← Volver al listado</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-2">Registrar pago</h1>
        <p class="text-slate-500 text-sm mt-1">Registro contable en base central; al marcar <strong>pagado</strong> se sincroniza <code>tenant_billing</code> y el tenant.</p>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('central.payments.store') }}">
            @csrf
            @include('central.payments._form_fields')
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('central.payments.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-900 text-sm">Cancelar</a>
                <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Guardar</button>
            </div>
        </form>
    </div>
@endsection
