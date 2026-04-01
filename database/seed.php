<?php

declare(strict_types=1);

use Dotenv\Dotenv;

const SEED_USERS = [
    [
        'username' => 'dmc_admin',
        'email' => 'dmc@resqnet.local',
        'role' => 'dmc',
        'active' => 1,
        'password' => 'Dmc@12345',
    ],
    [
        'username' => 'general_demo',
        'email' => 'general@example.com',
        'role' => 'general',
        'active' => 1,
        'password' => 'General@123',
        'profile' => [
            'name' => 'General User Demo',
            'contact_number' => '0770000001',
            'house_no' => '12A',
            'street' => 'Lake Road',
            'city' => 'Colombo',
            'district' => 'Colombo District',
            'gn_division' => 'Colombo',
            'sms_alert' => 1,
        ],
    ],
    [
        'username' => 'volunteer_pending',
        'email' => 'volunteer.pending@example.com',
        'role' => 'volunteer',
        'active' => 0,
        'password' => 'Volunteer@123',
        'profile' => [
            'name' => 'Volunteer Pending',
            'age' => 28,
            'gender' => 'male',
            'contact_number' => '0770000002',
            'house_no' => '34B',
            'street' => 'Temple Road',
            'city' => 'Gampaha',
            'district' => 'Gampaha District',
            'gn_division' => 'Gampaha',
            'preferences' => ['Search & Rescue', 'Medical Aid'],
            'skills' => ['First Aid Certified', 'Disaster Management Training'],
        ],
    ],
    [
        'username' => 'volunteer_active',
        'email' => 'volunteer.active@example.com',
        'role' => 'volunteer',
        'active' => 1,
        'password' => 'Volunteer@123',
        'profile' => [
            'name' => 'Volunteer Active',
            'age' => 32,
            'gender' => 'female',
            'contact_number' => '0770000003',
            'house_no' => '88',
            'street' => 'Green Road',
            'city' => 'Kalutara',
            'district' => 'Kalutara District',
            'gn_division' => 'Kalutara',
            'preferences' => ['Logistics Support', 'Shelter Management'],
            'skills' => ['Swimming / Lifesaving', 'Rescue & Handling'],
        ],
    ],
    [
        'username' => 'ngo_pending',
        'email' => 'ngo.pending@example.com',
        'role' => 'ngo',
        'active' => 0,
        'password' => 'Ngo@12345',
        'profile' => [
            'organization_name' => 'Helping Hands Pending',
            'registration_number' => 'NGO-P-001',
            'years_of_operation' => 4,
            'address' => '22 Relief Avenue, Colombo',
            'contact_person_name' => 'Nadeesha Perera',
            'contact_person_telephone' => '0711000001',
            'contact_person_email' => 'ngo.pending@example.com',
        ],
    ],
    [
        'username' => 'ngo_active',
        'email' => 'ngo.active@example.com',
        'role' => 'ngo',
        'active' => 1,
        'password' => 'Ngo@12345',
        'profile' => [
            'organization_name' => 'Community Relief Active',
            'registration_number' => 'NGO-A-001',
            'years_of_operation' => 7,
            'address' => '14 Support Street, Galle',
            'contact_person_name' => 'Sachini Fernando',
            'contact_person_telephone' => '0711000002',
            'contact_person_email' => 'ngo.active@example.com',
        ],
    ],
    [
        'username' => 'gn_officer',
        'email' => 'gn@example.com',
        'role' => 'grama_niladhari',
        'active' => 1,
        'password' => 'Gn@12345',
        'profile' => [
            'name' => 'GN Officer Demo',
            'contact_number' => '0770000004',
            'address' => '10 Division Road, Matara',
            'gn_division' => 'Matara Four Gravets',
            'service_number' => 'GN-S-1001',
            'gn_division_number' => 'GN-D-89',
        ],
    ],
];

const DONATION_ITEMS = [
    ['item_name' => 'Rice (1kg)', 'category' => 'Food'],
    ['item_name' => 'Canned Fish', 'category' => 'Food'],
    ['item_name' => 'Drinking Water (1L)', 'category' => 'Food'],
    ['item_name' => 'Blanket', 'category' => 'Shelter'],
    ['item_name' => 'Tarpaulin Sheet', 'category' => 'Shelter'],
    ['item_name' => 'First Aid Kit', 'category' => 'Medicine'],
    ['item_name' => 'Oral Rehydration Salts', 'category' => 'Medicine'],
];

const SAFE_LOCATIONS = [
    ['location_name' => 'Colombo Public Shelter', 'latitude' => 6.9271, 'longitude' => 79.8612],
    ['location_name' => 'Gampaha Community Hall', 'latitude' => 7.0873, 'longitude' => 79.9996],
    ['location_name' => 'Matara Relief Center', 'latitude' => 5.9549, 'longitude' => 80.5549],
];

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

require BASE_PATH . '/core/database.php';

