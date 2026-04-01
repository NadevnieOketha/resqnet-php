<div class="dashboard-header">
    <h1>DMC Admin Dashboard</h1>
    <p>Welcome, <?= e(auth_display_name()) ?>.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= (int) $pending_count ?></div>
        <div class="stat-label">Pending Volunteer/NGO Approvals</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count($gn_users) ?></div>
        <div class="stat-label">Grama Niladhari Accounts</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Account Operations</h2></div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="/dashboard/admin/pending" class="btn btn-primary">Review Approvals</a>
            <a href="/dashboard/admin/grama-niladhari/create" class="btn btn-outline">Create GN Account</a>
            <a href="/profile" class="btn btn-outline">Edit Profile</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Pending Approvals</h2></div>
    <div class="card-body">
        <?php if (empty($pending_users)): ?>
            <p class="text-muted">No pending volunteer or NGO approvals.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_users as $pending): ?>
                        <tr>
                            <td><?= e($pending['display_name']) ?></td>
                            <td><?= e($pending['username']) ?></td>
                            <td><?= e($pending['email']) ?></td>
                            <td><?= e(ucfirst($pending['role'])) ?></td>
                            <td>
                                <form method="POST" action="/dashboard/admin/approve/<?= (int) $pending['user_id'] ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
