<nav class="sidebar-nav">
    <ul>
        <li><a href="/dashboard" class="<?= is_current_url('/dashboard') ? 'active' : '' ?>">📊 Dashboard</a></li>
        <li><a href="/warnings" class="<?= is_current_url('/warnings') ? 'active' : '' ?>">🚨 Public Warning Feed</a></li>
    </ul>
    <div class="sidebar-section-label">Warning Operations</div>
    <ul>
        <li><a href="/dashboard/warnings" class="<?= is_current_url('/dashboard/warnings') ? 'active' : '' ?>">📋 Manage Warnings</a></li>
        <li><a href="/dashboard/warnings/create" class="<?= is_current_url('/dashboard/warnings/create') ? 'active' : '' ?>">➕ Issue Warning</a></li>
    </ul>
</nav>
