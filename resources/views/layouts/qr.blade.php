<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GuardiAPP QR</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100">
    <main class="min-h-screen w-full px-4 py-8 flex items-start justify-center">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>
</body>
</html>
