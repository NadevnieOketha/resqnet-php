<?php
$oldInput = $_SESSION['_old_input'] ?? [];
$districtMap = $district_map ?? [];
$districts = $districts ?? [];
$prefilledReporterName = (string) ($prefilled_reporter_name ?? '');
$prefilledContactNumber = (string) ($prefilled_contact_number ?? '');
$isGnAreaLocked = (bool) ($is_gn_area_locked ?? false);
$lockedDistrict = trim((string) ($locked_district ?? ''));
$lockedGnDivision = trim((string) ($locked_gn_division ?? ''));

$oldValue = static function (string $key, string $default = '') use ($oldInput): string {
    return e((string) ($oldInput[$key] ?? $default));
};

$selectedType = (string) ($oldInput['disaster_type'] ?? 'Flood');
$selectedDistrict = (string) ($oldInput['district'] ?? ($isGnAreaLocked ? $lockedDistrict : ''));
$selectedGn = (string) ($oldInput['gn_division'] ?? ($isGnAreaLocked ? $lockedGnDivision : ''));
$districtOther = (string) ($oldInput['district_other'] ?? '');
$gnOther = (string) ($oldInput['gn_division_other'] ?? '');
$typeOther = (string) ($oldInput['other_disaster_type'] ?? '');
?>

