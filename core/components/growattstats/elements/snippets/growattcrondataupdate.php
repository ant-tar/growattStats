<?php
/**
 * growattCronDataUpdate
 *
 * Запрашивает данные с Growatt API и обновляет файл chart-data.js.
 * Предназначен для запуска по расписанию (например, через MODX Schedule или cron).
 *
 * Системные настройки:
 *   growattstats_plant_id  — ID установки в Growatt
 *   growattstats_token_id  — API-токен Growatt
 *
 * @var modX  $modx
 * @var array $scriptProperties
 */

$modx->log(modX::LOG_LEVEL_INFO, '[growattStats] CronDataUpdate запущен');

/** @var growattStats $growattstats */
$growattstats = $modx->getService(
    'growattstats',
    'growattStats',
    $modx->getOption('growattstats_core_path', null,
        MODX_CORE_PATH . 'components/growattstats/') . 'model/'
);
if (!$growattstats) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] Не удалось загрузить класс growattStats');
    return false;
}

$data = $growattstats->fetchApiData();
if (!$data) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] CronDataUpdate: нет данных от API');
    return false;
}

// Сбросить кэш, чтобы сниппет на фронтенде взял свежие данные
$modx->cacheManager->delete('growattstats_apidata');

$result = $growattstats->updateChartData($data);
$modx->log(modX::LOG_LEVEL_INFO,
    '[growattStats] CronDataUpdate: ' . ($result ? 'успешно' : 'ошибка записи файла'));

return $result;
