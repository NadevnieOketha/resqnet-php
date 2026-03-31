<div class="dashboard-header">
    <h1>Grama Niladhari Dashboard</h1>
    <p>Welcome back, <?= e($user['name']) ?>.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= (int) $my_warning_count ?></div>
        <div class="stat-label">Warnings Issued by You</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= (int) $published_warning_count ?></div>
        <div class="stat-label">Published Warnings (System)</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Field Operations</h2>
    </div>
    <div class="card-body">
        <p>Create and update location-specific warnings so communities can react quickly.</p>
        <div class="quick-actions">
            <a href="/dashboard/warnings" class="btn btn-primary">Manage Warnings</a>
            <a href="/dashboard/warnings/create" class="btn btn-outline">Issue New Warning</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Warnings</h2>
    </div>
    <div class="card-body">
        <?php if (empty($warnings)): ?>
            <p class="text-muted">No warnings yet.</p>
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
