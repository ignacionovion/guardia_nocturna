{{-- Contenido del modal de Asignación de Aseo --}}
<div class="p-6">
    <div class="mb-4 flex items-center gap-3">
        <div class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date->toDateString() }}" id="aseo-date-input"
                class="px-3 py-2 rounded-lg border border-slate-700 bg-slate-950 text-sm font-semibold text-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500" 
                onchange="reloadAseoModal(this.value)" />
            <button type="button" onclick="reloadAseoModal(document.getElementById('aseo-date-input').value)" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg text-sm uppercase tracking-wide transition-colors">
                Ver
            </button>
        </div>
        <span class="text-sm text-slate-400">{{ $date->translatedFormat('d \d\e F \d\e Y') }}</span>
    </div>

    <form method="POST" action="{{ route('guardia.aseo.store') }}" id="aseo-form">
        @csrf
        <input type="hidden" name="assigned_date" value="{{ $date->toDateString() }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[50vh] overflow-y-auto pr-2">
            @foreach($tasks as $task)
                @php $current = $assignmentsByTaskId->get($task->id); @endphp
                <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-bold text-white uppercase tracking-wide leading-tight">{{ $task->name }}</span>
                        <span class="shrink-0 text-xs font-bold uppercase tracking-wide px-2 py-1 rounded border {{ $current ? 'border-emerald-500/30 bg-emerald-500/20 text-emerald-300' : 'border-slate-700 bg-slate-800 text-slate-400' }}">
                            {{ $current ? 'Asignado' : 'Pendiente' }}
                        </span>
                    </div>
                    @if($task->description)
                        <p class="text-sm text-slate-400 -mt-1">{{ $task->description }}</p>
                    @endif
                    <select name="assignments[{{ $task->id }}]" 
                        class="w-full px-3 py-2 border border-slate-700 rounded-lg bg-slate-950 text-sm font-semibold text-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Sin asignar</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ((string)($current?->firefighter_id) === (string)$u->id) ? 'selected' : '' }}>
                                {{ $u->apellido_paterno }}, {{ $u->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeAseoModal()" 
                class="px-5 py-2.5 rounded-lg border border-slate-800 bg-slate-950 text-slate-100 font-bold text-sm hover:bg-slate-900 transition-colors uppercase">
                Cancelar
            </button>
            <button type="submit" 
                class="flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white font-bold py-2.5 px-5 rounded-lg text-sm uppercase tracking-wide transition-colors shadow-md">
                <i class="fas fa-floppy-disk"></i> Guardar
            </button>
        </div>
    </form>
</div>

<script>
    // Reload modal content with new date
    window.reloadAseoModal = async function(date) {
        const content = document.getElementById('aseoModalContent');
        if (!content) return;
        
        content.innerHTML = '<div class="flex items-center justify-center py-12"><i class="fas fa-spinner fa-spin text-slate-400 text-2xl"></i></div>';
        
        try {
            const response = await fetch('/aseo/modal?date=' + date, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const html = await response.text();
                content.innerHTML = html;
            }
        } catch (error) {
            console.error('Error reloading aseo modal:', error);
        }
    }

    // Handle form submission via AJAX to avoid page reload
    document.getElementById('aseo-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAseoModal();
                if (typeof showSuccessToast === 'function') {
                    showSuccessToast('Aseo Guardado', 'Las asignaciones se guardaron correctamente');
                }
            } else {
                alert(data.message || 'Error al guardar');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error saving aseo:', error);
            alert('Error de conexión al guardar');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
</script>
