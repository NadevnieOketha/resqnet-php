<section class="welcome">
    <h1>Welcome Volunteer <?= e(auth_display_name()) ?>!</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="shield-check"></span>
        <p>Your volunteer account is active. Keep your skills and preferences updated for faster assignment.</p>
    </div>
</section>

<section class="quick-actions" aria-label="Quick actions">
    <article class="action-card">
        <h3>Update Profile</h3>
        <p>Maintain contact, district, and GN division information.</p>
        <a href="/profile" class="btn btn-primary">Edit Profile</a>
    </article>
    <article class="action-card">
        <h3>Skills & Preferences</h3>
        <p>Adjust your volunteer preferences and specialized skills.</p>
        <a href="/profile" class="btn">Manage Skills</a>
    </article>
</section>

<section class="section-card" aria-label="Volunteer account">
    <h2>Volunteer Account</h2>
    <div class="form-grid-2">
        <div><strong>Username</strong><br><span class="muted"><?= e($user['username']) ?></span></div>
        <div><strong>Email</strong><br><span class="muted"><?= e($user['email']) ?></span></div>
        <div><strong>District</strong><br><span class="muted"><?= e($profile['district'] ?? '-') ?></span></div>
        <div><strong>GN Division</strong><br><span class="muted"><?= e($profile['gn_division'] ?? '-') ?></span></div>
    </div>

    <?php if (!empty($profile['preferences'])): ?>
        <h2 style="margin-top:1.2rem;">Preferences</h2>
        <div>
            <?php foreach (($profile['preferences'] ?? []) as $preference): ?>
                <span class="tag"><?= e($preference) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($profile['skills'])): ?>
        <h2 style="margin-top:1.2rem;">Skills</h2>
        <div>
            <?php foreach (($profile['skills'] ?? []) as $skill): ?>
                <span class="tag"><?= e($skill) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
