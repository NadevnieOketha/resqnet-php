<?php
$oldInput = $_SESSION['_old_input'] ?? [];
$selectedPreferences = $oldInput['preferences'] ?? ($oldInput['preferences[]'] ?? []);
$selectedSkills = $oldInput['skills'] ?? ($oldInput['skills[]'] ?? []);
$oldValue = static function (string $primary, ?string $fallback = null) use ($oldInput): string {
        $value = $oldInput[$primary] ?? ($fallback ? ($oldInput[$fallback] ?? '') : '');
        return e((string) $value);
};
if (!is_array($selectedPreferences)) {
        $selectedPreferences = [];
}
if (!is_array($selectedSkills)) {
        $selectedSkills = [];
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= e(config('app.name')) ?> Volunteer Registration</title>
        <link rel="stylesheet" href="<?= asset('auth-template/styles/core.css') ?>" />
        <link rel="stylesheet" href="<?= asset('auth-template/styles/dashboard.css') ?>" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <script src="https://unpkg.com/lucide@latest" defer></script>
        <style>
            .registration-wrapper { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:2.25rem clamp(1.25rem,2.5vw,2.75rem); box-shadow:var(--shadow-sm); max-width:1180px; }
            .form-layout { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:2.25rem 3rem; }
            .col-left, .col-right { display:flex; flex-direction:column; gap:1.25rem; }
            .field-group { display:flex; flex-direction:column; }
            .checkbox-group { display:flex; flex-direction:column; gap:0.65rem; margin-top:0.35rem; }
            .checkbox-group label { font-weight:400; font-size:0.75rem; display:flex; align-items:center; gap:0.5rem; margin:0; }
            .checkbox-group input[type=checkbox] { accent-color: var(--color-accent); width:14px; height:14px; }
            h2.section-title { font-size:0.85rem; letter-spacing:.25px; font-weight:600; margin:0 0 .4rem; }
            .account-section { display:flex; flex-direction:column; gap:1.25rem; margin-top:1.25rem; }
            .consent-row { display:flex; align-items:flex-start; gap:0.5rem; font-size:0.65rem; margin-top:1.5rem; }
            .consent-row input { margin-top:2px; accent-color: var(--color-accent); }
            .actions { margin-top:1.2rem; }
            .actions .btn-primary { width:100%; }
            .two-col-inline { display:grid; gap:1rem 1.25rem; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); }
            @media (max-width:860px){ .form-layout { grid-template-columns:1fr; } }
            .flash-error { color:#991b1b; font-size:var(--font-size-xs); margin:0 0 14px; border:1px solid #fecaca; background:#fee2e2; border-radius:10px; padding:10px 12px; }
        </style>
    </head>
    <body>
        <div class="layout">
            <aside class="sidebar" aria-label="Primary">
                <div class="brand">
                    <img class="logo-img" src="<?= asset('auth-template/assets/img/logo.svg') ?>" alt="ResQnet Logo" width="120" height="32" />
                    <span class="brand-name sr-only"><?= e(config('app.name')) ?></span>
                </div>
                <nav class="nav">
                    <button class="nav-item" data-section="overview"><span class="icon" data-lucide="home"></span><span>Overview</span></button>
                    <button class="nav-item" data-section="forecast"><span class="icon" data-lucide="line-chart"></span><span>Forecast Dashboard</span></button>
                    <button class="nav-item" data-section="make-donation"><span class="icon" data-lucide="gift"></span><span>Make a Donation</span></button>
                    <button class="nav-item" data-section="request-donation"><span class="icon" data-lucide="package"></span><span>Request a Donation</span></button>
                    <button class="nav-item" data-section="report-disaster"><span class="icon" data-lucide="alert-triangle"></span><span>Report a Disaster</span></button>
                    <button class="nav-item active" data-section="be-volunteer"><span class="icon" data-lucide="user-plus"></span><span>Be a Volunteer</span></button>
                    <button class="nav-item" data-section="forum"><span class="icon" data-lucide="message-circle"></span><span>Forum</span></button>
                    <button class="nav-item" data-section="profile-settings"><span class="icon" data-lucide="user"></span><span>Profile Settings</span></button>
                </nav>
                <div class="sidebar-footer">
                    <a class="logout" href="/login" aria-label="Back to login">Back to Login</a>
                </div>
            </aside>

            <header class="topbar">
                <div class="breadcrumb">General Public Dashboard / <span>Volunteer Registration</span></div>
                <div class="topbar-right">
                    <div class="hotline" role="button" tabindex="0" aria-label="Hotline 117"><span class="hotline-icon" data-lucide="phone"></span>Hotline: <strong>117</strong></div>
                    <div class="user-avatar" aria-label="User Menu" role="button"><img src="https://via.placeholder.com/40x40.png?text=U" alt="User Avatar" /></div>
                    <button class="menu-toggle" aria-label="Open Menu"><span data-lucide="menu"></span></button>
                </div>
            </header>

            <main class="content" id="mainContent" tabindex="-1">
                <h1>Volunteer Registration</h1>
                <div class="registration-wrapper">
                    <?php if ($error = get_flash('error')): ?>
                        <div class="flash-error"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form id="volunteerForm" method="POST" action="/register" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="role" value="volunteer" />

                        <div class="form-layout">
                            <div class="col-left">
                                <div class="field-group">
                                    <label for="fullName">Name</label>
                                    <input class="input" type="text" id="fullName" name="fullName" placeholder="Enter your full name" value="<?= $oldValue('fullName', 'full_name') ?>" required />
                                </div>
                                <div class="two-col-inline">
                                    <div class="field-group">
                                        <label for="age">Age</label>
                                        <input class="input" type="number" min="16" id="age" name="age" placeholder="Enter your age" value="<?= $oldValue('age') ?>" required />
                                    </div>
                                    <div class="field-group">
                                        <label for="gender">Gender</label>
                                        <select class="input" id="gender" name="gender" required>
                                            <option value="">Select your gender</option>
                                            <option value="Male" <?= old('gender') === 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= old('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
                                            <option value="Other" <?= old('gender') === 'Other' ? 'selected' : '' ?>>Other</option>
                                            <option value="Prefer not to say" <?= old('gender') === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="field-group">
                                    <label for="contactNo">Contact No</label>
                                    <input class="input" type="tel" id="contactNo" name="contactNo" placeholder="Enter your contact number" value="<?= $oldValue('contactNo', 'contact_no') ?>" required />
                                </div>
                                <h2 class="section-title" style="margin-top:0.75rem;">Address</h2>
                                <div class="field-group">
                                    <label for="houseNo">House No</label>
                                    <input class="input" type="text" id="houseNo" name="houseNo" placeholder="Enter your house number" value="<?= $oldValue('houseNo', 'house_no') ?>" />
                                </div>
                                <div class="field-group">
                                    <label for="street">Street</label>
                                    <input class="input" type="text" id="street" name="street" placeholder="Enter your street name" value="<?= $oldValue('street') ?>" />
                                </div>
                                <div class="field-group">
                                    <label for="city">City</label>
                                    <input class="input" type="text" id="city" name="city" placeholder="Enter your city" value="<?= $oldValue('city') ?>" />
                                </div>
                                <div class="field-group">
                                    <label for="district">District</label>
                                    <select class="input" id="district" name="district">
                                        <?php $district = (string) ($oldInput['district'] ?? ''); ?>
                                        <option value="" <?= $district === '' ? 'selected' : '' ?>>Select your district</option>
                                        <option value="Colombo" <?= $district === 'Colombo' ? 'selected' : '' ?>>Colombo</option>
                                        <option value="Gampaha" <?= $district === 'Gampaha' ? 'selected' : '' ?>>Gampaha</option>
                                        <option value="Kalutara" <?= $district === 'Kalutara' ? 'selected' : '' ?>>Kalutara</option>
                                        <option value="Kandy" <?= $district === 'Kandy' ? 'selected' : '' ?>>Kandy</option>
                                        <option value="Galle" <?= $district === 'Galle' ? 'selected' : '' ?>>Galle</option>
                                    </select>
                                </div>
                                <div class="field-group">
                                    <label for="gnDivision">Grama Niladhari Division</label>
                                    <input class="input" type="text" id="gnDivision" name="gnDivision" placeholder="Enter your Grama Niladhari Division" value="<?= $oldValue('gnDivision', 'gn_division') ?>" />
                                </div>
                            </div>

                            <div class="col-right">
                                <div>
                                    <h2 class="section-title">Volunteer Preferences</h2>
                                    <div class="checkbox-group" id="preferencesGroup">
                                        <?php foreach (['Search & Rescue', 'Medical Aid', 'Logistics Support', 'Technical Support', 'Shelter Management', 'Food Preparation & Distribution', 'Childcare Support', 'Elderly Assistance'] as $pref): ?>
                                            <label><input type="checkbox" name="preferences[]" value="<?= e($pref) ?>" <?= in_array($pref, $selectedPreferences, true) ? 'checked' : '' ?> /> <?= e($pref) ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div style="margin-top:1.25rem;">
                                    <h2 class="section-title">Specialized Skills</h2>
                                    <div class="checkbox-group" id="skillsGroup">
                                        <?php foreach (['First Aid Certified', 'Medical Professional', 'Firefighting', 'Swimming / Lifesaving', 'Rescue & Handling', 'Disaster Management Training'] as $skill): ?>
                                            <label><input type="checkbox" name="skills[]" value="<?= e($skill) ?>" <?= in_array($skill, $selectedSkills, true) ? 'checked' : '' ?> /> <?= e($skill) ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="account-section">
                                    <h2 class="section-title">Account Details</h2>
                                    <div class="field-group">
                                        <label for="username">Username</label>
                                        <input class="input" type="text" id="username" name="username" placeholder="Choose a username" value="<?= $oldValue('username') ?>" required />
                                    </div>
                                    <div class="field-group">
                                        <label for="email">Email</label>
                                        <input class="input" type="email" id="email" name="email" placeholder="Enter your email" value="<?= $oldValue('email') ?>" required />
                                    </div>
                                    <div class="field-group">
                                        <label for="password">Password</label>
                                        <input class="input" type="password" id="password" name="password" placeholder="Create a password" minlength="8" required />
                                        <div class="form-help">Minimum 8 characters.</div>
                                    </div>
                                    <div class="field-group">
                                        <label for="confirmPassword">Confirm Password</label>
                                        <input class="input" type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="consent-row">
                            <input type="checkbox" id="consent" name="consent" value="1" <?= old('consent') ? 'checked' : '' ?> required />
                            <label for="consent">I agree to provide emergency contact information and understand my responsibilities as a volunteer.</label>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn btn-primary" style="font-weight:600;">Register</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        </script>
    </body>
</html>
