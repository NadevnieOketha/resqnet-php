<div class="auth-container">
    <div class="auth-card" style="max-width: 460px;">
        <h2>Reset Password</h2>
        <p class="auth-subtitle">Set a new password for your account.</p>

        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/reset-password">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Update Password</button>
        </form>
    </div>
</div>
