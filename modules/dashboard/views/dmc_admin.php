<div class="dashboard-header">
    <h1>DMC Admin Dashboard</h1>
    <p>Welcome back, <?= e($user['name']) ?>.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= (int) $user_count ?></div>
        <div class="stat-label">Registered Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= (int) $published_warning_count ?></div>
        <div class="stat-label">Published Warnings</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= (int) $open_request_count ?></div>
        <div class="stat-label">Open Donation Appeals</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">LKR <?= number_format((float) $total_contributions, 2) ?></div>
        <div class="stat-label">Total Contributions</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Control Center</h2>
    </div>
    <div class="card-body">
        <p>Monitor active alerts and coordinate post-disaster donation operations across districts.</p>
        <div class="quick-actions">
            <a href="/dashboard/warnings" class="btn btn-primary">Manage Warnings</a>
            <a href="/dashboard/donations/manage" class="btn btn-outline">Manage Donation Appeals</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Latest Alerts</h2>
    </div>
    <div class="card-body">
        <?php if (empty($warnings)): ?>
            <p class="text-muted">No warnings available.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Severity</th>
                        <th>Location</th>
                        <th>Title</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warnings as $warning): ?>
                        <tr>
                            <td><span class="badge severity-<?= e($warning['severity']) ?>"><?= strtoupper(e($warning['severity'])) ?></span></td>
                            <td><?= e($warning['location']) ?></td>
                            <td><?= e($warning['title']) ?></td>
                            <td><?= e(ucfirst($warning['status'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
