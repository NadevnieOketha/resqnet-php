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

function forecast_hydrometric_catalog(): array
{
    return [
        'Mahaweli' => [
            [
                'station_name' => 'Manampitiya',
                'district' => 'Polonnaruwa',
                'local_area' => 'Manampitiya GN (Dimbulagala DS)',
                'latitude' => 7.86,
                'longitude' => 81.09,
            ],
            [
                'station_name' => 'Nawalapitiya',
                'district' => 'Kandy',
                'local_area' => 'Nawalapitiya Town GN Divisions',
                'latitude' => 7.05,
                'longitude' => 80.53,
            ],
            [
                'station_name' => 'Peradeniya',
                'district' => 'Kandy',
                'local_area' => 'Peradeniya Town GN Divisions',
                'latitude' => 7.27,
                'longitude' => 80.59,
            ],
            [
                'station_name' => 'Thaldena',
                'district' => 'Badulla',
                'local_area' => 'Adjacent GN in Welimada / Uva region',
                'latitude' => 6.90,
                'longitude' => 80.94,
            ],
            [
                'station_name' => 'Weraganthota',
                'district' => 'Kandy',
                'local_area' => 'Weraganthota / Adjacent GN',
                'latitude' => 7.29,
                'longitude' => 80.76,
            ],
        ],
        'Kalu' => [
            [
                'station_name' => 'Ellagawa',
                'district' => 'Ratnapura',
                'local_area' => 'Ellagawa GN area',
                'latitude' => 6.62,
                'longitude' => 80.30,
            ],
            [
                'station_name' => 'Kalawellawa',
                'district' => 'Ratnapura',
                'local_area' => 'Kalawellawa GN area',
                'latitude' => 6.64,
                'longitude' => 80.36,
            ],
            [
                'station_name' => 'Magura',
                'district' => 'Ratnapura',
                'local_area' => 'Magura GN area',
                'latitude' => 6.58,
                'longitude' => 80.29,
            ],
            [
                'station_name' => 'Putupaula',
                'district' => 'Ratnapura',
                'local_area' => 'Putupaula GN area',
                'latitude' => 6.60,
                'longitude' => 80.33,
            ],
            [
                'station_name' => 'Rathnapura',
                'district' => 'Ratnapura',
                'local_area' => 'Rathnapura Town GN divisions',
                'latitude' => 6.68,
                'longitude' => 80.40,
            ],
        ],
        'Kelani' => [
            [
                'station_name' => 'Deraniyagala',
                'district' => 'Kegalle',
                'local_area' => 'Deraniyagala GN',
                'latitude' => 6.92,
                'longitude' => 80.34,
            ],
            [
                'station_name' => 'Glencourse',
                'district' => 'Kalutara',
                'local_area' => 'Glencourse / Avissawella GN',
                'latitude' => 6.96,
                'longitude' => 80.21,
            ],
            [
                'station_name' => 'Hanwella',
                'district' => 'Colombo',
                'local_area' => 'Hanwella GN (Seethawaka DS)',
                'latitude' => 6.91,
                'longitude' => 80.08,
            ],
            [
                'station_name' => 'Holombuwa',
                'district' => 'Kegalle',
                'local_area' => 'Holombuwa GN',
                'latitude' => 7.03,
                'longitude' => 80.27,
            ],
            [
                'station_name' => 'Kithulgala',
                'district' => 'Kegalle',
                'local_area' => 'Kithulgala GN',
                'latitude' => 6.99,
                'longitude' => 80.42,
            ],
            [
                'station_name' => 'Nagalagam Street',
                'district' => 'Colombo',
                'local_area' => 'Colombo riverside GN divisions',
                'latitude' => 6.95,
                'longitude' => 79.87,
            ],
        ],
    ];
}

function forecast_http_get_json(string $url, int $timeoutSeconds = 15): ?array
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

function forecast_open_meteo_daily(float $latitude, float $longitude): array
{
    $url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'daily' => 'rain_sum,temperature_2m_max,temperature_2m_min,showers_sum',
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
    $rain = (array) ($daily['rain_sum'] ?? []);
    $showers = (array) ($daily['showers_sum'] ?? []);
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
            'rain_sum' => isset($rain[$i]) ? (float) $rain[$i] : null,
            'showers_sum' => isset($showers[$i]) ? (float) $showers[$i] : null,
            'temperature_2m_max' => isset($maxTemp[$i]) ? (float) $maxTemp[$i] : null,
            'temperature_2m_min' => isset($minTemp[$i]) ? (float) $minTemp[$i] : null,
        ];
    }

    return $series;
}

function forecast_open_meteo_flood_daily(float $latitude, float $longitude): array
{
    $url = 'https://flood-api.open-meteo.com/v1/flood?' . http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'daily' => 'river_discharge,river_discharge_max,river_discharge_min',
        'past_days' => 2,
        'forecast_days' => 8,
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
        ];
    }

    return $series;
}

