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
foreach ($values as $key => $value) {
    $setting = $modx->getObject('modSystemSetting', ['key' => $key]);
    if (!$setting) {
        $setting = $modx->newObject('modSystemSetting');
        $setting->fromArray([
            'key' => $key,
            'namespace' => 'growattstats',
            'area' => 'growattstats_main',
            'xtype' => $key === 'growattstats_token' ? 'text-password' : 'textfield',
        ], '', true, true);
    }
    if ($key === 'growattstats_token') {
        $setting->set('xtype', 'text-password');
    }
    $setting->set('value', $value);
    if (!$setting->save()) {
        return false;
    }
}
$modx->cacheManager->refresh(['system_settings' => []]);
return true;
