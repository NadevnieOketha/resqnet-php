<?php
$requirements = $requirements ?? [];
?>

<section class="welcome">
    <h1>Donation Requirements Feed</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="package-search"></span>
        <p>Item-wise requirement totals submitted by Grama Niladhari officers. Use this feed to plan NGO inventory and DMC coordination.</p>
    </div>
</section>

<section class="section-card" aria-label="Requirement list">
    <h2 style="margin-top:0;">Safe Location Requirements</h2>

    <?php if (empty($requirements)): ?>
        <p class="muted">No gathered requirements available at the moment.</p>
    <?php else: ?>
        <div style="display:grid; gap:0.95rem;">
            <?php foreach ($requirements as $requirement): ?>
                <article class="card" style="margin:0;">
                    <header class="card-header">
                        <h2 style="margin:0; font-size:0.95rem;">
                            <?= e((string) ($requirement['location_name'] ?? $requirement['relief_center_name'] ?? 'Safe Location')) ?>
                            <span class="muted" style="font-size:0.72rem; font-weight:400;">
                                (<?= e((string) ($requirement['district'] ?? '-')) ?> / <?= e((string) ($requirement['gn_division'] ?? '-')) ?>)
                            </span>
                        </h2>
                        <span class="tag"><?= e((string) ($requirement['status'] ?? 'Gathered')) ?></span>
                    </header>

                    <div class="card-body" style="display:grid; gap:0.8rem;">
                        <div class="form-grid-3">
                            <div><strong>GN Officer</strong><br><span class="muted"><?= e((string) ($requirement['gn_name'] ?? '-')) ?></span></div>
                            <div><strong>Contact</strong><br><span class="muted"><?= e((string) ($requirement['contact_number'] ?? '-')) ?></span></div>
                            <div><strong>Days</strong><br><span class="muted"><?= (int) ($requirement['days_count'] ?? 1) ?></span></div>
                        </div>

                        <div>
                            <strong>Situation Description</strong>
                            <p style="margin:0.2rem 0 0;" class="muted"><?= nl2br(e((string) ($requirement['situation_description'] ?? '-'))) ?></p>
                        </div>

                        <div>
                            <strong>Special Notes</strong>
                            <p style="margin:0.2rem 0 0;" class="muted"><?= nl2br(e((string) ($requirement['special_notes'] ?? '-'))) ?></p>
                        </div>

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
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
