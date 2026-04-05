<?php
$location = $location ?? [];
$pendingRequests = $pending_requests ?? [];
$packDefinitions = $pack_definitions ?? [];
$additionalCatalog = $additional_catalog ?? [];
$oldInput = $_SESSION['_old_input'] ?? [];

$oldScalar = static function (string $key, string $default = '') use ($oldInput): string {
    return e((string) ($oldInput[$key] ?? $default));
};

$oldPack = static function (string $key, string $default = '0') use ($oldInput): string {
    $packs = (array) ($oldInput['packs'] ?? []);
    return e((string) ($packs[$key] ?? $default));
};

$oldExtra = static function (string $category, string $itemName, string $default = '0') use ($oldInput): string {
    $extras = (array) ($oldInput['extras'] ?? []);
    $categoryItems = (array) ($extras[$category] ?? []);
    return e((string) ($categoryItems[$itemName] ?? $default));
};

$locationLabel = trim((string) ($location['district'] ?? '') . ' / ' . (string) ($location['gn_division'] ?? ''));

$packDailyRequirements = [
    'toddlers' => [
        'Food & Water' => [
            ['item' => 'Water', 'quantity' => '1.2 L'],
            ['item' => 'Infant cereal / Porridge', 'quantity' => '200 g'],
            ['item' => 'Milk formula', 'quantity' => '50-60 g'],
            ['item' => 'Fruit puree', 'quantity' => '80 g'],
            ['item' => 'Soft snacks', 'quantity' => '50 g'],
        ],
        'Medicine & Health' => [
            ['item' => 'ORS', 'quantity' => '1 sachet'],
            ['item' => 'Baby paracetamol', 'quantity' => '1 dose'],
        ],
        'Shelter & Hygiene' => [
            ['item' => 'Diapers', 'quantity' => '4-5 pcs'],
            ['item' => 'Baby wipes', 'quantity' => '10 pcs'],
            ['item' => 'Baby soap', 'quantity' => '1 small bar'],
            ['item' => 'Baby blanket', 'quantity' => '1/week'],
        ],
    ],
    'children' => [
        'Food & Water' => [
            ['item' => 'Water', 'quantity' => '2 L'],
            ['item' => 'Rice / Rotis / Noodles', 'quantity' => '350 g'],
            ['item' => 'High-Energy Biscuits', 'quantity' => '150 g'],
            ['item' => 'Protein - Eggs / Beans', 'quantity' => '100 g'],
            ['item' => 'Milk powder', 'quantity' => '30 g'],
        ],
        'Medicine & Health' => [
            ['item' => 'ORS', 'quantity' => '1 sachet'],
            ['item' => 'Child-dose Paracetamol', 'quantity' => '1-2 tablets'],
            ['item' => 'Basic first aid ointment', 'quantity' => 'Small portion'],
        ],
        'Shelter & Hygiene' => [
            ['item' => 'Blanket', 'quantity' => '1/week'],
            ['item' => 'Soap', 'quantity' => '1/4 bar/day'],
            ['item' => 'Wet wipes', 'quantity' => '3 pcs'],
            ['item' => 'Toothpaste & brush', 'quantity' => 'Weekly kit'],
        ],
    ],
    'adults' => [
        'Food & Water' => [
            ['item' => 'Water', 'quantity' => '2.5 L'],
            ['item' => 'Rice / Noodles / Rotis', 'quantity' => '400 g'],
            ['item' => 'High-Energy Biscuits', 'quantity' => '200 g'],
            ['item' => 'Protein - Fish / Eggs / Beans', 'quantity' => '150 g'],
            ['item' => 'Tea / Sugar', 'quantity' => '30 g'],
        ],
        'Medicine & Health' => [
            ['item' => 'First aid basics', 'quantity' => 'Shared portion'],
            ['item' => 'ORS', 'quantity' => '1 sachet'],
            ['item' => 'Pain relief tablets', 'quantity' => '1-2 tablets'],
        ],
        'Shelter & Hygiene' => [
            ['item' => 'Blanket', 'quantity' => '1/week'],
            ['item' => 'Soap', 'quantity' => '1/3 bar/day'],
            ['item' => 'Wet wipes', 'quantity' => '3 pcs'],
            ['item' => 'Toothpaste & brush', 'quantity' => 'Weekly kit'],
        ],
    ],
    'pregnant_women' => [
        'Food & Water' => [
            ['item' => 'Water', 'quantity' => '3 L'],
            ['item' => 'Rice / Porridge', 'quantity' => '400 g'],
            ['item' => 'High-Energy Biscuits', 'quantity' => '250 g'],
            ['item' => 'Protein - Eggs / Beans / Fish', 'quantity' => '150 g'],
            ['item' => 'Milk powder', 'quantity' => '50 g'],
        ],
        'Medicine & Health' => [
            ['item' => 'Prenatal vitamins', 'quantity' => '1 dose'],
            ['item' => 'Iron Folic Acid tablets', 'quantity' => '1 dose'],
            ['item' => 'ORS', 'quantity' => '1 sachet'],
            ['item' => 'Paracetamol', 'quantity' => '1-2 tablets'],
        ],
        'Shelter & Hygiene' => [
            ['item' => 'Blanket', 'quantity' => '1/week'],
            ['item' => 'Soap', 'quantity' => '1/4 bar/day'],
            ['item' => 'Wet wipes', 'quantity' => '5 pcs'],
            ['item' => 'Sanitary pads', 'quantity' => '3 pcs'],
            ['item' => 'Toothpaste & brush', 'quantity' => 'Weekly kit'],
        ],
    ],
    'elderly' => [
        'Food & Water' => [
            ['item' => 'Water', 'quantity' => '2.5 L'],
            ['item' => 'Soft foods / Porridge', 'quantity' => '350 g'],
            ['item' => 'High-Energy Biscuits', 'quantity' => '200 g'],
            ['item' => 'Protein - Fish / Eggs / Beans', 'quantity' => '120 g'],
            ['item' => 'Fruit puree', 'quantity' => '100 g'],
        ],
        'Medicine & Health' => [
            ['item' => 'Chronic meds - BP / Diabetes', 'quantity' => 'Daily dose'],
            ['item' => 'ORS', 'quantity' => '1 sachet'],
            ['item' => 'Paracetamol', 'quantity' => '1-2 tablets'],
        ],
        'Shelter & Hygiene' => [
            ['item' => 'Blanket', 'quantity' => '1/week'],
            ['item' => 'Soap', 'quantity' => '1/2 bar/day'],
            ['item' => 'Wet wipes', 'quantity' => '4 pcs'],
            ['item' => 'Adult diapers', 'quantity' => '2 pcs if needed'],
            ['item' => 'Toothpaste & brush', 'quantity' => 'Weekly kit'],
        ],
    ],
];
?>

