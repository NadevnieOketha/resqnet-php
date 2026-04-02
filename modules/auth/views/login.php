<div class="auth-panel panel" role="form" aria-labelledby="loginHeading">
    <div class="auth-brand">
        <img src="<?= asset('img/logo.svg') ?>" alt="<?= e(config('app.name')) ?> logo">
        <span class="sr-only"><?= e(config('app.name')) ?></span>
    </div>

    <h1 id="loginHeading" class="auth-heading">Welcome back</h1>
    <p class="auth-subtitle">Use your username or email and password to continue.</p>

    <form method="POST" action="/login" id="loginForm" novalidate>
        <?= csrf_field() ?>

        <div class="form-field">
            <label for="identifier">Username or Email</label>
            <input id="identifier" name="identifier" type="text" class="input" value="<?= old('identifier') ?>" placeholder="Enter your username or email" autocomplete="username" required autofocus>
        </div>

        <div class="form-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" class="input" placeholder="Enter your password" autocomplete="current-password" required>
        </div>

        <div class="mb-4">
            <a href="/forgot-password" class="underline-link" style="font-size:var(--font-size-xs);color:var(--color-text-subtle);">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Log In</button>
    </form>

    <div class="auth-links">
        <span>Don't have an account? <a href="/register" class="underline-link">Sign up</a></span>
    </div>
</div>
