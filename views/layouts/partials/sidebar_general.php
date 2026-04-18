<nav class="nav">
    <a href="/dashboard" class="nav-item <?= is_current_url('/dashboard') ? 'active' : '' ?>" data-section="overview">
        <span class="icon" data-lucide="home"></span>
        <span>Overview</span>
    </a>
    <a href="/report-disaster" class="nav-item <?= is_current_url('/report-disaster') ? 'active' : '' ?>" data-section="report-disaster">
        <span class="icon" data-lucide="alert-triangle"></span>
        <span>Report a Disaster</span>
    </a>
    <a href="/safe-locations" class="nav-item <?= is_current_url('/safe-locations') ? 'active' : '' ?>" data-section="safe-locations">
        <span class="icon" data-lucide="map-pinned"></span>
        <span>Safe Locations</span>
    </a>
    <a href="/dashboard/forecast" class="nav-item <?= is_current_url('/dashboard/forecast') ? 'active' : '' ?>" data-section="forecast-dashboard">
        <span class="icon" data-lucide="cloud-rain"></span>
        <span>Forecast Dashboard</span>
    </a>
    <a href="/dashboard/forum" class="nav-item <?= is_current_url('/dashboard/forum') ? 'active' : '' ?>" data-section="forum">
        <span class="icon" data-lucide="message-square"></span>
        <span>Forum</span>
    </a>
    <a href="/donation-requests/create" class="nav-item <?= is_current_url('/donation-requests/create') ? 'active' : '' ?>" data-section="donation-requests">
        <span class="icon" data-lucide="heart-handshake"></span>
        <span>Request a Donation</span>
    </a>
    <a href="/make-donation" class="nav-item <?= is_current_url('/make-donation') ? 'active' : '' ?>" data-section="make-donation">
        <span class="icon" data-lucide="gift"></span>
        <span>Make a Donation</span>
    </a>
    <a href="/dashboard/my-donations" class="nav-item <?= is_current_url('/dashboard/my-donations') ? 'active' : '' ?>" data-section="my-donations">
        <span class="icon" data-lucide="package-search"></span>
        <span>My Donations</span>
    </a>
    <a href="/dashboard/become-volunteer" class="nav-item <?= is_current_url('/dashboard/become-volunteer') ? 'active' : '' ?>" data-section="become-volunteer">
        <span class="icon" data-lucide="users"></span>
        <span>Be a Volunteer</span>
    </a>
    <a href="/profile" class="nav-item <?= is_current_url('/profile') ? 'active' : '' ?>" data-section="profile-settings">
        <span class="icon" data-lucide="user"></span>
        <span>Profile Settings</span>
    </a>
</nav>
