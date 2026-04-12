<?php
$snapshot = is_array($rainfall_snapshot ?? null) ? $rainfall_snapshot : [];
$defaultSelection = is_array($default_selection ?? null) ? $default_selection : [];

$snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$snapshotJson = is_string($snapshotJson) ? $snapshotJson : '{}';

$defaultJson = json_encode($defaultSelection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$defaultJson = is_string($defaultJson) ? $defaultJson : '{}';

$source = (string) ($snapshot['source'] ?? '');
$fetchedAt = (string) ($snapshot['fetched_at'] ?? '');
?>

<style>
    :root {
        --forecast-ink: #0f172a;
        --forecast-muted: #475569;
        --forecast-border: #d9e2ec;
    }

    .forecast-wrap {
        display: grid;
        gap: 1.2rem;
    }

    .forecast-top {
        background: linear-gradient(145deg, #e6f4ff 0%, #f8fbff 58%, #eefcf6 100%);
        border: 1px solid #cfe0ea;
        border-radius: 14px;
        padding: 1.15rem 1.2rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
    }

    .forecast-title {
        margin: 0;
        color: var(--forecast-ink);
        font-size: 1.3rem;
        letter-spacing: 0.2px;
    }

    .forecast-subtitle {
        margin-top: 0.55rem;
        color: #1e293b;
        font-size: 0.95rem;
        max-width: 80ch;
    }

    .forecast-toolbar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.85rem;
        align-items: end;
    }

    .forecast-field {
        background: #fff;
        border: 1px solid var(--forecast-border);
        border-radius: 12px;
        padding: 0.75rem;
        box-shadow: 0 4px 16px rgba(2, 6, 23, 0.03);
    }

    .forecast-field label {
        display: block;
        margin-bottom: 0.45rem;
        color: #0f172a;
        font-weight: 600;
        font-size: 0.88rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .forecast-field select {
        width: 100%;
        border: 1px solid #b8ccda;
        border-radius: 8px;
        min-height: 42px;
        padding: 0.5rem 0.65rem;
        background: #fff;
        color: #0f172a;
        font-weight: 500;
    }

    .forecast-meta {
        background: #ffffff;
        border: 1px solid var(--forecast-border);
        border-radius: 12px;
        padding: 0.85rem 0.95rem;
        color: #334155;
        font-size: 0.9rem;
        line-height: 1.5;
        box-shadow: 0 6px 18px rgba(2, 6, 23, 0.03);
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.5rem;
    }

    .meta-item {
        border: 1px solid #dbe7ef;
        background: #f8fbff;
        border-radius: 9px;
        padding: 0.4rem 0.55rem;
        color: #334155;
    }

    .forecast-grid {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    .chart-card {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        border: 1px solid #d8e4ed;
        border-left-width: 6px;
        border-radius: 13px;
        padding: 0.95rem;
        min-height: 290px;
        box-shadow: 0 8px 26px rgba(2, 6, 23, 0.05);
    }

    .chart-observed { border-left-color: #0284c7; }
    .chart-discharge { border-left-color: #16a34a; }
    .chart-rainfall { border-left-color: #2563eb; }
    .chart-temperature { border-left-color: #ea580c; }

    .chart-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.6rem;
    }

    .chart-title {
        margin: 0;
        color: var(--forecast-ink);
        font-size: 1.06rem;
    }

    .chart-caption {
        margin: 0;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    .chart-surface {
        margin-top: 0.75rem;
        min-height: 225px;
        border: 1px solid #e4edf3;
        border-radius: 10px;
        padding: 0.65rem 0.55rem;
        background:
            linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(255, 255, 255, 0.96)),
            repeating-linear-gradient(
                to right,
                rgba(148, 163, 184, 0.09) 0,
                rgba(148, 163, 184, 0.09) 1px,
                transparent 1px,
                transparent 42px
            );
    }

    .chart-empty {
        margin-top: 0.85rem;
        color: #5f6f84;
        font-style: italic;
    }

    .bars {
        display: flex;
        align-items: flex-end;
        gap: 0.65rem;
        min-height: 202px;
        overflow-x: auto;
        padding: 0.15rem 0.1rem 0.2rem;
    }

    .bar-col {
        min-width: 72px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.33rem;
    }

    .bar-track {
        width: 28px;
        height: 160px;
        border-radius: 8px;
        background: linear-gradient(180deg, #ebf2f8 0%, #dbe5ee 100%);
        position: relative;
        overflow: hidden;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.22);
    }

    .bar {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 8px 8px 4px 4px;
    }

    .bar-value {
        font-size: 0.73rem;
        color: #0f172a;
        text-align: center;
        line-height: 1.2;
        min-height: 1.9rem;
        font-weight: 600;
    }

    .bar-date {
        font-size: 0.7rem;
        color: #64748b;
        text-align: center;
        font-weight: 600;
    }

    .flag {
        font-size: 0.64rem;
        border-radius: 999px;
        padding: 0.11rem 0.5rem;
        border: 1px solid #bad0df;
        color: #1f2937;
        background: #f0f7ff;
        font-weight: 700;
    }

    .forecast-divider {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        border-left: 2px dashed #ef4444;
        opacity: 0.85;
        pointer-events: none;
        z-index: 3;
    }

    .threshold-line {
        position: absolute;
        left: 0;
        right: 0;
        border-top-width: 2px;
        border-top-style: dashed;
        z-index: 2;
    }

    .threshold-alert { border-top-color: #f59e0b; }
    .threshold-minor { border-top-color: #fb7185; }
    .threshold-major { border-top-color: #dc2626; }

    .status-safe { background: #0ea5e9; }
    .status-alert { background: #f59e0b; }
    .status-minor { background: #fb7185; }
    .status-major { background: #dc2626; }
    .status-unknown { background: #94a3b8; }

    .rain-bar { background: linear-gradient(180deg, #38bdf8 0%, #0284c7 100%); }
    .temp-max { background: #f97316; }
    .temp-min { background: #22d3ee; opacity: 0.9; }

    .temp-stack {
        width: 28px;
        height: 160px;
        position: relative;
        background: linear-gradient(180deg, #ebf2f8 0%, #dbe5ee 100%);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.22);
    }

    .temp-segment {
        position: absolute;
        left: 0;
        right: 0;
    }

    @media (max-width: 760px) {
        .meta-grid {
            grid-template-columns: 1fr;
        }

        .chart-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.2rem;
        }

        .bar-col {
            min-width: 64px;
        }

        .bar-track,
        .temp-stack {
            width: 24px;
        }
    }
</style>

<section class="section-card" aria-label="River basin forecast dashboard">
    <div class="forecast-wrap">
        <div class="forecast-top">
            <h2 class="forecast-title">River Forecast Dashboard</h2>
            <p class="forecast-subtitle">
                Observed water levels are from Irrigation Department ArcGIS gauges. Forecast discharge, rainfall, and temperature are from Open-Meteo APIs.
            </p>
        </div>

        <div class="forecast-toolbar">
            <div class="forecast-field">
                <label for="riverSelect">River</label>
                <select id="riverSelect"></select>
            </div>
            <div class="forecast-field">
                <label for="stationSelect">Station</label>
                <select id="stationSelect"></select>
            </div>
        </div>

        <div class="forecast-meta" id="forecastMeta">Select a station to view details.</div>

        <div class="forecast-grid">
            <section class="chart-card chart-observed">
                <div class="chart-head">
                    <h3 class="chart-title">Observed Water Levels</h3>
                    <p class="chart-caption">Unit: m</p>
                </div>
                <div class="chart-surface" id="observedChart"></div>
            </section>

            <section class="chart-card chart-discharge">
                <div class="chart-head">
                    <h3 class="chart-title">River Discharge Forecast</h3>
                    <p class="chart-caption">Unit: m3/s</p>
                </div>
                <div class="chart-surface" id="dischargeChart"></div>
            </section>

            <section class="chart-card chart-rainfall">
                <div class="chart-head">
                    <h3 class="chart-title">Rainfall Forecast</h3>
                    <p class="chart-caption">Unit: mm/day</p>
                </div>
                <div class="chart-surface" id="rainfallChart"></div>
            </section>

            <section class="chart-card chart-temperature">
                <div class="chart-head">
                    <h3 class="chart-title">Temperature Forecast</h3>
                    <p class="chart-caption">Unit: C (max/min)</p>
                </div>
                <div class="chart-surface" id="temperatureChart"></div>
            </section>
        </div>
    </div>
</section>

<script>
(() => {
    const snapshot = <?= $snapshotJson ?>;
    const defaults = <?= $defaultJson ?>;
    const rivers = Array.isArray(snapshot.rivers) ? snapshot.rivers : [];
    const sourceText = <?= json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const fetchedAt = <?= json_encode($fetchedAt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const riverSelect = document.getElementById('riverSelect');
    const stationSelect = document.getElementById('stationSelect');
    const metaBox = document.getElementById('forecastMeta');

    const observedChart = document.getElementById('observedChart');
    const dischargeChart = document.getElementById('dischargeChart');
    const rainfallChart = document.getElementById('rainfallChart');
    const temperatureChart = document.getElementById('temperatureChart');

    if (!riverSelect || !stationSelect || !metaBox || !observedChart || !dischargeChart || !rainfallChart || !temperatureChart) {
        return;
    }

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function shortDate(dateString) {
        if (!dateString) return '-';
        const d = new Date(dateString + 'T00:00:00');
        if (Number.isNaN(d.getTime())) return dateString;
        return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    function num(value, digits = 2) {
        if (value === null || value === undefined || value === '' || Number.isNaN(Number(value))) {
            return '-';
        }
        return Number(value).toFixed(digits);
    }

    function isForecastDate(dateString) {
        if (!dateString) {
            return false;
        }

        const now = new Date();
        now.setHours(0, 0, 0, 0);
        const date = new Date(dateString + 'T00:00:00');
        if (Number.isNaN(date.getTime())) {
            return false;
        }

        return date.getTime() > now.getTime();
    }

    function stationDailyRows(station) {
        return Array.isArray(station && station.daily) ? station.daily : [];
    }

    function observedRowsForStation(station) {
        if (Array.isArray(station && station.observed_water_levels) && station.observed_water_levels.length > 0) {
            return station.observed_water_levels;
        }

        const daily = stationDailyRows(station)
            .filter((row) => row && row.water_level !== null && row.water_level !== undefined)
            .map((row) => ({
                date: row.date,
                water_level: row.water_level,
                water_level_max: row.water_level_max,
                water_level_min: row.water_level_min,
                alert_level: station && station.alert_level,
                minor_flood_level: station && station.minor_flood_level,
                major_flood_level: station && station.major_flood_level,
                flood_status: row.flood_status || 'unknown',
                data_unit: 'm',
            }));

        if (daily.length <= 4) {
            return daily;
        }

        return daily.slice(daily.length - 4);
    }

    function dischargeRowsForStation(station) {
        if (Array.isArray(station && station.discharge_forecast) && station.discharge_forecast.length > 0) {
            return station.discharge_forecast;
        }

        return stationDailyRows(station).map((row) => ({
            date: row.date,
            river_discharge: row.river_discharge,
            river_discharge_max: row.river_discharge_max,
            river_discharge_min: row.river_discharge_min,
            river_discharge_mean: row.river_discharge_mean,
            alert_threshold: null,
            minor_threshold: null,
            major_threshold: null,
            flood_status: row.flood_status || 'unknown',
            is_forecast_day: row.is_forecast_day !== undefined ? Boolean(row.is_forecast_day) : isForecastDate(row.date),
            data_unit: 'm3/s',
        }));
    }

    function rainfallRowsForStation(station) {
        if (Array.isArray(station && station.rainfall_forecast) && station.rainfall_forecast.length > 0) {
            return station.rainfall_forecast;
        }

        return stationDailyRows(station).map((row) => ({
            date: row.date,
            precipitation_sum: row.precipitation_sum ?? row.rain_sum ?? row.showers_sum ?? null,
            is_forecast_day: row.is_forecast_day !== undefined ? Boolean(row.is_forecast_day) : isForecastDate(row.date),
            data_unit: 'mm',
        }));
    }

    function temperatureRowsForStation(station) {
        if (Array.isArray(station && station.temperature_forecast) && station.temperature_forecast.length > 0) {
            return station.temperature_forecast;
        }

        return stationDailyRows(station).map((row) => ({
            date: row.date,
            temperature_2m_max: row.temperature_2m_max,
            temperature_2m_min: row.temperature_2m_min,
            is_forecast_day: row.is_forecast_day !== undefined ? Boolean(row.is_forecast_day) : isForecastDate(row.date),
            data_unit: 'celsius',
        }));
    }

    function statusClass(status) {
        if (status === 'major_flood') return 'status-major';
        if (status === 'minor_flood') return 'status-minor';
        if (status === 'alert') return 'status-alert';
        if (status === 'safe') return 'status-safe';
        return 'status-unknown';
    }

    function renderEmpty(element, text) {
        element.innerHTML = '<div class="chart-empty">' + esc(text) + '</div>';
    }

    function thresholdLines(maxValue, lines) {
        if (!maxValue || maxValue <= 0) {
            return '';
        }

        return lines
            .filter((line) => line && line.value !== null && line.value !== undefined && !Number.isNaN(Number(line.value)))
            .map((line) => {
                const ratio = Math.max(0, Math.min(1, Number(line.value) / maxValue));
                const bottom = ratio * 150;
                return '<span class="threshold-line ' + esc(line.className) + '" style="bottom:' + bottom.toFixed(1) + 'px" title="' + esc(line.label) + ': ' + esc(num(line.value, 2)) + '"></span>';
            })
            .join('');
    }

    function forecastDivider(index, total) {
        if (index < 0 || total <= 0) {
            return '';
        }
        const x = ((index + 0.5) / total) * 100;
        return '<span class="forecast-divider" style="left:' + x.toFixed(2) + '%"></span>';
    }

    function riverByKey(key) {
        return rivers.find((river) => river.river_key === key) || null;
    }

    function stationByKey(river, stationKey) {
        if (!river || !Array.isArray(river.stations)) {
            return null;
        }
        return river.stations.find((station) => station.station_key === stationKey) || null;
    }

    function currentSelection() {
        const river = riverByKey(riverSelect.value) || rivers[0] || null;
        const station = stationByKey(river, stationSelect.value)
            || (river && Array.isArray(river.stations) ? river.stations[0] : null);

        return { river, station };
    }

    function populateRivers() {
        riverSelect.innerHTML = '';

        if (!Array.isArray(rivers) || rivers.length === 0) {
            riverSelect.innerHTML = '<option value="">No rivers available</option>';
            stationSelect.innerHTML = '<option value="">No stations available</option>';
            renderEmpty(observedChart, 'No observed water level data available.');
            renderEmpty(dischargeChart, 'No discharge forecast data available.');
            renderEmpty(rainfallChart, 'No rainfall forecast data available.');
            renderEmpty(temperatureChart, 'No temperature forecast data available.');
            metaBox.textContent = 'No forecast data found for today.';
            return;
        }

        rivers.forEach((river) => {
            const option = document.createElement('option');
            option.value = river.river_key || '';
            option.textContent = river.river_name || river.river_key || 'Unnamed river';
            riverSelect.appendChild(option);
        });

        const preferredRiverKey = defaults.riverKey || defaults.river_key;
        if (preferredRiverKey && rivers.some((r) => r.river_key === preferredRiverKey)) {
            riverSelect.value = preferredRiverKey;
        } else {
            riverSelect.selectedIndex = 0;
        }

        populateStations();
        renderSelected();
    }

    function populateStations() {
        const river = riverByKey(riverSelect.value);
        stationSelect.innerHTML = '';

        if (!river || !Array.isArray(river.stations) || river.stations.length === 0) {
            stationSelect.innerHTML = '<option value="">No stations available</option>';
            return;
        }

        river.stations.forEach((station) => {
            const option = document.createElement('option');
            option.value = station.station_key || '';
            const basin = station.basin_name ? ' - ' + station.basin_name : '';
            option.textContent = (station.station_name || station.station_key || 'Station') + basin;
            stationSelect.appendChild(option);
        });

        const preferredStationKey = defaults.stationKey || defaults.station_key;
        if (preferredStationKey && river.stations.some((s) => s.station_key === preferredStationKey)) {
            stationSelect.value = preferredStationKey;
        } else {
            stationSelect.selectedIndex = 0;
        }
    }

    function renderMeta(river, station) {
        if (!station) {
            metaBox.textContent = 'No station selected.';
            return;
        }

        const dischargeRows = dischargeRowsForStation(station);
        const firstThresholdRow = dischargeRows.find((row) => row && row.alert_threshold !== null && row.alert_threshold !== undefined)
            || dischargeRows.find((row) => row && row.river_discharge_mean !== null && row.river_discharge_mean !== undefined)
            || null;

        const dischargeAlert = firstThresholdRow ? firstThresholdRow.alert_threshold : null;
        const dischargeMinor = firstThresholdRow ? firstThresholdRow.minor_threshold : null;
        const dischargeMajor = firstThresholdRow ? firstThresholdRow.major_threshold : null;

        const latestObserved = station.latest_observed_at || '-';
        const riverName = river && river.river_name ? river.river_name : '-';

        metaBox.innerHTML = '<div class="meta-grid">'
            + '<div class="meta-item"><strong>River:</strong> ' + esc(riverName) + '</div>'
            + '<div class="meta-item"><strong>Station:</strong> ' + esc(station.station_name || '-') + '</div>'
            + '<div class="meta-item"><strong>Basin:</strong> ' + esc(station.basin_name || '-') + '</div>'
            + '<div class="meta-item"><strong>Water Thresholds (m):</strong> Alert ' + esc(num(station.alert_level, 2)) + ', Minor ' + esc(num(station.minor_flood_level, 2)) + ', Major ' + esc(num(station.major_flood_level, 2)) + '</div>'
            + '<div class="meta-item"><strong>Discharge Thresholds (m3/s):</strong> Alert ' + esc(num(dischargeAlert, 2)) + ', Minor ' + esc(num(dischargeMinor, 2)) + ', Major ' + esc(num(dischargeMajor, 2)) + '</div>'
            + '<div class="meta-item"><strong>Latest Observed:</strong> ' + esc(latestObserved) + '</div>'
            + '<div class="meta-item"><strong>Data Source:</strong> ' + esc(sourceText || '-') + '</div>'
            + '<div class="meta-item"><strong>Fetched:</strong> ' + esc(fetchedAt || '-') + '</div>'
            + '</div>';
    }

    function renderObserved(station) {
        const rows = observedRowsForStation(station);
        if (rows.length === 0) {
            renderEmpty(observedChart, 'No observed water-level values available for the selected station.');
            return;
        }

        const values = rows
            .map((row) => Number(row && row.water_level))
            .filter((value) => Number.isFinite(value));

        const alert = Number(station && station.alert_level);
        const minor = Number(station && station.minor_flood_level);
        const major = Number(station && station.major_flood_level);

        const maxValue = Math.max(
            values.length ? Math.max(...values) : 0,
            Number.isFinite(alert) ? alert : 0,
            Number.isFinite(minor) ? minor : 0,
            Number.isFinite(major) ? major : 0,
            1
        ) * 1.15;

        const lines = thresholdLines(maxValue, [
            { value: Number.isFinite(alert) ? alert : null, label: 'Alert level', className: 'threshold-alert' },
            { value: Number.isFinite(minor) ? minor : null, label: 'Minor flood level', className: 'threshold-minor' },
            { value: Number.isFinite(major) ? major : null, label: 'Major flood level', className: 'threshold-major' },
        ]);

        const cols = rows.map((row) => {
            const level = Number(row && row.water_level);
            const value = Number.isFinite(level) ? level : null;
            const ratio = value === null ? 0 : Math.max(0, Math.min(1, value / maxValue));
            const height = Math.max(8, ratio * 150);
            const status = row && row.flood_status ? row.flood_status : 'unknown';

            return '<div class="bar-col">'
                + '<div class="bar-value">' + (value === null ? '-' : (num(value, 2) + ' m')) + '</div>'
                + '<div class="bar-track">' + lines + '<span class="bar ' + statusClass(status) + '" style="height:' + height.toFixed(1) + 'px"></span></div>'
                + '<div class="bar-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        observedChart.innerHTML = '<div class="bars">' + cols + '</div>';
    }

    function renderDischarge(station) {
        const rows = dischargeRowsForStation(station);
        if (rows.length === 0) {
            renderEmpty(dischargeChart, 'No discharge forecast values available for the selected station.');
            return;
        }

        const values = rows
            .map((row) => Number(row && row.river_discharge))
            .filter((value) => Number.isFinite(value));

        const thresholds = rows
            .map((row) => ({
                alert: Number(row && row.alert_threshold),
                minor: Number(row && row.minor_threshold),
                major: Number(row && row.major_threshold),
            }))
            .find((row) => Number.isFinite(row.alert) || Number.isFinite(row.minor) || Number.isFinite(row.major))
            || { alert: NaN, minor: NaN, major: NaN };

        const maxValue = Math.max(
            values.length ? Math.max(...values) : 0,
            Number.isFinite(thresholds.alert) ? thresholds.alert : 0,
            Number.isFinite(thresholds.minor) ? thresholds.minor : 0,
            Number.isFinite(thresholds.major) ? thresholds.major : 0,
            1
        ) * 1.15;

        const lines = thresholdLines(maxValue, [
            { value: Number.isFinite(thresholds.alert) ? thresholds.alert : null, label: 'Alert threshold', className: 'threshold-alert' },
            { value: Number.isFinite(thresholds.minor) ? thresholds.minor : null, label: 'Minor threshold', className: 'threshold-minor' },
            { value: Number.isFinite(thresholds.major) ? thresholds.major : null, label: 'Major threshold', className: 'threshold-major' },
        ]);

        const firstForecastIdx = rows.findIndex((row) => Boolean(row && row.is_forecast_day));
        const divider = forecastDivider(firstForecastIdx, rows.length);

        const cols = rows.map((row) => {
            const discharge = Number(row && row.river_discharge);
            const value = Number.isFinite(discharge) ? discharge : null;
            const ratio = value === null ? 0 : Math.max(0, Math.min(1, value / maxValue));
            const height = Math.max(8, ratio * 150);
            const status = row && row.flood_status ? row.flood_status : 'unknown';
            const mode = row && row.is_forecast_day ? 'Forecast' : 'Past';

            return '<div class="bar-col">'
                + '<div class="bar-value">' + (value === null ? '-' : (num(value, 2) + ' m3/s')) + '</div>'
                + '<div class="bar-track">' + lines + '<span class="bar ' + statusClass(status) + '" style="height:' + height.toFixed(1) + 'px"></span></div>'
                + '<span class="flag">' + esc(mode) + '</span>'
                + '<div class="bar-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        dischargeChart.innerHTML = '<div class="bars" style="position:relative">' + divider + cols + '</div>';
    }

    function renderRainfall(station) {
        const rows = rainfallRowsForStation(station);
        if (rows.length === 0) {
            renderEmpty(rainfallChart, 'No rainfall forecast values available for the selected station.');
            return;
        }

        const values = rows
            .map((row) => Number(row && row.precipitation_sum))
            .filter((value) => Number.isFinite(value));

        const maxValue = Math.max(values.length ? Math.max(...values) : 0, 1) * 1.15;

        const cols = rows.map((row) => {
            const rainfall = Number(row && row.precipitation_sum);
            const value = Number.isFinite(rainfall) ? rainfall : 0;
            const ratio = Math.max(0, Math.min(1, value / maxValue));
            const height = Math.max(8, ratio * 150);
            const flag = row && row.is_forecast_day ? 'Forecast' : 'Past';

            return '<div class="bar-col">'
                + '<div class="bar-value">' + num(value, 1) + ' mm</div>'
                + '<div class="bar-track"><span class="bar rain-bar" style="height:' + height.toFixed(1) + 'px"></span></div>'
                + '<span class="flag">' + esc(flag) + '</span>'
                + '<div class="bar-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        rainfallChart.innerHTML = '<div class="bars">' + cols + '</div>';
    }

    function renderTemperature(station) {
        const rows = temperatureRowsForStation(station);
        if (rows.length === 0) {
            renderEmpty(temperatureChart, 'No temperature forecast values available for the selected station.');
            return;
        }

        const validNumbers = [];
        rows.forEach((row) => {
            const maxTemp = Number(row && row.temperature_2m_max);
            const minTemp = Number(row && row.temperature_2m_min);
            if (Number.isFinite(maxTemp)) validNumbers.push(maxTemp);
            if (Number.isFinite(minTemp)) validNumbers.push(minTemp);
        });

        const maxVal = validNumbers.length ? Math.max(...validNumbers) : 40;
        const minVal = validNumbers.length ? Math.min(...validNumbers) : 0;
        const span = Math.max(1, maxVal - minVal);

        const cols = rows.map((row) => {
            const tMax = Number(row && row.temperature_2m_max);
            const tMin = Number(row && row.temperature_2m_min);

            const maxOk = Number.isFinite(tMax);
            const minOk = Number.isFinite(tMin);

            const maxHeight = maxOk ? ((tMax - minVal) / span) * 150 : 0;
            const minHeight = minOk ? ((tMin - minVal) / span) * 150 : 0;
            const flag = row && row.is_forecast_day ? 'Forecast' : 'Past';

            return '<div class="bar-col">'
                + '<div class="bar-value">' + (maxOk || minOk ? (num(tMax, 1) + ' / ' + num(tMin, 1) + ' C') : '-') + '</div>'
                + '<div class="temp-stack">'
                + (minOk ? ('<span class="temp-segment temp-min" style="bottom:0;height:' + Math.max(6, minHeight).toFixed(1) + 'px"></span>') : '')
                + (maxOk ? ('<span class="temp-segment temp-max" style="bottom:0;height:' + Math.max(6, maxHeight).toFixed(1) + 'px"></span>') : '')
                + '</div>'
                + '<span class="flag">' + esc(flag) + '</span>'
                + '<div class="bar-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        temperatureChart.innerHTML = '<div class="bars">' + cols + '</div>';
    }

    function renderSelected() {
        const selection = currentSelection();
        renderMeta(selection.river, selection.station);

        if (!selection.station) {
            renderEmpty(observedChart, 'No observed water-level data available.');
            renderEmpty(dischargeChart, 'No discharge forecast data available.');
            renderEmpty(rainfallChart, 'No rainfall forecast data available.');
            renderEmpty(temperatureChart, 'No temperature forecast data available.');
            return;
        }

        renderObserved(selection.station);
        renderDischarge(selection.station);
        renderRainfall(selection.station);
        renderTemperature(selection.station);
    }

    riverSelect.addEventListener('change', () => {
        populateStations();
        renderSelected();
    });

    stationSelect.addEventListener('change', renderSelected);

    populateRivers();
})();
</script>
