<?php

return [
    'growattStats' => [
        'description' => 'growattStats — статистика солнечной генерации',
        'type'        => 'file',
        // Путь относительно MODX_CORE_PATH (так MODX ищет файл виджета типа 'file')
        'content'     => '[[++core_path]]components/growattstats/elements/widgets/growattstats.widget.php',
        'namespace'   => 'growattstats',
        'lexicon'     => 'growattstats:default',
        'size'        => 'double',
    ],
];
