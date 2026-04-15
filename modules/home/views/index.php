<?php
$isAuthenticated = auth_check();
$appName = (string) config('app.name');

$dashboardHref = $isAuthenticated ? '/dashboard' : '/login';
$loginHref = '/login';
$registerHref = '/register';
?>

<div class="landing-page">
    <section class="hero">
        <div class="hero__container">
            <div class="hero__content">
                <div class="hero__badge">
                    <span class="icon" data-lucide="shield-check"></span>
                    <span>Trusted Disaster Management Platform</span>
                </div>
                <h1 class="hero__title">
                    Saving Lives Through<br>Smart Disaster Response
                </h1>
                <p class="hero__subtitle">
                    <?= e($appName) ?> connects the general public, volunteers, NGOs, Grama Niladhari officers, and DMC
                    teams in real-time to coordinate early warnings, disaster reporting, and post-disaster donation
                    management.
                </p>
                <div class="hero__actions">
                    <?php if ($isAuthenticated): ?>
                        <a href="/dashboard" class="btn btn-primary btn-large">
                            Open Dashboard
                            <span data-lucide="arrow-right" style="width:18px;height:18px"></span>
                        </a>
                        <a href="/make-donation" class="btn btn-large">Make Donation</a>
                    <?php else: ?>
                        <a href="/register?role=volunteer" class="btn btn-primary btn-large">
                            Join as Volunteer
                            <span data-lucide="arrow-right" style="width:18px;height:18px"></span>
                        </a>
                        <a href="/register" class="btn btn-large">Choose Your Role</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero__image">
                <div class="hero__image-wrapper">
                    <img
                        src="https://images.unsplash.com/photo-1593113598332-cd288d649433?w=1200&q=80"
                        alt="Emergency response team coordinating disaster relief"
                        loading="eager"
                    >
                </div>
                <div class="hero__image-accent"></div>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="stats__container">
            <div class="stat">
                <div class="stat__value">24/7</div>
                <div class="stat__label">Emergency Support</div>
            </div>
            <div class="stat">
                <div class="stat__value">5</div>
                <div class="stat__label">Operational Roles</div>
            </div>
            <div class="stat">
                <div class="stat__value">Real-Time</div>
                <div class="stat__label">Alert Coordination</div>
            </div>
            <div class="stat">
                <div class="stat__value">Islandwide</div>
                <div class="stat__label">Response Coverage</div>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="features__container">
            <div class="section-header">
                <h2 class="section-title">Complete Disaster Management Solution</h2>
                <p class="section-subtitle">
                    Built for preparedness, rapid response, and transparent relief distribution across communities.
                </p>
            </div>
            <div class="features__grid">
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="line-chart"></span></div>
                    <h3 class="feature-card__title">Early Warning Insights</h3>
                    <p class="feature-card__desc">
                        Monitor risk indicators and weather-linked warning signals to prepare communities before impact.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="alert-triangle"></span></div>
                    <h3 class="feature-card__title">Disaster Reporting</h3>
                    <p class="feature-card__desc">
                        Submit incident reports with verification flow for fast action by Grama Niladhari and DMC teams.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="users"></span></div>
                    <h3 class="feature-card__title">Volunteer Coordination</h3>
                    <p class="feature-card__desc">
                        Match volunteers by skills and preferences to support ground operations and relief delivery.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="package"></span></div>
                    <h3 class="feature-card__title">Donation Management</h3>
                    <p class="feature-card__desc">
                        Coordinate collection points, inventory, and donation requests with full lifecycle visibility.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="map-pin"></span></div>
                    <h3 class="feature-card__title">Safe Location Mapping</h3>
                    <p class="feature-card__desc">
                        Publish and maintain verified safe locations to guide public evacuation and temporary sheltering.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="message-circle"></span></div>
                    <h3 class="feature-card__title">Community Forum</h3>
                    <p class="feature-card__desc">
                        Share official updates, preparedness guidance, and community support information in one channel.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="cta" id="about">
        <div class="cta__container">
            <h2 class="cta__title">Ready to Make a Difference?</h2>
            <p class="cta__subtitle">
                Join <?= e($appName) ?> and help build resilient communities through timely alerts, coordinated response,
                and transparent donation delivery.
            </p>
            <div class="cta__actions">
                <?php if ($isAuthenticated): ?>
                    <a href="/dashboard" class="btn btn-primary btn-large">Go to Dashboard</a>
                <?php else: ?>
                    <a href="/register" class="btn btn-primary btn-large">Choose Your Role</a>
                <?php endif; ?>
                <a href="/make-donation" class="btn btn-large cta-ghost">Make a Donation</a>
            </div>
        </div>
    </section>

    <footer class="footer" id="contact">
        <div class="footer__container">
            <div class="footer__grid">
                <div>
                    <div class="footer__brand">
                        <img src="<?= asset('img/logo.svg') ?>" alt="<?= e($appName) ?>">
                    </div>
                    <p class="footer__desc">
                        <?= e($appName) ?> is a disaster early warning and post-disaster donation management platform for
                        coordinated public safety and relief operations.
                    </p>
                </div>
                <div>
                    <h4 class="footer__title">Platform</h4>
                    <ul class="footer__links">
                        <li><a href="<?= e($dashboardHref) ?>">Dashboard</a></li>
                        <li><a href="/register?role=volunteer">Become a Volunteer</a></li>
                        <li><a href="/register?role=ngo">Join as NGO</a></li>
                        <li><a href="#features">Features</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer__title">Resources</h4>
                    <ul class="footer__links">
                        <li><a href="/forum">Community Forum</a></li>
                        <li><a href="/safe-locations">Safe Locations</a></li>
                        <li><a href="/make-donation">Donation Portal</a></li>
                        <li><a href="/forgot-password">Account Help</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer__title">Contact</h4>
                    <ul class="footer__links">
                        <li><a href="#about">About Platform</a></li>
                        <li><a href="#contact">Contact Section</a></li>
                        <li><a href="<?= e($loginHref) ?>">Login</a></li>
                        <li><a href="<?= e($registerHref) ?>">Create Account</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <div class="footer__copyright">
                    © <?= e((string) date('Y')) ?> <?= e($appName) ?>. All rights reserved.
                </div>
                <div class="footer__social">
                    <a href="/forum" class="social-link" aria-label="Open Forum">
                        <span data-lucide="message-square"></span>
                    </a>
                    <a href="/safe-locations" class="social-link" aria-label="Safe Locations">
                        <span data-lucide="map"></span>
                    </a>
                    <a href="/make-donation" class="social-link" aria-label="Make Donation">
                        <span data-lucide="heart-handshake"></span>
                    </a>
                    <a href="<?= e($dashboardHref) ?>" class="social-link" aria-label="Dashboard">
                        <span data-lucide="layout-dashboard"></span>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.landing-page a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (event) {
            var href = this.getAttribute('href');
            if (!href || href === '#') return;

            var target = document.querySelector(href);
            if (!target) return;

            event.preventDefault();
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
});
</script>
