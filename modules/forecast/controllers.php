<?php

/**
 * Forecast Module - Controllers
 */

function forecast_dashboard_index(): void
{
    $requestedRiverKey = trim((string) request_query('river', ''));
    $requestedStationKey = trim((string) request_query('station', ''));

    $snapshot = forecast_snapshot();
    $defaultSelection = forecast_default_selection($snapshot, $requestedRiverKey, $requestedStationKey);

    view('forecast::index', [
        'breadcrumb' => 'River Forecast',
        'rainfall_snapshot' => $snapshot,
        'default_selection' => $defaultSelection,
    ], 'dashboard');
}
