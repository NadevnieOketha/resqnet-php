<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send an email using configured SMTP.
 */
function mail_send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $config = require BASE_PATH . '/config/mail.php';

    if ($config['username'] === '' || $config['password'] === '') {
        return false;
    }

    if (!class_exists(PHPMailer::class)) {
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) $config['host'];
        $mail->Port = (int) $config['port'];
        $mail->SMTPAuth = true;
        $mail->Username = (string) $config['username'];
        $mail->Password = (string) $config['password'];

        $encryption = strtolower((string) $config['encryption']);
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $fromAddress = (string) $config['from_address'];
        $fromName = (string) $config['from_name'];
        if ($fromAddress === '') {
            $fromAddress = (string) $config['username'];
        }

        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody === '' ? strip_tags($htmlBody) : $textBody;
        $mail->send();

        return true;
    } catch (Exception) {
        return false;
    }
}
