<?php
$districtMap = $district_map ?? [];
$districts = $districts ?? [];
$gnOfficers = $gn_officers ?? [];
$locations = $locations ?? [];
$oldInput = $_SESSION['_old_input'] ?? [];

$oldValue = static function (string $key, string $default = '') use ($oldInput): string {
    return e((string) ($oldInput[$key] ?? $default));
};

$selectedDistrict = (string) ($oldInput['district'] ?? '');
$selectedGnDivision = (string) ($oldInput['gn_division'] ?? '');
?>

<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""
/>

<style>
  .safe-grid { display:grid; gap:1rem; }
  .safe-form-card { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1rem; }
  .safe-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:0.75rem; }
  .safe-map-card { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1rem; }
  .safe-map { width:100%; height:360px; border:1px solid var(--color-border); border-radius:var(--radius-md); }
  .safe-table-shell { border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:auto; background:#fff; }
  .safe-table { width:100%; border-collapse:collapse; font-size:0.72rem; min-width:1180px; }
  .safe-table thead th { text-align:left; padding:0.75rem 0.8rem; background:#fafafa; border-bottom:1px solid var(--color-border); }
  .safe-table tbody td { padding:0.75rem 0.8rem; border-bottom:1px solid var(--color-border); vertical-align:top; }
  .safe-table tbody tr:last-child td { border-bottom:none; }
  .tiny { color:#666; font-size:0.62rem; }
  .tag-full { color:#a4161a; font-weight:700; }
  .tag-open { color:#1f7a1f; font-weight:700; }
  details.edit-box { margin-top:0.45rem; }
  details.edit-box summary { cursor:pointer; color:#1f4f96; font-size:0.67rem; }
  .edit-form { margin-top:0.5rem; display:grid; gap:0.45rem; }
  .edit-form .input { font-size:0.68rem; padding:0.45rem 0.55rem; }
  .row-actions { display:flex; gap:0.35rem; align-items:center; }
</style>

<section class="safe-grid">
  <div class="safe-form-card">
    <h1 style="margin:0 0 0.3rem;">Safe Locations Management</h1>
    <p class="muted" style="margin:0 0 0.8rem;">Add, edit, and assign shelters for division-level occupancy management.</p>

    <form method="POST" action="/dashboard/admin/safe-locations/create" class="safe-form-grid">
      <?= csrf_field() ?>

      <div class="form-group" style="margin:0; grid-column:span 2;">
        <label for="location_name">Location name</label>
        <input class="input" type="text" id="location_name" name="location_name" value="<?= $oldValue('location_name') ?>" placeholder="e.g. Kaduwela Community Hall" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="address_house_no">House No (optional)</label>
        <input class="input" type="text" id="address_house_no" name="address_house_no" value="<?= $oldValue('address_house_no') ?>">
      </div>

      <div class="form-group" style="margin:0;">
        <label for="address_street">Street</label>
        <input class="input" type="text" id="address_street" name="address_street" value="<?= $oldValue('address_street') ?>" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="address_city">City</label>
        <input class="input" type="text" id="address_city" name="address_city" value="<?= $oldValue('address_city') ?>" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="district">District</label>
        <select class="input" id="district" name="district" required>
          <option value="">Select district</option>
          <?php foreach ($districts as $district): ?>
            <option value="<?= e((string) $district) ?>" <?= $selectedDistrict === $district ? 'selected' : '' ?>><?= e((string) $district) ?></option>
          <?php endforeach; ?>
          <option value="__other__" <?= $selectedDistrict === '__other__' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <div class="form-group <?= $selectedDistrict === '__other__' ? '' : 'hidden' ?>" id="district_other_wrap" style="margin:0;">
        <label for="district_other">Other district</label>
        <input class="input" type="text" id="district_other" name="district_other" value="<?= $oldValue('district_other') ?>" placeholder="Type district">
      </div>

      <div class="form-group" style="margin:0;">
        <label for="gn_division">GN Division</label>
        <select class="input" id="gn_division" name="gn_division" required>
          <option value="">Select district first</option>
        </select>
      </div>

      <div class="form-group <?= $selectedGnDivision === '__other__' ? '' : 'hidden' ?>" id="gn_division_other_wrap" style="margin:0;">
        <label for="gn_division_other">Other GN division</label>
        <input class="input" type="text" id="gn_division_other" name="gn_division_other" value="<?= $oldValue('gn_division_other') ?>" placeholder="Type GN division">
      </div>

      <div class="form-group" style="margin:0;">
        <label for="latitude">Latitude</label>
        <input class="input" type="number" id="latitude" name="latitude" step="0.00000001" min="-90" max="90" value="<?= $oldValue('latitude') ?>" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="longitude">Longitude</label>
        <input class="input" type="number" id="longitude" name="longitude" step="0.00000001" min="-180" max="180" value="<?= $oldValue('longitude') ?>" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="max_capacity">Maximum capacity</label>
        <input class="input" type="number" id="max_capacity" name="max_capacity" min="1" value="<?= $oldValue('max_capacity', '100') ?>" required>
      </div>

      <div class="form-group" style="margin:0; grid-column:span 2;">
        <label for="assigned_gn_user_id">Assigned GN officer</label>
        <select class="input" id="assigned_gn_user_id" name="assigned_gn_user_id" required>
          <option value="">Select responsible GN officer</option>
          <?php foreach ($gnOfficers as $officer): ?>
            <?php $officerId = (int) ($officer['user_id'] ?? 0); ?>
            <option value="<?= $officerId ?>" <?= $oldValue('assigned_gn_user_id') === (string) $officerId ? 'selected' : '' ?>>
              <?= e((string) (($officer['name'] ?? 'GN Officer') . ' - ' . ($officer['gn_division'] ?? '-'))) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:flex; gap:0.5rem; grid-column:1 / -1;">
        <button type="submit" class="btn btn-primary">Add Safe Location</button>
      </div>
    </form>
  </div>

  <div class="safe-map-card">
    <h2 style="margin:0 0 0.35rem;">Safe Locations Map</h2>
    <p class="muted" style="margin:0 0 0.75rem;">Interactive OpenStreetMap view of all shelters. Green markers have space, red markers are full.</p>
    <div id="dmcSafeMap" class="safe-map" aria-label="DMC safe locations map"></div>
    <p class="tiny" style="margin:0.6rem 0 0;">Map source: <a href="https://www.openstreetmap.org" target="_blank" rel="noopener noreferrer">OpenStreetMap</a></p>
  </div>

  <div class="safe-table-shell">
    <table class="safe-table">
      <thead>
        <tr>
          <th>Location</th>
          <th>Address</th>
          <th>GN Officer</th>
          <th>Coordinates</th>
          <th>Capacity</th>
          <th>Occupancy Breakdown</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($locations)): ?>
          <tr><td colspan="7" class="tiny">No safe locations have been created yet.</td></tr>
        <?php else: ?>
          <?php foreach ($locations as $location): ?>
            <?php
              $current = (int) ($location['current_occupancy'] ?? 0);
              $capacity = (int) ($location['max_capacity'] ?? 0);
              $available = (int) ($location['available_space'] ?? 0);
            ?>
            <tr>
              <td>
                <strong><?= e((string) ($location['location_name'] ?? '-')) ?></strong><br>
                <span class="tiny">#<?= (int) ($location['location_id'] ?? 0) ?></span>
              </td>
              <td><?= e((string) ($location['address_line'] ?? '-')) ?></td>
              <td>
                <?= e((string) ($location['assigned_gn_name'] ?? '-')) ?><br>
                <span class="tiny"><?= e((string) ($location['assigned_gn_contact'] ?? '')) ?></span>
              </td>
              <td>
                <span class="tiny">Lat: <?= e((string) ($location['latitude'] ?? '-')) ?></span><br>
                <span class="tiny">Lng: <?= e((string) ($location['longitude'] ?? '-')) ?></span>
              </td>
              <td>
                <strong><?= $current ?>/<?= $capacity ?></strong><br>
                <span class="<?= $available > 0 ? 'tag-open' : 'tag-full' ?>"><?= $available > 0 ? ($available . ' available') : 'Full' ?></span>
              </td>
              <td>
                <span class="tiny">Toddlers: <?= (int) ($location['toddlers'] ?? 0) ?></span><br>
                <span class="tiny">Children: <?= (int) ($location['children'] ?? 0) ?></span><br>
                <span class="tiny">Adults: <?= (int) ($location['adults'] ?? 0) ?></span><br>
                <span class="tiny">Elderly: <?= (int) ($location['elderly'] ?? 0) ?></span><br>
                <span class="tiny">Pregnant Women: <?= (int) ($location['pregnant_women'] ?? 0) ?></span>
              </td>
              <td>
                <details class="edit-box">
                  <summary>Edit</summary>
                  <form method="POST" action="/dashboard/admin/safe-locations/<?= (int) ($location['location_id'] ?? 0) ?>/update" class="edit-form">
                    <?= csrf_field() ?>
                    <input class="input" type="text" name="location_name" value="<?= e((string) ($location['location_name'] ?? '')) ?>" placeholder="Location name" required>
                    <input class="input" type="text" name="address_house_no" value="<?= e((string) ($location['address_house_no'] ?? '')) ?>" placeholder="House No">
                    <input class="input" type="text" name="address_street" value="<?= e((string) ($location['address_street'] ?? '')) ?>" placeholder="Street" required>
                    <input class="input" type="text" name="address_city" value="<?= e((string) ($location['address_city'] ?? '')) ?>" placeholder="City" required>
                    <input class="input" type="text" name="district" value="<?= e((string) ($location['district'] ?? '')) ?>" placeholder="District" required>
                    <input class="input" type="text" name="gn_division" value="<?= e((string) ($location['gn_division'] ?? '')) ?>" placeholder="GN Division" required>
                    <input class="input" type="number" name="latitude" step="0.00000001" min="-90" max="90" value="<?= e((string) ($location['latitude'] ?? '')) ?>" required>
                    <input class="input" type="number" name="longitude" step="0.00000001" min="-180" max="180" value="<?= e((string) ($location['longitude'] ?? '')) ?>" required>
                    <input class="input" type="number" name="max_capacity" min="1" value="<?= (int) ($location['max_capacity'] ?? 0) ?>" required>

                    <select class="input" name="assigned_gn_user_id" required>
                      <option value="">Select responsible GN officer</option>
                      <?php foreach ($gnOfficers as $officer): ?>
                        <?php $officerId = (int) ($officer['user_id'] ?? 0); ?>
                        <option value="<?= $officerId ?>" <?= (int) ($location['assigned_gn_user_id'] ?? 0) === $officerId ? 'selected' : '' ?>>
                          <?= e((string) (($officer['name'] ?? 'GN Officer') . ' - ' . ($officer['gn_division'] ?? '-'))) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>

                    <div class="row-actions">
                      <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                  </form>
                </details>

                <form method="POST" action="/dashboard/admin/safe-locations/<?= (int) ($location['location_id'] ?? 0) ?>/delete" onsubmit="return confirm('Remove this safe location?');" style="margin-top:0.4rem;">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-outline btn-sm">Delete</button>
                </form>
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
    const districtMap = <?= json_encode($districtMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const mapLocations = <?= json_encode($locations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const districtEl = document.getElementById('district');
    const gnDivisionEl = document.getElementById('gn_division');
    const districtOtherWrap = document.getElementById('district_other_wrap');
    const districtOtherInput = document.getElementById('district_other');
    const gnDivisionOtherWrap = document.getElementById('gn_division_other_wrap');
    const gnDivisionOtherInput = document.getElementById('gn_division_other');
    const selectedGnDivision = <?= json_encode($selectedGnDivision, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

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

    function availableSpace(location) {
      const max = toInt(location.max_capacity);
      const current = toInt(location.current_occupancy);
      return Math.max(0, max - current);
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

      map = L.map('dmcSafeMap').setView([7.8731, 80.7718], 8);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      markerLayer = L.layerGroup().addTo(map);
      renderMapMarkers();
    }

    function renderMapMarkers() {
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

    function setDistrictOtherState() {
      const isOther = districtEl.value === '__other__';
      districtOtherWrap.classList.toggle('hidden', !isOther);
      districtOtherInput.disabled = !isOther;
      if (!isOther) {
        districtOtherInput.value = '';
      }
    }

    function setGnOtherState() {
      const isOther = gnDivisionEl.value === '__other__';
      gnDivisionOtherWrap.classList.toggle('hidden', !isOther);
      gnDivisionOtherInput.disabled = !isOther;
      if (!isOther) {
        gnDivisionOtherInput.value = '';
      }
    }

    function renderGnDivisions() {
      const district = districtEl.value;
      const list = districtMap[district] || [];

      gnDivisionEl.innerHTML = '';

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = district ? 'Select GN division' : 'Select district first';
      gnDivisionEl.appendChild(placeholder);

      list.forEach((name) => {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        if (selectedGnDivision === name) {
          option.selected = true;
        }
        gnDivisionEl.appendChild(option);
      });

      const other = document.createElement('option');
      other.value = '__other__';
      other.textContent = 'Other';
      if (selectedGnDivision === '__other__') {
        other.selected = true;
      }
      gnDivisionEl.appendChild(other);

      setGnOtherState();
    }

    districtEl.addEventListener('change', () => {
      setDistrictOtherState();
      renderGnDivisions();
    });

    gnDivisionEl.addEventListener('change', setGnOtherState);

    setDistrictOtherState();
    renderGnDivisions();
    initMap();
  })();
</script>
