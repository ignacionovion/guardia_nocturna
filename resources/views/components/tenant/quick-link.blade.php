{{--
    Acceso rápido según plan: incluido → enlace al módulo; no incluido → CTA a tenant.upgrade (usa feature()).
    Props: featureKey, href, icon, title, subtitle,
           variant = dark | light | emerald | amber (solo afecta el estilo cuando el módulo está habilitado)
--}}
@props([
    'featureKey',
    'href',
    'icon',
    'title',
    'subtitle',
    'variant' => 'light',
])

@php
    $enabled = feature($featureKey);
    $upgradeUrl = route('tenant.upgrade');

    $styles = match ($variant) {
        'dark' => [
            'wrap' => 'bg-[#0f172a] hover:bg-[#1e293b] shadow-md',
            'iconBox' => 'bg-white/10',
            'icon' => 'text-white',
            'title' => 'text-white',
            'sub' => 'text-slate-400',
        ],
        'emerald' => [
            'wrap' => 'bg-white hover:bg-white border border-slate-200 shadow-sm',
            'iconBox' => 'bg-emerald-100',
            'icon' => 'text-emerald-600',
            'title' => 'text-[#1e293b]',
            'sub' => 'text-[#475569]',
        ],
        'amber' => [
            'wrap' => 'bg-white hover:bg-white border border-slate-200 shadow-sm',
            'iconBox' => 'bg-amber-100',
            'icon' => 'text-amber-600',
            'title' => 'text-[#1e293b]',
            'sub' => 'text-[#475569]',
        ],
        default => [
            'wrap' => 'bg-white hover:bg-white border border-slate-200 shadow-sm',
            'iconBox' => 'bg-white border border-slate-100',
            'icon' => 'text-slate-900',
            'title' => 'text-[#1e293b]',
            'sub' => 'text-[#475569]',
        ],
    };
@endphp

@if($enabled)
    <a href="{{ $href }}"
       {{ $attributes->class([
           'group p-4 rounded-[14px] transition-colors flex items-center gap-3 no-underline',
           $styles['wrap'],
       ]) }}>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 {{ $styles['iconBox'] }}">
            <i class="{{ $icon }} {{ $styles['icon'] }}"></i>
        </div>
        <div>
            <p class="font-semibold text-sm {{ $styles['title'] }}">{{ $title }}</p>
            <p class="text-xs {{ $styles['sub'] }}">{{ $subtitle }}</p>
        </div>
    </a>
@else
    <a href="{{ $upgradeUrl }}"
       title="Este módulo no está incluido en tu plan actual"
       {{ $attributes->class([
           'group p-4 rounded-[14px] flex items-center gap-3 no-underline border-2 border-dashed transition-colors relative overflow-hidden',
           $variant === 'dark'
               ? 'bg-[#0f172a]/60 border-slate-600 hover:border-indigo-400/50'
               : 'bg-slate-50 border-slate-300 hover:border-indigo-300 hover:bg-slate-100/80',
       ]) }}>
        <span class="absolute top-2 right-2 text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 border border-indigo-200">Plan</span>
        <div @class([
            'w-10 h-10 rounded-lg flex items-center justify-center shrink-0 opacity-50',
            $variant === 'dark' ? 'bg-white/5' : 'bg-slate-200/80',
        ])>
            <i class="{{ $icon }} {{ $variant === 'dark' ? 'text-slate-400' : 'text-slate-500' }}"></i>
        </div>
        <div class="min-w-0 pr-7">
            <p class="font-semibold text-sm {{ $variant === 'dark' ? 'text-slate-300' : 'text-slate-600' }}">{{ $title }}</p>
            <p class="text-xs text-indigo-600 font-medium">Mejorar plan para acceder</p>
        </div>
    </a>
@endif
