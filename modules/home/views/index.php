<?php
$isAuthenticated = auth_check();
$appName = (string) config("app.name");

$dashboardHref = $isAuthenticated ? "/dashboard" : "/login";
$loginHref = "/login";
$registerHref = "/register";
?>

<div class="landing-page">
    <section class="hero" id="top">
        <div class="hero__container">
            <div class="hero__content">
                <div class="hero__badge">
                    <span class="icon" data-lucide="shield-check"></span>
                    <span>National Disaster Early Warning and Relief Coordination Platform</span>
                </div>
                <h1 class="hero__title">
                    Saving Lives Through<br>Smart Disaster Response
                </h1>
                <p class="hero__subtitle">
                    <?= e(
                        $appName,
                    ) ?> connects communities, responders, and relief partners on one trusted platform for
                    early warnings, verified incident reporting, and transparent post-disaster donation management.
                </p>
                <div class="hero__actions">
                    <?php if ($isAuthenticated): ?>
                        <a href="/dashboard" class="btn btn-primary btn-large">
                            Open Dashboard
                            <span data-lucide="arrow-right" style="width:18px;height:18px"></span>
                        </a>
                        <a href="/make-donation" class="btn btn-large">Donate Support</a>
                    <?php else: ?>
                        <a href="/register?role=volunteer" class="btn btn-primary btn-large">
                            Join as Volunteer
                            <span data-lucide="arrow-right" style="width:18px;height:18px"></span>
                        </a>
                        <a href="#workflow" class="btn btn-large">Explore How It Works</a>
                    <?php endif; ?>
                </div>
                <ul class="hero__trust-list" aria-label="Platform trust highlights">
                    <li><span data-lucide="check-check"></span><span>DMC-governed account approvals</span></li>
                    <li><span data-lucide="check-check"></span><span>Role-based operational access</span></li>
                    <li><span data-lucide="check-check"></span><span>Guest-friendly donation support</span></li>
                </ul>
            </div>
            <div class="hero__media">
                <div class="hero__image-wrapper">
                    <img
                        src="https://images.unsplash.com/photo-1593113598332-cd288d649433?w=1200&q=80"
                        alt="Emergency response team coordinating disaster relief"
                        loading="eager"
                    >
                </div>
                <div class="hero__image-accent"></div>
                <aside class="hero-status" aria-label="Operational readiness">
                    <h3 class="hero-status__title">Operational Readiness</h3>
                    <ul class="hero-status__list">
                        <li><span data-lucide="circle-check"></span><span>Verified disaster report workflow active</span></li>
                        <li><span data-lucide="circle-check"></span><span>Volunteer and NGO approval pipeline managed</span></li>
                        <li><span data-lucide="circle-check"></span><span>Safe location and donation modules online</span></li>
                    </ul>
                </aside>
            </div>
        </div>
    </section>

    <section class="impact-strip">
        <div class="impact-strip__container">

            <article class="impact-card">
                <div class="impact-card__value">5 Core Roles</div>
                <p class="impact-card__label">Public, Volunteer, NGO, GN, and DMC</p>
            </article>
            <article class="impact-card">
                <div class="impact-card__value">24/7 Access</div>
                <p class="impact-card__label">Always-on reporting and donation flows</p>
            </article>
            <article class="impact-card">
                <div class="impact-card__value">Traceable Actions</div>
                <p class="impact-card__label">Structured workflows with accountable updates</p>
            </article>
        </div>
    </section>

    <section class="audience" id="audience">
        <div class="audience__container">
            <div class="section-header">
                <h2 class="section-title">Built for Citizens and Responders</h2>
                <p class="section-subtitle">
                    Purpose-driven workflows for public safety, disaster response teams, and relief organizations.
                </p>
            </div>
            <div class="audience__grid">
                <article class="audience-card">
                    <h3 class="audience-card__title">For General Public and Guests</h3>
                    <ul class="audience-card__list">
                        <li><span data-lucide="check"></span><span>Access early warnings and safe location guidance.</span></li>
                        <li><span data-lucide="check"></span><span>Submit verified disaster reports with location context.</span></li>
                        <li><span data-lucide="check"></span><span>Contribute donations with transparent follow-up flow.</span></li>
                    </ul>
                    <div class="audience-card__actions">
                        <a href="/register?role=general" class="btn btn-primary">Create Public Account</a>
                        <a href="/make-donation" class="btn">Donate as Guest</a>
                    </div>
                </article>
                <article class="audience-card">
                    <h3 class="audience-card__title">For Volunteers, NGOs, GN, and DMC</h3>
                    <ul class="audience-card__list">
                        <li><span data-lucide="check"></span><span>Manage approvals and role-specific operational panels.</span></li>
                        <li><span data-lucide="check"></span><span>Coordinate reports, requests, collection points, and inventories.</span></li>
                        <li><span data-lucide="check"></span><span>Track mission-critical activities with clear accountability.</span></li>
                    </ul>
                    <div class="audience-card__actions">
                        <a href="/login" class="btn btn-primary">Open Role Dashboard</a>
                        <a href="/register" class="btn">Choose Registration Role</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="features__container">
            <div class="section-header">
                <h2 class="section-title">Core Platform Capabilities</h2>
                <p class="section-subtitle">
                    Everything required for preparedness, coordinated response, and relief execution.
                </p>
            </div>
            <div class="features__grid">
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="line-chart"></span></div>
                    <h3 class="feature-card__title">Forecast and Risk Monitoring</h3>
                    <p class="feature-card__desc">
                        View forecast-driven risk indicators to activate preparedness before escalation.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="alert-triangle"></span></div>
                    <h3 class="feature-card__title">Verified Incident Reporting</h3>
                    <p class="feature-card__desc">
                        Capture field reports and process verifications with structured DMC workflows.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="users"></span></div>
                    <h3 class="feature-card__title">Volunteer Task Coordination</h3>
                    <p class="feature-card__desc">
                        Assign operations by volunteer skills, availability, and local response needs.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="package"></span></div>
                    <h3 class="feature-card__title">Donation and Inventory Control</h3>
                    <p class="feature-card__desc">
                        Manage collection points, request fulfillment, and inventory lifecycle updates.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="map-pin"></span></div>
                    <h3 class="feature-card__title">Safe Location Registry</h3>
                    <p class="feature-card__desc">
                        Maintain verified safe zones and shelter references for public access.
                    </p>
                </article>
                <article class="feature-card">
                    <div class="feature-card__icon"><span data-lucide="message-circle"></span></div>
                    <h3 class="feature-card__title">Community Communication</h3>
                    <p class="feature-card__desc">
                        Deliver public guidance and operational updates through centralized announcements.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="operations" id="workflow">
        <div class="operations__container">
            <div class="section-header">
                <h2 class="section-title">How Response Works</h2>
                <p class="section-subtitle">
                    A clear operational chain from warning issuance to accountable relief distribution.
                </p>
            </div>
            <div class="operations__grid">
                <article class="operation-step">
                    <div class="operation-step__index">01</div>
                    <h3 class="operation-step__title">Alert and Readiness Phase</h3>
                    <p class="operation-step__desc">
                        DMC publishes verified alerts and preparedness instructions for at-risk districts.
                    </p>
                </article>
                <article class="operation-step">
                    <div class="operation-step__index">02</div>
                    <h3 class="operation-step__title">Incident Capture and Verification</h3>
                    <p class="operation-step__desc">
                        Public and field officers submit reports for rapid review and decision support.
                    </p>
                </article>
                <article class="operation-step">
                    <div class="operation-step__index">03</div>
                    <h3 class="operation-step__title">Resource and Team Coordination</h3>
                    <p class="operation-step__desc">
                        Volunteers and NGOs are assigned based on role, capability, and local demand.
                    </p>
                </article>
                <article class="operation-step">
                    <div class="operation-step__index">04</div>
                    <h3 class="operation-step__title">Relief Tracking and Closure</h3>
                    <p class="operation-step__desc">
                        Requests, donations, and inventory updates are tracked through completion.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="roles-showcase" id="roles">
        <div class="roles-showcase__container">
            <div class="section-header">
                <h2 class="section-title">Built for Every Response Role</h2>
                <p class="section-subtitle">
                    Structured role access enables secure collaboration without operational overlap.
                </p>
            </div>
            <div class="roles-showcase__grid">
                <article class="role-card">
                    <div class="role-card__top">
                        <span class="role-card__badge">Public</span>
                        <span data-lucide="user-round"></span>
                    </div>
                    <h3 class="role-card__title">General Public</h3>
                    <p class="role-card__desc">Receive alerts, report disasters, and request relief support.</p>
                    <a href="/register?role=general" class="role-card__link">Create Public Account</a>
                </article>
                <article class="role-card">
                    <div class="role-card__top">
                        <span class="role-card__badge">Approved</span>
                        <span data-lucide="handshake"></span>
                    </div>
                    <h3 class="role-card__title">Volunteer</h3>
                    <p class="role-card__desc">Execute assigned response tasks based on approved competencies.</p>
                    <a href="/register?role=volunteer" class="role-card__link">Apply as Volunteer</a>
                </article>
                <article class="role-card">
                    <div class="role-card__top">
                        <span class="role-card__badge">Approved</span>
                        <span data-lucide="building-2"></span>
                    </div>
                    <h3 class="role-card__title">NGO</h3>
                    <p class="role-card__desc">Manage collection points, donation intake, and stock movements.</p>
                    <a href="/register?role=ngo" class="role-card__link">Register NGO</a>
                </article>
                <article class="role-card">
                    <div class="role-card__top">
                        <span class="role-card__badge">DMC Managed</span>
                        <span data-lucide="map-pinned"></span>
                    </div>
                    <h3 class="role-card__title">Grama Niladhari</h3>
                    <p class="role-card__desc">Validate local incidents and coordinate division-level priorities.</p>
                    <a href="/login" class="role-card__link">Officer Login</a>
                </article>
                <article class="role-card">
                    <div class="role-card__top">
                        <span class="role-card__badge">Admin</span>
                        <span data-lucide="shield"></span>
                    </div>
                    <h3 class="role-card__title">DMC</h3>
                    <p class="role-card__desc">Govern platform operations, approvals, and response oversight.</p>
                    <a href="/login" class="role-card__link">Admin Login</a>
                </article>
            </div>
        </div>
    </section>

    <section class="assurance" id="about">
        <div class="assurance__container">
            <div class="section-header">
                <p class="section-eyebrow">Operational Trust</p>
                <h2 class="section-title">Secure, Governed, and Field-Ready</h2>
                <p class="section-subtitle">
                    Governance and security controls built for high-pressure emergency operations.
                </p>
            </div>
            <div class="assurance__grid">
                <article class="assurance-item">
                    <div class="assurance-item__head">
                        <span class="assurance-item__pill">Access Governance</span>
                        <span class="assurance-item__icon" data-lucide="badge-check"></span>
                    </div>
                    <h3 class="assurance-item__title">Controlled Account Lifecycle</h3>
                    <p class="assurance-item__desc">
                        Volunteer and NGO registrations remain inactive until DMC review and approval is completed.
                    </p>
                    <div class="assurance-item__meta">
                        <span data-lucide="circle-check"></span>
                        <span>DMC approval checkpoints enforced</span>
                    </div>
                </article>
                <article class="assurance-item">
                    <div class="assurance-item__head">
                        <span class="assurance-item__pill">Permission Control</span>
                        <span class="assurance-item__icon" data-lucide="lock-keyhole"></span>
                    </div>
                    <h3 class="assurance-item__title">Role-Based Permissions</h3>
                    <p class="assurance-item__desc">
                        Each role is granted access only to the workflows and data needed for assigned responsibilities.
                    </p>
                    <div class="assurance-item__meta">
                        <span data-lucide="circle-check"></span>
                        <span>Least-privilege model across modules</span>
                    </div>
                </article>
                <article class="assurance-item">
                    <div class="assurance-item__head">
                        <span class="assurance-item__pill">Operational Oversight</span>
                        <span class="assurance-item__icon" data-lucide="clipboard-list"></span>
                    </div>
                    <h3 class="assurance-item__title">Traceable Operations</h3>
                    <p class="assurance-item__desc">
                        Incident handling, donation requests, and inventory updates remain visible through full lifecycle.
                    </p>
                    <div class="assurance-item__meta">
                        <span data-lucide="circle-check"></span>
                        <span>Status transitions remain auditable</span>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="faq" id="faq">
        <div class="faq__container">
            <div class="section-header">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Key questions from public users and response organizations.</p>
            </div>
            <div class="faq__list">
                <details class="faq-item">
                    <summary>Do volunteer and NGO accounts activate immediately?</summary>
                    <p>No. They remain pending until approved by DMC administrators.</p>
                </details>
                <details class="faq-item">
                    <summary>Can unregistered users make donations?</summary>
                    <p>Yes. Guest donations are supported through the public donation flow.</p>
                </details>
                <details class="faq-item">
                    <summary>Who can verify disaster reports?</summary>
                    <p>DMC handles report verification and coordinates assignments for response teams.</p>
                </details>
                <details class="faq-item">
                    <summary>How can I track submitted donations?</summary>
                    <p>Registered users can monitor donation status in dashboard views, and guests receive token-based tracking links.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="cta__container">
            <h2 class="cta__title">Ready to Make a Difference?</h2>
            <p class="cta__subtitle">
                Join <?= e(
                    $appName,
                ) ?> and help build resilient communities through timely alerts, coordinated response,
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
                        <img src="<?= asset("img/logo.svg") ?>" alt="<?= e(
    $appName,
) ?>">
                    </div>
                    <p class="footer__desc">
                        <?= e(
                            $appName,
                        ) ?> is a disaster early warning and post-disaster donation management platform built
                        for coordinated public safety and accountable relief operations.
                    </p>
                </div>
                <div>
                    <h4 class="footer__title">Platform</h4>
                    <ul class="footer__links">
                        <li><a href="<?= e(
                            $dashboardHref,
                        ) ?>">Dashboard</a></li>
                        <li><a href="/register?role=volunteer">Become a Volunteer</a></li>
                        <li><a href="/register?role=ngo">Join as NGO</a></li>
                        <li><a href="#workflow">How It Works</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer__title">Resources</h4>
                    <ul class="footer__links">
                        <li><a href="/forum">Community Forum</a></li>
                        <li><a href="/safe-locations">Safe Locations</a></li>
                        <li><a href="/make-donation">Donation Portal</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer__title">Contact</h4>
                    <ul class="footer__links">
                        <li><a href="#about">About Platform</a></li>
                        <li><a href="#contact">Contact Section</a></li>
                        <li><a href="<?= e($loginHref) ?>">Login</a></li>
                        <li><a href="<?= e(
                            $registerHref,
                        ) ?>">Create Account</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <div class="footer__copyright">
                    © <?= e((string) date("Y")) ?> <?= e(
     $appName,
 ) ?>. All rights reserved.
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
                    <a href="<?= e(
                        $dashboardHref,
                    ) ?>" class="social-link" aria-label="Dashboard">
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
