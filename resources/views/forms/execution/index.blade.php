@extends('layouts.modern')

@section('title', 'Formularios')

@section('content')
<div class="container mx-auto px-4 py-8">
    <x-ui.page-header title="Formularios" subtitle="Completa y gestiona tus formularios" icon="fas fa-clipboard-list" iconVariant="slate">
        @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
            <a href="{{ route('forms.builder.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl font-semibold text-sm hover:bg-slate-800 transition-all shadow-sm">
                <i class="fas fa-cog"></i>
                Configuración
            </a>
        @endif
    </x-ui.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Plantillas Disponibles -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Plantillas Disponibles</h2>
                <div class="space-y-3">
                    @forelse($templates as $template)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-white transition-colors">
                            <h3 class="font-medium text-gray-900">{{ $template->nombre }}</h3>
                            <p class="text-sm text-gray-500 mb-3">{{ count($template->estructura) }} campos</p>
                            <div class="flex gap-2">
                                <a href="{{ route('forms.execution.show', ['template' => $template->id]) }}" 
                                   class="flex-1 bg-blue-600 text-white text-center px-3 py-2 rounded hover:bg-blue-700 transition-colors text-sm">
                                    <i class="fas fa-play mr-1"></i>Completar
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-clipboard-list text-4xl mb-3"></i>
                            <p>No hay plantillas disponibles</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Mis Envíos -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Mis Envíos</h2>
                
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="space-y-4">
                    @forelse($submissions as $submission)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $submission->template->nombre }}</h3>
                                    <p class="text-sm text-gray-500">
                                        Enviado: {{ $submission->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($submission->estado === 'borrador')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                            Borrador
                                        </span>
                                        <a href="{{ route('forms.execution.edit', ['submission' => $submission->id]) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Enviado
                                        </span>
                                    @endif
                                    <form action="{{ route('forms.execution.destroy', ['submission' => $submission->id]) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Mostrar algunos datos del formulario -->
                            <div class="mt-3 text-sm text-gray-600">
                                @php
                                    $data = $submission->data;
                                    $showFields = array_slice($data, 0, 3, true);
                                @endphp
                                @foreach($showFields as $key => $value)
                                    @if(!empty($value))
                                        <span class="inline-block bg-white rounded px-2 py-1 mr-2 mb-1">
                                            <strong>{{ $key }}:</strong> {{ is_bool($value) ? ($value ? 'Sí' : 'No') : $value }}
                                        </span>
                                    @endif
                                @endforeach
                                @if(count($data) > 3)
                                    <span class="text-gray-400">...</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>No has completado ningún formulario aún</p>
                        </div>
                    @endforelse
                </div>

                {{ $submissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
