(function () {
    'use strict';
    function init(root) {
        if (root.dataset.gsReady || !window.echarts || !root.isConnected) return;
        const config = JSON.parse(root.dataset.gsEchart);
        const labels = config.labels;
        const data = config.series.filter(p => Array.isArray(p) && p[0] !== null && p[1] !== null
            && Number.isFinite(+p[0]) && Number.isFinite(+p[1]))
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
            root.appendChild(note);
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
        let chart, setWindow;
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
            if (index === 3) return Math.max(first, Date.UTC(date.getUTCFullYear(), 0, 1));
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
        {
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
                xAxis: { type: 'time', min: first === last ? first - day / 2 : first,
                    max: first === last ? last + day / 2 : last, axisLabel: { formatter: shortDate, hideOverlap: true } },
                yAxis: { type: 'value', min: 0, axisLabel: { formatter: formatNumber } },
                dataZoom: [
                    { type: 'slider', bottom: 8, height: 34, filterMode: 'none', labelFormatter: formatDate },
                    { type: 'inside', filterMode: 'none' }
                ],
                series: [{ type: 'line', name: labels.series, data: plotted, connectNulls: false,
                    showSymbol: data.length === 1,
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
        }
        reportWindow(first, last);
        // Expose the instance on its own container for resizing and comparison diagnostics.
        root.dataset.gsReady = '1';
        root.gsChart = { engine: 'echarts', chart, points: data.length, gaps: gaps.length };
        if (window.ResizeObserver) {
            const observer = new ResizeObserver(() => chart.resize());
            observer.observe(plot);
            root.gsChart.observer = observer;
        }

    }
    function start() {
        document.querySelectorAll('[data-gs-echart]').forEach(root => {
            try { init(root); } catch (error) {
                console.error('growattStats chart:', error);
                const config = JSON.parse(root.dataset.gsEchart);
                root.querySelector('.gs-chart-status').textContent = config.labels.compare_error;
            }
        });
    }
    // Manager panels may insert/remove widgets after the initial document load.
    if (window.GrowattStatsCharts) { window.GrowattStatsCharts.start(); return; }
    window.GrowattStatsCharts = { start };
    const lifecycle = new MutationObserver(records => {
        let added = false;
        records.forEach(record => {
            record.addedNodes.forEach(node => {
                if (node.nodeType === 1 && (node.matches('[data-gs-echart]') || node.querySelector('[data-gs-echart]'))) added = true;
            });
            record.removedNodes.forEach(node => {
                if (node.nodeType !== 1) return;
                const roots = [...node.querySelectorAll('[data-gs-echart]')];
                if (node.matches('[data-gs-echart]')) roots.push(node);
                roots.forEach(root => {
                    if (root.isConnected || !root.gsChart) return;
                    if (root.gsChart.observer) root.gsChart.observer.disconnect();
                    root.gsChart.chart.dispose();
                    delete root.gsChart; delete root.dataset.gsReady;
                    root.querySelector('.gs-periods').textContent = '';
                    root.querySelectorAll('.gs-gap-note').forEach(note => note.remove());
                });
            });
        });
        if (added) start();
    });
    lifecycle.observe(document.documentElement, {childList:true, subtree:true});
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
}());
