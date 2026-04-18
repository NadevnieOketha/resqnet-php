<?php
$role = (string) (user_role() ?? '');
$sidebarFile = BASE_PATH . '/views/layouts/partials/sidebar_' . $role . '.php';
if (!file_exists($sidebarFile)) {
    $sidebarFile = BASE_PATH . '/views/layouts/partials/sidebar_general.php';
}

$roleTitle = role_label($role) . ' Dashboard';
$crumb = $breadcrumb ?? 'Overview';
$displayName = auth_display_name();
$avatarInitial = strtoupper(substr($displayName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($roleTitle) ?> - <?= e(config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo.svg') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>
<div class="layout">
    <aside class="sidebar" aria-label="Primary navigation">
        <div class="brand">
            <a href="/" aria-label="<?= e(config('app.name')) ?> home">
                <img class="logo-img" src="<?= asset('img/logo.svg') ?>" alt="<?= e(config('app.name')) ?> logo" width="120" height="32">
            </a>
            <span class="brand-name sr-only"><?= e(config('app.name')) ?></span>
        </div>

        <?php require $sidebarFile; ?>

        <div class="sidebar-footer">
            <a href="/logout" class="logout" aria-label="Logout">↩ Logout</a>
        </div>
    </aside>

    <header class="topbar">
        <div class="breadcrumb"><?= e($roleTitle) ?> / <span><?= e($crumb) ?></span></div>
        <div class="topbar-right">
            <a href="tel:117" class="hotline" aria-label="Call DMC Hotline 117">
                <span class="hotline-icon" data-lucide="phone"></span>
                Hotline: <strong>117</strong>
            </a>
            <div class="user-avatar" aria-label="Current user">
                <span style="font-weight:700;"><?= e($avatarInitial !== '' ? $avatarInitial : 'U') ?></span>
            </div>

        </div>
    </header>

    <main class="content" id="mainContent" tabindex="-1">
        <?php
        $error = get_flash('error');
        $warning = get_flash('warning');
        $success = get_flash('success');
        ?>

        <?php if ($error || $warning || $success): ?>
            <div class="flash-stack" style="margin-bottom:0;">
                <?php if ($error): ?><div class="app-flash app-flash-error"><?= e($error) ?></div><?php endif; ?>
                <?php if ($warning): ?><div class="app-flash app-flash-warning"><?= e($warning) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="app-flash app-flash-success"><?= e($success) ?></div><?php endif; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }

        const toggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                const open = sidebar.classList.toggle('open');
                document.body.classList.toggle('menu-open', open);
                toggle.setAttribute('aria-expanded', String(open));
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    document.body.classList.remove('menu-open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
</script>
</body>
</html>
