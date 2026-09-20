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
    return false;
}

$result = $growattstats->refreshCache();
$modx->log(
    modX::LOG_LEVEL_INFO,
    '[growattStats] CronDataUpdate: ' . ($result ? 'success' : 'failed')
);

return $result;
