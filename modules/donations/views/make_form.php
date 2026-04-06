<?php
$submitRole = (string) ($submit_role ?? 'guest');
$isLoggedDonor = !empty($is_logged_donor);
$defaults = is_array($defaults ?? null) ? $defaults : [];
$districts = is_array($districts ?? null) ? $districts : [];
$districtMap = is_array($district_map ?? null) ? $district_map : [];
$selectedDistrict = (string) ($selected_district ?? '');
$selectedGn = (string) ($selected_gn ?? '');
$collectionPoints = is_array($collection_points ?? null) ? $collection_points : [];
$catalogGrouped = is_array($catalog_grouped ?? null) ? $catalog_grouped : [];
$timeSlots = is_array($time_slots ?? null) ? $time_slots : [];
$locationProfileComplete = !empty($location_profile_complete);
$oldInput = $_SESSION['_old_input'] ?? [];

$fieldValue = static function (string $key, string $fallback = '') use ($oldInput, $defaults): string {
    if (array_key_exists($key, $oldInput)) {
        return (string) $oldInput[$key];
    }

    return (string) ($defaults[$key] ?? $fallback);
};

$selectedPointId = (int) ($oldInput['collection_point_id'] ?? 0);
$selectedTimeSlot = (string) ($oldInput['time_slot'] ?? '');
$selectedDate = (string) ($oldInput['collection_date'] ?? date('Y-m-d'));
$confirmedChecked = array_key_exists('confirmation', $oldInput) ? ((string) $oldInput['confirmation'] === '1') : false;
?>

<section class="welcome">
    <h1>Make a Donation</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="hand-heart"></span>
        <p>
            <?= $isLoggedDonor
                ? 'Submit donation details and track your status from My Donations.'
                : 'You can donate as a guest. Use your email tracking link to view status or cancel while the donation is pending.' ?>
        </p>
    </div>
</section>

