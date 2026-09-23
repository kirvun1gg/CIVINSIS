<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0c1612; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #131f1a; padding: 0; border-radius: 16px; overflow: hidden; border: 1px solid rgba(54,192,161,.25); }
        .header { background: linear-gradient(135deg, #36c0a1, #ef7e22); padding: 28px 30px; text-align: center; }
        .header .brand { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: .5px; }
        .body { padding: 32px 30px; color: #e8f4f0; line-height: 1.65; }
        .body p { margin: 0 0 14px; font-size: 15px; }
        .button-wrap { text-align: center; margin: 26px 0; }
        .button {
            display: inline-block; padding: 14px 34px;
            background: linear-gradient(135deg, #36c0a1, #ef7e22);
            color: #ffffff !important; text-decoration: none;
            border-radius: 12px; font-weight: 700; font-size: 15px;
        }
        .fallback-link {
            margin-top: 6px; padding: 12px 16px;
            background: rgba(54,192,161,.08); border: 1px dashed rgba(54,192,161,.3);
            border-radius: 10px; font-size: 12px; color: #8cb4a5;
            word-break: break-all;
        }
        .fallback-link a { color: #36c0a1; }
        .notice {
            margin-top: 18px; padding: 12px 16px;
            background: rgba(239,126,34,.08); border-left: 3px solid #ef7e22;
            border-radius: 8px; font-size: 13px; color: #d8c2a8;
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
            <p>Hola <strong>{{ $user ? $user->nombre : 'Usuario' }}</strong>,</p>
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Si fuiste tú, haz clic en el siguiente botón para crear una nueva:</p>
            <div class="button-wrap">
                <a href="{{ $resetLink }}" class="button">Restablecer contraseña</a>
            </div>
            <p style="font-size:13px;color:#8cb4a5">¿El botón no funciona? Copia y pega este enlace en tu navegador:</p>
            <div class="fallback-link"><a href="{{ $resetLink }}">{{ $resetLink }}</a></div>
            <div class="notice">
                Este enlace vence pronto por seguridad. Si no solicitaste este cambio, ignora este correo — tu contraseña seguirá siendo la misma.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CIVINSIS. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
