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

$locationReady = $selectedDistrict !== '' && $selectedGn !== '';
$hasCollectionPoints = !empty($collectionPoints);
$canProceedToDetails = $locationReady && $hasCollectionPoints;

$selectedPoint = null;
foreach ($collectionPoints as $point) {
    $pointId = (int) ($point['collection_point_id'] ?? 0);
    if ($pointId > 0 && $pointId === $selectedPointId) {
        $selectedPoint = $point;
        break;
    }
}
?>

<style>
  .donation-step + .donation-step {
    margin-top: 1rem;
  }

  .donation-step__title-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.7rem;
  }

  .step-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 58px;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: #fff4cc;
    color: #6f5600;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    text-transform: uppercase;
  }

  .donation-step__title-row h2 {
    margin: 0;
    font-size: 1.05rem;
  }

  .donation-hint {
    margin: 0.35rem 0 0;
    font-size: 0.74rem;
  }

  .donation-selected-area {
    margin: 0.75rem 0 0;
    font-size: 0.76rem;
  }

  .collection-point-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 0.8rem;
    margin-top: 0.75rem;
  }

  .collection-point-card {
    position: relative;
    display: block;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: 0.8rem;
    background: #fff;
    cursor: pointer;
    transition: border-color var(--transition-fast), box-shadow var(--transition-fast), transform var(--transition-fast);
  }

  .collection-point-card:hover {
    border-color: #d5d5d5;
    transform: translateY(-1px);
  }

  .collection-point-card:focus-within {
    box-shadow: var(--shadow-focus);
  }

  .collection-point-card.is-selected {
    border-color: var(--color-accent);
    box-shadow: 0 0 0 1px rgba(255, 204, 0, 0.4);
    background: #fffcf2;
  }

  .collection-point-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .collection-point-card__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
  }

  .collection-point-card__name {
    font-size: 0.82rem;
    line-height: 1.2;
  }

  .collection-point-card__badge {
    font-size: 0.65rem;
    font-weight: 700;
    color: #7a6200;
    background: #fff2bf;
    border-radius: 999px;
    padding: 0.15rem 0.5rem;
    display: none;
  }

  .collection-point-card.is-selected .collection-point-card__badge {
    display: inline-flex;
  }

  .collection-point-card__line {
    margin: 0.2rem 0 0;
    font-size: 0.72rem;
    color: var(--color-text-subtle);
    line-height: 1.35;
  }

  .selected-point-summary {
    margin: 0.65rem 0 0.9rem;
    padding: 0.7rem 0.8rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
    background: var(--color-surface-alt);
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    font-size: 0.74rem;
  }

  .details-fieldset {
    border: 0;
    margin: 0;
    padding: 0;
    min-width: 0;
  }

  .details-fieldset[disabled] {
    opacity: 0.55;
  }

  .donation-empty-state {
    margin-top: 0.75rem;
    padding: 0.8rem;
    border: 1px dashed var(--color-border-strong);
    border-radius: var(--radius-md);
    background: var(--color-surface-alt);
    font-size: 0.74rem;
    color: var(--color-text-subtle);
  }
</style>

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

