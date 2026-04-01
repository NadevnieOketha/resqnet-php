<?php
$oldInput = $_SESSION['_old_input'] ?? [];
$oldValue = static function (string $primary, ?string $fallback = null) use ($oldInput): string {
        $value = $oldInput[$primary] ?? ($fallback ? ($oldInput[$fallback] ?? '') : '');
        return e((string) $value);
};
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= e(config('app.name')) ?> - Organization Signup</title>
        <link rel="stylesheet" href="<?= asset('auth-template/styles/core.css') ?>" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <style>
            body { background:#fff; }
            .site-header { position:sticky; top:0; z-index:40; }
            .auth-actions { margin-left:auto; display:flex; gap:.75rem; }
            .main-wrapper { max-width:1080px; margin:0 auto; padding:3.5rem clamp(1rem,3vw,2.5rem) 4rem; }
            h1 { text-align:center; margin:0 0 2.8rem; font-size:clamp(1.9rem,3vw,2.1rem); }
            form.org-form { display:flex; flex-direction:column; gap:2.2rem; }
            .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:1.4rem 2.5rem; }
            .grid .wide { grid-column:1/-1; }
            .form-field { margin:0; display:flex; flex-direction:column; }
            .btn-primary { --btn-bg: var(--color-accent); --btn-border: var(--color-accent); width:100%; font-weight:600; padding:16px 24px; font-size:0.85rem; }
            .flash-error { font-size:var(--font-size-xs); color:#991b1b; margin:0 0 14px; border:1px solid #fecaca; background:#fee2e2; border-radius:10px; padding:10px 12px; }
            @media (max-width:640px){ .grid { grid-template-columns:1fr; } }
        </style>
    </head>
    <body>
        <header class="site-header">
            <div class="site-header__inner">
                <a href="/" class="brand-inline">
                    <img src="<?= asset('auth-template/assets/img/logo.svg') ?>" alt="ResQnet Logo" />
                    <span><?= e(config('app.name')) ?></span>
                </a>
                <nav class="primary-nav" aria-label="Main"></nav>
                <div class="auth-actions">
                    <a class="btn" href="/login">Login</a>
                    <a class="btn btn-primary" href="/register">Sign Up</a>
                </div>
            </div>
        </header>

        <main class="main-wrapper" id="mainContent" tabindex="-1">
            <h1>Sign up your organization</h1>

            <?php if ($error = get_flash('error')): ?>
                <div class="flash-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form id="orgPublicSignupForm" class="org-form" method="POST" action="/register" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="role" value="ngo" />

                <div class="grid">
                    <div class="form-field wide">
                        <label for="orgName">Organization Name</label>
                        <input class="input" id="orgName" name="orgName" placeholder="Enter organization name" value="<?= $oldValue('orgName', 'org_name') ?>" required />
                    </div>
                    <div class="form-field">
                        <label for="regNo">Registration No.</label>
                        <input class="input" id="regNo" name="regNo" placeholder="Enter registration number" value="<?= $oldValue('regNo', 'registration_no') ?>" required />
                    </div>
                    <div class="form-field">
                        <label for="years">Years of Operation</label>
                        <input class="input" type="number" min="0" id="years" name="years" placeholder="Enter years of operation" value="<?= $oldValue('years', 'years_of_operation') ?>" />
                    </div>
                    <div class="form-field">
                        <label for="contactPerson">Name</label>
                        <input class="input" id="contactPerson" name="contactPerson" placeholder="Enter contact person's name" value="<?= $oldValue('contactPerson', 'contact_person') ?>" required />
                    </div>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input class="input" type="email" id="email" name="email" placeholder="Enter contact person's email" value="<?= $oldValue('email') ?>" required />
                    </div>
                    <div class="form-field">
                        <label for="telephone">Telephone</label>
                        <input class="input" id="telephone" name="telephone" placeholder="Enter contact person's telephone" value="<?= $oldValue('telephone') ?>" required />
                    </div>
                    <div class="form-field">
                        <label for="address">Address</label>
                        <input class="input" id="address" name="address" placeholder="Enter organization address" value="<?= $oldValue('address') ?>" />
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input class="input" type="password" id="password" name="password" placeholder="Enter password" minlength="8" required />
                        <div class="form-help">Minimum 8 characters.</div>
                    </div>
                    <div class="form-field">
                        <label for="confirmPassword">Confirm Password</label>
                        <input class="input" type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required />
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </div>
            </form>
        </main>
    </body>
</html>
