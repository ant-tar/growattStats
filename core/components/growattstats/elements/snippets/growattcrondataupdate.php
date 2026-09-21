<?php

$modx->lexicon->load('growattstats:default');
$modx->log(modX::LOG_LEVEL_INFO, '[growattStats] ' . $modx->lexicon('growattstats_cron_started'));

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] ' . $modx->lexicon('growattstats_err_class'));
    if (!empty($scriptProperties['CronManager'])) {
        return json_encode([
            'error' => true,
            'message' => '[growattStats] ' . $modx->lexicon('growattstats_err_class'),
        ]);
    }
    return false;
}

$result = $growattstats->refreshCache();
$modx->log(
    modX::LOG_LEVEL_INFO,
    '[growattStats] ' . $modx->lexicon($result ? 'growattstats_cron_success' : 'growattstats_cron_failed')
);

if (!empty($scriptProperties['CronManager'])) {
    return json_encode([
        'error' => !$result,
        'message' => $result
            ? '[growattStats] ' . $modx->lexicon('growattstats_cron_success')
            : '[growattStats] ' . $modx->lexicon('growattstats_cron_failed'),
    ]);
}
return $result;
