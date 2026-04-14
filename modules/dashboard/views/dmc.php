<?php
$analytics = is_array($analytics ?? null) ? $analytics : [];

$cards = (array) ($analytics['cards'] ?? []);
$disasters = (array) ($analytics['disasters'] ?? []);
$volunteers = (array) ($analytics['volunteers'] ?? []);
$donations = (array) ($analytics['donations'] ?? []);
$shelters = (array) ($analytics['shelters'] ?? []);
$requirements = (array) ($analytics['requirements'] ?? []);
$users = (array) ($analytics['users'] ?? []);
$sms = (array) ($analytics['sms'] ?? []);

$maxValue = static function (array $rows): int {
    $max = 0;
    foreach ($rows as $row) {
        $value = (int) ($row['value'] ?? 0);
        if ($value > $max) {
            $max = $value;
        }
    }

    return max(1, $max);
};

$sumValue = static function (array $rows): int {
    $sum = 0;
    foreach ($rows as $row) {
        $sum += (int) ($row['value'] ?? 0);
    }

    return $sum;
};

$fmtPct = static function (float $value): string {
    return number_format($value, 1) . '%';
};

$statusTone = static function (string $status): string {
    return match (strtolower(trim($status))) {
        'pending', 'open', 'alert' => 'warn',
        'approved', 'completed', 'verified', 'received', 'delivered', 'fulfilled', 'active', 'major' => 'good',
        'rejected', 'declined', 'cancelled', 'inactive' => 'bad',
        'reserved', 'assigned', 'accepted', 'in progress', 'minor' => 'mid',
        default => 'neutral',
    };
};

$disasterStatus = (array) ($disasters['status'] ?? []);
$disasterTypes = (array) ($disasters['types'] ?? []);
$disasterDistricts = (array) ($disasters['districts'] ?? []);
$disasterMonthly = (array) ($disasters['monthly'] ?? []);

$volunteerStatus = (array) ($volunteers['status'] ?? []);
$volunteerWorkload = (array) ($volunteers['workload'] ?? []);
$declineByDistrict = (array) ($volunteers['decline_by_district'] ?? []);

$donationStatus = (array) ($donations['status'] ?? []);
$donationPoints = (array) ($donations['collection_points'] ?? []);
$inventoryCategories = (array) ($donations['inventory_categories'] ?? []);
$lowStockItems = (array) ($donations['low_stock_items'] ?? []);

$shelterTotals = (array) ($shelters['totals'] ?? []);
$shelterLocations = (array) ($shelters['locations'] ?? []);

$requirementStatus = (array) ($requirements['status'] ?? []);
$requirementDistricts = (array) ($requirements['districts'] ?? []);
$requirementCategories = (array) ($requirements['categories'] ?? []);

$roleCounts = (array) ($users['roles'] ?? []);
$activityCounts = (array) ($users['activity'] ?? []);

$smsSeverity = (array) ($sms['severity'] ?? []);
$smsStations = (array) ($sms['stations'] ?? []);
$smsMonthly = (array) ($sms['monthly'] ?? []);
?>

