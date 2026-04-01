<div class="hero">
    <h1>Disaster readiness starts with shared information</h1>
    <p class="hero-subtitle"><?= e(config('app.name')) ?> connects early warnings with transparent post-disaster donation coordination.</p>
    <div class="hero-actions">
        <?php if (auth_check()): ?>
            <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
            <a href="/profile" class="btn btn-outline">Manage Profile</a>
        <?php else: ?>
            <a href="/login" class="btn btn-primary">Sign In</a>
            <a href="/register" class="btn btn-outline">Create Account</a>
        <?php endif; ?>
    </div>
</div>

<div class="features-grid">
    <div class="feature-card">
        <div class="feature-icon">🚨</div>
        <h3>Early Warning Ready</h3>
        <p>Role-aware platform foundation for verified alerts from Grama Niladhari and DMC teams.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🤝</div>
        <h3>Donation Workflow Ready</h3>
        <p>Authentication and approvals are prepared for post-disaster donation coordination modules.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🧭</div>
        <h3>Role-Based Operations</h3>
        <p>Guest access plus General Public, Volunteer, NGO, Grama Niladhari, and DMC role-specific access control.</p>
    </div>
</div>
