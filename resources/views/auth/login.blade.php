<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - {{ config('app.name', 'AppGuardia') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Design System --}}
    @include('components.design-system')
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 font-sans leading-normal tracking-normal flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <!-- Logo / Marca Principal -->
        <div class="text-center mb-8">
            @if(file_exists(public_path('brand/guardiapp.png')))
                <img src="{{ asset('brand/guardiapp.png') }}?v={{ filemtime(public_path('brand/guardiapp.png')) }}" alt="GuardiAPP" class="mx-auto mb-1 h-[90px] w-auto">
            @else
                <div class="icon-box icon-box-gradient-red icon-box-xl mx-auto mb-4">
                    <i class="fas fa-helmet-safety text-4xl"></i>
                </div>
            @endif
            <p class="text-caption uppercase">Sistema de Gestión Operativa</p>
        </div>

        <!-- Tarjeta de Login -->
        <div class="card-base !shadow-2xl !border-t-4 !border-t-red-600 overflow-hidden">
            <div class="card-body">
                <div class="mb-6">
                    <h2 class="text-title-md">Bienvenido</h2>
                    <p class="text-body-sm">Ingrese sus credenciales para acceder al sistema.</p>
                </div>

               <form method="POST" action="{{ url()->current() }}">
                    @csrf

                    <div class="mb-5">
                        <label class="form-label" for="username">
                            Usuario
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-slate-400"></i>
                            </div>
                            <input class="form-input pl-10 @error('username') !border-red-500 !bg-red-50 dark:!bg-red-900/20 @enderror" 
                                   id="username" type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="admin">
                        </div>
                        @error('username')
                            <p class="form-error"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-8">
                        <label class="form-label" for="password">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400"></i>
                            </div>
                            <input class="form-input pl-10 @error('password') !border-red-500 !bg-red-50 dark:!bg-red-900/20 @enderror" 
                                   id="password" type="password" name="password" required placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="form-error"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <button class="btn btn-primary btn-lg w-full" type="submit">
                        <i class="fas fa-right-to-bracket mr-2"></i> Iniciar Sesión
                    </button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="text-caption">
                    &copy; {{ date('Y') }} {{ config('app.name', 'AppGuardia') }}.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
