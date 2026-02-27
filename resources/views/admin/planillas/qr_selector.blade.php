<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planillas - Seleccionar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
<div class="w-full py-6 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
                <div class="text-2xl font-extrabold text-slate-900">Mis revisiones</div>
                <div class="text-sm text-slate-600 mt-1">Tienes planillas pendientes de terminar.</div>
            </div>

            <a href="{{ route('planillas.qr.identificar.form', ['token' => $token]) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Salir</a>
        </div>

        @if(session('success'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-900">
                <div class="text-sm font-extrabold">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-6 py-4 text-rose-900">
                <div class="text-sm font-extrabold">{{ session('error') }}</div>
            </div>
        @endif

        <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-sky-100 border-b border-teal-900/20">
                <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLAS EN EDICIÓN</div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($planillas as $p)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <div class="text-lg font-extrabold text-slate-900">{{ $p->unidad }}</div>
                                    <div class="text-sm text-slate-600 mt-1">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        Creada el {{ $p->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        Última edición: {{ $p->updated_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold uppercase bg-amber-100 text-amber-900 border border-amber-200">
                                            En edición
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('planillas.qr.edit.form', ['token' => $token, 'planilla' => $p]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-teal-900/20 bg-sky-50 hover:bg-sky-100 text-slate-900 font-extrabold text-xs">
                                        <i class="fas fa-pen"></i>
                                        Continuar editando
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="text-sm font-extrabold text-slate-900">¿Quieres crear una nueva planilla?</div>
                        <div class="text-xs text-slate-600 mt-1">Si ya completaste las anteriores, puedes comenzar una nueva revisión.</div>
                    </div>
                    <a href="{{ route('planillas.qr.create.form', ['token' => $token]) }}" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                        <i class="fas fa-plus"></i>
                        Nueva planilla
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
