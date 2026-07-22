<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return false;
}

$modx =& $transport->xpdo;

$saveSetting = static function (modX $modx, $key, $value) {
    $setting = $modx->getObject('modSystemSetting', ['key' => $key]);
    if (!$setting) {
        $setting = $modx->newObject('modSystemSetting');
        $setting->fromArray([
            'key' => $key,
            'namespace' => 'growattstats',
        ], '', true, true);
    }

    $setting->set('value', $value);
    return (bool)$setting->save();
};

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $apiUrl = trim((string)($modx->getOption('growattstats_api_url', null, 'https://openapi.growatt.com/v1/plant/data')));
        $plantId = trim((string)($options['plant_id'] ?? ''));
        $tokenId = trim((string)($options['token_id'] ?? ''));
        $plantName = trim((string)($options['plant_name'] ?? ''));
        $price = trim((string)($options['price'] ?? '1.20'));

        if ($plantId === '' || $tokenId === '') {
            $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] Plant ID and API Token are required before installation.');
            return false;
        }

        if (
            !$saveSetting($modx, 'growattstats_api_url', $apiUrl) ||
            !$saveSetting($modx, 'growattstats_plant_id', $plantId) ||
            !$saveSetting($modx, 'growattstats_token_id', $tokenId) ||
            !$saveSetting($modx, 'growattstats_plant_name', $plantName) ||
            !$saveSetting($modx, 'growattstats_price', $price)
        ) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] Could not save system settings.');
            return false;
        }

        if ($modx->cacheManager) {
            $modx->cacheManager->refresh([
                'system_settings' => [],
            ]);
        }
        break;
}

return true;
