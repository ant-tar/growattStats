(function () {
    'use strict';
    function init(root) {
        if (root.dataset.gsReady) return;
        root.dataset.gsReady = '1';
        const config = JSON.parse(root.dataset.gsChart);
        const labels = config.labels;
        const data = config.series.filter(p => Array.isArray(p) && Number.isFinite(+p[0]) && Number.isFinite(+p[1]))
            .map(p => [+p[0], +p[1]]).sort((a, b) => a[0] - b[0]);
        const plot = root.querySelector('.gs-plot');
        const status = root.querySelector('.gs-chart-status');
        if (!data.length) { status.textContent = labels.compare_empty; return; }
        const first = data[0][0], last = data[data.length - 1][0];
        const day = 86400000;
        const gaps = [];
        const plotted = [];
        data.forEach((point, index) => {
            const previous = data[index - 1];
            if (previous && point[0] - previous[0] > day) {
                gaps.push([previous, point]);
                // Rendering-only separator; never invent readings for missing dates.
                plotted.push([(previous[0] + point[0]) / 2, null]);
            }
            plotted.push(point);
        });
        if (gaps.length) {
            const note = document.createElement('p');
            note.className = 'gs-gap-note';
            note.textContent = labels.compare_gap;
            root.querySelector('.gs-chart-note').before(note);
        }
        const formatDate = v => new Intl.DateTimeFormat(labels.locale, {
            day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC'
        }).format(new Date(v));
        const shortDate = v => new Intl.DateTimeFormat(labels.locale, {
            month: 'short', year: 'numeric', timeZone: 'UTC'
        }).format(new Date(v));
        const formatNumber = v => new Intl.NumberFormat(labels.locale, { maximumFractionDigits: 2 }).format(v);
        const toolbar = root.querySelector('.gs-periods');
        const periods = ['range_month', 'range_quarter', 'range_half_year', 'range_ytd', 'range_year', 'range_all'];
        let chart, overview, setWindow, startInput, endInput;
        let active = 5;
        const reportWindow = (start, end) => {
            status.textContent = formatDate(start) + ' — ' + formatDate(end);
        };
        const select = index => {
            active = index;
            toolbar.querySelectorAll('button').forEach((b, i) => b.setAttribute('aria-pressed', String(i === index)));
        };
        const bound = index => {
            if (index === 5) return first;
            const date = new Date(last);
            if (index === 3) return Date.UTC(date.getUTCFullYear(), 0, 1);
            const months = [1, 3, 6, 0, 12][index];
            const day = date.getUTCDate();
            date.setUTCDate(1);
            date.setUTCMonth(date.getUTCMonth() - months);
            const endOfMonth = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + 1, 0)).getUTCDate();
            date.setUTCDate(Math.min(day, endOfMonth));
            return Math.max(first, date.getTime());
        };
        periods.forEach((key, index) => {
            const button = document.createElement('button');
            button.type = 'button'; button.textContent = labels[key];
            button.setAttribute('aria-pressed', String(index === 5));
            button.addEventListener('click', () => {
                setWindow(bound(index), last); select(index);
            });
            toolbar.appendChild(button);
        });
        if (config.engine === 'echarts') {
            chart = echarts.init(plot, null, { renderer: 'canvas' });
            chart.setOption({
                animation: false, useUTC: true,
                grid: { left: 58, right: 18, top: 20, bottom: 95 },
                tooltip: { trigger: 'axis', confine: true, renderMode: 'richText', formatter: items => {
                    const item = items.find(item => item.seriesIndex === 0 && item.value[1] !== null);
                    if (!item) return labels.compare_gap;
                    const point = item.value;
                    return formatDate(point[0]) + '\n' + labels.series + ': ' + formatNumber(point[1]);
                } },
                xAxis: { type: 'time', axisLabel: { formatter: shortDate, hideOverlap: true } },
                yAxis: { type: 'value', min: 0, axisLabel: { formatter: formatNumber } },
                dataZoom: [
                    { type: 'slider', bottom: 8, height: 34, filterMode: 'none', labelFormatter: formatDate },
                    { type: 'inside', filterMode: 'none' }
                ],
                series: [{ type: 'line', name: labels.series, data: plotted, connectNulls: false,
                    showSymbol: false,
                    lineStyle: { width: 1.5, color: '#2e5a90' }, itemStyle: { color: '#2e5a90' } }]
                    .concat(gaps.map(points => ({ type: 'line', data: points, showSymbol: false,
                        silent: true, tooltip: { show: false },
                        lineStyle: { width: 1.5, color: '#94a3b8', type: 'dashed' } })))
            });
            setWindow = (start, end) => {
                chart.dispatchAction({ type: 'dataZoom', startValue: start, endValue: end });
                reportWindow(start, end);
            };
            chart.on('datazoom', () => {
                const zoom = chart.getOption().dataZoom[0];
                reportWindow(zoom.startValue, zoom.endValue); select(-1);
            });
        } else if (config.engine === 'dygraphs') {
            chart = new Dygraph(plot, plotted.map(p => [new Date(p[0]), p[1] === null ? NaN : p[1]]), {
                labels: ['Date', labels.series], colors: ['#2e5a90'], strokeWidth: 1.5,
                connectSeparatedPoints: false,
                underlayCallback: (context, area, graph) => {
                    context.save();
                    context.beginPath(); context.rect(area.x, area.y, area.w, area.h); context.clip();
                    context.strokeStyle = '#94a3b8'; context.lineWidth = 1.5; context.setLineDash([6, 5]);
                    gaps.forEach(([a, b]) => {
                        const start = graph.toDomCoords(a[0], a[1]), end = graph.toDomCoords(b[0], b[1]);
                        context.beginPath(); context.moveTo(start[0], start[1]);
                        context.lineTo(end[0], end[1]); context.stroke();
                    });
                    context.restore();
                },
                showRangeSelector: true, rangeSelectorHeight: 40, labelsUTC: true,
                includeZero: true, legend: 'onmouseover', animatedZooms: false,
                axes: {
                    x: { axisLabelFormatter: date => shortDate(+date), valueFormatter: formatDate },
                    y: { axisLabelFormatter: formatNumber, valueFormatter: formatNumber }
                },
                drawCallback: g => { const range = g.xAxisRange(); reportWindow(range[0], range[1]); },
                zoomCallback: () => select(-1)
            });
            setWindow = (start, end) => chart.updateOptions({ dateWindow: [start, end] });
        } else {
            const canvas = document.createElement('canvas'); plot.appendChild(canvas);
            const points = plotted.map(p => ({ x: p[0], y: p[1] }));
            const bridges = gaps.map(pair => ({
                label: labels.compare_gap, data: pair.map(p => ({ x: p[0], y: p[1] })),
                borderColor: '#94a3b8', borderDash: [6, 5], borderWidth: 1.5,
                pointRadius: 0, pointHitRadius: 0, fill: false
            }));
            chart = new Chart(canvas, {
                type: 'line', data: { datasets: [{ label: labels.series, data: points,
                    spanGaps: false, borderColor: '#2e5a90', borderWidth: 1.5,
                    pointRadius: 0, pointHitRadius: 8 }].concat(bridges) },
                options: {
                    animation: false, responsive: true, maintainAspectRatio: false, parsing: false,
                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    plugins: { legend: { display: false }, tooltip: { filter: item => item.datasetIndex === 0,
                        callbacks: {
                        title: items => formatDate(items[0].parsed.x),
                        label: item => labels.series + ': ' + formatNumber(item.parsed.y)
                    } } },
                    scales: {
                        x: { type: 'linear', min: first, max: last, ticks: { maxTicksLimit: 6, callback: shortDate } },
                        y: { beginAtZero: true, ticks: { callback: formatNumber } }
                    }
                }
            });
            const holder = root.querySelector('.gs-overview'); holder.style.display = 'block';
            const miniature = document.createElement('canvas'); holder.appendChild(miniature);
            overview = new Chart(miniature, {
                type: 'line', data: { datasets: [{ data: points, borderColor: '#7e9ac2', borderWidth: 1,
                    pointRadius: 0, spanGaps: false, fill: true, backgroundColor: '#edf2fa' }].concat(bridges) },
                options: { animation: false, responsive: true, maintainAspectRatio: false, parsing: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: { x: { type: 'linear', display: false }, y: { display: false, beginAtZero: true } } }
            });
            const ranges = root.querySelector('.gs-ranges');
            const input = (key, value) => {
                const label = document.createElement('label'); label.textContent = labels[key];
                const slider = document.createElement('input'); slider.type = 'range';
                slider.min = '0'; slider.max = String(Math.max(1, data.length - 1)); slider.value = String(value);
                label.appendChild(slider); ranges.appendChild(label); return slider;
            };
            startInput = input('compare_start', 0); endInput = input('compare_end', data.length - 1);
            setWindow = (start, end) => {
                chart.options.scales.x.min = start; chart.options.scales.x.max = end;
                chart.update('none'); reportWindow(start, end);
                startInput.value = String(Math.max(0, data.findIndex(p => p[0] >= start)));
                endInput.value = String(Math.max(0, data.findIndex(p => p[0] >= end)));
            };
            [startInput, endInput].forEach(slider => slider.addEventListener('input', () => {
                let a = +startInput.value, b = +endInput.value;
                if (a >= b && data.length > 1) {
                    if (slider === startInput) a = Math.max(0, b - 1);
                    else b = Math.min(data.length - 1, a + 1);
                }
                setWindow(data[Math.min(a, data.length - 1)][0], data[Math.min(b, data.length - 1)][0]); select(-1);
            }));
        }
        reportWindow(first, last);
        // Expose the instance on its own container for resizing and comparison diagnostics.
        root.gsChart = { engine: config.engine, chart, overview, points: data.length, gaps: gaps.length };
        if (window.ResizeObserver) {
            const observer = new ResizeObserver(() => {
                if (config.engine === 'echarts') chart.resize();
                else if (config.engine === 'dygraphs') chart.resize();
            });
            observer.observe(plot);
        }
    }
    function start() {
        document.querySelectorAll('[data-gs-chart]').forEach(root => {
            try { init(root); } catch (error) {
                console.error('growattStats chart:', error);
                const config = JSON.parse(root.dataset.gsChart);
                root.querySelector('.gs-chart-status').textContent = config.labels.compare_error;
            }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
}());
