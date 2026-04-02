<section class="welcome">
    <h1>Welcome GN <?= e(auth_display_name()) ?>!</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="map-pin"></span>
        <p>Your GN account is active. Keep division-level contact and service details current.</p>
    </div>
</section>

<section class="quick-actions" aria-label="Quick actions">
    <article class="action-card">
        <h3>GN Profile</h3>
        <p>Update GN division details and service information.</p>
        <a href="/profile" class="btn btn-primary">Edit Profile</a>
    </article>
    <article class="action-card">
        <h3>Account Security</h3>
        <p>Change your login credentials and contact email.</p>
        <a href="/profile" class="btn">Manage Access</a>
    </article>
</section>

<section class="section-card" aria-label="GN account">
    <h2>GN Account</h2>
    <div class="form-grid-2">
        <div><strong>Username</strong><br><span class="muted"><?= e($user['username']) ?></span></div>
        <div><strong>Email</strong><br><span class="muted"><?= e($user['email']) ?></span></div>
        <div><strong>GN Division</strong><br><span class="muted"><?= e($profile['gn_division'] ?? '-') ?></span></div>
        <div><strong>Service Number</strong><br><span class="muted"><?= e($profile['service_number'] ?? '-') ?></span></div>
        <div><strong>GN Division Number</strong><br><span class="muted"><?= e($profile['gn_division_number'] ?? '-') ?></span></div>
    </div>
</section>
