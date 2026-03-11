@if(session('impersonating'))
    @php $imp = session('impersonating'); @endphp
    <div class="bg-amber-500 text-amber-950 px-4 py-2 text-center text-sm font-medium sticky top-0 z-[9999] shadow-lg">
        <div class="flex items-center justify-center space-x-4">
            <span>
                🎭 Impersonando como <strong>{{ $imp['user_name'] }}</strong> en <strong>{{ $imp['tenant_name'] }}</strong>
                <span class="text-amber-800 text-xs ml-2">(Admin: {{ $imp['central_admin_name'] }})</span>
            </span>
            <a href="{{ route('impersonate.stop') }}"
               class="bg-amber-950 text-amber-100 px-3 py-1 rounded-lg text-xs font-bold hover:bg-amber-900 transition">
                Salir de Impersonación
            </a>
        </div>
    </div>
@endif