<?php if (!$isLoggedDonor): ?>
<section class="section-card" aria-label="Location filters">
    <h2 style="margin-top:0;">Choose District and GN Division</h2>
    <form method="GET" action="/make-donation" class="form-grid-3">
        <div class="form-group" style="margin:0;">
            <label for="district_filter">District</label>
            <select class="input" id="district_filter" name="district" required>
                <option value="">Select district</option>
                <?php foreach ($districts as $district): ?>
                    <option value="<?= e((string) $district) ?>" <?= $selectedDistrict === $district ? 'selected' : '' ?>>
                        <?= e((string) $district) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin:0;">
            <label for="gn_filter">GN Division</label>
            <select class="input" id="gn_filter" name="gn_division" required>
                <option value="">Select district first</option>
            </select>
        </div>

        <div class="form-group" style="margin:0; display:flex; align-items:end;">
            <button type="submit" class="btn btn-primary">Load Collection Points</button>
        </div>
    </form>

    <?php if ($selectedDistrict !== '' && $selectedGn !== '' && empty($collectionPoints)): ?>
        <p class="muted" style="margin-top:0.75rem;">No collection points found for the selected district and GN Division.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="section-card" aria-label="Donation details form">
    <h2 style="margin-top:0;">Donation Details</h2>

    <form method="POST" action="/make-donation">
        <?= csrf_field() ?>

        <?php if ($isLoggedDonor): ?>
            <div class="form-grid-2" style="margin-bottom:0.75rem;">
                <div>
                    <strong>District</strong><br>
                    <span class="muted"><?= e($selectedDistrict !== '' ? $selectedDistrict : '-') ?></span>
                </div>
                <div>
                    <strong>GN Division</strong><br>
                    <span class="muted"><?= e($selectedGn !== '' ? $selectedGn : '-') ?></span>
                </div>
            </div>
            <?php if (!$locationProfileComplete): ?>
                <p class="muted" style="margin-top:0; margin-bottom:0.75rem;">Complete your district and GN division in Profile Settings before submitting a donation.</p>
            <?php endif; ?>
        <?php endif; ?>

        <input type="hidden" name="district" value="<?= e($selectedDistrict) ?>">
        <input type="hidden" name="gn_division" value="<?= e($selectedGn) ?>">

        <div class="form-grid-2">
            <div class="form-group" style="margin:0;">
                <label for="name">Name</label>
                <input type="text" class="input" id="name" name="name" value="<?= e($fieldValue('name')) ?>" required>
            </div>

            <div class="form-group" style="margin:0;">
                <label for="contact_number">Contact Number</label>
                <input type="text" class="input" id="contact_number" name="contact_number" value="<?= e($fieldValue('contact_number')) ?>" required>
            </div>

            <div class="form-group" style="margin:0;">
                <label for="email">Email</label>
                <input type="email" class="input" id="email" name="email" value="<?= e($fieldValue('email')) ?>" required>
            </div>

            <div class="form-group" style="margin:0;">
                <label for="collection_point_id">Collection Point</label>
                <select class="input" id="collection_point_id" name="collection_point_id" required>
                    <option value="">Select collection point</option>
                    <?php foreach ($collectionPoints as $point): ?>
                        <?php $pointId = (int) ($point['collection_point_id'] ?? 0); ?>
                        <option value="<?= $pointId ?>" <?= $selectedPointId === $pointId ? 'selected' : '' ?>>
                            <?= e((string) ($point['name'] ?? '-')) ?> - <?= e((string) ($point['full_address'] ?? '-')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($collectionPoints)): ?>
                    <small class="muted">No collection points loaded yet.</small>
                <?php endif; ?>
            </div>

            <div class="form-group" style="margin:0; grid-column:span 2;">
                <label for="address">Address</label>
                <textarea class="input" id="address" name="address" rows="2" required><?= e($fieldValue('address')) ?></textarea>
            </div>

            <div class="form-group" style="margin:0;">
                <label for="collection_date">Expected Collection Date</label>
                <input type="date" class="input" id="collection_date" name="collection_date" min="<?= e(date('Y-m-d')) ?>" value="<?= e($selectedDate) ?>" required>
            </div>

            <div class="form-group" style="margin:0;">
                <label for="time_slot">Preferred Time Slot</label>
                <select class="input" id="time_slot" name="time_slot" required>
                    <option value="">Select time slot</option>
                    <?php foreach ($timeSlots as $slot): ?>
                        <option value="<?= e((string) $slot) ?>" <?= $selectedTimeSlot === $slot ? 'selected' : '' ?>><?= e((string) $slot) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top:0.75rem;">
            <label for="special_notes">Special Notes (optional)</label>
            <textarea class="input" id="special_notes" name="special_notes" rows="3" placeholder="Any handling notes or special pickup instructions"><?= e((string) ($oldInput['special_notes'] ?? '')) ?></textarea>
        </div>

        <h2 style="margin-top:1rem; margin-bottom:0.5rem;">Donation Items</h2>
        <p class="muted" style="margin-top:0;">Enter a quantity for at least one item.</p>

        <?php foreach ($catalogGrouped as $category => $items): ?>
            <div class="table-shell" style="margin-bottom:0.7rem;">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2"><?= e((string) $category) ?></th>
                        </tr>
                        <tr>
                            <th>Item</th>
                            <th style="width:180px;">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ((array) $items as $item): ?>
                            <?php
                            $itemId = (int) ($item['item_id'] ?? 0);
                            $qtyValue = (string) ($oldInput['items'][$itemId] ?? '0');
                            ?>
                            <tr>
                                <td><?= e((string) ($item['item_name'] ?? '-')) ?></td>
                                <td>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="input"
                                        name="items[<?= $itemId ?>]"
                                        value="<?= e($qtyValue) ?>"
                                    >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="form-check" style="margin:0.75rem 0 0;">
            <input type="checkbox" id="confirmation" name="confirmation" value="1" <?= $confirmedChecked ? 'checked' : '' ?>>
            <label for="confirmation">I confirm these donation details are correct.</label>
        </div>

        <div class="form-actions" style="margin-top:0.9rem;">
            <button type="submit" class="btn btn-primary">Submit Donation</button>
            <?php if ($isLoggedDonor): ?>
                <a href="/dashboard/my-donations" class="btn">My Donations</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<script>
(function () {
    const districtMap = <?= json_encode($districtMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const districtEl = document.getElementById('district_filter');
    const gnEl = document.getElementById('gn_filter');
    const selectedGn = <?= json_encode($selectedGn, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    if (!districtEl || !gnEl) {
        return;
    }

    function renderGnOptions() {
        const district = districtEl.value;
        const list = districtMap[district] || [];

        gnEl.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = district ? 'Select GN division' : 'Select district first';
        gnEl.appendChild(placeholder);

        list.forEach((name) => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            if (name === selectedGn) {
                option.selected = true;
            }
            gnEl.appendChild(option);
        });
    }

    districtEl.addEventListener('change', renderGnOptions);
    renderGnOptions();
})();
</script>
