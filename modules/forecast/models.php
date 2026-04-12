<?php

/**
 * Forecast Module - Models
 */

function forecast_weather_station_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug) ?? '';
    $slug = trim($slug, '_');

    return $slug !== '' ? $slug : 'station';
}

function forecast_station_key(string $stationName): string
{
    $clean = preg_replace('/\s*\([^)]*\)\s*/', ' ', $stationName) ?? $stationName;
    return forecast_weather_station_slug($clean);
}

function forecast_http_get_json(string $url, int $timeoutSeconds = 20): ?array
{
    $responseBody = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if (is_string($raw) && $status >= 200 && $status < 300) {
            $responseBody = $raw;
        }
    }

    if (!is_string($responseBody)) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSeconds,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if (is_string($raw) && $raw !== '') {
            $responseBody = $raw;
        }
    }

    if (!is_string($responseBody) || $responseBody === '') {
        return null;
    }

    $decoded = json_decode($responseBody, true);
    return is_array($decoded) ? $decoded : null;
}

function forecast_env_bool(string $key, bool $default = false): bool
{
    $raw = env($key, $default ? '1' : '0');

    if (is_bool($raw)) {
        return $raw;
    }

    $value = strtolower(trim((string) $raw));
    if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }

    if (in_array($value, ['0', 'false', 'no', 'off', ''], true)) {
        return false;
    }

    return $default;
}

function forecast_arcgis_hydrostations_service_url(): string
{
    return 'https://services3.arcgis.com/J7ZFXmR8rSmQ3FGf/arcgis/rest/services/hydrostations/FeatureServer/0';
}

function forecast_arcgis_gauges_service_url(): string
{
    return 'https://services3.arcgis.com/J7ZFXmR8rSmQ3FGf/arcgis/rest/services/gauges_2_view/FeatureServer/0';
}

function forecast_arcgis_query_features(string $serviceUrl, array $query, int $pageSize = 2000, int $maxPages = 30): array
{
    $all = [];
    $offset = 0;

    for ($page = 0; $page < $maxPages; $page++) {
        $params = array_merge([
            'f' => 'json',
            'returnGeometry' => 'false',
            'resultOffset' => $offset,
            'resultRecordCount' => $pageSize,
        ], $query);

        $url = $serviceUrl . '/query?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $payload = forecast_http_get_json($url);
        if (!$payload || !isset($payload['features']) || !is_array($payload['features'])) {
            break;
        }

        $chunk = $payload['features'];
        if (empty($chunk)) {
            break;
        }

        $all = array_merge($all, $chunk);
        $count = count($chunk);
        $offset += $count;

        $exceeded = !empty($payload['exceededTransferLimit']);
        if (!$exceeded && $count < $pageSize) {
            break;
        }
    }

    return $all;
}

function forecast_basin_name_clean(string $value): string
{
    $clean = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    return $clean;
}

function forecast_river_name_from_basin(string $basinName): string
{
    $basin = forecast_basin_name_clean($basinName);
    if ($basin === '') {
        return '';
    }

    $river = preg_replace('/\s+(ganga|oya)\s*$/i', '', $basin) ?? $basin;
    $river = trim($river);
    return $river !== '' ? $river : $basin;
}

function forecast_arcgis_fetch_hydrostations(): array
{
    $features = forecast_arcgis_query_features(forecast_arcgis_hydrostations_service_url(), [
        'where' => "(station <> 'Calidonia') AND (basin <> 'Kala Oya')",
        'outFields' => 'station,basin,latitude,longitude',
        'orderByFields' => 'station ASC',
    ], 500, 5);

    $stations = [];
    foreach ($features as $feature) {
        $attrs = (array) ($feature['attributes'] ?? []);

        $stationName = trim((string) ($attrs['station'] ?? ''));
        $basinName = forecast_basin_name_clean((string) ($attrs['basin'] ?? ''));

        if ($stationName === '' || $basinName === '') {
            continue;
        }

        $stationKey = forecast_station_key($stationName);
        $stations[$stationKey] = [
            'station_key' => $stationKey,
            'station_name' => $stationName,
            'basin_name' => $basinName,
            'river_name' => forecast_river_name_from_basin($basinName),
            'latitude' => isset($attrs['latitude']) ? (float) $attrs['latitude'] : 0.0,
            'longitude' => isset($attrs['longitude']) ? (float) $attrs['longitude'] : 0.0,
        ];
    }

    ksort($stations, SORT_NATURAL | SORT_FLAG_CASE);
    return array_values($stations);
}

