<div class="auth-container">
    <div class="auth-card" style="max-width: 460px;">
        <h2>Forgot Password</h2>
        <p class="auth-subtitle">Enter your username or email to generate a reset link.</p>

        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($info = get_flash('info')): ?>
            <div class="alert alert-info" style="word-break: break-word;"><?= e($info) ?></div>
        <?php endif; ?>

        <form method="POST" action="/forgot-password">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="identifier">Username or Email</label>
                <input type="text" id="identifier" name="identifier" value="<?= old('identifier') ?>" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Generate Reset Link</button>
        </form>

        <p class="auth-link">
            Back to <a href="/login">Sign in</a>
        </p>
    </div>
</div>
