<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#334155;line-height:1.6;">
    <div style="max-width:600px;margin:0 auto;padding:20px;">
        
        {{-- Header con Logo --}}
        <div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);border-radius:12px 12px 0 0;padding:24px 28px;text-align:center;border-bottom:4px solid #dc2626;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center">
                        <div style="display:inline-flex;align-items:center;gap:12px;">
                            <div style="width:44px;height:44px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;">🚒</div>
                            <div style="text-align:left;">
                                <div style="font-size:11px;letter-spacing:0.15em;text-transform:uppercase;font-weight:700;color:#94a3b8;">{{ config('app.name', 'GuardiAPP') }}</div>
                                <div style="font-size:18px;font-weight:800;color:#ffffff;margin-top:2px;">{{ $mailSubject ?? $subject ?? 'Notificación' }}</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Badge de tipo --}}
        @if(isset($notificationType))
        <div style="background:#ffffff;padding:0 28px;">
            <div style="padding:16px 0 0 0;">
                <span style="display:inline-block;background:{{
                    match($notificationType) {
                        'novedad', 'novelty' => '#dc2626',
                        'academy' => '#0891b2',
                        'beds', 'cama' => '#059669',
                        'cleaning', 'aseo' => '#7c3aed',
                        'asistencia' => '#ea580c',
                        default => '#475569'
                    }
                }};color:#ffffff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;padding:6px 14px;border-radius:20px;">
                    {{ match($notificationType) {
                        'novedad' => '🚨 NOVEDAD',
                        'novelty' => '🚨 NOVEDAD',
                        'academy' => '🎓 ACADEMIA',
                        'beds' => '🛏️ ASIGNACIÓN DE CAMA',
                        'cama' => '🛏️ ASIGNACIÓN DE CAMA',
                        'cleaning' => '🧹 ASIGNACIÓN DE ASEO',
                        'aseo' => '🧹 ASIGNACIÓN DE ASEO',
                        'asistencia' => '✓ ASISTENCIA',
                        default => '📋 NOTIFICACIÓN'
                    } }}
                </span>
            </div>
        </div>
        @endif

        {{-- Origen/Contexto --}}
        @if(isset($sourceLabel) && $sourceLabel)
        <div style="background:#ffffff;padding:0 28px;">
            <div style="background:#f8fafc;border-left:3px solid #64748b;padding:12px 16px;margin:16px 0 0 0;border-radius:0 8px 8px 0;">
                <p style="margin:0;font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">📍 Origen</p>
                <p style="margin:4px 0 0 0;font-size:14px;color:#334155;font-weight:500;">{{ $sourceLabel }}</p>
            </div>
        </div>
        @endif

        {{-- Contenido principal --}}
        <div style="background:#ffffff;padding:24px 28px;border-radius:0 0 12px 12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
            
            {{-- Líneas de contenido --}}
            @foreach(($lines ?? []) as $line)
                @php
                    $isLabel = str_contains($line, ':') && !str_starts_with($line, '•') && !str_starts_with($line, '-');
                    $isImportant = str_contains(strtolower($line), 'urgente') || str_contains(strtolower($line), 'importante');
                @endphp
                
                @if($isLabel)
                    <div style="margin:0 0 12px 0;padding-bottom:8px;border-bottom:1px solid #e2e8f0;">
                        <p style="margin:0;font-size:13px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;">
                            {{ explode(':', $line)[0] }}:
                        </p>
                        <p style="margin:4px 0 0 0;font-size:15px;color:#0f172a;font-weight:500;">
                            {!! e(trim(explode(':', $line, 2)[1] ?? '')) !!}
                        </p>
                    </div>
                @elseif($isImportant)
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin:0 0 12px 0;">
                        <p style="margin:0;font-size:14px;color:#dc2626;font-weight:600;">⚠️ {!! e($line) !!}</p>
                    </div>
                @else
                    <div style="margin:0 0 10px 0;">
                        <p style="margin:0;font-size:14px;color:#475569;line-height:1.6;">{!! e($line) !!}</p>
                    </div>
                @endif
            @endforeach

            {{-- Información del remitente --}}
            @if(isset($senderName) && $senderName)
            <div style="background:#f1f5f9;border-radius:8px;padding:16px;margin-top:20px;border:1px solid #e2e8f0;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="40" valign="middle">
                            <div style="width:36px;height:36px;background:#0f172a;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#ffffff;font-size:14px;font-weight:700;">
                                {{ strtoupper(substr($senderName, 0, 1)) }}
                            </div>
                        </td>
                        <td valign="middle" style="padding-left:12px;">
                            <p style="margin:0;font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Registrado por</p>
                            <p style="margin:2px 0 0 0;font-size:14px;color:#0f172a;font-weight:600;">{{ $senderName }}</p>
                            @if(isset($senderEmail) && $senderEmail)
                            <p style="margin:0;font-size:12px;color:#64748b;">{{ $senderEmail }}</p>
                            @endif
                            @if(isset($senderRole) && $senderRole)
                            <p style="margin:4px 0 0 0;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">{{ $senderRole }}</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            @endif

            {{-- Timestamp --}}
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0;">
                <p style="margin:0;font-size:12px;color:#94a3b8;text-align:center;">
                    🕐 {{ now()->format('d \d\e F \d\e Y, H:i') }} hrs
                </p>
            </div>
        </div>

        {{-- Footer fijo --}}
        <div style="margin-top:20px;padding:20px 28px;background:#ffffff;border-radius:12px;text-align:center;border:1px solid #e2e8f0;">
            <p style="margin:0 0 8px 0;font-size:13px;color:#64748b;font-style:italic;">
                "Equipo GuardiAPP agradece el no responder este correo."
            </p>
            <p style="margin:0;font-size:11px;color:#94a3b8;">
                © {{ date('Y') }} {{ config('app.name', 'GuardiAPP') }} · Sistema de Gestión de Guardias
            </p>
        </div>

        {{-- Nota técnica --}}
        <div style="margin-top:12px;text-align:center;">
            <p style="margin:0;font-size:10px;color:#cbd5e1;">
                Este es un correo automático generado por el sistema
            </p>
        </div>
    </div>
</body>
</html>
