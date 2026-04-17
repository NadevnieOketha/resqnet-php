# Remove Third-Party Libraries — Implementation Plan

## Goal

Remove the two Composer dependencies (`vlucas/phpdotenv` and `phpmailer/phpmailer`) and replace them with vanilla PHP equivalents, so the project has **zero third-party backend libraries** while keeping all features working for the demo.

---

## Impact Summary

| Item | Current | After |
|---|---|---|
| Composer packages | 2 (`phpdotenv`, `phpmailer`) | 0 |
| `env()` function | Works via phpdotenv loading `$_ENV` | Works via custom `.env` parser loading `$_ENV` — **same behaviour** |
| `mail_send()` function | Uses PHPMailer class | Uses PHP `stream_socket_client()` raw SMTP — **same function signature** |
| Calling code changes | — | **None.** All 7 call sites in auth, donations, disaster_reports keep working unchanged |

---

## Fix 1: Replace phpdotenv

### What it does today
- `bootstrap.php` line 14: `require vendor/autoload.php` (loads phpdotenv + phpmailer classes)
- `bootstrap.php` line 17–18: `Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad()` reads `.env` and populates `$_ENV` and `putenv()`

### What to change

#### [MODIFY] [bootstrap.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/core/bootstrap.php)

Replace lines 13–18:

```diff
-// Composer autoloader (loads helpers.php via "files" autoload)
-require BASE_PATH . '/vendor/autoload.php';
-
-// Load .env
-$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
-$dotenv->safeLoad();
+// Load core helpers
+require BASE_PATH . '/core/helpers.php';
+
+// Load .env (vanilla parser — no third-party library)
+$envFile = BASE_PATH . '/.env';
+if (is_file($envFile)) {
+    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
+    foreach ($lines as $line) {
+        $line = trim($line);
+        if ($line === '' || $line[0] === '#') continue;
+        if (strpos($line, '=') === false) continue;
+        [$key, $val] = explode('=', $line, 2);
+        $key = trim($key);
+        $val = trim($val);
+        // Strip surrounding quotes
+        if (strlen($val) >= 2 && ($val[0] === '"' || $val[0] === "'") && $val[0] === $val[strlen($val) - 1]) {
+            $val = substr($val, 1, -1);
+        }
+        $_ENV[$key] = $val;
+        putenv("{$key}={$val}");
+    }
+}
```

> **Why this works:** The `env()` helper in `core/helpers.php` reads from `$_ENV` and `getenv()`. Our custom parser populates both — identical behaviour to phpdotenv.

---

## Fix 2: Replace PHPMailer

### What it does today
- `core/mailer.php`: Uses `PHPMailer\PHPMailer\PHPMailer` class for SMTP email
- Called from **7 places**: auth (password reset, GN credentials, GN resend), donations (confirmation emails), disaster_reports (volunteer notifications)
- All call `mail_send($to, $subject, $htmlBody, $textBody)` — we keep this signature

### What to change

#### [MODIFY] [mailer.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/core/mailer.php)

Replace the entire file with a raw SMTP socket client:

