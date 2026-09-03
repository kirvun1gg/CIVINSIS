<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación - {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 50px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #eaeaea; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #333; margin: 0; }
        .content { text-align: center; color: #555; line-height: 1.6; }
        .code { font-size: 32px; font-weight: bold; color: #2d89ef; margin: 20px 0; padding: 15px; border: 2px dashed #2d89ef; display: inline-block; border-radius: 5px; letter-spacing: 5px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verificación de Seguridad</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $user->nombre }}</strong>,</p>
            <p>Has iniciado sesión con tu cuenta de Google. Para completar el acceso, ingresa el siguiente código de verificación en la pantalla de inicio de sesión:</p>
            <div class="code">{{ $code }}</div>
            <p>Si no fuiste tú quien solicitó este acceso, puedes ignorar este mensaje.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>

