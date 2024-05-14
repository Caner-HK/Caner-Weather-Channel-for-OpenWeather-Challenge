<?php
    require '../access/api/config.php';
    
    header('Content-Type: application/json; charset=utf-8');
    
    $lat = isset($_GET['lat']) ? $_GET['lat'] : null;
    $lon = isset($_GET['lon']) ? $_GET['lon'] : null;
    $lang = isset($_GET['lang']) ? $_GET['lang'] : 'zh_cn';
    $units = isset($_GET['units']) ? $_GET['units'] : 'metric';
    $weatherApiKey = OWM_API;
    
    if (is_null($lat) || is_null($lon) || !is_numeric($lat) || !is_numeric($lon)) {
        echo json_encode([
            'error' => 'Invalid location',
            'details' => 'You must pass in the correct latitude and longitude to query the weather'
        ]);
        exit;
    }
    
    function curlGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=$weatherApiKey&units=$units&lang=$lang";
    $response = curlGet($weatherUrl);

    echo $response;