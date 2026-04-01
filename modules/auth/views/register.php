<?php
$selectedRole = old('role', 'general');
$oldInput = $_SESSION['_old_input'] ?? [];
$oldPreferences = is_array($oldInput['preferences'] ?? null) ? $oldInput['preferences'] : [];
$oldSkills = is_array($oldInput['skills'] ?? null) ? $oldInput['skills'] : [];
?>

<div class="auth-container">
    <div class="auth-card auth-card-wide">
        <h2>Create Account</h2>
        <p class="auth-subtitle">Register as General Public, Volunteer, or NGO</p>

        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register" id="register-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="role">Register As</label>
                <select id="role" name="role" required>
                    <option value="general" <?= $selectedRole === 'general' ? 'selected' : '' ?>>General Public</option>
                    <option value="volunteer" <?= $selectedRole === 'volunteer' ? 'selected' : '' ?>>Volunteer</option>
                    <option value="ngo" <?= $selectedRole === 'ngo' ? 'selected' : '' ?>>NGO</option>
                </select>
            </div>

            <div class="role-section" data-role="general">
                <h3 class="section-title">General User Details</h3>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="general_name">Name</label>
                        <input type="text" id="general_name" name="name" value="<?= old('name') ?>">
                    </div>
                    <div class="form-group">
                        <label for="general_contact">Contact Number</label>
                        <input type="text" id="general_contact" name="contact_number" value="<?= old('contact_number') ?>">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="general_house_no">House No</label>
                        <input type="text" id="general_house_no" name="house_no" value="<?= old('house_no') ?>">
                    </div>
                    <div class="form-group">
                        <label for="general_street">Street</label>
                        <input type="text" id="general_street" name="street" value="<?= old('street') ?>">
                    </div>
                    <div class="form-group">
                        <label for="general_city">City</label>
                        <input type="text" id="general_city" name="city" value="<?= old('city') ?>">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="general_district">District</label>
                        <select id="general_district" class="district-select" name="district">
                            <option value="">Select District</option>
                            <?php foreach (($districts ?? []) as $district): ?>
                                <option value="<?= e($district) ?>" <?= old('district') === $district ? 'selected' : '' ?>><?= e($district) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="general_gn">Grama Niladhari Division</label>
                        <select id="general_gn" class="gn-select" name="gn_division" data-selected="<?= e(old('gn_division')) ?>"></select>
                    </div>
                </div>

                <div class="form-group gn-other-wrapper" style="display:none;">
                    <label for="general_gn_other">Other GN Division</label>
                    <input type="text" id="general_gn_other" class="gn-other-input" name="gn_division_other" value="<?= old('gn_division_other') ?>">
                </div>

                <div class="form-group">
                    <label for="general_email">Email</label>
                    <input type="email" id="general_email" name="email" value="<?= old('email') ?>">
                </div>
            </div>

            <div class="role-section" data-role="volunteer">
                <h3 class="section-title">Volunteer Details</h3>
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="volunteer_name">Name</label>
                        <input type="text" id="volunteer_name" name="name" value="<?= old('name') ?>">
                    </div>
                    <div class="form-group">
                        <label for="volunteer_age">Age</label>
                        <input type="number" id="volunteer_age" name="age" value="<?= old('age') ?>" min="16" max="100">
                    </div>
                    <div class="form-group">
                        <label for="volunteer_gender">Gender</label>
                        <select id="volunteer_gender" name="gender">
                            <option value="">Select</option>
                            <?php foreach (($genders ?? []) as $gender): ?>
                                <option value="<?= e($gender) ?>" <?= old('gender') === $gender ? 'selected' : '' ?>><?= ucfirst(e($gender)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="volunteer_contact">Contact Number</label>
                    <input type="text" id="volunteer_contact" name="contact_number" value="<?= old('contact_number') ?>">
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="volunteer_house_no">House No</label>
                        <input type="text" id="volunteer_house_no" name="house_no" value="<?= old('house_no') ?>">
                    </div>
                    <div class="form-group">
                        <label for="volunteer_street">Street</label>
                        <input type="text" id="volunteer_street" name="street" value="<?= old('street') ?>">
                    </div>
                    <div class="form-group">
                        <label for="volunteer_city">City</label>
                        <input type="text" id="volunteer_city" name="city" value="<?= old('city') ?>">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="volunteer_district">District</label>
                        <select id="volunteer_district" class="district-select" name="district">
                            <option value="">Select District</option>
                            <?php foreach (($districts ?? []) as $district): ?>
                                <option value="<?= e($district) ?>" <?= old('district') === $district ? 'selected' : '' ?>><?= e($district) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="volunteer_gn">Grama Niladhari Division</label>
                        <select id="volunteer_gn" class="gn-select" name="gn_division" data-selected="<?= e(old('gn_division')) ?>"></select>
                    </div>
                </div>

                <div class="form-group gn-other-wrapper" style="display:none;">
                    <label for="volunteer_gn_other">Other GN Division</label>
                    <input type="text" id="volunteer_gn_other" class="gn-other-input" name="gn_division_other" value="<?= old('gn_division_other') ?>">
                </div>

                <div class="form-group">
                    <label for="volunteer_email">Email</label>
                    <input type="email" id="volunteer_email" name="email" value="<?= old('email') ?>">
                </div>

                <div class="form-group">
                    <label>Volunteer Preferences</label>
                    <div class="checkbox-grid">
                        <?php foreach (($volunteer_preferences ?? []) as $preference): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="preferences[]" value="<?= e($preference) ?>" <?= in_array($preference, $oldPreferences, true) ? 'checked' : '' ?>>
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
                                <input type="checkbox" name="skills[]" value="<?= e($skill) ?>" <?= in_array($skill, $oldSkills, true) ? 'checked' : '' ?>>
                                <span><?= e($skill) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="role-section" data-role="ngo">
                <h3 class="section-title">NGO Details</h3>
                <div class="form-group">
                    <label for="ngo_org_name">Organization Name</label>
                    <input type="text" id="ngo_org_name" name="organization_name" value="<?= old('organization_name') ?>">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="ngo_reg_no">Registration Number</label>
                        <input type="text" id="ngo_reg_no" name="registration_number" value="<?= old('registration_number') ?>">
                    </div>
                    <div class="form-group">
                        <label for="ngo_years">Years of Operation</label>
                        <input type="number" id="ngo_years" name="years_of_operation" value="<?= old('years_of_operation') ?>" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="ngo_address">Organization Address</label>
                    <textarea id="ngo_address" name="address" rows="3"><?= old('address') ?></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="ngo_contact_name">Contact Person Name</label>
                        <input type="text" id="ngo_contact_name" name="contact_person_name" value="<?= old('contact_person_name') ?>">
                    </div>
                    <div class="form-group">
                        <label for="ngo_contact_tel">Contact Person Telephone</label>
                        <input type="text" id="ngo_contact_tel" name="contact_person_telephone" value="<?= old('contact_person_telephone') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="ngo_contact_email">Contact Person Email (Login Email)</label>
                    <input type="email" id="ngo_contact_email" name="contact_person_email" value="<?= old('contact_person_email') ?>">
                </div>
            </div>

            <h3 class="section-title">Account Credentials</h3>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= old('username') ?>" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p class="auth-link">
            Already have an account? <a href="/login">Sign in</a>
        </p>
    </div>
</div>

<script>
(() => {
    const roleSelect = document.getElementById('role');
    const sections = Array.from(document.querySelectorAll('.role-section'));
    const divisionMap = <?= json_encode($gn_divisions ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const setOtherFieldVisibility = (section) => {
        const gnSelect = section.querySelector('.gn-select');
        const otherWrap = section.querySelector('.gn-other-wrapper');
        const otherInput = section.querySelector('.gn-other-input');

        if (!gnSelect || !otherWrap || !otherInput) return;

        const show = gnSelect.value === '__other__';
        otherWrap.style.display = show ? 'block' : 'none';
        otherInput.disabled = !show;
    };

    const populateGnDivisions = (section) => {
        const districtSelect = section.querySelector('.district-select');
        const gnSelect = section.querySelector('.gn-select');
        if (!districtSelect || !gnSelect) return;

        const selectedDistrict = districtSelect.value;
        const selectedGn = gnSelect.dataset.selected || '';
        const options = divisionMap[selectedDistrict] || [];

        gnSelect.innerHTML = '<option value="">Select GN Division</option>';

        options.forEach((name) => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            if (selectedGn === name) option.selected = true;
            gnSelect.appendChild(option);
        });

        const otherOption = document.createElement('option');
        otherOption.value = '__other__';
        otherOption.textContent = 'Other (type manually)';
        gnSelect.appendChild(otherOption);

        if (selectedGn && !options.includes(selectedGn)) {
            gnSelect.value = '__other__';
            const otherInput = section.querySelector('.gn-other-input');
            if (otherInput) otherInput.value = selectedGn;
        }

        setOtherFieldVisibility(section);
    };

    const toggleRoleSection = () => {
        const selectedRole = roleSelect.value;

        sections.forEach((section) => {
            const active = section.dataset.role === selectedRole;
            section.style.display = active ? 'block' : 'none';

            section.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = !active;
            });

            if (active) {
                populateGnDivisions(section);
            }
        });
    };

    sections.forEach((section) => {
        const districtSelect = section.querySelector('.district-select');
        const gnSelect = section.querySelector('.gn-select');

        if (districtSelect) {
            districtSelect.addEventListener('change', () => {
                if (gnSelect) gnSelect.dataset.selected = '';
                populateGnDivisions(section);
            });
        }

        if (gnSelect) {
            gnSelect.addEventListener('change', () => setOtherFieldVisibility(section));
        }
    });

    roleSelect.addEventListener('change', toggleRoleSection);
    toggleRoleSection();
})();
</script>
