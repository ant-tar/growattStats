<?php

require rtrim(getenv('MODX_CORE_PATH'), '/\\') . '/model/modx/modx.class.php';
require dirname(__DIR__) . '/core/components/growattstats/bootstrap.php';
$modx = new modX();
$modx->initialize('mgr');
$read = static function ($file) {
    $_lang = [];
    include $file;
    return $_lang;
};
$check = static function ($ok, $label) {
    if (!$ok) {
        throw new RuntimeException($label);
    }
    echo 'PASS: ' . $label . PHP_EOL;
};
$core = dirname(__DIR__) . '/core/components/growattstats/';
$en = array_merge($read($core . 'lexicon/en/default.inc.php'), $read($core . 'lexicon/en/setup.inc.php'));
$ru = array_merge($read($core . 'lexicon/ru/default.inc.php'), $read($core . 'lexicon/ru/setup.inc.php'));
$check(!array_diff_key($en, $ru) && !array_diff_key($ru, $en), 'English and Russian key parity');
$check(!in_array('', $en, true) && !in_array('', $ru, true), 'translations are nonempty');
$service = new GrowattStats\Service($modx);
$renderSetup = static function ($path) use ($modx) {
    return include $path;
};
foreach (['en' => $en, 'ru' => $ru] as $language => $messages) {
    $modx->setOption('manager_language', $language);
    $modx->setOption('cultureKey', $language);
    $modx->elementCache = [];
    $modx->lexicon->set($messages);
    foreach (['growattshowchart', 'growattshowwidget'] as $name) {
        $chunk = $modx->newObject('modChunk');
        $chunk->setContent(file_get_contents($core . 'elements/chunks/' . $name . '.tpl'));
        $html = $chunk->process(['today_energy' => 1, 'total_energy' => 2, 'plant_name' => 'QA']);
        $check(strpos($html, $messages['growattstats_generation']) !== false, $language . ' ' . $name . ' label');
        $check(strpos($html, '[[%') === false, $language . ' ' . $name . ' lexicon tags resolved');
    }
    $script = $service->getChartScript([[1, 2]]);
    preg_match('/var labels = (.*);/', $script, $match);
    $labels = json_decode($match[1], true);
    $check($labels['series'] === $messages['growattstats_series'], $language . ' chart series');
    $check($labels['locale'] === $language, $language . ' chart dates locale');
    foreach (['setup.options.php', 'dist/installer/setup.options.php'] as $file) {
        $html = $renderSetup(__DIR__ . '/' . $file);
        $check(strpos($html, $messages['growattstats_setup_token']) !== false, $language . ' ' . $file);
        $check(strpos($html, 'https://jako.github.io/CronManager/usage/') !== false, 'installer documentation link');
    }
}
$modx->setOption('manager_language', 'unknown');
$html = $renderSetup(__DIR__ . '/dist/installer/setup.options.php');
$check(strpos($html, $en['growattstats_setup_token']) !== false, 'installer English fallback');
$compiled = file_get_contents(__DIR__ . '/dist/installer/setup.options.php');
$check(strpos($compiled, "require __DIR__ . '/setup-lexicon.php'") === false, 'standalone installer translations');
