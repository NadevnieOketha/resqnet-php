<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>Edit Donation Appeal</h1>
    <a href="/dashboard/donations/manage" class="btn btn-outline">← Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/dashboard/donations/<?= (int) $request['id'] ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title">Appeal Title</label>
                <input type="text" id="title" name="title" value="<?= old('title', $request['title']) ?>" required maxlength="200">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required><?= old('description', $request['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="needed_location">Affected Location</label>
                <input type="text" id="needed_location" name="needed_location" value="<?= old('needed_location', $request['needed_location']) ?>" required maxlength="180">
            </div>

            <div class="form-group">
                <label for="target_amount">Target Amount (LKR)</label>
                <input type="number" id="target_amount" name="target_amount" value="<?= old('target_amount', (string) $request['target_amount']) ?>" min="1" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <?php $selectedStatus = old('status', $request['status']); ?>
                <select id="status" name="status" required>
                    <option value="open" <?= $selectedStatus === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="closed" <?= $selectedStatus === 'closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="fulfilled" <?= $selectedStatus === 'fulfilled' ? 'selected' : '' ?>>Fulfilled</option>
                </select>
            </div>

            <?php if (is_role('dmc_admin')): ?>
                <div class="form-group">
                    <label for="assigned_ngo">Assign NGO (optional)</label>
                    <?php $selectedNgo = old('assigned_ngo', (string) ($request['assigned_ngo'] ?? '')); ?>
                    <select id="assigned_ngo" name="assigned_ngo">
                        <option value="">Unassigned</option>
                        <?php foreach ($ngoUsers as $ngo): ?>
                            <option value="<?= (int) $ngo['id'] ?>" <?= $selectedNgo === (string) $ngo['id'] ? 'selected' : '' ?>>
                                <?= e($ngo['name']) ?> (<?= e($ngo['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Appeal</button>
                <a href="/dashboard/donations/manage" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
