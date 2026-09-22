# Third-party libraries

The component license does not relicense bundled libraries.

The transport uses Apache ECharts 5.6.0 (Apache-2.0), pinned under
assets/components/growattstats/js/vendor/echarts.min.js. The adjacent echarts-LICENSE.txt
includes upstream dependency notices; echarts-NOTICE.txt contains Apache attribution.
Both are included in the transport package.

- Source: https://github.com/apache/echarts/tree/5.6.0
- License: https://github.com/apache/echarts/blob/5.6.0/LICENSE
- NOTICE: https://github.com/apache/echarts/blob/5.6.0/NOTICE

Highstock is no longer distributed or loaded as of beta12. Upgrade removes only the
hash-matching original highstock.js; a customized file is left untouched and is not
loaded by bundled chunks.

Archived prototypes under docs/examples/comparison/ are excluded from the package:
Dygraphs 2.2.1 and Chart.js 4.4.8 use MIT licenses included beside their distributions.
Historical editorial drafts are not release code.
