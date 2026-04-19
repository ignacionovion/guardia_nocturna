<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta operativa</title>
</head>
<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    @php
        $eventLabel = match ($eventType) {
            'escalated' => 'Escalación a crítico',
            'resolved' => 'Incidente resuelto',
            default => 'Nueva alerta',
        };
        $sevColor = $alert->severity === 'critical' ? '#b91c1c' : '#b45309';
    @endphp

    <p style="margin: 0 0 8px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">GuardiAPP · Panel central</p>
    <h1 style="margin: 0 0 16px; font-size: 20px;">{{ $eventLabel }}</h1>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #64748b; width: 38%;">Entorno</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;"><strong>{{ $appEnv }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;">Severidad</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: {{ $sevColor }};">{{ strtoupper($alert->severity) }}</strong>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;">Área</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $alert->source }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;">Asunto</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $alert->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #64748b; vertical-align: top;">Detalle</td>
            <td style="padding: 8px 0;">{{ $alert->message }}</td>
        </tr>
    </table>

    <p style="margin: 0 0 20px; font-size: 13px; color: #64748b;">
        Notificación generada: <strong>{{ $triggeredAt }}</strong> ({{ config('app.timezone') }})
    </p>

    <p style="margin: 0;">
        <a href="{{ $panelUrl }}" style="display: inline-block; background: #0f172a; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600;">Abrir panel central</a>
    </p>

    <p style="margin: 24px 0 0; font-size: 11px; color: #94a3b8;">
        Este mensaje se envía solo ante incidentes nuevos, escalaciones o resoluciones; no hay recordatorios repetidos mientras el estado no cambie.
    </p>
</body>
</html>
