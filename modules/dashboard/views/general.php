<div class="dashboard-header">
    <h1>General Public Dashboard</h1>
    <p>Welcome, <?= e(auth_display_name()) ?>.</p>
</div>

<div class="card">
    <div class="card-header"><h2>Account Overview</h2></div>
    <div class="card-body">
        <p><strong>Username:</strong> <?= e($user['username']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Role:</strong> <?= e(role_label($user['role'])) ?></p>
        <p><strong>SMS Alerts:</strong> <?= !empty($profile['sms_alert']) ? 'Enabled' : 'Disabled' ?></p>
        <div class="quick-actions">
            <a href="/profile" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>
</div>
