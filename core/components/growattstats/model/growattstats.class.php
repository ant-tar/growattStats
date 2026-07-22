<?php

class growattStats
{
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config = [];

    /**
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        $corePath = $modx->getOption('growattstats_core_path', null, MODX_CORE_PATH . 'components/growattstats/');
        $assetsUrl = $modx->getOption('growattstats_assets_url', null, MODX_ASSETS_URL . 'components/growattstats/');
        $assetsPath = $modx->getOption('growattstats_assets_path', null, MODX_ASSETS_PATH . 'components/growattstats/');

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'assetsUrl' => $assetsUrl,
            'assetsPath' => $assetsPath,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'dataFile' => $assetsPath . 'data/chart-data.json',
        ], $config);

        $this->modx->lexicon->load('growattstats:default');
    }

    protected function getSetting($key, $default = '')
    {
        return $this->modx->getOption('growattstats_' . $key, null, $default);
    }

    protected function getTodayTimestamp()
    {
        return (int)gmmktime(
            0,
            0,
            0,
            (int)gmdate('n'),
            (int)gmdate('j'),
            (int)gmdate('Y')
        ) * 1000;
    }

    protected function loadLegacyChartPayload()
    {
        $legacyFile = preg_replace('#\.json$#', '.js', $this->config['dataFile']);
        if (!$legacyFile || !file_exists($legacyFile)) {
            return [];
        }

        $content = (string)file_get_contents($legacyFile);
        if (!preg_match_all(
            '#Date\.UTC\((\d+),\s*(\d+),\s*(\d+)\),\s*([0-9.]+)#',
            $content,
            $matches,
            PREG_SET_ORDER
        )) {
            return [];
        }

        $series = [];
        foreach ($matches as $match) {
            $series[] = [
                gmmktime(
                    0,
                    0,
                    0,
                    (int)$match[2] + 1,
                    (int)$match[3],
                    (int)$match[1]
                ) * 1000,
                (float)$match[4],
            ];
        }

        if (empty($series)) {
            return [];
        }

        return [
            'updated_at' => gmdate('c'),
            'series' => $series,
        ];
    }

    protected function loadChartPayload()
    {
        $dataFile = $this->config['dataFile'];
        if (!file_exists($dataFile)) {
            return $this->loadLegacyChartPayload();
        }

        $payload = json_decode((string)file_get_contents($dataFile), true);
        if (!is_array($payload)) {
            return $this->loadLegacyChartPayload();
        }

        if (empty($payload['series']) || !is_array($payload['series'])) {
            $payload['series'] = [];
        }

        if (
            empty($payload['series']) &&
            empty($payload['api']) &&
            !isset($payload['today_energy']) &&
            !isset($payload['total_energy'])
        ) {
            $legacyPayload = $this->loadLegacyChartPayload();
            if (!empty($legacyPayload)) {
                $this->saveChartPayload($legacyPayload);
                return $legacyPayload;
            }
        }

        return $payload;
    }

    protected function saveChartPayload(array $payload)
    {
        $dataFile = $this->config['dataFile'];
        $dir = dirname($dataFile);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] Could not create data directory: ' . $dir);
            return false;
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[growattStats] Could not encode chart payload to JSON');
            return false;
        }

        return file_put_contents($dataFile, $json . PHP_EOL, LOCK_EX) !== false;
    }

    public function fetchApiData()
    {
        $plantId = trim((string)$this->getSetting('plant_id', ''));
        $token = trim((string)$this->getSetting('token_id', ''));
        $apiUrl = trim((string)$this->getSetting('api_url', 'https://openapi.growatt.com/v1/plant/data'));

        if ($plantId === '' || $token === '') {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[growattStats] System settings growattstats_plant_id or growattstats_token_id are missing'
            );
            return false;
        }

        if ($apiUrl === '') {
            $apiUrl = 'https://openapi.growatt.com/v1/plant/data';
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => rtrim($apiUrl, '?&') . '?plant_id=' . urlencode($plantId),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'token: ' . $token,
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (!$response || $httpCode !== 200) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[growattStats] API request failed, HTTP code: ' . $httpCode . ($curlError ? '; ' . $curlError : '')
            );
            return false;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || empty($decoded['data']) || !is_array($decoded['data'])) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[growattStats] API returned invalid payload: ' . $response
            );
            return false;
        }

        return $decoded['data'];
    }

    public function updateChartData(array $apiData)
    {
        $payload = $this->loadChartPayload();
        if (!is_array($payload)) {
            $payload = [];
        }

        $series = isset($payload['series']) && is_array($payload['series']) ? $payload['series'] : [];
        $todayEnergy = isset($apiData['today_energy']) ? (float)$apiData['today_energy'] : 0.0;
        $todayPoint = [$this->getTodayTimestamp(), $todayEnergy];

        $found = false;
        foreach ($series as $index => $point) {
            if (!is_array($point) || count($point) < 2) {
                continue;
            }
            if ((int)$point[0] === $todayPoint[0]) {
                $series[$index] = $todayPoint;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $series[] = $todayPoint;
        }

        $payload['updated_at'] = gmdate('c');
        $payload['today_energy'] = $todayEnergy;
        $payload['total_energy'] = isset($apiData['total_energy']) ? (float)$apiData['total_energy'] : 0.0;
        $payload['api'] = $apiData;
        $payload['series'] = array_values($series);

        return $this->saveChartPayload($payload);
    }

    public function refreshCache()
    {
        $apiData = $this->fetchApiData();
        if (!$apiData) {
            return false;
        }

        return $this->updateChartData($apiData);
    }

    public function getChartPayload($refreshIfMissing = true)
    {
        $payload = $this->loadChartPayload();

        if (empty($payload) && $refreshIfMissing) {
            $apiData = $this->fetchApiData();
            if ($apiData) {
                $this->updateChartData($apiData);
                $payload = $this->loadChartPayload();
            }
        }

        return is_array($payload) ? $payload : [];
    }

    public function getDisplayData(array $options = [])
    {
        $price = isset($options['price'])
            ? (float)$options['price']
            : (float)$this->getSetting('price', 1.20);
        $plantName = trim((string)($options['plantName'] ?? $options['plant_name'] ?? ''));

        $payload = $this->getChartPayload(true);
        if (empty($payload['api']) || !isset($payload['today_energy']) || !isset($payload['total_energy'])) {
            $this->refreshCache();
            $payload = $this->getChartPayload(false);
        }

        $apiData = isset($payload['api']) && is_array($payload['api']) ? $payload['api'] : [];
        $series = isset($payload['series']) && is_array($payload['series']) ? array_values($payload['series']) : [];
        $fallbackEnergy = 0.0;
        if (!empty($series)) {
            $lastPoint = end($series);
            if (is_array($lastPoint) && isset($lastPoint[1])) {
                $fallbackEnergy = (float)$lastPoint[1];
            }
        }

        $data = array_merge($apiData, [
            'today_energy' => isset($payload['today_energy']) ? (float)$payload['today_energy'] : $fallbackEnergy,
            'total_energy' => isset($payload['total_energy']) ? (float)$payload['total_energy'] : $fallbackEnergy,
            'today_revenue' => 0.0,
            'total_revenue' => 0.0,
            'plant_name' => $plantName !== '' ? $plantName : (string)$this->getSetting('plant_name', ''),
            'series' => $series,
            'updated_at' => $payload['updated_at'] ?? null,
        ]);

        $data['today_revenue'] = round($price * (float)$data['today_energy'], 2);
        $data['total_revenue'] = round($price * (float)$data['total_energy'], 2);

        return $data;
    }

    public function registerAssets()
    {
        $this->modx->regClientCSS($this->config['cssUrl'] . 'bootstrap.css');
        $this->modx->regClientCSS($this->config['cssUrl'] . 'styles.css');
        $this->modx->regClientStartupScript(
            '<script src="' . $this->config['jsUrl'] . 'jquery-1.11.1.min.js"></script>',
            true
        );
        $this->modx->regClientStartupScript(
            '<script src="' . $this->config['jsUrl'] . 'bootstrap.min.js"></script>',
            true
        );
        $this->modx->regClientStartupScript(
            '<script src="' . $this->config['jsUrl'] . 'highstock.js"></script>',
            true
        );
    }

    public function registerChartScript(array $series = [])
    {
        $seriesJson = json_encode(array_values($series), JSON_UNESCAPED_SLASHES);
        if ($seriesJson === false) {
            $seriesJson = '[]';
        }

        $script = '<script>
(function () {
    var seriesData = ' . $seriesJson . ';

    function initGrowattChart() {
        if (typeof Highcharts === "undefined") {
            setTimeout(initGrowattChart, 100);
            return;
        }

        Highcharts.stockChart("growattstats-container", {
            chart: { zoomType: "x" },
            xAxis: { minRange: 3600 * 1000 },
            rangeSelector: {
                selected: 5,
                inputEnabled: false,
                buttons: [
                    { type: "month", count: 1, text: "1m" },
                    { type: "month", count: 3, text: "3m" },
                    { type: "month", count: 6, text: "6m" },
                    { type: "ytd", text: "YTD" },
                    { type: "year", count: 1, text: "1y" },
                    { type: "all", text: "All" }
                ]
            },
            title: { text: null },
            series: [{
                name: "Generation (kWh)",
                data: seriesData,
                color: "#2e5a90"
            }]
        });
    }

    initGrowattChart();
}());
</script>';

        $this->modx->regClientScript($script, true);
    }

    public function getStats()
    {
        $data = $this->getDisplayData();
        $this->registerAssets();
        $this->registerChartScript($data['series'] ?? []);

        return $data;
    }
}
