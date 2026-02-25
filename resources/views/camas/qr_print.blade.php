<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Camas - QR Cama #{{ $bed->number }}</title>
    <style>
        @page { margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif; color: #0f172a; background: #f8fafc; }
        .wrap { max-width: 900px; margin: 0 auto; }
        .card { border: 2px solid #1e293b; border-radius: 20px; padding: 22px; background: #fff; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1); }
        .top { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 20px; }
        .kicker { font-size: 12px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: #64748b; }
        .title { font-size: 26px; font-weight: 900; margin-top: 4px; line-height: 1.2; color: #0f172a; }
        .subtitle { font-size: 14px; margin-top: 8px; color: #475569; font-weight: 500; }
        .grid { display: grid; grid-template-columns: 1fr 420px; gap: 24px; align-items: center; }
        .bedbadge { display: inline-block; padding: 8px 14px; border: 2px solid #1e293b; border-radius: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; font-size: 13px; background: #1e293b; color: white; }
        
        {{-- QR Box Styling --}}
        .qr-wrapper { position: relative; display: flex; justify-content: center; }
        .qrbox { position: relative; border: 2px solid #1e293b; border-radius: 24px; padding: 20px; display: flex; align-items: center; justify-content: center; background: #fff; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.15); }
        .qrbox svg { max-width: 100%; height: auto; max-height: 320px; }
        
        {{-- Logo Overlay --}}
        .logo-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; }
        .logo-container { width: 80px; height: 80px; background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); padding: 6px; display: flex; align-items: center; justify-content: center; border: 2px solid #e2e8f0; }
        .logo-container img { width: 60px; height: 60px; object-fit: contain; }
        
        {{-- QR Badge --}}
        .qr-badge { position: absolute; top: -12px; right: -12px; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); background: #1e293b; color: white; font-size: 14px; }
        
        .steps { border: 2px dashed #94a3b8; border-radius: 16px; padding: 18px; background: #f8fafc; }
        .steps h3 { margin: 0; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #1e293b; }
        .steps ol { margin: 12px 0 0 18px; padding: 0; }
        .steps li { margin: 8px 0; font-size: 13px; color: #475569; }
        .url { margin-top: 14px; font-size: 11px; color: #64748b; word-break: break-all; font-family: monospace; background: #e2e8f0; padding: 6px 10px; border-radius: 6px; }

        .screen-only { margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 12px 20px; border-radius: 12px; border: 2px solid #0f172a; background: #0f172a; color: #fff; font-weight: 900; font-size: 12px; letter-spacing: 0.1em; text-transform: uppercase; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn.secondary { background: #fff; color: #0f172a; }

        @media print {
            body { background: #fff; }
            .screen-only { display: none; }
            .card { box-shadow: none; border-width: 2px; }
            a { color: inherit; text-decoration: none; }
        }

        @media (max-width: 768px) {
            .wrap { padding: 12px; }
            .card { padding: 16px; border-width: 2px; }
            .top { flex-direction: column; align-items: flex-start; gap: 16px; }
            .title { font-size: 22px; }
            .subtitle { font-size: 13px; }
            .grid { grid-template-columns: 1fr; gap: 20px; }
            .qrbox { padding: 14px; }
            .qrbox svg { max-height: 260px; }
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

            <div class="qr-wrapper">
                <div class="qrbox">
                    {!! $qrSvg !!}
                    
                    {{-- Logo Overlay --}}
                    <div class="logo-overlay">
                        <div class="logo-container">
                            <img src="{{ asset('brand/Logo png Alta Def.png') }}" alt="Logo">
                        </div>
                    </div>
                    
                    <div class="qr-badge">⌗</div>
                </div>
            </div>
        </div>

        <div class="screen-only">
            <a class="btn" href="#" onclick="window.print(); return false;">🖨️ Imprimir</a>
            <a class="btn secondary" href="{{ route('camas') }}">← Volver</a>
        </div>
    </div>
</div>
</body>
</html>
