<nav class="sidebar-nav">
    <ul>
        <li><a href="/dashboard" class="<?= is_current_url('/dashboard') ? 'active' : '' ?>">📊 Dashboard</a></li>
        <li><a href="/warnings" class="<?= is_current_url('/warnings') ? 'active' : '' ?>">🚨 Public Warning Feed</a></li>
        <li><a href="/donations" class="<?= is_current_url('/donations') ? 'active' : '' ?>">🤝 Public Appeals</a></li>
    </ul>
    <div class="sidebar-section-label">System Operations</div>
    <ul>
        <li><a href="/dashboard/warnings" class="<?= is_current_url('/dashboard/warnings') ? 'active' : '' ?>">🚨 Manage Warnings</a></li>
        <li><a href="/dashboard/donations/manage" class="<?= is_current_url('/dashboard/donations/manage') ? 'active' : '' ?>">📦 Manage Appeals</a></li>
    </ul>
</nav>
