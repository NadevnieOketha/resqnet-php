<div class="dashboard-header">
    <h1>Create Grama Niladhari Account</h1>
    <p>Create a GN user and send credentials via email.</p>
</div>

<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/dashboard/admin/grama-niladhari/create" id="create-gn-form">
            <?= csrf_field() ?>

            <h3 class="section-title">GN Profile</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" value="<?= old('contact_number') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" required><?= old('address') ?></textarea>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="district">District</label>
                    <select id="district" class="district-select" name="district">
                        <option value="">Select District</option>
                        <?php foreach (($districts ?? []) as $district): ?>
                            <option value="<?= e($district) ?>" <?= old('district') === $district ? 'selected' : '' ?>><?= e($district) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gn_division">GN Division</label>
                    <select id="gn_division" class="gn-select" name="gn_division" data-selected="<?= e(old('gn_division')) ?>"></select>
                </div>
            </div>

            <div class="form-group gn-other-wrapper" style="display:none;">
                <label for="gn_division_other">Other GN Division</label>
                <input type="text" id="gn_division_other" class="gn-other-input" name="gn_division_other" value="<?= old('gn_division_other') ?>">
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="service_number">Service Number</label>
                    <input type="text" id="service_number" name="service_number" value="<?= old('service_number') ?>" required>
                </div>
                <div class="form-group">
                    <label for="gn_division_number">GN Division Number</label>
                    <input type="text" id="gn_division_number" name="gn_division_number" value="<?= old('gn_division_number') ?>" required>
                </div>
            </div>

            <h3 class="section-title">Account Credentials</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= old('username') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="6" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Account</button>
                <a href="/dashboard/admin/pending" class="btn btn-outline">Back</a>
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
