<?php

return [
    'growattStats' => [
        'description' => 'growattStats — статистика солнечной генерации',
        'type'        => 'file',
        // Путь относительно MODX_CORE_PATH (так MODX ищет файл виджета типа 'file')
        'content'     => 'components/growattstats/elements/widgets/growattstats.widget.php',
        'namespace'   => 'growattstats',
        'lexicon'     => 'growattstats:default',
        'size'        => 'double',
    ],
];
