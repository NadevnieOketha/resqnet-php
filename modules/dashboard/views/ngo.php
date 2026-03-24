<div class="dashboard-header">
    <h1>NGO Dashboard</h1>
    <p>Welcome back, <?= e($user['name']) ?>.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= (int) $my_request_count ?></div>
        <div class="stat-label">Your Donation Appeals</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">LKR <?= number_format((float) $my_collected_total, 2) ?></div>
        <div class="stat-label">Funds Collected for Your Appeals</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= (int) $open_request_count ?></div>
        <div class="stat-label">Open Appeals (System)</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Response Coordination</h2>
    </div>
    <div class="card-body">
        <p>Publish urgent donation appeals and keep progress transparent to contributors.</p>
        <div class="quick-actions">
            <a href="/dashboard/donations/manage" class="btn btn-primary">Manage Donation Appeals</a>
            <a href="/dashboard/donations/create" class="btn btn-outline">Create New Appeal</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Appeals</h2>
    </div>
    <div class="card-body">
        <?php if (empty($requests)): ?>
            <p class="text-muted">No donation appeals yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['title']) ?></td>
                            <td><?= e($request['needed_location']) ?></td>
                            <td>LKR <?= number_format((float) $request['collected_amount'], 2) ?> / <?= number_format((float) $request['target_amount'], 2) ?></td>
                            <td><?= e(ucfirst($request['status'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
