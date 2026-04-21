<?php
$districtMap = $district_map ?? [];
$districts = $districts ?? [];
$collectionPoints = $collection_points ?? [];
$editingPoint = $editing_point ?? null;
$oldInput = $_SESSION['_old_input'] ?? [];

$isEditing = is_array($editingPoint);

$fieldValue = static function (string $key, string $default = '') use ($oldInput, $editingPoint): string {
    if (array_key_exists($key, $oldInput)) {
        return (string) $oldInput[$key];
    }

    if (is_array($editingPoint) && array_key_exists($key, $editingPoint)) {
        return (string) $editingPoint[$key];
    }

    return $default;
};

$selectedDistrictRaw = trim($fieldValue('district', ''));
$selectedDistrictOtherRaw = trim($fieldValue('district_other', ''));

$selectedDistrictKnown = in_array($selectedDistrictRaw, $districts, true);
$selectedDistrictForSelect = $selectedDistrictKnown ? $selectedDistrictRaw : ($selectedDistrictRaw !== '' ? '__other__' : '');
$selectedDistrictOther = $selectedDistrictRaw === '__other__' ? $selectedDistrictOtherRaw : ($selectedDistrictKnown ? $selectedDistrictOtherRaw : $selectedDistrictRaw);

$selectedGnRaw = trim($fieldValue('gn_division', ''));
$selectedGnOtherRaw = trim($fieldValue('gn_division_other', ''));

$gnListForDistrict = is_array($districtMap[$selectedDistrictRaw] ?? null) ? $districtMap[$selectedDistrictRaw] : [];
$selectedGnKnown = in_array($selectedGnRaw, $gnListForDistrict, true);
$selectedGnForSelect = $selectedGnKnown ? $selectedGnRaw : ($selectedGnRaw !== '' ? '__other__' : '');
$selectedGnOther = $selectedGnRaw === '__other__' ? $selectedGnOtherRaw : ($selectedGnKnown ? $selectedGnOtherRaw : $selectedGnRaw);

$formAction = $isEditing
    ? '/dashboard/collection-points/' . (int) ($editingPoint['collection_point_id'] ?? 0) . '/update'
    : '/dashboard/collection-points/create';

$activeValue = $fieldValue('active', $isEditing ? (string) ((int) ($editingPoint['active'] ?? 1)) : '1');
$isActiveChecked = $activeValue === '1';
?>

