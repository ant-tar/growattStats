<?php

if (!getenv('MODX_CORE_PATH')) {
    throw new RuntimeException('Set MODX_CORE_PATH to an installed MODX 2.x core directory.');
}

return [
    'name' => 'growattStats',
    'name_lower' => 'growattstats',
    'version' => '1.0.1',
    'release' => 'beta7',
];
