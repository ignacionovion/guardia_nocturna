<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
        body { font-size: 10px; color: #0f172a; }
        .title { text-align:center; font-weight:700; font-size: 14px; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 6px; vertical-align: top; }
        th { background: #1e40af; color: #fff; font-weight: 700; text-align: center; }
        .time { width: 12%; background: #e2e8f0; font-weight: 700; text-align: center; }
        .names { font-size: 9px; line-height: 1.25; }
        .dateHead { background: #3b82f6; color: #fff; font-weight: 700; }
    </style>
</head>
<body>
    <div class="title">CALENDARIO GUARDIAS PREVENTIVAS {{ strtoupper($event->title) }}</div>

    <table style="margin-top:10px;">
        <thead>
            <tr>
                <th class="time">Horario</th>
                @foreach($dates as $d)
                    <th class="dateHead">
                        {{ $d->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $tpl)
                <tr>
                    <td class="time">
                        {{ substr((string) $tpl->start_time, 0, 5) }}
                        <br>
                        a
                        <br>
                        {{ substr((string) $tpl->end_time, 0, 5) }}
                    </td>
                    @foreach($dates as $d)
                        @php
                            $key = $d->toDateString() . '|' . (int) $tpl->sort_order;
                            $shift = $shiftMap->get($key);
                            $names = $shift ? $shift->assignments->map(fn($a) => trim((string)($a->firefighter?->nombres ?? '') . ' ' . (string)($a->firefighter?->apellido_paterno ?? '')))->filter()->values() : collect();
                        @endphp
                        <td>
                            <div class="names">
                                @if($names->isEmpty())
                                    -
                                @else
                                    {!! nl2br(e($names->implode("\n"))) !!}
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
