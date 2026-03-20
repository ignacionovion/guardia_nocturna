<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR - {{ $bed->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f8f9fa;
            padding: 20px;
        }
        .container {
            background: white;
            border: 3px solid #1e293b;
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .qr-wrapper {
            background: white;
            padding: 20px;
            border: 2px solid #e7eef5;
            border-radius: 12px;
            display: inline-block;
            margin: 20px 0;
        }
        h1 {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .location {
            font-size: 16px;
            color: #475569;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .badges {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-gender {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-status {
            background: #dcfce7;
            color: #166534;
        }
        .instruction {
            font-size: 14px;
            color: #64748b;
            margin-top: 20px;
            padding: 16px;
            background: #f1f5f9;
            border-radius: 8px;
            font-weight: 500;
        }
        @media print {
            body {
                background: white;
            }
            .container {
                border: 2px solid #1e293b;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $bed->name }}</h1>
        
        @if($bed->location)
        <div class="location">
            <i>📍</i> {{ $bed->location }}
        </div>
        @endif

        <div class="badges">
            <span class="badge badge-gender">{{ $bed->gender_label }}</span>
            <span class="badge badge-status">{{ $bed->status_label }}</span>
        </div>

        <div class="qr-wrapper">
            {!! QrCode::size(280)->generate(route('qr.bed.show', $bed->qr_token)) !!}
        </div>

        <div class="instruction">
            <strong>Escanea para gestionar esta cama</strong><br>
            Accede a la información y gestión de esta cama desde tu dispositivo móvil
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
