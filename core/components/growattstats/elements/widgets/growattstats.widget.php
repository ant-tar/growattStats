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

$data = $growattstats->getDisplayData([
    'plantName' => $modx->getOption('growattstats_plant_name', null, ''),
]);
if (empty($data)) {
    return $modx->lexicon('growattstats_err_api');
}

$tpl = $modx->getOption('tpl', $scriptProperties, 'growattShowWidget');

$output = $modx->getChunk($tpl, $data);
if (trim((string)$output) === '') {
    return $modx->lexicon('growattstats_err_tpl');
}

$output .= $growattstats->getAssetTags();
$output .= $growattstats->getChartScript($data['series'] ?? []);

return $output;

