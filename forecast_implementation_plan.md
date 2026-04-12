# Fix Forecast Unit Mismatch + SMS Flood Alert Module

## Background

The forecast module currently mixes two incompatible units: **observed water level (meters)** from the Irrigation Department and **river discharge (m³/s)** from Open-Meteo. Discharge values are compared against meter-based flood thresholds, producing incorrect flood classifications for forecast days.

This plan fixes that problem and builds an SMS alert module that sends flood warnings to users in affected areas based on 3-day forecasts.

---

## Part 1: Fix Forecast Discharge-Based Classification

### Approach

Compute **per-station flood thresholds in discharge units** by fetching 1 year of historical discharge data from Open-Meteo and calculating percentiles:

| Flood Level | Percentile | Meaning |
|---|---|---|
| Alert | P90 | Discharge exceeded only 10% of days |
| Minor Flood | P95 | Exceeded only 5% of days |
| Major Flood | P99 | Exceeded only 1% of days |

These thresholds are cached and used **only for forecast days**. Past/today days continue using observed water level data with Irrigation Department thresholds.

---

### Forecast Module Changes

#### [MODIFY] [models.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/forecast/models.php)

**New functions:**

1. `forecast_discharge_thresholds_cache_dir()` — returns `storage/logs/open_meteo/thresholds/`

2. `forecast_compute_discharge_thresholds(float $lat, float $lon)` — Fetches 1 year of historical discharge from Open-Meteo (`past_days=365`), sorts the values, computes P90/P95/P99 percentiles. Returns `['alert' => float, 'minor' => float, 'major' => float]`.

3. `forecast_get_discharge_thresholds(string $stationKey, float $lat, float $lon)` — Wrapper that checks `storage/logs/open_meteo/thresholds/{station_key}.json` first. If the cache is less than 30 days old, return it. Otherwise compute, cache, and return.

**Modified functions:**

4. `forecast_build_station_daily()` — For forecast days (where `$source === 'forecast'`), use discharge-based thresholds from `forecast_get_discharge_thresholds()` instead of the Irrigation Department's water level thresholds. Add `data_unit` field to each daily row (`'m'` for observed, `'m³/s'` for forecast).

5. `forecast_classify_flood_level()` — No change needed (it already accepts threshold params). The caller will just pass different thresholds depending on the data source.

#### [MODIFY] [views/index.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/forecast/views/index.php)

- Show the correct unit label: `"X.XX m"` for observed days, `"X.XX m³/s"` for forecast days
- Add a visual divider line between the last observed day and the first forecast day
- Add a small "(forecast)" label below forecast day bars
- Update the metadata panel to show both threshold sets (water level thresholds for observed, discharge thresholds for forecast)

---

## Part 2: SMS Flood Alert Module

### Prerequisites

> [!IMPORTANT]
> **Notify.lk SDK**: The Notify.lk PHP SDK is not yet installed in the project. We need to either:
> - Install via Composer: `composer require notifylk/notifylk-php-sdk` (if available), OR
> - Create a simple cURL-based SMS sender function in `core/sms.php` using the Notify.lk REST API directly
>
> **Which approach do you prefer?** The cURL approach keeps dependencies minimal and matches the project's existing style (no heavy SDKs).

> [!IMPORTANT]
> **Volunteer SMS opt-in**: The `volunteers` table currently has **no `sms_alert` column**. Only `general_user` has it. We need to decide:
> - Add `sms_alert` column to `volunteers` table too? (Recommended — your requirement mentions alerting volunteers)
> - Or alert ALL volunteers in affected areas without opt-in?

---

### Database Changes

#### [MODIFY] [schema.sql](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/database/schema.sql)

**Add `sms_alert` to volunteers table:**
```sql
ALTER TABLE `volunteers` ADD COLUMN `sms_alert` tinyint(1) DEFAULT '0';
```

**New table for tracking sent alerts:**
```sql
CREATE TABLE IF NOT EXISTS `sms_alert_log` (
  `alert_id` int NOT NULL AUTO_INCREMENT,
  `station_key` varchar(100) NOT NULL,
  `station_name` varchar(150) NOT NULL,
  `alert_date` date NOT NULL,
  `alert_level` enum('alert','minor_flood','major_flood') NOT NULL,
  `forecast_value` decimal(10,2) DEFAULT NULL,
  `threshold_value` decimal(10,2) DEFAULT NULL,
  `recipients_count` int NOT NULL DEFAULT 0,
  `sent_count` int NOT NULL DEFAULT 0,
  `failed_count` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`alert_id`),
  UNIQUE KEY `uq_alert_station_date` (`station_key`, `alert_date`),
  KEY `idx_alert_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

The `UNIQUE KEY` on `(station_key, alert_date)` prevents duplicate alerts for the same station + date combination.

---

### Core SMS Helper

#### [NEW] [core/sms.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/core/sms.php)

Simple cURL-based helper for the Notify.lk REST API:

```php
function sms_send(string $phoneNumber, string $message): array
```

- Reads `NOTIFY_USER_ID`, `NOTIFY_API_KEY`, `NOTIFY_SENDER_ID` from `.env`
- Formats the phone number (handles `07X` → `947X` conversion for Sri Lankan numbers)
- Sends via Notify.lk REST API: `https://app.notify.lk/api/v1/send`
- Returns `['success' => bool, 'error' => string|null]`
- Logs failures to `storage/logs/sms_errors.log`

#### [MODIFY] [.env.example](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/.env.example)

Add:
```
NOTIFY_USER_ID=
NOTIFY_API_KEY=
NOTIFY_SENDER_ID=ResQnet
```

#### [MODIFY] [core/bootstrap.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/core/bootstrap.php)

Add `require BASE_PATH . '/core/sms.php';` to the core file loading section.

