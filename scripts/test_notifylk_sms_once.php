<?php

declare(strict_types=1);

/**
 * One-time Notify.lk SMS test utility for forecast alerts.
 *
 * Dry run (no SMS sent):
 *   php scripts/test_notifylk_sms_once.php --user-id=12 --level=major
 *
 * Send real SMS:
 *   php scripts/test_notifylk_sms_once.php --user-id=12 --level=major --send
 *
 * Optional overrides:
 *   --station-key=kalawellawa
 *   --phone=0771234567
 *   --area="Colombo 05"
 *   --sender-id=YourApprovedSender
 */

require __DIR__ . '/../core/bootstrap.php';

$options = getopt('', [
    'user-id:',
    'station-key::',
    'level::',
    'phone::',
    'area::',
    'sender-id::',
    'send',
    'help',
]);

if (isset($options['help'])) {
    print_usage();
    exit(0);
}

$userId = isset($options['user-id']) ? (int) $options['user-id'] : 0;
$stationKeyOverride = trim((string) ($options['station-key'] ?? ''));
$level = strtolower(trim((string) ($options['level'] ?? 'alert')));
$phoneOverride = trim((string) ($options['phone'] ?? ''));
$areaOverride = trim((string) ($options['area'] ?? ''));
$senderIdOverride = trim((string) ($options['sender-id'] ?? ''));
$send = isset($options['send']);

if ($senderIdOverride !== '' && strlen($senderIdOverride) > 11) {
    fwrite(STDERR, "Error: --sender-id must be 11 characters or fewer.\n");
    exit(1);
}

if ($senderIdOverride !== '' && strtoupper($senderIdOverride) === 'YOUR_APPROVED_SENDER') {
    fwrite(STDERR, "Error: Replace placeholder YOUR_APPROVED_SENDER with your actual Notify.lk approved sender ID.\n");
    exit(1);
}

if ($userId <= 0) {
    fwrite(STDERR, "Error: --user-id is required.\n\n");
    print_usage();
    exit(1);
}

if (!in_array($level, ['alert', 'minor', 'major'], true)) {
    fwrite(STDERR, "Error: --level must be one of alert|minor|major.\n");
    exit(1);
}

if (!function_exists('sms_alert_notifylk_is_configured') || !sms_alert_notifylk_is_configured()) {
    fwrite(STDERR, "Error: Notify.lk credentials are missing in .env\n");
    exit(1);
}

$subscription = db_fetch(
    "SELECT s.user_id, s.role, s.station_key,
            COALESCE(g.contact_number, v.contact_number, '') AS contact_number,
            COALESCE(NULLIF(g.gn_division, ''), NULLIF(v.gn_division, ''), '') AS area_name
     FROM forecast_sms_alert_subscription s
     INNER JOIN users u ON u.user_id = s.user_id
     LEFT JOIN general_user g ON g.user_id = s.user_id AND s.role = 'general'
     LEFT JOIN volunteers v ON v.user_id = s.user_id AND s.role = 'volunteer'
     WHERE s.user_id = ?
     LIMIT 1",
    [$userId]
);

if (!is_array($subscription)) {
    fwrite(STDERR, "Error: No forecast SMS subscription found for user_id={$userId}.\n");
    fwrite(STDERR, "Enable SMS on /dashboard/forecast first.\n");
    exit(1);
}

$stationKey = $stationKeyOverride !== ''
    ? $stationKeyOverride
    : trim((string) ($subscription['station_key'] ?? ''));

if ($stationKey === '') {
    fwrite(STDERR, "Error: station key is empty. Pass --station-key or save a station in forecast SMS settings.\n");
    exit(1);
}

$phoneRaw = $phoneOverride !== '' ? $phoneOverride : (string) ($subscription['contact_number'] ?? '');
$phone = sms_alert_normalize_phone($phoneRaw);
if ($phone === '') {
    fwrite(STDERR, "Error: Invalid contact number for user_id={$userId}. Use --phone to override.\n");
    exit(1);
}

$area = $areaOverride !== '' ? $areaOverride : (string) ($subscription['area_name'] ?? '');
$area = trim($area) !== '' ? $area : '-';

