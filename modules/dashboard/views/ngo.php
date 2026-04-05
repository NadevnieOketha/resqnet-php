<section class="welcome">
    <h1>Welcome NGO <?= e(auth_display_name()) ?>!</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="building-2"></span>
        <p>Your NGO account is active. Keep organization and contact details up to date.</p>
    </div>
</section>

<section class="quick-actions" aria-label="Quick actions">
    <article class="action-card">
        <h3>Donation Requirements Feed</h3>
        <p>View safe-location requirement totals gathered by Grama Niladhari officers.</p>
        <a href="/dashboard/donation-requirements" class="btn btn-primary">Open Feed</a>
    </article>
    <article class="action-card">
        <h3>Organization Profile</h3>
        <p>Update registration information and contact details.</p>
        <a href="/profile" class="btn btn-primary">Edit Profile</a>
    </article>
    <article class="action-card">
        <h3>Contact Credentials</h3>
        <p>Manage login username and credentials safely.</p>
        <a href="/profile" class="btn">Manage Access</a>
    </article>
</section>

<section class="section-card" aria-label="Organization account">
    <h2>Organization Account</h2>
    <div class="form-grid-2">
        <div><strong>Username</strong><br><span class="muted"><?= e($user['username']) ?></span></div>
        <div><strong>Login Email</strong><br><span class="muted"><?= e($user['email']) ?></span></div>
        <div><strong>Organization</strong><br><span class="muted"><?= e($profile['organization_name'] ?? '-') ?></span></div>
        <div><strong>Registration No</strong><br><span class="muted"><?= e($profile['registration_number'] ?? '-') ?></span></div>
        <div><strong>Years of Operation</strong><br><span class="muted"><?= e((string) ($profile['years_of_operation'] ?? '-')) ?></span></div>
        <div><strong>Contact Person</strong><br><span class="muted"><?= e($profile['contact_person_name'] ?? '-') ?></span></div>
    </div>
</section>
