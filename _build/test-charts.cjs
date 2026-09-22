// Browser integration check against the installed local package.
// npm install --prefix _build/local playwright-core
// Generate _build/local/widget.html using MODX's dashboard widget renderer first.
const { chromium } = require('./local/node_modules/playwright-core');
const fs = require('fs');
const assert = require('assert/strict');
(async () => {
    const browser = await chromium.launch({executablePath: process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless:true});
    try {
        const page = await browser.newPage({viewport:{width:1280,height:900}});
        const errors = [], highstockRequests = [];
        page.on('pageerror', error => errors.push(error.message));
        page.on('request', request => { if (/highstock|highcharts/i.test(request.url())) highstockRequests.push(request.url()); });
        const ready = () => page.waitForFunction(() => document.querySelector('[data-gs-echart]')?.gsChart);
        await page.goto(process.env.GROWATTSTATS_TEST_URL || 'http://modx-2.8.6.test/', {waitUntil:'load'});
        await ready();
        for (const mode of ['frontend','widget']) {
            if (mode === 'widget') {
                await page.goto('http://modx-2.8.6.test/',{waitUntil:'load'});
                // setContent replaces documentElement, unlike normal Manager panel updates.
                // Reset the bootstrap so its observer attaches to the new document tree.
                await page.evaluate(() => { delete window.GrowattStatsCharts; });
                await page.setContent(fs.readFileSync(__dirname+'/local/widget.html','utf8'),{waitUntil:'load'});
                await page.addStyleTag({content:fs.readFileSync(process.env.MODX_MANAGER_CSS || 'D:/laragon/www/MODX-2.8.6/manager/templates/default/css/index-min.css','utf8')});
                await ready();
            }
            const info = await page.evaluate(() => {
                const root = document.querySelector('[data-gs-echart]');
                return {points:root.gsChart.points,gaps:root.gsChart.gaps,locale:JSON.parse(root.dataset.gsEchart).labels.locale,engine:root.gsChart.engine};
            });
            assert(info.points > 0); assert.equal(info.engine,'echarts');
            for (let i=0;i<6;i++) {
                await page.locator('.gs-periods button').nth(i).click();
                assert.equal(await page.locator('.gs-periods button').nth(i).getAttribute('aria-pressed'),'true');
            }
            for (const width of [1280,390]) {
                await page.setViewportSize({width,height:900});
                await page.waitForTimeout(150);
                const layout = await page.evaluate(() => {
                    const root = document.querySelector('[data-gs-echart]');
                    const buttons=[...root.querySelectorAll('button')].map(b=>b.getBoundingClientRect());
                    return {overflow:root.scrollWidth>root.clientWidth+1,overlap:buttons.some((a,i)=>buttons.slice(i+1).some(b=>a.left<b.right&&a.right>b.left&&a.top<b.bottom&&a.bottom>b.top))};
                });
                assert(!layout.overflow && !layout.overlap);
                await page.locator(mode==='frontend'?'.js-growattstats':'.js-growattstats-widget').first().screenshot({path:__dirname+'/local/echarts-'+mode+'-'+width+'.png'});
            }
            console.log('PASS '+mode+' '+JSON.stringify(info));
        }
        // Independent instances, sparse data, no history and single-point series.
        await page.evaluate(() => {
            const original = document.querySelector('[data-gs-echart]');
            const labels=JSON.parse(original.dataset.gsEchart).labels;
            window.testOriginalChart=original.gsChart.chart;
            [[],[[Date.UTC(2026,0,1),12]],[[Date.UTC(2026,0,1),3],[Date.UTC(2026,0,4),9]]].forEach((series,i)=>{
                const root=document.createElement('div');root.className='gs-echart';root.id='fixture-'+i;
                root.dataset.gsEchart=JSON.stringify({series,labels});
                root.innerHTML='<div class="gs-periods"></div><div class="gs-plot"></div><p class="gs-chart-status"></p>';
                document.body.appendChild(root);
            });
        });
        await page.waitForFunction(()=>document.getElementById('fixture-2').gsChart);
        const edge=await page.evaluate(()=>{
            const root=document.getElementById('fixture-2');
            const series=root.gsChart.chart.getOption().series;
            const empty=document.getElementById('fixture-0');
            return {empty:empty.querySelector('.gs-chart-status').textContent===JSON.parse(empty.dataset.gsEchart).labels.compare_empty,
                single:document.getElementById('fixture-1').gsChart.points,
                gaps:root.gsChart.gaps,nullBreak:series[0].data.some(p=>p[1]===null),dashed:series[1].lineStyle.type,
                independent:root.gsChart.chart!==window.testOriginalChart};
        });
        assert.deepEqual(edge,{empty:true,single:1,gaps:1,nullBreak:true,dashed:'dashed',independent:true});
        await page.evaluate(()=>{window.removedChart=document.getElementById('fixture-2').gsChart.chart;document.getElementById('fixture-2').remove();});
        await page.waitForFunction(()=>window.removedChart.isDisposed());
        assert.deepEqual(errors,[]); assert.deepEqual(highstockRequests,[]);
        console.log('PASS edge cases, independent instances, disposal; no JS errors or Highstock requests');
    } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
