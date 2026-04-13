<?php
declare(strict_types=1);

/**
 * Compute river discharge baseline thresholds for active hydrostations.
 *
 * Usage:
 *   php tools/discharge_baselines/compute.php
 */

const HIST_START_DATE = '2020-01-01';
const HIST_END_DATE = '2024-12-31';

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
    $hydrostations = fetch_hydrostations();
    if (empty($hydrostations)) {
        fwrite(STDERR, "Failed: no hydrostations returned from ArcGIS.\n");
        return 1;
    }

    $activeGauges = fetch_active_gauges();
    if (empty($activeGauges)) {
        fwrite(STDERR, "Failed: no active gauges returned from ArcGIS.\n");
        return 1;
    }

    $activeLookup = [];
    foreach ($activeGauges as $gaugeName) {
        $activeLookup[normalize_name($gaugeName)] = true;
    }

    $stations = [];
    foreach ($hydrostations as $station) {
        $name = (string) ($station['station'] ?? '');
        if ($name === '') {
            continue;
        }

        if (isset($activeLookup[normalize_name($name)])) {
            $stations[] = $station;
        }
    }

    if (count($stations) === 0) {
        fwrite(STDERR, "Failed: 0 matching stations after active-gauge filtering.\n");
        return 1;
    }

    if (count($stations) !== 40) {
        fwrite(STDERR, "Warning: expected 40 active stations, got " . count($stations) . ".\n");
    }

    usort(
        $stations,
        static fn(array $a, array $b): int => strcmp((string) ($a['station'] ?? ''), (string) ($b['station'] ?? ''))
    );

    $rows = [];
    $skipped = [];
    $usedKeys = [];
    $total = count($stations);

    foreach ($stations as $index => $station) {
        $stationName = (string) ($station['station'] ?? '');
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
        $alert = $mean * 2.0;
        $minor = $mean * 5.0;
        $major = $mean * 10.0;

        $stationKey = unique_station_key($stationName, $usedKeys);

        $rows[] = [
            'station_key' => $stationKey,
            'station_name' => $stationName,
            'basin' => (string) ($station['basin'] ?? ''),
            'latitude' => (float) $lat,
            'longitude' => (float) $lon,
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
            'active_gauges' => ARC_GAUGES_VIEW_ENDPOINT,
            'flood_api' => OPEN_METEO_FLOOD_ENDPOINT,
        ],
        'window' => [
            'start' => HIST_START_DATE,
            'end' => HIST_END_DATE,
        ],
        'active_station_count' => count($stations),
        'computed_count' => count($rows),
        'skipped_count' => count($skipped),
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

function fetch_hydrostations(): array
{
    $query = http_build_query([
        'where' => '1=1',
        'outFields' => 'station,latitude,longitude,basin',
        'orderByFields' => 'station ASC',
        'resultRecordCount' => 1000,
        'f' => 'json',
    ], '', '&', PHP_QUERY_RFC3986);

    $url = ARC_HYDROSTATIONS_ENDPOINT . '?' . $query;
    $payload = http_get_json($url);
    if (!is_array($payload)) {
        return [];
    }

    $features = (array) ($payload['features'] ?? []);
    $stations = [];
    foreach ($features as $feature) {
        $attributes = (array) ($feature['attributes'] ?? []);
        $stations[] = [
            'station' => (string) ($attributes['station'] ?? ''),
            'latitude' => $attributes['latitude'] ?? null,
            'longitude' => $attributes['longitude'] ?? null,
            'basin' => (string) ($attributes['basin'] ?? ''),
        ];
    }

    return $stations;
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
    $lines[] = ' * River discharge flood thresholds per station.';
    $lines[] = ' * Derived from 5-year historical mean (2020-2024) via Open-Meteo GloFAS.';
    $lines[] = ' * Thresholds: alert = 2x mean, minor = 5x mean, major = 10x mean.';
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
        $lines[] = '            \'' . 'mean' . '\'' . ' => ' . float_literal($mean) . ',';
        $lines[] = '            \'' . 'alert' . '\'' . ' => ' . float_literal($alert) . ',';
        $lines[] = '            \'' . 'minor' . '\'' . ' => ' . float_literal($minor) . ',';
        $lines[] = '            \'' . 'major' . '\'' . ' => ' . float_literal($major) . ',';
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
