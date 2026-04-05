<nav class="nav">
    <a href="/dashboard" class="nav-item <?= is_current_url('/dashboard') ? 'active' : '' ?>" data-section="overview">
        <span class="icon" data-lucide="home"></span>
        <span>Overview</span>
    </a>
    <a href="/dashboard/donation-requirements" class="nav-item <?= is_current_url('/dashboard/donation-requirements') ? 'active' : '' ?>" data-section="donation-requirements">
        <span class="icon" data-lucide="package-search"></span>
        <span>Donation Requirements</span>
    </a>
    <a href="/profile" class="nav-item <?= is_current_url('/profile') ? 'active' : '' ?>" data-section="profile-settings">
        <span class="icon" data-lucide="user"></span>
        <span>Profile Settings</span>
    </a>
</nav>
