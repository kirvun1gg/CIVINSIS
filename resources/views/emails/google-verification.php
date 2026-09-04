<?php
// google-verification.php - Correo con el código de verificación al iniciar
// sesión con Google (GoogleAuthController::callback). CSS en <style> (no
// inline) porque así ya estaba y los clientes de correo modernos lo soportan.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('civinsis.auth.verify_titulo') ?> - <?= config('app.name') ?></title>
    <style>
        body { font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0c1612; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #131f1a; padding: 0; border-radius: 16px; overflow: hidden; border: 1px solid rgba(54,192,161,.25); }
        .header { background: linear-gradient(135deg, #36c0a1, #ef7e22); padding: 28px 30px; text-align: center; }
        .header .brand { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: .5px; }
        .body { padding: 32px 30px; color: #e8f4f0; line-height: 1.65; }
        .body p { margin: 0 0 14px; font-size: 15px; }
        .code-box {
            text-align: center; margin: 26px 0; padding: 18px;
            background: rgba(54,192,161,.08); border: 2px dashed #36c0a1; border-radius: 12px;
        }
        .code {
            font-size: 34px; font-weight: 800; color: #36c0a1;
            letter-spacing: 10px; font-family: 'Courier New', monospace;
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
            <p>Hola <strong><?= htmlspecialchars($user->nombre) ?></strong>,</p>
            <p>Iniciaste sesión con tu cuenta de Google. Para completar el acceso, ingresa el siguiente código de verificación en la pantalla que te está esperando:</p>
            <div class="code-box">
                <span class="code"><?= htmlspecialchars($code) ?></span>
            </div>
            <p>Si no fuiste tú quien intentó acceder a tu cuenta, puedes ignorar este mensaje con tranquilidad.</p>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> CIVINSIS. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
