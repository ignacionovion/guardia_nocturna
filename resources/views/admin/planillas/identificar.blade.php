<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planillas - Identificación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 min-h-screen text-slate-100">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="text-center">
            @if(file_exists(public_path('brand/guardiappcheck.png')))
                <img src="{{ asset('brand/guardiappcheck.png') }}?v={{ filemtime(public_path('brand/guardiappcheck.png')) }}" alt="GuardiaAPP" class="mx-auto h-[80px] w-auto drop-shadow-sm">
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 shadow-xl">
                    <i class="fas fa-table-list text-2xl text-slate-100"></i>
                </div>
            @endif
            <div class="-mt-3 text-xs font-black uppercase tracking-widest text-slate-400">Planillas</div>
            <div class="text-2xl font-extrabold text-white">Identificación</div>
            <div class="text-sm text-slate-400 mt-1">Ingresa tu RUT antes de registrar una revisión.</div>
        </div>

        @if(session('success'))
            <div class="mt-8 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-6 py-4 text-emerald-100">
                <div class="text-sm font-extrabold">{{ session('success') }}</div>
            </div>
        @endif

        <div class="mt-8 bg-white/5 border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/10 bg-white/5">
                <div class="text-sm font-black uppercase tracking-widest text-slate-300">RUT del bombero</div>
                <div class="text-sm text-slate-300 mt-1">Formato requerido: <span class="font-mono font-black">11222333-4</span></div>
            </div>

            <form method="POST" action="{{ route('planillas.qr.identificar.store', ['token' => $token]) }}" class="p-6 space-y-4">
                @csrf

                @if($errors->any())
                    <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                        <div class="text-sm font-extrabold">Revisa el RUT e intenta nuevamente.</div>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">RUT</label>
                    <input
                        type="text"
                        name="rut"
                        id="rut-input"
                        value="{{ old('rut') }}"
                        required
                        placeholder="11222333-4"
                        class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold font-mono uppercase"
                        autocomplete="off"
                        inputmode="numeric"
                    />
                    @error('rut')
                        <div class="mt-2 text-sm text-rose-200 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <script>
                    (function() {
                        const rutInput = document.getElementById('rut-input');
                        if (!rutInput) return;

                        function formatRUT(value) {
                            // Remove all non-alphanumeric characters
                            value = value.replace(/[^a-zA-Z0-9]/g, '');
                            
                            // Convert to uppercase
                            value = value.toUpperCase();
                            
                            // Limit to 9 characters (8 digits + 1 check digit)
                            if (value.length > 9) {
                                value = value.substring(0, 9);
                            }
                            
                            // Format with hyphen
                            if (value.length > 1) {
                                const body = value.substring(0, value.length - 1);
                                const checkDigit = value.substring(value.length - 1);
                                value = body + '-' + checkDigit;
                            }
                            
                            return value;
                        }

                        rutInput.addEventListener('input', function(e) {
                            const cursorPosition = this.selectionStart;
                            const oldValue = this.value;
                            const newValue = formatRUT(this.value);
                            
                            this.value = newValue;
                            
                            // Adjust cursor position
                            if (cursorPosition === oldValue.length) {
                                this.setSelectionRange(newValue.length, newValue.length);
                            } else {
                                // Keep cursor at same relative position
                                const diff = newValue.length - oldValue.length;
                                const newPosition = Math.max(0, cursorPosition + diff);
                                this.setSelectionRange(newPosition, newPosition);
                            }
                        });

                        rutInput.addEventListener('blur', function() {
                            // Final format on blur
                            this.value = formatRUT(this.value);
                        });

                        rutInput.addEventListener('keydown', function(e) {
                            // Allow: backspace, delete, tab, escape, enter
                            if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                                (e.keyCode === 65 && e.ctrlKey === true) ||
                                (e.keyCode === 67 && e.ctrlKey === true) ||
                                (e.keyCode === 86 && e.ctrlKey === true) ||
                                (e.keyCode === 88 && e.ctrlKey === true) ||
                                // Allow: home, end, left, right
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                                return;
                            }
                            
                            // Ensure that it is a number or K/k
                            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && 
                                (e.keyCode < 96 || e.keyCode > 105) && 
                                e.keyCode !== 75 && e.keyCode !== 107) {
                                e.preventDefault();
                            }
                        });
                    })();
                </script>

                <button type="submit" class="mt-2 w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[12px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                    <i class="fas fa-arrow-right"></i>
                    Continuar
                </button>

                <div class="text-xs text-slate-400">
                    Si tu RUT no aparece en el sistema, solicita que te agreguen en <span class="font-bold">Gestión de Voluntarios</span>.
                </div>
            </form>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            GuardiaAPP
        </div>
    </div>
</body>
</html>
