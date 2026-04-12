# Forecast 4-Chart Redesign + SMS Alert Module

Redesign the forecast module to show 4 separate charts (water level, river discharge, rainfall, temperature) with correct units per chart, and build the SMS alert module that sends flood warnings to users based on forecast data.

## User Review Required

> [!IMPORTANT]
> **Discharge-based flood thresholds for SMS alerts:** The SMS module will classify flood risk using multiples of the station's historical mean discharge from Open-Meteo (2× = Alert, 5× = Minor, 10× = Major). These multipliers are configurable but must be set to something. Are 2×/5×/10× acceptable starting points?

> [!IMPORTANT]
> **Notify.lk SDK is not installed.** The `composer.json` does not include the Notify.lk dependency, and there's no `send_sms()` helper anywhere in the codebase. I'll need to add the SDK dependency and create a core SMS helper. Should I install `notifylk/notifylk` via Composer, or do you have a different SMS sending approach in mind?

> [!IMPORTANT]
> **SMS trigger mechanism:** The SMS alerts need to be triggered somehow — either by a cron job, a manual DMC dashboard button, or on every forecast page load. Which approach do you prefer? I'll plan for a **manual trigger via a DMC dashboard button** plus a **/cli/sms-alerts** endpoint that a cron can hit.

---

## Proposed Changes

### Part 1 — Forecast Module: 4-Chart Redesign

---

#### Forecast Models — Data Fetching

##### [MODIFY] [models.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/forecast/models.php)

1. **Add Open-Meteo Weather API fetch function** — new `forecast_open_meteo_weather_daily()` that calls `api.open-meteo.com/v1/forecast` with `daily=temperature_2m_max,temperature_2m_min,precipitation_sum` for a given lat/lon. Returns daily temperature and rainfall arrays.

2. **Add `river_discharge_mean` to flood API request** — modify `forecast_open_meteo_flood_daily()` to also request `river_discharge_mean` field. This provides the long-term average discharge needed for threshold calculation.

3. **Add discharge-based flood classification function** — new `forecast_classify_discharge_flood()` that compares forecast discharge against 2×/5×/10× of mean discharge.

4. **Restructure the snapshot payload** — `forecast_fetch_river_rainfall_snapshot()` will now produce a richer structure per station:
   ```php
   'observed_water_levels' => [...],   // Chart 1: from ArcGIS, meters
   'discharge_forecast'    => [...],   // Chart 2: from Open-Meteo flood, m³/s 
   'rainfall_forecast'     => [...],   // Chart 3: from Open-Meteo weather, mm
   'temperature_forecast'  => [...],   // Chart 4: from Open-Meteo weather, °C
   ```

5. **Stop mixing discharge into water_level** — remove the current logic that assigns `river_discharge` to `$waterLevel` for forecast days (the root cause of the unit mismatch).

6. **Update cache format** — new cache files will be `daily_forecast_v2_YYYY-MM-DD.json`. Existing `daily_water_levels_*.json` caches remain readable as fallback.

---

#### Forecast Controllers

##### [MODIFY] [controllers.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/forecast/controllers.php)

- Pass the restructured snapshot to the view (no controller logic changes needed, just the data shape changes).

---

#### Forecast View — 4-Chart UI

##### [MODIFY] [views/index.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/forecast/views/index.php)

Complete redesign of the view to render 4 separate charts:

**Chart 1 — Observed Water Level (meters)**
- Bar chart showing last ~4 days of observed water levels from Irrigation Dept
- Horizontal threshold lines for alert (yellow), minor (orange), major (red)
- Color-coded bars based on threshold comparison
- Label: "Observed Water Level (m) — Irrigation Department"

**Chart 2 — River Discharge Forecast (m³/s)**  
- Bar chart showing 2 past + 7 forecast days of discharge from Open-Meteo
- Horizontal threshold lines at 2×, 5×, 10× of mean discharge
- Color-coded bars: green (safe), yellow (alert), orange (minor), red (major)
- Visual divider between past and forecast days
- Label: "River Discharge Forecast (m³/s) — GloFAS Model"

**Chart 3 — Rainfall Forecast (mm)**
- Bar chart showing 2 past + 7 forecast days of daily precipitation
- Blue-themed bars with intensity scaling
- Label: "Daily Rainfall (mm)"

**Chart 4 — Temperature Forecast (°C)**
- Line or bar chart showing 2 past + 7 forecast days
- Shows max and min temperature range
- Label: "Daily Temperature (°C)"

All 4 charts share: river/station selector dropdowns, same date axis, responsive grid layout (2×2 on desktop, stacked on mobile).

---

### Part 2 — SMS Alert Module

---

#### Core SMS Helper

##### [NEW] [core/sms.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/core/sms.php)

