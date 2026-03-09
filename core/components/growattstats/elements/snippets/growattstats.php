<?php
/**
 * growattStats
 *
 * Псевдоним growattShowChart — выводит виджет статистики солнечной генерации.
 * Используйте [[!growattShowChart]] или [[!growattStats]] — оба работают одинаково.
 *
 * Параметры: см. сниппет growattShowChart
 *
 * @var modX  $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('growattstats_core_path', null,
    MODX_CORE_PATH . 'components/growattstats/');

return include $corePath . 'elements/snippets/growattshowchart.php';
