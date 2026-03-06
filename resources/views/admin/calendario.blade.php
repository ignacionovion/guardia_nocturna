@extends('layouts.app')

@section('content')
    {{-- Header --}}
    <div class="max-w-7xl mx-auto mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-600 to-red-700 text-white flex items-center justify-center shadow-lg shadow-red-200">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Calendario de Guardias</h1>
                    <p class="text-slate-500 text-sm">Planificación semanal y asignación de dotaciones</p>
                </div>
            </div>

            {{-- Month/Year Selector --}}
            <form action="{{ route('admin.calendario') }}" method="GET" class="flex items-center gap-2 bg-white p-1.5 rounded-xl shadow-sm border border-slate-200">
                <select name="month" class="px-3 py-2 bg-slate-50 border-0 rounded-lg text-sm font-semibold text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors focus:ring-2 focus:ring-blue-500">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                        </option>
                    @endforeach
                </select>
                <select name="year" class="px-3 py-2 bg-slate-50 border-0 rounded-lg text-sm font-semibold text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors focus:ring-2 focus:ring-blue-500">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-4 rounded-lg text-sm transition">
                    <i class="fas fa-eye"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Action Cards --}}
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Asignar Rango --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-white font-black text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-calendar-plus"></i>
                    Asignar Rango de Fechas
                </h2>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.calendario.assign_range') }}" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha Inicio</label>
                            <div class="relative">
                                <i class="fas fa-calendar absolute left-3 top-3 text-slate-400 text-sm"></i>
                                <input type="date" name="from" required 
                                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha Fin</label>
                            <div class="relative">
                                <i class="fas fa-calendar absolute left-3 top-3 text-slate-400 text-sm"></i>
                                <input type="date" name="to" required 
                                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Guardia Asignada</label>
                        <div class="relative">
                            <i class="fas fa-shield absolute left-3 top-3 text-slate-400 text-sm"></i>
                            <select name="guardia_id" required 
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none">
                                @foreach($guardias as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-3 text-slate-400 text-xs"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Días de la Semana</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                ['1', 'Lun', 'blue'], ['2', 'Mar', 'emerald'], ['3', 'Mié', 'amber'],
                                ['4', 'Jue', 'purple'], ['5', 'Vie', 'rose'], ['6', 'Sáb', 'indigo'], ['0', 'Dom', 'red']
                            ] as [$val, $label, $color])
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="weekdays[]" value="{{ $val }}" checked class="peer sr-only">
                                    <span class="inline-flex items-center justify-center w-14 h-10 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold uppercase border border-slate-200 peer-checked:bg-{{ $color }}-500 peer-checked:text-white peer-checked:border-{{ $color }}-500 transition-all hover:bg-slate-200">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-4 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-check-circle"></i>
                        Aplicar Asignación
                    </button>
                </form>
            </div>
        </div>

        {{-- Generar Rotación --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-6 py-4">
                <h2 class="text-white font-black text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    Generar Rotación Semanal
                </h2>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.calendario.generate_rotation') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Domingo Inicio</label>
                            <div class="relative">
                                <i class="fas fa-play-circle absolute left-3 top-3 text-slate-400 text-sm"></i>
                                <input type="date" name="start_sunday" required 
                                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:bg-white focus:border-slate-500 focus:ring-2 focus:ring-slate-200 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Hasta (Opcional)</label>
                            <div class="relative">
                                <i class="fas fa-stop-circle absolute left-3 top-3 text-slate-400 text-sm"></i>
                                <input type="date" name="end_date" 
                                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:bg-white focus:border-slate-500 focus:ring-2 focus:ring-slate-200 transition-all">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Vacío = hasta fin de año</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Orden de Rotación</label>
                        <div class="space-y-2">
                            @foreach($guardias as $index => $g)
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                    <div class="w-8 h-8 rounded-lg bg-slate-700 text-white flex items-center justify-center text-xs font-black">
                                        {{ $index + 1 }}
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">Semana {{ $index + 1 }}</span>
                                    <div class="flex-1">
                                        <select name="guardia_ids[]" required 
                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                                            @foreach($guardias as $optionG)
                                                <option value="{{ $optionG->id }}" {{ $g->id === $optionG->id ? 'selected' : '' }}>{{ $optionG->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="send_email" value="1" class="mt-0.5 rounded text-slate-700 focus:ring-slate-500 h-4 w-4">
                            <div>
                                <div class="text-sm font-bold text-slate-700">Enviar resumen por correo</div>
                                <div class="text-xs text-slate-500 mt-0.5">Se enviará el detalle de la rotación generada</div>
                            </div>
                        </label>
                        <input type="text" name="email_recipients" 
                            class="w-full mt-3 px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 placeholder:text-slate-400"
                            placeholder="usuario@ejemplo.com, otro@ejemplo.com">
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-black py-3 px-4 rounded-xl shadow-lg shadow-slate-200 transition-all flex items-center justify-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-magic"></i>
                        Generar Rotación
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 px-6 py-5 border-b border-slate-800 flex justify-between items-center">
            <h2 class="text-lg font-black text-white flex items-center uppercase tracking-wider">
                <i class="fas fa-calendar-week mr-3 text-red-500"></i>
                {{ ucfirst($startOfMonth->locale('es')->monthName) }} {{ $year }}
            </h2>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-400 font-medium">{{ $calendarDays->count() }} días configurados</span>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-xs text-slate-400">Con guardia</span>
                </div>
            </div>
        </div>

        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200">
            @foreach(['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $dayName)
                <div class="px-2 py-3 text-center border-r border-slate-200 last:border-r-0">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider">{{ $dayName }}</div>
                </div>
            @endforeach
        </div>

        {{-- Calendar Days --}}
        <div class="grid grid-cols-7 auto-rows-fr bg-slate-200 gap-px">
            @php
                $firstDayOfMonth = $startOfMonth->copy()->startOfMonth();
                $startOfGrid = $firstDayOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                $endOfGrid = $startOfMonth->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
                $cursor = $startOfGrid->copy();
            @endphp
            
            @while($cursor->lessThanOrEqualTo($endOfGrid))
                @php
                    $key = $cursor->toDateString();
                    $row = $calendarDays->get($key);
                    $isCurrentMonth = $cursor->month === $startOfMonth->month;
                    $isToday = $cursor->toDateString() === now()->toDateString();
                @endphp
                <div class="bg-white min-h-[120px] p-3 relative {{ !$isCurrentMonth ? 'bg-slate-50/70' : '' }} {{ $isToday ? 'ring-2 ring-inset ring-red-500' : '' }}">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-lg font-black {{ $isCurrentMonth ? 'text-slate-800' : 'text-slate-300' }} {{ $isToday ? 'w-8 h-8 bg-red-600 text-white rounded-lg flex items-center justify-center text-base' : '' }}">
                            {{ $cursor->day }}
                        </span>
                        @if($isToday)
                            <span class="text-[10px] font-black text-red-600 bg-red-50 px-2 py-0.5 rounded uppercase">Hoy</span>
                        @endif
                    </div>
                    @if($row && $row->guardia)
                        <div class="mt-1">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                                {{ $row->guardia->name }}
                            </div>
                        </div>
                    @else
                        <div class="mt-2 text-xs text-slate-300 italic">Sin asignar</div>
                    @endif
                </div>
                @php $cursor->addDay(); @endphp
            @endwhile
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="max-w-7xl mx-auto mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($guardias as $g)
            @php
                $daysCount = $calendarDays->filter(fn($d) => $d->guardia_id === $g->id)->count();
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                    <i class="fas fa-shield text-lg"></i>
                </div>
                <div class="flex-1">
                    <div class="text-base font-black text-slate-800">{{ $g->name }}</div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-2xl font-black text-blue-600">{{ $daysCount }}</span>
                        <span class="text-xs text-slate-500">días asignados</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs font-bold text-slate-400 uppercase">{{ round(($daysCount / max($calendarDays->count(), 1)) * 100) }}%</div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
