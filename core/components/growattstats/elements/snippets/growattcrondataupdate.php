<?php

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    $message = '[growattStats] Could not load growattStats class';
    $modx->log(modX::LOG_LEVEL_ERROR, $message);
    return $message;
}

$message = $growattstats->refreshCacheMessage();
$level = strpos($message, 'successfully') !== false ? modX::LOG_LEVEL_INFO : modX::LOG_LEVEL_ERROR;
$modx->log($level, $message);

return $message;

