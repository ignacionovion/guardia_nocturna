<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($blocked['title'] ?? $title) }} — GuardiAPP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 sm:p-6">
@php
    $rk = $reason_key ?? 'suspendido';
    if (! in_array($rk, ['suspendido', 'vencido', 'cancelado'], true)) {
        $rk = 'suspendido';
    }
    $blocked = \App\Support\TenantSubscriptionUx::forBlockedScreen(tenant(), $rk);
@endphp
    <div class="max-w-lg w-full">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-8 pt-10 pb-6 text-center border-b border-slate-100">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-5">
                    @if($rk === 'suspendido')
                        <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($rk === 'vencido')
                        <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $blocked['title'] }}</h1>
                @if(!empty($expired_at))
                    <p class="mt-2 text-sm text-slate-500">Fecha de referencia: {{ $expired_at }}</p>
                @endif
            </div>
            <div class="px-8 py-7 text-left">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $blocked['lead'] }}</p>
                @if(!empty($blocked['points']))
                    <ul class="mt-5 space-y-3">
                        @foreach($blocked['points'] as $point)
                            <li class="flex gap-3 text-sm text-slate-700 leading-relaxed">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400" aria-hidden="true"></span>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500 mb-2">¿Necesitás ayuda?</p>
                <a href="mailto:{{ $blocked['support'] }}" class="text-sm font-semibold text-slate-900 hover:underline">{{ $blocked['support'] }}</a>
            </div>
        </div>
        <p class="text-center text-xs text-slate-400 mt-8">GuardiAPP</p>
    </div>
</body>
</html>
