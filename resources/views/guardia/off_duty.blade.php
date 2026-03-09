<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia fuera de servicio - {{ config('app.name', 'AppGuardia') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-6">
    <div class="fixed inset-0 bg-slate-950/95"></div>

    <div class="relative z-10 w-full max-w-xl">
        <div class="rounded-3xl border border-slate-800 bg-slate-900/95 shadow-2xl overflow-hidden">
            <div class="px-8 py-10 sm:px-10 sm:py-12 text-center">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300">
                    <i class="fas fa-moon text-3xl"></i>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Esta guardia está fuera de servicio.
                </h1>

                <p class="mt-3 text-base sm:text-lg font-medium text-slate-300">
                    ¡Nos vemos pronto!
                </p>

                <div class="mt-8 rounded-2xl border border-slate-800 bg-slate-950/70 px-5 py-4 text-sm text-slate-400">
                    Mientras esta guardia no esté en turno, el único acceso disponible es cerrar la sesión.
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-4 text-sm font-extrabold uppercase tracking-wider text-white shadow-lg transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                        <i class="fas fa-right-from-bracket"></i>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