<style>
    .dmc-analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 0.9rem;
    }

    .dmc-chart-card {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        background: #fff;
        padding: 1rem;
    }

    .dmc-chart-card h3 {
        margin: 0;
        font-size: 0.9rem;
    }

    .dmc-chart-card .muted {
        font-size: 0.74rem;
        margin-top: 0.2rem;
    }

    .dmc-stack {
        display: flex;
        width: 100%;
        height: 12px;
        overflow: hidden;
        border-radius: 999px;
        background: #eef2f4;
        margin-top: 0.7rem;
    }

    .dmc-stack-segment {
        height: 100%;
    }

    .dmc-seg-good { background: #2f9e44; }
    .dmc-seg-bad { background: #d9480f; }
    .dmc-seg-warn { background: #f08c00; }
    .dmc-seg-mid { background: #1c7ed6; }
    .dmc-seg-neutral { background: #868e96; }

    .dmc-legend {
        list-style: none;
        margin: 0.75rem 0 0;
        padding: 0;
        display: grid;
        gap: 0.35rem;
    }

    .dmc-legend li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.74rem;
    }

    .dmc-legend-label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .dmc-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .dmc-bars {
        margin-top: 0.65rem;
        display: grid;
        gap: 0.45rem;
    }

    .dmc-bar-row {
        display: grid;
        grid-template-columns: minmax(130px, 1fr) 2fr auto;
        gap: 0.55rem;
        align-items: center;
        font-size: 0.74rem;
    }

    .dmc-bar-track {
        height: 9px;
        border-radius: 999px;
        background: #edf1f3;
        overflow: hidden;
    }

    .dmc-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: #1c7ed6;
    }

    .dmc-columns {
        margin-top: 0.75rem;
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.45rem;
        align-items: end;
    }

    .dmc-column {
        text-align: center;
    }

    .dmc-column-bar {
        height: 90px;
        display: flex;
        align-items: flex-end;
    }

    .dmc-column-bar span {
        width: 100%;
        background: linear-gradient(180deg, #1c7ed6 0%, #4dabf7 100%);
        border-radius: 8px 8px 3px 3px;
        min-height: 6px;
        display: block;
    }

    .dmc-column-label {
        margin-top: 0.35rem;
        font-size: 0.66rem;
        color: var(--color-text-subtle);
    }

    .dmc-column-value {
        font-size: 0.7rem;
        font-weight: 700;
    }

    .dmc-risk-list {
        list-style: none;
        margin: 0.75rem 0 0;
        padding: 0;
        display: grid;
        gap: 0.45rem;
    }

    .dmc-risk-item {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 0.55rem 0.65rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.73rem;
    }

    .dmc-risk-item .meta {
        color: var(--color-text-subtle);
        font-size: 0.68rem;
    }

    .dmc-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.18rem 0.48rem;
        border: 1px solid var(--color-border);
        font-size: 0.64rem;
        font-weight: 700;
    }

    .dmc-badge-green { background: #e9f8ee; color: #1f7a3f; }
    .dmc-badge-yellow { background: #fff8e6; color: #a55f00; }
    .dmc-badge-red { background: #fff0ed; color: #b02a00; }
</style>

<section class="welcome">
    <h1>DMC Analytics Dashboard</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="line-chart"></span>
        <p>Operational analytics are now shown first while all existing DMC management actions remain available below.</p>
    </div>
</section>

<section class="kpi-grid" aria-label="DMC top metrics">
    <article class="kpi-card">
        <div class="label">Pending Approvals</div>
        <div class="value"><?= (int) ($cards['pending_approvals'] ?? 0) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">Pending Disaster Reports</div>
        <div class="value"><?= (int) ($cards['pending_reports'] ?? 0) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">Active Volunteer Tasks</div>
        <div class="value"><?= (int) ($cards['active_tasks'] ?? 0) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">Shelter Utilization</div>
        <div class="value"><?= e($fmtPct((float) ($cards['shelter_utilization_pct'] ?? 0.0))) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">Low / Out Stock Items</div>
        <div class="value"><?= (int) ($cards['low_stock_items'] ?? 0) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">Active SMS Subscribers</div>
        <div class="value"><?= (int) ($cards['active_sms_subscribers'] ?? 0) ?></div>
    </article>
</section>

<section class="section-card" aria-label="Disaster and volunteer analytics">
    <h2>Disaster Reports and Volunteer Response</h2>
    <div class="dmc-analytics-grid">
        <article class="dmc-chart-card">
            <h3>Disaster Reports by Status</h3>
            <p class="muted">Current processing pipeline health.</p>
            <?php $totalDisasterStatus = $sumValue($disasterStatus); ?>
            <div class="dmc-stack">
                <?php foreach ($disasterStatus as $row): ?>
                    <?php $w = $totalDisasterStatus > 0 ? (((int) ($row['value'] ?? 0) * 100) / $totalDisasterStatus) : 0; ?>
                    <span class="dmc-stack-segment dmc-seg-<?= e($statusTone((string) ($row['label'] ?? ''))) ?>" style="width: <?= number_format($w, 2, '.', '') ?>%"></span>
                <?php endforeach; ?>
            </div>
            <ul class="dmc-legend">
                <?php foreach ($disasterStatus as $row): ?>
                    <?php $tone = $statusTone((string) ($row['label'] ?? '')); ?>
                    <li>
                        <span class="dmc-legend-label"><span class="dmc-dot dmc-seg-<?= e($tone) ?>"></span><?= e((string) ($row['label'] ?? '-')) ?></span>
                        <strong><?= (int) ($row['value'] ?? 0) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="dmc-chart-card">
            <h3>Disaster Type Distribution</h3>
            <p class="muted">Most reported incidents by category.</p>
            <?php $typeMax = $maxValue($disasterTypes); ?>
            <div class="dmc-bars">
                <?php if (empty($disasterTypes)): ?>
                    <p class="muted">No data yet.</p>
                <?php else: ?>
                    <?php foreach ($disasterTypes as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $typeMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Disaster Reports Trend (6 Months)</h3>
            <p class="muted">Monthly report volume for seasonality tracking.</p>
            <?php $reportTrendMax = $maxValue($disasterMonthly); ?>
            <div class="dmc-columns">
                <?php foreach ($disasterMonthly as $row): ?>
                    <?php $value = (int) ($row['value'] ?? 0); ?>
                    <div class="dmc-column">
                        <div class="dmc-column-bar"><span style="height: <?= number_format(($value * 100) / $reportTrendMax, 2, '.', '') ?>%"></span></div>
                        <div class="dmc-column-value"><?= $value ?></div>
                        <div class="dmc-column-label"><?= e((string) ($row['label'] ?? '-')) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Volunteer Task Status</h3>
            <p class="muted">Current task lifecycle mix.</p>
            <?php $totalVolunteerStatus = $sumValue($volunteerStatus); ?>
            <div class="dmc-stack">
                <?php foreach ($volunteerStatus as $row): ?>
                    <?php $w = $totalVolunteerStatus > 0 ? (((int) ($row['value'] ?? 0) * 100) / $totalVolunteerStatus) : 0; ?>
                    <span class="dmc-stack-segment dmc-seg-<?= e($statusTone((string) ($row['label'] ?? ''))) ?>" style="width: <?= number_format($w, 2, '.', '') ?>%"></span>
                <?php endforeach; ?>
            </div>
            <ul class="dmc-legend">
                <?php foreach ($volunteerStatus as $row): ?>
                    <?php $tone = $statusTone((string) ($row['label'] ?? '')); ?>
                    <li>
                        <span class="dmc-legend-label"><span class="dmc-dot dmc-seg-<?= e($tone) ?>"></span><?= e((string) ($row['label'] ?? '-')) ?></span>
                        <strong><?= (int) ($row['value'] ?? 0) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="dmc-chart-card">
            <h3>Active Tasks per Volunteer</h3>
            <p class="muted">Workload distribution for ongoing operations.</p>
            <?php $workloadMax = $maxValue($volunteerWorkload); ?>
            <div class="dmc-bars">
                <?php if (empty($volunteerWorkload)): ?>
                    <p class="muted">No active assignments right now.</p>
                <?php else: ?>
                    <?php foreach ($volunteerWorkload as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $workloadMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Decline Rate by District</h3>
            <p class="muted">Higher percentages indicate volunteer coverage risk.</p>
            <?php $declineMax = $maxValue($declineByDistrict); ?>
            <div class="dmc-bars">
                <?php if (empty($declineByDistrict)): ?>
                    <p class="muted">No decline data available.</p>
                <?php else: ?>
                    <?php foreach ($declineByDistrict as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $declineMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= e($fmtPct((float) $value)) ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </div>
</section>

<section class="section-card" aria-label="Donations and requirements analytics">
    <h2>Donations, Inventory, and Requirements</h2>
    <div class="dmc-analytics-grid">
        <article class="dmc-chart-card">
            <h3>Donation Status</h3>
            <p class="muted">Pipeline from submission to completion.</p>
            <?php $totalDonationStatus = $sumValue($donationStatus); ?>
            <div class="dmc-stack">
                <?php foreach ($donationStatus as $row): ?>
                    <?php $w = $totalDonationStatus > 0 ? (((int) ($row['value'] ?? 0) * 100) / $totalDonationStatus) : 0; ?>
                    <span class="dmc-stack-segment dmc-seg-<?= e($statusTone((string) ($row['label'] ?? ''))) ?>" style="width: <?= number_format($w, 2, '.', '') ?>%"></span>
                <?php endforeach; ?>
            </div>
            <ul class="dmc-legend">
                <?php foreach ($donationStatus as $row): ?>
                    <?php $tone = $statusTone((string) ($row['label'] ?? '')); ?>
                    <li>
                        <span class="dmc-legend-label"><span class="dmc-dot dmc-seg-<?= e($tone) ?>"></span><?= e((string) ($row['label'] ?? '-')) ?></span>
                        <strong><?= (int) ($row['value'] ?? 0) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="dmc-chart-card">
            <h3>Busiest Collection Points</h3>
            <p class="muted">Total donations received by point.</p>
            <?php $pointMax = $maxValue($donationPoints); ?>
            <div class="dmc-bars">
                <?php if (empty($donationPoints)): ?>
                    <p class="muted">No donations logged yet.</p>
                <?php else: ?>
                    <?php foreach ($donationPoints as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $pointMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Inventory by Category</h3>
            <p class="muted">Current stock units across NGOs and collection points.</p>
            <?php $invMax = $maxValue($inventoryCategories); ?>
            <div class="dmc-bars">
                <?php if (empty($inventoryCategories)): ?>
                    <p class="muted">No inventory rows available.</p>
                <?php else: ?>
                    <?php foreach ($inventoryCategories as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $invMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Low and Out of Stock</h3>
            <p class="muted">Actionable replenishment list.</p>
            <ul class="dmc-risk-list">
                <?php if (empty($lowStockItems)): ?>
                    <li class="dmc-risk-item"><span>No low stock risks right now.</span></li>
                <?php else: ?>
                    <?php foreach ($lowStockItems as $item): ?>
                        <li class="dmc-risk-item">
                            <div>
                                <strong><?= e((string) ($item['item_name'] ?? '-')) ?></strong>
                                <div class="meta"><?= e((string) ($item['collection_point'] ?? '-')) ?> • <?= e((string) ($item['category'] ?? '-')) ?></div>
                            </div>
                            <span class="tag"><?= (int) ($item['quantity'] ?? 0) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </article>

        <article class="dmc-chart-card">
            <h3>Requirement Fulfillment Status</h3>
            <p class="muted">Open vs reserved vs fulfilled requests.</p>
            <?php $totalReqStatus = $sumValue($requirementStatus); ?>
            <div class="dmc-stack">
                <?php foreach ($requirementStatus as $row): ?>
                    <?php $w = $totalReqStatus > 0 ? (((int) ($row['value'] ?? 0) * 100) / $totalReqStatus) : 0; ?>
                    <span class="dmc-stack-segment dmc-seg-<?= e($statusTone((string) ($row['label'] ?? ''))) ?>" style="width: <?= number_format($w, 2, '.', '') ?>%"></span>
                <?php endforeach; ?>
            </div>
            <ul class="dmc-legend">
                <?php foreach ($requirementStatus as $row): ?>
                    <?php $tone = $statusTone((string) ($row['label'] ?? '')); ?>
                    <li>
                        <span class="dmc-legend-label"><span class="dmc-dot dmc-seg-<?= e($tone) ?>"></span><?= e((string) ($row['label'] ?? '-')) ?></span>
                        <strong><?= (int) ($row['value'] ?? 0) ?></strong>
                    </li>
                <?php endforeach; ?>
                <li>
                    <span class="dmc-legend-label"><span class="dmc-dot dmc-seg-warn"></span>Unfulfilled Total</span>
                    <strong><?= (int) ($requirements['unfulfilled'] ?? 0) ?></strong>
                </li>
            </ul>
        </article>

        <article class="dmc-chart-card">
            <h3>Most Requested Categories</h3>
            <p class="muted">Demand pressure by item category.</p>
            <?php $reqCatMax = $maxValue($requirementCategories); ?>
            <div class="dmc-bars">
                <?php if (empty($requirementCategories)): ?>
                    <p class="muted">No requirement items logged yet.</p>
                <?php else: ?>
                    <?php foreach ($requirementCategories as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $reqCatMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </div>
</section>

<section class="section-card" aria-label="Shelter, users, and alerts analytics">
    <h2>Shelters, Accounts, and Flood Alert Messaging</h2>
    <div class="dmc-analytics-grid">
        <article class="dmc-chart-card">
            <h3>Shelter Utilization Snapshot</h3>
            <p class="muted">Capacity and occupancy health across safe locations.</p>
            <ul class="dmc-legend">
                <li><span>Total Safe Locations</span><strong><?= (int) ($shelterTotals['locations'] ?? 0) ?></strong></li>
                <li><span>Total Capacity</span><strong><?= (int) ($shelterTotals['capacity'] ?? 0) ?></strong></li>
                <li><span>Total Occupancy</span><strong><?= (int) ($shelterTotals['occupancy'] ?? 0) ?></strong></li>
                <li><span>Overall Utilization</span><strong><?= e($fmtPct((float) ($shelterTotals['utilization_pct'] ?? 0.0))) ?></strong></li>
            </ul>
            <ul class="dmc-risk-list">
                <?php if (empty($shelterLocations)): ?>
                    <li class="dmc-risk-item"><span>No shelters configured yet.</span></li>
                <?php else: ?>
                    <?php foreach (array_slice($shelterLocations, 0, 6) as $location): ?>
                        <?php
                        $badgeClass = 'dmc-badge-green';
                        if (($location['status'] ?? '') === 'red') {
                            $badgeClass = 'dmc-badge-red';
                        } elseif (($location['status'] ?? '') === 'yellow') {
                            $badgeClass = 'dmc-badge-yellow';
                        }
                        ?>
                        <li class="dmc-risk-item">
                            <div>
                                <strong><?= e((string) ($location['label'] ?? '-')) ?></strong>
                                <div class="meta"><?= (int) ($location['occupancy'] ?? 0) ?> / <?= (int) ($location['capacity'] ?? 0) ?></div>
                            </div>
                            <span class="dmc-badge <?= e($badgeClass) ?>"><?= e($fmtPct((float) ($location['value'] ?? 0.0))) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </article>

        <article class="dmc-chart-card">
            <h3>User Roles and Account Activity</h3>
            <p class="muted">Platform account composition and activation state.</p>
            <?php $roleMax = $maxValue($roleCounts); ?>
            <div class="dmc-bars">
                <?php if (empty($roleCounts)): ?>
                    <p class="muted">No account data available.</p>
                <?php else: ?>
                    <?php foreach ($roleCounts as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e(role_label((string) ($row['label'] ?? ''))) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $roleMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <ul class="dmc-legend">
                <?php foreach ($activityCounts as $row): ?>
                    <?php $tone = $statusTone((string) ($row['label'] ?? '')); ?>
                    <li>
                        <span class="dmc-legend-label"><span class="dmc-dot dmc-seg-<?= e($tone) ?>"></span><?= e((string) ($row['label'] ?? '-')) ?></span>
                        <strong><?= (int) ($row['value'] ?? 0) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="dmc-chart-card">
            <h3>SMS Alert Throughput</h3>
            <p class="muted">Delivered forecast alerts over current periods.</p>
            <ul class="dmc-legend">
                <li><span>Sent Today</span><strong><?= (int) ($sms['sent_today'] ?? 0) ?></strong></li>
                <li><span>Sent This Week</span><strong><?= (int) ($sms['sent_this_week'] ?? 0) ?></strong></li>
                <li><span>Sent This Month</span><strong><?= (int) ($sms['sent_this_month'] ?? 0) ?></strong></li>
                <li><span>Active Subscribers</span><strong><?= (int) ($cards['active_sms_subscribers'] ?? 0) ?></strong></li>
            </ul>
            <?php $smsTrendMax = $maxValue($smsMonthly); ?>
            <div class="dmc-columns">
                <?php foreach ($smsMonthly as $row): ?>
                    <?php $value = (int) ($row['value'] ?? 0); ?>
                    <div class="dmc-column">
                        <div class="dmc-column-bar"><span style="height: <?= number_format(($value * 100) / $smsTrendMax, 2, '.', '') ?>%"></span></div>
                        <div class="dmc-column-value"><?= $value ?></div>
                        <div class="dmc-column-label"><?= e((string) ($row['label'] ?? '-')) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>SMS Alerts by Severity</h3>
            <p class="muted">Flood warning levels that triggered deliveries.</p>
            <?php $smsSevMax = $maxValue($smsSeverity); ?>
            <div class="dmc-bars">
                <?php if (empty($smsSeverity)): ?>
                    <p class="muted">No sent alerts in log yet.</p>
                <?php else: ?>
                    <?php foreach ($smsSeverity as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e(ucfirst((string) ($row['label'] ?? '-'))) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $smsSevMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Alerts by Station</h3>
            <p class="muted">Stations generating the most flood warning messages.</p>
            <?php $stationMax = $maxValue($smsStations); ?>
            <div class="dmc-bars">
                <?php if (empty($smsStations)): ?>
                    <p class="muted">No station activity yet.</p>
                <?php else: ?>
                    <?php foreach ($smsStations as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $stationMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Disaster Reports by District</h3>
            <p class="muted">Districts with highest report volume.</p>
            <?php $districtMax = $maxValue($disasterDistricts); ?>
            <div class="dmc-bars">
                <?php if (empty($disasterDistricts)): ?>
                    <p class="muted">No district data available.</p>
                <?php else: ?>
                    <?php foreach ($disasterDistricts as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $districtMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dmc-chart-card">
            <h3>Requirements by District</h3>
            <p class="muted">Where supply demand is concentrated.</p>
            <?php $reqDistrictMax = $maxValue($requirementDistricts); ?>
            <div class="dmc-bars">
                <?php if (empty($requirementDistricts)): ?>
                    <p class="muted">No requirement district data available.</p>
                <?php else: ?>
                    <?php foreach ($requirementDistricts as $row): ?>
                        <?php $value = (int) ($row['value'] ?? 0); ?>
                        <div class="dmc-bar-row">
                            <span><?= e((string) ($row['label'] ?? '-')) ?></span>
                            <div class="dmc-bar-track"><span class="dmc-bar-fill" style="width: <?= number_format(($value * 100) / $reqDistrictMax, 2, '.', '') ?>%"></span></div>
                            <strong><?= $value ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </div>
</section>

<section class="section-card" aria-label="DMC actions" style="margin-top: 1rem;">
    <h2>Management Actions</h2>
    <div class="quick-actions">
        <article class="action-card">
            <h3><span data-lucide="file-text" style="width:14px;height:14px;vertical-align:-2px;"></span> Disaster Reports</h3>
            <p>Review pending reports and verify or reject submissions.</p>
            <a href="/dashboard/reports" class="btn btn-primary">Open Reports</a>
        </article>
        <article class="action-card">
            <h3><span data-lucide="message-square" style="width:14px;height:14px;vertical-align:-2px;"></span> Forum Posts</h3>
            <p>Create, edit, and delete public forum posts.</p>
            <a href="/dashboard/admin/forum-posts" class="btn btn-primary">Manage Posts</a>
        </article>
        <article class="action-card">
            <h3><span data-lucide="building" style="width:14px;height:14px;vertical-align:-2px;"></span> Safe Locations</h3>
            <p>Add shelters, assign GN officers, and maintain capacity records.</p>
            <a href="/dashboard/admin/safe-locations" class="btn btn-primary">Manage Locations</a>
        </article>
        <article class="action-card">
            <h3><span data-lucide="clipboard-check" style="width:14px;height:14px;vertical-align:-2px;"></span> Volunteer Assignments</h3>
            <p>Oversee assignment progress, reassign tasks, and verify completion.</p>
            <a href="/dashboard/admin/volunteer-tasks" class="btn btn-primary">Open Tasks</a>
        </article>
        <article class="action-card">
            <h3><span data-lucide="cloud-rain" style="width:14px;height:14px;vertical-align:-2px;"></span> Forecast Dashboard</h3>
            <p>Track rainfall and temperature for Mahaweli, Kalu, and Kelani basin stations.</p>
            <a href="/dashboard/forecast" class="btn btn-primary">Open Forecast</a>
        </article>
        <article class="action-card">
            <h3><span data-lucide="package-search" style="width:14px;height:14px;vertical-align:-2px;"></span> Donation Requirements</h3>
            <p>Review item-wise requirement totals and notes submitted by GN officers.</p>
            <a href="/dashboard/donation-requirements" class="btn btn-primary">Open Requirements</a>
        </article>
        <article class="action-card">
            <h3>Review Approvals</h3>
            <p>Approve volunteer and NGO registrations pending activation.</p>
            <a href="/dashboard/admin/pending" class="btn btn-primary">Open Queue</a>
        </article>
        <article class="action-card">
            <h3>Create GN Account</h3>
            <p>Create a new Grama Niladhari account with direct credentials.</p>
            <a href="/dashboard/admin/grama-niladhari/create" class="btn">Create Account</a>
        </article>
        <article class="action-card">
            <h3>Profile Settings</h3>
            <p>Update DMC account credentials and contact details.</p>
            <a href="/profile" class="btn">Edit Profile</a>
        </article>
    </div>
</section>

<section class="section-card" aria-label="Pending approvals preview">
    <h2>Pending Approval Preview</h2>

    <?php if (empty($pending_users ?? [])): ?>
        <p class="muted mb-0">No pending volunteer or NGO approvals.</p>
    <?php else: ?>
        <div class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($pending_users ?? []) as $pending): ?>
                        <tr>
                            <td><?= e($pending['display_name']) ?></td>
                            <td><?= e($pending['username']) ?></td>
                            <td><?= e($pending['email']) ?></td>
                            <td><?= e(role_label($pending['role'])) ?></td>
                            <td style="text-align:right;">
                                <form method="POST" action="/dashboard/admin/approve/<?= (int) $pending['user_id'] ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary">Approve</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
