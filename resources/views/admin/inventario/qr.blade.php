@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Inventario</div>
            <div class="text-2xl font-extrabold text-slate-900">QR fijo</div>
            <div class="text-sm text-slate-600 mt-1">Escanea para ir directo al formulario de retiro.</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventario.config.form') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs">
                <i class="fas fa-gear"></i>
                Configuración
            </a>
            <a href="{{ route('inventario.qr.print') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-extrabold text-xs">
                <i class="fas fa-print"></i>
                Imprimir
            </a>
            <form method="POST" action="{{ route('inventario.qr.regenerar') }}" onsubmit="return confirm('¿Regenerar QR? El código anterior dejará de funcionar.')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-extrabold text-xs">
                    <i class="fas fa-rotate"></i>
                    Regenerar
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-teal-900/20 bg-sky-100">
            <div class="text-xs font-black uppercase tracking-widest text-slate-600">Enlace</div>
            <div class="text-sm text-slate-700 mt-1 break-all">{{ $url }}</div>
        </div>

        <div class="p-6 flex flex-col items-center">
            <div class="bg-white p-4 rounded-2xl border border-slate-200">
                {!! $qrSvg !!}
            </div>
            <div class="mt-4 text-sm text-slate-600">Token: <span class="font-mono text-xs">{{ $link->token }}</span></div>
        </div>
    </div>
</div>
@endsection
