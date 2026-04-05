<?php
$requirement = $requirement ?? [];
$canReserve = !empty($can_reserve);
$status = (string) ($requirement['fulfillment_status'] ?? 'Open');
$ngoName = trim((string) ($requirement['ngo_organization_name'] ?? ''));
?>

<section class="welcome">
    <h1>Donation Requirement Details</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="list-checks"></span>
        <p>Review requirement details, item quantities, and fulfillment ownership before proceeding.</p>
    </div>
</section>

<section class="section-card" aria-label="Requirement summary">
    <h2 style="margin-top:0;">Request Summary</h2>

    <div class="form-grid-3">
        <div>
            <strong>Safe Location</strong><br>
            <span class="muted"><?= e((string) ($requirement['location_name'] ?? $requirement['relief_center_name'] ?? '-')) ?></span>
        </div>
        <div>
            <strong>GN Officer</strong><br>
            <span class="muted"><?= e((string) ($requirement['gn_name'] ?? '-')) ?></span>
        </div>
        <div>
            <strong>Status</strong><br>
            <span class="tag"><?= e($status) ?></span>
        </div>
    </div>

    <div class="form-grid-3" style="margin-top:0.75rem;">
        <div>
            <strong>Contact</strong><br>
            <span class="muted"><?= e((string) ($requirement['contact_number'] ?? '-')) ?></span>
        </div>
        <div>
            <strong>Days</strong><br>
            <span class="muted"><?= (int) ($requirement['days_count'] ?? 1) ?></span>
        </div>
        <div>
            <strong>Created</strong><br>
            <span class="muted"><?= e((string) ($requirement['created_at'] ?? '-')) ?></span>
        </div>
    </div>

    <div class="form-grid-2" style="margin-top:0.75rem;">
        <div>
            <strong>Reserved At</strong><br>
            <span class="muted"><?= e((string) ($requirement['reserved_at'] ?? '-')) ?></span>
        </div>
        <div>
            <strong>Fulfilled At</strong><br>
            <span class="muted"><?= e((string) ($requirement['fulfilled_at'] ?? '-')) ?></span>
        </div>
    </div>

    <div style="margin-top:0.75rem;">
        <strong>Situation Description</strong>
        <p class="muted" style="margin:0.2rem 0 0;"><?= nl2br(e((string) ($requirement['situation_description'] ?? '-'))) ?></p>
    </div>

    <div style="margin-top:0.75rem;">
        <strong>Special Notes</strong>
        <p class="muted" style="margin:0.2rem 0 0;"><?= nl2br(e((string) ($requirement['special_notes'] ?? '-'))) ?></p>
    </div>
</section>

<section class="section-card" aria-label="NGO reservation details">
    <h2 style="margin-top:0;">NGO Fulfillment Details</h2>

    <?php if ($ngoName !== ''): ?>
        <div class="form-grid-2">
            <div><strong>NGO Name</strong><br><span class="muted"><?= e($ngoName) ?></span></div>
            <div><strong>Contact Person</strong><br><span class="muted"><?= e((string) ($requirement['ngo_contact_person_name'] ?? '-')) ?></span></div>
            <div><strong>Contact Email</strong><br><span class="muted"><?= e((string) ($requirement['ngo_contact_email'] ?? '-')) ?></span></div>
            <div><strong>Contact Number</strong><br><span class="muted"><?= e((string) ($requirement['ngo_contact_number'] ?? '-')) ?></span></div>
        </div>
    <?php else: ?>
        <p class="muted">No NGO has reserved this request yet.</p>
    <?php endif; ?>

    <?php if ($canReserve && $status === 'Open'): ?>
        <form method="POST" action="/dashboard/donation-requirements/<?= (int) ($requirement['requirement_id'] ?? 0) ?>/reserve" style="margin-top:0.75rem;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Reserve this donation request for fulfillment?');">Fulfill</button>
        </form>
    <?php endif; ?>
</section>

<section class="section-card" aria-label="Requirement items">
    <h2 style="margin-top:0;">Goods and Quantities</h2>

    <div class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ((array) ($requirement['items'] ?? []) as $item): ?>
                    <tr>
                        <td><?= e((string) ($item['item_category'] ?? '-')) ?></td>
                        <td><?= e((string) ($item['item_name'] ?? '-')) ?></td>
                        <td><?= e((string) ($item['quantity'] ?? '0')) ?></td>
                        <td><?= e((string) ($item['unit'] ?? 'units')) ?></td>
                        <td><?= e((string) ($item['source'] ?? 'pack')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:0.75rem;">
        <a href="/dashboard/donation-requirements" class="btn">Back to List</a>
    </div>
</section>
