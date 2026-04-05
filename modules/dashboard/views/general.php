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
        <h3><span data-lucide="alert-triangle" style="width:14px;height:14px;vertical-align:-2px;"></span> Report a Disaster</h3>
        <p>Submit a verified incident report with location and proof image.</p>
        <a href="/report-disaster" class="btn btn-primary">Open Report Form</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="map-pinned" style="width:14px;height:14px;vertical-align:-2px;"></span> Safe Locations</h3>
        <p>Find nearby shelters and check available space in real time.</p>
        <a href="/safe-locations" class="btn btn-primary">Open Map</a>
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
