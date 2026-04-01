<div class="dashboard-header">
    <h1>DMC Account Operations</h1>
    <p>Approve pending accounts and manage Grama Niladhari access.</p>
</div>

<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($warning = get_flash('warning')): ?>
    <div class="alert alert-warning"><?= e($warning) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Pending Volunteer & NGO Approvals</h2>
        <a href="/dashboard/admin/grama-niladhari/create" class="btn btn-primary btn-sm">Create GN Account</a>
    </div>
    <div class="card-body no-padding">
        <?php if (empty($pending_users ?? [])): ?>
            <div class="card-body">
                <p class="text-muted">No pending volunteer or NGO accounts.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($pending_users ?? []) as $pending): ?>
                        <tr>
                            <td><?= e($pending['display_name']) ?></td>
                            <td><?= e($pending['username']) ?></td>
                            <td><?= e($pending['email']) ?></td>
                            <td><?= e(role_label($pending['role'])) ?></td>
                            <td class="actions">
                                <form method="POST" action="/dashboard/admin/approve/<?= (int) $pending['user_id'] ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Grama Niladhari Accounts</h2>
    </div>
    <div class="card-body no-padding">
        <?php if (empty($gn_users ?? [])): ?>
            <div class="card-body">
                <p class="text-muted">No Grama Niladhari accounts created yet.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>GN Division</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($gn_users ?? []) as $gn): ?>
                        <tr>
                            <td><?= e($gn['name']) ?></td>
                            <td><?= e($gn['username']) ?></td>
                            <td><?= e($gn['email']) ?></td>
                            <td><?= e($gn['gn_division'] ?? '-') ?></td>
                            <td class="actions">
                                <form method="POST" action="/dashboard/admin/grama-niladhari/<?= (int) $gn['user_id'] ?>/resend">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline">Resend Access Email</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
