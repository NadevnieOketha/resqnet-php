<?php
$snapshot = is_array($rainfall_snapshot ?? null) ? $rainfall_snapshot : [];
$defaultSelection = is_array($default_selection ?? null) ? $default_selection : [];

$snapshotJson = json_encode($snapshot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$snapshotJson = is_string($snapshotJson) ? $snapshotJson : '{}';

$defaultJson = json_encode($defaultSelection, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$defaultJson = is_string($defaultJson) ? $defaultJson : '{}';
?>

<style>
  .forecast-header {
    margin-bottom: 0.85rem;
  }

  .forecast-header p {
    margin: 0.3rem 0 0;
    font-size: 0.78rem;
    color: #526071;
  }

  .forecast-toolbar {
    display: grid;
    gap: 0.9rem 1rem;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }

  .forecast-meta {
    display: grid;
    gap: 0.45rem 1rem;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    margin-top: 0.85rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: #f8fbff;
    padding: 0.78rem;
    font-size: 0.76rem;
    color: #334155;
  }

  .forecast-chart-wrap {
    margin-top: 0.85rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: #fff;
    padding: 0.65rem;
  }

    .forecast-chart-title {
        margin: 0 0 0.55rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #334155;
    }

  .forecast-chart {
    display: flex;
    align-items: flex-end;
    gap: 0.6rem;
    overflow-x: auto;
    min-height: 228px;
  }

  .forecast-column {
    min-width: 84px;
    display: grid;
    gap: 0.35rem;
    justify-items: center;
  }

  .forecast-mm {
    font-size: 0.68rem;
    font-weight: 700;
    color: #0f4f73;
  }

  .forecast-track {
    width: 100%;
    min-height: 162px;
    border-radius: 11px;
    padding: 0.2rem;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    background: linear-gradient(180deg, #ebf8ff 0%, #dbeafe 100%);
    box-sizing: border-box;
  }

  .forecast-fill {
    width: 100%;
    border-radius: 9px;
    background: linear-gradient(180deg, #2dd4bf 0%, #0284c7 100%);
  }

  .forecast-date {
    font-size: 0.67rem;
    font-weight: 600;
    color: #334155;
    text-align: center;
    line-height: 1.1;
  }

  .forecast-day-label {
    font-size: 0.64rem;
    color: #64748b;
    text-align: center;
    line-height: 1.1;
  }

  .forecast-empty {
    margin-top: 0.75rem;
    padding: 0.75rem;
    border: 1px dashed var(--color-border);
    border-radius: var(--radius-md);
    background: #fafafa;
    color: #6b7280;
    font-size: 0.78rem;
  }

    .forecast-temp-chart {
        display: flex;
        align-items: flex-end;
        gap: 0.6rem;
        overflow-x: auto;
        min-height: 218px;
    }

    .forecast-temp-column {
        min-width: 84px;
        display: grid;
        gap: 0.35rem;
        justify-items: center;
    }

    .forecast-temp-values {
        font-size: 0.67rem;
        font-weight: 700;
        color: #7c2d12;
        text-align: center;
        line-height: 1.15;
    }

    .forecast-temp-track {
        width: 100%;
        min-height: 152px;
        border-radius: 11px;
        padding: 0.2rem;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        background: linear-gradient(180deg, #fff7ed 0%, #e0f2fe 100%);
        box-sizing: border-box;
    }

    .forecast-temp-bars {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 0.2rem;
    }

    .forecast-temp-max,
    .forecast-temp-min {
        width: calc(50% - 0.14rem);
        border-radius: 6px 6px 3px 3px;
    }

    .forecast-temp-max {
        background: linear-gradient(180deg, #fb923c 0%, #f97316 100%);
    }

    .forecast-temp-min {
        background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
    }

    .forecast-flood-chart {
        display: flex;
        align-items: flex-end;
        gap: 0.6rem;
        overflow-x: auto;
        min-height: 218px;
    }

    .forecast-flood-column {
        min-width: 86px;
        display: grid;
        gap: 0.35rem;
        justify-items: center;
    }

    .forecast-flood-value {
        font-size: 0.67rem;
        font-weight: 700;
        color: #365314;
        text-align: center;
        line-height: 1.15;
    }

    .forecast-flood-track {
        width: 100%;
        min-height: 152px;
        border-radius: 11px;
        padding: 0.2rem;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%);
        box-sizing: border-box;
    }

    .forecast-flood-fill {
        width: 100%;
        border-radius: 6px;
        background: linear-gradient(180deg, #4ade80 0%, #16a34a 100%);
    }
</style>

<section class="section-card" aria-label="River basin forecast dashboard">
    <div class="forecast-header">
        <h1 style="margin:0;">Forecast Dashboard</h1>
        <p>Open-Meteo rainfall outlook covering day prior to yesterday, yesterday, today, and 7 forecast days across configured hydrometric stations.</p>
    </div>

    <div class="forecast-toolbar">
        <div class="form-group" style="margin:0;">
            <label for="forecast_river_select">River</label>
            <select id="forecast_river_select" class="input"></select>
        </div>
        <div class="form-group" style="margin:0;">
            <label for="forecast_station_select">River Basin / Hydrometric Station</label>
            <select id="forecast_station_select" class="input"></select>
        </div>
    </div>

    <div class="forecast-meta" id="forecast_meta" aria-live="polite"></div>

    <div class="forecast-chart-wrap" id="forecast_chart_wrap">
        <p class="forecast-chart-title">Daily Rainfall (mm)</p>
        <div class="forecast-chart" id="forecast_chart" role="img" aria-label="Daily rainfall forecast chart"></div>
    </div>

    <div class="forecast-chart-wrap" id="forecast_temp_wrap">
        <p class="forecast-chart-title">Daily Temperature (Max/Min °C)</p>
        <div class="forecast-temp-chart" id="forecast_temp_chart" role="img" aria-label="Daily temperature max and min chart"></div>
    </div>

    <div class="forecast-chart-wrap" id="forecast_flood_wrap">
        <p class="forecast-chart-title">Daily Flood Level (River Discharge m³/s)</p>
        <div class="forecast-flood-chart" id="forecast_flood_chart" role="img" aria-label="Daily flood level chart"></div>
    </div>

    <div class="forecast-empty" id="forecast_empty" hidden>
        Forecast data is unavailable for the selected station right now. Please try a different station or reload.
    </div>
</section>

<script>
(() => {
    const snapshot = <?= $snapshotJson ?>;
    const defaultSelection = <?= $defaultJson ?>;
    const rivers = Array.isArray(snapshot.rivers) ? snapshot.rivers : [];

    const riverSelect = document.getElementById('forecast_river_select');
    const stationSelect = document.getElementById('forecast_station_select');
    const metaEl = document.getElementById('forecast_meta');
    const chartEl = document.getElementById('forecast_chart');
    const tempChartEl = document.getElementById('forecast_temp_chart');
    const floodChartEl = document.getElementById('forecast_flood_chart');
    const chartWrapEl = document.getElementById('forecast_chart_wrap');
    const tempWrapEl = document.getElementById('forecast_temp_wrap');
    const floodWrapEl = document.getElementById('forecast_flood_wrap');
    const emptyEl = document.getElementById('forecast_empty');

    if (!riverSelect || !stationSelect || !metaEl || !chartEl || !tempChartEl || !floodChartEl || !chartWrapEl || !tempWrapEl || !floodWrapEl || !emptyEl) {
        return;
    }

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char] || char));

    const formatDate = (isoDate) => {
        const date = new Date(`${isoDate}T00:00:00`);
        if (Number.isNaN(date.getTime())) {
            return isoDate;
        }

        return date.toLocaleDateString('en-LK', {
            month: 'short',
            day: 'numeric'
        });
    };

    const relativeLabel = (isoDate) => {
        const now = new Date();
        now.setHours(0, 0, 0, 0);

        const date = new Date(`${isoDate}T00:00:00`);
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        const delta = Math.round((date.getTime() - now.getTime()) / 86400000);
        if (delta === -2) return 'Day Prior to Yesterday';
        if (delta === -1) return 'Yesterday';
        if (delta === 0) return 'Today';
        if (delta > 0) return `D+${delta}`;
        return `D${delta}`;
    };

    const getStationsByRiver = (riverKey) => {
        const river = rivers.find((row) => String(row.river_key) === String(riverKey));
        return Array.isArray(river?.stations) ? river.stations : [];
    };

    const populateRivers = () => {
        riverSelect.innerHTML = rivers.map((river, idx) => {
            const key = escapeHtml(river.river_key || `river_${idx}`);
            const label = escapeHtml(river.river_name || `River ${idx + 1}`);
            return `<option value="${key}">${label}</option>`;
        }).join('');
    };

    const populateStations = (riverKey) => {
        const stations = getStationsByRiver(riverKey);
        stationSelect.innerHTML = stations.map((station, idx) => {
            const key = escapeHtml(station.station_key || `station_${idx}`);
            const label = escapeHtml(station.station_name || `Station ${idx + 1}`);
            return `<option value="${key}">${label}</option>`;
        }).join('');
    };

    const renderSelected = () => {
        const river = rivers.find((row) => String(row.river_key) === String(riverSelect.value));
        const stations = Array.isArray(river?.stations) ? river.stations : [];
        const station = stations.find((row) => String(row.station_key) === String(stationSelect.value));

        if (!river || !station) {
            chartWrapEl.hidden = true;
            tempWrapEl.hidden = true;
            floodWrapEl.hidden = true;
            chartEl.innerHTML = '';
            tempChartEl.innerHTML = '';
            floodChartEl.innerHTML = '';
            emptyEl.hidden = false;
            metaEl.innerHTML = '<div>No station selected.</div>';
            return;
        }

        const daily = Array.isArray(station.daily) ? station.daily : [];

        metaEl.innerHTML = [
            `<div><strong>River:</strong> ${escapeHtml(river.river_name || '-')}</div>`,
            `<div><strong>Station:</strong> ${escapeHtml(station.station_name || '-')}</div>`,
            `<div><strong>District:</strong> ${escapeHtml(station.district || '-')}</div>`,
            `<div><strong>Local Area:</strong> ${escapeHtml(station.local_area || '-')}</div>`,
            `<div><strong>Coordinates:</strong> ${escapeHtml(station.latitude)}, ${escapeHtml(station.longitude)}</div>`,
            `<div><strong>Updated:</strong> ${escapeHtml(snapshot.fetched_at || '-')}</div>`
        ].join('');

        if (daily.length === 0) {
            chartWrapEl.hidden = true;
            tempWrapEl.hidden = true;
            floodWrapEl.hidden = true;
            chartEl.innerHTML = '';
            tempChartEl.innerHTML = '';
            floodChartEl.innerHTML = '';
            emptyEl.hidden = false;
            return;
        }

        const rainValues = daily.map((row) => Number(row.rain_sum)).filter((value) => Number.isFinite(value));
        const maxRain = Math.max(...rainValues, 1);

        chartEl.innerHTML = daily.map((row) => {
            const rain = Number(row.rain_sum);
            const value = Number.isFinite(rain) ? rain : 0;
            const height = Math.max(8, Math.round((value / maxRain) * 150));

            return `
                <div class="forecast-column">
                    <span class="forecast-mm">${value.toFixed(1)} mm</span>
                    <div class="forecast-track">
                        <div class="forecast-fill" style="height:${height}px"></div>
                    </div>
                    <span class="forecast-date">${escapeHtml(formatDate(row.date || ''))}</span>
                    <span class="forecast-day-label">${escapeHtml(relativeLabel(row.date || ''))}</span>
                </div>
            `;
        }).join('');

        const maxTempValues = daily
            .map((row) => Number(row.temperature_2m_max))
            .filter((value) => Number.isFinite(value));
        const minTempValues = daily
            .map((row) => Number(row.temperature_2m_min))
            .filter((value) => Number.isFinite(value));
        const tempValues = [...maxTempValues, ...minTempValues];

        if (tempValues.length === 0) {
            tempWrapEl.hidden = true;
            tempChartEl.innerHTML = '';
        } else {
            const minTemp = Math.min(...tempValues);
            const maxTemp = Math.max(...tempValues);
            const tempRange = Math.max(1, maxTemp - minTemp);

            tempChartEl.innerHTML = daily.map((row) => {
                const rawMax = Number(row.temperature_2m_max);
                const rawMin = Number(row.temperature_2m_min);
                const maxValue = Number.isFinite(rawMax) ? rawMax : minTemp;
                const minValue = Number.isFinite(rawMin) ? rawMin : minTemp;

                const maxHeight = Math.max(8, Math.round(((maxValue - minTemp) / tempRange) * 140));
                const minHeight = Math.max(8, Math.round(((minValue - minTemp) / tempRange) * 140));

                return `
                    <div class="forecast-temp-column">
                        <span class="forecast-temp-values">${maxValue.toFixed(1)}° / ${minValue.toFixed(1)}°</span>
                        <div class="forecast-temp-track">
                            <div class="forecast-temp-bars">
                                <div class="forecast-temp-max" style="height:${maxHeight}px"></div>
                                <div class="forecast-temp-min" style="height:${minHeight}px"></div>
                            </div>
                        </div>
                        <span class="forecast-date">${escapeHtml(formatDate(row.date || ''))}</span>
                        <span class="forecast-day-label">${escapeHtml(relativeLabel(row.date || ''))}</span>
                    </div>
                `;
            }).join('');

            tempWrapEl.hidden = false;
        }

        const floodValues = daily
            .map((row) => Number(row.river_discharge))
            .filter((value) => Number.isFinite(value));

        if (floodValues.length === 0) {
            floodWrapEl.hidden = true;
            floodChartEl.innerHTML = '';
        } else {
            const maxFlood = Math.max(...floodValues, 1);

            floodChartEl.innerHTML = daily.map((row) => {
                const rawDischarge = Number(row.river_discharge);
                const rawMax = Number(row.river_discharge_max);
                const rawMin = Number(row.river_discharge_min);

                const discharge = Number.isFinite(rawDischarge) ? rawDischarge : 0;
                const dischargeMax = Number.isFinite(rawMax) ? rawMax : null;
                const dischargeMin = Number.isFinite(rawMin) ? rawMin : null;
                const height = Math.max(8, Math.round((discharge / maxFlood) * 150));

                const summaryParts = [];
                if (dischargeMax !== null) summaryParts.push(`max ${dischargeMax.toFixed(1)}`);
                if (dischargeMin !== null) summaryParts.push(`min ${dischargeMin.toFixed(1)}`);

                return `
                    <div class="forecast-flood-column">
                        <span class="forecast-flood-value">${discharge.toFixed(1)} m³/s${summaryParts.length ? `<br><span style="font-weight:500;color:#4b5563;">${summaryParts.join(' / ')}</span>` : ''}</span>
                        <div class="forecast-flood-track">
                            <div class="forecast-flood-fill" style="height:${height}px"></div>
                        </div>
                        <span class="forecast-date">${escapeHtml(formatDate(row.date || ''))}</span>
                        <span class="forecast-day-label">${escapeHtml(relativeLabel(row.date || ''))}</span>
                    </div>
                `;
            }).join('');

            floodWrapEl.hidden = false;
        }

        chartWrapEl.hidden = false;
        emptyEl.hidden = true;
    };

    if (rivers.length === 0) {
        riverSelect.innerHTML = '<option value="">No river data available</option>';
        stationSelect.innerHTML = '<option value="">No station data available</option>';
        riverSelect.disabled = true;
        stationSelect.disabled = true;
        chartWrapEl.hidden = true;
        tempWrapEl.hidden = true;
        floodWrapEl.hidden = true;
        emptyEl.hidden = false;
        metaEl.innerHTML = '<div>Weather API data is currently unavailable.</div>';
        return;
    }

    populateRivers();

    const defaultRiver = String(defaultSelection.river_key || '');
    if (defaultRiver !== '' && rivers.some((river) => String(river.river_key) === defaultRiver)) {
        riverSelect.value = defaultRiver;
    }

    populateStations(riverSelect.value);

    const defaultStation = String(defaultSelection.station_key || '');
    const options = Array.from(stationSelect.options).map((opt) => String(opt.value));
    if (defaultStation !== '' && options.includes(defaultStation)) {
        stationSelect.value = defaultStation;
    }

    riverSelect.addEventListener('change', () => {
        populateStations(riverSelect.value);
        renderSelected();
    });

    stationSelect.addEventListener('change', renderSelected);

    renderSelected();
})();
</script>
