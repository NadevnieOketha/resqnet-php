<?php

/**
 * SMS Alerts Module - Models
 */

const SMS_ALERT_FORECAST_LOOKAHEAD_DAYS = 3;

function sms_alert_forecast_dispatch(array $snapshot): void
{
    if (!sms_alert_notifylk_is_configured()) {
        return;
    }

    sms_alert_forecast_ensure_delivery_table();

    $targetDate = (new DateTimeImmutable('now', new DateTimeZone('Asia/Colombo')))
        ->modify('+' . SMS_ALERT_FORECAST_LOOKAHEAD_DAYS . ' days')
        ->format('Y-m-d');

    $stationForecasts = sms_alert_forecast_station_index($snapshot, $targetDate);
    if (empty($stationForecasts)) {
        return;
    }

    $subscriptions = sms_alert_forecast_active_subscriptions();
    foreach ($subscriptions as $subscription) {
        $userId = (int) ($subscription['user_id'] ?? 0);
        $stationKey = trim((string) ($subscription['station_key'] ?? ''));

        if ($userId <= 0 || $stationKey === '' || !isset($stationForecasts[$stationKey])) {
            continue;
        }

        $forecast = (array) $stationForecasts[$stationKey];
        $level = (string) ($forecast['level'] ?? 'safe');
        if (!in_array($level, ['alert', 'minor', 'major'], true)) {
            continue;
        }

        if (sms_alert_forecast_already_sent($userId, $stationKey, $targetDate, $level)) {
            continue;
        }

        $phone = sms_alert_normalize_phone((string) ($subscription['contact_number'] ?? ''));
        $areaName = trim((string) ($subscription['area_name'] ?? ''));
        if ($areaName === '') {
            $areaName = (string) ($forecast['local_area'] ?? '-');
        }

        $message = sms_alert_forecast_message(
            $level,
            (string) ($forecast['river_name'] ?? '-'),
            (string) ($forecast['station_name'] ?? '-'),
            $areaName
        );

        if ($phone === '') {
            sms_alert_forecast_log_delivery([
                'user_id' => $userId,
                'station_key' => $stationKey,
                'river_key' => (string) ($forecast['river_key'] ?? ''),
                'forecast_date' => $targetDate,
                'forecast_level' => $level,
                'forecast_discharge' => forecast_nullable_float($forecast['discharge'] ?? null),
                'message_text' => $message,
                'delivery_status' => 'failed',
                'provider_response' => json_encode(['error' => 'invalid-contact-number']),
            ]);
            continue;
        }

        $result = sms_alert_notifylk_send($phone, $message);
        sms_alert_forecast_log_delivery([
            'user_id' => $userId,
            'station_key' => $stationKey,
            'river_key' => (string) ($forecast['river_key'] ?? ''),
            'forecast_date' => $targetDate,
            'forecast_level' => $level,
            'forecast_discharge' => forecast_nullable_float($forecast['discharge'] ?? null),
            'message_text' => $message,
            'delivery_status' => !empty($result['success']) ? 'sent' : 'failed',
            'provider_response' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}

function sms_alert_forecast_station_index(array $snapshot, string $targetDate): array
{
    $index = [];

    foreach ((array) ($snapshot['rivers'] ?? []) as $river) {
        $riverKey = (string) ($river['river_key'] ?? '');
        $riverName = (string) ($river['river_name'] ?? $riverKey);

        foreach ((array) ($river['stations'] ?? []) as $station) {
            $stationKey = (string) ($station['station_key'] ?? '');
            if ($stationKey === '') {
                continue;
            }

            $rows = (array) ($station['daily_discharge'] ?? []);
            $targetRow = null;
            foreach ($rows as $row) {
                $rowDate = (string) ($row['date'] ?? '');
                if ($rowDate === $targetDate && !empty($row['is_forecast_day'])) {
                    $targetRow = $row;
                    break;
                }
            }

            if (!is_array($targetRow)) {
                continue;
            }

            $discharge = forecast_nullable_float($targetRow['river_discharge'] ?? null);
            if ($discharge === null) {
                continue;
            }

            $thresholds = (array) ($station['discharge_thresholds'] ?? []);
            $level = sms_alert_forecast_discharge_level($discharge, $thresholds);
            if ($level === 'safe') {
                continue;
            }

            $index[$stationKey] = [
                'river_key' => $riverKey,
                'river_name' => $riverName,
                'station_key' => $stationKey,
                'station_name' => (string) ($station['station_name'] ?? $stationKey),
                'local_area' => (string) ($station['local_area'] ?? ''),
                'discharge' => $discharge,
                'level' => $level,
            ];
        }
    }

    return $index;
}

function sms_alert_forecast_discharge_level(float $discharge, array $thresholds): string
{
    $major = forecast_nullable_float($thresholds['major'] ?? null);
    $minor = forecast_nullable_float($thresholds['minor'] ?? null);
    $alert = forecast_nullable_float($thresholds['alert'] ?? null);

    if ($major !== null && $discharge >= $major) {
        return 'major';
    }

    if ($minor !== null && $discharge >= $minor) {
        return 'minor';
    }

    if ($alert !== null && $discharge >= $alert) {
        return 'alert';
    }

    return 'safe';
}

function sms_alert_forecast_active_subscriptions(): array
{
    return db_fetch_all(
        "SELECT s.user_id, s.role, s.station_key,
                COALESCE(g.contact_number, v.contact_number, '') AS contact_number,
                COALESCE(NULLIF(g.gn_division, ''), NULLIF(v.gn_division, ''), '') AS area_name
         FROM forecast_sms_alert_subscription s
         INNER JOIN users u ON u.user_id = s.user_id AND u.active = 1
         LEFT JOIN general_user g ON g.user_id = s.user_id AND s.role = 'general'
         LEFT JOIN volunteers v ON v.user_id = s.user_id AND s.role = 'volunteer'
         WHERE s.sms_alert = 1 AND s.station_key IS NOT NULL AND s.station_key <> ''"
    );
}

function sms_alert_forecast_already_sent(int $userId, string $stationKey, string $forecastDate, string $forecastLevel): bool
{
    $row = db_fetch(
        'SELECT id
         FROM forecast_sms_alert_delivery_log
         WHERE user_id = ? AND station_key = ? AND forecast_date = ? AND forecast_level = ? AND delivery_status = ?
         LIMIT 1',
        [$userId, $stationKey, $forecastDate, $forecastLevel, 'sent']
    );

    return is_array($row);
}

function sms_alert_forecast_log_delivery(array $data): void
{
    db_insert('forecast_sms_alert_delivery_log', [
        'user_id' => (int) ($data['user_id'] ?? 0),
        'station_key' => (string) ($data['station_key'] ?? ''),
        'river_key' => (string) ($data['river_key'] ?? ''),
        'forecast_date' => (string) ($data['forecast_date'] ?? ''),
        'forecast_level' => (string) ($data['forecast_level'] ?? 'alert'),
        'forecast_discharge' => forecast_nullable_float($data['forecast_discharge'] ?? null),
        'message_text' => (string) ($data['message_text'] ?? ''),
        'delivery_status' => (string) ($data['delivery_status'] ?? 'failed'),
        'provider_response' => (string) ($data['provider_response'] ?? ''),
    ]);
}

function sms_alert_forecast_message(string $level, string $riverName, string $stationName, string $areaName): string
{
    $riverName = trim($riverName) !== '' ? $riverName : '-';
    $stationName = trim($stationName) !== '' ? $stationName : '-';
    $areaName = trim($areaName) !== '' ? $areaName : '-';

    if ($level === 'major') {
        return "[ResQnet] 🚨 DANGER\n"
            . "River: {$riverName}\n"
            . "Station: {$stationName}\n"
            . "Forecast: Major flood level\n"
            . "Area: {$areaName}\n"
            . "Action: Evacuate immediately to safe locations.";
    }

    if ($level === 'minor') {
        return "[ResQnet] ⚠️ FORECAST ALERT\n"
            . "River: {$riverName}\n"
            . "Station: {$stationName}\n"
            . "Forecast: Minor flood level\n"
            . "Area: {$areaName}\n"
            . "Action: Prepare for possible flooding.";
    }

    return "ResQnet ⚠️ FORECAST ALERT\n"
        . "River: {$riverName}\n"
        . "Station: {$stationName}\n"
        . "Forecast: Alert\n"
        . "Area: {$areaName}\n"
        . "Action: Stay alert and monitor updates.";
}

function sms_alert_notifylk_is_configured(): bool
{
    return trim((string) env('NOTIFY_LK_USER_ID', '')) !== ''
        && trim((string) env('NOTIFY_LK_API_KEY', '')) !== ''
        && trim((string) env('NOTIFY_LK_SENDER_ID', '')) !== '';
}

function sms_alert_notifylk_send(string $to, string $message, ?string $senderId = null): array
{
    $endpoint = 'https://app.notify.lk/api/v1/send';
    $resolvedSenderId = trim((string) ($senderId ?? env('NOTIFY_LK_SENDER_ID', '')));

    if ($resolvedSenderId === '') {
        return [
            'success' => false,
            'status_code' => 0,
            'error' => 'notifylk-sender-id-missing',
        ];
    }

    if (strlen($resolvedSenderId) > 11) {
        return [
            'success' => false,
            'status_code' => 0,
            'error' => 'notifylk-sender-id-too-long',
            'max_length' => 11,
        ];
    }

    $payload = http_build_query([
        'user_id' => (string) env('NOTIFY_LK_USER_ID', ''),
        'api_key' => (string) env('NOTIFY_LK_API_KEY', ''),
        'sender_id' => $resolvedSenderId,
        'to' => $to,
        'message' => $message,
    ], '', '&', PHP_QUERY_RFC3986);

    $responseBody = '';
    $statusCode = 0;

    if (function_exists('curl_init')) {
        $curl = curl_init($endpoint);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT => 'resqnet-sms/1.0',
        ]);

        $body = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (!is_string($body)) {
            return [
                'success' => false,
                'status_code' => $statusCode,
                'error' => $curlError !== '' ? $curlError : 'notifylk-request-failed',
            ];
        }

        $responseBody = $body;
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                    . "User-Agent: resqnet-sms/1.0\r\n",
                'content' => $payload,
                'timeout' => 20,
            ],
        ]);

        $body = @file_get_contents($endpoint, false, $context);
        $responseBody = is_string($body) ? $body : '';

        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $headerLine) {
                if (preg_match('/^HTTP\/\S+\s+(\d{3})/', (string) $headerLine, $matches)) {
                    $statusCode = (int) ($matches[1] ?? 0);
                    break;
                }
            }
        }
    }

    $decoded = json_decode($responseBody, true);
    $status = strtolower((string) ($decoded['status'] ?? ''));
    $isSuccess = $statusCode >= 200 && $statusCode < 300
        && ($status === 'success' || $status === 'ok' || $status === '1000');

    return [
        'success' => $isSuccess,
        'status_code' => $statusCode,
        'response' => is_array($decoded) ? $decoded : ['raw' => $responseBody],
    ];
}

