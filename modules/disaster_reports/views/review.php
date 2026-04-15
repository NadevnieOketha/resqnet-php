<style>
  .page-head { display:flex; justify-content:space-between; align-items:center; gap:0.8rem; margin:0 0 1.2rem; flex-wrap:wrap; }
  .page-head h1 { margin:0; }
  .tabs { display:flex; gap:2.75rem; border-bottom:1px solid var(--color-border); margin-bottom:1rem; position:relative; }
  .tab-btn { all:unset; cursor:pointer; font-size:0.7rem; font-weight:600; padding:0.9rem 0; color:#222; }
  .tab-btn[aria-selected='true'] { color:#000; }
  .tab-indicator { position:absolute; bottom:-1px; height:2px; background:var(--color-accent); width:220px; transition:transform .25s ease, width .25s ease; }
  .table-shell { border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:hidden; background:#fff; }
  table.report-table { width:100%; border-collapse:collapse; font-size:0.65rem; }
  table.report-table thead th { text-align:left; padding:0.75rem 0.85rem; background:#fafafa; font-weight:600; border-bottom:1px solid var(--color-border); }
  table.report-table tbody td { padding:0.9rem 0.85rem; border-bottom:1px solid var(--color-border); vertical-align:top; }
  table.report-table tbody tr:last-child td { border-bottom:none; }
  .hidden { display:none !important; }
  .reported-by { color:#666; }
  .date-time { display:flex; flex-direction:column; gap:0.2rem; color:#666; }
  .type-strong { font-weight:700; }
  .action-pills { display:flex; gap:0.55rem; align-items:center; }
  .pill { all:unset; cursor:pointer; font-size:0.55rem; font-weight:600; padding:0.5rem 1.05rem; border-radius:999px; background:#e3e3e3; display:inline-flex; align-items:center; gap:0.35rem; }
  .pill-danger { background:#d91e18; color:#fff; }
  .pill svg { width:14px; height:14px; }
  .volunteer-stack { display:grid; gap:0.55rem; min-width:250px; }
  .volunteer-item { border:1px solid var(--color-border); border-radius:10px; padding:0.5rem 0.6rem; background:#fafafa; }
  .volunteer-head { display:flex; justify-content:space-between; gap:0.45rem; align-items:center; }
  .volunteer-name { font-weight:600; }
  .volunteer-status { font-size:0.58rem; padding:0.18rem 0.5rem; border-radius:999px; border:1px solid var(--color-border); background:#fff; }
  .volunteer-notes { margin:0.4rem 0 0; padding-left:1rem; color:#444; }
  .volunteer-notes li { margin-bottom:0.2rem; }
  .volunteer-notes strong { font-size:0.6rem; }
  .no-notes { color:#777; font-size:0.6rem; margin-top:0.35rem; }
  .group-row td { background:#f7fafc; font-weight:700; color:#0f172a; }
  .group-row-type td { background:#fefaf0; font-weight:600; color:#1f2937; }
  .empty-state { padding: 1rem; color: var(--color-text-subtle); }
  @media (max-width:780px){
    table.report-table thead { display:none; }
    table.report-table tbody td { display:block; padding:0.6rem 0.85rem; }
    table.report-table tbody tr { border-bottom:1px solid var(--color-border); }
    table.report-table tbody td::before { content:attr(data-label); display:block; font-weight:600; margin-bottom:0.25rem; }
    .action-pills{ flex-wrap:wrap; }
  }
</style>

<div class="page-head">
  <h1>Disaster Reports</h1>
  <a href="/report-disaster" class="btn btn-primary">Report a Disaster</a>
</div>
<div class="tabs" role="tablist">
  <button class="tab-btn" id="tab-pending" role="tab" aria-controls="panel-pending" aria-selected="true">Pending Reports</button>
  <button class="tab-btn" id="tab-approved" role="tab" aria-controls="panel-approved" aria-selected="false">Approved Reports</button>
  <span class="tab-indicator" aria-hidden="true"></span>
</div>

<section id="panel-pending" role="tabpanel" aria-labelledby="tab-pending">
  <div class="table-shell">
    <table class="report-table" aria-describedby="mainContent">
      <thead>
        <tr>
          <th>Report ID</th>
          <th>Reported By</th>
          <th>Date/Time</th>
          <th>Location</th>
          <th>Disaster Type</th>
          <th>Description/Notes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($pending_reports ?? [])): ?>
        <tr><td colspan="7" class="empty-state">No pending reports.</td></tr>
      <?php else: ?>
        <?php foreach (($pending_reports_grouped ?? []) as $gnDivision => $typeGroups): ?>
          <tr class="group-row group-row-gn">
            <td colspan="7">GN Division: <?= e((string) $gnDivision) ?></td>
          </tr>
          <?php foreach ((array) $typeGroups as $typeLabel => $reports): ?>
            <tr class="group-row group-row-type">
              <td colspan="7">Disaster Type: <?= e((string) $typeLabel) ?></td>
            </tr>
            <?php foreach ((array) $reports as $report): ?>
              <tr>
                <td data-label="Report ID">#<?= (int) $report['report_id'] ?></td>
                <td data-label="Reported By" class="reported-by">
                  <div><?= e($report['reporter_name']) ?></div>
                  <div><?= e($report['contact_number']) ?></div>
                </td>
                <td data-label="Date/Time" class="date-time">
                  <span><?= e(date('Y-m-d', strtotime((string) $report['disaster_datetime']))) ?></span>
                  <span><?= e(date('H:i', strtotime((string) $report['disaster_datetime']))) ?></span>
                </td>
                <td data-label="Location"><?= e(trim((string) $report['district'] . ' / ' . $report['gn_division'] . (($report['location'] ?? '') !== '' ? ' / ' . $report['location'] : ''))) ?></td>
                <td data-label="Disaster Type" class="type-strong"><?= e(disaster_reports_disaster_label($report)) ?></td>
                <td data-label="Description/Notes"><?= e((string) ($report['description'] ?? '-')) ?></td>
                <td data-label="Actions">
                  <div class="action-pills">
                    <form method="POST" action="/dashboard/reports/<?= (int) $report['report_id'] ?>/verify" class="inline-form">
                      <?= csrf_field() ?>
                      <button type="submit" class="pill"><span data-lucide="check"></span><span>Verify</span></button>
                    </form>
                    <form method="POST" action="/dashboard/reports/<?= (int) $report['report_id'] ?>/reject" class="inline-form">
                      <?= csrf_field() ?>
                      <button type="submit" class="pill pill-danger"><span data-lucide="trash-2"></span><span>Reject</span></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section id="panel-approved" role="tabpanel" aria-labelledby="tab-approved" class="hidden">
  <div class="table-shell">
    <table class="report-table" aria-describedby="mainContent">
      <thead>
        <tr>
          <th>Report ID</th>
          <th>Reported By</th>
          <th>Date/Time</th>
          <th>Location</th>
          <th>Disaster Type</th>
          <th>Description/Notes</th>
          <th>Verified At</th>
          <th>Assigned Volunteers</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($approved_reports ?? [])): ?>
        <tr><td colspan="9" class="empty-state">No approved reports yet.</td></tr>
      <?php else: ?>
        <?php foreach (($approved_reports_grouped ?? []) as $gnDivision => $typeGroups): ?>
          <tr class="group-row group-row-gn">
            <td colspan="9">GN Division: <?= e((string) $gnDivision) ?></td>
          </tr>
          <?php foreach ((array) $typeGroups as $typeLabel => $reports): ?>
            <tr class="group-row group-row-type">
              <td colspan="9">Disaster Type: <?= e((string) $typeLabel) ?></td>
            </tr>
            <?php foreach ((array) $reports as $report): ?>
              <?php $assignedCount = (int) (($assigned_counts ?? [])[(int) ($report['report_id'] ?? 0)] ?? 0); ?>
              <?php $assignedVolunteers = (array) (($assigned_volunteers_by_report ?? [])[(int) ($report['report_id'] ?? 0)] ?? []); ?>
              <tr>
                <td data-label="Report ID">#<?= (int) $report['report_id'] ?></td>
                <td data-label="Reported By" class="reported-by">
                  <div><?= e($report['reporter_name']) ?></div>
                  <div><?= e($report['contact_number']) ?></div>
                </td>
                <td data-label="Date/Time" class="date-time">
                  <span><?= e(date('Y-m-d', strtotime((string) $report['disaster_datetime']))) ?></span>
                  <span><?= e(date('H:i', strtotime((string) $report['disaster_datetime']))) ?></span>
                </td>
                <td data-label="Location"><?= e(trim((string) $report['district'] . ' / ' . $report['gn_division'] . (($report['location'] ?? '') !== '' ? ' / ' . $report['location'] : ''))) ?></td>
                <td data-label="Disaster Type" class="type-strong"><?= e(disaster_reports_disaster_label($report)) ?></td>
                <td data-label="Description/Notes"><?= e((string) ($report['description'] ?? '-')) ?></td>
                <td data-label="Verified At"><?= e((string) ($report['verified_at'] ?? '-')) ?></td>
                <td data-label="Assigned Volunteers">
                  <?php if (empty($assignedVolunteers)): ?>
                    <small style="color:#666;">No volunteers assigned yet.</small>
                  <?php else: ?>
                    <div class="volunteer-stack">
                      <?php foreach ($assignedVolunteers as $volunteer): ?>
                        <?php $notes = (array) ($volunteer['notes'] ?? []); ?>
                        <div class="volunteer-item">
                          <div class="volunteer-head">
                            <span class="volunteer-name"><?= e((string) ($volunteer['volunteer_name'] ?? 'Volunteer')) ?></span>
                            <span class="volunteer-status"><?= e((string) ($volunteer['status'] ?? '-')) ?></span>
                          </div>

                          <?php if (empty($notes)): ?>
                            <div class="no-notes">No notes shared yet.</div>
                          <?php else: ?>
                            <ul class="volunteer-notes">
                              <?php foreach ($notes as $note): ?>
                                <?php $noteText = trim((string) ($note['update_text'] ?? '')); ?>
                                <?php if ($noteText === '') continue; ?>
                                <?php
                                  $stage = trim((string) ($note['stage_status'] ?? ''));
                                  $stageLabel = $stage !== '' ? $stage : 'Update';
                                ?>
                                <li>
                                  <strong><?= e($stageLabel) ?>:</strong>
                                  <?= e($noteText) ?>
                                </li>
                              <?php endforeach; ?>
                            </ul>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td data-label="Actions">
                  <div class="action-pills" style="flex-direction:column;align-items:flex-start;gap:0.4rem;">
                    <form method="POST" action="/dashboard/reports/<?= (int) $report['report_id'] ?>/assign-volunteers" class="inline-form">
                      <?= csrf_field() ?>
                      <button type="submit" class="pill"><span data-lucide="user-plus"></span><span>Assign volunteers</span></button>
                    </form>
                    <small style="color:#666;">Assigned so far: <?= $assignedCount ?> / minimum 5</small>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
  (function () {
    const tabs = Array.from(document.querySelectorAll('.tab-btn'));
    const indicator = document.querySelector('.tab-indicator');
    const panels = Array.from(document.querySelectorAll('[role="tabpanel"]'));

    function moveIndicator(active) {
      indicator.style.width = active.offsetWidth + 'px';
      indicator.style.transform = 'translateX(' + active.offsetLeft + 'px)';
    }

    function activate(button) {
      tabs.forEach((tab) => tab.setAttribute('aria-selected', tab === button ? 'true' : 'false'));
      panels.forEach((panel) => panel.classList.add('hidden'));
      const panelId = button.getAttribute('aria-controls');
      const panel = document.getElementById(panelId);
      if (panel) {
        panel.classList.remove('hidden');
      }
      moveIndicator(button);
    }

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => activate(tab));
    });

    const active = tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabs[0];
    if (active) {
      activate(active);
    }

    window.addEventListener('resize', () => {
      const current = tabs.find((tab) => tab.getAttribute('aria-selected') === 'true');
      if (current) {
        moveIndicator(current);
      }
    });
  })();
</script>
