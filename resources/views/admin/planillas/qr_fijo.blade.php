@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">QR fijo (Revisión de niveles)</div>
            <div class="text-sm text-slate-600 dark:text-slate-400 mt-1">Escanea para ir directo a crear una nueva planilla.</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.planillas.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white font-bold text-xs">
                <i class="fas fa-arrow-left"></i>
                Historial
            </a>
            <a href="{{ route('admin.planillas.qr_fijo.print') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white font-extrabold text-xs">
                <i class="fas fa-print"></i>
                Imprimir
            </a>
            <form method="POST" action="{{ route('admin.planillas.qr_fijo.regenerar') }}" onsubmit="return confirm('¿Regenerar QR? El código anterior dejará de funcionar.')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-extrabold text-xs">
                    <i class="fas fa-rotate"></i>
                    Regenerar
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-slate-100">
            <div class="text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-400">Enlace</div>
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
                
                {{-- QR Badge --}}
                <div class="absolute -top-3 -right-3 w-10 h-10 bg-slate-800 rounded-full shadow-lg flex items-center justify-center border-2 border-white">
                    <i class="fas fa-qrcode text-white text-sm"></i>
                </div>
            </div>
            
            {{-- Instructions --}}
            <div class="mt-8 text-center">
                <p class="text-lg font-bold text-slate-800 dark:text-white">Escanea para crear planilla</p>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Planillas • Guardia Nocturna</p>
            </div>
            
            {{-- Decorative Line --}}
            <div class="mt-6 w-24 h-0.5 bg-slate-400 rounded-full"></div>
        </div>
    </div>
</div>
@endsection
