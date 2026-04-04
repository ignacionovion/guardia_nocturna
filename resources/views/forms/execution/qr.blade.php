@extends('layouts.modern')

@section('title', 'QR Formularios')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('forms.execution.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-slate-900">QR Formularios</h1>
        </div>
        <p class="text-slate-600 ml-11">Código QR para acceso rápido a formularios desde dispositivos móviles</p>
    </div>

    {{-- QR Card --}}
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
        
        {{-- Header Card --}}
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 px-8 py-6 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <i class="fas fa-qrcode text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Código QR de Acceso</h2>
                    <p class="text-slate-300 text-sm">Escanea para completar formularios</p>
                </div>
            </div>
        </div>

        {{-- QR Content --}}
        <div class="p-12">
            
            {{-- DEBUG: URL Generada --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-xs font-bold text-blue-900 mb-1">DEBUG - URL Codificada en QR:</p>
                <p class="text-sm text-blue-700 font-mono break-all">{{ $qrUrl }}</p>
            </div>
            
            {{-- QR Code --}}
            <div class="flex justify-center mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-slate-200">
                    {!! QrCode::size(300)->margin(1)->errorCorrection('H')->generate($qrUrl) !!}
                </div>
            </div>

            {{-- URL Display --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-slate-900 mb-3">
                    <i class="fas fa-link mr-2"></i>
                    Enlace de Acceso
                </label>
                <div class="flex items-center gap-3">
                    <input 
                        type="text" 
                        id="qrUrl" 
                        value="{{ $qrUrl }}" 
                        readonly
                        class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 font-mono focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400">
                    <button 
                        onclick="copyUrl()"
                        class="px-4 py-3 bg-slate-100 text-slate-700 rounded-xl font-semibold text-sm hover:bg-slate-200 transition-all border border-slate-200">
                        <i class="fas fa-copy mr-2"></i>
                        Copiar
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este enlace es específico para tu compañía
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a 
                    href="{{ route('forms.execution.qr.download') }}"
                    class="flex items-center justify-center gap-2 px-6 py-4 bg-slate-900 text-white rounded-xl font-semibold text-sm hover:bg-slate-800 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-download"></i>
                    Descargar QR (PNG)
                </a>
                <button 
                    onclick="printQr()"
                    class="flex items-center justify-center gap-2 px-6 py-4 bg-white text-slate-700 border border-slate-200 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-all">
                    <i class="fas fa-print"></i>
                    Imprimir QR
                </button>
            </div>

            {{-- Instructions --}}
            <div class="mt-8 p-6 bg-slate-50 rounded-xl border border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3">
                    <i class="fas fa-lightbulb text-amber-500 mr-2"></i>
                    Instrucciones de Uso
                </h3>
                <ul class="space-y-2 text-sm text-slate-600">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                        <span>Los bomberos pueden escanear este QR con su celular para acceder a los formularios</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                        <span>Solo necesitan ingresar su RUT para validar su identidad</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                        <span>Pueden guardar borradores y continuar después</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                        <span>Puedes imprimir el QR y colocarlo en lugares visibles del cuartel</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    {{-- Back Button --}}
    <div class="mt-6 text-center">
        <a href="{{ route('forms.execution.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium transition-colors">
            <i class="fas fa-arrow-left"></i>
            Volver a Formularios
        </a>
    </div>
</div>

<script>
function copyUrl() {
    const urlInput = document.getElementById('qrUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // Para móviles
    
    navigator.clipboard.writeText(urlInput.value).then(() => {
        // Mostrar feedback visual
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-2"></i>Copiado';
        button.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
        button.classList.remove('bg-slate-100', 'text-slate-700', 'border-slate-200');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
            button.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-200');
        }, 2000);
    });
}

function printQr() {
    window.print();
}
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    
    .bg-white.rounded-2xl.shadow-lg,
    .bg-white.rounded-2xl.shadow-lg * {
        visibility: visible;
    }
    
    .bg-white.rounded-2xl.shadow-lg {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    button, a[href*="download"] {
        display: none !important;
    }
}
</style>
@endsection
