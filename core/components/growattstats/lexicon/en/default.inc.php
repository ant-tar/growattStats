<?php

include __DIR__ . '/setting.inc.php';

$_lang['growattstats'] = 'growattStats';
$_lang['growattstats_err_class'] = 'Could not load growattStats class.';
$_lang['growattstats_err_api'] = 'Could not fetch data from Growatt API. Check api_url, token and plant_id settings.';
$_lang['growattstats_err_tpl'] = 'Could not load growattStats widget template.';
$_lang['growattstats_widget_title'] = 'Solar Generation Stats';
$_lang['growattstats_today_energy'] = 'Generation Today';
$_lang['growattstats_total_energy'] = 'Total Generation';
$_lang['growattstats_today_revenue'] = 'Revenue Today';
$_lang['growattstats_total_revenue'] = 'Total Revenue';
$_lang['growattstats_chart_note'] =
    'The graph shows daily electricity production from the solar installation. Use the range selector '
    . 'or drag the navigator to inspect the history.';

$_lang['growattstats_generation'] = 'Generation';
$_lang['growattstats_today'] = 'Today';
$_lang['growattstats_all_time'] = 'All time';
$_lang['growattstats_unit'] = 'kWh';
$_lang['growattstats_series'] = 'Generation (kWh)';
$_lang['growattstats_locale'] = 'en';
$_lang['growattstats_range_month'] = '1m';
$_lang['growattstats_range_quarter'] = '3m';
$_lang['growattstats_range_half_year'] = '6m';
$_lang['growattstats_range_ytd'] = 'YTD';
$_lang['growattstats_range_year'] = '1y';
$_lang['growattstats_range_all'] = 'All';
$_lang['growattstats_zoom'] = 'Zoom';
$_lang['growattstats_reset_zoom'] = 'Reset zoom';
$_lang['growattstats_reset_zoom_title'] = 'Reset zoom level 1:1';
$_lang['growattstats_loading'] = 'Loading...';
$_lang['growattstats_cron_started'] = 'Refresh started';
$_lang['growattstats_cron_success'] = 'Readings updated';
$_lang['growattstats_cron_failed'] = 'Refresh failed; previous data retained';
$_lang['growattstats_error_method'] = 'Unsupported HTTP method';
$_lang['growattstats_error_url'] = 'Empty API URL';
$_lang['growattstats_error_curl'] = 'PHP cURL is required';
$_lang['growattstats_error_body'] = 'Could not encode API body to JSON';
$_lang['growattstats_error_http'] = 'Unexpected HTTP status';
$_lang['growattstats_error_api'] = 'Growatt API error';
$_lang['growattstats_error_command'] = 'Unknown Growatt command';
$_lang['growattstats_error_directory'] = 'Could not create data directory';
$_lang['growattstats_error_json'] = 'Could not encode chart payload to JSON';
$_lang['growattstats_error_request'] = 'Plant data request failed; HTTP status';
$_lang['growattstats_desc_alias'] = 'Alias snippet for growattShowChart';
$_lang['growattstats_desc_chart'] = 'Display Growatt generation and history';
$_lang['growattstats_desc_cron'] = 'Refresh chart cache from Growatt API';
$_lang['growattstats_desc_widget'] = 'Solar generation dashboard chart';
