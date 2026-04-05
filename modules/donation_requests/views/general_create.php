<?php
$locations = $locations ?? [];
$profile = $profile ?? [];
$oldInput = $_SESSION['_old_input'] ?? [];
$selectedLocationId = (string) ($oldInput['safe_location_id'] ?? '');
?>

<section class="welcome">
    <h1>Request a Donation</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="heart-handshake"></span>
        <p>Submit a donation request from your GN division safe location. The assigned Grama Niladhari will gather detailed requirements for NGOs.</p>
    </div>
</section>

<section class="section-card" aria-label="Donation request form">
    <h2 style="margin-top:0;">General User Donation Request</h2>
    <p class="muted" style="margin-top:0;">
        District: <?= e((string) ($profile['district'] ?? '-')) ?> |
        GN Division: <?= e((string) ($profile['gn_division'] ?? '-')) ?>
    </p>

    <form method="POST" action="/donation-requests/submit">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="safe_location_id">Safe Location in Your GN Division</label>
            <select id="safe_location_id" name="safe_location_id" class="input" required>
                <option value="">Select safe location</option>
                <?php foreach ($locations as $location): ?>
                    <?php $id = (int) ($location['location_id'] ?? 0); ?>
                    <option value="<?= $id ?>" <?= $selectedLocationId === (string) $id ? 'selected' : '' ?>>
                        <?= e((string) ($location['location_name'] ?? 'Safe Location')) ?>
                        (<?= e((string) ($location['district'] ?? '-')) ?> / <?= e((string) ($location['gn_division'] ?? '-')) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</section>
