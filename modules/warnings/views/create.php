<?php if ($error = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>Issue Early Warning</h1>
    <a href="/dashboard/warnings" class="btn btn-outline">← Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/dashboard/warnings">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= old('title') ?>" required maxlength="180">
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="4" required><?= old('message') ?></textarea>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?= old('location') ?>" required maxlength="180" placeholder="e.g., Galle District - Coastal Belt">
            </div>

            <div class="form-group">
                <label for="severity">Severity</label>
                <select id="severity" name="severity" required>
                    <?php $selectedSeverity = old('severity', 'medium'); ?>
                    <option value="low" <?= $selectedSeverity === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $selectedSeverity === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $selectedSeverity === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="critical" <?= $selectedSeverity === 'critical' ? 'selected' : '' ?>>Critical</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <?php $selectedStatus = old('status', 'draft'); ?>
                    <option value="draft" <?= $selectedStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $selectedStatus === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Warning</button>
                <a href="/dashboard/warnings" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
