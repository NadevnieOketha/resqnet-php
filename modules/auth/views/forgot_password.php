<div class="auth-panel panel" role="form" aria-labelledby="forgotHeading">
    <div class="auth-brand">
        <img src="<?= asset('img/logo.svg') ?>" alt="<?= e(config('app.name')) ?> logo">
        <span class="sr-only"><?= e(config('app.name')) ?></span>
    </div>

    <h1 id="forgotHeading" class="auth-heading">Forgot password</h1>
    <p class="auth-subtitle">Enter your username or email to receive a reset link.</p>

    <form method="POST" action="/forgot-password" novalidate>
        <?= csrf_field() ?>

        <div class="form-field">
            <label for="identifier">Username or Email</label>
            <input id="identifier" name="identifier" type="text" class="input" value="<?= old('identifier') ?>" placeholder="Enter your username or email" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
    </form>

    <div class="auth-links">
        <a href="/login" class="underline-link">Back to login</a>
    </div>
</div>
