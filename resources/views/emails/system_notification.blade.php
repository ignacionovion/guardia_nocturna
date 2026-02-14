<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#0f172a;color:#fff;border-radius:16px;padding:18px 20px;">
            <div style="font-size:12px;letter-spacing:0.12em;text-transform:uppercase;font-weight:800;color:#cbd5e1;">{{ config('app.name', 'AppGuardia') }}</div>
            <div style="font-size:18px;font-weight:800;margin-top:6px;">{{ $subject ?? '' }}</div>
        </div>

        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;margin-top:14px;">
            @foreach(($lines ?? []) as $line)
                <div style="font-size:14px;line-height:1.5;margin:0 0 10px 0;">{!! e($line) !!}</div>
            @endforeach
        </div>

        <div style="font-size:11px;color:#64748b;margin-top:14px;text-align:center;">
            {{ config('app.name', 'AppGuardia') }} · {{ now()->format('d-m-Y H:i') }}
        </div>
    </div>
</body>
</html>
