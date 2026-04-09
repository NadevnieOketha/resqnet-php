<?php
$rows = is_array($rows ?? null) ? $rows : [];
$centralRows = is_array($central_rows ?? null) ? $central_rows : [];
$summary = is_array($summary ?? null) ? $summary : [];
$collectionPoints = is_array($collection_points ?? null) ? $collection_points : [];

$statusClass = static function (string $status): string {
    return match ($status) {
        'In Stock' => 'inv-status inv-status-green',
        'Low on Stock' => 'inv-status inv-status-orange',
        default => 'inv-status inv-status-red',
    };
};
?>

<style>
  .inv-filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
    margin-bottom: 0.85rem;
  }
  .inv-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
    margin-bottom: 0.9rem;
  }
  .inv-summary-card {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    background: #fff;
    padding: 0.75rem 0.85rem;
  }
  .inv-summary-card .label {
    font-size: 0.68rem;
    color: var(--color-muted);
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }
  .inv-summary-card .value {
    font-size: 1.25rem;
    font-weight: 700;
    margin-top: 0.18rem;
  }
  .inv-status {
    display: inline-block;
    border-radius: 999px;
    padding: 0.2rem 0.5rem;
    font-size: 0.67rem;
    font-weight: 700;
  }
  .inv-status-green {
    background: #e9f9ef;
    color: #1f7a3f;
  }
  .inv-status-orange {
    background: #fff2e5;
    color: #b85a00;
  }
  .inv-status-red {
    background: #ffe7e7;
    color: #a02121;
  }
  .inv-qty-input {
    width: 90px;
    display: inline-block;
  }
  .inv-subtitle {
    margin: 0 0 0.65rem;
    color: var(--color-muted);
    font-size: 0.76rem;
  }
  .inv-hidden {
    display: none !important;
  }
</style>

<section class="welcome">
    <h1>Inventory Management</h1>
    <div class="alert">
        <span class="alert-icon" data-lucide="boxes"></span>
        <p>Track stock by collection point, monitor low items, and update quantities inline. Inventory is auto-increased when donations are marked as received.</p>
    </div>
</section>

<section class="section-card" aria-label="Inventory summary">
    <h2 style="margin-top:0;">Stock Overview</h2>
    <div class="inv-summary-grid">
        <article class="inv-summary-card">
            <div class="label">Inventory Rows</div>
            <div class="value"><?= (int) ($summary['inventory_rows'] ?? 0) ?></div>
        </article>
        <article class="inv-summary-card">
            <div class="label">Total Units</div>
            <div class="value"><?= (int) ($summary['total_units'] ?? 0) ?></div>
        </article>
        <article class="inv-summary-card">
            <div class="label">Collection Points</div>
            <div class="value"><?= (int) ($summary['covered_points'] ?? 0) ?></div>
        </article>
        <article class="inv-summary-card">
            <div class="label">In Stock Rows</div>
            <div class="value"><?= (int) ($summary['in_stock_rows'] ?? 0) ?></div>
        </article>
        <article class="inv-summary-card">
            <div class="label">Low on Stock Rows</div>
            <div class="value"><?= (int) ($summary['low_stock_rows'] ?? 0) ?></div>
        </article>
        <article class="inv-summary-card">
            <div class="label">Out of Stock Rows</div>
            <div class="value"><?= (int) ($summary['out_stock_rows'] ?? 0) ?></div>
        </article>
    </div>
</section>

