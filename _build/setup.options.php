<?php

$translate = require __DIR__ . '/setup-lexicon.php';
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$plantId = '';
if (isset($modx) && $modx instanceof modX) {
    $setting = $modx->getObject('modSystemSetting', ['key' => 'growattstats_plant_id']);
    if ($setting) {
        $plantId = (string) $setting->get('value');
    }
}
return '<p>' . $escape($translate('growattstats_setup_intro')) . ' '
    . $escape($translate('growattstats_setup_upgrade')) . '</p>
<div class="form-group">
    <label for="growattstats-token">' . $escape($translate('growattstats_setup_token')) . '</label>
    <input type="password" name="growattstats_token" id="growattstats-token"
        value="" autocomplete="new-password" style="width:100%">
</div>
<div class="form-group">
    <label for="growattstats-plant-id">' . $escape($translate('growattstats_setup_plant')) . '</label>
    <input type="text" name="growattstats_plant_id" id="growattstats-plant-id"
        value="' . $escape($plantId) . '" style="width:100%">
</div>
<p>' . $escape($translate('growattstats_setup_cron')) . ' '
    . $escape($translate('growattstats_setup_preserve')) . '</p>
<p>' . $escape($translate('growattstats_setup_external')) . '</p>
<pre>* * * * * /usr/bin/php /path/to/modx/assets/components/cronmanager/cron.php</pre>
<p><a href="https://jako.github.io/CronManager/usage/" target="_blank" rel="noopener noreferrer">'
    . $escape($translate('growattstats_setup_help')) . '</a> | '
    . '<a href="https://man7.org/linux/man-pages/man5/crontab.5.html" target="_blank" rel="noopener noreferrer">'
    . $escape($translate('growattstats_setup_crontab')) . '</a></p>';
