<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>Edit Warning</h1>
    <a href="/dashboard/warnings" class="btn btn-outline">← Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/dashboard/warnings/<?= (int) $warning['id'] ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= old('title', $warning['title']) ?>" required maxlength="180">
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="4" required><?= old('message', $warning['message']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?= old('location', $warning['location']) ?>" required maxlength="180">
            </div>

            <div class="form-group">
                <label for="severity">Severity</label>
                <?php $selectedSeverity = old('severity', $warning['severity']); ?>
                <select id="severity" name="severity" required>
                    <option value="low" <?= $selectedSeverity === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $selectedSeverity === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $selectedSeverity === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="critical" <?= $selectedSeverity === 'critical' ? 'selected' : '' ?>>Critical</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <?php $selectedStatus = old('status', $warning['status']); ?>
                <select id="status" name="status" required>
                    <option value="draft" <?= $selectedStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $selectedStatus === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Warning</button>
                <a href="/dashboard/warnings" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
