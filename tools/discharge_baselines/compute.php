<?php
declare(strict_types=1);

/**
 * Compute river discharge baseline thresholds for monitored forecast stations.
 *
 * Usage:
 *   php tools/discharge_baselines/compute.php
 */

const HIST_START_DATE = '2020-01-01';
const HIST_END_DATE = '2024-12-31';

const TARGET_STATION_COUNT = 16;
const THRESHOLD_ALERT_MULTIPLIER = 2.0;
const THRESHOLD_MINOR_MULTIPLIER = 3.0;
const THRESHOLD_MAJOR_MULTIPLIER = 5.0;

const ARC_HYDROSTATIONS_ENDPOINT = 'https://services3.arcgis.com/J7ZFXmR8rSmQ3FGf/arcgis/rest/services/hydrostations/FeatureServer/0/query';
const ARC_GAUGES_VIEW_ENDPOINT = 'https://services3.arcgis.com/J7ZFXmR8rSmQ3FGf/arcgis/rest/services/gauges_2_view/FeatureServer/0/query';
const OPEN_METEO_FLOOD_ENDPOINT = 'https://flood-api.open-meteo.com/v1/flood';

const OUTPUT_JSON_PATH = __DIR__ . '/output/baselines.json';
const OUTPUT_PHP_PATH = __DIR__ . '/../../modules/forecast/discharge_thresholds.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must run in CLI mode.\n");
    exit(1);
}

$exitCode = run_baseline_computation();
exit($exitCode);

