<div class="page-header">
    <h1>Early Warning Feed</h1>
</div>

<?php if (empty($warnings)): ?>
    <div class="card">
        <div class="card-body">
            <p class="text-muted">No active warnings at the moment.</p>
        </div>
    </div>
<?php else: ?>
    <div class="warning-grid">
        <?php foreach ($warnings as $warning): ?>
            <div class="warning-card warning-<?= e($warning['severity']) ?>">
                <div class="warning-card-top">
                    <span class="badge severity-<?= e($warning['severity']) ?>"><?= strtoupper(e($warning['severity'])) ?></span>
                    <span class="text-muted"><?= e(date('d M Y H:i', strtotime($warning['issued_at'] ?? $warning['created_at']))) ?></span>
                </div>
                <h3><?= e($warning['title']) ?></h3>
                <p><?= e($warning['message']) ?></p>
                <p><strong>Location:</strong> <?= e($warning['location']) ?></p>
                <p class="text-muted">Issued by <?= e($warning['issuer_name'] ?? 'System') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
