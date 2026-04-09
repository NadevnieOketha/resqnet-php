<?php
$donations = is_array($donations ?? null) ? $donations : [];
?>

<section class="welcome">
    <h1>My Donations</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="package-check"></span>
        <p>Track your submitted donations and cancel any request that is still pending.</p>
    </div>
</section>

<section class="section-card" aria-label="Donation history">
    <div style="display:flex; justify-content:space-between; gap:0.7rem; align-items:center; margin-bottom:0.75rem; flex-wrap:wrap;">
        <h2 style="margin:0;">Donation History</h2>
        <a href="/make-donation" class="btn btn-primary">Make a New Donation</a>
    </div>

    <?php if (empty($donations)): ?>
        <p class="muted">You have not submitted any donations yet.</p>
    <?php else: ?>
        <div class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Collection Point</th>
                        <th>Collection Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $donation): ?>
                        <?php
                        $id = (int) ($donation['donation_id'] ?? 0);
                        $status = (string) ($donation['display_status'] ?? ($donation['status'] ?? 'Pending'));
                        $rawStatus = (string) ($donation['status'] ?? 'Pending');
                        $canCancel = $rawStatus === 'Pending';
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= $id ?></strong><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($donation['submitted_at'] ?? '-')) ?></span>
                            </td>
                            <td><?= e((string) ($donation['collection_point_name'] ?? '-')) ?></td>
                            <td>
                                <?= e((string) ($donation['collection_date'] ?? '-')) ?><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($donation['time_slot'] ?? '-')) ?></span>
                            </td>
                            <td><span class="tag"><?= e($status) ?></span></td>
                            <td>
                                <?php foreach ((array) ($donation['items'] ?? []) as $item): ?>
                                    <div class="muted" style="font-size:0.72rem;">
                                        <?= e((string) ($item['item_name'] ?? '-')) ?> x <?= (int) ($item['quantity'] ?? 0) ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <?php if ($canCancel): ?>
                                    <form method="POST" action="/dashboard/my-donations/<?= $id ?>/cancel" class="inline-form" onsubmit="return confirm('Cancel this pending donation?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted" style="font-size:0.72rem;">Not cancellable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