---

### SMS Alert Module

#### [NEW] [modules/sms_alerts/models.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/sms_alerts/models.php)

Key functions:

1. `sms_alerts_find_recipients_for_station(string $stationKey)` — Uses the existing `forecast_station_mapping_groups()` mapping (station → districts → GN areas) to reverse-lookup:
   ```sql
   SELECT u.user_id, g.name, g.contact_number, g.district, g.gn_division
   FROM users u
   INNER JOIN general_user g ON g.user_id = u.user_id  
   WHERE u.active = 1 AND u.role = 'general'
     AND g.sms_alert = 1
     AND g.contact_number IS NOT NULL
     AND g.district = ? AND g.gn_division IN (?, ?, ...)

   UNION

   SELECT u.user_id, v.name, v.contact_number, v.district, v.gn_division
   FROM users u
   INNER JOIN volunteers v ON v.user_id = u.user_id
   WHERE u.active = 1 AND u.role = 'volunteer'
     AND v.sms_alert = 1
     AND v.contact_number IS NOT NULL
     AND v.district = ? AND v.gn_division IN (?, ?, ...)
   ```

2. `sms_alerts_already_sent(string $stationKey, string $alertDate)` — Checks `sms_alert_log` for duplicate prevention.

3. `sms_alerts_log_alert(array $data)` — Inserts into `sms_alert_log`.

4. `sms_alerts_recent_log(int $limit)` — Fetches recent alert log entries for the DMC dashboard.

#### [NEW] [modules/sms_alerts/controllers.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/sms_alerts/controllers.php)

Key functions:

1. `sms_alerts_check_and_send()` — The main logic, designed to be called via CLI:
   ```
   For each station in the forecast snapshot:
     For each of the next 3 days:
       Get the forecast discharge value
       Get the discharge thresholds for this station
       If discharge >= alert threshold:
         Check if alert already sent for this station+date
         If not sent:
           Find all recipients in this station's area
           Send SMS to each recipient
           Log the alert
   ```

   SMS message template:
   ```
   ⚠️ ResQnet Flood Alert
   {station_name} ({river_name}): {alert_level} risk on {date}.
   Predicted discharge: {value} m³/s.
   Stay alert and follow safety guidelines.
   ```

2. `sms_alerts_dashboard_index()` — DMC-only view showing recent alert log, send statistics, and manual trigger button.

3. `sms_alerts_manual_trigger()` — POST handler for DMC to manually run the alert check.

#### [NEW] [modules/sms_alerts/views/dashboard.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/sms_alerts/views/dashboard.php)

DMC admin view showing:
- Recent alert log table (station, date, level, recipients, sent/failed counts, timestamp)
- "Run Alert Check Now" button
- Link to SMS configuration

#### [NEW] [scripts/check_flood_alerts.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/scripts/check_flood_alerts.php)

CLI entry point for automated checks (e.g., cron job):
```bash
php scripts/check_flood_alerts.php
```

This script:
- Bootstraps the application (requires bootstrap, loads modules)
- Calls `sms_alerts_check_and_send()`
- Outputs a summary of what was sent
- Can be scheduled via cron to run every 6-12 hours

---

### Route Wiring

#### [MODIFY] [routes.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/routes.php)

```php
// SMS Alert Dashboard (DMC only)
route('GET',  '/dashboard/sms-alerts',         'sms_alerts_dashboard_index', ['middleware_auth', 'middleware_role:dmc']);
route('POST', '/dashboard/sms-alerts/trigger',  'sms_alerts_manual_trigger',  ['middleware_auth', 'middleware_role:dmc']);
```

---

### Volunteer Profile Changes

#### [MODIFY] [modules/auth/controllers.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/auth/controllers.php)

- Add `sms_alert` field handling in volunteer profile update (similar to general user)
- Extend `auth_profile_sms_toggle()` to also work for volunteers

#### [MODIFY] [modules/auth/models.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/auth/models.php)

- Add `sms_alert` to volunteer INSERT and UPDATE queries
- Create `auth_set_volunteer_sms_alert(int $userId, bool $enabled)` function

#### [MODIFY] [modules/auth/views/profile.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/auth/views/profile.php)

- Show SMS alert toggle for volunteers (currently only shown for general users)

---

## Open Questions

> [!IMPORTANT]
> 1. **SMS API approach**: Should we use Notify.lk's PHP SDK via Composer, or a lightweight cURL helper in `core/sms.php`? The cURL approach matches the project's style better.

> [!IMPORTANT]  
> 2. **Volunteer SMS opt-in**: Should volunteers have an opt-in toggle like general users, or should all volunteers in affected areas be alerted automatically?

> [!WARNING]
> 3. **Alert frequency**: How often should the check run? Every 6 hours? 12 hours? Once per day? More frequent = faster alerts but more API calls and potential SMS costs.

> [!NOTE]
> 4. **SMS cost awareness**: Each flood event could trigger hundreds of SMS messages. Should there be a daily cap per station or total? Should DMC admin need to approve alerts before sending?

---

## Verification Plan

### Automated Tests
1. `php -l` on all modified/new PHP files
2. Run `composer serve` and verify:
   - `/forecast` shows correct units (m for observed, m³/s for forecast)
   - Forecast bars have different status classifications than before
   - Metadata panel shows both threshold types
3. Run `php scripts/check_flood_alerts.php` and verify it outputs correct alerts without actually sending (dry-run mode)

### Manual Verification
- Test SMS sending with a test phone number via the DMC dashboard manual trigger
- Verify duplicate prevention (running the check twice doesn't send duplicate alerts)
- Verify the volunteer profile now shows SMS alert toggle
- Check that the `sms_alert_log` table records sent alerts correctly
