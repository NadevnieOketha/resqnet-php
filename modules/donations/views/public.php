<div class="page-header">
    <h1>Post-Disaster Donation Appeals</h1>
</div>

<?php if (empty($requests)): ?>
    <div class="card">
        <div class="card-body">
            <p class="text-muted">No donation appeals yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="warning-grid">
        <?php foreach ($requests as $request): ?>
            <div class="warning-card">
                <div class="warning-card-top">
                    <span class="badge"><?= e(strtoupper($request['status'])) ?></span>
                    <span class="text-muted"><?= e(date('d M Y', strtotime($request['created_at']))) ?></span>
                </div>
                <h3><?= e($request['title']) ?></h3>
                <p><?= e(substr($request['description'], 0, 180)) ?><?= strlen($request['description']) > 180 ? '...' : '' ?></p>
                <p><strong>Location:</strong> <?= e($request['needed_location']) ?></p>
                <p><strong>Progress:</strong> LKR <?= number_format((float) $request['collected_amount'], 2) ?> / <?= number_format((float) $request['target_amount'], 2) ?></p>
                <p class="text-muted">Managed by <?= e($request['ngo_name'] ?? $request['creator_name'] ?? 'Unassigned') ?></p>
                <a href="/donations/<?= (int) $request['id'] ?>" class="btn btn-sm btn-primary">View & Contribute</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
