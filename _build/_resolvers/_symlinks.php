<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/growattStats/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/growattstats')) {
            $cache->deleteTree(
                $dev . 'assets/components/growattstats/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/growattstats/', $dev . 'assets/components/growattstats');
        }
        if (!is_link($dev . 'core/components/growattstats')) {
            $cache->deleteTree(
                $dev . 'core/components/growattstats/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/growattstats/', $dev . 'core/components/growattstats');
        }
    }
}

return true;