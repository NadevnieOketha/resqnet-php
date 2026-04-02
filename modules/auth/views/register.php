<?php
$oldInput = $_SESSION["_old_input"] ?? [];
$queryRole = trim((string) request_query("role", ""));
$allowedRoles = ["general", "volunteer", "ngo"];

$selectedRole = null;
if (
    isset($oldInput["role"]) &&
    in_array((string) $oldInput["role"], $allowedRoles, true)
) {
    $selectedRole = (string) $oldInput["role"];
} elseif (in_array($queryRole, $allowedRoles, true)) {
    $selectedRole = $queryRole;
}

$oldPreferences = is_array($oldInput["preferences"] ?? null)
    ? array_map("strval", $oldInput["preferences"])
    : [];
$oldSkills = is_array($oldInput["skills"] ?? null)
    ? array_map("strval", $oldInput["skills"])
    : [];

$roleTitle = match ($selectedRole) {
    "general" => "General User",
    "volunteer" => "Volunteer",
    "ngo" => "NGO",
    default => "",
};
?>

<?php if ($selectedRole === null): ?>
    <section class="role-layout" aria-labelledby="roleChooserHeading">
        <div class="role-heading-wrap">
            <h1 id="roleChooserHeading">Choose your role</h1>
            <p>Select the role that best describes you to get started.</p>
        </div>

        <div class="choice-grid">
            <article class="choice-card" aria-label="General User signup option">
                <div class="choice-icon"><span data-lucide="user"></span></div>
                <h2>General User</h2>
                <p>Individuals seeking assistance or resources during a disaster.</p>
                <div class="choice-actions">
                    <a href="/register?role=general" class="btn btn-primary">Sign Up</a>
                </div>
            </article>

            <article class="choice-card" aria-label="NGO signup option">
                <div class="choice-icon"><span data-lucide="building-2"></span></div>
                <h2>NGO</h2>
                <p>Organizations providing aid and support to affected communities.</p>
                <div class="choice-actions">
                    <a href="/register?role=ngo" class="btn btn-primary">Sign Up</a>
                </div>
            </article>

            <article class="choice-card" aria-label="Volunteer signup option">
                <div class="choice-icon"><span data-lucide="users"></span></div>
                <h2>Volunteer</h2>
                <p>Individuals offering their time and skills to support relief efforts.</p>
                <div class="choice-actions">
                    <a href="/register?role=volunteer" class="btn btn-primary">Sign Up</a>
                </div>
            </article>
        </div>
    </section>
