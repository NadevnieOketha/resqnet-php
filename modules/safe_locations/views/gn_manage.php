<?php
$locations = $locations ?? [];
?>

<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""
/>

<style>
  .gn-safe-shell { display:grid; gap:1rem; }
  .gn-safe-card { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1rem; }
  .gn-safe-map-card { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1rem; }
  .gn-safe-map { width:100%; height:340px; border:1px solid var(--color-border); border-radius:var(--radius-md); }
  .gn-safe-table-shell { border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:auto; background:#fff; }
  .gn-safe-table { width:100%; border-collapse:collapse; font-size:0.72rem; min-width:1180px; }
  .gn-safe-table thead th { text-align:left; padding:0.75rem 0.8rem; background:#fafafa; border-bottom:1px solid var(--color-border); }
  .gn-safe-table tbody td { padding:0.75rem 0.8rem; border-bottom:1px solid var(--color-border); vertical-align:top; }
  .gn-safe-table tbody tr:last-child td { border-bottom:none; }
  .occupancy-grid { display:grid; grid-template-columns:repeat(5, minmax(90px, 1fr)); gap:0.4rem; }
  .occupancy-grid .input { padding:0.45rem 0.55rem; font-size:0.68rem; }
  .status-open { color:#1f7a1f; font-weight:700; }
  .status-full { color:#a4161a; font-weight:700; }
  .tiny { font-size:0.62rem; color:#666; }
</style>

<section class="gn-safe-shell">
  <div class="gn-safe-card">
    <h1 style="margin:0 0 0.3rem;">Safe Locations</h1>
    <p class="muted" style="margin:0;">Update occupancy only for shelters assigned to your GN division.</p>
  </div>

  <div class="gn-safe-map-card">
    <h2 style="margin:0 0 0.35rem;">Assigned Shelters Map</h2>
    <p class="muted" style="margin:0 0 0.75rem;">OpenStreetMap view of shelters assigned to your account.</p>
    <div id="gnSafeMap" class="gn-safe-map" aria-label="GN safe locations map"></div>
    <p class="tiny" style="margin:0.6rem 0 0;">Map source: <a href="https://www.openstreetmap.org" target="_blank" rel="noopener noreferrer">OpenStreetMap</a></p>
  </div>

  <div class="gn-safe-table-shell">
    <table class="gn-safe-table">
      <thead>
        <tr>
          <th>Location</th>
          <th>Address</th>
          <th>Capacity</th>
          <th>Occupancy by Category</th>
          <th>Update</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($locations)): ?>
          <tr><td colspan="5" class="tiny">No safe locations are assigned to your account yet.</td></tr>
        <?php else: ?>
          <?php foreach ($locations as $location): ?>
            <?php
              $capacity = (int) ($location['max_capacity'] ?? 0);
              $current = (int) ($location['current_occupancy'] ?? 0);
              $available = (int) ($location['available_space'] ?? 0);
              $statusClass = $available > 0 ? 'status-open' : 'status-full';
            ?>
            <tr>
              <td>
                <strong><?= e((string) ($location['location_name'] ?? '-')) ?></strong><br>
                <span class="tiny">#<?= (int) ($location['location_id'] ?? 0) ?></span>
              </td>
              <td>
                <?= e(safe_locations_address_line($location)) ?><br>
                <span class="tiny">Lat: <?= e((string) ($location['latitude'] ?? '-')) ?> | Lng: <?= e((string) ($location['longitude'] ?? '-')) ?></span>
              </td>
              <td>
                <strong><?= $current ?>/<?= $capacity ?></strong><br>
                <span class="<?= $statusClass ?>"><?= $available > 0 ? ($available . ' available') : 'Full' ?></span>
              </td>
              <td>
                <form method="POST" action="/dashboard/safe-locations/<?= (int) ($location['location_id'] ?? 0) ?>/occupancy" id="occupancy-form-<?= (int) ($location['location_id'] ?? 0) ?>">
                  <?= csrf_field() ?>
                  <div class="occupancy-grid">
                    <div>
                      <label class="tiny" for="toddlers-<?= (int) ($location['location_id'] ?? 0) ?>">Toddlers</label>
                      <input class="input" type="number" min="0" id="toddlers-<?= (int) ($location['location_id'] ?? 0) ?>" name="toddlers" value="<?= (int) ($location['toddlers'] ?? 0) ?>" required>
                    </div>
                    <div>
                      <label class="tiny" for="children-<?= (int) ($location['location_id'] ?? 0) ?>">Children</label>
                      <input class="input" type="number" min="0" id="children-<?= (int) ($location['location_id'] ?? 0) ?>" name="children" value="<?= (int) ($location['children'] ?? 0) ?>" required>
                    </div>
                    <div>
                      <label class="tiny" for="adults-<?= (int) ($location['location_id'] ?? 0) ?>">Adults</label>
                      <input class="input" type="number" min="0" id="adults-<?= (int) ($location['location_id'] ?? 0) ?>" name="adults" value="<?= (int) ($location['adults'] ?? 0) ?>" required>
                    </div>
                    <div>
                      <label class="tiny" for="elderly-<?= (int) ($location['location_id'] ?? 0) ?>">Elderly</label>
                      <input class="input" type="number" min="0" id="elderly-<?= (int) ($location['location_id'] ?? 0) ?>" name="elderly" value="<?= (int) ($location['elderly'] ?? 0) ?>" required>
                    </div>
                    <div>
                      <label class="tiny" for="pregnant-<?= (int) ($location['location_id'] ?? 0) ?>">Pregnant Women</label>
                      <input class="input" type="number" min="0" id="pregnant-<?= (int) ($location['location_id'] ?? 0) ?>" name="pregnant_women" value="<?= (int) ($location['pregnant_women'] ?? 0) ?>" required>
                    </div>
                  </div>
                </form>
              </td>
              <td>
                <button type="submit" class="btn btn-primary btn-sm" form="occupancy-form-<?= (int) ($location['location_id'] ?? 0) ?>">Save Occupancy</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  (function () {
    const mapLocations = <?= json_encode($locations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let map = null;
    let markerLayer = null;

    function escapeHtml(value) {
      return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }

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

    function availableSpace(location) {
      const max = toInt(location.max_capacity);
      const current = toInt(location.current_occupancy);
      return Math.max(0, max - current);
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

    function popupHtml(location) {
      const max = toInt(location.max_capacity);
      const current = toInt(location.current_occupancy);
      const available = availableSpace(location);

      return [
        '<div style="font-size:12px;line-height:1.35;">',
        '<strong>' + escapeHtml(location.location_name || 'Safe Location') + '</strong><br>',
        'Address: ' + escapeHtml(addressLine(location)) + '<br>',
        'Capacity: ' + max + '<br>',
        'Current: ' + current + '<br>',
        'Available: ' + available + '<br>',
        '<a href="' + escapeHtml(openStreetMapUrl(location)) + '" target="_blank" rel="noopener noreferrer">Open in OpenStreetMap</a>',
        '</div>'
      ].join('');
    }

    function initMap() {
      if (!window.L) {
        return;
      }

      map = L.map('gnSafeMap').setView([7.8731, 80.7718], 9);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      markerLayer = L.layerGroup().addTo(map);
      renderMarkers();
    }

    function renderMarkers() {
      if (!map || !markerLayer) {
        return;
      }

      markerLayer.clearLayers();
      const bounds = [];

      (Array.isArray(mapLocations) ? mapLocations : []).forEach((location) => {
        const lat = toFloat(location.latitude);
        const lng = toFloat(location.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
          return;
        }

        const color = availableSpace(location) > 0 ? '#2f9e44' : '#d64545';
        const marker = L.circleMarker([lat, lng], {
          radius: 8,
          color,
          fillColor: color,
          fillOpacity: 0.92,
          weight: 2,
        });

        marker.bindPopup(popupHtml(location));
        marker.addTo(markerLayer);
        bounds.push([lat, lng]);
      });

      if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [28, 28] });
      }
    }

    initMap();
  })();
</script>