function forecast_arcgis_fetch_recent_gauge_rows(): array
{
    $features = forecast_arcgis_query_features(forecast_arcgis_gauges_service_url(), [
        'where' => "CreationDate BETWEEN CURRENT_TIMESTAMP - 4 AND CURRENT_TIMESTAMP AND gauge <> 'Calidonia'",
        'outFields' => 'gauge,basin,water_level,alertpull,minorpull,majorpull,CreationDate',
        'orderByFields' => 'CreationDate ASC, gauge ASC',
    ]);

    $rows = [];
    foreach ($features as $feature) {
        $attrs = (array) ($feature['attributes'] ?? []);

        $stationName = trim((string) ($attrs['gauge'] ?? ''));
        $basinName = forecast_basin_name_clean((string) ($attrs['basin'] ?? ''));
        $createdAtMs = isset($attrs['CreationDate']) ? (int) $attrs['CreationDate'] : 0;

        if ($stationName === '' || $basinName === '' || $createdAtMs <= 0) {
            continue;
        }

        $rows[] = [
            'station_key' => forecast_station_key($stationName),
            'station_name' => $stationName,
            'basin_name' => $basinName,
            'water_level' => is_null($attrs['water_level'] ?? null) ? null : (float) $attrs['water_level'],
            'alertpull' => is_null($attrs['alertpull'] ?? null) ? null : (float) $attrs['alertpull'],
            'minorpull' => is_null($attrs['minorpull'] ?? null) ? null : (float) $attrs['minorpull'],
            'majorpull' => is_null($attrs['majorpull'] ?? null) ? null : (float) $attrs['majorpull'],
            'created_at_ms' => $createdAtMs,
        ];
    }

    return $rows;
}

function forecast_ms_to_local_date(int $epochMs): string
{
    $seconds = (int) floor($epochMs / 1000);
    $dt = new DateTimeImmutable('@' . $seconds);
    $dt = $dt->setTimezone(new DateTimeZone('Asia/Colombo'));
    return $dt->format('Y-m-d');
}

function forecast_observed_daily_by_station(array $gaugeRows): array
{
    $daily = [];

    foreach ($gaugeRows as $row) {
        $stationKey = (string) ($row['station_key'] ?? '');
        if ($stationKey === '') {
            continue;
        }

        $date = forecast_ms_to_local_date((int) ($row['created_at_ms'] ?? 0));
        if ($date === '') {
            continue;
        }

        if (!isset($daily[$stationKey])) {
            $daily[$stationKey] = [
                'dates' => [],
                'alertpull' => null,
                'minorpull' => null,
                'majorpull' => null,
                'latest_at_ms' => 0,
            ];
        }

        if (!is_null($row['alertpull']) && is_null($daily[$stationKey]['alertpull'])) {
            $daily[$stationKey]['alertpull'] = (float) $row['alertpull'];
        }
        if (!is_null($row['minorpull']) && is_null($daily[$stationKey]['minorpull'])) {
            $daily[$stationKey]['minorpull'] = (float) $row['minorpull'];
        }
        if (!is_null($row['majorpull']) && is_null($daily[$stationKey]['majorpull'])) {
            $daily[$stationKey]['majorpull'] = (float) $row['majorpull'];
        }

        $createdAtMs = (int) ($row['created_at_ms'] ?? 0);
        if ($createdAtMs > (int) $daily[$stationKey]['latest_at_ms']) {
            $daily[$stationKey]['latest_at_ms'] = $createdAtMs;

            if (!is_null($row['alertpull'])) {
                $daily[$stationKey]['alertpull'] = (float) $row['alertpull'];
            }
            if (!is_null($row['minorpull'])) {
                $daily[$stationKey]['minorpull'] = (float) $row['minorpull'];
            }
            if (!is_null($row['majorpull'])) {
                $daily[$stationKey]['majorpull'] = (float) $row['majorpull'];
            }
        }

        if (!isset($daily[$stationKey]['dates'][$date])) {
            $daily[$stationKey]['dates'][$date] = [
                'sum' => 0.0,
                'count' => 0,
                'max' => null,
                'min' => null,
            ];
        }

        if (!is_null($row['water_level'])) {
            $value = (float) $row['water_level'];
            $daily[$stationKey]['dates'][$date]['sum'] += $value;
            $daily[$stationKey]['dates'][$date]['count']++;

            $existingMax = $daily[$stationKey]['dates'][$date]['max'];
            $existingMin = $daily[$stationKey]['dates'][$date]['min'];

            $daily[$stationKey]['dates'][$date]['max'] = is_null($existingMax) ? $value : max((float) $existingMax, $value);
            $daily[$stationKey]['dates'][$date]['min'] = is_null($existingMin) ? $value : min((float) $existingMin, $value);
        }
    }

    foreach ($daily as $stationKey => $data) {
        foreach ((array) ($data['dates'] ?? []) as $date => $bucket) {
            $count = (int) ($bucket['count'] ?? 0);
            $avg = $count > 0 ? ((float) ($bucket['sum'] ?? 0.0) / $count) : null;

            $daily[$stationKey]['dates'][$date] = [
                'water_level' => is_null($avg) ? null : round($avg, 2),
                'water_level_max' => is_null($bucket['max']) ? null : round((float) $bucket['max'], 2),
                'water_level_min' => is_null($bucket['min']) ? null : round((float) $bucket['min'], 2),
            ];
        }
    }

    return $daily;
}

