<?php
$report = $report ?? [];
$assignedVolunteers = (array) ($assigned_volunteers ?? []);
$assignedCount = (int) ($assigned_count ?? 0);

$reportId = (int) ($report['report_id'] ?? 0);
$status = trim((string) ($report['status'] ?? 'Pending'));
$disasterTypeLabel = disaster_reports_disaster_label((array) $report);

$disasterDateTimeRaw = trim((string) ($report['disaster_datetime'] ?? ''));
$submittedAtRaw = trim((string) ($report['submitted_at'] ?? ''));
$verifiedAtRaw = trim((string) ($report['verified_at'] ?? ''));

$disasterDateTime = $disasterDateTimeRaw !== '' && strtotime($disasterDateTimeRaw) !== false
    ? date('Y-m-d H:i', strtotime($disasterDateTimeRaw))
    : '-';

$submittedAt = $submittedAtRaw !== '' && strtotime($submittedAtRaw) !== false
    ? date('Y-m-d H:i', strtotime($submittedAtRaw))
    : '-';

$verifiedAt = $verifiedAtRaw !== '' && strtotime($verifiedAtRaw) !== false
    ? date('Y-m-d H:i', strtotime($verifiedAtRaw))
    : '-';

$proofImagePath = trim((string) ($report['proof_image_path'] ?? ''));
$locationLabel = trim((string) (($report['district'] ?? '') . ' / ' . ($report['gn_division'] ?? '')));
if (trim((string) ($report['location'] ?? '')) !== '') {
    $locationLabel .= ' / ' . trim((string) $report['location']);
}

$statusClass = match ($status) {
    'Approved' => 'status-approved',
    'Rejected' => 'status-rejected',
    default => 'status-pending',
};
?>

