@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-broom mr-3 text-red-700"></i> Asignación de Aseo
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Asigna tareas de aseo para la guardia</p>
        </div>

        <a href="{{ route('dashboard') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-lg shadow-sm border border-slate-200 flex items-center gap-2 uppercase text-xs tracking-widest">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-4 border-b border-slate-200 bg-slate-50">
            <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Tareas</div>

            <form method="GET" action="{{ route('guardia.aseo') }}" class="flex items-center gap-3">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700" />
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-black py-2 px-4 rounded-lg text-[10px] uppercase tracking-widest">Ver</button>
            </form>
        </div>

        <form method="POST" action="{{ route('guardia.aseo.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="assigned_date" value="{{ $date->toDateString() }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($tasks as $task)
                    @php
                        $current = $assignmentsByTaskId->get($task->id);
                    @endphp
                    <div class="border border-slate-200 rounded-xl p-4 bg-white">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-black text-slate-900 uppercase tracking-wide">{{ $task->name }}</div>
                                @if($task->description)
                                    <div class="text-xs text-slate-500 mt-1">{{ $task->description }}</div>
                                @endif
                            </div>
                            <div class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded-lg border {{ $current ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600' }} shrink-0">
                                {{ $current ? 'ASIGNADO' : 'PENDIENTE' }}
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Responsable</label>
                            <select name="assignments[{{ $task->id }}]" class="w-full px-3 py-2.5 border border-slate-200 rounded-lg bg-white text-sm font-semibold text-slate-800">
                                <option value="">Sin asignar</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ ($current?->user_id === $u->id) ? 'selected' : '' }}>
                                        {{ $u->name }} {{ $u->last_name_paternal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center justify-end">
                <button type="submit" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-600 text-white">
                        <i class="fas fa-floppy-disk text-[11px]"></i>
                    </span>
                    <span>Guardar Asignación</span>
                </button>
            </div>
        </form>
    </div>
@endsection