<style>
  .donation-gather-grid {
    display: grid;
    gap: 1rem;
  }

  .donation-pack-grid {
    display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 0.75rem;
  }

    .donation-pack-card {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        background: #fff;
        padding: 0.85rem;
    }

    .donation-pack-note {
        margin: 0.45rem 0 0.35rem;
        font-size: 0.7rem;
        color: #555;
        font-weight: 600;
    }

    .donation-pack-category + .donation-pack-category {
        margin-top: 0.55rem;
    }

    .donation-pack-category-title {
        margin: 0 0 0.25rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #555;
    }

    .donation-pack-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        overflow: hidden;
        font-size: 0.68rem;
    }

    .donation-pack-table th,
    .donation-pack-table td {
        text-align: left;
        padding: 0.35rem 0.45rem;
        border-bottom: 1px solid var(--color-border);
        vertical-align: top;
    }

    .donation-pack-table thead th {
        background: var(--color-surface-alt);
        font-weight: 700;
    }

    .donation-pack-table tbody tr:last-child td {
        border-bottom: none;
    }

  .donation-extra-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 0.75rem;
  }

  .donation-extra-card {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    background: #fff;
    padding: 0.85rem;
  }

  .donation-extra-card h3 {
    margin: 0 0 0.45rem;
    font-size: 0.86rem;
  }

  .donation-pending-list {
    margin: 0.6rem 0 0;
    padding-left: 1rem;
    color: #555;
    font-size: 0.76rem;
  }
</style>

<section class="welcome">
    <h1>Gather Requirement</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="clipboard-check"></span>
        <p>Enter pack counts and additional item quantities. The system computes item-wise totals for DMC and NGOs.</p>
    </div>
</section>

