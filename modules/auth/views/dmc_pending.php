<section class="welcome">
    <h1>DMC Account Operations</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="users"></span>
        <p>Approve volunteer and NGO registrations. Manage Grama Niladhari lifecycle from GN Accounts.</p>
    </div>
</section>

<section class="quick-actions" aria-label="DMC quick actions">
    <article class="action-card">
        <h3>GN Accounts</h3>
        <p>Create, activate, deactivate, and resend access confirmation emails for GN officers.</p>
        <a href="/dashboard/admin/grama-niladhari/accounts" class="btn btn-primary">Open GN Accounts</a>
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
