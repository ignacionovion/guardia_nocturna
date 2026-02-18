@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Guardias Preventivas</div>
            <div class="text-2xl font-extrabold text-slate-900">QR · {{ $event->title }}</div>
            <div class="text-sm text-slate-600 mt-1">Este QR abre el formulario del turno actual.</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.preventivas.show', $event) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Volver</a>
        </div>
    </div>

    @php
        $status = strtolower((string) ($event->status ?? 'draft'));
        if (!in_array($status, ['draft', 'active', 'closed'], true)) {
            $status = 'draft';
        }
    @endphp

    @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-900">
            <div class="text-sm font-extrabold">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('warning'))
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 text-amber-900">
            <div class="text-sm font-extrabold">{{ session('warning') }}</div>
        </div>
    @endif

    <div class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <div class="flex items-center justify-center bg-slate-50 border border-slate-200 rounded-2xl p-6">
                {!! $svg !!}
            </div>

            <div>
                <div class="text-xs font-black uppercase tracking-widest text-slate-500">Link</div>
                <div class="mt-2 break-all text-sm font-semibold text-slate-800">{{ $url }}</div>

                <div class="mt-6">
                    <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                        Abrir formulario público
                    </a>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.preventivas.qr.print', $event) }}" target="_blank" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-900 font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-200">
                        <i class="fas fa-print"></i>
                        Imprimir QR
                    </a>
                </div>

                @if($status !== 'closed')
                    <form method="POST" action="{{ route('admin.preventivas.qr.regenerate', $event) }}" class="mt-3" onsubmit="return confirm('Esto generará un nuevo QR/link y el anterior dejará de funcionar. ¿Continuar?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-900 font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-200">
                            <i class="fas fa-rotate"></i>
                            Regenerar QR
                        </button>
                    </form>
                @endif

                <div class="mt-6 text-xs text-slate-500">
                    Comparte este QR para que el personal confirme su asistencia.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
