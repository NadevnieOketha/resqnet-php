<style>
  h1 { margin: 0 0 1rem; }
  .kpi-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:0.7rem; margin-bottom:1rem; }
  .kpi { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-md); padding:0.7rem 0.8rem; }
  .kpi .label { font-size:0.7rem; color:#666; }
  .kpi .value { font-size:1.05rem; font-weight:700; }
  .table-shell { border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:auto; background:#fff; }
  table.task-table { width:100%; border-collapse:collapse; font-size:0.72rem; min-width:980px; }
  table.task-table thead th { text-align:left; padding:0.75rem 0.85rem; background:#fafafa; border-bottom:1px solid var(--color-border); }
  table.task-table tbody td { padding:0.85rem; border-bottom:1px solid var(--color-border); vertical-align:top; }
  .status-badge { display:inline-flex; border-radius:999px; padding:0.2rem 0.55rem; font-weight:600; font-size:0.62rem; border:1px solid var(--color-border); background:#f4f4f4; }
  .actions { display:grid; gap:0.35rem; }
  .actions form { display:grid; gap:0.35rem; }
  .actions textarea { min-height:54px; resize:vertical; }
  .empty-state { padding:1rem; color:#666; }
</style>

<h1>Assigned Volunteer Tasks</h1>

<div class="kpi-row">
  <div class="kpi"><div class="label">Pending</div><div class="value"><?= (int) (($task_counts ?? [])['pending'] ?? 0) ?></div></div>
  <div class="kpi"><div class="label">Assigned</div><div class="value"><?= (int) (($task_counts ?? [])['assigned'] ?? 0) ?></div></div>
  <div class="kpi"><div class="label">Accepted</div><div class="value"><?= (int) (($task_counts ?? [])['accepted'] ?? 0) ?></div></div>
  <div class="kpi"><div class="label">In Progress</div><div class="value"><?= (int) (($task_counts ?? [])['in_progress'] ?? 0) ?></div></div>
  <div class="kpi"><div class="label">Completed</div><div class="value"><?= (int) (($task_counts ?? [])['completed'] ?? 0) ?></div></div>
  <div class="kpi"><div class="label">Verified</div><div class="value"><?= (int) (($task_counts ?? [])['verified'] ?? 0) ?></div></div>
</div>

<div class="table-shell">
  <table class="task-table">
    <thead>
      <tr>
        <th>Task ID</th>
        <th>Report</th>
        <th>Location</th>
        <th>Assigned</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($tasks ?? [])): ?>
        <tr><td colspan="6" class="empty-state">No tasks assigned yet.</td></tr>
      <?php else: ?>
        <?php foreach (($tasks ?? []) as $task): ?>
          <?php
            $status = (string) ($task['status'] ?? 'Pending');
            $actions = [];
            if (in_array($status, ['Pending', 'Assigned'], true)) {
                $actions[] = 'Accepted';
                $actions[] = 'Declined';
            } elseif ($status === 'Accepted') {
                $actions[] = 'In Progress';
                $actions[] = 'Declined';
            } elseif ($status === 'In Progress') {
                $actions[] = 'Completed';
            }
          ?>
          <tr>
            <td>#<?= (int) ($task['id'] ?? 0) ?></td>
            <td>
              <strong>#<?= (int) ($task['report_id'] ?? 0) ?></strong><br>
              <?= e(disaster_reports_disaster_label($task)) ?><br>
              <small><?= e((string) ($task['role'] ?? 'Disaster Response')) ?></small>
            </td>
            <td>
              <?= e((string) (($task['district'] ?? '') . ' / ' . ($task['gn_division'] ?? ''))) ?><br>
              <small><?= e((string) ($task['location'] ?? '-')) ?></small>
            </td>
            <td>
              <?= e((string) ($task['date_assigned'] ?? '-')) ?><br>
              <small><?= e((string) ($task['disaster_datetime'] ?? '-')) ?></small>
            </td>
            <td><span class="status-badge"><?= e($status) ?></span></td>
            <td>
              <?php if (empty($actions)): ?>
                <small style="color:#666;">No action available</small>
              <?php else: ?>
                <div class="actions">
                  <?php foreach ($actions as $nextStatus): ?>
                    <form method="POST" action="/dashboard/volunteer-tasks/<?= (int) ($task['id'] ?? 0) ?>/status">
                      <?= csrf_field() ?>
                      <input type="hidden" name="next_status" value="<?= e($nextStatus) ?>">
                      <textarea name="update_note" class="input" placeholder="Optional field update note"></textarea>
                      <button type="submit" class="btn btn-primary btn-sm">Mark <?= e($nextStatus) ?></button>
                    </form>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
