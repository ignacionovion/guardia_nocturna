@extends('layouts.qr')

@section('content')
<div class="min-h-screen p-4 sm:p-6 pb-20">
    <div class="w-full" style="max-width: 448px;">
        
        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Formularios Disponibles</h1>
                        <p class="text-slate-300 text-sm">{{ $bomberoName }}</p>
                    </div>
                    <form action="{{ route('qr.forms.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-white/80 hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if($templates->isEmpty())
            <div class="bg-white rounded-3xl shadow-xl p-12 text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-slate-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">No hay formularios disponibles</h3>
                <p class="text-slate-600">No hay formularios activos en este momento.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($templates as $template)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-900 mb-1">{{ $template->nombre }}</h3>
                                    @if($template->descripcion)
                                        <p class="text-sm text-slate-600">{{ $template->descripcion }}</p>
                                    @endif
                                </div>
                                @if(in_array($template->id, $drafts))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        <i class="fas fa-clock mr-1"></i>
                                        Borrador
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                                <div class="flex items-center gap-4 text-sm text-slate-600">
                                    <span>
                                        <i class="fas fa-list-ul mr-1"></i>
                                        {{ count($template->estructura) }} campos
                                    </span>
                                </div>
                                <a 
                                    href="{{ route('qr.forms.show', ['template' => $template->id, 'tenant' => tenant()->id]) }}"
                                    class="inline-flex items-center px-4 py-2 bg-slate-900 text-white rounded-xl font-semibold text-sm hover:bg-slate-800 transition-all">
                                    @if(in_array($template->id, $drafts))
                                        <i class="fas fa-edit mr-2"></i>
                                        Continuar
                                    @else
                                        <i class="fas fa-pen mr-2"></i>
                                        Completar
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Footer fijo --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4">
            <div class="w-full flex items-center justify-between" style="max-width: 448px; margin: 0 auto;">
                <p class="text-xs text-slate-500">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Sesión segura
                </p>
                <form action="{{ route('qr.forms.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-slate-600 hover:text-slate-900 font-medium">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
