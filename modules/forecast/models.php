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

function forecast_default_selection(array $snapshot, string $requestedRiverKey = '', string $requestedStationKey = '', ?array $profile = null): array
{
    $rivers = (array) ($snapshot['rivers'] ?? []);
    if (empty($rivers)) {
        return [
            'river_key' => '',
            'station_key' => '',
        ];
    }

    if ($requestedRiverKey !== '' || $requestedStationKey !== '') {
        $selection = forecast_selection_from_request($snapshot, $requestedRiverKey, $requestedStationKey);
        if ((string) ($selection['river_key'] ?? '') !== '' && (string) ($selection['station_key'] ?? '') !== '') {
            return $selection;
        }
    }

    $mappedStationKey = forecast_station_key_from_profile($profile);
    if ($mappedStationKey !== '') {
        $mappedSelection = forecast_selection_from_station_key($snapshot, $mappedStationKey);
        if ((string) ($mappedSelection['river_key'] ?? '') !== '' && (string) ($mappedSelection['station_key'] ?? '') !== '') {
            return $mappedSelection;
        }
    }

    return forecast_fallback_selection($snapshot);
}

function forecast_selection_from_request(array $snapshot, string $requestedRiverKey, string $requestedStationKey): array
{
    $rivers = (array) ($snapshot['rivers'] ?? []);

    if ($requestedRiverKey !== '') {
        foreach ($rivers as $river) {
            $riverKey = (string) ($river['river_key'] ?? '');
            if ($riverKey !== $requestedRiverKey) {
                continue;
            }

            $stations = (array) ($river['stations'] ?? []);
            if ($requestedStationKey !== '') {
                foreach ($stations as $station) {
                    if ((string) ($station['station_key'] ?? '') === $requestedStationKey) {
                        return [
                            'river_key' => $riverKey,
                            'station_key' => $requestedStationKey,
                        ];
                    }
                }
            }

            $fallbackStation = forecast_first_station_with_data($stations);
            if ($fallbackStation !== '') {
                return [
                    'river_key' => $riverKey,
                    'station_key' => $fallbackStation,
                ];
            }

            if (!empty($stations)) {
                return [
                    'river_key' => $riverKey,
                    'station_key' => (string) ($stations[0]['station_key'] ?? ''),
                ];
            }
        }
    }

    if ($requestedStationKey !== '') {
        return forecast_selection_from_station_key($snapshot, $requestedStationKey);
    }

    return [
        'river_key' => '',
        'station_key' => '',
    ];
}

function forecast_selection_from_station_key(array $snapshot, string $stationKey): array
{
    $target = forecast_normalize_text($stationKey);
    if ($target === '') {
        return [
            'river_key' => '',
            'station_key' => '',
        ];
    }

    foreach ((array) ($snapshot['rivers'] ?? []) as $river) {
        $riverKey = (string) ($river['river_key'] ?? '');
        foreach ((array) ($river['stations'] ?? []) as $station) {
            $candidate = forecast_normalize_text((string) ($station['station_key'] ?? ''));
            if ($candidate === $target) {
                return [
                    'river_key' => $riverKey,
                    'station_key' => (string) ($station['station_key'] ?? ''),
                ];
            }
        }
    }

    return [
        'river_key' => '',
        'station_key' => '',
    ];
}

function forecast_fallback_selection(array $snapshot): array
{
    $rivers = (array) ($snapshot['rivers'] ?? []);
    if (empty($rivers)) {
        return [
            'river_key' => '',
            'station_key' => '',
        ];
    }

    foreach ($rivers as $river) {
        $stations = (array) ($river['stations'] ?? []);
        $stationKey = forecast_first_station_with_data($stations);
        if ($stationKey !== '') {
            return [
                'river_key' => (string) ($river['river_key'] ?? ''),
                'station_key' => $stationKey,
            ];
        }
    }

    $firstRiver = $rivers[0];
    $firstStations = (array) ($firstRiver['stations'] ?? []);

    return [
        'river_key' => (string) ($firstRiver['river_key'] ?? ''),
        'station_key' => (string) (($firstStations[0]['station_key'] ?? '') ?: ''),
    ];
}

function forecast_first_station_with_data(array $stations): string
{
    foreach ($stations as $station) {
        if (count((array) ($station['daily_weather'] ?? [])) > 0) {
            return (string) ($station['station_key'] ?? '');
        }
    }

    return '';
}