function seed_require_tables(): void
{
    $requiredTables = [
        'users',
        'general_user',
        'volunteers',
        'ngos',
        'grama_niladhari',
        'skills',
        'skills_volunteers',
        'volunteer_preferences',
        'volunteer_preference_volunteers',
        'donation_items_catalog',
        'safe_locations',
    ];

    foreach ($requiredTables as $table) {
        $exists = db_fetch('SHOW TABLES LIKE ?', [$table]);
        if (!$exists) {
            throw new RuntimeException("Missing required table '{$table}'. Run database/schema.sql first.");
        }
    }
}

function seed_upsert_user(array $user): int
{
    $existing = db_fetch(
        'SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1',
        [$user['username'], $user['email']]
    );

    $passwordHash = password_hash((string) $user['password'], PASSWORD_DEFAULT);

    if ($existing) {
        db_query(
            'UPDATE users
             SET username = ?, email = ?, password_hash = ?, role = ?, active = ?
             WHERE user_id = ?',
            [
                $user['username'],
                $user['email'],
                $passwordHash,
                $user['role'],
                (int) $user['active'],
                (int) $existing['user_id'],
            ]
        );

        return (int) $existing['user_id'];
    }

    db_query(
        'INSERT INTO users (username, password_hash, email, role, active) VALUES (?, ?, ?, ?, ?)',
        [$user['username'], $passwordHash, $user['email'], $user['role'], (int) $user['active']]
    );

    return (int) db_connect()->lastInsertId();
}

function seed_upsert_general_profile(int $userId, array $profile): void
{
    db_query(
        'INSERT INTO general_user (user_id, name, contact_number, house_no, street, city, district, gn_division, sms_alert)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            contact_number = VALUES(contact_number),
            house_no = VALUES(house_no),
            street = VALUES(street),
            city = VALUES(city),
            district = VALUES(district),
            gn_division = VALUES(gn_division),
            sms_alert = VALUES(sms_alert)',
        [
            $userId,
            $profile['name'],
            $profile['contact_number'] ?? null,
            $profile['house_no'] ?? null,
            $profile['street'] ?? null,
            $profile['city'] ?? null,
            $profile['district'] ?? null,
            $profile['gn_division'] ?? null,
            (int) ($profile['sms_alert'] ?? 0),
        ]
    );
}

function seed_upsert_volunteer_profile(int $userId, array $profile): void
{
    db_query(
        'INSERT INTO volunteers (user_id, name, age, gender, contact_number, house_no, street, city, district, gn_division)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            age = VALUES(age),
            gender = VALUES(gender),
            contact_number = VALUES(contact_number),
            house_no = VALUES(house_no),
            street = VALUES(street),
            city = VALUES(city),
            district = VALUES(district),
            gn_division = VALUES(gn_division)',
        [
            $userId,
            $profile['name'],
            $profile['age'] ?? null,
            $profile['gender'] ?? null,
            $profile['contact_number'] ?? null,
            $profile['house_no'] ?? null,
            $profile['street'] ?? null,
            $profile['city'] ?? null,
            $profile['district'] ?? null,
            $profile['gn_division'] ?? null,
        ]
    );

    seed_sync_volunteer_preferences($userId, $profile['preferences'] ?? []);
    seed_sync_volunteer_skills($userId, $profile['skills'] ?? []);
}

function seed_upsert_ngo_profile(int $userId, array $profile): void
{
    db_query(
        'INSERT INTO ngos (user_id, organization_name, registration_number, years_of_operation, address, contact_person_name, contact_person_telephone, contact_person_email)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            organization_name = VALUES(organization_name),
            registration_number = VALUES(registration_number),
            years_of_operation = VALUES(years_of_operation),
            address = VALUES(address),
            contact_person_name = VALUES(contact_person_name),
            contact_person_telephone = VALUES(contact_person_telephone),
            contact_person_email = VALUES(contact_person_email)',
        [
            $userId,
            $profile['organization_name'],
            $profile['registration_number'],
            $profile['years_of_operation'] ?? null,
            $profile['address'] ?? null,
            $profile['contact_person_name'] ?? null,
            $profile['contact_person_telephone'] ?? null,
            $profile['contact_person_email'] ?? null,
        ]
    );
}

function seed_upsert_gn_profile(int $userId, array $profile): void
{
    db_query(
        'INSERT INTO grama_niladhari (user_id, name, contact_number, address, gn_division, service_number, gn_division_number)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            contact_number = VALUES(contact_number),
            address = VALUES(address),
            gn_division = VALUES(gn_division),
            service_number = VALUES(service_number),
            gn_division_number = VALUES(gn_division_number)',
        [
            $userId,
            $profile['name'],
            $profile['contact_number'] ?? null,
            $profile['address'] ?? null,
            $profile['gn_division'] ?? null,
            $profile['service_number'] ?? null,
            $profile['gn_division_number'] ?? null,
        ]
    );
}

