<?php

/**
 * Mailer - Raw SMTP client using stream_socket_client.
 */
function mail_set_last_error(string $message): void
{
    $normalized = trim(preg_replace('/\s+/', ' ', $message) ?? $message);
    $GLOBALS['_mail_last_error'] = $normalized;

    $logDir = BASE_PATH . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    $logLine = '[' . date('Y-m-d H:i:s') . '] ' . ($normalized === '' ? 'mail ok' : $normalized) . PHP_EOL;
    @file_put_contents($logDir . '/mail.log', $logLine, FILE_APPEND);
}

function mail_last_error(): string
{
    return (string) ($GLOBALS['_mail_last_error'] ?? '');
}

function mail_send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    mail_set_last_error('');

    $config = require BASE_PATH . '/config/mail.php';

    $host = (string) ($config['host'] ?? 'smtp.gmail.com');
    $port = (int) ($config['port'] ?? 465);
    $user = (string) ($config['username'] ?? '');
    $pass = trim((string) ($config['password'] ?? ''));
    $encryption = strtolower((string) ($config['encryption'] ?? 'ssl'));
    $fromAddress = trim((string) ($config['from_address'] ?? ''));
    $fromName = (string) ($config['from_name'] ?? 'ResQnet');

    if (str_contains(strtolower($host), 'gmail.com')) {
        // Users often paste Gmail app passwords with spaces; Gmail expects contiguous value.
        $pass = str_replace(' ', '', $pass);
    }

    if ($user === '' || $pass === '') {
        mail_set_last_error('SMTP credentials are missing.');
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
            mail_set_last_error('SMTP connection failed: ' . $errstr . ' (errno ' . (string) $errno . ').');
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
            mail_set_last_error('SMTP greeting failed: ' . trim($greeting));
            fclose($socket);
            return false;
        }

        $ehlo = $sendCommand($socket, 'EHLO localhost');
        if (!str_starts_with($ehlo, '250')) {
            mail_set_last_error('SMTP EHLO failed: ' . trim($ehlo));
            fclose($socket);
            return false;
        }

        if ($encryption === 'tls' && $port !== 465) {
            $startTls = $sendCommand($socket, 'STARTTLS');
            if (!str_starts_with($startTls, '220')) {
                mail_set_last_error('SMTP STARTTLS failed: ' . trim($startTls));
                fclose($socket);
                return false;
            }

            $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($crypto !== true) {
                mail_set_last_error('SMTP TLS negotiation failed.');
                fclose($socket);
                return false;
            }

            $ehlo = $sendCommand($socket, 'EHLO localhost');
            if (!str_starts_with($ehlo, '250')) {
                mail_set_last_error('SMTP EHLO after TLS failed: ' . trim($ehlo));
                fclose($socket);
                return false;
            }
        }

        $auth = $sendCommand($socket, 'AUTH LOGIN');
        if (!str_starts_with($auth, '334')) {
            mail_set_last_error('SMTP AUTH failed: ' . trim($auth));
            fclose($socket);
            return false;
        }

        $userResp = $sendCommand($socket, base64_encode($user));
        if (!str_starts_with($userResp, '334')) {
            mail_set_last_error('SMTP username rejected: ' . trim($userResp));
            fclose($socket);
            return false;
        }

        $passResp = $sendCommand($socket, base64_encode($pass));
        if (!str_starts_with($passResp, '235')) {
            mail_set_last_error('SMTP password rejected: ' . trim($passResp));
            fclose($socket);
            return false;
        }

        $mailFrom = $sendCommand($socket, 'MAIL FROM:<' . $fromAddress . '>');
        if (!str_starts_with($mailFrom, '250')) {
            mail_set_last_error('SMTP MAIL FROM rejected: ' . trim($mailFrom));
            fclose($socket);
            return false;
        }

        $rcptTo = $sendCommand($socket, 'RCPT TO:<' . $to . '>');
        if (!(str_starts_with($rcptTo, '250') || str_starts_with($rcptTo, '251'))) {
            mail_set_last_error('SMTP RCPT TO rejected: ' . trim($rcptTo));
            fclose($socket);
            return false;
        }

        $dataReady = $sendCommand($socket, 'DATA');
        if (!str_starts_with($dataReady, '354')) {
            mail_set_last_error('SMTP DATA rejected: ' . trim($dataReady));
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

        if (!str_starts_with($dataResp, '250')) {
            mail_set_last_error('SMTP message rejected: ' . trim($dataResp));
            return false;
        }

        mail_set_last_error('');
        return true;
    } catch (Throwable $e) {
        mail_set_last_error('SMTP client exception: ' . $e->getMessage());
        return false;
    }
}
