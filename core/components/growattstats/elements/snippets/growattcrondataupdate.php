<?php

$modx->log(modX::LOG_LEVEL_INFO, '[growattStats] CronDataUpdate started');

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] Could not load growattStats class');
    if (!empty($scriptProperties['CronManager'])) {
        return json_encode(['error' => true, 'message' => '[growattStats] Could not load service']);
    }
    return false;
}

$result = $growattstats->refreshCache();
$modx->log(
    modX::LOG_LEVEL_INFO,
    '[growattStats] CronDataUpdate: ' . ($result ? 'success' : 'failed')
);

if (!empty($scriptProperties['CronManager'])) {
    return json_encode([
        'error' => !$result,
        'message' => $result
            ? '[growattStats] Readings updated' : '[growattStats] Refresh failed; previous data retained',
    ]);
}
return $result;
