<?php if ($success = get_flash('success')): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1><?= e($request['title']) ?></h1>
    <a href="/donations" class="btn btn-outline">← Back to Appeals</a>
</div>

<div class="card">
    <div class="card-body">
        <p><?= nl2br(e($request['description'])) ?></p>
        <p><strong>Affected Location:</strong> <?= e($request['needed_location']) ?></p>
        <p><strong>Status:</strong> <?= e(ucfirst($request['status'])) ?></p>
        <p><strong>Managed by:</strong> <?= e($request['ngo_name'] ?? $request['creator_name'] ?? 'Unassigned') ?></p>
        <p><strong>Progress:</strong> LKR <?= number_format((float) $request['collected_amount'], 2) ?> / <?= number_format((float) $request['target_amount'], 2) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Make a Contribution</h2>
    </div>
    <div class="card-body">
        <?php if ($request['status'] !== 'open'): ?>
            <p class="text-muted">This appeal is currently closed for new contributions.</p>
        <?php else: ?>
            <form method="POST" action="/donations/<?= (int) $request['id'] ?>/contribute">
                <?= csrf_field() ?>

                <?php if (!auth_check()): ?>
                    <div class="form-group">
                        <label for="donor_name">Your Name</label>
                        <input type="text" id="donor_name" name="donor_name" value="<?= old('donor_name') ?>" required maxlength="120">
                    </div>

                    <div class="form-group">
                        <label for="donor_email">Your Email (optional)</label>
                        <input type="email" id="donor_email" name="donor_email" value="<?= old('donor_email') ?>" maxlength="150">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="amount">Amount (LKR)</label>
                    <input type="number" id="amount" name="amount" value="<?= old('amount') ?>" min="1" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="message">Message (optional)</label>
                    <textarea id="message" name="message" rows="3"><?= old('message') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Contribute Now</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Contributions</h2>
    </div>
    <div class="card-body">
        <?php if (empty($contributions)): ?>
            <p class="text-muted">No contributions recorded yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Donor</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contributions as $contribution): ?>
                        <tr>
                            <td><?= e($contribution['donor_user_name'] ?? $contribution['donor_name']) ?></td>
                            <td>LKR <?= number_format((float) $contribution['amount'], 2) ?></td>
                            <td><?= e(date('d M Y H:i', strtotime($contribution['created_at']))) ?></td>
                            <td><?= e($contribution['message'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
