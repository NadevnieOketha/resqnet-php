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

function forecast_normalize_district(string $value): string
{
    $normalized = forecast_normalize($value);
    $normalized = preg_replace('/\bdistrict\b/', '', $normalized) ?? $normalized;
    return trim((string) $normalized);
}

function forecast_station_mapping_groups(): array
{
    return [
        ['district' => 'Colombo', 'station' => 'nagalagam_street', 'areas' => ['Colombo', 'Kolonnawa', 'Nagalagam Street', 'Sri Jayawardenepura Kotte', 'Thimbirigasyaya', 'Dehiwala', 'Ratmalana', 'Moratuwa']],
        ['district' => 'Colombo', 'station' => 'hanwella', 'areas' => ['Kaduwela', 'Maharagama', 'Kesbewa', 'Homagama', 'Padukka', 'Hanwella (Seethawaka)', 'Hanwella']],

        ['district' => 'Gampaha', 'station' => 'nagalagam_street', 'areas' => ['Wattala', 'Kelaniya', 'Biyagama', 'Ja-Ela']],
        ['district' => 'Gampaha', 'station' => 'hanwella', 'areas' => ['Gampaha', 'Mahara', 'Dompe', 'Attanagalla']],
        ['district' => 'Gampaha', 'station' => 'holombuwa', 'areas' => ['Negombo', 'Katana', 'Divulapitiya', 'Mirigama', 'Minuwangoda']],

        ['district' => 'Kalutara', 'station' => 'glencourse', 'areas' => ['Avissawella', 'Glencourse']],
        ['district' => 'Kalutara', 'station' => 'ellagawa', 'areas' => ['Ingiriya', 'Horana']],
        ['district' => 'Kalutara', 'station' => 'kalawellawa', 'areas' => ['Bandaragama', 'Millaniya', 'Madurawala', 'Bulathsinhala']],
        ['district' => 'Kalutara', 'station' => 'putupaula', 'areas' => ['Panadura', 'Kalutara', 'Beruwala', 'Dodangoda']],
        ['district' => 'Kalutara', 'station' => 'magura', 'areas' => ['Matugama', 'Agalawatta', 'Palindanuwara', 'Walallavita']],

        ['district' => 'Kandy', 'station' => 'nawalapitiya', 'areas' => ['Nawalapitiya', 'Pasbage Korale', 'Ganga Ihala Korale']],
        ['district' => 'Kandy', 'station' => 'peradeniya', 'areas' => ['Peradeniya', 'Kandy Four Gravets', 'Gangawata Korale', 'Yatinuwara', 'Udunuwara', 'Doluwa', 'Hatharaliyadda', 'Thumpane', 'Pujapitiya', 'Akurana', 'Udapalatha', 'Pathahewaheta', 'Delthota']],
        ['district' => 'Kandy', 'station' => 'weraganthota', 'areas' => ['Weraganthota', 'Minipe', 'Ududumbara', 'Medadumbara', 'Pathadumbara', 'Panvila', 'Kundasale']],

        ['district' => 'Kegalle', 'station' => 'deraniyagala', 'areas' => ['Deraniyagala']],
        ['district' => 'Kegalle', 'station' => 'kithulgala', 'areas' => ['Kithulgala', 'Yatiyanthota', 'Ruwanwella']],
        ['district' => 'Kegalle', 'station' => 'holombuwa', 'areas' => ['Holombuwa', 'Warakapola', 'Mawanella']],

        ['district' => 'Ratnapura', 'station' => 'rathnapura', 'areas' => ['Rathnapura', 'Kuruwita', 'Pelmadulla', 'Balangoda', 'Godakawela']],
        ['district' => 'Ratnapura', 'station' => 'ellagawa', 'areas' => ['Eheliyagoda', 'Ellagawa']],
        ['district' => 'Ratnapura', 'station' => 'kalawellawa', 'areas' => ['Kalawellawa']],
        ['district' => 'Ratnapura', 'station' => 'magura', 'areas' => ['Magura']],
        ['district' => 'Ratnapura', 'station' => 'putupaula', 'areas' => ['Putupaula']],

        ['district' => 'Polonnaruwa', 'station' => 'manampitiya', 'areas' => ['Manampitiya', 'Dimbulagala', 'Polonnaruwa', 'Hingurakgoda', 'Lankapura', 'Welikanda']],
        ['district' => 'Badulla', 'station' => 'thaldena', 'areas' => ['Thaldena', 'Welimada', 'Badulla', 'Passara', 'Bandarawela', 'Hali-Ela']],

        ['district' => 'Galle', 'station' => 'putupaula', 'areas' => ['Bentota', 'Balapitiya', 'Karandeniya', 'Elpitiya', 'Ambalangoda', 'Hikkaduwa', 'Gonapinuwala', 'Baddegama', 'Welivitiya-Divithura', 'Bope-Poddala', 'Galle Four Gravets', 'Akmeemana', 'Habaraduwa', 'Talape']],
        ['district' => 'Galle', 'station' => 'magura', 'areas' => ['Nagoda', 'Yakkalamulla', 'Imaduwa', 'Neluwa', 'Thawalama']],

        ['district' => 'Matara', 'station' => 'magura', 'areas' => ['Pitabeddara', 'Kotapola', 'Pasgoda', 'Mulatiyana', 'Akuressa']],
        ['district' => 'Matara', 'station' => 'putupaula', 'areas' => ['Athuraliya', 'Welipitiya', 'Malimbada', 'Kamburupitiya', 'Hakmana', 'Kirinda-Puhulwella', 'Thihagoda', 'Weligama', 'Matara Four Gravets', 'Devinuwara', 'Dickwella']],
    ];
}