<style>
  h1 { margin: 0 0 0.5rem; }
  .detail-subtitle { margin: 0 0 1rem; color: #666; font-size: 0.78rem; }
  .detail-grid { display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); margin-bottom: 0.9rem; }
  .detail-card { background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.9rem; }
  .detail-card h2 { margin: 0 0 0.65rem; font-size: 0.88rem; }
  .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.7rem 0.85rem; }
  .meta-item strong { display: block; font-size: 0.62rem; color: #555; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.15rem; }
  .meta-item span { font-size: 0.73rem; color: #222; }
  .status-pill { display: inline-flex; align-items: center; border: 1px solid var(--color-border); border-radius: 999px; padding: 0.2rem 0.55rem; font-size: 0.6rem; font-weight: 700; }
  .status-pending { background: #fff5df; border-color: #f0d9a5; color: #7d4f00; }
  .status-approved { background: #edf8ee; border-color: #b8dfbc; color: #1f5f2a; }
  .status-rejected { background: #fdeeee; border-color: #f0bbbb; color: #8a1616; }
  .description-box { border: 1px dashed var(--color-border); border-radius: 10px; padding: 0.7rem 0.75rem; background: #fcfcfc; font-size: 0.73rem; color: #333; line-height: 1.45; white-space: pre-wrap; }
  .proof-wrap { display: grid; gap: 0.6rem; }
  .proof-preview { max-width: 380px; border: 1px solid var(--color-border); border-radius: 10px; overflow: hidden; background: #fafafa; }
  .proof-preview img { width: 100%; display: block; }
  .volunteer-stack { display: grid; gap: 0.6rem; }
  .volunteer-item { border: 1px solid var(--color-border); border-radius: 10px; padding: 0.6rem 0.7rem; background: #fafafa; }
  .volunteer-head { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; }
  .volunteer-name { font-size: 0.73rem; font-weight: 700; }
  .volunteer-status { font-size: 0.58rem; border: 1px solid var(--color-border); background: #fff; border-radius: 999px; padding: 0.16rem 0.48rem; }
  .volunteer-meta { margin-top: 0.35rem; font-size: 0.62rem; color: #666; }
  .volunteer-notes { margin: 0.4rem 0 0; padding-left: 1rem; }
  .volunteer-notes li { font-size: 0.64rem; line-height: 1.4; margin-bottom: 0.2rem; }
  .actions-row { display: flex; flex-wrap: wrap; gap: 0.55rem; align-items: center; margin-top: 0.9rem; }
  .inline-form { display: inline-flex; }
</style>

<h1>Disaster Report #<?= $reportId ?></h1>
<p class="detail-subtitle">Review complete report information, submitted evidence, and current response assignment state.</p>

<section class="detail-card" aria-label="Report summary">
  <h2>Report Summary</h2>
  <div class="meta-grid">
    <div class="meta-item">
      <strong>Status</strong>
      <span class="status-pill <?= e($statusClass) ?>"><?= e($status) ?></span>
    </div>
    <div class="meta-item">
      <strong>Disaster Type</strong>
      <span><?= e($disasterTypeLabel !== '' ? $disasterTypeLabel : '-') ?></span>
    </div>
    <div class="meta-item">
      <strong>Occurred At</strong>
      <span><?= e($disasterDateTime) ?></span>
    </div>
    <div class="meta-item">
      <strong>Submitted At</strong>
      <span><?= e($submittedAt) ?></span>
    </div>
    <div class="meta-item">
      <strong>Verified At</strong>
      <span><?= e($verifiedAt) ?></span>
    </div>
    <div class="meta-item">
      <strong>Confirmation</strong>
      <span><?= (int) ($report['confirmation'] ?? 0) === 1 ? 'Confirmed' : 'Not confirmed' ?></span>
    </div>
  </div>
</section>

<div class="detail-grid">
  <section class="detail-card" aria-label="Reporter details">
    <h2>Reporter Details</h2>
    <div class="meta-grid">
      <div class="meta-item">
        <strong>Reported By</strong>
        <span><?= e((string) ($report['reporter_name'] ?? '-')) ?></span>
      </div>
      <div class="meta-item">
        <strong>Contact Number</strong>
        <span><?= e((string) ($report['contact_number'] ?? '-')) ?></span>
      </div>
      <div class="meta-item" style="grid-column: 1 / -1;">
        <strong>Location</strong>
        <span><?= e($locationLabel !== '' ? $locationLabel : '-') ?></span>
      </div>
    </div>
  </section>

  <section class="detail-card" aria-label="Report notes">
    <h2>Description / Notes</h2>
    <div class="description-box"><?= e((string) ($report['description'] ?? '-')) ?></div>
  </section>
</div>

<section class="detail-card" aria-label="Proof image">
  <h2>Attached Evidence</h2>
  <?php if ($proofImagePath === ''): ?>
    <p class="detail-subtitle" style="margin:0;">No image evidence was attached to this report.</p>
  <?php else: ?>
    <div class="proof-wrap">
      <div class="proof-preview">
        <img src="<?= e($proofImagePath) ?>" alt="Disaster report evidence image">
      </div>
      <div>
        <a href="<?= e($proofImagePath) ?>" target="_blank" rel="noopener" class="btn">Open Full Image</a>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="detail-card" aria-label="Volunteer assignments">
  <h2>Assigned Volunteers (<?= $assignedCount ?>)</h2>

  <?php if (empty($assignedVolunteers)): ?>
    <p class="detail-subtitle" style="margin:0;">No volunteers have been assigned to this report yet.</p>
  <?php else: ?>
    <div class="volunteer-stack">
      <?php foreach ($assignedVolunteers as $volunteer): ?>
        <?php $notes = (array) ($volunteer['notes'] ?? []); ?>
        <article class="volunteer-item">
          <div class="volunteer-head">
            <span class="volunteer-name"><?= e((string) ($volunteer['volunteer_name'] ?? 'Volunteer')) ?></span>
            <span class="volunteer-status"><?= e((string) ($volunteer['status'] ?? '-')) ?></span>
          </div>
          <div class="volunteer-meta">
            Assigned at: <?= e((string) ($volunteer['date_assigned'] ?? '-')) ?>
          </div>

          <?php if (!empty($notes)): ?>
            <ul class="volunteer-notes">
              <?php foreach ($notes as $note): ?>
                <?php $noteText = trim((string) ($note['update_text'] ?? '')); ?>
                <?php if ($noteText === '') continue; ?>
                <?php $stageLabel = trim((string) ($note['stage_status'] ?? '')) ?: 'Update'; ?>
                <li><strong><?= e($stageLabel) ?>:</strong> <?= e($noteText) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="actions-row">
    <a href="/dashboard/reports" class="btn">Back to Reports</a>

    <?php if ($status === 'Pending'): ?>
      <form method="POST" action="/dashboard/reports/<?= $reportId ?>/verify" class="inline-form">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary">Verify Report</button>
      </form>
      <form method="POST" action="/dashboard/reports/<?= $reportId ?>/reject" class="inline-form">
        <?= csrf_field() ?>
        <button type="submit" class="btn" style="border-color:#d91e18;color:#d91e18;">Reject Report</button>
      </form>
    <?php elseif ($status === 'Approved'): ?>
      <form method="POST" action="/dashboard/reports/<?= $reportId ?>/assign-volunteers" class="inline-form">
        <?= csrf_field() ?>
        <button type="submit" class="btn">Assign Volunteers</button>
      </form>
    <?php endif; ?>
  </div>
</section>
