{{-- Grid de personal activo - Diseño Premium --}}
<section>
    {{-- DEBUG PROFESIONAL: Panel de diagnóstico --}}
    @php
        $generatedUrl = route('admin.guardias.bulk_update', ['tenant' => tenant('id'), 'id' => $myGuardia->id]);
        $currentTenant = tenant('id');
        $currentDomain = request()->getHost();
        $routeParams = ['tenant' => $currentTenant, 'id' => $myGuardia->id];
        \Log::info('=== DEBUG ASISTENCIA ===');
        \Log::info('Tenant: ' . $currentTenant);
        \Log::info('Domain: ' . $currentDomain);
        \Log::info('Guardia ID: ' . $myGuardia->id);
        \Log::info('Generated URL: ' . $generatedUrl);
    @endphp
    
    @if(app()->environment('local') || request()->has('debug'))
    <div id="debug-asistencia-panel" style="background:#1e293b;border:2px solid #ef4444;border-radius:8px;padding:12px;margin-bottom:16px;font-family:monospace;font-size:12px;">
        <div style="color:#ef4444;font-weight:bold;margin-bottom:8px;">🔍 DEBUG ASISTENCIA</div>
        <div style="color:#94a3b8;">URL Generada: <span style="color:#22d3ee;word-break:break-all;">{{ $generatedUrl }}</span></div>
        <div style="color:#94a3b8;">Tenant: <span style="color:#22d3ee;">{{ $currentTenant }}</span></div>
        <div style="color:#94a3b8;">Dominio: <span style="color:#22d3ee;">{{ $currentDomain }}</span></div>
        <div style="color:#94a3b8;">Guardia ID: <span style="color:#22d3ee;">{{ $myGuardia->id }}</span></div>
        <div style="color:#94a3b8;">Route Params: <span style="color:#22d3ee;">{{ json_encode($routeParams) }}</span></div>
        <button type="button" onclick="testAttendanceUrl()" style="margin-top:8px;background:#3b82f6;color:white;border:none;padding:4px 12px;border-radius:4px;cursor:pointer;">Test URL (Fetch)</button>
        <div id="debug-test-result" style="margin-top:8px;color:#fbbf24;"></div>
    </div>
    
    <script>
    function testAttendanceUrl() {
        const url = '{{ $generatedUrl }}';
        const resultDiv = document.getElementById('debug-test-result');
        resultDiv.innerHTML = 'Testing...';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData()
        }).then(r => {
            resultDiv.innerHTML = '<span style="color:#4ade7d;">Status: ' + r.status + ' ' + r.statusText + '</span><br>Content-Type: ' + r.headers.get('content-type');
            return r.text();
        }).then(t => {
            console.log('[DEBUG] Test response:', t.substring(0, 500));
            resultDiv.innerHTML += '<br>Response length: ' + t.length;
        }).catch(e => {
            resultDiv.innerHTML = '<span style="color:#ef4444;">Error: ' + e.message + '</span>';
            console.error('[DEBUG] Test error:', e);
        });
    }
    
    // Interceptar submit del form para debug
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('guardia-attendance-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('=== FORM SUBMIT DEBUG ===');
                console.log('Action:', form.action);
                console.log('Method:', form.method);
                console.log('Form data:');
                const data = new FormData(form);
                for (let [key, value] of data.entries()) {
                    console.log('  ' + key + ':', value);
                }
                
                // Mostrar en panel debug
                const panel = document.getElementById('debug-asistencia-panel');
                if (panel) {
                    const submitDebug = document.createElement('div');
                    submitDebug.style.cssText = 'margin-top:8px;padding:8px;background:#0f172a;border-radius:4px;';
                    submitDebug.innerHTML = '<div style="color:#fbbf24;">Submit detected!</div><div style="color:#94a3b8;font-size:10px;">Action: ' + form.action + '</div>';
                    panel.appendChild(submitDebug);
                }
            });
        }
    });
    </script>
    @endif
    
    <form id="guardia-attendance-form" method="POST" action="{{ $generatedUrl }}">
        @csrf

        {{-- Grid principal de bomberos - más columnas para tarjetas angostas --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5">
            @forelse($activeStaff as $staff)
                @include('dashboard.partials.guardia._staff_card', [
                    'staff' => $staff,
                    'myGuardia' => $myGuardia,
                    'replacementByReplacement' => $replacementByReplacement,
                    'replacementByOriginal' => $replacementByOriginal,
                    'bedByFirefighter' => $bedByFirefighter,
                    'canInhabilitar' => $canInhabilitar,
                ])
            @empty
                <div class="col-span-full bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border border-slate-700/50 p-12 text-center shadow-lg">
                    <div class="w-16 h-16 rounded-2xl bg-slate-700/50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl text-slate-500"></i>
                    </div>
                    <div class="text-lg font-semibold text-slate-400">Sin dotación asignada</div>
                    <div class="text-sm text-slate-500 mt-1">No hay personal asignado a esta guardia</div>
                </div>
            @endforelse
        </div>

        {{-- Sección de inhabilitados --}}
        @if($outOfServiceStaff->isNotEmpty())
            <div class="mt-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center">
                        <i class="fas fa-user-slash text-red-400 text-sm"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Inhabilitados</span>
                    <span class="px-2 py-0.5 rounded-lg bg-red-500/20 border border-red-500/30 text-xs font-bold text-red-400">{{ $outOfServiceStaff->count() }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5">
                    @foreach($outOfServiceStaff as $staff)
                        <div class="bg-gradient-to-b from-slate-800/70 to-slate-900/70 rounded-xl border border-red-900/30 overflow-hidden p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[9px] font-medium text-slate-500 uppercase tracking-wider">{{ $staff->cargo_texto ?? 'Bombero' }}</span>
                                <span class="text-[8px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-red-500/20 text-red-400 border border-red-500/30">Inhab.</span>
                            </div>
                            <div class="text-xs font-semibold text-slate-300 leading-tight truncate" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                                {{ $staff->apellido_paterno }}, {{ $staff->nombres }}
                            </div>
                            @if($canInhabilitar)
                                <button type="button" onclick="toggleHabilitar('{{ $staff->id }}')" class="mt-2 w-full bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 font-semibold uppercase tracking-wider text-[9px] py-1.5 rounded border border-emerald-500/30 transition-colors">
                                    <i class="fas fa-user-check mr-1"></i> Habilitar
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </form>
</section>
