<?php
require '../access/api/config.php';
$api_key = GPT_API;
$endpoint = "https://openai-api.caner.tw/v1/chat/completions";

$selected_lang = $_GET['lang'] ?? 'en-us';
$lang = require "../access/language/gpt/{$selected_lang}.php";

$receivedData = json_decode(file_get_contents('php://input'), true);
if (!$receivedData) {
    echo "<p class='cwc-weather-suggestion'>" . $lang['get_err'] . "</p>";
    exit;
}

$currentWeather = $receivedData['currentWeather'];
$hourlyForecasts = $receivedData['hourlyForecasts'];
$weatherLocation = $receivedData['weatherLocation'];
$tempUnits = $receivedData['tempUnits'];
$windUnits = $receivedData['windUnits'];
$weatherAQI = $receivedData['weatherAQI'];
$weatherAlert = $receivedData['weatherAlert'];

$mainInfo = $lang['location'] . ": " . $weatherLocation . ".";

$currentDescription = $lang['current_weather'] . ": \n" .
    $lang['temp'] . " " . $currentWeather['temp'] . $tempUnits .
    ", " . $lang['feelslike'] . " " . $currentWeather['feels_like'] . $tempUnits .
    ", " . $lang['pressure'] . " " . $currentWeather['pressure'] . "hPa" .
    ", " . $lang['humidity'] . " " . $currentWeather['humidity'] . "%" .
    ", " . $lang['dew_point'] . " " . $currentWeather['dew_point'] . $tempUnits .
    ", UVI " . $currentWeather['uvi'] .
    ", " . $lang['clouds'] . " " . $currentWeather['clouds'] . "%" .
    ", " . $lang['visibility'] . " " . $currentWeather['visibility'] . "m" .
    ", " . $lang['wind_speed'] . " " . $currentWeather['wind_speed'] . $windUnits .
    ", " . $lang['wind_deg'] . " " . $currentWeather['wind_deg'] . "°。" .
    $currentWeather['weather'][0]['main'] . "。";

$hourlyDescription = $lang['forecast_weather'] . ": \n";
foreach ($hourlyForecasts as $forecast) {
    $hourlyDescription .= $lang['temp'] . " " . $forecast['temp'] . $tempUnits .
        ", " . $lang['feelslike'] . " " . $forecast['feels_like'] . $tempUnits .
        ", " . $lang['pressure'] . " " . $forecast['pressure'] . "hPa" .
        ", " . $lang['humidity'] . " " . $forecast['humidity'] . "%" .
        ", " . $lang['dew_point'] . " " . $forecast['dew_point'] . $tempUnits .
        ", UVI " . (isset($forecast['uvi']) ? $forecast['uvi'] : 'N/A') .
        ", " . $lang['clouds'] . " " . $forecast['clouds'] . "%" .
        ", " . $lang['visibility'] . " " . (isset($forecast['visibility']) ? $forecast['visibility'] : 'N/A') . "m" .
        ", " . $lang['wind_speed'] . " " . $forecast['wind_speed'] . $windUnits .
        ", " . $lang['wind_deg'] . " " . $forecast['wind_deg'] . "°。" .
        $forecast['weather'][0]['main'] . "。\n";
}


$aqiDescription = $lang['aqi'] . ": " . $weatherAQI . " " . $lang['aqi_max'] . "。";
$alertDescription = $lang['alert'] . ": " . $weatherAlert . "。";

$prompt = $mainInfo . "\n" .
          $currentDescription . "\n" .
          $hourlyDescription . "\n" .
          $aqiDescription . "\n" .
          $alertDescription . "\n" .
          $lang['advice_prompt'] . 
          $lang['resp_lang'];

$messages = [
    ["role" => "user", "content" => $prompt]
];
$data = [
    "model" => "gpt-4-turbo-preview",
    "messages" => $messages,
    "temperature" => 0.4,
    "max_tokens" => 470,
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key",
]);
$response = curl_exec($ch);
if ($response === false) {
    $curlError = curl_error($ch);
    $adviceContent = $lang['unavailable'];
} else {
    $responseArray = json_decode($response, true);
    if (!isset($responseArray['choices'][0]['message']['content'])) {
        $adviceContent = $lang['resp_err'];
    } else {
        $advice = $responseArray['choices'][0]['message']['content'];
        $adviceContent = "<p class='cwc-weather-suggestion'>" . $advice . "</p>";
    }
}
curl_close($ch);
echo $adviceContent;
