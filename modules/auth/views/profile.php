<?php
$profile = $profile ?? [];
$role = (string) ($role ?? user_role() ?? '');
$oldInput = $_SESSION['_old_input'] ?? [];

$selectedPreferences = is_array($oldInput['preferences'] ?? null)
    ? array_map('strval', $oldInput['preferences'])
    : array_map('strval', $profile['preferences'] ?? []);

$selectedSkills = is_array($oldInput['skills'] ?? null)
    ? array_map('strval', $oldInput['skills'])
    : array_map('strval', $profile['skills'] ?? []);

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
?>

<div class="dashboard-header">
    <h1>Profile</h1>
    <p>Update your account details and role profile.</p>
</div>

<div class="card">
    <div class="card-header">
        <h2>Account Details</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="/profile" id="profile-form">
            <?= csrf_field() ?>

            <h3 class="section-title">Common Credentials</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= old('username', (string) ($profile['username'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= old('email', (string) ($profile['email'] ?? '')) ?>" <?= $role === 'ngo' ? 'readonly' : '' ?> required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="password">New Password (optional)</label>
                    <input type="password" id="password" name="password" minlength="6">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="6">
                </div>
            </div>

            <?php if ($role === 'general'): ?>
                <h3 class="section-title">General Public Profile</h3>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="general_name">Name</label>
                        <input type="text" id="general_name" name="name" value="<?= old('name', (string) ($profile['name'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="general_contact">Contact Number</label>
                        <input type="text" id="general_contact" name="contact_number" value="<?= old('contact_number', (string) ($profile['contact_number'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="general_house_no">House No</label>
                        <input type="text" id="general_house_no" name="house_no" value="<?= old('house_no', (string) ($profile['house_no'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="general_street">Street</label>
                        <input type="text" id="general_street" name="street" value="<?= old('street', (string) ($profile['street'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="general_city">City</label>
                        <input type="text" id="general_city" name="city" value="<?= old('city', (string) ($profile['city'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="general_district">District</label>
                        <select id="general_district" class="district-select" name="district" required>
                            <option value="">Select District</option>
                            <?php foreach (($districts ?? []) as $district): ?>
                                <option value="<?= e($district) ?>" <?= $selectedDistrict === $district ? 'selected' : '' ?>><?= e($district) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="general_gn">Grama Niladhari Division</label>
                        <select id="general_gn" class="gn-select" name="gn_division" data-selected="<?= e($selectedGnDivision) ?>"></select>
                    </div>
                </div>

                <div class="form-group gn-other-wrapper" style="display:none;">
                    <label for="general_gn_other">Other GN Division</label>
                    <input type="text" id="general_gn_other" class="gn-other-input" name="gn_division_other" value="<?= e($selectedGnOther) ?>">
                </div>
            <?php endif; ?>

            <?php if ($role === 'volunteer'): ?>
                <h3 class="section-title">Volunteer Profile</h3>
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="volunteer_name">Name</label>
                        <input type="text" id="volunteer_name" name="name" value="<?= old('name', (string) ($profile['name'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="volunteer_age">Age</label>
                        <input type="number" id="volunteer_age" name="age" value="<?= old('age', (string) ($profile['age'] ?? '')) ?>" min="16" max="100" required>
                    </div>
                    <div class="form-group">
                        <label for="volunteer_gender">Gender</label>
                        <select id="volunteer_gender" name="gender" required>
                            <option value="">Select</option>
                            <?php foreach (($genders ?? []) as $gender): ?>
                                <option value="<?= e($gender) ?>" <?= old('gender', (string) ($profile['gender'] ?? '')) === $gender ? 'selected' : '' ?>><?= ucfirst(e($gender)) ?></option>
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

                <div class="form-group">
                    <label>Volunteer Preferences</label>
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
                    <label>Specialized Skills</label>
                    <div class="checkbox-grid">
                        <?php foreach (($volunteer_skills ?? []) as $skill): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="skills[]" value="<?= e($skill) ?>" <?= in_array($skill, $selectedSkills, true) ? 'checked' : '' ?>>
                                <span><?= e($skill) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'ngo'): ?>
                <h3 class="section-title">NGO Profile</h3>
                <div class="form-group">
                    <label for="ngo_org_name">Organization Name</label>
                    <input type="text" id="ngo_org_name" name="organization_name" value="<?= old('organization_name', (string) ($profile['organization_name'] ?? '')) ?>" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="ngo_reg_no">Registration Number</label>
                        <input type="text" id="ngo_reg_no" name="registration_number" value="<?= old('registration_number', (string) ($profile['registration_number'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ngo_years">Years of Operation</label>
                        <input type="number" id="ngo_years" name="years_of_operation" value="<?= old('years_of_operation', (string) ($profile['years_of_operation'] ?? '')) ?>" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="ngo_address">Organization Address</label>
                    <textarea id="ngo_address" name="address" rows="3" required><?= old('address', (string) ($profile['address'] ?? '')) ?></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="ngo_contact_name">Contact Person Name</label>
                        <input type="text" id="ngo_contact_name" name="contact_person_name" value="<?= old('contact_person_name', (string) ($profile['contact_person_name'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ngo_contact_tel">Contact Person Telephone</label>
                        <input type="text" id="ngo_contact_tel" name="contact_person_telephone" value="<?= old('contact_person_telephone', (string) ($profile['contact_person_telephone'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ngo_contact_email">Contact Person Email (Login Email)</label>
                    <input type="email" id="ngo_contact_email" name="contact_person_email" value="<?= old('contact_person_email', (string) ($profile['contact_person_email'] ?? $profile['email'] ?? '')) ?>" required>
                </div>
            <?php endif; ?>

            <?php if ($role === 'grama_niladhari'): ?>
                <h3 class="section-title">Grama Niladhari Profile</h3>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="gn_name">Name</label>
                        <input type="text" id="gn_name" name="name" value="<?= old('name', (string) ($profile['name'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="gn_contact">Contact Number</label>
                        <input type="text" id="gn_contact" name="contact_number" value="<?= old('contact_number', (string) ($profile['contact_number'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gn_address">Address</label>
                    <textarea id="gn_address" name="address" rows="3" required><?= old('address', (string) ($profile['address'] ?? '')) ?></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="gn_division">GN Division</label>
                        <input type="text" id="gn_division" name="gn_division" value="<?= old('gn_division', (string) ($profile['gn_division'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="service_number">Service Number</label>
                        <input type="text" id="service_number" name="service_number" value="<?= old('service_number', (string) ($profile['service_number'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gn_division_number">GN Division Number</label>
                    <input type="text" id="gn_division_number" name="gn_division_number" value="<?= old('gn_division_number', (string) ($profile['gn_division_number'] ?? '')) ?>" required>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/dashboard" class="btn btn-outline">Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>

<?php if (in_array($role, ['general', 'volunteer', 'ngo'], true)): ?>
<div class="card" style="border-color:#d9534f;">
    <div class="card-body">
        <p style="margin-top:0; color:#555;">
            Deleting your account is permanent. Your profile access will be removed immediately.
        </p>
        <form method="POST" action="/profile/delete" onsubmit="return confirm('Are you sure you want to permanently delete your account? This action cannot be undone.');">
            <?= csrf_field() ?>
            <input type="hidden" name="confirm_delete" value="1">
            <button type="submit" class="btn btn-outline" style="border-color:#d9534f; color:#b52b27;">Delete Account</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (in_array($role, ['general', 'volunteer'], true)): ?>
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
<?php endif; ?>

<?php if ($role === 'ngo'): ?>
<script>
(() => {
    const contactEmail = document.getElementById('ngo_contact_email');
    const loginEmail = document.getElementById('email');

    if (!contactEmail || !loginEmail) {
        return;
    }

    const sync = () => {
        loginEmail.value = contactEmail.value;
    };

    contactEmail.addEventListener('input', sync);
    sync();
})();
</script>
<?php endif; ?>

<!--<div class="form-group">
    <label for="donation_priority">Donation Priority</label>
    <select id="donation_priority" name="donation_priority">
        <option value="Low" <?= (($old['donation_priority'] ?? '') === 'Low') ? 'selected' : '' ?>>Low</option>
        <option value="Medium" <?= (($old['donation_priority'] ?? 'Medium') === 'Medium') ? 'selected' : '' ?>>Medium</option>
        <option value="High" <?= (($old['donation_priority'] ?? '') === 'High') ? 'selected' : '' ?>>High</option>
    </select>
</div>

