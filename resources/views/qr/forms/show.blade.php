@extends('layouts.qr')

@section('content')
<div class="min-h-screen p-4 sm:p-6 pb-24">
    <div class="w-full" style="max-width: 448px;">
        
        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6">
                <div class="flex items-center justify-between mb-3">
                    <a href="{{ route('qr.forms.list') }}" class="text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <form action="{{ route('qr.forms.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-white/80 hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </button>
                    </form>
                </div>
                <h1 class="text-2xl font-bold text-white mb-1">{{ $template->nombre }}</h1>
                <p class="text-slate-300 text-sm">{{ $bomberoName }}</p>
                @if($template->descripcion)
                    <p class="text-slate-400 text-sm mt-2">{{ $template->descripcion }}</p>
                @endif
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 font-semibold mb-2">Por favor corrige los siguientes errores:</p>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario --}}
        <form id="formQr" method="POST" class="space-y-4">
            @csrf
            
            @foreach($template->estructura as $index => $campo)
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <label class="block text-sm font-bold text-slate-900 mb-3">
                        {{ $campo['nombre'] }}
                        @if($campo['requerido'] ?? false)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @php
                        $fieldName = "campo_{$index}";
                        $oldValue = old($fieldName);
                        $draftValue = $draft ? ($draft->data_json[$campo['nombre']] ?? null) : null;
                        $value = $oldValue ?? $draftValue;
                    @endphp

                    @switch($campo['tipo'])
                        @case('text')
                            <input 
                                type="text" 
                                name="{{ $fieldName }}" 
                                value="{{ $value }}"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all"
                                @if($campo['requerido'] ?? false) required @endif>
                            @break

                        @case('number')
                            <input 
                                type="number" 
                                name="{{ $fieldName }}" 
                                value="{{ $value }}"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all"
                                @if($campo['requerido'] ?? false) required @endif>
                            @break

                        @case('textarea')
                            <textarea 
                                name="{{ $fieldName }}" 
                                rows="4"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all resize-none"
                                @if($campo['requerido'] ?? false) required @endif>{{ $value }}</textarea>
                            @break

                        @case('select')
                            <select 
                                name="{{ $fieldName }}"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all"
                                @if($campo['requerido'] ?? false) required @endif>
                                <option value="">Selecciona una opción</option>
                                @foreach($campo['opciones'] ?? [] as $opcion)
                                    <option value="{{ $opcion }}" {{ $value == $opcion ? 'selected' : '' }}>
                                        {{ $opcion }}
                                    </option>
                                @endforeach
                            </select>
                            @break

                        @case('checkbox')
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="{{ $fieldName }}" 
                                    value="1"
                                    {{ $value ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-slate-300 text-slate-900 focus:ring-slate-900/10">
                                <span class="text-sm text-slate-600">Sí</span>
                            </label>
                            @break
                    @endswitch

                    @error($fieldName)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            {{-- Botones fijos en el footer --}}
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 shadow-lg">
                <div class="grid grid-cols-2 gap-3" style="max-width: 448px; margin: 0 auto;">
                    <button 
                        type="button"
                        onclick="submitAsDraft()"
                        class="px-6 py-3 bg-white text-slate-700 border border-slate-200 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-all">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Borrador
                    </button>
                    <button 
                        type="button"
                        onclick="submitFinal()"
                        class="px-6 py-3 bg-slate-900 text-white rounded-xl font-semibold text-sm hover:bg-slate-800 transition-all shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function submitAsDraft() {
    const form = document.getElementById('formQr');
    form.action = "{{ route('qr.forms.draft', $template->id) }}";
    form.submit();
}

function submitFinal() {
    const form = document.getElementById('formQr');
    form.action = "{{ route('qr.forms.submit', $template->id) }}";
    form.submit();
}
</script>
@endsection