function sms_alert_normalize_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';
    if ($digits === '') {
        return '';
    }

    if (str_starts_with($digits, '0094') && strlen($digits) >= 13) {
        $digits = substr($digits, 2);
    }

    if (str_starts_with($digits, '94') && strlen($digits) === 11) {
        return $digits;
    }

    if (str_starts_with($digits, '0') && strlen($digits) === 10) {
        return '94' . substr($digits, 1);
    }

    if (str_starts_with($digits, '7') && strlen($digits) === 9) {
        return '94' . $digits;
    }

    return '';
}

function sms_alert_forecast_ensure_delivery_table(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    db_query(
        'CREATE TABLE IF NOT EXISTS forecast_sms_alert_delivery_log (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            station_key VARCHAR(128) NOT NULL,
            river_key VARCHAR(64) DEFAULT NULL,
            forecast_date DATE NOT NULL,
            forecast_level ENUM(\'alert\', \'minor\', \'major\') NOT NULL,
            forecast_discharge DECIMAL(12,3) DEFAULT NULL,
            message_text TEXT NOT NULL,
            delivery_status ENUM(\'sent\', \'failed\') NOT NULL DEFAULT \'failed\',
            provider_response TEXT,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_forecast_sms_delivery_user (user_id),
            KEY idx_forecast_sms_delivery_target (station_key, forecast_date, forecast_level),
            CONSTRAINT fk_forecast_sms_delivery_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB'
    );

    $ready = true;
}
