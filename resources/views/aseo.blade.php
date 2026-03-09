@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Guardia</div>
            <h1 class="text-xl font-black text-slate-800 uppercase tracking-tight">Asignación de Aseo</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition-colors">
            <i class="fas fa-arrow-left text-[11px]"></i> Volver
        </a>
    </div>

    <div class="mb-4 flex items-center gap-3">
        <form method="GET" action="{{ route('guardia.aseo') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300" />
            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg text-xs uppercase tracking-widest transition-colors">Ver</button>
        </form>
        <span class="text-xs text-slate-400">{{ $date->translatedFormat('d \d\e F \d\e Y') }}</span>
    </div>

    <form method="POST" action="{{ route('guardia.aseo.store') }}">
        @csrf
        <input type="hidden" name="assigned_date" value="{{ $date->toDateString() }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($tasks as $task)
                @php $current = $assignmentsByTaskId->get($task->id); @endphp
                <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-black text-slate-800 uppercase tracking-wide leading-tight">{{ $task->name }}</span>
                        <span class="shrink-0 text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded border {{ $current ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-500' }}">
                            {{ $current ? 'Asignado' : 'Pendiente' }}
                        </span>
                    </div>
                    @if($task->description)
                        <p class="text-xs text-slate-400 -mt-1">{{ $task->description }}</p>
                    @endif
                    <select name="assignments[{{ $task->id }}]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <option value="">Sin asignar</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ((string)($current?->firefighter_id) === (string)$u->id) ? 'selected' : '' }}>
                                {{ $u->apellido_paterno }}, {{ $u->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        <div class="mt-5 flex justify-end">
            <button type="submit" class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-black py-2.5 px-5 rounded-xl text-xs uppercase tracking-widest transition-colors">
                <i class="fas fa-floppy-disk"></i> Guardar
            </button>
        </div>
    </form>
@endsection
