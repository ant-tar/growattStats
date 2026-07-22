<?php

$values = [
    'token' => '',
    'plant_id' => '',
    'plant_name' => '',
    'price' => '1.20',
];

if (isset($modx) && $modx instanceof modX) {
    foreach ($values as $key => $value) {
        $setting = $modx->getObject('modSystemSetting', ['key' => 'growattstats_' . $key]);
        if ($setting) {
            $values[$key] = (string)$setting->get('value');
        }
    }
}

$escape = static function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

return '
<div class="form-group">
    <label for="growattstats-token">API Token</label>
    <input type="text" class="form-control" name="token" id="growattstats-token" value="' . $escape($values['token']) . '" required>
</div>
<div class="form-group">
    <label for="growattstats-plant-id">Plant ID</label>
    <input type="text" class="form-control" name="plant_id" id="growattstats-plant-id" value="' . $escape($values['plant_id']) . '" required>
</div>
<div class="form-group">
    <label for="growattstats-plant-name">Plant Name</label>
    <input type="text" class="form-control" name="plant_name" id="growattstats-plant-name" value="' . $escape($values['plant_name']) . '">
</div>
<div class="form-group">
    <label for="growattstats-price">Price per kWh</label>
    <input type="text" class="form-control" name="price" id="growattstats-price" value="' . $escape($values['price']) . '">
</div>';