function run_baseline_computation(): int
{
    $stations = monitored_station_catalog();
    if (empty($stations)) {
        fwrite(STDERR, "Failed: monitored station catalog is empty.\n");
        return 1;
    }

    if (count($stations) !== TARGET_STATION_COUNT) {
        fwrite(STDERR, "Warning: expected " . TARGET_STATION_COUNT . " monitored stations, got " . count($stations) . ".\n");
    }

    $activeGauges = fetch_active_gauges();
    $activeLookup = [];
    foreach ($activeGauges as $gaugeName) {
        $activeLookup[normalize_name($gaugeName)] = true;
    }

    usort(
        $stations,
        static fn(array $a, array $b): int => strcmp((string) ($a['station_key'] ?? ''), (string) ($b['station_key'] ?? ''))
    );

    $rows = [];
    $skipped = [];
    $activeGaugeUnmatched = [];
    $usedKeys = [];
    $total = count($stations);

    foreach ($stations as $index => $station) {
        $stationName = (string) ($station['station_name'] ?? '');
        $stationAliases = (array) ($station['station_aliases'] ?? []);
        $gaugeActive = station_has_active_gauge($stationName, $activeLookup, $stationAliases);
        if (!$gaugeActive) {
            $activeGaugeUnmatched[] = $stationName;
        }

        $lat = to_nullable_float($station['latitude'] ?? null);
        $lon = to_nullable_float($station['longitude'] ?? null);

        if ($lat === null || $lon === null) {
            $skipped[] = [
                'station' => $stationName,
                'reason' => 'missing_coordinates',
            ];
            continue;
        }

        $progress = '[' . ($index + 1) . '/' . $total . '] ' . $stationName;
        fwrite(STDOUT, $progress . " ... ");

        $dischargeSeries = fetch_discharge_series($lat, $lon, HIST_START_DATE, HIST_END_DATE);
        if (empty($dischargeSeries)) {
            fwrite(STDOUT, "skipped (no discharge data)\n");
            $skipped[] = [
                'station' => $stationName,
                'reason' => 'no_discharge_data',
            ];
            continue;
        }

        $sampleCount = count($dischargeSeries);
        $mean = array_sum($dischargeSeries) / max(1, $sampleCount);
        $alert = $mean * THRESHOLD_ALERT_MULTIPLIER;
        $minor = $mean * THRESHOLD_MINOR_MULTIPLIER;
        $major = $mean * THRESHOLD_MAJOR_MULTIPLIER;

        $stationKey = (string) ($station['station_key'] ?? '');
        if ($stationKey === '') {
            $stationKey = unique_station_key($stationName, $usedKeys);
        }

        $rows[] = [
            'station_key' => $stationKey,
            'station_name' => $stationName,
            'basin' => (string) ($station['basin'] ?? ''),
            'latitude' => (float) $lat,
            'longitude' => (float) $lon,
            'active_gauge_match' => $gaugeActive,
            'sample_count' => $sampleCount,
            'mean' => round($mean, 6),
            'alert' => round($alert, 6),
            'minor' => round($minor, 6),
            'major' => round($major, 6),
            'window' => [
                'start' => HIST_START_DATE,
                'end' => HIST_END_DATE,
            ],
        ];

        fwrite(STDOUT, 'ok (mean=' . number_format($mean, 2, '.', '') . ")\n");
    }

    usort(
        $rows,
        static fn(array $a, array $b): int => strcmp((string) ($a['station_key'] ?? ''), (string) ($b['station_key'] ?? ''))
    );

    $generatedAt = gmdate('c');

    $jsonPayload = [
        'generated_at_utc' => $generatedAt,
        'source' => [
            'hydrostations' => ARC_HYDROSTATIONS_ENDPOINT,
            'station_catalog' => 'tools/discharge_baselines/compute.php::monitored_station_catalog',
            'active_gauges' => ARC_GAUGES_VIEW_ENDPOINT,
            'flood_api' => OPEN_METEO_FLOOD_ENDPOINT,
        ],
        'window' => [
            'start' => HIST_START_DATE,
            'end' => HIST_END_DATE,
        ],
        'target_station_count' => TARGET_STATION_COUNT,
        'active_station_count' => count($stations),
        'active_gauge_count' => count($activeLookup),
        'active_gauge_unmatched_count' => count($activeGaugeUnmatched),
        'active_gauge_unmatched' => $activeGaugeUnmatched,
        'computed_count' => count($rows),
        'skipped_count' => count($skipped),
        'threshold_policy' => [
            'alert_multiplier' => THRESHOLD_ALERT_MULTIPLIER,
            'minor_multiplier' => THRESHOLD_MINOR_MULTIPLIER,
            'major_multiplier' => THRESHOLD_MAJOR_MULTIPLIER,
        ],
        'stations' => $rows,
        'skipped' => $skipped,
    ];

    if (!is_dir(dirname(OUTPUT_JSON_PATH))) {
        mkdir(dirname(OUTPUT_JSON_PATH), 0777, true);
    }

    file_put_contents(
        OUTPUT_JSON_PATH,
        json_encode($jsonPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );

    file_put_contents(OUTPUT_PHP_PATH, render_thresholds_php($rows, $generatedAt));

    fwrite(STDOUT, "\nGenerated files:\n");
    fwrite(STDOUT, '- ' . relative_path(OUTPUT_JSON_PATH) . "\n");
    fwrite(STDOUT, '- ' . relative_path(OUTPUT_PHP_PATH) . "\n");

    return 0;
}

function fetch_active_gauges(): array
{
    $outStats = json_encode([
        [
            'statisticType' => 'count',
            'onStatisticField' => 'objectid',
            'outStatisticFieldName' => 'feature_count',
        ],
    ], JSON_UNESCAPED_SLASHES);

    $query = http_build_query([
        'where' => '1=1',
        'groupByFieldsForStatistics' => 'gauge',
        'outStatistics' => $outStats,
        'orderByFields' => 'gauge ASC',
        'f' => 'json',
    ], '', '&', PHP_QUERY_RFC3986);

    $url = ARC_GAUGES_VIEW_ENDPOINT . '?' . $query;
    $payload = http_get_json($url);
    if (!is_array($payload)) {
        return [];
    }

    $features = (array) ($payload['features'] ?? []);
    $gauges = [];
    foreach ($features as $feature) {
        $attributes = (array) ($feature['attributes'] ?? []);
        $name = trim((string) ($attributes['gauge'] ?? ''));
        if ($name !== '') {
            $gauges[] = $name;
        }
    }

    return $gauges;
}

function fetch_discharge_series(float $latitude, float $longitude, string $startDate, string $endDate): array
{
    $query = http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'daily' => 'river_discharge',
        'start_date' => $startDate,
        'end_date' => $endDate,
        'timezone' => 'UTC',
    ], '', '&', PHP_QUERY_RFC3986);

    $url = OPEN_METEO_FLOOD_ENDPOINT . '?' . $query;

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $payload = http_get_json($url, 45);
        if (!is_array($payload)) {
            continue;
        }

        $daily = (array) ($payload['daily'] ?? []);
        $values = (array) ($daily['river_discharge'] ?? []);

        $series = [];
        foreach ($values as $value) {
            $n = to_nullable_float($value);
            if ($n !== null) {
                $series[] = $n;
            }
        }

        if (!empty($series)) {
            return $series;
        }
    }

    return [];
}