```php
<?php

/**
 * Mailer — Raw SMTP Client
 *
 * Sends email via direct SMTP socket connection.
 * No third-party libraries. Uses PHP's built-in stream_socket_client().
 */

function mail_send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $config = require BASE_PATH . '/config/mail.php';

    $host = (string) ($config['host'] ?? 'smtp.gmail.com');
    $port = (int) ($config['port'] ?? 465);
    $user = (string) ($config['username'] ?? '');
    $pass = (string) ($config['password'] ?? '');
    $encryption = strtolower((string) ($config['encryption'] ?? 'ssl'));
    $fromAddr = (string) ($config['from_address'] ?? $user);
    $fromName = (string) ($config['from_name'] ?? 'ResQnet');

    if ($user === '' || $pass === '') {
        return false;
    }

    try {
        // Determine connection prefix
        $prefix = ($encryption === 'ssl' || $port === 465) ? 'ssl://' : 'tcp://';

        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $socket = @stream_socket_client(
            $prefix . $host . ':' . $port,
            $errno, $errstr, 15,
            STREAM_CLIENT_CONNECT, $ctx
        );

        if (!$socket) {
            return false;
        }

        // Helper: read server response
        $read = function () use ($socket): string {
            $response = '';
            while ($line = @fgets($socket, 512)) {
                $response .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $response;
        };

        // Helper: send command and return response
        $send = function (string $cmd) use ($socket, $read): string {
            fwrite($socket, $cmd . "\r\n");
            return $read();
        };

        // SMTP conversation
        $read(); // 220 greeting

        $send('EHLO localhost');

        // STARTTLS for port 587
        if ($encryption === 'tls' && $port !== 465) {
            $send('STARTTLS');
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
            $send('EHLO localhost');
        }

        // Authenticate
        $send('AUTH LOGIN');
        $send(base64_encode($user));
        $authResp = $send(base64_encode($pass));

        if (!str_starts_with($authResp, '235')) {
            fclose($socket);
            return false;
        }

        // Envelope
        $send("MAIL FROM:<{$fromAddr}>");
        $send("RCPT TO:<{$to}>");
        $send('DATA');

        // Compose message
        $boundary = '----=_Part_' . bin2hex(random_bytes(8));
        $altBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

        $message  = "From: {$fromName} <{$fromAddr}>\r\n";
        $message .= "To: {$to}\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $message .= "\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $message .= $altBody . "\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $message .= $htmlBody . "\r\n";
        $message .= "--{$boundary}--\r\n";

        // Send body, terminated by CRLF.CRLF
        $dataResp = $send($message . "\r\n.");

        $send('QUIT');
        fclose($socket);

        return str_starts_with($dataResp, '250');

    } catch (\Throwable) {
        return false;
    }
}
```

> **Key points:**
> - Same `mail_send()` signature — zero changes needed in 7 calling locations
> - Supports both SSL (port 465) and STARTTLS (port 587)
> - Sends multipart/alternative (plain text + HTML) — same as PHPMailer was doing
> - Works on Windows with no mail server needed — connects directly to Gmail SMTP

---

## Fix 3: Update composer.json

#### [MODIFY] [composer.json](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/composer.json)

```diff
   "require": {
-    "php": ">=8.0",
-    "phpmailer/phpmailer": "^6.9",
-    "vlucas/phpdotenv": "^5.6"
+    "php": ">=8.0"
   },
   ...
   "autoload": {
-    "files": [
-      "core/helpers.php"
-    ]
+    "files": []
   }
```

> **Note:** `core/helpers.php` is now loaded explicitly via `require` in `bootstrap.php` instead of via Composer autoload, since we're removing the autoloader dependency.

---

## Fix 4: Update mail config default port

#### [MODIFY] [mail.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/config/mail.php)

```diff
-    'port' => (int) env('MAIL_PORT', 587),
-    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
+    'port' => (int) env('MAIL_PORT', 465),
+    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
```

> **Why:** SSL on port 465 is simpler and more reliable for the raw socket approach. Port 587 with STARTTLS also works but 465/SSL is the recommended default for Gmail.

---

## Post-Change Commands

```bash
# 1. Delete vendor directory and lock file
Remove-Item -Recurse -Force vendor, composer.lock

# 2. Reinstall (will install nothing since no deps)
composer install

# 3. Syntax-check all modified files
php -l core/bootstrap.php
php -l core/mailer.php
php -l config/mail.php

# 4. Start server and test
composer serve
```

---

## Verification Plan

| Test | Steps | Expected |
|---|---|---|
| **App starts** | Run `composer serve`, navigate to `http://localhost:8001` | Home page loads without errors |
| **Env loading** | Login with existing credentials | `.env` DB credentials loaded correctly; login works |
| **Password reset** | Click "Forgot Password", enter email, submit | Email received with reset link |
| **GN credential send** | DMC creates a new GN account | Email sent to GN with username and activation link |
| **All routes work** | Navigate through dashboard, forecast, forum | No errors related to missing classes or autoloader |

---

## Files Changed Summary

| File | Change |
|---|---|
| `core/bootstrap.php` | Replace Composer autoloader + phpdotenv with manual require + custom `.env` parser |
| `core/mailer.php` | Replace PHPMailer with raw SMTP socket client |
| `composer.json` | Remove both dependencies; clear autoload files array |
| `config/mail.php` | Default port 587→465, encryption tls→ssl |
| `vendor/` | Delete entirely and reinstall (empty) |
