> Historical experiment: beta12 now uses ECharts in production. Prototype files are
> archived under `docs/examples/comparison/` and are not installed by the package.

# Frontend chart comparison (local experiment)

Three independent MODX snippets are available alongside the unchanged Highstock renderer:

```modx
[[!growattShowECharts]]
[[!growattShowDygraphs]]
[[!growattShowChartJs]]
```

All three support `plantName` and read the same persisted history through the
existing service. No migration, rewriting or additional API request is performed.
The shared source is `assets/components/growattstats/data/chart-data.json`.
Each point remains `[UTC timestamp in milliseconds, daily energy in kWh]`.
Dygraphs receives JavaScript Date objects and Chart.js receives `{x, y}` objects
in memory only. English/Russian text comes from component lexicons.

The snippets can coexist on a page, including alongside the current
`growattShowChart`. Each new chart owns its own container and controls.
No global Highcharts settings are changed. The experiment does not replace
the homepage or Manager dashboard, and is not a new release package.

Local comparison page: <http://modx-2.8.6.test/index.php?id=3>.

| Engine | Version | License | Period navigation |
| --- | --- | --- | --- |
| Apache ECharts | 5.6.0 | Apache-2.0 | Native slider and inside zoom/pan |
| Dygraphs | 2.2.1 | MIT | Native range selector and mouse selection |
| Chart.js | 4.4.8 | MIT | Custom overview and two range inputs; no zoom plugin |

The same six period buttons are provided for all engines. Periods end at the
latest recorded point. Dates are UTC; numbers and dates use the component locale.
Raw daily points are displayed without automatic weekly/monthly averaging.
Intervals longer than one UTC day are broken in the measured series and bridged
with a muted dashed line on the main chart. The caption identifies missing readings;
the bridge does not generate measurements or change the history file.
Highstock may group points on long ranges, so peaks may appear different even
though the source readings match. This is an intentional comparison difference.

Libraries are pinned and hosted locally under `assets/components/growattstats/js/vendor/`,
with their license/notice files. Browsers do not need a CDN to render the charts.
The existing Highstock distribution question remains open while it is retained.

Sources:
- https://github.com/apache/echarts/tree/5.6.0
- https://github.com/danvk/dygraphs
- https://github.com/chartjs/Chart.js/tree/v4.4.8

Local experiment deployment is performed by the ignored helper
`_build/local/install-comparison.php`. It registers the three snippets and a
standalone comparison resource. All changes remain uncommitted at the user's request.