function forecast_open_meteo_weather_daily(float $latitude, float $longitude): array
{
    if ($latitude == 0.0 && $longitude == 0.0) {
        return [];
    }

    $url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'daily' => 'precipitation_sum,temperature_2m_max,temperature_2m_min',
        'past_days' => 2,
        'forecast_days' => 8,
        'timezone' => 'Asia/Colombo',
    ], '', '&', PHP_QUERY_RFC3986);

    $payload = forecast_http_get_json($url);
    if (!$payload) {
        return [];
    }

    $daily = (array) ($payload['daily'] ?? []);
    $dates = (array) ($daily['time'] ?? []);
    $rain = (array) ($daily['precipitation_sum'] ?? []);
    $maxTemp = (array) ($daily['temperature_2m_max'] ?? []);
    $minTemp = (array) ($daily['temperature_2m_min'] ?? []);

    $count = count($dates);
    if ($count === 0) {
        return [];
    }

    $series = [];
    for ($i = 0; $i < $count; $i++) {
        $series[] = [
            'date' => (string) ($dates[$i] ?? ''),
            'precipitation_sum' => isset($rain[$i]) ? (float) $rain[$i] : null,
            'temperature_2m_max' => isset($maxTemp[$i]) ? (float) $maxTemp[$i] : null,
            'temperature_2m_min' => isset($minTemp[$i]) ? (float) $minTemp[$i] : null,
        ];
    }

    return $series;
}

function forecast_open_meteo_flood_daily(float $latitude, float $longitude): array
{
    if ($latitude == 0.0 && $longitude == 0.0) {
        return [];
    }

    $url = 'https://flood-api.open-meteo.com/v1/flood?' . http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'daily' => 'river_discharge,river_discharge_max,river_discharge_min,river_discharge_mean',
        'past_days' => 2,
        'forecast_days' => 8,
        'timezone' => 'Asia/Colombo',
    ], '', '&', PHP_QUERY_RFC3986);

    $payload = forecast_http_get_json($url);
    if (!$payload) {
        return [];
    }

    $daily = (array) ($payload['daily'] ?? []);
    $dates = (array) ($daily['time'] ?? []);
    $discharge = (array) ($daily['river_discharge'] ?? []);
    $dischargeMax = (array) ($daily['river_discharge_max'] ?? []);
    $dischargeMin = (array) ($daily['river_discharge_min'] ?? []);
    $dischargeMean = (array) ($daily['river_discharge_mean'] ?? []);

    $count = count($dates);
    if ($count === 0) {
        return [];
    }

    $series = [];
    for ($i = 0; $i < $count; $i++) {
        $series[] = [
            'date' => (string) ($dates[$i] ?? ''),
            'river_discharge' => isset($discharge[$i]) ? (float) $discharge[$i] : null,
            'river_discharge_max' => isset($dischargeMax[$i]) ? (float) $dischargeMax[$i] : null,
            'river_discharge_min' => isset($dischargeMin[$i]) ? (float) $dischargeMin[$i] : null,
            'river_discharge_mean' => isset($dischargeMean[$i]) ? (float) $dischargeMean[$i] : null,
        ];
    }

    return $series;
}

