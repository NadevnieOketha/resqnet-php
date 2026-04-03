<?php
$districtMap = $district_map ?? [];
$districts = $districts ?? [];
$selectedDistrict = (string) ($selected_district ?? '');
$selectedGnDivision = (string) ($selected_gn_division ?? '');
$availableOnly = (bool) ($available_only ?? false);
$defaultDistrict = (string) ($default_district ?? '');
$defaultGnDivision = (string) ($default_gn_division ?? '');
$locations = $locations ?? [];
?>

<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""
/>

<style>
  .safe-locations-shell { display:grid; gap:1rem; }
  .safe-map-panel { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1rem; }
  .filter-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:0.8rem; align-items:end; margin-bottom:0.85rem; }
  .safe-map { width:100%; height:420px; border:1px solid var(--color-border); border-radius:var(--radius-md); }
  .status-dot { display:inline-block; width:10px; height:10px; border-radius:999px; margin-right:0.35rem; vertical-align:middle; }
  .status-dot-green { background:#2f9e44; }
  .status-dot-red { background:#d64545; }
  .nearest-box { border:1px solid var(--color-border); border-radius:var(--radius-md); padding:0.8rem 0.9rem; background:#fff; margin-top:0.85rem; }
  .nearest-box strong { display:block; margin-bottom:0.2rem; }
  .safe-list { display:grid; gap:0.75rem; }
  .safe-card { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-md); padding:0.9rem; }
  .safe-card-head { display:flex; justify-content:space-between; gap:0.75rem; align-items:flex-start; }
  .safe-card h3 { margin:0; font-size:0.9rem; }
  .safe-meta { color:var(--color-text-subtle); font-size:0.72rem; margin-top:0.35rem; }
  .occupancy-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(110px,1fr)); gap:0.45rem; margin-top:0.7rem; }
  .occupancy-chip { border:1px solid var(--color-border); border-radius:999px; padding:0.25rem 0.5rem; font-size:0.62rem; background:#fafafa; text-align:center; }
  .capacity-state { font-size:0.72rem; margin-top:0.55rem; font-weight:600; }
  .capacity-state.available { color:#1f7a1f; }
  .capacity-state.full { color:#a4161a; }
</style>

<section class="safe-locations-shell">
  <div class="safe-map-panel">
    <h1 style="margin:0 0 0.3rem;">Safe Locations Map</h1>
    <p class="muted" style="margin:0 0 0.75rem;">Find nearby shelters and check live occupancy before traveling.</p>

    <form id="safeLocationFilterForm" method="GET" action="/safe-locations" class="filter-row">
      <div class="form-group" style="margin:0;">
        <label for="district">District</label>
        <select id="district" name="district" class="input">
          <option value="">All districts</option>
          <?php foreach ($districts as $district): ?>
            <option value="<?= e((string) $district) ?>" <?= $selectedDistrict === $district ? 'selected' : '' ?>><?= e((string) $district) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="gn_division">GN Division</label>
        <select id="gn_division" name="gn_division" class="input">
          <option value="">All GN divisions</option>
        </select>
      </div>

      <div class="form-group" style="margin:0; display:flex; gap:0.5rem; align-items:center;">
        <input type="checkbox" id="available_only" name="available_only" value="1" <?= $availableOnly ? 'checked' : '' ?>>
        <label for="available_only" style="margin:0; font-weight:500;">Only show available shelters</label>
      </div>

      <div class="form-group" style="margin:0; display:flex; gap:0.5rem;">
        <button type="submit" class="btn btn-primary">Apply</button>
        <button type="button" class="btn btn-outline" id="nearestBtn">Find Nearest Available</button>
      </div>
    </form>

    <div style="font-size:0.72rem; margin-bottom:0.65rem;">
      <span><span class="status-dot status-dot-green"></span>Space available</span>
      <span style="margin-left:0.9rem;"><span class="status-dot status-dot-red"></span>Full</span>
      <span style="margin-left:0.9rem;" class="muted">Map source: <a href="https://www.openstreetmap.org" target="_blank" rel="noopener noreferrer">OpenStreetMap</a></span>
    </div>

    <div id="safeMap" class="safe-map" aria-label="Safe locations map"></div>

    <div id="nearestBox" class="nearest-box" style="display:none;"></div>
  </div>

  <div class="safe-list" id="safeLocationCards"></div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  (function () {
    const districtMap = <?= json_encode($districtMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialLocations = <?= json_encode($locations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const selectedGnDivision = <?= json_encode($selectedGnDivision, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const defaultDistrict = <?= json_encode($defaultDistrict, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const defaultGnDivision = <?= json_encode($defaultGnDivision, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const districtEl = document.getElementById('district');
    const gnDivisionEl = document.getElementById('gn_division');
    const availableOnlyEl = document.getElementById('available_only');
    const filterFormEl = document.getElementById('safeLocationFilterForm');
    const safeLocationCardsEl = document.getElementById('safeLocationCards');
    const nearestBtnEl = document.getElementById('nearestBtn');
    const nearestBoxEl = document.getElementById('nearestBox');

    let map = null;
    let markerLayer = null;
    let currentLocations = Array.isArray(initialLocations) ? initialLocations : [];

    function toInt(value) {
      const number = Number(value);
      return Number.isFinite(number) ? Math.max(0, Math.round(number)) : 0;
    }

    function toFloat(value) {
      const number = Number(value);
      return Number.isFinite(number) ? number : NaN;
    }

    function addressLine(location) {
      const values = [
        location.address_house_no,
        location.address_street,
        location.address_city,
        location.district,
        location.gn_division,
      ].filter((item) => String(item || '').trim() !== '');

      return values.length ? values.join(', ') : '-';
    }

    function occupancyTotal(location) {
      return toInt(location.current_occupancy);
    }

    function availableSpace(location) {
      const capacity = toInt(location.max_capacity);
      return Math.max(0, capacity - occupancyTotal(location));
    }

    function isAvailable(location) {
      return availableSpace(location) > 0;
    }

    function renderGnOptions(preferredGn = '') {
      const district = districtEl.value;
      const options = districtMap[district] || [];

      gnDivisionEl.innerHTML = '';
      const allOption = document.createElement('option');
      allOption.value = '';
      allOption.textContent = 'All GN divisions';
      gnDivisionEl.appendChild(allOption);

      options.forEach((name) => {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        if (preferredGn && preferredGn === name) {
          option.selected = true;
        }
        gnDivisionEl.appendChild(option);
      });

      if (preferredGn && !options.includes(preferredGn)) {
        const custom = document.createElement('option');
        custom.value = preferredGn;
        custom.textContent = preferredGn;
        custom.selected = true;
        gnDivisionEl.appendChild(custom);
      }
    }

    function createPopupContent(location) {
      const capacity = toInt(location.max_capacity);
      const current = occupancyTotal(location);
      const available = availableSpace(location);
      const state = available > 0 ? 'Space available' : 'Full';
      const osmUrl = openStreetMapUrl(location);

      return [
        '<div style="font-size:12px;line-height:1.35;">',
        '<strong>' + escapeHtml(String(location.location_name || 'Safe Location')) + '</strong><br>',
        'Address: ' + escapeHtml(addressLine(location)) + '<br>',
        'Capacity: ' + capacity + '<br>',
        'Current: ' + current + '<br>',
        'Status: <strong>' + state + '</strong><br>',
        '<a href="' + escapeHtml(osmUrl) + '" target="_blank" rel="noopener noreferrer">Open in OpenStreetMap</a>',
        '</div>'
      ].join('');
    }

    function openStreetMapUrl(location) {
      const lat = toFloat(location.latitude);
      const lng = toFloat(location.longitude);

      if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return 'https://www.openstreetmap.org';
      }

      return 'https://www.openstreetmap.org/?mlat=' + encodeURIComponent(String(lat))
        + '&mlon=' + encodeURIComponent(String(lng))
        + '#map=16/' + encodeURIComponent(String(lat)) + '/' + encodeURIComponent(String(lng));
    }

    function escapeHtml(value) {
      return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

    function renderMap(locations) {
      if (!window.L) {
        return;
      }

      if (!map) {
        map = L.map('safeMap').setView([7.8731, 80.7718], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        markerLayer = L.layerGroup().addTo(map);
      }

      markerLayer.clearLayers();

      const bounds = [];
      locations.forEach((location) => {
        const lat = toFloat(location.latitude);
        const lng = toFloat(location.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
          return;
        }

        const color = isAvailable(location) ? '#2f9e44' : '#d64545';
        const marker = L.circleMarker([lat, lng], {
          radius: 8,
          color,
          fillColor: color,
          fillOpacity: 0.9,
          weight: 2,
        });

        marker.bindPopup(createPopupContent(location));
        marker.addTo(markerLayer);
        bounds.push([lat, lng]);
      });

      if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [30, 30] });
      }
    }

    function renderCards(locations) {
      if (!Array.isArray(locations) || locations.length === 0) {
        safeLocationCardsEl.innerHTML = '<article class="safe-card"><p class="muted" style="margin:0;">No safe locations match the current filter.</p></article>';
        return;
      }

      safeLocationCardsEl.innerHTML = locations.map((location) => {
        const capacity = toInt(location.max_capacity);
        const current = occupancyTotal(location);
        const available = availableSpace(location);
        const availableClass = available > 0 ? 'available' : 'full';
        const availableLabel = available > 0 ? 'Space available' : 'Full';

        return [
          '<article class="safe-card">',
            '<div class="safe-card-head">',
              '<div>',
                '<h3>' + escapeHtml(String(location.location_name || 'Safe Location')) + '</h3>',
                '<div class="safe-meta">' + escapeHtml(addressLine(location)) + '</div>',
                '<div class="safe-meta"><a href="' + escapeHtml(openStreetMapUrl(location)) + '" target="_blank" rel="noopener noreferrer">View on OpenStreetMap</a></div>',
              '</div>',
              '<div style="text-align:right;font-size:0.7rem;">',
                '<div><strong>' + available + '</strong> free</div>',
                '<div class="muted">of ' + capacity + '</div>',
              '</div>',
            '</div>',
            '<div class="occupancy-grid">',
              '<div class="occupancy-chip">Toddlers: ' + toInt(location.toddlers) + '</div>',
              '<div class="occupancy-chip">Children: ' + toInt(location.children) + '</div>',
              '<div class="occupancy-chip">Adults: ' + toInt(location.adults) + '</div>',
              '<div class="occupancy-chip">Elderly: ' + toInt(location.elderly) + '</div>',
              '<div class="occupancy-chip">Pregnant Women: ' + toInt(location.pregnant_women) + '</div>',
            '</div>',
            '<div class="capacity-state ' + availableClass + '">Current: ' + current + ' / ' + capacity + ' (' + availableLabel + ')</div>',
          '</article>'
        ].join('');
      }).join('');
    }

    function currentFilterQuery() {
      const query = new URLSearchParams();
      if (districtEl.value) {
        query.set('district', districtEl.value);
      }
      if (gnDivisionEl.value) {
        query.set('gn_division', gnDivisionEl.value);
      }
      if (availableOnlyEl.checked) {
        query.set('available_only', '1');
      }
      return query;
    }

    function updateUrl(query) {
      const suffix = query.toString();
      const nextUrl = '/safe-locations' + (suffix ? ('?' + suffix) : '');
      window.history.replaceState({}, '', nextUrl);
    }

    async function refreshLocations() {
      const query = currentFilterQuery();
      try {
        const response = await fetch('/safe-locations/data?' + query.toString(), {
          headers: {
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        currentLocations = Array.isArray(payload.locations) ? payload.locations : [];
        renderMap(currentLocations);
        renderCards(currentLocations);
      } catch (error) {
        // Keep the latest successful state on transient network issues.
      }
    }

    function haversineKm(lat1, lon1, lat2, lon2) {
      const toRad = (deg) => deg * (Math.PI / 180);
      const r = 6371;
      const dLat = toRad(lat2 - lat1);
      const dLon = toRad(lon2 - lon1);
      const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
        + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
        * Math.sin(dLon / 2) * Math.sin(dLon / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return r * c;
    }

    function findNearestAvailable() {
      if (!navigator.geolocation) {
        nearestBoxEl.style.display = 'block';
        nearestBoxEl.innerHTML = '<strong>Nearest available shelter</strong><span class="muted">Location access is not supported in this browser.</span>';
        return;
      }

      navigator.geolocation.getCurrentPosition((position) => {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;

        let nearest = null;
        let nearestDistance = Infinity;

        currentLocations.forEach((location) => {
          if (!isAvailable(location)) {
            return;
          }

          const lat = toFloat(location.latitude);
          const lng = toFloat(location.longitude);
          if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
          }

          const distance = haversineKm(userLat, userLng, lat, lng);
          if (distance < nearestDistance) {
            nearestDistance = distance;
            nearest = location;
          }
        });

        nearestBoxEl.style.display = 'block';

        if (!nearest) {
          nearestBoxEl.innerHTML = '<strong>Nearest available shelter</strong><span class="muted">No shelter with available space was found for the current filter.</span>';
          return;
        }

        nearestBoxEl.innerHTML = [
          '<strong>Nearest available shelter</strong>',
          escapeHtml(String(nearest.location_name || 'Safe Location')),
          '<br><span class="muted">' + escapeHtml(addressLine(nearest)) + '</span>',
          '<br><span class="muted">Distance: ' + nearestDistance.toFixed(2) + ' km</span>'
        ].join('');

        if (map) {
          map.setView([toFloat(nearest.latitude), toFloat(nearest.longitude)], 13);
        }
      }, () => {
        nearestBoxEl.style.display = 'block';
        nearestBoxEl.innerHTML = '<strong>Nearest available shelter</strong><span class="muted">Location access was denied. Enable it to find nearest shelter.</span>';
      });
    }

    if (districtEl.value) {
      renderGnOptions(selectedGnDivision || defaultGnDivision);
    } else if (defaultDistrict && defaultGnDivision) {
      districtEl.value = defaultDistrict;
      renderGnOptions(defaultGnDivision);
      gnDivisionEl.value = defaultGnDivision;
    } else {
      renderGnOptions(selectedGnDivision);
    }

    renderMap(currentLocations);
    renderCards(currentLocations);

    districtEl.addEventListener('change', () => {
      renderGnOptions('');
    });

    filterFormEl.addEventListener('submit', async (event) => {
      event.preventDefault();
      const query = currentFilterQuery();
      updateUrl(query);
      await refreshLocations();
    });

    nearestBtnEl.addEventListener('click', findNearestAvailable);

    window.setInterval(refreshLocations, 30000);
  })();
</script>
