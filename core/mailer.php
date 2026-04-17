<?php

/**
 * Mailer - Raw SMTP client using stream_socket_client.
 */
function mail_send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $config = require BASE_PATH . '/config/mail.php';

    $host = (string) ($config['host'] ?? 'smtp.gmail.com');
    $port = (int) ($config['port'] ?? 465);
    $user = (string) ($config['username'] ?? '');
    $pass = (string) ($config['password'] ?? '');
    $encryption = strtolower((string) ($config['encryption'] ?? 'ssl'));
    $fromAddress = trim((string) ($config['from_address'] ?? ''));
    $fromName = (string) ($config['from_name'] ?? 'ResQnet');

    if ($user === '' || $pass === '') {
        return false;
    }

    if ($fromAddress === '') {
        $fromAddress = $user;
    }

    try {
        $prefix = ($encryption === 'ssl' || $port === 465) ? 'ssl://' : 'tcp://';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $socket = @stream_socket_client(
            $prefix . $host . ':' . $port,
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            return false;
        }

        $readResponse = static function ($handle): string {
            $response = '';
            while ($line = @fgets($handle, 512)) {
                $response .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $response;
        };

        $sendCommand = static function ($handle, string $command) use ($readResponse): string {
            fwrite($handle, $command . "\r\n");
            return $readResponse($handle);
        };

        $greeting = $readResponse($socket);
        if (!str_starts_with($greeting, '220')) {
            fclose($socket);
            return false;
        }

        $ehlo = $sendCommand($socket, 'EHLO localhost');
        if (!str_starts_with($ehlo, '250')) {
            fclose($socket);
            return false;
        }

        if ($encryption === 'tls' && $port !== 465) {
            $startTls = $sendCommand($socket, 'STARTTLS');
            if (!str_starts_with($startTls, '220')) {
                fclose($socket);
                return false;
            }

            $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($crypto !== true) {
                fclose($socket);
                return false;
            }

            $ehlo = $sendCommand($socket, 'EHLO localhost');
            if (!str_starts_with($ehlo, '250')) {
                fclose($socket);
                return false;
            }
        }

        $auth = $sendCommand($socket, 'AUTH LOGIN');
        if (!str_starts_with($auth, '334')) {
            fclose($socket);
            return false;
        }

        $userResp = $sendCommand($socket, base64_encode($user));
        if (!str_starts_with($userResp, '334')) {
            fclose($socket);
            return false;
        }

        $passResp = $sendCommand($socket, base64_encode($pass));
        if (!str_starts_with($passResp, '235')) {
            fclose($socket);
            return false;
        }

        $mailFrom = $sendCommand($socket, 'MAIL FROM:<' . $fromAddress . '>');
        if (!str_starts_with($mailFrom, '250')) {
            fclose($socket);
            return false;
        }

        $rcptTo = $sendCommand($socket, 'RCPT TO:<' . $to . '>');
        if (!(str_starts_with($rcptTo, '250') || str_starts_with($rcptTo, '251'))) {
            fclose($socket);
            return false;
        }

        $dataReady = $sendCommand($socket, 'DATA');
        if (!str_starts_with($dataReady, '354')) {
            fclose($socket);
            return false;
        }

        $boundary = '----=_Part_' . bin2hex(random_bytes(8));
        $altBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

        $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $fromNameEncoded = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

        $message = '';
        $message .= 'From: ' . $fromNameEncoded . ' <' . $fromAddress . ">\r\n";
        $message .= 'To: <' . $to . ">\r\n";
        $message .= 'Subject: ' . $subjectEncoded . "\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . "\"\r\n";
        $message .= "\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $altBody . "\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $htmlBody . "\r\n";
        $message .= '--' . $boundary . "--\r\n";

        fwrite($socket, $message . "\r\n.\r\n");
        $dataResp = $readResponse($socket);

        $sendCommand($socket, 'QUIT');
        fclose($socket);

        return str_starts_with($dataResp, '250');
    } catch (Throwable) {
        return false;
    }
}
