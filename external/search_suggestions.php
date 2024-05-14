<?php
require '../access/api/config.php';
require '../access/fetch/curl_utils.php';

if(isset($_GET['input'])) {
    $input = $_GET['input'];
    $apiKey = Google_API;
    $lang = isset($_GET['lang']) ? $_GET['lang'] : 'en-us';
    $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=" . urlencode($input) . "&key=" . $apiKey . "&language=" . $lang;
    $response = curlGet($url);
    header('Content-Type: application/json');
    echo $response;
    exit;
}