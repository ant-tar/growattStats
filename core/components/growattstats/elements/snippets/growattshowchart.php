<?php

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    return $modx->lexicon('growattstats_err_class');
}

$tpl = $modx->getOption('tpl', $scriptProperties, 'growattShowChart');
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '');

$data = $growattstats->getDisplayData($scriptProperties);
if (!$data) {
    return $modx->lexicon('growattstats_err_api');
}

$growattstats->registerAssets();
$growattstats->registerChartScript($data['series'] ?? []);

$output = $modx->getChunk($tpl, $data);

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}

return $output;
