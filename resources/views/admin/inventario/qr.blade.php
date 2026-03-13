@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <x-ui.page-header title="QR Fijo" subtitle="Escanea para ir directo al formulario de retiro." icon="fas fa-qrcode" iconVariant="purple">
        <div class="flex items-center gap-2">
            <x-ui.button variant="secondary" size="sm" icon="fas fa-gear" href="{{ route('inventario.config.form') }}">Configuración</x-ui.button>
            <x-ui.button variant="secondary" size="sm" icon="fas fa-print" href="{{ route('inventario.qr.print') }}" target="_blank">Imprimir</x-ui.button>
            <form method="POST" action="{{ route('inventario.qr.regenerar') }}" onsubmit="return confirm('¿Regenerar QR? El código anterior dejará de funcionar.')">
                @csrf
                <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-rotate">Regenerar</x-ui.button>
            </form>
        </div>
    </x-ui.page-header>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-slate-100">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Enlace</div>
            <div class="text-sm text-slate-700 dark:text-slate-300 mt-1 break-all font-mono">{{ $url }}</div>
        </div>

        <div class="p-10 flex flex-col items-center bg-slate-50 dark:bg-slate-800">
            {{-- QR Container --}}
            <div class="relative">
                {{-- Main QR Card --}}
                <div class="relative bg-white dark:bg-slate-900 p-8 rounded-3xl border-2 border-slate-800 shadow-xl">
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
                <p class="text-lg font-bold text-slate-800 dark:text-white">Escanea para acceder al retiro</p>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Inventario • Guardia Nocturna</p>
            </div>
            
            {{-- Decorative Line --}}
            <div class="mt-6 w-24 h-0.5 bg-slate-400 rounded-full"></div>
        </div>
    </div>
</div>
@endsection
