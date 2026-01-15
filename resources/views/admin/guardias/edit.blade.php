@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto py-12">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                    <i class="fas fa-edit mr-3 text-red-700"></i> Editar Guardia
                </h1>
                <p class="text-slate-500 mt-1 font-medium">Modificar nombre de la unidad operativa</p>
            </div>
            <a href="{{ route('admin.guardias') }}" class="inline-flex items-center text-slate-600 hover:text-blue-600 font-medium transition-colors bg-white px-4 py-2 rounded-lg border border-slate-200 hover:border-blue-300 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
            <!-- Barra superior decorativa -->
            <div class="h-2 bg-red-700"></div>

            <div class="p-8">
                <form action="{{ route('admin.guardias.update', $guardia->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-8">
                        <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="name">
                            Nombre de la Guardia
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-shield-halved text-slate-400"></i>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name', $guardia->name) }}" required
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:bg-white transition-all text-slate-800 font-medium placeholder-slate-400">
                        </div>
                        @error('name')
                            <p class="text-red-600 text-xs mt-2 font-medium flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center uppercase tracking-wide text-sm">
                            <i class="fas fa-save mr-2"></i> Actualizar Nombre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
