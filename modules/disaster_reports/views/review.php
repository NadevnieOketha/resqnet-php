<style>
  h1 { margin:0 0 1.4rem; }
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
  .empty-state { padding: 1rem; color: var(--color-text-subtle); }
  @media (max-width:780px){
    table.report-table thead { display:none; }
    table.report-table tbody td { display:block; padding:0.6rem 0.85rem; }
    table.report-table tbody tr { border-bottom:1px solid var(--color-border); }
    table.report-table tbody td::before { content:attr(data-label); display:block; font-weight:600; margin-bottom:0.25rem; }
    .action-pills{ flex-wrap:wrap; }
  }
</style>

<h1>Disaster Reports</h1>
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
        <?php foreach (($pending_reports ?? []) as $report): ?>
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
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($approved_reports ?? [])): ?>
        <tr><td colspan="8" class="empty-state">No approved reports yet.</td></tr>
      <?php else: ?>
        <?php foreach (($approved_reports ?? []) as $report): ?>
          <?php $assignedCount = (int) (($assigned_counts ?? [])[(int) ($report['report_id'] ?? 0)] ?? 0); ?>
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
            <td data-label="Actions">
              <div class="action-pills" style="flex-direction:column;align-items:flex-start;gap:0.4rem;">
                <form method="POST" action="/dashboard/reports/<?= (int) $report['report_id'] ?>/assign-volunteers" class="inline-form">
                  <?= csrf_field() ?>
                  <button type="submit" class="pill"><span data-lucide="user-plus"></span><span>Assign volunteers</span></button>
                </form>
                <small style="color:#666;">Assigned so far: <?= $assignedCount ?></small>
              </div>
            </td>
          </tr>
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
