<section class="welcome">
    <h1>GN Accounts</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="user-cog"></span>
        <p>Create and manage Grama Niladhari accounts. A GN account becomes active after the officer confirms access via the email link.</p>
    </div>
</section>

<section class="quick-actions" aria-label="GN account actions">
    <article class="action-card">
        <h3>Add GN Account</h3>
        <p>Provision a new GN account and send the email confirmation link.</p>
        <a href="/dashboard/admin/grama-niladhari/create" class="btn btn-primary">Create Account</a>
    </article>
</section>

<section class="section-card" aria-label="GN accounts table">
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
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($gn_users ?? []) as $gn): ?>
                        <?php $isActive = (int) ($gn['active'] ?? 0) === 1; ?>
                        <tr>
                            <td><?= e($gn['name']) ?></td>
                            <td><?= e($gn['username']) ?></td>
                            <td><?= e($gn['email']) ?></td>
                            <td><?= e($gn['gn_division'] ?? '-') ?></td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="tag" style="background:#e9f8ee;color:#1f7a3f;">Active</span>
                                <?php else: ?>
                                    <span class="tag" style="background:#fff8e6;color:#a55f00;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <form method="POST" action="/dashboard/admin/grama-niladhari/<?= (int) $gn['user_id'] ?>/resend" class="inline-form" style="margin-right:0.35rem;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn">Resend Email</button>
                                </form>

                                <?php if ($isActive): ?>
                                    <form method="POST" action="/dashboard/admin/grama-niladhari/<?= (int) $gn['user_id'] ?>/deactivate" class="inline-form" onsubmit="return confirm('Deactivate this GN account?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn">Deactivate</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/dashboard/admin/grama-niladhari/<?= (int) $gn['user_id'] ?>/activate" class="inline-form" onsubmit="return confirm('Activate this GN account?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-primary">Activate</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
