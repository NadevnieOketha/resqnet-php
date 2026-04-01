<div class="dashboard-header">
    <h1>Grama Niladhari Dashboard</h1>
    <p>Welcome, <?= e(auth_display_name()) ?>.</p>
</div>

<div class="card">
    <div class="card-header"><h2>GN Account</h2></div>
    <div class="card-body">
        <p><strong>Username:</strong> <?= e($user['username']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>GN Division:</strong> <?= e($profile['gn_division'] ?? '-') ?></p>
        <p><strong>Service Number:</strong> <?= e($profile['service_number'] ?? '-') ?></p>
        <div class="quick-actions">
            <a href="/profile" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>
</div>
