<?php
$requirements = $requirements ?? [];
$isNgo = is_role('ngo');
?>

<section class="welcome">
    <h1>Donation Requirements Feed</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="package-search"></span>
        <p>Browse requests submitted by GN officers. Open each request using View Details to inspect item quantities and fulfillment progress.</p>
    </div>
</section>

<section class="section-card" aria-label="Requirement list">
    <h2 style="margin-top:0;">Safe Location Requirements</h2>

    <?php if (empty($requirements)): ?>
        <p class="muted">No gathered requirements available at the moment.</p>
    <?php else: ?>
        <div class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>Safe Location</th>
                        <th>GN Officer</th>
                        <th>Status</th>
                        <th>Reserved NGO</th>
                        <th>Reserved At</th>
                        <th>Fulfilled At</th>
                        <th>Created</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requirements as $requirement): ?>
                        <?php
                        $status = (string) ($requirement['fulfillment_status'] ?? 'Open');
                        $ngoName = trim((string) ($requirement['ngo_organization_name'] ?? ''));
                        ?>
                        <tr>
                            <td>
                                <strong><?= e((string) ($requirement['location_name'] ?? $requirement['relief_center_name'] ?? 'Safe Location')) ?></strong><br>
                                <span class="muted" style="font-size:0.7rem;">
                                    <?= e((string) ($requirement['district'] ?? '-')) ?> / <?= e((string) ($requirement['gn_division'] ?? '-')) ?>
                                </span>
                            </td>
                            <td>
                                <?= e((string) ($requirement['gn_name'] ?? '-')) ?><br>
                                <span class="muted" style="font-size:0.7rem;"><?= e((string) ($requirement['contact_number'] ?? '-')) ?></span>
                            </td>
                            <td>
                                <span class="tag"><?= e($status) ?></span>
                            </td>
                            <td>
                                <?php if ($ngoName !== ''): ?>
                                    <strong><?= e($ngoName) ?></strong><br>
                                    <span class="muted" style="font-size:0.7rem;"><?= e((string) ($requirement['ngo_contact_person_name'] ?? '-')) ?></span>
                                <?php else: ?>
                                    <span class="muted">Not reserved</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e((string) ($requirement['reserved_at'] ?? '-')) ?></td>
                            <td><?= e((string) ($requirement['fulfilled_at'] ?? '-')) ?></td>
                            <td><?= e((string) ($requirement['created_at'] ?? '-')) ?></td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="/dashboard/donation-requirements/<?= (int) ($requirement['requirement_id'] ?? 0) ?>" class="btn btn-primary btn-sm">View Details</a>
                                <?php if ($isNgo && $status === 'Open'): ?>
                                    <span class="muted" style="font-size:0.7rem; display:block; margin-top:0.25rem;">Reserve available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
