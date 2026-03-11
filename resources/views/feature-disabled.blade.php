<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Función no disponible — GuardiAPP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-10">
            <div class="w-20 h-20 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m-4.93-4.364A9 9 0 1119.07 8.636L12 21l-6.93-12.364z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Función no disponible</h1>
            <p class="text-slate-500 text-sm leading-relaxed mb-4">
                <strong>{{ $label }}</strong> no está incluida en tu plan <strong>{{ ucfirst($plan) }}</strong>.
            </p>
            <p class="text-slate-400 text-xs mb-8">Contacta al administrador de la plataforma para actualizar tu plan.</p>
            <a href="/dashboard" class="inline-block bg-slate-900 text-white text-sm font-medium py-2.5 px-6 rounded-xl hover:bg-slate-800 transition">
                Volver al Dashboard
            </a>
        </div>
    </div>
</body>
</html>
