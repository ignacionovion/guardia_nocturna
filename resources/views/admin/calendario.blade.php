@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-calendar-alt mr-3 text-red-700"></i> Calendario
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Planificación de guardias por fecha</p>
        </div>

        <div class="bg-white p-1.5 rounded-lg shadow-sm border border-slate-200">
            <form action="{{ route('admin.calendario') }}" method="GET" class="flex items-center space-x-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-days text-slate-400"></i>
                    </div>
                    <select name="month" class="pl-10 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <select name="year" class="pl-4 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        @foreach(range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center">
                    <i class="fas fa-filter mr-2"></i> Ver
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-wide mb-4">Asignar rango</h2>

            <form method="POST" action="{{ route('admin.calendario.assign_range') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-1 uppercase tracking-wide">Desde</label>
                        <input type="date" name="from" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-1 uppercase tracking-wide">Hasta</label>
                        <input type="date" name="to" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-bold mb-1 uppercase tracking-wide">Guardia</label>
                    <select name="guardia_id" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2 bg-white">
                        @foreach($guardias as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Días</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="weekdays[]" value="1" checked class="rounded border-slate-300"> Lun</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="weekdays[]" value="2" checked class="rounded border-slate-300"> Mar</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="weekdays[]" value="3" checked class="rounded border-slate-300"> Mié</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="weekdays[]" value="4" checked class="rounded border-slate-300"> Jue</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="weekdays[]" value="5" checked class="rounded border-slate-300"> Vie</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="weekdays[]" value="6" checked class="rounded border-slate-300"> Sáb</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="weekdays[]" value="0" checked class="rounded border-slate-300"> Dom</label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center uppercase text-sm tracking-wide">
                    <i class="fas fa-save mr-2"></i> Aplicar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-wide mb-1">Generar rotación semanal</h2>
            <p class="text-xs text-slate-500 mb-4">Detectadas {{ $guardias->count() }} guardias en el sistema</p>

            <form method="POST" action="{{ route('admin.calendario.generate_rotation') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-1 uppercase tracking-wide">Domingo de inicio</label>
                        <input type="date" name="start_sunday" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-1 uppercase tracking-wide">Hasta</label>
                        <input type="date" name="end_date" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2">
                        <p class="text-xs text-slate-400 mt-1">Si lo dejás vacío, se genera hasta el 31/12 del año del domingo de inicio.</p>
                    </div>
                </div>

                @php
                    $guardiaCount = $guardias->count();
                    $defaultGuardias = $guardias->take($guardiaCount);
                @endphp

                <div class="grid grid-cols-1 {{ $guardiaCount == 2 ? 'sm:grid-cols-2' : ($guardiaCount == 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2 lg:grid-cols-' . min($guardiaCount, 4)) }} gap-4">
                    @foreach($guardias as $index => $g)
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1 uppercase tracking-wide">Semana {{ $index + 1 }}</label>
                            <select name="guardia_ids[]" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2 bg-white">
                                @foreach($guardias as $optionG)
                                    <option value="{{ $optionG->id }}" {{ $g->id === $optionG->id ? 'selected' : '' }}>{{ $optionG->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <!-- Email options -->
                <div class="border-t border-slate-200 pt-4 mt-4">
                    <label class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="send_email" value="1" class="rounded text-blue-600 focus:ring-blue-500 h-5 w-5 border-slate-300">
                        <div class="min-w-0">
                            <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Enviar resumen por correo</div>
                            <div class="text-xs text-slate-500">Se enviará el detalle de la rotación generada.</div>
                        </div>
                    </label>

                    <div class="mt-3">
                        <label class="block text-slate-700 text-xs font-bold mb-1 uppercase tracking-wide">Destinatarios (emails separados por coma)</label>
                        <input type="text" name="email_recipients" 
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2"
                            placeholder="ej: usuario@ejemplo.com, otro@ejemplo.com">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center uppercase text-sm tracking-wide">
                    <i class="fas fa-wand-magic-sparkles mr-2"></i> Generar Rotación
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800 flex items-center uppercase tracking-wide">
                <span class="w-2 h-6 bg-red-600 rounded mr-3"></span>
                {{ ucfirst($startOfMonth->locale('es')->monthName) }} {{ $year }}
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Día</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Guardia</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @php
                        $cursor = $startOfMonth->copy();
                    @endphp
                    @while($cursor->lessThanOrEqualTo($endOfMonth))
                        @php
                            $key = $cursor->toDateString();
                            $row = $calendarDays->get($key);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-slate-700">{{ $cursor->format('d-m-Y') }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-600">{{ ucfirst($cursor->locale('es')->dayName) }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-700">
                                @if($row && $row->guardia)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $row->guardia->name }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                        @php
                            $cursor->addDay();
                        @endphp
                    @endwhile
                </tbody>
            </table>
        </div>
    </div>
@endsection
