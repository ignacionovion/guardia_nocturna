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

    <div class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100">
            <div class="text-xs font-black uppercase tracking-widest text-slate-600">Enlace</div>
            <div class="text-sm text-slate-700 mt-1 break-all font-mono">{{ $url }}</div>
        </div>

        <div class="p-10 flex flex-col items-center bg-slate-50">
            {{-- QR Container --}}
            <div class="relative">
                {{-- Main QR Card --}}
                <div class="relative bg-white p-8 rounded-3xl border-2 border-slate-800 shadow-xl">
                    <div class="relative">
                        {!! $qrSvg !!}
                    </div>
                </div>
            </div>
            
            {{-- Token Badge --}}
            <div class="mt-8 flex items-center gap-2 bg-slate-800 text-white rounded-full px-5 py-2.5 shadow-md">
                <i class="fas fa-fingerprint text-slate-400"></i>
                <span class="text-xs font-mono tracking-wider">{{ substr($link->token, 0, 16) }}...</span>
            </div>
            
            {{-- Instructions --}}
            <div class="mt-6 text-center">
                <p class="text-lg font-bold text-slate-800">Escanea para acceder al retiro</p>
                <p class="text-sm text-slate-500 mt-1 font-medium">Inventario • Guardia Nocturna</p>
            </div>
            
            {{-- Decorative Line --}}
            <div class="mt-6 w-24 h-0.5 bg-slate-400 rounded-full"></div>
        </div>
    </div>
</div>
@endsection
