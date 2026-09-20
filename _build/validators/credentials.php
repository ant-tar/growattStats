<?php

if (!isset($transport) || !($transport instanceof xPDOTransport)) {
    return false;
}
$action = $options[xPDOTransport::PACKAGE_ACTION] ?? null;
if (!in_array($action, [xPDOTransport::ACTION_INSTALL, xPDOTransport::ACTION_UPGRADE], true)) {
    return true;
}
$modx = $transport->xpdo;
$cronPath = $modx->getOption('cronmanager.core_path', null, MODX_CORE_PATH . 'components/cronmanager/');
if (!is_file($cronPath . 'model/cronmanager/modcronjob.class.php')) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] CronManager must be installed first.');
    return false;
}
$values = [];
foreach (['plant_id', 'token'] as $name) {
    $key = 'growattstats_' . $name;
    $value = trim((string) ($options[$key] ?? ''));
    if ($value === '') {
        $setting = $modx->getObject('modSystemSetting', ['key' => $key]);
        $value = $setting ? trim((string) $setting->get('value')) : '';
    }
    if ($name === 'token' && $value === '') {
        $legacy = $modx->getObject('modSystemSetting', ['key' => 'growattstats_token_id']);
        $value = $legacy ? trim((string) $legacy->get('value')) : '';
    }
    if ($value === '') {
        $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] API token and Plant ID are required.');
        return false;
    }
    $values[$key] = $value;
}
return true;
