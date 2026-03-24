<?php if ($success = get_flash('success')): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>Manage Donation Appeals</h1>
    <a href="/dashboard/donations/create" class="btn btn-primary">+ New Appeal</a>
</div>

<?php if (empty($requests)): ?>
    <div class="card">
        <div class="card-body">
            <p class="text-muted">No donation appeals found.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body no-padding">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>NGO</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['title']) ?></td>
                            <td><?= e($request['needed_location']) ?></td>
                            <td>LKR <?= number_format((float) $request['collected_amount'], 2) ?> / <?= number_format((float) $request['target_amount'], 2) ?></td>
                            <td><?= e(ucfirst($request['status'])) ?></td>
                            <td><?= e($request['ngo_name'] ?? 'Unassigned') ?></td>
                            <td class="actions">
                                <a href="/dashboard/donations/<?= (int) $request['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
                                <form method="POST" action="/dashboard/donations/<?= (int) $request['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this donation appeal?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