function forecast_station_lookup(): array
{
    static $lookup = null;
    if (is_array($lookup)) {
        return $lookup;
    }

    $byDistrict = [];
    $byGn = [];

    foreach (forecast_station_mapping_groups() as $group) {
        $districtKey = forecast_normalize_district((string) ($group['district'] ?? ''));
        $stationKey = (string) ($group['station'] ?? '');
        $areas = (array) ($group['areas'] ?? []);

        if ($districtKey === '' || $stationKey === '' || empty($areas)) {
            continue;
        }

        foreach ($areas as $area) {
            $areaKey = forecast_normalize((string) $area);
            if ($areaKey === '') {
                continue;
            }

            $byDistrict[$districtKey][$areaKey] = $stationKey;

            if (!isset($byGn[$areaKey])) {
                $byGn[$areaKey] = $stationKey;
            }
        }
    }

    $lookup = [
        'by_district' => $byDistrict,
        'by_gn' => $byGn,
    ];

    return $lookup;
}

function forecast_selection_from_station(array $snapshot, string $stationKey): array
{
    $target = forecast_normalize((string) $stationKey);
    if ($target === '') {
        return ['river_key' => '', 'station_key' => ''];
    }

    foreach ((array) ($snapshot['rivers'] ?? []) as $river) {
        $riverKey = (string) ($river['river_key'] ?? '');
        foreach ((array) ($river['stations'] ?? []) as $station) {
            $candidate = forecast_normalize((string) ($station['station_key'] ?? ''));
            if ($candidate === $target) {
                return [
                    'river_key' => $riverKey,
                    'station_key' => (string) ($station['station_key'] ?? ''),
                ];
            }
        }
    }

    return ['river_key' => '', 'station_key' => ''];
}

function forecast_station_from_profile_map(?array $profile): string
{
    if (!$profile) {
        return '';
    }

    $districtKey = forecast_normalize_district((string) ($profile['district'] ?? ''));
    $gnKey = forecast_normalize((string) ($profile['gn_division'] ?? ''));
    $lookup = forecast_station_lookup();

    if ($districtKey !== '' && $gnKey !== '') {
        $byDistrict = (array) ($lookup['by_district'] ?? []);
        if (isset($byDistrict[$districtKey][$gnKey])) {
            return (string) $byDistrict[$districtKey][$gnKey];
        }
    }

    if ($gnKey !== '') {
        $byGn = (array) ($lookup['by_gn'] ?? []);
        if (isset($byGn[$gnKey])) {
            return (string) $byGn[$gnKey];
        }
    }

    return '';
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

    $mappedStation = forecast_station_from_profile_map($profile);
    if ($mappedStation !== '') {
        $mappedSelection = forecast_selection_from_station($snapshot, $mappedStation);
        if ((string) ($mappedSelection['river_key'] ?? '') !== '' && (string) ($mappedSelection['station_key'] ?? '') !== '') {
            return $mappedSelection;
        }
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