function render_thresholds_php(array $rows, string $generatedAt): string
{
    $lines = [];
    $lines[] = '<?php';
    $lines[] = '';
    $lines[] = '/**';
    $lines[] = ' * River discharge flood thresholds for monitored basin locations.';
    $lines[] = ' * Derived from 5-year historical mean (2020-2024) via Open-Meteo GloFAS.';
    $lines[] = ' * Thresholds: alert = 2x mean, minor = 3x mean, major = 5x mean.';
    $lines[] = ' *';
    $lines[] = ' * Generated by: php tools/discharge_baselines/compute.php';
    $lines[] = ' * Generated at (UTC): ' . $generatedAt;
    $lines[] = ' *';
    $lines[] = ' * DO NOT EDIT MANUALLY.';
    $lines[] = ' */';
    $lines[] = '';
    $lines[] = 'function forecast_discharge_thresholds(): array';
    $lines[] = '{';
    $lines[] = '    return [';

    foreach ($rows as $row) {
        $key = (string) ($row['station_key'] ?? '');
        $stationName = (string) ($row['station_name'] ?? '');
        $mean = (float) ($row['mean'] ?? 0.0);
        $alert = (float) ($row['alert'] ?? 0.0);
        $minor = (float) ($row['minor'] ?? 0.0);
        $major = (float) ($row['major'] ?? 0.0);

        $lines[] = "        '" . addslashes($key) . "' => [";
        $lines[] = "            // " . addslashes($stationName);
        $lines[] = '            \'mean\' => ' . float_literal($mean) . ',';
        $lines[] = '            \'alert\' => ' . float_literal($alert) . ',';
        $lines[] = '            \'minor\' => ' . float_literal($minor) . ',';
        $lines[] = '            \'major\' => ' . float_literal($major) . ',';
        $lines[] = '        ],';
    }

    $lines[] = '    ];';
    $lines[] = '}';
    $lines[] = '';

    return implode(PHP_EOL, $lines);
}

function float_literal(float $value): string
{
    return number_format($value, 2, '.', '');
}

function unique_station_key(string $stationName, array &$usedKeys): string
{
    $base = slugify_station_name($stationName);
    if ($base === '') {
        $base = 'station';
    }

    if (!isset($usedKeys[$base])) {
        $usedKeys[$base] = 1;
        return $base;
    }

    $usedKeys[$base]++;
    return $base . '_' . $usedKeys[$base];
}

function slugify_station_name(string $name): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    $text = $ascii !== false ? $ascii : $name;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '_', $text) ?? '';
    return trim($text, '_');
}

function normalize_name(string $name): string
{
    $name = strtolower(trim($name));
    $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? '';
    return trim(preg_replace('/\s+/', ' ', $name) ?? '');
}

