<?php
$hideHeader = !empty($hide_header);
$pageTitle = $page_title ?? config('app.name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= e(config('app.name')) ?></title>
    <meta name="description" content="resqnet - Disaster early warning and post-disaster donation management system">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo.svg') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>
<?php if (!$hideHeader): ?>
    <header class="site-header" role="banner">
        <div class="site-header__inner">
            <a href="/" class="brand-inline" aria-label="<?= e(config('app.name')) ?> home">
                <img src="<?= asset('img/logo.svg') ?>" alt="<?= e(config('app.name')) ?> logo">
                <span><?= e(config('app.name')) ?></span>
            </a>
            <nav class="primary-nav" aria-label="Primary navigation">
                <ul>
                    <li><a href="/" <?= is_current_url('/') ? 'aria-current="page"' : '' ?>>Home</a></li>
                    <li><a href="/safe-locations" <?= is_current_url('/safe-locations') ? 'aria-current="page"' : '' ?>>Safe Locations</a></li>
                    <li><a href="/forecast" <?= is_current_url('/forecast') ? 'aria-current="page"' : '' ?>>Forecast</a></li>
                    <li><a href="/make-donation" <?= is_current_url('/make-donation') ? 'aria-current="page"' : '' ?>>Make Donation</a></li>
                    <?php if (auth_check()): ?>
                        <li><a href="/dashboard" <?= is_current_url('/dashboard') ? 'aria-current="page"' : '' ?>>Dashboard</a></li>
                        <li><a href="/profile" <?= is_current_url('/profile') ? 'aria-current="page"' : '' ?>>Profile</a></li>
                    <?php else: ?>
                        <li><a href="/register?role=volunteer">Become a Volunteer</a></li>
                        <li><a href="/register?role=ngo">Join as NGO</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if (auth_check()): ?>
                    <a href="/logout" class="btn">Logout</a>
                    <a href="/forecast" class="btn">Forecast</a>
                    <a href="/dashboard" class="btn btn-primary">Open Dashboard</a>
                <?php else: ?>
                    <a href="/register" class="btn" <?= is_current_url('/register') ? 'aria-current="page"' : '' ?>>Sign Up</a>
                    <a href="/forecast" class="btn">Forecast</a>
                    <a href="/login" class="btn btn-primary" <?= is_current_url('/login') ? 'aria-current="page"' : '' ?>>Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
<?php endif; ?>

<main class="app-main <?= $hideHeader ? 'auth-shell' : '' ?>">
    <div class="container">
        <?php
        $error = get_flash('error');
        $warning = get_flash('warning');
        $success = get_flash('success');
        ?>

        <?php if ($error || $warning || $success): ?>
            <div class="flash-stack">
                <?php if ($error): ?><div class="app-flash app-flash-error"><?= e($error) ?></div><?php endif; ?>
                <?php if ($warning): ?><div class="app-flash app-flash-warning"><?= e($warning) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="app-flash app-flash-success"><?= e($success) ?></div><?php endif; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
</body>
</html>