function forecast_merge_hydrometric_daily(array $weatherDaily, array $floodDaily): array
{
    $dates = [];

    foreach ($weatherDaily as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '' && !in_array($date, $dates, true)) {
            $dates[] = $date;
        }
    }

    foreach ($floodDaily as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '' && !in_array($date, $dates, true)) {
            $dates[] = $date;
        }
    }

    sort($dates, SORT_STRING);

    $weatherByDate = [];
    foreach ($weatherDaily as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '') {
            $weatherByDate[$date] = $row;
        }
    }

    $floodByDate = [];
    foreach ($floodDaily as $row) {
        $date = (string) ($row['date'] ?? '');
        if ($date !== '') {
            $floodByDate[$date] = $row;
        }
    }

    $merged = [];
    foreach ($dates as $date) {
        $weather = (array) ($weatherByDate[$date] ?? []);
        $flood = (array) ($floodByDate[$date] ?? []);

        $merged[] = [
            'date' => $date,
            'rain_sum' => array_key_exists('rain_sum', $weather) ? (is_null($weather['rain_sum']) ? null : (float) $weather['rain_sum']) : null,
            'showers_sum' => array_key_exists('showers_sum', $weather) ? (is_null($weather['showers_sum']) ? null : (float) $weather['showers_sum']) : null,
            'temperature_2m_max' => array_key_exists('temperature_2m_max', $weather) ? (is_null($weather['temperature_2m_max']) ? null : (float) $weather['temperature_2m_max']) : null,
            'temperature_2m_min' => array_key_exists('temperature_2m_min', $weather) ? (is_null($weather['temperature_2m_min']) ? null : (float) $weather['temperature_2m_min']) : null,
            'river_discharge' => array_key_exists('river_discharge', $flood) ? (is_null($flood['river_discharge']) ? null : (float) $flood['river_discharge']) : null,
            'river_discharge_max' => array_key_exists('river_discharge_max', $flood) ? (is_null($flood['river_discharge_max']) ? null : (float) $flood['river_discharge_max']) : null,
            'river_discharge_min' => array_key_exists('river_discharge_min', $flood) ? (is_null($flood['river_discharge_min']) ? null : (float) $flood['river_discharge_min']) : null,
        ];
    }

    return $merged;
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

    $hasDailyRows = false;
    $hasFloodField = false;

    foreach ($payload['rivers'] as $river) {
        $stations = (array) ($river['stations'] ?? []);
        foreach ($stations as $station) {
            $dailyRows = (array) ($station['daily'] ?? []);
            if (!empty($dailyRows)) {
                $hasDailyRows = true;

                foreach ($dailyRows as $row) {
                    if (is_array($row) && array_key_exists('river_discharge', $row)) {
                        $hasFloodField = true;
                        break;
                    }
                }
            }

            if ($hasDailyRows && $hasFloodField) {
                return true;
            }
        }
    }

    return $hasDailyRows && $hasFloodField;
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
    $candidates = glob($cacheDir . '/daily_rainfall_*.json');
    if (!is_array($candidates) || empty($candidates)) {
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
    $cachePath = $cacheDir . '/daily_rainfall_' . $today . '.json';
    $cachedToday = forecast_weather_read_cache($cachePath);
    if (is_array($cachedToday) && forecast_weather_cache_is_valid($cachedToday)) {
        return $cachedToday;
    }

    $catalog = forecast_hydrometric_catalog();
    $rivers = [];

    foreach ($catalog as $riverName => $stations) {
        $riverEntry = [
            'river_key' => forecast_weather_station_slug((string) $riverName),
            'river_name' => (string) $riverName,
            'stations' => [],
        ];

        foreach ($stations as $station) {
            $stationName = (string) ($station['station_name'] ?? 'Station');
            $weatherDaily = forecast_open_meteo_daily(
                (float) ($station['latitude'] ?? 0),
                (float) ($station['longitude'] ?? 0)
            );
            $floodDaily = forecast_open_meteo_flood_daily(
                (float) ($station['latitude'] ?? 0),
                (float) ($station['longitude'] ?? 0)
            );
            $daily = forecast_merge_hydrometric_daily($weatherDaily, $floodDaily);

            $riverEntry['stations'][] = [
                'station_key' => forecast_weather_station_slug($stationName),
                'station_name' => $stationName,
                'district' => (string) ($station['district'] ?? ''),
                'local_area' => (string) ($station['local_area'] ?? ''),
                'latitude' => (float) ($station['latitude'] ?? 0),
                'longitude' => (float) ($station['longitude'] ?? 0),
                'daily' => $daily,
            ];
        }

        $rivers[] = $riverEntry;
    }

    $payload = [
        'source' => 'open-meteo',
        'fetched_at' => date('c'),
        'fetched_date' => $today,
        'rivers' => $rivers,
    ];

    if (forecast_weather_cache_is_valid($payload)) {
        @file_put_contents($cachePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $payload;
    }

    $fallback = forecast_weather_latest_cache($cacheDir);
    if (is_array($fallback)) {
        return $fallback;
    }

    return $payload;
}
