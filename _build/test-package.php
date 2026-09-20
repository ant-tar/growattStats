<?php

require rtrim(getenv('MODX_CORE_PATH'), '/\\') . '/model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogTarget('ECHO');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
if (
    !getenv('GROWATTSTATS_TEST_DB')
    || strpos(getenv('GROWATTSTATS_TEST_DB'), 'growattstats_qa_') !== 0
    || $modx->query('SELECT DATABASE()')->fetchColumn() !== getenv('GROWATTSTATS_TEST_DB')
) {
    throw new RuntimeException('Refusing to modify a non-QA database');
}
$check = function ($ok, $label) {
    if (!$ok) {
        throw new RuntimeException($label);
    }
    echo 'PASS: ' . $label . PHP_EOL;
};
$modx->addPackage('cronmanager', MODX_CORE_PATH . 'components/cronmanager/model/');
$modx->removeCollection('modCronjob', ['properties' => '{"growattstats_managed_job":true}']);
$modx->removeCollection('modSystemSetting', ['namespace' => 'growattstats']);
$modx->removeCollection('modSnippet', ['name:IN' => ['growattStats','growattShowChart','growattCronDataUpdate']]);
$modx->removeCollection('modChunk', ['name:IN' => ['growattShowChart','growattShowWidget']]);
$modx->removeCollection('modDashboardWidget', ['name' => 'growattStats']);
$modx->removeCollection('transport.modTransportPackage', ['signature:LIKE' => 'growattstats-%']);
$config = require __DIR__ . '/config.inc.php';
$signature = 'growattstats-' . $config['version'] . '-' . $config['release'];
copy(__DIR__ . '/dist/' . $signature . '.transport.zip', MODX_CORE_PATH . 'packages/' . $signature . '.transport.zip');
$p = $modx->newObject('transport.modTransportPackage');
$p->fromArray(['signature' => $signature,'source' => $signature . '.transport.zip','state' => 1,'workspace' => 1,
    'provider' => 0,'package_name' => 'growattstats','version_major' => 1,'version_minor' => 0,'version_patch' => 1,
    'release' => 'beta','release_index' => 4], '', true, true);
$check($p->save(), 'register transport package');
$check(!$p->install(), 'reject clean installation without credentials');
$check(!$modx->getObject('modSnippet', ['name' => 'growattShowChart']), 'failed validation creates no snippet');
$check(
    $p->install(['growattstats_token' => 'qa-token','growattstats_plant_id' => '12345']),
    'clean transport installation'
);
$token = $modx->getObject('modSystemSetting', ['key' => 'growattstats_token']);
$check($token && $token->get('value') === 'qa-token', 'save installer token');
$check($token->get('xtype') === 'text-password', 'mask system-setting token field');
$cronSnippet = $modx->getObject('modSnippet', ['name' => 'growattCronDataUpdate']);
$job = $modx->getObject('modCronjob', ['snippet' => $cronSnippet->get('id')]);
$check($job && (int) $job->get('minutes') === 15 && $job->get('active'), 'create active 15-minute job');
$jobId = $job->get('id');
$job->set('properties', '{"growattstats_managed_job":true,"customProperty":"preserved"}');
$job->save();
// Reload transport as Package Manager would between separate install/uninstall requests.
$p->package = null;
$check($p->uninstall(), 'uninstall clean installation');
$check(!$modx->getObject('modCronjob', $jobId), 'remove owned job on clean uninstall');
$check(!$modx->getObject('modSnippet', ['name' => 'growattShowChart']), 'remove clean-install snippets');
$check(
    $p->install(['growattstats_token' => 'qa-token','growattstats_plant_id' => '12345']),
    'install again for upgrade checks'
);
$cronSnippet = $modx->getObject('modSnippet', ['name' => 'growattCronDataUpdate']);
$job = $modx->getObject('modCronjob', ['snippet' => $cronSnippet->get('id')]);
$jobId = $job->get('id');
$job->set('minutes', 30);
$job->set('active', false);
$job->save();
$setting = $modx->getObject('modSystemSetting', ['key' => 'growattstats_plant_name']);
$setting->set('value', 'QA plant');
$setting->save();
$dataDir = MODX_ASSETS_PATH . 'components/growattstats/data/';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0775, true);
}
$payload = ['series' => [[946684800000,12.5]], 'today_energy' => 12.5, 'total_energy' => 99,
    'api' => ['today_energy' => 12.5,'total_energy' => 99]];
file_put_contents($dataDir . 'chart-data.json', json_encode($payload));
$hash = hash_file('sha256', $dataDir . 'chart-data.json');
$check($p->install(['growattstats_token' => '','growattstats_plant_id' => '']), 'reinstall with blank credentials');
$check(
    $modx->getObject('modSystemSetting', ['key' => 'growattstats_token'])->get('value') === 'qa-token',
    'preserve token'
);
$check(
    $modx->getObject('modSystemSetting', ['key' => 'growattstats_plant_name'])->get('value') === 'QA plant',
    'preserve optional setting'
);
$job = $modx->getObject('modCronjob', $jobId);
$check($job && (int) $job->get('minutes') === 30 && !$job->get('active'), 'preserve edited cron schedule');
$check($modx->getCount('modCronjob', ['snippet' => $cronSnippet->get('id')]) === 1, 'no duplicate cron job');
$check(hash_file('sha256', $dataDir . 'chart-data.json') === $hash, 'preserve chart history');
$modx->setOption('growattstats_plant_name', 'QA plant');
$out = $modx->runSnippet('growattShowChart');
$check(strpos($out, '12.5') !== false && strpos($out, 'growattstats-container') !== false, 'render installed snippet');
$widget = $modx->getObject('modDashboardWidget', ['name' => 'growattStats']);
$modx->loadClass('modManagerController', '', false, true);
$controller = new class ($modx) extends modManagerController {
    public function checkPermissions()
    {
        return true;
    }
    public function process(array $scriptProperties = [])
    {
        return '';
    }
    public function getPageTitle()
    {
        return 'QA';
    }
    public function loadCustomCssJs()
    {
    }
    public function getTemplateFile()
    {
        return '';
    }
};
$html = $widget->getContent($controller);

$check(strpos($html, 'growattstats-widget') !== false, 'render installed Manager widget');
$token = $modx->getObject('modSystemSetting', ['key' => 'growattstats_token']);
$token->set('value', '');
$token->save();
$legacy = $modx->newObject('modSystemSetting');
$legacy->fromArray([
    'key' => 'growattstats_token_id', 'namespace' => 'growattstats', 'value' => 'legacy-token',
], '', true, true);
$legacy->save();
$check($p->install(), 'upgrade using legacy token setting');
$token = $modx->getObject('modSystemSetting', ['key' => 'growattstats_token']);
$check($token->get('value') === 'legacy-token', 'migrate legacy token value');
$p->package = null;
$check($p->uninstall(), 'uninstall transport package');

echo "Transport lifecycle tests completed.\n";
