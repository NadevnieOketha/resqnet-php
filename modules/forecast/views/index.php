<?php
$snapshot = is_array($rainfall_snapshot ?? null) ? $rainfall_snapshot : [];
$defaultSelection = is_array($default_selection ?? null) ? $default_selection : [];

$snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$snapshotJson = is_string($snapshotJson) ? $snapshotJson : '{}';

$defaultJson = json_encode($defaultSelection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$defaultJson = is_string($defaultJson) ? $defaultJson : '{}';
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

    .wx-temp {
        border-left-color: #ea580c;
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

    .wx-flag {
        font-size: 0.62rem;
        border-radius: 999px;
        border: 1px solid #bbd3e4;
        background: #f1f8ff;
        color: #1f2937;
        padding: 0.08rem 0.46rem;
        font-weight: 700;
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
            <h2 class="wx-title">River Basin Rainfall and Temperature Forecast</h2>
            <p class="wx-subtitle">
                Data is fetched from Open-Meteo Weather Forecast API for day-before-yesterday, yesterday, today, and the next 7 forecast days.
                Select a river to filter its hydrometric basin locations.
            </p>
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

        <div id="metaBox" class="wx-meta">Select a river basin location to view details.</div>

        <div class="wx-grid">
            <section class="wx-card wx-rain">
                <div class="wx-head">
                    <h3>Daily Rainfall</h3>
                    <p class="wx-caption">Unit: mm/day</p>
                </div>
                <div class="wx-surface" id="rainfallChart"></div>
            </section>

            <section class="wx-card wx-temp">
                <div class="wx-head">
                    <h3>Daily Temperature Range</h3>
                    <p class="wx-caption">Unit: C (max/min)</p>
                </div>
                <div class="wx-surface" id="temperatureChart"></div>
            </section>
        </div>
    </div>
</section>

<script>
(() => {
    const snapshot = <?= $snapshotJson ?>;
    const defaults = <?= $defaultJson ?>;

    const rivers = Array.isArray(snapshot.rivers) ? snapshot.rivers : [];
    const source = String(snapshot.source || 'Open-Meteo');
    const fetchedAt = String(snapshot.fetched_at || '-');
    const windowInfo = snapshot.window || {};

    const riverSelect = document.getElementById('riverSelect');
    const basinSelect = document.getElementById('basinSelect');
    const metaBox = document.getElementById('metaBox');
    const rainfallChart = document.getElementById('rainfallChart');
    const temperatureChart = document.getElementById('temperatureChart');

    if (!riverSelect || !basinSelect || !metaBox || !rainfallChart || !temperatureChart) {
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

    function renderEmpty(element, text) {
        element.innerHTML = '<div class="wx-empty">' + esc(text) + '</div>';
    }

    function renderMeta(river, station) {
        if (!river || !station) {
            metaBox.textContent = 'No river basin location selected.';
            return;
        }

        const fromDate = windowInfo.from || '-';
        const toDate = windowInfo.to || '-';

        metaBox.innerHTML = ''
            + '<div class="wx-meta-grid">'
            + '<div class="wx-meta-item"><strong>River:</strong> ' + esc(river.river_name || '-') + '</div>'
            + '<div class="wx-meta-item"><strong>Hydrometric Station:</strong> ' + esc(station.station_name || '-') + '</div>'
            + '<div class="wx-meta-item"><strong>District:</strong> ' + esc(station.district || '-') + '</div>'
            + '<div class="wx-meta-item"><strong>GN / Local Area:</strong> ' + esc(station.local_area || '-') + '</div>'
            + '<div class="wx-meta-item"><strong>Coordinates:</strong> ' + esc(fmt(station.latitude, 4)) + ', ' + esc(fmt(station.longitude, 4)) + '</div>'
            + '<div class="wx-meta-item"><strong>Date Window:</strong> ' + esc(fromDate) + ' to ' + esc(toDate) + '</div>'
            + '<div class="wx-meta-item"><strong>Source:</strong> ' + esc(source) + '</div>'
            + '<div class="wx-meta-item"><strong>Fetched At:</strong> ' + esc(fetchedAt) + '</div>'
            + '</div>';
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
            const period = row && row.is_forecast_day ? 'Forecast' : 'Observed';

            return ''
                + '<div class="wx-col">'
                + '<div class="wx-value">' + esc(fmt(value, 1)) + ' mm</div>'
                + '<div class="wx-track"><span class="wx-rain-bar" style="height:' + height.toFixed(1) + 'px"></span></div>'
                + '<span class="wx-flag">' + esc(period) + '</span>'
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
            const period = row && row.is_forecast_day ? 'Forecast' : 'Observed';

            if (tMax === null || tMin === null) {
                return ''
                    + '<div class="wx-col">'
                    + '<div class="wx-value">-</div>'
                    + '<div class="wx-temp-track"></div>'
                    + '<span class="wx-flag">' + esc(period) + '</span>'
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
                + '<span class="wx-flag">' + esc(period) + '</span>'
                + '<div class="wx-date">' + esc(shortDate(row && row.date)) + '</div>'
                + '</div>';
        }).join('');

        temperatureChart.innerHTML = '<div class="wx-bars">' + bars + '</div>';
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
            renderEmpty(temperatureChart, 'No temperature data available.');
            metaBox.textContent = 'No Open-Meteo data is currently available.';
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
            renderEmpty(temperatureChart, 'No temperature data available for the selected river.');
            return;
        }

        renderRainfall(selection.station);
        renderTemperature(selection.station);
    }

    riverSelect.addEventListener('change', () => {
        populateBasins();
        renderSelected();
    });

    basinSelect.addEventListener('change', renderSelected);

    populateRivers();
})();
</script>
