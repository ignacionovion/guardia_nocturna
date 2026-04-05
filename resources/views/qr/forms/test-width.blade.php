<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Width</title>
    @vite(['resources/css/app.css'])
</head>
<body style="margin:0; background:#f8fafc;">
    <div style="min-height:100vh; display:flex; justify-content:center; padding:20px;">
        <div id="test-box" style="width:100%; max-width:420px; background:#fff; border:4px solid red; border-radius:20px; padding:24px;">
            <h1>TEST WIDTH</h1>
            <p>Si esta caja también se ve angosta, el problema es CSS global.</p>
            <input style="width:100%; height:48px;" placeholder="RUT">
            <button style="width:100%; height:48px; margin-top:12px;">Continuar</button>
        </div>
    </div>

    <script>
    const el = document.getElementById('test-box');
    const info = document.createElement('div');
    info.style.position = 'fixed';
    info.style.bottom = '10px';
    info.style.left = '10px';
    info.style.background = '#111827';
    info.style.color = '#fff';
    info.style.padding = '10px';
    info.style.borderRadius = '10px';
    info.style.zIndex = '99999';
    info.textContent = 'test-box width=' + getComputedStyle(el).width;
    document.body.appendChild(info);
    </script>
</body>
</html>
