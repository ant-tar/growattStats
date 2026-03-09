<?php
/**
 * growattStats Dashboard Widget
 *
 * Отображает статистику солнечной генерации на дашборде MODX.
 * Тип виджета: file
 *
 * @var modX $modx
 */

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null,
        MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    echo $modx->lexicon('growattstats_err_class');
    return;
}

// Кэш на 5 минут
$data = $modx->cacheManager->get('growattstats_apidata');
if (empty($data)) {
    $data = $growattstats->fetchApiData();
    if (!$data) {
        echo $modx->lexicon('growattstats_err_api');
        return;
    }
    $price = (float)$modx->getOption('growattstats_price', null, 1.20);
    $data['today_revenue'] = round($price * (float)$data['today_energy'], 2);
    $data['total_revenue'] = round($price * (float)$data['total_energy'], 2);
    $modx->cacheManager->set('growattstats_apidata', $data, 300);
}

$data['plant_name'] = $modx->getOption('growattstats_plant_name', null, '');

// Подключить ресурсы (работает и в контексте admin)
$growattstats->registerAssets();
$growattstats->registerChartScript();

echo $modx->getChunk('growattShowChart', $data);
