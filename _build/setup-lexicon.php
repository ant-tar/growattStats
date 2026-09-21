<?php

// The builder embeds this catalogue into installer scripts for clean installations.
$catalogue = []; // GROWATTSTATS_SETUP_CATALOGUE
if (!$catalogue) {
    foreach (glob(dirname(__DIR__) . '/core/components/growattstats/lexicon/*/setup.inc.php') as $file) {
        $_lang = [];
        include $file;
        $catalogue[basename(dirname($file))] = $_lang;
    }
}
$language = isset($modx) ? $modx->getOption('manager_language', null, 'en') : 'en';
$messages = array_merge($catalogue['en'], $catalogue[$language] ?? []);
return static function ($key) use ($messages) {
    return $messages[$key] ?? $key;
};
