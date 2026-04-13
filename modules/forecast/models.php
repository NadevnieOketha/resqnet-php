<?php

/**
 * Forecast Module - Models
 *
 * Builds daily rainfall and temperature snapshots from Open-Meteo
 * for configured river basin hydrometric stations.
 */

function forecast_station_catalog(): array
{
    return [
        [
            'river_key' => 'mahaweli',
            'river_name' => 'Mahaweli',
            'stations' => [
                [
                    'station_key' => 'manampitiya',
                    'station_name' => 'Manampitiya',
                    'district' => 'Polonnaruwa',
                    'local_area' => 'Manampitiya GN (Dimbulagala DS)',
                    'latitude' => 7.9138,
                    'longitude' => 81.0903,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'nawalapitiya',
                    'station_name' => 'Nawalapitiya',
                    'district' => 'Kandy',
                    'local_area' => 'Nawalapitiya Town GN Divisions',
                    'latitude' => 7.0492,
                    'longitude' => 80.5318,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'peradeniya',
                    'station_name' => 'Peradeniya',
                    'district' => 'Kandy',
                    'local_area' => 'Peradeniya Town GN Divisions',
                    'latitude' => 7.2660,
                    'longitude' => 80.5966,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'thaldena',
                    'station_name' => 'Thaldena',
                    'district' => 'Badulla',
                    'local_area' => 'Adjacent GN in Welimada / Uva region',
                    'latitude' => 6.9013,
                    'longitude' => 80.9111,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'weraganthota',
                    'station_name' => 'Weraganthota',
                    'district' => 'Kandy',
                    'local_area' => 'Weraganthota / adjacent GN',
                    'latitude' => 7.2951,
                    'longitude' => 80.7362,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
            ],
        ],
        [
            'river_key' => 'kalu',
            'river_name' => 'Kalu',
            'stations' => [
                [
                    'station_key' => 'ellagawa',
                    'station_name' => 'Ellagawa',
                    'district' => 'Ratnapura',
                    'local_area' => 'Ellagawa GN area',
                    'latitude' => 6.6027,
                    'longitude' => 80.4515,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'kalawellawa',
                    'station_name' => 'Kalawellawa',
                    'district' => 'Ratnapura',
                    'local_area' => 'Kalawellawa GN area',
                    'latitude' => 6.6179,
                    'longitude' => 80.4163,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'magura',
                    'station_name' => 'Magura',
                    'district' => 'Ratnapura',
                    'local_area' => 'Magura GN area',
                    'latitude' => 6.5758,
                    'longitude' => 80.3840,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'putupaula',
                    'station_name' => 'Putupaula',
                    'district' => 'Ratnapura',
                    'local_area' => 'Putupaula GN area',
                    'latitude' => 6.5694,
                    'longitude' => 80.5252,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'rathnapura',
                    'station_name' => 'Rathnapura',
                    'district' => 'Ratnapura',
                    'local_area' => 'Rathnapura Town GN divisions',
                    'latitude' => 6.6828,
                    'longitude' => 80.3992,
                    'description' => 'Kalu basin hydrometric station',
                ],
            ],
        ],
        [
            'river_key' => 'kelani',
            'river_name' => 'Kelani',
            'stations' => [
                [
                    'station_key' => 'deraniyagala',
                    'station_name' => 'Deraniyagala',
                    'district' => 'Kegalle',
                    'local_area' => 'Deraniyagala GN',
                    'latitude' => 6.9244,
                    'longitude' => 80.3357,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'glencourse',
                    'station_name' => 'Glencourse',
                    'district' => 'Kalutara',
                    'local_area' => 'Glencourse / Avissawella GN',
                    'latitude' => 6.9623,
                    'longitude' => 80.2066,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'hanwella',
                    'station_name' => 'Hanwella',
                    'district' => 'Colombo',
                    'local_area' => 'Hanwella GN (Seethawaka DS)',
                    'latitude' => 6.9097,
                    'longitude' => 80.0814,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'holombuwa',
                    'station_name' => 'Holombuwa',
                    'district' => 'Kegalle',
                    'local_area' => 'Holombuwa GN',
                    'latitude' => 7.1171,
                    'longitude' => 80.2331,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'kithulgala',
                    'station_name' => 'Kithulgala',
                    'district' => 'Kegalle',
                    'local_area' => 'Kithulgala GN',
                    'latitude' => 6.9905,
                    'longitude' => 80.4172,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'nagalagam_street',
                    'station_name' => 'Nagalagam Street',
                    'district' => 'Colombo',
                    'local_area' => 'Colombo riverside GN divisions',
                    'latitude' => 6.9494,
                    'longitude' => 79.8631,
                    'description' => 'Kelani basin hydrometric station',
                ],
            ],
        ],
    ];
}

function forecast_snapshot(): array
{
    $timeZone = new DateTimeZone('Asia/Colombo');
    $now = new DateTimeImmutable('now', $timeZone);

    $fromDate = $now->modify('-2 days')->format('Y-m-d');
    $toDate = $now->modify('+7 days')->format('Y-m-d');

    $cacheDir = BASE_PATH . '/storage/logs/open_meteo';
    $cacheFile = $cacheDir . '/rain_temp_snapshot_' . $now->format('Y-m-d') . '.json';

    if (is_file($cacheFile)) {
        $cachedJson = @file_get_contents($cacheFile);
        $cachedData = is_string($cachedJson) ? json_decode($cachedJson, true) : null;
        if (
            is_array($cachedData)
            && is_array($cachedData['rivers'] ?? null)
            && forecast_snapshot_has_weather_data($cachedData)
        ) {
            return $cachedData;
        }
    }

    $rivers = [];
    foreach (forecast_station_catalog() as $river) {
        $stations = [];

        foreach ((array) ($river['stations'] ?? []) as $station) {
            $dailyWeather = forecast_fetch_station_daily_weather(
                (float) ($station['latitude'] ?? 0),
                (float) ($station['longitude'] ?? 0),
                'Asia/Colombo'
            );

            // Retry once because occasional transient API/network failures can return empty rows.
            if (empty($dailyWeather)) {
                $dailyWeather = forecast_fetch_station_daily_weather(
                    (float) ($station['latitude'] ?? 0),
                    (float) ($station['longitude'] ?? 0),
                    'Asia/Colombo'
                );
            }

            $stations[] = [
                'station_key' => (string) ($station['station_key'] ?? ''),
                'station_name' => (string) ($station['station_name'] ?? ''),
                'district' => (string) ($station['district'] ?? ''),
                'local_area' => (string) ($station['local_area'] ?? ''),
                'latitude' => (float) ($station['latitude'] ?? 0),
                'longitude' => (float) ($station['longitude'] ?? 0),
                'description' => (string) ($station['description'] ?? ''),
                'daily_weather' => $dailyWeather,
            ];
        }

        $rivers[] = [
            'river_key' => (string) ($river['river_key'] ?? ''),
            'river_name' => (string) ($river['river_name'] ?? ''),
            'stations' => $stations,
        ];
    }

    $snapshot = [
        'source' => 'Open-Meteo Weather Forecast API',
        'endpoint' => 'https://api.open-meteo.com/v1/forecast',
        'fetched_at' => $now->format('Y-m-d H:i:s T'),
        'window' => [
            'from' => $fromDate,
            'to' => $toDate,
        ],
        'rivers' => $rivers,
    ];

    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    @file_put_contents(
        $cacheFile,
        json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );

    return $snapshot;
}

function forecast_default_selection(array $snapshot, string $requestedRiverKey = '', string $requestedStationKey = ''): array
{
    $rivers = (array) ($snapshot['rivers'] ?? []);
    if (empty($rivers)) {
        return [
            'river_key' => '',
            'station_key' => '',
        ];
    }

    $selectedRiver = null;
    if ($requestedRiverKey !== '') {
        foreach ($rivers as $river) {
            if ((string) ($river['river_key'] ?? '') === $requestedRiverKey) {
                $selectedRiver = $river;
                break;
            }
        }
    }

    if (!$selectedRiver) {
        $selectedRiver = $rivers[0];
    }

    $stations = (array) ($selectedRiver['stations'] ?? []);
    $selectedStationKey = '';

    if ($requestedStationKey !== '') {
        foreach ($stations as $station) {
            if ((string) ($station['station_key'] ?? '') === $requestedStationKey) {
                $selectedStationKey = $requestedStationKey;
                break;
            }
        }
    }

    if ($selectedStationKey === '' && !empty($stations)) {
        foreach ($stations as $station) {
            if (count((array) ($station['daily_weather'] ?? [])) > 0) {
                $selectedStationKey = (string) ($station['station_key'] ?? '');
                break;
            }
        }

        if ($selectedStationKey === '') {
            $selectedStationKey = (string) ($stations[0]['station_key'] ?? '');
        }
    }

    return [
        'river_key' => (string) ($selectedRiver['river_key'] ?? ''),
        'station_key' => $selectedStationKey,
    ];
}

function forecast_fetch_station_daily_weather(float $latitude, float $longitude, string $timeZone = 'Asia/Colombo'): array
{
    if ($latitude === 0.0 && $longitude === 0.0) {
        return [];
    }

    $query = http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'daily' => 'rain_sum,showers_sum,temperature_2m_max,temperature_2m_min',
        'timezone' => $timeZone,
        'past_days' => 2,
        'forecast_days' => 8,
    ], '', '&', PHP_QUERY_RFC3986);

    $url = 'https://api.open-meteo.com/v1/forecast?' . $query;
    $payload = forecast_http_get_json($url);
    if (!is_array($payload)) {
        return [];
    }

    $daily = (array) ($payload['daily'] ?? []);
    $dates = (array) ($daily['time'] ?? []);

    $today = (new DateTimeImmutable('now', new DateTimeZone($timeZone)))->format('Y-m-d');

    $rows = [];
    foreach ($dates as $index => $date) {
        $dateString = trim((string) $date);
        if ($dateString === '') {
            continue;
        }

        $rainSum = forecast_nullable_float($daily['rain_sum'][$index] ?? null);
        $showersSum = forecast_nullable_float($daily['showers_sum'][$index] ?? null);

        $rows[] = [
            'date' => $dateString,
            'rain_sum' => $rainSum,
            'showers_sum' => $showersSum,
            'precipitation_sum' => $rainSum !== null ? $rainSum : $showersSum,
            'temperature_2m_max' => forecast_nullable_float($daily['temperature_2m_max'][$index] ?? null),
            'temperature_2m_min' => forecast_nullable_float($daily['temperature_2m_min'][$index] ?? null),
            'is_forecast_day' => $dateString > $today,
        ];
    }

    return $rows;
}

function forecast_http_get_json(string $url, int $timeout = 20): ?array
{
    $body = null;

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_USERAGENT => 'resqnet-forecast/1.0',
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
                'header' => "User-Agent: resqnet-forecast/1.0\r\n",
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

function forecast_nullable_float(mixed $value): ?float
{
    if ($value === null || $value === '' || !is_numeric($value)) {
        return null;
    }

    return (float) $value;
}

function forecast_snapshot_has_weather_data(array $snapshot): bool
{
    foreach ((array) ($snapshot['rivers'] ?? []) as $river) {
        foreach ((array) ($river['stations'] ?? []) as $station) {
            if (count((array) ($station['daily_weather'] ?? [])) > 0) {
                return true;
            }
        }
    }

    return false;
}
