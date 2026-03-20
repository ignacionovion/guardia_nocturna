@extends('layouts.modern')

@section('content')
<div class="w-full max-w-2xl mx-auto">
    <x-ui.page-header title="Código QR" subtitle="{{ $bed->name }}" icon="fas fa-qrcode" iconVariant="primary">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.beds.index') }}">
            Volver
        </x-ui.button>
        <x-ui.button variant="primary" size="md" icon="fas fa-print" href="{{ route('admin.beds.qr.print', $bed) }}" target="_blank">
            Imprimir
        </x-ui.button>
    </x-ui.page-header>

    <x-ui.card>
        <div class="p-8">
            {{-- QR Code --}}
            <div class="flex flex-col items-center mb-8">
                <div class="bg-white p-6 rounded-2xl border-4 border-[#9fb0c3] shadow-lg mb-4">
                    {!! QrCode::size(300)->generate(route('qr.bed.show', $bed->qr_token)) !!}
                </div>
                <p class="text-sm text-[#475569] text-center">Escanea este código para acceder a la información de la cama</p>
            </div>

            {{-- Información de la Cama --}}
            <div class="space-y-4">
                <div class="p-4 bg-[#e7eef5] border border-[#9fb0c3] rounded-xl">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Nombre</p>
                            <p class="text-sm font-bold text-[#1e293b]">{{ $bed->name }}</p>
                        </div>
                        @if($bed->location)
                        <div>
                            <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Ubicación</p>
                            <p class="text-sm font-bold text-[#1e293b]">{{ $bed->location }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Género</p>
                            <x-ui.badge variant="{{ $bed->gender_color }}" size="sm">
                                {{ $bed->gender_label }}
                            </x-ui.badge>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Estado</p>
                            <x-ui.badge variant="{{ $bed->status_color }}" size="sm">
                                {{ $bed->status_label }}
                            </x-ui.badge>
                        </div>
                    </div>
                </div>

                {{-- URL del QR --}}
                <div class="p-4 bg-[#e7eef5] border border-[#9fb0c3] rounded-xl">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-2">URL del código QR</p>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ route('qr.bed.show', $bed->qr_token) }}" 
                            class="form-input text-xs flex-1" id="qr-url">
                        <x-ui.button variant="secondary" size="sm" onclick="copyQrUrl()">
                            Copiar
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>
</div>

<script>
function copyQrUrl() {
    const input = document.getElementById('qr-url');
    input.select();
    document.execCommand('copy');
    alert('URL copiada al portapapeles');
}
</script>
@endsection
