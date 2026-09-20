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
            'description' => $definition['description'],
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
$vehicle->validate('php', ['source' => __DIR__ . '/validators/credentials.php']);
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
$vehicle->resolve('php', ['source' => __DIR__ . '/resolvers/setupoptions.resolver.php']);
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
    'setup-options' => ['source' => __DIR__ . '/setup.options.php'],
    'requires' => ['modx' => '>=2.8.0 <3.0.0', 'php' => '>=7.4', 'cronmanager' => '*'],
]);
if (!$builder->pack()) {
    throw new RuntimeException('Transport package creation failed.');
}
echo $builder->directory . $builder->getSignature() . '.transport.zip' . PHP_EOL;
