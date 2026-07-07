<?php
require_once __DIR__ . "/mailerConfig.php";
require_once __DIR__ . "/../vendor/PHPMailer/Exception.php";
require_once __DIR__ . "/../vendor/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../vendor/PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmail(string $destinatario, string $assunto, string $corpoHtml): array
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpoHtml;
        $mail->AltBody = strip_tags($corpoHtml);

        $mail->send();
        return ['ok' => true, 'erro' => null];
    } catch (Exception $e) {
        return ['ok' => false, 'erro' => $mail->ErrorInfo];
    }
}
