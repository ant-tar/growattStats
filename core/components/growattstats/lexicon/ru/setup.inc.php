<?php

$_lang['growattstats_setup_intro'] = 'Введите данные Growatt API. При первой установке обязательны оба поля.';
$_lang['growattstats_setup_upgrade'] = 'При обновлении оставьте поля пустыми, чтобы сохранить прежние значения.';
$_lang['growattstats_setup_token'] = 'Токен Growatt API';
$_lang['growattstats_setup_plant'] = 'ID установки Growatt';
$_lang['growattstats_setup_cron'] = 'В CronManager создаётся активное задание обновления каждые 15 минут.';
$_lang['growattstats_setup_preserve'] = 'Расписание существующего задания сохраняется.';
$_lang['growattstats_setup_external'] =
    'Внешняя служба cron должна быть запущена. Настройте запуск этой команды '
    . 'каждую минуту, указав пути своего сервера:';
$_lang['growattstats_setup_help'] = 'Настройка CronManager';
$_lang['growattstats_setup_crontab'] = 'Синтаксис и документация crontab';
$_lang['growattstats_setup_required'] = 'Токен Growatt API и ID установки обязательны.';
$_lang['growattstats_setup_dependency'] = 'Перед установкой growattStats установите CronManager.';
$_lang['growattstats_setup_created'] =
    'Создано задание с интервалом 15 минут. Настройте внешний запуск cron.php '
    . 'CronManager каждую минуту.';