function forecast_classify_flood_level(?float $waterLevel, ?float $alert, ?float $minor, ?float $major): string
{
    if (is_null($waterLevel)) {
        return 'unknown';
    }

    if (!is_null($major) && $waterLevel >= $major) {
        return 'major_flood';
    }

    if (!is_null($minor) && $waterLevel >= $minor) {
        return 'minor_flood';
    }

    if (!is_null($alert) && $waterLevel >= $alert) {
        return 'alert';
    }

    if (is_null($alert) && is_null($minor) && is_null($major)) {
        return 'unknown';
    }

    return 'safe';
}

function forecast_discharge_multipliers(): array
{
    $alert = (float) env('FORECAST_DISCHARGE_ALERT_MULTIPLIER', 2.0);
    $minor = (float) env('FORECAST_DISCHARGE_MINOR_MULTIPLIER', 5.0);
    $major = (float) env('FORECAST_DISCHARGE_MAJOR_MULTIPLIER', 10.0);

    return [
        'alert' => $alert > 0 ? $alert : 2.0,
        'minor' => $minor > 0 ? $minor : 5.0,
        'major' => $major > 0 ? $major : 10.0,
    ];
}

function forecast_discharge_thresholds_from_mean(?float $mean): array
{
    if (is_null($mean) || $mean <= 0) {
        return [
            'alert' => null,
            'minor' => null,
            'major' => null,
        ];
    }

    $multipliers = forecast_discharge_multipliers();

    return [
        'alert' => round($mean * (float) $multipliers['alert'], 2),
        'minor' => round($mean * (float) $multipliers['minor'], 2),
        'major' => round($mean * (float) $multipliers['major'], 2),
    ];
}

function forecast_classify_discharge_flood(?float $discharge, ?float $mean): string
{
    if (is_null($discharge)) {
        return 'unknown';
    }

    $thresholds = forecast_discharge_thresholds_from_mean($mean);
    $alert = $thresholds['alert'];
    $minor = $thresholds['minor'];
    $major = $thresholds['major'];

    if (is_null($alert) || is_null($minor) || is_null($major)) {
        return 'unknown';
    }

    if ($discharge >= (float) $major) {
        return 'major_flood';
    }

    if ($discharge >= (float) $minor) {
        return 'minor_flood';
    }

    if ($discharge >= (float) $alert) {
        return 'alert';
    }

    return 'safe';
}

function forecast_target_date_window(): array
{
    $tz = new DateTimeZone('Asia/Colombo');
    $today = new DateTimeImmutable('now', $tz);

    $dates = [];
    for ($offset = -2; $offset <= 7; $offset++) {
        $dates[] = $today->modify(($offset >= 0 ? '+' : '') . $offset . ' day')->format('Y-m-d');
    }

    return [
        'today' => $today->format('Y-m-d'),
        'dates' => $dates,
    ];
}

function forecast_merge_forecast_dates(array $weatherSeries, array $floodSeries): array
{
    $dates = [];

    foreach ($weatherSeries as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '' && !in_array($date, $dates, true)) {
            $dates[] = $date;
        }
    }

    foreach ($floodSeries as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '' && !in_array($date, $dates, true)) {
            $dates[] = $date;
        }
    }

    if (empty($dates)) {
        $window = forecast_target_date_window();
        $dates = (array) ($window['dates'] ?? []);
    }

    sort($dates, SORT_STRING);
    return $dates;
}