<?php else: ?>
    <div class="auth-panel auth-panel-wide">
        <h1 class="page-heading">Sign up as <?= e($roleTitle) ?></h1>
        <p class="page-subheading">Complete your account details to continue.</p>

        <div class="form-actions" style="margin-bottom:1rem;">
            <a href="/register" class="btn">Change Role</a>
            <a href="/login" class="btn">Back to Login</a>
        </div>

        <form method="POST" action="/register" id="register-form"  novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="role" value="<?= e($selectedRole) ?>">

            <?php if ($selectedRole === "general"): ?>
                <section class="role-section" data-role="general">
                    <h2>General Public Information</h2>

                    <div class="form-grid-2">
                        <div class="form-field">
                            <label for="general_name">Name</label>
                            <input class="input" type="text" id="general_name" name="name" value="<?= old(
                                "name",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="general_contact">Contact Number</label>
                            <input class="input" type="text" id="general_contact" name="contact_number" value="<?= old(
                                "contact_number",
                            ) ?>">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-field">
                            <label for="general_house_no">House No</label>
                            <input class="input" type="text" id="general_house_no" name="house_no" value="<?= old(
                                "house_no",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="general_street">Street</label>
                            <input class="input" type="text" id="general_street" name="street" value="<?= old(
                                "street",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="general_city">City</label>
                            <input class="input" type="text" id="general_city" name="city" value="<?= old(
                                "city",
                            ) ?>">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-field">
                            <label for="general_district">District</label>
                            <select id="general_district" class="input district-select" name="district">
                                <option value="">Select district</option>
                                <?php foreach (
                                    $districts ?? []
                                    as $district
                                ): ?>
                                    <option value="<?= e($district) ?>" <?= old(
    "district",
) === $district
    ? "selected"
    : "" ?>><?= e($district) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="general_gn">Grama Niladhari Division</label>
                            <select id="general_gn" class="input gn-select" name="gn_division" data-selected="<?= e(
                                old("gn_division"),
                            ) ?>"></select>
                        </div>
                    </div>

                    <div class="form-field gn-other-wrapper" style="display:none;">
                        <label for="general_gn_other">Other GN Division</label>
                        <input class="input gn-other-input" type="text" id="general_gn_other" name="gn_division_other" value="<?= old(
                            "gn_division_other",
                        ) ?>">
                    </div>

                    <div class="form-field">
                        <label for="general_email">Email</label>
                        <input class="input" type="email" id="general_email" name="email" value="<?= old(
                            "email",
                        ) ?>">
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($selectedRole === "volunteer"): ?>
                <section class="role-section" data-role="volunteer">
                    <h2>Volunteer Information</h2>

                    <div class="form-grid-3">
                        <div class="form-field">
                            <label for="volunteer_name">Name</label>
                            <input class="input" type="text" id="volunteer_name" name="name" value="<?= old(
                                "name",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="volunteer_age">Age</label>
                            <input class="input" type="number" id="volunteer_age" name="age" value="<?= old(
                                "age",
                            ) ?>" min="16" max="100">
                        </div>
                        <div class="form-field">
                            <label for="volunteer_gender">Gender</label>
                            <select id="volunteer_gender" class="input" name="gender">
                                <option value="">Select gender</option>
                                <?php foreach ($genders ?? [] as $gender): ?>
                                    <option value="<?= e($gender) ?>" <?= old(
    "gender",
) === $gender
    ? "selected"
    : "" ?>><?= ucfirst(e($gender)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="volunteer_contact">Contact Number</label>
                        <input class="input" type="text" id="volunteer_contact" name="contact_number" value="<?= old(
                            "contact_number",
                        ) ?>">
                    </div>

                    <div class="form-grid-3">
                        <div class="form-field">
                            <label for="volunteer_house_no">House No</label>
                            <input class="input" type="text" id="volunteer_house_no" name="house_no" value="<?= old(
                                "house_no",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="volunteer_street">Street</label>
                            <input class="input" type="text" id="volunteer_street" name="street" value="<?= old(
                                "street",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="volunteer_city">City</label>
                            <input class="input" type="text" id="volunteer_city" name="city" value="<?= old(
                                "city",
                            ) ?>">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-field">
                            <label for="volunteer_district">District</label>
                            <select id="volunteer_district" class="input district-select" name="district">
                                <option value="">Select district</option>
                                <?php foreach (
                                    $districts ?? []
                                    as $district
                                ): ?>
                                    <option value="<?= e($district) ?>" <?= old(
    "district",
) === $district
    ? "selected"
    : "" ?>><?= e($district) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="volunteer_gn">Grama Niladhari Division</label>
                            <select id="volunteer_gn" class="input gn-select" name="gn_division" data-selected="<?= e(
                                old("gn_division"),
                            ) ?>"></select>
                        </div>
                    </div>

                    <div class="form-field gn-other-wrapper" style="display:none;">
                        <label for="volunteer_gn_other">Other GN Division</label>
                        <input class="input gn-other-input" type="text" id="volunteer_gn_other" name="gn_division_other" value="<?= old(
                            "gn_division_other",
                        ) ?>">
                    </div>

                    <div class="form-field">
                        <label for="volunteer_email">Email</label>
                        <input class="input" type="email" id="volunteer_email" name="email" value="<?= old(
                            "email",
                        ) ?>">
                    </div>

                    <div class="form-field">
                        <label>Volunteer Preferences</label>
                        <div class="checkbox-grid">
                            <?php foreach (
                                $volunteer_preferences ?? []
                                as $preference
                            ): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="preferences[]" value="<?= e(
                                        $preference,
                                    ) ?>" <?= in_array(
    $preference,
    $oldPreferences,
    true,
)
    ? "checked"
    : "" ?>>
                                    <span><?= e($preference) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-field">
                        <label>Specialized Skills</label>
                        <div class="checkbox-grid">
                            <?php foreach (
                                $volunteer_skills ?? []
                                as $skill
                            ): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="skills[]" value="<?= e(
                                        $skill,
                                    ) ?>" <?= in_array($skill, $oldSkills, true)
    ? "checked"
    : "" ?>>
                                    <span><?= e($skill) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($selectedRole === "ngo"): ?>
                <section class="role-section" data-role="ngo">
                    <h2>NGO Information</h2>

                    <div class="form-field">
                        <label for="ngo_org_name">Organization Name</label>
                        <input class="input" type="text" id="ngo_org_name" name="organization_name" value="<?= old(
                            "organization_name",
                        ) ?>">
                    </div>

                    <div class="form-grid-2">
                        <div class="form-field">
                            <label for="ngo_reg_no">Registration Number</label>
                            <input class="input" type="text" id="ngo_reg_no" name="registration_number" value="<?= old(
                                "registration_number",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="ngo_years">Years of Operation</label>
                            <input class="input" type="number" id="ngo_years" name="years_of_operation" value="<?= old(
                                "years_of_operation",
                            ) ?>" min="0">
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="ngo_address">Organization Address</label>
                        <textarea class="input" id="ngo_address" name="address" rows="3"><?= old(
                            "address",
                        ) ?></textarea>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-field">
                            <label for="ngo_contact_name">Contact Person Name</label>
                            <input class="input" type="text" id="ngo_contact_name" name="contact_person_name" value="<?= old(
                                "contact_person_name",
                            ) ?>">
                        </div>
                        <div class="form-field">
                            <label for="ngo_contact_tel">Contact Person Telephone</label>
                            <input class="input" type="text" id="ngo_contact_tel" name="contact_person_telephone" value="<?= old(
                                "contact_person_telephone",
                            ) ?>">
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="ngo_contact_email">Contact Person Email (Login Email)</label>
                        <input class="input" type="email" id="ngo_contact_email" name="contact_person_email" value="<?= old(
                            "contact_person_email",
                        ) ?>">
                    </div>
                </section>
            <?php endif; ?>

            <section class="role-section" data-role="credentials" style="display:block; margin-top:1rem;">
                <h2>Account Credentials</h2>

                <div class="form-field">
                    <label for="username">Username</label>
                    <input class="input" type="text" id="username" name="username" value="<?= old(
                        "username",
                    ) ?>" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input class="input" type="password" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="form-field">
                        <label for="password_confirmation">Confirm Password</label>
                        <input class="input" type="password" id="password_confirmation" name="password_confirmation" minlength="6" required>
                    </div>
                </div>
            </section>

            <div class="form-actions" style="margin-top:1.2rem;">
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </div>
        </form>
    </div>

    <?php if (in_array($selectedRole, ["general", "volunteer"], true)): ?>
        <script>
        (() => {
            const divisionMap = <?= json_encode(
                $gn_divisions ?? [],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ) ?>;
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
<?php endif; ?>
