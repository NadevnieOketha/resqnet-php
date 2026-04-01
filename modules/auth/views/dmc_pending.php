<section class="welcome">
    <h1>DMC Account Operations</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="users"></span>
        <p>Approve volunteer and NGO registrations and manage Grama Niladhari access emails.</p>
    </div>
</section>

<section class="quick-actions" aria-label="DMC quick actions">
    <article class="action-card">
        <h3>Create GN Account</h3>
        <p>Create and provision a new Grama Niladhari account.</p>
        <a href="/dashboard/admin/grama-niladhari/create" class="btn btn-primary">Create Account</a>
    </article>
</section>

<section class="section-card" aria-label="Pending approvals">
    <h2>Pending Volunteer & NGO Approvals</h2>

    <?php if (empty($pending_users ?? [])): ?>
        <p class="muted mb-0">No pending volunteer or NGO accounts.</p>
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

<section class="section-card" aria-label="GN accounts">
    <h2>Grama Niladhari Accounts</h2>

    <?php if (empty($gn_users ?? [])): ?>
        <p class="muted mb-0">No Grama Niladhari accounts created yet.</p>
    <?php else: ?>
        <div class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>GN Division</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($gn_users ?? []) as $gn): ?>
                        <tr>
                            <td><?= e($gn['name']) ?></td>
                            <td><?= e($gn['username']) ?></td>
                            <td><?= e($gn['email']) ?></td>
                            <td><?= e($gn['gn_division'] ?? '-') ?></td>
                            <td style="text-align:right;">
                                <form method="POST" action="/dashboard/admin/grama-niladhari/<?= (int) $gn['user_id'] ?>/resend" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn">Resend Access Email</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
