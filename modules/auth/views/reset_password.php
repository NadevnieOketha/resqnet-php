<div class="auth-panel panel" role="form" aria-labelledby="resetHeading">
    <div class="auth-brand">
        <a href="/" aria-label="<?= e(config('app.name')) ?> home">
            <img src="<?= asset('img/logo.svg') ?>" alt="<?= e(config('app.name')) ?> logo">
        </a>
        <span class="sr-only"><?= e(config('app.name')) ?></span>
    </div>

    <h1 id="resetHeading" class="auth-heading">Reset password</h1>

    <?php if (empty($token_valid)): ?>
        <p class="auth-subtitle">This reset link is invalid, expired, or has been replaced by a newer link.</p>
        <div class="auth-links">
            <a href="/forgot-password" class="underline-link">Request a new reset link</a>
        </div>
    <?php else: ?>
        <p class="auth-subtitle">Set a new password for your account.</p>

        <form method="POST" action="/reset-password" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="form-field">
                <label for="password">New Password</label>
                <input id="password" name="password" type="password" class="input" minlength="6" placeholder="Enter new password" required>
            </div>

            <div class="form-field">
                <label for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" minlength="6" placeholder="Confirm new password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Update Password</button>
        </form>

        <div class="auth-links">
            <a href="/login" class="underline-link">Back to login</a>
        </div>
    <?php endif; ?>
</div>
