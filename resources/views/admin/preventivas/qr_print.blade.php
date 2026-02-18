<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guardias Preventivas - QR</title>
    <style>
        @page { margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif; color: #0f172a; }
        .wrap { max-width: 900px; margin: 0 auto; }
        .card { border: 2px solid #0f172a; border-radius: 18px; padding: 18px; }
        .top { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { height: 86px; width: auto; }
        .kicker { font-size: 12px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #334155; }
        .title { font-size: 28px; font-weight: 900; margin-top: 4px; }
        .subtitle { font-size: 14px; margin-top: 6px; color: #334155; }
        .grid { display: grid; grid-template-columns: 1fr 520px; gap: 18px; margin-top: 18px; align-items: center; }
        .qrbox { border: 2px solid #e2e8f0; border-radius: 18px; padding: 18px; display: flex; align-items: center; justify-content: center; background: #fff; }
        .steps { border: 2px dashed #cbd5e1; border-radius: 18px; padding: 14px; }
        .steps h3 { margin: 0; font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.14em; color: #0f172a; }
        .steps ol { margin: 10px 0 0 18px; padding: 0; }
        .steps li { margin: 8px 0; font-size: 14px; }
        .url { margin-top: 12px; font-size: 12px; color: #334155; word-break: break-all; }

        .screen-only { margin-top: 14px; display: flex; gap: 10px; }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 12px; border: 1px solid #0f172a; background: #0f172a; color: #fff; font-weight: 900; font-size: 12px; letter-spacing: 0.14em; text-transform: uppercase; text-decoration: none; }
        .btn.secondary { background: #fff; color: #0f172a; }

        @media print {
            .screen-only { display: none; }
            a { color: inherit; text-decoration: none; }
        }

        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="top">
            <div>
                <div class="kicker">Guardias Preventivas</div>
                <div class="title">{{ $event->title }}</div>
                <div class="subtitle">Escanea el QR para confirmar tu asistencia al turno.</div>
            </div>
            <div class="brand">
                @if(file_exists(public_path('brand/guardiappcheck.png')))
                    <img src="{{ asset('brand/guardiappcheck.png') }}" alt="GuardiaAPP">
                @endif
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="steps">
                    <h3>Pasos</h3>
                    <ol>
                        <li>Escanea el código QR.</li>
                        <li>Ingresa tu código de bombero.</li>
                        <li>Confirma tu asistencia.</li>
                    </ol>
                    <div class="url">{{ $url }}</div>
                </div>
            </div>

            <div class="qrbox">
                {!! $svg !!}
            </div>
        </div>

        <div class="screen-only">
            <a class="btn" href="#" onclick="window.print(); return false;">Imprimir</a>
            <a class="btn secondary" href="{{ route('admin.preventivas.show', $event) }}">Volver</a>
        </div>
    </div>
</div>
</body>
</html>
