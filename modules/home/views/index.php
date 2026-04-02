<section class="panel" style="padding:clamp(1.8rem,3vw,2.4rem);">
    <h1 class="mb-3">Disaster readiness starts with shared information</h1>
    <p class="page-subheading" style="margin-bottom:1.2rem;max-width:820px;">
        <?= e(config('app.name')) ?> connects early warning operations and post-disaster donation coordination through a role-based platform for General Public, Volunteers, NGOs, Grama Niladhari officers, and DMC administrators.
    </p>

    <div class="form-actions">
        <?php if (auth_check()): ?>
            <a href="/dashboard" class="btn btn-primary">Open Dashboard</a>
            <a href="/profile" class="btn">Manage Profile</a>
        <?php else: ?>
            <a href="/login" class="btn btn-primary">Log In</a>
            <a href="/register" class="btn">Sign Up</a>
        <?php endif; ?>
    </div>
</section>

<section class="kpi-grid" style="margin-top:1rem;">
    <article class="kpi-card">
        <div class="label">Authentication Model</div>
        <div class="value">5 Roles</div>
        <p class="muted mb-0" style="font-size:0.75rem;">General, Volunteer, NGO, Grama Niladhari, DMC</p>
    </article>
    <article class="kpi-card">
        <div class="label">Security</div>
        <div class="value">CSRF + Hash</div>
        <p class="muted mb-0" style="font-size:0.75rem;">Server-side session auth with password hashing</p>
    </article>
    <article class="kpi-card">
        <div class="label">Approval Flow</div>
        <div class="value">DMC Gate</div>
        <p class="muted mb-0" style="font-size:0.75rem;">Volunteer and NGO activation managed by DMC</p>
    </article>
</section>