Core helper for sending SMS via Notify.lk API:
- `sms_send(string $to, string $message): array` — sends a single SMS, returns `['success' => bool, 'error' => string|null]`
- Uses `NOTIFY_LK_USER_ID` and `NOTIFY_LK_API_KEY` from `.env`
- Handles the HTTP request to Notify.lk API directly via cURL (avoiding the SDK dependency since the API is simple enough)

##### [MODIFY] [core/bootstrap.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/core/bootstrap.php)

- Add `require_once BASE_PATH . '/core/sms.php';` to load the SMS helper.

---

#### SMS Alerts Module

##### [NEW] [modules/sms_alerts/models.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/sms_alerts/models.php)

- `sms_alerts_find_at_risk_stations(array $snapshot, int $daysAhead = 3): array` — Scans the forecast snapshot for stations where any of the next N days shows alert/minor/major discharge flood status. Returns station keys with risk details.

- `sms_alerts_find_target_users(string $stationKey): array` — Uses the existing station→GN mapping in `forecast_station_mapping_groups()` to find all GN areas for the station, then queries `general_user` and `volunteers` tables for users in those GN divisions who have `sms_alert = 1` (for general users) or are active volunteers.

- `sms_alerts_build_message(array $stationData, array $riskDay): string` — Composes the SMS body, e.g.:
  ```
  ⚠️ ResQnet Flood Alert: [River] at [Station] is forecast to reach 
  Alert level on [Date]. Current forecast: [X] m³/s (normal: [Y] m³/s). 
  Stay vigilant. Visit resqnet.lk/forecast for details.
  ```

- `sms_alerts_log_sent(int $userId, string $stationKey, string $riskDate, string $status): void` — Logs sent alerts to prevent duplicate sends within the same day.

##### [NEW] [modules/sms_alerts/controllers.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/modules/sms_alerts/controllers.php)

- `sms_alerts_trigger(): void` — Main controller that:
  1. Fetches the forecast snapshot (uses existing cache if available)
  2. Finds at-risk stations for the next 3 days
  3. For each at-risk station, finds target users
  4. Sends SMS to each user (skipping duplicates logged today)
  5. Returns/renders a summary of alerts sent

- `sms_alerts_dashboard(): void` — DMC dashboard view showing alert status and a manual trigger button.

---

#### SMS Alert Log Table

##### [NEW] Schema addition (no new file — document for manual migration)

```sql
CREATE TABLE IF NOT EXISTS `sms_alert_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `station_key` varchar(100) NOT NULL,
  `risk_date` date NOT NULL,
  `risk_level` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','failed') NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  UNIQUE KEY `uq_sms_alert_daily` (`user_id`, `station_key`, `risk_date`),
  KEY `idx_sms_alert_date` (`sent_at`)
) ENGINE=InnoDB;
```

The unique key on `(user_id, station_key, risk_date)` prevents duplicate alerts — a user won't get the same alert for the same station/date twice.

---

#### Routes

##### [MODIFY] [routes.php](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/routes.php)

Add:
```php
route('GET',  '/dashboard/sms-alerts',      'sms_alerts_dashboard',  ['middleware_auth', 'middleware_role:dmc']);
route('POST', '/dashboard/sms-alerts/send',  'sms_alerts_trigger',    ['middleware_auth', 'middleware_role:dmc']);
```

---

### Part 3 — Environment Configuration

##### [MODIFY] [.env.example](file:///c:/Users/oketh/Documents/GitHub/resqnet-php/.env.example)

Add:
```
NOTIFY_LK_USER_ID=
NOTIFY_LK_API_KEY=
NOTIFY_LK_SENDER_ID=ResQnet
```

---

## Open Questions

> [!IMPORTANT]
> **Notify.lk credentials:** Do you already have a Notify.lk account with API credentials? The SMS sending will be a no-op (logged but not sent) until credentials are configured in `.env`.

> [!WARNING]
> **Volunteer SMS opt-in:** General users have a `sms_alert` field to opt in/out. Volunteers don't have this field in the schema. Should we:
> - (a) Add an `sms_alert` column to `volunteers` table too, or
> - (b) Always send to all volunteers in the area (since they signed up to help)?

---

## Verification Plan

### Automated Tests
- `php -l` on all modified/new PHP files
- Start dev server with `composer serve`
- Visit `/forecast` — verify 4 charts render with correct units
- Visit `/dashboard/forecast` — verify same 4 charts for authenticated users
- Visit `/dashboard/sms-alerts` — verify DMC dashboard shows alert status

### Manual Verification
- Compare Chart 1 (water level) values against the raw ArcGIS API to confirm accuracy
- Compare Chart 2 (discharge) values against raw Open-Meteo flood API response
- Compare Charts 3-4 against raw Open-Meteo weather API response
- Test SMS trigger with a test Notify.lk account
