<?php

/**
 * Forecast Module - Controllers
 */

function forecast_dashboard_index(): void
{
    $requestedRiverKey = trim((string) request_query('river', ''));
    $requestedStationKey = trim((string) request_query('station', ''));
    $profile = null;

    if (auth_check()) {
        $role = (string) (user_role() ?? '');
        $profile = auth_get_profile((int) auth_id(), $role);
    }

    $snapshot = forecast_snapshot();
    $defaultSelection = forecast_default_selection($snapshot, $requestedRiverKey, $requestedStationKey, $profile);

    $smsAlertPreference = [
        'enabled' => false,
        'river_key' => '',
        'station_key' => '',
        'fallback_river_key' => '',
        'fallback_station_key' => '',
    ];

    if (auth_check() && forecast_sms_alert_supported_role((string) (user_role() ?? ''))) {
        $smsAlertPreference = forecast_sms_alert_preference((int) auth_id(), (string) user_role(), $snapshot, $profile);
    }

    view('forecast::index', [
        'breadcrumb' => 'River Forecast',
        'rainfall_snapshot' => $snapshot,
        'default_selection' => $defaultSelection,
        'sms_alert_preference' => $smsAlertPreference,
        'role' => (string) (user_role() ?? ''),
    ], 'dashboard');
}

function forecast_sms_alert_update(): void
{
    csrf_check();

    if (!auth_check()) {
        redirect('/login');
    }

    $role = (string) (user_role() ?? '');
    if (!forecast_sms_alert_supported_role($role)) {
        abort(403, 'Only general users and volunteers can update forecast SMS alerts.');
    }

    $enabled = (string) request_input('sms_alert', '0') === '1';
    $riverKey = trim((string) request_input('sms_river_key', ''));
    $stationKey = trim((string) request_input('sms_station_key', ''));

    $profile = auth_get_profile((int) auth_id(), $role);
    $snapshot = forecast_snapshot();

    $result = forecast_sms_alert_save_preference(
        (int) auth_id(),
        $role,
        $enabled,
        $riverKey,
        $stationKey,
        $snapshot,
        $profile
    );

    if (!$result['enabled']) {
        flash('success', 'Forecast SMS alerts disabled.');
        redirect('/dashboard/forecast');
    }

    if (!empty($result['used_fallback'])) {
        flash('success', 'Forecast SMS alerts enabled. No gauge station was selected, so your closest GN-mapped station was assigned.');
        redirect('/dashboard/forecast');
    }

    flash('success', 'Forecast SMS alerts updated successfully.');
    redirect('/dashboard/forecast');
}
