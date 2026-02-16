<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Retiro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 min-h-screen text-slate-100">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="text-center">
            @if(file_exists(public_path('brand/guardiapp9-0.png')))
                <img src="{{ asset('brand/guardiapp9-0.png') }}" alt="GuardiaAPP" class="mx-auto h-[80px] w-auto drop-shadow-sm">
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 shadow-xl">
                    <i class="fas fa-boxes-stacked text-2xl text-slate-100"></i>
                </div>
            @endif
            <div class="-mt-3 text-xs font-black uppercase tracking-widest text-slate-400">Inventario</div>
            <div class="text-2xl font-extrabold text-white">Retiro de bodega</div>
            <div class="text-sm text-slate-400 mt-1">{{ $bodega->nombre }}</div>
        </div>

        <div class="mt-8 bg-white/5 border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/10 bg-white/5">
                <div class="text-sm font-black uppercase tracking-widest text-slate-300">Registrar retiro</div>
                <div class="text-sm text-slate-300 mt-1">Selecciona un ítem e ingresa la cantidad retirada.</div>
            </div>

            <form method="POST" action="{{ isset($token) ? route('inventario.qr.retiro.store', ['token' => $token]) : route('inventario.retiro.store') }}" class="p-6 space-y-4">
                @csrf

                @if(session('success'))
                    <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-100">
                        <div class="text-sm font-extrabold">{{ session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                        <div class="text-sm font-extrabold">Revisa los datos e intenta nuevamente.</div>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Ítem</label>
                    <select name="item_id" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold">
                        <option value="">Seleccionar...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ (string) old('item_id') === (string) $item->id ? 'selected' : '' }}>
                                {{ $item->display_name }} (Stock: {{ $item->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Cantidad</label>
                    <input type="number" name="cantidad" min="1" value="{{ old('cantidad', 1) }}" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold" />
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Nota (opcional)</label>
                    <textarea name="nota" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold" placeholder="Ej: Se retiró para atención de trauma">{{ old('nota') }}</textarea>
                </div>

                <button type="submit" class="mt-2 w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[12px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                    <i class="fas fa-check"></i>
                    Registrar retiro
                </button>

                <div class="text-xs text-slate-400">
                    Deja una nota si el retiro fue para un uso específico.
                </div>
            </form>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            GuardiaAPP
        </div>
    </div>
</body>
</html>
