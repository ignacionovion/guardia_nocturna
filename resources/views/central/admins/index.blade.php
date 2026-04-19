@extends('central.layouts.app')

@section('title', 'Administradores SaaS')

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Administradores SaaS</h1>
            <p class="text-slate-500 text-sm mt-1">Usuarios del panel central (sin relación con compañías tenant)</p>
        </div>
        <a href="{{ Route::has('central.admins.create') ? route('central.admins.create') : url('/admin/admins/create') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition">
            Nuevo administrador
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 w-16">ID</th>
                        <th class="px-5 py-3">Nombre</th>
                        <th class="px-5 py-3">Usuario</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Super admin</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($admins as $a)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3 text-slate-500 font-mono text-xs">{{ $a->id }}</td>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $a->name }}</td>
                            <td class="px-5 py-3 font-mono text-slate-700">{{ $a->username }}</td>
                            <td class="px-5 py-3">
                                @if($a->activo)
                                    <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Activo</span>
                                @else
                                    <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if($a->is_super_admin)
                                    <span class="text-xs font-medium text-amber-800 bg-amber-100 px-2 py-0.5 rounded">Super admin</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ Route::has('central.admins.edit') ? route('central.admins.edit', $a) : url('/admin/admins/'.$a->id.'/edit') }}"
                                   class="text-amber-700 hover:text-amber-900 font-medium mr-3">Editar</a>
                                @if($a->isNot(Auth::guard('central')->user()))
                                    <form method="POST"
                                          action="{{ Route::has('central.admins.destroy') ? route('central.admins.destroy', $a) : url('/admin/admins/'.$a) }}"
                                          class="inline"
                                          onsubmit="return confirm('¿Eliminar este administrador? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
