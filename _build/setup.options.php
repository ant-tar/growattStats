<?php

$plantId = '';
if (isset($modx) && $modx instanceof modX) {
    $setting = $modx->getObject('modSystemSetting', ['key' => 'growattstats_plant_id']);
    if ($setting) {
        $plantId = (string) $setting->get('value');
    }
}
$plantId = htmlspecialchars($plantId, ENT_QUOTES, 'UTF-8');

return '<p>Enter your Growatt API credentials. Both settings are required for a new installation.
On upgrade, leave fields blank to keep existing values.</p>
<div class="form-group">
    <label for="growattstats-token">growattstats_token ? API token</label>
    <input type="password" name="growattstats_token" id="growattstats-token"
        value="" autocomplete="new-password" style="width:100%">
</div>
<div class="form-group">
    <label for="growattstats-plant-id">growattstats_plant_id ? Plant ID</label>
    <input type="text" name="growattstats_plant_id" id="growattstats-plant-id"
        value="' . $plantId . '" style="width:100%">
</div>';