<style>
  .two-col-grid { display:grid; gap:2rem 2.25rem; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); max-width:1100px; }
  .disaster-types { display:flex; flex-direction:column; gap:0.75rem; }
  .disaster-option { border:1px solid var(--color-border); border-radius:var(--radius-sm); padding:0.9rem 1rem; display:flex; align-items:center; gap:0.75rem; font-size:0.85rem; cursor:pointer; background:#fff; }
  .disaster-option:hover { background:var(--color-hover-surface); }
  .disaster-option input { accent-color: var(--color-accent); }
  #reportDisasterForm { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:2rem 2rem 2.5rem; box-shadow: var(--shadow-sm); max-width:1100px; }
  .inline-actions { display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem; gap:1rem; }
  .ack { display:flex; align-items:center; gap:0.6rem; font-size:0.75rem; }
  .ack input { width:16px; height:16px; }
  .file-input { border:1px dashed var(--color-border); padding:0.9rem 1rem; border-radius:var(--radius-sm); background:#fff; font-size:0.75rem; cursor:pointer; }
  .file-input:hover { background:var(--color-hover-surface); }
  .helper-muted { color: var(--color-text-subtle); font-size: 0.72rem; margin-top: 0.4rem; }
  .hidden { display:none; }
</style>

<h1>Report a Disaster</h1>
<form id="reportDisasterForm" method="POST" action="/report-disaster" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <div class="two-col-grid">
    <div class="form-field">
      <label for="reporter_name">Reporter's Name</label>
      <input class="input" type="text" id="reporter_name" name="reporter_name" placeholder="Enter your name" value="<?= $oldValue('reporter_name', $prefilledReporterName) ?>" required />
    </div>
    <div class="form-field">
      <label for="contact_number">Contact Number</label>
      <input class="input" type="tel" id="contact_number" name="contact_number" placeholder="Enter your contact number" value="<?= $oldValue('contact_number', $prefilledContactNumber) ?>" required />
    </div>

    <div class="form-field" style="grid-row: span 8;">
      <label>Type of Disaster</label>
      <div class="disaster-types" role="radiogroup" aria-label="Disaster Type">
        <?php foreach (['Flood', 'Landslide', 'Fire', 'Earthquake', 'Tsunami', 'Other'] as $type): ?>
          <label class="disaster-option">
            <input type="radio" name="disaster_type" value="<?= e($type) ?>" <?= $selectedType === $type ? 'checked' : '' ?> />
            <span><?= e($type) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <div class="form-field">
      <label for="other_disaster_type">If 'Other', specify</label>
      <input class="input" type="text" id="other_disaster_type" name="other_disaster_type" placeholder="Specify the type of disaster" value="<?= e($typeOther) ?>" <?= $selectedType === 'Other' ? '' : 'disabled' ?> />
    </div>
    </div>

    <div class="form-field">
      <label for="disaster_datetime">Date and Time of Disaster</label>
      <input class="input" type="datetime-local" id="disaster_datetime" name="disaster_datetime" value="<?= $oldValue('disaster_datetime') ?>" required />
    </div>

    <div class="form-field">
      <label for="location">Location Details (optional)</label>
      <input class="input" type="text" id="location" name="location" placeholder="Any specific location details" value="<?= $oldValue('location') ?>" />
    </div>

    <div class="form-field">
      <label for="district">District</label>
      <select class="input" id="district" name="district" required <?= $isGnAreaLocked ? 'disabled' : '' ?>>
        <option value="">Select district</option>
        <?php foreach ($districts as $district): ?>
          <option value="<?= e($district) ?>" <?= $selectedDistrict === $district ? 'selected' : '' ?>><?= e($district) ?></option>
        <?php endforeach; ?>
        <?php if (!$isGnAreaLocked): ?>
          <option value="__other__" <?= $selectedDistrict === '__other__' ? 'selected' : '' ?>>Other</option>
        <?php endif; ?>
      </select>
      <?php if ($isGnAreaLocked): ?>
        <input type="hidden" name="district" value="<?= e($lockedDistrict) ?>">
        <p class="helper-muted">District is locked to your GN area.</p>
      <?php endif; ?>
    </div>

    <div class="form-field <?= ($selectedDistrict === '__other__' && !$isGnAreaLocked) ? '' : 'hidden' ?>" id="district_other_wrap">
      <label for="district_other">If district is not listed, type it</label>
      <input class="input" type="text" id="district_other" name="district_other" value="<?= e($districtOther) ?>" placeholder="Enter district" />
    </div>

    <div class="form-field">
      <label for="gn_division">Grama Niladhari Division</label>
      <select class="input" id="gn_division" name="gn_division" required <?= $isGnAreaLocked ? 'disabled' : '' ?>>
        <option value="">Select district first</option>
      </select>
      <?php if ($isGnAreaLocked): ?>
        <input type="hidden" name="gn_division" value="<?= e($lockedGnDivision) ?>">
        <p class="helper-muted">GN division is locked to your profile.</p>
      <?php endif; ?>
    </div>

    <div class="form-field <?= ($selectedGn === '__other__' && !$isGnAreaLocked) ? '' : 'hidden' ?>" id="gn_other_wrap">
      <label for="gn_division_other">If GN division is not listed, type it</label>
      <input class="input" type="text" id="gn_division_other" name="gn_division_other" value="<?= e($gnOther) ?>" placeholder="Enter GN division" />
    </div>

    <div class="form-field">
      <label for="proof_image">Upload Image of Proof (optional)</label>
      <input class="input file-input" type="file" id="proof_image" name="proof_image" accept="image/*" />
      <p class="helper-muted">Maximum size: 10 MB</p>
    </div>

    <div class="form-field" style="grid-column: 1 / -1;">
      <label for="description">Description (optional)</label>
      <textarea class="input" id="description" name="description" rows="4" placeholder="Any additional details"><?= $oldValue('description') ?></textarea>
    </div>
  </div>

  <div class="ack" style="margin-top:1.25rem;">
    <input type="checkbox" id="confirmation" name="confirmation" value="1" <?= old('confirmation') === '1' ? 'checked' : '' ?> required />
    <label for="confirmation" style="margin:0;font-weight:400;">I confirm that the information provided is accurate to the best of my knowledge.</label>
  </div>

  <div class="inline-actions">
    <button type="reset" class="btn btn-outline" id="cancelBtn">Cancel</button>
    <button type="submit" class="btn btn-primary">Submit Report</button>
  </div>
</form>

<script>
  (function () {
    const districtMap = <?= json_encode($districtMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const districtEl = document.getElementById('district');
    const districtOtherWrap = document.getElementById('district_other_wrap');
    const districtOtherInput = document.getElementById('district_other');
    const gnEl = document.getElementById('gn_division');
    const gnOtherWrap = document.getElementById('gn_other_wrap');
    const gnOtherInput = document.getElementById('gn_division_other');
    const otherTypeInput = document.getElementById('other_disaster_type');
    const isGnAreaLocked = <?= $isGnAreaLocked ? 'true' : 'false' ?>;
    const lockedDistrict = <?= json_encode($lockedDistrict, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const lockedGnDivision = <?= json_encode($lockedGnDivision, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const selectedGn = <?= json_encode($selectedGn, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function setDisasterTypeState() {
      const selected = document.querySelector('input[name="disaster_type"]:checked');
      const isOther = selected && selected.value === 'Other';
      otherTypeInput.disabled = !isOther;
      if (!isOther) {
        otherTypeInput.value = '';
      }
    }

    function renderGnOptions() {
      const district = districtEl.value;
      const options = districtMap[district] || [];

      gnEl.innerHTML = '';

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = district ? 'Select GN division' : 'Select district first';
      gnEl.appendChild(placeholder);

      options.forEach((value) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        if (selectedGn === value) {
          option.selected = true;
        }
        gnEl.appendChild(option);
      });

      const otherOption = document.createElement('option');
      if (!isGnAreaLocked) {
        otherOption.value = '__other__';
        otherOption.textContent = 'Other';
        if (selectedGn === '__other__') {
          otherOption.selected = true;
        }
        gnEl.appendChild(otherOption);
      }

      setGnOtherState();
    }

    function lockGnAreaSelection() {
      if (!lockedDistrict || !lockedGnDivision) {
        return;
      }

      districtEl.innerHTML = '';
      const districtOption = document.createElement('option');
      districtOption.value = lockedDistrict;
      districtOption.textContent = lockedDistrict;
      districtOption.selected = true;
      districtEl.appendChild(districtOption);
      districtEl.disabled = true;

      gnEl.innerHTML = '';
      const gnOption = document.createElement('option');
      gnOption.value = lockedGnDivision;
      gnOption.textContent = lockedGnDivision;
      gnOption.selected = true;
      gnEl.appendChild(gnOption);
      gnEl.disabled = true;

      districtOtherWrap.classList.add('hidden');
      districtOtherInput.disabled = true;
      districtOtherInput.value = '';

      gnOtherWrap.classList.add('hidden');
      gnOtherInput.disabled = true;
      gnOtherInput.value = '';
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
      const isOther = gnEl.value === '__other__';
      gnOtherWrap.classList.toggle('hidden', !isOther);
      gnOtherInput.disabled = !isOther;
      if (!isOther) {
        gnOtherInput.value = '';
      }
    }

    if (isGnAreaLocked) {
      lockGnAreaSelection();
    } else {
      districtEl.addEventListener('change', function () {
        setDistrictOtherState();
        renderGnOptions();
      });

      gnEl.addEventListener('change', setGnOtherState);
      setDistrictOtherState();
      renderGnOptions();
    }

    document.querySelectorAll('input[name="disaster_type"]').forEach((input) => {
      input.addEventListener('change', setDisasterTypeState);
    });

    setDisasterTypeState();
  })();
</script>