function forecast_station_key_from_profile(?array $profile): string
{
    if (!is_array($profile) || empty($profile)) {
        return '';
    }

    $district = forecast_normalize_text((string) ($profile['district'] ?? ''));
    $candidates = forecast_profile_location_candidates($profile);
    if (empty($candidates)) {
        return '';
    }

    $bestStation = '';
    $bestScore = 0;

    foreach (forecast_station_mapping_groups() as $group) {
        $groupDistrict = forecast_normalize_text((string) ($group['district'] ?? ''));
        $stationKey = (string) ($group['station_key'] ?? '');
        $areas = (array) ($group['areas'] ?? []);

        if ($stationKey === '' || empty($areas)) {
            continue;
        }

        $score = 0;
        if ($district !== '' && $groupDistrict !== '' && $district === $groupDistrict) {
            $score += 60;
        }

        foreach ($areas as $area) {
            $areaNorm = forecast_normalize_text((string) $area);
            if ($areaNorm === '') {
                continue;
            }

            foreach ($candidates as $candidate) {
                if ($candidate === $areaNorm) {
                    $score += 120;
                } elseif (
                    strlen($candidate) >= 4
                    && (str_contains($candidate, $areaNorm) || str_contains($areaNorm, $candidate))
                ) {
                    $score += 40;
                }
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestStation = $stationKey;
        }
    }

    return $bestScore > 0 ? $bestStation : '';
}

function forecast_profile_location_candidates(array $profile): array
{
    $fields = [
        (string) ($profile['gn_division'] ?? ''),
        (string) ($profile['city'] ?? ''),
        (string) ($profile['address'] ?? ''),
        (string) ($profile['street'] ?? ''),
        (string) ($profile['organization_name'] ?? ''),
    ];

    $candidates = [];
    foreach ($fields as $value) {
        $value = trim($value);
        if ($value === '') {
            continue;
        }

        $normalized = forecast_normalize_text($value);
        if ($normalized !== '') {
            $candidates[$normalized] = true;
        }

        $parts = preg_split('/[,;\/\\|\-]+/', $value) ?: [];
        foreach ($parts as $part) {
            $partNorm = forecast_normalize_text((string) $part);
            if ($partNorm !== '') {
                $candidates[$partNorm] = true;
            }
        }
    }

    return array_keys($candidates);
}

function forecast_normalize_text(string $value): string
{
    $normalized = strtolower(trim($value));
    $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? '';
    $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    return $normalized;
}

function forecast_station_mapping_groups(): array
{
    return [
        [
            'district' => 'Colombo',
            'station_key' => 'nagalagam_street',
            'areas' => ['Colombo', 'Kolonnawa', 'Nagalagam Street', 'Sri Jayawardenepura Kotte', 'Thimbirigasyaya', 'Dehiwala', 'Ratmalana', 'Moratuwa'],
        ],
        [
            'district' => 'Colombo',
            'station_key' => 'hanwella',
            'areas' => ['Kaduwela', 'Maharagama', 'Kesbewa', 'Homagama', 'Padukka', 'Hanwella (Seethawaka)', 'Hanwella'],
        ],
        [
            'district' => 'Gampaha',
            'station_key' => 'nagalagam_street',
            'areas' => ['Wattala', 'Kelaniya', 'Biyagama', 'Ja-Ela'],
        ],
        [
            'district' => 'Gampaha',
            'station_key' => 'hanwella',
            'areas' => ['Gampaha', 'Mahara', 'Dompe', 'Attanagalla'],
        ],
        [
            'district' => 'Gampaha',
            'station_key' => 'holombuwa',
            'areas' => ['Negombo', 'Katana', 'Divulapitiya', 'Mirigama', 'Minuwangoda'],
        ],
        [
            'district' => 'Kalutara',
            'station_key' => 'glencourse',
            'areas' => ['Avissawella', 'Glencourse'],
        ],
        [
            'district' => 'Kalutara',
            'station_key' => 'ellagawa',
            'areas' => ['Ingiriya', 'Horana'],
        ],
        [
            'district' => 'Kalutara',
            'station_key' => 'kalawellawa',
            'areas' => ['Bandaragama', 'Millaniya', 'Madurawala', 'Bulathsinhala'],
        ],
        [
            'district' => 'Kalutara',
            'station_key' => 'putupaula',
            'areas' => ['Panadura', 'Kalutara', 'Beruwala', 'Dodangoda'],
        ],
        [
            'district' => 'Kalutara',
            'station_key' => 'magura',
            'areas' => ['Matugama', 'Agalawatta', 'Palindanuwara', 'Walallavita'],
        ],
        [
            'district' => 'Kandy',
            'station_key' => 'nawalapitiya',
            'areas' => ['Nawalapitiya', 'Pasbage Korale', 'Ganga Ihala Korale'],
        ],
        [
            'district' => 'Kandy',
            'station_key' => 'peradeniya',
            'areas' => ['Peradeniya', 'Kandy Four Gravets', 'Gangawata Korale', 'Yatinuwara', 'Udunuwara', 'Doluwa', 'Hatharaliyadda', 'Thumpane', 'Pujapitiya', 'Akurana', 'Udapalatha', 'Pathahewaheta', 'Delthota'],
        ],
        [
            'district' => 'Kandy',
            'station_key' => 'weraganthota',
            'areas' => ['Weraganthota', 'Minipe', 'Ududumbara', 'Medadumbara', 'Pathadumbara', 'Panvila', 'Kundasale'],
        ],
        [
            'district' => 'Kegalle',
            'station_key' => 'deraniyagala',
            'areas' => ['Deraniyagala'],
        ],
        [
            'district' => 'Kegalle',
            'station_key' => 'kithulgala',
            'areas' => ['Kithulgala', 'Yatiyanthota', 'Ruwanwella'],
        ],
        [
            'district' => 'Kegalle',
            'station_key' => 'holombuwa',
            'areas' => ['Holombuwa', 'Warakapola', 'Mawanella'],
        ],
        [
            'district' => 'Ratnapura',
            'station_key' => 'rathnapura',
            'areas' => ['Rathnapura', 'Kuruwita', 'Pelmadulla', 'Balangoda', 'Godakawela'],
        ],
        [
            'district' => 'Ratnapura',
            'station_key' => 'ellagawa',
            'areas' => ['Eheliyagoda', 'Ellagawa'],
        ],
        [
            'district' => 'Ratnapura',
            'station_key' => 'kalawellawa',
            'areas' => ['Kalawellawa'],
        ],
        [
            'district' => 'Ratnapura',
            'station_key' => 'magura',
            'areas' => ['Magura'],
        ],
        [
            'district' => 'Ratnapura',
            'station_key' => 'putupaula',
            'areas' => ['Putupaula'],
        ],
        [
            'district' => 'Polonnaruwa',
            'station_key' => 'manampitiya',
            'areas' => ['Manampitiya', 'Dimbulagala', 'Polonnaruwa', 'Hingurakgoda', 'Lankapura', 'Welikanda'],
        ],
        [
            'district' => 'Badulla',
            'station_key' => 'thaldena',
            'areas' => ['Thaldena', 'Welimada', 'Badulla', 'Passara', 'Bandarawela', 'Hali-Ela'],
        ],
        [
            'district' => 'Galle',
            'station_key' => 'putupaula',
            'areas' => ['Bentota', 'Balapitiya', 'Karandeniya', 'Elpitiya', 'Ambalangoda', 'Hikkaduwa', 'Gonapinuwala', 'Baddegama', 'Welivitiya-Divithura', 'Bope-Poddala', 'Galle Four Gravets', 'Akmeemana', 'Habaraduwa', 'Talape'],
        ],
        [
            'district' => 'Galle',
            'station_key' => 'magura',
            'areas' => ['Nagoda', 'Yakkalamulla', 'Imaduwa', 'Neluwa', 'Thawalama'],
        ],
        [
            'district' => 'Matara',
            'station_key' => 'magura',
            'areas' => ['Pitabeddara', 'Kotapola', 'Pasgoda', 'Mulatiyana', 'Akuressa'],
        ],
        [
            'district' => 'Matara',
            'station_key' => 'putupaula',
            'areas' => ['Athuraliya', 'Welipitiya', 'Malimbada', 'Kamburupitiya', 'Hakmana', 'Kirinda-Puhulwella', 'Thihagoda', 'Weligama', 'Matara Four Gravets', 'Devinuwara', 'Dickwella'],
        ],
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
