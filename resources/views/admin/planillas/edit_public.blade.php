<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planillas - Continuar revisión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
<div class="w-full py-6 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
                <div class="text-2xl font-extrabold text-slate-900">Continuar revisión</div>
                <div class="text-sm text-slate-600 mt-1">Unidad: {{ $unidad }} · Iniciada: {{ $planilla->created_at->format('d/m/Y H:i') }}</div>
            </div>

            <a href="{{ route('planillas.qr.identificar.form', ['token' => $token]) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Salir</a>
        </div>

        @if(session('success'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-900">
                <div class="text-sm font-extrabold">{{ session('success') }}</div>
            </div>
        @endif

        <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-sky-100 border-b border-teal-900/20">
                <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
                <div class="text-xs font-black uppercase tracking-widest text-slate-900 mt-1">{{ $unidad }}</div>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('planillas.qr.update', ['token' => $token, 'planilla' => $planilla]) }}" class="mt-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="unidad" value="{{ $unidad }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Fecha y hora de revisión</div>
                            <input type="text" id="fechaRevisionDisplay" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold" value="{{ $planilla->fecha_revision->format('d/m/Y H:i') }}" disabled>
                            <input type="hidden" id="fechaRevisionHidden" name="fecha_revision" value="{{ $planilla->fecha_revision->format('Y-m-d\TH:i') }}" required>
                            <div class="text-xs text-slate-500 mt-2 font-semibold">Fecha de inicio de la revisión.</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        @php($data = old('data', $planilla->data ?? []))

                        @if($unidad === 'BR-3')
                            @include('admin.planillas.forms.br3', ['data' => $data])
                        @elseif($unidad === 'B-3')
                            @include('admin.planillas.forms.b3', ['data' => $data])
                        @elseif($unidad === 'RX-3')
                            @include('admin.planillas.forms.rx3', ['data' => $data])
                        @endif
                    </div>

                    <div class="mt-6 flex flex-col md:flex-row items-stretch md:items-center justify-end gap-3">
                        <button type="submit" name="guardar_continuar" value="1" class="inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                            <i class="fas fa-pause"></i>
                            Guardar y continuar después
                        </button>

                        <button type="submit" name="guardar_finalizar" value="1" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                            <i class="fas fa-check"></i>
                            Guardar y finalizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
