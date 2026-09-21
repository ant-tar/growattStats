<?php

if (!isset($transport) || !($transport instanceof xPDOTransport)) {
    return false;
}
$action = $options[xPDOTransport::PACKAGE_ACTION] ?? null;
if (!in_array($action, [xPDOTransport::ACTION_INSTALL, xPDOTransport::ACTION_UPGRADE], true)) {
    return true;
}
$modx = $transport->xpdo;
$translate = require dirname(__DIR__) . '/setup-lexicon.php';
$cronPath = $modx->getOption('cronmanager.core_path', null, MODX_CORE_PATH . 'components/cronmanager/');
if (!is_file($cronPath . 'model/cronmanager/modcronjob.class.php')) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] ' . $translate('growattstats_setup_dependency'));
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
        $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] ' . $translate('growattstats_setup_required'));
        return false;
    }
    $values[$key] = $value;
}
return true;