function forecast_build_station_snapshot(array $station, array $observedByStation): array
{
    $window = forecast_target_date_window();
    $today = (string) ($window['today'] ?? date('Y-m-d'));

    $stationKey = (string) ($station['station_key'] ?? '');
    $observed = (array) ($observedByStation[$stationKey] ?? []);
    $observedDates = (array) ($observed['dates'] ?? []);

    $waterAlert = array_key_exists('alertpull', $observed) && !is_null($observed['alertpull']) ? (float) $observed['alertpull'] : null;
    $waterMinor = array_key_exists('minorpull', $observed) && !is_null($observed['minorpull']) ? (float) $observed['minorpull'] : null;
    $waterMajor = array_key_exists('majorpull', $observed) && !is_null($observed['majorpull']) ? (float) $observed['majorpull'] : null;

    $weatherSeries = forecast_open_meteo_weather_daily(
        (float) ($station['latitude'] ?? 0),
        (float) ($station['longitude'] ?? 0)
    );
    $floodSeries = forecast_open_meteo_flood_daily(
        (float) ($station['latitude'] ?? 0),
        (float) ($station['longitude'] ?? 0)
    );

    $weatherByDate = [];
    foreach ($weatherSeries as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '') {
            $weatherByDate[$date] = $row;
        }
    }

    $floodByDate = [];
    foreach ($floodSeries as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '') {
            $floodByDate[$date] = $row;
        }
    }

    $targetDates = forecast_merge_forecast_dates($weatherSeries, $floodSeries);

    $observedRows = [];
    $observedDateKeys = array_keys($observedDates);
    sort($observedDateKeys, SORT_STRING);

    foreach ($observedDateKeys as $date) {
        $row = (array) ($observedDates[$date] ?? []);
        $level = array_key_exists('water_level', $row) && !is_null($row['water_level']) ? (float) $row['water_level'] : null;
        $levelMax = array_key_exists('water_level_max', $row) && !is_null($row['water_level_max']) ? (float) $row['water_level_max'] : null;
        $levelMin = array_key_exists('water_level_min', $row) && !is_null($row['water_level_min']) ? (float) $row['water_level_min'] : null;

        $observedRows[] = [
            'date' => $date,
            'water_level' => $level,
            'water_level_max' => $levelMax,
            'water_level_min' => $levelMin,
            'alert_level' => $waterAlert,
            'minor_flood_level' => $waterMinor,
            'major_flood_level' => $waterMajor,
            'flood_status' => forecast_classify_flood_level($level, $waterAlert, $waterMinor, $waterMajor),
            'data_unit' => 'm',
        ];
    }

    if (count($observedRows) > 4) {
        $observedRows = array_slice($observedRows, -4);
    }

    $dischargeRows = [];
    $rainfallRows = [];
    $temperatureRows = [];
    $dailyRows = [];

    foreach ($targetDates as $date) {
        $weatherRow = (array) ($weatherByDate[$date] ?? []);
        $floodRow = (array) ($floodByDate[$date] ?? []);
        $observedRow = (array) ($observedDates[$date] ?? []);

        $isForecastDay = strcmp($date, $today) > 0;

        $discharge = array_key_exists('river_discharge', $floodRow) && !is_null($floodRow['river_discharge'])
            ? (float) $floodRow['river_discharge']
            : null;
        $dischargeMax = array_key_exists('river_discharge_max', $floodRow) && !is_null($floodRow['river_discharge_max'])
            ? (float) $floodRow['river_discharge_max']
            : null;
        $dischargeMin = array_key_exists('river_discharge_min', $floodRow) && !is_null($floodRow['river_discharge_min'])
            ? (float) $floodRow['river_discharge_min']
            : null;
        $dischargeMean = array_key_exists('river_discharge_mean', $floodRow) && !is_null($floodRow['river_discharge_mean'])
            ? (float) $floodRow['river_discharge_mean']
            : null;

        $dischargeThresholds = forecast_discharge_thresholds_from_mean($dischargeMean);
        $dischargeStatus = forecast_classify_discharge_flood($discharge, $dischargeMean);

        $dischargeRows[] = [
            'date' => $date,
            'river_discharge' => $discharge,
            'river_discharge_max' => $dischargeMax,
            'river_discharge_min' => $dischargeMin,
            'river_discharge_mean' => $dischargeMean,
            'alert_threshold' => $dischargeThresholds['alert'],
            'minor_threshold' => $dischargeThresholds['minor'],
            'major_threshold' => $dischargeThresholds['major'],
            'flood_status' => $dischargeStatus,
            'is_forecast_day' => $isForecastDay,
            'data_unit' => 'm3/s',
        ];

        $rainfall = array_key_exists('precipitation_sum', $weatherRow) && !is_null($weatherRow['precipitation_sum'])
            ? (float) $weatherRow['precipitation_sum']
            : null;
        $tempMax = array_key_exists('temperature_2m_max', $weatherRow) && !is_null($weatherRow['temperature_2m_max'])
            ? (float) $weatherRow['temperature_2m_max']
            : null;
        $tempMin = array_key_exists('temperature_2m_min', $weatherRow) && !is_null($weatherRow['temperature_2m_min'])
            ? (float) $weatherRow['temperature_2m_min']
            : null;

        $rainfallRows[] = [
            'date' => $date,
            'precipitation_sum' => $rainfall,
            'is_forecast_day' => $isForecastDay,
            'data_unit' => 'mm',
        ];

        $temperatureRows[] = [
            'date' => $date,
            'temperature_2m_max' => $tempMax,
            'temperature_2m_min' => $tempMin,
            'is_forecast_day' => $isForecastDay,
            'data_unit' => 'celsius',
        ];

        $observedLevel = array_key_exists('water_level', $observedRow) && !is_null($observedRow['water_level'])
            ? (float) $observedRow['water_level']
            : null;
        $observedMax = array_key_exists('water_level_max', $observedRow) && !is_null($observedRow['water_level_max'])
            ? (float) $observedRow['water_level_max']
            : null;
        $observedMin = array_key_exists('water_level_min', $observedRow) && !is_null($observedRow['water_level_min'])
            ? (float) $observedRow['water_level_min']
            : null;

        $dailyRows[] = [
            'date' => $date,
            'water_level' => $observedLevel,
            'water_level_max' => $observedMax,
            'water_level_min' => $observedMin,
            'river_discharge' => $discharge,
            'river_discharge_max' => $dischargeMax,
            'river_discharge_min' => $dischargeMin,
            'river_discharge_mean' => $dischargeMean,
            'rain_sum' => $rainfall,
            'temperature_2m_max' => $tempMax,
            'temperature_2m_min' => $tempMin,
            'alert_level' => $waterAlert,
            'minor_flood_level' => $waterMinor,
            'major_flood_level' => $waterMajor,
            'flood_status' => $isForecastDay
                ? $dischargeStatus
                : forecast_classify_flood_level($observedLevel, $waterAlert, $waterMinor, $waterMajor),
            'is_forecast_day' => $isForecastDay,
        ];
    }

    $latestObservedAt = isset($observed['latest_at_ms']) && (int) $observed['latest_at_ms'] > 0
        ? (new DateTimeImmutable('@' . (int) floor(((int) $observed['latest_at_ms']) / 1000)))
            ->setTimezone(new DateTimeZone('Asia/Colombo'))
            ->format('c')
        : null;

    return [
        'alert_level' => $waterAlert,
        'minor_flood_level' => $waterMinor,
        'major_flood_level' => $waterMajor,
        'latest_observed_at' => $latestObservedAt,
        'observed_water_levels' => $observedRows,
        'discharge_forecast' => $dischargeRows,
        'rainfall_forecast' => $rainfallRows,
        'temperature_forecast' => $temperatureRows,
        'daily' => $dailyRows,
    ];
}