<style>
  .cp-shell { display:grid; gap:1rem; }
  .cp-form-card { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1rem; }
  .cp-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:0.75rem; }
  .cp-table-shell { border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:auto; background:#fff; }
  .cp-table { width:100%; border-collapse:collapse; font-size:0.76rem; min-width:980px; }
  .cp-table thead th { text-align:left; padding:0.75rem 0.8rem; background:#fafafa; border-bottom:1px solid var(--color-border); }
  .cp-table tbody td { padding:0.75rem 0.8rem; border-bottom:1px solid var(--color-border); vertical-align:top; }
  .cp-table tbody tr:last-child td { border-bottom:none; }
  .cp-status { display:inline-flex; align-items:center; border-radius:999px; padding:0.22rem 0.6rem; font-size:0.64rem; font-weight:700; border:1px solid var(--color-border); }
  .cp-status-active { background:#edf8ee; border-color:#b8dfbc; color:#1f5f2a; }
  .cp-status-inactive { background:#fdeeee; border-color:#f0bbbb; color:#8a1616; }
  .tiny { color:#666; font-size:0.66rem; }
</style>

<section class="cp-shell">
  <div class="cp-form-card">
    <h1 style="margin:0 0 0.3rem;"><?= $isEditing ? 'Edit Collection Point' : 'Manage Collection Points' ?></h1>
    <p class="muted" style="margin:0 0 0.8rem;">Create, edit, and delete your NGO drop-off locations for public donations.</p>

    <form method="POST" action="<?= e($formAction) ?>" class="cp-form-grid">
      <?= csrf_field() ?>

      <div class="form-group" style="margin:0; grid-column:span 2;">
        <label for="name">Collection Point Name</label>
        <input class="input" type="text" id="name" name="name" value="<?= e($fieldValue('name')) ?>" placeholder="e.g. Community Center" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="address_house_no">House No (optional)</label>
        <input class="input" type="text" id="address_house_no" name="address_house_no" value="<?= e($fieldValue('address_house_no')) ?>">
      </div>

      <div class="form-group" style="margin:0;">
        <label for="address_street">Street</label>
        <input class="input" type="text" id="address_street" name="address_street" value="<?= e($fieldValue('address_street')) ?>" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="address_city">City</label>
        <input class="input" type="text" id="address_city" name="address_city" value="<?= e($fieldValue('address_city')) ?>" required>
      </div>

      <div class="form-group" style="margin:0;">
        <label for="district">District</label>
        <select class="input" id="district" name="district" required>
          <option value="">Select district</option>
          <?php foreach ($districts as $district): ?>
            <option value="<?= e((string) $district) ?>" <?= $selectedDistrictForSelect === $district ? 'selected' : '' ?>><?= e((string) $district) ?></option>
          <?php endforeach; ?>
          <option value="__other__" <?= $selectedDistrictForSelect === '__other__' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <div class="form-group <?= $selectedDistrictForSelect === '__other__' ? '' : 'hidden' ?>" id="district_other_wrap" style="margin:0;">
        <label for="district_other">Other district</label>
        <input class="input" type="text" id="district_other" name="district_other" value="<?= e($selectedDistrictOther) ?>" placeholder="Type district">
      </div>

      <div class="form-group" style="margin:0;">
        <label for="gn_division">GN Division</label>
        <select class="input" id="gn_division" name="gn_division" required>
          <option value="">Select district first</option>
        </select>
      </div>

      <div class="form-group <?= $selectedGnForSelect === '__other__' ? '' : 'hidden' ?>" id="gn_division_other_wrap" style="margin:0;">
        <label for="gn_division_other">Other GN division</label>
        <input class="input" type="text" id="gn_division_other" name="gn_division_other" value="<?= e($selectedGnOther) ?>" placeholder="Type GN division">
      </div>

      <div class="form-group" style="margin:0; grid-column:span 2;">
        <label for="location_landmark">Location / Landmark</label>
        <input class="input" type="text" id="location_landmark" name="location_landmark" value="<?= e($fieldValue('location_landmark')) ?>" placeholder="Nearby landmark">
      </div>

      <div class="form-group" style="margin:0;">
        <label for="contact_person">Contact Person</label>
        <input class="input" type="text" id="contact_person" name="contact_person" value="<?= e($fieldValue('contact_person')) ?>">
      </div>

      <div class="form-group" style="margin:0;">
        <label for="contact_number">Contact Number</label>
        <input class="input" type="text" id="contact_number" name="contact_number" value="<?= e($fieldValue('contact_number')) ?>">
      </div>

        <div class="form-group" style="margin:0; display:flex; align-items:center; gap:0.5rem;">
          <input type="hidden" name="active" value="0">
          <input type="checkbox" id="active" name="active" value="1" <?= $isActiveChecked ? 'checked' : '' ?>>
          <label for="active" style="margin:0;">Active</label>
        </div>

      <div style="display:flex; gap:0.5rem; grid-column:1 / -1;">
        <button type="submit" class="btn btn-primary"><?= $isEditing ? 'Update Collection Point' : 'Add Collection Point' ?></button>
        <?php if ($isEditing): ?>
          <a href="/dashboard/collection-points" class="btn">Cancel Edit</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="cp-table-shell">
    <table class="cp-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Address</th>
          <th>Landmark</th>
          <th>Contact</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($collectionPoints)): ?>
          <tr><td colspan="6" class="tiny">No collection points created yet.</td></tr>
        <?php else: ?>
          <?php foreach ($collectionPoints as $point): ?>
            <?php $pointId = (int) ($point['collection_point_id'] ?? 0); ?>
            <?php $isActive = (int) ($point['active'] ?? 1) === 1; ?>
            <tr>
              <td>
                <strong><?= e((string) ($point['name'] ?? '-')) ?></strong><br>
                <span class="tiny">#<?= $pointId ?></span>
              </td>
              <td><?= e((string) ($point['full_address'] ?? '-')) ?></td>
              <td><?= e((string) ($point['location_landmark'] ?? '-')) ?></td>
              <td>
                <?= e((string) ($point['contact_person'] ?? '-')) ?><br>
                <span class="tiny"><?= e((string) ($point['contact_number'] ?? '-')) ?></span>
              </td>
              <td>
                <span class="cp-status <?= $isActive ? 'cp-status-active' : 'cp-status-inactive' ?>">
                  <?= $isActive ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td style="text-align:right; white-space:nowrap;">
                <a href="/dashboard/collection-points?edit=<?= $pointId ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="POST" action="/dashboard/collection-points/<?= $pointId ?>/delete" class="inline-form" style="margin-left:0.35rem;" onsubmit="return confirm('Delete this collection point?');">
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

<script>
  (function () {
    const districtMap = <?= json_encode($districtMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const districtEl = document.getElementById('district');
    const gnDivisionEl = document.getElementById('gn_division');
    const districtOtherWrap = document.getElementById('district_other_wrap');
    const districtOtherInput = document.getElementById('district_other');
    const gnDivisionOtherWrap = document.getElementById('gn_division_other_wrap');
    const gnDivisionOtherInput = document.getElementById('gn_division_other');

    const selectedGnDivision = <?= json_encode($selectedGnRaw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const selectedGnDivisionOther = <?= json_encode($selectedGnOther, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let useInitialSelection = true;

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
      const selectedValue = useInitialSelection ? selectedGnDivision : '';

      gnDivisionEl.innerHTML = '';

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = district && district !== '__other__' ? 'Select GN division' : 'Select district first';
      gnDivisionEl.appendChild(placeholder);

      let hasSelectedKnown = false;

      list.forEach((name) => {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        if (selectedValue === name) {
          option.selected = true;
          hasSelectedKnown = true;
        }
        gnDivisionEl.appendChild(option);
      });

      const other = document.createElement('option');
      other.value = '__other__';
      other.textContent = 'Other';

      const shouldUseOther = !hasSelectedKnown && selectedValue !== '';
      if (shouldUseOther || selectedValue === '__other__') {
        other.selected = true;
      }

      gnDivisionEl.appendChild(other);

      if (shouldUseOther) {
        gnDivisionOtherInput.value = selectedGnDivisionOther !== '' ? selectedGnDivisionOther : selectedValue;
      }

      setGnOtherState();
      useInitialSelection = false;
    }

    districtEl.addEventListener('change', () => {
      setDistrictOtherState();
      renderGnDivisions();
    });

    gnDivisionEl.addEventListener('change', setGnOtherState);

    setDistrictOtherState();
    renderGnDivisions();
  })();
</script>
