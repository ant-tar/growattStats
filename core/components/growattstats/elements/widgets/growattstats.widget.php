<?php

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    echo $modx->lexicon('growattstats_err_class');
    return;
}

$data = $growattstats->getDisplayData([
    'plantName' => $modx->getOption('growattstats_plant_name', null, ''),
]);
if (empty($data)) {
    echo $modx->lexicon('growattstats_err_api');
    return;
}

$growattstats->registerAssets();
$growattstats->registerChartScript($data['series'] ?? []);

echo $modx->getChunk('growattShowChart', $data);