function seed_ensure_skill_id(string $skillName): int
{
    $existing = db_fetch('SELECT skill_id FROM skills WHERE skill_name = ? LIMIT 1', [$skillName]);
    if ($existing) {
        return (int) $existing['skill_id'];
    }

    db_query('INSERT INTO skills (skill_name) VALUES (?)', [$skillName]);
    return (int) db_connect()->lastInsertId();
}

function seed_ensure_preference_id(string $preferenceName): int
{
    $existing = db_fetch('SELECT preference_id FROM volunteer_preferences WHERE preference_name = ? LIMIT 1', [$preferenceName]);
    if ($existing) {
        return (int) $existing['preference_id'];
    }

    db_query('INSERT INTO volunteer_preferences (preference_name) VALUES (?)', [$preferenceName]);
    return (int) db_connect()->lastInsertId();
}

function seed_sync_volunteer_preferences(int $userId, array $preferences): void
{
    db_query('DELETE FROM volunteer_preference_volunteers WHERE user_id = ?', [$userId]);

    foreach ($preferences as $name) {
        $cleanName = trim((string) $name);
        if ($cleanName === '') {
            continue;
        }

        $preferenceId = seed_ensure_preference_id($cleanName);
        db_query(
            'INSERT INTO volunteer_preference_volunteers (user_id, preference_id) VALUES (?, ?)',
            [$userId, $preferenceId]
        );
    }
}

function seed_sync_volunteer_skills(int $userId, array $skills): void
{
    db_query('DELETE FROM skills_volunteers WHERE user_id = ?', [$userId]);

    foreach ($skills as $name) {
        $cleanName = trim((string) $name);
        if ($cleanName === '') {
            continue;
        }

        $skillId = seed_ensure_skill_id($cleanName);
        db_query(
            'INSERT INTO skills_volunteers (user_id, skill_id) VALUES (?, ?)',
            [$userId, $skillId]
        );
    }
}

function seed_catalog_data(): void
{
    $skills = (array) config('auth_options.volunteer_skills', []);
    foreach ($skills as $skill) {
        seed_ensure_skill_id((string) $skill);
    }

    $preferences = (array) config('auth_options.volunteer_preferences', []);
    foreach ($preferences as $preference) {
        seed_ensure_preference_id((string) $preference);
    }

    foreach (DONATION_ITEMS as $item) {
        db_query(
            'INSERT INTO donation_items_catalog (item_name, category)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE category = VALUES(category)',
            [$item['item_name'], $item['category']]
        );
    }

    foreach (SAFE_LOCATIONS as $location) {
        $existing = db_fetch('SELECT location_id FROM safe_locations WHERE location_name = ? LIMIT 1', [$location['location_name']]);
        if ($existing) {
            db_query(
                'UPDATE safe_locations SET latitude = ?, longitude = ? WHERE location_id = ?',
                [$location['latitude'], $location['longitude'], (int) $existing['location_id']]
            );
            continue;
        }

        db_query(
            'INSERT INTO safe_locations (location_name, latitude, longitude) VALUES (?, ?, ?)',
            [$location['location_name'], $location['latitude'], $location['longitude']]
        );
    }
}

function seed_profiles_by_role(int $userId, array $seedUser): void
{
    $profile = (array) ($seedUser['profile'] ?? []);

    switch ((string) $seedUser['role']) {
        case 'general':
            seed_upsert_general_profile($userId, $profile);
            break;

        case 'volunteer':
            seed_upsert_volunteer_profile($userId, $profile);
            break;

        case 'ngo':
            seed_upsert_ngo_profile($userId, $profile);
            break;

        case 'grama_niladhari':
            seed_upsert_gn_profile($userId, $profile);
            break;

        case 'dmc':
            // No dedicated profile table for dmc in current schema.
            break;
    }
}

$pdo = null;

try {
    seed_require_tables();

    $pdo = db_connect();
    $pdo->beginTransaction();

    seed_catalog_data();

    $createdUsers = [];

    foreach (SEED_USERS as $seedUser) {
        $userId = seed_upsert_user($seedUser);
        seed_profiles_by_role($userId, $seedUser);

        $createdUsers[] = [
            'username' => $seedUser['username'],
            'password' => $seedUser['password'],
            'role' => $seedUser['role'],
            'active' => (int) $seedUser['active'],
        ];
    }

    $pdo->commit();

    echo "Database seed completed successfully." . PHP_EOL;
    echo PHP_EOL;
    echo "Seeded login accounts:" . PHP_EOL;
    echo str_repeat('-', 72) . PHP_EOL;

    foreach ($createdUsers as $account) {
        $status = $account['active'] === 1 ? 'active' : 'pending';
        printf(
            "%-20s %-16s %-16s %s" . PHP_EOL,
            $account['username'],
            $account['password'],
            $account['role'],
            $status
        );
    }

    echo str_repeat('-', 72) . PHP_EOL;
    echo "Default DMC account: dmc_admin / Dmc@12345" . PHP_EOL;
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'Seed failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
