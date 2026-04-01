<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Login - <?= e(config('app.name')) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
        <link rel="stylesheet" href="<?= asset('auth-template/styles/core.css') ?>" />
        <script src="https://unpkg.com/lucide@latest" defer></script>
        <style>
            .auth-wrapper {
                width: 100%;
            }
            .forgot-link {
                font-size: var(--font-size-xs);
                color: var(--color-text-subtle);
            }
            .forgot-link:hover {
                color: var(--color-text);
            }
            .brand-inline {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.65rem;
                margin-bottom: var(--space-6);
            }
            .brand-inline img {
                height: 36px;
                width: auto;
                display: block;
            }
            .brand-inline span {
                font-weight: 600;
                font-size: 1.05rem;
                letter-spacing: 0.5px;
            }
            #loginHeading {
                text-align: center;
            }
            .signup-hint {
                font-size: var(--font-size-xs);
                color: var(--color-text-subtle);
                margin-top: var(--space-6);
            }
            .signup-hint a {
                font-weight: 600;
            }
            .signup-hint a:hover {
                color: var(--color-text);
            }
            .flash-block {
                margin-bottom: 14px;
            }
            .flash-error,
            .flash-info {
                border-radius: 10px;
                padding: 10px 12px;
                font-size: var(--font-size-xs);
            }
            .flash-error {
                background: #fef2f2;
                color: #991b1b;
                border: 1px solid #fecaca;
            }
            .flash-info {
                background: #eff6ff;
                color: #1e3a8a;
                border: 1px solid #bfdbfe;
            }
        </style>
    </head>
    <body>
        <div class="center-grid">
            <div
                class="auth-wrapper panel max-w-sm full-width"
                role="form"
                aria-labelledby="loginHeading"
            >
                <div class="brand-inline">
                    <img src="<?= asset('auth-template/assets/img/logo.svg') ?>" alt="ResQnet Logo" height="36" />
                    <span class="sr-only"><?= e(config('app.name')) ?></span>
                </div>
                <h1 id="loginHeading" class="h1">Welcome back</h1>

                <?php if ($error = get_flash('error')): ?>
                    <div class="flash-block flash-error"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if ($info = get_flash('info')): ?>
                    <div class="flash-block flash-info"><?= e($info) ?></div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="/login" novalidate>
                    <?= csrf_field() ?>
                    <div class="form-field">
                        <label for="username">Username</label>
                        <input
                            id="username"
                            name="identifier"
                            type="text"
                            class="input"
                            placeholder="Enter your username"
                            autocomplete="username"
                            value="<?= old('identifier') ?>"
                            required
                        />
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="input"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                            minlength="8"
                        />
                    </div>
                    <div class="mb-4">
                        <a href="/forgot-password" class="underline-link forgot-link">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                        Log In
                    </button>
                </form>
                <div class="signup-hint text-center">
                    <span>Don't have an account? </span><a href="/register" class="underline-link signup-link">Sign up</a>
                </div>
            </div>
        </div>
    </body>
</html>