function monitored_station_catalog(): array
{
    return [
        [
            'station_key' => 'manampitiya',
            'station_name' => 'Manampitiya',
            'basin' => 'Mahaweli Ganga',
            'latitude' => 7.912987,
            'longitude' => 81.090594,
        ],
        [
            'station_key' => 'nawalapitiya',
            'station_name' => 'Nawalapitiya',
            'basin' => 'Mahaweli Ganga',
            'latitude' => 7.04894547,
            'longitude' => 80.53616209,
        ],
        [
            'station_key' => 'peradeniya',
            'station_name' => 'Peradeniya',
            'basin' => 'Mahaweli Ganga',
            'latitude' => 7.269059,
            'longitude' => 80.593005,
        ],
        [
            'station_key' => 'thaldena',
            'station_name' => 'Thaldena',
            'basin' => 'Mahaweli Ganga',
            'latitude' => 7.090653,
            'longitude' => 81.048886,
        ],
        [
            'station_key' => 'weraganthota',
            'station_name' => 'Weraganthota',
            'basin' => 'Mahaweli Ganga',
            'latitude' => 7.31647,
            'longitude' => 80.98911,
        ],
        [
            'station_key' => 'ellagawa',
            'station_name' => 'Ellagawa',
            'basin' => 'Kalu Ganga',
            'latitude' => 6.731554,
            'longitude' => 80.218254,
        ],
        [
            'station_key' => 'kalawellawa',
            'station_name' => 'Kalawellawa',
            'station_aliases' => ['Kalawellawa (Millakanda)'],
            'basin' => 'Kalu Ganga',
            'latitude' => 6.631166,
            'longitude' => 80.16069,
        ],
        [
            'station_key' => 'magura',
            'station_name' => 'Magura',
            'basin' => 'Kalu Ganga',
            'latitude' => 6.513727,
            'longitude' => 80.243935,
        ],
        [
            'station_key' => 'putupaula',
            'station_name' => 'Putupaula',
            'basin' => 'Kalu Ganga',
            'latitude' => 6.614324,
            'longitude' => 80.061511,
        ],
        [
            'station_key' => 'rathnapura',
            'station_name' => 'Rathnapura',
            'basin' => 'Kalu Ganga',
            'latitude' => 6.679067,
            'longitude' => 80.397235,
        ],
        [
            'station_key' => 'deraniyagala',
            'station_name' => 'Deraniyagala',
            'basin' => 'Kelani Ganga',
            'latitude' => 6.925934,
            'longitude' => 80.339212,
        ],
        [
            'station_key' => 'glencourse',
            'station_name' => 'Glencourse',
            'basin' => 'Kelani Ganga',
            'latitude' => 6.976981,
            'longitude' => 80.194247,
        ],
        [
            'station_key' => 'hanwella',
            'station_name' => 'Hanwella',
            'basin' => 'Kelani Ganga',
            'latitude' => 6.9097,
            'longitude' => 80.083126,
        ],
        [
            'station_key' => 'holombuwa',
            'station_name' => 'Holombuwa',
            'basin' => 'Kelani Ganga',
            'latitude' => 7.188201,
            'longitude' => 80.266331,
        ],
        [
            'station_key' => 'kithulgala',
            'station_name' => 'Kithulgala',
            'basin' => 'Kelani Ganga',
            'latitude' => 6.991259,
            'longitude' => 80.419213,
        ],
        [
            'station_key' => 'nagalagam_street',
            'station_name' => 'Nagalagam Street',
            'basin' => 'Kelani Ganga',
            'latitude' => 6.958265,
            'longitude' => 79.878642,
        ],
    ];
}

function station_has_active_gauge(string $stationName, array $activeLookup, array $aliases = []): bool
{
    if (empty($activeLookup)) {
        return false;
    }

    $candidates = array_merge([$stationName], $aliases);
    foreach ($candidates as $candidate) {
        $key = normalize_name((string) $candidate);
        if ($key !== '' && isset($activeLookup[$key])) {
            return true;
        }
    }

    return false;
}

function to_nullable_float(mixed $value): ?float
{
    if ($value === null || $value === '' || !is_numeric($value)) {
        return null;
    }

    return (float) $value;
}

function relative_path(string $absolutePath): string
{
    $root = realpath(__DIR__ . '/../../');
    $path = realpath($absolutePath);

    if ($root === false || $path === false) {
        return $absolutePath;
    }

    if (str_starts_with($path, $root)) {
        return ltrim(str_replace('\\', '/', substr($path, strlen($root))), '/');
    }

    return str_replace('\\', '/', $path);
}

function http_get_json(string $url, int $timeout = 25): ?array
{
    $body = null;

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_USERAGENT => 'resqnet-discharge-baseline/1.0',
        ]);

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (is_string($response) && $statusCode >= 200 && $statusCode < 300) {
            $body = $response;
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'header' => "User-Agent: resqnet-discharge-baseline/1.0\r\n",
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if (is_string($response)) {
            $body = $response;
        }
    }

    if (!is_string($body) || $body === '') {
        return null;
    }

    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : null;
}
