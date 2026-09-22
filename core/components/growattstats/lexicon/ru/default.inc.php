<?php

include __DIR__ . '/setting.inc.php';
$_lang['growattstats_compare_gap'] = 'Пунктир — пропуск суточных показаний, а не измеренная выработка.';
$_lang['growattstats_compare_start'] = 'Начало периода';
$_lang['growattstats_compare_end'] = 'Конец периода';
$_lang['growattstats_compare_empty'] = 'История показаний пока пуста.';
$_lang['growattstats_compare_error'] = 'Не удалось отобразить график.';
$_lang['growattstats_compare_period'] = 'Период графика';
$_lang['growattstats_compare_note'] =
    'Суточная выработка. Все сравниваемые графики используют одну историю, даты в UTC.';
$_lang['growattstats_desc_echarts'] = 'Просмотр истории Growatt через Apache ECharts';
$_lang['growattstats_desc_dygraphs'] = 'Просмотр истории Growatt через Dygraphs';
$_lang['growattstats_desc_chartjs'] = 'Просмотр истории Growatt через Chart.js';

$_lang['growattstats'] = 'growattStats';
$_lang['growattstats_err_class'] = 'Не удалось загрузить класс growattStats.';
$_lang['growattstats_err_api'] =
    'Не удалось получить данные от Growatt API. Проверьте настройки api_url, token и plant_id.';
$_lang['growattstats_err_tpl'] = 'Не удалось загрузить шаблон виджета growattStats.';
$_lang['growattstats_widget_title'] = 'Статистика генерации';
$_lang['growattstats_today_energy'] = 'Генерация сегодня';
$_lang['growattstats_total_energy'] = 'Всего генерации';
$_lang['growattstats_today_revenue'] = 'Доход сегодня';
$_lang['growattstats_total_revenue'] = 'Всего доход';
$_lang['growattstats_chart_note'] =
    'График показывает суточную выработку солнечной установки. Используйте переключатель диапазона или '
    . 'навигатор, чтобы просматривать историю.';

$_lang['growattstats_generation'] = 'Выработка';
$_lang['growattstats_today'] = 'Сегодня';
$_lang['growattstats_all_time'] = 'За всё время';
$_lang['growattstats_unit'] = 'кВт·ч';
$_lang['growattstats_series'] = 'Выработка (кВт·ч)';
$_lang['growattstats_locale'] = 'ru';
$_lang['growattstats_range_month'] = '1м';
$_lang['growattstats_range_quarter'] = '3м';
$_lang['growattstats_range_half_year'] = '6м';
$_lang['growattstats_range_ytd'] = 'С начала года';
$_lang['growattstats_range_year'] = '1г';
$_lang['growattstats_range_all'] = 'Всё';
$_lang['growattstats_zoom'] = 'Период';
$_lang['growattstats_reset_zoom'] = 'Сбросить масштаб';
$_lang['growattstats_reset_zoom_title'] = 'Вернуть масштаб 1:1';
$_lang['growattstats_loading'] = 'Загрузка...';
$_lang['growattstats_cron_started'] = 'Обновление начато';
$_lang['growattstats_cron_success'] = 'Показания обновлены';
$_lang['growattstats_cron_failed'] = 'Обновление не удалось; прежние данные сохранены';
$_lang['growattstats_error_method'] = 'Неподдерживаемый метод HTTP';
$_lang['growattstats_error_url'] = 'Не указан URL API';
$_lang['growattstats_error_curl'] = 'Необходимо расширение PHP cURL';
$_lang['growattstats_error_body'] = 'Не удалось преобразовать тело запроса API в JSON';
$_lang['growattstats_error_http'] = 'Неожиданный статус HTTP';
$_lang['growattstats_error_api'] = 'Ошибка Growatt API';
$_lang['growattstats_error_command'] = 'Неизвестная команда Growatt';
$_lang['growattstats_error_directory'] = 'Не удалось создать каталог данных';
$_lang['growattstats_error_json'] = 'Не удалось преобразовать данные графика в JSON';
$_lang['growattstats_error_request'] = 'Не удалось получить данные установки; статус HTTP';
$_lang['growattstats_desc_alias'] = 'Альтернативное имя сниппета growattShowChart';
$_lang['growattstats_desc_chart'] = 'Показать выработку Growatt и график истории';
$_lang['growattstats_desc_cron'] = 'Обновить кеш графика из Growatt API';
$_lang['growattstats_desc_widget'] = 'График солнечной генерации на панели управления';
