<?php

namespace GrowattStats;

use modX;

class Comparison
{
    private $modx;
    private $service;

    public function __construct(modX $modx, Service $service)
    {
        $this->modx = $modx;
        $this->service = $service;
    }

    public function render($engine, array $properties = [])
    {
        $libraries = ['echarts' => 'echarts.min.js', 'dygraphs' => 'dygraph.min.js', 'chartjs' => 'chart.umd.js'];
        if (!isset($libraries[$engine])) {
            return '';
        }
        // All renderers consume the same persisted history; no API refresh or data rewrite.
        $payload = $this->service->getChartPayload(false);
        $labels = [];
        foreach (
            ['locale', 'series', 'unit', 'range_month', 'range_quarter', 'range_half_year',
            'range_ytd', 'range_year', 'range_all', 'compare_start', 'compare_end', 'compare_empty',
            'compare_error', 'compare_period', 'compare_note', 'compare_gap'] as $key
        ) {
            $labels[$key] = $this->modx->lexicon('growattstats_' . $key);
        }
        $config = [
            'engine' => $engine,
            'series' => array_values($payload['series'] ?? []),
            'labels' => $labels,
        ];
        $escape = static function ($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        };
        $json = json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $json = str_replace(['[', ']'], ['&#91;', '&#93;'], $escape($json));
        $title = $properties['plantName'] ?? $this->modx->getOption('growattstats_plant_name', null, '');
        $assets = $this->service->config['assetsUrl'];
        $this->modx->regClientCSS($assets . 'css/comparison.css');
        if ($engine === 'dygraphs') {
            $this->modx->regClientCSS($assets . 'js/vendor/dygraph.css');
        }
        $this->modx->regClientScript($assets . 'js/vendor/' . $libraries[$engine]);
        $this->modx->regClientScript($assets . 'js/comparison.js');
        return '<section class="gs-comparison" data-gs-chart="' . $json . '">'
            . '<h3>' . $escape($title) . '</h3>'
            . '<div class="gs-periods" role="group" aria-label="' . $escape($labels['compare_period']) . '"></div>'
            . '<div class="gs-plot"></div><div class="gs-overview"></div>'
            . '<div class="gs-ranges"></div><p class="gs-chart-status" aria-live="polite"></p>'
            . '<p class="gs-chart-note">' . $escape($labels['compare_note']) . '</p></section>';
    }
}
