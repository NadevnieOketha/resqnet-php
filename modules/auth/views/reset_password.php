<div class="auth-container">
    <div class="auth-card">
        <h2>Reset Password</h2>

        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (empty($token_valid)): ?>
            <div class="alert alert-error">This reset link is invalid or expired.</div>
            <p class="auth-link"><a href="/forgot-password">Request a new reset link</a></p>
        <?php else: ?>
            <form method="POST" action="/reset-password">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Update Password</button>
            </form>
        <?php endif; ?>

        <p class="auth-link"><a href="/login">Back to sign in</a></p>
    </div>
</div>
