<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta a tu mensaje - {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0c1612; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #131f1a; padding: 0; border-radius: 16px; overflow: hidden; border: 1px solid rgba(54,192,161,.25); }
        .header { background: linear-gradient(135deg, #36c0a1, #ef7e22); padding: 28px 30px; text-align: center; }
        .header .brand { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: .5px; }
        .body { padding: 32px 30px; color: #e8f4f0; line-height: 1.65; }
        .body p { margin: 0 0 14px; font-size: 15px; }
        .original-box {
            margin: 8px 0 20px; padding: 14px 16px;
            background: rgba(255,255,255,.04); border-left: 3px solid #6a8a80;
            border-radius: 8px; font-size: 13px; color: #8cb4a5; font-style: italic;
        }
        .respuesta-box {
            margin: 8px 0 22px; padding: 16px 18px;
            background: rgba(54,192,161,.08); border: 1px solid rgba(54,192,161,.3);
            border-radius: 12px; font-size: 15px; color: #e8f4f0; white-space: pre-line;
        }
        .button-wrap { text-align: center; margin: 22px 0 6px; }
        .button {
            display: inline-block; padding: 13px 30px;
            background: linear-gradient(135deg, #36c0a1, #ef7e22);
            color: #ffffff !important; text-decoration: none;
            border-radius: 12px; font-weight: 700; font-size: 14px;
        }
        .footer { text-align: center; padding: 18px 30px 26px; font-size: 12px; color: #6a8a80; }
        a { color: #36c0a1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="brand">CIVINSIS</span>
        </div>
        <div class="body">
            <p>Hola <strong>{{ $nombre }}</strong>,</p>
            <p>Un miembro del equipo de CIVINSIS respondió al mensaje que enviaste con el asunto «{{ $asunto }}»:</p>

            <div class="original-box">"{{ $mensajeOriginal }}"</div>

            <p style="margin-bottom:6px"><strong>Respuesta:</strong></p>
            <div class="respuesta-box">{{ $respuesta }}</div>

            <div class="button-wrap">
                <a href="{{ url('/contacto.php') }}" class="button">Ir a CIVINSIS</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CIVINSIS. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
