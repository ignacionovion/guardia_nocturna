@extends('layouts.qr')

@section('content')
<div class="w-full" style="width: 100% !important; max-width: none !important;">
    <div id="qr-debug-wrapper" class="w-full mx-auto bg-white border-4 border-red-500 rounded-2xl p-6" style="width: 100% !important; max-width: 420px !important; min-width: 0 !important; display: block !important;">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900 mb-1">Formularios</h1>
            <p class="text-sm text-slate-600">Acceso por QR</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                <p class="text-sm text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <p class="text-sm text-slate-600 mb-5">Ingresa tu RUT para acceder a los formularios disponibles.</p>

        <form action="{{ route('qr.forms.process') }}" method="POST" class="w-full space-y-5">
            @csrf

            <div class="w-full">
                <label for="rut" class="block text-sm font-semibold text-slate-900 mb-2">RUT</label>
                <input
                    type="text"
                    name="rut"
                    id="rut"
                    required
                    placeholder="12345678-9"
                    class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400"
                    value="{{ old('rut') }}"
                    autofocus
                >
                @error('rut')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full bg-slate-900 text-white px-6 py-3 rounded-xl font-semibold text-base hover:bg-slate-800"
            >
                Continuar
            </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-200">
            <p class="text-xs text-slate-500 text-center">Solo personal registrado puede acceder</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('qr-debug-wrapper');
    if (el) {
        const info = document.createElement('div');
        info.style.position = 'fixed';
        info.style.bottom = '10px';
        info.style.left = '10px';
        info.style.zIndex = '99999';
        info.style.background = '#111827';
        info.style.color = '#fff';
        info.style.padding = '10px 12px';
        info.style.borderRadius = '10px';
        info.style.fontSize = '12px';
        info.textContent =
            'offsetWidth=' + el.offsetWidth +
            ' | clientWidth=' + el.clientWidth +
            ' | computedWidth=' + getComputedStyle(el).width +
            ' | display=' + getComputedStyle(el).display +
            ' | maxWidth=' + getComputedStyle(el).maxWidth +
            ' | minWidth=' + getComputedStyle(el).minWidth;
        document.body.appendChild(info);
    }
});
</script>
@endsection
