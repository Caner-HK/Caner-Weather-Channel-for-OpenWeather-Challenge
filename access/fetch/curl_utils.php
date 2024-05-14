<?php
function curlGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        return 'Curl Error: ' . curl_error($curl);
    }
    curl_close($curl);
    return $response;
}

function curlPost($url, $data, $headers = ['Content-Type: application/json']) {
    $postData = is_array($data) ? json_encode($data) : $data;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return 'Curl Error: ' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
}