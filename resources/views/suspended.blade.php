<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — GuardiAPP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-10">
            <div class="w-20 h-20 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ $title }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8">{{ $message }}</p>
            <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl text-xs text-slate-400">
                <p>Si crees que esto es un error, contacta al administrador de la plataforma.</p>
                <p class="mt-2 font-medium text-slate-500 dark:text-slate-400">soporte@guardianocturna.cl</p>
            </div>
        </div>
        <p class="text-xs text-slate-300 mt-6">GuardiAPP SaaS Platform</p>
    </div>
</body>
</html>
