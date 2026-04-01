<?php
$oldInput = $_SESSION['_old_input'] ?? [];
$oldValue = static function (string $primary, ?string $fallback = null) use ($oldInput): string {
        $value = $oldInput[$primary] ?? ($fallback ? ($oldInput[$fallback] ?? '') : '');
        return e((string) $value);
};

$district = (string) ($oldInput['district'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Sign Up - <?= e(config('app.name')) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
        <link rel="stylesheet" href="<?= asset('auth-template/styles/core.css') ?>" />
        <script src="https://unpkg.com/lucide@latest" defer></script>
        <style>
            .signup-container {
                max-width: var(--layout-max-width);
                margin: 0 auto;
                padding: 0 var(--space-4) 3.5rem;
            }
            @media (max-width: 680px) {
                .signup-container {
                    padding: 0 var(--space-3) 3rem;
                }
            }

            .signup-heading {
                margin: 0 0 var(--space-2);
                font-size: clamp(1.55rem, 2.3vw, 1.9rem);
                font-weight: 600;
                line-height: 1.15;
            }
            .signup-subheading {
                margin: 0 0 var(--space-6);
                font-size: var(--font-size-sm);
                color: var(--color-text-subtle);
                max-width: 640px;
            }

            .form-sections {
                display: grid;
                gap: var(--space-8);
                grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
                margin-bottom: var(--space-6);
            }
            @media (max-width: 760px) {
                .form-sections {
                    grid-template-columns: 1fr;
                    gap: var(--space-6);
                }
            }

            .form-section h2 {
                font-size: var(--font-size-sm);
                letter-spacing: 0.5px;
                font-weight: 600;
                margin: 0 0 var(--space-4);
            }

            .form-actions {
                margin-top: var(--space-2);
                display: flex;
                gap: var(--space-4);
                align-items: center;
                flex-wrap: wrap;
            }
            .form-actions .btn {
                flex: 1;
                min-width: 180px;
            }
            @media (max-width: 600px) {
                .form-actions {
                    flex-direction: column;
                    align-items: stretch;
                }
                .form-actions .btn {
                    width: 100%;
                    flex: unset;
                }
            }

            .role-badge {
                background: var(--color-surface-alt-2);
                border: 1px solid var(--color-border);
                padding: 4px 10px;
                border-radius: var(--radius-pill);
                font-size: var(--font-size-xs);
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                margin-left: 0.5rem;
            }

            .flash-error {
                background: #fee2e2;
                border: 1px solid #fecaca;
                color: #991b1b;
                border-radius: 10px;
                padding: 10px 12px;
                margin-bottom: 16px;
                font-size: var(--font-size-xs);
            }
        </style>
    </head>
    <body>
        <header class="site-header" role="banner">
            <div class="site-header__inner">
                <a href="/" class="brand-inline" aria-label="ResQnet home">
                    <img src="<?= asset('auth-template/assets/img/logo.svg') ?>" alt="ResQnet logo" />
                    <span class="sr-only"><?= e(config('app.name')) ?></span>
                </a>
                <nav class="primary-nav" aria-label="Primary navigation">
                    <ul>
                        <li><a href="/warnings">Forecast Dashboard</a></li>
                        <li><a href="#">Community Forum</a></li>
                        <li><a href="/register/volunteer">Become a Volunteer</a></li>
                        <li><a href="/register/ngo">Join as a NGO</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <a href="/register" class="btn" aria-current="page">Sign Up</a>
                    <a href="/login" class="btn btn-primary">Login</a>
                </div>
            </div>
        </header>

        <main class="signup-container" id="mainContent" tabindex="-1">
            <h1 class="signup-heading">
                Sign up<span class="role-badge">General User</span>
            </h1>
            <p class="signup-subheading">
                Create your account to access disaster response resources
            </p>

            <?php if ($error = get_flash('error')): ?>
                <div class="flash-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form id="signupForm" method="POST" action="/register" novalidate autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="role" value="general" />

                <div class="form-sections">
                    <section class="form-section" aria-labelledby="personalInfoHeading">
                        <h2 id="personalInfoHeading">Personal information</h2>
                        <div class="form-field">
                            <label for="fullName">Name</label>
                            <input id="fullName" name="fullName" type="text" class="input" placeholder="Enter your full name" value="<?= $oldValue('fullName', 'full_name') ?>" required />
                        </div>
                        <div class="form-field">
                            <label for="contactNo">Contact No</label>
                            <input id="contactNo" name="contactNo" type="tel" class="input" placeholder="Enter your contact number" value="<?= $oldValue('contactNo', 'contact_no') ?>" required pattern="[0-9+\-() ]{7,}" />
                        </div>
                        <div class="form-field">
                            <label for="username">Username</label>
                            <input id="username" name="username" type="text" class="input" placeholder="Choose a username" minlength="3" value="<?= $oldValue('username') ?>" required />
                        </div>
                        <div class="form-field">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" class="input" placeholder="Create a password" minlength="8" required autocomplete="new-password" />
                        </div>
                        <div class="form-field">
                            <label for="confirmPassword">Confirm Password</label>
                            <input id="confirmPassword" name="confirmPassword" type="password" class="input" placeholder="Confirm your password" minlength="8" required autocomplete="new-password" />
                        </div>
                        <div class="form-field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" class="input" placeholder="Enter your email address" value="<?= $oldValue('email') ?>" required />
                        </div>
                    </section>

                    <section class="form-section" aria-labelledby="addressInfoHeading">
                        <h2 id="addressInfoHeading">Address</h2>
                        <div class="form-field">
                            <label for="houseNo">House No</label>
                            <input id="houseNo" name="houseNo" type="text" class="input" placeholder="Enter your house number" value="<?= $oldValue('houseNo', 'house_no') ?>" required />
                        </div>
                        <div class="form-field">
                            <label for="street">Street</label>
                            <input id="street" name="street" type="text" class="input" placeholder="Enter your street name" value="<?= $oldValue('street') ?>" required />
                        </div>
                        <div class="form-field">
                            <label for="city">City</label>
                            <input id="city" name="city" type="text" class="input" placeholder="Enter your city" value="<?= $oldValue('city') ?>" required />
                        </div>
                        <div class="form-field">
                            <label for="district">District</label>
                            <select id="district" name="district" class="input" required>
                                <option value="" <?= $district === '' ? 'selected' : '' ?>>Select your district</option>
                                <option value="Colombo" <?= $district === 'Colombo' ? 'selected' : '' ?>>Colombo</option>
                                <option value="Gampaha" <?= $district === 'Gampaha' ? 'selected' : '' ?>>Gampaha</option>
                                <option value="Kalutara" <?= $district === 'Kalutara' ? 'selected' : '' ?>>Kalutara</option>
                                <option value="Kandy" <?= $district === 'Kandy' ? 'selected' : '' ?>>Kandy</option>
                                <option value="Galle" <?= $district === 'Galle' ? 'selected' : '' ?>>Galle</option>
                                <option value="Matara" <?= $district === 'Matara' ? 'selected' : '' ?>>Matara</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="gnDivision">Grama Niladari Division</label>
                            <input id="gnDivision" name="gnDivision" type="text" class="input" placeholder="Enter your Grama Niladari Division" value="<?= $oldValue('gnDivision', 'gn_division') ?>" required />
                        </div>
                        <div class="form-field">
                            <label for="sms_alert" style="display:flex;gap:8px;align-items:center;">
                                <input type="checkbox" id="sms_alert" name="sms_alert" value="1" <?= old('sms_alert') ? 'checked' : '' ?> />
                                <span>Send me SMS alerts for severe warnings.</span>
                            </label>
                        </div>
                    </section>
                </div>

                <div class="form-actions">
                    <a href="/register" class="btn" id="backRolesBtn" aria-label="Back to role selection">Back</a>
                    <button type="submit" class="btn btn-primary" id="signupBtn">Sign Up</button>
                </div>
            </form>
        </main>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                if (window.lucide) window.lucide.createIcons();
            });
        </script>
    </body>
</html>
