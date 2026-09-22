<?php

$config = require __DIR__ . '/config.inc.php';
$corePath = rtrim(getenv('MODX_CORE_PATH'), '/\\') . '/';
require_once $corePath . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$version = $modx->getVersionData();
if ($version['version'] !== '2') {
    throw new RuntimeException('This package targets MODX 2.x only.');
}
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->directory = __DIR__ . '/dist/';
if (!is_dir($builder->directory)) {
    mkdir($builder->directory, 0775, true);
}
$builder->createPackage($config['name_lower'], $config['version'], $config['release']);
$builder->registerNamespace('growattstats', false, true, '{core_path}components/growattstats/');
$core = dirname(__DIR__) . '/core/components/growattstats/';
$assets = dirname(__DIR__) . '/assets/components/growattstats/';
$readLexicon = static function ($file) {
    $_lang = [];
    include $file;
    return $_lang;
};
$english = $readLexicon($core . 'lexicon/en/default.inc.php');
$catalogue = [];
foreach (glob($core . 'lexicon/*/setup.inc.php') as $file) {
    $catalogue[basename(dirname($file))] = $readLexicon($file);
}
$helper = preg_replace('/^<\?php\s*/', '', file_get_contents(__DIR__ . '/setup-lexicon.php'));
$helper = str_replace(
    '$catalogue = []; // GROWATTSTATS_SETUP_CATALOGUE',
    '$catalogue = ' . var_export($catalogue, true) . ';',
    $helper
);
$installerDirectory = $builder->directory . 'installer/';
if (!is_dir($installerDirectory)) {
    mkdir($installerDirectory, 0775, true);
}
foreach (
    ['setup.options.php', 'validators/credentials.php',
    'resolvers/setupoptions.resolver.php', 'resolvers/cronjob.resolver.php'] as $script
) {
    $source = file_get_contents(__DIR__ . '/' . $script);
    $source = str_replace(
        ["require __DIR__ . '/setup-lexicon.php'", "require dirname(__DIR__) . '/setup-lexicon.php'"],
        '(static function () use ($modx) {' . $helper . '})()',
        $source
    );
    file_put_contents($installerDirectory . basename($script), $source);
}
$category = $modx->newObject('modCategory');
$category->set('category', 'growattStats');
$related = [];
foreach (['snippets' => 'modSnippet', 'chunks' => 'modChunk'] as $type => $class) {
    $definitions = require __DIR__ . '/elements/' . $type . '.php';
    $objects = [];
    foreach ($definitions as $name => $definition) {
        $extension = $type === 'snippets' ? '.php' : '.tpl';
        $content = file_get_contents($core . 'elements/' . $type . '/' . $definition['file'] . $extension);
        if ($type === 'snippets') {
            $content = preg_replace('/^<\?php\s*/', '', $content);
        }
        $object = $modx->newObject($class);
        $object->fromArray([
            'name' => $name,
            'description' => $english[$definition['description']] ?? $definition['description'],
            'snippet' => $content,
            'static' => false,
        ], '', true, true);
        $objects[] = $object;
    }
    $relation = ucfirst($type);
    $category->addMany($objects, $relation);
    $related[$relation] = [
        xPDOTransport::UNIQUE_KEY => 'name',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
    ];
}
$vehicle = $builder->createVehicle($category, [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => $related,
    xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
]);
$vehicle->validate('php', ['source' => $installerDirectory . 'credentials.php']);
$vehicle->resolve('file', [
    'source' => $core,
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
// Runtime history is never shipped or overwritten by a package upgrade.
foreach (['css', 'js', 'images', 'connector.php'] as $path) {
    $vehicle->resolve('file', [
        'source' => $assets . $path,
        'target' => "return MODX_ASSETS_PATH . 'components/growattstats/';",
    ]);
}
$vehicle->resolve('php', ['source' => $installerDirectory . 'setupoptions.resolver.php']);
$vehicle->resolve('php', ['source' => $installerDirectory . 'cronjob.resolver.php']);
$vehicle->resolve('php', ['source' => __DIR__ . '/resolvers/echarts.resolver.php']);
$builder->putVehicle($vehicle);

$settings = require __DIR__ . '/elements/settings.php';
foreach ($settings as $key => $definition) {
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge($definition, [
        'key' => 'growattstats_' . $key,
        'namespace' => 'growattstats',
    ]), '', true, true);
    $builder->putVehicle($builder->createVehicle($setting, [
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    ]));
}
$widgets = require __DIR__ . '/elements/widgets.php';
foreach ($widgets as $name => $definition) {
    $widget = $modx->newObject('modDashboardWidget');
    $widget->fromArray(array_merge($definition, ['name' => $name]), '', true, true);
    $builder->putVehicle($builder->createVehicle($widget, [
        xPDOTransport::UNIQUE_KEY => 'name',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
    ]));
}
$builder->setPackageAttributes([
    'changelog' => file_get_contents($core . 'docs/changelog.txt'),
    'license' => file_get_contents($core . 'docs/license.txt'),
    'readme' => file_get_contents($core . 'docs/readme.txt'),
    'setup-options' => ['source' => $installerDirectory . 'setup.options.php'],
    'requires' => ['modx' => '>=2.8.0 <3.0.0', 'php' => '>=7.4', 'cronmanager' => '>=1.2.2'],
]);
if (!$builder->pack()) {
    throw new RuntimeException('Transport package creation failed.');
}
echo $builder->directory . $builder->getSignature() . '.transport.zip' . PHP_EOL;
