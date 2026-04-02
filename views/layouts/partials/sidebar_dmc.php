<nav class="nav">
    <a href="/dashboard" class="nav-item <?= is_current_url('/dashboard') ? 'active' : '' ?>" data-section="overview">
        <span class="icon" data-lucide="home"></span>
        <span>Overview</span>
    </a>
    <a href="/dashboard/reports" class="nav-item <?= is_current_url('/dashboard/reports') ? 'active' : '' ?>" data-section="disaster-reports">
        <span class="icon" data-lucide="file-text"></span>
        <span>Disaster Reports</span>
    </a>
    <a href="/dashboard/admin/pending" class="nav-item <?= is_current_url('/dashboard/admin/pending') ? 'active' : '' ?>" data-section="approvals">
        <span class="icon" data-lucide="users"></span>
        <span>Pending Approvals</span>
    </a>
    <a href="/dashboard/admin/grama-niladhari/create" class="nav-item <?= is_current_url('/dashboard/admin/grama-niladhari/create') ? 'active' : '' ?>" data-section="gn-registry">
        <span class="icon" data-lucide="user-plus"></span>
        <span>Create GN Account</span>
    </a>
    <a href="/profile" class="nav-item <?= is_current_url('/profile') ? 'active' : '' ?>" data-section="profile-settings">
        <span class="icon" data-lucide="user"></span>
        <span>Profile Settings</span>
    </a>
</nav>
