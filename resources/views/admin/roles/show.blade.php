@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="flex items-start justify-between gap-4 mb-8">
            <div>
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Rol</div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $role->name }}</h1>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-50 text-slate-700 border border-slate-100 font-mono">
                        {{ $role->slug }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">Volver</a>
                <a href="{{ route('admin.roles.edit', $role->id) }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            </div>
        </div>

        @php
            $perms = is_array($role->permissions) ? $role->permissions : [];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Permisos</div>
                </div>
                <div class="p-6">
                    @if(empty($perms))
                        <div class="text-sm text-slate-500 italic">Sin permisos asignados.</div>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($perms as $p)
                                <span class="inline-flex items-center px-2 py-1 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">
                                    {{ $p }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Usuarios asignados</div>
                </div>
                <div class="p-6">
                    @if($role->users->isEmpty())
                        <div class="text-sm text-slate-500 italic">Ningún usuario tiene este rol asignado.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left text-xs font-black text-slate-500 uppercase tracking-wider pb-3">Usuario</th>
                                        <th class="text-left text-xs font-black text-slate-500 uppercase tracking-wider pb-3">Rol base</th>
                                        <th class="text-right text-xs font-black text-slate-500 uppercase tracking-wider pb-3">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($role->users as $u)
                                        <tr>
                                            <td class="py-3">
                                                <div class="text-sm font-bold text-slate-900">{{ $u->name }}</div>
                                                <div class="text-xs text-slate-500 font-mono">{{ $u->email }}</div>
                                            </td>
                                            <td class="py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                                    {{ str_replace('_', ' ', $u->role) }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-right">
                                                <a href="{{ route('admin.users.edit', $u->id) }}" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Editar usuario">
                                                    <i class="fas fa-user-pen"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
