<?php

$_lang['growattstats_setup_intro'] =
    'Enter your Growatt API credentials. Both fields are required for a new '
    . 'installation.';
$_lang['growattstats_setup_upgrade'] = 'On upgrade, leave fields blank to keep existing values.';
$_lang['growattstats_setup_token'] = 'Growatt API token';
$_lang['growattstats_setup_plant'] = 'Growatt Plant ID';
$_lang['growattstats_setup_cron'] = 'An active refresh job is created in CronManager every 15 minutes.';
$_lang['growattstats_setup_preserve'] = 'Existing job schedules are preserved.';
$_lang['growattstats_setup_external'] =
    'The external cron service must be running. Configure it to run this '
    . 'command every minute, using your server paths:';
$_lang['growattstats_setup_help'] = 'CronManager setup';
$_lang['growattstats_setup_crontab'] = 'Crontab syntax and documentation';
$_lang['growattstats_setup_required'] = 'Growatt API token and Plant ID are required.';
$_lang['growattstats_setup_dependency'] = 'Install CronManager before installing growattStats.';
$_lang['growattstats_setup_created'] =
    'Created a 15-minute refresh job. Schedule CronManager cron.php externally '
    . 'every minute.';
