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

<section class="section-card" aria-label="Active disaster notifications" style="margin-top:1rem;">
    <h2>Disaster Notifications</h2>
    <?php $notifications = (array) ($gn_disaster_notifications ?? []); ?>

    <?php if (empty($notifications)): ?>
        <p class="muted">No active disaster notifications for your GN division.</p>
    <?php else: ?>
        <div style="display:grid; gap:0.7rem; margin-top:0.5rem;">
            <?php foreach ($notifications as $item): ?>
                <article style="border:1px solid var(--color-border); border-radius:12px; padding:0.75rem 0.9rem; background:#fff;">
                    <div style="display:flex; justify-content:space-between; gap:0.8rem; align-items:flex-start;">
                        <div>
                            <div style="font-size:0.68rem; color:#666;">Report #<?= (int) ($item['report_id'] ?? 0) ?></div>
                            <div style="font-weight:700; margin-top:0.15rem;"><?= e(disaster_reports_disaster_label($item)) ?></div>
                            <div class="muted" style="margin-top:0.2rem;">
                                <?= e((string) (($item['district'] ?? '') . ' / ' . ($item['gn_division'] ?? '') . (($item['location'] ?? '') !== '' ? ' / ' . $item['location'] : ''))) ?>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.65rem; color:#666;">Volunteer Verification</div>
                            <div style="font-weight:600; font-size:0.75rem;">
                                <?= (int) ($item['verified_tasks'] ?? 0) ?>/<?= (int) ($item['total_tasks'] ?? 0) ?> Verified
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
