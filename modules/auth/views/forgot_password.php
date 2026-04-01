<div class="auth-container">
    <div class="auth-card">
        <h2>Forgot Password</h2>
        <p class="auth-subtitle">Enter your username or email to receive a reset link.</p>

        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success = get_flash('success')): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($warning = get_flash('warning')): ?>
            <div class="alert alert-warning"><?= e($warning) ?></div>
        <?php endif; ?>

        <form method="POST" action="/forgot-password">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="identifier">Username or Email</label>
                <input type="text" id="identifier" name="identifier" value="<?= old('identifier') ?>" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>

        <p class="auth-link"><a href="/login">Back to sign in</a></p>
    </div>
</div>
