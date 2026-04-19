@props(['ux' => []])

@if(!empty($ux['show_banner']))
    @php
        $variant = $ux['variant'] ?? 'due_soon';
        $wrap = match ($variant) {
            'trial' => 'border-sky-200/80 bg-sky-50 text-sky-950',
            'grace' => 'border-amber-200/80 bg-amber-50 text-amber-950',
            default => 'border-slate-200/90 bg-slate-50 text-slate-900',
        };
        $iconWrap = match ($variant) {
            'trial' => 'bg-sky-100 text-sky-700',
            'grace' => 'bg-amber-100 text-amber-800',
            default => 'bg-slate-200/80 text-slate-700',
        };
        $icon = match ($variant) {
            'trial' => 'fa-seedling',
            'grace' => 'fa-hourglass-half',
            default => 'fa-calendar-day',
        };
    @endphp
    <div class="px-4 sm:px-6 pt-3 pb-0 shrink-0 border-b border-slate-200/80 bg-white/90">
        <div class="rounded-2xl border {{ $wrap }} px-4 py-3.5 sm:px-5 sm:py-4 flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconWrap }}">
                <i class="fas {{ $icon }} text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold leading-snug">{{ $ux['title'] ?? '' }}</p>
                @if(!empty($ux['body']))
                    <p class="mt-1 text-sm leading-relaxed opacity-95">{{ $ux['body'] }}</p>
                @endif
                @if(!empty($ux['hint']))
                    <p class="mt-2 text-xs leading-relaxed opacity-90">{{ $ux['hint'] }}</p>
                @endif
            </div>
            @if(!empty($ux['cta_route']) && !empty($ux['cta_label']))
                <div class="shrink-0 sm:self-center">
                    <a href="{{ $ux['cta_route'] }}"
                       class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 transition-colors">
                        {{ $ux['cta_label'] }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
