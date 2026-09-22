<?php

$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    return $modx->lexicon('growattstats_err_class');
}
$comparison = new GrowattStats\Comparison($modx, $growattstats);
return $comparison->render('echarts', $scriptProperties);
