<?php

namespace GrowattStats;

use modX;

/** Shared, parser-safe chart markup for frontend and Manager widgets. */
class Chart
{
    public static function render(modX $modx, array $series)
    {
        $labels = [];
        foreach (
            ['series', 'locale', 'range_month', 'range_quarter', 'range_half_year',
            'range_ytd', 'range_year', 'range_all', 'compare_empty', 'compare_error',
            'compare_period', 'compare_gap'] as $key
        ) {
            $labels[$key] = $modx->lexicon('growattstats_' . $key);
        }
        $config = json_encode(
            ['series' => array_values($series), 'labels' => $labels],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        $escape = static function ($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        };
        // Avoid MODX parsing nested JSON arrays as resource/snippet tags.
        $config = str_replace(['[', ']'], ['&#91;', '&#93;'], $escape($config));
        return '<div class="gs-echart" data-gs-echart="' . $config . '">'
            . '<div class="gs-periods" role="group" aria-label="' . $escape($labels['compare_period']) . '"></div>'
            . '<div class="gs-plot" role="img" aria-label="' . $escape($labels['series']) . '"></div>'
            . '<p class="gs-chart-status" aria-live="polite"></p></div>';
    }
}
