@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Emergencias</h1>
            <p class="text-gray-500 text-sm mt-1">Registro de emergencias transcurridas en guardias nocturnas</p>
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            <a href="{{ route('admin.emergency-keys.index') }}" class="inline-flex items-center bg-slate-700 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200">
                <i class="fas fa-key mr-2"></i> Claves
            </a>
            <a href="{{ route('admin.emergency-units.index') }}" class="inline-flex items-center bg-slate-700 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200">
                <i class="fas fa-truck mr-2"></i> Unidades
            </a>
            <a href="{{ route('admin.emergencies.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Nueva Emergencia
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm mb-8 border border-gray-100">
        <form action="{{ route('admin.emergencies.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por clave o detalle del llamado..."
                    class="w-full pl-11 pr-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">

                @if(request('search'))
                    <a href="{{ route('admin.emergencies.index') }}" class="absolute right-20 text-gray-400 hover:text-gray-600 p-2">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <button type="submit" class="ml-3 bg-slate-800 hover:bg-slate-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    @if($emergencies->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-dashed border-slate-300">
            <div class="bg-slate-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-truck-medical text-slate-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-slate-900">No hay emergencias registradas</h3>
            <p class="text-slate-500 mt-1">Registra una emergencia para comenzar el historial.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Clave</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">H. salida</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">H. llegada</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Unidades</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">A cargo</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($emergencies as $emergency)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-bold text-slate-900">{{ $emergency->key?->code ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($emergency->key?->description ?? '', 60) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    <div class="text-sm font-semibold text-slate-900">{{ $emergency->dispatched_at?->format('d-m-Y') }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $emergency->dispatched_at?->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    @if($emergency->arrived_at)
                                        <div class="text-sm font-semibold text-slate-900">{{ $emergency->arrived_at->format('d-m-Y') }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $emergency->arrived_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-wrap gap-1 max-w-sm">
                                        @forelse($emergency->units as $unit)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $unit->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    @if($emergency->officerInCharge)
                                        <div class="text-sm font-medium text-slate-900">{{ $emergency->officerInCharge->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $emergency->guardia?->name ?? '-' }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.emergencies.edit', $emergency->id) }}" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.emergencies.destroy', $emergency->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta emergencia? Esta acción no se puede deshacer.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Eliminar">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                {{ $emergencies->links() }}
            </div>
        </div>
    @endif
@endsection
