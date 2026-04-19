<section class="welcome">
    <h1>DMC Account Operations</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="users"></span>
        <p>Approve volunteer and NGO registrations. Manage Grama Niladhari lifecycle from GN Accounts.</p>
    </div>
</section>

<section class="quick-actions" aria-label="DMC quick actions">
    <article class="action-card">
        <h3>GN Accounts</h3>
        <p>Create, activate, deactivate, and resend access confirmation emails for GN officers.</p>
        <a href="/dashboard/admin/grama-niladhari/accounts" class="btn btn-primary">Open GN Accounts</a>
    </article>
</section>

<section class="section-card" aria-label="Pending approvals">
    <h2>Pending Volunteer & NGO Approvals</h2>

    <?php
    $pendingVolunteers = $pending_volunteers ?? [];
    $pendingNgos = $pending_ngos ?? [];
    $hasPending = !empty($pendingVolunteers) || !empty($pendingNgos);
    ?>

    <?php if (!$hasPending): ?>
        <p class="muted mb-0">No pending volunteer or NGO accounts.</p>
    <?php else: ?>
        <div class="tab-switch" role="tablist" aria-label="Pending approval groups" style="display:flex; gap:0.5rem; margin-bottom:1rem;">
            <button id="tab-ngos" type="button" class="btn btn-primary" data-target="panel-ngos" role="tab" aria-controls="panel-ngos" aria-selected="true">NGOs (<?= count($pendingNgos) ?>)</button>
            <button id="tab-volunteers" type="button" class="btn" data-target="panel-volunteers" role="tab" aria-controls="panel-volunteers" aria-selected="false">Volunteers (<?= count($pendingVolunteers) ?>)</button>
        </div>

        <div id="panel-ngos" class="approval-panel" role="tabpanel" aria-labelledby="tab-ngos">
            <?php if (empty($pendingNgos)): ?>
                <p class="muted mb-0">No pending NGO approvals.</p>
            <?php else: ?>
                <div class="table-shell">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Organization Name</th>
                                <th>Registration Number</th>
                                <th>Contact Person</th>
                                <th>Contact Telephone</th>
                                <th>Contact Email</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingNgos as $ngo): ?>
                                <tr>
                                    <td><?= e((string) ($ngo['organization_name'] ?? '-')) ?></td>
                                    <td><?= e((string) ($ngo['registration_number'] ?? '-')) ?></td>
                                    <td><?= e((string) ($ngo['contact_person_name'] ?? '-')) ?></td>
                                    <td><?= e((string) ($ngo['contact_person_telephone'] ?? '-')) ?></td>
                                    <td><?= e((string) ($ngo['contact_person_email'] ?? $ngo['email'] ?? '-')) ?></td>
                                    <td style="text-align:right;">
                                        <form method="POST" action="/dashboard/admin/approve/<?= (int) $ngo['user_id'] ?>" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div id="panel-volunteers" class="approval-panel" role="tabpanel" aria-labelledby="tab-volunteers" hidden>
            <?php if (empty($pendingVolunteers)): ?>
                <p class="muted mb-0">No pending volunteer approvals.</p>
            <?php else: ?>
                <div class="table-shell">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>GN Division</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingVolunteers as $volunteer): ?>
                                <tr>
                                    <td><?= e((string) ($volunteer['volunteer_name'] ?? $volunteer['display_name'] ?? '-')) ?></td>
                                    <td><?= e((string) ($volunteer['volunteer_contact_number'] ?? '-')) ?></td>
                                    <td><?= e((string) ($volunteer['email'] ?? '-')) ?></td>
                                    <td><?= e((string) ($volunteer['volunteer_gn_division'] ?? '-')) ?></td>
                                    <td style="text-align:right;">
                                        <form method="POST" action="/dashboard/admin/approve/<?= (int) $volunteer['user_id'] ?>" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <script>
            (function () {
                var buttons = document.querySelectorAll('.tab-switch [data-target]');
                var panels = document.querySelectorAll('.approval-panel');
                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        var targetId = button.getAttribute('data-target');
                        buttons.forEach(function (item) {
                            var isActive = item === button;
                            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                            item.classList.toggle('btn-primary', isActive);
                        });
                        panels.forEach(function (panel) {
                            panel.hidden = panel.id !== targetId;
                        });
                    });
                });
            })();
        </script>
    <?php endif; ?>
</section>
