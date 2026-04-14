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
        <h3><span data-lucide="alert-triangle" style="width:14px;height:14px;vertical-align:-2px;"></span> Report a Disaster</h3>
        <p>Submit a disaster report for DMC verification and response routing.</p>
        <a href="/report-disaster" class="btn btn-primary">Open Report Form</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="map-pinned" style="width:14px;height:14px;vertical-align:-2px;"></span> Safe Locations</h3>
        <p>View shelters in your area and check which ones still have space.</p>
        <a href="/safe-locations" class="btn btn-primary">Open Map</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="cloud-rain" style="width:14px;height:14px;vertical-align:-2px;"></span> Forecast Dashboard</h3>
        <p>Monitor rainfall and temperature outlook at river-basin stations.</p>
        <a href="/dashboard/forecast" class="btn btn-primary">Open Forecast</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="gift" style="width:14px;height:14px;vertical-align:-2px;"></span> Make a Donation</h3>
        <p>Donate supplies to NGO collection points in your district and GN division.</p>
        <a href="/make-donation" class="btn btn-primary">Open Donation Form</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="package-search" style="width:14px;height:14px;vertical-align:-2px;"></span> My Donations</h3>
        <p>Review your donation history and pending requests.</p>
        <a href="/dashboard/my-donations" class="btn">View My Donations</a>
    </article>
    <article class="action-card">
        <h3>Skills & Preferences</h3>
        <p>Adjust your volunteer preferences and specialized skills.</p>
        <a href="/profile" class="btn">Manage Skills</a>
    </article>
</section>

<?php
$volunteerSnapshot = (array) ($volunteer_snapshot ?? []);
$latestTask = (array) ($volunteerSnapshot['latest_task'] ?? []);
$latestTaskLabel = !empty($latestTask) ? disaster_reports_disaster_label($latestTask) : '-';
$latestTaskDistrict = trim((string) ($latestTask['district'] ?? ''));
$latestTaskStatus = trim((string) ($latestTask['status'] ?? ''));
?>
<section class="kpi-grid" aria-label="Volunteer assignments summary">
    <article class="kpi-card">
        <div class="label">Active Tasks</div>
        <div class="value"><?= (int) ($volunteerSnapshot['active_tasks'] ?? 0) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">Completed + Verified</div>
        <div class="value"><?= (int) ($volunteerSnapshot['completed_tasks'] ?? 0) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">Latest Task</div>
        <div class="value" style="font-size:1.05rem;"><?= e($latestTaskStatus !== '' ? $latestTaskStatus : '-') ?></div>
        <div class="muted" style="font-size:0.72rem;"><?= e($latestTaskLabel . ($latestTaskDistrict !== '' ? ' | ' . $latestTaskDistrict : '')) ?></div>
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
