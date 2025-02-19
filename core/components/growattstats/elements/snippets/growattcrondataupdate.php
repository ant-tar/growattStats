<?php
$modx->log(modX::LOG_LEVEL_ERROR,'start growattAPI ping');

if (!$growattstats = $modx->getService('growatttats', 'growattStats', $modx->getOption('growattstats_core_path', null, $modx->getOption('core_path') . 'components/growattstats/') . 'model/growattstats/', array())) {
		return 'Could not load class growattStats';
	}

$dataFilePath = $modx->getOption('assets_path')."components/growattstats/data/chart-data.js";
$modx->log(modX::LOG_LEVEL_ERROR,'file path='.$dataFilePath);

$plantID = 1855185;
$URL = "https://openapi.growatt.com/v1/plant/data";
// 1. CURL for main statistics

$price = 1.20;

//https://openapi.growatt.com/v1/plant/data?plant_id=1855185



// Загрузка содержимого файла
$content = file_get_contents($dataFilePath);

// Обработка содержимого, чтобы извлечь JSON
$start = strpos($content, '[');  // Найти начало массива
$end = strrpos($content, ']');    // Найти конец массива
$json = substr($content, $start, $end - $start + 1);
$modx->log(modX::LOG_LEVEL_ERROR,'JSON before replacements='.$json);

$json = str_replace('),', ')","', $json);
$json = str_replace('[[', '[["', $json);
$json = str_replace('[Date', '["Date', $json);
$json = str_replace(']]', '"]]', $json);
$json = str_replace('],', '"],', $json);
//$json = str_replace(']\n]', '"]\n]', $json);
//$modx->log(modX::LOG_LEVEL_ERROR,'JSON from path='.$json);
// Декодирование JSON в PHP массив
$existingData = json_decode($json, true);
//$modx->log(modX::LOG_LEVEL_ERROR,'decoded JSON='.print_r($existingData,true));

$newData = [];

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


//if(isset($_COOKIE['growattData'])) {
    //$data = json_decode($_COOKIE['growattData'], true);
   // $modx->log( modX::LOG_LEVEL_ERROR, 'from COOKIE='.print_r($data,true));
//}else{
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
    $modx->log( modX::LOG_LEVEL_ERROR, 'response='.print_r($data,true));
    $modx->log( modX::LOG_LEVEL_ERROR, 'response var dump='.var_dump($response));
    curl_close($curl);
    //echo $response;
    if($response && strlen(trim($response)) !== 0){
        $data = json_decode($response, JSON_PRETTY_PRINT);
        $data = $data['data'];
        $data['today_revenue'] = $price * $data['today_energy'];
        $data['total_revenue'] = $price * $data['total_energy'];
        
        
        $newData = [
            ["Date.UTC(".date("Y", time()).", ".(date("n", time())-1).", ".date("d", time()).")", $data['today_energy']]
        ];
        
        foreach ($newData as $key => $dataPoint) {
            $found = false;
            foreach ($existingData as $key2 => $existingPoint) {
                if ($existingPoint[0] == $dataPoint[0]) {
                    $found = true;
                    if($dataPoint >= $existingData[$key2]){
                        $existingData[$key2] = $dataPoint;
                    }
                    break;
                }
            }
            if (!$found) {
                $existingData[] = $dataPoint;
            }
        }
        $modx->log( modX::LOG_LEVEL_ERROR, 'final array='.print_r($existingData,true));
        $jsonData = json_encode($existingData);
         $modx->log( modX::LOG_LEVEL_ERROR, "var usdeur = ".str_replace('"', "", $jsonData).";");
        file_put_contents($dataFilePath, "var usdeur = ".str_replace('"', "", $jsonData).";");
    }    
    
    
  



    
    
//    setcookie("growattData", json_encode($data), time()+5*60); // 5 minutes
//}