@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Editar Clave</h1>
                <p class="text-slate-500 text-sm mt-1">Actualiza código y descripción</p>
            </div>
            <a href="{{ route('admin.emergency-keys.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border-2 border-slate-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.emergency-keys.update', $key->id) }}" class="p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Código</label>
                        <input type="text" name="code" value="{{ old('code', $key->code) }}" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Descripción</label>
                        <textarea name="description" rows="4" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">{{ old('description', $key->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.emergency-keys.index') }}" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition-colors">Cancelar</a>
                    <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
