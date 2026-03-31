<nav class="sidebar-nav">
    <ul>
        <li><a href="/dashboard" class="<?= is_current_url('/dashboard') ? 'active' : '' ?>">📊 Dashboard</a></li>
        <li><a href="/warnings" class="<?= is_current_url('/warnings') ? 'active' : '' ?>">🚨 Early Warnings</a></li>
        <li><a href="/donations" class="<?= is_current_url('/donations') ? 'active' : '' ?>">🤝 Public Appeals</a></li>
    </ul>
    <div class="sidebar-section-label">Donation Management</div>
    <ul>
        <li><a href="/dashboard/donations/manage" class="<?= is_current_url('/dashboard/donations/manage') ? 'active' : '' ?>">📋 Manage Appeals</a></li>
        <li><a href="/dashboard/donations/create" class="<?= is_current_url('/dashboard/donations/create') ? 'active' : '' ?>">➕ New Appeal</a></li>
    </ul>
</nav>
