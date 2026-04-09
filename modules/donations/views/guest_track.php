<?php
$donation = is_array($donation ?? null) ? $donation : [];
$trackingToken = (string) ($tracking_token ?? '');
$status = (string) ($donation['display_status'] ?? ($donation['status'] ?? 'Pending'));
$rawStatus = (string) ($donation['status'] ?? 'Pending');
$canCancel = $rawStatus === 'Pending';
?>

<section class="panel" style="padding:1.1rem;">
    <h1 style="margin-top:0;">Guest Donation Tracker</h1>
    <p class="page-subheading" style="margin-bottom:0.6rem;">
        This page lets you monitor your donation status and cancel while it is still pending.
    </p>

    <div class="form-grid-3" style="margin-top:0.65rem;">
        <div>
            <strong>Reference</strong><br>
            <span class="muted">#<?= (int) ($donation['donation_id'] ?? 0) ?></span>
        </div>
        <div>
            <strong>Status</strong><br>
            <span class="tag"><?= e($status) ?></span>
        </div>
        <div>
            <strong>Collection Point</strong><br>
            <span class="muted"><?= e((string) ($donation['collection_point_name'] ?? '-')) ?></span>
        </div>
    </div>

    <div class="form-grid-2" style="margin-top:0.65rem;">
        <div>
            <strong>Name</strong><br>
            <span class="muted"><?= e((string) ($donation['name'] ?? '-')) ?></span>
        </div>
        <div>
            <strong>Contact Number</strong><br>
            <span class="muted"><?= e((string) ($donation['contact_number'] ?? '-')) ?></span>
        </div>
        <div>
            <strong>Email</strong><br>
            <span class="muted"><?= e((string) ($donation['email'] ?? '-')) ?></span>
        </div>
        <div>
            <strong>Collection Date</strong><br>
            <span class="muted"><?= e((string) ($donation['collection_date'] ?? '-')) ?> (<?= e((string) ($donation['time_slot'] ?? '-')) ?>)</span>
        </div>
    </div>

    <div style="margin-top:0.65rem;">
        <strong>Address</strong><br>
        <span class="muted"><?= nl2br(e((string) ($donation['address'] ?? '-'))) ?></span>
    </div>

    <?php if (trim((string) ($donation['special_notes'] ?? '')) !== ''): ?>
        <div style="margin-top:0.65rem;">
            <strong>Special Notes</strong><br>
            <span class="muted"><?= nl2br(e((string) ($donation['special_notes'] ?? ''))) ?></span>
        </div>
    <?php endif; ?>

    <h2 style="margin-top:1rem; margin-bottom:0.5rem;">Items</h2>
    <div class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ((array) ($donation['items'] ?? []) as $item): ?>
                    <tr>
                        <td><?= e((string) ($item['category'] ?? '-')) ?></td>
                        <td><?= e((string) ($item['item_name'] ?? '-')) ?></td>
                        <td><?= (int) ($item['quantity'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="form-actions" style="margin-top:0.75rem;">
        <a href="/make-donation" class="btn">Make Another Donation</a>
        <?php if ($canCancel): ?>
            <form method="POST" action="/donations/guest/<?= e($trackingToken) ?>/cancel" class="inline-form" onsubmit="return confirm('Cancel this pending donation?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline">Cancel Donation</button>
            </form>
        <?php else: ?>
            <span class="muted" style="font-size:0.75rem;">This donation is no longer cancellable.</span>
        <?php endif; ?>
    </div>
</section>
