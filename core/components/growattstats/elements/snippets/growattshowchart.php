<?php
/**
 * growattShowChart
 *
 * Выводит виджет статистики солнечной генерации через чанк growattShowChart.
 *
 * Параметры:
 *   &tpl        string  Имя чанка-шаблона. По умолчанию: growattShowChart
 *   &price      float   Цена за 1 kWh для расчёта дохода. По умолчанию: 1.20
 *   &plantName  string  Название установки для заголовка графика
 *   &toPlaceholder string  Если задан — вывод помещается в плейсхолдер, снипет возвращает ''
 *
 * @var modX  $modx
 * @var array $scriptProperties
 */

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null,
        MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    return $modx->lexicon('growattstats_err_class');
}

$tpl           = $modx->getOption('tpl',           $scriptProperties, 'growattShowChart');
$price         = (float)$modx->getOption('price',  $scriptProperties,
    $modx->getOption('growattstats_price', null, 1.20));
$plantName     = $modx->getOption('plantName',     $scriptProperties, '');
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '');

// Кэш на 5 минут
$data = $modx->cacheManager->get('growattstats_apidata');
if (empty($data)) {
    $data = $growattstats->fetchApiData();
    if (!$data) {
        return $modx->lexicon('growattstats_err_api');
    }
    $data['today_revenue'] = round($price * (float)$data['today_energy'], 2);
    $data['total_revenue'] = round($price * (float)$data['total_energy'], 2);
    $modx->cacheManager->set('growattstats_apidata', $data, 300);
}

$data['plant_name'] = $plantName;

$growattstats->registerAssets();
$growattstats->registerChartScript();

$output = $modx->getChunk($tpl, $data);

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}

return $output;
