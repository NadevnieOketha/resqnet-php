<?php
$profile = $profile ?? [];
$oldInput = $_SESSION['_old_input'] ?? [];

$selectedPreferences = is_array($oldInput['preferences'] ?? null)
    ? array_map('strval', $oldInput['preferences'])
    : [];

$selectedSkills = is_array($oldInput['skills'] ?? null)
    ? array_map('strval', $oldInput['skills'])
    : [];

$selectedDistrict = old('district', (string) ($profile['district'] ?? ''));
$selectedGnDivision = old('gn_division', (string) ($profile['gn_division'] ?? ''));
$selectedGnOther = old('gn_division_other', '');
if (
    $selectedGnOther === ''
    && $selectedGnDivision !== ''
    && !in_array($selectedGnDivision, $gn_divisions[$selectedDistrict] ?? [], true)
) {
    $selectedGnOther = $selectedGnDivision;
}

$currentUser = auth_user() ?? [];
?>

<div class="dashboard-header">
    <h1>Become a Volunteer</h1>
    <p>Your existing general-user details are pre-filled. Add volunteer details and submit to switch your account role.</p>
</div>

<div class="card">
    <div class="card-header">
        <h2>Volunteer Conversion Form</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="/dashboard/become-volunteer" id="become-volunteer-form">
            <?= csrf_field() ?>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="account_username">Username</label>
                    <input type="text" id="account_username" value="<?= e((string) ($currentUser['username'] ?? '')) ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="account_email">Email</label>
                    <input type="email" id="account_email" value="<?= e((string) ($currentUser['email'] ?? '')) ?>" readonly>
                </div>
            </div>

            <h3 class="section-title">Personal Details</h3>
            <div class="form-grid-3">
                <div class="form-group">
                    <label for="volunteer_name">Name</label>
                    <input type="text" id="volunteer_name" name="name" value="<?= old('name', (string) ($profile['name'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="volunteer_age">Age (optional)</label>
                    <input type="number" id="volunteer_age" name="age" value="<?= old('age', '') ?>" min="16" max="100">
                </div>
                <div class="form-group">
                    <label for="volunteer_gender">Gender (optional)</label>
                    <select id="volunteer_gender" name="gender">
                        <option value="">Select</option>
                        <?php foreach (($genders ?? []) as $gender): ?>
                            <option value="<?= e($gender) ?>" <?= old('gender', '') === $gender ? 'selected' : '' ?>><?= ucfirst(e($gender)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="volunteer_contact">Contact Number</label>
                <input type="text" id="volunteer_contact" name="contact_number" value="<?= old('contact_number', (string) ($profile['contact_number'] ?? '')) ?>" required>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label for="volunteer_house_no">House No</label>
                    <input type="text" id="volunteer_house_no" name="house_no" value="<?= old('house_no', (string) ($profile['house_no'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="volunteer_street">Street</label>
                    <input type="text" id="volunteer_street" name="street" value="<?= old('street', (string) ($profile['street'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="volunteer_city">City</label>
                    <input type="text" id="volunteer_city" name="city" value="<?= old('city', (string) ($profile['city'] ?? '')) ?>" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="volunteer_district">District</label>
                    <select id="volunteer_district" class="district-select" name="district" required>
                        <option value="">Select District</option>
                        <?php foreach (($districts ?? []) as $district): ?>
                            <option value="<?= e($district) ?>" <?= $selectedDistrict === $district ? 'selected' : '' ?>><?= e($district) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="volunteer_gn">Grama Niladhari Division</label>
                    <select id="volunteer_gn" class="gn-select" name="gn_division" data-selected="<?= e($selectedGnDivision) ?>"></select>
                </div>
            </div>

            <div class="form-group gn-other-wrapper" style="display:none;">
                <label for="volunteer_gn_other">Other GN Division</label>
                <input type="text" id="volunteer_gn_other" class="gn-other-input" name="gn_division_other" value="<?= e($selectedGnOther) ?>">
            </div>

            <h3 class="section-title">Volunteer Settings</h3>
            <div class="form-group">
                <label>Volunteer Preferences (optional)</label>
                <div class="checkbox-grid">
                    <?php foreach (($volunteer_preferences ?? []) as $preference): ?>
                        <label class="checkbox-item">
                            <input type="checkbox" name="preferences[]" value="<?= e($preference) ?>" <?= in_array($preference, $selectedPreferences, true) ? 'checked' : '' ?>>
                            <span><?= e($preference) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Specialized Skills (required)</label>
                <div class="checkbox-grid">
                    <?php foreach (($volunteer_skills ?? []) as $skill): ?>
                        <label class="checkbox-item">
                            <input type="checkbox" name="skills[]" value="<?= e($skill) ?>" <?= in_array($skill, $selectedSkills, true) ? 'checked' : '' ?>>
                            <span><?= e($skill) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit and Become Volunteer</button>
                <a href="/dashboard" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const divisionMap = <?= json_encode($gn_divisions ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const districtSelect = document.querySelector('.district-select');
    const gnSelect = document.querySelector('.gn-select');
    const gnOtherWrap = document.querySelector('.gn-other-wrapper');
    const gnOtherInput = document.querySelector('.gn-other-input');

    if (!districtSelect || !gnSelect || !gnOtherWrap || !gnOtherInput) {
        return;
    }

    const setOtherVisibility = () => {
        const showOther = gnSelect.value === '__other__';
        gnOtherWrap.style.display = showOther ? 'block' : 'none';
        gnOtherInput.disabled = !showOther;
    };

    const populateGnDivisions = () => {
        const district = districtSelect.value;
        const selected = gnSelect.dataset.selected || '';
        const options = divisionMap[district] || [];

        gnSelect.innerHTML = '<option value="">Select GN Division</option>';

        options.forEach((name) => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            if (selected === name) {
                option.selected = true;
            }
            gnSelect.appendChild(option);
        });

        const otherOption = document.createElement('option');
        otherOption.value = '__other__';
        otherOption.textContent = 'Other (type manually)';
        gnSelect.appendChild(otherOption);

        if (selected && !options.includes(selected)) {
            gnSelect.value = '__other__';
            gnOtherInput.value = selected;
        }

        setOtherVisibility();
    };

    districtSelect.addEventListener('change', () => {
        gnSelect.dataset.selected = '';
        populateGnDivisions();
    });

    gnSelect.addEventListener('change', setOtherVisibility);

    populateGnDivisions();
})();
</script>
