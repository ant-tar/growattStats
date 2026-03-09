<?php

class growattStats
{
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config = [];

    /**
     * @param modX  $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        $corePath   = $modx->getOption('growattstats_core_path', null,
            MODX_CORE_PATH . 'components/growattstats/');
        $assetsUrl  = $modx->getOption('growattstats_assets_url', null,
            MODX_ASSETS_URL . 'components/growattstats/');
        $assetsPath = $modx->getOption('growattstats_assets_path', null,
            MODX_ASSETS_PATH . 'components/growattstats/');

        $this->config = array_merge([
            'corePath'    => $corePath,
            'modelPath'   => $corePath . 'model/',
            'assetsUrl'   => $assetsUrl,
            'assetsPath'  => $assetsPath,
            'cssUrl'      => $assetsUrl . 'css/',
            'jsUrl'       => $assetsUrl . 'js/',
            'dataFile'    => $assetsPath . 'data/chart-data.js',
            'dataUrl'     => $assetsUrl . 'data/chart-data.js',
        ], $config);

        $this->modx->lexicon->load('growattstats:default');
    }

    /**
     * Получить данные с Growatt API.
     * plant_id и token_id берутся из системных настроек MODX.
     *
     * @return array|false
     */
    public function fetchApiData()
    {
        $plantId = $this->modx->getOption('growattstats_plant_id', null, '');
        $token   = $this->modx->getOption('growattstats_token_id', null, '');

        if (empty($plantId) || empty($token)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,
                '[growattStats] Системные настройки growattstats_plant_id или growattstats_token_id не заданы');
            return false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://openapi.growatt.com/v1/plant/data?plant_id=' . urlencode($plantId),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => ['token: ' . $token],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!$response || $httpCode !== 200) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,
                '[growattStats] Ошибка API, HTTP код: ' . $httpCode);
            return false;
        }

        $decoded = json_decode($response, true);
        if (empty($decoded['data'])) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,
                '[growattStats] API вернул пустой data, ответ: ' . $response);
            return false;
        }

        return $decoded['data'];
    }

    /**
     * Зарегистрировать CSS и JS ресурсы на фронтенде.
     */
    public function registerAssets()
    {
        $this->modx->regClientCSS($this->config['cssUrl'] . 'bootstrap.css');
        $this->modx->regClientCSS($this->config['cssUrl'] . 'styles.css');
        $this->modx->regClientStartupScript(
            '<script src="' . $this->config['jsUrl'] . 'jquery-1.11.1.min.js"></script>', true);
        $this->modx->regClientStartupScript(
            '<script src="' . $this->config['jsUrl'] . 'bootstrap.min.js"></script>', true);
        $this->modx->regClientStartupScript(
            '<script src="//code.highcharts.com/stock/highstock.js"></script>', true);
        $this->modx->regClientStartupScript(
            '<script src="' . $this->config['dataUrl'] . '"></script>', true);
    }

    /**
     * Зарегистрировать скрипт инициализации графика Highcharts.
     * Вызывать после registerAssets().
     */
    public function registerChartScript()
    {
        $script = '<script>
(function () {
    function initGrowattChart() {
        if (typeof Highcharts === "undefined" || typeof usdeur === "undefined") {
            setTimeout(initGrowattChart, 100);
            return;
        }
        Highcharts.stockChart("growattstats-container", {
            chart: {zoomType: "x"},
            xAxis: {minRange: 3600},
            rangeSelector: {selected: 1, labelStyle: {display: "none"}},
            plotOptions: {
                series: {
                    fillColor: {
                        linearGradient: [0, 0, 0, 300],
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.color(Highcharts.getOptions().colors[0])
                                .setOpacity(0).get("rgba")]
                        ]
                    }
                }
            },
            series: [{name: "Generation (kWh)", data: usdeur, color: "#2e5a90"}]
        });
    }
    initGrowattChart();
}());
</script>';
        $this->modx->regClientScript($script, true);
    }

    /**
     * Обновить файл chart-data.js новой точкой данных.
     *
     * @param array $apiData  Данные из fetchApiData()
     * @return bool
     */
    public function updateChartData(array $apiData)
    {
        $dataFile   = $this->config['dataFile'];
        $todayEnergy = isset($apiData['today_energy']) ? (float)$apiData['today_energy'] : 0;
        $dateJs     = 'Date.UTC(' . date('Y') . ',' . (date('n') - 1) . ',' . date('j') . ')';
        $newPoint   = [$dateJs, $todayEnergy];

        // Загрузить существующие данные из файла
        $existingData = [];
        if (file_exists($dataFile)) {
            $content = file_get_contents($dataFile);
            $start   = strpos($content, '[');
            $end     = strrpos($content, ']');
            if ($start !== false && $end !== false) {
                $json = substr($content, $start, $end - $start + 1);
                // Восстановить JSON: Date.UTC(...) не является валидным JSON,
                // поэтому оборачиваем в строки
                $json = str_replace('),', ')","',  $json);
                $json = str_replace('[[',  '[["',  $json);
                $json = str_replace('[Date', '["Date', $json);
                $json = str_replace(']]',  '"]]',  $json);
                $json = str_replace('],',  '"],',  $json);
                $existingData = json_decode($json, true) ?: [];
            }
        }

        // Обновить или добавить точку
        $found = false;
        foreach ($existingData as $k => $point) {
            if ($point[0] === $dateJs) {
                if ($todayEnergy >= (float)$point[1]) {
                    $existingData[$k] = $newPoint;
                }
                $found = true;
                break;
            }
        }
        if (!$found) {
            $existingData[] = $newPoint;
        }

        // Записать файл: убираем кавычки вокруг Date.UTC(...)
        $jsonOut = json_encode($existingData);
        $jsOut   = 'var usdeur = ' . str_replace('"', '', $jsonOut) . ';';

        return file_put_contents($dataFile, $jsOut) !== false;
    }
}