function forecast_weather_cache_dir(): string
{
    return BASE_PATH . '/storage/logs/open_meteo';
}

function forecast_weather_cache_is_valid(array $payload): bool
{
    if (!isset($payload['rivers']) || !is_array($payload['rivers'])) {
        return false;
    }

    foreach ($payload['rivers'] as $river) {
        $stations = (array) ($river['stations'] ?? []);
        foreach ($stations as $station) {
            if (
                isset($station['observed_water_levels']) && is_array($station['observed_water_levels'])
                && isset($station['discharge_forecast']) && is_array($station['discharge_forecast'])
            ) {
                return true;
            }

            $dailyRows = (array) ($station['daily'] ?? []);
            foreach ($dailyRows as $row) {
                if (is_array($row) && array_key_exists('river_discharge', $row)) {
                    return true;
                }
            }
        }
    }

    return false;
}

function forecast_weather_read_cache(string $cachePath): ?array
{
    if (!is_file($cachePath)) {
        return null;
    }

    $raw = @file_get_contents($cachePath);
    if (!is_string($raw) || $raw === '') {
        return null;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function forecast_weather_latest_cache(string $cacheDir): ?array
{
    $candidates = [];

    $v2Pattern = glob($cacheDir . '/daily_forecast_v2_*.json');
    if (is_array($v2Pattern)) {
        $candidates = array_merge($candidates, $v2Pattern);
    }

    $waterPattern = glob($cacheDir . '/daily_water_levels_*.json');
    if (is_array($waterPattern)) {
        $candidates = array_merge($candidates, $waterPattern);
    }

    $legacyPattern = glob($cacheDir . '/daily_rainfall_*.json');
    if (is_array($legacyPattern)) {
        $candidates = array_merge($candidates, $legacyPattern);
    }

    if (empty($candidates)) {
        return null;
    }

    rsort($candidates, SORT_STRING);

    foreach ($candidates as $filePath) {
        $cached = forecast_weather_read_cache($filePath);
        if (is_array($cached) && forecast_weather_cache_is_valid($cached)) {
            return $cached;
        }
    }

    return null;
}

function forecast_fetch_river_rainfall_snapshot(): array
{
    $cacheDir = forecast_weather_cache_dir();
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0775, true);
    }

    $today = date('Y-m-d');
    $cachePath = $cacheDir . '/daily_forecast_v2_' . $today . '.json';

    $cachedToday = forecast_weather_read_cache($cachePath);
    if (is_array($cachedToday) && forecast_weather_cache_is_valid($cachedToday)) {
        return $cachedToday;
    }

    // Avoid expensive full rebuilds during user requests when a recent valid cache already exists.
    $preferCache = forecast_env_bool('FORECAST_PREFER_CACHE', true);
    $fallback = forecast_weather_latest_cache($cacheDir);
    if ($preferCache && is_array($fallback)) {
        return $fallback;
    }

    $stations = forecast_arcgis_fetch_hydrostations();
    if (empty($stations)) {
        return is_array($fallback) ? $fallback : [
            'source' => 'irrigation-department-arcgis + open-meteo-weather + open-meteo-flood',
            'fetched_at' => date('c'),
            'fetched_date' => $today,
            'rivers' => [],
        ];
    }

    $gaugeRows = forecast_arcgis_fetch_recent_gauge_rows();
    $observedByStation = forecast_observed_daily_by_station($gaugeRows);

    $riversMap = [];

    foreach ($stations as $station) {
        $riverName = (string) ($station['river_name'] ?? '');
        $riverName = $riverName !== '' ? $riverName : (string) ($station['basin_name'] ?? '');
        $riverKey = forecast_weather_station_slug($riverName);

        if (!isset($riversMap[$riverKey])) {
            $riversMap[$riverKey] = [
                'river_key' => $riverKey,
                'river_name' => $riverName,
                'stations' => [],
            ];
        }

        $built = forecast_build_station_snapshot($station, $observedByStation);

        $riversMap[$riverKey]['stations'][] = [
            'station_key' => (string) ($station['station_key'] ?? ''),
            'station_name' => (string) ($station['station_name'] ?? ''),
            'basin_name' => (string) ($station['basin_name'] ?? ''),
            'district' => '',
            'local_area' => (string) ($station['basin_name'] ?? ''),
            'latitude' => (float) ($station['latitude'] ?? 0),
            'longitude' => (float) ($station['longitude'] ?? 0),
            'alert_level' => $built['alert_level'],
            'minor_flood_level' => $built['minor_flood_level'],
            'major_flood_level' => $built['major_flood_level'],
            'latest_observed_at' => $built['latest_observed_at'],
            'observed_water_levels' => $built['observed_water_levels'],
            'discharge_forecast' => $built['discharge_forecast'],
            'rainfall_forecast' => $built['rainfall_forecast'],
            'temperature_forecast' => $built['temperature_forecast'],
            'daily' => $built['daily'],
        ];
    }

    $rivers = array_values($riversMap);

    usort($rivers, static function (array $a, array $b): int {
        return strcasecmp((string) ($a['river_name'] ?? ''), (string) ($b['river_name'] ?? ''));
    });

    foreach ($rivers as &$river) {
        $stationsList = (array) ($river['stations'] ?? []);
        usort($stationsList, static function (array $a, array $b): int {
            return strcasecmp((string) ($a['station_name'] ?? ''), (string) ($b['station_name'] ?? ''));
        });
        $river['stations'] = $stationsList;
    }
    unset($river);

    $payload = [
        'source' => 'irrigation-department-arcgis + open-meteo-weather + open-meteo-flood',
        'fetched_at' => date('c'),
        'fetched_date' => $today,
        'rivers' => $rivers,
    ];

    if (forecast_weather_cache_is_valid($payload)) {
        @file_put_contents($cachePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $payload;
    }

    if (is_array($fallback)) {
        return $fallback;
    }

    return $payload;
}
