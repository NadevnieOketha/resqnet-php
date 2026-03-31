<?php if ($success = get_flash('success')): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>Manage Early Warnings</h1>
    <a href="/dashboard/warnings/create" class="btn btn-primary">+ New Warning</a>
</div>

<?php if (empty($warnings)): ?>
    <div class="card">
        <div class="card-body">
            <p class="text-muted">No warnings found.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body no-padding">
            <table class="table">
                <thead>
                    <tr>
                        <th>Severity</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Issued By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warnings as $warning): ?>
                        <tr>
                            <td><span class="badge severity-<?= e($warning['severity']) ?>"><?= strtoupper(e($warning['severity'])) ?></span></td>
                            <td><?= e($warning['title']) ?></td>
                            <td><?= e($warning['location']) ?></td>
                            <td><?= e(ucfirst($warning['status'])) ?></td>
                            <td><?= e($warning['issuer_name'] ?? 'Unknown') ?></td>
                            <td class="actions">
                                <a href="/dashboard/warnings/<?= (int) $warning['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
                                <form method="POST" action="/dashboard/warnings/<?= (int) $warning['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this warning?');">
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
