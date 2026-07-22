<?php

$corePath = $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/');

return include $corePath . 'elements/snippets/growattshowchart.php';

