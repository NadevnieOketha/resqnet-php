<?php
$snapshot = is_array($rainfall_snapshot ?? null) ? $rainfall_snapshot : [];
$defaultSelection = is_array($default_selection ?? null) ? $default_selection : [];
$smsAlertPreference = is_array($sms_alert_preference ?? null) ? $sms_alert_preference : [];
$userRole = (string) ($role ?? '');
$canManageSms = in_array($userRole, ['general', 'volunteer'], true);

$snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$snapshotJson = is_string($snapshotJson) ? $snapshotJson : '{}';

$defaultJson = json_encode($defaultSelection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$defaultJson = is_string($defaultJson) ? $defaultJson : '{}';

$smsAlertJson = json_encode($smsAlertPreference, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$smsAlertJson = is_string($smsAlertJson) ? $smsAlertJson : '{}';
?>

<style>
    :root {
        --wx-ink: #0b132b;
        --wx-muted: #475569;
        --wx-border: #d2dce8;
    }

    .wx-wrap {
        display: grid;
        gap: 1rem;
    }

    .wx-hero {
        padding: 1rem 1.1rem;
        border-radius: 14px;
        border: 1px solid #c6d8ea;
        background: linear-gradient(130deg, #e9f3ff 0%, #f6fbff 55%, #ecfff6 100%);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .wx-title {
        margin: 0;
        color: var(--wx-ink);
        font-size: 1.2rem;
    }

    .wx-subtitle {
        margin: 0.5rem 0 0;
        color: #1e293b;
        max-width: 82ch;
        font-size: 0.94rem;
    }

    .wx-toolbar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.8rem;
    }

    .wx-sms-box {
        border: 1px solid #c8d9ea;
        border-radius: 12px;
        background: #f7fbff;
        padding: 0.85rem;
    }

    .wx-sms-title {
        margin: 0 0 0.55rem;
        color: #0f172a;
        font-size: 0.98rem;
    }

    .wx-sms-help {
        margin: 0 0 0.75rem;
        color: #334155;
        font-size: 0.86rem;
    }

    .wx-sms-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.8rem;
        align-items: end;
    }

    .wx-sms-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #0f172a;
    }

    .wx-sms-toggle input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }

    .wx-sms-actions {
        margin-top: 0.75rem;
        display: flex;
        justify-content: flex-end;
    }

    .wx-sms-note {
        margin-top: 0.45rem;
        font-size: 0.82rem;
        color: #475569;
    }

    .wx-field {
        border: 1px solid var(--wx-border);
        background: #fff;
        border-radius: 12px;
        padding: 0.75rem;
    }

    .wx-field label {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #0f172a;
    }

    .wx-field select {
        width: 100%;
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid #b7cad9;
        padding: 0.45rem 0.6rem;
        background: #fff;
        color: #0f172a;
        font-weight: 600;
    }

    .wx-meta {
        border: 1px solid var(--wx-border);
        border-radius: 12px;
        background: #fff;
        padding: 0.8rem;
        color: #334155;
        font-size: 0.89rem;
        line-height: 1.45;
    }

    .wx-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.5rem;
    }

    .wx-meta-item {
        border: 1px solid #dbe6f0;
        border-radius: 9px;
        background: #f8fbff;
        padding: 0.42rem 0.55rem;
    }

    .wx-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.9rem;
    }

    .wx-card {
        border: 1px solid #d6e3ee;
        border-left: 6px solid transparent;
        border-radius: 13px;
        background: linear-gradient(180deg, #fff 0%, #fafdff 100%);
        padding: 0.9rem;
        box-shadow: 0 8px 24px rgba(2, 6, 23, 0.05);
    }

    .wx-rain {
        border-left-color: #0284c7;
    }

    .wx-discharge {
        border-left-color: #2563eb;
    }

    .wx-temp {
        border-left-color: #ea580c;
    }

    .wx-water {
        border-left-color: #0f766e;
    }

    .wx-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.7rem;
    }

    .wx-head h3 {
        margin: 0;
        color: var(--wx-ink);
        font-size: 1.02rem;
    }

    .wx-caption {
        margin: 0;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .wx-surface {
        margin-top: 0.7rem;
        min-height: 230px;
        border: 1px solid #e2ecf4;
        border-radius: 10px;
        padding: 0.6rem 0.55rem;
        background:
            linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(255, 255, 255, 0.96)),
            repeating-linear-gradient(
                to right,
                rgba(148, 163, 184, 0.08) 0,
                rgba(148, 163, 184, 0.08) 1px,
                transparent 1px,
                transparent 42px
            );
    }

    .wx-empty {
        margin-top: 0.8rem;
        color: #64748b;
        font-style: italic;
    }

    .wx-bars {
        display: flex;
        align-items: flex-end;
        gap: 0.65rem;
        min-height: 200px;
        overflow-x: auto;
        padding: 0.1rem 0.1rem 0.25rem;
    }

    .wx-col {
        min-width: 74px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.3rem;
    }

    .wx-value {
        font-size: 0.72rem;
        text-align: center;
        color: #0f172a;
        line-height: 1.2;
        min-height: 1.8rem;
        font-weight: 700;
    }

    .wx-date {
        font-size: 0.68rem;
        text-align: center;
        color: #64748b;
        font-weight: 600;
    }

    .wx-track {
        width: 28px;
        height: 160px;
        border-radius: 8px;
        background: linear-gradient(180deg, #ecf3f8 0%, #dbe7ef 100%);
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.24);
        position: relative;
        overflow: hidden;
    }

    .wx-rain-bar {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 8px 8px 4px 4px;
        background: linear-gradient(180deg, #38bdf8 0%, #0284c7 100%);
    }

    .wx-discharge-bar {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 8px 8px 4px 4px;
        background: linear-gradient(180deg, #93c5fd 0%, #2563eb 100%);
    }

    .wx-threshold-line {
        position: absolute;
        left: 0;
        right: 0;
        height: 2px;
        background: #ef4444;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.4);
    }

    .wx-threshold-note {
        margin-top: 0.55rem;
        color: #334155;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .wx-temp-track {
        width: 28px;
        height: 160px;
        border-radius: 8px;
        background: linear-gradient(180deg, #ecf3f8 0%, #dbe7ef 100%);
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.24);
        position: relative;
        overflow: hidden;
    }

    .wx-temp-range {
        position: absolute;
        left: 0;
        right: 0;
        border-radius: 6px;
        background: linear-gradient(180deg, #fdba74 0%, #f97316 100%);
    }

    .wx-temp-min-dot,
    .wx-temp-max-dot {
        position: absolute;
        left: 50%;
        width: 9px;
        height: 9px;
        margin-left: -4.5px;
        border-radius: 50%;
        border: 1px solid #fff;
        z-index: 2;
    }

    .wx-temp-min-dot {
        background: #22d3ee;
    }

    .wx-temp-max-dot {
        background: #ef4444;
    }

    .wx-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.45rem;
    }

    .wx-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.22rem 0.52rem;
        border: 1px solid #cbd5e1;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: #0f172a;
        background: #ffffff;
    }

    .wx-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
    }

    .wx-dot-safe {
        background: #16a34a;
    }

    .wx-dot-alert {
        background: #eab308;
    }

    .wx-dot-minor {
        background: #f97316;
    }

    .wx-dot-major {
        background: #dc2626;
    }

    .wx-water-bar {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 8px 8px 4px 4px;
    }

    .wx-water-safe {
        background: #2626dc;
    }

    .wx-water-alert {
        background: linear-gradient(180deg, #fde047 0%, #eab308 100%);
    }

    .wx-water-minor {
        background: linear-gradient(180deg, #fdba74 0%, #f97316 100%);
    }

    .wx-water-major {
        background: #dc2626;
    }

    @media (max-width: 760px) {
        .wx-meta-grid {
            grid-template-columns: 1fr;
        }

        .wx-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.2rem;
        }

        .wx-col {
            min-width: 66px;
        }

        .wx-track,
        .wx-temp-track {
            width: 24px;
        }
    }
</style>

<section class="section-card" aria-label="River basin weather dashboard">
    <div class="wx-wrap">
        <div class="wx-hero">
            <h2 class="wx-title">River Basin Rainfall, Discharge, Temperature, and Water Level Dashboard</h2>
        </div>

        <div class="wx-toolbar">
            <div class="wx-field">
                <label for="riverSelect">River</label>
                <select id="riverSelect"></select>
            </div>
            <div class="wx-field">
                <label for="basinSelect">River Basin Location</label>
                <select id="basinSelect"></select>
            </div>
        </div>

        <?php if ($canManageSms): ?>
            <div class="wx-sms-box" aria-label="Forecast SMS alerts">
                <h3 class="wx-sms-title">SMS Alerts</h3>
                <p class="wx-sms-help">Enable alerts and optionally pick a river and gauge station. If station is not selected, your closest GN-mapped station will be used.</p>
                <form method="POST" action="/dashboard/forecast/sms-alert" id="forecastSmsForm">
                    <?= csrf_field() ?>
                    <div class="wx-sms-row">
                        <div class="wx-field">
                            <label class="wx-sms-toggle" for="smsAlertCheckbox">
                                <input type="checkbox" id="smsAlertCheckbox" name="sms_alert" value="1">
                                Receive forecast SMS alerts
                            </label>
                            <div class="wx-sms-note">Available for general users and volunteers.</div>
                        </div>
                        <div class="wx-field">
                            <label for="smsRiverSelect">Alert River (optional)</label>
                            <select id="smsRiverSelect" name="sms_river_key"></select>
                        </div>
                        <div class="wx-field">
                            <label for="smsStationSelect">Alert Gauge Station (optional)</label>
                            <select id="smsStationSelect" name="sms_station_key"></select>
                        </div>
                    </div>
                    <div class="wx-sms-actions">
                        <button type="submit" class="btn btn-primary">Save SMS Alert Preference</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div id="metaBox" class="wx-meta">Select a river basin location to view details.</div>

        <div class="wx-grid">
            <section class="wx-card wx-rain">
                <div class="wx-head">
                    <h3>Daily Rainfall</h3>
                    <p class="wx-caption">Unit: mm/day</p>
                </div>
                <div class="wx-surface" id="rainfallChart"></div>
            </section>

            <section class="wx-card wx-discharge">
                <div class="wx-head">
                    <h3>Daily River Discharge</h3>
                    <p class="wx-caption">Unit: m3/s</p>
                </div>
                <div class="wx-surface" id="dischargeChart"></div>
            </section>

            <section class="wx-card wx-temp">
                <div class="wx-head">
                    <h3>Daily Temperature Range</h3>
                    <p class="wx-caption">Unit: C (max/min)</p>
                </div>
                <div class="wx-surface" id="temperatureChart"></div>
            </section>

            <section class="wx-card wx-water">
                <div class="wx-head">
                    <h3>Daily River Water Level</h3>
                    <p class="wx-caption">Peak level by day</p>
                </div>
                <div class="wx-legend">
                    <span class="wx-chip"><span class="wx-dot wx-dot-safe"></span>Safe</span>
                    <span class="wx-chip"><span class="wx-dot wx-dot-alert"></span>Alert</span>
                    <span class="wx-chip"><span class="wx-dot wx-dot-minor"></span>Minor Flood</span>
                    <span class="wx-chip"><span class="wx-dot wx-dot-major"></span>Major Flood</span>
                </div>
                <div class="wx-surface" id="waterLevelChart"></div>
            </section>
        </div>
    </div>
</section>
<script>
(() => {
    const snapshot = <?= $snapshotJson ?>;
    const defaults = <?= $defaultJson ?>;
    const smsPref = <?= $smsAlertJson ?>;
    const canManageSms = <?= $canManageSms ? 'true' : 'false' ?>;

    const rivers = Array.isArray(snapshot.rivers) ? snapshot.rivers : [];
    const windowInfo = snapshot.window || {};
    const riverSelect = document.getElementById('riverSelect');
    const basinSelect = document.getElementById('basinSelect');
    const metaBox = document.getElementById('metaBox');
    const rainfallChart = document.getElementById('rainfallChart');
    const dischargeChart = document.getElementById('dischargeChart');
    const temperatureChart = document.getElementById('temperatureChart');
    const waterLevelChart = document.getElementById('waterLevelChart');
    const smsAlertCheckbox = document.getElementById('smsAlertCheckbox');
    const smsRiverSelect = document.getElementById('smsRiverSelect');
    const smsStationSelect = document.getElementById('smsStationSelect');

    if (!riverSelect || !basinSelect || !metaBox || !rainfallChart || !dischargeChart || !temperatureChart || !waterLevelChart) {
        return;
    }

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toNum(value) {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function fmt(value, digits = 1) {
        const n = toNum(value);
        return n === null ? '-' : n.toFixed(digits);
    }

    function shortDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString + 'T00:00:00');
        if (Number.isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    function riverByKey(riverKey) {
        return rivers.find((river) => String(river.river_key || '') === String(riverKey || '')) || null;
    }

    function stationByKey(river, stationKey) {
        if (!river || !Array.isArray(river.stations)) return null;
        return river.stations.find((station) => String(station.station_key || '') === String(stationKey || '')) || null;
    }

    function rowsForStation(station) {
        return Array.isArray(station && station.daily_weather) ? station.daily_weather : [];
    }

    function rowsForWater(station) {
        return Array.isArray(station && station.daily_water_levels) ? station.daily_water_levels : [];
    }

    function rowsForDischarge(station) {
        return Array.isArray(station && station.daily_discharge) ? station.daily_discharge : [];
    }

    function latestObservedDischarge(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            return null;
        }

        for (let i = rows.length - 1; i >= 0; i -= 1) {
            const row = rows[i];
            if (!row || row.is_forecast_day === true) {
                continue;
            }

            const discharge = toNum(row.river_discharge);
            if (discharge !== null) {
                return discharge;
            }
        }

        // Fallback to the most recent value if observed rows are unavailable.
        for (let i = rows.length - 1; i >= 0; i -= 1) {
            const discharge = toNum(rows[i] && rows[i].river_discharge);
            if (discharge !== null) {
                return discharge;
            }
        }

        return null;
    }

    function renderEmpty(element, text) {
        element.innerHTML = '<div class="wx-empty">' + esc(text) + '</div>';
    }

    function renderMeta(river, station) {
        if (!river || !station) {
            metaBox.textContent = 'No river basin location selected.';
            return;
        }

        const fromDate = String(windowInfo.from || '-');
        const toDate = String(windowInfo.to || '-');
        const waterRows = rowsForWater(station);
        const latestWater = waterRows.length ? waterRows[waterRows.length - 1] : null;
        const latestWaterStatus = latestWater ? String(latestWater.status || 'safe').toLowerCase() : '';
        const latestStatus = latestWater ? statusLabel(latestWaterStatus) : 'No data';
        const latestValue = latestWater ? (fmt(latestWater.water_level, 2) + ' ' + String(latestWater.unit || 'm')) : '-';
        const dischargeRows = rowsForDischarge(station);
        const latestDischarge = latestObservedDischarge(dischargeRows);
        const alertThreshold = toNum(station && station.discharge_thresholds && station.discharge_thresholds.alert);
        const latestStatusStyle = latestWaterStatus === 'safe'
            ? ' style="background-color: #dbeafe;"'
            : (latestWaterStatus === 'major' ? ' style="background-color: #fee2e2;"' : '');

        metaBox.innerHTML = ''
            + '<div class="wx-meta-grid">'
            + '<div class="wx-meta-item"><strong>District:</strong> ' + esc(station.district || '-') + '</div>'
            + '<div class="wx-meta-item"><strong>GN / Local Area:</strong> ' + esc(station.local_area || '-') + '</div>'
            + '<div class="wx-meta-item"><strong>Coordinates:</strong> ' + esc(fmt(station.latitude, 4)) + ', ' + esc(fmt(station.longitude, 4)) + '</div>'
            + '<div class="wx-meta-item"><strong>Date Window:</strong> ' + esc(fromDate) + ' to ' + esc(toDate) + '</div>'
            + '<div class="wx-meta-item"><strong>Latest Observed Discharge:</strong> ' + esc(latestDischarge === null ? '-' : fmt(latestDischarge, 2) + ' m3/s') + '</div>'
            + '<div class="wx-meta-item"><strong>Alert Baseline:</strong> ' + esc(alertThreshold === null ? '-' : fmt(alertThreshold, 2) + ' m3/s') + '</div>'
            + '<div class="wx-meta-item"><strong>Latest Water Level:</strong> ' + esc(latestValue) + '</div>'
            + '<div class="wx-meta-item"' + latestStatusStyle + '><strong>Latest Status:</strong> ' + esc(latestStatus) + '</div>'
            + '</div>';
    }

    function statusLabel(status) {
        const value = String(status || '').toLowerCase();
        if (value === 'major') return 'Major Flood';
        if (value === 'minor') return 'Minor Flood';
        if (value === 'alert') return 'Alert';
        return 'Safe';
    }

    function statusClass(status) {
        const value = String(status || '').toLowerCase();
        if (value === 'major') return 'wx-water-major';
        if (value === 'minor') return 'wx-water-minor';
        if (value === 'alert') return 'wx-water-alert';
        return 'wx-water-safe';
    }

    function renderRainfall(station) {
        const rows = rowsForStation(station);
        if (rows.length === 0) {
            renderEmpty(rainfallChart, 'No rainfall data available for the selected basin location.');
            return;
        }

        const values = rows
            .map((row) => toNum(row && row.precipitation_sum))
            .filter((value) => value !== null);

        const maxValue = Math.max(values.length ? Math.max(...values) : 0, 1) * 1.15;

        const bars = rows.map((row) => {
            const amount = toNum(row && row.precipitation_sum);
            const value = amount === null ? 0 : amount;
            const ratio = Math.max(0, Math.min(1, value / maxValue));
            const height = Math.max(8, ratio * 150);
            return ''
                + '<div class="wx-col">'
                + '<div class="wx-value">' + esc(fmt(value, 1)) + ' mm</div>'
                + '<div class="wx-track"><span class="wx-rain-bar" style="height:' + height.toFixed(1) + 'px"></span></div>'
                + '<div class="wx-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        rainfallChart.innerHTML = '<div class="wx-bars">' + bars + '</div>';
    }

    function renderTemperature(station) {
        const rows = rowsForStation(station);
        if (rows.length === 0) {
            renderEmpty(temperatureChart, 'No temperature data available for the selected basin location.');
            return;
        }

        const allValues = [];
        rows.forEach((row) => {
            const max = toNum(row && row.temperature_2m_max);
            const min = toNum(row && row.temperature_2m_min);
            if (max !== null) allValues.push(max);
            if (min !== null) allValues.push(min);
        });

        const top = allValues.length ? Math.max(...allValues) : 40;
        const bottom = allValues.length ? Math.min(...allValues) : 0;
        const span = Math.max(1, top - bottom);

        const bars = rows.map((row) => {
            const tMax = toNum(row && row.temperature_2m_max);
            const tMin = toNum(row && row.temperature_2m_min);
            if (tMax === null || tMin === null) {
                return ''
                    + '<div class="wx-col">'
                    + '<div class="wx-value">-</div>'
                    + '<div class="wx-temp-track"></div>'
                    + '<div class="wx-date">' + esc(shortDate(row && row.date)) + '</div>'
                    + '</div>';
            }

            const minRatio = (tMin - bottom) / span;
            const maxRatio = (tMax - bottom) / span;
            const lowPx = Math.max(0, Math.min(1, minRatio)) * 150;
            const highPx = Math.max(0, Math.min(1, maxRatio)) * 150;
            const rangeHeight = Math.max(6, highPx - lowPx);

            return ''
                + '<div class="wx-col">'
                + '<div class="wx-value">' + esc(fmt(tMax, 1)) + ' / ' + esc(fmt(tMin, 1)) + ' C</div>'
                + '<div class="wx-temp-track">'
                + '<span class="wx-temp-range" style="bottom:' + lowPx.toFixed(1) + 'px;height:' + rangeHeight.toFixed(1) + 'px"></span>'
                + '<span class="wx-temp-min-dot" style="bottom:' + Math.max(0, lowPx - 4).toFixed(1) + 'px"></span>'
                + '<span class="wx-temp-max-dot" style="bottom:' + Math.max(0, highPx - 4).toFixed(1) + 'px"></span>'
                + '</div>'
                + '<div class="wx-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        temperatureChart.innerHTML = '<div class="wx-bars">' + bars + '</div>';
    }

    function renderDischarge(station) {
        const rows = rowsForDischarge(station);
        if (rows.length === 0) {
            renderEmpty(dischargeChart, 'No Open-Meteo flood discharge data available for the selected basin location.');
            return;
        }

        const alertThreshold = toNum(station && station.discharge_thresholds && station.discharge_thresholds.alert);

        const values = rows
            .map((row) => toNum(row && row.river_discharge))
            .filter((value) => value !== null);

        const maxData = values.length ? Math.max(...values) : 0;
        const maxValue = Math.max(maxData, alertThreshold || 0, 1) * 1.15;

        const bars = rows.map((row) => {
            const discharge = toNum(row && row.river_discharge);
            const value = discharge === null ? 0 : discharge;
            const ratio = Math.max(0, Math.min(1, value / maxValue));
            const height = Math.max(8, ratio * 150);

            let thresholdMarkup = '';
            if (alertThreshold !== null) {
                const thresholdRatio = Math.max(0, Math.min(1, alertThreshold / maxValue));
                const thresholdPx = thresholdRatio * 150;
                thresholdMarkup = '<span class="wx-threshold-line" style="bottom:' + Math.max(0, thresholdPx - 1).toFixed(1) + 'px"></span>';
            }

            return ''
                + '<div class="wx-col">'
                + '<div class="wx-value">' + esc(fmt(value, 2)) + ' m3/s</div>'
                + '<div class="wx-track">'
                + '<span class="wx-discharge-bar" style="height:' + height.toFixed(1) + 'px"></span>'
                + thresholdMarkup
                + '</div>'
                + '<div class="wx-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        const thresholdNote = alertThreshold === null
            ? 'Alert baseline threshold is unavailable for this location.'
            : ('Alert baseline: ' + fmt(alertThreshold, 2) + ' m3/s');

        dischargeChart.innerHTML = '<div class="wx-bars">' + bars + '</div><div class="wx-threshold-note">' + esc(thresholdNote) + '</div>';
    }

    function renderWaterLevels(station) {
        const rows = rowsForWater(station);
        if (rows.length === 0) {
            renderEmpty(waterLevelChart, 'No ArcGIS water-level data available for the selected basin location.');
            return;
        }

        const values = rows
            .map((row) => toNum(row && row.water_level))
            .filter((value) => value !== null);

        const maxValue = Math.max(values.length ? Math.max(...values) : 0, 1) * 1.15;

        const bars = rows.map((row) => {
            const waterLevel = toNum(row && row.water_level);
            const value = waterLevel === null ? 0 : waterLevel;
            const ratio = Math.max(0, Math.min(1, value / maxValue));
            const height = Math.max(8, ratio * 150);
            const unit = String((row && row.unit) || 'm');
            const status = String((row && row.status) || 'safe');

            return ''
                + '<div class="wx-col">'
                + '<div class="wx-value">' + esc(fmt(value, 2)) + ' ' + esc(unit) + '<br>' + esc(statusLabel(status)) + '</div>'
                + '<div class="wx-track"><span class="wx-water-bar ' + esc(statusClass(status)) + '" style="height:' + height.toFixed(1) + 'px"></span></div>'
                + '<div class="wx-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        waterLevelChart.innerHTML = '<div class="wx-bars">' + bars + '</div>';
    }

    function currentSelection() {
        const selectedRiver = riverByKey(riverSelect.value) || rivers[0] || null;
        const selectedStation = stationByKey(selectedRiver, basinSelect.value)
            || (selectedRiver && Array.isArray(selectedRiver.stations) ? selectedRiver.stations[0] : null);

        return { river: selectedRiver, station: selectedStation };
    }

    function populateRivers() {
        riverSelect.innerHTML = '';

        if (!Array.isArray(rivers) || rivers.length === 0) {
            riverSelect.innerHTML = '<option value="">No rivers available</option>';
            basinSelect.innerHTML = '<option value="">No basin locations available</option>';
            renderEmpty(rainfallChart, 'No rainfall data available.');
            renderEmpty(dischargeChart, 'No discharge data available.');
            renderEmpty(temperatureChart, 'No temperature data available.');
            renderEmpty(waterLevelChart, 'No water-level data available.');
            metaBox.textContent = 'No forecast or water-level data is currently available.';
            return;
        }

        rivers.forEach((river) => {
            const option = document.createElement('option');
            option.value = river.river_key || '';
            option.textContent = river.river_name || river.river_key || 'Unnamed river';
            riverSelect.appendChild(option);
        });

        const defaultRiver = defaults.river_key || defaults.riverKey;
        if (defaultRiver && rivers.some((river) => river.river_key === defaultRiver)) {
            riverSelect.value = defaultRiver;
        } else {
            riverSelect.selectedIndex = 0;
        }

        populateBasins();
        renderSelected();
    }

    function populateBasins() {
        basinSelect.innerHTML = '';

        const river = riverByKey(riverSelect.value);
        const stations = river && Array.isArray(river.stations) ? river.stations : [];

        if (stations.length === 0) {
            basinSelect.innerHTML = '<option value="">No basin locations available</option>';
            return;
        }

        stations.forEach((station) => {
            const option = document.createElement('option');
            option.value = station.station_key || '';
            option.textContent = station.station_name || station.station_key || 'Unnamed location';
            basinSelect.appendChild(option);
        });

        const defaultStation = defaults.station_key || defaults.stationKey;
        if (defaultStation && stations.some((station) => station.station_key === defaultStation)) {
            basinSelect.value = defaultStation;
        } else {
            basinSelect.selectedIndex = 0;
        }
    }

    function renderSelected() {
        const selection = currentSelection();
        renderMeta(selection.river, selection.station);

        if (!selection.station) {
            renderEmpty(rainfallChart, 'No rainfall data available for the selected river.');
            renderEmpty(dischargeChart, 'No discharge data available for the selected river.');
            renderEmpty(temperatureChart, 'No temperature data available for the selected river.');
            renderEmpty(waterLevelChart, 'No water-level data available for the selected river.');
            return;
        }

        renderRainfall(selection.station);
        renderDischarge(selection.station);
        renderTemperature(selection.station);
        renderWaterLevels(selection.station);
    }

    function populateSmsRiverOptions() {
        if (!canManageSms || !smsRiverSelect) {
            return;
        }

        smsRiverSelect.innerHTML = '<option value="">Auto (use GN mapping)</option>';
        rivers.forEach((river) => {
            const option = document.createElement('option');
            option.value = river.river_key || '';
            option.textContent = river.river_name || river.river_key || 'Unnamed river';
            smsRiverSelect.appendChild(option);
        });

        const selectedRiver = String(smsPref.river_key || '');
        if (selectedRiver && rivers.some((river) => String(river.river_key || '') === selectedRiver)) {
            smsRiverSelect.value = selectedRiver;
        }
    }

    function populateSmsStationOptions() {
        if (!canManageSms || !smsStationSelect || !smsRiverSelect) {
            return;
        }

        const river = riverByKey(smsRiverSelect.value);
        const stations = river && Array.isArray(river.stations) ? river.stations : [];

        smsStationSelect.innerHTML = '<option value="">Auto (closest GN-mapped station)</option>';
        stations.forEach((station) => {
            const option = document.createElement('option');
            option.value = station.station_key || '';
            option.textContent = station.station_name || station.station_key || 'Unnamed location';
            smsStationSelect.appendChild(option);
        });

        const selectedStation = String(smsPref.station_key || '');
        if (selectedStation && stations.some((station) => String(station.station_key || '') === selectedStation)) {
            smsStationSelect.value = selectedStation;
        }
    }

    function syncSmsControlState() {
        if (!canManageSms || !smsAlertCheckbox || !smsRiverSelect || !smsStationSelect) {
            return;
        }

        const enabled = smsAlertCheckbox.checked;
        smsRiverSelect.disabled = !enabled;
        smsStationSelect.disabled = !enabled;
    }

    riverSelect.addEventListener('change', () => {
        populateBasins();
        renderSelected();
    });

    basinSelect.addEventListener('change', renderSelected);

    if (canManageSms && smsAlertCheckbox && smsRiverSelect && smsStationSelect) {
        smsAlertCheckbox.checked = Boolean(smsPref.enabled);
        populateSmsRiverOptions();
        populateSmsStationOptions();
        syncSmsControlState();

        smsAlertCheckbox.addEventListener('change', syncSmsControlState);
        smsRiverSelect.addEventListener('change', () => {
            smsPref.station_key = '';
            populateSmsStationOptions();
        });
    }

    populateRivers();
})();
</script>
