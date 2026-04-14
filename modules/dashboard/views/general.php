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
        <h3><span data-lucide="cloud-rain" style="width:14px;height:14px;vertical-align:-2px;"></span> Forecast Dashboard</h3>
        <p>View river-basin rainfall and temperature trends for alerts and planning.</p>
        <a href="/dashboard/forecast" class="btn btn-primary">Open Forecast</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="heart-handshake" style="width:14px;height:14px;vertical-align:-2px;"></span> Request a Donation</h3>
        <p>Submit a donation request for your GN division safe location.</p>
        <a href="/donation-requests/create" class="btn btn-primary">Open Request Form</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="gift" style="width:14px;height:14px;vertical-align:-2px;"></span> Make a Donation</h3>
        <p>Offer medicine, food, and shelter items to nearby NGO collection points.</p>
        <a href="/make-donation" class="btn btn-primary">Open Donation Form</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="package-search" style="width:14px;height:14px;vertical-align:-2px;"></span> My Donations</h3>
        <p>Track donation status and cancel while requests are still pending.</p>
        <a href="/dashboard/my-donations" class="btn">View My Donations</a>
    </article>
    <article class="action-card">
        <h3>SMS Alerts</h3>
        <p>Enable alerts and choose river/gauge targets from Forecast Dashboard.</p>
        <a href="/dashboard/forecast" class="btn">Manage SMS</a>
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
