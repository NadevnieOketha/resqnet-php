<?php

/**
 * Forecast Module - Controllers
 */

function forecast_user_profile(): ?array
{
    if (!auth_check()) {
        return null;
    }

    $role = (string) user_role();
    return auth_get_profile((int) auth_id(), $role);
}

function forecast_normalize(string $value): string
{
    $clean = strtolower(trim($value));
    $clean = preg_replace('/[^a-z0-9]+/', ' ', $clean) ?? '';
    return trim($clean);
}

function forecast_station_aliases(): array
{
    return [
        'manampitiya' => ['manampitiya', 'dimbulagala', 'polonnaruwa'],
        'nawalapitiya' => ['nawalapitiya'],
        'peradeniya' => ['peradeniya'],
        'thaldena' => ['thaldena', 'welimada', 'uva'],
        'weraganthota' => ['weraganthota'],
        'ellagawa' => ['ellagawa'],
        'kalawellawa' => ['kalawellawa'],
        'magura' => ['magura'],
        'putupaula' => ['putupaula'],
        'rathnapura' => ['rathnapura', 'ratnapura'],
        'deraniyagala' => ['deraniyagala'],
        'glencourse' => ['glencourse', 'avissawella'],
        'hanwella' => ['hanwella', 'seethawaka'],
        'holombuwa' => ['holombuwa'],
        'kithulgala' => ['kithulgala'],
        'nagalagam_street' => ['nagalagam', 'nagalagam street', 'colombo riverside'],
    ];
}

function forecast_random_station_selection(array $snapshot): array
{
    $flat = [];

    foreach ((array) ($snapshot['rivers'] ?? []) as $river) {
        $riverKey = (string) ($river['river_key'] ?? '');
        foreach ((array) ($river['stations'] ?? []) as $station) {
            $stationKey = (string) ($station['station_key'] ?? '');
            if ($riverKey === '' || $stationKey === '') {
                continue;
            }

            $flat[] = [
                'river_key' => $riverKey,
                'station_key' => $stationKey,
            ];
        }
    }

    if (empty($flat)) {
        return ['river_key' => '', 'station_key' => ''];
    }

    $selected = $flat[array_rand($flat)];
    return [
        'river_key' => (string) ($selected['river_key'] ?? ''),
        'station_key' => (string) ($selected['station_key'] ?? ''),
    ];
}

function forecast_profile_station_selection(array $snapshot, ?array $profile): array
{
    if (!$profile) {
        return ['river_key' => '', 'station_key' => ''];
    }

    $gnDivision = forecast_normalize((string) ($profile['gn_division'] ?? ''));
    $district = forecast_normalize((string) ($profile['district'] ?? ''));

    $aliases = forecast_station_aliases();
    $best = [
        'river_key' => '',
        'station_key' => '',
        'score' => -1,
    ];

    foreach ((array) ($snapshot['rivers'] ?? []) as $river) {
        $riverKey = (string) ($river['river_key'] ?? '');

        foreach ((array) ($river['stations'] ?? []) as $station) {
            $stationKey = (string) ($station['station_key'] ?? '');
            if ($riverKey === '' || $stationKey === '') {
                continue;
            }

            $score = 0;
            $stationDistrict = forecast_normalize((string) ($station['district'] ?? ''));
            $stationName = forecast_normalize((string) ($station['station_name'] ?? ''));

            if ($district !== '' && $stationDistrict !== '' && str_contains($stationDistrict, $district)) {
                $score += 20;
            }

            if ($district !== '' && $stationDistrict !== '' && str_contains($district, $stationDistrict)) {
                $score += 20;
            }

            if ($gnDivision !== '' && $stationName !== '' && str_contains($gnDivision, $stationName)) {
                $score += 40;
            }

            foreach ((array) ($aliases[$stationKey] ?? []) as $alias) {
                $aliasKey = forecast_normalize((string) $alias);
                if ($aliasKey !== '' && $gnDivision !== '' && str_contains($gnDivision, $aliasKey)) {
                    $score += 50;
                }

                if ($aliasKey !== '' && $district !== '' && str_contains($district, $aliasKey)) {
                    $score += 10;
                }
            }

            if ($score > $best['score']) {
                $best = [
                    'river_key' => $riverKey,
                    'station_key' => $stationKey,
                    'score' => $score,
                ];
            }
        }
    }

    if ((int) $best['score'] < 0) {
        return ['river_key' => '', 'station_key' => ''];
    }

    return [
        'river_key' => (string) $best['river_key'],
        'station_key' => (string) $best['station_key'],
    ];
}

function forecast_default_selection(array $snapshot, ?string $role, ?array $profile): array
{
    if (in_array((string) $role, ['general', 'volunteer', 'grama_niladhari'], true)) {
        $fromProfile = forecast_profile_station_selection($snapshot, $profile);
        if ((string) ($fromProfile['river_key'] ?? '') !== '' && (string) ($fromProfile['station_key'] ?? '') !== '') {
            return $fromProfile;
        }
    }

    return forecast_random_station_selection($snapshot);
}

function forecast_render(string $layout, string $breadcrumb, string $pageTitle): void
{
    $role = auth_check() ? (string) user_role() : null;
    $profile = forecast_user_profile();
    $snapshot = forecast_fetch_river_rainfall_snapshot();
    $defaultSelection = forecast_default_selection($snapshot, $role, $profile);

    view('forecast::index', [
        'breadcrumb' => $breadcrumb,
        'page_title' => $pageTitle,
        'rainfall_snapshot' => $snapshot,
        'default_selection' => $defaultSelection,
        'forecast_role' => $role,
    ], $layout);
}

function forecast_index(): void
{
    if (auth_check()) {
        forecast_render('dashboard', 'Forecast Dashboard', 'Forecast Dashboard');
        return;
    }

    forecast_render('main', 'Forecast Dashboard', 'Forecast Dashboard');
}

function forecast_dashboard_index(): void
{
    if (!auth_check()) {
        redirect('/login');
    }

    forecast_render('dashboard', 'Forecast Dashboard', 'Forecast Dashboard');
}
