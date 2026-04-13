<nav class="nav">
    <a href="/dashboard" class="nav-item <?= is_current_url('/dashboard') ? 'active' : '' ?>" data-section="overview">
        <span class="icon" data-lucide="home"></span>
        <span>Overview</span>
    </a>
    <a href="/dashboard/collection-points" class="nav-item <?= is_current_url('/dashboard/collection-points') ? 'active' : '' ?>" data-section="collection-points">
        <span class="icon" data-lucide="map-pin-house"></span>
        <span>Manage Collection Points</span>
    </a>
    <a href="/dashboard/donation-requirements" class="nav-item <?= is_current_url('/dashboard/donation-requirements') ? 'active' : '' ?>" data-section="donation-requirements">
        <span class="icon" data-lucide="package-search"></span>
        <span>Donation Requirements</span>
    </a>
    <a href="/dashboard/ngo/donations" class="nav-item <?= is_current_url('/dashboard/ngo/donations') ? 'active' : '' ?>" data-section="received-donations">
        <span class="icon" data-lucide="package-check"></span>
        <span>Donations Received</span>
    </a>
    <a href="/dashboard/ngo/inventory" class="nav-item <?= is_current_url('/dashboard/ngo/inventory') ? 'active' : '' ?>" data-section="inventory-management">
        <span class="icon" data-lucide="boxes"></span>
        <span>Inventory Management</span>
    </a>
    <a href="/dashboard/forecast" class="nav-item <?= is_current_url('/dashboard/forecast') ? 'active' : '' ?>" data-section="forecast-dashboard">
        <span class="icon" data-lucide="cloud-rain"></span>
        <span>Forecast Dashboard</span>
    </a>
    <a href="/profile" class="nav-item <?= is_current_url('/profile') ? 'active' : '' ?>" data-section="profile-settings">
        <span class="icon" data-lucide="user"></span>
        <span>Profile Settings</span>
    </a>
</nav>