<section class="section-card" aria-label="Safe location summary">
    <h2 style="margin-top:0;">Safe Location</h2>
    <div class="form-grid-2">
        <div><strong>Relief Center Name</strong><br><span class="muted"><?= e((string) ($location['location_name'] ?? '-')) ?></span></div>
        <div><strong>Location</strong><br><span class="muted"><?= e($locationLabel !== '' ? $locationLabel : '-') ?></span></div>
    </div>

    <?php if (!empty($pendingRequests)): ?>
        <h3 style="margin:0.85rem 0 0.4rem; font-size:0.86rem;">Pending Requests for This Safe Location</h3>
        <ul class="donation-pending-list">
            <?php foreach ($pendingRequests as $request): ?>
                <li>
                    #<?= (int) ($request['request_id'] ?? 0) ?>
                    - <?= e((string) ($request['requester_name'] ?? 'User')) ?>
                    (<?= e((string) ($request['submitted_at'] ?? '-')) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    </section>

<section class="section-card" aria-label="Gather requirement form">
    <h2 style="margin-top:0;">Requirement Form</h2>

    <form method="POST" action="/dashboard/gn/donation-requests/<?= (int) ($location['location_id'] ?? 0) ?>/gather" class="donation-gather-grid">
        <?= csrf_field() ?>

        <div class="form-grid-2">
            <div class="form-group" style="margin:0;">
                <label for="contact_number">Contact Number</label>
                <input id="contact_number" class="input" type="text" name="contact_number" value="<?= $oldScalar('contact_number') ?>" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label for="days_count">Approximate Number of Days Staying</label>
                <input id="days_count" class="input" type="number" min="1" name="days_count" value="<?= $oldScalar('days_count', '1') ?>" required>
            </div>
        </div>

        <div class="form-group" style="margin:0;">
            <label for="situation_description">Situation Description</label>
            <textarea id="situation_description" class="input" name="situation_description" rows="3" required><?= $oldScalar('situation_description') ?></textarea>
        </div>

        <div class="form-group" style="margin:0;">
            <label for="special_notes">Special Notes</label>
            <textarea id="special_notes" class="input" name="special_notes" rows="3"><?= $oldScalar('special_notes') ?></textarea>
        </div>

        <div>
            <h3 style="margin:0 0 0.45rem;">Pack Counts by Population Group</h3>
            <div class="donation-pack-grid">
                <?php foreach ($packDefinitions as $packKey => $packDef): ?>
                    <?php $packRequirements = (array) ($packDailyRequirements[(string) $packKey] ?? []); ?>
                    <div class="donation-pack-card">
                        <div class="form-group" style="margin:0;">
                            <label for="pack_<?= e((string) $packKey) ?>"><?= e((string) ($packDef['label'] ?? $packKey)) ?></label>
                            <input id="pack_<?= e((string) $packKey) ?>" class="input" type="number" min="0" name="packs[<?= e((string) $packKey) ?>]" value="<?= $oldPack((string) $packKey, '0') ?>">
                        </div>

                        <?php if (!empty($packRequirements)): ?>
                            <p class="donation-pack-note">Daily requirement per 1 pack:</p>
                            <?php foreach ($packRequirements as $section => $rows): ?>
                                <div class="donation-pack-category">
                                    <p class="donation-pack-category-title"><?= e((string) $section) ?></p>
                                    <table class="donation-pack-table" aria-label="<?= e((string) $section) ?> daily requirement">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ((array) $rows as $row): ?>
                                                <tr>
                                                    <td><?= e((string) ($row['item'] ?? '')) ?></td>
                                                    <td><?= e((string) ($row['quantity'] ?? '')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <h3 style="margin:0 0 0.45rem;">Additional Item Quantities</h3>
            <div class="donation-extra-grid">
                <?php foreach ($additionalCatalog as $category => $itemNames): ?>
                    <section class="donation-extra-card">
                        <h3><?= e((string) $category) ?></h3>

                        <?php foreach ($itemNames as $itemName): ?>
                            <div class="form-group" style="margin:0 0 0.45rem;">
                                <label for="extra_<?= e((string) md5((string) $category . '|' . (string) $itemName)) ?>"><?= e((string) $itemName) ?></label>
                                <input
                                    id="extra_<?= e((string) md5((string) $category . '|' . (string) $itemName)) ?>"
                                    class="input"
                                    type="number"
                                    min="0"
                                    name="extras[<?= e((string) $category) ?>][<?= e((string) $itemName) ?>]"
                                    value="<?= $oldExtra((string) $category, (string) $itemName, '0') ?>"
                                >
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Submit Requirement</button>
            <a href="/dashboard/gn/donation-requests" class="btn" style="margin-left:0.35rem;">Back</a>
        </div>
    </form>
</section>