<section class="section-card donation-step" aria-label="Service area step">
    <div class="donation-step__title-row">
        <span class="step-pill">Step 1</span>
        <h2>Choose Service Area</h2>
    </div>

    <?php if (!$isLoggedDonor): ?>
        <form method="GET" action="/make-donation" class="form-grid-3" id="donationAreaForm">
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
                <button type="submit" class="btn">Apply Area</button>
            </div>
        </form>

        <p class="muted donation-hint">Collection points refresh automatically once both district and GN Division are selected.</p>

        <?php if ($selectedDistrict !== '' || $selectedGn !== ''): ?>
            <p class="donation-selected-area">
                <strong>Current area:</strong>
                <?= e($selectedDistrict !== '' ? $selectedDistrict : '-') ?> / <?= e($selectedGn !== '' ? $selectedGn : '-') ?>
            </p>
        <?php endif; ?>
    <?php else: ?>
        <div class="form-grid-2" style="margin-bottom:0.25rem;">
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
            <p class="muted donation-hint">Complete your district and GN division in <a class="underline-link" href="/profile">Profile Settings</a> before submitting a donation.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="section-card donation-step" aria-label="Donation details form">
    <form method="POST" action="/make-donation" id="donationForm">
        <?= csrf_field() ?>

        <input type="hidden" name="district" value="<?= e($selectedDistrict) ?>">
        <input type="hidden" name="gn_division" value="<?= e($selectedGn) ?>">

        <div class="donation-step__title-row">
            <span class="step-pill">Step 2</span>
            <h2>Select Collection Point</h2>
        </div>

        <?php if (!$locationReady): ?>
            <div class="donation-empty-state">Choose district and GN Division first to load collection points.</div>
        <?php elseif (!$hasCollectionPoints): ?>
            <div class="donation-empty-state">No collection points found for this service area. Try another district or GN Division.</div>
        <?php else: ?>
            <p class="muted donation-hint">Choose one verified drop-off point. The selected point will be used for this donation request.</p>

            <div class="collection-point-grid" id="collectionPointGrid">
                <?php foreach ($collectionPoints as $point): ?>
                    <?php
                    $pointId = (int) ($point['collection_point_id'] ?? 0);
                    $isSelected = $selectedPointId === $pointId;
                    $pointName = (string) ($point['name'] ?? '-');
                    $pointAddress = (string) ($point['full_address'] ?? '-');
                    $landmark = trim((string) ($point['location_landmark'] ?? ''));
                    $contactParts = [];
                    $contactPerson = trim((string) ($point['contact_person'] ?? ''));
                    $contactNumber = trim((string) ($point['contact_number'] ?? ''));
                    if ($contactPerson !== '') {
                        $contactParts[] = $contactPerson;
                    }
                    if ($contactNumber !== '') {
                        $contactParts[] = $contactNumber;
                    }
                    $contactText = implode(' | ', $contactParts);
                    ?>
                    <label
                        class="collection-point-card<?= $isSelected ? ' is-selected' : '' ?>"
                        data-point-card
                        data-point-name="<?= e($pointName) ?>"
                        data-point-address="<?= e($pointAddress) ?>"
                        data-point-contact="<?= e($contactText) ?>"
                    >
                        <input
                            class="collection-point-input"
                            type="radio"
                            name="collection_point_id"
                            value="<?= $pointId ?>"
                            <?= $isSelected ? 'checked' : '' ?>
                            required
                        >
                        <div class="collection-point-card__head">
                            <strong class="collection-point-card__name"><?= e($pointName) ?></strong>
                            <span class="collection-point-card__badge">Selected</span>
                        </div>
                        <p class="collection-point-card__line"><?= e($pointAddress) ?></p>
                        <?php if ($landmark !== ''): ?>
                            <p class="collection-point-card__line">Landmark: <?= e($landmark) ?></p>
                        <?php endif; ?>
                        <?php if ($contactText !== ''): ?>
                            <p class="collection-point-card__line">Contact: <?= e($contactText) ?></p>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <fieldset
            id="donationDetailsFieldset"
            class="details-fieldset"
            data-enforce-point="<?= $canProceedToDetails ? '1' : '0' ?>"
            <?= !$canProceedToDetails ? 'disabled' : '' ?>
        >
            <div class="donation-step__title-row" style="margin-top:1rem;">
                <span class="step-pill">Step 3</span>
                <h2>Donation Details</h2>
            </div>

            <div class="selected-point-summary" id="selectedPointSummary">
                <?php if ($selectedPoint): ?>
                    <strong><?= e((string) ($selectedPoint['name'] ?? 'Selected Collection Point')) ?></strong>
                    <span><?= e((string) ($selectedPoint['full_address'] ?? '-')) ?></span>
                    <?php
                    $summaryContact = trim((string) ($selectedPoint['contact_person'] ?? ''));
                    $summaryNumber = trim((string) ($selectedPoint['contact_number'] ?? ''));
                    $summaryContactText = trim($summaryContact . ($summaryContact !== '' && $summaryNumber !== '' ? ' | ' : '') . $summaryNumber);
                    ?>
                    <?php if ($summaryContactText !== ''): ?>
                        <span class="muted">Contact: <?= e($summaryContactText) ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="muted">Select one collection point to continue with donation details.</span>
                <?php endif; ?>
            </div>

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
                    <label for="time_slot">Preferred Time Slot</label>
                    <select class="input" id="time_slot" name="time_slot" required>
                        <option value="">Select time slot</option>
                        <?php foreach ($timeSlots as $slot): ?>
                            <option value="<?= e((string) $slot) ?>" <?= $selectedTimeSlot === $slot ? 'selected' : '' ?>><?= e((string) $slot) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin:0; grid-column:span 2;">
                    <label for="address">Address</label>
                    <textarea class="input" id="address" name="address" rows="2" required><?= e($fieldValue('address')) ?></textarea>
                </div>

                <div class="form-group" style="margin:0;">
                    <label for="collection_date">Expected Collection Date</label>
                    <input type="date" class="input" id="collection_date" name="collection_date" min="<?= e(date('Y-m-d')) ?>" value="<?= e($selectedDate) ?>" required>
                </div>

                <div class="form-group" style="margin:0;"></div>
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
                <button type="submit" class="btn btn-primary" id="donationSubmitBtn" <?= !$canProceedToDetails ? 'disabled' : '' ?>>Submit Donation</button>
                <?php if ($isLoggedDonor): ?>
                    <a href="/dashboard/my-donations" class="btn">My Donations</a>
                <?php endif; ?>
            </div>
        </fieldset>
    </form>
