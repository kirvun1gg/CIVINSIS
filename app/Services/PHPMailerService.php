<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHPMailerService
{
    /**
     * Envía un correo utilizando PHPMailer.
     *
     * @param string $to Dirección de correo de destino.
     * @param string $subject Asunto del correo.
     * @param string $body Cuerpo del correo (HTML).
     * @return bool True si se envió correctamente, false si falló.
     */
    public function sendEmail($to, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
            $mail->Port       = env('MAIL_PORT', 587);
            
            // Configurar codificación a UTF-8 para caracteres especiales
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Remitente y destinatario
            $mail->setFrom(env('MAIL_FROM_ADDRESS', 'hello@example.com'), env('MAIL_FROM_NAME', config('app.name')));
            $mail->addAddress($to);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            \Log::error("Error enviando correo con PHPMailer: {$mail->ErrorInfo}");
            return false;
        }
    }
}

