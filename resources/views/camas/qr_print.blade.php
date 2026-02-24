<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Camas - QR Cama #{{ $bed->number }}</title>
    <style>
        @page { margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif; color: #0f172a; }
        .wrap { max-width: 900px; margin: 0 auto; }
        .card { border: 2px solid #0f172a; border-radius: 18px; padding: 18px; }
        .top { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .kicker { font-size: 11px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #334155; }
        .title { font-size: 22px; font-weight: 900; margin-top: 4px; line-height: 1.2; }
        .subtitle { font-size: 13px; margin-top: 6px; color: #334155; }
        .grid { display: grid; grid-template-columns: 1fr 400px; gap: 18px; margin-top: 18px; align-items: center; }
        .qrbox { border: 2px solid #e2e8f0; border-radius: 18px; padding: 14px; display: flex; align-items: center; justify-content: center; background: #fff; }
        .qrbox svg { max-width: 100%; height: auto; }
        .steps { border: 2px dashed #cbd5e1; border-radius: 18px; padding: 14px; }
        .steps h3 { margin: 0; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.14em; color: #0f172a; }
        .steps ol { margin: 10px 0 0 18px; padding: 0; }
        .steps li { margin: 8px 0; font-size: 13px; }
        .url { margin-top: 12px; font-size: 11px; color: #334155; word-break: break-all; }
        .bedbadge { display:inline-block; padding: 6px 10px; border: 2px solid #0f172a; border-radius: 12px; font-weight: 900; letter-spacing: 0.12em; text-transform: uppercase; }

        .screen-only { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 12px; border: 1px solid #0f172a; background: #0f172a; color: #fff; font-weight: 900; font-size: 12px; letter-spacing: 0.14em; text-transform: uppercase; text-decoration: none; }
        .btn.secondary { background: #fff; color: #0f172a; }

        @media print {
            .screen-only { display: none; }
            a { color: inherit; text-decoration: none; }
        }

        @media (max-width: 768px) {
            .wrap { padding: 10px; }
            .card { padding: 14px; border-width: 1px; }
            .top { flex-direction: column; align-items: flex-start; gap: 12px; }
            .title { font-size: 18px; }
            .subtitle { font-size: 12px; }
            .grid { grid-template-columns: 1fr; gap: 14px; }
            .qrbox { order: -1; padding: 10px; }
            .qrbox svg { max-height: 280px; }
            .steps { padding: 12px; }
            .steps li { font-size: 12px; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="top">
            <div>
                <div class="kicker">Camas</div>
                <div class="title">Asignación de cama</div>
                <div class="subtitle">Escanea el QR y sigue el flujo: RUT → Confirmar asignación.</div>
            </div>
            <div class="bedbadge">CAMA #{{ $bed->number }}</div>
        </div>

        <div class="grid">
            <div>
                <div class="steps">
                    <h3>Pasos</h3>
                    <ol>
                        <li>Escanea el código QR.</li>
                        <li>Ingresa tu RUT (ej: 11222333-4).</li>
                        <li>Confirma la asignación.</li>
                    </ol>
                    <div class="url">{{ $url }}</div>
                </div>
            </div>

            <div class="qrbox">
                {!! $qrSvg !!}
            </div>
        </div>

        <div class="screen-only">
            <a class="btn" href="#" onclick="window.print(); return false;">Imprimir</a>
            <a class="btn secondary" href="{{ route('camas') }}">Volver</a>
        </div>
    </div>
</div>
</body>
</html>
