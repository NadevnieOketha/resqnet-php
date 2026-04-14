<?php

/**
 * Forecast Module - Models
 *
 * Builds weather snapshots from Open-Meteo and daily river water level
 * snapshots from Irrigation Department ArcGIS services.
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
                    'latitude' => 7.912987,
                    'longitude' => 81.090594,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'nawalapitiya',
                    'station_name' => 'Nawalapitiya',
                    'district' => 'Kandy',
                    'local_area' => 'Nawalapitiya Town GN Divisions',
                    'latitude' => 7.04894547,
                    'longitude' => 80.53616209,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'peradeniya',
                    'station_name' => 'Peradeniya',
                    'district' => 'Kandy',
                    'local_area' => 'Peradeniya Town GN Divisions',
                    'latitude' => 7.269059,
                    'longitude' => 80.593005,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'thaldena',
                    'station_name' => 'Thaldena',
                    'district' => 'Badulla',
                    'local_area' => 'Adjacent GN in Welimada / Uva region',
                    'latitude' => 7.090653,
                    'longitude' => 81.048886,
                    'description' => 'Mahaweli basin hydrometric station',
                ],
                [
                    'station_key' => 'weraganthota',
                    'station_name' => 'Weraganthota',
                    'district' => 'Kandy',
                    'local_area' => 'Weraganthota / adjacent GN',
                    'latitude' => 7.31647,
                    'longitude' => 80.98911,
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
                    'latitude' => 6.731554,
                    'longitude' => 80.218254,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'kalawellawa',
                    'station_name' => 'Kalawellawa',
                    'district' => 'Ratnapura',
                    'local_area' => 'Kalawellawa GN area',
                    'latitude' => 6.631166,
                    'longitude' => 80.16069,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'magura',
                    'station_name' => 'Magura',
                    'district' => 'Ratnapura',
                    'local_area' => 'Magura GN area',
                    'latitude' => 6.513727,
                    'longitude' => 80.243935,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'putupaula',
                    'station_name' => 'Putupaula',
                    'district' => 'Ratnapura',
                    'local_area' => 'Putupaula GN area',
                    'latitude' => 6.614324,
                    'longitude' => 80.061511,
                    'description' => 'Kalu basin hydrometric station',
                ],
                [
                    'station_key' => 'rathnapura',
                    'station_name' => 'Rathnapura',
                    'district' => 'Ratnapura',
                    'local_area' => 'Rathnapura Town GN divisions',
                    'latitude' => 6.679067,
                    'longitude' => 80.397235,
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
                    'latitude' => 6.925934,
                    'longitude' => 80.339212,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'glencourse',
                    'station_name' => 'Glencourse',
                    'district' => 'Kalutara',
                    'local_area' => 'Glencourse / Avissawella GN',
                    'latitude' => 6.976981,
                    'longitude' => 80.194247,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'hanwella',
                    'station_name' => 'Hanwella',
                    'district' => 'Colombo',
                    'local_area' => 'Hanwella GN (Seethawaka DS)',
                    'latitude' => 6.9097,
                    'longitude' => 80.083126,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'holombuwa',
                    'station_name' => 'Holombuwa',
                    'district' => 'Kegalle',
                    'local_area' => 'Holombuwa GN',
                    'latitude' => 7.188201,
                    'longitude' => 80.266331,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'kithulgala',
                    'station_name' => 'Kithulgala',
                    'district' => 'Kegalle',
                    'local_area' => 'Kithulgala GN',
                    'latitude' => 6.991259,
                    'longitude' => 80.419213,
                    'description' => 'Kelani basin hydrometric station',
                ],
                [
                    'station_key' => 'nagalagam_street',
                    'station_name' => 'Nagalagam Street',
                    'district' => 'Colombo',
                    'local_area' => 'Colombo riverside GN divisions',
                    'latitude' => 6.958265,
                    'longitude' => 79.878642,
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
    $observedToDate = $now->format('Y-m-d');

    $cacheDir = BASE_PATH . '/storage/logs/open_meteo';
    $cacheFile = $cacheDir . '/rain_temp_snapshot_' . $now->format('Y-m-d') . '.json';

    if (is_file($cacheFile)) {
        $cachedJson = @file_get_contents($cacheFile);
        $cachedData = is_string($cachedJson) ? json_decode($cachedJson, true) : null;
        if (
            is_array($cachedData)
            && (int) ($cachedData['version'] ?? 1) >= 3
            && is_array($cachedData['rivers'] ?? null)
            && forecast_snapshot_has_weather_data($cachedData)
        ) {
            forecast_dispatch_sms_alerts($cachedData);
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

            $dailyWaterLevels = forecast_fetch_station_daily_water_levels(
                (string) ($station['station_name'] ?? ''),
                $fromDate,
                $observedToDate,
                'Asia/Colombo'
            );

            if (empty($dailyWaterLevels)) {
                $dailyWaterLevels = forecast_fetch_station_daily_water_levels(
                    (string) ($station['station_name'] ?? ''),
                    $fromDate,
                    $observedToDate,
                    'Asia/Colombo'
                );
            }

            $dailyDischarge = forecast_fetch_station_daily_discharge(
                (float) ($station['latitude'] ?? 0),
                (float) ($station['longitude'] ?? 0),
                'Asia/Colombo'
            );

            if (empty($dailyDischarge)) {
                $dailyDischarge = forecast_fetch_station_daily_discharge(
                    (float) ($station['latitude'] ?? 0),
                    (float) ($station['longitude'] ?? 0),
                    'Asia/Colombo'
                );
            }

            $dischargeThresholds = forecast_discharge_threshold_for_station($station);

            $stations[] = [
                'station_key' => (string) ($station['station_key'] ?? ''),
                'station_name' => (string) ($station['station_name'] ?? ''),
                'district' => (string) ($station['district'] ?? ''),
                'local_area' => (string) ($station['local_area'] ?? ''),
                'latitude' => (float) ($station['latitude'] ?? 0),
                'longitude' => (float) ($station['longitude'] ?? 0),
                'description' => (string) ($station['description'] ?? ''),
                'daily_weather' => $dailyWeather,
                'daily_water_levels' => $dailyWaterLevels,
                'daily_discharge' => $dailyDischarge,
                'discharge_thresholds' => $dischargeThresholds,
            ];
        }

        $rivers[] = [
            'river_key' => (string) ($river['river_key'] ?? ''),
            'river_name' => (string) ($river['river_name'] ?? ''),
            'stations' => $stations,
        ];
    }

    $snapshot = [
        'version' => 3,
        'source' => [
            'weather' => 'Open-Meteo Weather Forecast API',
            'discharge' => 'Open-Meteo Flood API (GloFAS)',
            'water_levels' => 'Irrigation Department ArcGIS Realtime Water Level Dashboard',
        ],
        'endpoint' => [
            'weather' => 'https://api.open-meteo.com/v1/forecast',
            'discharge' => 'https://flood-api.open-meteo.com/v1/flood',
            'water_levels' => 'https://services3.arcgis.com/J7ZFXmR8rSmQ3FGf/arcgis/rest/services/gauges_2_view/FeatureServer/0',
        ],
        'fetched_at' => $now->format('Y-m-d H:i:s T'),
        'window' => [
            'from' => $fromDate,
            'to' => $toDate,
            'observed_to' => $observedToDate,
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

    forecast_dispatch_sms_alerts($snapshot);

    return $snapshot;
}

function forecast_dispatch_sms_alerts(array $snapshot): void
{
    if (!function_exists('sms_alert_forecast_dispatch')) {
        return;
    }

    try {
        sms_alert_forecast_dispatch($snapshot);
    } catch (Throwable $e) {
        error_log('Forecast SMS dispatch failed: ' . $e->getMessage());
    }
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

function forecast_sms_alert_supported_role(string $role): bool
{
    return in_array($role, ['general', 'volunteer'], true);
}

function forecast_sms_alert_preference(int $userId, string $role, array $snapshot, ?array $profile = null): array
{
    $fallback = forecast_default_selection($snapshot, '', '', $profile);
    $preference = [
        'enabled' => false,
        'river_key' => (string) ($fallback['river_key'] ?? ''),
        'station_key' => '',
        'fallback_river_key' => (string) ($fallback['river_key'] ?? ''),
        'fallback_station_key' => (string) ($fallback['station_key'] ?? ''),
    ];

    if (!forecast_sms_alert_supported_role($role) || $userId <= 0) {
        return $preference;
    }

    forecast_sms_alert_ensure_table();

    $row = db_fetch(
        'SELECT sms_alert, river_key, station_key FROM forecast_sms_alert_subscription WHERE user_id = ? LIMIT 1',
        [$userId]
    );

    if (!is_array($row)) {
        if ($role === 'general' && !empty($profile['sms_alert'])) {
            $preference['enabled'] = true;
            $preference['station_key'] = (string) ($fallback['station_key'] ?? '');
        }
        return $preference;
    }

    $enabled = (int) ($row['sms_alert'] ?? 0) === 1;
    $savedStation = trim((string) ($row['station_key'] ?? ''));
    $savedRiver = trim((string) ($row['river_key'] ?? ''));
    $resolved = [
        'river_key' => '',
        'station_key' => '',
    ];

    if ($savedStation !== '') {
        $resolved = forecast_selection_from_station_key($snapshot, $savedStation);
    } elseif ($savedRiver !== '') {
        $resolved = forecast_selection_from_request($snapshot, $savedRiver, '');
    }

    if ((string) ($resolved['station_key'] ?? '') === '') {
        $resolved = $fallback;
    }

    $preference['enabled'] = $enabled;
    $preference['river_key'] = (string) ($resolved['river_key'] ?? (string) ($fallback['river_key'] ?? ''));
    $preference['station_key'] = $enabled ? (string) ($resolved['station_key'] ?? '') : '';

    return $preference;
}

function forecast_sms_alert_save_preference(
    int $userId,
    string $role,
    bool $enabled,
    string $requestedRiverKey,
    string $requestedStationKey,
    array $snapshot,
    ?array $profile = null
): array {
    if (!forecast_sms_alert_supported_role($role) || $userId <= 0) {
        return [
            'enabled' => false,
            'river_key' => '',
            'station_key' => '',
            'used_fallback' => false,
        ];
    }

    forecast_sms_alert_ensure_table();

    $finalEnabled = $enabled;
    $usedFallback = false;
    $selection = [
        'river_key' => '',
        'station_key' => '',
    ];

    if ($finalEnabled) {
        $requestedStationKey = trim($requestedStationKey);
        $requestedRiverKey = trim($requestedRiverKey);

        if ($requestedStationKey !== '') {
            $selection = forecast_selection_from_station_key($snapshot, $requestedStationKey);
        }

        if ((string) ($selection['station_key'] ?? '') === '') {
            $selection = forecast_default_selection($snapshot, '', '', $profile);
            $usedFallback = true;
        }

        if ((string) ($selection['station_key'] ?? '') === '') {
            $finalEnabled = false;
        }
    }

    $riverKey = $finalEnabled ? (string) ($selection['river_key'] ?? '') : null;
    $stationKey = $finalEnabled ? (string) ($selection['station_key'] ?? '') : null;
    $smsValue = $finalEnabled ? 1 : 0;

    db_query(
        'INSERT INTO forecast_sms_alert_subscription (user_id, role, sms_alert, river_key, station_key)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE role = VALUES(role), sms_alert = VALUES(sms_alert), river_key = VALUES(river_key), station_key = VALUES(station_key)',
        [$userId, $role, $smsValue, $riverKey, $stationKey]
    );

    if ($role === 'general') {
        auth_set_general_sms_alert($userId, $finalEnabled);
    }

    return [
        'enabled' => $finalEnabled,
        'river_key' => (string) ($riverKey ?? ''),
        'station_key' => (string) ($stationKey ?? ''),
        'used_fallback' => $usedFallback,
    ];
}

function forecast_sms_alert_ensure_table(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    db_query(
        'CREATE TABLE IF NOT EXISTS forecast_sms_alert_subscription (
            user_id INT NOT NULL,
            role ENUM(\'general\', \'volunteer\') NOT NULL,
            sms_alert TINYINT(1) NOT NULL DEFAULT 0,
            river_key VARCHAR(64) DEFAULT NULL,
            station_key VARCHAR(128) DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            KEY idx_forecast_sms_alert_status (sms_alert),
            KEY idx_forecast_sms_alert_station (station_key),
            CONSTRAINT fk_forecast_sms_alert_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB'
    );

    $ready = true;
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

function forecast_fetch_station_daily_discharge(float $latitude, float $longitude, string $timeZone = 'Asia/Colombo'): array
{
    if ($latitude === 0.0 && $longitude === 0.0) {
        return [];
    }

    $query = http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'daily' => 'river_discharge',
        'timezone' => $timeZone,
        'past_days' => 2,
        'forecast_days' => 8,
    ], '', '&', PHP_QUERY_RFC3986);

    $url = 'https://flood-api.open-meteo.com/v1/flood?' . $query;
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

        $rows[] = [
            'date' => $dateString,
            'river_discharge' => forecast_nullable_float($daily['river_discharge'][$index] ?? null),
            'is_forecast_day' => $dateString > $today,
        ];
    }

    return $rows;
}

function forecast_discharge_threshold_for_station(array $station): array
{
    $thresholdMap = forecast_discharge_threshold_map();
    if (empty($thresholdMap)) {
        return [];
    }

    $stationKey = (string) ($station['station_key'] ?? '');
    $stationName = (string) ($station['station_name'] ?? '');

    $candidates = [
        $stationKey,
        forecast_discharge_threshold_alias_for_station_key($stationKey),
        forecast_discharge_key_from_station_name($stationName),
    ];

    foreach ($candidates as $candidate) {
        $candidateKey = trim((string) $candidate);
        if ($candidateKey === '') {
            continue;
        }

        if (isset($thresholdMap[$candidateKey]) && is_array($thresholdMap[$candidateKey])) {
            return (array) $thresholdMap[$candidateKey];
        }
    }

    return [];
}

function forecast_discharge_threshold_alias_for_station_key(string $stationKey): string
{
    $map = [
        'kalawellawa' => 'kalawellawa_millakanda',
        'rathnapura' => 'rathnapura',
    ];

    return (string) ($map[$stationKey] ?? '');
}

function forecast_discharge_key_from_station_name(string $stationName): string
{
    $normalized = forecast_normalize_text($stationName);
    if ($normalized === '') {
        return '';
    }

    $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($stationName))) ?? '';
    return trim($slug, '_');
}

function forecast_discharge_threshold_map(): array
{
    static $thresholds = null;
    if (is_array($thresholds)) {
        return $thresholds;
    }

    $thresholds = [];

    $filePath = __DIR__ . '/discharge_thresholds.php';
    if (is_file($filePath)) {
        require_once $filePath;
    }

    if (function_exists('forecast_discharge_thresholds')) {
        $data = forecast_discharge_thresholds();
        if (is_array($data)) {
            $thresholds = $data;
        }
    }

    return $thresholds;
}

function forecast_fetch_station_daily_water_levels(string $stationName, string $fromDate, string $toDate, string $timeZone = 'Asia/Colombo'): array
{
    $stationName = trim($stationName);
    if ($stationName === '') {
        return [];
    }

    $aliases = forecast_station_name_aliases($stationName);
    if (empty($aliases)) {
        return [];
    }

    $gaugeRules = [];
    foreach ($aliases as $alias) {
        $safeAlias = forecast_arcgis_escape_sql_string($alias);
        if ($safeAlias !== '') {
            $gaugeRules[] = "gauge = '{$safeAlias}'";
        }
    }

    if (empty($gaugeRules)) {
        return [];
    }

    $where = '(' . implode(' OR ', $gaugeRules) . ') AND CreationDate >= CURRENT_TIMESTAMP - 30';

    $query = http_build_query([
        'where' => $where,
        'outFields' => 'gauge,CreationDate,water_level,alertpull,minorpull,majorpull',
        'orderByFields' => 'CreationDate ASC',
        'f' => 'json',
    ], '', '&', PHP_QUERY_RFC3986);

    $url = 'https://services3.arcgis.com/J7ZFXmR8rSmQ3FGf/arcgis/rest/services/gauges_2_view/FeatureServer/0/query?' . $query;
    $payload = forecast_http_get_json($url);
    if (!is_array($payload)) {
        return [];
    }

    $features = (array) ($payload['features'] ?? []);
    if (empty($features)) {
        return [];
    }

    $daily = [];
    foreach ($features as $feature) {
        $attributes = (array) ($feature['attributes'] ?? []);
        $createdAtRaw = $attributes['CreationDate'] ?? null;
        $waterLevel = forecast_nullable_float($attributes['water_level'] ?? null);

        if (!is_numeric($createdAtRaw) || $waterLevel === null) {
            continue;
        }

        $timestamp = (int) floor(((float) $createdAtRaw) / 1000);
        if ($timestamp <= 0) {
            continue;
        }

        $dt = (new DateTimeImmutable('@' . $timestamp))->setTimezone(new DateTimeZone($timeZone));
        $dateKey = $dt->format('Y-m-d');

        if ($dateKey < $fromDate || $dateKey > $toDate) {
            continue;
        }

        $entry = [
            'date' => $dateKey,
            'gauge' => (string) ($attributes['gauge'] ?? $stationName),
            'water_level' => $waterLevel,
            'alert_threshold' => forecast_nullable_float($attributes['alertpull'] ?? null),
            'minor_threshold' => forecast_nullable_float($attributes['minorpull'] ?? null),
            'major_threshold' => forecast_nullable_float($attributes['majorpull'] ?? null),
            'unit' => forecast_water_level_unit((string) ($attributes['gauge'] ?? $stationName)),
        ];

        if (!isset($daily[$dateKey]) || $waterLevel > (float) ($daily[$dateKey]['water_level'] ?? -INF)) {
            $daily[$dateKey] = $entry;
        }
    }

    if (empty($daily)) {
        return [];
    }

    ksort($daily);
    $rows = [];
    foreach ($daily as $dateKey => $entry) {
        $status = forecast_water_level_status(
            forecast_nullable_float($entry['water_level'] ?? null),
            forecast_nullable_float($entry['alert_threshold'] ?? null),
            forecast_nullable_float($entry['minor_threshold'] ?? null),
            forecast_nullable_float($entry['major_threshold'] ?? null)
        );

        $entry['status'] = $status;
        $entry['is_forecast_day'] = false;
        $rows[] = $entry;
    }

    return $rows;
}

function forecast_station_name_aliases(string $stationName): array
{
    $stationName = trim($stationName);
    if ($stationName === '') {
        return [];
    }

    $aliases = [
        $stationName,
    ];

    $normalized = forecast_normalize_text($stationName);
    if ($normalized === 'kalawellawa') {
        $aliases[] = 'Kalawellawa (Millakanda)';
    }

    if ($normalized === 'rathnapura') {
        $aliases[] = 'Ratnapura';
    }

    if ($normalized === 'ratnapura') {
        $aliases[] = 'Rathnapura';
    }

    $unique = [];
    foreach ($aliases as $alias) {
        $key = forecast_normalize_text((string) $alias);
        if ($key === '') {
            continue;
        }

        if (!isset($unique[$key])) {
            $unique[$key] = (string) $alias;
        }
    }

    return array_values($unique);
}

function forecast_arcgis_escape_sql_string(string $value): string
{
    return str_replace("'", "''", trim($value));
}

function forecast_water_level_unit(string $gaugeName): string
{
    $normalized = forecast_normalize_text($gaugeName);
    return $normalized === 'nagalagam street' ? 'ft' : 'm';
}

function forecast_water_level_status(?float $waterLevel, ?float $alertThreshold, ?float $minorThreshold, ?float $majorThreshold): string
{
    if ($waterLevel === null) {
        return 'safe';
    }

    if ($majorThreshold !== null && $waterLevel >= $majorThreshold) {
        return 'major';
    }

    if ($minorThreshold !== null && $waterLevel >= $minorThreshold) {
        return 'minor';
    }

    if ($alertThreshold !== null && $waterLevel >= $alertThreshold) {
        return 'alert';
    }

    return 'safe';
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