</section>

<script>
(function () {
    const districtMap = <?= json_encode($districtMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const districtEl = document.getElementById('district_filter');
    const gnEl = document.getElementById('gn_filter');
    const currentDistrict = <?= json_encode($selectedDistrict, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const currentGn = <?= json_encode($selectedGn, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    if (districtEl && gnEl) {
        function renderGnOptions(selectedValue) {
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
                if (selectedValue && name === selectedValue) {
                    option.selected = true;
                }
                gnEl.appendChild(option);
            });
        }

        function applyAreaSelection() {
            const district = districtEl.value.trim();
            const gn = gnEl.value.trim();

            if (!district || !gn) {
                return;
            }

            if (district === currentDistrict && gn === currentGn) {
                return;
            }

            const params = new URLSearchParams(window.location.search);
            params.set('district', district);
            params.set('gn_division', gn);
            window.location.assign('/make-donation?' + params.toString());
        }

        districtEl.addEventListener('change', function () {
            renderGnOptions('');
        });

        gnEl.addEventListener('change', applyAreaSelection);
        renderGnOptions(currentGn);
    }

    const pointCards = Array.from(document.querySelectorAll('[data-point-card]'));
    const detailsFieldset = document.getElementById('donationDetailsFieldset');
    const summaryEl = document.getElementById('selectedPointSummary');
    const submitBtn = document.getElementById('donationSubmitBtn');

    function selectedPointInput() {
        return document.querySelector('input[name="collection_point_id"]:checked');
    }

    function renderPointSummary(card) {
        if (!summaryEl) {
            return;
        }

        if (!card) {
            summaryEl.innerHTML = '<span class="muted">Select one collection point to continue with donation details.</span>';
            return;
        }

        const pointName = card.dataset.pointName || 'Selected Collection Point';
        const pointAddress = card.dataset.pointAddress || '-';
        const pointContact = card.dataset.pointContact || '';

        let html = '<strong>' + escapeHtml(pointName) + '</strong>';
        html += '<span>' + escapeHtml(pointAddress) + '</span>';

        if (pointContact !== '') {
            html += '<span class="muted">Contact: ' + escapeHtml(pointContact) + '</span>';
        }

        summaryEl.innerHTML = html;
    }

    function syncCollectionPointState() {
        const checkedInput = selectedPointInput();

        pointCards.forEach((card) => {
            const input = card.querySelector('input[name="collection_point_id"]');
            card.classList.toggle('is-selected', Boolean(input && input.checked));
        });

        const selectedCard = checkedInput ? checkedInput.closest('[data-point-card]') : null;
        renderPointSummary(selectedCard);

        if (detailsFieldset && detailsFieldset.dataset.enforcePoint === '1') {
            const hasPoint = Boolean(checkedInput);
            detailsFieldset.disabled = !hasPoint;
            if (submitBtn) {
                submitBtn.disabled = !hasPoint;
            }
        }
    }

    pointCards.forEach((card) => {
        const input = card.querySelector('input[name="collection_point_id"]');
        if (!input) {
            return;
        }

        input.addEventListener('change', syncCollectionPointState);
    });

    syncCollectionPointState();
})();
</script>
