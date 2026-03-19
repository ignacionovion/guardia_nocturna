@props([
    'title' => null,
    'subtitle' => null,
    'breadcrumbs' => null,
    'actions' => null,
])

<div class="page-container">
    @if($title || $breadcrumbs || $actions)
    <div class="mb-6">
        @if($breadcrumbs)
        <nav class="mb-3">
            <ol class="flex items-center gap-2 text-sm text-slate-600">
                {{ $breadcrumbs }}
            </ol>
        </nav>
        @endif
        
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                @if($title)
                <h1 class="text-2xl font-black text-slate-900 mb-1">{{ $title }}</h1>
                @endif
                
                @if($subtitle)
                <p class="text-sm text-slate-600">{{ $subtitle }}</p>
                @endif
            </div>
            
            @if($actions)
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
            @endif
        </div>
    </div>
    @endif
    
    {{ $slot }}
</div>
