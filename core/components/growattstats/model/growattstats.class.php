<?php

class growattStats
{
    /** @var modX $modx */
    public $modx;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/growattstats/';
        $assetsUrl = MODX_ASSETS_URL . 'components/growattstats/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('growattstats', $this->config['modelPath']);
        $this->modx->lexicon->load('growattstats:default');
    }
	public function getStats(){
		$stats = 'ok	';

		$this->modx->regClientCSS('/assets/components/growattstats/lumino/css/bootstrap.css');
	///	$this->modx->regClientCSS('/assets/components/growattstats/lumino/css/datepicker3.css');
		$this->modx->regClientCSS('/assets/components/growattstats/lumino/css/styles.css');

		$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"/assets/components/growattstats/lumino/js/jquery-1.11.1.min.js\"></script>", true);
		$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"/assets/components/growattstats/lumino/js/bootstrap.min.js\"></script>", true);
		//$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"/assets/components/growattstats/lumino/js/chart.min.js\"></script>", true);
		//$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"/assets/components/growattstats/lumino/js/chart-data.js\"></script>", true);
///		$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"/assets/components/growattstats/lumino/js/easypiechart.js\"></script>", true);
	
///		$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"/assets/components/growattstats/lumino/js/bootstrap-datepicker.js\"></script>", true);
		
		$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"//code.highcharts.com/stock/highstock.js\"></script>", true);
	//	$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"//code.highcharts.com/stock/modules/exporting.js\"></script>", true);
		$this->modx->regClientStartupScript("<script type=\"text/javascript\" src=\"/assets/components/growattstats/data/chart-data.js\"></script>", true);
		
	//	require_once $this->config['modelPath'].'/growattstats/minishop2.class.php';
	//	$stats_class = $this->modx->getOption('growattstats_namespace', null, 'minishop2_shop');
	///	if ($stats_class != 'minishop2_shop') {$this->loadCustomClasses($stats_class);}


	//	$this->shop = new $stats_class($this, $this->config);
	///	if (!($this->shop instanceof statsInterface) || $this->shop->initialize($ctx) !== true) {
	//		$this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not initialize shop class: "'.$stats_class.'"');
	///		return false;
	//	}

	//	$stats = $this->shop->getStats();
/*
		foreach($stats['total_counts'] as $status_key => $status){
			foreach($stats['stats_month'] as $month_key => $month){
		        $labels[$month_key] = '"'.$month_key.'"';
		        if(count($month[$status_key]) > 0){
		            $dataCount[$status_key][$month_key] = $month[$status_key]['count_orders'];
		        }else{
		            $dataCount[$status_key][$month_key] = 0;
		        }

		        $dataCost[$status_key][$month_key] = !empty($month[$status_key]['total_cost']) ? $month[$status_key]['total_cost'] : 0;
		    }
		    $datasetsCount[] = '{
				label: "'.$status['name'].'",
				fillColor : "rgba(220,220,220,0.2)",
				strokeColor : "#'.$status['color'].'",
				pointColor : "#'.$status['color'].'",
				pointStrokeColor : "#'.$status['color'].'",
				pointHighlightFill : "#'.$status['color'].'",
				pointHighlightStroke : "#'.$status['color'].'",
				data : [0,'.implode(",", $dataCount[$status_key]).'],
				options:{
				    scales: {
				        y: {
				            title: "TITLE"
				            
				        }
				    }
				}    
			}';
			$datasetsCost[] = '{
				label: "'.$status['name'].'",
				fillColor : "rgba(220,220,220,0.2)",
				strokeColor : "#'.$status['color'].'",
				pointColor : "#'.$status['color'].'",
				pointStrokeColor : "#'.$status['color'].'",
				pointHighlightFill : "#'.$status['color'].'",
				pointHighlightStroke : "#'.$status['color'].'",
				data : [0,'.implode(",", $dataCost[$status_key]).']
			}';
		}
		
*/		
		$datasetsCount = implode(",", $datasetsCount);
		$datasetsCost = implode(",", $datasetsCost);
		$labels = '0,'.implode(",", $labels);

		$this->modx->regClientStartupScript('<script type="text/javascript">
			var lineChartCount = {
				labels: ['.$labels.'],
				datasets : [
					'.$datasetsCount.'
				]
				
			}
			var lineChartCost = {
				labels: ['.$labels.'],
				datasets : [
					'.$datasetsCost.'
				]
			
			}

			window.onload = function(){
				//var chart1 = document.getElementById("line-chart").getContext("2d");
				//window.myLine = new Chart(chart1).Line(lineChartCount, {
			//		responsive: true
			//	});
	

				var dashboard = $(".js-dashboard-stats");
				var d_height = dashboard.height();
				var d_parent = dashboard.parents(".dashboard-block");
				d_parent.addClass("dashboard-stats");
				d_parent.find("h3").hide();
				d_parent.find(".body").css("max-height", d_height+50);
				d_parent.height(d_height);
				
				
				Highcharts.stockChart("container", {
                  chart: {
                    zoomType: "x"
                  },
                  xAxis: {
                    minRange: 3600
                  },
                
                  rangeSelector: {
                    selected: 1,
                    labelStyle: {
                         display: "none"
                      }
                  },
                  
                  plotOptions: {
                        series: {
                            fillColor: {
                                linearGradient: [0, 0, 0, 300],
                                stops: [
                                    [0, Highcharts.getOptions().colors[0]],
                                    [
                                        1,
                                        Highcharts.color(Highcharts.getOptions().colors[0])
                                            .setOpacity(0).get("rgba")
                                    ]
                                ]
                            }
                        }
                    },
                
                  series: [{
                    name: "Generation",
                    data: usdeur,
                    color: "#2e5a90"
                  }]
                });

				


			};
		</script>', true);


		return $stats;
	}

	public function loadCustomClasses($dir) {
		$files = scandir($this->config['customPath'] . $dir);
		foreach ($files as $file) {
			if (preg_match('/.*?\.class\.php$/i', $file)) {
				include_once($this->config['customPath'] . $dir . '/' . $file);
			}
		}
	}

	function month($month){
		$months = array(
			'1' => 'Январь',
			'2' => 'Февраль',
			'3' => 'Март',
			'4' => 'Апрель',
			'5' => 'Май',
			'6' => 'Июнь',
			'7' => 'Июль',
			'8' => 'Август',
			'9' => 'Сентябрь',
			'10' => 'Октябрь',
			'11' => 'Ноябрь',
			'12' => 'Декабрь',
		);
		return $months[$month];
	}

}