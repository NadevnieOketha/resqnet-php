<section class="welcome">
    <h1>Welcome DMC <?= e(auth_display_name()) ?>!</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="shield-alert"></span>
        <p>Review pending account approvals and manage Grama Niladhari account lifecycle operations.</p>
    </div>
</section>

<section class="kpi-grid" aria-label="DMC metrics">
    <article class="kpi-card">
        <div class="label">Pending Approvals</div>
        <div class="value"><?= (int) ($pending_count ?? 0) ?></div>
    </article>
    <article class="kpi-card">
        <div class="label">GN Accounts</div>
        <div class="value"><?= count($gn_users ?? []) ?></div>
    </article>
</section>

<section class="quick-actions" aria-label="DMC actions">
    <article class="action-card">
        <h3><span data-lucide="file-text" style="width:14px;height:14px;vertical-align:-2px;"></span> Disaster Reports</h3>
        <p>Review pending reports and verify or reject submissions.</p>
        <a href="/dashboard/reports" class="btn btn-primary">Open Reports</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="building" style="width:14px;height:14px;vertical-align:-2px;"></span> Safe Locations</h3>
        <p>Add shelters, assign GN officers, and maintain capacity records.</p>
        <a href="/dashboard/admin/safe-locations" class="btn btn-primary">Manage Locations</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="cloud-rain" style="width:14px;height:14px;vertical-align:-2px;"></span> Forecast Dashboard</h3>
        <p>Track rainfall and temperature for Mahaweli, Kalu, and Kelani basin stations.</p>
        <a href="/dashboard/forecast" class="btn btn-primary">Open Forecast</a>
    </article>
    <article class="action-card">
        <h3><span data-lucide="package-search" style="width:14px;height:14px;vertical-align:-2px;"></span> Donation Requirements</h3>
        <p>Review item-wise requirement totals and notes submitted by GN officers.</p>
        <a href="/dashboard/donation-requirements" class="btn btn-primary">Open Requirements</a>
    </article>
    <article class="action-card">
        <h3>Review Approvals</h3>
        <p>Approve volunteer and NGO registrations pending activation.</p>
        <a href="/dashboard/admin/pending" class="btn btn-primary">Open Queue</a>
    </article>
    <article class="action-card">
        <h3>Create GN Account</h3>
        <p>Create a new Grama Niladhari account with direct credentials.</p>
        <a href="/dashboard/admin/grama-niladhari/create" class="btn">Create Account</a>
    </article>
    <article class="action-card">
        <h3>Profile Settings</h3>
        <p>Update DMC account credentials and contact details.</p>
        <a href="/profile" class="btn">Edit Profile</a>
    </article>
</section>

<section class="section-card" aria-label="Pending approvals preview">
    <h2>Pending Approval Preview</h2>

    <?php if (empty($pending_users ?? [])): ?>
        <p class="muted mb-0">No pending volunteer or NGO approvals.</p>
    <?php else: ?>
        <div class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($pending_users ?? []) as $pending): ?>
                        <tr>
                            <td><?= e($pending['display_name']) ?></td>
                            <td><?= e($pending['username']) ?></td>
                            <td><?= e($pending['email']) ?></td>
                            <td><?= e(role_label($pending['role'])) ?></td>
                            <td style="text-align:right;">
                                <form method="POST" action="/dashboard/admin/approve/<?= (int) $pending['user_id'] ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary">Approve</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
