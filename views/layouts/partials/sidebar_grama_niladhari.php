<nav class="nav">
    <a href="/dashboard" class="nav-item <?= is_current_url('/dashboard') ? 'active' : '' ?>" data-section="overview">
        <span class="icon" data-lucide="home"></span>
        <span>Overview</span>
    </a>
    <a href="/report-disaster" class="nav-item <?= is_current_url('/report-disaster') ? 'active' : '' ?>" data-section="report-disaster">
        <span class="icon" data-lucide="triangle-alert"></span>
        <span>Report a Disaster</span>
    </a>
    <a href="/dashboard/safe-locations" class="nav-item <?= is_current_url('/dashboard/safe-locations') ? 'active' : '' ?>" data-section="safe-locations">
        <span class="icon" data-lucide="house"></span>
        <span>Safe Locations</span>
    </a>
    <a href="/profile" class="nav-item <?= is_current_url('/profile') ? 'active' : '' ?>" data-section="profile-settings">
        <span class="icon" data-lucide="user"></span>
        <span>Profile Settings</span>
    </a>
</nav>
