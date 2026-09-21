<?php

namespace GrowattStats;

use modX;

class Service
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

    /**
     * Return a setting value from growattStats namespace.
     */
    protected function getSetting($key, $default = '')
    {
        return $this->modx->getOption('growattstats_' . $key, null, $default);
    }

    /**
     * Get current UTC midnight timestamp in milliseconds.
     */
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

    protected function buildApiUrl($url, array $query = [])
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        if (!empty($query)) {
            $glue = (strpos($url, '?') === false) ? '?' : '&';
            $url .= $glue . http_build_query($query);
        }

        return $url;
    }

    protected function getApiHeaders(array $headers = [])
    {
        $result = [
            'Accept: application/json',
        ];

        $token = trim((string)($this->getSetting('token', '') ?: $this->getSetting('token_id', '')));
        if ($token !== '') {
            $result[] = 'token: ' . $token;
        }

        foreach ($headers as $header) {
            $header = trim((string)$header);
            if ($header !== '') {
                $result[] = $header;
            }
        }

        return array_values(array_unique($result));
    }

    public function requestApi(
        $method,
        $url,
        array $query = [],
        array $body = [],
        array $headers = [],
        array $options = []
    ) {
        $method = strtoupper(trim((string)$method ?: 'GET'));
        if (!in_array($method, ['GET', 'POST', 'PUT'], true)) {
            return [
                'success' => false,
                'error' => $this->modx->lexicon('growattstats_error_method') . ': ' . $method,
                'http_code' => 0,
                'method' => $method,
                'url' => $url,
            ];
        }

        $requestUrl = $this->buildApiUrl($url, $query);
        if ($requestUrl === '') {
            return [
                'success' => false,
                'error' => $this->modx->lexicon('growattstats_error_url'),
                'http_code' => 0,
                'method' => $method,
                'url' => $requestUrl,
            ];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => $this->modx->lexicon('growattstats_error_curl'), 'http_code' => 0];
        }

        $timeout = isset($options['timeout']) ? (int)$options['timeout'] : 20;
        $curl = curl_init();
        $curlHeaders = $this->getApiHeaders($headers);
        $curlOptions = [
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $curlHeaders,
        ];

        if ($method !== 'GET' && !empty($body)) {
            $bodyFormat = strtolower((string)($options['body_format'] ?? 'json'));
            if ($bodyFormat === 'form') {
                $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($body);
                $curlHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
                $curlOptions[CURLOPT_HTTPHEADER] = $curlHeaders;
            } else {
                $encodedBody = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if ($encodedBody === false) {
                    return [
                        'success' => false,
                        'error' => $this->modx->lexicon('growattstats_error_body'),
                        'http_code' => 0,
                        'method' => $method,
                        'url' => $requestUrl,
                    ];
                }
                $curlOptions[CURLOPT_POSTFIELDS] = $encodedBody;
                $curlHeaders[] = 'Content-Type: application/json';
                $curlOptions[CURLOPT_HTTPHEADER] = $curlHeaders;
            }
        }

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false || $response === null || $httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'error' => $curlError !== ''
                    ? $curlError : $this->modx->lexicon('growattstats_error_http') . ': ' . $httpCode,
                'http_code' => $httpCode,
                'method' => $method,
                'url' => $requestUrl,
                'raw' => is_string($response) ? $response : '',
            ];
        }

        $decoded = json_decode((string)$response, true);
        $apiError = null;
        if (is_array($decoded)) {
            $errorCode = $decoded['error_code'] ?? null;
            $errorMsg = isset($decoded['error_msg']) ? trim((string)$decoded['error_msg']) : '';
            if ((is_numeric($errorCode) && (int)$errorCode !== 0) || $errorMsg !== '') {
                $apiError = $this->modx->lexicon('growattstats_error_api')
                    . ($errorCode !== null ? ' #' . $errorCode : '');
            }
        }

        return [
            'success' => $apiError === null && is_array($decoded),
            'http_code' => $httpCode,
            'method' => $method,
            'url' => $requestUrl,
            'raw' => (string)$response,
            'decoded' => is_array($decoded) ? $decoded : null,
            'api_error' => $apiError,
        ];
    }

    protected function getGrowattCommandMap()
    {
        return [
            'plant_data' => [
                'method' => 'GET',
                'url' => $this->getSetting('api_url', 'https://openapi.growatt.com/v1/plant/data'),
                'query' => [
                    'plant_id' => $this->getSetting('plant_id', ''),
                ],
            ],
            'plant_energy' => [
                'method' => 'GET',
                'url' => 'https://openapi.growatt.com/v1/plant/energy',
                'query' => [
                    'plant_id' => $this->getSetting('plant_id', ''),
                ],
            ],
            'plant_power' => [
                'method' => 'GET',
                'url' => 'https://openapi.growatt.com/v1/plant/power',
                'query' => [
                    'plant_id' => $this->getSetting('plant_id', ''),
                ],
            ],
            'plant_details' => [
                'method' => 'GET',
                'url' => 'https://openapi.growatt.com/v1/plant/details',
                'query' => [
                    'plant_id' => $this->getSetting('plant_id', ''),
                ],
            ],
            'device_inverter_data' => [
                'method' => 'GET',
                'url' => 'https://openapi.growatt.com/v1/device/inverter/data',
            ],
            'device_inverter_day_energy' => [
                'method' => 'GET',
                'url' => 'https://openapi.growatt.com/v1/device/inverter/day_energy',
            ],
            'device_inverter_details' => [
                'method' => 'GET',
                'url' => 'https://openapi.growatt.com/v1/device/inverter/details',
            ],
        ];
    }

    public function requestGrowattCommand(
        $command,
        array $query = [],
        array $body = [],
        array $headers = [],
        array $options = []
    ) {
        $map = $this->getGrowattCommandMap();
        $key = strtolower(trim((string)$command));
        if ($key === '' || empty($map[$key])) {
            return [
                'success' => false,
                'error' => $this->modx->lexicon('growattstats_error_command') . ': ' . $command,
                'http_code' => 0,
                'method' => 'GET',
                'url' => '',
            ];
        }

        $definition = $map[$key];
        $method = $definition['method'] ?? 'GET';
        $url = $definition['url'] ?? '';
        $defaultQuery = isset($definition['query']) && is_array($definition['query']) ? $definition['query'] : [];
        $defaultBody = isset($definition['body']) && is_array($definition['body']) ? $definition['body'] : [];
        $defaultHeaders = isset($definition['headers']) && is_array($definition['headers'])
            ? $definition['headers'] : [];
        $defaultOptions = isset($definition['options']) && is_array($definition['options'])
            ? $definition['options'] : [];

        $query = array_merge($defaultQuery, $query);
        $body = array_merge($defaultBody, $body);
        $headers = array_merge($defaultHeaders, $headers);
        $options = array_merge($defaultOptions, $options);

        return $this->requestApi($method, $url, $query, $body, $headers, $options);
    }

    public function requestPagedGrowattCommand(
        $command,
        array $query = [],
        array $body = [],
        array $headers = [],
        array $options = []
    ) {
        $pageKey = isset($options['page_key']) ? (string)$options['page_key'] : 'page';
        $perPageKey = isset($options['per_page_key']) ? (string)$options['per_page_key'] : 'perpage';
        $resultKey = isset($options['result_key']) ? (string)$options['result_key'] : 'data';
        $page = max(1, (int)($options['page'] ?? 1));
        $perPage = max(1, (int)($options['per_page'] ?? 50));
        $maxPages = max(1, (int)($options['max_pages'] ?? 50));
        $items = [];
        $pages = [];
        $lastResult = null;

        for ($index = 0; $index < $maxPages; $index++) {
            $pagedQuery = $query;
            $pagedQuery[$pageKey] = $page;
            $pagedQuery[$perPageKey] = $perPage;

            $result = $this->requestGrowattCommand($command, $pagedQuery, $body, $headers, $options);
            if (empty($result['success'])) {
                return $result + [
                    'pages' => $pages,
                    'items' => $items,
                ];
            }

            $pages[] = $result;
            $lastResult = $result;

            $decoded = isset($result['decoded']) && is_array($result['decoded']) ? $result['decoded'] : [];
            $pageData = $decoded[$resultKey] ?? $decoded['data'] ?? $decoded;
            if (is_array($pageData)) {
                if (array_keys($pageData) === range(0, count($pageData) - 1)) {
                    $items = array_merge($items, $pageData);
                } else {
                    $items[] = $pageData;
                }
            }

            $count = null;
            if (isset($decoded['count']) && is_numeric($decoded['count'])) {
                $count = (int)$decoded['count'];
            } elseif (isset($decoded['total']) && is_numeric($decoded['total'])) {
                $count = (int)$decoded['total'];
            }

            $currentSize = is_array($pageData) ? count($pageData) : 0;
            if ($currentSize < $perPage) {
                break;
            }
            if ($count !== null && count($items) >= $count) {
                break;
            }

            $page++;
        }

        return [
            'success' => true,
            'command' => $command,
            'pages' => $pages,
            'items' => $items,
            'last_result' => $lastResult,
        ];
    }

    public function requestPlantData(array $query = [], array $headers = [], array $options = [])
    {
        return $this->requestGrowattCommand('plant_data', $query, [], $headers, $options);
    }

    public function requestPlantEnergy(array $query = [], array $headers = [], array $options = [])
    {
        return $this->requestGrowattCommand('plant_energy', $query, [], $headers, $options);
    }

    public function requestDeviceInverterData(array $query = [], array $headers = [], array $options = [])
    {
        return $this->requestGrowattCommand('device_inverter_data', $query, [], $headers, $options);
    }

    public function requestDeviceInverterDayEnergy(array $query = [], array $headers = [], array $options = [])
    {
        return $this->requestGrowattCommand('device_inverter_day_energy', $query, [], $headers, $options);
    }

    public function requestPlantPower(array $query = [], array $headers = [], array $options = [])
    {
        return $this->requestGrowattCommand('plant_power', $query, [], $headers, $options);
    }

    public function requestPlantDetails(array $query = [], array $headers = [], array $options = [])
    {
        return $this->requestGrowattCommand('plant_details', $query, [], $headers, $options);
    }

    public function requestDeviceInverterDetails(array $query = [], array $headers = [], array $options = [])
    {
        return $this->requestGrowattCommand('device_inverter_details', $query, [], $headers, $options);
    }

    /**
     * Load raw payload from the JSON cache.
     */
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
            empty($payload['api'])
        ) {
            $legacyPayload = $this->loadLegacyChartPayload();
            if (!empty($legacyPayload)) {
                $this->saveChartPayload($legacyPayload);
                return $legacyPayload;
            }
        }

        return $payload;
    }

    /**
     * Migrate the old JS cache format if it is still present.
     */
    protected function loadLegacyChartPayload()
    {
        $legacyFile = preg_replace('#\.json$#', '.js', $this->config['dataFile']);
        if (!$legacyFile || !file_exists($legacyFile)) {
            return [];
        }

        $content = (string)file_get_contents($legacyFile);
        if (preg_match('#growattStatsData\s*=\s*(\{.*?\})\s*;#s', $content, $match)) {
            $payload = json_decode($match[1], true);
            if (is_array($payload) && isset($payload['series']) && is_array($payload['series'])) {
                return $payload;
            }
        }

        if (
            !preg_match_all(
                '#Date\.UTC\((\d+),\s*(\d+),\s*(\d+)\),\s*([0-9.]+)#',
                $content,
                $matches,
                PREG_SET_ORDER
            )
        ) {
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

    /**
     * Persist the chart payload as JSON.
     */
    protected function saveChartPayload(array $payload)
    {
        $dataFile = $this->config['dataFile'];
        $dir = dirname($dataFile);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[growattStats] ' . $this->modx->lexicon('growattstats_error_directory') . ': ' . $dir
            );
            return false;
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[growattStats] ' . $this->modx->lexicon('growattstats_error_json')
            );
            return false;
        }

        return file_put_contents($dataFile, $json . PHP_EOL, LOCK_EX) !== false;
    }

    /**
     * Get data from Growatt API.
     */
    public function renderTemplateFile($path, array $placeholders = [])
    {
        $path = (string)$path;
        if ($path === '' || !is_file($path)) {
            return '';
        }

        $content = (string)file_get_contents($path);
        if ($content === '') {
            return '';
        }

        return $this->parseTemplatePlaceholders($content, $placeholders);
    }

    protected function parseTemplatePlaceholders($content, array $placeholders = [])
    {
        $replacements = [];
        foreach ($placeholders as $key => $value) {
            $replacements['[[+' . $key . ']]'] = is_scalar($value) || $value === null
                ? (string)$value
                : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return strtr((string)$content, $replacements);
    }

    public function fetchApiData()
    {
        $result = $this->requestPlantData();
        $data = $result['decoded']['data'] ?? null;
        if (empty($result['success']) || !is_array($data) || !isset($data['today_energy'], $data['total_energy'])) {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[growattStats] ' . $this->modx->lexicon('growattstats_error_request')
                . ': ' . ($result['http_code'] ?? 0)
            );
            return false;
        }
        return $data;
    }

    /**
     * Return chart payload, refreshing from API when needed.
     */
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

    /**
     * Update the stored JSON cache from API data.
     */
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

    /**
     * Refresh the JSON cache from API.
     */
    public function refreshCache()
    {
        $apiData = $this->fetchApiData();
        if (!$apiData) {
            return false;
        }

        return $this->updateChartData($apiData);
    }

    /**
     * Prepare template data for frontend and dashboard rendering.
     */
    public function getDisplayData(array $options = [])
    {
        $price = isset($options['price'])
            ? (float)$options['price']
            : (float)$this->getSetting('price', 1.20);
        $plantName = trim((string)($options['plantName'] ?? $options['plant_name'] ?? ''));

        $payload = $this->getChartPayload(false);
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

    /**
     * Register CSS/JS assets.
     */
    public function registerAssets()
    {
        $this->modx->regClientStartupScript($this->getAssetTags(), true);
    }

    public function getAssetTags()
    {
        return '<script src="' . $this->config['jsUrl'] . 'highstock.js"></script>';
    }

    public function getChartScript(array $series = [])
    {
        $seriesJson = json_encode(array_values($series), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if ($seriesJson === false) {
            $seriesJson = '[]';
        }
        // Manager widget output passes through the MODX tag parser: avoid literal [[...]].
        $seriesJson = str_replace(
            ['[', ']'],
            ['\\u005B', '\\u005D'],
            json_encode($seriesJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        );

        $labels = [];
        foreach (
            ['series', 'locale', 'range_month', 'range_quarter', 'range_half_year', 'range_ytd',
            'range_year', 'range_all', 'zoom', 'reset_zoom', 'reset_zoom_title', 'loading'] as $key
        ) {
            $labels[$key] = $this->modx->lexicon('growattstats_' . $key);
        }
        $labelsJson = json_encode($labels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $labelsJson = str_replace(['[', ']'], ['\\u005B', '\\u005D'], $labelsJson);

        $script = '<script>
(function () {
    var seriesData = JSON.parse(' . $seriesJson . ');
    var labels = ' . $labelsJson . ';

    function initGrowattChart() {
        if (typeof Highcharts === "undefined") {
            setTimeout(initGrowattChart, 100);
            return;
        }

        var chart = Highcharts.stockChart("growattstats-container", {
            chart: { zoomType: "x" },
            lang: { locale: labels.locale, rangeSelectorZoom: labels.zoom, resetZoom: labels.reset_zoom,
                resetZoomTitle: labels.reset_zoom_title, loading: labels.loading },
            xAxis: { minRange: 3600 * 1000 },
            rangeSelector: {
                selected: 5,
                inputEnabled: false,
                buttonTheme: { width: null, padding: 6 },
                buttons: [
                    { type: "month", count: 1, text: labels.range_month },
                    { type: "month", count: 3, text: labels.range_quarter },
                    { type: "month", count: 6, text: labels.range_half_year },
                    { type: "ytd", text: labels.range_ytd },
                    { type: "year", count: 1, text: labels.range_year },
                    { type: "all", text: labels.range_all }
                ]
            },
            title: { text: null },
            series: [{
                name: labels.series,
                data: seriesData,
                color: "#2e5a90"
            }]
        });
        // Recalculate positions after SVG labels have their final measured widths.
        chart.redraw(false);
    }

    initGrowattChart();
}());
</script>';
        return $script;
    }

    public function registerChartScript(array $series = [])
    {
        $this->modx->regClientScript($this->getChartScript($series), true);
    }

    public function refreshCacheMessage()
    {
        return $this->refreshCache()
            ? '[growattStats] ' . $this->modx->lexicon('growattstats_cron_success')
            : '[growattStats] ' . $this->modx->lexicon('growattstats_cron_failed');
    }

    public function getStats()
    {
        $data = $this->getDisplayData();
        $this->registerAssets();
        $this->registerChartScript($data['series'] ?? []);

        return $data;
    }
}
