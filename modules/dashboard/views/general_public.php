<div class="dashboard-header">
    <h1>General Public Dashboard</h1>
    <p>Welcome, <?= e($user['name']) ?>. Stay updated and support active recovery appeals.</p>
</div>

<div class="stats-grid">
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
        <div class="stat-label">Total Contributions So Far</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>What You Can Do</h2>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="/warnings" class="btn btn-primary">Check Latest Warnings</a>
            <a href="/donations" class="btn btn-outline">Support Donation Appeals</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Warnings</h2>
    </div>
    <div class="card-body">
        <?php if (empty($warnings)): ?>
            <p class="text-muted">No published warnings right now.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Severity</th>
                        <th>Location</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warnings as $warning): ?>
                        <tr>
                            <td><span class="badge severity-<?= e($warning['severity']) ?>"><?= strtoupper(e($warning['severity'])) ?></span></td>
                            <td><?= e($warning['location']) ?></td>
                            <td><?= e($warning['title']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
