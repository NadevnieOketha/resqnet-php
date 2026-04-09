<?php

/**
 * Inventory Module - Controllers
 */

function inventory_ngo_index(): void
{
    $ngoUserId = (int) auth_id();

    inventory_ensure_ngo_baseline_rows($ngoUserId);

    $rows = inventory_list_for_ngo($ngoUserId);
    $centralRows = inventory_list_central_totals_for_ngo($ngoUserId);

    view('inventory::manage', [
        'breadcrumb' => 'Inventory Management',
        'rows' => $rows,
        'central_rows' => $centralRows,
        'summary' => inventory_summary_counts($ngoUserId),
        'collection_points' => inventory_collection_point_options($ngoUserId),
    ], 'dashboard');
}

function inventory_ngo_update_quantity_action(string $inventoryId): void
{
    csrf_check();

    $ngoUserId = (int) auth_id();
    $id = (int) $inventoryId;

    if ($id <= 0) {
        flash('error', 'Invalid inventory row id.');
        redirect('/dashboard/ngo/inventory');
    }

    $quantityRaw = request_input('quantity', '');
    if ($quantityRaw === '' || !is_numeric((string) $quantityRaw)) {
        flash('error', 'Quantity must be a valid number.');
        redirect('/dashboard/ngo/inventory');
    }

    $quantity = (int) $quantityRaw;
    if ($quantity < 0) {
        flash('error', 'Quantity cannot be negative.');
        redirect('/dashboard/ngo/inventory');
    }

    $row = inventory_find_for_ngo($id, $ngoUserId);
    if (!$row) {
        flash('error', 'Inventory row not found or access denied.');
        redirect('/dashboard/ngo/inventory');
    }

    $updated = inventory_update_quantity_for_ngo($id, $ngoUserId, $quantity);

    if ($updated > 0) {
        flash('success', 'Inventory quantity updated successfully.');
    } else {
        flash('warning', 'No inventory changes detected.');
    }

    redirect('/dashboard/ngo/inventory');
}
