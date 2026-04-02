<section class="welcome">
    <h1>Welcome <?= e(auth_display_name()) ?>!</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="alert-triangle"></span>
        <p>Your account is active. Manage your profile and emergency notification preferences from this dashboard.</p>
    </div>
</section>

<section class="quick-actions" aria-label="Quick actions">
    <article class="action-card">
        <h3>Profile Settings</h3>
        <p>Update personal information, address, and credentials.</p>
        <a href="/profile" class="btn btn-primary">Open Profile</a>
    </article>
    <article class="action-card">
        <h3>SMS Alerts</h3>
        <p>Enable or disable SMS alerts for early warnings.</p>
        <a href="/profile" class="btn">Manage SMS</a>
    </article>
</section>

<section class="section-card" aria-label="Account overview">
    <h2>Account Overview</h2>
    <div class="form-grid-2">
        <div><strong>Username</strong><br><span class="muted"><?= e($user['username']) ?></span></div>
        <div><strong>Email</strong><br><span class="muted"><?= e($user['email']) ?></span></div>
        <div><strong>Role</strong><br><span class="muted"><?= e(role_label($user['role'])) ?></span></div>
        <div><strong>SMS Alerts</strong><br><span class="muted"><?= !empty($profile['sms_alert']) ? 'Enabled' : 'Disabled' ?></span></div>
    </div>
</section>
