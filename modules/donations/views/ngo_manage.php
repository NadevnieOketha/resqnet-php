<?php
$currentTab = (string) ($current_tab ?? 'pending');
$counts = is_array($counts ?? null) ? $counts : ['pending' => 0, 'received' => 0, 'cancelled' => 0];
$donations = is_array($donations ?? null) ? $donations : [];

$tabs = [
    'pending' => 'Pending',
    'received' => 'Received',
    'cancelled' => 'Cancelled',
];
?>

<section class="welcome">
    <h1>Donations Received</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="package-open"></span>
        <p>Review incoming donations for your collection points. Mark pending donations as received to update inventory.</p>
    </div>
</section>

<section class="section-card" aria-label="Donation tabs">
    <h2 style="margin-top:0;">Donation Queue</h2>

    <div class="form-actions" style="margin:0.4rem 0 0.75rem;">
        <?php foreach ($tabs as $tabKey => $tabLabel): ?>
            <?php $isActive = $currentTab === $tabKey; ?>
            <a href="/dashboard/ngo/donations?tab=<?= e($tabKey) ?>" class="btn <?= $isActive ? 'btn-primary' : '' ?>">
                <?= e($tabLabel) ?> (<?= (int) ($counts[$tabKey] ?? 0) ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($donations)): ?>
        <p class="muted">No <?= e(strtolower($tabs[$currentTab] ?? 'pending')) ?> donations for your collection points.</p>
    <?php else: ?>
        <div class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Donor</th>
                        <th>Collection Point</th>
                        <th>Collection Date</th>
                        <th>Items</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $donation): ?>
                        <?php
                        $id = (int) ($donation['donation_id'] ?? 0);
                        $status = (string) ($donation['status'] ?? 'Pending');
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= $id ?></strong><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($donation['submitted_at'] ?? '-')) ?></span>
                            </td>
                            <td>
                                <?= e((string) ($donation['name'] ?? '-')) ?><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($donation['contact_number'] ?? '-')) ?></span><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($donation['email'] ?? '-')) ?></span>
                            </td>
                            <td>
                                <?= e((string) ($donation['collection_point_name'] ?? '-')) ?><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($donation['collection_point_address'] ?? '-')) ?></span>
                            </td>
                            <td>
                                <?= e((string) ($donation['collection_date'] ?? '-')) ?><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($donation['time_slot'] ?? '-')) ?></span>
                            </td>
                            <td>
                                <?php foreach ((array) ($donation['items'] ?? []) as $item): ?>
                                    <div class="muted" style="font-size:0.72rem;">
                                        <?= e((string) ($item['item_name'] ?? '-')) ?> x <?= (int) ($item['quantity'] ?? 0) ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <?php if ($status === 'Pending'): ?>
                                    <form method="POST" action="/dashboard/ngo/donations/<?= $id ?>/receive" class="inline-form" onsubmit="return confirm('Mark this donation as received and add items to inventory?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-primary btn-sm">Mark Received</button>
                                    </form>
                                <?php else: ?>
                                    <span class="tag"><?= e($status) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
