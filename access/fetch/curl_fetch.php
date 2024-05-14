<?php
    function curlGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    function fetchData($url) {
        $response = curlGet($url);
        return json_decode($response, true);
    }