<section class="section-card" aria-label="Central inventory totals">
    <h2 style="margin-top:0;">Central Inventory (All Collection Points)</h2>
    <p class="inv-subtitle">This is your NGO-wide stock balance aggregated across all collection points.</p>

    <div class="table-shell">
        <table class="table" id="centralInventoryTable">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Total Quantity</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($centralRows as $row): ?>
                    <?php $status = (string) ($row['status'] ?? 'Out of Stock'); ?>
                    <tr>
                        <td><?= e((string) ($row['item_name'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['category'] ?? '-')) ?></td>
                        <td><?= (int) ($row['quantity'] ?? 0) ?></td>
                        <td><span class="<?= e($statusClass($status)) ?>"><?= e($status) ?></span></td>
                        <td><?= e((string) ($row['last_updated'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="section-card" aria-label="Collection point inventory table">
    <h2 style="margin-top:0;">Collection Point Inventory</h2>
    <p class="inv-subtitle">Use filters for instant client-side search without reloading this page.</p>

    <div class="inv-filter-row">
        <div class="form-group" style="margin:0;">
            <label for="inv_search">Search Item</label>
            <input type="text" class="input" id="inv_search" placeholder="Type item name">
        </div>

        <div class="form-group" style="margin:0;">
            <label for="inv_category">Category</label>
            <select class="input" id="inv_category">
                <option value="">All categories</option>
                <option value="Medicine">Medicine</option>
                <option value="Food">Food</option>
                <option value="Shelter">Shelter</option>
            </select>
        </div>

        <div class="form-group" style="margin:0;">
            <label for="inv_collection_point">Collection Point</label>
            <select class="input" id="inv_collection_point">
                <option value="">All collection points</option>
                <?php foreach ($collectionPoints as $cp): ?>
                    <option value="<?= e((string) ($cp['collection_point_id'] ?? '')) ?>"><?= e((string) ($cp['name'] ?? '-')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin:0;">
            <label for="inv_status">Status</label>
            <select class="input" id="inv_status">
                <option value="">All statuses</option>
                <option value="In Stock">In Stock</option>
                <option value="Low on Stock">Low on Stock</option>
                <option value="Out of Stock">Out of Stock</option>
            </select>
        </div>
    </div>

    <div class="table-shell">
        <table class="table" id="inventoryTable">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Collection Point</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th style="text-align:right;">Update Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $status = (string) ($row['status'] ?? 'Out of Stock');
                    $collectionPointId = (int) ($row['collection_point_id'] ?? 0);
                    ?>
                    <tr
                        data-item-name="<?= e(strtolower((string) ($row['item_name'] ?? ''))) ?>"
                        data-category="<?= e((string) ($row['category'] ?? '')) ?>"
                        data-collection-point-id="<?= $collectionPointId ?>"
                        data-status="<?= e($status) ?>"
                    >
                        <td><?= e((string) ($row['item_name'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['category'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['collection_point_name'] ?? '-')) ?></td>
                        <td><?= (int) ($row['quantity'] ?? 0) ?></td>
                        <td><span class="<?= e($statusClass($status)) ?>"><?= e($status) ?></span></td>
                        <td><?= e((string) ($row['last_updated'] ?? '-')) ?></td>
                        <td style="text-align:right; white-space:nowrap;">
                            <form method="POST" action="/dashboard/ngo/inventory/<?= (int) ($row['inventory_id'] ?? 0) ?>/quantity" class="inline-form">
                                <?= csrf_field() ?>
                                <input
                                    type="number"
                                    min="0"
                                    class="input inv-qty-input"
                                    name="quantity"
                                    value="<?= (int) ($row['quantity'] ?? 0) ?>"
                                    required
                                >
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
(function () {
    const searchEl = document.getElementById('inv_search');
    const categoryEl = document.getElementById('inv_category');
    const collectionPointEl = document.getElementById('inv_collection_point');
    const statusEl = document.getElementById('inv_status');
    const table = document.getElementById('inventoryTable');

    if (!table) {
        return;
    }

    const rows = Array.from(table.querySelectorAll('tbody tr'));

    function applyFilters() {
        const searchValue = (searchEl?.value || '').trim().toLowerCase();
        const categoryValue = categoryEl?.value || '';
        const collectionValue = collectionPointEl?.value || '';
        const statusValue = statusEl?.value || '';

        rows.forEach((row) => {
            const itemName = row.getAttribute('data-item-name') || '';
            const category = row.getAttribute('data-category') || '';
            const collectionPointId = row.getAttribute('data-collection-point-id') || '';
            const status = row.getAttribute('data-status') || '';

            const matchesSearch = searchValue === '' || itemName.includes(searchValue);
            const matchesCategory = categoryValue === '' || category === categoryValue;
            const matchesCollectionPoint = collectionValue === '' || collectionPointId === collectionValue;
            const matchesStatus = statusValue === '' || status === statusValue;

            if (matchesSearch && matchesCategory && matchesCollectionPoint && matchesStatus) {
                row.classList.remove('inv-hidden');
            } else {
                row.classList.add('inv-hidden');
            }
        });
    }

    [searchEl, categoryEl, collectionPointEl, statusEl].forEach((el) => {
        if (!el) {
            return;
        }
        const eventName = el.tagName === 'INPUT' ? 'input' : 'change';
        el.addEventListener(eventName, applyFilters);
    });
})();
</script>
