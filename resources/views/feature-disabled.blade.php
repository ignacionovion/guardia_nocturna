<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionalidad no disponible en tu plan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 p-10">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-indigo-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m-4.93-4.364A9 9 0 1119.07 8.636L12 21l-6.93-12.364z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-3">Funcionalidad no disponible en tu plan</h1>
            <p class="text-slate-600 text-sm leading-relaxed mb-8">
                Para acceder a <strong>{{ $label }}</strong> necesitás actualizar tu plan.
            </p>
            <a href="{{ route('tenant.upgrade') }}" class="inline-block w-full sm:w-auto bg-indigo-600 text-white text-sm font-semibold py-3 px-8 rounded-2xl hover:bg-indigo-700 transition shadow-md">
                Ver planes y actualizar
            </a>
            <a href="/dashboard" class="mt-3 inline-block text-sm font-medium text-slate-600 hover:text-slate-900">
                Volver al inicio
            </a>
        </div>
    </div>
</body>
</html>
