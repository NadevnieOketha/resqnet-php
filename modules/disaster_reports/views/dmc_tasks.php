<style>
  h1 { margin: 0 0 1rem; }
  .top-tools { display:flex; justify-content:space-between; gap:0.8rem; align-items:flex-end; margin-bottom:1rem; flex-wrap:wrap; }
  .kpi-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:0.6rem; flex:1; min-width:420px; }
  .kpi { background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-md); padding:0.6rem 0.7rem; }
  .kpi .label { font-size:0.66rem; color:#666; }
  .kpi .value { font-size:1rem; font-weight:700; }
  .filter-form { display:flex; align-items:flex-end; gap:0.5rem; }
  .table-shell { border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:auto; background:#fff; }
  table.task-table { width:100%; border-collapse:collapse; font-size:0.7rem; min-width:1200px; }
  table.task-table thead th { text-align:left; padding:0.75rem 0.85rem; background:#fafafa; border-bottom:1px solid var(--color-border); }
  table.task-table tbody td { padding:0.85rem; border-bottom:1px solid var(--color-border); vertical-align:top; }
  .status-badge { display:inline-flex; border-radius:999px; padding:0.2rem 0.55rem; font-weight:600; font-size:0.62rem; border:1px solid var(--color-border); background:#f4f4f4; }
  .cell-actions { display:grid; gap:0.45rem; }
  .cell-actions form { display:flex; gap:0.35rem; align-items:center; }
  .cell-actions select { min-width:180px; }
  .empty-state { padding:1rem; color:#666; }
</style>

<h1>Volunteer Assignment Oversight</h1>

<div class="top-tools">
  <div class="kpi-row">
    <div class="kpi"><div class="label">Pending</div><div class="value"><?= (int) (($task_counts ?? [])['pending'] ?? 0) ?></div></div>
    <div class="kpi"><div class="label">Assigned</div><div class="value"><?= (int) (($task_counts ?? [])['assigned'] ?? 0) ?></div></div>
    <div class="kpi"><div class="label">Accepted</div><div class="value"><?= (int) (($task_counts ?? [])['accepted'] ?? 0) ?></div></div>
    <div class="kpi"><div class="label">In Progress</div><div class="value"><?= (int) (($task_counts ?? [])['in_progress'] ?? 0) ?></div></div>
    <div class="kpi"><div class="label">Completed</div><div class="value"><?= (int) (($task_counts ?? [])['completed'] ?? 0) ?></div></div>
    <div class="kpi"><div class="label">Verified</div><div class="value"><?= (int) (($task_counts ?? [])['verified'] ?? 0) ?></div></div>
  </div>

  <form method="GET" action="/dashboard/admin/volunteer-tasks" class="filter-form">
    <div class="form-group" style="margin:0;">
      <label for="status">Filter status</label>
      <?php $statusFilter = (string) ($status_filter ?? ''); ?>
      <select id="status" name="status" class="input">
        <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>All</option>
        <?php foreach (['Pending', 'Assigned', 'Accepted', 'In Progress', 'Completed', 'Verified', 'Declined'] as $status): ?>
          <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-outline">Apply</button>
  </form>
</div>

<div class="table-shell">
  <table class="task-table">
    <thead>
      <tr>
        <th>Task</th>
        <th>Volunteer</th>
        <th>Report</th>
        <th>Location</th>
        <th>Assigned</th>
        <th>Status</th>
        <th>DMC Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($tasks ?? [])): ?>
        <tr><td colspan="7" class="empty-state">No tasks available for this filter.</td></tr>
      <?php else: ?>
        <?php foreach (($tasks ?? []) as $task): ?>
          <?php
            $district = (string) ($task['district'] ?? '');
            $availableVolunteers = disaster_reports_list_active_volunteers($district);
          ?>
          <tr>
            <td>
              <strong>#<?= (int) ($task['id'] ?? 0) ?></strong><br>
              <small><?= e((string) ($task['role'] ?? 'Disaster Response')) ?></small>
            </td>
            <td>
              <?= e((string) ($task['volunteer_name'] ?? '-')) ?><br>
              <small>ID: <?= (int) ($task['volunteer_id'] ?? 0) ?></small>
            </td>
            <td>
              <strong>#<?= (int) ($task['report_id'] ?? 0) ?></strong><br>
              <?= e(disaster_reports_disaster_label($task)) ?><br>
              <small><?= e((string) ($task['disaster_datetime'] ?? '-')) ?></small>
            </td>
            <td>
              <?= e((string) (($task['district'] ?? '') . ' / ' . ($task['gn_division'] ?? ''))) ?><br>
              <small><?= e((string) ($task['location'] ?? '-')) ?></small>
            </td>
            <td><?= e((string) ($task['date_assigned'] ?? '-')) ?></td>
            <td><span class="status-badge"><?= e((string) ($task['status'] ?? '-')) ?></span></td>
            <td>
              <div class="cell-actions">
                <form method="POST" action="/dashboard/admin/volunteer-tasks/<?= (int) ($task['id'] ?? 0) ?>/reassign">
                  <?= csrf_field() ?>
                  <select name="new_volunteer_id" class="input" required>
                    <option value="">Reassign volunteer</option>
                    <?php foreach ($availableVolunteers as $vol): ?>
                      <option value="<?= (int) ($vol['user_id'] ?? 0) ?>">
                        <?= e((string) (($vol['name'] ?? 'Volunteer') . ' - ' . ($vol['district'] ?? ''))) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-outline btn-sm">Reassign</button>
                </form>

                <?php if ((string) ($task['status'] ?? '') === 'Completed'): ?>
                  <form method="POST" action="/dashboard/admin/volunteer-tasks/<?= (int) ($task['id'] ?? 0) ?>/verify">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm">Mark Verified</button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
