<?php
$plantID = 1855185;
$URL = "https://openapi.growatt.com/v1/plant/data";
// 1. CURL for main statistics

$price = 1.20;

//https://openapi.growatt.com/v1/plant/data?plant_id=1855185



/*

  if ($http_code_PlantAPI == 200 xor $http_code_PlantAPI == 302){
     echo "Retrieval of the power plant data was successful, Status ".$http_code_PlantAPI." <br>\n";
  }else{
     echo "Retrieval of the power plant data was not successful, Status ".$http_code_PlantAPI.", program is terminated  <br>\n";
     exit;
  }
*/

// 2. CURL for historical data


///$date = date_parse(date('c'));
//print_r($chunkArr['stats_month'][$date['year'].'-'.$date['month']]);
/*
foreach($chunkArr['total_counts'] as $status_key => $status){
	$statuses .= '<span style="background: #'.$status['color'].'"></span> '.$status['name'].'&nbsp;&nbsp;&nbsp;'; 
}
*/

$modx->log( modX::LOG_LEVEL_ERROR, 'START');


if(isset($_COOKIE['growattData'])) {
    $data = json_decode($_COOKIE['growattData'], true);
    $modx->log( modX::LOG_LEVEL_ERROR, 'from COOKIE='.print_r($data,true));
}else{
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://openapi.growatt.com/v1/plant/data?plant_id=1855185',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FRESH_CONNECT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'token: 6j8i282gao343104pvx3p8g76p9u3vd5'
       
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    //echo $response;
    
    $data = json_decode($response, JSON_PRETTY_PRINT);
    $data = $data['data'];
    $data['today_revenue'] = $price * $data['today_energy'];
    $data['total_revenue'] = $price * $data['total_energy'];
    $modx->log( modX::LOG_LEVEL_ERROR, 'response='.print_r($data,true));
    setcookie("growattData", json_encode($data), time()+5*60); // 5 minutes
}

$tpl = <<<EOT

<style>

g.highcharts-label.highcharts-range-label {
    display: block !important; 

}

g.highcharts-label.highcharts-range-label > text{

    content: '/' !important;
}


</style>

    <script type="text/javascript" src="/assets/components/growattstats/lumino/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="/assets/components/growattstats/lumino/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="//code.highcharts.com/stock/highstock.js"></script>
    <script type="text/javascript" src="/assets/components/growattstats/data/chart-data.js"></script>
    <script type="text/javascript">
        window.onload = function(){
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
    
    </script>
	<div class="js-dashboard-stats">
		<div class="row">
			<div class="large-3 small-6 columns">
				<div class="panel panel-blue panel-widget " style="padding-top:0.4em !important">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left" style="background:linear-gradient(90deg, #302b6b 0%, #2e5a90 100%);" >
							<img src="/assets/components/growattstats/images/power.png">
						</div>
						<div class="col-sm-9 col-lg-6 widget-right" style="padding-top: 15px;">
							<div class="large"><i>$data[today_energy]</i> kWh</div>
							<div class="text-muted">Generation <span class="blue-accent">Today</span></div>
						</div>
					</div>
				</div>
			</div>
			<div class="large-3 small-6 columns">
				<div class="panel panel-orange panel-widget" style="padding-top:0.4em !important">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left" style="background:linear-gradient(90deg, #302b6b 0%, #2e5a90 100%);" >
							<img src="/assets/components/growattstats/images/power.png">
						</div>
						<div class="col-sm-9 col-lg-6 widget-right" style="padding-top: 15px;">
							<div class="large"><i>$data[total_energy]</i> kWh</div>
							<div class="text-muted"><span class="blue-accent">Total</span> Generation</div>
						</div>
					</div>
				</div>
			</div>
			<div class="large-3 small-6 columns">
				<div class="panel panel-teal panel-widget" style="padding-top:0.4em !important">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left" style="background:linear-gradient(90deg, #302b6b 0%, #2e5a90 100%);" >
							<img src="/assets/components/growattstats/images/revenue.png">
						</div>
						<div class="col-sm-9 col-lg-6 widget-right" style="padding-top: 15px;">
							<div class="large"><i>$data[today_revenue]</i> DKK</div>
							<div class="text-muted">Revenue <span class="blue-accent">Today</span></div>
						</div>
					</div>
				</div>
			</div>
			<div class="large-3 small-6 columns">
				<div class="panel panel-teal panel-widget" style="padding-top:0.4em !important">
					<div class="row no-padding">
						<div class="col-sm-3 col-lg-5 widget-left" style="background:linear-gradient(90deg, #302b6b 0%, #2e5a90 100%);" >
							<img src="/assets/components/growattstats/images/revenue.png">
						</div>
						<div class="col-sm-9 col-lg-6 widget-right" style="padding-top: 15px;">
							<div class="large"><i>$data[total_revenue]</i> DKK</div>
							<div class="text-muted"><span class="blue-accent">Total</span> Revenue</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="large-12" style="padding: 0 13px;">
				<div class="panel panel-default" style="padding:0.8em 0">
					<div class="panel-heading" style="padding-bottom:15px;">Fiskeparken 4 generation </div>
					<div class="panel-body">
						<div id="container" style=""></div>
					</div>
				</div>
			</div>
		</div>
	</div>
EOT;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array('name'=>"INLINE-".uniqid(),'snippet'=>$tpl));
$chunk->setCacheable(false);

$output = $chunk->process($data);

//$modx->log( modX::LOG_LEVEL_ERROR, 'response='.print_r($data,true));

return $output;