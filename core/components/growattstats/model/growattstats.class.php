<?php

// MODX 2 getService() compatibility entry point.
require_once dirname(__DIR__) . '/bootstrap.php';
if (!class_exists('growattStats', false)) {
    class_alias(\GrowattStats\Service::class, 'growattStats');
}