$snapshot = forecast_snapshot();
$stationMeta = find_station_meta($snapshot, $stationKey);
if (!is_array($stationMeta)) {
    fwrite(STDERR, "Error: Station '{$stationKey}' not found in current forecast snapshot.\n");
    exit(1);
}

$message = sms_alert_forecast_message(
    $level,
    (string) ($stationMeta['river_name'] ?? '-'),
    (string) ($stationMeta['station_name'] ?? '-'),
    $area
);

$header = [
    'mode' => $send ? 'live-send' : 'dry-run',
    'user_id' => $userId,
    'role' => (string) ($subscription['role'] ?? ''),
    'sender_id' => $senderIdOverride !== '' ? $senderIdOverride : (string) env('NOTIFY_LK_SENDER_ID', ''),
    'phone' => $phone,
    'station_key' => $stationKey,
    'river' => (string) ($stationMeta['river_name'] ?? '-'),
    'station' => (string) ($stationMeta['station_name'] ?? '-'),
    'level' => $level,
    'area' => $area,
];

echo json_encode($header, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . PHP_EOL;
echo "Message preview:\n";
echo "----------------\n";
echo $message . PHP_EOL;
echo "----------------\n\n";

if (!$send) {
    echo "Dry run complete. Add --send to deliver this SMS via Notify.lk.\n";
    exit(0);
}

$result = sms_alert_notifylk_send($phone, $message, $senderIdOverride !== '' ? $senderIdOverride : null);
echo "Notify.lk response:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

if (empty($result['success'])) {
    $errorHints = extract_notifylk_error_hints($result);
    if (!empty($errorHints)) {
        echo PHP_EOL . "Hints:" . PHP_EOL;
        foreach ($errorHints as $hint) {
            echo "- {$hint}" . PHP_EOL;
        }
    }
}

exit(!empty($result['success']) ? 0 : 2);

function find_station_meta(array $snapshot, string $stationKey): ?array
{
    foreach ((array) ($snapshot['rivers'] ?? []) as $river) {
        $riverName = (string) ($river['river_name'] ?? '');
        foreach ((array) ($river['stations'] ?? []) as $station) {
            if ((string) ($station['station_key'] ?? '') !== $stationKey) {
                continue;
            }

            return [
                'river_name' => $riverName,
                'station_name' => (string) ($station['station_name'] ?? $stationKey),
            ];
        }
    }

    return null;
}

function print_usage(): void
{
    $usage = [
        'Usage:',
        '  php scripts/test_notifylk_sms_once.php --user-id=12 [--level=alert|minor|major] [--station-key=key] [--phone=0771234567] [--area="Area"] [--sender-id=ApprovedSender] [--send]',
        '',
        'Examples:',
        '  php scripts/test_notifylk_sms_once.php --user-id=12 --level=major',
        '  php scripts/test_notifylk_sms_once.php --user-id=12 --level=minor --send',
        '  php scripts/test_notifylk_sms_once.php --user-id=12 --station-key=nagalagam_street --phone=0771234567 --level=alert --sender-id=MyBrand --send',
    ];

    echo implode(PHP_EOL, $usage) . PHP_EOL;
}

function extract_notifylk_error_hints(array $result): array
{
    $hints = [];

    if (($result['error'] ?? '') === 'notifylk-sender-id-too-long') {
        $hints[] = 'Sender ID must be 11 characters or fewer.';
        $hints[] = 'Use your actual approved sender ID from Notify.lk dashboard, then update NOTIFY_LK_SENDER_ID in .env.';
        return $hints;
    }

    $response = (array) ($result['response'] ?? []);
    $errors = (array) ($response['errors'] ?? []);
    $errorText = strtolower(implode(' | ', array_map('strval', $errors)));

    if (str_contains($errorText, 'sender id is not registered')) {
        $hints[] = 'Use a sender ID already approved in your Notify.lk account.';
        $hints[] = 'For a quick retry, pass --sender-id=<approved_sender_id>.';
        $hints[] = 'Then update NOTIFY_LK_SENDER_ID in .env to the same approved sender ID.';
    }

    if (str_contains($errorText, 'invalid') && str_contains($errorText, 'phone')) {
        $hints[] = 'Verify destination number format (Sri Lanka) and retry with --phone=07XXXXXXXX.';
    }

    return $hints;
}
