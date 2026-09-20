<?php

if (!isset($transport) || !($transport instanceof xPDOTransport)) {
    return false;
}
$modx = $transport->xpdo;
$action = $options[xPDOTransport::PACKAGE_ACTION] ?? null;
$modelPath = $modx->getOption('cronmanager.core_path', null, MODX_CORE_PATH . 'components/cronmanager/');
if (!is_file($modelPath . 'model/cronmanager/modcronjob.class.php')) {
    if ($action === xPDOTransport::ACTION_UNINSTALL) {
        return true;
    }
    $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] Install CronManager before installing this package.');
    return false;
}
$modx->addPackage('cronmanager', $modelPath . 'model/');
$marker = '{"growattstats_managed_job":true}';
$snippet = $modx->getObject('modSnippet', ['name' => 'growattCronDataUpdate']);

if ($action === xPDOTransport::ACTION_UNINSTALL) {
    // An upgrade rollback may restore the previous snippet: keep its working job.
    if (!$snippet) {
        foreach ($modx->getCollection('modCronjob') as $job) {
            $properties = json_decode((string) $job->get('properties'), true);
            if (!empty($properties['growattstats_managed_job']) && !$job->remove()) {
                return false;
            }
        }
    }
    return true;
}
if (!in_array($action, [xPDOTransport::ACTION_INSTALL, xPDOTransport::ACTION_UPGRADE], true)) {
    return true;
}
if (!$snippet) {
    return false;
}
$snippetId = (int) $snippet->get('id');
if ($modx->getObject('modCronjob', ['snippet' => $snippetId])) {
    // Preserve existing jobs, including deliberate deactivation and custom intervals.
    return true;
}
$job = $modx->newObject('modCronjob');
$job->fromArray([
    'snippet' => $snippetId,
    'properties' => $marker,
    'minutes' => 15,
    'active' => true,
    'running' => false,
    'lastrun' => null,
    'nextrun' => null,
], '', true, true);
if (!$job->save()) {
    return false;
}
$modx->log(
    modX::LOG_LEVEL_INFO,
    '[growattStats] Created a 15-minute refresh job. Schedule CronManager cron.php externally every minute.'
);
return true;
