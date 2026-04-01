<div class="dashboard-header">
    <h1>Volunteer Dashboard</h1>
    <p>Welcome, <?= e(auth_display_name()) ?>.</p>
</div>

<div class="card">
    <div class="card-header"><h2>Account Status</h2></div>
    <div class="card-body">
        <p><strong>Username:</strong> <?= e($user['username']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Role:</strong> <?= e(role_label($user['role'])) ?></p>
        <p><strong>Status:</strong> Active</p>
        <div class="quick-actions">
            <a href="/profile" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>
</div>
