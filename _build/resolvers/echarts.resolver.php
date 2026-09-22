<?php

/** Remove only the known, unmodified distribution replaced by ECharts. */

if (!$transport->xpdo instanceof modX) {
    return false;
}
$action = $options[xPDOTransport::PACKAGE_ACTION] ?? null;
if (in_array($action, [xPDOTransport::ACTION_INSTALL, xPDOTransport::ACTION_UPGRADE], true)) {
    $file = MODX_ASSETS_PATH . 'components/growattstats/js/highstock.js';
    if (
        is_file($file)
        && hash_file('sha256', $file) === '13fd12d10fbad6e7fcf0aa26eed1e05f8df9878ef37b552602aa6b97b8d4abbd'
    ) {
        return unlink($file);
    }
}
return true;
