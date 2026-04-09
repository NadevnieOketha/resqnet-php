<?php
$locations = $locations ?? [];
?>

<section class="welcome">
    <h1>Donation Requests by Safe Location</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="clipboard-list"></span>
        <p>Requests are grouped by assigned safe location. Use Gather Requirement to calculate item-wise totals, then mark location as Fulfilled when complete.</p>
    </div>
</section>

<section class="section-card" aria-label="Safe location request groups">
    <h2 style="margin-top:0;">Assigned Safe Locations</h2>

    <?php if (empty($locations)): ?>
        <p class="muted">No safe locations are assigned to your account yet.</p>
    <?php else: ?>
        <div class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>Safe Location</th>
                        <th>Pending Requests</th>
                        <th>Fulfillment Progress</th>
                        <th>Reserved NGO Details</th>
                        <th>Latest Request</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                        <?php
                        $locationId = (int) ($location['location_id'] ?? 0);
                        $pendingCount = (int) ($location['requested_count'] ?? 0);
                        $latestStatus = (string) ($location['latest_fulfillment_status'] ?? 'Open');
                        $pendingRequests = (array) ($location['pending_requests'] ?? []);
                        $ngoName = trim((string) ($location['ngo_organization_name'] ?? ''));
                        ?>
                        <tr>
                            <td>
                                <strong><?= e((string) ($location['location_name'] ?? 'Safe Location')) ?></strong><br>
                                <span class="muted" style="font-size:0.7rem;">
                                    <?= e((string) ($location['district'] ?? '-')) ?> / <?= e((string) ($location['gn_division'] ?? '-')) ?>
                                </span>
                                <?php if (!empty($pendingRequests)): ?>
                                    <div style="margin-top:0.5rem; font-size:0.68rem; color:#555;">
                                        <?php foreach ($pendingRequests as $request): ?>
                                            <div>
                                                #<?= (int) ($request['request_id'] ?? 0) ?>
                                                - <?= e((string) ($request['requester_name'] ?? 'User')) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= $pendingCount ?></strong>
                            </td>
                            <td>
                                <?= e($latestStatus !== '' ? $latestStatus : 'Open') ?>
                            </td>
                            <td>
                                <?php if ($ngoName !== ''): ?>
                                    <strong><?= e($ngoName) ?></strong><br>
                                    <span class="muted" style="font-size:0.7rem;"><?= e((string) ($location['ngo_contact_person_name'] ?? '-')) ?></span><br>
                                    <span class="muted" style="font-size:0.7rem;"><?= e((string) ($location['ngo_contact_email'] ?? '-')) ?></span><br>
                                    <span class="muted" style="font-size:0.7rem;"><?= e((string) ($location['ngo_contact_number'] ?? '-')) ?></span>
                                <?php else: ?>
                                    <span class="muted">No NGO reservation yet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e((string) ($location['latest_request_at'] ?? '-')) ?>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="/dashboard/gn/donation-requests/<?= $locationId ?>/gather" class="btn btn-primary btn-sm">Gather Requirement</a>
                                <form method="POST" action="/dashboard/gn/donation-requests/<?= $locationId ?>/fulfilled" class="inline-form" style="margin-left:0.35rem;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm" onclick="return confirm('Mark this safe location as fulfilled?');">Fulfilled</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
