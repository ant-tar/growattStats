<?php

require rtrim(getenv('MODX_CORE_PATH'), '/\\') . '/model/modx/modx.class.php';
require dirname(__DIR__) . '/core/components/growattstats/bootstrap.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogTarget('ECHO');
$check = static function ($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo 'PASS: ' . $message . PHP_EOL;
};
$directory = __DIR__ . '/local/runtime-' . uniqid();
mkdir($directory, 0775, true);
$file = $directory . '/chart-data.json';
$service = new GrowattStats\Service($modx, ['dataFile' => $file]);
try {
    $example = dirname(__DIR__) . '/core/components/growattstats/docs/examples/chart-data.example.js';
    copy($example, $directory . '/chart-data.js');
    $examplePayload = $service->getChartPayload(false);
    $check(count($examplePayload['series']) > 1000, 'read retained Date.UTC history example');
    $legacy = ['series' => [[946684800000, 12.5]], 'today_energy' => 12.5, 'total_energy' => 99];
    file_put_contents($directory . '/chart-data.js', 'var growattStatsData = ' . json_encode($legacy) . ';');
    file_put_contents($file, json_encode(['series' => [], 'today_energy' => 0, 'total_energy' => 0]));
    $check($service->getChartPayload(false)['series'] === $legacy['series'], 'migrate structured JS history');
    $check($service->updateChartData(['today_energy' => 4.5, 'total_energy' => 100]), 'persist fresh readings');
    $check($service->updateChartData(['today_energy' => 7.5, 'total_energy' => 103]), 'update same-day reading');
    $data = $service->getDisplayData(['price' => 2, 'plantName' => 'Test plant']);
    $check(count($data['series']) === 2, 'preserve history without duplicate daily points');
    $check($data['today_revenue'] === 15.0 && $data['total_revenue'] === 206.0, 'calculate revenue');
    $check($data['plant_name'] === 'Test plant', 'honor snippet plantName');
    $script = $service->getChartScript([[1, '</script><script>alert(1)</script>']]);
    $check(strpos($script, '</script><script>alert') === false, 'escape inline JSON');
    $form = require __DIR__ . '/setup.options.php';
    $check(strpos($form, 'name="growattstats_token"') !== false, 'installer requests canonical token');
    $check(strpos($form, 'name="growattstats_plant_id"') !== false, 'installer requests Plant ID');
    $check(strpos($form, 'type="password"') !== false, 'installer masks token input');
    $token = $modx->getObject('modSystemSetting', ['key' => 'growattstats_token']);
    if ($token && $token->get('value') !== '') {
        $check(strpos($form, $token->get('value')) === false, 'installer does not expose stored token');
    }
    require dirname(__DIR__) . '/core/components/growattstats/model/growattstats.class.php';
    $check(is_a($service, 'growattStats'), 'MODX 2 class alias');
    $check(empty($service->requestGrowattCommand('unknown')['success']), 'reject unknown API command');
    $scriptProperties = ['CronManager' => '1'];
    $fakeService = new class {
        public $result = true;
        public function refreshCache()
        {
            return $this->result;
        }
    };
    $modx->services['growattstats'] = $fakeService;
    $snippetPath = dirname(__DIR__) . '/core/components/growattstats/elements/snippets/growattcrondataupdate.php';
    $response = json_decode(include $snippetPath, true);
    $check($response['error'] === false && is_string($response['message']), 'CronManager success JSON');
    $fakeService->result = false;
    $response = json_decode(include $snippetPath, true);
    $check($response['error'] === true, 'CronManager failure JSON');
    unset($modx->services['growattstats']);
    if (in_array('--live', $argv, true)) {
        $check(is_array($service->fetchApiData()), 'live Growatt plant data request');
    }
} finally {
    foreach (glob($directory . '/*') as $path) {
        unlink($path);
    }
    rmdir($directory);
}
